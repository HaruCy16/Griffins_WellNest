<?php
/**
 * WELLNEST API Router
 * Main entry point for all API requests
 * Routes all actions to appropriate handlers
 * 
 * Format: src/api.php?action=<action>&<params>
 * Response: JSON with { success, message, data, timestamp }
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/settings.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/functions.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle CORS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    // Get action from GET parameter or POST parameter
    $action = $_GET['action'] ?? $_POST['action'] ?? '';

    if (empty($action)) {
        respondError('No action specified', 400);
    }

    // Route to appropriate handler
    switch ($action) {
        // ===== AUTH ENDPOINTS =====
        case 'login':
            handleApiLogin();
            break;

        case 'logout':
            handleApiLogout();
            break;

        // ===== ASSESSMENT ENDPOINTS =====
        case 'get_assessments':
            handleGetAssessments();
            break;

        case 'get_assessment_questions':
            handleGetAssessmentQuestions();
            break;

        case 'submit_response':
            handleSubmitResponse();
            break;

        case 'complete_assessment':
            handleCompleteAssessment();
            break;

        // ===== COUNSELOR ENDPOINTS =====
        case 'get_section_students':
            handleGetSectionStudents();
            break;

        case 'get_student_details':
            handleGetStudentDetails();
            break;

        // ===== GAMES ENDPOINTS =====
        case 'get_games':
            handleGetGames();
            break;

        case 'save_game_progress':
            handleSaveGameProgress();
            break;

        case 'save_mood':
            handleSaveMood();
            break;

        case 'save_assessment_response':
            handleSaveAssessmentResponse();
            break;

        // ===== RECOMMENDATIONS ENDPOINT =====
        case 'get_recommendations':
            handleGetRecommendations();
            break;

        // ===== NOTIFICATIONS ENDPOINT =====
        case 'get_notifications':
            handleGetNotifications();
            break;

        // ===== HEALTH CHECK =====
        case 'health':
            respondSuccess(['status' => 'ok', 'timestamp' => date('Y-m-d H:i:s')], 'API is healthy');
            break;

        default:
            respondError('Action not found: ' . $action, 404);
            break;
    }

} catch (Exception $e) {
    logError('API Error', [
        'action' => $_GET['action'] ?? 'unknown',
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
    respondError('Internal server error', 500);
}

// =============================================================================
// AUTH HANDLERS
// =============================================================================

/**
 * API Login Handler
 * POST /api.php?action=login
 * Body: {email, password}
 * Returns: {success, message, data: {user_id, email, first_name, last_name, role_id, role_name}}
 */
function handleApiLogin() {
    try {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            respondError('POST request required', 405);
        }

        // Get form data
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        // Validate input
        if (empty($email) || empty($password)) {
            respondError('Email and password are required', 400, ['email' => 'Email required', 'password' => 'Password required']);
        }

        // Find user by email
        $user = Database::fetchOne(
            "SELECT u.*, r.role_name 
             FROM users u 
             JOIN roles r ON u.role_id = r.role_id 
             WHERE u.email = ? AND u.deleted_at IS NULL",
            [$email]
        );

        // Check if user exists
        if (!$user) {
            respondError('Invalid email or password', 401);
        }

        // Check if account is active
        if (!$user['is_active']) {
            respondError('Your account has been deactivated. Please contact support.', 403);
        }

        // Verify password
        if (!password_verify($password, $user['password_hash'])) {
            respondError('Invalid email or password', 401);
        }

        // Login successful - set session
        startSession();
        session_regenerate_id(true);
        
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['last_name'] = $user['last_name'];
        $_SESSION['role_id'] = $user['role_id'];
        $_SESSION['role_name'] = $user['role_name'];
        $_SESSION['created'] = time();

        // Update last login time
        Database::query(
            "UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE user_id = ?",
            [$user['user_id']]
        );

        // Log the action
        logAudit('LOGIN', 'users', $user['user_id'], ['action' => 'login_via_api']);

        respondSuccess([
            'user_id' => $user['user_id'],
            'email' => $user['email'],
            'first_name' => $user['first_name'],
            'last_name' => $user['last_name'],
            'role_id' => $user['role_id'],
            'role_name' => $user['role_name']
        ], 'Login successful', 200);

    } catch (Exception $e) {
        logError('API Login error', ['error' => $e->getMessage()]);
        respondError($e->getMessage(), 500);
    }
}

