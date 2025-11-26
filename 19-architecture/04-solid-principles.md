# Lesson 04 - SOLID Principles Explained

**Duration**: 3-4 hours

---

## Introduction

**SOLID** is an acronym for five design principles that make software more understandable, flexible, and maintainable. These principles were introduced by Robert C. Martin (Uncle Bob) and form the foundation of object-oriented design.

Think of SOLID principles as rules for building with LEGO blocks:
- Each block has one purpose (Single Responsibility)
- You can add new blocks without breaking existing ones (Open/Closed)
- Different blocks can replace each other if they fit (Liskov Substitution)
- Use only the connections you need (Interface Segregation)
- Blocks connect through standardized pegs, not glued together (Dependency Inversion)

Let's explore each principle with Laravel examples.

---

## S - Single Responsibility Principle (SRP)

**Definition**: A class should have one, and only one, reason to change.

In other words: **Each class should do one thing and do it well.**

### Violation Example

```php
// Bad: User class doing EVERYTHING
namespace App\Models;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Mail\WelcomeEmail;

class User extends Model
{
    // Model concerns (OK)
    protected $fillable = ['name', 'email', 'password'];

    // Validation logic (WRONG - should be in Request)
    public function validate(array $data): bool
    {
        if (strlen($data['password']) < 8) {
            return false;
        }
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        return true;
    }

    // Business logic (WRONG - should be in Service)
    public function register(array $data): bool
    {
        if (!$this->validate($data)) {
            return false;
        }

        $this->name = $data['name'];
        $this->email = $data['email'];
        $this->password = Hash::make($data['password']);
        $this->save();

        // Email logic (WRONG - should be separate)
        Mail::to($this->email)->send(new WelcomeEmail($this));

        // Logging (WRONG - should be separate)
        Log::info('User registered: ' . $this->email);

        return true;
    }

    // Report generation (WRONG - should be separate class)
    public function generateReport(): string
    {
        return "User Report for {$this->name}...";
    }
}
```

**Why is this bad?**

This class has multiple reasons to change:
1. Database schema changes (model structure)
2. Validation rules change
3. Registration process changes
4. Email template changes
5. Logging format changes
6. Report format changes

**One class, six reasons to change = violation of SRP**

### Correct Implementation

```php
// app/Models/User.php - ONE responsibility: Represent user entity
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $fillable = ['name', 'email', 'password'];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    // Only model-specific methods
}
```

```php
// app/Http/Requests/RegisterRequest.php - ONE responsibility: Validate registration
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
        ];
    }
}
```

```php
// app/Services/UserRegistrationService.php - ONE responsibility: Register users
namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserRegistrationService
{
    public function __construct(
        private UserNotificationService $notificationService,
        private ActivityLogger $logger
    ) {}

    public function register(array $data): User
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $this->notificationService->sendWelcomeEmail($user);
        $this->logger->logRegistration($user);

        return $user;
    }
}
```

```php
// app/Services/UserNotificationService.php - ONE responsibility: Send notifications
namespace App\Services;

use App\Models\User;
use App\Mail\WelcomeEmail;
use Illuminate\Support\Facades\Mail;

class UserNotificationService
{
    public function sendWelcomeEmail(User $user): void
    {
        Mail::to($user->email)->send(new WelcomeEmail($user));
    }
}
```

```php
// app/Services/Reports/UserReportGenerator.php - ONE responsibility: Generate reports
namespace App\Services\Reports;

use App\Models\User;

class UserReportGenerator
{
    public function generate(User $user): string
    {
        return "User Report for {$user->name}...";
    }
}
```

**Benefits:**
- Each class has one reason to change
- Easy to test each responsibility independently
- Easy to reuse components
- Changes are isolated - fixing email doesn't risk breaking registration

---

## O - Open/Closed Principle (OCP)

**Definition**: Software entities should be open for extension but closed for modification.

In other words: **You should be able to add new functionality without changing existing code.**

### Violation Example

```php
// Bad: Must modify class to add new payment methods
namespace App\Services;

class PaymentService
{
    public function processPayment(Order $order, string $method): bool
    {
        if ($method === 'stripe') {
            // Stripe payment logic
            $stripe = new \Stripe\StripeClient(config('stripe.secret'));
            $charge = $stripe->charges->create([
                'amount' => $order->total * 100,
                'currency' => 'usd',
            ]);
            return $charge->status === 'succeeded';
        }

        if ($method === 'paypal') {
            // PayPal payment logic
            $paypal = new PayPalClient();
            return $paypal->createPayment($order);
        }

        // Want to add Bitcoin? Must modify this class!
        // Want to add ApplePay? Must modify this class!

        throw new \Exception('Unknown payment method');
    }
}
```

