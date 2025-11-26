# Lesson 02 - Properties and Methods

In the previous lesson, you learned what classes and objects are. Now let's dive deeper into **properties** (data) and **methods** (behavior) - the building blocks of every class.

---

## Properties: Object Data

Properties are variables that belong to an object. They store the object's **state** - its data at any given moment.

### Declaring Properties

```php
<?php
class User {
    // Simple property declarations
    public string $name;
    public string $email;
    public int $age;
    public bool $isActive;

    // Property with default value
    public string $role = "user";
    public int $loginCount = 0;
}
```

**Key points:**
- Properties are declared with visibility (`public` for now)
- Can specify types (string, int, float, bool, array, etc.)
- Can set default values
- If no default, property is uninitialized until you set it

### Accessing Properties

```php
<?php
$user = new User();

// Set property value
$user->name = "Kieu";
$user->email = "kieu@example.com";
$user->age = 28;

// Get property value
echo $user->name; // Kieu

// Modify property
$user->age = 29;
$user->loginCount++; // Increment
```

---

## Property Types

PHP supports various property types:

### Scalar Types

```php
<?php
class Product {
    public string $name;        // Text
    public int $stock;          // Whole number
    public float $price;        // Decimal number
    public bool $available;     // true or false
}

$product = new Product();
$product->name = "Laptop";
$product->stock = 5;
$product->price = 999.99;
$product->available = true;
```

### Array Type

```php
<?php
class ShoppingCart {
    public array $items = [];
    public array $metadata = [];
}

$cart = new ShoppingCart();
$cart->items[] = "Laptop";
$cart->items[] = "Mouse";
print_r($cart->items); // Array with 2 items
```

### Nullable Types

```php
<?php
class User {
    public string $name;
    public ?string $middleName = null; // Can be string or null
    public ?int $age = null;           // Can be int or null
}

$user = new User();
$user->name = "Kieu";
// middleName stays null - that's OK!

if ($user->middleName !== null) {
    echo $user->middleName;
}
```

**The `?` before the type means "this can be null".**

### Union Types (PHP 8+)

```php
<?php
class Payment {
    // Can be either int or float
    public int|float $amount;

    // Can be string or array
    public string|array $data;
}

$payment = new Payment();
$payment->amount = 100;      // ✅ int
$payment->amount = 99.99;    // ✅ float
$payment->amount = "100";    // ❌ Error: must be int or float
```

---

## Property Default Values

### With Defaults

```php
<?php
class User {
    public string $role = "user";
    public int $points = 0;
    public bool $verified = false;
    public array $permissions = ["read"];
}

$user = new User();
echo $user->role; // "user" (already set)
echo $user->points; // 0
```

### Without Defaults

```php
<?php
class Product {
    public string $name; // No default
    public float $price; // No default
}

$product = new Product();
// ⚠️ $product->name is uninitialized!
// echo $product->name; // Warning in PHP 8.2+

// Must set before using
$product->name = "Laptop";
echo $product->name; // ✅ OK now
```

---

## Methods: Object Behavior

Methods are functions that belong to a class. They define what the object can **do**.

### Basic Method Structure

```php
<?php
class User {
    public string $name;
    public int $age;

    // Method with no parameters, no return
    public function sayHello() {
        echo "Hello, I'm $this->name!";
    }

    // Method with parameters
    public function changeName(string $newName) {
        $this->name = $newName;
    }

    // Method with return value
    public function isAdult(): bool {
        return $this->age >= 18;
    }

    // Method with parameters and return
    public function getDescription(bool $detailed): string {
        if ($detailed) {
            return "$this->name is $this->age years old";
        }
        return $this->name;
    }
}
```

### Using Methods

```php
<?php
$user = new User();
$user->name = "Kieu";
$user->age = 28;

// Call method with no return
$user->sayHello(); // Hello, I'm Kieu!

// Call method that modifies object
$user->changeName("Kim");
echo $user->name; // Kim

// Call method that returns value
if ($user->isAdult()) {
    echo "Can vote!";
}

// Call method with parameters
$desc = $user->getDescription(true);
echo $desc; // Kim is 28 years old
```

---

