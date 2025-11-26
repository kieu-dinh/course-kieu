# Lesson 09 - Namespaces

## The Problem: Name Collisions

As your project grows, you'll have many classes. What if two classes have the same name?

```php
<?php
// File: src/Database/Connection.php
class Connection {
    public function connect() {
        echo "Database connection\n";
    }
}

// File: src/Network/Connection.php
class Connection {  // ❌ Error: Cannot redeclare class Connection
    public function connect() {
        echo "Network connection\n";
    }
}

// File: index.php
require 'src/Database/Connection.php';
require 'src/Network/Connection.php'; // Fatal error!

$db = new Connection(); // Which Connection?
```

**Problems:**
- Can't have two classes with the same name
- No way to organize code logically
- Hard to know which class you're using
- Vendor libraries might conflict with your code

---

## What are Namespaces?

**Namespaces** organize code into logical groups and prevent name collisions, like folders organize files on your computer.

Think of it like:
- **File system**: `/Users/kieu/Documents/report.pdf` vs `/Users/john/Documents/report.pdf`
- Two files with the same name, different folders
- Full path is unique

**In PHP:**
- Classes with the same name, different namespaces
- Fully qualified name is unique
- Organize by feature, layer, or vendor

```php
<?php
// File: src/Database/Connection.php
namespace Database;

class Connection {
    public function connect() {
        echo "Database connection\n";
    }
}

// File: src/Network/Connection.php
namespace Network;

class Connection {
    public function connect() {
        echo "Network connection\n";
    }
}

// File: index.php
require 'src/Database/Connection.php';
require 'src/Network/Connection.php'; // ✅ No error!

$db = new Database\Connection();  // Database connection
$net = new Network\Connection();  // Network connection
```

---

## Namespace Syntax

### Declaring a Namespace

```php
<?php
// MUST be the first statement (after <?php)
namespace MyApp\Models;

class User {
    public string $name;
}

// Full name is: MyApp\Models\User
```

**Rules:**
- Must be first statement in file (except for `declare`)
- Use backslash `\` as separator
- Convention: PascalCase for each part
- One namespace per file (best practice)

### Using Classes from Namespaces

```php
<?php
namespace MyApp\Controllers;

// Option 1: Fully qualified name (starts with \)
$user = new \MyApp\Models\User();

// Option 2: Relative to current namespace
$user = new Models\User(); // ❌ Looks for MyApp\Controllers\Models\User

// Option 3: Import with 'use' (best practice)
use MyApp\Models\User;
$user = new User();
```

---

## The `use` Statement

Import classes to use shorter names:

```php
<?php
namespace MyApp\Controllers;

use MyApp\Models\User;
use MyApp\Models\Product;
use MyApp\Services\EmailService;

class UserController {
    public function register(string $name, string $email) {
        // Can use short names now
        $user = new User();
        $user->name = $name;
        $user->email = $email;

        $emailService = new EmailService();
        $emailService->send($user);
    }
}
```

**Key points:**
- `use` imports the class
- Put `use` statements after `namespace`, before class definitions
- Can import multiple classes

---

## Aliasing with `as`

Resolve name conflicts or create shorter names:

```php
<?php
namespace MyApp;

use Database\Connection as DbConnection;
use Network\Connection as NetConnection;
use Some\Very\Long\Namespace\ClassName as ShortName;

$db = new DbConnection();
$net = new NetConnection();
$obj = new ShortName();
```

---

## Practical Example: E-commerce Application

```php
<?php
// File: src/Models/Product.php
namespace MyApp\Models;

class Product {
    public function __construct(
        public string $name,
        public float $price
    ) {}

    public function display(): void {
        echo "$this->name - $$this->price\n";
    }
}

// File: src/Models/User.php
namespace MyApp\Models;

class User {
    public function __construct(
        public string $name,
        public string $email
    ) {}
}

// File: src/Services/PaymentService.php
namespace MyApp\Services;

class PaymentService {
    public function process(float $amount): bool {
        echo "Processing payment: $$amount\n";
        return true;
    }
}

// File: src/Controllers/OrderController.php
namespace MyApp\Controllers;

