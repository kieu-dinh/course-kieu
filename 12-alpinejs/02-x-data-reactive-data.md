# Lesson 02 - x-data and Reactive Data

**Duration**: 1-1.5 hours

---

## What is x-data?

`x-data` is the **heart** of every Alpine component. It defines the reactive data that powers your component's behavior.

Think of `x-data` as:
- A **data store** for your component
- The **state** that drives what users see
- A **JavaScript object** attached to an HTML element

### Basic Syntax

```html
<div x-data="{ property: value }">
    <!-- Component content can access 'property' -->
</div>
```

Everything inside this div has access to `property`!

---

## Simple Data Examples

### Example 1: Counter

```html
<div x-data="{ count: 0 }">
    <p>Count: <span x-text="count"></span></p>
    <button @click="count++">Increment</button>
</div>
```

**Data**: `{ count: 0 }`
- `count` is a number
- Starts at 0
- Can be changed by clicking the button

### Example 2: Text

```html
<div x-data="{ message: 'Hello Alpine!' }">
    <p x-text="message"></p>
    <button @click="message = 'Goodbye!'">Change</button>
</div>
```

**Data**: `{ message: 'Hello Alpine!' }`
- `message` is a string
- Starts as 'Hello Alpine!'
- Changes to 'Goodbye!' when clicked

### Example 3: Boolean

```html
<div x-data="{ isOpen: false }">
    <button @click="isOpen = !isOpen">Toggle</button>
    <p x-show="isOpen">I'm visible!</p>
</div>
```

**Data**: `{ isOpen: false }`
- `isOpen` is a boolean (true/false)
- Starts as `false` (hidden)
- Toggles on each click

---

## Data Types in x-data

You can use any JavaScript data type!

### 1. Numbers

```html
<div x-data="{
    age: 25,
    price: 99.99,
    quantity: 0
}">
    <p>Age: <span x-text="age"></span></p>
    <p>Price: $<span x-text="price"></span></p>
    <p>Quantity: <span x-text="quantity"></span></p>
</div>
```

### 2. Strings

```html
<div x-data="{
    name: 'Kieu',
    status: 'online',
    message: ''
}">
    <p>Name: <span x-text="name"></span></p>
    <p>Status: <span x-text="status"></span></p>
</div>
```

### 3. Booleans

```html
<div x-data="{
    isLoggedIn: true,
    hasError: false,
    isLoading: false
}">
    <p x-show="isLoggedIn">Welcome back!</p>
    <p x-show="hasError">Something went wrong!</p>
    <p x-show="isLoading">Loading...</p>
</div>
```

### 4. Arrays

```html
<div x-data="{
    items: ['Apple', 'Banana', 'Cherry'],
    numbers: [1, 2, 3, 4, 5],
    tasks: []
}">
    <p>First item: <span x-text="items[0]"></span></p>
    <p>Total items: <span x-text="items.length"></span></p>
</div>
```

### 5. Objects

```html
<div x-data="{
    user: {
        name: 'Kieu',
        email: 'kieu@example.com',
        age: 25
    }
}">
    <p>Name: <span x-text="user.name"></span></p>
    <p>Email: <span x-text="user.email"></span></p>
    <p>Age: <span x-text="user.age"></span></p>
</div>
```

### 6. Nested Data

```html
<div x-data="{
    cart: {
        items: [
            { name: 'Laptop', price: 999 },
            { name: 'Mouse', price: 29 }
        ],
        total: 1028
    }
}">
    <p>First item: <span x-text="cart.items[0].name"></span></p>
    <p>Total: $<span x-text="cart.total"></span></p>
</div>
```

---

## Understanding Reactivity

**Reactivity** means: When data changes, the UI updates automatically!

### How Reactivity Works

```html
<div x-data="{ count: 0 }">
    <p>Count: <span x-text="count"></span></p>
    <!-- Shows: Count: 0 -->

    <button @click="count++">Increment</button>
    <!-- Click! -->

    <!-- Alpine detects 'count' changed -->
    <!-- Alpine finds all places using 'count' -->
    <!-- Alpine updates those places automatically -->
    <!-- Now shows: Count: 1 -->
</div>
```

**You don't need to:**
- Manually update the DOM
- Call an update function
- Trigger a re-render

**Alpine does it automatically!**

### Reactivity Example: Live Character Counter

