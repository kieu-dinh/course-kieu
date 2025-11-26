<?php

// TODO: Implement these functions

function add(float $a, float $b): float
{
    // Your code here
}

function subtract(float $a, float $b): float
{
    // Your code here
}

function multiply(float $a, float $b): float
{
    // Your code here
}

function divide(float $a, float $b): ?float
{
    // Your code here
    // Remember to handle division by zero!
}

function calculate(float $a, string $operator, float $b): ?float
{
    // Your code here
    // Use switch or match to call the right function
}

// Tests (don't modify)
echo "5 + 3 = " . calculate(5, "+", 3) . "\n";  // Expected: 8
echo "10 - 4 = " . calculate(10, "-", 4) . "\n"; // Expected: 6
echo "6 * 7 = " . calculate(6, "*", 7) . "\n";   // Expected: 42
echo "20 / 4 = " . calculate(20, "/", 4) . "\n"; // Expected: 5
echo "10 / 0 = " . (calculate(10, "/", 0) ?? "Error") . "\n"; // Expected: Error
