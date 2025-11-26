# Lesson 10 - Magic Methods

## What are Magic Methods?

**Magic methods** are special methods in PHP that are called automatically in specific situations. They all start with double underscores `__`.

Think of them like:
- **Event handlers** that respond to specific actions
- **Hooks** that PHP calls at the right time
- **Interceptors** that catch certain operations

You've already used one: `__construct()`!

```php
<?php
class User {
    // You've been using magic methods all along!
    public function __construct(public string $name) {
        // Called automatically when: new User()
    }
}
```

---

## Common Magic Methods

| Method | When It's Called |
|--------|------------------|
| `__construct()` | When object is created with `new` |
| `__destruct()` | When object is destroyed |
| `__toString()` | When object is used as string |
| `__get($name)` | When accessing undefined property |
| `__set($name, $value)` | When setting undefined property |
| `__isset($name)` | When `isset()` or `empty()` on undefined property |
| `__unset($name)` | When `unset()` on undefined property |
| `__call($name, $args)` | When calling undefined method |
| `__callStatic($name, $args)` | When calling undefined static method |
| `__clone()` | When object is cloned with `clone` |
| `__invoke()` | When object is called like a function |
| `__sleep()` | When `serialize()` is called |
| `__wakeup()` | When `unserialize()` is called |

Let's explore the most useful ones!

---

## `__toString()` - String Representation

Called when object is used as a string:

```php
<?php
class User {
    public function __construct(
        public string $name,
        public string $email
    ) {}

    public function __toString(): string {
        return "{$this->name} ({$this->email})";
    }
}

$user = new User("Kieu", "kieu@example.com");

echo $user; // Kieu (kieu@example.com)
echo "User: $user"; // User: Kieu (kieu@example.com)

// Without __toString(), would get error:
// "Object of class User could not be converted to string"
```

**Use cases:**
- Debug output
- Logging
- Display in templates
- API responses

---

## `__get()` and `__set()` - Property Overloading

Handle access to undefined or inaccessible properties:

```php
<?php
class User {
    private array $data = [];

    // Called when accessing undefined property
    public function __get(string $name) {
        if (array_key_exists($name, $this->data)) {
            return $this->data[$name];
        }
        throw new Exception("Property $name does not exist");
    }

    // Called when setting undefined property
    public function __set(string $name, $value): void {
        $this->data[$name] = $value;
    }

    // Called when isset() on undefined property
    public function __isset(string $name): bool {
        return isset($this->data[$name]);
    }

    // Called when unset() on undefined property
    public function __unset(string $name): void {
        unset($this->data[$name]);
    }
}

$user = new User();

// Triggers __set()
$user->name = "Kieu";
$user->email = "kieu@example.com";
$user->age = 28;

// Triggers __get()
echo $user->name; // Kieu
echo $user->email; // kieu@example.com

// Triggers __isset()
if (isset($user->age)) {
    echo "Age is set";
}

// Triggers __unset()
unset($user->age);
```

**Common use case: Dynamic attributes**

```php
<?php
class DynamicModel {
    private array $attributes = [];

    public function __get(string $name) {
        return $this->attributes[$name] ?? null;
    }

    public function __set(string $name, $value): void {
        $this->attributes[$name] = $value;
    }

    public function toArray(): array {
        return $this->attributes;
    }
}

$product = new DynamicModel();
$product->name = "Laptop";
$product->price = 999.99;
$product->inStock = true;
$product->customField = "Any value"; // Can add any property!

print_r($product->toArray());
// Array (
//   [name] => Laptop
//   [price] => 999.99
//   [inStock] => 1
//   [customField] => Any value
// )
```

---

## `__call()` - Method Overloading

Called when invoking undefined methods:

```php
<?php
class Database {
    private array $queries = [];

    // Called when method doesn't exist
    public function __call(string $method, array $args) {
        // Handle findByXXX methods
        if (str_starts_with($method, 'findBy')) {
            $field = lcfirst(substr($method, 6)); // findByEmail -> email
            $value = $args[0] ?? null;

            echo "Finding by $field = $value\n";
            return ["Mock result for $field: $value"];
        }

        throw new Exception("Method $method does not exist");
    }
}

$db = new Database();

// These methods don't exist, but __call() handles them!
$db->findByEmail("kieu@example.com");
// Output: Finding by email = kieu@example.com

$db->findByName("Kieu");
// Output: Finding by name = Kieu

$db->findById(123);
// Output: Finding by id = 123
```

