# Attendance & Workforce Management System (AMS)
## Document 02: System Reference Document (SRD)

| Document Metadata | Details |
| :--- | :--- |
| **System Name** | Attendance Management System (AMS) - Biometric, Workforce & Payroll Platform |
| **Current Version** | V2.4 (Enterprise Edition) |
| **Engine** | Laravel 12.x / PHP 8.2+ / MySQL 8.0+ / MariaDB 10.4+ |
| **Document Purpose** | Comprehensive System Architecture, Data Catalog, API Specification & Security Reference |
| **Author** | Senior Engineering & Architecture Team |
| **Classification** | Confidential / Technical Reference Manual |

---

## 1. Architectural Design & Topology

The **Attendance Management System (AMS)** is architected as an event-driven, multi-tenant enterprise system bridging physical IoT biometric hardware (ZKTeco SenseFace M2F-LR) with modern web-based workforce administration, advanced scheduling, and statutory financial compliance.

```mermaid
graph TB
    subgraph EdgeHardware ["Physical IoT Hardware Layer"]
        Terminal1["ZKTeco SenseFace M2F-LR<br/>(Push SDK 2.0 / ADMS)"]
        Terminal2["ZKTeco Biometric Device<br/>(Fingerprint / Face / RFID)"]
        TerminalN["Multi-Branch Terminals<br/>(Head Office, Regional Sites)"]
    end

    subgraph IngressGateway ["Network Ingress & Reverse Proxy"]
        LB["Nginx / Apache Reverse Proxy<br/>Port 80 (HTTP) & Port 443 (HTTPS)"]
        SSL["TLS Termination & Rate Limiting<br/>(throttle:adms, throttle:login)"]
    end

    subgraph LaravelCore ["Core Application Framework (Laravel 12)"]
        subgraph HardwareInterface ["Biometric Protocol Ingress"]
            AdmsController["AdmsController<br/>(/iclock/* & /adms/*)"]
            AdmsCmdService["AdmsCommandService<br/>(Command String Generator)"]
            DeviceWatcher["Device Status & Command Timeouts"]
        end

        subgraph CoreBusinessEngines ["Domain Business Services"]
            ShiftResolver["ShiftResolverService<br/>(Hierarchy & Roster Resolution)"]
            AttProcessor["AttendanceProcessingService<br/>(Punch Windows & Summaries)"]
            PayrollCalc["PayrollCalculationService<br/>(EPF, ETF, APIT, Allowances)"]
            PermissionEngine["PermissionService<br/>(RBAC, Scopes, Access Levels)"]
        end

        subgraph UserInterfaces ["Web Interface & Portal"]
            AdminConsole["Enterprise Management Console<br/>(Blade, Tailwind, Vanilla JS)"]
            EmployeePortal["Self-Service Portal (/portal/*)<br/>(Web Punch, Leaves, Payslips)"]
            SwaggerDocs["L5-Swagger API Documentation<br/>(/api/documentation)"]
        end
    end

    subgraph DataStorageCluster ["Persistence & Storage Tier"]
        DB[("MySQL / MariaDB<br/>emsprimeone_fp")]
        FileStorage[("Encrypted File Storage<br/>(Backups, Documents, Payslip PDFs)")]
    end

    subgraph BackgroundWorkers ["Task Automation Tier"]
        Cron["Laravel Scheduler (routes/console.php)<br/>(Heartbeat Sync, Timeouts, Backups)"]
        Queue["Async Queue Worker<br/>(SyncEmployeeToDevices Job)"]
    end

    Terminal1 -- "HTTP GET/POST (/iclock/cdata)" --> LB
    Terminal2 -- "HTTP GET/POST (/iclock/cdata)" --> LB
    TerminalN -- "HTTP GET/POST (/iclock/cdata)" --> LB
    LB --> SSL --> AdmsController

    AdmsController --> AdmsCmdService
    AdmsController --> ShiftResolver
    ShiftResolver --> AttProcessor
    AttProcessor --> DB

    Cron --> DeviceWatcher
    DeviceWatcher --> DB
    Queue --> AdmsCmdService

    AdminConsole --> PermissionEngine
    EmployeePortal --> PermissionEngine
    PermissionEngine --> DB
    PayrollCalc --> DB
    PayrollCalc --> FileStorage
```

---

## 2. Comprehensive Database Schema & Entity Catalog

The AMS database comprises 101 migration phases organized across 8 relational domains. All tables use InnoDB storage engine with `utf8mb4_unicode_ci` character encoding.

### 2.1 Core Identity, Security & RBAC Domain

