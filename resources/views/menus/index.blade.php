@extends('layouts.app')

@section('content')
<div class="glass-card mb-4">
    <div class="d-flex align-items-center justify-content-between">
        <div>
            <h4 class="mb-1 display-font text-indigo"><i class="bi bi-list-nested me-2"></i> Sidebar Menu Builder</h4>
            <p class="text-secondary mb-0 fs-8">Configure, reorder, and secure navigation links dynamically from the database.</p>
        </div>
        <button type="button" class="btn btn-indigo btn-sm px-3" data-bs-toggle="modal" data-bs-target="#addMenuModal">
            <i class="bi bi-plus-lg me-1"></i> Add Menu Item
        </button>
    </div>
</div>

<div class="row g-4">
    <!-- Menu Tree Panel -->
    <div class="col-lg-8">
        <div class="glass-card">
            <h5 class="mb-4 display-font"><i class="bi bi-tree me-2 text-indigo"></i> Sidebar Menu Structure</h5>
            
            <div class="menu-list-wrapper">
                @forelse($menus as $menu)
                    <div class="menu-item-card border rounded p-3 mb-3 bg-light-subtle">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-3">
                                <span class="badge bg-secondary-subtle text-secondary py-1 px-2 fs-9">#{{ $menu->sort_order }}</span>
                                <span class="fs-5 text-indigo"><i class="bi {{ $menu->icon ?? 'bi-link-45deg' }}"></i></span>
                                <div>
                                    <span class="fw-bold text-dark fs-8">{{ $menu->title }}</span>
                                    @if($menu->url)
                                        <span class="text-secondary fs-9 ms-2">({{ $menu->url }})</span>
                                    @else
                                        <span class="badge bg-dark-subtle text-dark fs-9 ms-2">Header Group</span>
                                    @endif
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge {{ $menu->status === 'Active' ? 'bg-success' : 'bg-danger' }} fs-9">{{ $menu->status }}</span>
                                <button type="button" class="btn btn-sm btn-outline-primary border-0" data-bs-toggle="modal" data-bs-target="#editMenuModal{{ $menu->id }}">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form action="{{ route('menus.delete', $menu->id) }}" method="POST" class="m-0" onsubmit="return confirm('Are you sure you want to delete this menu and all its submenus?');">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-danger border-0">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Children (Submenu items) -->
                        @if($menu->children->isNotEmpty())
                            <div class="submenu-container mt-3 ps-4 border-start ms-2">
                                @foreach($menu->children as $child)
                                    <div class="submenu-item border rounded p-2 mb-2 bg-white d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge bg-light text-secondary fs-9">#{{ $child->sort_order }}</span>
                                            <span class="text-secondary"><i class="bi {{ $child->icon ?? 'bi-dot' }}"></i></span>
                                            <span class="fw-semibold text-dark fs-8">{{ $child->title }}</span>
                                            <span class="text-secondary fs-9">({{ $child->url }})</span>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge {{ $child->status === 'Active' ? 'bg-success' : 'bg-danger' }} fs-9">{{ $child->status }}</span>
                                            <button type="button" class="btn btn-sm btn-outline-primary border-0 py-0 px-1" data-bs-toggle="modal" data-bs-target="#editMenuModal{{ $child->id }}">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <form action="{{ route('menus.delete', $child->id) }}" method="POST" class="m-0" onsubmit="return confirm('Delete this submenu item?');">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-danger border-0 py-0 px-1">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>

                                    <!-- Submenu Edit Modal -->
                                    @include('menus.partials.edit_modal', ['item' => $child, 'parents' => $menus])
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <!-- Parent Edit Modal -->
                    @include('menus.partials.edit_modal', ['item' => $menu, 'parents' => $menus])
                @empty
                    <div class="text-center py-5">
                        <i class="bi bi-list-nested fs-1 text-secondary opacity-50"></i>
                        <h6 class="mt-3 text-secondary">No custom menus configured.</h6>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Help Panel -->
    <div class="col-lg-4">
        <div class="glass-card mb-4 bg-light-subtle">
            <h6 class="display-font mb-3 text-indigo"><i class="bi bi-question-circle me-1"></i> Dynamic Gating Information</h6>
            <p class="fs-8 text-secondary">Sidebar links are dynamically checked against individual permissions. If a user does not hold the defined <strong>Permission Key</strong>, the link is hidden automatically.</p>
            <p class="fs-8 text-secondary mb-0">For instance, setting the permission key to <code>payroll.view</code> restricts that menu item to only users holding the payroll view permission.</p>
        </div>
    </div>
</div>

<!-- Add Menu Modal -->
<div class="modal fade" id="addMenuModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content glass-card p-2 border-0">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title display-font"><i class="bi bi-plus-circle me-1 text-indigo"></i> Add Menu Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('menus.store') }}" method="POST">
                @csrf
                <div class="modal-body border-0 fs-8">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Menu Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control form-control-sm" required placeholder="e.g. Directory, Live Attendance">
                    </div>
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label fw-bold">Bootstrap Icon Class</label>
                            <input type="text" name="icon" class="form-control form-control-sm" placeholder="e.g. bi-people-fill">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold">Sort Order</label>
                            <input type="number" name="sort_order" class="form-control form-control-sm" value="0">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">URL Route Path</label>
                        <input type="text" name="url" class="form-control form-control-sm" placeholder="e.g. /employees, or leave blank for folder group">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Route Name (for active class matching)</label>
                        <input type="text" name="route_name" class="form-control form-control-sm" placeholder="e.g. employees">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Parent Group Menu</label>
                        <select name="parent_id" class="form-select form-select-sm">
                            <option value="">-- None (Top Level Menu) --</option>
                            @foreach($menus as $p)
                                @if(!$p->parent_id)
                                    <option value="{{ $p->id }}">{{ $p->title }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Permission Key restriction</label>
                        <input type="text" name="permission_key" class="form-control form-control-sm" placeholder="e.g. employee.view, or leave empty for public">
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-indigo"><i class="bi bi-save me-1"></i> Save Menu</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