**Use cases:**
- Dynamic method names
- Method aliasing
- Fluent interfaces
- Proxying calls to another object

---

## `__invoke()` - Callable Objects

Makes objects callable like functions:

```php
<?php
class Multiplier {
    public function __construct(
        private int $factor
    ) {}

    public function __invoke(int $number): int {
        return $number * $this->factor;
    }
}

$double = new Multiplier(2);
$triple = new Multiplier(3);

// Call object like a function!
echo $double(5);  // 10
echo $triple(5);  // 15

// Can pass to array_map, usort, etc.
$numbers = [1, 2, 3, 4, 5];
$doubled = array_map($double, $numbers);
print_r($doubled); // [2, 4, 6, 8, 10]
```

**Practical example: Route handler**

```php
<?php
class RouteHandler {
    public function __construct(
        private string $controllerClass,
        private string $method
    ) {}

    public function __invoke() {
        $controller = new $this->controllerClass();
        return $controller->{$this->method}();
    }
}

// Router
$routes = [
    '/users' => new RouteHandler(UserController::class, 'index'),
    '/products' => new RouteHandler(ProductController::class, 'index'),
];

// Handle request
$route = $routes['/users'];
$route(); // Calls UserController->index()
```

---

## `__clone()` - Object Cloning

Called when cloning an object with `clone`:

```php
<?php
class User {
    public function __construct(
        public string $name,
        public Address $address
    ) {}

    public function __clone() {
        // Clone nested objects too
        $this->address = clone $this->address;

        echo "User cloned!\n";
    }
}

class Address {
    public function __construct(
        public string $street,
        public string $city
    ) {}
}

$user1 = new User("Kieu", new Address("123 Main St", "Hanoi"));
$user2 = clone $user1; // Triggers __clone()

// Without __clone(), both would share the same Address object
$user2->address->city = "HCMC";

echo $user1->address->city; // Hanoi (not affected thanks to __clone())
echo $user2->address->city; // HCMC
```

---

## `__destruct()` - Destructor

Called when object is destroyed (goes out of scope, script ends, or `unset()`):

```php
<?php
class FileHandler {
    private $handle;

    public function __construct(string $filename) {
        $this->handle = fopen($filename, 'w');
        echo "File opened: $filename\n";
    }

    public function write(string $content): void {
        fwrite($this->handle, $content);
    }

    public function __destruct() {
        if ($this->handle) {
            fclose($this->handle);
            echo "File closed\n";
        }
    }
}

$file = new FileHandler('output.txt');
$file->write("Hello World");
// __destruct() called automatically when script ends or $file is unset
```

**Use cases:**
- Close file handles
- Close database connections
- Release resources
- Cleanup operations

---

## `__sleep()` and `__wakeup()` - Serialization

Control what happens during serialization:

```php
<?php
class User {
    public string $name;
    public string $email;
    private string $password;
    private $dbConnection; // Resource, can't serialize

    public function __construct(string $name, string $email, string $password) {
        $this->name = $name;
        $this->email = $email;
        $this->password = $password;
        $this->dbConnection = "Database connection";
    }

    // Called before serialization
    public function __sleep(): array {
        // Return array of property names to serialize
        // Exclude sensitive data and resources
        return ['name', 'email'];
    }

    // Called after unserialization
    public function __wakeup(): void {
        // Restore resources
        $this->dbConnection = "Reconnected to database";
        echo "User object restored\n";
    }
}

$user = new User("Kieu", "kieu@example.com", "secret123");

// Serialize (calls __sleep())
$serialized = serialize($user);
echo $serialized . "\n";
// Only name and email are included, not password or dbConnection

// Unserialize (calls __wakeup())
$restored = unserialize($serialized);
// Output: User object restored

echo $restored->name; // Kieu
// $restored->password is not available (wasn't serialized)
```

---

## Practical Example: Fluent Configuration

