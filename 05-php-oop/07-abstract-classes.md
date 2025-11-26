# Lesson 07 - Abstract Classes

## The Problem

You learned that interfaces define contracts (what methods must exist), and inheritance shares code. But what if you want **both**?

```php
<?php
// With interface: all implementations repeat code
interface Shape {
    public function getArea(): float;
    public function getPerimeter(): float;
    public function display(): void;
}

class Circle implements Shape {
    public function __construct(
        public string $color, // Every shape has color
        public float $radius
    ) {}

    public function getArea(): float {
        return pi() * $this->radius ** 2;
    }

    public function getPerimeter(): float {
        return 2 * pi() * $this->radius;
    }

    // This display logic is the SAME for all shapes
    public function display(): void {
        echo "A {$this->color} shape\n";
        echo "Area: {$this->getArea()}\n";
        echo "Perimeter: {$this->getPerimeter()}\n";
    }
}

class Rectangle implements Shape {
    public function __construct(
        public string $color, // Duplicated!
        public float $width,
        public float $height
    ) {}

    public function getArea(): float {
        return $this->width * $this->height;
    }

    public function getPerimeter(): float {
        return 2 * ($this->width + $this->height);
    }

    // Same display logic - duplicated!
    public function display(): void {
        echo "A {$this->color} shape\n";
        echo "Area: {$this->getArea()}\n";
        echo "Perimeter: {$this->getPerimeter()}\n";
    }
}
```

**Problem:** We're duplicating the `display()` method and `$color` property!

---

## What is an Abstract Class?

