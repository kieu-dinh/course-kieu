<?php

require_once 'Database.php';
require_once 'Category.php';
require_once 'Product.php';

echo "=== Product Catalog System ===\n\n";

try {
    // TODO: Create database connection
    $db = new Database('localhost', 'test_db', 'root', '');
    $pdo = $db->connect();

    // TODO: Create instances
    $category = new Category($pdo);
    $product = new Product($pdo);

    // Create categories
    echo "Categories created...\n";
    $electronicsId = $category->create("Electronics", "Electronic devices and gadgets");
    $sportsId = $category->create("Sports", "Sports equipment and gear");

    // Create products
    echo "\nProducts created and added to catalog...\n\n";

    $product->create("Laptop", "High-performance laptop for professionals", 999.99, $electronicsId, 5);
    $product->create("Mouse", "Wireless mouse with precision tracking", 29.99, $electronicsId, 50);
    $product->create("Running Shoes", "Comfortable running shoes for athletes", 89.99, $sportsId, 15);
    $product->create("Basketball", "Official size basketball for games", 39.99, $sportsId, 20);

    // Get all products
    echo "All Products:\n";
    $allProducts = $product->readAll();
    if ($allProducts) {
        foreach ($allProducts as $p) {
            $stockStatus = $p['stock'] > 0 ? "In Stock" : "Out of Stock";
            echo $p['id'] . ". " . $p['name'] . " - \$" . number_format($p['price'], 2) .
                 " (" . $p['category_name'] . ") - " . $stockStatus . "\n";
        }
    }
    echo "\n";

    // Filter by category
    echo "Products in Electronics:\n";
    $electronicsProducts = $product->filterByCategory($electronicsId);
    if ($electronicsProducts) {
        foreach ($electronicsProducts as $p) {
            echo "- " . $p['name'] . " (Rating: " . number_format($p['rating'], 2) . ")\n";
        }
    }
    echo "\n";

    // Filter by price
    echo "Products by Price (\$30-\$100):\n";
    $priceFiltered = $product->filterByPrice(30, 100);
    if ($priceFiltered) {
        foreach ($priceFiltered as $p) {
            echo "- " . $p['name'] . " - \$" . number_format($p['price'], 2) . "\n";
        }
    }
    echo "\n";

    // Search
    echo "Search Results for \"Laptop\":\n";
    $searchResults = $product->search("Laptop");
    if ($searchResults) {
        foreach ($searchResults as $p) {
            echo "- " . $p['name'] . " - \$" . number_format($p['price'], 2) . "\n";
        }
    }
    echo "\n";

    // Update stock
    echo "Stock Update:\n";
    $product->updateStock(1, -3);
    $laptop = $product->read(1);
    echo "Stock updated to: " . $laptop['stock'] . "\n";

    // Update rating
    $product->updateRating(1, 4.50);
    $product->updateRating(2, 4.20);

} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
