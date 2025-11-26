# Lesson 11 - OOP Best Practices & SOLID Principles

## Why Best Practices Matter

You now know HOW to write OOP code. But knowing the syntax isn't enough - you need to know how to write GOOD OOP code.

**Bad OOP code:**
- Hard to understand
- Hard to change
- Hard to test
- Breaks when you add features
- Full of bugs

**Good OOP code:**
- Easy to understand
- Easy to change
- Easy to test
- Flexible for new features
- Fewer bugs

The **SOLID principles** are five guidelines that help you write good OOP code. They were popularized by Robert C. Martin ("Uncle Bob") and are considered essential for professional developers.

---

## The SOLID Principles

**S**ingle Responsibility Principle
**O**pen/Closed Principle
**L**iskov Substitution Principle
**I**nterface Segregation Principle
**D**ependency Inversion Principle

Let's explore each one!

---

## S - Single Responsibility Principle (SRP)

**"A class should have one, and only one, reason to change."**

Each class should do ONE thing and do it well.

### ❌ Bad Example: God Class

```php
<?php
class User {
    public string $name;
    public string $email;
    public string $password;

    // Handles user data
    public function save(): void {
        // Database logic here
    }

    // Handles validation
    public function validate(): bool {
        // Validation logic
    }

    // Handles email sending
    public function sendWelcomeEmail(): void {
        // Email logic here
    }

    // Handles password hashing
    public function hashPassword(): void {
        // Hashing logic
    }

    // Handles PDF generation
    public function generatePdfReport(): void {
        // PDF logic here
    }
}

// This class has TOO MANY responsibilities!
// If email system changes, must modify User class
// If database changes, must modify User class
// If PDF library changes, must modify User class
```

### ✅ Good Example: Separate Responsibilities

```php
<?php
// 1. User entity - just data
class User {
    public function __construct(
        public string $name,
        public string $email,
        private string $password
    ) {}

    public function getPassword(): string {
        return $this->password;
    }
}

// 2. User repository - handles database
class UserRepository {
    public function save(User $user): void {
        // Database logic
        echo "Saving user to database\n";
    }

    public function find(int $id): ?User {
        // Find user logic
        return null;
    }
}

// 3. User validator - handles validation
class UserValidator {
    public function validate(User $user): bool {
        if (empty($user->name)) return false;
        if (!filter_var($user->email, FILTER_VALIDATE_EMAIL)) return false;
        return true;
    }
}

// 4. Email service - handles emails
class EmailService {
    public function sendWelcomeEmail(User $user): void {
        echo "Sending welcome email to {$user->email}\n";
    }
}

// 5. Password hasher - handles hashing
class PasswordHasher {
    public function hash(string $password): string {
        return password_hash($password, PASSWORD_DEFAULT);
    }
}

// Usage
$user = new User("Kieu", "kieu@example.com", "secret123");
$validator = new UserValidator();
$repository = new UserRepository();
$emailService = new EmailService();

if ($validator->validate($user)) {
    $repository->save($user);
    $emailService->sendWelcomeEmail($user);
}
```

**Benefits:**
- Each class has one reason to change
- Easier to understand
- Easier to test
- Easier to reuse

---

## O - Open/Closed Principle

**"Classes should be open for extension but closed for modification."**

You should be able to add new features without changing existing code.

### ❌ Bad Example: Modifying for New Features

```php
<?php
class PaymentProcessor {
    public function process(string $type, float $amount): void {
        if ($type === 'credit_card') {
            echo "Processing credit card payment: $$amount\n";
        } elseif ($type === 'paypal') {
            echo "Processing PayPal payment: $$amount\n";
        } elseif ($type === 'bank_transfer') {
            echo "Processing bank transfer: $$amount\n";
        }
        // Adding new payment method = modifying this class!
    }
}

// To add cryptocurrency, must modify PaymentProcessor ❌
```

### ✅ Good Example: Extend Without Modifying

