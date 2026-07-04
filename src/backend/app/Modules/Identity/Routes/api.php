<?php

use App\Modules\Identity\Infrastructure\Http\Controllers\Actions\{
    ActivateRoleController,
    AssignRoleUserController,
    ChangePasswordAuthController,
    DeactivateRoleController,
    DisableUserController,
    GrantPermissionRoleController,
    ListPermissionController,
    ListRoleController,
    ListUserController,
    LoginAuthController,
    LogoutAuthController,
    MeAuthController,
    ReactivateUserController,
    RevokePermissionRoleController,
    RevokeRoleUserController,
    ShowRoleController,
    ShowUserController,
    StoreRoleController,
    StoreUserController,
    UpdateRoleController,
    UpdateUserController,
};
use Illuminate\Support\Facades\Route;



Route::prefix('v1')->group(function () {
    Route::post('/auth/login', LoginAuthController::class);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', LogoutAuthController::class);
        Route::get('/auth/me', MeAuthController::class);
        Route::post('/auth/change-password', ChangePasswordAuthController::class);

        Route::get('/users', ListUserController::class)->middleware('permission:identity.user.list');
        Route::post('/users', StoreUserController::class)->middleware('permission:identity.user.create');
        Route::get('/users/{id}', ShowUserController::class)->middleware('permission:identity.user.view');
        Route::patch('/users/{id}', UpdateUserController::class)->middleware('permission:identity.user.update');
        Route::post('/users/{id}/disable', DisableUserController::class)->middleware('permission:identity.user.disable');
        Route::post('/users/{id}/reactivate', ReactivateUserController::class)->middleware('permission:identity.user.reactivate');
        Route::post('/users/{id}/roles', AssignRoleUserController::class)->middleware('permission:identity.user.assign_role');
        Route::delete('/users/{id}/roles/{roleId}', RevokeRoleUserController::class)->middleware('permission:identity.user.revoke_role');

        Route::get('/roles', ListRoleController::class)->middleware('permission:identity.role.list');
        Route::post('/roles', StoreRoleController::class)->middleware('permission:identity.role.create');
        Route::get('/roles/{id}', ShowRoleController::class)->middleware('permission:identity.role.view');
        Route::patch('/roles/{id}', UpdateRoleController::class)->middleware('permission:identity.role.update');
        Route::post('/roles/{id}/activate', ActivateRoleController::class)->middleware('permission:identity.role.update');
        Route::post('/roles/{id}/deactivate', DeactivateRoleController::class)->middleware('permission:identity.role.update');
        Route::post('/roles/{id}/permissions', GrantPermissionRoleController::class)->middleware('permission:identity.role.grant_permission');
        Route::delete('/roles/{id}/permissions/{code}', RevokePermissionRoleController::class)->middleware('permission:identity.role.revoke_permission');

        Route::get('/permissions', ListPermissionController::class)->middleware('permission:identity.permission.list');
    });
});
