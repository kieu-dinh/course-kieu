# Lesson 08 - Alpine Components, Plugins & Best Practices

**Duration**: 1.5-2 hours

---

## Alpine Components

As your app grows, you'll want to **reuse** component logic. Alpine provides several ways to do this!

---

## Method 1: Alpine.data()

Register reusable component data globally.

### Basic Registration

```html
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('dropdown', () => ({
            open: false,
            toggle() {
                this.open = !this.open
            },
            close() {
                this.open = false
            }
        }))
    })
</script>

<!-- Use anywhere! -->
<div x-data="dropdown">
    <button @click="toggle()">Toggle</button>
    <div x-show="open" @click.outside="close()">
        Dropdown content
    </div>
</div>

<!-- Use again with independent state! -->
<div x-data="dropdown">
    <button @click="toggle()">Another Dropdown</button>
    <div x-show="open" @click.outside="close()">
        More content
    </div>
</div>
```

Each instance has its own state!

### Parameterized Components

```html
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('counter', (initialValue = 0, step = 1) => ({
            count: initialValue,
            step: step,
            increment() {
                this.count += this.step
            },
            decrement() {
                this.count -= this.step
            },
            reset() {
                this.count = initialValue
            }
        }))
    })
</script>

<!-- Counter starting at 0, step 1 -->
<div x-data="counter()">
    <button @click="decrement()">-</button>
    <span x-text="count"></span>
    <button @click="increment()">+</button>
</div>

<!-- Counter starting at 100, step 10 -->
<div x-data="counter(100, 10)">
    <button @click="decrement()">-10</button>
    <span x-text="count"></span>
    <button @click="increment()">+10</button>
</div>
```

### Real-World Example: Modal Component

```html
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('modal', (title = 'Modal') => ({
            open: false,
            title: title,
            show() {
                this.open = true
                document.body.style.overflow = 'hidden'
            },
            hide() {
                this.open = false
                document.body.style.overflow = 'auto'
            }
        }))
    })
</script>

<div x-data="modal('Confirm Delete')">
    <button @click="show()">Open Modal</button>

    <div
        x-show="open"
        @keyup.escape.window="hide()"
        style="position: fixed; inset: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center;"
    >
        <div
            @click.outside="hide()"
            style="background: white; padding: 2rem; border-radius: 0.5rem; max-width: 500px;"
        >
            <h2 x-text="title"></h2>
            <p>Are you sure you want to delete this item?</p>
            <button @click="hide()">Cancel</button>
            <button @click="hide()">Confirm</button>
        </div>
    </div>
</div>
```

---

## Method 2: Alpine.store()

Share state **across multiple components**.

### Creating a Global Store

```html
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.store('auth', {
            user: null,
            isLoggedIn: false,
            login(name) {
                this.user = { name }
                this.isLoggedIn = true
            },
            logout() {
                this.user = null
                this.isLoggedIn = false
            }
        })
    })
</script>

<!-- Component 1: Header -->
<div x-data>
    <template x-if="$store.auth.isLoggedIn">
        <div>
            Welcome, <span x-text="$store.auth.user.name"></span>!
            <button @click="$store.auth.logout()">Logout</button>
        </div>
    </template>

    <template x-if="!$store.auth.isLoggedIn">
        <button @click="$store.auth.login('John')">Login</button>
    </template>
</div>

<!-- Component 2: Sidebar (different component, same state!) -->
<div x-data>
    <div x-show="$store.auth.isLoggedIn">
        <p>User: <span x-text="$store.auth.user?.name"></span></p>
    </div>
</div>
```

**Both components share the same auth state!**

### Shopping Cart Store

```html
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.store('cart', {
            items: [],
            add(product) {
                const existing = this.items.find(item => item.id === product.id)
                if (existing) {
                    existing.quantity++
                } else {
                    this.items.push({ ...product, quantity: 1 })
                }
            },
            remove(productId) {
                this.items = this.items.filter(item => item.id !== productId)
            },
            get total() {
                return this.items.reduce((sum, item) => sum + (item.price * item.quantity), 0)
            },
            get count() {
                return this.items.reduce((sum, item) => sum + item.quantity, 0)
            }
        })
    })
</script>

<!-- Product List -->
<div x-data="{
    products: [
        { id: 1, name: 'Laptop', price: 999 },
        { id: 2, name: 'Mouse', price: 29 }
    ]
}">
    <template x-for="product in products" :key="product.id">
        <div>
            <span x-text="product.name"></span>
            - $<span x-text="product.price"></span>
            <button @click="$store.cart.add(product)">Add to Cart</button>
        </div>
    </template>
</div>

<!-- Cart Badge (anywhere on page!) -->
<div x-data>
    <button>
        Cart (<span x-text="$store.cart.count"></span>)
    </button>
    <p>Total: $<span x-text="$store.cart.total"></span></p>
</div>
```

