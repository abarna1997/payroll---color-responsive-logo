<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class CompanyScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        if (Auth::hasUser()) {
            $user = Auth::user();
            
            // Super Admins and overarching Admins see everything
            if (in_array($user->role, ['Super Admin', 'Admin', 'Super Administrator'])) {
                return;
            }
            
            // Use DB::table to prevent infinite loop caused by Eloquent resolving the employee relationship
            // which in turn re-applies this scope to the Employee model query.
            $companyId = \Illuminate\Support\Facades\DB::table('employees')
                ->where('user_id', $user->id)
                ->value('company_id');
                
            if ($companyId) {
                $builder->where($model->getTable() . '.company_id', $companyId);
            } else {
                // If the user is NOT a global admin, and they DO NOT have an assigned company,
                // they should not see any records.
                $builder->whereRaw('1 = 0');
            }
        }
    }
}
