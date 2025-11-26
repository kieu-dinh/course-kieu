# Lesson 05 - Common Design Patterns

**Duration**: 4-5 hours

---

## Introduction

**Design patterns** are proven solutions to common problems in software design. They're like recipes that experienced developers have tested and refined over time. Instead of reinventing the wheel, you can apply these patterns to solve similar problems.

Think of design patterns as architectural blueprints:
- **Factory Pattern** = Assembly line that produces different products
- **Strategy Pattern** = Different routes to reach the same destination
- **Observer Pattern** = Newsletter subscription system
- **Decorator Pattern** = Adding toppings to a pizza
- **Singleton Pattern** = One CEO managing the entire company

In this lesson, we'll explore the most practical patterns for Laravel development.

---

## 1. Factory Pattern

**Purpose**: Create objects without specifying the exact class to create.

**When to use**: When you need to create different types of objects based on input or configuration.

### Real-World Analogy

A car factory can produce different car models (sedan, SUV, truck) based on customer orders, but the customer doesn't need to know the construction details.

### Laravel Example: Report Generator

```php
// app/Contracts/ReportInterface.php
namespace App\Contracts;

interface ReportInterface
{
    public function generate(array $data): string;
}
```

```php
// app/Services/Reports/PdfReport.php
namespace App\Services\Reports;

use App\Contracts\ReportInterface;

class PdfReport implements ReportInterface
{
    public function generate(array $data): string
    {
        // Generate PDF report
        $pdf = new \TCPDF();
        $pdf->AddPage();
        $pdf->Write(0, 'Report Data: ' . json_encode($data));

        $filename = 'report_' . time() . '.pdf';
        $pdf->Output(storage_path("app/reports/{$filename}"), 'F');

        return $filename;
    }
}
```

```php
// app/Services/Reports/ExcelReport.php
namespace App\Services\Reports;

use App\Contracts\ReportInterface;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExcelReport implements ReportInterface
{
    public function generate(array $data): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $row = 1;
        foreach ($data as $key => $value) {
            $sheet->setCellValue("A{$row}", $key);
            $sheet->setCellValue("B{$row}", $value);
            $row++;
        }

        $filename = 'report_' . time() . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save(storage_path("app/reports/{$filename}"));

        return $filename;
    }
}
```

```php
// app/Services/Reports/CsvReport.php
namespace App\Services\Reports;

use App\Contracts\ReportInterface;

class CsvReport implements ReportInterface
{
    public function generate(array $data): string
    {
        $filename = 'report_' . time() . '.csv';
        $filepath = storage_path("app/reports/{$filename}");

        $file = fopen($filepath, 'w');
        foreach ($data as $key => $value) {
            fputcsv($file, [$key, $value]);
        }
        fclose($file);

        return $filename;
    }
}
```

```php
// app/Services/Reports/ReportFactory.php
namespace App\Services\Reports;

use App\Contracts\ReportInterface;

class ReportFactory
{
    public static function create(string $type): ReportInterface
    {
        return match($type) {
            'pdf' => new PdfReport(),
            'excel' => new ExcelReport(),
            'csv' => new CsvReport(),
            default => throw new \InvalidArgumentException("Unknown report type: {$type}")
        };
    }
}
```

```php
// Usage in Controller
namespace App\Http\Controllers;

use App\Services\Reports\ReportFactory;

class ReportController extends Controller
{
    public function generate(Request $request)
    {
        $type = $request->input('format', 'pdf'); // pdf, excel, or csv

        // Factory creates the appropriate report generator
        $report = ReportFactory::create($type);

        $data = [
            'Total Orders' => 1500,
            'Total Revenue' => 50000,
            'New Customers' => 120,
        ];

        $filename = $report->generate($data);

        return response()->download(storage_path("app/reports/{$filename}"));
    }
}
```

**Benefits:**
- Add new report formats without changing existing code
- Client code doesn't need to know implementation details
- Centralized object creation

### Advanced: Factory with Dependency Injection

```php
// app/Services/Reports/ReportFactory.php
namespace App\Services\Reports;

use App\Contracts\ReportInterface;
use Illuminate\Contracts\Container\Container;

class ReportFactory
{
    private array $creators = [
        'pdf' => PdfReport::class,
        'excel' => ExcelReport::class,
        'csv' => CsvReport::class,
    ];

    public function __construct(
        private Container $container
    ) {}

    public function create(string $type): ReportInterface
    {
        if (!isset($this->creators[$type])) {
            throw new \InvalidArgumentException("Unknown report type: {$type}");
        }

        // Resolve from container (supports dependency injection)
        return $this->container->make($this->creators[$type]);
    }

    public function register(string $type, string $class): void
    {
        $this->creators[$type] = $class;
    }
}
```

