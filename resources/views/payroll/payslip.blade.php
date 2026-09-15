<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payslip - {{ $payslip->employee->employee_id }} - {{ $payslip->period->period_name }}</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --brand-color: {{ $payslip->employee->company->color_theme ?? '#f95716' }};
            --brand-secondary: {{ $payslip->employee->company->secondary_color ?? '#1b2a35' }};
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --border-color: #e2e8f0;
        }

        body {
            background-color: #f8fafc;
            color: var(--text-primary);
            font-family: '{{ $payslip->employee->company->font_family ?? 'Inter' }}', sans-serif;
            padding: 30px 15px;
        }

        .payslip-card {
            background-color: #ffffff;
            border-radius: 12px;
            padding: 35px;
            max-width: 850px;
            margin: 0 auto;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
            border: 1px solid var(--border-color);
            position: relative;
        }

        @if($payslip->employee->company->show_watermark && $payslip->employee->company->watermark_path)
        .payslip-card::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 350px;
            height: 350px;
            background-image: url('{{ asset('storage/' . $payslip->employee->company->watermark_path) }}');
            background-repeat: no-repeat;
            background-position: center;
            background-size: contain;
            opacity: 0.06;
            pointer-events: none;
            z-index: 0;
        }
        @endif

        .payslip-banner-top {
            background-color: var(--brand-secondary, #1e293b);
            border-radius: 8px 8px 0 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin: -35px -35px 25px -35px;
            overflow: hidden;
            border-bottom: 3px solid var(--brand-color);
        }

        .logo-box {
            background-color: var(--brand-color);
            padding: 16px 28px;
            color: #ffffff;
            font-family: 'Outfit', sans-serif;
            font-weight: 800;
            font-size: 1.6rem;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            display: flex;
            align-items: center;
        }

        .meta-label {
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--text-secondary);
            width: 140px;
        }

        .meta-val {
            font-size: 0.88rem;
            font-weight: 500;
            color: var(--text-primary);
        }

        .payout-callout-box {
            background-color: #e8f5e9;
            border: 1px solid #c8e6c9;
            border-radius: 8px;
            padding: 22px;
            text-align: center;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            box-shadow: 0 2px 8px rgba(46, 125, 50, 0.04);
        }

        .payout-amount {
            font-family: 'Outfit', sans-serif;
            font-weight: 800;
            color: #2e7d32;
            font-size: 2rem;
        }

        .payout-label {
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            color: #388e3c;
            letter-spacing: 0.5px;
            margin-top: 4px;
        }

        .side-table th {
            background-color: #f1f5f9;
            color: var(--text-secondary);
            font-weight: 700;
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 10px 12px;
            border: 1px solid var(--border-color);
        }

        .side-table td {
            padding: 10px 12px;
            font-size: 0.85rem;
            border: 1px solid var(--border-color);
            color: var(--text-primary);
        }

        .side-table .subtotal-row td {
            font-weight: 700;
            background-color: #f8fafc;
            border-top: 2px solid var(--text-primary);
        }

        .contributions-card {
            background-color: #f8fafc;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 18px 24px;
        }

        .contributions-table th {
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--text-secondary);
            border: none;
            padding: 6px 0;
        }

        .contributions-table td {
            font-size: 0.88rem;
            font-weight: 600;
            border: none;
            padding: 6px 0;
        }

        .contributions-table .total-line td {
            border-top: 1px solid var(--border-color);
            padding-top: 10px;
            font-weight: 700;
            color: var(--text-primary);
        }

        .disclaimer-text {
            font-size: 0.75rem;
            color: var(--text-secondary);
            line-height: 1.4;
        }

        .signature-placeholder {
            border-bottom: 1px solid var(--text-secondary);
            width: 160px;
            height: 45px;
            margin-bottom: 6px;
        }

        .verification-badge {
            background-color: #f8fafc;
            border: 1px dashed var(--border-color);
            border-radius: 8px;
            padding: 15px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        /* Print Settings */
        @media print {
            body {
                background-color: #ffffff;
                padding: 0;
            }
            .payslip-card {
                box-shadow: none;
                border: none;
                padding: 0;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>

    <!-- Print Control Bar -->
    <div class="container text-center mb-4 no-print" style="max-width: 850px;">
        <div class="d-flex justify-content-between align-items-center bg-white p-3 rounded-3 shadow-sm border border-light">
            <span class="fw-semibold text-secondary"><i class="bi bi-file-earmark-pdf me-1"></i> AMS Enterprise Payslip View</span>
            <div>
                <button type="button" class="btn btn-custom-secondary btn-sm me-2 px-3" onclick="window.close();">
                    <i class="bi bi-x-circle me-1"></i> Close
                </button>
                <button type="button" class="btn btn-primary btn-sm px-4 fw-bold" onclick="window.print();" style="background-color: var(--brand-color); border-color: var(--brand-color);">
                    <i class="bi bi-printer me-1"></i> Print Payslip
                </button>
            </div>
        </div>
    </div>

    <!-- Main Slip Card Container -->
    <div class="payslip-card">
        <!-- Banner Header -->
        @if($payslip->employee->company->show_header_banner && $payslip->employee->company->banner_path)
            <div class="payslip-banner-top mb-4" style="display: block; background-color: var(--brand-secondary, #1b2a35); height: 95px; padding: 10px 0; box-sizing: border-box;">
                <img src="{{ asset('storage/' . $payslip->employee->company->banner_path) }}" style="height: 100%; width: auto; max-width: none; display: block; object-fit: contain;">
            </div>
        @else
            <div class="payslip-banner-top">
                <div class="logo-box">
                    @if($payslip->employee->company->logo_path)
                        <img src="{{ asset('storage/' . $payslip->employee->company->logo_path) }}" style="max-height: 35px; object-fit: contain; margin-right: 12px; filter: brightness(0) invert(1);">
                    @endif
                    {{ strtoupper($payslip->employee->company->company_code) }} 1
                </div>
                <div class="pe-4 text-white text-end fs-7 opacity-75">
                    <i class="bi bi-shield-check text-success me-1"></i> Secured Digital Verification
                </div>
            </div>
        @endif

        <!-- Header Grid -->
        <div class="row g-4 mb-4 align-items-start">
            <div class="col-md-5">
                <h1 style="font-family: 'Outfit', sans-serif; font-weight: 800; font-size: 2.2rem; margin: 0; color: var(--text-primary); letter-spacing: -0.5px;">PAYSLIP</h1>
                <div class="fw-bold mt-2 text-dark" style="font-size: 1.05rem;">{{ $payslip->employee->full_name }}</div>
                <div class="text-secondary fs-8 mt-1" style="line-height: 1.4; max-width: 280px;">
                    No - 356, Mannar Road, Veppankulam,<br>
                    Vavuniya, NP, 43000, Sri Lanka.
                </div>
            </div>
            
            <div class="col-md-3 text-start text-md-center">
                <span class="text-secondary fs-8 uppercase fw-semibold tracking-wider">Payslip For the Month</span>
                <h4 class="fw-bold mt-1 text-dark" style="font-family: 'Outfit'; font-size: 1.45rem;">
                    {{ $payslip->period->start_date->format('F Y') }}
                </h4>
            </div>

            <div class="col-md-4 text-start text-md-end">
                <h6 class="fw-bold m-0 text-dark">{{ $payslip->employee->company->company_name }}</h6>
                <div class="text-secondary fs-8 mt-1" style="line-height: 1.4;">
                    {{ $payslip->employee->company->address ?? 'Colombo, Sri Lanka' }}
                </div>
                <div class="text-secondary fs-8 mt-2">
                    <strong>Company BR:</strong> {{ $payslip->employee->company->registration_number }}<br>
                    <strong>EPF No:</strong> {{ $payslip->employee->company->epf_registration_number ?? 'L/2447' }}
                </div>
            </div>
        </div>

        <hr style="border-top: 1px solid var(--border-color); margin: 25px 0;">

        <!-- Employee Summary & Payout Block -->
        <div class="row g-4 mb-4">
            <div class="col-md-7">
                <h6 class="display-font fw-bold mb-3 uppercase tracking-wider text-secondary" style="font-size: 0.72rem;">Employee Summary</h6>
                <table class="w-100" style="border-collapse: collapse;">
                    <tr style="height: 28px;">
                        <td class="meta-label">Employee ID</td>
                        <td class="meta-val">: <span class="fw-bold text-dark">{{ $payslip->employee->employee_id }}</span></td>
                    </tr>
                    <tr style="height: 28px;">
                        <td class="meta-label">NIC / Passport</td>
                        <td class="meta-val">: {{ $payslip->employee->nic ?? '200217400379' }}</td>
                    </tr>
                    <tr style="height: 28px;">
                        <td class="meta-label">Designation</td>
                        <td class="meta-val">: {{ $payslip->employee->designation ?? 'IT Professional' }}</td>
                    </tr>
                    <tr style="height: 28px;">
                        <td class="meta-label">Pay Mode</td>
                        <td class="meta-val">: Bank Transfer</td>
                    </tr>
                    <tr style="height: 28px;">
                        <td class="meta-label">Pay Period</td>
                        <td class="meta-val">: {{ $payslip->period->start_date->format('M d') }} to {{ $payslip->period->end_date->format('M d') }}</td>
                    </tr>
                    <tr style="height: 28px;">
                        <td class="meta-label">Pay Date</td>
                        <td class="meta-val">: {{ $payslip->period->end_date->format('d/m/Y') }}</td>
                    </tr>
                </table>
            </div>

            <div class="col-md-5">
                <div class="payout-callout-box">
                    <div class="payout-amount">
                        {{ $payslip->employee->company->currency ?? 'LKR' }} {{ number_format($payslip->net_salary, 2) }}
                    </div>
                    <div class="payout-label">Employee Home Take Away</div>
                </div>
            </div>
        </div>

        <!-- Earnings & Deductions Tables -->
        <div class="row g-4 mb-4">
            <!-- Earnings -->
            <div class="col-md-6">
                <table class="table side-table m-0">
                    <thead>
                        <tr>
                            <th>Earnings</th>
                            <th class="text-end" style="width: 120px;">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Basic Salary</td>
                            <td class="text-end">{{ number_format($payslip->basic_salary, 2) }}</td>
                        </tr>
                        @if($payslip->incentive > 0)
                        <tr>
                            <td>Incentive / Performance</td>
                            <td class="text-end">{{ number_format($payslip->incentive, 2) }}</td>
                        </tr>
                        @endif
                        @if($payslip->ot_payment > 0)
                        <tr>
                            <td>Overtime</td>
                            <td class="text-end">{{ number_format($payslip->ot_payment, 2) }}</td>
                        </tr>
                        @endif
                        @if(is_array($payslip->allowances_json))
                            @foreach($payslip->allowances_json as $allowance)
                                <tr>
                                    <td>{{ $allowance['name'] }}</td>
                                    <td class="text-end">{{ number_format($allowance['amount'], 2) }}</td>
                                </tr>
                            @endforeach
                        @endif
                        @if($payslip->no_pay_deduction > 0)
                        <tr>
                            <td class="text-danger fw-semibold">Less: No-Pay</td>
                            <td class="text-end text-danger fw-semibold">-{{ number_format($payslip->no_pay_deduction, 2) }}</td>
                        </tr>
                        @endif
                        <tr class="subtotal-row">
                            <td class="fw-bold">Gross Earnings In {{ $payslip->employee->company->currency ?? 'LKR' }}</td>
                            <td class="text-end fw-bold">{{ number_format($payslip->gross_salary, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Deductions -->
            <div class="col-md-6">
                <table class="table side-table m-0">
                    <thead>
                        <tr>
                            <th>Deductions</th>
                            <th class="text-end" style="width: 120px;">Amount</th>
                        </tr>
                    </thead>
                    <tbody>

                        @if($payslip->epf_employee > 0)
                        <tr>
                            <td>EPF {{ $payslip->epf_employee_rate ?? 8 }}%</td>
                            <td class="text-end">{{ number_format($payslip->epf_employee, 2) }}</td>
                        </tr>
                        @endif
                        @if($payslip->dedu_inc > 0)
                        <tr>
                            <td>Deduction for Incentive</td>
                            <td class="text-end">{{ number_format($payslip->dedu_inc, 2) }}</td>
                        </tr>
                        @endif
                        @if($payslip->dedu_inc_np > 0)
                        <tr>
                            <td>Non-Performance Deduction</td>
                            <td class="text-end">{{ number_format($payslip->dedu_inc_np, 2) }}</td>
                        </tr>
                        @endif
                        
                        @php
                            $totalDeductionsCalculated = $payslip->epf_employee + $payslip->dedu_inc + ($payslip->dedu_inc_np ?? 0);
                        @endphp

                        @if(is_array($payslip->deductions_json))
                            @foreach($payslip->deductions_json as $deduction)
                                @php
                                    $totalDeductionsCalculated += $deduction['amount'];
                                @endphp
                                <tr>
                                    <td>{{ $deduction['name'] }}</td>
                                    <td class="text-end">{{ number_format($deduction['amount'], 2) }}</td>
                                </tr>
                            @endforeach
                        @endif
                        
                        <tr class="subtotal-row">
                            <td class="fw-bold">Total Deductions In {{ $payslip->employee->company->currency ?? 'LKR' }}</td>
                            <td class="text-end fw-bold">{{ number_format($totalDeductionsCalculated, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Employer Contributions -->
        <div class="row g-4 mb-4 align-items-center">
            <div class="col-md-6">
                <div class="contributions-card">
                    <h6 class="display-font fw-bold mb-3 uppercase text-secondary" style="font-size: 0.72rem; letter-spacing: 0.5px;">Employer Contributions</h6>
                    <table class="w-100 contributions-table">
                        <tr>
                            <th>Employer EPF (12%)</th>
                            <td class="text-end">{{ number_format($payslip->epf_employer, 2) }}</td>
                        </tr>
                        <tr>
                            <th>Employer ETF (3%)</th>
                            <td class="text-end">{{ number_format($payslip->etf_employer, 2) }}</td>
                        </tr>
                        <tr class="total-line">
                            <td>Total Contribution in {{ $payslip->employee->company->currency ?? 'LKR' }}</td>
                            <td class="text-end">{{ number_format($payslip->epf_employer + $payslip->etf_employer, 2) }}</td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="disclaimer-text border-start ps-3 py-1">
                    <strong>Note:</strong> Employer EPF (12%) and ETF (3%) are not deductions from salary but should be shown separately for compliance purposes under the Sri Lankan EPF Act.
                </div>
            </div>
        </div>

        <!-- Validation & Signature block -->
        <div class="row g-4 align-items-end mt-4">
            <div class="col-md-6">
                <div class="verification-badge">
                    <!-- Secure Dynamic QR Code -->
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=72x72&data={{ urlencode(route('payroll.payslip', $payslip->id)) }}" style="width: 72px; height: 72px; border-radius: 4px;" alt="QR Code">
                    <div>
                        <div class="fw-bold text-dark" style="font-size: 0.82rem;"><i class="bi bi-shield-lock-fill text-success me-1"></i> Digital Verification Secured</div>
                        <div class="text-secondary fs-8 mt-1">Scan QR code or use the hash key below to authenticate:</div>
                        <code class="text-secondary fs-8 text-break mt-1 d-block" style="font-size: 0.68rem; max-width: 320px;">{{ $payslip->verification_hash }}</code>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="d-flex flex-wrap justify-content-start justify-content-md-end gap-3 text-center">
                    @php
                        $sigMode = $payslip->employee->company->signature_mode ?? 'Digital Signature';
                    @endphp

                    @if(in_array($sigMode, ['Digital Signature', 'Digital + Manual Signature']))
                        @if($payslip->employee->company->hr_signature_path)
                            <div class="d-inline-block text-center px-2">
                                <img src="{{ asset('storage/' . $payslip->employee->company->hr_signature_path) }}" style="max-height: 45px; object-fit: contain;">
                                <div class="text-secondary uppercase fw-semibold mt-1" style="font-size: 0.65rem; letter-spacing: 0.5px;">HR Signature</div>
                            </div>
                        @endif

                        @if($payslip->employee->company->show_company_seal && $payslip->employee->company->digital_seal_path)
                            <div class="d-inline-block text-center px-2">
                                <img src="{{ asset('storage/' . $payslip->employee->company->digital_seal_path) }}" style="max-height: 50px; object-fit: contain;">
                                <div class="text-secondary uppercase fw-semibold mt-1" style="font-size: 0.65rem; letter-spacing: 0.5px;">Company Seal</div>
                            </div>
                        @endif
                    @endif

                    @if(in_array($sigMode, ['Manual Signature', 'Digital + Manual Signature']))
                        <div class="d-inline-block border-bottom text-center" style="width: 100px; height: 45px; border-color: var(--text-secondary) !important;">
                            <div class="text-secondary uppercase fw-bold" style="font-size: 0.65rem; margin-top: 50px;">Prepared By</div>
                        </div>
                        <div class="d-inline-block border-bottom text-center" style="width: 100px; height: 45px; border-color: var(--text-secondary) !important;">
                            <div class="text-secondary uppercase fw-bold" style="font-size: 0.65rem; margin-top: 50px;">Approved By</div>
                        </div>
                    @else
                        @if(!in_array($sigMode, ['Digital Signature', 'Digital + Manual Signature']))
                            <div class="d-inline-block text-center">
                                <div class="signature-placeholder"></div>
                                <div class="text-secondary fs-8 uppercase fw-bold" style="letter-spacing: 0.5px;">Authorized Officer</div>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>

        <hr style="border-top: 1px dashed var(--border-color); margin: 30px 0 20px 0;">

        @if($payslip->employee->company->show_footer_banner && $payslip->employee->company->footer_banner_path)
            <div class="payslip-banner-footer mb-3 text-center">
                <img src="{{ asset('storage/' . $payslip->employee->company->footer_banner_path) }}" style="width: 100%; height: 50px; object-fit: cover; border-radius: 4px;">
            </div>
        @endif

        <!-- Footer -->
        <div class="text-center text-secondary fs-8" style="opacity: 0.85;">
            This document has been automatically generated and does not require a physical signature. Confidential.
        </div>
    </div>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
