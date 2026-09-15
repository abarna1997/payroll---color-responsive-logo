<?php

namespace App\Http\Controllers;

use App\Models\PayrollConfiguration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class PayrollConfigurationController extends Controller
{


    public function index()
    {
        // Show current configurations
        // Group by setting_key and show only active ones where effective_to is null or in future
        $companyId = Auth::check() && Auth::user()->employee ? Auth::user()->employee->company_id : null;
        
        $query = PayrollConfiguration::where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('effective_to')
                  ->orWhere('effective_to', '>=', now()->toDateString());
            });
            
        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        $configurations = $query->orderBy('setting_key')
            ->orderBy('effective_from', 'desc')
            ->get()
            ->groupBy('setting_key')
            ->map(function ($items) {
                return $items->first(); // Get most recently effective one
            });

        return view('payroll.configuration.index', compact('configurations'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'setting_key' => 'required|string|max:100',
            'value_type' => 'required|string|in:decimal,integer,boolean,string',
            'setting_value' => 'required|string|max:255',
            'effective_from' => 'required|date',
            'reason' => 'required|string|max:1000',
        ]);
        
        $companyId = Auth::check() && Auth::user()->employee ? Auth::user()->employee->company_id : null;
        
        // Prevent negative values for numbers
        if (in_array($validated['value_type'], ['decimal', 'integer']) && (float)$validated['setting_value'] < 0) {
            throw ValidationException::withMessages(['setting_value' => 'Value cannot be negative.']);
        }
        // Prevent 0 divisor for no_pay_divisor or ot_divisor
        if (in_array($validated['setting_key'], ['no_pay_divisor', 'ot_divisor']) && (float)$validated['setting_value'] <= 0) {
            throw ValidationException::withMessages(['setting_value' => 'Divisor must be greater than 0.']);
        }

        PayrollConfiguration::create([
            'company_id' => $companyId,
            'setting_key' => $validated['setting_key'],
            'value_type' => $validated['value_type'],
            'setting_value' => $validated['setting_value'],
            'effective_from' => $validated['effective_from'],
            'effective_to' => null,
            'is_active' => true, // Still active but in DRAFT state
            'status' => 'DRAFT',
            'updated_by' => Auth::id(),
            'approval_reason' => $validated['reason'],
        ]);

        return redirect()->route('payroll.configuration.index')->with('success', 'Configuration drafted successfully. Pending approval.');
    }

    public function approve(Request $request, $id)
    {
        $config = PayrollConfiguration::findOrFail($id);
        
        if ($config->status !== 'DRAFT') {
            return back()->with('error', 'Only draft configurations can be approved.');
        }

        // Optional: Check permissions (e.g., Gate::authorize('approve-payroll-config'))
        // Optional: Prevent self-approval if required by policy
        // if ($config->updated_by === Auth::id()) {
        //    return back()->with('error', 'You cannot approve your own configuration draft.');
        // }

        $validated = $request->validate([
            'approval_reason' => 'required|string|max:1000',
        ]);

        // Close overlap: if there's a current configuration without effective_to, close it just before this new effective_from
        $existingOpen = PayrollConfiguration::where('setting_key', $config->setting_key)
            ->where('status', 'APPROVED')
            ->where('is_active', true)
            ->where('company_id', $config->company_id)
            ->whereNull('effective_to')
            ->first();

        if ($existingOpen) {
            // Check if effective_from is before the existing one
            if (Carbon::parse($config->effective_from)->lt(Carbon::parse($existingOpen->effective_from))) {
                return back()->with('error', 'New effective date cannot be earlier than the current setting\'s effective date.');
            }
            $existingOpen->effective_to = Carbon::parse($config->effective_from)->subDay()->toDateString();
            $existingOpen->save();
        }

        $config->update([
            'status' => 'APPROVED',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'approval_reason' => $config->approval_reason . ' | Approved: ' . $validated['approval_reason'],
        ]);

        return redirect()->route('payroll.configuration.index')->with('success', 'Configuration approved successfully.');
    }
    
    public function reject(Request $request, $id)
    {
        $config = PayrollConfiguration::findOrFail($id);
        
        if ($config->status !== 'DRAFT') {
            return back()->with('error', 'Only draft configurations can be rejected.');
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        $config->update([
            'status' => 'REJECTED',
            'rejected_by' => Auth::id(),
            'rejected_at' => now(),
            'rejection_reason' => $validated['rejection_reason'],
            'is_active' => false,
        ]);

        return redirect()->route('payroll.configuration.index')->with('success', 'Configuration rejected successfully.');
    }
}
