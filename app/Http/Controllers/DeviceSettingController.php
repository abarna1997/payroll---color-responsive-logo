<?php

namespace App\Http\Controllers;

use App\Models\DeviceSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DeviceSettingController extends Controller
{
    public function index()
    {
        if (!Auth::user()->hasPermissionTo('admin.settings')) {
            abort(403, 'Unauthorized action.');
        }

        $settings = DeviceSetting::orderBy('key')->get();
        return view('devices.settings', compact('settings'));
    }

    public function store(Request $request)
    {
        if (!Auth::user()->hasPermissionTo('admin.settings')) {
            return back()->with('error', 'Unauthorized action.');
        }

        $request->validate([
            'key' => 'required|string|unique:device_settings,key',
        ]);

        DeviceSetting::create($request->all());

        return back()->with('success', 'Device setting created!');
    }

    public function update(Request $request, $id)
    {
        $setting = DeviceSetting::findOrFail($id);
        $setting->update($request->all());
        return back()->with('success', 'Device setting updated!');
    }

    public function destroy($id)
    {
        DeviceSetting::findOrFail($id)->delete();
        return back()->with('success', 'Device setting deleted!');
    }
}
