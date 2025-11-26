# Lesson 06 - Dependency Injection

**Duration**: 3-4 hours

---

## Introduction

**Dependency Injection (DI)** is a technique where an object receives other objects it depends on, rather than creating them itself. It's one of the most important concepts in modern PHP development.

Think of it like ordering at a restaurant:
- **Without DI**: You go to the kitchen, select ingredients, cook the food yourself (tight coupling)
- **With DI**: You order from a menu, the chef prepares your food, waiter brings it to you (loose coupling)

Laravel's service container makes dependency injection incredibly powerful and easy to use.

---

## The Problem: Tight Coupling

Let's look at code without dependency injection:

```php
namespace App\Services;

use App\Services\Payment\StripeGateway;
use App\Services\Email\MailService;
use App\Repositories\OrderRepository;

class OrderService
{
    private $paymentGateway;
    private $mailService;
    private $orderRepository;

    public function __construct()
    {
        // Creating dependencies inside the class
        $this->paymentGateway = new StripeGateway(); // Tight coupling!
        $this->mailService = new MailService();      // Tight coupling!
        $this->orderRepository = new OrderRepository(); // Tight coupling!
    }

    public function createOrder(array $data): Order
    {
        $order = $this->orderRepository->create($data);
        $this->paymentGateway->charge($order);
        $this->mailService->sendOrderConfirmation($order);

        return $order;
    }
}
```

**Problems:**

1. **Can't swap implementations** - What if you want to use PayPal instead of Stripe?
2. **Hard to test** - Can't mock payment gateway or email service
3. **Hidden dependencies** - Not obvious what the class needs
4. **Can't reuse** - OrderRepository might need configuration you can't provide
5. **Violates Dependency Inversion Principle** - Depends on concrete classes, not abstractions

---

## The Solution: Dependency Injection

### Constructor Injection (Most Common)

```php
namespace App\Services;

use App\Contracts\PaymentGatewayInterface;
use App\Contracts\MailServiceInterface;
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Models\Order;

class OrderService
{
    // Dependencies injected through constructor
    public function __construct(
        private PaymentGatewayInterface $paymentGateway,
        private MailServiceInterface $mailService,
        private OrderRepositoryInterface $orderRepository
    ) {}

    public function createOrder(array $data): Order
    {
        $order = $this->orderRepository->create($data);
        $this->paymentGateway->charge($order);
        $this->mailService->sendOrderConfirmation($order);

        return $order;
    }
}
```

**Benefits:**

1. **Dependencies are explicit** - Clear what the class needs
2. **Easy to swap implementations** - Pass different gateway
3. **Testable** - Can inject mocks
4. **Loose coupling** - Depends on interfaces, not concrete classes
5. **Laravel resolves automatically** - No manual instantiation needed

---

## Laravel's Service Container

Laravel's **service container** (also called IoC container) automatically resolves dependencies.

### Automatic Resolution

```php
// app/Http/Controllers/OrderController.php
namespace App\Http\Controllers;

use App\Services\OrderService;

class OrderController extends Controller
{
    // Laravel automatically injects OrderService
    public function __construct(
        private OrderService $orderService
    ) {}

    public function store(Request $request)
    {
        // OrderService already resolved with all its dependencies!
        $order = $this->orderService->createOrder($request->validated());

        return redirect()->route('orders.show', $order);
    }
}
```

**How it works:**

1. Laravel sees `OrderService` type hint
2. Looks at `OrderService` constructor
3. Sees it needs `PaymentGatewayInterface`, `MailServiceInterface`, `OrderRepositoryInterface`
4. Resolves those from the container
5. Creates `OrderService` with all dependencies
6. Injects it into the controller

**Magic!** But it needs binding configuration...

---

## Binding Interfaces to Implementations

Tell Laravel which concrete class to use for each interface.

### Service Provider Bindings

```php
// app/Providers/AppServiceProvider.php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Contracts\PaymentGatewayInterface;
use App\Services\Payment\StripeGateway;
use App\Contracts\MailServiceInterface;
use App\Services\Email\MailService;
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Repositories\EloquentOrderRepository;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind interfaces to concrete implementations
        $this->app->bind(
            PaymentGatewayInterface::class,
            StripeGateway::class
        );

        $this->app->bind(
            MailServiceInterface::class,
            MailService::class
        );

        $this->app->bind(
            OrderRepositoryInterface::class,
            EloquentOrderRepository::class
        );
    }
}
```

