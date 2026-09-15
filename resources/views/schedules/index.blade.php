@extends('layouts.app')

@section('title', 'Schedule Management')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3 mb-0 text-gray-800"><i class="bi bi-calendar-range text-indigo me-2"></i>Schedule Management</h2>
        <div class="d-flex gap-2">
            <!-- Desktop Buttons -->
            <div class="d-none d-md-flex gap-2">
                <button class="btn btn-custom-secondary" data-bs-toggle="modal" data-bs-target="#bulkAssignmentModal">
                    <i class="bi bi-collection"></i> Bulk Assign
                </button>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#assignmentModal">
                    <i class="bi bi-calendar-plus"></i> Assign Shift
                </button>
                <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#overrideModal">
                    <i class="bi bi-exclamation-triangle"></i> Daily Override
                </button>
                <button class="btn btn-info text-white" data-bs-toggle="modal" data-bs-target="#weeklyScheduleModal">
                    <i class="bi bi-arrow-repeat"></i> Weekly Schedule
                </button>
            </div>
            
            <!-- Mobile Dropdown -->
            <div class="dropdown d-md-none">
                <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-gear me-1"></i> Manage
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                    <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#bulkAssignmentModal"><i class="bi bi-collection me-2"></i>Bulk Assign</a></li>
                    <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#assignmentModal"><i class="bi bi-calendar-plus me-2 text-primary"></i>Assign Shift</a></li>
                    <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#overrideModal"><i class="bi bi-exclamation-triangle me-2 text-warning"></i>Daily Override</a></li>
                    <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#weeklyScheduleModal"><i class="bi bi-arrow-repeat me-2 text-info"></i>Weekly Schedule</a></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Mobile Filter Toggle -->
    <div class="d-block d-md-none mb-3">
        <button class="btn btn-outline-secondary w-100 d-flex justify-content-between align-items-center" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse">
            <span><i class="bi bi-funnel me-2"></i> Filters & View Options</span>
            <i class="bi bi-chevron-down"></i>
        </button>
    </div>

    <!-- View Mode & Filters Section -->
    <div class="collapse d-md-block" id="filterCollapse">
        <div class="card shadow-sm mb-4 border-0">
            <div class="card-header bg-white border-bottom-0 pt-3 pb-0 d-flex justify-content-between align-items-center flex-wrap">
                <div class="btn-group shadow-sm mb-2" role="group">
                    <input type="radio" class="btn-check" name="view_mode" id="viewModeOverview" value="overview" autocomplete="off" checked>
                    <label class="btn btn-outline-primary fw-bold" for="viewModeOverview"><i class="bi bi-grid me-1"></i> Overview</label>

                    <input type="radio" class="btn-check" name="view_mode" id="viewModeEmployees" value="employees" autocomplete="off">
                    <label class="btn btn-outline-primary fw-bold" for="viewModeEmployees"><i class="bi bi-people me-1"></i> Employees</label>
                </div>
                
                <div class="mb-2 text-end">
                    <span class="badge bg-light text-dark border px-2 py-1 fs-6">Employees in Scope: <span id="scopeCount">0</span></span>
                    <span class="badge bg-light text-dark border px-2 py-1 fs-6 ms-2">Selected: <span id="selectedCount">0</span></span>
                    <button class="btn btn-sm btn-outline-primary ms-2 d-none" id="btnManageEmployees" data-bs-toggle="modal" data-bs-target="#employeeSelectionModal">
                        <i class="bi bi-person-lines-fill me-1"></i> Manage Selection
                    </button>
                </div>
            </div>
            
            <!-- Selected Chips Area -->
            <div id="selectedChipsArea" class="px-3 pb-2 d-none flex-wrap gap-2"></div>

            <div class="card-body border-top">
                <div class="row g-3">
                    <div class="col-md-2 col-12">
                        <label class="form-label fs-8 fw-bold text-muted text-uppercase">Company</label>
                        <select class="form-select form-select-sm" id="filterCompany">
                            <option value="">All Companies</option>
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}">{{ $company->company_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 col-12">
                        <label class="form-label fs-8 fw-bold text-muted text-uppercase">Branch</label>
                        <select class="form-select form-select-sm" id="filterBranch">
                            <option value="">All Branches</option>
                            <!-- Populated via JS -->
                        </select>
                    </div>
                    <div class="col-md-2 col-12">
                        <label class="form-label fs-8 fw-bold text-muted text-uppercase">Department</label>
                        <select class="form-select form-select-sm" id="filterDepartment">
                            <option value="">All Departments</option>
                            <!-- Populated via JS -->
                        </select>
                    </div>
                    <div class="col-md-2 col-12">
                        <label class="form-label fs-8 fw-bold text-muted text-uppercase">Employee Search</label>
                        <input type="text" class="form-control form-control-sm" id="filterEmployee" placeholder="Name or ID...">
                    </div>
                    <div class="col-md-2 col-6">
                        <label class="form-label fs-8 fw-bold text-muted text-uppercase">Shift</label>
                        <select class="form-select form-select-sm" id="filterShift">
                            <option value="">All Shifts</option>
                            @foreach($shifts as $shift)
                                <option value="{{ $shift->id }}">{{ $shift->shift_name }}</option>
                            @endforeach
                            <option value="OFF">OFF Day</option>
                        </select>
                    </div>
                    <div class="col-md-1 col-6">
                        <label class="form-label fs-8 fw-bold text-muted text-uppercase">Source</label>
                        <select class="form-select form-select-sm" id="filterSource">
                            <option value="">All</option>
                            <option value="OVERRIDE">Override</option>
                            <option value="WEEKLY">Weekly</option>
                            <option value="ASSIGNMENT">Assignment</option>
                            <option value="LEGACY">Legacy</option>
                            <option value="DEFAULT">Default</option>
                        </select>
                    </div>
                    <div class="col-md-1 col-12 d-flex align-items-end">
                        <button class="btn btn-primary btn-sm w-100" id="btnApplyFilters">Apply</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Calendar Container -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div id="scheduleCalendar" style="min-height: 700px;"></div>
        </div>
    </div>
</div>

<!-- ================= MODALS ================= -->

