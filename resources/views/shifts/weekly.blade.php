@extends('layouts.app')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h4 class="fw-bold m-0 text-dark display-font">
            <i class="bi bi-calendar-week-fill me-2 text-primary"></i> Weekly Schedules
        </h4>
        <p class="text-secondary fs-7 m-0 mt-1">Assign and manage employee shift schedules on a weekly basis.</p>
    </div>
</div>

<div class="glass-card mb-4 p-3 d-flex justify-content-end">
    <button type="button" class="btn btn-custom-primary px-4 shadow-sm border-0" data-bs-toggle="offcanvas" data-bs-target="#createWeeklyDrawer">
        <i class="bi bi-plus-lg me-1"></i> New Schedule
    </button>
</div>

<div class="glass-card p-4">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h5 class="m-0 display-font text-dark"><i class="bi bi-list-task me-2 text-primary"></i> Weekly Schedules</h5>
        <span class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill fs-7 fw-semibold border border-primary border-opacity-25">
            Showing {{ $schedules->count() }} Records
        </span>
    </div>

    @if($schedules->count() > 0)
        <div class="table-responsive">
            <table class="table custom-table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Week Of</th>
                        <th>Mon</th>
                        <th>Tue</th>
                        <th>Wed</th>
                        <th>Thu</th>
                        <th>Fri</th>
                        <th>Sat</th>
                        <th>Sun</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($schedules as $s)
                        <tr>
                            <td>
                                <div class="fw-semibold text-dark fs-7.5">{{ $s->employee->first_name }} {{ $s->employee->last_name }}</div>
                                <span class="text-secondary fs-8 font-monospace">{{ $s->employee->emp_id }}</span>
                            </td>
                            <td>
                                <span class="badge bg-secondary-subtle text-dark border border-secondary border-opacity-25">{{ \Carbon\Carbon::parse($s->effective_from)->format('M d, Y') }}</span>
                            </td>
                            <td><span class="fs-8 text-secondary">{{ $s->monday_shift ?? 'Off' }}</span></td>
                            <td><span class="fs-8 text-secondary">{{ $s->tuesday_shift ?? 'Off' }}</span></td>
                            <td><span class="fs-8 text-secondary">{{ $s->wednesday_shift ?? 'Off' }}</span></td>
                            <td><span class="fs-8 text-secondary">{{ $s->thursday_shift ?? 'Off' }}</span></td>
                            <td><span class="fs-8 text-secondary">{{ $s->friday_shift ?? 'Off' }}</span></td>
                            <td><span class="fs-8 text-secondary">{{ $s->saturday_shift ?? 'Off' }}</span></td>
                            <td><span class="fs-8 text-secondary">{{ $s->sunday_shift ?? 'Off' }}</span></td>
                            <td class="text-end">
                                <form action="{{ route('shifts.weekly.delete', $s->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Delete this schedule?');">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-danger px-3 py-1 fs-8 border-0 shadow-sm rounded">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="text-center py-5 my-3">
            <div class="rounded-circle bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                <i class="bi bi-calendar-week-fill fs-1"></i>
            </div>
            <h5 class="fw-bold text-dark mb-2">No Weekly Schedules Found</h5>
            <p class="text-secondary fs-7 mx-auto mb-4" style="max-width: 480px;">
                Assign specific shifts for each day of the week to your employees.
            </p>
        </div>
    @endif
</div>

<!-- Offcanvas Drawer -->
<div class="offcanvas offcanvas-end bg-white shadow p-0" tabindex="-1" id="createWeeklyDrawer" style="width: 450px;">
    <div class="offcanvas-header border-bottom p-3 bg-light">
        <h5 class="offcanvas-title text-dark display-font m-0 fw-bold">
            <i class="bi bi-plus-circle-fill me-2 text-primary"></i> Create Schedule
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-4 text-start">
        <form action="{{ route('shifts.weekly.store') }}" method="POST">
            @csrf
            
            <div class="mb-3">
                <label class="form-label text-secondary fs-8 fw-semibold">Employee *</label>
                <select name="employee_id" class="form-select form-select-custom" required>
                    <option value="">-- Select Employee --</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}">{{ $emp->emp_id }} - {{ $emp->first_name }} {{ $emp->last_name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label text-secondary fs-8 fw-semibold">Effective From *</label>
                <input type="date" class="form-control form-control-custom" name="effective_from" required>
            </div>

            <h6 class="mt-4 mb-3 fw-bold text-dark border-bottom pb-2">Shift Assignments</h6>
            
            <div class="row g-2 mb-2">
                <div class="col-4 d-flex align-items-center"><span class="fs-8 fw-medium">Monday</span></div>
                <div class="col-8"><input type="text" class="form-control form-control-sm" name="monday_shift" placeholder="e.g. Morning"></div>
            </div>
            <div class="row g-2 mb-2">
                <div class="col-4 d-flex align-items-center"><span class="fs-8 fw-medium">Tuesday</span></div>
                <div class="col-8"><input type="text" class="form-control form-control-sm" name="tuesday_shift" placeholder="e.g. Morning"></div>
            </div>
            <div class="row g-2 mb-2">
                <div class="col-4 d-flex align-items-center"><span class="fs-8 fw-medium">Wednesday</span></div>
                <div class="col-8"><input type="text" class="form-control form-control-sm" name="wednesday_shift" placeholder="e.g. Morning"></div>
            </div>
            <div class="row g-2 mb-2">
                <div class="col-4 d-flex align-items-center"><span class="fs-8 fw-medium">Thursday</span></div>
                <div class="col-8"><input type="text" class="form-control form-control-sm" name="thursday_shift" placeholder="e.g. Night"></div>
            </div>
            <div class="row g-2 mb-2">
                <div class="col-4 d-flex align-items-center"><span class="fs-8 fw-medium">Friday</span></div>
                <div class="col-8"><input type="text" class="form-control form-control-sm" name="friday_shift" placeholder="e.g. Night"></div>
            </div>
            <div class="row g-2 mb-2">
                <div class="col-4 d-flex align-items-center"><span class="fs-8 fw-medium text-secondary">Saturday</span></div>
                <div class="col-8"><input type="text" class="form-control form-control-sm" name="saturday_shift" placeholder="e.g. Off"></div>
            </div>
            <div class="row g-2 mb-2">
                <div class="col-4 d-flex align-items-center"><span class="fs-8 fw-medium text-secondary">Sunday</span></div>
                <div class="col-8"><input type="text" class="form-control form-control-sm" name="sunday_shift" placeholder="e.g. Off"></div>
            </div>

            <div class="d-flex align-items-center justify-content-end gap-2 mt-4 pt-3 border-top">
                <button type="button" class="btn btn-custom-secondary px-4" data-bs-dismiss="offcanvas">Cancel</button>
                <button type="submit" class="btn btn-custom-primary border-0 shadow-sm px-4 fw-bold">
                    <i class="bi bi-save me-1"></i> Save Schedule
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
