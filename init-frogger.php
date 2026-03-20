<?php
/**
 * Initialize Frogger Game in Database
 * Run this once to add the game to the therapeutic_games table
 */

require_once 'config/database.php';

try {
    // Check if Frogger game already exists
    $existing = Database::fetchOne(
        "SELECT game_id FROM therapeutic_games WHERE game_name = ?",
        ['Frogger Challenge']
    );

    if ($existing) {
        echo "✓ Frogger game already exists in database (ID: " . $existing['game_id'] . ")\n";
        exit(0);
    }

    // Insert Frogger game
    $gameId = Database::insert(
        "INSERT INTO therapeutic_games 
        (game_name, game_type, description, target_emotion, difficulty_level, estimated_duration, instructions, benefits, is_active, min_age, max_age)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
        [
            'Frogger Challenge',              // game_name
            'puzzle',                         // game_type
            'A classic frog-crossing game to help students manage stress through focus and strategic thinking.',  // description
            'stress',                         // target_emotion
            'medium',                         // difficulty_level
            10,                               // estimated_duration (in minutes)
            'Help the frog cross safely to the goal. Avoid cars and obstacles. Arrow keys or WASD to move. Reach the yellow zone to complete each level.',  // instructions
            'This game helps build focus, strategic thinking, reflexes, and stress management. The endless gameplay promotes flow state and mindfulness.',  // benefits
            true,                             // is_active
            13,                               // min_age
            18                                // max_age
        ]
    );

    echo "✓ Frogger game successfully added! Game ID: " . $gameId . "\n";

} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
