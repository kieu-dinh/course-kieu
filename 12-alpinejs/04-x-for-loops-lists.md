# Lesson 04 - x-for (Loops and Lists)

**Duration**: 1-1.5 hours

---

## What is x-for?

`x-for` lets you loop through arrays and render elements for each item.

Think of it like PHP's `foreach` or JavaScript's `for...of`, but for rendering HTML!

### Why Do We Need x-for?

Imagine you have a list of items and want to display them:

**Without x-for:**
```html
<ul>
    <li>Item 1</li>
    <li>Item 2</li>
    <li>Item 3</li>
    <!-- Manually add each item - not dynamic! -->
</ul>
```

**With x-for:**
```html
<ul x-data="{ items: ['Item 1', 'Item 2', 'Item 3'] }">
    <template x-for="item in items">
        <li x-text="item"></li>
    </template>
    <!-- Automatically renders all items - dynamic! -->
</ul>
```

Add/remove items from the array → Alpine updates the list automatically!

---

## Basic x-for Syntax

```html
<template x-for="item in items" :key="item">
    <div x-text="item"></div>
</template>
```

**Parts:**
- `<template>` - Required wrapper (like `x-if`)
- `x-for="item in items"` - Loop syntax
- `:key="item"` - Unique identifier for each item
- Inside: One root element that gets repeated

---

## Simple Examples

### Example 1: List of Strings

```html
<div x-data="{
    fruits: ['Apple', 'Banana', 'Cherry']
}">
    <ul>
        <template x-for="fruit in fruits" :key="fruit">
            <li x-text="fruit"></li>
        </template>
    </ul>
</div>
```

**Output:**
```
• Apple
• Banana
• Cherry
```

### Example 2: List of Numbers

```html
<div x-data="{
    numbers: [1, 2, 3, 4, 5]
}">
    <ul>
        <template x-for="number in numbers" :key="number">
            <li>
                Number: <span x-text="number"></span>
                Squared: <span x-text="number * number"></span>
            </li>
        </template>
    </ul>
</div>
```

**Output:**
```
• Number: 1 Squared: 1
• Number: 2 Squared: 4
• Number: 3 Squared: 9
• Number: 4 Squared: 16
• Number: 5 Squared: 25
```

### Example 3: List of Objects

```html
<div x-data="{
    users: [
        { id: 1, name: 'Alice', age: 25 },
        { id: 2, name: 'Bob', age: 30 },
        { id: 3, name: 'Charlie', age: 35 }
    ]
}">
    <ul>
        <template x-for="user in users" :key="user.id">
            <li>
                <strong x-text="user.name"></strong> (age: <span x-text="user.age"></span>)
            </li>
        </template>
    </ul>
</div>
```

**Output:**
```
• Alice (age: 25)
• Bob (age: 30)
• Charlie (age: 35)
```

---

## The :key Attribute

The `:key` attribute is **crucial** for x-for performance!

### Why Do We Need Keys?

Keys help Alpine track which items changed, were added, or were removed.

**Without keys:**
- Alpine re-renders the entire list on every change
- Slow for large lists
- Loses element state (like focus, scroll position)

**With keys:**
- Alpine only updates changed items
- Much faster
- Preserves element state

### Key Requirements

1. **Must be unique** - No duplicates!
2. **Must be stable** - Same item = same key
3. **Should be simple** - String or number

### Good Keys

```html
<!-- Objects with ID -->
<template x-for="user in users" :key="user.id">
    <div x-text="user.name"></div>
</template>

<!-- Using index when no unique ID (not ideal, but works) -->
<template x-for="(item, index) in items" :key="index">
    <div x-text="item"></div>
</template>

<!-- Strings (if unique) -->
<template x-for="fruit in fruits" :key="fruit">
    <div x-text="fruit"></div>
</template>
```

### Bad Keys

```html
<!-- Wrong: Same key for all items -->
<template x-for="user in users" :key="'user'">
    <div x-text="user.name"></div>
</template>

<!-- Wrong: Random - changes every render -->
<template x-for="user in users" :key="Math.random()">
    <div x-text="user.name"></div>
</template>
```

**Best practice:** Use a unique ID property when available!

---

## Accessing Index