<!-- 1. Add Assignment Modal -->
<div class="modal fade" id="assignmentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white border-0">
                <h5 class="modal-title"><i class="bi bi-calendar-plus me-2"></i>Assign Permanent Shift</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formAddAssignment" method="POST" action="{{ url('/schedules/assignment') }}" data-ajax="true" data-reset-on-success="true">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Employee ID</label>
                        <input type="number" name="employee_id" class="form-control" required placeholder="Enter Employee ID">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Shift</label>
                        <select name="shift_id" class="form-select" required>
                            <option value="">Select Shift</option>
                            @foreach($shifts as $shift)
                                <option value="{{ $shift->id }}">{{ $shift->shift_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Effective From</label>
                            <input type="date" name="effective_from" class="form-control" required value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold text-muted">Effective To (Optional)</label>
                            <input type="date" name="effective_to" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Assign Shift</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 2. Daily Override Modal -->
<div class="modal fade" id="overrideModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-warning text-dark border-0">
                <h5 class="modal-title"><i class="bi bi-exclamation-triangle me-2"></i>Add Daily Override</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formAddOverride" method="POST" action="{{ url('/schedules/override') }}" data-ajax="true" data-reset-on-success="true">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Employee ID</label>
                        <input type="number" name="employee_id" id="override_emp_id" class="form-control" required placeholder="Enter Employee ID">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Date</label>
                        <input type="date" name="date" id="override_date" class="form-control" required value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Override Shift</label>
                        <select name="shift_id" class="form-select" required>
                            <option value="">Select Shift</option>
                            @foreach($shifts as $shift)
                                <option value="{{ $shift->id }}">{{ $shift->shift_name }}</option>
                            @endforeach
                            <option value="OFF">OFF DAY</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted">Reason (Optional)</label>
                        <textarea name="reason" class="form-control" rows="2" placeholder="e.g. Management Requirement"></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Save Override</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 3. Weekly Schedule Modal -->
<div class="modal fade" id="weeklyScheduleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-info text-white border-0">
                <h5 class="modal-title"><i class="bi bi-arrow-repeat me-2"></i>Recurring Weekly Schedule</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formAddWeekly" method="POST" action="{{ url('/schedules/weekly') }}" data-ajax="true" data-reset-on-success="true">
                @csrf
                <div class="modal-body p-4">
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Employee ID</label>
                            <input type="number" name="employee_id" class="form-control" required placeholder="Employee ID">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Effective From</label>
                            <input type="date" name="effective_from" class="form-control" required value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold text-muted">Effective To (Optional)</label>
                            <input type="date" name="effective_to" class="form-control">
                        </div>
                    </div>
                    
                    <h6 class="fw-bold mb-3 border-bottom pb-2">Weekly Pattern</h6>
                    
                    @php
                        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
                    @endphp
                    
                    <div class="row g-3">
                        @foreach($days as $day)
                        <div class="col-md-6">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text w-25 fw-semibold text-capitalize">{{ $day }}</span>
                                <select name="{{ $day }}_shift" class="form-select">
                                    <option value="">Default/Unchanged</option>
                                    @foreach($shifts as $shift)
                                        <option value="{{ $shift->id }}">{{ $shift->shift_name }}</option>
                                    @endforeach
                                    <option value="OFF">OFF DAY</option>
                                </select>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info text-white">Save Pattern</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 4. Bulk Assignment Modal (Wizard) -->
<div class="modal fade" id="bulkAssignmentModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark text-white border-0">
                <h5 class="modal-title"><i class="bi bi-collection me-2"></i>Bulk Shift Assignment</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="row g-0 h-100">
                    <!-- Wizard Steps Sidebar -->
                    <div class="col-md-3 bg-light border-end p-4 d-none d-md-block">
                        <ul class="nav flex-column nav-pills" id="bulkWizardTabs" role="tablist">
                            <li class="nav-item mb-2"><a class="nav-link active" id="tab-step-1" data-bs-toggle="pill" href="#step-1">1. Employees</a></li>
                            <li class="nav-item mb-2"><a class="nav-link disabled" id="tab-step-2" data-bs-toggle="pill" href="#step-2">2. Shift & Dates</a></li>
                            <li class="nav-item mb-2"><a class="nav-link disabled" id="tab-step-3" data-bs-toggle="pill" href="#step-3">3. Preview</a></li>
                        </ul>
                    </div>
                    
                    <!-- Wizard Content -->
                    <div class="col-md-9 p-4">
                        <div class="tab-content" id="bulkWizardContent">
                            <!-- STEP 1: EMPLOYEES -->
                            <div class="tab-pane fade show active" id="step-1">
                                <h5 class="fw-bold mb-3 border-bottom pb-2">Select Employees</h5>
                                <!-- Selection Mode -->
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Selection Mode</label>
                                    <div class="d-flex gap-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="bulk_selection_mode" id="modeOrganization" value="organization" checked>
                                            <label class="form-check-label" for="modeOrganization">Organization Filter</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="bulk_selection_mode" id="modeEmployees" value="employees">
                                            <label class="form-check-label" for="modeEmployees">Individual Employees</label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Filters -->
                                <div class="row g-2 mb-3 bg-light p-3 rounded border" id="bulkFiltersContainer">
                                    <div class="col-md-4">
                                        <label class="form-label fs-7 fw-bold">Company</label>
                                        <select class="form-select form-select-sm" id="bulk_company_id">
                                            <option value="">All Companies</option>
                                            @foreach($companies as $company)
                                                <option value="{{ $company->id }}">{{ $company->company_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fs-7 fw-bold">Branch</label>
                                        <select class="form-select form-select-sm" id="bulk_branch_id">
                                            <option value="">All Branches</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fs-7 fw-bold">Department</label>
                                        <select class="form-select form-select-sm" id="bulk_department_id">
                                            <option value="">All Departments</option>
                                        </select>
                                    </div>
                                    <div class="col-md-12 mt-2 d-none" id="bulk_search_container">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                                            <input type="text" class="form-control" id="bulk_search" placeholder="Search by name or ID...">
                                            <button class="btn btn-primary" type="button" id="btnBulkSearch">Search</button>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div class="form-check" id="bulkSelectAllFilteredContainer">
                                        <input class="form-check-input" type="checkbox" id="bulkSelectAllFiltered" checked>
                                        <label class="form-check-label fw-bold" for="bulkSelectAllFiltered">Select All Filtered (<span id="bulkTotalCount">0</span>)</label>
                                    </div>
                                    <div><span class="badge bg-primary" id="bulkSelectedCountBadge">Selected: All Filtered</span></div>
                                </div>
                                
                                <!-- Employee List Table -->
                                <div class="table-responsive" style="max-height: 250px; overflow-y: auto;">
                                    <table class="table table-sm table-hover align-middle mb-0" id="bulkEmployeeTable">
                                        <thead class="table-light sticky-top">
                                            <tr>
                                                <th width="40">
                                                    <input type="checkbox" class="form-check-input" id="bulkSelectAllVisible">
                                                </th>
                                                <th>Employee</th>
                                                <th>Dept / Branch</th>
                                                <th>Current Shift</th>
                                            </tr>
                                        </thead>
                                        <tbody id="bulkEmployeeTbody">
                                            <!-- Dynamically populated -->
                                        </tbody>
                                    </table>
                                </div>
                                
                                <!-- Pagination -->
                                <div class="d-flex justify-content-between align-items-center mt-2">
                                    <div class="fs-7 text-muted" id="bulkPaginationInfo">Loading...</div>
                                    <div id="bulkPagination" class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-secondary" id="bulkPrevPage"><i class="bi bi-chevron-left"></i></button>
                                        <button class="btn btn-outline-secondary" id="bulkNextPage"><i class="bi bi-chevron-right"></i></button>
                                    </div>
                                </div>

                                <div class="mt-4 text-end">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="button" class="btn btn-primary" id="btnBulkNext1">Next: Shift & Dates <i class="bi bi-arrow-right"></i></button>
                                </div>
                            </div>

                            <!-- STEP 2: SHIFT & DATES -->
                            <div class="tab-pane fade" id="step-2">
                                <h5 class="fw-bold mb-3 border-bottom pb-2">Shift & Dates</h5>
                                <div class="mb-4">
                                    <label class="form-label fw-bold">Select Shift</label>
                                    <select id="bulk_shift_id" class="form-select form-select-lg" required>
                                        <option value="">-- Choose Shift --</option>
                                        @foreach($shifts as $shift)
                                            <option value="{{ $shift->id }}">{{ $shift->shift_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Effective From <span class="text-danger">*</span></label>
                                        <input type="date" id="bulk_effective_from" class="form-control" required value="{{ date('Y-m-d') }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold text-muted">Effective To (Optional)</label>
                                        <input type="date" id="bulk_effective_to" class="form-control">
                                    </div>
                                </div>

                                <div class="mt-4 d-flex justify-content-between">
                                    <button type="button" class="btn btn-outline-secondary" onclick="new bootstrap.Tab(document.getElementById('tab-step-1')).show()"><i class="bi bi-arrow-left"></i> Back</button>
                                    <button type="button" class="btn btn-primary" id="btnBulkNext2">Generate Preview <i class="bi bi-magic"></i></button>
                                </div>
                            </div>

                            <!-- STEP 3: PREVIEW -->
                            <div class="tab-pane fade" id="step-3">
                                <h5 class="fw-bold mb-3 border-bottom pb-2">Conflict Preview</h5>
                                
                                <div class="row text-center mb-3 g-2" id="bulkPreviewStats">
                                    <div class="col"><div class="border rounded p-2 bg-light"><div class="fs-4 fw-bold" id="prevSelected">0</div><div class="fs-7 text-muted">Selected</div></div></div>
                                    <div class="col"><div class="border rounded p-2 bg-success bg-opacity-10 text-success"><div class="fs-4 fw-bold" id="prevValid">0</div><div class="fs-7">Valid</div></div></div>
                                    <div class="col"><div class="border rounded p-2 bg-secondary bg-opacity-10 text-secondary"><div class="fs-4 fw-bold" id="prevNoChange">0</div><div class="fs-7">No Change</div></div></div>
                                    <div class="col"><div class="border rounded p-2 bg-warning bg-opacity-10 text-warning"><div class="fs-4 fw-bold" id="prevConflict">0</div><div class="fs-7">Conflicts</div></div></div>
                                </div>
                                
                                <div class="alert alert-warning py-2 fs-7 mb-3 d-none" id="bulkConflictWarning">
                                    <i class="bi bi-exclamation-triangle-fill"></i> Conflicts detected. Existing assignments will be automatically end-dated.
                                </div>

                                <div class="table-responsive" style="max-height: 250px; overflow-y: auto;">
                                    <table class="table table-sm align-middle fs-7" id="bulkPreviewTable">
                                        <thead class="table-light sticky-top">
                                            <tr>
                                                <th>Employee</th>
                                                <th>Current</th>
                                                <th>Requested</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody id="bulkPreviewTbody">
                                            <!-- Preview rows injected here -->
                                        </tbody>
                                    </table>
                                </div>

                                <div class="mt-4 d-flex justify-content-between align-items-center">
                                    <button type="button" class="btn btn-outline-secondary" onclick="new bootstrap.Tab(document.getElementById('tab-step-2')).show()"><i class="bi bi-arrow-left"></i> Edit Details</button>
                                    <button type="button" class="btn btn-dark" id="btnBulkConfirm"><i class="bi bi-check-circle me-1"></i> Confirm & Execute</button>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- 5. Event Detail / Recalculate Modal -->
<div class="modal fade" id="eventDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 bg-light">
                <h5 class="modal-title fw-bold" id="detailTitle">Shift Detail</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="d-flex align-items-center mb-3">
                    <div class="fs-1 me-3 text-indigo" id="detailIcon"><i class="bi bi-calendar-event"></i></div>
                    <div>
                        <h4 class="mb-0 fw-bold" id="detailEmpName">Employee Name</h4>
                        <div class="text-muted fs-7" id="detailDate">Date</div>
                    </div>
                </div>
                
                <table class="table table-borderless table-sm mb-0">
                    <tbody>
                        <tr>
                            <td class="text-muted fw-bold w-25">Shift:</td>
                            <td class="fw-semibold fs-5 text-indigo" id="detailShiftName">Standard Staff</td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-bold">Source:</td>
                            <td><span class="badge bg-secondary" id="detailSource">ASSIGNMENT</span></td>
                        </tr>
                    </tbody>
                </table>
                
                <hr>
                <div class="alert alert-warning fs-7 d-none" id="detailRecalcAlert">
                    <i class="bi bi-info-circle me-1"></i> Modifying historical schedules does not auto-recalculate attendance. 
                </div>
                
            </div>
            <div class="modal-footer bg-light border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-outline-indigo" onclick="window.location.href='/daily-attendance'" title="Go to daily attendance">View Attendance</button>
            </div>
        </div>
    </div>
</div>

<!-- 6. Employee Selection Modal (For Employee View Mode) -->
<div class="modal fade" id="employeeSelectionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark text-white border-0">
                <h5 class="modal-title"><i class="bi bi-people me-2"></i>Select Employees to Display</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="p-3 bg-light border-bottom d-flex gap-2">
                    <input type="text" id="employeeSearchInput" class="form-control" placeholder="Search by name or ID...">
                    <button class="btn btn-primary" id="btnSelectAllFiltered" title="Select all that match current text search">Select All Filtered</button>
                    <button class="btn btn-outline-danger" id="btnClearSelection" title="Clear all selected employees">Clear All</button>
                </div>
                <div class="p-3" style="max-height: 400px; overflow-y: auto;">
                    <p class="text-muted small mb-2"><i class="bi bi-info-circle me-1"></i>Only employees matching your Company/Branch/Department filters are shown.</p>
                    <div id="employeeSelectionList" class="row g-2">
                        <!-- Populated by JS -->
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light border-0">
                <span class="text-muted me-auto">Selected: <span id="modalSelectedCount">0</span></span>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success" id="btnApplyEmployeeSelection" data-bs-dismiss="modal">Apply & Update Calendar</button>
            </div>
        </div>
    </div>
</div>

<!-- 7. Summary Detail Modal (For Overview Mode) -->
<div class="modal fade" id="summaryDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white border-0">
                <h5 class="modal-title" id="summaryDetailTitle">Shift Summary</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="bg-light p-3 border-bottom text-center">
                    <h4 class="mb-0 text-primary fw-bold" id="summaryDetailShiftName">Standard Staff</h4>
                    <p class="text-muted mb-0" id="summaryDetailDate">02 Sep 2026</p>
                    <div class="mt-2 badge bg-dark text-white fs-6">Employees: <span id="summaryDetailCount">0</span></div>
                </div>
                <div class="p-3" style="max-height: 300px; overflow-y: auto;">
                    <ul class="list-group list-group-flush" id="summaryDetailEmployeeList">
                        <!-- Populated by JS -->
                    </ul>
                </div>
            </div>
            <div class="modal-footer bg-light border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="btnViewTheseEmployees" data-bs-dismiss="modal">View These Employees in Calendar</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<!-- FullCalendar v6 CDN -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // --- Dependent Dropdown Logic ---
    const companyData = @json($companies);
    const filterCompany = document.getElementById('filterCompany');
    const filterBranch = document.getElementById('filterBranch');
    const filterDepartment = document.getElementById('filterDepartment');
    
    function populateDropdown(selectElement, items, textKey, valKey = 'id') {
        const isBranch = selectElement.id === 'filterBranch' || selectElement.id === 'bulk_branch_id';
        selectElement.innerHTML = `<option value="">All ${isBranch ? 'Branches' : 'Departments'}</option>`;
        items.forEach(item => {
            const option = document.createElement('option');
            option.value = item[valKey];
            option.textContent = item[textKey];
            selectElement.appendChild(option);
        });
    }

    filterCompany.addEventListener('change', function() {
        const companyId = this.value;
        filterBranch.innerHTML = '<option value="">All Branches</option>';
        filterDepartment.innerHTML = '<option value="">All Departments</option>';
        
        if (companyId) {
            const company = companyData.find(c => c.id == companyId);
            if (company) {
                if (company.branches) populateDropdown(filterBranch, company.branches, 'branch_name');
                if (company.departments) populateDropdown(filterDepartment, company.departments, 'department_name');
            }
        }
    });

    // --- Helper function for mapping Shift Sources to colors & icons ---
    function getSourceStyling(source, shiftName) {
        if (shiftName === 'OFF' || shiftName === 'OFF DAY') {
            return { color: '#64748B', icon: 'bi-cup-hot', textColor: '#fff', border: 'transparent' };
        }
        
        switch (source) {
            case 'OVERRIDE':
                return { color: 'rgba(245, 158, 11, 0.1)', icon: 'bi-exclamation-triangle-fill', textColor: '#d97706', border: '#f59e0b' };
            case 'WEEKLY':
                return { color: 'rgba(16, 185, 129, 0.1)', icon: 'bi-arrow-repeat', textColor: '#059669', border: '#10b981' };
            case 'ASSIGNMENT':
                return { color: 'rgba(59, 130, 246, 0.1)', icon: 'bi-person-badge', textColor: '#2563eb', border: '#3b82f6' };
            case 'LEGACY':
            case 'DEFAULT':
            default:
                return { color: 'rgba(79, 70, 229, 0.1)', icon: 'bi-building', textColor: '#4f46e5', border: '#4f46e5' };
        }
    }
    
    var calendarEl = document.getElementById('scheduleCalendar');
    
    // Initialize FullCalendar
    let currentViewMode = 'overview';
    let selectedEmployeeIds = new Set();
    let lastScopeEmployees = [];
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: window.innerWidth < 768 ? 'listWeek' : 'dayGridMonth',
        themeSystem: 'bootstrap5',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: window.innerWidth < 768 ? 'listWeek,timeGridDay' : 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
        },
        buttonText: {
            today: 'Today',
            month: 'Month',
            week: 'Week',
            day: 'Day',
            list: 'List'
        },
        timeZone: 'Asia/Colombo',
        editable: true,
        droppable: true,
        eventDurationEditable: false,
        dayMaxEvents: 3, 
        
        windowResize: function(arg) {
            if (window.innerWidth < 768) {
                calendar.changeView('listWeek');
                calendar.setOption('headerToolbar', {
                    left: 'prev,next',
                    center: 'title',
                    right: 'listWeek,timeGridDay'
                });
            } else {
                calendar.setOption('headerToolbar', {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
                });
            }
        },
        
        // Fetch events from our API
        events: function(fetchInfo, successCallback, failureCallback) {
            currentViewMode = document.querySelector('input[name="view_mode"]:checked').value;
            
            // Build Base Params
            const params = new URLSearchParams({
                start: fetchInfo.startStr,
                end: fetchInfo.endStr,
                company_id: document.getElementById('filterCompany').value,
                branch_id: document.getElementById('filterBranch').value,
                department_id: document.getElementById('filterDepartment').value,
                employee_search: document.getElementById('filterEmployee').value,
                shift_id: document.getElementById('filterShift').value,
                source: document.getElementById('filterSource').value,
                view_mode: currentViewMode
            });

            // Append employee IDs if in employee mode
            if (currentViewMode === 'employees') {
                selectedEmployeeIds.forEach(id => {
                    params.append('employee_ids[]', id);
                });
            }
            
            fetch(`/schedules/fetch?${params.toString()}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                let events = [];
                // Safety check
                if (!data || typeof data.schedules === 'undefined') {
                    console.error("Invalid data format received:", data);
                    return successCallback(events);
                }

                // Update metrics
                lastScopeEmployees = data.employees || [];
                document.getElementById('scopeCount').textContent = lastScopeEmployees.length;
                document.getElementById('selectedCount').textContent = selectedEmployeeIds.size;
                updateEmployeeSelectionModal();

                if (currentViewMode === 'overview') {
                    // Render Summary Events
                    if (data.summary_events) {
                        data.summary_events.forEach(summary => {
                            let isOff = (summary.shift_name === 'OFF' || summary.shift_id === 'OFF');
                            let bgColor = isOff ? '#f8f9fa' : '#e7f1ff';
                            let border = isOff ? '#dee2e6' : '#0d6efd';
                            let text = isOff ? '#6c757d' : '#0d6efd';
                            let icon = isOff ? 'bi-moon-stars' : 'bi-people';
                            
                            events.push({
                                id: `summary_${summary.shift_id}_${summary.date}`,
                                title: `${summary.shift_name} · ${summary.employee_count}`,
                                start: summary.date,
                                allDay: true,
                                backgroundColor: bgColor,
                                borderColor: border,
                                textColor: text,
                                extendedProps: {
                                    is_summary: true,
                                    shift_id: summary.shift_id,
                                    shift_name: summary.shift_name,
                                    employee_count: summary.employee_count,
                                    employees_list: summary.employees_list,
                                    date: summary.date,
                                    icon: icon
                                }
                            });
                        });
                    }
                } else {
                    // Render Individual Employee Events
                    Object.keys(data.schedules).forEach(empId => {
                        const empDataList = data.schedules[empId];
                        const empObj = data.employees.find(e => e.id == empId);
                        const empName = empObj ? empObj.first_name + ' ' + empObj.last_name : `Emp #${empId}`;
                        
                        empDataList.forEach(schedule => {
                            const style = getSourceStyling(schedule.source, schedule.shift_name);
                            
                            events.push({
                                id: `${empId}_${schedule.date}`,
                                title: `${empName.split(' ')[0]} — ${schedule.shift_name}`,
                                start: schedule.date,
                                allDay: true,
                                backgroundColor: style.color,
                                borderColor: style.border,
                                textColor: style.textColor,
                                extendedProps: {
                                    is_summary: false,
                                    employee_id: empId,
                                    employee_name: empName,
                                    shift_id: schedule.shift_id,
                                    shift_name: schedule.shift_name,
                                    source: schedule.source,
                                    icon: style.icon
                                }
                            });
                        });
                    });
                }
                
                successCallback(events);
            })
            .catch(error => {
                console.error("Error fetching schedules:", error);
                failureCallback(error);
                if (typeof AmsToast !== 'undefined') AmsToast.error('Failed to load calendar data.');
            });
        },
        
        // Render custom HTML for event content
        eventContent: function(arg) {
            const props = arg.event.extendedProps;
            let innerHtml = `
                <div class="p-1 rounded fw-semibold text-truncate" style="font-size: 0.75rem; color: ${arg.event.textColor};">
                    <i class="bi ${props.icon} me-1"></i>
                    ${arg.event.title}
                </div>
            `;
            return { html: innerHtml };
        },
        
        // Handle Event Click
        eventClick: function(info) {
            const props = info.event.extendedProps;
            const dateStr = info.event.start.toLocaleDateString();

            if (props.is_summary) {
                // Open Summary Detail Modal
                document.getElementById('summaryDetailShiftName').textContent = props.shift_name;
                document.getElementById('summaryDetailDate').textContent = dateStr;
                document.getElementById('summaryDetailCount').textContent = props.employee_count;
                
                let listHtml = '';
                if (props.employees_list && props.employees_list.length > 0) {
                    props.employees_list.forEach(emp => {
                        listHtml += `<li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-person me-2 text-secondary"></i>${emp.name}</span>
                            <span class="badge bg-light text-dark border">${emp.department}</span>
                        </li>`;
                    });
                } else {
                    listHtml = `<li class="list-group-item text-muted">No employees found.</li>`;
                }
                document.getElementById('summaryDetailEmployeeList').innerHTML = listHtml;

                // Setup "View These Employees" button
                document.getElementById('btnViewTheseEmployees').onclick = function() {
                    if (props.employees_list) {
                        selectedEmployeeIds.clear();
                        props.employees_list.forEach(emp => selectedEmployeeIds.add(String(emp.id)));
                        document.getElementById('viewModeEmployees').checked = true;
                        document.getElementById('viewModeEmployees').dispatchEvent(new Event('change'));
                    }
                };

                new bootstrap.Modal(document.getElementById('summaryDetailModal')).show();
            } else {
                // Individual Employee Event Click
                document.getElementById('detailEmpName').textContent = props.employee_name;
                document.getElementById('detailDate').textContent = dateStr;
                document.getElementById('detailShiftName').textContent = props.shift_name;
                document.getElementById('detailSource').textContent = props.source;
                document.getElementById('detailIcon').innerHTML = `<i class="bi ${props.icon}"></i>`;
                
                // Show historical warning if date is in the past
                const today = new Date();
                today.setHours(0,0,0,0);
                if (info.event.start < today) {
                    document.getElementById('detailRecalcAlert').classList.remove('d-none');
                } else {
                    document.getElementById('detailRecalcAlert').classList.add('d-none');
                }
                
                new bootstrap.Modal(document.getElementById('eventDetailModal')).show();
            }
        },
        
        // Handle Drag & Drop
        eventDrop: function(info) {
            info.revert(); // Always revert visual change until server confirms
            
            const props = info.event.extendedProps;
            if (props.is_summary) {
                if (typeof AmsToast !== 'undefined') AmsToast.warning('Cannot drag summary events.');
                return;
            }
            const newDateStr = FullCalendar.formatDate(info.event.start, {
                year: 'numeric', month: '2-digit', day: '2-digit'
            }).replace(/\//g, '-'); // naive formatting to YYYY-MM-DD
            
            const isHistorical = (info.event.start < new Date().setHours(0,0,0,0));
            const msg = isHistorical 
                ? `WARNING: You are dragging to a historical date (${newDateStr}). This will create a Daily Override but will NOT recalculate existing attendance automatically. Proceed?`
                : `Create a Daily Override for ${props.employee_name} on ${newDateStr} using shift "${props.shift_name}"?`;
                
            if (confirm(msg)) {
                // Pre-fill the modal and show it to force reason entry, or submit directly.
                // We'll show the Override Modal pre-filled to enforce security and reason tracking.
                document.getElementById('override_emp_id').value = props.employee_id;
                
                // Hacky date format fix since FullCalendar format can vary
                const d = info.event.start;
                const formattedDate = d.getFullYear() + "-" + String(d.getMonth() + 1).padStart(2, '0') + "-" + String(d.getDate()).padStart(2, '0');
                document.getElementById('override_date').value = formattedDate;
                
                // Set shift select
                const shiftSelect = document.querySelector('#overrideModal select[name="shift_id"]');
                if (props.shift_id) {
                    shiftSelect.value = props.shift_id;
                } else if (props.shift_name === 'OFF' || props.shift_name === 'OFF DAY') {
                    shiftSelect.value = 'OFF';
                }
                
                new bootstrap.Modal(document.getElementById('overrideModal')).show();
            }
        }
    });
    
    calendar.render();
    
    // Filter Apply Button
    document.getElementById('btnApplyFilters').addEventListener('click', function() {
        calendar.refetchEvents();
    });
    
    // Re-fetch calendar on successful modal submission
    const modals = ['assignmentModal', 'overrideModal', 'weeklyScheduleModal', 'bulkAssignmentModal'];
    modals.forEach(m => {
        const el = document.getElementById(m);
        if (el) {
            el.addEventListener('hidden.bs.modal', function () {
                calendar.refetchEvents();
            });
        }
    });
