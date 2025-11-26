# Lesson 4: Actions and Methods

**Duration**: 60 minutes
**Prerequisites**: Lesson 3 completed

---

## What You'll Learn

- Calling methods from the view
- Method parameters and data
- Event modifiers (prevent, stop, etc.)
- Loading states
- Confirming actions
- Magic actions
- Lifecycle methods

---

## Calling Methods from the View

In Livewire, you can call public methods from your view using `wire:` directives.

### Basic Method Call: wire:click

```php
class Counter extends Component
{
    public $count = 0;

    public function increment()
    {
        $this->count++;
    }

    public function decrement()
    {
        $this->count--;
    }

    public function reset()
    {
        $this->count = 0;
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
    <button wire:click="decrement">-</button>
    <button wire:click="reset">Reset</button>
</div>
```

**How it works:**
1. User clicks button
2. Livewire sends AJAX request
3. Method executes on server
4. Component re-renders
5. DOM updates

### Other Event Listeners

```html
<!-- Click -->
<button wire:click="doSomething">Click Me</button>

<!-- Submit -->
<form wire:submit="save">
    <button type="submit">Save</button>
</form>

<!-- Mouse events -->
<div wire:mouseenter="handleMouseEnter">Hover me</div>
<div wire:mouseleave="handleMouseLeave">Leave me</div>

<!-- Keyboard events -->
<input wire:keydown.enter="search" placeholder="Press Enter">
<input wire:keydown.escape="cancel" placeholder="Press Esc">

<!-- Input events -->
<input wire:input="handleInput" placeholder="Type something">
<input wire:change="handleChange" placeholder="Change me">

<!-- Focus events -->
<input wire:focus="handleFocus" placeholder="Focus me">
<input wire:blur="handleBlur" placeholder="Blur me">
```

---

## Passing Parameters to Methods

### Literal Values

```html
<button wire:click="addItem('Product A')">Add Product A</button>
<button wire:click="addItem('Product B')">Add Product B</button>
```

```php
public function addItem($productName)
{
    $this->cart[] = $productName;
}
```

### Variables

```html
@foreach($products as $product)
    <button wire:click="addToCart({{ $product->id }})">
        Add to Cart
    </button>
@endforeach
```

```php
public function addToCart($productId)
{
    $product = Product::findOrFail($productId);

    $this->cart[] = [
        'id' => $product->id,
        'name' => $product->name,
        'price' => $product->price
    ];
}
```

### Multiple Parameters

```html
<button wire:click="updateQuantity({{ $item->id }}, 5)">
    Set quantity to 5
</button>
```

```php
public function updateQuantity($itemId, $quantity)
{
    CartItem::where('id', $itemId)->update([
        'quantity' => $quantity
    ]);
}
```

### Named Parameters

```html
<button wire:click="createUser(name: 'John', email: 'john@example.com')">
    Create John
</button>
```

```php
public function createUser($name, $email)
{
    User::create([
        'name' => $name,
        'email' => $email
    ]);
}
```

---

## Event Modifiers

Modify how events behave:

### prevent - Prevent Default

```html
<!-- Prevent form from submitting normally -->
<form wire:submit.prevent="save">
    <button type="submit">Save</button>
</form>

<!-- Prevent link from navigating -->
<a href="/users" wire:click.prevent="showModal">View Users</a>
```

**Without `.prevent`:**
```html
<form wire:submit="save">
    <!-- Form submits normally AND runs the method (double submit!) -->
</form>
```

### stop - Stop Propagation

```html
<div wire:click="parentClicked">
    Parent
    <button wire:click.stop="childClicked">
        Child (won't trigger parent)
    </button>
</div>
```

**Without `.stop`:**
Clicking child button would trigger both `childClicked()` AND `parentClicked()`.

### self - Only if Event Target is Element Itself

```html
<div wire:click.self="closeModal" class="modal-backdrop">
    <div class="modal-content">
        <!-- Clicking here won't close modal -->
        <p>Content</p>
    </div>
    <!-- Clicking backdrop closes modal -->
</div>
```

### once - Only Fire Once

```html
<button wire:click.once="initialize">
    Initialize (only works once)
</button>
```

### debounce - Wait Before Firing