You can access the current loop index:

```html
<div x-data="{
    items: ['First', 'Second', 'Third']
}">
    <ul>
        <template x-for="(item, index) in items" :key="index">
            <li>
                <span x-text="index + 1"></span>.
                <span x-text="item"></span>
            </li>
        </template>
    </ul>
</div>
```

**Output:**
```
1. First
2. Second
3. Third
```

**Syntax:** `(item, index) in items`
- First parameter: The item
- Second parameter: The index (starts at 0)

---

## Adding and Removing Items

The magic of x-for: **automatically updates when the array changes!**

### Adding Items

```html
<div x-data="{
    items: ['Apple', 'Banana'],
    newItem: '',
    addItem() {
        if (this.newItem.trim()) {
            this.items.push(this.newItem)
            this.newItem = ''
        }
    }
}">
    <input x-model="newItem" type="text" placeholder="Add item">
    <button @click="addItem()">Add</button>

    <ul>
        <template x-for="item in items" :key="item">
            <li x-text="item"></li>
        </template>
    </ul>
</div>
```

Type "Cherry" and click Add → List updates automatically!

### Removing Items

```html
<div x-data="{
    items: ['Apple', 'Banana', 'Cherry'],
    removeItem(index) {
        this.items.splice(index, 1)
    }
}">
    <ul>
        <template x-for="(item, index) in items" :key="index">
            <li>
                <span x-text="item"></span>
                <button @click="removeItem(index)">Remove</button>
            </li>
        </template>
    </ul>
</div>
```

Click "Remove" → Item disappears from list!

### Complete CRUD Example

```html
<div x-data="{
    todos: [
        { id: 1, text: 'Learn Alpine', done: false },
        { id: 2, text: 'Build project', done: false }
    ],
    newTodo: '',
    nextId: 3,
    addTodo() {
        if (this.newTodo.trim()) {
            this.todos.push({
                id: this.nextId++,
                text: this.newTodo,
                done: false
            })
            this.newTodo = ''
        }
    },
    removeTodo(id) {
        this.todos = this.todos.filter(todo => todo.id !== id)
    },
    toggleDone(todo) {
        todo.done = !todo.done
    }
}">
    <!-- Add new todo -->
    <div>
        <input x-model="newTodo" type="text" placeholder="New todo">
        <button @click="addTodo()">Add</button>
    </div>

    <!-- Todo list -->
    <ul>
        <template x-for="todo in todos" :key="todo.id">
            <li>
                <input
                    type="checkbox"
                    :checked="todo.done"
                    @change="toggleDone(todo)"
                >
                <span
                    x-text="todo.text"
                    :style="todo.done ? 'text-decoration: line-through' : ''"
                ></span>
                <button @click="removeTodo(todo.id)">Delete</button>
            </li>
        </template>
    </ul>
</div>
```

Full todo app in one component!

---

## Nested Loops

You can nest x-for loops!

### Example: Categories and Items

```html
<div x-data="{
    categories: [
        {
            name: 'Fruits',
            items: ['Apple', 'Banana', 'Orange']
        },
        {
            name: 'Vegetables',
            items: ['Carrot', 'Broccoli', 'Spinach']
        }
    ]
}">
    <template x-for="category in categories" :key="category.name">
        <div style="margin-bottom: 1rem;">
            <h3 x-text="category.name"></h3>
            <ul>
                <template x-for="item in category.items" :key="item">
                    <li x-text="item"></li>
                </template>
            </ul>
        </div>
    </template>
</div>
```

**Output:**
```
Fruits
  • Apple
  • Banana
  • Orange

Vegetables
  • Carrot
  • Broccoli
  • Spinach
```

---

## Filtering and Sorting

Combine x-for with computed properties for dynamic filtering!

### Filtering Example

```html
<div x-data="{
    searchQuery: '',
    allItems: ['Apple', 'Banana', 'Cherry', 'Date', 'Elderberry'],
    get filteredItems() {
        if (!this.searchQuery) {
            return this.allItems
        }
        return this.allItems.filter(item =>
            item.toLowerCase().includes(this.searchQuery.toLowerCase())
        )
    }
}">
    <input
        x-model="searchQuery"
        type="text"
        placeholder="Search..."
    >

    <ul>
        <template x-for="item in filteredItems" :key="item">
            <li x-text="item"></li>
        </template>
    </ul>

    <p x-show="filteredItems.length === 0">No items found!</p>
</div>
```

