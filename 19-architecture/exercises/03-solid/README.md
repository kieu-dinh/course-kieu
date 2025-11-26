# Exercise 19.3 - SOLID Principles

## Objective

Learn and apply SOLID principles to write better object-oriented code.

---

## Task

Analyze and refactor code to follow SOLID principles.

### SOLID Principles

1. **S - Single Responsibility Principle**
   - Each class should have one reason to change
   - One responsibility per class

2. **O - Open/Closed Principle**
   - Classes should be open for extension, closed for modification
   - Use inheritance and polymorphism

3. **L - Liskov Substitution Principle**
   - Derived classes should be substitutable for base classes
   - Don't violate parent contracts

4. **I - Interface Segregation Principle**
   - Clients should not depend on interfaces they don't use
   - Create smaller, focused interfaces

5. **D - Dependency Inversion Principle**
   - Depend on abstractions, not concrete implementations
   - Inject dependencies

---

## Exercise 1: Single Responsibility

**Bad Code:**
```php
class UserManager
{
    public function createUser($data)
    {
        // Validate
        if (empty($data['email'])) throw new Exception('Email required');

        // Create in DB
        $user = User::create($data);

        // Send email
        Mail::send('welcome', ['user' => $user]);

        // Log
        Log::info('User created: ' . $user->id);

        return $user;
    }
}
```

This class has 4 responsibilities: validation, creation, email, logging.

**Good Code:**
```php
class UserValidator
{
    public function validate(array $data): bool
    {
        if (empty($data['email'])) {
            throw new Exception('Email required');
        }
        return true;
    }
}

class UserRepository
{
    public function create(array $data): User
    {
        return User::create($data);
    }
}

class UserNotifier
{
    public function notifyWelcome(User $user): void
    {
        Mail::send('welcome', ['user' => $user]);
    }
}

class UserLogger
{
    public function logCreation(User $user): void
    {
        Log::info('User created: ' . $user->id);
    }
}

class UserService
{
    public function __construct(
        private UserValidator $validator,
        private UserRepository $repository,
        private UserNotifier $notifier,
        private UserLogger $logger
    ) {}

    public function createUser(array $data): User
    {
        $this->validator->validate($data);
        $user = $this->repository->create($data);
        $this->notifier->notifyWelcome($user);
        $this->logger->logCreation($user);
        return $user;
    }
}
```

---

## Exercise 2: Open/Closed Principle

**Bad Code:**
```php
class PaymentProcessor
{
    public function process($payment, $method)
    {
        if ($method === 'credit_card') {
            // Process credit card
        } elseif ($method === 'paypal') {
            // Process PayPal
        } elseif ($method === 'stripe') {
            // Process Stripe
        }
        // Need to modify this class for each new payment method!
    }
}
```

**Good Code:**
```php
interface PaymentMethod
{
    public function pay(float $amount): bool;
}

class CreditCardPayment implements PaymentMethod
{
    public function pay(float $amount): bool
    {
        // Credit card logic
        return true;
    }
}

class PayPalPayment implements PaymentMethod
{
    public function pay(float $amount): bool
    {
        // PayPal logic
        return true;
    }
}

class PaymentProcessor
{
    public function process(PaymentMethod $method, float $amount): bool
    {
        return $method->pay($amount);
    }
}

// Can add new payment methods without modifying PaymentProcessor
class StripePayment implements PaymentMethod
{
    public function pay(float $amount): bool
    {
        // Stripe logic
        return true;
    }
}
```

---

## Exercise 3: Liskov Substitution Principle

**Bad Code:**
```php
class Bird
{
    public function fly(): void
    {
        echo "Flying...";
    }
}

class Penguin extends Bird
{
    public function fly(): void
    {
        throw new Exception("Penguins can't fly!");
    }
}

// This violates LSP - can't substitute Penguin for Bird
function makeBirdFly(Bird $bird)
{
    $bird->fly(); // Might throw exception!
}
```

