<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SystemController;
use App\Http\Controllers\CompanyController;

Route::middleware(['auth', 'password_changed'])->prefix('organization')->name('organization.')->group(function () {
    Route::get('/companies', [CompanyController::class, 'index'])->name('companies.index')->middleware('permission:company.view');
    Route::post('/companies', [CompanyController::class, 'store'])->name('companies.store')->middleware('permission:company.create');
    Route::get('/companies/{id}/edit', [CompanyController::class, 'edit'])->name('companies.edit')->middleware('permission:company.edit');
    Route::post('/companies/{id}/update', [CompanyController::class, 'update'])->name('companies.update')->middleware('permission:company.edit');
    Route::post('/companies/{id}/delete', [CompanyController::class, 'destroy'])->name('companies.delete')->middleware('permission:company.delete');

    Route::get('/branches', [\App\Http\Controllers\BranchController::class, 'index'])->name('branches.index')->middleware('permission:branch.view');
    Route::post('/branches', [\App\Http\Controllers\BranchController::class, 'store'])->name('branches.store')->middleware('permission:branch.manage');
    Route::post('/branches/{id}/update', [\App\Http\Controllers\BranchController::class, 'update'])->name('branches.update')->middleware('permission:branch.manage');
    Route::post('/branches/{id}/delete', [\App\Http\Controllers\BranchController::class, 'destroy'])->name('branches.delete')->middleware('permission:branch.manage');

    Route::get('/departments', [\App\Http\Controllers\DepartmentController::class, 'index'])->name('departments.index')->middleware('permission:employee.view');
    Route::post('/departments', [\App\Http\Controllers\DepartmentController::class, 'store'])->name('departments.store')->middleware('permission:employee.edit');
    Route::post('/departments/{id}/update', [\App\Http\Controllers\DepartmentController::class, 'update'])->name('departments.update')->middleware('permission:employee.edit');
    Route::post('/departments/{id}/delete', [\App\Http\Controllers\DepartmentController::class, 'destroy'])->name('departments.delete')->middleware('permission:employee.edit');

    Route::get('/designations', [\App\Http\Controllers\DesignationController::class, 'index'])->name('designations.index')->middleware('permission:employee.view');
    Route::post('/designations', [\App\Http\Controllers\DesignationController::class, 'store'])->name('designations.store')->middleware('permission:employee.edit');
    Route::post('/designations/{id}/update', [\App\Http\Controllers\DesignationController::class, 'update'])->name('designations.update')->middleware('permission:employee.edit');
    Route::post('/designations/{id}/delete', [\App\Http\Controllers\DesignationController::class, 'destroy'])->name('designations.delete')->middleware('permission:employee.edit');

    Route::get('/holidays', [\App\Http\Controllers\HolidayController::class, 'index'])->name('holidays.index');
    Route::post('/holidays', [\App\Http\Controllers\HolidayController::class, 'store'])->name('holidays.store');
    Route::post('/holidays/{id}/update', [\App\Http\Controllers\HolidayController::class, 'update'])->name('holidays.update');
    Route::post('/holidays/{id}/delete', [\App\Http\Controllers\HolidayController::class, 'destroy'])->name('holidays.delete');
});
