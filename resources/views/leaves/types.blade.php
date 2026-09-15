@extends('layouts.app')

@section('content')
<div class="row g-4">
    <!-- Main Card Column -->
    <div class="col-lg-8">
        <div class="glass-card">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="m-0 display-font">
                    <i class="bi bi-tag-fill me-2 text-indigo"></i> Leave Categories
                </h5>
                <a href="{{ route('leaves') }}" class="btn btn-custom-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
                </a>
            </div>

            <!-- Categories Table -->
            <div class="table-responsive">
                <table class="table custom-table mb-0">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Paid Leave</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($leaveTypes as $t)
                            <tr>
                                <td class="fw-semibold text-white fs-6">{{ $t->name }}</td>
                                <td>
                                    <span class="badge-status {{ $t->status === 'Active' ? 'badge-online' : 'badge-disabled' }} py-1 px-2.5 fs-8">
                                        {{ $t->status }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge {{ $t->is_paid ? 'bg-success bg-opacity-25 text-success' : 'bg-warning bg-opacity-25 text-warning' }} py-1 px-2.5 fs-8">
                                        {{ $t->is_paid ? 'Paid' : 'Unpaid' }}
                                    </span>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-outline-primary border-0 p-1.5 fs-7" data-bs-toggle="modal" data-bs-target="#editTypeModal{{ $t->id }}">
                                        <i class="bi bi-pencil-fill"></i> Edit
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-secondary py-4">No categories defined. Use the sidebar panel to add one.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Right Side Panel: Add Category -->
    <div class="col-lg-4">
        <div class="glass-card">
            <h5 class="mb-4 display-font">
                <i class="bi bi-plus-circle-fill me-2 text-indigo"></i> Add New Category
            </h5>
            
            <form action="{{ route('leaves.types.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label text-secondary fw-semibold mb-2">Category Name</label>
                    <input type="text" name="name" class="form-control form-control-custom" placeholder="e.g. Annual Leave, Casual Leave..." required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label text-secondary fw-semibold mb-2">Payment Status</label>
                    <select class="form-select form-select-custom" name="is_paid" required>
                        <option value="1">Paid Leave</option>
                        <option value="0">Unpaid / No-Pay Leave</option>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-custom-primary w-100 mt-2 py-2">
                    <i class="bi bi-plus-lg me-1"></i> Add Category
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Edit Type Modals Loop (Placed at bottom to prevent z-index/backdrop trapping) -->
@foreach($leaveTypes as $t)
    <div class="modal fade" id="editTypeModal{{ $t->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content glass-card p-0" >
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title display-font"><i class="bi bi-pencil-square me-2 text-indigo"></i> Edit Leave Category</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('leaves.types.update', $t->id) }}" method="POST">
                    @csrf
                    <div class="modal-body text-start">
                        <div class="mb-3">
                            <label class="form-label text-secondary mb-2">Category Name</label>
                            <input type="text" class="form-control form-control-custom" name="name" value="{{ $t->name }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-secondary mb-2">Payment Status</label>
                            <select class="form-select form-select-custom" name="is_paid" required>
                                <option value="1" {{ $t->is_paid ? 'selected' : '' }}>Paid Leave</option>
                                <option value="0" {{ !$t->is_paid ? 'selected' : '' }}>Unpaid / No-Pay Leave</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-secondary mb-2">Status</label>
                            <select class="form-select form-select-custom" name="status" required>
                                <option value="Active" {{ $t->status === 'Active' ? 'selected' : '' }}>Active</option>
                                <option value="Inactive" {{ $t->status === 'Inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
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
