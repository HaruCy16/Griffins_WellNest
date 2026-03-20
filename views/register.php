<?php
/**
 * Registration Page
 * Wellnest Mental Health Web Application
 */

require_once __DIR__ . '/../config/settings.php';
require_once __DIR__ . '/../src/functions.php';

// Start session
startSession();

// If already logged in, redirect to dashboard
if (isLoggedIn()) {
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
    }
}

// Get flash message
$flash = getFlashMessage();

// Get registration errors and old input
$errors = $_SESSION['register_errors'] ?? [];
$oldData = $_SESSION['register_data'] ?? [];
unset($_SESSION['register_errors'], $_SESSION['register_data']);

// Fetch sections for dropdown
try {
    $sections = Database::fetchAll(
        "SELECT section_id, section_name, grade_level FROM sections WHERE is_active = TRUE ORDER BY grade_level, section_name"
    );
} catch (Exception $e) {
    $sections = [];
}

// Generate CSRF token
$csrfToken = generateCsrfToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - <?= e(APP_NAME) ?></title>
    <link href="../css/output.css" rel="stylesheet">
</head>
<body class="bg-cool-white min-h-screen flex items-center justify-center p-4">
    
    <div class="w-full max-w-lg">
        <!-- Logo/Header -->
        <div class="text-center mb-6">
            <img src="../assets/logo_griffin.png" alt="Griffins' WellNest" class="w-20 h-20 mx-auto mb-4 object-contain">
            <h1 class="text-3xl font-bold text-bronze"><?= e(APP_NAME) ?></h1>
            <p class="text-body-gray mt-2">Create Your Account</p>
        </div>

        <!-- Registration Card -->
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <h2 class="text-2xl font-semibold text-bronze mb-6 text-center">Student Registration</h2>
            
            <!-- Flash Message -->
            <?php if ($flash): ?>
                <div class="mb-4 p-4 rounded-lg <?= $flash['type'] === 'success' ? 'bg-green-100 text-green-700 border border-green-200' : ($flash['type'] === 'error' ? 'bg-red-100 text-red-700 border border-red-200' : 'bg-blue-100 text-blue-700 border border-blue-200') ?>">
                    <?= e($flash['message']) ?>
                </div>
            <?php endif; ?>
            
            <!-- General Error -->
            <?php if (!empty($errors['general'])): ?>
                <div class="mb-4 p-4 rounded-lg bg-red-100 text-red-700 border border-red-200">
                    <?= e($errors['general']) ?>
                </div>
            <?php endif; ?>
            
            <form action="../src/auth.php?action=register" method="POST" class="space-y-4">
                <!-- CSRF Token -->
                <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= e($csrfToken) ?>">
                
                <!-- Name Row -->
                <div class="grid grid-cols-2 gap-4">
                    <!-- First Name -->
                    <div>
                        <label for="first_name" class="block text-sm font-medium text-body-gray mb-1">First Name</label>
                        <input 
                            type="text" 
                            id="first_name" 
                            name="first_name" 
                            value="<?= e($oldData['first_name'] ?? '') ?>"
                            required
                            class="w-full px-4 py-3 border <?= isset($errors['first_name']) ? 'border-red-500' : 'border-gray-300' ?> rounded-lg focus:ring-2 focus:ring-golden focus:border-transparent transition duration-200 outline-none"
                            placeholder="Juan"
                        >
                        <?php if (isset($errors['first_name'])): ?>
                            <p class="mt-1 text-sm text-red-500"><?= e($errors['first_name']) ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Last Name -->
                    <div>
                        <label for="last_name" class="block text-sm font-medium text-body-gray mb-1">Last Name</label>
                        <input 
                            type="text" 
                            id="last_name" 
                            name="last_name" 
                            value="<?= e($oldData['last_name'] ?? '') ?>"
                            required
                            class="w-full px-4 py-3 border <?= isset($errors['last_name']) ? 'border-red-500' : 'border-gray-300' ?> rounded-lg focus:ring-2 focus:ring-golden focus:border-transparent transition duration-200 outline-none"
                            placeholder="Dela Cruz"
                        >
                        <?php if (isset($errors['last_name'])): ?>
                            <p class="mt-1 text-sm text-red-500"><?= e($errors['last_name']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-body-gray mb-1">Email Address</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        value="<?= e($oldData['email'] ?? '') ?>"
                        required
                        class="w-full px-4 py-3 border <?= isset($errors['email']) ? 'border-red-500' : 'border-gray-300' ?> rounded-lg focus:ring-2 focus:ring-golden focus:border-transparent transition duration-200 outline-none"
                        placeholder="juan.delacruz@my.nst.edu.ph"
                    >
                    <?php if (isset($errors['email'])): ?>
                        <p class="mt-1 text-sm text-red-500"><?= e($errors['email']) ?></p>
                    <?php endif; ?>
                </div>
                
                <!-- Student ID and Section Row -->
                <div class="grid grid-cols-2 gap-4">
                    <!-- Student ID -->
                    <div>
                        <label for="student_id" class="block text-sm font-medium text-body-gray mb-1">Student ID</label>
                        <input 
                            type="text" 
                            id="student_id" 
                            name="student_id" 
                            value="<?= e($oldData['student_id'] ?? '') ?>"
                            required
                            class="w-full px-4 py-3 border <?= isset($errors['student_id']) ? 'border-red-500' : 'border-gray-300' ?> rounded-lg focus:ring-2 focus:ring-golden focus:border-transparent transition duration-200 outline-none"
                            placeholder="2025SHS0299"
                        >
                        <?php if (isset($errors['student_id'])): ?>
                            <p class="mt-1 text-sm text-red-500"><?= e($errors['student_id']) ?></p>
                        <?php else: ?>
                            <p class="mt-1 text-xs text-gray-500">Format: YYYYSSSNNNN (e.g., 2025SHS0299)</p>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Section -->
                    <div>
                        <label for="section_id" class="block text-sm font-medium text-body-gray mb-1">Section</label>
                        <select 
                            id="section_id" 
                            name="section_id" 
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-golden focus:border-transparent transition duration-200 outline-none bg-white"
                        >
                            <option value="">Select Section</option>
                            <?php foreach ($sections as $section): ?>
                                <option 
                                    value="<?= $section['section_id'] ?>"
                                    <?= (($oldData['section_id'] ?? '') == $section['section_id']) ? 'selected' : '' ?>
                                >
                                    <?= e($section['section_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-body-gray mb-1">Password</label>
                    <div class="relative">
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            required
                            class="w-full px-4 py-3 border <?= isset($errors['password']) ? 'border-red-500' : 'border-gray-300' ?> rounded-lg focus:ring-2 focus:ring-golden focus:border-transparent transition duration-200 outline-none"
                            placeholder="Create a strong password"
                        >
                        <button 
                            type="button" 
                            onclick="togglePassword('password')"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700"
                        >
                            <svg id="password-eye" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
                    </div>
                    <?php if (isset($errors['password'])): ?>
                        <p class="mt-1 text-sm text-red-500"><?= e($errors['password']) ?></p>
                    <?php else: ?>
                        <p class="mt-1 text-xs text-gray-500">Min 8 characters with uppercase, lowercase, and number</p>
                    <?php endif; ?>
                </div>
                
                <!-- Confirm Password -->
                <div>
                    <label for="confirm_password" class="block text-sm font-medium text-body-gray mb-1">Confirm Password</label>
                    <div class="relative">
                        <input 
                            type="password" 
                            id="confirm_password" 
                            name="confirm_password" 
                            required
                            class="w-full px-4 py-3 border <?= isset($errors['confirm_password']) ? 'border-red-500' : 'border-gray-300' ?> rounded-lg focus:ring-2 focus:ring-golden focus:border-transparent transition duration-200 outline-none"
                            placeholder="Confirm your password"
                        >
                        <button 
                            type="button" 
                            onclick="togglePassword('confirm_password')"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700"
                        >
                            <svg id="confirm_password-eye" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
                    </div>
                    <?php if (isset($errors['confirm_password'])): ?>
                        <p class="mt-1 text-sm text-red-500"><?= e($errors['confirm_password']) ?></p>
                    <?php endif; ?>
                </div>
                
                <!-- Submit Button -->
                <button 
                    type="submit" 
                    class="w-full bg-golden text-bronze-800 py-3 px-4 rounded-lg font-semibold hover:bg-golden-400 focus:ring-4 focus:ring-golden-200 transition duration-200 mt-6"
                >
                    Create Account
                </button>
            </form>
            
            <!-- Login Link -->
            <div class="mt-6 text-center">
                <p class="text-body-gray">
                    Already have an account? 
                    <a href="login.php" class="text-electric-blue font-semibold hover:text-electric-blue-700">Sign in</a>
                </p>
            </div>
        </div>
        
        <!-- Back to Home -->
        <div class="text-center mt-6">
            <a href="../index.php" class="text-body-gray hover:text-bronze text-sm">
                ← Back to Home
            </a>
        </div>
    </div>
    
    <script>
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const eye = document.getElementById(inputId + '-eye');
            
            if (input.type === 'password') {
                input.type = 'text';
                eye.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                `;
            } else {
                input.type = 'password';
                eye.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                `;
            }
        }
    </script>
</body>
</html>
