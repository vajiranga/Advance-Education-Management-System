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
            ->with(['course' => function ($q) {
                $q->withTrashed();
            }, 'course.subject', 'course.batch'])
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
             // Ensure Student is actually Enrolled in the course (Active)
            ->whereHas('course', function ($query) use ($user) {
                 $query->whereHas('students', function ($q) use ($user) {
                     $q->where('users.id', $user->id) // Current Student
                       ->where('enrollments.status', 'active'); // Active Enrollment
                 });
            })
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

        // Link Payment ID to Fees and Update Fee Status
        foreach ($validFees as $fee) {
             // If cash/online (immediate success), mark fee as paid immediately
             // If bank transfer, fee remains pending until admin approves payment
             if ($status === 'paid') {
                 $fee->update(['status' => 'paid', 'paid_at' => now(), 'payment_id' => $payment->id]);
             } else {
                 // Optionally link payment_id even if pending?
                 // The schema might not have foreign key strictness?
                 // Let's assume we can link it
                 // $fee->payment_id = $payment->id; $fee->save();
             }
        }

        // --- NOTIFICATION TRIGGERS ---
        if ($status === 'paid') {
            // Notify Student
            \App\Models\Notification::create([
                'user_id' => $payment->user_id,
                'type' => 'payment_success',
                'title' => 'Payment Successful',
                'message' => 'Your payment of LKR ' . number_format($totalAmount) . ' has been successfully recorded.',
                'data' => json_encode(['payment_id' => $payment->id])
            ]);
        } else {
             // Notify Student (Pending Approval)
            \App\Models\Notification::create([
                'user_id' => $payment->user_id,
                'type' => 'payment_pending',
                'title' => 'Payment Under Review',
                'message' => 'Your payment of LKR ' . number_format($totalAmount) . ' is pending verification.',
                'data' => json_encode(['payment_id' => $payment->id]) // Should be array, Laravel casts to json automatically if cast is set, but explicit json_encode safer for string column
            ]);
        }
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
            // Fix: Check Active Enrollment
            ->whereHas('course', function ($query) {
                 $query->whereHas('students', function ($q) {
                       $q->whereColumn('enrollments.user_id', 'student_fees.student_id')
                         ->where('enrollments.status', 'active');
                 });
            })
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
            // Fix: Check Active Enrollment
            ->whereHas('course', function ($query) use ($id) {
                 $query->whereHas('students', function ($q) use ($id) {
                     $q->where('users.id', $id)
                       ->where('enrollments.status', 'active');
                 });
            })
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
            ->with(['course' => function ($q) {
                $q->withTrashed();
            }, 'course.subject', 'course.batch'])
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

         // Date Range Filter
         if ($request->has('start_date') && $request->has('end_date')) {
             $start = $request->start_date . ' 00:00:00';
             $end = $request->end_date . ' 23:59:59';
             $query->whereBetween('created_at', [$start, $end]);
         }

         // Calculate Global Stats
         $statsQuery = Payment::query();
         if ($request->has('start_date') && $request->has('end_date')) {
             $statsQuery->whereBetween('created_at', [$request->start_date . ' 00:00:00', $request->end_date . ' 23:59:59']);
         }

         $totalRevenue = (clone $statsQuery)->where('status', 'paid')->sum('amount');
         $totalPendingCount = (clone $statsQuery)->where('status', 'pending')->count();
         $uncollectedAmount = \App\Models\StudentFee::where('status', 'pending')->sum('amount'); // This is total outstanding, arguably shouldn't filter by transaction date.

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
        // 1. Determine Academic Year
        // If today is Jan 2026, we are in 2025 Academic Year (Feb 2025 - Jan 2026)
        // If today is Feb 2026, we are in 2026 Academic Year (Feb 2026 - Jan 2027)
        $currentYear = Carbon::now()->year;
        if (Carbon::now()->month < 2) {
            $currentYear--;
        }

        $year = $request->input('year', $currentYear);
        $startDay = (int) (\App\Models\SystemSetting::where('key', 'feeCycleStartDay')->value('value') ?? 10);
        $teacherDeduction = (float) (\App\Models\SystemSetting::where('key', 'teacherFeeDeductionPercentage')->value('value') ?? 25);
        $instituteShare = $teacherDeduction / 100; // Institute keeps the deduction percentage

        // 2. Prepare Data Structure
        $labels = [];
        $revenueData = [];
        $pendingData = [];
        $studentData = [];
        $netProfitData = [];

        // Loop 12 months: Feb (2) of $year to Jan (1) of $year+1
        $currentDate = Carbon::create($year, 2, 1); // Feb 1st

        for ($i = 0; $i < 12; $i++) {
            // Calculate Cycle Range
            // Start: Month M, Day S
            // End: Month M+1, Day S-1

            // We need to handle the specific Cycle Start Day
            // E.g., for Feb: Start = Feb 15. End = Mar 14.

            $cycleStart = $currentDate->copy()->day($startDay);
            $cycleEnd = $cycleStart->copy()->addMonth()->subDay()->endOfDay();

            // Label: "Feb" or "Feb '25"
            $labels[] = $currentDate->format('M Y');

            // 3. Query Metrics

            // A. Revenue (Collected Fees) - Based on Transaction Date (paid_at)
            $rev = Payment::where('status', 'paid')
                ->whereBetween('paid_at', [$cycleStart, $cycleEnd])
                ->sum('amount');
            $revenueData[] = $rev;

            // B. Uncollected (Pending Fees) - Based on Due Date
            // Use StudentFee logic where status is pending and due_date falls in this cycle
            // OR created_at falls in this cycle? Usually Due Date aligns with cycle.
            $pending = \App\Models\StudentFee::where('status', 'pending')
                 ->whereBetween('due_date', [$cycleStart, $cycleEnd])
                 ->sum('amount');
            $pendingData[] = $pending;

            // C. New Students - Based on User creation date (Role: student)
            $students = \App\Models\User::where('role', 'student')
                ->whereBetween('created_at', [$cycleStart, $cycleEnd])
                ->count();
            $studentData[] = $students;

            // D. Net Profit (Revenue × Institute Share %)
            // Institute Share = 100% - Teacher Fee Deduction %
            $netProfitData[] = $rev * $instituteShare;

            // Move to next month
            $currentDate->addMonth();
        }

        return response()->json([
            'year' => $year,
            'labels' => $labels,
            'datasets' => [
                'revenue' => $revenueData,
                'pending' => $pendingData,
                'students' => $studentData,
                'netProfit' => $netProfitData
            ],
            // Keep legacy structure if needed for safety (optional)
            'monthly_revenue' => [], // Deprecated
            'course_revenue' => [], // Can fetch separate if needed
            'payment_methods' => [] // Can fetch separate if needed
        ]);
    }

    /**
     * Admin: Export Monthly Report
     */
    public function exportReport(Request $request) {
        // ... (existing code) ...
        $month = $request->month ?? Carbon::now()->format('Y-m');

        $query = Payment::query()->with(['student', 'course']);

        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('paid_at', [$request->start_date . ' 00:00:00', $request->end_date . ' 23:59:59']);
        } else {
            $query->where('month', $month);
        }

        $payments = $query->orderBy('paid_at', 'desc')->get();

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
            ->with(['course', 'student.parentAccount'])
            ->get();

        foreach ($enrollments as $enrollment) {
            // Check if fee already exists
            $exists = \App\Models\StudentFee::where('student_id', $enrollment->user_id)
                ->where('course_id', $enrollment->course_id)
                ->where('month', $month)
                ->exists();

            if (!$exists) {
                $fee = \App\Models\StudentFee::create([
                    'student_id' => $enrollment->user_id,
                    'course_id' => $enrollment->course_id,
                    'month' => $month,
                    'amount' => $enrollment->course->fee_amount,
                    'due_date' => $dueDate,
                    'status' => 'pending'
                ]);
                $count++;

                // Notify Student
                \App\Models\Notification::create([
                    'user_id' => $enrollment->user_id,
                    'type' => 'fee_due',
                    'title' => 'New Fee Details',
                    'message' => 'Fee for ' . $enrollment->course->name . ' (' . $month . ') is now due.',
                    'data' => json_encode(['fee_id' => $fee->id])
                ]);

                // Notify Parent
                if ($enrollment->student && $enrollment->student->parentAccount) {
                    \App\Models\Notification::create([
                        'user_id' => $enrollment->student->parentAccount->id,
                        'type' => 'fee_due',
                        'title' => 'Fee Due for ' . $enrollment->student->name,
                        'message' => 'Fee for ' . $enrollment->course->name . ' (' . $month . ') is now pending.',
                        'data' => json_encode(['fee_id' => $fee->id, 'student_id' => $enrollment->user_id])
                    ]);
                }
            }
        }

        return response()->json(['message' => "Successfully generated {$count} fee records for {$month}", 'count' => $count]);
    }

    /**
     * Admin: Get Teacher Settlements (Detailed)
     */
    public function getTeacherSettlements(Request $request) {
        $month = $request->month ?? Carbon::now()->format('Y-m');

        // Get configured deduction percentage from settings (default 10%)
        $deductionPercentage = (float) \App\Models\SystemSetting::where('key', 'teacherFeeDeductionPercentage')->value('value') ?? 10;

        $allFees = collect();

        if ($request->has('start_date') && $request->has('end_date')) {
             // Collection Based Report
             $allFees = \App\Models\StudentFee::where('status', 'paid')
                ->whereBetween('paid_at', [$request->start_date . ' 00:00:00', $request->end_date . ' 23:59:59'])
                ->with(['course.teacher', 'student'])
                ->get();
        } else {
             // Month Based Report (Default)
             $allFees = \App\Models\StudentFee::where('month', $month)
                ->with(['course.teacher', 'student'])
                ->get();
        }

        if ($allFees->isEmpty()) {
            return response()->json([]);
        }

        // Group by Teacher
        $settlements = $allFees->groupBy(function($fee) {
            return $fee->course && $fee->course->teacher_id ? $fee->course->teacher_id : 'unknown';
        })->filter(function($group, $key) {
            return $key !== 'unknown'; // Remove entries without teachers
        })->map(function($feeGroup) use ($deductionPercentage) {
            $firstFee = $feeGroup->first();
            $teacher = $firstFee->course ? $firstFee->course->teacher : null;

            if (!$teacher) {
                return null;
            }

            // Separate paid and pending
            $paidFees = $feeGroup->where('status', 'paid');
            $pendingFees = $feeGroup->where('status', 'pending');

            $totalCollected = $paidFees->sum('amount');
            $totalStudents = $feeGroup->count();
            $paidCount = $paidFees->count();
            $pendingCount = $pendingFees->count();

            // Calculate teacher share based on settings
            $totalDeduction = ($totalCollected * $deductionPercentage) / 100;
            $totalTeacherShare = $totalCollected - $totalDeduction;

            return [
                'teacher_id' => $teacher->id,
                'teacher_name' => $teacher->name,
                'total_students' => $totalStudents,
                'paid' => $paidCount,
                'pending' => $pendingCount,
                'collected' => $totalCollected,
                'default_share' => $totalTeacherShare,
                'deduction_percentage' => $deductionPercentage,
                'deduction_amount' => $totalDeduction,
                'teacher_share' => $totalTeacherShare
            ];
        })->filter()->values(); // Remove nulls and reindex

        return response()->json($settlements);
    }
    /**
     * Admin: Get Pending Fees for Specific Student (for Cash Payment)
     */
    public function getStudentPendingFees(Request $request, $id) {
        $fees = \App\Models\StudentFee::where('student_id', $id)
            ->where('status', 'pending')
            ->whereHas('course')
            ->with(['course.subject'])
            ->orderBy('month', 'asc')
            ->get()
            ->map(function($fee) {
                return [
                    'id' => $fee->id, // fee_id
                    'course_name' => $fee->course->name,
                    'amount' => $fee->amount,
                    'month' => $fee->month,
                    'month_label' => Carbon::createFromFormat('Y-m', $fee->month)->format('F Y'),
                    'selected' => true // Default select all
                ];
            });

        return response()->json($fees);
    }

    /**
     * Admin: Record Cash Payment (Manual)
     */
    public function recordCashPayment(Request $request) {
        $request->validate([
             'student_id' => 'required|exists:users,id',
             'amount' => 'required|numeric|min:0',
             // 'course_id' => 'nullable', // No longer strictly needed if fee_ids present
             'fee_ids' => 'nullable|array',
             'note' => 'nullable|string'
        ]);

        // Logic similar to store() but admin specific context
        $payment = Payment::create([
            'user_id' => $request->student_id,
            'course_id' => $request->course_id, // Can be null for bulk
            'amount' => $request->amount,
            'month' => $request->month ?? now()->format('Y-m'),
            'type' => 'cash',
            'paid_at' => now(),
            'status' => 'paid',
            'note' => $request->note . ' (Admin Record)'
        ]);

        if ($request->has('fee_ids')) {
             \App\Models\StudentFee::whereIn('id', $request->fee_ids)
                ->update(['status' => 'paid', 'paid_at' => now(), 'payment_id' => $payment->id]);
        }
        // Backward compatibility if just course_id provided (single month)
        elseif ($request->course_id && $request->month) {
             \App\Models\StudentFee::where('student_id', $request->student_id)
                ->where('course_id', $request->course_id)
                ->where('month', $request->month)
                ->update(['status' => 'paid', 'paid_at' => now(), 'payment_id' => $payment->id]);
        }

        // Notify Student
        \App\Models\Notification::create([
            'user_id' => $payment->user_id,
            'type' => 'payment_success',
            'title' => 'Cash Payment Recorded',
            'message' => 'Admin recorded a cash payment of LKR ' . number_format($payment->amount),
            'data' => json_encode(['payment_id' => $payment->id])
        ]);

        return response()->json(['message' => 'Payment recorded successfully', 'payment' => $payment]);
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
