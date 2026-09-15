<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

class AuthController extends Controller
{
    /**
     * Show login form or redirect directly to WSO2 Identity Server SSO
     */
    public function showLogin(Request $request)
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    /**
     * Authenticate user login
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $username = $request->input('username');
        $password = $request->input('password');

        // Dynamic account lockout configuration
        $maxAttempts = 5;
        try {
            $maxAttempts = (int) Setting::getVal('Security', 'MaxLoginAttempts', '5');
        } catch (\Exception $e) {
            // fallback
        }

        $key = 'login-attempts:'.$request->ip().'|'.$username;

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);

            // Log lockout error in Audit Trail
            AuditLog::create([
                'user_id' => null,
                'action' => 'LOGIN_LOCKED',
                'module' => 'Authentication',
                'record_id' => null,
                'old_value' => null,
                'new_value' => json_encode(['username' => $username, 'ip' => $request->ip()]),
                'ip_address' => $request->ip(),
            ]);

            return back()->withErrors([
                'username' => "Too many failed login attempts. Locked out for {$seconds} seconds.",
            ]);
        }

        // Try authenticating
        $loginField = filter_var($username, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        
        if (Auth::attempt([$loginField => $username, 'password' => $password, 'status' => 'Active'], $request->filled('remember'))) {
            RateLimiter::clear($key);

            $user = Auth::user();
            $user->last_login = now();
            $user->save();

            // Log successful login audit trail
            AuditLog::create([
                'user_id' => $user->id,
                'action' => 'LOGIN_SUCCESS',
                'module' => 'Authentication',
                'record_id' => $user->id,
                'old_value' => null,
                'new_value' => json_encode(['username' => $username]),
                'ip_address' => $request->ip(),
            ]);

            if ($user->force_password_change) {
                return redirect()->route('password.change');
            }

            // Redirect based on permissions
            if ($user->hasPermissionTo('dashboard.view')) {
                return redirect()->route('dashboard');
            } else {
                return redirect()->route('portal.dashboard');
            }
        }

        // Authentication failed
        RateLimiter::hit($key, 60); // 1 minute window lock

        // Log failed login audit trail
        AuditLog::create([
            'user_id' => null,
            'action' => 'LOGIN_FAILED',
            'module' => 'Authentication',
            'record_id' => null,
            'old_value' => null,
            'new_value' => json_encode(['username' => $username]),
            'ip_address' => $request->ip(),
        ]);

        $attemptsLeft = RateLimiter::remaining($key, $maxAttempts);
        $errorMsg = 'Invalid email/username or password.';
        if ($attemptsLeft > 0) {
            $errorMsg .= " You have {$attemptsLeft} login attempts remaining before lockout.";
        }

        return back()->withInput($request->only('username', 'remember'))->withErrors([
            'username' => $errorMsg,
        ]);
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            AuditLog::create([
                'user_id' => $user->id,
                'action' => 'LOGOUT',
                'module' => 'Authentication',
                'record_id' => $user->id,
                'old_value' => null,
                'new_value' => null,
                'ip_address' => $request->ip(),
            ]);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * Show force password change page
     */
    public function showPasswordChange()
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        return view('auth.change_password');
    }

    /**
     * Submit password change
     */
    public function passwordChange(Request $request)
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Get dynamic minimum password length from settings
        $minLength = 8;
        try {
            $minLength = (int) Setting::getVal('Security', 'PasswordMinLength', '8');
        } catch (\Exception $e) {
            // fallback
        }

        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:'.$minLength.'|confirmed|different:current_password',
        ]);

        if (! Hash::check($request->input('current_password'), $user->password)) {
            return back()->withErrors(['current_password' => 'The provided current password does not match our records.']);
        }

        // Save new password
        $user->password = Hash::make($request->input('password'));
        $user->force_password_change = false;
        $user->save();

        // Log audit event
        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'PASSWORD_CHANGED',
            'module' => 'Authentication',
            'record_id' => $user->id,
            'old_value' => null,
            'new_value' => json_encode(['username' => $user->username]),
            'ip_address' => $request->ip(),
        ]);

        // Redirect based on permissions
        if ($user->hasPermissionTo('dashboard.view')) {
            return redirect()->route('dashboard')->with('success', 'Your password was changed successfully!');
        } else {
            return redirect()->route('portal.dashboard')->with('success', 'Your password was changed successfully!');
        }
    }
}
