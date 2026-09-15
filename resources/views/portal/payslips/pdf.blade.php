<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payslip - {{ $payslip->period->period_name }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .payslip-container {
            width: 100%;
        }
        .payslip-header {
            border-bottom: 2px solid #4F46E5;
            padding-bottom: 20px;
            margin-bottom: 30px;
            width: 100%;
        }
        .company-details {
            float: left;
            width: 50%;
        }
        .employee-details {
            float: right;
            width: 50%;
            text-align: right;
        }
        .payslip-title {
            font-size: 28px;
            color: #4F46E5;
            margin: 0 0 10px 0;
        }
        .company-details p, .employee-details p {
            margin: 4px 0;
            color: #555;
            font-size: 14px;
        }
        .clear {
            clear: both;
        }
        .payslip-body {
            width: 100%;
            margin-top: 20px;
        }
        .section-earnings {
            float: left;
            width: 48%;
        }
        .section-deductions {
            float: right;
            width: 48%;
        }
        h4 {
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
            margin-bottom: 15px;
            color: #333;
        }
        .amount-row {
            margin-bottom: 8px;
            font-size: 14px;
        }
        .amount-row span:first-child {
            float: left;
        }
        .amount-row span:last-child {
            float: right;
        }
        .amount-row.total {
            font-weight: bold;
            border-top: 1px dashed #ddd;
            padding-top: 10px;
            margin-top: 15px;
            font-size: 16px;
        }
        .net-pay-box {
            background-color: #F8FAFC;
            border: 1px solid #E2E8F0;
            padding: 20px;
            text-align: center;
            margin-top: 40px;
            border-radius: 8px;
        }
        .net-pay-box h3 {
            margin: 0 0 10px 0;
            color: #64748B;
            font-size: 16px;
            text-transform: uppercase;
        }
        .net-pay-box .amount {
            font-size: 32px;
            font-weight: bold;
            color: #4F46E5;
            margin: 0;
        }
        .footer {
            margin-top: 50px;
            border-top: 1px solid #ddd;
            padding-top: 15px;
            color: #888;
            font-size: 12px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="payslip-container">
        <div class="payslip-header">
            <div class="company-details">
                <h1 class="payslip-title">Payslip</h1>
                <p><strong>{{ $payslip->employee->company->company_name ?? 'BioMetrics AMS' }}</strong></p>
                <p>Pay Period: {{ Carbon\Carbon::parse($payslip->period->start_date)->format('M d, Y') }} - {{ Carbon\Carbon::parse($payslip->period->end_date)->format('M d, Y') }}</p>
            </div>
            <div class="employee-details">
                <p style="font-size: 20px; font-weight: bold; color: #111;">{{ $payslip->employee->first_name }} {{ $payslip->employee->last_name }}</p>
                <p>Employee ID: {{ $payslip->employee->employee_id }}</p>
                <p>Department: {{ $payslip->employee->department->name ?? 'N/A' }}</p>
                <p>Designation: {{ $payslip->employee->designation->name ?? 'N/A' }}</p>
            </div>
            <div class="clear"></div>
        </div>
        
        <div class="payslip-body">
            <div class="section-earnings">
                <h4>Earnings</h4>
                <div class="amount-row">
                    <span>Basic Salary</span>
                    <span>{{ number_format($payslip->basic_salary, 2) }}</span>
                    <div class="clear"></div>
                </div>
                
                @php
                    $allowances = json_decode($payslip->allowances_json, true) ?? [];
                    $totalAllowances = 0;
                @endphp
                
                @foreach($allowances as $name => $amount)
                    <div class="amount-row">
                        <span>{{ $name }}</span>
                        <span>{{ number_format($amount, 2) }}</span>
                        <div class="clear"></div>
                    </div>
                    @php $totalAllowances += $amount; @endphp
                @endforeach
                
                <div class="amount-row total">
                    <span>Gross Earnings</span>
                    <span>{{ number_format($payslip->gross_salary, 2) }}</span>
                    <div class="clear"></div>
                </div>
            </div>
            
            <div class="section-deductions">
                <h4>Deductions</h4>
                @php
                    $deductions = json_decode($payslip->deductions_json, true) ?? [];
                    $totalDeductions = 0;
                @endphp
                
                @foreach($deductions as $name => $amount)
                    <div class="amount-row">
                        <span>{{ $name }}</span>
                        <span>{{ number_format($amount, 2) }}</span>
                        <div class="clear"></div>
                    </div>
                    @php $totalDeductions += $amount; @endphp
                @endforeach
                
                <div class="amount-row total">
                    <span>Total Deductions</span>
                    <span>{{ number_format($totalDeductions, 2) }}</span>
                    <div class="clear"></div>
                </div>
            </div>
            <div class="clear"></div>
        </div>
        
        <div class="net-pay-box">
            <h3>Net Pay</h3>
            <p class="amount">LKR {{ number_format($payslip->net_pay, 2) }}</p>
            <p style="margin: 10px 0 0 0; color: #888; font-size: 14px;">For the period ending {{ Carbon\Carbon::parse($payslip->period->end_date)->format('F d, Y') }}</p>
        </div>
        
        <div class="footer">
            This is a computer-generated document. No signature is required.
        </div>
    </div>
</body>
</html>
