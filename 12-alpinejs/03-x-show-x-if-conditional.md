# Lesson 03 - x-show & x-if (Conditional Rendering)

**Duration**: 1 hour

---

## What is Conditional Rendering?

**Conditional rendering** means showing or hiding elements based on conditions.

Examples:
- Show a success message after form submission
- Hide a modal when closed
- Display different content for logged-in vs logged-out users
- Show errors only when they exist

Alpine gives us two directives for this:
- `x-show` - Toggles CSS display property
- `x-if` - Adds/removes element from DOM

---

## x-show Directive

`x-show` shows or hides an element by toggling its CSS `display` property.

### Basic Syntax

```html
<div x-show="condition">
    This shows when condition is true
</div>
```

### Simple Example

```html
<div x-data="{ open: false }">
    <button @click="open = !open">Toggle</button>

    <div x-show="open">
        <p>I'm visible!</p>
    </div>
</div>
```

**How it works:**
- When `open` is `false`: Element gets `display: none`
- When `open` is `true`: Element becomes visible
- Element stays in DOM, just hidden with CSS

### Real-World Example: Modal

```html
<div x-data="{ showModal: false }">
    <button @click="showModal = true">Open Modal</button>

    <!-- Backdrop -->
    <div
        x-show="showModal"
        @click="showModal = false"
        style="position: fixed; inset: 0; background: rgba(0,0,0,0.5);"
    >
        <!-- Modal -->
        <div
            @click.stop
            style="background: white; padding: 2rem; margin: 10% auto; max-width: 500px; border-radius: 0.5rem;"
        >
            <h2>Modal Title</h2>
            <p>This is a modal dialog!</p>
            <button @click="showModal = false">Close</button>
        </div>
    </div>
</div>
```

**Key points:**
- `@click.stop` prevents closing when clicking inside modal
- `@click="showModal = false"` on backdrop closes modal
- Everything stays in DOM, just hidden

---

## x-if Directive

`x-if` completely adds or removes elements from the DOM.

### Basic Syntax

```html
<template x-if="condition">
    <div>
        This is added to DOM when condition is true
    </div>
</template>
```

**Important:** `x-if` must be on a `<template>` tag!

### Simple Example

```html
<div x-data="{ loggedIn: false }">
    <button @click="loggedIn = !loggedIn">Toggle Login</button>

    <template x-if="loggedIn">
        <div>
            <p>Welcome back!</p>
        </div>
    </template>

    <template x-if="!loggedIn">
        <div>
            <p>Please log in</p>
        </div>
    </template>
</div>
```

**How it works:**
- When `loggedIn` is `true`: First div is added to DOM
- When `loggedIn` is `false`: First div is removed, second div is added
- Elements are actually created/destroyed

---

## x-show vs x-if: When to Use Which?

### x-show

**Use when:**
- Element toggles frequently
- Element is simple/lightweight
- You need smooth transitions
- Initial state doesn't matter much

**Pros:**
- Faster toggling (just CSS change)
- Works with CSS transitions
- Simpler syntax

**Cons:**
- Element always in DOM (takes up memory)
- Runs initialization code even when hidden
- Can't use with `<template>`

### x-if

**Use when:**
- Element rarely toggles
- Element is heavy/complex (lots of children)
- You want to skip initialization when hidden
- Need to completely remove from DOM

**Pros:**
- Saves memory (element not in DOM)
- Skips initialization when false
- Truly removes element

**Cons:**
- Slower toggling (DOM manipulation)
- Harder to animate transitions
- Must use `<template>` tag

### Visual Comparison

```html
<div x-data="{ show: false }">
    <!-- x-show: Element always in DOM -->
    <div x-show="show" style="display: none;">
        I'm hidden with CSS
    </div>

    <!-- x-if: Element not in DOM when false -->
    <!-- Nothing here when show is false! -->
    <template x-if="show">
        <div>I'm completely removed</div>
    </template>
</div>
```

### Performance Example