```php
<?php
class Config {
    private array $data = [];

    // __set: Allow setting any config key
    public function __set(string $key, $value): void {
        $this->data[$key] = $value;
    }

    // __get: Retrieve config value
    public function __get(string $key) {
        return $this->data[$key] ?? null;
    }

    // __call: Handle get/set methods dynamically
    public function __call(string $method, array $args) {
        if (str_starts_with($method, 'set')) {
            $key = lcfirst(substr($method, 3));
            $this->data[$key] = $args[0];
            return $this; // Fluent interface
        }

        if (str_starts_with($method, 'get')) {
            $key = lcfirst(substr($method, 3));
            return $this->data[$key] ?? null;
        }

        throw new Exception("Method $method not found");
    }

    // __toString: Display config as JSON
    public function __toString(): string {
        return json_encode($this->data, JSON_PRETTY_PRINT);
    }

    // __invoke: Get specific config or all
    public function __invoke(string $key = null) {
        return $key ? ($this->data[$key] ?? null) : $this->data;
    }
}

$config = new Config();

// Using __set
$config->appName = "My App";
$config->version = "1.0.0";

// Using __call (fluent)
$config->setDebug(true)
       ->setTimezone('Asia/Ho_Chi_Minh')
       ->setLocale('vi');

// Using __get
echo $config->appName; // My App

// Using __call (getter)
echo $config->getDebug(); // 1

// Using __toString
echo $config;
// {
//   "appName": "My App",
//   "version": "1.0.0",
//   "debug": true,
//   ...
// }

// Using __invoke
print_r($config('debug')); // true
print_r($config()); // All config
```

---

## Practical Example: Simple ORM

```php
<?php
class Model {
    protected array $attributes = [];
    protected array $original = [];
    protected bool $exists = false;

    public function __construct(array $attributes = []) {
        $this->fill($attributes);
        $this->original = $this->attributes;
    }

    public function fill(array $attributes): self {
        foreach ($attributes as $key => $value) {
            $this->attributes[$key] = $value;
        }
        return $this;
    }

    // __get: Access attributes
    public function __get(string $key) {
        return $this->attributes[$key] ?? null;
    }

    // __set: Set attributes
    public function __set(string $key, $value): void {
        $this->attributes[$key] = $value;
    }

    // __isset: Check if attribute exists
    public function __isset(string $key): bool {
        return isset($this->attributes[$key]);
    }

    // __toString: JSON representation
    public function __toString(): string {
        return json_encode($this->attributes);
    }

    // Check if attribute changed
    public function isDirty(string $key = null): bool {
        if ($key) {
            return ($this->attributes[$key] ?? null) !== ($this->original[$key] ?? null);
        }

        return $this->attributes !== $this->original;
    }

    // Get changed attributes
    public function getDirty(): array {
        $dirty = [];
        foreach ($this->attributes as $key => $value) {
            if ($this->isDirty($key)) {
                $dirty[$key] = $value;
            }
        }
        return $dirty;
    }

    public function save(): bool {
        if ($this->isDirty()) {
            echo "Saving changes: " . json_encode($this->getDirty()) . "\n";
            $this->original = $this->attributes;
            $this->exists = true;
            return true;
        }
        echo "No changes to save\n";
        return false;
    }
}

// Usage
$user = new Model(['name' => 'Kieu', 'email' => 'kieu@example.com']);

echo $user->name; // Kieu (via __get)

$user->name = "Kieu Nguyen"; // via __set
$user->age = 28;

echo $user; // {"name":"Kieu Nguyen","email":"kieu@example.com","age":28}

if ($user->isDirty()) {
    $user->save();
    // Output: Saving changes: {"name":"Kieu Nguyen","age":28}
}

$user->save(); // No changes to save
```

---

## When to Use Magic Methods

### ✅ Good Use Cases

**1. Framework/Library Code**
```php
<?php
// Laravel's Eloquent models use magic methods extensively
$user = User::find(1);
echo $user->name; // __get
$user->name = "New Name"; // __set
```

**2. Proxy/Wrapper Classes**
```php
<?php
class ApiClient {
    public function __call($method, $args) {
        // Forward all calls to API
        return $this->request($method, $args);
    }
}
```

**3. Dynamic Behavior**
```php
<?php
class QueryBuilder {
    public function __call($method, $args) {
        // Handle where(), orderBy(), limit(), etc.
    }
}
```

### ❌ When to Avoid

**1. Making Code Hard to Understand**
```php
<?php
// ❌ Too much magic, hard to know what methods exist
class MyClass {
    public function __call($method, $args) {
        // Does mysterious things
    }
}
```

**2. IDE Can't Autocomplete**
```php
<?php
// ❌ IDE doesn't know these properties exist
$user->magicProperty; // via __get

// ✅ Better: declare properties or use PHPDoc
/** @property string $name */
class User {
    public function __get($key) {}
}
```

