@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="mb-0 display-font"><i class="bi bi-file-earmark-medical-fill me-2 text-indigo"></i> Permission Templates Registry</h5>
            <a href="{{ route('permissions.index') }}" class="btn btn-custom-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Back to Overrides
            </a>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Templates List -->
    <div class="col-lg-6">
        <div class="glass-card">
            <h5 class="mb-4 display-font"><i class="bi bi-list-stars me-2 text-indigo"></i> Configured Templates</h5>
            <div class="table-responsive">
                <table class="table custom-table">
                    <thead>
                        <tr>
                            <th>Template Name</th>
                            <th>Description</th>
                            <th class="text-center">Assigned Flags</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($templates as $t)
                            @php
                                $assigns = count($t->permissions ?? []);
                            @endphp
                            <tr>
                                <td>
                                    <span class="fw-semibold text-dark">{{ $t->name }}</span>
                                </td>
                                <td class="fs-8 text-secondary" style="max-width: 250px;">
                                    {{ $t->description ?? 'No description provided.' }}
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-indigo-light text-indigo px-2 py-1 fs-9">{{ $assigns }} Overrides</span>
                                </td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-1">
                                        <button type="button" class="btn btn-sm btn-outline-primary border-0" onclick="loadTemplateToEditor({{ json_encode($t) }})">
                                            <i class="bi bi-pencil"></i> Edit
                                        </button>
                                        <form action="{{ route('permissions.templates.delete', $t->id) }}" method="POST" data-ajax="true" data-confirm="Are you sure you want to delete this template?" class="m-0">
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
                                <td colspan="4" class="text-center text-secondary py-4">No permission templates created yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Create / Edit Template Form -->
    <div class="col-lg-6">
        <div class="glass-card">
            <h5 class="mb-4 display-font" id="form-title"><i class="bi bi-plus-circle-fill me-2 text-indigo"></i> Create Permission Template</h5>
            <form action="{{ route('permissions.templates.store') }}" method="POST" data-ajax="true" data-loading-msg="Saving template...">
                @csrf
                <div class="mb-3">
                    <label class="form-label text-secondary">Template Name</label>
                    <input type="text" class="form-control form-control-custom" name="name" id="template_name" placeholder="e.g. Finance Officer" required>
                </div>
                <div class="mb-3">
                    <label class="form-label text-secondary">Description</label>
                    <textarea class="form-control form-control-custom" name="description" id="template_description" placeholder="Preconfigured permissions template mapping" rows="2"></textarea>
                </div>

                <h6 class="text-indigo display-font mb-3 mt-4">Preconfigured Matrix Rules</h6>
                <div class="row g-2 overflow-auto" style="max-height: 450px;">
                    @foreach($matrix as $group => $items)
                        <div class="col-12">
                            <div class="p-2 fw-bold text-dark rounded" style="background-color: rgba(255, 255, 255, 0.04);">
                                {{ $group }}
                            </div>
                        </div>
                        @foreach($items as $key => $label)
                            <div class="col-md-12">
                                <div class="p-2 border rounded" style="border-color: var(--border-color) !important; background-color: rgba(0,0,0,0.02);">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <span class="fs-8 text-secondary">{{ $label }}</span>
                                        <div class="d-flex gap-2">
                                            <div class="form-check m-0">
                                                <input class="form-check-input radio-inherit" type="radio" name="permissions[{{ $key }}]" value="Inherit" id="tmpl_{{ $key }}_inherit" checked>
                                                <label class="form-check-label fs-9 text-secondary" for="tmpl_{{ $key }}_inherit">Inherit</label>
                                            </div>
                                            <div class="form-check m-0">
                                                <input class="form-check-input radio-allow text-success" type="radio" name="permissions[{{ $key }}]" value="Allow" id="tmpl_{{ $key }}_allow">
                                                <label class="form-check-label fs-9 text-success" for="tmpl_{{ $key }}_allow">Allow</label>
                                            </div>
                                            <div class="form-check m-0">
                                                <input class="form-check-input radio-deny text-danger" type="radio" name="permissions[{{ $key }}]" value="Deny" id="tmpl_{{ $key }}_deny">
                                                <label class="form-check-label fs-9 text-danger" for="tmpl_{{ $key }}_deny">Deny</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endforeach
                </div>

                <button type="submit" class="btn btn-custom-primary w-100 mt-4">
                    <i class="bi bi-save me-2"></i> Save Template
                </button>
            </form>
        </div>
    </div>
</div>

<script>
function loadTemplateToEditor(template) {
    document.getElementById('form-title').innerHTML = '<i class="bi bi-pencil-square me-2 text-indigo"></i> Edit Permission Template: ' + template.name;
    document.getElementById('template_name').value = template.name;
    document.getElementById('template_description').value = template.description || '';

    // Clear all radio check selections first
    document.querySelectorAll('.radio-inherit').forEach(function(el) { el.checked = true; });

    // Set template permissions
    Object.keys(template.permissions).forEach(function(key) {
        var val = template.permissions[key];
        if (val === 'Allow') {
            document.getElementById('tmpl_' + key + '_allow').checked = true;
        } else if (val === 'Deny') {
            document.getElementById('tmpl_' + key + '_deny').checked = true;
        } else {
            document.getElementById('tmpl_' + key + '_inherit').checked = true;
        }
    });

    window.scrollTo({ top: 0, behavior: 'smooth' });
}
</script>
@endsection
