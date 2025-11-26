# Lesson 03 - Constructor

## The Problem

So far, creating objects has been a bit messy:

```php
<?php
class User {
    public string $name;
    public string $email;
    public int $age;
}

$user = new User();
$user->name = "Kieu";
$user->email = "kieu@example.com";
$user->age = 28;
// That's 4 lines just to create one user!

// Worse: easy to forget required properties
$user2 = new User();
// Forgot to set email!
$user2->sendWelcomeEmail(); // Error: email is undefined
```

**Problems:**
- Too much code to create an object
- Easy to forget required properties
- Object can exist in invalid state
- No way to enforce required data

---

## The Solution: Constructor

A **constructor** is a special method that runs automatically when you create an object. It's the perfect place to initialize required properties.

```php
<?php
class User {
    public string $name;
    public string $email;
    public int $age;

    // Constructor: runs when you use 'new User()'
    public function __construct(string $name, string $email, int $age) {
        $this->name = $name;
        $this->email = $email;
        $this->age = $age;
    }
}

// Now creating a user is one line, and all data is required!
$user = new User("Kieu", "kieu@example.com", 28);

echo $user->name; // Kieu
echo $user->email; // kieu@example.com

// This won't compile: missing parameters
// $user2 = new User(); // Error!
```

**Benefits:**
- One line to create objects
- All required data is enforced
- Object is always in valid state
- Clear what's needed to create an object

---

## Constructor Syntax

```php
<?php
class ClassName {
    // Properties
    public type $property;

    // Constructor
    public function __construct(parameters) {
        // Initialize properties
        $this->property = $parameter;
    }
}

// Create object with arguments
$object = new ClassName($arg1, $arg2);
```

