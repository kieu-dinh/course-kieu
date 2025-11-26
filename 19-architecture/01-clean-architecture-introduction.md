# Lesson 01 - Clean Architecture Introduction

**Duration**: 3-4 hours

---

## Introduction

As your Laravel applications grow beyond simple CRUD operations, you'll notice that controllers become bloated, models contain too much logic, and making changes becomes increasingly difficult. This is where **clean architecture** comes in.

Clean architecture is a set of principles and patterns that help you organize your code in a way that makes it:
- **Testable** - Easy to write unit tests
- **Maintainable** - Changes don't break everything
- **Flexible** - Swap implementations without rewriting
- **Scalable** - Grows with your application

Think of architecture as the blueprint of your application. Just as a building needs a solid foundation and structure, your code needs organized layers that work together harmoniously.

---

## The Problem: Spaghetti Code

Let's look at a typical "fat controller" that does everything:

```php
// Bad: Controller doing EVERYTHING
class OrderController extends Controller
{
    public function store(Request $request)
    {
        // Validation
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        // Check stock availability
        foreach ($validated['items'] as $item) {
            $product = Product::find($item['product_id']);
            if ($product->stock < $item['quantity']) {
                return back()->with('error', 'Insufficient stock for ' . $product->name);
            }
        }

        // Calculate totals
        $subtotal = 0;
        foreach ($validated['items'] as $item) {
            $product = Product::find($item['product_id']);
            $subtotal += $product->price * $item['quantity'];
        }

        // Apply discount
        $customer = Customer::find($validated['customer_id']);
        $discount = 0;
        if ($customer->orders()->count() >= 10) {
            $discount = $subtotal * 0.10; // 10% loyal customer discount
        }

        $tax = ($subtotal - $discount) * 0.20; // 20% tax
        $total = $subtotal - $discount + $tax;

        // Create order
        DB::beginTransaction();
        try {
            $order = Order::create([
                'customer_id' => $validated['customer_id'],
                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax' => $tax,
                'total' => $total,
                'status' => 'pending',
            ]);

            // Create order items and reduce stock
            foreach ($validated['items'] as $item) {
                $product = Product::find($item['product_id']);

                $order->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                ]);

                $product->decrement('stock', $item['quantity']);
            }

            // Send email notification
            Mail::to($customer->email)->send(new OrderConfirmation($order));

            // Log activity
            Log::info('Order created', ['order_id' => $order->id]);

            DB::commit();

            return redirect()->route('orders.show', $order)
                ->with('success', 'Order created successfully');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Order creation failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to create order');
        }
    }
}
```

**What's wrong here?**

1. **Too many responsibilities** - Validation, business logic, database operations, email, logging
2. **Hard to test** - Need to set up database, emails, everything
3. **Difficult to reuse** - What if you need to create an order from an API? Command? Queue?
4. **Impossible to maintain** - One change can break multiple things
5. **No separation of concerns** - Everything is tangled together

---

## The Solution: Layered Architecture

Clean architecture divides your application into layers, each with a specific responsibility:

```
┌─────────────────────────────────────────┐
│         Presentation Layer              │
│    (Controllers, Views, API Routes)     │
└─────────────────────────────────────────┘
                    ↓
┌─────────────────────────────────────────┐
│         Application Layer               │
│         (Services, Use Cases)           │
└─────────────────────────────────────────┘
                    ↓
┌─────────────────────────────────────────┐
│          Domain Layer                   │
│      (Business Logic, Rules)            │
└─────────────────────────────────────────┘
                    ↓
┌─────────────────────────────────────────┐
│       Infrastructure Layer              │
│   (Database, APIs, Email, Storage)      │
└─────────────────────────────────────────┘
```

### Layer Responsibilities

**1. Presentation Layer** (Controllers, Views, API Controllers)
- Receives user input
- Validates requests
- Calls application services
- Returns responses
- **Should NOT contain business logic**

**2. Application Layer** (Services)
- Coordinates use cases
- Orchestrates domain logic
- Manages transactions
- Handles external services (email, notifications)
- **Contains application-specific business rules**

**3. Domain Layer** (Models, Value Objects, Domain Events)
- Core business entities
- Business rules and validation
- Domain logic
- **Independent of frameworks and databases**

**4. Infrastructure Layer** (Repositories, External APIs)
- Database access
- External service integration
- File system operations
- **Implementation details hidden behind interfaces**

---

## Refactoring Our Order Example

