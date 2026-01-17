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
            'fee_id' => 'required|exists:student_fees,id',
            'amount' => 'required|numeric|min:0',
            'type' => 'required|in:cash,bank_transfer,online,card',
            'note' => 'nullable|string',
            'slip' => 'nullable|image|max:2048' // Max 2MB
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $fee = \App\Models\StudentFee::findOrFail($request->fee_id);

        if ($fee->status === 'paid') {
            return response()->json(['message' => 'Fee already paid'], 400);
        }

        // Handle File Upload
        $slipPath = null;
        if ($request->hasFile('slip')) {
            $file = $request->file('slip');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/slips'), $filename);
            $slipPath = 'uploads/slips/' . $filename;
        }

        // Determine Status
        // Online payments are instant (mocked as valid), Bank Transfers are pending
        $status = ($request->type === 'bank_transfer') ? 'pending' : 'paid';

        $payment = Payment::create([
            'user_id' => $fee->student_id,
            'course_id' => $fee->course_id,
            'amount' => $request->amount,
            'month' => $fee->month,
            'type' => $request->type,
            'paid_at' => ($status === 'paid') ? now() : null,
            'status' => $status, 
            'note' => $request->note,
            'slip_image' => $slipPath
        ]);

        // Only update Fee record if payment is confirmed immediately
        if ($status === 'paid') {
            $fee->update([
                'status' => 'paid',
                'paid_at' => now(),
                'payment_method' => $request->type,
                'transaction_ref' => $payment->id
            ]);

            // Reactivate Student
            $student = \App\Models\User::find($fee->student_id);
            if ($student && $student->status === 'inactive') {
                $student->status = 'active';
                $student->save();
            }
        }

        return response()->json(['message' => 'Payment recorded', 'payment' => $payment], 201);
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
                $query->where('parent_email', $user->email)
                      ->orWhere('parent_id', $user->id);
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
                 $q->where('parent_email', $user->email)
                   ->orWhere('parent_id', $user->id);
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
                 $q->where('parent_email', $user->email)
                   ->orWhere('parent_id', $user->id);
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
         $query = Payment::query()->with(['student', 'course']); // Changed from StudentFee to Payment to match UI expectation of transactions
         
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
         
         // Default sort
         $query->orderBy('created_at', 'desc');

         return response()->json($query->paginate(20));
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
            
        return response()->json([
            'monthly_revenue' => $monthlyRevenue,
            'course_revenue' => $courseRevenue
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
     * Admin: Get Teacher Settlements
     */
    public function getTeacherSettlements(Request $request) {
        $month = $request->month ?? Carbon::now()->format('Y-m');

        // Fetch payments with nested relationships
        $payments = Payment::where('status', 'paid')
            ->where('month', $month)
            ->with(['course.teacher']) 
            ->get();

        // Group by Teacher ID
        $settlements = $payments->groupBy(function($payment) {
            return $payment->course->teacher_id ?? 'unknown';
        })->map(function($group) {
            $teacher = $group->first()->course->teacher;
            $total = $group->sum('amount');
            
            return [
                'teacher_id' => $teacher->id ?? 0,
                'teacher_name' => $teacher->name ?? 'Unknown Teacher',
                'payment_count' => $group->count(),
                'total_collected' => $total,
                'teacher_share' => $total * 0.8
            ];
        })->values();

        return response()->json($settlements);
    }
}
