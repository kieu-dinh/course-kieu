# 03 - Live Search

## Objective
Build a real-time search feature using Livewire to demonstrate reactive data filtering without page reloads.

## Prerequisites
- Completed "01-component" and "02-todo" exercises
- Understanding of Livewire properties and lifecycle
- Knowledge of database queries

## Instructions

### Step 1: Create Product Model and Migration
```bash
php artisan make:model Product -m
```

Edit migration:

```php
Schema::create('products', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->text('description')->nullable();
    $table->decimal('price', 10, 2);
    $table->integer('quantity')->default(0);
    $table->string('category');
    $table->timestamps();
});
```

Run migration:

```bash
php artisan migrate
```

### Step 2: Define Product Model
In `app/Models/Product.php`:

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = ['name', 'description', 'price', 'quantity', 'category'];

    protected $casts = [
        'price' => 'float',
    ];
}
```

### Step 3: Seed Products
Create seeder:

```bash
php artisan make:seeder ProductSeeder
```

In `database/seeders/ProductSeeder.php`:

```php
public function run(): void
{
    Product::create([
        'name' => 'Laptop Pro',
        'description' => 'High-performance laptop',
        'price' => 1299.99,
        'quantity' => 5,
        'category' => 'Electronics',
    ]);

    Product::create([
        'name' => 'Wireless Mouse',
        'description' => 'Ergonomic wireless mouse',
        'price' => 29.99,
        'quantity' => 50,
        'category' => 'Electronics',
    ]);

    Product::create([
        'name' => 'USB-C Cable',
        'description' => '2-meter USB-C cable',
        'price' => 12.99,
        'quantity' => 100,
        'category' => 'Cables',
    ]);

    Product::create([
        'name' => 'Office Chair',
        'description' => 'Comfortable office chair',
        'price' => 299.99,
        'quantity' => 20,
        'category' => 'Furniture',
    ]);

    Product::create([
        'name' => 'Desk Lamp',
        'description' => 'LED desk lamp',
        'price' => 49.99,
        'quantity' => 15,
        'category' => 'Lighting',
    ]);
}
```

Seed the database:

```bash
php artisan db:seed --class=ProductSeeder
```

### Step 4: Create Search Component
```bash
php artisan make:livewire ProductSearch
```

In `app/Livewire/ProductSearch.php`:

```php
namespace App\Livewire;

use App\Models\Product;
use Livewire\Component;
use Livewire\Attributes\Validate;

class ProductSearch extends Component
{
    #[Validate('string|max:255')]
    public string $search = '';

    public string $sortBy = 'name'; // name, price, quantity
    public string $sortDirection = 'asc'; // asc, desc
    public string $filterCategory = 'all';

    public function updatedSearch(): void
    {
        // Reset to first page when search changes
        $this->resetPage();
    }

    public function toggleSort(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }

    public function getProductsProperty()
    {
        $query = Product::query();

        // Search by name or description
        if ($this->search) {
            $query->where('name', 'like', "%{$this->search}%")
                  ->orWhere('description', 'like', "%{$this->search}%");
        }

        // Filter by category
        if ($this->filterCategory !== 'all') {
            $query->where('category', $this->filterCategory);
        }

        // Sort results
        $query->orderBy($this->sortBy, $this->sortDirection);

        return $query->get();
    }

    public function getCategoriesProperty()
    {
        return Product::distinct()
            ->pluck('category')
            ->sort()
            ->toArray();
    }