```html
<div x-data="{ text: '' }">
    <textarea
        x-model="text"
        placeholder="Type something..."
        rows="5"
        style="width: 100%"
    ></textarea>

    <p>Characters: <span x-text="text.length"></span></p>
    <p x-show="text.length > 100" style="color: red;">
        Too long! Maximum 100 characters.
    </p>
</div>
```

**What happens:**
1. User types → `text` changes
2. `text.length` recalculates automatically
3. Character count updates
4. Warning appears if over 100 characters

All reactive, no manual code needed!

---

## Multiple Properties

You can define multiple properties in one component:

```html
<div x-data="{
    firstName: 'John',
    lastName: 'Doe',
    age: 30,
    isActive: true
}">
    <p>Name: <span x-text="firstName + ' ' + lastName"></span></p>
    <p>Age: <span x-text="age"></span></p>
    <p x-show="isActive">Status: Active</p>
</div>
```

**Important:** Use commas to separate properties!

---

## Accessing Data in Alpine

Once you define data with `x-data`, you can access it anywhere inside that component:

### In Directives

```html
<div x-data="{ name: 'Kieu' }">
    <!-- x-text -->
    <p x-text="name"></p>

    <!-- x-show -->
    <div x-show="name === 'Kieu'">Hello Kieu!</div>

    <!-- @click -->
    <button @click="name = 'John'">Change to John</button>

    <!-- x-bind -->
    <input type="text" x-bind:value="name">

    <!-- x-model -->
    <input type="text" x-model="name">
</div>
```

### In Expressions

You can use JavaScript expressions:

```html
<div x-data="{
    price: 100,
    quantity: 3,
    discount: 0.1
}">
    <!-- Calculate total -->
    <p>Subtotal: $<span x-text="price * quantity"></span></p>

    <!-- Calculate with discount -->
    <p>Discount: $<span x-text="price * quantity * discount"></span></p>

    <!-- Final price -->
    <p>Total: $<span x-text="price * quantity * (1 - discount)"></span></p>
</div>
```

### Using Methods

You can even call JavaScript functions:

```html
<div x-data="{
    firstName: 'John',
    lastName: 'Doe'
}">
    <!-- Use template literals -->
    <p x-text="`${firstName} ${lastName}`"></p>

    <!-- Use toUpperCase() -->
    <p x-text="firstName.toUpperCase()"></p>

    <!-- Use string concatenation -->
    <p x-text="'Hello, ' + firstName + '!'"></p>
</div>
```

---

## Component Methods

You can define **methods** (functions) inside `x-data`:

### Basic Method

```html
<div x-data="{
    count: 0,
    increment() {
        this.count++
    }
}">
    <p>Count: <span x-text="count"></span></p>
    <button @click="increment()">Increment</button>
</div>
```

**Note:** Use `this.count` to access properties inside methods!

### Methods with Parameters

```html
<div x-data="{
    count: 0,
    add(amount) {
        this.count += amount
    }
}">
    <p>Count: <span x-text="count"></span></p>
    <button @click="add(1)">+1</button>
    <button @click="add(5)">+5</button>
    <button @click="add(10)">+10</button>
</div>
```

### Multiple Methods

```html
<div x-data="{
    count: 0,
    increment() {
        this.count++
    },
    decrement() {
        this.count--
    },
    reset() {
        this.count = 0
    }
}">
    <p>Count: <span x-text="count"></span></p>
    <button @click="increment()">+</button>
    <button @click="decrement()">-</button>
    <button @click="reset()">Reset</button>
</div>
```

### Complex Logic in Methods

```html
<div x-data="{
    items: [],
    newItem: '',
    addItem() {
        if (this.newItem.trim() !== '') {
            this.items.push(this.newItem)
            this.newItem = ''
        }
    },
    removeItem(index) {
        this.items.splice(index, 1)
    }
}">
    <input x-model="newItem" type="text" placeholder="Add item">
    <button @click="addItem()">Add</button>

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

---

## Computed Properties (Getters)

You can define **computed properties** that automatically recalculate when their dependencies change:

### Using Getters

```html
<div x-data="{
    firstName: 'John',
    lastName: 'Doe',
    get fullName() {
        return this.firstName + ' ' + this.lastName
    }
}">
    <input x-model="firstName" placeholder="First name">
    <input x-model="lastName" placeholder="Last name">

    <!-- fullName updates automatically! -->
    <p>Full name: <span x-text="fullName"></span></p>