---

## Magic Properties

Alpine provides special **magic properties** starting with `$`.

### $el

Reference to the current element:

```html
<div x-data>
    <button @click="$el.style.background = 'red'">
        Turn me red
    </button>

    <div @click="console.log($el)">
        Click to log this element
    </div>
</div>
```

### $refs

Reference to elements marked with `x-ref`:

```html
<div x-data>
    <input x-ref="email" type="email" placeholder="Email">
    <button @click="console.log($refs.email.value)">
        Log Email
    </button>

    <button @click="$refs.email.focus()">
        Focus Email Input
    </button>
</div>
```

### $watch

Watch for changes to data:

```html
<div x-data="{ count: 0 }" x-init="
    $watch('count', (newValue, oldValue) => {
        console.log('Count changed from', oldValue, 'to', newValue)
    })
">
    <button @click="count++">
        Count: <span x-text="count"></span>
    </button>
</div>
```

### $dispatch

Dispatch custom events:

```html
<div x-data @custom-event="alert('Custom event fired!')">
    <button @click="$dispatch('custom-event')">
        Dispatch Event
    </button>
</div>
```

### $nextTick

Wait for DOM to update:

```html
<div x-data="{ message: 'Hello' }">
    <p x-text="message"></p>
    <button @click="
        message = 'Updated';
        $nextTick(() => {
            console.log('DOM updated with:', message)
        })
    ">
        Update
    </button>
</div>
```

### $root

Reference to root component element:

```html
<div x-data="{ title: 'My App' }">
    <div>
        <div>
            <button @click="console.log($root)">
                Log Root Element
            </button>
        </div>
    </div>
</div>
```

---

## Alpine Plugins

Extend Alpine with official and community plugins!

### Official Plugins

#### 1. Persist Plugin

Save data to localStorage automatically!

```html
<script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/persist@3.x.x/dist/cdn.min.js"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

<div x-data="{
    darkMode: $persist(false),
    name: $persist('Guest')
}">
    <label>
        <input type="checkbox" x-model="darkMode">
        Dark Mode (persisted across page reloads!)
    </label>

    <input type="text" x-model="name" placeholder="Name">
    <p>Hello, <span x-text="name"></span>!</p>

    <p>Reload the page - your settings are saved!</p>
</div>
```

#### 2. Focus Plugin

Advanced focus management:

```html
<script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/focus@3.x.x/dist/cdn.min.js"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

<div x-data="{ open: false }">
    <button @click="open = true">Open Dialog</button>

    <div x-show="open" x-trap="open">
        <input type="text" placeholder="First name">
        <input type="text" placeholder="Last name">
        <button @click="open = false">Close</button>
    </div>
</div>
```

#### 3. Collapse Plugin

Smooth height transitions:

```html
<script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

<div x-data="{ expanded: false }">
    <button @click="expanded = !expanded">
        Toggle
    </button>

    <div x-show="expanded" x-collapse>
        <p>This content smoothly collapses!</p>
    </div>
</div>
```

#### 4. Morph Plugin

Update DOM without losing state (like Livewire!):

```html
<script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/morph@3.x.x/dist/cdn.min.js"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

<div x-data="{ html: '<p>Original</p>' }">
    <div x-ref="content" x-html="html"></div>

    <button @click="
        html = '<p>Updated!</p>';
        $nextTick(() => Alpine.morph($refs.content, html))
    ">
        Morph Content
    </button>
</div>
```

---

## Best Practices

### 1. Keep Components Small and Focused

**Bad:**
```html
<div x-data="{
    users: [],
    products: [],
    cart: [],
    auth: {},
    settings: {},
    // ... everything in one component
}">
    <!-- Massive component with everything -->
</div>
```