```html
<!-- Wait 500ms after last click -->
<button wire:click.debounce.500ms="search">Search</button>
```

### throttle - Limit Fire Rate

```html
<!-- Maximum once per second -->
<button wire:click.throttle.1000ms="track">Track Event</button>
```

### Combining Modifiers

```html
<form wire:submit.prevent>
    <input wire:keydown.enter.prevent="search">
</form>
```

---

## Form Submission

### Basic Form

```php
class CreatePost extends Component
{
    public $title = '';
    public $content = '';

    public function save()
    {
        Post::create([
            'title' => $this->title,
            'content' => $this->content
        ]);

        session()->flash('message', 'Post created!');

        return redirect()->route('posts.index');
    }

    public function render()
    {
        return view('livewire.create-post');
    }
}
```

```html
<div>
    <form wire:submit="save">
        <div>
            <label>Title</label>
            <input type="text" wire:model="title">
        </div>

        <div>
            <label>Content</label>
            <textarea wire:model="content"></textarea>
        </div>

        <button type="submit">Create Post</button>
    </form>
</div>
```

**Important**: Use `wire:submit` not `wire:submit.prevent`. Livewire handles prevention automatically!

---

## Loading States

Show feedback while actions are processing:

### wire:loading

```html
<button wire:click="save">
    Save Post
</button>

<!-- Shows while save() is running -->
<div wire:loading>
    Saving...
</div>
```

### Target Specific Actions

```html
<button wire:click="save">Save</button>
<button wire:click="delete">Delete</button>

<!-- Only shows while save() runs -->
<div wire:loading wire:target="save">
    Saving...
</div>

<!-- Only shows while delete() runs -->
<div wire:loading wire:target="delete">
    Deleting...
</div>
```

### Loading with remove/class

```html
<!-- Hide element while loading -->
<div wire:loading.remove>
    Click the button...
</div>

<!-- Add class while loading -->
<button
    wire:click="save"
    wire:loading.class="opacity-50 cursor-not-allowed"
>
    Save
</button>

<!-- Remove class while loading -->
<button
    wire:click="save"
    wire:loading.class.remove="bg-blue-500"
>
    Save
</button>
```

### Common Pattern: Disabled Button

```html
<button
    wire:click="save"
    wire:loading.attr="disabled"
    wire:loading.class="opacity-50 cursor-not-allowed"
>
    <span wire:loading.remove wire:target="save">Save Post</span>
    <span wire:loading wire:target="save">Saving...</span>
</button>
```

### Delay Loading Indicator

Only show if action takes longer than X ms:

```html
<!-- Only show if save() takes longer than 200ms -->
<div wire:loading.delay.200ms wire:target="save">
    Saving...
</div>
```

**Why?** Don't flash loading states for fast operations.

---

## Confirming Actions

### Basic Confirmation

```html
<button
    wire:click="delete"
    wire:confirm="Are you sure you want to delete this?"
>
    Delete
</button>
```

Livewire shows native browser confirm dialog.

### Custom Confirmation Messages

```html
<button
    wire:click="delete({{ $post->id }})"
    wire:confirm="Are you sure you want to delete '{{ $post->title }}'?"
>
    Delete
</button>
```

### Better UX: Modal Confirmation

```html
<div x-data="{ confirmDelete: false }">
    <button @click="confirmDelete = true">
        Delete Post
    </button>

    <!-- Confirmation Modal -->
    <div x-show="confirmDelete" x-cloak class="modal">
        <div class="modal-content">
            <h2>Are you sure?</h2>
            <p>This action cannot be undone.</p>

            <div class="flex gap-2">
                <button
                    @click="confirmDelete = false"
                    class="btn-secondary"
                >
                    Cancel
                </button>
                <button
                    wire:click="delete"
                    @click="confirmDelete = false"
                    class="btn-danger"
                >
                    Yes, Delete
                </button>
            </div>
        </div>
    </div>
</div>
```

---

## Magic Actions

Livewire provides some built-in "magic" methods:

### $refresh

Re-render the component without calling a method:

```html
<button wire:click="$refresh">
    Refresh Component
</button>
```

**Use case**: Refresh data after time passes.

### $set

