<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AMS Enterprise Login - Workforce Management System</title>
    <meta name="description" content="Sign in to AMS Enterprise Workforce Management System to manage attendance, payroll, shifts and biometric devices.">
    
    <!-- Google Fonts: Inter & Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        /* ══════════════════════════════════════════════
           Design Tokens
        ══════════════════════════════════════════════ */
        :root {
            --primary:          #FF4916;
            --primary-hover:    #E03E0F;
            --primary-light:    rgba(255, 73, 22, 0.08);
            --primary-subtle:   rgba(255, 73, 22, 0.15);
            --primary-glow:     rgba(255, 73, 22, 0.28);
            --secondary:        #162029;
            --secondary-light:  #202D39;
            --canvas-bg:        #F8FAFC;
            --card-bg:          #FFFFFF;
            --border-color:     #E2E8F0;
            --text-main:        #162029;
            --text-secondary:   #64748B;
            --text-muted:       #94A3B8;
            --font-family-sans:    'Inter', system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
            --font-family-display: 'Plus Jakarta Sans', 'Inter', system-ui, sans-serif;
        }

        *, *::before, *::after { box-sizing: border-box; }

        html, body {
            height: 100%;
            margin: 0;
            overflow-x: hidden;
        }

        body {
            color: var(--text-main);
            font-family: var(--font-family-sans);
            background-color: var(--canvas-bg);
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
        }

        /* ══════════════════════════════════════════════
           Split Layout (Left brand + Right form)
        ══════════════════════════════════════════════ */
        .split-layout {
            display: flex;
            min-height: 100vh;
            width: 100%;
        }

        /* ── Left Panel: Dark Navy Brand Showcase ── */
        .info-panel {
            background-color: var(--secondary);
            background-image:
                radial-gradient(circle at 10% 15%, rgba(255, 73, 22, 0.18) 0%, transparent 45%),
                radial-gradient(circle at 90% 85%, rgba(32, 45, 57, 0.6) 0%, transparent 50%);
            color: #FFFFFF;
            width: 50%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 60px 48px;
            position: relative;
            overflow: hidden;
        }

        .info-content {
            max-width: 500px;
            width: 100%;
            text-align: left;
        }

        /* Logo box */
        .logo-box {
            width: 58px;
            height: 58px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 24px;
        }

        /* Pill badge */
        .info-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            background: rgba(255, 73, 22, 0.14);
            border: 1px solid rgba(255, 73, 22, 0.35);
            color: #FF835E;
            margin-bottom: 16px;
        }
        .info-badge-dot {
            width: 6px; height: 6px;
            border-radius: 50%;
            background: var(--primary);
            box-shadow: 0 0 8px var(--primary);
            flex-shrink: 0;
        }

        /* Headline */
        .info-title {
            font-family: var(--font-family-display);
            font-weight: 800;
            font-size: clamp(1.7rem, 3.2vw, 2.45rem);
            letter-spacing: -0.03em;
            line-height: 1.15;
            color: #FFFFFF;
            margin-bottom: 14px;
        }
        .info-title span { color: var(--primary); }

        .info-subtitle {
            color: #94A3B8;
            font-size: 0.94rem;
            line-height: 1.65;
            margin-bottom: 36px;
            font-weight: 400;
        }

        /* Feature cards grid */
        .features-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 14px;
            width: 100%;
        }

        .feature-card {
            background-color: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 12px;
            padding: 16px;
            transition: all 0.25s ease;
        }
        .feature-card:hover {
            background-color: rgba(255, 255, 255, 0.07);
            border-color: rgba(255, 73, 22, 0.4);
            transform: translateY(-2px);
        }

        .feature-icon-wrap {
            width: 32px; height: 32px;
            border-radius: 8px;
            background: rgba(255, 73, 22, 0.12);
            color: var(--primary);
            display: flex; align-items: center; justify-content: center;
            font-size: 1rem;
            margin-bottom: 10px;
        }
        .feature-title {
            font-family: var(--font-family-display);
            font-size: 0.88rem; font-weight: 700;
            color: #FFFFFF; margin-bottom: 4px;
        }
        .feature-desc {
            color: #94A3B8; font-size: 0.76rem;
            line-height: 1.45; margin: 0;
        }

        /* ── Right Panel: White Login Card ── */
        .form-panel {
            background-color: var(--canvas-bg);
            width: 50%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 40px 24px;
            min-height: 100vh;
        }

        .form-content {
            background-color: var(--card-bg);
            border-radius: 20px;
            padding: 44px 40px;
            width: 100%;
            max-width: 440px;
            box-shadow:
                0 1px 3px 0 rgba(16, 24, 40, 0.06),
                0 8px 24px -4px rgba(22, 32, 41, 0.06);
            border: 1px solid var(--border-color);
        }

        /* Mobile-only brand shown when left panel is hidden */
        .mobile-brand {
            display: none;
            margin-bottom: 24px;
            text-align: center;
        }

        /* Form headings */
        .login-title {
            font-family: var(--font-family-display);
            font-weight: 800;
            font-size: 1.7rem;
            letter-spacing: -0.03em;
            color: var(--secondary);
            margin-bottom: 6px;
        }
        .login-subtitle {
            color: var(--text-secondary);
            font-size: 0.88rem;
            margin-bottom: 28px;
        }

        /* Labels */
        .form-label-custom {
            font-size: 0.78rem;
            font-weight: 700;
            color: var(--secondary);
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: block;
        }

        /* Input icon wrapper */
        .input-icon-group { position: relative; }
        .input-icon-group .input-icon {
            position: absolute;
            left: 14px; top: 50%;
            transform: translateY(-50%);
            color: #94A3B8;
            font-size: 1rem;
            pointer-events: none;
            transition: color 0.2s ease;
        }
        .input-icon-group:focus-within .input-icon { color: var(--primary); }

        /* Inputs */
        .form-control-custom {
            background-color: #FFFFFF !important;
            border: 1px solid var(--border-color) !important;
            color: var(--text-main) !important;
            border-radius: 10px !important;
            padding: 12px 16px 12px 42px !important;
            transition: all 0.2s ease !important;
            font-size: 0.92rem;
            font-weight: 500;
            width: 100%;
        }
        .form-control-custom:focus {
            border-color: var(--primary) !important;
            box-shadow: 0 0 0 3px var(--primary-subtle) !important;
            outline: none;
        }

        /* Checkbox */
        .form-check-input-custom {
            border-color: #CBD5E1;
            width: 1.05rem; height: 1.05rem;
            border-radius: 4px;
            cursor: pointer;
            flex-shrink: 0;
        }
        .form-check-input-custom:checked {
            background-color: var(--primary) !important;
            border-color: var(--primary) !important;
            box-shadow: 0 0 0 2px var(--primary-light);
        }

        /* Login button */
        .btn-login {
            background: linear-gradient(135deg, #FF5B29, var(--primary));
            border: none;
            color: #FFFFFF;
            padding: 12.5px;
            font-weight: 700;
            font-family: var(--font-family-display);
            font-size: 0.95rem;
            border-radius: 10px;
            width: 100%;
            transition: all 0.2s ease;
            box-shadow: 0 4px 14px var(--primary-glow);
            margin-top: 18px;
            display: flex; align-items: center; justify-content: center; gap: 8px;
            cursor: pointer;
        }
        .btn-login:hover {
            background: linear-gradient(135deg, var(--primary), var(--primary-hover));
            box-shadow: 0 6px 20px rgba(255, 73, 22, 0.42);
            transform: translateY(-1px);
            color: #FFFFFF;
        }
        .btn-login:active { transform: translateY(0); }

        /* Footer note */
        .auth-footer-note {
            margin-top: 24px;
            padding-top: 16px;
            border-top: 1px solid var(--border-color);
            text-align: center;
            font-size: 0.75rem;
            color: var(--text-muted);
            display: flex; align-items: center; justify-content: center; gap: 6px;
        }

        /* Show/hide password toggle */
        .password-toggle {
            position: absolute;
            right: 12px; top: 50%;
            transform: translateY(-50%);
            background: none; border: none;
            color: #94A3B8;
            cursor: pointer;
            padding: 0; display: flex; align-items: center;
            font-size: 1rem;
            transition: color 0.2s;
        }
        .password-toggle:hover { color: var(--primary); }

        /* ══════════════════════════════════════════════
           RESPONSIVE — Full RWD Coverage
        ══════════════════════════════════════════════ */

        /* ── ≥ 1400px: extra-large desktops (defaults above) ── */

        /* ── 1200–1399px: standard desktops ── */
        @media (max-width: 1399px) {
            .info-panel      { padding: 50px 36px; }
            .form-content    { padding: 36px 32px; max-width: 400px; }
        }

        /* ── 1024–1199px: laptops / small desktops ── */
        @media (max-width: 1199px) {
            .info-panel   { padding: 40px 28px; }
            .form-panel   { padding: 32px 18px; }
            .form-content { padding: 32px 28px; max-width: 380px; }
            .features-grid { gap: 10px; }
            .feature-card  { padding: 12px; }
            .info-subtitle { font-size: 0.88rem; margin-bottom: 28px; }
        }

        /* ── 1366px laptops (common) ── */
        @media (max-width: 1366px) and (min-width: 1200px) {
            .info-panel   { padding: 40px 30px; }
            .form-panel   { padding: 28px 16px; }
            .form-content { padding: 32px 28px; max-width: 390px; }
            .login-title  { font-size: 1.5rem; }
            .login-subtitle { margin-bottom: 20px; font-size: 0.82rem; }
            .form-control-custom { padding: 10px 14px 10px 40px !important; font-size: 0.88rem; }
            .btn-login    { padding: 11px; font-size: 0.9rem; }
        }

        /* ── 768–991px: tablet landscape — hide left panel ── */
        @media (max-width: 991.98px) {
            .info-panel {
                display: none;
            }
            .form-panel {
                width: 100%;
                min-height: 100vh;
                padding: 48px 24px;
                background: linear-gradient(135deg, #F8FAFC 0%, #EEF3FA 100%);
            }
            .form-content {
                max-width: 500px;
                padding: 40px 36px;
                margin: 0 auto;
            }
            .mobile-brand { display: block; }
        }

        /* ── 576–767px: tablet portrait ── */
        @media (max-width: 767.98px) {
            .form-panel   { padding: 36px 16px; }
            .form-content {
                padding: 32px 24px;
                max-width: 100%;
                border-radius: 16px;
            }
            .login-title    { font-size: 1.5rem; }
            .login-subtitle { font-size: 0.85rem; margin-bottom: 22px; }
        }

        /* ── ≤ 575px: mobile phones ── */
        @media (max-width: 575.98px) {
            .form-panel   { padding: 24px 12px; }
            .form-content {
                padding: 28px 18px;
                border-radius: 14px;
            }
            .login-title    { font-size: 1.35rem; }
            .login-subtitle { font-size: 0.82rem; margin-bottom: 18px; }
            .btn-login      { font-size: 0.9rem; padding: 11px; }
            .features-grid  { grid-template-columns: 1fr; }
        }

        /* ── ≤ 360px: very small phones ── */
        @media (max-width: 360px) {
            .form-content { padding: 22px 14px; }
            .login-title  { font-size: 1.2rem; }
            .form-control-custom { font-size: 0.85rem !important; }
        }
    </style>
</head>
<body>

    <div class="split-layout">
        <!-- ══ LEFT PANEL: Dark Enterprise Brand Info ══ -->
        <div class="info-panel" aria-hidden="true">
            <div class="info-content">
                <div class="logo-box">
                    <img src="{{ asset('images/company_logo.png') }}" alt="Company Logo"
                         style="height: 52px; max-width: 100%; object-fit: contain;">
                </div>

                <div class="info-badge">
                    <span class="info-badge-dot"></span>
                    <span>AMS Enterprise v3.2</span>
                </div>

                <h1 class="info-title">Workforce &amp; <span>Biometrics</span> Console</h1>
                <p class="info-subtitle">Unified biometric terminal integration, automated shift scheduling, statutory compliance, and payroll processing.</p>

                <div class="features-grid">
                    <div class="feature-card">
                        <div class="feature-icon-wrap"><i class="bi bi-cpu-fill"></i></div>
                        <div class="feature-title">Device ADMS Push</div>
                        <p class="feature-desc">ZKTeco hardware auto-discovery &amp; real-time punch sync.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon-wrap"><i class="bi bi-calendar3-range-fill"></i></div>
                        <div class="feature-title">Advanced Rostering</div>
                        <p class="feature-desc">Cross-midnight, split shifts, grace period &amp; overtime rules.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon-wrap"><i class="bi bi-cash-stack"></i></div>
                        <div class="feature-title">Statutory Payroll</div>
                        <p class="feature-desc">Automated EPF/ETF, IRD APIT tax rules &amp; digital payslips.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon-wrap"><i class="bi bi-shield-lock-fill"></i></div>
                        <div class="feature-title">Audit Governance</div>
                        <p class="feature-desc">3-tiered RBAC, tamper-proof logs &amp; access workflows.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- ══ RIGHT PANEL: Sign In Form ══ -->
        <div class="form-panel">
            <div class="form-content">

                <!-- Mobile-only brand logo (shows when left panel is hidden) -->
                <div class="mobile-brand">
                    <img src="{{ asset('images/company_logo.png') }}" alt="Company Logo"
                         style="height: 44px; max-width: 180px; object-fit: contain; margin-bottom: 0.75rem;">
                    <p style="font-size:0.78rem; color:var(--text-muted); margin:0;">
                        AMS Enterprise &mdash; Workforce Console
                    </p>
                </div>

                <h2 class="login-title">Sign In</h2>
                <p class="login-subtitle">Access your workforce management console</p>

                @if ($errors->any())
                    <div class="alert border-0 p-3 mb-4" role="alert"
                         style="background-color: rgba(239,68,68,0.08); color:#DC2626; font-size:0.85rem; border-radius:10px;">
                        <ul class="m-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('login.post') }}" method="POST" autocomplete="on">
                    @csrf

                    <!-- Username / Email -->
                    <div class="mb-3">
                        <label for="username" class="form-label-custom">Email or Username</label>
                        <div class="input-icon-group">
                            <i class="bi bi-person-fill input-icon"></i>
                            <input type="text"
                                   class="form-control form-control-custom"
                                   id="username" name="username"
                                   value="{{ old('username') }}"
                                   placeholder="e.g. prime1-admin"
                                   required autocomplete="username" autofocus>
                        </div>
                    </div>

                    <!-- Password -->
                    <div class="mb-3">
                        <label for="password" class="form-label-custom">Password</label>
                        <div class="input-icon-group">
                            <i class="bi bi-lock-fill input-icon"></i>
                            <input type="password"
                                   class="form-control form-control-custom"
                                   id="password" name="password"
                                   placeholder="••••••••"
                                   required autocomplete="current-password"
                                   style="padding-right: 44px !important;">
                            <button type="button" class="password-toggle"
                                    id="togglePassword" aria-label="Show/hide password">
                                <i class="bi bi-eye" id="togglePasswordIcon"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Remember + Forgot row -->
                    <div class="d-flex align-items-center justify-content-between mb-4 mt-2">
                        <div class="form-check m-0 d-flex align-items-center gap-2">
                            <input type="checkbox"
                                   class="form-check-input form-check-input-custom m-0"
                                   id="remember" name="remember">
                            <label class="form-check-label text-secondary user-select-none"
                                   style="font-size:0.84rem;" for="remember">
                                Remember this device
                            </label>
                        </div>
                    </div>

                    <!-- Submit -->
                    <button type="submit" class="btn btn-login" id="loginBtn">
                        <span>Sign In to Console</span>
                        <i class="bi bi-arrow-right"></i>
                    </button>
                </form>

                <div class="auth-footer-note">
                    <i class="bi bi-shield-check text-success"></i>
                    <span>256-bit SSL Secure Enterprise Gateway</span>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        /* Show/hide password */
        const toggleBtn = document.getElementById('togglePassword');
        const pwdInput  = document.getElementById('password');
        const pwdIcon   = document.getElementById('togglePasswordIcon');
        if (toggleBtn) {
            toggleBtn.addEventListener('click', () => {
                const show   = pwdInput.type === 'password';
                pwdInput.type = show ? 'text' : 'password';
                pwdIcon.className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
            });
        }

        /* Loading state on submit */
        const loginForm = document.querySelector('form');
        const loginBtn  = document.getElementById('loginBtn');
        if (loginForm && loginBtn) {
            loginForm.addEventListener('submit', () => {
                loginBtn.disabled = true;
                loginBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span> Signing in…';
            });
        }
    </script>
</body>
</html>
