<?php
/**
 * Student Dashboard
 * Wellnest Mental Health Web Application
 * 
 * Features:
 * - Profile preview with stats
 * - Current mood display and reporting
 * - Daily check-in system
 * - Quick access to assessments, games, notifications
 * - Recent recommendations
 */

$pageTitle = 'Student Dashboard';

require_once __DIR__ . '/../../config/settings.php';
require_once __DIR__ . '/../../src/functions.php';

startSession();
requireRole(ROLE_STUDENT);

$user = getCurrentUser();

try {
    $assessmentCount = Database::fetchOne(
        "SELECT COUNT(*) as count FROM assessment_scores WHERE user_id = ?",
        [$user['user_id']]
    )['count'] ?? 0;
    
    $latestScore = Database::fetchOne(
        "SELECT as2.*, a.assessment_name 
         FROM assessment_scores as2
         JOIN assessments a ON as2.assessment_id = a.assessment_id
         WHERE as2.user_id = ? 
         ORDER BY as2.completed_at DESC 
         LIMIT 1",
        [$user['user_id']]
    );
    
    $gamesPlayed = Database::fetchOne(
        "SELECT COUNT(*) as count FROM game_progress WHERE user_id = ?",
        [$user['user_id']]
    )['count'] ?? 0;
    
    $latestMood = Database::fetchOne(
        "SELECT * FROM game_progress WHERE user_id = ? ORDER BY last_played_at DESC LIMIT 1",
        [$user['user_id']]
    );
    
    $availableAssessments = Database::fetchAll(
        "SELECT * FROM assessments WHERE is_active = TRUE ORDER BY assessment_name"
    );
    
    $pendingRecommendations = Database::fetchAll(
        "SELECT * FROM recommendations 
         WHERE user_id = ? AND is_acted_upon = FALSE 
         ORDER BY priority_level DESC, created_at DESC 
         LIMIT 3",
        [$user['user_id']]
    );
    
    $games = Database::fetchAll("SELECT * FROM therapeutic_games WHERE is_active = TRUE");
    
} catch (Exception $e) {
    logError('Dashboard Error', ['error' => $e->getMessage()]);
    $assessmentCount = 0;
    $latestScore = null;
    $gamesPlayed = 0;
    $latestMood = null;
    $availableAssessments = [];
    $pendingRecommendations = [];
    $games = [];
}

include __DIR__ . '/../layouts/header.php';
?>

