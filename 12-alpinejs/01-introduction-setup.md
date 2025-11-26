# Lesson 01 - Alpine.js Introduction & Setup

**Duration**: 30-45 minutes

---

## What is Alpine.js?

Alpine.js is a lightweight JavaScript framework that brings **reactive** and **declarative** behavior to your HTML.

Think of it as "Tailwind for JavaScript" - instead of writing JavaScript in separate files, you add special attributes directly in your HTML.

### The Alpine Philosophy

Alpine describes itself as:
> "A rugged, minimal tool for composing behavior directly in your markup."

Translation: **Add interactivity to your HTML without leaving your HTML!**

### Why Learn Alpine?

**1. It's Simple**
- Only ~15 directives to learn
- No build tools required
- Add it with a single `<script>` tag

**2. It's Powerful**
- All the reactivity you need for 80% of projects
- Perfect for adding interactivity to server-rendered pages
- Great bridge between vanilla JS and full frameworks

**3. It Prepares You for Laravel**
- Laravel Livewire uses Alpine under the hood
- Understanding Alpine makes Livewire much easier
- Same mental model and syntax

**4. It's Practical**
- Real freelance projects need modals, dropdowns, tabs
- Alpine does these with minimal code
- Perfect for agency work

---

## Alpine vs Vanilla JavaScript

Let's compare a simple counter example.

### Vanilla JavaScript Way

```html
<!DOCTYPE html>
<html>
<head>
    <title>Counter - Vanilla JS</title>
</head>
<body>
    <div id="counter">
        <p>Count: <span id="count">0</span></p>
        <button id="increment">Increment</button>
        <button id="decrement">Decrement</button>
    </div>

    <script>
        // 1. Store state
        let count = 0;

        // 2. Get DOM elements
        const countSpan = document.getElementById('count');
        const incrementBtn = document.getElementById('increment');
        const decrementBtn = document.getElementById('decrement');

        // 3. Update function
        function updateDisplay() {
            countSpan.textContent = count;
        }

        // 4. Event listeners
        incrementBtn.addEventListener('click', () => {
            count++;
            updateDisplay();
        });

        decrementBtn.addEventListener('click', () => {
            count--;
            updateDisplay();
        });
    </script>
</body>
</html>
```

**Problems:**
- Separated logic: HTML, state, and updates are in different places
- Manual DOM updates: We must explicitly update the display
- Boilerplate: Need IDs, querySelector, addEventListener
- Scale issues: Imagine this with 10 components!

### Alpine.js Way

```html
<!DOCTYPE html>
<html>
<head>
    <title>Counter - Alpine.js</title>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body>
    <div x-data="{ count: 0 }">
        <p>Count: <span x-text="count"></span></p>
        <button @click="count++">Increment</button>
        <button @click="count--">Decrement</button>
    </div>
</body>
</html>
```

**Benefits:**
- Everything in one place: Data and behavior together
- Automatic updates: Alpine updates the DOM for you
- Declarative: Say WHAT you want, not HOW to do it
- Less code: ~10 lines vs ~25 lines

**That's the Alpine magic!**

---

## Core Concepts

Before diving into setup, understand these key concepts:

### 1. Directives

Alpine uses **directives** - special HTML attributes that start with `x-`:

```html
<div x-data="{ open: false }">
    <button x-on:click="open = true">Open</button>
    <div x-show="open">I'm visible!</div>
</div>
```

- `x-data` - Define reactive data
- `x-show` - Show/hide elements
- `x-on` - Listen to events
- And more!

### 2. Reactivity

When data changes, Alpine **automatically** updates the DOM:

```html
<div x-data="{ name: 'Kieu' }">
    <p x-text="name"></p>
    <!-- Shows: Kieu -->

    <button @click="name = 'John'">Change Name</button>
    <!-- Click = automatically updates the paragraph! -->
</div>
```

No need to manually update elements!

### 3. Scope

Each `x-data` creates a **scope**:

```html
<!-- Component 1 -->
<div x-data="{ count: 0 }">
    <span x-text="count"></span> <!-- 0 -->
    <button @click="count++">+</button>
</div>

<!-- Component 2 - separate scope! -->
<div x-data="{ count: 10 }">
    <span x-text="count"></span> <!-- 10 -->
    <button @click="count++">+</button>
</div>
```

