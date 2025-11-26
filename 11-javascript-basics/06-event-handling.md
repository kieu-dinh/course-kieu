# Lesson 06 - Event Handling

**Duration**: 3-4 hours
**Prerequisites**: Lesson 05 - DOM Manipulation

---

## What are Events?

**Events** are things that happen in the browser:
- User clicks a button
- User types in an input
- User submits a form
- Page finishes loading
- Mouse hovers over an element
- User presses a key

JavaScript can **listen** for these events and **respond** to them.

### Real-World Analogy

Think of events like a doorbell:
- **Event**: Someone presses the doorbell button
- **Event Listener**: The doorbell sensor detects the press
- **Event Handler**: You get up and answer the door

In code:
```javascript
button.addEventListener("click", function() {
    alert("Button was clicked!");
});
```

- **Event**: "click"
- **Event Listener**: `addEventListener()`
- **Event Handler**: The function that runs

---

## Adding Event Listeners

### addEventListener() - The Modern Way

```html
<button id="myButton">Click Me</button>
```

```javascript
let button = document.querySelector("#myButton");

button.addEventListener("click", function() {
    alert("Button was clicked!");
});

// Or with arrow function
button.addEventListener("click", () => {
    alert("Button was clicked!");
});
```

**Syntax:**
```javascript
element.addEventListener(eventType, handlerFunction);
```

### Old Ways (Avoid These)

```html
<!-- Inline (bad) -->
<button onclick="alert('Clicked')">Click</button>

<script>
// Property (bad)
button.onclick = function() {
    alert("Clicked");
};
</script>
```

**Why addEventListener is better:**
- Can add multiple listeners to same event
- Can remove listeners later
- Cleaner separation of HTML and JavaScript

---

## Common Event Types

### Mouse Events

```javascript
let element = document.querySelector("#element");

// Click
element.addEventListener("click", () => {
    console.log("Clicked!");
});

// Double click
element.addEventListener("dblclick", () => {
    console.log("Double clicked!");
});

// Mouse enter (hover)
element.addEventListener("mouseenter", () => {
    console.log("Mouse entered!");
});

// Mouse leave
element.addEventListener("mouseleave", () => {
    console.log("Mouse left!");
});

// Mouse move
element.addEventListener("mousemove", () => {
    console.log("Mouse moving!");
});

// Right click (context menu)
element.addEventListener("contextmenu", () => {
    console.log("Right clicked!");
});
```

### Keyboard Events

```javascript
let input = document.querySelector("#input");

// Key down (when key is pressed)
input.addEventListener("keydown", () => {
    console.log("Key down!");
});

// Key up (when key is released)
input.addEventListener("keyup", () => {
    console.log("Key up!");
});

// Key press (deprecated, but still used)
input.addEventListener("keypress", () => {
    console.log("Key pressed!");
});
```

**keydown vs keyup:**
- `keydown`: Fires when key is pressed (repeats if held)
- `keyup`: Fires when key is released (fires once)

### Form Events

```javascript
let form = document.querySelector("#form");
let input = document.querySelector("#input");

// Submit
form.addEventListener("submit", (e) => {
    e.preventDefault(); // Prevent page reload
    console.log("Form submitted!");
});

// Input (fires on every change)
input.addEventListener("input", () => {
    console.log("Input changed:", input.value);
});

// Change (fires when input loses focus)
input.addEventListener("change", () => {
    console.log("Input changed:", input.value);
});

// Focus
input.addEventListener("focus", () => {
    console.log("Input focused!");
});

// Blur (lose focus)
input.addEventListener("blur", () => {
    console.log("Input lost focus!");
});
```

### Window Events

```javascript
// Page loaded
window.addEventListener("load", () => {
    console.log("Page fully loaded!");
});

// DOM ready (before images load)
document.addEventListener("DOMContentLoaded", () => {
    console.log("DOM ready!");
});

// Window resized
window.addEventListener("resize", () => {
    console.log("Window resized!");
});

// Scroll
window.addEventListener("scroll", () => {
    console.log("Page scrolled!");
});

// Before page unload
window.addEventListener("beforeunload", (e) => {
    e.preventDefault();
    e.returnValue = ""; // Show confirmation dialog
});
```

