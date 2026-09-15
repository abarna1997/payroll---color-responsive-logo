@extends('layouts.app')

@section('content')
<style>
    .stat-card-custom {
        border-left: 4px solid var(--accent-color);
    }
</style>

<div class="row g-4">
    <!-- Top Statistics Dashboard Row 1 -->
    <div class="col-12">
        <div class="row row-cols-1 row-cols-sm-2 row-cols-xl-4 g-3">
            <div class="col">
                <div class="glass-card d-flex align-items-center justify-content-between p-3 p-md-4 h-100">
                    <div>
                        <div class="text-secondary fs-8 uppercase fw-bold mb-1">Total Cycles</div>
                        <h3 class="display-font m-0 fw-bold">{{ $periods->count() }}</h3>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background-color: rgba(249, 87, 22, 0.1); color: var(--accent-color); font-size: 1.4rem;">
                        <i class="bi bi-calendar-range"></i>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="glass-card d-flex align-items-center justify-content-between p-3 p-md-4 h-100">
                    <div>
                        <div class="text-secondary fs-8 uppercase fw-bold mb-1">Active Runs</div>
                        <h3 class="display-font m-0 fw-bold">{{ $periods->where('status', 'Processed')->count() }}</h3>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background-color: rgba(25, 135, 84, 0.1); color: #198754; font-size: 1.4rem;">
                        <i class="bi bi-clock-history"></i>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="glass-card d-flex align-items-center justify-content-between p-3 p-md-4 h-100">
                    <div>
                        <div class="text-secondary fs-8 uppercase fw-bold mb-1">Locked Records</div>
                        <h3 class="display-font m-0 fw-bold">{{ $periods->where('status', 'Locked')->count() }}</h3>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background-color: rgba(13, 110, 253, 0.1); color: #0d6efd; font-size: 1.4rem;">
                        <i class="bi bi-lock-fill"></i>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="glass-card d-flex align-items-center justify-content-between p-3 p-md-4 h-100">
                    <div>
                        <div class="text-secondary fs-8 uppercase fw-bold mb-1">Pending Approval</div>
                        <h3 class="display-font m-0 fw-bold">{{ $periods->where('status', 'Approved')->count() }}</h3>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background-color: rgba(255, 193, 7, 0.1); color: #ffc107; font-size: 1.4rem;">
                        <i class="bi bi-patch-check"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Row 2: Gross Costs, Employee metrics & Chart.js Trends -->
    <div class="col-12">
        <div class="row g-4">
            <!-- Left: Cost Trend line chart and quick metrics -->
            <div class="col-lg-8">
                <div class="glass-card h-100">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h6 class="display-font fw-bold text-dark m-0"><i class="bi bi-graph-up me-1 text-primary"></i> Monthly Costs Trend Analysis</h6>
                            <p class="text-secondary fs-9 mb-0">Visual summary of completed runs and net salaries payout allocations.</p>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-primary fs-9">Active Period: {{ $metrics['period_name'] }}</span>
                        </div>
                    </div>
                    
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <div class="border rounded p-2 bg-white shadow-sm stat-card-custom" style="border-left-color: #3b82f6;">
                                <div class="fs-9 uppercase fw-bold text-secondary">Employees Processed</div>
                                <h5 class="fw-bold text-dark m-0 mt-1">{{ $metrics['employees_included'] }} Staff</h5>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded p-2 bg-white shadow-sm stat-card-custom" style="border-left-color: #10b981;">
                                <div class="fs-9 uppercase fw-bold text-secondary">Gross Payroll Costs</div>
                                <h5 class="fw-bold text-dark m-0 mt-1">{{ $currency }} {{ number_format($metrics['payroll_cost'], 2) }}</h5>
                            </div>
                        </div>
                    </div>

                    <canvas id="costsTrendChart" style="max-height: 200px;"></canvas>
                </div>
            </div>
            <!-- Right: Doughnut chart ratio breakdown -->
            <div class="col-lg-4">
                <div class="glass-card h-100">
                    <h6 class="display-font fw-bold text-dark mb-4"><i class="bi bi-pie-chart me-1 text-primary"></i> Contributions Ratio</h6>
                    <canvas id="contributionsChart" style="max-height: 200px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Row 3: Active Runs list table & Quick Actions -->
    <div class="col-lg-8" id="periods-section">
        <div class="glass-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="m-0 display-font fw-bold"><i class="bi bi-credit-card-2-back me-2 text-primary"></i> Payroll Periods</h5>
                <button type="button" class="btn btn-custom-primary" data-bs-toggle="modal" data-bs-target="#createPeriodModal">
                    <i class="bi bi-plus-circle-fill me-1"></i> New Period
                </button>
            </div>
            
            <div class="table-responsive">
                <table class="table custom-table">
                    <thead>
                        <tr>
                            <th>Period Name</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Cycle Type</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($periods as $p)
                            <tr>
                                <td><span class="fw-semibold">{{ $p->period_name }}</span></td>
                                <td>{{ $p->start_date->format('Y-m-d') }}</td>
                                <td>{{ $p->end_date->format('Y-m-d') }}</td>
                                <td><span class="badge bg-secondary">{{ $p->cycle_type }}</span></td>
                                <td>
                                    @if($p->status === 'Draft')
                                        <span class="badge bg-warning text-dark">Draft</span>
                                    @elseif($p->status === 'Processed')
                                        <span class="badge bg-info text-dark">Processed</span>
                                    @elseif($p->status === 'Approved')
                                        <span class="badge bg-success">Approved</span>
                                    @else
                                        <span class="badge bg-dark">Locked</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="{{ route('payroll.show', $p->id) }}" class="btn btn-sm btn-outline-primary border-0 me-1">
                                            <i class="bi bi-eye"></i> View Run
                                        </a>
                                        @if($p->status === 'Draft' || $p->status === 'Revision Required')
                                        <button type="button" class="btn btn-sm btn-outline-secondary border-0 me-1" data-bs-toggle="modal" data-bs-target="#editPeriodModal{{ $p->id }}">
                                            <i class="bi bi-pencil-square"></i> Edit
                                        </button>
                                        @endif
                                        @if($p->status === 'Draft')
                                        <form action="{{ route('payroll.periods.destroy', $p->id) }}" method="POST" class="m-0" onsubmit="return confirm('Are you sure you want to delete this Draft payroll period?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger border-0">
                                                <i class="bi bi-trash"></i> Delete
                                            </button>
                                        </form>
                                        @endif
                                    </div>

                                    @if($p->status === 'Draft' || $p->status === 'Revision Required')
                                    <!-- Edit Period Modal for {{ $p->id }} -->
                                    <div class="modal fade" id="editPeriodModal{{ $p->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content glass-card p-0" style="background-color: var(--sidebar-bg); border: 1px solid var(--border-color); color: white;">
                                                <div class="modal-header border-bottom-0 pb-0">
                                                    <h5 class="modal-title display-font text-white"><i class="bi bi-pencil-square me-2 text-primary"></i> Edit Payroll Period</h5>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <form action="{{ route('payroll.periods.update', $p->id) }}" method="POST">
                                                    @csrf
                                                    <div class="modal-body text-start">
                                                        <div class="mb-3">
                                                            <label class="form-label text-secondary fs-8 uppercase">Period Name</label>
                                                            <input type="text" class="form-control form-control-custom" name="period_name" value="{{ $p->period_name }}" required>
                                                        </div>
                                                        <div class="row mb-3">
                                                            <div class="col">
                                                                <label class="form-label text-secondary fs-8 uppercase">Start Date</label>
                                                                <input type="date" class="form-control form-control-custom" name="start_date" value="{{ $p->start_date->format('Y-m-d') }}" required>
                                                            </div>
                                                            <div class="col">
                                                                <label class="form-label text-secondary fs-8 uppercase">End Date</label>
                                                                <input type="date" class="form-control form-control-custom" name="end_date" value="{{ $p->end_date->format('Y-m-d') }}" required>
                                                            </div>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label text-secondary fs-8 uppercase">Cycle Type</label>
                                                            <select class="form-select form-select-custom" name="cycle_type" required>
                                                                <option value="Monthly" {{ $p->cycle_type === 'Monthly' ? 'selected' : '' }}>Monthly</option>
                                                                <option value="Weekly" {{ $p->cycle_type === 'Weekly' ? 'selected' : '' }}>Weekly</option>
                                                                <option value="Bi-Weekly" {{ $p->cycle_type === 'Bi-Weekly' ? 'selected' : '' }}>Bi-Weekly</option>
                                                                <option value="Custom" {{ $p->cycle_type === 'Custom' ? 'selected' : '' }}>Custom Dates</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer border-top-0 pt-0">
                                                        <button type="button" class="btn btn-custom-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-custom-primary">Save Changes</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-secondary py-4">No payroll periods found. Trigger a new one above.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Quick Navigation Side Card -->
    <div class="col-lg-4">
        <div class="glass-card mb-4">
            <h5 class="mb-3 display-font fw-bold"><i class="bi bi-gear-fill me-2 text-primary"></i> Actions</h5>
            <p class="text-secondary fs-7 mb-4">Manage employee structural base settings and allowances configuration before running payroll calculations.</p>
            <a href="{{ route('payroll.profiles') }}" class="btn btn-custom-secondary w-100 mb-2 py-2">
                <i class="bi bi-person-fill-gear me-2"></i> Employee Salary Profiles
            </a>

        </div>
        
        <div class="glass-card" style="background-color: var(--sidebar-bg); color: white; border: none;">
            <h5 class="mb-3 display-font fw-bold text-white"><i class="bi bi-shield-lock-fill me-2 text-primary"></i> Compliance Note</h5>
            <p class="fs-7 text-secondary mb-0">Statutory calculation logic is mapped according to the Inland Revenue Department (IRD) and Sri Lankan EPF Act parameters (8% Employee, 12% Employer, 3% ETF).</p>
        </div>
    </div>