</div>
```

**Difference from methods:**
- Methods: Call with `()`  → `increment()`
- Getters: Access like properties → `fullName` (no parentheses)

### Practical Example: Shopping Cart

```html
<div x-data="{
    price: 100,
    quantity: 1,
    taxRate: 0.2,
    get subtotal() {
        return this.price * this.quantity
    },
    get tax() {
        return this.subtotal * this.taxRate
    },
    get total() {
        return this.subtotal + this.tax
    }
}">
    <label>
        Price:
        <input type="number" x-model.number="price">
    </label>

    <label>
        Quantity:
        <input type="number" x-model.number="quantity">
    </label>

    <div style="margin-top: 1rem; padding: 1rem; background: #f3f4f6;">
        <p>Subtotal: $<span x-text="subtotal.toFixed(2)"></span></p>
        <p>Tax (20%): $<span x-text="tax.toFixed(2)"></span></p>
        <p><strong>Total: $<span x-text="total.toFixed(2)"></span></strong></p>
    </div>
</div>
```

Change price or quantity → All calculations update automatically!

---

## x-data with Alpine.data()

For **reusable components**, you can define data as a function and register it globally:

### Registering Component Data

```html
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('dropdown', () => ({
            open: false,
            toggle() {
                this.open = !this.open
            }
        }))
    })
</script>

<!-- Use anywhere! -->
<div x-data="dropdown">
    <button @click="toggle()">Toggle Dropdown</button>
    <div x-show="open">
        <p>Dropdown content</p>
    </div>
</div>

<!-- Use again! -->
<div x-data="dropdown">
    <button @click="toggle()">Another Dropdown</button>
    <div x-show="open">
        <p>More content</p>
    </div>
</div>
```

**Each instance is independent** - they don't share state!

### Parameterized Components

```html
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('counter', (initial = 0) => ({
            count: initial,
            increment() {
                this.count++
            },
            decrement() {
                this.count--
            }
        }))
    })
</script>

<!-- Start at 0 -->
<div x-data="counter()">
    <button @click="decrement()">-</button>
    <span x-text="count"></span>
    <button @click="increment()">+</button>
</div>

<!-- Start at 100 -->
<div x-data="counter(100)">
    <button @click="decrement()">-</button>
    <span x-text="count"></span>
    <button @click="increment()">+</button>
</div>
```

---

## Scope and Nesting

### Component Scope

Each `x-data` creates its own scope:

```html
<!-- Component A -->
<div x-data="{ name: 'Alice' }">
    <p x-text="name"></p> <!-- Alice -->
</div>

<!-- Component B - different scope! -->
<div x-data="{ name: 'Bob' }">
    <p x-text="name"></p> <!-- Bob -->
</div>
```

They're completely independent!

### Nested Components

Child components can access parent data:

```html
<div x-data="{ count: 0 }">
    <p>Parent count: <span x-text="count"></span></p>

    <!-- Child can access parent's 'count' -->
    <div x-data="{ multiplier: 2 }">
        <p>Multiplied: <span x-text="count * multiplier"></span></p>
    </div>
</div>
```

**Rule:** Inner components can access outer data, but not vice versa!

### $parent Magic Property

Access parent data explicitly:

```html
<div x-data="{ parentCount: 0 }">
    <div x-data="{ childCount: 0 }">
        <!-- Access parent data -->
        <p>Parent: <span x-text="$parent.parentCount"></span></p>
        <p>Child: <span x-text="childCount"></span></p>
    </div>
</div>
```

---

## Common Patterns

### Pattern 1: Toggle

```html
<div x-data="{ open: false }">
    <button @click="open = !open">
        <span x-text="open ? 'Close' : 'Open'"></span>
    </button>
    <div x-show="open">Content</div>
</div>
```

### Pattern 2: Multi-step Form

```html
<div x-data="{ step: 1 }">
    <div x-show="step === 1">
        <h2>Step 1</h2>
        <button @click="step = 2">Next</button>
    </div>

    <div x-show="step === 2">
        <h2>Step 2</h2>
        <button @click="step = 1">Back</button>
        <button @click="step = 3">Next</button>
    </div>

    <div x-show="step === 3">
        <h2>Step 3</h2>
        <button @click="step = 2">Back</button>
        <button @click="step = 1">Reset</button>
    </div>
