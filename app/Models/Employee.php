<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use App\Models\Scopes\CompanyScope;

class Employee extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;
    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::addGlobalScope(new CompanyScope);
    }

    protected $fillable = [
        'user_id',
        'company_id',
        'branch_id',
        'department_id',
        'shift_id',
        'secondary_shift_id',
        'employee_number',
        'employee_id',
        'device_user_id',
        'sync_pin',
        'title',
        'first_name',
        'middle_name',
        'last_name',
        'gender',
        'date_of_birth',
        'nic',
        'nationality',
        'religion',
        'marital_status',
        'blood_group',
        'email',
        'personal_email',
        'company_email',
        'mobile_number',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relationship',
        'permanent_address',
        'current_address',
        'designation',
        'job_grade',
        'employment_status',
        'join_date',
        'probation_period',
        'confirmation_date',
        'work_location',
        'cost_center',
        'payroll_group',
        'attendance_policy',
        'leave_policy',
        'holiday_calendar',
        'basic_salary',
        'bank_name',
        'bank_account',
        'bank_swift',
        'bank_branch',
        'currency',
        'payment_method',
        'profile_photo',
        'signature',
        'signature',
        'status',
        'work_mode',
        'biometric_status',
        'card_number',
        'privilege',
        'fingerprint_enrolled',
        'face_enrolled',
        'password_registered',
        'enrollment_date',
        'enrollment_device_id',
        'allow_remote_punch',
        'home_latitude',
        'home_longitude',
        'allowed_radius',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'join_date' => 'date',
        'join_date' => 'date',
        'employee_number' => 'integer',
        'enrollment_date' => 'datetime',
        'fingerprint_enrolled' => 'boolean',
        'face_enrolled' => 'boolean',
        'password_registered' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function secondaryShift(): BelongsTo
    {
        return $this->belongsTo(Shift::class, 'secondary_shift_id');
    }

    public function salaryProfile(): HasOne
    {
        return $this->hasOne(SalaryProfile::class)->where('status', 'Active')->orderBy('effective_from', 'desc');
    }

    public function salaryProfiles(): HasMany
    {
        return $this->hasMany(SalaryProfile::class)->orderBy('effective_from', 'desc');
    }

    public function payslips(): HasMany
    {
        return $this->hasMany(Payslip::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(EmployeeDocument::class);
    }

    public function agreements(): HasMany
    {
        return $this->hasMany(EmployeeAgreement::class);
    }

    public function assets(): HasMany
    {
        return $this->hasMany(EmployeeAsset::class);
    }

    public function onboardingStages(): HasMany
    {
        return $this->hasMany(OnboardingStage::class);
    }

    public function checklistItems(): HasMany
    {
        return $this->hasMany(EmployeeChecklist::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(OnboardingNotification::class);
    }

    /**
     * Determine which shift (primary or secondary) is closer to the punch time
     */
    public function getMatchingShift($punchTimeStr)
    {
        $primary = $this->shift;
        $secondary = $this->secondaryShift;

        if (! $primary) {
            return $secondary;
        }
        if (! $secondary) {
            return $primary;
        }
        if (! $punchTimeStr || $punchTimeStr === '—') {
            return $primary;
        }

        try {
            $punch = Carbon::parse($punchTimeStr);
            $pStart = Carbon::parse($primary->start_time);
            $sStart = Carbon::parse($secondary->start_time);

            $pDiff = $punch->diffInMinutes($pStart);
            $sDiff = $punch->diffInMinutes($sStart);

            return ($pDiff <= $sDiff) ? $primary : $secondary;
        } catch (\Exception $e) {
            return $primary;
        }
    }

    public function checkIns(): HasMany
    {
        return $this->hasMany(AttendanceLog::class);
    }

    public function salaryComponents(): HasMany
    {
        return $this->hasMany(EmployeeSalaryComponent::class);
    }

    public function attendanceLogs(): HasMany
    {
        return $this->hasMany(AttendanceLog::class);
    }

    public function biometricTemplates(): HasMany
    {
        return $this->hasMany(BiometricTemplate::class);
    }

    public function devices(): BelongsToMany
    {
        return $this->belongsToMany(Device::class, 'employee_devices');
    }

    public function manualLogs(): HasMany
    {
        return $this->hasMany(ManualLog::class);
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name.' '.$this->last_name);
    }

    public function getBasicSalaryAttribute(): float
    {
        return $this->salaryProfile ? (float)$this->salaryProfile->basic_salary : 0.0;
    }

    public function getAttendanceStats($startDate, $endDate)
    {
        $startDate = Carbon::parse($startDate);
        $endDate = Carbon::parse($endDate);

        $logs = $this->attendanceLogs()
            ->whereBetween('attendance_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get();

        $presentDays = 0;
        $absentDays = 0;
        $lateArrivals = 0;
        $earlyDepartures = 0;
        $otHours = 0.0;

        foreach ($logs as $log) {
            if ($log->attendance_status === 'Late') {
                $lateArrivals++;
            }
            if ($log->attendance_status === 'Early Out') {
                $earlyDepartures++;
            }

            if ($log->attendance_status === 'Overtime' && $log->attendance_type === 'Check-Out') {
                $shift = $this->getMatchingShift($log->attendance_time);
                if ($shift) {
                    $shiftEnd = Carbon::parse($shift->end_time);
                    $punchTime = Carbon::parse($log->attendance_time);
                    $diffHours = max(0, $punchTime->diffInMinutes($shiftEnd) / 60.0);
                    $otHours += $diffHours;
                }
            }
        }

        // Count unique present dates
        $presentDates = $this->attendanceLogs()
            ->whereBetween('attendance_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->whereIn('attendance_status', ['Present', 'Late', 'Early Out', 'Overtime'])
            ->distinct()
            ->pluck('attendance_date')
            ->count();

        $absentDays = $this->attendanceLogs()
            ->whereBetween('attendance_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->where('attendance_status', 'Absent')
            ->distinct()
            ->pluck('attendance_date')
            ->count();

        // Calculate leaves
        $leaveRequests = LeaveRequest::where('employee_id', $this->id)
            ->where('status', 'Approved')
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate->toDateString(), $endDate->toDateString()])
                    ->orWhereBetween('end_date', [$startDate->toDateString(), $endDate->toDateString()]);
            })
            ->get();

        $paidLeaveDays = 0;
        $unpaidLeaveDays = 0;

        foreach ($leaveRequests as $req) {
            $reqStart = Carbon::parse($req->start_date);
            $reqEnd = Carbon::parse($req->end_date);

            $overlapStart = $reqStart->greaterThan($startDate) ? $reqStart : $startDate;
            $overlapEnd = $reqEnd->lessThan($endDate) ? $reqEnd : $endDate;

            $days = $overlapStart->diffInDays($overlapEnd) + 1;

            // Automatically exclude holidays from leave duration if company policy requires
            $holidaysInLeave = \App\Models\Holiday::where('company_id', $this->company_id)
                ->whereBetween('holiday_date', [$overlapStart->toDateString(), $overlapEnd->toDateString()])
                ->where('affects_leave', true)
                ->count();

            $actualDays = max(0, $days - $holidaysInLeave);

            $typeName = strtolower($req->leaveType->name ?? '');
            if (str_contains($typeName, 'unpaid') || str_contains($typeName, 'no-pay') || str_contains($typeName, 'no pay')) {
                $unpaidLeaveDays += $actualDays;
            } else {
                $paidLeaveDays += $actualDays;
            }
        }

        return [
            'present_days' => $presentDates,
            'absent_days' => $absentDays,
            'paid_leaves' => $paidLeaveDays,
            'unpaid_leaves' => $unpaidLeaveDays,
            'late_arrivals' => $lateArrivals,
            'early_departures' => $earlyDepartures,
            'ot_hours' => round($otHours, 2),
        ];
    }
}


