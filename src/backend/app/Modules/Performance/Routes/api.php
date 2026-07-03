<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Performance\Infrastructure\Http\Controllers\PerformanceCycleController;
use App\Modules\Performance\Infrastructure\Http\Controllers\PerformanceReviewController;
use App\Modules\Performance\Infrastructure\Http\Controllers\GoalController;
use App\Modules\Performance\Infrastructure\Http\Controllers\CompetencyTemplateController;

Route::prefix('v1/performance')
    ->middleware(['auth:sanctum'])
    ->group(function () {
        // Cycles
        Route::get('cycles', [PerformanceCycleController::class, 'index'])->middleware('permission:performance.cycle.view');
        Route::post('cycles', [PerformanceCycleController::class, 'store'])->middleware('permission:performance.cycle.create');
        Route::get('cycles/{id}', [PerformanceCycleController::class, 'show'])->middleware('permission:performance.cycle.view');
        Route::put('cycles/{id}', [PerformanceCycleController::class, 'update'])->middleware('permission:performance.cycle.update');
        Route::post('cycles/{id}/activate', [PerformanceCycleController::class, 'activate'])->middleware('permission:performance.cycle.activate');
        Route::post('cycles/{id}/complete', [PerformanceCycleController::class, 'complete'])->middleware('permission:performance.cycle.complete');
        Route::post('cycles/{id}/cancel', [PerformanceCycleController::class, 'cancel'])->middleware('permission:performance.cycle.cancel');
        // Reviews
        Route::get('reviews', [PerformanceReviewController::class, 'index'])->middleware('permission:performance.review.view');
        Route::post('reviews', [PerformanceReviewController::class, 'store'])->middleware('permission:performance.review.create');
        Route::get('reviews/{id}', [PerformanceReviewController::class, 'show'])->middleware('permission:performance.review.view');
        Route::post('reviews/{id}/self', [PerformanceReviewController::class, 'submitSelf'])->middleware('permission:performance.review.submit_self');
        Route::post('reviews/{id}/manager', [PerformanceReviewController::class, 'submitManager'])->middleware('permission:performance.review.submit_manager');
        Route::post('reviews/{id}/hr', [PerformanceReviewController::class, 'submitHr'])->middleware('permission:performance.review.submit_hr');
        Route::post('reviews/{id}/finalize', [PerformanceReviewController::class, 'finalize'])->middleware('permission:performance.review.finalize');
        // Goals
        Route::get('goals', [GoalController::class, 'index'])->middleware('permission:performance.goal.view');
        Route::post('goals', [GoalController::class, 'store'])->middleware('permission:performance.goal.create');
        Route::get('goals/{id}', [GoalController::class, 'show'])->middleware('permission:performance.goal.view');
        Route::put('goals/{id}', [GoalController::class, 'update'])->middleware('permission:performance.goal.update');
        Route::post('goals/{id}/complete', [GoalController::class, 'complete'])->middleware('permission:performance.goal.complete');
        // Competency Templates
        Route::get('templates', [CompetencyTemplateController::class, 'index'])->middleware('permission:performance.template.view');
        Route::post('templates', [CompetencyTemplateController::class, 'store'])->middleware('permission:performance.template.create');
        Route::get('templates/{id}', [CompetencyTemplateController::class, 'show'])->middleware('permission:performance.template.view');
        Route::put('templates/{id}', [CompetencyTemplateController::class, 'update'])->middleware('permission:performance.template.update');
        Route::delete('templates/{id}', [CompetencyTemplateController::class, 'destroy'])->middleware('permission:performance.template.delete');
    });
