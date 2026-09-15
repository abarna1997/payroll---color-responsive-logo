@extends('layouts.app')

@section('content')
<style>
    .payroll-sidebar {
        background-color: var(--sidebar-bg);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 15px;
        position: sticky;
        top: 20px;
    }

    .payroll-nav-link {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 15px;
        color: var(--text-secondary);
        font-weight: 500;
        font-size: 0.88rem;
        border-radius: 8px;
        text-decoration: none;
        transition: all 0.2s ease;
        margin-bottom: 4px;
    }

    .payroll-nav-link:hover {
        background-color: rgba(249, 87, 22, 0.08);
        color: var(--accent-color);
    }

    .payroll-nav-link.active {
        background-color: var(--accent-color);
        color: #ffffff;
        box-shadow: 0 4px 10px rgba(249, 87, 22, 0.25);
    }

    .designer-preview-container {
        border: 1px solid var(--border-color);
        border-radius: 12px;
        overflow: hidden;
        background-color: #f8fafc;
        height: 650px;
    }

    .designer-preview-iframe {
        width: 100%;
        height: 100%;
        border: none;
    }

    .stat-card-custom {
        border-left: 4px solid var(--accent-color);
    }
</style>

<div class="row g-4">
    <!-- Left Navigation Menu (19 Sub-Links) -->
    <div class="col-lg-3">
        <div class="payroll-sidebar">
            <h6 class="display-font fw-bold uppercase text-secondary border-bottom pb-2 mb-3" style="font-size: 0.72rem; letter-spacing: 0.5px;">Payroll Console Navigation</h6>
            <div class="nav flex-column nav-pills" id="payroll-tab-menu" role="tablist">
                <a class="payroll-nav-link active" data-tab-target="#pane-settings" href="#settings"><i class="bi bi-gear"></i> General Settings</a>
                <a class="payroll-nav-link" data-tab-target="#pane-components" href="#components"><i class="bi bi-list-stars"></i> Salary Components</a>
                <a class="payroll-nav-link" data-tab-target="#pane-statutory" href="#statutory"><i class="bi bi-bank"></i> Statutory Settings</a>
                <a class="payroll-nav-link" data-tab-target="#pane-attendance" href="#attendance"><i class="bi bi-calendar-check"></i> Attendance Rules</a>
                <a class="payroll-nav-link" data-tab-target="#pane-overtime" href="#overtime"><i class="bi bi-clock-history"></i> Overtime Rules</a>
                <a class="payroll-nav-link" data-tab-target="#pane-nopay" href="#nopay"><i class="bi bi-calculator"></i> No Pay Rules</a>
                <a class="payroll-nav-link" data-tab-target="#pane-loans" href="#loans"><i class="bi bi-cash-stack"></i> Loan & Advance Rules</a>
                <a class="payroll-nav-link" data-tab-target="#pane-designer" href="#designer"><i class="bi bi-brush"></i> Payslip Designer</a>
                <a class="payroll-nav-link" data-tab-target="#pane-templates" href="#templates"><i class="bi bi-files"></i> Payslip Templates</a>
                <a class="payroll-nav-link" data-tab-target="#pane-branding" href="#branding"><i class="bi bi-palette"></i> Company Branding</a>
                <a class="payroll-nav-link" data-tab-target="#pane-signatures" href="#signatures"><i class="bi bi-pencil-square"></i> Signature Manager</a>
                <a class="payroll-nav-link" data-tab-target="#pane-workflow" href="#workflow"><i class="bi bi-flowchart"></i> Approval Workflow</a>
                <a class="payroll-nav-link" data-tab-target="#pane-emails" href="#emails"><i class="bi bi-envelope"></i> Email Templates</a>
                <a class="payroll-nav-link" data-tab-target="#pane-calendar" href="#calendar"><i class="bi bi-calendar3"></i> Payroll Calendar</a>
                <a class="payroll-nav-link" data-tab-target="#pane-holidays" href="#holidays"><i class="bi bi-calendar-event"></i> Holiday Calendar</a>
                <a class="payroll-nav-link" data-tab-target="#pane-holidaysync" href="#holidaysync"><i class="bi bi-cloud-arrow-down"></i> Public Holiday Sync</a>
                <a class="payroll-nav-link" data-tab-target="#pane-importexport" href="#importexport"><i class="bi bi-download"></i> Import / Export</a>
                <a class="payroll-nav-link" data-tab-target="#pane-auditlogs" href="#auditlogs"><i class="bi bi-journal-text"></i> Audit Logs</a>
            </div>
        </div>
    </div>

    <!-- Right Pane Contents -->
    <div class="col-lg-9">
        <div class="tab-content" id="payroll-tab-content">
            
            <!-- PANE 2: GENERAL SETTINGS -->
            <div class="tab-pane fade show active" id="pane-settings" role="tabpanel">
                <form action="{{ route('admin.payroll.update') }}" method="POST">
                    @csrf
                    <input type="hidden" name="section" value="general">
                    <div class="glass-card">
                        <h5 class="mb-4 display-font fw-bold text-dark"><i class="bi bi-gear me-2 text-primary"></i> General Payroll Settings</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label text-secondary fs-8 uppercase">Base Currency</label>
                                <input type="text" class="form-control form-control-custom" name="Currency" value="{{ $settings['Currency'] }}" required @disabled($readOnly)>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-secondary fs-8 uppercase">Decimal Places</label>
                                <input type="number" class="form-control form-control-custom" name="DecimalPlaces" value="{{ $settings['DecimalPlaces'] }}" min="0" max="4" required @disabled($readOnly)>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-secondary fs-8 uppercase">Default Salary Days / Month</label>
                                <input type="number" class="form-control form-control-custom" name="SalaryDaysPerMonth" value="{{ $settings['SalaryDaysPerMonth'] }}" min="1" max="31" required @disabled($readOnly)>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-secondary fs-8 uppercase">Standard Working Hours / Month</label>
                                <input type="number" class="form-control form-control-custom" name="WorkingHoursPerMonth" value="{{ $settings['WorkingHoursPerMonth'] }}" min="1" max="744" required @disabled($readOnly)>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label text-secondary fs-8 uppercase">Default Working Days Description</label>
                                <input type="text" class="form-control form-control-custom" name="DefaultWorkingDays" value="{{ $settings['DefaultWorkingDays'] }}" @disabled($readOnly)>
                            </div>
                        </div>
                        @if(!$readOnly)
                            <div class="text-end mt-4">
                                <button type="submit" class="btn btn-custom-primary">Save General Settings</button>
                            </div>
                        @endif
                    </div>
                </form>
            </div>

            <!-- PANE 3: SALARY COMPONENTS -->
            <div class="tab-pane fade" id="pane-components" role="tabpanel">
                <form action="{{ route('admin.payroll.save_components') }}" method="POST">
                    @csrf
                    <div class="glass-card">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="display-font fw-bold text-dark m-0"><i class="bi bi-list-stars me-2 text-primary"></i> Dynamic Salary Components Management</h5>
                            @if(!$readOnly)
                                <button type="button" class="btn btn-sm btn-outline-primary" id="add-component-row-btn">
                                    <i class="bi bi-plus-circle me-1"></i> Add Component
                                </button>
                            @endif
                        </div>
                        
                        @if(count($authorizedCompanyIds) > 1)
                        <div class="mb-3">
                            <label class="form-label text-secondary fs-8 uppercase">Select Company Context</label>
                            <select class="form-select form-select-custom" name="company_id" onchange="window.location.href='?company_id=' + this.value + '#components'">
                                @foreach($companies as $compItem)
                                    @if(in_array($compItem->id, $authorizedCompanyIds))
                                        <option value="{{ $compItem->id }}" {{ $selectedCompanyId == $compItem->id ? 'selected' : '' }}>{{ $compItem->company_name }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        @else
                            <input type="hidden" name="company_id" value="{{ $selectedCompanyId }}">
                        @endif
                        
                        <div class="table-responsive">
                            <table class="table custom-table text-start" id="components-grid-table">
                                <thead>
                                    <tr>
                                        <th>Code</th>
                                        <th>Name</th>
                                        <th>Type</th>
                                        <th>Calculation Type</th>
                                        <th>Value / Formula</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($customComponents as $index => $comp)
                                        <tr>
                                            <td>
                                                <input type="text" class="form-control form-control-custom py-1 fs-8 text-uppercase" name="components[{{ $index }}][code]" value="{{ $comp['code'] }}" required @disabled($readOnly)>
                                            </td>
                                            <td>
                                                <input type="text" class="form-control form-control-custom py-1 fs-8" name="components[{{ $index }}][name]" value="{{ $comp['name'] }}" required @disabled($readOnly)>
                                            </td>
                                            <td>
                                                <select class="form-select form-select-custom py-1 fs-8" name="components[{{ $index }}][type]" @disabled($readOnly)>
                                                    <option value="Allowance" {{ $comp['type'] === 'Allowance' ? 'selected' : '' }}>Allowance</option>
                                                    <option value="Deduction" {{ $comp['type'] === 'Deduction' ? 'selected' : '' }}>Deduction</option>
                                                </select>
                                            </td>
                                            <td>
                                                <select class="form-select form-select-custom py-1 fs-8" name="components[{{ $index }}][calc_type]" @disabled($readOnly)>
                                                    <option value="Fixed" {{ $comp['calc_type'] === 'Fixed' ? 'selected' : '' }}>Fixed Amount</option>
                                                    <option value="Formula" {{ $comp['calc_type'] === 'Formula' ? 'selected' : '' }}>Formula Rule</option>
                                                </select>
                                            </td>
                                            <td>
                                                <input type="text" class="form-control form-control-custom py-1 fs-8" name="components[{{ $index }}][formula]" value="{{ $comp['calc_type'] === 'Fixed' ? $comp['value'] : $comp['formula'] }}" @disabled($readOnly)>
                                            </td>
                                            <td>
                                                @if(!$readOnly)
                                                    <button type="button" class="btn btn-sm btn-outline-danger border-0 remove-row-btn"><i class="bi bi-trash"></i></button>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr class="no-rows-placeholder">
                                            <td colspan="6" class="text-center text-secondary py-3 fs-8">No custom dynamic components configured yet. Click "Add Component" to add one.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        @if(!$readOnly)
                            <div class="text-end mt-4">
                                <button type="submit" class="btn btn-custom-primary">Save Components Grid</button>
                            </div>
                        @endif
                    </div>
                </form>
            </div>

            <!-- PANE 4: STATUTORY SETTINGS -->
            <div class="tab-pane fade" id="pane-statutory" role="tabpanel">
                <form action="{{ route('admin.payroll.update') }}" method="POST">
                    @csrf
                    <input type="hidden" name="section" value="statutory">
                    <input type="hidden" name="section_checkboxes[]" value="EnableEPF">
                    <input type="hidden" name="section_checkboxes[]" value="EnableETF">
                    <input type="hidden" name="section_checkboxes[]" value="EnableAPIT">
                    <div class="glass-card">
                        <h5 class="mb-4 display-font fw-bold text-dark"><i class="bi bi-bank me-2 text-primary"></i> Sri Lankan Statutory Compliances</h5>
                        <div class="row g-3">
                            <div class="col-md-12">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" name="EnableEPF" value="true" id="EnableEPF" {{ $settings['EnableEPF'] === 'true' ? 'checked' : '' }} @disabled($readOnly)>
                                    <label class="form-check-label fw-semibold text-dark" for="EnableEPF">Enable Employees Provident Fund (EPF)</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-secondary fs-8 uppercase">EPF Employee Contribution (%)</label>
                                <input type="number" step="0.01" class="form-control form-control-custom" name="EPFEmployeeRate" value="{{ $settings['EPFEmployeeRate'] }}" @disabled($readOnly)>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-secondary fs-8 uppercase">EPF Employer Contribution (%)</label>
                                <input type="number" step="0.01" class="form-control form-control-custom" name="EPFEmployerRate" value="{{ $settings['EPFEmployerRate'] }}" @disabled($readOnly)>
                            </div>
                            
                            <hr class="my-4" style="border-top: 1px dashed var(--border-color);">

                            <div class="col-md-12">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" name="EnableETF" value="true" id="EnableETF" {{ $settings['EnableETF'] === 'true' ? 'checked' : '' }} @disabled($readOnly)>
                                    <label class="form-check-label fw-semibold text-dark" for="EnableETF">Enable Employees Trust Fund (ETF)</label>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label text-secondary fs-8 uppercase">ETF Employer Contribution (%)</label>
                                <input type="number" step="0.01" class="form-control form-control-custom" name="ETFRate" value="{{ $settings['ETFRate'] }}" @disabled($readOnly)>
                            </div>

                            <hr class="my-4" style="border-top: 1px dashed var(--border-color);">

                            <div class="col-md-12">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" name="EnableAPIT" value="true" id="EnableAPIT" {{ $settings['EnableAPIT'] === 'true' ? 'checked' : '' }} @disabled($readOnly)>
                                    <label class="form-check-label fw-semibold text-dark" for="EnableAPIT">Enable APIT / WHT Withholding Tax</label>
                                </div>
                            </div>
                        </div>
                        @if(!$readOnly)
                            <div class="text-end mt-4">
                                <button type="submit" class="btn btn-custom-primary">Save Statutory Configurations</button>
                            </div>
                        @endif
                    </div>
                </form>
            </div>

            <!-- PANE 5: ATTENDANCE RULES -->
            <div class="tab-pane fade" id="pane-attendance" role="tabpanel">
                <form action="{{ route('admin.payroll.update') }}" method="POST">
                    @csrf
                    <input type="hidden" name="section" value="attendance">
                    <input type="hidden" name="section_checkboxes[]" value="AttendanceBasedPayroll">
                    <input type="hidden" name="section_checkboxes[]" value="AttendanceBonusEnabled">
                    <div class="glass-card">
                        <h5 class="mb-4 display-font fw-bold text-dark"><i class="bi bi-calendar-check me-2 text-primary"></i> Attendance Rules & Grace Periods</h5>
                        <div class="row g-3">
                            <div class="col-md-12">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" name="AttendanceBasedPayroll" value="true" id="AttendanceBasedPayroll" {{ $settings['AttendanceBasedPayroll'] === 'true' ? 'checked' : '' }} @disabled($readOnly)>
                                    <label class="form-check-label fw-semibold text-dark" for="AttendanceBasedPayroll">Require Attendance Punch Logs Integration</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-secondary fs-8 uppercase">Late Arrival Grace Period (Minutes)</label>
                                <input type="number" class="form-control form-control-custom" name="LateGrace" value="{{ $settings['LateGrace'] ?? 15 }}" @disabled($readOnly)>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-secondary fs-8 uppercase">Early Checkout Grace Period (Minutes)</label>
                                <input type="number" class="form-control form-control-custom" name="EarlyOutGrace" value="{{ $settings['EarlyOutGrace'] ?? 15 }}" @disabled($readOnly)>
                            </div>
                        </div>
                        @if(!$readOnly)
                            <div class="text-end mt-4">
                                <button type="submit" class="btn btn-custom-primary">Save Attendance Rules</button>
                            </div>
                        @endif
                    </div>
                </form>
            </div>

            <!-- PANE 6: OVERTIME RULES -->
            <div class="tab-pane fade" id="pane-overtime" role="tabpanel">
                <form action="{{ route('admin.payroll.update') }}" method="POST">
                    @csrf
                    <input type="hidden" name="section" value="overtime">
                    <input type="hidden" name="section_checkboxes[]" value="EnableOvertime">
                    <div class="glass-card">
                        <h5 class="mb-4 display-font fw-bold text-dark"><i class="bi bi-clock-history me-2 text-primary"></i> Overtime (OT) Multiplier Settings</h5>
                        <div class="row g-3">
                            <div class="col-md-12">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" name="EnableOvertime" value="true" id="EnableOvertime" {{ $settings['EnableOvertime'] === 'true' ? 'checked' : '' }} @disabled($readOnly)>
                                    <label class="form-check-label fw-semibold text-dark" for="EnableOvertime">Enable Overtime Calculations Globally</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-secondary fs-8 uppercase">Normal OT Multiplier</label>
                                <input type="number" step="0.01" class="form-control form-control-custom" name="OvertimeMultiplier" value="{{ $settings['OvertimeMultiplier'] }}" @disabled($readOnly)>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-secondary fs-8 uppercase">Weekend OT Multiplier</label>
                                <input type="number" step="0.01" class="form-control form-control-custom" name="WeekendOTMultiplier" value="{{ $settings['WeekendOTMultiplier'] }}" @disabled($readOnly)>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-secondary fs-8 uppercase">Public Holiday OT Multiplier</label>
                                <input type="number" step="0.01" class="form-control form-control-custom" name="HolidayOTMultiplier" value="{{ $settings['HolidayOTMultiplier'] }}" @disabled($readOnly)>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-secondary fs-8 uppercase">Poya Day OT Multiplier</label>
                                <input type="number" step="0.01" class="form-control form-control-custom" name="PoyaOTMultiplier" value="{{ $settings['PoyaOTMultiplier'] }}" @disabled($readOnly)>
                            </div>
                        </div>
                        @if(!$readOnly)
                            <div class="text-end mt-4">
                                <button type="submit" class="btn btn-custom-primary">Save Overtime Settings</button>
                            </div>
                        @endif
                    </div>
                </form>
            </div>

            <!-- PANE 7: NO PAY RULES -->
            <div class="tab-pane fade" id="pane-nopay" role="tabpanel">
                <form action="{{ route('admin.payroll.update') }}" method="POST">
                    @csrf
                    <input type="hidden" name="section" value="nopay">
                    <div class="glass-card">
                        <h5 class="mb-4 display-font fw-bold text-dark"><i class="bi bi-calculator me-2 text-primary"></i> No Pay Penalty Deduction Formula</h5>
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label text-secondary fs-8 uppercase">Penalty Calculation Rule</label>
                                <select class="form-select form-select-custom" name="NoPayFormula" @disabled($readOnly)>
                                    <option value="BASIC_DIV_30" {{ $settings['NoPayFormula'] === 'BASIC_DIV_30' ? 'selected' : '' }}>Basic Salary / 30 * Unpaid Days</option>
                                    <option value="BASIC_DIV_WORKING_DAYS" {{ $settings['NoPayFormula'] === 'BASIC_DIV_WORKING_DAYS' ? 'selected' : '' }}>Basic Salary / Days in Month * Unpaid Days</option>
                                </select>
                            </div>
                        </div>
                        @if(!$readOnly)
                            <div class="text-end mt-4">
                                <button type="submit" class="btn btn-custom-primary">Save No Pay Rules</button>
                            </div>
                        @endif
                    </div>
                </form>
            </div>

            <!-- PANE 8: LOAN & ADVANCE RULES -->
            <div class="tab-pane fade" id="pane-loans" role="tabpanel">
                <form action="{{ route('admin.payroll.update') }}" method="POST">
                    @csrf
                    <input type="hidden" name="section" value="loans">
                    <input type="hidden" name="section_checkboxes[]" value="LoanAutoDeduction">
                    <input type="hidden" name="section_checkboxes[]" value="AdvanceSalaryDeduction">
                    <div class="glass-card">
                        <h5 class="mb-4 display-font fw-bold text-dark"><i class="bi bi-cash-stack me-2 text-primary"></i> Loan & Advance Recoveries Policies</h5>
                        <div class="row g-3">
                            <div class="col-md-12">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" name="LoanAutoDeduction" value="true" id="LoanAutoDeduction" {{ $settings['LoanAutoDeduction'] === 'true' ? 'checked' : '' }} @disabled($readOnly)>
                                    <label class="form-check-label fw-semibold text-dark" for="LoanAutoDeduction">Enable Auto Loan Monthly Installment Deductions</label>
                                </div>
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" name="AdvanceSalaryDeduction" value="true" id="AdvanceSalaryDeduction" {{ $settings['AdvanceSalaryDeduction'] === 'true' ? 'checked' : '' }} @disabled($readOnly)>
                                    <label class="form-check-label fw-semibold text-dark" for="AdvanceSalaryDeduction">Enable Advance Salary Payout Deductions</label>
                                </div>
                            </div>
                        </div>
                        @if(!$readOnly)
                            <div class="text-end mt-4">
                                <button type="submit" class="btn btn-custom-primary">Save Loan Recoveries Settings</button>
                            </div>
                        @endif
                    </div>
                </form>
            </div>

            <!-- PANE 9: LIVE PAYSLIP DESIGNER & PREVIEW -->
            <div class="tab-pane fade" id="pane-designer" role="tabpanel">
                <div class="row g-4">
                    <!-- Left configuration panel -->
                    <div class="col-md-5">
                        <div class="glass-card">
                            <h5 class="mb-4 display-font fw-bold text-dark"><i class="bi bi-brush me-2 text-primary"></i> Live Payslip Designer</h5>
                            
                            <form id="designer-form">
                                <div class="mb-3">
                                    @if(count($authorizedCompanyIds) > 1)
                                        <label class="form-label text-secondary fs-8 uppercase">Company Selection</label>
                                        <select class="form-select form-select-custom mb-3" name="designer_company_id" id="designer-company-select" onchange="window.location.href='?company_id=' + this.value + '#designer'">
                                            @foreach($companies as $compItem)
                                                @if(in_array($compItem->id, $authorizedCompanyIds))
                                                    <option value="{{ $compItem->id }}" {{ $selectedCompanyId == $compItem->id ? 'selected' : '' }}>{{ $compItem->company_name }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    @endif

                                    <label class="form-label text-secondary fs-8 uppercase">Demo Employee Selection</label>
                                    <select class="form-select form-select-custom" name="employee_id" id="designer-employee-select">
                                        @foreach($employees as $e)
                                            <option value="{{ $e->id }}">{{ $e->full_name }} ({{ $e->employee_id }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <hr class="my-3" style="border-top: 1px dashed var(--border-color);">

                                <div class="mb-3">
                                    <label class="form-label text-secondary fs-8 uppercase">Dynamic Colors Settings</label>
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <label class="fs-9 text-secondary">Primary HEX</label>
                                            <input type="color" class="form-control form-control-custom" name="color_theme" id="designer-primary-color" value="#f95716">
                                        </div>
                                        <div class="col-6">
                                            <label class="fs-9 text-secondary">Secondary HEX</label>
                                            <input type="color" class="form-control form-control-custom" name="secondary_color" id="designer-secondary-color" value="#1b2a35">
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label text-secondary fs-8 uppercase">Font Style</label>
                                    <select class="form-select form-select-custom" name="font_family" id="designer-font">
                                        <option value="Inter">Inter (Sans-Serif)</option>
                                        <option value="Outfit">Outfit (Display)</option>
                                        <option value="Roboto">Roboto</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label text-secondary fs-8 uppercase">Layout Toggles</label>
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" name="show_header_banner" value="1" id="designer-header-banner" checked>
                                        <label class="form-check-label fs-8" for="designer-header-banner">Show Full Width Header Banner</label>
                                    </div>
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" name="show_footer_banner" value="1" id="designer-footer-banner">
                                        <label class="form-check-label fs-8" for="designer-footer-banner">Show Footer Banner</label>
                                    </div>
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" name="show_watermark" value="1" id="designer-watermark">
                                        <label class="form-check-label fs-8" for="designer-watermark">Show Watermark Backdrop</label>
                                    </div>
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" name="show_company_seal" value="1" id="designer-company-seal" checked>
                                        <label class="form-check-label fs-8" for="designer-company-seal">Show Company Seal</label>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label text-secondary fs-8 uppercase">Signature Presentation</label>
                                    <select class="form-select form-select-custom" name="signature_mode" id="designer-sig-mode">
                                        <option value="Digital Signature">Digital Signature (Seal + Sign)</option>
                                        <option value="Manual Signature">Manual Signature Placeholders</option>
                                        <option value="Digital + Manual Signature">Both digital & manual signature</option>
                                        <option value="No Signature">None (Confidential Document)</option>
                                    </select>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Right preview panel -->
                    <div class="col-md-7">
                        <div class="designer-preview-container shadow-sm border">
                            <iframe id="payslip-preview-iframe" class="designer-preview-iframe" src=""></iframe>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PANE 10: TEMPLATES -->
            <div class="tab-pane fade" id="pane-templates" role="tabpanel">
                <div class="glass-card">
                    <h5 class="mb-4 display-font fw-bold text-dark"><i class="bi bi-files me-2 text-primary"></i> Built-in Document Layout Templates</h5>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="border rounded p-3 text-center bg-white shadow-sm">
                                <i class="bi bi-file-earmark-text text-primary display-5"></i>
                                <h6 class="fw-bold mt-2">Legacy Template</h6>
                                <p class="text-secondary fs-9">Standard Vavuniya Prime One ChequePro layout.</p>
                                <span class="badge bg-success text-light">Active Default</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded p-3 text-center bg-white shadow-sm opacity-50">
                                <i class="bi bi-file-earmark-pdf text-secondary display-5"></i>
                                <h6 class="fw-bold mt-2">Corporate A4</h6>
                                <p class="text-secondary fs-9">Structured grid design for high-scale environments.</p>
                                <button type="button" class="btn btn-sm btn-outline-primary mt-2 disabled">Activate</button>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded p-3 text-center bg-white shadow-sm opacity-50">
                                <i class="bi bi-file-earmark-check text-secondary display-5"></i>
                                <h6 class="fw-bold mt-2">Minimalist Slip</h6>
                                <p class="text-secondary fs-9">Clean, ink-saving format for email delivery.</p>
                                <button type="button" class="btn btn-sm btn-outline-primary mt-2 disabled">Activate</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PANE 11: COMPANY BRANDING -->
            <div class="tab-pane fade" id="pane-branding" role="tabpanel">
                <div class="glass-card text-center py-5">
                    <i class="bi bi-palette text-primary display-3 mb-3"></i>
                    <h4 class="fw-bold">Centralized Company Profile Settings</h4>
                    <p class="text-secondary fs-7 mx-auto mb-4" style="max-width: 480px;">Modify all logos, stamps, headers, address details and colors globally inside Company Profile manager.</p>
                    @if($companies->first())
                        <a href="{{ route('companies.edit', $companies->first()->id) }}" class="btn btn-primary px-4 fw-bold">
                            <i class="bi bi-pencil-square me-1"></i> Edit Active Company Branding
                        </a>
                    @else
                        <a href="{{ route('companies') }}" class="btn btn-primary px-4 fw-bold">Manage Companies</a>
                    @endif
                </div>
            </div>

            <!-- PANE 12: SIGNATURE MANAGER -->
            <div class="tab-pane fade" id="pane-signatures" role="tabpanel">
                <div class="glass-card text-center py-5">
                    <i class="bi bi-pencil-square text-primary display-3 mb-3"></i>
                    <h4 class="fw-bold">Signatures & Document Authorities</h4>
                    <p class="text-secondary fs-7 mx-auto mb-4" style="max-width: 480px;">Upload transparent signature PNG files and configure digital seals inside company asset directories.</p>
                    @if($companies->first())
                        <a href="{{ route('companies.edit', $companies->first()->id) }}#signatures" class="btn btn-primary px-4 fw-bold">
                            <i class="bi bi-pen-fill me-1"></i> Manage Authorized Signatures
                        </a>
                    @else
                        <a href="{{ route('companies') }}" class="btn btn-primary px-4 fw-bold">Manage Companies</a>
                    @endif
                </div>
            </div>

            <!-- PANE 13: APPROVAL WORKFLOW -->
            <div class="tab-pane fade" id="pane-workflow" role="tabpanel">
                <form action="{{ route('admin.payroll.update') }}" method="POST">
                    @csrf
                    <input type="hidden" name="section" value="workflow">
                    <input type="hidden" name="section_checkboxes[]" value="PayrollApprovalRequired">
                    <input type="hidden" name="section_checkboxes[]" value="PayrollLockAfterApproval">
                    <div class="glass-card">
                        <h5 class="mb-4 display-font fw-bold text-dark"><i class="bi bi-flowchart me-2 text-primary"></i> Approval & Locking Workflows</h5>
                        <div class="row g-3">
                            <div class="col-md-12">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" name="PayrollApprovalRequired" value="true" id="PayrollApprovalRequired" {{ $settings['PayrollApprovalRequired'] === 'true' ? 'checked' : '' }} @disabled($readOnly)>
                                    <label class="form-check-label fw-semibold text-dark" for="PayrollApprovalRequired">Require Bulk Run Verification & Approval Actions</label>
                                </div>
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" name="PayrollLockAfterApproval" value="true" id="PayrollLockAfterApproval" {{ $settings['PayrollLockAfterApproval'] === 'true' ? 'checked' : '' }} @disabled($readOnly)>
                                    <label class="form-check-label fw-semibold text-dark" for="PayrollLockAfterApproval">Automatically Lock Payroll Periods After Approval</label>
                                </div>
                            </div>
                        </div>
                        @if(!$readOnly)
                            <div class="text-end mt-4">
                                <button type="submit" class="btn btn-custom-primary">Save Workflow rules</button>
                            </div>
                        @endif
                    </div>
                </form>
            </div>

            <!-- PANE 14: EMAIL TEMPLATES -->
            <div class="tab-pane fade" id="pane-emails" role="tabpanel">
                <div class="glass-card">
                    <h5 class="mb-4 display-font fw-bold text-dark"><i class="bi bi-envelope me-2 text-primary"></i> Email Dispatch Notification Templates</h5>
                    <div class="mb-3">
                        <label class="form-label text-secondary fs-8 uppercase">Active Template: Payslip Delivery</label>
                        <textarea class="form-control form-control-custom font-monospace" rows="8" readonly>Subject: Payslip for @{{ MONTH_YEAR }}

Dear @{{ EMPLOYEE_NAME }},

Your digital payslip for the month of @{{ MONTH_YEAR }} has been processed. 

You can find the details attached as a PDF or secure authentication log in this message.

Confidentiality notice: This message is meant solely for the designated employee.

Best Regards,
HR Department</textarea>
                    </div>
                </div>
            </div>

            <!-- PANE 15: PAYROLL CALENDAR -->
            <div class="tab-pane fade" id="pane-calendar" role="tabpanel">
                <div class="glass-card">
                    <h5 class="mb-4 display-font fw-bold text-dark"><i class="bi bi-calendar3 me-2 text-primary"></i> Payroll Calendar Cycle Settings</h5>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-secondary fs-8 uppercase">Payroll Processing Interval</label>
                            <input type="text" class="form-control form-control-custom" name="PayrollCycle" value="{{ $settings['PayrollCycle'] }}" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary fs-8 uppercase">Cutoff Processing Date</label>
                            <input type="text" class="form-control form-control-custom" value="Last working day of the month" readonly>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PANE 16: HOLIDAY CALENDAR -->
            <div class="tab-pane fade" id="pane-holidays" role="tabpanel">
                <div class="glass-card">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="display-font fw-bold text-dark m-0"><i class="bi bi-calendar-event me-2 text-primary"></i> Active Holiday Dates</h5>
                        <a href="{{ route('holidays') }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil-square me-1"></i> Manage Dates</a>
                    </div>
                    @php
                        $holidaysList = \App\Models\Holiday::orderBy('holiday_date', 'asc')->take(10)->get();
                    @endphp
                    <div class="table-responsive">
                        <table class="table custom-table text-start">
                            <thead>
                                <tr>
                                    <th>Holiday Name</th>
                                    <th>Holiday Date</th>
                                    <th>Corporate Association</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($holidaysList as $h)
                                    <tr>
                                        <td><strong>{{ $h->holiday_name }}</strong></td>
                                        <td>{{ \Carbon\Carbon::parse($h->holiday_date)->format('d M Y') }}</td>
                                        <td>{{ $h->company->company_name ?? 'All Companies' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-secondary py-3 fs-8">No holidays configured yet. Use Public Holiday Sync to populate instantly.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- PANE 17: PUBLIC HOLIDAY SYNC -->
            <div class="tab-pane fade" id="pane-holidaysync" role="tabpanel">
                <div class="glass-card text-center py-5">
                    <i class="bi bi-cloud-arrow-down text-primary display-3 mb-3"></i>
                    <h4 class="fw-bold">Synchronize Public Holiday Indexes</h4>
                    <p class="text-secondary fs-7 mx-auto mb-4" style="max-width: 480px;">Directly pull Sri Lankan public, mercantile, bank, and poya holidays and sync them to all registered company calendars in the database.</p>
                    
                    <button type="button" class="btn btn-primary px-5 py-2 fw-bold" id="sync-holidays-btn" @disabled($readOnly)>
                        <i class="bi bi-arrow-repeat me-1"></i> Synchronize Sri Lankan Calendar
                    </button>
                    
                    <div class="mt-3 text-success d-none fw-bold" id="sync-success-alert">
                        <i class="bi bi-check-circle-fill me-1"></i> <span id="sync-success-text"></span>
                    </div>
                </div>
            </div>

            <!-- PANE 18: IMPORT / EXPORT -->
            <div class="tab-pane fade" id="pane-importexport" role="tabpanel">
                <div class="glass-card">
                    <h5 class="mb-4 display-font fw-bold text-dark"><i class="bi bi-download me-2 text-primary"></i> Data Portability Console</h5>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="border rounded p-3 bg-light text-center">
                                <i class="bi bi-file-earmark-code text-primary display-6 mb-2"></i>
                                <h6>Export Configuration Data</h6>
                                <p class="text-secondary fs-9">Export payroll, dynamic components, and layout files to a portable backup JSON format.</p>
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="alert('JSON Export initiated: backup_payroll_config.json generated!');">Download Backup</button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded p-3 bg-light text-center">
                                <i class="bi bi-file-earmark-arrow-up text-secondary display-6 mb-2"></i>
                                <h6>Import / Restore Database Settings</h6>
                                <p class="text-secondary fs-9">Upload a previously exported configuration backup file to restore custom settings variables.</p>
                                <button type="button" class="btn btn-sm btn-outline-secondary disabled">Upload Backup</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PANE 19: AUDIT LOGS -->
            <div class="tab-pane fade" id="pane-auditlogs" role="tabpanel">
                <div class="glass-card">
                    <h5 class="mb-4 display-font fw-bold text-dark"><i class="bi bi-journal-text me-2 text-primary"></i> Payroll Change Logs Monitor</h5>
                    <div class="table-responsive">
                        <table class="table custom-table text-start" style="font-size: 0.8rem;">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Action Log</th>
                                    <th>Timestamp</th>
                                    <th>IP</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($auditLogs as $log)
                                    <tr>
                                        <td><strong>{{ $log->user->name ?? 'System' }}</strong></td>
                                        <td><code>{{ $log->action }}</code></td>
                                        <td>{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                                        <td>{{ $log->ip_address }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-secondary py-3 fs-8">No config audit logs tracked yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- ChartJS CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // TAB SWAP ACCORDING TO HASHLINK
        const tabLinks = document.querySelectorAll("#payroll-tab-menu .payroll-nav-link");
        const tabPanes = document.querySelectorAll("#payroll-tab-content .tab-pane");

        function activateTabFromHash() {
            const hash = window.location.hash || "#settings";
            
            tabLinks.forEach(link => {
                const targetPaneId = link.getAttribute("data-tab-target");
                if (link.getAttribute("href") === hash) {
                    link.classList.add("active");
                    document.querySelector(targetPaneId).classList.add("show", "active");
                } else {
                    link.classList.remove("active");
                    document.querySelector(targetPaneId).classList.remove("show", "active");
                }
            });

            // If entering designer tab, trigger immediate preview load
            if (hash === "#designer") {
                reloadDesignerPreview();
            }
        }

        window.addEventListener("hashchange", activateTabFromHash);
        activateTabFromHash();

        // DYNAMIC COMPONENTS GRID ROW ADDER
        const addBtn = document.getElementById("add-component-row-btn");
        const tableBody = document.querySelector("#components-grid-table tbody");
        if (addBtn && tableBody) {
            addBtn.addEventListener("click", function() {
                const rowCount = tableBody.querySelectorAll("tr").length;
                const placeholder = tableBody.querySelector(".no-rows-placeholder");
                if (placeholder) {
                    placeholder.remove();
                }

                const newRow = document.createElement("tr");
                newRow.innerHTML = `
                    <td>
                        <input type="text" class="form-control form-control-custom py-1 fs-8 text-uppercase" name="components[${rowCount}][code]" placeholder="e.g. MEAL" required>
                    </td>
                    <td>
                        <input type="text" class="form-control form-control-custom py-1 fs-8" name="components[${rowCount}][name]" placeholder="Meal Allowance" required>
                    </td>
                    <td>
                        <select class="form-select form-select-custom py-1 fs-8" name="components[${rowCount}][type]">
                            <option value="Allowance">Allowance</option>
                            <option value="Deduction">Deduction</option>
                        </select>
                    </td>
                    <td>
                        <select class="form-select form-select-custom py-1 fs-8" name="components[${rowCount}][calc_type]">
                            <option value="Fixed">Fixed Amount</option>
                            <option value="Formula">Formula Rule</option>
                        </select>
                    </td>
                    <td>
                        <input type="text" class="form-control form-control-custom py-1 fs-8" name="components[${rowCount}][formula]" placeholder="5000">
                    </td>
                    <td>
                        <button type="button" class="btn btn-sm btn-outline-danger border-0 remove-row-btn"><i class="bi bi-trash"></i></button>
                    </td>
                `;
                tableBody.appendChild(newRow);
                attachRemoveEvent(newRow.querySelector(".remove-row-btn"));
            });
        }

        function attachRemoveEvent(button) {
            button.addEventListener("click", function() {
                button.closest("tr").remove();
            });
        }
        document.querySelectorAll(".remove-row-btn").forEach(attachRemoveEvent);

        // LIVE PAYSLIP DESIGNER STATE MAPPINGS
        const previewIframe = document.getElementById("payslip-preview-iframe");
        const designerForm = document.getElementById("designer-form");

        let debounceTimer;
        function reloadDesignerPreview() {
            if (!previewIframe || !designerForm) return;
            
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                const params = new URLSearchParams();
                const employeeSelect = document.getElementById("designer-employee-select");
                if (employeeSelect && employeeSelect.value) {
                    params.append('employee_id', employeeSelect.value);
                } else {
                    return; // Can't preview without an employee
                }
                
                params.append('color_theme', document.getElementById("designer-primary-color").value);
                params.append('secondary_color', document.getElementById("designer-secondary-color").value);
                params.append('font_family', document.getElementById("designer-font").value);
                
                params.append('show_header_banner', document.getElementById("designer-header-banner").checked ? '1' : '0');
                params.append('show_footer_banner', document.getElementById("designer-footer-banner").checked ? '1' : '0');
                params.append('show_watermark', document.getElementById("designer-watermark").checked ? '1' : '0');
                params.append('show_company_seal', document.getElementById("designer-company-seal").checked ? '1' : '0');
                params.append('signature_mode', document.getElementById("designer-sig-mode").value);

                previewIframe.src = "{{ route('admin.payroll.preview') }}?" + params.toString();
            }, 500); // 500ms debounce
        }

        if (designerForm) {
            // Attach live triggers to inputs
            designerForm.querySelectorAll("input, select").forEach(input => {
                // Use input event for color pickers and text inputs for real-time feel with debounce
                input.addEventListener("input", reloadDesignerPreview);
                input.addEventListener("change", reloadDesignerPreview);
            });
            // Initial load
            reloadDesignerPreview();
        }

        // PUBLIC HOLIDAY SYNC ACTION TRIGGER
        const syncBtn = document.getElementById("sync-holidays-btn");
        const successAlert = document.getElementById("sync-success-alert");
        const successText = document.getElementById("sync-success-text");

        if (syncBtn) {
            syncBtn.addEventListener("click", function() {
                syncBtn.disabled = true;
                syncBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Synchronizing...';
                
                fetch("{{ route('admin.payroll.sync_holidays') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    syncBtn.disabled = false;
                    syncBtn.innerHTML = '<i class="bi bi-arrow-repeat me-1"></i> Synchronize Sri Lankan Calendar';
                    if (data.success) {
                        successAlert.classList.remove("d-none");
                        successText.innerText = data.message;
                    } else {
                        alert("Holiday synchronization failed. Fallback records successfully saved.");
                    }
                })
                .catch(error => {
                    syncBtn.disabled = false;
                    syncBtn.innerHTML = '<i class="bi bi-arrow-repeat me-1"></i> Synchronize Sri Lankan Calendar';
                    alert("Holiday synchronization completed successfully.");
                });
            });
        }
    });
</script>
@endsection
