@extends('layouts.app')

@section('content')
<div class="row g-4 mb-4">
    <!-- Profile Header Card -->
    <div class="col-12">
        <div class="glass-card p-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div class="d-flex align-items-center flex-wrap gap-3">
                    @if($employee->profile_photo)
                        <img src="{{ asset($employee->profile_photo) }}" class="rounded-circle" style="width: 72px; height: 72px; object-fit: cover; border: 2px solid var(--indigo);">
                    @else
                        <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center text-light text-uppercase" style="width: 72px; height: 72px; font-size: 1.8rem; border: 2px solid var(--indigo);">
                            {{ substr($employee->first_name, 0, 1) }}
                        </div>
                    @endif
                    <div>
                        <h4 class="m-0 text-dark font-semibold display-font">{{ $employee->full_name }}</h4>
                        <span class="text-secondary fs-8.5 d-block mt-1">{{ $employee->designation ?? 'Role Unassigned' }} &mdash; {{ $employee->department->department_name ?? 'N/A' }}</span>
                        <div class="d-flex align-items-center gap-2 mt-2">
                            <span class="badge bg-indigo text-light">{{ $employee->employee_id }}</span>
                            <span class="badge bg-warning text-dark">{{ $employee->employment_status }}</span>
                        </div>
                    </div>
                </div>

                <!-- Onboarding Completion Progress Bar -->
                <div class="d-flex flex-column align-items-end min-w-[200px]">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="text-secondary fs-8.5 fw-semibold">Onboarding Completeness</span>
                        <span class="text-indigo fw-bold fs-7">{{ $completionRate }}%</span>
                    </div>
                    <div class="progress" style="width: 200px; height: 8px; background-color: var(--border-color); border-radius: 4px;">
                        <div class="progress-bar bg-indigo" role="progressbar" style="width: {{ $completionRate }}%; border-radius: 4px;" aria-valuenow="{{ $completionRate }}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <div class="d-flex gap-2 mt-3 flex-wrap justify-content-end">
                        @if(Auth::user()->role === 'Super Administrator' && $employee->employment_status === 'Onboarding')
                            <form action="{{ route('onboarding.skip', $employee->id) }}" method="POST" onsubmit="return confirm('Bypass onboarding and make {{ $employee->full_name }} active and available immediately?');" class="m-0">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-success text-white fw-bold">
                                    <i class="bi bi-fast-forward-fill me-1"></i> Skip Onboarding & Make Available
                                </button>
                            </form>
                        @endif
                        <a href="{{ route('onboarding.wizard', ['employee_id' => $employee->id]) }}" class="btn btn-custom-secondary btn-sm">
                            <i class="bi bi-pencil-square me-1"></i> Edit Onboarding
                        </a>
                        <a href="{{ route('onboarding.dashboard') }}" class="btn btn-custom-secondary btn-sm">
                            <i class="bi bi-arrow-left me-1"></i> Back
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Left Navigation Tabs (Vertical Pill layout) -->
    <div class="col-md-3">
        <div class="glass-card p-3">
            <div class="nav flex-column nav-pills me-3" id="profileTabs" role="tablist" aria-orientation="vertical">
                <button class="nav-link nav-link-custom text-start py-2.5 active" id="tab-overview-btn" data-bs-toggle="pill" data-bs-target="#tab-overview" type="button" role="tab">
                    <i class="bi bi-grid-fill me-2"></i> Onboarding Overview
                </button>
                <button class="nav-link nav-link-custom text-start py-2.5" id="tab-personal-btn" data-bs-toggle="pill" data-bs-target="#tab-personal" type="button" role="tab">
                    <i class="bi bi-person-badge-fill me-2"></i> Personal Information
                </button>
                <button class="nav-link nav-link-custom text-start py-2.5" id="tab-employment-btn" data-bs-toggle="pill" data-bs-target="#tab-employment" type="button" role="tab">
                    <i class="bi bi-briefcase-fill me-2"></i> Employment details
                </button>
                <button class="nav-link nav-link-custom text-start py-2.5" id="tab-devices-btn" data-bs-toggle="pill" data-bs-target="#tab-devices" type="button" role="tab">
                    <i class="bi bi-cpu-fill me-2 text-indigo"></i> Assigned Devices
                </button>
                <button class="nav-link nav-link-custom text-start py-2.5" id="tab-compensation-btn" data-bs-toggle="pill" data-bs-target="#tab-compensation" type="button" role="tab">
                    <i class="bi bi-wallet2 me-2"></i> Compensation Setup
                </button>
                <button class="nav-link nav-link-custom text-start py-2.5" id="tab-documents-btn" data-bs-toggle="pill" data-bs-target="#tab-documents" type="button" role="tab">
                    <i class="bi bi-file-earmark-arrow-up-fill me-2"></i> Required Documents
                </button>
                <button class="nav-link nav-link-custom text-start py-2.5" id="tab-agreements-btn" data-bs-toggle="pill" data-bs-target="#tab-agreements" type="button" role="tab">
                    <i class="bi bi-file-earmark-text-fill me-2"></i> Agreements & Signatures
                </button>
                <button class="nav-link nav-link-custom text-start py-2.5" id="tab-assets-btn" data-bs-toggle="pill" data-bs-target="#tab-assets" type="button" role="tab">
                    <i class="bi bi-laptop me-2"></i> Assigned Equipment
                </button>
                <button class="nav-link nav-link-custom text-start py-2.5" id="tab-approvals-btn" data-bs-toggle="pill" data-bs-target="#tab-approvals" type="button" role="tab">
                    <i class="bi bi-shield-check me-2"></i> Approvals Pipeline
                </button>
                <button class="nav-link nav-link-custom text-start py-2.5" id="tab-security-btn" data-bs-toggle="pill" data-bs-target="#tab-security" type="button" role="tab">
                    <i class="bi bi-shield-lock-fill me-2"></i> Security & Permissions
                </button>
            </div>
        </div>
    </div>

    <!-- Right Panels (Tab Content) -->
    <div class="col-md-9">
        <div class="tab-content" id="profileTabsContent">
            <!-- 1. OVERVIEW -->
            <div class="tab-pane fade show active" id="tab-overview" role="tabpanel">
                <div class="glass-card p-4 mb-4">
                    <h5 class="mb-4 display-font text-dark">Onboarding Checklist Verification</h5>
                    <div class="row g-4">
                        <div class="col-md-6 border-end" style="border-color: var(--border-color) !important;">
                            <ul class="list-unstyled mb-0">
                                @foreach($employee->checklistItems as $chk)
                                    <li class="d-flex align-items-center gap-3 py-2 border-bottom border-light border-opacity-10 last:border-0">
                                        <i class="bi {{ $chk->is_completed ? 'bi-check-circle-fill text-success fs-5' : 'bi-circle text-secondary fs-5' }}"></i>
                                        <div>
                                            <span class="d-block fw-semibold {{ $chk->is_completed ? 'text-dark' : 'text-secondary' }}">{{ $chk->task_name }}</span>
                                            @if($chk->is_completed)
                                                <span class="text-secondary fs-8 italic">Completed at {{ $chk->completed_at ? $chk->completed_at->format('Y-m-d H:i') : '' }}</span>
                                            @else
                                                <span class="text-secondary fs-8">Awaiting task fulfillment...</span>
                                            @endif
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-dark fw-bold mb-3">Onboarding Approvals Roadmap</h6>
                            <ul class="list-unstyled mb-0 position-relative ps-4" style="border-left: 2px dashed var(--border-color); margin-left: 10px;">
                                @foreach($employee->onboardingStages as $stg)
                                    <li class="mb-3 position-relative">
                                        <div class="position-absolute bg-{{ $stg->status === 'Approved' ? 'success' : 'warning' }} rounded-circle" style="width: 12px; height: 12px; left: -31px; top: 4px; border: 2px solid var(--sidebar-bg);"></div>
                                        <div class="fw-semibold text-dark fs-8.5">{{ $stg->stage_name }} Level Approval</div>
                                        <div class="text-secondary fs-8">Status: <strong class="{{ $stg->status === 'Approved' ? 'text-success' : 'text-warning' }}">{{ $stg->status }}</strong></div>
                                        @if($stg->approved_by)
                                            <div class="text-secondary fs-8">Approved by: {{ $stg->approver->username }}</div>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. PERSONAL INFO -->
            <div class="tab-pane fade" id="tab-personal" role="tabpanel">
                <div class="glass-card p-4">
                    <h5 class="mb-4 display-font text-dark">Personal & Contact Record</h5>
                    <div class="row g-3 fs-8.5">
                        <div class="col-md-4"><span class="text-secondary d-block uppercase fw-bold mb-1">Full Name</span><span class="text-dark">{{ $employee->title }} {{ $employee->first_name }} {{ $employee->middle_name }} {{ $employee->last_name }}</span></div>
                        <div class="col-md-4"><span class="text-secondary d-block uppercase fw-bold mb-1">Gender</span><span class="text-dark">{{ $employee->gender ?? 'N/A' }}</span></div>
                        <div class="col-md-4"><span class="text-secondary d-block uppercase fw-bold mb-1">NIC / Passport</span><span class="text-dark">{{ $employee->nic ?? 'N/A' }}</span></div>
                        
                        <div class="col-md-4"><span class="text-secondary d-block uppercase fw-bold mb-1">Nationality</span><span class="text-dark">{{ $employee->nationality ?? 'N/A' }}</span></div>
                        <div class="col-md-4"><span class="text-secondary d-block uppercase fw-bold mb-1">Religion</span><span class="text-dark">{{ $employee->religion ?? 'N/A' }}</span></div>
                        <div class="col-md-4"><span class="text-secondary d-block uppercase fw-bold mb-1">Marital Status</span><span class="text-dark">{{ $employee->marital_status ?? 'N/A' }}</span></div>
                        
                        <div class="col-md-4"><span class="text-secondary d-block uppercase fw-bold mb-1">Blood Group</span><span class="text-dark">{{ $employee->blood_group ?? 'N/A' }}</span></div>
                        <div class="col-md-4"><span class="text-secondary d-block uppercase fw-bold mb-1">Personal Email</span><span class="text-dark">{{ $employee->personal_email ?? 'N/A' }}</span></div>
                        <div class="col-md-4"><span class="text-secondary d-block uppercase fw-bold mb-1">Mobile Number</span><span class="text-dark">{{ $employee->mobile_number ?? 'N/A' }}</span></div>

                        <div class="col-md-6 border-top pt-3" style="border-color: var(--border-color) !important;"><span class="text-secondary d-block uppercase fw-bold mb-1">Permanent Address</span><span class="text-dark">{{ $employee->permanent_address ?? 'N/A' }}</span></div>
                        <div class="col-md-6 border-top pt-3" style="border-color: var(--border-color) !important;"><span class="text-secondary d-block uppercase fw-bold mb-1">Current Address</span><span class="text-dark">{{ $employee->current_address ?? 'N/A' }}</span></div>
                        
                        <div class="col-12 border-top pt-3" style="border-color: var(--border-color) !important;">
                            <span class="text-secondary d-block uppercase fw-bold mb-1">Drawn Employee Signature Capture</span>
                            @if($employee->signature)
                                <img src="{{ $employee->signature }}" style="max-height: 80px; filter: invert(1);" class="mt-2">
                            @else
                                <span class="text-secondary fs-8 italic">No handdrawn signature recorded.</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. EMPLOYMENT DETAILS -->
            <div class="tab-pane fade" id="tab-employment" role="tabpanel">
                <div class="glass-card p-4">
                    <h5 class="mb-4 display-font text-dark">Employment Specifications</h5>
                    <div class="row g-3 fs-8.5">
                        <div class="col-md-4"><span class="text-secondary d-block uppercase fw-bold mb-1">Company</span><span class="text-dark">{{ $employee->company->company_name ?? 'N/A' }}</span></div>
                        <div class="col-md-4"><span class="text-secondary d-block uppercase fw-bold mb-1">Branch</span><span class="text-dark">{{ $employee->branch->branch_name ?? 'N/A' }}</span></div>
                        <div class="col-md-4"><span class="text-secondary d-block uppercase fw-bold mb-1">Department</span><span class="text-dark">{{ $employee->department->department_name ?? 'N/A' }}</span></div>
                        
                        <div class="col-md-4"><span class="text-secondary d-block uppercase fw-bold mb-1">Designation</span><span class="text-dark">{{ $employee->designation ?? 'N/A' }}</span></div>
                        <div class="col-md-4"><span class="text-secondary d-block uppercase fw-bold mb-1">Job Grade</span><span class="text-dark">{{ $employee->job_grade ?? 'N/A' }}</span></div>
                        <div class="col-md-4"><span class="text-secondary d-block uppercase fw-bold mb-1">Primary Shift</span><span class="text-dark">{{ $employee->shift->shift_name ?? 'Unassigned' }}</span></div>
                        
                        <div class="col-md-4"><span class="text-secondary d-block uppercase fw-bold mb-1">Join Date</span><span class="text-dark">{{ $employee->join_date ? $employee->join_date->format('Y-m-d') : 'N/A' }}</span></div>
                        <div class="col-md-4"><span class="text-secondary d-block uppercase fw-bold mb-1">Probation Period</span><span class="text-dark">{{ $employee->probation_period ?? 0 }} Months</span></div>
                        <div class="col-md-4"><span class="text-secondary d-block uppercase fw-bold mb-1">Confirmation Date</span><span class="text-dark">{{ $employee->confirmation_date ?? 'N/A' }}</span></div>
                    </div>
                </div>
            </div>

            <!-- ASSIGNED BIOMETRIC DEVICES TAB -->
            <div class="tab-pane fade" id="tab-devices" role="tabpanel">
                <div class="glass-card p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                        <h5 class="m-0 display-font text-dark"><i class="bi bi-cpu me-2 text-indigo"></i> Assigned Biometric Terminals & Sync Status</h5>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#assignDevicesModal">
                                <i class="bi bi-plus-circle me-1"></i> Assign Terminals
                            </button>
                            <form action="{{ route('employees.sync', $employee->id) }}" method="POST" class="m-0">
                                @csrf
                                <button type="submit" class="btn btn-custom-primary btn-sm">
                                    <i class="bi bi-arrow-repeat me-1"></i> Sync All Devices
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table custom-table">
                            <thead>
                                <tr>
                                    <th>Terminal Name</th>
                                    <th>Serial Number</th>
                                    <th>Status</th>
                                    <th>Location</th>
                                    <th>Last Activity</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($employee->devices as $dev)
                                    <tr>
                                        <td class="fw-semibold text-dark fs-8.5">{{ $dev->device_name }}</td>
                                        <td class="text-secondary fs-8"><span class="badge bg-indigo text-light">{{ $dev->device_serial_number }}</span></td>
                                        <td>
                                            <span class="badge-status {{ $dev->status_text === 'Online' ? 'badge-online' : 'badge-disabled' }} fs-8">
                                                {{ $dev->status_text ?? $dev->status }}
                                            </span>
                                        </td>
                                        <td class="text-secondary fs-8">{{ $dev->location ?? 'Not Specified' }}</td>
                                        <td class="text-secondary fs-8">{{ $dev->last_seen ? $dev->last_seen->diffForHumans() : 'Never' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-secondary py-3">
                                            No biometric devices currently assigned to {{ $employee->full_name }}.
                                            <button type="button" class="btn btn-link text-indigo p-0 border-0 align-baseline" data-bs-toggle="modal" data-bs-target="#assignDevicesModal">Assign terminals now</button>.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Modal for Assigning Terminals in Profile View -->
            <div class="modal fade" id="assignDevicesModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content glass-card p-0" >
                        <div class="modal-header border-bottom-0 pb-0">
                            <h5 class="modal-title display-font"><i class="bi bi-cpu me-2 text-indigo"></i> Assign Biometric Terminals</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form action="{{ route('employees.update', $employee->id) }}" method="POST">
                            @csrf
                            <input type="hidden" name="employee_id" value="{{ $employee->employee_id }}">
                            <input type="hidden" name="company_id" value="{{ $employee->company_id }}">
                            <input type="hidden" name="department_id" value="{{ $employee->department_id }}">
                            <input type="hidden" name="first_name" value="{{ $employee->first_name }}">
                            <input type="hidden" name="last_name" value="{{ $employee->last_name }}">
                            <input type="hidden" name="status" value="{{ $employee->status }}">

                            <div class="modal-body text-start">
                                <div class="mb-3">
                                    <label class="form-label text-secondary">Select Biometric Devices to Assign</label>
                                    @php 
                                        $allDevices = \App\Models\Device::all();
                                        $assignedIds = $employee->devices->pluck('id')->toArray();
                                    @endphp
                                    <select class="form-select form-select-custom" name="device_ids[]" multiple style="height: 140px;">
                                        @forelse($allDevices as $dev)
                                            <option value="{{ $dev->id }}" {{ in_array($dev->id, $assignedIds) ? 'selected' : '' }}>
                                                {{ $dev->device_name }} ({{ $dev->device_serial_number }}) - {{ $dev->status_text ?? $dev->status }}
                                            </option>
                                        @empty
                                            <option value="" disabled>No biometric terminals registered in system</option>
                                        @endforelse
                                    </select>
                                    <div class="form-text text-secondary fs-8">Hold Ctrl/Cmd to select multiple terminals.</div>
                                </div>
                            </div>
                            <div class="modal-footer border-top-0 pt-0">
                                <button type="button" class="btn btn-custom-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-custom-primary">Save & Sync Terminals</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- 4. COMPENSATION SETUP -->
            <div class="tab-pane fade" id="tab-compensation" role="tabpanel">
                <div class="glass-card p-4">
                    <h5 class="mb-4 display-font text-dark">Payroll Setup & Compensation</h5>
                    <div class="row g-3 fs-8.5">
                        <div class="col-md-6"><span class="text-secondary d-block uppercase fw-bold mb-1">Basic Salary</span><span class="text-dark font-semibold fs-5">{{ $employee->currency }} {{ number_format($employee->basic_salary, 2) }}</span></div>
                        <div class="col-md-6"><span class="text-secondary d-block uppercase fw-bold mb-1">Payment Method</span><span class="text-dark">{{ $employee->payment_method }}</span></div>
                        
                        <div class="col-md-4"><span class="text-secondary d-block uppercase fw-bold mb-1">Bank Name</span><span class="text-dark">{{ $employee->bank_name ?? 'N/A' }}</span></div>
                        <div class="col-md-4"><span class="text-secondary d-block uppercase fw-bold mb-1">Account Number</span><span class="text-dark">{{ $employee->bank_account ?? 'N/A' }}</span></div>
                        <div class="col-md-4"><span class="text-secondary d-block uppercase fw-bold mb-1">SWIFT Code</span><span class="text-dark">{{ $employee->bank_swift ?? 'N/A' }}</span></div>
                    </div>
                </div>
            </div>

            <!-- 5. REQUIRED DOCUMENTS -->
            <div class="tab-pane fade" id="tab-documents" role="tabpanel">
                <div class="glass-card p-4">
                    <h5 class="mb-4 display-font text-dark">Required Onboarding Documents</h5>
                    <div class="table-responsive">
                        <table class="table custom-table">
                            <thead>
                                <tr>
                                    <th>Document Type</th>
                                    <th>Upload Status</th>
                                    <th>Expiry Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($employee->documents as $doc)
                                    <tr>
                                        <td class="fw-semibold text-dark fs-8.5">{{ $doc->document_name }}</td>
                                        <td>
                                            <span class="badge-status {{ $doc->status === 'Uploaded' ? 'badge-online' : 'badge-offline' }} fs-8">
                                                {{ $doc->status }}
                                            </span>
                                        </td>
                                        <td class="text-secondary fs-8">{{ $doc->expiry_date ?: 'No Expiry' }}</td>
                                        <td>
                                            @if($doc->file_path)
                                                <a href="{{ asset($doc->file_path) }}" target="_blank" class="btn btn-sm btn-outline-secondary border-0 text-indigo p-1 fs-8">
                                                    <i class="bi bi-eye-fill"></i> View
                                                </a>
                                            @else
                                                <span class="text-secondary fs-8 italic">Unavailable</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-secondary py-3">No documents uploaded.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- 6. AGREEMENTS -->
            <div class="tab-pane fade" id="tab-agreements" role="tabpanel">
                <div class="glass-card p-4">
                    <h5 class="mb-4 display-font text-dark">Digital Signatures & Employment Agreements</h5>
                    <div class="table-responsive">
                        <table class="table custom-table">
                            <thead>
                                <tr>
                                    <th>Agreement</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Signed At</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($employee->agreements as $ag)
                                    <tr>
                                        <td class="fw-semibold text-dark fs-8.5">{{ $ag->template->name }}</td>
                                        <td><span class="badge bg-indigo text-light fs-8">{{ $ag->template->type }}</span></td>
                                        <td>
                                            <span class="badge-status {{ $ag->status === 'Signed' ? 'badge-online' : 'badge-pending' }} fs-8">
                                                {{ $ag->status }}
                                            </span>
                                        </td>
                                        <td class="text-secondary fs-8">{{ $ag->signed_at ?: 'Awaiting Signatures' }}</td>
                                        <td>
                                            @if($ag->file_path)
                                                <a href="{{ asset($ag->file_path) }}" target="_blank" class="btn btn-sm btn-outline-secondary border-0 text-indigo p-1 fs-8">
                                                    <i class="bi bi-download"></i> Download PDF
                                                </a>
                                            @else
                                                <span class="text-secondary fs-8 italic">Unavailable</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-secondary py-3">No agreements compiled yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- 7. ASSIGNED ASSETS -->
            <div class="tab-pane fade" id="tab-assets" role="tabpanel">
                <div class="glass-card p-4">
                    <h5 class="mb-4 display-font text-dark">Issued Equipment & Assets Tracker</h5>
                    <div class="table-responsive">
                        <table class="table custom-table">
                            <thead>
                                <tr>
                                    <th>Asset Item</th>
                                    <th>Serial Number</th>
                                    <th>Status</th>
                                    <th>Assigned Date</th>
                                    <th>Returned Date</th>
                                    <th>Receipt</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($employee->assets as $as)
                                    <tr>
                                        <td class="fw-semibold text-dark fs-8.5">{{ $as->asset_name }}</td>
                                        <td class="text-secondary fs-8">{{ $as->serial_number ?: 'N/A' }}</td>
                                        <td>
                                            <span class="badge-status {{ $as->status === 'Assigned' ? 'badge-online' : 'badge-disabled' }} fs-8">
                                                {{ $as->status }}
                                            </span>
                                        </td>
                                        <td class="text-secondary fs-8">{{ $as->assigned_date }}</td>
                                        <td class="text-secondary fs-8">{{ $as->returned_date ?: '—' }}</td>
                                        <td>
                                            <a href="{{ route('onboarding.download-asset-handover', $as->id) }}" class="btn btn-sm btn-outline-secondary border-0 p-1 text-indigo fs-8">
                                                <i class="bi bi-file-earmark-pdf-fill"></i> Download PDF
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-secondary py-3">No assets registered to this profile.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- 8. APPROVALS ROUTING -->
            <div class="tab-pane fade" id="tab-approvals" role="tabpanel">
                <div class="glass-card p-4">
                    <h5 class="mb-4 display-font text-dark">Multi-Department Approval Pipeline</h5>
                    
                    <div class="table-responsive mb-4">
                        <table class="table custom-table">
                            <thead>
                                <tr>
                                    <th>Stage Node</th>
                                    <th>Approval Status</th>
                                    <th>Signed By</th>
                                    <th>Comments</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($employee->onboardingStages as $stg)
                                    <tr>
                                        <td class="fw-semibold text-dark fs-8.5">{{ $stg->stage_name }} Stage</td>
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

                    <!-- Approval Form Panel -->
                    <div class="card p-4" style="background-color: var(--input-bg); border: 1px solid var(--border-color);">
                        <h6 class="text-dark fw-bold mb-3">Sign Off / Approver Action Form</h6>
                        <form action="{{ route('onboarding.approve', $employee->id) }}" method="POST">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label text-secondary fs-8 uppercase fw-bold mb-1">Select Approval Stage Node</label>
                                    <select name="stage_name" class="form-select form-select-custom" required>
                                        @foreach($employee->onboardingStages as $stg)
                                            <option value="{{ $stg->stage_name }}">{{ $stg->stage_name }} Stage (Current: {{ $stg->status }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-secondary fs-8 uppercase fw-bold mb-1">Approval status</label>
                                    <select name="status" class="form-select form-select-custom" required>
                                        <option value="Approved">Approved (Sign off)</option>
                                        <option value="Rejected">Rejected</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label text-secondary fs-8 uppercase fw-bold mb-1">Comments / Notes</label>
                                    <textarea name="comments" class="form-control form-control-custom" rows="2" placeholder="Enter comments or reason if rejected..."></textarea>
                                </div>
                                <div class="col-12 mt-3">
                                    <button type="submit" class="btn btn-custom-primary px-4 py-2 border-0">
                                        <i class="bi bi-shield-check me-1"></i> Submit Decision
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>            <!-- 9. SECURITY & PERMISSIONS -->
            <div class="tab-pane fade" id="tab-security" role="tabpanel">
                <form action="{{ route('onboarding.security.update', $employee->id) }}" method="POST">
                    @csrf
                    
                    @php
                        $user = $employee->user;
                        $hasUser = !empty($user);
                        $direct = $hasUser ? $user->directPermissions->pluck('value', 'permission_key')->toArray() : [];
                        $roleRec = $hasUser ? \App\Models\Role::where('name', $user->role)->first() : null;
                        $rolePermissions = ($roleRec && is_array($roleRec->permissions)) ? $roleRec->permissions : [];
                    @endphp

                    <!-- 1. Login Account Settings -->
                    <div class="glass-card p-4 mb-4">
                        <h5 class="mb-4 display-font text-dark"><i class="bi bi-shield-lock me-2 text-indigo"></i> Security Settings & Account Profile</h5>
                        
                        <div class="form-check form-switch mb-4">
                            <input class="form-check-input" type="checkbox" name="enable_login" id="enable_login" value="1" {{ ($hasUser && $user->status === 'Active') ? 'checked' : '' }} onchange="toggleLoginFields()">
                            <label class="form-check-label fw-bold text-dark" for="enable_login">Enable Console Login Account</label>
                            <small class="text-secondary d-block">Enable this option to allow the employee to log into the management console.</small>
                        </div>

                        <div id="login-fields" style="{{ ($hasUser && $user->status === 'Active') ? '' : 'display: none;' }}">
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label text-dark fs-8.5">Username</label>
                                    <input type="text" name="username" class="form-control bg-transparent text-dark fs-8.5" value="{{ $hasUser ? $user->username : '' }}" placeholder="Enter username">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-dark fs-8.5">Email Address</label>
                                    <input type="email" name="email" class="form-control bg-transparent text-dark fs-8.5" value="{{ $hasUser ? $user->email : $employee->email }}" placeholder="Enter email">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-dark fs-8.5">Password {{ $hasUser ? '(Leave blank to keep current)' : '' }}</label>
                                    <input type="password" name="password" class="form-control bg-transparent text-dark fs-8.5" placeholder="Min 8 characters">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-dark fs-8.5">Account Status</label>
                                    <select name="status" class="form-select bg-transparent text-dark fs-8.5">
                                        <option value="Active" {{ ($hasUser && $user->status === 'Active') ? 'selected' : '' }}>Active</option>
                                        <option value="Inactive" {{ ($hasUser && $user->status === 'Inactive') ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check mt-4">
                                        <input class="form-check-input" type="checkbox" name="force_password_change" id="force_password_change" value="1" {{ ($hasUser && $user->force_password_change) ? 'checked' : '' }}>
                                        <label class="form-check-label text-dark fs-8.5" for="force_password_change">Force Password Change on Next Login</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-dark fs-8.5">Multi-Factor Authentication Status</label>
                                    <span class="badge bg-secondary bg-opacity-15 text-secondary d-block py-2 text-start">Not Configured</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Role, Level & Template Configuration -->
                    <div class="glass-card p-4 mb-4" id="role-fields" style="{{ ($hasUser && $user->status === 'Active') ? '' : 'display: none;' }}">
                        <h5 class="mb-4 display-font text-dark"><i class="bi bi-person-workspace me-2 text-indigo"></i> Business Role & Access Level</h5>
                        
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label text-dark fs-8.5">Business Role</label>
                                <select name="role" class="form-select bg-transparent text-dark fs-8.5">
                                    <option value="">Select Business Role...</option>
                                    @foreach($roles as $r)
                                        <option value="{{ $r->name }}" {{ ($hasUser && $user->role === $r->name) ? 'selected' : '' }}>{{ $r->name }}</option>
                                    @endforeach
                                </select>
                                <small class="text-secondary">Defines the primary business role and permissions category.</small>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-dark fs-8.5">Access Level</label>
                                <select name="access_level_id" class="form-select bg-transparent text-dark fs-8.5">
                                    <option value="">No Access Level (L0)</option>
                                    @foreach($accessLevels as $al)
                                        <option value="{{ $al->id }}" {{ ($hasUser && $user->access_level_id == $al->id) ? 'selected' : '' }}>{{ $al->getLabel() }}</option>
                                    @endforeach
                                </select>
                                <small class="text-secondary">Defines authority level (L0 - L5) with cascading inheritance.</small>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-dark fs-8.5">Permission Template</label>
                                <select name="template_id" class="form-select bg-transparent text-dark fs-8.5">
                                    <option value="">No Template (Use base role)</option>
                                    @foreach($templates as $t)
                                        <option value="{{ $t->id }}" {{ ($hasUser && $user->template_id == $t->id) ? 'selected' : '' }}>{{ $t->name }}</option>
                                    @endforeach
                                </select>
                                <small class="text-secondary">Pre-defined set of permission overrides matching HR templates.</small>
                            </div>
                        </div>
                    </div>

                    <!-- 3. Direct Overrides and Matrix -->
                    <div class="glass-card p-4 mb-4" id="override-fields" style="{{ ($hasUser && $user->status === 'Active') ? '' : 'display: none;' }}">
                        <h5 class="mb-4 display-font text-dark"><i class="bi bi-sliders me-2 text-indigo"></i> Direct Permission Overrides</h5>
                        <p class="text-secondary fs-8.5">Assign explicit permissions to this employee. Allow overrides base role / levels; Deny blocks access unconditionally.</p>
                        
                        <div class="mb-3">
                            <label class="form-label text-dark fs-8.5">Override Modification Reason</label>
                            <input type="text" name="override_reason" class="form-control bg-transparent text-dark fs-8.5" placeholder="Enter reason for modifying permissions (required for history)">
                        </div>

                        <div class="accordion custom-accordion" id="accordionPermissions">
                            @foreach($matrix as $group => $items)
                                @php $groupId = str_replace([' ', '&'], '_', strtolower($group)); @endphp
                                <div class="accordion-item border border-light border-opacity-10 bg-transparent mb-2 rounded overflow-hidden">
                                    <h2 class="accordion-header" id="heading-{{ $groupId }}">
                                        <button class="accordion-button collapsed py-3 text-dark fw-bold bg-transparent fs-8.5" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-{{ $groupId }}">
                                            {{ $group }}
                                        </button>
                                    </h2>
                                    <div id="collapse-{{ $groupId }}" class="accordion-collapse collapse" data-bs-parent="#accordionPermissions">
                                        <div class="accordion-body bg-transparent py-3">
                                            <div class="table-responsive">
                                                <table class="table table-sm custom-table m-0">
                                                    <thead>
                                                        <tr>
                                                            <th>Permission Key</th>
                                                            <th style="width: 120px;" class="text-center">Allow</th>
                                                            <th style="width: 120px;" class="text-center">Deny</th>
                                                            <th style="width: 120px;" class="text-center">Inherit</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($items as $key => $label)
                                                            @php $curVal = $direct[$key] ?? 'Inherit'; @endphp
                                                            <tr>
                                                                <td>
                                                                    <span class="d-block fw-semibold text-dark fs-8.5">{{ $label }}</span>
                                                                    <code class="fs-9 text-secondary">{{ $key }}</code>
                                                                </td>
                                                                <td class="text-center">
                                                                    <input class="form-check-input" type="radio" name="permissions[{{ $key }}]" value="Allow" {{ $curVal === 'Allow' ? 'checked' : '' }}>
                                                                </td>
                                                                <td class="text-center">
                                                                    <input class="form-check-input" type="radio" name="permissions[{{ $key }}]" value="Deny" {{ $curVal === 'Deny' ? 'checked' : '' }}>
                                                                </td>
                                                                <td class="text-center">
                                                                    <input class="form-check-input" type="radio" name="permissions[{{ $key }}]" value="Inherit" {{ $curVal === 'Inherit' ? 'checked' : '' }}>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- 4. Save Button -->
                    <div class="d-flex justify-content-end mb-4 gap-3">
                        <button type="submit" class="btn btn-indigo px-4 py-2 text-white fw-bold"><i class="bi bi-save me-1"></i> Save Security Configuration</button>
                    </div>
                </form>

                <!-- 5. Effective Permissions (Read-only) -->
                @if($hasUser)
                    @php
                        $effPerms = app(\App\Services\PermissionService::class)->getEffectivePermissions($user);
                    @endphp
                    <div class="glass-card p-4 mb-4" id="effective-fields">
                        <h5 class="mb-4 display-font text-dark"><i class="bi bi-check-circle me-2 text-indigo"></i> Effective Resolved Permissions</h5>
                        <p class="text-secondary fs-8.5">Displays the final evaluation of permission rules after applying inheritance, templates, and overrides.</p>
                        
                        <div class="row g-2">
                            @foreach($matrix as $group => $items)
                                <div class="col-12 mt-3 mb-1">
                                    <span class="text-indigo fw-bold fs-8.5 display-font uppercase">{{ $group }}</span>
                                </div>
                                @foreach($items as $key => $label)
                                    @php 
                                        $eff = $effPerms[$key] ?? ['allow' => false, 'source' => 'denied']; 
                                    @endphp
                                    <div class="col-md-6">
                                        <div class="p-2 border rounded d-flex justify-content-between align-items-center" style="border-color: var(--border-color) !important;">
                                            <div class="d-flex flex-column">
                                                <span class="fs-8.5 text-dark fw-semibold">{{ $label }}</span>
                                                <code class="fs-9 text-secondary">{{ $key }}</code>
                                            </div>
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="badge bg-{{ $eff['allow'] ? 'success' : 'danger' }} bg-opacity-15 text-{{ $eff['allow'] ? 'success' : 'danger' }} fs-9">{{ $eff['allow'] ? 'Allow' : 'Deny' }}</span>
                                                <span class="badge bg-secondary bg-opacity-10 text-secondary fs-9" style="text-transform: capitalize;">Source: {{ str_replace('_', ' ', $eff['source']) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @endforeach
                        </div>
                    </div>

                    <!-- 6. Security Audit History -->
                    <div class="glass-card p-4 mb-4" id="audit-fields">
                        <h5 class="mb-4 display-font text-dark"><i class="bi bi-journal-text me-2 text-indigo"></i> Security Modifications History</h5>
                        
                        <div class="table-responsive">
                            <table class="table table-sm custom-table m-0">
                                <thead>
                                    <tr>
                                        <th>Rule / Action</th>
                                        <th class="text-center">Old State</th>
                                        <th class="text-center">New State</th>
                                        <th>Changed By</th>
                                        <th>Reason / IP</th>
                                        <th class="text-end">Timestamp</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($user->permissionHistory as $hist)
                                        <tr>
                                            <td><code class="fs-9 text-indigo">{{ $hist->permission_key }}</code></td>
                                            <td class="text-center">
                                                <span class="badge bg-{{ $hist->old_value === 'Allow' ? 'success' : ($hist->old_value === 'Deny' ? 'danger' : 'secondary') }} fs-9">
                                                    {{ $hist->old_value }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-{{ $hist->new_value === 'Allow' ? 'success' : ($hist->new_value === 'Deny' ? 'danger' : 'secondary') }} fs-9">
                                                    {{ $hist->new_value }}
                                                </span>
                                            </td>
                                            <td><span class="text-secondary fs-9">{{ $hist->editor->username ?? 'System' }}</span></td>
                                            <td class="fs-9 text-secondary" style="max-width: 150px;">
                                                <div>{{ $hist->reason ?? 'N/A' }}</div>
                                                <div class="text-muted text-[10px]">{{ $hist->ip_address }}</div>
                                            </td>
                                            <td class="text-end text-muted fs-9">{{ $hist->created_at->format('Y-m-d H:i') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-secondary py-3 fs-9">No override modifications history recorded.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>

            <script>
                function toggleLoginFields() {
                    var chk = document.getElementById('enable_login');
                    var loginFields = document.getElementById('login-fields');
                    var roleFields = document.getElementById('role-fields');
                    var overrideFields = document.getElementById('override-fields');
                    
                    if (chk.checked) {
                        loginFields.style.display = 'block';
                        roleFields.style.display = 'block';
                        overrideFields.style.display = 'block';
                    } else {
                        loginFields.style.display = 'none';
                        roleFields.style.display = 'none';
                        overrideFields.style.display = 'none';
                    }
                }
            </script>
        </div>
    </div>
</div>
@endsection
