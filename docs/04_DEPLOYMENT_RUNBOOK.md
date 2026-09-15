# Attendance & Workforce Management System (AMS)
## Document 04: Runbook / Deployment Guide

| Document Metadata | Details |
| :--- | :--- |
| **System Name** | Attendance Management System (AMS) - Biometric, Workforce & Payroll Platform |
| **Current Version** | V2.4 (Enterprise Edition) |
| **Operating Environments** | Linux VPS (Ubuntu 22.04 LTS / Debian 12) & cPanel Shared Hosting (CloudLinux / AlmaLinux) |
| **Document Purpose** | Complete Production Deployment, Hardware Provisioning & Disaster Recovery Manual |
| **Author** | Senior DevOps & Systems Engineering Team |
| **Classification** | Operational Runbook / Technical Deployment Manual |

---

## 1. System Prerequisites & Infrastructure Sizing

### 1.1 Infrastructure Sizing Matrix

| Organization Sizing | Active Workforce | Terminal Count | Recommended Compute | Memory | Storage | Database Engine |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **Small / Branch Site** | Up to 150 staff | 1 – 3 Devices | 2 vCPU | 2 GB RAM | 25 GB SSD | MariaDB 10.4+ / MySQL 8.0 |
| **Mid-Market Enterprise** | 150 – 1,000 staff | 4 – 15 Devices | 4 vCPU | 4 GB RAM | 60 GB NVMe | MySQL 8.0 / Percona 8.0 |
| **Multi-Company Group** | 1,000 – 10,000 staff | 15 – 60+ Devices | 8 vCPU | 16 GB RAM | 200 GB NVMe | MySQL 8.0 with dedicated pool |

### 1.2 Required Software Packages & PHP Extensions

- **PHP**: Version 8.2.x or 8.3.x (CLI & FPM)
- **Required PHP Extensions**:
  `bcmath`, `curl`, `dom`, `fileinfo`, `gd`, `json`, `mbstring`, `openssl`, `pdo_mysql`, `tokenizer`, `xml`, `zip`, `sockets`
- **Database Engine**: MySQL 8.0+ or MariaDB 10.4+ with `innodb_file_per_table=1` and `max_allowed_packet=64M`.
- **Process Supervisor**: `supervisor` or `systemd` (for background queues).
- **Time Sync Daemon**: `chrony` or `systemd-timesyncd` (mandatory for millisecond biometric timestamp alignment).

---

## 2. Deployment Option A: cPanel Shared Hosting Deployment

This workflow installs AMS on standard shared cPanel hosting environments.

### Step 2.1: Local Build & Package Preparation
Run the optimization commands locally in PowerShell or Terminal:
```bash
# 1. Install dependencies and compile production assets
composer install --no-dev --optimize-autoloader
npm install
npm run build

# 2. Clear local configuration and cache files
php artisan optimize:clear

# 3. Create a clean archive excluding unnecessary development assets
# Exclude: node_modules, .git, tests, .env, and local temp files
```

### Step 2.2: Upload & Extract via cPanel File Manager
1. Log in to your **cPanel Dashboard**.
2. Open **File Manager** and navigate to your target root:
   - Primary domain: `/public_html`
   - Subdomain (recommended): `/attendance.yourdomain.com`
3. Click **Upload** and transfer your project zip archive.
4. Right-click the uploaded archive and select **Extract**.

### Step 2.3: Directory Structure Adjustment (Critical for cPanel)

Laravel's entry point is `public/index.php`. On shared hosting where the web root cannot be pointed to a `/public` subfolder, apply **Method A** (Recommended for security and compatibility):

1. Inside the File Manager, open the extracted `public/` directory.
2. Select **all items** inside `public/` (including `index.php`, `.htaccess`, `build/`, `favicon.ico`).
3. Click **Move** and transfer them one level up into the site root (`public_html` or subdomain root).
4. Edit the moved `index.php` file and update paths to point to the parent framework folders:
   ```php
   // Change line 41 from:
   require __DIR__.'/../vendor/autoload.php';
   // To:
   require __DIR__.'/vendor/autoload.php';

   // Change line 50 from:
   $app = require_once __DIR__.'/../bootstrap/app.php';
   // To:
   $app = require_once __DIR__.'/bootstrap/app.php';
   ```
