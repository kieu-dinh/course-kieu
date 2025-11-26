# Lesson 1: Introduction to Laravel Livewire

**Duration**: 45 minutes
**Prerequisites**: Module 16 completed, Laravel + Alpine.js knowledge

---

## What You'll Learn

- What Livewire is and why it's revolutionary
- How Livewire works under the hood
- The TALL stack philosophy
- Installing and configuring Livewire
- Creating your first reactive component

---

## What is Laravel Livewire?

**Livewire is a full-stack framework for Laravel that makes building dynamic interfaces simple, without leaving the comfort of Laravel.**

### The Traditional Problem

Before Livewire, if you wanted interactivity, you had to:

```
1. Write backend API endpoints (Laravel)
2. Write frontend JavaScript (Vue/React)
3. Handle state management
4. Deal with AJAX requests
5. Manage two separate codebases
```

This is complex and time-consuming.

### The Livewire Solution

With Livewire, you write PHP components that feel reactive:

```php
// This is a complete interactive search component!
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
    <input type="text" wire:model.live="search">

    @foreach($users as $user)
        <div>{{ $user->name }}</div>
    @endforeach
</div>
```

That's it! The search happens automatically as you type. No JavaScript required.

---

## How Livewire Works

### The Magic Explained

Livewire isn't actually magic. Here's what happens:

**1. Initial Page Load (Normal Laravel)**

```
Browser → Laravel → Renders Livewire Component → Returns HTML
```

**2. User Interaction (AJAX)**

```
User types in input
↓
Livewire detects wire:model
↓
Sends AJAX request to Laravel with component state
↓
Laravel re-renders component with new data
↓
Livewire updates ONLY the changed parts of the DOM
```

**Key Points:**

- Component state is maintained on the server
- Only diffs are sent back (efficient)
- DOM updates are surgical (fast)
- You write PHP, Livewire handles AJAX

### What Livewire Is NOT

- **Not a JavaScript framework** - It's a Laravel package
- **Not replacing Alpine** - They work together (TALL stack)
- **Not for everything** - Small interactions use Alpine
- **Not magic** - Uses standard Laravel + AJAX

---

## The TALL Stack

You've been building toward this moment!

### T - Tailwind CSS

**What**: Utility-first CSS framework
**Purpose**: Rapid UI development
**You Learned**: Module 02

```html
<button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
    Click Me
</button>
```

### A - Alpine.js

**What**: Lightweight JavaScript framework
**Purpose**: Small interactions (dropdowns, modals, toggles)
**You Learned**: Module 13

```html
<div x-data="{ open: false }">
    <button @click="open = !open">Toggle</button>
    <div x-show="open">Content</div>
</div>
```

### L - Laravel

**What**: PHP framework
**Purpose**: Backend, routing, database, auth
**You Learned**: Modules 14-16

```php
Route::get('/users', [UserController::class, 'index']);
```

### L - Livewire

**What**: Full-stack framework for Laravel
**Purpose**: Reactive components without writing JavaScript
**You're Learning**: This module!

```php
class Counter extends Component
{
    public $count = 0;

    public function increment()
    {
        $this->count++;
    }
}
```

### How They Work Together

**Alpine**: Handles client-side interactions (no server needed)
- Dropdown menus
- Modal open/close
- Tab switching
- Tooltips

**Livewire**: Handles server-side interactions (needs database/Laravel)
- Forms with validation
- Search functionality
- Data tables with pagination
- Shopping carts

**Together**: Ultimate power!

```html
<!-- Alpine handles the modal, Livewire handles the form -->
<div x-data="{ showModal: false }">
    <button @click="showModal = true">Create User</button>

    <div x-show="showModal" x-cloak>
        <livewire:user-form @saved="showModal = false" />
    </div>
</div>
```

---

## Installing Livewire

### Step 1: Install via Composer

```bash
cd /Users/pouget/Projects/cours-kieu/17-laravel-livewire
composer require livewire/livewire
```

### Step 2: Publish Config (Optional)

```bash
php artisan livewire:publish --config
```

This creates `config/livewire.php` where you can customize:
- Component namespace
- View path
- Layout
- Asset URL

### Step 3: Add Livewire Assets

In your layout (`resources/views/layouts/app.blade.php`):

```html
<!DOCTYPE html>
<html>
<head>
    <title>My App</title>

    <!-- Tailwind CSS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Livewire Styles (before closing </head>) -->
    @livewireStyles
</head>
<body>
    {{ $slot }}

    <!-- Livewire Scripts (before closing </body>) -->
    @livewireScripts
</body>
</html>
```

