# Lesson 02 - Service Layer Pattern

**Duration**: 3-4 hours

---

## Introduction

The **Service Layer Pattern** is one of the most practical and immediately useful architectural patterns you'll implement in Laravel. It sits between your controllers and models, containing your application's business logic.

Think of services as the "brain" of your application. Controllers are the "hands" that receive requests, models are the "memory" that stores data, and services are the "brain" that makes decisions and orchestrates actions.

In this lesson, we'll learn when to use services, how to structure them, and best practices for keeping them maintainable.

---

## What is a Service?

A **service** is a class that:
- Contains business logic
- Orchestrates multiple operations
- Coordinates between different parts of your application
- Is reusable across controllers, commands, jobs, and APIs

### When to Create a Service

Create a service when:

1. **Business logic is complex** - More than simple CRUD
2. **Multiple steps are involved** - Orchestrating several operations
3. **Logic needs to be reused** - Used in multiple places (web, API, CLI)
4. **External services are involved** - Payment processing, email, APIs
5. **Transaction coordination** - Multiple database operations that must succeed/fail together

### When NOT to Create a Service

Don't create a service when:

1. **Simple CRUD operations** - Direct model interaction is fine
2. **Single responsibility already clear** - Controller handles it well
3. **No business logic** - Just passing data through
4. **Only used once** - No reusability benefit

---

## Real-World Example: User Registration

Let's see how a service improves a typical user registration flow.

### Before: Fat Controller

```php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Mail\WelcomeEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RegisterController extends Controller
{
    public function register(Request $request)
    {
        // Validation
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
        ]);

        DB::beginTransaction();
        try {
            // Create user
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            // Assign default role
            $role = Role::where('name', 'customer')->first();
            $user->roles()->attach($role->id);

            // Create profile
            $user->profile()->create([
                'bio' => '',
                'avatar' => 'default-avatar.png',
            ]);

            // Create settings with defaults
            $user->settings()->create([
                'email_notifications' => true,
                'newsletter' => false,
                'theme' => 'light',
            ]);

            // Send welcome email
            Mail::to($user->email)->send(new WelcomeEmail($user));

            // Log registration
            Log::info('New user registered', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);

            // Fire event
            event(new UserRegistered($user));

            DB::commit();

            // Auto-login
            auth()->login($user);

            return redirect('/dashboard')
                ->with('success', 'Welcome! Your account has been created.');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Registration failed: ' . $e->getMessage());

            return back()
                ->withInput()
                ->with('error', 'Registration failed. Please try again.');
        }
    }
}
```

**Problems:**
- Controller has 60+ lines
- Mixes HTTP concerns with business logic
- Hard to test
- Can't reuse for API registration
- Can't register users from command line
- Can't test registration logic without HTTP

### After: Service Layer

```php
// app/Services/Auth/UserRegistrationService.php
namespace App\Services\Auth;

use App\Models\User;
use App\Models\Role;
use App\Mail\WelcomeEmail;
use App\Events\UserRegistered;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserRegistrationService
{
    public function register(array $data): User
    {
        DB::beginTransaction();
        try {
            $user = $this->createUser($data);
            $this->assignDefaultRole($user);
            $this->createUserProfile($user);
            $this->createUserSettings($user);

            DB::commit();

            // Post-registration actions (outside transaction)
            $this->sendWelcomeEmail($user);
            $this->logRegistration($user);
            $this->fireRegistrationEvent($user);

            return $user;
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Registration failed: ' . $e->getMessage());
            throw $e;
        }
    }

    private function createUser(array $data): User
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
    }

    private function assignDefaultRole(User $user): void
    {
        $role = Role::where('name', 'customer')->first();
        $user->roles()->attach($role->id);
    }

    private function createUserProfile(User $user): void
    {
        $user->profile()->create([
            'bio' => '',
            'avatar' => 'default-avatar.png',
        ]);
    }

    private function createUserSettings(User $user): void
    {
        $user->settings()->create([
            'email_notifications' => true,
            'newsletter' => false,
            'theme' => 'light',
        ]);
    }

    private function sendWelcomeEmail(User $user): void
    {
        Mail::to($user->email)->send(new WelcomeEmail($user));
    }

    private function logRegistration(User $user): void
    {
        Log::info('New user registered', [
            'user_id' => $user->id,
            'email' => $user->email,
        ]);
    }

    private function fireRegistrationEvent(User $user): void
    {
        event(new UserRegistered($user));
    }
}
```

