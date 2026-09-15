<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Attendance\WebPunchEligibilityService;
use App\Models\AttendanceLog;
use App\Models\AuditLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use OpenApi\Attributes as OA;

#[OA\Info(
    version: "1.0.0",
    title: "Bio-metrics AMS API Documentation",
    description: "API Endpoints for Bio-metrics Attendance Management System"
)]
#[OA\Server(
    url: L5_SWAGGER_CONST_HOST,
    description: "Main API Server"
)]
class WebPunchController extends Controller
{
    protected $eligibilityService;

    public function __construct(WebPunchEligibilityService $eligibilityService)
    {
        $this->eligibilityService = $eligibilityService;
    }

    #[OA\Post(
        path: "/api/attendance/web-punch",
        summary: "Submit a Web Punch Attendance Record",
        tags: ["Attendance"]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["latitude", "longitude", "accuracy", "action"],
            properties: [
                new OA\Property(property: "latitude", type: "number", format: "float", example: 6.9271),
                new OA\Property(property: "longitude", type: "number", format: "float", example: 79.8612),
                new OA\Property(property: "accuracy", type: "number", format: "float", example: 25.5),
                new OA\Property(property: "action", type: "string", enum: ["CHECK_IN", "CHECK_OUT"])
            ]
        )
    )]
    #[OA\Response(response: 200, description: "Successful operation")]
    #[OA\Response(response: 400, description: "Invalid state (already checked in/out)")]
    #[OA\Response(response: 403, description: "Forbidden (not eligible, GPS out of bounds, etc.)")]
    public function store(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'accuracy' => 'required|numeric|min:0',
            'action' => 'required|in:CHECK_IN,CHECK_OUT'
        ]);

        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee) {
            return response()->json(['error' => 'No employee profile linked.'], 403);
        }

        $date = Carbon::today()->toDateString();
        $eligibility = $this->eligibilityService->canWebPunch($employee, $date);

        if (!$eligibility['eligible']) {
            return response()->json(['error' => $eligibility['message']], 403);
        }

        // GPS Validation
        if ($request->accuracy > 500) {
            return response()->json(['error' => 'GPS accuracy is too low. Please try again.'], 403);
        }

        $distance = $this->calculateDistance(
            $employee->home_latitude,
            $employee->home_longitude,
            $request->latitude,
            $request->longitude
        );

        $allowedRadius = $employee->allowed_radius ?? 150;

        if ($distance > $allowedRadius) {
            return response()->json(['error' => 'You are outside the approved WFH location.'], 403);
        }

        $expectedAction = $eligibility['next_action'];
        $requestedAction = $request->action === 'CHECK_IN' ? 'Check-In' : 'Check-Out';

        if ($requestedAction === 'Check-In' && $expectedAction !== 'CHECK_IN') {
            return response()->json(['error' => 'You have already checked in today.'], 400);
        }

        if ($requestedAction === 'Check-Out' && $expectedAction !== 'CHECK_OUT') {
            return response()->json(['error' => 'You must check in before checking out, or you have already checked out.'], 400);
        }

        $now = Carbon::now();

        DB::beginTransaction();
        try {
            $attendanceLog = AttendanceLog::create([
                'employee_id' => $employee->id,
                'attendance_date' => $now->toDateString(),
                'attendance_time' => $now->toTimeString(),
                'attendance_timestamp' => $now,
                'verification_method' => 'WEB',
                'source' => 'WEB',
                'device_serial' => 'WEB-PORTAL',
                'attendance_status' => 'Present', // Further rules can be applied
                'attendance_type' => $requestedAction,
                'wfh_request_id' => $eligibility['wfh_request_id'],
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'gps_accuracy' => $request->accuracy,
            ]);

            AuditLog::create([
                'user_id' => $user->id,
                'action' => 'WFH_WEB_PUNCH',
                'module' => 'Attendance',
                'record_id' => $attendanceLog->id,
                'new_value' => json_encode($attendanceLog),
                'ip_address' => $request->ip(),
                'browser' => $request->header('User-Agent'),
            ]);

            DB::commit();

            return response()->json([
                'message' => "Successfully $requestedAction via Web Punch.",
                'log' => $attendanceLog
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to record attendance.'], 500);
        }
    }

    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // in meters

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);
             
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
