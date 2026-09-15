<?php

namespace App\Http\Controllers;

use App\Models\DeviceGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DeviceGroupController extends Controller
{
    public function index()
    {
        if (!Auth::user()->hasPermissionTo('device.view')) {
            abort(403, 'Unauthorized action.');
        }

        $groups = DeviceGroup::orderBy('name')->get();
        return view('devices.groups', compact('groups'));
    }

    public function store(Request $request)
    {
        if (!Auth::user()->hasPermissionTo('device.view')) {
            return back()->with('error', 'Unauthorized action.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        DeviceGroup::create($request->all());

        return back()->with('success', 'Device group created!');
    }

    public function update(Request $request, $id)
    {
        $group = DeviceGroup::findOrFail($id);
        $group->update($request->all());
        return back()->with('success', 'Device group updated!');
    }

    public function destroy($id)
    {
        DeviceGroup::findOrFail($id)->delete();
        return back()->with('success', 'Device group deleted!');
    }
}
