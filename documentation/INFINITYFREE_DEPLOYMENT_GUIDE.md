# InfinityFree Deployment Configuration Guide

## ✅ Pre-Deployment Checklist (Besides Database Connectivity)

### 1. 🔧 Application Settings Configuration

**File:** `config/settings.php`

Change from:
```php
define('APP_ENV', 'development');
define('APP_DEBUG', APP_ENV === 'development');
define('APP_URL', 'http://localhost/Wellnest_Sim_Web_Application');
```

Change to:
```php
define('APP_ENV', 'production');
define('APP_DEBUG', APP_ENV === 'development'); // Will be false
define('APP_URL', 'https://yourdomain.infinityfree.com'); // Use your actual domain
```

**Or if using a subdomain:**
```php
define('APP_URL', 'https://wellnest.yourdomain.infinityfree.com');
```

---

### 2. 🌐 Session Configuration

**File:** `config/settings.php`

**Current (Development):**
```php
ini_set('session.cookie_secure', APP_ENV === 'production' ? '1' : '0');
```

**For InfinityFree (no changes needed - auto-adapts):**
This already adapts to `production` mode, so HTTPS will automatically be enforced.

**Additional security setting (add if not present):**
```php
// Force secure session cookies on HTTPS
if (APP_ENV === 'production') {
    ini_set('session.cookie_secure', '1');      // Only send over HTTPS
    ini_set('session.cookie_httponly', '1');    // No JavaScript access
    ini_set('session.cookie_samesite', 'Lax');  // CSRF protection
}
```

---

### 3. 📋 Error Handling & Logging

**File:** `config/settings.php`

**Current (Development - shows errors):**
```php
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}
```

**For InfinityFree (Production - logs errors instead):**
```php
if (APP_ENV === 'production') {
    error_reporting(E_ALL);
    ini_set('display_errors', '0');           // Don't show to users
    ini_set('log_errors', '1');               // Log to file instead
    ini_set('error_log', LOGS_PATH . '/error.log');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');           // Show errors in development
}
```

---

### 4. 📁 File Path Configuration

**File:** `config/constants.php` (if exists) or add to `config/settings.php`

**Add:**
```php
// Define base paths
define('LOGS_PATH', __DIR__ . '/../logs');
define('ASSETS_PATH', __DIR__ . '/../assets');
define('VIEWS_PATH', __DIR__ . '/../views');

// Ensure logs directory exists and is writable
if (!is_dir(LOGS_PATH)) {
    mkdir(LOGS_PATH, 0755, true);
}
```

---

### 5. 🔐 HTTPS/SSL Enforcement

**File:** Add to top of `index.php` (after requires)**

```php
<?php
// Force HTTPS on production
if ($_SERVER['HTTP_X_FORWARDED_PROTO'] === 'http' && $_SERVER['SERVER_PORT'] !== '80') {
    header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    exit;
}

// Alternative method (if above doesn't work)
if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off') {
    if ($_SERVER['HTTP_HOST'] !== 'localhost' && $_SERVER['HTTP_HOST'] !== '127.0.0.1') {
        header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
        exit;
    }
}
?>
```

Or in `.htaccess` (recommended):
```apache
# Force HTTPS
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{HTTPS} off
    RewriteCond %{HTTP_HOST} !^localhost [NC]
    RewriteCond %{HTTP_HOST} !^127.0.0.1 [NC]
    RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
    
    # Remove public_html from URL path
    RewriteCond %{REQUEST_URI} !^public_html/
    RewriteRule ^(.*)$ public_html/$1 [L]
</IfModule>
```

---

### 6. 🌍 Domain/URL Configuration

**Check all hardcoded URLs:**

**Files to update:**
- `views/layouts/header.php`
- `views/login.php`
- `views/register.php`
- `index.php`

**Replace hardcoded paths like:**
```php
// ❌ BEFORE (hardcoded localhost)
<a href="/Wellnest_Sim_Web_Application/views/student/dashboard.php">

// ✅ AFTER (use APP_URL constant)
<a href="<?= APP_URL ?>/views/student/dashboard.php">
```

