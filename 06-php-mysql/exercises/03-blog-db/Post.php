<?php

require_once 'Database.php';

// TODO: Create a Post class for blog posts
// - Property: $pdo
// - Constructor: accepts PDO connection
// - Methods: create(), read(), readAll(), readByCategory(), incrementViews(), update(), delete()

class Post
{
    private $pdo;

    public function __construct($pdo)
    {
        // TODO: Store PDO connection
    }

    // TODO: Create create() method
    // Parameters: $title, $content, $categoryId, $author
    // Returns: lastInsertId() on success
    public function create($title, $content, $categoryId, $author)
    {

    }

    // TODO: Create read() method
    // Parameters: $id
    // Returns: post array with category info or null
    public function read($id)
    {

    }

    // TODO: Create readAll() method
    // Returns: array of all published posts with category info
    public function readAll()
    {

    }

    // TODO: Create readByCategory() method
    // Parameters: $categoryId
    // Returns: array of posts in that category
    public function readByCategory($categoryId)
    {

    }

    // TODO: Create incrementViews() method
    // Parameters: $id
    // Returns: new view count or false
    public function incrementViews($id)
    {

    }

    // TODO: Create getPopular() method
    // Parameters: $limit (default 5)
    // Returns: array of most viewed posts
    public function getPopular($limit = 5)
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
}
