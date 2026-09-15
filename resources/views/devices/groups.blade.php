@extends('layouts.app')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h4 class="fw-bold m-0 text-dark display-font">
            <i class="bi bi-collection-fill me-2 text-primary"></i> Device Groups
        </h4>
        <p class="text-secondary fs-7 m-0 mt-1">Organize and manage biometric hardware in logical clusters.</p>
    </div>
</div>

<div class="glass-card mb-4 p-3 d-flex justify-content-end">
    <button type="button" class="btn btn-custom-primary px-4 shadow-sm border-0" data-bs-toggle="offcanvas" data-bs-target="#createGroupDrawer">
        <i class="bi bi-plus-lg me-1"></i> Add Group
    </button>
</div>

<div class="glass-card p-4">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h5 class="m-0 display-font text-dark"><i class="bi bi-hdd-network me-2 text-primary"></i> Configured Groups</h5>
        <span class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill fs-7 fw-semibold border border-primary border-opacity-25">
            Showing {{ $groups->count() }} Records
        </span>
    </div>

    @if($groups->count() > 0)
        <div class="table-responsive">
            <table class="table custom-table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Group Name</th>
                        <th>Location</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($groups as $g)
                        <tr>
                            <td>
                                <div class="fw-semibold text-dark fs-7.5">{{ $g->name }}</div>
                            </td>
                            <td>
                                <span class="text-secondary fs-8">{{ $g->location ?: 'Not specified' }}</span>
                            </td>
                            <td>
                                <span class="text-secondary fs-8">{{ Str::limit($g->description, 50) ?: 'N/A' }}</span>
                            </td>
                            <td>
                                @php
                                    $badgeClass = $g->status === 'Active' ? 'bg-success-subtle text-success border-success' : 'bg-secondary-subtle text-secondary border-secondary';
                                @endphp
                                <span class="badge {{ $badgeClass }} border border-opacity-25">{{ $g->status }}</span>
                            </td>
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-custom-secondary px-3 py-1 fs-8 mb-1" data-bs-toggle="modal" data-bs-target="#editGroupModal{{ $g->id }}">
                                    <i class="bi bi-pencil me-1"></i> Edit
                                </button>
                                <form action="{{ route('devices.groups.delete', $g->id) }}" method="POST" class="d-inline-block mb-1" onsubmit="return confirm('Delete this device group?');">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-danger px-3 py-1 fs-8 border-0 shadow-sm rounded">
                                        <i class="bi bi-trash"></i>
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
                <i class="bi bi-collection fs-1"></i>
            </div>
            <h5 class="fw-bold text-dark mb-2">No Device Groups Found</h5>
            <p class="text-secondary fs-7 mx-auto mb-4" style="max-width: 480px;">
                Group your biometric devices by location, floor, or function for easier bulk management.
            </p>
            <button type="button" class="btn btn-custom-primary px-4 py-2 border-0 shadow-sm" data-bs-toggle="offcanvas" data-bs-target="#createGroupDrawer">
                <i class="bi bi-plus-lg me-1"></i> Add Group
            </button>
        </div>
    @endif
</div>

<!-- Offcanvas Drawer -->
<div class="offcanvas offcanvas-end bg-white shadow p-0" tabindex="-1" id="createGroupDrawer" style="width: 400px;">
    <div class="offcanvas-header border-bottom p-3 bg-light">
        <h5 class="offcanvas-title text-dark display-font m-0 fw-bold">
            <i class="bi bi-plus-circle-fill me-2 text-primary"></i> Create Group
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-4 text-start">
        <form action="{{ route('devices.groups.store') }}" method="POST">
            @csrf
            
            <div class="mb-3">
                <label class="form-label text-secondary fs-8 fw-semibold">Group Name *</label>
                <input type="text" class="form-control form-control-custom" name="name" required placeholder="e.g. Main Entrance">
            </div>

            <div class="mb-3">
                <label class="form-label text-secondary fs-8 fw-semibold">Location</label>
                <input type="text" class="form-control form-control-custom" name="location" placeholder="e.g. Building A">
            </div>
            
            <div class="mb-3">
                <label class="form-label text-secondary fs-8 fw-semibold">Description</label>
                <textarea class="form-control form-control-custom" name="description" rows="3"></textarea>
            </div>

            <div class="d-flex align-items-center justify-content-end gap-2 mt-4 pt-3 border-top">
                <button type="button" class="btn btn-custom-secondary px-4" data-bs-dismiss="offcanvas">Cancel</button>
                <button type="submit" class="btn btn-custom-primary border-0 shadow-sm px-4 fw-bold">
                    <i class="bi bi-save me-1"></i> Save Group
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modals -->
@foreach($groups as $g)
<div class="modal fade" id="editGroupModal{{ $g->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-white shadow-lg border-0">
            <div class="modal-header border-bottom p-3 bg-light">
                <h5 class="modal-title display-font m-0 text-dark"><i class="bi bi-pencil-square me-2 text-primary"></i>Edit Group</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 text-start">
                <form action="{{ route('devices.groups.update', $g->id) }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label class="form-label text-secondary fs-8 fw-semibold">Group Name *</label>
                        <input type="text" class="form-control form-control-custom" name="name" value="{{ $g->name }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-secondary fs-8 fw-semibold">Location</label>
                        <input type="text" class="form-control form-control-custom" name="location" value="{{ $g->location }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-secondary fs-8 fw-semibold">Status *</label>
                        <select name="status" class="form-select form-select-custom" required>
                            <option value="Active" {{ $g->status === 'Active' ? 'selected' : '' }}>Active</option>
                            <option value="Inactive" {{ $g->status === 'Inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                        <button type="button" class="btn btn-custom-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-custom-primary px-4 fw-semibold"><i class="bi bi-save me-1"></i> Update Group</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endforeach
@endsection
