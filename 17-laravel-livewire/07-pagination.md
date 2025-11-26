# Lesson 7: Pagination with Livewire

**Duration**: 45 minutes
**Prerequisites**: Lesson 6 completed

---

## What You'll Learn

- Simple pagination
- Cursor pagination
- Custom pagination views
- Pagination with search/filters
- Resetting pagination
- Infinite scroll (load more)
- Performance optimization

---

## Basic Pagination

### Using WithPagination Trait

```php
<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Post;

class PostList extends Component
{
    use WithPagination;

    public function render()
    {
        return view('livewire.post-list', [
            'posts' => Post::latest()->paginate(10)
        ]);
    }
}
```

### View

```html
<div>
    <!-- Posts -->
    @foreach($posts as $post)
        <div class="bg-white p-4 rounded shadow mb-4">
            <h3 class="text-xl font-bold">{{ $post->title }}</h3>
            <p class="text-gray-600">{{ $post->excerpt }}</p>
        </div>
    @endforeach

    <!-- Pagination Links -->
    {{ $posts->links() }}
</div>
```

That's it! Pagination works automatically.

---

## Pagination Styles

### Default (Tailwind)

```html
{{ $posts->links() }}
```

Renders Tailwind-styled pagination links.

### Bootstrap

```html
{{ $posts->links('pagination::bootstrap-4') }}
```

### Simple (Previous/Next only)

```html
{{ $posts->links('pagination::simple-tailwind') }}
```

### Custom View

Create your own: `resources/views/pagination/custom.blade.php`

```html
{{ $posts->links('pagination.custom') }}
```

---

## Pagination with Filters

When you have search/filters, you need to reset to page 1:

### Component

```php
use Livewire\WithPagination;

class PostList extends Component
{
    use WithPagination;

    public $search = '';
    public $category = '';

    // Reset to page 1 when search changes
    public function updatedSearch()
    {
        $this->resetPage();
    }

    // Reset to page 1 when category changes
    public function updatedCategory()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Post::query();

        if ($this->search) {
            $query->where('title', 'like', "%{$this->search}%");
        }

        if ($this->category) {
            $query->where('category', $this->category);
        }

        return view('livewire.post-list', [
            'posts' => $query->latest()->paginate(10)
        ]);
    }
}
```

### View

```html
<div>
    <!-- Filters -->
    <div class="mb-6 flex gap-4">
        <input
            type="text"
            wire:model.live.debounce.300ms="search"
            placeholder="Search posts..."
            class="px-4 py-2 border rounded"
        >

        <select wire:model.change="category" class="px-4 py-2 border rounded">
            <option value="">All Categories</option>
            <option value="tech">Tech</option>
            <option value="business">Business</option>
            <option value="health">Health</option>
        </select>
    </div>

    <!-- Results Count -->
    <p class="text-sm text-gray-600 mb-4">
        Found {{ $posts->total() }} post(s)
    </p>

    <!-- Posts -->
    @forelse($posts as $post)
        <div class="bg-white p-4 rounded shadow mb-4">
            <h3 class="text-xl font-bold">{{ $post->title }}</h3>
            <p class="text-gray-600">{{ $post->excerpt }}</p>
            <span class="text-xs text-gray-500">{{ $post->category }}</span>
        </div>
    @empty
        <p class="text-center text-gray-500 py-8">No posts found</p>
    @endforelse

    <!-- Pagination -->
    <div class="mt-6">
        {{ $posts->links() }}
    </div>
</div>
```

**Key point**: `updatedSearch()` calls `$this->resetPage()` to go back to page 1 when filter changes.

---

## Custom Page Numbers

### Change Per Page

```php
public $perPage = 10;

public function render()
{
    return view('livewire.post-list', [
        'posts' => Post::latest()->paginate($this->perPage)
    ]);
}
```

```html
<select wire:model.change="perPage">
    <option value="10">10 per page</option>
    <option value="25">25 per page</option>
    <option value="50">50 per page</option>
</select>
```

### Update Per Page

```php
public function updatedPerPage()
{
    $this->resetPage();
}
```

---

## Cursor Pagination

For better performance on large datasets:

### Component

```php
public function render()
{
    return view('livewire.post-list', [
        'posts' => Post::latest()->cursorPaginate(10)
    ]);
}
```