Set a property value directly:

```html
<button wire:click="$set('count', 0)">
    Reset Count
</button>

<!-- Instead of creating a method -->
```

```php
// No need for:
public function resetCount()
{
    $this->count = 0;
}
```

### $toggle

Toggle a boolean property:

```html
<button wire:click="$toggle('showDetails')">
    Toggle Details
</button>

@if($showDetails)
    <div>Details content...</div>
@endif
```

### Combining with Alpine

```html
<div x-data="{ open: false }">
    <!-- Alpine handles modal open/close (client-side) -->
    <button @click="open = true">New Post</button>

    <div x-show="open" x-cloak>
        <form wire:submit="save">
            <!-- Livewire handles form (server-side) -->
            <input wire:model="title">
            <button type="submit">Save</button>
        </form>

        <!-- Close modal after save -->
        <button @click="open = false">Cancel</button>
    </div>
</div>
```

---

## Dispatching Events

Components can communicate via events:

### Dispatch from Component

```php
class CreatePost extends Component
{
    public $title = '';

    public function save()
    {
        $post = Post::create(['title' => $this->title]);

        // Dispatch event
        $this->dispatch('post-created', postId: $post->id);

        $this->reset('title');
    }
}
```

### Listen in Another Component

```php
use Livewire\Attributes\On;

class PostList extends Component
{
    #[On('post-created')]
    public function handlePostCreated($postId)
    {
        // Refresh the list
        // (render will be called automatically)
    }

    public function render()
    {
        return view('livewire.post-list', [
            'posts' => Post::latest()->get()
        ]);
    }
}
```

### Listen in Alpine

```html
<div x-data="{ notification: '' }" @post-created.window="notification = 'Post created!'">
    <div x-show="notification" x-text="notification"></div>
</div>
```

### Dispatch to Specific Component

```php
// To component by name
$this->dispatch('refresh')->to(PostList::class);

// To self (current component)
$this->dispatch('refresh')->self();
```

---

## Return Types

What can methods return?

### Nothing (Most Common)

```php
public function save()
{
    Post::create([...]);
    // Component re-renders automatically
}
```

### Redirect

```php
public function save()
{
    Post::create([...]);

    return redirect()->route('posts.index');
}
```

### Redirect with Flash Message

```php
public function save()
{
    Post::create([...]);

    session()->flash('message', 'Post created successfully!');

    return redirect()->route('posts.index');
}
```

### Download

```php
public function export()
{
    $csv = $this->generateCsv();

    return response()->streamDownload(function () use ($csv) {
        echo $csv;
    }, 'export.csv');
}
```

---

## Lifecycle Hooks

Methods that run automatically at specific times:

### mount()

Runs once when component is first loaded:

```php
public function mount($userId)
{
    $this->user = User::findOrFail($userId);
    $this->loadData();
}
```

**Use for:**
- Initialization
- Loading initial data
- Authorization checks

### hydrate()

Runs before EVERY request (including subsequent updates):

```php
public function hydrate()
{
    $this->authorize('view', $this->user);
}
```

**Use for:**
- Authorization on every request
- Setting up listeners
- Logging

### updated()

Runs after ANY property updates:

```php
public function updated($property, $value)
{
    $this->validate(); // Validate all fields on any change
}
```

### updatedPropertyName()

Runs after specific property updates:

```php
public function updatedSearch()
{
    $this->resetPage(); // Reset pagination when search changes
}
```

### dehydrate()

Runs after render, before response sent:

```php
public function dehydrate()
{
    // Log component state
    Log::info('Component state', $this->all());
}
```

**Rarely used.**

---

## Real-World Patterns

### 1. Delete with Confirmation

```php
class ManagePosts extends Component
{
    public $posts;

    public function mount()
    {
        $this->posts = Post::all();
    }

    public function delete($postId)
    {
        Post::findOrFail($postId)->delete();

        $this->posts = Post::all(); // Refresh

        session()->flash('message', 'Post deleted!');
    }

    public function render()
    {
        return view('livewire.manage-posts');
    }
}
```