Type "erry" → Shows only "Cherry"!

### Sorting Example

```html
<div x-data="{
    sortBy: 'name',
    users: [
        { id: 1, name: 'Charlie', age: 30 },
        { id: 2, name: 'Alice', age: 25 },
        { id: 3, name: 'Bob', age: 35 }
    ],
    get sortedUsers() {
        return [...this.users].sort((a, b) => {
            if (this.sortBy === 'name') {
                return a.name.localeCompare(b.name)
            } else {
                return a.age - b.age
            }
        })
    }
}">
    <div>
        <button @click="sortBy = 'name'">Sort by Name</button>
        <button @click="sortBy = 'age'">Sort by Age</button>
    </div>

    <ul>
        <template x-for="user in sortedUsers" :key="user.id">
            <li>
                <span x-text="user.name"></span> -
                <span x-text="user.age"></span> years old
            </li>
        </template>
    </ul>
</div>
```

Click "Sort by Name" → Alphabetical order!
Click "Sort by Age" → Youngest to oldest!

### Advanced Filtering

```html
<div x-data="{
    filter: 'all',
    todos: [
        { id: 1, text: 'Learn Alpine', done: true },
        { id: 2, text: 'Build project', done: false },
        { id: 3, text: 'Deploy app', done: false }
    ],
    get filteredTodos() {
        if (this.filter === 'active') {
            return this.todos.filter(todo => !todo.done)
        } else if (this.filter === 'completed') {
            return this.todos.filter(todo => todo.done)
        }
        return this.todos
    }
}">
    <div>
        <button @click="filter = 'all'">All</button>
        <button @click="filter = 'active'">Active</button>
        <button @click="filter = 'completed'">Completed</button>
    </div>

    <ul>
        <template x-for="todo in filteredTodos" :key="todo.id">
            <li>
                <span x-text="todo.text"></span>
                <span x-show="todo.done">✓</span>
            </li>
        </template>
    </ul>

    <p>Showing: <span x-text="filteredTodos.length"></span> todos</p>
</div>
```

---

## Empty States

Handle empty arrays gracefully:

```html
<div x-data="{
    items: []
}">
    <!-- Show list if items exist -->
    <template x-if="items.length > 0">
        <ul>
            <template x-for="item in items" :key="item">
                <li x-text="item"></li>
            </template>
        </ul>
    </template>

    <!-- Show message if empty -->
    <template x-if="items.length === 0">
        <p>No items yet. Add your first item!</p>
    </template>
</div>
```

Or use `x-show`:

```html
<div x-data="{ items: [] }">
    <ul x-show="items.length > 0">
        <template x-for="item in items" :key="item">
            <li x-text="item"></li>
        </template>
    </ul>

    <p x-show="items.length === 0">No items yet!</p>
</div>
```

---

## Common Patterns

### Pattern 1: Product Grid

```html
<div x-data="{
    products: [
        { id: 1, name: 'Laptop', price: 999, image: '💻' },
        { id: 2, name: 'Mouse', price: 29, image: '🖱️' },
        { id: 3, name: 'Keyboard', price: 79, image: '⌨️' }
    ]
}">
    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem;">
        <template x-for="product in products" :key="product.id">
            <div style="border: 1px solid #e5e7eb; padding: 1rem; border-radius: 0.5rem;">
                <div style="font-size: 3rem; text-align: center;" x-text="product.image"></div>
                <h3 x-text="product.name"></h3>
                <p>$<span x-text="product.price"></span></p>
                <button>Add to Cart</button>
            </div>
        </template>
    </div>
</div>
```

### Pattern 2: Table Rows

