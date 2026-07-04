<?php

use App\Modules\Asset\Infrastructure\Http\Controllers\Actions\{
    DeleteAssetItemController,
    ListAssetAssignmentController,
    ListAssetItemController,
    MarkAvailableAssetItemController,
    MarkDamagedAssetItemController,
    MarkLostAssetItemController,
    MarkMaintenanceAssetItemController,
    ReturnAssetAssetAssignmentController,
    ShowAssetAssignmentController,
    ShowAssetItemController,
    StoreAssetAssignmentController,
    StoreAssetItemController,
    UpdateAssetItemController,
};
use Illuminate\Support\Facades\Route;

use App\Modules\Asset\Infrastructure\Http\Controllers\AssetObligationController;

Route::prefix('v1/assets')->middleware(['auth:sanctum'])->group(function () {
    Route::get('items', ListAssetItemController::class)->middleware('permission:asset.item.view');
    Route::post('items', StoreAssetItemController::class)->middleware('permission:asset.item.create');
    Route::get('items/{id}', ShowAssetItemController::class)->middleware('permission:asset.item.view');
    Route::put('items/{id}', UpdateAssetItemController::class)->middleware('permission:asset.item.update');
    Route::delete('items/{id}', DeleteAssetItemController::class)->middleware('permission:asset.item.delete');

    Route::post('items/{id}/mark-available', MarkAvailableAssetItemController::class)->middleware('permission:asset.item.mark-status');
    Route::post('items/{id}/mark-maintenance', MarkMaintenanceAssetItemController::class)->middleware('permission:asset.item.mark-status');
    Route::post('items/{id}/mark-lost', MarkLostAssetItemController::class)->middleware('permission:asset.item.mark-status');
    Route::post('items/{id}/mark-damaged', MarkDamagedAssetItemController::class)->middleware('permission:asset.item.mark-status');

    Route::get('assignments', ListAssetAssignmentController::class)->middleware('permission:asset.assignment.view');
    Route::post('assignments', StoreAssetAssignmentController::class)->middleware('permission:asset.assignment.create');
    Route::get('assignments/{id}', ShowAssetAssignmentController::class)->middleware('permission:asset.assignment.view');
    Route::post('assignments/{id}/return', ReturnAssetAssetAssignmentController::class)->middleware('permission:asset.assignment.return');

    Route::get('employees/{employeeId}/obligations', AssetObligationController::class)->middleware('permission:asset.obligation.view');
});
