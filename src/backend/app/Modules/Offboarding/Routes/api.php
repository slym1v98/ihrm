<?php

use App\Modules\Offboarding\Infrastructure\Http\Controllers\Actions\{
    ActivateOffboardingPlanController,
    ApproveOffboardingRequestController,
    CompleteOffboardingPlanController,
    CompleteOffboardingTaskController,
    FinalClearanceOffboardingPlanController,
    ListOffboardingPlanController,
    ListOffboardingRequestController,
    ListOffboardingTaskController,
    RejectOffboardingRequestController,
    ShowOffboardingPlanController,
    ShowOffboardingRequestController,
    StartOffboardingTaskController,
    StoreOffboardingPlanController,
    StoreOffboardingRequestController,
    StoreOffboardingTaskController,
    SubmitOffboardingRequestController,
    WaiveOffboardingTaskController,
};
use Illuminate\Support\Facades\Route;



Route::prefix('v1/offboarding')
    ->middleware(['auth:sanctum'])
    ->group(function () {
        Route::get('requests', ListOffboardingRequestController::class)->middleware('permission:offboarding.request.view');
        Route::post('requests', StoreOffboardingRequestController::class)->middleware('permission:offboarding.request.create');
        Route::get('requests/{id}', ShowOffboardingRequestController::class)->middleware('permission:offboarding.request.view');
        Route::post('requests/{id}/submit', SubmitOffboardingRequestController::class)->middleware('permission:offboarding.request.submit');
        Route::post('requests/{id}/approve', ApproveOffboardingRequestController::class)->middleware('permission:offboarding.request.approve');
        Route::post('requests/{id}/reject', RejectOffboardingRequestController::class)->middleware('permission:offboarding.request.reject');
        Route::get('plans', ListOffboardingPlanController::class)->middleware('permission:offboarding.plan.view');
        Route::post('plans', StoreOffboardingPlanController::class)->middleware('permission:offboarding.plan.create');
        Route::get('plans/{id}', ShowOffboardingPlanController::class)->middleware('permission:offboarding.plan.view');
        Route::post('plans/{id}/activate', ActivateOffboardingPlanController::class)->middleware('permission:offboarding.plan.activate');
        Route::post('plans/{id}/complete', CompleteOffboardingPlanController::class)->middleware('permission:offboarding.plan.complete');
        Route::get('plans/{planId}/tasks', ListOffboardingTaskController::class)->middleware('permission:offboarding.task.view');
        Route::post('plans/{planId}/tasks', StoreOffboardingTaskController::class)->middleware('permission:offboarding.task.create');
        Route::post('tasks/{id}/start', StartOffboardingTaskController::class)->middleware('permission:offboarding.task.start');
        Route::post('tasks/{id}/complete', CompleteOffboardingTaskController::class)->middleware('permission:offboarding.task.complete');
        Route::post('tasks/{id}/waive', WaiveOffboardingTaskController::class)->middleware('permission:offboarding.task.waive');
        Route::post('plans/{id}/final-clearance', FinalClearanceOffboardingPlanController::class)->middleware('permission:offboarding.clearance.complete');
    });
