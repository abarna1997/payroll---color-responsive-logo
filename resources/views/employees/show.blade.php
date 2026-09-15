@extends('layouts.app')

@section('title', 'Employee Profile - ' . $employee->first_name . ' ' . $employee->last_name)

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3 mb-0 text-gray-800"><i class="bi bi-person-badge text-indigo me-2"></i>Employee Profile</h2>
        <a href="{{ route('employees') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to Directory
        </a>
    </div>

    <div class="row">
        <!-- Profile Info -->
        <div class="col-xl-4 col-lg-5 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body text-center mt-4">
                    <div class="mb-3">
                        @if($employee->profile_photo)
                            <img src="{{ Storage::url($employee->profile_photo) }}" class="rounded-circle img-fluid border border-3 border-indigo shadow-sm" style="width: 150px; height: 150px; object-fit: cover;" alt="Profile Photo">
                        @else
                            <div class="rounded-circle bg-light d-flex align-items-center justify-content-center mx-auto border border-3 border-indigo shadow-sm" style="width: 150px; height: 150px;">
                                <i class="bi bi-person text-secondary" style="font-size: 4rem;"></i>
                            </div>
                        @endif
                    </div>
                    <h4 class="mb-1 fw-bold">{{ $employee->first_name }} {{ $employee->last_name }}</h4>
                    <p class="text-muted mb-2">{{ $employee->designation ?? 'No Designation' }}</p>
                    <span class="badge {{ $employee->status === 'Active' ? 'bg-success' : 'bg-secondary' }} px-3 py-2 mb-4">
                        {{ $employee->status }}
                    </span>

                    <ul class="list-group list-group-flush text-start mt-3">
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted fw-bold"><i class="bi bi-hash me-2"></i>Employee ID</span>
                            <span class="fw-semibold">{{ $employee->employee_id }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted fw-bold"><i class="bi bi-building me-2"></i>Company</span>
                            <span class="fw-semibold">{{ $employee->company->company_name ?? 'N/A' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted fw-bold"><i class="bi bi-diagram-3 me-2"></i>Department</span>
                            <span class="fw-semibold">{{ $employee->department->department_name ?? 'N/A' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted fw-bold"><i class="bi bi-clock me-2"></i>Base Shift</span>
                            <span class="fw-semibold text-primary">{{ $employee->shift->shift_name ?? 'N/A' }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Schedule Section -->
        <div class="col-xl-8 col-lg-7 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="m-0 fw-bold text-indigo"><i class="bi bi-calendar-week me-2"></i>Personal Schedule</h5>
                    <a href="{{ route('schedules.index') }}?employee={{ $employee->id }}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-box-arrow-up-right"></i> Full Schedule Manager
                    </a>
                </div>
                <div class="card-body p-0">
                    <div id="employeeScheduleCalendar" style="min-height: 500px;" class="p-3"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Event Detail Modal (Read Only) -->
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
                        <h4 class="mb-0 fw-bold" id="detailDate">Date</h4>
                        <div class="text-muted fs-7">Scheduled Shift</div>
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
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
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

    var calendarEl = document.getElementById('employeeScheduleCalendar');
    var employeeId = {{ $employee->id }};
    
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        themeSystem: 'bootstrap5',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,listWeek'
        },
        timeZone: 'Asia/Colombo',
        editable: false, // Read only in profile
        
        events: function(fetchInfo, successCallback, failureCallback) {
            const params = new URLSearchParams({
                start: fetchInfo.startStr,
                end: fetchInfo.endStr,
                employee_id: employeeId
            });
            
            fetch(`/schedules/fetch?${params.toString()}`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => response.json())
            .then(data => {
                let events = [];
                if (data.schedules[employeeId]) {
                    data.schedules[employeeId].forEach(schedule => {
                        const style = getSourceStyling(schedule.source, schedule.shift_name);
                        events.push({
                            id: `${employeeId}_${schedule.date}`,
                            title: schedule.shift_name,
                            start: schedule.date,
                            allDay: true,
                            backgroundColor: style.color,
                            borderColor: style.border,
                            textColor: style.textColor,
                            extendedProps: {
                                shift_name: schedule.shift_name,
                                source: schedule.source,
                                icon: style.icon
                            }
                        });
                    });
                }
                successCallback(events);
            })
            .catch(error => {
                console.error("Error fetching schedules:", error);
                failureCallback(error);
            });
        },
        
        eventContent: function(arg) {
            const props = arg.event.extendedProps;
            return {
                html: `<div class="p-1 rounded fw-semibold text-truncate" style="font-size: 0.75rem; color: ${arg.event.textColor};">
                    <i class="bi ${props.icon} me-1"></i> ${arg.event.title}
                </div>`
            };
        },
        
        eventClick: function(info) {
            const props = info.event.extendedProps;
            document.getElementById('detailDate').textContent = info.event.start.toLocaleDateString();
            document.getElementById('detailShiftName').textContent = props.shift_name;
            document.getElementById('detailSource').textContent = props.source;
            document.getElementById('detailIcon').innerHTML = `<i class="bi ${props.icon}"></i>`;
            new bootstrap.Modal(document.getElementById('eventDetailModal')).show();
        }
    });
    
    calendar.render();
});
</script>
@endsection
