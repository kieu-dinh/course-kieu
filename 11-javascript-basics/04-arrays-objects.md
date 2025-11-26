# Lesson 04 - Arrays and Objects

**Duration**: 3-4 hours
**Prerequisites**: Lesson 03 - Functions and Scope

---

## Arrays in JavaScript

Arrays in JavaScript are similar to PHP arrays, but with powerful built-in methods.

### Creating Arrays

```javascript
// Empty array
let fruits = [];

// Array with values
let fruits = ["apple", "banana", "cherry"];

// Mixed types (JavaScript allows this)
let mixed = [1, "hello", true, null];

// Using Array constructor (less common)
let numbers = new Array(1, 2, 3);
```

**Compare to PHP:**
```php
<?php
$fruits = [];
$fruits = ["apple", "banana", "cherry"];
$mixed = [1, "hello", true, null]; // Also allowed in PHP
?>
```

**Same concept!**

### Accessing Array Elements

```javascript
let fruits = ["apple", "banana", "cherry"];

console.log(fruits[0]);  // "apple"
console.log(fruits[1]);  // "banana"
console.log(fruits[2]);  // "cherry"
console.log(fruits[3]);  // undefined (doesn't exist)

// Get array length
console.log(fruits.length); // 3

// Get last element
console.log(fruits[fruits.length - 1]); // "cherry"
```

**Exactly like PHP!**

### Modifying Arrays

```javascript
let fruits = ["apple", "banana"];

// Change element
fruits[0] = "orange";
console.log(fruits); // ["orange", "banana"]

// Add element at end
fruits.push("cherry");
console.log(fruits); // ["orange", "banana", "cherry"]

// Add element at beginning
fruits.unshift("mango");
console.log(fruits); // ["mango", "orange", "banana", "cherry"]

// Remove last element
let last = fruits.pop();
console.log(last);   // "cherry"
console.log(fruits); // ["mango", "orange", "banana"]

// Remove first element
let first = fruits.shift();
console.log(first);  // "mango"
console.log(fruits); // ["orange", "banana"]
```

**Compare to PHP:**
```php
<?php
$fruits = ["apple", "banana"];

// Change element
$fruits[0] = "orange";

// Add element at end
array_push($fruits, "cherry");
// Or: $fruits[] = "cherry";

// Remove last element
$last = array_pop($fruits);

// Remove first element
$first = array_shift($fruits);

// Add element at beginning
array_unshift($fruits, "mango");
?>
```

**Very similar, but JavaScript methods are cleaner!**

---

## Essential Array Methods

### 1. push() and pop() - End of Array

```javascript
let stack = [];

stack.push("first");      // ["first"]
stack.push("second");     // ["first", "second"]
stack.push("third");      // ["first", "second", "third"]

let last = stack.pop();   // "third"
console.log(stack);       // ["first", "second"]
```

**Use case:** Stack data structure (LIFO - Last In, First Out)

### 2. unshift() and shift() - Beginning of Array

```javascript
let queue = [];

queue.push("first");      // ["first"]
queue.push("second");     // ["first", "second"]
queue.push("third");      // ["first", "second", "third"]

let first = queue.shift(); // "first"
console.log(queue);        // ["second", "third"]
```

**Use case:** Queue data structure (FIFO - First In, First Out)

### 3. slice() - Extract Portion

```javascript
let fruits = ["apple", "banana", "cherry", "date", "elderberry"];

let some = fruits.slice(1, 3);  // ["banana", "cherry"]
let last2 = fruits.slice(-2);   // ["date", "elderberry"]
let copy = fruits.slice();      // Copy entire array

console.log(fruits); // Original unchanged
```

**Does NOT modify original array.**

### 4. splice() - Add/Remove Elements

```javascript
let fruits = ["apple", "banana", "cherry"];

// Remove 1 element at index 1
fruits.splice(1, 1);
console.log(fruits); // ["apple", "cherry"]

// Remove 1 element at index 1 and add 2 new ones
fruits = ["apple", "banana", "cherry"];
fruits.splice(1, 1, "mango", "orange");
console.log(fruits); // ["apple", "mango", "orange", "cherry"]

// Insert without removing (delete count = 0)
fruits.splice(2, 0, "kiwi");
console.log(fruits); // ["apple", "mango", "kiwi", "orange", "cherry"]
```

**DOES modify original array.**