Let's refactor the messy controller using clean architecture principles.

### Step 1: Extract Service Layer

```php
// app/Services/OrderService.php
namespace App\Services;

use App\Models\Order;
use App\Models\Customer;
use App\Models\Product;
use App\Mail\OrderConfirmation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class OrderService
{
    public function createOrder(int $customerId, array $items): Order
    {
        // Check stock availability
        $this->validateStock($items);

        // Calculate totals
        $customer = Customer::findOrFail($customerId);
        $subtotal = $this->calculateSubtotal($items);
        $discount = $this->calculateDiscount($customer, $subtotal);
        $tax = $this->calculateTax($subtotal - $discount);
        $total = $subtotal - $discount + $tax;

        // Create order with transaction
        DB::beginTransaction();
        try {
            $order = $this->persistOrder($customerId, $subtotal, $discount, $tax, $total);
            $this->persistOrderItems($order, $items);
            $this->reduceStock($items);

            DB::commit();

            // Post-order actions
            $this->sendOrderConfirmation($order, $customer);
            $this->logOrderCreation($order);

            return $order;
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Order creation failed: ' . $e->getMessage());
            throw $e;
        }
    }

    private function validateStock(array $items): void
    {
        foreach ($items as $item) {
            $product = Product::findOrFail($item['product_id']);
            if ($product->stock < $item['quantity']) {
                throw new \Exception("Insufficient stock for {$product->name}");
            }
        }
    }

    private function calculateSubtotal(array $items): float
    {
        $subtotal = 0;
        foreach ($items as $item) {
            $product = Product::find($item['product_id']);
            $subtotal += $product->price * $item['quantity'];
        }
        return $subtotal;
    }

    private function calculateDiscount(Customer $customer, float $subtotal): float
    {
        // Loyal customer discount
        if ($customer->orders()->count() >= 10) {
            return $subtotal * 0.10;
        }
        return 0;
    }

    private function calculateTax(float $amount): float
    {
        return $amount * 0.20; // 20% tax
    }

    private function persistOrder(int $customerId, float $subtotal, float $discount, float $tax, float $total): Order
    {
        return Order::create([
            'customer_id' => $customerId,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'tax' => $tax,
            'total' => $total,
            'status' => 'pending',
        ]);
    }

    private function persistOrderItems(Order $order, array $items): void
    {
        foreach ($items as $item) {
            $product = Product::find($item['product_id']);

            $order->items()->create([
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'price' => $product->price,
            ]);
        }
    }

    private function reduceStock(array $items): void
    {
        foreach ($items as $item) {
            Product::find($item['product_id'])
                ->decrement('stock', $item['quantity']);
        }
    }

    private function sendOrderConfirmation(Order $order, Customer $customer): void
    {
        Mail::to($customer->email)->send(new OrderConfirmation($order));
    }

    private function logOrderCreation(Order $order): void
    {
        Log::info('Order created', ['order_id' => $order->id]);
    }
}
```

### Step 2: Simplify Controller

```php
// app/Http/Controllers/OrderController.php
namespace App\Http\Controllers;

use App\Services\OrderService;
use App\Http\Requests\StoreOrderRequest;

class OrderController extends Controller
{
    public function __construct(
        private OrderService $orderService
    ) {}

    public function store(StoreOrderRequest $request)
    {
        try {
            $order = $this->orderService->createOrder(
                $request->validated('customer_id'),
                $request->validated('items')
            );

            return redirect()
                ->route('orders.show', $order)
                ->with('success', 'Order created successfully');
        } catch (\Exception $e) {
            return back()
                ->with('error', $e->getMessage());
        }
    }
}
```

### Step 3: Extract Validation to Form Request

```php
// app/Http/Requests/StoreOrderRequest.php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => 'required|exists:customers,id',
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'customer_id.required' => 'Please select a customer',
            'customer_id.exists' => 'Selected customer does not exist',
            'items.required' => 'Please add at least one item',
            'items.*.quantity.min' => 'Quantity must be at least 1',
        ];
    }
}
```

---

## Benefits of Clean Architecture

**Before:**
- 150+ lines in controller
- Mixed responsibilities
- Hard to test
- Difficult to reuse

**After:**
- 20 lines in controller
- Clear separation of concerns
- Each method has one responsibility
- Easy to test each part independently
- Service reusable from API, commands, jobs

### Testing Becomes Easy

