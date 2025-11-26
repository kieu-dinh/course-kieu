# Lesson 06 - Interfaces

## The Problem

Imagine you're building a payment system that supports multiple payment methods:

```php
<?php
class CreditCard {
    public function processCreditCardPayment(float $amount) {}
}

class PayPal {
    public function makePayPalPayment(float $amount) {}
}

class BankTransfer {
    public function executeBankTransfer(float $amount) {}
}

// How do you handle payments generically?
function processOrder($paymentMethod, float $amount) {
    // Different methods for each type!
    if ($paymentMethod instanceof CreditCard) {
        $paymentMethod->processCreditCardPayment($amount);
    } elseif ($paymentMethod instanceof PayPal) {
        $paymentMethod->makePayPalPayment($amount);
    } elseif ($paymentMethod instanceof BankTransfer) {
        $paymentMethod->executeBankTransfer($amount);
    }
    // This gets messy fast!
}
```

**Problems:**
- Each payment method has different method names
- Can't treat them uniformly
- Adding new payment method requires modifying processOrder()
- No guarantee classes have the required methods

---

## What is an Interface?

An **interface** is a **contract** that defines what methods a class must implement, without specifying how.

Think of it like a job description:
- Lists required skills (methods)
- Doesn't say how you learned those skills (implementation)
- Anyone meeting the requirements can apply (implement the interface)

**In OOP:**
- Interface declares method signatures
- Classes implement the interface by providing the actual code
- Multiple unrelated classes can implement the same interface
- Enables polymorphism (treating different objects the same way)

---

## Interface Syntax

```php
<?php
// Define an interface
interface PaymentInterface {
    // Method declarations only (no implementation)
    public function processPayment(float $amount): bool;
    public function refund(float $amount): bool;
    public function getTransactionId(): string;
}

// Implement the interface
class CreditCard implements PaymentInterface {
    // Must implement ALL methods from interface
    public function processPayment(float $amount): bool {
        echo "Processing credit card payment: $$amount\n";
        return true;
    }

    public function refund(float $amount): bool {
        echo "Refunding to credit card: $$amount\n";
        return true;
    }

    public function getTransactionId(): string {
        return "CC-" . uniqid();
    }
}

class PayPal implements PaymentInterface {
    public function processPayment(float $amount): bool {
        echo "Processing PayPal payment: $$amount\n";
        return true;
    }

    public function refund(float $amount): bool {
        echo "Refunding to PayPal: $$amount\n";
        return true;
    }

    public function getTransactionId(): string {
        return "PP-" . uniqid();
    }
}

// Now we can treat all payments the same way!
function processOrder(PaymentInterface $payment, float $amount) {
    if ($payment->processPayment($amount)) {
        echo "Transaction ID: " . $payment->getTransactionId() . "\n";
    }
}

// Works with any PaymentInterface implementation
$creditCard = new CreditCard();
$paypal = new PayPal();

processOrder($creditCard, 99.99); // Works!
processOrder($paypal, 149.99);    // Works!
```

**Key points:**
- Use `interface` keyword to define
- Use `implements` keyword in class
- All interface methods are automatically public
- Must implement ALL methods (or class must be abstract)
- Methods have no implementation in interface

---

## Real-World Example: Content Management

```php
<?php
interface Publishable {
    public function publish(): void;
    public function unpublish(): void;
    public function isPublished(): bool;
    public function getPublishedDate(): ?string;
}

class BlogPost implements Publishable {
    private bool $published = false;
    private ?string $publishedDate = null;

    public function __construct(
        public string $title,
        public string $content
    ) {}

    public function publish(): void {
        $this->published = true;
        $this->publishedDate = date('Y-m-d H:i:s');
        echo "Blog post published: $this->title\n";
    }

    public function unpublish(): void {
        $this->published = false;
        echo "Blog post unpublished: $this->title\n";
    }

    public function isPublished(): bool {
        return $this->published;
    }

    public function getPublishedDate(): ?string {
        return $this->publishedDate;
    }
}

class Video implements Publishable {
    private bool $published = false;
    private ?string $publishedDate = null;

    public function __construct(
        public string $title,
        public string $url,
        public int $duration
    ) {}

    public function publish(): void {
        $this->published = true;
        $this->publishedDate = date('Y-m-d H:i:s');
        echo "Video published: $this->title ($this->duration seconds)\n";
    }

    public function unpublish(): void {
        $this->published = false;
        echo "Video unpublished: $this->title\n";
    }

    public function isPublished(): bool {
        return $this->published;
    }

    public function getPublishedDate(): ?string {
        return $this->publishedDate;
    }
}

// Generic function works with ANY Publishable
function publishContent(Publishable $content): void {
    if (!$content->isPublished()) {
        $content->publish();
        echo "Published on: " . $content->getPublishedDate() . "\n";
    } else {
        echo "Already published!\n";
    }
}

// Works with different types
$post = new BlogPost("Learning Interfaces", "Interfaces are great...");
$video = new Video("PHP Tutorial", "https://example.com/video", 600);

publishContent($post);  // Blog post published: Learning Interfaces
publishContent($video); // Video published: PHP Tutorial
```