```php
// app/Http/Controllers/Auth/RegisterController.php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\UserRegistrationService;
use App\Http\Requests\Auth\RegisterRequest;

class RegisterController extends Controller
{
    public function __construct(
        private UserRegistrationService $registrationService
    ) {}

    public function register(RegisterRequest $request)
    {
        try {
            $user = $this->registrationService->register(
                $request->validated()
            );

            auth()->login($user);

            return redirect('/dashboard')
                ->with('success', 'Welcome! Your account has been created.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Registration failed. Please try again.');
        }
    }
}
```

**Benefits:**
- Controller is now 15 lines (down from 60+)
- Business logic separated from HTTP concerns
- Service reusable from API, CLI, jobs
- Each method has single responsibility
- Easy to test each part
- Clear transaction boundaries

---

## Service Organization Strategies

### Strategy 1: Action-Based Services (Recommended for Most Cases)

One service per major action or use case.

```
app/Services/
├── Auth/
│   ├── UserRegistrationService.php
│   ├── UserLoginService.php
│   └── PasswordResetService.php
├── Order/
│   ├── OrderCreationService.php
│   ├── OrderCancellationService.php
│   └── OrderFulfillmentService.php
└── Payment/
    ├── PaymentProcessingService.php
    └── RefundService.php
```

**When to use:**
- Complex operations with multiple steps
- Clear use cases (user stories)
- Operations that need transaction management

```php
// app/Services/Order/OrderCreationService.php
class OrderCreationService
{
    public function createOrder(int $customerId, array $items): Order
    {
        // Focus: Creating orders
    }
}

// app/Services/Order/OrderCancellationService.php
class OrderCancellationService
{
    public function cancelOrder(Order $order, string $reason): void
    {
        // Focus: Cancelling orders
    }
}
```

### Strategy 2: Entity-Based Services

One service managing all operations for an entity.

```
app/Services/
├── UserService.php
├── OrderService.php
└── ProductService.php
```

**When to use:**
- Simpler domains
- Operations are closely related
- Entity lifecycle management

```php
// app/Services/OrderService.php
class OrderService
{
    public function create(array $data): Order { }
    public function update(Order $order, array $data): Order { }
    public function cancel(Order $order): void { }
    public function complete(Order $order): void { }
}
```

**Warning:** These can become "God Classes" if not careful. Split when > 10 methods.

### Strategy 3: Domain Services

Services that don't belong to a specific entity but serve a domain concept.

```php
// app/Services/PricingService.php
class PricingService
{
    public function calculateOrderTotal(Order $order): float
    {
        $subtotal = $this->calculateSubtotal($order);
        $discount = $this->calculateDiscount($order);
        $tax = $this->calculateTax($subtotal - $discount);
        $shipping = $this->calculateShipping($order);

        return $subtotal - $discount + $tax + $shipping;
    }

    public function calculateDiscount(Order $order): float
    {
        // Pricing domain logic
    }

    public function calculateTax(float $amount): float
    {
        // Tax calculation logic
    }
}
```

---

## Service Communication Patterns

### Pattern 1: Services Calling Other Services

Services can depend on other services for complex workflows.

```php
// app/Services/Order/OrderCreationService.php
class OrderCreationService
{
    public function __construct(
        private InventoryService $inventoryService,
        private PricingService $pricingService,
        private PaymentService $paymentService,
        private NotificationService $notificationService
    ) {}

    public function createOrder(int $customerId, array $items): Order
    {
        // Check inventory
        $this->inventoryService->reserveStock($items);

        // Calculate pricing
        $pricing = $this->pricingService->calculateOrderPricing($items, $customerId);

        // Create order
        $order = Order::create([
            'customer_id' => $customerId,
            'subtotal' => $pricing['subtotal'],
            'tax' => $pricing['tax'],
            'total' => $pricing['total'],
        ]);

        // Process payment
        $this->paymentService->charge($order);

        // Send notification
        $this->notificationService->sendOrderConfirmation($order);

        return $order;
    }
}
```

