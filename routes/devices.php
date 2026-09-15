<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DeviceController;

Route::middleware(['auth', 'password_changed'])->prefix('devices')->name('devices.')->group(function () {
    Route::get('/', [DeviceController::class, 'devices'])->name('index')->middleware('permission:device.view');
    Route::post('/scan', [DeviceController::class, 'scanNetwork'])->name('scan')->middleware('permission:device.register');
    Route::post('/discover', [DeviceController::class, 'discoverDevice'])->name('discover')->middleware('permission:device.register');
    Route::post('/{id}/update', [DeviceController::class, 'updateDevice'])->name('update')->middleware('permission:device.register');
    Route::post('/{id}/delete', [DeviceController::class, 'destroyDevice'])->name('delete')->middleware('permission:device.register');
    Route::post('/{id}/approve', [DeviceController::class, 'approveDevice'])->name('approve')->middleware('permission:device.approve');
    Route::post('/{id}/disable', [DeviceController::class, 'disableDevice'])->name('disable')->middleware('permission:device.disable');
    Route::post('/{id}/enable', [DeviceController::class, 'enableDevice'])->name('enable')->middleware('permission:device.disable');
    Route::post('/{id}/command', [DeviceController::class, 'triggerCommand'])->name('command')->middleware('permission:device.commands');
    Route::post('/{id}/sync-employees', [DeviceController::class, 'syncDevice'])->name('sync-employees')->middleware('permission:device.edit');
});
