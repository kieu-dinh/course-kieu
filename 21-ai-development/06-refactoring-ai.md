# Lesson 6 - Refactoring with AI

**Duration**: 3-4 hours
**Prerequisites**: Lessons 1-5

---

## Introduction

Refactoring is the process of improving code structure without changing its behavior. It's essential for maintaining healthy codebases, but it's time-consuming and risky - you might break something while improving it.

AI can accelerate refactoring by:
- Suggesting structural improvements
- Applying design patterns
- Extracting reusable code
- Modernizing legacy code
- Enforcing consistent style

But AI refactoring comes with risks:
- Might change behavior subtly
- Can over-engineer simple code
- Might not understand business context
- Can introduce bugs

In this lesson, you'll learn to refactor safely and effectively with AI assistance, leveraging your fundamental knowledge to guide and verify the changes.

---

## When to Refactor

### Good Reasons to Refactor

**1. Code Smells**
- Duplicated code
- Long methods (>50 lines)
- Large classes (>500 lines)
- Too many parameters (>3)
- Complex conditionals
- Magic numbers/strings

**2. Maintainability Issues**
- Hard to understand
- Hard to test
- Hard to change
- Tightly coupled
- No clear responsibilities

**3. Performance Problems**
- Inefficient algorithms
- Unnecessary computations
- Memory issues
- Slow queries

**4. Modernization**
- Outdated syntax
- Deprecated features
- New language features available
- Framework updates

**5. Technical Debt**
- Quick hacks grown old
- Inconsistent patterns
- Missing abstractions

---

### Bad Reasons to Refactor

**❌ Don't refactor if:**
- Code works fine and rarely changes
- You're on a tight deadline
- No tests to verify behavior
- You don't understand the code
- Just for the sake of using new pattern
- Someone says "this looks ugly" without specific issues

**Golden Rule:** If it ain't broke and maintainable, don't fix it.

---

## Types of Refactoring

### 1. Structural Refactoring

Improving code organization without changing logic:

**Examples:**
- Extract method
- Extract class
- Move method
- Rename variable/method
- Split large file

---

### 2. Pattern-Based Refactoring

Applying design patterns:

**Examples:**
- Service layer extraction
- Repository pattern
- Strategy pattern
- Factory pattern
- Observer pattern

---

### 3. Modernization Refactoring

Updating to newer syntax/features:

**Examples:**
- PHP 7 → PHP 8 features
- Laravel 8 → Laravel 11 syntax
- Array syntax → Collection methods
- Traditional loops → Modern iterations

---

### 4. Performance Refactoring

Optimizing for speed/memory:

**Examples:**
- Query optimization
- Caching implementation
- Algorithm improvements
- Lazy loading

---

## AI Refactoring Workflow

### The Safe Refactoring Process

```
1. BACKUP
   ↓ Commit current working code
   ↓ Create refactoring branch

2. UNDERSTAND
   ↓ Read and understand current code
   ↓ Document expected behavior

3. TEST
   ↓ Write tests if none exist
   ↓ Ensure all tests pass

4. PLAN
   ↓ Identify what to refactor
   ↓ Define desired outcome

5. PROMPT AI
   ↓ Request specific refactoring
   ↓ Provide full context

6. REVIEW
   ↓ Understand AI suggestions
   ↓ Check for behavior changes
   ↓ Verify improvements

7. APPLY
   ↓ Implement changes carefully
   ↓ One refactoring at a time

8. TEST AGAIN
   ↓ Run all tests
   ↓ Manual testing
   ↓ Compare with original

9. COMMIT
   ↓ Small, focused commits
   ↓ Clear commit messages

10. ITERATE
    ↓ Next refactoring
```

**Critical:** Never skip steps, especially testing!

---

## Refactoring Prompt Templates

### Template 1: Extract Method

```
Refactor this method by extracting logical sections into separate methods:

[paste method]

Requirements:
- Each extracted method should have single responsibility
- Use descriptive names
- Add type hints and return types
- Keep original behavior identical
- Maintain readability

Context:
- [Framework version]
- [Purpose of original method]
- [Any business logic to preserve]

Show:
1. Refactored code
2. List of extracted methods
3. Explanation of changes
```

---

### Template 2: Apply Design Pattern

