<?php

// TODO: Create a Database class for connection management
// Copy and adapt from exercise 01 if needed

class Database
{
    // TODO: Add properties for connection details
    private $host;
    private $dbname;
    private $user;
    private $password;
    private $pdo;

    // TODO: Create constructor
    public function __construct($host, $dbname, $user, $password)
    {
        // TODO: Initialize properties
    }

    // TODO: Create connect() method with PDO
    public function connect()
    {
        // TODO: Create and return PDO connection
    }

    // TODO: Create getConnection() method
    public function getConnection()
    {
        // TODO: Return PDO instance
    }
}
