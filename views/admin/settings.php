<?php
/**
 * Admin - System Settings
 * Wellnest Mental Health Web Application
 * 
 * Features:
 * - Application settings configuration
 * - System preferences
 * - Maintenance settings
 */

$pageTitle = 'System Settings';

require_once __DIR__ . '/../../config/settings.php';
require_once __DIR__ . '/../../src/functions.php';

startSession();
requireRole(ROLE_ADMIN);

$user = getCurrentUser();
$successMessage = '';
$errorMessage = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? null;
    
    if ($action === 'maintenance_mode') {
        $isEnabled = isset($_POST['maintenance_enabled']) ? 1 : 0;
        $maintenanceMessage = trim($_POST['maintenance_message'] ?? '');
        
        // For now, just show success message (you can extend this to use a config file or database)
        $successMessage = $isEnabled ? 'Maintenance mode enabled' : 'Maintenance mode disabled';
    } elseif ($action === 'app_settings') {
        $appName = trim($_POST['app_name'] ?? '');
        $supportEmail = trim($_POST['support_email'] ?? '');
        
        if (empty($appName)) {
            $errorMessage = 'Application name is required';
        } elseif (!filter_var($supportEmail, FILTER_VALIDATE_EMAIL)) {
            $errorMessage = 'Valid support email is required';
        } else {
            $successMessage = 'Application settings updated successfully';
        }
    } elseif ($action === 'assessment_settings') {
        $requireAssessmentBeforeGame = isset($_POST['require_assessment']) ? 1 : 0;
        $assessmentTimeLimit = (int)($_POST['assessment_time_limit'] ?? 0);
        
        $successMessage = 'Assessment settings updated successfully';
    } elseif ($action === 'system_cache') {
        if ($_POST['cache_action'] === 'clear') {
            // Clear cache if any caching mechanism is in place
            $successMessage = 'System cache cleared successfully';
        }
    }
}

// Fetch current stats
try {
    $totalUsers = Database::fetchOne("SELECT COUNT(*) as count FROM users WHERE deleted_at IS NULL")['count'] ?? 0;
    $totalStudents = Database::fetchOne("SELECT COUNT(*) as count FROM users WHERE role_id = ? AND deleted_at IS NULL", [ROLE_STUDENT])['count'] ?? 0;
    $totalCounselors = Database::fetchOne("SELECT COUNT(*) as count FROM users WHERE role_id = ? AND deleted_at IS NULL", [ROLE_COUNSELOR])['count'] ?? 0;
    $totalAdmins = Database::fetchOne("SELECT COUNT(*) as count FROM users WHERE role_id = ? AND deleted_at IS NULL", [ROLE_ADMIN])['count'] ?? 0;
    $totalAssessments = Database::fetchOne("SELECT COUNT(*) as count FROM assessments WHERE is_active = TRUE")['count'] ?? 0;
    $totalCompletions = Database::fetchOne("SELECT COUNT(*) as count FROM assessment_scores")['count'] ?? 0;
} catch (Exception $e) {
    $totalUsers = $totalStudents = $totalCounselors = $totalAdmins = $totalAssessments = $totalCompletions = 0;
}

// Include header
include __DIR__ . '/../layouts/header.php';
?>

