<?php

use App\Modules\Shift\Infrastructure\Http\Controllers\Actions\{
    ActivateShiftTemplateController,
    DeactivateShiftTemplateController,
    DepartmentShiftsShiftAssignmentController,
    EmployeeShiftsShiftAssignmentController,
    EndShiftAssignmentController,
    ListShiftTemplateController,
    ShowShiftTemplateController,
    StoreShiftAssignmentController,
    StoreShiftTemplateController,
    UpdateShiftAssignmentController,
    UpdateShiftTemplateController,
};
use Illuminate\Support\Facades\Route;



Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    Route::get('/shift-templates', ListShiftTemplateController::class)->middleware('permission:shift.template.view');
    Route::post('/shift-templates', StoreShiftTemplateController::class)->middleware('permission:shift.template.create');
    Route::get('/shift-templates/{id}', ShowShiftTemplateController::class)->middleware('permission:shift.template.view');
    Route::patch('/shift-templates/{id}', UpdateShiftTemplateController::class)->middleware('permission:shift.template.update');
    Route::post('/shift-templates/{id}/activate', ActivateShiftTemplateController::class)->middleware('permission:shift.template.update');
    Route::post('/shift-templates/{id}/deactivate', DeactivateShiftTemplateController::class)->middleware('permission:shift.template.update');

    Route::post('/shift-assignments', StoreShiftAssignmentController::class)->middleware('permission:shift.template.update');
    Route::patch('/shift-assignments/{id}', UpdateShiftAssignmentController::class)->middleware('permission:shift.template.update');
    Route::post('/shift-assignments/{id}/end', EndShiftAssignmentController::class)->middleware('permission:shift.template.update');

    Route::get('/employees/{id}/shifts', EmployeeShiftsShiftAssignmentController::class)->middleware('permission:shift.template.view');
    Route::get('/departments/{id}/shifts', DepartmentShiftsShiftAssignmentController::class)->middleware('permission:shift.template.view');
});
