# Lesson 04 - Encapsulation

## The Problem with Public Everything

So far, all our properties have been `public`, meaning anyone can access and change them:

```php
<?php
class BankAccount {
    public float $balance = 0;
}

$account = new BankAccount();
$account->balance = 1000;

// Nothing stops this:
$account->balance = -5000; // Negative balance!
$account->balance = "hacked"; // Not even a number!
```

**Problems:**
- No validation
- No control over changes
- Internal implementation exposed
- Easy to break the object's state

---

## What is Encapsulation?

**Encapsulation** is the principle of hiding an object's internal details and controlling access through methods.

Think of it like a car:
- You don't directly touch the engine (private)
- You use the steering wheel, pedals, buttons (public interface)
- The car controls HOW the engine responds

**In OOP:**
- Hide internal data (`private`)
- Provide controlled access through methods (`public`)
- Validate before changing anything

---

## Visibility Modifiers

PHP has three visibility levels:

| Modifier | Access From |
|----------|-------------|
| `public` | Everywhere - inside class, outside class, child classes |
| `private` | Only inside the same class |
| `protected` | Inside the class and child classes (inheritance) |

```php
<?php
class Example {
    public string $publicProp;      // Anyone can access
    private string $privateProp;    // Only this class
    protected string $protectedProp; // This class and children

    public function publicMethod() {}     // Anyone can call
    private function privateMethod() {}   // Only internal use
    protected function protectedMethod() {} // This class and children
}
```

---

## Private Properties

Let's fix the bank account:

```php
<?php
class BankAccount {
    private float $balance = 0; // Can't access directly from outside

    public function deposit(float $amount): void {
        if ($amount <= 0) {
            throw new Exception("Deposit amount must be positive");
        }
        $this->balance += $amount;
        echo "Deposited $$amount. Balance: $$this->balance\n";
    }

    public function withdraw(float $amount): bool {
        if ($amount <= 0) {
            echo "Withdrawal amount must be positive\n";
            return false;
        }

        if ($amount > $this->balance) {
            echo "Insufficient funds\n";
            return false;
        }

        $this->balance -= $amount;
        echo "Withdrew $$amount. Balance: $$this->balance\n";
        return true;
    }

    public function getBalance(): float {
        return $this->balance;
    }
}

$account = new BankAccount();
$account->deposit(1000); // ✅ OK - uses method with validation

// ❌ Can't do this anymore:
// $account->balance = -5000; // Error: Cannot access private property

// ✅ Must use methods:
echo $account->getBalance(); // 1000
$account->withdraw(300);     // OK
$account->withdraw(5000);    // Insufficient funds
```

**Benefits:**
- Balance can never be invalid
- All changes go through validation
- Can add logging, notifications, etc.
- Can change internal implementation without breaking code using the class

---

## Getters and Setters

**Getters** read private properties. **Setters** write to them with validation.

```php
<?php
class User {
    private string $email;
    private int $age;

    public function __construct(string $email, int $age) {
        $this->setEmail($email); // Use setter for validation
        $this->setAge($age);
    }

    // Getter for email
    public function getEmail(): string {
        return $this->email;
    }

    // Setter for email with validation
    public function setEmail(string $email): void {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format");
        }
        $this->email = $email;
    }

    // Getter for age
    public function getAge(): int {
        return $this->age;
    }

    // Setter for age with validation
    public function setAge(int $age): void {
        if ($age < 0 || $age > 150) {
            throw new Exception("Age must be between 0 and 150");
        }
        $this->age = $age;
    }
}

$user = new User("kieu@example.com", 28);

// Get values
echo $user->getEmail(); // kieu@example.com
echo $user->getAge();   // 28

// Set with validation
$user->setEmail("newemail@example.com"); // ✅ Valid
$user->setAge(29); // ✅ Valid

// These will throw exceptions:
// $user->setEmail("not-an-email"); // Invalid email format
// $user->setAge(-5); // Age must be between 0 and 150
```

---

## When to Use Getters/Setters

### ❌ Don't Write Pointless Getters/Setters

```php
<?php
// ❌ Bad: just wrapping without any logic
class User {
    private string $name;

    public function getName(): string {
        return $this->name; // Just returning, no logic
    }

    public function setName(string $name): void {
        $this->name = $name; // Just setting, no validation
    }
}

// Why not just make it public?
```

### ✅ Use Them When You Need:

