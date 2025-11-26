<?php

require_once 'Product.php';

// TODO: Create a Cart class
// - Properties: $customerId, $items (array to store cart items) - all private or protected
// - Create a constructor to initialize customerId
// - Cart items should be associative array: [productId => ['product' => Product, 'quantity' => int]]
// - Create addItem(Product $product, $quantity) method
// - Create removeItem($productId) method
// - Create getItems() method - returns array of items
// - Create getTotalPrice() method - sum of (price * quantity) for all items
// - Create getItemCount() method - total number of different products
// - Create clear() method - empty the cart

class Cart
{
    // TODO: Add properties


    // TODO: Create constructor
    public function __construct()
    {

    }

    // TODO: Create addItem() method
    public function addItem($product, $quantity = 1)
    {

    }

    // TODO: Create removeItem() method
    public function removeItem($productId)
    {

    }

    // TODO: Create getItems() method
    public function getItems()
    {

    }

    // TODO: Create getTotalPrice() method
    public function getTotalPrice()
    {

    }

    // TODO: Create getItemCount() method
    public function getItemCount()
    {

    }

    // TODO: Create clear() method
    public function clear()
    {

    }
}
