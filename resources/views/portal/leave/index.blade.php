@extends('portal.layout')

@section('content')
<div class="card" style="margin-bottom: 2rem;">
    <div class="card-header">
        <h2 class="card-title"><i class="fa-solid fa-umbrella-beach"></i> Leave Management</h2>
    </div>

    <!-- Leave Balances Summary -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
        @foreach($leaveBalances as $balance)
            <div style="background: rgba(248, 250, 252, 0.8); border: 1px solid var(--border); border-radius: 8px; padding: 1.5rem; text-align: center;">
                <h4 style="margin: 0 0 0.5rem 0; color: var(--text-muted); font-size: 0.9rem; text-transform: uppercase;">{{ $balance->leaveType->name }}</h4>
                <div style="font-size: 2rem; font-weight: bold; color: var(--primary);">{{ $balance->allocated - $balance->used }}</div>
                <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.5rem;">Allocated: {{ $balance->allocated }} | Used: {{ $balance->used }}</div>
            </div>
        @endforeach
    </div>

    <!-- Leave Request Form -->
    <div style="background: rgba(248, 250, 252, 0.5); padding: 1.5rem; border-radius: 8px; border: 1px solid var(--border); margin-bottom: 2rem;">
        <h3 style="margin-top: 0; font-size: 1.1rem;">Submit Leave Request</h3>
        <form action="{{ route('portal.leave.store') }}" method="POST">
            @csrf
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="leave_type_id" class="form-label">Leave Type</label>
                    <select name="leave_type_id" id="leave_type_id" class="form-control" required>
                        <option value="">Select Type</option>
                        @foreach($leaveTypes as $type)
                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="start_date" class="form-label">Start Date</label>
                    <input type="date" name="start_date" id="start_date" class="form-control" required min="{{ date('Y-m-d') }}">
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="end_date" class="form-label">End Date</label>
                    <input type="date" name="end_date" id="end_date" class="form-control" required min="{{ date('Y-m-d') }}">
                </div>
            </div>
            
            <div class="form-group">
                <label for="reason" class="form-label">Reason</label>
                <textarea name="reason" id="reason" class="form-control" rows="2" required placeholder="Provide a reason for your leave..."></textarea>
            </div>
            
            <div style="text-align: right;">
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-paper-plane"></i> Submit Request</button>
            </div>
        </form>
    </div>

    <h3 style="margin-top: 0; font-size: 1.1rem; margin-bottom: 1rem;">My Leave History</h3>
    @if($leaveRequests->isEmpty())
        <p style="text-align: center; color: var(--text-muted); padding: 2rem 0; border: 1px dashed var(--border); border-radius: 8px;">No leave requests found.</p>
    @else
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Duration</th>
                        <th>Days</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th>Requested On</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($leaveRequests as $req)
                    @php
                        $start = \Carbon\Carbon::parse($req->start_date);
                        $end = \Carbon\Carbon::parse($req->end_date);
                        $days = $start->diffInDays($end) + 1;
                    @endphp
                    <tr>
                        <td style="font-weight: 500;">{{ $req->leaveType->name }}</td>
                        <td>{{ $start->format('M d, Y') }} - {{ $end->format('M d, Y') }}</td>
                        <td>{{ $days }} Day(s)</td>
                        <td style="max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $req->reason }}">
                            {{ $req->reason }}
                        </td>
                        <td>
                            @if($req->status == 'Approved')
                                <span class="badge badge-success">Approved</span>
                            @elseif($req->status == 'Pending')
                                <span class="badge badge-warning">Pending</span>
                            @else
                                <span class="badge badge-danger">{{ $req->status }}</span>
                            @endif
                        </td>
                        <td style="color: var(--text-muted); font-size: 0.9rem;">{{ $req->created_at->format('M d, Y') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
