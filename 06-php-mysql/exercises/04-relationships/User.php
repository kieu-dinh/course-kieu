<?php

require_once 'Database.php';

// TODO: Create a User class
// - Property: $pdo
// - Constructor: accepts PDO connection
// - Methods: create(), read(), readAll()

class User
{
    private $pdo;

    public function __construct($pdo)
    {
        // TODO: Store PDO connection
    }

    // TODO: Create create() method
    // Parameters: $name, $email
    // Returns: lastInsertId() on success
    public function create($name, $email)
    {

    }

    // TODO: Create read() method
    // Parameters: $id
    // Returns: user array or null
    public function read($id)
    {

    }

    // TODO: Create readAll() method
    // Returns: array of all users
    public function readAll()
    {

    }
}
