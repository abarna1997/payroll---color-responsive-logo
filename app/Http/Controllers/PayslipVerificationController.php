<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payslip;

class PayslipVerificationController extends Controller
{
    public function verify($hash)
    {
        $payslip = Payslip::with(['employee.company', 'payrollPeriod'])->where('verification_hash', $hash)->first();
        
        if (!$payslip) {
            return view('verification.invalid');
        }

        return view('verification.payslip', compact('payslip'));
    }
}
