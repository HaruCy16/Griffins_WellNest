<?php
/**
 * Student Dashboard
 * Wellnest Mental Health Web Application
 */

$pageTitle = 'Student Dashboard';

require_once __DIR__ . '/../../config/settings.php';
require_once __DIR__ . '/../../src/functions.php';

// Require login and student role
startSession();
requireRole(ROLE_STUDENT);

$user = getCurrentUser();

// Fetch student stats
try {
    // Get completed assessments count
    $assessmentCount = Database::fetchOne(
        "SELECT COUNT(*) as count FROM assessment_scores WHERE user_id = ?",
        [$user['user_id']]
    )['count'] ?? 0;
    
    // Get latest assessment score
    $latestScore = Database::fetchOne(
        "SELECT as2.*, a.assessment_name 
         FROM assessment_scores as2
         JOIN assessments a ON as2.assessment_id = a.assessment_id
         WHERE as2.user_id = ? 
         ORDER BY as2.completed_at DESC 
         LIMIT 1",
        [$user['user_id']]
    );
    
    // Get pending recommendations
    $pendingRecommendations = Database::fetchOne(
        "SELECT COUNT(*) as count FROM recommendations WHERE user_id = ? AND is_acted_upon = FALSE",
        [$user['user_id']]
    )['count'] ?? 0;
    
    // Get available assessments
    $availableAssessments = Database::fetchAll(
        "SELECT * FROM assessments WHERE is_active = TRUE ORDER BY assessment_name"
    );
    
    // Get recent recommendations
    $recentRecommendations = Database::fetchAll(
        "SELECT * FROM recommendations 
         WHERE user_id = ? AND is_acted_upon = FALSE 
         ORDER BY priority_level DESC, created_at DESC 
         LIMIT 3",
        [$user['user_id']]
    );
    
} catch (Exception $e) {
    $assessmentCount = 0;
    $latestScore = null;
    $pendingRecommendations = 0;
    $availableAssessments = [];
    $recentRecommendations = [];
}

// Include header
include __DIR__ . '/../layouts/header.php';
?>

<!-- Welcome Section -->
<div class="mb-8">
    <h1 class="text-3xl font-bold text-bronze">Welcome back, <?= e($user['first_name']) ?>!</h1>
    <p class="text-body-gray mt-2">Here's an overview of your mental wellness journey.</p>
</div>

<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <!-- Assessments Completed -->
    <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-golden">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-body-gray text-sm font-medium">Assessments Completed</p>
                <p class="text-3xl font-bold text-bronze mt-1"><?= $assessmentCount ?></p>
            </div>
            <div class="bg-golden-100 p-3 rounded-full">
                <svg class="w-8 h-8 text-golden-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>
    </div>
    
    <!-- Current Status -->
    <div class="bg-white rounded-xl shadow-md p-6 border-l-4 <?= $latestScore ? ($latestScore['risk_level'] === 'low' ? 'border-green-500' : ($latestScore['risk_level'] === 'medium' ? 'border-yellow-500' : ($latestScore['risk_level'] === 'high' ? 'border-orange-500' : 'border-red-500'))) : 'border-gray-300' ?>">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm font-medium">Current Status</p>
                <p class="text-xl font-bold mt-1 <?= $latestScore ? ($latestScore['risk_level'] === 'low' ? 'text-green-600' : ($latestScore['risk_level'] === 'medium' ? 'text-yellow-600' : ($latestScore['risk_level'] === 'high' ? 'text-orange-600' : 'text-red-600'))) : 'text-gray-400' ?>">
                    <?= $latestScore ? ucfirst($latestScore['risk_level']) . ' Risk' : 'No Assessment Yet' ?>
                </p>
            </div>
            <div class="<?= $latestScore ? ($latestScore['risk_level'] === 'low' ? 'bg-green-100' : ($latestScore['risk_level'] === 'medium' ? 'bg-yellow-100' : ($latestScore['risk_level'] === 'high' ? 'bg-orange-100' : 'bg-red-100'))) : 'bg-gray-100' ?> p-3 rounded-full">
                <svg class="w-8 h-8 <?= $latestScore ? ($latestScore['risk_level'] === 'low' ? 'text-green-600' : ($latestScore['risk_level'] === 'medium' ? 'text-yellow-600' : ($latestScore['risk_level'] === 'high' ? 'text-orange-600' : 'text-red-600'))) : 'text-gray-400' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                </svg>
            </div>
        </div>
    </div>
    
    <!-- Pending Recommendations -->
    <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-electric-blue">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-body-gray text-sm font-medium">Pending Recommendations</p>
                <p class="text-3xl font-bold text-bronze mt-1"><?= $pendingRecommendations ?></p>
            </div>
            <div class="bg-electric-blue-100 p-3 rounded-full">
                <svg class="w-8 h-8 text-electric-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                </svg>
            </div>
        </div>
    </div>
</div>

<!-- Two Column Layout -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    
    <!-- Available Assessments -->
    <div class="bg-white rounded-xl shadow-md p-6">
        <h2 class="text-xl font-semibold text-bronze mb-4">Take an Assessment</h2>
        
        <?php if (empty($availableAssessments)): ?>
            <p class="text-body-gray">No assessments available at the moment.</p>
        <?php else: ?>
            <div class="space-y-3">
                <?php foreach ($availableAssessments as $assessment): ?>
                    <div class="border border-gray-200 rounded-lg p-4 hover:border-golden hover:bg-golden-50 transition cursor-pointer">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="font-medium text-bronze"><?= e($assessment['assessment_name']) ?></h3>
                                <p class="text-sm text-body-gray mt-1"><?= e($assessment['description'] ?? '') ?></p>
                                <div class="flex items-center space-x-4 mt-2 text-xs text-gray-400">
                                    <span><?= $assessment['total_questions'] ?> questions</span>
                                    <span><?= $assessment['estimated_time'] ?> mins</span>
                                </div>
                            </div>
                            <a href="assessments.php?take=<?= $assessment['assessment_id'] ?>" class="bg-golden text-bronze-800 px-4 py-2 rounded-lg text-sm hover:bg-golden-400 transition font-semibold">
                                Start
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Recent Recommendations -->
    <div class="bg-white rounded-xl shadow-md p-6">
        <h2 class="text-xl font-semibold text-bronze mb-4">Your Recommendations</h2>
        
        <?php if (empty($recentRecommendations)): ?>
            <div class="text-center py-8">
                <svg class="w-16 h-16 mx-auto text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                </svg>
                <p class="text-body-gray mt-4">Complete an assessment to get personalized recommendations.</p>
            </div>
        <?php else: ?>
            <div class="space-y-3">
                <?php foreach ($recentRecommendations as $rec): ?>
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex items-start space-x-3">
                            <span class="px-2 py-1 text-xs rounded-full font-medium <?= $rec['priority_level'] === 'urgent' ? 'bg-red-100 text-red-700' : ($rec['priority_level'] === 'high' ? 'bg-orange-100 text-orange-700' : ($rec['priority_level'] === 'medium' ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700')) ?>">
                                <?= ucfirst($rec['priority_level']) ?>
                            </span>
                            <div class="flex-1">
                                <h3 class="font-medium text-bronze"><?= e($rec['title']) ?></h3>
                                <p class="text-sm text-body-gray mt-1"><?= e(substr($rec['description'], 0, 100)) ?>...</p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <a href="recommendations.php" class="block text-center mt-4 text-electric-blue hover:text-electric-blue-700 font-medium">
                View All Recommendations →
            </a>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