---

## The Event Object

When an event happens, JavaScript passes an **event object** to your handler function:

```javascript
button.addEventListener("click", function(event) {
    console.log(event); // Event object
});

// Or with arrow function
button.addEventListener("click", (e) => {
    console.log(e); // Common to use 'e' or 'event'
});
```

### Common Event Properties

```javascript
element.addEventListener("click", (e) => {
    console.log(e.type);          // "click"
    console.log(e.target);        // Element that triggered event
    console.log(e.currentTarget); // Element with listener attached
    console.log(e.timeStamp);     // When event occurred
});
```

### Mouse Event Properties

```javascript
element.addEventListener("click", (e) => {
    console.log(e.clientX);  // X position relative to viewport
    console.log(e.clientY);  // Y position relative to viewport
    console.log(e.pageX);    // X position relative to page
    console.log(e.pageY);    // Y position relative to page
    console.log(e.screenX);  // X position relative to screen
    console.log(e.screenY);  // Y position relative to screen

    console.log(e.button);   // Which button: 0=left, 1=middle, 2=right
    console.log(e.shiftKey); // Was Shift pressed?
    console.log(e.ctrlKey);  // Was Ctrl pressed?
    console.log(e.altKey);   // Was Alt pressed?
    console.log(e.metaKey);  // Was Cmd (Mac) or Windows key pressed?
});
```

### Keyboard Event Properties

```javascript
input.addEventListener("keydown", (e) => {
    console.log(e.key);      // "a", "Enter", "ArrowUp", etc.
    console.log(e.code);     // "KeyA", "Enter", "ArrowUp", etc.
    console.log(e.keyCode);  // Numeric code (deprecated)

    console.log(e.shiftKey); // Was Shift pressed?
    console.log(e.ctrlKey);  // Was Ctrl pressed?
    console.log(e.altKey);   // Was Alt pressed?
});
```

**Practical Example: Detect Enter Key**

```javascript
input.addEventListener("keydown", (e) => {
    if (e.key === "Enter") {
        console.log("Enter was pressed!");
    }
});
```

### Form Event Properties

```javascript
input.addEventListener("input", (e) => {
    console.log(e.target.value); // Current input value
});

form.addEventListener("submit", (e) => {
    console.log(e.target); // The form element
});
```

---

## Event Methods

### preventDefault() - Stop Default Behavior

```html
<a href="https://google.com" id="link">Google</a>
<form id="form">
    <input type="text" required>
    <button>Submit</button>
</form>
```

```javascript
// Prevent link from navigating
let link = document.querySelector("#link");
link.addEventListener("click", (e) => {
    e.preventDefault();
    console.log("Link clicked but not following!");
});

// Prevent form from submitting
let form = document.querySelector("#form");
form.addEventListener("submit", (e) => {
    e.preventDefault();
    console.log("Form submitted but not refreshing page!");
});
```

**Use cases:**
- Handle form submission with JavaScript (AJAX)
- Create single-page applications
- Custom link behavior

### stopPropagation() - Stop Event Bubbling

We'll cover this in the Event Bubbling section below.

---

## Event Bubbling and Capturing

### Event Bubbling

When an event fires on an element, it **bubbles up** to its parents:

```html
<div id="outer">
    <div id="middle">
        <button id="inner">Click Me</button>
    </div>
</div>
```

```javascript
document.querySelector("#outer").addEventListener("click", () => {
    console.log("Outer clicked");
});

document.querySelector("#middle").addEventListener("click", () => {
    console.log("Middle clicked");
});

document.querySelector("#inner").addEventListener("click", () => {
    console.log("Inner clicked");
});

// Click the button, you'll see:
// "Inner clicked"
// "Middle clicked"
// "Outer clicked"
```

**The event bubbles from inner to outer!**

### Stopping Propagation

```javascript
document.querySelector("#inner").addEventListener("click", (e) => {
    e.stopPropagation(); // Stop bubbling
    console.log("Inner clicked");
});

// Now only "Inner clicked" will log
```

### Event Capturing (Rare)

Events can also **capture** (go from outer to inner):

