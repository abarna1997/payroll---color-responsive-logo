<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password - AMS</title>
    
    <!-- Google Fonts: Outfit & Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --bg-color: #0f172a;
            --card-bg: rgba(30, 41, 59, 0.75);
            --border-color: rgba(255, 255, 255, 0.08);
            --text-primary: #f8fafc;
            --text-secondary: #94a3b8;
            --accent-color: #4f46e5;
        }

        body {
            background-color: var(--bg-color);
            background-image: radial-gradient(at 0% 0%, rgba(79, 70, 229, 0.15) 0px, transparent 50%),
                              radial-gradient(at 100% 100%, rgba(99, 102, 241, 0.1) 0px, transparent 50%);
            color: var(--text-primary);
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .change-card {
            background-color: var(--card-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 40px;
            width: 100%;
            max-width: 480px;
            box-shadow: 0 10px 40px 0 rgba(0, 0, 0, 0.3);
        }

        .title {
            font-family: 'Outfit', sans-serif;
            font-weight: 800;
            letter-spacing: -1px;
            color: var(--text-primary);
            font-size: 1.8rem;
            text-align: center;
            margin-bottom: 12px;
        }

        .subtitle {
            color: var(--text-secondary);
            font-size: 0.9rem;
            text-align: center;
            margin-bottom: 30px;
        }

        .form-control-custom {
            background-color: rgba(15, 23, 42, 0.6) !important;
            border: 1px solid var(--border-color) !important;
            color: var(--text-primary) !important;
            border-radius: 8px !important;
            padding: 12px 16px !important;
            transition: all 0.2s ease !important;
        }

        .form-control-custom:focus {
            border-color: var(--accent-color) !important;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.25) !important;
            background-color: rgba(15, 23, 42, 0.8) !important;
        }

        .btn-submit {
            background: linear-gradient(135deg, #818cf8, #4f46e5);
            border: none;
            color: white;
            padding: 12px;
            font-weight: 600;
            border-radius: 8px;
            width: 100%;
            transition: all 0.2s ease;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2);
            margin-top: 16px;
        }

        .btn-submit:hover {
            background: linear-gradient(135deg, #6366f1, #4338ca);
            box-shadow: 0 6px 16px rgba(79, 70, 229, 0.3);
            transform: translateY(-1px);
        }
    </style>
</head>
<body>

    <div class="change-card">
        <h2 class="title"><i class="bi bi-shield-lock me-2 text-warning"></i> Security Update</h2>
        <p class="subtitle">You are required to change your password on first login to secure your account.</p>

        @if ($errors->any())
            <div class="alert alert-danger border-0 p-3 mb-4" style="background-color: rgba(239, 68, 68, 0.15); color: #ef4444; font-size: 0.85rem;" role="alert">
                <ul class="m-0 pl-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('password.change.post') }}" method="POST">
            @csrf
            
            <div class="mb-3">
                <label for="current_password" class="form-label text-secondary fs-7">Current Temporary Password</label>
                <input type="password" class="form-control form-control-custom" id="current_password" name="current_password" required placeholder="Enter temporary password">
            </div>

            <div class="mb-3">
                <label for="password" class="form-label text-secondary fs-7">New Password</label>
                <input type="password" class="form-control form-control-custom" id="password" name="password" required placeholder="Enter at least 8 characters">
            </div>

            <div class="mb-4">
                <label for="password_confirmation" class="form-label text-secondary fs-7">Confirm New Password</label>
                <input type="password" class="form-control form-control-custom" id="password_confirmation" name="password_confirmation" required placeholder="Re-type new password">
            </div>

            <button type="submit" class="btn btn-submit">
                <i class="bi bi-key-fill me-2"></i> Update Password & Continue
            </button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
