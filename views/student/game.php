<?php
/**
 * House Navigation Game - 3D Wellness Adventure
 * Wellnest Mental Health Web Application
 * 
 * An endless runner style game where players guide a small animal through a peaceful house,
 * dodging furniture obstacles while building calm energy through sustained gameplay.
 * Themed like Animal Crossing with wellness-focused mechanics.
 */

$pageTitle = 'Play Game - House Adventure';

require_once __DIR__ . '/../../config/settings.php';
require_once __DIR__ . '/../../src/functions.php';

startSession();
requireRole(ROLE_STUDENT);

$user = getCurrentUser();
$gameId = isset($_GET['id']) ? intval($_GET['id']) : 1; // Default to House Adventure (id=1)

// Special handling for Frogger game (id=2)
if ($gameId === 2) {
    // Redirect to Frogger game
    redirect('/Wellnest_Sim_Web_Application/views/student/games/frogger.php');
}

try {
    $game = Database::fetchOne(
        "SELECT * FROM therapeutic_games WHERE game_id = ? AND is_active = TRUE",
        [$gameId]
    );
    
    if (!$game) {
        redirect('/Wellnest_Sim_Web_Application/views/student/dashboard.php');
    }
    
    $gameProgress = Database::fetchOne(
        "SELECT * FROM game_progress WHERE user_id = ? AND game_id = ?",
        [$user['user_id'], $gameId]
    );
    
} catch (Exception $e) {
    logError('Game Error', ['error' => $e->getMessage()]);
    $game = null;
    $gameProgress = null;
}

// Game scenarios for Crossroads simulation
$gameScenarios = [
    [
        'id' => 1,
        'title' => 'School Exam Stress',
        'description' => 'You have a major exam coming up next week and you\'re feeling overwhelmed by the amount of material to study.',
        'choices' => [
            ['text' => 'Create a study schedule and break material into manageable chunks', 'points' => 100, 'impact' => 'positive', 'message' => 'Great choice! Breaking tasks down reduces overwhelm.'],
            ['text' => 'Avoid studying and watch videos to distract yourself', 'points' => 20, 'impact' => 'negative', 'message' => 'Avoidance usually makes anxiety worse.'],
            ['text' => 'Talk to a teacher about your concerns and ask for help', 'points' => 90, 'impact' => 'positive', 'message' => 'Seeking support is a sign of strength!']
        ]
    ],
    [
        'id' => 2,
        'title' => 'Friendship Conflict',
        'description' => 'A close friend said something that hurt your feelings, and now you\'re contemplating how to respond.',
        'choices' => [
            ['text' => 'Take time to calm down, then talk to them calmly about how you feel', 'points' => 95, 'impact' => 'positive', 'message' => 'Communication is key to healthy relationships!'],
            ['text' => 'Ignore them for a few days to show them you\'re upset', 'points' => 30, 'impact' => 'negative', 'message' => 'Silent treatment can escalate conflicts.'],
            ['text' => 'Post about it on social media to vent', 'points' => 15, 'impact' => 'negative', 'message' => 'Public venting can damage relationships further.']
        ]
    ],
    [
        'id' => 3,
        'title' => 'Family Pressure',
        'description' => 'Your parents are pushing you hard to get perfect grades and you feel exhausted and pressured.',
        'choices' => [
            ['text' => 'Express your feelings calmly and discuss realistic expectations', 'points' => 100, 'impact' => 'positive', 'message' => 'Open communication helps reset expectations.'],
            ['text' => 'Work even harder to try to please them', 'points' => 25, 'impact' => 'negative', 'message' => 'Living for others\' approval leads to burnout.'],
            ['text' => 'Practice relaxation techniques like deep breathing or meditation', 'points' => 85, 'impact' => 'positive', 'message' => 'Self-care helps manage stress effectively!']
        ]
    ],
    [
        'id' => 4,
        'title' => 'Social Anxiety',
        'description' => 'You\'re invited to a school event where you don\'t know many people and you\'re feeling anxious about going.',
        'choices' => [
            ['text' => 'Go with a friend for support and take gradual steps to socialize', 'points' => 90, 'impact' => 'positive', 'message' => 'Facing fears gradually builds confidence!'],
            ['text' => 'Cancel and stay home where you feel safe', 'points' => 15, 'impact' => 'negative', 'message' => 'Avoidance reinforces anxiety in the long term.'],
            ['text' => 'Arrive early to meet a few people before the crowd arrives', 'points' => 95, 'impact' => 'positive', 'message' => 'Strategic preparation reduces anxiety!']
        ]
    ],
    [
        'id' => 5,
        'title' => 'Feeling Overwhelmed',
        'description' => 'Multiple assignments, extracurriculars, and personal issues are piling up and you feel overwhelmed.',
        'choices' => [
            ['text' => 'Prioritize tasks, delegate what you can, and ask for help when needed', 'points' => 100, 'impact' => 'positive', 'message' => 'Smart prioritization prevents overwhelm!'],
            ['text' => 'Try to handle everything yourself to avoid burdening others', 'points' => 20, 'impact' => 'negative', 'message' => 'Taking on too much alone leads to burnout.'],
            ['text' => 'Take a mental health break - rest is productive too!', 'points' => 85, 'impact' => 'positive', 'message' => 'Rest and recuperation are essential for wellbeing!']
        ]
    ]
];

