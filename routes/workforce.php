<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmployeeController;

Route::middleware(['auth', 'password_changed'])->prefix('workforce')->name('workforce.')->group(function () {
    Route::get('/employees', [EmployeeController::class, 'employees'])->name('employees.index')->middleware('permission:employee.view');
    Route::get('/employees/create', [EmployeeController::class, 'createEmployee'])->name('employees.create')->middleware('permission:employee.create');
    Route::post('/employees', [EmployeeController::class, 'storeEmployee'])->name('employees.store')->middleware('permission:employee.create');
    Route::post('/employees/{id}/update', [EmployeeController::class, 'updateEmployee'])->name('employees.update')->middleware('permission:employee.edit');
    Route::post('/employees/{id}/delete', [EmployeeController::class, 'destroyEmployee'])->name('employees.delete')->middleware('permission:employee.delete');
    Route::post('/employees/bulk-update', [EmployeeController::class, 'bulkUpdateEmployees'])->name('employees.bulk-update')->middleware('permission:employee.edit');
    Route::post('/employees/{id}/sync', [EmployeeController::class, 'syncEmployee'])->name('employees.sync')->middleware('permission:employee.edit');
    Route::get('/employees/{id}/sync-status', [EmployeeController::class, 'getEmployeeSyncStatus'])->name('employees.sync-status')->middleware('permission:employee.view');
    Route::post('/employees/{id}/trigger-enrollment', [EmployeeController::class, 'triggerRemoteEnrollment'])->name('employees.enroll')->middleware('permission:employee.edit');
});