// BULK ASSIGNMENT WIZARD LOGIC
let bulkSelectedEmployees = new Set();
let bulkCurrentPage = 1;

function fetchBulkEmployees() {
    const isOrgMode = document.getElementById('modeOrganization').checked;
    
    const params = new URLSearchParams({
        page: bulkCurrentPage,
        selection_mode: isOrgMode ? 'organization' : 'employees',
        company_id: document.getElementById('bulk_company_id').value,
        branch_id: document.getElementById('bulk_branch_id').value,
        department_id: document.getElementById('bulk_department_id').value,
        search: document.getElementById('bulk_search').value,
        per_page: 50
    });

    fetch(`{{ url('/schedules/bulk-assignment/employees') }}?${params.toString()}`)
        .then(res => res.json())
        .then(data => {
            const tbody = document.getElementById('bulkEmployeeTbody');
            tbody.innerHTML = '';
            
            document.getElementById('bulkTotalCount').textContent = data.total_matching_filters;
            document.getElementById('bulkPaginationInfo').textContent = `Showing page ${data.current_page} of ${data.last_page}`;
            
            const isAllFiltered = document.getElementById('bulkSelectAllFiltered').checked;

            data.data.forEach(emp => {
                const tr = document.createElement('tr');
                // If Select All Filtered is true, we visually show checked, 
                // but conceptually the backend resolves it. For UI clarity, let's check it if it's in the Set OR if all filtered is true.
                const isChecked = isAllFiltered || bulkSelectedEmployees.has(emp.id.toString());
                
                tr.innerHTML = `
                    <td><input type="checkbox" class="form-check-input bulk-emp-checkbox" value="${emp.id}" ${isChecked ? 'checked' : ''} ${isAllFiltered ? 'disabled' : ''}></td>
                    <td>
                        <div class="fw-bold">${emp.name}</div>
                        <div class="text-muted fs-7">${emp.employee_id}</div>
                    </td>
                    <td>
                        <div class="fs-7">${emp.branch}</div>
                        <div class="fs-7 text-muted">${emp.department}</div>
                    </td>
                    <td><span class="badge bg-secondary">${emp.current_shift}</span></td>
                `;
                tbody.appendChild(tr);
            });
            
            // Attach individual checkbox events
            document.querySelectorAll('.bulk-emp-checkbox').forEach(cb => {
                cb.addEventListener('change', function() {
                    if(this.checked) bulkSelectedEmployees.add(this.value);
                    else bulkSelectedEmployees.delete(this.value);
                    updateBulkSelectionCount();
                });
            });
        })
        .catch(err => {
            console.error('Failed to load bulk employees:', err);
            document.getElementById('bulkPaginationInfo').textContent = 'Error loading employees. Please try again.';
            if (typeof AmsToast !== 'undefined') AmsToast.error('Failed to load employees.');
        });
}

