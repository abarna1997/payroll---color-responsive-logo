@extends('layouts.app')

@section('content')
<!-- Page Header -->
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h4 class="fw-bold m-0 text-dark display-font">
            <i class="bi bi-clock-history me-2 text-indigo"></i> Enterprise Attendance Operations Center
        </h4>
        <p class="text-secondary fs-7 m-0 mt-1">Real-time biometric punch logs, verification analytics, device telemetry, and automated attendance calculation.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('attendance.recalculate') }}" class="btn btn-custom-secondary btn-sm px-3" onclick="return confirm('Recalculate attendance statuses for all logs?')">
            <i class="bi bi-cpu me-1"></i> Bulk Recalculate
        </a>
        <button type="button" class="btn btn-outline-danger btn-sm px-3" onclick="if(confirm('Are you sure you want to clear raw punch logs? This action cannot be undone.')) document.getElementById('clearLogsForm').submit();">
            <i class="bi bi-trash-fill me-1"></i> Clear Raw Punch Logs
        </button>
    </div>
</div>

<form id="clearLogsForm" action="{{ route('attendance.clear-logs') }}" method="POST" class="d-none">
    @csrf
    <input type="hidden" name="start_date" value="{{ request('start_date') }}">
    <input type="hidden" name="end_date" value="{{ request('end_date') }}">
</form>

<!-- 1. Operational Telemetry Dashboard Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3 col-xl-1-5">
        <div class="glass-card p-3 d-flex align-items-center justify-content-between h-100">
            <div>
                <span class="text-secondary fs-8 d-block fw-medium">Today's Punches</span>
                <h4 class="fw-bold m-0 text-dark mt-1">{{ $metrics['today_punches'] }}</h4>
            </div>
            <div class="rounded-circle p-2 bg-indigo-subtle text-indigo d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                <i class="bi bi-fingerprint fs-5"></i>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3 col-xl-1-5">
        <div class="glass-card p-3 d-flex align-items-center justify-content-between h-100">
            <div>
                <span class="text-secondary fs-8 d-block fw-medium">Present Today</span>
                <h4 class="fw-bold m-0 text-success mt-1">{{ $metrics['employees_present'] }}</h4>
            </div>
            <div class="rounded-circle p-2 bg-success-subtle text-success d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                <i class="bi bi-person-check-fill fs-5"></i>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3 col-xl-1-5">
        <div class="glass-card p-3 d-flex align-items-center justify-content-between h-100">
            <div>
                <span class="text-secondary fs-8 d-block fw-medium">Absent Today</span>
                <h4 class="fw-bold m-0 text-danger mt-1">{{ $metrics['employees_absent'] }}</h4>
            </div>
            <div class="rounded-circle p-2 bg-danger-subtle text-danger d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                <i class="bi bi-person-x-fill fs-5"></i>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3 col-xl-1-5">
        <div class="glass-card p-3 d-flex align-items-center justify-content-between h-100">
            <div>
                <span class="text-secondary fs-8 d-block fw-medium">Late Arrivals</span>
                <h4 class="fw-bold m-0 text-warning mt-1">{{ $metrics['late_arrivals'] }}</h4>
            </div>
            <div class="rounded-circle p-2 bg-warning-subtle text-warning d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                <i class="bi bi-alarm-fill fs-5"></i>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3 col-xl-1-5">
        <div class="glass-card p-3 d-flex align-items-center justify-content-between h-100">
            <div>
                <span class="text-secondary fs-8 d-block fw-medium">Early Departures</span>
                <h4 class="fw-bold m-0 text-info mt-1">{{ $metrics['early_departures'] }}</h4>
            </div>
            <div class="rounded-circle p-2 bg-info-subtle text-info d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                <i class="bi bi-box-arrow-right fs-5"></i>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3 col-xl-1-5">
        <div class="glass-card p-3 d-flex align-items-center justify-content-between h-100">
            <div>
                <span class="text-secondary fs-8 d-block fw-medium">Terminals Online</span>
                <h4 class="fw-bold m-0 text-primary mt-1">{{ $metrics['devices_online'] }} / {{ $metrics['devices_online'] + $metrics['devices_offline'] }}</h4>
            </div>
            <div class="rounded-circle p-2 bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                <i class="bi bi-wifi fs-5"></i>
            </div>
        </div>
    </div>
