# Lesson 08 - Refactoring Techniques

**Duration**: 4-5 hours

---

## Introduction

**Refactoring** is the process of restructuring existing code without changing its external behavior. It's like renovating a house - you improve the structure, but it still functions as a home.

Think of refactoring as:
- Cleaning up messy code
- Making code easier to understand
- Preparing code for new features
- Reducing technical debt

In this lesson, we'll explore practical refactoring techniques to transform messy Laravel code into clean, maintainable architecture.

---

## When to Refactor

### Good Times to Refactor

1. **Before adding new features** - Clean foundation for new code
2. **After code review** - Apply feedback
3. **When you see duplication** - DRY (Don't Repeat Yourself)
4. **When tests are hard to write** - Sign of poor design
5. **When you struggle to understand code** - Even your own after 3 months

### Bad Times to Refactor

1. **When deadlines are tight** - Risk breaking things
2. **Without tests** - How do you know it still works?
3. **Just because** - Have a clear goal
4. **Everything at once** - Refactor incrementally

---

## The Refactoring Process

**Golden Rule**: Refactor in small steps with tests.

```
1. Write/ensure tests pass
2. Make small refactoring change
3. Run tests
4. Repeat
```

Never change behavior and structure simultaneously.

---

## Technique 1: Extract Method

**Problem**: Long methods that do multiple things.

### Before

```php
class OrderController extends Controller
{
    public function store(Request $request)
    {
        // Validation
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        // Check stock
        foreach ($request->items as $item) {
            $product = Product::find($item['product_id']);
            if ($product->stock < $item['quantity']) {
                return back()->with('error', "Insufficient stock for {$product->name}");
            }
        }

        // Calculate total
        $subtotal = 0;
        foreach ($request->items as $item) {
            $product = Product::find($item['product_id']);
            $subtotal += $product->price * $item['quantity'];
        }

        $tax = $subtotal * 0.20;
        $total = $subtotal + $tax;

        // Create order
        DB::beginTransaction();
        try {
            $order = Order::create([
                'customer_id' => $request->customer_id,
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total' => $total,
            ]);

            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);
                $order->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                ]);
                $product->decrement('stock', $item['quantity']);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Order creation failed');
        }

        return redirect()->route('orders.show', $order);
    }
}
```

### After: Extract to Methods

```php
class OrderController extends Controller
{
    public function store(Request $request)
    {
        $validated = $this->validateOrder($request);

        if (!$this->hasAvailableStock($validated['items'])) {
            return back()->with('error', 'Insufficient stock');
        }

        $order = $this->createOrder($validated);

        return redirect()->route('orders.show', $order);
    }

    private function validateOrder(Request $request): array
    {
        return $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);
    }

    private function hasAvailableStock(array $items): bool
    {
        foreach ($items as $item) {
            $product = Product::find($item['product_id']);
            if ($product->stock < $item['quantity']) {
                return false;
            }
        }
        return true;
    }

    private function createOrder(array $data): Order
    {
        $subtotal = $this->calculateSubtotal($data['items']);
        $tax = $subtotal * 0.20;
        $total = $subtotal + $tax;

        DB::beginTransaction();
        try {
            $order = Order::create([
                'customer_id' => $data['customer_id'],
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total' => $total,
            ]);

            $this->createOrderItems($order, $data['items']);

            DB::commit();
            return $order;
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
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

    private function createOrderItems(Order $order, array $items): void
    {
        foreach ($items as $item) {
            $product = Product::find($item['product_id']);
            $order->items()->create([
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'price' => $product->price,
            ]);
            $product->decrement('stock', $item['quantity']);
        }
    }
}
```

**Better**: Now each method has a clear, single purpose.

---

## Technique 2: Extract Class (Service)

**Problem**: Controller still has too much logic.

### After: Extract to Service

```php
// app/Services/OrderService.php
namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function createOrder(array $data): Order
    {
        $this->validateStock($data['items']);

        $subtotal = $this->calculateSubtotal($data['items']);
        $tax = $subtotal * 0.20;
        $total = $subtotal + $tax;

        DB::beginTransaction();
        try {
            $order = Order::create([
                'customer_id' => $data['customer_id'],
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total' => $total,
            ]);

            $this->createOrderItems($order, $data['items']);

            DB::commit();
            return $order;
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    private function validateStock(array $items): void
    {
        foreach ($items as $item) {
            $product = Product::find($item['product_id']);
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

    private function createOrderItems(Order $order, array $items): void
    {
        foreach ($items as $item) {
            $product = Product::find($item['product_id']);
            $order->items()->create([
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'price' => $product->price,
            ]);
            $product->decrement('stock', $item['quantity']);
        }
    }
}
```

```php
// app/Http/Controllers/OrderController.php
namespace App\Http\Controllers;

use App\Services\OrderService;
use App\Http\Requests\CreateOrderRequest;

class OrderController extends Controller
{
    public function __construct(
        private OrderService $orderService
    ) {}

    public function store(CreateOrderRequest $request)
    {
        try {
            $order = $this->orderService->createOrder($request->validated());

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

**Result**: Controller is now 15 lines, focused only on HTTP concerns.

---

## Technique 3: Replace Conditional with Polymorphism

**Problem**: Long if/else or switch statements.

### Before

```php
class PaymentService
{
    public function processPayment(Order $order, string $method): bool
    {
        if ($method === 'stripe') {
            $stripe = new \Stripe\StripeClient(config('stripe.secret'));
            $charge = $stripe->charges->create([
                'amount' => $order->total * 100,
                'currency' => 'usd',
            ]);
            return $charge->status === 'succeeded';
        }

        if ($method === 'paypal') {
            $paypal = new PayPalClient();
            return $paypal->createPayment($order);
        }

        if ($method === 'bank_transfer') {
            // Generate bank transfer instructions
            return $this->generateBankTransferInstructions($order);
        }

        throw new \Exception('Unknown payment method');
    }
}
```

### After: Use Strategy Pattern

```php
// app/Contracts/PaymentGatewayInterface.php
interface PaymentGatewayInterface
{
    public function charge(Order $order): bool;
}

// app/Services/Payment/StripeGateway.php
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
}