```html
<div x-data="{
    users: [
        { id: 1, name: 'Alice', email: 'alice@example.com', role: 'Admin' },
        { id: 2, name: 'Bob', email: 'bob@example.com', role: 'User' },
        { id: 3, name: 'Charlie', email: 'charlie@example.com', role: 'User' }
    ]
}">
    <table style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="background: #f3f4f6;">
                <th style="padding: 0.5rem; text-align: left;">Name</th>
                <th style="padding: 0.5rem; text-align: left;">Email</th>
                <th style="padding: 0.5rem; text-align: left;">Role</th>
            </tr>
        </thead>
        <tbody>
            <template x-for="user in users" :key="user.id">
                <tr style="border-bottom: 1px solid #e5e7eb;">
                    <td style="padding: 0.5rem;" x-text="user.name"></td>
                    <td style="padding: 0.5rem;" x-text="user.email"></td>
                    <td style="padding: 0.5rem;" x-text="user.role"></td>
                </tr>
            </template>
        </tbody>
    </table>
</div>
```

### Pattern 3: Tag List

```html
<div x-data="{
    tags: ['alpine', 'javascript', 'frontend', 'reactive'],
    removeTag(index) {
        this.tags.splice(index, 1)
    }
}">
    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
        <template x-for="(tag, index) in tags" :key="tag">
            <span style="background: #dbeafe; padding: 0.25rem 0.5rem; border-radius: 0.25rem; display: flex; align-items: center; gap: 0.5rem;">
                <span x-text="tag"></span>
                <button
                    @click="removeTag(index)"
                    style="background: none; border: none; cursor: pointer; font-weight: bold;"
                >
                    ×
                </button>
            </span>
        </template>
    </div>
</div>
```

### Pattern 4: Select All / Deselect All

```html
<div x-data="{
    items: [
        { id: 1, name: 'Item 1', selected: false },
        { id: 2, name: 'Item 2', selected: false },
        { id: 3, name: 'Item 3', selected: false }
    ],
    get allSelected() {
        return this.items.every(item => item.selected)
    },
    toggleAll() {
        const newState = !this.allSelected
        this.items.forEach(item => item.selected = newState)
    }
}">
    <div>
        <label>
            <input
                type="checkbox"
                :checked="allSelected"
                @change="toggleAll()"
            >
            Select All
        </label>
    </div>

    <ul>
        <template x-for="item in items" :key="item.id">
            <li>
                <label>
                    <input type="checkbox" x-model="item.selected">
                    <span x-text="item.name"></span>
                </label>
            </li>
        </template>
    </ul>
</div>
```

---

## Comparison: Vanilla JS vs Alpine

### Vanilla JavaScript

```html
<div id="app">
    <ul id="list"></ul>
</div>

<script>
    const items = ['Apple', 'Banana', 'Cherry']
    const list = document.getElementById('list')

    function renderList() {
        list.innerHTML = ''
        items.forEach(item => {
            const li = document.createElement('li')
            li.textContent = item
            list.appendChild(li)
        })
    }

    renderList()

    // Need to call renderList() every time items change!
</script>
```

### Alpine

```html
<div x-data="{ items: ['Apple', 'Banana', 'Cherry'] }">
    <ul>
        <template x-for="item in items" :key="item">
            <li x-text="item"></li>
        </template>
    </ul>
</div>
```

**Alpine automatically re-renders when items change!**

---

## Performance Tips

### 1. Use Unique Keys

```html
<!-- Good - unique ID -->
<template x-for="user in users" :key="user.id">
    <div x-text="user.name"></div>
</template>

<!-- Okay - index (if items don't reorder) -->
<template x-for="(item, index) in items" :key="index">
    <div x-text="item"></div>
</template>

<!-- Bad - same key for all -->
<template x-for="item in items" :key="'item'">
    <div x-text="item"></div>
</template>
```

### 2. Filter/Sort with Computed Properties

```html
<!-- Good - computed property -->
<div x-data="{
    items: [...],
    get filteredItems() {
        return this.items.filter(...)
    }
}">
    <template x-for="item in filteredItems" :key="item.id">
        <div x-text="item"></div>
    </template>
</div>

<!-- Bad - filtering in template -->
<template x-for="item in items.filter(...)" :key="item.id">
    <div x-text="item"></div>
</template>
```

### 3. Avoid Nested x-for with Large Lists

For very large lists (1000+ items), consider pagination or virtual scrolling instead of rendering everything at once.

---

## Common Mistakes

### Mistake 1: Missing :key