```
Refactor this code to use [pattern name] pattern:

[paste code]

Current issues:
- [issue 1]
- [issue 2]

Goals:
- Improve testability
- Reduce coupling
- Increase flexibility

Requirements:
- Preserve all current behavior
- Add type hints
- Include usage example
- Show before/after comparison

Context:
- Laravel 11, PHP 8.2
- [Current architecture]
```

---

### Template 3: Modernize Code

```
Modernize this code to use PHP 8.2 and Laravel 11 features:

[paste code]

Current version: [PHP 7.4/Laravel 8]
Target version: PHP 8.2/Laravel 11

Apply:
- Union types
- Named arguments
- Match expressions
- Constructor property promotion
- Null safe operator
- Laravel 11 conventions

Show:
- Before/after comparison
- Explanation of each modernization
- Benefits of changes
```

---

### Template 4: Performance Optimization

```
Optimize this code for performance:

[paste code]

Current performance:
- [measurement]

Problems:
- [specific issue 1]
- [specific issue 2]

Requirements:
- Maintain exact behavior
- Show performance impact estimate
- Explain each optimization
- Keep code readable

Context:
- Data volume: [X records]
- Current query count: [Y]
- Target: [desired performance]
```

---

## Real-World Refactoring Examples

### Example 1: Extract Service Layer

**Before (Fat Controller):**
```php
<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderConfirmation;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'products' => 'required|array',
            'products.*.id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();

        try {
            $total = 0;
            $orderItems = [];

            foreach ($validated['products'] as $item) {
                $product = Product::findOrFail($item['id']);

                if ($product->stock < $item['quantity']) {
                    return back()->withErrors(['stock' => 'Insufficient stock for ' . $product->name]);
                }

                $product->decrement('stock', $item['quantity']);

                $subtotal = $product->price * $item['quantity'];
                $total += $subtotal;

                $orderItems[] = [
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                    'subtotal' => $subtotal,
                ];
            }

            $order = Order::create([
                'user_id' => auth()->id(),
                'total' => $total,
                'status' => 'pending',
            ]);

            $order->items()->createMany($orderItems);

            Mail::to(auth()->user())->send(new OrderConfirmation($order));

            DB::commit();

            return redirect()->route('orders.show', $order)->with('success', 'Order placed successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to place order: ' . $e->getMessage()]);
        }
    }
}
```

**Refactoring Prompt:**
```
Refactor this OrderController to extract business logic into a service layer:

[paste controller above]

Requirements:
- Create OrderService for business logic
- Keep controller thin (just HTTP concerns)
- Use dependency injection
- Maintain all current behavior
- Improve testability
- Add type hints everywhere
- Handle errors appropriately

Show:
1. Refactored controller
2. OrderService class
3. Any DTOs/Value Objects if needed
4. Explanation of separation
```

**After (Refactored):**

```php
<?php
// app/Services/OrderService.php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\DTOs\OrderData;
use App\Exceptions\InsufficientStockException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderConfirmation;

class OrderService
{
    public function createOrder(User $user, array $products): Order
    {
        DB::beginTransaction();

        try {
            $orderData = $this->prepareOrderData($products);

            $this->validateStock($orderData);

            $order = $this->saveOrder($user, $orderData);

            $this->updateInventory($orderData);

            $this->sendConfirmation($user, $order);

            DB::commit();

            return $order;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function prepareOrderData(array $products): OrderData
    {
        $total = 0;
        $items = [];

        foreach ($products as $item) {
            $product = Product::findOrFail($item['id']);

            $subtotal = $product->price * $item['quantity'];
            $total += $subtotal;

            $items[] = [
                'product' => $product,
                'quantity' => $item['quantity'],
                'price' => $product->price,
                'subtotal' => $subtotal,
            ];
        }

        return new OrderData($total, $items);
    }

    private function validateStock(OrderData $orderData): void
    {
        foreach ($orderData->items as $item) {
            if ($item['product']->stock < $item['quantity']) {
                throw new InsufficientStockException(
                    "Insufficient stock for {$item['product']->name}"
                );
            }
        }
    }

    private function saveOrder(User $user, OrderData $orderData): Order
    {
        $order = Order::create([
            'user_id' => $user->id,
            'total' => $orderData->total,
            'status' => 'pending',
        ]);

        $orderItems = collect($orderData->items)->map(fn($item) => [
            'product_id' => $item['product']->id,
            'quantity' => $item['quantity'],
            'price' => $item['price'],
            'subtotal' => $item['subtotal'],
        ])->toArray();

        $order->items()->createMany($orderItems);

        return $order;
    }

    private function updateInventory(OrderData $orderData): void
    {
        foreach ($orderData->items as $item) {
            $item['product']->decrement('stock', $item['quantity']);
        }
    }

    private function sendConfirmation(User $user, Order $order): void
    {
        Mail::to($user)->send(new OrderConfirmation($order));
    }
}
```

