<?php

require_once 'Database.php';

// Test database connection
echo "Database Connection Test\n";
echo "========================\n\n";

echo "Attempting to connect to MySQL database...\n";

try {
    // TODO: Create Database instance with your credentials
    // For testing, you can use:
    // - host: localhost
    // - dbname: test_db (or any existing database)
    // - user: root
    // - password: (your password)

    $database = new Database(
        'localhost',      // host
        'test_db',       // database name
        'root',          // username
        ''               // password
    );

    $pdo = $database->connect();
    echo "Connection successful!\n\n";

    // Test query to verify connection
    $stmt = $pdo->query("SELECT VERSION() as version");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    echo "Database: MySQL\n";
    echo "Version: " . $result['version'] . "\n\n";

    echo "Executing test query...\n";
    $stmt = $pdo->query("SELECT 1 as status");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Test query completed successfully!\n\n";

    echo "Connection test passed!\n";

} catch (PDOException $e) {
    echo "Connection failed!\n";
    echo "Error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "Unexpected error!\n";
    echo "Error: " . $e->getMessage() . "\n";
}
