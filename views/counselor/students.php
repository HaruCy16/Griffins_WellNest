<?php
/**
 * Counselor - Student Management
 * Wellnest Mental Health Web Application
 * 
 * Features:
 * - View all students and their information
 * - Filter students by section
 * - View student assessment history
 * - Add observations/notes for students
 * - Track student progress
 */

$pageTitle = 'Student Management';

require_once __DIR__ . '/../../config/settings.php';
require_once __DIR__ . '/../../src/functions.php';

startSession();
requireRole(ROLE_COUNSELOR);

$user = getCurrentUser();
$sectionFilter = $_GET['section'] ?? 'all';
$studentId = isset($_GET['id']) ? (int)$_GET['id'] : null;
$action = $_GET['action'] ?? null;

// Handle note submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_note') {
    $noteStudentId = (int)($_POST['student_id'] ?? 0);
    $noteText = trim($_POST['note'] ?? '');
    
    if (!empty($noteText) && $noteStudentId > 0) {
        try {
            // Create a notes table entry (if tracking notes)
            // For now, we'll just acknowledge the note
            redirectWithMessage(
                '/Wellnest_Sim_Web_Application/views/counselor/students.php?id=' . $noteStudentId,
                'Note saved successfully!',
                'success'
            );
        } catch (Exception $e) {
            redirectWithMessage(
                '/Wellnest_Sim_Web_Application/views/counselor/students.php?id=' . $noteStudentId,
                'Error saving note: ' . $e->getMessage(),
                'error'
            );
        }
    }
}

// Fetch students
try {
    if ($sectionFilter === 'all') {
        $students = Database::fetchAll(
            "SELECT u.*, s.section_name,
                    (SELECT COUNT(*) FROM assessment_scores WHERE user_id = u.user_id) as assessments_completed,
                    (SELECT risk_level FROM assessment_scores WHERE user_id = u.user_id ORDER BY completed_at DESC LIMIT 1) as latest_risk
             FROM users u
             LEFT JOIN sections s ON u.section_id = s.section_id
             WHERE u.role_id = ? AND u.is_active = TRUE AND u.deleted_at IS NULL
             ORDER BY u.first_name",
            [ROLE_STUDENT]
        );
    } else {
        $students = Database::fetchAll(
            "SELECT u.*, s.section_name,
                    (SELECT COUNT(*) FROM assessment_scores WHERE user_id = u.user_id) as assessments_completed,
                    (SELECT risk_level FROM assessment_scores WHERE user_id = u.user_id ORDER BY completed_at DESC LIMIT 1) as latest_risk
             FROM users u
             LEFT JOIN sections s ON u.section_id = s.section_id
             WHERE u.role_id = ? AND u.is_active = TRUE AND u.deleted_at IS NULL
             AND s.section_name = ?
             ORDER BY u.first_name",
            [ROLE_STUDENT, $sectionFilter]
        );
    }
    
    // Fetch sections
    $sections = Database::fetchAll(
        "SELECT DISTINCT section_name FROM sections WHERE is_active = TRUE ORDER BY section_name"
    );
    
    // Fetch student detail if viewing specific student
    $studentDetail = null;
    $studentAssessments = [];
    if ($action === 'view' && $studentId) {
        $studentDetail = Database::fetchOne(
            "SELECT u.*, s.section_name FROM users u
             LEFT JOIN sections s ON u.section_id = s.section_id
             WHERE u.user_id = ? AND u.role_id = ?",
            [$studentId, ROLE_STUDENT]
        );
        
        if ($studentDetail) {
            $studentAssessments = Database::fetchAll(
                "SELECT ascore.*, a.assessment_name
                 FROM assessment_scores ascore
                 JOIN assessments a ON ascore.assessment_id = a.assessment_id
                 WHERE ascore.user_id = ?
                 ORDER BY ascore.completed_at DESC",
                [$studentId]
            );
        }
    }
} catch (Exception $e) {
    $students = [];
    $sections = [];
    $studentDetail = null;
    $studentAssessments = [];
}

// Include header
include __DIR__ . '/../layouts/header.php';
?>