```html
<div>
    @if(session('message'))
        <div class="alert-success">{{ session('message') }}</div>
    @endif

    @foreach($posts as $post)
        <div class="flex justify-between items-center">
            <h3>{{ $post->title }}</h3>
            <button
                wire:click="delete({{ $post->id }})"
                wire:confirm="Delete '{{ $post->title }}'?"
                wire:loading.class="opacity-50"
                wire:target="delete({{ $post->id }})"
            >
                <span wire:loading.remove wire:target="delete({{ $post->id }})">
                    Delete
                </span>
                <span wire:loading wire:target="delete({{ $post->id }})">
                    Deleting...
                </span>
            </button>
        </div>
    @endforeach
</div>
```

### 2. Inline Edit

```php
class TaskList extends Component
{
    public $tasks;
    public $editingTask = null;
    public $editTitle = '';

    public function mount()
    {
        $this->tasks = Task::all();
    }

    public function edit($taskId)
    {
        $this->editingTask = $taskId;
        $this->editTitle = Task::find($taskId)->title;
    }

    public function update()
    {
        Task::find($this->editingTask)->update([
            'title' => $this->editTitle
        ]);

        $this->editingTask = null;
        $this->tasks = Task::all();
    }

    public function cancel()
    {
        $this->editingTask = null;
    }

    public function render()
    {
        return view('livewire.task-list');
    }
}
```

```html
<div>
    @foreach($tasks as $task)
        <div>
            @if($editingTask === $task->id)
                <!-- Edit mode -->
                <form wire:submit="update">
                    <input wire:model="editTitle" autofocus>
                    <button type="submit">Save</button>
                    <button type="button" wire:click="cancel">Cancel</button>
                </form>
            @else
                <!-- View mode -->
                <span>{{ $task->title }}</span>
                <button wire:click="edit({{ $task->id }})">Edit</button>
            @endif
        </div>
    @endforeach
</div>
```

### 3. Multi-Step Action

```php
class ProcessOrder extends Component
{
    public $orderId;
    public $step = 'confirm'; // confirm -> processing -> complete

    public function confirm()
    {
        $this->step = 'processing';
        $this->processPayment();
    }

    public function processPayment()
    {
        // Simulate payment processing
        sleep(2);

        $order = Order::find($this->orderId);
        $order->markAsPaid();

        $this->step = 'complete';

        $this->dispatch('order-completed', orderId: $this->orderId);
    }

    public function render()
    {
        return view('livewire.process-order');
    }
}
```

```html
<div>
    @if($step === 'confirm')
        <h2>Confirm your order</h2>
        <button wire:click="confirm">Confirm & Pay</button>
    @elseif($step === 'processing')
        <h2>Processing payment...</h2>
        <div wire:loading>
            <spinner />
        </div>
    @else
        <h2>Order complete!</h2>
        <p>Thank you for your purchase.</p>
    @endif
</div>
```

---

## Quick Quiz

**Question 1**: What's wrong here?

```html
<form wire:submit.prevent="save">
```

<details>
<summary>Show Answer</summary>

Don't use `.prevent` with `wire:submit`. Livewire handles it automatically:

```html
<form wire:submit="save">
```

</details>

**Question 2**: How do you show a loading state for a specific method?

<details>
<summary>Show Answer</summary>

```html
<button wire:click="save">Save</button>
<div wire:loading wire:target="save">Saving...</div>
```

</details>

**Question 3**: What does this do?

```html
<button wire:click="$toggle('showMore')">
```

<details>
<summary>Show Answer</summary>

Toggles the boolean `$showMore` property between true/false without needing a dedicated method.

</details>

---

## Practice Exercise

Create a **Todo List** with these actions:

**Requirements:**
1. Add todo (with input validation)
2. Toggle complete/incomplete
3. Delete todo (with confirmation)
4. Edit todo inline
5. Show loading states
6. Flash success messages

Try building it yourself!

<details>
<summary>Show Solution</summary>

