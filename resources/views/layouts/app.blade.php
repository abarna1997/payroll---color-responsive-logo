<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AMS - Enterprise Admin Console</title>
    
    <!-- PWA Setup -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#4F46E5">
    <link rel="apple-touch-icon" href="/favicon.ico">
    <script>
      if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
          navigator.serviceWorker.register('/sw.js');
        });
      }
    </script>
    
    <!-- Google Fonts: Inter & Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        /* ══════════════════════════════════════════════
           Design Tokens
        ══════════════════════════════════════════════ */
        :root {
            --primary:          #FF4916;
            --primary-hover:    #E03E0F;
            --primary-light:    rgba(255, 73, 22, 0.08);
            --primary-subtle:   rgba(255, 73, 22, 0.12);
            --primary-glow:     rgba(255, 73, 22, 0.25);
            --secondary:        #162029;
            --secondary-light:  #243442;
            --secondary-hover:  #1e2c38;
            --dark:             #162029;
            --dark-light:       #243442;
            --light:            #F8FAFC;
            --canvas-bg:        #F8FAFC;
            --card-bg:          #FFFFFF;
            --border:           #E2E8F0;
            --border-color:     #E2E8F0;
            --text-main:        #162029;
            --text-secondary:   #64748B;
            --text-muted:       #64748B;
            --indigo:           #FF4916;
            --indigo-light:     rgba(255, 73, 22, 0.08);
            --font-family-sans:    'Inter', system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
            --font-family-display: 'Plus Jakarta Sans', 'Inter', system-ui, sans-serif;
        }

        *, *::before, *::after { box-sizing: border-box; }

        body {
            font-family: var(--font-family-sans);
            background-color: var(--canvas-bg);
            background-image: 
                radial-gradient(at 95% 5%,  rgba(255, 73, 22, 0.035) 0px, transparent 45%),
                radial-gradient(at 5%  95%, rgba(22, 32, 41, 0.03)   0px, transparent 45%);
            margin: 0;
            padding: 0;
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }

        h1, h2, h3, h4, h5, h6, .display-font {
            font-family: var(--font-family-display);
            font-weight: 700;
            color: var(--secondary);
            letter-spacing: -0.02em;
        }

        /* ══════════════════════════════════════════════
           Top Navbar
        ══════════════════════════════════════════════ */
        .navbar-top {
            background: #FFFFFF;
            border-bottom: 1px solid var(--border);
            padding: 0 1.5rem;
            height: 56px;
            position: sticky;
            top: 0;
            z-index: 1040;
            box-shadow: 0 1px 3px 0 rgba(16, 24, 40, 0.04);
        }

        /* Brand */
        .navbar-brand {
            font-family: var(--font-family-display);
            font-size: 1.1rem;
            font-weight: 800;
            color: var(--secondary);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            letter-spacing: -0.02em;
            flex-shrink: 0;
        }
        .navbar-brand:hover { color: var(--secondary); }
        .navbar-brand img {
            height: 32px;
            max-width: 140px;
            object-fit: contain;
        }

        /* ── Primary nav links (visible inline) ── */
        .nav-primary { flex-wrap: nowrap; }

        .nav-link {
            color: #475569;
            font-weight: 500;
            transition: all 0.2s ease;
            padding: 0.38rem 0.6rem !important;
            border-radius: 7px;
            font-family: var(--font-family-display);
            font-size: 0.81rem;
            white-space: nowrap;
            line-height: 1;
        }
        .nav-link:hover {
            background: rgba(255, 73, 22, 0.06);
            color: var(--primary);
        }
        .nav-link.active {
            background: var(--primary);
            color: #FFFFFF !important;
            font-weight: 600;
            box-shadow: 0 2px 8px rgba(255, 73, 22, 0.3);
        }

        /* ── Dropdown menus (primary + More) ── */
        .dropdown-menu {
            background: #FFFFFF;
            border: 1px solid var(--border);
            border-radius: 12px;
            box-shadow: 0 10px 25px -5px rgba(22, 32, 41, 0.1), 0 4px 6px -2px rgba(22, 32, 41, 0.05);
            padding: 0.5rem;
            min-width: 210px;
            animation: dropIn 0.18s ease;
        }
        @keyframes dropIn {
            from { opacity: 0; transform: translateY(-6px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .dropdown-item {
            border-radius: 6px;
            padding: 0.5rem 0.9rem;
            font-weight: 500;
            color: var(--text-main);
            transition: all 0.15s ease;
            font-size: 0.84rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .dropdown-item:hover {
            background: var(--primary-light);
            color: var(--primary);
        }
        .dropdown-item.active,
        .dropdown-item:active {
            background: var(--primary-light) !important;
            color: var(--primary) !important;
            font-weight: 600;
        }
        .dropdown-divider { margin: 0.35rem 0; border-color: var(--border); }

        /* ── More hamburger button ── */
        #moreMenuBtn {
            display: flex;
            align-items: center;
            gap: 0.35rem;
            background: #F1F5F9;
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 0.38rem 0.65rem;
            font-size: 0.81rem;
            font-weight: 600;
            font-family: var(--font-family-display);
            color: var(--secondary);
            cursor: pointer;
            transition: all 0.2s ease;
            flex-shrink: 0;
        }
        #moreMenuBtn:hover {
            background: var(--primary-light);
            border-color: rgba(255, 73, 22, 0.3);
            color: var(--primary);
        }
        #moreMenuBtn .bi-list { font-size: 1.05rem; }

        /* Section header inside dropdown */
        .dropdown-section-label {
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: #94A3B8;
            padding: 0.35rem 0.9rem 0.2rem;
        }

        /* ── Right-side action area ── */
        .navbar-actions {
            display: flex;
            align-items: center;
            gap: 0.45rem;
            flex-shrink: 0;
        }

        /* User profile pill */
        .user-profile {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: #FFFFFF;
            padding: 0.25rem 0.65rem;
            border-radius: 50px;
            box-shadow: 0 1px 2px rgba(16, 24, 40, 0.05);
            border: 1px solid var(--border);
            flex-shrink: 0;
            white-space: nowrap;
        }
        .user-avatar {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: var(--primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.76rem;
            flex-shrink: 0;
            box-shadow: 0 2px 6px rgba(255, 73, 22, 0.3);
        }
        .user-info { line-height: 1.2; }
        .user-info .user-name  { font-weight: 600; font-size: 0.82rem; color: var(--text-main); }
        .user-info .user-role  { font-size: 0.7rem;  color: var(--text-muted); }
        .user-divider {
            width: 1px; height: 26px;
            background: var(--border);
            margin: 0 0.2rem;
            flex-shrink: 0;
        }
        .btn-logout {
            background: none; border: none;
            color: var(--text-muted);
            cursor: pointer;
            transition: color 0.2s;
            padding: 0;
            display: flex; align-items: center;
        }
        .btn-logout:hover { color: #DC2626; }

        /* ── Navbar toggler (mobile collapse) ── */
        .navbar-toggler {
            border: none !important;
            box-shadow: none !important;
            padding: 0.3rem 0.4rem;
        }
        .navbar-toggler-icon {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='%23162029' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
        }

        /* ══════════════════════════════════════════════
           Main content container
        ══════════════════════════════════════════════ */
        .main-container {
            max-width: 1600px;
            margin: 1.5rem auto;
            padding: 0 1.25rem;
            flex-grow: 1;
            width: 100%;
        }

        /* ══════════════════════════════════════════════
           Cards
        ══════════════════════════════════════════════ */
        .glass-card, .card {
            background: #FFFFFF !important;
            border: 1px solid var(--border) !important;
            border-radius: 16px !important;
            padding: 1.5rem;
            box-shadow: 0 1px 3px 0 rgba(16, 24, 40, 0.06), 0 1px 2px -1px rgba(16, 24, 40, 0.06) !important;
            margin-bottom: 1.5rem;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .glass-card-hover:hover, .glass-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 24px -4px rgba(22, 32, 41, 0.08), 0 4px 6px -2px rgba(22, 32, 41, 0.04) !important;
            border-color: #CBD5E1 !important;
        }

        /* ══════════════════════════════════════════════
           Colour helpers
        ══════════════════════════════════════════════ */
        .text-indigo        { color: var(--primary) !important; }
        .bg-indigo          { background-color: var(--primary) !important; color: white !important; }
        .bg-indigo-light    { background-color: var(--primary-light) !important; }
        .bg-indigo-subtle   { background-color: var(--primary-light) !important; }

        /* ══════════════════════════════════════════════
           Buttons
        ══════════════════════════════════════════════ */
        .btn-custom-primary, .btn-primary, .btn-indigo {
            background: var(--primary) !important;
            border-color: var(--primary) !important;
            color: #FFFFFF !important;
            border-radius: 8px !important;
            padding: 0.55rem 1.15rem !important;
            font-weight: 600 !important;
            font-family: var(--font-family-display) !important;
            transition: all 0.2s ease !important;
            box-shadow: 0 2px 6px rgba(255, 73, 22, 0.2) !important;
        }
        .btn-custom-primary:hover, .btn-primary:hover, .btn-indigo:hover {
            background: var(--primary-hover) !important;
            border-color: var(--primary-hover) !important;
            transform: translateY(-1px) !important;
            box-shadow: 0 6px 16px rgba(255, 73, 22, 0.35) !important;
            color: #FFFFFF !important;
        }
        .btn-outline-primary {
            border-color: var(--primary) !important;
            color: var(--primary) !important;
            border-radius: 8px !important;
            font-weight: 600 !important;
        }
        .btn-outline-primary:hover {
            background: var(--primary) !important;
            color: #FFFFFF !important;
            box-shadow: 0 4px 12px rgba(255, 73, 22, 0.25) !important;
        }
        .btn-custom-secondary {
            background: #FFFFFF;
            color: var(--secondary);
            border: 1px solid var(--border);
            border-radius: 8px;
            font-weight: 600;
        }
        .btn-custom-secondary:hover { background: #F8FAFC; border-color: #CBD5E1; color: var(--secondary); }

        /* ══════════════════════════════════════════════
           Form Controls
        ══════════════════════════════════════════════ */
        .form-control-custom, .form-control, .form-select {
            background-color: #FFFFFF !important;
            border: 1px solid var(--border) !important;
            border-radius: 8px !important;
            padding: 0.6rem 1rem !important;
            font-size: 0.95rem;
            color: var(--text-main) !important;
        }
        .form-control:focus, .form-select:focus, .form-control-custom:focus {
            border-color: var(--primary) !important;
            box-shadow: 0 0 0 3px rgba(255, 73, 22, 0.15) !important;
        }

        /* ══════════════════════════════════════════════
           Tables
        ══════════════════════════════════════════════ */
        .table-responsive {
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid var(--border);
        }
        .custom-table { width: 100% !important; margin-bottom: 0 !important; }
        .custom-table th {
            background: #F8FAFC !important;
            padding: 0.85rem 1.25rem !important;
            font-family: var(--font-family-display) !important;
            font-weight: 700 !important;
            color: var(--secondary) !important;
            font-size: 0.75rem !important;
            text-transform: uppercase !important;
            letter-spacing: 0.6px !important;
            border-bottom: 1px solid var(--border) !important;
        }
        .custom-table td {
            background: #FFFFFF !important;
            padding: 1.1rem 1.25rem !important;
            border-bottom: 1px solid var(--border) !important;
            vertical-align: middle !important;
            color: var(--text-main) !important;
            font-size: 0.88rem !important;
        }
        .custom-table tr:last-child td { border-bottom: none !important; }
        .custom-table tr:hover td     { background: #F8FAFC !important; }

        /* ══════════════════════════════════════════════
           Utilities & Badges
        ══════════════════════════════════════════════ */
        .fs-7 { font-size: 0.9rem; }
        .fs-8 { font-size: 0.8rem; }
        .fs-9 { font-size: 0.75rem; }

        .badge-primary, .bg-primary, .bg-indigo { background-color: var(--primary) !important; color: #FFFFFF !important; }
        .badge-subtle-primary { background-color: var(--primary-light) !important; color: var(--primary) !important; font-weight: 600; }
        .badge-secondary { background-color: var(--secondary) !important; color: #FFFFFF !important; }

        /* ══════════════════════════════════════════════
           Animations
        ══════════════════════════════════════════════ */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(16px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-up { animation: fadeUp 0.4s cubic-bezier(0.4, 0, 0.2, 1) forwards; opacity: 0; }
        .delay-100 { animation-delay: 80ms; }
        .delay-200 { animation-delay: 160ms; }
        .delay-300 { animation-delay: 240ms; }

        /* ══════════════════════════════════════════════
           Overlays & Toasts
        ══════════════════════════════════════════════ */
        #ams-overlay {
            display: none; position: fixed; inset: 0;
            background: rgba(22, 32, 41, 0.6);
            z-index: 2000; align-items: center; justify-content: center;
            backdrop-filter: blur(4px);
        }
        .toast-container { position: fixed; top: 1rem; right: 1rem; z-index: 2001; }

        /* ══════════════════════════════════════════════
           RESPONSIVE BREAKPOINTS
        ══════════════════════════════════════════════ */

        /* ── ≥ 1400px: extra-large desktops (default) ── */

        /* ── 1200-1399px: standard desktops / laptops ── */
        @media (max-width: 1399px) {
            .navbar-top  { padding: 0 1.1rem; }
            .nav-link    { padding: 0.35rem 0.52rem !important; font-size: 0.79rem; }
            .main-container { padding: 0 1.25rem; margin: 1.25rem auto; }
        }

        /* ── 992-1199px: laptops / small desktops ── */
        @media (max-width: 1199px) {
            .navbar-top  { padding: 0 0.9rem; }
            .nav-link    { padding: 0.3rem 0.42rem !important; font-size: 0.77rem; }
            .user-info   { display: none; }
            .user-divider{ display: none; }
            .user-profile{ gap: 0.3rem; padding: 0.25rem 0.45rem; }
            .navbar-brand img { height: 28px; max-width: 110px; }
            .main-container { padding: 0 1rem; }
        }

        /* ── 768-991px: tablet landscape ── */
        @media (max-width: 991.98px) {
            /* Collapse primary nav into toggler; show More menu inside collapse */
            .navbar-top { height: auto; padding: 0.45rem 1rem; }
            .navbar-brand img { height: 28px; }
            .nav-primary { flex-wrap: wrap !important; }
            .nav-link    { font-size: 0.87rem; padding: 0.42rem 0.7rem !important; }
            .user-info   { display: flex; flex-direction: column; }
            .user-divider{ display: block; }
            #adminNavbar.show {
                padding: 0.5rem 0;
                border-top: 1px solid var(--border);
                margin-top: 0.4rem;
            }
            /* Keep More button inside collapsed menu */
            .navbar-actions { flex-wrap: wrap; gap: 0.4rem; }
            .main-container { margin: 1rem auto; padding: 0 0.75rem; }
        }

        /* ── ≤ 767px: tablets portrait ── */
        @media (max-width: 767.98px) {
            .navbar-top { padding: 0.4rem 0.85rem; }
            .navbar-brand img { height: 26px; max-width: 115px; }
            .main-container { padding: 0 0.65rem; }
        }

        /* ── ≤ 575px: mobile phones ── */
        @media (max-width: 575.98px) {
            .navbar-top { padding: 0.35rem 0.6rem; }
            .navbar-brand img { height: 24px; max-width: 95px; }
            .main-container { padding: 0 0.5rem; }
            .user-profile { padding: 0.22rem 0.4rem; }
        }
    </style>
</head>
<body>

    <!-- ══════════════════════════════════════════════
         TOP NAVIGATION BAR
    ══════════════════════════════════════════════ -->
    <nav class="navbar navbar-expand-lg navbar-top">
        <div class="container-fluid px-0 d-flex align-items-center gap-2">

            <!-- Brand Logo -->
            <a class="navbar-brand me-2" href="{{ route('dashboard') }}">
                <img src="{{ asset('images/company_logo.png') }}" alt="Company Logo">
            </a>

            <!-- Mobile collapse toggler -->
            <button class="navbar-toggler ms-auto order-lg-last" type="button"
                    data-bs-toggle="collapse" data-bs-target="#adminNavbar"
                    aria-controls="adminNavbar" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- ── Collapsible section ── -->
            <div class="collapse navbar-collapse" id="adminNavbar">

                <!-- PRIMARY NAV: Dashboard → Payroll (always visible as inline links) -->
                <ul class="navbar-nav nav-primary me-auto mb-2 mb-lg-0 ms-1 gap-1">

                    @inject('menuService', 'App\Services\MenuService')
                    @php
                        $adminMenus = $menuService->buildSidebar(Auth::user());

                        /* Titles that stay visible inline in the primary nav */
                        $primaryTitles = [
                            'Dashboard',
                            'Organization',
                            'Workforce',
                            'Attendance',
                            'Shift Management',
                            'Leave Management',
                            'Payroll',
                        ];

                        /* Titles that go into the hamburger "More" menu */
                        $moreTitles = [
                            'Biometric Devices',
                            'Reports & Analytics',
                            'Administration',
                            'Admin Profile',
                        ];

                        $primaryMenus = $adminMenus->filter(fn($m) => in_array($m->title, $primaryTitles));
                        $moreMenus    = $adminMenus->filter(fn($m) => in_array($m->title, $moreTitles));
                        /* Any menu not explicitly in either list goes to primary by default */
                        $otherMenus   = $adminMenus->filter(fn($m) =>
                            !in_array($m->title, $primaryTitles) && !in_array($m->title, $moreTitles)
                        );
                    @endphp

                    @foreach($primaryMenus->merge($otherMenus) as $menu)
                        @if($menu->children->isEmpty())
                            <li class="nav-item">
                                <a class="nav-link {{ $menu->isActive() ? 'active' : '' }}"
                                   href="{{ $menu->url ? url($menu->url) : '#' }}">
                                    @if($menu->icon)<i class="bi {{ $menu->icon }} me-1"></i>@endif
                                    {{ $menu->title }}
                                </a>
                            </li>
                        @else
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle {{ $menu->hasActiveChild() ? 'active' : '' }}"
                                   href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    @if($menu->icon)<i class="bi {{ $menu->icon }} me-1"></i>@endif
                                    {{ $menu->title }}
                                </a>
                                <ul class="dropdown-menu">
                                    @foreach($menu->children as $child)
                                        @php $isDisabled = ($child->url === '#' || $child->badge_text === 'Soon'); @endphp
                                        <li>
                                            <a class="dropdown-item {{ $child->isActive() ? 'active text-indigo bg-indigo-light' : '' }} {{ $isDisabled ? 'disabled text-muted' : '' }}"
                                               href="{{ $isDisabled ? 'javascript:void(0)' : url($child->url) }}">
                                                @if($child->icon)<i class="bi {{ $child->icon }}"></i>@endif
                                                {{ $child->title }}
                                                @if($child->badge_text)
                                                    <span class="badge bg-primary ms-auto fs-9">{{ $child->badge_text }}</span>
                                                @endif
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </li>
                        @endif
                    @endforeach

                    <!-- ── "More ☰" dropdown for Biometric Devices, Reports, Administration, Admin Profile ── -->
                    @if($moreMenus->isNotEmpty())
                    <li class="nav-item dropdown">
                        <button class="nav-link" id="moreMenuBtn"
                                data-bs-toggle="dropdown" aria-expanded="false"
                                aria-label="More menu items">
                            <i class="bi bi-list"></i>
                            <span>More</span>
                            <i class="bi bi-chevron-down" style="font-size:0.65rem;"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" style="min-width:230px;" aria-labelledby="moreMenuBtn">
                            <li><div class="dropdown-section-label">Additional Modules</div></li>
                            @foreach($moreMenus as $menu)
                                @if($menu->children->isEmpty())
                                    <li>
                                        <a class="dropdown-item {{ $menu->isActive() ? 'active text-indigo bg-indigo-light' : '' }}"
                                           href="{{ $menu->url ? url($menu->url) : '#' }}">
                                            @if($menu->icon)<i class="bi {{ $menu->icon }}"></i>@endif
                                            {{ $menu->title }}
                                        </a>
                                    </li>
                                @else
                                    {{-- Submenu rendered as group header + flat items --}}
                                    <li><div class="dropdown-section-label mt-1">{{ $menu->title }}</div></li>
                                    @foreach($menu->children as $child)
                                        @php $isDisabled = ($child->url === '#' || $child->badge_text === 'Soon'); @endphp
                                        <li>
                                            <a class="dropdown-item {{ $child->isActive() ? 'active text-indigo bg-indigo-light' : '' }} {{ $isDisabled ? 'disabled text-muted' : '' }}"
                                               href="{{ $isDisabled ? 'javascript:void(0)' : url($child->url) }}">
                                                @if($child->icon)<i class="bi {{ $child->icon }}"></i>@endif
                                                {{ $child->title }}
                                                @if($child->badge_text)
                                                    <span class="badge bg-primary ms-auto fs-9">{{ $child->badge_text }}</span>
                                                @endif
                                            </a>
                                        </li>
                                    @endforeach
                                    <li><hr class="dropdown-divider"></li>
                                @endif
                            @endforeach
                        </ul>
                    </li>
                    @endif

                </ul><!-- /nav-primary -->

                <!-- ── Right-side actions ── -->
                <div class="navbar-actions ms-lg-2">
                    @if(Auth::user()->employee)
                        <a href="{{ route('portal.dashboard') }}"
                           class="btn btn-sm btn-outline-primary fw-semibold d-none d-lg-inline-flex"
                           title="Go to My Employee Portal">
                            <i class="bi bi-person-workspace me-1"></i> My Portal
                        </a>
                    @endif

                    <button class="btn btn-sm btn-light border" onclick="toggleFullScreen()"
                            id="fullscreenToggleBtn" title="Full Screen">
                        <i class="bi bi-arrows-fullscreen"></i>
                    </button>

                    <!-- User Profile Pill -->
                    <div class="user-profile">
                        <div class="user-avatar">
                            {{ strtoupper(substr(Auth::user()->username, 0, 2)) }}
                        </div>
                        <div class="user-info">
                            <div class="user-name">{{ Auth::user()->username }}</div>
                            <div class="user-role">{{ Auth::user()->role }}</div>
                        </div>
                        <div class="user-divider"></div>
                        <form action="{{ route('logout') }}" method="POST" class="m-0">
                            @csrf
                            <button type="submit" class="btn-logout" title="Logout">
                                <i class="bi bi-box-arrow-right fs-5"></i>
                            </button>
                        </form>
                    </div>
                </div>

            </div><!-- /collapse -->
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-container">
        <!-- Toast Container -->
        <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1080;"></div>
        
        <!-- Session Flash Alerts -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-4 shadow-sm" role="alert" style="border-radius: 12px;">
                <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                <div class="flex-grow-1">{{ session('success') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show d-flex align-items-start mb-4 shadow-sm" role="alert" style="border-radius: 12px;">
                <i class="bi bi-exclamation-triangle-fill me-2 fs-5 mt-1"></i>
                <div class="flex-grow-1" style="white-space: pre-line;">{{ session('error') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if(session('warning'))
            <div class="alert alert-warning alert-dismissible fade show d-flex align-items-center mb-4 shadow-sm" role="alert" style="border-radius: 12px;">
                <i class="bi bi-exclamation-circle-fill me-2 fs-5"></i>
                <div class="flex-grow-1" style="white-space: pre-line;">{{ session('warning') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if(session('info'))
            <div class="alert alert-info alert-dismissible fade show d-flex align-items-center mb-4 shadow-sm" role="alert" style="border-radius: 12px;">
                <i class="bi bi-info-circle-fill me-2 fs-5"></i>
                <div class="flex-grow-1">{{ session('info') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Overlay Loader -->
    <div id="ams-overlay">
        <div class="spinner-border text-light" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        /* ── Full Screen Toggle ── */
        function toggleFullScreen() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen().then(() => {
                    const btn = document.getElementById('fullscreenToggleBtn');
                    if (btn) btn.innerHTML = '<i class="bi bi-fullscreen-exit"></i>';
                });
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen().then(() => {
                        const btn = document.getElementById('fullscreenToggleBtn');
                        if (btn) btn.innerHTML = '<i class="bi bi-arrows-fullscreen"></i>';
                    });
                }
            }
        }

        /* ── Toast System ── */
        const AmsToast = {
            show: (msg, type = 'info') => {
                const container = document.querySelector('.toast-container') || document.body;
                const toastEl   = document.createElement('div');
                const isWarning = type === 'warning';
                const bgClass   = type === 'error'   ? 'bg-danger text-white'
                                : isWarning           ? 'bg-warning text-dark'
                                : type === 'success'  ? 'bg-success text-white'
                                :                       'bg-primary text-white';
                toastEl.className = `toast align-items-center ${bgClass} border-0 show mb-2 shadow`;
                toastEl.setAttribute('role', 'alert');
                toastEl.setAttribute('aria-live', 'assertive');
                toastEl.setAttribute('aria-atomic', 'true');
                toastEl.style.borderRadius = '10px';
                toastEl.innerHTML = `
                    <div class="d-flex">
                        <div class="toast-body" style="white-space:pre-line;font-size:0.9rem;">${msg}</div>
                        <button type="button" class="btn-close ${isWarning ? '' : 'btn-close-white'} me-2 m-auto"
                                data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>`;
                container.appendChild(toastEl);
                setTimeout(() => {
                    toastEl.classList.remove('show');
                    setTimeout(() => toastEl.remove(), 400);
                }, 8000);
            },
            success: (msg) => AmsToast.show(msg, 'success'),
            error:   (msg) => AmsToast.show(msg, 'error'),
            warning: (msg) => AmsToast.show(msg, 'warning'),
            info:    (msg) => AmsToast.show(msg, 'info')
        };

        const CSRF    = () => document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const Overlay = {
            show: () => { document.getElementById('ams-overlay').style.display = 'flex'; },
            hide: () => { document.getElementById('ams-overlay').style.display = 'none'; }
        };

        /* ── HTTP Helper ── */
        const AMSHttp = {
            async request(method, url, data = null, options = {}) {
                const { loadingMsg = 'Processing...', silent = false, toastSuccess = true } = options;
                if (!silent) Overlay.show(loadingMsg);

                const headers = {
                    'X-CSRF-TOKEN':     CSRF(),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept':           'application/json'
                };
                const fetchOptions = { method, headers };
                if (data) {
                    if (data instanceof FormData) {
                        fetchOptions.body = data;
                    } else {
                        headers['Content-Type'] = 'application/json';
                        fetchOptions.body = JSON.stringify(data);
                    }
                }

                try {
                    const response    = await fetch(url, fetchOptions);
                    let   responseData;
                    const contentType = response.headers.get('content-type');
                    if (contentType && contentType.includes('application/json')) {
                        responseData = await response.json();
                    } else {
                        responseData = await response.text();
                    }

                    if (!silent) Overlay.hide();

                    if (!response.ok) {
                        if (!silent) {
                            if (responseData?.errors) {
                                AmsToast.warning(Object.values(responseData.errors).flat().join('\n'));
                            } else if (responseData?.message) {
                                AmsToast.error(responseData.message);
                            }
                        }
                        return { ok: false, status: response.status, data: responseData };
                    }

                    if (!silent && toastSuccess && responseData?.message) {
                        AmsToast.success(responseData.message);
                    }
                    return { ok: true, status: response.status, data: responseData };
                } catch (err) {
                    if (!silent) { Overlay.hide(); AmsToast.error('Network error occurred.'); }
                    throw err;
                }
            }
        };

        /* ── AJAX Form Interceptor ── */
        document.addEventListener('submit', async function (e) {
            const form = e.target;
            if (form.dataset.ajax !== 'true') return;
            e.preventDefault();

            const method   = (form.method || 'POST').toUpperCase();
            const action   = form.action || window.location.href;
            const formData = new FormData(form);
            const loadMsg  = form.dataset.loadingMsg || 'Saving…';
            const redirect = form.dataset.redirect;

            const result = await AMSHttp.request(method, action, formData, { loadingMsg: loadMsg });
            if (result.ok) {
                if (redirect) window.location.href = redirect;
                else if (result.data?.redirect) window.location.href = result.data.redirect;
                if (form.dataset.resetOnSuccess === 'true') form.reset();
            }
        });

        /* ── Flash Messages on DOMContentLoaded ── */
        document.addEventListener('DOMContentLoaded', () => {
            const flashData = document.getElementById('ams-flash-data');
            if (flashData) {
                const error   = flashData.getAttribute('data-error');
                const success = flashData.getAttribute('data-success');
                const warning = flashData.getAttribute('data-warning');
                const info    = flashData.getAttribute('data-info');
                if (error)   AmsToast.error(error);
                if (success) AmsToast.success(success);
                if (warning) AmsToast.warning(warning);
                if (info)    AmsToast.info(info);
            }
        });
    </script>

    <div id="ams-flash-data" style="display:none;"
         data-success="{{ session('success') }}"
         data-error="{{ session('error') }}"
         data-warning="{{ session('warning') }}"
         data-info="{{ session('info') }}"
    ></div>

    @yield('scripts')
    @stack('scripts')
</body>
</html>