**Key points:**
- Name is always `__construct` (double underscore)
- Runs automatically when you use `new`
- No return type (constructors don't return anything)
- Can have parameters (usually does)

---

## Basic Constructor Example

```php
<?php
class Product {
    public string $name;
    public float $price;
    public int $stock;

    public function __construct(string $name, float $price, int $stock) {
        $this->name = $name;
        $this->price = $price;
        $this->stock = $stock;

        echo "Created product: $name\n";
    }

    public function display() {
        echo "$this->name - $$this->price (Stock: $this->stock)\n";
    }
}

// Create products
$laptop = new Product("MacBook Pro", 1999.99, 5);
// Output: Created product: MacBook Pro

$mouse = new Product("Magic Mouse", 79.99, 20);
// Output: Created product: Magic Mouse

$laptop->display(); // MacBook Pro - $1999.99 (Stock: 5)
```

---

## Constructor with Default Parameters

Not all parameters need to be required:

```php
<?php
class User {
    public string $name;
    public string $email;
    public string $role;
    public bool $isActive;

    public function __construct(
        string $name,
        string $email,
        string $role = "user",      // Default value
        bool $isActive = true       // Default value
    ) {
        $this->name = $name;
        $this->email = $email;
        $this->role = $role;
        $this->isActive = $isActive;
    }
}

// Only provide required parameters
$user1 = new User("Kieu", "kieu@example.com");
echo $user1->role; // "user" (default)
echo $user1->isActive; // true (default)

// Override defaults
$user2 = new User("John", "john@example.com", "admin", false);
echo $user2->role; // "admin"
echo $user2->isActive; // false
```

**Pro tip:** Put required parameters first, optional ones last.

---

## Validation in Constructor

Ensure the object is created with valid data:

```php
<?php
class BankAccount {
    public string $accountNumber;
    public string $holder;
    public float $balance;

    public function __construct(string $accountNumber, string $holder, float $initialBalance = 0) {
        // Validate account number
        if (strlen($accountNumber) !== 10) {
            throw new Exception("Account number must be 10 digits");
        }

        // Validate holder name
        if (empty($holder)) {
            throw new Exception("Holder name is required");
        }

        // Validate balance
        if ($initialBalance < 0) {
            throw new Exception("Initial balance cannot be negative");
        }

        $this->accountNumber = $accountNumber;
        $this->holder = $holder;
        $this->balance = $initialBalance;
    }

    public function getBalance(): float {
        return $this->balance;
    }
}

// Valid account
$account1 = new BankAccount("1234567890", "Kieu", 1000);
echo $account1->getBalance(); // 1000

// Invalid accounts
try {
    $account2 = new BankAccount("123", "John", 500); // Error: too short
} catch (Exception $e) {
    echo $e->getMessage(); // Account number must be 10 digits
}

try {
    $account3 = new BankAccount("1234567890", "", 500); // Error: empty name
} catch (Exception $e) {
    echo $e->getMessage(); // Holder name is required
}

try {
    $account4 = new BankAccount("1234567890", "John", -100); // Error: negative
} catch (Exception $e) {
    echo $e->getMessage(); // Initial balance cannot be negative
}
```

---

## Constructor Logic

Constructors can do more than just assign properties:

```php
<?php
class BlogPost {
    public string $title;
    public string $content;
    public string $slug;
    public string $createdAt;

    public function __construct(string $title, string $content) {
        $this->title = $title;
        $this->content = $content;

        // Generate slug from title
        $this->slug = $this->generateSlug($title);

        // Set creation timestamp
        $this->createdAt = date('Y-m-d H:i:s');
    }

    private function generateSlug(string $title): string {
        // Convert to lowercase
        $slug = strtolower($title);
        // Replace spaces with hyphens
        $slug = str_replace(' ', '-', $slug);
        // Remove special characters
        $slug = preg_replace('/[^a-z0-9-]/', '', $slug);
        return $slug;
    }

    public function getUrl(): string {
        return "/blog/" . $this->slug;
    }
}

$post = new BlogPost("Learning PHP OOP!", "Today I learned about constructors...");

echo $post->title;     // Learning PHP OOP!
echo $post->slug;      // learning-php-oop
echo $post->createdAt; // 2024-01-15 14:30:00
echo $post->getUrl();  // /blog/learning-php-oop
```

---

## Constructor Property Promotion (PHP 8+)

PHP 8 introduced a shortcut to declare properties directly in the constructor:

### Old Way (Verbose)

```php
<?php
class User {
    public string $name;
    public string $email;
    public int $age;

    public function __construct(string $name, string $email, int $age) {
        $this->name = $name;
        $this->email = $email;
        $this->age = $age;
    }
}
```

### New Way (Property Promotion)

```php
<?php
class User {
    // Declare AND assign in one place!
    public function __construct(
        public string $name,
        public string $email,
        public int $age
    ) {
        // Properties are automatically created and assigned
        // No need for $this->name = $name;
    }
}

$user = new User("Kieu", "kieu@example.com", 28);
echo $user->name; // Kieu
```

**This is exactly the same as the old way, just shorter!**

### Mixing Promoted and Regular Properties

```php
<?php
class Product {
    public string $slug; // Regular property

    public function __construct(
        public string $name,     // Promoted property
        public float $price,     // Promoted property
        public int $stock = 0    // Promoted with default
    ) {
        // Can still do additional logic
        $this->slug = strtolower(str_replace(' ', '-', $name));
        echo "Product created: $this->name\n";
    }
}

$product = new Product("Magic Mouse", 79.99, 10);
echo $product->slug; // magic-mouse
```

---

## Constructor with Type Checking

```php
<?php
class Rectangle {
    public function __construct(
        public float $width,
        public float $height
    ) {
        if ($width <= 0 || $height <= 0) {
            throw new Exception("Width and height must be positive");
        }
    }

    public function getArea(): float {
        return $this->width * $this->height;
    }

    public function getPerimeter(): float {
        return 2 * ($this->width + $this->height);
    }
}

$rect1 = new Rectangle(10, 5);
echo $rect1->getArea(); // 50

$rect2 = new Rectangle(-5, 10); // Error: Width and height must be positive
```

---

## Multiple Ways to Create Objects (Factory Pattern Preview)

Sometimes you want different ways to create an object:

```php
<?php
class Temperature {
    public function __construct(
        public float $celsius
    ) {}

    // Alternative constructor: from Fahrenheit
    public static function fromFahrenheit(float $fahrenheit): self {
        $celsius = ($fahrenheit - 32) * 5/9;
        return new self($celsius);
    }

    // Alternative constructor: from Kelvin
    public static function fromKelvin(float $kelvin): self {
        $celsius = $kelvin - 273.15;
        return new self($celsius);
    }

    public function toCelsius(): float {
        return $this->celsius;
    }

    public function toFahrenheit(): float {
        return ($this->celsius * 9/5) + 32;
    }
}

// Create from Celsius (normal constructor)
$temp1 = new Temperature(25);
echo $temp1->toCelsius(); // 25

// Create from Fahrenheit
$temp2 = Temperature::fromFahrenheit(77);
echo $temp2->toCelsius(); // 25

// Create from Kelvin
$temp3 = Temperature::fromKelvin(298.15);
echo $temp3->toCelsius(); // 25
```

**This pattern gives you multiple ways to create objects!**

---

## Constructor vs Factory Methods: When to Use What?

### Use Constructor When:
- One obvious way to create the object
- Simple initialization
- Required data is straightforward

```php
<?php
class User {
    public function __construct(
        public string $name,
        public string $email
    ) {}
}

$user = new User("Kieu", "kieu@example.com");
```

### Use Factory Methods When:
- Multiple ways to create the object
- Complex creation logic
- Want descriptive names

```php
<?php
class User {
    public function __construct(
        public string $name,
        public string $email,
        public string $provider
    ) {}

    public static function fromEmail(string $name, string $email): self {
        return new self($name, $email, "email");
    }

    public static function fromGoogle(string $googleId, string $email): self {
        // Fetch name from Google API
        $name = "Fetched from Google";
        return new self($name, $email, "google");
    }

    public static function fromFacebook(string $facebookId): self {
        // Fetch data from Facebook API
        return new self("Facebook User", "fb@example.com", "facebook");
    }
}

$user1 = User::fromEmail("Kieu", "kieu@example.com");
$user2 = User::fromGoogle("google-id-123", "user@gmail.com");
$user3 = User::fromFacebook("fb-id-456");
```

---

## Practical Example: Shopping Cart

```php
<?php
class CartItem {
    public function __construct(
        public string $productName,
        public float $price,
        public int $quantity = 1
    ) {
        if ($price < 0) {
            throw new Exception("Price cannot be negative");
        }
        if ($quantity < 1) {
            throw new Exception("Quantity must be at least 1");
        }
    }

    public function getSubtotal(): float {
        return $this->price * $this->quantity;
    }

    public function increaseQuantity(int $amount = 1): void {
        $this->quantity += $amount;
    }
}

class ShoppingCart {
    private array $items = [];

    public function __construct(
        public string $customerName,
        public float $taxRate = 0.1
    ) {}

    public function addItem(string $name, float $price, int $quantity = 1): void {
        $item = new CartItem($name, $price, $quantity);
        $this->items[] = $item;
        echo "Added: {$item->productName} x{$item->quantity}\n";
    }

    public function getSubtotal(): float {
        $total = 0;
        foreach ($this->items as $item) {
            $total += $item->getSubtotal();
        }
        return $total;
    }

    public function getTax(): float {
        return $this->getSubtotal() * $this->taxRate;
    }

    public function getTotal(): float {
        return $this->getSubtotal() + $this->getTax();
    }

    public function display(): void {
        echo "\n=== Cart for {$this->customerName} ===\n";
        foreach ($this->items as $item) {
            printf("%s x%d @ $%.2f = $%.2f\n",
                $item->productName,
                $item->quantity,
                $item->price,
                $item->getSubtotal()
            );
        }
        printf("\nSubtotal: $%.2f\n", $this->getSubtotal());
        printf("Tax (%.0f%%): $%.2f\n", $this->taxRate * 100, $this->getTax());
        printf("Total: $%.2f\n", $this->getTotal());
    }
}

// Create cart
$cart = new ShoppingCart("Kieu", 0.08); // 8% tax

// Add items
$cart->addItem("MacBook Pro", 1999.99);
$cart->addItem("Magic Mouse", 79.99, 2);
$cart->addItem("USB-C Cable", 19.99, 3);

// Display
$cart->display();

/*
Output:
Added: MacBook Pro x1
Added: Magic Mouse x2
Added: USB-C Cable x3

=== Cart for Kieu ===
MacBook Pro x1 @ $1999.99 = $1999.99
Magic Mouse x2 @ $79.99 = $159.98
USB-C Cable x3 @ $19.99 = $59.97

Subtotal: $2219.94
Tax (8%): $177.60
Total: $2397.54
*/
```

---

## Constructor Inheritance

When a class extends another, you can call the parent's constructor:

```php
<?php
class Animal {
    public function __construct(
        public string $name,
        public int $age
    ) {
        echo "Animal created: $name\n";
    }
}

class Dog extends Animal {
    public function __construct(
        string $name,
        int $age,
        public string $breed
    ) {
        // Call parent constructor
        parent::__construct($name, $age);
        echo "Dog breed: $breed\n";
    }
}

$dog = new Dog("Max", 3, "Labrador");
// Output:
// Animal created: Max
// Dog breed: Labrador

echo $dog->name;  // Max (from parent)
echo $dog->breed; // Labrador (from child)
```

**We'll cover inheritance in detail in Lesson 05!**

---

## Common Mistakes

### 1. Forgetting `$this->`

```php
<?php
class User {
    public string $name;

    public function __construct(string $name) {
        name = $name; // ❌ Creates local variable
    }
}

$user = new User("Kieu");
echo $user->name; // Undefined property

// ✅ Correct:
public function __construct(string $name) {
    $this->name = $name;
}
```

### 2. Adding Return Type

```php
<?php
// ❌ Wrong: constructors don't have return types
public function __construct(string $name): void {
    $this->name = $name;
}

// ✅ Correct: no return type
public function __construct(string $name) {
    $this->name = $name;
}
```

### 3. Mixing Promoted and Non-Promoted Wrong

```php
<?php
// ❌ Wrong: can't promote and declare separately
class User {
    public string $name;

    public function __construct(public string $name) {} // Error!
}

// ✅ Correct: choose one approach
class User {
    public function __construct(public string $name) {}
}

// Or:
class User {
    public string $name;

    public function __construct(string $name) {
        $this->name = $name;
    }
}
```

---

## Try It Yourself

### Exercise 1: Book Class with Constructor

Create a `Book` class with:
- Properties: title, author, pages, year, isRead (default false)
- Constructor that requires title, author, pages, year
- Validate: pages must be positive, year between 1000 and current year
- Methods:
  - `markAsRead()`
  - `getAge()` - returns current year - publication year
  - `display()`

### Exercise 2: Date Class

Create a `Date` class with:
- Constructor: `__construct(int $day, int $month, int $year)`
- Validate the date is valid (check days in month, leap years)
- Static factory methods:
  - `today()` - creates Date for today
  - `fromString(string $date)` - creates from "YYYY-MM-DD"
- Methods:
  - `format(string $format)` - formats the date
  - `isWeekend()` - checks if Saturday or Sunday

### Exercise 3: Rectangle with Factory Methods

Create a `Rectangle` class with:
- Constructor: `__construct(float $width, float $height)`
- Static factory methods:
  - `square(float $size)` - creates a square
  - `fromArea(float $area, float $aspectRatio)` - calculates dimensions
- Methods:
  - `getArea()`
  - `getPerimeter()`
  - `scale(float $factor)` - multiply dimensions

---

## Best Practices

1. **Keep constructors simple** - just initialize, don't do heavy work
2. **Validate early** - check parameters in constructor
3. **Use property promotion** - less code, same result (PHP 8+)
4. **Default values for optional parameters** - make them optional
5. **Factory methods for complex creation** - use static methods
6. **Document what's required** - make it clear what's needed

---

## Quick Reference

```php
<?php
// Basic constructor
class User {
    public string $name;

    public function __construct(string $name) {
        $this->name = $name;
    }
}

// Constructor with defaults
public function __construct(
    string $name,
    string $role = "user"
) {
    $this->name = $name;
    $this->role = $role;
}

// Property promotion (PHP 8+)
public function __construct(
    public string $name,
    public string $email
) {}

// Factory method
public static function fromEmail(string $email): self {
    return new self("Name", $email);
}

// Calling parent constructor
parent::__construct($param1, $param2);
```

---

## What's Next?

You now know how to properly initialize objects with constructors! But what if you want to hide some properties and only allow access through methods? What if you want to validate data before it's set?

In the next lesson, you'll learn about **encapsulation** - the principle of hiding internal details and controlling access to your object's data using `public`, `private`, and `protected`.

---

## Key Takeaways

- **Constructor** (`__construct`) runs automatically when creating an object
- Ensures all required data is provided upfront
- Can validate parameters and throw exceptions
- Can have default values for optional parameters
- **Property promotion** (PHP 8+) reduces boilerplate
- **Factory methods** provide alternative ways to create objects
- Objects should always be in a valid state after construction
