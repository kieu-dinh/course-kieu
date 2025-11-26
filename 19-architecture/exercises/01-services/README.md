# Exercise 19.1 - Service Layer

## Objective

Learn how to use the Service Layer pattern to encapsulate business logic separate from controllers.

---

## Task

Refactor your API from previous exercises to use a Service layer for handling business logic.

### Requirements

1. **Create Service Classes**
   - Extract business logic from controller into service classes
   - One service per feature/domain (e.g., ItemService)
   - Services contain the "how" of operations

2. **Service Responsibilities**
   - Data validation
   - Database operations
   - Business logic calculations
   - External API calls
   - Email/notification sending

3. **Controller Responsibilities** (After refactoring)
   - Request handling
   - Calling appropriate services
   - Returning responses
   - HTTP concerns only

4. **Dependency Injection**
   - Inject services into controllers via constructor
   - Use Laravel's service container
   - Make services testable

5. **Error Handling**
   - Create custom exceptions for business logic errors
   - Handle errors gracefully
   - Return appropriate error responses

---

## Starter Code

```bash
# Create service class
mkdir -p app/Services
```

```php
// app/Services/ItemService.php
namespace App\Services;

use App\Models\Item;
use Exception;

class ItemService
{
    /**
     * Get all items with optional filters
     */
    public function getAllItems(array $filters = [])
    {
        $query = Item::query();

        if (isset($filters['search'])) {
            $query->where('name', 'like', "%{$filters['search']}%");
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->paginate(15);
    }

    /**
     * Create a new item
     */
    public function createItem(array $data): Item
    {
        // Validate data (or use FormRequest in controller)
        if (empty($data['name'])) {
            throw new Exception('Name is required');
        }

        return Item::create($data);
    }

    /**
     * Update an existing item
     */
    public function updateItem(Item $item, array $data): Item
    {
        $item->update($data);
        return $item->fresh();
    }

    /**
     * Delete an item
     */
    public function deleteItem(Item $item): bool
    {
        return $item->delete();
    }

    /**
     * Get item with related data
     */
    public function getItemWithDetails(Item $item): Item
    {
        return $item->load(['category', 'tags']);
    }

    /**
     * Complex business logic example
     */
    public function calculateDiscount(Item $item, int $quantity): float
    {
        $subtotal = $item->price * $quantity;

        if ($quantity >= 10) {
            return $subtotal * 0.90; // 10% discount
        }

        if ($quantity >= 5) {
            return $subtotal * 0.95; // 5% discount
        }

        return $subtotal;
    }
}

// app/Http/Controllers/ItemController.php
namespace App\Http\Controllers;

use App\Services\ItemService;
use App\Http\Resources\ItemResource;
use App\Http\Resources\ItemCollection;
use App\Http\Requests\StoreItemRequest;
use App\Models\Item;

class ItemController extends Controller
{
    private ItemService $itemService;

    public function __construct(ItemService $itemService)
    {
        $this->itemService = $itemService;
    }

    public function index()
    {
        $items = $this->itemService->getAllItems(
            request()->only(['search', 'status'])
        );
        return new ItemCollection($items);
    }

    public function store(StoreItemRequest $request)
    {
        $item = $this->itemService->createItem($request->validated());
        return new ItemResource($item);
    }

    public function show(Item $item)
    {
        $item = $this->itemService->getItemWithDetails($item);
        return new ItemResource($item);
    }

    public function update(StoreItemRequest $request, Item $item)
    {
        $item = $this->itemService->updateItem($item, $request->validated());
        return new ItemResource($item);
    }

    public function destroy(Item $item)
    {
        $this->itemService->deleteItem($item);
        return response()->json(['message' => 'Item deleted']);
    }
}
```

---

## Directory Structure

```
app/
├── Services/
│   ├── ItemService.php
│   ├── OrderService.php
│   ├── PaymentService.php
│   └── NotificationService.php
├── Http/
│   ├── Controllers/
│   │   ├── ItemController.php
│   │   ├── OrderController.php
│   │   └── PaymentController.php
│   └── Requests/
└── Models/
```

---

## Benefits of Service Layer

- **Separation of Concerns**: Controllers only handle HTTP
- **Reusability**: Use services from commands, jobs, other services
- **Testability**: Test business logic without HTTP layer
- **Maintainability**: Logic is organized and easy to find
- **Scalability**: Add features without touching controllers

---

## Testing Services

```php
// tests/Unit/ItemServiceTest.php
namespace Tests\Unit;

use Tests\TestCase;
use App\Services\ItemService;
use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ItemServiceTest extends TestCase
{
    use RefreshDatabase;

    private ItemService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ItemService();
    }

    public function test_service_creates_item()
    {
        $item = $this->service->createItem([
            'name' => 'Test Item',
            'description' => 'Test'
        ]);

        $this->assertDatabaseHas('items', ['id' => $item->id]);
    }

    public function test_service_calculates_discount()
    {
        $item = Item::factory()->create(['price' => 100]);

        $price = $this->service->calculateDiscount($item, 10);
        $this->assertEquals(900, $price);
    }
}
```

---

## Bonus Challenges

1. Create NotificationService for sending emails
2. Create PaymentService for processing payments
3. Create custom exceptions (ItemNotFoundException, InvalidPriceException)
4. Add logging to service methods
5. Create a BaseService class with common methods
6. Implement service with transactions for multi-step operations

---

## Checklist

- [ ] ItemService created with all CRUD methods
- [ ] Controller refactored to use service
- [ ] Business logic moved to service
- [ ] Dependency injection working
- [ ] API endpoints still work
- [ ] Services are testable
- [ ] Code is cleaner and more maintainable

---

## Solution Check

The controller should be much simpler:
- Only HTTP concerns
- Delegates to service
- No database queries

The service should have:
- All business logic
- Testable without HTTP
- Reusable from other contexts
