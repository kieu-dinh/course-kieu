<?php

require_once 'Vehicle.php';

// TODO: Create a Bike class that extends Vehicle
// - Add property: $hasStorage (private or protected, boolean)
// - Create a constructor that calls parent constructor
// - Override getInfo() to include storage information

class Bike extends Vehicle
{
    // TODO: Add $hasStorage property


    // TODO: Create constructor
    public function __construct()
    {

    }

    // TODO: Override getInfo() method to include storage
    public function getInfo()
    {

    }
}


// Test your classes (don't modify this part)
require_once 'Car.php';
require_once 'Bike.php';

echo "=== Car ===\n";
$car = new Car("Toyota", "Camry", 2023, 4);
echo $car->getInfo();
echo "Speed: " . "0 km/h\n\n";

echo "After accelerating:\n";
$car->accelerate(100);
echo "Speed: " . "100 km/h\n\n";

echo "After braking:\n";
$car->brake(50);
echo "Speed: " . "50 km/h\n\n";

echo "=== Bike ===\n";
$bike = new Bike("Harley-Davidson", "Sportster", 2022, true);
echo $bike->getInfo();
echo "Speed: " . "0 km/h\n\n";

echo "After accelerating:\n";
$bike->accelerate(120);
echo "Speed: " . "120 km/h\n";
