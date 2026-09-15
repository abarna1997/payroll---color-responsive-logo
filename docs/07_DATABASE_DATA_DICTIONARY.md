# Attendance & Workforce Management System (AMS)
## Document 07: Database Data Dictionary

| Document Metadata | Details |
| :--- | :--- |
| **System Name** | Attendance Management System (AMS) - Biometric, Workforce & Payroll Platform |
| **Current Version** | V2.4 (Enterprise Edition) |
| **Database Engine** | MySQL 8.0+ / MariaDB 10.4+ (InnoDB Engine) |
| **Default Database** | `emsprimeone_fp` |
| **Collation & Charset** | `utf8mb4_unicode_ci` / `utf8mb4` |
| **Intended Audience** | Database Administrators, Backend Engineers, Data Integration Leads |
| **Classification** | Technical Reference Data Dictionary |

---

## 1. Database Standards & Conventions

1. **Primary Keys**: Every relational table utilizes an unsigned auto-incrementing 64-bit integer (`bigint unsigned`) named `id`.
2. **Foreign Keys**: Named using the singular form of the referenced table suffixed with `_id` (e.g., `company_id`, `employee_id`, `shift_id`). Enforces `ON DELETE RESTRICT` or `ON DELETE CASCADE` where specified in migration files.
3. **Audit Timestamps**: All entities include standard Laravel `created_at` and `updated_at` nullable timestamps. Soft deletes utilize `deleted_at` (`SoftDeletes` trait).
4. **Boolean Representation**: Stored as `tinyint(1)` where `0 = false` and `1 = true`.
5. **Monetary Precision**: All financial and salary calculations use `decimal(12, 2)` or `decimal(10, 2)` to eliminate floating-point approximation inaccuracies.

---

## 2. Core Identity & Governance Schema

### 2.1 Table: `users`
*System user accounts and web authentication credentials.*

| Column Name | Data Type | Nullable | Default | Constraints | Description |
| :--- | :--- | :--- | :--- | :--- | :--- |
| `id` | `BIGINT UNSIGNED` | No | Auto | **PK** | Internal surrogate identifier |
| `name` | `VARCHAR(255)` | No | None | | User display name |
| `username` | `VARCHAR(100)` | No | None | **UNIQUE** | Unique login handle (e.g. `Prime1-admin`) |
| `email` | `VARCHAR(255)` | No | None | **UNIQUE** | Corporate email address |
| `password` | `VARCHAR(255)` | No | None | | Bcrypt password hash (cost factor 12) |
| `role` | `VARCHAR(50)` | No | `'Employee'` | | High-level role classification |
| `access_level_id` | `BIGINT UNSIGNED` | Yes | `NULL` | **FK** $\rightarrow$ `access_levels(id)` | Hierarchical security clearance level |
| `is_active` | `TINYINT(1)` | No | `1` | | `1` if account permitted to login; `0` if locked |
| `must_change_password` | `TINYINT(1)` | No | `0` | | `1` forces user to change password on next visit |
| `last_password_change` | `DATETIME` | Yes | `NULL` | | Timestamp of last password modification |
| `remember_token` | `VARCHAR(100)` | Yes | `NULL` | | Laravel persistent session cookie token |
| `created_at`, `updated_at` | `TIMESTAMP` | Yes | `NULL` | | Audit creation and modification timestamps |

---

### 2.2 Table: `audit_logs`
*Non-repudiable audit logging recording all administrative state changes.*

| Column Name | Data Type | Nullable | Default | Constraints | Description |
| :--- | :--- | :--- | :--- | :--- | :--- |
| `id` | `BIGINT UNSIGNED` | No | Auto | **PK** | Log record identifier |
| `user_id` | `BIGINT UNSIGNED` | Yes | `NULL` | **FK** $\rightarrow$ `users(id)` | User who executed the modification |
| `action` | `VARCHAR(100)` | No | None | | Event code (e.g., `UPDATE_SALARY`, `CREATE_BRANCH`) |
| `module` | `VARCHAR(100)` | No | None | | Functional module (e.g., `Payroll`, `Workforce`) |
| `record_id` | `VARCHAR(50)` | Yes | `NULL` | | Primary key of modified entity |
| `old_value` | `LONGTEXT` | Yes | `NULL` | | JSON snapshot of entity attributes before edit |
| `new_value` | `LONGTEXT` | Yes | `NULL` | | JSON snapshot of entity attributes after edit |
| `ip_address` | `VARCHAR(45)` | Yes | `NULL` | | Client IPv4 or IPv6 address |
| `user_agent` | `VARCHAR(255)` | Yes | `NULL` | | Client browser / device user agent |
| `created_at` | `TIMESTAMP` | Yes | `NULL` | | Exact timestamp event was committed |

