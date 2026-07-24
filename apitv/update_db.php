<?php
/**
 * MovieFlixTV - Database Update Script
 * Run this once to add the 'country' column to the profiles table.
 */

require_once 'config/db.php';
require_once 'utils/response.php';

$db = Database::getConnection();

try {
    // Check if column exists
    $stmt = $db->query("SHOW COLUMNS FROM profiles LIKE 'country'");
    $exists = $stmt->fetch();

    if (!$exists) {
        $db->exec("ALTER TABLE profiles ADD COLUMN country VARCHAR(100) DEFAULT NULL AFTER pin");
        sendSuccess(null, "Column 'country' added to profiles table successfully.");
    } else {
        sendSuccess(null, "Column 'country' already exists in profiles table.");
    }
} catch (PDOException $e) {
    sendError("Database error: " . $e->getMessage(), 500);
}
?>