```html
<div x-data="{ activeTab: 'home' }">
    <!-- Tabs switch frequently → use x-show -->
    <div x-show="activeTab === 'home'">Home content</div>
    <div x-show="activeTab === 'profile'">Profile content</div>
    <div x-show="activeTab === 'settings'">Settings content</div>

    <!-- Heavy component shown once → use x-if -->
    <template x-if="userIsAdmin">
        <div>
            <!-- Complex admin panel with many components -->
        </div>
    </template>
</div>
```

---

## Expressions in Conditions

You can use any JavaScript expression:

### Comparisons

```html
<div x-data="{ age: 20 }">
    <template x-if="age >= 18">
        <p>You can vote!</p>
    </template>

    <template x-if="age < 18">
        <p>Too young to vote</p>
    </template>
</div>
```

### Logical Operators

```html
<div x-data="{
    isLoggedIn: true,
    isPremium: false
}">
    <!-- AND -->
    <template x-if="isLoggedIn && isPremium">
        <div>Premium member dashboard</div>
    </template>

    <!-- OR -->
    <template x-if="!isLoggedIn || !isPremium">
        <div>Upgrade to premium!</div>
    </template>
</div>
```

### Checking Arrays/Strings

```html
<div x-data="{
    items: ['apple', 'banana'],
    searchQuery: ''
}">
    <!-- Check array length -->
    <template x-if="items.length > 0">
        <p>You have <span x-text="items.length"></span> items</p>
    </template>

    <template x-if="items.length === 0">
        <p>No items yet</p>
    </template>

    <!-- Check string length -->
    <template x-if="searchQuery.length > 0">
        <p>Searching for: <span x-text="searchQuery"></span></p>
    </template>
</div>
```

### Ternary Expressions

You can use ternary in `x-text`, `x-bind`, etc., but not directly in `x-if`:

```html
<div x-data="{ count: 5 }">
    <!-- Works in x-text -->
    <p x-text="count > 0 ? 'Items available' : 'Out of stock'"></p>

    <!-- For x-if, use separate conditions -->
    <template x-if="count > 0">
        <p>Items available</p>
    </template>

    <template x-if="count === 0">
        <p>Out of stock</p>
    </template>
</div>
```

---

## Common Patterns

### Pattern 1: Loading States

```html
<div x-data="{
    isLoading: false,
    data: null,
    async fetchData() {
        this.isLoading = true
        // Simulate API call
        await new Promise(resolve => setTimeout(resolve, 2000))
        this.data = { message: 'Data loaded!' }
        this.isLoading = false
    }
}">
    <button @click="fetchData()">Load Data</button>

    <!-- Loading spinner -->
    <div x-show="isLoading">
        <p>Loading...</p>
    </div>

    <!-- Data display -->
    <template x-if="data">
        <div>
            <p x-text="data.message"></p>
        </div>
    </template>
</div>
```

### Pattern 2: Error Messages

```html
<div x-data="{
    email: '',
    error: '',
    validate() {
        if (!this.email.includes('@')) {
            this.error = 'Invalid email address'
        } else {
            this.error = ''
        }
    }
}">
    <input
        x-model="email"
        @input="validate()"
        type="email"
        placeholder="Enter email"
    >

    <!-- Show error if exists -->
    <p x-show="error" style="color: red;" x-text="error"></p>

    <!-- Show success if no error and email entered -->
    <p x-show="!error && email" style="color: green;">
        Email looks good!
    </p>
</div>
```

### Pattern 3: Multi-Step Forms

```html
<div x-data="{ step: 1 }">
    <!-- Progress indicator -->
    <div>
        Step <span x-text="step"></span> of 3
    </div>

    <!-- Step 1 -->
    <div x-show="step === 1">
        <h2>Personal Info</h2>
        <input type="text" placeholder="Name">
        <button @click="step = 2">Next</button>
    </div>

    <!-- Step 2 -->
    <div x-show="step === 2">
        <h2>Contact Info</h2>
        <input type="email" placeholder="Email">
        <button @click="step = 1">Back</button>
        <button @click="step = 3">Next</button>
    </div>

    <!-- Step 3 -->
    <div x-show="step === 3">
        <h2>Confirm</h2>
        <button @click="step = 2">Back</button>
        <button>Submit</button>
    </div>
</div>
```