1. **Validation**
```php
<?php
public function setAge(int $age): void {
    if ($age < 0) {
        throw new Exception("Age cannot be negative");
    }
    $this->age = $age;
}
```

2. **Transformation**
```php
<?php
public function setEmail(string $email): void {
    $this->email = strtolower(trim($email)); // Normalize
}
```

3. **Computed Values**
```php
<?php
private string $firstName;
private string $lastName;

public function getFullName(): string {
    return "$this->firstName $this->lastName"; // Computed
}
```

4. **Side Effects**
```php
<?php
public function setPassword(string $password): void {
    $this->password = password_hash($password, PASSWORD_DEFAULT);
    $this->sendPasswordChangedEmail(); // Side effect
}
```

5. **Logging/Tracking**
```php
<?php
public function getBalance(): float {
    $this->logAccess("Balance accessed"); // Track access
    return $this->balance;
}
```

---

## Read-Only Properties

Sometimes you want a property that can be set once but never changed:

```php
<?php
class Order {
    private string $id;
    private string $createdAt;
    private float $total;

    public function __construct(float $total) {
        $this->id = uniqid('order_');
        $this->createdAt = date('Y-m-d H:i:s');
        $this->total = $total;
    }

    // Getters only - no setters!
    public function getId(): string {
        return $this->id;
    }

    public function getCreatedAt(): string {
        return $this->createdAt;
    }

    public function getTotal(): float {
        return $this->total;
    }
}

$order = new Order(99.99);
echo $order->getId();        // order_abc123
echo $order->getCreatedAt(); // 2024-01-15 14:30:00

// Can't change these - no setters exist!
// $order->setId("hacked"); // Method doesn't exist
```

---

## Readonly Keyword (PHP 8.1+)

PHP 8.1 added a `readonly` keyword for properties:

```php
<?php
class Order {
    public function __construct(
        public readonly string $id,
        public readonly string $createdAt,
        public readonly float $total
    ) {}
}

$order = new Order("order_123", "2024-01-15", 99.99);

echo $order->id; // Can read
// $order->id = "new_id"; // Error: Cannot modify readonly property

// Readonly properties:
// - Can be set once (in constructor)
// - Cannot be changed after
// - Must have a type
```

---

## Private Methods

Methods can be private too, for internal use only:

```php
<?php
class PasswordManager {
    private string $password;

    public function setPassword(string $password): void {
        if (!$this->isPasswordStrong($password)) {
            throw new Exception("Password is too weak");
        }

        $this->password = $this->hashPassword($password);
    }

    public function checkPassword(string $password): bool {
        return $this->password === $this->hashPassword($password);
    }

    // Private: only used internally
    private function isPasswordStrong(string $password): bool {
        if (strlen($password) < 8) return false;
        if (!preg_match('/[A-Z]/', $password)) return false;
        if (!preg_match('/[a-z]/', $password)) return false;
        if (!preg_match('/[0-9]/', $password)) return false;
        return true;
    }

    // Private: internal implementation detail
    private function hashPassword(string $password): string {
        return hash('sha256', $password . 'secret_salt');
    }
}

$pm = new PasswordManager();
$pm->setPassword("StrongPass123"); // ✅ OK

// ❌ Can't call private methods:
// $pm->hashPassword("test"); // Error: Cannot access private method

// ✅ But can use public interface:
if ($pm->checkPassword("StrongPass123")) {
    echo "Password correct!";
}
```

**Why private methods?**
- Hide implementation details
- Can change them without breaking external code
- Keep public API clean and simple

---

## Protected: For Inheritance

`protected` is between `public` and `private`. You can access it in the class AND child classes:

```php
<?php
class Animal {
    protected string $name; // Child classes can access

    public function __construct(string $name) {
        $this->name = $name;
    }

    protected function makeSound(): string {
        return "Some sound";
    }
}

class Dog extends Animal {
    public function bark(): void {
        // ✅ Can access protected property from parent
        echo "$this->name says: ";

        // ✅ Can call protected method from parent
        echo $this->makeSound() . "\n";
    }

    protected function makeSound(): string {
        return "Woof!"; // Override parent's protected method
    }
}

$dog = new Dog("Max");
$dog->bark(); // Max says: Woof!

// ❌ Can't access from outside:
// echo $dog->name; // Error: Cannot access protected property
// $dog->makeSound(); // Error: Cannot access protected method
```

**We'll cover inheritance in detail in the next lesson!**

