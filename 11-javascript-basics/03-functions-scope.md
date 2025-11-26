# Lesson 03 - Functions and Scope

**Duration**: 2-3 hours
**Prerequisites**: Lesson 02 - Variables, Data Types, Operators

---

## Functions in JavaScript

You already know functions from PHP! JavaScript functions work similarly but have more flexibility.

### PHP Functions (Review)

```php
<?php
function greet($name) {
    return "Hello, $name!";
}

echo greet("Kieu"); // "Hello, Kieu!"
?>
```

### JavaScript Functions

There are **three ways** to create functions in JavaScript:

1. **Function Declaration** (like PHP)
2. **Function Expression** (store function in variable)
3. **Arrow Function** (modern, short syntax)

---

## 1. Function Declaration

The traditional way, most similar to PHP:

```javascript
function greet(name) {
    return "Hello, " + name + "!";
}

console.log(greet("Kieu")); // "Hello, Kieu!"
```

**Key Differences from PHP:**
- No `function` keyword for parameters (no `$name`)
- No `$` for variables inside function
- Otherwise, syntax is almost identical!

### Multiple Parameters

```javascript
function add(a, b) {
    return a + b;
}

console.log(add(5, 3)); // 8
```

**Compare to PHP:**
```php
<?php
function add($a, $b) {
    return $a + $b;
}
?>
```

### Default Parameters

```javascript
function greet(name = "Guest") {
    return `Hello, ${name}!`;
}

console.log(greet());        // "Hello, Guest!"
console.log(greet("Kieu"));  // "Hello, Kieu!"
```

**Compare to PHP:**
```php
<?php
function greet($name = "Guest") {
    return "Hello, $name!";
}
?>
```

**Same concept!**

### No Return Value

If a function doesn't return anything, it returns `undefined`:

```javascript
function sayHello(name) {
    console.log(`Hello, ${name}!`);
    // No return statement
}

let result = sayHello("Kieu"); // Logs: "Hello, Kieu!"
console.log(result);            // undefined
```

---

## 2. Function Expression

Store a function in a variable:

```javascript
const greet = function(name) {
    return `Hello, ${name}!`;
};

console.log(greet("Kieu")); // "Hello, Kieu!"
```