**Good Code:**
```php
interface Animal
{
    public function move(): void;
}

interface Flyable
{
    public function fly(): void;
}

class Eagle implements Animal, Flyable
{
    public function move(): void
    {
        $this->fly();
    }

    public function fly(): void
    {
        echo "Eagle flying...";
    }
}

class Penguin implements Animal
{
    public function move(): void
    {
        echo "Penguin swimming...";
    }
}

// Correct usage
function moveAnimal(Animal $animal): void
{
    $animal->move(); // Works for all animals
}

function makeFly(Flyable $animal): void
{
    $animal->fly(); // Only for flyable animals
}
```

---

## Exercise 4: Interface Segregation

**Bad Code:**
```php
interface Worker
{
    public function work(): void;
    public function eat(): void;
    public function sleep(): void;
    public function washCar(): void; // Not all workers wash cars!
}

class Developer implements Worker
{
    public function work() { }
    public function eat() { }
    public function sleep() { }
    public function washCar() { } // Developer doesn't wash cars!
}
```

**Good Code:**
```php
interface Worker
{
    public function work(): void;
}

interface Eater
{
    public function eat(): void;
}

interface Sleeper
{
    public function sleep(): void;
}

interface CarWasher
{
    public function washCar(): void;
}

class Developer implements Worker, Eater, Sleeper
{
    public function work() { }
    public function eat() { }
    public function sleep() { }
}

class CarWashWorker implements Worker, Eater, Sleeper, CarWasher
{
    public function work() { }
    public function eat() { }
    public function sleep() { }
    public function washCar() { }
}
```

---

## Exercise 5: Dependency Inversion

**Bad Code:**
```php
class OrderService
{
    private MySQLDatabase $database; // Depends on concrete class!

    public function __construct()
    {
        $this->database = new MySQLDatabase();
    }

    public function createOrder($data)
    {
        return $this->database->insert('orders', $data);
    }
}
```

**Good Code:**
```php
interface DatabaseInterface
{
    public function insert(string $table, array $data);
}

class MySQLDatabase implements DatabaseInterface
{
    public function insert(string $table, array $data) { }
}

class MongoDBDatabase implements DatabaseInterface
{
    public function insert(string $table, array $data) { }
}

class OrderService
{
    public function __construct(
        private DatabaseInterface $database // Depends on abstraction!
    ) {}

    public function createOrder($data)
    {
        return $this->database->insert('orders', $data);
    }
}

// Can use with any database implementation
$service = new OrderService(new MySQLDatabase());
// or
$service = new OrderService(new MongoDBDatabase());
```

---

## Refactoring Exercise

Refactor the provided code to follow SOLID principles:

```php
// Current code - violates multiple SOLID principles
class UserController
{
    public function store(Request $request)
    {
        // Validation
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8'
        ];

        if (!$this->validate($request->all(), $rules)) {
            return response()->json(['error' => 'Validation failed'], 422);
        }

        // Create user
        $user = User::create($request->all());

        // Send email
        Mail::to($user->email)->send(new WelcomeMail($user));

        // Log
        Log::info('User registered: ' . $user->email);

        // Calculate something
        $days = \Carbon\Carbon::now()->diffInDays($user->created_at);

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'created' => $days . ' days ago'
        ]);
    }

    private function validate($data, $rules)
    {
        // Validation logic
    }
}
```

---

## Checklist

- [ ] Understand each SOLID principle
- [ ] Identify violations in existing code
- [ ] Refactor using single responsibility
- [ ] Use interfaces for abstraction
- [ ] Implement dependency injection
- [ ] Each class has one reason to change
- [ ] Code is more maintainable and testable
- [ ] Can extend without modifying existing code

---

## Bonus Challenges

1. Apply all SOLID principles to your Item/Order system
2. Create a base service/repository with SOLID design
3. Refactor existing code in your project to follow SOLID
4. Write tests that verify SOLID compliance
5. Document design decisions in code comments

---

## Resources

- Research each principle deeply
- Look for violations in your codebase
- Refactor incrementally
- Test after each change
