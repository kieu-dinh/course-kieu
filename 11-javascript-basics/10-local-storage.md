# Lesson 10 - Local Storage and Session Storage

**Duration**: 2-3 hours
**Prerequisites**: Lesson 09 - Fetch API

---

## What is Web Storage?

**Web Storage** allows you to store data in the browser, even after the page is closed.

**Two types:**
1. **localStorage** - Persists forever (until manually cleared)
2. **sessionStorage** - Persists only for the browser session (until tab closes)

### Compare to PHP Sessions and Cookies

**PHP (Server-side):**
```php
<?php
// Session (stored on server)
$_SESSION['username'] = "Kieu";

// Cookie (stored in browser)
setcookie("theme", "dark", time() + 86400);
?>
```

**JavaScript (Client-side):**
```javascript
// localStorage (stored in browser, persistent)
localStorage.setItem("username", "Kieu");

// sessionStorage (stored in browser, temporary)
sessionStorage.setItem("theme", "dark");
```

**Key Differences:**
- **PHP sessions**: Server-side, secure, can store any data type
- **Cookies**: Sent with every request, limited size (4KB)
- **Web Storage**: Client-side only, larger size (5-10MB), never sent to server

---

## localStorage Basics

### Storing Data

```javascript
// Store string
localStorage.setItem("username", "Kieu");
localStorage.setItem("email", "kieu@example.com");

// Or use property syntax (less common)
localStorage.username = "Kieu";
localStorage["email"] = "kieu@example.com";
```

### Retrieving Data

```javascript
// Get value
const username = localStorage.getItem("username");
console.log(username); // "Kieu"

// Or property syntax
const email = localStorage.email;
console.log(email); // "kieu@example.com"

// Returns null if key doesn't exist
const age = localStorage.getItem("age");
console.log(age); // null
```

### Removing Data

```javascript
// Remove specific item
localStorage.removeItem("username");

// Clear all items
localStorage.clear();
```

### Checking for Data

```javascript
// Check if key exists
if (localStorage.getItem("username")) {
    console.log("User is logged in");
}

// Or check for null
const theme = localStorage.getItem("theme");
if (theme === null) {
    console.log("No theme preference saved");
}
```

---

## Storing Complex Data (Objects and Arrays)

**localStorage only stores strings!** You must convert objects/arrays to JSON.

### Storing Objects

```javascript
const user = {
    name: "Kieu",
    age: 25,
    email: "kieu@example.com"
};

// Convert to JSON string
localStorage.setItem("user", JSON.stringify(user));

// Retrieve and parse
const storedUser = JSON.parse(localStorage.getItem("user"));
console.log(storedUser.name); // "Kieu"
```

### Storing Arrays

```javascript
const todos = [
    { id: 1, title: "Learn JavaScript", completed: true },
    { id: 2, title: "Build project", completed: false }
];

// Store
localStorage.setItem("todos", JSON.stringify(todos));

// Retrieve
const storedTodos = JSON.parse(localStorage.getItem("todos"));
console.log(storedTodos[0].title); // "Learn JavaScript"
```

### Helper Functions

```javascript
// Save data (handles JSON conversion)
function saveData(key, data) {
    localStorage.setItem(key, JSON.stringify(data));
}

// Load data (handles JSON parsing)
function loadData(key, defaultValue = null) {
    const data = localStorage.getItem(key);
    return data ? JSON.parse(data) : defaultValue;
}

// Usage
saveData("user", { name: "Kieu", age: 25 });
const user = loadData("user");
console.log(user.name); // "Kieu"

// With default value
const settings = loadData("settings", { theme: "light" });
```

---

## sessionStorage Basics

**Exactly the same API as localStorage**, but data is cleared when tab/browser closes.

```javascript
// Store data (temporary)
sessionStorage.setItem("tempData", "value");

// Retrieve data
const data = sessionStorage.getItem("tempData");

// Remove data
sessionStorage.removeItem("tempData");

// Clear all
sessionStorage.clear();
```