```mermaid
erDiagram
    users ||--o{ user_permissions : "has direct"
    users ||--o{ user_permission_history : "tracks changes"
    users ||--o{ login_history : "records session"
    roles ||--o{ role_permissions : "contains"
    roles ||--o{ access_levels : "mapped to"
    permissions ||--o{ role_permissions : "granted to"
    permissions ||--o{ user_permissions : "assigned to"
    permissions ||--o{ access_level_permissions : "scoped by"
    permission_categories ||--o{ permissions : "categorizes"
    permission_templates ||--o{ permission_template_items : "groups"

    users {
        bigint id PK
        string name
        string username UK
        string email UK
        string password
        string role
        bigint access_level_id FK
        boolean is_active
        boolean must_change_password
        timestamp last_password_change
        timestamp created_at
    }

    roles {
        bigint id PK
        string name UK
        string slug UK
        string description
        integer hierarchy_level
        boolean is_system
    }

    permissions {
        bigint id PK
        bigint category_id FK
        string name UK
        string label
        string description
        string module
    }

    access_levels {
        bigint id PK
        string name
        string code UK
        integer level_rank
        string description
    }

    audit_logs {
        bigint id PK
        bigint user_id FK
        string action
        string module
        string record_id
        longtext old_value
        longtext new_value
        string ip_address
        string user_agent
        timestamp created_at
    }

    login_history {
        bigint id PK
        bigint user_id FK
        string ip_address
        string user_agent
        string login_status
        timestamp login_at
        timestamp logout_at
    }
```

### 2.2 Organization & Workforce Hierarchy Domain

```mermaid
erDiagram
    companies ||--o{ branches : "has"
    companies ||--o{ departments : "owns"
    companies ||--o{ employees : "employs"
    branches ||--o{ employees : "stationed at"
    departments ||--o{ employees : "assigned to"
    designations ||--o{ employees : "categorizes"
    employees ||--o{ employee_documents : "maintains"
    employees ||--o{ employee_assets : "assigned"
    employees ||--o{ employee_agreements : "signs"
    employees ||--o{ employee_transfers : "experiences"
    employees ||--o{ employee_resignations : "submits"
    employees ||--o{ employee_terminations : "undergoes"

    companies {
        bigint id PK
        string company_code UK
        string company_name
        string registration_number
        string tax_number
        string logo_path
        string brand_primary_color
        string brand_secondary_color
        string contact_email
        string contact_phone
        boolean is_active
    }

    branches {
        bigint id PK
        bigint company_id FK
        string branch_code
        string branch_name
        string address
        string city
        string phone
        string status
    }

    departments {
        bigint id PK
        bigint company_id FK
        string department_code
        string department_name
        string status
    }

    employees {
        bigint id PK
        bigint user_id FK "Nullable"
        bigint company_id FK
        bigint branch_id FK
        bigint department_id FK
        bigint designation_id FK
        bigint shift_id FK
        bigint secondary_shift_id FK "Nullable"
        string employee_id UK
        string employee_number
        string sync_pin "Numeric PIN for terminal"
        string first_name
        string last_name
        string email UK "Nullable"
        string nic_number UK "Nullable"
        string mobile_phone
        date date_of_birth
        date date_of_joining
        enum gender "Male, Female, Other"
        enum work_mode "Onsite, Remote, Hybrid"
        boolean web_punch_allowed
        string bank_name
        string bank_branch_code
        string bank_account_number
        enum status "Active, Suspended, Terminated, Resigned"
    }
```

### 2.3 Biometric Hardware & Device Protocol Domain

| Table Name | Primary Purpose | Key Fields |
| :--- | :--- | :--- |
| `devices` | Registers physical biometric terminals and stores hardware telemetry | `id`, `company_id`, `branch_id`, `device_name`, `device_serial_number` (UK), `ip_address`, `public_ip_address`, `port`, `firmware_version`, `user_count`, `face_count`, `fingerprint_count`, `device_attendance_count`, `status` (`Online`, `Offline`, `Pending Approval`, `Disabled`), `health_score`, `last_seen` |
| `device_commands` | Outbound asynchronous FIFO queue for ZKTeco ADMS terminal commands | `id`, `device_id` (FK), `command` (Text), `status` (`pending`, `sent`, `completed`, `failed`, `timeout`), `retry_count`, `sent_at`, `completed_at`, `execution_time_ms`, `failure_reason` |
| `device_event_logs` | Audit trail of raw hardware heartbeats, connection attempts, and alarms | `id`, `device_id` (FK), `event_type`, `severity` (`Info`, `Warning`, `Critical`), `event_message`, `raw_payload` (LongText), `created_at` |
| `biometric_templates` | Stores binary/encoded biometric template data for centralized backup & sync | `id`, `employee_id` (FK), `device_id` (FK), `biometric_type` (`Face`, `Fingerprint`, `Palm`, `Card`), `finger_index`, `template_data` (LongText), `template_version`, `checksum_sha256` |
| `device_groups` | Logical grouping of terminals for mass schedule or user synchronization | `id`, `group_name`, `description` |
| `device_settings` | Terminal-specific operational flags (volume, language, timeout) | `id`, `device_id` (FK), `setting_key`, `setting_value` |

