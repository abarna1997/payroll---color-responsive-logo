@extends('layouts.app')

@section('content')
<div class="row g-4">
    <!-- List of Correction Requests -->
    <div class="col-lg-8">
        <div class="glass-card">
            <h5 class="mb-4 display-font"><i class="bi bi-patch-check me-2 text-indigo"></i> Attendance Correction Logs</h5>
            <div class="table-responsive">
                <table class="table custom-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Employee</th>
                            <th>Correction Date</th>
                            <th>Request Type</th>
                            <th>Proposed Punch</th>
                            <th>Reason</th>
                            <th>Status</th>
                            <th>Approved By & Remarks</th>
                            @if(in_array(Auth::user()->role, ['Super Administrator', 'HR Administrator', 'Manager']))
                                <th>Action Panel</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($corrections as $corr)
                            <tr>
                                <td><span class="text-secondary">#{{ $corr->id }}</span></td>
                                <td>
                                    <div class="fw-semibold">{{ $corr->employee->full_name }}</div>
                                    <div class="text-secondary fs-8">ID: {{ $corr->employee->employee_id }}</div>
                                </td>
                                <td>{{ $corr->request_date->format('Y-m-d') }}</td>
                                <td><span class="badge bg-secondary">{{ $corr->request_type }}</span></td>
                                <td>
                                    <div class="text-light">
                                        @if($corr->check_in) <span class="badge bg-success">In: {{ Carbon\Carbon::parse($corr->check_in)->format('H:i') }}</span> @endif
                                        @if($corr->check_out) <span class="badge bg-danger">Out: {{ Carbon\Carbon::parse($corr->check_out)->format('H:i') }}</span> @endif
                                    </div>
                                </td>
                                <td style="max-width: 150px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $corr->reason }}">
                                    {{ $corr->reason }}
                                </td>
                                <td>
                                    <span class="badge-status {{ $corr->status === 'Approved' ? 'badge-online' : ($corr->status === 'Pending' ? 'badge-pending' : 'badge-offline') }}">
                                        {{ $corr->status }}
                                    </span>
                                </td>
                                <td>
                                    @if($corr->status !== 'Pending')
                                        <div class="text-light fs-8">By: {{ $corr->approvedBy ? $corr->approvedBy->username : 'System' }}</div>
                                        <div class="text-secondary fs-8">Remarks: {{ $corr->remarks ?? 'None' }}</div>
                                    @else
                                        <span class="text-secondary fs-8">Awaiting approval</span>
                                    @endif
                                </td>
                                @if(in_array(Auth::user()->role, ['Super Administrator', 'HR Administrator', 'Manager']))
                                    <td>
                                        @if($corr->status === 'Pending')
                                            <div class="d-flex gap-1">
                                                <button class="btn btn-xs btn-success py-1 px-2 fs-8 rounded" data-bs-toggle="modal" data-bs-target="#appModal{{ $corr->id }}"><i class="bi bi-check"></i> Approve</button>
                                                <button class="btn btn-xs btn-danger py-1 px-2 fs-8 rounded" data-bs-toggle="modal" data-bs-target="#rejModal{{ $corr->id }}"><i class="bi bi-x"></i> Reject</button>
                                            </div>
                                        @else
                                            <span class="text-secondary fs-8">Processed</span>
                                        @endif
                                    </td>
                                @endif
                            </tr>


                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-secondary">No correction requests logged in the system.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Create Correction Form -->
    <div class="col-lg-4">
        <div class="glass-card">
            <h5 class="mb-4 display-font"><i class="bi bi-plus-circle-fill me-2 text-indigo"></i> Request Correction</h5>
            <form action="{{ route('manual-logs.store') }}" method="POST">
                @csrf
                
                <div class="mb-3">
                    <label class="form-label text-secondary">Select Employee</label>
                    <select class="form-select form-select-custom" name="employee_id" required>
                        <option value="">Select Employee</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->employee_id }} - {{ $emp->full_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label text-secondary">Correction Type</label>
                    <select class="form-select form-select-custom" name="request_type" required>
                        <option value="Forgot Check-In">Forgot Check-In</option>
                        <option value="Forgot Check-Out">Forgot Check-Out</option>
                        <option value="Incorrect Attendance">Incorrect Attendance</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label text-secondary">Target Date</label>
                    <input type="date" class="form-control form-control-custom" name="request_date" value="{{ date('Y-m-d') }}" required>
                </div>

                <div class="row mb-3">
                    <div class="col">
                        <label class="form-label text-secondary">Check-In Time</label>
                        <input type="time" class="form-control form-control-custom" name="check_in">
                    </div>
                    <div class="col">
                        <label class="form-label text-secondary">Check-Out Time</label>
                        <input type="time" class="form-control form-control-custom" name="check_out">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label text-secondary">Reason / Explanation</label>
                    <textarea class="form-control form-control-custom" name="reason" rows="4" placeholder="Detail reason for manual log adjustment request..." required></textarea>
                </div>

                <button type="submit" class="btn btn-custom-primary w-100 mt-2">
                    <i class="bi bi-send-fill me-2"></i> Submit Request
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Modals must be outside glass-cards to prevent stacking context issues -->
@foreach($corrections as $corr)
    @if($corr->status === 'Pending' && in_array(Auth::user()->role, ['Super Administrator', 'HR Administrator', 'Manager']))
        <!-- Approve Remark Modal -->
        <div class="modal fade" id="appModal{{ $corr->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content p-0 glass-card" style="color: white; border: 1px solid var(--border);">
                    <div class="modal-header border-bottom-0 pb-0">
                        <h5 class="modal-title display-font text-success"><i class="bi bi-check-circle-fill me-2"></i> Approve Correction Request</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('manual-logs.approve', $corr->id) }}" method="POST">
                        @csrf
                        <div class="modal-body py-4">
                            <div class="mb-3">
                                <label class="form-label text-light fs-8">Remarks / Approver Notes</label>
                                <input type="text" class="form-control form-control-custom" name="remarks" placeholder="e.g. Approved. Verified with supervisor." required>
                            </div>
                        </div>
                        <div class="modal-footer border-top-0 pt-0">
                            <button type="button" class="btn btn-sm btn-custom-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-sm btn-success">Approve Request</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Reject Remark Modal -->
        <div class="modal fade" id="rejModal{{ $corr->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content p-0 glass-card" style="color: white; border: 1px solid var(--border);">
                    <div class="modal-header border-bottom-0 pb-0">
                        <h5 class="modal-title display-font text-danger"><i class="bi bi-x-circle-fill me-2"></i> Reject Correction Request</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('manual-logs.reject', $corr->id) }}" method="POST">
                        @csrf
                        <div class="modal-body py-4">
                            <div class="mb-3">
                                <label class="form-label text-light fs-8">Reason for Rejection</label>
                                <input type="text" class="form-control form-control-custom" name="remarks" placeholder="e.g. Rejected. No corroborating entry records." required>
                            </div>
                        </div>
                        <div class="modal-footer border-top-0 pt-0">
                            <button type="button" class="btn btn-sm btn-custom-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-sm btn-danger">Reject Request</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endforeach

@endsection
