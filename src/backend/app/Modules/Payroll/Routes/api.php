<?php

use App\Modules\Payroll\Infrastructure\Http\Controllers\{
    Actions\ListPayrollPeriodsController,
    Actions\StorePayrollPeriodController,
    Actions\ShowPayrollPeriodController,
    Actions\ShowPayrollPeriodSummaryController,
    Actions\SubmitPayrollPeriodApprovalController,
    Actions\ApprovePayrollPeriodController,
    Actions\RejectPayrollPeriodController,
    Actions\LockPayrollPeriodController,
    Actions\ReopenPayrollPeriodController,
    Actions\StartPayrollRunController,
    Actions\ListPayrollEntriesController,
    Actions\ShowPayrollEntryController,
    Actions\ReviewPayrollEntryController,
    Actions\ListPayrollAdjustmentsController,
    Actions\StorePayrollAdjustmentController,
    Actions\ApprovePayrollAdjustmentController,
    Actions\RejectPayrollAdjustmentController,
    Actions\ListPayslipsController,
    Actions\ShowPayslipController,
    Actions\DownloadPayslipController,
    Actions\PublishPayslipsController,
    Actions\ListPayrollComponentsController,
    Actions\StorePayrollComponentController,
    Actions\UpdatePayrollComponentController,
    Actions\DeletePayrollComponentController,
};
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    // Periods

    Route::get('/payroll/periods', ListPayrollPeriodsController::class)->middleware('permission:payroll.period.view');
    Route::get('/payroll/periods/{id}/summary', ShowPayrollPeriodSummaryController::class)->middleware('permission:payroll.period.view');
    Route::post('/payroll/periods', StorePayrollPeriodController::class)->middleware('permission:payroll.period.manage');
    Route::get('/payroll/periods/{id}', ShowPayrollPeriodController::class)->middleware('permission:payroll.period.view');
    Route::post('/payroll/periods/{id}/submit-approval', SubmitPayrollPeriodApprovalController::class)->middleware('permission:payroll.period.manage');
    Route::post('/payroll/periods/{id}/approve', ApprovePayrollPeriodController::class)->middleware('permission:payroll.approve');
    Route::post('/payroll/periods/{id}/reject', RejectPayrollPeriodController::class)->middleware('permission:payroll.approve');
    Route::post('/payroll/periods/{id}/lock', LockPayrollPeriodController::class)->middleware('permission:payroll.lock');
    Route::post('/payroll/periods/{id}/reopen', ReopenPayrollPeriodController::class)->middleware('permission:payroll.period.manage');
    Route::post('/payroll/periods/{periodId}/start-run', StartPayrollRunController::class)->middleware('permission:payroll.run.start');
    Route::get('/payroll/periods/{periodId}/entries', ListPayrollEntriesController::class)->middleware('permission:payroll.entry.view');
    Route::get('/payroll/entries/{id}', ShowPayrollEntryController::class)->middleware('permission:payroll.entry.view');
    Route::post('/payroll/entries/{id}/review', ReviewPayrollEntryController::class)->middleware('permission:payroll.entry.review');
    Route::get('/payroll/entries/{entryId}/adjustments', ListPayrollAdjustmentsController::class)->middleware('permission:payroll.entry.view');
    Route::post('/payroll/entries/{entryId}/adjustments', StorePayrollAdjustmentController::class)->middleware('permission:payroll.adjustment.manage');
    Route::post('/payroll/adjustments/{id}/approve', ApprovePayrollAdjustmentController::class)->middleware('permission:payroll.adjustment.manage');
    Route::post('/payroll/adjustments/{id}/reject', RejectPayrollAdjustmentController::class)->middleware('permission:payroll.adjustment.manage');
    Route::get('/payroll/payslips', ListPayslipsController::class);
    Route::get('/payroll/payslips/{id}', ShowPayslipController::class);
    Route::get('/payroll/payslips/{id}/download', DownloadPayslipController::class);
    Route::post('/payroll/periods/{periodId}/publish', PublishPayslipsController::class)->middleware('permission:payroll.publish');
    Route::get('/payroll/components', ListPayrollComponentsController::class)->middleware('permission:payroll.period.view');
    Route::post('/payroll/components', StorePayrollComponentController::class)->middleware('permission:payroll.component.manage');
    Route::patch('/payroll/components/{id}', UpdatePayrollComponentController::class)->middleware('permission:payroll.component.manage');
    Route::delete('/payroll/components/{id}', DeletePayrollComponentController::class)->middleware('permission:payroll.component.manage');
});
