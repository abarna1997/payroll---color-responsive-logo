@extends('layouts.app')

@section('content')
<div class="row g-4">
    <div class="col-12">
        <div class="glass-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="m-0 display-font fw-bold"><i class="bi bi-person-fill-gear me-2 text-primary"></i> Employee Salary Profiles</h5>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-custom-primary" data-bs-toggle="modal" data-bs-target="#bulkUpdateModal">
                        <i class="bi bi-pencil-square me-1"></i> Bulk Update
                    </button>
                    <a href="{{ route('payroll.index') }}" class="btn btn-custom-secondary">
                        <i class="bi bi-arrow-left me-1"></i> Back to Payroll
                    </a>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table custom-table">
                    <thead>
                        <tr>
                            <th>Employee ID</th>
                            <th>Name</th>
                            <th>Company / Department</th>
                            <th>Basic Salary</th>
                            <th>Incentive (KPI)</th>
                            <th>Allowances</th>
                            <th>Deductions</th>
                            <th>EPF/ETF</th>
                            <th>Setup</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($employees as $e)
                            <tr>
                                <td><span class="badge bg-indigo text-light">{{ $e->employee_id }}</span></td>
                                <td class="fw-semibold">{{ $e->full_name }}</td>
                                <td>
                                    <div>{{ $e->company->company_name ?? '-' }}</div>
                                    <div class="text-secondary fs-8">{{ $e->department->department_name ?? '-' }}</div>
                                </td>
                                <td>
                                    @if($e->salaryProfile)
                                        Rs. {{ number_format($e->salaryProfile->basic_salary, 2) }}
                                    @else
                                        <span class="text-danger fs-8">Not Setup</span>
                                    @endif
                                </td>
                                <td>
                                    @if($e->salaryProfile)
                                        Rs. {{ number_format($e->salaryProfile->incentive, 2) }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>
                                    @if($e->salaryProfile)
                                        Rs. {{ number_format($e->salaryProfile->getFixedAllowancesSum(), 2) }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>
                                    @if($e->salaryProfile)
                                        Rs. {{ number_format($e->salaryProfile->getFixedDeductionsSum(), 2) }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>
                                    @if($e->salaryProfile)
                                        @if($e->salaryProfile->epf_eligible)
                                            <span class="badge bg-success me-1">EPF</span>
                                        @endif
                                        @if($e->salaryProfile->etf_eligible)
                                            <span class="badge bg-info text-dark">ETF</span>
                                        @endif
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-outline-primary border-0" data-bs-toggle="modal" data-bs-target="#profileModal{{ $e->id }}">
                                        <i class="bi bi-pencil"></i> Configure
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-secondary py-4">No active employees found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Profiles Setup Modals -->
@foreach($employees as $e)
    @php
        $profile = $e->salaryProfile;
        $allowancesArr = [];
        if ($profile && is_array($profile->allowances_json)) {
            foreach ($profile->allowances_json as $item) {
                $allowancesArr[$item['name']] = $item['amount'];
            }
        }
        $deductionsArr = [];
        if ($profile && is_array($profile->deductions_json)) {
            foreach ($profile->deductions_json as $item) {
                $deductionsArr[$item['name']] = $item['amount'];
            }
        }
    @endphp
    <div class="modal fade" id="profileModal{{ $e->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content glass-card p-0" style="background-color: var(--sidebar-bg); border: 1px solid var(--border-color); color: white;">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title display-font text-white"><i class="bi bi-wallet me-2 text-primary"></i> Setup Salary Profile - {{ $e->full_name }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body text-start pt-0">
                    <!-- Nav tabs -->
                    <ul class="nav nav-tabs custom-tabs mt-3 border-secondary border-opacity-25" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#active-tab-{{ $e->id }}" type="button" role="tab">Active Profile & Config</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#history-tab-{{ $e->id }}" type="button" role="tab">Profile History</button>
                        </li>
                    </ul>

                    <!-- Tab panes -->
                    <div class="tab-content pt-4">
                        <div class="tab-pane fade show active" id="active-tab-{{ $e->id }}" role="tabpanel">
                            <form action="{{ route('payroll.profiles.update', $e->id) }}" method="POST">
                                @csrf
                                
                                <div class="row mb-4 bg-dark bg-opacity-25 p-3 rounded border border-secondary border-opacity-25">
                                    <div class="col-md-6">
                                        <label class="form-label text-light fs-8 text-uppercase">Effective From Date</label>
                                        <input type="date" class="form-control form-control-custom" name="effective_from" value="{{ date('Y-m-d') }}" required>
                                        <small class="text-secondary fs-9">This determines when the new profile version takes effect.</small>
                                    </div>
                                    <div class="col-md-6 text-end">
                                        @if($profile)
                                            <div class="text-secondary fs-8 mt-4">Current Profile Version: <span class="text-white fw-bold">{{ $profile->effective_from->format('Y-m-d') }}</span></div>
                                        @endif
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <div class="col-md-4">
                                        <label class="form-label text-light fs-8 text-uppercase">Basic Contractual Salary</label>
                                        <input type="number" step="0.01" class="form-control form-control-custom" name="basic_salary" value="{{ $profile ? $profile->basic_salary : 0 }}" required>
                                    </div>
                                    <div class="col-md-8 d-flex align-items-end pb-1">
                                        <div>
                                            <div class="form-check form-check-inline me-4">
                                                <input class="form-check-input check-custom border-success" type="checkbox" name="epf_eligible" value="1" id="epf{{ $e->id }}" {{ !$profile || $profile->epf_eligible ? 'checked' : '' }}>
                                                <label class="form-check-label text-light" for="epf{{ $e->id }}">EPF Eligible ({{ $epfEmpRate }}% / {{ $epfEmprRate }}%)</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input check-custom border-info" type="checkbox" name="etf_eligible" value="1" id="etf{{ $e->id }}" {{ !$profile || $profile->etf_eligible ? 'checked' : '' }}>
                                                <label class="form-check-label text-light" for="etf{{ $e->id }}">ETF Eligible ({{ $etfRate }}%)</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-4 mb-4">
                                    <!-- Allowances Section -->
                                    <div class="col-md-4">
                                        <div class="p-3 border border-secondary border-opacity-25 rounded bg-dark bg-opacity-10 h-100">
                                            <h6 class="display-font text-white border-bottom border-secondary border-opacity-25 pb-2 mb-3"><i class="bi bi-plus-circle text-success me-2"></i> Allowances (Earnings)</h6>
                                            <div class="mb-3">
                                                <label class="form-label text-light fs-8">Transport Allowance</label>
                                                <input type="number" step="0.01" class="form-control form-control-custom" name="allowances[Transport]" value="{{ $allowancesArr['Transport'] ?? '' }}">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label text-light fs-8">Meal Allowance</label>
                                                <input type="number" step="0.01" class="form-control form-control-custom" name="allowances[Meal]" value="{{ $allowancesArr['Meal'] ?? '' }}">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label text-light fs-8">Communication Allowance</label>
                                                <input type="number" step="0.01" class="form-control form-control-custom" name="allowances[Communication]" value="{{ $allowancesArr['Communication'] ?? '' }}">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label text-light fs-8">KPI/Performance Incentive</label>
                                                <input type="number" step="0.01" class="form-control form-control-custom" name="incentive" value="{{ $profile ? $profile->incentive : '' }}">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Deductions Section -->
                                    <div class="col-md-4">
                                        <div class="p-3 border border-secondary border-opacity-25 rounded bg-dark bg-opacity-10 h-100">
                                            <h6 class="display-font text-white border-bottom border-secondary border-opacity-25 pb-2 mb-3"><i class="bi bi-dash-circle text-danger me-2"></i> Fixed Deductions</h6>
                                            <div class="mb-3">
                                                <label class="form-label text-light fs-8">Salary Advance Recovery</label>
                                                <input type="number" step="0.01" class="form-control form-control-custom" name="deductions[Advance]" value="{{ $deductionsArr['Advance'] ?? '' }}">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label text-light fs-8">Loan Installment</label>
                                                <input type="number" step="0.01" class="form-control form-control-custom" name="deductions[Loan]" value="{{ $deductionsArr['Loan'] ?? '' }}">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Tax Profile Section -->
                                    <div class="col-md-4">
                                        <div class="p-3 border border-secondary border-opacity-25 rounded bg-dark bg-opacity-10 h-100">
                                            <h6 class="display-font text-white border-bottom border-secondary border-opacity-25 pb-2 mb-3"><i class="bi bi-bank2 text-warning me-2"></i> Tax Profile</h6>
                                            
                                            <div class="mb-3">
                                                <label class="form-label text-light fs-8">Employment Type</label>
                                                <select class="form-select form-select-custom" name="tax_employment_type">
                                                    <option value="Primary" {{ $e->tax_employment_type == 'Primary' ? 'selected' : '' }}>Primary Employment</option>
                                                    <option value="Secondary" {{ $e->tax_employment_type == 'Secondary' ? 'selected' : '' }}>Secondary Employment</option>
                                                </select>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label text-light fs-8">Residency Status</label>
                                                <select class="form-select form-select-custom" name="tax_residency">
                                                    <option value="Resident" {{ $e->tax_residency == 'Resident' ? 'selected' : '' }}>Resident</option>
                                                    <option value="Non-Resident" {{ $e->tax_residency == 'Non-Resident' ? 'selected' : '' }}>Non-Resident</option>
                                                </select>
                                            </div>

                                            <div class="form-check mt-4">
                                                <input class="form-check-input check-custom" type="checkbox" name="tax_cumulative_enabled" value="1" id="tax_cum_{{ $e->id }}" {{ $e->tax_cumulative_enabled ? 'checked' : '' }}>
                                                <label class="form-check-label text-light" for="tax_cum_{{ $e->id }}">Enable Cumulative Tax (APIT)</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-end gap-2 mt-4">
                                    <button type="button" class="btn btn-custom-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-custom-primary"><i class="bi bi-save me-1"></i> Save Version</button>
                                </div>
                            </form>
                        </div>

                        <div class="tab-pane fade" id="history-tab-{{ $e->id }}" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table custom-table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Status</th>
                                            <th>Effective From</th>
                                            <th>Effective To</th>
                                            <th>Basic Salary</th>
                                            <th>Incentive</th>
                                            <th>Allow/Ded</th>
                                            <th>EPF/ETF</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($e->salaryProfiles as $sp)
                                            <tr>
                                                <td>
                                                    @if($sp->status == 'Active')
                                                        <span class="badge bg-success">Active</span>
                                                    @else
                                                        <span class="badge bg-secondary">Historical</span>
                                                    @endif
                                                </td>
                                                <td>{{ $sp->effective_from ? $sp->effective_from->format('Y-m-d') : '-' }}</td>
                                                <td>{{ $sp->effective_to ? $sp->effective_to->format('Y-m-d') : 'Present' }}</td>
                                                <td>Rs. {{ number_format($sp->basic_salary, 2) }}</td>
                                                <td>Rs. {{ number_format($sp->incentive, 2) }}</td>
                                                <td>
                                                    <span class="text-success">+{{ number_format($sp->getFixedAllowancesSum(), 2) }}</span> / 
                                                    <span class="text-danger">-{{ number_format($sp->getFixedDeductionsSum(), 2) }}</span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-dark border {{ $sp->epf_eligible ? 'border-success text-success' : 'border-secondary text-secondary' }}">EPF</span>
                                                    <span class="badge bg-dark border {{ $sp->etf_eligible ? 'border-info text-info' : 'border-secondary text-secondary' }}">ETF</span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center text-secondary py-3">No history available.</td>
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
    </div>
@endforeach

<!-- Bulk Update Modal -->
<div class="modal fade" id="bulkUpdateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content glass-card p-0" style="background-color: var(--sidebar-bg); border: 1px solid var(--border-color); color: white;">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title display-font text-white"><i class="bi bi-pencil-square me-2 text-primary"></i> Bulk Update Salary Profiles</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form action="{{ route('payroll.profiles.bulk-update') }}" method="POST" id="bulkUpdateForm">
                @csrf
                <div class="modal-body text-start">
                    
                    <!-- Wizard Progress -->
                    <div class="d-flex justify-content-between mb-4 border-bottom border-secondary border-opacity-25 pb-3 px-3">
                        <div class="text-center w-100 position-relative">
                            <div class="fw-bold text-primary mb-1"><i class="bi bi-1-circle"></i> Selection</div>
                            <div class="fs-9 text-secondary">Choose Employees</div>
                        </div>
                        <div class="text-center w-100 position-relative">
                            <div class="fw-bold text-primary mb-1"><i class="bi bi-2-circle"></i> Configuration</div>
                            <div class="fs-9 text-secondary">Set Fields & Date</div>
                        </div>
                        <div class="text-center w-100 position-relative">
                            <div class="fw-bold text-primary mb-1"><i class="bi bi-3-circle"></i> Preview</div>
                            <div class="fs-9 text-secondary">Verify Changes</div>
                        </div>
                    </div>

                    <div class="row g-4 px-2">
                        <!-- Left Panel: Employee Selection -->
                        <div class="col-md-4 border-end border-secondary border-opacity-25 pe-md-3">
                            <h6 class="display-font text-white mb-3"><i class="bi bi-people me-2"></i> Select Employees</h6>
                            
                            <div class="mb-3">
                                <input type="text" id="bulk-employee-search" class="form-control form-control-custom" placeholder="Search by name or ID...">
                            </div>

                            <div class="form-check mb-3">
                                <input class="form-check-input check-custom" type="checkbox" id="select-all-bulk-employees">
                                <label class="form-check-label fw-bold text-white" for="select-all-bulk-employees">Select All Employees</label>
                            </div>

                            <div class="overflow-auto border rounded border-secondary border-opacity-25 p-2" style="max-height: 400px; background: rgba(0,0,0,0.15);">
                                <div id="bulk-employee-list">
                                    @foreach($employees as $e)
                                        <div class="form-check mb-2 bulk-employee-row" data-name="{{ strtolower($e->full_name) }}" data-id="{{ strtolower($e->employee_id) }}">
                                            <input class="form-check-input check-custom bulk-employee-checkbox" type="checkbox" name="employee_ids[]" value="{{ $e->id }}" id="bulk-emp-{{ $e->id }}">
                                            <label class="form-check-label text-secondary fs-8" for="bulk-emp-{{ $e->id }}">
                                                <span class="text-white fw-semibold">{{ $e->full_name }}</span> ({{ $e->employee_id }})
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <!-- Middle Panel: Fields to Update -->
                        <div class="col-md-5 border-end border-secondary border-opacity-25 px-md-3">
                            <h6 class="display-font text-white mb-3"><i class="bi bi-sliders me-2"></i> Fields to Update</h6>
                            
                            <div class="mb-4">
                                <label class="form-label text-light fs-8 text-uppercase">Effective From Date</label>
                                <input type="date" class="form-control form-control-custom border-primary" name="effective_from" id="bulk_effective_from" value="{{ date('Y-m-d') }}" required>
                            </div>

                            <!-- Basic Salary -->
                            <div class="card p-3 mb-3 border-secondary border-opacity-25" style="background: rgba(255,255,255,0.02);">
                                <div class="form-check mb-2">
                                    <input class="form-check-input check-custom field-toggle" type="checkbox" name="update_basic_salary" id="toggle_basic_salary" value="1">
                                    <label class="form-check-label fw-bold text-white" for="toggle_basic_salary">Update Basic Salary</label>
                                </div>
                                <input type="number" step="0.01" class="form-control form-control-custom bulk-field-input" name="basic_salary" id="input_basic_salary" placeholder="Amount" disabled>
                            </div>

                            <!-- EPF / ETF -->
                            <div class="card p-3 mb-3 border-secondary border-opacity-25" style="background: rgba(255,255,255,0.02);">
                                <div class="row">
                                    <div class="col-6 border-end border-secondary border-opacity-25">
                                        <div class="form-check mb-2">
                                            <input class="form-check-input check-custom field-toggle" type="checkbox" name="update_epf_eligible" id="toggle_epf" value="1">
                                            <label class="form-check-label fw-bold text-white" for="toggle_epf">Update EPF</label>
                                        </div>
                                        <div class="form-check mt-2">
                                            <input class="form-check-input check-custom bulk-field-input" type="checkbox" name="epf_eligible" id="input_epf" value="1" disabled>
                                            <label class="form-check-label fs-9 text-light" for="input_epf">EPF Eligible</label>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-check mb-2">
                                            <input class="form-check-input check-custom field-toggle" type="checkbox" name="update_etf_eligible" id="toggle_etf" value="1">
                                            <label class="form-check-label fw-bold text-white" for="toggle_etf">Update ETF</label>
                                        </div>
                                        <div class="form-check mt-2">
                                            <input class="form-check-input check-custom bulk-field-input" type="checkbox" name="etf_eligible" id="input_etf" value="1" disabled>
                                            <label class="form-check-label fs-9 text-light" for="input_etf">ETF Eligible</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Allowances Section -->
                            <div class="card p-3 mb-3 border-secondary border-opacity-25" style="background: rgba(255,255,255,0.02);">
                                <div class="form-check mb-3">
                                    <input class="form-check-input check-custom field-toggle" type="checkbox" name="update_allowances" id="toggle_allowances" value="1">
                                    <label class="form-check-label fw-bold text-white" for="toggle_allowances">Update Allowances</label>
                                </div>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <label class="form-label text-light fs-9">Transport</label>
                                        <input type="number" step="0.01" class="form-control form-control-sm form-control-custom bulk-field-input mb-2" name="allowances[Transport]" id="input_allowance_transport" disabled>
                                        
                                        <label class="form-label text-light fs-9">Meal</label>
                                        <input type="number" step="0.01" class="form-control form-control-sm form-control-custom bulk-field-input" name="allowances[Meal]" id="input_allowance_meal" disabled>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label text-light fs-9">Communication</label>
                                        <input type="number" step="0.01" class="form-control form-control-sm form-control-custom bulk-field-input mb-2" name="allowances[Communication]" id="input_allowance_communication" disabled>
                                        
                                        <label class="form-label text-light fs-9">Incentive</label>
                                        <input type="number" step="0.01" class="form-control form-control-sm form-control-custom bulk-field-input" name="incentive" id="input_incentive" disabled>
                                    </div>
                                </div>
                            </div>

                            <!-- Deductions Section -->
                            <div class="card p-3 mb-3 border-secondary border-opacity-25" style="background: rgba(255,255,255,0.02);">
                                <div class="form-check mb-3">
                                    <input class="form-check-input check-custom field-toggle" type="checkbox" name="update_deductions" id="toggle_deductions" value="1">
                                    <label class="form-check-label fw-bold text-white" for="toggle_deductions">Update Deductions</label>
                                </div>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <label class="form-label text-light fs-9">Advance</label>
                                        <input type="number" step="0.01" class="form-control form-control-sm form-control-custom bulk-field-input" name="deductions[Advance]" id="input_deduction_advance" disabled>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label text-light fs-9">Loan</label>
                                        <input type="number" step="0.01" class="form-control form-control-sm form-control-custom bulk-field-input" name="deductions[Loan]" id="input_deduction_loan" disabled>
                                    </div>
                                </div>
                            </div>

                            <!-- Tax Profile -->
                            <div class="card p-3 border-secondary border-opacity-25" style="background: rgba(255,255,255,0.02);">
                                <div class="form-check mb-3">
                                    <input class="form-check-input check-custom field-toggle" type="checkbox" name="update_tax_profile" id="toggle_tax_profile" value="1">
                                    <label class="form-check-label fw-bold text-white" for="toggle_tax_profile">Update Tax Profile</label>
                                </div>
                                <div class="row g-2">
                                    <div class="col-12">
                                        <select class="form-select form-select-sm form-select-custom bulk-field-input mb-2" name="tax_employment_type" disabled>
                                            <option value="" selected disabled>Select Emp Type...</option>
                                            <option value="Primary">Primary</option>
                                            <option value="Secondary">Secondary</option>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <select class="form-select form-select-sm form-select-custom bulk-field-input mb-2" name="tax_residency" disabled>
                                            <option value="" selected disabled>Select Residency...</option>
                                            <option value="Resident">Resident</option>
                                            <option value="Non-Resident">Non-Resident</option>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <input type="hidden" name="tax_cumulative_enabled_input" value="1">
                                        <div class="form-check mt-1">
                                            <input class="form-check-input check-custom bulk-field-input" type="checkbox" name="tax_cumulative_enabled" id="input_tax_cum" value="1" disabled>
                                            <label class="form-check-label fs-9 text-light" for="input_tax_cum">Enable Cumulative Tax</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Right Panel: Preview Area -->
                        <div class="col-md-3 ps-md-2 d-flex flex-column">
                            <h6 class="display-font text-white mb-3"><i class="bi bi-eye me-2"></i> Preview</h6>
                            
                            <div id="bulkPreviewContainer" class="flex-grow-1">
                                <div class="text-center text-secondary py-5 my-5">
                                    <i class="bi bi-arrow-left-circle fs-2 d-block mb-3"></i>
                                    Select employees and fields to update, then click Preview to verify changes.
                                </div>
                            </div>
                            
                            <div class="mt-3">
                                <button type="button" class="btn btn-outline-info w-100 mb-2" id="btnPreviewBulk">
                                    <i class="bi bi-magic me-1"></i> Generate Preview
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-3">
                    <button type="button" class="btn btn-custom-secondary" data-bs-dismiss="modal">Cancel</button>
                    <!-- Actual submit button, normally hidden or disabled until previewed -->
                    <button type="submit" class="btn btn-custom-primary" id="btnSubmitBulk" style="display: none;">
                        <i class="bi bi-check-circle me-1"></i> Apply Updates
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle input enabled/disabled state based on its parent field-toggle checkbox
        const fieldToggles = document.querySelectorAll('.field-toggle');
        fieldToggles.forEach(toggle => {
            toggle.addEventListener('change', function() {
                const card = this.closest('.card');
                const inputs = card.querySelectorAll('.bulk-field-input');
                inputs.forEach(input => {
                    input.disabled = !this.checked;
                });
            });
        });

        // Bulk employee checklist search filter
        const searchInput = document.getElementById('bulk-employee-search');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const query = this.value.toLowerCase().trim();
                const rows = document.querySelectorAll('.bulk-employee-row');
                rows.forEach(row => {
                    const name = row.getAttribute('data-name');
                    const id = row.getAttribute('data-id');
                    if (name.includes(query) || id.includes(query)) {
                        row.style.setProperty('display', 'block', 'important');
                    } else {
                        row.style.setProperty('display', 'none', 'important');
                    }
                });
            });
        }

        // Select All bulk employees checklist
        const selectAllCheckbox = document.getElementById('select-all-bulk-employees');
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                const checkboxes = document.querySelectorAll('.bulk-employee-checkbox');
                checkboxes.forEach(checkbox => {
                    const row = checkbox.closest('.bulk-employee-row');
                    if (row.style.display !== 'none') {
                        checkbox.checked = this.checked;
                    }
                });
            });
        }

        // Preview Logic via AJAX
        const btnPreview = document.getElementById('btnPreviewBulk');
        const form = document.getElementById('bulkUpdateForm');
        const previewContainer = document.getElementById('bulkPreviewContainer');
        const btnSubmit = document.getElementById('btnSubmitBulk');

        if (btnPreview) {
            btnPreview.addEventListener('click', function() {
                const formData = new FormData(form);
                formData.append('preview', '1');
                
                // Add token
                const token = document.querySelector('meta[name="csrf-token"]')?.content;
                
                btnPreview.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Loading...';
                btnPreview.disabled = true;

                fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.html) {
                        previewContainer.innerHTML = data.html;
                        btnSubmit.style.display = 'inline-block';
                    }
                })
                .catch(error => {
                    console.error('Error generating preview:', error);
                    previewContainer.innerHTML = '<div class="alert alert-danger">Error generating preview. Please ensure employees are selected.</div>';
                })
                .finally(() => {
                    btnPreview.innerHTML = '<i class="bi bi-magic me-1"></i> Refresh Preview';
                    btnPreview.disabled = false;
                });
            });
        }

        // Handle JSON response on submit instead of normal redirect for better UX
        if (form && btnSubmit) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(form);
                
                btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Saving...';
                btnSubmit.disabled = true;

                fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.reload();
                    } else {
                        alert(data.message || 'An error occurred during update.');
                        btnSubmit.innerHTML = '<i class="bi bi-check-circle me-1"></i> Apply Updates';
                        btnSubmit.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Error updating profiles:', error);
                    alert('An unexpected error occurred.');
                    btnSubmit.innerHTML = '<i class="bi bi-check-circle me-1"></i> Apply Updates';
                    btnSubmit.disabled = false;
                });
            });
        }
    });
</script>
@endsection
