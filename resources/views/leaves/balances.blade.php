@extends('layouts.app')

@section('content')
<div class="row g-4">
    <!-- Main Card Column -->
    <div class="col-12">
        <div class="glass-card">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="m-0 display-font">
                    <i class="bi bi-percent me-2 text-indigo"></i> Leave Balances & Usage Summary
                </h5>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-custom-primary" data-bs-toggle="modal" data-bs-target="#bulkAssignModal">
                        <i class="bi bi-plus-circle me-1"></i> Bulk Assign Leaves
                    </button>
                    <a href="{{ route('leaves') }}" class="btn btn-custom-secondary">
                        <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
                    </a>
                </div>
            </div>

            <!-- Balances Table -->
            <div class="table-responsive">
                <table class="table custom-table">
                    <thead>
                        <tr>
                            <th>Employee ID</th>
                            <th>Employee Name</th>
                            @foreach($leaveTypes as $t)
                                <th>{{ $t->name }}</th>
                            @endforeach
                            <th class="text-indigo">Total Taken</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $approvedRequests = $leaveRequests->where('status', 'Approved');
                        @endphp
                        @forelse($employees as $emp)
                            @php
                                $grandTotal = 0;
                            @endphp
                            <tr>
                                <td><span class="badge bg-indigo text-light">{{ $emp->employee_id }}</span></td>
                                <td class="fw-semibold text-white fs-6">{{ $emp->full_name }}</td>
                                @foreach($leaveTypes as $t)
                                    @php
                                        $empTypeRequests = $approvedRequests->where('employee_id', $emp->id)->where('leave_type_id', $t->id);
                                        $days = 0;
                                        foreach ($empTypeRequests as $req) {
                                            $start = \Carbon\Carbon::parse($req->start_date);
                                            $end = \Carbon\Carbon::parse($req->end_date);
                                            $days += $start->diffInDays($end) + 1;
                                        }
                                        $grandTotal += $days;
                                    @endphp
                                    <td>
                                        @if($days > 0)
                                            <span class="fw-bold text-light">{{ $days }} {{ Str::plural('day', $days) }}</span>
                                        @else
                                            <span class="text-secondary">—</span>
                                        @endif
                                    </td>
                                @endforeach
                                <td class="fw-bold text-indigo">
                                    {{ $grandTotal }} {{ Str::plural('day', $grandTotal) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ 3 + $leaveTypes->count() }}" class="text-center text-secondary py-4">No active employees found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Assign Leaves Modal -->