```javascript
document.querySelector("#outer").addEventListener("click", () => {
    console.log("Outer clicked");
}, true); // true = use capture phase

document.querySelector("#inner").addEventListener("click", () => {
    console.log("Inner clicked");
});

// Click the button, you'll see:
// "Outer clicked" (capture phase)
// "Inner clicked" (bubble phase)
```

**You'll rarely use capture phase.**

---

## Event Delegation

Instead of adding listeners to many elements, add **one listener to a parent**.

### The Problem

```html
<ul id="list">
    <li>Item 1</li>
    <li>Item 2</li>
    <li>Item 3</li>
</ul>
```

```javascript
// Bad: Add listener to each <li>
let items = document.querySelectorAll("#list li");
items.forEach((item) => {
    item.addEventListener("click", () => {
        console.log("Item clicked:", item.textContent);
    });
});

// Problems:
// 1. Inefficient if many items
// 2. New items won't have listeners
```

### The Solution: Event Delegation

```javascript
// Good: Add one listener to <ul>
let list = document.querySelector("#list");

list.addEventListener("click", (e) => {
    // Check if clicked element is an <li>
    if (e.target.tagName === "LI") {
        console.log("Item clicked:", e.target.textContent);
    }
});

// Benefits:
// 1. One listener (efficient)
// 2. Works for dynamically added items
```

**Real-World Example: Dynamic Todo List**

```html
<div id="app">
    <input type="text" id="todo-input">
    <button id="add-btn">Add</button>
    <ul id="todo-list"></ul>
</div>
```

```javascript
let input = document.querySelector("#todo-input");
let addBtn = document.querySelector("#add-btn");
let list = document.querySelector("#todo-list");

// Add todo
addBtn.addEventListener("click", () => {
    if (input.value.trim() === "") return;

    let li = document.createElement("li");
    li.innerHTML = `
        ${input.value}
        <button class="delete-btn">Delete</button>
    `;

    list.appendChild(li);
    input.value = "";
});

// Event delegation for delete buttons
list.addEventListener("click", (e) => {
    if (e.target.classList.contains("delete-btn")) {
        e.target.parentElement.remove();
    }
});
```

**Why this works:**
- Don't need to add listener to each delete button
- Dynamically added buttons automatically work

---

## Removing Event Listeners

To remove a listener, you need a **named function**:

```javascript
function handleClick() {
    console.log("Clicked!");
}

let button = document.querySelector("#button");

// Add listener
button.addEventListener("click", handleClick);

// Remove listener
button.removeEventListener("click", handleClick);
```

**This won't work (anonymous function):**

```javascript
button.addEventListener("click", () => {
    console.log("Clicked!");
});

// Can't remove because we don't have a reference to the function
button.removeEventListener("click", () => {
    console.log("Clicked!");
}); // Won't work!
```

**Practical Example: One-Time Event**

```javascript
function handleClick() {
    console.log("Clicked once!");
    button.removeEventListener("click", handleClick); // Remove after first click
}

let button = document.querySelector("#button");
button.addEventListener("click", handleClick);
```

**Or use `once` option:**

```javascript
button.addEventListener("click", () => {
    console.log("Clicked once!");
}, { once: true }); // Automatically removes after first trigger
```

---

## Event Listener Options

```javascript
element.addEventListener("click", handler, options);
```

**Options object:**

```javascript
{
    capture: false,  // Use capture phase (default: false)
    once: false,     // Remove after first trigger (default: false)
    passive: false   // Never call preventDefault() (default: false)
}
```

**Examples:**

```javascript
// Remove after first click
button.addEventListener("click", handler, { once: true });

// Use capture phase
element.addEventListener("click", handler, { capture: true });

// Passive (for better scroll performance)
element.addEventListener("scroll", handler, { passive: true });

// Combine options
button.addEventListener("click", handler, {
    once: true,
    capture: true
});
```

---

## Practical Examples

### Example 1: Toggle Show/Hide

```html
<button id="toggle-btn">Toggle Content</button>
<div id="content">
    <p>This content can be toggled</p>
</div>
```

```javascript
let toggleBtn = document.querySelector("#toggle-btn");
let content = document.querySelector("#content");

toggleBtn.addEventListener("click", () => {
    content.classList.toggle("hidden");
});
```

