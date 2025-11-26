# 03 - Optimization (Fix N+1 Queries)

## Objective
Identify and fix the N+1 query problem using eager loading and other optimization techniques to improve application performance.

## Prerequisites
- Completed "01-models" and "02-relationships" exercises
- Understanding of database queries and performance impact
- Knowledge of Laravel relationships

## Instructions

### Step 1: Understand the N+1 Problem
The N+1 query problem occurs when:
- 1 query fetches N records
- N additional queries fetch related data for each record

Example of BAD code:

```php
// This executes 1 + N queries (N = number of authors)
$books = Book::all();
foreach ($books as $book) {
    echo $book->author->name; // Executes 1 query per book!
}
```

### Step 2: Create Test Database with Sample Data
Create a seeder:

```bash
php artisan make:seeder BookSeeder
```

In `database/seeders/BookSeeder.php`:

```php
public function run(): void
{
    // Create 10 authors
    $authors = Author::factory()->count(10)->create();

    // Create 100 books for these authors
    Book::factory()
        ->count(100)
        ->recycle($authors)
        ->create();
}
```

Create factories:

```bash
php artisan make:factory AuthorFactory
php artisan make:factory BookFactory
```

In `database/factories/AuthorFactory.php`:

```php
public function definition(): array
{
    return [
        'name' => $this->faker->name(),
        'email' => $this->faker->unique()->safeEmail(),
        'bio' => $this->faker->paragraph(),
    ];
}
```

In `database/factories/BookFactory.php`:

```php
public function definition(): array
{
    return [
        'title' => $this->faker->sentence(3),
        'description' => $this->faker->paragraph(),
        'isbn' => $this->faker->unique()->isbn13(),
        'price' => $this->faker->numberBetween(10, 50),
        'author_id' => Author::factory(),
    ];
}
```

Seed the database:

```bash
php artisan migrate:fresh --seed --seeder=BookSeeder
```

### Step 3: Demonstrate the N+1 Problem
Create a test controller:

```bash
php artisan make:controller OptimizationController
```

In `app/Http/Controllers/OptimizationController.php`:

```php
public function slowQuery()
{
    // BAD: N+1 queries (1 for books + 100 for authors)
    $books = Book::all();

    $results = [];
    foreach ($books as $book) {
        $results[] = [
            'title' => $book->title,
            'author' => $book->author->name, // Executes query!
        ];
    }

    return response()->json([
        'count' => count($results),
        'data' => $results,
    ]);
}
```

Add a debug middleware to count queries:

```bash
php artisan make:middleware QueryLog
```

In `app/Http/Middleware/QueryLog.php`:

```php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;

class QueryLog
{
    public function handle($request, Closure $next)
    {
        $queries = [];

        DB::listen(function (QueryExecuted $query) use (&$queries) {
            $queries[] = $query;
        });

        $response = $next($request);

        // Add query count to response headers
        $response->header('X-Query-Count', count($queries));

        return $response;
    }
}
```

Register in `app/Http/Kernel.php`:

```php
protected $middleware = [
    // ...
    \App\Http\Middleware\QueryLog::class,
];
```

### Step 4: Fix with Eager Loading
In `OptimizationController`:

```php
public function fastQuery()
{
    // GOOD: Only 2 queries (1 for books + 1 for authors)
    $books = Book::with('author')->get();

    $results = [];
    foreach ($books as $book) {
        $results[] = [
            'title' => $book->title,
            'author' => $book->author->name, // No query!
        ];
    }

    return response()->json([
        'count' => count($results),
        'data' => $results,
    ]);
}
```

### Step 5: Multiple Relationships
Create a complex scenario:

```bash
php artisan make:model Publisher -m
php artisan make:model Genre -m
```

Migrations:

```php
// publishers table
Schema::create('publishers', function (Blueprint $table) {
    $table->id();
    $table->string('name')->unique();
    $table->timestamps();
});

// genres table
Schema::create('genres', function (Blueprint $table) {
    $table->id();
    $table->string('name')->unique();
    $table->timestamps();
});

// Add publisher_id to books
Schema::table('books', function (Blueprint $table) {
    $table->foreignId('publisher_id')->constrained();
});

// Create pivot table for many-to-many
Schema::create('book_genre', function (Blueprint $table) {
    $table->foreignId('book_id')->constrained()->onDelete('cascade');
    $table->foreignId('genre_id')->constrained()->onDelete('cascade');
    $table->primary(['book_id', 'genre_id']);
});
```

### Step 6: Define Multiple Relationships
In `Book` model:

