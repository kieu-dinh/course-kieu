# Lesson 01 - JavaScript Introduction and Setup

**Duration**: 1-2 hours
**Prerequisites**: Understanding of PHP, HTML, CSS

---

## What is JavaScript?

JavaScript is a **client-side programming language** that runs in the web browser. While PHP runs on the server and generates HTML, JavaScript runs **after** the page loads and makes it interactive.

### PHP vs JavaScript: A Quick Comparison

| Aspect | PHP | JavaScript |
|--------|-----|------------|
| **Runs where?** | Server (your computer/hosting) | Browser (user's computer) |
| **When?** | Before page is sent | After page loads |
| **Purpose** | Generate HTML, process forms, database | Make page interactive, validate forms, animations |
| **File extension** | `.php` | `.js` |
| **Syntax** | `<?php echo "Hello"; ?>` | `console.log("Hello");` |

### Why Learn JavaScript After PHP?

You already understand:
- Variables and data types
- Functions and scope
- Arrays and objects
- Conditions and loops

**JavaScript uses the same concepts!** The syntax is slightly different, but the logic is the same.

**Key Difference**:
- PHP: "What should the page look like when it loads?"
- JavaScript: "What should happen when the user clicks, types, or scrolls?"

---

## Where Does JavaScript Run?

### Every Browser Has a JavaScript Engine

- **Chrome**: V8 engine
- **Firefox**: SpiderMonkey
- **Safari**: JavaScriptCore

When you open a webpage, the browser:
1. Downloads the HTML
2. Downloads the CSS
3. Downloads the JavaScript
4. **Executes the JavaScript** in the user's browser

This means:
- JavaScript can be faster (no server round-trip)
- JavaScript can see what the user does (clicks, typing)
- JavaScript **cannot** access your database directly (security)

---

## How to Include JavaScript in HTML

There are three ways to add JavaScript to your webpage:

### Method 1: Inline (Avoid This)

```html
<button onclick="alert('Hello!')">Click me</button>
```

**Don't do this!** Just like inline CSS, it mixes behavior with structure.

### Method 2: Internal (Good for Learning)

```html
<!DOCTYPE html>
<html>
<head>
    <title>My Page</title>
</head>
<body>
    <h1>Hello World</h1>

    <script>
        // Your JavaScript code here
        console.log("Hello from JavaScript!");
    </script>
</body>
</html>
```

The `<script>` tag can go in `<head>` or `<body>`. Best practice: **put it at the end of `<body>`** so the HTML loads first.

### Method 3: External File (Best Practice)

**index.html:**
```html
<!DOCTYPE html>
<html>
<head>
    <title>My Page</title>
</head>
<body>
    <h1>Hello World</h1>

    <!-- Link to external JavaScript file -->
    <script src="script.js"></script>
</body>
</html>
```

**script.js:**
```javascript
// Your JavaScript code here
console.log("Hello from an external file!");
```

**Why external files are better:**
- Separation of concerns (like CSS)
- Reusable across pages
- Browser caching (faster loading)
- Easier to maintain

---

## The Browser Console: Your New Best Friend

The browser console is like `var_dump()` and `echo` combined. It's where JavaScript output appears and where you can test code.

### Opening the Console

**Chrome/Edge:**
- Mac: `Cmd + Option + J`
- Windows/Linux: `Ctrl + Shift + J`
- Or: Right-click → Inspect → Console tab

**Firefox:**
- Mac: `Cmd + Option + K`
- Windows/Linux: `Ctrl + Shift + K`

**Safari:**
- First enable: Safari → Settings → Advanced → Show Develop menu
- Then: `Cmd + Option + C`

### Using console.log()

In PHP, you use `echo` or `var_dump()`:
```php
<?php
$name = "Kieu";
echo $name; // Output to page
var_dump($name); // Debug output
?>
```

In JavaScript, you use `console.log()`:
```javascript
let name = "Kieu";
console.log(name); // Output to console
console.log("The name is:", name); // Can log multiple things
```

### Console Methods

JavaScript console has many helpful methods:

```javascript
// Regular message
console.log("This is a message");

// Warning (yellow)
console.warn("This is a warning");

// Error (red)
console.error("This is an error");

// Info (blue)
console.info("This is information");

// Clear console
console.clear();

// Log an object/array nicely
let user = { name: "Kieu", age: 25 };
console.table(user); // Shows as a table!
```

---

## Your First JavaScript Program

Let's write a simple program that changes the page content.

**Create `first-script.html`:**

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My First JavaScript</title>
</head>
<body>
    <h1 id="greeting">Hello World</h1>
    <button id="changeBtn">Change Greeting</button>

    <script>
        // Log to console
        console.log("JavaScript is running!");

        // Get the button element
        let button = document.getElementById("changeBtn");

        // Add a click event
        button.addEventListener("click", function() {
            // Get the h1 element
            let heading = document.getElementById("greeting");

            // Change its text
            heading.textContent = "Hello from JavaScript!";

            // Log to console
            console.log("Button was clicked!");
        });
    </script>
</body>
</html>
```

**What's happening here?**

1. `document.getElementById()` - Finds an HTML element by its ID
2. `addEventListener()` - Waits for a click on the button
3. `textContent` - Changes the text inside an element
4. `console.log()` - Outputs to the browser console

**Try it:**
1. Save the file
2. Open it in your browser (double-click or use Herd)
3. Open the console (`Cmd + Option + J`)
4. Click the button
5. See the heading change AND see the console message!

---

## Comparing PHP and JavaScript Syntax

You already know these concepts in PHP. Here's how they translate to JavaScript:

### Comments

```php
// PHP single-line comment
# Also works in PHP
/* Multi-line
   comment */
```

```javascript
// JavaScript single-line comment
/* Multi-line
   comment */
```

**Same!**

### Variables

```php
<?php
$name = "Kieu";        // String
$age = 25;             // Integer
$price = 19.99;        // Float
$isStudent = true;     // Boolean
?>
```

```javascript
let name = "Kieu";        // String
let age = 25;             // Number
let price = 19.99;        // Number
let isStudent = true;     // Boolean
```

**Differences:**
- No `$` in JavaScript
- Use `let` or `const` instead of `$`
- No distinction between int and float (both are "number")

### Strings

```php
<?php
$name = "Kieu";
$greeting = "Hello, $name!";           // Variable interpolation
$greeting2 = "Hello, " . $name . "!";  // Concatenation
?>
```

```javascript
let name = "Kieu";
let greeting = `Hello, ${name}!`;        // Template literal (ES6)
let greeting2 = "Hello, " + name + "!";  // Concatenation
```

**JavaScript has template literals** (backticks ` \` `) which are like PHP double quotes!

