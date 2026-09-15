<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;

Route::middleware(['auth', 'password_changed'])->prefix('attendance')->name('attendance.')->group(function () {
    Route::get('/logs', [AttendanceController::class, 'attendanceLogs'])->name('index');
    Route::get('/recalculate', [AttendanceController::class, 'recalculateAttendance'])->name('recalculate');
    Route::post('/clear-logs', [AttendanceController::class, 'clearAttendanceLogs'])->name('clear-logs.action')->middleware('permission:attendance.delete');
    
    Route::get('/manual-logs', [AttendanceController::class, 'manualLogs'])->name('manual-logs.index');
    Route::post('/manual-logs', [AttendanceController::class, 'storeManualLog'])->name('manual-logs.store');
    Route::post('/manual-logs/{id}/approve', [AttendanceController::class, 'approveManualLog'])->name('manual-logs.approve');
    Route::post('/manual-logs/{id}/reject', [AttendanceController::class, 'rejectManualLog'])->name('manual-logs.reject');
});
