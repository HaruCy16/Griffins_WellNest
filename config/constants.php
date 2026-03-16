<?php
/**
 * Application Constants
 * Wellnest Mental Health Web Application
 */

// Prevent direct access
if (!defined('WELLNEST_APP')) {
    define('WELLNEST_APP', true);
}

// =============================================================================
// ROLE CONSTANTS
// =============================================================================
define('ROLE_STUDENT', 1);
define('ROLE_COUNSELOR', 2);
define('ROLE_ADMIN', 3);

define('ROLE_NAMES', [
    ROLE_STUDENT => 'student',
    ROLE_COUNSELOR => 'counselor',
    ROLE_ADMIN => 'admin'
]);

// =============================================================================
// ASSESSMENT TYPE CONSTANTS
// =============================================================================
define('ASSESSMENT_SCHOOL_EXPERIENCE', 'school_experience');
define('ASSESSMENT_MENTAL_HEALTH', 'mental_health');
define('ASSESSMENT_HELP_SEEKING', 'help_seeking');

// Backward-compatible constants (legacy)
define('ASSESSMENT_STRESS', 'stress');
define('ASSESSMENT_ANXIETY', 'anxiety');
define('ASSESSMENT_DEPRESSION', 'depression');
define('ASSESSMENT_GENERAL', 'general');

// =============================================================================
// RISK LEVEL CONSTANTS
// =============================================================================
define('RISK_LOW', 'low');
define('RISK_MEDIUM', 'medium');
define('RISK_HIGH', 'high');
define('RISK_CRITICAL', 'critical');

define('RISK_THRESHOLDS', [
    'low' => 25,      // 0-25%
    'medium' => 50,   // 26-50%
    'high' => 75,     // 51-75%
    'critical' => 100 // 76-100%
]);

// =============================================================================
// GAME TYPE CONSTANTS
// =============================================================================
define('GAME_BREATHING', 'breathing');
define('GAME_MEMORY', 'memory');
define('GAME_PUZZLE', 'puzzle');
define('GAME_GARDEN', 'garden');
define('GAME_MINDFULNESS', 'mindfulness');
define('GAME_SIMULATION', 'simulation');

// =============================================================================
// NOTIFICATION TYPE CONSTANTS
// =============================================================================
define('NOTIFY_ASSESSMENT_DUE', 'assessment_due');
define('NOTIFY_RECOMMENDATION', 'recommendation');
define('NOTIFY_REMINDER', 'reminder');
define('NOTIFY_ALERT', 'alert');
define('NOTIFY_COUNSELOR_MESSAGE', 'counselor_message');

// =============================================================================
// PRIORITY CONSTANTS
// =============================================================================
define('PRIORITY_LOW', 'low');
define('PRIORITY_MEDIUM', 'medium');
define('PRIORITY_HIGH', 'high');
define('PRIORITY_URGENT', 'urgent');

// =============================================================================
// SESSION CONSTANTS
// =============================================================================
define('SESSION_LIFETIME', 3600);       // 1 hour
define('SESSION_NAME', 'WELLNEST_SID');
define('REMEMBER_ME_LIFETIME', 604800); // 7 days

// =============================================================================
// PASSWORD CONSTANTS
// =============================================================================
define('PASSWORD_MIN_LENGTH', 8);
define('BCRYPT_COST', 12);

// =============================================================================
// PAGINATION
// =============================================================================
define('DEFAULT_PAGE_SIZE', 10);
define('MAX_PAGE_SIZE', 100);

// =============================================================================
// FILE PATHS
// =============================================================================
define('ROOT_PATH', dirname(__DIR__));
define('CONFIG_PATH', ROOT_PATH . '/config');
define('SRC_PATH', ROOT_PATH . '/src');
define('VIEWS_PATH', ROOT_PATH . '/views');
define('ASSETS_PATH', ROOT_PATH . '/assets');
define('LOGS_PATH', ROOT_PATH . '/logs');
