@extends('portal.layout')

@section('content')
<div class="card" style="margin-bottom: 2rem; background: linear-gradient(135deg, var(--primary) 0%, #312E81 100%); color: white; border: none; padding: 2.5rem; position: relative; overflow: hidden; box-shadow: 0 20px 40px rgba(49, 46, 129, 0.4);">
    <div style="position: absolute; top: -50%; right: -10%; width: 300px; height: 300px; background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 70%); border-radius: 50%;"></div>
    <div style="position: absolute; bottom: -30%; right: 15%; width: 200px; height: 200px; background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%); border-radius: 50%;"></div>
    
    <div style="display: flex; justify-content: space-between; align-items: center; position: relative; z-index: 10;">
        <div>
            <h1 style="margin: 0 0 0.5rem 0; font-size: 2.5rem; font-weight: 800; letter-spacing: -0.5px;">Welcome back, {{ $employee->first_name }}!</h1>
            <p style="margin: 0; opacity: 0.9; font-size: 1.15rem; font-weight: 300;">Here's an overview of your recent activity and requests.</p>
        </div>
        <div style="background: rgba(255,255,255,0.1); backdrop-filter: blur(10px); padding: 1.5rem; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: inset 0 0 0 1px rgba(255,255,255,0.2);">
            <i class="fa-solid fa-face-smile" style="font-size: 3rem; color: white;"></i>
        </div>
    </div>
</div>