```css
.hidden {
    display: none;
}
```

### Example 2: Form Validation

```html
<form id="form">
    <input type="text" id="name" placeholder="Name" required>
    <input type="email" id="email" placeholder="Email" required>
    <button>Submit</button>
    <div id="error" style="color: red;"></div>
</form>
```

```javascript
let form = document.querySelector("#form");
let nameInput = document.querySelector("#name");
let emailInput = document.querySelector("#email");
let errorDiv = document.querySelector("#error");

form.addEventListener("submit", (e) => {
    e.preventDefault();

    // Clear previous errors
    errorDiv.textContent = "";

    // Validate name
    if (nameInput.value.trim() === "") {
        errorDiv.textContent = "Name is required";
        return;
    }

    // Validate email
    if (!emailInput.value.includes("@")) {
        errorDiv.textContent = "Invalid email";
        return;
    }

    // Success
    console.log("Form is valid!");
    console.log("Name:", nameInput.value);
    console.log("Email:", emailInput.value);
});
```

### Example 3: Live Search

```html
<input type="text" id="search" placeholder="Search...">
<ul id="results">
    <li>Apple</li>
    <li>Banana</li>
    <li>Cherry</li>
    <li>Date</li>
    <li>Elderberry</li>
</ul>
```

```javascript
let search = document.querySelector("#search");
let items = document.querySelectorAll("#results li");

search.addEventListener("input", (e) => {
    let searchTerm = e.target.value.toLowerCase();

    items.forEach((item) => {
        let text = item.textContent.toLowerCase();

        if (text.includes(searchTerm)) {
            item.style.display = "block";
        } else {
            item.style.display = "none";
        }
    });
});
```

### Example 4: Character Counter

```html
<textarea id="message" maxlength="100"></textarea>
<div id="counter">0 / 100</div>
```

```javascript
let textarea = document.querySelector("#message");
let counter = document.querySelector("#counter");

textarea.addEventListener("input", () => {
    let length = textarea.value.length;
    let max = textarea.maxLength;

    counter.textContent = `${length} / ${max}`;

    // Change color when near limit
    if (length > max * 0.9) {
        counter.style.color = "red";
    } else {
        counter.style.color = "black";
    }
});
```

### Example 5: Keyboard Shortcuts

```javascript
document.addEventListener("keydown", (e) => {
    // Ctrl+S to save
    if (e.ctrlKey && e.key === "s") {
        e.preventDefault();
        console.log("Saving...");
    }

    // Escape to close modal
    if (e.key === "Escape") {
        closeModal();
    }

    // Ctrl+K to focus search
    if (e.ctrlKey && e.key === "k") {
        e.preventDefault();
        document.querySelector("#search").focus();
    }
});
```

### Example 6: Click Outside to Close

```html
<button id="open-menu">Open Menu</button>
<div id="menu" class="hidden">
    <p>Menu content...</p>
</div>
```

```javascript
let openBtn = document.querySelector("#open-menu");
let menu = document.querySelector("#menu");

openBtn.addEventListener("click", () => {
    menu.classList.remove("hidden");
});

document.addEventListener("click", (e) => {
    // If click is outside menu and button
    if (!menu.contains(e.target) && e.target !== openBtn) {
        menu.classList.add("hidden");
    }
});
```

### Example 7: Drag and Drop Basics

```html
<div id="draggable" draggable="true">Drag me</div>
<div id="dropzone">Drop here</div>
```

```javascript
let draggable = document.querySelector("#draggable");
let dropzone = document.querySelector("#dropzone");

draggable.addEventListener("dragstart", (e) => {
    e.dataTransfer.setData("text", e.target.id);
});

dropzone.addEventListener("dragover", (e) => {
    e.preventDefault(); // Allow drop
});

dropzone.addEventListener("drop", (e) => {
    e.preventDefault();
    let id = e.dataTransfer.getData("text");
    let element = document.getElementById(id);
    dropzone.appendChild(element);
});
```

---

## Debouncing and Throttling

### The Problem: Too Many Events

```javascript
// Bad: Fires hundreds of times per second
window.addEventListener("resize", () => {
    console.log("Resized!"); // Too many calls!
});
```