```php
<?php

namespace App\Livewire;

use Livewire\Component;

class TodoList extends Component
{
    public $todos = [];
    public $newTodo = '';
    public $editingIndex = null;
    public $editText = '';

    public function mount()
    {
        $this->todos = [
            ['text' => 'Learn Livewire', 'completed' => false],
            ['text' => 'Build an app', 'completed' => false],
        ];
    }

    public function addTodo()
    {
        $this->validate(['newTodo' => 'required|min:3']);

        $this->todos[] = [
            'text' => $this->newTodo,
            'completed' => false
        ];

        $this->reset('newTodo');

        session()->flash('message', 'Todo added!');
    }

    public function toggleComplete($index)
    {
        $this->todos[$index]['completed'] = !$this->todos[$index]['completed'];
    }

    public function startEdit($index)
    {
        $this->editingIndex = $index;
        $this->editText = $this->todos[$index]['text'];
    }

    public function updateTodo()
    {
        $this->validate(['editText' => 'required|min:3']);

        $this->todos[$this->editingIndex]['text'] = $this->editText;
        $this->cancelEdit();

        session()->flash('message', 'Todo updated!');
    }

    public function cancelEdit()
    {
        $this->editingIndex = null;
        $this->editText = '';
    }

    public function deleteTodo($index)
    {
        unset($this->todos[$index]);
        $this->todos = array_values($this->todos);

        session()->flash('message', 'Todo deleted!');
    }

    public function render()
    {
        return view('livewire.todo-list');
    }
}
```

```html
<div class="max-w-2xl mx-auto p-6">
    <!-- Flash Message -->
    @if(session('message'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('message') }}
        </div>
    @endif

    <!-- Add Todo Form -->
    <form wire:submit="addTodo" class="mb-6">
        <div class="flex gap-2">
            <input
                type="text"
                wire:model="newTodo"
                placeholder="New todo..."
                class="flex-1 px-4 py-2 border rounded"
            >
            <button
                type="submit"
                wire:loading.attr="disabled"
                class="bg-blue-500 text-white px-6 py-2 rounded"
            >
                <span wire:loading.remove wire:target="addTodo">Add</span>
                <span wire:loading wire:target="addTodo">Adding...</span>
            </button>
        </div>
        @error('newTodo')
            <span class="text-red-500 text-sm">{{ $message }}</span>
        @enderror
    </form>

    <!-- Todo List -->
    <div class="space-y-2">
        @foreach($todos as $index => $todo)
            <div class="flex items-center gap-2 bg-white p-4 rounded shadow">
                @if($editingIndex === $index)
                    <!-- Edit Mode -->
                    <form wire:submit="updateTodo" class="flex-1 flex gap-2">
                        <input
                            type="text"
                            wire:model="editText"
                            class="flex-1 px-2 py-1 border rounded"
                            autofocus
                        >
                        <button type="submit" class="text-green-600">Save</button>
                        <button type="button" wire:click="cancelEdit" class="text-gray-600">Cancel</button>
                    </form>
                    @error('editText')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                @else
                    <!-- View Mode -->
                    <input
                        type="checkbox"
                        wire:click="toggleComplete({{ $index }})"
                        @checked($todo['completed'])
                    >
                    <span class="flex-1 {{ $todo['completed'] ? 'line-through text-gray-500' : '' }}">
                        {{ $todo['text'] }}
                    </span>
                    <button
                        wire:click="startEdit({{ $index }})"
                        class="text-blue-600 text-sm"
                    >
                        Edit
                    </button>
                    <button
                        wire:click="deleteTodo({{ $index }})"
                        wire:confirm="Are you sure?"
                        wire:loading.class="opacity-50"
                        wire:target="deleteTodo({{ $index }})"
                        class="text-red-600 text-sm"
                    >
                        Delete
                    </button>
                @endif
            </div>
        @endforeach

        @if(empty($todos))
            <p class="text-center text-gray-500 py-8">No todos yet. Add one!</p>
        @endif
    </div>
</div>
```

</details>

---

## Summary

You learned:

- ✅ Calling methods with wire:click, wire:submit, etc.
- ✅ Passing parameters to methods
- ✅ Event modifiers (prevent, stop, self, etc.)
- ✅ Loading states and targets
- ✅ Confirming actions
- ✅ Magic actions ($refresh, $set, $toggle)
- ✅ Dispatching and listening to events
- ✅ Lifecycle hooks

**Next Lesson**: Forms & Validation - build robust forms with real-time validation!

---

**Actions bring your components to life. Next, we'll handle forms like pros!**
