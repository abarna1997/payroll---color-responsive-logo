<?php

namespace App\Models\Traits;

use App\Models\Scopes\CompanyScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

trait TenantScoped
{
    /**
     * Boot the trait.
     */
    protected static function bootTenantScoped()
    {
        // Apply the global scope to filter by company_id
        static::addGlobalScope(new CompanyScope);

        // Automatically assign company_id when creating a new record
        static::creating(function ($model) {
            if (!$model->company_id) {
                // If it belongs to an employee, pull the company_id from there
                if (isset($model->employee_id) && $model->employee_id) {
                    $companyId = DB::table('employees')
                        ->where('id', $model->employee_id)
                        ->value('company_id');
                    
                    if ($companyId) {
                        $model->company_id = $companyId;
                    }
                } 
                // Otherwise, pull from the currently authenticated user's employee record
                else if (Auth::hasUser() && Auth::user()->employee) {
                    $model->company_id = Auth::user()->employee->company_id;
                }
            }
        });
    }
}