---

## Practical Example: Product Class

```php
<?php
class Product {
    private string $name;
    private float $price;
    private int $stock;
    private float $discountPercent = 0;

    public function __construct(string $name, float $price, int $stock) {
        $this->setName($name);
        $this->setPrice($price);
        $this->setStock($stock);
    }

    // Getters
    public function getName(): string {
        return $this->name;
    }

    public function getPrice(): float {
        return $this->calculateFinalPrice();
    }

    public function getStock(): int {
        return $this->stock;
    }

    public function getDiscountPercent(): float {
        return $this->discountPercent;
    }

    // Setters with validation
    public function setName(string $name): void {
        if (empty($name)) {
            throw new Exception("Product name cannot be empty");
        }
        $this->name = $name;
    }

    public function setPrice(float $price): void {
        if ($price < 0) {
            throw new Exception("Price cannot be negative");
        }
        $this->price = $price;
    }

    public function setStock(int $stock): void {
        if ($stock < 0) {
            throw new Exception("Stock cannot be negative");
        }
        $this->stock = $stock;
    }

    public function applyDiscount(float $percent): void {
        if ($percent < 0 || $percent > 100) {
            throw new Exception("Discount must be between 0 and 100");
        }
        $this->discountPercent = $percent;
        echo "Applied {$percent}% discount to {$this->name}\n";
    }

    // Public methods
    public function sell(int $quantity): bool {
        if ($quantity > $this->stock) {
            echo "Not enough stock! Available: {$this->stock}\n";
            return false;
        }

        $this->stock -= $quantity;
        $total = $this->calculateFinalPrice() * $quantity;

        echo "Sold {$quantity} x {$this->name} for $" . number_format($total, 2) . "\n";
        echo "Remaining stock: {$this->stock}\n";

        return true;
    }

    public function restock(int $quantity): void {
        if ($quantity <= 0) {
            throw new Exception("Restock quantity must be positive");
        }
        $this->stock += $quantity;
        echo "Restocked {$quantity} units. New stock: {$this->stock}\n";
    }

    public function isAvailable(): bool {
        return $this->stock > 0;
    }

    // Private helper method
    private function calculateFinalPrice(): float {
        if ($this->discountPercent > 0) {
            return $this->price * (1 - $this->discountPercent / 100);
        }
        return $this->price;
    }
}

// Usage
$laptop = new Product("MacBook Pro", 1999.99, 5);

// Can't access private properties:
// echo $laptop->price; // Error!

// Must use getters:
echo "Price: $" . $laptop->getPrice() . "\n"; // Price: $1999.99

// Apply discount
$laptop->applyDiscount(10); // 10% off
echo "Discounted price: $" . $laptop->getPrice() . "\n"; // $1799.99

// Sell products
$laptop->sell(2); // Sold 2 x MacBook Pro for $3599.98
$laptop->sell(5); // Not enough stock! Available: 3

// Restock
$laptop->restock(10); // Restocked 10 units. New stock: 13

// Try invalid operations:
// $laptop->setPrice(-100); // Exception: Price cannot be negative
// $laptop->applyDiscount(150); // Exception: Discount must be between 0 and 100
```

---

## Best Practices

### 1. Start with Private, Make Public Only When Needed

```php
<?php
// ✅ Good: private by default
class User {
    private string $email;

    public function getEmail(): string {
        return $this->email;
    }
}

// ❌ Bad: public by default
class User {
    public string $email; // Anyone can change it!
}
```

### 2. Don't Expose Internal Implementation

```php
<?php
// ❌ Bad: exposing database connection
class UserRepository {
    public PDO $db; // Now everyone depends on PDO!
}

// ✅ Good: hide implementation
class UserRepository {
    private PDO $db;

    public function findById(int $id): ?User {
        // Use $this->db internally
    }
}
```

### 3. Validate in Setters

```php
<?php
// ✅ Good: always validate
public function setAge(int $age): void {
    if ($age < 0 || $age > 150) {
        throw new Exception("Invalid age");
    }
    $this->age = $age;
}

// ❌ Bad: no validation
public function setAge(int $age): void {
    $this->age = $age; // Could be anything!
}
```

### 4. Use Readonly for Immutable Data