Each component has its own data - they don't interfere!

### 4. Declarative vs Imperative

**Imperative** (vanilla JS): Tell HOW to do things step-by-step
```javascript
// Get element
const modal = document.getElementById('modal');
// Add class
modal.classList.add('hidden');
// Listen for click
button.addEventListener('click', () => {
    // Remove class
    modal.classList.remove('hidden');
});
```

**Declarative** (Alpine): Say WHAT you want
```html
<div x-data="{ open: false }">
    <button @click="open = true">Open Modal</button>
    <div x-show="open">Modal content</div>
</div>
```

Alpine handles the HOW - you focus on the WHAT!

---

## Setting Up Alpine.js

Alpine is incredibly easy to set up. Multiple methods available!

### Method 1: CDN (Recommended for Learning)

The simplest way - just add one `<script>` tag:

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alpine.js App</title>

    <!-- Alpine.js CDN -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body>
    <div x-data="{ message: 'Hello Alpine!' }">
        <p x-text="message"></p>
    </div>
</body>
</html>
```

**Important:** Use `defer` attribute so Alpine loads after HTML is parsed!

### Method 2: NPM (For Production Projects)

For real projects with build tools:

```bash
npm install alpinejs
```

```javascript
// app.js
import Alpine from 'alpinejs'

window.Alpine = Alpine

Alpine.start()
```

**For this course, we'll use the CDN method!**

### Method 3: Download and Host

Download from [alpinejs.dev](https://alpinejs.dev) and serve locally:

```html
<script defer src="/js/alpine.min.js"></script>
```

---

## Your First Alpine Component

Let's build a simple "show/hide" component step by step.

### Step 1: Basic HTML

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My First Alpine Component</title>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        body {
            font-family: system-ui;
            padding: 2rem;
            max-width: 600px;
            margin: 0 auto;
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
        .message {
            margin-top: 1rem;
            padding: 1rem;
            background: #dbeafe;
            border-left: 4px solid #3b82f6;
            border-radius: 0.375rem;
        }
    </style>
</head>
<body>
    <h1>Show/Hide Message</h1>

    <button>Toggle Message</button>

    <div class="message">
        <p>This is a secret message!</p>
    </div>
</body>
</html>
```

Nothing special yet - just plain HTML!

### Step 2: Add Alpine Data

Add `x-data` to create a component with state:

```html
<body>
    <h1>Show/Hide Message</h1>

    <!-- Alpine component starts here -->
    <div x-data="{ visible: false }">
        <button>Toggle Message</button>

        <div class="message">
            <p>This is a secret message!</p>
        </div>
    </div>
</body>
```

We now have a component with:
- `visible` = a piece of state (starts as `false`)

### Step 3: Add Click Handler

Make the button toggle the state:

```html
<div x-data="{ visible: false }">
    <button @click="visible = !visible">Toggle Message</button>

    <div class="message">
        <p>This is a secret message!</p>
    </div>
</div>
```

`@click` is shorthand for `x-on:click`
- When clicked, it flips `visible` (false → true, or true → false)

### Step 4: Show/Hide Based on State

Use `x-show` to control visibility:

```html
<div x-data="{ visible: false }">
    <button @click="visible = !visible">Toggle Message</button>

    <div x-show="visible" class="message">
        <p>This is a secret message!</p>
    </div>
</div>
```

**Done!** The message now shows/hides when you click the button!

### How It Works

1. **Initial state**: `visible: false` - message is hidden
2. **User clicks button**: `@click="visible = !visible"` runs
3. **State changes**: `visible` becomes `true`
4. **Alpine reacts**: `x-show="visible"` notices the change
5. **DOM updates**: Message appears automatically!

All without writing a single line of JavaScript!

---

## Understanding the Magic

### What Happens Behind the Scenes?

When Alpine loads:

