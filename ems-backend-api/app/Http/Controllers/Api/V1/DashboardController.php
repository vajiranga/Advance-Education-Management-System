<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function getPendingActions()
    {
        try {
            $pendingClasses = Course::where('status', 'pending')->count();
            $pendingPayments = Payment::where('status', 'pending')->count();
            // $pendingTeachers = User::where('role', 'teacher')->where('status', 'pending')->count(); // Future use

            return response()->json([
                'pending_classes' => $pendingClasses,
                'pending_payments' => $pendingPayments,
                // 'pending_teachers' => $pendingTeachers,
                'total_pending' => $pendingClasses + $pendingPayments
            ]);
        } catch (\Exception $e) {
            Log::error('Dashboard Pending Actions Error: ' . $e->getMessage());
            return response()->json(['message' => 'Error fetching pending actions'], 500);
        }
    }
}
