@extends('portal.layout')

@section('styles')
<style>
    .payslip-container {
        background: white;
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 3rem;
        box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        max-width: 800px;
        margin: 0 auto;
    }
    .payslip-header {
        display: flex;
        justify-content: space-between;
        border-bottom: 2px solid var(--primary);
        padding-bottom: 1.5rem;
        margin-bottom: 2rem;
    }
    .payslip-title {
        font-size: 2rem;
        color: var(--primary);
        margin: 0 0 0.5rem 0;
    }
    .company-details p, .employee-details p {
        margin: 0.25rem 0;
        color: var(--text-muted);
    }
    .payslip-body {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 2rem;
    }
    .payslip-section h4 {
        margin-top: 0;
        color: var(--dark-light);
        border-bottom: 1px solid var(--border);
        padding-bottom: 0.5rem;
        margin-bottom: 1rem;
    }
    .amount-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 0.5rem;
        font-size: 0.95rem;
    }
    .amount-row.total {
        font-weight: 700;
        border-top: 1px dashed var(--border);
        padding-top: 0.5rem;
        margin-top: 1rem;
        font-size: 1.1rem;
        color: var(--dark);
    }
    .net-pay-box {
        background: rgba(79, 70, 229, 0.05);
        border: 1px solid rgba(79, 70, 229, 0.2);
        border-radius: 8px;
        padding: 1.5rem;
        text-align: center;
        margin-top: 2rem;
    }
    .net-pay-box h3 {
        margin: 0 0 0.5rem 0;
        color: var(--text-muted);
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .net-pay-box .amount {
        font-size: 2.5rem;
        font-weight: 700;
        color: var(--primary);
        margin: 0;
    }
    
    @media print {
        body { background: white; }
        .navbar, .btn { display: none !important; }
        .payslip-container { box-shadow: none; border: none; padding: 0; }
        .container { margin: 0; padding: 0; }
    }
</style>
@endsection

@section('content')
<div style="max-width: 800px; margin: 0 auto 1rem auto; display: flex; justify-content: space-between; align-items: center;">
    <a href="{{ route('portal.payslips.index') }}" class="btn btn-outline"><i class="fa-solid fa-arrow-left"></i> Back to List</a>
    <div>
        <a href="{{ route('portal.payslips.pdf', $payslip->uuid) }}" class="btn btn-outline" style="margin-right: 0.5rem;"><i class="fa-solid fa-file-pdf"></i> Download PDF</a>
        <button onclick="window.print()" class="btn btn-primary"><i class="fa-solid fa-print"></i> Print Payslip</button>
    </div>
</div>

<div class="payslip-container">
    <div class="payslip-header">
        <div class="company-details">
            <h1 class="payslip-title">Payslip</h1>
            <p><strong>{{ $payslip->employee->company->company_name ?? 'BioMetrics AMS' }}</strong></p>
            <p>Pay Period: {{ Carbon\Carbon::parse($payslip->period->start_date)->format('M d, Y') }} - {{ Carbon\Carbon::parse($payslip->period->end_date)->format('M d, Y') }}</p>
        </div>
        <div class="employee-details" style="text-align: right;">
            <p style="font-size: 1.2rem; font-weight: 600; color: var(--dark); margin: 0 0 0.5rem 0;">{{ $payslip->employee->first_name }} {{ $payslip->employee->last_name }}</p>
            <p>Employee ID: {{ $payslip->employee->employee_id }}</p>
            <p>Department: {{ $payslip->employee->department->name ?? 'N/A' }}</p>
            <p>Designation: {{ $payslip->employee->designation->name ?? 'N/A' }}</p>
        </div>
    </div>
    
    <div class="payslip-body">
        <div class="payslip-section">
            <h4>Earnings</h4>
            <div class="amount-row">
                <span>Basic Salary</span>
                <span>{{ number_format($payslip->basic_salary, 2) }}</span>
            </div>
            
            @if($payslip->incentive > 0)
            <div class="amount-row">
                <span>Incentive</span>
                <span>{{ number_format($payslip->incentive, 2) }}</span>
            </div>
            @endif
            @if($payslip->ot_payment > 0)
            <div class="amount-row">
                <span>Overtime</span>
                <span>{{ number_format($payslip->ot_payment, 2) }}</span>
            </div>
            @endif
            @if(is_array($payslip->allowances_json))
                @foreach($payslip->allowances_json as $al)
                    @if(!in_array($al['name'], ['Performance', 'Incentive', 'OT']))
                    <div class="amount-row">
                        <span>{{ $al['name'] }}</span>
                        <span>{{ number_format($al['amount'], 2) }}</span>
                    </div>
                    @endif
                @endforeach
            @endif
            
            <div class="amount-row total">
                <span>Gross Earnings</span>
                <span>{{ number_format($payslip->gross_salary, 2) }}</span>
            </div>
        </div>
        
        <div class="payslip-section">
            <h4>Deductions</h4>
            
            @php
                $totalDeductionsCalculated = $payslip->no_pay_deduction + $payslip->epf_employee + $payslip->dedu_inc + ($payslip->dedu_inc_np ?? 0);
            @endphp

            @if($payslip->no_pay_deduction > 0)
            <div class="amount-row">
                <span>No Pay Amount</span>
                <span>{{ number_format($payslip->no_pay_deduction, 2) }}</span>
            </div>
            @endif
            
            @if($payslip->epf_employee > 0)
            <div class="amount-row">
                <span>EPF {{ $payslip->epf_employee_rate ?? 8 }}%</span>
                <span>{{ number_format($payslip->epf_employee, 2) }}</span>
            </div>
            @endif

            @if($payslip->dedu_inc > 0)
            <div class="amount-row">
                <span>Deduction for Incentive</span>
                <span>{{ number_format($payslip->dedu_inc, 2) }}</span>
            </div>
            @endif

            @if($payslip->dedu_inc_np > 0)
            <div class="amount-row">
                <span>Non-Performance Deduction</span>
                <span>{{ number_format($payslip->dedu_inc_np, 2) }}</span>
            </div>
            @endif

            @if(is_array($payslip->deductions_json))
                @foreach($payslip->deductions_json as $de)
                    <div class="amount-row">
                        <span>{{ $de['name'] }}</span>
                        <span>{{ number_format($de['amount'], 2) }}</span>
                    </div>
                    @php $totalDeductionsCalculated += $de['amount']; @endphp
                @endforeach
            @endif
            
            <div class="amount-row total">
                <span>Total Deductions</span>
                <span>{{ number_format($totalDeductionsCalculated, 2) }}</span>
            </div>
        </div>
    </div>
    
    <div class="net-pay-box">
        <h3>Net Pay</h3>
        <p class="amount">LKR {{ number_format($payslip->net_salary, 2) }}</p>
        <p style="margin: 0.5rem 0 0 0; color: var(--text-muted); font-size: 0.85rem;">For the period ending {{ Carbon\Carbon::parse($payslip->period->end_date)->format('F d, Y') }}</p>
    </div>
    
    <div style="margin-top: 3rem; border-top: 1px solid var(--border); padding-top: 1rem; color: var(--text-muted); font-size: 0.8rem; text-align: center;">
        This is a computer-generated document. No signature is required.
    </div>
</div>
@endsection
