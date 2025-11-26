# Lesson 3: Properties and Data Binding

**Duration**: 60 minutes
**Prerequisites**: Lesson 2 completed

---

## What You'll Learn

- Public vs protected properties
- Reactive properties
- Two-way data binding with wire:model
- Debouncing and throttling
- Property types and casting
- Computed properties
- Resetting properties

---

## Understanding Properties

Properties are the **state** of your component. They persist between requests.

### Public Properties

```php
class MyComponent extends Component
{
    public $name = 'John';      // ✅ Available in view
    public $email;              // ✅ Available in view
    public $age = 25;          // ✅ Available in view

    protected $password;        // ❌ NOT available in view
    private $secret;           // ❌ NOT available in view

    public function render()
    {
        return view('livewire.my-component');
    }
}
```

**In the view:**
```html
<div>
    {{ $name }}    <!-- Works: "John" -->
    {{ $email }}   <!-- Works: null initially -->
    {{ $age }}     <!-- Works: 25 -->
    {{ $password }} <!-- Error: undefined variable -->
</div>
```

### Initial Values

Properties can have default values:

```php
public $search = '';           // Empty string
public $isActive = true;       // Boolean
public $count = 0;             // Number
public $items = [];            // Array
public $user = null;           // Null
```

Or set in `mount()`:

```php
public $userId;
public $userData;

public function mount($userId)
{
    $this->userId = $userId;
    $this->userData = User::find($userId);
}
```

---

## Reactivity: The Magic

When a public property changes, Livewire **automatically re-renders** the component.

### Example: Counter

```php
class Counter extends Component
{
    public $count = 0;

    public function increment()
    {
        $this->count++; // Property changed → re-render!
    }

    public function render()
    {
        return view('livewire.counter');
    }
}
```

```html
<div>
    <h1>Count: {{ $count }}</h1>
    <button wire:click="increment">+</button>
</div>
```

**What happens:**
1. User clicks button
2. `increment()` method runs
3. `$count` changes from 0 to 1
4. Component re-renders automatically
5. View shows new value

**You don't have to manually update the DOM!**

---

## Data Binding: wire:model

The most powerful Livewire directive. Creates **two-way binding** between input and property.

### Basic Usage

```php
class SearchUsers extends Component
{
    public $search = '';

    public function render()
    {
        return view('livewire.search-users', [
            'users' => User::where('name', 'like', "%{$this->search}%")->get()
        ]);
    }
}
```

```html
<div>
    <input type="text" wire:model="search">

    @foreach($users as $user)
        <div>{{ $user->name }}</div>
    @endforeach
</div>
```

**How it works:**
1. User types in input
2. On blur (default), Livewire updates `$search` property
3. Component re-renders with new search term
4. User list updates

### wire:model Modifiers

Different ways to trigger updates:

#### 1. wire:model.live (Real-time)

Updates on every keystroke:

```html
<input type="text" wire:model.live="search">
```

**Use for:**
- Search bars
- Live filters
- Character counters

**Warning**: Can cause many server requests. Use debounce!

#### 2. wire:model.blur (Default)

Updates when input loses focus:

```html
<input type="text" wire:model.blur="email">
<!-- Or just -->
<input type="text" wire:model="email">
```

**Use for:**
- Regular form fields
- Email inputs
- Less critical updates

#### 3. wire:model.change

Updates on `change` event (for selects, checkboxes):

```html
<select wire:model.change="category">
    <option value="">Select...</option>
    <option value="tech">Tech</option>
    <option value="business">Business</option>
</select>
```

#### 4. wire:model.live.debounce

Updates live, but waits for user to stop typing:

```html
<!-- Wait 500ms after last keystroke -->
<input type="text" wire:model.live.debounce.500ms="search">

<!-- Or shorter -->
<input type="text" wire:model.live.debounce="search">
```

**Perfect for:**
- Search bars (most common)
- Any live input that queries database

**Comparison:**

```html
<!-- 🔥 Fires on EVERY keystroke (100 requests for "hello world") -->
<input type="text" wire:model.live="search">

<!-- ✅ Waits 500ms, then fires (1-2 requests for "hello world") -->
<input type="text" wire:model.live.debounce.500ms="search">

<!-- 💤 Only fires on blur (1 request) -->
<input type="text" wire:model.blur="search">
```

#### 5. wire:model.live.throttle

Limits requests to maximum frequency:

```html
<!-- Maximum 1 request per second -->
<input type="text" wire:model.live.throttle.1000ms="search">
```

**Debounce vs Throttle:**
- **Debounce**: Waits for pause in typing
- **Throttle**: Fires at regular intervals while typing