```php
<?php
interface PaymentMethod {
    public function process(float $amount): void;
}

class CreditCardPayment implements PaymentMethod {
    public function process(float $amount): void {
        echo "Processing credit card payment: $$amount\n";
    }
}

class PayPalPayment implements PaymentMethod {
    public function process(float $amount): void {
        echo "Processing PayPal payment: $$amount\n";
    }
}

class BankTransferPayment implements PaymentMethod {
    public function process(float $amount): void {
        echo "Processing bank transfer: $$amount\n";
    }
}

// PaymentProcessor doesn't need to change for new methods!
class PaymentProcessor {
    public function process(PaymentMethod $method, float $amount): void {
        $method->process($amount);
    }
}

// Adding cryptocurrency = just create new class, no modifications!
class CryptoPayment implements PaymentMethod {
    public function process(float $amount): void {
        echo "Processing crypto payment: $$amount\n";
    }
}

// Usage
$processor = new PaymentProcessor();
$processor->process(new CreditCardPayment(), 99.99);
$processor->process(new CryptoPayment(), 0.001); // New feature, no changes to processor!
```

---

## L - Liskov Substitution Principle

**"Objects of a superclass should be replaceable with objects of its subclasses without breaking the application."**

Subclasses must behave like their parent class.

### ❌ Bad Example: Breaking Parent Contract

```php
<?php
class Bird {
    public function fly(): void {
        echo "Flying...\n";
    }
}

class Sparrow extends Bird {
    // Can fly - no problem
}

class Penguin extends Bird {
    public function fly(): void {
        // Penguins can't fly!
        throw new Exception("Penguins can't fly!");
    }
}

function makeBirdFly(Bird $bird): void {
    $bird->fly(); // Expects all birds to fly
}

makeBirdFly(new Sparrow()); // ✅ Works
makeBirdFly(new Penguin()); // ❌ Exception! Breaks LSP
```

### ✅ Good Example: Proper Abstraction

```php
<?php
class Bird {
    public function eat(): void {
        echo "Eating...\n";
    }
}

class FlyingBird extends Bird {
    public function fly(): void {
        echo "Flying...\n";
    }
}

class Sparrow extends FlyingBird {
    // Can fly
}

class Penguin extends Bird {
    // Doesn't extend FlyingBird, so no fly() method
    public function swim(): void {
        echo "Swimming...\n";
    }
}

function makeFlyingBirdFly(FlyingBird $bird): void {
    $bird->fly(); // Only accepts flying birds
}

makeFlyingBirdFly(new Sparrow()); // ✅ Works
// makeFlyingBirdFly(new Penguin()); // ✅ Won't compile - type error
```

---

## I - Interface Segregation Principle

**"Clients should not be forced to depend on interfaces they don't use."**

Better to have many small, specific interfaces than one large, general interface.

### ❌ Bad Example: Fat Interface

```php
<?php
interface Worker {
    public function work(): void;
    public function eat(): void;
    public function sleep(): void;
}

class Human implements Worker {
    public function work(): void { echo "Working...\n"; }
    public function eat(): void { echo "Eating...\n"; }
    public function sleep(): void { echo "Sleeping...\n"; }
}

class Robot implements Worker {
    public function work(): void { echo "Working...\n"; }

    // Forced to implement these even though robots don't eat or sleep!
    public function eat(): void {
        throw new Exception("Robots don't eat");
    }

    public function sleep(): void {
        throw new Exception("Robots don't sleep");
    }
}
```

### ✅ Good Example: Segregated Interfaces

```php
<?php
interface Workable {
    public function work(): void;
}

interface Eatable {
    public function eat(): void;
}

interface Sleepable {
    public function sleep(): void;
}

class Human implements Workable, Eatable, Sleepable {
    public function work(): void { echo "Working...\n"; }
    public function eat(): void { echo "Eating...\n"; }
    public function sleep(): void { echo "Sleeping...\n"; }
}

class Robot implements Workable {
    // Only implements what it needs
    public function work(): void { echo "Working...\n"; }
}

// Functions depend on specific interfaces
function makeWork(Workable $worker): void {
    $worker->work();
}

function feedWorker(Eatable $worker): void {
    $worker->eat();
}

makeWork(new Human()); // ✅
makeWork(new Robot()); // ✅

feedWorker(new Human()); // ✅
// feedWorker(new Robot()); // ✅ Won't compile - Robot doesn't implement Eatable
```

---