---

## 2. Strategy Pattern

**Purpose**: Define a family of algorithms, encapsulate each one, and make them interchangeable.

**When to use**: When you have multiple ways to perform an operation and want to switch between them.

### Real-World Analogy

Different payment methods (credit card, PayPal, bank transfer) to pay for the same order. Customer chooses the strategy.

### Laravel Example: Shipping Calculator

```php
// app/Contracts/ShippingStrategyInterface.php
namespace App\Contracts;

use App\Models\Order;

interface ShippingStrategyInterface
{
    public function calculateCost(Order $order): float;
    public function getEstimatedDays(): int;
}
```

```php
// app/Services/Shipping/StandardShipping.php
namespace App\Services\Shipping;

use App\Contracts\ShippingStrategyInterface;
use App\Models\Order;

class StandardShipping implements ShippingStrategyInterface
{
    public function calculateCost(Order $order): float
    {
        $weight = $order->items->sum('weight');
        return $weight * 0.50; // $0.50 per kg
    }

    public function getEstimatedDays(): int
    {
        return 5; // 5-7 business days
    }
}
```

```php
// app/Services/Shipping/ExpressShipping.php
namespace App\Services\Shipping;

use App\Contracts\ShippingStrategyInterface;
use App\Models\Order;

class ExpressShipping implements ShippingStrategyInterface
{
    public function calculateCost(Order $order): float
    {
        $weight = $order->items->sum('weight');
        return ($weight * 0.50) + 10; // Standard + $10 express fee
    }

    public function getEstimatedDays(): int
    {
        return 2; // 1-2 business days
    }
}
```

```php
// app/Services/Shipping/OvernightShipping.php
namespace App\Services\Shipping;

use App\Contracts\ShippingStrategyInterface;
use App\Models\Order;

class OvernightShipping implements ShippingStrategyInterface
{
    public function calculateCost(Order $order): float
    {
        $weight = $order->items->sum('weight');
        return ($weight * 0.50) + 25; // Standard + $25 overnight fee
    }

    public function getEstimatedDays(): int
    {
        return 1; // Next business day
    }
}
```

```php
// app/Services/ShippingService.php
namespace App\Services;

use App\Contracts\ShippingStrategyInterface;
use App\Models\Order;

class ShippingService
{
    private ShippingStrategyInterface $strategy;

    public function setStrategy(ShippingStrategyInterface $strategy): void
    {
        $this->strategy = $strategy;
    }

    public function calculateShipping(Order $order): array
    {
        return [
            'cost' => $this->strategy->calculateCost($order),
            'estimated_days' => $this->strategy->getEstimatedDays(),
        ];
    }
}
```

```php
// Usage in Controller
namespace App\Http\Controllers;

use App\Services\ShippingService;
use App\Services\Shipping\StandardShipping;
use App\Services\Shipping\ExpressShipping;
use App\Services\Shipping\OvernightShipping;

class CheckoutController extends Controller
{
    public function calculateShipping(Request $request)
    {
        $order = Order::find($request->order_id);

        $shippingService = new ShippingService();

        // Customer chooses shipping method
        $strategy = match($request->shipping_method) {
            'standard' => new StandardShipping(),
            'express' => new ExpressShipping(),
            'overnight' => new OvernightShipping(),
        };

        $shippingService->setStrategy($strategy);

        $shipping = $shippingService->calculateShipping($order);

        return response()->json($shipping);
    }
}
```

**Benefits:**
- Easy to add new shipping methods
- Change shipping calculation at runtime
- Each strategy is independently testable

---

## 3. Observer Pattern

**Purpose**: Define a one-to-many dependency where when one object changes state, all dependents are notified.

**When to use**: When an event should trigger multiple actions.

### Real-World Analogy

When you publish a YouTube video (event), all subscribers (observers) get notified.

### Laravel Example: Order Events

Laravel's event system implements Observer pattern beautifully.

```php
// app/Events/OrderPlaced.php
namespace App\Events;

use App\Models\Order;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderPlaced
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Order $order
    ) {}
}
```

```php
// app/Listeners/SendOrderConfirmationEmail.php
namespace App\Listeners;

use App\Events\OrderPlaced;
use App\Mail\OrderConfirmation;
use Illuminate\Support\Facades\Mail;

class SendOrderConfirmationEmail
{
    public function handle(OrderPlaced $event): void
    {
        Mail::to($event->order->customer->email)
            ->send(new OrderConfirmation($event->order));
    }
}
```

