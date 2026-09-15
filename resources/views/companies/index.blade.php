@extends('layouts.app')

@section('content')
<!-- Page Header -->
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h4 class="fw-bold m-0 text-dark display-font">
            <i class="bi bi-building-gear me-2 text-primary"></i> Enterprise Company Control Center
        </h4>
        <p class="text-secondary fs-7 m-0 mt-1">Manage corporate entities, branch networks, department hierarchies, employee prefixes & ADMS terminal sync policies.</p>
    </div>
</div>

<!-- 1. Top Summary Dashboard Telemetry Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-xl-2">
        <div class="glass-card p-3 d-flex align-items-center justify-content-between h-100">
            <div>
                <span class="text-secondary fs-8 d-block fw-medium">Total Companies</span>
                <h4 class="fw-bold m-0 text-dark mt-1">{{ $metrics['total_companies'] }}</h4>
            </div>
            <div class="rounded-circle p-2 bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                <i class="bi bi-building-fill fs-5"></i>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="glass-card p-3 d-flex align-items-center justify-content-between h-100">
            <div>
                <span class="text-secondary fs-8 d-block fw-medium">Active Status</span>
                <h4 class="fw-bold m-0 text-success mt-1">{{ $metrics['active_companies'] }}</h4>
            </div>
            <div class="rounded-circle p-2 bg-success-subtle text-success d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                <i class="bi bi-check-circle-fill fs-5"></i>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="glass-card p-3 d-flex align-items-center justify-content-between h-100">
            <div>
                <span class="text-secondary fs-8 d-block fw-medium">Inactive</span>
                <h4 class="fw-bold m-0 text-secondary mt-1">{{ $metrics['inactive_companies'] }}</h4>
            </div>
            <div class="rounded-circle p-2 bg-secondary-subtle text-secondary d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                <i class="bi bi-pause-circle-fill fs-5"></i>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="glass-card p-3 d-flex align-items-center justify-content-between h-100">
            <div>
                <span class="text-secondary fs-8 d-block fw-medium">Total Branches</span>
                <h4 class="fw-bold m-0 text-info mt-1">{{ $metrics['total_branches'] }}</h4>
            </div>
            <div class="rounded-circle p-2 bg-info-subtle text-info d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                <i class="bi bi-diagram-3-fill fs-5"></i>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="glass-card p-3 d-flex align-items-center justify-content-between h-100">
            <div>
                <span class="text-secondary fs-8 d-block fw-medium">Total Workforce</span>
                <h4 class="fw-bold m-0 text-primary mt-1">{{ $metrics['total_employees'] }}</h4>
            </div>
            <div class="rounded-circle p-2 bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                <i class="bi bi-people-fill fs-5"></i>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="glass-card p-3 d-flex align-items-center justify-content-between h-100">
            <div>
                <span class="text-secondary fs-8 d-block fw-medium">Terminals</span>
                <h4 class="fw-bold m-0 text-warning mt-1">{{ $metrics['total_devices'] }}</h4>
            </div>
            <div class="rounded-circle p-2 bg-warning-subtle text-warning d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                <i class="bi bi-cpu-fill fs-5"></i>
            </div>
        </div>
    </div>
</div>