### Debouncing: Wait for Pause

Execute function only after user **stops** triggering the event for X milliseconds:

```javascript
function debounce(func, delay) {
    let timeout;
    return function(...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this, args), delay);
    };
}

// Use it
window.addEventListener("resize", debounce(() => {
    console.log("Resized!"); // Only fires after user stops resizing
}, 500));
```

**Use cases:**
- Search as you type (wait for user to stop typing)
- Window resize
- Scroll events

### Throttling: Limit Frequency

Execute function at most once per X milliseconds:

```javascript
function throttle(func, limit) {
    let inThrottle;
    return function(...args) {
        if (!inThrottle) {
            func.apply(this, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

// Use it
window.addEventListener("scroll", throttle(() => {
    console.log("Scrolled!"); // Fires at most once per 500ms
}, 500));
```

**Use cases:**
- Scroll events
- Mouse move tracking
- Game loops

---

## Common Mistakes

### Mistake 1: Not Preventing Default

```javascript
// Bad: Form refreshes page
form.addEventListener("submit", () => {
    console.log("Submitted!");
    // Page refreshes!
});

// Good
form.addEventListener("submit", (e) => {
    e.preventDefault();
    console.log("Submitted!");
});
```

### Mistake 2: Can't Remove Anonymous Function

```javascript
// Bad: Can't remove
button.addEventListener("click", () => {
    console.log("Clicked!");
});

// Good: Can remove
function handleClick() {
    console.log("Clicked!");
}
button.addEventListener("click", handleClick);
button.removeEventListener("click", handleClick);
```

### Mistake 3: Adding Listener in Loop

```javascript
// Bad: Many listeners
items.forEach((item) => {
    item.addEventListener("click", handleClick);
});

// Good: One listener (event delegation)
list.addEventListener("click", (e) => {
    if (e.target.matches(".item")) {
        handleClick(e);
    }
});
```

---

## Practice Exercises

### Exercise 1: Click Counter

Create a button that displays how many times it's been clicked.

### Exercise 2: Color Changer

Create buttons for different colors. When clicked, change the page background color.

### Exercise 3: Todo List with Delete

Create a todo list where each item has a delete button. Use event delegation.

### Exercise 4: Form Validation

Create a form with name, email, and password. Validate:
- Name: not empty
- Email: contains @
- Password: at least 8 characters

Show errors in real-time as user types.

### Exercise 5: Keyboard Navigation

Create a list. Allow user to:
- Press arrow up/down to highlight items
- Press Enter to select item
- Press Escape to deselect

### Exercise 6: Modal

Create a "Open Modal" button. When clicked:
- Show a modal overlay
- Close modal when clicking X button
- Close modal when clicking outside
- Close modal when pressing Escape

---

## Quick Reference

### Add Listener
```javascript
element.addEventListener("event", handler);
element.addEventListener("event", handler, { once: true });
```

### Remove Listener
```javascript
element.removeEventListener("event", handler);
```

### Event Object
```javascript
(e) => {
    e.type          // Event type
    e.target        // Element that triggered
    e.preventDefault()   // Stop default behavior
    e.stopPropagation()  // Stop bubbling
}
```

### Common Events
```javascript
"click", "dblclick"           // Mouse
"mouseenter", "mouseleave"    // Hover
"keydown", "keyup"            // Keyboard
"input", "change"             // Form input
"submit"                      // Form
"load", "DOMContentLoaded"    // Page
"scroll", "resize"            // Window
```

---

## Key Takeaways

1. **Use addEventListener()** - modern, flexible, can add multiple listeners
2. **Always check event object** - has useful properties
3. **Prevent default** when handling forms and links
4. **Use event delegation** for dynamic elements
5. **Name functions** if you need to remove listeners
6. **Debounce/throttle** for expensive operations
7. **Stop propagation** when needed to prevent bubbling

---

## Next Lesson

In Lesson 07, we'll learn about:
- ES6 features (let/const, arrow functions, template literals)
- Destructuring and spread operator
- Default parameters and rest parameters
- Array and object methods (map, filter, reduce)

Modern JavaScript features that make your code cleaner and more powerful!