```php
// tests/Unit/Services/OrderServiceTest.php
namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\OrderService;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_order_with_correct_totals()
    {
        $customer = Customer::factory()->create();
        $product = Product::factory()->create([
            'price' => 100,
            'stock' => 10
        ]);

        $service = new OrderService();

        $order = $service->createOrder($customer->id, [
            ['product_id' => $product->id, 'quantity' => 2]
        ]);

        $this->assertEquals(200, $order->subtotal);
        $this->assertEquals(40, $order->tax); // 20% of 200
        $this->assertEquals(240, $order->total);
    }

    public function test_applies_loyal_customer_discount()
    {
        $customer = Customer::factory()->create();

        // Create 10 previous orders to make customer loyal
        Order::factory()->count(10)->create([
            'customer_id' => $customer->id
        ]);

        $product = Product::factory()->create([
            'price' => 100,
            'stock' => 10
        ]);

        $service = new OrderService();

        $order = $service->createOrder($customer->id, [
            ['product_id' => $product->id, 'quantity' => 2]
        ]);

        $this->assertEquals(200, $order->subtotal);
        $this->assertEquals(20, $order->discount); // 10% of 200
        $this->assertEquals(36, $order->tax); // 20% of (200-20)
        $this->assertEquals(216, $order->total);
    }

    public function test_throws_exception_when_insufficient_stock()
    {
        $customer = Customer::factory()->create();
        $product = Product::factory()->create([
            'stock' => 5
        ]);

        $service = new OrderService();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Insufficient stock');

        $service->createOrder($customer->id, [
            ['product_id' => $product->id, 'quantity' => 10]
        ]);
    }
}
```

---

## Key Principles of Clean Architecture

### 1. Dependency Rule

**Dependencies should point inward** - outer layers depend on inner layers, never the reverse.

```
Controllers → Services → Models
(outer)                  (inner)
```

Inner layers should never know about outer layers:
- Services should NOT know about HTTP requests/responses
- Models should NOT know about views or controllers
- Domain logic should NOT depend on database implementation

### 2. Separation of Concerns

Each class/method should have **one reason to change**.

```php
// Bad: Multiple reasons to change
class OrderProcessor
{
    public function process($data)
    {
        // Database logic (changes if DB structure changes)
        // Business logic (changes if rules change)
        // Email logic (changes if email provider changes)
        // Validation logic (changes if rules change)
    }
}

// Good: Single responsibility
class OrderService
{
    public function createOrder(CreateOrderDTO $dto): Order
    {
        // Only orchestrates - delegates to specialists
    }
}

class OrderValidator
{
    public function validate(array $data): void
    {
        // Only validates
    }
}

class OrderRepository
{
    public function save(Order $order): void
    {
        // Only persists
    }
}

class OrderNotifier
{
    public function sendConfirmation(Order $order): void
    {
        // Only notifies
    }
}
```

### 3. Dependency Injection

Don't create dependencies inside classes - inject them.

```php
// Bad: Hard-coded dependencies
class OrderService
{
    public function createOrder($data)
    {
        $repository = new OrderRepository(); // Tight coupling
        $notifier = new EmailNotifier(); // Can't swap implementations
        // ...
    }
}

// Good: Injected dependencies
class OrderService
{
    public function __construct(
        private OrderRepository $repository,
        private NotifierInterface $notifier // Can inject different implementations
    ) {}

    public function createOrder($data)
    {
        // Use injected dependencies
    }
}
```

### 4. Interface Segregation

Depend on abstractions (interfaces), not concrete implementations.

```php
// Define interface
interface NotifierInterface
{
    public function sendOrderConfirmation(Order $order): void;
}

// Multiple implementations
class EmailNotifier implements NotifierInterface
{
    public function sendOrderConfirmation(Order $order): void
    {
        Mail::to($order->customer->email)->send(new OrderConfirmation($order));
    }
}

class SmsNotifier implements NotifierInterface
{
    public function sendOrderConfirmation(Order $order): void
    {
        SMS::send($order->customer->phone, "Order #{$order->id} confirmed!");
    }
}

// Service depends on interface
class OrderService
{
    public function __construct(
        private NotifierInterface $notifier
    ) {}
}

// Bind in service provider
$this->app->bind(NotifierInterface::class, EmailNotifier::class);
```

---

## Laravel-Specific Architecture

Laravel provides excellent support for clean architecture:

### Directory Structure

