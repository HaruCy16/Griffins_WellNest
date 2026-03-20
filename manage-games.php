<?php
require_once 'config/database.php';

// Get all games
$games = Database::fetchAll("SELECT * FROM therapeutic_games", []);

echo "Current Games in Database:\n";
echo "==========================\n";
foreach ($games as $game) {
    echo "ID: {$game['game_id']} | Name: {$game['game_name']} | Active: {$game['is_active']}\n";
}

echo "\n\nDisabling all games except Frogger...\n";

// Disable all games except game_id = 2 (Frogger)
Database::query(
    "UPDATE therapeutic_games SET is_active = 0 WHERE game_id != 2",
    []
);

// Make sure Frogger is active
Database::query(
    "UPDATE therapeutic_games SET is_active = 1 WHERE game_id = 2",
    []
);

echo "✓ Updated! Only Frogger (game_id = 2) is now active.\n";

// Show final state
$games = Database::fetchAll("SELECT * FROM therapeutic_games", []);
echo "\n\nFinal State:\n";
echo "============\n";
foreach ($games as $game) {
    echo "ID: {$game['game_id']} | Name: {$game['game_name']} | Active: {$game['is_active']}\n";
}
?>