```php
<?php
// app/DTOs/OrderData.php

namespace App\DTOs;

class OrderData
{
    public function __construct(
        public readonly float $total,
        public readonly array $items,
    ) {}
}
```

```php
<?php
// app/Exceptions/InsufficientStockException.php

namespace App\Exceptions;

use Exception;

class InsufficientStockException extends Exception
{
    //
}
```

```php
<?php
// app/Http/Controllers/OrderController.php (Refactored)

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Services\OrderService;
use App\Exceptions\InsufficientStockException;
use Illuminate\Http\RedirectResponse;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService
    ) {}

    public function store(StoreOrderRequest $request): RedirectResponse
    {
        try {
            $order = $this->orderService->createOrder(
                $request->user(),
                $request->validated('products')
            );

            return redirect()
                ->route('orders.show', $order)
                ->with('success', 'Order placed successfully!');

        } catch (InsufficientStockException $e) {
            return back()->withErrors(['stock' => $e->getMessage()]);

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to place order']);
        }
    }
}
```

**Benefits:**
- ✅ Controller reduced from 60 lines to 25 lines
- ✅ Business logic testable independently
- ✅ Single Responsibility Principle
- ✅ Easier to maintain and modify
- ✅ Reusable service (e.g., for admin orders, API)
- ✅ Clear method names document the process

---

### Example 2: Modernize to PHP 8.2

**Before (PHP 7.4 style):**
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $fillable = ['title', 'body', 'status'];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->status = $this->status ?? 'draft';
    }

    public function getIsPublishedAttribute()
    {
        if ($this->published_at === null) {
            return false;
        }

        return $this->published_at->isPast();
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
```

**Refactoring Prompt:**
```
Modernize this Laravel model to use PHP 8.2 and Laravel 11 features:

[paste code above]

Apply:
- Constructor property promotion
- Null safe operator
- Type hints and return types
- Enum for status (if appropriate)
- Modern Laravel conventions
- Attribute instead of accessor method

Maintain all behavior.
```

**After (PHP 8.2 / Laravel 11):**

```php
<?php

namespace App\Models;

use App\Enums\PostStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Post extends Model
{
    protected $fillable = ['title', 'body', 'status'];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'status' => PostStatus::class,
        ];
    }

    protected function isPublished(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->published_at?->isPast() ?? false
        );
    }

    public function scopeByStatus($query, PostStatus $status): void
    {
        $query->where('status', $status);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
```

```php
<?php
// app/Enums/PostStatus.php

namespace App\Enums;

enum PostStatus: string
{
    case DRAFT = 'draft';
    case PUBLISHED = 'published';
    case ARCHIVED = 'archived';
}
```

**Improvements:**
- ✅ Type hints on all methods
- ✅ Return types specified
- ✅ Null safe operator (?->)
- ✅ Enum for status (type-safe)
- ✅ Modern Laravel 11 casts method
- ✅ Modern attribute syntax
- ✅ BelongsTo return type

---

### Example 3: Repository Pattern

**Before (Direct Eloquent in Controller):**
```php
<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::with('author')
            ->where('status', 'published')
            ->orderBy('published_at', 'desc')
            ->paginate(15);

        return view('posts.index', compact('posts'));
    }

    public function show($id)
    {
        $post = Post::with(['author', 'comments.user'])
            ->where('status', 'published')
            ->findOrFail($id);

        return view('posts.show', compact('post'));
    }

    public function userPosts($userId)
    {
        $posts = Post::where('user_id', $userId)
            ->where('status', 'published')
            ->orderBy('published_at', 'desc')
            ->paginate(10);

        return view('posts.user', compact('posts'));
    }
}
```

**Refactoring Prompt:**
```
Refactor this controller to use Repository pattern:

[paste code above]

Requirements:
- Create PostRepository interface
- Create Eloquent implementation
- Extract all queries to repository
- Use dependency injection
- Keep controllers thin
- Add type hints everywhere

