<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enterprise App Launcher - WSO2 Single Sign-On</title>
    
    <!-- Google Fonts: Outfit & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --brand-orange: #f95716;
            --brand-orange-hover: #e04b0e;
            --brand-navy: #0b1329;
            --brand-card-bg: rgba(23, 32, 54, 0.7);
            --font-family-sans: 'Inter', sans-serif;
            --font-family-display: 'Outfit', sans-serif;
        }

        body {
            background-color: var(--brand-navy);
            background-image: 
                radial-gradient(circle at 10% 20%, rgba(249, 87, 22, 0.12) 0%, transparent 40%),
                radial-gradient(circle at 90% 80%, rgba(13, 110, 253, 0.12) 0%, transparent 40%);
            color: #f8fafc;
            font-family: var(--font-family-sans);
            min-height: 100vh;
            margin: 0;
            padding-bottom: 60px;
        }

        /* Top Navbar */
        .launcher-nav {
            background: rgba(11, 19, 41, 0.85);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            padding: 16px 32px;
        }

        .brand-logo {
            font-family: var(--font-family-display);
            font-weight: 800;
            font-size: 1.5rem;
            color: #ffffff;
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
        }

        .brand-icon-box {
            width: 42px;
            height: 42px;
            background: linear-gradient(135deg, var(--brand-orange), #ff804a);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            color: #ffffff;
            box-shadow: 0 4px 15px rgba(249, 87, 22, 0.4);
        }

        .user-badge-box {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .org-badge {
            background: rgba(249, 87, 22, 0.15);
            border: 1px solid rgba(249, 87, 22, 0.3);
            color: #ff9d76;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.82rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .role-badge {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.12);
            color: #cbd5e1;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.82rem;
            font-weight: 500;
        }

        /* Hero Section */
        .launcher-hero {
            padding: 40px 0 20px 0;
            text-align: center;
        }

        .hero-title {
            font-family: var(--font-family-display);
            font-weight: 800;
            font-size: 2.25rem;
            letter-spacing: -0.5px;
            margin-bottom: 8px;
        }

        .hero-subtitle {
            color: #94a3b8;
            font-size: 1rem;
            max-width: 540px;
            margin: 0 auto 30px auto;
        }

        /* Search Bar */
        .search-box-container {
            max-width: 520px;
            margin: 0 auto 32px auto;
            position: relative;
        }

        .search-input {
            background: rgba(255, 255, 255, 0.06) !important;
            border: 1px solid rgba(255, 255, 255, 0.12) !important;
            color: #ffffff !important;
            border-radius: 14px !important;
            padding: 14px 20px 14px 48px !important;
            font-size: 0.95rem;
            backdrop-filter: blur(8px);
            transition: all 0.2s ease;
        }

        .search-input:focus {
            border-color: var(--brand-orange) !important;
            box-shadow: 0 0 0 3px rgba(249, 87, 22, 0.2) !important;
            background: rgba(255, 255, 255, 0.09) !important;
        }

        .search-icon {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 1.1rem;
        }

        /* Category Filter Tabs */
        .category-tabs {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 40px;
            flex-wrap: wrap;
        }

        .tab-btn {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.08);
            color: #94a3b8;
            padding: 8px 20px;
            border-radius: 30px;
            font-size: 0.88rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .tab-btn:hover, .tab-btn.active {
            background: var(--brand-orange);
            border-color: var(--brand-orange);
            color: #ffffff;
            box-shadow: 0 4px 15px rgba(249, 87, 22, 0.3);
        }

        /* Application Tiles Grid */
        .apps-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 24px;
            max-width: 1140px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .app-card {
            background: var(--brand-card-bg);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 20px;
            padding: 24px;
            text-decoration: none;
            color: inherit;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .app-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, transparent, var(--brand-orange), transparent);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .app-card:hover {
            transform: translateY(-5px);
            border-color: rgba(249, 87, 22, 0.4);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.4), 0 0 20px rgba(249, 87, 22, 0.15);
            color: #ffffff;
        }

        .app-card:hover::before {
            opacity: 1;
        }

        .app-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
        }

        .app-icon {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.6rem;
            color: #ffffff;
        }

        .app-tag {
            font-size: 0.72rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 4px 10px;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.06);
            color: #cbd5e1;
        }

        .app-title {
            font-family: var(--font-family-display);
            font-weight: 700;
            font-size: 1.15rem;
            margin-bottom: 6px;
        }

        .app-description {
            color: #94a3b8;
            font-size: 0.85rem;
            line-height: 1.5;
            margin-bottom: 20px;
        }

        .app-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 0.82rem;
            font-weight: 600;
            color: var(--brand-orange);
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            padding-top: 14px;
            margin-top: auto;
        }

        .launch-icon {
            transition: transform 0.2s ease;
        }

        .app-card:hover .launch-icon {
            transform: translateX(4px);
        }
    </style>
