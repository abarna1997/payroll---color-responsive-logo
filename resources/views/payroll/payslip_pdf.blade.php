<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Payslip - {{ $payslip->employee->employee_id }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 12mm 15mm 12mm 15mm;
        }

        body {
            font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
            font-size: 9pt;
            color: #000000;
            line-height: 1.3;
            margin: 0;
            padding: 0;
        }

        .header-bar-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .title-text {
            font-size: 22pt;
            font-weight: bold;
            color: #000000;
            margin: 0;
            padding: 0;
            text-transform: uppercase;
        }

        .emp-name {
            font-size: 11pt;
            font-weight: bold;
            color: #000000;
            margin-top: 6px;
        }

        .emp-addr {
            font-size: 8.5pt;
            color: #444444;
            line-height: 1.3;
            margin-top: 3px;
        }

        .month-label {
            font-size: 9pt;
            color: #777777;
            text-align: center;
        }

        .month-val {
            font-size: 16pt;
            font-weight: bold;
            color: #000000;
            text-align: center;
            margin-top: 4px;
        }

        .comp-name {
            font-size: 11pt;
            font-weight: bold;
            color: #000000;
            text-align: right;
        }

        .comp-addr {
            font-size: 8.5pt;
            color: #444444;
            text-align: right;
            line-height: 1.3;
            margin-top: 3px;
        }

        .comp-meta {
            font-size: 8.5pt;
            color: #333333;
            text-align: right;
            margin-top: 5px;
        }

        .divider {
            border-bottom: 1px solid #e5e7eb;
            margin: 15px 0;
        }

        .sec-title {
            font-size: 9.5pt;
            font-weight: bold;
            color: #000000;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 10px;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table td {
            padding: 3px 0;
            vertical-align: top;
            font-size: 9pt;
        }

        .label {
            color: #555555;
            width: 130px;
        }

        .val {
            color: #000000;
            font-weight: bold;
        }

        .payout-box {
            background-color: #d1e7dd;
            border: 1px solid #badbcc;
            border-radius: 4px;
            padding: 18px 10px;
            text-align: center;
        }

        .payout-amount {
            font-size: 22pt;
            font-weight: bold;
            color: #000000;
            line-height: 1;
        }

        .payout-lbl {
            font-size: 9pt;
            font-weight: bold;
            color: #0f5132;
            margin-top: 6px;
        }

        .salary-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #e5e7eb;
            margin-top: 15px;
        }

        .col-hdr {
            font-weight: bold;
            color: #333333;
            font-size: 8.5pt;
            text-transform: uppercase;
            background-color: #f3f4f6;
            padding: 8px;
            border-bottom: 1px solid #e5e7eb;
        }

        .col-sub-hdr {
            font-size: 7.5pt;
            font-weight: bold;
            color: #888888;
            text-transform: uppercase;
            border-bottom: 1px solid #e5e7eb;
            padding: 5px 8px;
            background-color: #ffffff;
        }

        .cell {
            padding: 6px 8px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 8.5pt;
            color: #000000;
        }

        .cell-right {
            text-align: right;
        }

        .total-row {
            background-color: #f8fafc;
            font-weight: bold;
            border-top: 2px solid #e2e8f0;
        }

        .note-text {
            font-size: 8pt;
            color: #000000;
            line-height: 1.3;
            margin-top: 10px;
        }

        .footer-text {
            text-align: center;
            font-size: 8.5pt;
            font-weight: bold;
            color: #000000;
            border-top: 1px solid #e5e7eb;
            padding-top: 10px;
            margin-top: 35px;
        }
    </style>