### Pattern 4: Accordion/Collapsible

```html
<div x-data="{ expanded: false }">
    <div
        @click="expanded = !expanded"
        style="cursor: pointer; padding: 1rem; background: #f3f4f6;"
    >
        <span x-text="expanded ? '▼' : '▶'"></span>
        <strong>Click to expand</strong>
    </div>

    <div x-show="expanded" style="padding: 1rem; border: 1px solid #e5e7eb;">
        <p>This content can be expanded and collapsed!</p>
        <p>Lorem ipsum dolor sit amet...</p>
    </div>
</div>
```

### Pattern 5: Conditional Classes

Combine `x-show` with dynamic classes:

```html
<div x-data="{ active: false }">
    <button
        @click="active = !active"
        :class="active ? 'bg-green-500' : 'bg-gray-500'"
    >
        Toggle
    </button>

    <div
        x-show="active"
        :class="active ? 'fade-in' : 'fade-out'"
    >
        Content with animation classes
    </div>
</div>
```

---

## Transitions with x-show

`x-show` works beautifully with Alpine's transition directives!

### Basic Transition

```html
<div x-data="{ open: false }">
    <button @click="open = !open">Toggle</button>

    <div
        x-show="open"
        x-transition
    >
        <p>I fade in and out!</p>
    </div>
</div>
```

`x-transition` adds a smooth fade effect automatically!

### Custom Transition

```html
<div x-data="{ open: false }">
    <button @click="open = !open">Toggle</button>

    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform scale-90"
        x-transition:enter-end="opacity-100 transform scale-100"
        x-transition:leave="transition ease-in duration-300"
        x-transition:leave-start="opacity-100 transform scale-100"
        x-transition:leave-end="opacity-0 transform scale-90"
    >
        <p>I scale and fade!</p>
    </div>
</div>
```

**Note:** We'll cover transitions in depth in a later lesson!

### Why x-if Can't Transition

```html
<!-- This WON'T work smoothly -->
<template x-if="open">
    <div x-transition>
        Element is removed instantly, no time for transition!
    </div>
</template>
```

`x-if` removes the element immediately - transitions need the element in DOM!

**Use `x-show` for transitions!**

---

## Debugging Conditionals

### Check Current State

Add temporary debug info:

```html
<div x-data="{ isOpen: false }">
    <!-- Debug helper -->
    <p>isOpen: <span x-text="isOpen"></span></p>

    <button @click="isOpen = !isOpen">Toggle</button>
    <div x-show="isOpen">Content</div>
</div>
```

### Console Logging

```html
<div x-data="{ count: 0 }">
    <button
        @click="count++; console.log('Count:', count)"
    >
        Increment
    </button>

    <template x-if="count > 5">
        <p>Count is greater than 5!</p>
    </template>
</div>
```

### Common Mistakes

**Mistake 1: Wrong comparison**
```html
<!-- Wrong - comparing to string -->
<div x-data="{ count: 0 }">
    <template x-if="count === '0'">  <!-- Never true! -->
        <p>Zero</p>
    </template>
</div>

<!-- Correct -->
<template x-if="count === 0">
    <p>Zero</p>
</template>
```

**Mistake 2: Missing template tag with x-if**
```html
<!-- Wrong -->
<div x-if="condition">Won't work!</div>

<!-- Correct -->
<template x-if="condition">
    <div>Works!</div>
</template>
```

**Mistake 3: Multiple root elements in x-if**
```html
<!-- Wrong - x-if template needs ONE root -->
<template x-if="condition">
    <p>First</p>
    <p>Second</p>  <!-- Error! -->
</template>

<!-- Correct - wrap in single element -->
<template x-if="condition">
    <div>
        <p>First</p>
        <p>Second</p>
    </div>
</template>
```