---

## 3. Organization & Workforce Schema

### 3.1 Table: `companies`
*Multi-tenant enterprise company definitions.*

| Column Name | Data Type | Nullable | Default | Constraints | Description |
| :--- | :--- | :--- | :--- | :--- | :--- |
| `id` | `BIGINT UNSIGNED` | No | Auto | **PK** | Company record identifier |
| `company_code` | `VARCHAR(20)` | No | None | **UNIQUE** | Corporate abbreviation (e.g., `P1`, `A1`) |
| `company_name` | `VARCHAR(255)` | No | None | | Full corporate legal entity name |
| `registration_number` | `VARCHAR(100)` | Yes | `NULL` | | Government corporate registration number |
| `tax_number` | `VARCHAR(100)` | Yes | `NULL` | | Inland Revenue Department VAT/TIN number |
| `logo_path` | `VARCHAR(255)` | Yes | `NULL` | | Relative storage path to corporate logo |
| `brand_primary_color` | `VARCHAR(10)` | Yes | `'#1E3A8A'` | | Hex color code for UI and payslip branding |
| `contact_email` | `VARCHAR(255)` | Yes | `NULL` | | Official HR / corporate contact email |
| `is_active` | `TINYINT(1)` | No | `1` | | Status flag |

---

### 3.2 Table: `employees`
*Master employee directory, bio-data, and organizational stationing.*

| Column Name | Data Type | Nullable | Default | Constraints | Description |
| :--- | :--- | :--- | :--- | :--- | :--- |
| `id` | `BIGINT UNSIGNED` | No | Auto | **PK** | Employee surrogate identifier |
| `user_id` | `BIGINT UNSIGNED` | Yes | `NULL` | **FK** $\rightarrow$ `users(id)` | Linked self-service user login account |
| `company_id` | `BIGINT UNSIGNED` | No | None | **FK** $\rightarrow$ `companies(id)` | Parent company |
| `branch_id` | `BIGINT UNSIGNED` | No | None | **FK** $\rightarrow$ `branches(id)` | Physical site / branch office stationed |
| `department_id` | `BIGINT UNSIGNED` | No | None | **FK** $\rightarrow$ `departments(id)` | Department assigned |
| `designation_id` | `BIGINT UNSIGNED` | Yes | `NULL` | **FK** $\rightarrow$ `designations(id)` | Official job designation |
| `shift_id` | `BIGINT UNSIGNED` | Yes | `NULL` | **FK** $\rightarrow$ `shifts(id)` | Default primary shift configuration |
| `secondary_shift_id` | `BIGINT UNSIGNED` | Yes | `NULL` | **FK** $\rightarrow$ `shifts(id)` | Alternate shift configuration |
| `employee_id` | `VARCHAR(50)` | No | None | **UNIQUE** | Corporate employee code (e.g. `P1-01`) |
| `employee_number` | `VARCHAR(50)` | Yes | `NULL` | | Numeric sequence within company |
| `sync_pin` | `VARCHAR(20)` | Yes | `NULL` | | Strict numeric PIN pushed to ZKTeco terminals |
| `first_name` | `VARCHAR(100)` | No | None | | Employee given name |
| `last_name` | `VARCHAR(100)` | No | None | | Employee surname |
| `email` | `VARCHAR(255)` | Yes | `NULL` | **UNIQUE** | Official corporate email address |
| `nic_number` | `VARCHAR(50)` | Yes | `NULL` | **UNIQUE** | Sri Lankan National Identity Card Number |
| `date_of_birth` | `DATE` | Yes | `NULL` | | Date of birth (age verification) |
| `date_of_joining` | `DATE` | Yes | `NULL` | | Official employment start date |
| `gender` | `ENUM` | No | `'Male'` | | `'Male'`, `'Female'`, `'Other'` |
| `work_mode` | `ENUM` | No | `'Onsite'` | | `'Onsite'`, `'Remote'`, `'Hybrid'` |
| `web_punch_allowed` | `TINYINT(1)` | No | `0` | | If `1`, user may clock in via web portal |
| `bank_name` | `VARCHAR(100)` | Yes | `NULL` | | Commercial bank name for salary deposit |
| `bank_branch_code` | `VARCHAR(20)` | Yes | `NULL` | | Commercial bank branch routing number |
| `bank_account_number` | `VARCHAR(50)` | Yes | `NULL` | | Employee bank account number |
| `status` | `ENUM` | No | `'Active'` | | `'Active'`, `'Suspended'`, `'Terminated'`, `'Resigned'` |

