@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="mb-0 display-font"><i class="bi bi-shield-lock me-2 text-indigo"></i> Individual User Permissions Override</h5>
            <div class="d-flex gap-2">
                <a href="{{ route('permissions.templates') }}" class="btn btn-custom-secondary btn-sm">
                    <i class="bi bi-file-earmark-medical me-1"></i> Templates Registry
                </a>
                <a href="{{ route('permissions.history') }}" class="btn btn-custom-secondary btn-sm">
                    <i class="bi bi-clock-history me-1"></i> Audit Trail
                </a>
                <button type="button" class="btn btn-custom-primary btn-sm" data-bs-toggle="modal" data-bs-target="#bulkAssignModal">
                    <i class="bi bi-collection-play me-1"></i> Bulk Assignment
                </button>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Filter Panel -->
    <div class="col-12 mb-4">
        <div class="glass-card p-3">
            <form action="{{ route('permissions.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-3">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0 text-secondary"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control form-control-custom border-start-0" name="search" value="{{ request('search') }}" placeholder="Search username, email...">
                    </div>
                </div>
                <div class="col-md-2">
                    <select class="form-select form-select-custom" name="role">
                        <option value="">-- All Roles --</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->name }}" {{ request('role') == $role->name ? 'selected' : '' }}>{{ $role->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select form-select-custom" name="department_id">
                        <option value="">-- All Departments --</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->department_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select form-select-custom" name="status">
                        <option value="">-- All Statuses --</option>
                        <option value="Active" {{ request('status') == 'Active' ? 'selected' : '' }}>Active</option>
                        <option value="Inactive" {{ request('status') == 'Inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-custom-primary w-100"><i class="bi bi-funnel me-1"></i> Filter</button>
                    <a href="{{ route('permissions.index') }}" class="btn btn-custom-secondary w-100"><i class="bi bi-arrow-counterclockwise"></i> Reset</a>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="row">
    <!-- Users overrides table -->
    <div class="col-12">
        <div class="glass-card">
            <div class="table-responsive">
                <table class="table custom-table">
                    <thead>
                        <tr>
                            <th>User (Employee)</th>
                            <th>Department</th>
                            <th>Role (RBAC Base)</th>
                            <th class="text-center">Overrides count</th>
                            <th class="text-center">Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $u)
                            @php
                                $allowed = $u->directPermissions->where('value', 'Allow')->count();
                                $denied = $u->directPermissions->where('value', 'Deny')->count();
                            @endphp
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="avatar-circle bg-indigo-light text-indigo fw-bold d-flex align-items-center justify-content-center" style="width:38px; height:38px; border-radius:50%;">
                                            {{ strtoupper(substr($u->username, 0, 2)) }}
                                        </div>
                                        <div>
                                            <div class="fw-semibold text-dark">{{ $u->username }}</div>
                                            <div class="text-secondary fs-8">{{ $u->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-secondary">{{ $u->employee->department->department_name ?? 'N/A' }}</span>
                                    <div class="fs-9 text-muted">{{ $u->employee->branch->branch_name ?? '' }}</div>
                                </td>
                                <td>
                                    <span class="badge bg-indigo-light text-indigo">{{ $u->role }}</span>
                                </td>
                                <td class="text-center">
                                    @if($allowed > 0 || $denied > 0)
                                        <span class="badge bg-success bg-opacity-15 text-success me-1">+{{ $allowed }} Allow</span>
                                        <span class="badge bg-danger bg-opacity-15 text-danger">-{{ $denied }} Deny</span>
                                    @else
                                        <span class="text-muted fs-8.5">Inherited (No Overrides)</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge-status {{ $u->status === 'Active' ? 'badge-online' : 'badge-disabled' }}">
                                        {{ $u->status }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-1">
                                        <button type="button" class="btn btn-sm btn-outline-primary border-0" data-bs-toggle="modal" data-bs-target="#editUserModal{{ $u->id }}">
                                            <i class="bi bi-sliders me-1"></i> Overrides
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary border-0" data-bs-toggle="modal" data-bs-target="#applyTemplateModal{{ $u->id }}">
                                            <i class="bi bi-file-earmark-arrow-down me-1"></i> Template
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <!-- Edit Overrides Modal -->
                            @include('user_permissions.editor_modal', ['user' => $u, 'matrix' => $matrix])

                            <!-- Apply Template Modal -->
                            <div class="modal fade" id="applyTemplateModal{{ $u->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content glass-card p-0" >
                                        <div class="modal-header border-bottom-0 pb-0">
                                            <h5 class="modal-title display-font"><i class="bi bi-file-earmark-arrow-down-fill text-indigo me-2"></i> Apply Template to {{ $u->username }}</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form action="{{ route('permissions.apply-template', $u->id) }}" method="POST" data-ajax="true" data-loading-msg="Applying template...">
                                            @csrf
                                            <div class="modal-body text-start">
                                                <p class="text-secondary fs-8">Applying a template will clear all current direct override rules for this user and map the template's Allow/Deny flags.</p>
                                                <div class="mb-3">
                                                    <label class="form-label text-secondary">Choose Template</label>
                                                    <select class="form-select form-select-custom" name="template_id" required>
                                                        <option value="">-- Choose Template --</option>
                                                        @foreach($templates as $t)
                                                            <option value="{{ $t->id }}">{{ $t->name }} ({{ $t->description }})</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="modal-footer border-top-0 pt-0">
                                                <button type="button" class="btn btn-custom-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-custom-primary">Apply Template</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-secondary py-4">No users found matching your filters.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-3 border-top" style="border-color: var(--border-color) !important;">
                {{ $users->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Bulk Assignment Modal -->
<div class="modal fade" id="bulkAssignModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content glass-card p-0" >
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title display-font"><i class="bi bi-collection-play-fill text-indigo me-2"></i> Bulk Overrides Assignment</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('permissions.bulk-assign') }}" method="POST" data-ajax="true" data-loading-msg="Applying bulk settings...">
                @csrf
                <div class="modal-body text-start">
                    <p class="text-secondary fs-8">Specify targets using bulk department/branch selectors, or leave fields blank to update all users matching the criteria.</p>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-secondary">Company</label>
                            <select class="form-select form-select-custom" name="company_id">
                                <option value="">-- All Companies --</option>
                                @foreach($companies as $c)
                                    <option value="{{ $c->id }}">{{ $c->company_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary">Branch</label>
                            <select class="form-select form-select-custom" name="branch_id">
                                <option value="">-- All Branches --</option>
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}">{{ $b->branch_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary">Department</label>
                            <select class="form-select form-select-custom" name="department_id">
                                <option value="">-- All Departments --</option>
                                @foreach($departments as $d)
                                    <option value="{{ $d->id }}">{{ $d->department_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary">Designation</label>
                            <input type="text" class="form-control form-control-custom" name="designation" placeholder="e.g. Software Engineer">
                        </div>
                    </div>

                    <hr class="my-4" style="background-color: var(--border-color);">

                    <div class="mb-3">
                        <label class="form-label text-secondary">Bulk Mode</label>
                        <select class="form-select form-select-custom" name="assignment_type" id="bulk_assignment_type" onchange="toggleBulkSections()">
                            <option value="template">Apply Selected Template</option>
                            <option value="direct">Apply Direct Overrides</option>
                        </select>
                    </div>

                    <div class="mb-3" id="bulk_template_section">
                        <label class="form-label text-secondary">Permissions Template</label>
                        <select class="form-select form-select-custom" name="template_id">
                            <option value="">-- Choose Template --</option>
                            @foreach($templates as $t)
                                <option value="{{ $t->id }}">{{ $t->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div id="bulk_direct_section" style="display:none;">
                        <h6 class="text-indigo display-font mb-3 mt-4">Assign Direct Overrides Matrix</h6>
                        <div class="row g-2 overflow-auto" style="max-height: 350px;">
                            @foreach($matrix as $group => $items)
                                <div class="col-12">
                                    <div class="p-2 fw-bold text-dark rounded" style="background-color: rgba(255, 255, 255, 0.04);">
                                        {{ $group }}
                                    </div>
                                </div>
                                @foreach($items as $key => $label)
                                    <div class="col-md-6">
                                        <div class="p-2 border rounded" style="border-color: var(--border-color) !important; background-color: rgba(0,0,0,0.05);">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <span class="fs-8 text-secondary">{{ $label }}</span>
                                                <div class="d-flex gap-2">
                                                    <div class="form-check m-0">
                                                        <input class="form-check-input" type="radio" name="permissions[{{ $key }}]" value="Inherit" checked>
                                                        <label class="form-check-label fs-9 text-secondary">Inherit</label>
                                                    </div>
                                                    <div class="form-check m-0">
                                                        <input class="form-check-input text-success" type="radio" name="permissions[{{ $key }}]" value="Allow">
                                                        <label class="form-check-label fs-9 text-success">Allow</label>
                                                    </div>
                                                    <div class="form-check m-0">
                                                        <input class="form-check-input text-danger" type="radio" name="permissions[{{ $key }}]" value="Deny">
                                                        <label class="form-check-label fs-9 text-danger">Deny</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @endforeach
                        </div>
                    </div>

                    <div class="mt-3">
                        <label class="form-label text-secondary">IP/Audit Reason</label>
                        <input type="text" class="form-control form-control-custom" name="reason" placeholder="Audit Trail justification note" required>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-custom-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-custom-primary">Apply Settings</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleBulkSections() {
    var type = document.getElementById('bulk_assignment_type').value;
    if (type === 'template') {
        document.getElementById('bulk_template_section').style.display = 'block';
        document.getElementById('bulk_direct_section').style.display = 'none';
    } else {
        document.getElementById('bulk_template_section').style.display = 'none';
        document.getElementById('bulk_direct_section').style.display = 'block';
    }
}
</script>
@endsection
