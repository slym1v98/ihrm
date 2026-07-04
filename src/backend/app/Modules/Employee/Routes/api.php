<?php

use App\Modules\Employee\Infrastructure\Http\Controllers\Actions\{
    ActivateContractController,
    ArchiveEmployeeDocumentController,
    ChangeManagerEmployeeController,
    ChangeStatusEmployeeController,
    DownloadEmployeeDocumentController,
    LinkUserEmployeeController,
    ListContractController,
    ListEmployeeController,
    ListEmployeeDocumentController,
    RenewContractController,
    ReplaceEmployeeDocumentController,
    ShowEmployeeController,
    StoreContractController,
    StoreEmployeeController,
    StoreEmployeeDocumentController,
    TerminateContractController,
    TransferEmployeeController,
    UpdatePersonalInfoEmployeeController,
};
use Illuminate\Support\Facades\Route;



Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    Route::get('/employees', ListEmployeeController::class)->middleware('permission:employee.view');
    Route::post('/employees', StoreEmployeeController::class)->middleware('permission:employee.create');
    Route::get('/employees/{id}', ShowEmployeeController::class)->middleware('permission:employee.view');
    Route::patch('/employees/{id}/personal-info', UpdatePersonalInfoEmployeeController::class)->middleware('permission:employee.update');
    Route::patch('/employees/{id}/employment', TransferEmployeeController::class)->middleware('permission:employee.update');
    Route::patch('/employees/{id}/manager', ChangeManagerEmployeeController::class)->middleware('permission:employee.update');
    Route::patch('/employees/{id}/status', ChangeStatusEmployeeController::class)->middleware('permission:employee.status.change');
    Route::post('/employees/{id}/link-user', LinkUserEmployeeController::class)->middleware('permission:employee.update');

    Route::get('/employees/{id}/contracts', ListContractController::class)->middleware('permission:employee.contract.view');
    Route::post('/employees/{id}/contracts', StoreContractController::class)->middleware('permission:employee.contract.create');
    Route::post('/contracts/{id}/activate', ActivateContractController::class)->middleware('permission:employee.contract.activate');
    Route::post('/contracts/{id}/renew', RenewContractController::class)->middleware('permission:employee.contract.renew');
    Route::post('/contracts/{id}/terminate', TerminateContractController::class)->middleware('permission:employee.contract.terminate');

    Route::get('/employees/{id}/documents', ListEmployeeDocumentController::class)->middleware('permission:employee.document.view');
    Route::post('/employees/{id}/documents', StoreEmployeeDocumentController::class)->middleware('permission:employee.document.upload');
    Route::post('/documents/{id}/replace', ReplaceEmployeeDocumentController::class)->middleware('permission:employee.document.replace');
    Route::post('/documents/{id}/archive', ArchiveEmployeeDocumentController::class)->middleware('permission:employee.document.archive');
    Route::get('/documents/{id}/download', DownloadEmployeeDocumentController::class)->middleware('permission:employee.document.download');
});