use MyApp\Models\Product;
use MyApp\Models\User;
use MyApp\Services\PaymentService;

class OrderController {
    public function checkout(User $user, array $products): void {
        echo "Order for: {$user->name}\n";

        $total = 0;
        foreach ($products as $product) {
            $product->display();
            $total += $product->price;
        }

        echo "Total: $$total\n";

        $payment = new PaymentService();
        $payment->process($total);
    }
}

// File: index.php
require_once 'src/Models/Product.php';
require_once 'src/Models/User.php';
require_once 'src/Services/PaymentService.php';
require_once 'src/Controllers/OrderController.php';

use MyApp\Controllers\OrderController;
use MyApp\Models\User;
use MyApp\Models\Product;

$user = new User("Kieu", "kieu@example.com");
$products = [
    new Product("Laptop", 999.99),
    new Product("Mouse", 29.99)
];

$controller = new OrderController();
$controller->checkout($user, $products);

/*
Output:
Order for: Kieu
Laptop - $999.99
Mouse - $29.99
Total: $1029.98
Processing payment: $1029.98
*/
```

---

## Sub-namespaces

Organize code into multiple levels:

```php
<?php
// File: src/Database/MySQL/Connection.php
namespace MyApp\Database\MySQL;

class Connection {
    public function connect() {
        echo "MySQL connection\n";
    }
}

// File: src/Database/PostgreSQL/Connection.php
namespace MyApp\Database\PostgreSQL;

class Connection {
    public function connect() {
        echo "PostgreSQL connection\n";
    }
}

// Usage
use MyApp\Database\MySQL\Connection as MySQLConnection;
use MyApp\Database\PostgreSQL\Connection as PostgreSQLConnection;

$mysql = new MySQLConnection();
$postgres = new PostgreSQLConnection();
```

---

## The Global Namespace

Classes without a namespace are in the **global namespace**:

```php
<?php
// No namespace = global namespace
class Connection {
    // This is \Connection
}

// To access global class from inside a namespace:
namespace MyApp;

$conn = new \Connection(); // Leading \ = global namespace

// PHP built-in classes are in global namespace
$date = new \DateTime();
$pdo = new \PDO('mysql:host=localhost', 'user', 'pass');
```

---

## Namespace Resolution

### Unqualified Name

```php
<?php
namespace MyApp\Models;

// No backslash
$user = new User(); // Looks for MyApp\Models\User
```

### Qualified Name

```php
<?php
namespace MyApp\Controllers;

// Has backslash, but doesn't start with one
$user = new Models\User(); // Looks for MyApp\Controllers\Models\User
```

### Fully Qualified Name

```php
<?php
namespace MyApp\Controllers;

// Starts with backslash
$user = new \MyApp\Models\User(); // Exact: MyApp\Models\User
```

---

## Autoloading with Composer

Manual `require` statements are tedious. Use Composer's autoloader!

### Step 1: Create `composer.json`

```json
{
    "autoload": {
        "psr-4": {
            "MyApp\\": "src/"
        }
    }
}
```

**PSR-4** is a standard that maps namespaces to directories:
- `MyApp\Models\User` → `src/Models/User.php`
- `MyApp\Controllers\UserController` → `src/Controllers/UserController.php`

### Step 2: Generate Autoloader

```bash
composer dump-autoload
```

This creates `vendor/autoload.php`.

### Step 3: Use It

```php
<?php
// File: index.php
require 'vendor/autoload.php'; // That's it!

use MyApp\Models\User;
use MyApp\Controllers\UserController;

// Classes are automatically loaded
$user = new User("Kieu", "kieu@example.com");
$controller = new UserController();
```

**No more manual `require` statements!**

---

## Practical Example with Autoloading

### Directory Structure

```
project/
├── composer.json
├── vendor/
│   └── autoload.php (generated)
├── src/
│   ├── Models/
│   │   ├── User.php
│   │   └── Product.php
│   ├── Controllers/
│   │   └── ProductController.php
│   └── Services/
│       └── CartService.php
└── index.php
```

### composer.json

```json
{
    "autoload": {
        "psr-4": {
            "MyApp\\": "src/"
        }
    }
}
```

### src/Models/Product.php

```php
<?php
namespace MyApp\Models;

