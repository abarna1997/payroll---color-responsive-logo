@extends('layouts.app')

@section('content')
<div class="glass-card mb-4">
    <div class="d-flex align-items-center justify-content-between">
        <div>
            <h4 class="mb-1 display-font text-indigo"><i class="bi bi-shield-lock-fill me-2"></i> Access Level Matrix</h4>
            <p class="text-secondary mb-0 fs-8">Configure inherited capabilities from L0 (Intern) up to L5 (Director) for each Business Role.</p>
        </div>
        <a href="{{ route('roles') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Back to Roles</a>
    </div>
</div>

<div class="row g-4">
    <!-- Role List Left Panel -->
    <div class="col-xl-3 col-lg-4">
        <div class="glass-card mb-4 mb-lg-0">
            <h6 class="mb-3 display-font"><i class="bi bi-building me-2 text-indigo"></i> Business Roles</h6>
            <div class="list-group list-group-flush" id="roleList" role="tablist">
                @foreach($roles as $role)
                    <button type="button" class="list-group-item list-group-item-action role-tab-btn text-start d-flex align-items-center justify-content-between px-3 py-2 rounded mb-1 {{ $loop->first ? 'active bg-indigo text-white' : 'border-0' }}" 
                            data-role-id="{{ $role->id }}" 
                            data-role-name="{{ $role->name }}">
                        <span><i class="bi bi-folder-fill me-2 text-warning"></i> {{ $role->name }}</span>
                        <i class="bi bi-chevron-right fs-9 opacity-50"></i>
                    </button>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Levels & Permissions Matrix Right Panel -->
    <div class="col-xl-9 col-lg-8">
        <div class="glass-card">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 border-bottom pb-3 mb-4">
                <h5 class="mb-0 display-font fw-bold" id="activeRoleTitle">Role: <span class="text-indigo">{{ $roles->first()->name ?? 'N/A' }}</span></h5>
                <div class="nav nav-pills flex-wrap gap-1" id="levelTabs" role="tablist">
                    @foreach($levels as $level)
                        <button type="button" class="nav-link level-tab-btn px-3 py-1.5 fs-8 {{ $loop->first ? 'active bg-indigo' : 'btn-outline-secondary border' }}" 
                                data-level-id="{{ $level->id }}" 
                                data-level-code="{{ $level->code }}"
                                style="font-weight: 500; border-radius: 8px;">
                            <i class="bi {{ $level->icon ?? 'bi-star' }} me-1"></i> {{ $level->code }} ({{ $level->name }})
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Permission Form -->
            <form id="matrixForm" method="POST">
                @csrf
                <div class="matrix-sections-container" style="max-height: 550px; overflow-y: auto; padding-right: 5px;">
                    @foreach($permissions as $categoryName => $perms)
                        <div class="mb-4 category-group">
                            <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                                <h6 class="display-font text-dark mb-0" style="font-weight: 600; font-size: 0.9rem;">
                                    <i class="bi bi-tags-fill text-indigo me-2"></i> {{ $categoryName }} Permissions
                                </h6>
                                <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 fs-9 select-all-btn">Toggle All</button>
                            </div>
                            <div class="row g-3">
                                @foreach($perms as $perm)
                                    <div class="col-md-6">
                                        <div class="p-2 border rounded bg-light-subtle d-flex align-items-center justify-content-between">
                                            <div>
                                                <div class="fw-semibold text-dark fs-8">{{ $perm->permission_name }}</div>
                                                <div class="text-secondary fs-9" style="max-width: 250px;">{{ $perm->description ?? $perm->permission_key }}</div>
                                            </div>
                                            <div class="form-check form-switch m-0">
                                                <input class="form-check-input permission-checkbox" 
                                                       type="checkbox" 
                                                       name="permissions[{{ $perm->id }}]" 
                                                       value="1" 
                                                       id="perm_{{ $perm->id }}"
                                                       data-perm-id="{{ $perm->id }}">
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="border-top pt-3 mt-4 d-flex align-items-center justify-content-between">
                    <span class="text-secondary fs-9"><i class="bi bi-info-circle me-1"></i> Changes will be inherited automatically by higher access levels.</span>
                    <button type="submit" class="btn btn-indigo btn-sm px-4" id="saveMatrixBtn">
                        <i class="bi bi-save me-1"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentRoleId = "{{ $roles->first()->id ?? '' }}";
    let currentLevelId = "{{ $levels->first()->id ?? '' }}";
    let matrixData = {};

    // Load Matrix on page start
    fetchMatrix();

    // Handle Role Tab Clicks
    document.querySelectorAll('.role-tab-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.role-tab-btn').forEach(b => {
                b.classList.remove('active', 'bg-indigo', 'text-white');
                b.classList.add('border-0');
            });
            this.classList.add('active', 'bg-indigo', 'text-white');
            this.classList.remove('border-0');

            currentRoleId = this.getAttribute('data-role-id');
            document.getElementById('activeRoleTitle').innerHTML = 'Role: <span class="text-indigo">' + this.getAttribute('data-role-name') + '</span>';
            fetchMatrix();
        });
    });

    // Handle Level Tab Clicks via Delegation
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.level-tab-btn');
        if (btn) {
            e.preventDefault();
            document.querySelectorAll('.level-tab-btn').forEach(b => {
                b.classList.remove('active', 'bg-indigo', 'text-white');
                b.classList.add('btn-outline-secondary', 'border');
            });
            btn.classList.add('active', 'bg-indigo', 'text-white');
            btn.classList.remove('btn-outline-secondary', 'border');

            currentLevelId = btn.getAttribute('data-level-id');
            applyMatrixToForm();
            return;
        }

        const selectAllBtn = e.target.closest('.select-all-btn');
        if (selectAllBtn) {
            e.preventDefault();
            const group = selectAllBtn.closest('.category-group');
            if (!group) return;
            
            const checkboxes = group.querySelectorAll('.permission-checkbox');
            let allChecked = true;
            checkboxes.forEach(cb => {
                if (!cb.checked) allChecked = false;
            });
            
            const newState = !allChecked;
            checkboxes.forEach(cb => {
                cb.checked = newState;
            });
        }
    });

    // Fetch Level Matrix for Current Role
    function fetchMatrix() {
        if (!currentRoleId) return;
        const saveBtn = document.getElementById('saveMatrixBtn');
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Loading...';

        fetch(`/access-levels/${currentRoleId}/matrix`)
            .then(res => res.json())
            .then(data => {
                matrixData = data;
                applyMatrixToForm();
                saveBtn.disabled = false;
                saveBtn.innerHTML = '<i class="bi bi-save me-1"></i> Save Changes';
            })
            .catch(err => {
                console.error(err);
                showAlert('danger', 'Failed to retrieve access level matrix.');
                saveBtn.disabled = false;
                saveBtn.innerHTML = '<i class="bi bi-save me-1"></i> Save Changes';
            });
    }

    // Apply active role & level matrix mapping to Checkboxes
    function applyMatrixToForm() {
        const checkMap = matrixData[currentLevelId] || {};
        document.querySelectorAll('.permission-checkbox').forEach(cb => {
            const permId = cb.getAttribute('data-perm-id');
            cb.checked = !!checkMap[permId];
        });
    }

    // Save Matrix changes via AJAX
    document.getElementById('matrixForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const saveBtn = document.getElementById('saveMatrixBtn');
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Saving...';

        const formData = new FormData(this);
        // Build JSON representation
        const permissions = {};
        document.querySelectorAll('.permission-checkbox').forEach(cb => {
            const permId = cb.getAttribute('data-perm-id');
            permissions[permId] = cb.checked ? 1 : 0;
        });

        fetch(`/access-levels/${currentRoleId}/${currentLevelId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
            },
            body: JSON.stringify({ permissions: permissions })
        })
        .then(res => res.json())
        .then(data => {
            showAlert('success', data.message || 'Access level permissions saved successfully!');
            // Update local copy
            if (!matrixData[currentLevelId]) matrixData[currentLevelId] = {};
            matrixData[currentLevelId] = permissions;
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="bi bi-save me-1"></i> Save Changes';
        })
        .catch(err => {
            console.error(err);
            showAlert('danger', 'Failed to save access level permissions.');
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="bi bi-save me-1"></i> Save Changes';
        });
    });

    function showAlert(type, msg) {
        // Quick visual toast or alert notice
        const wrapper = document.createElement('div');
        wrapper.className = `alert alert-${type} alert-dismissible fade show border-0 shadow-sm mb-3 position-fixed bottom-0 end-0 m-4 z-index-modal`;
        wrapper.style.minWidth = '300px';
        wrapper.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="bi ${type === 'success' ? 'bi-check-circle-fill text-success' : 'bi-exclamation-triangle-fill text-danger'} me-2 fs-5"></i>
                <span class="fs-8 fw-semibold">${msg}</span>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        document.body.appendChild(wrapper);
        setTimeout(() => {
            const alert = bootstrap.Alert.getInstance(wrapper);
            if (alert) alert.close();
        }, 3500);
    }
});
</script>
@endsection
