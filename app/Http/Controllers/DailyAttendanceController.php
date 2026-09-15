<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DailyAttendanceController extends Controller
{
    /**
     * Deprecated Daily Attendance index view.
     * Redirects to the new consolidated Report Center.
     */
    public function index(Request $request)
    {
        return redirect()->route('reports', array_merge($request->query(), ['report_type' => 'daily']));
    }

    /**
     * Deprecated export.
     * Redirects to Report Center export.
     */
    public function export(Request $request)
    {
        return redirect()->route('reports.export', array_merge($request->query(), ['report_type' => 'daily']));
    }

    /**
     * This was a detail view for a specific summary.
     * We'll redirect to the main report for now since this isn't actively linked, 
     * but preserve the intent.
     */
    public function show($id)
    {
        return redirect()->route('reports', ['report_type' => 'daily']);
    }
}
