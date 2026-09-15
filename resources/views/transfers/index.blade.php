@extends('layouts.app')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h4 class="fw-bold m-0 text-dark display-font">
            <i class="bi bi-arrow-left-right me-2 text-primary"></i> Employee Transfers
        </h4>
        <p class="text-secondary fs-7 m-0 mt-1">Manage cross-department and cross-branch employee movements.</p>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-xl-4">
        <div class="glass-card p-3 d-flex align-items-center justify-content-between h-100">
            <div>
                <span class="text-secondary fs-8 d-block fw-medium">Total Transfers</span>
                <h4 class="fw-bold m-0 text-dark mt-1">{{ $transfers->count() }}</h4>
            </div>
            <div class="rounded-circle p-2 bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                <i class="bi bi-arrow-left-right fs-5"></i>
            </div>
        </div>
    </div>
</div>

<div class="glass-card mb-4 p-3 d-flex justify-content-end">
    <button type="button" class="btn btn-custom-primary px-4" data-bs-toggle="offcanvas" data-bs-target="#createTransferDrawer">
        <i class="bi bi-plus-lg me-1"></i> New Transfer
    </button>
</div>

<div class="glass-card p-4">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h5 class="m-0 display-font text-dark"><i class="bi bi-arrow-left-right me-2 text-primary"></i> Transfer History</h5>
        <span class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill fs-7 fw-semibold">
            Showing {{ $transfers->count() }} Records
        </span>
    </div>

    @if($transfers->count() > 0)
        <div class="table-responsive">
            <table class="table custom-table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>From</th>
                        <th>To</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transfers as $t)
                        <tr>
                            <td>
                                <div class="fw-semibold text-dark fs-7.5">{{ $t->employee->first_name }} {{ $t->employee->last_name }}</div>
                                <span class="text-secondary fs-8 font-monospace">{{ $t->employee->emp_id }}</span>
                            </td>
                            <td>
                                <span class="badge bg-secondary-subtle text-dark border border-secondary border-opacity-25 mb-1 d-inline-block">{{ $t->previousCompany->company_name ?? 'N/A' }}</span><br>
                                <span class="fs-8 text-secondary"><i class="bi bi-diagram-3 me-1"></i>{{ $t->previousBranch->branch_name ?? 'N/A' }}</span><br>
                                <span class="fs-8 text-secondary"><i class="bi bi-briefcase me-1"></i>{{ $t->previousDepartment->department_name ?? 'N/A' }}</span>
                            </td>
                            <td>
                                <span class="badge bg-primary-subtle text-primary border border-primary border-opacity-25 mb-1 d-inline-block">{{ $t->newCompany->company_name ?? 'N/A' }}</span><br>
                                <span class="fs-8 text-primary"><i class="bi bi-diagram-3 me-1"></i>{{ $t->newBranch->branch_name ?? 'N/A' }}</span><br>
                                <span class="fs-8 text-primary"><i class="bi bi-briefcase me-1"></i>{{ $t->newDepartment->department_name ?? 'N/A' }}</span>
                            </td>
                            <td>
                                <span class="text-dark fs-8">{{ \Carbon\Carbon::parse($t->transfer_date)->format('M d, Y') }}</span>
                            </td>
                            <td>
                                <span class="badge bg-success-subtle text-success border border-success border-opacity-25">{{ $t->status }}</span>
                            </td>
                            <td class="text-end">
                                <form action="{{ route('transfers.delete', $t->id) }}" method="POST" onsubmit="return confirm('Delete this record? If approved, it will REVERT the employee back to their previous department/branch!');">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-danger px-3 py-1 fs-8 border-0 shadow-sm rounded">
                                        <i class="bi bi-arrow-counterclockwise me-1"></i> Revert & Delete
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
                <i class="bi bi-arrow-left-right fs-1"></i>
            </div>
            <h5 class="fw-bold text-dark mb-2">No Transfers Recorded</h5>
            <p class="text-secondary fs-7 mx-auto mb-4" style="max-width: 480px;">
                Record employee transfers across different branches and departments.
            </p>
            <button type="button" class="btn btn-custom-primary px-4 py-2" data-bs-toggle="offcanvas" data-bs-target="#createTransferDrawer">
                <i class="bi bi-plus-lg me-1"></i> First Transfer
            </button>
        </div>
    @endif
</div>

<!-- Offcanvas Drawer -->
<div class="offcanvas offcanvas-end bg-white shadow p-0" tabindex="-1" id="createTransferDrawer" style="width: 500px; border-start: 1px solid var(--border-color);">
    <div class="offcanvas-header border-bottom p-3 bg-light">
        <h5 class="offcanvas-title text-dark display-font m-0">
            <i class="bi bi-plus-circle-fill me-2 text-primary"></i> Process Transfer
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-4 text-start">
        <form action="{{ route('transfers.store') }}" method="POST">
            @csrf
            
            <div class="mb-3">
                <label class="form-label text-secondary fs-8 fw-semibold">Employee *</label>
                <select name="employee_id" class="form-select form-select-custom" required>
                    <option value="">-- Select Employee --</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}">{{ $emp->emp_id }} - {{ $emp->first_name }} {{ $emp->last_name }}</option>
                    @endforeach
                </select>
            </div>

            <h6 class="mt-4 mb-3 text-primary fw-bold fs-7 border-bottom pb-2">New Assignment Details</h6>

            <div class="mb-3">
                <label class="form-label text-secondary fs-8 fw-semibold">New Company *</label>
                <select name="new_company_id" class="form-select form-select-custom" required>
                    <option value="">-- Select Company --</option>
                    @foreach($companies as $company)
                        <option value="{{ $company->id }}">{{ $company->company_name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label text-secondary fs-8 fw-semibold">New Branch *</label>
                <select name="new_branch_id" class="form-select form-select-custom" required>
                    <option value="">-- Select Branch --</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->branch_name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label text-secondary fs-8 fw-semibold">New Department *</label>
                <select name="new_department_id" class="form-select form-select-custom" required>
                    <option value="">-- Select Department --</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}">{{ $dept->department_name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label text-secondary fs-8 fw-semibold">Transfer Date *</label>
                <input type="date" class="form-control form-control-custom" name="transfer_date" required>
            </div>
            
            <div class="mb-3">
                <label class="form-label text-secondary fs-8 fw-semibold">Reason for Transfer</label>
                <textarea class="form-control form-control-custom" name="reason" rows="2"></textarea>
            </div>

            <div class="d-flex align-items-center justify-content-end gap-2 mt-4 pt-3 border-top">
                <button type="button" class="btn btn-custom-secondary px-4" data-bs-dismiss="offcanvas">Cancel</button>
                <button type="submit" class="btn btn-custom-primary px-4 fw-semibold">
                    <i class="bi bi-save me-1"></i> Process Transfer
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
