# Lesson 05 - Query Scopes

## The DRY Problem with Queries

You've learned to write Eloquent queries. But you'll notice patterns repeating:

```php
// In PostController
$posts = Post::where('status', 'published')
    ->where('published_at', '<=', now())
    ->orderBy('created_at', 'desc')
    ->get();

// In HomeController
$latestPosts = Post::where('status', 'published')
    ->where('published_at', '<=', now())
    ->orderBy('created_at', 'desc')
    ->take(5)
    ->get();

// In ApiController
$apiPosts = Post::where('status', 'published')
    ->where('published_at', '<=', now())
    ->orderBy('created_at', 'desc')
    ->paginate(20);
```

**You're repeating the same "published" logic everywhere!**

With PDO, you'd create helper functions or build query strings. With Eloquent, you use **Query Scopes**.

---

## What are Query Scopes?

**Query scopes** are reusable query constraints you define in your models. Think of them as methods you can chain in your queries.

### Before Scopes (Repetitive)

```php
// Repeated everywhere
Post::where('status', 'published')->where('published_at', '<=', now())->get();
```

### After Scopes (DRY)

```php
// Define once in model
public function scopePublished($query)
{
    return $query->where('status', 'published')
                 ->where('published_at', '<=', now());
}

// Use everywhere
Post::published()->get();
Post::published()->latest()->take(5)->get();
Post::published()->where('user_id', 1)->get();
```

**Much cleaner!**

---

## Local Scopes

Local scopes are methods you define in your model that start with `scope`.

### Defining a Local Scope

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Post extends Model
{
    /**
     * Scope to filter published posts
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')
                     ->where('published_at', '<=', now());
    }

    /**
     * Scope to filter draft posts
     */
    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scope to order by most viewed
     */
    public function scopePopular(Builder $query): Builder
    {
        return $query->orderBy('views', 'desc');
    }
}
```

**Key points:**
- Method name: `scope` + PascalCase name
- First parameter: always `$query` (Illuminate\Database\Eloquent\Builder)
- Return: the `$query` object
- Usage: call without `scope` prefix, in camelCase

### Using Local Scopes

```php
// Basic usage
$posts = Post::published()->get();
$drafts = Post::draft()->get();
$popular = Post::popular()->get();

// Chain with other methods
$posts = Post::published()->latest()->take(10)->get();

// Chain multiple scopes
$posts = Post::published()->popular()->get();

// Chain with where clauses
$posts = Post::published()->where('user_id', 1)->get();
```

---

## Dynamic Scopes (With Parameters)

Scopes can accept parameters:

```php
class Post extends Model
{
    /**
     * Scope to filter by status
     */
    public function scopeStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter by user
     */
    public function scopeByUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to filter posts with at least X views
     */
    public function scopeMinViews(Builder $query, int $views): Builder
    {
        return $query->where('views', '>=', $views);
    }

    /**
     * Scope to filter posts created in the last X days
     */
    public function scopeRecent(Builder $query, int $days = 7): Builder
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}
```

**Usage:**

```php
// Pass parameters
$posts = Post::status('published')->get();
$myPosts = Post::byUser(auth()->id())->get();
$viral = Post::minViews(10000)->get();

// With default parameter
$recent = Post::recent()->get();  // Last 7 days
$recent = Post::recent(30)->get();  // Last 30 days

// Chain multiple dynamic scopes
$posts = Post::status('published')
    ->byUser(1)
    ->minViews(100)
    ->recent(14)
    ->get();
```

---

## Practical Scope Examples

### Common Blog Scopes

```php
class Post extends Model
{
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')
                     ->where('published_at', '<=', now());
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeWithCategory(Builder $query, $categoryId): Builder
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where('title', 'like', "%{$term}%")
                     ->orWhere('content', 'like', "%{$term}%");
    }

    public function scopePublishedBetween(Builder $query, $startDate, $endDate): Builder
    {
        return $query->whereBetween('published_at', [$startDate, $endDate]);
    }
}

// Usage
Post::published()->featured()->get();
Post::published()->withCategory(5)->latest()->get();
Post::published()->search('Laravel')->get();
Post::publishedBetween('2024-01-01', '2024-12-31')->get();
```

### E-commerce Product Scopes

```php
class Product extends Model
{
    public function scopeInStock(Builder $query): Builder
    {
        return $query->where('stock', '>', 0);
    }

