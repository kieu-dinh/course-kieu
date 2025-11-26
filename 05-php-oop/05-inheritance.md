# Lesson 05 - Inheritance

## The Problem: Code Duplication

Imagine you're building an e-commerce site with different types of products:

```php
<?php
class Book {
    public string $name;
    public float $price;
    public int $stock;
    public string $author;
    public int $pages;

    public function display() {
        echo "$this->name - $$this->price\n";
    }

    public function sell(int $quantity) {
        if ($quantity > $this->stock) return false;
        $this->stock -= $quantity;
        return true;
    }
}

class ElectronicDevice {
    public string $name;      // Duplicate!
    public float $price;      // Duplicate!
    public int $stock;        // Duplicate!
    public string $brand;
    public int $warrantyMonths;

    public function display() {  // Duplicate!
        echo "$this->name - $$this->price\n";
    }

    public function sell(int $quantity) {  // Duplicate!
        if ($quantity > $this->stock) return false;
        $this->stock -= $quantity;
        return true;
    }
}
```

**Notice the duplication?** Name, price, stock, display(), and sell() are identical!

---

## What is Inheritance?

**Inheritance** allows you to create a new class based on an existing class, inheriting its properties and methods.

Think of it like genetics:
- A child inherits traits from their parents (eye color, height)
- But also has their own unique traits

**In OOP:**
- **Parent class** (base class, superclass) = the class being inherited from
- **Child class** (derived class, subclass) = the class inheriting
- Child gets all parent's public/protected properties and methods
- Child can add its own properties and methods
- Child can override parent's methods

---

## Basic Inheritance Syntax

```php
<?php
// Parent class
class Product {
    public string $name;
    public float $price;
    public int $stock;

    public function display() {
        echo "$this->name - $$this->price\n";
    }

    public function sell(int $quantity): bool {
        if ($quantity > $this->stock) return false;
        $this->stock -= $quantity;
        return true;
    }
}

// Child class extends parent
class Book extends Product {
    // Inherits: name, price, stock, display(), sell()

    // Add book-specific properties
    public string $author;
    public int $pages;

    // Add book-specific methods
    public function getReadingTime(): int {
        return $this->pages * 2; // 2 minutes per page
    }
}

// Usage
$book = new Book();
$book->name = "PHP Mastery";      // From parent
$book->price = 29.99;             // From parent
$book->stock = 50;                // From parent
$book->author = "John Doe";       // From child
$book->pages = 300;               // From child

$book->display();                 // From parent: PHP Mastery - $29.99
echo $book->getReadingTime();     // From child: 600 minutes
$book->sell(5);                   // From parent
```

**Key points:**
- Use `extends` keyword
- Child has everything from parent + its own stuff
- Create objects normally - they work with inherited members

---

## Real-World Example: Vehicles

```php
<?php
// Parent class
class Vehicle {
    public function __construct(
        public string $brand,
        public string $model,
        public int $year
    ) {}

    public function startEngine(): void {
        echo "Engine started: $this->brand $this->model\n";
    }

    public function getAge(): int {
        return date('Y') - $this->year;
    }

    public function display(): void {
        echo "{$this->year} {$this->brand} {$this->model}\n";
    }
}

// Child class: Car
class Car extends Vehicle {
    public function __construct(
        string $brand,
        string $model,
        int $year,
        public int $doors,
        public string $fuelType
    ) {
        parent::__construct($brand, $model, $year); // Call parent constructor
    }

    public function honk(): void {
        echo "Beep beep!\n";
    }
}

// Child class: Motorcycle
class Motorcycle extends Vehicle {
    public function __construct(
        string $brand,
        string $model,
        int $year,
        public int $engineCC
    ) {
        parent::__construct($brand, $model, $year);
    }

    public function wheelie(): void {
        echo "Doing a wheelie!\n";
    }
}

// Usage
$car = new Car("Toyota", "Camry", 2020, 4, "Hybrid");
$car->display();       // From Vehicle
$car->startEngine();   // From Vehicle
$car->honk();          // From Car
echo $car->getAge();   // From Vehicle: 4

$bike = new Motorcycle("Honda", "CBR", 2021, 600);
$bike->display();      // From Vehicle
$bike->startEngine();  // From Vehicle
$bike->wheelie();      // From Motorcycle
```

---

## The `parent` Keyword

Use `parent::` to access the parent class's methods:

```php
<?php
class Animal {
    public function __construct(
        public string $name
    ) {
        echo "Animal created: $name\n";
    }

    public function eat(): void {
        echo "$this->name is eating\n";
    }
}

class Dog extends Animal {
    public function __construct(
        string $name,
        public string $breed
    ) {
        // Call parent constructor FIRST
        parent::__construct($name);
        echo "Dog breed: $breed\n";
    }

    public function eat(): void {
        echo "$this->name is excited!\n";
        parent::eat(); // Call parent's eat method
        echo "$this->name wants more!\n";
    }

    public function bark(): void {
        echo "$this->name says: Woof!\n";
    }
}

$dog = new Dog("Max", "Labrador");
// Output:
// Animal created: Max
// Dog breed: Labrador

$dog->eat();
// Output:
// Max is excited!
// Max is eating
// Max wants more!

$dog->bark();
// Output:
// Max says: Woof!
```

---

## Method Overriding

Child classes can **override** parent methods to provide different behavior:

```php
<?php
class Shape {
    public function __construct(
        public string $color
    ) {}

    public function describe(): string {
        return "A $this->color shape";
    }

    public function getArea(): float {
        return 0.0; // Default implementation
    }
}

class Circle extends Shape {
    public function __construct(
        string $color,
        public float $radius
    ) {
        parent::__construct($color);
    }

    // Override parent's describe method
    public function describe(): string {
        return "A $this->color circle with radius $this->radius";
    }

    // Override parent's getArea method
    public function getArea(): float {
        return pi() * $this->radius ** 2;
    }
}

class Rectangle extends Shape {
    public function __construct(
        string $color,
        public float $width,
        public float $height
    ) {
        parent::__construct($color);
    }

    public function describe(): string {
        return "A $this->color rectangle ({$this->width}x{$this->height})";
    }

    public function getArea(): float {
        return $this->width * $this->height;
    }
}

// Usage
$shapes = [
    new Circle("red", 5),
    new Rectangle("blue", 4, 6),
    new Circle("green", 3)
];

foreach ($shapes as $shape) {
    echo $shape->describe() . "\n";
    echo "Area: " . $shape->getArea() . "\n\n";
}

// Output:
// A red circle with radius 5
// Area: 78.539816339745
//
// A blue rectangle (4x6)
// Area: 24
//
// A green circle with radius 3
// Area: 28.274333882308
```

---

## Protected Members in Inheritance

Remember `protected`? It's designed for inheritance!

```php
<?php
class BankAccount {
    protected float $balance = 0; // Child classes can access

    public function deposit(float $amount): void {
        if ($amount > 0) {
            $this->balance += $amount;
        }
    }

    public function getBalance(): float {
        return $this->balance;
    }
}

class SavingsAccount extends BankAccount {
    private float $interestRate;

    public function __construct(float $interestRate) {
        $this->interestRate = $interestRate;
    }

    public function addInterest(): void {
        // Can access protected $balance from parent
        $interest = $this->balance * $this->interestRate;
        $this->balance += $interest;
        echo "Interest added: $$interest\n";
    }
}

$savings = new SavingsAccount(0.05); // 5% interest
$savings->deposit(1000);
echo $savings->getBalance(); // 1000

$savings->addInterest();
echo $savings->getBalance(); // 1050

// But can't access from outside:
// echo $savings->balance; // Error: Cannot access protected property
```

---

## Practical Example: User Types

```php
<?php
class User {
    protected array $permissions = [];

    public function __construct(
        public string $name,
        public string $email
    ) {}

    public function hasPermission(string $permission): bool {
        return in_array($permission, $this->permissions);
    }

    public function displayRole(): void {
        echo "Regular User: $this->name\n";
    }

    public function canDelete(): bool {
        return false; // Regular users can't delete
    }
}

class Admin extends User {
    public function __construct(string $name, string $email) {
        parent::__construct($name, $email);
        $this->permissions = ['read', 'write', 'delete', 'manage_users'];
    }

    public function displayRole(): void {
        echo "Administrator: $this->name\n";
    }

    public function canDelete(): bool {
        return true; // Admins can delete
    }

    public function banUser(User $user): void {
        echo "Admin $this->name banned user {$user->name}\n";
    }
}

class Moderator extends User {
    public function __construct(string $name, string $email) {
        parent::__construct($name, $email);
        $this->permissions = ['read', 'write', 'moderate'];
    }

    public function displayRole(): void {
        echo "Moderator: $this->name\n";
    }

    public function moderateContent(string $content): void {
        echo "Moderator $this->name is reviewing content...\n";
    }
}

// Usage
$users = [
    new User("John", "john@example.com"),
    new Admin("Kieu", "kieu@example.com"),
    new Moderator("Sarah", "sarah@example.com")
];

foreach ($users as $user) {
    $user->displayRole();
    echo "Can delete: " . ($user->canDelete() ? "Yes" : "No") . "\n";
    echo "Has write permission: " . ($user->hasPermission('write') ? "Yes" : "No") . "\n\n";
}

// Output:
// Regular User: John
// Can delete: No
// Has write permission: No
//
// Administrator: Kieu
// Can delete: Yes
// Has write permission: Yes
//
// Moderator: Sarah
// Can delete: No
// Has write permission: Yes
```