---

## 4. Biometric Device & ADMS Schema

### 4.1 Table: `devices`
*Physical biometric terminals registered in the cloud ecosystem.*

| Column Name | Data Type | Nullable | Default | Constraints | Description |
| :--- | :--- | :--- | :--- | :--- | :--- |
| `id` | `BIGINT UNSIGNED` | No | Auto | **PK** | Internal device identifier |
| `company_id` | `BIGINT UNSIGNED` | Yes | `NULL` | **FK** $\rightarrow$ `companies(id)` | Company owning this device |
| `branch_id` | `BIGINT UNSIGNED` | Yes | `NULL` | **FK** $\rightarrow$ `branches(id)` | Physical branch site installed |
| `device_name` | `VARCHAR(100)` | No | None | | Friendly terminal label |
| `device_serial_number`| `VARCHAR(100)` | No | None | **UNIQUE** | ZKTeco hardware Serial Number (`SN`) |
| `ip_address` | `VARCHAR(45)` | Yes | `NULL` | | Local subnet IP reported by terminal |
| `public_ip_address` | `VARCHAR(45)` | Yes | `NULL` | | WAN IP captured from inbound HTTP push |
| `firmware_version` | `VARCHAR(100)` | Yes | `NULL` | | Terminal firmware / Push SDK build |
| `user_count` | `INT` | No | `0` | | Total employee records stored on terminal |
| `face_count` | `INT` | No | `0` | | Total facial biometric templates stored |
| `fingerprint_count` | `INT` | No | `0` | | Total fingerprint templates stored |
| `device_attendance_count`| `INT` | No | `0` | | Total historical attendance scans stored |
| `storage_capacity` | `BIGINT` | Yes | `NULL` | | Total flash memory capacity in bytes |
| `storage_used` | `BIGINT` | Yes | `NULL` | | Used flash memory in bytes |
| `status` | `ENUM` | No | `'Pending Approval'`| | `'Online'`, `'Offline'`, `'Pending Approval'`, `'Disabled'` |
| `health_score` | `INT` | No | `100` | | Dynamic hardware health score ($0 - 100\%$) |
| `last_seen` | `DATETIME` | Yes | `NULL` | | Timestamp of most recent HTTP heartbeat |

---

### 4.2 Table: `device_commands`
*Outbound asynchronous FIFO command queue dispatched to terminals.*

| Column Name | Data Type | Nullable | Default | Constraints | Description |
| :--- | :--- | :--- | :--- | :--- | :--- |
| `id` | `BIGINT UNSIGNED` | No | Auto | **PK** | Command identifier |
| `device_id` | `BIGINT UNSIGNED` | No | None | **FK** $\rightarrow$ `devices(id)` | Destination terminal |
| `command` | `TEXT` | No | None | | Exact ZKTeco ADMS command string |
| `status` | `ENUM` | No | `'pending'` | | `'pending'`, `'sent'`, `'completed'`, `'failed'`, `'timeout'` |
| `retry_count` | `INT` | No | `0` | | Number of dispatch retry attempts |
| `sent_at` | `DATETIME` | Yes | `NULL` | | Timestamp command was delivered to terminal |
| `completed_at` | `DATETIME` | Yes | `NULL` | | Timestamp terminal confirmed execution |
| `execution_time_ms` | `INT` | Yes | `NULL` | | Round-trip processing time in milliseconds |
| `failure_reason` | `TEXT` | Yes | `NULL` | | Error message or non-zero return code |

---

## 5. Attendance & Shift Engine Schema

### 5.1 Table: `shifts`
*Shift schedule definitions, timing boundaries, and tolerances.*

