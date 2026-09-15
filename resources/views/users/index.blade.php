@extends('layouts.app')

@section('content')
<div class="row g-4">
    <!-- List of Users -->
    <div class="col-lg-8">
        <div class="glass-card">
            <h5 class="mb-4 display-font"><i class="bi bi-people-fill me-2 text-indigo"></i> Console Users</h5>
            <div class="table-responsive">
                <table class="table custom-table">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Email Address</th>
                            <th>Role / Level</th>
                            <th>Template</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $u)
                            <tr>
                                <td class="fw-semibold">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle bg-indigo text-light d-flex align-items-center justify-content-center fw-bold" style="width: 28px; height: 28px; font-size: 0.75rem;">
                                            {{ strtoupper(substr($u->username, 0, 2)) }}
                                        </div>
                                        <span>{{ $u->username }}</span>
                                    </div>
                                </td>
                                <td>{{ $u->email }}</td>
                                <td>
                                    <span class="badge-status {{ $u->role === 'Super Administrator' ? 'badge-online' : ($u->role === 'HR Administrator' ? 'badge-pending' : 'badge-disabled') }}">
                                        {{ $u->role }}
                                    </span>
                                    @if($u->accessLevel)
                                        <div class="fs-9 text-indigo mt-1"><i class="bi bi-shield-lock-fill"></i> {{ $u->accessLevel->getLabel() }}</div>
                                    @endif
                                </td>
                                <td>
                                    @if($u->template)
                                        <span class="badge bg-indigo-light text-indigo px-2 py-1 fs-9">{{ $u->template->name }}</span>
                                    @else
                                        <span class="text-secondary fs-9">Default</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $u->status === 'Active' ? 'bg-success' : 'bg-danger' }} fs-9">{{ $u->status ?? 'Active' }}</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <button type="button" class="btn btn-sm btn-outline-primary border-0 me-2" data-bs-toggle="modal" data-bs-target="#editModal{{ $u->id }}">
                                            <i class="bi bi-pencil"></i> Edit
                                        </button>
                                        @if(Auth::id() !== $u->id && $u->username !== 'Prime1-admin')
                                            <form action="{{ route('users.delete', $u->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this user?');" class="m-0">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-danger border-0">
                                                    <i class="bi bi-trash"></i> Delete
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-secondary fs-8 italic">(Protected)</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Create User Form -->
    <div class="col-lg-4">
        <div class="glass-card">
            <h5 class="mb-4 display-font"><i class="bi bi-person-plus-fill me-2 text-indigo"></i> Register Console User</h5>
            <form action="{{ route('users.store') }}" method="POST">
                @csrf
                
                <div class="mb-3">
                    <label class="form-label text-secondary fs-8">Username</label>
                    <input type="text" class="form-control form-control-custom" name="username" placeholder="e.g. mishal2002" required>
                </div>

                <div class="mb-3">
                    <label class="form-label text-secondary fs-8">Email Address</label>
                    <input type="email" class="form-control form-control-custom" name="email" placeholder="e.g. admin@primeone.lk" required>
                </div>

                <div class="mb-3">
                    <label class="form-label text-secondary fs-8">Console Role</label>
                    <select class="form-select form-select-custom" name="role" required>
                        <option value="">Select Role</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->name }}">{{ $role->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label text-secondary fs-8">Access Level</label>
                    <select class="form-select form-select-custom" name="access_level_id">
                        <option value="">Select Access Level (Optional)</option>
                        @foreach($accessLevels as $al)
                            <option value="{{ $al->id }}">{{ $al->getLabel() }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label text-secondary fs-8">Permission Template</label>
                    <select class="form-select form-select-custom" name="template_id">
                        <option value="">Select Template (Optional)</option>
                        @foreach($templates as $tpl)
                            <option value="{{ $tpl->id }}">{{ $tpl->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label text-secondary fs-8">Password</label>
                    <input type="password" class="form-control form-control-custom" name="password" placeholder="Password (Min. 6 chars)" required>
                </div>

                <button type="submit" class="btn btn-custom-primary w-100 mt-2">
                    <i class="bi bi-save me-2"></i> Register User
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modals Loop -->
@foreach($users as $u)
    <div class="modal fade" id="editModal{{ $u->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content glass-card p-0" >
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title display-font"><i class="bi bi-pencil-square me-2 text-indigo"></i> Edit User & RBAC Role</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('users.update', $u->id) }}" method="POST">
                    @csrf
                    <div class="modal-body text-start fs-8">
                        <div class="mb-3">
                            <label class="form-label text-secondary">Username</label>
                            <input type="text" class="form-control form-control-custom" name="username" value="{{ $u->username }}" required {{ $u->username === 'Prime1-admin' ? 'disabled' : '' }}>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-secondary">Email Address</label>
                            <input type="email" class="form-control form-control-custom" name="email" value="{{ $u->email }}" required {{ $u->username === 'Prime1-admin' ? 'disabled' : '' }}>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-secondary">Console Role</label>
                            <select class="form-select form-select-custom" name="role" required {{ $u->username === 'Prime1-admin' ? 'disabled' : '' }}>
                                @foreach($roles as $role)
                                    <option value="{{ $role->name }}" {{ $u->role === $role->name ? 'selected' : '' }}>{{ $role->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-secondary">Access Level</label>
                            <select class="form-select form-select-custom" name="access_level_id" {{ $u->username === 'Prime1-admin' ? 'disabled' : '' }}>
                                <option value="">Select Access Level (Optional)</option>
                                @foreach($accessLevels as $al)
                                    <option value="{{ $al->id }}" {{ $u->access_level_id == $al->id ? 'selected' : '' }}>{{ $al->getLabel() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-secondary">Permission Template</label>
                            <select class="form-select form-select-custom" name="template_id" {{ $u->username === 'Prime1-admin' ? 'disabled' : '' }}>
                                <option value="">Select Template (Optional)</option>
                                @foreach($templates as $tpl)
                                    <option value="{{ $tpl->id }}" {{ $u->template_id == $tpl->id ? 'selected' : '' }}>{{ $tpl->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-secondary">Status</label>
                            <select class="form-select form-select-custom" name="status" {{ $u->username === 'Prime1-admin' ? 'disabled' : '' }}>
                                <option value="Active" {{ $u->status === 'Active' ? 'selected' : '' }}>Active</option>
                                <option value="Inactive" {{ $u->status === 'Inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-secondary">New Password (Leave blank to keep current)</label>
                            <input type="password" class="form-control form-control-custom" name="password" placeholder="New Password (optional)">
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