### Arrays

```php
<?php
$fruits = ["apple", "banana", "cherry"];
echo $fruits[0]; // "apple"
?>
```

```javascript
let fruits = ["apple", "banana", "cherry"];
console.log(fruits[0]); // "apple"
```

**Almost identical!**

### Associative Arrays / Objects

```php
<?php
$user = [
    "name" => "Kieu",
    "age" => 25
];
echo $user["name"]; // "Kieu"
?>
```

```javascript
let user = {
    name: "Kieu",
    age: 25
};
console.log(user.name);      // "Kieu" (dot notation)
console.log(user["name"]);   // "Kieu" (bracket notation)
```

**JavaScript uses objects** instead of associative arrays.

### Functions

```php
<?php
function greet($name) {
    return "Hello, $name!";
}

echo greet("Kieu");
?>
```

```javascript
function greet(name) {
    return "Hello, " + name + "!";
}

console.log(greet("Kieu"));
```

**Very similar!** JavaScript functions don't need `$` for parameters.

---

## JavaScript in the Real World

### What JavaScript Can Do

✅ **Change HTML content**
```javascript
document.getElementById("title").textContent = "New Title";
```

✅ **React to user actions**
```javascript
button.addEventListener("click", function() {
    alert("Button clicked!");
});
```

✅ **Validate forms before submission**
```javascript
if (email.includes("@")) {
    // Valid email
}
```

✅ **Fetch data without page reload (AJAX)**
```javascript
fetch("https://api.example.com/users")
    .then(response => response.json())
    .then(data => console.log(data));
```

✅ **Create animations and effects**
```javascript
element.style.opacity = "0.5";
```

✅ **Store data in browser (Local Storage)**
```javascript
localStorage.setItem("username", "Kieu");
```

### What JavaScript CANNOT Do

❌ **Access your file system** (security)
❌ **Connect to MySQL directly** (security)
❌ **Run PHP code** (different languages)
❌ **Access other websites' data** (CORS policy)

**That's why we need both PHP and JavaScript!**
- PHP: Database, authentication, file uploads, email
- JavaScript: Interactivity, validation, animations, AJAX

---

## Setting Up Your JavaScript Development Environment

You already have everything you need! But here are some tips:

### 1. Use VS Code

Your VS Code is already perfect for JavaScript. Install these extensions if you haven't:

- **ESLint** - Finds errors in your code
- **JavaScript (ES6) code snippets** - Quick code templates
- **Prettier** - Auto-formats your code