**Pros:**
- Clear dependencies
- Easy to test with mocks
- Follows Single Responsibility Principle

**Cons:**
- Can create deep dependency chains
- Risk of circular dependencies

### Pattern 2: Event-Driven Communication

Services trigger events, listeners handle side effects.

```php
// app/Services/Order/OrderCreationService.php
class OrderCreationService
{
    public function createOrder(int $customerId, array $items): Order
    {
        $order = Order::create([...]);

        // Fire event - don't directly call other services
        event(new OrderCreated($order));

        return $order;
    }
}

// app/Listeners/ReserveStockForOrder.php
class ReserveStockForOrder
{
    public function __construct(
        private InventoryService $inventoryService
    ) {}

    public function handle(OrderCreated $event): void
    {
        $this->inventoryService->reserveStock($event->order->items);
    }
}

// app/Listeners/SendOrderConfirmation.php
class SendOrderConfirmation
{
    public function handle(OrderCreated $event): void
    {
        Mail::to($event->order->customer->email)
            ->send(new OrderConfirmation($event->order));
    }
}
```

**Pros:**
- Loose coupling
- Easy to add/remove side effects
- Async processing possible

**Cons:**
- Harder to track flow
- No direct return values from side effects

---

## Data Transfer Objects (DTOs)

Instead of passing arrays to services, use DTOs for type safety and clarity.

### Without DTOs

```php
$service->createOrder([
    'customer_id' => 1,
    'items' => [...],
    'shipping_address' => [...],
    'billing_address' => [...],
]);
// What if you mistype a key? PHP won't catch it until runtime
```

### With DTOs

```php
// app/DataTransferObjects/CreateOrderDTO.php
namespace App\DataTransferObjects;

class CreateOrderDTO
{
    public function __construct(
        public readonly int $customerId,
        public readonly array $items,
        public readonly AddressDTO $shippingAddress,
        public readonly AddressDTO $billingAddress,
        public readonly ?string $promoCode = null
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            customerId: $data['customer_id'],
            items: $data['items'],
            shippingAddress: AddressDTO::fromArray($data['shipping_address']),
            billingAddress: AddressDTO::fromArray($data['billing_address']),
            promoCode: $data['promo_code'] ?? null
        );
    }
}
```

```php
// app/Services/Order/OrderCreationService.php
class OrderCreationService
{
    public function createOrder(CreateOrderDTO $dto): Order
    {
        // Type-safe access
        $order = Order::create([
            'customer_id' => $dto->customerId,
            'promo_code' => $dto->promoCode,
            // ...
        ]);

        foreach ($dto->items as $item) {
            // ...
        }

        return $order;
    }
}
```

```php
// Controller usage
public function store(CreateOrderRequest $request)
{
    $dto = CreateOrderDTO::fromRequest($request->validated());
    $order = $this->orderService->createOrder($dto);
    // ...
}
```

**Benefits:**
- Type safety
- IDE autocomplete
- Clear method signatures
- Validation in one place
- Immutable data structures

---

## Error Handling in Services

### Strategy 1: Let Exceptions Bubble Up

```php
class OrderCreationService
{
    public function createOrder(array $data): Order
    {
        // Just throw exceptions, let caller handle them
        if ($this->inventoryService->getAvailableStock($productId) < $quantity) {
            throw new InsufficientStockException("Not enough stock for product {$productId}");
        }

        return Order::create($data);
    }
}

// Controller catches and converts to HTTP response
public function store(Request $request)
{
    try {
        $order = $this->service->createOrder($request->validated());
        return response()->json($order, 201);
    } catch (InsufficientStockException $e) {
        return response()->json(['error' => $e->getMessage()], 422);
    } catch (\Exception $e) {
        Log::error('Order creation failed', ['error' => $e->getMessage()]);
        return response()->json(['error' => 'Server error'], 500);
    }
}
```

