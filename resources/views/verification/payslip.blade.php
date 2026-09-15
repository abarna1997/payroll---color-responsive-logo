@extends('layouts.guest')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-success text-white text-center py-3">
                    <h4 class="mb-0"><i class="bi bi-shield-check me-2"></i>Verified Document</h4>
                </div>
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <p class="text-muted">This Payslip is authentic and has been verified by the system.</p>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-4 text-end text-muted fw-bold">Employee:</div>
                        <div class="col-8 fw-semibold">{{ $payslip->employee->first_name }} {{ $payslip->employee->last_name }} ({{ $payslip->employee->employee_id }})</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-4 text-end text-muted fw-bold">Company:</div>
                        <div class="col-8 fw-semibold">{{ $payslip->employee->company->company_name ?? 'N/A' }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-4 text-end text-muted fw-bold">Pay Period:</div>
                        <div class="col-8 fw-semibold">{{ $payslip->payrollPeriod->month_name }} {{ $payslip->payrollPeriod->year }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-4 text-end text-muted fw-bold">Net Salary:</div>
                        <div class="col-8 fw-bold text-primary">{{ number_format($payslip->net_salary, 2) }}</div>
                    </div>
                    <div class="row">
                        <div class="col-4 text-end text-muted fw-bold">Generated On:</div>
                        <div class="col-8 fw-semibold">{{ $payslip->created_at->format('M d, Y H:i:s') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