<!-- 2. Search & Toolbar Panel -->
<div class="glass-card mb-4 p-3">
    <form action="{{ route('companies') }}" method="GET" class="row g-2 align-items-center">
        <div class="col-12 col-md-5 col-lg-6">
            <div class="input-group">
                <span class="input-group-text bg-transparent border-end-0 text-secondary" style="border-color: var(--border-color);">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text" name="search" class="form-control form-control-custom border-start-0" placeholder="Search company by name, code, or reg number..." value="{{ request('search') }}">
            </div>
        </div>
        <div class="col-6 col-md-3 col-lg-3">
            <select name="status" class="form-select form-select-custom" onchange="this.form.submit()">
                <option value="">All Statuses</option>
                <option value="Active" {{ request('status') === 'Active' ? 'selected' : '' }}>Active</option>
                <option value="Inactive" {{ request('status') === 'Inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>
        <div class="col-6 col-md-2 col-lg-1">
            @if(request()->hasAny(['search', 'status']))
                <a href="{{ route('companies') }}" class="btn btn-custom-secondary w-100" title="Reset Filters">
                    <i class="bi bi-arrow-counterclockwise"></i> Reset
                </a>
            @endif
        </div>
        <div class="col-12 col-lg-2 text-lg-end ms-auto">
            <button type="button" class="btn btn-custom-primary w-100" data-bs-toggle="offcanvas" data-bs-target="#createCompanyDrawer">
                <i class="bi bi-plus-lg me-1"></i> Add Company
            </button>
        </div>
    </form>
</div>

<!-- 3. Full Width Company Registry Table -->
<div class="glass-card p-4">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h5 class="m-0 display-font text-dark"><i class="bi bi-building me-2 text-primary"></i> Registered Corporate Entities</h5>
        <span class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill fs-7 fw-semibold">
            Showing {{ $companies->count() }} Corporate Entities
        </span>
    </div>

    @if($companies->count() > 0)
        <div class="table-responsive">
            <table class="table custom-table align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 50px;">Status</th>
                        <th>Company Code</th>
                        <th>Company Name</th>
                        <th>Registration No.</th>
                        <th>Branches</th>
                        <th>Employees</th>
                        <th>Contact / Email</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($companies as $c)
                        <tr>
                            <td>
                                <span class="badge-status {{ $c->status === 'Active' ? 'badge-online' : 'badge-disabled' }}">
                                    {{ $c->status }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-primary text-light font-monospace fs-7.5 px-2 py-1">{{ $c->company_code }}</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    @if($c->logo_path)
                                        <img src="{{ asset($c->logo_path) }}" alt="Logo" class="rounded" style="width: 28px; height: 28px; object-fit: contain;">
                                    @else
                                        <div class="rounded bg-primary-subtle text-primary d-flex align-items-center justify-content-center fw-bold fs-8" style="width: 28px; height: 28px;">
                                            {{ strtoupper(substr($c->company_name, 0, 2)) }}
                                        </div>
                                    @endif
                                    <div>
                                        <div class="fw-semibold text-dark fs-7.5">{{ $c->company_name }}</div>
                                        <span class="text-secondary fs-8 font-monospace">Prefix: {{ $c->company_code }}-*</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="text-secondary font-monospace fs-8">{{ $c->registration_number ?? 'N/A' }}</span>
                            </td>
                            <td>
                                <span class="badge bg-secondary-subtle text-dark fs-8 border border-secondary border-opacity-25"><i class="bi bi-diagram-3 me-1 text-primary"></i>{{ $c->branches_count }} Branches</span>
                            </td>
                            <td>
                                <span class="badge bg-info-subtle text-info fs-8 border border-info border-opacity-25"><i class="bi bi-people me-1"></i>{{ $c->employees_count }} Staff</span>
                            </td>
                            <td>
                                <div class="fs-8 text-secondary">
                                    @if($c->contact_number)<i class="bi bi-telephone me-1"></i>{{ $c->contact_number }}@endif
                                    @if($c->email)<br><i class="bi bi-envelope me-1"></i>{{ $c->email }}@endif
                                </div>
                            </td>
                            <td class="text-end">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-custom-secondary dropdown-toggle px-3 py-1 fs-8" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        Actions
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm p-1 border border-secondary border-opacity-25">
                                        <li>
                                            <button type="button" class="dropdown-item fs-8 rounded py-2 text-info" data-bs-toggle="modal" data-bs-target="#profileCompanyModal{{ $c->id }}">
                                                <i class="bi bi-eye-fill me-2"></i> View Profile & Stats
                                            </button>
                                        </li>
                                        <li>
                                            <form action="{{ route('companies.sync-all-employees', $c->id) }}" method="POST" class="m-0">
                                                @csrf
                                                <button type="submit" class="dropdown-item fs-8 rounded py-2 text-success">
                                                    <i class="bi bi-arrow-repeat me-2"></i> Sync All Terminals
                                                </button>
                                            </form>
                                        </li>
                                        <li>
                                            <a class="dropdown-item fs-8 rounded py-2 text-primary" href="{{ route('companies.edit', $c->id) }}">
                                                <i class="bi bi-sliders me-2"></i> Company Settings
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider my-1"></li>
                                        <li>
                                            <form action="{{ route('companies.delete', $c->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete {{ $c->company_name }}? All employees, departments, and logs will be permanently deleted!');">
                                                @csrf
                                                <button type="submit" class="dropdown-item fs-8 rounded py-2 text-danger">
                                                    <i class="bi bi-trash-fill me-2"></i> Delete Company
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <!-- Professional Empty State -->
        <div class="text-center py-5 my-3">
            <div class="rounded-circle bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                <i class="bi bi-building fs-1"></i>
            </div>
            <h5 class="fw-bold text-dark mb-2">No Companies Registered Yet</h5>
            <p class="text-secondary fs-7 mx-auto mb-4" style="max-width: 480px;">
                Add your corporate parent entities and subsidiaries to establish organizational hierarchies, employee ID prefixes, and ADMS terminal policies.
            </p>
            <button type="button" class="btn btn-custom-primary px-4 py-2" data-bs-toggle="offcanvas" data-bs-target="#createCompanyDrawer">
                <i class="bi bi-plus-lg me-1"></i> Register First Company
            </button>
        </div>
    @endif
</div>

<!-- 4. Create Company Slide-over Offcanvas Drawer -->
<div class="offcanvas offcanvas-end bg-white shadow p-0" tabindex="-1" id="createCompanyDrawer" style="width: 620px; border-start: 1px solid var(--border-color);">
    <div class="offcanvas-header border-bottom p-3 bg-light">
        <h5 class="offcanvas-title text-dark display-font m-0">
            <i class="bi bi-building-add me-2 text-primary"></i> Register New Corporate Entity
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-4 text-start">
        <form action="{{ route('companies.store') }}" method="POST">
            @csrf
            
            <div class="mb-3">
                <label class="form-label text-secondary fs-8 fw-semibold">Company Name *</label>
                <input type="text" class="form-control form-control-custom" name="company_name" placeholder="e.g. Prime One Global Holdings Ltd" required>
            </div>

            <div class="row g-2 mb-3">
                <div class="col-6">
                    <label class="form-label text-secondary fs-8 fw-semibold">Company Code *</label>
                    <input type="text" class="form-control form-control-custom font-monospace" name="company_code" placeholder="e.g. P1" required>
                    <div class="form-text text-secondary fs-8">Prefixes employee IDs (e.g. P1-0001)</div>
                </div>
                <div class="col-6">
                    <label class="form-label text-secondary fs-8 fw-semibold">Registration Number</label>
                    <input type="text" class="form-control form-control-custom font-monospace" name="registration_number" placeholder="e.g. PV-100293">
                </div>
            </div>

            <div class="row g-2 mb-3">
                <div class="col-6">
                    <label class="form-label text-secondary fs-8 fw-semibold">Contact Phone Number</label>
                    <input type="text" class="form-control form-control-custom" name="contact_number" placeholder="e.g. +94112345678">
                </div>
                <div class="col-6">
                    <label class="form-label text-secondary fs-8 fw-semibold">Official Email Address</label>
                    <input type="email" class="form-control form-control-custom" name="email" placeholder="e.g. info@primeone.lk">
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label text-secondary fs-8 fw-semibold">Headquarters Physical Address</label>
                <textarea class="form-control form-control-custom" name="address" rows="3" placeholder="Company headquarters street address, city, country..."></textarea>
            </div>

            <div class="d-flex align-items-center justify-content-end gap-2">
                <button type="button" class="btn btn-custom-secondary px-4" data-bs-dismiss="offcanvas">Cancel</button>
                <button type="submit" class="btn btn-custom-primary px-4 py-2 fw-semibold">
                    <i class="bi bi-save me-1"></i> Register Company
                </button>
            </div>
        </form>
    </div>
</div>

<!-- 5. Company Detailed Profile & Telemetry Modals -->
@foreach($companies as $c)
    <div class="modal fade" id="profileCompanyModal{{ $c->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content bg-white shadow-lg border-0 p-0">
                <div class="modal-header border-bottom p-3 bg-light">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-building-check fs-4 text-primary"></i>
                        <div>
                            <h5 class="modal-title display-font m-0 text-dark">{{ $c->company_name }}</h5>
                            <span class="text-secondary fs-8 font-monospace">Company Code: {{ $c->company_code }} | Reg: {{ $c->registration_number ?? 'N/A' }}</span>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 text-start">
                    <div class="row g-3 mb-4">
                        <div class="col-4">
                            <div class="p-3 rounded bg-light border border-secondary border-opacity-25 text-center">
                                <span class="text-secondary fs-8 d-block">Branches</span>
                                <h4 class="fw-bold text-info m-0 mt-1">{{ $c->branches_count }}</h4>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-3 rounded bg-light border border-secondary border-opacity-25 text-center">
                                <span class="text-secondary fs-8 d-block">Total Workforce</span>
                                <h4 class="fw-bold text-primary m-0 mt-1">{{ $c->employees_count }}</h4>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-3 rounded bg-light border border-secondary border-opacity-25 text-center">
                                <span class="text-secondary fs-8 d-block">Status</span>
                                <span class="badge-status {{ $c->status === 'Active' ? 'badge-online' : 'badge-disabled' }} d-inline-block mt-2">
                                    {{ $c->status }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <h6 class="text-primary fs-8 uppercase fw-bold mb-2">Corporate Details</h6>
                        <div class="fs-8 text-dark lh-lg">
                            <strong>Phone:</strong> {{ $c->contact_number ?? 'Not registered' }}<br>
                            <strong>Email:</strong> {{ $c->email ?? 'Not registered' }}<br>
                            <strong>Address:</strong> {{ $c->address ?? 'Not registered' }}
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top p-3 bg-light">
                    <button type="button" class="btn btn-custom-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                    <a href="{{ route('companies.edit', $c->id) }}" class="btn btn-custom-primary btn-sm">
                        <i class="bi bi-sliders me-1"></i> Open Full Company Console
                    </a>
                </div>
            </div>
        </div>
    </div>
@endforeach
@endsection
