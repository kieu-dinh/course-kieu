# Lesson 05 - Arrays

## What is an Array?

An array stores multiple values in one variable:

```php
<?php
// Without arrays (bad)
$fruit1 = "apple";
$fruit2 = "banana";
$fruit3 = "orange";

// With array (good)
$fruits = ["apple", "banana", "orange"];
```

---

## Indexed Arrays

Access by number (index starts at 0):

```php
<?php
$fruits = ["apple", "banana", "orange"];

echo $fruits[0];  // apple
echo $fruits[1];  // banana
echo $fruits[2];  // orange

// Change value
$fruits[1] = "mango";

// Add item
$fruits[] = "grape";  // Adds at end
$fruits[10] = "kiwi"; // Adds at index 10

// Count items
echo count($fruits);  // 5
```

---

## Associative Arrays

Access by key (like a dictionary):

```php
<?php
$person = [
    "name" => "Kieu",
    "age" => 28,
    "city" => "Paris"
];

echo $person["name"];  // Kieu
echo $person["age"];   // 28

// Change value
$person["age"] = 29;

// Add new key
$person["email"] = "kieu@example.com";
```

---

## Multi-dimensional Arrays

Arrays inside arrays:

```php
<?php
$users = [
    [
        "name" => "Kieu",
        "age" => 28
    ],
    [
        "name" => "John",
        "age" => 32
    ]
];

echo $users[0]["name"];  // Kieu
echo $users[1]["age"];   // 32
```

---

## Array Functions

### Adding / Removing

```php
<?php
$fruits = ["apple", "banana"];

// Add to end
$fruits[] = "orange";
array_push($fruits, "grape");

// Add to beginning
array_unshift($fruits, "mango");

// Remove from end
$last = array_pop($fruits);

// Remove from beginning
$first = array_shift($fruits);

// Remove specific index
unset($fruits[1]);
```

### Checking

```php
<?php
$fruits = ["apple", "banana", "orange"];

// Check if value exists
if (in_array("banana", $fruits)) {
    echo "Found!";
}

// Check if key exists
$person = ["name" => "Kieu"];
if (array_key_exists("name", $person)) {
    echo "Key exists";
}

// Is it empty?
if (empty($fruits)) {
    echo "Array is empty";
}
```

### Searching

```php
<?php
$fruits = ["apple", "banana", "orange"];

// Find index of value
$index = array_search("banana", $fruits);  // 1

// Get all keys
$keys = array_keys($fruits);  // [0, 1, 2]

// Get all values
$values = array_values($fruits);
```

### Transforming

```php
<?php
$numbers = [3, 1, 4, 1, 5, 9];

// Sort (modifies original)
sort($numbers);           // [1, 1, 3, 4, 5, 9]
rsort($numbers);          // [9, 5, 4, 3, 1, 1] (reverse)

// Sort associative by value
$ages = ["John" => 25, "Jane" => 30];
asort($ages);   // Keep keys, sort by value

// Sort associative by key
ksort($ages);   // Sort by key

// Reverse array
$reversed = array_reverse($numbers);

// Merge arrays
$combined = array_merge($array1, $array2);

// Filter
$evens = array_filter($numbers, fn($n) => $n % 2 === 0);

// Map (transform each)
$doubled = array_map(fn($n) => $n * 2, $numbers);

// Reduce (to single value)
$sum = array_reduce($numbers, fn($carry, $n) => $carry + $n, 0);
```

### Slicing

```php
<?php
$fruits = ["apple", "banana", "orange", "grape", "mango"];

// Get portion
$some = array_slice($fruits, 1, 2);  // ["banana", "orange"]

// Get first 3
$first3 = array_slice($fruits, 0, 3);

// Get last 2
$last2 = array_slice($fruits, -2);
```

---

## Looping Arrays

```php
<?php
$fruits = ["apple", "banana", "orange"];

// Simple loop
foreach ($fruits as $fruit) {
    echo "$fruit\n";
}

// With index
foreach ($fruits as $index => $fruit) {
    echo "$index: $fruit\n";
}

// Associative
$person = ["name" => "Kieu", "age" => 28];

foreach ($person as $key => $value) {
    echo "$key: $value\n";
}
```

---

## Spread Operator

```php
<?php
$fruits = ["apple", "banana"];
$more = ["orange", ...$fruits, "grape"];
// ["orange", "apple", "banana", "grape"]
```

---

## Practice

```php
<?php
// 1. Create shopping list array
$shopping = ["milk", "bread", "eggs"];

// 2. Add "butter" to end
$shopping[] = "butter";

// 3. Add "coffee" to beginning
array_unshift($shopping, "coffee");

// 4. Remove last item
$removed = array_pop($shopping);

// 5. Check if "bread" is in list
if (in_array("bread", $shopping)) {
    echo "Need to buy bread\n";
}

// 6. Print all items
foreach ($shopping as $item) {
    echo "- $item\n";
}

// 7. Create price list (associative)
$prices = [
    "milk" => 2.50,
    "bread" => 1.80,
    "eggs" => 3.00
];

// 8. Calculate total
$total = array_sum($prices);
echo "Total: $total€\n";
```

---

## Next

[Lesson 06: Functions](./06-functions.md)
