<?php
/**
 * Admin Dashboard
 * Wellnest Mental Health Web Application
 */

$pageTitle = 'Admin Dashboard';

require_once __DIR__ . '/../../config/settings.php';
require_once __DIR__ . '/../../src/functions.php';

// Require login and admin role
startSession();
requireRole(ROLE_ADMIN);

$user = getCurrentUser();

// Fetch admin stats
try {
    // Get user counts by role
    $userCounts = Database::fetchAll(
        "SELECT r.role_name, COUNT(u.user_id) as count 
         FROM roles r 
         LEFT JOIN users u ON r.role_id = u.role_id AND u.is_active = TRUE AND u.deleted_at IS NULL
         GROUP BY r.role_id, r.role_name"
    );
    
    $counts = ['student' => 0, 'counselor' => 0, 'admin' => 0];
    foreach ($userCounts as $uc) {
        $counts[$uc['role_name']] = $uc['count'];
    }
    
    // Get total assessments taken
    $totalAssessments = Database::fetchOne(
        "SELECT COUNT(*) as count FROM assessment_scores"
    )['count'] ?? 0;
    
    // Get recent audit logs
    $recentLogs = Database::fetchAll(
        "SELECT al.*, u.first_name, u.last_name 
         FROM audit_logs al
         LEFT JOIN users u ON al.user_id = u.user_id
         ORDER BY al.created_at DESC
         LIMIT 10"
    );
    
} catch (Exception $e) {
    $counts = ['student' => 0, 'counselor' => 0, 'admin' => 0];
    $totalAssessments = 0;
    $recentLogs = [];
}

// Include header
include __DIR__ . '/../layouts/header.php';
?>

<!-- Welcome Section -->
<div class="mb-8">
    <h1 class="text-3xl font-bold text-bronze">Admin Dashboard</h1>
    <p class="text-body-gray mt-2">System overview and management.</p>
</div>

<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <!-- Students -->
    <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-electric-blue">
        <p class="text-body-gray text-sm font-medium">Students</p>
        <p class="text-3xl font-bold text-bronze mt-1"><?= $counts['student'] ?></p>
    </div>
    
    <!-- Counselors -->
    <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-green-500">
        <p class="text-body-gray text-sm font-medium">Counselors</p>
        <p class="text-3xl font-bold text-bronze mt-1"><?= $counts['counselor'] ?></p>
    </div>
    
    <!-- Admins -->
    <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-bronze">
        <p class="text-body-gray text-sm font-medium">Administrators</p>
        <p class="text-3xl font-bold text-bronze mt-1"><?= $counts['admin'] ?></p>
    </div>
    
    <!-- Assessments -->
    <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-golden">
        <p class="text-body-gray text-sm font-medium">Total Assessments</p>
        <p class="text-3xl font-bold text-bronze mt-1"><?= $totalAssessments ?></p>
    </div>
</div>

<!-- Recent Activity -->
<div id="recent-activity" class="bg-white rounded-xl shadow-md p-6">
    <h2 class="text-xl font-semibold text-bronze mb-4">Recent Activity</h2>
    
    <?php if (empty($recentLogs)): ?>
        <p class="text-body-gray text-center py-8">No recent activity.</p>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b">
                        <th class="text-left py-2 text-body-gray">User</th>
                        <th class="text-left py-2 text-body-gray">Action</th>
                        <th class="text-left py-2 text-body-gray">Entity</th>
                        <th class="text-left py-2 text-body-gray">Description</th>
                        <th class="text-left py-2 text-body-gray">Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentLogs as $log): ?>
                        <tr class="border-b hover:bg-golden-50">
                            <td class="py-3"><?= e(($log['first_name'] ?? 'System') . ' ' . ($log['last_name'] ?? '')) ?></td>
                            <td class="py-3">
                                <span class="px-2 py-1 text-xs rounded-full bg-bronze-100 text-bronze">
                                    <?= e($log['action_type']) ?>
                                </span>
                            </td>
                            <td class="py-3 text-body-gray"><?= e($log['entity_type'] ?? '-') ?></td>
                            <td class="py-3 text-body-gray"><?= e(substr($log['description'] ?? '', 0, 40)) ?></td>
                            <td class="py-3 text-body-gray"><?= timeAgo($log['created_at']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
