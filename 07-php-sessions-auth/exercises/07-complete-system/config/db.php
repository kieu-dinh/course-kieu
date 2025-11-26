<?php
// TODO: Database configuration
$host = 'localhost';
$dbname = 'auth_system';
$username = 'root';
$password = '';

try {
    // TODO: Create PDO connection


} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