5. You may now delete the empty `public/` folder.

### Step 2.4: Database Provisioning & Environment Configuration
1. In cPanel, navigate to **MySQL® Databases**.
2. Create a database: `youruser_ams_fp`.
3. Create a database user with a strong password: `youruser_ams_dbuser`.
4. Assign the user to the database with **All Privileges**.
5. In File Manager, create or edit `.env` in the root folder:
   ```env
   APP_NAME="AMS Enterprise"
   APP_ENV=production
   APP_KEY=base64:py+cKEP55KsdYoAgSL8q7Md9j4U0sj1Ho0YaiOs/l80=
   APP_DEBUG=false
   APP_URL=https://attendance.yourdomain.com

   LOG_CHANNEL=stack
   LOG_LEVEL=error

   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=youruser_ams_fp
   DB_USERNAME=youruser_ams_dbuser
   DB_PASSWORD=YourStrongPasswordHere

   SESSION_DRIVER=database
   QUEUE_CONNECTION=database
   CACHE_STORE=database
   FILESYSTEM_DISK=local
   ```
6. Open **phpMyAdmin**, select your new database, and import your SQL schema dump.

### Step 2.5: File Permissions & Cron Configuration
1. In File Manager, right-click `storage/` $\rightarrow$ **Change Permissions** $\rightarrow$ Set to `775`.
2. Right-click `bootstrap/cache/` $\rightarrow$ **Change Permissions** $\rightarrow$ Set to `775`.
3. Ensure all other folders are `755` and files are `644`.
4. Navigate to **cPanel $\rightarrow$ Cron Jobs** and register the 1-minute Laravel task runner:
   ```cron
   * * * * * /usr/local/bin/php /home/yourusername/public_html/artisan schedule:run >> /dev/null 2>&1
   ```

---

## 3. Deployment Option B: Ubuntu / Debian Linux VPS Deployment

This workflow installs AMS on an Ubuntu 22.04 LTS VPS with Nginx, PHP 8.2-FPM, and MySQL 8.0.

### Step 3.1: System Updates & Package Installation
```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y nginx mysql-server git curl unzip chrony supervisor

# Add PHP 8.2 repository
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install -y php8.2-fpm php8.2-cli php8.2-mysql php8.2-curl php8.2-gd \
    php8.2-mbstring php8.2-xml php8.2-zip php8.2-bcmath php8.2-sockets
```

### Step 3.2: Database Initialization
```bash
sudo mysql -u root
```
```sql
CREATE DATABASE emsprimeone_fp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'ams_user'@'localhost' IDENTIFIED BY 'SuperSecurePassword2026!';
GRANT ALL PRIVILEGES ON emsprimeone_fp.* TO 'ams_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### Step 3.3: Repository Setup & Build
```bash
# Clone repository
sudo mkdir -p /var/www/ams
sudo git clone https://github.com/abarna1997/laravel-project-Bio-metrics.git /var/www/ams
cd /var/www/ams

# Environment configuration
sudo cp .env.example .env
sudo nano .env # Set APP_URL, DB credentials, APP_ENV=production, APP_DEBUG=false

# Dependency installation
sudo composer install --no-dev --optimize-autoloader
sudo php artisan key:generate --force
sudo php artisan migrate --force
sudo php artisan storage:link

