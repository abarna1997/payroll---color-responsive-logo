# Laravel cPanel Hosting Setup Guide

This guide provides step-by-step instructions on how to properly deploy your Laravel application on a shared cPanel hosting environment.

## 1. Prepare Your Application for Deployment

Before uploading your files to cPanel, you need to prepare your Laravel project locally:

1. **Optimize your application:**
   Run the following commands in your local terminal:
   ```bash
   php artisan optimize:clear
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```
2. **Zip your project files:**
   Select all files and folders in your project directory and compress them into a single `.zip` file (e.g., `laravel-app.zip`). 
   *Note: Make sure to **exclude** all unwanted files/folders to save space and maintain security, such as `node_modules`, `.git`, `tests`, `database/factories`, `database/seeders`, `.env.example`, and any `test_*.php` scripts.*

## 2. Uploading Files to cPanel

1. Log in to your **cPanel** dashboard.
2. Go to **File Manager** (found under the Files section).
3. Navigate to the root directory for your domain (usually `public_html` for your primary domain, or a specific folder for a subdomain like `attendance.yourdomain.com`).
4. Click **Upload** from the top menu and upload your `laravel-app.zip` file.
5. Once uploaded, right-click the `.zip` file and select **Extract** to extract all files into the folder.

## 3. Adjusting the Public Directory (Crucial Step for Shared Hosting)

Laravel's entry point is the `public/index.php` file, but cPanel domains usually point to the `public_html` folder or the root of the subdomain folder. To secure your application and ensure it loads properly, follow these steps:

### Method A: Moving the `public` contents (Recommended)
1. In the File Manager, open the extracted Laravel `public` folder.
2. Select **all** the files inside the `public` folder (including `index.php` and `.htaccess`).
3. Click **Move** and move them one level up, directly into the `public_html` (or your subdomain's root folder).
4. Now, open the `index.php` file you just moved and edit it:
   
   Change this line:
   ```php
   require __DIR__.'/../vendor/autoload.php';
   ```
   To this:
   ```php
   require __DIR__.'/vendor/autoload.php';
   ```

   And change this line:
   ```php
   $app = require_once __DIR__.'/../bootstrap/app.php';
   ```
   To this:
   ```php
   $app = require_once __DIR__.'/bootstrap/app.php';
   ```
5. Save the changes. You can safely delete the empty `public` folder now.

### Method B: Using .htaccess in the root folder (Alternative)
If you prefer not to move files, you can create an `.htaccess` file in your root folder (e.g., `public_html`) with the following rules to redirect all traffic to the `public` folder:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
```

## 4. Configure Your Environment Variables

1. Find the `.env` file in your root directory (make sure hidden files are visible in cPanel settings).
2. Right-click and **Edit** the file.
3. Update the environment variables to match your cPanel server:
   ```env
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://attendance.yourdomain.com
   
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=your_cpanel_db_name
   DB_USERNAME=your_cpanel_db_user
   DB_PASSWORD=your_cpanel_db_password
   ```
4. Save the changes.

## 5. Setup the Database

1. In cPanel, go to **MySQL® Databases**.
2. Create a new database (e.g., `your_cpanel_db_name`).
3. Create a new database user and set a strong password.
4. Add the user to the database and assign **All Privileges**.
5. Go to **phpMyAdmin** in cPanel.
6. Select your new database on the left sidebar.
7. Click **Import** and upload the `.sql` backup file exported from your local machine to populate the database tables.

## 6. Set Proper File Permissions

For Laravel to function correctly (especially for logging and caching), you need to set the correct permissions:

1. Right-click on the `storage` folder, select **Change Permissions**, and set it to `775` or `777` (depending on your host's security rules).
2. Do the same for the `bootstrap/cache` folder, setting it to `775` or `777`.
3. Ensure all other folders are set to `755` and files are set to `644`.

## 7. Run Migrations (Optional)

If you have SSH access to your cPanel, you can connect via terminal and run migrations directly:
```bash
php artisan migrate --force
```
If you do not have SSH access, simply importing your local `.sql` database via phpMyAdmin (as explained in Step 5) is sufficient.

## 8. Final Check
Open your domain in your web browser (e.g., `https://attendance.yourdomain.com`). Your Laravel application should now be live! If you encounter a 500 Server Error, check the `storage/logs/laravel.log` file for detailed error messages.