Now when Laravel needs `PaymentGatewayInterface`, it automatically provides `StripeGateway`.

### Singleton Binding

Sometimes you want the same instance throughout the request:

```php
public function register(): void
{
    // New instance every time
    $this->app->bind(ReportGenerator::class, PdfReportGenerator::class);

    // Same instance every time (singleton)
    $this->app->singleton(CacheManager::class, RedisCacheManager::class);
}
```

```php
// Different instances
$report1 = app(ReportGenerator::class);
$report2 = app(ReportGenerator::class);
// $report1 !== $report2

// Same instance
$cache1 = app(CacheManager::class);
$cache2 = app(CacheManager::class);
// $cache1 === $cache2
```

### Contextual Binding

Different implementations based on where they're used:

```php
public function register(): void
{
    // OrderService gets StripeGateway
    $this->app->when(OrderService::class)
        ->needs(PaymentGatewayInterface::class)
        ->give(StripeGateway::class);

    // SubscriptionService gets RecurringGateway
    $this->app->when(SubscriptionService::class)
        ->needs(PaymentGatewayInterface::class)
        ->give(RecurringGateway::class);
}
```

---

## Types of Dependency Injection

### 1. Constructor Injection (Recommended)

Dependencies injected through constructor.

```php
class OrderService
{
    public function __construct(
        private PaymentGatewayInterface $gateway,
        private OrderRepository $repository
    ) {}
}
```

**Pros:**
- Dependencies immutable after construction
- Clear required dependencies
- Works with auto-wiring

**Cons:**
- Can have many constructor parameters (smell of too many responsibilities)

### 2. Method Injection

Dependencies injected into specific methods.

```php
class OrderService
{
    // No constructor dependencies

    public function createOrder(
        array $data,
        PaymentGatewayInterface $gateway  // Injected per method call
    ): Order {
        $order = Order::create($data);
        $gateway->charge($order);
        return $order;
    }
}

// Usage
$service = new OrderService();
$service->createOrder($data, new StripeGateway());  // Inject on call
$service->createOrder($data, new PayPalGateway());  // Different gateway
```

**Pros:**
- Flexible - different dependencies per call
- Only inject when needed

**Cons:**
- Caller must provide dependencies
- Less common in Laravel

### 3. Property Injection (Avoid)

Dependencies set via public properties.

```php
class OrderService
{
    public PaymentGatewayInterface $gateway; // Public property

    public function createOrder(array $data): Order
    {
        $order = Order::create($data);
        $this->gateway->charge($order);  // What if not set?
        return $order;
    }
}

// Usage
$service = new OrderService();
$service->gateway = new StripeGateway();  // Must remember to set!
$service->createOrder($data);
```

**Cons:**
- Easy to forget to set
- Mutable after construction
- No type safety until runtime

**Don't use property injection in new code.**

---

## Advanced DI Patterns

### Pattern 1: Factory with Dependency Injection

```php
// app/Services/Reports/ReportFactory.php
namespace App\Services\Reports;

use App\Contracts\ReportInterface;
use Illuminate\Contracts\Container\Container;

class ReportFactory
{
    public function __construct(
        private Container $container  // Inject the container
    ) {}

    public function create(string $type): ReportInterface
    {
        $class = match($type) {
            'pdf' => PdfReport::class,
            'excel' => ExcelReport::class,
            'csv' => CsvReport::class,
            default => throw new \InvalidArgumentException("Unknown type: {$type}")
        };

        // Container resolves with all dependencies
        return $this->container->make($class);
    }
}
```

```php
// PdfReport can have its own dependencies
class PdfReport implements ReportInterface
{
    public function __construct(
        private FileSystem $filesystem,  // Auto-injected
        private Logger $logger           // Auto-injected
    ) {}

    public function generate(array $data): string
    {
        // Use injected dependencies
        $this->logger->info('Generating PDF report');
        // ...
    }
}
```

### Pattern 2: Optional Dependencies

Some dependencies are optional:

```php
namespace App\Services;

use App\Contracts\CacheInterface;
use Psr\Log\LoggerInterface;

class UserService
{
    public function __construct(
        private UserRepository $repository,
        private ?CacheInterface $cache = null,      // Optional
        private ?LoggerInterface $logger = null     // Optional
    ) {}

    public function findUser(int $id): ?User
    {
        // Use cache if available
        if ($this->cache) {
            $cached = $this->cache->get("user.{$id}");
            if ($cached) {
                return $cached;
            }
        }

        $user = $this->repository->find($id);

        // Log if available
        if ($this->logger) {
            $this->logger->info("User {$id} fetched from database");
        }

        // Cache if available
        if ($this->cache && $user) {
            $this->cache->set("user.{$id}", $user, 3600);
        }

        return $user;
    }
}
```