**Use cases for sessionStorage:**
- Shopping cart (cleared when user leaves)
- Multi-step form data (cleared after submission)
- Temporary filters/sorting preferences

**Use cases for localStorage:**
- User preferences (theme, language)
- Saved login state
- Cached API responses
- Todo lists, notes

---

## Practical Examples

### Example 1: Dark Mode Toggle

```html
<button id="theme-toggle">Toggle Dark Mode</button>
```

```javascript
const toggleBtn = document.querySelector("#theme-toggle");
const body = document.body;

// Load saved theme
const savedTheme = localStorage.getItem("theme");
if (savedTheme === "dark") {
    body.classList.add("dark-mode");
}

// Toggle theme
toggleBtn.addEventListener("click", () => {
    body.classList.toggle("dark-mode");

    // Save preference
    if (body.classList.contains("dark-mode")) {
        localStorage.setItem("theme", "dark");
    } else {
        localStorage.setItem("theme", "light");
    }
});
```

```css
body {
    background: white;
    color: black;
}

body.dark-mode {
    background: #1a1a1a;
    color: white;
}
```

### Example 2: Persistent Todo List

```html
<div id="app">
    <form id="todo-form">
        <input type="text" id="todo-input" placeholder="New todo" required>
        <button type="submit">Add</button>
    </form>
    <ul id="todo-list"></ul>
</div>
```

```javascript
const form = document.querySelector("#todo-form");
const input = document.querySelector("#todo-input");
const list = document.querySelector("#todo-list");

let todos = [];

// Load todos from localStorage
function loadTodos() {
    const stored = localStorage.getItem("todos");
    todos = stored ? JSON.parse(stored) : [];
    renderTodos();
}

// Save todos to localStorage
function saveTodos() {
    localStorage.setItem("todos", JSON.stringify(todos));
}

// Render todos to DOM
function renderTodos() {
    list.innerHTML = "";

    todos.forEach((todo, index) => {
        const li = document.createElement("li");
        li.innerHTML = `
            <input type="checkbox" ${todo.completed ? "checked" : ""} data-index="${index}">
            <span style="${todo.completed ? 'text-decoration: line-through;' : ''}">${todo.text}</span>
            <button class="delete-btn" data-index="${index}">Delete</button>
        `;
        list.appendChild(li);
    });
}

// Add todo
form.addEventListener("submit", (e) => {
    e.preventDefault();

    const text = input.value.trim();
    if (text === "") return;

    todos.push({
        text: text,
        completed: false,
        createdAt: new Date().toISOString()
    });

    saveTodos();
    renderTodos();

    input.value = "";
});

// Toggle or delete todo (event delegation)
list.addEventListener("click", (e) => {
    const index = e.target.dataset.index;

    if (e.target.type === "checkbox") {
        // Toggle completed
        todos[index].completed = e.target.checked;
        saveTodos();
        renderTodos();
    }

    if (e.target.classList.contains("delete-btn")) {
        // Delete todo
        todos.splice(index, 1);
        saveTodos();
        renderTodos();
    }
});

// Load on page load
loadTodos();
```

### Example 3: Form Data Persistence

```html
<form id="contact-form">
    <input type="text" id="name" placeholder="Name">
    <input type="email" id="email" placeholder="Email">
    <textarea id="message" placeholder="Message"></textarea>
    <button type="submit">Submit</button>
    <button type="button" id="clear">Clear</button>
</form>
```