// Don't include header for game - we want full-screen canvas
// include __DIR__ . '/../layouts/header.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? 'Game') ?> - <?= e(APP_NAME) ?></title>
    <link href="<?= APP_URL ?>/css/output.css" rel="stylesheet">
</head>
<body style="margin: 0; padding: 0; overflow: hidden;">
<style>
    /* Game Container */
    html, body { 
        margin: 0; 
        padding: 0;
        width: 100%; 
        height: 100%; 
        overflow: hidden; 
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
    }
    
    #gameContainer {
        width: 100%;
        height: 100vh;
        position: fixed;
        top: 0;
        left: 0;
        background: linear-gradient(135deg, #8B6F47 0%, #D4A574 100%);
    }
    
    #canvas {
        display: block;
        width: 100vw;
        height: 100vh;
        pointer-events: none; /* Disabled until game starts */
    }
    
    /* Heads-Up Display */
    .game-hud {
        position: absolute;
        top: 20px;
        left: 20px;
        right: 20px;
        z-index: 10;
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 15px;
    }
    
    .hud-card {
        background: rgba(255, 255, 255, 0.95);
        border-radius: 12px;
        padding: 15px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    }
    
    .hud-label {
        color: #999;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 8px;
    }
    
    .hud-value {
        color: #8B6F47;
        font-size: 28px;
        font-weight: bold;
    }
    
    /* Energy Bar */
    .energy-bar {
        background: #e0e0e0;
        border-radius: 10px;
        height: 12px;
        overflow: hidden;
        margin-top: 8px;
    }
    
    .energy-fill {
        background: linear-gradient(90deg, #10b981 0%, #059669 100%);
        height: 100%;
        transition: width 0.2s ease;
        border-radius: 10px;
    }
    
    .energy-fill.warning {
        background: linear-gradient(90deg, #f59e0b 0%, #d97706 100%);
    }
    
    .energy-fill.danger {
        background: linear-gradient(90deg, #ef4444 0%, #dc2626 100%);
    }
    
    /* Game Over Screen */
    .game-over-screen {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.85);
        display: none;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        z-index: 100;
    }
    
    .game-over-screen.show {
        display: flex;
    }
    
    .game-over-content {
        background: white;
        border-radius: 20px;
        padding: 40px;
        text-align: center;
        max-width: 500px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    }
    
    .game-over-title {
        font-size: 36px;
        color: #8B6F47;
        margin-bottom: 20px;
    }
    
    .game-over-stats {
        margin: 30px 0;
        text-align: left;
        background: #f9f9f9;
        padding: 20px;
        border-radius: 12px;
    }
    
    .stat-row {
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
        font-size: 16px;
    }
    
    .stat-label { color: #999; }
    .stat-value { color: #8B6F47; font-weight: bold; }
    
    /* Buttons */
    .game-btn {
        padding: 12px 30px;
        border: none;
        border-radius: 8px;
        font-weight: bold;
        cursor: pointer;
        transition: all 0.3s;
        margin: 10px;
        font-size: 16px;
    }
    
    .btn-primary {
        background: linear-gradient(135deg, #8B6F47 0%, #D4A574 100%);
        color: white;
    }
    
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
    }
    
    .btn-secondary {
        background: white;
        color: #8B6F47;
        border: 2px solid #8B6F47;
    }
    
    .btn-secondary:hover {
        background: #f9f9f9;
    }
    
    /* Start Screen */
    .start-screen {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(135deg, #8B6F47 0%, #D4A574 100%);
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        z-index: 50;
    }
    
    .start-screen.hidden {
        display: none;
    }
    
    .start-content {
        background: white;
        border-radius: 20px;
        padding: 50px;
        text-align: center;
        max-width: 600px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    }
    
    .start-title {
        font-size: 42px;
        color: #8B6F47;
        margin-bottom: 15px;
    }
    
    .start-subtitle {
        font-size: 18px;
        color: #999;
        margin-bottom: 30px;
    }
    
    .how-to-play {
        text-align: left;
        background: #f9f9f9;
        padding: 20px;
        border-radius: 12px;
        margin: 30px 0;
        font-size: 14px;
        color: #666;
    }
    
    .how-to-play h3 {
        margin-top: 0;
        color: #8B6F47;
    }
    
    .how-to-play ul {
        margin: 10px 0;
        padding-left: 20px;
    }
    
    .how-to-play li {
        margin: 8px 0;
    }
    
    /* Controls Info */
    .controls-info {
        position: absolute;
        bottom: 20px;
        left: 20px;
        background: rgba(255, 255, 255, 0.9);
        padding: 15px 20px;
        border-radius: 10px;
        font-size: 13px;
        z-index: 10;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }
    
    .controls-info strong {
        color: #8B6F47;
        display: block;
        margin-bottom: 5px;
    }
    
    .pause-btn {
        position: absolute;
        bottom: 20px;
        right: 20px;
        padding: 10px 20px;
        background: white;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-weight: bold;
        color: #8B6F47;
        z-index: 10;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        transition: all 0.3s;
    }
    
    .pause-btn:hover {
        background: #f9f9f9;
    }
</style>

<!-- Game Container -->
<div id="gameContainer">
    <!-- Canvas for Three.js -->
    <canvas id="canvas"></canvas>
    
    <!-- Start Screen -->
    <div id="startScreen" class="start-screen">
        <div class="start-content">
            <div class="start-title">🏠 House Adventure</div>
            <div class="start-subtitle">Guide your animal friend through the house!</div>
            
            <div class="how-to-play">
                <h3>How to Play:</h3>
                <ul>
                    <li><strong>🎮 Arrow Keys</strong> or <strong>WASD</strong> - Move left/right</li>
                    <li><strong>Space</strong> - Jump over obstacles</li>
                    <li><strong>Goal:</strong> Survive as long as possible</li>
                    <li><strong>Energy:</strong> Builds up as you play and dodge obstacles</li>
                    <li><strong>⚠️ Obstacles:</strong> Sliding furniture you must dodge or jump</li>
                </ul>
            </div>
            
            <button class="game-btn btn-primary" onclick="startGame()" style="font-size: 18px; padding: 15px 40px;">
                Start Game 🎮
            </button>
        </div>
    </div>
    
    <!-- Game HUD (Top left) -->
    <div class="game-hud">
        <div class="hud-card">
            <div class="hud-label">Survival Time</div>
            <div class="hud-value"><span id="scoreDisplay">0.0</span>s</div>
        </div>
        <div class="hud-card">
            <div class="hud-label">Energy Level</div>
            <div class="energy-bar">
                <div class="energy-fill" id="energyFill" style="width: 60%"></div>
            </div>
        </div>
        <div class="hud-card">
            <div class="hud-label">Safe Zones Crossed</div>
            <div class="hud-value" id="safesDisplay">0</div>
        </div>
    </div>
    
    <!-- Controls Info -->
    <div class="controls-info">
        <strong>⌨️ Controls:</strong>
        Arrows/WASD: Move | Space: Jump
    </div>
    
    <!-- Pause Button -->
    <button class="pause-btn" onclick="togglePause()">⏸ Pause</button>
    
    <!-- Game Over Screen -->
    <div id="gameOverScreen" class="game-over-screen">
        <div class="game-over-content">
            <div class="game-over-title">Game Over! 🎮</div>
            
            <div class="game-over-stats">
                <div class="stat-row">
                    <span class="stat-label">Survival Time:</span>
                    <span class="stat-value"><span id="finalScore">0</span>s</span>
                </div>
                <div class="stat-row">
                    <span class="stat-label">Final Energy:</span>
                    <span class="stat-value"><span id="finalEnergy">0</span>%</span>
                </div>
                <div class="stat-row">
                    <span class="stat-label">Safe Zones Crossed:</span>
                    <span class="stat-value"><span id="finalSafes">0</span></span>
                </div>
                <div class="stat-row">
                    <span class="stat-label">Wellness Score:</span>
                    <span class="stat-value"><span id="wellnessScore">0</span></span>
                </div>
            </div>
            
            <div style="margin-top: 30px;">
                <button class="game-btn btn-primary" onclick="startGame()">Play Again</button>
                <button class="game-btn btn-secondary" onclick="goHome()">Back to Dashboard</button>
            </div>
        </div>
    </div>
</div>

<!-- Three.js Library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>

<script>
'use strict';

// FIRST THING: Define startGame before anything else, with error handling
window.gameStarted = false;
window.startGame = function() {
    console.log('=== START GAME CLICKED ===');
    if (gameState.isRunning) {
        console.log('Game already running');
        return;
    }
    
    try {
        console.log('Hiding start screen...');
        const startScreen = document.getElementById('startScreen');
        if (startScreen) {
            startScreen.style.display = 'none';
        }
        
        console.log('Resetting game state...');
        gameState.isRunning = true;
        gameState.isPaused = false;
        gameState.survival_time = 0;
        gameState.energy = 60;
        gameState.safesCrossed = 0;
        gameState.gameStartTime = Date.now();
        gameState.animalPosition = { x: 0, y: 1.2, z: 0 };
        gameState.obstacles = [];
        gameState.safeZones = [];
        gameState.isJumping = false;
        gameState.jumpVelocity = 0;
        
        // Reset animal position in 3D
        if (animal) {
            animal.position.set(0, 1.2, 0);
        }
        
        // Clear obstacles from scene
        gameState.obstacles.forEach(obs => {
            if (obs.mesh) scene.remove(obs.mesh);
        });
        gameState.obstacles = [];
        
        console.log('Starting game loop...');
        gameLoop();
    } catch(e) {
        console.error('Error in startGame:', e);
        console.error('Stack:', e.stack);
        alert('Error starting game: ' + e.message);
    }
};

console.log('window.startGame defined as:', typeof window.startGame);

// ============= Game State - MUST be first so startGame can use it =============
let gameState = {
    isRunning: false,
    isPaused: false,
    survival_time: 0,
    energy: 60,
    safesCrossed: 0,
    gameStartTime: 0,
    animalPosition: { x: 0, y: 1.2, z: 0 },
    isJumping: false,
    jumpVelocity: 0,
    obstacles: [],
    safeZones: [],
    moodBefore: 'calm',
    moodAfter: 'calm'
};

// ============= Game Configuration =============
const CONFIG = {
    gameWidth: 10,
    gameHeight: 100,
    gameDepth: 10,
    animalSpeed: 0.1,
    maxEnergy: 100,
    energyDecayRate: 0.3,
    obstacleSpeed: 0.15,
    obstacleSpawnRate: 0.02,
    safeZoneFrequency: 15,
    jumpPower: 1.2,
    gravity: 0.085
};

// ============= Three.js Setup =============
let scene, camera, renderer, animal, obstacles = [], safeMarkers = [], testCube;
let keys = {};
let clock = new THREE.Clock();

// Game loop - render the scene continuously
function gameLoop() {
    if (!gameState.isRunning) return;
    requestAnimationFrame(gameLoop);
    
    if (!gameState.isPaused) {
        updateGame();
    }
    
    // Always render
    try {
        renderer.render(scene, camera);
    } catch (e) {
        console.error('Render error:', e);
    }
}

// Update game state each frame
function updateGame() {
    const deltaTime = 0.016; // Assume 60 FPS
    
    // ===== KEYBOARD INPUT =====
    const moveSpeed = 6;
    const forwardSpeed = 4;
    
    // Left/Right movement
    if (keys['ArrowLeft'] || keys['a'] || keys['A']) {
        gameState.animalPosition.x = Math.max(-CONFIG.gameWidth / 2, gameState.animalPosition.x - moveSpeed * deltaTime);
    }
    if (keys['ArrowRight'] || keys['d'] || keys['D']) {
        gameState.animalPosition.x = Math.min(CONFIG.gameWidth / 2, gameState.animalPosition.x + moveSpeed * deltaTime);
    }
    
    // Forward/Backward movement
    if (keys['w'] || keys['W']) {
        gameState.animalPosition.z += forwardSpeed * deltaTime;
    }
    if (keys['s'] || keys['S']) {
        gameState.animalPosition.z = Math.max(0, gameState.animalPosition.z - forwardSpeed * deltaTime);
    }
    
    // ===== JUMP PHYSICS =====
    if (gameState.isJumping) {
        gameState.jumpVelocity -= CONFIG.gravity;
        gameState.animalPosition.y += gameState.jumpVelocity;
        
        // Check if landed on ground
        if (gameState.animalPosition.y <= 1.2) {
            gameState.animalPosition.y = 1.2;
            gameState.isJumping = false;
            gameState.jumpVelocity = 0;
        }
    }
    
    // ===== KEEP PLAYER IN BOUNDS =====
    // Ensure player stays within game world
    gameState.animalPosition.x = Math.max(-CONFIG.gameWidth / 2, Math.min(CONFIG.gameWidth / 2, gameState.animalPosition.x));
    gameState.animalPosition.y = Math.max(1.2, gameState.animalPosition.y);
    gameState.animalPosition.z = Math.max(0, gameState.animalPosition.z);
    
    // ===== UPDATE PLAYER POSITION IN 3D =====
    if (animal) {
        animal.position.set(gameState.animalPosition.x, gameState.animalPosition.y, gameState.animalPosition.z);
    }
    
    // ===== OBSTACLE & SAFE ZONE SPAWNING =====
    if (Math.random() < CONFIG.obstacleSpawnRate) {
        const newObstacle = createObstacle({ x: (Math.random() - 0.5) * CONFIG.gameWidth, z: gameState.animalPosition.z + 50 });
        gameState.obstacles.push(newObstacle);
    }
    
    // Update obstacle positions and remove off-screen ones
    for (let i = gameState.obstacles.length - 1; i >= 0; i--) {
        const obs = gameState.obstacles[i];
        obs.z -= obs.speed * deltaTime;
        obs.mesh.position.z = obs.z;
        
        // Remove if too far behind player
        if (obs.z < gameState.animalPosition.z - 10) {
            scene.remove(obs.mesh);
            gameState.obstacles.splice(i, 1);
        }
    }
    
    // ===== SAFE ZONE SPAWNING =====
    // Spawn safe zones periodically ahead of player
    if (gameState.safeZones.length === 0 || gameState.safeZones[gameState.safeZones.length - 1].z < gameState.animalPosition.z + 100) {
        const safeZone = createSafeZone(gameState.animalPosition.z + 150);
        gameState.safeZones.push(safeZone);
    }
    
    // ===== TEST CUBE ANIMATION =====
    if (testCube) {
        testCube.rotation.x += 0.01;
        testCube.rotation.y += 0.01;
        testCube.position.z += 0.05;
    }
    
    // ===== COLLISION & SAFE ZONE CHECKS =====
    checkCollisions();
    checkSafeZones();
    
    // ===== UPDATE TIMER & DISPLAYS =====
    gameState.survival_time = (Date.now() - gameState.gameStartTime) / 1000;
    document.getElementById('scoreDisplay').textContent = gameState.survival_time.toFixed(1);
    
    // ===== ENERGY DECAY =====
    gameState.energy = Math.max(0, gameState.energy - CONFIG.energyDecayRate * deltaTime);
    updateEnergyDisplay();
    
    // ===== CAMERA FOLLOW =====
    if (camera && animal) {
        // Follow player with smooth offset
        const targetX = gameState.animalPosition.x;
        const targetZ = gameState.animalPosition.z + 20; // Keep distance behind
        const targetY = gameState.animalPosition.y + 8;  // Keep looking down at player
        
        // Smooth camera movement for smoother experience
        camera.position.x += (targetX - camera.position.x) * 0.1;
        camera.position.z += (targetZ - camera.position.z) * 0.1;
        camera.position.y += (targetY - camera.position.y) * 0.1;
        
        // Look at player head area
        camera.lookAt(gameState.animalPosition.x, gameState.animalPosition.y + 1.5, gameState.animalPosition.z);
    }
    
    // ===== END GAME CONDITION =====
    if (gameState.energy <= 0) {
        endGame();
    }
}

// Update the energy bar display
function updateEnergyDisplay() {
    const energyPercent = (gameState.energy / CONFIG.maxEnergy) * 100;
    const energyFill = document.getElementById('energyFill');
    if (energyFill) {
        energyFill.style.width = energyPercent + '%';
        energyFill.className = 'energy-fill' + (energyPercent > 60 ? '' : energyPercent > 30 ? ' warning' : ' danger');
    }
}

function initThreeJS() {
    console.log('=== INITIALIZING THREE.JS ===');
    
    // Get canvas
    const canvas = document.getElementById('canvas');
    console.log('Canvas found:', !!canvas);
    console.log('Canvas dimensions:', canvas.clientWidth, 'x', canvas.clientHeight);
    
    if (!canvas) {
        console.error('FATAL: Canvas element not found!');
        alert('Canvas not found!');
        return;
    }
    
    // Check WebGL support
    const webglSupported = (() => {
        try {
            const canvas2 = document.createElement('canvas');
            return !!(window.WebGLRenderingContext && (canvas2.getContext('webgl') || canvas2.getContext('experimental-webgl')));
        } catch(e) { 
            return false; 
        }
    })();
    console.log('WebGL supported:', webglSupported);
    
    if (!webglSupported) {
        alert('WebGL is not supported in your browser!');
        return;
    }
    
    // Create scene
    scene = new THREE.Scene();
    scene.background = new THREE.Color(0x87CEEB);
    console.log('Scene created');
    
    // Create camera
    camera = new THREE.PerspectiveCamera(
        75,
        window.innerWidth / window.innerHeight,
        0.1,
        3000
    );
    camera.position.z = 15;
    camera.position.y = 5;
    console.log('Camera created, position:', camera.position);
    
    // Create renderer with explicit canvas
    try {
        const rendererParams = {
            canvas: canvas,
            antialias: true,
            alpha: false,
            logarithmicDepthBuffer: false
        };
        console.log('Creating renderer with params:', rendererParams);
        
        renderer = new THREE.WebGLRenderer(rendererParams);
        console.log('WebGLRenderer created successfully');
        
        renderer.setSize(window.innerWidth, window.innerHeight);
        renderer.setPixelRatio(window.devicePixelRatio);
        renderer.setClearColor(0x87CEEB, 1);
        console.log('Renderer configured');
        
    } catch (e) {
        console.error('FATAL: Failed to create WebGL renderer:', e);
        alert('Failed to initialize WebGL: ' + e.message);
        return;
    }
    
    // Add lighting
    const light1 = new THREE.AmbientLight(0xffffff, 0.9);
    scene.add(light1);
    console.log('Ambient light added');
    
    const light2 = new THREE.DirectionalLight(0xffffff, 0.8);
    light2.position.set(10, 20, 10);
    scene.add(light2);
    console.log('Directional light added');
    
    // Add a test CUBE
    const cubeGeo = new THREE.BoxGeometry(2, 2, 2);
    const cubeMat = new THREE.MeshLambertMaterial({ color: 0xFF0000 });
    testCube = new THREE.Mesh(cubeGeo, cubeMat);
    testCube.position.z = 0;
    testCube.position.y = 2;
    testCube.position.x = 0;
    scene.add(testCube);
    console.log('RED TEST CUBE added to scene');
    
    // Create house environment
    console.log('Creating walls...');
    createWalls();
    
    console.log('Creating player animal...');
    createAnimal();
    
    console.log('Scene children count:', scene.children.length);
    
    // Set up resize listener
    window.addEventListener('resize', onWindowResize);
    
    // Render
    console.log('About to render...');
    renderer.render(scene, camera);
    console.log('=== RENDER COMPLETE ===');
}

function createWalls() {
    // Left and right walls
    const wallMaterial = new THREE.MeshLambertMaterial({ color: 0xdaa520 }); // Goldenrod
    
    // Left wall
    const leftWallGeometry = new THREE.BoxGeometry(1, 10, CONFIG.gameHeight);
    const leftWall = new THREE.Mesh(leftWallGeometry, wallMaterial);
    leftWall.position.set(-CONFIG.gameWidth / 2 - 0.5, 5, 0);
    leftWall.castShadow = true;
    leftWall.receiveShadow = true;
    scene.add(leftWall);
    
    // Right wall
    const rightWall = new THREE.Mesh(leftWallGeometry, wallMaterial);
    rightWall.position.set(CONFIG.gameWidth / 2 + 0.5, 5, 0);
    rightWall.castShadow = true;
    rightWall.receiveShadow = true;
    scene.add(rightWall);
    
    // Back wall
    const backWallGeometry = new THREE.BoxGeometry(CONFIG.gameWidth, 10, 1);
    const backWall = new THREE.Mesh(backWallGeometry, wallMaterial);
    backWall.position.set(0, 5, -CONFIG.gameHeight / 2 - 0.5);
    backWall.castShadow = true;
    backWall.receiveShadow = true;
    scene.add(backWall);
}

function createAnimal() {
    // Simple animal shape (cube with eyes effect)
    const geometry = new THREE.BoxGeometry(0.6, 0.8, 0.6);
    const material = new THREE.MeshLambertMaterial({ color: 0xff69b4 }); // Pink (cute animal)
    animal = new THREE.Mesh(geometry, material);
    animal.position.set(0, 1.2, 0);
    animal.castShadow = true;
    animal.receiveShadow = true;
    
    scene.add(animal);
    console.log('Animal (pink) added at:', animal.position);
    
    // Add eyes (simple dots)
    const eyeGeometry = new THREE.SphereGeometry(0.1, 8, 8);
    const eyeMaterial = new THREE.MeshLambertMaterial({ color: 0x000000 });
    const leftEye = new THREE.Mesh(eyeGeometry, eyeMaterial);
    leftEye.position.set(-0.15, 0.15, 0.35);
    animal.add(leftEye);
    
    const rightEye = new THREE.Mesh(eyeGeometry, eyeMaterial);
    rightEye.position.set(0.15, 0.15, 0.35);
    animal.add(rightEye);
    
    console.log('Animal eyes added');
}

function createObstacle(position) {
    // Furniture obstacle (chair, shelf, etc.)
    const randomFurniture = Math.random();
    let geometry, width, depth, height, color;
    
    if (randomFurniture < 0.5) {
        // Chair
        width = 1.5;
        depth = 0.8;
        height = 1.2;
        color = 0x8b4513; // Saddle brown
    } else {
        // Shelf
        width = 2;
        depth = 0.5;
        height = 0.6;
        color = 0xa0522d; // Sienna
    }
    
    geometry = new THREE.BoxGeometry(width, height, depth);
    const material = new THREE.MeshLambertMaterial({ color: color });
    const obstacle = new THREE.Mesh(geometry, material);
    
    obstacle.position.set(position.x, height / 2, position.z);
    obstacle.castShadow = true;
    obstacle.receiveShadow = true;
    
    scene.add(obstacle);
    
    return {
        mesh: obstacle,
        width: width,
        depth: depth,
        height: height,
        x: position.x,
        z: position.z,
        speed: CONFIG.obstacleSpeed + (Math.random() - 0.5) * 0.05
    };
}

function createSafeZone(zPosition) {
    // A safe "lily pad" where the player is safe
    const geometry = new THREE.CylinderGeometry(3, 3, 0.3, 32);
    const material = new THREE.MeshLambertMaterial({ color: 0x90ee90 }); // Light green
    const safeZone = new THREE.Mesh(geometry, material);
    
    safeZone.position.set(0, 0.2, zPosition);
    safeZone.receiveShadow = true;
    scene.add(safeZone);
    
    return {
        mesh: safeZone,
        z: zPosition,
        crossed: false
    };
}

// ============= Input Handling =============
window.addEventListener('keydown', (e) => {
    keys[e.key] = true;
    if (e.key === ' ') {
        e.preventDefault();
        if (!gameState.isJumping && gameState.isRunning && !gameState.isPaused) {
            gameState.isJumping = true;
            gameState.jumpVelocity = CONFIG.jumpPower;
        }
    }
});

window.addEventListener('keyup', (e) => {
    keys[e.key] = false;
});

// ============= Game Loop & Update =============

function checkCollisions() {
    // Don't check collisions if jumping - player is in the air!
    if (gameState.isJumping) return;
    
    const animalBounds = {
        x: gameState.animalPosition.x,
        z: gameState.animalPosition.z,
        width: 0.6,
        depth: 0.6
    };
    
    for (let obs of gameState.obstacles) {
        const distance = Math.abs(animalBounds.z - obs.z);
        
        // Collision detection (only when on ground)
        if (distance < 1 && Math.abs(animalBounds.x - obs.x) < (animalBounds.width + obs.width) / 2) {
            // Hit obstacle on ground - drain energy
            gameState.energy = Math.max(0, gameState.energy - 15);
            showCollisionFeedback();
        }
    }
}

function checkSafeZones() {
    for (let safe of gameState.safeZones) {
        if (!safe.crossed && gameState.animalPosition.z > safe.z) {
            safe.crossed = true;
            gameState.safesCrossed++;
            gameState.energy = Math.min(CONFIG.maxEnergy, gameState.energy + 10);
            document.getElementById('safesDisplay').textContent = gameState.safesCrossed;
        }
    }
}

function showCollisionFeedback() {
    // Could add visual or audio feedback here
}

function endGame() {
    gameState.isRunning = false;
    
    // Calculate wellness score (0-500)
    const timeBonus = Math.min(300, gameState.survival_time * 10);
    const energyBonus = (gameState.energy / CONFIG.maxEnergy) * 100;
    const safeBonus = gameState.safesCrossed * 20;
    const wellnessScore = Math.round(timeBonus + energyBonus + safeBonus);
    
    // Show game over screen
    document.getElementById('gameOverScreen').classList.add('show');
    document.getElementById('finalScore').textContent = gameState.survival_time.toFixed(1);
    document.getElementById('finalEnergy').textContent = Math.round(gameState.energy);
    document.getElementById('finalSafes').textContent = gameState.safesCrossed;
    document.getElementById('wellnessScore').textContent = wellnessScore;
    
    // Save to API
    saveGameProgress(wellnessScore);
}

function togglePause() {
    if (!gameState.isRunning) return;
    gameState.isPaused = !gameState.isPaused;
    document.querySelector('.pause-btn').textContent = gameState.isPaused ? '▶ Resume' : '⏸ Pause';
}

function goHome() {
    window.location.href = 'dashboard.php';
}

function onWindowResize() {
    camera.aspect = window.innerWidth / window.innerHeight;
    camera.updateProjectionMatrix();
    renderer.setSize(window.innerWidth, window.innerHeight);
}

// ============= API Integration =============
function saveGameProgress(score) {
    const formData = new FormData();
    formData.append('action', 'save_game_progress');
    formData.append('game_id', 1); // House Adventure game ID
    formData.append('score', Math.round(score));
    formData.append('mood_before', 'calm');
    formData.append('mood_after', gameState.energy > 50 ? 'happy' : 'neutral');
    formData.append('level_reached', gameState.safesCrossed);
    
    fetch('../../src/api.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('Game progress saved:', data.message);
        } else {
            console.error('Error saving game progress:', data.message);
        }
    })
    .catch(error => console.error('API Error:', error));
}

// ============= Initialize =============
try {
    console.log('=== GAME SCRIPT LOADING ===');
    console.log('=== ALL FUNCTIONS DEFINED ===');
    console.log('startGame function exists:', typeof startGame);
    console.log('window.startGame function exists:', typeof window.startGame);
    console.log('gameLoop function exists:', typeof gameLoop);
    console.log('initThreeJS function exists:', typeof initThreeJS);

    // Try to initialize when page loads
    console.log('Attempting DOMContentLoaded setup...');
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOMContentLoaded - page fully loaded');
        try {
            console.log('Calling initThreeJS...');
            initThreeJS();
            console.log('initThreeJS completed successfully');
        } catch(e) {
            console.error('Error in initThreeJS:', e.message, e.stack);
        }
    });
} catch(scriptError) {
    console.error('=== FATAL SCRIPT ERROR ===');
    console.error('Message:', scriptError.message);
    console.error('Stack:', scriptError.stack);
    window.startGame = function() {
        alert('Script Error: ' + scriptError.message + '\n\nCheck console for details.');
    };
}
</script>

<?php // Don't include modals or footer for game page - keep it clean
// include __DIR__ . '/../layouts/modal.php'; 
// include __DIR__ . '/../layouts/footer.php'; 
?>
</body>
</html>
