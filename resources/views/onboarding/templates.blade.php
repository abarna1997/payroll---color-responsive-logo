@extends('layouts.app')

@section('content')
<!-- Page Header -->
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h4 class="fw-bold m-0 text-light display-font">
            <i class="bi bi-file-earmark-richtext-fill me-2 text-indigo"></i> Enterprise Document Management Center
        </h4>
        <p class="text-secondary fs-7 m-0 mt-1">Manage corporate agreement templates, policy disclosures, placeholder macros & digital signatures.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('onboarding.dashboard') }}" class="btn btn-custom-secondary btn-sm px-3">
            <i class="bi bi-arrow-left me-1"></i> Onboarding Dashboard
        </a>
        <button type="button" class="btn btn-custom-primary btn-sm px-3 fw-semibold" data-bs-toggle="offcanvas" data-bs-target="#createTemplateDrawer">
            <i class="bi bi-plus-lg me-1"></i> Create Document Template
        </button>
    </div>
</div>

<!-- 1. Top Summary Telemetry Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-xl-2">
        <div class="glass-card p-3 d-flex align-items-center justify-content-between h-100">
            <div>
                <span class="text-secondary fs-8 d-block fw-medium">Total Templates</span>
                <h4 class="fw-bold m-0 text-light mt-1">{{ $metrics['total_templates'] }}</h4>
            </div>
            <div class="rounded-circle p-2 bg-indigo-subtle text-indigo d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                <i class="bi bi-file-earmark-stack-fill fs-5"></i>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="glass-card p-3 d-flex align-items-center justify-content-between h-100">
            <div>
                <span class="text-secondary fs-8 d-block fw-medium">Active Templates</span>
                <h4 class="fw-bold m-0 text-success mt-1">{{ $metrics['active_templates'] }}</h4>
            </div>
            <div class="rounded-circle p-2 bg-success-subtle text-success d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                <i class="bi bi-check-circle-fill fs-5"></i>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="glass-card p-3 d-flex align-items-center justify-content-between h-100">
            <div>
                <span class="text-secondary fs-8 d-block fw-medium">Generated Docs</span>
                <h4 class="fw-bold m-0 text-info mt-1">{{ $metrics['generated_documents'] }}</h4>
            </div>
            <div class="rounded-circle p-2 bg-info-subtle text-info d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                <i class="bi bi-file-earmark-arrow-up-fill fs-5"></i>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="glass-card p-3 d-flex align-items-center justify-content-between h-100">
            <div>
                <span class="text-secondary fs-8 d-block fw-medium">Pending Signature</span>
                <h4 class="fw-bold m-0 text-warning mt-1">{{ $metrics['pending_signatures'] }}</h4>
            </div>
            <div class="rounded-circle p-2 bg-warning-subtle text-warning d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                <i class="bi bi-clock-history fs-5"></i>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="glass-card p-3 d-flex align-items-center justify-content-between h-100">
            <div>
                <span class="text-secondary fs-8 d-block fw-medium">Signed Documents</span>
                <h4 class="fw-bold m-0 text-primary mt-1">{{ $metrics['signed_documents'] }}</h4>
            </div>
            <div class="rounded-circle p-2 bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                <i class="bi bi-pen-fill fs-5"></i>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="glass-card p-3 d-flex align-items-center justify-content-between h-100">
            <div>
                <span class="text-secondary fs-8 d-block fw-medium">Expired / Obsolete</span>
                <h4 class="fw-bold m-0 text-muted mt-1">{{ $metrics['expired_documents'] }}</h4>
            </div>
            <div class="rounded-circle p-2 bg-secondary-subtle text-secondary d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                <i class="bi bi-file-earmark-x-fill fs-5"></i>
            </div>
        </div>
    </div>
</div>

