<div class="modal fade" id="editMenuModal{{ $item->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content glass-card p-2 border-0 text-start">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title display-font"><i class="bi bi-pencil-square me-1 text-indigo"></i> Edit Menu Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('menus.update', $item->id) }}" method="POST">
                @csrf
                <div class="modal-body border-0 fs-8">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Menu Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control form-control-sm" value="{{ $item->title }}" required>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label fw-bold">Bootstrap Icon Class</label>
                            <input type="text" name="icon" class="form-control form-control-sm" value="{{ $item->icon }}">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold">Sort Order</label>
                            <input type="number" name="sort_order" class="form-control form-control-sm" value="{{ $item->sort_order }}">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">URL Route Path</label>
                        <input type="text" name="url" class="form-control form-control-sm" value="{{ $item->url }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Route Name</label>
                        <input type="text" name="route_name" class="form-control form-control-sm" value="{{ $item->route_name }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Parent Group Menu</label>
                        <select name="parent_id" class="form-select form-select-sm">
                            <option value="">-- None (Top Level Menu) --</option>
                            @foreach($parents as $p)
                                @if($p->id != $item->id && !$p->parent_id)
                                    <option value="{{ $p->id }}" {{ $item->parent_id == $p->id ? 'selected' : '' }}>{{ $p->title }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Permission Key restriction</label>
                        <input type="text" name="permission_key" class="form-control form-control-sm" value="{{ $item->permission_key }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Status</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="Active" {{ $item->status === 'Active' ? 'selected' : '' }}>Active</option>
                            <option value="Inactive" {{ $item->status === 'Inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-indigo"><i class="bi bi-save me-1"></i> Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