```javascript
const form = document.querySelector("#contact-form");
const nameInput = document.querySelector("#name");
const emailInput = document.querySelector("#email");
const messageInput = document.querySelector("#message");
const clearBtn = document.querySelector("#clear");

// Load saved data
function loadFormData() {
    nameInput.value = localStorage.getItem("formName") || "";
    emailInput.value = localStorage.getItem("formEmail") || "";
    messageInput.value = localStorage.getItem("formMessage") || "";
}

// Save data on input
function saveFormData() {
    localStorage.setItem("formName", nameInput.value);
    localStorage.setItem("formEmail", emailInput.value);
    localStorage.setItem("formMessage", messageInput.value);
}

// Auto-save on input (debounced)
let saveTimeout;
[nameInput, emailInput, messageInput].forEach((input) => {
    input.addEventListener("input", () => {
        clearTimeout(saveTimeout);
        saveTimeout = setTimeout(saveFormData, 500);
    });
});

// Submit form
form.addEventListener("submit", (e) => {
    e.preventDefault();

    console.log("Form submitted:", {
        name: nameInput.value,
        email: emailInput.value,
        message: messageInput.value
    });

    // Clear saved data after submission
    localStorage.removeItem("formName");
    localStorage.removeItem("formEmail");
    localStorage.removeItem("formMessage");

    form.reset();
});

// Clear button
clearBtn.addEventListener("click", () => {
    localStorage.removeItem("formName");
    localStorage.removeItem("formEmail");
    localStorage.removeItem("formMessage");
    form.reset();
});

// Load on page load
loadFormData();
```

### Example 4: Shopping Cart

```javascript
class ShoppingCart {
    constructor() {
        this.items = this.loadCart();
    }

    loadCart() {
        const stored = localStorage.getItem("cart");
        return stored ? JSON.parse(stored) : [];
    }

    saveCart() {
        localStorage.setItem("cart", JSON.stringify(this.items));
    }

    addItem(product) {
        // Check if product already in cart
        const existing = this.items.find((item) => item.id === product.id);

        if (existing) {
            existing.quantity++;
        } else {
            this.items.push({ ...product, quantity: 1 });
        }

        this.saveCart();
    }

    removeItem(productId) {
        this.items = this.items.filter((item) => item.id !== productId);
        this.saveCart();
    }

    updateQuantity(productId, quantity) {
        const item = this.items.find((item) => item.id === productId);

        if (item) {
            item.quantity = quantity;

            if (item.quantity <= 0) {
                this.removeItem(productId);
            } else {
                this.saveCart();
            }
        }
    }

    getTotal() {
        return this.items.reduce((total, item) => {
            return total + (item.price * item.quantity);
        }, 0);
    }

    clear() {
        this.items = [];
        this.saveCart();
    }
}

// Usage
const cart = new ShoppingCart();

// Add product
cart.addItem({
    id: 1,
    name: "Laptop",
    price: 999
});

// Get cart items
console.log(cart.items);

// Get total
console.log(cart.getTotal()); // 999

// Update quantity
cart.updateQuantity(1, 2);
console.log(cart.getTotal()); // 1998

// Remove item
cart.removeItem(1);
```

### Example 5: User Preferences

```javascript
class UserPreferences {
    constructor() {
        this.defaults = {
            theme: "light",
            language: "en",
            notifications: true,
            fontSize: 16
        };

        this.prefs = this.load();
    }

    load() {
        const stored = localStorage.getItem("userPreferences");
        return stored ? { ...this.defaults, ...JSON.parse(stored) } : { ...this.defaults };
    }

    save() {
        localStorage.setItem("userPreferences", JSON.stringify(this.prefs));
    }

    get(key) {
        return this.prefs[key];
    }

    set(key, value) {
        this.prefs[key] = value;
        this.save();
    }

    reset() {
        this.prefs = { ...this.defaults };
        this.save();
    }

    apply() {
        // Apply theme
        document.body.className = this.prefs.theme === "dark" ? "dark-mode" : "";

        // Apply font size
        document.documentElement.style.fontSize = `${this.prefs.fontSize}px`;

        // Apply language
        document.documentElement.lang = this.prefs.language;
    }
}

// Usage
const prefs = new UserPreferences();

// Apply saved preferences
prefs.apply();

// Change preference
prefs.set("theme", "dark");
prefs.apply();

// Get preference
console.log(prefs.get("theme")); // "dark"

// Reset to defaults
prefs.reset();
prefs.apply();
```