```php
<?php
// ✅ Good: can't change after creation
class Order {
    public function __construct(
        public readonly string $id,
        public readonly DateTime $createdAt
    ) {}
}

// Instead of:
class Order {
    private string $id;
    private DateTime $createdAt;

    public function __construct(string $id, DateTime $createdAt) {
        $this->id = $id;
        $this->createdAt = $createdAt;
    }

    public function getId(): string { return $this->id; }
    public function getCreatedAt(): DateTime { return $this->createdAt; }
}
```

---

## Common Mistakes

### 1. Making Everything Private

```php
<?php
// ❌ Too restrictive
class Product {
    private string $name;

    private function getName(): string { // Why is this private?
        return $this->name;
    }
}

// ✅ Balance: private data, public interface
class Product {
    private string $name;

    public function getName(): string {
        return $this->name;
    }
}
```

### 2. Getters/Setters for Everything

```php
<?php
// ❌ Pointless encapsulation
class Point {
    private int $x;
    private int $y;

    public function getX(): int { return $this->x; }
    public function setX(int $x): void { $this->x = $x; }
    public function getY(): int { return $this->y; }
    public function setY(int $y): void { $this->y = $y; }
}

// ✅ Just make them public if no validation needed
class Point {
    public function __construct(
        public int $x,
        public int $y
    ) {}
}
```

### 3. Breaking Encapsulation with Getters

```php
<?php
// ❌ Returning mutable reference
class Cart {
    private array $items = [];

    public function getItems(): array {
        return $this->items; // Danger! They can modify it
    }
}

$cart = new Cart();
$items = $cart->getItems();
$items[] = "hacked"; // This modifies the internal array!

// ✅ Return copy or use better methods
class Cart {
    private array $items = [];

    public function getItems(): array {
        return [...$this->items]; // Return copy
    }

    public function addItem(string $item): void {
        $this->items[] = $item; // Controlled way to add
    }
}
```

---

## Try It Yourself

### Exercise 1: Email Validator

Create an `EmailValidator` class with:
- Private property: `emails` (array of validated emails)
- Method `add(string $email)` - validates and adds email
- Method `remove(string $email)` - removes email
- Method `has(string $email): bool` - checks if email exists
- Method `getAll(): array` - returns all emails
- Method `count(): int` - returns count
- Private method `isValid(string $email): bool` - validates format

### Exercise 2: Temperature Class

Create a `Temperature` class with:
- Private property storing Celsius value
- Constructor takes Celsius value
- Setter validates temperature is above absolute zero (-273.15°C)
- Getters: `getCelsius()`, `getFahrenheit()`, `getKelvin()`
- No setters for Fahrenheit/Kelvin (calculated from Celsius)
- Method `compare(Temperature $other): int` - returns -1, 0, or 1

### Exercise 3: Bank Account with Transaction History

Create a `BankAccount` class with:
- Private properties: balance, accountNumber, transactions (array)
- Constructor takes accountNumber and initialBalance
- Methods: `deposit($amount)`, `withdraw($amount)`
- Each transaction is stored with timestamp, type, amount
- Method `getBalance(): float`
- Method `getTransactionHistory(): array` - returns copy of transactions
- Private method `addTransaction(string $type, float $amount)`
- Validate all amounts are positive

---

## Quick Reference

```php
<?php
class Example {
    // Visibility modifiers
    public $anyoneCanAccess;
    private $onlyThisClass;
    protected $thisClassAndChildren;

    // Getter
    public function getValue(): type {
        return $this->privateProperty;
    }

    // Setter with validation
    public function setValue(type $value): void {
        if (/* validation */) {
            throw new Exception("Invalid");
        }
        $this->privateProperty = $value;
    }

    // Private helper method
    private function internalHelper(): void {
        // Only used inside this class
    }

    // Readonly property (PHP 8.1+)
    public readonly string $immutable;
}
```

---

## What's Next?

You now understand how to protect your class's data with encapsulation! But what if you want to reuse code from one class in another? What if you have a base class and want to extend it?

In the next lesson, you'll learn about **inheritance** - how to create child classes that inherit properties and methods from parent classes, allowing powerful code reuse.

---

## Key Takeaways

- **Encapsulation** = hiding internal details, exposing controlled interface
- **Public** = accessible everywhere
- **Private** = only inside the class
- **Protected** = inside class and children (inheritance)
- Use **getters** to read private properties
- Use **setters** to validate before writing
- Make properties `readonly` when they shouldn't change
- Private methods hide implementation details
- Start private, make public only when necessary
- Don't write getters/setters without purpose
