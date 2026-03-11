<?php
/**
 * Database Connection Test
 * Run this file to verify your database setup is correct
 * DELETE THIS FILE after testing!
 */

require_once __DIR__ . '/config/settings.php';

echo "<h1>Wellnest Database Connection Test</h1>";

try {
    // Test connection
    $db = Database::getConnection();
    echo "<p style='color: green;'>✓ Database connection successful!</p>";
    
    // Test if tables exist
    $tables = Database::fetchAll("SHOW TABLES");
    $tableCount = count($tables);
    
    echo "<p>Found <strong>{$tableCount}</strong> tables in database.</p>";
    
    if ($tableCount > 0) {
        echo "<h3>Tables:</h3><ol>";
        $i = 1;
        foreach ($tables as $table) {
            $tableName = array_values($table)[0];
            echo "<li>{$i}. {$tableName}</li>";
            $i++;
        }
        echo "</ol>";
        
        // Also verify with COUNT query
        $countCheck = Database::fetchOne("SELECT COUNT(*) as total FROM information_schema.tables WHERE table_schema = 'griffin_wellnest_db'");
        echo "<p><em>Verification: information_schema shows {$countCheck['total']} tables</em></p>";
    } else {
        echo "<p style='color: orange;'>⚠ No tables found. Please run <code>database/schema.sql</code> in phpMyAdmin.</p>";
    }
    
    // Test roles table if exists
    $rolesExist = Database::fetchOne("SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = 'griffin_wellnest_db' AND table_name = 'roles'");
    
    if ($rolesExist && $rolesExist['count'] > 0) {
        $roles = Database::fetchAll("SELECT * FROM roles");
        if (count($roles) > 0) {
            echo "<h3>Roles:</h3><ul>";
            foreach ($roles as $role) {
                echo "<li>{$role['role_name']}</li>";
            }
            echo "</ul>";
        } else {
            echo "<p style='color: orange;'>⚠ Roles table is empty. Insert default roles.</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Connection failed: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<h3>Troubleshooting:</h3>";
    echo "<ol>";
    echo "<li>Make sure XAMPP MySQL is running</li>";
    echo "<li>Create database <code>wellnest_db</code> in phpMyAdmin</li>";
    echo "<li>Run <code>database/schema.sql</code> to create tables</li>";
    echo "</ol>";
}