**Important**:
- `@livewireStyles` in `<head>`
- `@livewireScripts` before `</body>`

### What These Directives Do

**@livewireStyles**: Injects Livewire's CSS
- Loading indicators
- Validation styles
- Component styles

**@livewireScripts**: Injects Livewire's JavaScript
- Handles AJAX requests
- Updates DOM
- Manages component state

---

## Your First Livewire Component

Let's build a simple counter to understand the basics.

### Step 1: Create the Component

```bash
php artisan make:livewire Counter
```

This creates TWO files:

**1. `app/Livewire/Counter.php`** (The PHP class)

```php
<?php

namespace App\Livewire;

use Livewire\Component;

class Counter extends Component
{
    public function render()
    {
        return view('livewire.counter');
    }
}
```

**2. `resources/views/livewire/counter.blade.php`** (The view)

```html
<div>
    {{-- Your component content --}}
</div>
```

### Step 2: Add Functionality

**Edit `app/Livewire/Counter.php`:**

```php
<?php

namespace App\Livewire;

use Livewire\Component;

class Counter extends Component
{
    // Public properties are available in the view
    public $count = 0;

    // Public methods can be called from the view
    public function increment()
    {
        $this->count++;
    }

    public function decrement()
    {
        $this->count--;
    }

    public function render()
    {
        return view('livewire.counter');
    }
}
```

**Edit `resources/views/livewire/counter.blade.php`:**

```html
<div class="p-6 max-w-sm mx-auto bg-white rounded-xl shadow-md">
    <h2 class="text-2xl font-bold mb-4">Counter: {{ $count }}</h2>

    <div class="flex gap-2">
        <button
            wire:click="increment"
            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
        >
            +
        </button>

        <button
            wire:click="decrement"
            class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded"
        >
            -
        </button>
    </div>
</div>
```

### Step 3: Use the Component

In any Blade view or route:

```html
<!-- As a tag -->
<livewire:counter />

<!-- Or as a directive -->
@livewire('counter')
```

### Step 4: Test It

Create a route in `routes/web.php`:

```php
Route::get('/counter', function () {
    return view('counter-page');
});
```

Create `resources/views/counter-page.blade.php`:

```html
<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <livewire:counter />
        </div>
    </div>
</x-app-layout>
```

Visit `http://127.0.0.1/counter` and click the buttons!

---

## Understanding the Component Lifecycle

When you click the increment button:

**1. User Clicks Button**
```
wire:click="increment" triggers
```

**2. Livewire Sends AJAX Request**
```json
{
    "fingerprint": {...},
    "serverMemo": {
        "data": {
            "count": 5
        }
    },
    "updates": [
        {
            "type": "callMethod",
            "payload": {
                "method": "increment"
            }
        }
    ]
}
```

**3. Laravel Processes Request**
- Recreates component from state
- Calls `increment()` method
- Re-renders component

**4. Livewire Sends Response**
```json
{
    "effects": {
        "html": "<div>...(only changed parts)...</div>"
    },
    "serverMemo": {
        "data": {
            "count": 6
        }
    }
}
```

**5. DOM Updates**
- JavaScript updates only `{{ $count }}`
- No full page reload
- Smooth and fast

---

## Key Livewire Concepts

### 1. Public Properties

```php
class MyComponent extends Component
{
    public $name = 'John';        // Available in view
    protected $email = 'a@b.com'; // NOT available in view
    private $secret = '123';      // NOT available in view
}
```

**In view:**
```html
<div>
    {{ $name }} <!-- Works -->
    {{ $email }} <!-- Error: undefined variable -->
</div>
```

### 2. Wire Directives

Common directives you'll use:

**wire:click** - Trigger method on click
```html
<button wire:click="save">Save</button>
```

**wire:model** - Two-way data binding
```html
<input type="text" wire:model="name">
```

**wire:submit** - Handle form submission
```html
<form wire:submit="save">
    <!-- form fields -->
</form>
```

We'll explore more in upcoming lessons!

### 3. Component Root Element

**Important**: Every Livewire component view must have a SINGLE root element:

```html
<!-- ✅ GOOD -->
<div>
    <h1>Title</h1>
    <p>Content</p>
</div>

<!-- ❌ BAD -->
<h1>Title</h1>
<p>Content</p>

<!-- ❌ BAD -->
<div></div>
<div></div>
```

