@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Phase 4B: Business Rule Decisions</h2>
        <span class="badge bg-warning text-dark">UI ONLY - NO CALCULATION CHANGES ACTIVE</span>
    </div>

    <!-- OT DATA FLOW -->
    <div class="card mb-4 shadow-sm border-primary">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">OT Data Flow Architecture</h5>
        </div>
        <div class="card-body bg-light">
            <div class="d-flex justify-content-between text-center align-items-center flex-wrap">
                <div class="p-2 border bg-white rounded"><strong>Attendance Engine</strong><br><small>overtime_minutes</small></div>
                <div class="px-2">→</div>
                <div class="p-2 border bg-white rounded"><strong>Payroll</strong><br><small>OT Payment Setting</small></div>
                <div class="px-2">→</div>
                <div class="p-2 border bg-white rounded"><strong>Formula</strong><br><small>Approved OT Formula</small></div>
                <div class="px-2">→</div>
                <div class="p-2 border bg-white rounded"><strong>Result</strong><br><small>OT Amount</small></div>
            </div>
            <div class="mt-3 text-muted text-center">
                <em>If OT Payment Enabled = NO: overtime_minutes remains stored/displayed, but OT Amount = 0</em>
            </div>
        </div>
    </div>

    <form action="#" method="POST">
        @csrf
        
        <!-- OT DECISION SCREEN -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">OT Settings Configuration</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">OT Payment Enabled</label>
                        <select class="form-select" name="ot_payment_enabled">
                            <option value="">-- Select --</option>
                            <option value="1">YES</option>
                            <option value="0">NO</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">OT Formula Model</label>
                        <select class="form-select" name="ot_formula_model">
                            <option value="">-- Select --</option>
                            <option value="Model A">Model A (Basic / Hourly Divisor * OT Hours * Multiplier)</option>
                            <option value="Model B">Model B (Basic / Working Days / Daily Hours * OT Hours * Multiplier)</option>
                            <option value="Model C">Model C (Fixed Hourly Rate)</option>
                            <option value="Custom">Custom Formula</option>
                        </select>
                        <small class="text-muted">No default formula selected automatically.</small>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Normal Day OT</label>
                        <input type="text" class="form-control" placeholder="e.g. 1.5" name="normal_day_ot">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Holiday OT</label>
                        <input type="text" class="form-control" placeholder="e.g. 2.0" name="holiday_ot">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">OFF-Day OT</label>
                        <input type="text" class="form-control" placeholder="e.g. 1.5" name="off_day_ot">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Weekend OT</label>
                        <input type="text" class="form-control" placeholder="e.g. 1.5" name="weekend_ot">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Minimum OT Minutes</label>
                        <input type="number" class="form-control" name="min_ot_minutes" placeholder="Optional">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Maximum OT Hours/Month</label>
                        <input type="number" class="form-control" name="max_ot_hours" placeholder="Optional">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">OT Rounding</label>
                        <select class="form-select" name="ot_rounding">
                            <option value="">-- Select --</option>
                            <option value="2">2 Decimal Places</option>
                            <option value="0">Nearest Integer</option>
                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">OT Approval Workflow</label>
                        <select class="form-select" name="ot_approval">
                            <option value="">-- Select --</option>
                            <option value="Automatic">Automatic (from Summary)</option>
                            <option value="Manager">Manager Approval Required</option>
                            <option value="HR">HR Approval Required</option>
                            <option value="Payroll">Payroll Approval Required</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- APIT/PAYE DECISION -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">APIT/PAYE Configuration</h5>
            </div>
            <div class="card-body">
                <p><strong>Current Status:</strong> The existing implementation uses a hardcoded 2023/24 SL monthly tax structure (100k exempt, 41,667 @ 6%, 12%, 18%, 24%, 30%, excess @ 36%). No cumulative year-of-assessment rules or Resident/Non-Resident differentiations exist.</p>
                <div class="alert alert-info">
                    <strong>Proposed Versioned Architecture:</strong><br>
                    Tax Rule → Tax Year (e.g. April to March) → Effective From/To → Employment Type → Resident/Non-Resident → Brackets → Rate → Relief
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">APIT Calculation Method</label>
                        <select class="form-select" name="apit_method">
                            <option value="">-- Select --</option>
                            <option value="keep_hardcoded">Keep Current Hardcoded Logic (2023/24)</option>
                            <option value="build_versioned">Build New Versioned DB Architecture (2025/26 ready)</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- SNAPSHOT DESIGN -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0">Payroll Snapshot Architecture (To Be Implemented)</h5>
            </div>
            <div class="card-body">
                <ul>
                    <li><strong>OT:</strong> OT minutes, OT rate, OT formula version, OT multiplier, OT amount</li>
                    <li><strong>APIT:</strong> Tax year, Tax rule version, Taxable amount, Tax amount</li>
                    <li><strong>EPF:</strong> Employee rate (currently 8%), Employer rate (currently 12%)</li>
                    <li><strong>ETF:</strong> Rate (currently 3%)</li>
                    <li><strong>No-Pay:</strong> Divisor, Days, Amount</li>
                </ul>
                <p class="text-danger small mb-0"><i class="fas fa-exclamation-triangle"></i> Database schema has not been modified yet pending these decisions.</p>
            </div>
        </div>
        
        <div class="d-flex justify-content-end mb-5">
            <button type="button" class="btn btn-success btn-lg" onclick="alert('Configuration captured for design Phase 4B. Implementation pending approval.')">Submit Business Decisions</button>
        </div>
    </form>
</div>
@endsection
