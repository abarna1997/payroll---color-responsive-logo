@extends('layouts.app')

@section('content')
<div class="row g-4 mb-4">
    <!-- Stat Widgets -->
    <div class="col-md-3">
        <div class="glass-card d-flex align-items-center justify-content-between p-4">
            <div>
                <span class="text-secondary fs-8 uppercase fw-bold mb-1 d-block">Employees Onboarding</span>
                <h3 class="m-0 text-dark font-semibold">{{ $onboardingEmployees->count() }}</h3>
            </div>
            <div class="rounded-circle p-3 bg-indigo bg-opacity-10 text-indigo fs-4">
                <i class="bi bi-people-fill"></i>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="glass-card d-flex align-items-center justify-content-between p-4">
            <div>
                <span class="text-secondary fs-8 uppercase fw-bold mb-1 d-block">Pending Documents</span>
                <h3 class="m-0 text-warning font-semibold">{{ $pendingDocs }}</h3>
            </div>
            <div class="rounded-circle p-3 bg-warning bg-opacity-10 text-warning fs-4">
                <i class="bi bi-file-earmark-arrow-up-fill"></i>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="glass-card d-flex align-items-center justify-content-between p-4">
            <div>
                <span class="text-secondary fs-8 uppercase fw-bold mb-1 d-block">Pending Signatures</span>
                <h3 class="m-0 text-danger font-semibold">{{ $pendingAgreements }}</h3>
            </div>
            <div class="rounded-circle p-3 bg-danger bg-opacity-10 text-danger fs-4">
                <i class="bi bi-pen-fill"></i>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="glass-card d-flex align-items-center justify-content-between p-4">
            <div>
                <span class="text-secondary fs-8 uppercase fw-bold mb-1 d-block">Pending Approvals</span>
                <h3 class="m-0 text-info font-semibold">{{ $pendingApprovals }}</h3>
            </div>
            <div class="rounded-circle p-3 bg-info bg-opacity-10 text-info fs-4">
                <i class="bi bi-shield-check"></i>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Active Onboarding List -->
    <div class="col-lg-8">
        <div class="glass-card p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="m-0 display-font">
                    <i class="bi bi-arrow-right-circle-fill me-2 text-indigo"></i> Active Onboarding Pipeline
                </h5>
                <div class="d-flex gap-2">
                    <a href="{{ route('onboarding.templates') }}" class="btn btn-custom-secondary btn-sm">
                        <i class="bi bi-file-earmark-text-fill me-1"></i> Document Templates ({{ $templatesCount }})
                    </a>
                    <a href="{{ route('onboarding.wizard') }}" class="btn btn-custom-primary btn-sm">
                        <i class="bi bi-plus-lg me-1"></i> Initiate Onboarding
                    </a>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table custom-table">
                    <thead>
                        <tr>
                            <th>Employee ID</th>
                            <th>Employee Name</th>
                            <th>Role & Department</th>
                            <th>Onboarding Checklist</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($onboardingEmployees as $emp)
                            @php
                                $totalChecklists = $emp->checklistItems->count() ?: 1;
                                $completedChecklists = $emp->checklistItems->where('is_completed', true)->count();
                                $pct = round(($completedChecklists / $totalChecklists) * 100);
                            @endphp
                            <tr>
                                <td>
                                    <span class="badge bg-indigo text-light">{{ $emp->employee_id }}</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($emp->profile_photo)
                                            <img src="{{ asset($emp->profile_photo) }}" class="rounded-circle me-2" style="width: 32px; height: 32px; object-fit: cover;">
                                        @else
                                            <div class="rounded-circle bg-secondary me-2 d-flex align-items-center justify-content-center text-light" style="width: 32px; height: 32px; font-size: 0.8rem;">
                                                {{ strtoupper(substr($emp->first_name, 0, 1)) }}
                                            </div>
                                        @endif
                                        <div class="fw-semibold text-dark fs-8.5">{{ $emp->full_name }}</div>
                                    </div>
                                </td>
                                <td>
                                    <div class="text-dark fs-8.5">{{ $emp->designation ?? 'Unassigned' }}</div>
                                    <div class="text-secondary fs-8">{{ $emp->department->department_name ?? 'N/A' }}</div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress flex-grow-1" style="height: 6px; background-color: var(--border-color); border-radius: 3px;">
                                            <div class="progress-bar bg-indigo" role="progressbar" style="width: {{ $pct }}%; border-radius: 3px;" aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                        <span class="text-secondary fs-8 fw-semibold">{{ $pct }}%</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('onboarding.wizard', ['employee_id' => $emp->id]) }}" class="btn btn-sm btn-outline-primary border-0 p-1 fs-8.5">
                                            <i class="bi bi-pencil-square"></i> Resume
                                        </a>
                                        <a href="{{ route('onboarding.profile', $emp->id) }}" class="btn btn-sm btn-outline-secondary border-0 p-1 fs-8.5 text-indigo">
                                            <i class="bi bi-eye-fill"></i> Profile
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-secondary py-5">
                                    <i class="bi bi-inbox-fill text-muted fs-1 mb-2 d-block"></i>
                                    No active employees are in onboarding. Click <strong>Initiate Onboarding</strong> to start one.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- New Joiners Timeline -->
    <div class="col-lg-4">
        <div class="glass-card p-4">
            <h5 class="mb-4 display-font">
                <i class="bi bi-star-fill me-2 text-warning"></i> Recent New Joiners
            </h5>

            <div class="timeline-container ps-3" style="border-left: 1px solid var(--border-color);">
                @forelse($newJoiners as $joiner)
                    <div class="position-relative mb-4">
                        <div class="position-absolute bg-indigo rounded-circle" style="width: 10px; height: 10px; left: -19px; top: 6px; border: 2px solid var(--sidebar-bg);"></div>
                        <div class="fw-semibold text-dark fs-8.5">{{ $joiner->full_name }}</div>
                        <div class="text-secondary fs-8">{{ $joiner->designation }} — {{ $joiner->department->department_name ?? 'N/A' }}</div>
                        <div class="text-secondary fs-8 italic mt-1">Joined: {{ $joiner->join_date ? $joiner->join_date->format('M d, Y') : 'N/A' }}</div>
                    </div>
                @empty
                    <div class="text-center text-secondary py-4 fs-8">No new joiners recorded in the past 30 days.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
