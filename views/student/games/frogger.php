<?php
// Set custom session name BEFORE starting session
// This must match what login.php uses
session_name('WELLNEST_SID');
session_start();

// DEBUG: Log session info to file
$debugLog = __DIR__ . '/../../logs/frogger-debug.log';
$debugMsg = date('Y-m-d H:i:s') . " | SESSION_NAME: " . session_name() . " | SESSION_ID: " . session_id() . " | USER_ID: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'NOT SET') . "\n";
@file_put_contents($debugLog, $debugMsg, FILE_APPEND);

// Check if user is logged in (session must have user_id)
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    // Not logged in - redirect to login
    // DEBUG: Log redirect
    @file_put_contents($debugLog, date('Y-m-d H:i:s') . " | REDIRECTING - No user_id in session\n", FILE_APPEND);
    header('Location: /Wellnest_Sim_Web_Application/index.php?reason=session_expired', true, 302);
    exit;
}

// User is logged in, allow game to load
$userId = $_SESSION['user_id'];
// DEBUG: Log success
@file_put_contents($debugLog, date('Y-m-d H:i:s') . " | SUCCESS - GAME LOADING for user: " . $userId . "\n", FILE_APPEND);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wellnest - Frogger Game</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #A37D4A 0%, #6B4E27 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .game-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 20px;
            max-width: 900px;
            width: 100%;
        }

        .game-header {
            text-align: center;
            margin-bottom: 20px;
        }

        .game-header h1 {
            color: #333;
            font-size: 28px;
            margin-bottom: 10px;
        }

        .game-stats {
            display: flex;
            justify-content: space-around;
            gap: 20px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .stat-box {
            background: linear-gradient(135deg, #A37D4A 0%, #6B4E27 100%);
            color: white;
            padding: 15px 25px;
            border-radius: 10px;
            min-width: 150px;
            text-align: center;
        }

        .stat-box label {
            display: block;
            font-size: 12px;
            opacity: 0.9;
            margin-bottom: 5px;
        }

        .stat-box .value {
            font-size: 24px;
            font-weight: bold;
        }

        #gameCanvas {
            display: none;
            margin: 0 auto;
            border: 3px solid #333;
            background: #90ee90;
            cursor: none;
            image-rendering: pixelated;
            -ms-interpolation-mode: nearest-neighbor;
        }

        .game-controls {
            text-align: center;
            margin-top: 20px;
        }

        .controls-help {
            background: #f0f0f0;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 15px;
            font-size: 14px;
            color: #555;
        }

        .controls-help strong {
            display: block;
            color: #333;
            margin-bottom: 8px;
        }

        .button-group {
            display: flex;
            gap: 10px;
            justify-content: center;
            flex-wrap: wrap;
        }

        button {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        #restartBtn {
            background: linear-gradient(135deg, #A37D4A 0%, #6B4E27 100%);
            color: white;
        }

        #restartBtn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(130, 94, 47, 0.4);
        }

        #backBtn {
            background: #ddd;
            color: #333;
        }

        #backBtn:hover {
            background: #ccc;
            transform: translateY(-2px);
        }

        .game-message {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
            display: none;
        }

        .game-message.show {
            display: block;
            animation: slideDown 0.5s ease;
        }

        .game-message.success {
            background: #90EE90;
            color: #006600;
            border: 2px solid #006600;
        }

        .game-message.error {
            background: #FFB6C6;
            color: #990000;
            border: 2px solid #990000;
        }

        @keyframes slideDown {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .mood-section {
            margin-top: 20px;
            padding: 20px;
            background: #f9f9f9;
            border-radius: 10px;
        }

        .mood-section h3 {
            color: #333;
            margin-bottom: 15px;
        }

        .mood-options {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .mood-btn {
            background: white;
            border: 2px solid #ddd;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 14px;
        }

        .mood-btn:hover {
            border-color: #825E2F;
            box-shadow: 0 4px 12px rgba(130, 94, 47, 0.2);
        }

        .mood-btn.selected {
            background: #825E2F;
            color: white;
            border-color: #825E2F;
        }

        /* Game Over Modal */
        .game-over-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .game-over-modal.show {
            display: flex;
        }

        .game-over-panel {
            background: white;
            border-radius: 20px;
            padding: 40px;
            text-align: center;
            max-width: 500px;
            width: 90%;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
            animation: scaleIn 0.3s ease;
        }

        @keyframes scaleIn {
            from {
                transform: scale(0.9);
                opacity: 0;
            }
            to {
                transform: scale(1);
                opacity: 1;
            }
        }

        .game-over-panel h2 {
            color: #825E2F;
            font-size: 32px;
            margin-bottom: 20px;
        }

        .game-over-stats {
            background: linear-gradient(135deg, #A37D4A 0%, #6B4E27 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            font-size: 18px;
        }

        .game-over-stats div {
            margin: 10px 0;
        }

        .game-over-stats .label {
            font-size: 14px;
            opacity: 0.9;
        }

        .game-over-stats .value {
            font-size: 24px;
            font-weight: bold;
        }

        .game-over-buttons {
            display: flex;
            gap: 10px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .game-over-buttons button {
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        #gameOverRestartBtn {
            background: linear-gradient(135deg, #A37D4A 0%, #6B4E27 100%);
            color: white;
        }

        #gameOverRestartBtn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(130, 94, 47, 0.4);
        }

        #gameOverBackBtn {
            background: #ddd;
            color: #333;
        }

        #gameOverBackBtn:hover {
            background: #ccc;
            transform: translateY(-2px);
        }

        @media (max-width: 600px) {
            .game-stats {
                flex-direction: column;
                gap: 10px;
            }

            #gameCanvas {
                max-width: 100%;
                height: auto;
            }
        }
    </style>
</head>
<body>
    <div class="game-container">
        <div class="game-header">
            <h1>🐸 Frogger Challenge</h1>
            <p style="color: #666; font-size: 14px;">Help the frog cross safely to reach the goal!</p>
        </div>

        <div class="game-stats">
            <div class="stat-box">
                <label>Score</label>
                <div class="value" id="scoreDisplay">0</div>
            </div>
            <div class="stat-box">
                <label>Level</label>
                <div class="value" id="levelDisplay">1</div>
            </div>
            <div class="stat-box">
                <label>Lives</label>
                <div class="value" id="livesDisplay">3</div>
            </div>
            <div class="stat-box">
                <label>High Score</label>
                <div class="value" id="highScoreDisplay">0</div>
            </div>
        </div>

        <div class="game-message" id="gameMessage"></div>

        <canvas id="gameCanvas" width="800" height="600"></canvas>

        <!-- Pre-Game Mood Selection (Hidden once game starts) -->
        <div id="preGameScreen" class="mood-section" style="text-align: center;">
            <h3 style="margin-bottom: 20px;">Before we start, how are you feeling right now?</h3>
            <div class="mood-options" style="justify-content: center; margin-bottom: 20px;">
                <button class="mood-btn pre-mood-btn" data-mood="calm">😊 Calm</button>
                <button class="mood-btn pre-mood-btn" data-mood="stressed">😰 Stressed</button>
                <button class="mood-btn pre-mood-btn" data-mood="anxious">😟 Anxious</button>
                <button class="mood-btn pre-mood-btn" data-mood="happy">😄 Happy</button>
                <button class="mood-btn pre-mood-btn" data-mood="sad">😢 Sad</button>
                <button class="mood-btn pre-mood-btn" data-mood="energetic">⚡ Energetic</button>
                <button class="mood-btn pre-mood-btn" data-mood="tired">😴 Tired</button>
            </div>
            <button id="startGameBtn" disabled style="background: #ccc; color: white; padding: 12px 30px; border: none; border-radius: 8px; cursor: not-allowed; font-size: 16px; font-weight: bold;">
                Select Your Mood to Start →
            </button>
        </div>

        <div class="game-controls">
            <div class="controls-help">
                <strong>🎮 CONTROLS:</strong>
                Use <strong>Arrow Keys</strong> or <strong>WASD</strong> to move the frog up, down, left, right
                <br><strong>Goal:</strong> Reach the yellow safe zones at the top while avoiding obstacles!
            </div>

            <div class="button-group">
                <button id="restartBtn">Restart Game</button>
                <button id="backBtn">Back to Dashboard</button>
            </div>
        </div>

        <!-- Mood tracking for after-game questionnaire -->
        <div class="mood-section" id="postGameMoodSection" style="display: none;">
            <h3>After playing, how do you feel now?</h3>
            <div class="mood-options">
                <button class="mood-btn" data-mood="calm">😊 Calm</button>
                <button class="mood-btn" data-mood="stressed">😰 Stressed</button>
                <button class="mood-btn" data-mood="anxious">😟 Anxious</button>
                <button class="mood-btn" data-mood="happy">😄 Happy</button>
                <button class="mood-btn" data-mood="sad">😢 Sad</button>
                <button class="mood-btn" data-mood="energetic">⚡ Energetic</button>
                <button class="mood-btn" data-mood="tired">😴 Tired</button>
            </div>
        </div>
    </div>

    <!-- Game Over Modal -->
    <div class="game-over-modal" id="gameOverModal">
        <div class="game-over-panel">
            <h2>💀 Game Over!</h2>
            <div class="game-over-stats">
                <div>
                    <div class="label">Final Score</div>
                    <div class="value" id="gameOverScore">0</div>
                </div>
                <div>
                    <div class="label">Level Reached</div>
                    <div class="value" id="gameOverLevel">1</div>
                </div>
            </div>
            <div class="game-over-buttons">
                <button id="gameOverRestartBtn">🎮 Restart Game</button>
                <button id="gameOverBackBtn">← Back to Dashboard</button>
            </div>
        </div>
    </div>

    <script>
        // ===== GAME ENGINE =====
        const canvas = document.getElementById('gameCanvas');
        const ctx = canvas.getContext('2d');
        const TILE_SIZE = 40;
        const GRID_WIDTH = canvas.width / TILE_SIZE;
        const GRID_HEIGHT = canvas.height / TILE_SIZE;

        // Game states
        const GAME_STATE = {
            PLAYING: 'playing',
            GAME_OVER: 'gameover',
            LEVEL_COMPLETE: 'levelcomplete'
        };

        // Game variables
        let gameState = GAME_STATE.PLAYING;
        let score = 0;
        let highScore = localStorage.getItem('froggerHighScore') || 0;
        let level = 1;
        let lives = 3;
        let isGameRunning = false; // Changed to false - game starts after mood selection
        let selectedMood = null;
        let moodBefore = null;
        let gameStarted = false;

        // Player object
        const player = {
            x: Math.floor(GRID_WIDTH / 2),
            y: GRID_HEIGHT - 2,
            width: 1,
            height: 1,
            color: '#00aa00'
        };

        // Game objects arrays
        let cars = [];
        let logs = [];
        let obstacles = [];
        
        // Score tracking: track highest row reached for dodge-based scoring
        let highestRowReached = GRID_HEIGHT - 1; // Start at bottom (player spawn)

        // Initialize display
        document.getElementById('highScoreDisplay').textContent = highScore;

        // ===== LANE CONFIGURATION FUNCTION =====
        // Create lanes based on current level for proper difficulty scaling
        function createLanes() {
            return [
                { type: 'goal', speed: 0, color: '#ffdd00', obstacle: 'none' },
                { type: 'grass', speed: 0, color: '#90ee90', obstacle: 'none' },
                { type: 'water', speed: 2 * level, color: '#4ba3ff', obstacle: 'log', direction: 1 },
                { type: 'water', speed: 2.5 * level, color: '#4ba3ff', obstacle: 'log', direction: -1 },
                { type: 'water', speed: 2 * level, color: '#4ba3ff', obstacle: 'log', direction: 1 },
                { type: 'grass', speed: 0, color: '#90ee90', obstacle: 'none' },
                { type: 'road', speed: 3 * level, color: '#666666', obstacle: 'car', direction: 1 },
                { type: 'road', speed: 3.5 * level, color: '#666666', obstacle: 'car', direction: -1 },
                { type: 'road', speed: 2.8 * level, color: '#666666', obstacle: 'car', direction: 1 },
                { type: 'grass', speed: 0, color: '#90ee90', obstacle: 'none' },
                { type: 'road', speed: 3.2 * level, color: '#666666', obstacle: 'car', direction: -1 },
                { type: 'road', speed: 2.5 * level, color: '#666666', obstacle: 'car', direction: 1 },
                { type: 'grass', speed: 0, color: '#90ee90', obstacle: 'none' },
                { type: 'grass', speed: 0, color: '#90ee90', obstacle: 'none' },
                { type: 'grass', speed: 0, color: '#90ee90', obstacle: 'none' }
            ];
        }
        let lanes = createLanes();

        // ===== INITIALIZATION =====
        function initializeGame() {
            cars = [];
            logs = [];

            // Spawn obstacles based on lanes
            for (let y = 0; y < lanes.length; y++) {
                const lane = lanes[y];

                if (lane.obstacle === 'car') {
                    // Spawn cars
                    const carWidth = 2;
                    const spacing = 4;
                    for (let x = 0; x < GRID_WIDTH + carWidth; x += carWidth + spacing) {
                        cars.push({
                            x: x,
                            y: y,
                            width: carWidth,
                            height: 1,
                            speed: lane.speed,
                            direction: lane.direction,
                            color: lane.direction > 0 ? '#ff6600' : '#0066ff'
                        });
                    }
                } else if (lane.obstacle === 'log') {
                    // Spawn logs
                    const logWidth = 3;
                    const spacing = 2;
                    for (let x = 0; x < GRID_WIDTH + logWidth; x += logWidth + spacing) {
                        logs.push({
                            x: x,
                            y: y,
                            width: logWidth,
                            height: 1,
                            speed: lane.speed,
                            direction: lane.direction,
                            color: '#8b4513'
                        });
                    }
                }
            }
        }

        // ===== GAME LOOP =====
        function update() {
            if (!isGameRunning) return;

            // Track row progress for dodge scoring
            if (player.y < highestRowReached) {
                // Player moved to a new highest row - award 5 points per row
                const rowsAdvanced = highestRowReached - player.y;
                score += rowsAdvanced * 5;
                highestRowReached = player.y;
                document.getElementById('scoreDisplay').textContent = score;
            }

            // Update player position based on movement
            const newPlayer = { ...player };

            // Move cars
            cars.forEach(car => {
                car.x += (car.direction * car.speed) * 0.016; // Frame-independent
                if (car.direction > 0 && car.x > GRID_WIDTH) {
                    car.x = -car.width;
                } else if (car.direction < 0 && car.x < -car.width) {
                    car.x = GRID_WIDTH;
                }
            });

            // Move logs
            logs.forEach(log => {
                log.x += (log.direction * log.speed) * 0.016;
                if (log.direction > 0 && log.x > GRID_WIDTH) {
                    log.x = -log.width;
                } else if (log.direction < 0 && log.x < -log.width) {
                    log.x = GRID_WIDTH;
                }
            });

            // Check if player is on water - if so, move with log
            const playerLane = lanes[player.y];
            if (playerLane.obstacle === 'log') {
                const onLog = logs.find(log => isColliding(player, log));
                if (onLog) {
                    player.x += (onLog.direction * onLog.speed) * 0.016;
                } else {
                    // Fell in water!
                    playerDies();
                    return;
                }
            }

            // Check collision with cars
            cars.forEach(car => {
                if (isColliding(player, car)) {
                    playerDies();
                }
            });

            // Check if player reached goal
            if (player.y === 0 && isGameRunning) {
                isGameRunning = false; // CRITICAL: Stop game immediately to prevent glitching
                levelComplete();
            }

            // Keep player in bounds
            if (player.x < 0) player.x = 0;
            if (player.x + player.width > GRID_WIDTH) {
                player.x = GRID_WIDTH - player.width;
            }
            if (player.y < 0) player.y = 0;
            if (player.y >= GRID_HEIGHT) player.y = GRID_HEIGHT - 1;
        }

        function draw() {
            // Draw background lanes
            for (let y = 0; y < lanes.length; y++) {
                const lane = lanes[y];
                ctx.fillStyle = lane.color;
                ctx.fillRect(0, y * TILE_SIZE, canvas.width, TILE_SIZE);
            }

            // Draw grid lines (debugging)
            // ctx.strokeStyle = 'rgba(0, 0, 0, 0.1)';
            // for (let i = 0; i <= GRID_WIDTH; i++) {
            //     ctx.beginPath();
            //     ctx.moveTo(i * TILE_SIZE, 0);
            //     ctx.lineTo(i * TILE_SIZE, canvas.height);
            //     ctx.stroke();
            // }
            // for (let i = 0; i <= GRID_HEIGHT; i++) {
            //     ctx.beginPath();
            //     ctx.moveTo(0, i * TILE_SIZE);
            //     ctx.lineTo(canvas.width, i * TILE_SIZE);
            //     ctx.stroke();
            // }

            // Draw cars
            cars.forEach(car => {
                ctx.fillStyle = car.color;
                ctx.fillRect(
                    car.x * TILE_SIZE + 2,
                    car.y * TILE_SIZE + 5,
                    car.width * TILE_SIZE - 4,
                    TILE_SIZE - 10
                );
                // Draw windows
                ctx.fillStyle = '#333';
                ctx.fillRect(car.x * TILE_SIZE + 5, car.y * TILE_SIZE + 8, 6, 6);
                ctx.fillRect(car.x * TILE_SIZE + 15, car.y * TILE_SIZE + 8, 6, 6);
            });

            // Draw logs
            logs.forEach(log => {
                ctx.fillStyle = log.color;
                ctx.fillRect(
                    log.x * TILE_SIZE,
                    log.y * TILE_SIZE + 8,
                    log.width * TILE_SIZE,
                    TILE_SIZE - 16
                );
                // Add wood texture
                ctx.strokeStyle = '#654321';
                ctx.lineWidth = 1;
                for (let i = 0; i < log.width; i++) {
                    ctx.beginPath();
                    ctx.moveTo((log.x + i) * TILE_SIZE + TILE_SIZE / 2, log.y * TILE_SIZE + 8);
                    ctx.lineTo((log.x + i) * TILE_SIZE + TILE_SIZE / 2, log.y * TILE_SIZE + TILE_SIZE - 8);
                    ctx.stroke();
                }
            });

            // Draw player (frog)
            ctx.fillStyle = player.color;
            ctx.beginPath();
            const frogCenterX = player.x * TILE_SIZE + TILE_SIZE / 2;
            const frogCenterY = player.y * TILE_SIZE + TILE_SIZE / 2;
            const frogSize = TILE_SIZE / 2.5;

            // Body
            ctx.fillRect(
                frogCenterX - frogSize / 2,
                frogCenterY - frogSize / 2,
                frogSize,
                frogSize
            );

            // Eyes
            ctx.fillStyle = '#000';
            ctx.beginPath();
            ctx.arc(frogCenterX - 6, frogCenterY - 4, 2, 0, Math.PI * 2);
            ctx.fill();
            ctx.beginPath();
            ctx.arc(frogCenterX + 6, frogCenterY - 4, 2, 0, Math.PI * 2);
            ctx.fill();

            // Mouth
            ctx.strokeStyle = '#000';
            ctx.lineWidth = 1;
            ctx.beginPath();
            ctx.arc(frogCenterX, frogCenterY + 4, 3, 0, Math.PI);
            ctx.stroke();
        }

        // ===== COLLISION DETECTION =====
        function isColliding(obj1, obj2) {
            return obj1.x < obj2.x + obj2.width &&
                   obj1.x + obj1.width > obj2.x &&
                   obj1.y < obj2.y + obj2.height &&
                   obj1.y + obj1.height > obj2.y;
        }

        // ===== GAME EVENTS =====
        function playerDies() {
            lives--;
            score = Math.max(0, score - 1); // Lose 1 point for collision
            document.getElementById('livesDisplay').textContent = lives;
            document.getElementById('scoreDisplay').textContent = score;

            if (lives <= 0) {
                gameOver();
            } else {
                // Reset player position
                player.x = Math.floor(GRID_WIDTH / 2);
                player.y = GRID_HEIGHT - 2;
                // CRITICAL: Reset all obstacles to starting positions
                initializeGame();
                showMessage('Lost a life! -1 point | ' + lives + ' remaining', 'error', 2000);
            }
        }

        function levelComplete() {
            // Award 25 bonus points for completing the level
            score += 25;
            level++;
            
            if (score > highScore) {
                highScore = score;
                localStorage.setItem('froggerHighScore', highScore);
                document.getElementById('highScoreDisplay').textContent = highScore;
            }

            document.getElementById('scoreDisplay').textContent = score;
            document.getElementById('levelDisplay').textContent = level;

            showMessage('Level ' + (level - 1) + ' Complete! +25 bonus | Next level...', 'success', 2000);

            // Reset for next level - game is already paused
            setTimeout(() => {
                // Recreate lanes with new level difficulty
                lanes = createLanes();
                highestRowReached = GRID_HEIGHT - 1; // Reset row tracking for new level
                player.x = Math.floor(GRID_WIDTH / 2);
                player.y = GRID_HEIGHT - 2;
                initializeGame();
                // Resume game after obstacles are initialized
                isGameRunning = true;
            }, 2000);
        }

        function gameOver() {
            isGameRunning = false;
            gameState = GAME_STATE.GAME_OVER;
            
            // Update and show game over modal
            document.getElementById('gameOverScore').textContent = score;
            document.getElementById('gameOverLevel').textContent = level;
            document.getElementById('gameOverModal').classList.add('show');
            
            // Save score to database
            saveGameScore();
        }

        function showMessage(text, type, duration = 0) {
            const msgEl = document.getElementById('gameMessage');
            msgEl.textContent = text;
            msgEl.className = 'game-message show ' + type;

            if (duration > 0) {
                setTimeout(() => {
                    msgEl.classList.remove('show');
                }, duration);
            }
        }

        // ===== KEYBOARD CONTROLS =====
        const keys = {};
        window.addEventListener('keydown', (e) => {
            // Only allow movement if game is running
            if (!isGameRunning) return;
            
            keys[e.key] = true;

            // Handle arrow keys and WASD
            if (['ArrowUp', 'w', 'W'].includes(e.key)) {
                if (player.y > 0) player.y--;
                e.preventDefault();
            }
            if (['ArrowDown', 's', 'S'].includes(e.key)) {
                if (player.y < GRID_HEIGHT - 1) player.y++;
                e.preventDefault();
            }
            if (['ArrowLeft', 'a', 'A'].includes(e.key)) {
                if (player.x > 0) player.x--;
                e.preventDefault();
            }
            if (['ArrowRight', 'd', 'D'].includes(e.key)) {
                if (player.x < GRID_WIDTH - 1) player.x++;
                e.preventDefault();
            }
        });

        // ===== DATABASE INTEGRATION =====
        function saveGameScore() {
            const gameId = 2; // Frogger game ID (from database)
            const moodAfter = selectedMood || 'neutral';

            const formData = new FormData();
            formData.append('action', 'save_game_progress');
            formData.append('game_id', gameId);
            formData.append('score', score);
            formData.append('level_reached', level);
            formData.append('mood_before', moodBefore || 'neutral');
            formData.append('mood_after', moodAfter);

            // Use absolute path without leading slash for better compatibility
            const apiUrl = window.location.origin + '/Wellnest_Sim_Web_Application/src/api.php';

            fetch(apiUrl, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                console.log('Score saved:', data);
            })
            .catch(error => console.error('Error saving score:', error));
        }

        // ===== UI BUTTON HANDLERS =====
        // Pre-game mood selection
        document.querySelectorAll('.pre-mood-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.pre-mood-btn').forEach(b => b.classList.remove('selected'));
                btn.classList.add('selected');
                moodBefore = btn.dataset.mood;
                
                // Enable start button
                const startBtn = document.getElementById('startGameBtn');
                startBtn.disabled = false;
                startBtn.style.background = 'linear-gradient(135deg, #A37D4A 0%, #6B4E27 100%)';
                startBtn.style.cursor = 'pointer';
                startBtn.textContent = 'Start Game →';
            });
        });

        // Start game button
        document.getElementById('startGameBtn').addEventListener('click', () => {
            if (moodBefore) {
                // Hide pre-game screen
                document.getElementById('preGameScreen').style.display = 'none';
                // Show canvas
                canvas.style.display = 'block';
                // Start the game
                gameStarted = true;
                isGameRunning = true;
                gameLoop();
            }
        });

        function resetGameState() {
            score = 0;
            level = 1; // CRITICAL: Reset level to 1
            lives = 3;
            highestRowReached = GRID_HEIGHT - 1; // Reset row tracking
            gameState = GAME_STATE.PLAYING;
            isGameRunning = false;
            gameStarted = false;
            selectedMood = null;
            moodBefore = null;
            player.x = Math.floor(GRID_WIDTH / 2);
            player.y = GRID_HEIGHT - 2;

            // CRITICAL: Recreate lanes AFTER level is reset to 1
            lanes = createLanes();

            document.getElementById('scoreDisplay').textContent = score;
            document.getElementById('levelDisplay').textContent = level;
            document.getElementById('livesDisplay').textContent = lives;
            document.getElementById('gameMessage').classList.remove('show');

            // Hide game over modal if shown
            document.getElementById('gameOverModal').classList.remove('show');

            // Show pre-game screen
            document.getElementById('preGameScreen').style.display = 'block';
            canvas.style.display = 'none';

            // Clear all mood selections
            document.querySelectorAll('.mood-btn').forEach(b => b.classList.remove('selected'));
            const startBtn = document.getElementById('startGameBtn');
            startBtn.disabled = true;
            startBtn.style.background = '#ccc';
            startBtn.style.cursor = 'not-allowed';
            startBtn.textContent = 'Select Your Mood to Start →';

            // CRITICAL: Initialize game with level 1 lanes
            initializeGame();
        }

        document.getElementById('restartBtn').addEventListener('click', resetGameState);

        document.getElementById('backBtn').addEventListener('click', () => {
            if (isGameRunning) {
                isGameRunning = false;
            }
            // Navigate to dashboard
            const dashboardUrl = window.location.origin + '/Wellnest_Sim_Web_Application/views/student/dashboard.php';
            window.location.href = dashboardUrl;
        });

        // Post-game mood selection (bottom of screen after game)
        document.querySelectorAll('.mood-btn:not(.pre-mood-btn)').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.mood-btn:not(.pre-mood-btn)').forEach(b => b.classList.remove('selected'));
                btn.classList.add('selected');
                selectedMood = btn.dataset.mood;
            });
        });

        // Game Over Modal Buttons
        document.getElementById('gameOverRestartBtn').addEventListener('click', resetGameState);
        
        document.getElementById('gameOverBackBtn').addEventListener('click', () => {
            isGameRunning = false;
            document.getElementById('gameOverModal').classList.remove('show');
            const dashboardUrl = window.location.origin + '/Wellnest_Sim_Web_Application/views/student/dashboard.php';
            window.location.href = dashboardUrl;
        });

        // ===== MAIN GAME LOOP =====
        let lastTime = 0;
        function gameLoop(currentTime) {
            update();
            draw();
            requestAnimationFrame(gameLoop);
        }

        // Initialize everything
        initializeGame();
    </script>
</body>
</html>
