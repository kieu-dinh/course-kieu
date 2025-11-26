# Lesson 05 - x-on (Event Handling)

**Duration**: 1-1.5 hours

---

## What is x-on?

`x-on` lets you listen for browser events and run code when they happen.

Think of it as Alpine's version of `addEventListener()` - but much simpler!

### Events Everywhere

Web applications are **event-driven**:
- User clicks a button → `click` event
- User types in input → `input` event
- Mouse moves over element → `mouseover` event
- Form is submitted → `submit` event
- And many more!

`x-on` handles all of these!

---

## Basic Syntax

### Long Form

```html
<button x-on:click="count++">Click me</button>
```

### Short Form (Recommended)

```html
<button @click="count++">Click me</button>
```

`@` is shorthand for `x-on:` - much cleaner!

**Parts:**
- `@` - Event listener indicator
- `click` - Event name
- `"count++"` - Code to run when event fires

---

## Common Events

### Click Events

```html
<div x-data="{ count: 0 }">
    <button @click="count++">
        Clicked <span x-text="count"></span> times
    </button>
</div>
```

### Input Events

```html
<div x-data="{ message: '' }">
    <input
        type="text"
        @input="message = $event.target.value"
        placeholder="Type something"
    >
    <p>You typed: <span x-text="message"></span></p>
</div>
```

**Note:** `x-model` is easier for inputs - we'll cover that next lesson!

### Submit Events

```html
<div x-data="{
    email: '',
    handleSubmit() {
        alert('Form submitted with: ' + this.email)
    }
}">
    <form @submit.prevent="handleSubmit()">
        <input x-model="email" type="email" placeholder="Email">
        <button type="submit">Submit</button>
    </form>
</div>
```

`.prevent` prevents the default form submission!

### Mouse Events

```html
<div x-data="{ x: 0, y: 0 }">
    <div
        @mousemove="x = $event.clientX; y = $event.clientY"
        style="height: 200px; background: #f3f4f6; padding: 1rem;"
    >
        Move your mouse here!
        <p>X: <span x-text="x"></span>, Y: <span x-text="y"></span></p>
    </div>
</div>
```

### Keyboard Events

```html
<div x-data="{ key: '' }">
    <input
        type="text"
        @keydown="key = $event.key"
        placeholder="Press any key"
    >
    <p>Last key pressed: <span x-text="key"></span></p>
</div>
```

### Focus Events

```html
<div x-data="{ focused: false }">
    <input
        type="text"
        @focus="focused = true"
        @blur="focused = false"
        placeholder="Click here"
    >
    <p x-show="focused" style="color: green;">Input is focused!</p>
    <p x-show="!focused" style="color: gray;">Input not focused</p>
</div>
```

---

## The $event Object

`$event` gives you access to the native JavaScript event object.

### Common Event Properties

```html
<div x-data="{}">
    <button @click="
        console.log('Event type:', $event.type);
        console.log('Target element:', $event.target);
        console.log('Current target:', $event.currentTarget);
    ">
        Click me (check console)
    </button>
</div>
```

### Event Target Value

```html
<div x-data="{ value: '' }">
    <input
        type="text"
        @input="value = $event.target.value"
    >
    <p x-text="value"></p>
</div>
```

### Mouse Position

```html
<div x-data="{ clickX: 0, clickY: 0 }">
    <div
        @click="clickX = $event.clientX; clickY = $event.clientY"
        style="height: 200px; background: #dbeafe; cursor: crosshair;"
    >
        Click anywhere!
        <p>Last click: (<span x-text="clickX"></span>, <span x-text="clickY"></span>)</p>
    </div>
</div>
```

### Keyboard Key

```html
<div x-data="{ lastKey: '' }">
    <input
        type="text"
        @keyup="lastKey = $event.key"
        placeholder="Type something"
    >
    <p>Last key: <span x-text="lastKey"></span></p>
</div>
```

---

## Event Modifiers

