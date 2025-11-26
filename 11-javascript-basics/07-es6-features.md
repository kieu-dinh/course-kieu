# Lesson 07 - ES6 Features (Modern JavaScript)

**Duration**: 2-3 hours
**Prerequisites**: Lesson 06 - Event Handling

---

## What is ES6?

**ES6** (ECMAScript 2015) was a major update to JavaScript released in 2015. It added many features that make JavaScript more powerful and easier to write.

You've already been using some ES6 features:
- `let` and `const`
- Arrow functions (`=>`)
- Template literals (backticks)

Let's dive deeper into these and learn more modern JavaScript!

---

## let and const (Review + Deep Dive)

### The Problem with var

Before ES6, we only had `var`:

```javascript
// var is function-scoped, not block-scoped
if (true) {
    var message = "Hello";
}
console.log(message); // "Hello" - accessible outside block!

// var can be redeclared
var name = "Kieu";
var name = "John"; // No error (confusing!)

// var is hoisted
console.log(count); // undefined (not error!)
var count = 5;
```

### let and const Fix These Issues

```javascript
// Block-scoped
if (true) {
    let message = "Hello";
}
console.log(message); // Error: message is not defined

// Cannot be redeclared
let name = "Kieu";
let name = "John"; // Error!

// Not hoisted (Temporal Dead Zone)
console.log(count); // Error: Cannot access before initialization
let count = 5;
```

### When to Use Each

**Use `const` by default:**
```javascript
const name = "Kieu";
const age = 25;
const fruits = ["apple", "banana"];
```

**Use `let` when you need to reassign:**
```javascript
let count = 0;
count++; // Reassigning

let message = "Hello";
message = "Goodbye"; // Reassigning
```

**Remember**: `const` objects and arrays can have their contents changed:
```javascript
const user = { name: "Kieu" };
user.name = "John"; // OK! (changing property)
user.age = 25;      // OK! (adding property)
// user = {};       // Error! (reassigning)

const fruits = ["apple"];
fruits.push("banana"); // OK! (changing contents)
// fruits = [];        // Error! (reassigning)
```

---

## Arrow Functions (Deep Dive)

You've seen basic arrow functions. Let's explore all their features.

### Basic Syntax

```javascript
// Traditional function
function add(a, b) {
    return a + b;
}

// Arrow function
const add = (a, b) => {
    return a + b;
};

// Implicit return (one-liner)
const add = (a, b) => a + b;
```

### Syntax Variations

```javascript
// No parameters
const greet = () => "Hello!";

// One parameter (parentheses optional)
const square = x => x * x;
const square = (x) => x * x; // Same thing

// Multiple parameters
const add = (a, b) => a + b;

// Multiple statements (need braces and return)
const greet = (name) => {
    const message = `Hello, ${name}!`;
    return message;
};

// Returning object (wrap in parentheses)
const makePerson = (name, age) => ({ name: name, age: age });
// Or with shorthand:
const makePerson = (name, age) => ({ name, age });
```

### Arrow Functions and `this`

**Important difference** from regular functions:

```javascript
// Regular function: 'this' depends on how it's called
const person = {
    name: "Kieu",
    greet: function() {
        console.log(this.name); // "Kieu"
    }
};

// Arrow function: 'this' is inherited from outer scope
const person = {
    name: "Kieu",
    greet: () => {
        console.log(this.name); // undefined (not what you want!)
    }
};
```

**Rule**: Don't use arrow functions for object methods.

**Use arrow functions for callbacks:**
```javascript
const person = {
    name: "Kieu",
    friends: ["John", "Jane"],

    greetAll: function() {
        // Arrow function inherits 'this' from greetAll
        this.friends.forEach((friend) => {
            console.log(`${this.name} says hello to ${friend}`);
        });
    }
};

person.greetAll();
// "Kieu says hello to John"
// "Kieu says hello to Jane"
```

---

## Template Literals (Deep Dive)

### Basic Usage

```javascript
const name = "Kieu";
const age = 25;

// Old way
const message = "Hello, " + name + "! You are " + age + " years old.";

// Template literal
const message = `Hello, ${name}! You are ${age} years old.`;
```

### Multi-line Strings