**3. Performance-Critical Code**
Magic methods have small performance overhead.

---

## Best Practices

### 1. Document Magic Behavior

```php
<?php
/**
 * @property string $name User's name
 * @property string $email User's email
 * @property int $age User's age
 *
 * @method self setName(string $name)
 * @method self setEmail(string $email)
 * @method string getName()
 * @method string getEmail()
 */
class User {
    public function __get($key) {}
    public function __set($key, $value) {}
    public function __call($method, $args) {}
}
```

### 2. Use Type Hints When Possible

```php
<?php
// ✅ Good: type hint
public function __toString(): string {
    return $this->name;
}

// ✅ Good: return type
public function __get(string $name) {
    return $this->data[$name];
}
```

### 3. Keep Magic Methods Simple

```php
<?php
// ❌ Bad: too complex
public function __call($method, $args) {
    // 100 lines of complex logic
}

// ✅ Good: delegate to other methods
public function __call($method, $args) {
    if (str_starts_with($method, 'find')) {
        return $this->handleFindMethod($method, $args);
    }
    // ... etc
}
```

### 4. Validate in __set

```php
<?php
public function __set(string $name, $value): void {
    if ($name === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
        throw new InvalidArgumentException("Invalid email");
    }
    $this->data[$name] = $value;
}
```

---

## Try It Yourself

### Exercise 1: Smart Array

Create an `ArrayObject` class that:
- Uses `__get` to access array elements
- Uses `__set` to set array elements
- Uses `__isset` and `__unset`
- Uses `__toString` to output as JSON
- Uses `__count` to return element count (research this magic method!)

### Exercise 2: Logger

Create a `Logger` class that:
- Uses `__call` to handle methods like `info()`, `warning()`, `error()`
- Uses `__toString` to output all logs
- Uses `__destruct` to save logs to file when script ends

### Exercise 3: Event Dispatcher

Create an `EventDispatcher` that:
- Uses `__invoke` to dispatch events
- Uses `__call` to register listeners (on, once, off)
- Document with PHPDoc annotations

---

## Common Mistakes

### 1. Forgetting Return Type for __toString

```php
<?php
// ❌ Missing return type
public function __toString() {
    return $this->name;
}

// ✅ Must specify: string
public function __toString(): string {
    return $this->name;
}
```

### 2. Infinite Loops in __get

```php
<?php
// ❌ Infinite loop
public function __get($name) {
    return $this->$name; // Calls __get again!
}

// ✅ Use array or check existence
private array $data = [];

public function __get($name) {
    return $this->data[$name] ?? null;
}
```

### 3. Not Handling __clone Properly

```php
<?php
// ❌ Shallow clone - shares nested objects
public function __clone() {
    // Nothing here
}

// ✅ Deep clone nested objects
public function __clone() {
    $this->address = clone $this->address;
    $this->orders = array_map(fn($o) => clone $o, $this->orders);
}
```

---

## Quick Reference

```php
<?php
class Example {
    // Constructor
    public function __construct() {}

    // Destructor
    public function __destruct() {}

    // String conversion
    public function __toString(): string {}

    // Property overloading
    public function __get(string $name) {}
    public function __set(string $name, $value): void {}
    public function __isset(string $name): bool {}
    public function __unset(string $name): void {}

    // Method overloading
    public function __call(string $method, array $args) {}
    public static function __callStatic(string $method, array $args) {}

    // Object as function
    public function __invoke() {}

    // Cloning
    public function __clone() {}

    // Serialization
    public function __sleep(): array {}
    public function __wakeup(): void {}
}
```

---

## What's Next?

You now understand magic methods and all the core OOP concepts! But knowing the concepts isn't enough - you need to know how to apply them properly.

In the next lesson, you'll learn about **OOP best practices** including the SOLID principles - guidelines that help you write clean, maintainable, and scalable object-oriented code.

---

## Key Takeaways

- **Magic methods** start with `__` and are called automatically
- `__construct()` and `__destruct()` handle object lifecycle
- `__toString()` converts object to string
- `__get()` and `__set()` handle property access
- `__call()` handles method calls
- `__invoke()` makes objects callable
- `__clone()` handles object cloning
- Use sparingly - they can make code harder to understand
- Document magic behavior with PHPDoc
- Great for frameworks and libraries
- Keep implementations simple and clear
