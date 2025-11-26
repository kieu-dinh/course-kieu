<?php

require_once 'Database.php';
require_once 'User.php';

echo "=== User CRUD Operations ===\n\n";

try {
    // TODO: Create database connection
    $db = new Database('localhost', 'test_db', 'root', '');
    $pdo = $db->connect();

    // TODO: Create User instance
    $user = new User($pdo);

    // CREATE - Add new user
    echo "Creating new user...\n";
    $newUserId = $user->create("John Doe", "john@example.com", "555-0123");
    echo "User created with ID: " . $newUserId . "\n\n";

    // READ - Get single user
    echo "Reading user (ID: " . $newUserId . ")...\n";
    $userData = $user->read($newUserId);
    if ($userData) {
        echo "Name: " . $userData['name'] . "\n";
        echo "Email: " . $userData['email'] . "\n";
        echo "Phone: " . $userData['phone'] . "\n\n";
    }

    // UPDATE - Modify user
    echo "Updating user...\n";
    $updateData = ['name' => 'John D.'];
    $user->update($newUserId, $updateData);
    echo "User updated successfully!\n\n";

    // READ - Verify update
    echo "Reading updated user...\n";
    $updatedData = $user->read($newUserId);
    if ($updatedData) {
        echo "Name: " . $updatedData['name'] . "\n";
        echo "Email: " . $updatedData['email'] . "\n\n";
    }

    // READ ALL - List all users
    echo "Listing all users...\n";
    $allUsers = $user->readAll();
    if ($allUsers) {
        foreach ($allUsers as $u) {
            echo $u['id'] . ". " . $u['name'] . " (" . $u['email'] . ")\n";
        }
    }
    echo "\n";

    // DELETE - Remove user
    echo "Deleting user...\n";
    $user->delete($newUserId);
    echo "User deleted successfully!\n\n";

    // READ ALL - Verify deletion
    echo "All users after deletion:\n";
    $allUsers = $user->readAll();
    if (empty($allUsers)) {
        echo "(empty)\n";
    } else {
        foreach ($allUsers as $u) {
            echo $u['id'] . ". " . $u['name'] . " (" . $u['email'] . ")\n";
        }
    }

} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
