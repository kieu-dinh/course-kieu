# Exercise 19.4 - Refactoring Exercise

## Objective

Practice refactoring legacy code to improve quality, readability, and maintainability.

---

## Task

Refactor an existing piece of code (from previous exercises or provided) to be cleaner, more efficient, and follow best practices.

---

## Example: Refactor Item API Controller

### Starting Point: Bad Code

```php
// BAD - Multiple responsibilities, hard to test, tightly coupled
class ItemController extends Controller
{
    public function index(Request $request)
    {
        $items = DB::table('items')->get();

        // Manual filtering
        if ($request->has('search')) {
            $items = $items->filter(function($item) use ($request) {
                return strpos($item->name, $request->search) !== false;
            });
        }

        if ($request->has('status')) {
            $items = $items->filter(function($item) use ($request) {
                return $item->status === $request->status;
            });
        }

        // Manual sorting
        if ($request->has('sort')) {
            $items = $items->sortBy($request->sort);
        }

        // Manual response formatting
        $result = [];
        foreach ($items as $item) {
            $result[] = [
                'id' => $item->id,
                'name' => $item->name,
                'description' => $item->description,
                'price' => $item->price,
                'created_at' => $item->created_at->format('Y-m-d'),
                'updated_at' => $item->updated_at->format('Y-m-d'),
            ];
        }

        return response()->json($result);
    }

    public function store(Request $request)
    {
        // Manual validation scattered throughout
        $errors = [];
        if (empty($request->name)) {
            $errors['name'] = 'Name is required';
        }
        if (strlen($request->name) < 3) {
            $errors['name'] = 'Name must be at least 3 characters';
        }
        if (empty($request->email)) {
            $errors['email'] = 'Email is required';
        }
        if (!filter_var($request->email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email must be valid';
        }

        if (!empty($errors)) {
            return response()->json(['errors' => $errors], 422);
        }

        // Hardcoded database operations
        $id = DB::table('items')->insertGetId([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Manual response
        $item = DB::table('items')->where('id', $id)->first();

        return response()->json([
            'id' => $item->id,
            'name' => $item->name,
            'description' => $item->description,
            'price' => $item->price,
        ], 201);
    }

    public function show($id)
    {
        $item = DB::table('items')->where('id', $id)->first();

        if (!$item) {
            return response()->json(['error' => 'Not found'], 404);
        }

        return response()->json([
            'id' => $item->id,
            'name' => $item->name,
            'description' => $item->description,
            'price' => $item->price,
        ]);
    }

    public function update(Request $request, $id)
    {
        $item = DB::table('items')->where('id', $id)->first();

        if (!$item) {
            return response()->json(['error' => 'Not found'], 404);
        }

        // Validation again
        $errors = [];
        if (!empty($request->name) && strlen($request->name) < 3) {
            $errors['name'] = 'Name must be at least 3 characters';
        }

        if (!empty($errors)) {
            return response()->json(['errors' => $errors], 422);
        }

        DB::table('items')
            ->where('id', $id)
            ->update([
                'name' => $request->name ?? $item->name,
                'description' => $request->description ?? $item->description,
                'price' => $request->price ?? $item->price,
                'updated_at' => now(),
            ]);

        $updated = DB::table('items')->where('id', $id)->first();

        return response()->json([
            'id' => $updated->id,
            'name' => $updated->name,
            'description' => $updated->description,
            'price' => $updated->price,
        ]);
    }

    public function destroy($id)
    {
        $count = DB::table('items')->where('id', $id)->delete();

        if ($count === 0) {
            return response()->json(['error' => 'Not found'], 404);
        }

        return response()->json(['message' => 'Deleted']);
    }
}
```

---

## Refactored Version

**Step 1: Create Model & Use Eloquent**
```php
class Item extends Model
{
    protected $fillable = ['name', 'description', 'price', 'status'];
}
```

**Step 2: Create Form Request for Validation**
```php
class StoreItemRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|min:3|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'status' => 'nullable|in:active,inactive',
        ];
    }
}
```

**Step 3: Create API Resource**
```php
class ItemResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'created_at' => $this->created_at?->format('Y-m-d'),
        ];
    }
}

class ItemCollection extends ResourceCollection
{
    public $collects = ItemResource::class;
}
```

**Step 4: Create Service**
```php
class ItemService
{
    public function index(array $filters = [])
    {
        $query = Item::query();

        if (isset($filters['search'])) {
            $query->where('name', 'like', "%{$filters['search']}%");
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['sort'])) {
            $query->orderBy($filters['sort']);
        }

        return $query->paginate();
    }

    public function create(array $data): Item
    {
        return Item::create($data);
    }

    public function update(Item $item, array $data): Item
    {
        $item->update($data);
        return $item->fresh();
    }

    public function delete(Item $item): bool
    {
        return $item->delete();
    }
}
```

**Step 5: Clean Controller**
```php
class ItemController extends Controller
{
    public function __construct(
        private ItemService $service
    ) {}

    public function index(Request $request)
    {
        $items = $this->service->index(
            $request->only(['search', 'status', 'sort'])
        );
        return new ItemCollection($items);
    }

    public function store(StoreItemRequest $request)
    {
        $item = $this->service->create($request->validated());
        return new ItemResource($item);
    }

    public function show(Item $item)
    {
        return new ItemResource($item);
    }

    public function update(StoreItemRequest $request, Item $item)
    {
        $item = $this->service->update($item, $request->validated());
        return new ItemResource($item);
    }

    public function destroy(Item $item)
    {
        $this->service->delete($item);
        return response()->json(['message' => 'Deleted']);
    }
}
```

---

## Refactoring Improvements

| Aspect | Before | After |
|--------|--------|-------|
| **LOC** | 180+ | 50+ |
| **Validation** | Manual, repetitive | Form Request (single source) |
| **Database** | Raw SQL | Eloquent ORM |
| **Filtering** | Manual loops | Query builder |
| **Response** | Manual formatting | API Resources |
| **Testability** | Hard to test | Easy to test |
| **Reusability** | Limited | High (service layer) |
| **Maintainability** | Low | High |

---

## Refactoring Checklist

- [ ] Remove code duplication
- [ ] Extract validation to Form Request
- [ ] Use Eloquent instead of raw SQL
- [ ] Create API Resources
- [ ] Extract business logic to Service
- [ ] Reduce controller size
- [ ] Add type hints
- [ ] Improve readability
- [ ] Make code testable
- [ ] Follow Laravel conventions
- [ ] Tests still pass

---

## Your Refactoring Task

Choose one of:

1. **Refactor your Item API** (from previous exercises)
2. **Refactor provided code** (ask for sample)
3. **Refactor existing project code** (if you have one)

Make sure to:
- Before: Document current code structure
- During: Apply refactoring techniques step-by-step
- After: Verify all functionality still works
- Test: Run tests to ensure nothing broke

---

## Common Refactoring Techniques

1. **Extract Method**: Break large methods into smaller ones
2. **Extract Class**: Move related code to new class
3. **Replace Magic Strings**: Use constants
4. **Simplify Conditionals**: Use guard clauses
5. **Remove Duplicates**: DRY principle
6. **Rename for Clarity**: Better variable/method names
7. **Use Appropriate Patterns**: Service, Repository, etc.
8. **Add Type Hints**: Better IDE support and safety

---

## Solution Check

After refactoring:
- [ ] All endpoints still work
- [ ] Code is more readable
- [ ] No code duplication
- [ ] Tests pass
- [ ] Controller is simplified
- [ ] Business logic extracted
- [ ] Validation is consistent
- [ ] Follow Laravel best practices
