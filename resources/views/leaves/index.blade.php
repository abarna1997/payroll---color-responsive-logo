@extends('layouts.app')

@section('content')
<div class="row g-4">
    <!-- Wide Column: Log -->
    <div class="col-lg-8">
        <!-- List of Leave Requests -->
        <div class="glass-card mb-4" id="requests-section">
            <h5 class="mb-4 display-font"><i class="bi bi-calendar-check-fill me-2 text-indigo"></i> Leave Requests Log</h5>
            <div class="table-responsive">
                <table class="table custom-table">
                    <thead>
                        <tr>
                            <th>Employee ID</th>
                            <th>Employee Name</th>
                            <th>Leave Category</th>
                            <th>Duration</th>
                            <th>Reason</th>
                            <th>Status</th>
                            <th>Approver</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($leaveRequests as $r)
                            <tr>
                                <td><span class="badge bg-indigo text-light">{{ $r->employee->employee_id }}</span></td>
                                <td class="fw-semibold">{{ $r->employee->full_name }}</td>
                                <td>{{ $r->leaveType->name }}</td>
                                <td>
                                    <div class="text-light fs-8">{{ \Carbon\Carbon::parse($r->start_date)->format('Y-m-d') }}</div>
                                    <div class="text-secondary fs-8">to {{ \Carbon\Carbon::parse($r->end_date)->format('Y-m-d') }}</div>
                                </td>
                                <td>{{ $r->reason ?? 'N/A' }}</td>
                                <td>
                                    <span class="badge-status {{ $r->status === 'Approved' ? 'badge-online' : ($r->status === 'Pending' ? 'badge-pending' : 'badge-offline') }}">
                                        {{ $r->status }}
                                    </span>
                                </td>
                                <td>
                                    @if($r->approver)
                                        <span class="text-light fs-8">{{ $r->approver->username }}</span>
                                    @else
                                        <span class="text-secondary fs-8 italic">Unassigned</span>
                                    @endif
                                </td>
                                <td>
                                    @if($r->status === 'Pending')
                                        <div class="d-flex align-items-center">
                                            <form action="{{ route('leaves.request.approve', $r->id) }}" method="POST" class="m-0 me-1">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success border-0 px-2 py-1 fs-8">
                                                    <i class="bi bi-check-lg"></i> Approve
                                                </button>
                                            </form>
                                            <form action="{{ route('leaves.request.reject', $r->id) }}" method="POST" class="m-0">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-danger border-0 px-2 py-1 fs-8">
                                                    <i class="bi bi-x-lg"></i> Reject
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        <span class="text-secondary fs-8 italic">Processed</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-secondary">No leave requests filed yet. Use the panel to submit a request.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Narrow Column: Actions -->
    <div class="col-lg-4">
        <!-- Submit Leave Request -->
        <div class="glass-card mb-4">
            <h5 class="mb-4 display-font"><i class="bi bi-file-earmark-plus-fill me-2 text-indigo"></i> Request Leave</h5>
            <form action="{{ route('leaves.request.store') }}" method="POST">
                @csrf
                
                <div class="mb-3">
                    <label class="form-label text-secondary">Employee</label>
                    <select class="form-select form-select-custom" name="employee_id" required>
                        <option value="">Select Employee</option>
                        @foreach($employees as $e)
                            <option value="{{ $e->id }}">{{ $e->full_name }} ({{ $e->employee_id }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label text-secondary">Leave Category</label>
                    <select class="form-select form-select-custom" name="leave_type_id" required>
                        <option value="">Select Category</option>
                        @foreach($leaveTypes as $t)
                            @if($t->status === 'Active')
                                <option value="{{ $t->id }}">{{ $t->name }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>

                <div class="row mb-3">
                    <div class="col">
                        <label class="form-label text-secondary">Start Date</label>
                        <input type="date" class="form-control form-control-custom" name="start_date" required>
                    </div>
                    <div class="col">
                        <label class="form-label text-secondary">End Date</label>
                        <input type="date" class="form-control form-control-custom" name="end_date" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label text-secondary">Reason / Comments</label>
                    <textarea class="form-control form-control-custom" name="reason" rows="2" placeholder="Describe the reason for leave request..."></textarea>
                </div>

                <button type="submit" class="btn btn-custom-primary w-100 mt-1">
                    <i class="bi bi-send me-2"></i> Submit Leave Request
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