```html
<!-- Wrong - no key -->
<template x-for="item in items">
    <div x-text="item"></div>
</template>

<!-- Correct -->
<template x-for="item in items" :key="item">
    <div x-text="item"></div>
</template>
```

### Mistake 2: Multiple Root Elements

```html
<!-- Wrong - multiple roots -->
<template x-for="item in items" :key="item">
    <li x-text="item"></li>
    <li>Separator</li>
</template>

<!-- Correct - one root -->
<template x-for="item in items" :key="item">
    <li>
        <span x-text="item"></span>
        <span>Separator</span>
    </li>
</template>
```

### Mistake 3: Modifying Array Incorrectly

```html
<div x-data="{ items: [1, 2, 3] }">
    <!-- Wrong - doesn't trigger reactivity -->
    <button @click="items[0] = 99">Change First</button>

    <!-- Correct - use splice -->
    <button @click="items.splice(0, 1, 99)">Change First</button>

    <!-- Or reassign entire array -->
    <button @click="items = [99, ...items.slice(1)]">Change First</button>
</div>
```

**Reactive methods:** `push()`, `pop()`, `shift()`, `unshift()`, `splice()`
**Non-reactive:** Direct index assignment (`items[0] = value`)

---

## Practice Exercise

Create a **shopping cart** component.

### Requirements

1. Display a list of available products (name, price)
2. Each product has an "Add to Cart" button
3. Display cart items with quantity
4. Allow increasing/decreasing quantity
5. Show total price
6. Allow removing items from cart