</head>
<body>

    @php
        $layout = $payslip->employee->company->payslip_layout ?? ['header', 'payout_summary', 'financials', 'attendance', 'signatures'];
    @endphp

    @foreach($layout as $block)
        @if($block === 'header')
    <!-- Header Visual Bar Matching Screenshot -->
    <table width="100%" cellpadding="0" cellspacing="0" border="0" class="header-bar-table">
        <tr>
            <td width="20" style="background-color: #ff3d00; height: 48px;">&nbsp;</td>
            <td width="5" style="background-color: #ffffff;">&nbsp;</td>
            <td width="220" style="vertical-align: middle; padding: 0 5px; background-color: #ffffff;">
                @if($payslip->employee->company->company_code === 'A1')
                    <img src="{{ public_path('images/altitude_one_logo.png') }}" style="height: 40px; width: auto; max-width: 200px; display: block;" alt="Altitude One">
                @elseif($payslip->employee->company->company_code === 'P1')
                    <img src="{{ public_path('images/prime_one_global_logo.png') }}" style="height: 40px; width: auto; max-width: 200px; display: block;" alt="Prime One Global">
                @elseif(!empty($payslip->employee->company->logo_path) && file_exists(public_path($payslip->employee->company->logo_path)))
                    <img src="{{ public_path($payslip->employee->company->logo_path) }}" style="height: 40px; width: auto; max-width: 200px; display: block;" alt="Logo">
                @else
                    <div style="color: #000000; font-size: 14pt; font-weight: bold; padding-left: 10px;">{{ strtoupper($payslip->employee->company->company_code) }} 1</div>
                @endif
            </td>
            <td width="5" style="background-color: #ffffff;">&nbsp;</td>
            <td style="background-color: #111827; height: 48px;">&nbsp;</td>
        </tr>
    </table>

    <!-- Main Header Grid -->
    <table width="100%" cellpadding="0" cellspacing="0" border="0">
        <tr>
            <!-- Left Column: PAYSLIP, Name, Address -->
            <td width="38%" style="vertical-align: top;">
                <div class="title-text">PAYSLIP</div>
                <div class="emp-name">{{ $payslip->employee->full_name }}</div>
                <div class="emp-addr">
                    {{ $payslip->employee->current_address ?? $payslip->employee->permanent_address ?? '16 ,4/2 , 55th Lane, Colombo -06' }}
                </div>
            </td>

            <!-- Center Column: Month -->
            <td width="28%" align="center" style="vertical-align: top; padding-top: 10px;">
                <div class="month-label">Payslip For the Month</div>
                <div class="month-val">{{ $payslip->period->start_date->format('F Y') }}</div>
            </td>

            <!-- Right Column: Company Info -->
            <td width="34%" style="vertical-align: top;">
                <div class="comp-name">{{ $payslip->employee->company->company_name }}</div>
                <div class="comp-addr">
                    {{ $payslip->employee->company->address ?? '#146B, Goodshed Road, Thonikkal, Vavuniya, NP, 43000, Sri Lanka' }}
                </div>
                <table width="100%" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td style="vertical-align: top;">
                            <div class="comp-meta">
                                <b>Company BR -</b> {{ $payslip->employee->company->registration_number ?? ($payslip->employee->company->company_code === 'P1' ? 'PV00241009' : 'PV00292308') }}<br>
                                <b>EPF No -</b> {{ $payslip->employee->company->epf_registration_number ?? '2565/L' }}
                            </div>
                        </td>
                        @if($payslip->verification_hash)
                        <td width="70" style="vertical-align: top; text-align: right;">
                            @php
                                $qrUrl = route('verify.payslip', $payslip->verification_hash);
                                $options = new \chillerlan\QRCode\QROptions([
                                    'version' => 5,
                                    'outputInterface' => \chillerlan\QRCode\Output\QRMarkupSVG::class,
                                    'eccLevel' => \chillerlan\QRCode\Common\EccLevel::L,
                                ]);
                                $qrcode = (new \chillerlan\QRCode\QRCode($options))->render($qrUrl);
                            @endphp
                            <img src="{{ $qrcode }}" style="width: 60px; height: 60px;" alt="QR Code">
                        </td>
                        @endif
                    </tr>
                </table>
            </td>
        </tr>
    </table>

        @elseif($block === 'payout_summary')
    <div class="divider"></div>

    <!-- Employee Summary & Net Pay Section -->
    <div class="sec-title">EMPLOYEE SUMMARY</div>
    <table width="100%" cellpadding="0" cellspacing="0" border="0">
        <tr>
            <td width="60%" style="vertical-align: top;">
                <table class="info-table">
                    <tr><td class="label">Employee ID</td><td width="5%">:</td><td class="val">{{ $payslip->employee->employee_id }}</td></tr>
                    <tr><td class="label">NIC / Passport</td><td>:</td><td class="val">{{ $payslip->employee->nic ?? '976720687V' }}</td></tr>
                    <tr><td class="label">Designation</td><td>:</td><td class="val">{{ $payslip->employee->designation ?? 'Junior Web Developer' }}</td></tr>
                    @if($payslip->employee->branch)
                    <tr><td class="label">Branch / Location</td><td>:</td><td class="val">{{ $payslip->employee->branch->branch_name }}</td></tr>
                    @endif
                    <tr><td class="label">Pay Mode</td><td>:</td><td class="val">{{ $payslip->payment_method ?? 'Bank Transfer' }}</td></tr>
                    <tr><td class="label">Pay Period</td><td>:</td><td class="val">{{ $payslip->period->start_date->format('M d') }} to {{ $payslip->period->end_date->format('M d') }}</td></tr>
                    <tr><td class="label">Pay Date</td><td>:</td><td class="val">{{ $payslip->period->end_date->format('d/m/Y') }}</td></tr>
                </table>
            </td>
            <td width="5%">&nbsp;</td>
            <td width="35%" style="vertical-align: top;">
                <div class="payout-box">
                    <div class="payout-amount">{{ number_format($payslip->net_salary, 2) }}</div>
                    <div class="payout-lbl">Employee Home Take Away</div>
                </div>
            </td>
        </tr>
    </table>

        @elseif($block === 'financials')
    <!-- Earnings & Deductions Table -->
    @php
        $earnings = [];
        $earnings[] = ['label' => 'Basic Salary', 'amount' => $payslip->basic_salary];
        if ($payslip->incentive > 0) $earnings[] = ['label' => 'Incentive', 'amount' => $payslip->incentive];
        if ($payslip->ot_payment > 0) $earnings[] = ['label' => 'Overtime', 'amount' => $payslip->ot_payment];
        if (is_array($payslip->allowances_json)) {
            foreach($payslip->allowances_json as $al) {
                $earnings[] = ['label' => $al['name'], 'amount' => $al['amount']];
            }
        }
        if ($payslip->no_pay_deduction > 0) {
            $earnings[] = ['label' => 'Less: No-Pay', 'amount' => -$payslip->no_pay_deduction];
        }

        $deductions = [];
        if ($payslip->epf_employee > 0) $deductions[] = ['label' => 'EPF ' . ($payslip->epf_employee_rate ?? 8) . '%', 'amount' => $payslip->epf_employee];
        if ($payslip->dedu_inc > 0) $deductions[] = ['label' => 'Deduction for Incentive', 'amount' => $payslip->dedu_inc];
        if ($payslip->dedu_inc_np > 0) $deductions[] = ['label' => 'Non-Performance Deduction', 'amount' => $payslip->dedu_inc_np];
        
        $totalDeductionsCalculated = $payslip->epf_employee + $payslip->dedu_inc + ($payslip->dedu_inc_np ?? 0);
        
        if (is_array($payslip->deductions_json)) {
            foreach($payslip->deductions_json as $de) {
                $deductions[] = ['label' => $de['name'], 'amount' => $de['amount']];
                $totalDeductionsCalculated += $de['amount'];
            }
        }

        $rowCount = max(count($earnings), count($deductions));
        if ($rowCount == 0) $rowCount = 1;
    @endphp

    <table class="salary-table" cellpadding="0" cellspacing="0">
        <tr>
            <td width="50%" colspan="2" class="col-hdr">EARNINGS</td>
            <td width="50%" colspan="2" class="col-hdr" style="border-left: 1px solid #e5e7eb;">DEDUCTIONS</td>
        </tr>
        <tr>
            <td width="35%" class="col-sub-hdr">ITEM</td>
            <td width="15%" class="col-sub-hdr cell-right">AMOUNT</td>
            <td width="35%" class="col-sub-hdr" style="border-left: 1px solid #e5e7eb;">ITEM</td>
            <td width="15%" class="col-sub-hdr cell-right">AMOUNT</td>
        </tr>
        @for($i = 0; $i < $rowCount; $i++)
            <tr>
                <td class="cell">{!! $i < count($earnings) ? htmlspecialchars($earnings[$i]['label']) : '&nbsp;' !!}</td>
                <td class="cell cell-right">{!! $i < count($earnings) ? number_format($earnings[$i]['amount'], 2) : '&nbsp;' !!}</td>
                <td class="cell" style="border-left: 1px solid #f1f5f9;">{!! $i < count($deductions) ? htmlspecialchars($deductions[$i]['label']) : '&nbsp;' !!}</td>
                <td class="cell cell-right">{!! $i < count($deductions) ? number_format($deductions[$i]['amount'], 2) : '&nbsp;' !!}</td>
            </tr>
        @endfor
        <tr class="total-row">
            <td class="cell">Total Earnings In {{ $payslip->employee->company->currency ?? 'LKR' }}</td>
            <td class="cell cell-right">{{ number_format($payslip->gross_salary, 2) }}</td>
            <td class="cell" style="border-left: 1px solid #e5e7eb;">Total Deductions In {{ $payslip->employee->company->currency ?? 'LKR' }}</td>
            <td class="cell cell-right">{{ number_format($totalDeductionsCalculated, 2) }}</td>
        </tr>
    </table>

    <br>

    <!-- Employer Contributions Section -->
    <div class="sec-title">EMPLOYER CONTRIBUTIONS</div>
    <table width="50%" cellpadding="0" cellspacing="0" class="salary-table" style="margin-top: 0;">
        <tr>
            <td class="cell">Employer EPF (12%)</td>
            <td class="cell cell-right">{{ number_format($payslip->epf_employer, 2) }}</td>
        </tr>
        <tr>
            <td class="cell">Employer ETF (3%)</td>
            <td class="cell cell-right">{{ number_format($payslip->etf_employer, 2) }}</td>
        </tr>
        <tr class="total-row">
            <td class="cell">Total Contribution in {{ $payslip->employee->company->currency ?? 'LKR' }}</td>
            <td class="cell cell-right">{{ number_format($payslip->epf_employer + $payslip->etf_employer, 2) }}</td>
        </tr>
    </table>

    <div class="note-text">
        Note: Employer EPF (12%) and ETF (3%) are not deductions from salary but should be shown separately for compliance
    </div>

        @elseif($block === 'signatures')
    <!-- Footer & Signatures -->
    @php
        $sigMode = $payslip->employee->company->signature_mode ?? 'No Signature';
        $showSeal = $payslip->employee->company->show_company_seal;
    @endphp

    @if($sigMode === 'No Signature')
        <div class="footer-text">
            This document has been automatically generated and does not require a physical signature.
        </div>
    @else
        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top: 40px;">
            <tr>
                @php
                    $signatures = [];
                    if ($payslip->employee->company->hr_signature_path) $signatures[] = ['title' => 'HR Manager', 'path' => $payslip->employee->company->hr_signature_path];
                    if ($payslip->employee->company->finance_signature_path) $signatures[] = ['title' => 'Finance Manager', 'path' => $payslip->employee->company->finance_signature_path];
                    if ($payslip->employee->company->director_signature_path) $signatures[] = ['title' => 'Managing Director', 'path' => $payslip->employee->company->director_signature_path];
                    if ($payslip->employee->company->ceo_signature_path) $signatures[] = ['title' => 'CEO', 'path' => $payslip->employee->company->ceo_signature_path];
                    if ($payslip->employee->company->authorized_signature_path) $signatures[] = ['title' => 'Authorized Officer', 'path' => $payslip->employee->company->authorized_signature_path];
                    
                    // For Manual Mode, just put placeholders if no images exist.
                    if (empty($signatures) && str_contains($sigMode, 'Manual')) {
                        $signatures = [
                            ['title' => 'Prepared By', 'path' => null],
                            ['title' => 'Checked By', 'path' => null],
                            ['title' => 'Approved By', 'path' => null]
                        ];
                    }
                @endphp

                @php 
                    $sealWidth = ($showSeal && $payslip->employee->company->company_stamp_path && file_exists(storage_path('app/public/' . $payslip->employee->company->company_stamp_path))) ? 20 : 0;
                    $sigsToRender = array_slice($signatures, 0, 4);
                    $sigWidth = (100 - $sealWidth) / max(1, count($sigsToRender));
                @endphp
                @foreach($sigsToRender as $sig)
                    <td width="{{ $sigWidth }}%" align="center" style="vertical-align: bottom; padding: 10px;">
                        @if(str_contains($sigMode, 'Digital') && $sig['path'] && file_exists(storage_path('app/public/' . $sig['path'])))
                            <img src="{{ storage_path('app/public/' . $sig['path']) }}" style="max-height: 40px; display: block; margin: 0 auto 5px auto;" alt="Signature">
                        @else
                            <div style="height: 40px;"></div>
                        @endif
                        <div style="border-top: 1px dashed #000; padding-top: 5px; font-size: 8pt; font-weight: bold;">{{ $sig['title'] }}</div>
                    </td>
                @endforeach

                @if($sealWidth > 0)
                    <td width="20%" align="center" style="vertical-align: bottom;">
                        <img src="{{ storage_path('app/public/' . $payslip->employee->company->company_stamp_path) }}" style="max-height: 60px; opacity: 0.8;" alt="Company Seal">
                    </td>
                @endif
            </tr>
        </table>
    @endif
        @endif
    @endforeach

</body>
</html>