```php
// app/Listeners/UpdateInventory.php
namespace App\Listeners;

use App\Events\OrderPlaced;

class UpdateInventory
{
    public function handle(OrderPlaced $event): void
    {
        foreach ($event->order->items as $item) {
            $item->product->decrement('stock', $item->quantity);
        }
    }
}
```

```php
// app/Listeners/CreateInvoice.php
namespace App\Listeners;

use App\Events\OrderPlaced;
use App\Models\Invoice;

class CreateInvoice
{
    public function handle(OrderPlaced $event): void
    {
        Invoice::create([
            'order_id' => $event->order->id,
            'total' => $event->order->total,
            'status' => 'pending',
        ]);
    }
}
```

```php
// app/Listeners/NotifyAdmins.php
namespace App\Listeners;

use App\Events\OrderPlaced;
use App\Notifications\NewOrderNotification;
use App\Models\User;

class NotifyAdmins
{
    public function handle(OrderPlaced $event): void
    {
        $admins = User::where('role', 'admin')->get();

        foreach ($admins as $admin) {
            $admin->notify(new NewOrderNotification($event->order));
        }
    }
}
```

```php
// app/Providers/EventServiceProvider.php
namespace App\Providers;

use App\Events\OrderPlaced;
use App\Listeners\SendOrderConfirmationEmail;
use App\Listeners\UpdateInventory;
use App\Listeners\CreateInvoice;
use App\Listeners\NotifyAdmins;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        OrderPlaced::class => [
            SendOrderConfirmationEmail::class,
            UpdateInventory::class,
            CreateInvoice::class,
            NotifyAdmins::class,
        ],
    ];
}
```

```php
// Triggering the event
namespace App\Services;

use App\Events\OrderPlaced;
use App\Models\Order;

class OrderService
{
    public function createOrder(array $data): Order
    {
        $order = Order::create($data);

        // Fire event - all listeners automatically execute
        event(new OrderPlaced($order));

        return $order;
    }
}
```

**Benefits:**
- Loose coupling - service doesn't know about listeners
- Easy to add/remove side effects
- Listeners can be queued for async processing
- Each listener is independently testable

---

## 4. Decorator Pattern

**Purpose**: Add behavior to objects dynamically without changing their structure.

**When to use**: When you want to add responsibilities to objects without subclassing.

### Real-World Analogy

A basic coffee (base object) can be decorated with milk, sugar, whipped cream (decorators), each adding functionality and cost.

### Laravel Example: Price Calculator with Discounts

```php
// app/Contracts/PriceCalculatorInterface.php
namespace App\Contracts;

interface PriceCalculatorInterface
{
    public function calculate(): float;
}
```

```php
// app/Services/Pricing/BasePrice.php
namespace App\Services\Pricing;

use App\Contracts\PriceCalculatorInterface;
use App\Models\Order;

class BasePrice implements PriceCalculatorInterface
{
    public function __construct(
        private Order $order
    ) {}

    public function calculate(): float
    {
        return $this->order->items->sum(function ($item) {
            return $item->price * $item->quantity;
        });
    }
}
```

```php
// app/Services/Pricing/TaxDecorator.php
namespace App\Services\Pricing;

use App\Contracts\PriceCalculatorInterface;

class TaxDecorator implements PriceCalculatorInterface
{
    public function __construct(
        private PriceCalculatorInterface $calculator,
        private float $taxRate = 0.20 // 20% tax
    ) {}

    public function calculate(): float
    {
        $basePrice = $this->calculator->calculate();
        return $basePrice + ($basePrice * $this->taxRate);
    }
}
```

```php
// app/Services/Pricing/DiscountDecorator.php
namespace App\Services\Pricing;

use App\Contracts\PriceCalculatorInterface;

class DiscountDecorator implements PriceCalculatorInterface
{
    public function __construct(
        private PriceCalculatorInterface $calculator,
        private float $discountPercentage // e.g., 10 for 10%
    ) {}

    public function calculate(): float
    {
        $basePrice = $this->calculator->calculate();
        $discount = $basePrice * ($this->discountPercentage / 100);
        return $basePrice - $discount;
    }
}
```

```php
// app/Services/Pricing/ShippingDecorator.php
namespace App\Services\Pricing;

use App\Contracts\PriceCalculatorInterface;

class ShippingDecorator implements PriceCalculatorInterface
{
    public function __construct(
        private PriceCalculatorInterface $calculator,
        private float $shippingCost
    ) {}

    public function calculate(): float
    {
        return $this->calculator->calculate() + $this->shippingCost;
    }
}
```

