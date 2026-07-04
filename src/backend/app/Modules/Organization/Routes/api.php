<?php

use App\Modules\Organization\Infrastructure\Http\Controllers\Actions\{
    ActivateBranchController,
    ActivateDepartmentController,
    ActivatePositionController,
    DeactivateBranchController,
    DeactivateDepartmentController,
    DeactivatePositionController,
    ListBranchController,
    ListDepartmentController,
    ListPositionController,
    MoveDepartmentController,
    ShowBranchController,
    ShowDepartmentController,
    ShowPositionController,
    StoreBranchController,
    StoreDepartmentController,
    StorePositionController,
    UpdateBranchController,
    UpdateDepartmentController,
    UpdatePositionController,
};
use Illuminate\Support\Facades\Route;


use App\Modules\Organization\Infrastructure\Http\Controllers\OrgTreeController;

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    Route::get('/branches', ListBranchController::class)->middleware('permission:organization.branch.list');
    Route::post('/branches', StoreBranchController::class)->middleware('permission:organization.branch.create');
    Route::get('/branches/{id}', ShowBranchController::class)->middleware('permission:organization.branch.view');
    Route::patch('/branches/{id}', UpdateBranchController::class)->middleware('permission:organization.branch.update');
    Route::post('/branches/{id}/activate', ActivateBranchController::class)->middleware('permission:organization.branch.update');
    Route::post('/branches/{id}/deactivate', DeactivateBranchController::class)->middleware('permission:organization.branch.update');

    Route::get('/departments', ListDepartmentController::class)->middleware('permission:organization.department.list');
    Route::post('/departments', StoreDepartmentController::class)->middleware('permission:organization.department.create');
    Route::get('/departments/{id}', ShowDepartmentController::class)->middleware('permission:organization.department.view');
    Route::patch('/departments/{id}', UpdateDepartmentController::class)->middleware('permission:organization.department.update');
    Route::post('/departments/{id}/move', MoveDepartmentController::class)->middleware('permission:organization.department.move');
    Route::post('/departments/{id}/activate', ActivateDepartmentController::class)->middleware('permission:organization.department.update');
    Route::post('/departments/{id}/deactivate', DeactivateDepartmentController::class)->middleware('permission:organization.department.update');

    Route::get('/positions', ListPositionController::class)->middleware('permission:organization.position.list');
    Route::post('/positions', StorePositionController::class)->middleware('permission:organization.position.create');
    Route::get('/positions/{id}', ShowPositionController::class)->middleware('permission:organization.position.view');
    Route::patch('/positions/{id}', UpdatePositionController::class)->middleware('permission:organization.position.update');
    Route::post('/positions/{id}/activate', ActivatePositionController::class)->middleware('permission:organization.position.update');
    Route::post('/positions/{id}/deactivate', DeactivatePositionController::class)->middleware('permission:organization.position.update');

    Route::get('/org-tree', OrgTreeController::class)->middleware('permission:organization.tree.view');
});