---

## Inheritance Chain (Multi-Level)

Classes can inherit from classes that inherit from other classes:

```php
<?php
class LivingThing {
    public function breathe(): void {
        echo "Breathing...\n";
    }
}

class Animal extends LivingThing {
    public function move(): void {
        echo "Moving...\n";
    }
}

class Mammal extends Animal {
    public function feedMilk(): void {
        echo "Feeding milk to young...\n";
    }
}

class Dog extends Mammal {
    public function bark(): void {
        echo "Woof!\n";
    }
}

$dog = new Dog();
$dog->breathe();  // From LivingThing
$dog->move();     // From Animal
$dog->feedMilk(); // From Mammal
$dog->bark();     // From Dog
```

---

## `instanceof` Operator

Check if an object is an instance of a class or its parent:

```php
<?php
class Animal {}
class Dog extends Animal {}
class Cat extends Animal {}

$dog = new Dog();

var_dump($dog instanceof Dog);    // true
var_dump($dog instanceof Animal); // true (Dog is a type of Animal)
var_dump($dog instanceof Cat);    // false

// Useful for type checking
function feedAnimal($animal): void {
    if ($animal instanceof Dog) {
        echo "Giving dog food\n";
    } elseif ($animal instanceof Cat) {
        echo "Giving cat food\n";
    }
}

feedAnimal($dog); // Giving dog food
```

---

## When to Use Inheritance

### ✅ Good Use Cases

**1. "Is-A" Relationship**
```php
<?php
// A Car IS-A Vehicle ✅
class Car extends Vehicle {}

// A Manager IS-A User ✅
class Manager extends User {}
```

**2. Code Reuse with Specialization**
```php
<?php
// All products share common behavior
class Product {}
class PhysicalProduct extends Product {} // Adds shipping
class DigitalProduct extends Product {}  // Adds download
```

**3. Polymorphism (Different objects, same interface)**
```php
<?php
class Shape {
    public function getArea(): float { return 0; }
}

class Circle extends Shape {
    public function getArea(): float { /* calculate */ }
}

class Rectangle extends Shape {
    public function getArea(): float { /* calculate */ }
}

// Can treat all shapes the same way
function printArea(Shape $shape) {
    echo $shape->getArea();
}
```

### ❌ Bad Use Cases

**1. "Has-A" Relationship**
```php
<?php
// ❌ Wrong: A Car HAS-A Engine, doesn't IS-AN Engine
class Car extends Engine {}

// ✅ Correct: Use composition
class Car {
    private Engine $engine;
}
```

**2. To Reuse Code Without Logical Relationship**
```php
<?php
// ❌ Wrong: Just to reuse logging
class User extends Logger {}

// ✅ Correct: Use traits or composition
class User {
    use LoggerTrait;
}
```

---

## Inheritance Limitations

### PHP Only Supports Single Inheritance

```php
<?php
// ❌ Can't do this in PHP:
class Car extends Vehicle, Machine {} // Error!

// ✅ Can only extend one class:
class Car extends Vehicle {}

// For multiple behaviors, use Traits (Lesson 08)
```

### Final Classes and Methods

Prevent inheritance with `final`:

```php
<?php
// Can't be extended
final class Configuration {
    public function get(string $key) {}
}

// class MyConfig extends Configuration {} // Error!

// ----

class User {
    // Can't be overridden
    final public function getId(): int {
        return $this->id;
    }
}

class Admin extends User {
    // public function getId(): int {} // Error: can't override final method
}
```

---

## Best Practices

### 1. Favor Composition Over Inheritance

```php
<?php
// ❌ Inheritance hierarchy getting complex
class User extends Person extends Entity extends Model {}

// ✅ Composition is cleaner
class User {
    private PersonalInfo $personalInfo;
    private Address $address;
    private Permissions $permissions;
}
```

### 2. Keep Inheritance Hierarchies Shallow

```php
<?php
// ❌ Too deep (hard to understand)
class A {}
class B extends A {}
class C extends B {}
class D extends C {}
class E extends D {}

// ✅ 2-3 levels max
class Entity {}
class User extends Entity {}
class Admin extends User {}
```

### 3. Use Protected for Extensibility