</div>

<!-- 2. Advanced Multi-Criteria Filter Panel -->
<div class="glass-card mb-4 p-3">
    <form action="{{ route('attendance') }}" method="GET" class="row g-2 align-items-end">
        <div class="col-12 col-md-3">
            <label class="form-label text-secondary fs-8 fw-semibold">Search Employee / ID</label>
            <div class="input-group">
                <span class="input-group-text bg-transparent border-end-0 text-secondary" style="border-color: var(--border-color);">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text" class="form-control form-control-custom border-start-0" name="search" value="{{ request('search') }}" placeholder="Emp ID, First or Last Name">
            </div>
        </div>
        
        <div class="col-6 col-md-2">
            <label class="form-label text-secondary fs-8 fw-semibold">Company</label>
            <select class="form-select form-select-custom" name="company_id" onchange="this.form.submit()">
                <option value="">All Companies</option>
                @foreach($companies as $c)
                    <option value="{{ $c->id }}" {{ request('company_id') == $c->id ? 'selected' : '' }}>{{ $c->company_name }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-6 col-md-2">
            <label class="form-label text-secondary fs-8 fw-semibold">Branch</label>
            <select class="form-select form-select-custom" name="branch_id" onchange="this.form.submit()">
                <option value="">All Branches</option>
                @foreach($branches as $b)
                    <option value="{{ $b->id }}" {{ request('branch_id') == $b->id ? 'selected' : '' }}>{{ $b->branch_name }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-6 col-md-2">
            <label class="form-label text-secondary fs-8 fw-semibold">Department</label>
            <select class="form-select form-select-custom" name="department_id" onchange="this.form.submit()">
                <option value="">All Depts</option>
                @foreach($departments as $d)
                    <option value="{{ $d->id }}" {{ request('department_id') == $d->id ? 'selected' : '' }}>{{ $d->department_name }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-6 col-md-3">
            <label class="form-label text-secondary fs-8 fw-semibold">Status / Verification</label>
            <div class="d-flex gap-2">
                <select class="form-select form-select-custom" name="status" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    <option value="Present" {{ request('status') === 'Present' ? 'selected' : '' }}>Present</option>
                    <option value="Late" {{ request('status') === 'Late' ? 'selected' : '' }}>Late Arrival</option>
                    <option value="Early Out" {{ request('status') === 'Early Out' ? 'selected' : '' }}>Early Out</option>
                    <option value="Absent" {{ request('status') === 'Absent' ? 'selected' : '' }}>Absent</option>
                </select>
                <select class="form-select form-select-custom" name="verification_method" onchange="this.form.submit()">
                    <option value="">All Verification</option>
                    <option value="Face" {{ request('verification_method') === 'Face' ? 'selected' : '' }}>Face</option>
                    <option value="Fingerprint" {{ request('verification_method') === 'Fingerprint' ? 'selected' : '' }}>Fingerprint</option>
                    <option value="Card" {{ request('verification_method') === 'Card' ? 'selected' : '' }}>Card</option>
                    <option value="Password" {{ request('verification_method') === 'Password' ? 'selected' : '' }}>Password</option>
                </select>
            </div>
        </div>

        <div class="col-12 col-md-4 mt-2">
            <label class="form-label text-secondary fs-8 fw-semibold">Date Range</label>
            <div class="d-flex gap-2">
                <input type="date" class="form-control form-control-custom" name="start_date" value="{{ request('start_date', date('Y-m-d')) }}">
                <input type="date" class="form-control form-control-custom" name="end_date" value="{{ request('end_date', date('Y-m-d')) }}">
            </div>
        </div>

        <div class="col-12 col-md-8 text-md-end mt-2">
            @if(request()->hasAny(['search', 'company_id', 'branch_id', 'department_id', 'status', 'verification_method']))
                <a href="{{ route('attendance') }}" class="btn btn-custom-secondary me-2 px-3 py-1.5 fs-8" title="Reset Filters">
                    <i class="bi bi-arrow-counterclockwise"></i> Reset
                </a>
            @endif
            <button type="submit" class="btn btn-custom-primary px-4 py-1.5 fs-8 fw-semibold">
                <i class="bi bi-filter me-1"></i> Apply Filters
            </button>
        </div>
    </form>
</div>

<!-- 3. Raw Punch Logs Registry Table -->
<div class="glass-card p-4">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h5 class="m-0 display-font text-dark"><i class="bi bi-journal-text me-2 text-indigo"></i> Raw Biometric Punch Logs</h5>
        <span class="badge bg-indigo-subtle text-indigo px-3 py-2 rounded-pill fs-7 fw-semibold">
            Showing {{ $logs->total() }} Recorded Logs
        </span>
    </div>

    @if($logs->count() > 0)
        <div class="table-responsive">
            <table class="table custom-table align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 70px;">Log ID</th>
                        <th>Employee ID</th>
                        <th>Employee Name</th>
                        <th>Company & Branch</th>
                        <th>Department</th>
                        <th>Date & Time</th>
                        <th>Punch Type</th>
                        <th>Verification</th>
                        <th>Calculated Status</th>
                        <th>Terminal Serial</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($logs as $log)
                        <tr>
                            <td><span class="text-secondary font-monospace fs-8">#{{ $log->id }}</span></td>
                            <td>
                                @if($log->employee)
                                    <span class="badge bg-indigo text-light font-monospace fs-7.5 px-2 py-1">{{ $log->employee->employee_id }}</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger font-monospace fs-8">Unassigned</span>
                                @endif
                            </td>
                            <td>
                                <div class="fw-semibold text-dark fs-7.5">
                                    {{ $log->employee ? $log->employee->full_name : 'Device PIN: ' . $log->verify_code }}
                                </div>
                            </td>
                            <td>
                                @if($log->employee)
                                    <div class="text-dark fs-7.5 fw-medium">{{ $log->employee->company->company_name }}</div>
                                    <div class="text-secondary fs-8">{{ $log->employee->branch ? $log->employee->branch->branch_name : 'Headquarters' }}</div>
                                @else
                                    <span class="text-secondary fs-8">N/A</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-secondary-subtle text-dark fs-8 border border-secondary border-opacity-25">
                                    {{ $log->employee ? $log->employee->department->department_name : 'N/A' }}
                                </span>
                            </td>
                            <td>
                                <div class="text-dark fw-semibold fs-7.5">{{ $log->attendance_date->format('Y-m-d') }}</div>
                                <div class="text-indigo fs-8"><i class="bi bi-clock me-1"></i>{{ Carbon\Carbon::parse($log->attendance_time)->format('H:i:s') }}</div>
                            </td>
                            <td>
                                <span class="badge bg-dark text-light fs-8 px-2 py-1">
                                    {{ $log->attendance_type ?? 'Check-In' }}
                                </span>
                            </td>
                            <td>
                                <span class="text-dark fs-8">
                                    @if(strtolower($log->verification_method) === 'face')
                                        <i class="bi bi-person-bounding-box text-primary me-1"></i> Face
                                    @elseif(strtolower($log->verification_method) === 'fingerprint')
                                        <i class="bi bi-fingerprint text-success me-1"></i> Fingerprint
                                    @elseif(strtolower($log->verification_method) === 'card')
                                        <i class="bi bi-credit-card-2-front text-warning me-1"></i> Card
                                    @elseif(strtolower($log->verification_method) === 'web punch' || strtolower($log->verification_method) === 'web')
                                        <i class="bi bi-laptop text-info me-1"></i> Web Punch
                                    @else
                                        <i class="bi bi-shield-check text-info me-1"></i> {{ $log->verification_method ?? 'Unknown' }}
                                    @endif
                                </span>
                            </td>
                            <td>
                                <span class="badge-status {{ $log->attendance_status === 'Present' ? 'badge-online' : ($log->attendance_status === 'Late' || $log->attendance_status === 'Early Out' ? 'badge-pending' : 'badge-offline') }}">
                                    {{ $log->attendance_status }}
                                </span>
                            </td>
                            <td><span class="text-secondary font-monospace fs-8">{{ $log->device_serial }}</span></td>
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-outline-info border-0 p-1 fs-8.5" data-bs-toggle="modal" data-bs-target="#punchDetailModal{{ $log->id }}" title="View Punch Telemetry">
                                    <i class="bi bi-eye-fill"></i> Details
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination Links -->
        <div class="d-flex justify-content-center mt-4">
            {{ $logs->appends(request()->all())->links('pagination::bootstrap-5') }}
        </div>
    @else
        <!-- Professional Empty State -->
        <div class="text-center py-5 my-3">
            <div class="rounded-circle bg-indigo-subtle text-indigo d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                <i class="bi bi-clock-history fs-1"></i>
            </div>
            <h5 class="fw-bold text-dark mb-2">No Attendance Logs Found</h5>
            <p class="text-secondary fs-7 mx-auto mb-4" style="max-width: 480px;">
                No biometric punch logs match your search filters for the selected date range. Try broadening your criteria or trigger a terminal sync.
            </p>
            <a href="{{ route('devices') }}" class="btn btn-custom-primary px-4 py-2">
                <i class="bi bi-cpu me-1"></i> Check Biometric Terminals
            </a>
        </div>
    @endif
</div>

<!-- 4. Punch Detail Modals -->
@foreach($logs as $log)
    <div class="modal fade" id="punchDetailModal{{ $log->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-white shadow-lg border-0 p-0">
                <div class="modal-header border-bottom p-3 bg-light">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-fingerprint fs-4 text-indigo"></i>
                        <div>
                            <h5 class="modal-title display-font m-0 text-dark">Punch Log Telemetry #{{ $log->id }}</h5>
                            <span class="text-secondary fs-8 font-monospace">Terminal Serial: {{ $log->device_serial }}</span>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 text-start">
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <span class="text-secondary fs-8 d-block">Employee Name</span>
                            <div class="fw-bold text-dark fs-7.5">{{ $log->employee ? $log->employee->full_name : 'Unassigned (PIN: ' . $log->verify_code . ')' }}</div>
                        </div>
                        <div class="col-6">
                            <span class="text-secondary fs-8 d-block">Employee ID</span>
                            <div class="fw-bold text-indigo font-monospace fs-7.5">{{ $log->employee ? $log->employee->employee_id : 'N/A' }}</div>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <span class="text-secondary fs-8 d-block">Punch Timestamp</span>
                            <div class="fw-bold text-dark fs-7.5">{{ $log->attendance_date->format('Y-m-d') }} {{ Carbon\Carbon::parse($log->attendance_time)->format('H:i:s') }}</div>
                        </div>
                        <div class="col-6">
                            <span class="text-secondary fs-8 d-block">Verification Mode</span>
                            <div class="fw-bold text-dark fs-7.5">{{ $log->verification_method }}</div>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <span class="text-secondary fs-8 d-block">Punch Event Type</span>
                            <span class="badge bg-dark text-light fs-8">{{ $log->attendance_type ?? 'Check-In' }}</span>
                        </div>
                        <div class="col-6">
                            <span class="text-secondary fs-8 d-block">Calculated Status</span>
                            <span class="badge-status {{ $log->attendance_status === 'Present' ? 'badge-online' : 'badge-pending' }} d-inline-block mt-1">
                                {{ $log->attendance_status }}
                            </span>
                        </div>
                    </div>

                    <div class="p-3 rounded bg-light border border-secondary border-opacity-25 mt-3">
                        <span class="text-indigo fs-8 uppercase fw-bold d-block mb-1"><i class="bi bi-cpu me-1"></i> Device Telemetry</span>
                        <div class="fs-8 text-secondary font-monospace">
                            Terminal: {{ $log->source === 'WEB' ? 'Employee Web Portal' : ($log->device ? $log->device->device_name : 'ADMS Biometric Terminal') }}<br>
                            IP Address: {{ $log->source === 'WEB' ? 'Remote Client IP' : ($log->device ? $log->device->ip_address : '192.168.1.*') }}<br>
                            Location: 
                            @if($log->source === 'WEB')
                                @if($log->latitude && $log->longitude)
                                    <a href="https://maps.google.com/?q={{ $log->latitude }},{{ $log->longitude }}" target="_blank" class="text-indigo text-decoration-none fw-semibold"><i class="bi bi-geo-alt-fill"></i> View GPS Map (Lat: {{ $log->latitude }})</a>
                                @else
                                    <span class="text-warning"><i class="bi bi-exclamation-triangle-fill"></i> Web Portal (GPS Not Captured)</span>
                                @endif
                            @else
                                {{ $log->device ? $log->device->location : 'Headquarters Main Entrance' }}
                            @endif
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top p-3 bg-light">
                    <button type="button" class="btn btn-custom-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endforeach
@endsection