function updateBulkSelectionCount() {
    const badge = document.getElementById('bulkSelectedCountBadge');
    if (document.getElementById('bulkSelectAllFiltered').checked) {
        badge.textContent = `Selected: All Filtered (${document.getElementById('bulkTotalCount').textContent})`;
        document.querySelectorAll('.bulk-emp-checkbox').forEach(cb => { cb.checked = true; cb.disabled = true; });
    } else {
        badge.textContent = `Selected: ${bulkSelectedEmployees.size}`;
        document.querySelectorAll('.bulk-emp-checkbox').forEach(cb => { cb.disabled = false; });
    }
}

document.querySelectorAll('input[name="bulk_selection_mode"]').forEach(radio => {
    radio.addEventListener('change', function() {
        if(this.value === 'organization') {
            document.getElementById('bulk_search_container').classList.add('d-none');
            document.getElementById('bulkSelectAllFilteredContainer').classList.remove('d-none');
            document.getElementById('bulkSelectAllFiltered').checked = true;
        } else {
            document.getElementById('bulk_search_container').classList.remove('d-none');
            document.getElementById('bulkSelectAllFilteredContainer').classList.add('d-none');
            document.getElementById('bulkSelectAllFiltered').checked = false;
        }
        bulkSelectedEmployees.clear();
        bulkCurrentPage = 1;
        fetchBulkEmployees();
        updateBulkSelectionCount();
    });
});

