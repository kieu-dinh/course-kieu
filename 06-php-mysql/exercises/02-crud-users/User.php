<?php

require_once 'Database.php';

// TODO: Create a User class with CRUD operations
// - Property: $pdo (from database connection)
// - Constructor: accepts PDO connection
// - Methods: create(), read(), readAll(), update(), delete()

class User
{
    // TODO: Add properties
    private $pdo;

    // TODO: Create constructor
    public function __construct($pdo)
    {
        // TODO: Store PDO connection
    }

    // TODO: Create create() method
    // Parameters: $name, $email, $phone
    // Returns: lastInsertId() on success, false on failure
    public function create($name, $email, $phone = null)
    {

    }

    // TODO: Create read() method
    // Parameters: $id
    // Returns: user array or null if not found
    public function read($id)
    {

    }

    // TODO: Create readAll() method
    // Returns: array of all users
    public function readAll()
    {

    }

    // TODO: Create update() method
    // Parameters: $id, $data (associative array with fields to update)
    // Returns: true on success, false on failure
    public function update($id, $data)
    {

    }

    // TODO: Create delete() method
    // Parameters: $id
    // Returns: true on success, false on failure
    public function delete($id)
    {

    }
}