```javascript
// Old way (awkward)
const html = "<div>\n" +
    "  <h1>Title</h1>\n" +
    "  <p>Content</p>\n" +
    "</div>";

// Template literal (natural)
const html = `
<div>
  <h1>Title</h1>
  <p>Content</p>
</div>
`;
```

### Expression Interpolation

You can put **any expression** inside `${}`:

```javascript
const a = 10;
const b = 20;

console.log(`Sum: ${a + b}`);           // "Sum: 30"
console.log(`Product: ${a * b}`);       // "Product: 200"
console.log(`Larger: ${a > b ? a : b}`); // "Larger: 20"

// Function calls
const getUsername = () => "Kieu";
console.log(`User: ${getUsername()}`);  // "User: Kieu"

// Object properties
const user = { name: "Kieu", age: 25 };
console.log(`${user.name} is ${user.age} years old`);
```

### Nested Template Literals

```javascript
const users = [
    { name: "Kieu", age: 25 },
    { name: "John", age: 30 }
];

const html = `
<ul>
    ${users.map(user => `
        <li>${user.name} (${user.age})</li>
    `).join("")}
</ul>
`;
```

### Tagged Templates (Advanced)

You can process template literals with a function:

```javascript
function highlight(strings, ...values) {
    return strings.reduce((result, str, i) => {
        return `${result}${str}<strong>${values[i] || ""}</strong>`;
    }, "");
}

const name = "Kieu";
const age = 25;
const message = highlight`Hello, ${name}! You are ${age} years old.`;
// "Hello, <strong>Kieu</strong>! You are <strong>25</strong> years old."
```

---

## Destructuring Assignment

Extract values from arrays or objects into variables.

### Array Destructuring

```javascript
// Old way
const colors = ["red", "green", "blue"];
const first = colors[0];
const second = colors[1];

// Destructuring
const [first, second, third] = colors;
console.log(first);  // "red"
console.log(second); // "green"
console.log(third);  // "blue"
```

**Advanced patterns:**

```javascript
// Skip elements
const [first, , third] = ["red", "green", "blue"];
console.log(first);  // "red"
console.log(third);  // "blue"

// Default values
const [a, b, c = "yellow"] = ["red", "green"];
console.log(c); // "yellow" (default)

// Rest operator
const [first, ...rest] = ["red", "green", "blue", "yellow"];
console.log(first); // "red"
console.log(rest);  // ["green", "blue", "yellow"]

// Swapping variables
let a = 1;
let b = 2;
[a, b] = [b, a];
console.log(a); // 2
console.log(b); // 1
```

### Object Destructuring

```javascript
// Old way
const user = { name: "Kieu", age: 25, city: "Hanoi" };
const name = user.name;
const age = user.age;

// Destructuring
const { name, age, city } = user;
console.log(name); // "Kieu"
console.log(age);  // 25
console.log(city); // "Hanoi"
```

**Advanced patterns:**

```javascript
// Different variable names
const { name: userName, age: userAge } = user;
console.log(userName); // "Kieu"
console.log(userAge);  // 25

// Default values
const { name, country = "Vietnam" } = user;
console.log(country); // "Vietnam" (default)

// Rest operator
const { name, ...rest } = user;
console.log(name); // "Kieu"
console.log(rest); // { age: 25, city: "Hanoi" }

// Nested destructuring
const user = {
    name: "Kieu",
    address: {
        city: "Hanoi",
        country: "Vietnam"
    }
};

const { address: { city, country } } = user;
console.log(city);    // "Hanoi"
console.log(country); // "Vietnam"
```

### Destructuring in Function Parameters

```javascript
// Instead of this
function greet(user) {
    console.log(`Hello, ${user.name}! You are ${user.age} years old.`);
}

// Use destructuring
function greet({ name, age }) {
    console.log(`Hello, ${name}! You are ${age} years old.`);
}

const user = { name: "Kieu", age: 25 };
greet(user); // "Hello, Kieu! You are 25 years old."

// With defaults
function greet({ name, age = 18 }) {
    console.log(`Hello, ${name}! You are ${age} years old.`);
}

greet({ name: "John" }); // "Hello, John! You are 18 years old."
```

---

## Spread Operator (...)

Expand arrays or objects.

### Array Spread