### Strategy 2: Result Objects

Return success/failure objects instead of throwing exceptions.

```php
// app/Services/Results/ServiceResult.php
class ServiceResult
{
    public function __construct(
        public readonly bool $success,
        public readonly mixed $data = null,
        public readonly ?string $error = null
    ) {}

    public static function success(mixed $data): self
    {
        return new self(success: true, data: $data);
    }

    public static function failure(string $error): self
    {
        return new self(success: false, error: $error);
    }
}
```

```php
class OrderCreationService
{
    public function createOrder(array $data): ServiceResult
    {
        if ($this->inventoryService->getAvailableStock($productId) < $quantity) {
            return ServiceResult::failure("Not enough stock for product {$productId}");
        }

        $order = Order::create($data);
        return ServiceResult::success($order);
    }
}

// Controller
public function store(Request $request)
{
    $result = $this->service->createOrder($request->validated());

    if (!$result->success) {
        return back()->with('error', $result->error);
    }

    return redirect()
        ->route('orders.show', $result->data)
        ->with('success', 'Order created!');
}
```

**Choose based on:**
- **Exceptions**: For truly exceptional cases (should rarely happen)
- **Result objects**: For expected failures (validation, business rules)

---

## Testing Services

Services are easy to test because they're independent of HTTP.

```php
// tests/Unit/Services/OrderCreationServiceTest.php
namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\Order\OrderCreationService;
use App\Services\InventoryService;
use App\Services\PricingService;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderCreationServiceTest extends TestCase
{
    use RefreshDatabase;

    private OrderCreationService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new OrderCreationService(
            new InventoryService(),
            new PricingService()
        );
    }

    public function test_creates_order_with_valid_data()
    {
        $customer = Customer::factory()->create();
        $product = Product::factory()->create([
            'price' => 100,
            'stock' => 10
        ]);

        $order = $this->service->createOrder($customer->id, [
            ['product_id' => $product->id, 'quantity' => 2]
        ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'customer_id' => $customer->id,
        ]);

        $this->assertEquals(200, $order->subtotal);
    }

    public function test_throws_exception_when_insufficient_stock()
    {
        $customer = Customer::factory()->create();
        $product = Product::factory()->create(['stock' => 5]);

        $this->expectException(InsufficientStockException::class);

        $this->service->createOrder($customer->id, [
            ['product_id' => $product->id, 'quantity' => 10]
        ]);
    }

    public function test_applies_customer_discount()
    {
        $customer = Customer::factory()->create(['loyalty_points' => 1000]);
        $product = Product::factory()->create(['price' => 100]);

        $order = $this->service->createOrder($customer->id, [
            ['product_id' => $product->id, 'quantity' => 1]
        ]);

        $this->assertGreaterThan(0, $order->discount);
    }
}
```

### Mocking Dependencies

```php
public function test_sends_confirmation_email()
{
    Mail::fake();

    $customer = Customer::factory()->create();
    $product = Product::factory()->create();

    $order = $this->service->createOrder($customer->id, [
        ['product_id' => $product->id, 'quantity' => 1]
    ]);

    Mail::assertSent(OrderConfirmation::class, function ($mail) use ($order) {
        return $mail->order->id === $order->id;
    });
}
```

---

## Best Practices

### 1. Single Responsibility

Each service should do ONE thing well.

```php
// Bad: Service doing too much
class OrderService
{
    public function createOrder() {}
    public function cancelOrder() {}
    public function processPayment() {}
    public function sendInvoice() {}
    public function calculateShipping() {}
    public function applyDiscount() {}
    public function generateReport() {}
}

// Good: Split by responsibility
class OrderCreationService {}
class OrderCancellationService {}
class PaymentProcessingService {}
class InvoiceService {}
class ShippingCalculator {}
class DiscountCalculator {}
class OrderReportGenerator {}
```

### 2. Dependency Injection

Always inject dependencies in constructor.

