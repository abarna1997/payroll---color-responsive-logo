@extends('layouts.app')

@section('content')
<div class="row g-4">
    <!-- Steps Checklist Sidebar -->
    <div class="col-lg-3">
        <div class="glass-card p-4">
            <h6 class="text-secondary fs-8 uppercase fw-bold mb-3">Onboarding Progress</h6>
            
            <div class="d-flex align-items-center gap-2 mb-4">
                @php
                    $completedCount = $employee ? $employee->checklistItems->where('is_completed', true)->count() : 0;
                    $totalTasks = $employee ? $employee->checklistItems->count() : 0;
                    $pct = ($totalTasks > 0) ? round(($completedCount / $totalTasks) * 100) : 0;
                @endphp
                <div class="progress flex-grow-1" style="height: 8px; background-color: var(--border-color); border-radius: 4px;">
                    <div class="progress-bar bg-indigo animate-all" role="progressbar" style="width: {{ $pct }}%;" aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <span class="text-indigo fw-bold fs-7">{{ $pct }}%</span>
            </div>

            <ul class="list-unstyled mb-0">
                @php
                    $stepsList = [
                        1 => ['title' => 'Personal Information', 'icon' => 'bi-person-fill'],
                        2 => ['title' => 'Employment Details', 'icon' => 'bi-briefcase-fill'],
                        3 => ['title' => 'Compensation Setup', 'icon' => 'bi-wallet2'],
                        4 => ['title' => 'Required Documents', 'icon' => 'bi-file-earmark-arrow-up'],
                        5 => ['title' => 'Agreement Generator', 'icon' => 'bi-file-earmark-text'],
                        6 => ['title' => 'Leave Allocation', 'icon' => 'bi-calendar-check'],
                        7 => ['title' => 'Asset Assignment', 'icon' => 'bi-laptop'],
                        8 => ['title' => 'Approvals Routing', 'icon' => 'bi-shield-check'],
                        9 => ['title' => 'Welcome Package', 'icon' => 'bi-gift-fill'],
                        10 => ['title' => 'Review & Submit', 'icon' => 'bi-check-all'],
                    ];
                @endphp

                @foreach($stepsList as $num => $info)
                    @php
                        $isCurrent = $step === $num;
                        $isPast = $step > $num || ($employee && $num < 4); // basic heuristic for past
                    @endphp
                    <li class="d-flex align-items-center gap-3 py-2 border-bottom border-light border-opacity-10 last:border-0 {{ $isCurrent ? 'text-indigo fw-bold' : ($isPast ? 'text-success' : 'text-secondary') }}">
                        <div class="rounded-circle d-flex align-items-center justify-content-center fs-7" style="width: 28px; height: 28px; background-color: {{ $isCurrent ? 'rgba(79, 70, 229, 0.1)' : ($isPast ? 'rgba(22, 163, 74, 0.1)' : 'rgba(255,255,255,0.05)') }}; border: 1px solid {{ $isCurrent ? 'var(--indigo)' : ($isPast ? '#16a34a' : 'transparent') }};">
                            @if($isPast)
                                <i class="bi bi-check-lg"></i>
                            @else
                                <span style="font-size: 0.8rem;">{{ $num }}</span>
                            @endif
                        </div>
                        <div class="fs-8">
                            <span class="d-block">{{ $info['title'] }}</span>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    <!-- Active Form View Panel -->
    <div class="col-lg-9">
        <div class="glass-card p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="m-0 display-font">
                    <i class="bi {{ $stepsList[$step]['icon'] }} me-2 text-indigo"></i> Step {{ $step }} &mdash; {{ $stepsList[$step]['title'] }}
                </h5>
                <span class="badge bg-indigo text-light">Onboarding ID: {{ $employee->employee_id ?? 'Drafting' }}</span>
            </div>

            <form action="{{ route('onboarding.store') }}" method="POST" enctype="multipart/form-data" id="wizardForm">
                @csrf
                <input type="hidden" name="step" value="{{ $step }}">
                @if($employee)
                    <input type="hidden" name="employee_id" value="{{ $employee->id }}">
                @endif

                <!-- STEP 1: Personal Info -->
                @if($step === 1)
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label text-secondary fs-8 uppercase fw-bold mb-1">Title</label>
                            <select name="title" class="form-select form-select-custom">
                                <option value="Mr." {{ ($employee && $employee->title === 'Mr.') ? 'selected' : '' }}>Mr.</option>
                                <option value="Mrs." {{ ($employee && $employee->title === 'Mrs.') ? 'selected' : '' }}>Mrs.</option>
                                <option value="Miss" {{ ($employee && $employee->title === 'Miss') ? 'selected' : '' }}>Miss</option>
                                <option value="Dr." {{ ($employee && $employee->title === 'Dr.') ? 'selected' : '' }}>Dr.</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-secondary fs-8 uppercase fw-bold mb-1">Employee ID *</label>
                            <input type="text" name="employee_id_code" class="form-control form-control-custom" value="{{ $employee ? $employee->employee_id : '' }}" placeholder="e.g. EMP001" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-secondary fs-8 uppercase fw-bold mb-1">First Name *</label>
                            <input type="text" name="first_name" class="form-control form-control-custom" value="{{ $employee ? $employee->first_name : '' }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-secondary fs-8 uppercase fw-bold mb-1">Last Name *</label>
                            <input type="text" name="last_name" class="form-control form-control-custom" value="{{ $employee ? $employee->last_name : '' }}" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label text-secondary fs-8 uppercase fw-bold mb-1">Middle Name</label>
                            <input type="text" name="middle_name" class="form-control form-control-custom" value="{{ $employee ? $employee->middle_name : '' }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-secondary fs-8 uppercase fw-bold mb-1">NIC / Passport</label>
                            <input type="text" name="nic" class="form-control form-control-custom" value="{{ $employee ? $employee->nic : '' }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-secondary fs-8 uppercase fw-bold mb-1">Gender</label>
                            <select name="gender" class="form-select form-select-custom">
                                <option value="Male" {{ ($employee && $employee->gender === 'Male') ? 'selected' : '' }}>Male</option>
                                <option value="Female" {{ ($employee && $employee->gender === 'Female') ? 'selected' : '' }}>Female</option>
                                <option value="Other" {{ ($employee && $employee->gender === 'Other') ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label text-secondary fs-8 uppercase fw-bold mb-1">Date of Birth</label>
                            <input type="date" name="date_of_birth" class="form-control form-control-custom" value="{{ ($employee && $employee->date_of_birth) ? $employee->date_of_birth->format('Y-m-d') : '' }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-secondary fs-8 uppercase fw-bold mb-1">Nationality</label>
                            <input type="text" name="nationality" class="form-control form-control-custom" value="{{ $employee ? $employee->nationality : 'Sri Lankan' }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-secondary fs-8 uppercase fw-bold mb-1">Religion</label>
                            <input type="text" name="religion" class="form-control form-control-custom" value="{{ $employee ? $employee->religion : '' }}">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label text-secondary fs-8 uppercase fw-bold mb-1">Marital Status</label>
                            <select name="marital_status" class="form-select form-select-custom">
                                <option value="Single" {{ ($employee && $employee->marital_status === 'Single') ? 'selected' : '' }}>Single</option>
                                <option value="Married" {{ ($employee && $employee->marital_status === 'Married') ? 'selected' : '' }}>Married</option>
                                <option value="Divorced" {{ ($employee && $employee->marital_status === 'Divorced') ? 'selected' : '' }}>Divorced</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-secondary fs-8 uppercase fw-bold mb-1">Blood Group</label>
                            <input type="text" name="blood_group" class="form-control form-control-custom" value="{{ $employee ? $employee->blood_group : '' }}" placeholder="e.g. O+">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-secondary fs-8 uppercase fw-bold mb-1">Mobile Number</label>
                            <input type="text" name="mobile_number" class="form-control form-control-custom" value="{{ $employee ? $employee->mobile_number : '' }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-secondary fs-8 uppercase fw-bold mb-1">Personal Email</label>
                            <input type="email" name="personal_email" class="form-control form-control-custom" value="{{ $employee ? $employee->personal_email : '' }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary fs-8 uppercase fw-bold mb-1">Company Email</label>
                            <input type="email" name="company_email" class="form-control form-control-custom" value="{{ $employee ? $employee->company_email : '' }}">
                        </div>

                        <!-- Emergency Contact -->
                        <div class="col-md-4">
                            <label class="form-label text-secondary fs-8 uppercase fw-bold mb-1">Emergency Contact Name</label>
                            <input type="text" name="emergency_contact_name" class="form-control form-control-custom" value="{{ $employee ? $employee->emergency_contact_name : '' }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-secondary fs-8 uppercase fw-bold mb-1">Contact Phone</label>
                            <input type="text" name="emergency_contact_phone" class="form-control form-control-custom" value="{{ $employee ? $employee->emergency_contact_phone : '' }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-secondary fs-8 uppercase fw-bold mb-1">Relationship</label>
                            <input type="text" name="emergency_contact_relationship" class="form-control form-control-custom" value="{{ $employee ? $employee->emergency_contact_relationship : '' }}" placeholder="e.g. Spouse">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-secondary fs-8 uppercase fw-bold mb-1">Permanent Address</label>
                            <textarea name="permanent_address" class="form-control form-control-custom" rows="2">{{ $employee ? $employee->permanent_address : '' }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary fs-8 uppercase fw-bold mb-1">Current Address</label>
                            <textarea name="current_address" class="form-control form-control-custom" rows="2">{{ $employee ? $employee->current_address : '' }}</textarea>
                        </div>

                        <!-- Image File Uploads -->
                        <div class="col-md-6">
                            <label class="form-label text-secondary fs-8 uppercase fw-bold mb-1">Profile Photo</label>
                            <input type="file" name="profile_photo" class="form-control form-control-custom">
                            @if($employee && $employee->profile_photo)
                                <div class="mt-2"><img src="{{ asset($employee->profile_photo) }}" style="width: 50px; height: 50px; border-radius: 4px; object-fit: cover;"></div>
                            @endif
                        </div>

                        <!-- Drawn Signature Canvas -->
                        <div class="col-md-6">
                            <label class="form-label text-secondary fs-8 uppercase fw-bold mb-1">Drawn Employee Signature</label>
                            <div class="signature-canvas-wrapper rounded bg-dark p-2" style="border: 1px solid var(--border-color);">
                                <canvas id="signaturePad" width="350" height="100" style="width: 100%; height: 100px; cursor: crosshair;"></canvas>
                                <button type="button" id="clearSignatureBtn" class="btn btn-sm btn-outline-secondary border-0 mt-1 fs-9">Clear Canvas</button>
                            </div>
                            <input type="hidden" name="drawn_signature_data" id="signatureInput" value="{{ $employee ? $employee->signature : '' }}">
                        </div>
                    </div>
                @endif

                <!-- STEP 2: Employment Details -->
                @if($step === 2)
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label text-secondary fs-8 uppercase fw-bold mb-1">Company *</label>
                            <select name="company_id" class="form-select form-select-custom" required>
                                @foreach($companies as $c)
                                    <option value="{{ $c->id }}" {{ ($employee && $employee->company_id === $c->id) ? 'selected' : '' }}>{{ $c->company_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-secondary fs-8 uppercase fw-bold mb-1">Branch</label>
                            <select name="branch_id" class="form-select form-select-custom">
                                <option value="">Select Branch</option>
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}" {{ ($employee && $employee->branch_id === $b->id) ? 'selected' : '' }}>{{ $b->branch_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-secondary fs-8 uppercase fw-bold mb-1">Department *</label>
                            <select name="department_id" class="form-select form-select-custom" required>
                                @foreach($departments as $d)
                                    <option value="{{ $d->id }}" {{ ($employee && $employee->department_id === $d->id) ? 'selected' : '' }}>{{ $d->department_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label text-secondary fs-8 uppercase fw-bold mb-1">Designation</label>
                            <select name="designation" class="form-select form-control-custom">
                                <option value="">Select Designation...</option>
                                @foreach($designations as $designation)
                                    <option value="{{ $designation->title }}" {{ ($employee && $employee->designation == $designation->title) ? 'selected' : '' }}>
                                        {{ $designation->title }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-secondary fs-8 uppercase fw-bold mb-1">Job Grade</label>
                            <input type="text" name="job_grade" class="form-control form-control-custom" value="{{ $employee ? $employee->job_grade : '' }}" placeholder="e.g. Grade A">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-secondary fs-8 uppercase fw-bold mb-1">Shift</label>
                            <select name="shift_id" class="form-select form-select-custom">
                                <option value="">Select Shift</option>
                                @foreach($shifts as $s)
                                    <option value="{{ $s->id }}" {{ ($employee && $employee->shift_id === $s->id) ? 'selected' : '' }}>{{ $s->shift_name }} ({{ $s->start_time }} to {{ $s->end_time }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label text-secondary fs-8 uppercase fw-bold mb-1">Join Date</label>
                            <input type="date" name="join_date" class="form-control form-control-custom" value="{{ ($employee && $employee->join_date) ? $employee->join_date->format('Y-m-d') : '' }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-secondary fs-8 uppercase fw-bold mb-1">Probation Period (Months)</label>
                            <input type="number" name="probation_period" class="form-control form-control-custom" value="{{ $employee ? $employee->probation_period : 6 }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-secondary fs-8 uppercase fw-bold mb-1">Confirmation Date</label>
                            <input type="date" name="confirmation_date" class="form-control form-control-custom" value="{{ $employee && $employee->confirmation_date ? $employee->confirmation_date : '' }}">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label text-secondary fs-8 uppercase fw-bold mb-1">Work Location</label>
                            <input type="text" name="work_location" class="form-control form-control-custom" value="{{ $employee ? $employee->work_location : 'Colombo' }}">
                        </div>
                        
                        <div class="col-12 mt-4 mb-2">
                            <h5 class="text-indigo display-font fw-bold"><i class="bi bi-laptop me-2"></i>Work Arrangement</h5>
                            <p class="text-muted small">Configure normal work arrangement and WFH eligibility.</p>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-secondary fs-8 uppercase fw-bold mb-1">Work Mode</label>
                            <select name="work_mode" class="form-select form-select-custom" id="work_mode_select">
                                <option value="NORMAL" {{ ($employee && $employee->work_mode == 'NORMAL') ? 'selected' : '' }}>NORMAL (Office Based)</option>
                                <option value="WFH" {{ ($employee && $employee->work_mode == 'WFH') ? 'selected' : '' }}>WFH (Permanent Remote)</option>
                                <option value="HYBRID" {{ ($employee && $employee->work_mode == 'HYBRID') ? 'selected' : '' }}>HYBRID (Flexible / Both)</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-secondary fs-8 uppercase fw-bold mb-1">Remote Punch</label>
                            <select name="allow_remote_punch" class="form-select form-select-custom" id="allow_remote_punch_select">
                                <option value="0" {{ ($employee && !$employee->allow_remote_punch) ? 'selected' : '' }}>Disabled</option>
                                <option value="1" {{ ($employee && $employee->allow_remote_punch) ? 'selected' : '' }}>Enabled</option>
                            </select>
                        </div>
                        <div class="col-md-4"></div>
                        <div class="col-md-4 wfh-fields">
                            <label class="form-label text-secondary fs-8 uppercase fw-bold mb-1">WFH Latitude</label>
                            <input type="number" step="0.00000001" name="home_latitude" class="form-control form-control-custom" value="{{ $employee ? $employee->home_latitude : '' }}" placeholder="e.g. 6.92707860">
                        </div>
                        <div class="col-md-4 wfh-fields">
                            <label class="form-label text-secondary fs-8 uppercase fw-bold mb-1">WFH Longitude</label>
                            <input type="number" step="0.00000001" name="home_longitude" class="form-control form-control-custom" value="{{ $employee ? $employee->home_longitude : '' }}" placeholder="e.g. 79.86124300">
                        </div>
                        <div class="col-md-4 wfh-fields">
                            <label class="form-label text-secondary fs-8 uppercase fw-bold mb-1">Allowed Radius (meters)</label>
                            <input type="number" name="allowed_radius" class="form-control form-control-custom" value="{{ $employee ? $employee->allowed_radius : 150 }}">
                        </div>

                        <div class="col-12 mt-4 mb-2">
                            <h5 class="text-indigo display-font fw-bold"><i class="bi bi-wallet me-2"></i>Cost Center</h5>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-secondary fs-8 uppercase fw-bold mb-1">Cost Center</label>
                            <input type="text" name="cost_center" class="form-control form-control-custom" value="{{ $employee ? $employee->cost_center : '' }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-secondary fs-8 uppercase fw-bold mb-1">Payroll Group</label>
                            <input type="text" name="payroll_group" class="form-control form-control-custom" value="{{ $employee ? $employee->payroll_group : '' }}">
                        </div>
                    </div>
                @endif

                <!-- STEP 3: Salary & Compensation Setup -->
                @if($step === 3)
                    @php
                        $sp             = $employee?->salaryProfile;
                        $basicSalaryVal = $sp ? (float)$sp->basic_salary : 0.0;
                        $incentiveVal   = $sp ? (float)$sp->incentive    : 0.0;
                        $totalPkgVal    = $basicSalaryVal + $incentiveVal;
                    @endphp

                    <div class="row g-3">

                        {{-- ── Salary Package Inputs ──────────────────────────────── --}}

                        {{-- Basic Salary --}}
                        <div class="col-md-4">
                            <label class="form-label text-secondary fs-8 uppercase fw-bold mb-1">
                                Basic Salary (LKR) <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-transparent border-end-0 text-secondary" style="border-color:#d1d5db;">LKR</span>
                                <input type="number"
                                       id="basicSalaryInput"
                                       name="basic_salary"
                                       class="form-control form-control-custom border-start-0"
                                       style="border-left:none !important;"
                                       value="{{ number_format($basicSalaryVal, 2, '.', '') }}"
                                       step="0.01" min="0" required
                                       placeholder="0.00">
                            </div>
                            <div class="mt-1" style="font-size:0.72rem; color:var(--text-secondary);">
                                Fixed monthly base pay
                            </div>
                        </div>

                        {{-- Incentive --}}
                        <div class="col-md-4">
                            <label class="form-label text-secondary fs-8 uppercase fw-bold mb-1">
                                Incentive (LKR)
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-transparent border-end-0 text-secondary" style="border-color:#d1d5db;">LKR</span>
                                <input type="number"
                                       id="incentiveInput"
                                       name="incentive"
                                       class="form-control form-control-custom border-start-0"
                                       style="border-left:none !important;"
                                       value="{{ number_format($incentiveVal, 2, '.', '') }}"
                                       step="0.01" min="0"
                                       placeholder="0.00">
                            </div>
                            <div class="mt-1" style="font-size:0.72rem; color:var(--text-secondary);">
                                Performance / fixed incentive
                            </div>
                        </div>

                        {{-- Total Salary Package (read-only, auto-computed) --}}
                        <div class="col-md-4">
                            <label class="form-label text-secondary fs-8 uppercase fw-bold mb-1">
                                Total Salary Package (LKR)
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-transparent border-end-0 text-secondary" style="border-color:rgba(249,87,22,0.35);">LKR</span>
                                <div class="form-control form-control-custom border-start-0 d-flex align-items-center justify-content-between"
                                     style="border-left:none !important; background:rgba(249,87,22,0.04); border-color:rgba(249,87,22,0.35); cursor:default; user-select:none;">
                                    <span id="totalPkgValue"
                                          class="fw-bold display-font text-primary"
                                          style="font-size:1.05rem; letter-spacing:-0.5px;">
                                        {{ number_format($totalPkgVal, 2) }}
                                    </span>
                                    <i class="bi bi-calculator-fill text-primary opacity-40 ms-2"></i>
                                </div>
                            </div>
                            <div class="mt-1" style="font-size:0.72rem; color:var(--text-secondary);">
                                = Basic + Incentive (auto-calculated)
                            </div>
                        </div>

                        {{-- ── Salary Package Summary Card ─────────────────────────── --}}
                        <div class="col-12">
                            <div class="rounded-3 border p-0 overflow-hidden" style="border-color:var(--border-color);">
                                {{-- Header --}}
                                <div class="px-3 py-2 d-flex align-items-center justify-content-between"
                                     style="background:rgba(249,87,22,0.05); border-bottom:1px solid var(--border-color);">
                                    <span class="display-font fw-bold text-dark" style="font-size:0.85rem;">
                                        <i class="bi bi-wallet2 me-2 text-primary"></i>Total Salary Package Breakdown
                                    </span>
                                    <small class="text-secondary" style="font-size:0.72rem;">Live preview — updates as you type</small>
                                </div>
                                {{-- Table --}}
                                <table class="table custom-table mb-0" style="font-size:0.86rem;">
                                    <thead>
                                        <tr>
                                            <th style="width:45%;">Component</th>
                                            <th>Description</th>
                                            <th class="text-end">Amount (LKR)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="fw-semibold">
                                                <i class="bi bi-cash-coin me-2 text-success"></i>Basic Salary
                                            </td>
                                            <td class="text-secondary" style="font-size:0.8rem;">Fixed monthly base pay</td>
                                            <td class="text-end fw-bold text-success" id="tblBasic">
                                                {{ number_format($basicSalaryVal, 2) }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="fw-semibold">
                                                <i class="bi bi-stars me-2 text-warning"></i>Incentive
                                            </td>
                                            <td class="text-secondary" style="font-size:0.8rem;">Performance / fixed incentive</td>
                                            <td class="text-end fw-bold text-warning" id="tblIncentive">
                                                {{ number_format($incentiveVal, 2) }}
                                            </td>
                                        </tr>
                                        <tr style="background:rgba(249,87,22,0.04); border-top:2px solid var(--border-color);">
                                            <td class="fw-bold display-font" style="font-size:0.9rem;">
                                                <i class="bi bi-award-fill me-2 text-primary"></i>Total Salary Package
                                            </td>
                                            <td class="text-secondary fst-italic" style="font-size:0.8rem;">Basic + Incentive</td>
                                            <td class="text-end fw-bold text-primary display-font" style="font-size:1rem;" id="tblTotal">
                                                {{ number_format($totalPkgVal, 2) }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- ── Payment & Banking Details ────────────────────────────── --}}

                        {{-- Divider --}}
                        <div class="col-12 mt-2">
                            <div class="d-flex align-items-center gap-3">
                                <hr class="flex-grow-1 m-0" style="border-color:var(--border-color);">
                                <span class="text-secondary fw-semibold" style="font-size:0.75rem; white-space:nowrap; text-transform:uppercase; letter-spacing:1px;">
                                    <i class="bi bi-bank me-1"></i>Payment &amp; Banking Details
                                </span>
                                <hr class="flex-grow-1 m-0" style="border-color:var(--border-color);">
                            </div>
                        </div>

                        {{-- Payment Method --}}
                        <div class="col-md-6">
                            <label class="form-label text-secondary fs-8 uppercase fw-bold mb-1">Payment Method</label>
                            <select name="payment_method" class="form-select form-select-custom">
                                <option value="Bank Transfer" {{ ($employee && $employee->payment_method === 'Bank Transfer') ? 'selected' : '' }}>Bank Transfer</option>
                                <option value="Cheque"        {{ ($employee && $employee->payment_method === 'Cheque')        ? 'selected' : '' }}>Cheque</option>
                                <option value="Cash"          {{ ($employee && $employee->payment_method === 'Cash')          ? 'selected' : '' }}>Cash</option>
                            </select>
                        </div>

                        {{-- Bank Name --}}
                        <div class="col-md-4">
                            <label class="form-label text-secondary fs-8 uppercase fw-bold mb-1">Bank Name</label>
                            <input type="text" name="bank_name" class="form-control form-control-custom"
                                   value="{{ $employee ? $employee->bank_name : '' }}"
                                   placeholder="e.g. Commercial Bank">
                        </div>

                        {{-- Account Number --}}
                        <div class="col-md-4">
                            <label class="form-label text-secondary fs-8 uppercase fw-bold mb-1">Account Number</label>
                            <input type="text" name="bank_account" class="form-control form-control-custom"
                                   value="{{ $employee ? $employee->bank_account : '' }}">
                        </div>

                        {{-- SWIFT Code --}}
                        <div class="col-md-4">
                            <label class="form-label text-secondary fs-8 uppercase fw-bold mb-1">Bank SWIFT Code</label>
                            <input type="text" name="bank_swift" class="form-control form-control-custom"
                                   value="{{ $employee ? $employee->bank_swift : '' }}">
                        </div>

                    </div>{{-- /row --}}

                    {{-- ── Live Calculator ─────────────────────────────────────────── --}}
                    <script>
                    (function () {
                        const basicInput     = document.getElementById('basicSalaryInput');
                        const incentiveInput = document.getElementById('incentiveInput');
                        const totalPkgSpan   = document.getElementById('totalPkgValue');
                        const tblBasic       = document.getElementById('tblBasic');
                        const tblIncentive   = document.getElementById('tblIncentive');
                        const tblTotal       = document.getElementById('tblTotal');

                        function fmt(n) {
                            return n.toLocaleString('en-LK', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                        }

                        function recalculate() {
                            const basic     = parseFloat(basicInput.value)     || 0;
                            const incentive = parseFloat(incentiveInput.value) || 0;
                            const total     = basic + incentive;

                            // Header display
                            totalPkgSpan.textContent = fmt(total);

                            // Table cells
                            if (tblBasic)     tblBasic.textContent     = fmt(basic);
                            if (tblIncentive) tblIncentive.textContent = fmt(incentive);
                            if (tblTotal)     tblTotal.textContent     = fmt(total);
                        }

                        [basicInput, incentiveInput].forEach(function (el) {
                            if (el) {
                                el.addEventListener('input',  recalculate);
                                el.addEventListener('change', recalculate);
                            }
                        });
                    })();
                    </script>
                @endif

                <!-- STEP 4: Required Documents Upload -->
                @if($step === 4)
                    <div class="row g-4">
                        @php
                            $docsList = [
                                'nic_upload' => ['title' => 'National Identity Card (NIC)', 'db' => 'NIC'],
                                'passport_upload' => ['title' => 'Passport Photo Page', 'db' => 'Passport'],
                                'birth_upload' => ['title' => 'Birth Certificate Copy', 'db' => 'Birth Certificate'],
                                'cv_upload' => ['title' => 'CV / Resume File', 'db' => 'CV / Resume']
                            ];
                        @endphp
                        @foreach($docsList as $inputName => $meta)
                            @php
                                $existing = $employee ? $employee->documents->where('document_name', $meta['db'])->first() : null;
                            @endphp
                            <div class="col-md-6">
                                <div class="card p-3" style="background-color: var(--input-bg); border: 1px solid var(--border-color);">
                                    <h6 class="text-dark fw-bold mb-2">{{ $meta['title'] }}</h6>
                                    @if($existing)
                                        <div class="alert alert-success py-2 px-3 fs-8 d-flex align-items-center justify-content-between mb-2">
                                            <span><i class="bi bi-check-circle-fill me-1"></i> Document Uploaded</span>
                                            <a href="{{ asset($existing->file_path) }}" target="_blank" class="text-indigo fw-bold decoration-none fs-8">View File</a>
                                        </div>
                                    @endif
                                    <input type="file" name="{{ $inputName }}" class="form-control form-control-custom mb-2">
                                    <input type="date" name="{{ $inputName }}_expiry" class="form-control form-control-custom fs-8 py-1" placeholder="Expiry Date (optional)">
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                <!-- STEP 5: Agreements Compiler -->
                @if($step === 5)
                    <div class="row g-3">
                        <div class="col-12">
                            <p class="text-secondary fs-8">Select one of the registered paperless agreement templates below to compile and attach it to this employee's file. Once generated, the employee can sign the agreement electronically.</p>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label text-secondary fs-8 uppercase fw-bold mb-1">Select Document Template</label>
                            <select id="agreementTemplateSelect" class="form-select form-select-custom">
                                @foreach($templates as $t)
                                    <option value="{{ $t->id }}">{{ $t->name }} ({{ $t->type }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="button" id="compileAgreementBtn" class="btn btn-custom-primary w-100 py-2">
                                <i class="bi bi-file-earmark-pdf-fill me-1"></i> Compile Agreement
                            </button>
                        </div>

                        <!-- Compiled/Draft Agreements Log -->
                        <div class="col-12 mt-4">
                            <h6 class="text-dark fw-semibold mb-3">Generated Employee Agreements</h6>
                            <div class="table-responsive">
                                <table class="table custom-table">
                                    <thead>
                                        <tr>
                                            <th>Agreement Name</th>
                                            <th>Status</th>
                                            <th>Date Compiled</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($employee->agreements as $ag)
                                            <tr>
                                                <td class="fw-semibold text-dark fs-8.5">{{ $ag->template->name }}</td>
                                                <td>
                                                    <span class="badge-status {{ $ag->status === 'Signed' ? 'badge-online' : 'badge-pending' }} fs-8">
                                                        {{ $ag->status }}
                                                    </span>
                                                </td>
                                                <td class="text-secondary fs-8">{{ $ag->created_at->format('Y-m-d H:i') }}</td>
                                                <td>
                                                    @if($ag->status === 'Generated')
                                                        <button type="button" class="btn btn-sm btn-success border-0 px-2 py-1 fs-8.5" data-bs-toggle="modal" data-bs-target="#signAgreementModal{{ $ag->id }}">
                                                            <i class="bi bi-pen"></i> Sign Document
                                                        </button>
                                                    @else
                                                        <a href="{{ route('onboarding.download-agreement', $ag->id) }}" class="btn btn-sm btn-outline-secondary border-0 px-2 py-1 fs-8.5 text-indigo">
                                                            <i class="bi bi-download"></i> Download PDF
                                                        </a>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center text-secondary py-3">No agreements compiled yet.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- STEP 6: Leave Allocation -->
                @if($step === 6)
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="alert alert-indigo d-flex align-items-center py-3">
                                <i class="bi bi-info-circle-fill me-3 fs-4 text-indigo"></i>
                                <div>
                                    <strong>Default Leaves Allotted</strong><br>
                                    Employee is mapped to the standard Corporate Leave limits. The system will allot 14 days of Annual Leave, 7 days of Casual Leave, and 7 days of Medical Leave pro-rated from the join date dynamically.
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- STEP 7: Asset Assignment -->
                @if($step === 7)
                    <div class="row g-3">
                        <div class="col-md-5">
                            <label class="form-label text-secondary fs-8 uppercase fw-bold mb-1">Asset Name</label>
                            <input type="text" id="assetNameInput" class="form-control form-control-custom" placeholder="e.g. Dell Latitude 5420">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-secondary fs-8 uppercase fw-bold mb-1">Serial Number</label>
                            <input type="text" id="assetSerialInput" class="form-control form-control-custom" placeholder="e.g. CN-0XYZ123">
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="button" id="assignAssetBtn" class="btn btn-custom-primary w-100 py-2">
                                <i class="bi bi-plus-circle-fill me-1"></i> Issue Asset
                            </button>
                        </div>

                        <!-- Issued Assets Log -->
                        <div class="col-12 mt-4">
                            <h6 class="text-dark fw-semibold mb-3">Issued Equipment Log</h6>
                            <div class="table-responsive">
                                <table class="table custom-table">
                                    <thead>
                                        <tr>
                                            <th>Asset Item</th>
                                            <th>Serial Number</th>
                                            <th>Issue Date</th>
                                            <th>Handover Form</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($employee->assets as $as)
                                            <tr>
                                                <td class="fw-semibold text-dark fs-8.5">{{ $as->asset_name }}</td>
                                                <td class="text-secondary fs-8">{{ $as->serial_number ?: 'N/A' }}</td>
                                                <td class="text-secondary fs-8">{{ $as->assigned_date }}</td>
                                                <td>
                                                    <a href="{{ route('onboarding.download-asset-handover', $as->id) }}" class="btn btn-sm btn-outline-secondary border-0 px-2 py-1 fs-8.5 text-indigo">
                                                        <i class="bi bi-file-earmark-pdf-fill"></i> Download Receipt
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center text-secondary py-3">No assets assigned yet.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- STEP 8: Approvals Workflow -->
                @if($step === 8)
                    <div class="row g-3">
                        <div class="col-12">
                            <p class="text-secondary fs-8">Onboarding submits a dynamic checklist verification task across active department nodes. Approvers can sign off from their respective dashboards.</p>
                            
                            <div class="table-responsive mt-3">
                                <table class="table custom-table">
                                    <thead>
                                        <tr>
                                            <th>Department Node</th>
                                            <th>Status</th>
                                            <th>Signed By</th>
                                            <th>Comments</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($employee->onboardingStages as $stg)
                                            <tr>
                                                <td class="fw-semibold text-dark fs-8.5">{{ $stg->stage_name }} Approval</td>
                                                <td>
                                                    <span class="badge-status {{ $stg->status === 'Approved' ? 'badge-online' : ($stg->status === 'Pending' ? 'badge-pending' : 'badge-offline') }} fs-8">
                                                        {{ $stg->status }}
                                                    </span>
                                                </td>
                                                <td class="text-secondary fs-8">{{ $stg->approver ? $stg->approver->username : '—' }}</td>
                                                <td class="text-secondary fs-8 italic">{{ $stg->comments ?: 'No comments.' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- STEP 9: Welcome Package -->
                @if($step === 9)
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="welcomeLetterSwitch" checked>
                                <label class="form-check-label text-dark fs-8.5" for="welcomeLetterSwitch">Include Digital Welcome Letter</label>
                            </div>
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="handbookSwitch" checked>
                                <label class="form-check-label text-dark fs-8.5" for="handbookSwitch">Include Employee Handbook & Company Policies</label>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- STEP 10: Final Review -->
                @if($step === 10)
                    <div class="row g-3">
                        <div class="col-md-6 border-end" style="border-color: var(--border-color) !important;">
                            <h6 class="text-dark fw-bold mb-3">Personal & Contact Review</h6>
                            <p class="fs-8 text-secondary mb-1">Employee Name: <strong class="text-dark">{{ $employee->full_name }}</strong></p>
                            <p class="fs-8 text-secondary mb-1">NIC: <span class="text-dark">{{ $employee->nic ?? 'N/A' }}</span></p>
                            <p class="fs-8 text-secondary mb-1">Mobile: <span class="text-dark">{{ $employee->mobile_number ?? 'N/A' }}</span></p>
                            <p class="fs-8 text-secondary mb-1">Role: <span class="text-dark">{{ $employee->designation ?? 'Staff' }}</span></p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-dark fw-bold mb-3">Onboarding Checklist Checklist</h6>
                            <ul class="list-unstyled mb-0">
                                @foreach($employee->checklistItems as $chk)
                                    <li class="d-flex align-items-center gap-2 py-1 fs-8.5">
                                        <i class="bi {{ $chk->is_completed ? 'bi-check-circle-fill text-success' : 'bi-circle text-secondary' }}"></i>
                                        <span class="{{ $chk->is_completed ? 'text-dark' : 'text-secondary' }}">{{ $chk->task_name }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                <!-- Footer Buttons -->
                <div class="d-flex justify-content-between align-items-center mt-5 pt-3 border-top" style="border-color: var(--border-color) !important;">
                    @if($step > 1)
                        <a href="{{ route('onboarding.wizard', ['step' => $step - 1, 'employee_id' => $employee->id]) }}" class="btn btn-custom-secondary px-4">
                            <i class="bi bi-chevron-left me-1"></i> Back
                        </a>
                    @else
                        <div></div>
                    @endif

                    @if($step < 10)
                        <button type="submit" class="btn btn-custom-primary px-4 py-2">
                            Next Step <i class="bi bi-chevron-right ms-1"></i>
                        </button>
                    @else
                        <button type="submit" class="btn btn-success px-4 py-2 border-0">
                            <i class="bi bi-check-circle-fill me-1"></i> Complete Onboarding
                        </button>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal signatures loop -->
@if($step === 5)
    @foreach($employee->agreements as $ag)
        @if($ag->status === 'Generated')
            <div class="modal fade" id="signAgreementModal{{ $ag->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content glass-card p-0" >
                        <div class="modal-header border-bottom-0 pb-0">
                            <h5 class="modal-title display-font"><i class="bi bi-pen me-2 text-indigo"></i> Sign Agreement</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form action="{{ route('onboarding.sign-agreement', [$employee->id, $ag->id]) }}" method="POST">
                            @csrf
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label text-secondary fw-semibold">Signature Type</label>
                                    <select name="signature_type" class="form-select form-select-custom" required>
                                        <option value="typed">Type Electronic Signature (Auto Watermark)</option>
                                        <option value="drawn">Use Handdrawn Personal Profile Signature</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label text-secondary fw-semibold">Signature Text (For Typed option)</label>
                                    <input type="text" name="signature_data" class="form-control form-control-custom" placeholder="Type your full name..." required>
                                </div>
                            </div>
                            <div class="modal-footer border-top-0 pt-0 pb-4 pe-4">
                                <button type="button" class="btn btn-custom-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-custom-primary">Sign Document</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    @endforeach
@endif

<!-- JavaScript helpers for signatures/canvas and AJAX compiles -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Step 1 signature canvas handling
        const canvas = document.getElementById('signaturePad');
        const signatureInput = document.getElementById('signatureInput');
        const clearBtn = document.getElementById('clearSignatureBtn');

        if (canvas) {
            const ctx = canvas.getContext('2d');
            let drawing = false;

            ctx.strokeStyle = '#818CF8'; // Indigo brush
            ctx.lineWidth = 2.5;
            ctx.lineJoin = 'round';
            ctx.lineCap = 'round';

            function getMousePos(canvasDom, touchOrMouseEvent) {
                const rect = canvasDom.getBoundingClientRect();
                const clientX = touchOrMouseEvent.touches ? touchOrMouseEvent.touches[0].clientX : touchOrMouseEvent.clientX;
                const clientY = touchOrMouseEvent.touches ? touchOrMouseEvent.touches[0].clientY : touchOrMouseEvent.clientY;
                return {
                    x: clientX - rect.left,
                    y: clientY - rect.top
                };
            }

            function startDrawing(e) {
                drawing = true;
                const pos = getMousePos(canvas, e);
                ctx.beginPath();
                ctx.moveTo(pos.x, pos.y);
            }

            function draw(e) {
                if (!drawing) return;
                e.preventDefault();
                const pos = getMousePos(canvas, e);
                ctx.lineTo(pos.x, pos.y);
                ctx.stroke();
            }

            function stopDrawing() {
                if (drawing) {
                    drawing = false;
                    signatureInput.value = canvas.toDataURL(); // Store png as base64 string
                }
            }

            // Mouse Events
            canvas.addEventListener('mousedown', startDrawing);
            canvas.addEventListener('mousemove', draw);
            window.addEventListener('mouseup', stopDrawing);

            // Touch Events (Mobile)
            canvas.addEventListener('touchstart', startDrawing);
            canvas.addEventListener('touchmove', draw);
            window.addEventListener('touchend', stopDrawing);

            clearBtn.addEventListener('click', function() {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                signatureInput.value = '';
            });

            // Pre-load drawn signature if exists
            const existingSig = signatureInput.value;
            if (existingSig && existingSig.startsWith('data:image/png;base64')) {
                const img = new Image();
                img.onload = function() {
                    ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
                };
                img.src = existingSig;
            }
        }

        // Step 5 AJAX Compile
        const compileBtn = document.getElementById('compileAgreementBtn');
        const templateSelect = document.getElementById('agreementTemplateSelect');

        if (compileBtn && templateSelect) {
            compileBtn.addEventListener('click', function() {
                const templateId = templateSelect.value;
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = "{{ route('onboarding.generate-agreement', $employee->id ?? 0) }}";
                
                const csrf = document.createElement('input');
                csrf.type = 'hidden';
                csrf.name = '_token';
                csrf.value = "{{ csrf_token() }}";
                
                const tmpl = document.createElement('input');
                tmpl.type = 'hidden';
                tmpl.name = 'template_id';
                tmpl.value = templateId;

                form.appendChild(csrf);
                form.appendChild(tmpl);
                document.body.appendChild(form);
                form.submit();
            });
        }

        // Step 7 Asset Assignment
        const assignAssetBtn = document.getElementById('assignAssetBtn');
        if (assignAssetBtn) {
            assignAssetBtn.addEventListener('click', function() {
                const name = document.getElementById('assetNameInput').value;
                const serial = document.getElementById('assetSerialInput').value;
                
                if (!name) {
                    alert('Please enter an asset name.');
                    return;
                }

                const form = document.createElement('form');
                form.method = 'POST';
                form.action = "{{ route('onboarding.assign-asset', $employee->id ?? 0) }}";
                
                const csrf = document.createElement('input');
                csrf.type = 'hidden';
                csrf.name = '_token';
                csrf.value = "{{ csrf_token() }}";

                const inName = document.createElement('input');
                inName.type = 'hidden';
                inName.name = 'asset_name';
                inName.value = name;

                const inSerial = document.createElement('input');
                inSerial.type = 'hidden';
                inSerial.name = 'serial_number';
                inSerial.value = serial;

                const inDate = document.createElement('input');
                inDate.type = 'hidden';
                inDate.name = 'assigned_date';
                inDate.value = new Date().toISOString().split('T')[0];

                form.appendChild(csrf);
                form.appendChild(inName);
                form.appendChild(inSerial);
                form.appendChild(inDate);
                document.body.appendChild(form);
                form.submit();
            });
        }
    });
</script>
@endsection
