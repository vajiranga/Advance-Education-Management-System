<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\TenantController;
use App\Http\Controllers\Api\V1\CourseController;
use App\Http\Controllers\Api\AuthController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/users', [App\Http\Controllers\Api\UserManagementController::class, 'index']); // Protected by auth if needed, but keeping open for dev/admin portal access for now or add middleware later

Route::prefix('v1')->group(function () {
    
    // Landlord / System Admin Routes
    Route::apiResource('tenants', TenantController::class);

    // Tenant / Institute Routes 
    // (Note: In production, these should be inside the tenant.php routes file and protected by tenancy middleware)
    Route::apiResource('courses', CourseController::class);

    // Academic Routes (Grades & Subjects)
    Route::get('grades', [App\Http\Controllers\Api\V1\AcademicController::class, 'getGrades']);
    Route::post('subjects', [App\Http\Controllers\Api\V1\AcademicController::class, 'createSubject']);
    Route::get('grades/{gradeId}/subjects', [App\Http\Controllers\Api\V1\AcademicController::class, 'getSubjects']);
    // Student Enrollment Routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('enroll', [App\Http\Controllers\Api\V1\EnrollmentController::class, 'enroll']);
        Route::get('my-courses', [App\Http\Controllers\Api\V1\EnrollmentController::class, 'myCourses']);
        Route::post('courses/{id}/drop', [App\Http\Controllers\Api\V1\EnrollmentController::class, 'drop']);
    });
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