## D - Dependency Inversion Principle

**"High-level modules should not depend on low-level modules. Both should depend on abstractions."**

Depend on interfaces, not concrete classes.

### ❌ Bad Example: Tight Coupling

```php
<?php
class MySQLDatabase {
    public function save(array $data): void {
        echo "Saving to MySQL: " . json_encode($data) . "\n";
    }
}

class UserController {
    private MySQLDatabase $database; // Tightly coupled to MySQL!

    public function __construct() {
        $this->database = new MySQLDatabase(); // Creates dependency itself
    }

    public function createUser(string $name, string $email): void {
        $this->database->save(['name' => $name, 'email' => $email]);
    }
}

// Problems:
// 1. Can't switch to PostgreSQL without modifying UserController
// 2. Can't test UserController without real MySQL database
// 3. UserController decides which database to use
```

### ✅ Good Example: Dependency Injection

```php
<?php
// Abstraction
interface Database {
    public function save(array $data): void;
}

// Implementations
class MySQLDatabase implements Database {
    public function save(array $data): void {
        echo "Saving to MySQL: " . json_encode($data) . "\n";
    }
}

class PostgreSQLDatabase implements Database {
    public function save(array $data): void {
        echo "Saving to PostgreSQL: " . json_encode($data) . "\n";
    }
}

class InMemoryDatabase implements Database {
    private array $storage = [];

    public function save(array $data): void {
        $this->storage[] = $data;
        echo "Saved to memory\n";
    }
}

// High-level module depends on abstraction
class UserController {
    public function __construct(
        private Database $database // Depends on interface!
    ) {}

    public function createUser(string $name, string $email): void {
        $this->database->save(['name' => $name, 'email' => $email]);
    }
}

// Usage - inject dependency from outside
$controller1 = new UserController(new MySQLDatabase());
$controller1->createUser("Kieu", "kieu@example.com");

$controller2 = new UserController(new PostgreSQLDatabase());
$controller2->createUser("John", "john@example.com");

// Easy to test with fake database
$controller3 = new UserController(new InMemoryDatabase());
$controller3->createUser("Test", "test@example.com");
```

---

## Practical Example: Applying SOLID

Let's build a notification system applying all SOLID principles:

```php
<?php
// ===== Interfaces (D - Depend on abstractions) =====

interface NotificationChannel {
    public function send(string $recipient, string $message): void;
}

interface User {
    public function getNotificationPreference(): string;
    public function getEmail(): string;
    public function getPhone(): string;
}

// ===== Implementations (O - Open for extension) =====

class EmailChannel implements NotificationChannel {
    public function send(string $recipient, string $message): void {
        echo "Sending email to $recipient: $message\n";
    }
}

class SmsChannel implements NotificationChannel {
    public function send(string $recipient, string $message): void {
        echo "Sending SMS to $recipient: $message\n";
    }
}

class PushChannel implements NotificationChannel {
    public function send(string $recipient, string $message): void {
        echo "Sending push notification to $recipient: $message\n";
    }
}

// ===== User Implementation (L - Proper inheritance) =====

class StandardUser implements User {
    public function __construct(
        private string $email,
        private string $phone,
        private string $preference = 'email'
    ) {}

    public function getNotificationPreference(): string {
        return $this->preference;
    }

    public function getEmail(): string {
        return $this->email;
    }

    public function getPhone(): string {
        return $this->phone;
    }
}

// ===== Services (S - Single responsibility) =====

// S: Only handles channel selection
class ChannelSelector {
    private array $channels = [];

    public function __construct() {
        $this->channels = [
            'email' => new EmailChannel(),
            'sms' => new SmsChannel(),
            'push' => new PushChannel(),
        ];
    }

    public function getChannel(string $type): NotificationChannel {
        return $this->channels[$type] ?? $this->channels['email'];
    }
}

// S: Only handles recipient resolution
class RecipientResolver {
    public function resolve(User $user, string $channelType): string {
        return match($channelType) {
            'email' => $user->getEmail(),
            'sms' => $user->getPhone(),
            'push' => $user->getEmail(), // Push uses email as ID
            default => $user->getEmail()
        };
    }
}

// S: Orchestrates the notification process
class NotificationService {
    public function __construct(
        private ChannelSelector $channelSelector,
        private RecipientResolver $recipientResolver
    ) {}

    public function notify(User $user, string $message): void {
        $channelType = $user->getNotificationPreference();
        $channel = $this->channelSelector->getChannel($channelType);
        $recipient = $this->recipientResolver->resolve($user, $channelType);

        $channel->send($recipient, $message);
    }
}

// ===== Usage =====

$channelSelector = new ChannelSelector();
$recipientResolver = new RecipientResolver();
$notificationService = new NotificationService($channelSelector, $recipientResolver);

$user1 = new StandardUser("kieu@example.com", "+84123456789", "email");
$user2 = new StandardUser("john@example.com", "+84987654321", "sms");

$notificationService->notify($user1, "Your order has shipped!");
// Output: Sending email to kieu@example.com: Your order has shipped!

$notificationService->notify($user2, "Your order has shipped!");
// Output: Sending SMS to +84987654321: Your order has shipped!

// Easy to add new channel - just implement interface (O)
// Easy to test - inject fake dependencies (D)
// Easy to understand - each class has one job (S)
```

