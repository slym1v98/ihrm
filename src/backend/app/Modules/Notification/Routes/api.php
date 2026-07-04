<?php

use App\Modules\Notification\Infrastructure\Http\Controllers\Actions\{
    ActivateMessageTemplateController,
    DeactivateMessageTemplateController,
    ListMessageTemplateController,
    ListNotificationController,
    ListNotificationPreferenceController,
    MarkAllReadNotificationController,
    MarkReadNotificationController,
    ProcessOutboxNotificationController,
    StoreMessageTemplateController,
    UnreadCountNotificationController,
    UpdateMessageTemplateController,
    UpdateNotificationPreferenceController,
};
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