```javascript
const arr1 = [1, 2, 3];
const arr2 = [4, 5, 6];

// Copy array
const copy = [...arr1];
console.log(copy); // [1, 2, 3]

// Merge arrays
const merged = [...arr1, ...arr2];
console.log(merged); // [1, 2, 3, 4, 5, 6]

// Add elements
const newArr = [0, ...arr1, 4];
console.log(newArr); // [0, 1, 2, 3, 4]

// Pass array as arguments
const numbers = [1, 5, 3, 9, 2];
console.log(Math.max(...numbers)); // 9
// Same as: Math.max(1, 5, 3, 9, 2)
```

### Object Spread

```javascript
const user = { name: "Kieu", age: 25 };

// Copy object
const copy = { ...user };
console.log(copy); // { name: "Kieu", age: 25 }

// Merge objects
const location = { city: "Hanoi", country: "Vietnam" };
const merged = { ...user, ...location };
console.log(merged);
// { name: "Kieu", age: 25, city: "Hanoi", country: "Vietnam" }

// Override properties
const updated = { ...user, age: 26 };
console.log(updated); // { name: "Kieu", age: 26 }

// Add properties
const extended = { ...user, email: "kieu@example.com" };
console.log(extended);
// { name: "Kieu", age: 25, email: "kieu@example.com" }
```

---

## Rest Parameters

Collect remaining arguments into an array.

```javascript
// Old way (arguments object)
function sum() {
    let total = 0;
    for (let i = 0; i < arguments.length; i++) {
        total += arguments[i];
    }
    return total;
}

// Rest parameters (better)
function sum(...numbers) {
    return numbers.reduce((total, num) => total + num, 0);
}

console.log(sum(1, 2, 3));       // 6
console.log(sum(1, 2, 3, 4, 5)); // 15
```

**Combine with regular parameters:**

```javascript
function greet(greeting, ...names) {
    return `${greeting}, ${names.join(" and ")}!`;
}

console.log(greet("Hello", "Kieu"));              // "Hello, Kieu!"
console.log(greet("Hello", "Kieu", "John"));      // "Hello, Kieu and John!"
console.log(greet("Hello", "Kieu", "John", "Jane")); // "Hello, Kieu and John and Jane!"
```

**Rest must be last parameter:**

```javascript
// Good
function example(a, b, ...rest) { }

// Bad
function example(...rest, a, b) { } // Error!
```

---

## Default Parameters

Set default values for function parameters.

```javascript
// Old way
function greet(name) {
    name = name || "Guest";
    return `Hello, ${name}!`;
}

// ES6 way (cleaner)
function greet(name = "Guest") {
    return `Hello, ${name}!`;
}

console.log(greet());        // "Hello, Guest!"
console.log(greet("Kieu"));  // "Hello, Kieu!"
```

**Multiple defaults:**

```javascript
function createUser(name = "Guest", age = 18, country = "Vietnam") {
    return { name, age, country };
}

console.log(createUser());
// { name: "Guest", age: 18, country: "Vietnam" }

console.log(createUser("Kieu", 25));
// { name: "Kieu", age: 25, country: "Vietnam" }
```

**Defaults can be expressions:**

```javascript
function greet(name, greeting = `Hello, ${name}!`) {
    return greeting;
}

console.log(greet("Kieu")); // "Hello, Kieu!"

function createId(prefix = "user", id = Math.random()) {
    return `${prefix}-${id}`;
}

console.log(createId()); // "user-0.123456789"
```

---

## Enhanced Object Literals

### Property Shorthand

```javascript
const name = "Kieu";
const age = 25;

// Old way
const user = {
    name: name,
    age: age
};

// Shorthand
const user = { name, age };
```

### Method Shorthand

```javascript
// Old way
const user = {
    name: "Kieu",
    greet: function() {
        return `Hello, ${this.name}!`;
    }
};

// Shorthand
const user = {
    name: "Kieu",
    greet() {
        return `Hello, ${this.name}!`;
    }
};
```

### Computed Property Names

```javascript
const key = "name";
const value = "Kieu";

// Old way
const user = {};
user[key] = value;

// ES6 way
const user = {
    [key]: value
};

console.log(user); // { name: "Kieu" }

// Dynamic keys
const prop = "age";
const user = {
    name: "Kieu",
    [prop]: 25,
    [`get${prop}`]: function() { return this[prop]; }
};

console.log(user); // { name: "Kieu", age: 25, getage: [Function] }
```

---

