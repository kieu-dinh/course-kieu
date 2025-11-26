# 01 - Models and CRUD

## Objective
Master Eloquent ORM to create models and perform CRUD operations efficiently using Laravel's built-in database abstraction.

## Prerequisites
- Completed Module 14 exercises
- Basic understanding of relational databases
- Knowledge of SQL queries

## Instructions

### Step 1: Create a Model
Generate a model with migration:

```bash
php artisan make:model Product -m
```

### Step 2: Define Migration
Edit the migration in `database/migrations/`:

```php
Schema::create('products', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->text('description')->nullable();
    $table->decimal('price', 10, 2);
    $table->integer('quantity')->default(0);
    $table->string('sku')->unique();
    $table->timestamps();
});
```

Run migration:

```bash
php artisan migrate
```

### Step 3: Define Model Attributes
Edit `app/Models/Product.php`:

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = ['name', 'description', 'price', 'quantity', 'sku'];
    protected $casts = [
        'price' => 'float',
        'quantity' => 'integer',
    ];
}
```

### Step 4: Use Tinker (Interactive Shell)
Test your model interactively:

```bash
php artisan tinker
```

### Step 5: Create Records (CREATE)
In Tinker or controller:

```php
// Method 1: Using create()
$product = Product::create([
    'name' => 'Laptop',
    'description' => 'High-performance laptop',
    'price' => 999.99,
    'quantity' => 5,
    'sku' => 'LAPTOP-001',
]);

// Method 2: Using new() and save()
$product = new Product();
$product->name = 'Desktop';
$product->price = 1299.99;
$product->sku = 'DESKTOP-001';
$product->save();

// Method 3: Using firstOrCreate()
$product = Product::firstOrCreate(
    ['sku' => 'PHONE-001'],
    ['name' => 'Phone', 'price' => 799.99]
);
```

### Step 6: Read Records (READ)
Retrieve data from database:

```php
// Get all products
$products = Product::all();

// Get first product
$product = Product::first();

// Get by ID
$product = Product::find(1);

// Get or fail (throws exception if not found)
$product = Product::findOrFail(1);

// Get with conditions
$products = Product::where('price', '>', 500)->get();
$products = Product::where('quantity', '>', 0)
    ->where('price', '<', 1000)
    ->get();

// Get with specific columns
$products = Product::select('name', 'price')->get();

// Order results
$products = Product::orderBy('price', 'desc')->get();
$products = Product::latest()->get(); // By created_at desc

// Limit results
$products = Product::limit(10)->get();

// Count
$count = Product::count();
$count = Product::where('quantity', '>', 0)->count();
```

### Step 7: Update Records (UPDATE)
Modify existing records:

```php
// Method 1: Using update()
$product = Product::find(1);
$product->update([
    'name' => 'Updated Name',
    'price' => 899.99,
]);

// Method 2: Update directly on model
$product = Product::find(1);
$product->name = 'New Name';
$product->save();

// Method 3: Update multiple records
Product::where('quantity', 0)
    ->update(['quantity' => 10]);

// Method 4: Using updateOrCreate()
$product = Product::updateOrCreate(
    ['sku' => 'TABLET-001'],
    ['name' => 'Tablet', 'price' => 499.99]
);
```

### Step 8: Delete Records (DELETE)
Remove records from database:

```php
// Delete single record
$product = Product::find(1);
$product->delete();

// Delete multiple records
Product::where('quantity', 0)->delete();

// Delete by ID
Product::destroy(1, 2, 3);

// Delete all
Product::query()->delete(); // Careful!
```

### Step 9: Create ProductController
Generate controller:

```bash
php artisan make:controller ProductController --resource
```

Implement methods:

```php
public function index()
{
    $products = Product::paginate(15);
    return view('products.index', compact('products'));
}

public function show($id)
{
    $product = Product::findOrFail($id);
    return view('products.show', compact('product'));
}

public function store(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'price' => 'required|numeric|min:0',
        'quantity' => 'required|integer|min:0',
        'sku' => 'required|unique:products',
    ]);

    Product::create($validated);
    return redirect('/products')->with('success', 'Product created!');
}

public function update(Request $request, $id)
{
    $product = Product::findOrFail($id);

    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'price' => 'required|numeric|min:0',
        'quantity' => 'required|integer|min:0',
    ]);

    $product->update($validated);
    return redirect("/products/{$id}")->with('success', 'Updated!');
}

public function destroy($id)
{
    $product = Product::findOrFail($id);
    $product->delete();
    return redirect('/products')->with('success', 'Deleted!');
}
```

### Step 10: Create Views
Create `resources/views/products/index.blade.php`:

```blade
@extends('layout')

@section('title', 'Products')

@section('content')
    <h1>Products</h1>

    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>SKU</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($products as $product)
                <tr>
                    <td>{{ $product->name }}</td>
                    <td>{{ $product->sku }}</td>
                    <td>${{ number_format($product->price, 2) }}</td>
                    <td>{{ $product->quantity }}</td>
                    <td>
                        <a href="/products/{{ $product->id }}">View</a>
                        <a href="/products/{{ $product->id }}/edit">Edit</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5">No products</td></tr>
            @endforelse
        </tbody>
    </table>

    {{ $products->links() }}
@endsection
```

Create `resources/views/products/show.blade.php`:

```blade
@extends('layout')

@section('title', $product->name)

@section('content')
    <h1>{{ $product->name }}</h1>

    <p><strong>SKU:</strong> {{ $product->sku }}</p>
    <p><strong>Price:</strong> ${{ number_format($product->price, 2) }}</p>
    <p><strong>Quantity:</strong> {{ $product->quantity }}</p>
    @if($product->description)
        <p><strong>Description:</strong> {{ $product->description }}</p>
    @endif

    <a href="/products">Back</a>
@endsection
```

### Step 11: Practical Exercises in Tinker
Practice these queries:

```php
php artisan tinker

// Find average price
Product::avg('price');

// Find max price
Product::max('price');

// Count low-stock items
Product::where('quantity', '<', 10)->count();

// Get expensive items
Product::where('price', '>', 1000)->orderBy('price', 'desc')->get();

// Find by name (partial match)
Product::where('name', 'like', '%Phone%')->get();
```

## Deliverables
- [ ] Product model created with proper attributes
- [ ] Migration created and run
- [ ] All CRUD operations tested in Tinker
- [ ] ProductController with full CRUD methods
- [ ] Views displaying products
- [ ] Form validation working
- [ ] Pagination implemented
- [ ] Error handling with findOrFail()
- [ ] Mass assignment protected with $fillable

## Resources
- [Eloquent ORM](https://laravel.com/docs/11.x/eloquent)
- [CRUD Operations](https://laravel.com/docs/11.x/eloquent#creating-models)
- [Model Relationships](https://laravel.com/docs/11.x/eloquent-relationships)
- [Query Builder](https://laravel.com/docs/11.x/queries)

## Tips
- Always use `$fillable` to prevent mass assignment vulnerabilities
- Use `findOrFail()` instead of `find()` to show proper 404 errors
- Use `firstOrCreate()` and `updateOrCreate()` for idempotent operations
- Tinker is your best friend for testing queries
- Use `dd()` to debug queries
- Check generated SQL with `->toSql()`
