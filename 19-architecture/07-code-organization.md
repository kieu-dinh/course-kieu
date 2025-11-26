# Lesson 07 - Code Organization

**Duration**: 3-4 hours

---

## Introduction

As your Laravel application grows, proper code organization becomes critical. A well-organized codebase is like a well-organized library - you can find what you need quickly, and everything has its logical place.

In this lesson, we'll explore how to structure your Laravel application beyond the default directory layout, keeping it maintainable as it scales from 10 to 10,000 files.

---

## Laravel's Default Structure

```
app/
├── Console/
├── Exceptions/
├── Http/
│   ├── Controllers/
│   ├── Middleware/
│   └── Requests/
├── Models/
└── Providers/
```

This works great for small applications, but needs extension for larger projects.

---

## Extended Directory Structure

Here's a well-organized structure for medium to large applications:

```
app/
├── Console/
│   ├── Commands/
│   └── Kernel.php
├── Contracts/                    # Interfaces
│   ├── Repositories/
│   │   ├── UserRepositoryInterface.php
│   │   └── OrderRepositoryInterface.php
│   ├── Services/
│   │   └── PaymentGatewayInterface.php
│   └── Notifications/
│       └── NotificationInterface.php
├── DataTransferObjects/          # DTOs
│   ├── CreateOrderDTO.php
│   ├── UpdateUserDTO.php
│   └── AddressDTO.php
├── Enums/                        # PHP 8.1+ Enums
│   ├── OrderStatus.php
│   ├── PaymentMethod.php
│   └── UserRole.php
├── Events/
│   ├── Order/
│   │   ├── OrderCreated.php
│   │   ├── OrderCancelled.php
│   │   └── OrderShipped.php
│   └── User/
│       ├── UserRegistered.php
│       └── UserDeleted.php
├── Exceptions/
│   ├── Order/
│   │   ├── InsufficientStockException.php
│   │   └── InvalidOrderStateException.php
│   └── Payment/
│       └── PaymentFailedException.php
├── Http/
│   ├── Controllers/
│   │   ├── Admin/
│   │   │   ├── DashboardController.php
│   │   │   ├── UserController.php
│   │   │   └── OrderController.php
│   │   ├── Api/
│   │   │   └── V1/
│   │   │       ├── OrderController.php
│   │   │       └── ProductController.php
│   │   └── Web/
│   │       ├── HomeController.php
│   │       ├── OrderController.php
│   │       └── ProfileController.php
│   ├── Middleware/
│   ├── Requests/
│   │   ├── Order/
│   │   │   ├── CreateOrderRequest.php
│   │   │   └── UpdateOrderRequest.php
│   │   └── User/
│   │       ├── RegisterRequest.php
│   │       └── UpdateProfileRequest.php
│   └── Resources/
│       ├── OrderResource.php
│       └── UserResource.php
├── Jobs/
│   ├── Order/
│   │   ├── ProcessOrderPayment.php
│   │   └── SendOrderConfirmation.php
│   └── Reports/
│       └── GenerateMonthlyReport.php
├── Listeners/
│   ├── Order/
│   │   ├── SendOrderConfirmationEmail.php
│   │   ├── UpdateInventory.php
│   │   └── CreateInvoice.php
│   └── User/
│       └── SendWelcomeEmail.php
├── Mail/
│   ├── Orders/
│   │   └── OrderConfirmation.php
│   └── Users/
│       └── WelcomeEmail.php
├── Models/
│   ├── Order.php
│   ├── OrderItem.php
│   ├── Product.php
│   └── User.php
├── Notifications/
│   └── OrderShipped.php
├── Policies/
│   ├── OrderPolicy.php
│   └── UserPolicy.php
├── Providers/
│   ├── AppServiceProvider.php
│   ├── EventServiceProvider.php
│   ├── RouteServiceProvider.php
│   └── RepositoryServiceProvider.php
├── Repositories/
│   ├── EloquentOrderRepository.php
│   ├── EloquentProductRepository.php
│   └── EloquentUserRepository.php
├── Rules/                        # Custom validation rules
│   ├── ValidCouponCode.php
│   └── PhoneNumber.php
├── Services/
│   ├── Order/
│   │   ├── OrderCreationService.php
│   │   ├── OrderCancellationService.php
│   │   └── OrderFulfillmentService.php
│   ├── Payment/
│   │   ├── PaymentService.php
│   │   ├── Gateways/
│   │   │   ├── StripeGateway.php
│   │   │   └── PayPalGateway.php
│   │   └── PaymentFactory.php
│   ├── Reports/
│   │   ├── ReportGenerator.php
│   │   └── Generators/
│   │       ├── PdfGenerator.php
│   │       └── ExcelGenerator.php
│   └── Notification/
│       ├── EmailNotificationService.php
│       └── SmsNotificationService.php
└── Support/                      # Helper classes
    ├── Helpers.php
    ├── Macros.php
    └── Traits/
        ├── HasUuid.php
        └── Sluggable.php
```

