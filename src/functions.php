<?php
/**
 * Helper Functions
 * Wellnest Mental Health Web Application
 */

// Prevent direct access
if (!defined('WELLNEST_APP')) {
    define('WELLNEST_APP', true);
}

// Load database class
require_once __DIR__ . '/../config/database.php';

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
 * Build application URL using APP_URL base path.
 * @param string $path Absolute or relative path from app root
 * @return string
 */
function appUrl(string $path = ''): string {
    $base = rtrim(APP_URL, '/');

    if ($path === '') {
        return $base === '' ? '/' : $base;
    }

    if (preg_match('#^https?://#i', $path) === 1) {
        return $path;
    }

    return ($base === '' ? '' : $base) . '/' . ltrim($path, '/');
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
function requireLogin(string $redirectTo = ''): void {
    if ($redirectTo === '') {
        $redirectTo = appUrl('/views/login.php');
    }

    if (!isLoggedIn()) {
        redirectWithMessage($redirectTo, 'Please log in to continue.', 'warning');
    }
}

/**
 * Require specific role(s)
 * @param int|array $allowedRoles Role ID or array of role IDs
 * @param string $redirectTo URL to redirect if not authorized
 */
function requireRole(int|array $allowedRoles, string $redirectTo = ''): void {
    if ($redirectTo === '') {
        $redirectTo = appUrl('/index.php');
    }

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

// =============================================================================
// API HELPER FUNCTIONS
// =============================================================================

/**
 * Return JSON success response
 * @param mixed $data Data to return
 * @param string $message Success message
 * @param int $statusCode HTTP status code
 */
function respondSuccess($data = [], string $message = 'Success', int $statusCode = 200): void {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => $message,
        'data' => $data,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    exit;
}

/**
 * Return JSON error response
 * @param string $message Error message
 * @param int $statusCode HTTP status code
 * @param mixed $errors Additional error details
 */
function respondError(string $message = 'Error', int $statusCode = 400, $errors = null): void {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    $response = [
        'success' => false,
        'message' => $message,
        'code' => $statusCode,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    if ($errors !== null) {
        $response['errors'] = $errors;
    }
    echo json_encode($response);
    exit;
}

/**
 * Require user to be authenticated and optionally check role
 * @param int|array|null $allowedRoles Optional role ID(s) to restrict access
 * @return bool|void Returns true if authorized, exits if not
 */
function requireAuth($allowedRoles = null) {
    startSession();
    
    if (!isLoggedIn()) {
        return respondError('Unauthorized: Please log in', 401);
    }
    
    // If specific roles required, check them
    if ($allowedRoles !== null) {
        $allowedRoles = is_array($allowedRoles) ? $allowedRoles : [$allowedRoles];
        $currentRole = getCurrentUserRole();
        
        if (!in_array($currentRole, $allowedRoles)) {
            return respondError('Forbidden: You do not have permission to access this resource', 403);
        }
    }
    
    return true;
}

/**
 * Execute a query with parameters
 * @param string $sql SQL query with placeholders
 * @param array $params Parameters to bind
 * @return int Returns affected rows count
 */
function executeQuery(string $sql, array $params = []): int {
    try {
        $stmt = Database::query($sql, $params);
        return $stmt->rowCount();
    } catch (Exception $e) {
        logError('Query Execution Error', ['sql' => $sql, 'error' => $e->getMessage()]);
        throw $e;
    }
}

/**
 * Check if a record exists in a table
 * @param string $table Table name
 * @param array $where Where conditions ['column' => 'value']
 * @return bool
 */
function recordExists(string $table, array $where = []): bool {
    try {
        $conditions = [];
        $params = [];
        
        foreach ($where as $column => $value) {
            $conditions[] = "$column = ?";
            $params[] = $value;
        }
        
        $sql = "SELECT COUNT(*) as count FROM $table";
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }
        
        $result = Database::fetchOne($sql, $params);
        return ($result['count'] ?? 0) > 0;
    } catch (Exception $e) {
        logError('Record Exists Check Error', ['table' => $table, 'error' => $e->getMessage()]);
        return false;
    }
}

/**
 * Log error message
 * @param string $context Context/title of error
 * @param array $details Error details
 */
function logError(string $context, array $details = []): void {
    $logMessage = "[" . date('Y-m-d H:i:s') . "] " . $context;
    
    if (!empty($details)) {
        $logMessage .= " | " . json_encode($details);
    }
    
    $logFile = __DIR__ . '/../logs/error.log';
    
    // Create logs directory if it doesn't exist
    if (!is_dir(dirname($logFile))) {
        mkdir(dirname($logFile), 0755, true);
    }
    
    error_log($logMessage . PHP_EOL, 3, $logFile);
}

/**
 * Alias for logAction - logs to audit_logs table
 * @param string $actionType CREATE, READ, UPDATE, DELETE, LOGIN, LOGOUT, SUBMIT_RESPONSE, COMPLETE_ASSESSMENT, etc
 * @param string $entityType Table/entity name
 * @param int|null $userId User ID (defaults to current user)
 * @param array $details Additional details
 */
function logAudit(string $actionType, string $entityType, ?int $userId = null, array $details = []): void {
    try {
        $userId = $userId ?? getCurrentUserId();
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
        $description = !empty($details) ? json_encode($details) : null;
        
        Database::insert(
            "INSERT INTO audit_logs (user_id, action_type, entity_type, description, ip_address, user_agent) 
             VALUES (?, ?, ?, ?, ?, ?)",
            [$userId, $actionType, $entityType, $description, $ip, $userAgent]
        );
    } catch (Exception $e) {
        logError('Audit log failed', ['error' => $e->getMessage()]);
    }
}

/**
 * Get database connection (for compatibility with provided API code)
 * Should use Database:: class instead, but kept for backward compatibility
 * @return PDO
 */
function getConnection(): PDO {
    return Database::getConnection();
}

/**
 * Fetch single row (alias for Database::fetchOne)
 * @param string $sql SQL query
 * @param array $params Parameters
 * @return array|false
 */
function fetchOne(string $sql, array $params = []) {
    return Database::fetchOne($sql, $params);
}

/**
 * Fetch all rows (alias for Database::fetchAll)
 * @param string $sql SQL query
 * @param array $params Parameters
 * @return array
 */
function fetchAll(string $sql, array $params = []): array {
    return Database::fetchAll($sql, $params);
}

// =============================================================================
// ASSESSMENT HELPERS
// =============================================================================

/**
 * Generate detailed, personalized conclusion based on assessment type and score
 * @param string $assessmentType Type of assessment
 * @param float $percentage Percentage score
 * @param string $riskLevel Risk level
 * @return array Detailed conclusion with title, summary, and advice
 */
function generateDetailedConclusion(string $assessmentType, float $percentage, string $riskLevel): array {
    $conclusions = [];

    switch ($assessmentType) {
        case 'help_seeking':
            // Help-seeking attitudes: Higher = Better
            $conclusions = [
                'low' => [
                    'title' => '✓ Positive Help-Seeking Attitudes',
                    'emoji' => '👍',
                    'summary' => 'Excellent! You have positive attitudes toward seeking help and support. Scores at ' . round($percentage) . '% indicate you recognize the value of counseling and are comfortable reaching out.',
                    'details' => 'You understand that seeking help is a sign of strength, not weakness. You\'re likely to pursue support when needed and encourage others to do the same.',
                    'action' => 'Continue fostering these healthy attitudes. Remember, the school counselor is always available if you need support.'
                ],
                'medium' => [
                    'title' => '⚠ Mixed Help-Seeking Attitudes',
                    'emoji' => '🤔',
                    'summary' => 'Your score of ' . round($percentage) . '% suggests some uncertainty about seeking help. You may sometimes feel hesitant about reaching out.',
                    'details' => 'It\'s normal to have mixed feelings about counseling. Many students feel this way initially. Understanding that seeking help is valuable can improve your wellbeing.',
                    'action' => 'Consider exploring what makes you hesitant about seeking help. Try our wellness games or chatting with a counselor - they can help you build confidence.'
                ],
                'high' => [
                    'title' => '⚠ Concerns About Help-Seeking',
                    'emoji' => '😟',
                    'summary' => 'Your score of ' . round($percentage) . '% indicates some barriers to seeking help. You may feel uncomfortable asking for support.',
                    'details' => 'Many students feel uncomfortable seeking help due to stigma, fear of judgment, or uncertainty. These feelings are valid but can be overcome.',
                    'action' => 'We encourage you to talk with our school counselor confidentially. They\'re trained to help and are there specifically to support you.'
                ],
                'critical' => [
                    'title' => '🚨 Significant Help-Seeking Barriers',
                    'emoji' => '🆘',
                    'summary' => 'Your score of ' . round($percentage) . '% shows significant reluctance toward seeking help. This is concerning as support is crucial for wellbeing.',
                    'details' => 'Please know that seeking help is not weakness. Counselors are confidential, non-judgmental, and experienced in helping students.',
                    'action' => 'We strongly recommend scheduling a meeting with our school counselor. Your mental health is important, and they\'re here to support you.'
                ]
            ];
            break;

        case 'stress':
            // Stress: Lower % = Low stress, Higher % = High stress
            $conclusions = [
                'low' => [
                    'title' => '✓ Healthy Stress Levels',
                    'emoji' => '😊',
                    'summary' => 'Great! Your stress score of ' . round($percentage) . '% indicates you\'re managing stress well. You\'re maintaining healthy coping skills.',
                    'details' => 'You\'re handling life\'s challenges effectively and likely have good support systems and coping mechanisms in place.',
                    'action' => 'Continue your current wellness habits. Regular exercise, good sleep, and social connections help maintain this balance.'
                ],
                'medium' => [
                    'title' => '⚠ Moderate Stress Levels',
                    'emoji' => '😐',
                    'summary' => 'Your stress score of ' . round($percentage) . '% shows moderate stress levels. You\'re managing but could benefit from additional support.',
                    'details' => 'It\'s normal to experience stress, but there are ways to better manage it. Identifying your stressors can help.',
                    'action' => 'Try our relaxation games, journaling, or talking to someone you trust. Even small stress-relief activities can help.'
                ],
                'high' => [
                    'title' => '⚠ High Stress Levels',
                    'emoji' => '😤',
                    'summary' => 'Your stress score of ' . round($percentage) . '% indicates elevated stress that deserves attention. You may be feeling overwhelmed.',
                    'details' => 'High stress can affect your health, sleep, and academics. Identifying stressors and developing coping strategies is important.',
                    'action' => 'We recommend trying our therapeutic games, breathing exercises, or scheduling time with a counselor to develop a stress management plan.'
                ],
                'critical' => [
                    'title' => '🚨 Critical Stress Levels',
                    'emoji' => '😰',
                    'summary' => 'Your stress score of ' . round($percentage) . '% indicates critical stress levels. You may be feeling severe overwhelm or anxiety.',
                    'details' => 'When stress reaches this level, professional support becomes especially important. You don\'t have to face this alone.',
                    'action' => 'Please reach out to our school counselor immediately. They can help you develop coping strategies and connect you with additional resources.'
                ]
            ];
            break;

        case 'anxiety':
            // Anxiety: Lower % = Low anxiety, Higher % = High anxiety  
            $conclusions = [
                'low' => [
                    'title' => '✓ Low Anxiety Levels',
                    'emoji' => '😌',
                    'summary' => 'Excellent! Your anxiety score of ' . round($percentage) . '% shows you\'re managing anxiety well. You\'re calm and grounded.',
                    'details' => 'You have good control over anxious thoughts and feelings. Your coping strategies are working effectively.',
                    'action' => 'Maintain your current healthy habits. Keep practicing relaxation techniques and maintaining your support network.'
                ],
                'medium' => [
                    'title' => '⚠ Moderate Anxiety',
                    'emoji' => '😟',
                    'summary' => 'Your anxiety score of ' . round($percentage) . '% indicates moderate anxiety. You likely feel nervous or worried at times.',
                    'details' => 'Some anxiety is normal, especially during challenging situations. Learning specific techniques can help you manage it better.',
                    'action' => 'Try our breathing games, mindfulness exercises, or journaling. These can help reduce anxious thoughts.'
                ],
                'high' => [
                    'title' => '⚠ High Anxiety Levels',
                    'emoji' => '😨',
                    'summary' => 'Your anxiety score of ' . round($percentage) . '% shows elevated anxiety that\'s likely affecting your daily life.',
                    'details' => 'High anxiety can make it hard to concentrate, sleep, or enjoy activities. Professional support can teach you effective strategies.',
                    'action' => 'We recommend speaking with a counselor about anxiety management techniques. Therapy can be very effective.'
                ],
                'critical' => [
                    'title' => '🚨 Severe Anxiety',
                    'emoji' => '😱',
                    'summary' => 'Your anxiety score of ' . round($percentage) . '% indicates severe anxiety that needs professional support.',
                    'details' => 'Severe anxiety is interfering with your functioning. You deserve professional help to manage these feelings.',
                    'action' => 'Please contact our school counselor right away. They can provide immediate support and connect you with resources.'
                ]
            ];
            break;

        case 'depression':
            // Depression: Lower % = Low depression, Higher % = High depression
            $conclusions = [
                'low' => [
                    'title' => '✓ Low Depression Levels',
                    'emoji' => '😊',
                    'summary' => 'Great! Your depression score of ' . round($percentage) . '% shows you\'re in a positive mental state. You\'re managing well.',
                    'details' => 'You maintain positive mood and energy levels. Your outlook and engagement with life are healthy.',
                    'action' => 'Continue your current lifestyle and self-care practices. Stay connected with friends and maintain activities you enjoy.'
                ],
                'medium' => [
                    'title' => '⚠ Moderate Depressive Symptoms',
                    'emoji' => '😐',
                    'summary' => 'Your depression score of ' . round($percentage) . '% shows some depressive symptoms. You may feel lonely or lack energy sometimes.',
                    'details' => 'These feelings are common in students. Increasing social connection and physical activity often helps improve mood.',
                    'action' => 'Engage in activities you enjoy, spend time with friends, and maintain regular sleep and exercise. Consider talking to someone.'
                ],
                'high' => [
                    'title' => '⚠ High Depressive Symptoms',
                    'emoji' => '😔',
                    'summary' => 'Your depression score of ' . round($percentage) . '% indicates significant depressive symptoms affecting your life.',
                    'details' => 'You may be experiencing persistent sadness, loss of interest in activities, or difficulty concentrating. Professional support can help.',
                    'action' => 'Please speak with our school counselor. They can help you understand these feelings and develop a recovery plan.'
                ],
                'critical' => [
                    'title' => '🚨 Severe Depression',
                    'emoji' => '😞',
                    'summary' => 'Your depression score of ' . round($percentage) . '% shows severe depressive symptoms. You need professional support now.',
                    'details' => 'Severe depression requires treatment. You\'re not alone, and help is available. Recovery is possible.',
                    'action' => 'Please contact our school counselor immediately or call a crisis helpline. Your wellbeing is our priority.'
                ]
            ];
            break;

        case 'school_experience':
            // School experience: Higher % = Better experience, Lower % = Worse
            $conclusions = [
                'low' => [
                    'title' => '😠 Poor School Experience',
                    'emoji' => '😞',
                    'summary' => 'Your school experience score of ' . round($percentage) . '% is low. You\'re struggling with school life.',
                    'details' => 'Something about your school environment may be causing dissatisfaction. This could relate to academics, social dynamics, or other factors.',
                    'action' => 'Talk with a counselor about what\'s making school difficult. They can help you navigate challenges and find solutions.'
                ],
                'medium' => [
                    'title' => '🤷 Mixed School Experience',
                    'emoji' => '😐',
                    'summary' => 'Your school experience score of ' . round($percentage) . '% shows mixed feelings about school.',
                    'details' => 'You find some good things about school but also face challenges. Finding your niche can improve your experience.',
                    'action' => 'Consider joining clubs or activities you\'re interested in. Connect with classmates who share your interests.'
                ],
                'high' => [
                    'title' => '😊 Good School Experience',
                    'emoji' => '😊',
                    'summary' => 'Excellent! Your school experience score of ' . round($percentage) . '% shows you\'re generally satisfied with school.',
                    'details' => 'You\'ve developed positive relationships and are engaged with school life. You\'re thriving academically and socially.',
                    'action' => 'Keep up your positive engagement! Share what works for you with other students.'
                ],
                'critical' => [
                    'title' => '✓ Outstanding School Experience',
                    'emoji' => '🎉',
                    'summary' => 'Outstanding! Your school experience score of ' . round($percentage) . '% shows you\'re thriving at school!',
                    'details' => 'You\'re fully engaged, making strong connections, and enjoying your school experience. You\'re making the most of your time here.',
                    'action' => 'Continue being a positive presence at school. Help others who may be struggling with their own experiences.'
                ]
            ];
            break;

        default:
            // Generic conclusion for unspecified types
            $conclusions = [
                'low' => [
                    'title' => '✓ Low Risk',
                    'emoji' => '✓',
                    'summary' => 'Your assessment score of ' . round($percentage) . '% indicates low risk. You\'re managing well.',
                    'details' => 'You\'re showing positive indicators in this area. Keep up your current approach.',
                    'action' => 'Continue what you\'re doing. Regular check-ins and self-care are important.'
                ],
                'medium' => [
                    'title' => '⚠ Medium Risk',
                    'emoji' => '⚠',
                    'summary' => 'Your assessment score of ' . round($percentage) . '% shows areas that could use attention.',
                    'details' => 'There are opportunities to improve. Consider engaging with wellness resources.',
                    'action' => 'Try our wellness activities, games, or speak with a counselor for guidance.'
                ],
                'high' => [
                    'title' => '⚠ High Risk',
                    'emoji' => '⚠',
                    'summary' => 'Your assessment score of ' . round($percentage) . '% indicates areas that need attention.',
                    'details' => 'Professional support would be beneficial at this time.',
                    'action' => 'We recommend scheduling time with our school counselor soon.'
                ],
                'critical' => [
                    'title' => '🚨 Critical Risk',
                    'emoji' => '🚨',
                    'summary' => 'Your assessment score of ' . round($percentage) . '% indicates critical concerns.',
                    'details' => 'Professional support is important now. You don\'t have to handle this alone.',
                    'action' => 'Please reach out to our school counselor immediately.'
                ]
            ];
            break;
    }

    return $conclusions[$riskLevel] ?? $conclusions['medium'];
}