    public function scopeOutOfStock(Builder $query): Builder
    {
        return $query->where('stock', '<=', 0);
    }

    public function scopePriceBetween(Builder $query, $min, $max): Builder
    {
        return $query->whereBetween('price', [$min, $max]);
    }

    public function scopeOnSale(Builder $query): Builder
    {
        return $query->whereNotNull('sale_price')
                     ->where('sale_price', '<', DB::raw('price'));
    }

    public function scopeByBrand(Builder $query, string $brand): Builder
    {
        return $query->where('brand', $brand);
    }

    public function scopeHighRated(Builder $query, float $minRating = 4.0): Builder
    {
        return $query->where('average_rating', '>=', $minRating);
    }
}

// Usage
Product::inStock()->onSale()->get();
Product::priceBetween(50, 100)->get();
Product::byBrand('Apple')->highRated()->get();
```

### User Management Scopes

```php
class User extends Model
{
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)
                     ->whereNotNull('email_verified_at');
    }

    public function scopeAdmins(Builder $query): Builder
    {
        return $query->where('role', 'admin');
    }

    public function scopeVerified(Builder $query): Builder
    {
        return $query->whereNotNull('email_verified_at');
    }

    public function scopeRegisteredAfter(Builder $query, $date): Builder
    {
        return $query->where('created_at', '>=', $date);
    }

    public function scopeWithMinPosts(Builder $query, int $count): Builder
    {
        return $query->has('posts', '>=', $count);
    }
}

// Usage
User::active()->get();
User::admins()->get();
User::verified()->registeredAfter('2024-01-01')->get();
User::withMinPosts(5)->get();
```

---

## Global Scopes

**Global scopes** automatically apply to ALL queries for a model. Useful for multi-tenancy, soft deletes, etc.

### Defining a Global Scope

```php
<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class PublishedScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $builder->where('status', 'published');
    }
}
```

### Applying Global Scope to Model

```php
<?php

namespace App\Models;

use App\Scopes\PublishedScope;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected static function booted(): void
    {
        static::addGlobalScope(new PublishedScope);
    }
}

// Now ALL queries automatically filter published
$posts = Post::all();  // WHERE status = 'published'
$post = Post::find(1);  // WHERE id = 1 AND status = 'published'
```

### Removing Global Scopes

```php
// Remove specific global scope
$posts = Post::withoutGlobalScope(PublishedScope::class)->get();

// Remove all global scopes
$posts = Post::withoutGlobalScopes()->get();
```

### Anonymous Global Scopes

For simple cases, use anonymous scopes:

```php
class Post extends Model
{
    protected static function booted(): void
    {
        static::addGlobalScope('published', function (Builder $builder) {
            $builder->where('status', 'published');
        });
    }
}

// Remove by name
$posts = Post::withoutGlobalScope('published')->get();
```

### Common Global Scope: Tenant Filtering

```php
class Post extends Model
{
    protected static function booted(): void
    {
        // Only show posts from current organization
        static::addGlobalScope('organization', function (Builder $builder) {
            $builder->where('organization_id', auth()->user()->organization_id);
        });
    }
}
```

**Now users automatically only see their organization's posts!**

---

## Comparing PDO and Eloquent

### PDO Approach

You'd create helper functions:

```php
function getPublishedPosts($pdo) {
    $sql = "SELECT * FROM posts WHERE status = 'published' AND published_at <= NOW()";
    return $pdo->query($sql)->fetchAll();
}

function getPublishedPostsByUser($pdo, $userId) {
    $sql = "SELECT * FROM posts WHERE status = 'published' AND published_at <= NOW() AND user_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

// More functions for every combination...
```

**Problems:**
- Many functions needed
- Can't easily chain conditions
- Harder to compose queries

### Eloquent Approach with Scopes

```php
// Define once
class Post extends Model
{
    public function scopePublished($query) {
        return $query->where('status', 'published')
                     ->where('published_at', '<=', now());
    }

    public function scopeByUser($query, $userId) {
        return $query->where('user_id', $userId);
    }
}

// Use anywhere, chainable
Post::published()->get();
Post::published()->byUser(1)->get();
Post::published()->byUser(1)->latest()->take(10)->get();
```

**Much more flexible!**

---

## Combining Scopes with Relationships

Scopes work great with relationships:

```php
class User extends Model
{
    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}

class Post extends Model
{
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }
}

