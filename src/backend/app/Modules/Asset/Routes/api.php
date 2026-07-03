<?php
use Illuminate\Support\Facades\Route;
use App\Modules\Asset\Infrastructure\Http\Controllers\AssetItemController;
use App\Modules\Asset\Infrastructure\Http\Controllers\AssetAssignmentController;
use App\Modules\Asset\Infrastructure\Http\Controllers\AssetObligationController;

Route::prefix('v1/assets')->middleware(['auth:sanctum'])->group(function () {
    Route::get('items', [AssetItemController::class, 'index'])->middleware('permission:asset.item.view');
    Route::post('items', [AssetItemController::class, 'store'])->middleware('permission:asset.item.create');
    Route::get('items/{id}', [AssetItemController::class, 'show'])->middleware('permission:asset.item.view');
    Route::put('items/{id}', [AssetItemController::class, 'update'])->middleware('permission:asset.item.update');
    Route::delete('items/{id}', [AssetItemController::class, 'destroy'])->middleware('permission:asset.item.delete');

    Route::post('items/{id}/mark-available', [AssetItemController::class, 'markAvailable'])->middleware('permission:asset.item.mark-status');
    Route::post('items/{id}/mark-maintenance', [AssetItemController::class, 'markMaintenance'])->middleware('permission:asset.item.mark-status');
    Route::post('items/{id}/mark-lost', [AssetItemController::class, 'markLost'])->middleware('permission:asset.item.mark-status');
    Route::post('items/{id}/mark-damaged', [AssetItemController::class, 'markDamaged'])->middleware('permission:asset.item.mark-status');

    Route::get('assignments', [AssetAssignmentController::class, 'index'])->middleware('permission:asset.assignment.view');
    Route::post('assignments', [AssetAssignmentController::class, 'store'])->middleware('permission:asset.assignment.create');
    Route::get('assignments/{id}', [AssetAssignmentController::class, 'show'])->middleware('permission:asset.assignment.view');
    Route::post('assignments/{id}/return', [AssetAssignmentController::class, 'returnAsset'])->middleware('permission:asset.assignment.return');

    Route::get('employees/{employeeId}/obligations', AssetObligationController::class)->middleware('permission:asset.obligation.view');
});
