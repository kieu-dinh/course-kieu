# Lesson 06 - Functions

## What is a Function?

A function is reusable code that performs a task:

```php
<?php
// Without function (repeating)
echo "Hello John!\n";
echo "Hello Jane!\n";
echo "Hello Bob!\n";

// With function (reusable)
function greet($name) {
    echo "Hello $name!\n";
}

greet("John");
greet("Jane");
greet("Bob");
```

---

## Defining a Function

```php
<?php
function functionName() {
    // code
}

// Call it
functionName();
```

---

## Parameters

Functions can accept inputs:

```php
<?php
function greet($name) {
    echo "Hello $name!";
}

greet("Kieu");  // Hello Kieu!

// Multiple parameters
function introduce($name, $age) {
    echo "$name is $age years old.";
}

introduce("Kieu", 28);
```

---

## Default Values

```php
<?php
function greet($name = "Guest") {
    echo "Hello $name!";
}

greet();         // Hello Guest!
greet("Kieu");   // Hello Kieu!
```

---

## Return Values

Functions can give back a result:

```php
<?php
function add($a, $b) {
    return $a + $b;
}

$result = add(5, 3);
echo $result;  // 8

// Without return
function sayHello() {
    echo "Hello!";
    // Returns null by default
}
```

---

## Type Hints

Specify expected types:

```php
<?php
function add(int $a, int $b): int {
    return $a + $b;
}

function getName(): string {
    return "Kieu";
}

function getUser(): ?array {  // ? means can be null
    return null;
}
```

---

## Arrow Functions

Short syntax for simple functions:

```php
<?php
// Traditional
$double = function($n) {
    return $n * 2;
};

// Arrow function
$double = fn($n) => $n * 2;

echo $double(5);  // 10

// Useful with array functions
$numbers = [1, 2, 3, 4, 5];
$doubled = array_map(fn($n) => $n * 2, $numbers);
```

---

## Variable Scope

Variables inside functions are local:

```php
<?php
$name = "Kieu";  // Global

function test() {
    $name = "John";  // Local, different variable
    echo $name;      // John
}

test();
echo $name;  // Kieu

// To access global (avoid if possible)
function test2() {
    global $name;
    echo $name;  // Kieu
}
```

---

## Passing by Reference

Modify original variable:

```php
<?php
function addOne(&$number) {  // & = reference
    $number++;
}

$n = 5;
addOne($n);
echo $n;  // 6 (modified!)
```

---

## Common Patterns

### Guard Clause

```php
<?php
function divide($a, $b) {
    if ($b === 0) {
        return null;  // Guard: handle edge case early
    }

    return $a / $b;
}
```

### Pure Function

```php
<?php
// Pure: same input = same output, no side effects
function calculateTax(float $amount, float $rate): float {
    return $amount * $rate;
}

// Not pure: depends on external state
$taxRate = 0.2;
function calculateTax2(float $amount): float {
    global $taxRate;  // Bad!
    return $amount * $taxRate;
}
```

---

## Built-in Functions

PHP has thousands. Some useful ones:

```php
<?php
// String
strlen("hello");        // 5
strtoupper("hello");    // HELLO
trim("  hello  ");      // "hello"
explode(",", "a,b,c");  // ["a", "b", "c"]
implode(",", ["a","b"]); // "a,b"

// Array
count([1,2,3]);         // 3
array_sum([1,2,3]);     // 6
sort($array);           // Sort array

// Math
abs(-5);                // 5
round(3.7);             // 4
max(1, 5, 3);           // 5

// Date
date("Y-m-d");          // 2024-01-15
time();                 // Unix timestamp
```

---

## Practice

```php
<?php
// 1. Create a function that calculates area of rectangle
function rectangleArea(float $width, float $height): float {
    return $width * $height;
}

echo rectangleArea(5, 3);  // 15

// 2. Create a function that checks if number is even
function isEven(int $n): bool {
    return $n % 2 === 0;
}

// 3. Create a function that returns largest number in array
function findMax(array $numbers): int {
    return max($numbers);
}

// 4. Create a function that formats price
function formatPrice(float $price, string $currency = "€"): string {
    return number_format($price, 2) . $currency;
}

echo formatPrice(29.9);      // 29.90€
echo formatPrice(29.9, "$"); // 29.90$

// 5. Create a function that filters adult users
function getAdults(array $users): array {
    return array_filter($users, fn($user) => $user['age'] >= 18);
}
```

---

## Next

[Lesson 07: Classes & Objects](./07-classes.md)
