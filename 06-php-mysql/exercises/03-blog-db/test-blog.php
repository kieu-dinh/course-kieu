<?php

require_once 'Database.php';
require_once 'Category.php';
require_once 'Post.php';

echo "=== Blog System ===\n\n";

try {
    // TODO: Create database connection
    $db = new Database('localhost', 'test_db', 'root', '');
    $pdo = $db->connect();

    // TODO: Create instances
    $category = new Category($pdo);
    $post = new Post($pdo);

    // Create categories
    echo "Creating categories...\n";
    $techCatId = $category->create("Technology", "Tech articles and tutorials", "technology");
    echo "Category created: Technology (ID: " . $techCatId . ")\n";

    $lifeCatId = $category->create("Lifestyle", "Lifestyle tips and advice", "lifestyle");
    echo "Category created: Lifestyle (ID: " . $lifeCatId . ")\n\n";

    // Create posts
    echo "Creating posts...\n";
    $post1Id = $post->create(
        "PHP Tips and Tricks",
        "Here are some useful PHP tips...",
        $techCatId,
        "Author"
    );
    echo "Post created: PHP Tips and Tricks (ID: " . $post1Id . ")\n";

    $post2Id = $post->create(
        "Healthy Living Guide",
        "Tips for a healthier lifestyle...",
        $lifeCatId,
        "Author"
    );
    echo "Post created: Healthy Living Guide (ID: " . $post2Id . ")\n\n";

    // Fetch all posts
    echo "Fetching all posts...\n";
    $allPosts = $post->readAll();
    if ($allPosts) {
        foreach ($allPosts as $p) {
            echo $p['id'] . ". " . $p['title'] . " by " . $p['author'] . " (" . $p['views'] . " views)\n";
            echo "   Category: " . $p['category_name'] . "\n\n";
        }
    }

    // Fetch posts by category
    echo "Fetching posts by category (Technology)...\n";
    $techPosts = $post->readByCategory($techCatId);
    if ($techPosts) {
        foreach ($techPosts as $p) {
            echo "- " . $p['title'] . "\n";
        }
    }
    echo "\n";

    // Increment views
    echo "Incrementing views...\n";
    $post->incrementViews($post1Id);
    $post->incrementViews($post1Id);
    $post->incrementViews($post1Id);
    $post->incrementViews($post1Id);
    $post->incrementViews($post1Id);

    $updatedPost = $post->read($post1Id);
    echo "Post views updated to: " . $updatedPost['views'] . "\n";

} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