/**
 * API Logout Handler
 * POST /api.php?action=logout
 * Returns: {success, message}
 */
function handleApiLogout() {
    try {
        requireAuth();

        $userId = getCurrentUserId();

        // Log the action
        logAudit('LOGOUT', 'users', $userId, ['action' => 'logout_via_api']);

        // Destroy session
        destroySession();

        respondSuccess([], 'Logged out successfully', 200);

    } catch (Exception $e) {
        logError('API Logout error', ['error' => $e->getMessage()]);
        respondError($e->getMessage(), 500);
    }
}

// =============================================================================
// ASSESSMENT HANDLERS
// =============================================================================

/**
 * Get list of all available assessments
 * GET /api.php?action=get_assessments
 */
function handleGetAssessments() {
    try {
        requireAuth();

        $query = "SELECT assessment_id, assessment_name, description, assessment_type, total_questions, estimated_time
                  FROM assessments
                  WHERE is_active = 1
                  ORDER BY assessment_type ASC";

        $assessments = fetchAll($query, []);

        respondSuccess($assessments, 'Assessments retrieved successfully');

    } catch (Exception $e) {
        logError('Get assessments error', ['error' => $e->getMessage()]);
        respondError($e->getMessage(), 500);
    }
}

/**
 * Get questions for an assessment
 * GET /api.php?action=get_assessment_questions&assessment_id=1
 */
function handleGetAssessmentQuestions() {
    try {
        requireAuth();

        $assessmentId = (int)($_GET['assessment_id'] ?? 0);

        if (empty($assessmentId)) {
            respondError('Assessment ID is required', 400);
        }

        // Get assessment details
        $assessmentQuery = "SELECT assessment_id, assessment_name, instructions FROM assessments 
                            WHERE assessment_id = ? AND is_active = 1";
        $assessment = fetchOne($assessmentQuery, [$assessmentId]);

        if (!$assessment) {
            respondError('Assessment not found', 404);
        }

        // Get questions with options
        $questionsQuery = "SELECT q.question_id, q.question_text, q.question_type, q.question_order, q.weight
                           FROM questions q
                           WHERE q.assessment_id = ? 
                           ORDER BY q.question_order ASC";
        $questions = fetchAll($questionsQuery, [$assessmentId]);

        // Get options for each question (only if it has answer options)
        foreach ($questions as &$question) {
            if (in_array($question['question_type'], ['likert', 'yes_no', 'multiple_choice', 'scale'])) {
                $optionsQuery = "SELECT option_id, option_text, option_value, option_order
                                 FROM answer_options
                                 WHERE question_id = ?
                                 ORDER BY option_order ASC";
                $question['options'] = fetchAll($optionsQuery, [$question['question_id']]);
            } else {
                // Open text or voice note - no options to display
                $question['options'] = [];
            }
        }

        respondSuccess([
            'assessment' => $assessment,
            'questions' => $questions
        ], 'Questions retrieved successfully');

    } catch (Exception $e) {
        logError('Get questions error', ['error' => $e->getMessage()]);
        respondError($e->getMessage(), 500);
    }
}

/**
 * Submit a single response
 * POST /api.php?action=submit_response
 * 
 * For option-based questions:
 * {assessment_id, question_id, option_id}
 * 
 * For open-text questions:
 * {assessment_id, question_id, response_text}
 * 
 * For voice note questions:
 * {assessment_id, question_id, voice_note_path}
 */