```
app/
├── Http/
│   ├── Controllers/     # Presentation layer
│   ├── Requests/        # Validation
│   └── Resources/       # Response transformation
├── Services/            # Application layer (create this)
├── Repositories/        # Infrastructure layer (create this)
├── Models/              # Domain layer
├── Events/              # Domain events
├── Listeners/           # Event handlers
├── Jobs/                # Async tasks
├── Mail/                # Email templates
└── Providers/           # Service bindings
```

### Service Providers for Dependency Injection

```php
// app/Providers/AppServiceProvider.php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\OrderRepository;
use App\Repositories\EloquentOrderRepository;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind interfaces to implementations
        $this->app->bind(
            OrderRepositoryInterface::class,
            EloquentOrderRepository::class
        );
    }
}
```

---

## Common Pitfalls to Avoid

### 1. Over-Engineering

Don't create layers for simple operations.

```php
// Overkill for simple CRUD
class UserController
{
    public function index()
    {
        return view('users.index', [
            'users' => User::paginate(20)
        ]);
    }
}

// This is fine! No need for service layer here.
```

**Rule of thumb**: Add layers when logic becomes complex, not before.

### 2. Anemic Services

Services should orchestrate, not just wrap model calls.

```php
// Bad: Just wrapping Eloquent
class UserService
{
    public function create(array $data)
    {
        return User::create($data); // Why have service?
    }
}

// Good: Actual business logic
class UserService
{
    public function registerNewUser(array $data)
    {
        $user = User::create($data);
        $this->assignDefaultRole($user);
        $this->sendWelcomeEmail($user);
        $this->notifyAdmins($user);
        return $user;
    }
}
```

### 3. Fat Services

Don't let services become the new fat controllers.

If a service has 20+ methods, it's doing too much. Split it:

```php
// Too fat
class OrderService
{
    public function createOrder() {}
    public function updateOrder() {}
    public function cancelOrder() {}
    public function refundOrder() {}
    public function shipOrder() {}
    public function trackOrder() {}
    public function calculateShipping() {}
    public function applyPromoCode() {}
    // ... 15 more methods
}

// Better: Split by concern
class OrderCreationService {}
class OrderCancellationService {}
class OrderShippingService {}
class OrderPricingService {}
```

---

## Quick Quiz

Test your understanding:

1. **What is the main benefit of layered architecture?**
   - A) Makes code run faster
   - B) Separation of concerns and testability
   - C) Reduces lines of code
   - D) Makes it work with any framework

2. **Which layer should contain business rules like "loyal customers get 10% discount"?**
   - A) Controller
   - B) Model
   - C) Service/Application layer
   - D) Repository

3. **What's wrong with this code?**
   ```php
   class OrderService
   {
       public function createOrder(Request $request)
       {
           $validated = $request->validate([...]);
           // ...
       }
   }
   ```
   - A) Nothing wrong
   - B) Service depends on HTTP Request (presentation layer concern)
   - C) Should use dependency injection
   - D) Missing return type

**Answers**: 1-B, 2-C, 3-B

---

## Summary

**Clean architecture** is about organizing code into layers with clear responsibilities:

- **Presentation Layer** (Controllers): Handle HTTP, call services
- **Application Layer** (Services): Orchestrate business logic
- **Domain Layer** (Models): Core business entities and rules
- **Infrastructure Layer** (Repositories): Database and external services

**Key principles:**
1. Dependency Rule - inner layers don't know about outer layers
2. Separation of Concerns - one class, one responsibility
3. Dependency Injection - inject dependencies, don't create them
4. Interface Segregation - depend on abstractions

**Benefits:**
- Testable code
- Maintainable codebase
- Flexible architecture
- Reusable components

**Remember**: Don't over-engineer simple operations. Add architecture as complexity grows.

---

## Practice Exercise

Before moving to the next lesson, try this:

**Identify the architectural problems** in this controller:

```php
class ProductController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->all();

        if (strlen($data['name']) < 3) {
            return back()->with('error', 'Name too short');
        }

        $product = new Product();
        $product->name = $data['name'];
        $product->price = $data['price'];
        $product->save();

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products');
            $product->image = $path;
            $product->save();
        }

        Mail::to('admin@example.com')->send(new ProductCreated($product));

        Cache::forget('products');

        return redirect('/products');
    }
}
```

**Questions to think about:**
1. What are all the different responsibilities this controller has?
2. How would you test this code?
3. What if you need to create products from an API or command line?
4. How would you refactor this using clean architecture?

We'll explore the answers as we implement Service and Repository patterns in the next lessons!
