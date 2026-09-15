<?php

namespace App\Services\Payroll;

use App\Models\PayrollConfiguration;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class PayrollConfigurationService
{
    /**
     * Cache for resolved settings per company and date to prevent duplicate queries
     * in a single payroll run.
     * @var array
     */
    protected array $runtimeCache = [];

    /**
     * Get a resolved payroll setting for a specific date and company.
     *
     * @param string $key
     * @param string|Carbon $date The effective date (e.g., PayrollPeriod end_date or start_date)
     * @param mixed $default
     * @param int|null $companyId
     * @return mixed
     */
    public function getValue(string $key, $date, $default = null, ?int $companyId = null)
    {
        $dateObj = $date instanceof Carbon ? $date : Carbon::parse($date);
        $dateString = $dateObj->toDateString();
        
        // Scope companyId to 0 if null, to represent global or currently active tenant
        $cacheCompanyId = $companyId ?? 0;
        $cacheKey = "{$cacheCompanyId}_{$key}_{$dateString}";

        if (array_key_exists($cacheKey, $this->runtimeCache)) {
            return $this->runtimeCache[$cacheKey];
        }

        $query = PayrollConfiguration::where('setting_key', $key)
            ->where('status', 'APPROVED')
            ->where('is_active', true)
            ->where('effective_from', '<=', $dateObj->copy()->endOfDay())
            ->where(function ($q) use ($dateObj) {
                $q->whereNull('effective_to')
                  ->orWhere('effective_to', '>=', $dateObj->copy()->startOfDay());
            });

        // Apply company scope if explicitly provided. 
        // If not provided, TenantScoped trait handles it based on auth/employee,
        // but we explicitly filter if $companyId is given.
        if ($companyId !== null) {
            $query->withoutGlobalScope(\App\Models\Scopes\CompanyScope::class)
                  ->where('company_id', $companyId);
        }

        $config = $query->orderBy('effective_from', 'desc')
                        ->orderBy('id', 'desc')
                        ->first();

        if ($config) {
            $value = $config->getTypedValue();
        } else {
            $value = $default;
        }

        $this->runtimeCache[$cacheKey] = $value;

        return $value;
    }
}