| Column Name | Data Type | Nullable | Default | Constraints | Description |
| :--- | :--- | :--- | :--- | :--- | :--- |
| `id` | `BIGINT UNSIGNED` | No | Auto | **PK** | Shift identifier |
| `company_id` | `BIGINT UNSIGNED` | No | None | **FK** $\rightarrow$ `companies(id)` | Organization owning shift |
| `shift_name` | `VARCHAR(100)` | No | None | | Shift descriptive title |
| `start_time` | `TIME` | No | None | | Scheduled start time (`HH:MM:SS`) |
| `end_time` | `TIME` | No | None | | Scheduled end time (`HH:MM:SS`) |
| `first_half_end` | `TIME` | Yes | `NULL` | | Boundary timestamp dividing half days |
| `late_tolerance_time` | `TIME` | Yes | `NULL` | | Grace period cutoff time |
| `early_in_threshold` | `TIME` | Yes | `NULL` | | Earliest valid check-in punch |
| `overtime_start` | `TIME` | Yes | `NULL` | | Timestamp when overtime calculation triggers |
| `is_cross_midnight` | `TINYINT(1)` | No | `0` | | `1` if shift spans across midnight (overnight) |
| `required_work_hours`| `INT` | No | `8` | | Expected net work hours |

---

### 5.2 Table: `attendance_logs`
*Raw biometric scans and web punch transactions.*

| Column Name | Data Type | Nullable | Default | Constraints | Description |
| :--- | :--- | :--- | :--- | :--- | :--- |
| `id` | `BIGINT UNSIGNED` | No | Auto | **PK** | Transaction log identifier |
| `employee_id` | `BIGINT UNSIGNED` | No | None | **FK** $\rightarrow$ `employees(id)` | Matched employee |
| `device_id` | `BIGINT UNSIGNED` | Yes | `NULL` | **FK** $\rightarrow$ `devices(id)` | Biometric terminal (NULL for web punch) |
| `device_user_id` | `VARCHAR(50)` | No | None | | Raw PIN parsed from terminal payload |
| `attendance_date` | `DATE` | No | None | | Logical attendance date (`YYYY-MM-DD`) |
| `attendance_time` | `TIME` | No | None | | Punch time (`HH:MM:SS`) |
| `attendance_timestamp`| `DATETIME` | No | None | **INDEX** | Full punch datetime |
| `verification_method` | `ENUM` | No | `'Fingerprint'`| | `'Face'`, `'Fingerprint'`, `'RFID Card'`, `'PIN'`, `'Web'` |
| `verify_code` | `VARCHAR(10)` | Yes | `NULL` | | Raw hardware verification code (e.g. `15`) |
| `device_serial` | `VARCHAR(100)` | Yes | `NULL` | **INDEX** | Hardware serial number |
| `attendance_type` | `ENUM` | No | `'Check-In'` | | `'Check-In'`, `'Check-Out'`, `'Break-In'`, `'Break-Out'` |
| `attendance_status` | `ENUM` | No | `'Present'` | **INDEX** | `'Present'`, `'Late'`, `'Early Out'`, `'Overtime'` |
| `raw_data` | `TEXT` | Yes | `NULL` | | Raw unparsed payload line for auditing |
| `latitude`, `longitude`| `DECIMAL(10,8)`| Yes | `NULL` | | GPS coordinates captured during web punch |

---

### 5.3 Table: `daily_attendance_summaries`
*Aggregated daily attendance metrics driving statutory payroll calculations.*

| Column Name | Data Type | Nullable | Default | Constraints | Description |
| :--- | :--- | :--- | :--- | :--- | :--- |
| `id` | `BIGINT UNSIGNED` | No | Auto | **PK** | Summary identifier |
| `employee_id` | `BIGINT UNSIGNED` | No | None | **FK** $\rightarrow$ `employees(id)` | Employee summarized |
| `attendance_date` | `DATE` | No | None | **INDEX** | Work date summarized |
| `first_in` | `DATETIME` | Yes | `NULL` | | Earliest verified check-in timestamp |
| `last_out` | `DATETIME` | Yes | `NULL` | | Latest verified check-out timestamp |
| `total_work_hours` | `DECIMAL(5,2)` | No | `0.00` | | Net verified working hours |
| `total_break_minutes`| `INT` | No | `0` | | Total break duration in minutes |
| `late_minutes` | `INT` | No | `0` | | Minutes clocked in past shift start |
| `early_out_minutes` | `INT` | No | `0` | | Minutes clocked out prior to shift end |
| `overtime_minutes` | `INT` | No | `0` | | Verified overtime minutes |
| `status` | `VARCHAR(50)` | No | `'PRESENT'` | | `'PRESENT'`, `'ABSENT'`, `'HALF_DAY'`, `'LEAVE'`, `'HOLIDAY'`, `'OFF_DAY'` |
| `schedule_source` | `VARCHAR(50)` | Yes | `'DEFAULT'` | | `'OVERRIDE'`, `'ASSIGNMENT'`, `'WEEKLY'`, `'DEFAULT'` |

