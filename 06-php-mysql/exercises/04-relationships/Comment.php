<?php

require_once 'Database.php';

// TODO: Create a Comment class
// - Property: $pdo
// - Constructor: accepts PDO connection
// - Methods: create(), getCommentsByPost(), getCommentsByPostWithAuthors()

class Comment
{
    private $pdo;

    public function __construct($pdo)
    {
        // TODO: Store PDO connection
    }

    // TODO: Create create() method
    // Parameters: $content, $postId, $userId
    // Returns: lastInsertId() on success
    public function create($content, $postId, $userId)
    {

    }

    // TODO: Create getCommentsByPost() method
    // Parameters: $postId
    // Returns: array of comments for post
    public function getCommentsByPost($postId)
    {

    }

    // TODO: Create getCommentsByPostWithAuthors() method
    // Parameters: $postId
    // Uses JOIN with users table
    // Returns: array of comments with author info
    public function getCommentsByPostWithAuthors($postId)
    {

    }
}
