<?php
/**
 * Student Assessment (Questionnaire)
 * Wellnest Mental Health Web Application
 * 
 * Features:
 * - Load available assessments from database
 * - Display questions one-by-one or paginated
 * - Collect responses (likert, multiple choice, open text)
 * - Save responses to database
 * - Calculate immediate scoring
 * - Progress indicator
 */

$pageTitle = 'Mental Health Assessment';

require_once __DIR__ . '/../../config/settings.php';
require_once __DIR__ . '/../../src/functions.php';

startSession();
requireRole(ROLE_STUDENT);

$user = getCurrentUser();
$assessment_id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$currentQuestion = isset($_GET['q']) ? (int)$_GET['q'] : 1;

$assessment = null;
$questions = [];
$currentQ = null;
$totalQuestions = 0;
$progressPercent = 0;

try {
    // If no assessment selected, show list
    if (!$assessment_id) {
        $assessments = Database::fetchAll(
            "SELECT * FROM assessments WHERE is_active = TRUE ORDER BY assessment_name"
        );
        include __DIR__ . '/../layouts/header.php';
        ?>
        <style>
            .assessment-card { transition: all 0.3s; cursor: pointer; }
            .assessment-card:hover { transform: translateY(-5px); box-shadow: 0 15px 35px rgba(0,0,0,0.15); }
            .assessment-icon { font-size: 48px; margin-bottom: 15px; }
            .start-btn { display: inline-block; margin-top: 15px; padding: 10px 20px; background: #B8860B; color: white; text-decoration: none; border-radius: 8px; font-weight: bold; }
            .start-btn:hover { background: #8B6F47; }
        </style>

        <div class="mb-8">
            <h1 class="text-4xl font-bold text-bronze">📋 Available Assessments</h1>
            <p class="text-body-gray text-lg mt-2">Choose an assessment to evaluate your mental health and well-being</p>
        </div>

        <!-- Privacy & Confidentiality Notice -->
        <div class="bg-golden-50 border-2 border-golden rounded-xl shadow-lg p-6 mb-8">
            <div class="flex items-start gap-4">
                <div class="text-3xl flex-shrink-0">🔒</div>
                <div>
                    <h3 class="text-lg font-bold mb-2 text-bronze">Your Privacy Matters</h3>
                    <p class="leading-relaxed text-sm text-bronze">
                        Your thoughts and feelings are important to us. Please know that anything you share here is completely private and will only be accessible to the Office of the School Counselor. Your honesty is safe, and we're here to support you.
                    </p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($assessments as $a): ?>
                <div class="bg-white rounded-xl shadow p-8 assessment-card">
                    <div class="assessment-icon">
                        <?php 
                            $icons = [
                                'stress' => '😰',
                                'anxiety' => '😟',
                                'depression' => '😢',
                                'school_experience' => '🏫',
                                'mental_health' => '🧠',
                                'help_seeking' => '🤝',
                                'general' => '❓'
                            ];
                            echo $icons[$a['assessment_type']] ?? '📝';
                        ?>
                    </div>
                    <h3 class="text-xl font-bold text-bronze mb-2"><?= e($a['assessment_name']) ?></h3>
                    <p class="text-gray-600 mb-3 text-sm"><?= e($a['description']) ?></p>
                    <div class="text-xs text-gray-500 mb-4">
                        <p>⏱️ <strong><?= $a['estimated_time'] ?></strong> minutes</p>
                        <p>❓ <strong><?= $a['total_questions'] ?></strong> questions</p>
                    </div>
                    <a href="?id=<?= $a['assessment_id'] ?>" class="start-btn">Start Assessment →</a>
                </div>
            <?php endforeach; ?>
        </div>

        <?php
        include __DIR__ . '/../layouts/modal.php';
        include __DIR__ . '/../layouts/footer.php';
        exit;
    }

    // Load selected assessment
    $assessment = Database::fetchOne(
        "SELECT * FROM assessments WHERE assessment_id = ? AND is_active = TRUE",
        [$assessment_id]
    );

    if (!$assessment) {
        throw new Exception("Assessment not found.");
    }

    // Load all questions for this assessment
    $questions = Database::fetchAll(
        "SELECT q.*, COUNT(ao.option_id) as option_count 
         FROM questions q
         LEFT JOIN answer_options ao ON q.question_id = ao.question_id
         WHERE q.assessment_id = ?
         GROUP BY q.question_id
         ORDER BY q.question_order ASC",
        [$assessment_id]
    );

    $totalQuestions = count($questions);
    
    if ($totalQuestions === 0) {
        throw new Exception("No questions found for this assessment.");
    }

    // Ensure currentQuestion is valid
    if ($currentQuestion < 1 || $currentQuestion > $totalQuestions) {
        $currentQuestion = 1;
    }

    $currentQ = $questions[$currentQuestion - 1];
    $progressPercent = ($currentQuestion / $totalQuestions) * 100;

    // Load answer options for current question
    $currentQ['options'] = Database::fetchAll(
        "SELECT * FROM answer_options WHERE question_id = ? ORDER BY option_order ASC",
        [$currentQ['question_id']]
    );

    include __DIR__ . '/../layouts/header.php';
    ?>

    <style>
        .progress-bar { background: linear-gradient(90deg, #B8860B 0%, #DAA520 100%); height: 6px; border-radius: 3px; }
        .question-container { background: white; border-radius: 12px; padding: 40px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
        .question-text { font-size: 20px; font-weight: 600; color: #333; margin-bottom: 30px; line-height: 1.6; }
        .likert-buttons { display: grid; grid-template-columns: repeat(auto-fit, minmax(80px, 1fr)); gap: 10px; margin: 25px 0; }
        .likert-btn { padding: 15px; border: 2px solid #ddd; border-radius: 8px; background: white; cursor: pointer; transition: all 0.3s; font-size: 14px; font-weight: 500; }
        .likert-btn:hover { border-color: #B8860B; background: #fffaf0; }
        .likert-btn.selected { background: #B8860B; color: white; border-color: #8B6F47; }
        .option-group { margin-bottom: 18px; }
        .radio-option { display: flex; align-items: center; padding: 15px; border: 2px solid #ddd; border-radius: 8px; cursor: pointer; transition: all 0.3s; }
        .radio-option:hover { border-color: #B8860B; background: #fffaf0; }
        .radio-option input[type="radio"]:checked + label { color: #B8860B; font-weight: 600; }
        .radio-option.selected { background: #B8860B; color: white; border-color: #8B6F47; }
        .textarea-input { width: 100%; padding: 15px; border: 2px solid #ddd; border-radius: 8px; font-size: 16px; font-family: inherit; resize: vertical; min-height: 120px; }
        .textarea-input:focus { outline: none; border-color: #B8860B; box-shadow: 0 0 0 3px rgba(184, 134, 11, 0.1); }
        .nav-buttons { display: flex; gap: 15px; justify-content: space-between; margin-top: 40px; }
        .btn-nav { padding: 12px 30px; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; font-size: 16px; transition: all 0.3s; }
        .btn-prev { background: #ddd; color: #333; }
        .btn-prev:hover:not(:disabled) { background: #bbb; }
        .btn-next { background: #B8860B; color: white; margin-left: auto; }
        .btn-next:hover:not(:disabled) { background: #8B6F47; }
        .btn-submit { background: #28a745; color: white; }
        .btn-submit:hover { background: #218838; }
        .btn-nav:disabled { opacity: 0.5; cursor: not-allowed; }
        .question-info { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        .question-number { background: #B8860B; color: white; padding: 8px 16px; border-radius: 20px; font-weight: bold; font-size: 14px; }
        .required-tag { color: #dc3545; font-weight: bold; margin-left: 5px; }
        .icon-row { display: flex; justify-content: space-between; font-size: 12px; color: #666; margin-top: 10px; font-weight: 500; }
    </style>

    <div class="mb-8 max-w-3xl">
        <div class="mb-4">
            <h1 class="text-3xl font-bold text-bronze mb-2"><?= e($assessment['assessment_name']) ?></h1>
            <p class="text-gray-600"><?= e($assessment['description']) ?></p>
        </div>

        <!-- Progress Bar -->
        <div class="mb-6">
            <div class="progress-bar" style="width: <?= $progressPercent ?>%"></div>
            <p class="text-sm text-gray-600 mt-2">Question <?= $currentQuestion ?> of <?= $totalQuestions ?></p>
        </div>

        <!-- Privacy & Confidentiality Notice -->
        <div class="bg-golden-50 border-2 border-golden rounded-xl shadow-lg p-6 mb-8">
            <div class="flex items-start gap-4">
                <div class="text-3xl flex-shrink-0">🔒</div>
                <div>
                    <h3 class="text-lg font-bold mb-2 text-bronze">Your Privacy Matters</h3>
                    <p class="leading-relaxed text-sm text-bronze">
                        Your thoughts and feelings are important to us. Please know that anything you share here is completely private and will only be accessible to the Office of the School Counselor. Your honesty is safe, and we're here to support you.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Question Container -->
    <div class="question-container max-w-3xl">
        <form id="assessmentForm" method="POST" action="">
            <input type="hidden" name="action" value="save_assessment_response">
            <input type="hidden" name="assessment_id" value="<?= $assessment_id ?>">
            <input type="hidden" name="question_id" value="<?= $currentQ['question_id'] ?>">

            <div class="question-info">
                <div>
                    <h2 class="text-2xl font-bold text-bronze">
                        <?= e($currentQ['question_text']) ?>
                        <?php if ($currentQ['is_required']): ?>
                            <span class="required-tag">*</span>
                        <?php endif; ?>
                    </h2>
                </div>
                <span class="question-number"><?= $currentQuestion ?>/<?= $totalQuestions ?></span>
            </div>

            <!-- Question Type: Likert Scale -->
            <?php if ($currentQ['question_type'] === 'likert'): ?>
                <div class="likert-buttons" id="likertScale">
                    <?php foreach ($currentQ['options'] as $option): ?>
                        <button 
                            type="button"
                            class="likert-btn" 
                            data-value="<?= $option['option_id'] ?>"
                            data-score="<?= $option['option_value'] ?>"
                            onclick="selectLikert(this)">
                            <div style="font-weight: 700; font-size: 18px; margin-bottom: 5px;">
                                <?php 
                                    $emoticons = [
                                        'very_negative' => '😔',
                                        'negative' => '😟',
                                        'slightly_negative' => '😕',
                                        'neutral' => '😐',
                                        'slightly_positive' => '🙂',
                                        'positive' => '😊',
                                        'very_positive' => '😄'
                                    ];
                                    echo $emoticons[$option['emotional_indicator']] ?? '●';
                                ?>
                            </div>
                            <div><?= e($option['option_text']) ?></div>
                        </button>
                    <?php endforeach; ?>
                </div>
                <input type="hidden" id="selectedValue" name="response_value">

            <!-- Question Type: Multiple Choice -->
            <?php elseif ($currentQ['question_type'] === 'multiple_choice'): ?>
                <div id="mcOptions">
                    <?php foreach ($currentQ['options'] as $option): ?>
                        <div class="option-group">
                            <label class="radio-option" onclick="selectOption(this)">
                                <input type="radio" name="response_value" value="<?= $option['option_id'] ?>" style="margin-right: 15px;">
                                <span>
                                    <?php 
                                        $optionEmojis = [
                                            'yes' => '✅',
                                            'no' => '❌',
                                            'agree' => '👍',
                                            'disagree' => '👎',
                                            'strongly agree' => '💯',
                                            'strongly disagree' => '🚫',
                                            'somewhat agree' => '👍',
                                            'somewhat disagree' => '👎',
                                        ];
                                        $optionLower = strtolower(trim($option['option_text']));
                                        $emoji = '•';
                                        foreach ($optionEmojis as $key => $em) {
                                            if (strpos($optionLower, $key) !== false) {
                                                $emoji = $em;
                                                break;
                                            }
                                        }
                                        echo $emoji . ' ';
                                    ?>
                                    <?= e($option['option_text']) ?>
                                </span>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>

            <!-- Question Type: Open Text -->
            <?php elseif ($currentQ['question_type'] === 'open_text'): ?>
                <textarea 
                    class="textarea-input" 
                    id="openText"
                    name="response_text" 
                    placeholder="Share your thoughts here..."
                    maxlength="1000"></textarea>
                <p class="text-xs text-gray-500 mt-2">Maximum 1000 characters</p>

            <!-- Question Type: Yes/No -->
            <?php elseif ($currentQ['question_type'] === 'yes_no'): ?>
                <div class="likert-buttons" style="grid-template-columns: 1fr 1fr;">
                    <?php foreach ($currentQ['options'] as $option): ?>
                        <button 
                            type="button"
                            class="likert-btn" 
                            data-value="<?= $option['option_id'] ?>"
                            onclick="selectLikert(this)">
                            <div style="font-size: 24px; margin-bottom: 8px;">
                                <?= $option['option_text'] === 'Yes' ? '✅' : '❌' ?>
                            </div>
                            <div><?= e($option['option_text']) ?></div>
                        </button>
                    <?php endforeach; ?>
                </div>
                <input type="hidden" id="selectedValue" name="response_value">

            <!-- Question Type: Scale -->
            <?php elseif ($currentQ['question_type'] === 'scale'): ?>
                <div class="likert-buttons">
                    <?php foreach ($currentQ['options'] as $option): ?>
                        <button 
                            type="button"
                            class="likert-btn" 
                            data-value="<?= $option['option_id'] ?>"
                            onclick="selectLikert(this)"
                            style="font-size: 20px; font-weight: bold; display: flex; flex-direction: column; align-items: center; gap: 5px;">
                            <div>
                                <?php 
                                    $scaleEmojis = [
                                        '1' => '😔',
                                        '2' => '😟',
                                        '3' => '😐',
                                        '4' => '🙂',
                                        '5' => '😊',
                                        '6' => '😄',
                                        '7' => '😄',
                                        '8' => '😄',
                                        '9' => '😄',
                                        '10' => '🎉',
                                        'very low' => '😔',
                                        'low' => '😟',
                                        'medium' => '😐',
                                        'high' => '😊',
                                        'very high' => '😄',
                                        'never' => '❌',
                                        'rarely' => '😟',
                                        'sometimes' => '😐',
                                        'often' => '😊',
                                        'always' => '✅',
                                    ];
                                    $optionLower = strtolower(trim($option['option_text']));
                                    $emoji = '•';
                                    foreach ($scaleEmojis as $key => $em) {
                                        if ($optionLower === $key || strpos($optionLower, $key) !== false) {
                                            $emoji = $em;
                                            break;
                                        }
                                    }
                                    echo $emoji;
                                ?>
                            </div>
                            <div><?= e($option['option_text']) ?></div>
                        </button>
                    <?php endforeach; ?>
                </div>
                <input type="hidden" id="selectedValue" name="response_value">
            <?php endif; ?>

            <!-- Navigation Buttons -->
            <div class="nav-buttons">
                <?php if ($currentQuestion > 1): ?>
                    <button type="button" class="btn-nav btn-prev" onclick="goToPrevious()">← Previous</button>
                <?php else: ?>
                    <button type="button" class="btn-nav btn-prev" disabled>← Previous</button>
                <?php endif; ?>

                <?php if ($currentQuestion < $totalQuestions): ?>
                    <button type="button" class="btn-nav btn-next" onclick="goToNext()">Next →</button>
                <?php else: ?>
                    <button type="submit" class="btn-nav btn-submit">Submit Assessment ✓</button>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <script>
        let selectedValue = null;

        function selectLikert(btn) {
            document.querySelectorAll('.likert-btn').forEach(b => b.classList.remove('selected'));
            btn.classList.add('selected');
            selectedValue = btn.dataset.value;
            document.getElementById('selectedValue').value = selectedValue;
        }

        function selectOption(label) {
            document.querySelectorAll('.radio-option').forEach(opt => opt.classList.remove('selected'));
            label.classList.add('selected');
        }

        function goToNext() {
            // Validate response is selected (unless optional)
            const questionType = '<?= $currentQ['question_type'] ?>';
            const isRequired = <?= $currentQ['is_required'] ? 'true' : 'false' ?>;

            if (questionType === 'open_text') {
                // Open text can be empty, proceed
                saveCurrentResponse(() => {
                    window.location.href = '?id=<?= $assessment_id ?>&q=<?= $currentQuestion + 1 ?>';
                });
            } else if (questionType === 'likert' || questionType === 'yes_no' || questionType === 'scale') {
                if (!selectedValue && isRequired) {
                    alert('Please select an answer');
                    return;
                }
                saveCurrentResponse(() => {
                    window.location.href = '?id=<?= $assessment_id ?>&q=<?= $currentQuestion + 1 ?>';
                });
            } else if (questionType === 'multiple_choice') {
                const checked = document.querySelector('input[name="response_value"]:checked');
                if (!checked && isRequired) {
                    alert('Please select an answer');
                    return;
                }
                saveCurrentResponse(() => {
                    window.location.href = '?id=<?= $assessment_id ?>&q=<?= $currentQuestion + 1 ?>';
                });
            }
        }

        function saveCurrentResponse(callback) {
            const formData = new FormData(document.getElementById('assessmentForm'));
            
            fetch('/Wellnest_Sim_Web_Application/src/api.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (callback) callback();
                } else {
                    alert('Error saving response: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error saving response: ' + error.message);
            });
        }

        function goToPrevious() {
            window.location.href = '?id=<?= $assessment_id ?>&q=<?= $currentQuestion - 1 ?>';
        }

        document.getElementById('assessmentForm').addEventListener('submit', async (e) => {
            e.preventDefault();

            // First save the final question's response
            const formData = new FormData(e.target);

            try {
                // Save the last response
                const saveResponse = await fetch('/Wellnest_Sim_Web_Application/src/api.php', {
                    method: 'POST',
                    body: formData
                });

                const saveData = await saveResponse.json();

                if (!saveData.success) {
                    showError('Error saving final response: ' + (saveData.message || 'Unknown error'));
                    return;
                }

                // Now complete the assessment to calculate final score
                const assessment_id = '<?= $assessment_id ?>';
                const completeData = new FormData();
                completeData.append('action', 'complete_assessment');
                completeData.append('assessment_id', assessment_id);

                const completeResponse = await fetch('/Wellnest_Sim_Web_Application/src/api.php', {
                    method: 'POST',
                    body: completeData
                });

                const completeResult = await completeResponse.json();

                if (completeResult.success) {
                    showSuccess('Assessment submitted! Processing results...', () => {
                        window.location.href = '/Wellnest_Sim_Web_Application/views/student/assessment-results.php?score_id=' + completeResult.data.score_id;
                    });
                } else {
                    showError('Error: ' + (completeResult.message || 'Failed to complete assessment'));
                }
            } catch (error) {
                showError('Submission error: ' + error.message);
            }
        });
    </script>

    <?php
    
} catch (Exception $e) {
    logError('Assessment Error', ['error' => $e->getMessage()]);
    include __DIR__ . '/../layouts/header.php';
    ?>
    <div class="bg-red-50 border border-red-200 rounded-lg p-6 max-w-2xl">
        <h2 class="text-2xl font-bold text-red-700 mb-2">Error Loading Assessment</h2>
        <p class="text-red-600 mb-4"><?= e($e->getMessage()) ?></p>
        <a href="/Wellnest_Sim_Web_Application/views/student/dashboard.php" class="text-blue-600 hover:underline">← Back to Dashboard</a>
    </div>
    <?php
}

include __DIR__ . '/../layouts/modal.php';
include __DIR__ . '/../layouts/footer.php';
?>
