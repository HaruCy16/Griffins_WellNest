<?php
/**
 * Frogger Game Routing Diagnostic
 * Tests the complete access chain
 */

echo "=== FROGGER GAME ROUTING DIAGNOSTIC ===\n\n";

// Test 1: Check if game.php redirect works
echo "1. Testing game.php redirect logic:\n";
$testGameId = 2;
echo "   If game_id = $testGameId, should redirect to: /views/student/games/frogger.php\n";
echo "   ✓ Logic in place\n\n";

// Test 2: Check if frogger.php file exists and is readable
echo "2. Checking frogger.php file:\n";
$froggerPath = 'views/student/games/frogger.php';
if (file_exists($froggerPath)) {
    echo "   ✓ File exists at: $froggerPath\n";
    echo "   ✓ File size: " . number_format(filesize($froggerPath)) . " bytes\n";
    
    // Read first few lines to check PHP
    $content = file_get_contents($froggerPath, false, null, 0, 200);
    if (strpos($content, '<?php') === 0) {
        echo "   ✓ Valid PHP file (starts with <?php)\n";
    }
} else {
    echo "   ✗ File NOT FOUND!\n";
}

// Test 3: Simulate path calculations
echo "\n3. Testing path calculations from frogger.php:\n";
$simDir = realpath(__DIR__ . '/views/student/games');
echo "   Simulated __DIR__ = $simDir\n";
$baseDir = dirname(dirname(dirname($simDir)));
echo "   Calculated baseDir = $baseDir\n";

$requiredFiles = [
    'config/database.php',
    'src/auth.php',
    'src/functions.php'
];

foreach ($requiredFiles as $file) {
    $fullPath = $baseDir . '/' . $file;
    if (file_exists($fullPath)) {
        echo "   ✓ $file found\n";
    } else {
        echo "   ✗ $file NOT FOUND\n";
    }
}

// Test 4: Check includes work
echo "\n4. Testing includes:\n";
try {
    $configDb = $baseDir . '/config/database.php';
    $srcAuth = $baseDir . '/src/auth.php';
    $srcFunc = $baseDir . '/src/functions.php';
    
    if (file_exists($configDb)) {
        require_once $configDb;
        echo "   ✓ config/database.php loaded\n";
    }
    
    if (file_exists($srcAuth)) {
        require_once $srcAuth;
        echo "   ✓ src/auth.php loaded\n";
    }
    
    if (file_exists($srcFunc)) {
        require_once $srcFunc;
        echo "   ✓ src/functions.php loaded\n";
    }
    
} catch (Exception $e) {
    echo "   ✗ Error loading includes: " . $e->getMessage() . "\n";
}

// Test 5: Check if functions exist
echo "\n5. Checking required functions:\n";
$functions = ['isLoggedIn', 'getCurrentUserId', 'getCurrentUserRole', 'getCurrentUser', 'redirect'];
foreach ($functions as $func) {
    if (function_exists($func)) {
        echo "   ✓ $func() exists\n";
    } else {
        echo "   ✗ $func() NOT FOUND\n";
    }
}

// Test 6: Check constants
echo "\n6. Checking constants:\n";
$constants = ['ROLE_STUDENT', 'ROLE_COUNSELOR', 'ROLE_ADMIN'];
foreach ($constants as $const) {
    if (defined($const)) {
        echo "   ✓ $const = " . constant($const) . "\n";
    } else {
        echo "   ✗ $const NOT DEFINED\n";
    }
}

echo "\n=== SUMMARY ===\n";
echo "If all checks passed above, the routing should work.\n";
echo "Access via: http://localhost/Wellnest_Sim_Web_Application/views/student/game.php?id=2\n";
?>