# Permissions
sudo chown -R www-data:www-data /var/www/ams
sudo chmod -R 775 /var/www/ams/storage /var/www/ams/bootstrap/cache
```

### Step 3.4: Nginx Virtual Host Configuration
Create `/etc/nginx/sites-available/ams.conf`:
```nginx
server {
    listen 80;
    server_name attendance.yourdomain.com;
    root /var/www/ams/public;

    index index.php index.html;
    charset utf-8;

    # Client upload limit for document attachments and backups
    client_max_body_size 64M;

    # Hardware ADMS push endpoints tuning
    location /iclock/ {
        try_files $uri $uri/ /index.php?$query_string;
        proxy_read_timeout 120s;
        proxy_connect_timeout 60s;
    }

    location /adms/ {
        try_files $uri $uri/ /index.php?$query_string;
        proxy_read_timeout 120s;
        proxy_connect_timeout 60s;
    }

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_read_timeout 180s;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```
Enable the site and obtain a free SSL certificate:
```bash
sudo ln -s /etc/nginx/sites-available/ams.conf /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d attendance.yourdomain.com
```

### Step 3.5: Supervisor Queue Worker & Crontab
Create `/etc/supervisor/conf.d/ams-worker.conf`:
```ini
[program:ams-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/ams/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/ams/storage/logs/queue-worker.log
stopwaitsecs=3600
```
Start Supervisor:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start ams-worker:*
```
Add Laravel Scheduler to `crontab`:
```bash
sudo crontab -u www-data -e
# Insert line:
* * * * * cd /var/www/ams && php artisan schedule:run >> /dev/null 2>&1
```

---

## 4. Physical Biometric Device Configuration (ZKTeco SenseFace M2F-LR)

Follow these steps to connect a brand-new physical ZKTeco SenseFace M2F-LR terminal to the cloud AMS platform:

### Step 4.1: Terminal Physical & Local Network Setup
1. Mount the terminal at a height of 1.45m – 1.55m from the floor for optimal facial recognition angles.
2. Connect an RJ45 Ethernet cable to the terminal's LAN port (or connect to a dedicated Wi-Fi network).
3. On the device touchscreen, access the **Main Menu** (press `M/OK` $\rightarrow$ authenticate using default administrator PIN or card).
4. Navigate to: **Comm.** (Communication) $\rightarrow$ **Ethernet**.
   * Set **DHCP**: `OFF` (Static IP recommended for corporate network stability).
   * **IP Address**: e.g., `192.168.1.200`
   * **Subnet Mask**: `255.255.255.0`
   * **Gateway**: e.g., `192.168.1.1`
   * **DNS**: `8.8.8.8` or corporate internal DNS.

### Step 4.2: ADMS / Cloud Server Communication Setup
1. In the **Comm.** menu, tap **Cloud Server Setting** (or **ADMS Setting** / **Web Server Setting**).
2. Configure the following parameters:
   * **Enable Cloud Server**: `ON`
   * **Server Address**: Enter your public domain or server IP:
     `attendance.yourdomain.com` (Do NOT include `http://` or trailing slashes)
   * **Server Port**: `80` (if standard HTTP) or `443` (if SSL/HTTPS)
   * **Server Path**: `/` (or leave default `/iclock/cdata`)
   * **Enable Proxy Server**: `OFF`
   * **HTTPS**: Set to `ON` if connecting over port 443 with an SSL certificate.
3. Save changes and restart the device.

### Step 4.3: Terminal Status Verification & Admin Console Approval
1. Once rebooted, check the top icon bar on the terminal display. The **Cloud Server Icon** (globe/cloud) should change from a red cross to **Green/Blue**, indicating a successful handshake.
2. Log in to the AMS Enterprise Web Console as Super Administrator (`Prime1-admin`).
3. Navigate to **Devices Management**:
   * The new terminal will be displayed in the list with status **Pending Approval**.
   * The terminal's Serial Number (e.g. `VAL_M2FLR_999`), local IP, and public IP will be auto-populated.
4. Click **Review / Approve Device**:
   * Assign the **Company** (e.g., *Prime One Global*).
   * Assign the **Branch** (e.g., *Head Office*).
   * Enter a friendly name (e.g., *Head Office Main Entrance*).
   * Click **Approve & Enable Device**.
5. Once approved, the system automatically triggers an outbound command to download user profiles, sync biometric templates, and set terminal time.

---

## 5. Automated Backup, Restoration & Disaster Recovery

### 5.1 Backup Generation Procedures
- **Scheduled Automated Backups**:
  The system automatically generates a complete SQL dump every morning at **01:00 AM** via `php artisan app:backup`. Backups are compressed and cataloged in the `backups` database table.
- **On-Demand Manual Backup (CLI)**:
  ```bash
  php artisan app:backup
  ```
- **On-Demand Manual Backup (Web Console)**:
  Navigate to **Administration $\rightarrow$ System Backups** $\rightarrow$ Click **Generate Backup Now**. Download the generated `.sql` file to offsite cold storage.

### 5.2 Database Restoration Procedures
In the event of database corruption or hardware failure:

#### Option 1: Automated Artisan Restoration
```bash
# List available system backups
php artisan backups:list

# Restore specific backup by ID
php artisan app:restore {backup_id}
```

#### Option 2: Direct CLI MySQL Restoration
```bash
# Decompress and import backup file
mysql -u ams_user -p emsprimeone_fp < /var/www/ams/storage/app/backups/backup_2026_06_15_010000.sql

# Clear cached queries
php artisan optimize:clear
```

### 5.3 File Assets Disaster Recovery
All uploaded employee contracts, signed agreements, profile photos, and payslip PDFs reside in `storage/app/public/`.
Synchronize this directory to offsite cloud storage (AWS S3, Google Cloud Storage, or secondary NAS) daily:
```bash
# Example rsync to offsite backup server
rsync -avz --delete /var/www/ams/storage/app/backup_user@offsite.backup.server:/backups/ams_storage/
```

---

## 6. Troubleshooting & Operational Incident Runbook

### Incident 1: Biometric Terminal Shows "Disconnected" / Cloud Icon Red
- **Symptom**: Terminal display shows red cross on cloud icon; device status in AMS remains `Offline`.
- **Diagnostic Steps**:
  1. Ping the server domain from a laptop on the same local subnet as the terminal:
     `ping attendance.yourdomain.com`
  2. Verify firewall allows outbound TCP port 80/443 from the terminal IP.
  3. Verify Nginx/Apache access log for incoming device handshakes:
     ```bash
     tail -f /var/log/nginx/access.log | grep "iclock"
     ```
  4. Inspect `storage/logs/laravel.log` for rejected handshakes.

---

### Incident 2: Employee Scans at Terminal, but Punch Does Not Appear in Web Logs
- **Symptom**: Terminal beeps "Thank You", but scan is missing from Daily Attendance.
- **Root Cause & Fix**:
  1. Verify the employee's `sync_pin` or `device_user_id` on the terminal matches the AMS database record.
  2. Check if the device is assigned to Company A, but the employee belongs to Company B. AMS enforces strict multi-company device routing.
  3. Check `attendance_logs` for duplicate timestamp rejections. If the employee scanned twice within 60 seconds, duplicate suppression automatically prevents clutter.

---

### Incident 3: 500 Internal Server Error / cPanel White Screen
- **Symptom**: Accessing `https://attendance.yourdomain.com` returns HTTP 500 or a blank page.
- **Root Cause & Fix**:
  1. Inspect the application log:
     ```bash
     tail -n 100 /var/www/ams/storage/logs/laravel.log
     ```
  2. Check file permissions on `storage` and `bootstrap/cache`. Ensure they are writeable by the web server (`775` permissions).
  3. If on shared cPanel hosting, verify `index.php` paths were modified to match `__DIR__.'/vendor/autoload.php'`.
  4. Ensure `.env` exists and contains a valid `APP_KEY`. Run `php artisan key:generate` if missing.

---

### Incident 4: Automated Tasks or Command Queue Not Processing
- **Symptom**: Commands remain in `pending` status indefinitely; device statuses do not update to `Offline`.
- **Root Cause & Fix**:
  1. Check crontab: `sudo crontab -u www-data -l`. Ensure `schedule:run` is active.
  2. Verify Supervisor queue worker:
     ```bash
     sudo supervisorctl status ams-worker:*
     ```
  3. Restart workers if stuck:
     ```bash
     sudo supervisorctl restart ams-worker:*
     php artisan queue:restart
     ```

---

### Incident 5: Run Full Production Validation Suite
To execute an automated 8-phase health verification across all system domains:
```bash
php artisan app:validate-production
```
This tests device auto-registration, on-time/late/overtime calculations, multi-company routing, deletion protection on `Prime1-admin`, database backup generation, and index query speeds.