---

## Other Important Principles

### DRY - Don't Repeat Yourself

**Don't copy-paste code. Extract to methods/classes.**

```php
<?php
// ❌ Bad: Repetition
class OrderController {
    public function createOrder() {
        $userId = $_SESSION['user_id'];
        $user = $this->db->query("SELECT * FROM users WHERE id = $userId");
        // Create order logic
    }

    public function updateOrder() {
        $userId = $_SESSION['user_id'];
        $user = $this->db->query("SELECT * FROM users WHERE id = $userId");
        // Update order logic
    }
}

// ✅ Good: Extract common logic
class OrderController {
    private function getCurrentUser(): User {
        $userId = $_SESSION['user_id'];
        return $this->db->query("SELECT * FROM users WHERE id = $userId");
    }

    public function createOrder() {
        $user = $this->getCurrentUser();
        // Create order logic
    }

    public function updateOrder() {
        $user = $this->getCurrentUser();
        // Update order logic
    }
}
```

### KISS - Keep It Simple, Stupid

**Simple solutions are better than complex ones.**

```php
<?php
// ❌ Bad: Over-engineered
class StringHelper {
    public function reverseString(string $str): string {
        $result = [];
        for ($i = strlen($str) - 1; $i >= 0; $i--) {
            $result[] = $str[$i];
        }
        return implode('', $result);
    }
}

// ✅ Good: Use built-in function
class StringHelper {
    public function reverseString(string $str): string {
        return strrev($str);
    }
}
```

### YAGNI - You Aren't Gonna Need It

**Don't add functionality until you need it.**

```php
<?php
// ❌ Bad: Adding features "just in case"
class User {
    public string $name;
    public string $email;
    public ?string $middleName;        // Not needed yet
    public ?string $nickname;          // Not needed yet
    public ?string $preferredLanguage; // Not needed yet
    public ?string $timezone;          // Not needed yet
    public ?array $socialLinks;        // Not needed yet
    // ... 20 more "just in case" fields
}

// ✅ Good: Start minimal
class User {
    public string $name;
    public string $email;
    // Add more fields when actually needed
}
```

### Composition Over Inheritance

**Prefer composing objects over inheriting.**

```php
<?php
// ❌ Bad: Deep inheritance
class Employee {}
class Manager extends Employee {}
class SeniorManager extends Manager {}
class Director extends SeniorManager {}
class VicePresident extends Director {}

// ✅ Good: Composition
interface Role {
    public function getPermissions(): array;
}

class Employee {
    public function __construct(
        public string $name,
        private Role $role  // Composed, not inherited
    ) {}
}

class ManagerRole implements Role {
    public function getPermissions(): array {
        return ['manage_team', 'approve_expenses'];
    }
}
```

---

## Code Smells to Avoid

### 1. God Class

A class that does too much.

```php
<?php
// ❌ 1000+ line class that does everything
class Application {
    // Database logic
    // Business logic
    // View rendering
    // Email sending
    // File handling
    // ... everything
}
```

### 2. Long Method

Methods should be 5-15 lines ideally.

