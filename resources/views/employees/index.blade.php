@extends('layouts.app')

@section('content')
<!-- Metric Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3 col-xl-auto flex-fill animate-fade-up">
        <div class="glass-card p-3 d-flex align-items-center justify-content-between h-100">
            <div>
                <span class="text-secondary fs-8 d-block fw-medium">Total Staff</span>
                <h4 class="fw-bold m-0 text-dark mt-1">{{ $stats['total'] }}</h4>
            </div>
            <div class="rounded-circle p-2 bg-indigo-subtle text-indigo d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                <i class="bi bi-people-fill fs-5"></i>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3 col-xl-auto flex-fill animate-fade-up delay-100">
        <div class="glass-card p-3 d-flex align-items-center justify-content-between h-100">
            <div>
                <span class="text-secondary fs-8 d-block fw-medium">Active Status</span>
                <h4 class="fw-bold m-0 text-success mt-1">{{ $stats['active'] }}</h4>
            </div>
            <div class="rounded-circle p-2 bg-success-subtle text-success d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                <i class="bi bi-person-check-fill fs-5"></i>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3 col-xl-auto flex-fill animate-fade-up delay-100">
        <div class="glass-card p-3 d-flex align-items-center justify-content-between h-100">
            <div>
                <span class="text-secondary fs-8 d-block fw-medium">Inactive / Resigned</span>
                <h4 class="fw-bold m-0 text-muted mt-1">{{ $stats['inactive'] }}</h4>
            </div>
            <div class="rounded-circle p-2 bg-secondary-subtle text-secondary d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                <i class="bi bi-person-x-fill fs-5"></i>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3 col-xl-auto flex-fill animate-fade-up delay-200">
        <div class="glass-card p-3 d-flex align-items-center justify-content-between h-100">
            <div>
                <span class="text-secondary fs-8 d-block fw-medium">ADMS Synchronized</span>
                <h4 class="fw-bold m-0 text-info mt-1">{{ $stats['synced'] }}</h4>
            </div>
            <div class="rounded-circle p-2 bg-info-subtle text-info d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                <i class="bi bi-cloud-check-fill fs-5"></i>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3 col-xl-auto flex-fill animate-fade-up delay-200">
        <div class="glass-card p-3 d-flex align-items-center justify-content-between h-100">
            <div>
                <span class="text-secondary fs-8 d-block fw-medium">Pending ADMS Sync</span>
                <h4 class="fw-bold m-0 text-warning mt-1">{{ $stats['pending_sync'] }}</h4>
            </div>
            <div class="rounded-circle p-2 bg-warning-subtle text-warning d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                <i class="bi bi-arrow-repeat fs-5"></i>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3 col-xl-auto flex-fill animate-fade-up delay-300">
        <div class="glass-card p-3 d-flex align-items-center justify-content-between h-100">
            <div>
                <span class="text-secondary fs-8 d-block fw-medium">Biometric Terminals</span>
                <div class="d-flex align-items-center gap-2 mt-1">
                    <span class="badge bg-success text-light fs-8"><i class="bi bi-wifi me-1"></i>{{ $stats['online_devices'] }} Online</span>
                    <span class="badge bg-danger text-light fs-8"><i class="bi bi-wifi-off me-1"></i>{{ $stats['offline_devices'] }} Offline</span>
                </div>
            </div>
            <div class="rounded-circle p-2 bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                <i class="bi bi-cpu-fill fs-5"></i>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Full Width List of Employees -->
    <div class="col-12">
        <div class="glass-card animate-fade-up delay-300">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
                <div>
                    <h5 class="m-0 display-font"><i class="bi bi-people me-2 text-indigo"></i> Employee Directory</h5>
                    <p class="text-secondary fs-8 m-0 mt-1">Manage corporate staff profiles, assignments, and ADMS biometric status.</p>
                </div>
                <link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" rel="stylesheet">
                <style>
                    #cropperImage { max-width: 100%; display: block; }
                    .preview-container { text-align: left; margin-top: 10px; }
                    .croppedPreview { border-radius: 50%; width: 100px; height: 100px; object-fit: cover; display: none; border: 2px solid var(--primary); }
                </style>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-indigo-subtle text-indigo px-3 py-2 rounded-pill fs-7 fw-semibold me-2">
                        <i class="bi bi-layers-fill me-1"></i> Showing {{ $employees->count() }} Profiles
                    </span>
                    <a href="{{ route('employees.downloadTemplate') }}" class="btn btn-outline-info px-3 py-2 fw-semibold fs-7">
                        <i class="bi bi-file-earmark-arrow-down-fill me-1"></i> Download Template
                    </a>
                    <a href="{{ route('employees.export', request()->all()) }}" class="btn btn-outline-success px-3 py-2 fw-semibold fs-7">
                        <i class="bi bi-file-earmark-excel-fill me-1"></i> Bulk Export
                    </a>
                    <button type="button" class="btn btn-outline-primary px-3 py-2 fw-semibold fs-7" data-bs-toggle="modal" data-bs-target="#bulkImportModal">
                        <i class="bi bi-file-earmark-spreadsheet-fill me-1"></i> Bulk Import
                    </button>
                    <a href="{{ route('employees.create') }}" class="btn btn-custom-primary px-3 py-2 fw-semibold fs-7">
                        <i class="bi bi-person-plus-fill me-1"></i> Register Employee
                    </a>
                </div>
            </div>
            
            <!-- Quick Filter & Search Toolbar -->
            <form action="{{ route('employees') }}" method="GET" class="mb-4">
                <div class="row g-2">
                    <div class="col-12 col-md-4">
                        <div class="input-group">
                            <span class="input-group-text bg-transparent border-end-0" style="border-color: var(--border); color: var(--text-muted);">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" name="search" class="form-control form-control-custom border-start-0" placeholder="Search employee, ID, NIC..." value="{{ request('search') }}" style="border-top-left-radius: 0; border-bottom-left-radius: 0;">
                        </div>
                    </div>
                    <div class="col-6 col-md-2">
                        <select name="company_id" class="form-select form-select-custom" onchange="this.form.submit()">
                            <option value="">All Companies</option>
                            @foreach($companies as $c)
                                <option value="{{ $c->id }}" {{ request('company_id') == $c->id ? 'selected' : '' }}>{{ $c->company_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <select name="branch_id" class="form-select form-select-custom" onchange="this.form.submit()">
                            <option value="">All Branches</option>
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}" {{ request('branch_id') == $b->id ? 'selected' : '' }}>{{ $b->branch_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <select name="department_id" class="form-select form-select-custom" onchange="this.form.submit()">
                            <option value="">All Depts</option>
                            @foreach($departments as $d)
                                <option value="{{ $d->id }}" {{ request('department_id') == $d->id ? 'selected' : '' }}>{{ $d->department_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-2 d-flex gap-1">
                        <select name="status" class="form-select form-select-custom" onchange="this.form.submit()">
                            <option value="">All Statuses</option>
                            <option value="Active" {{ request('status') === 'Active' ? 'selected' : '' }}>Active</option>
                            <option value="Inactive" {{ request('status') === 'Inactive' ? 'selected' : '' }}>Inactive</option>
                            <option value="Resigned" {{ request('status') === 'Resigned' ? 'selected' : '' }}>Resigned</option>
                        </select>
                        @if(request()->hasAny(['search', 'company_id', 'branch_id', 'department_id', 'status', 'biometric_status']))
                            <a href="{{ route('employees') }}" class="btn btn-custom-secondary px-2 d-flex align-items-center" title="Reset Filters">
                                <i class="bi bi-arrow-counterclockwise"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </form>

            <!-- Bulk Update Form Wrapper -->
            <form id="bulkUpdateForm" action="{{ route('employees.bulk-update') }}" method="POST">
                @csrf
                <!-- Bulk Action Bar -->
                <div id="bulkActionBar" class="mb-4 p-3 rounded d-none animate-slide-up" style="background-color: rgba(99, 102, 241, 0.12); border: 1px solid rgba(99, 102, 241, 0.25);">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span id="selectedCount" class="fw-semibold text-primary" style="font-size: 1.1rem;">0</span> <span class="text-secondary fs-7">employees selected</span>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-outline-success btn-sm px-3 fw-semibold" formaction="{{ route('employees.bulk-skip-onboarding') }}">
                                <i class="bi bi-person-check-fill me-1"></i> Skip Onboarding
                            </button>
                            <button type="submit" class="btn btn-outline-warning btn-sm px-3 fw-semibold" id="bulkForceSyncBtn">
                                <i class="bi bi-lightning-charge-fill me-1"></i> Sync to Devices
                            </button>
                            <button type="submit" class="btn btn-outline-danger btn-sm px-3 fw-semibold" formaction="{{ route('employees.bulk-delete') }}" onclick="return confirm('Are you sure you want to delete these employees?');">
                                <i class="bi bi-trash-fill me-1"></i> Delete
                            </button>
                            <button type="button" class="btn btn-custom-primary btn-sm px-3" data-bs-toggle="modal" data-bs-target="#bulkEditModal">
                                <i class="bi bi-pencil-square me-1"></i> Bulk Edit
                            </button>
                        </div>
                    </div>
                </div>

                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const bulkForceSyncBtn = document.getElementById('bulkForceSyncBtn');
                        if (bulkForceSyncBtn) {
                            bulkForceSyncBtn.addEventListener('click', function(e) {
                                e.preventDefault();
                                
                                let forceInput = document.createElement('input');
                                forceInput.type = 'hidden';
                                forceInput.name = 'force';
                                forceInput.value = '1';
                                document.getElementById('bulkUpdateForm').appendChild(forceInput);
                                
                                document.getElementById('bulkUpdateForm').action = "{{ route('employees.bulk-sync') }}";
                                document.getElementById('bulkUpdateForm').submit();
                            });
                        }
                    });
                </script>

                <div class="table-responsive">
                    <table class="table custom-table align-middle">
                        <thead>
                            <tr>
                                <th style="width: 40px;">
                                    <div class="form-check m-0">
                                        <input class="form-check-input check-custom border-secondary" type="checkbox" id="selectAllEmployees">
                                    </div>
                                </th>
                                <th>Staff</th>
                                <th>IDs & PIN</th>
                                <th>Organization</th>
                                <th>Department & Post</th>
                                <th>Shift Schedule</th>
                                <th>Work Arrangement</th>
                                <th>Status & ADMS</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($employees as $e)
                                <tr>
                                    <td>
                                        <div class="form-check m-0">
                                            <input class="form-check-input employee-checkbox check-custom border-secondary" type="checkbox" name="employee_ids[]" value="{{ $e->id }}">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            @if($e->profile_photo)
                                                <img src="{{ asset($e->profile_photo) }}" alt="avatar" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover; border: 2px solid rgba(99,102,241,0.3);">
                                            @else
                                                <div class="rounded-circle text-center bg-indigo text-light d-flex align-items-center justify-content-center fw-bold fs-7 shadow-sm" style="width: 40px; height: 40px; background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);">
                                                    {{ strtoupper(substr($e->first_name, 0, 1) . substr($e->last_name, 0, 1)) }}
                                                </div>
                                            @endif
                                            <div>
                                                <div class="fw-semibold text-dark">{{ $e->full_name }}</div>
                                                <div class="fs-8 text-secondary">{{ $e->email ?? 'No email registered' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-indigo text-light font-monospace fs-8">{{ $e->device_user_id ?? $e->employee_id }}</span>
                                        <div class="text-secondary fs-8 mt-1"><i class="bi bi-hash me-1"></i>PIN: <span class="fw-bold text-dark font-monospace">{{ $e->sync_pin ?? 'N/A' }}</span></div>
                                    </td>
                                    <td>
                                        <div class="text-dark fw-medium">{{ $e->company->company_name }}</div>
                                        <div class="text-secondary fs-8"><i class="bi bi-geo-alt me-1"></i>{{ $e->branch ? $e->branch->branch_name : 'Default Branch' }}</div>
                                    </td>
                                    <td>
                                        <div class="text-dark fw-medium">{{ $e->department->department_name }}</div>
                                        <div class="text-secondary fs-8"><i class="bi bi-briefcase me-1"></i>{{ $e->designation ?? 'Staff Member' }}</div>
                                    </td>
                                    <td>
                                        @if($e->shift)
                                            <div class="mb-1"><span class="badge bg-primary-subtle text-primary border border-primary-subtle fs-8"><i class="bi bi-clock me-1"></i>{{ $e->shift->shift_name }}</span></div>
                                        @endif
                                        @if($e->secondaryShift)
                                            <div><span class="badge bg-info-subtle text-info border border-info-subtle fs-8"><i class="bi bi-clock-history me-1"></i>{{ $e->secondaryShift->shift_name }}</span></div>
                                        @endif
                                        @if(!$e->shift && !$e->secondaryShift)
                                            <span class="text-secondary fs-8">Unassigned</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div>
                                            @if($e->work_mode === 'WFH')
                                                <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25" style="font-size: 0.7rem;">WFH</span>
                                            @elseif($e->work_mode === 'HYBRID')
                                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25" style="font-size: 0.7rem;">HYBRID</span>
                                            @else
                                                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25" style="font-size: 0.7rem;">NORMAL</span>
                                            @endif
                                        </div>
                                        <div class="mt-1">
                                            @if($e->allow_remote_punch)
                                                <small class="text-success" style="font-size:0.65rem;"><i class="bi bi-geo-alt-fill"></i> Remote Punch</small>
                                            @else
                                                <small class="text-secondary" style="font-size:0.65rem;"><i class="bi bi-fingerprint"></i> Office Only</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div class="mb-1">
                                            @if($e->employment_status === 'Onboarding')
                                                <span class="badge-status badge-pending">Onboarding</span>
                                            @else
                                                <span class="badge-status {{ $e->status === 'Active' ? 'badge-online' : 'badge-disabled' }}">
                                                    {{ $e->status }}
                                                </span>
                                            @endif
                                        </div>
                                        <div>
                                            @if($e->biometric_status === 'User Synchronized')
                                                <span class="badge bg-success-subtle text-success fs-8 border border-success-subtle"><i class="bi bi-check-circle-fill me-1"></i>Synced</span>
                                            @else
                                                <span class="badge bg-warning-subtle text-warning fs-8 border border-warning-subtle"><i class="bi bi-arrow-repeat me-1"></i>Pending</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-custom-secondary dropdown-toggle px-2 py-1" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="bi bi-three-dots-vertical"></i> Actions
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end shadow border-0" style="background-color: var(--card-bg); backdrop-filter: blur(16px);">
                                                <li>
                                                    <button type="button" class="dropdown-item fs-7 py-2" data-bs-toggle="modal" data-bs-target="#profileModal{{ $e->id }}">
                                                        <i class="bi bi-person-badge-fill me-2 text-indigo"></i> View Profile & ADMS
                                                    </button>
                                                </li>
                                                <li>
                                                    <button type="button" class="dropdown-item fs-7 py-2" onclick="openSyncTelemetry({{ $e->id }})">
                                                        <i class="bi bi-diagram-3-fill me-2 text-info"></i> Device Sync Status
                                                    </button>
                                                </li>
                                                <li>
                                                    <button type="button" class="dropdown-item fs-7 py-2 text-warning" onclick="document.getElementById('syncSingleForm{{ $e->id }}').submit();">
                                                        <i class="bi bi-lightning-charge-fill me-2"></i> Force ADMS Sync
                                                    </button>
                                                </li>
                                                <li>
                                                    <button type="button" class="dropdown-item fs-7 py-2 text-indigo" data-bs-toggle="modal" data-bs-target="#enrollModal{{ $e->id }}">
                                                        <i class="bi bi-broadcast me-2"></i> Remote Biometric Enrollment
                                                    </button>
                                                </li>
                                                <li><hr class="dropdown-divider border-secondary opacity-25"></li>
                                                @if($e->employment_status === 'Onboarding')
                                                    <li>
                                                        <a href="{{ route('onboarding.profile', $e->id) }}" class="dropdown-item fs-7 py-2 text-info">
                                                            <i class="bi bi-eye-fill me-2"></i> Onboarding Profile
                                                        </a>
                                                    </li>
                                                @else
                                                    <li>
                                                        <button type="button" class="dropdown-item fs-7 py-2" data-bs-toggle="modal" data-bs-target="#editModal{{ $e->id }}">
                                                            <i class="bi bi-pencil-square me-2 text-primary"></i> Edit Employee
                                                        </button>
                                                    </li>
                                                @endif
                                                <li>
                                                    <button type="button" class="dropdown-item fs-7 py-2 text-danger" onclick="if(confirm('Are you sure you want to delete {{ $e->full_name }}? This will queue a delete command across all biometrics terminals.')) document.getElementById('deleteSingleForm{{ $e->id }}').submit();">
                                                        <i class="bi bi-trash-fill me-2"></i> Delete Employee
                                                    </button>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                <!-- External Forms for Single Sync & Delete outside bulk form -->
                                <form id="syncSingleForm{{ $e->id }}" action="{{ route('employees.sync', $e->id) }}" method="POST" class="d-none">@csrf</form>
                                <form id="deleteSingleForm{{ $e->id }}" action="{{ route('employees.delete', $e->id) }}" method="POST" class="d-none">@csrf</form>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-secondary">
                                        <i class="bi bi-people fs-1 d-block mb-2 text-muted"></i>
                                        No employees found matching selected criteria.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Employee Detail Profile Modals & Edit Modals -->
@foreach($employees as $e)
    <!-- Enterprise View Profile & ADMS Detail Modal -->
    <div class="modal fade" id="profileModal{{ $e->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content glass-card p-0">
                <div class="modal-header border-bottom border-secondary opacity-25 p-3">
                    <div class="d-flex align-items-center gap-3">
                        @if($e->profile_photo)
                            <img src="{{ asset($e->profile_photo) }}" alt="avatar" class="rounded-circle" style="width: 48px; height: 48px; object-fit: cover;">
                        @else
                            <div class="rounded-circle text-center bg-indigo text-light d-flex align-items-center justify-content-center fw-bold fs-5" style="width: 48px; height: 48px;">
                                {{ strtoupper(substr($e->first_name, 0, 1) . substr($e->last_name, 0, 1)) }}
                            </div>
                        @endif
                        <div>
                            <h5 class="modal-title display-font m-0 text-dark">{{ $e->full_name }}</h5>
                            <span class="badge bg-indigo font-monospace mt-1">{{ $e->device_user_id ?? $e->employee_id }}</span>
                            <span class="badge bg-secondary font-monospace">PIN: {{ $e->sync_pin ?? 'N/A' }}</span>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <ul class="nav nav-tabs border-bottom-0 mb-4 gap-2" id="profileTabs{{ $e->id }}" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active btn-sm btn-custom-secondary px-3 py-2" id="general-tab-{{ $e->id }}" data-bs-toggle="tab" data-bs-target="#general-pane-{{ $e->id }}" type="button" role="tab">
                                <i class="bi bi-person-badge me-1"></i> General & Org
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link btn-sm btn-custom-secondary px-3 py-2" id="bio-tab-{{ $e->id }}" data-bs-toggle="tab" data-bs-target="#bio-pane-{{ $e->id }}" type="button" role="tab">
                                <i class="bi bi-fingerprint me-1"></i> Biometrics & Finger Map
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link btn-sm btn-custom-secondary px-3 py-2" id="adms-tab-{{ $e->id }}" data-bs-toggle="tab" data-bs-target="#adms-pane-{{ $e->id }}" type="button" role="tab">
                                <i class="bi bi-cpu me-1"></i> ADMS Sync & Hardware
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link btn-sm btn-custom-secondary px-3 py-2" id="devices-tab-{{ $e->id }}" data-bs-toggle="tab" data-bs-target="#devices-pane-{{ $e->id }}" type="button" role="tab">
                                <i class="bi bi-diagram-3 me-1"></i> Assigned Terminals ({{ $e->devices->count() }})
                            </button>
                        </li>
                    </ul>
                    <div class="tab-content" id="profileTabsContent{{ $e->id }}">
                        <!-- Tab 1: General & Org -->
                        <div class="tab-pane fade show active" id="general-pane-{{ $e->id }}" role="tabpanel">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="p-3 rounded bg-light border">
                                        <span class="text-secondary fs-8 d-block">Company</span>
                                        <span class="fw-semibold text-dark">{{ $e->company->company_name }} ({{ $e->company->company_code }})</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 rounded bg-light border">
                                        <span class="text-secondary fs-8 d-block">Branch</span>
                                        <span class="fw-semibold text-dark">{{ $e->branch ? $e->branch->branch_name : 'Main Headquarters' }}</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 rounded bg-light border">
                                        <span class="text-secondary fs-8 d-block">Department</span>
                                        <span class="fw-semibold text-dark">{{ $e->department->department_name }}</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 rounded bg-light border">
                                        <span class="text-secondary fs-8 d-block">Designation</span>
                                        <span class="fw-semibold text-dark">{{ $e->designation ?? 'Staff' }}</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="p-3 rounded bg-light border">
                                        <span class="text-secondary fs-8 d-block">NIC Number</span>
                                        <span class="fw-semibold text-dark">{{ $e->nic ?? 'N/A' }}</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="p-3 rounded bg-light border">
                                        <span class="text-secondary fs-8 d-block">Mobile Contact</span>
                                        <span class="fw-semibold text-dark">{{ $e->mobile_number ?? 'N/A' }}</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="p-3 rounded bg-light border">
                                        <span class="text-secondary fs-8 d-block">Employment Status</span>
                                        <span class="badge bg-indigo">{{ $e->status }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tab 2: Biometric & 10-Finger Map -->
                        <div class="tab-pane fade" id="bio-pane-{{ $e->id }}" role="tabpanel">
                            <div class="alert alert-info border-info text-info fs-8 mb-4">
                                <i class="bi bi-shield-lock-fill me-1"></i> Biometric templates are stored as AES-256 encrypted vector hashes. Raw biometric images are never stored.
                            </div>
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <div class="p-3 rounded bg-light border d-flex align-items-center justify-content-between">
                                        <div>
                                            <span class="text-secondary fs-8 d-block">Face Recognition Template</span>
                                            <span class="fw-bold fs-7 {{ $e->face_enrolled ? 'text-success' : 'text-warning' }}">
                                                <i class="bi {{ $e->face_enrolled ? 'bi-person-check-fill' : 'bi-person-x-fill' }} me-1"></i>
                                                {{ $e->face_enrolled ? 'Registered on Device' : 'Not Registered (Enrollment Required)' }}
                                            </span>
                                        </div>
                                        <span class="badge bg-indigo">v12.0 SenseFace</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 rounded bg-light border d-flex align-items-center justify-content-between">
                                        <div>
                                            <span class="text-secondary fs-8 d-block">Fingerprint Enrollment Status</span>
                                            <span class="fw-bold fs-7 {{ $e->fingerprint_enrolled ? 'text-success' : 'text-warning' }}">
                                                <i class="bi {{ $e->fingerprint_enrolled ? 'bi-fingerprint' : 'bi-x-circle' }} me-1"></i>
                                                {{ $e->fingerprint_enrolled ? 'Registered' : 'No Fingerprints Registered' }}
                                            </span>
                                        </div>
                                        <span class="badge bg-secondary">ZKFinger VX10.0</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Remote Enrollment Actions -->
                            <div class="p-3 rounded bg-light border border-indigo mb-4">
                                <span class="fw-bold text-dark fs-8 d-block mb-2"><i class="bi bi-broadcast me-1 text-indigo"></i> Trigger Remote Enrollment on Terminal</span>
                                <div class="d-flex flex-wrap gap-2">
                                    <form action="{{ route('employees.enroll', $e->id) }}" method="POST" class="m-0">
                                        @csrf
                                        <input type="hidden" name="enroll_type" value="face">
                                        <button type="submit" class="btn btn-sm btn-custom-primary">
                                            <i class="bi bi-person-bounding-box me-1"></i> Trigger Face Enrollment
                                        </button>
                                    </form>
                                    <form action="{{ route('employees.enroll', $e->id) }}" method="POST" class="m-0">
                                        @csrf
                                        <input type="hidden" name="enroll_type" value="fingerprint">
                                        <input type="hidden" name="finger_id" value="0">
                                        <button type="submit" class="btn btn-sm btn-custom-secondary text-info">
                                            <i class="bi bi-fingerprint me-1"></i> Trigger Right Thumb (FID 0)
                                        </button>
                                    </form>
                                    <form action="{{ route('employees.enroll', $e->id) }}" method="POST" class="m-0">
                                        @csrf
                                        <input type="hidden" name="enroll_type" value="fingerprint">
                                        <input type="hidden" name="finger_id" value="1">
                                        <button type="submit" class="btn btn-sm btn-custom-secondary text-info">
                                            <i class="bi bi-fingerprint me-1"></i> Trigger Right Index (FID 1)
                                        </button>
                                    </form>
                                    <form action="{{ route('employees.enroll', $e->id) }}" method="POST" class="m-0">
                                        @csrf
                                        <input type="hidden" name="enroll_type" value="query">
                                        <button type="submit" class="btn btn-sm btn-outline-warning">
                                            <i class="bi bi-download me-1"></i> Pull Templates from Device
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <!-- 10-Finger Map Matrix -->
                            <h6 class="text-dark fs-8 uppercase-track mb-3"><i class="bi bi-hand-index-thumb me-1 text-indigo"></i> 10-Finger Registration Map</h6>
                            @php
                                $registeredFingers = $e->biometricTemplates->where('biometric_type', 'fingerprint')->pluck('finger_position')->toArray();
                                $leftFingers = ['left_thumb' => 'Thumb', 'left_index' => 'Index', 'left_middle' => 'Middle', 'left_ring' => 'Ring', 'left_little' => 'Little'];
                                $rightFingers = ['right_thumb' => 'Thumb', 'right_index' => 'Index', 'right_middle' => 'Middle', 'right_ring' => 'Ring', 'right_little' => 'Little'];
                            @endphp
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="p-3 rounded bg-light border">
                                        <span class="fw-semibold text-indigo fs-8 d-block mb-2"><i class="bi bi-hand-index me-1"></i> Left Hand</span>
                                        <div class="d-flex flex-wrap gap-2">
                                            @foreach($leftFingers as $posKey => $posName)
                                                @php $isReg = in_array($posKey, $registeredFingers); @endphp
                                                <span class="badge {{ $isReg ? 'bg-success text-white' : 'bg-secondary text-white border' }} px-2 py-1 fs-8">
                                                    <i class="bi {{ $isReg ? 'bi-check-circle-fill text-white' : 'bi-x-circle' }} me-1"></i> {{ $posName }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 rounded bg-light border">
                                        <span class="fw-semibold text-indigo fs-8 d-block mb-2"><i class="bi bi-hand-index me-1"></i> Right Hand</span>
                                        <div class="d-flex flex-wrap gap-2">
                                            @foreach($rightFingers as $posKey => $posName)
                                                @php $isReg = in_array($posKey, $registeredFingers); @endphp
                                                <span class="badge {{ $isReg ? 'bg-success text-white' : 'bg-secondary text-white border' }} px-2 py-1 fs-8">
                                                    <i class="bi {{ $isReg ? 'bi-check-circle-fill text-white' : 'bi-x-circle' }} me-1"></i> {{ $posName }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tab 2: ADMS & Hardware -->
                        <div class="tab-pane fade" id="adms-pane-{{ $e->id }}" role="tabpanel">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="p-3 rounded bg-light border">
                                        <span class="text-secondary fs-8 d-block">ADMS Sync Status</span>
                                        <span class="fw-bold text-success"><i class="bi bi-cloud-check me-1"></i>{{ $e->biometric_status }}</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 rounded bg-light border">
                                        <span class="text-secondary fs-8 d-block">Hardware PIN (Numeric)</span>
                                        <span class="fw-bold text-dark font-monospace">{{ $e->sync_pin }}</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 rounded bg-light border">
                                        <span class="text-secondary fs-8 d-block">RFID Card Number</span>
                                        <span class="fw-semibold text-dark font-monospace">{{ $e->card_number ?? 'Not Enrolled' }}</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 rounded bg-light border">
                                        <span class="text-secondary fs-8 d-block">Terminal Privilege</span>
                                        <span class="fw-semibold text-dark">{{ $e->privilege == 14 ? 'Super Admin (14)' : 'Normal User (0)' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tab 3: Assigned Terminals -->
                        <div class="tab-pane fade" id="devices-pane-{{ $e->id }}" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table custom-table table-sm m-0">
                                    <thead>
                                        <tr>
                                            <th>Terminal Name</th>
                                            <th>Serial Number</th>
                                            <th>Company / Branch</th>
                                            <th>Last Seen</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($e->devices as $dev)
                                            <tr>
                                                <td class="fw-semibold text-dark">{{ $dev->device_name }}</td>
                                                <td class="font-monospace text-indigo">{{ $dev->device_serial_number }}</td>
                                                <td class="fs-8 text-secondary">{{ $dev->company->company_name ?? 'Global' }}</td>
                                                <td class="fs-8 text-dark">{{ $dev->last_seen ? \Carbon\Carbon::parse($dev->last_seen)->diffForHumans() : 'Never' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center py-3 text-secondary fs-8">No explicit biometric terminals linked. Auto-discovers active company terminals.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0 p-3">
                    <button type="button" class="btn btn-custom-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-custom-primary" onclick="openSyncTelemetry({{ $e->id }})">
                        <i class="bi bi-diagram-3 me-1"></i> Detailed Telemetry
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Dedicated Remote Enrollment Modal -->
    <div class="modal fade" id="enrollModal{{ $e->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content glass-card p-0">
                <div class="modal-header border-bottom-0 pb-0 p-4">
                    <h5 class="modal-title display-font"><i class="bi bi-broadcast me-2 text-indigo"></i> Remote Biometric Enrollment - {{ $e->full_name }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 text-start">
                    <p class="text-secondary fs-8 mb-4">Select a biometric enrollment action to dispatch to terminal <span class="badge bg-indigo font-monospace">{{ $e->sync_pin }}</span>. The device will receive this command during its next heartbeat poll.</p>

                    <!-- Face Enrollment Form -->
                    <div class="p-3 rounded bg-dark-subtle border border-secondary opacity-75 mb-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="fw-bold text-dark fs-7 d-block"><i class="bi bi-person-bounding-box me-1 text-indigo"></i> Face Recognition Enrollment</span>
                                <span class="text-secondary fs-8">Triggers terminal camera prompt (SenseFace M2F-LR)</span>
                            </div>
                            <form action="{{ route('employees.enroll', $e->id) }}" method="POST" class="m-0">
                                @csrf
                                <input type="hidden" name="enroll_type" value="face">
                                <button type="submit" class="btn btn-sm btn-custom-primary">
                                    <i class="bi bi-play-fill me-1"></i> Enroll Face
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Fingerprint Enrollment Form -->
                    <div class="p-3 rounded bg-dark-subtle border border-secondary opacity-75 mb-3">
                        <span class="fw-bold text-light fs-7 d-block mb-2"><i class="bi bi-fingerprint me-1 text-info"></i> Fingerprint Remote Enrollment</span>
                        <form action="{{ route('employees.enroll', $e->id) }}" method="POST">
                            @csrf
                            <input type="hidden" name="enroll_type" value="fingerprint">
                            <div class="row g-2 mb-3">
                                <div class="col-8">
                                    <select class="form-select form-select-custom form-select-sm" name="finger_id">
                                        <option value="0">Right Thumb (FID 0)</option>
                                        <option value="1" selected>Right Index (FID 1)</option>
                                        <option value="2">Right Middle (FID 2)</option>
                                        <option value="3">Right Ring (FID 3)</option>
                                        <option value="4">Right Little (FID 4)</option>
                                        <option value="5">Left Thumb (FID 5)</option>
                                        <option value="6">Left Index (FID 6)</option>
                                        <option value="7">Left Middle (FID 7)</option>
                                        <option value="8">Left Ring (FID 8)</option>
                                        <option value="9">Left Little (FID 9)</option>
                                    </select>
                                </div>
                                <div class="col-4">
                                    <button type="submit" class="btn btn-sm btn-custom-secondary text-info w-100">
                                        <i class="bi bi-play-fill me-1"></i> Enroll Finger
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Template Retrieval Query -->
                    <div class="p-3 rounded bg-dark-subtle border border-secondary opacity-75">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="fw-bold text-dark fs-7 d-block"><i class="bi bi-download me-1 text-warning"></i> Pull Templates From Device</span>
                                <span class="text-secondary fs-8">Fetches existing enrolled biometrics from terminal</span>
                            </div>
                            <form action="{{ route('employees.enroll', $e->id) }}" method="POST" class="m-0">
                                @csrf
                                <input type="hidden" name="enroll_type" value="query">
                                <button type="submit" class="btn btn-sm btn-outline-warning">
                                    <i class="bi bi-arrow-down-circle me-1"></i> Pull Templates
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Employee Modal -->
    <div class="modal fade" id="editModal{{ $e->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content glass-card p-0" >
                <div class="modal-header border-bottom-0 pb-0 p-4">
                    <h5 class="modal-title display-font"><i class="bi bi-pencil-square me-2 text-indigo"></i> Edit Employee - {{ $e->full_name }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('employees.update', $e->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body text-start p-4">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label text-secondary fw-medium">Employee ID / Biometric User ID</label>
                                <input type="text" class="form-control form-control-custom font-monospace" name="employee_id" value="{{ $e->employee_id }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-secondary fw-medium">Company</label>
                                <select class="form-select form-select-custom" name="company_id" required>
                                    @foreach($companies as $c)
                                        <option value="{{ $c->id }}" {{ $e->company_id == $c->id ? 'selected' : '' }}>{{ $c->company_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label text-secondary fw-medium">Branch</label>
                                <select class="form-select form-select-custom" name="branch_id">
                                    <option value="">Select Branch</option>
                                    @foreach($branches as $b)
                                        <option value="{{ $b->id }}" {{ $e->branch_id == $b->id ? 'selected' : '' }}>{{ $b->branch_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-secondary fw-medium">Department</label>
                                <select class="form-select form-select-custom" name="department_id" required>
                                    @foreach($departments as $d)
                                        <option value="{{ $d->id }}" {{ $e->department_id == $d->id ? 'selected' : '' }}>{{ $d->department_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col">
                                <label class="form-label text-secondary fw-medium">First Name</label>
                                <input type="text" class="form-control form-control-custom" name="first_name" value="{{ $e->first_name }}" required>
                            </div>
                            <div class="col">
                                <label class="form-label text-secondary fw-medium">Last Name</label>
                                <input type="text" class="form-control form-control-custom" name="last_name" value="{{ $e->last_name }}" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col">
                                <label class="form-label text-secondary fw-medium">Gender</label>
                                <select class="form-select form-select-custom" name="gender">
                                    <option value="">Select</option>
                                    <option value="Male" {{ $e->gender === 'Male' ? 'selected' : '' }}>Male</option>
                                    <option value="Female" {{ $e->gender === 'Female' ? 'selected' : '' }}>Female</option>
                                    <option value="Other" {{ $e->gender === 'Other' ? 'selected' : '' }}>Other</option>
                                </select>
                            </div>
                            <div class="col">
                                <label class="form-label text-secondary fw-medium">Date of Birth</label>
                                <input type="date" class="form-control form-control-custom" name="date_of_birth" value="{{ $e->date_of_birth ? \Carbon\Carbon::parse($e->date_of_birth)->format('Y-m-d') : '' }}">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col">
                                <label class="form-label text-secondary fw-medium">NIC Number</label>
                                <input type="text" class="form-control form-control-custom" name="nic" value="{{ $e->nic }}">
                            </div>
                            <div class="col">
                                <label class="form-label text-secondary fw-medium">Mobile Number</label>
                                <input type="text" class="form-control form-control-custom" name="mobile_number" value="{{ $e->mobile_number }}">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-secondary fw-medium">Email Address</label>
                            <input type="email" class="form-control form-control-custom" name="email" value="{{ $e->email }}">
                        </div>

                        <div class="row mb-3">
                            <div class="col">
                                <label class="form-label text-secondary fw-medium">Designation</label>
                                <select name="designation" class="form-select form-control-custom">
                                    <option value="">Select Designation</option>
                                    @foreach($designations as $designation)
                                        <option value="{{ $designation->title }}" {{ $e->designation == $designation->title ? 'selected' : '' }}>{{ $designation->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col">
                                <label class="form-label text-secondary fw-medium">Status</label>
                                <select class="form-select form-select-custom" name="status" required>
                                    <option value="Active" {{ $e->status === 'Active' ? 'selected' : '' }}>Active</option>
                                    <option value="Inactive" {{ $e->status === 'Inactive' ? 'selected' : '' }}>Inactive</option>
                                    <option value="Resigned" {{ $e->status === 'Resigned' ? 'selected' : '' }}>Resigned</option>
                                    <option value="Terminated" {{ $e->status === 'Terminated' ? 'selected' : '' }}>Terminated</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label text-secondary fw-medium">Primary Shift</label>
                                <select class="form-select form-select-custom" name="shift_id">
                                    <option value="">Assign Shift</option>
                                    @foreach($shifts as $s)
                                        <option value="{{ $s->id }}" {{ $e->shift_id == $s->id ? 'selected' : '' }}>{{ $s->shift_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-secondary fw-medium">Secondary Shift</label>
                                <select class="form-select form-select-custom" name="secondary_shift_id">
                                    <option value="">None</option>
                                    @foreach($shifts as $s)
                                        <option value="{{ $s->id }}" {{ $e->secondary_shift_id == $s->id ? 'selected' : '' }}>{{ $s->shift_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <hr class="border-secondary my-4">
                        <h6 class="text-indigo mb-3"><i class="bi bi-house-laptop me-2"></i>Work From Home & Web Punch Settings</h6>
                        
                        <div class="row mb-3">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-secondary fw-medium">Default Work Mode</label>
                                <select class="form-select form-select-custom" name="work_mode" required>
                                    <option value="NORMAL" {{ $e->work_mode === 'NORMAL' ? 'selected' : '' }}>NORMAL (Office Based)</option>
                                    <option value="WFH" {{ $e->work_mode === 'WFH' ? 'selected' : '' }}>WFH (Permanent Remote)</option>
                                    <option value="HYBRID" {{ $e->work_mode === 'HYBRID' ? 'selected' : '' }}>HYBRID (Flexible / Both)</option>
                                </select>
                                <small class="text-muted d-block mt-1">WFH mode permanently enables web punch.</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-secondary fw-medium">Web Punch Access</label>
                                <div class="form-check form-switch mt-1">
                                    <input class="form-check-input" type="checkbox" name="allow_remote_punch" value="1" id="allowRemotePunch_{{ $e->id }}" {{ $e->allow_remote_punch ? 'checked' : '' }}>
                                    <label class="form-check-label text-secondary" for="allowRemotePunch_{{ $e->id }}">Allow Remote Web Punch</label>
                                </div>
                                <small class="text-muted d-block mt-1">Required for WFH or temporary web punch.</small>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label text-secondary fw-medium">Home Latitude</label>
                                <input type="number" step="0.00000001" class="form-control form-control-custom font-monospace" name="home_latitude" value="{{ $e->home_latitude }}" placeholder="e.g. 6.9271">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-secondary fw-medium">Home Longitude</label>
                                <input type="number" step="0.00000001" class="form-control form-control-custom font-monospace" name="home_longitude" value="{{ $e->home_longitude }}" placeholder="e.g. 79.8612">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-secondary fw-medium">Allowed Radius (m)</label>
                                <input type="number" class="form-control form-control-custom" name="allowed_radius" value="{{ $e->allowed_radius ?? 150 }}">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label text-secondary fw-medium">Card Number</label>
                                <input type="text" class="form-control form-control-custom" name="card_number" value="{{ $e->card_number }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-secondary fw-medium">Terminal Password</label>
                                <input type="password" class="form-control form-control-custom" name="terminal_password" placeholder="Terminal PIN/Pass">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-secondary fw-medium">Device Privilege</label>
                                <select class="form-select form-select-custom" name="privilege">
                                    <option value="0" {{ $e->privilege == 0 ? 'selected' : '' }}>Normal User (0)</option>
                                    <option value="14" {{ $e->privilege == 14 ? 'selected' : '' }}>Super Admin (14)</option>
                                </select>
                            </div>
                        </div>

                        <hr class="border-secondary my-4">
                        <h6 class="text-indigo mb-3"><i class="bi bi-shield-lock me-2"></i>Web Portal Account Settings</h6>
                        <div class="glass-card p-3 bg-light bg-opacity-50 mb-3">
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" name="create_account" value="1" id="createAccountToggle_{{ $e->id }}" {{ $e->user_id ? 'checked' : '' }}>
                                <label class="form-check-label text-dark fw-bold" for="createAccountToggle_{{ $e->id }}">Enable Web Portal Access</label>
                                <div class="form-text fs-8 text-secondary mt-1">If enabled, the employee can log in to the web portal using their email (or Employee ID).</div>
                            </div>
                            
                            <div id="accountFields_{{ $e->id }}" style="display: {{ $e->user_id ? 'block' : 'none' }};">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label text-secondary fs-8 fw-semibold">Business Role</label>
                                        <select class="form-select form-select-custom" name="role">
                                            <option value="Employee">Standard Employee</option>
                                            @foreach($roles as $r)
                                                @if($r->name !== 'Employee')
                                                    <option value="{{ $r->name }}" {{ ($e->user && $e->user->role === $r->name) ? 'selected' : '' }}>{{ $r->name }} ({{ $r->category }})</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label text-secondary fs-8 fw-semibold">New Password</label>
                                        <input type="password" class="form-control form-control-custom" name="password" placeholder="Leave blank to keep current">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                const toggle_{{ $e->id }} = document.getElementById('createAccountToggle_{{ $e->id }}');
                                const fields_{{ $e->id }} = document.getElementById('accountFields_{{ $e->id }}');
                                if(toggle_{{ $e->id }} && fields_{{ $e->id }}) {
                                    toggle_{{ $e->id }}.addEventListener('change', function() {
                                        fields_{{ $e->id }}.style.display = this.checked ? 'block' : 'none';
                                    });
                                }
                            });
                        </script>

                        <div class="mb-3">
                            <label class="form-label text-secondary fw-medium">Assign Biometric Devices</label>
                            @php $assignedIds = $e->devices->pluck('id')->toArray(); @endphp
                            <select class="form-select form-select-custom" name="device_ids[]" multiple style="height: 110px;">
                                @foreach($devices as $dev)
                                    <option value="{{ $dev->id }}" {{ in_array($dev->id, $assignedIds) ? 'selected' : '' }}>
                                        {{ $dev->device_name }} ({{ $dev->device_serial_number }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-secondary fw-medium">Profile Photo (Optional)</label>
                            <input type="file" class="form-control form-control-custom" name="profile_photo" accept=".jpg,.jpeg">
                            <small class="form-text text-muted">
                                <span class="badge bg-warning text-dark"><i class="fas fa-exclamation-triangle"></i> Note</span> Only <strong>JPG / JPEG</strong> images are supported by fingerprint devices.
                            </small>
                            <small class="text-muted d-block mt-1">Image will be automatically optimized for Face Recognition.</small>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 pt-0 p-4">
                        <button type="button" class="btn btn-custom-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-custom-primary">Save Changes & Dispatch ADMS Sync</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach

<!-- Bulk Edit Modal -->
<div class="modal fade" id="bulkEditModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content glass-card p-0" >
            <div class="modal-header border-bottom-0 pb-0 p-4">
                <h5 class="modal-title display-font"><i class="bi bi-pencil-square me-2 text-indigo"></i> Bulk Update Employees</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-start p-4">
                <p class="text-secondary fs-8 mb-4">Select the checkbox next to each field you want to bulk update. Ticked fields will overwrite values for all selected employees, while unchecked fields will remain unchanged.</p>

                <!-- Company -->
                <div class="mb-4">
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="update_company" value="1" id="bulkUpdateCompanyToggle" form="bulkUpdateForm">
                        <label class="form-check-label fw-semibold text-dark mb-1" for="bulkUpdateCompanyToggle">Update Company</label>
                    </div>
                    <select class="form-select form-select-custom" name="company_id" id="bulkCompanySelect" disabled form="bulkUpdateForm">
                        <option value="">Select Company</option>
                        @foreach($companies as $c)
                            <option value="{{ $c->id }}">{{ $c->company_name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Branch -->
                <div class="mb-4">
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="update_branch" value="1" id="bulkUpdateBranchToggle" form="bulkUpdateForm">
                        <label class="form-check-label fw-semibold text-dark mb-1" for="bulkUpdateBranchToggle">Update Branch</label>
                    </div>
                    <select class="form-select form-select-custom" name="branch_id" id="bulkBranchSelect" disabled form="bulkUpdateForm">
                        <option value="">Select Branch</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}">{{ $b->company->company_name ?? '' }} - {{ $b->branch_name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Department -->
                <div class="mb-4">
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="update_department" value="1" id="bulkUpdateDeptToggle" form="bulkUpdateForm">
                        <label class="form-check-label fw-semibold text-dark mb-1" for="bulkUpdateDeptToggle">Update Department</label>
                    </div>
                    <select class="form-select form-select-custom" name="department_id" id="bulkDeptSelect" disabled form="bulkUpdateForm">
                        <option value="">Select Department</option>
                        @foreach($departments as $d)
                            <option value="{{ $d->id }}">{{ $d->company->company_name ?? '' }} - {{ $d->department_name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Designation -->
                <div class="mb-4">
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="update_designation" value="1" id="bulkUpdateDesigToggle" form="bulkUpdateForm">
                        <label class="form-check-label fw-semibold text-dark mb-1" for="bulkUpdateDesigToggle">Update Designation</label>
                    </div>
                    <select class="form-select form-control-custom" name="designation" id="bulkDesigInput" disabled form="bulkUpdateForm">
                        <option value="">Select Designation...</option>
                        @foreach($designations as $designation)
                            <option value="{{ $designation->title }}">{{ $designation->title }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Shift -->
                <div class="mb-4">
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="update_shift" value="1" id="bulkUpdateShiftToggle" form="bulkUpdateForm">
                        <label class="form-check-label fw-semibold text-dark mb-1" for="bulkUpdateShiftToggle">Update Primary Shift</label>
                    </div>
                    <select class="form-select form-select-custom" name="shift_id" id="bulkShiftSelect" disabled form="bulkUpdateForm">
                        <option value="">Select Shift</option>
                        @foreach($shifts as $s)
                            <option value="{{ $s->id }}">{{ $s->shift_name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Secondary Shift -->
                <div class="mb-4">
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="update_secondary_shift" value="1" id="bulkUpdateSecondaryShiftToggle" form="bulkUpdateForm">
                        <label class="form-check-label fw-semibold text-dark mb-1" for="bulkUpdateSecondaryShiftToggle">Update Secondary Shift</label>
                    </div>
                    <select class="form-select form-select-custom" name="secondary_shift_id" id="bulkSecondaryShiftSelect" disabled form="bulkUpdateForm">
                        <option value="">Select Shift</option>
                        @foreach($shifts as $s)
                            <option value="{{ $s->id }}">{{ $s->shift_name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Force Sync -->
                <div class="mb-2 p-3 rounded" style="background-color: rgba(255, 193, 7, 0.1); border: 1px solid rgba(255, 193, 7, 0.3);">
                    <div class="form-check mb-1">
                        <input class="form-check-input" type="checkbox" name="force_sync" value="1" id="bulkUpdateForceSyncToggle" form="bulkUpdateForm">
                        <label class="form-check-label fw-semibold text-warning" for="bulkUpdateForceSyncToggle">
                            <i class="bi bi-lightning-charge-fill me-1"></i> Force ADMS Sync
                        </label>
                    </div>
                    <div class="text-secondary fs-8 ms-4">Immediately queues CREATE_USER commands for the selected employees on their assigned terminals after updating.</div>
                </div>
            </div>
            <div class="modal-footer border-top-0 pt-0 p-4">
                <button type="button" class="btn btn-custom-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-custom-primary" form="bulkUpdateForm">Apply Bulk Update</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('selectAllEmployees');
    const employeeCheckboxes = document.querySelectorAll('.employee-checkbox');
    const bulkActionBar = document.getElementById('bulkActionBar');
    const selectedCountSpan = document.getElementById('selectedCount');

    // Toggles logic
    if (document.getElementById('bulkUpdateCompanyToggle')) {
        document.getElementById('bulkUpdateCompanyToggle').addEventListener('change', function() {
            document.getElementById('bulkCompanySelect').disabled = !this.checked;
        });
        document.getElementById('bulkUpdateBranchToggle').addEventListener('change', function() {
            document.getElementById('bulkBranchSelect').disabled = !this.checked;
        });
        document.getElementById('bulkUpdateDeptToggle').addEventListener('change', function() {
            document.getElementById('bulkDeptSelect').disabled = !this.checked;
        });
        document.getElementById('bulkUpdateDesigToggle').addEventListener('change', function() {
            document.getElementById('bulkDesigInput').disabled = !this.checked;
        });
        document.getElementById('bulkUpdateShiftToggle').addEventListener('change', function() {
            document.getElementById('bulkShiftSelect').disabled = !this.checked;
        });
        document.getElementById('bulkUpdateSecondaryShiftToggle').addEventListener('change', function() {
            document.getElementById('bulkSecondaryShiftSelect').disabled = !this.checked;
        });
    }

    // ActionBar view updates logic
    function updateBulkActionBar() {
        const checkedCount = document.querySelectorAll('.employee-checkbox:checked').length;
        if (selectedCountSpan) selectedCountSpan.textContent = checkedCount;

        if (bulkActionBar) {
            if (checkedCount > 0) {
                bulkActionBar.classList.remove('d-none');
            } else {
                bulkActionBar.classList.add('d-none');
            }
        }
    }

    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            employeeCheckboxes.forEach(cb => {
                cb.checked = this.checked;
            });
            updateBulkActionBar();
        });
    }

    employeeCheckboxes.forEach(cb => {
        cb.addEventListener('change', function() {
            if (!this.checked && selectAllCheckbox) {
                selectAllCheckbox.checked = false;
            }
            updateBulkActionBar();
        });
    });

// Company Prefix & Device User ID Auto-Generation
    const companySelect = document.getElementById('createCompanySelect');
    const employeeIdInput = document.getElementById('createEmployeeIdInput');
    const prefixAddon = document.getElementById('empIdPrefixAddon');
    
    if (companySelect && employeeIdInput) {
        companySelect.addEventListener('change', function() {
            const companyId = this.value;
            const selectedOption = this.options[this.selectedIndex];
            const code = selectedOption ? selectedOption.getAttribute('data-code') : null;
            
            if (code) {
                prefixAddon.textContent = code + '-';
            } else {
                prefixAddon.textContent = 'P1-';
            }

            if (!companyId) {
                employeeIdInput.value = '';
                return;
            }
            
            // Fetch next ID from endpoint
            fetch(`/api/companies/${companyId}/next-employee-id`)
                .then(response => response.json())
                .then(data => {
                    if (data.next_id) {
                        employeeIdInput.value = data.next_id;
                    }
                })
                .catch(error => {
                    console.error('Error fetching next employee ID:', error);
                });
        });
    }
});

function openSyncTelemetry(employeeId) {
    const modal = new bootstrap.Modal(document.getElementById('syncTelemetryModal'));
    const body = document.getElementById('syncTelemetryModalBody');
    body.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-indigo" role="status"></div><div class="mt-2 text-secondary fs-8">Fetching per-device telemetry...</div></div>';
    modal.show();

    fetch(`/employees/${employeeId}/sync-status`)
        .then(res => res.json())
        .then(data => {
            document.getElementById('syncTelemetryModalLabel').textContent = `Device Sync Telemetry - ${data.employee_name} (${data.device_user_id})`;
            
            if (!data.devices || data.devices.length === 0) {
                body.innerHTML = '<div class="alert alert-warning m-0 fs-7">No biometric devices currently assigned to this employee.</div>';
                return;
            }

            let rows = '';
            data.devices.forEach(dev => {
                let badgeClass = 'bg-secondary';
                if (dev.status === 'Completed') badgeClass = 'bg-success';
                else if (dev.status === 'Pending' || dev.status === 'Sent') badgeClass = 'bg-warning text-dark';
                else if (dev.status === 'Failed') badgeClass = 'bg-danger';

                rows += `
                    <tr>
                        <td class="fw-semibold text-dark">${dev.device_name}<div class="fs-8 text-secondary">${dev.serial_number}</div></td>
                        <td><span class="badge bg-indigo">${dev.command_type}</span></td>
                        <td><span class="badge ${badgeClass}">${dev.status}</span></td>
                        <td class="fs-8 text-secondary">${dev.retry_count > 0 ? dev.retry_count + ' retries' : 'None'}</td>
                        <td class="fs-8 text-dark">${dev.last_sync}</td>
                    </tr>
                `;
            });

            body.innerHTML = `
                <div class="table-responsive">
                    <table class="table custom-table table-sm m-0">
                        <thead>
                            <tr>
                                <th>Device</th>
                                <th>Command</th>
                                <th>Status</th>
                                <th>Retries</th>
                                <th>Last Sync</th>
                            </tr>
                        </thead>
                        <tbody>${rows}</tbody>
                    </table>
                </div>
            `;
        })
        .catch(err => {
            body.innerHTML = '<div class="alert alert-danger m-0 fs-7">Failed to load device sync telemetry.</div>';
        });
}
</script>

<!-- Sync Telemetry Modal -->
<div class="modal fade" id="syncTelemetryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content glass-card border-0" style="background-color: var(--card-bg); backdrop-filter: blur(20px);">
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title display-font" id="syncTelemetryModalLabel"><i class="bi bi-diagram-3-fill me-2 text-indigo"></i> Device Sync Telemetry</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="syncTelemetryModalBody">
            </div>
        </div>
    </div>
</div>
<!-- Bulk Import Modal -->
<div class="modal fade" id="bulkImportModal" tabindex="-1" aria-labelledby="bulkImportModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('employees.bulk-import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-content border-0 shadow-lg" style="background-color: var(--card-bg); border-radius: 12px;">
                <div class="modal-header border-bottom border-secondary-subtle">
                    <h5 class="modal-title fw-bold text-dark" id="bulkImportModalLabel"><i class="bi bi-file-earmark-spreadsheet me-2 text-primary"></i> Bulk Import Employees</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="import_company_id" class="form-label text-secondary fw-semibold">Target Company (Optional)</label>
                        <select class="form-select border-secondary-subtle text-dark" id="import_company_id" name="company_id">
                            <option value="">-- Let CSV Auto-Detect --</option>
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}">{{ $company->company_name }}</option>
                            @endforeach
                        </select>
                        <div class="form-text text-muted">
                            If selected, all imported employees will be assigned to this company regardless of their CSV Branch.
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="csv_file" class="form-label text-secondary fw-semibold">Upload CSV File</label>
                        <input class="form-control border-secondary-subtle text-dark" type="file" id="csv_file" name="csv_file" accept=".csv" required>
                        <div class="form-text text-muted mt-2">
                            Please ensure your CSV matches the required template format. Missing employee numbers will automatically fallback to the EPF Number.
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top border-secondary-subtle">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-custom-primary rounded-pill px-4">Import Data</button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection
