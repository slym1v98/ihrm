<?php

use App\Modules\Workflow\Infrastructure\Http\Controllers\Actions\{
    ApproveWorkflowRequestController,
    CancelWorkflowRequestController,
    DeleteWorkflowDelegationController,
    ListWorkflowRequestController,
    ListWorkflowDelegationController,
    ListWorkflowTemplateController,
    RejectWorkflowRequestController,
    ReturnForEditWorkflowRequestController,
    ShowWorkflowRequestController,
    ShowWorkflowTemplateController,
    StoreWorkflowDelegationController,
    StoreWorkflowRequestController,
    StoreWorkflowTemplateController,
};
use Illuminate\Support\Facades\Route;



Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    Route::post('workflow-templates', StoreWorkflowTemplateController::class)->middleware('permission:workflow.template.create');
    Route::get('workflow-templates', ListWorkflowTemplateController::class)->middleware('permission:workflow.template.view');
    Route::get('workflow-templates/{id}', ShowWorkflowTemplateController::class)->middleware('permission:workflow.template.view');

    Route::post('workflow-requests', StoreWorkflowRequestController::class)->middleware('permission:workflow.request.start');
    Route::get('workflow-requests', ListWorkflowRequestController::class)->middleware('permission:workflow.request.view');
    Route::get('workflow-requests/{id}', ShowWorkflowRequestController::class)->middleware('permission:workflow.request.view');
    Route::post('workflow-requests/{id}/approve', ApproveWorkflowRequestController::class)->middleware('permission:workflow.request.approve');
    Route::post('workflow-requests/{id}/reject', RejectWorkflowRequestController::class)->middleware('permission:workflow.request.reject');
    Route::post('workflow-requests/{id}/return-for-edit', ReturnForEditWorkflowRequestController::class)->middleware('permission:workflow.request.return');
    Route::post('workflow-requests/{id}/cancel', CancelWorkflowRequestController::class)->middleware('permission:workflow.request.cancel');

    Route::post('workflow-delegations', StoreWorkflowDelegationController::class)->middleware('permission:workflow.delegation.create');
    Route::get('workflow-delegations', ListWorkflowDelegationController::class)->middleware('permission:workflow.delegation.view');
    Route::delete('workflow-delegations/{id}', DeleteWorkflowDelegationController::class)->middleware('permission:workflow.delegation.delete');
});