**Why use function expressions?**
- Can pass functions as arguments
- Can return functions from functions
- More flexibility (we'll see why later)

**Note the semicolon** at the end (it's a statement).

### Named Function Expression

You can name the function for debugging:

```javascript
const greet = function greeting(name) {
    return `Hello, ${name}!`;
};

// Call it using the variable name
console.log(greet("Kieu"));
```

The name `greeting` is only available inside the function (for recursion or debugging).

---

## 3. Arrow Functions (ES6)

Modern, concise syntax:

```javascript
// Traditional function
function add(a, b) {
    return a + b;
}

// Arrow function
const add = (a, b) => {
    return a + b;
};

// Short form (implicit return)
const add = (a, b) => a + b;
```

**Arrow Function Syntax:**

```javascript
// Multiple parameters
const add = (a, b) => a + b;

// Single parameter (parentheses optional)
const square = x => x * x;
const square = (x) => x * x; // Same thing

// No parameters
const sayHello = () => "Hello!";

// Multiple statements (need braces and return)
const greet = (name) => {
    const message = `Hello, ${name}!`;
    return message;
};

// Return object (wrap in parentheses)
const makePerson = (name, age) => ({ name: name, age: age });
// Or shorter:
const makePerson = (name, age) => ({ name, age });
```

**When to Use Arrow Functions:**
- Short, simple functions
- Callbacks (we'll learn soon)
- When you need lexical `this` (advanced)

**When NOT to Use Arrow Functions:**
- Methods in objects (we'll see why)
- When you need `arguments` object

**My Recommendation:**
- Start with function declarations
- Use arrow functions for callbacks
- You'll develop a feel for when to use each

---

## Comparing the Three Ways

All three do the same thing:

```javascript
// 1. Function Declaration
function greet1(name) {
    return `Hello, ${name}!`;
}

// 2. Function Expression
const greet2 = function(name) {
    return `Hello, ${name}!`;
};

// 3. Arrow Function
const greet3 = (name) => `Hello, ${name}!`;

// All work the same way
console.log(greet1("Kieu")); // "Hello, Kieu!"
console.log(greet2("Kieu")); // "Hello, Kieu!"
console.log(greet3("Kieu")); // "Hello, Kieu!"
```

**Key Difference: Hoisting**

Function declarations are "hoisted" (moved to top):

```javascript
// This works!
console.log(greet("Kieu")); // "Hello, Kieu!"

function greet(name) {
    return `Hello, ${name}!`;
}
```

But function expressions and arrow functions are NOT:

```javascript
// This doesn't work!
console.log(greet("Kieu")); // Error: Cannot access 'greet' before initialization

const greet = (name) => `Hello, ${name}!`;
```

**Best Practice:** Define functions before using them (regardless of type).

---

## Scope in JavaScript

**Scope** = where variables are accessible.

PHP has two main scopes:
- Global scope (outside functions)
- Function scope (inside functions)

JavaScript has three:
- Global scope
- Function scope
- Block scope (new with `let` and `const`)

### Global Scope

Variables declared outside functions:

```javascript
let userName = "Kieu"; // Global variable

function greet() {
    console.log(userName); // Can access global variable
}

greet(); // "Kieu"
console.log(userName); // "Kieu"
```

**Same as PHP:**
```php
<?php
$userName = "Kieu"; // Global

function greet() {
    global $userName; // Need 'global' keyword in PHP!
    echo $userName;
}
?>
```

**JavaScript is easier**: No need for `global` keyword!

### Function Scope

Variables declared inside functions:

```javascript
function greet() {
    let message = "Hello!"; // Local variable
    console.log(message);   // Works
}

greet();
console.log(message); // Error: message is not defined
```

**Same as PHP:**
```php
<?php
function greet() {
    $message = "Hello!"; // Local variable
    echo $message; // Works
}

greet();
echo $message; // Error: undefined variable
?>
```

### Block Scope (NEW in JavaScript)

Variables declared with `let` and `const` are block-scoped:

```javascript
if (true) {
    let message = "Hello!"; // Block-scoped
    console.log(message);   // Works
}

console.log(message); // Error: message is not defined
```

**This is NEW compared to PHP!**

```php
<?php
if (true) {
    $message = "Hello!";
    echo $message; // Works
}

echo $message; // Also works in PHP! (not block-scoped)
?>
```

**Block Scope Examples:**

```javascript
// In loops
for (let i = 0; i < 3; i++) {
    console.log(i); // 0, 1, 2
}
console.log(i); // Error: i is not defined

// In if statements
if (true) {
    const temp = "temporary";
}
console.log(temp); // Error: temp is not defined

// In code blocks
{
    let secret = "password";
}
console.log(secret); // Error: secret is not defined
```

**Why is this useful?**
- Variables are only accessible where needed
- Prevents bugs from variable name collisions
- Cleaner code

### Variable Shadowing

Inner scope can "shadow" outer scope:

```javascript
let name = "Global";

function greet() {
    let name = "Local"; // Shadows global 'name'
    console.log(name);  // "Local"
}

greet();
console.log(name); // "Global"
```

**Same as PHP!**

### Lexical Scope (Static Scope)

Inner functions can access outer function variables:

```javascript
function outer() {
    let outerVar = "I'm outer";

    function inner() {
        console.log(outerVar); // Can access outer variable
    }

    inner();
}

outer(); // "I'm outer"
```

**This creates closures** (advanced topic we'll cover later).

---

## Parameters and Arguments

**Parameters** = Variables in function definition
**Arguments** = Values passed when calling function

```javascript
function greet(name) {  // 'name' is a parameter
    return `Hello, ${name}!`;
}

greet("Kieu"); // "Kieu" is an argument
```

### Rest Parameters

Collect remaining arguments into an array:

```javascript
function sum(...numbers) {
    let total = 0;
    for (let num of numbers) {
        total += num;
    }
    return total;
}

console.log(sum(1, 2, 3));       // 6
console.log(sum(1, 2, 3, 4, 5)); // 15
```

**Compare to PHP:**
```php
<?php
function sum(...$numbers) {
    return array_sum($numbers);
}
?>
```

**Same concept!**

### Destructuring Parameters

Extract values from objects/arrays:

```javascript
// Object destructuring
function greet({ name, age }) {
    return `${name} is ${age} years old`;
}

let user = { name: "Kieu", age: 25 };
console.log(greet(user)); // "Kieu is 25 years old"

// Array destructuring
function getFirst([first, second]) {
    return first;
}

console.log(getFirst([10, 20, 30])); // 10
```

---

## Return Values

Functions can return any value:

```javascript
// Return string
function greet(name) {
    return `Hello, ${name}!`;
}

// Return number
function add(a, b) {
    return a + b;
}

// Return boolean
function isAdult(age) {
    return age >= 18;
}

// Return array
function getColors() {
    return ["red", "green", "blue"];
}

// Return object
function makePerson(name, age) {
    return { name: name, age: age };
    // Or shorter: return { name, age };
}

// Return function
function makeGreeter(greeting) {
    return function(name) {
        return `${greeting}, ${name}!`;
    };
}

let greet = makeGreeter("Hello");
console.log(greet("Kieu")); // "Hello, Kieu!"
```

### Early Return

Return early to exit function:

```javascript
function divide(a, b) {
    if (b === 0) {
        return null; // Exit early
    }
    return a / b;
}

console.log(divide(10, 2)); // 5
console.log(divide(10, 0)); // null
```

**Same as PHP guard clauses!**

---

## Callback Functions

A **callback** is a function passed as an argument to another function.

### Simple Example

```javascript
function processUser(name, callback) {
    console.log(`Processing ${name}...`);
    callback(name); // Call the callback function
}

function greet(name) {
    console.log(`Hello, ${name}!`);
}

processUser("Kieu", greet);
// Logs:
// "Processing Kieu..."
// "Hello, Kieu!"
```

### Inline Callback

Usually, callbacks are defined inline:

```javascript
processUser("Kieu", function(name) {
    console.log(`Hello, ${name}!`);
});

// Or with arrow function (cleaner)
processUser("Kieu", (name) => {
    console.log(`Hello, ${name}!`);
});
```

### Real-World Example: Event Listeners

```javascript
let button = document.getElementById("myButton");

// The function is a callback - called when button is clicked
button.addEventListener("click", function() {
    console.log("Button was clicked!");
});

// Or with arrow function
button.addEventListener("click", () => {
    console.log("Button was clicked!");
});
```

We'll use callbacks extensively with:
- Event listeners (clicks, inputs)
- Array methods (map, filter, forEach)
- Timers (setTimeout, setInterval)
- AJAX requests (fetch)

---

## Higher-Order Functions

A **higher-order function** either:
1. Takes a function as argument (uses callbacks)
2. Returns a function

### Example 1: Takes Function as Argument

```javascript
function repeat(n, action) {
    for (let i = 0; i < n; i++) {
        action(i);
    }
}

// Pass a callback
repeat(3, (i) => {
    console.log(`Iteration ${i}`);
});
// Logs:
// "Iteration 0"
// "Iteration 1"
// "Iteration 2"
```

### Example 2: Returns a Function

```javascript
function multiplier(factor) {
    return (number) => number * factor;
}

let double = multiplier(2);
let triple = multiplier(3);

console.log(double(5)); // 10
console.log(triple(5)); // 15
```

**Why is this useful?**
- Create specialized functions
- Functional programming patterns
- More flexible code

---

## Practical Examples

### Example 1: Temperature Converter

```javascript
function celsiusToFahrenheit(celsius) {
    return (celsius * 9/5) + 32;
}

function fahrenheitToCelsius(fahrenheit) {
    return (fahrenheit - 32) * 5/9;
}

console.log(celsiusToFahrenheit(25));  // 77
console.log(fahrenheitToCelsius(77));  // 25
```

### Example 2: Form Validator

```javascript
function validateEmail(email) {
    return email.includes("@") && email.includes(".");
}

function validatePassword(password) {
    return password.length >= 8;
}

function validateForm(email, password) {
    let isEmailValid = validateEmail(email);
    let isPasswordValid = validatePassword(password);

    return isEmailValid && isPasswordValid;
}

console.log(validateForm("user@example.com", "secret123")); // true
console.log(validateForm("invalid", "short"));               // false
```

### Example 3: Calculate Discount

```javascript
function calculateDiscount(price, discountPercent) {
    let discount = price * (discountPercent / 100);
    return price - discount;
}

function calculateTotal(price, quantity, discountPercent = 0) {
    let subtotal = price * quantity;
    let total = calculateDiscount(subtotal, discountPercent);
    return total;
}

console.log(calculateTotal(29.99, 3));     // 89.97
console.log(calculateTotal(29.99, 3, 10)); // 80.973
```

### Example 4: Array Processing

```javascript
function sumArray(numbers) {
    let total = 0;
    for (let num of numbers) {
        total += num;
    }
    return total;
}

function averageArray(numbers) {
    let sum = sumArray(numbers);
    return sum / numbers.length;
}

let scores = [85, 90, 78, 92, 88];
console.log(sumArray(scores));     // 433
console.log(averageArray(scores)); // 86.6
```

### Example 5: Text Formatter

```javascript
function capitalize(text) {
    return text.charAt(0).toUpperCase() + text.slice(1).toLowerCase();
}

function formatName(firstName, lastName) {
    return `${capitalize(firstName)} ${capitalize(lastName)}`;
}

console.log(formatName("KIEU", "NGUYEN")); // "Kieu Nguyen"
```

---

## Common Mistakes and How to Fix Them

### Mistake 1: Forgetting to Return

```javascript
// Bad
function add(a, b) {
    a + b; // Forgot 'return'
}

console.log(add(5, 3)); // undefined

// Good
function add(a, b) {
    return a + b;
}
```

### Mistake 2: Using Variables Before Declaration

```javascript
// Bad
greet("Kieu"); // Error!

const greet = (name) => `Hello, ${name}!`;

// Good
const greet = (name) => `Hello, ${name}!`;

greet("Kieu"); // Works
```

### Mistake 3: Accessing Local Variables Globally

```javascript
// Bad
function greet() {
    let message = "Hello!";
}

console.log(message); // Error: message is not defined

// Good
function greet() {
    let message = "Hello!";
    return message; // Return it
}

let message = greet();
console.log(message); // "Hello!"
```

### Mistake 4: Arrow Function Syntax Errors

```javascript
// Bad
const greet = name => return `Hello, ${name}!`; // Syntax error

// Good (implicit return, no braces)
const greet = name => `Hello, ${name}!`;

// Or (explicit return, with braces)
const greet = name => {
    return `Hello, ${name}!`;
};
```

### Mistake 5: Modifying Parameters

```javascript
// Bad (modifies original)
function addToArray(arr, item) {
    arr.push(item); // Modifies original array!
}

let fruits = ["apple"];
addToArray(fruits, "banana");
console.log(fruits); // ["apple", "banana"] - original modified

// Good (return new array)
function addToArray(arr, item) {
    return [...arr, item]; // Creates new array
}

let fruits = ["apple"];
let newFruits = addToArray(fruits, "banana");
console.log(fruits);    // ["apple"] - original unchanged
console.log(newFruits); // ["apple", "banana"]
```

---

## Practice Exercises

### Exercise 1: Basic Functions

Create these functions:
1. `greet(name)` - Returns greeting message
2. `add(a, b)` - Returns sum
3. `isEven(number)` - Returns true if even
4. `getMax(a, b)` - Returns larger number
5. `repeat(text, times)` - Returns text repeated n times

Test each function with console.log().

### Exercise 2: Arrow Function Practice

Convert these to arrow functions:

```javascript
function square(x) {
    return x * x;
}

function isPositive(num) {
    return num > 0;
}

function getFullName(firstName, lastName) {
    return firstName + " " + lastName;
}
```

### Exercise 3: Calculator

Create a calculator object with arrow functions:

```javascript
const calculator = {
    add: (a, b) => a + b,
    subtract: // You complete
    multiply: // You complete
    divide: // You complete (check for division by zero!)
};

console.log(calculator.add(5, 3));      // 8
console.log(calculator.divide(10, 2));  // 5
console.log(calculator.divide(10, 0));  // null or error message
```

### Exercise 4: Callback Practice

Create a function `processNumbers(numbers, callback)` that:
- Takes an array of numbers
- Applies the callback to each number
- Returns new array with results

Test with these callbacks:
- Double each number
- Square each number
- Check if each is even

### Exercise 5: Real-World Function

Create a function `formatPrice(price, currency = "USD")` that:
- Takes a number and optional currency
- Returns formatted price string

Examples:
```javascript
formatPrice(19.99);        // "$19.99"
formatPrice(19.99, "EUR"); // "€19.99"
formatPrice(19.99, "VND"); // "19.99đ"
```

---

## Debugging Functions

### Use console.log() Inside Functions

```javascript
function calculateTotal(price, quantity) {
    console.log("Price:", price);
    console.log("Quantity:", quantity);

    let total = price * quantity;
    console.log("Total:", total);

    return total;
}

calculateTotal(29.99, 3);
```

### Use debugger Statement

```javascript
function calculateTotal(price, quantity) {
    debugger; // Pauses execution in DevTools
    let total = price * quantity;
    return total;
}
```

Open DevTools, call the function, and step through line by line!

### Check Function Exists

```javascript
if (typeof myFunction === "function") {
    myFunction();
} else {
    console.log("myFunction is not defined");
}
```

---

## Quick Reference

### Function Declaration
```javascript
function name(param1, param2) {
    // code
    return value;
}
```

### Function Expression
```javascript
const name = function(param1, param2) {
    // code
    return value;
};
```

### Arrow Function
```javascript
const name = (param1, param2) => {
    // code
    return value;
};

// Short form (implicit return)
const name = (param) => value;
```

### Scope Levels
```javascript
// Global scope
let global = "accessible everywhere";

function outer() {
    // Function scope
    let functionScoped = "accessible in function";

    if (true) {
        // Block scope
        let blockScoped = "accessible in block";
    }
}
```

### Callback Pattern
```javascript
function doSomething(callback) {
    callback();
}

doSomething(() => {
    console.log("Callback executed");
});
```

---

## Key Takeaways

1. **Three ways to create functions**: declaration, expression, arrow
2. **Arrow functions** are great for short functions and callbacks
3. **`let` and `const`** are block-scoped (not just function-scoped)
4. **Callbacks** are functions passed as arguments
5. **Scope rules**: inner can access outer, outer cannot access inner
6. **Always return a value** if you need to use it
7. **Define before use** (don't rely on hoisting)
8. **Functions are first-class** (can be passed around like values)

---

## Next Lesson

In Lesson 04, we'll learn about:
- Arrays in detail (methods, iteration)
- Objects (properties, methods)
- Array methods (map, filter, reduce)
- Object destructuring

Functions + Arrays + Objects = Powerful JavaScript! Get ready!
