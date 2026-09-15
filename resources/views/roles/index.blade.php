@extends('layouts.app')

@section('content')
<div class="glass-card mb-4">
    <div class="d-flex align-items-center justify-content-between">
        <div>
            <h4 class="mb-1 display-font text-primary"><i class="bi bi-shield-lock-fill me-2"></i> Roles & Access Controls</h4>
            <p class="text-secondary mb-0 fs-8">Configure business department roles, sort priorities, and access levels matrix defaults.</p>
        </div>
        <a href="{{ route('access-levels.index') }}" class="btn btn-custom-primary btn-sm px-3">
            <i class="bi bi-grid-3x3-gap-fill me-1"></i> Access Level Matrix Defaults
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- List of Roles -->
    <div class="col-lg-8">
        <div class="glass-card">
            <h5 class="mb-4 display-font"><i class="bi bi-list me-2 text-primary"></i> Configured Roles</h5>
            
            <div class="table-responsive">
                <table class="table custom-table">
                    <thead>
                        <tr>
                            <th style="width: 80px;">Priority</th>
                            <th>Role Details</th>
                            <th>Category</th>
                            <th>System Role</th>
                            <th>Status</th>
                            <th style="width: 100px;" class="text-center">Reorder</th>
                            <th style="width: 120px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($roles as $index => $r)
                            <tr>
                                <td class="text-center fw-bold">
                                    <span class="badge bg-dark border text-secondary">#{{ $r->sort_order }}</span>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark fs-8">{{ $r->name }}</div>
                                    <div class="text-secondary fs-9">slug: <code>{{ $r->getSlugOrGenerated() }}</code></div>
                                    <div class="fs-9 text-muted mt-1" style="max-width: 250px;">
                                        {{ $r->description ?? 'No description provided.' }}
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-primary-light text-primary px-2 py-1 fs-9">{{ $r->category ?? 'General' }}</span>
                                </td>
                                <td>
                                    @if($r->is_system_role)
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle fs-9"><i class="bi bi-shield-fill-check"></i> System Protected</span>
                                    @else
                                        <span class="text-secondary fs-9">Custom</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $r->status === 'Active' ? 'bg-success' : 'bg-danger' }} fs-9">{{ $r->status ?? 'Active' }}</span>
                                </td>
                                <td>
                                    <div class="d-flex justify-content-center gap-1">
                                        <!-- Move Up -->
                                        <form action="{{ route('roles.move-up', $r->id) }}" method="POST" class="m-0" data-ajax="true" data-loading-msg="Reordering…">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-secondary border-0 p-1" {{ $index == 0 ? 'disabled' : '' }} title="Move Up">
                                                <i class="bi bi-arrow-up-circle fs-6"></i>
                                            </button>
                                        </form>
                                        <!-- Move Down -->
                                        <form action="{{ route('roles.move-down', $r->id) }}" method="POST" class="m-0" data-ajax="true" data-loading-msg="Reordering…">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-secondary border-0 p-1" {{ $index == count($roles) - 1 ? 'disabled' : '' }} title="Move Down">
                                                <i class="bi bi-arrow-down-circle fs-6"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-1">
                                        <button type="button" class="btn btn-sm btn-outline-primary border-0" data-bs-toggle="modal" data-bs-target="#editRoleModal{{ $r->id }}">
                                            <i class="bi bi-pencil"></i> Edit
                                        </button>
                                        @if($r->name !== 'Super Administrator' && !$r->is_system_role)
                                            <form action="{{ route('roles.delete', $r->id) }}" method="POST" class="m-0" data-ajax="true" data-confirm="Are you sure you want to delete this role?">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-danger border-0">
                                                    <i class="bi bi-trash"></i> Delete
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>

                            <!-- Edit Role Modal -->
                            <div class="modal fade" id="editRoleModal{{ $r->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content glass-card p-0" >
                                        <div class="modal-header border-bottom-0 pb-0">
                                            <h5 class="modal-title display-font"><i class="bi bi-pencil me-2 text-primary"></i> Edit Role: {{ $r->name }}</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form action="{{ route('roles.update', $r->id) }}" method="POST" data-ajax="true" data-loading-msg="Saving changes…">
                                            @csrf
                                            <div class="modal-body text-start fs-8">
                                                <div class="mb-3">
                                                    <label class="form-label text-secondary">Role Name</label>
                                                    <input type="text" class="form-control form-control-custom" name="name" value="{{ $r->name }}" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label text-secondary">Role Slug</label>
                                                    <input type="text" class="form-control form-control-custom" name="slug" value="{{ $r->slug }}">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label text-secondary">Category Group</label>
                                                    <input type="text" class="form-control form-control-custom" name="category" value="{{ $r->category }}" placeholder="e.g. HR, IT, Finance">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label text-secondary">Status</label>
                                                    <select class="form-select form-control-custom text-white" name="status" style="background-color: #1a1a2e;">
                                                        <option value="Active" {{ $r->status === 'Active' ? 'selected' : '' }}>Active</option>
                                                        <option value="Inactive" {{ $r->status === 'Inactive' ? 'selected' : '' }}>Inactive</option>
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label text-secondary">Description</label>
                                                    <textarea class="form-control form-control-custom" name="description" rows="2">{{ $r->description }}</textarea>
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
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-secondary py-4">No custom roles configured.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Create Role Form -->
    <div class="col-lg-4">
        <div class="glass-card">
            <h5 class="mb-4 display-font"><i class="bi bi-plus-circle-fill me-2 text-primary"></i> Create Custom Role</h5>
            <form action="{{ route('roles.store') }}" method="POST" data-ajax="true" data-loading-msg="Creating role…">
                @csrf
                <div class="mb-3">
                    <label class="form-label text-secondary fs-8">Role Name</label>
                    <input type="text" class="form-control form-control-custom" name="name" placeholder="e.g. Supervisor" required>
                </div>
                <div class="mb-3">
                    <label class="form-label text-secondary fs-8">Role Slug</label>
                    <input type="text" class="form-control form-control-custom" name="slug" placeholder="e.g. supervisor">
                </div>
                <div class="mb-3">
                    <label class="form-label text-secondary fs-8">Category Group</label>
                    <input type="text" class="form-control form-control-custom" name="category" placeholder="e.g. Operations, Payroll">
                </div>
                <div class="mb-3">
                    <label class="form-label text-secondary fs-8">Description</label>
                    <textarea class="form-control form-control-custom" name="description" placeholder="Describe scope of responsibility" rows="3"></textarea>
                </div>

                <button type="submit" class="btn btn-custom-primary w-100 mt-3">
                    <i class="bi bi-save me-2"></i> Save Custom Role
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
