<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\TenantController;
use App\Http\Controllers\Api\V1\CourseController;
use App\Http\Controllers\Api\AuthController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/parent-login', [AuthController::class, 'parentLogin']);
Route::get('/users', [App\Http\Controllers\Api\UserManagementController::class, 'index']);
Route::post('/users', [App\Http\Controllers\Api\UserManagementController::class, 'store']);
Route::put('/users/{id}', [App\Http\Controllers\Api\UserManagementController::class, 'update']);
Route::delete('/users/{id}', [App\Http\Controllers\Api\UserManagementController::class, 'destroy']);

Route::prefix('v1')->group(function () {
    
    // Landlord / System Admin Routes
    Route::apiResource('tenants', TenantController::class);

    // Tenant / Institute Routes 
    // (Note: In production, these should be inside the tenant.php routes file and protected by tenancy middleware)
    Route::post('courses/bulk', [CourseController::class, 'bulkAction']);
    Route::apiResource('courses', CourseController::class);
    Route::put('courses/{id}/status', [CourseController::class, 'updateStatus']);
    Route::apiResource('batches', App\Http\Controllers\Api\V1\BatchController::class);
    Route::get('subjects', [App\Http\Controllers\Api\V1\AcademicController::class, 'getAllSubjects']);
    Route::apiResource('halls', App\Http\Controllers\Api\V1\HallController::class);
    Route::post('halls/check', [App\Http\Controllers\Api\V1\HallController::class, 'checkAvailability']);

    // Academic Routes (Grades & Subjects)
    Route::get('grades', [App\Http\Controllers\Api\V1\AcademicController::class, 'getGrades']);
    Route::post('subjects', [App\Http\Controllers\Api\V1\AcademicController::class, 'createSubject']);
    Route::get('grades/{gradeId}/subjects', [App\Http\Controllers\Api\V1\AcademicController::class, 'getSubjects']);
    // Student Enrollment Routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('enroll', [App\Http\Controllers\Api\V1\EnrollmentController::class, 'enroll']);
        Route::get('my-courses', [App\Http\Controllers\Api\V1\EnrollmentController::class, 'myCourses']);
        Route::post('courses/{id}/drop', [App\Http\Controllers\Api\V1\EnrollmentController::class, 'drop']);
        
        // Attendance
        Route::get('attendance/students', [App\Http\Controllers\Api\V1\AttendanceController::class, 'getStudents']);
        Route::post('attendance', [App\Http\Controllers\Api\V1\AttendanceController::class, 'store']);
        Route::get('attendance/my-history', [App\Http\Controllers\Api\V1\AttendanceController::class, 'myAttendance']);
    });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::put('/user/profile', [AuthController::class, 'updateProfile']);
    Route::get('/parent/children', [App\Http\Controllers\Api\ParentController::class, 'getChildren']);
});