### Starter Code

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart</title>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        body {
            font-family: system-ui;
            padding: 2rem;
            max-width: 800px;
            margin: 0 auto;
        }
        .products, .cart {
            margin: 2rem 0;
        }
        .product, .cart-item {
            padding: 1rem;
            border: 1px solid #e5e7eb;
            margin: 0.5rem 0;
            border-radius: 0.375rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        button {
            padding: 0.5rem 1rem;
            background: #3b82f6;
            color: white;
            border: none;
            border-radius: 0.375rem;
            cursor: pointer;
        }
        button:hover {
            background: #2563eb;
        }
        .btn-small {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
    </style>
</head>
<body>
    <!-- TODO: Add your shopping cart component -->
    <h1>Shopping Cart</h1>

    <div class="products">
        <h2>Products</h2>
        <!-- TODO: List products -->
    </div>

    <div class="cart">
        <h2>Your Cart</h2>
        <!-- TODO: List cart items -->
        <!-- TODO: Show total -->
    </div>
</body>
</html>
```

### Hints

- Products: Array of `{ id, name, price }`
- Cart: Array of `{ productId, name, price, quantity }`
- Methods needed: `addToCart()`, `removeFromCart()`, `increaseQuantity()`, `decreaseQuantity()`
- Computed property: `get cartTotal()`

Try it yourself first!

---

## Solution

<details>
<summary>Click to reveal solution</summary>

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart</title>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        body {
            font-family: system-ui;
            padding: 2rem;
            max-width: 800px;
            margin: 0 auto;
        }
        .products, .cart {
            margin: 2rem 0;
        }
        .product, .cart-item {
            padding: 1rem;
            border: 1px solid #e5e7eb;
            margin: 0.5rem 0;
            border-radius: 0.375rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        button {
            padding: 0.5rem 1rem;
            background: #3b82f6;
            color: white;
            border: none;
            border-radius: 0.375rem;
            cursor: pointer;
        }
        button:hover {
            background: #2563eb;
        }
        .btn-small {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
        .btn-danger {
            background: #ef4444;
        }
        .btn-danger:hover {
            background: #dc2626;
        }
        .quantity-controls {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }
        .total {
            font-size: 1.5rem;
            font-weight: bold;
            margin-top: 1rem;
            padding: 1rem;
            background: #f3f4f6;
            border-radius: 0.375rem;
        }
    </style>
</head>
<body>
    <div x-data="{
        products: [
            { id: 1, name: 'Laptop', price: 999 },
            { id: 2, name: 'Mouse', price: 29 },
            { id: 3, name: 'Keyboard', price: 79 },
            { id: 4, name: 'Monitor', price: 299 }
        ],
        cart: [],
        addToCart(product) {
            const existingItem = this.cart.find(item => item.productId === product.id)

            if (existingItem) {
                existingItem.quantity++
            } else {
                this.cart.push({
                    productId: product.id,
                    name: product.name,
                    price: product.price,
                    quantity: 1
                })
            }
        },
        removeFromCart(productId) {
            this.cart = this.cart.filter(item => item.productId !== productId)
        },
        increaseQuantity(item) {
            item.quantity++
        },
        decreaseQuantity(item) {
            if (item.quantity > 1) {
                item.quantity--
            } else {
                this.removeFromCart(item.productId)
            }
        },
        get cartTotal() {
            return this.cart.reduce((total, item) => {
                return total + (item.price * item.quantity)
            }, 0)
        }
    }">
        <h1>Shopping Cart</h1>

        <div class="products">
            <h2>Products</h2>
            <template x-for="product in products" :key="product.id">
                <div class="product">
                    <div>
                        <strong x-text="product.name"></strong>
                        <p>$<span x-text="product.price"></span></p>
                    </div>
                    <button @click="addToCart(product)">Add to Cart</button>
                </div>
            </template>
        </div>

        <div class="cart">
            <h2>Your Cart</h2>

            <template x-if="cart.length === 0">
                <p>Your cart is empty</p>
            </template>

            <template x-if="cart.length > 0">
                <div>
                    <template x-for="item in cart" :key="item.productId">
                        <div class="cart-item">
                            <div>
                                <strong x-text="item.name"></strong>
                                <p>$<span x-text="item.price"></span> each</p>
                            </div>

                            <div class="quantity-controls">
                                <button
                                    @click="decreaseQuantity(item)"
                                    class="btn-small"
                                >
                                    -
                                </button>

                                <span x-text="item.quantity"></span>

                                <button
                                    @click="increaseQuantity(item)"
                                    class="btn-small"
                                >
                                    +
                                </button>

                                <button
                                    @click="removeFromCart(item.productId)"
                                    class="btn-small btn-danger"
                                >
                                    Remove
                                </button>
                            </div>
                        </div>
                    </template>

                    <div class="total">
                        Total: $<span x-text="cartTotal.toFixed(2)"></span>
                    </div>
                </div>
            </template>
        </div>
    </div>
</body>
</html>
```

**Key concepts:**
1. Two separate lists: products and cart items
2. `addToCart()` checks if item exists and increases quantity or adds new
3. `removeFromCart()` filters out the item
4. `increaseQuantity()` / `decreaseQuantity()` modify quantity
5. `cartTotal` computed property calculates sum using `reduce()`
6. Empty state shown with `x-if` when cart is empty
7. Each list uses `x-for` with proper keys

</details>

---

## Quick Quiz

### Question 1
What's wrong with this code?

```html
<div x-for="item in items" :key="item">
    <span x-text="item"></span>
</div>
```

<details>
<summary>Answer</summary>

`x-for` must be on a `<template>` tag, not a regular element!

**Correct:**
```html
<template x-for="item in items" :key="item">
    <div>
        <span x-text="item"></span>
    </div>
</template>
```

The `<template>` tag doesn't render itself - it's just a wrapper for the repeated content.
</details>

### Question 2
How do you access both the item and its index in x-for?

<details>
<summary>Answer</summary>

Use parentheses with two parameters: `(item, index)`:

```html
<template x-for="(item, index) in items" :key="index">
    <div>
        <span x-text="index + 1"></span>.
        <span x-text="item"></span>
    </div>
</template>
```

First parameter = item value
Second parameter = index (starts at 0)
</details>

---

## Key Takeaways

1. **x-for loops through arrays** - Renders elements for each item
2. **Must use `<template>`** - x-for only works on template tags
3. **:key is required** - Use unique, stable identifiers
4. **Access index with (item, index)** - Second parameter is the index
5. **Automatically reactive** - Change array, list updates
6. **Use computed properties for filtering** - Keep templates clean
7. **Handle empty states** - Show message when no items
8. **Reactive array methods** - push, pop, splice, etc. trigger updates

**Next lesson**: We'll learn `x-on` for event handling in detail!