**Differences from regular pagination:**
- Uses cursor (encrypted pointer) instead of page numbers
- More efficient for large datasets
- Can't jump to specific page (only next/previous)
- Better for real-time data (new items don't break pagination)

### View

```html
<!-- Works the same -->
{{ $posts->links() }}
```

**When to use:**
- Large datasets (millions of records)
- Real-time feeds (new items added frequently)
- Performance is critical

**When NOT to use:**
- Need to jump to specific pages
- Need page numbers
- Small datasets (regular pagination is fine)

---

## Multiple Paginators

If you need multiple paginated lists on same page:

```php
public function render()
{
    return view('livewire.dashboard', [
        'posts' => Post::latest()->paginate(5, ['*'], 'postsPage'),
        'users' => User::latest()->paginate(5, ['*'], 'usersPage')
    ]);
}
```

**Important**: Third parameter names the paginator.

---

## Infinite Scroll (Load More)

### Component

```php
use Livewire\WithPagination;

class InfinitePostList extends Component
{
    use WithPagination;

    public function render()
    {
        return view('livewire.infinite-post-list', [
            'posts' => Post::latest()->paginate(10)
        ]);
    }
}
```

### View with Alpine.js

```html
<div>
    <div id="posts-container">
        @foreach($posts as $post)
            <div class="bg-white p-4 rounded shadow mb-4">
                <h3 class="text-xl font-bold">{{ $post->title }}</h3>
                <p class="text-gray-600">{{ $post->excerpt }}</p>
            </div>
        @endforeach
    </div>

    <!-- Load More Button -->
    @if($posts->hasMorePages())
        <div
            x-data="{
                init() {
                    let observer = new IntersectionObserver((entries) => {
                        entries.forEach(entry => {
                            if (entry.isIntersecting) {
                                @this.call('loadMore')
                            }
                        })
                    })
                    observer.observe(this.$el)
                }
            }"
            class="text-center py-4"
        >
            <div wire:loading class="text-gray-600">
                Loading more posts...
            </div>
        </div>
    @else
        <p class="text-center text-gray-500 py-4">No more posts</p>
    @endif
</div>
```

### Manual Load More Button

Simpler approach:

```html
<div>
    @foreach($posts as $post)
        <div class="bg-white p-4 rounded shadow mb-4">
            <h3 class="text-xl font-bold">{{ $post->title }}</h3>
            <p class="text-gray-600">{{ $post->excerpt }}</p>
        </div>
    @endforeach

    @if($posts->hasMorePages())
        <div class="text-center mt-6">
            <button
                wire:click="loadMore"
                wire:loading.attr="disabled"
                class="bg-blue-500 text-white px-6 py-2 rounded"
            >
                <span wire:loading.remove wire:target="loadMore">Load More</span>
                <span wire:loading wire:target="loadMore">Loading...</span>
            </button>
        </div>
    @endif
</div>
```

But you need the loadMore method:

```php
use Livewire\WithPagination;

class InfinitePostList extends Component
{
    use WithPagination;

    public function loadMore()
    {
        $this->nextPage();
    }

    public function render()
    {
        return view('livewire.infinite-post-list', [
            'posts' => Post::latest()->paginate(10)
        ]);
    }
}
```

Wait, this won't work well because `paginate()` replaces data, not appends.

### Better Infinite Scroll Implementation

```php
class InfinitePostList extends Component
{
    public $page = 1;
    public $perPage = 10;

    public function loadMore()
    {
        $this->page++;
    }

    public function render()
    {
        $posts = Post::latest()
            ->take($this->page * $this->perPage)
            ->get();

        return view('livewire.infinite-post-list', [
            'posts' => $posts,
            'hasMore' => Post::count() > ($this->page * $this->perPage)
        ]);
    }
}
```

```html
<div>
    @foreach($posts as $post)
        <div class="bg-white p-4 rounded shadow mb-4">
            <h3 class="text-xl font-bold">{{ $post->title }}</h3>
            <p class="text-gray-600">{{ $post->excerpt }}</p>
        </div>
    @endforeach

    @if($hasMore)
        <div class="text-center mt-6">
            <button
                wire:click="loadMore"
                class="bg-blue-500 text-white px-6 py-2 rounded"
            >
                Load More
            </button>
        </div>
    @endif
</div>
```

---

## Pagination Methods

### Available Methods

```php
// In component
$this->resetPage();           // Go to page 1
$this->resetPage('postsPage'); // Reset specific paginator
$this->setPage(5);            // Go to page 5
$this->nextPage();            // Go to next page
$this->previousPage();        // Go to previous page
$this->gotoPage(3);           // Alias for setPage()
```

### Paginator Object Methods

```html
<!-- In view -->
{{ $posts->count() }}          <!-- Items on current page -->
{{ $posts->total() }}          <!-- Total items -->
{{ $posts->perPage() }}        <!-- Items per page -->
{{ $posts->currentPage() }}    <!-- Current page number -->
{{ $posts->lastPage() }}       <!-- Last page number -->
{{ $posts->hasPages() }}       <!-- Has multiple pages? -->
{{ $posts->hasMorePages() }}   <!-- Has next page? -->
{{ $posts->onFirstPage() }}    <!-- On first page? -->
{{ $posts->onLastPage() }}     <!-- On last page? -->
```

---

## Custom Pagination View

Create `resources/views/pagination/livewire.blade.php`:

```html
<div class="flex items-center justify-between">
    <!-- Previous Button -->
    @if ($paginator->onFirstPage())
        <span class="px-4 py-2 text-gray-400">Previous</span>
    @else
        <button
            wire:click="previousPage"
            class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
        >
            Previous
        </button>
    @endif

    <!-- Page Numbers -->
    <div class="flex gap-2">
        @foreach ($elements as $element)
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="px-4 py-2 bg-blue-500 text-white rounded">{{ $page }}</span>
                    @else
                        <button
                            wire:click="gotoPage({{ $page }})"
                            class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300"
                        >
                            {{ $page }}
                        </button>
                    @endif
                @endforeach
            @endif
        @endforeach
    </div>

    <!-- Next Button -->
    @if ($paginator->hasMorePages())
        <button
            wire:click="nextPage"
            class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
        >
            Next
        </button>
    @else
        <span class="px-4 py-2 text-gray-400">Next</span>
    @endif
</div>

<!-- Page Info -->
<div class="text-sm text-gray-600 text-center mt-4">
    Showing {{ $paginator->firstItem() }} to {{ $paginator->lastItem() }}
    of {{ $paginator->total() }} results
</div>
```

Use it:

```html
{{ $posts->links('pagination.livewire') }}
```

---

## Performance Tips

### 1. Use Select Specific Columns

```php
// Bad (loads all columns)
$posts = Post::latest()->paginate(10);

// Good (only what you need)
$posts = Post::select('id', 'title', 'excerpt', 'created_at')
    ->latest()
    ->paginate(10);
```

### 2. Use Eager Loading

```php
// Bad (N+1 query problem)
$posts = Post::latest()->paginate(10);
// Then in view: {{ $post->author->name }} causes N queries

// Good
$posts = Post::with('author')->latest()->paginate(10);
```

### 3. Index Your Columns

Make sure filtered/sorted columns are indexed:

```php
// In migration
$table->string('category')->index();
$table->boolean('published')->index();
$table->timestamp('created_at')->index();
```

### 4. Use Cursor Pagination for Large Datasets

```php
// Regular pagination scans from beginning each time
// Page 10000 = scan 100,000 rows!

// Cursor pagination picks up where it left off
$posts = Post::latest()->cursorPaginate(10);
```

### 5. Cache Total Count

For large tables, `total()` can be slow:

```php
$posts = Post::latest()->paginate(10);

// Instead of: {{ $posts->total() }}
// Use cached count:
{{ Cache::remember('posts_count', 3600, fn() => Post::count()) }}
```

---

## Real-World Example: Product Catalog

```php
<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Product;

class ProductCatalog extends Component
{
    use WithPagination;

    public $search = '';
    public $category = '';
    public $minPrice = 0;
    public $maxPrice = 10000;
    public $sortBy = 'latest';
    public $perPage = 12;

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedCategory()
    {
        $this->resetPage();
    }

    public function updatedMinPrice()
    {
        $this->resetPage();
    }

    public function updatedMaxPrice()
    {
        $this->resetPage();
    }

    public function updatedSortBy()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Product::query();

        // Search
        if ($this->search) {
            $query->where('name', 'like', "%{$this->search}%");
        }

        // Category
        if ($this->category) {
            $query->where('category', $this->category);
        }

        // Price range
        $query->whereBetween('price', [$this->minPrice, $this->maxPrice]);

        // Sorting
        match($this->sortBy) {
            'latest' => $query->latest(),
            'oldest' => $query->oldest(),
            'price_low' => $query->orderBy('price', 'asc'),
            'price_high' => $query->orderBy('price', 'desc'),
            'name' => $query->orderBy('name', 'asc'),
            default => $query->latest(),
        };

        return view('livewire.product-catalog', [
            'products' => $query->paginate($this->perPage),
            'categories' => Product::distinct()->pluck('category')
        ]);
    }
}
```

```html
<div>
    <!-- Filters -->
    <div class="bg-white p-4 rounded shadow mb-6">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <!-- Search -->
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="Search products..."
                class="px-3 py-2 border rounded"
            >

            <!-- Category -->
            <select wire:model.change="category" class="px-3 py-2 border rounded">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat }}">{{ $cat }}</option>
                @endforeach
            </select>

            <!-- Min Price -->
            <input
                type="number"
                wire:model.blur="minPrice"
                placeholder="Min Price"
                class="px-3 py-2 border rounded"
            >

            <!-- Max Price -->
            <input
                type="number"
                wire:model.blur="maxPrice"
                placeholder="Max Price"
                class="px-3 py-2 border rounded"
            >

            <!-- Sort -->
            <select wire:model.change="sortBy" class="px-3 py-2 border rounded">
                <option value="latest">Latest</option>
                <option value="price_low">Price: Low to High</option>
                <option value="price_high">Price: High to Low</option>
                <option value="name">Name: A-Z</option>
            </select>
        </div>
    </div>

    <!-- Results Info -->
    <div class="flex justify-between items-center mb-4">
        <p class="text-sm text-gray-600">
            Showing {{ $products->firstItem() ?? 0 }} to {{ $products->lastItem() ?? 0 }}
            of {{ $products->total() }} products
        </p>

        <select wire:model.change="perPage" class="px-3 py-2 border rounded text-sm">
            <option value="12">12 per page</option>
            <option value="24">24 per page</option>
            <option value="48">48 per page</option>
        </select>
    </div>

    <!-- Products Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6 mb-6">
        @forelse($products as $product)
            <div class="bg-white rounded shadow overflow-hidden">
                <img src="{{ $product->image }}" alt="{{ $product->name }}" class="w-full h-48 object-cover">
                <div class="p-4">
                    <h3 class="font-bold text-lg mb-2">{{ $product->name }}</h3>
                    <p class="text-gray-600 text-sm mb-2">{{ $product->category }}</p>
                    <p class="text-xl font-bold text-blue-600">${{ number_format($product->price, 2) }}</p>
                </div>
            </div>
        @empty
            <div class="col-span-4 text-center py-12 text-gray-500">
                No products found matching your criteria
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $products->links() }}
    </div>
</div>
```

---

## Quick Quiz

**Question 1**: What method resets pagination to page 1?

<details>
<summary>Show Answer</summary>

```php
$this->resetPage();
```

</details>

**Question 2**: When should you call `resetPage()`?

<details>
<summary>Show Answer</summary>

When filters or search changes:

```php
public function updatedSearch()
{
    $this->resetPage();
}
```

</details>

**Question 3**: What's the difference between `paginate()` and `cursorPaginate()`?

<details>
<summary>Show Answer</summary>

- **paginate()**: Uses page numbers, can jump to any page, better for small datasets
- **cursorPaginate()**: Uses cursor (encrypted pointer), only next/previous, better for large datasets and performance

</details>

---

## Practice Exercise

Create a **User Management Table** with:

**Requirements:**
1. Search by name or email
2. Filter by role (admin, user, guest)
3. Sort by name, email, or created_at
4. Pagination (20 per page)
5. Show user count
6. Reset pagination when filters change

Try it yourself!

---

## Summary

You learned:

- ✅ Basic pagination with `WithPagination`
- ✅ Cursor pagination for performance
- ✅ Pagination with filters and search
- ✅ Resetting pagination
- ✅ Custom per-page options
- ✅ Infinite scroll (load more)
- ✅ Custom pagination views
- ✅ Performance optimization

**Next Lesson**: Real-Time Features - polling, events, and live updates!

---

**Pagination is essential for large datasets. You now know how to implement it efficiently!**