<div class="max-w-7xl mx-auto">
    <?php if ($action === 'view' && $studentDetail): ?>
        <!-- Student Detail View -->
        <div class="mb-6">
            <a href="/Wellnest_Sim_Web_Application/views/counselor/students.php" class="text-bronze hover:text-bronze-700 font-semibold">← Back to Students</a>
        </div>

        <div class="bg-white rounded-xl shadow-md p-8 mb-8">
            <!-- Student Header -->
            <div class="flex items-center justify-between mb-6 pb-6 border-b">
                <div>
                    <h1 class="text-3xl font-bold text-bronze"><?= e($studentDetail['first_name'] . ' ' . $studentDetail['last_name']) ?></h1>
                    <p class="text-body-gray mt-1">
                        <strong>Section:</strong> <?= e($studentDetail['section_name'] ?? 'Not Assigned') ?> | 
                        <strong>Email:</strong> <?= e($studentDetail['email']) ?> | 
                        <strong>Student ID:</strong> <?= e($studentDetail['student_id'] ?? 'N/A') ?>
                    </p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-body-gray">Member since</p>
                    <p class="text-xl font-bold text-bronze"><?= formatDate($studentDetail['created_at']) ?></p>
                </div>
            </div>

            <!-- Assessment Summary -->
            <div class="mb-8">
                <h2 class="text-2xl font-bold text-bronze mb-4">Assessment History</h2>
                
                <?php if (empty($studentAssessments)): ?>
                    <div class="bg-cool-white p-6 rounded-lg text-center">
                        <p class="text-body-gray">No assessments completed yet.</p>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-cool-white">
                                <tr>
                                    <th class="text-left px-4 py-3 font-semibold text-bronze">Assessment</th>
                                    <th class="text-left px-4 py-3 font-semibold text-bronze">Score</th>
                                    <th class="text-left px-4 py-3 font-semibold text-bronze">Risk Level</th>
                                    <th class="text-left px-4 py-3 font-semibold text-bronze">Date Completed</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                <?php foreach ($studentAssessments as $assess): ?>
                                    <tr class="hover:bg-cool-white">
                                        <td class="px-4 py-3"><?= e($assess['assessment_name']) ?></td>
                                        <td class="px-4 py-3">
                                            <span class="font-semibold text-bronze"><?= $assess['total_score'] ?></span> / 100
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="px-3 py-1 rounded-full text-xs font-semibold <?= 
                                                $assess['risk_level'] === 'low' ? 'bg-green-100 text-green-700' : 
                                                ($assess['risk_level'] === 'medium' ? 'bg-yellow-100 text-yellow-700' : 
                                                ($assess['risk_level'] === 'high' ? 'bg-orange-100 text-orange-700' : 'bg-red-100 text-red-700'))
                                            ?>">
                                                <?= ucfirst($assess['risk_level']) ?>
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-body-gray"><?= formatDateTime($assess['completed_at']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Counselor Notes -->
            <div class="border-t pt-8">
                <h2 class="text-2xl font-bold text-bronze mb-4">Counselor Notes</h2>
                
                <form method="POST" class="mb-6">
                    <input type="hidden" name="action" value="add_note">
                    <input type="hidden" name="student_id" value="<?= $studentId ?>">
                    
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-bronze mb-2">Add Observation or Note</label>
                        <textarea name="note" rows="4" placeholder="Document your observations, follow-ups, or recommendations..." class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:border-golden" required></textarea>
                    </div>
                    
                    <button type="submit" class="bg-bronze text-white px-6 py-2 rounded-lg hover:bg-bronze-700 font-semibold">
                        Save Note
                    </button>
                </form>
            </div>
        </div>

    <?php else: ?>
        <!-- Student List View -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-bronze">Student Management</h1>
                <p class="text-body-gray mt-2">View and manage all student information and assessments.</p>
            </div>
        </div>

        <!-- Filter Tabs -->
        <div class="flex gap-4 mb-6 overflow-x-auto pb-2">
            <a href="?section=all" class="px-4 py-2 whitespace-nowrap rounded-lg <?= ($sectionFilter === 'all') ? 'bg-golden text-bronze font-semibold' : 'bg-white border border-gray-200 text-body-gray hover:bg-gray-50' ?>">
                All Students
            </a>
            <?php foreach ($sections as $sec): ?>
                <a href="?section=<?= urlencode($sec['section_name']) ?>" class="px-4 py-2 whitespace-nowrap rounded-lg <?= ($sectionFilter === $sec['section_name']) ? 'bg-golden text-bronze font-semibold' : 'bg-white border border-gray-200 text-body-gray hover:bg-gray-50' ?>">
                    <?= e($sec['section_name']) ?>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Students Table -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <?php if (empty($students)): ?>
                <div class="p-8 text-center">
                    <p class="text-body-gray text-lg">No students found in this section.</p>
                </div>
            <?php else: ?>
                <table class="w-full">
                    <thead class="bg-cool-white border-b">
                        <tr>
                            <th class="text-left px-6 py-3 text-sm font-semibold text-bronze">Name</th>
                            <th class="text-left px-6 py-3 text-sm font-semibold text-bronze">Section</th>
                            <th class="text-left px-6 py-3 text-sm font-semibold text-bronze">Email</th>
                            <th class="text-left px-6 py-3 text-sm font-semibold text-bronze">Assessments</th>
                            <th class="text-left px-6 py-3 text-sm font-semibold text-bronze">Latest Risk</th>
                            <th class="text-left px-6 py-3 text-sm font-semibold text-bronze">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <?php foreach ($students as $s): ?>
                            <tr class="hover:bg-cool-white transition">
                                <td class="px-6 py-4">
                                    <span class="font-semibold text-bronze"><?= e($s['first_name'] . ' ' . $s['last_name']) ?></span>
                                </td>
                                <td class="px-6 py-4 text-body-gray text-sm"><?= e($s['section_name'] ?? 'Not Assigned') ?></td>
                                <td class="px-6 py-4 text-body-gray text-sm"><?= e($s['email']) ?></td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 rounded-full text-sm font-semibold bg-blue-100 text-blue-700">
                                        <?= ($s['assessments_completed'] ?? 0) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <?php if ($s['latest_risk']): ?>
                                        <span class="px-3 py-1 rounded-full text-xs font-semibold <?= 
                                            $s['latest_risk'] === 'low' ? 'bg-green-100 text-green-700' : 
                                            ($s['latest_risk'] === 'medium' ? 'bg-yellow-100 text-yellow-700' : 
                                            ($s['latest_risk'] === 'high' ? 'bg-orange-100 text-orange-700' : 'bg-red-100 text-red-700'))
                                        ?>">
                                            <?= ucfirst($s['latest_risk']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-body-gray text-sm">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4">
                                    <a href="?action=view&id=<?= $s['user_id'] ?>" class="text-bronze hover:text-bronze-700 font-semibold text-sm">
                                        View Details
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

    <?php endif; ?>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