<style>
    .mood-selector { display: grid; grid-template-columns: repeat(auto-fit, minmax(60px, 1fr)); gap: 10px; max-width: 500px; }
    .mood-btn { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 15px; border: 2px solid #ddd; border-radius: 12px; background: white; cursor: pointer; transition: all 0.3s ease; font-size: 32px; }
    .mood-btn:hover { border-color: #B8860B; transform: scale(1.05); }
    .mood-btn.selected { background-color: #B8860B; border-color: #B8860B; }
    .card-hover { transition: all 0.3s; }
    .card-hover:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.15); }
</style>

<div class="mb-8">
    <h1 class="text-4xl font-bold text-bronze">Welcome, <?= e($user['first_name']) ?>! 👋</h1>
    <p class="text-body-gray text-lg mt-2">Your mental wellness dashboard</p>
</div>

<!-- Stats Grid -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
    <div class="bg-white rounded-xl shadow p-6 border-l-4 border-golden card-hover">
        <div class="flex justify-between">
            <div><p class="text-gray-600 text-sm">Assessments</p><p class="text-3xl font-bold text-bronze mt-1"><?= $assessmentCount ?></p></div>
            <div class="text-4xl">📋</div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow p-6 border-l-4 border-green-500 card-hover">
        <div class="flex justify-between">
            <div><p class="text-gray-600 text-sm">Games Played</p><p class="text-3xl font-bold text-green-600 mt-1"><?= $gamesPlayed ?></p></div>
            <div class="text-4xl">🎮</div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow p-6 border-l-4 <?= $latestScore ? (strpos($latestScore['risk_level'], 'low') !== false ? 'border-green-500' : (strpos($latestScore['risk_level'], 'med') !== false ? 'border-yellow-500' : 'border-red-500')) : 'border-gray-300' ?> card-hover">
        <div class="flex justify-between">
            <div><p class="text-gray-600 text-sm">Status</p><p class="text-lg font-bold text-bronze mt-1"><?= $latestScore ? ucfirst($latestScore['risk_level']) : 'Not Set' ?></p></div>
            <div class="text-4xl"><?= $latestScore ? ('😊') : ('❓') ?></div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow p-6 border-l-4 border-purple-500 card-hover">
        <div class="flex justify-between">
            <div><p class="text-gray-600 text-sm">Current Mood</p><p class="text-lg font-bold text-purple-600 mt-1"><?= $latestMood ? ucfirst($latestMood['player_mood_after']) : '-' ?></p></div>
            <div class="text-4xl">😊</div>
        </div>
    </div>
</div>

<!-- Check-In Section (Full Width) -->
<div class="bg-white rounded-xl shadow p-8 mb-8 card-hover">
    <h2 class="text-2xl font-bold text-bronze mb-4">How are you feeling today?</h2>
    <p class="text-gray-600 mb-6">Track your emotional state and get better support</p>
    <div class="mood-selector" id="moodSelector">
        <button class="mood-btn" data-mood="calm" title="Calm">😌</button>
        <button class="mood-btn" data-mood="happy" title="Happy">😊</button>
        <button class="mood-btn" data-mood="energetic" title="Energetic">🤩</button>
        <button class="mood-btn" data-mood="neutral" title="Neutral">😐</button>
        <button class="mood-btn" data-mood="stressed" title="Stressed">😰</button>
        <button class="mood-btn" data-mood="anxious" title="Anxious">😟</button>
        <button class="mood-btn" data-mood="sad" title="Sad">😢</button>
        <button class="mood-btn" data-mood="tired" title="Tired">😴</button>
    </div>
    <button class="mt-6 bg-bronze text-white px-6 py-3 rounded-lg hover:bg-bronze-700 font-semibold" id="saveMoodBtn" style="display:none; background-color: #8B6F47; cursor: pointer;">Save Mood</button>
</div>

<!-- Games and Assessments Grid Layout -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
    
    <!-- Wellness Games - Left Column -->
    <div>
        <h2 class="text-2xl font-bold text-bronze mb-6">🎮 Wellness Games</h2>
        <?php if (!empty($games)): ?>
            <div class="space-y-4">
                <?php foreach ($games as $game): ?>
                    <a href="/Wellnest_Sim_Web_Application/views/student/games/frogger.php" class="block border-2 border-gray-200 rounded-lg p-6 hover:border-golden hover:shadow-lg transition hover:bg-gray-50 text-decoration-none card-hover">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <div class="text-3xl mb-2">🐸</div>
                                <h3 class="font-semibold text-bronze text-lg"><?= e($game['game_name']) ?></h3>
                                <p class="text-gray-600 text-sm mt-1"><?= e($game['description'] ?? 'A therapeutic wellness game') ?></p>
                                <p class="text-xs text-gray-500 mt-3">
                                    <span class="mr-4">⏱️ <?= $game['estimated_duration'] ?> mins</span>
                                    <span>🎯 <?= ucfirst($game['game_type']) ?></span>
                                </p>
                                <?php if (!empty($game['benefits'])): ?>
                                    <p class="text-xs text-gray-600 mt-2 italic">💡 <?= e($game['benefits']) ?></p>
                                <?php endif; ?>
                            </div>
                            <span class="bg-golden text-bronze-800 px-4 py-2 rounded-lg text-sm font-semibold whitespace-nowrap ml-4">Play Now →</span>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="bg-gray-50 rounded-lg p-6 text-center">
                <p class="text-gray-600">No games available at the moment.</p>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Available Assessments - Right Column -->
    <div>
        <h2 class="text-2xl font-bold text-bronze mb-6">📋 Available Assessments</h2>
        <div class="space-y-4">
            <?php foreach ($availableAssessments as $a): ?>
                <a href="/Wellnest_Sim_Web_Application/views/student/assessment.php?id=<?= $a['assessment_id'] ?>" class="block border-2 border-gray-200 rounded-lg p-6 hover:border-golden hover:shadow-lg transition hover:bg-gray-50 text-decoration-none card-hover">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <h3 class="font-semibold text-bronze text-lg"><?= e($a['assessment_name']) ?></h3>
                            <p class="text-gray-600 text-sm mt-1"><?= e($a['description'] ?? '') ?></p>
                            <p class="text-xs text-gray-500 mt-3">⏱️ <?= $a['estimated_time'] ?> mins | ❓ <?= $a['total_questions'] ?> questions</p>
                        </div>
                        <span class="bg-golden text-bronze-800 px-4 py-2 rounded-lg text-sm font-semibold whitespace-nowrap ml-4">Start Now →</span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Profile and Notifications Sidebar -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Profile Card -->
    <div class="bg-white rounded-xl shadow p-6 card-hover">
        <div class="text-center mb-4">
            <div class="w-16 h-16 bg-gradient-to-br from-bronze to-golden rounded-full mx-auto mb-3 flex items-center justify-center text-2xl font-bold text-white">
                <?= strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)) ?>
            </div>
            <h3 class="text-xl font-bold text-bronze"><?= e($user['first_name']) ?> <?= e($user['last_name']) ?></h3>
            <p class="text-gray-600 text-sm mt-1"><?= e($user['email']) ?></p>
        </div>
        <a href="/Wellnest_Sim_Web_Application/views/student/profile.php" class="block w-full text-center bg-bronze text-white px-4 py-2 rounded-lg hover:bg-bronze-700 font-semibold text-sm mb-2">View Profile</a>
        <a href="/Wellnest_Sim_Web_Application/views/student/profile.php" class="block w-full text-center border border-bronze text-bronze px-4 py-2 rounded-lg hover:bg-bronze-50 font-semibold text-sm">Edit Profile</a>
    </div>
    
    <!-- Notifications -->
    <div class="bg-white rounded-xl shadow p-6 card-hover">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-bronze">🔔 Notifications</h3>
            <a href="/Wellnest_Sim_Web_Application/views/student/notifications.php" class="text-golden text-sm font-semibold hover:underline">View All</a>
        </div>
        <p class="text-gray-500 text-sm">You're all caught up!</p>
    </div>