This is required for Livewire to track the component in the DOM.

---

## Livewire vs Traditional Approach

Let's compare building a search feature:

### Traditional (Laravel + AJAX)

**Route:**
```php
Route::get('/users/search', [UserController::class, 'search']);
```

**Controller:**
```php
public function search(Request $request)
{
    $users = User::where('name', 'like', "%{$request->search}%")->get();
    return response()->json($users);
}
```

**JavaScript:**
```javascript
const searchInput = document.querySelector('#search');
searchInput.addEventListener('input', async (e) => {
    const response = await fetch(`/users/search?search=${e.target.value}`);
    const users = await response.json();
    updateUsersList(users); // Need to write this function
});
```

**Total**: 3 files, ~40 lines of code

### With Livewire

**Component:**
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

**View:**
```html
<div>
    <input type="text" wire:model.live="search">

    @foreach($users as $user)
        <div>{{ $user->name }}</div>
    @endforeach
</div>
```

**Total**: 2 files, ~15 lines of code

**Benefits:**
- 60% less code
- No JavaScript to write
- No API routes needed
- Easier to maintain
- More readable

---

## When to Use Livewire

### ✅ Perfect for Livewire:

- Forms with validation
- CRUD operations
- Search and filtering
- Data tables with pagination
- Shopping carts
- Dashboards
- Any feature needing server data

### ❌ Better with Alpine:

- Dropdowns and menus
- Modals open/close
- Tab switching
- Tooltips
- Animations
- Anything purely client-side

### 💡 Use Both!

Most real applications use Alpine for UI interactions and Livewire for data operations.

---

## Quick Quiz

**Question 1**: What two directives must you include in your layout for Livewire to work?

<details>
<summary>Show Answer</summary>

`@livewireStyles` in the `<head>` and `@livewireScripts` before `</body>`.

</details>

**Question 2**: What's wrong with this component view?

```html
<h1>Hello</h1>
<p>World</p>
```

<details>
<summary>Show Answer</summary>

It has two root elements. Livewire components need a single root element:
```html
<div>
    <h1>Hello</h1>
    <p>World</p>
</div>
```

</details>

**Question 3**: Which property is available in the view?

```php
class MyComponent extends Component
{
    public $name = 'John';
    protected $email = 'test@test.com';
}
```

<details>
<summary>Show Answer</summary>

Only `$name` (public properties). Protected and private properties are not available in views.

</details>

---

## Practice Exercise

Create a simple "Hello Name" component:

**Requirements:**
1. Input field for name
2. Display "Hello [name]!" below
3. Updates as you type
4. If name is empty, show "Hello stranger!"

**Steps:**
1. `php artisan make:livewire HelloName`
2. Add a public `$name` property
3. Create the view with input and greeting
4. Use `wire:model.live` for real-time updates

Try it yourself before looking at the solution!

<details>
<summary>Show Solution</summary>

**app/Livewire/HelloName.php:**
```php
<?php

namespace App\Livewire;

use Livewire\Component;

class HelloName extends Component
{
    public $name = '';

    public function render()
    {
        return view('livewire.hello-name');
    }
}
```

**resources/views/livewire/hello-name.blade.php:**
```html
<div class="p-6 max-w-sm mx-auto bg-white rounded-xl shadow-md">
    <label class="block mb-2 text-sm font-medium text-gray-700">
        Your Name:
    </label>
    <input
        type="text"
        wire:model.live="name"
        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
        placeholder="Enter your name"
    >

    <div class="mt-4 text-xl font-bold text-gray-800">
        @if($name)
            Hello {{ $name }}!
        @else
            Hello stranger!
        @endif
    </div>
</div>
```

</details>

---

## Summary

In this lesson, you learned:

- ✅ What Livewire is and why it's powerful
- ✅ How Livewire works (AJAX + DOM diffing)
- ✅ The complete TALL stack
- ✅ How to install Livewire
- ✅ Creating your first component
- ✅ Component lifecycle basics
- ✅ When to use Livewire vs Alpine

**Next Lesson**: Creating Components - dive deep into component structure, naming conventions, and organization!

---

## Additional Resources

- [Livewire Documentation](https://livewire.laravel.com)
- [Livewire Screencasts](https://laravel-livewire.com/screencasts)
- [TALL Stack Tutorial](https://tallstack.dev)

---

**You're now ready to build reactive applications without leaving PHP! Let's continue...**