**Problem:** Every new payment method requires modifying this class.

### Correct Implementation

```php
// app/Contracts/PaymentGatewayInterface.php - Abstraction
namespace App\Contracts;

use App\Models\Order;

interface PaymentGatewayInterface
{
    public function charge(Order $order): bool;
    public function refund(Order $order): bool;
}
```

```php
// app/Services/Payment/StripeGateway.php
namespace App\Services\Payment;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Order;

class StripeGateway implements PaymentGatewayInterface
{
    public function charge(Order $order): bool
    {
        $stripe = new \Stripe\StripeClient(config('stripe.secret'));
        $charge = $stripe->charges->create([
            'amount' => $order->total * 100,
            'currency' => 'usd',
        ]);
        return $charge->status === 'succeeded';
    }

    public function refund(Order $order): bool
    {
        // Stripe refund logic
    }
}
```

```php
// app/Services/Payment/PayPalGateway.php
namespace App\Services\Payment;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Order;

class PayPalGateway implements PaymentGatewayInterface
{
    public function charge(Order $order): bool
    {
        $paypal = new PayPalClient();
        return $paypal->createPayment($order);
    }

    public function refund(Order $order): bool
    {
        // PayPal refund logic
    }
}
```

```php
// app/Services/Payment/BitcoinGateway.php - NEW gateway, NO modification to existing code
namespace App\Services\Payment;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Order;

class BitcoinGateway implements PaymentGatewayInterface
{
    public function charge(Order $order): bool
    {
        // Bitcoin payment logic
    }

    public function refund(Order $order): bool
    {
        // Bitcoin refund logic
    }
}
```

```php
// app/Services/PaymentService.php - Doesn't need modification for new gateways!
namespace App\Services;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Order;

class PaymentService
{
    public function __construct(
        private PaymentGatewayInterface $gateway
    ) {}

    public function processPayment(Order $order): bool
    {
        return $this->gateway->charge($order);
    }

    public function processRefund(Order $order): bool
    {
        return $this->gateway->refund($order);
    }
}
```

```php
// app/Providers/AppServiceProvider.php
public function register(): void
{
    // Switch payment gateway without changing PaymentService
    $this->app->bind(PaymentGatewayInterface::class, function () {
        $gateway = config('payment.default_gateway');

        return match($gateway) {
            'stripe' => new StripeGateway(),
            'paypal' => new PayPalGateway(),
            'bitcoin' => new BitcoinGateway(),
        };
    });
}
```

**Benefits:**
- Add new payment methods without modifying existing code
- Existing gateways remain untouched and tested
- Easy to test each gateway independently
- Configuration-based gateway selection

---

## L - Liskov Substitution Principle (LSP)

**Definition**: Objects of a superclass should be replaceable with objects of a subclass without breaking the application.

In other words: **Subclasses must be substitutable for their base classes.**

### Violation Example

```php
// Bad: Bird base class
class Bird
{
    public function fly(): void
    {
        echo "Flying in the sky!";
    }
}

class Sparrow extends Bird
{
    // Works fine
}

class Penguin extends Bird
{
    public function fly(): void
    {
        throw new \Exception("Penguins can't fly!"); // Breaks LSP!
    }
}

// Usage
function makeBirdFly(Bird $bird)
{
    $bird->fly(); // Breaks with Penguin!
}

makeBirdFly(new Sparrow()); // OK
makeBirdFly(new Penguin()); // Exception! LSP violated
```

### Correct Implementation

```php
// Good: Proper abstraction
abstract class Bird
{
    abstract public function move(): void;
}

class FlyingBird extends Bird
{
    public function move(): void
    {
        echo "Flying in the sky!";
    }
}

class SwimmingBird extends Bird
{
    public function move(): void
    {
        echo "Swimming in water!";
    }
}

class Sparrow extends FlyingBird {}
class Penguin extends SwimmingBird {}

// Usage
function makeBirdMove(Bird $bird)
{
    $bird->move(); // Works with any bird!
}

makeBirdMove(new Sparrow()); // Flying in the sky!
makeBirdMove(new Penguin()); // Swimming in water!
```

