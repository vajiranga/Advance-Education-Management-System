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
            'fee_id' => 'required|exists:student_fees,id', // Pay specific fee
            'amount' => 'required|numeric|min:0',
            'type' => 'required|in:cash,bank_transfer,online,card',
            'note' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // 1. Find the Fee Record
        $fee = \App\Models\StudentFee::findOrFail($request->fee_id);

        if ($fee->status === 'paid') {
            return response()->json(['message' => 'Fee already paid'], 400);
        }

        // 2. Create Payment Record (Transaction History)
        $payment = Payment::create([
            'user_id' => $fee->student_id, // Student who owes the fee
            'course_id' => $fee->course_id,
            'amount' => $request->amount,
            'month' => $fee->month,
            'type' => $request->type,
            'paid_at' => now(),
            'status' => 'paid', // Assuming instant success for now
            'note' => $request->note
        ]);

        // 3. Update Fee Status
        $fee->update([
            'status' => 'paid',
            'paid_at' => now(),
            'payment_method' => $request->type,
            'transaction_ref' => $payment->id // Link to payment table
        ]);
        
        // 4. Reactivate Student if Inactive
        $student = \App\Models\User::find($fee->student_id);
        if ($student && $student->status === 'inactive') {
            $student->status = 'active';
            $student->save();
        }

        return response()->json(['message' => 'Payment successful', 'payment' => $payment], 201);
    }

    /**
     * Parent: Get Pending Fees for All Children
     */
    public function getParentDueFees(Request $request)
    {
        $user = $request->user();
        
        // Find children
        $childrenIds = \App\Models\User::where('parent_id', $user->id)->pluck('id');
        
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
         // Logic to filter by status, student_status, etc.
         // This can be expanded based on specific admin panel needs.
         
         $query = \App\Models\StudentFee::query()->with(['student', 'course']);
         
         if ($request->has('status')) {
             $query->where('status', $request->status);
         }
         
         if ($request->has('student_status')) {
             $query->whereHas('student', function($q) use ($request) {
                 $q->where('status', $request->student_status);
             });
         }
         
         return response()->json($query->paginate(20));
    }
}
