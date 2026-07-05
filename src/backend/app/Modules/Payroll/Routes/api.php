<?php

use App\Modules\Payroll\Infrastructure\Http\Controllers\Actions\ApprovePayrollAdjustmentController;
use App\Modules\Payroll\Infrastructure\Http\Controllers\Actions\ApprovePayrollPeriodController;
use App\Modules\Payroll\Infrastructure\Http\Controllers\Actions\DeletePayrollComponentController;
use App\Modules\Payroll\Infrastructure\Http\Controllers\Actions\DownloadPayslipController;
use App\Modules\Payroll\Infrastructure\Http\Controllers\Actions\ListPayrollAdjustmentsController;
use App\Modules\Payroll\Infrastructure\Http\Controllers\Actions\ListPayrollComponentsController;
use App\Modules\Payroll\Infrastructure\Http\Controllers\Actions\ListPayrollEntriesController;
use App\Modules\Payroll\Infrastructure\Http\Controllers\Actions\ListPayrollPeriodsController;
use App\Modules\Payroll\Infrastructure\Http\Controllers\Actions\ListPayslipsController;
use App\Modules\Payroll\Infrastructure\Http\Controllers\Actions\LockPayrollPeriodController;
use App\Modules\Payroll\Infrastructure\Http\Controllers\Actions\PublishPayslipsController;
use App\Modules\Payroll\Infrastructure\Http\Controllers\Actions\RejectPayrollAdjustmentController;
use App\Modules\Payroll\Infrastructure\Http\Controllers\Actions\RejectPayrollPeriodController;
use App\Modules\Payroll\Infrastructure\Http\Controllers\Actions\ReopenPayrollPeriodController;
use App\Modules\Payroll\Infrastructure\Http\Controllers\Actions\ReviewPayrollEntryController;
use App\Modules\Payroll\Infrastructure\Http\Controllers\Actions\ShowPayrollEntryController;
use App\Modules\Payroll\Infrastructure\Http\Controllers\Actions\ShowPayrollPeriodController;
use App\Modules\Payroll\Infrastructure\Http\Controllers\Actions\ShowPayrollPeriodSummaryController;
use App\Modules\Payroll\Infrastructure\Http\Controllers\Actions\ShowPayslipController;
use App\Modules\Payroll\Infrastructure\Http\Controllers\Actions\StartPayrollRunController;
use App\Modules\Payroll\Infrastructure\Http\Controllers\Actions\StorePayrollAdjustmentController;
use App\Modules\Payroll\Infrastructure\Http\Controllers\Actions\StorePayrollComponentController;
use App\Modules\Payroll\Infrastructure\Http\Controllers\Actions\StorePayrollPeriodController;
use App\Modules\Payroll\Infrastructure\Http\Controllers\Actions\SubmitPayrollPeriodApprovalController;
use App\Modules\Payroll\Infrastructure\Http\Controllers\Actions\UpdatePayrollComponentController;
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