```php
// app/Services/Pricing/CouponDecorator.php
namespace App\Services\Pricing;

use App\Contracts\PriceCalculatorInterface;

class CouponDecorator implements PriceCalculatorInterface
{
    public function __construct(
        private PriceCalculatorInterface $calculator,
        private float $couponValue
    ) {}

    public function calculate(): float
    {
        $basePrice = $this->calculator->calculate();
        return max(0, $basePrice - $this->couponValue); // Can't be negative
    }
}
```

```php
// Usage
namespace App\Services;

use App\Services\Pricing\BasePrice;
use App\Services\Pricing\TaxDecorator;
use App\Services\Pricing\DiscountDecorator;
use App\Services\Pricing\ShippingDecorator;
use App\Services\Pricing\CouponDecorator;
use App\Models\Order;

class OrderPricingService
{
    public function calculateFinalPrice(Order $order): float
    {
        // Start with base price
        $calculator = new BasePrice($order);

        // Add decorators based on order properties
        if ($order->customer->is_loyal) {
            $calculator = new DiscountDecorator($calculator, 10); // 10% loyalty discount
        }

        if ($order->coupon_code) {
            $couponValue = $this->getCouponValue($order->coupon_code);
            $calculator = new CouponDecorator($calculator, $couponValue);
        }

        // Add shipping
        $calculator = new ShippingDecorator($calculator, 15);

        // Add tax (after discounts)
        $calculator = new TaxDecorator($calculator, 0.20);

        return $calculator->calculate();
    }

    private function getCouponValue(string $code): float
    {
        // Look up coupon value
        return 20; // Example: $20 off
    }
}
```

**Benefits:**
- Add/remove price modifiers without changing base code
- Mix and match decorators in any order
- Each decorator is testable independently
- Flexible pricing calculation

---

## 5. Singleton Pattern

**Purpose**: Ensure a class has only one instance and provide a global access point.

**When to use**: When exactly one object is needed to coordinate actions across the system.

### Real-World Analogy

A country has only one president at a time. You can't have multiple presidents simultaneously.

### Laravel Example: Configuration Manager

Laravel facades are essentially Singletons.

```php
// app/Services/ConfigurationManager.php
namespace App\Services;

class ConfigurationManager
{
    private static ?ConfigurationManager $instance = null;
    private array $config = [];

    // Private constructor prevents direct instantiation
    private function __construct()
    {
        $this->loadConfiguration();
    }

    // Prevent cloning
    private function __clone() {}

    // Prevent unserialization
    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize singleton");
    }

    public static function getInstance(): ConfigurationManager
    {
        if (self::$instance === null) {
            self::$instance = new ConfigurationManager();
        }

        return self::$instance;
    }

    private function loadConfiguration(): void
    {
        // Load configuration from database or cache
        $this->config = [
            'app_name' => 'My App',
            'maintenance_mode' => false,
            'max_upload_size' => 10485760, // 10MB
        ];
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->config[$key] ?? $default;
    }

    public function set(string $key, mixed $value): void
    {
        $this->config[$key] = $value;
    }
}
```

```php
// Usage
$config = ConfigurationManager::getInstance();
$appName = $config->get('app_name');

// Same instance everywhere
$config2 = ConfigurationManager::getInstance();
$config2->set('maintenance_mode', true);

$config3 = ConfigurationManager::getInstance();
echo $config3->get('maintenance_mode'); // true (same instance)
```

**Warning**: Singletons can make testing difficult and create hidden dependencies. Use sparingly!

### Better Alternative in Laravel: Service Container

Laravel's service container provides singleton behavior without the downsides:

```php
// app/Providers/AppServiceProvider.php
public function register(): void
{
    // Singleton binding
    $this->app->singleton(ConfigurationManager::class, function ($app) {
        return new ConfigurationManager();
    });
}

// Usage - Laravel resolves the same instance every time
public function __construct(ConfigurationManager $config)
{
    $this->config = $config;
}
```

---

## 6. Repository Pattern (Revisited)

We covered this in Lesson 03, but it's worth mentioning as a design pattern.

**Purpose**: Abstract data access logic.

---

## 7. Adapter Pattern

**Purpose**: Convert one interface to another that clients expect.

**When to use**: When you want to use an existing class but its interface doesn't match what you need.

### Real-World Analogy

A power adapter converts US plug (110V) to European socket (220V).

