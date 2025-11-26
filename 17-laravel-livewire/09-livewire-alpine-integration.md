# Lesson 9: Livewire + Alpine Integration

**Duration**: 60 minutes
**Prerequisites**: Lesson 8 completed, Module 13 (Alpine.js) recommended

---

## What You'll Learn

- When to use Alpine vs Livewire
- Combining Alpine and Livewire
- Accessing Livewire from Alpine
- Accessing Alpine from Livewire
- Common patterns and best practices
- Real-world examples

---

## Alpine vs Livewire: The Decision Tree

### Use Alpine When:

✅ **Pure client-side interaction** (no server needed)
- Dropdown menus
- Modal open/close
- Tab switching
- Accordions
- Tooltips
- Toggle visibility
- Client-side animations

**Example:**
```html
<div x-data="{ open: false }">
    <button @click="open = !open">Toggle Menu</button>
    <div x-show="open" x-transition>Menu content</div>
</div>
```

### Use Livewire When:

✅ **Server-side data or logic** (needs database, validation, auth)
- Forms with validation
- CRUD operations
- Search functionality
- Authentication
- File uploads
- Shopping cart
- Anything requiring database

**Example:**
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

### Use Both Together When:

✅ **Complex interactions with server data**
- Modal with server-side form
- Tabs with database content
- Dropdown with server actions
- Animated transitions with data updates

---

## Accessing Livewire from Alpine

### The Magic $wire Property

Alpine can access Livewire properties and methods via `$wire`:

```html
<div x-data="{ /* Alpine data */ }">
    <!-- Access Livewire property -->
    <p x-text="$wire.count"></p>

    <!-- Call Livewire method -->
    <button @click="$wire.increment()">Increment</button>

    <!-- Two-way bind to Livewire property -->
    <input x-model="$wire.search">
</div>
```

### Example: Counter with Alpine Animation

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
}
```

```html
<div x-data="{ flash: false }">
    <div
        class="text-4xl font-bold"
        :class="flash && 'text-green-500'"
        x-text="$wire.count"
    ></div>

    <button
        @click="
            $wire.increment();
            flash = true;
            setTimeout(() => flash = false, 200)
        "
        class="bg-blue-500 text-white px-4 py-2 rounded"
    >
        Increment
    </button>
</div>
```

**What happens:**
1. Alpine calls Livewire's `increment()` method
2. Livewire updates `$count` on server
3. Livewire sends new count back
4. Alpine flashes green for 200ms

**Best of both worlds!**

---

## Common Patterns

### 1. Modal with Livewire Form

Alpine handles modal open/close (client-side), Livewire handles form (server-side):

```html
<div x-data="{ showModal: false }">
    <!-- Open Button -->
    <button
        @click="showModal = true"
        class="bg-blue-500 text-white px-4 py-2 rounded"
    >
        Create Post
    </button>

    <!-- Modal -->
    <div
        x-show="showModal"
        x-cloak
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center"
        @click.self="showModal = false"
    >
        <div class="bg-white rounded-lg p-6 max-w-md w-full">
            <h2 class="text-2xl font-bold mb-4">Create Post</h2>

            <!-- Livewire Form -->
            <form wire:submit="save">
                <div class="mb-4">
                    <label>Title</label>
                    <input wire:model="title" class="w-full px-3 py-2 border rounded">
                    @error('title') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="mb-4">
                    <label>Content</label>
                    <textarea wire:model="content" class="w-full px-3 py-2 border rounded"></textarea>
                    @error('content') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="flex gap-2">
                    <button
                        type="button"
                        @click="showModal = false"
                        class="bg-gray-500 text-white px-4 py-2 rounded"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        class="bg-blue-500 text-white px-4 py-2 rounded"
                    >
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
```

**Component:**
```php
class CreatePost extends Component
{
    public $title = '';
    public $content = '';

    protected function rules()
    {
        return [
            'title' => 'required|min:3',
            'content' => 'required|min:10',
        ];
    }

    public function save()
    {
        $validated = $this->validate();
        Post::create($validated);

        // Tell Alpine to close modal
        $this->dispatch('post-saved')->toJs();

        $this->reset();
    }
}
```

**Close modal after save:**
```html
<div
    x-data="{ showModal: false }"
    @post-saved.window="showModal = false"