```
User types: h-e-l-l-o-w-o-r-l-d

Debounce (500ms): ........................request (after they stop)
Throttle (1000ms): ....request....request....request (every 1s)
```

---

## Binding Different Input Types

### Text Input

```html
<input type="text" wire:model.blur="name">
```

### Textarea

```html
<textarea wire:model.blur="description"></textarea>
```

### Checkbox (Boolean)

```php
public $acceptTerms = false;
```

```html
<input type="checkbox" wire:model.live="acceptTerms">
<label>I accept the terms</label>

@if($acceptTerms)
    <button type="submit">Submit</button>
@endif
```

### Checkbox (Array - Multiple)

```php
public $selectedCategories = [];
```

```html
<input type="checkbox" value="tech" wire:model.live="selectedCategories"> Tech
<input type="checkbox" value="business" wire:model.live="selectedCategories"> Business
<input type="checkbox" value="health" wire:model.live="selectedCategories"> Health

<div>Selected: {{ implode(', ', $selectedCategories) }}</div>
```

Result when checking "Tech" and "Health":
```php
$selectedCategories = ['tech', 'health']
```

### Radio Buttons

```php
public $paymentMethod = 'card';
```

```html
<input type="radio" value="card" wire:model.live="paymentMethod"> Credit Card
<input type="radio" value="paypal" wire:model.live="paymentMethod"> PayPal
<input type="radio" value="bank" wire:model.live="paymentMethod"> Bank Transfer

<div>Selected: {{ $paymentMethod }}</div>
```

### Select Dropdown

```php
public $country = '';
```

```html
<select wire:model.change="country">
    <option value="">Select country...</option>
    <option value="vn">Vietnam</option>
    <option value="us">USA</option>
    <option value="fr">France</option>
</select>

@if($country === 'vn')
    <div>Xin chào!</div>
@endif
```

### Multi-Select

```php
public $selectedTags = [];
```

```html
<select wire:model.live="selectedTags" multiple>
    <option value="laravel">Laravel</option>
    <option value="php">PHP</option>
    <option value="javascript">JavaScript</option>
    <option value="tailwind">Tailwind</option>
</select>

<div>
    @foreach($selectedTags as $tag)
        <span class="badge">{{ $tag }}</span>
    @endforeach
</div>
```

---

## Property Types and Casting

### Type Hinting

Laravel 10+ with PHP 8.1+:

```php
class MyComponent extends Component
{
    public string $name = '';
    public int $age = 0;
    public bool $isActive = false;
    public array $items = [];
    public ?User $user = null;
}
```

Benefits:
- Type safety
- IDE autocomplete
- Documentation

### Locked Properties

Prevent properties from being modified from frontend:

```php
use Livewire\Attributes\Locked;

class ShowPost extends Component
{
    #[Locked]
    public $postId; // Cannot be tampered with from frontend

    public $title; // Can be modified
}
```

**Use for:**
- IDs
- User roles
- Prices
- Anything security-sensitive

### URL Query String Binding

Sync property with URL query string:

```php
use Livewire\Attributes\Url;

class SearchProducts extends Component
{
    #[Url]
    public $search = '';

    #[Url]
    public $category = '';
}
```

**Result:**
```
User searches "laptop" and selects "tech" category:

URL: /products?search=laptop&category=tech

Shareable! Bookmarkable! Browser back button works!
```

**With custom key:**
```php
#[Url(as: 'q')]
public $search = '';

// URL: /products?q=laptop
```

---

## Computed Properties

For derived values that you don't want to store as properties:

### Basic Pattern

```php
class ShowPost extends Component
{
    public $postId;

    // Computed property (note the "get" prefix and "Property" suffix)
    public function getPostProperty()
    {
        return Post::find($this->postId);
    }

    public function getCommentCountProperty()
    {
        return $this->post->comments()->count();
    }

    public function render()
    {
        return view('livewire.show-post');
    }
}
```

**In view (access without parentheses):**
```html
<div>
    <h1>{{ $this->post->title }}</h1>
    <p>{{ $this->commentCount }} comments</p>
</div>
```

### Benefits of Computed Properties

**1. Cached per request:**
```html
<!-- Queries database only ONCE, even though used 3 times -->
<h1>{{ $this->post->title }}</h1>
<div>{{ $this->post->content }}</div>
<small>By {{ $this->post->author->name }}</small>
```

**2. Lazy loaded:**
```php
// Only runs if actually used in the view
public function getExpensiveDataProperty()
{
    return ExpensiveCalculation::run();
}
```

**3. Cleaner views:**
```html
<!-- Instead of -->
{{ $posts->where('published', true)->count() }}

<!-- Use -->
{{ $this->publishedCount }}
```