<!-- 2. Search & Toolbar Panel -->
<div class="glass-card mb-4 p-3">
    <form action="{{ route('onboarding.templates') }}" method="GET" class="row g-2 align-items-center">
        <div class="col-12 col-md-4 col-lg-5">
            <div class="input-group">
                <span class="input-group-text bg-transparent border-end-0 text-secondary" style="border-color: var(--border-color);">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text" name="search" class="form-control form-control-custom border-start-0" placeholder="Search templates by title or category..." value="{{ request('search') }}">
            </div>
        </div>
        <div class="col-6 col-md-3 col-lg-3">
            <select name="type" class="form-select form-select-custom" onchange="this.form.submit()">
                <option value="">All Document Categories</option>
                <option value="Offer Letter" {{ request('type') === 'Offer Letter' ? 'selected' : '' }}>Offer Letter</option>
                <option value="Appointment Letter" {{ request('type') === 'Appointment Letter' ? 'selected' : '' }}>Appointment Letter</option>
                <option value="NDA" {{ request('type') === 'NDA' ? 'selected' : '' }}>Non-Disclosure Agreement (NDA)</option>
                <option value="Code of Conduct" {{ request('type') === 'Code of Conduct' ? 'selected' : '' }}>Code of Conduct Policy</option>
                <option value="IT Policy" {{ request('type') === 'IT Policy' ? 'selected' : '' }}>IT & Security Policy</option>
                <option value="Leave Policy" {{ request('type') === 'Leave Policy' ? 'selected' : '' }}>Leave Policy Handout</option>
                <option value="Custom Agreement" {{ request('type') === 'Custom Agreement' ? 'selected' : '' }}>Custom Agreement</option>
            </select>
        </div>
        <div class="col-6 col-md-3 col-lg-2 d-flex gap-2">
            @if(request()->hasAny(['search', 'type']))
                <a href="{{ route('onboarding.templates') }}" class="btn btn-custom-secondary px-3" title="Clear Filters">
                    <i class="bi bi-arrow-counterclockwise"></i> Reset
                </a>
            @endif
        </div>
        <div class="col-12 col-lg-2 text-lg-end ms-auto">
            <button type="button" class="btn btn-custom-primary w-100" data-bs-toggle="offcanvas" data-bs-target="#createTemplateDrawer">
                <i class="bi bi-file-earmark-plus me-1"></i> New Template
            </button>
        </div>
    </form>
</div>

<!-- 3. Template Registry Table / Empty State -->
<div class="glass-card p-4">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h5 class="m-0 display-font"><i class="bi bi-journal-bookmark-fill me-2 text-indigo"></i> Template Registry</h5>
        <span class="badge bg-indigo-subtle text-indigo px-3 py-2 rounded-pill fs-7 fw-semibold">
            {{ $templates->count() }} Templates Registered
        </span>
    </div>

    @if($templates->count() > 0)
        <div class="table-responsive">
            <table class="table custom-table align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 50px;">Status</th>
                        <th>Template Title</th>
                        <th>Category</th>
                        <th>Version</th>
                        <th>Issued Docs</th>
                        <th>Placeholders</th>
                        <th>Last Modified</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($templates as $t)
                        <tr>
                            <td>
                                <span class="badge bg-success-subtle text-success fs-8 rounded-pill">Active</span>
                            </td>
                            <td>
                                <div class="fw-semibold text-light fs-7.5">{{ $t->name }}</div>
                                <span class="text-secondary fs-8 font-monospace">ID: TMPL-00{{ $t->id }}</span>
                            </td>
                            <td>
                                <span class="badge bg-indigo text-light fs-8 px-2 py-1">{{ $t->type }}</span>
                            </td>
                            <td>
                                <span class="badge bg-dark text-secondary border border-secondary font-monospace fs-8">v1.0</span>
                            </td>
                            <td>
                                <span class="fw-bold text-info font-monospace">{{ $t->agreements_count ?? 0 }}</span>
                                <span class="text-secondary fs-8">Employees</span>
                            </td>
                            <td>
                                <span class="badge bg-secondary-subtle text-light fs-8">10 Macros</span>
                            </td>
                            <td class="text-secondary fs-8">
                                {{ $t->updated_at ? $t->updated_at->diffForHumans() : 'Recently' }}
                            </td>
                            <td class="text-end">
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-outline-info border-0 p-1 fs-8.5 me-1" data-bs-toggle="modal" data-bs-target="#previewTemplateModal{{ $t->id }}" title="Live Preview">
                                        <i class="bi bi-eye-fill me-1"></i> Preview
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-primary border-0 p-1 fs-8.5 me-1" data-bs-toggle="offcanvas" data-bs-target="#editTemplateDrawer{{ $t->id }}" title="Edit Template">
                                        <i class="bi bi-pencil-square me-1"></i> Edit
                                    </button>
                                    <form action="{{ route('onboarding.templates.delete', $t->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this agreement template?')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-danger border-0 p-1 fs-8.5" title="Delete Template">
                                            <i class="bi bi-trash-fill"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <!-- Professional Empty State -->
        <div class="text-center py-5 my-3">
            <div class="rounded-circle bg-indigo-subtle text-indigo d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                <i class="bi bi-file-earmark-richtext fs-1"></i>
            </div>
            <h5 class="fw-bold text-light mb-2">No Document Templates Registered Yet</h5>
            <p class="text-secondary fs-7 mx-auto mb-4" style="max-width: 480px;">
                Build corporate NDA agreements, offer letters, employment contracts, and compliance policies with auto-filling employee placeholders.
            </p>
            <div class="d-flex align-items-center justify-content-center gap-2">
                <button type="button" class="btn btn-custom-primary px-4 py-2" data-bs-toggle="offcanvas" data-bs-target="#createTemplateDrawer">
                    <i class="bi bi-plus-lg me-1"></i> Create First Template
                </button>
            </div>
        </div>
    @endif
