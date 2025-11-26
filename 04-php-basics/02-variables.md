# Lesson 02 - Variables & Types

## Data Types

PHP has several data types:

| Type | Example | Description |
|------|---------|-------------|
| String | `"Hello"` | Text |
| Integer | `42` | Whole number |
| Float | `3.14` | Decimal number |
| Boolean | `true` / `false` | Yes or no |
| Array | `[1, 2, 3]` | Collection |
| Null | `null` | No value |

---

## Strings

```php
<?php
$single = 'Hello';           // Single quotes
$double = "Hello";           // Double quotes
$name = "Kieu";
$greeting = "Hello $name";   // Variable interpolation (only double quotes)

// String length
echo strlen("Hello");  // 5

// Uppercase / lowercase
echo strtoupper("hello");  // HELLO
echo strtolower("HELLO");  // hello

// Find text
echo strpos("Hello World", "World");  // 6 (position)

// Replace text
echo str_replace("World", "PHP", "Hello World");  // Hello PHP

// Substring
echo substr("Hello", 0, 3);  // Hel
```

---

## Numbers

```php
<?php
// Integer
$age = 28;

// Float
$price = 19.99;

// Operations
$sum = 10 + 5;       // 15
$diff = 10 - 5;      // 5
$product = 10 * 5;   // 50
$quotient = 10 / 5;  // 2
$remainder = 10 % 3; // 1 (modulo)
$power = 2 ** 3;     // 8 (exponent)

// Shortcuts
$count = 0;
$count++;   // $count is now 1
$count--;   // $count is now 0
$count += 5;  // $count is now 5
$count *= 2;  // $count is now 10

// Useful functions
echo abs(-5);        // 5
echo round(3.7);     // 4
echo floor(3.7);     // 3
echo ceil(3.2);      // 4
echo max(1, 5, 3);   // 5
echo min(1, 5, 3);   // 1
echo rand(1, 100);   // Random number 1-100
```

---

## Booleans

```php
<?php
$isActive = true;
$isDeleted = false;

// Comparison returns boolean
$result = (5 > 3);   // true
$result = (5 == 5);  // true
$result = (5 === "5"); // false (strict: different types)

// Truthy and Falsy
// Falsy: false, 0, "", "0", null, []
// Everything else is truthy

if ($isActive) {
    echo "Active!";
}
```

---

## Null

```php
<?php
$value = null;  // Explicitly no value

// Check if null
if ($value === null) {
    echo "No value";
}

// Check if variable exists and is not null
if (isset($value)) {
    echo "Has value";
}
```

---

## Type Checking

```php
<?php
$name = "Kieu";
$age = 28;

echo gettype($name);  // string
echo gettype($age);   // integer

// Check specific type
var_dump(is_string($name));  // bool(true)
var_dump(is_int($age));      // bool(true)
var_dump(is_array($name));   // bool(false)
```

---

## Type Conversion

```php
<?php
// String to number
$str = "42";
$num = (int) $str;     // 42
$num = (float) "3.14"; // 3.14

// Number to string
$num = 42;
$str = (string) $num;  // "42"

// To boolean
$bool = (bool) 1;      // true
$bool = (bool) 0;      // false
$bool = (bool) "";     // false
$bool = (bool) "hello"; // true
```

---

## Constants

Values that never change:

```php
<?php
define("TAX_RATE", 0.2);
echo TAX_RATE;  // 0.2

// Modern syntax
const MAX_USERS = 100;
echo MAX_USERS;  // 100

// Constants don't use $
```

---

## Practice

```php
<?php
// 1. Create a price as float
$price = 29.99;

// 2. Create quantity as integer
$quantity = 3;

// 3. Calculate total
$total = $price * $quantity;
echo "Total: $total\n";

// 4. Apply 20% discount
$discount = 0.2;
$discounted = $total * (1 - $discount);
echo "After discount: $discounted\n";

// 5. Round to 2 decimals
$final = round($discounted, 2);
echo "Final: $final\n";
```

---

## Next

[Lesson 03: Conditions](./03-conditions.md)