### 5. concat() - Merge Arrays

```javascript
let arr1 = [1, 2, 3];
let arr2 = [4, 5, 6];
let merged = arr1.concat(arr2);

console.log(merged); // [1, 2, 3, 4, 5, 6]
console.log(arr1);   // [1, 2, 3] (unchanged)

// Modern way: spread operator
let merged2 = [...arr1, ...arr2];
console.log(merged2); // [1, 2, 3, 4, 5, 6]
```

### 6. join() - Array to String

```javascript
let fruits = ["apple", "banana", "cherry"];

let str1 = fruits.join();      // "apple,banana,cherry"
let str2 = fruits.join(" ");   // "apple banana cherry"
let str3 = fruits.join(" - "); // "apple - banana - cherry"
```

**Compare to PHP:**
```php
<?php
$fruits = ["apple", "banana", "cherry"];
$str = implode(" - ", $fruits); // "apple - banana - cherry"
?>
```

### 7. split() - String to Array

Actually a **string method**, but returns an array:

```javascript
let str = "apple,banana,cherry";
let fruits = str.split(",");
console.log(fruits); // ["apple", "banana", "cherry"]

let words = "Hello world".split(" ");
console.log(words); // ["Hello", "world"]
```

**Compare to PHP:**
```php
<?php
$str = "apple,banana,cherry";
$fruits = explode(",", $str);
?>
```

### 8. includes() - Check if Element Exists

```javascript
let fruits = ["apple", "banana", "cherry"];

console.log(fruits.includes("banana")); // true
console.log(fruits.includes("mango"));  // false
```

**Compare to PHP:**
```php
<?php
$fruits = ["apple", "banana", "cherry"];
in_array("banana", $fruits); // true
?>
```

### 9. indexOf() and lastIndexOf() - Find Index

```javascript
let fruits = ["apple", "banana", "cherry", "banana"];

console.log(fruits.indexOf("banana"));     // 1 (first occurrence)
console.log(fruits.lastIndexOf("banana")); // 3 (last occurrence)
console.log(fruits.indexOf("mango"));      // -1 (not found)
```

### 10. reverse() - Reverse Array

```javascript
let numbers = [1, 2, 3, 4, 5];
numbers.reverse();
console.log(numbers); // [5, 4, 3, 2, 1]
```

**Modifies original array.**

### 11. sort() - Sort Array

```javascript
let fruits = ["cherry", "apple", "banana"];
fruits.sort();
console.log(fruits); // ["apple", "banana", "cherry"]

// Numbers need custom sort function
let numbers = [10, 5, 20, 3];
numbers.sort(); // Wrong! ["10", "20", "3", "5"] (sorts as strings)

// Correct way for numbers
numbers.sort((a, b) => a - b);
console.log(numbers); // [3, 5, 10, 20]

// Descending order
numbers.sort((a, b) => b - a);
console.log(numbers); // [20, 10, 5, 3]
```

**Modifies original array.**

---

## Advanced Array Methods (Functional Programming)

These methods use **callbacks** and don't modify the original array.

### 1. forEach() - Iterate Over Array

```javascript
let fruits = ["apple", "banana", "cherry"];

// With arrow function
fruits.forEach((fruit) => {
    console.log(fruit);
});

// With index and array
fruits.forEach((fruit, index, array) => {
    console.log(`${index}: ${fruit}`);
});
```

**Compare to PHP:**
```php
<?php
$fruits = ["apple", "banana", "cherry"];
foreach ($fruits as $fruit) {
    echo $fruit;
}
?>
```

**Similar, but JavaScript forEach is a method.**

### 2. map() - Transform Each Element

Creates a **new array** with transformed values:

```javascript
let numbers = [1, 2, 3, 4, 5];

// Double each number
let doubled = numbers.map((num) => num * 2);
console.log(doubled); // [2, 4, 6, 8, 10]

// Square each number
let squared = numbers.map((num) => num ** 2);
console.log(squared); // [1, 4, 9, 16, 25]

// Convert to strings
let strings = numbers.map((num) => `Number: ${num}`);
console.log(strings); // ["Number: 1", "Number: 2", ...]
```

**Real-world example:**
```javascript
let users = [
    { name: "Kieu", age: 25 },
    { name: "John", age: 30 },
    { name: "Jane", age: 28 }
];

let names = users.map((user) => user.name);
console.log(names); // ["Kieu", "John", "Jane"]
```

