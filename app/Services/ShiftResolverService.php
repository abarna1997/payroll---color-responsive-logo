<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Shift;
use App\Models\EmployeeScheduleOverride;
use App\Models\WeeklySchedule;
use App\Models\EmployeeShiftAssignment;
use Carbon\Carbon;

class ShiftResolverService
{
    /**
     * Resolve the active shift for an employee on a given date based on hierarchy:
     * 1. Daily Override
     * 2. Weekly Schedule
     * 3. Effective Employee Shift Assignment
     * 4. Legacy employee.shift_id
     * 5. Default Company Shift (Not explicitly modeled yet, fallback to first shift or null)
     *
     * @param Employee $employee
     * @param Carbon|string $date
     * @param array|null $preloadedData (Optional: 'overrides', 'weeklies', 'assignments', 'shifts')
     * @return array
     */
    public function resolve(Employee $employee, $date, ?array $preloadedData = null)
    {
        $targetDate = Carbon::parse($date)->startOfDay();

        // 1. Daily Override
        if ($preloadedData !== null && isset($preloadedData['overrides'])) {
            $override = $preloadedData['overrides']
                ->where('employee_id', $employee->id)
                ->where('date', $targetDate->toDateString())
                ->first();
        } else {
            $override = EmployeeScheduleOverride::where('employee_id', $employee->id)
                ->whereDate('date', $targetDate)
                ->first();
        }
            
        if ($override && $override->shift_id !== null) {
            $shift = null;
            if ($preloadedData !== null && isset($preloadedData['shifts'])) {
                $shift = $preloadedData['shifts']->firstWhere('id', $override->shift_id);
            } else {
                $shift = $override->shift;
            }
            if ($shift) {
                return [
                    'shift' => $shift,
                    'source' => 'OVERRIDE',
                    'effective_from' => $targetDate->toDateString(),
                    'effective_to' => $targetDate->toDateString(),
                    'reason' => $override->reason
                ];
            }
        } elseif ($override && $override->shift_id === null) {
            // Null shift_id means explicitly scheduled OFF via override (based on UI logic 'OFF' = null)
            return [
                'shift' => null,
                'source' => 'OVERRIDE',
                'effective_from' => $targetDate->toDateString(),
                'effective_to' => $targetDate->toDateString(),
                'reason' => $override->reason ?? 'Scheduled OFF via Override'
            ];
        }

        // 2. Weekly Schedule
        if ($preloadedData !== null && isset($preloadedData['weeklies'])) {
            $weekly = $preloadedData['weeklies']
                ->where('employee_id', $employee->id)
                ->filter(function($w) use ($targetDate) {
                    $effFrom = Carbon::parse($w->effective_from)->startOfDay();
                    if ($targetDate->lt($effFrom)) return false;
                    if ($w->effective_to) {
                        $effTo = Carbon::parse($w->effective_to)->endOfDay();
                        if ($targetDate->gt($effTo)) return false;
                    }
                    return true;
                })->first();
        } else {
            $weekly = WeeklySchedule::where('employee_id', $employee->id)
                ->whereDate('effective_from', '<=', $targetDate)
                ->where(function ($query) use ($targetDate) {
                    $query->whereNull('effective_to')
                          ->orWhereDate('effective_to', '>=', $targetDate);
                })
                ->first();
        }

        if ($weekly) {
            $dayOfWeek = strtolower($targetDate->englishDayOfWeek); // e.g., 'monday'
            $shiftCol = "{$dayOfWeek}_shift";
            
            $shiftId = $weekly->$shiftCol;
            if ($shiftId) {
                // If it's stored as 'OFF' or an ID
                if (strtoupper($shiftId) === 'OFF') {
                    return [
                        'shift' => null,
                        'source' => 'WEEKLY',
                        'effective_from' => Carbon::parse($weekly->effective_from)->toDateString(),
                        'effective_to' => $weekly->effective_to ? Carbon::parse($weekly->effective_to)->toDateString() : null,
                        'reason' => 'Scheduled OFF'
                    ];
                }
                
                $shift = null;
                if ($preloadedData !== null && isset($preloadedData['shifts'])) {
                    $shift = $preloadedData['shifts']->firstWhere('id', $shiftId);
                } else {
                    $shift = Shift::find($shiftId);
                }
                
                if ($shift) {
                    return [
                        'shift' => $shift,
                        'source' => 'WEEKLY',
                        'effective_from' => Carbon::parse($weekly->effective_from)->toDateString(),
                        'effective_to' => $weekly->effective_to ? Carbon::parse($weekly->effective_to)->toDateString() : null,
                        'reason' => 'Weekly Pattern'
                    ];
                }
            }
        }

        // 3. Effective Employee Shift Assignment
        if ($preloadedData !== null && isset($preloadedData['assignments'])) {
            $assignment = $preloadedData['assignments']
                ->where('employee_id', $employee->id)
                ->filter(function($a) use ($targetDate) {
                    $effFrom = Carbon::parse($a->effective_from)->startOfDay();
                    if ($targetDate->lt($effFrom)) return false;
                    if ($a->effective_to) {
                        $effTo = Carbon::parse($a->effective_to)->endOfDay();
                        if ($targetDate->gt($effTo)) return false;
                    }
                    return true;
                })->first();
        } else {
            $assignment = EmployeeShiftAssignment::where('employee_id', $employee->id)
                ->whereDate('effective_from', '<=', $targetDate)
                ->where(function ($query) use ($targetDate) {
                    $query->whereNull('effective_to')
                          ->orWhereDate('effective_to', '>=', $targetDate);
                })
                ->first();
        }

        if ($assignment && $assignment->shift_id !== null) {
            $shift = null;
            if ($preloadedData !== null && isset($preloadedData['shifts'])) {
                $shift = $preloadedData['shifts']->firstWhere('id', $assignment->shift_id);
            } else {
                $shift = $assignment->shift;
            }
            if ($shift) {
                return [
                    'shift' => $shift,
                    'source' => 'ASSIGNMENT',
                    'effective_from' => Carbon::parse($assignment->effective_from)->toDateString(),
                    'effective_to' => $assignment->effective_to ? Carbon::parse($assignment->effective_to)->toDateString() : null,
                    'reason' => 'Permanent Assignment'
                ];
            }
        }

        // 4. Legacy Employee Shift
        if ($employee->shift_id) {
            $shift = null;
            if ($preloadedData !== null && isset($preloadedData['shifts'])) {
                $shift = $preloadedData['shifts']->firstWhere('id', $employee->shift_id);
            } else {
                $shift = clone $employee->shift; 
            }
            if ($shift) {
                return [
                    'shift' => $shift,
                    'source' => 'LEGACY',
                    'effective_from' => null,
                    'effective_to' => null,
                    'reason' => 'Legacy assignment'
                ];
            }
        }

        // 5. Company Default Shift (Fallback)
        // If Company model has a default_shift_id, we'd use it here.
        // For now, return null.
        return [
            'shift' => null,
            'source' => 'DEFAULT',
            'effective_from' => null,
            'effective_to' => null,
            'reason' => 'No schedule found'
        ];
    }
}

