@extends('layouts.app')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h4 class="fw-bold m-0 text-dark display-font">
            <i class="bi bi-alarm-fill me-2 text-warning"></i> Late Arrivals
        </h4>
        <p class="text-secondary fs-7 m-0 mt-1">Track and manage employee tardiness.</p>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-xl-4">
        <div class="glass-card p-3 d-flex align-items-center justify-content-between h-100">
            <div>
                <span class="text-secondary fs-8 d-block fw-medium">Total Late Arrivals</span>
                <h4 class="fw-bold m-0 text-dark mt-1">{{ $lateArrivals->count() }}</h4>
            </div>
            <div class="rounded-circle p-2 bg-warning-subtle text-warning d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                <i class="bi bi-alarm fs-5"></i>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-4">
        <div class="glass-card p-3 d-flex align-items-center justify-content-between h-100">
            <div>
                <span class="text-secondary fs-8 d-block fw-medium">Pending Review</span>
                <h4 class="fw-bold m-0 text-primary mt-1">{{ $lateArrivals->where('status', 'Pending')->count() }}</h4>
            </div>
            <div class="rounded-circle p-2 bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                <i class="bi bi-hourglass-split fs-5"></i>
            </div>
        </div>
    </div>
</div>

<div class="glass-card mb-4 p-3 d-flex justify-content-end">
    <button type="button" class="btn btn-warning text-dark fw-semibold px-4 shadow-sm border-0" data-bs-toggle="offcanvas" data-bs-target="#createLateArrivalDrawer">
        <i class="bi bi-plus-lg me-1"></i> Log Late Arrival
    </button>
</div>

<div class="glass-card p-4">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h5 class="m-0 display-font text-dark"><i class="bi bi-list-ul me-2 text-warning"></i> Late Arrival History</h5>
        <span class="badge bg-warning-subtle text-warning px-3 py-2 rounded-pill fs-7 fw-semibold border border-warning border-opacity-25">
            Showing {{ $lateArrivals->count() }} Records
        </span>
    </div>

    @if($lateArrivals->count() > 0)
        <div class="table-responsive">
            <table class="table custom-table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Date</th>
                        <th>Time Late</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($lateArrivals as $l)
                        <tr>
                            <td>
                                <div class="fw-semibold text-dark fs-7.5">{{ $l->employee->first_name }} {{ $l->employee->last_name }}</div>
                                <span class="text-secondary fs-8 font-monospace">{{ $l->employee->emp_id }}</span>
                            </td>
                            <td>
                                <span class="text-dark fs-8">{{ \Carbon\Carbon::parse($l->date)->format('M d, Y') }}</span>
                            </td>
                            <td>
                                <span class="badge bg-danger-subtle text-danger border border-danger border-opacity-25">{{ $l->minutes_late }} Mins</span>
                            </td>
                            <td>
                                <span class="text-secondary fs-8">{{ Str::limit($l->reason, 40) ?: 'N/A' }}</span>
                            </td>
                            <td>
                                @php
                                    $badgeClass = 'bg-secondary-subtle text-secondary border-secondary';
                                    if ($l->status === 'Excused') $badgeClass = 'bg-success-subtle text-success border-success';
                                    if ($l->status === 'Unexcused') $badgeClass = 'bg-danger-subtle text-danger border-danger';
                                @endphp
                                <span class="badge {{ $badgeClass }} border border-opacity-25">{{ $l->status }}</span>
                            </td>
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-custom-secondary px-3 py-1 fs-8 mb-1" data-bs-toggle="modal" data-bs-target="#editLateArrivalModal{{ $l->id }}">
                                    <i class="bi bi-pencil me-1"></i> Update Status
                                </button>
                                <form action="{{ route('late-arrivals.delete', $l->id) }}" method="POST" class="d-inline-block mb-1" onsubmit="return confirm('Delete this record?');">
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
            <div class="rounded-circle bg-warning-subtle text-warning d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                <i class="bi bi-alarm fs-1"></i>
            </div>
            <h5 class="fw-bold text-dark mb-2">No Late Arrivals Logged</h5>
            <p class="text-secondary fs-7 mx-auto mb-4" style="max-width: 480px;">
                Track employee tardiness and mark them as excused or unexcused.
            </p>
            <button type="button" class="btn btn-warning text-dark fw-semibold px-4 py-2 border-0 shadow-sm" data-bs-toggle="offcanvas" data-bs-target="#createLateArrivalDrawer">
                <i class="bi bi-plus-lg me-1"></i> Log Late Arrival
            </button>
        </div>
    @endif
