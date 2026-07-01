<?php

use App\Modules\Employee\Infrastructure\Http\Controllers\ContractController;
use App\Modules\Employee\Infrastructure\Http\Controllers\EmployeeController;
use App\Modules\Employee\Infrastructure\Http\Controllers\EmployeeDocumentController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    Route::get('/employees', [EmployeeController::class, 'index'])->middleware('permission:employee.view');
    Route::post('/employees', [EmployeeController::class, 'store'])->middleware('permission:employee.create');
    Route::get('/employees/{id}', [EmployeeController::class, 'show'])->middleware('permission:employee.view');
    Route::patch('/employees/{id}/personal-info', [EmployeeController::class, 'updatePersonalInfo'])->middleware('permission:employee.update');
    Route::patch('/employees/{id}/employment', [EmployeeController::class, 'transfer'])->middleware('permission:employee.update');
    Route::patch('/employees/{id}/manager', [EmployeeController::class, 'changeManager'])->middleware('permission:employee.update');
    Route::patch('/employees/{id}/status', [EmployeeController::class, 'changeStatus'])->middleware('permission:employee.status.change');
    Route::post('/employees/{id}/link-user', [EmployeeController::class, 'linkUser'])->middleware('permission:employee.update');

    Route::get('/employees/{id}/contracts', [ContractController::class, 'index'])->middleware('permission:employee.contract.view');
    Route::post('/employees/{id}/contracts', [ContractController::class, 'store'])->middleware('permission:employee.contract.create');
    Route::post('/contracts/{id}/activate', [ContractController::class, 'activate'])->middleware('permission:employee.contract.activate');
    Route::post('/contracts/{id}/renew', [ContractController::class, 'renew'])->middleware('permission:employee.contract.renew');
    Route::post('/contracts/{id}/terminate', [ContractController::class, 'terminate'])->middleware('permission:employee.contract.terminate');

    Route::get('/employees/{id}/documents', [EmployeeDocumentController::class, 'index'])->middleware('permission:employee.document.view');
    Route::post('/employees/{id}/documents', [EmployeeDocumentController::class, 'store'])->middleware('permission:employee.document.upload');
    Route::post('/documents/{id}/replace', [EmployeeDocumentController::class, 'replace'])->middleware('permission:employee.document.replace');
    Route::post('/documents/{id}/archive', [EmployeeDocumentController::class, 'archive'])->middleware('permission:employee.document.archive');
    Route::get('/documents/{id}/download', [EmployeeDocumentController::class, 'download'])->middleware('permission:employee.document.download');
});
