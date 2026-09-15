@extends('layouts.app')

@section('content')
<div class="row g-4">
    <!-- List of Branches -->
    <div class="col-lg-8">
        <div class="glass-card">
            <h5 class="mb-4 display-font"><i class="bi bi-diagram-3 me-2 text-indigo"></i> Registered Branches</h5>
            <div class="table-responsive">
                <table class="table custom-table">
                    <thead>
                        <tr>
                            <th>Branch Code</th>
                            <th>Branch Name</th>
                            <th>Company</th>
                            <th>Address</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($branches as $b)
                            <tr>
                                <td><span class="badge bg-indigo text-light">{{ $b->branch_code }}</span></td>
                                <td>{{ $b->branch_name }}</td>
                                <td>{{ $b->company->company_name }}</td>
                                <td>{{ $b->address ?? 'N/A' }}</td>
                                <td>
                                    <span class="badge-status {{ $b->status === 'Active' ? 'badge-online' : 'badge-disabled' }}">
                                        {{ $b->status }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <button type="button" class="btn btn-sm btn-outline-primary border-0 me-2" data-bs-toggle="modal" data-bs-target="#editModal{{ $b->id }}">
                                            <i class="bi bi-pencil"></i> Edit
                                        </button>
                                        <form action="{{ route('branches.delete', $b->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this branch?');" class="m-0">
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
                                <td colspan="6" class="text-center text-secondary">No branches registered. Use the form to add one.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Create Branch Form -->
    <div class="col-lg-4">
        <div class="glass-card">
            <h5 class="mb-4 display-font"><i class="bi bi-plus-circle-fill me-2 text-indigo"></i> Add Branch</h5>
            <form action="{{ route('branches.store') }}" method="POST">
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
                    <label class="form-label text-secondary">Branch Name</label>
                    <input type="text" class="form-control form-control-custom" name="branch_name" placeholder="e.g. Prime One - Head Office" required>
                </div>

                <div class="mb-3">
                    <label class="form-label text-secondary">Branch Code</label>
                    <input type="text" class="form-control form-control-custom" name="branch_code" placeholder="e.g. P1-HO" required>
                </div>

                <div class="mb-3">
                    <label class="form-label text-secondary">Address</label>
                    <textarea class="form-control form-control-custom" name="address" rows="3" placeholder="Branch location address..."></textarea>
                </div>

                <button type="submit" class="btn btn-custom-primary w-100 mt-2">
                    <i class="bi bi-save me-2"></i> Register Branch
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modals Loop (Placed at bottom to prevent z-index/backdrop trapping) -->
@foreach($branches as $b)
    <div class="modal fade" id="editModal{{ $b->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content glass-card p-0" >
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title display-font"><i class="bi bi-pencil-square me-2 text-indigo"></i> Edit Branch</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('branches.update', $b->id) }}" method="POST">
                    @csrf
                    <div class="modal-body text-start">
                        <div class="mb-3">
                            <label class="form-label text-secondary">Company</label>
                            <select class="form-select form-select-custom" name="company_id" required>
                                @foreach($companies as $c)
                                    <option value="{{ $c->id }}" {{ $b->company_id == $c->id ? 'selected' : '' }}>{{ $c->company_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-secondary">Branch Name</label>
                            <input type="text" class="form-control form-control-custom" name="branch_name" value="{{ $b->branch_name }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-secondary">Branch Code</label>
                            <input type="text" class="form-control form-control-custom" name="branch_code" value="{{ $b->branch_code }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-secondary">Address</label>
                            <textarea class="form-control form-control-custom" name="address" rows="3">{{ $b->address }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-secondary">Status</label>
                            <select class="form-select form-select-custom" name="status" required>
                                <option value="Active" {{ $b->status === 'Active' ? 'selected' : '' }}>Active</option>
                                <option value="Inactive" {{ $b->status === 'Inactive' ? 'selected' : '' }}>Inactive</option>
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