```php
public function author(): BelongsTo
{
    return $this->belongsTo(Author::class);
}

public function publisher(): BelongsTo
{
    return $this->belongsTo(Publisher::class);
}

public function genres(): BelongsToMany
{
    return $this->belongsToMany(Genre::class, 'book_genre');
}
```

### Step 7: Eager Load Multiple Relationships
```php
public function optimizedQuery()
{
    // Load author, publisher, and genres with one query
    $books = Book::with('author', 'publisher', 'genres')
        ->get();

    return response()->json([
        'count' => $books->count(),
        'data' => $books->map(fn($book) => [
            'title' => $book->title,
            'author' => $book->author->name,
            'publisher' => $book->publisher->name,
            'genres' => $book->genres->pluck('name'),
        ]),
    ]);
}
```

### Step 8: Nested Eager Loading
If Author has Reviews:

```php
$books = Book::with([
    'author' => function($query) {
        $query->with('reviews');
    },
    'publisher',
    'genres'
])->get();
```

Or simpler:

```php
$books = Book::with('author.reviews', 'publisher', 'genres')->get();
```

### Step 9: Conditional Eager Loading
```php
$books = Book::query()
    ->when(request('include_author'), fn($q) => $q->with('author'))
    ->when(request('include_genres'), fn($q) => $q->with('genres'))
    ->get();
```

### Step 10: Count Optimization
BAD:

```php
$authors = Author::all();
foreach ($authors as $author) {
    echo $author->books()->count(); // N queries!
}
```

GOOD:

```php
$authors = Author::withCount('books')->get();
foreach ($authors as $author) {
    echo $author->books_count; // No queries!
}
```

### Step 11: Query Analysis
Create a testing route:

```bash
php artisan make:command AnalyzeQueries
```

Or test in controller:

```php
use Illuminate\Support\Facades\DB;

public function analyzeQueries()
{
    // Enable query logging
    DB::enableQueryLog();

    // Run your query
    $books = Book::with('author', 'publisher')->get();

    // Get all queries
    $queries = DB::getQueryLog();

    return [
        'query_count' => count($queries),
        'queries' => $queries,
        'time' => collect($queries)->sum('time'),
    ];
}
```

### Step 12: Practical Exercise
Create a view showing books with optimization:

```php
public function index()
{
    // Optimized query
    $books = Book::with('author', 'publisher', 'genres')
        ->latest()
        ->paginate(20);

    return view('books.index', compact('books'));
}
```

In `resources/views/books/index.blade.php`:

```blade
@extends('layout')

@section('content')
    <h1>Books</h1>

    <table>
        <thead>
            <tr>
                <th>Title</th>
                <th>Author</th>
                <th>Publisher</th>
                <th>Genres</th>
                <th>Price</th>
            </tr>
        </thead>
        <tbody>
            @foreach($books as $book)
                <tr>
                    <td>{{ $book->title }}</td>
                    <td>{{ $book->author->name }}</td>
                    <td>{{ $book->publisher->name }}</td>
                    <td>{{ $book->genres->pluck('name')->join(', ') }}</td>
                    <td>${{ number_format($book->price, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{ $books->links() }}
@endsection
```

### Step 13: Benchmark Performance
Compare slow vs fast query:

```php
$start = microtime(true);

// Your slow query
$books = Book::all();
foreach ($books as $book) {
    $book->author->name;
}

$slowTime = microtime(true) - $start;

// Your fast query
$start = microtime(true);
$books = Book::with('author')->get();
foreach ($books as $book) {
    $book->author->name;
}
$fastTime = microtime(true) - $start;

echo "Slow: {$slowTime}s, Fast: {$fastTime}s";
```

## Deliverables
- [ ] N+1 query problem demonstrated
- [ ] Slow query performance measured
- [ ] Eager loading implemented with `with()`
- [ ] Query count verified (compare slow vs optimized)
- [ ] Multiple relationships loaded efficiently
- [ ] withCount() used for counting relationships
- [ ] Views optimized with proper eager loading
- [ ] Performance improvement documented

## Resources
- [Eager Loading](https://laravel.com/docs/11.x/eloquent-relationships#eager-loading)
- [Query Performance](https://laravel.com/docs/11.x/queries#debugging)
- [Database Optimization](https://laravel.com/docs/11.x/database)

## Tips
- Always use `with()` when accessing relationships in loops
- Use `withCount()` instead of `count()` for relationship counts
- Monitor query count with middleware or debugging tools
- Use `dd(DB::getQueryLog())` to inspect queries
- Use Query Builder's `explain()` to understand query execution
- Consider pagination for large result sets
- Use `lazy()` for streaming large datasets