### Laravel Example: Storage Drivers

```php
// Bad: Violates LSP
interface StorageInterface
{
    public function store(string $path, string $contents): bool;
    public function getUrl(string $path): string; // Problem: Not all storage has URLs
}

class S3Storage implements StorageInterface
{
    public function store(string $path, string $contents): bool
    {
        // Store in S3
        return true;
    }

    public function getUrl(string $path): string
    {
        return "https://s3.amazonaws.com/bucket/{$path}";
    }
}

class LocalStorage implements StorageInterface
{
    public function store(string $path, string $contents): bool
    {
        return file_put_contents($path, $contents) !== false;
    }

    public function getUrl(string $path): string
    {
        throw new \Exception("Local storage doesn't have public URLs!"); // LSP violation!
    }
}
```

```php
// Good: Proper interface segregation
interface StorageInterface
{
    public function store(string $path, string $contents): bool;
    public function retrieve(string $path): string;
}

interface PublicStorageInterface extends StorageInterface
{
    public function getUrl(string $path): string;
}

class S3Storage implements PublicStorageInterface
{
    public function store(string $path, string $contents): bool
    {
        // Store in S3
        return true;
    }

    public function retrieve(string $path): string
    {
        // Get from S3
    }

    public function getUrl(string $path): string
    {
        return "https://s3.amazonaws.com/bucket/{$path}";
    }
}

class LocalStorage implements StorageInterface
{
    public function store(string $path, string $contents): bool
    {
        return file_put_contents($path, $contents) !== false;
    }

    public function retrieve(string $path): string
    {
        return file_get_contents($path);
    }

    // No getUrl() method - doesn't implement PublicStorageInterface
}

// Usage
function storeFile(StorageInterface $storage, string $path, string $contents)
{
    $storage->store($path, $contents); // Works with ANY storage
}

function displayImage(PublicStorageInterface $storage, string $path)
{
    echo "<img src='{$storage->getUrl($path)}'>";
    // Only accepts storage with public URLs
}
```

---

## I - Interface Segregation Principle (ISP)

**Definition**: Clients should not be forced to depend on interfaces they don't use.

In other words: **Make fine-grained interfaces that are client-specific.**

### Violation Example

```php
// Bad: Fat interface forces implementing unused methods
interface WorkerInterface
{
    public function work(): void;
    public function eat(): void;
    public function sleep(): void;
    public function getPaid(): void;
}

class HumanWorker implements WorkerInterface
{
    public function work(): void
    {
        echo "Working...";
    }

    public function eat(): void
    {
        echo "Eating lunch...";
    }

    public function sleep(): void
    {
        echo "Sleeping...";
    }

    public function getPaid(): void
    {
        echo "Getting paid...";
    }
}

class RobotWorker implements WorkerInterface
{
    public function work(): void
    {
        echo "Working...";
    }

    // Robots don't eat or sleep!
    public function eat(): void
    {
        throw new \Exception("Robots don't eat!"); // Forced to implement
    }

    public function sleep(): void
    {
        throw new \Exception("Robots don't sleep!"); // Forced to implement
    }

    public function getPaid(): void
    {
        throw new \Exception("Robots don't get paid!"); // Forced to implement
    }
}
```

### Correct Implementation

```php
// Good: Split into specific interfaces
interface WorkableInterface
{
    public function work(): void;
}

interface FeedableInterface
{
    public function eat(): void;
}

interface SleepableInterface
{
    public function sleep(): void;
}

interface PayableInterface
{
    public function getPaid(): void;
}

class HumanWorker implements WorkableInterface, FeedableInterface, SleepableInterface, PayableInterface
{
    public function work(): void
    {
        echo "Working...";
    }

    public function eat(): void
    {
        echo "Eating lunch...";
    }

    public function sleep(): void
    {
        echo "Sleeping...";
    }

    public function getPaid(): void
    {
        echo "Getting paid...";
    }
}

class RobotWorker implements WorkableInterface
{
    public function work(): void
    {
        echo "Working...";
    }

    // Only implements what it needs!
}
```

### Laravel Example: Repository Interfaces

