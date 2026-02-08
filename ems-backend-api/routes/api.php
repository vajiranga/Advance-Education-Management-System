<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\TenantController;
use App\Http\Controllers\Api\V1\CourseController;
use App\Http\Controllers\Api\AuthController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/parent-login', [AuthController::class, 'parentLogin']);

// --- TEMPORARY TEST ROUTE REMOVED ---
// Temporary Setup Route (DISABLED FOR PRODUCTION)
// Route::get('/setup-admin', function () {
//     $user = \App\Models\User::firstOrCreate(
//         ['email' => 'admin@ems.com'],
//         [
//             'name' => 'System Admin',
//             'password' => \Illuminate\Support\Facades\Hash::make('password'),
//             'plain_password' => 'password',
//             'role' => 'admin',
//             'username' => 'ADMIN001'
//         ]
//     );
//      // Update role just in case it existed but wasn't admin
//     if ($user->role !== 'admin') {
//         $user->role = 'admin';
//         $user->save();
//     }
//     return response()->json(['message' => 'Admin Ready', 'credentials' => ['email' => 'admin@ems.com', 'password' => 'password']]);
// });

Route::prefix('v1')->group(function () {

    // Landlord / System Admin Routes
    Route::apiResource('tenants', TenantController::class);

    // Public Routes (or protected by middleware later)
    Route::get('subjects', [App\Http\Controllers\Api\V1\AcademicController::class, 'getAllSubjects']);
    Route::get('grades', [App\Http\Controllers\Api\V1\AcademicController::class, 'getGrades']);
    Route::get('grades/{gradeId}/subjects', [App\Http\Controllers\Api\V1\AcademicController::class, 'getSubjects']);
    Route::get('public/courses', [CourseController::class, 'index']);
    Route::get('settings/config', [App\Http\Controllers\Api\SystemSettingController::class, 'publicSettings']);

    // Authenticated Routes
    Route::middleware(['auth:sanctum', 'maintenance'])->group(function () {

        // --- STUDENT ROUTES (Moved to Shared due to broad access needs) ---
        // Route::middleware('role:student')->group(function () { ... });

        // --- TEACHER & ADMIN ROUTES ---
        Route::middleware('role:teacher,admin')->group(function () {
             // Course Management
             Route::apiResource('courses', CourseController::class)->except(['index', 'show']); // Teacher manages their courses
             Route::post('courses/bulk', [CourseController::class, 'bulkAction']);
             Route::get('courses/{id}/students', [CourseController::class, 'getStudents']);
             Route::put('courses/{id}/status', [CourseController::class, 'updateStatus']);

             // Batches & Halls
             Route::apiResource('batches', App\Http\Controllers\Api\V1\BatchController::class); // Assuming teacher creates batches
             // Halls should probably be admin managed, but keeping here for now if simple
             Route::apiResource('halls', App\Http\Controllers\Api\V1\HallController::class);
             Route::post('halls/check', [App\Http\Controllers\Api\V1\HallController::class, 'checkAvailability']);

             // Exams Management
             Route::apiResource('exams', App\Http\Controllers\Api\V1\ExamController::class);
             Route::get('exams/{id}/results', [App\Http\Controllers\Api\V1\ExamController::class, 'getResults']);
             Route::post('exams/{id}/results', [App\Http\Controllers\Api\V1\ExamController::class, 'storeResults']);

             // Attendance
             Route::get('teacher/students', [CourseController::class, 'getMyStudents']);
             Route::get('attendance/students', [App\Http\Controllers\Api\V1\AttendanceController::class, 'getStudents']);
             Route::post('attendance/bulk', [App\Http\Controllers\Api\V1\AttendanceController::class, 'bulkStore']);
             Route::post('attendance', [App\Http\Controllers\Api\V1\AttendanceController::class, 'store']);

             // Payments View
             Route::get('teacher/payments', [App\Http\Controllers\Api\V1\PaymentController::class, 'getTeacherStudentPayments']);

             // Notices
             Route::post('notices', [App\Http\Controllers\Api\V1\NoticeController::class, 'store']);
        });

        // --- PARENT ROUTES ---
        Route::middleware('role:parent')->group(function () {
            // Route::get('parent/children', ...); // Moved to Shared
            Route::get('parent/children/{id}/stats', [App\Http\Controllers\Api\V1\ParentController::class, 'getChildStats']);
            Route::get('parent/children/{id}/courses', [App\Http\Controllers\Api\V1\ParentController::class, 'getChildCourses']);
            Route::get('parent/children/{id}/results', [App\Http\Controllers\Api\V1\ParentController::class, 'getChildResults']);
            Route::get('parent/children/{id}/attendance', [App\Http\Controllers\Api\V1\ParentController::class, 'getChildAttendance']);
            Route::get('parent/children/{id}/notices', [App\Http\Controllers\Api\V1\ParentController::class, 'getChildNotices']);
            // Route::get('parent/fees/due', ...); // Moved to Shared
        });

        // --- ADMIN ROUTES ---
        Route::middleware('role:admin')->group(function () {
             Route::get('admin/payments/summary', [App\Http\Controllers\Api\V1\PaymentController::class, 'getAdminPaymentSummary']);
             Route::get('admin/payments/analytics', [App\Http\Controllers\Api\V1\PaymentController::class, 'getAnalytics']);
             Route::get('admin/payments/settlements', [App\Http\Controllers\Api\V1\PaymentController::class, 'getTeacherSettlements']);
             Route::get('admin/payments/export', [App\Http\Controllers\Api\V1\PaymentController::class, 'exportReport']);
             Route::post('admin/payments/generate', [App\Http\Controllers\Api\V1\PaymentController::class, 'generateMonthlyFees']);
             Route::post('admin/payments/record-cash', [App\Http\Controllers\Api\V1\PaymentController::class, 'recordCashPayment']);
             Route::get('admin/students/{id}/pending-fees', [App\Http\Controllers\Api\V1\PaymentController::class, 'getStudentPendingFees']);
             Route::get('admin/payments/pending-list', [App\Http\Controllers\Api\V1\PaymentController::class, 'getPendingFeeList']);
             Route::post('payments/{id}/approve', [App\Http\Controllers\Api\V1\PaymentController::class, 'approve']);
             Route::post('payments/{id}/reject', [App\Http\Controllers\Api\V1\PaymentController::class, 'reject']);
             Route::post('subjects', [App\Http\Controllers\Api\V1\AcademicController::class, 'createSubject']);

             // User Management
             Route::get('/users', [App\Http\Controllers\Api\UserManagementController::class, 'index']);
             Route::post('/users', [App\Http\Controllers\Api\UserManagementController::class, 'store']);
             Route::put('/users/{id}', [App\Http\Controllers\Api\UserManagementController::class, 'update']);
             Route::delete('/users/{id}', [App\Http\Controllers\Api\UserManagementController::class, 'destroy']);

             Route::get('admin/attendance/dashboard', [App\Http\Controllers\Api\V1\AttendanceController::class, 'getAdminDashboard']);
             Route::get('admin/dashboard/pending', [App\Http\Controllers\Api\V1\DashboardController::class, 'getPendingActions']);
             Route::get('admin/classes/today', [CourseController::class, 'getTodayClasses']);

             // Admin can likely access almost everything else too
             Route::post('admin/verify-password', [App\Http\Controllers\Api\AdminController::class, 'verifySuperAdmin']);
             Route::get('admin/admins', [App\Http\Controllers\Api\AdminController::class, 'index']);
             Route::post('admin/admins', [App\Http\Controllers\Api\AdminController::class, 'store']);
             Route::put('admin/admins/{id}', [App\Http\Controllers\Api\AdminController::class, 'update']);
             Route::delete('admin/admins/{id}', [App\Http\Controllers\Api\AdminController::class, 'destroy']);

             Route::get('admin/settings', [App\Http\Controllers\Api\SystemSettingController::class, 'index']);
             Route::post('admin/settings', [App\Http\Controllers\Api\SystemSettingController::class, 'update']);
        });

        // Shared Reads (Authenticated users can read these)
        Route::apiResource('courses', CourseController::class)->only(['index', 'show']);

        // Personal Payments (Available to any Auth User)
        Route::get('my-payments', [App\Http\Controllers\Api\V1\PaymentController::class, 'myPayments']);
        Route::get('my-due-fees', [App\Http\Controllers\Api\V1\PaymentController::class, 'getDueFees']);
        Route::get('parent/fees/due', [App\Http\Controllers\Api\V1\PaymentController::class, 'getParentDueFees']); // Moved here to avoid 403
        Route::get('parent/children', [App\Http\Controllers\Api\V1\ParentController::class, 'getChildren']); // Moved here to avoid 403
        Route::get('parent/children/{id}/fees', [App\Http\Controllers\Api\V1\PaymentController::class, 'getChildDueFees']);
        Route::get('parent/children/{id}/payments', [App\Http\Controllers\Api\V1\PaymentController::class, 'getChildPayments']);
        Route::post('payments', [App\Http\Controllers\Api\V1\PaymentController::class, 'store']);

        // Student Data (Available to any Auth User - Controller handles specifics)
        Route::post('enroll', [App\Http\Controllers\Api\V1\EnrollmentController::class, 'enroll']);
        Route::get('my-courses', [App\Http\Controllers\Api\V1\EnrollmentController::class, 'myCourses']);
        Route::post('courses/{id}/drop', [App\Http\Controllers\Api\V1\EnrollmentController::class, 'drop']);
        Route::get('attendance/my-history', [App\Http\Controllers\Api\V1\AttendanceController::class, 'myAttendance']);
        Route::get('attendance/dashboard', [App\Http\Controllers\Api\V1\AttendanceController::class, 'getStudentDashboard']);
        Route::get('my-exams', [App\Http\Controllers\Api\V1\ExamController::class, 'myExams']);
        // Notifications
        Route::get('/notifications', [App\Http\Controllers\Api\V1\NotificationController::class, 'index']);
        Route::post('/notifications/mark-read', [App\Http\Controllers\Api\V1\NotificationController::class, 'markAllRead']);
        Route::post('/notifications/{id}/read', [App\Http\Controllers\Api\V1\NotificationController::class, 'markAsRead']);
    });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::put('/user/profile', [AuthController::class, 'updateProfile']);
});
