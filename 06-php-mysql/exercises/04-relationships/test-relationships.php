<?php

require_once 'Database.php';
require_once 'User.php';
require_once 'Post.php';
require_once 'Comment.php';

echo "=== Database Relationships and JOINs ===\n\n";

try {
    // TODO: Create database connection
    $db = new Database('localhost', 'test_db', 'root', '');
    $pdo = $db->connect();

    // TODO: Create instances
    $user = new User($pdo);
    $post = new Post($pdo);
    $comment = new Comment($pdo);

    echo "Created users, posts, and comments...\n\n";

    // Create users
    $user1Id = $user->create("John Doe", "john@example.com");
    $user2Id = $user->create("Jane Smith", "jane@example.com");
    $user3Id = $user->create("Alice Brown", "alice@example.com");
    $user4Id = $user->create("Bob Wilson", "bob@example.com");

    // Create posts
    $post1Id = $post->create("First Post", "Content of first post", $user1Id);
    $post2Id = $post->create("Second Post", "Content of second post", $user2Id);

    // Add comments
    $comment->create("Great article!", $post1Id, $user3Id);
    $comment->create("Thanks for sharing!", $post1Id, $user4Id);
    $comment->create("Nice insights!", $post2Id, $user3Id);

    // Test INNER JOIN
    echo "Posts with Authors (INNER JOIN):\n";
    $postsWithAuthors = $post->getPostsWithAuthors();
    if ($postsWithAuthors) {
        foreach ($postsWithAuthors as $p) {
            echo $p['id'] . ". " . $p['title'] . " by " . $p['author_name'] . "\n";
        }
    }
    echo "\n";

    // Test LEFT JOIN with COUNT
    echo "Posts with Comment Count (LEFT JOIN):\n";
    $postsWithCounts = $post->getPostsWithCommentCount();
    if ($postsWithCounts) {
        foreach ($postsWithCounts as $p) {
            echo $p['id'] . ". " . $p['title'] . " - " . $p['comment_count'] . " comments\n";
        }
    }
    echo "\n";

    // Test Multiple JOINs
    echo "Full Post Details:\n";
    $fullPost1 = $post->getFullPostDetails($post1Id);
    if ($fullPost1) {
        echo $fullPost1['title'] . " by " . $fullPost1['author_name'] . "\n";
        echo "Comments:\n";
        if (!empty($fullPost1['comments'])) {
            foreach ($fullPost1['comments'] as $c) {
                echo "  - " . $c['content'] . " by " . $c['commenter_name'] . "\n";
            }
        }
    }

    $fullPost2 = $post->getFullPostDetails($post2Id);
    if ($fullPost2) {
        echo "\n" . $fullPost2['title'] . " by " . $fullPost2['author_name'] . "\n";
        echo "Comments:\n";
        if (!empty($fullPost2['comments'])) {
            foreach ($fullPost2['comments'] as $c) {
                echo "  - " . $c['content'] . " by " . $c['commenter_name'] . "\n";
            }
        }
    }

} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