class Product {
    public function __construct(
        public string $name,
        public float $price,
        public int $stock
    ) {}

    public function isAvailable(): bool {
        return $this->stock > 0;
    }
}
```

### src/Services/CartService.php

```php
<?php
namespace MyApp\Services;

use MyApp\Models\Product;

class CartService {
    private array $items = [];

    public function add(Product $product): void {
        if (!$product->isAvailable()) {
            throw new \Exception("Product not available");
        }

        $this->items[] = $product;
        echo "Added: {$product->name}\n";
    }

    public function getTotal(): float {
        $total = 0;
        foreach ($this->items as $item) {
            $total += $item->price;
        }
        return $total;
    }
}
```

### src/Controllers/ProductController.php

```php
<?php
namespace MyApp\Controllers;

use MyApp\Models\Product;
use MyApp\Services\CartService;

class ProductController {
    public function addToCart(Product $product, CartService $cart): void {
        try {
            $cart->add($product);
            echo "Total: $" . $cart->getTotal() . "\n";
        } catch (\Exception $e) {
            echo "Error: " . $e->getMessage() . "\n";
        }
    }
}
```

### index.php

```php
<?php
require 'vendor/autoload.php';

use MyApp\Models\Product;
use MyApp\Services\CartService;
use MyApp\Controllers\ProductController;

// All classes auto-loaded!
$product1 = new Product("Laptop", 999.99, 5);
$product2 = new Product("Mouse", 29.99, 0); // Out of stock

$cart = new CartService();
$controller = new ProductController();

$controller->addToCart($product1, $cart);
// Output:
// Added: Laptop
// Total: $999.99

$controller->addToCart($product2, $cart);
// Output:
// Error: Product not available
```

---

## Importing Functions and Constants

You can also import functions and constants:

```php
<?php
namespace MyApp\Helpers;

function formatPrice(float $price): string {
    return '$' . number_format($price, 2);
}

const TAX_RATE = 0.08;

// Using them
namespace MyApp\Controllers;

use function MyApp\Helpers\formatPrice;
use const MyApp\Helpers\TAX_RATE;

$price = 99.99;
echo formatPrice($price); // $99.99
echo TAX_RATE; // 0.08
```

---

## Group `use` Declarations

Import multiple items from same namespace:

```php
<?php
// Instead of:
use MyApp\Models\User;
use MyApp\Models\Product;
use MyApp\Models\Order;
use MyApp\Models\Category;

// Can do:
use MyApp\Models\{User, Product, Order, Category};

// With aliases:
use MyApp\Models\{
    User,
    Product,
    Order as OrderModel,
    Category
};
```

---

## Common Namespace Conventions

### By Layer (MVC)

```
MyApp\
├── Models\
│   ├── User.php
│   └── Product.php
├── Controllers\
│   ├── UserController.php
│   └── ProductController.php
└── Views\
    └── ...
```

### By Feature

```
MyApp\
├── User\
│   ├── User.php
│   ├── UserController.php
│   └── UserRepository.php
├── Product\
│   ├── Product.php
│   ├── ProductController.php
│   └── ProductRepository.php
```

### Hybrid Approach

```
MyApp\
├── Domain\
│   ├── User\
│   └── Product\
├── Application\
│   ├── Controllers\
│   └── Services\
└── Infrastructure\
    ├── Database\
    └── Email\
```

---

## Best Practices

### 1. One Class Per File

```php
<?php
// ❌ Bad: multiple classes in one file
namespace MyApp\Models;

class User {}
class Product {}
class Order {}

// ✅ Good: one class per file
// User.php
namespace MyApp\Models;
class User {}

// Product.php
namespace MyApp\Models;
class Product {}
```

### 2. Match Namespace to Directory Structure

```php
<?php
// ✅ File: src/Models/User.php
namespace MyApp\Models;

// ✅ File: src/Controllers/Admin/UserController.php
namespace MyApp\Controllers\Admin;

