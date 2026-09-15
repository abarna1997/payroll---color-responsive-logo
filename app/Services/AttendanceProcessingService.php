<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\AttendanceLog;
use App\Models\DailyAttendanceSummary;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AttendanceProcessingService
{
    const STATUS_PENDING = 'PENDING';
    const STATUS_PRESENT = 'PRESENT';
    const STATUS_ABSENT = 'ABSENT';
    const STATUS_HALF_DAY = 'HALF_DAY';
    const STATUS_FIRST_HALF = 'FIRST_HALF';
    const STATUS_SECOND_HALF = 'SECOND_HALF';
    const STATUS_LEAVE = 'LEAVE';
    const STATUS_HOLIDAY = 'HOLIDAY';
    const STATUS_WEEKEND = 'WEEKEND';
    const STATUS_OFF_DAY = 'OFF_DAY';
    const STATUS_INCOMPLETE = 'INCOMPLETE';

    public function processDaily(int $employeeId, string $date)
    {
        try {
            $employee = Employee::with(['shift', 'secondaryShift'])->findOrFail($employeeId);
            $logicalDate = Carbon::parse($date)->startOfDay();
            
            // For leave/holiday/weekend logic (simplified placeholder as requested by spec "check existing")
            // Normally we'd query LeaveRequests, Holidays, etc.
            // If they are on leave, return early. (Assuming not implemented here yet, so skipping to core engine).

            $resolved = app(\App\Services\ShiftResolverService::class)->resolve($employee, $date);
            $shift = $resolved['shift'];
            $scheduleSource = $resolved['source'];
            
            if (!$shift) {
                // If it's a scheduled OFF day
                if ($scheduleSource === 'WEEKLY' && $resolved['reason'] === 'Scheduled OFF') {
                    $summary = DailyAttendanceSummary::updateOrCreate(
                        [
                            'employee_id' => $employeeId,
                            'attendance_date' => $logicalDate->toDateString(),
                        ],
                        [
                            'status' => self::STATUS_OFF_DAY,
                            'calculation_version' => 2,
                            'schedule_source' => $scheduleSource,
                        ]
                    );
                    return $summary;
                }

                Log::warning("No shift assigned for employee {$employeeId} on {$date}");
                return false;
            }

            $shiftStart = Carbon::parse($date . ' ' . $shift->start_time);
            $shiftEnd = Carbon::parse($date . ' ' . $shift->end_time);

            if ($shift->is_cross_midnight || $shiftEnd->lt($shiftStart)) {
                $shiftEnd->addDay();
            }

            // Dynamic Window
            $windowStart = $shiftStart->copy()->subHours(6);
            if ($shift->early_in_threshold) {
                $windowStart = Carbon::parse($date . ' ' . $shift->early_in_threshold);
                if ($windowStart->gt($shiftStart)) $windowStart->subDay();
            }

            $windowEnd = $shiftEnd->copy()->addHours(8);
            if ($shift->overtime_start) {
                $otEndGuess = Carbon::parse($date . ' ' . $shift->overtime_start);
                if ($otEndGuess->lt($shiftStart) && !$shift->is_cross_midnight) {
                    $otEndGuess->addDay();
                } elseif ($shift->is_cross_midnight && $otEndGuess->lt($shiftStart->copy()->addHours(12))) {
                    $otEndGuess->addDay();
                }
                $windowEnd = max($windowEnd, $otEndGuess->copy()->addHours(6));
            }

            $logs = AttendanceLog::where('employee_id', $employeeId)
                ->whereBetween('attendance_timestamp', [$windowStart, $windowEnd])
                ->orderBy('attendance_timestamp', 'asc')
                ->get();

            $flags = [
                'is_early_in' => false,
                'is_late_in' => false,
                'is_early_out' => false,
                'is_late_out' => false,
                'is_ot_eligible' => false,
                'is_missing_in' => false,
                'is_missing_out' => false,
                'is_grace_used' => false,
            ];

            $lateMinutes = 0;
            $earlyOutMinutes = 0;
            $workingMinutes = 0;
            $overtimeMinutes = 0;
            $status = self::STATUS_ABSENT; 

            $debounceWindow = config('attendance.duplicate_punch_window_seconds', 60);
            $meaningfulLogs = collect();
            $lastRetainedLog = null;

            foreach ($logs as $log) {
                if ($lastRetainedLog === null) {
                    $meaningfulLogs->push($log);
                    $lastRetainedLog = $log;
                    continue;
                }

                $state = strtolower($log->attendance_status);
                $type = strtolower($log->attendance_type);
                $isExplicitIn = ($log->attendance_status === '0' || $state === 'in' || $type === 'check-in');
                $isExplicitOut = ($log->attendance_status === '1' || $state === 'out' || $type === 'check-out');

                $lastState = strtolower($lastRetainedLog->attendance_status);
                $lastType = strtolower($lastRetainedLog->attendance_type);
                $lastIsExplicitIn = ($lastRetainedLog->attendance_status === '0' || $lastState === 'in' || $lastType === 'check-in');
                $lastIsExplicitOut = ($lastRetainedLog->attendance_status === '1' || $lastState === 'out' || $lastType === 'check-out');

                $timeDiff = $lastRetainedLog->attendance_timestamp->diffInSeconds($log->attendance_timestamp);
                $sameSource = ($log->source === $lastRetainedLog->source && $log->device_id === $lastRetainedLog->device_id);

                $isBothUnknown = (!$isExplicitIn && !$isExplicitOut) && (!$lastIsExplicitIn && !$lastIsExplicitOut);
                $isSameExplicit = ($isExplicitIn && $lastIsExplicitIn) || ($isExplicitOut && $lastIsExplicitOut);

                if ($timeDiff <= $debounceWindow && $sameSource && ($isBothUnknown || $isSameExplicit)) {
                    continue; // Suppress duplicate
                }

                $meaningfulLogs->push($log);
                $lastRetainedLog = $log;
            }

            $logs = $meaningfulLogs;

            $intervals = [];
            $currentIn = null;

            foreach ($logs as $log) {
                $state = strtolower($log->attendance_status);
                $type = strtolower($log->attendance_type);
                
                $isExplicitIn = ($log->attendance_status === '0' || $state === 'in' || $type === 'check-in');
                $isExplicitOut = ($log->attendance_status === '1' || $state === 'out' || $type === 'check-out');
                
                if (!$isExplicitIn && !$isExplicitOut) {
                    if ($currentIn === null) $isExplicitIn = true;
                    else $isExplicitOut = true;
                }
                
                if ($isExplicitIn) {
                    if ($currentIn === null) {
                        $currentIn = $log->attendance_timestamp;
                    }
                } elseif ($isExplicitOut) {
                    if ($currentIn !== null) {
                        $intervals[] = ['in' => $currentIn, 'out' => $log->attendance_timestamp];
                        $currentIn = null;
                    } else {
                        $flags['is_missing_in'] = true;
                    }
                }
            }

            if ($currentIn !== null) {
                $intervals[] = ['in' => $currentIn, 'out' => null];
                $flags['is_missing_out'] = true;
            }

            $firstIn = null;
            $lastOut = null;

            if (empty($intervals) && !$flags['is_missing_in']) {
                $absentThreshold = clone $shiftEnd;
                if ($shift->absent_threshold) {
                    $absentThreshold = Carbon::parse($date . ' ' . $shift->absent_threshold);
                    if ($shift->is_cross_midnight && $absentThreshold->lt($shiftStart->copy()->subHours(6))) {
                        $absentThreshold->addDay();
                    }
                }
                
                if (now()->lt($absentThreshold)) {
                    $status = self::STATUS_PENDING;
                }
            } else {
                if (isset($intervals[0]['in'])) $firstIn = clone $intervals[0]['in'];
                $lastInterval = end($intervals);
                if ($lastInterval && $lastInterval['out']) {
                    $lastOut = clone $lastInterval['out'];
                }
                
                if ($flags['is_missing_in'] || $flags['is_missing_out']) {
                    $status = self::STATUS_INCOMPLETE;
                } else {
                    $status = self::STATUS_PRESENT; 
                }

                if ($firstIn) {
                    $graceEnd = $shiftStart->copy()->addMinutes((int)$shift->grace_period);
                    
                    $lateThreshold = null;
                    if ($shift->late_threshold) {
                        $lateThreshold = Carbon::parse($date . ' ' . $shift->late_threshold);
                        if ($shift->is_cross_midnight && $lateThreshold->lt($shiftStart->copy()->subHours(6))) $lateThreshold->addDay();
                    }

                    $halfDayThreshold = null;
                    if ($shift->half_day_threshold) {
                        $halfDayThreshold = Carbon::parse($date . ' ' . $shift->half_day_threshold);
                        if ($shift->is_cross_midnight && $halfDayThreshold->lt($shiftStart->copy()->subHours(6))) $halfDayThreshold->addDay();
                    }
                    
                    $secondHalfStart = null;
                    if ($shift->second_half_start) {
                        $secondHalfStart = Carbon::parse($date . ' ' . $shift->second_half_start);
                        if ($shift->is_cross_midnight && $secondHalfStart->lt($shiftStart->copy()->subHours(6))) $secondHalfStart->addDay();
                    }

                    if ($firstIn->lt($shiftStart)) {
                        $flags['is_early_in'] = true;
                    } elseif ($firstIn->gt($shiftStart) && $firstIn->lte($graceEnd)) {
                        $flags['is_grace_used'] = true;
                    } elseif ($firstIn->gt($graceEnd)) {
                        if ($secondHalfStart && $firstIn->gte($secondHalfStart)) {
                            if ($status !== self::STATUS_INCOMPLETE) $status = self::STATUS_SECOND_HALF;
                        } elseif ($lateThreshold && $firstIn->gt($lateThreshold)) {
                            if ($status !== self::STATUS_INCOMPLETE) $status = self::STATUS_HALF_DAY;
                        } else {
                            $flags['is_late_in'] = true;
                            $lateMinutes = $shiftStart->diffInMinutes($firstIn); 
                        }
                    }
                }

                if ($lastOut) {
                    $firstHalfEnd = null;
                    if ($shift->first_half_end) {
                        $firstHalfEnd = Carbon::parse($date . ' ' . $shift->first_half_end);
                        if ($shift->is_cross_midnight && $firstHalfEnd->lt($shiftStart->copy()->subHours(6))) $firstHalfEnd->addDay();
                    }

                    // Early Out Evaluation
                    // If Early Out Tracking is disabled (both threshold is null and grace is 0), early out is not flagged.
                    $hasEarlyOutRule = !empty($shift->early_out_threshold) || ((int)($shift->early_out_grace ?? 0) > 0);

                    if ($hasEarlyOutRule && $lastOut->lt($shiftEnd)) {
                        $graceMinutes = (int)($shift->early_out_grace ?? 0);
                        if ($graceMinutes > 0) {
                            // Authoritative grace boundary: Normal End Time minus early_out_grace
                            $graceBoundary = $shiftEnd->copy()->subMinutes($graceMinutes);
                        } elseif (!empty($shift->early_out_threshold)) {
                            $graceBoundary = Carbon::parse($date . ' ' . $shift->early_out_threshold);
                            if ($shift->is_cross_midnight && $graceBoundary->lt($shiftStart->copy()->subHours(6))) {
                                $graceBoundary->addDay();
                            }
                        } else {
                            $graceBoundary = clone $shiftEnd;
                        }

                        // A checkout at or after Grace Boundary is NOT Early Out due to grace.
                        // A checkout strictly before Grace Boundary is flagged as Early Out.
                        if ($lastOut->lt($graceBoundary)) {
                            $flags['is_early_out'] = true;
                            $earlyOutMinutes = $lastOut->diffInMinutes($shiftEnd);
                            
                            if ($firstHalfEnd && $lastOut->lt($firstHalfEnd)) {
                                if ($status !== self::STATUS_INCOMPLETE && $status !== self::STATUS_SECOND_HALF && $status !== self::STATUS_HALF_DAY) {
                                    $status = self::STATUS_FIRST_HALF;
                                }
                            }
                        }
                    } elseif ($lastOut->gte($shiftEnd)) {
                        $flags['is_late_out'] = true;
                    }
                    
                    if ($shift->overtime_eligibility) {
                        $otStart = clone $shiftEnd;
                        if ($shift->overtime_start) {
                            $otStart = Carbon::parse($date . ' ' . $shift->overtime_start);
                            if ($shift->is_cross_midnight && $otStart->lt($shiftStart->copy()->subHours(6))) $otStart->addDay();
                        }
                        
                        if ($lastOut->gte($otStart)) {
                            $otMins = $otStart->diffInMinutes($lastOut);
                            if ($otMins >= (int)$shift->minimum_overtime_minutes) {
                                $flags['is_ot_eligible'] = true;
                                $overtimeMinutes = $otMins;
                            }
                        }
                    }
                }

                foreach ($intervals as $interval) {
                    if ($interval['in'] && $interval['out']) {
                        $iStart = clone $interval['in'];
                        $iEnd = clone $interval['out'];
                        $wMins = $iStart->diffInMinutes($iEnd);
                        
                        foreach ($shift->breaks as $break) {
                            if (!$break->is_paid) {
                                $bStart = Carbon::parse($date . ' ' . $break->start_time);
                                $bEnd = Carbon::parse($date . ' ' . $break->end_time);
                                if ($shift->is_cross_midnight && $bStart->lt($shiftStart->copy()->subHours(6))) $bStart->addDay();
                                if ($shift->is_cross_midnight && $bEnd->lt($shiftStart->copy()->subHours(6))) $bEnd->addDay();
                                if ($bEnd->lt($bStart)) $bEnd->addDay();
                                
                                $overlapStart = max($iStart->timestamp, $bStart->timestamp);
                                $overlapEnd = min($iEnd->timestamp, $bEnd->timestamp);
                                
                                if ($overlapStart < $overlapEnd) {
                                    $overlapMins = floor(($overlapEnd - $overlapStart) / 60);
                                    $wMins -= $overlapMins;
                                }
                            }
                        }
                        $workingMinutes += max(0, $wMins);
                    }
                }
            }

            $summary = DailyAttendanceSummary::updateOrCreate(
                [
                    'employee_id' => $employeeId,
                    'attendance_date' => $logicalDate->toDateString(),
                ],
                array_merge([
                    'shift_id' => $shift->id,
                    'check_in' => $firstIn,
                    'check_out' => $lastOut,
                    'late_minutes' => $lateMinutes,
                    'early_out_minutes' => $earlyOutMinutes,
                    'overtime_minutes' => $overtimeMinutes,
                    'working_minutes' => $workingMinutes,
                    'status' => $status,
                    'calculation_version' => 2,
                    'schedule_source' => $scheduleSource ?? null,
                ], $flags)
            );

            return $summary;

        } catch (\Exception $e) {
            Log::error("Attendance processing failed for employee {$employeeId} on {$date}: " . $e->getMessage());
            return false;
        }
    }
}