---

## Multiple Interfaces

A class can implement multiple interfaces (unlike inheritance where you can only extend one class):

```php
<?php
interface Loggable {
    public function log(string $message): void;
}

interface Cacheable {
    public function cache(): void;
    public function clearCache(): void;
}

interface Searchable {
    public function search(string $query): array;
}

// Implement multiple interfaces
class Article implements Loggable, Cacheable, Searchable {
    public function __construct(
        public string $title,
        public string $content
    ) {}

    // From Loggable
    public function log(string $message): void {
        echo "[LOG] $message\n";
    }

    // From Cacheable
    public function cache(): void {
        echo "Caching article: $this->title\n";
    }

    public function clearCache(): void {
        echo "Clearing cache for: $this->title\n";
    }

    // From Searchable
    public function search(string $query): array {
        if (str_contains($this->content, $query)) {
            return [$this->title];
        }
        return [];
    }
}

$article = new Article("PHP Guide", "Learn PHP interfaces...");

$article->log("Article created");
$article->cache();
$results = $article->search("interfaces");
print_r($results); // ["PHP Guide"]
```

---

## Interface Inheritance

Interfaces can extend other interfaces:

```php
<?php
interface Animal {
    public function eat(): void;
    public function sleep(): void;
}

interface Pet extends Animal {
    public function play(): void;
    public function getName(): string;
}

// Must implement methods from both Animal and Pet
class Dog implements Pet {
    public function __construct(
        private string $name
    ) {}

    // From Animal
    public function eat(): void {
        echo "$this->name is eating\n";
    }

    public function sleep(): void {
        echo "$this->name is sleeping\n";
    }

    // From Pet
    public function play(): void {
        echo "$this->name is playing\n";
    }

    public function getName(): string {
        return $this->name;
    }

    // Can add own methods too
    public function bark(): void {
        echo "$this->name says: Woof!\n";
    }
}

$dog = new Dog("Max");
$dog->eat();   // From Animal
$dog->play();  // From Pet
$dog->bark();  // Own method
```

---

## Interface Constants

Interfaces can have constants (but not properties):

```php
<?php
interface PaymentStatus {
    public const PENDING = 'pending';
    public const COMPLETED = 'completed';
    public const FAILED = 'failed';
    public const REFUNDED = 'refunded';
}

class Payment implements PaymentStatus {
    private string $status = self::PENDING;

    public function complete(): void {
        $this->status = self::COMPLETED;
        echo "Payment status: " . $this->status . "\n";
    }

    public function fail(): void {
        $this->status = self::FAILED;
    }

    public function getStatus(): string {
        return $this->status;
    }
}

$payment = new Payment();
echo $payment->getStatus(); // "pending"
$payment->complete();       // "Payment status: completed"

// Can also access via interface
echo PaymentStatus::PENDING; // "pending"
```

---

## Type Hinting with Interfaces

Interfaces are perfect for type hints:

```php
<?php
interface Logger {
    public function log(string $level, string $message): void;
}

class FileLogger implements Logger {
    public function log(string $level, string $message): void {
        file_put_contents('app.log', "[$level] $message\n", FILE_APPEND);
    }
}

class DatabaseLogger implements Logger {
    public function log(string $level, string $message): void {
        // Insert into database
        echo "Logging to database: [$level] $message\n";
    }
}

// Function accepts ANY Logger
class Application {
    public function __construct(
        private Logger $logger // Type hint to interface!
    ) {}

    public function run(): void {
        $this->logger->log('INFO', 'Application started');

        try {
            // Do stuff
            $this->logger->log('INFO', 'Processing...');
        } catch (Exception $e) {
            $this->logger->log('ERROR', $e->getMessage());
        }
    }
}

// Can inject any Logger implementation
$app1 = new Application(new FileLogger());
$app1->run(); // Logs to file

$app2 = new Application(new DatabaseLogger());
$app2->run(); // Logs to database
```

**This is Dependency Injection - a key design pattern!**

---

## Practical Example: E-commerce