Show:
1. Repository interface
2. Repository implementation
3. Service provider binding
4. Refactored controller
```

**After:**

```php
<?php
// app/Repositories/PostRepositoryInterface.php

namespace App\Repositories;

use App\Models\Post;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface PostRepositoryInterface
{
    public function getPublished(int $perPage = 15): LengthAwarePaginator;

    public function findPublished(int $id): Post;

    public function getByUser(int $userId, int $perPage = 10): LengthAwarePaginator;
}
```

```php
<?php
// app/Repositories/EloquentPostRepository.php

namespace App\Repositories;

use App\Models\Post;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentPostRepository implements PostRepositoryInterface
{
    public function getPublished(int $perPage = 15): LengthAwarePaginator
    {
        return Post::query()
            ->with('author')
            ->where('status', 'published')
            ->latest('published_at')
            ->paginate($perPage);
    }

    public function findPublished(int $id): Post
    {
        return Post::query()
            ->with(['author', 'comments.user'])
            ->where('status', 'published')
            ->findOrFail($id);
    }

    public function getByUser(int $userId, int $perPage = 10): LengthAwarePaginator
    {
        return Post::query()
            ->where('user_id', $userId)
            ->where('status', 'published')
            ->latest('published_at')
            ->paginate($perPage);
    }
}
```

```php
<?php
// app/Providers/RepositoryServiceProvider.php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\PostRepositoryInterface;
use App\Repositories\EloquentPostRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            PostRepositoryInterface::class,
            EloquentPostRepository::class
        );
    }
}
```

```php
<?php
// app/Http/Controllers/PostController.php (Refactored)

namespace App\Http\Controllers;

use App\Repositories\PostRepositoryInterface;
use Illuminate\View\View;

class PostController extends Controller
{
    public function __construct(
        private readonly PostRepositoryInterface $posts
    ) {}

    public function index(): View
    {
        $posts = $this->posts->getPublished();

        return view('posts.index', compact('posts'));
    }

    public function show(int $id): View
    {
        $post = $this->posts->findPublished($id);

        return view('posts.show', compact('post'));
    }

    public function userPosts(int $userId): View
    {
        $posts = $this->posts->getByUser($userId);

        return view('posts.user', compact('posts'));
    }
}
```

**Benefits:**
- ✅ Testable (mock repository interface)
- ✅ Swappable implementations
- ✅ Single source of truth for queries
- ✅ Reusable across controllers/services
- ✅ Cleaner controller code
- ✅ Follows Dependency Inversion Principle

---

## Incremental Refactoring

**Don't refactor everything at once!** Do it incrementally:

### The Strangler Fig Pattern

Named after how the strangler fig tree slowly replaces its host:

```
1. Old code continues working
   ↓
2. Build new implementation alongside
   ↓
3. Gradually route to new implementation
   ↓
4. Test thoroughly at each step
   ↓
5. Remove old code when fully replaced
```

**Example:**

```php
// Step 1: Both exist
public function store(Request $request)
{
    // Old way (still used)
    $this->legacyCreateOrder($request);

    // New way (testing)
    if (config('features.new_order_service')) {
        $this->orderService->create($request->user(), $request->validated());
    }
}

// Step 2: Switch to new, keep old as fallback
public function store(Request $request)
{
    try {
        return $this->orderService->create($request->user(), $request->validated());
    } catch (\Exception $e) {
        Log::warning('New order service failed, using legacy', ['error' => $e]);
        return $this->legacyCreateOrder($request);
    }
}

// Step 3: Remove old entirely
public function store(Request $request)
{
    return $this->orderService->create($request->user(), $request->validated());
}
```

---

## Refactoring Anti-Patterns

### Anti-Pattern 1: Premature Optimization

❌ **Bad:**
```
"This code might be slow someday. Let's refactor it to use caching, queues, and a complex architecture."
```

✅ **Good:**
```
"This code works well for current needs. If it becomes slow, we'll optimize then."
```

**Rule:** Don't optimize without measurement.

---

### Anti-Pattern 2: Resume-Driven Development

❌ **Bad:**
```
"Let's use microservices, event sourcing, CQRS, and DDD because they're trendy!"
```

✅ **Good:**
```
"Our current architecture serves our needs well. If we grow to need these patterns, we'll adopt them."
```

**Rule:** Solve actual problems, not theoretical ones.

---

### Anti-Pattern 3: Refactoring Without Tests

❌ **Bad:**
```php
// No tests exist
// Refactor anyway
// Hope nothing breaks
```

✅ **Good:**
```php
// Write tests first
// Verify they pass
// Refactor
// Verify tests still pass
```

**Rule:** Never refactor without tests.

---

### Anti-Pattern 4: Big Bang Refactoring

❌ **Bad:**
```
"Let's rewrite the entire system in one go!"
```

✅ **Good:**
```
"Let's refactor one module at a time, shipping working code continuously."
```

**Rule:** Small, incremental changes.

---

## AI Refactoring Pitfalls

### Pitfall 1: Over-Engineering

AI might suggest complex patterns for simple code:

```php
// Simple code
public function getFullName()
{
    return $this->first_name . ' ' . $this->last_name;
}