An **abstract class** is a class that:
- Cannot be instantiated directly (can't do `new AbstractClass()`)
- Can have both implemented methods (like normal classes) AND abstract methods (like interfaces)
- Serves as a base class that children must extend
- Shares code while enforcing certain methods must be implemented

Think of it like a template with some blanks to fill in:
- Template provides common structure and some complete parts
- You must fill in the blanks (implement abstract methods)
- Can't use the template directly, must create specific version

---

## Abstract Class Syntax

```php
<?php
// Abstract class - cannot instantiate
abstract class Shape {
    // Regular property - all children get this
    protected string $color;

    // Regular constructor - all children get this
    public function __construct(string $color) {
        $this->color = $color;
    }

    // Abstract methods - children MUST implement these
    abstract public function getArea(): float;
    abstract public function getPerimeter(): float;

    // Regular method - all children inherit this
    public function display(): void {
        echo "A {$this->color} shape\n";
        echo "Area: {$this->getArea()}\n"; // Calls child's implementation
        echo "Perimeter: {$this->getPerimeter()}\n";
    }

    public function setColor(string $color): void {
        $this->color = $color;
    }
}

// Child must implement abstract methods
class Circle extends Shape {
    public function __construct(
        string $color,
        private float $radius
    ) {
        parent::__construct($color);
    }

    // Must implement
    public function getArea(): float {
        return pi() * $this->radius ** 2;
    }

    // Must implement
    public function getPerimeter(): float {
        return 2 * pi() * $this->radius;
    }

    // Inherits display() - no need to rewrite!
}

class Rectangle extends Shape {
    public function __construct(
        string $color,
        private float $width,
        private float $height
    ) {
        parent::__construct($color);
    }

    public function getArea(): float {
        return $this->width * $this->height;
    }

    public function getPerimeter(): float {
        return 2 * ($this->width + $this->height);
    }
}

// Usage
// $shape = new Shape("red"); // ❌ Error: Cannot instantiate abstract class

$circle = new Circle("red", 5);
$circle->display();
// Output:
// A red shape
// Area: 78.539816339745
// Perimeter: 31.415926535898

$rectangle = new Rectangle("blue", 4, 6);
$rectangle->display(); // Same display method works!
```

**Key points:**
- Use `abstract class` keyword
- Use `abstract` keyword for methods that children must implement
- Abstract methods have no body (no `{}`)
- Can mix abstract and concrete methods
- Children must implement ALL abstract methods
- Cannot create instance of abstract class directly

---

## Real-World Example: Database Connection

```php
<?php
abstract class DatabaseConnection {
    protected string $host;
    protected string $database;
    protected string $username;
    protected string $password;
    protected $connection;

    public function __construct(string $host, string $database, string $username, string $password) {
        $this->host = $host;
        $this->database = $database;
        $this->username = $username;
        $this->password = $password;
    }

    // Abstract: each database connects differently
    abstract public function connect(): void;
    abstract public function disconnect(): void;
    abstract public function query(string $sql): array;

    // Concrete: same for all databases
    public function isConnected(): bool {
        return $this->connection !== null;
    }

    public function getDatabase(): string {
        return $this->database;
    }

    // Template method pattern
    public function executeQuery(string $sql): array {
        if (!$this->isConnected()) {
            $this->connect();
        }

        $results = $this->query($sql);

        // Could add logging, caching, etc. here
        $this->log("Executed: $sql");

        return $results;
    }

    protected function log(string $message): void {
        echo "[" . date('Y-m-d H:i:s') . "] $message\n";
    }
}

class MySQLConnection extends DatabaseConnection {
    public function connect(): void {
        echo "Connecting to MySQL: {$this->host}/{$this->database}\n";
        // $this->connection = new PDO(...);
        $this->connection = "mysql_connection";
    }

    public function disconnect(): void {
        echo "Disconnecting from MySQL\n";
        $this->connection = null;
    }

    public function query(string $sql): array {
        echo "MySQL query: $sql\n";
        return []; // Simulated results
    }
}

class PostgreSQLConnection extends DatabaseConnection {
    public function connect(): void {
        echo "Connecting to PostgreSQL: {$this->host}/{$this->database}\n";
        $this->connection = "pgsql_connection";
    }

    public function disconnect(): void {
        echo "Disconnecting from PostgreSQL\n";
        $this->connection = null;
    }

    public function query(string $sql): array {
        echo "PostgreSQL query: $sql\n";
        return [];
    }
}

// Usage
$mysql = new MySQLConnection("localhost", "mydb", "root", "password");
$mysql->executeQuery("SELECT * FROM users");
// Output:
// Connecting to MySQL: localhost/mydb
// MySQL query: SELECT * FROM users
// [2024-01-15 14:30:00] Executed: SELECT * FROM users

$postgres = new PostgreSQLConnection("localhost", "mydb", "admin", "password");
$postgres->executeQuery("SELECT * FROM products");
// Same interface, different implementation!
```

---

## Abstract Methods Rules

```php
<?php
abstract class Example {
    // ✅ Valid: abstract method with return type
    abstract public function method1(): string;

    // ✅ Valid: abstract method with parameters
    abstract public function method2(int $id, string $name): bool;

    // ✅ Valid: abstract method with no return
    abstract public function method3(): void;

    // ❌ Invalid: abstract methods can't have body
    // abstract public function method4() {
    //     echo "Hello"; // Error!
    // }

    // ❌ Invalid: abstract methods can't be private
    // abstract private function method5();

    // ✅ Valid: can be protected
    abstract protected function method6();
}
```

---

## Combining with Interfaces

A class can extend one abstract class AND implement multiple interfaces:

```php
<?php
interface Loggable {
    public function log(string $message): void;
}

interface Cacheable {
    public function cache(): void;
}

abstract class Model {
    protected int $id;

    abstract public function save(): bool;
    abstract public function delete(): bool;

    public function getId(): int {
        return $this->id;
    }
}

// Extends abstract class AND implements interfaces
class User extends Model implements Loggable, Cacheable {
    public function __construct(
        public string $name,
        public string $email
    ) {}

    // From Model (abstract)
    public function save(): bool {
        echo "Saving user: $this->name\n";
        return true;
    }

    public function delete(): bool {
        echo "Deleting user: $this->name\n";
        return true;
    }

    // From Loggable
    public function log(string $message): void {
        echo "[USER LOG] $message\n";
    }

    // From Cacheable
    public function cache(): void {
        echo "Caching user: $this->name\n";
    }
}

$user = new User("Kieu", "kieu@example.com");
$user->save();   // From Model
$user->log("User created"); // From Loggable
$user->cache();  // From Cacheable
```

---

## Practical Example: Payment Processing

```php
<?php
abstract class PaymentGateway {
    protected float $amount;
    protected string $transactionId;
    protected array $metadata = [];

    public function __construct(float $amount) {
        if ($amount <= 0) {
            throw new Exception("Amount must be positive");
        }
        $this->amount = $amount;
    }

    // Template method - defines the workflow
    final public function processPayment(): bool {
        $this->validatePayment();

        if (!$this->checkFraud()) {
            $this->logFailure("Fraud detected");
            return false;
        }

        $success = $this->executePayment();

        if ($success) {
            $this->transactionId = $this->generateTransactionId();
            $this->sendReceipt();
            $this->logSuccess();
        } else {
            $this->logFailure("Payment failed");
        }

        return $success;
    }

    // Abstract methods - must be implemented by children
    abstract protected function executePayment(): bool;
    abstract protected function sendReceipt(): void;

    // Concrete methods - shared by all gateways
    protected function validatePayment(): void {
        echo "Validating payment amount: $$this->amount\n";
    }

    protected function checkFraud(): bool {
        echo "Running fraud check...\n";
        return true; // Simplified
    }

    protected function generateTransactionId(): string {
        return uniqid('txn_');
    }

    protected function logSuccess(): void {
        echo "✅ Payment successful: {$this->transactionId}\n";
    }

    protected function logFailure(string $reason): void {
        echo "❌ Payment failed: $reason\n";
    }

    public function getTransactionId(): string {
        return $this->transactionId;
    }
}

class CreditCardPayment extends PaymentGateway {
    public function __construct(
        float $amount,
        private string $cardNumber,
        private string $cvv
    ) {
        parent::__construct($amount);
    }

    protected function executePayment(): bool {
        echo "Processing credit card: ****" . substr($this->cardNumber, -4) . "\n";
        echo "Charging $$this->amount\n";
        return true; // Simulated success
    }

    protected function sendReceipt(): void {
        echo "Sending credit card receipt\n";
    }
}

class PayPalPayment extends PaymentGateway {
    public function __construct(
        float $amount,
        private string $email
    ) {
        parent::__construct($amount);
    }

    protected function executePayment(): bool {
        echo "Processing PayPal payment for: $this->email\n";
        echo "Charging $$this->amount\n";
        return true;
    }

    protected function sendReceipt(): void {
        echo "Sending PayPal receipt to: $this->email\n";
    }
}

// Usage
echo "=== Credit Card Payment ===\n";
$cc = new CreditCardPayment(99.99, "1234567890123456", "123");
if ($cc->processPayment()) {
    echo "Transaction ID: " . $cc->getTransactionId() . "\n";
}

echo "\n=== PayPal Payment ===\n";
$pp = new PayPalPayment(149.99, "kieu@example.com");
$pp->processPayment();

/*
Output:
=== Credit Card Payment ===
Validating payment amount: $99.99
Running fraud check...
Processing credit card: ****3456
Charging $99.99
Sending credit card receipt
✅ Payment successful: txn_abc123

=== PayPal Payment ===
Validating payment amount: $149.99
Running fraud check...
Processing PayPal payment for: kieu@example.com
Charging $149.99
Sending PayPal receipt to: kieu@example.com
✅ Payment successful: txn_def456
*/
```

**This is the Template Method Pattern!**

---

## Abstract Class vs Interface vs Regular Class

| Feature | Abstract Class | Interface | Regular Class |
|---------|---------------|-----------|---------------|
| Can instantiate | ❌ No | ❌ No | ✅ Yes |
| Can have properties | ✅ Yes | ❌ No (constants only) | ✅ Yes |
| Can have implemented methods | ✅ Yes | ❌ No | ✅ Yes |
| Can have abstract methods | ✅ Yes | ✅ All methods | ❌ No |
| Can have constructor | ✅ Yes | ❌ No | ✅ Yes |
| Inheritance | Extend one | Implement many | Extend one |
| Purpose | Share code + enforce contract | Define contract | Create objects |

```php
<?php
// Regular class - fully implemented
class ConcreteClass {
    public function method() {
        // Implementation
    }
}

// Abstract class - partial implementation
abstract class AbstractClass {
    public function implemented() {
        // Has implementation
    }
    abstract public function mustImplement(); // No implementation
}

// Interface - no implementation
interface InterfaceName {
    public function method(); // Declaration only
}

// Usage
$obj = new ConcreteClass(); // ✅ OK
// $obj = new AbstractClass(); // ❌ Error
// $obj = new InterfaceName(); // ❌ Error
```

---

## When to Use Abstract Classes

### ✅ Use Abstract Classes When:

**1. You want to share code among related classes**
```php
<?php
abstract class Controller {
    // Shared authentication logic
    protected function requireAuth(): void { /* ... */ }

    // Must implement in child
    abstract public function index(): void;
}
```

**2. You need a template method pattern**
```php
<?php
abstract class DataImporter {
    final public function import(string $file): void {
        $this->validate($file);
        $data = $this->parse($file);
        $this->save($data);
    }

    abstract protected function parse(string $file): array;
    // validate() and save() are concrete
}
```

**3. You need to enforce a contract AND share implementation**
```php
<?php
abstract class Model {
    // Shared code
    protected array $attributes = [];

    // Enforced contract
    abstract public function validate(): bool;
}
```

### ❌ Don't Use Abstract Classes When:

**1. Classes aren't related**
```php
<?php
// ❌ Bad: Car and User aren't related
abstract class Thing {}
class Car extends Thing {}
class User extends Thing {}

// ✅ Better: use interfaces
interface Identifiable {}
class Car implements Identifiable {}
class User implements Identifiable {}
```

**2. You need multiple inheritance**
```php
<?php
// ❌ Can't extend multiple abstract classes
class MyClass extends AbstractA, AbstractB {} // Error!

// ✅ Can implement multiple interfaces
class MyClass implements InterfaceA, InterfaceB {}
```

---

## Best Practices

### 1. Use `final` for Template Methods

Prevent children from breaking the workflow:

```php
<?php
abstract class Process {
    // Can't be overridden - ensures workflow is followed
    final public function execute(): void {
        $this->prepare();
        $this->run();
        $this->finish();
    }

    abstract protected function run(): void;

    protected function prepare(): void {
        echo "Preparing...\n";
    }

    protected function finish(): void {
        echo "Finishing...\n";
    }
}
```

### 2. Make Abstract Methods Protected When Possible

```php
<?php
abstract class Base {
    // Public interface
    public function doSomething(): void {
        $this->initialize();
        $this->process();
    }

    // Protected abstract - internal detail
    abstract protected function initialize(): void;
    abstract protected function process(): void;
}
```

### 3. Provide Default Implementations

```php
<?php
abstract class Model {
    // Can override if needed
    public function validate(): bool {
        return true; // Default: always valid
    }

    // Must override - no default makes sense
    abstract public function save(): bool;
}
```

### 4. Document the Contract

```php
<?php
/**
 * Base class for all exporters.
 *
 * Children must implement:
 * - getFormat(): Returns export format (e.g., 'csv', 'json')
 * - formatData($data): Formats data for export
 *
 * The export() method orchestrates the process.
 */
abstract class Exporter {
    final public function export(array $data): string {
        $formatted = $this->formatData($data);
        return $this->save($formatted);
    }

    abstract protected function getFormat(): string;
    abstract protected function formatData(array $data): string;

    protected function save(string $formatted): string {
        $filename = time() . '.' . $this->getFormat();
        file_put_contents($filename, $formatted);
        return $filename;
    }
}
```

---

## Try It Yourself

### Exercise 1: Report Generator

Create an abstract `Report` class with:
- Properties: title, data (array)
- Constructor that sets title and data
- Abstract methods: `generateHeader()`, `generateBody()`, `generateFooter()`
- Concrete method: `generate()` that calls the three abstract methods in order
- Implement: `PDFReport`, `HTMLReport`, `CSVReport` classes

### Exercise 2: Game Character

Create:
- Abstract class `Character` with: name, health, level
- Methods: `takeDamage(int $amount)`, `heal(int $amount)`, `isAlive(): bool`
- Abstract methods: `attack(): int`, `defend(): int`
- Implement: `Warrior`, `Mage`, `Archer` classes with different attack/defend logic

### Exercise 3: Data Validator

Create:
- Abstract class `Validator` with: data (array), errors (array)
- Method `validate(): bool` that calls abstract `rules()`, runs validation, stores errors
- Abstract method `rules(): array` returns validation rules
- Concrete method `getErrors(): array`
- Implement: `UserValidator`, `ProductValidator` with different rules

---

## Common Mistakes

### 1. Trying to Instantiate Abstract Class

```php
<?php
abstract class Shape {}

// ❌ Error
$shape = new Shape();

// ✅ Must use concrete child
class Circle extends Shape {}
$circle = new Circle();
```

### 2. Not Implementing All Abstract Methods

```php
<?php
abstract class Animal {
    abstract public function eat(): void;
    abstract public function sleep(): void;
}

// ❌ Error: didn't implement sleep()
class Dog extends Animal {
    public function eat(): void {
        echo "Eating";
    }
}

// ✅ Must implement all
class Dog extends Animal {
    public function eat(): void {
        echo "Eating";
    }
    public function sleep(): void {
        echo "Sleeping";
    }
}
```

### 3. Making Abstract Method Private

```php
<?php
abstract class Base {
    // ❌ Error: abstract methods can't be private
    abstract private function method();

    // ✅ Can be public or protected
    abstract public function method1();
    abstract protected function method2();
}
```

---

## Quick Reference

```php
<?php
// Define abstract class
abstract class AbstractClass {
    // Regular property
    protected $property;

    // Regular method
    public function concreteMethod() {
        // Implementation
    }

    // Abstract method (no body)
    abstract public function abstractMethod(): returnType;

    // Template method (often final)
    final public function templateMethod() {
        $this->abstractMethod(); // Calls child's implementation
    }
}

// Extend and implement
class ConcreteClass extends AbstractClass {
    // Must implement all abstract methods
    public function abstractMethod(): returnType {
        // Implementation
    }
}

// Can combine with interfaces
class MyClass extends AbstractClass implements Interface1, Interface2 {
    // Implement abstract methods + interface methods
}
```

---

## What's Next?

You now understand abstract classes - sharing code while enforcing contracts. But what if you need to share code across unrelated classes? What if PHP's single inheritance limitation is too restrictive?

In the next lesson, you'll learn about **traits** - a way to reuse code across multiple classes without inheritance!

---

## Key Takeaways

- **Abstract class** = partially implemented class that can't be instantiated
- Can have both concrete AND abstract methods
- Children must implement all abstract methods
- Great for template method pattern
- Shares code while enforcing contract
- Use `abstract` keyword for class and methods
- Can have properties, constructor, and full methods (unlike interfaces)
- Only single inheritance (unlike interfaces where you can implement many)
- Use when classes are related and need shared code
- Make template methods `final` to protect workflow
- Choose abstract class over interface when you need shared implementation