**Compare to PHP:**
```php
<?php
$numbers = [1, 2, 3, 4, 5];
$doubled = array_map(fn($num) => $num * 2, $numbers);
?>
```

### 3. filter() - Keep Only Matching Elements

Creates a **new array** with elements that pass the test:

```javascript
let numbers = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];

// Get only even numbers
let evens = numbers.filter((num) => num % 2 === 0);
console.log(evens); // [2, 4, 6, 8, 10]

// Get only numbers > 5
let large = numbers.filter((num) => num > 5);
console.log(large); // [6, 7, 8, 9, 10]
```

**Real-world example:**
```javascript
let users = [
    { name: "Kieu", age: 17 },
    { name: "John", age: 30 },
    { name: "Jane", age: 25 }
];

// Get only adults
let adults = users.filter((user) => user.age >= 18);
console.log(adults);
// [{ name: "John", age: 30 }, { name: "Jane", age: 25 }]
```

**Compare to PHP:**
```php
<?php
$numbers = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
$evens = array_filter($numbers, fn($num) => $num % 2 === 0);
?>
```

### 4. find() - Find First Matching Element

Returns the **first element** that passes the test:

```javascript
let users = [
    { id: 1, name: "Kieu" },
    { id: 2, name: "John" },
    { id: 3, name: "Jane" }
];

let user = users.find((u) => u.id === 2);
console.log(user); // { id: 2, name: "John" }

let notFound = users.find((u) => u.id === 99);
console.log(notFound); // undefined
```

### 5. findIndex() - Find Index of First Match

```javascript
let users = [
    { id: 1, name: "Kieu" },
    { id: 2, name: "John" },
    { id: 3, name: "Jane" }
];

let index = users.findIndex((u) => u.id === 2);
console.log(index); // 1

let notFound = users.findIndex((u) => u.id === 99);
console.log(notFound); // -1
```

### 6. some() - Test If Any Element Passes

```javascript
let numbers = [1, 2, 3, 4, 5];

let hasEven = numbers.some((num) => num % 2 === 0);
console.log(hasEven); // true

let hasNegative = numbers.some((num) => num < 0);
console.log(hasNegative); // false
```

### 7. every() - Test If All Elements Pass

```javascript
let numbers = [2, 4, 6, 8, 10];

let allEven = numbers.every((num) => num % 2 === 0);
console.log(allEven); // true

let allPositive = numbers.every((num) => num > 0);
console.log(allPositive); // true

let allLarge = numbers.every((num) => num > 5);
console.log(allLarge); // false
```

### 8. reduce() - Reduce to Single Value

Most powerful array method:

```javascript
let numbers = [1, 2, 3, 4, 5];

// Sum all numbers
let sum = numbers.reduce((total, num) => total + num, 0);
console.log(sum); // 15

// With explanation:
// Iteration 1: total = 0, num = 1 → return 0 + 1 = 1
// Iteration 2: total = 1, num = 2 → return 1 + 2 = 3
// Iteration 3: total = 3, num = 3 → return 3 + 3 = 6
// Iteration 4: total = 6, num = 4 → return 6 + 4 = 10
// Iteration 5: total = 10, num = 5 → return 10 + 5 = 15
```

**More reduce() examples:**

```javascript
// Find maximum
let max = numbers.reduce((max, num) => num > max ? num : max, 0);
console.log(max); // 5

// Count occurrences
let fruits = ["apple", "banana", "apple", "cherry", "banana", "apple"];
let count = fruits.reduce((acc, fruit) => {
    acc[fruit] = (acc[fruit] || 0) + 1;
    return acc;
}, {});
console.log(count);
// { apple: 3, banana: 2, cherry: 1 }
```

**Compare to PHP:**
```php
<?php
$numbers = [1, 2, 3, 4, 5];
$sum = array_reduce($numbers, fn($total, $num) => $total + $num, 0);
?>
```

---

## Method Chaining

Combine methods for powerful data transformations:

```javascript
let numbers = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];

// Get sum of squares of even numbers
let result = numbers
    .filter((num) => num % 2 === 0)     // [2, 4, 6, 8, 10]
    .map((num) => num ** 2)             // [4, 16, 36, 64, 100]
    .reduce((sum, num) => sum + num, 0); // 220

console.log(result); // 220
```

