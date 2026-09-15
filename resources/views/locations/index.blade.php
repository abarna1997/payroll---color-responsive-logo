@extends('layouts.app')

@section('content')
<!-- Page Header -->
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h4 class="fw-bold m-0 text-dark display-font">
            <i class="bi bi-geo-alt-fill me-2 text-primary"></i> Company Locations & Sites
        </h4>
        <p class="text-secondary fs-7 m-0 mt-1">Manage physical office locations, regional headquarters, and operational sites.</p>
    </div>
</div>

<!-- 1. Top Summary Dashboard Telemetry Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-xl-4">
        <div class="glass-card p-3 d-flex align-items-center justify-content-between h-100">
            <div>
                <span class="text-secondary fs-8 d-block fw-medium">Total Locations</span>
                <h4 class="fw-bold m-0 text-dark mt-1">{{ $locations->count() }}</h4>
            </div>
            <div class="rounded-circle p-2 bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                <i class="bi bi-geo-fill fs-5"></i>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-4">
        <div class="glass-card p-3 d-flex align-items-center justify-content-between h-100">
            <div>
                <span class="text-secondary fs-8 d-block fw-medium">Active Sites</span>
                <h4 class="fw-bold m-0 text-success mt-1">{{ $locations->where('status', 'Active')->count() }}</h4>
            </div>
            <div class="rounded-circle p-2 bg-success-subtle text-success d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                <i class="bi bi-check-circle-fill fs-5"></i>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-4">
        <div class="glass-card p-3 d-flex align-items-center justify-content-between h-100">
            <div>
                <span class="text-secondary fs-8 d-block fw-medium">Inactive Sites</span>
                <h4 class="fw-bold m-0 text-secondary mt-1">{{ $locations->where('status', 'Inactive')->count() }}</h4>
            </div>
            <div class="rounded-circle p-2 bg-secondary-subtle text-secondary d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                <i class="bi bi-pause-circle-fill fs-5"></i>
            </div>
        </div>
    </div>
</div>

<!-- 2. Search & Toolbar Panel -->
<div class="glass-card mb-4 p-3 d-flex justify-content-end">
    <button type="button" class="btn btn-custom-primary px-4" data-bs-toggle="offcanvas" data-bs-target="#createLocationDrawer">
        <i class="bi bi-plus-lg me-1"></i> Add Location
    </button>
</div>

<!-- 3. Full Width Locations Table -->
<div class="glass-card p-4">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h5 class="m-0 display-font text-dark"><i class="bi bi-geo-alt me-2 text-primary"></i> Registered Locations</h5>
        <span class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill fs-7 fw-semibold">
            Showing {{ $locations->count() }} Sites
        </span>
    </div>

    @if($locations->count() > 0)
        <div class="table-responsive">
            <table class="table custom-table align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 50px;">Status</th>
                        <th>Location Name</th>
                        <th>Company</th>
                        <th>City / State</th>
                        <th>Country</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($locations as $l)
                        <tr>
                            <td>
                                <span class="badge-status {{ $l->status === 'Active' ? 'badge-online' : 'badge-disabled' }}">
                                    {{ $l->status }}
                                </span>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark fs-7.5">{{ $l->location_name }}</div>
                                <div class="fs-8 text-secondary mt-1"><i class="bi bi-pin-map me-1"></i>{{ Str::limit($l->address, 30) ?: 'No address specified' }}</div>
                            </td>
                            <td>
                                <span class="badge bg-primary-subtle text-primary fs-8 border border-primary border-opacity-25">{{ $l->company->company_name ?? 'N/A' }}</span>
                            </td>
                            <td>
                                <span class="text-dark fs-8">{{ $l->city ?? 'N/A' }}</span>
                                @if($l->state)
                                <span class="text-secondary fs-8">, {{ $l->state }}</span>
                                @endif
                            </td>
                            <td>
                                <span class="text-secondary fs-8">{{ $l->country ?? 'N/A' }}</span>
                            </td>
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-custom-secondary px-3 py-1 fs-8" data-bs-toggle="modal" data-bs-target="#editLocationModal{{ $l->id }}">
                                    <i class="bi bi-pencil me-1"></i> Edit
                                </button>
                                <form action="{{ route('locations.delete', $l->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Are you sure you want to delete this location?');">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-danger px-3 py-1 fs-8 border-0 shadow-sm rounded">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
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
                <i class="bi bi-geo-alt fs-1"></i>
            </div>
            <h5 class="fw-bold text-dark mb-2">No Locations Registered Yet</h5>
            <p class="text-secondary fs-7 mx-auto mb-4" style="max-width: 480px;">
                Add your physical office locations and operational sites to map workforce presence and branch networks.
            </p>
            <button type="button" class="btn btn-custom-primary px-4 py-2" data-bs-toggle="offcanvas" data-bs-target="#createLocationDrawer">
                <i class="bi bi-plus-lg me-1"></i> Register First Location
            </button>
        </div>
    @endif
