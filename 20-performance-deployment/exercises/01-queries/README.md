# Exercise 20.1 - Query Optimization

## Objective

Learn to identify and optimize database queries for better performance.

---

## Task

Profile and optimize database queries in your application.

### Requirements

1. **Query Analysis**
   - Use Laravel Debugbar to identify slow queries
   - Check for N+1 problems
   - Analyze query count and execution time
   - Identify missing indexes

2. **N+1 Problem**
   - Understand what N+1 is
   - Use eager loading to fix it
   - Optimize relationship queries

3. **Indexing**
   - Create database indexes for frequently queried fields
   - Add composite indexes where appropriate
   - Test query performance before/after indexes

4. **Query Optimization**
   - Use select() to fetch only needed columns
   - Use limit() and pagination appropriately
   - Use database functions for calculations

5. **Caching Queries**
   - Cache expensive queries
   - Use remember() method
   - Set appropriate cache duration

---

## Setup

```bash
# Install Laravel Debugbar for development
composer require barryvdh/laravel-debugbar --dev

# Create optimization test file
php artisan make:test QueryOptimizationTest
```

---

## Example: N+1 Problem

### Bad: N+1 Queries

```php
// This causes 1 + N queries (1 for items + N for each author)
class ItemController extends Controller
{
    public function index()
    {
        $items = Item::all(); // 1 query

        foreach ($items as $item) {
            echo $item->author->name; // 1 query per item (N queries)
        }
        // Total: 1 + N queries
    }
}
```

**Debug Output:**
```
Query 1: SELECT * FROM items
Query 2: SELECT * FROM authors WHERE id = ?
Query 3: SELECT * FROM authors WHERE id = ?
Query 4: SELECT * FROM authors WHERE id = ?
...
Total: 101 queries for 100 items
```

### Good: Eager Loading

```php
class ItemController extends Controller
{
    public function index()
    {
        // Eager load relationships
        $items = Item::with('author')->get(); // 2 queries total

        foreach ($items as $item) {
            echo $item->author->name;
        }
        // Total: 2 queries
    }
}
```

**Debug Output:**
```
Query 1: SELECT * FROM items
Query 2: SELECT * FROM authors WHERE id IN (?, ?, ?, ...)
Total: 2 queries
```

---

## Optimization Techniques

### 1. Eager Loading

```php
// Load single relationship
$items = Item::with('author')->get();

// Load multiple relationships
$items = Item::with('author', 'category', 'tags')->get();

// Load nested relationships
$items = Item::with('author.profile', 'category.parent')->get();

// Load with constraints
$items = Item::with(['reviews' => function($query) {
    $query->where('rating', '>=', 4)->orderBy('created_at', 'desc');
}])->get();
```

### 2. Selective Columns

```php
// Only fetch needed columns
$items = Item::select('id', 'name', 'price')->get();

// With relationships
$items = Item::select('id', 'name', 'author_id')
    ->with(['author' => function($query) {
        $query->select('id', 'name');
    }])
    ->get();
```

### 3. Chunking Large Datasets

```php
// Process large query without loading all in memory
Item::chunk(100, function($items) {
    foreach ($items as $item) {
        $item->process();
    }
});

// Same with eager loading
Item::with('author')->chunk(100, function($items) {
    // Process 100 items at a time
});
```

### 4. Lazy Loading Prevention

```php
// Bad - allows lazy loading
class Item extends Model
{
    protected $fillable = ['name', 'author_id'];
}

// Good - prevent lazy loading in production
class Item extends Model
{
    protected $fillable = ['name', 'author_id'];

    protected $with = ['author']; // Always load author

    // Or use preventLazyLoading()
    protected static function boot()
    {
        parent::boot();

        if (app()->isProduction()) {
            Model::preventLazyLoading();
        }
    }
}
```

### 5. Database Indexes

```php
// Migration with indexes
Schema::create('items', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('slug')->unique(); // Unique index
    $table->foreignId('author_id')->constrained();
    $table->string('status')->default('active');
    $table->timestamps();

    // Add indexes
    $table->index('author_id');
    $table->index('status');
    $table->index(['author_id', 'status']); // Composite index
    $table->fullText('name'); // Full text search
});

// Or add to existing table
Schema::table('items', function (Blueprint $table) {
    $table->index('created_at');
    $table->index('category_id');
});
```

### 6. Query Caching

```php
// Cache query results
$items = Cache::remember('items.all', 3600, function() {
    return Item::with('author')->get();
});

// Cache with dynamic key
$items = Cache::remember("items.user.{$userId}", 1800, function() use ($userId) {
    return Item::where('user_id', $userId)->get();
});

// Use query model caching with package
$items = Item::query()
    ->with('author')
    ->cache(3600) // Requires query-cache package
    ->get();
```

---

## Testing Query Performance

```php
// tests/Feature/QueryOptimizationTest.php
namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class QueryOptimizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_items_are_eager_loaded()
    {
        // Create test data
        $author = User::factory()->create();
        Item::factory(10)->for($author)->create();

        // Reset query count
        DB::flushQueryLog();
        DB::enableQueryLog();

        // Test with eager loading
        $items = Item::with('author')->get();
        foreach ($items as $item) {
            $item->author->name;
        }

        // Count queries
        $queries = DB::getQueryLog();
        $this->assertLessThanOrEqual(2, count($queries));
    }

    public function test_selective_columns_reduce_data_transfer()
    {
        Item::factory(100)->create();

        DB::flushQueryLog();
        DB::enableQueryLog();

        $items = Item::select('id', 'name')->get();

        $queries = DB::getQueryLog();
        $this->assertCount(1, $queries);
    }

    public function test_pagination_reduces_memory()
    {
        Item::factory(1000)->create();

        DB::flushQueryLog();
        DB::enableQueryLog();

        $paginated = Item::paginate(15); // Only fetches 15
        $all = Item::all(); // Fetches all 1000

        $this->assertEquals(15, count($paginated));
        $this->assertEquals(1000, count($all));
    }
}
```

---

## Optimization Checklist

- [ ] Identify N+1 problems
- [ ] Implement eager loading
- [ ] Add database indexes
- [ ] Use selective column selection
- [ ] Implement query caching
- [ ] Paginate large results
- [ ] Write performance tests
- [ ] Document query patterns
- [ ] Monitor query logs

---

## Before and After Metrics

**Before Optimization:**
- 150 queries on homepage
- 2.5 seconds load time
- 25MB memory usage

**After Optimization:**
- 8 queries on homepage
- 0.3 seconds load time
- 5MB memory usage

---

## Bonus Challenges

1. Create a query analyzer command
2. Log slow queries to database
3. Set up alerts for N+1 patterns
4. Create database optimization guide for team
5. Implement automatic index suggestions
6. Monitor query performance over time

---

## Solution Check

Run tests:
```bash
php artisan test tests/Feature/QueryOptimizationTest.php
```

Verify with Debugbar:
- Check Query count (should be minimal)
- Verify eager loading is working
- Monitor execution time
- Check memory usage
