<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class PaymentController extends Controller
{
    /**
     * Get logged-in user's payment history
     */
    public function myPayments(Request $request)
    {
        $user = $request->user();
        
        $payments = Payment::where('user_id', $user->id)
            ->whereNotNull('course_id') // Filter out orphaned records
            ->whereHas('course') // Ensure course relation exists
            ->with(['course', 'course.subject', 'course.batch'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
            
        return response()->json($payments);
    }

    /**
     * Get Pending Fees for Enrolled Courses
     */
    public function getDueFees(Request $request)
    {
        $user = $request->user();
        
        // If parent, get children's fees? 
        // For now, assuming this endpoint is for Student to see their own dues.
        // Parent logic will be separate or handled via 'child_id' param if needed.
        
        $fees = \App\Models\StudentFee::where('student_id', $user->id)
            ->where('status', 'pending')
            ->whereHas('course') // ONLY Return fees where course still exists
            ->with(['course.subject'])
            ->orderBy('month', 'desc')
            ->get()
            ->map(function($fee) {
                return [
                    'id' => $fee->id, // fee_id
                    'course_id' => $fee->course_id,
                    'course_name' => $fee->course->name ?? 'Unknown',
                    'subject' => $fee->course->subject->name ?? 'Subject',
                    'amount' => $fee->amount,
                    'month' => Carbon::createFromFormat('Y-m', $fee->month)->format('F Y'), // 2026-01 -> January 2026
                    'due_date' => $fee->due_date,
                    'is_overdue' => Carbon::now()->gt(Carbon::parse($fee->due_date))
                ];
            });
            
        return response()->json($fees);
    }

    /**
     * Record a Payment (Manual / Upload / Online Callback)
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fee_id' => 'required_without:fee_ids|exists:student_fees,id',
            'fee_ids' => 'required_without:fee_id|array', 
            'fee_ids.*' => 'exists:student_fees,id',
            // 'amount' is treated as Total Paid. 
            'amount' => 'required|numeric|min:0', 
            'type' => 'required|in:cash,bank_transfer,online,card',
            'note' => 'nullable|string',
            'slip' => 'nullable|image|max:5120' // Increased limit for ease
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Handle File Upload once
        $slipPath = null;
        if ($request->hasFile('slip')) {
            $file = $request->file('slip');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/slips'), $filename);
            $slipPath = 'uploads/slips/' . $filename;
        }

        // Normalize IDs
        $ids = $request->filled('fee_ids') ? $request->fee_ids : [$request->fee_id];

        // Ensure we handle array input if it came as string due to FormData
        // If coming from FormData, arrays might look like fee_ids[0], fee_ids[1]...
        // Laravel handles this if name is "fee_ids" array.
        // However, if manual CSV string passed? Assuming Standard Array or Single ID.
        if (is_string($ids)) {
             $ids = explode(',', $ids);
        }

        // Create Single Payment Record (Grouping)
        // If multiple IDs, we sum up the amount and create One Payment.
        // But we need to link this Payment to multiple Fees?
        // The 'Payment' model has 'course_id'. If bulk, course_id is singular?
        // If we pay for Math, Science, History -> Which course_id?
        // Solution: Create ONE Payment record with course_id = null (or first one) and a special flag/note.
        // OR: Strictly speaking, a Payment belongs to a User. The specific breakdown is in the Fees table.

        $totalAmount = 0;
        $validFees = [];
        $feeMonth = null;
        $courseId = null;

        foreach ($ids as $id) {
            $fee = \App\Models\StudentFee::find($id);
            if (!$fee || $fee->status === 'paid') continue;
            
            $validFees[] = $fee;
            $totalAmount += $fee->amount;
            
            // Capture first valid fee details for metadata
            if (!$courseId) {
                $courseId = $fee->course_id;
                $feeMonth = $fee->month;
            }
        }

        if (empty($validFees)) {
             return response()->json(['message' => 'No eligible fees found or already paid'], 400);
        }

        // Determine Status
        $status = ($request->type === 'bank_transfer') ? 'pending' : 'paid';

        // Create ONE Payment Record
        $payment = Payment::create([
            'user_id' => $validFees[0]->student_id,
            'course_id' => (count($validFees) === 1) ? $courseId : null, // If mixed courses, null
            'amount' => $totalAmount,
            'month' => (count($validFees) === 1) ? $feeMonth : now()->format('Y-m'), // Use current month for bulk
            'type' => $request->type,
            'paid_at' => ($status === 'paid') ? now() : null,
            'status' => $status, 
            'note' => $request->note . (count($validFees) > 1 ? ' (Bulk: ' . count($validFees) . ' items)' : ''),
            'slip_image' => $slipPath
        ]);

        // Update All Fees to point to this Payment
        foreach ($validFees as $fee) {
            if ($status === 'paid') {
                $fee->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                    'payment_method' => $request->type,
                    'transaction_ref' => $payment->id
                ]);
            } else {
                 // For pending bank transfer, we might want to link them too?
                 // Currently 'transaction_ref' implies a successful payment reference.
                 // We can leave them pending. The Admin will Approve the *Payment*.
                 // But the Payment doesn't know WHICH fees it covers if we don't store it?
                 // *Critical*: We need to know which fees this Payment covers to mark them paid on approval.
                 // We can store a JSON list in Payment 'metadata' or 'description' if specific columns don't exist,
                 // OR we can update the fees with a 'pending_payment_id' column (custom).
                 // SIMPLER: Just link transaction_ref NOW, but keep status 'pending'.
                 $fee->update([
                    'transaction_ref' => $payment->id
                 ]);
            }
        }

        // Reactivate Student (if paid)
        if ($status === 'paid') {
            $student = \App\Models\User::find($validFees[0]->student_id);
            if ($student && $student->status === 'inactive') {
                $student->status = 'active';
                $student->save();
            }
        }

        return response()->json(['message' => "Payment recorded for " . count($validFees) . " fees", 'payment' => $payment], 201);
    }

    /**
     * Admin: Approve a Pending Payment
     */
    public function approve(Request $request, $id)
    {
        $payment = Payment::findOrFail($id);

        if ($payment->status === 'paid') {
            return response()->json(['message' => 'Already approved'], 400);
        }

        $payment->status = 'paid';
        $payment->paid_at = now();
        $payment->save();

        // Update the related Fee
        // Find the fee matching user, course, month (since we didn't store fee_id in payment directly, logic inferred)
        // Ideally Payment should have fee_id, but current schema links loosely. 
        // Let's find the fee:
        $fee = \App\Models\StudentFee::where('student_id', $payment->user_id)
            ->where('course_id', $payment->course_id)
            ->where('month', $payment->month)
            ->first();

        if ($fee) {
            $fee->update([
                'status' => 'paid',
                'paid_at' => now(),
                'payment_method' => $payment->type,
                'transaction_ref' => $payment->id
            ]);
        }

        return response()->json(['message' => 'Payment Approved Successfully', 'payment' => $payment]);
    }

    /**
     * Admin: Record Cash Payment (Manual)
     */
    public function recordCashPayment(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:users,id',
            'course_id' => 'required|exists:courses,id',
            'amount' => 'required|numeric|min:0',
            'month' => 'required|string', // YYYY-MM
            'note' => 'nullable|string'
        ]);

        // Check if already paid
        $existingFee = \App\Models\StudentFee::where('student_id', $request->student_id)
            ->where('course_id', $request->course_id)
            ->where('month', $request->month)
            ->first();

        if ($existingFee && $existingFee->status === 'paid') {
            return response()->json(['message' => 'Fee is already marked as paid'], 400);
        }

        // Create Payment Record
        $payment = Payment::create([
            'user_id' => $request->student_id,
            'course_id' => $request->course_id,
            'amount' => $request->amount,
            'month' => $request->month,
            'type' => 'cash',
            'status' => 'paid',
            'paid_at' => now(),
            'note' => $request->note ?? 'Cash Payment at Counter'
        ]);

        // Update or Create Fee Record
        if ($existingFee) {
            $existingFee->update([
                'status' => 'paid',
                'paid_at' => now(),
                'amount' => $request->amount, // Update amount if different
                'payment_method' => 'cash',
                'transaction_ref' => $payment->id
            ]);
        } else {
             // Create fee record on the fly if it didn't exist (e.g. paying in advance)
             // Need due_date, defaulting to end of month
             $dueDate = Carbon::parse($request->month)->endOfMonth()->format('Y-m-d');
             
             \App\Models\StudentFee::create([
                'student_id' => $request->student_id,
                'course_id' => $request->course_id,
                'month' => $request->month,
                'amount' => $request->amount,
                'due_date' => $dueDate,
                'status' => 'paid',
                'paid_at' => now(),
                'payment_method' => 'cash',
                'transaction_ref' => $payment->id
             ]);
        }

        return response()->json(['message' => 'Cash payment recorded successfully', 'payment' => $payment]);
    }

    /**
     * Admin: Reject Payment
     */
    public function reject(Request $request, $id)
    {
        $payment = Payment::findOrFail($id);
        $payment->status = 'rejected';
        $payment->note = $request->note ?? 'Rejected by Admin';
        $payment->save();

        return response()->json(['message' => 'Payment Rejected', 'payment' => $payment]);
    }

    /**
     * Parent: Get Pending Fees for All Children
     */
    public function getParentDueFees(Request $request)
    {
        $user = $request->user();
        
        $childrenIds = \App\Models\User::where(function($query) use ($user) {
                if (!empty($user->email)) $query->where('parent_email', $user->email);
                $query->orWhere('parent_id', $user->id);
                if (!empty($user->phone)) $query->orWhere('parent_phone', $user->phone);
            })
            ->pluck('id');
        
        $fees = \App\Models\StudentFee::whereIn('student_id', $childrenIds)
            ->where('status', 'pending')
            ->with(['student', 'course'])
            ->orderBy('due_date', 'asc')
            ->get()
            ->map(function($fee) {
                return [
                    'id' => $fee->id,
                    'student_name' => $fee->student->name,
                    'course_name' => $fee->course->name ?? 'Unknown',
                    'amount' => $fee->amount,
                    'month_label' => Carbon::createFromFormat('Y-m', $fee->month)->format('F Y'),
                    'due_date' => $fee->due_date,
                    'is_overdue' => Carbon::now()->gt(Carbon::parse($fee->due_date))
                ];
            });
            
        return response()->json($fees);
    }
    
    /**
     * Parent: Get Pending Fees for Specific Child (Mirrors getDueFees)
     */
    public function getChildDueFees(Request $request, $id) {
        $user = $request->user();
        
        // Validation: Ensure child belongs to parent
        $isChild = \App\Models\User::where('id', $id)
             ->where(function($q) use ($user) {
                 if (!empty($user->email)) $q->where('parent_email', $user->email);
                 $q->orWhere('parent_id', $user->id);
                 if (!empty($user->phone)) $q->orWhere('parent_phone', $user->phone);
             })->exists();

        if (!$isChild) return response()->json(['message' => 'Unauthorized'], 403);

        // Same logic as getDueFees but for $id
        $fees = \App\Models\StudentFee::where('student_id', $id)
            ->where('status', 'pending')
            ->with(['course.subject'])
            ->orderBy('month', 'desc')
            ->get()
            ->map(function($fee) {
                return [
                    'id' => $fee->id, 
                    'course_id' => $fee->course_id,
                    'course_name' => $fee->course->name ?? 'Unknown',
                    'subject' => $fee->course->subject->name ?? 'Subject',
                    'amount' => $fee->amount,
                    'month' => Carbon::createFromFormat('Y-m', $fee->month)->format('F Y'),
                    'due_date' => $fee->due_date,
                    'is_overdue' => Carbon::now()->gt(Carbon::parse($fee->due_date))
                ];
            });
            
        return response()->json($fees);
    }

    /**
     * Parent: Get Payment History for Specific Child (Mirrors myPayments)
     */
    public function getChildPayments(Request $request, $id) {
        $user = $request->user();
        
        $isChild = \App\Models\User::where('id', $id)
             ->where(function($q) use ($user) {
                 if (!empty($user->email)) $q->where('parent_email', $user->email);
                 $q->orWhere('parent_id', $user->id);
                 if (!empty($user->phone)) $q->orWhere('parent_phone', $user->phone);
             })->exists();
        
        if (!$isChild) return response()->json(['message' => 'Unauthorized'], 403);
        
        $payments = Payment::where('user_id', $id)
            ->with(['course', 'course.subject', 'course.batch'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
            
        return response()->json($payments);
    }
    
    /**
     * Teacher: Get Payment Status for a Course & Month
     */
    public function getTeacherStudentPayments(Request $request)
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'month' => 'nullable|string' // YYYY-MM
        ]);
        
        $month = $request->month ?? Carbon::now()->format('Y-m');
        
        $fees = \App\Models\StudentFee::where('course_id', $request->course_id)
            ->where('month', $month)
            ->with(['student'])
            ->get()
            ->map(function($fee) {
                return [
                    'student_id' => $fee->student_id,
                    'student_name' => $fee->student->name,
                    'status' => $fee->status, // paid, pending
                    'paid_at' => $fee->paid_at ? Carbon::parse($fee->paid_at)->format('d M Y') : null
                ];
            });
            
        return response()->json($fees);
    }

    /**
     * Admin: Get Payment Summary / Filter
     */
    public function getAdminPaymentSummary(Request $request)
    {
         $query = Payment::query()->with(['student', 'course']);
         
         if ($request->has('status')) {
             $query->where('status', $request->status);
         }
         
         // Search by student name
         if ($request->has('search')) {
             $search = $request->search;
             $query->whereHas('student', function($q) use ($search) {
                 $q->where('name', 'like', "%{$search}%")
                   ->orWhere('username', 'like', "%{$search}%");
             });
         }
         
         // Calculate Global Stats
         $totalRevenue = Payment::where('status', 'paid')->sum('amount');
         $totalPendingCount = Payment::where('status', 'pending')->count();
         // Uncollected comes from Fee table (pending bills)
         $uncollectedAmount = \App\Models\StudentFee::where('status', 'pending')->sum('amount');
         
         // Default sort
         $query->orderBy('created_at', 'desc');
         $paginated = $query->paginate(20);

         // Custom response format to include stats
         return response()->json([
             'data' => $paginated->items(),
             'current_page' => $paginated->currentPage(),
             'last_page' => $paginated->lastPage(),
             'total' => $paginated->total(),
             'stats' => [
                 'total_revenue' => $totalRevenue,
                 'pending_count' => $totalPendingCount,
                 'uncollected_fees' => $uncollectedAmount
             ]
         ]);
    }

    /**
     * Admin: Get Analytics Data
     */
    public function getAnalytics(Request $request) {
        // 1. Monthly Revenue (Last 6 Months) - Collection Fallback
        $monthlyRevenue = Payment::where('status', 'paid')
            ->whereNotNull('paid_at')
            ->where('paid_at', '>=', Carbon::now()->subMonths(6))
            ->get()
            ->groupBy(function($val) {
                return Carbon::parse($val->paid_at)->format('Y-m');
            })
            ->map(function($group, $key) {
                return ['month' => $key, 'total' => $group->sum('amount')];
            })
            ->sortBy('month')
            ->values();
            
        // 2. Revenue by Course (Top 5) - PHP Collection Fallback
        $courseRevenue = Payment::where('status', 'paid')
            ->with('course')
            ->get()
            ->groupBy(fn($p) => $p->course->name ?? 'Other')
            ->map(fn($group, $name) => [
                'course_name' => $name,
                'total' => $group->sum('amount')
            ])
            ->sortByDesc('total')
            ->take(5)
            ->values();
            
        // 3. Payment Method Distribution
        $paymentMethods = Payment::select('type', \Illuminate\Support\Facades\DB::raw('count(*) as count'))
            ->groupBy('type')
            ->get()
            ->map(function($row) {
                 return ['type' => ucfirst(str_replace('_', ' ', $row->type)), 'count' => $row->count];
            });

        return response()->json([
            'monthly_revenue' => $monthlyRevenue,
            'course_revenue' => $courseRevenue,
            'payment_methods' => $paymentMethods
        ]);
    }

    /**
     * Admin: Export Monthly Report
     */
    public function exportReport(Request $request) {
        // ... (existing code) ...
        $month = $request->month ?? Carbon::now()->format('Y-m');
        
        $payments = Payment::where('month', $month)
            ->with(['student', 'course'])
            ->orderBy('paid_at', 'desc')
            ->get();
            
        $csvHeader = ["ID", "Student Name", "Course", "Amount", "Type", "Status", "Date", "Note"];
        $csvData = [];
        $csvData[] = implode(',', $csvHeader);
        
        foreach ($payments as $p) {
            $csvData[] = implode(',', [
                $p->id,
                '"' . ($p->student->name ?? 'Unknown') . '"',
                '"' . ($p->course->name ?? 'Unknown') . '"',
                $p->amount,
                $p->type,
                $p->status,
                $p->paid_at,
                '"' . $p->note . '"'
            ]);
        }
        
        $csvContent = implode("\n", $csvData);
        
        return response()->json([
            'file_name' => "ems_report_{$month}.csv",
            'content' => $csvContent
        ]);
    }

    /**
     * Admin: Generate Monthly Fees for Active Enrollments
     */
    public function generateMonthlyFees(Request $request) {
        $request->validate([
            'month' => 'required|date_format:Y-m', // e.g. 2026-02
            'due_date' => 'required|date' 
        ]);

        $month = $request->month;
        $dueDate = $request->due_date;
        $count = 0;

        // Get all active enrollments
        // Assuming 'enrollments' table has 'status' = 'active'
        // And linked course has a fee_amount > 0
        $enrollments = \App\Models\Enrollment::where('status', 'active')
            ->whereHas('course', function($q) {
                $q->where('fee_amount', '>', 0);
            })
            ->with('course')
            ->get();

        foreach ($enrollments as $enrollment) {
            // Check if fee already exists
            $exists = \App\Models\StudentFee::where('student_id', $enrollment->user_id)
                ->where('course_id', $enrollment->course_id)
                ->where('month', $month)
                ->exists();

            if (!$exists) {
                \App\Models\StudentFee::create([
                    'student_id' => $enrollment->user_id,
                    'course_id' => $enrollment->course_id,
                    'month' => $month,
                    'amount' => $enrollment->course->fee_amount,
                    'due_date' => $dueDate,
                    'status' => 'pending'
                ]);
                $count++;
            }
        }

        return response()->json(['message' => "Successfully generated {$count} fee records for {$month}", 'count' => $count]);
    }

    /**
     * Admin: Get Teacher Settlements (Detailed)
     */
    public function getTeacherSettlements(Request $request) {
        $month = $request->month ?? Carbon::now()->format('Y-m');

        // Fetch Fees (Expectations) with nested relationships
        // fees -> course -> teacher
        $fees = \App\Models\StudentFee::where('month', $month)
            ->with(['course.teacher']) 
            ->get();

        // Group by Teacher ID
        $settlements = $fees->groupBy(function($fee) {
            return $fee->course->teacher_id ?? 'unknown';
        })->map(function($group) {
            $teacher = $group->first()->course->teacher;
            
            $totalExpected = $group->sum('amount');
            $collected = $group->where('status', 'paid')->sum('amount');
            $pendingAmount = $group->where('status', 'pending')->sum('amount');
            
            $totalStudents = $group->count(); // Total fee records (enrollments)
            $paidCount = $group->where('status', 'paid')->count();
            $pendingCount = $group->where('status', 'pending')->count();

            return [
                'teacher_id' => $teacher->id ?? 0,
                'teacher_name' => $teacher->name ?? 'Unknown Teacher',
                'payment_count' => $paidCount,
                'pending_count' => $pendingCount,
                'total_students' => $totalStudents, 
                'total_collected' => $collected,
                'total_pending' => $pendingAmount,
                'teacher_share' => $collected * 0.8 // Default 80% (Frontend handles custom)
            ];
        })->values();

        return response()->json($settlements);
    }
    /**
     * Admin: Get List of Students with Pending Fees
     * Exclude students with >= 4 months of consecutive pending fees (likely dropouts)
     */
    public function getPendingFeeList(Request $request) {
        $pendingFees = \App\Models\StudentFee::where('status', 'pending')
            ->with(['student', 'course'])
            ->orderBy('month', 'desc')
            ->get();
            
        $grouped = $pendingFees->groupBy('student_id');
        $result = [];
        
        foreach ($grouped as $studentId => $fees) {
             // If student has 4 or more pending records, treat as "Dropped Out" and hide from active collection list
             if ($fees->count() >= 4) {
                 continue;
             }
             
             foreach ($fees as $fee) {
                 $result[] = [
                     'id' => $fee->id,
                     'student' => $fee->student, // Return full object for display if needed
                     'course' => $fee->course,
                     'amount' => $fee->amount,
                     'month' => $fee->month,
                     'status' => 'pending'
                 ];
             }
        }
        
        return response()->json($result);
    }
}