### Pattern 3: Binding with Configuration

```php
// app/Providers/PaymentServiceProvider.php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Contracts\PaymentGatewayInterface;
use App\Services\Payment\StripeGateway;
use App\Services\Payment\PayPalGateway;

class PaymentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            PaymentGatewayInterface::class,
            function ($app) {
                // Choose implementation based on config
                $gateway = config('payment.default_gateway');

                return match($gateway) {
                    'stripe' => new StripeGateway(
                        config('payment.stripe.secret_key')
                    ),
                    'paypal' => new PayPalGateway(
                        config('payment.paypal.client_id'),
                        config('payment.paypal.client_secret')
                    ),
                    default => throw new \Exception("Unknown gateway: {$gateway}")
                };
            }
        );
    }
}
```

```php
// config/payment.php
return [
    'default_gateway' => env('PAYMENT_GATEWAY', 'stripe'),

    'stripe' => [
        'secret_key' => env('STRIPE_SECRET_KEY'),
    ],

    'paypal' => [
        'client_id' => env('PAYPAL_CLIENT_ID'),
        'client_secret' => env('PAYPAL_CLIENT_SECRET'),
    ],
];
```

---

## Resolving from Container

### Method 1: Type Hint (Automatic)

```php
// Controller
public function __construct(OrderService $orderService)
{
    // Automatically resolved
}

// Route closure
Route::get('/orders', function (OrderService $orderService) {
    return $orderService->getRecentOrders();
});
```

### Method 2: app() Helper

```php
$orderService = app(OrderService::class);

// With interface
$gateway = app(PaymentGatewayInterface::class);
```

### Method 3: resolve() Helper

```php
$orderService = resolve(OrderService::class);
```

### Method 4: Container Directly

```php
$orderService = app()->make(OrderService::class);
```

### Method 5: Dependency Injection in Methods

```php
class OrderController extends Controller
{
    // Method injection
    public function store(Request $request, OrderService $orderService)
    {
        $order = $orderService->createOrder($request->validated());
        return redirect()->route('orders.show', $order);
    }
}
```

---

## Testing with Dependency Injection

DI makes testing incredibly easy.

### Without DI (Hard to Test)

```php
class OrderService
{
    public function __construct()
    {
        $this->gateway = new StripeGateway(); // Real Stripe calls!
    }

    public function createOrder(array $data): Order
    {
        $order = Order::create($data);
        $this->gateway->charge($order);  // Charges real money in test!
        return $order;
    }
}

// Test must use real Stripe = slow, expensive, flaky
```

### With DI (Easy to Test)

```php
class OrderService
{
    public function __construct(
        private PaymentGatewayInterface $gateway
    ) {}

    public function createOrder(array $data): Order
    {
        $order = Order::create($data);
        $this->gateway->charge($order);
        return $order;
    }
}
```

```php
// tests/Unit/Services/OrderServiceTest.php
namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\OrderService;
use App\Contracts\PaymentGatewayInterface;
use App\Models\Order;
use Mockery;

class OrderServiceTest extends TestCase
{
    public function test_creates_order_and_charges_payment()
    {
        // Mock the payment gateway
        $mockGateway = Mockery::mock(PaymentGatewayInterface::class);
        $mockGateway->shouldReceive('charge')
            ->once()
            ->with(Mockery::type(Order::class))
            ->andReturn(true);

        // Inject mock
        $service = new OrderService($mockGateway);

        // Test without real payment processing!
        $order = $service->createOrder([
            'customer_id' => 1,
            'total' => 100,
        ]);

        $this->assertInstanceOf(Order::class, $order);
    }
}
```

### Swapping Bindings in Tests

```php
// tests/Feature/OrderTest.php
namespace Tests\Feature;

use Tests\TestCase;
use App\Contracts\PaymentGatewayInterface;

class OrderTest extends TestCase
{
    public function test_user_can_create_order()
    {
        // Replace real gateway with fake
        $fakeGateway = new FakePaymentGateway();

        $this->app->instance(
            PaymentGatewayInterface::class,
            $fakeGateway
        );

        // Now all OrderService instances use fake gateway
        $response = $this->post('/orders', [
            'customer_id' => 1,
            'items' => [...]
        ]);

        $response->assertRedirect('/orders');
        $this->assertTrue($fakeGateway->wasCharged);
    }
}

class FakePaymentGateway implements PaymentGatewayInterface
{
    public bool $wasCharged = false;

    public function charge(Order $order): bool
    {
        $this->wasCharged = true;
        return true;
    }
}
```

