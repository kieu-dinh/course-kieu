# Exercise 01 - Calculator

## Objective

Build a command-line calculator using functions.

---

## Task

Create `calculator.php` with functions for basic math operations.

### Requirements

1. Function `add($a, $b)` - returns sum
2. Function `subtract($a, $b)` - returns difference
3. Function `multiply($a, $b)` - returns product
4. Function `divide($a, $b)` - returns quotient (handle division by zero!)
5. Function `calculate($a, $operator, $b)` - uses the right function based on operator

---

## Starter Code

```php
<?php

function add(float $a, float $b): float {
    // Your code
}

function subtract(float $a, float $b): float {
    // Your code
}

function multiply(float $a, float $b): float {
    // Your code
}

function divide(float $a, float $b): ?float {
    // Your code (return null if dividing by zero)
}

function calculate(float $a, string $operator, float $b): ?float {
    // Your code
    // Use switch or match on $operator (+, -, *, /)
}

// Tests
echo "5 + 3 = " . calculate(5, "+", 3) . "\n";  // 8
echo "10 - 4 = " . calculate(10, "-", 4) . "\n"; // 6
echo "6 * 7 = " . calculate(6, "*", 7) . "\n";   // 42
echo "20 / 4 = " . calculate(20, "/", 4) . "\n"; // 5
echo "10 / 0 = " . (calculate(10, "/", 0) ?? "Error") . "\n"; // Error
```

---

## Run

```bash
php calculator.php
```

---

## Bonus Challenges

1. Add `power($base, $exponent)` function
2. Add `modulo($a, $b)` function
3. Add `squareRoot($n)` function
4. Handle invalid operators in `calculate()`

---

## Checklist

- [ ] All 4 basic operations work
- [ ] Division by zero returns null (not error)
- [ ] `calculate()` works with all operators
- [ ] Code is clean and readable

---

## Solution Check

Run your tests. Expected output:
```
5 + 3 = 8
10 - 4 = 6
6 * 7 = 42
20 / 4 = 5
10 / 0 = Error
```