### 2.4 Scheduling & Attendance Engine Domain

```mermaid
erDiagram
    shifts ||--o{ shift_breaks : "contains"
    shifts ||--o{ employee_shift_assignments : "assigned via"
    shifts ||--o{ employee_schedule_overrides : "overridden with"
    employees ||--o{ employee_shift_assignments : "rostered with"
    employees ||--o{ employee_schedule_overrides : "assigned"
    employees ||--o{ attendance_logs : "punches"
    employees ||--o{ daily_attendance_summaries : "aggregated in"
    employees ||--o{ manual_logs : "requests"
    employees ||--o{ wfh_requests : "submits"

    shifts {
        bigint id PK
        bigint company_id FK
        string shift_name
        time start_time
        time end_time
        time first_half_end "Boundary for half day"
        time late_tolerance_time "Grace period threshold"
        time early_in_threshold "Earliest allowable punch"
        time overtime_start "OT calculation threshold"
        boolean is_cross_midnight "True for overnight shifts"
        boolean is_flexible
        integer required_work_hours
    }

    attendance_logs {
        bigint id PK
        bigint employee_id FK
        bigint device_id FK "Nullable for web punch"
        string device_user_id
        date attendance_date
        time attendance_time
        datetime attendance_timestamp
        enum verification_method "Face, Fingerprint, RFID Card, PIN, Web, Manual"
        string verify_code
        string device_serial
        enum attendance_type "Check-In, Check-Out, Break-In, Break-Out"
        enum attendance_status "Present, Late, Early Out, Overtime, Invalid"
        text raw_data
        string ip_address
        decimal latitude "Web punch"
        decimal longitude "Web punch"
    }

    daily_attendance_summaries {
        bigint id PK
        bigint employee_id FK
        date attendance_date
        datetime first_in
        datetime last_out
        decimal total_work_hours
        integer total_break_minutes
        integer late_minutes
        integer early_out_minutes
        integer overtime_minutes
        enum status "PRESENT, ABSENT, HALF_DAY, FIRST_HALF, SECOND_HALF, LEAVE, HOLIDAY, WEEKEND, OFF_DAY, INCOMPLETE"
        string schedule_source "OVERRIDE, ASSIGNMENT, WEEKLY, DEFAULT"
        integer calculation_version
    }
```

### 2.5 Sri Lankan Statutory Payroll Domain

| Table Name | Primary Purpose | Key Fields |
| :--- | :--- | :--- |
| `salary_profiles` | Base compensation parameters and recurring allowances/deductions | `id`, `employee_id` (FK), `basic_salary`, `budgetary_relief_allowance`, `travel_allowance`, `food_allowance`, `attendance_incentive`, `is_epf_eligible`, `effective_date`, `status` |
| `payroll_periods` | Monthly payroll execution containers and governance cycle | `id`, `company_id` (FK), `period_code` (`2026-06`), `start_date`, `end_date`, `cut_off_date`, `working_days`, `status` (`Draft`, `Processing`, `HR Review`, `Approved`, `Locked`), `approved_by`, `approved_at` |
| `payroll_runs` | Individual calculation batch run history with audit parameters | `id`, `payroll_period_id` (FK), `run_type` (`Full`, `Selective`, `Recalculate`), `processed_employees_count`, `total_net_payable`, `status` |
| `payslips` | Fully computed monthly payslip records | `id`, `uuid` (Verifiable token), `payroll_period_id` (FK), `employee_id` (FK), `basic_salary`, `nopay_days`, `nopay_deduction`, `gross_salary`, `epf_employee_amount` (8%), `epf_employer_amount` (12%), `etf_employer_amount` (3%), `apit_tax_amount`, `normal_ot_hours`, `double_ot_hours`, `total_ot_amount`, `net_salary`, `qr_code_hash` |
| `tax_years` & `tax_brackets` | Sri Lanka Inland Revenue Department (IRD) APIT progressive tax brackets | `id`, `tax_year_id` (FK), `bracket_order`, `min_income`, `max_income`, `tax_rate_percentage`, `base_tax_amount` |