</div>

<?php if (!empty($pendingRecommendations)): ?>n 
<div class="mt-8 bg-blue-50 rounded-xl shadow p-8 card-hover">
    <h2 class="text-2xl font-bold text-bronze mb-6">💡 Recommendations</h2>
    <div class="space-y-4">
        <?php foreach ($pendingRecommendations as $rec): ?>
            <div class="bg-white border-l-4 border-blue-500 rounded-lg p-5">
                <h4 class="font-semibold text-bronze"><?= e($rec['title']) ?></h4>
                <p class="text-gray-700 text-sm mt-2"><?= e($rec['description']) ?></p>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<script>
let selectedMood = null;

// Wait for DOM to be ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initMoodSelector);
} else {
    initMoodSelector();
}

function initMoodSelector() {
    // Setup mood button listeners
    document.querySelectorAll('.mood-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.mood-btn').forEach(b => b.classList.remove('selected'));
            this.classList.add('selected');
            selectedMood = this.dataset.mood;
            const saveBtn = document.getElementById('saveMoodBtn');
            if (saveBtn) {
                saveBtn.style.display = 'block';
            }
        });
    });
    
    // Setup save mood button listener
    const saveMoodBtn = document.getElementById('saveMoodBtn');
    if (saveMoodBtn) {
        saveMoodBtn.addEventListener('click', saveMood);
    }
}

function saveMood() {
    if (!selectedMood) {
        showError('Please select a mood first');
        return;
    }
    
    console.log('Saving mood:', selectedMood);
    
    // Save mood to session/database
    const formData = new FormData();
    formData.append('action', 'save_mood');
    formData.append('mood', selectedMood);
    
    fetch('/Wellnest_Sim_Web_Application/src/api.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log('Response status:', response.status);
        console.log('Response OK:', response.ok);
        return response.json();
    })
    .then(data => {
        console.log('API Response:', data);
        if (data.success) {
            // Update the Current Mood display in stat cards
            const moodEmoji = {
                'calm': '😌',
                'happy': '😊',
                'energetic': '🤩',
                'neutral': '😐',
                'stressed': '😰',
                'anxious': '😟',
                'sad': '😢',
                'tired': '😴'
            };
            
            // Find and update the Current Mood card
            const statCards = document.querySelectorAll('.bg-white.rounded-xl.shadow.p-6.border-l-4');
            for (let card of statCards) {
                const text = card.textContent;
                if (text.includes('Current Mood')) {
                    const valueSpan = card.querySelector('p.text-lg.font-bold');
                    if (valueSpan) {
                        valueSpan.textContent = selectedMood.charAt(0).toUpperCase() + selectedMood.slice(1);
                    }
                    const emojiDiv = card.querySelector('.text-4xl');
                    if (emojiDiv) {
                        emojiDiv.textContent = moodEmoji[selectedMood] || '😊';
                    }
                    break;
                }
            }
            
            showSuccess('Mood saved: ' + selectedMood, function() {
                document.querySelectorAll('.mood-btn').forEach(b => b.classList.remove('selected'));
                const saveMoodBtn = document.getElementById('saveMoodBtn');
                if (saveMoodBtn) {
                    saveMoodBtn.style.display = 'none';
                }
                selectedMood = null;
            });
        } else {
            console.error('API returned error:', data.message);
            showError('Failed to save mood: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Fetch error:', error);
        showError('Error saving mood: ' + error.message);
    });
}
</script>

<?php include __DIR__ . '/../layouts/modal.php'; ?>
<?php include __DIR__ . '/../layouts/footer.php'; ?>