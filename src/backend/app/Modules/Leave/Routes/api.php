<?php

use App\Modules\Leave\Infrastructure\Http\Controllers\Actions\ApproveLeaveRequestController;
use App\Modules\Leave\Infrastructure\Http\Controllers\Actions\CancelLeaveRequestController;
use App\Modules\Leave\Infrastructure\Http\Controllers\Actions\ListLeaveBalanceController;
use App\Modules\Leave\Infrastructure\Http\Controllers\Actions\ListLeavePolicyController;
use App\Modules\Leave\Infrastructure\Http\Controllers\Actions\ListLeaveRequestController;
use App\Modules\Leave\Infrastructure\Http\Controllers\Actions\ListLeaveTypeController;
use App\Modules\Leave\Infrastructure\Http\Controllers\Actions\RejectLeaveRequestController;
use App\Modules\Leave\Infrastructure\Http\Controllers\Actions\ShowLeaveRequestController;
use App\Modules\Leave\Infrastructure\Http\Controllers\Actions\StoreLeaveRequestController;
use App\Modules\Leave\Infrastructure\Http\Controllers\Actions\SummaryLeaveBalanceController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {

    // Leave types
    Route::get('leave-types', ListLeaveTypeController::class)
        ->middleware('permission:leave.type.view');

    // Leave policies
    Route::get('leave-policies', ListLeavePolicyController::class)
        ->middleware('permission:leave.policy.view');

    // Leave requests
    Route::post('leave-requests', StoreLeaveRequestController::class)
        ->middleware('permission:leave.request.create');
    Route::get('leave-requests', ListLeaveRequestController::class)
        ->middleware('permission:leave.request.view');
    Route::get('leave-requests/{id}', ShowLeaveRequestController::class)
        ->middleware('permission:leave.request.view');
    Route::post('leave-requests/{id}/approve', ApproveLeaveRequestController::class)
        ->middleware('permission:leave.request.approve');
    Route::post('leave-requests/{id}/reject', RejectLeaveRequestController::class)
        ->middleware('permission:leave.request.reject');
    Route::post('leave-requests/{id}/cancel', CancelLeaveRequestController::class)
        ->middleware('permission:leave.request.cancel');

    // Leave balances
    Route::get('leave-balances', ListLeaveBalanceController::class)
        ->middleware('permission:leave.balance.view');
    Route::get('leave-balances/summary', SummaryLeaveBalanceController::class)
        ->middleware('permission:leave.balance.view');
});
