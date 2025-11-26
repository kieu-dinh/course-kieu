# 01 - First Livewire Component

## Objective
Create your first Livewire component to understand reactive UI development with Laravel and Livewire.

## Prerequisites
- Completed Module 14 exercises
- Understanding of Laravel views and controllers
- Basic knowledge of event-driven programming

## Instructions

### Step 1: Create a New Laravel Project
```bash
composer create-project laravel/laravel livewire-app
cd livewire-app
```

### Step 2: Install Livewire
```bash
composer require livewire/livewire
```

If using Blade (not Inertia):

```bash
php artisan livewire:install --blade
```

### Step 3: Publish Livewire Assets
```bash
php artisan livewire:publish
```

### Step 4: Create Your First Component
```bash
php artisan make:livewire Counter
```

This creates:
- `app/Livewire/Counter.php` - The component class
- `resources/views/livewire/counter.blade.php` - The view

### Step 5: Edit the Component Class
In `app/Livewire/Counter.php`:

```php
namespace App\Livewire;

use Livewire\Component;

class Counter extends Component
{
    public int $count = 0;

    public function increment(): void
    {
        $this->count++;
    }

    public function decrement(): void
    {
        $this->count--;
    }

    public function reset(): void
    {
        $this->count = 0;
    }

    public function render()
    {
        return view('livewire.counter');
    }
}
```

### Step 6: Create the Component View
In `resources/views/livewire/counter.blade.php`:

```blade
<div class="counter p-6 bg-white rounded shadow-lg">
    <h2 class="text-2xl font-bold mb-4">Counter</h2>

    <div class="text-4xl font-bold text-blue-500 mb-6">
        {{ $count }}
    </div>

    <div class="flex gap-2">
        <button wire:click="decrement" class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600">
            -
        </button>

        <button wire:click="reset" class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">
            Reset
        </button>

        <button wire:click="increment" class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600">
            +
        </button>
    </div>
</div>
```

### Step 7: Create a Blade Layout
Create `resources/views/layouts/app.blade.php`:

```blade
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Livewire Demo</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @livewireStyles
</head>
<body class="bg-gray-100">
    <div class="container mx-auto py-8">
        @yield('content')
    </div>

    @livewireScripts
</body>
</html>
```

### Step 8: Create a Route
In `routes/web.php`:

```php
use App\Livewire\Counter;

Route::get('/', Counter::class);
```

### Step 9: Run the Application
```bash
php artisan serve
```

Visit `http://localhost:8000` and click the buttons to see reactivity!

### Step 10: Understand Component Lifecycle
Add lifecycle hooks to `Counter.php`:

```php
public function mount()
{
    // Called when component is first instantiated
    logger()->info('Counter component mounted');
}

public function updated($property, $value)
{
    // Called when any property changes
    logger()->info("Property {$property} changed to {$value}");
}

public function updatedCount($value)
{
    // Called when $count specifically changes
    logger()->info("Count is now {$value}");
}
```

### Step 11: Create a Todo Tracker Component
```bash
php artisan make:livewire TodoList
```

In `app/Livewire/TodoList.php`:

```php
namespace App\Livewire;

use Livewire\Component;

class TodoList extends Component
{
    public array $todos = [];
    public string $input = '';

    public function addTodo(): void
    {
        if (trim($this->input) === '') {
            return;
        }

        $this->todos[] = [
            'id' => uniqid(),
            'text' => $this->input,
            'completed' => false,
        ];

        $this->input = '';
    }

    public function removeTodo(string $id): void
    {
        $this->todos = array_filter($this->todos, fn($todo) => $todo['id'] !== $id);
    }

    public function toggleTodo(string $id): void
    {
        foreach ($this->todos as &$todo) {
            if ($todo['id'] === $id) {
                $todo['completed'] = !$todo['completed'];
            }
        }
    }

    public function clearCompleted(): void
    {
        $this->todos = array_filter($this->todos, fn($todo) => !$todo['completed']);
    }

    public function render()
    {
        return view('livewire.todo-list');
    }
}
```

In `resources/views/livewire/todo-list.blade.php`:

```blade
<div class="w-full max-w-md mx-auto p-6 bg-white rounded shadow-lg">
    <h2 class="text-2xl font-bold mb-4">My Todo List</h2>

    <div class="flex gap-2 mb-4">
        <input type="text"
               wire:model="input"
               wire:keydown.enter="addTodo"
               placeholder="Add a new todo..."
               class="flex-1 px-3 py-2 border rounded">
        <button wire:click="addTodo"
                class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
            Add
        </button>
    </div>

    @if(count($todos) > 0)
        <ul class="space-y-2 mb-4">
            @foreach($todos as $todo)
                <li class="flex items-center gap-2 p-2 bg-gray-50 rounded">
                    <input type="checkbox"
                           wire:change="toggleTodo('{{ $todo['id'] }}')"
                           @checked($todo['completed'])
                           class="w-4 h-4">

                    <span class="flex-1 @if($todo['completed']) line-through text-gray-400 @endif">
                        {{ $todo['text'] }}
                    </span>

                    <button wire:click="removeTodo('{{ $todo['id'] }}')"
                            class="text-red-500 hover:text-red-700">
                        Delete
                    </button>
                </li>
            @endforeach
        </ul>

        <div class="text-sm text-gray-600 mb-4">
            {{ count(array_filter($todos, fn($t) => !$t['completed'])) }} remaining
        </div>

        @if(count(array_filter($todos, fn($t) => $t['completed'])) > 0)
            <button wire:click="clearCompleted"
                    class="w-full px-4 py-2 bg-orange-500 text-white rounded hover:bg-orange-600">
                Clear Completed
            </button>
        @endif
    @else
        <p class="text-gray-500 text-center py-4">No todos yet. Add one above!</p>
    @endif
</div>
```