```php
<?php
// ❌ 200 line method
public function processOrder() {
    // Validate
    // Calculate price
    // Apply discount
    // Process payment
    // Update inventory
    // Send confirmation
    // Update analytics
    // Log everything
    // ... 200 lines later
}

// ✅ Break into smaller methods
public function processOrder() {
    $this->validate();
    $total = $this->calculateTotal();
    $this->processPayment($total);
    $this->updateInventory();
    $this->sendConfirmation();
}
```

### 3. Primitive Obsession

Using primitives instead of objects.

```php
<?php
// ❌ Primitives everywhere
function processPayment(string $cardNumber, string $cvv, string $expiry) {}

// ✅ Use value objects
class CreditCard {
    public function __construct(
        public string $number,
        public string $cvv,
        public string $expiry
    ) {}
}

function processPayment(CreditCard $card) {}
```

---

## Quick Checklist for Good OOP

- [ ] Each class has one clear responsibility (SRP)
- [ ] Use interfaces for flexibility (OCP, DIP)
- [ ] Child classes can replace parents (LSP)
- [ ] Interfaces are small and focused (ISP)
- [ ] Inject dependencies, don't create them (DIP)
- [ ] Methods are short (5-15 lines)
- [ ] Classes are reasonably sized (< 200 lines)
- [ ] No code duplication (DRY)
- [ ] Simple solutions over complex ones (KISS)
- [ ] Only add features you need now (YAGNI)
- [ ] Composition over inheritance
- [ ] Descriptive names for classes and methods
- [ ] Type hints everywhere
- [ ] Tests for critical logic

---

## Try It Yourself

### Exercise 1: Refactor God Class

Take this God class and refactor it following SOLID:

```php
<?php
class BlogPost {
    public string $title;
    public string $content;

    public function save() {
        // Database logic
    }

    public function validate(): bool {
        // Validation logic
    }

    public function sendNotification() {
        // Email logic
    }

    public function generateSlug() {
        // Slug logic
    }

    public function render(): string {
        // HTML rendering
    }
}
```

Break it into: Post (entity), PostRepository, PostValidator, NotificationService, SlugGenerator, PostRenderer.

### Exercise 2: Apply Open/Closed

Create a discount system that's open for extension:
- Interface: `Discount`
- Implementations: `PercentageDiscount`, `FixedAmountDiscount`, `BuyOneGetOneDiscount`
- `DiscountCalculator` that accepts any Discount

### Exercise 3: Dependency Injection

Refactor this to use dependency injection:

```php
<?php
class UserService {
    private $db;
    private $mailer;

    public function __construct() {
        $this->db = new Database();
        $this->mailer = new Mailer();
    }
}
```

---

## Final Thoughts

**SOLID principles are guidelines, not laws.**

- Don't over-engineer simple code
- Apply when it makes code clearer
- Start simple, refactor when needed
- Real-world code is often a compromise
- Experience teaches when to apply which principle

**The goal is maintainable code that:**
- Other developers can understand
- Can be tested easily
- Can be changed without breaking everything
- Can grow as requirements change

---

## What's Next?

Congratulations! You've completed the OOP fundamentals:
- Classes and objects
- Properties and methods
- Constructors
- Encapsulation
- Inheritance
- Interfaces
- Abstract classes
- Traits
- Namespaces
- Magic methods
- Best practices & SOLID

**You're now ready to:**
- Build real OOP applications
- Work with frameworks like Laravel
- Understand professional PHP code
- Write maintainable, testable code

**Next module:** You'll learn to persist your objects in a database using MySQL and PDO!

---

## Key Takeaways

- **SOLID principles** guide good OOP design
- **Single Responsibility**: One class, one job
- **Open/Closed**: Extend without modifying
- **Liskov Substitution**: Subclasses must behave like parents
- **Interface Segregation**: Small, focused interfaces
- **Dependency Inversion**: Depend on abstractions
- **DRY**: Don't repeat yourself
- **KISS**: Keep it simple
- **YAGNI**: Don't add until needed
- **Composition > Inheritance**
- Keep classes small and focused
- Keep methods short
- Use descriptive names
- Type hint everything
- Test your code
