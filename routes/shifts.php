<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SystemController;

Route::middleware(['auth', 'password_changed'])->prefix('shifts')->name('shifts.')->group(function () {
    Route::get('/', [\App\Http\Controllers\ShiftController::class, 'index'])->name('index')->middleware('permission:attendance.view');
    Route::post('/', [\App\Http\Controllers\ShiftController::class, 'store'])->name('store')->middleware('permission:attendance.edit');
    Route::post('/{id}/update', [\App\Http\Controllers\ShiftController::class, 'update'])->name('update')->middleware('permission:attendance.edit');
    Route::post('/{id}/delete', [\App\Http\Controllers\ShiftController::class, 'destroy'])->name('delete')->middleware('permission:attendance.delete');
});
