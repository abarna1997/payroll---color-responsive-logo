@extends('layouts.app')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h4 class="fw-bold m-0 text-dark display-font">
            <i class="bi bi-arrow-repeat me-2 text-primary"></i> Shift Rotations
        </h4>
        <p class="text-secondary fs-7 m-0 mt-1">Manage automated rotating shift patterns.</p>
    </div>
</div>

<div class="glass-card mb-4 p-3 d-flex justify-content-end">
    <button type="button" class="btn btn-custom-primary px-4 shadow-sm border-0" data-bs-toggle="offcanvas" data-bs-target="#createRotationDrawer">
        <i class="bi bi-plus-lg me-1"></i> New Rotation
    </button>
</div>

<div class="glass-card p-4">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h5 class="m-0 display-font text-dark"><i class="bi bi-card-list me-2 text-primary"></i> Active Rotations</h5>
        <span class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill fs-7 fw-semibold border border-primary border-opacity-25">
            Showing {{ $rotations->count() }} Records
        </span>
    </div>

    @if($rotations->count() > 0)
        <div class="table-responsive">
            <table class="table custom-table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Rotation Name</th>
                        <th>Pattern Type</th>
                        <th>Period</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rotations as $r)
                        <tr>
                            <td>
                                <div class="fw-semibold text-dark fs-7.5">{{ $r->name }}</div>
                            </td>
                            <td>
                                <span class="badge bg-info-subtle text-info border border-info border-opacity-25">{{ $r->pattern_type }}</span>
                            </td>
                            <td>
                                <span class="text-dark fs-8">{{ \Carbon\Carbon::parse($r->start_date)->format('M d, Y') }} - {{ $r->end_date ? \Carbon\Carbon::parse($r->end_date)->format('M d, Y') : 'Ongoing' }}</span>
                            </td>
                            <td>
                                @php
                                    $badgeClass = $r->status === 'Active' ? 'bg-success-subtle text-success border-success' : 'bg-secondary-subtle text-secondary border-secondary';
                                @endphp
                                <span class="badge {{ $badgeClass }} border border-opacity-25">{{ $r->status }}</span>
                            </td>
                            <td class="text-end">
                                <form action="{{ route('shifts.rotations.delete', $r->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Delete this rotation?');">
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
        <div class="text-center py-5 my-3">
            <div class="rounded-circle bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                <i class="bi bi-arrow-repeat fs-1"></i>
            </div>
            <h5 class="fw-bold text-dark mb-2">No Shift Rotations Found</h5>
            <p class="text-secondary fs-7 mx-auto mb-4" style="max-width: 480px;">
                Create automated shift patterns for employees that change weekly or monthly.
            </p>
        </div>
    @endif
</div>

<!-- Offcanvas Drawer -->
<div class="offcanvas offcanvas-end bg-white shadow p-0" tabindex="-1" id="createRotationDrawer" style="width: 450px;">
    <div class="offcanvas-header border-bottom p-3 bg-light">
        <h5 class="offcanvas-title text-dark display-font m-0 fw-bold">
            <i class="bi bi-plus-circle-fill me-2 text-primary"></i> Create Rotation
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-4 text-start">
        <form action="{{ route('shifts.rotations.store') }}" method="POST">
            @csrf
            
            <div class="mb-3">
                <label class="form-label text-secondary fs-8 fw-semibold">Rotation Name *</label>
                <input type="text" class="form-control form-control-custom" name="name" required placeholder="e.g. 2-Week Swing">
            </div>

            <div class="mb-3">
                <label class="form-label text-secondary fs-8 fw-semibold">Pattern Type *</label>
                <select name="pattern_type" class="form-select form-select-custom" required>
                    <option value="Weekly">Weekly</option>
                    <option value="Bi-Weekly">Bi-Weekly</option>
                    <option value="Monthly">Monthly</option>
                </select>
            </div>

            <div class="row g-2 mb-3">
                <div class="col-6">
                    <label class="form-label text-secondary fs-8 fw-semibold">Start Date *</label>
                    <input type="date" class="form-control form-control-custom" name="start_date" required>
                </div>
                <div class="col-6">
                    <label class="form-label text-secondary fs-8 fw-semibold">End Date (Optional)</label>
                    <input type="date" class="form-control form-control-custom" name="end_date">
                </div>
            </div>

            <div class="d-flex align-items-center justify-content-end gap-2 mt-4 pt-3 border-top">
                <button type="button" class="btn btn-custom-secondary px-4" data-bs-dismiss="offcanvas">Cancel</button>
                <button type="submit" class="btn btn-custom-primary border-0 shadow-sm px-4 fw-bold">
                    <i class="bi bi-save me-1"></i> Save Rotation
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