// Get user's published posts
$user = User::find(1);
$publishedPosts = $user->posts()->published()->get();

// Eager load only published posts
$users = User::with(['posts' => function ($query) {
    $query->published();
}])->get();

// Or simpler with scopes
$users = User::with('posts:published')->get();  // Laravel 10+
```

---

## Advanced: Conditional Scopes

Apply scopes conditionally:

```php
class Post extends Model
{
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['status'] ?? false, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($filters['user_id'] ?? false, function ($query, $userId) {
                $query->where('user_id', $userId);
            })
            ->when($filters['search'] ?? false, function ($query, $search) {
                $query->where('title', 'like', "%{$search}%");
            })
            ->when($filters['min_views'] ?? false, function ($query, $minViews) {
                $query->where('views', '>=', $minViews);
            });
    }
}

// Usage
$filters = request()->only(['status', 'user_id', 'search', 'min_views']);
$posts = Post::filter($filters)->get();
```

**Perfect for search/filter pages!**

---

## Real-World Example: Blog Controller

### Without Scopes (Messy)

```php
class PostController extends Controller
{
    public function index()
    {
        $posts = Post::where('status', 'published')
            ->where('published_at', '<=', now())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('posts.index', compact('posts'));
    }

    public function featured()
    {
        $posts = Post::where('status', 'published')
            ->where('published_at', '<=', now())
            ->where('is_featured', true)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('posts.featured', compact('posts'));
    }

    public function category($categoryId)
    {
        $posts = Post::where('status', 'published')
            ->where('published_at', '<=', now())
            ->where('category_id', $categoryId)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('posts.category', compact('posts'));
    }
}
```

### With Scopes (Clean)

```php
class Post extends Model
{
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
                     ->where('published_at', '<=', now());
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeInCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }
}

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::published()->latest()->paginate(10);
        return view('posts.index', compact('posts'));
    }

    public function featured()
    {
        $posts = Post::published()->featured()->latest()->paginate(10);
        return view('posts.featured', compact('posts'));
    }

    public function category($categoryId)
    {
        $posts = Post::published()->inCategory($categoryId)->latest()->paginate(10);
        return view('posts.category', compact('posts'));
    }
}
```

**Much cleaner and easier to maintain!**

---

## Scope Best Practices

### 1. Name Descriptively

```php
// Good
scopePublished()
scopeRecent()
scopeByUser()

// Bad
scopeFilter()  // Too generic
scopeGet()
scopeQuery()
```

### 2. Keep Scopes Simple

```php
// Good - single responsibility
public function scopePublished($query)
{
    return $query->where('status', 'published');
}

// Bad - doing too much
public function scopePublished($query)
{
    return $query->where('status', 'published')
                 ->where('published_at', '<=', now())
                 ->with('user')
                 ->withCount('comments')
                 ->orderBy('created_at', 'desc');
}
```

### 3. Compose Scopes

```php
// Define atomic scopes
public function scopePublished($query)
{
    return $query->where('status', 'published');
}

public function scopeNotFuture($query)
{
    return $query->where('published_at', '<=', now());
}

// Compose in controller
Post::published()->notFuture()->latest()->get();
```

### 4. Use Type Hints

```php
// Good
public function scopeByUser(Builder $query, int $userId): Builder
{
    return $query->where('user_id', $userId);
}

// Acceptable but less clear
public function scopeByUser($query, $userId)
{
    return $query->where('user_id', $userId);
}
```

---

## Testing Scopes

Scopes are easy to test:

```php
use Tests\TestCase;
use App\Models\Post;

class PostScopeTest extends TestCase
{
    /** @test */
    public function it_filters_published_posts()
    {
        Post::factory()->create(['status' => 'published']);
        Post::factory()->create(['status' => 'draft']);

        $published = Post::published()->get();

        $this->assertCount(1, $published);
        $this->assertEquals('published', $published->first()->status);
    }