<div class="modal fade" id="bulkAssignModal" tabindex="-1" aria-labelledby="bulkAssignModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content glass-card p-0" >
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title display-font" id="bulkAssignModalLabel">
                    <i class="bi bi-layers-fill me-2 text-indigo"></i> Bulk Assign Leaves
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form action="{{ route('leaves.balances.bulk-assign') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row g-4">
                        <!-- Left Panel: Select Employees -->
                        <div class="col-md-6 border-end" style="border-color: var(--border-color) !important;">
                            <label class="form-label text-secondary fw-semibold mb-2">1. Select Employees</label>
                            
                            <!-- Search & Select Toggle -->
                            <div class="input-group mb-3">
                                <span class="input-group-text" style="background-color: var(--input-bg); border-color: var(--border-color); color: var(--text-secondary);">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input type="text" id="empSearchInput" class="form-control form-control-custom" placeholder="Search employee by name...">
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-2 px-1">
                                <span class="text-secondary fs-8 uppercase fw-bold">Employee Checklist</span>
                                <button type="button" id="toggleSelectAllBtn" class="btn btn-sm btn-outline-secondary border-0 py-0 px-2 fs-8 text-indigo">
                                    Select All
                                </button>
                            </div>

                            <!-- Scrollable Checklist -->
                            <div class="px-2 py-2 rounded" style="background-color: var(--input-bg); border: 1px solid var(--border-color); max-height: 250px; overflow-y: auto;">
                                <div id="employeeChecklist">
                                    @foreach($employees as $e)
                                        <div class="form-check employee-checkbox-wrapper py-1" data-name="{{ strtolower($e->full_name) }} {{ strtolower($e->employee_id) }}">
                                            <input class="form-check-input employee-checkbox" type="checkbox" name="employee_ids[]" value="{{ $e->id }}" id="empCheckbox{{ $e->id }}">
                                            <label class="form-check-label text-light fs-8.5 d-block" for="empCheckbox{{ $e->id }}">
                                                <span class="fw-semibold text-white">{{ $e->full_name }}</span> 
                                                <span class="text-secondary ms-1">({{ $e->employee_id }})</span>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <!-- Right Panel: Leave Fields -->
                        <div class="col-md-6">
                            <label class="form-label text-secondary fw-semibold mb-2">2. Leave Specifications</label>

                            <!-- Leave Category -->
                            <div class="mb-3">
                                <label class="form-label text-secondary fs-8 uppercase fw-bold mb-1">Leave Category</label>
                                <select class="form-select form-select-custom" name="leave_type_id" required>
                                    <option value="">Select Category</option>
                                    @foreach($leaveTypes as $t)
                                        @if($t->status === 'Active')
                                            <option value="{{ $t->id }}">{{ $t->name }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>

                            <!-- Dates Row -->
                            <div class="row mb-3">
                                <div class="col-6">
                                    <label class="form-label text-secondary fs-8 uppercase fw-bold mb-1">Start Date</label>
                                    <input type="date" class="form-control form-control-custom" name="start_date" required>
                                </div>
                                <div class="col-6">
                                    <label class="form-label text-secondary fs-8 uppercase fw-bold mb-1">End Date</label>
                                    <input type="date" class="form-control form-control-custom" name="end_date" required>
                                </div>
                            </div>

                            <!-- Status -->
                            <div class="mb-3">
                                <label class="form-label text-secondary fs-8 uppercase fw-bold mb-1">Status</label>
                                <select class="form-select form-select-custom" name="status" required>
                                    <option value="Approved" selected>Approved (Default)</option>
                                    <option value="Pending">Pending</option>
                                    <option value="Rejected">Rejected</option>
                                </select>
                            </div>

                            <!-- Reason / Comments -->
                            <div class="mb-3">
                                <label class="form-label text-secondary fs-8 uppercase fw-bold mb-1">Reason / Comments</label>
                                <textarea class="form-control form-control-custom" name="reason" rows="2" placeholder="Describe the reason for leave assignment..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer border-top-0 pt-0 pb-4 pe-4">
                    <button type="button" class="btn btn-custom-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-custom-primary px-4 py-2">
                        <i class="bi bi-send me-1"></i> Apply Bulk Assignment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // 1. Employee Search Filter
        const searchInput = document.getElementById('empSearchInput');
        const checkboxes = document.querySelectorAll('.employee-checkbox-wrapper');

        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const query = this.value.toLowerCase().trim();
                checkboxes.forEach(wrapper => {
                    const dataName = wrapper.getAttribute('data-name');
                    if (dataName.includes(query)) {
                        wrapper.style.display = 'block';
                    } else {
                        wrapper.style.display = 'none';
                    }
                });
            });
        }

        // 2. Select All Toggle
        const toggleAllBtn = document.getElementById('toggleSelectAllBtn');
        const employeeCheckboxes = document.querySelectorAll('.employee-checkbox');

        if (toggleAllBtn) {
            let allSelected = false;
            toggleAllBtn.addEventListener('click', function() {
                allSelected = !allSelected;
                
                // Only select/deselect visible checkboxes
                employeeCheckboxes.forEach(cb => {
                    const wrapper = cb.closest('.employee-checkbox-wrapper');
                    if (wrapper && wrapper.style.display !== 'none') {
                        cb.checked = allSelected;
                    }
                });

                toggleAllBtn.innerText = allSelected ? 'Deselect All' : 'Select All';
                toggleAllBtn.classList.toggle('text-indigo', !allSelected);
                toggleAllBtn.classList.toggle('text-danger', allSelected);
            });
        }
    });
</script>
@endsection
