@extends('layouts.app')

@section('content')
<div class="row g-4">
    <div class="col-12">
        <div class="glass-card">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="m-0 display-font fw-bold">
                    <i class="bi bi-file-earmark-check-fill me-2 text-primary"></i> Computed Payslips Registry
                </h5>
                <a href="{{ route('payroll.index') }}" class="btn btn-custom-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back to Payroll
                </a>
            </div>

            <!-- Filters Form -->
            <form method="GET" action="{{ route('payroll.payslips') }}" class="row g-3 mb-4 align-items-end">
                <div class="col-md-5">
                    <label class="form-label text-secondary fs-8 uppercase fw-bold">Search Employee</label>
                    <div class="input-group">
                        <span class="input-group-text" style="background-color: var(--input-bg); border-color: var(--border-color); color: var(--text-secondary);">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" name="search" class="form-control form-control-custom" placeholder="Search by name or employee ID..." value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-md-4">
                    <label class="form-label text-secondary fs-8 uppercase fw-bold">Payroll Period</label>
                    <select name="period_id" class="form-select form-select-custom">
                        <option value="">-- All Periods --</option>
                        @foreach($periods as $p)
                            <option value="{{ $p->id }}" {{ request('period_id') == $p->id ? 'selected' : '' }}>
                                {{ $p->period_name }} ({{ $p->start_date->format('M d') }} - {{ $p->end_date->format('M d, Y') }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-custom-primary flex-grow-1 py-2">
                        <i class="bi bi-funnel me-1"></i> Filter
                    </button>
                    @if(request()->anyFilled(['search', 'period_id']))
                        <a href="{{ route('payroll.payslips') }}" class="btn btn-custom-secondary py-2">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    @endif
                </div>
            </form>

            <!-- Table -->
            <div class="table-responsive">
                <table class="table custom-table">
                    <thead>
                        <tr>
                            <th>Employee ID</th>
                            <th>Employee Name</th>
                            <th>Payroll Period</th>
                            <th>Basic Salary</th>
                            <th>Gross Earnings</th>
                            <th>Total Deductions</th>
                            <th>Net Payout</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payslips as $ps)
                            @php
                                $deductionsJsonSum = collect($ps->deductions_json)->sum('amount');
                                $totalDeductions = $ps->epf_employee + $ps->dedu_inc + ($ps->dedu_inc_np ?? 0) + $deductionsJsonSum;
                            @endphp
                            <tr>
                                <td>
                                    <span class="badge bg-indigo text-light">{{ $ps->employee->employee_id ?? 'N/A' }}</span>
                                </td>
                                <td class="fw-semibold">{{ $ps->employee->full_name ?? 'Unknown Employee' }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $ps->period->period_name ?? 'N/A' }}</div>
                                    <div class="text-secondary fs-8">{{ $ps->period ? $ps->period->start_date->format('Y-m-d') : '' }} to {{ $ps->period ? $ps->period->end_date->format('Y-m-d') : '' }}</div>
                                </td>
                                <td>{{ $currency }} {{ number_format($ps->basic_salary, 2) }}</td>
                                <td>{{ $currency }} {{ number_format($ps->gross_salary, 2) }}</td>
                                <td>{{ $currency }} {{ number_format($totalDeductions, 2) }}</td>
                                <td class="fw-bold text-success" style="font-size: 1.05rem;">
                                    {{ $currency }} {{ number_format($ps->net_salary, 2) }}
                                </td>
                                <td>
                                    <a href="{{ route('payroll.payslip', $ps->id) }}" class="btn btn-sm btn-outline-primary border-0" target="_blank">
                                        <i class="bi bi-file-pdf me-1"></i> View Payslip
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-secondary py-4">
                                    No computed payslips found matching your filters.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $payslips->appends(request()->query())->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>
@endsection