Modifiers change how events behave. Add them with a dot: `@click.modifier`

### .prevent

Prevents default browser behavior:

```html
<div x-data="{}">
    <!-- Without .prevent - page refreshes -->
    <form @submit="console.log('submitted')">
        <button type="submit">Submit (refreshes page)</button>
    </form>

    <!-- With .prevent - no refresh! -->
    <form @submit.prevent="console.log('submitted')">
        <button type="submit">Submit (no refresh)</button>
    </form>
</div>
```

**Common use:** Forms and links!

### .stop

Stops event from bubbling up to parent elements:

```html
<div x-data="{}" @click="alert('Parent clicked')">
    <div style="padding: 2rem; background: #f3f4f6;">
        Parent (click me)

        <!-- Without .stop - both alerts fire -->
        <button @click="alert('Button clicked')">
            Click me (both alerts)
        </button>

        <!-- With .stop - only button alert fires -->
        <button @click.stop="alert('Button clicked')">
            Click me (button only)
        </button>
    </div>
</div>
```

### .outside

Triggers when clicking outside the element:

```html
<div x-data="{ open: false }">
    <button @click="open = true">Open Dropdown</button>

    <div
        x-show="open"
        @click.outside="open = false"
        style="padding: 1rem; background: white; border: 1px solid #e5e7eb; margin-top: 0.5rem;"
    >
        Dropdown content
        <p>Click outside to close!</p>
    </div>
</div>
```

**Perfect for modals and dropdowns!**

### .window

Listen to events on the window object:

```html
<div x-data="{ scrollY: 0 }">
    <div
        @scroll.window="scrollY = window.scrollY"
        style="height: 2000px;"
    >
        <div style="position: fixed; top: 0; background: white; padding: 1rem;">
            Scroll position: <span x-text="scrollY"></span>px
        </div>
    </div>
</div>
```

### .document

Listen to events on the document:

```html
<div x-data="{ clicks: 0 }">
    <div @click.document="clicks++">
        <p>Click anywhere on the page!</p>
        <p>Total clicks: <span x-text="clicks"></span></p>
    </div>
</div>
```

### .once

Event handler only runs once:

```html
<div x-data="{ message: 'Not clicked yet' }">
    <button @click.once="message = 'Button clicked once!'">
        Click me (only works once)
    </button>
    <p x-text="message"></p>
</div>
```

### .debounce

Delays execution until user stops triggering the event:

```html
<div x-data="{ search: '', searching: false }">
    <!-- Without debounce - fires on every keystroke -->
    <input
        type="text"
        @input="search = $event.target.value"
        placeholder="Instant search"
    >

    <!-- With debounce - waits 500ms after user stops typing -->
    <input
        type="text"
        @input.debounce.500ms="search = $event.target.value; searching = true"
        placeholder="Debounced search"
    >

    <p x-show="searching">Searching for: <span x-text="search"></span></p>
</div>
```

**Perfect for search inputs and API calls!**

### .throttle

Limits how often the event can fire:

```html
<div x-data="{ count: 0 }">
    <!-- Without throttle - fires constantly -->
    <div @mousemove="count++">
        Regular: <span x-text="count"></span>
    </div>

    <!-- With throttle - max once per 1000ms -->
    <div @mousemove.throttle.1000ms="count++">
        Throttled: <span x-text="count"></span>
    </div>
</div>
```

**Perfect for scroll and resize events!**

### .self

Only trigger if event target is the element itself:

```html
<div x-data="{}">
    <div
        @click.self="alert('Clicked the div itself')"
        style="padding: 2rem; background: #f3f4f6;"
    >
        Click the gray area (not the button)
        <button @click="alert('Button clicked')">Button</button>
    </div>
</div>
```

---

## Keyboard Modifiers

Special modifiers for keyboard events:

### Specific Keys

