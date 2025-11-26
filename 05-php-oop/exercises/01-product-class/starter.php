<?php

// TODO: Create a Product class

class Product
{
    // TODO: Add private properties for name, price, stock


    // TODO: Create a constructor
    public function __construct()
    {

    }

    // TODO: Create a display() method
    public function display()
    {

    }

    // TODO: Create isAvailable() method
    public function isAvailable()
    {

    }

    // TODO: Create buy($quantity) method
    public function buy($quantity)
    {

    }
}


// Test your class (don't modify this part)
$laptop = new Product("Laptop", 999.99, 10);
$laptop->display();
echo "Available: " . ($laptop->isAvailable() ? "Yes" : "No") . "\n\n";

echo "Buying 3 units...\n";
$laptop->buy(3);
$laptop->display();