</div>

<!-- 4. Create Template Offcanvas Slide-over Drawer -->
<div class="offcanvas offcanvas-end glass-card p-0" tabindex="-1" id="createTemplateDrawer" style="width: 780px; background-color: var(--sidebar-bg); border-start: 1px solid var(--border-color);">
    <div class="offcanvas-header border-bottom border-secondary opacity-25 p-3">
        <h5 class="offcanvas-title text-light display-font">
            <i class="bi bi-file-earmark-plus-fill me-2 text-indigo"></i> Create Enterprise Document Template
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-4 text-start">
        <form action="{{ route('onboarding.templates.store') }}" method="POST" id="createTemplateForm">
            @csrf
            <div class="row g-3 mb-3">
                <div class="col-md-7">
                    <label class="form-label text-secondary fs-8 fw-semibold">Template Title *</label>
                    <input type="text" name="name" class="form-control form-control-custom" placeholder="e.g. Executive Non-Disclosure Agreement (NDA)" required>
                </div>
                <div class="col-md-5">
                    <label class="form-label text-secondary fs-8 fw-semibold">Agreement Category *</label>
                    <select name="type" class="form-select form-select-custom" required>
                        <option value="NDA">Non-Disclosure Agreement (NDA)</option>
                        <option value="Offer Letter">Offer Letter</option>
                        <option value="Appointment Letter">Appointment Letter</option>
                        <option value="Code of Conduct">Code of Conduct Policy</option>
                        <option value="IT Policy">IT & Asset Security Policy</option>
                        <option value="Leave Policy">Leave Policy Handout</option>
                        <option value="Custom Agreement">Custom Agreement</option>
                    </select>
                </div>
            </div>

            <!-- Interactive Macro Placeholder Insertion Bar -->
            <div class="mb-3">
                <label class="form-label text-secondary fs-8 fw-semibold mb-1">Click to Insert Dynamic Macro Placeholders</label>
                <div class="p-2 rounded d-flex flex-wrap gap-1" style="background-color: var(--input-bg); border: 1px solid var(--border-color);">
                    <button type="button" class="btn btn-sm btn-outline-indigo py-0 px-2 fs-8 font-monospace" onclick="insertPlaceholder('create_content_html', '&#123;&#123;company_name&#125;&#125;')">+ @{{company_name}}</button>
                    <button type="button" class="btn btn-sm btn-outline-indigo py-0 px-2 fs-8 font-monospace" onclick="insertPlaceholder('create_content_html', '&#123;&#123;employee_name&#125;&#125;')">+ @{{employee_name}}</button>
                    <button type="button" class="btn btn-sm btn-outline-indigo py-0 px-2 fs-8 font-monospace" onclick="insertPlaceholder('create_content_html', '&#123;&#123;employee_id&#125;&#125;')">+ @{{employee_id}}</button>
                    <button type="button" class="btn btn-sm btn-outline-indigo py-0 px-2 fs-8 font-monospace" onclick="insertPlaceholder('create_content_html', '&#123;&#123;designation&#125;&#125;')">+ @{{designation}}</button>
                    <button type="button" class="btn btn-sm btn-outline-indigo py-0 px-2 fs-8 font-monospace" onclick="insertPlaceholder('create_content_html', '&#123;&#123;department&#125;&#125;')">+ @{{department}}</button>
                    <button type="button" class="btn btn-sm btn-outline-indigo py-0 px-2 fs-8 font-monospace" onclick="insertPlaceholder('create_content_html', '&#123;&#123;salary&#125;&#125;')">+ @{{salary}}</button>
                    <button type="button" class="btn btn-sm btn-outline-indigo py-0 px-2 fs-8 font-monospace" onclick="insertPlaceholder('create_content_html', '&#123;&#123;join_date&#125;&#125;')">+ @{{join_date}}</button>
                    <button type="button" class="btn btn-sm btn-outline-indigo py-0 px-2 fs-8 font-monospace" onclick="insertPlaceholder('create_content_html', '&#123;&#123;nic&#125;&#125;')">+ @{{nic}}</button>
                    <button type="button" class="btn btn-sm btn-outline-indigo py-0 px-2 fs-8 font-monospace" onclick="insertPlaceholder('create_content_html', '&#123;&#123;today&#125;&#125;')">+ @{{today}}</button>
                </div>
            </div>

            <!-- Template HTML Body Editor -->
            <div class="mb-4">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <label class="form-label text-secondary fs-8 fw-semibold m-0">Document HTML Template Body *</label>
                    <span class="text-secondary fs-8">Supports Standard HTML & Inline Styling</span>
                </div>
                <textarea name="content_html" id="create_content_html" class="form-control form-control-custom font-monospace" rows="14" placeholder="&lt;h2 style='text-align:center;'&gt;CONFIDENTIALITY AGREEMENT&lt;/h2&gt;&#10;&lt;p&gt;This Non-Disclosure Agreement is executed on &lt;strong&gt;&#123;&#123;today&#125;&#125;&lt;/strong&gt; by and between &lt;strong&gt;&#123;&#123;company_name&#125;&#125;&lt;/strong&gt; and &lt;strong&gt;&#123;&#123;employee_name&#125;&#125;&lt;/strong&gt; (Emp ID: &#123;&#123;employee_id&#125;&#125;), residing at &#123;&#123;address&#125;&#125;.&lt;/p&gt;..." required></textarea>
            </div>

            <!-- Digital Signature Settings -->
            <div class="p-3 mb-4 rounded" style="background-color: rgba(99, 102, 241, 0.08); border: 1px dashed rgba(99, 102, 241, 0.3);">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="fw-semibold text-indigo fs-8 uppercase"><i class="bi bi-pen me-1"></i> Digital Signature Requirements</span>
                    <span class="badge bg-indigo-subtle text-indigo fs-8">Ready</span>
                </div>
                <div class="form-check form-check-inline fs-8">
                    <input class="form-check-input" type="checkbox" checked disabled>
                    <label class="form-check-label text-secondary">Employee E-Signature Required</label>
                </div>
                <div class="form-check form-check-inline fs-8">
                    <input class="form-check-input" type="checkbox" checked disabled>
                    <label class="form-check-label text-secondary">HR Representative Signature</label>
                </div>
                <div class="form-check form-check-inline fs-8">
                    <input class="form-check-input" type="checkbox" checked disabled>
                    <label class="form-check-label text-secondary">Company Digital Seal</label>
                </div>
            </div>

            <div class="d-flex align-items-center justify-content-end gap-2">
                <button type="button" class="btn btn-custom-secondary px-4" data-bs-dismiss="offcanvas">Cancel</button>
                <button type="submit" class="btn btn-custom-primary px-4 py-2">
                    <i class="bi bi-save me-1"></i> Save & Publish Template
                </button>
            </div>
        </form>
    </div>