### Step 12: Understand Data Binding
Create `app/Livewire/FormExample.php`:

```php
namespace App\Livewire;

use Livewire\Component;

class FormExample extends Component
{
    public string $name = '';
    public string $email = '';
    public string $message = '';

    public function submit(): void
    {
        // Your form handling logic
        logger()->info("Form submitted: {$this->name}, {$this->email}");
    }

    public function render()
    {
        return view('livewire.form-example');
    }
}
```

View `resources/views/livewire/form-example.blade.php`:

```blade
<div class="w-full max-w-md mx-auto p-6 bg-white rounded shadow-lg">
    <h2 class="text-2xl font-bold mb-4">Contact Form</h2>

    <form wire:submit="submit" class="space-y-4">
        <div>
            <label for="name" class="block font-bold mb-2">Name</label>
            <input type="text"
                   id="name"
                   wire:model="name"
                   placeholder="Your name"
                   class="w-full px-3 py-2 border rounded">
        </div>

        <div>
            <label for="email" class="block font-bold mb-2">Email</label>
            <input type="email"
                   id="email"
                   wire:model="email"
                   placeholder="Your email"
                   class="w-full px-3 py-2 border rounded">
        </div>

        <div>
            <label for="message" class="block font-bold mb-2">Message</label>
            <textarea id="message"
                      wire:model="message"
                      placeholder="Your message"
                      rows="5"
                      class="w-full px-3 py-2 border rounded"></textarea>
        </div>

        <div class="text-sm text-gray-600">
            <p>Name: {{ $name ?: 'Not set' }}</p>
            <p>Email: {{ $email ?: 'Not set' }}</p>
            <p>Message length: {{ strlen($message) }}</p>
        </div>

        <button type="submit"
                class="w-full px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
            Submit
        </button>
    </form>
</div>
```

### Step 13: Create a Master Layout Page
Create `resources/views/welcome.blade.php`:

```blade
@extends('layouts.app')

@section('content')
    <div class="max-w-4xl mx-auto">
        <h1 class="text-4xl font-bold mb-8">Livewire Components Demo</h1>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white p-6 rounded shadow-lg">
                <h2 class="text-xl font-bold mb-4">Counter</h2>
                <livewire:counter />
            </div>

            <div class="bg-white p-6 rounded shadow-lg">
                <h2 class="text-xl font-bold mb-4">Todo List</h2>
                <livewire:todo-list />
            </div>

            <div class="bg-white p-6 rounded shadow-lg md:col-span-2">
                <h2 class="text-xl font-bold mb-4">Form Example</h2>
                <livewire:form-example />
            </div>
        </div>
    </div>
@endsection
```

### Step 14: Understand Events
Modify Counter to dispatch events:

```php
public function increment(): void
{
    $this->count++;
    $this->dispatch('counter-updated', count: $this->count);
}
```

Listen for events:

```php
#[On('counter-updated')]
public function onCounterUpdated($count): void
{
    logger()->info("Counter was updated to: {$count}");
}
```

### Step 15: Test Your Components
1. Click the counter buttons
2. Type in todo input
3. Check the form fields update in real-time
4. Verify no page refresh occurs

## Deliverables
- [ ] Livewire installed and configured
- [ ] Counter component created with increment/decrement
- [ ] Counter component renders without page refresh
- [ ] Todo list component working
- [ ] Form example with data binding
- [ ] Multiple components on same page
- [ ] Lifecycle hooks understood
- [ ] Wire directives working (wire:click, wire:model)
- [ ] All interactions reactive

## Resources
- [Livewire Documentation](https://livewire.laravel.com)
- [Livewire Components](https://livewire.laravel.com/docs/components)
- [Wire Directives](https://livewire.laravel.com/docs/properties)
- [Lifecycle Hooks](https://livewire.laravel.com/docs/lifecycle)

## Tips
- Livewire components are just PHP classes
- Use wire:model for two-way data binding
- Use wire:click for method calls
- wire:keydown.enter submits forms on Enter
- Components auto-refresh after any action
- Keep components focused and small
- Test in browser DevTools network tab
- Use logging for debugging
