@extends('layouts.app')

@section('content')
<div class="row g-4">
    <!-- Period Details Header -->
    <div class="col-12">
        <div class="glass-card d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <a href="{{ route('payroll.index') }}" class="btn btn-sm btn-outline-secondary border-0 px-2 py-1" title="Back to Payroll">
                        <i class="bi bi-arrow-left me-1"></i> Back
                    </a>
                    <h4 class="display-font m-0 fw-bold">{{ $period->period_name }}</h4>
                </div>
                <div class="text-secondary fs-7 mt-1">
                    <i class="bi bi-calendar-range me-1"></i> {{ $period->start_date->format('M d, Y') }} - {{ $period->end_date->format('M d, Y') }}
                    <span class="mx-2">|</span>
                    <i class="bi bi-arrow-repeat me-1"></i> {{ $period->cycle_type }} Cycle
                </div>
            </div>
            
            <div class="d-flex align-items-center gap-2 flex-wrap">
                @if($period->status === 'Draft' || $period->status === 'Revision Required')
                    <span class="badge bg-warning text-dark px-3 py-2 fs-7 me-1">{{ $period->status }}</span>
                    
                    <button type="button" class="btn btn-custom-secondary me-1" data-bs-toggle="modal" data-bs-target="#editPeriodModal">
                        <i class="bi bi-pencil-square me-1"></i> Edit Period
                    </button>

                    <form action="{{ route('payroll.periods.process', $period->id) }}" method="POST" class="m-0 d-inline-block">
                        @csrf
                        <button type="submit" class="btn btn-custom-primary me-1">
                            <i class="bi bi-play-circle-fill me-1"></i> Process Payroll
                        </button>
                    </form>

                    <form action="{{ route('payroll.periods.submit', $period->id) }}" method="POST" class="m-0 d-inline-block">
                        @csrf
                        <button type="submit" class="btn btn-outline-primary me-1">
                            <i class="bi bi-arrow-right-circle-fill me-1"></i> Submit for HR Review
                        </button>
                    </form>

                    @if($period->status === 'Draft')
                        <form action="{{ route('payroll.periods.destroy', $period->id) }}" method="POST" class="m-0 d-inline-block" onsubmit="return confirm('Are you sure you want to delete this Draft payroll period?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger">
                                <i class="bi bi-trash me-1"></i> Delete
                            </button>
                        </form>
                    @endif
                @elseif($period->status === 'Processed')
                    <span class="badge bg-info text-dark px-3 py-2 fs-7 me-1">Processed</span>
                    <form action="{{ route('payroll.periods.process', $period->id) }}" method="POST" class="m-0 d-inline-block">
                        @csrf
                        <button type="submit" class="btn btn-custom-secondary me-1">
                            <i class="bi bi-arrow-clockwise me-1"></i> Re-Process Bulk
                        </button>
                    </form>
                    <form action="{{ route('payroll.periods.submit', $period->id) }}" method="POST" class="m-0 d-inline-block">
                        @csrf
                        <button type="submit" class="btn btn-custom-primary">
                            <i class="bi bi-arrow-right-circle-fill me-1"></i> Submit for HR Review
                        </button>
                    </form>
                @elseif($period->status === 'HR Review')
                    <span class="badge bg-primary px-3 py-2 fs-7 me-1">HR Review</span>
                    <button type="button" class="btn btn-outline-danger me-1" data-bs-toggle="modal" data-bs-target="#revisionModal">
                        <i class="bi bi-arrow-return-left me-1"></i> Request Revision
                    </button>
                    <form action="{{ route('payroll.periods.approve', $period->id) }}" method="POST" class="m-0 d-inline-block">
                        @csrf
                        <input type="hidden" name="comment" value="Approved automatically via UI">
                        <button type="submit" class="btn btn-custom-primary" onclick="return confirm('Are you sure you want to approve this run?');">
                            <i class="bi bi-check-circle-fill me-1"></i> Approve Payroll
                        </button>
                    </form>
                @elseif($period->status === 'Approved')
                    <span class="badge bg-success px-3 py-2 fs-7 me-1">Approved</span>
                    <form action="{{ route('payroll.periods.lock', $period->id) }}" method="POST" class="m-0 d-inline-block">
                        @csrf
                        <input type="hidden" name="reason" value="Locked via UI">
                        <button type="submit" class="btn btn-danger text-light px-3 py-2 fw-semibold" onclick="return confirm('CRITICAL: Locking this period makes it immutable. Proceed?');" style="border-radius: 10px;">
                            <i class="bi bi-lock-fill me-1"></i> Lock Payroll Run
                        </button>
                    </form>
                @else
                    <span class="badge bg-dark px-3 py-2 fs-7 me-1"><i class="bi bi-lock-fill"></i> Locked (Rev {{ $period->revision_number }})</span>
                    <button type="button" class="btn btn-outline-warning text-dark me-1" data-bs-toggle="modal" data-bs-target="#createRevisionModal">
                        <i class="bi bi-journal-plus me-1"></i> Create Revision
                    </button>
                @endif
            </div>
        </div>
    </div>

    <!-- Calculated Payslips Listing -->
    <div class="col-lg-12">
        <div class="glass-card">
            <h5 class="mb-4 display-font fw-bold"><i class="bi bi-file-earmark-check-fill me-2 text-primary"></i> Computed Salary Register</h5>
            
            @if($payslips->isEmpty())
                <div class="text-center text-secondary py-5">
                    <i class="bi bi-file-earmark-x display-1 text-muted opacity-50 mb-3"></i>
                    <p class="fs-5 fw-medium">No calculations found.</p>
                    <p class="fs-7 mb-3">Click <strong>"Process Payroll"</strong> at the top right or click the button below to calculate this period.</p>
                    @if($period->status === 'Draft' || $period->status === 'Revision Required')
                        <form action="{{ route('payroll.periods.process', $period->id) }}" method="POST" class="d-inline-block">
                            @csrf
                            <button type="submit" class="btn btn-custom-primary px-4 py-2 shadow-sm">
                                <i class="bi bi-play-circle-fill me-1"></i> Process Payroll Now
                            </button>
                        </form>
                    @endif
                </div>
            @else
                <div class="table-responsive" style="overflow-x: auto;">
                    <table class="table custom-table align-middle">
                        <thead>
                            <tr class="text-nowrap fs-8 uppercase">
                                <th>Emp. No.</th>
                                <th>Total Package</th>
                                <th>Basic Salary</th>
                                <th>No WFH</th>
                                <th>Half Day</th>
                                <th>No Pay Days</th>
                                <th>No Pay Amt</th>
                                <th>Gross Salary</th>
                                <th>Incentive</th>
                                <th>EPF 8%</th>
                                <th>Dedu Inc.</th>
                                <th>KPI %</th>
                                <th>Dedu Inc. NP</th>
                                <th>Advance</th>
                                <th>Loan</th>
                                <th>APIT (Tax)</th>
                                <th>EPF 12%</th>
                                <th>ETF 3%</th>
                                <th>EPF 20%</th>
                                <th>Net Salary</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($payslips as $ps)
                                <tr>
                                    <td class="text-nowrap">
                                        <div class="fw-semibold">{{ $ps->employee->employee_id ?? 'N/A' }}</div>
                                        <div class="text-secondary fs-8">{{ $ps->employee->full_name ?? '' }}</div>
                                    </td>
                                    <td class="text-nowrap">Rs. {{ number_format($ps->total_package, 2) }}</td>
                                    <td class="text-nowrap">Rs. {{ number_format($ps->basic_salary, 2) }}</td>
                                    <td class="text-nowrap text-center">{{ number_format($ps->no_wfh_days, 1) }}</td>
                                    <td class="text-nowrap text-center">{{ number_format($ps->half_days, 1) }}</td>
                                    <td class="text-nowrap text-center fw-semibold text-danger">{{ number_format($ps->no_pay_days, 1) }}</td>
                                    <td class="text-nowrap text-danger">Rs. {{ number_format($ps->no_pay_deduction, 2) }}</td>
                                    <td class="text-nowrap fw-bold">Rs. {{ number_format($ps->gross_salary, 2) }}</td>
                                    <td class="text-nowrap">Rs. {{ number_format($ps->incentive, 2) }}</td>
                                    <td class="text-nowrap text-warning">Rs. {{ number_format($ps->epf_employee, 2) }}</td>
                                    <td class="text-nowrap text-danger">Rs. {{ number_format($ps->dedu_inc, 2) }}</td>
                                    <td class="text-nowrap text-center">{{ number_format($ps->kpi_percentage, 0) }}%</td>
                                    <td class="text-nowrap text-danger">Rs. {{ number_format($ps->dedu_inc_np, 2) }}</td>
                                    <td class="text-nowrap text-danger">Rs. {{ number_format($ps->advance_deduction, 2) }}</td>
                                    <td class="text-nowrap text-danger">Rs. {{ number_format($ps->loan_deduction, 2) }}</td>
                                    <td class="text-nowrap text-danger">Rs. {{ number_format($ps->apit, 2) }}</td>
                                    <td class="text-nowrap text-info">Rs. {{ number_format($ps->epf_employer, 2) }}</td>
                                    <td class="text-nowrap text-info">Rs. {{ number_format($ps->etf_employer, 2) }}</td>
                                    <td class="text-nowrap fw-semibold text-primary">Rs. {{ number_format($ps->epf_total ?? ($ps->epf_employee + $ps->epf_employer), 2) }}</td>
                                    <td class="text-nowrap fw-bold text-success" style="font-size: 1.05rem;">Rs. {{ number_format($ps->net_salary, 2) }}</td>
                                    <td class="text-nowrap">
                                        <div class="d-flex align-items-center">
                                            <a href="{{ route('payroll.payslip', $ps->id) }}" class="btn btn-sm btn-outline-primary border-0 me-2" target="_blank">
                                                <i class="bi bi-file-text"></i> Payslip
                                            </a>
                                            @if($period->status !== 'Locked')
                                                <form action="{{ route('payroll.periods.recalculate', ['id' => $period->id, 'employee_id' => $ps->employee_id]) }}" method="POST" class="m-0">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-secondary border-0">
                                                        <i class="bi bi-arrow-clockwise"></i> Recalc
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <!-- Missing Profiles Warnings -->
    @if(isset($missingProfiles) && $missingProfiles->count() > 0)
        <div class="col-12">
            <div class="alert alert-warning border-0 shadow-sm p-4 d-flex align-items-start gap-3" style="background-color: rgba(245, 158, 11, 0.15); color: #f59e0b;" role="alert">
                <i class="bi bi-exclamation-triangle-fill fs-4"></i>
                <div>
                    <h5 class="alert-heading display-font fw-bold m-0 text-warning">Salary Profile Setup Missing</h5>
                    <p class="fs-7 mt-2 mb-3">The following active employees were skipped during processing because they do not have a configured Salary Profile. Setup their salaries first to process payouts.</p>
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        @foreach($missingProfiles as $ue)
                            <span class="badge bg-dark text-warning p-2">
                                <i class="bi bi-person-fill me-1"></i> {{ $ue->full_name }} ({{ $ue->employee_id }})
                            </span>
                        @endforeach
                    </div>
                    <a href="{{ route('payroll.profiles') }}" class="btn btn-sm btn-warning fw-semibold px-4" style="color: #4a3e00;">
                        <i class="bi bi-person-fill-gear me-1"></i> Setup Missing Salaries
                    </a>
                </div>
            </div>
        </div>
    @endif