## for...of Loop

Iterate over iterable objects (arrays, strings, etc).

### Array Iteration

```javascript
const fruits = ["apple", "banana", "cherry"];

// Old way
for (let i = 0; i < fruits.length; i++) {
    console.log(fruits[i]);
}

// forEach (ES5)
fruits.forEach((fruit) => {
    console.log(fruit);
});

// for...of (ES6)
for (let fruit of fruits) {
    console.log(fruit);
}
```

**When to use each:**

- `for`: Need index, need to break/continue
- `forEach`: Simple iteration, no break/continue
- `for...of`: Clean iteration, can break/continue

### With Index

```javascript
const fruits = ["apple", "banana", "cherry"];

for (let [index, fruit] of fruits.entries()) {
    console.log(`${index}: ${fruit}`);
}
// 0: apple
// 1: banana
// 2: cherry
```

### String Iteration

```javascript
const text = "Hello";

for (let char of text) {
    console.log(char);
}
// H
// e
// l
// l
// o
```

### for...of vs for...in

```javascript
const fruits = ["apple", "banana", "cherry"];

// for...in iterates over keys (avoid for arrays)
for (let index in fruits) {
    console.log(index); // "0", "1", "2" (strings!)
}

// for...of iterates over values (good for arrays)
for (let fruit of fruits) {
    console.log(fruit); // "apple", "banana", "cherry"
}
```

**Use for...in for objects, for...of for arrays.**

---

## String Methods (ES6+)

### startsWith(), endsWith(), includes()

```javascript
const text = "Hello World";

console.log(text.startsWith("Hello"));  // true
console.log(text.startsWith("World"));  // false
console.log(text.endsWith("World"));    // true
console.log(text.endsWith("Hello"));    // false
console.log(text.includes("o W"));      // true
console.log(text.includes("xyz"));      // false
```

### repeat()

```javascript
console.log("Ha".repeat(3));  // "HaHaHa"
console.log("-".repeat(10));  // "----------"
```

### padStart(), padEnd()

```javascript
const num = "5";
console.log(num.padStart(3, "0")); // "005"
console.log(num.padEnd(3, "0"));   // "500"

const time = "9:30";
console.log(time.padStart(5, "0")); // "09:30"
```

---

## Array Methods (ES6+)

### find() and findIndex()

```javascript
const users = [
    { id: 1, name: "Kieu" },
    { id: 2, name: "John" },
    { id: 3, name: "Jane" }
];

// find() returns first match
const user = users.find((u) => u.id === 2);
console.log(user); // { id: 2, name: "John" }

// findIndex() returns index
const index = users.findIndex((u) => u.id === 2);
console.log(index); // 1
```

### Array.from()

Convert array-like objects to arrays:

```javascript
// Convert string to array
const chars = Array.from("Hello");
console.log(chars); // ["H", "e", "l", "l", "o"]

// Convert NodeList to array
const divs = document.querySelectorAll("div");
const divsArray = Array.from(divs);

// With map function
const numbers = Array.from([1, 2, 3], (x) => x * 2);
console.log(numbers); // [2, 4, 6]

// Create array with length
const zeros = Array.from({ length: 5 }, () => 0);
console.log(zeros); // [0, 0, 0, 0, 0]
```

### Array.of()

Create array from arguments:

```javascript
const arr1 = Array.of(1, 2, 3);
console.log(arr1); // [1, 2, 3]

const arr2 = Array.of(5);
console.log(arr2); // [5] (not empty array with length 5)
```

---

## Practical Examples

### Example 1: Clean API Response

```javascript
// API returns messy data
const apiData = {
    user_id: 1,
    user_name: "Kieu",
    user_email: "kieu@example.com",
    user_age: 25,
    user_city: "Hanoi"
};

// Clean it up
const { user_id: id, user_name: name, user_email: email, user_age: age, user_city: city } = apiData;
const cleanUser = { id, name, email, age, city };

console.log(cleanUser);
// { id: 1, name: "Kieu", email: "kieu@example.com", age: 25, city: "Hanoi" }
```

### Example 2: Merge User Settings

```javascript
const defaultSettings = {
    theme: "light",
    notifications: true,
    language: "en"
};

const userSettings = {
    theme: "dark",
    fontSize: 16
};

// Merge (user settings override defaults)
const finalSettings = { ...defaultSettings, ...userSettings };
console.log(finalSettings);
// { theme: "dark", notifications: true, language: "en", fontSize: 16 }
```

