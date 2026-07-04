<?php

use App\Modules\Audit\Infrastructure\Http\Controllers\Actions\{
    ListAuditLogController,
};
use Illuminate\Support\Facades\Route;



Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::get('audit-logs', ListAuditLogController::class)->middleware('permission:audit.log.list');
});
