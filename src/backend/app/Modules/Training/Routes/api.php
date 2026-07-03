<?php
use Illuminate\Support\Facades\Route;
use App\Modules\Training\Infrastructure\Http\Controllers\TrainingCourseController;
use App\Modules\Training\Infrastructure\Http\Controllers\TrainingSessionController;
use App\Modules\Training\Infrastructure\Http\Controllers\TrainingEnrollmentController;
use App\Modules\Training\Infrastructure\Http\Controllers\TrainingResultController;
Route::prefix('v1/training')->middleware(['auth:sanctum'])->group(function () {
    Route::get('courses', [TrainingCourseController::class, 'index'])->middleware('permission:training.course.view');
    Route::post('courses', [TrainingCourseController::class, 'store'])->middleware('permission:training.course.create');
    Route::get('courses/{id}', [TrainingCourseController::class, 'show'])->middleware('permission:training.course.view');
    Route::put('courses/{id}', [TrainingCourseController::class, 'update'])->middleware('permission:training.course.update');
    Route::delete('courses/{id}', [TrainingCourseController::class, 'destroy'])->middleware('permission:training.course.delete');
    Route::get('courses/{courseId}/sessions', [TrainingSessionController::class, 'index'])->middleware('permission:training.session.view');
    Route::post('courses/{courseId}/sessions', [TrainingSessionController::class, 'store'])->middleware('permission:training.session.create');
    Route::get('sessions/{id}', [TrainingSessionController::class, 'show'])->middleware('permission:training.session.view');
    Route::put('sessions/{id}', [TrainingSessionController::class, 'update'])->middleware('permission:training.session.update');
    Route::post('sessions/{id}/enroll', [TrainingEnrollmentController::class, 'store'])->middleware('permission:training.enrollment.create');
    Route::post('enrollments/{id}/cancel', [TrainingEnrollmentController::class, 'cancel'])->middleware('permission:training.enrollment.cancel');
    Route::post('enrollments/{id}/attendance', [TrainingEnrollmentController::class, 'attendance'])->middleware('permission:training.enrollment.create');
    Route::post('enrollments/{id}/complete', [TrainingEnrollmentController::class, 'complete'])->middleware('permission:training.enrollment.create');
    Route::post('enrollments/{id}/result', [TrainingResultController::class, 'store'])->middleware('permission:training.result.create');
    Route::get('results/{id}', [TrainingResultController::class, 'show'])->middleware('permission:training.result.view');
});