>
    <!-- Modal content -->
</div>
```

### 2. Tabs with Database Content

Alpine handles tab switching (instant), Livewire loads content:

```html
<div x-data="{ activeTab: 'posts' }">
    <!-- Tab Buttons -->
    <div class="flex border-b mb-4">
        <button
            @click="activeTab = 'posts'; $wire.loadPosts()"
            :class="activeTab === 'posts' ? 'border-blue-500 text-blue-600' : ''"
            class="px-4 py-2 border-b-2"
        >
            Posts
        </button>
        <button
            @click="activeTab = 'comments'; $wire.loadComments()"
            :class="activeTab === 'comments' ? 'border-blue-500 text-blue-600' : ''"
            class="px-4 py-2 border-b-2"
        >
            Comments
        </button>
        <button
            @click="activeTab = 'likes'; $wire.loadLikes()"
            :class="activeTab === 'likes' ? 'border-blue-500 text-blue-600' : ''"
            class="px-4 py-2 border-b-2"
        >
            Likes
        </button>
    </div>

    <!-- Tab Content -->
    <div x-show="activeTab === 'posts'" x-transition>
        @foreach($posts as $post)
            <div>{{ $post->title }}</div>
        @endforeach
    </div>

    <div x-show="activeTab === 'comments'" x-transition>
        @foreach($comments as $comment)
            <div>{{ $comment->text }}</div>
        @endforeach
    </div>

    <div x-show="activeTab === 'likes'" x-transition>
        <p>{{ count($likes) }} likes</p>
    </div>
</div>
```

```php
class UserProfile extends Component
{
    public $userId;
    public $posts = [];
    public $comments = [];
    public $likes = [];

    public function mount($userId)
    {
        $this->userId = $userId;
        $this->loadPosts(); // Load first tab by default
    }

    public function loadPosts()
    {
        $this->posts = Post::where('user_id', $this->userId)->get();
    }

    public function loadComments()
    {
        $this->comments = Comment::where('user_id', $this->userId)->get();
    }

    public function loadLikes()
    {
        $this->likes = Like::where('user_id', $this->userId)->get();
    }
}
```

### 3. Dropdown with Server Actions

```html
<div x-data="{ open: false }" @click.away="open = false">
    <!-- Trigger -->
    <button @click="open = !open" class="bg-gray-200 px-4 py-2 rounded">
        Actions ▼
    </button>

    <!-- Dropdown Menu -->
    <div
        x-show="open"
        x-transition
        class="absolute mt-2 w-48 bg-white rounded shadow-lg"
    >
        <button
            @click="$wire.edit(); open = false"
            class="block w-full text-left px-4 py-2 hover:bg-gray-100"
        >
            Edit
        </button>
        <button
            @click="$wire.duplicate(); open = false"
            class="block w-full text-left px-4 py-2 hover:bg-gray-100"
        >
            Duplicate
        </button>
        <button
            @click="
                if (confirm('Are you sure?')) {
                    $wire.delete();
                    open = false;
                }
            "
            class="block w-full text-left px-4 py-2 hover:bg-gray-100 text-red-600"
        >
            Delete
        </button>
    </div>
</div>
```

### 4. Live Search with Debounce

Alpine adds smooth UX, Livewire handles search:

```html
<div x-data="{ searching: false }">
    <div class="relative">
        <input
            type="text"
            wire:model.live.debounce.300ms="search"
            @input="searching = true"
            placeholder="Search..."
            class="w-full px-4 py-2 border rounded"
        >

        <!-- Loading spinner (Alpine controlled) -->
        <div
            x-show="searching"
            wire:loading.remove
            class="absolute right-3 top-3"
        >
            <div class="animate-spin">⏳</div>
        </div>

        <!-- Success checkmark -->
        <div
            x-show="!searching"
            wire:loading.class="hidden"
            class="absolute right-3 top-3 text-green-500"
        >
            ✓
        </div>
    </div>

    <div wire:loading.class="opacity-50">
        @foreach($results as $result)
            <div>{{ $result->name }}</div>
        @endforeach
    </div>