### Laravel Example: Payment Gateway Adapter

```php
// Your application's payment interface
interface PaymentGatewayInterface
{
    public function charge(float $amount, string $currency): bool;
    public function refund(string $transactionId, float $amount): bool;
}

// Third-party Stripe library (you can't modify)
class StripeClient
{
    public function createCharge($amountInCents, $currencyCode, $options = [])
    {
        // Stripe's own method signature
    }

    public function createRefund($chargeId, $amountInCents)
    {
        // Stripe's own method signature
    }
}

// Adapter makes Stripe compatible with your interface
class StripeAdapter implements PaymentGatewayInterface
{
    public function __construct(
        private StripeClient $stripe
    ) {}

    public function charge(float $amount, string $currency): bool
    {
        // Adapt your interface to Stripe's interface
        $amountInCents = $amount * 100;
        $result = $this->stripe->createCharge($amountInCents, $currency);

        return $result->status === 'succeeded';
    }

    public function refund(string $transactionId, float $amount): bool
    {
        $amountInCents = $amount * 100;
        $result = $this->stripe->createRefund($transactionId, $amountInCents);

        return $result->status === 'succeeded';
    }
}

// Usage
$stripe = new StripeClient();
$adapter = new StripeAdapter($stripe);

// Now Stripe works with your standardized interface
$adapter->charge(100.00, 'USD');
```

---

## Pattern Selection Guide

| Problem | Pattern | Example |
|---------|---------|---------|
| Need to create different object types | Factory | Report generators (PDF, Excel, CSV) |
| Multiple algorithms for same task | Strategy | Shipping methods, payment methods |
| One event triggers multiple actions | Observer | Order placed → email, inventory, invoice |
| Add behavior without changing class | Decorator | Price calculation with tax, discount, shipping |
| Need exactly one instance | Singleton | Configuration, cache, logger |
| Abstract data access | Repository | User repository, order repository |
| Make incompatible interfaces work | Adapter | Third-party API integration |

---

## Anti-Patterns to Avoid

### 1. God Object

One class that does everything.

```php
// Bad: God class
class OrderManager
{
    public function createOrder() {}
    public function validateOrder() {}
    public function calculatePrice() {}
    public function processPayment() {}
    public function sendEmail() {}
    public function updateInventory() {}
    public function generateInvoice() {}
    public function calculateShipping() {}
    // ... 50 more methods
}

// Good: Split responsibilities
class OrderService {}
class OrderValidator {}
class PricingService {}
class PaymentService {}
class EmailService {}
class InventoryService {}
```

### 2. Premature Optimization

Applying complex patterns before they're needed.

```php
// Bad: Over-engineering simple case
$calculator = new BasePrice($order);
$calculator = new TaxDecorator($calculator);
$total = $calculator->calculate();

// Good: Simple case, simple solution
$total = $order->subtotal * 1.20; // Add 20% tax
```

### 3. Copy-Paste Programming

Duplicating code instead of using patterns.

```php
// Bad: Duplicated logic
if ($paymentMethod === 'stripe') {
    // 50 lines of Stripe code
}
if ($paymentMethod === 'paypal') {
    // 50 lines similar to Stripe
}
if ($paymentMethod === 'bitcoin') {
    // 50 lines similar to Stripe
}

// Good: Strategy pattern
$gateway = $paymentFactory->create($paymentMethod);
$gateway->charge($order);
```

---

## Quick Quiz

1. **Which pattern creates objects without specifying their concrete classes?**
   - A) Strategy
   - B) Factory
   - C) Observer
   - D) Singleton

2. **Which pattern allows adding behavior without modifying existing code?**
   - A) Adapter
   - B) Factory
   - C) Decorator
   - D) Repository

3. **Which pattern ensures only one instance of a class exists?**
   - A) Factory
   - B) Singleton
   - C) Strategy
   - D) Observer

**Answers**: 1-B, 2-C, 3-B

---

## Summary

**Common Design Patterns:**

1. **Factory**: Create objects based on input
2. **Strategy**: Interchangeable algorithms
3. **Observer**: One event, multiple reactions
4. **Decorator**: Add behavior dynamically
5. **Singleton**: One instance only
6. **Adapter**: Make incompatible interfaces work

**Key Takeaways:**
- Patterns solve recurring problems
- Don't over-engineer simple solutions
- Combine patterns for complex scenarios
- Laravel implements many patterns internally

**Remember**: Patterns are tools, not rules. Use them when they make your code clearer, not more complex.

In the next lesson, we'll explore **Dependency Injection** in depth!