---

## Comparison: Vanilla JS vs Alpine

### Vanilla JavaScript Way

```html
<div id="app">
    <button id="toggleBtn">Toggle</button>
    <div id="content" style="display: none;">
        <p>Content here</p>
    </div>
</div>

<script>
    const toggleBtn = document.getElementById('toggleBtn')
    const content = document.getElementById('content')
    let isOpen = false

    toggleBtn.addEventListener('click', () => {
        isOpen = !isOpen
        content.style.display = isOpen ? 'block' : 'none'
    })
</script>
```

### Alpine Way

```html
<div x-data="{ isOpen: false }">
    <button @click="isOpen = !isOpen">Toggle</button>
    <div x-show="isOpen">
        <p>Content here</p>
    </div>
</div>
```

**Much cleaner!** No IDs, no querySelector, no manual DOM manipulation.

---

## Practice Exercise

Create a **notification system** with different types of messages.

### Requirements

1. Component should have:
   - `notifications` array (empty initially)
   - `addNotification(type, message)` method
   - `removeNotification(index)` method

2. Notification types: 'success', 'error', 'warning', 'info'

3. Different colors for each type:
   - Success: Green background
   - Error: Red background
   - Warning: Yellow background
   - Info: Blue background

4. Each notification should:
   - Show the message
   - Have a close button
   - Auto-remove after 5 seconds (bonus!)

