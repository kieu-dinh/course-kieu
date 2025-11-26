# Lesson 06 - Accessors and Mutators

## The Data Transformation Problem

With PDO, transforming data required manual work everywhere you used it:

```php
// PDO: Manual formatting everywhere
$stmt = $pdo->query("SELECT * FROM users");
$users = $stmt->fetchAll();

foreach ($users as $user) {
    // Format name every time you use it
    echo ucwords(strtolower($user['name']));

    // Format date every time
    echo date('M d, Y', strtotime($user['created_at']));

    // Format price every time
    echo '$' . number_format($user['balance'], 2);
}
```

**With Eloquent accessors**, formatting happens automatically:

```php
// Eloquent: Automatic formatting
$users = User::all();

foreach ($users as $user) {
    echo $user->formatted_name;     // "John Doe"
    echo $user->created_at_human;   // "Jan 15, 2024"
    echo $user->formatted_balance;  // "$1,234.56"
}
```

---

## What are Accessors and Mutators?

**Accessors** (getters) transform attribute values when you **retrieve** them:
- Format dates
- Calculate values
- Combine fields
- Decrypt data

**Mutators** (setters) transform attribute values when you **set** them:
- Clean input
- Hash passwords
- Format phone numbers
- Encrypt data

Think of them as automatic formatters that run every time you access or set data.

---

## Accessors: Transforming Retrieved Data

### Defining an Accessor (Laravel 9+)

Use PHP 8 attributes:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    /**
     * Get the user's full name
     */
    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn () => "{$this->first_name} {$this->last_name}"
        );
    }
}
```

**Usage:**

```php
$user = User::find(1);
echo $user->full_name;  // "John Doe"
```

**No column `full_name` exists in database!** It's calculated on-the-fly.

### Accessor Examples

**Format name:**

```php
protected function name(): Attribute
{
    return Attribute::make(
        get: fn (string $value) => ucwords(strtolower($value))
    );
}

// Database: "JOHN DOE"
// Accessor returns: "John Doe"
```

**Format currency:**

```php
protected function formattedPrice(): Attribute
{
    return Attribute::make(
        get: fn () => '$' . number_format($this->price, 2)
    );
}

// Usage
echo $product->formatted_price;  // "$99.99"
```

**Calculate age from birthdate:**

```php
protected function age(): Attribute
{
    return Attribute::make(
        get: fn () => $this->birthdate->age
    );
}

// Usage
echo $user->age;  // 25
```

**Format date:**

```php
protected function createdAtHuman(): Attribute
{
    return Attribute::make(
        get: fn () => $this->created_at->format('M d, Y')
    );
}

// Usage
echo $post->created_at_human;  // "Jan 15, 2024"
```

**Combine address fields:**

```php
protected function fullAddress(): Attribute
{
    return Attribute::make(
        get: fn () => "{$this->address}, {$this->city}, {$this->state} {$this->zip}"
    );
}

// Usage
echo $user->full_address;  // "123 Main St, New York, NY 10001"
```

**Boolean to text:**

```php
protected function statusText(): Attribute
{
    return Attribute::make(
        get: fn () => $this->is_active ? 'Active' : 'Inactive'
    );
}

// Usage
echo $user->status_text;  // "Active"
```

---

## Mutators: Transforming Data Before Saving

### Defining a Mutator

```php
protected function name(): Attribute
{
    return Attribute::make(
        get: fn (string $value) => ucwords($value),
        set: fn (string $value) => strtolower($value)
    );
}
```

**Now:**
- When saving: automatically lowercased
- When retrieving: automatically capitalized

```php
$user = new User();
$user->name = 'JOHN DOE';  // Mutator converts to "john doe" before saving
$user->save();

// Database: "john doe"
// Accessor returns: "John Doe"
```

### Mutator Examples

**Clean phone number:**

```php
protected function phone(): Attribute
{
    return Attribute::make(
        get: fn (string $value) => $value,
        set: fn (string $value) => preg_replace('/[^0-9]/', '', $value)
    );
}

$user->phone = '(555) 123-4567';  // Saves as "5551234567"
```

**Hash password:**

```php
use Illuminate\Support\Facades\Hash;

protected function password(): Attribute
{
    return Attribute::make(
        set: fn (string $value) => Hash::make($value)
    );
}

$user->password = 'secret123';  // Automatically hashed before saving
```

**Format currency before saving:**

```php
protected function price(): Attribute
{
    return Attribute::make(
        get: fn ($value) => $value / 100,  // Store in cents
        set: fn ($value) => $value * 100   // Convert to cents
    );
}

