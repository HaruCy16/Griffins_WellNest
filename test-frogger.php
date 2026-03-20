<?php
/**
 * Frogger Game Accessibility Test (Simple Direct)
 * Verifies files and database setup without loading other modules
 */

echo "\n====================================================\n";
echo "Frogger Game Setup Verification\n";
echo "====================================================\n\n";

// Test 1: File existence
echo "1. File System:\n";

$files = [
    'views/student/games/frogger.php' => 'Frogger game',
    'views/student/dashboard.php' => 'Dashboard',
    'src/api.php' => 'API handler',
];

$allExist = true;
foreach ($files as $path => $desc) {
    if (file_exists($path)) {
        $size = filesize($path);
        echo "   ✓ $desc (" . number_format($size) . " bytes)\n";
    } else {
        echo "   ✗ $desc (NOT FOUND)\n";
        $allExist = false;
    }
}

// Test 2: Database connection and games
echo "\n2. Database:\n";
try {
    $pdo = new PDO('mysql:host=localhost;dbname=griffin_wellnest_db', 'root', '');
    echo "   ✓ Connected to griffin_wellnest_db\n";
    
    // Get all games
    $stmt = $pdo->query("SELECT game_id, game_name, is_active FROM therapeutic_games");
    $games = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "   ✓ Found " . count($games) . " game(s) total\n";
    
    foreach ($games as $game) {
        if ($game['is_active']) {
            echo "   ✓ ACTIVE: {$game['game_name']} (ID: {$game['game_id']})\n";
        } else {
            echo "   - Inactive: {$game['game_name']} (ID: {$game['game_id']})\n";
        }
    }
    
    // Check tables
    $stmt = $pdo->query("SHOW TABLES LIKE 'game_progress'");
    $table = $stmt->fetch();
    if ($table) {
        echo "   ✓ game_progress table exists\n";
    }
    
} catch (Exception $e) {
    echo "   ✗ Database error: " . $e->getMessage() . "\n";
    $allExist = false;
}

// Test 3: File size sanity check
echo "\n3. File Sizes:\n";
$froggerSize = file_exists('views/student/games/frogger.php') ? filesize('views/student/games/frogger.php') : 0;
if ($froggerSize > 10000) {
    echo "   ✓ Frogger file size reasonable (" . number_format($froggerSize) . " bytes)\n";
} else if ($froggerSize > 0) {
    echo "   ⚠ Frogger file seems small (" . number_format($froggerSize) . " bytes)\n";
}

// Summary
echo "\n====================================================\n";
if ($allExist) {
    echo "✓✓✓ SYSTEM READY! ✓✓✓\n\n";
    echo "HOW TO PLAY FROGGER:\n";
    echo "  1. Visit: http://localhost/Wellnest_Sim_Web_Application/\n";
    echo "  2. Login with student account\n";
    echo "  3. Go to Dashboard\n";
    echo "  4. Click: 🐸 Frogger Challenge\n";
    echo "  5. Select mood → Start Game →\n";
    echo "  6. Controls: Arrow Keys or WASD\n";
    echo "  7. Goal: Reach yellow zone at top\n";
    echo "\nRETURNS TO DASHBOARD: Click 'Back to Dashboard'\n";
} else {
    echo "✗ ISSUES DETECTED\n";
}
echo "====================================================\n\n";
?>