```php
<?php
interface Product {
    public function getName(): string;
    public function getPrice(): float;
    public function isAvailable(): bool;
}

interface Shippable {
    public function getWeight(): float;
    public function getShippingCost(): float;
    public function requiresSignature(): bool;
}

interface Downloadable {
    public function getDownloadUrl(): string;
    public function getFileSize(): int;
    public function getDownloadLimit(): int;
}

// Physical product
class Book implements Product, Shippable {
    public function __construct(
        private string $title,
        private float $price,
        private float $weight,
        private bool $inStock
    ) {}

    public function getName(): string { return $this->title; }
    public function getPrice(): float { return $this->price; }
    public function isAvailable(): bool { return $this->inStock; }

    public function getWeight(): float { return $this->weight; }
    public function getShippingCost(): float { return $this->weight * 2; }
    public function requiresSignature(): bool { return $this->price > 100; }
}

// Digital product
class EBook implements Product, Downloadable {
    public function __construct(
        private string $title,
        private float $price,
        private int $fileSize
    ) {}

    public function getName(): string { return $this->title; }
    public function getPrice(): float { return $this->price; }
    public function isAvailable(): bool { return true; } // Always available

    public function getDownloadUrl(): string { return "https://downloads.example.com/{$this->title}.pdf"; }
    public function getFileSize(): int { return $this->fileSize; }
    public function getDownloadLimit(): int { return 5; }
}

class ShoppingCart {
    private array $items = [];

    public function addProduct(Product $product): void {
        $this->items[] = $product;
        echo "Added: {$product->getName()}\n";
    }

    public function calculateTotal(): float {
        $total = 0;

        foreach ($this->items as $item) {
            $total += $item->getPrice();

            // Add shipping if applicable
            if ($item instanceof Shippable) {
                $total += $item->getShippingCost();
            }
        }

        return $total;
    }

    public function checkout(): void {
        echo "\n=== Checkout ===\n";

        foreach ($this->items as $item) {
            echo "{$item->getName()} - \${$item->getPrice()}\n";

            if ($item instanceof Shippable) {
                echo "  + Shipping: \${$item->getShippingCost()}\n";
                if ($item->requiresSignature()) {
                    echo "  ⚠ Requires signature\n";
                }
            }

            if ($item instanceof Downloadable) {
                echo "  📥 Download: {$item->getDownloadUrl()}\n";
                echo "  💾 Size: " . ($item->getFileSize() / 1024 / 1024) . " MB\n";
            }
        }

        echo "\nTotal: \$" . $this->calculateTotal() . "\n";
    }
}

// Usage
$cart = new ShoppingCart();
$cart->addProduct(new Book("PHP Mastery", 49.99, 0.8, true));
$cart->addProduct(new EBook("Laravel Guide", 29.99, 5242880));
$cart->addProduct(new Book("Clean Code", 59.99, 1.2, true));

$cart->checkout();

/*
Output:
Added: PHP Mastery
Added: Laravel Guide
Added: Clean Code

=== Checkout ===
PHP Mastery - $49.99
  + Shipping: $1.6
Laravel Guide - $29.99
  📥 Download: https://downloads.example.com/Laravel Guide.pdf
  💾 Size: 5 MB
Clean Code - $59.99
  + Shipping: $2.4

Total: $144.01
*/
```

---

## Interfaces vs Abstract Classes

Both define contracts, but there are key differences:

| Feature | Interface | Abstract Class |
|---------|-----------|----------------|
| Methods | Only signatures | Can have implementations |
| Properties | Only constants | Can have regular properties |
| Multiple | Class can implement many | Can only extend one |
| Constructor | Cannot have | Can have |
| Purpose | Define contract | Share code + define contract |

```php
<?php
// Interface: pure contract
interface Drivable {
    public function drive(): void;
    // No implementation, no properties
}

// Abstract class: partial implementation
abstract class Vehicle {
    protected string $brand; // Can have properties

    public function __construct(string $brand) { // Can have constructor
        $this->brand = $brand;
    }

    abstract public function drive(): void; // Must implement

    public function getBrand(): string { // Can have full implementation
        return $this->brand;
    }
}

// Can implement multiple interfaces
class Car implements Drivable, Rentable, Insurable {
    // Must implement all interface methods
}

// Can only extend one abstract class
class Car extends Vehicle {
    // Gets properties and methods from Vehicle
}
```

**Rule of thumb:**
- Use **interfaces** when you want to define "can do" capabilities
- Use **abstract classes** when you want to share code among related classes

---

## When to Use Interfaces

### ✅ Good Use Cases

**1. Defining Contracts for Different Implementations**
```php
<?php
interface Cache {
    public function get(string $key);
    public function set(string $key, $value): void;
}

class RedisCache implements Cache { /* ... */ }
class FileCache implements Cache { /* ... */ }
class MemoryCache implements Cache { /* ... */ }
```

**2. Dependency Injection**
```php
<?php
class UserController {
    public function __construct(
        private UserRepository $users,  // Interface
        private Logger $logger           // Interface
    ) {}
}
// Easy to swap implementations for testing!
```

**3. Polymorphism**
```php
<?php
function sendNotification(Notifiable $user) {
    // Works with any Notifiable implementation
}
```

