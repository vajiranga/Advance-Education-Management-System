<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\TenantController;
use App\Http\Controllers\Api\V1\CourseController;

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
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