### 2. Test in Multiple Browsers

JavaScript *should* work the same everywhere, but test in:
- Chrome (most popular)
- Firefox (good for debugging)
- Safari (if on Mac)

### 3. Use Browser DevTools

**Elements Tab**: See and edit HTML/CSS live
**Console Tab**: Test JavaScript, see errors
**Network Tab**: See API requests
**Application Tab**: Check Local Storage

### 4. Simple Starter Template

Create this as your JavaScript starter file:

**starter.html:**
```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JavaScript Practice</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-2xl mx-auto bg-white rounded-lg shadow-lg p-6">
        <h1 class="text-2xl font-bold mb-4">JavaScript Practice</h1>

        <!-- Your HTML here -->

    </div>

    <script>
        // Your JavaScript here
        console.log("Ready to code!");
    </script>
</body>
</html>
```

---

## Common Errors and How to Debug

### Error 1: "Uncaught ReferenceError: x is not defined"

**Means**: You're using a variable that doesn't exist.

```javascript
console.log(name); // Error if 'name' not declared
```

**Fix**: Declare the variable first.
```javascript
let name = "Kieu";
console.log(name); // Works!
```

### Error 2: "Uncaught TypeError: Cannot read property of null"

**Means**: You're trying to use an element that doesn't exist.

```javascript
let button = document.getElementById("btn"); // Returns null if no element
button.addEventListener("click", ...); // Error!
```

**Fix**: Check if element exists.
```javascript
let button = document.getElementById("btn");
if (button) {
    button.addEventListener("click", ...);
}
```

### Error 3: Script Runs Before HTML Loads

**Problem**: JavaScript tries to access HTML elements before they exist.

**Bad:**
```html
<script>
    let button = document.getElementById("btn"); // btn doesn't exist yet!
</script>
<button id="btn">Click</button>
```

**Fix 1**: Put `<script>` at the end of `<body>`
```html
<button id="btn">Click</button>
<script>
    let button = document.getElementById("btn"); // Now it exists!
</script>
```

**Fix 2**: Wait for page to load
```html
<script>
    document.addEventListener("DOMContentLoaded", function() {
        let button = document.getElementById("btn"); // Waits for HTML
    });
</script>
```

---

## Practice Exercises

### Exercise 1: Console Playground

Create an HTML file and practice these console commands:

1. Log your name
2. Log a warning about something
3. Create an object with your info (name, age, city)
4. Use `console.table()` to display it nicely
5. Clear the console

### Exercise 2: Change the Page

Create a page with:
- A heading with id="title"
- A paragraph with id="message"
- A button

When the button is clicked:
- Change the heading text
- Change the paragraph text
- Log something to console

### Exercise 3: PHP to JavaScript Translation

Translate this PHP code to JavaScript:

```php
<?php
$username = "Kieu";
$age = 25;
$greeting = "Hello, $username! You are $age years old.";
echo $greeting;
?>
```

Write the JavaScript equivalent using:
- Variables
- Template literals
- console.log()

---

## Quick Reference: JavaScript Basics

### Essential Methods

```javascript
// Console output
console.log("message");
console.error("error");
console.warn("warning");

// Get HTML elements
document.getElementById("id")
document.querySelector(".class")
document.querySelectorAll("div")

// Change content
element.textContent = "new text";
element.innerHTML = "<b>HTML</b>";

// Change styles
element.style.color = "red";
element.classList.add("active");

// Events
element.addEventListener("click", function() {
    // Do something
});
```

### Common Shortcuts

```javascript
// Arrow function (we'll learn this)
const greet = (name) => `Hello, ${name}!`;

// Ternary operator
let status = age >= 18 ? "adult" : "minor";

// Destructuring
const {name, age} = user;
```

---

## Next Steps

In the next lesson, we'll dive into:
- Variables (`let`, `const`, `var`)
- Data types in detail
- Operators (arithmetic, comparison, logical)
- Type conversion

But before that, **practice in the console!**

Open any website, open the console, and try:
```javascript
console.log("Hello from the console!");
document.body.style.backgroundColor = "lightblue";
alert("I can do this!");
```

See? You're already programming with JavaScript!

---

## Key Takeaways

1. **JavaScript runs in the browser**, PHP runs on the server
2. **Console is your friend** - use `console.log()` everywhere
3. **Syntax is similar to PHP** - you already know 80% of the logic
4. **Include scripts at the end** of `<body>` or wait for DOMContentLoaded
5. **Use external files** for real projects
6. **DevTools are powerful** - learn to use them

Ready to learn variables and data types? Let's go!