</div>
```

### Pattern 3: Tabs

```html
<div x-data="{ activeTab: 'home' }">
    <div>
        <button
            @click="activeTab = 'home'"
            :class="activeTab === 'home' ? 'active' : ''"
        >
            Home
        </button>
        <button
            @click="activeTab = 'profile'"
            :class="activeTab === 'profile' ? 'active' : ''"
        >
            Profile
        </button>
        <button
            @click="activeTab = 'settings'"
            :class="activeTab === 'settings' ? 'active' : ''"
        >
            Settings
        </button>
    </div>

    <div x-show="activeTab === 'home'">Home content</div>
    <div x-show="activeTab === 'profile'">Profile content</div>
    <div x-show="activeTab === 'settings'">Settings content</div>
</div>
```

### Pattern 4: Simple Form

```html
<div x-data="{
    formData: {
        name: '',
        email: '',
        message: ''
    },
    submitted: false,
    submit() {
        console.log('Form data:', this.formData)
        this.submitted = true
    }
}">
    <form @submit.prevent="submit()" x-show="!submitted">
        <input x-model="formData.name" placeholder="Name" required>
        <input x-model="formData.email" type="email" placeholder="Email" required>
        <textarea x-model="formData.message" placeholder="Message"></textarea>
        <button type="submit">Submit</button>
    </form>

    <div x-show="submitted">
        <h2>Thank you, <span x-text="formData.name"></span>!</h2>
        <p>We'll contact you at <span x-text="formData.email"></span></p>
    </div>
</div>
```

---

## Best Practices

### 1. Keep Data Relevant

**Bad:**
```html
<div x-data="{
    appName: 'My App',
    version: '1.0',
    author: 'Me',
    count: 0
}">
    <button @click="count++">Count: <span x-text="count"></span></button>
</div>
```

**Good:**
```html
<div x-data="{ count: 0 }">
    <button @click="count++">Count: <span x-text="count"></span></button>
</div>
```

Only include data this component actually uses!

### 2. Use Descriptive Names

**Bad:**
```html
<div x-data="{
    x: false,
    y: 0,
    z: ''
}">
```

**Good:**
```html
<div x-data="{
    isModalOpen: false,
    currentStep: 0,
    errorMessage: ''
}">
```

Clear names make code self-documenting!

### 3. Group Related Data

**Bad:**
```html
<div x-data="{
    userName: '',
    userEmail: '',
    userAge: 0,
    userCity: ''
}">
```

**Good:**
```html
<div x-data="{
    user: {
        name: '',
        email: '',
        age: 0,
        city: ''
    }
}">
```

Objects organize related properties!

### 4. Use Methods for Complex Logic

**Bad:**
```html
<button @click="items.push({ id: Date.now(), text: newItem, done: false }); newItem = ''">
    Add
</button>
```

**Good:**
```html
<div x-data="{
    items: [],
    newItem: '',
    addItem() {
        this.items.push({
            id: Date.now(),
            text: this.newItem,
            done: false
        })
        this.newItem = ''
    }
}">
    <button @click="addItem()">Add</button>
