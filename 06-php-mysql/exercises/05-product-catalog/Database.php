<?php

// TODO: Create Database class
// Adapt from previous exercises

class Database
{
    private $host;
    private $dbname;
    private $user;
    private $password;
    private $pdo;

    public function __construct($host, $dbname, $user, $password)
    {
        // TODO: Initialize properties
    }

    public function connect()
    {
        // TODO: Create and return PDO connection
    }

    public function getConnection()
    {
        // TODO: Return PDO instance
    }
}