function handleSubmitResponse() {
    try {
        requireAuth();

        $userId = getCurrentUserId();
        $assessmentId = (int)($_POST['assessment_id'] ?? 0);
        $questionId = (int)($_POST['question_id'] ?? 0);

        // Validate inputs
        if (empty($assessmentId) || empty($questionId)) {
            respondError('Assessment ID and Question ID are required', 400);
        }

        // Check if assessment exists
        if (!recordExists('assessments', ['assessment_id' => $assessmentId])) {
            respondError('Assessment not found', 404);
        }

        // Check if question exists and get its type
        $questionQuery = "SELECT question_id, question_type FROM questions WHERE question_id = ? AND assessment_id = ?";
        $question = fetchOne($questionQuery, [$questionId, $assessmentId]);

        if (!$question) {
            respondError('Question not found', 404);
        }

        // For now, only handle option-based questions
        // (response_text and voice_note columns don't exist in actual database)
        if (!in_array($question['question_type'], ['likert', 'yes_no', 'multiple_choice', 'scale'])) {
            respondError('Question type not yet supported for submission', 400);
        }

        // Get option ID and validate
        $optionId = (int)($_POST['option_id'] ?? 0);
        
        if (empty($optionId)) {
            respondError('Option ID is required', 400);
        }

        // Get option value
        $optionQuery = "SELECT option_id, option_value FROM answer_options 
                        WHERE option_id = ? AND question_id = ?";
        $option = fetchOne($optionQuery, [$optionId, $questionId]);

        if (!$option) {
            respondError('Option not found', 404);
        }

        $scorePoints = $option['option_value'];

        // Insert or update response
        $responseQuery = "INSERT INTO assessment_responses 
                          (assessment_id, user_id, question_id, selected_option_id, score_points, completed_at)
                          VALUES (?, ?, ?, ?, ?, NOW())
                          ON DUPLICATE KEY UPDATE
                          selected_option_id = VALUES(selected_option_id),
                          score_points = VALUES(score_points),
                          completed_at = NOW()";

        executeQuery($responseQuery, [$assessmentId, $userId, $questionId, $optionId, $scorePoints]);

        // Log the action
        logAudit('SUBMIT_RESPONSE', 'assessment_responses', $userId, [
            'assessment_id' => $assessmentId,
            'question_id' => $questionId,
            'option_id' => $optionId,
            'score_points' => $scorePoints
        ]);

        respondSuccess([], 'Response submitted successfully', 201);

    } catch (Exception $e) {
        logError('Submit response error', ['error' => $e->getMessage()]);
        respondError($e->getMessage(), 500);
    }
}

// =============================================================================
// ASSESSMENT COMPLETION HANDLER
// =============================================================================

/**
 * Complete assessment and calculate score
 * POST /api.php?action=complete_assessment
 * {assessment_id}
 */
function handleCompleteAssessment() {
    try {
        requireAuth();

        $userId = getCurrentUserId();
        $assessmentId = (int)($_POST['assessment_id'] ?? 0);

        if (empty($assessmentId)) {
            respondError('Assessment ID is required', 400);
        }

        // Check if assessment exists and get its type
        $assessmentQuery = "SELECT assessment_id, assessment_type FROM assessments WHERE assessment_id = ?";
        $assessment = fetchOne($assessmentQuery, [$assessmentId]);

        if (!$assessment) {
            respondError('Assessment not found', 404);
        }

        // Calculate total score
        $scoreQuery = "SELECT COALESCE(SUM(score_points), 0) as total_score, COUNT(*) as total_responses
                       FROM assessment_responses
                       WHERE user_id = ? AND assessment_id = ?";

        $scoreData = fetchOne($scoreQuery, [$userId, $assessmentId]);
        $totalScore = $scoreData['total_score'] ?? 0;

        // Calculate max score based on assessment type
        $maxScore = calculateMaxScore($assessmentId);

        $percentage = ($maxScore > 0) ? round(($totalScore / $maxScore) * 100, 2) : 0;

        // Determine risk level based on assessment type
        $riskLevel = determineRiskLevel($assessment['assessment_type'], $percentage);

        // Insert assessment score
        $query = "INSERT INTO assessment_scores 
                  (user_id, assessment_id, total_score, max_score, risk_level, completed_at)
                  VALUES (?, ?, ?, ?, ?, NOW())
                  ON DUPLICATE KEY UPDATE
                  total_score = VALUES(total_score),
                  max_score = VALUES(max_score),
                  risk_level = VALUES(risk_level),
                  completed_at = NOW()";

        executeQuery($query, [$userId, $assessmentId, $totalScore, $maxScore, $riskLevel]);

        // Log the action
        logAudit('COMPLETE_ASSESSMENT', 'assessment_scores', $userId, [
            'assessment_id' => $assessmentId,
            'assessment_type' => $assessment['assessment_type'],
            'score' => $totalScore,
            'max_score' => $maxScore,
            'percentage' => $percentage,
            'risk_level' => $riskLevel
        ]);

        respondSuccess([
            'total_score' => (float)$totalScore,
            'max_score' => (float)$maxScore,
            'percentage_score' => (float)$percentage,
            'risk_level' => $riskLevel,
            'status' => getRiskStatus($riskLevel),
            'completion_time' => date('Y-m-d H:i:s')
        ], 'Assessment completed successfully', 201);

    } catch (Exception $e) {
        logError('Complete assessment error', ['error' => $e->getMessage()]);
        respondError($e->getMessage(), 500);
    }
}

