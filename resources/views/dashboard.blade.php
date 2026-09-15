@extends('layouts.app')

@section('content')
{{-- =====================================================================
     Enterprise AMS Dashboard — Modern SaaS Design System
     Palette: Primary #FF4916, Secondary #162029, Canvas #F8FAFC
     ===================================================================== --}}

<style>
    /* ── Dashboard-Specific Styling Tokens ── */
    :root {
        --dash-primary: #FF4916;
        --dash-primary-hover: #E03E0F;
        --dash-primary-subtle: rgba(255, 73, 22, 0.08);
        --dash-primary-glow: rgba(255, 73, 22, 0.22);
        --dash-secondary: #162029;
        --dash-secondary-light: #243442;
        --dash-border: #E2E8F0;
        --dash-card-bg: #FFFFFF;
        --dash-canvas: #F8FAFC;
    }

    /* Welcome Header */
    .dashboard-header {
        background: #FFFFFF;
        border: 1px solid var(--dash-border);
        border-radius: 16px;
        padding: 24px 28px;
        margin-bottom: 24px;
        box-shadow: 0 1px 3px 0 rgba(16, 24, 40, 0.05);
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 16px;
    }

    .header-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: rgba(255, 73, 22, 0.08);
        color: var(--dash-primary);
        border: 1px solid rgba(255, 73, 22, 0.2);
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .header-badge-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: var(--dash-primary);
        box-shadow: 0 0 6px var(--dash-primary);
    }

    .header-title {
        font-family: var(--font-family-display);
        font-weight: 800;
        font-size: 1.6rem;
        letter-spacing: -0.02em;
        color: var(--dash-secondary);
        margin: 6px 0 2px 0;
    }

    .header-subtitle {
        color: #64748B;
        font-size: 0.88rem;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    /* Stat Cards */
    .stat-card-link {
        text-decoration: none;
        display: block;
        height: 100%;
    }
    .stat-card {
        background: #FFFFFF !important;
        border: 1px solid var(--dash-border) !important;
        border-radius: 16px !important;
        padding: 22px 20px !important;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        box-shadow: 0 1px 3px 0 rgba(16, 24, 40, 0.05) !important;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }
    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 24px -4px rgba(22, 32, 41, 0.08), 0 4px 6px -2px rgba(22, 32, 41, 0.04) !important;
        border-color: #CBD5E1 !important;
    }

    /* Top accent border on hover */
    .stat-card::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: transparent;
        transition: background 0.25s ease;
    }
    .stat-card.stat-present:hover::after  { background: #10B981; }
    .stat-card.stat-absent:hover::after   { background: #EF4444; }
    .stat-card.stat-late:hover::after     { background: #F59E0B; }
    .stat-card.stat-early:hover::after    { background: #0EA5E9; }
    .stat-card.stat-ot:hover::after       { background: var(--dash-primary); }

    .stat-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        flex-shrink: 0;
    }
    .stat-icon.present { background: rgba(16, 185, 129, 0.1); color: #10B981; }
    .stat-icon.absent  { background: rgba(239, 68, 68, 0.1);  color: #EF4444; }
    .stat-icon.late    { background: rgba(245, 158, 11, 0.1); color: #F59E0B; }
    .stat-icon.early   { background: rgba(14, 165, 233, 0.1); color: #0EA5E9; }
    .stat-icon.ot      { background: rgba(255, 73, 22, 0.12); color: var(--dash-primary); }

    .stat-number {
        font-family: var(--font-family-display);
        font-weight: 800;
        font-size: clamp(1.8rem, 3.5vw, 2.3rem);
        line-height: 1;
        letter-spacing: -0.03em;
        margin: 12px 0 6px 0;
    }
    .stat-number.present { color: #10B981; }
    .stat-number.absent  { color: #EF4444; }
    .stat-number.late    { color: #F59E0B; }
    .stat-number.early   { color: #0EA5E9; }
    .stat-number.ot      { color: var(--dash-primary); }

    .stat-label {
        font-size: 0.76rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        color: var(--dash-secondary);
    }
    .stat-sub {
        font-size: 0.78rem;
        color: #64748B;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    /* Section Cards */
    .saas-card {
        background: #FFFFFF !important;
        border: 1px solid var(--dash-border) !important;
        border-radius: 16px !important;
        padding: 24px !important;
        box-shadow: 0 1px 3px 0 rgba(16, 24, 40, 0.05) !important;
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    .saas-card-title {
        font-family: var(--font-family-display);
        font-weight: 700;
        font-size: 1.05rem;
        color: var(--dash-secondary);
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 20px;
    }

    /* Metric Mini Widgets */
    .metric-mini {
        background: #FFFFFF;
        border: 1px solid var(--dash-border);
        border-radius: 12px;
        padding: 16px;
        height: 100%;
        transition: all 0.2s ease;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .metric-mini:hover {
        border-color: rgba(255, 73, 22, 0.35);
        box-shadow: 0 4px 12px rgba(22, 32, 41, 0.04);
    }
    .metric-mini-label {
        font-size: 0.74rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #64748B;
        margin-bottom: 8px;
        display: block;
    }
    .metric-mini-val {
        font-family: var(--font-family-display);
        font-weight: 800;
        font-size: 1.25rem;
        color: var(--dash-secondary);
        display: flex;
        align-items: center;
        gap: 6px;
    }

    /* Communication Feed Items */
    .comm-log-box {
        border-left: 3px solid var(--dash-border);
        padding-left: 14px;
        margin-bottom: 18px;
        transition: border-color 0.2s;
    }
    .comm-log-box.accent-orange {
        border-left-color: var(--dash-primary);
    }
    .comm-log-box.accent-green {
        border-left-color: #10B981;
    }

    /* Security KPI Cards */
    .security-card {
        background: #FFFFFF;
        border: 1px solid var(--dash-border);
        border-radius: 12px;
        padding: 18px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        transition: all 0.2s ease;
    }
    .security-card:hover {
        border-color: rgba(255, 73, 22, 0.3);
        box-shadow: 0 4px 12px rgba(22, 32, 41, 0.04);
    }
    .security-card-num {
        font-family: var(--font-family-display);
        font-weight: 800;
        font-size: 1.5rem;
        color: var(--dash-secondary);
        line-height: 1;
        margin-top: 4px;
    }

    /* Badges & Highlights */
    .badge-orange {
        background: rgba(255, 73, 22, 0.1);
        color: var(--dash-primary);
        border: 1px solid rgba(255, 73, 22, 0.25);
        font-weight: 700;
    }
    .badge-navy {
        background: var(--dash-secondary);
        color: #FFFFFF;
        font-weight: 700;
    }
    .badge-online {
        background: rgba(16, 185, 129, 0.1);
        color: #10B981;
        border: 1px solid rgba(16, 185, 129, 0.25);
        font-weight: 600;
    }
    .badge-offline {
        background: rgba(239, 68, 68, 0.1);
        color: #EF4444;
        border: 1px solid rgba(239, 68, 68, 0.25);
        font-weight: 600;
    }
    .badge-pending {
        background: rgba(245, 158, 11, 0.1);
        color: #F59E0B;
        border: 1px solid rgba(245, 158, 11, 0.25);
        font-weight: 600;
    }

    /* Progress bar */
    .progress-bar-orange {
        background: linear-gradient(90deg, #FF7247, var(--dash-primary));
        border-radius: 4px;
    }

    /* Responsive */
    @media (max-width: 1440px) {
        .stat-number { font-size: clamp(1.4rem, 2.5vw, 1.8rem); }
        .saas-card { padding: 20px !important; }
        .metric-mini { padding: 12px; }
        .metric-mini-val { font-size: 1.15rem; }
        .security-card { padding: 14px 16px; }
    }
    @media (max-width: 1366px) {
        .dashboard-header { padding: 18px 22px; }
        .header-title { font-size: 1.4rem; }
        .stat-card { padding: 18px 16px !important; }
        .stat-icon { width: 40px; height: 40px; font-size: 1.1rem; }
    }
    @media (max-width: 767.98px) {
        .dashboard-header {
            padding: 16px 18px;
        }
        .saas-card {
            padding: 16px !important;
        }
        .stat-card {
            padding: 14px 12px !important;
        }
        .stat-icon {
            width: 36px;
            height: 36px;
            font-size: 1rem;
        }
        .header-title { font-size: 1.25rem; }
    }
</style>

{{-- ═══════════════════════════════════════════════════════════
     DASHBOARD GREETING & STATUS BANNER
     ═══════════════════════════════════════════════════════════ --}}
<div class="dashboard-header">
    <div>
        <div class="header-badge">
            <span class="header-badge-dot"></span>
            <span>Biometric ADMS Push Engine Active</span>
        </div>
        <h1 class="header-title">Workforce &amp; Attendance Console</h1>
        <p class="header-subtitle">
            <i class="bi bi-calendar3 text-secondary"></i>
            <span>{{ \Carbon\Carbon::now('Asia/Colombo')->format('l, d F Y') }}</span>
            <span class="text-muted">•</span>
            <i class="bi bi-geo-alt-fill" style="color: var(--dash-primary);"></i>
            <span>Asia/Colombo Timezone</span>
        </p>
    </div>

    <div class="d-flex align-items-center gap-2">
        <form action="{{ route('backups.trigger') }}" method="POST" class="m-0">
            @csrf
            <button type="submit" class="btn btn-custom-primary d-inline-flex align-items-center gap-2">
                <i class="bi bi-cloud-arrow-up-fill"></i>
                <span>Backup DB</span>
            </button>
        </form>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════
     ROW 1 — TODAY'S ATTENDANCE STAT CARDS (5 White Cards)
     ═══════════════════════════════════════════════════════════ --}}
<div class="row row-cols-2 row-cols-sm-3 row-cols-lg-5 row-cols-xl-5 g-3 g-md-4 mb-4">

    {{-- 1. Present Today --}}
    <div class="col">
        <a href="{{ route('daily-attendance.index', ['status' => 'PRESENT', 'date' => \Carbon\Carbon::today('Asia/Colombo')->toDateString()]) }}" class="stat-card-link">
            <div class="stat-card stat-present">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="stat-label">Present</span>
                    <div class="stat-icon present"><i class="bi bi-person-check-fill"></i></div>
                </div>
                <div class="stat-number present">{{ $presentToday }}</div>
                <div class="stat-sub">
                    <i class="bi bi-arrow-up-right text-success"></i>
                    <span>Active check-ins</span>
                </div>
            </div>
        </a>
    </div>

    {{-- 2. Absent Today --}}
    <div class="col">
        <a href="{{ route('daily-attendance.index', ['status' => 'ABSENT', 'date' => \Carbon\Carbon::today('Asia/Colombo')->toDateString()]) }}" class="stat-card-link">
            <div class="stat-card stat-absent">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="stat-label">Absent</span>
                    <div class="stat-icon absent"><i class="bi bi-person-x-fill"></i></div>
                </div>
                <div class="stat-number absent">{{ $absentToday }}</div>
                <div class="stat-sub">
                    <i class="bi bi-dash-circle text-danger"></i>
                    <span>No logs recorded</span>
                </div>
            </div>
        </a>
    </div>

    {{-- 3. Late Arrivals --}}
    <div class="col">
        <a href="{{ route('daily-attendance.index', ['flags' => ['late_in'], 'date' => \Carbon\Carbon::today('Asia/Colombo')->toDateString()]) }}" class="stat-card-link">
            <div class="stat-card stat-late">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="stat-label">Late In</span>
                    <div class="stat-icon late"><i class="bi bi-clock-fill"></i></div>
                </div>
                <div class="stat-number late">{{ $lateToday }}</div>
                <div class="stat-sub">
                    <i class="bi bi-exclamation-circle text-warning"></i>
                    <span>Over grace window</span>
                </div>
            </div>
        </a>
    </div>

    {{-- 4. Early Outs --}}
    <div class="col">
        <a href="{{ route('daily-attendance.index', ['flags' => ['early_out'], 'date' => \Carbon\Carbon::today('Asia/Colombo')->toDateString()]) }}" class="stat-card-link">
            <div class="stat-card stat-early">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="stat-label">Early Out</span>
                    <div class="stat-icon early"><i class="bi bi-box-arrow-left"></i></div>
                </div>
                <div class="stat-number early">{{ $earlyOutToday }}</div>
                <div class="stat-sub">
                    <i class="bi bi-arrow-left-circle text-info"></i>
                    <span>Before shift close</span>
                </div>
            </div>
        </a>
    </div>

    {{-- 5. Overtime Claims (Highlighted in Primary #FF4916) --}}
    <div class="col col-sm-12 col-md-auto col-xl">
        <a href="{{ route('daily-attendance.index', ['flags' => ['ot'], 'date' => \Carbon\Carbon::today('Asia/Colombo')->toDateString()]) }}" class="stat-card-link">
            <div class="stat-card stat-ot" style="border-color: rgba(255, 73, 22, 0.25) !important;">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="stat-label" style="color: var(--dash-primary);">Overtime</span>
                    <div class="stat-icon ot"><i class="bi bi-stopwatch-fill"></i></div>
                </div>
                <div class="stat-number ot">{{ $overtimeToday }}</div>
                <div class="stat-sub" style="color: var(--dash-primary); font-weight: 500;">
                    <i class="bi bi-lightning-charge-fill"></i>
                    <span>Eligible for OT</span>
                </div>
            </div>
        </a>
    </div>

</div>

{{-- ═══════════════════════════════════════════════════════════
     ROW 2 — SYSTEM HEALTH & COMMUNICATIONS
     ═══════════════════════════════════════════════════════════ --}}
<div class="row g-3 g-md-4 mb-4">

    {{-- System Health & Metrics (8/12 XL) --}}
    <div class="col-12 col-lg-7 col-xl-8">
        <div class="saas-card">
            <div class="saas-card-title">
                <div style="width: 28px; height: 28px; border-radius: 8px; background: rgba(255, 73, 22, 0.1); color: var(--dash-primary); display: flex; align-items: center; justify-content: center; font-size: 0.95rem;">
                    <i class="bi bi-heart-pulse-fill"></i>
                </div>
                <span>System Health &amp; Terminal Telemetry</span>
            </div>

            <div class="row row-cols-2 row-cols-md-3 g-3 flex-grow-1">

                <div class="col">
                    <div class="metric-mini">
                        <span class="metric-mini-label">Database Connection</span>
                        <div class="metric-mini-val text-success">
                            <i class="bi bi-database-check"></i>
                            <span>{{ $dbStatus }}</span>
                        </div>
                    </div>
                </div>

                <div class="col">
                    <div class="metric-mini">
                        <span class="metric-mini-label">Hardware Terminals</span>
                        <div class="d-flex flex-wrap gap-2 mt-1">
                            <span class="badge badge-online px-2.5 py-1.5 fs-8">{{ $onlineDevices }} Online</span>
                            <span class="badge badge-offline px-2.5 py-1.5 fs-8">{{ $offlineDevices }} Offline</span>
                        </div>
                    </div>
                </div>

                <div class="col">
                    <div class="metric-mini">
                        <span class="metric-mini-label">Last DB Snapshot</span>
                        <div class="metric-mini-val fs-6" style="color: var(--dash-primary);">
                            <i class="bi bi-cloud-check-fill"></i>
                            <span>
                                @if($lastBackup)
                                    {{ $lastBackup->backup_date->format('d M, H:i') }}
                                @else
                                    Never Backed Up
                                @endif
                            </span>
                        </div>
                    </div>
                </div>

                <div class="col">
                    <div class="metric-mini">
                        <span class="metric-mini-label">Today's Raw Punches</span>
                        <div class="metric-mini-val text-dark">
                            <i class="bi bi-fingerprint" style="color: var(--dash-primary);"></i>
                            <span>{{ $todayLogsCount }}</span>
                            <small class="text-muted fs-8 fw-normal">Logs</small>
                        </div>
                    </div>
                </div>

                <div class="col">
                    <div class="metric-mini">
                        <span class="metric-mini-label">Unassigned Punches</span>
                        <div class="metric-mini-val text-warning">
                            <i class="bi bi-question-diamond-fill"></i>
                            <span>{{ $unprocessedLogs }}</span>
                            <small class="text-muted fs-8 fw-normal">Pending</small>
                        </div>
                    </div>
                </div>

                <div class="col">
                    <div class="metric-mini">
                        <span class="metric-mini-label">Pending Reviews</span>
                        <div class="d-flex flex-wrap gap-2 mt-1">
                            <span class="badge badge-pending px-2.5 py-1.5 fs-8">{{ $pendingCorrections }} Requests</span>
                            <span class="badge badge-pending px-2.5 py-1.5 fs-8">{{ $pendingDevices }} Terminals</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- Communications Widget (4/12 XL) --}}
    <div class="col-12 col-lg-5 col-xl-4">
        <div class="saas-card">
            <div class="saas-card-title">
                <div style="width: 28px; height: 28px; border-radius: 8px; background: rgba(22, 32, 41, 0.08); color: var(--dash-secondary); display: flex; align-items: center; justify-content: center; font-size: 0.95rem;">
                    <i class="bi bi-activity"></i>
                </div>
                <span>Live Communications Feed</span>
            </div>

            <div class="flex-grow-1">
                <div class="comm-log-box accent-orange">
                    <span class="stat-label d-block mb-1" style="font-size: 0.72rem;">Last Device Event</span>
                    @if($lastComm)
                        <div class="fw-bold fs-7 text-dark">{{ $lastComm->event_type }}</div>
                        <div class="text-secondary fs-8 mt-1">
                            {{ $lastComm->created_at->diffForHumans() }} — {{ $lastComm->event_message }}
                        </div>
                    @else
                        <span class="text-muted fs-8">No terminal events logged</span>
                    @endif
                </div>

                <div class="comm-log-box accent-green">
                    <span class="stat-label d-block mb-1" style="font-size: 0.72rem;">Latest Biometric Punch</span>
                    @if($lastLog)
                        <div class="fw-bold fs-7 text-dark">
                            {{ $lastLog->employee ? $lastLog->employee->full_name : 'PIN ' . $lastLog->verify_code }}
                        </div>
                        <div class="text-secondary fs-8 mt-1">
                            {{ $lastLog->attendance_timestamp->diffForHumans() }} — {{ $lastLog->attendance_type }} via {{ $lastLog->verification_method }}
                        </div>
                    @else
                        <span class="text-muted fs-8">No attendance punches received</span>
                    @endif
                </div>
            </div>

            <div class="pt-3 mt-2 border-top" style="border-color: var(--dash-border) !important;">
                <div class="d-flex align-items-center justify-content-between">
                    <span class="fs-8 text-secondary">Device Protocol</span>
                    <span class="badge badge-navy px-2 py-1 fs-8">ADMS v2.4</span>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- ═══════════════════════════════════════════════════════════
     ROW 3 — SECURITY & PERMISSION TELEMETRY
     ═══════════════════════════════════════════════════════════ --}}
<div class="row g-3 g-md-4 mb-4">
    <div class="col-12">
        <div class="saas-card">
            <div class="saas-card-title mb-3">
                <div style="width: 28px; height: 28px; border-radius: 8px; background: rgba(22, 32, 41, 0.08); color: var(--dash-secondary); display: flex; align-items: center; justify-content: center; font-size: 0.95rem;">
                    <i class="bi bi-shield-lock-fill"></i>
                </div>
                <span>Security Governance &amp; RBAC Telemetry</span>
            </div>

            <div class="row row-cols-2 row-cols-sm-2 row-cols-xl-4 g-3">

                <div class="col">
                    <div class="security-card">
                        <div>
                            <span class="stat-label">Custom Overrides</span>
                            <div class="security-card-num text-warning">{{ $usersWithCustomPermsCount }}
                                <small class="text-muted fs-8 fw-normal">Users</small>
                            </div>
                        </div>
                        <i class="bi bi-sliders2 text-warning fs-3 opacity-75"></i>
                    </div>
                </div>

                <div class="col">
                    <div class="security-card" style="border-color: rgba(255, 73, 22, 0.25);">
                        <div>
                            <span class="stat-label" style="color: var(--dash-primary);">Admin Accounts</span>
                            <div class="security-card-num" style="color: var(--dash-primary);">{{ $adminUsersCount }}
                                <small class="text-muted fs-8 fw-normal">Privileged</small>
                            </div>
                        </div>
                        <i class="bi bi-shield-check fs-3" style="color: var(--dash-primary); opacity: 0.85;"></i>
                    </div>
                </div>

                <div class="col">
                    <div class="security-card">
                        <div>
                            <span class="stat-label">Updates Today</span>
                            <div class="security-card-num text-success">{{ $permissionChangesToday }}
                                <small class="text-muted fs-8 fw-normal">Audit Events</small>
                            </div>
                        </div>
                        <i class="bi bi-clock-history text-success fs-3 opacity-75"></i>
                    </div>
                </div>

                <div class="col">
                    <div class="security-card">
                        <div>
                            <span class="stat-label">Inactive Accounts</span>
                            <div class="security-card-num text-danger">{{ $lockedAccountsCount }}
                                <small class="text-muted fs-8 fw-normal">Restricted</small>
                            </div>
                        </div>
                        <i class="bi bi-lock-fill text-danger fs-3 opacity-75"></i>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════
     ROW 4 — MULTI-COMPANY & DEPARTMENT BREAKDOWN TABLES
     ═══════════════════════════════════════════════════════════ --}}
<div class="row g-3 g-md-4">

    {{-- Company Summary Table --}}
    <div class="col-12 col-xl-6">
        <div class="saas-card">
            <div class="saas-card-title mb-3">
                <div style="width: 28px; height: 28px; border-radius: 8px; background: rgba(255, 73, 22, 0.1); color: var(--dash-primary); display: flex; align-items: center; justify-content: center; font-size: 0.95rem;">
                    <i class="bi bi-building"></i>
                </div>
                <span>Company Attendance Overview</span>
            </div>

            <div class="table-responsive">
                <table class="table custom-table align-middle">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Company Entity</th>
                            <th class="text-center">Active Roster</th>
                            <th class="text-center">Present Ratio</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($companies as $c)
                            @php
                                $cEmps    = $c->employees()->where('status', 'Active')->count();
                                $cPresent = \App\Models\AttendanceLog::where('attendance_date', $today)
                                    ->whereHas('employee', fn($q) => $q->where('company_id', $c->id))
                                    ->distinct('employee_id')->count();
                                $cPct     = $cEmps > 0 ? round(($cPresent / $cEmps) * 100) : 0;
                            @endphp
                            <tr>
                                <td>
                                    <span class="badge badge-orange px-2.5 py-1 fs-8">{{ $c->company_code }}</span>
                                </td>
                                <td>
                                    <span class="fw-bold text-dark d-block text-truncate" style="max-width: 180px;" title="{{ $c->company_name }}">
                                        {{ $c->company_name }}
                                    </span>
                                </td>
                                <td class="text-center fw-semibold text-secondary">{{ $cEmps }}</td>
                                <td class="text-center" style="min-width: 140px;">
                                    <div class="d-flex align-items-center justify-content-center gap-2">
                                        <div class="progress flex-grow-1" style="height: 6px; background-color: #E2E8F0; border-radius: 4px;">
                                            <div class="progress-bar progress-bar-orange" style="width: {{ $cPct }}%;"></div>
                                        </div>
                                        <span class="fw-bold fs-8 text-dark" style="min-width: 36px;">{{ $cPct }}%</span>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">
                                    <i class="bi bi-building-slash d-block fs-3 mb-2 opacity-50"></i>
                                    No companies registered
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Department Summary Table --}}
    <div class="col-12 col-xl-6">
        <div class="saas-card">
            <div class="saas-card-title mb-3">
                <div style="width: 28px; height: 28px; border-radius: 8px; background: rgba(22, 32, 41, 0.08); color: var(--dash-secondary); display: flex; align-items: center; justify-content: center; font-size: 0.95rem;">
                    <i class="bi bi-briefcase-fill"></i>
                </div>
                <span>Department Attendance Overview</span>
            </div>

            <div class="table-responsive">
                <table class="table custom-table align-middle">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Department</th>
                            <th class="d-none d-sm-table-cell">Company</th>
                            <th class="text-center">Present / Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($departments as $d)
                            @php
                                $dEmps    = $d->employees()->where('status', 'Active')->count();
                                $dPresent = \App\Models\AttendanceLog::where('attendance_date', $today)
                                    ->whereHas('employee', fn($q) => $q->where('department_id', $d->id))
                                    ->distinct('employee_id')->count();
                                $dPct     = $dEmps > 0 ? round(($dPresent / $dEmps) * 100) : 0;
                            @endphp
                            <tr>
                                <td>
                                    <span class="badge badge-navy px-2.5 py-1 fs-8">{{ $d->department_code }}</span>
                                </td>
                                <td>
                                    <span class="fw-bold text-dark d-block text-truncate" style="max-width: 170px;" title="{{ $d->department_name }}">
                                        {{ $d->department_name }}
                                    </span>
                                </td>
                                <td class="text-muted fs-8 d-none d-sm-table-cell">{{ $d->company ? $d->company->company_name : '—' }}</td>
                                <td class="text-center">
                                    <span class="fw-bold" style="color: var(--dash-primary);">{{ $dPresent }}</span>
                                    <span class="text-muted fs-8">/ {{ $dEmps }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">
                                    <i class="bi bi-briefcase-fill d-block fs-3 mb-2 opacity-50"></i>
                                    No departments registered
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

@endsection
