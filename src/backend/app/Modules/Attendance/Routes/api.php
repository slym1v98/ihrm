<?php

use App\Modules\Attendance\Infrastructure\Http\Controllers\Actions\{
    ApproveAttendanceAdjustmentController,
    CalculateAttendanceTimesheetController,
    CloseAttendancePeriodController,
    EmployeeAttendanceAttendanceTimesheetController,
    ListAttendanceAdjustmentController,
    ListAttendancePeriodController,
    ListAttendanceRawLogController,
    ListAttendanceTimesheetController,
    RejectAttendanceAdjustmentController,
    ReopenAttendancePeriodController,
    StoreAttendanceAdjustmentController,
    StoreAttendancePeriodController,
    StoreAttendanceRawLogController,
};
use Illuminate\Support\Facades\Route;



Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    Route::get('/attendance/raw-logs', ListAttendanceRawLogController::class)->middleware('permission:attendance.raw-log.view');
    Route::post('/attendance/raw-logs', StoreAttendanceRawLogController::class)->middleware('permission:attendance.raw-log.create');

    Route::get('/attendance/timesheets', ListAttendanceTimesheetController::class)->middleware('permission:attendance.timesheet.view');
    Route::get('/employees/{id}/attendance', EmployeeAttendanceAttendanceTimesheetController::class)->middleware('permission:attendance.timesheet.view');
    Route::post('/attendance/calculate', CalculateAttendanceTimesheetController::class)->middleware('permission:attendance.timesheet.calculate');

    Route::get('/attendance-adjustment-requests', ListAttendanceAdjustmentController::class)->middleware('permission:attendance.adjustment.approve');
    Route::post('/attendance-adjustment-requests', StoreAttendanceAdjustmentController::class)->middleware('permission:attendance.adjustment.create');
    Route::post('/attendance-adjustment-requests/{id}/approve', ApproveAttendanceAdjustmentController::class)->middleware('permission:attendance.adjustment.approve');
    Route::post('/attendance-adjustment-requests/{id}/reject', RejectAttendanceAdjustmentController::class)->middleware('permission:attendance.adjustment.approve');

    Route::get('/attendance-periods', ListAttendancePeriodController::class)->middleware('permission:attendance.period.manage');
    Route::post('/attendance-periods', StoreAttendancePeriodController::class)->middleware('permission:attendance.period.manage');
    Route::post('/attendance-periods/{id}/close', CloseAttendancePeriodController::class)->middleware('permission:attendance.period.manage');
    Route::post('/attendance-periods/{id}/reopen', ReopenAttendancePeriodController::class)->middleware('permission:attendance.period.manage');
});