document.getElementById('bulkSelectAllFiltered').addEventListener('change', updateBulkSelectionCount);

document.getElementById('bulkSelectAllVisible').addEventListener('change', function() {
    const isChecked = this.checked;
    document.querySelectorAll('.bulk-emp-checkbox:not(:disabled)').forEach(cb => {
        cb.checked = isChecked;
        if(isChecked) bulkSelectedEmployees.add(cb.value);
        else bulkSelectedEmployees.delete(cb.value);
    });
    updateBulkSelectionCount();
});

document.getElementById('bulk_company_id').addEventListener('change', function() {
    bulkCurrentPage = 1;
    const companyId = this.value;
    const filterBranch = document.getElementById('bulk_branch_id');
    const filterDepartment = document.getElementById('bulk_department_id');
    
    filterBranch.innerHTML = '<option value="">All Branches</option>';
    filterDepartment.innerHTML = '<option value="">All Departments</option>';
    
    if (companyId) {
        const company = companyData.find(c => c.id == companyId);
        if (company) {
            if (company.branches) populateDropdown(filterBranch, company.branches, 'branch_name');
            if (company.departments) populateDropdown(filterDepartment, company.departments, 'department_name');
        }
    }
    fetchBulkEmployees();
});
document.getElementById('bulk_branch_id').addEventListener('change', () => { bulkCurrentPage = 1; fetchBulkEmployees(); });
document.getElementById('bulk_department_id').addEventListener('change', () => { bulkCurrentPage = 1; fetchBulkEmployees(); });
document.getElementById('btnBulkSearch').addEventListener('click', () => { bulkCurrentPage = 1; fetchBulkEmployees(); });

