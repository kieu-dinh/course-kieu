# 02 - Relationships (hasMany, belongsTo)

## Objective
Learn to define and use Eloquent relationships to model complex data structures and maintain referential integrity.

## Prerequisites
- Completed "01-models" exercise
- Understanding of database relationships
- Knowledge of foreign keys

## Instructions

### Step 1: Create Models and Migrations
Create Author and Book models with migrations:

```bash
php artisan make:model Author -m
php artisan make:model Book -m
```

### Step 2: Define Author Migration
Edit `database/migrations/*_create_authors_table.php`:

```php
Schema::create('authors', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('email')->unique();
    $table->text('bio')->nullable();
    $table->timestamps();
});
```

### Step 3: Define Book Migration
Edit `database/migrations/*_create_books_table.php`:

```php
Schema::create('books', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->text('description')->nullable();
    $table->string('isbn')->unique();
    $table->decimal('price', 10, 2);
    $table->foreignId('author_id')->constrained()->onDelete('cascade');
    $table->timestamps();
});
```

Run migrations:

```bash
php artisan migrate
```

### Step 4: Define hasMany Relationship
In `app/Models/Author.php`:

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Author extends Model
{
    protected $fillable = ['name', 'email', 'bio'];

    // Define one-to-many relationship
    public function books(): HasMany
    {
        return $this->hasMany(Book::class);
    }
}
```

### Step 5: Define belongsTo Relationship
In `app/Models/Book.php`:

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Book extends Model
{
    protected $fillable = ['title', 'description', 'isbn', 'price', 'author_id'];

    protected $casts = [
        'price' => 'float',
    ];

    // Define inverse relationship
    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class);
    }
}
```

### Step 6: Test Relationships in Tinker
```bash
php artisan tinker
```

Create test data:

```php
// Create an author
$author = Author::create([
    'name' => 'J.K. Rowling',
    'email' => 'jk@example.com',
    'bio' => 'Famous author',
]);

// Create books for the author
$author->books()->create([
    'title' => 'Harry Potter and the Philosopher\'s Stone',
    'isbn' => '978-0-7475-3269-9',
    'price' => 19.99,
]);

$author->books()->create([
    'title' => 'Harry Potter and the Chamber of Secrets',
    'isbn' => '978-0-7475-3849-3',
    'price' => 19.99,
]);

// Retrieve author's books
$author = Author::find(1);
$books = $author->books; // Collection of books

// Retrieve book's author
$book = Book::find(1);
$author = $book->author; // Single author
```

### Step 7: Query Related Data
Advanced queries with relationships:

```php
// Get author with all books
$author = Author::with('books')->find(1);

// Get all books with their authors
$books = Book::with('author')->get();

// Get authors who have books
$authors = Author::has('books')->get();

// Get authors with more than 2 books
$authors = Author::has('books', '>=', 2)->get();

// Get books by a specific author
$books = Author::find(1)->books;
$books = Book::where('author_id', 1)->get();

// Use a custom query on relationship
$recentBooks = $author->books()
    ->where('price', '>', 20)
    ->orderBy('created_at', 'desc')
    ->get();

// Count books per author
$author = Author::withCount('books')->get();
// Then access: $author->books_count
```

### Step 8: Create a Blog Example
Create Comment model:

```bash
php artisan make:model Comment -m
```

Migration:

```php
Schema::create('comments', function (Blueprint $table) {
    $table->id();
    $table->string('author');
    $table->text('content');
    $table->foreignId('post_id')->constrained()->onDelete('cascade');
    $table->timestamps();
});
```

### Step 9: Define Post and Comment Relationships
In `app/Models/Post.php` (from previous module):

```php
public function comments(): HasMany
{
    return $this->hasMany(Comment::class);
}
```

In `app/Models/Comment.php`:

```php
public function post(): BelongsTo
{
    return $this->belongsTo(Post::class);
}
```

### Step 10: Work with Comments
In Tinker:

