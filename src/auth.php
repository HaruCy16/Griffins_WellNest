<?php
/**
 * Authentication Handler
 * Handles Login, Register, and Logout
 * Wellnest Mental Health Web Application
 */

require_once __DIR__ . '/../config/settings.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/validators.php';

// Start session
startSession();

// Get action from request
$action = $_GET['action'] ?? $_POST['action'] ?? 'login';

// Route to appropriate handler
switch ($action) {
    case 'login':
        handleLogin();
        break;
    case 'register':
        handleRegister();
        break;
    case 'logout':
        handleLogout();
        break;
    default:
        redirect('/Wellnest_Sim_Web_Application/views/login.php');
}

// =============================================================================
// LOGIN HANDLER
// =============================================================================

function handleLogin(): void {
    // If already logged in, redirect to dashboard
    if (isLoggedIn()) {
        redirectToDashboard();
    }
    
    // If not POST request, show login form
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        redirect('/Wellnest_Sim_Web_Application/views/login.php');
    }
    
    // Verify CSRF token
    if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? null)) {
        redirectWithMessage('/Wellnest_Sim_Web_Application/views/login.php', 'Invalid request. Please try again.', 'error');
    }
    
    // Get form data
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Validate input
    $validation = validateLogin(['email' => $email, 'password' => $password]);
    if (!$validation->isValid) {
        $_SESSION['login_errors'] = $validation->getAllErrors();
        $_SESSION['login_email'] = $email;
        redirect('/Wellnest_Sim_Web_Application/views/login.php');
    }
    
    // Find user by email
    $user = Database::fetchOne(
        "SELECT u.*, r.role_name 
         FROM users u 
         JOIN roles r ON u.role_id = r.role_id 
         WHERE u.email = ? AND u.deleted_at IS NULL",
        [$email]
    );
    
    // Check if user exists
    if (!$user) {
        $_SESSION['login_errors'] = ['email' => 'Invalid email or password.'];
        $_SESSION['login_email'] = $email;
        redirect('/Wellnest_Sim_Web_Application/views/login.php');
    }
    
    // Check if account is active
    if (!$user['is_active']) {
        $_SESSION['login_errors'] = ['email' => 'Your account has been deactivated. Please contact support.'];
        $_SESSION['login_email'] = $email;
        redirect('/Wellnest_Sim_Web_Application/views/login.php');
    }
    
    // Verify password
    if (!verifyPassword($password, $user['password_hash'])) {
        $_SESSION['login_errors'] = ['email' => 'Invalid email or password.'];
        $_SESSION['login_email'] = $email;
        redirect('/Wellnest_Sim_Web_Application/views/login.php');
    }
    
    // Login successful - set session
    setUserSession($user);
    
    // Update last login time
    Database::query(
        "UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE user_id = ?",
        [$user['user_id']]
    );
    
    // Log the action
    logAction('LOGIN', 'users', $user['user_id'], 'User logged in');
    
    // Redirect to appropriate dashboard
    redirectToDashboard();
}

// =============================================================================
// REGISTER HANDLER
// =============================================================================

function handleRegister(): void {
    // If already logged in, redirect to dashboard
    if (isLoggedIn()) {
        redirectToDashboard();
    }
    
    // If not POST request, show register form
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        redirect('/Wellnest_Sim_Web_Application/views/register.php');
    }
    
    // Verify CSRF token
    if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? null)) {
        redirectWithMessage('/Wellnest_Sim_Web_Application/views/register.php', 'Invalid request. Please try again.', 'error');
    }
    
    // Get form data
    $data = [
        'first_name' => trim($_POST['first_name'] ?? ''),
        'last_name' => trim($_POST['last_name'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'student_id' => trim($_POST['student_id'] ?? ''),
        'section_id' => !empty($_POST['section_id']) ? (int)$_POST['section_id'] : null,
        'password' => $_POST['password'] ?? '',
        'confirm_password' => $_POST['confirm_password'] ?? '',
        'role_id' => ROLE_STUDENT // Students register themselves
    ];
    
    // Validate input
    $validation = validateRegistration($data);
    if (!$validation->isValid) {
        $_SESSION['register_errors'] = $validation->getAllErrors();
        $_SESSION['register_data'] = $data;
        unset($_SESSION['register_data']['password'], $_SESSION['register_data']['confirm_password']);
        redirect('/Wellnest_Sim_Web_Application/views/register.php');
    }
    
    try {
        // Begin transaction
        Database::beginTransaction();
        
        // Hash password
        $passwordHash = hashPassword($data['password']);
        
        // Insert user
        $userId = Database::insert(
            "INSERT INTO users (email, password_hash, first_name, last_name, role_id, section_id, student_id, is_active) 
             VALUES (?, ?, ?, ?, ?, ?, ?, TRUE)",
            [
                $data['email'],
                $passwordHash,
                $data['first_name'],
                $data['last_name'],
                ROLE_STUDENT,
                $data['section_id'],
                $data['student_id']
            ]
        );
        
        // Log the action
        logAction('CREATE', 'users', $userId, 'New student registered');
        
        // Commit transaction
        Database::commit();
        
        // Redirect to login with success message
        redirectWithMessage(
            '/Wellnest_Sim_Web_Application/views/login.php',
            'Registration successful! Please log in with your credentials.',
            'success'
        );
        
    } catch (Exception $e) {
        Database::rollback();
        error_log("Registration error: " . $e->getMessage());
        
        $_SESSION['register_errors'] = ['general' => 'Registration failed. Please try again.'];
        $_SESSION['register_data'] = $data;
        unset($_SESSION['register_data']['password'], $_SESSION['register_data']['confirm_password']);
        redirect('/Wellnest_Sim_Web_Application/views/register.php');
    }
}

// =============================================================================
// LOGOUT HANDLER
// =============================================================================

function handleLogout(): void {
    $userId = getCurrentUserId();
    
    if ($userId) {
        logAction('LOGOUT', 'users', $userId, 'User logged out');
    }
    
    destroySession();
    redirectWithMessage('/Wellnest_Sim_Web_Application/views/login.php', 'You have been logged out.', 'success');
}

// =============================================================================
// HELPER FUNCTIONS
// =============================================================================

/**
 * Redirect user to appropriate dashboard based on role
 */
function redirectToDashboard(): void {
    $role = getCurrentUserRole();
    
    switch ($role) {
        case ROLE_STUDENT:
            redirect('/Wellnest_Sim_Web_Application/views/student/dashboard.php');
            break;
        case ROLE_COUNSELOR:
            redirect('/Wellnest_Sim_Web_Application/views/counselor/dashboard.php');
            break;
        case ROLE_ADMIN:
            redirect('/Wellnest_Sim_Web_Application/views/admin/dashboard.php');
            break;
        default:
            redirect('/Wellnest_Sim_Web_Application/index.php');
    }
}
