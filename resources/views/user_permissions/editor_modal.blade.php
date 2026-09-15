@php
    $direct = $user->directPermissions->pluck('value', 'permission_key')->toArray();
    $roleRec = \App\Models\Role::where('name', $user->role)->first();
    $rolePermissions = ($roleRec && is_array($roleRec->permissions)) ? $roleRec->permissions : [];

    $inheritedCount = 0;
    $allowedCount = 0;
    $deniedCount = 0;
    $customCount = 0;
    $effectiveCount = 0;

    foreach ($matrix as $group => $items) {
        foreach ($items as $key => $label) {
            $val = $direct[$key] ?? 'Inherit';

            if ($val === 'Allow') {
                $allowedCount++;
                $customCount++;
                $effectiveCount++;
            } elseif ($val === 'Deny') {
                $deniedCount++;
                $customCount++;
            } else {
                $inheritedCount++;
                // Evaluate effective permission of role
                $mappedLegacy = $user->mapToLegacyPermission($key);
                $isAllowedByRole = false;
                if ($mappedLegacy && isset($rolePermissions[$mappedLegacy]) && $rolePermissions[$mappedLegacy]) {
                    $isAllowedByRole = true;
                } elseif (isset($rolePermissions[$key]) && $rolePermissions[$key]) {
                    $isAllowedByRole = true;
                }
                if ($isAllowedByRole) {
                    $effectiveCount++;
                }
            }
        }
    }
@endphp

<div class="modal fade" id="editUserModal{{ $user->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
        <div class="modal-content glass-card p-0" >
            <div class="modal-header border-bottom-0 pb-0">
                <div>
                    <h5 class="modal-title display-font mb-1"><i class="bi bi-sliders me-2 text-indigo"></i> Edit Override Matrix</h5>
                    <span class="text-secondary fs-8.5">Configure direct Allow/Deny overrides for <strong>{{ $user->username }}</strong> (RBAC Base: {{ $user->role }})</span>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('permissions.store', $user->id) }}" method="POST" data-ajax="true" data-loading-msg="Saving permission overrides...">
                @csrf
                <div class="modal-body text-start">
                    
                    <!-- Permission Summary Telemetry -->
                    <div class="row g-2 mb-4 text-center">
                        <div class="col-md-2 col-6">
                            <div class="p-2 border rounded" style="background-color: rgba(255,255,255,0.02); border-color: var(--border-color) !important;">
                                <div class="text-secondary fs-9 uppercase">Inherited</div>
                                <div class="fs-5 fw-bold text-indigo">{{ $inheritedCount }}</div>
                            </div>
                        </div>
                        <div class="col-md-2 col-6">
                            <div class="p-2 border rounded" style="background-color: rgba(255,255,255,0.02); border-color: var(--border-color) !important;">
                                <div class="text-secondary fs-9 uppercase">Allowed</div>
                                <div class="fs-5 fw-bold text-success">+{{ $allowedCount }}</div>
                            </div>
                        </div>
                        <div class="col-md-2 col-6">
                            <div class="p-2 border rounded" style="background-color: rgba(255,255,255,0.02); border-color: var(--border-color) !important;">
                                <div class="text-secondary fs-9 uppercase">Denied</div>
                                <div class="fs-5 fw-bold text-danger">-{{ $deniedCount }}</div>
                            </div>
                        </div>
                        <div class="col-md-2 col-6">
                            <div class="p-2 border rounded" style="background-color: rgba(255,255,255,0.02); border-color: var(--border-color) !important;">
                                <div class="text-secondary fs-9 uppercase">Custom Overrides</div>
                                <div class="fs-5 fw-bold text-warning">{{ $customCount }}</div>
                            </div>
                        </div>
                        <div class="col-md-4 col-12">
                            <div class="p-2 border border-indigo rounded" style="background-color: rgba(99, 102, 241, 0.08);">
                                <div class="text-indigo fs-9 uppercase fw-bold">Effective Permission Scope</div>
                                <div class="fs-5 fw-bold text-dark">{{ $effectiveCount }} Rules Active</div>
                            </div>
                        </div>
                    </div>

                    <!-- Direct Overrides Checklist Grid -->
                    <div class="row g-3">
                        @foreach($matrix as $group => $items)
                            <div class="col-12 mt-4">
                                <h6 class="text-indigo display-font border-bottom pb-2 mb-2">
                                    {{ $group }} Permissions
                                </h6>
                            </div>
                            @foreach($items as $key => $label)
                                @php
                                    $currVal = $direct[$key] ?? 'Inherit';
                                    
                                    // Calculate role base
                                    $mappedLegacy = $user->mapToLegacyPermission($key);
                                    $isAllowedByRole = false;
                                    if ($mappedLegacy && isset($rolePermissions[$mappedLegacy]) && $rolePermissions[$mappedLegacy]) {
                                        $isAllowedByRole = true;
                                    } elseif (isset($rolePermissions[$key]) && $rolePermissions[$key]) {
                                        $isAllowedByRole = true;
                                    }
                                @endphp
                                <div class="col-md-6">
                                    <div class="p-2 border rounded d-flex align-items-center justify-content-between" 
                                         style="border-color: var(--border-color) !important; background-color: rgba(255,255,255,0.01);">
                                        <div>
                                            <span class="fs-8 fw-semibold text-dark d-block">{{ $label }}</span>
                                            <span class="fs-9 text-muted">
                                                Role Base: 
                                                <span class="text-{{ $isAllowedByRole ? 'success' : 'danger' }} fw-bold">
                                                    {{ $isAllowedByRole ? 'Allow' : 'Deny' }}
                                                </span>
                                            </span>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <div class="form-check m-0">
                                                <input class="form-check-input" type="radio" name="permissions[{{ $key }}]" value="Inherit" id="radio_{{ $user->id }}_{{ $key }}_inherit" {{ $currVal === 'Inherit' ? 'checked' : '' }}>
                                                <label class="form-check-label fs-9 text-secondary" for="radio_{{ $user->id }}_{{ $key }}_inherit">Inherit</label>
                                            </div>
                                            <div class="form-check m-0">
                                                <input class="form-check-input text-success" type="radio" name="permissions[{{ $key }}]" value="Allow" id="radio_{{ $user->id }}_{{ $key }}_allow" {{ $currVal === 'Allow' ? 'checked' : '' }}>
                                                <label class="form-check-label fs-9 text-success" for="radio_{{ $user->id }}_{{ $key }}_allow">Allow</label>
                                            </div>
                                            <div class="form-check m-0">
                                                <input class="form-check-input text-danger" type="radio" name="permissions[{{ $key }}]" value="Deny" id="radio_{{ $user->id }}_{{ $key }}_deny" {{ $currVal === 'Deny' ? 'checked' : '' }}>
                                                <label class="form-check-label fs-9 text-danger" for="radio_{{ $user->id }}_{{ $key }}_deny">Deny</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endforeach
                    </div>

                    <div class="mt-4">
                        <label class="form-label text-secondary">Reason for override change</label>
                        <input type="text" class="form-control form-control-custom" name="reason" placeholder="e.g., Temporary payroll access granted" required>
                    </div>

                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-custom-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-custom-primary">Save Overrides</button>
                </div>
            </form>
        </div>
    </div>
</div>
