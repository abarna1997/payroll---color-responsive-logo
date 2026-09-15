@extends('layouts.app')

@section('title', 'WFH Requests')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0 text-white"><i class="bi bi-house-laptop me-2"></i>Work From Home Requests</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newWfhModal">
            <i class="bi bi-plus-lg me-2"></i>New Request
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card bg-dark text-white border-secondary">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-dark table-hover mb-0 align-middle">
                    <thead class="table-active border-secondary">
                        <tr>
                            <th>Date</th>
                            <th>Employee</th>
                            <th>Reason</th>
                            <th>Status</th>
                            <th>Approved By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requests as $req)
                            <tr>
                                <td>{{ $req->date->format('M d, Y') }}</td>
                                <td>
                                    @if($req->employee)
                                        <div class="fw-bold">{{ $req->employee->first_name }} {{ $req->employee->last_name }}</div>
                                        <div class="text-secondary small">{{ $req->employee->employee_number }}</div>
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>{{ $req->reason }}</td>
                                <td>
                                    @if($req->status === 'APPROVED')
                                        <span class="badge bg-success">Approved</span>
                                    @elseif($req->status === 'REJECTED')
                                        <span class="badge bg-danger">Rejected</span>
                                        <div class="small text-danger mt-1">{{ $req->rejection_reason }}</div>
                                    @elseif($req->status === 'CANCELLED')
                                        <span class="badge bg-secondary">Cancelled</span>
                                    @else
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    @endif
                                </td>
                                <td>{{ $req->approver ? $req->approver->username : '-' }}</td>
                                <td>
                                    @if($req->status === 'PENDING')
                                        @if(in_array(Auth::user()->role, ['Super Administrator', 'HR Administrator', 'Manager']))
                                            <form action="{{ route('wfh.approve', $req->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success" title="Approve"><i class="bi bi-check-circle"></i></button>
                                            </form>
                                            <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal-{{ $req->id }}" title="Reject"><i class="bi bi-x-circle"></i></button>

                                            <!-- Reject Modal -->
                                            <div class="modal fade text-dark" id="rejectModal-{{ $req->id }}" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <form action="{{ route('wfh.reject', $req->id) }}" method="POST">
                                                            @csrf
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Reject WFH Request</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <div class="mb-3">
                                                                    <label class="form-label">Reason for Rejection</label>
                                                                    <textarea name="rejection_reason" class="form-control" required rows="3"></textarea>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                                <button type="submit" class="btn btn-danger">Reject</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        @if($req->employee_id === $employee?->id)
                                            <form action="{{ route('wfh.cancel', $req->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-secondary" onclick="return confirm('Cancel this request?')">Cancel</button>
                                            </form>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-secondary py-4">No WFH requests found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- New Request Modal -->
<div class="modal fade text-dark" id="newWfhModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('wfh.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Submit WFH Request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @if(in_array(Auth::user()->role, ['Super Administrator', 'HR Administrator', 'Manager']))
                    <div class="mb-3">
                        <label class="form-label">Employee</label>
                        <select name="employee_id" class="form-select" required>
                            <option value="">Select Employee</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->first_name }} {{ $emp->last_name }} ({{ $emp->employee_number }})</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    <div class="mb-3">
                        <label class="form-label">Date</label>
                        <input type="date" name="date" class="form-control" required min="{{ date('Y-m-d') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reason</label>
                        <textarea name="reason" class="form-control" required rows="3" placeholder="Briefly explain why you need to work from home."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Submit Request</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
