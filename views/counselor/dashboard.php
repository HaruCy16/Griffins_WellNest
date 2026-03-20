<?php
/**
 * Counselor Dashboard
 * Wellnest Mental Health Web Application
 */

$pageTitle = 'Counselor Dashboard';

require_once __DIR__ . '/../../config/settings.php';
require_once __DIR__ . '/../../src/functions.php';

// Require login and counselor role
startSession();
requireRole(ROLE_COUNSELOR);

$user = getCurrentUser();

// Fetch counselor stats
try {
    // Get total students
    $totalStudents = Database::fetchOne(
        "SELECT COUNT(*) as count FROM users WHERE role_id = ? AND is_active = TRUE AND deleted_at IS NULL",
        [ROLE_STUDENT]
    )['count'] ?? 0;
    
    // Get high risk students
    $highRiskStudents = Database::fetchAll(
        "SELECT DISTINCT u.user_id, u.first_name, u.last_name, u.email, s.section_name,
                ascore.risk_level, ascore.completed_at
         FROM users u
         JOIN assessment_scores ascore ON u.user_id = ascore.user_id
         LEFT JOIN sections s ON u.section_id = s.section_id
         WHERE ascore.risk_level IN ('high', 'critical')
         AND ascore.completed_at = (
             SELECT MAX(completed_at) FROM assessment_scores WHERE user_id = u.user_id
         )
         ORDER BY FIELD(ascore.risk_level, 'critical', 'high'), ascore.completed_at DESC
         LIMIT 5"
    );
    
    // Get recent assessments
    $recentAssessments = Database::fetchAll(
        "SELECT ascore.*, u.first_name, u.last_name, a.assessment_name
         FROM assessment_scores ascore
         JOIN users u ON ascore.user_id = u.user_id
         JOIN assessments a ON ascore.assessment_id = a.assessment_id
         ORDER BY ascore.completed_at DESC
         LIMIT 10"
    );
    
    // Get pending reviews count
    $pendingReviews = Database::fetchOne(
        "SELECT COUNT(*) as count FROM assessment_scores WHERE reviewed_by IS NULL"
    )['count'] ?? 0;
    
} catch (Exception $e) {
    $totalStudents = 0;
    $highRiskStudents = [];
    $recentAssessments = [];
    $pendingReviews = 0;
}

// Include header
include __DIR__ . '/../layouts/header.php';
?>

<!-- Welcome Section -->
<div class="mb-8">
    <h1 class="text-3xl font-bold text-bronze">Counselor Dashboard</h1>
    <p class="text-body-gray mt-2">Welcome back, <?= e($user['first_name']) ?>. Here's your student wellness overview.</p>
</div>

<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <!-- Total Students -->
    <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-electric-blue">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-body-gray text-sm font-medium">Total Students</p>
                <p class="text-3xl font-bold text-bronze mt-1"><?= $totalStudents ?></p>
            </div>
            <div class="bg-electric-blue-100 p-3 rounded-full">
                <svg class="w-8 h-8 text-electric-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                </svg>
            </div>
        </div>
    </div>
    
    <!-- High Risk -->
    <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-red-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm font-medium">High Risk Students</p>
                <p class="text-3xl font-bold text-red-600 mt-1"><?= count($highRiskStudents) ?></p>
            </div>
            <div class="bg-red-100 p-3 rounded-full">
                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            </div>
        </div>
    </div>
    
    <!-- Pending Reviews -->
    <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-golden">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-body-gray text-sm font-medium">Pending Reviews</p>
                <p class="text-3xl font-bold text-bronze mt-1"><?= $pendingReviews ?></p>
            </div>
            <div class="bg-golden-100 p-3 rounded-full">
                <svg class="w-8 h-8 text-golden-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
            </div>
        </div>
    </div>
    
    <!-- Today's Date -->
    <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-bronze">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-body-gray text-sm font-medium">Today</p>
                <p class="text-xl font-bold text-bronze mt-1"><?= date('M d, Y') ?></p>
            </div>
            <div class="bg-bronze-100 p-3 rounded-full">
                <svg class="w-8 h-8 text-bronze" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <a href="#attention" class="bg-bronze text-white rounded-xl shadow-md p-6 hover:shadow-lg transition">
        <div class="text-3xl mb-2">🚨</div>
        <h3 class="text-xl font-bold mb-1">High Risk Students</h3>
        <p class="text-bronze-100 text-sm">Review students needing immediate attention</p>
    </a>
    
    <a href="#recent-assessments" class="bg-electric-blue text-bronze rounded-xl shadow-md p-6 hover:shadow-lg transition">
        <div class="text-3xl mb-2">📊</div>
        <h3 class="text-xl font-bold mb-1">View Assessments</h3>
        <p class="text-electric-blue-100 text-sm">Check recent assessment results</p>
    </a>
    
    <a href="/Wellnest_Sim_Web_Application/views/counselor/students.php" class="bg-golden text-bronze rounded-xl shadow-md p-6 hover:shadow-lg transition">
        <div class="text-3xl mb-2">👥</div>
        <h3 class="text-xl font-bold mb-1">Manage Students</h3>
        <p class="text-golden-800 text-sm">Access all student profiles and data</p>
    </a>
