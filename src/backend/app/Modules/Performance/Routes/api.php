<?php

use App\Modules\Performance\Infrastructure\Http\Controllers\Actions\{
    ActivatePerformanceCycleController,
    CancelPerformanceCycleController,
    CompleteGoalController,
    CompletePerformanceCycleController,
    DeleteCompetencyTemplateController,
    FinalizePerformanceReviewController,
    ListCompetencyTemplateController,
    ListGoalController,
    ListPerformanceCycleController,
    ListPerformanceReviewController,
    ShowCompetencyTemplateController,
    ShowGoalController,
    ShowPerformanceCycleController,
    ShowPerformanceReviewController,
    StoreCompetencyTemplateController,
    StoreGoalController,
    StorePerformanceCycleController,
    StorePerformanceReviewController,
    SubmitHrPerformanceReviewController,
    SubmitManagerPerformanceReviewController,
    SubmitSelfPerformanceReviewController,
    UpdateCompetencyTemplateController,
    UpdateGoalController,
    UpdatePerformanceCycleController,
};
use Illuminate\Support\Facades\Route;



Route::prefix('v1/performance')
    ->middleware(['auth:sanctum'])
    ->group(function () {
        // Cycles
        Route::get('cycles', ListPerformanceCycleController::class)->middleware('permission:performance.cycle.view');
        Route::post('cycles', StorePerformanceCycleController::class)->middleware('permission:performance.cycle.create');
        Route::get('cycles/{id}', ShowPerformanceCycleController::class)->middleware('permission:performance.cycle.view');
        Route::put('cycles/{id}', UpdatePerformanceCycleController::class)->middleware('permission:performance.cycle.update');
        Route::post('cycles/{id}/activate', ActivatePerformanceCycleController::class)->middleware('permission:performance.cycle.activate');
        Route::post('cycles/{id}/complete', CompletePerformanceCycleController::class)->middleware('permission:performance.cycle.complete');
        Route::post('cycles/{id}/cancel', CancelPerformanceCycleController::class)->middleware('permission:performance.cycle.cancel');
        // Reviews
        Route::get('reviews', ListPerformanceReviewController::class)->middleware('permission:performance.review.view');
        Route::post('reviews', StorePerformanceReviewController::class)->middleware('permission:performance.review.create');
        Route::get('reviews/{id}', ShowPerformanceReviewController::class)->middleware('permission:performance.review.view');
        Route::post('reviews/{id}/self', SubmitSelfPerformanceReviewController::class)->middleware('permission:performance.review.submit_self');
        Route::post('reviews/{id}/manager', SubmitManagerPerformanceReviewController::class)->middleware('permission:performance.review.submit_manager');
        Route::post('reviews/{id}/hr', SubmitHrPerformanceReviewController::class)->middleware('permission:performance.review.submit_hr');
        Route::post('reviews/{id}/finalize', FinalizePerformanceReviewController::class)->middleware('permission:performance.review.finalize');
        // Goals
        Route::get('goals', ListGoalController::class)->middleware('permission:performance.goal.view');
        Route::post('goals', StoreGoalController::class)->middleware('permission:performance.goal.create');
        Route::get('goals/{id}', ShowGoalController::class)->middleware('permission:performance.goal.view');
        Route::put('goals/{id}', UpdateGoalController::class)->middleware('permission:performance.goal.update');
        Route::post('goals/{id}/complete', CompleteGoalController::class)->middleware('permission:performance.goal.complete');
        // Competency Templates
        Route::get('templates', ListCompetencyTemplateController::class)->middleware('permission:performance.template.view');
        Route::post('templates', StoreCompetencyTemplateController::class)->middleware('permission:performance.template.create');
        Route::get('templates/{id}', ShowCompetencyTemplateController::class)->middleware('permission:performance.template.view');
        Route::put('templates/{id}', UpdateCompetencyTemplateController::class)->middleware('permission:performance.template.update');
        Route::delete('templates/{id}', DeleteCompetencyTemplateController::class)->middleware('permission:performance.template.delete');
    });