---

## Storage Events

Listen for changes to localStorage from other tabs/windows:

```javascript
window.addEventListener("storage", (e) => {
    console.log("Storage changed!");
    console.log("Key:", e.key);
    console.log("Old value:", e.oldValue);
    console.log("New value:", e.newValue);
    console.log("URL:", e.url);

    // Update UI based on change
    if (e.key === "theme") {
        applyTheme(e.newValue);
    }
});
```

**Use case**: Sync preferences across multiple tabs.

**Note**: Storage event only fires in OTHER tabs, not the current one.

---

## Storage Limitations

### Size Limits

- **localStorage**: 5-10 MB (varies by browser)
- **sessionStorage**: 5-10 MB (varies by browser)
- **Cookies**: 4 KB per cookie

### Check Available Space

```javascript
// Rough estimation
function getStorageSize() {
    let total = 0;
    for (let key in localStorage) {
        if (localStorage.hasOwnProperty(key)) {
            total += localStorage[key].length + key.length;
        }
    }
    return total;
}

console.log(`Storage used: ${getStorageSize()} bytes`);
```

### Handle Quota Exceeded

```javascript
function safeSetItem(key, value) {
    try {
        localStorage.setItem(key, value);
        return true;
    } catch (e) {
        if (e.name === "QuotaExceededError") {
            console.error("Storage quota exceeded!");
            // Clear old data or notify user
            return false;
        }
        throw e;
    }
}

// Usage
if (!safeSetItem("largeData", JSON.stringify(bigObject))) {
    alert("Storage is full. Please clear some data.");
}
```

---

## Best Practices

### 1. Namespace Your Keys

Avoid key collisions by prefixing:

```javascript
// Bad: Generic keys
localStorage.setItem("user", JSON.stringify(user));
localStorage.setItem("settings", JSON.stringify(settings));

// Good: Namespaced keys
localStorage.setItem("myApp_user", JSON.stringify(user));
localStorage.setItem("myApp_settings", JSON.stringify(settings));

// Or use a helper
const storage = {
    set(key, value) {
        localStorage.setItem(`myApp_${key}`, JSON.stringify(value));
    },
    get(key) {
        const data = localStorage.getItem(`myApp_${key}`);
        return data ? JSON.parse(data) : null;
    }
};
```

### 2. Handle JSON Errors

```javascript
function safeJSONParse(str, defaultValue = null) {
    try {
        return JSON.parse(str);
    } catch (e) {
        console.error("Invalid JSON:", str);
        return defaultValue;
    }
}

// Usage
const user = safeJSONParse(localStorage.getItem("user"), {});
```

### 3. Don't Store Sensitive Data

**Never store in localStorage:**
- Passwords
- Credit card numbers
- API keys (unless meant to be public)
- Personal identification numbers
- Session tokens (use httpOnly cookies instead)

**Why?** Any JavaScript on the page can access localStorage (XSS attacks).

### 4. Compress Large Data

```javascript
// Simple compression (not recommended for production)
function compress(str) {
    return btoa(unescape(encodeURIComponent(str)));
}

function decompress(str) {
    return decodeURIComponent(escape(atob(str)));
}

// Usage
const data = JSON.stringify(largeObject);
const compressed = compress(data);
localStorage.setItem("data", compressed);

const retrieved = localStorage.getItem("data");
const decompressed = decompress(retrieved);
const object = JSON.parse(decompressed);
```

**For real compression**, use a library like `lz-string`.

### 5. Set Expiration Dates

localStorage doesn't expire automatically, so implement your own:

```javascript
const storage = {
    set(key, value, expirationMs) {
        const item = {
            value: value,
            expiry: Date.now() + expirationMs
        };
        localStorage.setItem(key, JSON.stringify(item));
    },

    get(key) {
        const itemStr = localStorage.getItem(key);

        if (!itemStr) {
            return null;
        }

        const item = JSON.parse(itemStr);

        // Check if expired
        if (Date.now() > item.expiry) {
            localStorage.removeItem(key);
            return null;
        }

        return item.value;
    }
};

// Usage
// Expire in 1 hour (3600000 ms)
storage.set("tempData", { foo: "bar" }, 3600000);

// Later...
const data = storage.get("tempData"); // null if expired
```

---

## Debugging Storage

### View in DevTools

**Chrome/Edge:**
1. Open DevTools (F12)
2. Go to "Application" tab
3. Expand "Local Storage" or "Session Storage"
4. See all key-value pairs

**Firefox:**
1. Open DevTools (F12)
2. Go to "Storage" tab
3. Click "Local Storage" or "Session Storage"

### List All Keys

```javascript
// Get all localStorage keys
for (let i = 0; i < localStorage.length; i++) {
    const key = localStorage.key(i);
    console.log(key, localStorage.getItem(key));
}

// Or as array
const keys = Object.keys(localStorage);
console.log(keys);
```

### Export All Data

```javascript
function exportStorage() {
    const data = {};
    for (let key in localStorage) {
        if (localStorage.hasOwnProperty(key)) {
            data[key] = localStorage[key];
        }
    }
    return JSON.stringify(data, null, 2);
}

// Copy to clipboard
console.log(exportStorage());
```

### Import Data

```javascript
function importStorage(jsonString) {
    const data = JSON.parse(jsonString);
    for (let key in data) {
        localStorage.setItem(key, data[key]);
    }
}
```

---

## Practice Exercises

### Exercise 1: Notes App

Create a simple notes app that:
- Adds notes
- Saves notes to localStorage
- Loads notes on page load
- Deletes notes
- Persists after page reload

### Exercise 2: Settings Panel

Create a settings panel with:
- Theme toggle (light/dark)
- Font size slider (12-24px)
- Language dropdown
- Apply and reset buttons
- Save all settings to localStorage

### Exercise 3: Recently Viewed

Track recently viewed products:
- Store last 10 viewed products
- Show "Recently Viewed" section
- Implement using localStorage

### Exercise 4: Form Wizard

Create a multi-step form that:
- Saves progress to sessionStorage
- Allows going back/forward
- Clears data after submission
- Restores state on page refresh (if not submitted)

### Exercise 5: Offline Todo App

Build a todo app that:
- Works completely offline
- Stores all data in localStorage
- Syncs with server when online (simulate with console.log)
- Shows sync status

---

## Quick Reference

### localStorage
```javascript
// Store
localStorage.setItem("key", "value");

// Retrieve
const value = localStorage.getItem("key");

// Remove
localStorage.removeItem("key");

// Clear all
localStorage.clear();

// Get key by index
const key = localStorage.key(0);

// Number of items
const count = localStorage.length;
```

### Objects/Arrays
```javascript
// Store
localStorage.setItem("data", JSON.stringify(object));

// Retrieve
const data = JSON.parse(localStorage.getItem("data"));
```

### sessionStorage
```javascript
// Same API as localStorage
sessionStorage.setItem("key", "value");
const value = sessionStorage.getItem("key");
```

---

## Key Takeaways

1. **localStorage persists forever**, sessionStorage clears when tab closes
2. **Only stores strings** - use JSON.stringify/parse for objects
3. **Size limit** is 5-10 MB
4. **Synchronous API** - don't store huge amounts of data
5. **Not secure** - don't store sensitive data
6. **Available to all scripts** on the same origin
7. **Namespace your keys** to avoid collisions
8. **Handle errors** when parsing JSON

---

## Congratulations!

You've completed the JavaScript Basics module! You now know:
- JavaScript fundamentals
- DOM manipulation
- Event handling
- Modern ES6+ features
- Async programming
- Fetch API
- Web Storage

**Next**: Module 12 - Alpine.js (Reactive JavaScript Framework)

Keep practicing and building projects!
