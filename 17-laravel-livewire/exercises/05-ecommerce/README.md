# 05 - E-commerce with Livewire

## Objective
Build a complete e-commerce system using Livewire with shopping cart, checkout, and order management.

## Prerequisites
- Completed all Module 17 exercises
- Understanding of Livewire components
- Knowledge of Eloquent relationships
- Experience with form handling

## Instructions

### Step 1: Create Models and Migrations
```bash
php artisan make:model Category -m
php artisan make:model Product -m
php artisan make:model Cart -m
php artisan make:model CartItem -m
php artisan make:model Order -m
php artisan make:model OrderItem -m
```

Edit migrations:

```php
// categories table
Schema::create('categories', function (Blueprint $table) {
    $table->id();
    $table->string('name')->unique();
    $table->string('slug')->unique();
    $table->text('description')->nullable();
    $table->timestamps();
});

// products table
Schema::create('products', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('slug')->unique();
    $table->text('description')->nullable();
    $table->decimal('price', 10, 2);
    $table->integer('quantity')->default(0);
    $table->foreignId('category_id')->constrained()->onDelete('cascade');
    $table->string('image')->nullable();
    $table->timestamps();
});

// carts table
Schema::create('carts', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
    $table->string('session_id')->nullable()->unique();
    $table->timestamps();
});

// cart_items table
Schema::create('cart_items', function (Blueprint $table) {
    $table->id();
    $table->foreignId('cart_id')->constrained()->onDelete('cascade');
    $table->foreignId('product_id')->constrained()->onDelete('cascade');
    $table->integer('quantity')->default(1);
    $table->decimal('price', 10, 2); // Price at time of adding
    $table->timestamps();
});

// orders table
Schema::create('orders', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->string('order_number')->unique();
    $table->enum('status', ['pending', 'processing', 'shipped', 'delivered'])->default('pending');
    $table->decimal('total', 10, 2);
    $table->string('shipping_address');
    $table->string('shipping_city');
    $table->string('shipping_state');
    $table->string('shipping_zip');
    $table->decimal('shipping_cost', 10, 2)->default(0);
    $table->timestamp('shipped_at')->nullable();
    $table->timestamp('delivered_at')->nullable();
    $table->timestamps();
});

// order_items table
Schema::create('order_items', function (Blueprint $table) {
    $table->id();
    $table->foreignId('order_id')->constrained()->onDelete('cascade');
    $table->foreignId('product_id')->constrained()->onDelete('cascade');
    $table->integer('quantity');
    $table->decimal('price', 10, 2); // Price at time of order
    $table->timestamps();
});
```

Run migrations:

```bash
php artisan migrate
```