$product->price = 99.99;  // Saves as 9999 (cents)
echo $product->price;     // 99.99
```

**Trim whitespace:**

```php
protected function name(): Attribute
{
    return Attribute::make(
        set: fn (string $value) => trim($value)
    );
}

$user->name = '  John Doe  ';  // Saves as "John Doe"
```

**Encrypt sensitive data:**

```php
use Illuminate\Support\Facades\Crypt;

protected function ssn(): Attribute
{
    return Attribute::make(
        get: fn ($value) => Crypt::decryptString($value),
        set: fn ($value) => Crypt::encryptString($value)
    );
}

$user->ssn = '123-45-6789';  // Encrypted in database
echo $user->ssn;             // Decrypted when accessed
```

---

## Old Accessor/Mutator Syntax (Laravel 8 and earlier)

If you see older code or need to support Laravel 8:

### Old Accessor Syntax

```php
// Old way (still works)
public function getFullNameAttribute()
{
    return "{$this->first_name} {$this->last_name}";
}

// New way (Laravel 9+)
protected function fullName(): Attribute
{
    return Attribute::make(
        get: fn () => "{$this->first_name} {$this->last_name}"
    );
}
```

### Old Mutator Syntax

```php
// Old way
public function setNameAttribute($value)
{
    $this->attributes['name'] = strtolower($value);
}

// New way
protected function name(): Attribute
{
    return Attribute::make(
        set: fn ($value) => strtolower($value)
    );
}
```

**We'll use the new syntax in this course.**

---

## Comparing PDO and Eloquent

### PDO Approach

Create helper functions:

```php
function formatUserName($name) {
    return ucwords(strtolower($name));
}

function formatPrice($price) {
    return '$' . number_format($price / 100, 2);
}

// Use everywhere
$stmt = $pdo->query("SELECT * FROM users");
$users = $stmt->fetchAll();

foreach ($users as $user) {
    echo formatUserName($user['name']);
}

$stmt = $pdo->query("SELECT * FROM products");
$products = $stmt->fetchAll();

foreach ($products as $product) {
    echo formatPrice($product['price']);
}
```

**Problems:**
- Must remember to call functions
- Functions called in views/controllers
- Easy to forget
- Inconsistent usage

### Eloquent Approach

Define once in model, automatic everywhere:

```php
class User extends Model
{
    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => ucwords(strtolower($value))
        );
    }
}

class Product extends Model
{
    protected function formattedPrice(): Attribute
    {
        return Attribute::make(
            get: fn () => '$' . number_format($this->price / 100, 2)
        );
    }
}

// Automatic formatting everywhere
$users = User::all();
foreach ($users as $user) {
    echo $user->name;  // Automatically formatted
}

$products = Product::all();
foreach ($products as $product) {
    echo $product->formatted_price;  // Automatically formatted
}
```

**Much better!**

---

## Real-World Examples

### User Model

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable
{
    protected $fillable = ['first_name', 'last_name', 'email', 'password'];

    /**
     * Full name (accessor only)
     */
    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn () => "{$this->first_name} {$this->last_name}"
        );
    }

    /**
     * Initials
     */
    protected function initials(): Attribute
    {
        return Attribute::make(
            get: fn () => strtoupper(
                substr($this->first_name, 0, 1) .
                substr($this->last_name, 0, 1)
            )
        );
    }

    /**
     * Hash password when setting
     */
    protected function password(): Attribute
    {
        return Attribute::make(
            set: fn (string $value) => Hash::make($value)
        );
    }

    /**
     * Format name properly
     */
    protected function firstName(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => ucfirst($value),
            set: fn (string $value) => strtolower(trim($value))
        );
    }

    protected function lastName(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => ucfirst($value),
            set: fn (string $value) => strtolower(trim($value))
        );
    }

    /**
     * Gravatar URL
     */
    protected function avatarUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => 'https://www.gravatar.com/avatar/' .
                          md5(strtolower($this->email)) . '?s=200'
        );
    }
}
```

**Usage:**

```php
$user = new User([
    'first_name' => '  JOHN  ',    // Saved as "john"
    'last_name' => '  DOE  ',      // Saved as "doe"
    'email' => 'john@example.com',
    'password' => 'secret123'      // Automatically hashed
]);
$user->save();

// Automatic formatting
echo $user->first_name;   // "John" (capitalized)
echo $user->last_name;    // "Doe"
echo $user->full_name;    // "John Doe"
echo $user->initials;     // "JD"
echo $user->avatar_url;   // Gravatar URL

// Password is hashed
$user->password === 'secret123';  // false (it's hashed)
```