```html
<div x-data="{ message: '' }">
    <input
        type="text"
        @keyup.enter="message = 'You pressed Enter!'"
        @keyup.escape="message = 'You pressed Escape!'"
        @keyup.space="message = 'You pressed Space!'"
        @keyup.arrow-up="message = 'You pressed Up Arrow!'"
        placeholder="Try Enter, Escape, Space, or Arrow keys"
    >
    <p x-text="message"></p>
</div>
```

**Available key modifiers:**
- `.enter`
- `.escape` / `.esc`
- `.space`
- `.arrow-up` / `.arrow-down` / `.arrow-left` / `.arrow-right`
- `.tab`
- `.delete`
- `.backspace`
- And more! Any `$event.key` value works

### Modifier Keys

```html
<div x-data="{ message: '' }">
    <div
        @click.ctrl="message = 'Ctrl + Click'"
        @click.shift="message = 'Shift + Click'"
        @click.alt="message = 'Alt + Click'"
        @click.meta="message = 'Cmd/Win + Click'"
        style="padding: 2rem; background: #f3f4f6; cursor: pointer;"
    >
        Try Ctrl+Click, Shift+Click, Alt+Click, or Cmd/Win+Click
    </div>
    <p x-text="message"></p>
</div>
```

**Modifier keys:**
- `.ctrl` - Control key
- `.shift` - Shift key
- `.alt` - Alt/Option key
- `.meta` - Cmd (Mac) or Windows key

### Combining Modifiers

```html
<div x-data="{ message: '' }">
    <input
        type="text"
        @keyup.ctrl.enter="message = 'Ctrl + Enter pressed!'"
        @keyup.shift.escape="message = 'Shift + Escape pressed!'"
        placeholder="Try Ctrl+Enter or Shift+Escape"
    >
    <p x-text="message"></p>
</div>
```

---

## Calling Methods

You can call component methods from events:

### Simple Method Call

```html
<div x-data="{
    count: 0,
    increment() {
        this.count++
    }
}">
    <button @click="increment()">
        Count: <span x-text="count"></span>
    </button>
</div>
```

### Method with Parameters

```html
<div x-data="{
    count: 0,
    add(amount) {
        this.count += amount
    }
}">
    <button @click="add(1)">+1</button>
    <button @click="add(5)">+5</button>
    <button @click="add(10)">+10</button>
    <p>Count: <span x-text="count"></span></p>
</div>
```

### Passing Event to Method

```html
<div x-data="{
    handleClick(event) {
        console.log('Clicked at:', event.clientX, event.clientY)
        alert('Check the console!')
    }
}">
    <button @click="handleClick($event)">
        Click me
    </button>
</div>
```

### Complex Logic in Methods

```html
<div x-data="{
    items: [],
    newItem: '',
    addItem() {
        if (this.newItem.trim() === '') {
            alert('Please enter an item!')
            return
        }

        this.items.push(this.newItem)
        this.newItem = ''
    }
}">
    <input x-model="newItem" type="text" placeholder="New item">
    <button @click="addItem()">Add</button>

    <ul>
        <template x-for="item in items" :key="item">
            <li x-text="item"></li>
        </template>
    </ul>
</div>
```

---

## Multiple Events on One Element

You can listen to multiple events on the same element:

```html
<div x-data="{
    status: 'Ready',
    clicks: 0
}">
    <button
        @click="clicks++"
        @mouseenter="status = 'Hovering'"
        @mouseleave="status = 'Ready'"
    >
        Hover or Click Me
    </button>

    <p>Status: <span x-text="status"></span></p>
    <p>Clicks: <span x-text="clicks"></span></p>
</div>
```

---

## Custom Events

You can dispatch and listen to custom events!

### Dispatching Custom Events

```html
<div x-data="{
    notify() {
        this.$dispatch('notification', { message: 'Hello!' })
    }
}">
    <button @click="notify()">Send Notification</button>
</div>
```

### Listening to Custom Events

