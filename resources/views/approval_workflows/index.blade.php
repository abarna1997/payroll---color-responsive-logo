@extends('layouts.app')

@section('content')
<div class="glass-card mb-4">
    <div class="d-flex align-items-center justify-content-between">
        <div>
            <h4 class="mb-1 display-font text-indigo"><i class="bi bi-diagram-3-fill me-2"></i> Approval Workflow Chains</h4>
            <p class="text-secondary mb-0 fs-8">Establish database-driven multi-level approval hierarchies for system requests.</p>
        </div>
        <button type="button" class="btn btn-indigo btn-sm px-3" data-bs-toggle="modal" data-bs-target="#addWorkflowModal">
            <i class="bi bi-plus-lg me-1"></i> Add Workflow
        </button>
    </div>
</div>

<div class="row g-4">
    @forelse($workflows as $wf)
        <div class="col-lg-6">
            <div class="glass-card h-100">
                <div class="d-flex align-items-start justify-content-between border-bottom pb-3 mb-3">
                    <div>
                        <h5 class="mb-1 display-font fw-bold text-dark">{{ $wf->name }}</h5>
                        <span class="badge bg-secondary-subtle text-secondary fs-9">Module: {{ strtoupper($wf->module) }}</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge {{ $wf->is_active ? 'bg-success' : 'bg-danger' }} fs-9">
                            {{ $wf->is_active ? 'Active' : 'Disabled' }}
                        </span>
                        <button type="button" class="btn btn-sm btn-outline-primary border-0 py-0 px-1" data-bs-toggle="modal" data-bs-target="#addStepModal{{ $wf->id }}" title="Add Step">
                            <i class="bi bi-plus-circle-fill fs-5"></i>
                        </button>
                    </div>
                </div>

                <p class="text-secondary fs-8 mb-4">{{ $wf->description ?? 'No workflow description provided.' }}</p>

                <h6 class="display-font text-indigo fs-8 mb-3"><i class="bi bi-list-ol"></i> Step Configuration Chain</h6>
                
                <div class="workflow-steps-timeline position-relative ps-3">
                    @forelse($wf->steps as $index => $step)
                        <div class="timeline-step-item position-relative pb-4 ps-3 border-start" style="border-color: var(--border-color) !important;">
                            <div class="timeline-bullet position-absolute bg-indigo rounded-circle" style="width: 10px; height: 10px; left: -5.5px; top: 5px;"></div>
                            
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="fw-bold text-dark fs-8">
                                        Step {{ $step->step_number }}: {{ $step->step_name }}
                                    </div>
                                    <div class="text-secondary fs-9">
                                        Approver: <span class="fw-semibold text-indigo">{{ $step->role->name ?? 'Any' }}</span>
                                        @if($step->accessLevel)
                                            (Min: {{ $step->accessLevel->code }})
                                        @endif
                                    </div>
                                    @if($step->auto_approve_hours)
                                        <div class="text-warning fs-9 mt-1">
                                            <i class="bi bi-clock-history"></i> Auto-approves after {{ $step->auto_approve_hours }} hours
                                        </div>
                                    @endif
                                </div>
                                <form action="{{ route('approval-workflows.delete-step', $step->id) }}" method="POST" class="m-0" onsubmit="return confirm('Remove this step from the workflow?');">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-danger border-0 p-1">
                                        <i class="bi bi-x-circle fs-6"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4 bg-light rounded border border-dashed">
                            <i class="bi bi-slash-circle fs-2 text-secondary opacity-50"></i>
                            <p class="text-secondary fs-9 mb-0 mt-2">No approval steps defined for this workflow yet.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Add Step Modal for Workflow -->
            <div class="modal fade" id="addStepModal{{ $wf->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content glass-card p-2 border-0 text-start">
                        <div class="modal-header border-0 pb-0">
                            <h5 class="modal-title display-font"><i class="bi bi-plus-circle me-1 text-indigo"></i> Add Approval Step</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form action="{{ route('approval-workflows.add-step', $wf->id) }}" method="POST">
                            @csrf
                            <div class="modal-body border-0 fs-8">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Step Name <span class="text-danger">*</span></label>
                                    <input type="text" name="step_name" class="form-control form-control-sm" required placeholder="e.g. Supervisor Review, Director Approves">
                                </div>
                                <div class="row mb-3">
                                    <div class="col-6">
                                        <label class="form-label fw-bold">Step Number <span class="text-danger">*</span></label>
                                        <input type="number" name="step_number" class="form-control form-control-sm" value="{{ $wf->steps->max('step_number') + 1 }}" required>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label fw-bold">Auto-approve (Hours)</label>
                                        <input type="number" name="auto_approve_hours" class="form-control form-control-sm" placeholder="e.g. 48, or leave empty">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Approver Type</label>
                                    <select name="approver_type" class="form-select form-select-sm">
                                        <option value="Role">Role Constraint</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Target Approver Role <span class="text-danger">*</span></label>
                                    <select name="role_id" class="form-select form-select-sm" required>
                                        @foreach($roles as $r)
                                            <option value="{{ $r->id }}">{{ $r->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Minimum Access Level required</label>
                                    <select name="access_level_id" class="form-select form-select-sm">
                                        <option value="">-- No access level requirement --</option>
                                        @foreach($levels as $lvl)
                                            <option value="{{ $lvl->id }}">{{ $lvl->getLabel() }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="modal-footer border-0 pt-0">
                                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-sm btn-indigo"><i class="bi bi-save me-1"></i> Save Step</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12 text-center py-5">
            <i class="bi bi-diagram-3 fs-1 text-secondary opacity-50"></i>
            <h6 class="mt-3 text-secondary">No approval workflows configured.</h6>
        </div>
    @endforelse
</div>

<!-- Add Workflow Modal -->
<div class="modal fade" id="addWorkflowModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content glass-card p-2 border-0">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title display-font"><i class="bi bi-plus-circle me-1 text-indigo"></i> Add Workflow</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('approval-workflows.store') }}" method="POST">
                @csrf
                <div class="modal-body border-0 fs-8">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Workflow Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control form-control-sm" required placeholder="e.g. Leave Approval, Expense Approval">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Workflow Module Code <span class="text-danger">*</span></label>
                        <input type="text" name="module" class="form-control form-control-sm" required placeholder="e.g. leave, payroll, asset">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Description</label>
                        <textarea name="description" class="form-control form-control-sm text-area" rows="3" placeholder="Enter workflow purpose..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-indigo"><i class="bi bi-save me-1"></i> Save Workflow</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
