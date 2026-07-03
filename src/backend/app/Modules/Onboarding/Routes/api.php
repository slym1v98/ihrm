<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Onboarding\Infrastructure\Http\Controllers\OnboardingTemplateController;
use App\Modules\Onboarding\Infrastructure\Http\Controllers\OnboardingPlanController;
use App\Modules\Onboarding\Infrastructure\Http\Controllers\OnboardingTaskController;

Route::prefix('v1/onboarding')
    ->middleware(['auth:sanctum'])
    ->group(function () {

        Route::get('templates', [OnboardingTemplateController::class, 'index'])
            ->middleware('permission:onboarding.template.view');
        Route::post('templates', [OnboardingTemplateController::class, 'store'])
            ->middleware('permission:onboarding.template.create');
        Route::get('templates/{id}', [OnboardingTemplateController::class, 'show'])
            ->middleware('permission:onboarding.template.view');
        Route::patch('templates/{id}', [OnboardingTemplateController::class, 'update'])
            ->middleware('permission:onboarding.template.update');
        Route::delete('templates/{id}', [OnboardingTemplateController::class, 'destroy'])
            ->middleware('permission:onboarding.template.delete');

        Route::get('plans', [OnboardingPlanController::class, 'index'])
            ->middleware('permission:onboarding.plan.view');
        Route::post('plans', [OnboardingPlanController::class, 'store'])
            ->middleware('permission:onboarding.plan.create');
        Route::get('plans/{id}', [OnboardingPlanController::class, 'show'])
            ->middleware('permission:onboarding.plan.view');
        Route::post('plans/{id}/activate', [OnboardingPlanController::class, 'activate'])
            ->middleware('permission:onboarding.plan.activate');
        Route::post('plans/{id}/cancel', [OnboardingPlanController::class, 'cancel'])
            ->middleware('permission:onboarding.plan.cancel');
        Route::post('plans/{id}/complete', [OnboardingPlanController::class, 'complete'])
            ->middleware('permission:onboarding.plan.complete');

        Route::get('plans/{planId}/tasks', [OnboardingTaskController::class, 'index'])
            ->middleware('permission:onboarding.task.view');
        Route::post('plans/{planId}/tasks', [OnboardingTaskController::class, 'store'])
            ->middleware('permission:onboarding.task.create');
        Route::get('tasks/{id}', [OnboardingTaskController::class, 'show'])
            ->middleware('permission:onboarding.task.view');
        Route::patch('tasks/{id}', [OnboardingTaskController::class, 'update'])
            ->middleware('permission:onboarding.task.update');
        Route::post('tasks/{id}/start', [OnboardingTaskController::class, 'start'])
            ->middleware('permission:onboarding.task.start');
        Route::post('tasks/{id}/complete', [OnboardingTaskController::class, 'complete'])
            ->middleware('permission:onboarding.task.complete');
        Route::post('tasks/{id}/waive', [OnboardingTaskController::class, 'waive'])
            ->middleware('permission:onboarding.task.waive');
    });
