<?php

namespace Tests\Feature\Payroll;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\PayrollConfiguration;
use App\Models\PayrollPeriod;
use App\Models\Employee;
use App\Services\Payroll\PayrollConfigurationService;

class QaPayrollConfigurationTest extends TestCase
{
    use RefreshDatabase;

    public function test_configuration_historical_and_future_resolution()
    {
        $companyId = 1;
        // Setting A: 8% from Jan 1st 2026 to Jun 30th 2026
        PayrollConfiguration::create([
            'company_id' => $companyId,
            'setting_key' => 'epf_employee_rate',
            'value_type' => 'decimal',
            'setting_value' => '8.0',
            'effective_from' => '2026-01-01',
            'effective_to' => '2026-06-30',
            'is_active' => true,
        ]);

        // Setting B: 9% from Jul 1st 2026 onwards
        PayrollConfiguration::create([
            'company_id' => $companyId,
            'setting_key' => 'epf_employee_rate',
            'value_type' => 'decimal',
            'setting_value' => '9.0',
            'effective_from' => '2026-07-01',
            'effective_to' => null,
            'is_active' => true,
        ]);

        $service = app(PayrollConfigurationService::class);

        // Test Historical Resolution
        $this->assertEquals(8.0, $service->getValue('epf_employee_rate', '2026-06-30', null, $companyId));

        // Test Future Resolution
        $this->assertEquals(9.0, $service->getValue('epf_employee_rate', '2026-07-01', null, $companyId));
        $this->assertEquals(9.0, $service->getValue('epf_employee_rate', '2027-01-01', null, $companyId));
    }

    public function test_company_isolation()
    {
        // Company A
        PayrollConfiguration::create([
            'company_id' => 1,
            'setting_key' => 'epf_employee_rate',
            'value_type' => 'decimal',
            'setting_value' => '8.0',
            'effective_from' => '2026-01-01',
            'is_active' => true,
        ]);

        // Company B
        PayrollConfiguration::create([
            'company_id' => 2,
            'setting_key' => 'epf_employee_rate',
            'value_type' => 'decimal',
            'setting_value' => '9.0',
            'effective_from' => '2026-01-01',
            'is_active' => true,
        ]);

        $service = app(PayrollConfigurationService::class);

        $this->assertEquals(8.0, $service->getValue('epf_employee_rate', '2026-06-30', null, 1));
        $this->assertEquals(9.0, $service->getValue('epf_employee_rate', '2026-06-30', null, 2));
    }

    public function test_caching_within_service()
    {
        $service = app(PayrollConfigurationService::class);
        $companyId = 1;

        PayrollConfiguration::create([
            'company_id' => $companyId,
            'setting_key' => 'epf_employee_rate',
            'value_type' => 'decimal',
            'setting_value' => '8.0',
            'effective_from' => '2026-01-01',
            'is_active' => true,
        ]);

        $firstCall = $service->getValue('epf_employee_rate', '2026-06-30', null, $companyId);
        
        // Mutate DB to prove cache is hit
        PayrollConfiguration::where('setting_key', 'epf_employee_rate')->update(['setting_value' => '10.0']);

        $secondCall = $service->getValue('epf_employee_rate', '2026-06-30', null, $companyId);

        $this->assertEquals(8.0, $firstCall);
        $this->assertEquals(8.0, $secondCall); // Should remain 8.0 due to in-memory caching
    }

    public function test_default_fallback_values()
    {
        $service = app(PayrollConfigurationService::class);
        $this->assertEquals(30.0, $service->getValue('no_pay_divisor', '2026-06-30', 30.0, 1));
    }

    public function test_invalid_divisor_rejected()
    {
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        
        $request = \Illuminate\Http\Request::create('/payroll/configuration', 'POST', [
            'setting_key' => 'no_pay_divisor',
            'value_type' => 'decimal',
            'setting_value' => '0',
            'effective_from' => '2026-01-01',
        ]);
        
        $controller = new \App\Http\Controllers\PayrollConfigurationController();
        $controller->store($request);
    }

    public function test_invalid_percentage_rejected()
    {
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        
        $request = \Illuminate\Http\Request::create('/payroll/configuration', 'POST', [
            'setting_key' => 'epf_employee_rate',
            'value_type' => 'decimal',
            'setting_value' => '-5',
            'effective_from' => '2026-01-01',
        ]);
        
        $controller = new \App\Http\Controllers\PayrollConfigurationController();
        $controller->store($request);
    }

    public function test_overlap_rejection()
    {
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        
        PayrollConfiguration::create([
            'setting_key' => 'epf_employee_rate',
            'value_type' => 'decimal',
            'setting_value' => '8.0',
            'effective_from' => '2026-06-01',
            'effective_to' => null,
            'is_active' => true,
        ]);

        $request = \Illuminate\Http\Request::create('/payroll/configuration', 'POST', [
            'setting_key' => 'epf_employee_rate',
            'value_type' => 'decimal',
            'setting_value' => '9.0',
            'effective_from' => '2026-01-01', // Before the existing setting
        ]);
        
        $controller = new \App\Http\Controllers\PayrollConfigurationController();
        $controller->store($request);
    }
}