---

## 3. Hardware & Communication API Catalog

### 3.1 ZKTeco ADMS Protocol Endpoints

These endpoints service direct communication from physical biometric terminals using the ZKTeco PUSH / ADMS protocol.

#### 1. Device Handshake & Configuration Sync
- **Endpoint**: `GET /iclock/cdata` (Alias: `GET /adms/cdata`)
- **Middleware**: `throttle:adms` (Exempt from CSRF)
- **Parameters**:
  * `SN` (Query, Required): Device Serial Number (e.g., `SN=VAL_M2FLR_999`).
- **Response**:
  ```http
  HTTP/1.1 200 OK
  Content-Type: text/plain

  GET OPTION FROM: VAL_M2FLR_999
  Stamp=9999
  OpStamp=9999
  PhotoStamp=9999
  ErrorDelay=60
  Delay=30
  TransTimes=00:00;14:05
  TransInterval=1
  TransFlag=TransData AttLog\tOpLog\tAttPhoto\tEnrollUser\tChgUser\tEnrollFP\tChgFP\tFACE\tUserPic
  TimeZone=330
  Realtime=1
  Encrypt=0
  ```

#### 2. Real-Time Transaction Push (Attendance Log Upload)
- **Endpoint**: `POST /iclock/cdata` (Alias: `POST /adms/cdata`)
- **Query Parameters**: `SN={Serial}&table=ATTLOG`
- **Request Body (Tab-delimited text)**:
  ```text
  <DeviceUserId>\t<Timestamp>\t<PunchType>\t<VerifyType>\t<WorkCode>\t<Reserved>
  P1-01\t2026-06-15 08:30:00\t0\t15\t0\t0
  P1-02\t2026-06-15 08:50:00\t0\t1\t0\t0
  ```
- **Verification Type Mapping**:
  * `15`: Face Scan
  * `1`: Fingerprint Scan
  * `4`: RFID Smart Card
  * `3`: Password / PIN
- **Response**:
  ```http
  HTTP/1.1 200 OK
  Content-Type: text/plain

  OK: 2
  ```

#### 3. Outbound Command Polling (Device Heartbeat)
- **Endpoint**: `GET /iclock/getrequest` (Alias: `GET /adms/getrequest`)
- **Query Parameters**: `SN={Serial}`
- **Behavior**:
  1. Updates the device `last_seen` timestamp and captures client `public_ip_address`.
  2. If status is `Offline`, transitions device status to `Online`.
  3. Queries the `device_commands` table for oldest `pending` command targeted to this terminal.
  4. Marks command status as `sent` and returns command payload.
- **Response (When command is pending)**:
  ```http
  HTTP/1.1 200 OK
  Content-Type: text/plain

  C:142:DATA UPDATE USERINFO PIN=101	Name=John Doe	Pri=0	Grp=1	TZ=0
  ```
- **Response (When no commands pending)**:
  ```http
  HTTP/1.1 200 OK
  Content-Type: text/plain

  OK
  ```

#### 4. Command Execution Callback
- **Endpoint**: `POST /iclock/devicecmd` (Alias: `POST /adms/devicecmd`)
- **Query Parameters**: `SN={Serial}`
- **Request Body**:
  ```text
  ID=142&Return=0&CMD=DATA UPDATE USERINFO
  ```
- **Behavior**: Parses command return code (`Return=0` indicates success; non-zero indicates error), updates `execution_time_ms`, updates terminal storage/user counts, and marks command as `completed` or `failed`.
- **Response**: `OK`

---

## 4. Security, Access Control & Governance Matrix

### 4.1 Role-Based Access Control (RBAC) Architecture

The system enforces a 3-dimensional authorization model:
$$\text{Authorized} \iff \text{Role Has Permission} \land \text{User Access Level} \ge \text{Action Level} \land \text{Resource Within Scope}$$

