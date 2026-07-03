<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Offboarding\Infrastructure\Http\Controllers\OffboardingRequestController;
use App\Modules\Offboarding\Infrastructure\Http\Controllers\OffboardingPlanController;
use App\Modules\Offboarding\Infrastructure\Http\Controllers\OffboardingTaskController;

Route::prefix('v1/offboarding')
    ->middleware(['auth:sanctum'])
    ->group(function () {
        Route::get('requests', [OffboardingRequestController::class, 'index'])->middleware('permission:offboarding.request.view');
        Route::post('requests', [OffboardingRequestController::class, 'store'])->middleware('permission:offboarding.request.create');
        Route::get('requests/{id}', [OffboardingRequestController::class, 'show'])->middleware('permission:offboarding.request.view');
        Route::post('requests/{id}/submit', [OffboardingRequestController::class, 'submit'])->middleware('permission:offboarding.request.submit');
        Route::post('requests/{id}/approve', [OffboardingRequestController::class, 'approve'])->middleware('permission:offboarding.request.approve');
        Route::post('requests/{id}/reject', [OffboardingRequestController::class, 'reject'])->middleware('permission:offboarding.request.reject');
        Route::get('plans', [OffboardingPlanController::class, 'index'])->middleware('permission:offboarding.plan.view');
        Route::post('plans', [OffboardingPlanController::class, 'store'])->middleware('permission:offboarding.plan.create');
        Route::get('plans/{id}', [OffboardingPlanController::class, 'show'])->middleware('permission:offboarding.plan.view');
        Route::post('plans/{id}/activate', [OffboardingPlanController::class, 'activate'])->middleware('permission:offboarding.plan.activate');
        Route::post('plans/{id}/complete', [OffboardingPlanController::class, 'complete'])->middleware('permission:offboarding.plan.complete');
        Route::get('plans/{planId}/tasks', [OffboardingTaskController::class, 'index'])->middleware('permission:offboarding.task.view');
        Route::post('plans/{planId}/tasks', [OffboardingTaskController::class, 'store'])->middleware('permission:offboarding.task.create');
        Route::post('tasks/{id}/start', [OffboardingTaskController::class, 'start'])->middleware('permission:offboarding.task.start');
        Route::post('tasks/{id}/complete', [OffboardingTaskController::class, 'complete'])->middleware('permission:offboarding.task.complete');
        Route::post('tasks/{id}/waive', [OffboardingTaskController::class, 'waive'])->middleware('permission:offboarding.task.waive');
        Route::post('plans/{id}/final-clearance', [OffboardingPlanController::class, 'finalClearance'])->middleware('permission:offboarding.clearance.complete');
    });