// =============================================================================
// COUNSELOR HANDLERS
// =============================================================================

/**
 * Get students in counselor's section
 * GET /api.php?action=get_section_students
 */
function handleGetSectionStudents() {
    try {
        requireAuth([ROLE_COUNSELOR, ROLE_ADMIN]);

        $userId = getCurrentUserId();

        // Get counselor's section (admin can access all)
        if (isCounselor()) {
            $sectionQuery = "SELECT section_id FROM users WHERE user_id = ?";
            $counselor = fetchOne($sectionQuery, [$userId]);

            if (!$counselor || !$counselor['section_id']) {
                respondError('Counselor section not found', 404);
            }
            $sectionId = $counselor['section_id'];
        } else {
            // Admin - get section from query parameter
            $sectionId = (int)($_GET['section_id'] ?? 0);
            if (empty($sectionId)) {
                respondError('Section ID is required for admin', 400);
            }
        }

        // Get students in section with latest assessment scores
        $query = "SELECT u.user_id, u.email, u.first_name, u.last_name, u.student_id,
                         COALESCE(ascore.risk_level, 'not_assessed') as latest_risk_level,
                         COALESCE(ascore.percentage_score, 0) as latest_percentage,
                         COUNT(DISTINCT ascore.assessment_id) as assessments_completed,
                         COALESCE(ascore.completed_at, NULL) as last_assessment_date
                  FROM users u
                  LEFT JOIN assessment_scores ascore ON u.user_id = ascore.user_id
                  WHERE u.section_id = ? AND u.role_id = ?
                  GROUP BY u.user_id
                  ORDER BY u.last_name ASC, u.first_name ASC";

        $students = fetchAll($query, [$sectionId, ROLE_STUDENT]);

        respondSuccess($students, 'Students retrieved successfully');

    } catch (Exception $e) {
        logError('Get section students error', ['error' => $e->getMessage()]);
        respondError($e->getMessage(), 500);
    }
}

/**
 * Get detailed student information
 * GET /api.php?action=get_student_details&student_id=1
 */
function handleGetStudentDetails() {
    try {
        requireAuth([ROLE_COUNSELOR, ROLE_ADMIN]);

        $studentId = (int)($_GET['student_id'] ?? 0);

        if (empty($studentId)) {
            respondError('Student ID is required', 400);
        }

        // Get student info
        $studentQuery = "SELECT user_id, email, first_name, last_name, student_id, created_at FROM users 
                        WHERE user_id = ? AND role_id = ?";
        $student = fetchOne($studentQuery, [$studentId, ROLE_STUDENT]);

        if (!$student) {
            respondError('Student not found', 404);
        }

        // Get assessment history
        $assessmentsQuery = "SELECT ascore.score_id, a.assessment_name, a.assessment_type, ascore.total_score, ascore.max_score,
                                    ascore.percentage_score, ascore.risk_level, ascore.completed_at
                             FROM assessment_scores ascore
                             JOIN assessments a ON ascore.assessment_id = a.assessment_id
                             WHERE ascore.user_id = ?
                             ORDER BY ascore.completed_at DESC
                             LIMIT 10";

        $assessments = fetchAll($assessmentsQuery, [$studentId]);

        // Get game progress
        $gamesQuery = "SELECT g.game_name, gp.score, gp.times_played, gp.high_score, gp.player_mood_before, 
                              gp.player_mood_after, gp.last_played_at
                       FROM game_progress gp
                       JOIN therapeutic_games g ON gp.game_id = g.game_id
                       WHERE gp.user_id = ?
                       ORDER BY gp.last_played_at DESC
                       LIMIT 5";

        $games = fetchAll($gamesQuery, [$studentId]);

        respondSuccess([
            'student' => $student,
            'assessments' => $assessments,
            'games' => $games
        ], 'Student details retrieved successfully');

    } catch (Exception $e) {
        logError('Get student details error', ['error' => $e->getMessage()]);
        respondError($e->getMessage(), 500);
    }
}