| Role | Intended Users | Primary Capabilities |
| :--- | :--- | :--- |
| **Super Administrator** | Group IT & Systems Lead | Full access across all companies, system settings, database backups, audit logs, and hardware device registration. Account protected against deletion. |
| **Company Administrator** | Subsidiary HR Director / Operations Head | Company-wide workforce management, shift configurations, attendance approvals, and payroll period execution within assigned `company_id`. |
| **Branch / Site Manager** | Facility / Factory Managers | View branch attendance, approve manual punch requests, assign daily rosters, and inspect local device statuses. |
| **Department Head** | Department Managers / Team Leads | Review team leaves, approve WFH requests, view department daily attendance summaries. |
| **Payroll Officer** | Finance & Payroll Specialists | Review attendance variations, manage salary profiles, process monthly payroll runs, generate statutory EPF/ETF reports, and download bank export files. |
| **Employee** | General Staff Members | Restricted to Self-Service Portal (`/portal/*`): view personal roster, submit leave/WFH requests, clock web punch (if enabled), and download PDF payslips. |

### 4.2 Core Permission Taxonomy

Permissions are formatted using standard dot-notation: `<module>.<action>`

- `dashboard.view`
- `company.view`, `company.create`, `company.edit`, `company.delete`
- `employee.view`, `employee.create`, `employee.edit`, `employee.delete`
- `device.view`, `device.register`, `device.approve`, `device.disable`, `device.commands`
- `attendance.view`, `attendance.edit`, `attendance.delete`, `attendance.recalculate`
- `payroll.view`, `payroll.process`, `payroll.approve`, `payroll.lock`
- `admin.users`, `admin.roles`, `admin.permissions`, `admin.settings`, `admin.backups`

---

## 5. Background Automation & Scheduled Tasks

All system automation routines are registered in `routes/console.php` and executed via the unified Laravel Scheduler.

```mermaid
gantt
    title Scheduled Task Execution Frequency
    dateFormat HH:mm
    axisFormat %H:%M

    section Every Minute
    app:check-command-timeouts :active, 00:00, 00:01
    app:update-device-statuses :active, 00:00, 00:01

    section Daily 01:00 AM
    app:backup (Automated DB Dump) :01:00, 01:10

    section Daily 02:00 AM
    holidays:sync (Current Year) :02:00, 02:05
    holidays:sync (Upcoming Year) :02:15, 02:20

    section Daily Midnight
    app:cleanup-old-events (30d Purge) :00:05, 00:10
```

| Schedule Expression | Artisan Command | Description |
| :--- | :--- | :--- |
| `* * * * *` (Every Minute) | `app:check-command-timeouts` | Inspects outbound commands in `sent` state older than 180 seconds; increments `retry_count` or marks `timeout`. |
| `* * * * *` (Every Minute) | `app:update-device-statuses` | Checks `last_seen` timestamp of all terminals; sets status to `Offline` if no heartbeat received for > 10 minutes. |
| `0 1 * * *` (Daily at 01:00) | `app:backup` | Generates a compressed MySQL dump in `storage/app/backups/`, verifies dump integrity, and updates `backups` catalog. |
| `0 2 * * *` (Daily at 02:00) | `holidays:sync` | Queries government/commercial holiday APIs to update `holidays` table for current calendar year. |
| `15 2 * * *` (Daily at 02:15) | `holidays:sync --year={next}` | Synchronizes statutory holiday dates for the upcoming calendar year. |
| `0 0 * * *` (Daily Midnight) | `app:cleanup-old-events` | Soft-deletes `device_event_logs` records older than 30 days to optimize database index sizing. |

---

## 6. Audit Logging & System Diagnostics Architecture

### 6.1 Audit Trail Implementation
Audit logs are recorded synchronously via `App\Models\AuditLog` upon all administrative state mutations:
```php
AuditLog::create([
    'user_id'    => auth()->id() ?? 1,
    'action'     => 'UPDATE_SALARY_PROFILE',
    'module'     => 'Payroll Management',
    'record_id'  => $profile->id,
    'old_value'  => json_encode($originalAttributes),
    'new_value'  => json_encode($updatedAttributes),
    'ip_address' => request()->ip(),
    'user_agent' => request()->userAgent(),
]);
```

### 6.2 Hardware Event Logging
Hardware interactions are recorded in `device_event_logs` with severity levels:
- `Info`: Standard device connection, successful command completion, handshake.
- `Warning`: Retried commands, heartbeat delays, firmware configuration mismatch.
- `Critical`: Terminal tamper alerts, storage capacity $\ge 95\%$, repeated command rejections.

### 6.3 Diagnostic Artisan Commands
The platform includes built-in diagnostic and validation commands:
```bash
# Execute comprehensive 8-phase production readiness test
php artisan app:validate-production

# Simulate biometric hardware traffic from mock terminal
php artisan app:simulate-adms-device --sn=SIM_M2F_001

# Inspect and verify payroll calculations against statutory formulas
php artisan app:verify-payroll-migration
```
