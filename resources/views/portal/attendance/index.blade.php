@extends('portal.layout')

@section('content')
<div class="card" style="margin-bottom: 2rem;">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h1 style="margin: 0; font-size: 1.5rem; color: var(--dark-light);">
            <i class="fa-solid fa-clock-rotate-left"></i> Attendance History
        </h1>
        
        <form method="GET" action="{{ route('portal.attendance.index') }}" style="display: flex; gap: 1rem; margin: 0;">
            <select name="month" class="form-control" style="width: auto; padding: 0.5rem;" onchange="this.form.submit()">
                @for($m = 1; $m <= 12; $m++)
                    <option value="{{ sprintf('%02d', $m) }}" {{ $month == sprintf('%02d', $m) ? 'selected' : '' }}>
                        {{ date('F', mktime(0, 0, 0, $m, 10)) }}
                    </option>
                @endfor
            </select>
            <select name="year" class="form-control" style="width: auto; padding: 0.5rem;" onchange="this.form.submit()">
                @for($y = date('Y') - 2; $y <= date('Y'); $y++)
                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>
                        {{ $y }}
                    </option>
                @endfor
            </select>
        </form>
    </div>
</div>

<div class="card">
    @if($logs->isEmpty())
        <div style="text-align: center; padding: 3rem; color: var(--text-muted);">
            <div style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;">
                <i class="fa-solid fa-calendar-xmark"></i>
            </div>
            <h3>No Attendance Records Found</h3>
            <p>No punches recorded for {{ date('F Y', mktime(0, 0, 0, $month, 10, $year)) }}.</p>
        </div>
    @else
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Type</th>
                        <th>Method</th>
                        <th>Location</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($logs as $log)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($log->attendance_timestamp)->format('M d, Y') }}</td>
                        <td style="font-weight: 500;">{{ \Carbon\Carbon::parse($log->attendance_timestamp)->format('h:i A') }}</td>
                        <td>
                            @if($log->punch_state == 0)
                                <span class="badge badge-success">Check In</span>
                            @else
                                <span class="badge badge-warning">Check Out</span>
                            @endif
                        </td>
                        <td>
                            @if($log->verify_mode == 99)
                                <span style="color: var(--primary);"><i class="fa-solid fa-house-laptop"></i> Web Punch (WFH)</span>
                            @else
                                <span style="color: var(--text-muted);"><i class="fa-solid fa-fingerprint"></i> Device</span>
                            @endif
                        </td>
                        <td>
                            @if($log->verify_mode == 99 && $log->latitude && $log->longitude)
                                <a href="https://maps.google.com/?q={{ $log->latitude }},{{ $log->longitude }}" target="_blank" style="color: var(--primary); text-decoration: none;">
                                    <i class="fa-solid fa-map-location-dot"></i> View
                                </a>
                            @else
                                <span style="color: var(--text-muted);">-</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