### Using Attributes (Laravel 10+)

```php
use Livewire\Attributes\Computed;

class ShowPost extends Component
{
    public $postId;

    #[Computed]
    public function post()
    {
        return Post::find($this->postId);
    }

    #[Computed]
    public function commentCount()
    {
        return $this->post->comments()->count();
    }
}
```

Same usage in view:
```html
{{ $this->post->title }}
{{ $this->commentCount }}
```

---

## Property Hooks

React to property changes:

### updating{Property}

Runs BEFORE property is updated:

```php
class MyComponent extends Component
{
    public $name = '';

    public function updatingName($value)
    {
        // $value is the new value (not yet set)
        // $this->name is still the old value

        // Example: Format the value
        $value = ucfirst($value);
    }
}
```

**Use cases:**
- Format input (uppercase, trim, etc.)
- Prevent certain updates
- Validation before update

### updated{Property}

Runs AFTER property is updated:

```php
class SearchUsers extends Component
{
    public $search = '';
    public $page = 1;

    public function updatedSearch()
    {
        // Reset to page 1 when search changes
        $this->page = 1;
    }
}
```

**Use cases:**
- Trigger side effects
- Update related properties
- Log changes
- Real-time validation

### Example: Real-time Validation

```php
use Livewire\Attributes\Validate;

class CreateUser extends Component
{
    #[Validate('required|email')]
    public $email = '';

    public function updatedEmail()
    {
        // Validate this field every time it changes
        $this->validateOnly('email');
    }
}
```

```html
<input type="email" wire:model.blur="email">
@error('email') <span class="error">{{ $message }}</span> @enderror
```

### General updated() Hook

Runs after ANY property update:

```php
public function updated($property, $value)
{
    // $property = 'search' or 'category' or whatever changed
    // $value = new value

    // Validate all properties on every change
    $this->validate();
}
```

---

## Resetting Properties

### Reset Specific Properties

```php
class MyForm extends Component
{
    public $name = '';
    public $email = '';

    public function save()
    {
        // Save logic...

        // Reset the form
        $this->reset('name', 'email');
    }
}
```

### Reset All Properties

```php
public function clearForm()
{
    $this->reset();
}
```

### Reset Except

```php
public function save()
{
    // Save...

    // Reset everything except 'category'
    $this->resetExcept('category');
}
```

---

## Working with Complex Data

### Arrays

```php
class TodoList extends Component
{
    public $todos = [];
    public $newTodo = '';

    public function addTodo()
    {
        $this->todos[] = [
            'text' => $this->newTodo,
            'completed' => false
        ];

        $this->reset('newTodo');
    }

    public function toggleTodo($index)
    {
        $this->todos[$index]['completed'] = !$this->todos[$index]['completed'];
    }

    public function removeTodo($index)
    {
        unset($this->todos[$index]);
        $this->todos = array_values($this->todos); // Re-index
    }
}
```

```html
<div>
    <form wire:submit="addTodo">
        <input type="text" wire:model="newTodo">
        <button type="submit">Add</button>
    </form>

    @foreach($todos as $index => $todo)
        <div>
            <input
                type="checkbox"
                wire:click="toggleTodo({{ $index }})"
                @checked($todo['completed'])
            >
            <span class="{{ $todo['completed'] ? 'line-through' : '' }}">
                {{ $todo['text'] }}
            </span>
            <button wire:click="removeTodo({{ $index }})">Delete</button>
        </div>
    @endforeach
</div>
```

### Nested Properties

```php
public $user = [
    'name' => '',
    'email' => '',
    'address' => [
        'street' => '',
        'city' => '',
        'country' => ''
    ]
];
```

```html
<input wire:model="user.name">
<input wire:model="user.email">
<input wire:model="user.address.street">
<input wire:model="user.address.city">
<select wire:model="user.address.country">...</select>
```

---

## Common Patterns

### 1. Search with Debounce

```php
class SearchProducts extends Component
{
    public $search = '';

    public function render()
    {
        return view('livewire.search-products', [
            'products' => Product::where('name', 'like', "%{$this->search}%")
                ->limit(10)
                ->get()
        ]);
    }
}
```

```html
<div>
    <input
        type="text"
        wire:model.live.debounce.300ms="search"
        placeholder="Search products..."
    >

    <div>
        @forelse($products as $product)
            <div>{{ $product->name }}</div>
        @empty
            <div>No products found</div>
        @endforelse
    </div>
</div>
```

### 2. Filter with Multiple Criteria

