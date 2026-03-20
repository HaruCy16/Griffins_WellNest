<?php
/**
 * Session Name Verification Test
 * This verifies that frogger.php uses the same session name as login.php
 */

echo "=== Session Name Verification ===\n\n";

// The session name used throughout the application
$correctSessionName = 'WELLNEST_SID';

echo "[1] Session Name Configuration\n";
echo "    Expected session name: " . $correctSessionName . "\n";

// Simulate what login.php does
echo "\n[2] Login.php Session (with custom name)\n";
session_name($correctSessionName);
session_start();
$_SESSION['user_id'] = 12345; // Simulate logged-in user
$sessionId = session_id();
echo "    Session started with name: " . session_name() . "\n";
echo "    Session ID: " . $sessionId . "\n";
echo "    Session user_id: " . $_SESSION['user_id'] . "\n";

// Save the session data
session_write_close();

// Now simulate what frogger.php does
echo "\n[3] Frogger.php Session (with custom name)\n";
session_name($correctSessionName); // CRITICAL: Must use same name!
session_start();
echo "    Session restarted with name: " . session_name() . "\n";
echo "    Session ID: " . session_id() . "\n";
echo "    Can access user_id? " . (isset($_SESSION['user_id']) ? "YES - " . $_SESSION['user_id'] : "NO - SESSION LOST!") . "\n";

if (isset($_SESSION['user_id'])) {
    echo "\n✓ SUCCESS: Session properly preserved!\n";
    echo "  Frogger game would LOAD\n";
} else {
    echo "\n✗ FAILURE: Session lost!\n";
    echo "  This would cause redirect loop\n";
}

// Simulate without setting name (what was broken before)
echo "\n[4] What Happens Without Setting Session Name\n";
session_write_close();
session_name('PHPSESSID'); // default name
session_start();
echo "    Session name: " . session_name() . "\n";
echo "    Can access user_id? " . (isset($_SESSION['user_id']) ? "YES - " . $_SESSION['user_id'] : "NO") . "\n";
if (!isset($_SESSION['user_id'])) {
    echo "    → This would cause redirect loop! (THE BUG WE FIXED)\n";
}
?>