```html
<div
    x-data="{ notifications: [] }"
    @notification="notifications.push($event.detail.message)"
>
    <div x-data="{
        sendNotification() {
            this.$dispatch('notification', { message: 'New message!' })
        }
    }">
        <button @click="sendNotification()">Send</button>
    </div>

    <ul>
        <template x-for="(notif, index) in notifications" :key="index">
            <li x-text="notif"></li>
        </template>
    </ul>
</div>
```

**Custom events bubble up the DOM tree!**

---

## Common Patterns

### Pattern 1: Toggle

```html
<div x-data="{ open: false }">
    <button @click="open = !open">
        <span x-text="open ? 'Close' : 'Open'"></span>
    </button>

    <div x-show="open">
        Content here!
    </div>
</div>
```

### Pattern 2: Modal

```html
<div x-data="{ showModal: false }">
    <button @click="showModal = true">Open Modal</button>

    <div
        x-show="showModal"
        @click.self="showModal = false"
        @keyup.escape.window="showModal = false"
        style="position: fixed; inset: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center;"
    >
        <div style="background: white; padding: 2rem; border-radius: 0.5rem; max-width: 500px;">
            <h2>Modal Title</h2>
            <p>Press Escape or click outside to close</p>
            <button @click="showModal = false">Close</button>
        </div>
    </div>
</div>
```

### Pattern 3: Search with Debounce

```html
<div x-data="{
    query: '',
    results: [],
    async search() {
        if (!this.query) {
            this.results = []
            return
        }

        // Simulate API call
        await new Promise(resolve => setTimeout(resolve, 500))
        this.results = ['Result 1', 'Result 2', 'Result 3']
    }
}">
    <input
        x-model="query"
        @input.debounce.500ms="search()"
        type="text"
        placeholder="Search..."
    >

    <ul x-show="results.length > 0">
        <template x-for="result in results" :key="result">
            <li x-text="result"></li>
        </template>
    </ul>
</div>
```

### Pattern 4: Dropdown

```html
<div x-data="{ open: false }">
    <button @click="open = !open">
        Toggle Dropdown
    </button>

    <div
        x-show="open"
        @click.outside="open = false"
        style="position: absolute; background: white; border: 1px solid #e5e7eb; padding: 1rem; margin-top: 0.5rem; border-radius: 0.375rem;"
    >
        <ul>
            <li><a href="#" @click="open = false">Option 1</a></li>
            <li><a href="#" @click="open = false">Option 2</a></li>
            <li><a href="#" @click="open = false">Option 3</a></li>
        </ul>
    </div>
</div>
```

### Pattern 5: Form Validation

```html
<div x-data="{
    email: '',
    password: '',
    errors: {},
    validate() {
        this.errors = {}

        if (!this.email.includes('@')) {
            this.errors.email = 'Invalid email address'
        }

        if (this.password.length < 6) {
            this.errors.password = 'Password must be at least 6 characters'
        }
    },
    submit() {
        this.validate()

        if (Object.keys(this.errors).length === 0) {
            alert('Form is valid!')
        }
    }
}">
    <form @submit.prevent="submit()">
        <div>
            <input
                x-model="email"
                @blur="validate()"
                type="email"
                placeholder="Email"
            >
            <p x-show="errors.email" style="color: red;" x-text="errors.email"></p>
        </div>

        <div>
            <input
                x-model="password"
                @blur="validate()"
                type="password"
                placeholder="Password"
            >
            <p x-show="errors.password" style="color: red;" x-text="errors.password"></p>
        </div>

        <button type="submit">Submit</button>
    </form>
</div>
```

---

## Comparison: Vanilla JS vs Alpine

### Vanilla JavaScript

```html
<div id="app">
    <button id="myButton">Click me: <span id="count">0</span></button>
</div>

<script>
    let count = 0
    const button = document.getElementById('myButton')
    const countSpan = document.getElementById('count')

    button.addEventListener('click', () => {
        count++
        countSpan.textContent = count
    })

    // Need to manage multiple listeners manually
    button.addEventListener('mouseenter', () => {
        button.style.background = 'blue'
    })

    button.addEventListener('mouseleave', () => {
        button.style.background = ''
    })
</script>
```