// =============================================================================
// GAMES HANDLERS
// =============================================================================

/**
 * Get list of available games
 * GET /api.php?action=get_games
 */
function handleGetGames() {
    try {
        requireAuth();

        $query = "SELECT game_id, game_name, game_type, description, target_emotion, difficulty_level, estimated_duration
                  FROM therapeutic_games
                  WHERE is_active = 1
                  ORDER BY game_name ASC";

        $games = fetchAll($query, []);

        respondSuccess($games, 'Games retrieved successfully');

    } catch (Exception $e) {
        logError('Get games error', ['error' => $e->getMessage()]);
        respondError($e->getMessage(), 500);
    }
}

/**
 * Save game progress
 * POST /api.php?action=save_game_progress
 * {game_id, score, mood_before, mood_after, level_reached}
 */
function handleSaveGameProgress() {
    try {
        requireAuth();

        $userId = getCurrentUserId();
        $gameId = (int)($_POST['game_id'] ?? 0);
        $score = (int)($_POST['score'] ?? 0);
        $moodBefore = sanitize($_POST['mood_before'] ?? 'neutral');
        $moodAfter = sanitize($_POST['mood_after'] ?? 'neutral');
        $levelReached = (int)($_POST['level_reached'] ?? 1);

        // Validate inputs
        if (empty($gameId)) {
            respondError('Game ID is required', 400);
        }

        if ($score < 0) {
            respondError('Score must be non-negative', 400);
        }

        // Validate mood values
        $validMoods = ['calm', 'stressed', 'anxious', 'happy', 'sad', 'neutral', 'energetic', 'tired'];
        if (!in_array($moodBefore, $validMoods) || !in_array($moodAfter, $validMoods)) {
            respondError('Invalid mood value. Allowed: ' . implode(', ', $validMoods), 400);
        }

        // Check if game exists
        if (!recordExists('therapeutic_games', ['game_id' => $gameId])) {
            respondError('Game not found', 404);
        }

        // Insert or update game progress
        $query = "INSERT INTO game_progress 
                  (user_id, game_id, score, level_reached, player_mood_before, player_mood_after, times_played, high_score, last_played_at)
                  VALUES (?, ?, ?, ?, ?, ?, 1, ?, NOW())
                  ON DUPLICATE KEY UPDATE
                  score = VALUES(score),
                  level_reached = VALUES(level_reached),
                  player_mood_after = VALUES(player_mood_after),
                  times_played = times_played + 1,
                  high_score = GREATEST(high_score, VALUES(high_score)),
                  last_played_at = NOW()";

        executeQuery($query, [$userId, $gameId, $score, $levelReached, $moodBefore, $moodAfter, $score]);

        // Log the action
        logAudit('SAVE_GAME_PROGRESS', 'game_progress', $userId, [
            'game_id' => $gameId,
            'score' => $score,
            'level_reached' => $levelReached
        ]);

        respondSuccess([], 'Game progress saved successfully', 201);

    } catch (Exception $e) {
        logError('Save game progress error', ['error' => $e->getMessage()]);
        respondError($e->getMessage(), 500);
    }
}

// =============================================================================
// MOOD HANDLER
// =============================================================================

/**
 * Save user's current mood to session
 * POST /api.php?action=save_mood
 * Body: mood (calm, happy, energetic, neutral, stressed, anxious, sad, tired)
 */