</div>

<!-- 5. Edit Template Drawers & Live Preview Modals -->
@foreach($templates as $t)
    <!-- Edit Template Offcanvas Slide-over Drawer -->
    <div class="offcanvas offcanvas-end glass-card p-0" tabindex="-1" id="editTemplateDrawer{{ $t->id }}" style="width: 780px; background-color: var(--sidebar-bg); border-start: 1px solid var(--border-color);">
        <div class="offcanvas-header border-bottom border-secondary opacity-25 p-3">
            <h5 class="offcanvas-title text-light display-font">
                <i class="bi bi-pencil-square me-2 text-indigo"></i> Edit Template: {{ $t->name }}
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body p-4 text-start">
            <form action="{{ route('onboarding.templates.update', $t->id) }}" method="POST">
                @csrf
                <div class="row g-3 mb-3">
                    <div class="col-md-7">
                        <label class="form-label text-secondary fs-8 fw-semibold">Template Title *</label>
                        <input type="text" name="name" class="form-control form-control-custom" value="{{ $t->name }}" required>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label text-secondary fs-8 fw-semibold">Agreement Category *</label>
                        <select name="type" class="form-select form-select-custom" required>
                            <option value="NDA" {{ $t->type === 'NDA' ? 'selected' : '' }}>Non-Disclosure Agreement (NDA)</option>
                            <option value="Offer Letter" {{ $t->type === 'Offer Letter' ? 'selected' : '' }}>Offer Letter</option>
                            <option value="Appointment Letter" {{ $t->type === 'Appointment Letter' ? 'selected' : '' }}>Appointment Letter</option>
                            <option value="Code of Conduct" {{ $t->type === 'Code of Conduct' ? 'selected' : '' }}>Code of Conduct Policy</option>
                            <option value="IT Policy" {{ $t->type === 'IT Policy' ? 'selected' : '' }}>IT & Security Policy</option>
                            <option value="Leave Policy" {{ $t->type === 'Leave Policy' ? 'selected' : '' }}>Leave Policy Handout</option>
                            <option value="Custom Agreement" {{ $t->type === 'Custom Agreement' ? 'selected' : '' }}>Custom Agreement</option>
                        </select>
                    </div>
                </div>

                <!-- Interactive Macro Placeholder Insertion Bar -->
                <div class="mb-3">
                    <label class="form-label text-secondary fs-8 fw-semibold mb-1">Click to Insert Dynamic Macro Placeholders</label>
                    <div class="p-2 rounded d-flex flex-wrap gap-1" style="background-color: var(--input-bg); border: 1px solid var(--border-color);">
                        <button type="button" class="btn btn-sm btn-outline-indigo py-0 px-2 fs-8 font-monospace" onclick="insertPlaceholder('edit_content_html_{{ $t->id }}', '&#123;&#123;company_name&#125;&#125;')">+ @{{company_name}}</button>
                        <button type="button" class="btn btn-sm btn-outline-indigo py-0 px-2 fs-8 font-monospace" onclick="insertPlaceholder('edit_content_html_{{ $t->id }}', '&#123;&#123;employee_name&#125;&#125;')">+ @{{employee_name}}</button>
                        <button type="button" class="btn btn-sm btn-outline-indigo py-0 px-2 fs-8 font-monospace" onclick="insertPlaceholder('edit_content_html_{{ $t->id }}', '&#123;&#123;employee_id&#125;&#125;')">+ @{{employee_id}}</button>
                        <button type="button" class="btn btn-sm btn-outline-indigo py-0 px-2 fs-8 font-monospace" onclick="insertPlaceholder('edit_content_html_{{ $t->id }}', '&#123;&#123;designation&#125;&#125;')">+ @{{designation}}</button>
                        <button type="button" class="btn btn-sm btn-outline-indigo py-0 px-2 fs-8 font-monospace" onclick="insertPlaceholder('edit_content_html_{{ $t->id }}', '&#123;&#123;department&#125;&#125;')">+ @{{department}}</button>
                        <button type="button" class="btn btn-sm btn-outline-indigo py-0 px-2 fs-8 font-monospace" onclick="insertPlaceholder('edit_content_html_{{ $t->id }}', '&#123;&#123;salary&#125;&#125;')">+ @{{salary}}</button>
                        <button type="button" class="btn btn-sm btn-outline-indigo py-0 px-2 fs-8 font-monospace" onclick="insertPlaceholder('edit_content_html_{{ $t->id }}', '&#123;&#123;join_date&#125;&#125;')">+ @{{join_date}}</button>
                        <button type="button" class="btn btn-sm btn-outline-indigo py-0 px-2 fs-8 font-monospace" onclick="insertPlaceholder('edit_content_html_{{ $t->id }}', '&#123;&#123;nic&#125;&#125;')">+ @{{nic}}</button>
                        <button type="button" class="btn btn-sm btn-outline-indigo py-0 px-2 fs-8 font-monospace" onclick="insertPlaceholder('edit_content_html_{{ $t->id }}', '&#123;&#123;today&#125;&#125;')">+ @{{today}}</button>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label text-secondary fs-8 fw-semibold mb-1">Document HTML Template Body *</label>
                    <textarea name="content_html" id="edit_content_html_{{ $t->id }}" class="form-control form-control-custom font-monospace" rows="14" required>{{ $t->content_html }}</textarea>
                </div>

                <div class="d-flex align-items-center justify-content-end gap-2">
                    <button type="button" class="btn btn-custom-secondary px-4" data-bs-dismiss="offcanvas">Cancel</button>
                    <button type="submit" class="btn btn-custom-primary px-4 py-2">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Live Document Print Preview Modal -->
    <div class="modal fade" id="previewTemplateModal{{ $t->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content glass-card p-0" >
                <div class="modal-header border-bottom border-secondary opacity-25 p-3">
                    <h5 class="modal-title display-font"><i class="bi bi-eye me-2 text-indigo"></i> Live Document Preview: {{ $t->name }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 text-dark text-start bg-white rounded-bottom" style="min-height: 400px; line-height: 1.6;">
                    {!! str_replace(
                        ['{{company_name}}', '{{employee_name}}', '{{employee_id}}', '{{designation}}', '{{department}}', '{{salary}}', '{{join_date}}', '{{nic}}', '{{today}}'],
                        ['Prime One Global', 'John Doe', 'EMP-1001', 'Software Engineer', 'Information Technology', 'LKR 150,000', date('Y-m-d'), '991234567V', date('Y-m-d')],
                        $t->content_html
                    ) !!}
                </div>
                <div class="modal-footer border-top-0 p-3 bg-dark-subtle">
                    <button type="button" class="btn btn-custom-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-custom-primary btn-sm" onclick="window.print()">
                        <i class="bi bi-printer me-1"></i> Print Sample
                    </button>
                </div>
            </div>
        </div>
    </div>
@endforeach
@endsection

@section('scripts')
<script>
function insertPlaceholder(textareaId, placeholderText) {
    const txtArea = document.getElementById(textareaId);
    if (!txtArea) return;

    const startPos = txtArea.selectionStart;
    const endPos = txtArea.selectionEnd;
    const originalText = txtArea.value;

    txtArea.value = originalText.substring(0, startPos) + placeholderText + originalText.substring(endPos, originalText.length);
    txtArea.selectionStart = startPos + placeholderText.length;
    txtArea.selectionEnd = startPos + placeholderText.length;
    txtArea.focus();
}
</script>
@endsection
