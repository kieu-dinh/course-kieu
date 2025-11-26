# Lesson 01 - Classes and Objects

## What is Object-Oriented Programming?

Until now, you've been writing **procedural code** - a sequence of instructions that tell the computer what to do step by step.

```php
<?php
// Procedural approach
$userName = "Kieu";
$userEmail = "kieu@example.com";
$userAge = 28;

function displayUser($name, $email) {
    echo "$name ($email)";
}

displayUser($userName, $userEmail);
```

This works fine for small programs. But what happens when you have 10 users? 100 users? What if each user has 20 properties? Your code becomes a mess of variables and functions.

**Object-Oriented Programming (OOP)** solves this by grouping related data and functions together into **objects**.

---

## Real-World Analogy

Think about a car in the real world:

**A car has:**
- Properties (data): color, brand, speed, fuel level
- Actions (behavior): start(), accelerate(), brake(), refuel()

**In OOP:**
- Properties = variables inside an object
- Actions = functions (called "methods") inside an object

---

## What is a Class?

A **class** is a **blueprint** or template for creating objects.

Think of it like a cookie cutter:
- The cookie cutter (class) defines the shape
- Each cookie (object) made from it has the same shape but can be different (chocolate chips, vanilla, etc.)

```php
<?php
// This is a blueprint for creating User objects
class User {
    // Properties (data)
    public string $name;
    public string $email;

    // Methods (behavior)
    public function sayHello() {
        echo "Hello, I'm $this->name!";
    }
}
```