function handleSaveMood() {
    try {
        startSession();
        
        $mood = $_POST['mood'] ?? null;
        $validMoods = ['calm', 'happy', 'energetic', 'neutral', 'stressed', 'anxious', 'sad', 'tired'];
        
        if (!$mood) {
            respondError('Mood parameter is required', 400);
            return;
        }
        
        if (!in_array($mood, $validMoods)) {
            respondError('Invalid mood value. Must be one of: ' . implode(', ', $validMoods), 400);
            return;
        }
        
        // Store mood in session
        $_SESSION['current_mood'] = $mood;
        $_SESSION['mood_timestamp'] = date('Y-m-d H:i:s');
        
        respondSuccess([
            'mood' => $mood,
            'timestamp' => $_SESSION['mood_timestamp']
        ], 'Mood saved successfully', 200);

    } catch (Exception $e) {
        logError('Save mood error', ['error' => $e->getMessage()]);
        respondError('Failed to save mood: ' . $e->getMessage(), 500);
    }
}

// =============================================================================
// ASSESSMENT RESPONSE HANDLER
// =============================================================================

/**
 * Save a single assessment response/answer
 * POST /api.php?action=save_assessment_response
 * Required: assessment_id, question_id, response_value OR response_text
 */
function handleSaveAssessmentResponse() {
    try {
        startSession();
        requireAuth();
        
        $userId = getCurrentUserId();
        $assessment_id = (int)($_POST['assessment_id'] ?? 0);
        $question_id = (int)($_POST['question_id'] ?? 0);
        $response_value = $_POST['response_value'] ?? null;
        $response_text = $_POST['response_text'] ?? null;
        
        // Validate input
        if (!$assessment_id || !$question_id) {
            respondError('Missing assessment_id or question_id', 400);
            return;
        }
        
        // Verify assessment exists
        $assessment = Database::fetchOne(
            "SELECT * FROM assessments WHERE assessment_id = ?",
            [$assessment_id]
        );
        if (!$assessment) {
            respondError('Assessment not found', 404);
            return;
        }
        
        // Get question and validate
        $question = Database::fetchOne(
            "SELECT * FROM questions WHERE question_id = ? AND assessment_id = ?",
            [$question_id, $assessment_id]
        );
        if (!$question) {
            respondError('Question not found in this assessment', 404);
            return;
        }
        
        // Handle different question types
        $score_points = 0;
        $selected_option_id = null;
        
        if ($question['question_type'] === 'open_text') {
            // Open text - store text response, no score
            if (empty($response_text)) {
                $response_text = '';
            }
        } else {
            // Multiple choice, likert, yes_no, scale - need option selected
            if (!$response_value) {
                if ($question['is_required']) {
                    respondError('Answer is required for this question', 400);
                    return;
                }
            } else {
                $response_value = (int)$response_value;
                
                // Get the option and its point value
                $option = Database::fetchOne(
                    "SELECT * FROM answer_options WHERE option_id = ? AND question_id = ?",
                    [$response_value, $question_id]
                );
                
                if (!$option) {
                    respondError('Invalid answer option', 400);
                    return;
                }
                
                $score_points = $option['option_value'];
                $selected_option_id = $option['option_id'];
            }
        }
        
        // Save response to database
        try {
            Database::execute(
                "INSERT INTO assessment_responses 
                 (assessment_id, user_id, question_id, selected_option_id, response_text, score_points, completed_at)
                 VALUES (?, ?, ?, ?, ?, ?, NOW())
                 ON DUPLICATE KEY UPDATE 
                 selected_option_id = VALUES(selected_option_id),
                 response_text = VALUES(response_text),
                 score_points = VALUES(score_points),
                 completed_at = NOW()",
                [$assessment_id, $userId, $question_id, $selected_option_id, $response_text, $score_points]
            );
        } catch (Exception $e) {
            logError('Assessment response save error', [
                'user_id' => $userId,
                'assessment_id' => $assessment_id,
                'error' => $e->getMessage()
            ]);
            respondError('Failed to save response: ' . $e->getMessage(), 500);
            return;
        }
        
        // Check if all questions are answered
        $totalQuestions = $assessment['total_questions'];
        $answeredQuestions = Database::fetchOne(
            "SELECT COUNT(DISTINCT question_id) as count FROM assessment_responses 
             WHERE assessment_id = ? AND user_id = ?",
            [$assessment_id, $userId]
        )['count'] ?? 0;
        
        $isComplete = ($answeredQuestions >= $totalQuestions);
        
        $response_data = [
            'assessment_id' => $assessment_id,
            'question_id' => $question_id,
            'is_complete' => $isComplete,
            'progress' => round(($answeredQuestions / $totalQuestions) * 100)
        ];
        
        // If assessment is complete, calculate score
        if ($isComplete) {
            // Calculate total score
            $scoreData = Database::fetchOne(
                "SELECT SUM(score_points) as total FROM assessment_responses 
                 WHERE assessment_id = ? AND user_id = ?",
                [$assessment_id, $userId]
            );
            
            $total_score = $scoreData['total'] ?? 0;
            
            // Get max possible score
            $maxData = Database::fetchOne(
                "SELECT SUM(option_value) as max_score FROM answer_options ao
                 JOIN questions q ON ao.question_id = q.question_id
                 WHERE q.assessment_id = ? AND ao.option_order = 
                 (SELECT MAX(option_order) FROM answer_options 
                  WHERE question_id = q.question_id)
                 GROUP BY q.assessment_id",
                [$assessment_id]
            );
            
            // Simple approach: get highest option values
            $maxScore = Database::fetchOne(
                "SELECT SUM(COALESCE((
                    SELECT MAX(option_value) FROM answer_options WHERE question_id = q.question_id
                ), 0)) as max_score
                FROM questions q
                WHERE q.assessment_id = ?",
                [$assessment_id]
            )['max_score'] ?? 100;
            
            // Determine risk level (can be customized per assessment)
            $percentage = ($total_score / max($maxScore, 1)) * 100;
            
            $risk_level = 'low';
            if ($percentage >= 75) $risk_level = 'high';
            elseif ($percentage >= 50) $risk_level = 'medium';
            
            // Create assessment score record
            Database::execute(
                "INSERT INTO assessment_scores 
                 (user_id, assessment_id, total_score, max_score, risk_level, completed_at, completion_percentage)
                 VALUES (?, ?, ?, ?, ?, NOW(), 100)",
                [$userId, $assessment_id, $total_score, $maxScore, $risk_level]
            );
            
            $scoreId = Database::lastInsertId();
            $response_data['score_id'] = $scoreId;
            $response_data['total_score'] = $total_score;
            $response_data['max_score'] = $maxScore;
            $response_data['risk_level'] = $risk_level;
        }
        
        respondSuccess($response_data, 'Response saved successfully', 200);
        
    } catch (Exception $e) {
        logError('Assessment response handler error', ['error' => $e->getMessage()]);
        respondError('Failed to process assessment response: ' . $e->getMessage(), 500);
    }
}