### Alpine

```html
<div x-data="{ count: 0 }">
    <button
        @click="count++"
        @mouseenter="$el.style.background = 'blue'"
        @mouseleave="$el.style.background = ''"
    >
        Click me: <span x-text="count"></span>
    </button>
</div>
```

**Much cleaner and more declarative!**

---

## Practice Exercise

Create a **countdown timer** component.

### Requirements

1. Display time in format: "MM:SS"
2. Input to set initial minutes
3. "Start" button to begin countdown
4. "Pause" button to pause timer
5. "Reset" button to reset to initial time
6. Timer should automatically stop at 00:00
7. Show "Time's up!" message when timer reaches zero

### Starter Code

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Countdown Timer</title>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        body {
            font-family: system-ui;
            padding: 2rem;
            max-width: 400px;
            margin: 0 auto;
            text-align: center;
        }
        .timer {
            font-size: 4rem;
            font-weight: bold;
            margin: 2rem 0;
            font-family: 'Courier New', monospace;
        }
        input {
            padding: 0.5rem;
            font-size: 1rem;
            margin: 0.5rem;
        }
        button {
            padding: 0.5rem 1rem;
            margin: 0.5rem;
            font-size: 1rem;
            background: #3b82f6;
            color: white;
            border: none;
            border-radius: 0.375rem;
            cursor: pointer;
        }
        button:hover {
            background: #2563eb;
        }
        .alert {
            padding: 1rem;
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            border-radius: 0.375rem;
            margin-top: 1rem;
        }
    </style>
</head>
<body>
    <!-- TODO: Add your countdown timer component -->
    <h1>Countdown Timer</h1>

    <div>
        <label>
            Minutes:
            <input type="number" min="1" max="60" value="5">
        </label>
    </div>

    <div class="timer">00:00</div>

    <div>
        <!-- TODO: Add Start, Pause, Reset buttons -->
    </div>

    <!-- TODO: Add "Time's up!" message -->
</body>
</html>
```

### Hints

- Store: `minutes`, `seconds`, `isRunning`, `interval`
- Methods: `start()`, `pause()`, `reset()`, `tick()`
- Use `setInterval()` in `start()` method
- Use `clearInterval()` in `pause()` method
- Format time with computed property: `get formattedTime()`
- Stop timer when reaching 00:00

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
    <title>Countdown Timer</title>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        body {
            font-family: system-ui;
            padding: 2rem;
            max-width: 400px;
            margin: 0 auto;
            text-align: center;
        }
        .timer {
            font-size: 4rem;
            font-weight: bold;
            margin: 2rem 0;
            font-family: 'Courier New', monospace;
        }
        .timer.finished {
            color: #ef4444;
        }
        input {
            padding: 0.5rem;
            font-size: 1rem;
            margin: 0.5rem;
        }
        button {
            padding: 0.5rem 1rem;
            margin: 0.5rem;
            font-size: 1rem;
            background: #3b82f6;
            color: white;
            border: none;
            border-radius: 0.375rem;
            cursor: pointer;
        }
        button:hover {
            background: #2563eb;
        }
        button:disabled {
            background: #9ca3af;
            cursor: not-allowed;
        }
        .alert {
            padding: 1rem;
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            border-radius: 0.375rem;
            margin-top: 1rem;
        }
    </style>
</head>
<body>
    <div x-data="{
        initialMinutes: 5,
        minutes: 5,
        seconds: 0,
        isRunning: false,
        interval: null,
        get formattedTime() {
            const mins = String(this.minutes).padStart(2, '0')
            const secs = String(this.seconds).padStart(2, '0')
            return `${mins}:${secs}`
        },
        get isFinished() {
            return this.minutes === 0 && this.seconds === 0
        },
        start() {
            if (this.isRunning) return

            this.isRunning = true
            this.interval = setInterval(() => {
                this.tick()
            }, 1000)
        },
        pause() {
            this.isRunning = false
            if (this.interval) {
                clearInterval(this.interval)
                this.interval = null
            }
        },
        reset() {
            this.pause()
            this.minutes = this.initialMinutes
            this.seconds = 0
        },
        tick() {
            if (this.seconds > 0) {
                this.seconds--
            } else if (this.minutes > 0) {
                this.minutes--
                this.seconds = 59
            } else {
                // Timer finished
                this.pause()
            }
        },
        setInitialMinutes() {
            this.initialMinutes = parseInt(this.initialMinutes) || 1
            this.reset()
        }
    }" x-init="$watch('initialMinutes', () => setInitialMinutes())">
        <h1>Countdown Timer</h1>

        <div>
            <label>
                Minutes:
                <input
                    x-model.number="initialMinutes"
                    type="number"
                    min="1"
                    max="60"
                    :disabled="isRunning"
                >
            </label>
        </div>

        <div class="timer" :class="isFinished && 'finished'" x-text="formattedTime"></div>

        <div>
            <button
                @click="start()"
                :disabled="isRunning || isFinished"
            >
                Start
            </button>

            <button
                @click="pause()"
                :disabled="!isRunning"
            >
                Pause
            </button>

            <button @click="reset()">
                Reset
            </button>
        </div>

        <div x-show="isFinished" class="alert">
            <strong>Time's up!</strong>
        </div>
    </div>
</body>
</html>
```