**Real-world example:**
```javascript
let products = [
    { name: "Laptop", price: 999, category: "electronics" },
    { name: "Mouse", price: 25, category: "electronics" },
    { name: "Shirt", price: 30, category: "clothing" },
    { name: "Phone", price: 699, category: "electronics" }
];

// Get total price of electronics over $50
let total = products
    .filter((p) => p.category === "electronics")
    .filter((p) => p.price > 50)
    .reduce((sum, p) => sum + p.price, 0);

console.log(total); // 1698 (Laptop + Phone)
```

---

## Objects in JavaScript

Objects are like **associative arrays** in PHP, but more powerful.

### Creating Objects

```javascript
// Object literal (most common)
let user = {
    name: "Kieu",
    age: 25,
    city: "Hanoi"
};

// Empty object
let empty = {};

// Using Object constructor (less common)
let obj = new Object();
obj.name = "Kieu";
```

**Compare to PHP:**
```php
<?php
$user = [
    "name" => "Kieu",
    "age" => 25,
    "city" => "Hanoi"
];
?>
```

**JavaScript objects are more like PHP classes!**

### Accessing Object Properties

```javascript
let user = {
    name: "Kieu",
    age: 25,
    city: "Hanoi"
};

// Dot notation (most common)
console.log(user.name);  // "Kieu"
console.log(user.age);   // 25

// Bracket notation (when property name is dynamic)
console.log(user["name"]); // "Kieu"

let prop = "age";
console.log(user[prop]);   // 25 (dynamic)

// Access non-existent property
console.log(user.country); // undefined
```

### Adding/Modifying Properties

```javascript
let user = {
    name: "Kieu"
};

// Add property
user.age = 25;
user.city = "Hanoi";

// Modify property
user.name = "Kieu Nguyen";

console.log(user);
// { name: "Kieu Nguyen", age: 25, city: "Hanoi" }
```

### Deleting Properties

```javascript
let user = {
    name: "Kieu",
    age: 25,
    city: "Hanoi"
};

delete user.age;

console.log(user);
// { name: "Kieu", city: "Hanoi" }
```

### Checking If Property Exists

```javascript
let user = {
    name: "Kieu",
    age: 25
};

console.log("name" in user);    // true
console.log("city" in user);    // false

// Or check if not undefined
console.log(user.name !== undefined);  // true
console.log(user.city !== undefined);  // false
```

---

## Object Methods

Objects can have **functions as properties** (called methods):

```javascript
let user = {
    name: "Kieu",
    age: 25,

    // Method (function as property)
    greet: function() {
        return `Hello, I'm ${this.name}!`;
    },

    // Shorthand syntax (ES6)
    sayAge() {
        return `I'm ${this.age} years old`;
    }
};

console.log(user.greet());   // "Hello, I'm Kieu!"
console.log(user.sayAge());  // "I'm 25 years old"
```

**The `this` keyword** refers to the object itself.

**Compare to PHP:**
```php
<?php
class User {
    public $name = "Kieu";
    public $age = 25;

    public function greet() {
        return "Hello, I'm {$this->name}!";
    }
}

$user = new User();
echo $user->greet();
?>
```

**JavaScript objects are like PHP classes without the `class` keyword!**

---

## Iterating Over Objects

### Object.keys() - Get Array of Keys

```javascript
let user = {
    name: "Kieu",
    age: 25,
    city: "Hanoi"
};

let keys = Object.keys(user);
console.log(keys); // ["name", "age", "city"]
```

### Object.values() - Get Array of Values

```javascript
let values = Object.values(user);
console.log(values); // ["Kieu", 25, "Hanoi"]
```

### Object.entries() - Get Array of [Key, Value] Pairs

```javascript
let entries = Object.entries(user);
console.log(entries);
// [["name", "Kieu"], ["age", 25], ["city", "Hanoi"]]
```

### for...in Loop

```javascript
let user = {
    name: "Kieu",
    age: 25,
    city: "Hanoi"
};

for (let key in user) {
    console.log(`${key}: ${user[key]}`);
}
// name: Kieu
// age: 25
// city: Hanoi
```

**Compare to PHP:**
```php
<?php
$user = ["name" => "Kieu", "age" => 25, "city" => "Hanoi"];
foreach ($user as $key => $value) {
    echo "$key: $value\n";
}
?>
```

**Same concept!**

---

## Object Destructuring

Extract properties into variables:

```javascript
let user = {
    name: "Kieu",
    age: 25,
    city: "Hanoi"
};

