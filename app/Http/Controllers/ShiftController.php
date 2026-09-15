<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use Carbon\Carbon;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShiftController extends Controller
{
    public function index()
    {
        $shifts = Shift::all();
        return view('shifts.index', compact('shifts'));
    }

    public function store(Request $request)
    {
        $request->validate($this->validationRules());
        $this->validateLogicalRules($request);

        $data = $this->prepareData($request);
        $shift = Shift::create($data);

        $this->syncBreaks($shift, $request->input('breaks', []));

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'CREATE_SHIFT',
            'module' => 'Shift Management',
            'record_id' => $shift->id,
            'new_value' => json_encode($shift),
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Shift created successfully!');
    }

    public function update(Request $request, $id)
    {
        $shift = Shift::findOrFail($id);

        $rules = $this->validationRules();
        $rules['status'] = 'required|string|in:Active,Inactive';
        $request->validate($rules);
        $this->validateLogicalRules($request);

        $data = $this->prepareData($request);

        $oldVal = json_encode($shift);
        $shift->update($data);

        $this->syncBreaks($shift, $request->input('breaks', []));

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'UPDATE_SHIFT',
            'module' => 'Shift Management',
            'record_id' => $shift->id,
            'old_value' => $oldVal,
            'new_value' => json_encode($shift),
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Shift updated successfully!');
    }

    private function validateLogicalRules(Request $request)
    {
        $startTime = $request->input('start_time');
        $endTime = $request->input('end_time');
        $isCrossMidnight = (bool)$request->input('is_cross_midnight', 0);
        $earlyOutThreshold = $request->input('early_out_threshold');
        $earlyOutGrace = $request->input('early_out_grace');
        
        if ($startTime && $endTime) {
            $baseDate = '2026-01-01';
            $start = Carbon::parse($baseDate . ' ' . $startTime);
            $end = Carbon::parse($baseDate . ' ' . $endTime);
            if ($isCrossMidnight) {
                $end->addDay();
            }
            
            if ($earlyOutThreshold) {
                $earlyOut = Carbon::parse($baseDate . ' ' . $earlyOutThreshold);
                if ($isCrossMidnight && $earlyOut->lt($start->copy()->subHours(6))) {
                    $earlyOut->addDay();
                }
                
                if ($earlyOut->gt($end)) {
                    $message = !$isCrossMidnight 
                        ? 'Early Out Threshold cannot be later than the Normal End Time for a same-day shift.'
                        : 'Early Out Threshold cannot be later than the Normal End Time.';
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'early_out_threshold' => $message
                    ]);
                }

                if ($earlyOut->lt($start)) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'early_out_threshold' => 'Early Out Threshold cannot be earlier than the Shift Start Time.'
                    ]);
                }
            }

            if ($earlyOutGrace !== null && $earlyOutGrace !== '') {
                $graceMins = (int)$earlyOutGrace;
                if ($graceMins < 0) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'early_out_grace' => 'Early Out Grace cannot be negative.'
                    ]);
                }
                $graceBoundary = $end->copy()->subMinutes($graceMins);
                if ($graceBoundary->lt($start)) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'early_out_grace' => 'Early Out Grace cannot extend earlier than the Shift Start Time.'
                    ]);
                }
                if ($earlyOutThreshold) {
                    $earlyOut = Carbon::parse($baseDate . ' ' . $earlyOutThreshold);
                    if ($isCrossMidnight && $earlyOut->lt($start->copy()->subHours(6))) {
                        $earlyOut->addDay();
                    }
                    if ($graceBoundary->lt($earlyOut)) {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            'early_out_grace' => 'Early Out Grace produces an invalid boundary earlier than the Early Out Threshold.'
                        ]);
                    }
                }
            }
        }
    }

    private function validationRules()
    {
        return [
            'shift_name' => 'required|string',
            'start_time' => 'required',
            'end_time' => 'required',
            'grace_period' => 'required|integer|min:0',
            
            // Advanced Thresholds
            'expected_work_minutes' => 'nullable|integer',
            'early_in_threshold' => 'nullable',
            'late_threshold' => 'nullable',
            'half_day_threshold' => 'nullable',
            'absent_threshold' => 'nullable',
            'second_half_start' => 'nullable',
            'early_out_threshold' => 'nullable',
            'early_out_grace' => 'nullable|integer',
            'overtime_start' => 'nullable',
            'minimum_overtime_minutes' => 'nullable|integer',

            // Breaks
            'breaks' => 'nullable|array',
            'breaks.*.name' => 'required_with:breaks|string',
            'breaks.*.start_time' => 'required_with:breaks',
            'breaks.*.end_time' => 'required_with:breaks',
        ];
    }

    private function prepareData(Request $request)
    {
        $data = $request->except(['_token', 'breaks', 'auto_calc_addShiftModal']);
        $data['overtime_eligibility'] = $request->input('overtime_eligibility', 0);
        $data['is_cross_midnight'] = $request->input('is_cross_midnight', 0);
        
        // Ensure nullable fields are actually null if empty
        $nullableFields = [
            'early_in_threshold', 'late_threshold', 'half_day_threshold',
            'first_half_end', 
            'absent_threshold', 'second_half_start', 'early_out_threshold', 'overtime_start'
        ];
        foreach ($nullableFields as $field) {
            if (empty($data[$field])) {
                $data[$field] = null;
            }
        }
        
        return $data;
    }

    private function syncBreaks(Shift $shift, $breaks)
    {
        if (empty($breaks)) {
            $shift->breaks()->delete();
            return;
        }

        $existingBreakIds = $shift->breaks()->pluck('id')->toArray();
        $submittedBreakIds = [];

        foreach ($breaks as $breakData) {
            if (!empty($breakData['id'])) {
                // Update existing
                $b = \App\Models\ShiftBreak::find($breakData['id']);
                if ($b && $b->shift_id == $shift->id) {
                    $b->update([
                        'name' => $breakData['name'],
                        'start_time' => $breakData['start_time'],
                        'end_time' => $breakData['end_time'],
                        'is_paid' => $breakData['is_paid'] ?? 0,
                    ]);
                    $submittedBreakIds[] = $b->id;
                }
            } else {
                // Create new
                $b = $shift->breaks()->create([
                    'name' => $breakData['name'],
                    'start_time' => $breakData['start_time'],
                    'end_time' => $breakData['end_time'],
                    'is_paid' => $breakData['is_paid'] ?? 0,
                ]);
                $submittedBreakIds[] = $b->id;
            }
        }

        // Delete removed
        $toDelete = array_diff($existingBreakIds, $submittedBreakIds);
        if (!empty($toDelete)) {
            \App\Models\ShiftBreak::whereIn('id', $toDelete)->delete();
        }
    }

    public function destroy(Request $request, $id)
    {
        $shift = Shift::findOrFail($id);
        $oldVal = json_encode($shift);
        $shift->delete();

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'DELETE_SHIFT',
            'module' => 'Shift Management',
            'record_id' => $id,
            'old_value' => $oldVal,
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Shift deleted successfully!');
    }
}
