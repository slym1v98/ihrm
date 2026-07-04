<?php

use App\Modules\Reporting\Infrastructure\Http\Controllers\Actions\{
    DefinitionsReportController,
    ListRunsReportController,
    RunReportController,
    ShowRunReportController,
};
use Illuminate\Support\Facades\Route;



Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    Route::get('/reports', DefinitionsReportController::class)->middleware('permission:report.definition.view');
    Route::post('/reports/{code}/runs', RunReportController::class)->middleware('permission:report.run.create');
    Route::get('/report-runs', ListRunsReportController::class)->middleware('permission:report.run.view-own');
    Route::get('/report-runs/{id}', ShowRunReportController::class)->middleware('permission:report.run.view-own');
});