// Old way
let name = user.name;
let age = user.age;

// Destructuring (modern way)
let { name, age, city } = user;
console.log(name); // "Kieu"
console.log(age);  // 25
console.log(city); // "Hanoi"

// With different variable names
let { name: userName, age: userAge } = user;
console.log(userName); // "Kieu"
console.log(userAge);  // 25

// With default values
let { name, country = "Vietnam" } = user;
console.log(country); // "Vietnam" (default, not in object)
```

**Very useful for function parameters:**

```javascript
function greet({ name, age }) {
    return `Hello, ${name}! You are ${age} years old.`;
}

let user = { name: "Kieu", age: 25 };
console.log(greet(user)); // "Hello, Kieu! You are 25 years old."
```

---

## Array Destructuring

```javascript
let fruits = ["apple", "banana", "cherry"];

// Old way
let first = fruits[0];
let second = fruits[1];

// Destructuring
let [first, second, third] = fruits;
console.log(first);  // "apple"
console.log(second); // "banana"
console.log(third);  // "cherry"

// Skip elements
let [first, , third] = fruits;
console.log(first);  // "apple"
console.log(third);  // "cherry"

// Rest operator
let [first, ...rest] = fruits;
console.log(first); // "apple"
console.log(rest);  // ["banana", "cherry"]
```

---

## Spread Operator (...)

### With Arrays

```javascript
let arr1 = [1, 2, 3];
let arr2 = [4, 5, 6];

// Combine arrays
let combined = [...arr1, ...arr2];
console.log(combined); // [1, 2, 3, 4, 5, 6]

// Copy array
let copy = [...arr1];
console.log(copy); // [1, 2, 3]

// Add elements
let newArr = [0, ...arr1, 4];
console.log(newArr); // [0, 1, 2, 3, 4]
```

### With Objects

```javascript
let user = { name: "Kieu", age: 25 };

// Copy object
let copy = { ...user };

// Add properties
let extended = { ...user, city: "Hanoi" };
console.log(extended);
// { name: "Kieu", age: 25, city: "Hanoi" }

// Merge objects
let settings = { theme: "dark" };
let combined = { ...user, ...settings };
console.log(combined);
// { name: "Kieu", age: 25, theme: "dark" }

// Override properties
let updated = { ...user, age: 26 };
console.log(updated);
// { name: "Kieu", age: 26 }
```

---

## Practical Examples

### Example 1: Shopping Cart

```javascript
let cart = [
    { id: 1, name: "Laptop", price: 999, quantity: 1 },
    { id: 2, name: "Mouse", price: 25, quantity: 2 },
    { id: 3, name: "Keyboard", price: 75, quantity: 1 }
];

// Calculate total
let total = cart.reduce((sum, item) => {
    return sum + (item.price * item.quantity);
}, 0);

console.log(`Total: $${total}`); // Total: $1124
```

### Example 2: Filter and Sort Products

```javascript
let products = [
    { name: "Laptop", price: 999, inStock: true },
    { name: "Mouse", price: 25, inStock: true },
    { name: "Monitor", price: 399, inStock: false },
    { name: "Keyboard", price: 75, inStock: true }
];

// Get in-stock products, sorted by price
let available = products
    .filter((p) => p.inStock)
    .sort((a, b) => a.price - b.price);

console.log(available);
```

### Example 3: Group By Category

```javascript
let products = [
    { name: "Laptop", category: "electronics" },
    { name: "Shirt", category: "clothing" },
    { name: "Mouse", category: "electronics" },
    { name: "Pants", category: "clothing" }
];

let grouped = products.reduce((acc, product) => {
    let category = product.category;
    if (!acc[category]) {
        acc[category] = [];
    }
    acc[category].push(product);
    return acc;
}, {});

console.log(grouped);
// {
//   electronics: [{ name: "Laptop", ... }, { name: "Mouse", ... }],
//   clothing: [{ name: "Shirt", ... }, { name: "Pants", ... }]
// }
```

### Example 4: Transform API Response

```javascript
// API returns this
let apiResponse = [
    { user_id: 1, user_name: "Kieu", user_email: "kieu@example.com" },
    { user_id: 2, user_name: "John", user_email: "john@example.com" }
];

