@extends('layouts.app')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h4 class="fw-bold m-0 text-dark display-font">
            <i class="bi bi-gear-fill me-2 text-primary"></i> Device Settings
        </h4>
        <p class="text-secondary fs-7 m-0 mt-1">Configure global parameters and sync intervals for biometric hardware.</p>
    </div>
</div>

<div class="glass-card mb-4 p-3 d-flex justify-content-end">
    <button type="button" class="btn btn-custom-primary px-4 shadow-sm border-0" data-bs-toggle="offcanvas" data-bs-target="#createSettingDrawer">
        <i class="bi bi-plus-lg me-1"></i> Add Parameter
    </button>
</div>

<div class="glass-card p-4">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h5 class="m-0 display-font text-dark"><i class="bi bi-sliders me-2 text-primary"></i> Global Settings</h5>
        <span class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill fs-7 fw-semibold border border-primary border-opacity-25">
            Showing {{ $settings->count() }} Configs
        </span>
    </div>

    @if($settings->count() > 0)
        <div class="table-responsive">
            <table class="table custom-table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Configuration Key</th>
                        <th>Value</th>
                        <th>Data Type</th>
                        <th>Description</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($settings as $s)
                        <tr>
                            <td>
                                <div class="fw-bold text-dark fs-7.5 font-monospace">{{ $s->key }}</div>
                            </td>
                            <td>
                                <div class="bg-light p-2 rounded border font-monospace text-secondary fs-8 d-inline-block">{{ $s->value ?: 'NULL' }}</div>
                            </td>
                            <td>
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary border-opacity-25">{{ $s->type }}</span>
                            </td>
                            <td>
                                <span class="text-secondary fs-8">{{ Str::limit($s->description, 50) ?: 'N/A' }}</span>
                            </td>
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-custom-secondary px-3 py-1 fs-8 mb-1" data-bs-toggle="modal" data-bs-target="#editSettingModal{{ $s->id }}">
                                    <i class="bi bi-pencil me-1"></i> Edit
                                </button>
                                <form action="{{ route('devices.settings.delete', $s->id) }}" method="POST" class="d-inline-block mb-1" onsubmit="return confirm('Delete this setting parameter?');">
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
                <i class="bi bi-gear fs-1"></i>
            </div>
            <h5 class="fw-bold text-dark mb-2">No Settings Configured</h5>
            <p class="text-secondary fs-7 mx-auto mb-4" style="max-width: 480px;">
                Add configuration parameters like sync intervals, API keys, or timeout limits.
            </p>
            <button type="button" class="btn btn-custom-primary px-4 py-2 border-0 shadow-sm" data-bs-toggle="offcanvas" data-bs-target="#createSettingDrawer">
                <i class="bi bi-plus-lg me-1"></i> Add Parameter
            </button>
        </div>
    @endif
</div>

<!-- Offcanvas Drawer -->
<div class="offcanvas offcanvas-end bg-white shadow p-0" tabindex="-1" id="createSettingDrawer" style="width: 400px;">
    <div class="offcanvas-header border-bottom p-3 bg-light">
        <h5 class="offcanvas-title text-dark display-font m-0 fw-bold">
            <i class="bi bi-plus-circle-fill me-2 text-primary"></i> Create Parameter
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-4 text-start">
        <form action="{{ route('devices.settings.store') }}" method="POST">
            @csrf
            
            <div class="mb-3">
                <label class="form-label text-secondary fs-8 fw-semibold">Configuration Key *</label>
                <input type="text" class="form-control form-control-custom font-monospace" name="key" required placeholder="e.g. SYNC_INTERVAL">
            </div>

            <div class="mb-3">
                <label class="form-label text-secondary fs-8 fw-semibold">Value *</label>
                <input type="text" class="form-control form-control-custom" name="value" required placeholder="e.g. 60">
            </div>

            <div class="mb-3">
                <label class="form-label text-secondary fs-8 fw-semibold">Data Type *</label>
                <select name="type" class="form-select form-select-custom" required>
                    <option value="string">String</option>
                    <option value="integer">Integer</option>
                    <option value="boolean">Boolean</option>
                    <option value="json">JSON</option>
                </select>
            </div>
            
            <div class="mb-3">
                <label class="form-label text-secondary fs-8 fw-semibold">Description</label>
                <textarea class="form-control form-control-custom" name="description" rows="3"></textarea>
            </div>

            <div class="d-flex align-items-center justify-content-end gap-2 mt-4 pt-3 border-top">
                <button type="button" class="btn btn-custom-secondary px-4" data-bs-dismiss="offcanvas">Cancel</button>
                <button type="submit" class="btn btn-custom-primary border-0 shadow-sm px-4 fw-bold">
                    <i class="bi bi-save me-1"></i> Save
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modals -->
@foreach($settings as $s)
<div class="modal fade" id="editSettingModal{{ $s->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-white shadow-lg border-0">
            <div class="modal-header border-bottom p-3 bg-light">
                <h5 class="modal-title display-font m-0 text-dark"><i class="bi bi-pencil-square me-2 text-primary"></i>Edit Parameter</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 text-start">
                <form action="{{ route('devices.settings.update', $s->id) }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label class="form-label text-secondary fs-8 fw-semibold">Configuration Key</label>
                        <input type="text" class="form-control form-control-custom bg-light font-monospace" value="{{ $s->key }}" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-secondary fs-8 fw-semibold">Value *</label>
                        <input type="text" class="form-control form-control-custom" name="value" value="{{ $s->value }}" required>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                        <button type="button" class="btn btn-custom-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-custom-primary px-4 fw-semibold"><i class="bi bi-save me-1"></i> Update Value</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endforeach
@endsection
