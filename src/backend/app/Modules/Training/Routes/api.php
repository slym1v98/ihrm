<?php

use App\Modules\Training\Infrastructure\Http\Controllers\Actions\{
    AttendanceTrainingEnrollmentController,
    CancelTrainingEnrollmentController,
    CompleteTrainingEnrollmentController,
    DeleteTrainingCourseController,
    ListTrainingCourseController,
    ListTrainingSessionController,
    ShowTrainingCourseController,
    ShowTrainingResultController,
    ShowTrainingSessionController,
    StoreTrainingCourseController,
    StoreTrainingEnrollmentController,
    StoreTrainingResultController,
    StoreTrainingSessionController,
    UpdateTrainingCourseController,
    UpdateTrainingSessionController,
};
use Illuminate\Support\Facades\Route;

Route::prefix('v1/training')->middleware(['auth:sanctum'])->group(function () {
    Route::get('courses', ListTrainingCourseController::class)->middleware('permission:training.course.view');
    Route::post('courses', StoreTrainingCourseController::class)->middleware('permission:training.course.create');
    Route::get('courses/{id}', ShowTrainingCourseController::class)->middleware('permission:training.course.view');
    Route::put('courses/{id}', UpdateTrainingCourseController::class)->middleware('permission:training.course.update');
    Route::delete('courses/{id}', DeleteTrainingCourseController::class)->middleware('permission:training.course.delete');
    Route::get('courses/{courseId}/sessions', ListTrainingSessionController::class)->middleware('permission:training.session.view');
    Route::post('courses/{courseId}/sessions', StoreTrainingSessionController::class)->middleware('permission:training.session.create');
    Route::get('sessions/{id}', ShowTrainingSessionController::class)->middleware('permission:training.session.view');
    Route::put('sessions/{id}', UpdateTrainingSessionController::class)->middleware('permission:training.session.update');
    Route::post('sessions/{id}/enroll', StoreTrainingEnrollmentController::class)->middleware('permission:training.enrollment.create');
    Route::post('enrollments/{id}/cancel', CancelTrainingEnrollmentController::class)->middleware('permission:training.enrollment.cancel');
    Route::post('enrollments/{id}/attendance', AttendanceTrainingEnrollmentController::class)->middleware('permission:training.enrollment.create');
    Route::post('enrollments/{id}/complete', CompleteTrainingEnrollmentController::class)->middleware('permission:training.enrollment.create');
    Route::post('enrollments/{id}/result', StoreTrainingResultController::class)->middleware('permission:training.result.create');
    Route::get('results/{id}', ShowTrainingResultController::class)->middleware('permission:training.result.view');
});