// =============================================================================
// RECOMMENDATIONS HANDLER
// =============================================================================

/**
 * Get recommendations for user
 * GET /api.php?action=get_recommendations&limit=10
 */
function handleGetRecommendations() {
    try {
        requireAuth();

        $userId = getCurrentUserId();
        $limit = (int)($_GET['limit'] ?? 10);

        if ($limit < 1 || $limit > 100) {
            $limit = 10;
        }

        $query = "SELECT recommendation_id, score_id, assessment_id, recommendation_type, title, description,
                         priority_level, ai_source, ai_confidence_score, action_url, duration_minutes, 
                         is_acted_upon, user_found_helpful, expires_at, created_at
                  FROM recommendations
                  WHERE user_id = ? AND (expires_at IS NULL OR expires_at > NOW())
                  ORDER BY FIELD(priority_level, 'urgent', 'high', 'medium', 'low'), created_at DESC
                  LIMIT ?";

        $recommendations = fetchAll($query, [$userId, $limit]);

        respondSuccess($recommendations, 'Recommendations retrieved successfully');

    } catch (Exception $e) {
        logError('Get recommendations error', ['error' => $e->getMessage()]);
        respondError($e->getMessage(), 500);
    }
}

// =============================================================================
// NOTIFICATIONS HANDLER
// =============================================================================

/**
 * Get notifications for user
 * GET /api.php?action=get_notifications&limit=20&unread_only=false
 */