    public function render()
    {
        return view('livewire.product-search', [
            'products' => $this->products,
            'categories' => $this->categories,
            'totalResults' => count($this->products),
        ]);
    }
}
```

### Step 5: Create Search View
In `resources/views/livewire/product-search.blade.php`:

```blade
<div class="w-full max-w-6xl mx-auto p-6">
    <h1 class="text-3xl font-bold mb-6">Product Search</h1>

    {{-- Search Bar --}}
    <div class="mb-6 p-4 bg-white rounded shadow">
        <label for="search" class="block font-bold mb-2">Search Products</label>
        <input type="text"
               id="search"
               wire:model.live="search"
               placeholder="Search by name or description..."
               class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
        <p class="text-sm text-gray-600 mt-2">
            Showing {{ $totalResults }} result(s)
        </p>
    </div>

    {{-- Filters --}}
    <div class="mb-6 p-4 bg-white rounded shadow space-y-4">
        <div>
            <label for="category" class="block font-bold mb-2">Filter by Category</label>
            <select id="category"
                    wire:model.live="filterCategory"
                    class="w-full px-4 py-2 border rounded-lg">
                <option value="all">All Categories</option>
                @foreach($categories as $category)
                    <option value="{{ $category }}">{{ $category }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block font-bold mb-2">Sort By</label>
            <div class="flex flex-wrap gap-2">
                <button wire:click="toggleSort('name')"
                        @class(['px-4 py-2 rounded', 'bg-blue-500 text-white' => $sortBy === 'name', 'bg-gray-200' => $sortBy !== 'name'])>
                    Name
                    @if($sortBy === 'name')
                        {{ $sortDirection === 'asc' ? '↑' : '↓' }}
                    @endif
                </button>

                <button wire:click="toggleSort('price')"
                        @class(['px-4 py-2 rounded', 'bg-blue-500 text-white' => $sortBy === 'price', 'bg-gray-200' => $sortBy !== 'price'])>
                    Price
                    @if($sortBy === 'price')
                        {{ $sortDirection === 'asc' ? '↑' : '↓' }}
                    @endif
                </button>

                <button wire:click="toggleSort('quantity')"
                        @class(['px-4 py-2 rounded', 'bg-blue-500 text-white' => $sortBy === 'quantity', 'bg-gray-200' => $sortBy !== 'quantity'])>
                    Quantity
                    @if($sortBy === 'quantity')
                        {{ $sortDirection === 'asc' ? '↑' : '↓' }}
                    @endif
                </button>
            </div>
        </div>
    </div>

    {{-- Results --}}
    @if(count($products) > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($products as $product)
                <div class="p-4 bg-white rounded shadow hover:shadow-lg transition">
                    <div class="flex items-start justify-between mb-3">
                        <h3 class="text-lg font-bold">{{ $product->name }}</h3>
                        <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded">
                            {{ $product->category }}
                        </span>
                    </div>

                    <p class="text-gray-600 text-sm mb-3">
                        {{ $product->description ?? 'No description' }}
                    </p>

                    <div class="flex items-end justify-between">
                        <div>
                            <p class="text-2xl font-bold text-green-600">
                                ${{ number_format($product->price, 2) }}
                            </p>
                            <p class="text-sm text-gray-500">
                                {{ $product->quantity }} in stock
                            </p>
                        </div>

                        @if($product->quantity > 0)
                            <button class="px-3 py-2 bg-green-500 text-white rounded hover:bg-green-600">
                                Add to Cart
                            </button>
                        @else
                            <button disabled
                                    class="px-3 py-2 bg-gray-300 text-gray-600 rounded cursor-not-allowed">
                                Out of Stock
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-12 bg-white rounded shadow">
            <p class="text-2xl text-gray-500 mb-2">No products found</p>
            <p class="text-gray-400">
                @if($search || $filterCategory !== 'all')
                    Try adjusting your search or filters
                @else
                    Start by searching or browsing products
                @endif
            </p>
        </div>
    @endif
</div>
```

### Step 6: Create Advanced Search Component (Optional)
```bash
php artisan make:livewire AdvancedProductSearch
```

In `app/Livewire/AdvancedProductSearch.php`:

```php
namespace App\Livewire;

use App\Models\Product;
use Livewire\Component;

class AdvancedProductSearch extends Component
{
    public string $search = '';
    public string $category = 'all';
    public ?float $minPrice = null;
    public ?float $maxPrice = null;
    public bool $inStockOnly = false;
    public array $highlightedResults = [];

    public function updatedSearch(): void
    {
        // Highlight matching text
        if ($this->search) {
            $products = Product::where('name', 'like', "%{$this->search}%")->take(5)->get();
            $this->highlightedResults = $products->map(function($product) {
                return [
                    'id' => $product->id,
                    'name' => str_ireplace(
                        $this->search,
                        '<strong>' . $this->search . '</strong>',
                        $product->name
                    ),
                ];
            })->toArray();
        } else {
            $this->highlightedResults = [];
        }
    }

    public function getProductsProperty()
    {
        $query = Product::query();

        if ($this->search) {
            $query->where('name', 'like', "%{$this->search}%")
                  ->orWhere('description', 'like', "%{$this->search}%");
        }

        if ($this->category !== 'all') {
            $query->where('category', $this->category);
        }

        if ($this->minPrice !== null) {
            $query->where('price', '>=', $this->minPrice);
        }

        if ($this->maxPrice !== null) {
            $query->where('price', '<=', $this->maxPrice);
        }

        if ($this->inStockOnly) {
            $query->where('quantity', '>', 0);
        }

        return $query->get();
    }

    public function resetFilters(): void
    {
        $this->reset('search', 'category', 'minPrice', 'maxPrice', 'inStockOnly');
    }

    public function render()
    {
        return view('livewire.advanced-product-search', [
            'products' => $this->products,
            'categories' => Product::distinct()->pluck('category')->sort(),
        ]);
    }
}
```

### Step 7: Create Route
In `routes/web.php`:

```php
use App\Livewire\ProductSearch;

Route::get('/search', ProductSearch::class);
```

### Step 8: Test Search Features
1. Type in search box to see live filtering
2. Click sorting buttons to reorder results
3. Select category filter
4. Verify highlighting works
5. Check no page refresh occurs

## Deliverables
- [ ] Product model and migration created
- [ ] Products seeded into database
- [ ] Live search component working
- [ ] Search filters by name and description
- [ ] Category filter dropdown functional
- [ ] Sorting buttons working
- [ ] Sort direction toggles (asc/desc)
- [ ] No page refresh on search/filter
- [ ] Results update in real-time
- [ ] Empty state message shown
- [ ] Advanced search with price range (optional)

## Resources
- [Livewire Properties](https://livewire.laravel.com/docs/properties)
- [Computed Properties](https://livewire.laravel.com/docs/computed-properties)
- [Validation](https://livewire.laravel.com/docs/validation)
- [Database Queries](https://laravel.com/docs/11.x/queries)

## Tips
- Use wire:model.live for instant updates
- Use computed properties for derived data
- Use $this->resetPage() when search changes
- Keep queries efficient with proper indexing
- Use LIKE queries carefully (case-insensitive in most DBs)
- Consider adding pagination for large result sets
- Test performance with many products
- Use database indexes for search columns