// app/Services/Payment/PayPalGateway.php
class PayPalGateway implements PaymentGatewayInterface
{
    public function charge(Order $order): bool
    {
        $paypal = new PayPalClient();
        return $paypal->createPayment($order);
    }
}

// app/Services/Payment/BankTransferGateway.php
class BankTransferGateway implements PaymentGatewayInterface
{
    public function charge(Order $order): bool
    {
        // Generate instructions
        return true;
    }
}

// app/Services/PaymentService.php
class PaymentService
{
    public function __construct(
        private PaymentGatewayInterface $gateway
    ) {}

    public function processPayment(Order $order): bool
    {
        return $this->gateway->charge($order);
    }
}
```

**Benefits:**
- No conditional logic
- Easy to add new payment methods
- Each gateway testable independently

---

## Technique 4: Replace Magic Numbers with Constants

**Problem**: Numbers without context.

### Before

```php
class OrderService
{
    public function calculateTotal(float $subtotal, bool $isPremium): float
    {
        $tax = $subtotal * 0.20;  // What is 0.20?
        $shipping = 15;           // What is 15?

        if ($isPremium) {
            $discount = $subtotal * 0.10;  // What is 0.10?
        } else {
            $discount = 0;
        }

        if ($subtotal > 100) {  // Why 100?
            $shipping = 0;
        }

        return $subtotal + $tax + $shipping - $discount;
    }
}
```

### After: Named Constants

```php
class OrderService
{
    private const TAX_RATE = 0.20;              // 20% tax
    private const STANDARD_SHIPPING_COST = 15;  // $15 shipping
    private const PREMIUM_DISCOUNT_RATE = 0.10; // 10% premium discount
    private const FREE_SHIPPING_THRESHOLD = 100; // Free shipping over $100

