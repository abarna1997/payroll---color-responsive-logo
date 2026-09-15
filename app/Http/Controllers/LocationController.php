<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\Company;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LocationController extends Controller
{
    public function index()
    {
        if (!Auth::user()->hasPermissionTo('company.view')) {
            abort(403, 'Unauthorized action.');
        }

        $locations = Location::with('company')->orderBy('location_name')->get();
        $companies = Company::orderBy('company_name')->get();
        
        return view('locations.index', compact('locations', 'companies'));
    }

    public function store(Request $request)
    {
        if (!Auth::user()->hasPermissionTo('company.manage')) {
            return back()->with('error', 'Unauthorized action.');
        }

        $request->validate([
            'company_id' => 'required|exists:companies,id',
            'location_name' => 'required|string|max:255',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
        ]);

        $location = Location::create($request->all());

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'CREATE_LOCATION',
            'module' => 'Organization',
            'record_id' => $location->id,
            'new_value' => json_encode($location),
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Location created successfully!');
    }

    public function update(Request $request, $id)
    {
        if (!Auth::user()->hasPermissionTo('company.manage')) {
            return back()->with('error', 'Unauthorized action.');
        }

        $location = Location::findOrFail($id);

        $request->validate([
            'company_id' => 'required|exists:companies,id',
            'location_name' => 'required|string|max:255',
            'status' => 'required|in:Active,Inactive',
        ]);

        $old = json_encode($location);
        $location->update($request->all());

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'UPDATE_LOCATION',
            'module' => 'Organization',
            'record_id' => $location->id,
            'old_value' => $old,
            'new_value' => json_encode($location),
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Location updated successfully!');
    }

    public function destroy(Request $request, $id)
    {
        if (!Auth::user()->hasPermissionTo('company.manage')) {
            return back()->with('error', 'Unauthorized action.');
        }

        $location = Location::findOrFail($id);
        $old = json_encode($location);
        $location->delete();

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'DELETE_LOCATION',
            'module' => 'Organization',
            'record_id' => $id,
            'old_value' => $old,
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Location deleted successfully!');
    }
}
