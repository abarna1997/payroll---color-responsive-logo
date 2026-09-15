@extends('layouts.app')

@section('content')
<div class="container-fluid p-0">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0 text-gray-800">Daily Attendance</h1>
        
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('daily-attendance.index', array_merge(request()->query(), ['date' => \Carbon\Carbon::parse($date)->subDay()->toDateString()])) }}" class="btn btn-outline-secondary btn-sm">
                <i data-feather="chevron-left"></i> Previous Day
            </a>
            
            <form action="{{ route('daily-attendance.index') }}" method="GET" class="d-flex" id="dateForm">
                @foreach(request()->except('date', 'page') as $key => $val)
                    @if(is_array($val))
                        @foreach($val as $v)
                            <input type="hidden" name="{{ $key }}[]" value="{{ $v }}">
                        @endforeach
                    @else
                        <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                    @endif
                @endforeach
                <input type="date" name="date" class="form-control form-control-sm mx-2" value="{{ $date }}" onchange="document.getElementById('dateForm').submit()">
            </form>
            
            <a href="{{ route('daily-attendance.index', array_merge(request()->query(), ['date' => \Carbon\Carbon::parse($date)->addDay()->toDateString()])) }}" class="btn btn-outline-secondary btn-sm">
                Next Day <i data-feather="chevron-right"></i>
            </a>
            <a href="{{ route('daily-attendance.index', ['date' => \Carbon\Carbon::today('Asia/Colombo')->toDateString()]) }}" class="btn btn-primary btn-sm ms-2">
                Today
            </a>
        </div>
    </div>

    <!-- Summary Cards -->
    @include('daily-attendance.partials.summary-cards')

    <!-- Filters & Table -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3">
            <form action="{{ route('daily-attendance.index') }}" method="GET" class="row g-2 align-items-center">
                <input type="hidden" name="date" value="{{ $date }}">
                
                <div class="col-md-2">
                    <select name="department_id" class="form-select form-select-sm">
                        <option value="">All Departments</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-2">
                    <select name="shift_id" class="form-select form-select-sm">
                        <option value="">All Shifts</option>
                        @foreach($shifts as $s)
                            <option value="{{ $s->id }}" {{ request('shift_id') == $s->id ? 'selected' : '' }}>{{ $s->shift_name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-2">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Statuses</option>
                        <option value="PRESENT" {{ request('status') == 'PRESENT' ? 'selected' : '' }}>Present</option>
                        <option value="ABSENT" {{ request('status') == 'ABSENT' ? 'selected' : '' }}>Absent</option>
                        <option value="INCOMPLETE" {{ request('status') == 'INCOMPLETE' ? 'selected' : '' }}>Incomplete</option>
                        <option value="PENDING" {{ request('status') == 'PENDING' ? 'selected' : '' }}>Pending</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <div class="input-group input-group-sm">
                        <input type="text" name="search" class="form-control" placeholder="Search employee..." value="{{ request('search') }}">
                        <button class="btn btn-outline-secondary" type="submit"><i data-feather="search"></i></button>
                    </div>
                </div>
                
                <div class="col-md-3 text-end">
                    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                    <a href="{{ route('daily-attendance.index', ['date' => $date]) }}" class="btn btn-light btn-sm">Reset</a>
                    <a href="{{ route('daily-attendance.export', request()->query()) }}" class="btn btn-success btn-sm ms-2">
                        <i data-feather="download"></i> Export
                    </a>
                </div>
            </form>
        </div>
        
        <div class="card-body p-0 table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Employee</th>
                        <th>Shift & Dept</th>
                        <th>In</th>
                        <th>Out</th>
                        <th>Work Time</th>
                        <th>Status & Flags</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($summaries as $summary)
                        <tr>
                            <td>
                                <div class="fw-bold">{{ $summary->employee->first_name }} {{ $summary->employee->last_name }}</div>
                                <div class="text-muted small">{{ $summary->employee->emp_id }}</div>
                            </td>
                            <td>
                                <div>{{ $summary->shift->shift_name }}</div>
                                <div class="text-muted small">{{ $summary->employee->department->name ?? 'N/A' }}</div>
                            </td>
                            <td>
                                <div class="fw-bold">{{ $summary->check_in ? \Carbon\Carbon::parse($summary->check_in)->format('h:i A') : '--:--' }}</div>
                                <div class="text-muted small">Exp: {{ \Carbon\Carbon::parse($summary->shift->start_time)->format('h:i A') }}</div>
                            </td>
                            <td>
                                <div class="fw-bold">{{ $summary->check_out ? \Carbon\Carbon::parse($summary->check_out)->format('h:i A') : '--:--' }}</div>
                                <div class="text-muted small">Exp: {{ \Carbon\Carbon::parse($summary->shift->end_time)->format('h:i A') }}</div>
                            </td>
                            <td>
                                @if($summary->working_minutes > 0)
                                    {{ floor($summary->working_minutes / 60) }}h {{ $summary->working_minutes % 60 }}m
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                <div><x-attendance-badge :status="$summary->status" /></div>
                                <div class="mt-1 d-flex flex-wrap gap-1">
                                    @if($summary->is_late_in)
                                        <x-attendance-badge status="LATE_IN" detail="+{{ $summary->late_minutes }}m" />
                                    @endif
                                    @if($summary->is_early_in)
                                        <x-attendance-badge status="EARLY_IN" />
                                    @endif
                                    @if($summary->is_early_out)
                                        <x-attendance-badge status="EARLY_OUT" detail="-{{ $summary->early_out_minutes }}m" />
                                    @endif
                                    @if($summary->is_late_out)
                                        <x-attendance-badge status="LATE_OUT" />
                                    @endif
                                    @if($summary->is_ot_eligible)
                                        <x-attendance-badge status="OT_ELIGIBLE" />
                                    @endif
                                    @if($summary->is_missing_in)
                                        <x-attendance-badge status="MISSING_IN" />
                                    @endif
                                    @if($summary->is_missing_out)
                                        <x-attendance-badge status="MISSING_OUT" />
                                    @endif
                                </div>
                            </td>
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="viewDetails({{ $summary->id }})">
                                    View
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                No attendance records found for {{ \Carbon\Carbon::parse($date)->format('d M Y') }}.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($summaries->hasPages())
            <div class="card-footer bg-white border-top">
                {{ $summaries->links() }}
            </div>
        @endif
    </div>
</div>

@include('daily-attendance.partials.detail-modal')

@endsection

@push('scripts')
<script>
    function viewDetails(id) {
        // Show loading state
        const modalEl = document.getElementById('attendanceDetailModal');
        const modal = new bootstrap.Modal(modalEl);
        
        document.getElementById('modalContent').classList.add('d-none');
        document.getElementById('modalLoader').classList.remove('d-none');
        document.getElementById('modalError').classList.add('d-none');
        
        modal.show();

        fetch(`{{ url('/daily-attendance') }}/${id}/details`)
            .then(res => {
                if(!res.ok) throw new Error('Network response was not ok');
                return res.json();
            })
            .then(data => {
                document.getElementById('modalLoader').classList.add('d-none');
                document.getElementById('modalContent').classList.remove('d-none');
                
                populateModal(data);
            })
            .catch(err => {
                document.getElementById('modalLoader').classList.add('d-none');
                document.getElementById('modalError').classList.remove('d-none');
                console.error(err);
            });
    }
    
    function populateModal(data) {
        const { summary, timeline, raw_logs, explanation } = data;
        
        document.getElementById('detailEmpName').textContent = summary.employee.first_name + ' ' + summary.employee.last_name;
        document.getElementById('detailEmpId').textContent = summary.employee.emp_id;
        document.getElementById('detailDate').textContent = summary.attendance_date;
        document.getElementById('detailShift').textContent = summary.shift.shift_name;
        
        // Timeline
        const timelineList = document.getElementById('timelineList');
        timelineList.innerHTML = '';
        timeline.forEach(item => {
            const li = document.createElement('li');
            li.className = 'list-group-item d-flex justify-content-between align-items-center';
            
            let badgeClass = 'bg-secondary';
            if(item.type === 'early_in') badgeClass = 'bg-primary';
            if(item.type === 'late_in' || item.type === 'early_out' || item.type === 'late_out') badgeClass = 'bg-warning text-dark';
            if(item.type === 'ot') badgeClass = 'bg-info text-dark';
            if(item.type === 'grace') badgeClass = 'bg-success';
            
            li.innerHTML = `
                <span class="fw-bold">${item.time}</span>
                <span class="badge ${badgeClass}">${item.label}</span>
            `;
            timelineList.appendChild(li);
        });
        
        // Explanation
        const expList = document.getElementById('explanationList');
        expList.innerHTML = '';
        explanation.forEach(exp => {
            const p = document.createElement('p');
            p.className = 'mb-1 small';
            p.textContent = exp;
            expList.appendChild(p);
        });
        
        // Raw Punches
        const rawBody = document.getElementById('rawPunchesBody');
        rawBody.innerHTML = '';
        if(raw_logs.length === 0) {
            rawBody.innerHTML = '<tr><td colspan="4" class="text-center text-muted small">No raw punches recorded</td></tr>';
        } else {
            raw_logs.forEach(log => {
                const tr = document.createElement('tr');
                // Extract just time for simplicity
                const timeStr = log.attendance_timestamp.substring(11, 19);
                tr.innerHTML = `
                    <td class="small">${timeStr}</td>
                    <td class="small">${log.punch_state || '--'}</td>
                    <td class="small">${log.verify_type || 'Unknown'}</td>
                    <td class="small">${log.device ? log.device.device_name : (log.source || 'Manual')}</td>
                `;
                rawBody.appendChild(tr);
            });
        }
    }
</script>
@endpush