// AI might suggest
class FullNameService
{
    public function __construct(
        private NameFormatter $formatter,
        private NameValidator $validator
    ) {}

    public function format(User $user): string
    {
        $this->validator->validate($user);
        return $this->formatter->format($user->first_name, $user->last_name);
    }
}
```

**When to use complex:**
- Multiple formatting rules
- Internationalization needed
- Complex validation logic

**When to keep simple:**
- Just concatenating strings
- Works fine
- Unlikely to change

**Your judgment matters!**

---

### Pitfall 2: Changing Behavior Subtly

AI might introduce subtle behavior changes:

```php
// Original
public function calculateTotal()
{
    return $this->items->sum('price');
}

// AI refactored
public function calculateTotal(): float
{
    return $this->items->sum(fn($item) => $item->price * $item->quantity);
}
```

Looks good, but behavior changed! Now includes quantity.

**Solution:** Always compare behavior carefully.

---

### Pitfall 3: Breaking Dependencies

```php
// Original
public function process()
{
    $this->validate();
    $this->save();
    $this->notify();
}

// AI refactored
public function process()
{
    $this->save();      // Order changed!
    $this->validate();  // Might save invalid data
    $this->notify();
}
```

**Solution:** Verify order-dependent operations.

---

## Testing Refactored Code

### Testing Checklist

After refactoring:

**Unit Tests:**
- [ ] All existing tests pass
- [ ] Added tests for new methods
- [ ] Edge cases covered
- [ ] Error cases tested

**Integration Tests:**
- [ ] Feature tests pass
- [ ] Database interactions work
- [ ] External services work

**Manual Testing:**
- [ ] Happy path works
- [ ] Edge cases work
- [ ] Error handling works
- [ ] UI functions correctly

**Performance Testing:**
- [ ] No performance regression
- [ ] Improvements measured (if optimization)

**Behavior Verification:**
- [ ] Output identical to original
- [ ] Side effects identical
- [ ] Error messages same

---

## Exercises

### Exercise 1: Extract Service (60 minutes)

Take a fat controller from your project:
1. Write tests to lock in behavior
2. Ask AI to extract service layer
3. Review and apply changes
4. Verify all tests still pass
5. Compare: before vs. after

### Exercise 2: Apply Design Pattern (90 minutes)

Find code that could use a pattern:
1. Identify the pattern needed
2. Ask AI to apply it
3. Understand the transformation
4. Test thoroughly
5. Document the improvement

### Exercise 3: Modernization (45 minutes)

Find old-style code in your projects:
1. Ask AI to modernize to PHP 8.2/Laravel 11
2. Review each change
3. Understand new features used
4. Apply and test
5. Note improvements

---

## Summary

**Key Takeaways:**

1. **Refactoring is Risky**
   - Always have tests first
   - Small incremental changes
   - Verify behavior unchanged
   - Never refactor under pressure

2. **AI Accelerates Refactoring**
   - Suggests patterns quickly
   - Shows modern syntax
   - Identifies code smells
   - But needs human oversight

3. **Your Judgment Essential**
   - Don't over-engineer
   - Don't blindly apply patterns
   - Understand each change
   - Consider context

4. **The Safe Process**
   - Backup code
   - Write/run tests
   - Refactor incrementally
   - Test after each change
   - Commit frequently

5. **When to Refactor**
   - Code smells present
   - Hard to maintain
   - Before adding features
   - Performance issues
   - NOT just for the sake of it

In the next lesson, we'll explore **learning with AI** - how to use AI to learn new concepts, frameworks, and patterns effectively.

---

**Next Lesson**: [07-learning-ai.md](./07-learning-ai.md) - Learn new concepts faster with AI assistance.
