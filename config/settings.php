<?php
/**
 * Application Settings
 * Wellnest Mental Health Web Application
 */

// Load constants
require_once __DIR__ . '/constants.php';

// =============================================================================
// ENVIRONMENT SETTINGS
// =============================================================================
define('APP_ENV', 'development'); // 'development', 'staging', 'production'
define('APP_DEBUG', APP_ENV === 'development');

// =============================================================================
// APPLICATION INFO
// =============================================================================
define('APP_NAME', "Griffin's Wellnest");
define('APP_VERSION', '1.0.0');
define('APP_URL', 'http://localhost/Wellnest_Sim_Web_Application');

// =============================================================================
// ERROR HANDLING
// =============================================================================
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
    ini_set('log_errors', '1');
    ini_set('error_log', LOGS_PATH . '/error.log');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    ini_set('error_log', LOGS_PATH . '/error.log');
}

// =============================================================================
// TIMEZONE
// =============================================================================
date_default_timezone_set('Asia/Manila'); // Philippine Time

// =============================================================================
// SESSION CONFIGURATION
// =============================================================================
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_secure', APP_ENV === 'production' ? '1' : '0');
ini_set('session.use_strict_mode', '1');
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.gc_maxlifetime', (string) SESSION_LIFETIME);

// =============================================================================
// SECURITY
// =============================================================================
define('CSRF_TOKEN_NAME', 'csrf_token');
define('CSRF_TOKEN_LIFETIME', 3600); // 1 hour

// =============================================================================
// AI SETTINGS (for future Google Gemini integration)
// =============================================================================
define('AI_ENABLED', false);
define('AI_PROVIDER', 'google_gemini');
define('AI_API_KEY', ''); // Set in .env file for production

// =============================================================================
// EMAIL SETTINGS (for future notifications)
// =============================================================================
define('MAIL_ENABLED', false);
define('MAIL_HOST', 'smtp.example.com');
define('MAIL_PORT', 587);
define('MAIL_USERNAME', '');
define('MAIL_PASSWORD', '');
define('MAIL_FROM_ADDRESS', 'noreply@wellnest.edu.ph');
define('MAIL_FROM_NAME', APP_NAME);

// =============================================================================
// AUTOLOAD DATABASE CLASS
// =============================================================================
require_once __DIR__ . '/database.php';