### Starter Code

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notification System</title>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        body {
            font-family: system-ui;
            padding: 2rem;
        }
        .notification-container {
            position: fixed;
            top: 1rem;
            right: 1rem;
            width: 300px;
        }
        .notification {
            padding: 1rem;
            margin-bottom: 0.5rem;
            border-radius: 0.375rem;
            display: flex;
            justify-content: space-between;
            align-items: start;
        }
        .notification.success { background: #10b981; color: white; }
        .notification.error { background: #ef4444; color: white; }
        .notification.warning { background: #f59e0b; color: white; }
        .notification.info { background: #3b82f6; color: white; }
        .close-btn {
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
            padding: 0;
            line-height: 1;
        }
        button {
            margin: 0.5rem 0.5rem 0 0;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 0.375rem;
            cursor: pointer;
            color: white;
        }
        .btn-success { background: #10b981; }
        .btn-error { background: #ef4444; }
        .btn-warning { background: #f59e0b; }
        .btn-info { background: #3b82f6; }
    </style>
</head>
<body>
    <!-- TODO: Add your notification system here -->
    <div>
        <h1>Notification System</h1>

        <!-- TODO: Add buttons to trigger different notification types -->

        <!-- TODO: Add notification container -->
    </div>
</body>
</html>
```

### Hints

- Use `x-for` to loop through notifications (we'll cover this next lesson, but try it!)
- Use `:class` to dynamically set notification type class
- For auto-remove: Use `setTimeout()` in `addNotification()`
- Each notification needs a unique ID (use `Date.now()`)

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
    <title>Notification System</title>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        body {
            font-family: system-ui;
            padding: 2rem;
        }
        .notification-container {
            position: fixed;
            top: 1rem;
            right: 1rem;
            width: 300px;
        }
        .notification {
            padding: 1rem;
            margin-bottom: 0.5rem;
            border-radius: 0.375rem;
            display: flex;
            justify-content: space-between;
            align-items: start;
        }
        .notification.success { background: #10b981; color: white; }
        .notification.error { background: #ef4444; color: white; }
        .notification.warning { background: #f59e0b; color: white; }
        .notification.info { background: #3b82f6; color: white; }
        .close-btn {
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
            padding: 0;
            line-height: 1;
        }
        button {
            margin: 0.5rem 0.5rem 0 0;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 0.375rem;
            cursor: pointer;
            color: white;
        }
        .btn-success { background: #10b981; }
        .btn-error { background: #ef4444; }
        .btn-warning { background: #f59e0b; }
        .btn-info { background: #3b82f6; }
    </style>
</head>
<body>
    <div x-data="{
        notifications: [],
        addNotification(type, message) {
            const id = Date.now()
            this.notifications.push({ id, type, message })

            // Auto-remove after 5 seconds
            setTimeout(() => {
                this.removeNotification(id)
            }, 5000)
        },
        removeNotification(id) {
            this.notifications = this.notifications.filter(n => n.id !== id)
        }
    }">
        <h1>Notification System</h1>

        <div>
            <button
                @click="addNotification('success', 'Operation successful!')"
                class="btn-success"
            >
                Show Success
            </button>

            <button
                @click="addNotification('error', 'Something went wrong!')"
                class="btn-error"
            >
                Show Error
            </button>

            <button
                @click="addNotification('warning', 'Warning: Check this!')"
                class="btn-warning"
            >
                Show Warning
            </button>

            <button
                @click="addNotification('info', 'Here is some info')"
                class="btn-info"
            >
                Show Info
            </button>
        </div>

        <!-- Notification Container -->
        <div class="notification-container">
            <template x-for="notification in notifications" :key="notification.id">
                <div
                    class="notification"
                    :class="notification.type"
                    x-transition
                >
                    <span x-text="notification.message"></span>
                    <button
                        @click="removeNotification(notification.id)"
                        class="close-btn"
                    >
                        ×
                    </button>
                </div>
            </template>
        </div>
    </div>
</body>
</html>
```

**Key concepts:**
1. `notifications` array stores all active notifications
2. Each notification has `id`, `type`, and `message`
3. `addNotification()` creates notification and sets auto-remove timer
4. `removeNotification()` filters out the notification by ID
5. `x-for` loops through notifications (next lesson!)
6. `:class` dynamically sets the type class
7. `x-transition` adds smooth fade effect
8. Close button removes notification immediately

</details>

---

## Quick Quiz

### Question 1
When should you use `x-show` vs `x-if`?

<details>
<summary>Answer</summary>

**Use `x-show` when:**
- Element toggles frequently (tabs, dropdowns, modals)
- You need CSS transitions/animations
- Element is lightweight

**Use `x-if` when:**
- Element rarely changes state
- Element is heavy/complex (many children)
- You need to skip initialization when hidden
- Memory is a concern

General rule: **Start with `x-show`, switch to `x-if` if you have performance issues.**
</details>

### Question 2
Why must `x-if` be on a `<template>` tag?

<details>
<summary>Answer</summary>

Because `x-if` adds/removes elements from the DOM. The `<template>` tag is a browser-native element that:
- Doesn't render itself
- Holds content that can be cloned/inserted
- Allows Alpine to add/remove the content without wrapper divs

Without `<template>`, Alpine would have to manipulate the element itself, which causes issues.

```html
<!-- Correct -->
<template x-if="show">
    <div>Content</div>
</template>

<!-- Wrong -->
<div x-if="show">Content</div>
```
</details>

### Question 3
Can you use `x-show` and `x-if` on the same element?

<details>
<summary>Answer</summary>

No! They serve similar purposes - you should use one or the other, not both.

```html
<!-- Wrong - don't do this -->
<template x-if="condition" x-show="condition">
    <div>Content</div>
</template>

<!-- Correct - choose one -->
<template x-if="condition">
    <div>Content</div>
</template>

<!-- Or -->
<div x-show="condition">Content</div>
```

Mixing them creates confusion and unnecessary complexity.
</details>

---

## Key Takeaways

1. **x-show hides with CSS** - Element stays in DOM, just hidden
2. **x-if removes from DOM** - Element is added/removed completely
3. **Use x-show for frequent toggles** - Faster, works with transitions
4. **Use x-if for rare toggles** - Saves memory, skips initialization
5. **x-if requires `<template>`** - Must wrap content in template tag
6. **Transitions need x-show** - Can't animate elements being removed
7. **Use any JS expression** - Comparisons, logical operators, etc.

**Next lesson**: We'll learn `x-for` to loop through arrays and render lists!