**4. Multiple Capabilities**
```php
<?php
class Article implements Publishable, Commentable, Shareable, Searchable {
    // Can do many different things
}
```

---

## Best Practices

### 1. Name Interfaces as Adjectives or Capabilities

```php
<?php
// ✅ Good: describes capability
interface Payable {}
interface Drivable {}
interface Cacheable {}
interface Serializable {}

// ❌ Bad: too generic
interface Data {}
interface Thing {}
```

### 2. Keep Interfaces Small and Focused

```php
<?php
// ❌ Bad: too many responsibilities
interface God {
    public function create();
    public function read();
    public function update();
    public function delete();
    public function export();
    public function import();
    public function validate();
    public function transform();
}

// ✅ Good: single responsibility
interface Readable {
    public function read();
}

interface Writable {
    public function create();
    public function update();
    public function delete();
}
```

### 3. Use Interface Type Hints

```php
<?php
// ✅ Good: depend on interface
public function process(PaymentInterface $payment) {}

// ❌ Bad: depend on concrete class
public function process(CreditCard $payment) {}
```

### 4. Interface Segregation

```php
<?php
// ❌ Bad: forcing implementations to have methods they don't need
interface Worker {
    public function work();
    public function eat(); // Not all workers eat!
}

// ✅ Good: separate concerns
interface Workable {
    public function work();
}

interface Eatable {
    public function eat();
}

class Human implements Workable, Eatable {}
class Robot implements Workable {} // Doesn't need to eat
```

---

## Try It Yourself

### Exercise 1: Notification System

Create interfaces and implementations:
- Interface `Notifiable`: `send(string $message): bool`
- Classes: `EmailNotifier`, `SmsNotifier`, `PushNotifier`
- Function `notifyUser(Notifiable $notifier, string $message)`
- Test with all three implementations

### Exercise 2: Storage System

Create:
- Interface `Storage`: `save($data, string $key)`, `load(string $key)`, `delete(string $key)`, `exists(string $key): bool`
- Classes: `FileStorage`, `DatabaseStorage`, `MemoryStorage`
- Each with different implementation
- Create a generic `FileManager` class that uses `Storage` interface

### Exercise 3: Shape Area Calculator

Create:
- Interface `Shape`: `getArea(): float`, `getPerimeter(): float`
- Classes: `Circle`, `Rectangle`, `Triangle` (all implement Shape)
- Function `calculateTotalArea(array $shapes): float` that accepts array of Shape objects
- Create multiple shapes and calculate total

---

## Common Mistakes

### 1. Implementing Method with Different Signature

```php
<?php
interface Loggable {
    public function log(string $message): void;
}

// ❌ Wrong: changed signature
class FileLogger implements Loggable {
    public function log(string $message, string $level): void {} // Added parameter
}

// ✅ Correct: exact signature
class FileLogger implements Loggable {
    public function log(string $message): void {}
}
```

### 2. Forgetting to Implement All Methods

```php
<?php
interface PaymentInterface {
    public function pay(float $amount): bool;
    public function refund(float $amount): bool;
}

// ❌ Wrong: missing refund()
class CreditCard implements PaymentInterface {
    public function pay(float $amount): bool {
        return true;
    }
    // Error: must implement refund() too!
}
```

### 3. Adding Implementation to Interface

```php
<?php
// ❌ Wrong: interfaces can't have implementation
interface Loggable {
    public function log(string $message): void {
        echo $message; // Error!
    }
}

// ✅ Correct: declaration only
interface Loggable {
    public function log(string $message): void;
}
```

---

## Quick Reference

```php
<?php
// Define interface
interface InterfaceName {
    public const CONSTANT = 'value';
    public function method(string $param): returnType;
}

// Implement interface
class ClassName implements InterfaceName {
    public function method(string $param): returnType {
        // Implementation
    }
}

// Multiple interfaces
class ClassName implements Interface1, Interface2, Interface3 {}

// Interface inheritance
interface ChildInterface extends ParentInterface {}

// Type hint to interface
function process(InterfaceName $obj) {}

// Check if implements interface
$obj instanceof InterfaceName; // true/false
```

---

## What's Next?

Interfaces define what methods a class must have, but sometimes you want to share some implementation while still requiring certain methods. That's where **abstract classes** come in!

In the next lesson, you'll learn about abstract classes - a hybrid between regular classes and interfaces that allows partial implementation.

---

## Key Takeaways

- **Interface** = contract defining method signatures
- Classes `implement` interfaces and must provide all methods
- Enables polymorphism (treating different objects uniformly)
- A class can implement multiple interfaces
- Interfaces can extend other interfaces
- Cannot have properties (only constants) or method implementations
- Use for "can do" capabilities
- Type hint to interfaces for flexibility
- Keep interfaces small and focused
- Name as adjectives or capabilities (-able, -ible)
