@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between mb-4">
        <h2>Payroll Configuration (Production)</h2>
        <div>
            <button class="btn btn-primary" onclick="showCreateModal('ot')">New OT Rule</button>
            <button class="btn btn-secondary" onclick="showCreateModal('tax_year')">New Tax Year</button>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <ul class="nav nav-tabs" id="configTabs">
        <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#ot-rules">OT Rules</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tax-years">Tax Years</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tax-rules">Tax Rules</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#no-pay">No-Pay</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#history">History</a></li>
    </ul>

    <div class="tab-content mt-4">
        <!-- OT RULES TAB -->
        <div class="tab-pane fade show active" id="ot-rules">
            <h4>Overtime Configuration</h4>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Setting</th>
                        <th>Current Value</th>
                        <th>Effective From</th>
                        <th>Effective To</th>
                        <th>State</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $otKeys = ['ot_payment_enabled', 'ot_formula_type', 'ot_divisor', 'ot_multiplier', 'holiday_multiplier', 'off_day_multiplier', 'weekend_multiplier', 'minimum_ot_minutes', 'maximum_ot_minutes', 'rounding_rule'];
                    @endphp
                    @foreach(\App\Models\PayrollConfiguration::whereIn('setting_key', $otKeys)->orderBy('id', 'desc')->get() as $config)
                        @php
                            $state = $config->status;
                            if ($state === 'APPROVED' && $config->effective_from <= now()->toDateString() && ($config->effective_to === null || $config->effective_to >= now()->toDateString()) && $config->is_active) {
                                $state = 'ACTIVE';
                            } elseif ($state === 'APPROVED' && $config->effective_from > now()->toDateString()) {
                                $state = 'FUTURE';
                            } elseif ($state === 'APPROVED' && $config->effective_to < now()->toDateString()) {
                                $state = 'EXPIRED';
                            }
                        @endphp
                        <tr>
                            <td>{{ ucwords(str_replace('_', ' ', $config->setting_key)) }}</td>
                            <td>{{ $config->setting_value }}</td>
                            <td>{{ $config->effective_from->format('Y-m-d') }}</td>
                            <td>{{ $config->effective_to ? $config->effective_to->format('Y-m-d') : 'Ongoing' }}</td>
                            <td><span class="badge bg-{{ $state === 'ACTIVE' ? 'success' : ($state === 'DRAFT' ? 'warning' : 'secondary') }}">{{ $state }}</span></td>
                            <td>
                                @if($config->status === 'DRAFT')
                                    <form action="{{ route('payroll.configuration.approve', $config->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="approval_reason" value="Management Approval">
                                        <button type="submit" class="btn btn-sm btn-success">Approve</button>
                                    </form>
                                    <form action="{{ route('payroll.configuration.reject', $config->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="rejection_reason" value="Management Rejection">
                                        <button type="submit" class="btn btn-sm btn-danger">Reject</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- TAX YEARS TAB -->
        <div class="tab-pane fade" id="tax-years">
            <h4>Tax Years</h4>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Year</th>
                        <th>Start</th>
                        <th>End</th>
                        <th>Version</th>
                        <th>Status</th>
                        <th>Approved By</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach(\App\Models\TaxYear::orderBy('start_date', 'desc')->get() as $year)
                    <tr>
                        <td>{{ $year->name }}</td>
                        <td>{{ $year->start_date->format('Y-m-d') }}</td>
                        <td>{{ $year->end_date->format('Y-m-d') }}</td>
                        <td>{{ $year->version }}</td>
                        <td>{{ $year->status }}</td>
                        <td>{{ $year->approvedBy ? $year->approvedBy->name : 'N/A' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <!-- NO-PAY TAB -->
        <div class="tab-pane fade" id="no-pay">
            <h4>No-Pay Rules</h4>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Setting</th>
                        <th>Value</th>
                        <th>Effective From</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach(\App\Models\PayrollConfiguration::where('setting_key', 'no_pay_divisor')->get() as $config)
                    <tr>
                        <td>No-Pay Divisor</td>
                        <td>{{ $config->setting_value }}</td>
                        <td>{{ $config->effective_from->format('Y-m-d') }}</td>
                        <td>{{ $config->status }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <div class="tab-pane fade" id="history">
            <h4>Complete Audit History</h4>
            <p>Full record of configuration changes.</p>
        </div>
    </div>

    <!-- Create Configuration Modal -->
    <div class="modal fade" id="createConfigModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('payroll.configuration.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Draft New Configuration</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info">
                            Rules are created as DRAFT. An authorized user must APPROVE them before they become ACTIVE.
                        </div>
                        <div class="mb-3">
                            <label>Setting Key</label>
                            <select name="setting_key" class="form-control" required>
                                <option value="ot_payment_enabled">OT Payment Enabled</option>
                                <option value="ot_divisor">OT Divisor</option>
                                <option value="ot_multiplier">OT Multiplier</option>
                                <option value="holiday_multiplier">Holiday Multiplier</option>
                                <option value="off_day_multiplier">Off-Day Multiplier</option>
                                <option value="minimum_ot_minutes">Minimum OT Minutes</option>
                                <option value="no_pay_divisor">No-Pay Divisor</option>
                            </select>
                        </div>
                        <input type="hidden" name="value_type" value="decimal">
                        <div class="mb-3">
                            <label>New Value</label>
                            <input type="text" name="setting_value" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Effective From</label>
                            <input type="date" name="effective_from" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Business Justification / Reason</label>
                            <textarea name="reason" class="form-control" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-warning">Save as Draft</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function showCreateModal(type) {
        var modal = new bootstrap.Modal(document.getElementById('createConfigModal'));
        modal.show();
    }
</script>
@endsection