</div>

<!-- Request Revision Modal (HR Review) -->
<div class="modal fade" id="revisionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content glass-card border-0">
            <form action="{{ route('payroll.periods.revision', $period->id) }}" method="POST">
                @csrf
                <div class="modal-header border-0">
                    <h5 class="modal-title display-font fw-bold">Request Revision</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body border-0">
                    <p class="fs-7 text-secondary">Please provide a reason why this payroll requires revision. The payroll will be sent back for processing.</p>
                    <div class="mb-3">
                        <label class="form-label fs-7 fw-semibold">Reason for Revision</label>
                        <textarea class="form-control" name="reason" rows="3" required placeholder="E.g., Attendance correction required for P1-1059"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-transparent">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Request Revision</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Create Revision Modal (Locked Period) -->
<div class="modal fade" id="createRevisionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content glass-card border-0">
            <form action="{{ route('payroll.periods.create_revision', $period->id) }}" method="POST">
                @csrf
                <div class="modal-header border-0">
                    <h5 class="modal-title display-font fw-bold text-warning">Create New Revision</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body border-0">
                    <div class="alert alert-warning fs-7">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i>
                        You are about to create a new revision from this locked period. The original locked snapshot will be preserved as Revision {{ $period->revision_number }}.
                    </div>
                    <div class="mb-3">
                        <label class="form-label fs-7 fw-semibold">Reason for creating a new revision</label>
                        <textarea class="form-control" name="reason" rows="3" required placeholder="E.g., Management approved post-lock correction"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-transparent">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning text-dark fw-bold">Create Revision</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Period Modal -->