### Product Model

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = ['name', 'price', 'stock'];

    /**
     * Store price in cents, display in dollars
     */
    protected function price(): Attribute
    {
        return Attribute::make(
            get: fn (int $value) => $value / 100,
            set: fn (float $value) => $value * 100
        );
    }

    /**
     * Formatted price with currency
     */
    protected function formattedPrice(): Attribute
    {
        return Attribute::make(
            get: fn () => '$' . number_format($this->price, 2)
        );
    }

    /**
     * Stock status text
     */
    protected function stockStatus(): Attribute
    {
        return Attribute::make(
            get: fn () => match(true) {
                $this->stock === 0 => 'Out of Stock',
                $this->stock < 10 => 'Low Stock',
                default => 'In Stock'
            }
        );
    }

    /**
     * Is in stock boolean
     */
    protected function inStock(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->stock > 0
        );
    }

    /**
     * URL-friendly slug from name
     */
    protected function slug(): Attribute
    {
        return Attribute::make(
            get: fn () => \Str::slug($this->name)
        );
    }
}
```

**Usage:**

```php
$product = Product::create([
    'name' => 'Laptop Pro',
    'price' => 999.99,    // Stored as 99999 cents
    'stock' => 5
]);

echo $product->price;            // 999.99
echo $product->formatted_price;  // "$999.99"
echo $product->stock_status;     // "Low Stock"
echo $product->in_stock;         // true
echo $product->slug;             // "laptop-pro"
```

### Post Model

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Post extends Model
{
    protected $fillable = ['title', 'content', 'user_id'];

    /**
     * Auto-generate slug from title
     */
    protected function slug(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ?? Str::slug($this->title),
            set: fn ($value) => Str::slug($value)
        );
    }

    /**
     * Excerpt from content
     */
    protected function excerpt(): Attribute
    {
        return Attribute::make(
            get: fn () => Str::limit(strip_tags($this->content), 150)
        );
    }

    /**
     * Reading time in minutes
     */
    protected function readingTime(): Attribute
    {
        return Attribute::make(
            get: fn () => ceil(str_word_count($this->content) / 200)
        );
    }

    /**
     * Formatted reading time
     */
    protected function readingTimeText(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->reading_time . ' min read'
        );
    }

    /**
     * Published date in human format
     */
    protected function publishedAtHuman(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->published_at?->format('M d, Y')
        );
    }

    /**
     * Published date relative
     */
    protected function publishedAgo(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->published_at?->diffForHumans()
        );
    }
}
```

**Usage:**

```php
$post = Post::create([
    'title' => 'Getting Started with Laravel',
    'content' => str_repeat('Laravel is amazing. ', 200),
    'user_id' => 1
]);

echo $post->slug;              // "getting-started-with-laravel"
echo $post->excerpt;           // First 150 chars
echo $post->reading_time;      // 2
echo $post->reading_time_text; // "2 min read"
echo $post->published_ago;     // "2 days ago"
```

---

## Combining with Casting

Accessors/mutators work great with casting:

```php
class Post extends Model
{
    protected $casts = [
        'published_at' => 'datetime',
        'is_featured' => 'boolean',
        'metadata' => 'array',
    ];

    // Now you can use Carbon methods in accessor
    protected function publishedAgo(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->published_at->diffForHumans()
        );
    }

    // And boolean checks
    protected function statusBadge(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->is_featured
                ? '<span class="badge-featured">Featured</span>'
                : '<span class="badge-normal">Normal</span>'
        );
    }
}
```

---

## Accessor/Mutator Performance

### Be Careful with Heavy Computations

```php
// Bad - runs expensive operation every access
protected function relatedPosts(): Attribute
{
    return Attribute::make(
        get: fn () => Post::where('category_id', $this->category_id)
                          ->where('id', '!=', $this->id)
                          ->get()
    );
}

// Good - use a relationship instead
public function relatedPosts()
{
    return $this->hasMany(Post::class, 'category_id', 'category_id')
                ->where('id', '!=', $this->id);
}
```

### Cache Expensive Calculations

```php
protected function complexCalculation(): Attribute
{
    return Attribute::make(
        get: function () {
            if (!isset($this->attributes['cached_calculation'])) {
                // Expensive calculation
                $result = $this->performExpensiveCalculation();
                $this->attributes['cached_calculation'] = $result;
            }
            return $this->attributes['cached_calculation'];
        }
    );
}
```