```php
// Bad: Forcing all repositories to implement methods they don't need
interface RepositoryInterface
{
    public function all();
    public function find(int $id);
    public function create(array $data);
    public function update(int $id, array $data);
    public function delete(int $id);
    public function search(string $query);      // Not all entities are searchable
    public function export(string $format);     // Not all need export
    public function archive(int $id);           // Not all support archiving
    public function restore(int $id);           // Not all support restoration
}

// ReadOnlyRepository forced to implement write methods
class LogRepository implements RepositoryInterface
{
    // ... read methods work fine

    public function create(array $data)
    {
        throw new \Exception("Logs are read-only!"); // ISP violation
    }

    public function update(int $id, array $data)
    {
        throw new \Exception("Logs are read-only!");
    }

    public function delete(int $id)
    {
        throw new \Exception("Logs are read-only!");
    }
}
```

```php
// Good: Segregated interfaces
interface ReadableInterface
{
    public function all();
    public function find(int $id);
}

interface WritableInterface
{
    public function create(array $data);
    public function update(int $id, array $data);
    public function delete(int $id);
}

interface SearchableInterface
{
    public function search(string $query);
}

interface ExportableInterface
{
    public function export(string $format);
}

interface ArchivableInterface
{
    public function archive(int $id);
    public function restore(int $id);
}

// Full CRUD repository
class UserRepository implements ReadableInterface, WritableInterface, SearchableInterface
{
    // Implements all needed methods
}

// Read-only repository
class LogRepository implements ReadableInterface
{
    public function all() { /* ... */ }
    public function find(int $id) { /* ... */ }
    // No write methods - perfectly fine!
}

// Exportable repository
class ReportRepository implements ReadableInterface, ExportableInterface
{
    public function all() { /* ... */ }
    public function find(int $id) { /* ... */ }
    public function export(string $format) { /* ... */ }
}
```

---

## D - Dependency Inversion Principle (DIP)

**Definition**: High-level modules should not depend on low-level modules. Both should depend on abstractions.

In other words: **Depend on interfaces, not concrete implementations.**

### Violation Example

```php
// Bad: High-level service depends on low-level implementation
namespace App\Services;

use App\Services\Payment\StripeGateway; // Direct dependency on concrete class

class OrderService
{
    private StripeGateway $paymentGateway;

    public function __construct()
    {
        $this->paymentGateway = new StripeGateway(); // Tightly coupled!
    }

    public function checkout(Order $order): bool
    {
        return $this->paymentGateway->charge($order);
    }
}

// Problems:
// 1. Can't use PayPal without changing OrderService
// 2. Can't test without actual Stripe calls
// 3. OrderService controls payment gateway creation
```

### Correct Implementation

```php
// Good: Both depend on abstraction
namespace App\Contracts;

interface PaymentGatewayInterface
{
    public function charge(Order $order): bool;
}
```

```php
// Low-level module implements interface
namespace App\Services\Payment;

use App\Contracts\PaymentGatewayInterface;

class StripeGateway implements PaymentGatewayInterface
{
    public function charge(Order $order): bool
    {
        // Stripe implementation
    }
}

class PayPalGateway implements PaymentGatewayInterface
{
    public function charge(Order $order): bool
    {
        // PayPal implementation
    }
}
```

```php
// High-level module depends on interface
namespace App\Services;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Order;

class OrderService
{
    public function __construct(
        private PaymentGatewayInterface $paymentGateway // Depends on interface!
    ) {}

    public function checkout(Order $order): bool
    {
        return $this->paymentGateway->charge($order);
    }
}
```

```php
// Dependency injection via service provider
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Contracts\PaymentGatewayInterface;
use App\Services\Payment\StripeGateway;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind interface to implementation
        $this->app->bind(
            PaymentGatewayInterface::class,
            StripeGateway::class
        );

        // Easy to swap implementation:
        // $this->app->bind(PaymentGatewayInterface::class, PayPalGateway::class);
    }
}
```

**Benefits:**
- OrderService doesn't know which gateway it uses
- Easy to swap payment gateways
- Easy to test with mock gateway
- Loose coupling

### Testing with DIP

```php
// tests/Unit/Services/OrderServiceTest.php
use Tests\TestCase;
use App\Services\OrderService;
use App\Contracts\PaymentGatewayInterface;
use App\Models\Order;
use Mockery;

class OrderServiceTest extends TestCase
{
    public function test_checkout_charges_payment()
    {
        // Mock the interface
        $mockGateway = Mockery::mock(PaymentGatewayInterface::class);
        $mockGateway->shouldReceive('charge')
            ->once()
            ->andReturn(true);

        // Inject mock
        $service = new OrderService($mockGateway);

        $order = new Order();
        $result = $service->checkout($order);

        $this->assertTrue($result);
    }
}
```

