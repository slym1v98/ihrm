<?php

use App\Modules\Training\Infrastructure\Http\Controllers\Actions\AttendanceTrainingEnrollmentController;
use App\Modules\Training\Infrastructure\Http\Controllers\Actions\CancelTrainingEnrollmentController;
use App\Modules\Training\Infrastructure\Http\Controllers\Actions\CompleteTrainingEnrollmentController;
use App\Modules\Training\Infrastructure\Http\Controllers\Actions\DeleteTrainingCourseController;
use App\Modules\Training\Infrastructure\Http\Controllers\Actions\ListTrainingCourseController;
use App\Modules\Training\Infrastructure\Http\Controllers\Actions\ListTrainingSessionController;
use App\Modules\Training\Infrastructure\Http\Controllers\Actions\ShowTrainingCourseController;
use App\Modules\Training\Infrastructure\Http\Controllers\Actions\ShowTrainingResultController;
use App\Modules\Training\Infrastructure\Http\Controllers\Actions\ShowTrainingSessionController;
use App\Modules\Training\Infrastructure\Http\Controllers\Actions\StoreTrainingCourseController;
use App\Modules\Training\Infrastructure\Http\Controllers\Actions\StoreTrainingEnrollmentController;
use App\Modules\Training\Infrastructure\Http\Controllers\Actions\StoreTrainingResultController;
use App\Modules\Training\Infrastructure\Http\Controllers\Actions\StoreTrainingSessionController;
use App\Modules\Training\Infrastructure\Http\Controllers\Actions\UpdateTrainingCourseController;
use App\Modules\Training\Infrastructure\Http\Controllers\Actions\UpdateTrainingSessionController;
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