```php
// Bad: Creating dependencies inside
class OrderService
{
    public function createOrder()
    {
        $payment = new PaymentService(); // Hard-coded dependency
        $payment->process();
    }
}

// Good: Injected dependencies
class OrderService
{
    public function __construct(
        private PaymentService $paymentService
    ) {}

    public function createOrder()
    {
        $this->paymentService->process();
    }
}
```

### 3. Return Values

Services should return meaningful values.

```php
// Bad: Void return with side effects hidden
public function createOrder(array $data): void
{
    $order = Order::create($data);
    // Created order is lost
}

// Good: Return created entity
public function createOrder(array $data): Order
{
    return Order::create($data);
}
```

### 4. Transaction Management

Services should manage their own transactions.

```php
public function createOrder(array $data): Order
{
    DB::beginTransaction();
    try {
        $order = Order::create($data);
        $this->createOrderItems($order, $data['items']);
        $this->updateInventory($data['items']);

        DB::commit();
        return $order;
    } catch (\Exception $e) {
        DB::rollback();
        throw $e;
    }
}
```

### 5. Private Methods for Clarity

Break complex logic into private methods.

```php
public function createOrder(array $data): Order
{
    $this->validateInventory($data['items']);

    $order = $this->persistOrder($data);
    $this->persistOrderItems($order, $data['items']);
    $this->reduceInventory($data['items']);

    $this->sendNotifications($order);

    return $order;
}

private function validateInventory(array $items): void { }
private function persistOrder(array $data): Order { }
private function persistOrderItems(Order $order, array $items): void { }
private function reduceInventory(array $items): void { }
private function sendNotifications(Order $order): void { }
```

---

## Common Patterns

### Pattern: Service Factory

For complex service instantiation.

```php
// app/Services/Factories/PaymentServiceFactory.php
class PaymentServiceFactory
{
    public function make(string $gateway): PaymentServiceInterface
    {
        return match($gateway) {
            'stripe' => app(StripePaymentService::class),
            'paypal' => app(PaypalPaymentService::class),
            'manual' => app(ManualPaymentService::class),
            default => throw new \Exception("Unknown payment gateway: {$gateway}")
        };
    }
}

// Usage
public function processPayment(Order $order)
{
    $paymentService = $this->factory->make($order->payment_method);
    $paymentService->charge($order);
}
```

### Pattern: Service Pipeline

Chain multiple services together.

```php
// app/Services/Order/OrderProcessingPipeline.php
class OrderProcessingPipeline
{
    public function __construct(
        private OrderValidationService $validator,
        private OrderCreationService $creator,
        private PaymentService $payment,
        private FulfillmentService $fulfillment
    ) {}

    public function process(array $data): Order
    {
        $this->validator->validate($data);
        $order = $this->creator->create($data);
        $this->payment->charge($order);
        $this->fulfillment->initiate($order);

        return $order;
    }
}
```

---

## Quick Quiz

1. **When should you create a service?**
   - A) Always, for every controller action
   - B) When business logic is complex or needs to be reused
   - C) Only for API endpoints
   - D) Never, models should handle everything

2. **What should a service method return?**
   - A) void (nothing)
   - B) The created/modified entity or result object
   - C) An array
   - D) JSON response

3. **Where should transaction management happen?**
   - A) Controller
   - B) Model
   - C) Service
   - D) Database

**Answers**: 1-B, 2-B, 3-C

---

## Summary

**Service Layer Pattern** separates business logic from controllers:

**Key concepts:**
- Services contain business logic and orchestrate operations
- Controllers become thin HTTP handlers
- Easy to test, reuse, and maintain
- Use DTOs for type-safe data transfer
- Manage transactions within services
- Return meaningful values

**Organization strategies:**
- Action-based services (one per use case)
- Entity-based services (one per domain entity)
- Domain services (cross-entity logic)

**Best practices:**
- Single responsibility per service
- Dependency injection
- Clear return types
- Private methods for clarity
- Proper error handling

In the next lesson, we'll explore the **Repository Pattern** to abstract database operations and make services even more testable and flexible!
