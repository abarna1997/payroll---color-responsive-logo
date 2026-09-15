<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Portal - BioMetrics AMS</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4F46E5;
            --primary-hover: #4338CA;
            --secondary: #10B981;
            --dark: #0F172A;
            --dark-light: #1E293B;
            --light: #F8FAFC;
            --card-bg: rgba(255, 255, 255, 0.7);
            --border: rgba(255, 255, 255, 0.6);
            --text-main: #334155;
            --text-muted: #64748B;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: #f8fafc;
            background-image: 
                radial-gradient(at 15% 50%, rgba(99, 102, 241, 0.12) 0px, transparent 50%),
                radial-gradient(at 85% 30%, rgba(16, 185, 129, 0.08) 0px, transparent 50%),
                radial-gradient(at 50% 100%, rgba(59, 130, 246, 0.12) 0px, transparent 50%);
            background-attachment: fixed;
            margin: 0;
            padding: 0;
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
        }

        /* Glassmorphism Navbar */
        .navbar {
            background: rgba(255, 255, 255, 0.65);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.8);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.03);
        }

        .navbar-brand {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--primary);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-shrink: 0;
        }

        .navbar-nav {
            display: flex;
            gap: 1.5rem;
            list-style: none;
            margin: 0;
            padding: 0;
            align-items: center;
            flex-grow: 1;
            justify-content: center;
        }

        .nav-item a {
            text-decoration: none;
            color: var(--text-muted);
            font-weight: 600;
            transition: all 0.3s ease;
            padding: 0.5rem 1.25rem;
            border-radius: 12px;
            font-size: 0.95rem;
        }

        .nav-item a:hover {
            color: var(--primary);
            background: rgba(79, 70, 229, 0.08);
            transform: translateY(-1px);
        }
        
        .nav-item a.active {
            background: var(--primary);
            color: white;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25);
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 1rem;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(8px);
            padding: 0.4rem 1rem;
            border-radius: 50px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.03);
            border: 1px solid var(--border);
            transition: all 0.3s ease;
            flex-shrink: 0;
        }
        
        .user-profile:hover {
            box-shadow: 0 8px 25px rgba(0,0,0,0.06);
            transform: translateY(-1px);
        }
        
        .user-avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary) 0%, #818CF8 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.1rem;
            box-shadow: 0 4px 10px rgba(79, 70, 229, 0.3);
        }

        /* Layout Grid */
        .container {
            max-width: 1280px;
            margin: 2.5rem auto;
            padding: 0 1.5rem;
            flex-grow: 1;
            width: 100%;
            box-sizing: border-box;
            animation: fadeUp 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Glassmorphism Cards */
        .card {
            background: var(--card-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 1.75rem;
            box-shadow: 0 10px 40px -10px rgba(0, 0, 0, 0.05);
            margin-bottom: 1.5rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.08);
            background: rgba(255, 255, 255, 0.85);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid rgba(226, 232, 240, 0.6);
        }
        
        .card-title {
            margin: 0;
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.6rem 1.4rem;
            border-radius: 12px;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: none;
            text-decoration: none;
            font-size: 0.95rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, #6366F1 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(79, 70, 229, 0.3);
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, var(--primary-hover) 0%, var(--primary) 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(79, 70, 229, 0.4);
            color: white;
        }

        .btn-outline {
            background: rgba(255, 255, 255, 0.5);
            color: var(--primary);
            border: 1.5px solid var(--primary);
            backdrop-filter: blur(4px);
        }
        
        .btn-outline:hover {
            background: var(--primary);
            color: white;
            box-shadow: 0 4px 15px rgba(79, 70, 229, 0.25);
            transform: translateY(-2px);
        }

        /* Tables */
        .table-responsive {
            overflow-x: auto;
            border-radius: 16px;
        }
        
        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 0.4rem;
            margin-bottom: 0.5rem;
        }

        th {
            background: transparent;
            padding: 1rem 1.25rem;
            text-align: left;
            font-weight: 700;
            color: var(--text-muted);
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            border: none;
        }

        td {
            padding: 1.25rem;
            background: rgba(255, 255, 255, 0.5);
            backdrop-filter: blur(8px);
            color: var(--text-main);
            vertical-align: middle;
            border: none;
            border-top: 1px solid rgba(255,255,255,0.7);
            border-bottom: 1px solid rgba(255,255,255,0.7);
            transition: all 0.2s ease;
        }

        td:first-child {
            border-left: 1px solid rgba(255,255,255,0.7);
            border-top-left-radius: 14px;
            border-bottom-left-radius: 14px;
        }

        td:last-child {
            border-right: 1px solid rgba(255,255,255,0.7);
            border-top-right-radius: 14px;
            border-bottom-right-radius: 14px;
        }

        tr:hover td {
            background: rgba(255, 255, 255, 0.9);
            transform: scale(1.005);
            box-shadow: 0 4px 15px rgba(0,0,0,0.02);
            position: relative;
            z-index: 10;
        }

        /* Badges */
        .badge {
            padding: 0.4rem 0.8rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }

        .badge-success { background: linear-gradient(135deg, #D1FAE5 0%, #A7F3D0 100%); color: #065F46; border: 1px solid rgba(167, 243, 208, 0.5); }
        .badge-warning { background: linear-gradient(135deg, #FEF3C7 0%, #FDE68A 100%); color: #92400E; border: 1px solid rgba(253, 230, 138, 0.5); }
        .badge-danger { background: linear-gradient(135deg, #FEE2E2 0%, #FECACA 100%); color: #991B1B; border: 1px solid rgba(254, 202, 202, 0.5); }
        .badge-info { background: linear-gradient(135deg, #DBEAFE 0%, #BFDBFE 100%); color: #1E40AF; border: 1px solid rgba(191, 219, 254, 0.5); }

        /* Form elements */
        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--text-main);
        }

        .form-control {
            width: 100%;
            padding: 0.8rem 1.2rem;
            border: 1px solid rgba(226, 232, 240, 0.8);
            border-radius: 12px;
            font-family: inherit;
            transition: all 0.3s;
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(8px);
            box-sizing: border-box;
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.01);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.15);
            background: rgba(255, 255, 255, 0.95);
        }
        
        .alert {
            padding: 1.2rem 1.5rem;
            border-radius: 16px;
            margin-bottom: 1.5rem;
            font-weight: 600;
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.03);
            display: flex;
            align-items: center;
            gap: 0.8rem;
        }
        
        .alert-success { background: rgba(209, 250, 229, 0.9); color: #065F46; border: 1px solid rgba(167, 243, 208, 0.8); }
        .alert-error { background: rgba(254, 226, 226, 0.9); color: #991B1B; border: 1px solid rgba(254, 202, 202, 0.8); }

        /* Dashboard Grid Layout */
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 16px;
            background: linear-gradient(135deg, var(--primary) 0%, #818CF8 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            box-shadow: 0 8px 16px rgba(79, 70, 229, 0.2);
        }

        .stat-content h3 {
            margin: 0 0 0.25rem 0;
            font-size: 0.9rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .stat-content p {
            margin: 0;
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--dark);
        }
        
        /* Logout Button */
        .btn-logout {
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            font-weight: 500;
            font-family: inherit;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem;
            transition: color 0.3s;
        }
        
        .btn-logout:hover {
            color: var(--primary);
        }
    </style>
    @yield('styles')
</head>
<body>

    <nav class="navbar">
        <a href="{{ route('portal.dashboard') }}" class="navbar-brand">
            <i class="fa-solid fa-fingerprint"></i> BioMetrics <span style="font-size:0.9rem;color:var(--text-muted);font-weight:500;">Portal</span>
        </a>
        
        <ul class="navbar-nav">
            <li class="nav-item"><a href="{{ route('portal.dashboard') }}" class="{{ request()->routeIs('portal.dashboard') ? 'active' : '' }}">Dashboard</a></li>
            <li class="nav-item"><a href="{{ route('portal.attendance.index') }}" class="{{ request()->routeIs('portal.attendance.*') ? 'active' : '' }}">Attendance</a></li>
            <li class="nav-item"><a href="{{ route('portal.wfh.index') }}" class="{{ request()->routeIs('portal.wfh.*') ? 'active' : '' }}">WFH Requests</a></li>
            <li class="nav-item"><a href="{{ route('portal.leave.index') }}" class="{{ request()->routeIs('portal.leave.*') ? 'active' : '' }}">Leaves</a></li>
            <li class="nav-item"><a href="{{ route('portal.punch.index') }}" class="{{ request()->routeIs('portal.punch.*') ? 'active' : '' }}">Web Punch</a></li>
            <li class="nav-item"><a href="{{ route('portal.payslips.index') }}" class="{{ request()->routeIs('portal.payslips.*') ? 'active' : '' }}">Payslips</a></li>
        </ul>

        <div style="display: flex; align-items: center; gap: 1rem;">
            @if(Auth::user()->hasPermissionTo('dashboard.view'))
                <a href="{{ route('dashboard') }}" class="btn btn-outline" style="padding: 0.4rem 1rem; border-radius: 50px; font-size: 0.85rem; border-width: 1.5px;">
                    <i class="fa-solid fa-gauge" style="margin-right: 0.4rem;"></i> Admin Console
                </a>
            @endif
            <div class="user-profile">
                <a href="{{ route('portal.profile.index') }}" style="display: flex; align-items: center; text-decoration: none; color: inherit; gap: 0.5rem; transition: color 0.3s;" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='inherit'">
                    <div class="user-avatar">
                        {{ substr(Auth::user()->employee->first_name, 0, 1) }}
                    </div>
                    <div style="line-height: 1.2;">
                        <div style="font-weight: 600; font-size: 0.9rem;">{{ Auth::user()->employee->first_name }} {{ Auth::user()->employee->last_name }}</div>
                        <div style="font-size: 0.75rem; color: var(--text-muted);">{{ Auth::user()->employee->employee_id }}</div>
                    </div>
                </a>
                <div style="width: 1px; height: 30px; background: var(--border); margin: 0 0.5rem;"></div>
                <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
                    @csrf
                    <button type="submit" class="btn-logout" title="Logout"><i class="fa-solid fa-right-from-bracket"></i></button>
                </form>
            </div>
        </div>
    </nav>

    <div class="container">
        @if (session('success'))
            <div class="alert alert-success">
                <i class="fa-solid fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif
        
        @if (session('error'))
            <div class="alert alert-error">
                <i class="fa-solid fa-triangle-exclamation"></i> {{ session('error') }}
            </div>
        @endif

        @yield('content')
    </div>

    @yield('scripts')
</body>
</html>