---

## Organizing by Feature (Domain-Driven)

For very large applications, organize by feature/domain instead of type:

```
app/
├── Domain/
│   ├── Orders/
│   │   ├── Actions/              # Single-purpose classes
│   │   │   ├── CreateOrder.php
│   │   │   ├── CancelOrder.php
│   │   │   └── FulfillOrder.php
│   │   ├── DataTransferObjects/
│   │   │   └── OrderData.php
│   │   ├── Events/
│   │   │   ├── OrderCreated.php
│   │   │   └── OrderShipped.php
│   │   ├── Exceptions/
│   │   │   └── InsufficientStockException.php
│   │   ├── Models/
│   │   │   ├── Order.php
│   │   │   └── OrderItem.php
│   │   ├── Policies/
│   │   │   └── OrderPolicy.php
│   │   ├── Queries/              # Query builders
│   │   │   └── OrderQuery.php
│   │   └── States/               # State machines
│   │       ├── PendingState.php
│   │       ├── PaidState.php
│   │       └── ShippedState.php
│   ├── Products/
│   │   ├── Actions/
│   │   ├── Models/
│   │   └── ...
│   └── Users/
│       ├── Actions/
│       ├── Models/
│       └── ...
└── App/                          # Application layer
    ├── Admin/
    │   └── Controllers/
    ├── Api/
    │   └── Controllers/
    └── Web/
        └── Controllers/
```

**When to use:**
- Large applications (50+ models)
- Multiple developers/teams
- Clear business domains
- Need strong boundaries

**Benefits:**
- Related code stays together
- Easier to find files
- Can extract domains to packages
- Team ownership clear

---

## File Naming Conventions

### Controllers

```
# Feature-based
OrderController.php          # Web controller
Api/OrderController.php      # API controller
Admin/OrderController.php    # Admin controller

# Action-based (single action per controller)
Orders/StoreController.php
Orders/UpdateController.php
Orders/DeleteController.php
```

### Services

```
# Entity-based
OrderService.php

# Action-based (recommended)
OrderCreationService.php
OrderCancellationService.php
OrderFulfillmentService.php
```

### Repositories

```
EloquentOrderRepository.php       # Implementation
OrderRepositoryInterface.php      # Interface (in Contracts/)
```

### Events & Listeners

```
# Events - past tense
OrderCreated.php
UserRegistered.php
PaymentProcessed.php

# Listeners - present tense action
SendOrderConfirmationEmail.php
UpdateInventoryStock.php
NotifyAdministrators.php
```

### Jobs

```
ProcessOrderPayment.php
SendWelcomeEmail.php
GenerateMonthlyReport.php
```

---

## Namespace Organization

### Default Laravel Namespaces

```php
namespace App\Http\Controllers;
namespace App\Models;
namespace App\Services;
```

### Extended Namespaces

```php
namespace App\Contracts\Repositories;
namespace App\DataTransferObjects;
namespace App\Services\Order;
namespace App\Services\Payment\Gateways;
namespace App\Exceptions\Order;
```

### Domain-Based Namespaces

```php
namespace App\Domain\Orders\Models;
namespace App\Domain\Orders\Actions;
namespace App\Domain\Products\Models;
namespace App\App\Admin\Controllers;
```

---

## Organizing Controllers

### Fat Controller (Bad)

```php
// One controller handling everything
class OrderController extends Controller
{
    public function index() {}        // List orders
    public function create() {}       // Show form
    public function store() {}        // Create order
    public function show() {}         // View order
    public function edit() {}         // Show edit form
    public function update() {}       // Update order
    public function destroy() {}      // Delete order
    public function cancel() {}       // Cancel order
    public function ship() {}         // Ship order
    public function invoice() {}      // Generate invoice
    public function track() {}        // Track shipment
    // ... 20 more methods
}
```

### Split by Concern (Better)