</div>

<!-- Two Column Layout -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    
    <!-- Students Needing Attention -->
    <div id="attention" class="bg-white rounded-xl shadow-md p-6">
        <h2 class="text-xl font-semibold text-bronze mb-4 flex items-center">
            <svg class="w-5 h-5 text-red-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
            Students Needing Attention
        </h2>
        
        <?php if (empty($highRiskStudents)): ?>
            <div class="text-center py-8">
                <svg class="w-16 h-16 mx-auto text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="text-body-gray mt-4">No high-risk students at this time.</p>
            </div>
        <?php else: ?>
            <div class="space-y-3">
                <?php foreach ($highRiskStudents as $student): ?>
                    <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition">
                        <div class="flex justify-between items-center">
                            <div>
                                <h3 class="font-medium text-bronze">
                                    <?= e($student['first_name'] . ' ' . $student['last_name']) ?>
                                </h3>
                                <p class="text-sm text-body-gray"><?= e($student['section_name'] ?? 'No Section') ?></p>
                            </div>
                            <span class="px-3 py-1 text-xs rounded-full font-medium <?= $student['risk_level'] === 'critical' ? 'bg-red-100 text-red-700' : 'bg-orange-100 text-orange-700' ?>">
                                <?= ucfirst($student['risk_level']) ?> Risk
                            </span>
                        </div>
                        <p class="text-xs text-gray-400 mt-2">
                            Last assessment: <?= formatDateTime($student['completed_at']) ?>
                        </p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Recent Assessments -->
    <div id="recent-assessments" class="bg-white rounded-xl shadow-md p-6">
        <h2 class="text-xl font-semibold text-bronze mb-4">Recent Assessments</h2>
        
        <?php if (empty($recentAssessments)): ?>
            <p class="text-body-gray text-center py-8">No recent assessments.</p>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b">
                            <th class="text-left py-2 text-body-gray">Student</th>
                            <th class="text-left py-2 text-body-gray">Assessment</th>
                            <th class="text-left py-2 text-body-gray">Risk</th>
                            <th class="text-left py-2 text-body-gray">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_slice($recentAssessments, 0, 5) as $assessment): ?>
                            <tr class="border-b hover:bg-golden-50">
                                <td class="py-3"><?= e($assessment['first_name'] . ' ' . substr($assessment['last_name'], 0, 1) . '.') ?></td>
                                <td class="py-3 text-body-gray"><?= e(substr($assessment['assessment_name'], 0, 15)) ?></td>
                                <td class="py-3">
                                    <span class="px-2 py-1 text-xs rounded-full <?= $assessment['risk_level'] === 'low' ? 'bg-green-100 text-green-700' : ($assessment['risk_level'] === 'medium' ? 'bg-yellow-100 text-yellow-700' : ($assessment['risk_level'] === 'high' ? 'bg-orange-100 text-orange-700' : 'bg-red-100 text-red-700')) ?>">
                                        <?= ucfirst($assessment['risk_level']) ?>
                                    </span>
                                </td>
                                <td class="py-3 text-body-gray"><?= formatDate($assessment['completed_at']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