</div>

<!-- Offcanvas Drawer -->
<div class="offcanvas offcanvas-end bg-white shadow p-0" tabindex="-1" id="createLateArrivalDrawer" style="width: 500px; border-start: 4px solid var(--bs-warning);">
    <div class="offcanvas-header border-bottom p-3 bg-light">
        <h5 class="offcanvas-title text-dark display-font m-0 fw-bold">
            <i class="bi bi-plus-circle-fill me-2 text-warning"></i> Log Late Arrival
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-4 text-start">
        <form action="{{ route('late-arrivals.store') }}" method="POST">
            @csrf
            
            <div class="mb-3">
                <label class="form-label text-secondary fs-8 fw-semibold">Employee *</label>
                <select name="employee_id" class="form-select form-select-custom border-warning" required>
                    <option value="">-- Select Employee --</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}">{{ $emp->emp_id }} - {{ $emp->first_name }} {{ $emp->last_name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="row g-2 mb-3">
                <div class="col-6">
                    <label class="form-label text-secondary fs-8 fw-semibold">Date *</label>
                    <input type="date" class="form-control form-control-custom" name="date" required>
                </div>
                <div class="col-6">
                    <label class="form-label text-secondary fs-8 fw-semibold">Minutes Late *</label>
                    <input type="number" class="form-control form-control-custom" name="minutes_late" min="1" required>
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label text-secondary fs-8 fw-semibold">Reason provided</label>
                <textarea class="form-control form-control-custom" name="reason" rows="3"></textarea>
            </div>

            <div class="d-flex align-items-center justify-content-end gap-2 mt-4 pt-3 border-top">
                <button type="button" class="btn btn-custom-secondary px-4" data-bs-dismiss="offcanvas">Cancel</button>
                <button type="submit" class="btn btn-warning text-dark border-0 shadow-sm px-4 fw-bold">
                    <i class="bi bi-save me-1"></i> Save Record
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modals -->
@foreach($lateArrivals as $l)
<div class="modal fade" id="editLateArrivalModal{{ $l->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-white shadow-lg border-0">
            <div class="modal-header border-bottom p-3 bg-light">
                <h5 class="modal-title display-font m-0 text-dark"><i class="bi bi-pencil-square me-2 text-warning"></i>Update Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 text-start">
                <form action="{{ route('late-arrivals.update-status', $l->id) }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label class="form-label text-secondary fs-8 fw-semibold">Employee</label>
                        <input type="text" class="form-control form-control-custom bg-light" value="{{ $l->employee->first_name }} {{ $l->employee->last_name }}" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-secondary fs-8 fw-semibold">Status *</label>
                        <select name="status" class="form-select form-select-custom" required>
                            <option value="Pending" {{ $l->status === 'Pending' ? 'selected' : '' }}>Pending Review</option>
                            <option value="Excused" {{ $l->status === 'Excused' ? 'selected' : '' }}>Excused</option>
                            <option value="Unexcused" {{ $l->status === 'Unexcused' ? 'selected' : '' }}>Unexcused</option>
                        </select>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                        <button type="button" class="btn btn-custom-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-custom-primary px-4 fw-semibold"><i class="bi bi-save me-1"></i> Update Status</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endforeach
@endsection