```php
// app/Http/Controllers/Order/OrderController.php
class OrderController extends Controller
{
    public function index() {}     // List
    public function show() {}      // View
}

// app/Http/Controllers/Order/CreateOrderController.php
class CreateOrderController extends Controller
{
    public function create() {}    // Show form
    public function store() {}     // Process
}

// app/Http/Controllers/Order/CancelOrderController.php
class CancelOrderController extends Controller
{
    public function __invoke(Order $order)
    {
        // Single action controller
    }
}

// app/Http/Controllers/Order/ShipOrderController.php
class ShipOrderController extends Controller
{
    public function __invoke(Order $order)
    {
        // Single action
    }
}
```

### Routes for Split Controllers

```php
// routes/web.php
use App\Http\Controllers\Order\OrderController;
use App\Http\Controllers\Order\CreateOrderController;
use App\Http\Controllers\Order\CancelOrderController;
use App\Http\Controllers\Order\ShipOrderController;

Route::prefix('orders')->group(function () {
    Route::get('/', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/{order}', [OrderController::class, 'show'])->name('orders.show');

    Route::get('/create', [CreateOrderController::class, 'create'])->name('orders.create');
    Route::post('/', [CreateOrderController::class, 'store'])->name('orders.store');

    Route::post('/{order}/cancel', CancelOrderController::class)->name('orders.cancel');
    Route::post('/{order}/ship', ShipOrderController::class)->name('orders.ship');
});
```

---

## Organizing Services

### Single Service Class (Small App)

```php
// app/Services/OrderService.php
namespace App\Services;

class OrderService
{
    public function createOrder(array $data): Order {}
    public function updateOrder(Order $order, array $data): Order {}
    public function cancelOrder(Order $order): void {}
}
```

### Split by Action (Medium App)

```php
// app/Services/Order/OrderCreationService.php
namespace App\Services\Order;

class OrderCreationService
{
    public function create(array $data): Order {}
}

// app/Services/Order/OrderCancellationService.php
namespace App\Services\Order;

class OrderCancellationService
{
    public function cancel(Order $order): void {}
}

// app/Services/Order/OrderUpdateService.php
namespace App\Services\Order;

class OrderUpdateService
{
    public function update(Order $order, array $data): Order {}
}
```

### Action Classes (Large App)

```php
// app/Actions/Order/CreateOrder.php
namespace App\Actions\Order;

class CreateOrder
{
    public function __construct(
        private OrderRepository $repository,
        private PaymentService $paymentService
    ) {}

    public function execute(OrderData $data): Order
    {
        // Single responsibility: Create order
    }
}

// app/Actions/Order/CancelOrder.php
namespace App\Actions\Order;

class CancelOrder
{
    public function execute(Order $order, string $reason): void
    {
        // Single responsibility: Cancel order
    }
}
```

**Usage:**

```php
class OrderController extends Controller
{
    public function store(
        CreateOrderRequest $request,
        CreateOrder $action
    ) {
        $order = $action->execute(
            OrderData::fromRequest($request)
        );

        return redirect()->route('orders.show', $order);
    }
}
```

---

## Organizing Models

### Keep Models Lean

```php
// Bad: Fat model with everything
class Order extends Model
{
    // Relationships
    public function items() {}
    public function customer() {}

    // Business logic (should be in service)
    public function processPayment() {}
    public function calculateTotal() {}
    public function sendConfirmationEmail() {}

    // Queries (should be in repository or scope)
    public static function getPendingOrders() {}
    public static function getOrdersForCustomer($customerId) {}

    // ... 50 more methods
}
```

```php
// Good: Lean model
class Order extends Model
{
    protected $fillable = ['customer_id', 'total', 'status'];

    // Relationships only
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    // Query scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    // Simple accessors/mutators
    public function getTotalFormattedAttribute(): string
    {
        return '$' . number_format($this->total, 2);
    }
}
```

### Use Traits for Reusable Behavior

```php
// app/Support/Traits/HasUuid.php
namespace App\Support\Traits;

use Illuminate\Support\Str;

trait HasUuid
{
    protected static function bootHasUuid()
    {
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }
}

// app/Support/Traits/Sluggable.php
namespace App\Support\Traits;

use Illuminate\Support\Str;

trait Sluggable
{
    protected static function bootSluggable()
    {
        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->{$model->sluggableField()});
            }
        });
    }

    public function sluggableField(): string
    {
        return 'title';
    }
}
```

```php
// Usage in models
class Post extends Model
{
    use HasUuid, Sluggable;

    public function sluggableField(): string
    {
        return 'title';
    }
}

class Product extends Model
{
    use HasUuid, Sluggable;

    public function sluggableField(): string
    {
        return 'name';
    }
}
```

---

## Organizing Routes

### Split Route Files