    /** @test */
    public function it_filters_recent_posts()
    {
        Post::factory()->create(['created_at' => now()->subDays(5)]);
        Post::factory()->create(['created_at' => now()->subDays(10)]);

        $recent = Post::recent(7)->get();

        $this->assertCount(1, $recent);
    }
}
```

---

## Practice Exercises

### Exercise 1: Product Scopes

Create scopes for a `Product` model:

1. `inStock()` - stock > 0
2. `lowStock($threshold = 10)` - stock <= threshold
3. `priceRange($min, $max)` - between prices
4. `onSale()` - has sale_price
5. `category($categoryId)` - in category
6. `search($term)` - search name and description
7. `popular()` - order by sales_count
8. `newArrivals($days = 30)` - created in last X days

Test all scopes and chain them together.

### Exercise 2: User Management

Create scopes for a `User` model:

1. `active()` - is_active = true
2. `verified()` - email_verified_at not null
3. `admins()` - role = admin
4. `registeredBetween($start, $end)` - created_at between dates
5. `withMinPosts($count)` - has at least X posts
6. `inactive($days)` - last_login_at older than X days
7. `withEmail($domain)` - email ends with domain

Use scopes in a user dashboard.

### Exercise 3: Advanced Filtering

Create a `filter()` scope that accepts an array of filters and applies them conditionally:

```php
$filters = [
    'status' => 'published',
    'category_id' => 5,
    'min_price' => 50,
    'max_price' => 200,
    'search' => 'laptop',
    'in_stock' => true,
];

$products = Product::filter($filters)->paginate(20);
```

---

## Common Mistakes

### 1. Forgetting to Return Query

```php
// Wrong - doesn't return anything
public function scopePublished($query)
{
    $query->where('status', 'published');
}

// Right
public function scopePublished($query)
{
    return $query->where('status', 'published');
}
```

### 2. Wrong Method Name

```php
// Wrong - called publishedScope
public function publishedScope($query)
{
    return $query->where('status', 'published');
}

// Right - called scopePublished
public function scopePublished($query)
{
    return $query->where('status', 'published');
}
```

### 3. Not Using Builder Type Hint

```php
// Good practice
use Illuminate\Database\Eloquent\Builder;

public function scopePublished(Builder $query): Builder
{
    return $query->where('status', 'published');
}
```

### 4. Executing Query in Scope

```php
// Wrong - executes query
public function scopePublished($query)
{
    return $query->where('status', 'published')->get();
}

// Right - returns query builder
public function scopePublished($query)
{
    return $query->where('status', 'published');
}
```

---

## Quick Reference

```php
// Local scope definition
public function scopePublished(Builder $query): Builder
{
    return $query->where('status', 'published');
}

// Dynamic scope with parameter
public function scopeByUser(Builder $query, int $userId): Builder
{
    return $query->where('user_id', $userId);
}

// Usage
Post::published()->get();
Post::byUser(1)->get();
Post::published()->byUser(1)->latest()->get();

// Global scope
static::addGlobalScope(new PublishedScope);
static::addGlobalScope('published', fn($q) => $q->where('status', 'published'));

// Remove global scope
Post::withoutGlobalScope(PublishedScope::class)->get();
Post::withoutGlobalScopes()->get();
```

---

## What's Next?

You can now organize your queries cleanly with scopes! But there's more to learn about customizing how Eloquent works with your data.

**In the next lesson**, you'll learn about **Accessors and Mutators**:
- Transform data when retrieving it (accessors)
- Transform data before saving it (mutators)
- Create virtual attributes
- Format dates, prices, names automatically
- Encrypt/decrypt sensitive data

Accessors and mutators make your models even more powerful!

---

## Key Takeaways

1. **Scopes = reusable query constraints** - Define once, use everywhere
2. **Local scopes** - Method name starts with `scope`, use without prefix
3. **Dynamic scopes** - Accept parameters for flexibility
4. **Global scopes** - Automatically applied to all queries
5. **Chainable** - Combine multiple scopes easily
6. **DRY principle** - Don't repeat query logic
7. **Better than PDO functions** - More flexible and composable
8. **Test scopes independently** - Easy to unit test

---

**Next Lesson:** [06 - Accessors and Mutators](./06-accessors-mutators.md)
