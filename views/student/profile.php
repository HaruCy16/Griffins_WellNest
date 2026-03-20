<?php
/**
 * Student Profile Page
 * Wellnest Mental Health Web Application
 * 
 * Features:
 * - View personal information
 * - Edit profile details
 * - View assessment history
 * - View wellness statistics
 * - Manage preferences
 */

$pageTitle = 'Student Profile';

require_once __DIR__ . '/../../config/settings.php';
require_once __DIR__ . '/../../src/functions.php';

startSession();
requireRole(ROLE_STUDENT);

$user = getCurrentUser();
$success = $_SESSION['flash_message'] ?? null;
unset($_SESSION['flash_message']);

try {
    // Get assessment history
    $assessments = Database::fetchAll(
        "SELECT as2.*, a.assessment_name 
         FROM assessment_scores as2
         JOIN assessments a ON as2.assessment_id = a.assessment_id
         WHERE as2.user_id = ? 
         ORDER BY as2.completed_at DESC",
        [$user['user_id']]
    );
    
    $totalScore = Database::fetchOne(
        "SELECT SUM(total_score) as total, COUNT(*) as count FROM assessment_scores WHERE user_id = ?",
        [$user['user_id']]
    );
    
} catch (Exception $e) {
    logError('Profile Error', ['error' => $e->getMessage()]);
    $assessments = [];
    $totalScore = ['total' => 0, 'count' => 0];
}

include __DIR__ . '/../layouts/header.php';
?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    
    <!-- Main Profile Content -->
    <div class="lg:col-span-2">
        
        <!-- Personal Information -->
        <div class="bg-white rounded-xl shadow-md p-8 mb-8 card-hover">
            <h2 class="text-2xl font-bold text-bronze mb-6">👤 Personal Information</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">First Name</label>
                    <div class="p-3 bg-gray-50 rounded-lg border"><?= e($user['first_name']) ?></div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Last Name</label>
                    <div class="p-3 bg-gray-50 rounded-lg border"><?= e($user['last_name']) ?></div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                    <div class="p-3 bg-gray-50 rounded-lg border"><?= e($user['email']) ?></div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Account Status</label>
                    <div class="p-3 bg-green-50 rounded-lg border border-green-200 text-green-700 font-semibold">Active</div>
                </div>
            </div>
            
            <button class="mt-6 bg-bronze text-white px-6 py-2 rounded-lg hover:bg-bronze-700 transition font-semibold" id="editBtn">
                ✏️ Edit Profile
            </button>
        </div>
        
        <!-- Assessment History -->
        <div class="bg-white rounded-xl shadow-md p-8 card-hover">
            <h2 class="text-2xl font-bold text-bronze mb-6">📊 Assessment History</h2>
            
            <?php if (empty($assessments)): ?>
                <div class="text-center py-12">
                    <p class="text-gray-500 text-lg">No assessments completed yet.</p>
                    <a href="/Wellnest_Sim_Web_Application/views/student/dashboard.php" class="inline-block mt-4 bg-bronze text-white px-6 py-2 rounded-lg hover:bg-bronze-700">
                        Take an Assessment
                    </a>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left font-semibold text-gray-700">Assessment</th>
                                <th class="px-4 py-2 text-left font-semibold text-gray-700">Score</th>
                                <th class="px-4 py-2 text-left font-semibold text-gray-700">Risk Level</th>
                                <th class="px-4 py-2 text-left font-semibold text-gray-700">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($assessments as $a): ?>
                                <tr class="border-b hover:bg-gray-50 transition">
                                    <td class="px-4 py-3 font-medium text-bronze"><?= e($a['assessment_name']) ?></td>
                                    <td class="px-4 py-3"><?= round($a['percentage_score'], 1) ?>%</td>
                                    <td class="px-4 py-3">
                                        <span class="px-3 py-1 rounded-full text-xs font-semibold" style="background-color: <?= $a['risk_level'] === 'low' ? '#dcfce7' : ($a['risk_level'] === 'medium' ? '#fef3c7' : '#fecaca') ?>; color: <?= $a['risk_level'] === 'low' ? '#15803d' : ($a['risk_level'] === 'medium' ? '#b45309' : '#dc2626') ?>;">
                                            <?= ucfirst($a['risk_level']) ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-gray-600"><?= date('M d, Y', strtotime($a['completed_at'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
        
    </div>
    
    <!-- Sidebar Stats -->
    <div>
        
        <!-- Statistics Card -->
        <div class="bg-white rounded-xl shadow-md p-6 mb-6 card-hover">
            <h3 class="text-xl font-bold text-bronze mb-4">📈 Your Stats</h3>
            
            <div class="space-y-4">
                <div class="bg-gradient-to-br from-bronze to-golden rounded-lg p-4 text-white">
                    <p class="text-xs text-white text-opacity-90 font-semibold mb-1">Assessments Completed</p>
                    <p class="text-4xl font-bold"><?= $totalScore['count'] ?></p>
                </div>
                
                <div class="bg-gradient-to-br from-bronze to-golden rounded-lg p-4 text-white">
                    <p class="text-xs text-white text-opacity-90 font-semibold mb-1">Total Score Points</p>
                    <p class="text-4xl font-bold"><?= intval($totalScore['total'] ?? 0) ?></p>
                </div>
                
                <div class="bg-gradient-to-br from-bronze to-golden rounded-lg p-4 text-white">
                    <p class="text-xs text-white text-opacity-90 font-semibold mb-1">Average Score</p>
                    <p class="text-4xl font-bold"><?= $totalScore['count'] > 0 ? round($totalScore['total'] / $totalScore['count'], 1) : 0 ?>%</p>
                </div>
            </div>
        </div>
        
        <!-- Quick Links -->
        <div class="bg-white rounded-xl shadow-md p-6 card-hover">
            <h3 class="text-lg font-bold text-bronze mb-4">Quick Links</h3>
            
            <div class="space-y-3">
                <a href="/Wellnest_Sim_Web_Application/views/student/dashboard.php" class="block p-4 border-l-4 border-golden rounded-lg hover:shadow-md transition text-bronze font-semibold card-hover" style="background-color: #fafafa;">
                    📊 Dashboard
                </a>
                
                <a href="/Wellnest_Sim_Web_Application/views/student/games/frogger.php" class="block p-4 border-l-4 border-golden rounded-lg hover:shadow-md transition text-bronze font-semibold card-hover" style="background-color: #fafafa;">
                    🎮 Play Games
                </a>
                
                <a href="/Wellnest_Sim_Web_Application/views/student/notifications.php" class="block p-4 border-l-4 border-golden rounded-lg hover:shadow-md transition text-bronze font-semibold card-hover" style="background-color: #fafafa;">
                    🔔 Notifications
                </a>
                
                <a href="/Wellnest_Sim_Web_Application/src/auth.php?action=logout" class="block p-4 border-l-4 border-red-500 rounded-lg hover:shadow-md transition text-red-600 font-semibold card-hover" style="background-color: #fef2f2;">
                    🚪 Logout
                </a>
            </div>
        </div>
        
    </div>
    
</div>

<style>
    .card-hover { transition: all 0.3s; }
    .card-hover:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.15); }
</style>

<script>
document.getElementById('editBtn').addEventListener('click', function() {
    showModal('Profile editing coming soon!', 'Coming Soon', '🔄');
});
</script>

<?php include __DIR__ . '/../layouts/modal.php'; ?>
<?php include __DIR__ . '/../layouts/footer.php'; ?>