</div>

<!-- Create Period Modal -->
<div class="modal fade" id="createPeriodModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content glass-card p-0" style="background-color: var(--sidebar-bg); border: 1px solid var(--border-color); color: white;">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title display-font text-white"><i class="bi bi-calendar-plus me-2 text-primary"></i> Create Payroll Period</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('payroll.periods.store') }}" method="POST">
                @csrf
                <div class="modal-body text-start">
                    <div class="mb-3">
                        <label class="form-label text-secondary fs-8 uppercase">Period Name</label>
                        <input type="text" class="form-control form-control-custom" name="period_name" placeholder="e.g. July 2026 Monthly Run" required>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label class="form-label text-secondary fs-8 uppercase">Start Date</label>
                            <input type="date" class="form-control form-control-custom" name="start_date" required>
                        </div>
                        <div class="col">
                            <label class="form-label text-secondary fs-8 uppercase">End Date</label>
                            <input type="date" class="form-control form-control-custom" name="end_date" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-secondary fs-8 uppercase">Cycle Type</label>
                        <select class="form-select form-select-custom" name="cycle_type" required>
                            <option value="Monthly">Monthly</option>
                            <option value="Weekly">Weekly</option>
                            <option value="Bi-Weekly">Bi-Weekly</option>
                            <option value="Custom">Custom Dates</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-custom-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-custom-primary">Initialize Period</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ChartJS CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const hasTrendData = {{ count($trendData) > 0 ? 'true' : 'false' }};
        const hasContributionData = {{ $metrics['employees_included'] > 0 ? 'true' : 'false' }};

        // CHARTS: Monthly Cost Trend
        const ctxTrend = document.getElementById('costsTrendChart');
        if (ctxTrend) {
            if (hasTrendData) {
                new Chart(ctxTrend, {
                    type: 'line',
                    data: {
                        labels: {!! json_encode($trendLabels) !!},
                        datasets: [{
                            label: 'Gross Payroll Costs ({{ $currency }})',
                            data: {!! json_encode($trendData) !!},
                            borderColor: '#f95716',
                            backgroundColor: 'rgba(249, 87, 22, 0.1)',
                            tension: 0.3,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: { legend: { display: false } }
                    }
                });
            } else {
                ctxTrend.parentElement.innerHTML += '<div class="text-center text-secondary py-5"><i class="bi bi-bar-chart text-muted fs-1 mb-2"></i><br>No payroll data available</div>';
                ctxTrend.style.display = 'none';
            }
        }

        // CHARTS: Contributions Ratio Chart
        const ctxRatio = document.getElementById('contributionsChart');
        if (ctxRatio) {
            if (hasContributionData) {
                new Chart(ctxRatio, {
                    type: 'doughnut',
                    data: {
                        labels: ['Net Salary', 'EPF (Emp + Empr)', 'ETF', 'OT'],
                        datasets: [{
                            data: [
                                {{ $metrics['payroll_cost'] }},
                                {{ $metrics['total_epf'] }},
                                {{ $metrics['total_etf'] }},
                                {{ $metrics['total_ot'] }}
                            ],
                            backgroundColor: ['#f95716', '#1b2a35', '#388e3c', '#ffb300']
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: { legend: { position: 'bottom' } }
                    }
                });
            } else {
                ctxRatio.parentElement.innerHTML += '<div class="text-center text-secondary py-5"><i class="bi bi-pie-chart text-muted fs-1 mb-2"></i><br>No payroll data available</div>';
                ctxRatio.style.display = 'none';
            }
        }
    });
</script>
@endsection
