<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Shift;
use App\Models\EmployeeShiftAssignment;
use App\Models\EmployeeScheduleOverride;
use App\Models\WeeklySchedule;
use App\Services\ShiftResolverService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ScheduleController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->input('date', Carbon::today('Asia/Colombo')->toDateString());
        $viewType = $request->input('view', 'month');
        $shifts = Shift::all();
        
        $companiesQuery = \App\Models\Company::with(['branches', 'departments']);
        
        // Enforce Company Isolation if user is restricted
        if (auth()->check() && auth()->user()->company_id) {
            $companiesQuery->where('id', auth()->user()->company_id);
        }
        
        $companies = $companiesQuery->get();
        
        return view('schedules.index', compact('date', 'viewType', 'shifts', 'companies'));
    }

    public function fetchSchedules(Request $request, ShiftResolverService $resolver)
    {
        $start = Carbon::parse($request->input('start'))->startOfDay();
        $end = Carbon::parse($request->input('end'))->endOfDay();
        
        $query = Employee::with(['department']);

        if ($request->filled('employee_id')) {
            $query->where('id', $request->input('employee_id'));
        }
        
        if ($request->filled('company_id')) {
            $query->where('company_id', $request->input('company_id'));
        }
        
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->input('branch_id'));
        }
        
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->input('department_id'));
        }
        
        if ($request->filled('employee_search')) {
            $search = $request->input('employee_search');
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('employee_id', 'like', "%{$search}%");
            });
        }
        
        // Enforce Company Isolation if user is restricted
        if (auth()->check() && auth()->user()->company_id) {
             $query->where('company_id', auth()->user()->company_id);
        }

        $viewMode = $request->input('view_mode', 'overview');
        $requestedEmployeeIds = $request->input('employee_ids', []);

        if ($viewMode === 'employees') {
            if (empty($requestedEmployeeIds)) {
                return response()->json([
                    'view_mode' => 'employees',
                    'employees' => [],
                    'schedules' => [],
                    'summary_events' => []
                ]);
            }
            $query->whereIn('id', $requestedEmployeeIds);
        }

        // We fetch all matching employees. In overview mode, this is all authorized employees in scope.
        // In employees mode, it's the requested authorized IDs.
        $employees = $query->get();
        $employeeIds = $employees->pluck('id')->toArray();

        // Preload Schedule Data to prevent N+1 queries
        $preloadedData = [
            'overrides' => \App\Models\EmployeeScheduleOverride::whereIn('employee_id', $employeeIds)
                ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
                ->get(),
            'weeklies' => \App\Models\WeeklySchedule::whereIn('employee_id', $employeeIds)
                ->whereDate('effective_from', '<=', $end)
                ->where(function($q) use ($start) {
                    $q->whereNull('effective_to')
                      ->orWhereDate('effective_to', '>=', $start);
                })->get(),
            'assignments' => \App\Models\EmployeeShiftAssignment::whereIn('employee_id', $employeeIds)
                ->whereDate('effective_from', '<=', $end)
                ->where(function($q) use ($start) {
                    $q->whereNull('effective_to')
                      ->orWhereDate('effective_to', '>=', $start);
                })->get(),
            'shifts' => \App\Models\Shift::all()
        ];

        $schedules = [];
        $overviewAggregates = [];

        foreach ($employees as $employee) {
            $empSchedules = [];
            for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
                $resolved = $resolver->resolve($employee, $date, $preloadedData);
                
                // Filter by resolved shift if requested
                if ($request->filled('shift_id')) {
                    $reqShift = $request->input('shift_id');
                    $isOff = ($reqShift === 'OFF');
                    $resolvedShiftId = $resolved['shift'] ? $resolved['shift']->id : null;
                    
                    if ($isOff && $resolvedShiftId !== null) continue;
                    if (!$isOff && $resolvedShiftId != $reqShift) continue;
                }
                
                // Filter by source if requested
                if ($request->filled('source') && $resolved['source'] !== $request->input('source')) {
                    continue;
                }

                $dateStr = $date->toDateString();
                $shiftId = $resolved['shift'] ? $resolved['shift']->id : 'OFF';
                $shiftName = $resolved['shift'] ? $resolved['shift']->shift_name : 'OFF';
                $source = $resolved['source'];

                if ($viewMode === 'overview') {
                    // Aggregate counts
                    if (!isset($overviewAggregates[$dateStr])) {
                        $overviewAggregates[$dateStr] = [];
                    }
                    if (!isset($overviewAggregates[$dateStr][$shiftId])) {
                        $overviewAggregates[$dateStr][$shiftId] = [
                            'shift_name' => $shiftName,
                            'count' => 0,
                            'employees' => []
                        ];
                    }
                    $overviewAggregates[$dateStr][$shiftId]['count']++;
                    // Optional: store minimal employee details for the drill-down popup
                    $overviewAggregates[$dateStr][$shiftId]['employees'][] = [
                        'id' => $employee->id,
                        'name' => $employee->first_name . ' ' . $employee->last_name,
                        'department' => $employee->department ? $employee->department->department_name : 'N/A'
                    ];
                } else {
                    // Employee mode: build individual events
                    if ($resolved['shift']) {
                        $empSchedules[] = [
                            'date' => $dateStr,
                            'shift_id' => $shiftId,
                            'shift_name' => $shiftName,
                            'source' => $source,
                        ];
                    } else if (in_array($source, ['WEEKLY', 'OVERRIDE'])) {
                        $empSchedules[] = [
                            'date' => $dateStr,
                            'shift_id' => null,
                            'shift_name' => 'OFF',
                            'source' => $source,
                        ];
                    }
                }
            }
            if ($viewMode === 'employees' && count($empSchedules) > 0) {
                $schedules[$employee->id] = $empSchedules;
            }
        }

        $summaryEvents = [];
        if ($viewMode === 'overview') {
            foreach ($overviewAggregates as $date => $shifts) {
                foreach ($shifts as $sId => $data) {
                    $summaryEvents[] = [
                        'event_type' => 'SHIFT_SUMMARY',
                        'date' => $date,
                        'shift_id' => $sId,
                        'shift_name' => $data['shift_name'],
                        'employee_count' => $data['count'],
                        'employees_list' => $data['employees']
                    ];
                }
            }
        }

        return response()->json([
            'view_mode' => $viewMode,
            'employees' => $employees, // Send full objects for lookup
            'schedules' => $schedules,
            'summary_events' => $summaryEvents
        ]);
    }

    private function authorizeEmployee($employeeId)
    {
        $employee = Employee::findOrFail($employeeId);
        if (auth()->check() && auth()->user()->company_id) {
            abort_if($employee->company_id !== auth()->user()->company_id, 403, 'Unauthorized access to employee schedule.');
        }
        return $employee;
    }

    public function storeOverride(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'shift_id' => 'required',
            'reason' => 'nullable|string'
        ]);

        $this->authorizeEmployee($validated['employee_id']);

        $shiftId = $validated['shift_id'] === 'OFF' ? null : $validated['shift_id'];

        EmployeeScheduleOverride::updateOrCreate(
            ['employee_id' => $validated['employee_id'], 'date' => $validated['date']],
            ['shift_id' => $shiftId, 'reason' => $validated['reason'], 'created_by' => auth()->id()]
        );

        // Audit log
        \App\Models\AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'Created Schedule Override',
            'description' => "Override created for Employee {$validated['employee_id']} on {$validated['date']} (Shift: {$validated['shift_id']})"
        ]);

        return response()->json(['status' => 'success']);
    }

    public function storeAssignment(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'shift_id' => 'required|exists:shifts,id',
            'effective_from' => 'required|date',
            'effective_to' => 'nullable|date|after_or_equal:effective_from',
        ]);

        $this->authorizeEmployee($validated['employee_id']);

        // Basic conflict detection
        $conflict = EmployeeShiftAssignment::where('employee_id', $validated['employee_id'])
            ->where(function($query) use ($validated) {
                $query->where(function($q) use ($validated) {
                    $q->whereNull('effective_to')
                      ->orWhere('effective_to', '>=', $validated['effective_from']);
                });
                if (!empty($validated['effective_to'])) {
                    $query->where('effective_from', '<=', $validated['effective_to']);
                }
            })->exists();

        if ($conflict) {
            return response()->json(['status' => 'error', 'message' => 'Schedule Conflict: Overlapping assignment exists.'], 422);
        }

        $validated['created_by'] = auth()->id();
        EmployeeShiftAssignment::create($validated);

        // Audit log
        \App\Models\AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'Created Shift Assignment',
            'description' => "Shift Assignment {$validated['shift_id']} created for Employee {$validated['employee_id']} from {$validated['effective_from']}"
        ]);

        return response()->json(['status' => 'success']);
    }

    public function storeWeeklySchedule(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'effective_from' => 'required|date',
            'effective_to' => 'nullable|date|after_or_equal:effective_from',
            'monday_shift' => 'nullable|string',
            'tuesday_shift' => 'nullable|string',
            'wednesday_shift' => 'nullable|string',
            'thursday_shift' => 'nullable|string',
            'friday_shift' => 'nullable|string',
            'saturday_shift' => 'nullable|string',
            'sunday_shift' => 'nullable|string',
        ]);

        $this->authorizeEmployee($validated['employee_id']);

        $validated['created_by'] = auth()->id();
        WeeklySchedule::create($validated);

        // Audit log
        \App\Models\AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'Created Weekly Schedule',
            'description' => "Weekly schedule pattern created for Employee {$validated['employee_id']} from {$validated['effective_from']}"
        ]);

        return response()->json(['status' => 'success']);
    }

    public function bulkAssign(Request $request)
    {
        $validated = $request->validate([
            'employee_ids' => 'required|string', // Changed to string from frontend textarea
            'shift_id' => 'required|exists:shifts,id',
            'effective_from' => 'required|date',
            'effective_to' => 'nullable|date|after_or_equal:effective_from',
        ]);

        $empIds = array_map('trim', explode(',', $validated['employee_ids']));

        DB::transaction(function() use ($validated, $empIds) {
            foreach($empIds as $empId) {
                if (empty($empId)) continue;
                
                // If it's a numeric ID, try to find employee
                $emp = Employee::where('id', $empId)->orWhere('employee_id', $empId)->first();
                if (!$emp) continue;
                
                $this->authorizeEmployee($emp->id);

                // End-date overlaps
                $active = EmployeeShiftAssignment::where('employee_id', $emp->id)
                            ->whereNull('effective_to')
                            ->where('effective_from', '<=', $validated['effective_from'])
                            ->first();
                if ($active) {
                    $active->effective_to = Carbon::parse($validated['effective_from'])->subDay();
                    $active->save();
                }
                
                EmployeeShiftAssignment::create([
                    'employee_id' => $emp->id,
                    'shift_id' => $validated['shift_id'],
                    'effective_from' => $validated['effective_from'],
                    'effective_to' => $validated['effective_to'] ?? null,
                    'created_by' => auth()->id()
                ]);
            }
        });
        
        \App\Models\AuditLog::create([
            'user_id' => auth()->id(),
            'module' => 'Schedule Management',
            'action' => 'Bulk Shift Assignment',
            'description' => "Bulk assigned Shift {$validated['shift_id']} to " . count($empIds) . " employees."
        ]);

        return response()->json(['status' => 'success', 'message' => count($empIds) . ' employees updated.']);
    }

    public function bulkAssignmentEmployees(Request $request, ShiftResolverService $resolver)
    {
        $query = Employee::with(['department', 'branch', 'company']);

        if (auth()->check() && auth()->user()->company_id) {
            $query->where('company_id', auth()->user()->company_id);
        }

        if ($request->filled('company_id')) {
            $query->where('company_id', $request->input('company_id'));
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->input('branch_id'));
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->input('department_id'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('employee_id', 'like', "%{$search}%");
            });
        }

        // Get total matching for "Select All Filtered"
        $totalMatching = $query->count();
        
        $limit = $request->input('per_page', 20);
        $employees = $query->paginate($limit);

        // Map to include current shift (for today)
        $today = Carbon::today('Asia/Colombo');
        $employeeIds = $employees->pluck('id')->toArray();
        $preloadedData = [
            'overrides' => \App\Models\EmployeeScheduleOverride::whereIn('employee_id', $employeeIds)->whereDate('date', $today)->get(),
            'weeklies' => \App\Models\WeeklySchedule::whereIn('employee_id', $employeeIds)->whereDate('effective_from', '<=', $today)->where(function($q) use ($today) { $q->whereNull('effective_to')->orWhereDate('effective_to', '>=', $today); })->get(),
            'assignments' => \App\Models\EmployeeShiftAssignment::whereIn('employee_id', $employeeIds)->whereDate('effective_from', '<=', $today)->where(function($q) use ($today) { $q->whereNull('effective_to')->orWhereDate('effective_to', '>=', $today); })->get(),
            'shifts' => \App\Models\Shift::all()
        ];

        $mapped = $employees->map(function($emp) use ($resolver, $today, $preloadedData) {
            $resolved = $resolver->resolve($emp, $today, $preloadedData);
            return [
                'id' => $emp->id,
                'employee_id' => $emp->employee_id,
                'name' => $emp->first_name . ' ' . $emp->last_name,
                'branch' => $emp->branch ? $emp->branch->branch_name : '-',
                'department' => $emp->department ? $emp->department->department_name : '-',
                'current_shift' => $resolved['shift'] ? $resolved['shift']->shift_name : 'OFF',
            ];
        });

        return response()->json([
            'data' => $mapped,
            'current_page' => $employees->currentPage(),
            'last_page' => $employees->lastPage(),
            'total' => $employees->total(),
            'total_matching_filters' => $totalMatching
        ]);
    }

    private function getBulkEmployeesFromRequest(Request $request)
    {
        $query = Employee::query();
        
        if (auth()->check() && auth()->user()->company_id) {
            $query->where('company_id', auth()->user()->company_id);
        }

        if ($request->input('selection_mode') === 'organization' || $request->input('select_all_filtered') === 'true') {
            if ($request->filled('company_id')) $query->where('company_id', $request->input('company_id'));
            if ($request->filled('branch_id')) $query->where('branch_id', $request->input('branch_id'));
            if ($request->filled('department_id')) $query->where('department_id', $request->input('department_id'));
            if ($request->filled('search')) {
                $search = $request->input('search');
                $query->where(function($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")->orWhere('last_name', 'like', "%{$search}%")->orWhere('employee_id', 'like', "%{$search}%");
                });
            }
        } else {
            $ids = $request->input('employee_ids', []);
            if (!is_array($ids)) {
                $ids = explode(',', $ids);
            }
            $query->whereIn('id', $ids);
        }

        return $query->get();
    }

    public function bulkAssignmentPreview(Request $request, ShiftResolverService $resolver)
    {
        $validated = $request->validate([
            'shift_id' => 'required|exists:shifts,id',
            'effective_from' => 'required|date',
            'effective_to' => 'nullable|date|after_or_equal:effective_from',
        ]);

        $employees = $this->getBulkEmployeesFromRequest($request);
        
        $results = [];
        $counts = ['selected' => $employees->count(), 'valid' => 0, 'conflict' => 0, 'no_change' => 0, 'invalid' => 0];

        $shift = \App\Models\Shift::find($validated['shift_id']);
        $effFrom = Carbon::parse($validated['effective_from'])->startOfDay();
        $effTo = ($validated['effective_to'] ?? null) ? Carbon::parse($validated['effective_to'])->endOfDay() : null;

        foreach ($employees as $emp) {
            // Check current active assignment for the effective_from date
            $existing = \App\Models\EmployeeShiftAssignment::where('employee_id', $emp->id)
                ->where('effective_from', '<=', $effFrom)
                ->where(function($q) use ($effFrom) {
                    $q->whereNull('effective_to')->orWhere('effective_to', '>=', $effFrom);
                })
                ->orderBy('effective_from', 'desc')
                ->first();
                
            $currentShiftName = $existing && $existing->shift ? $existing->shift->shift_name : 'DEFAULT/NONE';
            $status = 'VALID';
            $reason = 'Ready to assign';

            if ($existing && $existing->shift_id == $validated['shift_id']) {
                $status = 'NO_CHANGE';
                $reason = 'Already assigned to this shift for this date';
                $counts['no_change']++;
            } else {
                // Check overlaps
                $overlapQuery = \App\Models\EmployeeShiftAssignment::where('employee_id', $emp->id)
                    ->where(function($query) use ($effFrom, $effTo) {
                        $query->where(function($q) use ($effFrom) {
                            $q->whereNull('effective_to')->orWhere('effective_to', '>=', $effFrom);
                        });
                        if ($effTo) {
                            $query->where('effective_from', '<=', $effTo);
                        }
                    });

                if ($overlapQuery->exists()) {
                    $status = 'CONFLICT';
                    $reason = 'Existing assignment will be automatically end-dated on ' . $effFrom->copy()->subDay()->toDateString();
                    $counts['conflict']++;
                } else {
                    $counts['valid']++;
                }
            }

            $results[] = [
                'employee_id' => $emp->employee_id,
                'name' => $emp->first_name . ' ' . $emp->last_name,
                'current_shift' => $currentShiftName,
                'requested_shift' => $shift->shift_name,
                'status' => $status,
                'reason' => $reason
            ];
        }

        return response()->json([
            'counts' => $counts,
            'preview' => $results,
            'operation_id' => uniqid('bulk_')
        ]);
    }

    public function bulkAssignmentExecute(Request $request)
    {
        $validated = $request->validate([
            'shift_id' => 'required|exists:shifts,id',
            'effective_from' => 'required|date',
            'effective_to' => 'nullable|date|after_or_equal:effective_from',
        ]);

        $employees = $this->getBulkEmployeesFromRequest($request);
        
        $effFrom = Carbon::parse($validated['effective_from'])->startOfDay();
        $effTo = ($validated['effective_to'] ?? null) ? Carbon::parse($validated['effective_to'])->endOfDay() : null;

        $created = 0;
        $updated = 0;
        $no_change = 0;
        $conflicts_resolved = 0;
        
        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            foreach ($employees as $emp) {
                $existing = \App\Models\EmployeeShiftAssignment::where('employee_id', $emp->id)
                    ->where('effective_from', '<=', $effFrom)
                    ->where(function($q) use ($effFrom) {
                        $q->whereNull('effective_to')->orWhere('effective_to', '>=', $effFrom);
                    })
                    ->orderBy('effective_from', 'desc')
                    ->first();

                if ($existing && $existing->shift_id == $validated['shift_id']) {
                    $no_change++;
                    continue;
                }

                // Handle Overlaps (End-date existing active assignments)
                $overlaps = \App\Models\EmployeeShiftAssignment::where('employee_id', $emp->id)
                    ->where(function($query) use ($effFrom, $effTo) {
                        $query->where(function($q) use ($effFrom) {
                            $q->whereNull('effective_to')->orWhere('effective_to', '>=', $effFrom);
                        });
                        if ($effTo) {
                            $query->where('effective_from', '<=', $effTo);
                        }
                    })->get();

                foreach ($overlaps as $overlap) {
                    if ($overlap->effective_from >= $effFrom) {
                        // Assignment starts after our new assignment, this shouldn't typically happen without explicit UI deletion
                        // For safety, we delete it if it's completely enveloped, or we adjust its start date.
                        // For simplicity, we just end-date overlapping past ones.
                        $overlap->delete();
                    } else {
                        // End date it the day before
                        $overlap->effective_to = $effFrom->copy()->subDay()->endOfDay();
                        $overlap->save();
                        $conflicts_resolved++;
                        $updated++;
                    }
                }

                \App\Models\EmployeeShiftAssignment::create([
                    'employee_id' => $emp->id,
                    'shift_id' => $validated['shift_id'],
                    'effective_from' => $effFrom,
                    'effective_to' => $effTo,
                    'created_by' => auth()->id()
                ]);
                $created++;
            }

            // Audit log
            \App\Models\AuditLog::create([
                'user_id' => auth()->id(),
            'module' => 'Schedule Management',
            'action' => 'Bulk Shift Assignment Executed',
                'description' => "Assigned shift {$validated['shift_id']} to {$employees->count()} employees starting {$effFrom->toDateString()}. Created: {$created}, Ended Overlaps: {$conflicts_resolved}"
            ]);

            \Illuminate\Support\Facades\DB::commit();

            return response()->json([
                'status' => 'success',
                'results' => [
                    'created' => $created,
                    'updated' => $updated,
                    'no_change' => $no_change,
                    'conflicts' => $conflicts_resolved,
                    'failed' => 0
                ]
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Bulk Assignment Error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'An error occurred during execution.'], 500);
        }
    }
}