```php
// Create comment on a post
$post = Post::find(1);
$post->comments()->create([
    'author' => 'John',
    'content' => 'Great post!',
]);

// Get all comments on a post
$comments = $post->comments;

// Get post from comment
$comment = Comment::find(1);
$post = $comment->post;

// Count comments on post
$count = $post->comments()->count();
```

### Step 11: Create Views
Create `resources/views/authors/show.blade.php`:

```blade
@extends('layout')

@section('title', $author->name)

@section('content')
    <h1>{{ $author->name }}</h1>

    <p>{{ $author->bio }}</p>

    <h2>Books ({{ $author->books->count() }})</h2>

    @forelse($author->books as $book)
        <div class="book">
            <h3>{{ $book->title }}</h3>
            <p>ISBN: {{ $book->isbn }}</p>
            <p>Price: ${{ number_format($book->price, 2) }}</p>
        </div>
    @empty
        <p>No books published yet</p>
    @endforelse
@endsection
```

Create `resources/views/posts/show.blade.php` with comments:

```blade
@extends('layout')

@section('title', $post->title)

@section('content')
    <article>
        <h1>{{ $post->title }}</h1>
        <p>{!! nl2br(e($post->content)) !!}</p>
    </article>

    <section class="comments">
        <h2>Comments ({{ $post->comments->count() }})</h2>

        @forelse($post->comments as $comment)
            <div class="comment">
                <strong>{{ $comment->author }}</strong>
                <p>{{ $comment->content }}</p>
                <small>{{ $comment->created_at->diffForHumans() }}</small>
            </div>
        @empty
            <p>No comments yet</p>
        @endforelse

        <form method="POST" action="/posts/{{ $post->id }}/comments">
            @csrf
            <input type="text" name="author" placeholder="Your name" required>
            <textarea name="content" placeholder="Your comment" required></textarea>
            <button type="submit">Post Comment</button>
        </form>
    </section>
@endsection
```

### Step 12: Eager Loading
Prevent N+1 queries:

```php
// Bad: N+1 query problem
$authors = Author::all();
foreach ($authors as $author) {
    echo $author->books->count(); // Executes query for each author
}

// Good: Eager load
$authors = Author::with('books')->get();
foreach ($authors as $author) {
    echo $author->books->count(); // No additional queries
}
```

### Step 13: Create Controllers
Create `app/Http/Controllers/CommentController.php`:

```php
public function store(Request $request, Post $post)
{
    $validated = $request->validate([
        'author' => 'required|string|max:255',
        'content' => 'required|string',
    ]);

    $post->comments()->create($validated);

    return redirect("/posts/{$post->id}")->with('success', 'Comment added!');
}
```

Add route:

```php
Route::post('/posts/{post}/comments', [CommentController::class, 'store']);
```

## Deliverables
- [ ] Author and Book models with migrations
- [ ] hasMany and belongsTo relationships defined
- [ ] Foreign keys properly configured
- [ ] Relationships tested in Tinker
- [ ] Eager loading implemented (no N+1 queries)
- [ ] Views displaying related data
- [ ] Comments on posts working
- [ ] Form to add comments
- [ ] Cascading deletes tested

## Resources
- [Eloquent Relationships](https://laravel.com/docs/11.x/eloquent-relationships)
- [One to Many](https://laravel.com/docs/11.x/eloquent-relationships#one-to-many)
- [Inverse Relationships](https://laravel.com/docs/11.x/eloquent-relationships#one-to-many-inverse-one-to-one)
- [Eager Loading](https://laravel.com/docs/11.x/eloquent-relationships#eager-loading)

## Tips
- Always eager load related data with `with()` to avoid N+1 queries
- Use `cascade` on foreign keys for automatic cleanup
- Name relationships clearly (singular for belongsTo, plural for hasMany)
- Test relationships in Tinker before implementing views
- Use `@forelse` in views to handle empty relationships gracefully
- Consider lazy loading for rarely accessed relationships