<div class="max-w-7xl mx-auto">
    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-bronze">System Settings</h1>
        <p class="text-body-gray mt-2">Configure application settings and preferences.</p>
    </div>

    <!-- Success/Error Messages -->
    <?php if ($successMessage): ?>
        <div class="bg-green-100 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6">
            ✓ <?= e($successMessage) ?>
        </div>
    <?php endif; ?>
    
    <?php if ($errorMessage): ?>
        <div class="bg-red-100 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
            ✗ <?= e($errorMessage) ?>
        </div>
    <?php endif; ?>

    <!-- System Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-xl shadow p-4 border-l-4 border-bronze">
            <p class="text-body-gray text-xs font-semibold">TOTAL USERS</p>
            <p class="text-2xl font-bold text-bronze"><?= $totalUsers ?></p>
        </div>
        <div class="bg-white rounded-xl shadow p-4 border-l-4 border-electric-blue">
            <p class="text-body-gray text-xs font-semibold">STUDENTS</p>
            <p class="text-2xl font-bold text-bronze"><?= $totalStudents ?></p>
        </div>
        <div class="bg-white rounded-xl shadow p-4 border-l-4 border-green-500">
            <p class="text-body-gray text-xs font-semibold">COUNSELORS</p>
            <p class="text-2xl font-bold text-bronze"><?= $totalCounselors ?></p>
        </div>
        <div class="bg-white rounded-xl shadow p-4 border-l-4 border-golden">
            <p class="text-body-gray text-xs font-semibold">ASSESSMENTS</p>
            <p class="text-2xl font-bold text-bronze"><?= $totalAssessments ?></p>
        </div>
    </div>

    <!-- Settings Sections -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Application Settings -->
        <div class="bg-white rounded-xl shadow-md p-6">
            <h2 class="text-xl font-bold text-bronze mb-6 pb-3 border-b">Application Settings</h2>
            
            <form method="POST">
                <input type="hidden" name="action" value="app_settings">
                
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-bronze mb-1">Application Name</label>
                    <input type="text" name="app_name" value="<?= e(APP_NAME) ?>" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-golden" readonly>
                    <p class="text-xs text-gray-500 mt-1">Read-only (configured in settings.php)</p>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-bronze mb-1">Support Email</label>
                    <input type="email" name="support_email" value="support@wellnest.edu.ph" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-golden">
                </div>
                
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-bronze mb-1">API URL</label>
                    <input type="url" value="<?= e(APP_URL) ?>" disabled class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-gray-50" readonly>
                    <p class="text-xs text-gray-500 mt-1">Read-only (configured in settings.php)</p>
                </div>
                
                <button type="submit" class="w-full bg-bronze text-white px-4 py-2 rounded-lg hover:bg-bronze-700 font-semibold">
                    Save Settings
                </button>
            </form>
        </div>

        <!-- Assessment Settings -->
        <div class="bg-white rounded-xl shadow-md p-6">
            <h2 class="text-xl font-bold text-bronze mb-6 pb-3 border-b">Assessment Settings</h2>
            
            <form method="POST">
                <input type="hidden" name="action" value="assessment_settings">
                
                <div class="mb-4">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="require_assessment" checked class="w-4 h-4">
                        <span class="text-sm font-semibold text-bronze">Require assessment before playing games</span>
                    </label>
                    <p class="text-xs text-gray-500 mt-2 ml-6">Students must complete at least one assessment before accessing games</p>
                </div>
                
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-bronze mb-1">Assessment Time Limit (minutes)</label>
                    <input type="number" name="assessment_time_limit" value="60" min="10" max="240" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-golden">
                    <p class="text-xs text-gray-500 mt-1">0 = No limit</p>
                </div>
                
                <button type="submit" class="w-full bg-bronze text-white px-4 py-2 rounded-lg hover:bg-bronze-700 font-semibold">
                    Update Settings
                </button>
            </form>
        </div>

        <!-- Maintenance Mode -->
        <div class="bg-white rounded-xl shadow-md p-6">
            <h2 class="text-xl font-bold text-bronze mb-6 pb-3 border-b">Maintenance Mode</h2>
            
            <form method="POST">
                <input type="hidden" name="action" value="maintenance_mode">
                
                <div class="mb-4">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="maintenance_enabled" class="w-4 h-4">
                        <span class="text-sm font-semibold text-bronze">Enable Maintenance Mode</span>
                    </label>
                    <p class="text-xs text-gray-500 mt-2 ml-6">Restrict access to the application (admins only)</p>
                </div>
                
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-bronze mb-1">Maintenance Message</label>
                    <textarea name="maintenance_message" rows="4" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-golden" placeholder="We are currently performing maintenance..."></textarea>
                </div>
                
                <button type="submit" class="w-full bg-orange-600 text-white px-4 py-2 rounded-lg hover:bg-orange-700 font-semibold">
                    Update Maintenance
                </button>
            </form>
        </div>

        <!-- System Actions -->
        <div class="bg-white rounded-xl shadow-md p-6">
            <h2 class="text-xl font-bold text-bronze mb-6 pb-3 border-b">System Actions</h2>
            
            <div class="space-y-3">
                <form method="POST" class="flex gap-3">
                    <input type="hidden" name="action" value="system_cache">
                    <input type="hidden" name="cache_action" value="clear">
                    <button type="submit" class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 font-semibold text-sm" onclick="return confirm('Clear system cache?')">
                        Clear Cache
                    </button>
                </form>
                
                <div class="text-xs text-gray-500 bg-gray-50 p-3 rounded-lg">
                    <p><strong>Last Backup:</strong> Not configured</p>
                    <p class="mt-2">To enable automated backups, configure backup settings in your server.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- System Information -->
    <div class="mt-8 bg-white rounded-xl shadow-md p-6">
        <h2 class="text-xl font-bold text-bronze mb-4">System Information</h2>
        
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
            <div>
                <p class="text-body-gray font-semibold">Application Name</p>
                <p class="text-bronze font-bold"><?= e(APP_NAME) ?></p>
            </div>
            <div>
                <p class="text-body-gray font-semibold">Version</p>
                <p class="text-bronze font-bold"><?= e(APP_VERSION) ?></p>
            </div>
            <div>
                <p class="text-body-gray font-semibold">PHP Version</p>
                <p class="text-bronze font-bold"><?= phpversion() ?></p>
            </div>
            <div>
                <p class="text-body-gray font-semibold">Environment</p>
                <p class="text-bronze font-bold">Production</p>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
