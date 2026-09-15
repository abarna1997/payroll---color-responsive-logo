<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MenuController extends Controller
{
    public function index()
    {
        if (!Auth::user()->hasPermissionTo('admin.menus')) {
            abort(403, 'Unauthorized access.');
        }

        $menus = Menu::with('children')->whereNull('parent_id')->orderBy('sort_order')->get();
        return view('menus.index', compact('menus'));
    }

    public function store(Request $request)
    {
        if (!Auth::user()->hasPermissionTo('admin.menus')) {
            return back()->with('error', 'Unauthorized action.');
        }

        $request->validate([
            'title' => 'required|string',
            'icon' => 'nullable|string',
            'url' => 'nullable|string',
            'route_name' => 'nullable|string',
            'parent_id' => 'nullable|exists:menus,id',
            'permission_key' => 'nullable|string',
            'sort_order' => 'integer',
        ]);

        $menu = Menu::create($request->all());

        cache()->forget('menu_tree');

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'CREATE_MENU_ITEM',
            'module' => 'Menu Builder',
            'record_id' => $menu->id,
            'new_value' => json_encode($menu),
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Menu item added successfully!');
    }

    public function update(Request $request, $id)
    {
        if (!Auth::user()->hasPermissionTo('admin.menus')) {
            return back()->with('error', 'Unauthorized action.');
        }

        $menu = Menu::findOrFail($id);

        $request->validate([
            'title' => 'required|string',
            'icon' => 'nullable|string',
            'url' => 'nullable|string',
            'route_name' => 'nullable|string',
            'parent_id' => 'nullable|exists:menus,id',
            'permission_key' => 'nullable|string',
            'sort_order' => 'integer',
            'status' => 'required|in:Active,Inactive',
        ]);

        $old = json_encode($menu);
        $menu->update($request->all());

        cache()->forget('menu_tree');

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'UPDATE_MENU_ITEM',
            'module' => 'Menu Builder',
            'record_id' => $menu->id,
            'old_value' => $old,
            'new_value' => json_encode($menu),
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Menu item updated successfully!');
    }

    public function destroy(Request $request, $id)
    {
        if (!Auth::user()->hasPermissionTo('admin.menus')) {
            return back()->with('error', 'Unauthorized action.');
        }

        $menu = Menu::findOrFail($id);
        $old = json_encode($menu);

        $menu->delete();

        cache()->forget('menu_tree');

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'DELETE_MENU_ITEM',
            'module' => 'Menu Builder',
            'record_id' => $id,
            'old_value' => $old,
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Menu item deleted successfully!');
    }

    public function reorder(Request $request)
    {
        if (!Auth::user()->hasPermissionTo('admin.menus')) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $order = $request->input('order', []); // [id => sort_order]
        foreach ($order as $id => $sort) {
            Menu::where('id', $id)->update(['sort_order' => $sort]);
        }

        cache()->forget('menu_tree');

        return response()->json(['message' => 'Menu order saved successfully!']);
    }
}