**Good:**
```html
<!-- Separate concerns -->
<div x-data="userList">
    <!-- User list component -->
</div>

<div x-data="productList">
    <!-- Product list component -->
</div>

<div x-data>
    <!-- Cart using store -->
    <div x-text="$store.cart.count"></div>
</div>
```

### 2. Use Computed Properties for Derived State

**Bad:**
```html
<div x-data="{ items: [] }">
    <!-- Recalculates every render -->
    <p x-text="items.filter(i => i.done).length"></p>
</div>
```

**Good:**
```html
<div x-data="{
    items: [],
    get completedCount() {
        return this.items.filter(i => i.done).length
    }
}">
    <!-- Cached, only recalculates when items change -->
    <p x-text="completedCount"></p>
</div>
```

### 3. Extract Complex Logic to Methods

**Bad:**
```html
<button @click="
    if (name.length < 3) {
        alert('Too short');
        return;
    }
    if (!email.includes('@')) {
        alert('Invalid email');
        return;
    }
    // ... lots more logic
">
    Submit
</button>
```

**Good:**
```html
<div x-data="{
    name: '',
    email: '',
    submit() {
        if (this.name.length < 3) {
            alert('Too short');
            return;
        }
        if (!this.email.includes('@')) {
            alert('Invalid email');
            return;
        }
        // ... logic in method
    }
}">
    <button @click="submit()">Submit</button>
</div>
```

### 4. Use x-cloak for Flash Prevention

Prevent showing template before Alpine loads:

```html
<style>
    [x-cloak] {
        display: none !important;
    }
</style>

<div x-data="{ message: 'Hello' }" x-cloak>
    <!-- Won't show until Alpine loads -->
    <p x-text="message"></p>
</div>
```

### 5. Name Boolean Data Clearly

**Bad:**
```html
<div x-data="{ modal: false, dropdown: true }">
```

**Good:**
```html
<div x-data="{ isModalOpen: false, showDropdown: true }">
```

Clear naming = easier to read!

### 6. Use Stores for Global State

**Bad:**
```html
<!-- Passing auth down through components -->
<div x-data="{ auth: {...} }">
    <header x-data="{ auth: $parent.auth }">
        <nav x-data="{ auth: $parent.auth }">
            <!-- Messy! -->
        </nav>
    </header>
</div>
```

**Good:**
```html
<script>
    Alpine.store('auth', { /* ... */ })
</script>

<!-- Access anywhere without passing down -->
<div x-data>
    <header x-data>
        <nav x-data>
            <span x-text="$store.auth.user.name"></span>
        </nav>
    </header>
</div>
```

### 7. Initialize Data Properly

**Bad:**
```html
<div x-data="{}">
    <!-- Undefined error! -->
    <p x-text="name"></p>
</div>
```

**Good:**
```html
<div x-data="{ name: '' }">
    <!-- Safe, defaults to empty string -->
    <p x-text="name || 'No name'"></p>
</div>
```

### 8. Use x-init for Setup

```html
<div x-data="{
    users: [],
    async init() {
        const response = await fetch('/api/users')
        this.users = await response.json()
    }
}" x-init="init()">
    <!-- Data loads on component init -->
    <template x-for="user in users" :key="user.id">
        <div x-text="user.name"></div>
    </template>
</div>
```

Or shorter:

```html
<div x-data="{ users: [] }" x-init="
    fetch('/api/users')
        .then(r => r.json())
        .then(data => users = data)
">
    <!-- ... -->
</div>
```

---

## Common Patterns

### Pattern 1: Confirm Action

```html
<script>
    Alpine.data('confirmable', (message = 'Are you sure?') => ({
        confirming: false,
        confirm(callback) {
            this.confirming = true
            this.onConfirm = callback
        },
        yes() {
            this.onConfirm?.()
            this.confirming = false
        },
        no() {
            this.confirming = false
        }
    }))
</script>

<div x-data="confirmable('Delete this item?')">
    <button @click="confirm(() => alert('Deleted!'))">
        Delete
    </button>

    <div x-show="confirming">
        <p>Are you sure?</p>
        <button @click="yes()">Yes</button>
        <button @click="no()">No</button>
    </div>
</div>
```

### Pattern 2: Toast Notifications