### Step 2: Define Models
In `app/Models/Product.php`:

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'price', 'quantity', 'category_id', 'image'];

    protected $casts = ['price' => 'float'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function isInStock(): bool
    {
        return $this->quantity > 0;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
```

In `app/Models/Category.php`:

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = ['name', 'slug', 'description'];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
```

In `app/Models/Cart.php`:

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cart extends Model
{
    protected $fillable = ['user_id', 'session_id'];

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getTotalProperty(): float
    {
        return $this->items()->sum(\DB::raw('quantity * price'));
    }

    public function getCountProperty(): int
    {
        return $this->items()->sum('quantity');
    }
}
```

In `app/Models/Order.php`:

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    protected $fillable = [
        'user_id', 'order_number', 'status', 'total',
        'shipping_address', 'shipping_city', 'shipping_state', 'shipping_zip',
        'shipping_cost'
    ];

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            $order->order_number = 'ORD-' . date('YmdHis') . '-' . rand(1000, 9999);
        });
    }
}
```

### Step 3: Create Shopping Cart Component
```bash
php artisan make:livewire ShoppingCart
```

In `app/Livewire/ShoppingCart.php`:

```php
namespace App\Livewire;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Livewire\Component;

class ShoppingCart extends Component
{
    public ?Cart $cart = null;

    public function mount(): void
    {
        $this->loadCart();
    }

    private function loadCart(): void
    {
        if (auth()->check()) {
            $this->cart = Cart::firstOrCreate(['user_id' => auth()->id()]);
        }
    }

    public function addToCart(Product $product): void
    {
        if (!$this->cart) {
            $this->loadCart();
        }

        $cartItem = $this->cart->items()
            ->where('product_id', $product->id)
            ->first();

        if ($cartItem) {
            $cartItem->increment('quantity');
        } else {
            $this->cart->items()->create([
                'product_id' => $product->id,
                'quantity' => 1,
                'price' => $product->price,
            ]);
        }

        $this->dispatch('cart-updated');
    }

    public function removeFromCart(CartItem $item): void
    {
        $item->delete();
        $this->dispatch('cart-updated');
    }

    public function updateQuantity(CartItem $item, int $quantity): void
    {
        if ($quantity > 0) {
            $item->update(['quantity' => $quantity]);
        } else {
            $item->delete();
        }

        $this->dispatch('cart-updated');
    }

    public function clearCart(): void
    {
        if ($this->cart) {
            $this->cart->items()->delete();
        }

        $this->dispatch('cart-updated');
    }

    public function render()
    {
        return view('livewire.shopping-cart', [
            'items' => $this->cart?->items()->with('product')->get() ?? [],
            'total' => $this->cart?->total ?? 0,
        ]);
    }
}
```

### Step 4: Create Cart View
In `resources/views/livewire/shopping-cart.blade.php`:

```blade
<div class="w-full max-w-4xl mx-auto p-6">
    <h1 class="text-3xl font-bold mb-6">Shopping Cart</h1>

    @if(count($items) > 0)
        <div class="grid grid-cols-3 gap-6">
            <div class="col-span-2">
                <div class="space-y-4">
                    @foreach($items as $item)
                        <div class="flex gap-4 p-4 bg-white rounded shadow">
                            @if($item->product->image)
                                <img src="{{ asset('storage/' . $item->product->image) }}"
                                     alt="{{ $item->product->name }}"
                                     class="w-24 h-24 object-cover rounded">
                            @else
                                <div class="w-24 h-24 bg-gray-200 rounded"></div>
                            @endif

                            <div class="flex-1">
                                <h3 class="font-bold text-lg">{{ $item->product->name }}</h3>
                                <p class="text-gray-600">${{ number_format($item->price, 2) }}</p>

                                <div class="flex items-center gap-2 mt-4">
                                    <button wire:click="updateQuantity(${{ $item->id }}, ${{ $item->quantity - 1 }})"
                                            class="px-2 py-1 bg-gray-200 rounded">-</button>
                                    <span class="px-4">{{ $item->quantity }}</span>
                                    <button wire:click="updateQuantity(${{ $item->id }}, ${{ $item->quantity + 1 }})"
                                            class="px-2 py-1 bg-gray-200 rounded">+</button>
                                </div>
                            </div>

                            <div class="text-right">
                                <p class="text-xl font-bold text-green-600">
                                    ${{ number_format($item->quantity * $item->price, 2) }}
                                </p>
                                <button wire:click="removeFromCart(${{ $item->id }})"
                                        class="text-red-500 hover:text-red-700 mt-2">Remove</button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Order Summary --}}
            <div class="bg-white p-6 rounded shadow h-fit">
                <h2 class="text-2xl font-bold mb-4">Order Summary</h2>

                <div class="space-y-3 mb-4">
                    <div class="flex justify-between">
                        <span>Subtotal</span>
                        <span>${{ number_format($total, 2) }}</span>
                    </div>

                    <div class="flex justify-between">
                        <span>Shipping</span>
                        <span>$10.00</span>
                    </div>

                    <div class="border-t pt-3">
                        <div class="flex justify-between font-bold text-lg">
                            <span>Total</span>
                            <span>${{ number_format($total + 10, 2) }}</span>
                        </div>
                    </div>
                </div>

                <a href="/checkout" class="w-full block text-center px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600 mb-2">
                    Proceed to Checkout
                </a>

                <button wire:click="clearCart"
                        onclick="return confirm('Clear your cart?')"
                        class="w-full px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600">
                    Clear Cart
                </button>
            </div>
        </div>
    @else
        <div class="text-center py-12 bg-white rounded shadow">
            <p class="text-2xl text-gray-500 mb-4">Your cart is empty</p>
            <a href="/products" class="px-6 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                Continue Shopping
            </a>
        </div>
    @endif
</div>
```

### Step 5: Create Products Listing Component
```bash
php artisan make:livewire ProductListing
```

In `app/Livewire/ProductListing.php`:

```php
namespace App\Livewire;

use App\Models\Category;
use App\Models\Product;
use Livewire\Component;

class ProductListing extends Component
{
    public string $selectedCategory = 'all';
    public string $search = '';
    public string $sortBy = 'name';

    public function addToCart(Product $product): void
    {
        $cart = auth()->user()?->cart ?? null;

        if (!$cart && auth()->check()) {
            $cart = auth()->user()->cart()->create();
        }

        if ($cart) {
            $item = $cart->items()->where('product_id', $product->id)->first();

            if ($item) {
                $item->increment('quantity');
            } else {
                $cart->items()->create([
                    'product_id' => $product->id,
                    'quantity' => 1,
                    'price' => $product->price,
                ]);
            }

            $this->dispatch('cart-updated');
            session()->flash('success', $product->name . ' added to cart!');
        }
    }

    public function getProductsProperty()
    {
        $query = Product::query();

        if ($this->selectedCategory !== 'all') {
            $query->where('category_id', $this->selectedCategory);
        }

        if ($this->search) {
            $query->where('name', 'like', "%{$this->search}%")
                  ->orWhere('description', 'like', "%{$this->search}%");
        }

        return $query->orderBy($this->sortBy)->paginate(12);
    }

    public function render()
    {
        return view('livewire.product-listing', [
            'products' => $this->products,
            'categories' => Category::all(),
        ]);
    }
}
```

### Step 6: Create Checkout Component
```bash
php artisan make:livewire Checkout
```

In `app/Livewire/Checkout.php`:

```php
namespace App\Livewire;

use App\Models\Order;
use App\Models\OrderItem;
use Livewire\Component;
use Livewire\Attributes\Validate;

class Checkout extends Component
{
    #[Validate('required|string')]
    public string $address = '';

    #[Validate('required|string')]
    public string $city = '';

    #[Validate('required|string')]
    public string $state = '';

    #[Validate('required|string')]
    public string $zip = '';

    public function checkout(): void
    {
        $this->authorize('checkout');

        $this->validate();

        $cart = auth()->user()->cart;

        if (!$cart || $cart->items->isEmpty()) {
            session()->flash('error', 'Cart is empty');
            $this->redirect('/cart');
            return;
        }

        // Create order
        $order = Order::create([
            'user_id' => auth()->id(),
            'status' => 'pending',
            'total' => $cart->total,
            'shipping_address' => $this->address,
            'shipping_city' => $this->city,
            'shipping_state' => $this->state,
            'shipping_zip' => $this->zip,
            'shipping_cost' => 10.00,
        ]);

        // Add items to order
        foreach ($cart->items as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'price' => $item->price,
            ]);
        }

        // Clear cart
        $cart->items()->delete();

        session()->flash('success', 'Order placed successfully!');
        $this->redirect("/orders/{$order->id}");
    }

    public function render()
    {
        $cart = auth()->user()?->cart;

        return view('livewire.checkout', [
            'cart' => $cart,
            'cartItems' => $cart?->items()->with('product')->get() ?? [],
        ]);
    }
}
```

### Step 7: Create Routes
In `routes/web.php`:

```php
use App\Livewire\ProductListing;
use App\Livewire\ShoppingCart;
use App\Livewire\Checkout;

Route::get('/products', ProductListing::class)->name('products');
Route::get('/cart', ShoppingCart::class)->name('cart');
Route::middleware('auth')->get('/checkout', Checkout::class)->name('checkout');
Route::middleware('auth')->get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
```

### Step 8: Seed Sample Data
```bash
php artisan make:seeder EcommerceSeeder
```

In seeder:

```php
public function run(): void
{
    $categories = [
        Category::create(['name' => 'Electronics', 'slug' => 'electronics']),
        Category::create(['name' => 'Clothing', 'slug' => 'clothing']),
        Category::create(['name' => 'Books', 'slug' => 'books']),
    ];

    foreach ($categories as $category) {
        Product::factory()->count(5)->create(['category_id' => $category->id]);
    }
}
```

### Step 9: Test E-commerce
1. Browse products
2. Add items to cart
3. View cart
4. Proceed to checkout
5. Complete order
6. Verify order in database

## Deliverables
- [ ] All models created with relationships
- [ ] Database migrations run
- [ ] Product listing component working
- [ ] Shopping cart component working
- [ ] Add/remove from cart functionality
- [ ] Update quantity in cart
- [ ] Checkout form with validation
- [ ] Order creation from cart
- [ ] Order tracking available
- [ ] Cart persists between pages
- [ ] Authorization checks on checkout
- [ ] Sample products seeded

## Resources
- [Livewire Documentation](https://livewire.laravel.com)
- [Eloquent Relationships](https://laravel.com/docs/11.x/eloquent-relationships)
- [E-commerce Best Practices](https://stripe.com/docs)

## Tips
- Implement payment processing (Stripe, PayPal)
- Add inventory management and low stock alerts
- Create order tracking and notifications
- Implement wishlists for users
- Add product reviews and ratings
- Consider tax calculations
- Implement promo codes
- Track user browsing for recommendations