    public function calculateTotal(float $subtotal, bool $isPremium): float
    {
        $tax = $subtotal * self::TAX_RATE;
        $shipping = self::STANDARD_SHIPPING_COST;

        if ($isPremium) {
            $discount = $subtotal * self::PREMIUM_DISCOUNT_RATE;
        } else {
            $discount = 0;
        }

        if ($subtotal > self::FREE_SHIPPING_THRESHOLD) {
            $shipping = 0;
        }

        return $subtotal + $tax + $shipping - $discount;
    }
}
```

**Better**: Use Enums for configuration (PHP 8.1+)

```php
// app/Enums/OrderConfiguration.php
enum OrderConfiguration: float
{
    case TAX_RATE = 0.20;
    case STANDARD_SHIPPING = 15.00;
    case PREMIUM_DISCOUNT = 0.10;
    case FREE_SHIPPING_THRESHOLD = 100.00;
}

// Usage
$tax = $subtotal * OrderConfiguration::TAX_RATE->value;
```

---

## Technique 5: Replace Nested Conditionals with Guard Clauses

**Problem**: Deep nesting makes code hard to follow.

### Before

```php
public function processOrder(Order $order): bool
{
    if ($order->status === 'pending') {
        if ($order->customer->isActive()) {
            if ($order->items->count() > 0) {
                if ($order->total > 0) {
                    // Actual processing logic buried here
                    $this->chargePayment($order);
                    $order->update(['status' => 'processing']);
                    return true;
                } else {
                    throw new \Exception('Order total must be greater than 0');
                }
            } else {
                throw new \Exception('Order must have items');
            }
        } else {
            throw new \Exception('Customer is not active');
        }
    } else {
        throw new \Exception('Order is not pending');
    }
}
```

### After: Guard Clauses

```php
public function processOrder(Order $order): bool
{
    // Guard clauses - fail fast
    if ($order->status !== 'pending') {
        throw new \Exception('Order is not pending');
    }

    if (!$order->customer->isActive()) {
        throw new \Exception('Customer is not active');
    }

    if ($order->items->count() === 0) {
        throw new \Exception('Order must have items');
    }

    if ($order->total <= 0) {
        throw new \Exception('Order total must be greater than 0');
    }

    // Happy path - clear and straightforward
    $this->chargePayment($order);
    $order->update(['status' => 'processing']);

    return true;
}
```

**Benefits:**
- Linear flow - easy to read
- Error cases handled first
- Happy path clear at the end

---

## Technique 6: Extract to Repository

**Problem**: Database queries scattered throughout services.

### Before

```php
class OrderService
{
    public function getPendingOrders(): Collection
    {
        return Order::where('status', 'pending')
            ->with(['customer', 'items'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getCustomerOrders(int $customerId): Collection
    {
        return Order::where('customer_id', $customerId)
            ->with('items')
            ->orderBy('created_at', 'desc')
            ->get();
    }
}

class ReportService
{
    public function getPendingOrdersCount(): int
    {
        return Order::where('status', 'pending')->count(); // Duplicated query
    }
}
```

### After: Repository Pattern

```php
// app/Repositories/OrderRepository.php
class OrderRepository
{
    public function findPending(): Collection
    {
        return Order::where('status', 'pending')
            ->with(['customer', 'items'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function findByCustomer(int $customerId): Collection
    {
        return Order::where('customer_id', $customerId)
            ->with('items')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function countPending(): int
    {
        return Order::where('status', 'pending')->count();
    }
}

// app/Services/OrderService.php
class OrderService
{
    public function __construct(
        private OrderRepository $repository
    ) {}

    public function getPendingOrders(): Collection
    {
        return $this->repository->findPending();
    }

    public function getCustomerOrders(int $customerId): Collection
    {
        return $this->repository->findByCustomer($customerId);
    }
}

// app/Services/ReportService.php
class ReportService
{
    public function __construct(
        private OrderRepository $repository
    ) {}

    public function getPendingOrdersCount(): int
    {
        return $this->repository->countPending();
    }
}
```

**Benefits:**
- Query logic centralized
- Reusable across services
- Easy to optimize queries in one place

---

## Technique 7: Replace Type Code with Enum

**Problem**: String status comparisons throughout code.

### Before

```php
class Order extends Model
{
    // Magic strings everywhere
    public function isPending(): bool
    {
        return $this->status === 'pending'; // Typo = bug
    }

    public function ship(): void
    {
        if ($this->status !== 'paid') {
            throw new \Exception('Cannot ship unpaid order');
        }
        $this->update(['status' => 'shipped']);
    }
}

// Usage - error-prone
if ($order->status === 'pendig') { // Typo!
    // Won't catch this until runtime
}
```

### After: PHP 8.1 Enum

```php
// app/Enums/OrderStatus.php
namespace App\Enums;

enum OrderStatus: string
{
    case PENDING = 'pending';
    case PAID = 'paid';
    case PROCESSING = 'processing';
    case SHIPPED = 'shipped';
    case DELIVERED = 'delivered';
    case CANCELLED = 'cancelled';

    public function canBeShipped(): bool
    {
        return $this === self::PAID || $this === self::PROCESSING;
    }

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Pending Payment',
            self::PAID => 'Paid',
            self::PROCESSING => 'Processing',
            self::SHIPPED => 'Shipped',
            self::DELIVERED => 'Delivered',
            self::CANCELLED => 'Cancelled',
        };
    }
}
```

```php
// app/Models/Order.php
use App\Enums\OrderStatus;

class Order extends Model
{
    protected $casts = [
        'status' => OrderStatus::class, // Auto-cast to enum
    ];

    public function isPending(): bool
    {
        return $this->status === OrderStatus::PENDING;
    }

    public function ship(): void
    {
        if (!$this->status->canBeShipped()) {
            throw new \Exception('Cannot ship this order');
        }

        $this->update(['status' => OrderStatus::SHIPPED]);
    }
}

// Usage - type-safe
if ($order->status === OrderStatus::PENDING) {
    // IDE autocomplete, no typos possible
}
```

**Benefits:**
- Type safety
- IDE autocomplete
- Centralized business logic
- No typos

---

## Technique 8: Replace Query with Eloquent Scopes

**Problem**: Complex queries repeated everywhere.

### Before

```php
// Duplicated in multiple places
$activeUsers = User::where('status', 'active')
    ->whereNotNull('email_verified_at')
    ->where('last_login_at', '>=', now()->subDays(30))
    ->get();

// Same query in another service
$users = User::where('status', 'active')
    ->whereNotNull('email_verified_at')
    ->where('last_login_at', '>=', now()->subDays(30))
    ->count();
```

### After: Query Scopes

```php
// app/Models/User.php
class User extends Model
{
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->whereNotNull('email_verified_at');
    }

    public function scopeRecentlyActive($query, int $days = 30)
    {
        return $query->where('last_login_at', '>=', now()->subDays($days));
    }
}

// Usage - clean and reusable
$activeUsers = User::active()->recentlyActive()->get();
$count = User::active()->recentlyActive()->count();
$activeLastWeek = User::active()->recentlyActive(7)->get();
```

---

## Technique 9: Extract to Events/Listeners

**Problem**: Service doing too many side effects.

### Before

```php
class OrderService
{
    public function createOrder(array $data): Order
    {
        $order = Order::create($data);

        // Side effects mixed with core logic
        Mail::to($order->customer->email)->send(new OrderConfirmation($order));
        $this->inventoryService->reduceStock($order->items);
        $this->invoiceService->generate($order);
        $this->loyaltyService->awardPoints($order);
        Notification::send($admins, new NewOrderNotification($order));

        return $order;
    }
}
```

### After: Event-Driven

```php
// app/Services/OrderService.php
class OrderService
{
    public function createOrder(array $data): Order
    {
        $order = Order::create($data);

        // Fire event - clean separation
        event(new OrderCreated($order));

        return $order;
    }
}

// app/Listeners/SendOrderConfirmationEmail.php
class SendOrderConfirmationEmail
{
    public function handle(OrderCreated $event): void
    {
        Mail::to($event->order->customer->email)
            ->send(new OrderConfirmation($event->order));
    }
}

// app/Listeners/ReduceInventoryStock.php
class ReduceInventoryStock
{
    public function __construct(
        private InventoryService $inventoryService
    ) {}

    public function handle(OrderCreated $event): void
    {
        $this->inventoryService->reduceStock($event->order->items);
    }
}

// app/Listeners/GenerateInvoice.php
class GenerateInvoice
{
    public function __construct(
        private InvoiceService $invoiceService
    ) {}

    public function handle(OrderCreated $event): void
    {
        $this->invoiceService->generate($event->order);
    }
}

// Bind in EventServiceProvider
protected $listen = [
    OrderCreated::class => [
        SendOrderConfirmationEmail::class,
        ReduceInventoryStock::class,
        GenerateInvoice::class,
        AwardLoyaltyPoints::class,
        NotifyAdmins::class,
    ],
];
```

**Benefits:**
- Service focused on core responsibility
- Easy to add/remove side effects
- Each listener independently testable
- Can queue listeners for async processing

---

## Technique 10: Introduce Data Transfer Objects (DTOs)

**Problem**: Passing arrays everywhere.

### Before

```php
// What keys does this array have? Unknown until runtime
$orderService->createOrder([
    'customer_id' => 1,
    'items' => [...],
    'shipping_address' => [...],
    'billing_address' => [...],
    'promo_code' => 'SAVE10',
]);

// Typo won't be caught
$orderService->createOrder([
    'custmer_id' => 1, // Typo!
    // ...
]);
```

### After: DTOs

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

// app/Services/OrderService.php
class OrderService
{
    public function createOrder(CreateOrderDTO $dto): Order
    {
        // Type-safe access
        $order = Order::create([
            'customer_id' => $dto->customerId, // IDE knows this exists
            'promo_code' => $dto->promoCode,
        ]);

        // ...
    }
}

// Usage
$dto = CreateOrderDTO::fromRequest($request->validated());
$order = $orderService->createOrder($dto);
```

**Benefits:**
- Type safety
- IDE autocomplete
- Clear contract
- Validation in one place

---

## Practical Refactoring Example: Complete Transformation

Let's refactor a real messy controller completely.

### Before: 150 Lines of Mess

```php
class ProductController extends Controller
{
    public function store(Request $request)
    {
        if (!$request->has('name') || strlen($request->name) < 3) {
            return back()->with('error', 'Name required');
        }

        if (!$request->has('price') || $request->price <= 0) {
            return back()->with('error', 'Valid price required');
        }

        $product = new Product();
        $product->name = $request->name;
        $product->slug = Str::slug($request->name);
        $product->description = $request->description;
        $product->price = $request->price;
        $product->stock = $request->stock ?? 0;

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
            $product->image = $path;
        }

        $product->save();

        if ($request->has('categories')) {
            foreach ($request->categories as $categoryId) {
                $product->categories()->attach($categoryId);
            }
        }

        Mail::to('admin@example.com')->send(new ProductCreated($product));

        Cache::forget('products');
        Cache::forget('product_categories');

        Log::info('Product created', ['product_id' => $product->id]);

        return redirect('/products')->with('success', 'Product created');
    }
}
```

### After: Clean Architecture

```php
// app/Http/Requests/CreateProductRequest.php
class CreateProductRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|min:3|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0.01',
            'stock' => 'nullable|integer|min:0',
            'image' => 'nullable|image|max:2048',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
        ];
    }
}

// app/DataTransferObjects/ProductData.php
class ProductData
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $description,
        public readonly float $price,
        public readonly int $stock,
        public readonly ?UploadedFile $image,
        public readonly array $categoryIds
    ) {}

    public static function fromRequest(CreateProductRequest $request): self
    {
        return new self(
            name: $request->validated('name'),
            description: $request->validated('description'),
            price: $request->validated('price'),
            stock: $request->validated('stock', 0),
            image: $request->file('image'),
            categoryIds: $request->validated('categories', [])
        );
    }
}

// app/Services/ProductService.php
class ProductService
{
    public function __construct(
        private ProductRepository $repository,
        private ImageUploadService $imageService,
        private CacheService $cacheService
    ) {}

    public function createProduct(ProductData $data): Product
    {
        $productData = [
            'name' => $data->name,
            'slug' => Str::slug($data->name),
            'description' => $data->description,
            'price' => $data->price,
            'stock' => $data->stock,
        ];

        if ($data->image) {
            $productData['image'] = $this->imageService->upload($data->image, 'products');
        }

        $product = $this->repository->create($productData);

        if (!empty($data->categoryIds)) {
            $product->categories()->sync($data->categoryIds);
        }

        event(new ProductCreated($product));

        $this->cacheService->clearProductCache();

        return $product;
    }
}

// app/Http/Controllers/ProductController.php
class ProductController extends Controller
{
    public function __construct(
        private ProductService $productService
    ) {}

    public function store(CreateProductRequest $request)
    {
        try {
            $product = $this->productService->createProduct(
                ProductData::fromRequest($request)
            );

            return redirect()
                ->route('products.show', $product)
                ->with('success', 'Product created successfully');
        } catch (\Exception $e) {
            Log::error('Product creation failed', [
                'error' => $e->getMessage()
            ]);

            return back()
                ->withInput()
                ->with('error', 'Failed to create product');
        }
    }
}

// app/Listeners/SendProductCreatedNotification.php
class SendProductCreatedNotification
{
    public function handle(ProductCreated $event): void
    {
        Mail::to('admin@example.com')
            ->send(new ProductCreatedMail($event->product));
    }
}

// app/Listeners/LogProductCreation.php
class LogProductCreation
{
    public function handle(ProductCreated $event): void
    {
        Log::info('Product created', [
            'product_id' => $event->product->id
        ]);
    }
}
```

**Result:**
- Controller: 20 lines (was 150)
- Clear separation of concerns
- Testable components
- Type-safe data flow
- Reusable services

---

## Refactoring Checklist

Before refactoring:
- [ ] Tests exist and pass
- [ ] Understand what code does
- [ ] Small, incremental changes
- [ ] Have a clear goal

During refactoring:
- [ ] One change at a time
- [ ] Run tests after each change
- [ ] Commit frequently
- [ ] Keep behavior unchanged

After refactoring:
- [ ] All tests pass
- [ ] Code is cleaner
- [ ] No new bugs introduced
- [ ] Document if needed

---

## Red Flags: When Code Needs Refactoring

1. **Long Methods** - > 20 lines
2. **Large Classes** - > 200 lines
3. **Many Parameters** - > 4 parameters
4. **Duplicated Code** - Copy-paste coding
5. **Long Parameter Lists** - Use DTOs
6. **Shotgun Surgery** - One change requires many file edits
7. **Feature Envy** - Method uses another class more than its own
8. **Data Clumps** - Same group of data passed together
9. **Primitive Obsession** - Using primitives instead of objects
10. **Magic Numbers** - Unexplained numbers in code

---

## Quick Quiz

1. **What's the first step before refactoring?**
   - A) Rewrite everything
   - B) Ensure tests exist and pass
   - C) Delete old code
   - D) Ask permission