<div class="dashboard-grid">
    <div class="card stat-card" style="transition: transform 0.3s ease, box-shadow 0.3s ease;">
        <div class="stat-icon" style="background: linear-gradient(135deg, var(--secondary) 0%, #059669 100%); box-shadow: 0 10px 20px rgba(16, 185, 129, 0.3);">
            <i class="fa-solid fa-clock-rotate-left"></i>
        </div>
        <div class="stat-content">
            <h3 style="color: var(--text-muted); font-weight: 700; letter-spacing: 1px;">Recent Punches</h3>
            <p style="font-size: 2.2rem; background: linear-gradient(135deg, var(--dark) 0%, var(--text-main) 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">{{ $recentLogs->count() }}</p>
        </div>
    </div>
    
    <div class="card stat-card" style="transition: transform 0.3s ease, box-shadow 0.3s ease;">
        <div class="stat-icon" style="background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%); box-shadow: 0 10px 20px rgba(245, 158, 11, 0.3);">
            <i class="fa-solid fa-house-laptop"></i>
        </div>
        <div class="stat-content">
            <h3 style="color: var(--text-muted); font-weight: 700; letter-spacing: 1px;">WFH Requests</h3>
            <p style="font-size: 2.2rem; background: linear-gradient(135deg, var(--dark) 0%, var(--text-main) 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">{{ $wfhRequests->count() }}</p>
        </div>
    </div>
    
    <div class="card stat-card" style="transition: transform 0.3s ease, box-shadow 0.3s ease; position: relative; overflow: hidden;">
        <div class="stat-icon" style="background: linear-gradient(135deg, #EC4899 0%, #BE185D 100%); box-shadow: 0 10px 20px rgba(236, 72, 153, 0.3);">
            <i class="fa-solid fa-file-invoice-dollar"></i>
        </div>
        <div class="stat-content">
            <h3 style="color: var(--text-muted); font-weight: 700; letter-spacing: 1px;">Payslips</h3>
            <a href="{{ route('portal.payslips.index') }}" style="display: inline-flex; align-items: center; gap: 0.5rem; margin-top: 0.5rem; font-weight: 700; color: var(--primary); text-decoration: none; font-size: 1.1rem; transition: color 0.2s;">
                View All <i class="fa-solid fa-arrow-right" style="transition: transform 0.2s;"></i>
            </a>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
    <!-- Recent Punches -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title"><div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(16, 185, 129, 0.1); color: var(--secondary); display: flex; align-items: center; justify-content: center;"><i class="fa-solid fa-clock"></i></div> Recent Attendance</h2>
            <a href="#" class="btn btn-outline" style="font-size: 0.8rem; padding: 0.4rem 0.8rem;"><i class="fa-solid fa-list me-1"></i> Full Log</a>
        </div>
        
        @if($recentLogs->isEmpty())
            <div style="text-align: center; padding: 3rem 1rem; color: var(--text-muted);">
                <div style="font-size: 3rem; color: rgba(100, 116, 139, 0.2); margin-bottom: 1rem;"><i class="fa-solid fa-clipboard-list"></i></div>
                <h4 style="font-weight: 600; color: var(--text-main); margin-bottom: 0.5rem;">No Attendance Found</h4>
                <p style="font-size: 0.95rem;">You haven't punched in or out recently.</p>
            </div>
        @else
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Date & Time</th>
                            <th>Type</th>
                            <th>Method</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentLogs as $log)
                        <tr>
                            <td style="font-weight: 500;">{{ \Carbon\Carbon::parse($log->attendance_timestamp)->format('M d, Y') }} <span style="color: var(--text-muted); font-size: 0.85rem; margin-left: 0.3rem;">{{ \Carbon\Carbon::parse($log->attendance_timestamp)->format('h:i A') }}</span></td>
                            <td>
                                @if($log->punch_state == 0)
                                    <span class="badge badge-success"><i class="fa-solid fa-arrow-right-to-bracket me-1"></i> Check In</span>
                                @else
                                    <span class="badge badge-warning"><i class="fa-solid fa-arrow-right-from-bracket me-1"></i> Check Out</span>
                                @endif
                            </td>
                            <td>
                                @if($log->verify_mode == 99)
                                    <span style="display: flex; align-items: center; gap: 0.4rem; color: var(--text-muted); font-weight: 500;"><i class="fa-solid fa-globe" style="color: var(--primary);"></i> Web</span>
                                @else
                                    <span style="display: flex; align-items: center; gap: 0.4rem; color: var(--text-muted); font-weight: 500;"><i class="fa-solid fa-fingerprint" style="color: var(--secondary);"></i> Device</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- Recent WFH -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title"><div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(245, 158, 11, 0.1); color: #F59E0B; display: flex; align-items: center; justify-content: center;"><i class="fa-solid fa-house-laptop"></i></div> My WFH Requests</h2>
            <a href="{{ route('portal.wfh.index') }}" class="btn btn-outline" style="font-size: 0.8rem; padding: 0.4rem 0.8rem;"><i class="fa-solid fa-plus me-1"></i> New</a>
        </div>
        
        @if($wfhRequests->isEmpty())
            <div style="text-align: center; padding: 3rem 1rem; color: var(--text-muted);">
                <div style="font-size: 3rem; color: rgba(100, 116, 139, 0.2); margin-bottom: 1rem;"><i class="fa-solid fa-mug-hot"></i></div>
                <h4 style="font-weight: 600; color: var(--text-main); margin-bottom: 0.5rem;">No WFH Requests</h4>
                <p style="font-size: 0.95rem;">You haven't requested to work from home.</p>
            </div>
        @else
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($wfhRequests as $req)
                        <tr>
                            <td style="font-weight: 500;">{{ \Carbon\Carbon::parse($req->date)->format('M d, Y') }}</td>
                            <td>
                                @if($req->status == 'Approved')
                                    <span class="badge badge-success"><i class="fa-solid fa-check me-1"></i> Approved</span>
                                @elseif($req->status == 'Pending')
                                    <span class="badge badge-warning"><i class="fa-solid fa-clock me-1"></i> Pending</span>
                                @else
                                    <span class="badge badge-danger"><i class="fa-solid fa-xmark me-1"></i> {{ $req->status }}</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-top: 2rem;">
    <!-- Leave Balances -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title"><div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(79, 70, 229, 0.1); color: var(--primary); display: flex; align-items: center; justify-content: center;"><i class="fa-solid fa-umbrella-beach"></i></div> Leave Balances</h2>
            <a href="{{ route('portal.leave.index') }}" class="btn btn-outline" style="font-size: 0.8rem; padding: 0.4rem 0.8rem;"><i class="fa-solid fa-paper-plane me-1"></i> Request</a>
        </div>
        
        @if($leaveBalances->isEmpty())
            <div style="text-align: center; padding: 3rem 1rem; color: var(--text-muted);">
                <div style="font-size: 3rem; color: rgba(100, 116, 139, 0.2); margin-bottom: 1rem;"><i class="fa-solid fa-plane-departure"></i></div>
                <h4 style="font-weight: 600; color: var(--text-main); margin-bottom: 0.5rem;">No Leave Balances</h4>
                <p style="font-size: 0.95rem;">You do not have any allocated leaves.</p>
            </div>
        @else
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Allocated</th>
                            <th>Used</th>
                            <th>Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($leaveBalances as $balance)
                        <tr>
                            <td style="font-weight: 600; color: var(--text-main);">{{ $balance->leaveType->name }}</td>
                            <td style="font-weight: 500;">{{ $balance->allocated }}</td>
                            <td style="font-weight: 500; color: var(--text-muted);">{{ $balance->used }}</td>
                            <td style="font-weight: 800; font-size: 1.1rem; color: var(--primary);">{{ $balance->allocated - $balance->used }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- Upcoming Holidays -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title"><div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(236, 72, 153, 0.1); color: #EC4899; display: flex; align-items: center; justify-content: center;"><i class="fa-solid fa-calendar-star"></i></div> Upcoming Holidays</h2>
        </div>
        
        @if($upcomingHolidays->isEmpty())
            <div style="text-align: center; padding: 3rem 1rem; color: var(--text-muted);">
                <div style="font-size: 3rem; color: rgba(100, 116, 139, 0.2); margin-bottom: 1rem;"><i class="fa-solid fa-calendar-xmark"></i></div>
                <h4 style="font-weight: 600; color: var(--text-main); margin-bottom: 0.5rem;">No Holidays Found</h4>
                <p style="font-size: 0.95rem;">There are no upcoming holidays scheduled.</p>
            </div>
        @else
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Holiday Name</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($upcomingHolidays as $holiday)
                        <tr>
                            <td>
                                <div style="display: flex; flex-direction: column;">
                                    <span style="font-weight: 600;">{{ \Carbon\Carbon::parse($holiday->date)->format('M d, Y') }}</span>
                                    <span style="font-size: 0.8rem; color: var(--text-muted);">{{ \Carbon\Carbon::parse($holiday->date)->format('l') }}</span>
                                </div>
                            </td>
                            <td style="font-weight: 500; color: var(--dark);">{{ $holiday->holiday_name }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
