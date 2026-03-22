<?php
/**
 * Application Settings
 * Wellnest Mental Health Web Application
 */

// Load constants
require_once __DIR__ . '/constants.php';

// =============================================================================
// SECURITY HEADERS
// =============================================================================
// Only send headers if not already sent (important for CLI and testing)
if (!headers_sent()) {
    // Prevent clickjacking
    header('X-Frame-Options: SAMEORIGIN');
    
    // Prevent MIME type sniffing
    header('X-Content-Type-Options: nosniff');
    
    // Enable XSS protection
    header('X-XSS-Protection: 1; mode=block');
    
    // Referrer policy
    header('Referrer-Policy: strict-origin-when-cross-origin');
    
    // Content Security Policy (permissive for development)
    header("Content-Security-Policy: default-src 'self' https:; script-src 'self' 'unsafe-inline' https:; style-src 'self' 'unsafe-inline' https:; img-src 'self' https: data:;");
}

// =============================================================================
// ENVIRONMENT SETTINGS
// =============================================================================
define('APP_ENV', 'production'); // 'development', 'staging', 'production'
define('APP_DEBUG', APP_ENV === 'development');

// =============================================================================
// APPLICATION INFO
// =============================================================================
define('APP_NAME', "Griffins' WellNest");
define('APP_VERSION', '1.0.0');

// Base URL can be overridden via APP_URL env var; otherwise infer from project folder.
$configuredAppUrl = getenv('APP_URL') ?: '';
if ($configuredAppUrl !== '') {
    $normalizedAppUrl = '/' . trim((string) $configuredAppUrl, '/');
    define('APP_URL', $normalizedAppUrl === '/' ? '' : $normalizedAppUrl);
} else {
    $documentRoot = realpath((string) ($_SERVER['DOCUMENT_ROOT'] ?? ''));
    $projectRoot = realpath(ROOT_PATH);

    if ($documentRoot !== false && $projectRoot !== false) {
        $docRootNormalized = strtolower(str_replace('\\', '/', $documentRoot));
        $projectRootNormalized = strtolower(str_replace('\\', '/', $projectRoot));

        if (str_starts_with($projectRootNormalized, $docRootNormalized)) {
            $relativePath = str_replace('\\', '/', substr($projectRoot, strlen($documentRoot)));
            $relativePath = trim($relativePath, '/');
            define('APP_URL', $relativePath === '' ? '' : '/' . $relativePath);
        } else {
            define('APP_URL', '');
        }
    } else {
        define('APP_URL', '');
    }
}

// =============================================================================
// ERROR HANDLING
// =============================================================================
// Ensure logs directory exists
if (!is_dir(LOGS_PATH)) {
    @mkdir(LOGS_PATH, 0755, true);
}

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
ini_set('session.cookie_secure', '0');  // Set to 0 for non-HTTPS environments
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
