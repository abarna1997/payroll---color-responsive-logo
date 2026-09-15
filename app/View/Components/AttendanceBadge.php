<?php

namespace App\View\Components;

use Illuminate\View\Component;

class AttendanceBadge extends Component
{
    public $status;
    public $type; // 'status' or 'flag'
    public $detail;

    public function __construct($status, $type = 'status', $detail = '')
    {
        $this->status = strtoupper($status);
        $this->type = $type;
        $this->detail = $detail;
    }

    public function getMapping()
    {
        $map = [
            'PRESENT' => ['color' => 'success', 'icon' => 'check-circle', 'label' => 'PRESENT', 'tooltip' => 'Employee was present.'],
            'ON_TIME' => ['color' => 'success', 'icon' => 'check-circle', 'label' => 'ON TIME', 'tooltip' => 'Employee arrived on time.'],
            'NORMAL' => ['color' => 'success', 'icon' => 'check-circle', 'label' => 'NORMAL', 'tooltip' => 'Employee checked out on time.'],
            'GRACE' => ['color' => 'success', 'icon' => 'clock', 'label' => 'GRACE', 'tooltip' => 'Employee arrived within the configured grace period.', 'bg' => 'bg-success-subtle text-success'],
            'EARLY_IN' => ['color' => 'primary', 'icon' => 'arrow-down', 'label' => 'EARLY IN', 'tooltip' => 'Employee arrived before the configured shift start.'],
            'LATE_IN' => ['color' => 'warning', 'icon' => 'clock-warning', 'label' => 'LATE IN', 'tooltip' => 'Employee arrived after the configured grace period.'],
            'EARLY_OUT' => ['color' => 'warning', 'icon' => 'arrow-left', 'label' => 'EARLY OUT', 'tooltip' => 'Employee checked out before the configured shift end.', 'bg' => 'bg-orange-subtle text-orange'],
            'LATE_OUT' => ['color' => 'warning', 'icon' => 'arrow-right', 'label' => 'LATE OUT', 'tooltip' => 'Employee checked out after the configured shift end.', 'bg' => 'bg-orange-subtle text-orange'],
            'OT_ELIGIBLE' => ['color' => 'info', 'icon' => 'clock-plus', 'label' => 'OT ELIGIBLE', 'tooltip' => 'Employee reached the configured overtime eligibility threshold.', 'bg' => 'bg-purple-subtle text-purple'],
            'ABSENT' => ['color' => 'danger', 'icon' => 'x-circle', 'label' => 'ABSENT', 'tooltip' => 'No qualifying attendance recorded.'],
            'INCOMPLETE' => ['color' => 'secondary', 'icon' => 'alert-circle', 'label' => 'INCOMPLETE', 'tooltip' => 'Attendance interval is missing a punch.'],
            'MISSING_IN' => ['color' => 'secondary', 'icon' => 'alert-circle', 'label' => 'MISSING IN', 'tooltip' => 'Employee has a check-out but no matching check-in.'],
            'MISSING_OUT' => ['color' => 'secondary', 'icon' => 'alert-circle', 'label' => 'MISSING OUT', 'tooltip' => 'Employee has a check-in but no matching check-out.'],
            'HALF_DAY' => ['color' => 'warning', 'icon' => 'clock-alert', 'label' => 'HALF DAY', 'tooltip' => 'Employee attendance classified as half day.', 'bg' => 'bg-orange-subtle text-orange'],
            'FIRST_HALF' => ['color' => 'warning', 'icon' => 'clock-alert', 'label' => 'FIRST HALF', 'tooltip' => 'Employee attendance classified as first half.', 'bg' => 'bg-orange-subtle text-orange'],
            'SECOND_HALF' => ['color' => 'warning', 'icon' => 'clock-alert', 'label' => 'SECOND HALF', 'tooltip' => 'Employee attendance classified as second half.', 'bg' => 'bg-orange-subtle text-orange'],
            'LEAVE' => ['color' => 'info', 'icon' => 'calendar-check', 'label' => 'LEAVE', 'tooltip' => 'Employee is on leave.', 'bg' => 'bg-teal-subtle text-teal'],
            'HOLIDAY' => ['color' => 'info', 'icon' => 'calendar-check', 'label' => 'HOLIDAY', 'tooltip' => 'Configured holiday.', 'bg' => 'bg-teal-subtle text-teal'],
            'WEEKEND' => ['color' => 'info', 'icon' => 'calendar-check', 'label' => 'OFF DAY', 'tooltip' => 'Configured weekend.', 'bg' => 'bg-teal-subtle text-teal'],
            'OFF_DAY' => ['color' => 'info', 'icon' => 'calendar-check', 'label' => 'OFF DAY', 'tooltip' => 'Configured off day.', 'bg' => 'bg-teal-subtle text-teal'],
            'PENDING' => ['color' => 'secondary', 'icon' => 'clock', 'label' => 'PENDING', 'tooltip' => 'Attendance calculation is pending.'],
        ];

        return $map[$this->status] ?? ['color' => 'secondary', 'icon' => 'help-circle', 'label' => $this->status, 'tooltip' => 'Unknown status.'];
    }

    public function render()
    {
        return view('components.attendance-badge');
    }
}