## Methods Accessing Properties

Methods can read and modify the object's properties using `$this->`:

```php
<?php
class BankAccount {
    public string $holder;
    public float $balance = 0;

    public function deposit(float $amount) {
        // Read current balance, modify it
        $this->balance += $amount;
        echo "Deposited $$amount. New balance: $$this->balance\n";
    }

    public function withdraw(float $amount) {
        // Check before modifying
        if ($amount > $this->balance) {
            echo "Insufficient funds!\n";
            return false;
        }

        $this->balance -= $amount;
        echo "Withdrew $$amount. New balance: $$this->balance\n";
        return true;
    }

    public function getBalance(): float {
        return $this->balance;
    }
}

$account = new BankAccount();
$account->holder = "Kieu";
$account->deposit(100);   // Deposited $100. New balance: $100
$account->withdraw(30);   // Withdrew $30. New balance: $70
$account->withdraw(100);  // Insufficient funds!
echo $account->getBalance(); // 70
```

---

## Method Chaining

Methods that return `$this` allow chaining:

```php
<?php
class QueryBuilder {
    public string $sql = "";

    public function select(string $columns) {
        $this->sql = "SELECT $columns";
        return $this; // Return object itself
    }

    public function from(string $table) {
        $this->sql .= " FROM $table";
        return $this;
    }

    public function where(string $condition) {
        $this->sql .= " WHERE $condition";
        return $this;
    }

    public function get(): string {
        return $this->sql;
    }
}

$query = new QueryBuilder();

// Chain methods!
$sql = $query->select("*")
             ->from("users")
             ->where("age > 18")
             ->get();

echo $sql; // SELECT * FROM users WHERE age > 18
```

**How it works:**
Each method returns `$this`, so you can immediately call another method on it.

---

## Methods Calling Other Methods

```php
<?php
class Calculator {
    public float $result = 0;

    public function add(float $n) {
        $this->result += $n;
        return $this;
    }

    public function subtract(float $n) {
        $this->result -= $n;
        return $this;
    }

    public function reset() {
        $this->result = 0;
        return $this;
    }

    // This method calls other methods
    public function calculate(string $operation, float $n) {
        if ($operation === 'add') {
            $this->add($n); // Call add method
        } elseif ($operation === 'subtract') {
            $this->subtract($n); // Call subtract method
        }
        return $this;
    }

    public function getResult(): float {
        return $this->result;
    }
}

$calc = new Calculator();
$calc->calculate('add', 10)
     ->calculate('add', 5)
     ->calculate('subtract', 3);

echo $calc->getResult(); // 12
```

---

## Static Properties and Methods

Sometimes you want something that belongs to the **class itself**, not to individual objects.

### Static Properties

```php
<?php
class User {
    // Belongs to the class, shared by all objects
    public static int $count = 0;

    public string $name;

    public function __construct(string $name) {
        $this->name = $name;
        self::$count++; // Increment class counter
    }
}

// Access static property with ::
echo User::$count; // 0

$user1 = new User("Kieu");
echo User::$count; // 1

$user2 = new User("John");
echo User::$count; // 2

// All objects share the same static property
```

**Use `ClassName::$property` to access static properties.**
**Use `self::$property` inside the class.**

### Static Methods

```php
<?php
class MathHelper {
    // Static method - doesn't need an object
    public static function square(float $n): float {
        return $n * $n;
    }

    public static function cube(float $n): float {
        return $n * $n * $n;
    }
}

// Call without creating an object
echo MathHelper::square(5); // 25
echo MathHelper::cube(3);   // 27

// No need for: $helper = new MathHelper();
```

**When to use static:**
- Utility functions (like Math helpers)
- Factory methods
- Counters shared across all objects

