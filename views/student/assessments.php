<?php
/**
 * Student Assessments Page
 * Wellnest Mental Health Web Application
 * 
 * Displays all available assessments with:
 * - Assessment details
 * - Quick start buttons
 * - Previous attempt history
 * - Filtering and search
 */

$pageTitle = 'Assessments';

require_once __DIR__ . '/../../config/settings.php';
require_once __DIR__ . '/../../src/functions.php';

startSession();
requireRole(ROLE_STUDENT);

$user = getCurrentUser();

try {
    // Get all active assessments
    $assessments = Database::fetchAll(
        "SELECT * FROM assessments WHERE is_active = TRUE ORDER BY assessment_name"
    );
    
    // Get assessment history for current user
    $assessmentHistory = Database::fetchAll(
        "SELECT as2.*, a.assessment_name, a.assessment_id
         FROM assessment_scores as2
         JOIN assessments a ON as2.assessment_id = a.assessment_id
         WHERE as2.user_id = ?
         ORDER BY as2.completed_at DESC",
        [$user['user_id']]
    );
    
} catch (Exception $e) {
    logError('Assessments Page Error', ['error' => $e->getMessage()]);
    $assessments = [];
    $assessmentHistory = [];
}

include __DIR__ . '/../layouts/header.php';
?>

<style>
    .assessment-card {
        transition: all 0.3s ease;
        border: 2px solid #e0e0e0;
    }
    .assessment-card:hover {
        border-color: #825E2F;
        box-shadow: 0 8px 20px rgba(130, 94, 47, 0.15);
        transform: translateY(-2px);
    }
    .assessment-icon {
        font-size: 2.5rem;
        margin-bottom: 10px;
    }
    .history-row:nth-child(odd) {
        background-color: #f9f9f9;
    }
</style>

<div class="mb-8">
    <h1 class="text-4xl font-bold text-bronze">📋 Mental Health Assessments</h1>
    <p class="text-body-gray text-lg mt-2">Complete assessments to track your mental wellness and get personalized recommendations</p>
</div>

<!-- Privacy & Confidentiality Notice -->
<div class="bg-golden-50 border-2 border-golden rounded-xl shadow-lg p-6 mb-8">
            <div class="flex items-start gap-4">
                <div class="text-3xl flex-shrink-0">🔒</div>
                <div>
                    <h3 class="text-lg font-bold mb-2 text-bronze">Your Privacy Matters</h3>
                    <p class="leading-relaxed text-sm text-bronze">
        </div>
    </div>
</div>

<!-- Stats -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
    <div class="bg-bronze text-white rounded-xl shadow p-6">
        <div class="text-3xl font-bold"><?= count($assessments) ?></div>
        <div class="text-sm mt-2 opacity-90">Available Assessments</div>
    </div>
    <div class="bg-golden text-bronze-800 rounded-xl shadow p-6">
        <div class="text-3xl font-bold"><?= count($assessmentHistory) ?></div>
        <div class="text-sm mt-2 opacity-90">Completed</div>
    </div>
    <div class="bg-green-500 text-bronze rounded-xl shadow p-6">
        <div class="text-3xl font-bold"><?= count(array_unique(array_column($assessmentHistory, 'assessment_id'))) ?></div>
        <div class="text-sm mt-2 opacity-90">Unique Assessments Taken</div>
    </div>
</div>

<!-- Available Assessments -->
<div class="mb-12">
    <h2 class="text-2xl font-bold text-bronze mb-6">Available to Start</h2>
    <?php if (empty($assessments)): ?>
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-8 text-center">
            <p class="text-yellow-800">No assessments are currently available.</p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($assessments as $a): ?>
                <div class="assessment-card bg-white rounded-xl shadow p-6">
                    <div class="text-center">
                        <div class="assessment-icon">📝</div>
                        <h3 class="text-xl font-bold text-bronze mb-2"><?= e($a['assessment_name']) ?></h3>
                        <p class="text-gray-600 text-sm mb-4"><?= e($a['description'] ?? '') ?></p>
                        
                        <div class="space-y-2 mb-6 text-sm text-gray-600">
                            <div>⏱️ <?= $a['estimated_time'] ?> minutes</div>
                            <div>❓ <?= $a['total_questions'] ?> questions</div>
                            <div>📊 <?= ucfirst($a['assessment_type'] ?? 'standard') ?> type</div>
                        </div>
                        
                        <a href="/Wellnest_Sim_Web_Application/views/student/assessment.php?id=<?= $a['assessment_id'] ?>" class="block w-full bg-bronze text-white px-4 py-3 rounded-lg hover:bg-bronze-700 font-semibold transition">
                            Start Assessment →
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Assessment History -->
<?php if (!empty($assessmentHistory)): ?>
<div class="bg-white rounded-xl shadow p-8">
    <h2 class="text-2xl font-bold text-bronze mb-6">📊 Your Assessment History</h2>
    
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="border-b-2 border-bronze">
                    <th class="pb-3 font-bold text-bronze">Assessment</th>
                    <th class="pb-3 font-bold text-bronze">Score</th>
                    <th class="pb-3 font-bold text-bronze">Risk Level</th>
                    <th class="pb-3 font-bold text-bronze">Date</th>
                    <th class="pb-3 font-bold text-bronze">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (array_slice($assessmentHistory, 0, 10) as $record): ?>
                <tr class="history-row border-b">
                    <td class="py-4 font-semibold text-bronze"><?= e($record['assessment_name']) ?></td>
                    <td class="py-4"><span class="bg-bronze text-white px-3 py-1 rounded-full text-sm font-bold"><?= $record['score'] ?></span></td>
                    <td class="py-4">
                        <span class="px-3 py-1 rounded-full text-sm font-semibold <?= 
                            strpos($record['risk_level'], 'low') !== false ? 'bg-green-100 text-green-800' : 
                            (strpos($record['risk_level'], 'med') !== false ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') 
                        ?>">
                            <?= ucfirst($record['risk_level']) ?>
                        </span>
                    </td>
                    <td class="py-4 text-gray-600">
                        <?= date('M d, Y', strtotime($record['completed_at'])) ?>
                    </td>
                    <td class="py-4">
                        <a href="/Wellnest_Sim_Web_Application/views/student/assessment-results.php?id=<?= $record['assessment_score_id'] ?>" class="text-bronze hover:text-bronze-700 font-semibold">
                            View Results →
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <?php if (count($assessmentHistory) > 10): ?>
    <div class="mt-6 text-center">
        <p class="text-gray-600 mb-3">Showing 10 of <?= count($assessmentHistory) ?> assessments</p>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php include __DIR__ . '/../layouts/modal.php'; ?>
<?php include __DIR__ . '/../layouts/footer.php'; ?>