---

## 6. Sri Lankan Statutory Payroll Schema

### 6.1 Table: `payroll_periods`
*Monthly payroll cycle containers.*

| Column Name | Data Type | Nullable | Default | Constraints | Description |
| :--- | :--- | :--- | :--- | :--- | :--- |
| `id` | `BIGINT UNSIGNED` | No | Auto | **PK** | Period identifier |
| `company_id` | `BIGINT UNSIGNED` | No | None | **FK** $\rightarrow$ `companies(id)` | Company owning payroll period |
| `period_code` | `VARCHAR(20)` | No | None | | Unique period identifier (e.g. `2026-09`) |
| `start_date` | `DATE` | No | None | | Payroll start cycle date |
| `end_date` | `DATE` | No | None | | Payroll end cycle date |
| `working_days` | `INT` | No | `26` | | Statutory working days for no-pay calculations |
| `status` | `ENUM` | No | `'Draft'` | | `'Draft'`, `'Processing'`, `'HR Review'`, `'Approved'`, `'Locked'` |
| `approved_by` | `BIGINT UNSIGNED` | Yes | `NULL` | **FK** $\rightarrow$ `users(id)` | Authorizing Director |
| `approved_at` | `DATETIME` | Yes | `NULL` | | Timestamp period was formally signed off |

---

### 6.2 Table: `payslips`
*Computed monthly payslips compliant with EPF, ETF, and IRD APIT regulations.*

| Column Name | Data Type | Nullable | Default | Constraints | Description |
| :--- | :--- | :--- | :--- | :--- | :--- |
| `id` | `BIGINT UNSIGNED` | No | Auto | **PK** | Payslip identifier |
| `uuid` | `CHAR(36)` | No | None | **UNIQUE** | Cryptographically random verifiable UUIDv4 token |
| `payroll_period_id` | `BIGINT UNSIGNED` | No | None | **FK** $\rightarrow$ `payroll_periods(id)` | Associated payroll run |
| `employee_id` | `BIGINT UNSIGNED` | No | None | **FK** $\rightarrow$ `employees(id)` | Employee receiving payment |
| `basic_salary` | `DECIMAL(12,2)`| No | `0.00` | | Base salary from active salary profile |
| `nopay_days` | `DECIMAL(4,1)` | No | `0.0` | | Total absent / unapproved no-pay days |
| `nopay_deduction` | `DECIMAL(12,2)`| No | `0.00` | | Deducted amount: $(\text{Basic} / \text{Days}) \times \text{NoPayDays}$ |
| `gross_salary` | `DECIMAL(12,2)`| No | `0.00` | | Total gross earnings including allowances & OT |
| `epf_employee_amount`| `DECIMAL(12,2)`| No | `0.00` | | Statutory Employee EPF deduction (**8%**) |
| `epf_employer_amount`| `DECIMAL(12,2)`| No | `0.00` | | Statutory Employer EPF contribution (**12%**) |
| `etf_employer_amount`| `DECIMAL(12,2)`| No | `0.00` | | Statutory Employer ETF contribution (**3%**) |
| `apit_tax_amount` | `DECIMAL(12,2)`| No | `0.00` | | Inland Revenue Department progressive tax |
| `normal_ot_hours` | `DECIMAL(5,2)` | No | `0.00` | | Normal overtime hours ($1.5\times$) |
| `double_ot_hours` | `DECIMAL(5,2)` | No | `0.00` | | Double / Holiday overtime hours ($2.0\times$) |
| `total_ot_amount` | `DECIMAL(12,2)`| No | `0.00` | | Combined overtime earnings |
| `net_salary` | `DECIMAL(12,2)`| No | `0.00` | | Final net take-home salary payable |
| `qr_code_hash` | `VARCHAR(255)` | Yes | `NULL` | | SHA-256 hash embedded in payslip verification QR |
