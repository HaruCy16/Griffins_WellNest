# InfinityFree Deployment Guide - File Manager Method

**Date:** March 20, 2026  
**Method:** Using InfinityFree File Manager (Web-based)  
**Status:** Step-by-Step Guide

---

## 📋 Table of Contents

1. [Pre-Deployment Steps](#pre-deployment-steps)
2. [Prepare Configuration Files](#prepare-configuration-files)
3. [InfinityFree Account Setup](#infinityfree-account-setup)
4. [Upload Files via File Manager](#upload-files-via-file-manager)
5. [Database Setup](#database-setup)
6. [Verification & Testing](#verification--testing)
7. [Go Live](#go-live)

---

## 🔧 Pre-Deployment Steps

Before you start uploading, prepare these files locally:

### Step 0: Create Deployment Folder

On your local computer:
```
Create folder: C:\temp\WellNest_Deploy\
```

This is where you'll put modified files that need to be deployed.

---

## 📝 Prepare Configuration Files

### 🔑 Key File 1: `config/settings.php`

**What to change:**
- Line 11: `APP_ENV` from `'development'` to `'production'`
- Line 17: `APP_URL` from localhost to your InfinityFree domain

**Steps:**

1. Open local file: `config/settings.php`
2. Find line 11:
```php
define('APP_ENV', 'development'); // Change this
```

Replace with:
```php
define('APP_ENV', 'production');
```

3. Find line 17:
```php
define('APP_URL', 'http://localhost/Wellnest_Sim_Web_Application');
```

Replace with (use YOUR actual domain):
```php
define('APP_URL', 'https://yourname.epizy.com');
```

**💡 How to find your InfinityFree domain:**
- Login to https://panel.infinityfree.com/
- Click on your account → View website
- Your domain is shown in the URL bar
- Format is usually: `yourname.epizy.com` or `yourname.infinityfree.com`

4. Save this file

---

### 🗄️ Key File 2: `config/database.php`

**What to change:**
- Database credentials from localhost to InfinityFree credentials

**Steps:**

1. Login to https://panel.infinityfree.com/
2. Go to **Databases** section
3. Find your MySQL database credentials:
   - **Host:** Look for "Database Server" (often `localhost` even on InfinityFree)
   - **Database Name:** Your database name
   - **Username:** Your database user
   - **Password:** Your database password

4. Open local file: `config/database.php`
5. Find the database constants:

```php
private const HOST = 'localhost';
private const DB_NAME = 'griffin_wellnest_db';
private const USERNAME = 'root';
private const PASSWORD = '';
```

Replace with your InfinityFree credentials (example):
```php
private const HOST = 'localhost';
private const DB_NAME = 'epiz_12345678_wellnest';  // From your account
private const USERNAME = 'epiz_12345678';           // From your account
private const PASSWORD = 'YourDatabasePassword123'; // From your account
```

6. Save this file

---

### 📋 Key File 3: `index.php` 

**What to change:**
- Update all hardcoded localhost redirects

**Steps:**

1. Open local file: `index.php`
2. Find lines with hardcoded paths:

```php
// Line around 18-26
redirect('/Wellnest_Sim_Web_Application/views/student/dashboard.php');
redirect('/Wellnest_Sim_Web_Application/views/counselor/dashboard.php');
redirect('/Wellnest_Sim_Web_Application/views/admin/dashboard.php');
```

Replace with:
```php
redirect(APP_URL . '/views/student/dashboard.php');
redirect(APP_URL . '/views/counselor/dashboard.php');
redirect(APP_URL . '/views/admin/dashboard.php');
```

3. Save this file

---

### 🎨 Key File 4: `.htaccess` (Create New)

**Create a new file in your project root**

**Steps:**

1. Create new file: `.htaccess` (with the dot at beginning)
2. Add this content:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    
    # Force HTTPS
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
    
    # Prevent direct access to config files
    <FilesMatch "^(config|\.env)">
        Deny from all
    </FilesMatch>
    
    # Prevent access to logs
    <FilesMatch "^logs">
        Deny from all
    </FilesMatch>
</IfModule>

# Disable directory listing
Options -Indexes

# Security headers
<IfModule mod_headers.c>
    Header set X-Content-Type-Options "nosniff"
    Header set X-Frame-Options "SAMEORIGIN"
    Header set X-XSS-Protection "1; mode=block"
</IfModule>
```

3. Save this file

---

## 🌐 InfinityFree Account Setup

### Step 1: Create/Login to InfinityFree Account

1. Visit: https://www.infinityfree.net/
2. If no account, sign up (free)
3. If you have account, login to: https://panel.infinityfree.com/

### Step 2: Create Hosting Account (if not done)

1. In panel, click **"Create Website"** or **"New Hosting"**
2. Complete setup wizard:
   - Domain name (choose your preferred domain)
   - Accept terms
   - Activate email
3. Note your:
   - **Domain:** (appears at top)
   - **FTP Host:** From control panel
   - **FTP Username:** From control panel
   - **FTP Password:** From control panel

### Step 3: Create Database

1. In panel, go to **Databases**
2. Click **"Create Database"**
3. Note these credentials:
   - Database name
   - Database user
   - Database password
   - Host (usually `localhost`)

**⭐ IMPORTANT:** These go in your `config/database.php`

### Step 4: Access File Manager

1. In panel, go to **File Manager**
2. Click **"Open File Manager"**
3. You should see folders like:
   - `public_html` ← **Upload your files here**
   - `logs`
   - `backups`

---

## 📤 Upload Files via File Manager

### Step 1: Create Application Folder

In InfinityFree File Manager:

1. Navigate to `/public_html/`
2. Right-click (or use menu) → **"Create Folder"**
3. Name it: `wellnest` (or any name you prefer)
4. You should now have: `/public_html/wellnest/`

### Step 2: Prepare ZIP File

On your local computer:

1. Zip your entire Wellnest application folder
2. **Exclude these folders** (not needed):
   - `.git/`
   - `node_modules/`
   - `documentation/` (backup copy is okay)
   - `.vscode/`
   - `dist/`

3. Create: `Wellnest_Deploy.zip` (only ~2-3 MB without node_modules)

### Step 3: Upload ZIP File

In InfinityFree File Manager:

1. Navigate to: `/public_html/wellnest/`
2. Click **"Upload"** button
3. Select your `Wellnest_Deploy.zip` file
4. Wait for upload to complete (shows progress)
5. ✅ You should see `Wellnest_Deploy.zip` in the folder

### Step 4: Extract ZIP File

In InfinityFree File Manager:

1. Right-click on `Wellnest_Deploy.zip`
2. Click **"Extract"** or **"Decompress"**
3. Wait for extraction to complete
4. You should see folders:
   - `config/`
   - `src/`
   - `views/`
   - `css/`
   - `database/`
   - `logs/`
   - `index.php`
   - etc.

5. Delete the `Wellnest_Deploy.zip` file (optional, to save space)

### Step 5: Create Required Directories

In InfinityFree File Manager:

1. Navigate to `/public_html/wellnest/`
2. Create these folders (if not already present):
   - **logs** - for error logs
   - **cache** - for caching (if needed)
   - **uploads** - for file uploads (if needed)

3. Right-click each folder → **"Properties"**
   - Change permissions to **755** (executable for directories)
   - These need to be writable

---

## 🗄️ Database Setup

### Step 1: Import Database Schema

In InfinityFree Panel:

1. Go to **Databases**
2. Find your database
3. Click **"Manage"** or **"phpMyAdmin"**
4. You're now in phpMyAdmin (database management tool)

### Step 2: Import SQL Schema

In phpMyAdmin:

1. Click **"Import"** tab (top menu)
2. Under **"File to import:"**
   - Click **"Browse"** 
   - Navigate to your local: `database/schema.sql`
   - Select it
3. At bottom, click **"Go"**
4. Wait for import to complete
5. ✅ Success message should appear

### Step 3: Verify Tables Created

In phpMyAdmin:

1. Click **"Databases"** tab
2. Select your database name
3. You should see **9 tables**:
   - roles
   - users
   - sections
   - assessments
   - assessment_scores
   - assessment_answers
   - therapeutic_games
   - game_sessions
   - audit_logs

4. ✅ If all 9 tables appear, database is ready!

### Step 4: Create Test Admin User

In phpMyAdmin:

1. Click your database name
2. Click **users** table
3. Click **"Insert"** tab
4. Fill in:
   - **user_id:** Leave blank (auto-increment)
   - **email:** `admin@wellnest.local`
   - **password_hash:** Use bcrypt hash (see below)
   - **first_name:** `Admin`
   - **last_name:** `User`
   - **role_id:** `3` (admin role)
   - **section_id:** Leave blank/NULL
   - **is_active:** `1` (true)
   - **created_at:** Click "Insert current date and time"

**🔐 For password_hash:**
- Use a bcrypt hash generator online: https://www.bcryptcalculator.com/
- Password: `Test@123456`
- Generate hash
- Paste into password_hash field

5. Click **"Go"** to insert

6. ✅ You now have an admin account to login with!

---

## ✅ Verification & Testing

### Step 1: Update Settings.php on Server

Wait! Before testing, you need to update the settings file on the server:

1. In InfinityFree File Manager, navigate to: `/public_html/wellnest/config/`
2. Right-click on **settings.php**
3. Click **"Edit"** (or double-click)
4. Find line 11:
```php
define('APP_ENV', 'development');
```

Change to:
```php
define('APP_ENV', 'production');
```

5. Find line 17:
```php
define('APP_URL', 'http://localhost/Wellnest_Sim_Web_Application');
```

Change to your actual domain (example):
```php
define('APP_URL', 'https://yourname.epizy.com/wellnest');
```

6. Click **"Save"**

### Step 2: Update index.php on Server

1. In InfinityFree File Manager, navigate to: `/public_html/wellnest/`
2. Right-click on **index.php**
3. Click **"Edit"**
4. Find lines with hardcoded paths (around line 18-26)
5. Update them if needed (should be okay if you did this locally)
6. Click **"Save"**

### Step 3: Test Application

1. Open browser
2. Visit: `https://yourname.epizy.com/wellnest/`
3. You should see:
   - Griffins' WellNest logo
   - "Welcome, Student!" message
   - Login & Register buttons

4. ✅ **If this appears, homepage works!**

### Step 4: Test Database Connection

1. Try to log in:
   - Email: `admin@wellnest.local`
   - Password: `Test@123456`

2. If login succeeds:
   - You see admin dashboard
   - Database connection works! ✅

3. If login fails:
   - Check database credentials in `config/database.php`
   - Verify database imported successfully
   - Check error logs

### Step 5: Check Error Logs

In InfinityFree File Manager:

1. Navigate to: `/public_html/wellnest/logs/`
2. If **error.log** file exists:
   - Right-click → **"Edit"**
   - Review errors (if any)
   - This helps debug issues

---

## 🚀 Go Live

### Step 1: Create Test Accounts

**Create Student Account:**
1. Go to: `https://yourname.epizy.com/wellnest/`
2. Click **"Register"**
3. Fill form:
   - Email: `student@wellnest.local`
   - Password: `Test@123456` (or stronger)
   - First Name: `Test`
   - Last Name: `Student`
   - Section: (select from dropdown)
4. Click **"Register"**
5. ✅ You should be redirected to student dashboard

**Create Counselor Account:**
1. Use admin dashboard
2. Go to: **Users**
3. Click **"Add New User"**
4. Fill:
   - Email: `counselor@wellnest.local`
   - Password: `TestCounselor@123`
   - First Name: `Test`
   - Last Name: `Counselor`
   - Role: **Counselor**
   - Section: (select)
5. Click **"Add User"**

### Step 2: Test All Features

**As Student:**
- ✅ View Dashboard
- ✅ View Assessments
- ✅ Start an Assessment
- ✅ Complete and view results
- ✅ Play Frogger game
- ✅ View notifications
- ✅ View profile

**As Counselor:**
- ✅ View Dashboard
- ✅ View Students
- ✅ Click student to see details
- ✅ View assessment history
- ✅ Add counselor notes

**As Admin:**
- ✅ View Dashboard
- ✅ Go to Users page
- ✅ Add/Edit/Delete users
- ✅ Go to Settings page
- ✅ View system information

### Step 3: Test on Mobile

1. Open application on phone/tablet
2. Test responsive design:
   - Check layout adjusts
   - Buttons are clickable
   - Forms are readable

### Step 4: Security Check

1. Check HTTPS is enforced:
   - Visit: `http://yourname.epizy.com/wellnest/`
   - Should redirect to `https://` version
   - ✅ If auto-redirects, HTTPS works!

2. Check error handling:
   - Try accessing: `/wellnest/config/settings.php`
   - Should show 403 Forbidden or blank
   - ✅ If you don't see code, security works!

---

## 📋 Complete Deployment Checklist

Before declaring deployment complete:

```
Pre-Deployment:
□ Modified config/settings.php (APP_ENV, APP_URL)
□ Modified config/database.php (DB credentials)
□ Modified index.php (APP_URL redirects)
□ Created .htaccess file
□ Created Wellnest_Deploy.zip (excluded node_modules)

InfinityFree Setup:
□ Created InfinityFree account
□ Created hosting
□ Created database
□ Noted domain name
□ Noted database credentials

Upload & Setup:
□ Created /public_html/wellnest/ folder
□ Uploaded and extracted Wellnest_Deploy.zip
□ Created logs/ folder (chmod 755)
□ Imported database schema via phpMyAdmin
□ Created admin test user

Verification:
□ Homepage loads at domain
□ Admin login works
□ Admin dashboard displays
□ Student registration works
□ Student dashboard loads
□ Counselor account created
□ Counselor dashboard works
□ HTTPS redirect works
□ Error logs don't show critical errors
□ Mobile layout works

Go Live:
□ All features tested
□ Admin users understand system
□ Student test data created
□ Counselor test data created
□ Backup of database created
□ Email admins know login credentials
□ Documentation shared with team
```

---

## 🆘 Troubleshooting

### Issue 1: "Database Connection Failed"

**Solution:**
1. Open InfinityFree panel → Databases
2. Copy exact database credentials
3. Edit `/public_html/wellnest/config/database.php`
4. Paste credentials carefully (no spaces!)
5. Save and refresh browser

### Issue 2: "Page Not Found (404)"

**Solution:**
1. Check URL: Should be `https://yourname.epizy.com/wellnest/`
2. Verify folder structure in File Manager
3. Check index.php exists in `/public_html/wellnest/`
4. Try accessing directly: `https://yourname.epizy.com/wellnest/index.php`

### Issue 3: "Blank Page / White Screen"

**Solution:**
1. Check error.log in `/logs/` folder
2. Enable debug:
   - Edit `config/settings.php`
   - Change `APP_DEBUG` to `true` temporarily
   - Try page again
   - Check error message
   - **Turn off debug after!**

### Issue 4: "Login Not Working"

**Solution:**
1. Verify admin user in database (phpMyAdmin → users table)
2. Check password hash is valid bcrypt
3. Check `config/database.php` credentials
4. Check error log for SQL errors

### Issue 5: "CSS Not Loading (Page Looks Plain)"

**Solution:**
1. Check CSS file exists: `/public_html/wellnest/css/output.css`
2. Verify file size > 40KB
3. Check browser DevTools (F12) → Network tab
4. Look for CSS file 404 error
5. In `views/layouts/header.php`, verify CSS path

---

## 📞 Need Help?

**If deployment stuck:**

1. Check error.log: `/wellnest/logs/error.log`
2. Review this guide's troubleshooting section
3. Check InfinityFree documentation: https://infinityfree.net/
4. Check phpMyAdmin for database issues

**Common InfinityFree Limitations:**
- Free plan has limited bandwidth
- Large files take longer to upload
- May need to disable some features if disk space low
- Email sending may be restricted (use external SMTP)

---

**Deployment Date:** March 20, 2026  
**Status:** ✅ Ready to Deploy  
**Next Step:** Follow steps above in order!