// Transform to cleaner format
let users = apiResponse.map((item) => ({
    id: item.user_id,
    name: item.user_name,
    email: item.user_email
}));

console.log(users);
// [
//   { id: 1, name: "Kieu", email: "kieu@example.com" },
//   { id: 2, name: "John", email: "john@example.com" }
// ]
```

---

## Practice Exercises

### Exercise 1: Array Methods

Create an array of numbers from 1-20. Use array methods to:
1. Get all even numbers
2. Double each number
3. Get sum of all numbers
4. Get numbers greater than 10
5. Check if any number is divisible by 7

### Exercise 2: User Management

Create an array of user objects:
```javascript
let users = [
    { id: 1, name: "Kieu", age: 25, isActive: true },
    { id: 2, name: "John", age: 17, isActive: false },
    { id: 3, name: "Jane", age: 30, isActive: true }
];
```

Write functions to:
1. Get all active users
2. Get all adults (age >= 18)
3. Get user names only
4. Find user by ID
5. Calculate average age

### Exercise 3: Shopping Cart

Create a shopping cart with these methods:
```javascript
let cart = {
    items: [],

    addItem(product, quantity) {
        // Add implementation
    },

    removeItem(productId) {
        // Add implementation
    },

    getTotal() {
        // Add implementation
    },

    getItemCount() {
        // Add implementation
    }
};
```

### Exercise 4: Data Transformation

You have this data:
```javascript
let orders = [
    { id: 1, customer: "Kieu", amount: 100, status: "paid" },
    { id: 2, customer: "John", amount: 200, status: "pending" },
    { id: 3, customer: "Kieu", amount: 150, status: "paid" },
    { id: 4, customer: "Jane", amount: 300, status: "paid" }
];
```

Create functions to:
1. Get total of paid orders
2. Group orders by customer
3. Get customers who have pending orders
4. Calculate average order amount

### Exercise 5: Object Manipulation

Create a function that merges two user objects:
```javascript
function mergeUsers(user1, user2) {
    // Merge objects, user2 properties override user1
    // Return new object without modifying originals
}

let user1 = { name: "Kieu", age: 25, city: "Hanoi" };
let user2 = { age: 26, country: "Vietnam" };

let merged = mergeUsers(user1, user2);
// Should return: { name: "Kieu", age: 26, city: "Hanoi", country: "Vietnam" }
```

---

## Quick Reference

### Array Methods
```javascript
// Modify original
arr.push(item)          // Add to end
arr.pop()               // Remove from end
arr.unshift(item)       // Add to beginning
arr.shift()             // Remove from beginning
arr.splice(i, n, ...)   // Remove/add at index
arr.reverse()           // Reverse
arr.sort()              // Sort

// Return new
arr.slice(start, end)   // Extract portion
arr.concat(arr2)        // Merge arrays
arr.map(fn)             // Transform each
arr.filter(fn)          // Keep matching
arr.reduce(fn, init)    // Reduce to one value

// Other
arr.forEach(fn)         // Iterate
arr.find(fn)            // Find first match
arr.includes(item)      // Check if contains
arr.indexOf(item)       // Find index
arr.join(separator)     // Array to string
```

### Object Methods
```javascript
Object.keys(obj)        // Array of keys
Object.values(obj)      // Array of values
Object.entries(obj)     // Array of [key, value]
Object.assign(t, s)     // Merge objects
```

### Destructuring
```javascript
let {name, age} = user;           // Object
let [first, second] = arr;        // Array
let {name, ...rest} = user;       // Rest
```

### Spread Operator
```javascript
let copy = [...arr];              // Copy array
let merged = [...arr1, ...arr2];  // Merge arrays
let copy = {...obj};              // Copy object
let merged = {...obj1, ...obj2};  // Merge objects
```

---

## Key Takeaways

1. **Use array methods** instead of loops when possible
2. **map, filter, reduce** are your best friends
3. **Method chaining** creates clean, readable code
4. **Objects** are like PHP associative arrays + classes
5. **Destructuring** makes code cleaner
6. **Spread operator** makes copying/merging easy
7. **Don't modify originals** unless intended
8. **forEach** iterates, **map** transforms, **filter** selects, **reduce** accumulates

---

## Next Lesson

In Lesson 05, we'll learn about:
- DOM manipulation (selecting elements, changing content)
- Creating and removing elements
- Modifying styles and classes
- Working with attributes

Time to make webpages interactive!
