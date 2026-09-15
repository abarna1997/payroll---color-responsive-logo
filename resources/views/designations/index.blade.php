@extends('layouts.app')

@section('content')
<div class="row g-4">
    <!-- List of Designations -->
    <div class="col-lg-8">
        <div class="glass-card">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <h5 class="mb-1 display-font"><i class="bi bi-award me-2 text-indigo"></i> Registered Designations</h5>
                    <p class="text-secondary fs-8 mb-0">Manage job titles, role codes, and workforce assignments across the organization.</p>
                </div>
                <a href="{{ route('employees') }}" class="btn btn-outline-indigo btn-sm px-3">
                    <i class="bi bi-people me-1"></i> Manage Workforce
                </a>
            </div>

            <div class="table-responsive">
                <table class="table custom-table">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Designation Title</th>
                            <th>Company</th>
                            <th>Workforce</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($designations as $d)
                            <tr>
                                <td><span class="badge bg-indigo text-light font-monospace">{{ $d->designation_code ?? 'DESG' }}</span></td>
                                <td><span class="fw-semibold text-dark fs-8">{{ $d->title }}</span></td>
                                <td><span class="fs-8 text-secondary">{{ $d->company->company_name ?? 'Global' }}</span></td>
                                <td>
                                    <span class="badge bg-indigo-subtle text-indigo fs-9 px-2 py-1">
                                        <i class="bi bi-person-check me-1"></i> {{ $d->employees_count ?? 0 }} Staff
                                    </span>
                                </td>
                                <td>
                                    <span class="badge {{ $d->status === 'Active' ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }} fs-9">
                                        {{ $d->status }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <button type="button" class="btn btn-sm btn-outline-primary border-0 py-1 px-2 fs-9" data-bs-toggle="modal" data-bs-target="#editModal{{ $d->id }}">
                                            <i class="bi bi-pencil me-1"></i> Edit
                                        </button>
                                        <form action="{{ route('designations.delete', $d->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this designation?');" class="m-0">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-danger border-0 py-1 px-2 fs-9">
                                                <i class="bi bi-trash me-1"></i> Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-secondary py-4">
                                    <i class="bi bi-award fs-1 text-secondary opacity-50 d-block mb-2"></i>
                                    No designations registered yet. Use the form to add one.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Create Designation Form -->
    <div class="col-lg-4">
        <div class="glass-card">
            <h5 class="mb-4 display-font"><i class="bi bi-plus-circle-fill me-2 text-indigo"></i> Add Designation</h5>
            <form action="{{ route('designations.store') }}" method="POST">
                @csrf
                
                <div class="mb-3">
                    <label class="form-label text-secondary fs-8 fw-semibold">Company (Optional)</label>
                    <select class="form-select form-select-custom" name="company_id">
                        <option value="">Global (All Companies)</option>
                        @foreach($companies as $c)
                            <option value="{{ $c->id }}">{{ $c->company_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label text-secondary fs-8 fw-semibold">Designation Title</label>
                    <input type="text" class="form-control form-control-custom" name="title" placeholder="e.g. Senior Software Engineer" required>
                </div>

                <div class="mb-3">
                    <label class="form-label text-secondary fs-8 fw-semibold">Designation Code</label>
                    <input type="text" class="form-control form-control-custom" name="designation_code" placeholder="e.g. SSE">
                </div>

                <div class="mb-3">
                    <label class="form-label text-secondary fs-8 fw-semibold">Description</label>
                    <textarea class="form-control form-control-custom" name="description" rows="2" placeholder="Role description or requirements"></textarea>
                </div>

                <button type="submit" class="btn btn-custom-primary w-100 mt-2">
                    <i class="bi bi-save me-2"></i> Register Designation
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modals -->
@foreach($designations as $d)
    <div class="modal fade" id="editModal{{ $d->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content glass-card p-0" >
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title display-font"><i class="bi bi-pencil-square me-2 text-indigo"></i> Edit Designation</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('designations.update', $d->id) }}" method="POST">
                    @csrf
                    <div class="modal-body text-start">
                        <div class="mb-3">
                            <label class="form-label text-secondary fs-8 fw-semibold">Company</label>
                            <select class="form-select form-select-custom" name="company_id">
                                <option value="">Global (All Companies)</option>
                                @foreach($companies as $c)
                                    <option value="{{ $c->id }}" {{ $d->company_id == $c->id ? 'selected' : '' }}>{{ $c->company_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-secondary fs-8 fw-semibold">Designation Title</label>
                            <input type="text" class="form-control form-control-custom" name="title" value="{{ $d->title }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-secondary fs-8 fw-semibold">Designation Code</label>
                            <input type="text" class="form-control form-control-custom" name="designation_code" value="{{ $d->designation_code }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-secondary fs-8 fw-semibold">Status</label>
                            <select class="form-select form-select-custom" name="status" required>
                                <option value="Active" {{ $d->status === 'Active' ? 'selected' : '' }}>Active</option>
                                <option value="Inactive" {{ $d->status === 'Inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-secondary fs-8 fw-semibold">Description</label>
                            <textarea class="form-control form-control-custom" name="description" rows="2">{{ $d->description }}</textarea>
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
