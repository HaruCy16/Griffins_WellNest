<?php
/**
 * Helper Functions
 * Wellnest Mental Health Web Application
 */

// Prevent direct access
if (!defined('WELLNEST_APP')) {
    define('WELLNEST_APP', true);
}

// =============================================================================
// SESSION FUNCTIONS
// =============================================================================

/**
 * Start secure session
 */
function startSession(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_name(SESSION_NAME);
        session_start();
        
        // Regenerate session ID periodically for security
        if (!isset($_SESSION['created'])) {
            $_SESSION['created'] = time();
        } elseif (time() - $_SESSION['created'] > 1800) {
            // Session started more than 30 minutes ago
            session_regenerate_id(true);
            $_SESSION['created'] = time();
        }
    }
}

/**
 * Check if user is logged in
 * @return bool
 */
function isLoggedIn(): bool {
    startSession();
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Get current user ID
 * @return int|null
 */
function getCurrentUserId(): ?int {
    startSession();
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current user role ID
 * @return int|null
 */
function getCurrentUserRole(): ?int {
    startSession();
    return $_SESSION['role_id'] ?? null;
}

/**
 * Get current user data from session
 * @return array|null
 */
function getCurrentUser(): ?array {
    startSession();
    if (!isLoggedIn()) {
        return null;
    }
    return [
        'user_id' => $_SESSION['user_id'],
        'email' => $_SESSION['email'] ?? '',
        'first_name' => $_SESSION['first_name'] ?? '',
        'last_name' => $_SESSION['last_name'] ?? '',
        'role_id' => $_SESSION['role_id'] ?? null,
        'role_name' => $_SESSION['role_name'] ?? ''
    ];
}

/**
 * Set user session data after login
 * @param array $user User data from database
 */
function setUserSession(array $user): void {
    startSession();
    session_regenerate_id(true);
    
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['first_name'] = $user['first_name'];
    $_SESSION['last_name'] = $user['last_name'];
    $_SESSION['role_id'] = $user['role_id'];
    $_SESSION['role_name'] = $user['role_name'] ?? ROLE_NAMES[$user['role_id']] ?? 'unknown';
    $_SESSION['created'] = time();
    $_SESSION['last_activity'] = time();
}

/**
 * Destroy session (logout)
 */
function destroySession(): void {
    startSession();
    $_SESSION = [];
    
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }
    
    session_destroy();
}

// =============================================================================
// CSRF PROTECTION
// =============================================================================

/**
 * Generate CSRF token
 * @return string
 */
function generateCsrfToken(): string {
    startSession();
    if (empty($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
        $_SESSION['csrf_time'] = time();
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

/**
 * Get CSRF token HTML input field
 * @return string
 */
function csrfField(): string {
    $token = generateCsrfToken();
    return '<input type="hidden" name="' . CSRF_TOKEN_NAME . '" value="' . htmlspecialchars($token) . '">';
}

/**
 * Verify CSRF token
 * @param string|null $token Token from form submission
 * @return bool
 */
function verifyCsrfToken(?string $token): bool {
    startSession();
    
    if (empty($token) || empty($_SESSION[CSRF_TOKEN_NAME])) {
        return false;
    }
    
    // Check if token expired
    if (isset($_SESSION['csrf_time']) && (time() - $_SESSION['csrf_time']) > CSRF_TOKEN_LIFETIME) {
        unset($_SESSION[CSRF_TOKEN_NAME], $_SESSION['csrf_time']);
        return false;
    }
    
    return hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

// =============================================================================
// REDIRECT & RESPONSE FUNCTIONS
// =============================================================================

/**
 * Redirect to URL
 * @param string $url URL to redirect to
 * @param int $statusCode HTTP status code
 */
function redirect(string $url, int $statusCode = 302): void {
    header("Location: {$url}", true, $statusCode);
    exit;
}

/**
 * Redirect with flash message
 * @param string $url URL to redirect to
 * @param string $message Message to display
 * @param string $type Message type (success, error, warning, info)
 */
function redirectWithMessage(string $url, string $message, string $type = 'info'): void {
    startSession();
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
    redirect($url);
}

/**
 * Get and clear flash message
 * @return array|null ['message' => string, 'type' => string]
 */
function getFlashMessage(): ?array {
    startSession();
    if (isset($_SESSION['flash_message'])) {
        $flash = [
            'message' => $_SESSION['flash_message'],
            'type' => $_SESSION['flash_type'] ?? 'info'
        ];
        unset($_SESSION['flash_message'], $_SESSION['flash_type']);
        return $flash;
    }
    return null;
}

/**
 * Return JSON response
 * @param array $data Data to return
 * @param int $statusCode HTTP status code
 */
function jsonResponse(array $data, int $statusCode = 200): void {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

// =============================================================================
// ACCESS CONTROL
// =============================================================================

/**
 * Require user to be logged in
 * @param string $redirectTo URL to redirect if not logged in
 */
function requireLogin(string $redirectTo = '/Wellnest_Sim_Web_Application/views/login.php'): void {
    if (!isLoggedIn()) {
        redirectWithMessage($redirectTo, 'Please log in to continue.', 'warning');
    }
}

/**
 * Require specific role(s)
 * @param int|array $allowedRoles Role ID or array of role IDs
 * @param string $redirectTo URL to redirect if not authorized
 */
function requireRole(int|array $allowedRoles, string $redirectTo = '/Wellnest_Sim_Web_Application/index.php'): void {
    requireLogin();
    
    $allowedRoles = is_array($allowedRoles) ? $allowedRoles : [$allowedRoles];
    $currentRole = getCurrentUserRole();
    
    if (!in_array($currentRole, $allowedRoles)) {
        redirectWithMessage($redirectTo, 'You do not have permission to access this page.', 'error');
    }
}

/**
 * Check if current user is student
 * @return bool
 */
function isStudent(): bool {
    return getCurrentUserRole() === ROLE_STUDENT;
}

/**
 * Check if current user is counselor
 * @return bool
 */
function isCounselor(): bool {
    return getCurrentUserRole() === ROLE_COUNSELOR;
}

/**
 * Check if current user is admin
 * @return bool
 */
function isAdmin(): bool {
    return getCurrentUserRole() === ROLE_ADMIN;
}

// =============================================================================
// INPUT & OUTPUT HELPERS
// =============================================================================

/**
 * Sanitize input string
 * @param string|null $input Input string
 * @return string
 */
function sanitize(?string $input): string {
    if ($input === null) {
        return '';
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Get POST value with default
 * @param string $key POST key
 * @param mixed $default Default value
 * @return mixed
 */
function post(string $key, mixed $default = ''): mixed {
    return $_POST[$key] ?? $default;
}

/**
 * Get GET value with default
 * @param string $key GET key
 * @param mixed $default Default value
 * @return mixed
 */
function get(string $key, mixed $default = ''): mixed {
    return $_GET[$key] ?? $default;
}

/**
 * Escape output for HTML
 * @param string|null $string String to escape
 * @return string
 */
function e(?string $string): string {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

// =============================================================================
// LOGGING
// =============================================================================

/**
 * Log action to audit_logs table
 * @param string $actionType CREATE, READ, UPDATE, DELETE, LOGIN, LOGOUT
 * @param string $entityType Table/entity name
 * @param int|null $entityId ID of affected entity
 * @param string|null $description Description of action
 */
function logAction(string $actionType, string $entityType, ?int $entityId = null, ?string $description = null): void {
    try {
        $userId = getCurrentUserId();
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
        
        Database::insert(
            "INSERT INTO audit_logs (user_id, action_type, entity_type, entity_id, description, ip_address, user_agent) 
             VALUES (?, ?, ?, ?, ?, ?, ?)",
            [$userId, $actionType, $entityType, $entityId, $description, $ip, $userAgent]
        );
    } catch (Exception $e) {
        error_log("Audit log failed: " . $e->getMessage());
    }
}

// =============================================================================
// DATE & TIME HELPERS
// =============================================================================

/**
 * Format date for display
 * @param string|null $datetime Database datetime
 * @param string $format PHP date format
 * @return string
 */
function formatDate(?string $datetime, string $format = 'M d, Y'): string {
    if (empty($datetime)) {
        return '';
    }
    return date($format, strtotime($datetime));
}

/**
 * Format datetime for display
 * @param string|null $datetime Database datetime
 * @param string $format PHP date format
 * @return string
 */
function formatDateTime(?string $datetime, string $format = 'M d, Y h:i A'): string {
    if (empty($datetime)) {
        return '';
    }
    return date($format, strtotime($datetime));
}

/**
 * Get time ago string
 * @param string $datetime Database datetime
 * @return string
 */
function timeAgo(string $datetime): string {
    $time = strtotime($datetime);
    $diff = time() - $time;
    
    if ($diff < 60) {
        return 'just now';
    } elseif ($diff < 3600) {
        $mins = floor($diff / 60);
        return $mins . ' minute' . ($mins > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } else {
        return formatDate($datetime);
    }
}
