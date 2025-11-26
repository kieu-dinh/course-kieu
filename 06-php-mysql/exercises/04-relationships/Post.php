<?php

require_once 'Database.php';

// TODO: Create a Post class
// - Property: $pdo
// - Constructor: accepts PDO connection
// - Methods for JOINs: getPostsWithAuthors(), getPostsWithCommentCount(), getFullPostDetails()

class Post
{
    private $pdo;

    public function __construct($pdo)
    {
        // TODO: Store PDO connection
    }

    // TODO: Create create() method
    // Parameters: $title, $content, $userId
    // Returns: lastInsertId() on success
    public function create($title, $content, $userId)
    {

    }

    // TODO: Create getPostsWithAuthors() method
    // Uses INNER JOIN with users table
    // Returns: array of posts with author names
    public function getPostsWithAuthors()
    {

    }

    // TODO: Create getPostsWithCommentCount() method
    // Uses LEFT JOIN with COUNT
    // Returns: array of posts with comment count
    public function getPostsWithCommentCount()
    {

    }

    // TODO: Create getFullPostDetails() method
    // Parameters: $postId
    // Uses multiple JOINs to get post with author and all comments
    // Returns: post array with related data
    public function getFullPostDetails($postId)
    {

    }

    // TODO: Create read() method
    // Parameters: $id
    // Returns: post array or null
    public function read($id)
    {

    }

    // TODO: Create readAll() method
    // Returns: array of all posts
    public function readAll()
    {

    }
}
