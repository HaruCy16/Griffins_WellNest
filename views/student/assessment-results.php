<?php
/**
 * Assessment Results Page
 * Displays score, risk level, and recommendations
 */

$pageTitle = 'Assessment Results';

require_once __DIR__ . '/../../config/settings.php';
require_once __DIR__ . '/../../src/functions.php';

startSession();
requireRole(ROLE_STUDENT);

$user = getCurrentUser();
$score_id = isset($_GET['score_id']) ? (int)$_GET['score_id'] : null;

$score = null;
$recommendations = [];

try {
    if (!$score_id) {
        throw new Exception("No assessment score provided.");
    }

    // Get score details
    $score = Database::fetchOne(
        "SELECT s.*, a.assessment_name, a.assessment_type
         FROM assessment_scores s
         JOIN assessments a ON s.assessment_id = a.assessment_id
         WHERE s.score_id = ? AND s.user_id = ?",
        [$score_id, $user['user_id']]
    );

    if (!$score) {
        throw new Exception("Assessment score not found.");
    }

    // Get recommendations for this score (if any)
    $recommendations = Database::fetchAll(
        "SELECT * FROM recommendations 
         WHERE score_id = ? 
         ORDER BY priority_level DESC, created_at DESC",
        [$score_id]
    );

    include __DIR__ . '/../layouts/header.php';
    ?>

    <style>
        .result-card { background: white; border-radius: 12px; padding: 30px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); margin-bottom: 25px; }
        .score-display { text-align: center; padding: 40px; background: linear-gradient(135deg, #B8860B 0%, #DAA520 100%); border-radius: 12px; color: white; margin-bottom: 30px; }
        .score-number { font-size: 72px; font-weight: bold; line-height: 1; }
        .score-label { font-size: 20px; margin-top: 15px; opacity: 0.95; }
        .risk-badge { display: inline-block; padding: 12px 24px; border-radius: 50px; font-weight: bold; font-size: 18px; margin-top: 15px; }
        .risk-low { background: #d4edda; color: #155724; }
        .risk-medium { background: #fff3cd; color: #856404; }
        .risk-high { background: #f8d7da; color: #721c24; }
        .risk-critical { background: #d32f2f; color: white; }
        .stat-item { display: flex; justify-content: space-between; padding: 15px 0; border-bottom: 1px solid #eee; }
        .stat-label { color: #666; font-weight: 500; }
        .stat-value { font-weight: bold; color: #B8860B; }
        .recommendation-item { border-left: 4px solid #B8860B; padding: 20px; margin-bottom: 15px; background: #fffaf0; border-radius: 8px; }
        .rec-priority { display: inline-block; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: bold; margin-bottom: 10px; }
        .rec-priority-urgent { background: #dc3545; color: white; }
        .rec-priority-high { background: #fd7e14; color: white; }
        .rec-priority-medium { background: #ffc107; color: #333; }
        .rec-priority-low { background: #28a745; color: white; }
        .btn-primary { display: inline-block; padding: 12px 30px; background: #B8860B; color: white; text-decoration: none; border-radius: 8px; font-weight: bold; margin-top: 15px; border: none; cursor: pointer; }
        .btn-primary:hover { background: #8B6F47; }
        .progress-ring { width: 120px; height: 120px; margin: 0 auto; }
        .chart-container { text-align: center; padding: 30px; background: #f9f9f9; border-radius: 12px; }
    </style>

    <div class="mb-8">
        <h1 class="text-4xl font-bold text-bronze">📊 Assessment Results</h1>
        <p class="text-body-gray text-lg mt-2"><?= e($score['assessment_name']) ?></p>
    </div>

    <!-- Score Display -->
    <div class="result-card">
        <div class="score-display">
            <div class="score-number"><?= round($score['percentage_score'], 1) ?>%</div>
            <div class="score-label">Your Assessment Score</div>
            <span class="risk-badge risk-<?= strtolower($score['risk_level']) ?>">
                <?= ucfirst($score['risk_level']) ?> Risk
            </span>
        </div>

        <!-- Details -->
        <div>
            <div class="stat-item">
                <span class="stat-label">Total Score:</span>
                <span class="stat-value"><?= $score['total_score'] ?> / <?= $score['max_score'] ?></span>
            </div>
            <div class="stat-item">
                <span class="stat-label">Score Date:</span>
                <span class="stat-value"><?= date('M d, Y g:i A', strtotime($score['completed_at'])) ?></span>
            </div>
            <div class="stat-item">
                <span class="stat-label">Mental Health Status:</span>
                <span class="stat-value"><?= e($score['mental_health_status'] ?? 'Assessment Complete') ?></span>
            </div>
            <div class="stat-item">
                <span class="stat-label">Completion:</span>
                <span class="stat-value"><?= $score['completion_percentage'] ?>%</span>
            </div>
        </div>
    </div>

    <!-- Risk Level Interpretation -->
    <div class="result-card">
        <h2 class="text-2xl font-bold text-bronze mb-4">📌 What This Means</h2>
        
        <?php if ($score['risk_level'] === 'low'): ?>
            <p class="text-green-700 text-lg mb-3">✓ <strong>You're doing well!</strong></p>
            <p class="text-gray-700">Your assessment results indicate a low risk level. Continue taking care of yourself and maintaining healthy habits. Remember, it's always okay to reach out for support if you need it.</p>
        
        <?php elseif ($score['risk_level'] === 'medium'): ?>
            <p class="text-yellow-700 text-lg mb-3">⚠ <strong>Some concerns detected</strong></p>
            <p class="text-gray-700">Your assessment shows moderate stress or concerns. Consider engaging in wellness activities or speaking with a counselor for support.</p>
        
        <?php elseif ($score['risk_level'] === 'high'): ?>
            <p class="text-red-700 text-lg mb-3">⚠ <strong>Elevated concerns</strong></p>
            <p class="text-gray-700">Your assessment indicates elevated concerns that deserve attention. We recommend scheduling a meeting with our school counselor who can provide personalized support.</p>
        
        <?php else: ?>
            <p class="text-gray-700">Review your assessment results and consider the recommendations below.</p>
        <?php endif; ?>
    </div>

    <!-- Recommendations -->
    <?php if (!empty($recommendations)): ?>
        <div class="result-card">
            <h2 class="text-2xl font-bold text-bronze mb-4">💡 Personalized Recommendations</h2>
            <p class="text-gray-600 mb-6">Based on your assessment results, here are activities and resources tailored for you:</p>
            
            <?php foreach ($recommendations as $rec): ?>
                <div class="recommendation-item">
                    <div>
                        <span class="rec-priority rec-priority-<?= strtolower($rec['priority_level']) ?>">
                            <?= ucfirst($rec['priority_level']) ?> Priority
                        </span>
                    </div>
                    <h4 class="font-bold text-bronze text-lg mt-2 mb-2"><?= e($rec['title']) ?></h4>
                    <p class="text-gray-700 mb-3"><?= e($rec['description']) ?></p>
                    
                    <div class="text-sm text-gray-600 mb-4">
                        <?php if ($rec['recommendation_type']): ?>
                            <p><strong>Type:</strong> <?= ucfirst(str_replace('_', ' ', $rec['recommendation_type'])) ?></p>
                        <?php endif; ?>
                        <?php if ($rec['duration_minutes']): ?>
                            <p><strong>Duration:</strong> <?= $rec['duration_minutes'] ?> minutes</p>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($rec['action_url']): ?>
                        <a href="<?= e($rec['action_url']) ?>" class="btn-primary">
                            Start Activity →
                        </a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="result-card bg-blue-50 border border-blue-200">
            <p class="text-blue-700">💭 <strong>Recommendations coming soon!</strong> We're generating personalized recommendations based on your assessment. Check back soon.</p>
        </div>
    <?php endif; ?>

    <!-- Next Steps -->
    <div class="result-card">
        <h2 class="text-2xl font-bold text-bronze mb-4">📋 Next Steps</h2>
        <ul class="space-y-3 text-gray-700">
            <li>✓ <strong>Review your results</strong> and understand your mental health status</li>
            <li>✓ <strong>Explore recommendations</strong> that resonate with you</li>
            <li>✓ <strong>Take action</strong> by engaging with suggested activities or games</li>
            <li>✓ <strong>Schedule a counselor chat</strong> if you'd like personalized support</li>
            <li>✓ <strong>Retake assessments</strong> periodically to track your progress</li>
        </ul>
    </div>

    <!-- Action Buttons -->
    <div class="result-card text-center">
        <a href="/Wellnest_Sim_Web_Application/views/student/dashboard.php" class="btn-primary" style="background: #6c757d;">
            ← Back to Dashboard
        </a>
        <a href="/Wellnest_Sim_Web_Application/views/student/assessment.php" class="btn-primary" style="margin-left: 15px;">
            Take Another Assessment
        </a>
    </div>

    <?php

} catch (Exception $e) {
    logError('Assessment Results Error', ['error' => $e->getMessage()]);
    include __DIR__ . '/../layouts/header.php';
    ?>
    <div class="bg-red-50 border border-red-200 rounded-lg p-6 max-w-2xl">
        <h2 class="text-2xl font-bold text-red-700 mb-2">Error Loading Results</h2>
        <p class="text-red-600 mb-4"><?= e($e->getMessage()) ?></p>
        <a href="/Wellnest_Sim_Web_Application/views/student/assessment.php" class="text-blue-600 hover:underline">← Back to Assessments</a>
    </div>
    <?php
}

include __DIR__ . '/../layouts/modal.php';
include __DIR__ . '/../layouts/footer.php';
?>