```html
<script>
    Alpine.store('toasts', {
        items: [],
        counter: 0,
        add(message, type = 'info') {
            const id = this.counter++
            this.items.push({ id, message, type })
            setTimeout(() => this.remove(id), 3000)
        },
        remove(id) {
            this.items = this.items.filter(toast => toast.id !== id)
        }
    })
</script>

<!-- Toast Container -->
<div x-data style="position: fixed; top: 1rem; right: 1rem;">
    <template x-for="toast in $store.toasts.items" :key="toast.id">
        <div
            x-transition
            :class="{
                'bg-blue-500': toast.type === 'info',
                'bg-green-500': toast.type === 'success',
                'bg-red-500': toast.type === 'error'
            }"
            style="padding: 1rem; margin-bottom: 0.5rem; color: white; border-radius: 0.375rem;"
        >
            <span x-text="toast.message"></span>
            <button @click="$store.toasts.remove(toast.id)">×</button>
        </div>
    </template>
</div>

<!-- Use anywhere! -->
<div x-data>
    <button @click="$store.toasts.add('Hello!', 'info')">Info</button>
    <button @click="$store.toasts.add('Success!', 'success')">Success</button>
    <button @click="$store.toasts.add('Error!', 'error')">Error</button>
</div>
```

### Pattern 3: Form Wizard

```html
<script>
    Alpine.data('wizard', (steps) => ({
        currentStep: 0,
        steps: steps,
        get progress() {
            return ((this.currentStep + 1) / this.steps.length) * 100
        },
        get isFirstStep() {
            return this.currentStep === 0
        },
        get isLastStep() {
            return this.currentStep === this.steps.length - 1
        },
        next() {
            if (!this.isLastStep) this.currentStep++
        },
        back() {
            if (!this.isFirstStep) this.currentStep--
        },
        goTo(index) {
            this.currentStep = index
        }
    }))
</script>

<div x-data="wizard(['Personal', 'Contact', 'Review'])">
    <!-- Progress bar -->
    <div style="background: #e5e7eb; height: 4px; border-radius: 2px;">
        <div
            :style="`width: ${progress}%; background: #3b82f6; height: 100%; transition: width 0.3s;`"
        ></div>
    </div>

    <!-- Step indicators -->
    <div style="display: flex; gap: 1rem; margin: 1rem 0;">
        <template x-for="(step, index) in steps" :key="index">
            <button
                @click="goTo(index)"
                :class="{ 'font-bold': currentStep === index }"
                x-text="step"
            ></button>
        </template>
    </div>

    <!-- Step content -->
    <div x-show="currentStep === 0">
        <h2>Personal Information</h2>
        <!-- Personal info fields -->
    </div>

    <div x-show="currentStep === 1">
        <h2>Contact Information</h2>
        <!-- Contact fields -->
    </div>

    <div x-show="currentStep === 2">
        <h2>Review</h2>
        <!-- Review summary -->
    </div>

    <!-- Navigation -->
    <div style="margin-top: 1rem;">
        <button @click="back()" :disabled="isFirstStep">Back</button>
        <button @click="next()" x-show="!isLastStep">Next</button>
        <button x-show="isLastStep">Submit</button>
    </div>
</div>
```

---

## Debugging Tips

### 1. Use x-init for Logging

```html
<div x-data="{ count: 0 }" x-init="console.log('Component initialized')">
    <button @click="count++; console.log('Count:', count)">
        Count: <span x-text="count"></span>
    </button>
</div>
```

### 2. Install Alpine DevTools

Chrome extension: [Alpine.js DevTools](https://chrome.google.com/webstore/detail/alpinejs-devtools/fopaemeedckajflibkpifppcankfmbhk)

Shows all components and their data in real-time!

### 3. Use $watch for Debugging

```html
<div x-data="{ value: '' }" x-init="
    $watch('value', (newVal, oldVal) => {
        console.log('Value changed from', oldVal, 'to', newVal)
    })
">
    <input x-model="value">
</div>
```

### 4. Check Alpine Version

```javascript
console.log(Alpine.version) // Should show 3.x.x
```

---

## Performance Tips

### 1. Use x-show for Frequent Toggles

```html
<!-- Fast - just CSS -->
<div x-show="open">Content</div>

<!-- Slower - adds/removes from DOM -->
<template x-if="open">
    <div>Content</div>
</template>
```

### 2. Debounce Expensive Operations

```html
<input
    x-model="search"
    @input.debounce.500ms="expensiveSearch()"
