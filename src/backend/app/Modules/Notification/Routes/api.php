<?php

use App\Modules\Notification\Infrastructure\Http\Controllers\Actions\ActivateMessageTemplateController;
use App\Modules\Notification\Infrastructure\Http\Controllers\Actions\DeactivateMessageTemplateController;
use App\Modules\Notification\Infrastructure\Http\Controllers\Actions\ListMessageTemplateController;
use App\Modules\Notification\Infrastructure\Http\Controllers\Actions\ListNotificationController;
use App\Modules\Notification\Infrastructure\Http\Controllers\Actions\ListNotificationPreferenceController;
use App\Modules\Notification\Infrastructure\Http\Controllers\Actions\MarkAllReadNotificationController;
use App\Modules\Notification\Infrastructure\Http\Controllers\Actions\MarkReadNotificationController;
use App\Modules\Notification\Infrastructure\Http\Controllers\Actions\ProcessOutboxNotificationController;
use App\Modules\Notification\Infrastructure\Http\Controllers\Actions\StoreMessageTemplateController;
use App\Modules\Notification\Infrastructure\Http\Controllers\Actions\UnreadCountNotificationController;
use App\Modules\Notification\Infrastructure\Http\Controllers\Actions\UpdateMessageTemplateController;
use App\Modules\Notification\Infrastructure\Http\Controllers\Actions\UpdateNotificationPreferenceController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    Route::get('/notifications', ListNotificationController::class)->middleware('permission:notification.view-own');
    Route::get('/notifications/unread-count', UnreadCountNotificationController::class)->middleware('permission:notification.view-own');
    Route::patch('/notifications/{id}/read', MarkReadNotificationController::class)->middleware('permission:notification.mark-read-own');
    Route::patch('/notifications/read-all', MarkAllReadNotificationController::class)->middleware('permission:notification.mark-read-own');

    Route::get('/notification-preferences', ListNotificationPreferenceController::class)->middleware('permission:notification.preference.manage-own');
    Route::put('/notification-preferences', UpdateNotificationPreferenceController::class)->middleware('permission:notification.preference.manage-own');

    Route::get('/notification-templates', ListMessageTemplateController::class)->middleware('permission:notification.template.view');
    Route::post('/notification-templates', StoreMessageTemplateController::class)->middleware('permission:notification.template.manage');
    Route::patch('/notification-templates/{id}', UpdateMessageTemplateController::class)->middleware('permission:notification.template.manage');
    Route::post('/notification-templates/{id}/activate', ActivateMessageTemplateController::class)->middleware('permission:notification.template.manage');
    Route::post('/notification-templates/{id}/deactivate', DeactivateMessageTemplateController::class)->middleware('permission:notification.template.manage');
    Route::post('/notification-outbox/process', ProcessOutboxNotificationController::class)->middleware('permission:notification.outbox.process');
});
