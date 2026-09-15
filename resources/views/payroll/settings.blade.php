@extends('layouts.app')

@section('content')
<div class="row g-4">
    <div class="col-lg-8 mx-auto">
        <div class="glass-card">
            <h5 class="mb-4 display-font fw-bold"><i class="bi bi-wallet2 me-2 text-primary"></i> Payroll Policies & Settings</h5>
            
            <form action="{{ route('payroll.settings.store') }}" method="POST">
                @csrf
                
                <!-- 1. Statutory Contributions -->
                <div class="mb-5 p-4 rounded border" style="background-color: #f9fafb; border-color: var(--border-color) !important;">
                    <h6 class="display-font text-primary border-bottom pb-2 mb-4">
                        <i class="bi bi-shield-check me-2"></i> Statutory Contributions (Sri Lankan Compliance)
                    </h6>
                    
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label text-secondary fs-7 mb-1">EPF Employee Contribution (%)</label>
                            <input type="number" step="0.01" class="form-control form-control-custom" name="EpfEmployeeRate" value="{{ $settings['EpfEmployeeRate'] }}" required>
                            <span class="fs-8 text-secondary mt-1 d-block">Percentage deducted from employee basic + fixed allowances (Default: 8%).</span>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label text-secondary fs-7 mb-1">EPF Employer Contribution (%)</label>
                            <input type="number" step="0.01" class="form-control form-control-custom" name="EpfEmployerRate" value="{{ $settings['EpfEmployerRate'] }}" required>
                            <span class="fs-8 text-secondary mt-1 d-block">Contribution paid by company towards employee EPF (Default: 12%).</span>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-secondary fs-7 mb-1">ETF Employer Contribution (%)</label>
                            <input type="number" step="0.01" class="form-control form-control-custom" name="EtfEmployerRate" value="{{ $settings['EtfEmployerRate'] }}" required>
                            <span class="fs-8 text-secondary mt-1 d-block">Contribution paid by company towards ETF trust (Default: 3%).</span>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-12">
                            <div class="form-check form-check-inline me-4">
                                <input class="form-check-input check-custom" type="checkbox" name="EnableEpf" value="1" id="EnableEpfCheckbox" {{ $settings['EnableEpf'] === '1' ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold text-dark" for="EnableEpfCheckbox">Enable EPF Calculations Globally</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input check-custom" type="checkbox" name="EnableEtf" value="1" id="EnableEtfCheckbox" {{ $settings['EnableEtf'] === '1' ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold text-dark" for="EnableEtfCheckbox">Enable ETF Calculations Globally</label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. Calculation Policies -->
                <div class="mb-5 p-4 rounded border" style="background-color: #f9fafb; border-color: var(--border-color) !important;">
                    <h6 class="display-font text-primary border-bottom pb-2 mb-4">
                        <i class="bi bi-sliders me-2"></i> Payroll Calculation Policies
                    </h6>
                    
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label text-secondary fs-7 mb-1">Overtime Rate Multiplier</label>
                            <input type="number" step="0.01" class="form-control form-control-custom" name="OvertimeRate" value="{{ $settings['OvertimeRate'] }}" required>
                            <span class="fs-8 text-secondary mt-1 d-block">OT hourly rate multiplier (Default: 1.5).</span>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label text-secondary fs-7 mb-1">No-Pay Divisor (Days)</label>
                            <input type="number" class="form-control form-control-custom" name="NoPayDivisor" value="{{ $settings['NoPayDivisor'] }}" required>
                            <span class="fs-8 text-secondary mt-1 d-block">Total days divisor for unpaid leave cuts (Default: 30 days).</span>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label text-secondary fs-7 mb-1">Normal Working Hours/Month</label>
                            <input type="number" class="form-control form-control-custom" name="NormalWorkingHours" value="{{ $settings['NormalWorkingHours'] }}" required>
                            <span class="fs-8 text-secondary mt-1 d-block">Monthly base hours used to calculate OT hourly rate (Default: 240 hrs).</span>
                        </div>
                    </div>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-custom-primary px-4">
                        <i class="bi bi-check-circle me-1"></i> Save Configuration
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
