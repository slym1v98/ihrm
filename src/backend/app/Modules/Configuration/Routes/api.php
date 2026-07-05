<?php

use App\Modules\Configuration\Infrastructure\Http\Controllers\Actions\ListCodeGenerationRuleController;
use App\Modules\Configuration\Infrastructure\Http\Controllers\Actions\ListHolidayCalendarController;
use App\Modules\Configuration\Infrastructure\Http\Controllers\Actions\ListLookupController;
use App\Modules\Configuration\Infrastructure\Http\Controllers\Actions\ListNotificationThresholdController;
use App\Modules\Configuration\Infrastructure\Http\Controllers\Actions\ListSystemSettingController;
use App\Modules\Configuration\Infrastructure\Http\Controllers\Actions\NextCodeGenerationRuleController;
use App\Modules\Configuration\Infrastructure\Http\Controllers\Actions\PreviewCodeGenerationRuleController;
use App\Modules\Configuration\Infrastructure\Http\Controllers\Actions\ShowLookupController;
use App\Modules\Configuration\Infrastructure\Http\Controllers\Actions\StoreCodeGenerationRuleController;
use App\Modules\Configuration\Infrastructure\Http\Controllers\Actions\StoreHolidayCalendarController;
use App\Modules\Configuration\Infrastructure\Http\Controllers\Actions\StoreHolidayHolidayCalendarController;
use App\Modules\Configuration\Infrastructure\Http\Controllers\Actions\StoreLookupController;
use App\Modules\Configuration\Infrastructure\Http\Controllers\Actions\StoreNotificationThresholdController;
use App\Modules\Configuration\Infrastructure\Http\Controllers\Actions\StoreSystemSettingController;
use App\Modules\Configuration\Infrastructure\Http\Controllers\Actions\StoreValueLookupController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->prefix('v1/config')->group(function () {
    Route::get('lookups', ListLookupController::class)->middleware('permission:configuration.lookup.list');
    Route::post('lookups', StoreLookupController::class)->middleware('permission:configuration.lookup.manage');
    Route::get('lookups/{id}', ShowLookupController::class)->middleware('permission:configuration.lookup.list');
    Route::post('lookups/{id}/values', StoreValueLookupController::class)->middleware('permission:configuration.lookup.manage');

    Route::get('code-generation-rules', ListCodeGenerationRuleController::class)->middleware('permission:configuration.code_generation.list');
    Route::post('code-generation-rules', StoreCodeGenerationRuleController::class)->name('config.code_rules.store')->middleware('permission:configuration.code_generation.manage');
    Route::post('code-generation-rules/{entityType}/preview', PreviewCodeGenerationRuleController::class)->middleware('permission:configuration.code_generation.list');
    Route::post('code-generation-rules/{entityType}/next', NextCodeGenerationRuleController::class)->middleware('permission:configuration.code_generation.manage');

    Route::get('settings', ListSystemSettingController::class)->middleware('permission:configuration.setting.list');
    Route::post('settings', StoreSystemSettingController::class)->name('config.settings.store')->middleware('permission:configuration.setting.manage');

    Route::get('holiday-calendars', ListHolidayCalendarController::class)->middleware('permission:configuration.holiday.list');
    Route::post('holiday-calendars', StoreHolidayCalendarController::class)->name('config.holidays.store')->middleware('permission:configuration.holiday.manage');
    Route::post('holiday-calendars/{id}/holidays', StoreHolidayHolidayCalendarController::class)->name('config.holidays.value.store')->middleware('permission:configuration.holiday.manage');

    Route::get('notification-thresholds', ListNotificationThresholdController::class)->middleware('permission:configuration.notification_threshold.list');
    Route::post('notification-thresholds', StoreNotificationThresholdController::class)->name('config.thresholds.store')->middleware('permission:configuration.notification_threshold.manage');
});
