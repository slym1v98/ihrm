<?php

use App\Modules\Organization\Infrastructure\Http\Controllers\Actions\ActivateBranchController;
use App\Modules\Organization\Infrastructure\Http\Controllers\Actions\ActivateDepartmentController;
use App\Modules\Organization\Infrastructure\Http\Controllers\Actions\ActivatePositionController;
use App\Modules\Organization\Infrastructure\Http\Controllers\Actions\DeactivateBranchController;
use App\Modules\Organization\Infrastructure\Http\Controllers\Actions\DeactivateDepartmentController;
use App\Modules\Organization\Infrastructure\Http\Controllers\Actions\DeactivatePositionController;
use App\Modules\Organization\Infrastructure\Http\Controllers\Actions\ListBranchController;
use App\Modules\Organization\Infrastructure\Http\Controllers\Actions\ListDepartmentController;
use App\Modules\Organization\Infrastructure\Http\Controllers\Actions\ListPositionController;
use App\Modules\Organization\Infrastructure\Http\Controllers\Actions\MoveDepartmentController;
use App\Modules\Organization\Infrastructure\Http\Controllers\Actions\ShowBranchController;
use App\Modules\Organization\Infrastructure\Http\Controllers\Actions\ShowDepartmentController;
use App\Modules\Organization\Infrastructure\Http\Controllers\Actions\ShowPositionController;
use App\Modules\Organization\Infrastructure\Http\Controllers\Actions\StoreBranchController;
use App\Modules\Organization\Infrastructure\Http\Controllers\Actions\StoreDepartmentController;
use App\Modules\Organization\Infrastructure\Http\Controllers\Actions\StorePositionController;
use App\Modules\Organization\Infrastructure\Http\Controllers\Actions\UpdateBranchController;
use App\Modules\Organization\Infrastructure\Http\Controllers\Actions\UpdateDepartmentController;
use App\Modules\Organization\Infrastructure\Http\Controllers\Actions\UpdatePositionController;
use App\Modules\Organization\Infrastructure\Http\Controllers\OrgTreeController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware(['auth:sanctum', 'response_cache:600'])->group(function () {
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