</div>
```

Methods keep HTML clean and logic testable!

---

## Practice Exercise

Create a **profile card** component with editable fields.

### Requirements

1. Component data should include:
   - `name` (string)
   - `bio` (string)
   - `age` (number)
   - `isEditing` (boolean, starts false)

2. When NOT editing:
   - Display name, bio, and age
   - Show an "Edit" button

3. When editing:
   - Show input fields to edit name, bio, and age
   - Show "Save" button that stops editing
   - Show "Cancel" button that discards changes

### Hints

- Use a method `startEdit()` to enter edit mode
- Use a method `saveEdit()` to exit edit mode
- Use a method `cancelEdit()` to discard changes (reset to original)
- You might need to store original values to cancel properly

### Starter Code

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Card</title>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        body {
            font-family: system-ui;
            padding: 2rem;
            background: #f3f4f6;
        }
        .card {
            max-width: 500px;
            margin: 0 auto;
            background: white;
            padding: 2rem;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        input, textarea {
            width: 100%;
            padding: 0.5rem;
            margin: 0.5rem 0;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
        }
        button {
            padding: 0.5rem 1rem;
            margin: 0.5rem 0.5rem 0 0;
            background: #3b82f6;
            color: white;
            border: none;
            border-radius: 0.375rem;
            cursor: pointer;
        }
        button:hover {
            background: #2563eb;
        }
    </style>
</head>
<body>
    <!-- TODO: Add your Alpine component here -->
    <div class="card">
        <h1>Profile Card</h1>
        <!-- Add your implementation -->
    </div>
</body>
</html>
```

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
    <title>Profile Card</title>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        body {
            font-family: system-ui;
            padding: 2rem;
            background: #f3f4f6;
        }
        .card {
            max-width: 500px;
            margin: 0 auto;
            background: white;
            padding: 2rem;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        input, textarea {
            width: 100%;
            padding: 0.5rem;
            margin: 0.5rem 0;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            box-sizing: border-box;
        }
        button {
            padding: 0.5rem 1rem;
            margin: 0.5rem 0.5rem 0 0;
            background: #3b82f6;
            color: white;
            border: none;
            border-radius: 0.375rem;
            cursor: pointer;
        }
        button:hover {
            background: #2563eb;
        }
        .cancel {
            background: #6b7280;
        }
        .cancel:hover {
            background: #4b5563;
        }
    </style>
</head>
<body>
    <div class="card" x-data="{
        name: 'Kieu Nguyen',
        bio: 'Learning Alpine.js and loving it!',
        age: 25,
        isEditing: false,
        originalData: {},
        startEdit() {
            // Save original data for cancel
            this.originalData = {
                name: this.name,
                bio: this.bio,
                age: this.age
            }
            this.isEditing = true
        },
        saveEdit() {
            this.isEditing = false
        },
        cancelEdit() {
            // Restore original data
            this.name = this.originalData.name
            this.bio = this.originalData.bio
            this.age = this.originalData.age
            this.isEditing = false
        }
    }">
        <h1>Profile Card</h1>

        <!-- View Mode -->
        <div x-show="!isEditing">
            <h2 x-text="name"></h2>
            <p x-text="bio"></p>
            <p>Age: <span x-text="age"></span></p>
            <button @click="startEdit()">Edit Profile</button>
        </div>

        <!-- Edit Mode -->
        <div x-show="isEditing">
            <div>
                <label>Name:</label>
                <input type="text" x-model="name">
            </div>

            <div>
                <label>Bio:</label>
                <textarea x-model="bio" rows="3"></textarea>
            </div>

            <div>
                <label>Age:</label>
                <input type="number" x-model.number="age">
            </div>

            <button @click="saveEdit()">Save</button>
            <button @click="cancelEdit()" class="cancel">Cancel</button>
        </div>
    </div>
</body>
</html>
```

**Key concepts used:**
1. `isEditing` boolean to toggle between view/edit modes
2. `originalData` object to store values for canceling
3. `startEdit()` method saves original data before editing
4. `saveEdit()` simply exits edit mode (changes are already applied via `x-model`)
5. `cancelEdit()` restores original data and exits edit mode
6. `x-show` to conditionally display view or edit sections
7. `x-model` for two-way binding on inputs

</details>

---

## Quick Quiz

### Question 1
What's the difference between a method and a getter in x-data?

<details>
<summary>Answer</summary>

**Method**: Called with parentheses, can have side effects
```javascript
x-data="{
    increment() {
        this.count++  // Side effect: modifies data
    }
}"
// Use: @click="increment()"
```

**Getter**: Accessed like a property, should be pure (no side effects)
```javascript
x-data="{
    get total() {
        return this.price * this.quantity  // Pure: just returns a value
    }
}"
// Use: x-text="total" (no parentheses!)
```

Getters are for calculated/computed values. Methods are for actions.
</details>

### Question 2
How do you access parent component data from a child component?

<details>
<summary>Answer</summary>

Use the `$parent` magic property:

```html
<div x-data="{ parentValue: 'Hello' }">
    <div x-data="{ childValue: 'World' }">
        <p x-text="$parent.parentValue"></p> <!-- Hello -->
        <p x-text="childValue"></p> <!-- World -->
    </div>
</div>
```

Or just reference it directly - child components inherit parent scope:
```html
<div x-data="{ parentValue: 'Hello' }">
    <div x-data="{ childValue: 'World' }">
        <p x-text="parentValue"></p> <!-- Also works! -->
    </div>
</div>
```
</details>

---

## Key Takeaways

1. **x-data defines component state** - All reactive data starts here
2. **Any JavaScript type works** - Strings, numbers, arrays, objects, booleans
3. **Reactivity is automatic** - Change data, UI updates
4. **Use methods for logic** - Keep HTML clean, logic in methods
5. **Use getters for computed values** - Automatic recalculation when dependencies change
6. **Each x-data creates a scope** - Components are isolated unless nested
7. **Alpine.data() for reusable components** - Define once, use many times

**Next lesson**: We'll explore `x-show` and `x-if` for conditional rendering!
