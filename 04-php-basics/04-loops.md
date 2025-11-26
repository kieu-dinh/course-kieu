# Lesson 04 - Loops

## The Concept

Loops repeat code multiple times:

```
Repeat 5 times:
    Print "Hello"
```

---

## For Loop

When you know how many times to loop:

```php
<?php
for ($i = 0; $i < 5; $i++) {
    echo "Count: $i\n";
}
// Output: 0, 1, 2, 3, 4
```

Structure:
```php
for (start; condition; increment) {
    // code
}
```

### Examples

```php
<?php
// Count 1 to 10
for ($i = 1; $i <= 10; $i++) {
    echo "$i ";
}
// 1 2 3 4 5 6 7 8 9 10

// Count down
for ($i = 5; $i >= 1; $i--) {
    echo "$i ";
}
// 5 4 3 2 1

// Step by 2
for ($i = 0; $i <= 10; $i += 2) {
    echo "$i ";
}
// 0 2 4 6 8 10
```

---

## While Loop

When you don't know how many times:

```php
<?php
$count = 0;

while ($count < 5) {
    echo "Count: $count\n";
    $count++;
}
```

**Be careful:** If condition never becomes false = infinite loop!

---

## Do-While Loop

Always runs at least once:

```php
<?php
$count = 0;

do {
    echo "Count: $count\n";
    $count++;
} while ($count < 5);
```

---

## Foreach Loop

Best for arrays:

```php
<?php
$fruits = ["apple", "banana", "orange"];

foreach ($fruits as $fruit) {
    echo "$fruit\n";
}
// apple
// banana
// orange
```

With key:
```php
<?php
$prices = [
    "apple" => 1.5,
    "banana" => 0.8,
    "orange" => 2.0
];

foreach ($prices as $fruit => $price) {
    echo "$fruit: $price€\n";
}
// apple: 1.5€
// banana: 0.8€
// orange: 2.0€
```

---

## Break and Continue

### Break: Exit loop early

```php
<?php
for ($i = 1; $i <= 10; $i++) {
    if ($i === 5) {
        break;  // Stop at 5
    }
    echo "$i ";
}
// 1 2 3 4
```

### Continue: Skip to next iteration

```php
<?php
for ($i = 1; $i <= 5; $i++) {
    if ($i === 3) {
        continue;  // Skip 3
    }
    echo "$i ";
}
// 1 2 4 5
```

---

## Nested Loops

Loop inside a loop:

```php
<?php
// Multiplication table
for ($i = 1; $i <= 3; $i++) {
    for ($j = 1; $j <= 3; $j++) {
        $result = $i * $j;
        echo "$i x $j = $result\n";
    }
    echo "---\n";
}
```

---

## Common Patterns

### Sum of numbers

```php
<?php
$sum = 0;
for ($i = 1; $i <= 10; $i++) {
    $sum += $i;
}
echo "Sum: $sum";  // 55
```

### Find in array

```php
<?php
$numbers = [4, 8, 15, 16, 23, 42];
$search = 15;
$found = false;

foreach ($numbers as $num) {
    if ($num === $search) {
        $found = true;
        break;
    }
}

echo $found ? "Found!" : "Not found";
```

### Filter array

```php
<?php
$numbers = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
$evens = [];

foreach ($numbers as $num) {
    if ($num % 2 === 0) {
        $evens[] = $num;
    }
}

print_r($evens);  // [2, 4, 6, 8, 10]
```

---

## Practice

```php
<?php
// 1. Print numbers 1 to 20
for ($i = 1; $i <= 20; $i++) {
    echo "$i ";
}
echo "\n";

// 2. Sum all even numbers from 1 to 100
$sum = 0;
for ($i = 2; $i <= 100; $i += 2) {
    $sum += $i;
}
echo "Sum of evens: $sum\n";

// 3. Print a triangle
//    *
//    **
//    ***
//    ****
//    *****
for ($i = 1; $i <= 5; $i++) {
    echo str_repeat("*", $i) . "\n";
}

// 4. FizzBuzz: 1 to 20
// - Multiple of 3: "Fizz"
// - Multiple of 5: "Buzz"
// - Multiple of both: "FizzBuzz"
// - Otherwise: the number
for ($i = 1; $i <= 20; $i++) {
    if ($i % 3 === 0 && $i % 5 === 0) {
        echo "FizzBuzz ";
    } elseif ($i % 3 === 0) {
        echo "Fizz ";
    } elseif ($i % 5 === 0) {
        echo "Buzz ";
    } else {
        echo "$i ";
    }
}
```

---

## Next

[Lesson 05: Arrays](./05-arrays.md)