</div>
```

### 5. Shopping Cart with Animations

```html
<div x-data="{ adding: false }">
    <button
        @click="
            adding = true;
            $wire.addToCart({{ $product->id }});
            setTimeout(() => adding = false, 1000)
        "
        :disabled="adding"
        class="bg-blue-500 text-white px-4 py-2 rounded"
        :class="adding && 'scale-95 opacity-50'"
    >
        <span x-show="!adding">Add to Cart</span>
        <span x-show="adding">Adding...</span>
    </button>
</div>
```

---

## Advanced Integration

### Entangle: Two-Way Binding

Sync Alpine data with Livewire property:

```html
<div x-data="{ count: $wire.entangle('count') }">
    <!-- Both Alpine and Livewire stay in sync -->
    <button @click="count++">Increment (Alpine)</button>
    <button wire:click="increment">Increment (Livewire)</button>

    <p>Count: <span x-text="count"></span></p>
</div>
```

**Both buttons update the same value!**

### Defer Updates

By default, `entangle` updates immediately. Use `.defer` for better performance:

```html
<div x-data="{ search: $wire.entangle('search').defer }">
    <!-- Only syncs on blur -->
    <input x-model="search">
</div>
```

### Live Updates

Force immediate sync (default behavior):

```html
<div x-data="{ search: $wire.entangle('search').live }">
    <!-- Syncs on every keystroke -->
    <input x-model="search">
</div>
```

---

## Accessing Alpine from Livewire

### Dispatch Events to Alpine

```php
public function save()
{
    Post::create([...]);

    // Alpine can listen to this
    $this->dispatch('post-saved', postId: 123)->toJs();
}
```

```html
<div
    x-data="{ notification: '' }"
    @post-saved.window="notification = 'Post saved!'"
>
    <div x-show="notification" x-text="notification"></div>
</div>
```

### Update Alpine State

```php
// In component
$this->dispatch('update-cart-count', count: Cart::count())->toJs();
```

```html
<div
    x-data="{ cartCount: 0 }"
    @update-cart-count.window="cartCount = $event.detail.count"
>
    Cart: <span x-text="cartCount"></span>
</div>
```

---

## Real-World Example: Image Gallery

Combines Alpine (lightbox) + Livewire (upload/delete):

```php
class ImageGallery extends Component
{
    use WithFileUploads;

    public $images;
    public $newImage;

    public function mount()
    {
        $this->images = Image::all();
    }

    public function upload()
    {
        $this->validate(['newImage' => 'required|image|max:2048']);

        $path = $this->newImage->store('gallery', 'public');

        Image::create(['path' => $path]);

        $this->images = Image::all();
        $this->reset('newImage');
    }

    public function delete($imageId)
    {
        $image = Image::findOrFail($imageId);
        Storage::disk('public')->delete($image->path);
        $image->delete();

        $this->images = Image::all();
    }
}
```

```html
<div x-data="{
    lightbox: false,
    currentImage: null,
    showImage(url) {
        this.currentImage = url;
        this.lightbox = true;
    }
}">
    <!-- Upload Form (Livewire) -->
    <form wire:submit="upload" class="mb-6">
        <input type="file" wire:model="newImage">
        @error('newImage') <span class="text-red-500">{{ $message }}</span> @enderror

        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">
            Upload
        </button>
    </form>

    <!-- Gallery Grid -->
    <div class="grid grid-cols-3 gap-4">
        @foreach($images as $image)
            <div class="relative group">
                <!-- Click to open lightbox (Alpine) -->
                <img
                    src="{{ Storage::url($image->path) }}"
                    @click="showImage('{{ Storage::url($image->path) }}')"
                    class="w-full h-48 object-cover rounded cursor-pointer"
                >

                <!-- Delete button (Livewire) -->
                <button
                    wire:click="delete({{ $image->id }})"
                    wire:confirm="Delete this image?"
                    class="absolute top-2 right-2 bg-red-500 text-white px-2 py-1 rounded opacity-0 group-hover:opacity-100"
                >
                    Delete
                </button>
            </div>
        @endforeach
    </div>

    <!-- Lightbox (Alpine) -->
    <div
        x-show="lightbox"
        x-cloak
        @click="lightbox = false"
        class="fixed inset-0 bg-black bg-opacity-90 flex items-center justify-center z-50"
    >
        <img
            :src="currentImage"
            class="max-w-4xl max-h-screen"
            @click.stop
        >
        <button
            @click="lightbox = false"
            class="absolute top-4 right-4 text-white text-4xl"
        >
            ×
        </button>
    </div>