```php
<?php
class BaseController {
    protected function validate(): bool {
        // Child classes can override
    }
}

class UserController extends BaseController {
    protected function validate(): bool {
        // Custom validation
    }
}
```

### 4. Document Inheritance Relationships

```php
<?php
/**
 * Base class for all products in the system.
 * Provides common functionality for pricing and inventory.
 */
class Product {}

/**
 * Represents physical products that require shipping.
 * Extends Product to add shipping-specific features.
 */
class PhysicalProduct extends Product {}
```

---

## Try It Yourself

### Exercise 1: Employee System

Create an inheritance hierarchy:
- Base class `Employee` with: name, salary, `calculateBonus()` (10% of salary)
- `Manager` extends Employee: adds teamSize, overrides bonus (15% + $100 per team member)
- `Developer` extends Employee: adds programmingLanguages array, override bonus (10% + $500)
- `Intern` extends Employee: override bonus (fixed $500)

Create one of each, display info and bonuses.

### Exercise 2: Shape Calculator

Create:
- Base class `Shape` with: color, `getArea()`, `getPerimeter()`, `describe()`
- `Circle` extends Shape: add radius, implement area/perimeter
- `Rectangle` extends Shape: add width/height, implement area/perimeter
- `Square` extends Rectangle: constructor takes only one size parameter

Create shapes array, loop through, display info.

### Exercise 3: Bank Account Types

Create:
- Base `BankAccount`: accountNumber, balance, `deposit()`, `withdraw()`, `getBalance()`
- `SavingsAccount`: add interestRate, `addInterest()` method, withdrawal limit (max 3/month)
- `CheckingAccount`: add overdraftLimit, allow negative balance up to limit
- `BusinessAccount`: add companyName, no withdrawal limit

Test different account types with various transactions.

---

## Common Mistakes

### 1. Forgetting parent::__construct()

```php
<?php
// ❌ Wrong: parent properties not initialized
class Dog extends Animal {
    public function __construct(string $name, string $breed) {
        $this->breed = $breed;
        // Forgot to call parent::__construct($name)!
    }
}

// ✅ Correct
class Dog extends Animal {
    public function __construct(string $name, string $breed) {
        parent::__construct($name); // Initialize parent first
        $this->breed = $breed;
    }
}
```

### 2. Accessing Private Parent Properties

```php
<?php
class Parent {
    private string $secret; // Private to Parent only
}

class Child extends Parent {
    public function reveal() {
        echo $this->secret; // ❌ Error: can't access private
    }
}

// ✅ Make it protected if children need access
class Parent {
    protected string $secret;
}
```

### 3. Overriding and Changing Signatures

```php
<?php
class Parent {
    public function greet(string $name): void {}
}

// ❌ Wrong: changed signature
class Child extends Parent {
    public function greet(string $name, string $title): void {}
}

// ✅ Keep signature compatible
class Child extends Parent {
    public function greet(string $name): void {
        // Can do different things inside
    }
}
```

---

## Quick Reference

```php
<?php
// Parent class
class Parent {
    protected $property; // Accessible in children

    public function __construct($value) {
        $this->property = $value;
    }

    public function method() {
        echo "Parent method";
    }
}

// Child class
class Child extends Parent {
    public function __construct($value, $extra) {
        parent::__construct($value); // Call parent constructor
        $this->extra = $extra;
    }

    // Override parent method
    public function method() {
        parent::method(); // Call parent version
        echo " + Child addition";
    }

    // New method
    public function childOnly() {
        echo $this->property; // Access protected parent property
    }
}

// Usage
$obj = new Child("value", "extra");
$obj->method(); // Calls overridden version
$obj instanceof Child;  // true
$obj instanceof Parent; // true

// Prevent inheritance
final class CannotExtend {}
class Parent {
    final public function cannotOverride() {}
}
```

---

## What's Next?

Inheritance is powerful for code reuse, but it has limitations. What if you want to ensure a class implements certain methods? What if different classes need to have the same methods but aren't related?

In the next lesson, you'll learn about **interfaces** - contracts that define what methods a class must implement, enabling polymorphism without inheritance.

---

## Key Takeaways

- **Inheritance** allows code reuse by extending existing classes
- Use `extends` keyword to create child classes
- Child inherits all public/protected members from parent
- Use `parent::` to access parent's methods
- **Override** methods to provide different behavior
- Use `protected` for members children should access
- Check types with `instanceof`
- Use `final` to prevent inheritance/overriding
- **Is-A relationship** = good for inheritance
- **Has-A relationship** = use composition instead
- Keep inheritance hierarchies shallow (2-3 levels max)