**When NOT to use static:**
- When you need `$this` (object's properties)
- Most regular methods

---

## Practical Example: Todo Item

Let's build a complete example:

```php
<?php
class TodoItem {
    // Properties
    public string $title;
    public string $description;
    public bool $completed = false;
    public ?string $completedAt = null;

    // Static counter for all todos
    public static int $totalCreated = 0;

    // Method: mark as complete
    public function complete() {
        if ($this->completed) {
            echo "$this->title is already completed!\n";
            return;
        }

        $this->completed = true;
        $this->completedAt = date('Y-m-d H:i:s');
        echo "✅ Completed: $this->title\n";
    }

    // Method: mark as incomplete
    public function uncomplete() {
        $this->completed = false;
        $this->completedAt = null;
        echo "⬜ Reopened: $this->title\n";
    }

    // Method: display status
    public function display() {
        $status = $this->completed ? '✅' : '⬜';
        echo "$status $this->title\n";

        if ($this->description) {
            echo "   Description: $this->description\n";
        }

        if ($this->completedAt) {
            echo "   Completed at: $this->completedAt\n";
        }
    }

    // Method: check if overdue (simplified)
    public function isOverdue(): bool {
        // In real app, would check against due date
        return !$this->completed;
    }

    // Static method: get total count
    public static function getTotalCreated(): int {
        return self::$totalCreated;
    }
}

// Create todos
$todo1 = new TodoItem();
$todo1->title = "Learn PHP OOP";
$todo1->description = "Study classes and objects";
TodoItem::$totalCreated++;

$todo2 = new TodoItem();
$todo2->title = "Build a project";
$todo2->description = "Apply OOP concepts";
TodoItem::$totalCreated++;

$todo3 = new TodoItem();
$todo3->title = "Exercise daily";
TodoItem::$totalCreated++;

// Use the todos
$todo1->complete();  // ✅ Completed: Learn PHP OOP
$todo1->display();
/*
✅ Learn PHP OOP
   Description: Study classes and objects
   Completed at: 2024-01-15 14:30:00
*/

$todo2->display();
/*
⬜ Build a project
   Description: Apply OOP concepts
*/

echo "\nTotal todos created: " . TodoItem::getTotalCreated(); // 3
```

---

## Best Practices

### 1. Name Methods as Actions (Verbs)

```php
<?php
// ✅ Good: verb phrases
class User {
    public function save() { }
    public function delete() { }
    public function sendEmail() { }
    public function isAdmin(): bool { }
    public function hasPermission(): bool { }
}

// ❌ Bad: noun phrases
class User {
    public function user() { }
    public function email() { }
}
```

### 2. Keep Methods Small and Focused

```php
<?php
// ❌ Bad: method does too much
class Order {
    public function process() {
        // Validate
        // Calculate price
        // Apply discount
        // Charge payment
        // Send confirmation email
        // Update inventory
        // Log everything
    }
}

// ✅ Good: break into smaller methods
class Order {
    public function process() {
        $this->validate();
        $total = $this->calculateTotal();
        $this->chargePayment($total);
        $this->sendConfirmation();
        $this->updateInventory();
        $this->log();
    }

    private function validate() { }
    private function calculateTotal(): float { }
    private function chargePayment(float $total) { }
    private function sendConfirmation() { }
    private function updateInventory() { }
    private function log() { }
}
```

### 3. One Responsibility per Method

```php
<?php
// ❌ Bad: method changes multiple things
class User {
    public function update(string $name, string $email) {
        $this->name = $name;
        $this->email = $email;
        $this->sendEmailChangeNotification();
        $this->logChange();
    }
}

// ✅ Good: separate concerns
class User {
    public function changeName(string $name) {
        $this->name = $name;
    }

    public function changeEmail(string $email) {
        $oldEmail = $this->email;
        $this->email = $email;
        $this->sendEmailChangeNotification($oldEmail);
    }

    private function sendEmailChangeNotification(string $oldEmail) { }
}
```

### 4. Return Values vs Side Effects

```php
<?php
class Product {
    public float $price;

    // ✅ Good: clear return value
    public function calculateDiscount(float $percentage): float {
        return $this->price * ($percentage / 100);
    }

    // ✅ Good: clear side effect
    public function applyDiscount(float $percentage): void {
        $discount = $this->calculateDiscount($percentage);
        $this->price -= $discount;
    }

    // ❌ Confusing: both modifies and returns
    public function processDiscount(float $percentage): float {
        $discount = $this->price * ($percentage / 100);
        $this->price -= $discount; // Side effect
        return $discount;          // Return value
    }
}
```

---

## Try It Yourself

### Exercise 1: BlogPost Class

Create a `BlogPost` class with:
- Properties: title, content, author, views (default 0), likes (default 0)
- Methods:
  - `incrementViews()` - adds 1 to views
  - `like()` - adds 1 to likes
  - `getEngagementRate()` - returns likes / views (handle division by zero)
  - `display()` - shows all information
  - Static property `$totalPosts` to track all posts created

Create 3 posts, simulate some activity, and display engagement rates.

### Exercise 2: Temperature Converter

Create a `Temperature` class with:
- Property: value, scale ('C' or 'F')
- Methods:
  - `toCelsius()` - converts to Celsius
  - `toFahrenheit()` - converts to Fahrenheit
  - `display()` - shows current value and scale
  - Static methods:
    - `fromCelsius($value)` - creates Temperature object
    - `fromFahrenheit($value)` - creates Temperature object

Formulas:
- C to F: (C × 9/5) + 32
- F to C: (F - 32) × 5/9

### Exercise 3: Shopping Cart

Create a `ShoppingCart` class with:
- Properties: items (array), discountPercentage (default 0)
- Methods:
  - `addItem($name, $price, $quantity)` - adds to items array
  - `removeItem($name)` - removes from items
  - `getSubtotal()` - calculates total before discount
  - `applyDiscount($percentage)` - sets discount
  - `getTotal()` - calculates final total with discount
  - `getItemCount()` - returns total number of items
  - `display()` - shows cart contents and total

---

## Common Mistakes

### 1. Forgetting Return Statement

```php
<?php
class Calculator {
    public function add(int $a, int $b): int {
        $a + $b; // ❌ Calculates but doesn't return
    }
}

$calc = new Calculator();
$result = $calc->add(5, 3);
echo $result; // null (nothing was returned)

// ✅ Correct:
public function add(int $a, int $b): int {
    return $a + $b;
}
```

### 2. Modifying Parameters Instead of Properties

```php
<?php
class User {
    public string $name;

    public function changeName(string $name) {
        $name = strtoupper($name); // ❌ Modifies parameter, not property
    }
}

// ✅ Correct:
public function changeName(string $name) {
    $this->name = strtoupper($name); // Modify property
}
```

### 3. Using Static When You Need Instance Data

```php
<?php
class User {
    public string $name;

    // ❌ Can't access $this in static method
    public static function greet() {
        echo "Hello, $this->name"; // Error!
    }
}

// ✅ Correct: make it instance method
public function greet() {
    echo "Hello, $this->name";
}
```

---

## Quick Reference

```php
<?php
class Example {
    // Property declarations
    public string $property;
    public int $withDefault = 0;
    public ?string $nullable = null;
    public static int $staticProperty = 0;

    // Method: no parameters, no return
    public function simpleMethod() {
        $this->property = "value";
    }

    // Method: with parameters and return
    public function calculate(int $a, int $b): int {
        return $a + $b;
    }

    // Method: returns $this for chaining
    public function chainable() {
        return $this;
    }

    // Static method
    public static function staticMethod() {
        self::$staticProperty++;
    }
}

// Usage
$obj = new Example();
$obj->property = "value";            // Set property
$value = $obj->property;             // Get property
$obj->simpleMethod();                // Call method
$result = $obj->calculate(5, 3);     // Call with parameters
$obj->chainable()->simpleMethod();   // Method chaining
Example::staticMethod();             // Call static method
echo Example::$staticProperty;       // Access static property
```

---

## What's Next?

You now understand properties and methods, but there's still a problem:

```php
<?php
$user = new User();
// Oops! Forgot to set required properties
$user->sendEmail(); // Error: email is undefined
```

In the next lesson, you'll learn about **constructors** - special methods that initialize objects properly when they're created, ensuring all required data is set from the start.

---

## Key Takeaways

- **Properties** store object state (data)
- **Methods** define object behavior (actions)
- Use `$this->` to access properties and methods inside the class
- Properties can have types and default values
- Methods can accept parameters and return values
- **Static** properties/methods belong to the class, not instances
- Methods should be small, focused, and clearly named
- Return `$this` to enable method chaining