<div class="modal fade" id="editPeriodModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content glass-card p-0" style="background-color: var(--sidebar-bg); border: 1px solid var(--border-color); color: white;">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title display-font text-white"><i class="bi bi-pencil-square me-2 text-primary"></i> Edit Payroll Period</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('payroll.periods.update', $period->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fs-8 uppercase fw-bold text-secondary">Period Name</label>
                        <input type="text" class="form-control" name="period_name" value="{{ $period->period_name }}" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fs-8 uppercase fw-bold text-secondary">Start Date</label>
                            <input type="date" class="form-control" name="start_date" value="{{ $period->start_date->format('Y-m-d') }}" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fs-8 uppercase fw-bold text-secondary">End Date</label>
                            <input type="date" class="form-control" name="end_date" value="{{ $period->end_date->format('Y-m-d') }}" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fs-8 uppercase fw-bold text-secondary">Cycle Type</label>
                        <select class="form-select" name="cycle_type" required>
                            <option value="Monthly" {{ $period->cycle_type === 'Monthly' ? 'selected' : '' }}>Monthly</option>
                            <option value="Weekly" {{ $period->cycle_type === 'Weekly' ? 'selected' : '' }}>Weekly</option>
                            <option value="Bi-Weekly" {{ $period->cycle_type === 'Bi-Weekly' ? 'selected' : '' }}>Bi-Weekly</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-custom-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