</div>

<!-- 4. Create Location Slide-over Offcanvas Drawer -->
<div class="offcanvas offcanvas-end bg-white shadow p-0" tabindex="-1" id="createLocationDrawer" style="width: 500px; border-start: 1px solid var(--border-color);">
    <div class="offcanvas-header border-bottom p-3 bg-light">
        <h5 class="offcanvas-title text-dark display-font m-0">
            <i class="bi bi-plus-circle-fill me-2 text-primary"></i> Register New Location
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-4 text-start">
        <form action="{{ route('locations.store') }}" method="POST">
            @csrf
            
            <div class="mb-3">
                <label class="form-label text-secondary fs-8 fw-semibold">Company Entity *</label>
                <select name="company_id" class="form-select form-select-custom" required>
                    <option value="">-- Select Company --</option>
                    @foreach($companies as $company)
                        <option value="{{ $company->id }}">{{ $company->company_name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label text-secondary fs-8 fw-semibold">Location Name *</label>
                <input type="text" class="form-control form-control-custom" name="location_name" placeholder="e.g. Colombo HQ" required>
            </div>

            <div class="mb-3">
                <label class="form-label text-secondary fs-8 fw-semibold">Street Address</label>
                <textarea class="form-control form-control-custom" name="address" rows="2" placeholder="Full street address..."></textarea>
            </div>

            <div class="row g-2 mb-3">
                <div class="col-6">
                    <label class="form-label text-secondary fs-8 fw-semibold">City</label>
                    <input type="text" class="form-control form-control-custom" name="city" placeholder="e.g. Colombo">
                </div>
                <div class="col-6">
                    <label class="form-label text-secondary fs-8 fw-semibold">State / Province</label>
                    <input type="text" class="form-control form-control-custom" name="state" placeholder="e.g. Western">
                </div>
            </div>

            <div class="row g-2 mb-4">
                <div class="col-6">
                    <label class="form-label text-secondary fs-8 fw-semibold">Country</label>
                    <input type="text" class="form-control form-control-custom" name="country" placeholder="e.g. Sri Lanka" value="Sri Lanka">
                </div>
                <div class="col-6">
                    <label class="form-label text-secondary fs-8 fw-semibold">Zip / Postal Code</label>
                    <input type="text" class="form-control form-control-custom" name="zip_code" placeholder="e.g. 00100">
                </div>
            </div>

            <div class="d-flex align-items-center justify-content-end gap-2 mt-4 pt-3 border-top">
                <button type="button" class="btn btn-custom-secondary px-4" data-bs-dismiss="offcanvas">Cancel</button>
                <button type="submit" class="btn btn-custom-primary px-4 fw-semibold">
                    <i class="bi bi-save me-1"></i> Register Location
                </button>
            </div>
        </form>
    </div>
</div>

<!-- 5. Edit Modals -->
@foreach($locations as $l)
<div class="modal fade" id="editLocationModal{{ $l->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-white shadow-lg border-0">
            <div class="modal-header border-bottom p-3 bg-light">
                <h5 class="modal-title display-font m-0 text-dark"><i class="bi bi-pencil-square me-2 text-primary"></i>Edit Location</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 text-start">
                <form action="{{ route('locations.update', $l->id) }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label class="form-label text-secondary fs-8 fw-semibold">Company Entity *</label>
                        <select name="company_id" class="form-select form-select-custom" required>
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}" {{ $l->company_id == $company->id ? 'selected' : '' }}>{{ $company->company_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-secondary fs-8 fw-semibold">Location Name *</label>
                        <input type="text" class="form-control form-control-custom" name="location_name" value="{{ $l->location_name }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-secondary fs-8 fw-semibold">Street Address</label>
                        <textarea class="form-control form-control-custom" name="address" rows="2">{{ $l->address }}</textarea>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label text-secondary fs-8 fw-semibold">City</label>
                            <input type="text" class="form-control form-control-custom" name="city" value="{{ $l->city }}">
                        </div>
                        <div class="col-6">
                            <label class="form-label text-secondary fs-8 fw-semibold">State / Province</label>
                            <input type="text" class="form-control form-control-custom" name="state" value="{{ $l->state }}">
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label text-secondary fs-8 fw-semibold">Country</label>
                            <input type="text" class="form-control form-control-custom" name="country" value="{{ $l->country }}">
                        </div>
                        <div class="col-6">
                            <label class="form-label text-secondary fs-8 fw-semibold">Status *</label>
                            <select name="status" class="form-select form-select-custom" required>
                                <option value="Active" {{ $l->status === 'Active' ? 'selected' : '' }}>Active</option>
                                <option value="Inactive" {{ $l->status === 'Inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                        <button type="button" class="btn btn-custom-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-custom-primary px-4 fw-semibold"><i class="bi bi-save me-1"></i> Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endforeach
@endsection