---

## SOLID in Practice: Refactoring Example

Let's refactor a messy controller using all SOLID principles.

### Before: Violates All SOLID Principles

```php
class OrderController extends Controller
{
    public function store(Request $request)
    {
        // Validation
        if (strlen($request->customer_name) < 3) {
            return back()->with('error', 'Name too short');
        }

        // Create order
        $order = new Order();
        $order->customer_name = $request->customer_name;
        $order->total = $request->total;
        $order->save();

        // Process payment
        if ($request->payment_method === 'stripe') {
            $stripe = new \Stripe\StripeClient(config('stripe.secret'));
            $stripe->charges->create([...]);
        } elseif ($request->payment_method === 'paypal') {
            $paypal = new PayPalClient();
            $paypal->createPayment([...]);
        }

        // Send email
        Mail::to($request->customer_email)->send(new OrderConfirmation($order));

        // Log
        Log::info('Order created: ' . $order->id);

        return redirect('/orders');
    }
}
```

### After: Follows SOLID Principles

```php
// S - Single Responsibility
// app/Http/Requests/CreateOrderRequest.php
class CreateOrderRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'customer_name' => 'required|min:3',
            'total' => 'required|numeric',
            'payment_method' => 'required|in:stripe,paypal',
        ];
    }
}

// O - Open/Closed, L - Liskov Substitution
// app/Contracts/PaymentGatewayInterface.php
interface PaymentGatewayInterface
{
    public function charge(Order $order): bool;
}

// app/Services/Payment/StripeGateway.php
class StripeGateway implements PaymentGatewayInterface
{
    public function charge(Order $order): bool { /* ... */ }
}

// app/Services/Payment/PayPalGateway.php
class PayPalGateway implements PaymentGatewayInterface
{
    public function charge(Order $order): bool { /* ... */ }
}

// I - Interface Segregation
// app/Contracts/NotificationInterface.php
interface NotificationInterface
{
    public function send(Order $order): void;
}

// app/Services/EmailNotificationService.php
class EmailNotificationService implements NotificationInterface
{
    public function send(Order $order): void
    {
        Mail::to($order->customer_email)->send(new OrderConfirmation($order));
    }
}

// D - Dependency Inversion
// app/Services/OrderService.php
class OrderService
{
    public function __construct(
        private PaymentGatewayInterface $paymentGateway,
        private NotificationInterface $notificationService
    ) {}

    public function createOrder(array $data): Order
    {
        $order = Order::create($data);

        $this->paymentGateway->charge($order);
        $this->notificationService->send($order);

        Log::info('Order created: ' . $order->id);

        return $order;
    }
}

// Controller - Clean and thin
class OrderController extends Controller
{
    public function __construct(
        private OrderService $orderService
    ) {}

    public function store(CreateOrderRequest $request)
    {
        $order = $this->orderService->createOrder($request->validated());

        return redirect('/orders')
            ->with('success', 'Order created successfully');
    }
}
```

---

## Quick Quiz

1. **Which principle states "a class should have one reason to change"?**
   - A) Open/Closed
   - B) Single Responsibility
   - C) Dependency Inversion
   - D) Interface Segregation

2. **What does "open for extension, closed for modification" mean?**
   - A) Never modify existing code
   - B) Add new features without changing existing code
   - C) Keep all methods public
   - D) Always extend base classes

3. **Which principle says "depend on abstractions, not concretions"?**
   - A) Single Responsibility
   - B) Liskov Substitution
   - C) Dependency Inversion
   - D) Interface Segregation

**Answers**: 1-B, 2-B, 3-C

---

## Summary

**SOLID Principles:**

1. **Single Responsibility (SRP)**: One class, one responsibility
2. **Open/Closed (OCP)**: Open for extension, closed for modification
3. **Liskov Substitution (LSP)**: Subclasses must be substitutable
4. **Interface Segregation (ISP)**: Many specific interfaces > one general interface
5. **Dependency Inversion (DIP)**: Depend on abstractions, not concrete classes

**Benefits:**
- More maintainable code
- Easier to test
- Flexible architecture
- Better code organization

**In Laravel:**
- Use Form Requests (SRP)
- Use interfaces and service providers (OCP, DIP)
- Create specific interfaces (ISP)
- Ensure proper inheritance (LSP)

These principles work together to create clean, maintainable applications. In the next lesson, we'll explore **Design Patterns** that implement these principles!