**Search for all instances:**
```
/Wellnest_Sim_Web_Application/
http://localhost
localhost:3000
```

Replace with `<?= APP_URL ?>` or just use relative paths.

---

### 7. 📧 Email Configuration (If using email features)

**Add to `config/settings.php`:**

```php
// Email Configuration
define('MAIL_FROM_NAME', "Griffins' WellNest");
define('MAIL_FROM_ADDRESS', 'noreply@yourdomain.infinityfree.com');

// For production, use external SMTP (Gmail, SendGrid, etc.)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'app-password-here');
define('SMTP_ENCRYPTION', 'tls');

// OR disable email for now
define('SEND_EMAILS', false);
```

---

### 8. 🗂️ Asset & CSS Path Configuration

**File:** Check `views/layouts/header.php` and all view files

**Update CSS paths:**
```html
<!-- ❌ BEFORE (may not work on production) -->
<link href="./css/output.css" rel="stylesheet">

<!-- ✅ AFTER (absolute path) -->
<link href="<?= APP_URL ?>/css/output.css" rel="stylesheet">
```

**Update image paths:**
```html
<!-- ✅ Good (relative) -->
<img src="../assets/logo_griffin.png" alt="Logo">

<!-- ✅ Also good (APP_URL) -->
<img src="<?= APP_URL ?>/assets/logo_griffin.png" alt="Logo">
```

---

### 9. 💾 Cache Configuration (If implemented)

**Add to `config/settings.php`:**

```php
// Cache Configuration
define('CACHE_DRIVER', 'array'); // Use 'array' or 'file' (not APC on InfinityFree)
define('CACHE_TTL', 3600); // 1 hour

// Cache directory
define('CACHE_PATH', __DIR__ . '/../cache');
if (!is_dir(CACHE_PATH) && CACHE_DRIVER === 'file') {
    mkdir(CACHE_PATH, 0755, true);
}
```

---

### 10. 🔒 Security Headers

**File:** `views/layouts/header.php` or `index.php` (add before HTML output)

```php
<?php
// Security Headers
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: SAMEORIGIN");
header("X-XSS-Protection: 1; mode=block");
header("Strict-Transport-Security: max-age=31536000; includeSubDomains"); // HSTS for HTTPS
header("Content-Security-Policy: default-src 'self' https:; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'");
?>
```

---

### 11. 📱 CORS Configuration (For future mobile app)

**File:** `src/api.php`

**Current (allows all origins):**
```php
header('Access-Control-Allow-Origin: *');
```

**For Production (restrict to your domain):**
```php
$allowed_origins = [
    'https://yourdomain.infinityfree.com',
    'https://www.yourdomain.infinityfree.com',
];

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

if (in_array($origin, $allowed_origins)) {
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Access-Control-Allow-Credentials: true');
}
```

---

### 12. 🗑️ Remove Development Files

**Before uploading, delete/exclude:**
```
/.git/
/node_modules/
/.gitignore
/package-lock.json
/.env.local
/README_DEV.md
/.vscode/
/dist/ (if exists)
```

---

### 13. 📋 Create `.htaccess` for InfinityFree

**File:** Create `/public_html/.htaccess` (root directory)

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    
    # Force HTTPS
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
    
    # Remove .php extension from URLs (if desired)
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    # RewriteRule ^([a-zA-Z0-9_-]+)$ $1.php [L]
    
    # Prevent direct access to config files
    <FilesMatch "^config|\.env">
        Deny from all
    </FilesMatch>
    
    # Prevent access to logs and other sensitive directories
    <FilesMatch "^logs|cache|uploads/private">
        Deny from all
    </FilesMatch>
</IfModule>

# Disable directory listing
Options -Indexes

# Set timezone
php_value date.timezone "Asia/Manila"

# Set memory limit (ensure it's sufficient)
php_value memory_limit 128M

# Set upload limit
php_value upload_max_filesize 20M
php_value post_max_size 20M

