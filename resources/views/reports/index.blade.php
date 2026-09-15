@extends('layouts.app')

@section('content')
<div class="row g-4">
    <!-- Main Report Container -->
    <div class="col-lg-12">
        <div class="glass-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0 display-font"><i class="bi bi-file-earmark-bar-graph me-2 text-indigo"></i> Report Center</h4>
            </div>

            <div class="row">
                <!-- Sidebar Navigation for Report Types -->
                <div class="col-md-3 border-end">
                    <h6 class="text-muted fw-bold mb-3 fs-7 text-uppercase">Report Categories</h6>
                    
                    <div class="accordion" id="reportCategories">
                        <!-- Attendance -->
                        <div class="accordion-item border-0 bg-transparent mb-2">
                            <h2 class="accordion-header">
                                <button class="accordion-button {{ in_array($reportType, ['daily', 'monthly', 'history', 'department', 'branch', 'company']) ? '' : 'collapsed' }} bg-transparent fw-bold shadow-none p-2" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAttendance">
                                    <i class="bi bi-journal-check me-2 text-indigo"></i> Attendance
                                </button>
                            </h2>
                            <div id="collapseAttendance" class="accordion-collapse collapse {{ in_array($reportType, ['daily', 'monthly', 'history', 'department', 'branch', 'company', '']) ? 'show' : '' }}" data-bs-parent="#reportCategories">
                                <div class="accordion-body p-0 pt-2 ms-4">
                                    <ul class="nav flex-column gap-1">
                                        <li class="nav-item"><a href="#" class="nav-link fs-7 px-2 py-1 rounded select-report {{ $reportType === 'daily' ? 'active bg-indigo text-white' : 'text-dark' }}" data-type="daily">Daily Attendance</a></li>
                                        <li class="nav-item"><a href="#" class="nav-link fs-7 px-2 py-1 rounded select-report {{ $reportType === 'monthly' ? 'active bg-indigo text-white' : 'text-dark' }}" data-type="monthly">Monthly Attendance</a></li>
                                        <li class="nav-item"><a href="#" class="nav-link fs-7 px-2 py-1 rounded select-report {{ $reportType === 'history' ? 'active bg-indigo text-white' : 'text-dark' }}" data-type="history">Employee History</a></li>
                                        <li class="nav-item"><a href="#" class="nav-link fs-7 px-2 py-1 rounded select-report {{ $reportType === 'department' ? 'active bg-indigo text-white' : 'text-dark' }}" data-type="department">Department Attendance</a></li>
                                        <li class="nav-item"><a href="#" class="nav-link fs-7 px-2 py-1 rounded select-report {{ $reportType === 'branch' ? 'active bg-indigo text-white' : 'text-dark' }}" data-type="branch">Branch Attendance</a></li>
                                        <li class="nav-item"><a href="#" class="nav-link fs-7 px-2 py-1 rounded select-report {{ $reportType === 'company' ? 'active bg-indigo text-white' : 'text-dark' }}" data-type="company">Company Attendance</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Exceptions -->
                        <div class="accordion-item border-0 bg-transparent mb-2">
                            <h2 class="accordion-header">
                                <button class="accordion-button {{ in_array($reportType, ['late', 'early_out', 'absent', 'exceptions']) ? '' : 'collapsed' }} bg-transparent fw-bold shadow-none p-2" type="button" data-bs-toggle="collapse" data-bs-target="#collapseExceptions">
                                    <i class="bi bi-exclamation-triangle me-2 text-warning"></i> Exceptions
                                </button>
                            </h2>
                            <div id="collapseExceptions" class="accordion-collapse collapse {{ in_array($reportType, ['late', 'early_out', 'absent', 'exceptions']) ? 'show' : '' }}" data-bs-parent="#reportCategories">
                                <div class="accordion-body p-0 pt-2 ms-4">
                                    <ul class="nav flex-column gap-1">
                                        <li class="nav-item"><a href="#" class="nav-link fs-7 px-2 py-1 rounded select-report {{ $reportType === 'late' ? 'active bg-indigo text-white' : 'text-dark' }}" data-type="late">Late Attendance</a></li>
                                        <li class="nav-item"><a href="#" class="nav-link fs-7 px-2 py-1 rounded select-report {{ $reportType === 'early_out' ? 'active bg-indigo text-white' : 'text-dark' }}" data-type="early_out">Early Out Report</a></li>
                                        <li class="nav-item"><a href="#" class="nav-link fs-7 px-2 py-1 rounded select-report {{ $reportType === 'absent' ? 'active bg-indigo text-white' : 'text-dark' }}" data-type="absent">Absent Employees</a></li>
                                        <li class="nav-item"><a href="#" class="nav-link fs-7 px-2 py-1 rounded select-report {{ $reportType === 'exceptions' ? 'active bg-indigo text-white' : 'text-dark' }}" data-type="exceptions">Attendance Exceptions</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Overtime -->
                        <div class="accordion-item border-0 bg-transparent mb-2">
                            <h2 class="accordion-header">
                                <button class="accordion-button {{ $reportType === 'overtime' ? '' : 'collapsed' }} bg-transparent fw-bold shadow-none p-2" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOvertime">
                                    <i class="bi bi-clock-history me-2 text-success"></i> Overtime
                                </button>
                            </h2>
                            <div id="collapseOvertime" class="accordion-collapse collapse {{ $reportType === 'overtime' ? 'show' : '' }}" data-bs-parent="#reportCategories">
                                <div class="accordion-body p-0 pt-2 ms-4">
                                    <ul class="nav flex-column gap-1">
                                        <li class="nav-item"><a href="#" class="nav-link fs-7 px-2 py-1 rounded select-report {{ $reportType === 'overtime' ? 'active bg-indigo text-white' : 'text-dark' }}" data-type="overtime">Overtime Report</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Devices -->
                        <div class="accordion-item border-0 bg-transparent mb-2">
                            <h2 class="accordion-header">
                                <button class="accordion-button {{ $reportType === 'device' ? '' : 'collapsed' }} bg-transparent fw-bold shadow-none p-2" type="button" data-bs-toggle="collapse" data-bs-target="#collapseDevices">
                                    <i class="bi bi-hdd-network me-2 text-primary"></i> Devices
                                </button>
                            </h2>
                            <div id="collapseDevices" class="accordion-collapse collapse {{ $reportType === 'device' ? 'show' : '' }}" data-bs-parent="#reportCategories">
                                <div class="accordion-body p-0 pt-2 ms-4">
                                    <ul class="nav flex-column gap-1">
                                        <li class="nav-item"><a href="#" class="nav-link fs-7 px-2 py-1 rounded select-report {{ $reportType === 'device' ? 'active bg-indigo text-white' : 'text-dark' }}" data-type="device">Device Attendance</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Raw Data -->
                        <div class="accordion-item border-0 bg-transparent mb-2">
                            <h2 class="accordion-header">
                                <button class="accordion-button {{ $reportType === 'transaction' ? '' : 'collapsed' }} bg-transparent fw-bold shadow-none p-2" type="button" data-bs-toggle="collapse" data-bs-target="#collapseRaw">
                                    <i class="bi bi-code-square me-2 text-danger"></i> Raw Data
                                </button>
                            </h2>
                            <div id="collapseRaw" class="accordion-collapse collapse {{ $reportType === 'transaction' ? 'show' : '' }}" data-bs-parent="#reportCategories">
                                <div class="accordion-body p-0 pt-2 ms-4">
                                    <ul class="nav flex-column gap-1">
                                        <li class="nav-item">
                                            <a href="#" class="nav-link fs-7 px-2 py-1 rounded select-report {{ $reportType === 'transaction' ? 'active bg-indigo text-white' : 'text-dark' }}" data-type="transaction">
                                                Transaction Report
                                                <div class="text-muted fs-9 mt-1" style="line-height:1.1;">Unprocessed biometric/web punch records.</div>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Filters & Results Area -->
                <div class="col-md-9">
                    <form action="{{ route('reports') }}" method="GET" id="reportForm" class="row g-3">
                        <input type="hidden" name="report_type" id="reportTypeInput" value="{{ $reportType ?: 'daily' }}">
                        
                        <div class="col-12">
                            <h6 class="text-muted fw-bold mb-3 fs-7 text-uppercase">Data Filters</h6>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label text-secondary fs-7">Company</label>
                            <select class="form-select form-select-custom" name="company_id" id="companyFilter">
                                <option value="">All Companies</option>
                                @foreach($companies as $c)
                                    <option value="{{ $c->id }}" {{ request('company_id') == $c->id ? 'selected' : '' }}>{{ $c->company_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label text-secondary fs-7">Branch</label>
                            <select class="form-select form-select-custom dependent-filter" name="branch_id" id="branchFilter" data-parent="companyFilter" data-field="company_id">
                                <option value="">All Branches</option>
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}" data-company_id="{{ $b->company_id }}" {{ request('branch_id') == $b->id ? 'selected' : '' }}>{{ $b->branch_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label text-secondary fs-7">Department</label>
                            <select class="form-select form-select-custom dependent-filter" name="department_id" id="departmentFilter" data-parent="branchFilter" data-field="branch_id">
                                <option value="">All Depts</option>
                                @foreach($departments as $d)
                                    <option value="{{ $d->id }}" data-branch_id="{{ $d->branch_id }}" {{ request('department_id') == $d->id ? 'selected' : '' }}>{{ $d->department_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label text-secondary fs-7">Employee</label>
                            <select class="form-select form-select-custom dependent-filter" name="employee_id" id="employeeFilter" data-parent="departmentFilter" data-field="department_id">
                                <option value="">All Employees</option>
                                @foreach($employees as $e)
                                    <option value="{{ $e->id }}" data-department_id="{{ $e->department_id }}" {{ request('employee_id') == $e->id ? 'selected' : '' }}>{{ $e->full_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-secondary fs-7">Date Range (or Month scope)</label>
                            <div class="d-flex gap-2">
                                <input type="date" class="form-control form-control-custom" name="start_date" value="{{ request('start_date', \Carbon\Carbon::today()->toDateString()) }}">
                                <input type="date" class="form-control form-control-custom" name="end_date" value="{{ request('end_date', \Carbon\Carbon::today()->toDateString()) }}">
                            </div>
                        </div>

                        <div class="col-12 d-flex justify-content-end mt-4 pt-2 border-top gap-2">
                            <a href="{{ route('reports') }}" class="btn btn-sm btn-custom-secondary"><i class="bi bi-arrow-counterclockwise"></i> Reset Filters</a>
                            <button type="submit" class="btn btn-sm btn-custom-primary" id="btnGenerate">
                                <span class="normal-state"><i class="bi bi-gear-wide-connected me-1"></i> Generate Report</span>
                                <span class="loading-state d-none"><span class="spinner-border spinner-border-sm me-1"></span> Generating...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Results Table -->
    @if($reportType)
        <div class="col-lg-12 print-section mt-4">
            <div class="glass-card">
                <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                    <div>
                        <h5 class="m-0 display-font text-indigo text-uppercase"><i class="bi bi-table me-2"></i> {{ str_replace('_', ' ', $reportType) }} Report</h5>
                        <div class="fs-8 text-secondary mt-1">
                            Scope: {{ request('start_date', \Carbon\Carbon::today()->toDateString()) }} to {{ request('end_date', \Carbon\Carbon::today()->toDateString()) }}
                            <span class="ms-3"><i class="bi bi-buildings"></i> Company: {{ request('company_id') ? $companies->where('id', request('company_id'))->first()->company_name : 'All' }}</span>
                            <span class="ms-3"><i class="bi bi-clock-history"></i> Generated: {{ now()->format('Y-m-d H:i') }}</span>
                        </div>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="button" onclick="window.print()" class="btn btn-sm btn-outline-secondary bg-white"><i class="bi bi-printer me-1"></i> Print</button>
                        <form action="{{ route('reports.export') }}" method="GET" class="m-0 p-0 d-inline">
                            @foreach(request()->query() as $key => $val)
                                <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                            @endforeach
                            <button type="submit" class="btn btn-sm btn-outline-info bg-white"><i class="bi bi-file-earmark-spreadsheet me-1"></i> Export CSV</button>
                        </form>
                    </div>
                </div>

                @if($reportType === 'exceptions')
                    <!-- Exceptions Layout -->
                    @if(count($data['unassigned']) == 0 && count($data['late_early']) == 0 && count($data['errors']) == 0)
                        <div class="text-center py-5">
                            <i class="bi bi-check-circle text-success fs-1 opacity-50 mb-3 d-block"></i>
                            <h6 class="text-secondary">No exception records found for the selected filters.</h6>
                        </div>
                    @else
                        <!-- Unassigned -->
                        @if(count($data['unassigned']) > 0)
                        <div class="mb-5">
                            <h6 class="display-font text-warning mb-3"><i class="bi bi-exclamation-triangle-fill me-1"></i> Unassigned Punch Logs (Biometric Logs with Unregistered PINs)</h6>
                            <div class="table-responsive">
                                <table class="table custom-table">
                                    <thead>
                                        <tr>
                                            <th>Employee ID</th>
                                            <th>Name</th>
                                            <th>Date</th>
                                            <th>Time</th>
                                            <th>Remarks</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($data['unassigned'] as $row)
                                            <tr>
                                                <td>{{ $row->employee_id }}</td>
                                                <td>{{ $row->employee_name }}</td>
                                                <td>{{ $row->date }}</td>
                                                <td>{{ $row->check_in_time }}</td>
                                                <td><span class="badge bg-warning text-dark">{{ $row->remarks }}</span></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @endif

                        <!-- Late / Early -->
                        @if(count($data['late_early']) > 0)
                        <div class="mb-5">
                            <h6 class="display-font text-warning mb-3"><i class="bi bi-clock-history me-1"></i> Late, Early Out & Missing Punches</h6>
                            <div class="table-responsive">
                                <table class="table custom-table">
                                    <thead>
                                        <tr>
                                            <th>Employee ID</th>
                                            <th>Name</th>
                                            <th>Date</th>
                                            <th>Check-In</th>
                                            <th>Check-Out</th>
                                            <th>Remarks</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($data['late_early'] as $row)
                                            <tr>
                                                <td class="fw-bold">{{ $row->employee_id }}</td>
                                                <td>{{ $row->employee_name }}</td>
                                                <td>{{ $row->date }}</td>
                                                <td>{{ $row->check_in_time }}</td>
                                                <td>{{ $row->check_out_time }}</td>
                                                <td><span class="badge bg-danger">{{ $row->remarks }}</span></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @endif
                        
                        <!-- Device Errors -->
                        @if(count($data['errors']) > 0)
                        <div class="mb-5">
                            <h6 class="display-font text-danger mb-3"><i class="bi bi-hdd-network me-1"></i> Hardware Errors</h6>
                            <div class="table-responsive">
                                <table class="table custom-table">
                                    <thead>
                                        <tr>
                                            <th>Device</th>
                                            <th>Date</th>
                                            <th>Time</th>
                                            <th>Error</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($data['errors'] as $row)
                                            <tr>
                                                <td class="fw-bold">{{ $row->employee_name }}</td>
                                                <td>{{ $row->date }}</td>
                                                <td>{{ $row->check_in_time }}</td>
                                                <td><span class="text-danger">{{ $row->remarks }}</span></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @endif
                    @endif

                @elseif($reportType === 'transaction')
                    <!-- Transaction Layout -->
                    @if(count($data) === 0)
                        <div class="text-center py-5">
                            <i class="bi bi-inbox text-secondary fs-1 opacity-50 mb-3 d-block"></i>
                            <h6 class="text-secondary">No raw transactions found for the selected filters.</h6>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table custom-table">
                                <thead>
                                    <tr>
                                        <th>Employee</th>
                                        <th>Company / Dept</th>
                                        <th>Timestamp</th>
                                        <th>Device</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($data as $row)
                                        <tr>
                                            <td>
                                                <div class="fw-bold text-indigo">{{ $row->employee ? $row->employee->employee_id : 'N/A' }}</div>
                                                <div class="fs-8 text-secondary">{{ $row->employee ? $row->employee->full_name : 'N/A' }}</div>
                                            </td>
                                            <td>
                                                <div class="fs-8 fw-semibold">{{ $row->employee && $row->employee->company ? $row->employee->company->company_name : 'N/A' }}</div>
                                                <div class="fs-9 text-muted">{{ $row->employee && $row->employee->department ? $row->employee->department->department_name : 'N/A' }}</div>
                                            </td>
                                            <td class="fw-bold">{{ $row->attendance_timestamp }}</td>
                                            <td>{{ $row->device ? $row->device->name : 'N/A' }}</td>
                                            <td>
                                                <span class="badge {{ strtolower($row->attendance_status) == 'success' ? 'bg-success' : 'bg-warning text-dark' }}">
                                                    {{ $row->attendance_status }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                @else
                    <!-- Standard Aggregated Layout -->
                    @if(count($data) === 0)
                        <div class="text-center py-5">
                            <i class="bi bi-inbox text-secondary fs-1 opacity-50 mb-3 d-block"></i>
                            <h6 class="text-secondary">No records found for the selected filters.</h6>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table custom-table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Employee</th>
                                        <th>Org / Dept</th>
                                        <th>Date</th>
                                        <th>Check In</th>
                                        <th>Check Out</th>
                                        <th>Hours</th>
                                        <th>OT</th>
                                        <th>Late/Early</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($data as $row)
                                        <tr>
                                            <td>
                                                <div class="fw-bold text-indigo">{{ $row->employee_id }}</div>
                                                <div class="fs-8 text-secondary">{{ $row->employee_name }}</div>
                                            </td>
                                            <td>
                                                <div class="fs-8 fw-semibold">{{ $row->company }} ({{ $row->branch }})</div>
                                                <div class="fs-9 text-muted">{{ $row->department }}</div>
                                            </td>
                                            <td class="fw-bold">{{ $row->date }}</td>
                                            <td>
                                                <div class="fw-bold">{{ $row->check_in_time }}</div>
                                                <span class="badge {{ $row->check_in_status == 'Late' ? 'bg-danger' : ($row->check_in_status == 'Early' ? 'bg-warning text-dark' : 'bg-success') }} fs-9">{{ $row->check_in_status }}</span>
                                            </td>
                                            <td>
                                                <div class="fw-bold">{{ $row->check_out_time }}</div>
                                                <span class="badge {{ $row->check_out_status == 'Early Out' ? 'bg-warning text-dark' : ($row->check_out_status == 'Late Out' ? 'bg-info text-dark' : 'bg-success') }} fs-9">{{ $row->check_out_status }}</span>
                                            </td>
                                            <td class="fw-bold text-primary">{{ $row->working_hours }}</td>
                                            <td class="fw-bold text-success">{{ $row->ot_hours }}</td>
                                            <td>
                                                @if($row->late_minutes > 0)
                                                    <div class="text-danger fs-8"><i class="bi bi-arrow-down-right"></i> {{ $row->late_minutes }}m L</div>
                                                @endif
                                                @if($row->early_out_minutes > 0)
                                                    <div class="text-warning fs-8"><i class="bi bi-arrow-up-left"></i> {{ $row->early_out_minutes }}m EO</div>
                                                @endif
                                                @if($row->late_minutes == 0 && $row->early_out_minutes == 0)
                                                    <span class="text-secondary fs-8">--</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($row->final_status === 'PRESENT')
                                                    <span class="badge bg-success">Present</span>
                                                @elseif($row->final_status === 'ABSENT')
                                                    <span class="badge bg-danger">Absent</span>
                                                @elseif($row->final_status === 'LEAVE')
                                                    <span class="badge bg-info text-dark">Leave</span>
                                                @elseif($row->final_status === 'OFF')
                                                    <span class="badge bg-secondary">Off</span>
                                                @elseif($row->final_status === 'HOLIDAY')
                                                    <span class="badge bg-primary">Holiday</span>
                                                @else
                                                    <span class="badge bg-warning text-dark">{{ $row->final_status }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Report Type Selection
        const reportLinks = document.querySelectorAll('.select-report');
        const reportTypeInput = document.getElementById('reportTypeInput');
        const reportForm = document.getElementById('reportForm');
        
        reportLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const type = this.getAttribute('data-type');
                reportTypeInput.value = type;
                
                // Show loading state
                const btn = document.getElementById('btnGenerate');
                btn.querySelector('.normal-state').classList.add('d-none');
                btn.querySelector('.loading-state').classList.remove('d-none');
                btn.disabled = true;
                
                reportForm.submit();
            });
        });

        // Form Submission Loading State
        reportForm.addEventListener('submit', function() {
            const btn = document.getElementById('btnGenerate');
            btn.querySelector('.normal-state').classList.add('d-none');
            btn.querySelector('.loading-state').classList.remove('d-none');
            btn.disabled = true;
        });

        // Dependent Filters Logic
        const filters = {
            companyFilter: document.getElementById('companyFilter'),
            branchFilter: document.getElementById('branchFilter'),
            departmentFilter: document.getElementById('departmentFilter'),
            employeeFilter: document.getElementById('employeeFilter')
        };

        function filterOptions(childSelect, parentValue, dataField) {
            const options = childSelect.querySelectorAll('option:not([value=""])');
            let hasVisibleOptions = false;

            options.forEach(opt => {
                if (!parentValue || opt.getAttribute('data-' + dataField) === parentValue) {
                    opt.style.display = '';
                    hasVisibleOptions = true;
                } else {
                    opt.style.display = 'none';
                    if (opt.selected) {
                        opt.selected = false;
                        childSelect.value = "";
                    }
                }
            });
        }

        // Apply filters top-down on change
        filters.companyFilter.addEventListener('change', function() {
            filterOptions(filters.branchFilter, this.value, 'company_id');
            // Trigger branch change to cascade to department
            filters.branchFilter.dispatchEvent(new Event('change'));
        });

        filters.branchFilter.addEventListener('change', function() {
            filterOptions(filters.departmentFilter, this.value, 'branch_id');
            // Trigger department change to cascade to employee
            filters.departmentFilter.dispatchEvent(new Event('change'));
        });

        filters.departmentFilter.addEventListener('change', function() {
            filterOptions(filters.employeeFilter, this.value, 'department_id');
        });

        // Initialize state on page load based on current selections
        if(filters.companyFilter.value) {
            filterOptions(filters.branchFilter, filters.companyFilter.value, 'company_id');
        }
        if(filters.branchFilter.value) {
            filterOptions(filters.departmentFilter, filters.branchFilter.value, 'branch_id');
        }
        if(filters.departmentFilter.value) {
            filterOptions(filters.employeeFilter, filters.departmentFilter.value, 'department_id');
        }
    });
</script>
<style>
    /* Print specific styles */
    @media print {
        body { background: white; margin: 0; padding: 0; }
        .navbar-top, .main-container > .row > .col-md-3, #reportForm, .btn, .glass-card { 
            box-shadow: none !important; border: none !important; background: transparent !important;
        }
        .main-container > .row > .col-md-3 { display: none !important; }
        #reportForm { display: none !important; }
        .col-md-9 { width: 100% !important; flex: 0 0 100% !important; max-width: 100% !important; }
        .print-section { width: 100% !important; padding: 0 !important; margin: 0 !important; }
        .glass-card { padding: 0 !important; }
        .table { width: 100% !important; font-size: 10pt !important; }
        .badge { border: 1px solid #ccc; color: black !important; background: white !important; }
        @page { size: landscape; margin: 1cm; }
    }
</style>
@endsection
