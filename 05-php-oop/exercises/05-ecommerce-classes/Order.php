<?php

require_once 'Cart.php';

// TODO: Create an Order class
// - Properties: $id, $customerId, $items (array), $totalPrice, $orderDate, $status (all private or protected)
// - Create a constructor to initialize properties
// - Create static method createFromCart($cart, $orderId) - creates order from cart
// - Create getOrderInfo() method - returns formatted order information
// - Create changeStatus($newStatus) method
// - Create getItems() method - returns items
// - Create getTotal() method - returns total price
// - Order status options: Pending, Processing, Shipped, Delivered, Cancelled

class Order
{
    // TODO: Add properties


    // TODO: Create constructor
    public function __construct()
    {

    }

    // TODO: Create static createFromCart() method
    public static function createFromCart($cart, $orderId)
    {

    }

    // TODO: Create getOrderInfo() method
    public function getOrderInfo()
    {

    }

    // TODO: Create changeStatus() method
    public function changeStatus($newStatus)
    {

    }

    // TODO: Create getItems() method
    public function getItems()
    {

    }

    // TODO: Create getTotal() method
    public function getTotal()
    {

    }

    // TODO: Create getter methods
    public function getId()
    {
        return $this->id;
    }

    public function getCustomerId()
    {
        return $this->customerId;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getOrderDate()
    {
        return $this->orderDate;
    }
}


// Test your classes (don't modify this part)
require_once 'Product.php';
require_once 'Cart.php';

echo "=== E-Commerce System ===\n\n";

// Create products
$laptop = new Product(1, "Laptop", 999.99, "High-performance laptop", 5);
$mouse = new Product(2, "Mouse", 29.99, "Wireless mouse", 50);
$keyboard = new Product(3, "Keyboard", 79.99, "Mechanical keyboard", 20);

echo "Adding products to cart:\n";

// Create cart and add items
$cart = new Cart(101);
$cart->addItem($laptop, 1);
echo "- Laptop (\$999.99) x 1 = \$999.99\n";

$cart->addItem($mouse, 2);
echo "- Mouse (\$29.99) x 2 = \$59.98\n";

echo "\nCart Total: \$" . number_format($cart->getTotalPrice(), 2) . "\n";
echo "Items in cart: " . $cart->getItemCount() . "\n\n";

echo "Creating order from cart:\n";
$order = Order::createFromCart($cart, 1001);
echo "Order #1001 created successfully!\n";
echo "Status: " . $order->getStatus() . "\n\n";

echo "Order Details:\n";
echo "Order ID: " . $order->getId() . "\n";
echo "Customer ID: " . $order->getCustomerId() . "\n";
echo "Items: " . count($order->getItems()) . "\n";
echo "Total: \$" . number_format($order->getTotal(), 2) . "\n";
