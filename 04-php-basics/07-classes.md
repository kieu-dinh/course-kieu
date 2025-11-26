# Lesson 07 - Classes & Objects

## What is OOP?

**Object-Oriented Programming** organizes code around "objects" - things with data and behavior.

```
Real world: A car
- Data: color, brand, speed
- Behavior: start, stop, accelerate

Code: Car class
- Properties: $color, $brand, $speed
- Methods: start(), stop(), accelerate()
```

---

## Class vs Object

- **Class** = Blueprint (the plan)
- **Object** = Instance (actual thing built from plan)

```php
<?php
// Class (blueprint)
class Car {
    public string $color;
}

// Objects (instances)
$myCar = new Car();
$yourCar = new Car();
```

---

## Defining a Class

```php
<?php
class User {
    // Properties (data)
    public string $name;
    public int $age;

    // Method (behavior)
    public function greet(): string {
        return "Hello, I'm {$this->name}!";
    }
}

// Create object
$user = new User();
$user->name = "Kieu";
$user->age = 28;

echo $user->greet();  // Hello, I'm Kieu!
```

---

## Constructor

Initialize object when created:

```php
<?php
class User {
    public string $name;
    public int $age;

    public function __construct(string $name, int $age) {
        $this->name = $name;
        $this->age = $age;
    }
}

$user = new User("Kieu", 28);
echo $user->name;  // Kieu
```

### Short Syntax (PHP 8+)

```php
<?php
class User {
    public function __construct(
        public string $name,
        public int $age
    ) {}
}

$user = new User("Kieu", 28);
```

---

## Visibility

Control access to properties/methods:

| Keyword | Access |
|---------|--------|
| `public` | Anywhere |
| `private` | Only inside class |
| `protected` | Inside class + child classes |

```php
<?php
class BankAccount {
    private float $balance = 0;  // Can't access from outside

    public function deposit(float $amount): void {
        $this->balance += $amount;
    }

    public function getBalance(): float {
        return $this->balance;
    }
}

$account = new BankAccount();
$account->deposit(100);
echo $account->getBalance();  // 100
// echo $account->balance;    // Error! Private
```

---

## Getters and Setters

Control access to properties:

```php
<?php
class User {
    private string $email;

    public function getEmail(): string {
        return $this->email;
    }

    public function setEmail(string $email): void {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email");
        }
        $this->email = $email;
    }
}
```

---

## Inheritance

Create classes based on other classes:

```php
<?php
class Animal {
    public string $name;

    public function speak(): string {
        return "...";
    }
}

class Dog extends Animal {
    public function speak(): string {
        return "Woof!";
    }
}

class Cat extends Animal {
    public function speak(): string {
        return "Meow!";
    }
}

$dog = new Dog();
$dog->name = "Rex";
echo $dog->speak();  // Woof!
```

---

## Static Properties/Methods

Belong to class, not instances:

```php
<?php
class Counter {
    private static int $count = 0;

    public static function increment(): void {
        self::$count++;
    }

    public static function getCount(): int {
        return self::$count;
    }
}

Counter::increment();
Counter::increment();
echo Counter::getCount();  // 2
```

---

## Constants

```php
<?php
class Config {
    public const APP_NAME = "My App";
    public const VERSION = "1.0.0";
}

echo Config::APP_NAME;  // My App
```

---

## Interfaces

Define a contract:

```php
<?php
interface Payable {
    public function pay(float $amount): bool;
}

class CreditCard implements Payable {
    public function pay(float $amount): bool {
        // Process payment
        return true;
    }
}

class PayPal implements Payable {
    public function pay(float $amount): bool {
        // Process payment
        return true;
    }
}
```

---

## Complete Example

```php
<?php
class Product {
    public function __construct(
        public string $name,
        public float $price,
        private int $stock = 0
    ) {}

    public function addStock(int $quantity): void {
        $this->stock += $quantity;
    }

    public function removeStock(int $quantity): bool {
        if ($quantity > $this->stock) {
            return false;
        }
        $this->stock -= $quantity;
        return true;
    }

    public function getStock(): int {
        return $this->stock;
    }

    public function getInfo(): string {
        return "{$this->name}: {$this->price}€ ({$this->stock} in stock)";
    }
}

// Usage
$phone = new Product("iPhone", 999.00, 10);
echo $phone->getInfo();  // iPhone: 999€ (10 in stock)

$phone->removeStock(3);
echo $phone->getStock();  // 7
```

---

## Practice

```php
<?php
// 1. Create a Book class
class Book {
    public function __construct(
        public string $title,
        public string $author,
        public int $pages
    ) {}

    public function getDescription(): string {
        return "{$this->title} by {$this->author} ({$this->pages} pages)";
    }
}

// 2. Create a ShoppingCart class
class ShoppingCart {
    private array $items = [];

    public function addItem(string $name, float $price): void {
        $this->items[] = ['name' => $name, 'price' => $price];
    }

    public function getTotal(): float {
        return array_sum(array_column($this->items, 'price'));
    }

    public function getItemCount(): int {
        return count($this->items);
    }
}

// Test
$cart = new ShoppingCart();
$cart->addItem("Apple", 1.50);
$cart->addItem("Bread", 2.00);
echo $cart->getTotal();      // 3.50
echo $cart->getItemCount();  // 2
```

---

## Module Complete!

Go to: [Exercises](./exercises/)
