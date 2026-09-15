@extends('layouts.app')

@section('content')
<div class="row g-4">
    <div class="col-lg-10 mx-auto">
        <div class="glass-card mb-4 d-flex justify-content-between align-items-center">
            <div>
                <h4 class="display-font m-0 fw-bold"><i class="bi bi-wallet2 me-2 text-primary"></i> Enterprise Payroll Settings</h4>
                <div class="text-secondary fs-7 mt-1">Configure statutory tax brackets, EPF rates, overtime multipliers, and calculations rules.</div>
            </div>
            @if($readOnly)
                <span class="badge bg-warning text-dark px-3 py-2 fs-7"><i class="bi bi-eye-fill me-1"></i> Read-Only Mode (HR Admin)</span>
            @else
                <span class="badge bg-success px-3 py-2 fs-7"><i class="bi bi-pencil-fill me-1"></i> Full Access (Super Admin)</span>
            @endif
        </div>

        <form action="{{ route('settings.payroll.update') }}" method="POST">
            @csrf

            <div class="row g-4">
                <!-- CARD 1: GENERAL -->
                <div class="col-md-6">
                    <div class="glass-card h-100">
                        <h6 class="display-font text-primary border-bottom pb-2 mb-4">
                            <i class="bi bi-gear-fill me-2"></i> 1. General Settings
                        </h6>
                        <div class="mb-3">
                            <label class="form-label text-secondary fs-8 uppercase fw-semibold">Currency Code (ISO)</label>
                            <input type="text" class="form-control form-control-custom" name="Currency" value="{{ $settings['Currency'] }}" placeholder="e.g. LKR" required @disabled($readOnly)>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label text-secondary fs-8 uppercase fw-semibold">Decimal Places</label>
                                <input type="number" class="form-control form-control-custom" name="DecimalPlaces" value="{{ $settings['DecimalPlaces'] }}" min="0" max="4" required @disabled($readOnly)>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-secondary fs-8 uppercase fw-semibold">Currency Position</label>
                                <select class="form-select form-select-custom" name="CurrencyPosition" required @disabled($readOnly)>
                                    <option value="Before" {{ $settings['CurrencyPosition'] === 'Before' ? 'selected' : '' }}>Before (e.g. LKR 1,000)</option>
                                    <option value="After" {{ $settings['CurrencyPosition'] === 'After' ? 'selected' : '' }}>After (e.g. 1,000 LKR)</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label text-secondary fs-8 uppercase fw-semibold">Salary Days Per Month</label>
                                <input type="number" class="form-control form-control-custom" name="SalaryDaysPerMonth" value="{{ $settings['SalaryDaysPerMonth'] }}" min="1" max="31" required @disabled($readOnly)>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-secondary fs-8 uppercase fw-semibold">Working Hours Per Month</label>
                                <input type="number" class="form-control form-control-custom" name="WorkingHoursPerMonth" value="{{ $settings['WorkingHoursPerMonth'] }}" min="1" max="744" required @disabled($readOnly)>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label text-secondary fs-8 uppercase fw-semibold">Payroll Cycle</label>
                                <select class="form-select form-select-custom" name="PayrollCycle" required @disabled($readOnly)>
                                    <option value="Monthly" {{ $settings['PayrollCycle'] === 'Monthly' ? 'selected' : '' }}>Monthly</option>
                                    <option value="Weekly" {{ $settings['PayrollCycle'] === 'Weekly' ? 'selected' : '' }}>Weekly</option>
                                    <option value="Bi-Weekly" {{ $settings['PayrollCycle'] === 'Bi-Weekly' ? 'selected' : '' }}>Bi-Weekly</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-secondary fs-8 uppercase fw-semibold">Default Working Days</label>
                                <input type="text" class="form-control form-control-custom" name="DefaultWorkingDays" value="{{ $settings['DefaultWorkingDays'] }}" placeholder="e.g. Monday-Friday" required @disabled($readOnly)>
                            </div>
                        </div>
                        <div class="mb-2">
                            <label class="form-label text-secondary fs-8 uppercase fw-semibold">Payslip Footer Note</label>
                            <input type="text" class="form-control form-control-custom" name="PayslipFooter" value="{{ $settings['PayslipFooter'] }}" required @disabled($readOnly)>
                        </div>
                    </div>
                </div>

                <!-- CARD 2: EPF / ETF -->
                <div class="col-md-6">
                    <div class="glass-card h-100 d-flex flex-column justify-content-between">
                        <div>
                            <h6 class="display-font text-primary border-bottom pb-2 mb-4">
                                <i class="bi bi-shield-check me-2"></i> 2. EPF / ETF Contributions
                            </h6>
                            <div class="mb-4">
                                <div class="form-check form-check-inline me-4">
                                    <input class="form-check-input check-custom" type="checkbox" name="EnableEPF" value="true" id="EnableEPF" {{ $settings['EnableEPF'] === 'true' ? 'checked' : '' }} @disabled($readOnly)>
                                    <label class="form-check-label fw-semibold text-dark" for="EnableEPF">Enable EPF Global Calculations</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input check-custom" type="checkbox" name="EnableETF" value="true" id="EnableETF" {{ $settings['EnableETF'] === 'true' ? 'checked' : '' }} @disabled($readOnly)>
                                    <label class="form-check-label fw-semibold text-dark" for="EnableETF">Enable ETF Global Calculations</label>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-secondary fs-8 uppercase fw-semibold">EPF Employee Contribution (%)</label>
                                <input type="number" step="0.01" class="form-control form-control-custom" name="EPFEmployeeRate" value="{{ $settings['EPFEmployeeRate'] }}" min="0" max="100" required @disabled($readOnly)>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-secondary fs-8 uppercase fw-semibold">EPF Employer Contribution (%)</label>
                                <input type="number" step="0.01" class="form-control form-control-custom" name="EPFEmployerRate" value="{{ $settings['EPFEmployerRate'] }}" min="0" max="100" required @disabled($readOnly)>
                            </div>
                            <div class="mb-2">
                                <label class="form-label text-secondary fs-8 uppercase fw-semibold">ETF Employer Contribution (%)</label>
                                <input type="number" step="0.01" class="form-control form-control-custom" name="ETFRate" value="{{ $settings['ETFRate'] }}" min="0" max="100" required @disabled($readOnly)>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CARD 3: ATTENDANCE -->
                <div class="col-md-6">
                    <div class="glass-card h-100">
                        <h6 class="display-font text-primary border-bottom pb-2 mb-4">
                            <i class="bi bi-clock-history me-2"></i> 3. Attendance Policies
                        </h6>
                        <div class="mb-3">
                            <div class="form-check mb-2">
                                <input class="form-check-input check-custom" type="checkbox" name="AttendanceBasedPayroll" value="true" id="AttendanceBasedPayroll" {{ $settings['AttendanceBasedPayroll'] === 'true' ? 'checked' : '' }} @disabled($readOnly)>
                                <label class="form-check-label fw-semibold text-dark" for="AttendanceBasedPayroll">Require Attendance Log Verification</label>
                            </div>
                            <div class="form-check mb-4">
                                <input class="form-check-input check-custom" type="checkbox" name="AttendanceBonusEnabled" value="true" id="AttendanceBonusEnabled" {{ $settings['AttendanceBonusEnabled'] === 'true' ? 'checked' : '' }} @disabled($readOnly)>
                                <label class="form-check-label fw-semibold text-dark" for="AttendanceBonusEnabled">Enable Perfect Attendance Bonus</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-secondary fs-8 uppercase fw-semibold">Late Arrival Grace (Mins)</label>
                                <input type="number" class="form-control form-control-custom" name="LateGrace" value="{{ $settings['LateGrace'] }}" min="0" required @disabled($readOnly)>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-secondary fs-8 uppercase fw-semibold">Early Checkout Grace (Mins)</label>
                                <input type="number" class="form-control form-control-custom" name="EarlyOutGrace" value="{{ $settings['EarlyOutGrace'] }}" min="0" required @disabled($readOnly)>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CARD 4: OVERTIME -->
                <div class="col-md-6">
                    <div class="glass-card h-100">
                        <h6 class="display-font text-primary border-bottom pb-2 mb-4">
                            <i class="bi bi-stopwatch me-2"></i> 4. Overtime (OT) Multipliers
                        </h6>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input check-custom" type="checkbox" name="EnableOvertime" value="true" id="EnableOvertime" {{ $settings['EnableOvertime'] === 'true' ? 'checked' : '' }} @disabled($readOnly)>
                                <label class="form-check-label fw-semibold text-dark" for="EnableOvertime">Enable Overtime Calculations Globally</label>
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label text-secondary fs-8 uppercase fw-semibold">Normal OT Multiplier</label>
                                <input type="number" step="0.01" class="form-control form-control-custom" name="OvertimeMultiplier" value="{{ $settings['OvertimeMultiplier'] }}" min="1" required @disabled($readOnly)>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-secondary fs-8 uppercase fw-semibold">Weekend OT Multiplier</label>
                                <input type="number" step="0.01" class="form-control form-control-custom" name="WeekendOTMultiplier" value="{{ $settings['WeekendOTMultiplier'] }}" min="1" required @disabled($readOnly)>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-secondary fs-8 uppercase fw-semibold">Holiday OT Multiplier</label>
                                <input type="number" step="0.01" class="form-control form-control-custom" name="HolidayOTMultiplier" value="{{ $settings['HolidayOTMultiplier'] }}" min="1" required @disabled($readOnly)>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-secondary fs-8 uppercase fw-semibold">Poya Day OT Multiplier</label>
                                <input type="number" step="0.01" class="form-control form-control-custom" name="PoyaOTMultiplier" value="{{ $settings['PoyaOTMultiplier'] }}" min="1" required @disabled($readOnly)>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CARD 5: NO PAY -->
                <div class="col-md-6">
                    <div class="glass-card h-100">
                        <h6 class="display-font text-primary border-bottom pb-2 mb-4">
                            <i class="bi bi-dash-circle me-2"></i> 5. No-Pay Leaves Logic
                        </h6>
                        <label class="form-label text-secondary fs-8 uppercase fw-semibold">Penalty Deduction Formula</label>
                        <select class="form-select form-select-custom" name="NoPayFormula" required @disabled($readOnly)>
                            <option value="BASIC_DIV_30" {{ $settings['NoPayFormula'] === 'BASIC_DIV_30' ? 'selected' : '' }}>Basic / 30 Days (Standard)</option>
                            <option value="BASIC_DIV_WORKING_DAYS" {{ $settings['NoPayFormula'] === 'BASIC_DIV_WORKING_DAYS' ? 'selected' : '' }}>Basic / Actual Working Days</option>
                            <option value="CUSTOM" {{ $settings['NoPayFormula'] === 'CUSTOM' ? 'selected' : '' }}>Custom Formula Rule</option>
                        </select>
                        <span class="fs-8 text-secondary mt-2 d-block">Configures how unpaid leave instances subtract contractual basic payments.</span>
                    </div>
                </div>

                <!-- CARD 6: LOANS -->
                <div class="col-md-6">
                    <div class="glass-card h-100">
                        <h6 class="display-font text-primary border-bottom pb-2 mb-4">
                            <i class="bi bi-cash-coin me-2"></i> 6. Loans & Advances
                        </h6>
                        <div class="form-check mb-3">
                            <input class="form-check-input check-custom" type="checkbox" name="LoanAutoDeduction" value="true" id="LoanAutoDeduction" {{ $settings['LoanAutoDeduction'] === 'true' ? 'checked' : '' }} @disabled($readOnly)>
                            <label class="form-check-label fw-semibold text-dark" for="LoanAutoDeduction">Auto-Deduct Monthly Loan Installments</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input check-custom" type="checkbox" name="AdvanceSalaryDeduction" value="true" id="AdvanceSalaryDeduction" {{ $settings['AdvanceSalaryDeduction'] === 'true' ? 'checked' : '' }} @disabled($readOnly)>
                            <label class="form-check-label fw-semibold text-dark" for="AdvanceSalaryDeduction">Auto-Recover Advance Salary Payouts</label>
                        </div>
                    </div>
                </div>

                <!-- CARD 7: APPROVALS -->
                <div class="col-md-6">
                    <div class="glass-card h-100">
                        <h6 class="display-font text-primary border-bottom pb-2 mb-4">
                            <i class="bi bi-patch-check me-2"></i> 7. Approval Hierarchies
                        </h6>
                        <div class="form-check mb-3">
                            <input class="form-check-input check-custom" type="checkbox" name="PayrollApprovalRequired" value="true" id="PayrollApprovalRequired" {{ $settings['PayrollApprovalRequired'] === 'true' ? 'checked' : '' }} @disabled($readOnly)>
                            <label class="form-check-label fw-semibold text-dark" for="PayrollApprovalRequired">Require Manager Verification to Release Slip</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input check-custom" type="checkbox" name="PayrollLockAfterApproval" value="true" id="PayrollLockAfterApproval" {{ $settings['PayrollLockAfterApproval'] === 'true' ? 'checked' : '' }} @disabled($readOnly)>
                            <label class="form-check-label fw-semibold text-dark" for="PayrollLockAfterApproval">Lock Periods and Make Data Immutable After Approval</label>
                        </div>
                    </div>
                </div>

                <!-- CARD 8: FUTURE -->
                <div class="col-md-6">
                    <div class="glass-card h-100">
                        <h6 class="display-font text-primary border-bottom pb-2 mb-4">
                            <i class="bi bi-clock me-2"></i> 8. Enterprise Roadmap Integrations
                        </h6>
                        <div class="form-check mb-3">
                            <input class="form-check-input check-custom" type="checkbox" name="EnableAPIT" value="true" id="EnableAPIT" {{ $settings['EnableAPIT'] === 'true' ? 'checked' : '' }} @disabled($readOnly)>
                            <label class="form-check-label fw-semibold text-dark" for="EnableAPIT">Enable Withholding Tax / APIT Bracket Computations</label>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input check-custom" type="checkbox" name="GratuityEnabled" value="true" id="GratuityEnabled" {{ $settings['GratuityEnabled'] === 'true' ? 'checked' : '' }} @disabled($readOnly)>
                            <label class="form-check-label fw-semibold text-dark" for="GratuityEnabled">Enable Gratuity Accrual Toggles</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input check-custom" type="checkbox" name="LeaveEncashment" value="true" id="LeaveEncashment" {{ $settings['LeaveEncashment'] === 'true' ? 'checked' : '' }} @disabled($readOnly)>
                            <label class="form-check-label fw-semibold text-dark" for="LeaveEncashment">Enable Annual Untouched Leave Encashments</label>
                        </div>
                    </div>
                </div>
            </div>

            @if(!$readOnly)
                <div class="text-end mt-4">
                    <button type="submit" class="btn btn-custom-primary px-5 py-2 fw-semibold">
                        <i class="bi bi-save me-2"></i> Save Configuration Changes
                    </button>
                </div>
            @endif
        </form>
    </div>
</div>
@endsection
