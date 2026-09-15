@extends('portal.layout')

@section('content')
<div class="card">
    <div class="card-header">
        <h2 class="card-title"><i class="fa-solid fa-house-laptop"></i> Work From Home Requests</h2>
    </div>
    
    <div style="background: rgba(248, 250, 252, 0.5); padding: 1.5rem; border-radius: 8px; border: 1px solid var(--border); margin-bottom: 2rem;">
        <h3 style="margin-top: 0; font-size: 1.1rem;">Submit New Request</h3>
        <form action="{{ route('portal.wfh.store') }}" method="POST">
            @csrf
            <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 1.5rem; align-items: end;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="date" class="form-label">Date</label>
                    <input type="date" name="date" id="date" class="form-control" required min="{{ date('Y-m-d') }}">
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="reason" class="form-label">Reason</label>
                    <input type="text" name="reason" id="reason" class="form-control" required placeholder="Brief reason for WFH...">
                </div>
            </div>
            <div style="margin-top: 1.5rem; text-align: right;">
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-paper-plane"></i> Submit Request</button>
            </div>
        </form>
    </div>

    <h3 style="margin-top: 0; font-size: 1.1rem; margin-bottom: 1rem;">My Request History</h3>
    @if($requests->isEmpty())
        <p style="text-align: center; color: var(--text-muted); padding: 2rem 0; border: 1px dashed var(--border); border-radius: 8px;">No WFH requests found.</p>
    @else
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th>Requested On</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($requests as $req)
                    <tr>
                        <td><strong>{{ \Carbon\Carbon::parse($req->date)->format('M d, Y') }}</strong></td>
                        <td>{{ $req->reason }}</td>
                        <td>
                            @if($req->status == 'Approved')
                                <span class="badge badge-success">Approved</span>
                            @elseif($req->status == 'Pending')
                                <span class="badge badge-warning">Pending</span>
                            @else
                                <span class="badge badge-danger">{{ $req->status }}</span>
                            @endif
                        </td>
                        <td style="color: var(--text-muted); font-size: 0.9rem;">{{ $req->created_at->format('M d, h:i A') }}</td>
                        <td>
                            @if($req->status == 'Pending')
                                <form action="{{ route('portal.wfh.cancel', $req->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to cancel this request?');">
                                    @csrf
                                    <button type="submit" class="btn btn-outline" style="padding: 0.2rem 0.5rem; font-size: 0.75rem; color: var(--danger); border-color: var(--danger);">Cancel</button>
                                </form>
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