**Key concepts:**
1. `@click` handlers for all buttons
2. `setInterval()` in `start()` method
3. `clearInterval()` in `pause()` method
4. `tick()` decrements time every second
5. `:disabled` dynamically disables buttons based on state
6. `formattedTime` computed property formats display
7. `isFinished` computed property detects when timer reaches zero
8. `x-show` displays "Time's up!" message
9. Timer automatically stops at 00:00

</details>

---

## Quick Quiz

### Question 1
What's the difference between `@click` and `x-on:click`?

<details>
<summary>Answer</summary>

They're the same! `@` is just shorthand for `x-on:`.

```html
<!-- Long form -->
<button x-on:click="count++">Click</button>

<!-- Short form (preferred) -->
<button @click="count++">Click</button>
```

Most developers use `@` because it's shorter and cleaner.
</details>

### Question 2
How do you prevent a form from submitting?

<details>
<summary>Answer</summary>

Use the `.prevent` modifier:

```html
<form @submit.prevent="handleSubmit()">
    <button type="submit">Submit</button>
</form>
```

`.prevent` calls `event.preventDefault()` for you!
</details>

### Question 3
When would you use `.debounce` vs `.throttle`?

<details>
<summary>Answer</summary>

**`.debounce`** - Waits until user **stops** triggering event
```html
<!-- Wait 500ms after user stops typing -->
<input @input.debounce.500ms="search()">
```
**Use for:** Search inputs, text validation

**`.throttle`** - Limits how **often** event can fire
```html
<!-- Max once per second while scrolling -->
<div @scroll.throttle.1000ms="updatePosition()">
```
**Use for:** Scroll, resize, mousemove events

**Think:** Debounce = "wait until they're done", Throttle = "maximum frequency"
</details>

---

## Key Takeaways

1. **`@` is shorthand for x-on** - Use `@click` instead of `x-on:click`
2. **Modifiers are powerful** - `.prevent`, `.stop`, `.outside`, `.debounce`, etc.
3. **Access event with $event** - Get event object when needed
4. **Keyboard modifiers** - `.enter`, `.escape`, `.ctrl`, etc.
5. **Call methods from events** - Keep templates clean, logic in methods
6. **Multiple events per element** - Add as many `@event` attributes as needed
7. **Custom events with $dispatch** - Create your own events that bubble up

**Next lesson**: We'll learn `x-model` for easy two-way data binding!
