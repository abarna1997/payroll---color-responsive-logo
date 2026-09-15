<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SystemController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\AccessLevelController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\ApprovalWorkflowController;
use App\Http\Controllers\QueueManagerController;
use App\Http\Controllers\SettingsController;

Route::middleware(['auth', 'password_changed'])->prefix('admin')->name('administration.')->group(function () {
    Route::get('/users', [SystemController::class, 'users'])->name('users.index');
    Route::post('/users', [SystemController::class, 'storeUser'])->name('users.store');
    Route::post('/users/{id}/update', [SystemController::class, 'updateUser'])->name('users.update');
    Route::post('/users/{id}/delete', [SystemController::class, 'destroyUser'])->name('users.delete');

    Route::get('/roles', [SystemController::class, 'roles'])->name('roles.index');
    Route::post('/roles', [SystemController::class, 'storeRole'])->name('roles.store');
    Route::post('/roles/{id}/update', [SystemController::class, 'updateRole'])->name('roles.update');
    Route::post('/roles/{id}/delete', [SystemController::class, 'destroyRole'])->name('roles.delete');
    Route::post('/roles/{id}/move-up', [SystemController::class, 'moveRoleUp'])->name('roles.move-up');
    Route::post('/roles/{id}/move-down', [SystemController::class, 'moveRoleDown'])->name('roles.move-down');

    Route::get('/permissions', [PermissionController::class, 'index'])->name('permissions.index');
    Route::post('/permissions/{userId}/store', [PermissionController::class, 'storeUserPermissions'])->name('permissions.store');
    Route::post('/permissions/{userId}/template', [PermissionController::class, 'applyTemplate'])->name('permissions.apply-template');
    Route::post('/permissions/bulk', [PermissionController::class, 'bulkAssign'])->name('permissions.bulk-assign');
    Route::get('/permissions/templates', [PermissionController::class, 'templatesIndex'])->name('permissions.templates');
    Route::post('/permissions/templates', [PermissionController::class, 'storeTemplate'])->name('permissions.templates.store');
    Route::post('/permissions/templates/{id}/delete', [PermissionController::class, 'destroyTemplate'])->name('permissions.templates.delete');
    Route::get('/permissions/history', [PermissionController::class, 'history'])->name('permissions.history');

    Route::get('/access-levels', [AccessLevelController::class, 'index'])->name('access-levels.index');
    Route::post('/access-levels/{roleId}/{levelId}', [AccessLevelController::class, 'store'])->name('access-levels.store');
    Route::get('/access-levels/{roleId}/matrix', [AccessLevelController::class, 'matrix'])->name('access-levels.matrix');

    Route::get('/queue-manager', [QueueManagerController::class, 'index'])->name('queue-manager.index');
    Route::post('/queue-manager', [QueueManagerController::class, 'queueCommand'])->name('queue-manager.store');
    Route::post('/queue-manager/{id}/retry', [QueueManagerController::class, 'retryCommand'])->name('queue-manager.retry');
    Route::post('/queue-manager/{id}/cancel', [QueueManagerController::class, 'cancelCommand'])->name('queue-manager.cancel');

    Route::get('/settings', [SystemController::class, 'settings'])->name('settings.index');
    Route::post('/settings', [SystemController::class, 'storeSettings'])->name('settings.store');

    Route::get('/backups', [SystemController::class, 'backups'])->name('backups.index');
    Route::post('/backups/trigger', [SystemController::class, 'triggerBackup'])->name('backups.trigger');
    Route::get('/backups/{id}/download', [SystemController::class, 'downloadBackup'])->name('backups.download');

    Route::get('/menus', [MenuController::class, 'index'])->name('menus.index');
    Route::post('/menus', [MenuController::class, 'store'])->name('menus.store');
    Route::post('/menus/{id}/update', [MenuController::class, 'update'])->name('menus.update');
    Route::post('/menus/{id}/delete', [MenuController::class, 'destroy'])->name('menus.delete');
    Route::post('/menus/reorder', [MenuController::class, 'reorder'])->name('menus.reorder');

    Route::get('/approval-workflows', [ApprovalWorkflowController::class, 'index'])->name('approval-workflows.index');
    Route::post('/approval-workflows', [ApprovalWorkflowController::class, 'store'])->name('approval-workflows.store');
    Route::post('/approval-workflows/{id}/update', [ApprovalWorkflowController::class, 'update'])->name('approval-workflows.update');
    Route::post('/approval-workflows/{id}/delete', [ApprovalWorkflowController::class, 'destroy'])->name('approval-workflows.delete');
    Route::post('/approval-workflows/{id}/steps', [ApprovalWorkflowController::class, 'addStep'])->name('approval-workflows.add-step');
    Route::post('/approval-workflows/steps/{stepId}/delete', [ApprovalWorkflowController::class, 'deleteStep'])->name('approval-workflows.delete-step');
});