1. **Finds all `x-data`**: Scans the page for Alpine components
2. **Creates reactive objects**: Turns `{ visible: false }` into a reactive state
3. **Watches directives**: Monitors `x-show`, `x-text`, `x-bind`, etc.
4. **Sets up listeners**: Attaches event handlers for `@click`, `@input`, etc.
5. **Reactivity loop**: When data changes, automatically re-evaluates all directives

### Debugging Alpine

**Check if Alpine loaded:**

Open browser console and type:
```javascript
Alpine.version
```

Should show something like: `"3.13.3"`

**Inspect Alpine data:**

Add `x-init` to log data when component initializes:

```html
<div x-data="{ count: 0 }" x-init="console.log('Count:', count)">
    <!-- Component content -->
</div>
```

**Use Alpine DevTools:**

Install [Alpine.js DevTools](https://chrome.google.com/webstore/detail/alpinejs-devtools) Chrome extension for better debugging!

---

## Common Setup Issues

### Issue 1: Alpine Loads Before HTML

**Problem:**
```html
<!-- Wrong! -->
<script src="alpine.min.js"></script>
```

Alpine runs before HTML exists - directives not found!

**Solution:**
```html
<!-- Correct! -->
<script defer src="alpine.min.js"></script>
```

The `defer` attribute waits for HTML to load first!

### Issue 2: Wrong CDN URL

**Problem:**
```html
<!-- Old version -->
<script defer src="https://unpkg.com/alpinejs@2.x.x/dist/alpine.min.js"></script>
```

**Solution:**
```html
<!-- Latest version -->
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
```

Always use Alpine 3.x for this course!

### Issue 3: Syntax Errors in x-data

**Problem:**
```html
<!-- Wrong - missing quotes -->
<div x-data="{ name: John }">
```

**Solution:**
```html
<!-- Correct - strings need quotes -->
<div x-data="{ name: 'John' }">
```

`x-data` contains JavaScript - follow JS syntax rules!

### Issue 4: Case Sensitivity

**Problem:**
```html
<!-- Wrong - capital X -->
<div X-data="{ count: 0 }">
```

**Solution:**
```html
<!-- Correct - lowercase x -->
<div x-data="{ count: 0 }">
```

All Alpine directives use lowercase `x-`!

---

## Browser Compatibility

Alpine.js 3.x works on all modern browsers:

- Chrome/Edge: ✅ 64+
- Firefox: ✅ 67+
- Safari: ✅ 12+
- Opera: ✅ 51+

**No IE11 support** - uses modern JavaScript features!

For our course: Any recent browser works fine!

---

## Quick Quiz

Test your understanding before moving on!

### Question 1
What attribute do you add to create an Alpine component?

<details>
<summary>Answer</summary>

`x-data` - This defines a component with reactive data.

Example: `<div x-data="{ count: 0 }"></div>`
</details>

### Question 2
What's the difference between `@click` and `x-on:click`?

<details>
<summary>Answer</summary>

They're the same! `@click` is just shorthand for `x-on:click`.

Both listen for click events:
- Long form: `<button x-on:click="count++">Click</button>`
- Short form: `<button @click="count++">Click</button>`

Most developers use the shorter `@click` syntax.
</details>

### Question 3
Why do we need the `defer` attribute on the Alpine script tag?

<details>
<summary>Answer</summary>

`defer` makes the script wait until the HTML is fully parsed before running.

Without it, Alpine might load before your HTML elements exist, so it can't find your `x-data` components!

```html
<!-- Correct -->
<script defer src="alpine.min.js"></script>
```
</details>

### Question 4
Can you have multiple Alpine components on the same page?

<details>
<summary>Answer</summary>

Yes! Each `x-data` creates a separate component with its own scope:

```html
<!-- Component 1 -->
<div x-data="{ count: 0 }">
    <button @click="count++">Count: <span x-text="count"></span></button>
</div>

<!-- Component 2 - independent! -->
<div x-data="{ count: 100 }">
    <button @click="count++">Count: <span x-text="count"></span></button>
</div>
```

Each component's data is isolated from the others!
</details>

---

## Practice Exercise

**Goal**: Set up Alpine and create a simple greeting component.

### Requirements

Create an HTML file with:
1. Alpine.js loaded from CDN
2. A component with data: `{ name: 'Student', showGreeting: false }`
3. An input to change the name
4. A button to toggle showing the greeting
5. A greeting message that shows: "Hello, [name]!"

### Starter Code

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alpine Greeting</title>

    <!-- TODO: Add Alpine.js CDN here -->

    <style>
        body {
            font-family: system-ui;
            padding: 2rem;
            max-width: 600px;
            margin: 0 auto;
        }
        .greeting {
            margin-top: 1rem;
            padding: 1rem;
            background: #f0fdf4;
            border-left: 4px solid #22c55e;
            border-radius: 0.375rem;
        }
        input {
            padding: 0.5rem;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            margin-right: 0.5rem;
        }
        button {
            padding: 0.5rem 1rem;
            background: #22c55e;
            color: white;
            border: none;
            border-radius: 0.375rem;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <h1>Alpine Greeting</h1>

    <!-- TODO: Add x-data with name and showGreeting -->
    <div>
        <div>
            <!-- TODO: Add input that updates name -->
            <input type="text" placeholder="Enter your name">

            <!-- TODO: Add button to toggle greeting -->
            <button>Toggle Greeting</button>
        </div>

        <!-- TODO: Add greeting div that shows conditionally -->
        <div class="greeting">
            <p>Hello, [name]!</p>
        </div>
    </div>
</body>
</html>
```

### Hints

- Use `x-data` to define your component state
- Use `x-model` to bind the input to the `name` data
- Use `@click` to toggle `showGreeting`
- Use `x-show` to conditionally display the greeting
- Use `x-text` to display the name in the greeting

### Try It Yourself First!

Give it a shot before looking at the solution. Remember:
- Alpine directives go in HTML attributes
- `x-data` creates the component
- Other directives access that data

**Don't peek at the solution until you've tried!**

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
    <title>Alpine Greeting</title>

    <!-- Alpine.js CDN -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        body {
            font-family: system-ui;
            padding: 2rem;
            max-width: 600px;
            margin: 0 auto;
        }
        .greeting {
            margin-top: 1rem;
            padding: 1rem;
            background: #f0fdf4;
            border-left: 4px solid #22c55e;
            border-radius: 0.375rem;
        }
        input {
            padding: 0.5rem;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            margin-right: 0.5rem;
        }
        button {
            padding: 0.5rem 1rem;
            background: #22c55e;
            color: white;
            border: none;
            border-radius: 0.375rem;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <h1>Alpine Greeting</h1>

    <div x-data="{ name: 'Student', showGreeting: false }">
        <div>
            <input
                type="text"
                x-model="name"
                placeholder="Enter your name"
            >

            <button @click="showGreeting = !showGreeting">
                Toggle Greeting
            </button>
        </div>

        <div x-show="showGreeting" class="greeting">
            <p>Hello, <span x-text="name"></span>!</p>
        </div>
    </div>
</body>
</html>
```

**What each part does:**

1. `x-data="{ name: 'Student', showGreeting: false }"` - Creates component with state
2. `x-model="name"` - Two-way binds input to name data
3. `@click="showGreeting = !showGreeting"` - Toggles greeting visibility
4. `x-show="showGreeting"` - Shows/hides greeting based on state
5. `x-text="name"` - Displays the current name value

Try typing in the input and clicking the button!
</details>

---

## Next Steps

You now understand:
- What Alpine.js is and why it's useful
- How to set up Alpine in your projects
- The difference between declarative and imperative code
- How Alpine's reactivity works
- How to create your first Alpine component

**Next lesson**: We'll dive deep into `x-data` and learn how to work with reactive data in Alpine!

---

## Key Takeaways

1. **Alpine is lightweight and simple** - Perfect for adding interactivity without heavy frameworks
2. **Directives start with `x-`** - These special attributes add Alpine behavior to HTML
3. **Reactivity is automatic** - Change data, Alpine updates the DOM
4. **Each `x-data` is a scope** - Components are isolated from each other
5. **CDN setup is easy** - Just one `<script defer>` tag to get started
6. **Alpine prepares you for Livewire** - Same concepts and syntax

**Ready for the next lesson?** We'll explore `x-data` in detail and learn all about reactive data!