```php
class ProductFilter extends Component
{
    public $search = '';
    public $category = '';
    public $minPrice = 0;
    public $maxPrice = 1000;

    public function render()
    {
        $query = Product::query();

        if ($this->search) {
            $query->where('name', 'like', "%{$this->search}%");
        }

        if ($this->category) {
            $query->where('category', $this->category);
        }

        $query->whereBetween('price', [$this->minPrice, $this->maxPrice]);

        return view('livewire.product-filter', [
            'products' => $query->get()
        ]);
    }
}
```

### 3. Dependent Dropdowns

```php
class LocationSelector extends Component
{
    public $selectedCountry = '';
    public $selectedCity = '';

    public function updatedSelectedCountry()
    {
        // Reset city when country changes
        $this->selectedCity = '';
    }

    public function render()
    {
        return view('livewire.location-selector', [
            'countries' => Country::all(),
            'cities' => $this->selectedCountry
                ? City::where('country_id', $this->selectedCountry)->get()
                : []
        ]);
    }
}
```

```html
<select wire:model.change="selectedCountry">
    <option value="">Select country...</option>
    @foreach($countries as $country)
        <option value="{{ $country->id }}">{{ $country->name }}</option>
    @endforeach
</select>

@if($selectedCountry)
    <select wire:model.change="selectedCity">
        <option value="">Select city...</option>
        @foreach($cities as $city)
            <option value="{{ $city->id }}">{{ $city->name }}</option>
        @endforeach
    </select>
@endif
```

---

## Quick Quiz

**Question 1**: What's the difference?

```html
<input wire:model.live="search">
<input wire:model.live.debounce.500ms="search">
```

<details>
<summary>Show Answer</summary>

- **live**: Updates on every keystroke (could be 50+ requests)
- **live.debounce.500ms**: Waits 500ms after user stops typing (1-2 requests)

Debounce is much more efficient for search!

</details>

**Question 2**: How do you access a computed property in the view?

```php
public function getPostProperty() { ... }
```

<details>
<summary>Show Answer</summary>

```html
{{ $this->post }}
<!-- Not: {{ $this->getPostProperty() }} -->
```

Without parentheses, without "get" prefix or "Property" suffix.

</details>

**Question 3**: What does this do?

```php
#[Url]
public $search = '';
```

<details>
<summary>Show Answer</summary>

Syncs the `$search` property with the URL query string:
- Property changes → URL updates
- URL changes (back button) → Property updates
- Makes searches shareable and bookmarkable

</details>

---

## Practice Exercise

Create a **Product Filter** component:

**Requirements:**
1. Search by name (debounced)
2. Filter by category (dropdown)
3. Filter by price range (min/max inputs)
4. Show results count
5. URL should reflect filters (shareable)

Try building it yourself!

<details>
<summary>Show Solution</summary>

**Component:**
```php
<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Product;
use Livewire\Attributes\Url;

class ProductFilter extends Component
{
    #[Url(as: 'q')]
    public $search = '';

    #[Url]
    public $category = '';

    #[Url]
    public $minPrice = 0;

    #[Url]
    public $maxPrice = 10000;

    public function render()
    {
        $query = Product::query();

        if ($this->search) {
            $query->where('name', 'like', "%{$this->search}%");
        }

        if ($this->category) {
            $query->where('category', $this->category);
        }

        $query->whereBetween('price', [$this->minPrice, $this->maxPrice]);

        $products = $query->get();

        return view('livewire.product-filter', [
            'products' => $products,
            'categories' => Product::distinct()->pluck('category')
        ]);
    }
}
```

**View:**
```html
<div class="space-y-4">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
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
    </div>

    <!-- Results Count -->
    <div class="text-sm text-gray-600">
        Found {{ count($products) }} product(s)
    </div>

    <!-- Products -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        @forelse($products as $product)
            <div class="border rounded p-4">
                <h3 class="font-bold">{{ $product->name }}</h3>
                <p class="text-sm text-gray-600">{{ $product->category }}</p>
                <p class="text-lg font-semibold mt-2">${{ number_format($product->price, 2) }}</p>
            </div>
        @empty
            <div class="col-span-3 text-center text-gray-500 py-8">
                No products found matching your criteria
            </div>
        @endforelse
    </div>
</div>
```

</details>

---

## Summary

You mastered:

- ✅ Public properties and reactivity
- ✅ Two-way data binding with wire:model
- ✅ Debouncing and throttling
- ✅ Binding all input types
- ✅ Computed properties
- ✅ Property hooks (updating/updated)
- ✅ Resetting properties
- ✅ URL query string binding

**Next Lesson**: Actions & Methods - trigger behavior from the view!

---

**Properties are the heart of Livewire. Master them and you'll build amazing reactive UIs!**