>
```

### 3. Use Computed Properties

```html
<div x-data="{
    items: [...],
    filter: '',
    // Computed - cached!
    get filteredItems() {
        return this.items.filter(...)
    }
}">
    <template x-for="item in filteredItems">
        <!-- ... -->
    </template>
</div>
```

### 4. Lazy Load Heavy Components

```html
<div x-data="{ loadHeavyComponent: false }">
    <button @click="loadHeavyComponent = true">
        Load Component
    </button>

    <template x-if="loadHeavyComponent">
        <div>
            <!-- Heavy component only loads when needed -->
        </div>
    </template>
</div>
```

---

## Preparing for Laravel Livewire

Alpine.js knowledge prepares you perfectly for Livewire!

### Livewire Uses Alpine Under the Hood

```html
<!-- Alpine -->
<div x-data="{ count: 0 }">
    <button @click="count++">Count: <span x-text="count"></span></button>
</div>

<!-- Livewire (looks similar!) -->
<div>
    <button wire:click="increment">Count: {{ $count }}</button>
</div>
```

### Concepts That Transfer

1. **Directives** - `wire:` instead of `x-`
2. **Reactivity** - Automatic UI updates
3. **Component thinking** - Small, focused components
4. **Event handling** - `wire:click` like `@click`
5. **Two-way binding** - `wire:model` like `x-model`

**You're already halfway to mastering Livewire!**

---

## Final Project Exercise

Create a complete **Todo App** with all Alpine features!

### Requirements

1. Add new todos
2. Mark todos as complete
3. Filter: All, Active, Completed
4. Delete todos
5. Edit todos (inline editing)
6. Persist to localStorage
7. Show completion statistics
8. Bulk actions: Select all, Delete completed

### Solution Framework

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alpine Todo App</title>
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/persist@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        /* Add your styles */
    </style>
</head>
<body>
    <div x-data="todoApp()" x-init="init()">
        <!-- Your todo app here -->
    </div>

    <script>
        function todoApp() {
            return {
                todos: $persist([]),
                newTodo: '',
                filter: 'all',
                editingId: null,
                editingText: '',

                init() {
                    // Component initialization
                },

                addTodo() {
                    // Add new todo
                },

                removeTodo(id) {
                    // Remove todo
                },

                toggleDone(todo) {
                    // Toggle completion
                },

                startEdit(todo) {
                    // Start editing
                },

                saveEdit() {
                    // Save edit
                },

                get filteredTodos() {
                    // Filter todos based on current filter
                },

                get stats() {
                    // Calculate statistics
                }
            }
        }
    </script>
</body>
</html>
```

**Try building this yourself!** It combines everything you've learned.

---

## Key Takeaways

1. **Alpine.data() for reusable components** - Register once, use everywhere
2. **Alpine.store() for global state** - Share data across components
3. **Magic properties are powerful** - $refs, $el, $watch, $dispatch, etc.
4. **Plugins extend functionality** - Persist, Focus, Collapse, Morph
5. **Keep components small** - Single responsibility principle
6. **Use computed properties** - Cache expensive calculations
7. **Extract complex logic** - Methods keep templates clean
8. **Alpine prepares you for Livewire** - Same concepts and patterns

---

## What's Next?

You've completed Alpine.js! You now know:

- ✅ Reactive data with x-data
- ✅ Conditional rendering with x-show/x-if
- ✅ Looping with x-for
- ✅ Event handling with x-on
- ✅ Two-way binding with x-model
- ✅ Dynamic attributes with x-bind
- ✅ Reusable components
- ✅ Global state management
- ✅ Best practices and patterns

**Next Module**: **Module 13 - AJAX & Fetch** - Combine Alpine with API calls to build dynamic, data-driven applications!

Then you'll move to **Laravel and Livewire** where all these concepts will feel familiar and powerful!

---

## Additional Resources

**Official Docs**: [alpinejs.dev](https://alpinejs.dev)
**Alpine Toolbox**: [alpinejs.dev/toolbox](https://alpinejs.dev/toolbox)
**Alpine Examples**: [alpinejs.dev/examples](https://alpinejs.dev/examples)

**Keep practicing!** The more you build with Alpine, the more natural it becomes. You're ready to build amazing interactive UIs!