</div>
```

**Perfect separation of concerns:**
- Alpine: Lightbox, animations, instant interactions
- Livewire: Upload, delete, database operations

---

## Best Practices

### 1. Don't Over-Livewire

❌ **Bad** (unnecessary server roundtrip):
```html
<button wire:click="toggleMenu">Toggle Menu</button>
```

✅ **Good** (Alpine handles it):
```html
<div x-data="{ menuOpen: false }">
    <button @click="menuOpen = !menuOpen">Toggle Menu</button>
    <div x-show="menuOpen">Menu content</div>
</div>
```

### 2. Don't Over-Alpine

❌ **Bad** (Alpine can't access database):
```html
<div x-data="{ users: [] }" x-init="fetch('/api/users').then(...)">
    <!-- Complex API handling in Alpine -->
</div>
```

✅ **Good** (Livewire handles it):
```php
public function render()
{
    return view('livewire.users', [
        'users' => User::all()
    ]);
}
```

### 3. Use Entangle Sparingly

Only when you truly need two-way sync between Alpine and Livewire.

❌ **Bad** (unnecessary):
```html
<div x-data="{ name: $wire.entangle('name') }">
    <input x-model="name">
</div>
```

✅ **Good** (just use wire:model):
```html
<input wire:model="name">
```

### 4. Events for Communication

Use events to communicate between Alpine and Livewire:

```php
// Livewire tells Alpine
$this->dispatch('update-ui')->toJs();
```

```html
<!-- Alpine listens -->
<div @update-ui.window="/* do something */">
```

---

## Common Mistakes

### Mistake 1: Accessing $wire Before It Exists

❌ **Bad**:
```html
<div x-data="{ count: $wire.count }">
    <!-- $wire might not exist yet -->
</div>
```

✅ **Good**:
```html
<div x-data="{ count: 0 }" x-init="count = $wire.count">
    <!-- Or use entangle -->
</div>
```

### Mistake 2: Not Using x-cloak

❌ **Bad** (flash of unstyled content):
```html
<div x-show="open">
    <!-- Visible for a moment on page load -->
</div>
```

✅ **Good**:
```html
<div x-show="open" x-cloak>
    <!-- Hidden until Alpine initializes -->
</div>
```

Add to CSS:
```css
[x-cloak] { display: none !important; }
```

### Mistake 3: Forgetting .toJs()

❌ **Bad** (Alpine won't receive event):
```php
$this->dispatch('notify', message: 'Hello');
```

✅ **Good**:
```php
$this->dispatch('notify', message: 'Hello')->toJs();
```

---

## Quick Quiz

**Question 1**: When should you use Alpine instead of Livewire?

<details>
<summary>Show Answer</summary>

When the interaction is purely client-side and doesn't need server data or logic (dropdowns, modals, tabs, animations).

</details>

**Question 2**: How do you access a Livewire property from Alpine?

<details>
<summary>Show Answer</summary>

```html
<div x-data="{}">
    <span x-text="$wire.propertyName"></span>
</div>
```

</details>

**Question 3**: How do you send an event from Livewire to Alpine?

<details>
<summary>Show Answer</summary>

```php
$this->dispatch('event-name', data: 'value')->toJs();
```

Then listen in Alpine:
```html
<div @event-name.window="/* handle event */">
```

</details>

---

## Practice Exercise

Create a **Todo App with Alpine + Livewire**:

**Requirements:**
1. Add todo (Livewire - needs database)
2. Toggle complete (Livewire - needs database)
3. Delete todo (Livewire - needs database)
4. Filter todos (Alpine - client-side only)
   - All
   - Active
   - Completed
5. Smooth animations (Alpine)
6. Todo count badges (Alpine)

**Hint:** Livewire manages data, Alpine manages UI state and animations.

---

## Summary

You mastered:

- ✅ When to use Alpine vs Livewire
- ✅ Accessing Livewire from Alpine ($wire)
- ✅ Accessing Alpine from Livewire (events)
- ✅ Common integration patterns
- ✅ Entangle for two-way binding
- ✅ Best practices and mistakes to avoid
- ✅ Real-world examples

**Next Lesson**: Best Practices & Performance - optimize your Livewire apps!

---

**The TALL stack is complete! Alpine + Livewire = Perfect harmony!**