</head>
<body>

    <!-- Top Navigation Bar -->
    <nav class="launcher-nav d-flex align-items-center justify-content-between">
        <a href="{{ route('apps.index') }}" class="brand-logo">
            <div class="brand-icon-box">
                <i class="bi bi-grid-3x3-gap-fill"></i>
            </div>
            <span>WSO2 Enterprise Hub</span>
        </a>

        <div class="user-badge-box">
            @if ($employee && $employee->company)
                <div class="org-badge">
                    <i class="bi bi-building"></i>
                    <span>{{ $employee->company->company_name }}</span>
                </div>
            @endif

            <div class="role-badge">
                <i class="bi bi-shield-lock me-1"></i>
                <span>{{ $user ? $user->role : 'Employee' }}</span>
            </div>

            <div class="dropdown">
                <button class="btn btn-dark dropdown-toggle d-flex align-items-center gap-2 rounded-3 border-secondary" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-person-circle fs-5"></i>
                    <span>{{ $user ? $user->username : 'User' }}</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end shadow">
                    @if($user && $user->hasPermissionTo('dashboard.view'))
                    <li><a class="dropdown-item" href="{{ route('dashboard') }}"><i class="bi bi-speedometer2 me-2"></i>AMS Dashboard</a></li>
                    @endif
                    @if($employee)
                    <li><a class="dropdown-item" href="{{ route('portal.dashboard') }}"><i class="bi bi-person-workspace me-2"></i>My Employee Portal</a></li>
                    @endif
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i>Sign Out</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content Header -->
    <div class="container launcher-hero">
        <h1 class="hero-title">Welcome to Enterprise Portal</h1>
        <p class="hero-subtitle">Single Sign-On powered by WSO2 Identity Server. Select an application to launch.</p>

        <!-- WFH Web Punch Widget -->
        @if(isset($eligibility))
            <div class="card bg-dark text-white mb-4" style="max-width: 600px; margin: 0 auto; border-radius: 15px; border: 1px solid rgba(255,255,255,0.1);">
                <div class="card-body p-4 text-center">
                    <h5 class="card-title text-warning mb-3"><i class="bi bi-geo-alt-fill me-2"></i>Remote Attendance (Web Punch)</h5>
                    
                    @if($eligibility['eligible'])
                        <div class="alert alert-success bg-transparent border-success text-success p-2 mb-3">
                            <i class="bi bi-check-circle-fill me-2"></i> You are eligible for Web Punch today.
                        </div>
                        <button id="webPunchBtn" class="btn btn-lg btn-warning w-100 fw-bold" onclick="performWebPunch('{{ $eligibility['next_action'] }}')">
                            {{ $eligibility['next_action'] === 'CHECK_IN' ? 'Punch In (Start Shift)' : 'Punch Out (End Shift)' }}
                        </button>
                    @else
                        <div class="alert alert-secondary bg-transparent border-secondary text-secondary p-2 mb-0">
                            <i class="bi bi-info-circle-fill me-2"></i> {{ $eligibility['message'] }}
                        </div>
                    @endif
                    <div id="punchStatus" class="mt-3 text-start fw-bold" style="display: none;"></div>
                </div>
            </div>

            @if($eligibility['eligible'])
            <script>
                function performWebPunch(action) {
                    const btn = document.getElementById('webPunchBtn');
                    const status = document.getElementById('punchStatus');
                    
                    btn.disabled = true;
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Locating...';
                    status.style.display = 'none';

                    if (!navigator.geolocation) {
                        showPunchError('Geolocation is not supported by your browser.');
                        return;
                    }

                    navigator.geolocation.getCurrentPosition(
                        position => {
                            sendPunchRequest(position.coords.latitude, position.coords.longitude, position.coords.accuracy, action);
                        },
                        error => {
                            showPunchError('Failed to get location: ' + error.message);
                        },
                        { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
                    );
                }

                function sendPunchRequest(lat, lng, accuracy, action) {
                    const btn = document.getElementById('webPunchBtn');
                    const status = document.getElementById('punchStatus');

                    fetch('{{ route('web-punch.store') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            latitude: lat,
                            longitude: lng,
                            accuracy: accuracy,
                            action: action
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.error) {
                            showPunchError(data.error);
                        } else {
                            btn.className = 'btn btn-lg btn-success w-100 fw-bold disabled';
                            btn.innerHTML = '<i class="bi bi-check2-circle me-2"></i>' + data.message;
                            status.className = 'mt-3 text-start fw-bold text-success';
                            status.innerHTML = 'Success: Attendance recorded.';
                            status.style.display = 'block';
                            setTimeout(() => window.location.reload(), 2000);
                        }
                    })
                    .catch(err => {
                        showPunchError('Network or Server Error: ' + err.message);
                    });
                }

                function showPunchError(msg) {
                    const btn = document.getElementById('webPunchBtn');
                    const status = document.getElementById('punchStatus');
                    btn.disabled = false;
                    btn.innerHTML = 'Try Again';
                    status.className = 'mt-3 text-start fw-bold text-danger';
                    status.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-2"></i>' + msg;
                    status.style.display = 'block';
                }
            </script>
            @endif
        @endif

        <!-- Search Bar -->
        <div class="search-box-container">
            <i class="bi bi-search search-icon"></i>
            <input type="text" id="appSearch" class="form-control search-input" placeholder="Search applications, tools, or portals...">
        </div>

        <!-- Category Filter Tabs -->
        <div class="category-tabs">
            <button class="tab-btn active" onclick="filterCategory('all', this)">All Apps</button>
            @if(isset($categories) && count($categories) > 0)
                @foreach($categories as $catKey => $catLabel)
                    <button class="tab-btn" onclick="filterCategory('{{ $catKey }}', this)">{{ $catLabel }}</button>
                @endforeach
            @endif
        </div>
    </div>

    <!-- Application Cards Grid -->
    <div class="apps-grid" id="appsGrid">
        @if(isset($apps) && count($apps) > 0)
            @foreach($apps as $app)
                <a href="{{ $app->launch_url }}" @if($app->target_blank) target="_blank" @endif class="app-card" data-category="{{ $app->category }}">
                    <div class="app-header">
                        <div class="app-icon" style="background: {{ $app->gradient_css }};">
                            <i class="{{ $app->icon_class }}"></i>
                        </div>
                        <span class="app-tag">{{ $app->tag }}</span>
                    </div>
                    <h3 class="app-title">{{ $app->name }}</h3>
                    <p class="app-description">{{ $app->description }}</p>
                    <div class="app-footer">
                        <span>{{ $app->target_blank ? 'Open Application' : 'Launch Application' }}</span>
                        <i class="bi {{ $app->target_blank ? 'bi-box-arrow-up-right' : 'bi-arrow-right' }} launch-icon"></i>
                    </div>
                </a>
            @endforeach
        @endif
    </div>

    <!-- Bootstrap Bundle JS & Interactive Filtering Script -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Category Tab Filter
        function filterCategory(category, btnElement) {
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            btnElement.classList.add('active');

            const appCards = document.querySelectorAll('.app-card');
            appCards.forEach(card => {
                if (category === 'all' || card.getAttribute('data-category') === category) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        // Live Search Filter
        document.getElementById('appSearch').addEventListener('input', function (e) {
            const searchTerm = e.target.value.toLowerCase();
            const appCards = document.querySelectorAll('.app-card');

            appCards.forEach(card => {
                const title = card.querySelector('.app-title').textContent.toLowerCase();
                const description = card.querySelector('.app-description').textContent.toLowerCase();

                if (title.includes(searchTerm) || description.includes(searchTerm)) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>
