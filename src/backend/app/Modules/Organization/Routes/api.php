<?php

use App\Modules\Organization\Infrastructure\Http\Controllers\BranchController;
use App\Modules\Organization\Infrastructure\Http\Controllers\DepartmentController;
use App\Modules\Organization\Infrastructure\Http\Controllers\OrgTreeController;
use App\Modules\Organization\Infrastructure\Http\Controllers\PositionController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    Route::get('/branches', [BranchController::class, 'index'])->middleware('permission:organization.branch.list');
    Route::post('/branches', [BranchController::class, 'store'])->middleware('permission:organization.branch.create');
    Route::get('/branches/{id}', [BranchController::class, 'show'])->middleware('permission:organization.branch.view');
    Route::patch('/branches/{id}', [BranchController::class, 'update'])->middleware('permission:organization.branch.update');
    Route::post('/branches/{id}/activate', [BranchController::class, 'activate'])->middleware('permission:organization.branch.update');
    Route::post('/branches/{id}/deactivate', [BranchController::class, 'deactivate'])->middleware('permission:organization.branch.update');

    Route::get('/departments', [DepartmentController::class, 'index'])->middleware('permission:organization.department.list');
    Route::post('/departments', [DepartmentController::class, 'store'])->middleware('permission:organization.department.create');
    Route::get('/departments/{id}', [DepartmentController::class, 'show'])->middleware('permission:organization.department.view');
    Route::patch('/departments/{id}', [DepartmentController::class, 'update'])->middleware('permission:organization.department.update');
    Route::post('/departments/{id}/move', [DepartmentController::class, 'move'])->middleware('permission:organization.department.move');
    Route::post('/departments/{id}/activate', [DepartmentController::class, 'activate'])->middleware('permission:organization.department.update');
    Route::post('/departments/{id}/deactivate', [DepartmentController::class, 'deactivate'])->middleware('permission:organization.department.update');

    Route::get('/positions', [PositionController::class, 'index'])->middleware('permission:organization.position.list');
    Route::post('/positions', [PositionController::class, 'store'])->middleware('permission:organization.position.create');
    Route::get('/positions/{id}', [PositionController::class, 'show'])->middleware('permission:organization.position.view');
    Route::patch('/positions/{id}', [PositionController::class, 'update'])->middleware('permission:organization.position.update');
    Route::post('/positions/{id}/activate', [PositionController::class, 'activate'])->middleware('permission:organization.position.update');
    Route::post('/positions/{id}/deactivate', [PositionController::class, 'deactivate'])->middleware('permission:organization.position.update');

    Route::get('/org-tree', OrgTreeController::class)->middleware('permission:organization.tree.view');
});
