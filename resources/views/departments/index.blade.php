@extends('layouts.app')

@section('content')
<div class="row g-4">
    <!-- List of Departments -->
    <div class="col-lg-8">
        <div class="glass-card">
            <h5 class="mb-4 display-font"><i class="bi bi-briefcase me-2 text-indigo"></i> Registered Departments</h5>
            <div class="table-responsive">
                <table class="table custom-table">
                    <thead>
                        <tr>
                            <th>Dept Code</th>
                            <th>Department Name</th>
                            <th>Company</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($departments as $d)
                            <tr>
                                <td><span class="badge bg-indigo text-light">{{ $d->department_code }}</span></td>
                                <td>{{ $d->department_name }}</td>
                                <td>{{ $d->company->company_name ?? 'N/A' }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <button type="button" class="btn btn-sm btn-outline-primary border-0 me-2" data-bs-toggle="modal" data-bs-target="#editModal{{ $d->id }}">
                                            <i class="bi bi-pencil"></i> Edit
                                        </button>
                                        <form action="{{ route('departments.delete', $d->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this department?');" class="m-0">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-danger border-0">
                                                <i class="bi bi-trash"></i> Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-secondary">No departments registered. Use the form to add one.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Create Department Form -->
    <div class="col-lg-4">
        <div class="glass-card">
            <h5 class="mb-4 display-font"><i class="bi bi-plus-circle-fill me-2 text-indigo"></i> Add Department</h5>
            <form action="{{ route('departments.store') }}" method="POST">
                @csrf
                
                <div class="mb-3">
                    <label class="form-label text-secondary">Company</label>
                    <select class="form-select form-select-custom" name="company_id" required>
                        <option value="">Select Company</option>
                        @foreach($companies as $c)
                            <option value="{{ $c->id }}">{{ $c->company_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label text-secondary">Department Name</label>
                    <input type="text" class="form-control form-control-custom" name="department_name" placeholder="e.g. Finance" required>
                </div>

                <div class="mb-3">
                    <label class="form-label text-secondary">Department Code</label>
                    <input type="text" class="form-control form-control-custom" name="department_code" placeholder="e.g. HR" required>
                </div>

                <button type="submit" class="btn btn-custom-primary w-100 mt-2">
                    <i class="bi bi-save me-2"></i> Register Department
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modals Loop -->
@foreach($departments as $d)
    <div class="modal fade" id="editModal{{ $d->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content glass-card p-0" >
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title display-font"><i class="bi bi-pencil-square me-2 text-indigo"></i> Edit Department</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('departments.update', $d->id) }}" method="POST">
                    @csrf
                    <div class="modal-body text-start">
                        <div class="mb-3">
                            <label class="form-label text-secondary">Company</label>
                            <select class="form-select form-select-custom" name="company_id" required>
                                @foreach($companies as $c)
                                    <option value="{{ $c->id }}" {{ $d->company_id == $c->id ? 'selected' : '' }}>{{ $c->company_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-secondary">Department Name</label>
                            <input type="text" class="form-control form-control-custom" name="department_name" value="{{ $d->department_name }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-secondary">Department Code</label>
                            <input type="text" class="form-control form-control-custom" name="department_code" value="{{ $d->department_code }}" required>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 pt-0">
                        <button type="button" class="btn btn-custom-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-custom-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach
@endsection