function handleGetNotifications() {
    try {
        requireAuth();

        $userId = getCurrentUserId();
        $limit = (int)($_GET['limit'] ?? 20);
        $unreadOnly = filter_var($_GET['unread_only'] ?? false, FILTER_VALIDATE_BOOLEAN);

        if ($limit < 1 || $limit > 100) {
            $limit = 20;
        }

        $query = "SELECT notification_id, notification_type, title, message, reference_id, is_read, 
                         priority, action_url, expires_at, created_at
                  FROM notifications
                  WHERE user_id = ? AND (expires_at IS NULL OR expires_at > NOW())";

        $params = [$userId];

        if ($unreadOnly) {
            $query .= " AND is_read = FALSE";
        }

        $query .= " ORDER BY created_at DESC LIMIT ?";
        $params[] = $limit;

        $notifications = fetchAll($query, $params);

        respondSuccess($notifications, 'Notifications retrieved successfully');

    } catch (Exception $e) {
        logError('Get notifications error', ['error' => $e->getMessage()]);
        respondError($e->getMessage(), 500);
    }
}

// =============================================================================
// HELPER FUNCTIONS
// =============================================================================

/**
 * Calculate maximum score for an assessment
 * @param int $assessmentId Assessment ID
 * @return float Maximum possible score
 */
function calculateMaxScore(int $assessmentId): float {
    try {
        // Get all questions for this assessment with answer options
        $questionsQuery = "SELECT q.question_id, q.question_type, q.weight FROM questions q 
                           WHERE q.assessment_id = ?
                           ORDER BY q.question_order";
        $questions = fetchAll($questionsQuery, [$assessmentId]);

        $maxScore = 0;

        foreach ($questions as $question) {
            // For open text and voice notes, no points
            if (in_array($question['question_type'], ['open_text', 'voice_note'])) {
                continue;
            }

            // For option-based questions, get max option value
            $maxOptionQuery = "SELECT MAX(option_value) as max_value FROM answer_options WHERE question_id = ?";
            $optionMax = fetchOne($maxOptionQuery, [$question['question_id']]);

            if ($optionMax && $optionMax['max_value'] !== null) {
                $maxScore += ($optionMax['max_value'] * $question['weight']);
            }
        }

        return $maxScore;

    } catch (Exception $e) {
        logError('Calculate max score error', ['assessmentId' => $assessmentId, 'error' => $e->getMessage()]);
        return 0;
    }
}

/**
 * Determine risk level based on assessment type and score percentage
 * @param string $assessmentType Assessment type (school_experience, mental_health, help_seeking)
 * @param float $percentage Score percentage
 * @return string Risk level (low, medium, high, critical)
 */
function determineRiskLevel(string $assessmentType, float $percentage): string {
    switch ($assessmentType) {
        case 'school_experience':
            // Higher score = better experience, so reverse scoring
            if ($percentage >= 75) return 'low';      // Very satisfied (6/6)
            if ($percentage >= 50) return 'medium';   // Satisfied (4-5/6)
            if ($percentage >= 25) return 'high';     // Dissatisfied (2-3/6)
            return 'critical';                         // Very dissatisfied (1/6)

        case 'mental_health':
            // Higher score = worse mental health
            if ($percentage <= 25) return 'low';      // Good mental health
            if ($percentage <= 50) return 'medium';   // Moderate concerns
            if ($percentage <= 75) return 'high';     // Significant concerns
            return 'critical';                         // Severe concerns

        case 'help_seeking':
            // Higher score = better attitudes toward help-seeking
            if ($percentage >= 75) return 'low';
            if ($percentage >= 50) return 'medium';
            if ($percentage >= 25) return 'high';
            return 'critical';

        default:
            // Generic scoring
            if ($percentage <= 25) return 'low';
            if ($percentage <= 50) return 'medium';
            if ($percentage <= 75) return 'high';
            return 'critical';
    }
}

/**
 * Get risk status message
 * @param string $riskLevel Risk level
 * @return string Status message
 */
function getRiskStatus(string $riskLevel): string {
    $statuses = [
        'critical' => 'Urgent: Please contact counselor immediately',
        'high' => 'High: Consider scheduling counseling session',
        'medium' => 'Medium: Try wellness activities or games',
        'low' => 'Low: Keep practicing wellness habits'
    ];
    return $statuses[$riskLevel] ?? 'Unknown';
}

?>
