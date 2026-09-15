<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use App\Jobs\SyncEmployeeToDevices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class Wso2Controller extends Controller
{
    /**
     * Build WSO2 Identity Server OAuth2 endpoint URLs dynamically.
     */
    protected function getWso2Endpoint(string $endpoint): string
    {
        $baseUrl = rtrim(config('services.wso2.base_url', 'https://localhost:9443'), '/');
        $endpoint = ltrim($endpoint, '/');

        if (str_contains($baseUrl, '/oauth2')) {
            return $baseUrl . '/' . $endpoint;
        }

        return $baseUrl . '/oauth2/' . $endpoint;
    }

    /**
     * Redirect user to WSO2 Identity Server 7.x OAuth2/OIDC Authorization page.
     */
    public function redirectToWso2()
    {
        $clientId = config('services.wso2.client_id');
        $redirectUri = config('services.wso2.redirect');

        if (empty($clientId)) {
            return redirect()->route('login', ['local' => 1])->withErrors([
                'username' => 'WSO2 Identity Server Client ID is not configured in .env file.',
            ]);
        }

        $state = Str::random(40);
        session(['wso2_oauth_state' => $state]);

        $query = http_build_query([
            'client_id'     => $clientId,
            'redirect_uri'  => $redirectUri,
            'response_type' => 'code',
            'scope'         => 'openid profile email roles organization',
            'state'         => $state,
        ]);

        $authorizeUrl = $this->getWso2Endpoint('authorize');

        return redirect($authorizeUrl . '?' . $query);
    }

    /**
     * Handle incoming OAuth2 callback from WSO2 Identity Server 7.x.
     */
    public function handleWso2Callback(Request $request)
    {
        $state = session()->pull('wso2_oauth_state');

        if (empty($state) || $request->input('state') !== $state) {
            return redirect()->route('login', ['local' => 1])->withErrors([
                'username' => 'Invalid WSO2 OAuth state token.',
            ]);
        }

        if ($request->has('error')) {
            return redirect()->route('login', ['local' => 1])->withErrors([
                'username' => 'WSO2 IS Authentication Error: ' . $request->input('error_description', $request->input('error')),
            ]);
        }

        $code = $request->input('code');
        if (empty($code)) {
            return redirect()->route('login', ['local' => 1])->withErrors([
                'username' => 'Authorization code not returned from WSO2 Identity Server.',
            ]);
        }

        try {
            $verifySsl = config('services.wso2.verify_ssl', false);
            $tokenUrl = $this->getWso2Endpoint('token');
            $userInfoUrl = $this->getWso2Endpoint('userinfo');

            // 1. Exchange Auth Code for Access Token
            $tokenResponse = Http::withoutVerifying(!$verifySsl)
                ->asForm()
                ->post($tokenUrl, [
                    'grant_type'    => 'authorization_code',
                    'client_id'     => config('services.wso2.client_id'),
                    'client_secret' => config('services.wso2.client_secret'),
                    'redirect_uri'  => config('services.wso2.redirect'),
                    'code'          => $code,
                ]);

            if (!$tokenResponse->successful()) {
                Log::error('WSO2 Token Exchange Failed: ' . $tokenResponse->body());
                return redirect()->route('login', ['local' => 1])->withErrors([
                    'username' => 'Failed to retrieve access token from WSO2 Identity Server.',
                ]);
            }

            $tokenData = $tokenResponse->json();
            $accessToken = $tokenData['access_token'] ?? null;

            if (empty($accessToken)) {
                return redirect()->route('login', ['local' => 1])->withErrors([
                    'username' => 'Access token missing in WSO2 Identity Server response.',
                ]);
            }

            // 2. Fetch User Profile from WSO2 IS UserInfo endpoint
            $userInfoResponse = Http::withoutVerifying(!$verifySsl)
                ->withToken($accessToken)
                ->get($userInfoUrl);

            if (!$userInfoResponse->successful()) {
                Log::error('WSO2 UserInfo Request Failed: ' . $userInfoResponse->body());
                return redirect()->route('login', ['local' => 1])->withErrors([
                    'username' => 'Failed to fetch user profile from WSO2 Identity Server.',
                ]);
            }

            $userClaims = $userInfoResponse->json();

            // Extract claims
            $email = $userClaims['email'] ?? ($userClaims['emails'][0]['value'] ?? null);
            $username = $userClaims['username'] 
                ?? $userClaims['preferred_username'] 
                ?? $userClaims['sub'] 
                ?? ($email ? explode('@', $email)[0] : null);

            if (empty($username) && empty($email)) {
                return redirect()->route('login', ['local' => 1])->withErrors([
                    'username' => 'WSO2 Identity Server did not return a valid user identity claim.',
                ]);
            }

            $roles = $userClaims['roles'] ?? $userClaims['groups'] ?? [];
            $rolesArray = is_array($roles) ? $roles : explode(',', (string)$roles);

            $orgClaim = $userClaims['organization'] 
                ?? $userClaims['org_id']
                ?? $userClaims['user_org_id']
                ?? $userClaims['urn:ietf:params:scim:schemas:extension:enterprise:2.0:User:organization'] 
                ?? null;

            // Map System Role & Company
            $role = $this->mapWso2RolesToRole($rolesArray);
            $company = $this->mapWso2OrgToCompany($orgClaim, $rolesArray, $email);

            // 3. Match or Create Local User
            $user = User::where('email', $email)
                ->orWhere('username', $username)
                ->first();

            $isNewUser = false;
            if (!$user) {
                $isNewUser = true;
                $user = User::create([
                    'username' => $username,
                    'email' => $email ?? ($username . '@company.local'),
                    'password' => bcrypt(Str::random(32)),
                    'role' => $role,
                    'status' => 'Active',
                    'auth_method' => 'wso2',
                    'force_password_change' => false,
                ]);
            } else {
                $user->update([
                    'role' => $role,
                    'auth_method' => 'wso2',
                ]);
            }

            if ($user->status === 'Inactive' || $user->isLocked()) {
                return redirect()->route('login', ['local' => 1])->withErrors([
                    'username' => 'Your account is deactivated or locked. Please contact your system administrator.',
                ]);
            }

            // 4. Provision Employee & Sync Hardware Terminals
            $employee = $this->ensureEmployeeSyncedToBiometrics($user, $userClaims, $role, $company, $isNewUser);

            // Establish Authenticated Session
            Auth::login($user, true);
            $user->last_login = now();
            $user->save();

            // Log Audit Trail
            AuditLog::create([
                'user_id' => $user->id,
                'action' => 'LOGIN_WSO2_SUCCESS',
                'module' => 'Authentication',
                'record_id' => $user->id,
                'old_value' => null,
                'new_value' => json_encode([
                    'provider' => 'WSO2 Identity Server 7.x',
                    'username' => $username,
                    'email' => $email,
                    'role' => $role,
                    'company' => $company->company_name,
                    'sub' => $userClaims['sub'] ?? null,
                ]),
                'ip_address' => $request->ip(),
            ]);

            // Route to Onboarding if pending
            if ($employee && $employee->employment_status === 'Onboarding') {
                return redirect()->route('onboarding.wizard', ['employee_id' => $employee->id]);
            }

            return redirect()->route('apps.index');

        } catch (\Exception $e) {
            Log::error('WSO2 OIDC Callback Error: ' . $e->getMessage(), [
                'exception' => $e,
            ]);

            return redirect()->route('login', ['local' => 1])->withErrors([
                'username' => 'WSO2 SSO Error: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Map WSO2 IS Roles to Laravel System Roles.
     */
    protected function mapWso2RolesToRole(array $roles): string
    {
        $lowerRoles = array_map('strtolower', $roles);

        if (in_array('admin', $lowerRoles) || in_array('superadministrator', $lowerRoles) || in_array('internal/admin', $lowerRoles)) {
            return 'Admin';
        }

        if (in_array('manager', $lowerRoles) || in_array('hr-administrator', $lowerRoles) || in_array('supervisor', $lowerRoles)) {
            return 'Manager';
        }

        return 'Employee';
    }

    /**
     * Map WSO2 IS Organization (ID, Handle, Roles, or Email Domain) to Laravel Company.
     */
    protected function mapWso2OrgToCompany(?string $orgClaim, array $roles, ?string $email): Company
    {
        // 1. Explicit WSO2 IS Organization ID & Handle Mapping
        if (!empty($orgClaim)) {
            // Prime One Global (ID: 48355f6b-1389-41bd-8c8f-c8209ede0b30 | Handle: primeoneglobal)
            if ($orgClaim === '48355f6b-1389-41bd-8c8f-c8209ede0b30' || str_contains(strtolower($orgClaim), 'primeone')) {
                $company = Company::where('company_code', 'P1')->first();
                if ($company) return $company;
            }

            // Altitude 1 (ID: 4b25f309-bba7-4d5c-bcbd-062a6c43a421 | Handle: altitude-1)
            if ($orgClaim === '4b25f309-bba7-4d5c-bcbd-062a6c43a421' || str_contains(strtolower($orgClaim), 'altitude')) {
                $company = Company::where('company_code', 'A1')->first();
                if ($company) return $company;
            }

            $company = Company::where('company_code', $orgClaim)
                ->orWhere('company_name', 'LIKE', '%' . $orgClaim . '%')
                ->first();
            if ($company) {
                return $company;
            }
        }

        // 2. Match Roles/Groups (e.g. 'P1', 'A1', 'Prime One Global', 'Altitude 1')
        foreach ($roles as $roleName) {
            $company = Company::where('company_code', $roleName)
                ->orWhere('company_name', 'LIKE', '%' . $roleName . '%')
                ->first();
            if ($company) {
                return $company;
            }
        }

        // 3. Match Email Domain Suffix (e.g. 'user@primeone.lk' -> 'primeone.lk')
        if ($email && str_contains($email, '@')) {
            $domain = explode('@', $email)[1] ?? null;
            if ($domain) {
                $company = Company::where('email', 'LIKE', '%' . $domain . '%')
                    ->orWhere('company_name', 'LIKE', '%' . explode('.', $domain)[0] . '%')
                    ->first();
                if ($company) {
                    return $company;
                }
            }
        }

        // 4. Default Active Company
        return Company::where('status', 'Active')->first()
            ?? Company::first()
            ?? Company::create([
                'company_code' => 'COMP01',
                'company_name' => 'Main Enterprise Company',
                'status' => 'Active',
            ]);
    }

    /**
     * Ensure Employee record exists, sync Department, & queue ZKTeco ADMS hardware sync commands.
     */
    protected function ensureEmployeeSyncedToBiometrics(User $user, array $claims, string $role, Company $company, bool $isNewUser): ?Employee
    {
        $employee = Employee::where('user_id', $user->id)
            ->orWhere('email', $user->email)
            ->first();

        $name = $claims['name'] ?? ($claims['given_name'] ?? $user->username);
        $nameParts = explode(' ', trim($name));

        $firstName = $claims['given_name'] ?? ($nameParts[0] ?? $user->username);
        $lastName = $claims['family_name'] ?? (count($nameParts) > 1 ? implode(' ', array_slice($nameParts, 1)) : '');

        $privilege = ($role === 'Admin') ? 14 : 0; // ZKTeco ADMS: 14 = Admin, 0 = Standard User

        // Resolve Department from WSO2 IS Claims
        $deptClaim = $claims['department'] 
            ?? $claims['urn:ietf:params:scim:schemas:extension:enterprise:2.0:User:department'] 
            ?? null;

        $department = null;
        if (!empty($deptClaim)) {
            $department = Department::where('company_id', $company->id)
                ->where(function($q) use ($deptClaim) {
                    $q->where('department_name', 'LIKE', '%' . $deptClaim . '%')
                      ->orWhere('department_code', $deptClaim);
                })
                ->first();

            if (!$department) {
                // Auto-create department for company if missing
                $department = Department::create([
                    'company_id' => $company->id,
                    'department_code' => strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $deptClaim), 0, 4)) ?: 'DEPT',
                    'department_name' => $deptClaim,
                ]);
            }
        }

        if (!$department) {
            $department = Department::where('company_id', $company->id)->first() ?? Department::first();
        }

        if (!$employee) {
            $maxPin = Employee::max('sync_pin') ?? 1000;
            $nextPin = is_numeric($maxPin) ? (int)$maxPin + 1 : 1001;

            $maxEmpNum = Employee::max('employee_number') ?? 1000;
            $nextEmpNum = is_numeric($maxEmpNum) ? (int)$maxEmpNum + 1 : 1001;

            $employee = Employee::create([
                'user_id' => $user->id,
                'company_id' => $company->id,
                'department_id' => $department->id,
                'employee_number' => $nextEmpNum,
                'employee_id' => 'EMP-' . str_pad($user->id, 4, '0', STR_PAD_LEFT),
                'sync_pin' => (string)$nextPin,
                'device_user_id' => (string)$nextPin,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $user->email,
                'company_email' => $user->email,
                'employment_status' => 'Onboarding',
                'status' => 'Active',
                'privilege' => $privilege,
                'biometric_status' => 'Pending Sync',
            ]);
        } else {
            $employee->update([
                'user_id' => $user->id,
                'company_id' => $company->id,
                'department_id' => $department->id,
                'privilege' => $privilege,
            ]);
        }

        try {
            SyncEmployeeToDevices::dispatch($employee->id, [], 'create');
        } catch (\Exception $e) {
            Log::warning('Failed to dispatch SyncEmployeeToDevices job: ' . $e->getMessage());
        }

        return $employee;
    }
}