### Example 3: Dynamic HTML Generation

```javascript
const products = [
    { id: 1, name: "Laptop", price: 999 },
    { id: 2, name: "Mouse", price: 25 },
    { id: 3, name: "Keyboard", price: 75 }
];

const html = `
<div class="products">
    ${products.map(({ name, price }) => `
        <div class="product">
            <h3>${name}</h3>
            <p>$${price}</p>
        </div>
    `).join("")}
</div>
`;

document.body.innerHTML = html;
```

### Example 4: Flexible Function

```javascript
function createUser({ name, age = 18, ...rest }) {
    return {
        id: Math.random(),
        name,
        age,
        createdAt: new Date(),
        ...rest
    };
}

const user1 = createUser({ name: "Kieu", age: 25 });
const user2 = createUser({ name: "John", city: "Hanoi", country: "Vietnam" });

console.log(user1);
// { id: 0.123, name: "Kieu", age: 25, createdAt: Date }

console.log(user2);
// { id: 0.456, name: "John", age: 18, createdAt: Date, city: "Hanoi", country: "Vietnam" }
```

---

## Practice Exercises

### Exercise 1: Refactor with ES6

Refactor this code using ES6 features:

```javascript
function calculateTotal(price, quantity, tax) {
    if (tax === undefined) {
        tax = 0.1;
    }
    var subtotal = price * quantity;
    var taxAmount = subtotal * tax;
    var total = subtotal + taxAmount;
    return total;
}
```

### Exercise 2: Object Destructuring

Extract data from this object:

```javascript
const response = {
    status: 200,
    data: {
        user: {
            id: 1,
            name: "Kieu",
            email: "kieu@example.com"
        },
        posts: [
            { id: 1, title: "First Post" },
            { id: 2, title: "Second Post" }
        ]
    }
};

// Extract: status, user name, user email, first post title
```

### Exercise 3: Dynamic Keys

Create an object from these arrays:

```javascript
const keys = ["name", "age", "city"];
const values = ["Kieu", 25, "Hanoi"];

// Result: { name: "Kieu", age: 25, city: "Hanoi" }
```

### Exercise 4: Array Manipulation

Use ES6 methods to:
1. Find user by ID
2. Get all adult users (age >= 18)
3. Get array of names only
4. Check if any user is under 18

```javascript
const users = [
    { id: 1, name: "Kieu", age: 17 },
    { id: 2, name: "John", age: 30 },
    { id: 3, name: "Jane", age: 25 }
];
```

### Exercise 5: Template Literal Function

Create a function that generates an HTML card:

```javascript
function createCard({ title, description, price, image }) {
    // Return HTML string using template literals
}

const product = {
    title: "Laptop",
    description: "High-performance laptop",
    price: 999,
    image: "laptop.jpg"
};

console.log(createCard(product));
```

---

## Quick Reference

### Variables
```javascript
const name = "value";  // Cannot reassign
let count = 0;         // Can reassign
```

### Arrow Functions
```javascript
const fn = () => value;           // No params
const fn = x => x * 2;            // One param
const fn = (a, b) => a + b;       // Multiple params
const fn = () => ({ key: value }); // Return object
```

### Template Literals
```javascript
`Hello, ${name}!`
`Multi
line`
```

### Destructuring
```javascript
const [a, b] = array;
const { name, age } = object;
```

### Spread/Rest
```javascript
const copy = [...arr];
const merged = { ...obj1, ...obj2 };
function fn(...args) { }
```

### Defaults
```javascript
function fn(param = "default") { }
```

---

## Key Takeaways

1. **Use `const` by default**, `let` when needed, never `var`
2. **Arrow functions** are great for callbacks and short functions
3. **Template literals** make string building easier
4. **Destructuring** makes extracting values cleaner
5. **Spread operator** simplifies copying and merging
6. **Default parameters** make functions more flexible
7. **for...of** is the modern way to iterate arrays

---

## Next Lesson

In Lesson 08, we'll learn about:
- Asynchronous JavaScript
- setTimeout and setInterval
- Callbacks and callback hell
- Introduction to Promises
- async/await basics

Time to learn how JavaScript handles time!
