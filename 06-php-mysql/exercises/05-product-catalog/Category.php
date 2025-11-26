<?php

require_once 'Database.php';

// TODO: Create a Category class for product categories
// - Property: $pdo
// - Constructor: accepts PDO connection
// - Methods: create(), read(), readAll(), update(), delete()

class Category
{
    private $pdo;

    public function __construct($pdo)
    {
        // TODO: Store PDO connection
    }

    // TODO: Create create() method
    // Parameters: $name, $description
    // Returns: lastInsertId() on success
    public function create($name, $description = '')
    {

    }

    // TODO: Create read() method
    // Parameters: $id
    // Returns: category array or null
    public function read($id)
    {

    }

    // TODO: Create readAll() method
    // Returns: array of all categories
    public function readAll()
    {

    }

    // TODO: Create update() method
    // Parameters: $id, $data
    // Returns: true on success
    public function update($id, $data)
    {

    }

    // TODO: Create delete() method
    // Parameters: $id
    // Returns: true on success
    public function delete($id)
    {

    }

    // TODO: Helper method to generate slug
    private function generateSlug($name)
    {
        return strtolower(str_replace(' ', '-', $name));
    }
}