# Set execution timeout
php_value max_execution_time 300
```

---

### 14. 🔄 Database Connection Verification

**Even though you're handling DB connectivity, verify:**

**File:** `config/database.php`

```php
// InfinityFree MySQL details:
// Host: Usually 'localhost' (even on InfinityFree)
// Username: cpanel_username_randomstring
// Password: Your MySQL password (from cpanel)
// Database: cpanel_username_dbname

private const HOST = 'localhost';
private const DB_NAME = 'wellnest_production';  // Your actual DB name
private const USERNAME = 'your_mysql_user';     // From cpanel
private const PASSWORD = 'your_mysql_pass';     // From cpanel
```

---

### 15. 📝 Create Production `.env` File

**File:** Create `config/.env` (don't commit to Git)

```
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.infinityfree.com

DB_HOST=localhost
DB_NAME=wellnest_production
DB_USER=mysql_username
DB_PASS=mysql_password
DB_PORT=3306

SESSION_LIFETIME=3600
SESSION_NAME=WELLNEST_SID_PROD

ADMIN_EMAIL=admin@yourdomain.infinityfree.com
SUPPORT_EMAIL=support@yourdomain.infinityfree.com
```

**Update `config/settings.php` to use ENV variables:**
```php
<?php
// Load .env file (optional)
if (file_exists(__DIR__ . '/.env')) {
    $env = parse_ini_file(__DIR__ . '/.env');
    foreach ($env as $key => $value) {
        define($key, $value);
    }
} else {
    // Fallback to hardcoded values
    define('APP_ENV', getenv('APP_ENV') ?: 'development');
    define('APP_URL', getenv('APP_URL') ?: 'http://localhost');
}
?>
```

---

### 16. ✅ Pre-Upload Verification Checklist

Before uploading to InfinityFree:

```
□ APP_ENV changed to 'production'
□ APP_DEBUG set to false
□ APP_URL updated to your InfinityFree domain
□ Database credentials verified
□ HTTPS redirect configured
□ Security headers added
□ Error logging configured (not displayed)
□ .htaccess created and configured
□ logs/ directory permissions set to 755
□ All hardcoded localhost URLs replaced
□ CSS/JS/Image paths use APP_URL or relative paths
□ Development files excluded (.git, node_modules, etc.)
□ .env file created with production credentials
□ Email configuration set (if needed)
□ Session security settings hardened
□ CORS configuration updated
□ No debug SQL queries in code
```

---

## 🚀 Summary of Changes Needed

| Configuration | Current (Dev) | Production (InfinityFree) |
|---------------|--------------|--------------------------|
| **APP_ENV** | 'development' | 'production' |
| **APP_DEBUG** | true | false |
| **APP_URL** | http://localhost | https://domain.infinityfree.com |
| **Database Host** | localhost | localhost |
| **HTTPS** | No | Yes (forced) |
| **Error Display** | Visible to users | Logged to file only |
| **Session Secure** | 0 | 1 (auto on HTTPS) |
| **CORS** | Allow all (*) | Restrict to domain |
| **Logs Path** | ./logs | ./logs (verify writable) |

---

## 📌 Priority Changes (Do These First)

1. **Change APP_URL** to your InfinityFree domain
2. **Change APP_ENV** to 'production'
3. **Change APP_DEBUG** to false
4. **Update database credentials** (you mentioned this)
5. **Create `.htaccess`** with HTTPS redirect
6. **Add security headers** to views
7. **Verify logs directory** is writable

---

## ⚠️ InfinityFree-Specific Notes

- **PHP Version:** Check what PHP version InfinityFree provides (usually 7.4 or 8.0+)
- **MySQL Version:** Usually 5.7 or 8.0
- **Directory Structure:** Files go in `/public_html/` via FTP
- **SSL:** Free SSL provided automatically
- **Email:** Use external SMTP service (limitations on free plan)
- **Cron Jobs:** Limited on free plan (use PHP session cleanup instead)
- **Uploads:** Store in `/public_html/uploads/` and ensure writable
- **Database Backups:** Back up regularly via phpMyAdmin

---

## 🔗 Useful InfinityFree Resources

- Control Panel: https://panel.infinityfree.com/
- phpMyAdmin: Access via control panel
- FTP Credentials: Available in control panel
- Support: Live chat in control panel