**Key parts:**
- `class User` - declares a new class named User
- `public` - means the property/method can be accessed from outside (we'll learn more in lesson 04)
- `$name`, `$email` - properties that hold data
- `sayHello()` - a method (function inside a class)
- `$this` - refers to the current object

---

## What is an Object?

An **object** is an **instance** of a class. It's a concrete thing created from the blueprint.

```php
<?php
// Create an object from the User class
$user1 = new User();
$user1->name = "Kieu";
$user1->email = "kieu@example.com";

// Create another object
$user2 = new User();
$user2->name = "John";
$user2->email = "john@example.com";

// Both objects have the same structure but different data
$user1->sayHello(); // Hello, I'm Kieu!
$user2->sayHello(); // Hello, I'm John!
```

**Key points:**
- `new User()` creates a new object (instance)
- `$user1` and `$user2` are separate objects
- Use `->` to access properties and methods
- Each object has its own data

---

## Your First Complete Class

Let's create a `Product` class for an e-commerce site:

```php
<?php
class Product {
    // Properties
    public string $name;
    public float $price;
    public int $stock;

    // Methods
    public function display() {
        echo "Product: $this->name\n";
        echo "Price: $$this->price\n";
        echo "In stock: $this->stock\n";
    }

    public function isAvailable() {
        return $this->stock > 0;
    }

    public function sell(int $quantity) {
        if ($quantity > $this->stock) {
            echo "Not enough stock!\n";
            return false;
        }

        $this->stock -= $quantity;
        echo "Sold $quantity units. Remaining: $this->stock\n";
        return true;
    }
}

// Create a product
$laptop = new Product();
$laptop->name = "MacBook Pro";
$laptop->price = 1999.99;
$laptop->stock = 5;

// Use the object
$laptop->display();
// Output:
// Product: MacBook Pro
// Price: $1999.99
// In stock: 5

if ($laptop->isAvailable()) {
    $laptop->sell(2);
}
// Output: Sold 2 units. Remaining: 3
```

---

## Understanding $this

`$this` is a special variable that refers to the **current object**.

```php
<?php
class Dog {
    public string $name;

    public function bark() {
        // $this->name refers to THIS dog's name
        echo "$this->name says: Woof!\n";
    }

    public function rename(string $newName) {
        // $this->name = the current object's name property
        $this->name = $newName;
    }
}

$dog1 = new Dog();
$dog1->name = "Max";
$dog1->bark(); // Max says: Woof!

$dog2 = new Dog();
$dog2->name = "Bella";
$dog2->bark(); // Bella says: Woof!

// Each object's $this refers to itself
$dog1->rename("Rex");
$dog1->bark(); // Rex says: Woof!
```

**Without `$this`, how would the method know which object's name to use?**

---

## Properties vs Local Variables

```php
<?php
class Calculator {
    // Property: belongs to the object, persists between method calls
    public float $result = 0;

    public function add(float $number) {
        // Local variable: only exists inside this method
        $temp = $this->result + $number;

        // Store in property so it persists
        $this->result = $temp;
    }

    public function getResult() {
        // Can access $result because it's a property
        return $this->result;

        // Cannot access $temp here - it doesn't exist outside add()
    }
}

$calc = new Calculator();
$calc->add(5);
$calc->add(3);
echo $calc->getResult(); // 8

// Can access property from outside
echo $calc->result; // 8
```

**Key difference:**
- **Properties** (`$this->result`): persist as long as the object exists
- **Local variables** (`$temp`): only exist within the method

---

## Methods Can Call Other Methods

```php
<?php
class User {
    public string $firstName;
    public string $lastName;
    public string $email;

    public function getFullName() {
        return $this->firstName . ' ' . $this->lastName;
    }

    public function sendWelcomeEmail() {
        // Call another method using $this
        $fullName = $this->getFullName();

        echo "Sending email to $this->email...\n";
        echo "Dear $fullName, welcome to our site!\n";
    }
}

$user = new User();
$user->firstName = "Kieu";
$user->lastName = "Nguyen";
$user->email = "kieu@example.com";

$user->sendWelcomeEmail();
// Output:
// Sending email to kieu@example.com...
// Dear Kieu Nguyen, welcome to our site!
```

---

## Why Use Classes and Objects?

### Before OOP:
```php
<?php
// Managing multiple users with arrays
$user1 = [
    'name' => 'Kieu',
    'email' => 'kieu@example.com'
];

$user2 = [
    'name' => 'John',
    'email' => 'john@example.com'
];

function displayUser($user) {
    echo $user['name'] . " (" . $user['email'] . ")";
}

displayUser($user1);
```

**Problems:**
- No structure enforcement (what if you forget the 'email' key?)
- Functions scattered everywhere
- Hard to maintain as the app grows
- No relationship between data and behavior

### With OOP:
```php
<?php
class User {
    public string $name;
    public string $email;

    public function display() {
        echo "$this->name ($this->email)";
    }
}

$user1 = new User();
$user1->name = "Kieu";
$user1->email = "kieu@example.com";

$user1->display();
```

**Benefits:**
- Structure is enforced
- Data and behavior are together
- Easy to find related code
- Can add new features without breaking existing code

---

## Multiple Objects Example

Let's build a simple blog:

```php
<?php
class BlogPost {
    public string $title;
    public string $content;
    public string $author;
    public int $views = 0;

    public function display() {
        echo "=== $this->title ===\n";
        echo "By: $this->author\n";
        echo "Views: $this->views\n";
        echo "$this->content\n\n";
    }

    public function incrementViews() {
        $this->views++;
    }
}

// Create multiple posts
$post1 = new BlogPost();
$post1->title = "Learning PHP OOP";
$post1->content = "Today I learned about classes and objects...";
$post1->author = "Kieu";

$post2 = new BlogPost();
$post2->title = "My First Project";
$post2->content = "I built a todo app using OOP!";
$post2->author = "Kieu";

$post3 = new BlogPost();
$post3->title = "Web Development Tips";
$post3->content = "Here are 5 tips for beginners...";
$post3->author = "John";

// Simulate users viewing posts
$post1->incrementViews();
$post1->incrementViews();
$post2->incrementViews();

// Display all posts
$posts = [$post1, $post2, $post3];
foreach ($posts as $post) {
    $post->display();
}

// Output:
// === Learning PHP OOP ===
// By: Kieu
// Views: 2
// Today I learned about classes and objects...
//
// === My First Project ===
// By: Kieu
// Views: 1
// I built a todo app using OOP!
//
// === Web Development Tips ===
// By: John
// Views: 0
// Here are 5 tips for beginners...
```

---

## Type Declarations

PHP allows you to specify types for properties:

```php
<?php
class Product {
    public string $name;      // Must be a string
    public float $price;      // Must be a float
    public int $stock;        // Must be an integer
    public bool $available;   // Must be true or false
    public array $tags;       // Must be an array
}

$product = new Product();
$product->name = "Laptop";     // ✅ OK
$product->price = 999.99;      // ✅ OK
$product->stock = 5;           // ✅ OK

$product->price = "expensive"; // ❌ Error: must be float
```

**Why use types?**
- Catches bugs early
- Makes code more readable
- IDE can help with autocomplete

---

## Try It Yourself

### Exercise 1: Book Class

Create a `Book` class with:
- Properties: title, author, pages, isRead (boolean)
- Methods:
  - `describe()` - prints all book info
  - `markAsRead()` - sets isRead to true
  - `getTotalReadingTime()` - assumes 1 page = 2 minutes

Create 3 books, mark some as read, and display them all.

<details>
<summary>Hint</summary>

```php
<?php
class Book {
    public string $title;
    public string $author;
    public int $pages;
    public bool $isRead = false;

    // Add methods here
}
```
</details>

### Exercise 2: Bank Account

Create a `BankAccount` class with:
- Properties: accountNumber, holderName, balance
- Methods:
  - `deposit($amount)` - adds money
  - `withdraw($amount)` - removes money (check if enough balance)
  - `getBalance()` - returns current balance
  - `displayInfo()` - shows account details

Create 2 accounts, do some transactions, and display final balances.

### Exercise 3: Shopping Cart Item

Create a `CartItem` class for an e-commerce site:
- Properties: productName, price, quantity
- Methods:
  - `getSubtotal()` - returns price × quantity
  - `changeQuantity($newQuantity)` - updates quantity
  - `display()` - shows item details and subtotal

Create 3 items with different quantities and show the total.

---

## Common Mistakes

### 1. Forgetting `new` keyword
```php
<?php
$user = User(); // ❌ Error
$user = new User(); // ✅ Correct
```

### 2. Forgetting `$this->`
```php
<?php
class User {
    public string $name;

    public function greet() {
        echo "Hello, $name"; // ❌ Wrong: $name is undefined
        echo "Hello, $this->name"; // ✅ Correct
    }
}
```

### 3. Using `$this` outside a class
```php
<?php
function test() {
    echo $this->name; // ❌ Error: $this only exists inside classes
}
```

### 4. Accessing properties like variables
```php
<?php
$user = new User();
echo $user.name; // ❌ Wrong syntax
echo $user->name; // ✅ Correct: use ->
```

---

## Quick Reference

```php
<?php
// Define a class
class ClassName {
    // Properties
    public type $propertyName;

    // Methods
    public function methodName() {
        // $this refers to current object
        $this->propertyName = "value";
    }
}

// Create object
$object = new ClassName();

// Access properties
$object->propertyName = "value";
$value = $object->propertyName;

// Call methods
$object->methodName();
```

---

## What's Next?

You now know the basics of classes and objects! But there's a problem with our current approach:

```php
<?php
$user = new User();
// Oops, forgot to set name and email!
$user->sayHello(); // Error or weird output
```

In the next lesson, you'll learn about **constructors** - a special method that runs automatically when you create an object, ensuring it always has the required data.

---

## Key Takeaways

- A **class** is a blueprint for creating objects
- An **object** is an instance of a class
- **Properties** store data, **methods** define behavior
- Use `$this->` to access properties and methods inside a class
- Use `->` to access properties and methods from outside
- Objects group related data and functions together
- Each object is independent with its own data

---

## Practice Challenge

Before moving to the next lesson, try building a `Rectangle` class:
- Properties: width, height
- Methods:
  - `getArea()` - returns width × height
  - `getPerimeter()` - returns 2 × (width + height)
  - `isSquare()` - returns true if width equals height
  - `scale($factor)` - multiplies both width and height by factor

Create 3 rectangles with different sizes and test all methods!