---

## Common Pitfalls

### Pitfall 1: Service Locator Anti-Pattern

```php
// Bad: Using container as service locator
class OrderService
{
    public function createOrder(array $data): Order
    {
        // Accessing container directly
        $gateway = app(PaymentGatewayInterface::class);  // Hidden dependency!
        $repository = app(OrderRepository::class);       // Hidden dependency!

        $order = $repository->create($data);
        $gateway->charge($order);

        return $order;
    }
}

// Good: Constructor injection
class OrderService
{
    public function __construct(
        private PaymentGatewayInterface $gateway,
        private OrderRepository $repository
    ) {}

    public function createOrder(array $data): Order
    {
        $order = $this->repository->create($data);
        $this->gateway->charge($order);
        return $order;
    }
}
```

**Why bad?** Hidden dependencies make testing harder and violate Dependency Inversion.

### Pitfall 2: Constructor Over-Injection

```php
// Bad: Too many dependencies (God class smell)
class OrderService
{
    public function __construct(
        private OrderRepository $orderRepository,
        private PaymentGateway $paymentGateway,
        private EmailService $emailService,
        private SmsService $smsService,
        private InventoryService $inventoryService,
        private TaxCalculator $taxCalculator,
        private ShippingCalculator $shippingCalculator,
        private CouponValidator $couponValidator,
        private LoyaltyService $loyaltyService,
        private InvoiceGenerator $invoiceGenerator
        // ... 10 dependencies = too much!
    ) {}
}

// Good: Split responsibilities
class OrderCreationService
{
    public function __construct(
        private OrderRepository $repository,
        private PricingService $pricingService
    ) {}
}

class OrderFulfillmentService
{
    public function __construct(
        private PaymentGateway $paymentGateway,
        private InventoryService $inventoryService,
        private NotificationService $notificationService
    ) {}
}
```

**Rule of thumb:** 3-4 dependencies = OK, 5+ = consider splitting the class.

### Pitfall 3: Circular Dependencies

```php
// Bad: A depends on B, B depends on A
class OrderService
{
    public function __construct(
        private CustomerService $customerService  // OrderService needs CustomerService
    ) {}
}

class CustomerService
{
    public function __construct(
        private OrderService $orderService  // CustomerService needs OrderService
    ) {}
}
// Laravel can't resolve this!
```

**Solution:** Refactor to remove circular dependency (use events, repositories, or split services).

---

## Best Practices

1. **Use constructor injection** for required dependencies
2. **Type hint interfaces**, not concrete classes
3. **Bind in service providers**, not hard-code
4. **Keep constructors simple** - just assignment
5. **Avoid service locator pattern** - don't call `app()` inside classes
6. **Limit dependencies** - 3-4 max per class
7. **Use contextual binding** when different contexts need different implementations
8. **Mock dependencies in tests** - never test with real external services

---

## Quick Quiz

1. **What is the main benefit of dependency injection?**
   - A) Makes code run faster
   - B) Reduces coupling and improves testability
   - C) Reduces lines of code
   - D) Automatically fixes bugs

2. **Where should you bind interfaces to implementations?**
   - A) Controller constructor
   - B) Service constructor
   - C) Service provider
   - D) Routes file

3. **Which type of injection is recommended in Laravel?**
   - A) Property injection
   - B) Setter injection
   - C) Constructor injection
   - D) Global variables

**Answers**: 1-B, 2-C, 3-C

---

## Summary

**Dependency Injection:**
- Objects receive their dependencies instead of creating them
- Promotes loose coupling and testability
- Laravel's container automatically resolves dependencies

**Key Concepts:**
- **Constructor injection** (recommended)
- **Interface binding** in service providers
- **Type hinting** for automatic resolution
- **Mocking** for testing

**Container Bindings:**
```php
$this->app->bind(Interface::class, Implementation::class);
$this->app->singleton(Interface::class, Implementation::class);
```

**Benefits:**
- Testable code
- Flexible implementations
- Clear dependencies
- Loose coupling

In the next lesson, we'll explore **Code Organization** - how to structure your Laravel application for maximum maintainability!
