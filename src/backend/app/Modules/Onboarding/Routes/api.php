<?php

use App\Modules\Onboarding\Infrastructure\Http\Controllers\Actions\ActivateOnboardingPlanController;
use App\Modules\Onboarding\Infrastructure\Http\Controllers\Actions\CancelOnboardingPlanController;
use App\Modules\Onboarding\Infrastructure\Http\Controllers\Actions\CompleteOnboardingPlanController;
use App\Modules\Onboarding\Infrastructure\Http\Controllers\Actions\CompleteOnboardingTaskController;
use App\Modules\Onboarding\Infrastructure\Http\Controllers\Actions\DeleteOnboardingTemplateController;
use App\Modules\Onboarding\Infrastructure\Http\Controllers\Actions\ListOnboardingPlanController;
use App\Modules\Onboarding\Infrastructure\Http\Controllers\Actions\ListOnboardingTaskController;
use App\Modules\Onboarding\Infrastructure\Http\Controllers\Actions\ListOnboardingTemplateController;
use App\Modules\Onboarding\Infrastructure\Http\Controllers\Actions\ShowOnboardingPlanController;
use App\Modules\Onboarding\Infrastructure\Http\Controllers\Actions\ShowOnboardingTaskController;
use App\Modules\Onboarding\Infrastructure\Http\Controllers\Actions\ShowOnboardingTemplateController;
use App\Modules\Onboarding\Infrastructure\Http\Controllers\Actions\StartOnboardingTaskController;
use App\Modules\Onboarding\Infrastructure\Http\Controllers\Actions\StoreOnboardingPlanController;
use App\Modules\Onboarding\Infrastructure\Http\Controllers\Actions\StoreOnboardingTaskController;
use App\Modules\Onboarding\Infrastructure\Http\Controllers\Actions\StoreOnboardingTemplateController;
use App\Modules\Onboarding\Infrastructure\Http\Controllers\Actions\UpdateOnboardingTaskController;
use App\Modules\Onboarding\Infrastructure\Http\Controllers\Actions\UpdateOnboardingTemplateController;
use App\Modules\Onboarding\Infrastructure\Http\Controllers\Actions\WaiveOnboardingTaskController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/onboarding')
    ->middleware(['auth:sanctum'])
    ->group(function () {

        Route::get('templates', ListOnboardingTemplateController::class)
            ->middleware('permission:onboarding.template.view');
        Route::post('templates', StoreOnboardingTemplateController::class)
            ->middleware('permission:onboarding.template.create');
        Route::get('templates/{id}', ShowOnboardingTemplateController::class)
            ->middleware('permission:onboarding.template.view');
        Route::patch('templates/{id}', UpdateOnboardingTemplateController::class)
            ->middleware('permission:onboarding.template.update');
        Route::delete('templates/{id}', DeleteOnboardingTemplateController::class)
            ->middleware('permission:onboarding.template.delete');

        Route::get('plans', ListOnboardingPlanController::class)
            ->middleware('permission:onboarding.plan.view');
        Route::post('plans', StoreOnboardingPlanController::class)
            ->middleware('permission:onboarding.plan.create');
        Route::get('plans/{id}', ShowOnboardingPlanController::class)
            ->middleware('permission:onboarding.plan.view');
        Route::post('plans/{id}/activate', ActivateOnboardingPlanController::class)
            ->middleware('permission:onboarding.plan.activate');
        Route::post('plans/{id}/cancel', CancelOnboardingPlanController::class)
            ->middleware('permission:onboarding.plan.cancel');
        Route::post('plans/{id}/complete', CompleteOnboardingPlanController::class)
            ->middleware('permission:onboarding.plan.complete');

        Route::get('plans/{planId}/tasks', ListOnboardingTaskController::class)
            ->middleware('permission:onboarding.task.view');
        Route::post('plans/{planId}/tasks', StoreOnboardingTaskController::class)
            ->middleware('permission:onboarding.task.create');
        Route::get('tasks/{id}', ShowOnboardingTaskController::class)
            ->middleware('permission:onboarding.task.view');
        Route::patch('tasks/{id}', UpdateOnboardingTaskController::class)
            ->middleware('permission:onboarding.task.update');
        Route::post('tasks/{id}/start', StartOnboardingTaskController::class)
            ->middleware('permission:onboarding.task.start');
        Route::post('tasks/{id}/complete', CompleteOnboardingTaskController::class)
            ->middleware('permission:onboarding.task.complete');
        Route::post('tasks/{id}/waive', WaiveOnboardingTaskController::class)
            ->middleware('permission:onboarding.task.waive');
    });
