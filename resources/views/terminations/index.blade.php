@extends('layouts.app')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h4 class="fw-bold m-0 text-dark display-font">
            <i class="bi bi-person-x-fill me-2 text-danger"></i> Employee Terminations
        </h4>
        <p class="text-secondary fs-7 m-0 mt-1">Manage disciplinary actions, involuntary offboarding, and access revocation.</p>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-xl-4">
        <div class="glass-card p-3 d-flex align-items-center justify-content-between h-100 border-start border-4 border-danger">
            <div>
                <span class="text-secondary fs-8 d-block fw-medium">Total Terminations</span>
                <h4 class="fw-bold m-0 text-dark mt-1">{{ $terminations->count() }}</h4>
            </div>
            <div class="rounded-circle p-2 bg-danger-subtle text-danger d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                <i class="bi bi-person-x fs-5"></i>
            </div>
        </div>
    </div>
</div>

<div class="glass-card mb-4 p-3 d-flex justify-content-end">
    <button type="button" class="btn btn-danger px-4 shadow-sm border-0" data-bs-toggle="offcanvas" data-bs-target="#createTerminationDrawer">
        <i class="bi bi-exclamation-triangle-fill me-1"></i> Process Termination
    </button>
</div>

<div class="glass-card p-4">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h5 class="m-0 display-font text-dark"><i class="bi bi-shield-slash me-2 text-danger"></i> Termination Records</h5>
        <span class="badge bg-danger-subtle text-danger px-3 py-2 rounded-pill fs-7 fw-semibold border border-danger border-opacity-25">
            Showing {{ $terminations->count() }} Records
        </span>
    </div>

    @if($terminations->count() > 0)
        <div class="table-responsive">
            <table class="table custom-table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Type</th>
                        <th>Termination Date</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($terminations as $t)
                        <tr>
                            <td>
                                <div class="fw-semibold text-dark fs-7.5">{{ $t->employee->first_name }} {{ $t->employee->last_name }}</div>
                                <span class="text-secondary fs-8 font-monospace">{{ $t->employee->emp_id }}</span>
                            </td>
                            <td>
                                @php
                                    $typeClass = 'bg-secondary-subtle text-secondary border-secondary';
                                    if ($t->type === 'Involuntary') $typeClass = 'bg-warning-subtle text-warning border-warning';
                                    if ($t->type === 'Disciplinary') $typeClass = 'bg-danger-subtle text-danger border-danger';
                                @endphp
                                <span class="badge {{ $typeClass }} border border-opacity-25">{{ $t->type }}</span>
                            </td>
                            <td>
                                <span class="text-dark fw-semibold fs-8">{{ \Carbon\Carbon::parse($t->termination_date)->format('M d, Y') }}</span>
                            </td>
                            <td>
                                <span class="text-secondary fs-8">{{ Str::limit($t->reason, 40) ?: 'N/A' }}</span>
                            </td>
                            <td>
                                <span class="badge bg-success-subtle text-success border border-success border-opacity-25">{{ $t->status }}</span>
                            </td>
                            <td class="text-end">
                                <form action="{{ route('terminations.delete', $t->id) }}" method="POST" class="d-inline-block mb-1" onsubmit="return confirm('Revert termination? This will completely restore the employee\'s system access and mark them Active again.');">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-custom-secondary px-3 py-1 fs-8 border-0 shadow-sm rounded">
                                        <i class="bi bi-arrow-counterclockwise me-1"></i> Revert
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
            <div class="rounded-circle bg-danger-subtle text-danger d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                <i class="bi bi-person-x fs-1"></i>
            </div>
            <h5 class="fw-bold text-dark mb-2">No Terminations Logged</h5>
            <p class="text-secondary fs-7 mx-auto mb-4" style="max-width: 480px;">
                Process employee terminations to immediately revoke system access and ADMS terminal syncing.
            </p>
            <button type="button" class="btn btn-danger border-0 shadow-sm px-4 py-2" data-bs-toggle="offcanvas" data-bs-target="#createTerminationDrawer">
                <i class="bi bi-exclamation-triangle-fill me-1"></i> Process First Termination
            </button>
        </div>
    @endif
</div>

<!-- Offcanvas Drawer -->
<div class="offcanvas offcanvas-end bg-white shadow p-0" tabindex="-1" id="createTerminationDrawer" style="width: 500px; border-start: 4px solid var(--bs-danger);">
    <div class="offcanvas-header border-bottom p-3 bg-light">
        <h5 class="offcanvas-title text-danger display-font m-0 fw-bold">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> Process Termination
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-4 text-start">
        <div class="alert alert-danger fs-8 fw-semibold border-danger border-opacity-50">
            <i class="bi bi-info-circle me-1"></i> Processing a termination will immediately change the employee's status to <strong>Inactive</strong> and revoke application access.
        </div>
        <form action="{{ route('terminations.store') }}" method="POST">
            @csrf
            
            <div class="mb-3">
                <label class="form-label text-secondary fs-8 fw-semibold">Employee *</label>
                <select name="employee_id" class="form-select form-select-custom border-danger" required>
                    <option value="">-- Select Employee --</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}">{{ $emp->emp_id }} - {{ $emp->first_name }} {{ $emp->last_name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="row g-2 mb-3">
                <div class="col-6">
                    <label class="form-label text-secondary fs-8 fw-semibold">Termination Date *</label>
                    <input type="date" class="form-control form-control-custom" name="termination_date" required>
                </div>
                <div class="col-6">
                    <label class="form-label text-secondary fs-8 fw-semibold">Termination Type *</label>
                    <select name="type" class="form-select form-select-custom" required>
                        <option value="Involuntary">Involuntary (Layoff)</option>
                        <option value="Disciplinary">Disciplinary (Fired)</option>
                        <option value="Voluntary">Voluntary (Quit immediately)</option>
                    </select>
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label text-secondary fs-8 fw-semibold">Reason / Details</label>
                <textarea class="form-control form-control-custom" name="reason" rows="3" placeholder="Document the reason for termination..."></textarea>
            </div>

            <div class="d-flex align-items-center justify-content-end gap-2 mt-4 pt-3 border-top">
                <button type="button" class="btn btn-custom-secondary px-4" data-bs-dismiss="offcanvas">Cancel</button>
                <button type="submit" class="btn btn-danger border-0 shadow-sm px-4 fw-bold">
                    <i class="bi bi-person-x-fill me-1"></i> Terminate Employee
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