```php
// routes/web.php - Public web routes
Route::get('/', HomeController::class);
Route::get('/about', AboutController::class);

// routes/admin.php - Admin routes
Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index']);
    Route::resource('users', AdminUserController::class);
    Route::resource('orders', AdminOrderController::class);
});

// routes/api.php - API routes
Route::prefix('v1')->group(function () {
    Route::apiResource('orders', Api\OrderController::class);
    Route::apiResource('products', Api\ProductController::class);
});
```

```php
// app/Providers/RouteServiceProvider.php
public function boot(): void
{
    $this->routes(function () {
        Route::middleware('api')
            ->prefix('api')
            ->group(base_path('routes/api.php'));

        Route::middleware('web')
            ->group(base_path('routes/web.php'));

        Route::middleware(['web', 'auth', 'admin'])
            ->prefix('admin')
            ->group(base_path('routes/admin.php'));
    });
}
```

### Group Related Routes

```php
// routes/web.php
Route::prefix('orders')->name('orders.')->group(function () {
    Route::get('/', [OrderController::class, 'index'])->name('index');
    Route::get('/create', [OrderController::class, 'create'])->name('create');
    Route::post('/', [OrderController::class, 'store'])->name('store');
    Route::get('/{order}', [OrderController::class, 'show'])->name('show');
});

Route::prefix('products')->name('products.')->group(function () {
    Route::get('/', [ProductController::class, 'index'])->name('index');
    Route::get('/{product}', [ProductController::class, 'show'])->name('show');
});
```

---

## Configuration Organization

### Environment-Specific Config

```php
// config/services.php
return [
    'stripe' => [
        'key' => env('STRIPE_PUBLIC_KEY'),
        'secret' => env('STRIPE_SECRET_KEY'),
    ],

    'paypal' => [
        'client_id' => env('PAYPAL_CLIENT_ID'),
        'client_secret' => env('PAYPAL_CLIENT_SECRET'),
        'mode' => env('PAYPAL_MODE', 'sandbox'),
    ],
];

// config/app.php - Custom settings
return [
    // ... default Laravel config

    'pagination' => [
        'per_page' => 15,
        'max_per_page' => 100,
    ],

    'uploads' => [
        'max_size' => 10 * 1024 * 1024, // 10MB
        'allowed_extensions' => ['jpg', 'png', 'pdf'],
    ],
];
```

---

## Testing Organization

```
tests/
├── Feature/
│   ├── Admin/
│   │   ├── OrderManagementTest.php
│   │   └── UserManagementTest.php
│   ├── Api/
│   │   ├── OrderApiTest.php
│   │   └── ProductApiTest.php
│   └── Web/
│       ├── OrderTest.php
│       └── CheckoutTest.php
├── Unit/
│   ├── Services/
│   │   ├── OrderCreationServiceTest.php
│   │   └── PaymentServiceTest.php
│   ├── Repositories/
│   │   └── OrderRepositoryTest.php
│   └── Models/
│       └── OrderTest.php
└── TestCase.php
```

---

## Documentation Organization

```
docs/
├── architecture/
│   ├── overview.md
│   ├── services.md
│   └── repositories.md
├── api/
│   ├── authentication.md
│   ├── orders.md
│   └── products.md
├── deployment/
│   ├── server-setup.md
│   └── deployment-process.md
└── README.md
```

---

## Quick Quiz

1. **When should you organize code by domain instead of type?**
   - A) Small applications (< 10 models)
   - B) Large applications with clear business domains
   - C) Never
   - D) Always

2. **Where should business logic go?**
   - A) Controllers
   - B) Models
   - C) Services/Actions
   - D) Views

3. **What's the benefit of single-action controllers?**
   - A) Fewer files
   - B) Easier to test and maintain
   - C) Faster performance
   - D) Required by Laravel

**Answers**: 1-B, 2-C, 3-B

---

## Summary

**Code Organization Principles:**

1. **Separate by concern** - Controllers, Services, Repositories
2. **Group related code** - By domain or feature when appropriate
3. **Keep files focused** - Single responsibility
4. **Use meaningful names** - Clear, descriptive
5. **Follow conventions** - Consistent naming and structure

**Directory Structure:**
- Small apps: Default Laravel structure + Services/Repositories
- Medium apps: Add Contracts, DTOs, split by entity
- Large apps: Consider domain-driven organization

**Key Takeaways:**
- Start simple, add structure as needed
- Consistency is more important than perfect structure
- Organize for your team's size and needs
- Keep related code together
- Make it easy to find things

In the final lesson, we'll explore **Refactoring Techniques** to improve existing codebases!