2. **What is guard clause technique?**
   - A) Using security guards
   - B) Early returns for error cases
   - C) Protecting database
   - D) Using middleware

3. **When should you NOT refactor?**
   - A) After code review
   - B) Before adding features
   - C) When deadlines are tight
   - D) When you see duplication

**Answers**: 1-B, 2-B, 3-C

---

## Summary

**Refactoring Techniques:**

1. **Extract Method** - Break long methods into smaller ones
2. **Extract Class** - Move logic to services
3. **Replace Conditional** - Use polymorphism (Strategy pattern)
4. **Magic Numbers** - Use constants/enums
5. **Guard Clauses** - Early returns for errors
6. **Extract Repository** - Centralize queries
7. **Type Code to Enum** - Type-safe status codes
8. **Query Scopes** - Reusable Eloquent queries
9. **Events/Listeners** - Decouple side effects
10. **DTOs** - Type-safe data transfer

**Key Principles:**
- Refactor in small steps
- Always have tests
- Keep behavior unchanged
- Commit frequently
- One change at a time

**Remember**: Refactoring is not rewriting. It's improving structure while preserving functionality.

---

## Module 19 Complete!

You've now learned:
1. Clean Architecture principles
2. Service Layer pattern
3. Repository pattern
4. SOLID principles
5. Common design patterns
6. Dependency injection
7. Code organization
8. Refactoring techniques

You're ready to build maintainable, scalable Laravel applications with clean architecture!

Next: Apply these concepts in the exercises to refactor real code.