document.getElementById('bulkAssignmentModal').addEventListener('shown.bs.modal', function () {
    if (document.getElementById('bulkEmployeeTbody').innerHTML.trim() === '') {
        fetchBulkEmployees();
    }
});
document.getElementById('bulkPrevPage').addEventListener('click', () => { if(bulkCurrentPage > 1) { bulkCurrentPage--; fetchBulkEmployees(); }});
document.getElementById('bulkNextPage').addEventListener('click', () => { bulkCurrentPage++; fetchBulkEmployees(); });

// Step 1 to Step 2
document.getElementById('btnBulkNext1').addEventListener('click', function() {
    if(!document.getElementById('bulkSelectAllFiltered').checked && bulkSelectedEmployees.size === 0) {
        alert('Please select at least one employee.');
        return;
    }
    document.getElementById('tab-step-2').classList.remove('disabled');
    new bootstrap.Tab(document.getElementById('tab-step-2')).show();
});

// Step 2 to Step 3 (Preview)
document.getElementById('btnBulkNext2').addEventListener('click', function() {
    const shiftId = document.getElementById('bulk_shift_id').value;
    const effFrom = document.getElementById('bulk_effective_from').value;
    if(!shiftId || !effFrom) {
        alert('Please select a shift and effective from date.');
        return;
    }
    
    document.getElementById('btnBulkNext2').innerHTML = '<span class="spinner-border spinner-border-sm"></span> Generating...';
    document.getElementById('btnBulkNext2').disabled = true;

    const payload = {
        _token: '{{ csrf_token() }}',
        shift_id: shiftId,
        effective_from: effFrom,
        effective_to: document.getElementById('bulk_effective_to').value,
        selection_mode: document.getElementById('modeOrganization').checked ? 'organization' : 'employees',
        select_all_filtered: document.getElementById('bulkSelectAllFiltered').checked,
        company_id: document.getElementById('bulk_company_id').value,
        branch_id: document.getElementById('bulk_branch_id').value,
        department_id: document.getElementById('bulk_department_id').value,
        search: document.getElementById('bulk_search').value,
        employee_ids: Array.from(bulkSelectedEmployees)
    };

    fetch('{{ url("/schedules/bulk-assignment/preview") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(res => res.json())
    .then(data => {
        document.getElementById('btnBulkNext2').innerHTML = 'Generate Preview <i class="bi bi-magic"></i>';
        document.getElementById('btnBulkNext2').disabled = false;
        
        if(data.counts) {
            document.getElementById('prevSelected').textContent = data.counts.selected;
            document.getElementById('prevValid').textContent = data.counts.valid;
            document.getElementById('prevNoChange').textContent = data.counts.no_change;
            document.getElementById('prevConflict').textContent = data.counts.conflict;
            
            if(data.counts.conflict > 0) document.getElementById('bulkConflictWarning').classList.remove('d-none');
            else document.getElementById('bulkConflictWarning').classList.add('d-none');
            
            const tbody = document.getElementById('bulkPreviewTbody');
            tbody.innerHTML = '';
            data.preview.forEach(p => {
                let statusBadge = '';
                if(p.status === 'VALID') statusBadge = '<span class="badge bg-success">Valid</span>';
                else if(p.status === 'NO_CHANGE') statusBadge = '<span class="badge bg-secondary">No Change</span>';
                else if(p.status === 'CONFLICT') statusBadge = '<span class="badge bg-warning text-dark" title="'+p.reason+'">Conflict</span>';
                
                tbody.innerHTML += `
                    <tr>
                        <td><div class="fw-bold">${p.name}</div><div class="fs-7 text-muted">${p.employee_id}</div></td>
                        <td>${p.current_shift}</td>
                        <td>${p.requested_shift}</td>
                        <td>${statusBadge}</td>
                    </tr>
                `;
            });
            
            document.getElementById('tab-step-3').classList.remove('disabled');
            new bootstrap.Tab(document.getElementById('tab-step-3')).show();
        }
    })
    .catch(err => {
        document.getElementById('btnBulkNext2').innerHTML = 'Generate Preview <i class="bi bi-magic"></i>';
        document.getElementById('btnBulkNext2').disabled = false;
        alert('Failed to generate preview. Check console.');
        console.error(err);
    });
});

// Confirm & Execute
document.getElementById('btnBulkConfirm').addEventListener('click', function() {
    if(!confirm('Are you sure you want to execute this bulk assignment?')) return;
    
    const btn = this;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Executing...';
    btn.disabled = true;

    const payload = {
        _token: '{{ csrf_token() }}',
        shift_id: document.getElementById('bulk_shift_id').value,
        effective_from: document.getElementById('bulk_effective_from').value,
        effective_to: document.getElementById('bulk_effective_to').value,
        selection_mode: document.getElementById('modeOrganization').checked ? 'organization' : 'employees',
        select_all_filtered: document.getElementById('bulkSelectAllFiltered').checked,
        company_id: document.getElementById('bulk_company_id').value,
        branch_id: document.getElementById('bulk_branch_id').value,
        department_id: document.getElementById('bulk_department_id').value,
        search: document.getElementById('bulk_search').value,
        employee_ids: Array.from(bulkSelectedEmployees)
    };

    fetch('{{ url("/schedules/bulk-assignment/execute") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(res => res.json())
    .then(data => {
        btn.innerHTML = '<i class="bi bi-check-circle me-1"></i> Confirm & Execute';
        btn.disabled = false;
        
        if(data.status === 'success') {
            const r = data.results;
            alert(`Bulk Assignment Complete!\nCreated: ${r.created}\nUpdated/Overlaps Resolved: ${r.conflicts}\nNo Change: ${r.no_change}`);
            bootstrap.Modal.getInstance(document.getElementById('bulkAssignmentModal')).hide();
            if(typeof calendar !== 'undefined') calendar.refetchEvents();
        } else {
            alert('Execution failed: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(err => {
        btn.innerHTML = '<i class="bi bi-check-circle me-1"></i> Confirm & Execute';
        btn.disabled = false;
        alert('An error occurred during execution.');
        console.error(err);
    });
});

// Initialize on modal open
document.getElementById('bulkAssignmentModal').addEventListener('show.bs.modal', function () {
    bulkCurrentPage = 1;
    bulkSelectedEmployees.clear();
    new bootstrap.Tab(document.getElementById('tab-step-1')).show();
    document.getElementById('tab-step-2').classList.add('disabled');
    document.getElementById('tab-step-3').classList.add('disabled');
    fetchBulkEmployees();
});

// --- View Mode & Employee Selection Logic ---

const viewModeRadios = document.querySelectorAll('input[name="view_mode"]');
const btnManageEmployees = document.getElementById('btnManageEmployees');
const selectedChipsArea = document.getElementById('selectedChipsArea');
const modalSelectedCount = document.getElementById('modalSelectedCount');

// Toggle UI based on view mode
viewModeRadios.forEach(radio => {
    radio.addEventListener('change', function() {
        if (this.value === 'employees') {
            btnManageEmployees.classList.remove('d-none');
            selectedChipsArea.classList.remove('d-none');
        } else {
            btnManageEmployees.classList.add('d-none');
            selectedChipsArea.classList.add('d-none');
        }
        calendar.refetchEvents();
    });
});

// Update the Employee Selection Modal List and Chips
window.updateEmployeeSelectionModal = function() {
    // 1. Remove IDs from selectedEmployeeIds that are no longer in scope
    const scopeIds = new Set(lastScopeEmployees.map(e => String(e.id)));
    let selectionChanged = false;
    selectedEmployeeIds.forEach(id => {
        if (!scopeIds.has(id)) {
            selectedEmployeeIds.delete(id);
            selectionChanged = true;
        }
    });

    // 2. Render Modal List
    const listContainer = document.getElementById('employeeSelectionList');
    const search = document.getElementById('employeeSearchInput').value.toLowerCase();
    
    listContainer.innerHTML = '';
    let visibleCount = 0;

    lastScopeEmployees.forEach(emp => {
        const empName = `${emp.first_name} ${emp.last_name}`;
        const searchStr = `${empName} ${emp.employee_id} ${emp.department ? emp.department.department_name : ''}`.toLowerCase();
        
        if (search && !searchStr.includes(search)) return;
        visibleCount++;

        const isChecked = selectedEmployeeIds.has(String(emp.id)) ? 'checked' : '';
        const dept = emp.department ? emp.department.department_name : 'N/A';
        
        listContainer.innerHTML += `
            <div class="col-md-6 col-12 employee-select-item">
                <div class="form-check border rounded p-2 bg-white">
                    <input class="form-check-input ms-1 emp-checkbox" type="checkbox" value="${emp.id}" id="chk_emp_${emp.id}" ${isChecked}>
                    <label class="form-check-label ms-2 d-block text-truncate" for="chk_emp_${emp.id}">
                        <strong>${empName}</strong> <span class="text-muted small">(${emp.employee_id})</span><br>
                        <small class="text-muted"><i class="bi bi-building me-1"></i>${dept}</small>
                    </label>
                </div>
            </div>
        `;
    });

    if (visibleCount === 0) {
        listContainer.innerHTML = `<div class="col-12 text-center text-muted py-3">No employees found matching the search.</div>`;
    }

    // Bind checkbox changes
    document.querySelectorAll('.emp-checkbox').forEach(chk => {
        chk.addEventListener('change', function() {
            if (this.checked) {
                selectedEmployeeIds.add(this.value);
            } else {
                selectedEmployeeIds.delete(this.value);
            }
            updateCounters();
        });
    });

    updateCounters();
    renderChips();
};

function updateCounters() {
    document.getElementById('selectedCount').textContent = selectedEmployeeIds.size;
    modalSelectedCount.textContent = selectedEmployeeIds.size;
}

function renderChips() {
    selectedChipsArea.innerHTML = '';
    if (selectedEmployeeIds.size === 0) {
        selectedChipsArea.innerHTML = '<span class="text-muted small">No employees selected.</span>';
        return;
    }

    selectedEmployeeIds.forEach(id => {
        const emp = lastScopeEmployees.find(e => String(e.id) === id);
        if (emp) {
            const name = emp.first_name;
            selectedChipsArea.innerHTML += `
                <span class="badge bg-primary d-flex align-items-center fs-7 py-2 px-3">
                    <i class="bi bi-person-fill me-1"></i> ${name}
                    <i class="bi bi-x-circle ms-2 cursor-pointer remove-chip" data-id="${id}" style="cursor: pointer;"></i>
                </span>
            `;
        }
    });

    // Bind chip remove
    document.querySelectorAll('.remove-chip').forEach(btn => {
        btn.addEventListener('click', function() {
            selectedEmployeeIds.delete(this.getAttribute('data-id'));
            updateEmployeeSelectionModal();
            calendar.refetchEvents();
        });
    });
}

// Modal Actions
document.getElementById('employeeSearchInput').addEventListener('input', function() {
    updateEmployeeSelectionModal();
});

document.getElementById('btnSelectAllFiltered').addEventListener('click', function() {
    document.querySelectorAll('.emp-checkbox').forEach(chk => {
        chk.checked = true;
        selectedEmployeeIds.add(chk.value);
    });
    updateCounters();
});

document.getElementById('btnClearSelection').addEventListener('click', function() {
    selectedEmployeeIds.clear();
    document.querySelectorAll('.emp-checkbox').forEach(chk => chk.checked = false);
    updateCounters();
});

document.getElementById('btnApplyEmployeeSelection').addEventListener('click', function() {
    calendar.refetchEvents();
});

// Re-fetch calendar when filters are applied (overriding old behavior if needed)
document.getElementById('btnApplyFilters').addEventListener('click', function() {
    calendar.refetchEvents();
});

});
</script>
@endsection
