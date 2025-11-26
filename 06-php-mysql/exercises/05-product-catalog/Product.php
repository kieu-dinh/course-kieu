<?php

require_once 'Database.php';

// TODO: Create a Product class for product management
// - Property: $pdo
// - Constructor: accepts PDO connection
// - Methods: create(), read(), readAll(), search(), filterByCategory(), filterByPrice(),
//           updateStock(), updateRating(), delete()

class Product
{
    private $pdo;

    public function __construct($pdo)
    {
        // TODO: Store PDO connection
    }

    // TODO: Create create() method
    // Parameters: $name, $description, $price, $categoryId, $stock
    // Returns: lastInsertId() on success
    public function create($name, $description, $price, $categoryId, $stock = 0)
    {

    }

    // TODO: Create read() method
    // Parameters: $id
    // Returns: product array with category info or null
    public function read($id)
    {

    }

    // TODO: Create readAll() method
    // Returns: array of all products with category info
    public function readAll()
    {

    }

    // TODO: Create search() method
    // Parameters: $keyword
    // Returns: array of matching products
    public function search($keyword)
    {

    }

    // TODO: Create filterByCategory() method
    // Parameters: $categoryId
    // Returns: array of products in category
    public function filterByCategory($categoryId)
    {

    }

    // TODO: Create filterByPrice() method
    // Parameters: $minPrice, $maxPrice
    // Returns: array of products in price range
    public function filterByPrice($minPrice, $maxPrice)
    {

    }

    // TODO: Create filterByStock() method
    // Parameters: $inStock (boolean)
    // Returns: array of products with stock/no stock
    public function filterByStock($inStock = true)
    {

    }

    // TODO: Create updateStock() method
    // Parameters: $id, $quantity (positive to add, negative to reduce)
    // Returns: new stock count or false if insufficient
    public function updateStock($id, $quantity)
    {

    }

    // TODO: Create updateRating() method
    // Parameters: $id, $rating (0-5)
    // Returns: true on success
    public function updateRating($id, $rating)
    {

    }

    // TODO: Create delete() method
    // Parameters: $id
    // Returns: true on success
    public function delete($id)
    {

    }

    // TODO: Helper method to check if in stock
    private function isInStock($stock)
    {
        return $stock > 0;
    }
}