---

## Practice Exercises

### Exercise 1: User Profile

Create a `User` model with:

1. `full_name` accessor (first_name + last_name)
2. `initials` accessor (e.g., "JD")
3. `avatar_url` accessor (Gravatar)
4. `member_since` accessor (e.g., "Member since Jan 2024")
5. `password` mutator (hash before saving)
6. `email` mutator (lowercase and trim)

### Exercise 2: E-commerce Product

Create a `Product` model with:

1. `price` mutator/accessor (store in cents, display in dollars)
2. `formatted_price` accessor (e.g., "$99.99")
3. `discount_price` accessor (apply discount percentage)
4. `final_price` accessor (price after discount)
5. `stock_status` accessor (In Stock / Low Stock / Out of Stock)
6. `sku` mutator (uppercase and trim)

### Exercise 3: Blog Post

Create a `Post` model with:

1. `slug` accessor (auto-generate from title)
2. `excerpt` accessor (first 200 chars of content)
3. `reading_time` accessor (calculate from word count)
4. `published_date` accessor (format: "Jan 15, 2024")
5. `title` mutator (trim and capitalize words)
6. `content` mutator (clean HTML)

---

## Common Mistakes

### 1. Forgetting Return Statement

```php
// Wrong - no return
protected function fullName(): Attribute
{
    Attribute::make(
        get: fn () => "{$this->first_name} {$this->last_name}"
    );
}

// Right
protected function fullName(): Attribute
{
    return Attribute::make(
        get: fn () => "{$this->first_name} {$this->last_name}"
    );
}
```

### 2. Wrong Method Name Format

```php
// Wrong - snake_case method name
protected function full_name(): Attribute
{
    return Attribute::make(
        get: fn () => "{$this->first_name} {$this->last_name}"
    );
}

// Right - camelCase method name, access as snake_case
protected function fullName(): Attribute
{
    return Attribute::make(
        get: fn () => "{$this->first_name} {$this->last_name}"
    );
}

// Usage
echo $user->full_name;  // Automatically converts
```

### 3. Modifying $value Parameter in Get

```php
// Wrong - trying to modify $value
protected function price(): Attribute
{
    return Attribute::make(
        get: fn ($value) => $value = $value / 100  // Assignment does nothing
    );
}

// Right - return the value
protected function price(): Attribute
{
    return Attribute::make(
        get: fn ($value) => $value / 100
    );
}
```

### 4. N+1 in Accessors

```php
// Bad - causes N+1 when looping through users
protected function postCount(): Attribute
{
    return Attribute::make(
        get: fn () => $this->posts()->count()  // Query per user!
    );
}

// Good - use withCount instead
User::withCount('posts')->get();
echo $user->posts_count;
```

---

## Quick Reference

```php
// Accessor (get)
protected function fullName(): Attribute
{
    return Attribute::make(
        get: fn () => "{$this->first_name} {$this->last_name}"
    );
}

// Mutator (set)
protected function name(): Attribute
{
    return Attribute::make(
        set: fn ($value) => strtolower($value)
    );
}

// Both accessor and mutator
protected function price(): Attribute
{
    return Attribute::make(
        get: fn ($value) => $value / 100,
        set: fn ($value) => $value * 100
    );
}

// Usage
echo $model->full_name;  // Uses accessor
$model->name = 'JOHN';   // Uses mutator
```

---

## What's Next?

You can now transform data automatically with accessors and mutators! But there's one more critical security topic to cover.

**In the next lesson**, you'll learn about **Mass Assignment Protection**:
- Understanding the mass assignment vulnerability
- Using $fillable and $guarded properly
- When to use which approach
- Real-world security scenarios
- Best practices for production apps

This is crucial for securing your Laravel applications!

---

## Key Takeaways

1. **Accessors transform on retrieval** - Automatic formatting when accessing
2. **Mutators transform on save** - Automatic cleaning/hashing when setting
3. **Method name = camelCase** - Access as snake_case
4. **Virtual attributes** - Create attributes not in database
5. **Combine with casts** - Use Carbon, arrays, booleans
6. **Better than PDO helpers** - Automatic, consistent, centralized
7. **Watch performance** - Don't run queries in accessors
8. **Laravel 9+ syntax** - Use Attribute::make() with arrow functions

---

**Next Lesson:** [07 - Mass Assignment and Fillable](./07-mass-assignment.md)