// ❌ File: src/Models/User.php (wrong namespace)
namespace MyApp\Controllers; // Doesn't match directory!
```

### 3. Use PSR-4 Autoloading

```json
{
    "autoload": {
        "psr-4": {
            "MyApp\\": "src/",
            "MyApp\\Tests\\": "tests/"
        }
    }
}
```

### 4. Import at Top of File

```php
<?php
namespace MyApp\Controllers;

use MyApp\Models\User;
use MyApp\Services\EmailService;
use MyApp\Exceptions\ValidationException;

class UserController {
    // Class code here
}
```

### 5. Avoid Deep Nesting

```php
<?php
// ❌ Too deep
namespace MyApp\Domain\User\Services\Authentication\Providers\OAuth\Google;

// ✅ Reasonable
namespace MyApp\Services\Auth;
```

---

## Try It Yourself

### Exercise 1: Blog Application

Create namespace structure:
```
Blog\
├── Models\
│   ├── Post.php
│   ├── Comment.php
│   └── Author.php
├── Services\
│   ├── PostService.php
│   └── CommentService.php
└── Controllers\
    └── PostController.php
```

Implement classes with proper namespaces and use statements.

### Exercise 2: E-commerce with Autoloading

1. Set up `composer.json` with PSR-4 autoloading
2. Create structure:
   ```
   Shop\
   ├── Products\
   ├── Orders\
   └── Payments\
   ```
3. Create classes in each namespace
4. Build a simple checkout flow using autoloading

### Exercise 3: Name Conflict Resolution

Create two `Logger` classes in different namespaces:
- `App\Services\Logger`
- `ThirdParty\Logging\Logger`

Use both in the same file with aliases.

---

## Common Mistakes

### 1. Namespace Not First Statement

```php
<?php
echo "Hello"; // ❌ Code before namespace

namespace MyApp\Models;

// ✅ Correct:
<?php
namespace MyApp\Models;

echo "Hello"; // After namespace is OK
```

### 2. Forgetting Leading Backslash for Global Classes

```php
<?php
namespace MyApp\Models;

$date = new DateTime(); // ❌ Looks for MyApp\Models\DateTime

$date = new \DateTime(); // ✅ Global DateTime
```

### 3. Wrong Namespace in File

```php
<?php
// File: src/Models/User.php

namespace MyApp\Controllers; // ❌ Wrong namespace for this file

// ✅ Should be:
namespace MyApp\Models;
```

### 4. Mixing Relative and Absolute Imports

```php
<?php
namespace MyApp\Controllers;

use MyApp\Models\User; // Absolute (good)
$product = new Models\Product(); // Relative (confusing!)

// ✅ Better: be consistent
use MyApp\Models\User;
use MyApp\Models\Product;

$user = new User();
$product = new Product();
```

---

## Quick Reference

```php
<?php
// Declare namespace (first statement)
namespace MyApp\Models;

// Import classes
use Other\Namespace\ClassName;
use Another\Namespace\ClassName as Alias;
use Some\Namespace\{ClassA, ClassB, ClassC};

// Import functions and constants
use function Some\Namespace\functionName;
use const Some\Namespace\CONSTANT_NAME;

// Using classes
$a = new ClassName(); // Imported
$b = new \Fully\Qualified\Name(); // Fully qualified
$c = new Sub\Relative\Name(); // Relative to current namespace

// Global namespace
$date = new \DateTime();
```

**Autoloading (composer.json):**
```json
{
    "autoload": {
        "psr-4": {
            "MyApp\\": "src/"
        }
    }
}
```

---

## What's Next?

You now know how to organize your code with namespaces! But PHP has some special "magic" methods that provide powerful functionality when certain events happen (like accessing a property that doesn't exist).

In the next lesson, you'll learn about **magic methods** - special methods that PHP calls automatically in specific situations.

---

## Key Takeaways

- **Namespaces** organize code and prevent name collisions
- Use backslash `\` as separator: `MyApp\Models\User`
- Declare with `namespace` keyword (must be first statement)
- Import with `use` keyword
- Alias with `as` keyword
- Leading `\` means global namespace
- **PSR-4 autoloading** maps namespaces to directories
- Use Composer for automatic class loading
- One class per file
- Match namespace to directory structure
- Keep nesting reasonable (2-4 levels)
- Import at top of file for clarity
