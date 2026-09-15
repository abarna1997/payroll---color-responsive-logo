<?php

namespace App\Http\Controllers;

use App\Models\AttendanceLog;
use App\Services\AttendanceEngineService;
use App\Services\WebPunchEligibilityService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class WebPunchController extends Controller
{
    protected $eligibilityService;
    protected $engineService;

    public function __construct(WebPunchEligibilityService $eligibilityService, AttendanceEngineService $engineService)
    {
        $this->eligibilityService = $eligibilityService;
        $this->engineService = $engineService;
    }

    public function punch(Request $request)
    {
        $employee = auth()->user()->employee; // Assuming employee relation on User
        
        if (!$employee) {
            return response()->json(['error' => 'No employee record found for user.'], 404);
        }

        // Strict WFH Rules execution
        $eligibility = $this->eligibilityService->checkEligibility($employee, $request->latitude, $request->longitude);
        
        if (!$eligibility['eligible']) {
            return response()->json(['error' => $eligibility['reason']], 403);
        }

        // Proceed to register punch
        $log = new AttendanceLog([
            'employee_id' => $employee->id,
            'attendance_timestamp' => Carbon::now()->format('Y-m-d H:i:s'),
            'source' => 'WEB', // Identifying source
            'device_serial' => 'WEB-PORTAL', // Explicitly mark it for the UI
            'verification_method' => 'Web Punch',
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ]);

        // Route it strictly through the same unified engine
        $summary = $this->engineService->processRawLog($log);

        return response()->json([
            'message' => 'Web punch recorded successfully.',
            'summary' => $summary
        ]);
    }
}
