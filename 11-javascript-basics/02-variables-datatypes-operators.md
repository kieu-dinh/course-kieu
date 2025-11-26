# Lesson 02 - Variables, Data Types, and Operators

**Duration**: 2-3 hours
**Prerequisites**: Lesson 01 - Introduction and Setup

---

## Variables in JavaScript

In PHP, you declare variables with `$`:
```php
<?php
$name = "Kieu";
?>
```

In JavaScript, you use `let`, `const`, or `var`:
```javascript
let name = "Kieu";
const age = 25;
var city = "Hanoi"; // Old way, avoid this
```

### The Three Ways to Declare Variables

#### 1. `let` - Changeable Variables

Use `let` when the value will change:

```javascript
let score = 0;
score = 10;      // OK!
score = 20;      // OK!

let message = "Hello";
message = "Goodbye"; // OK!
```

**Like PHP variables:**
```php
<?php
$score = 0;
$score = 10; // OK in PHP
?>
```

#### 2. `const` - Constants

Use `const` when the value should NOT change:

```javascript
const PI = 3.14159;
PI = 3.14; // ERROR! Cannot reassign const

const TAX_RATE = 0.10;
const API_URL = "https://api.example.com";
```

**Similar to PHP constants:**
```php
<?php
define("PI", 3.14159);
// PI = 3.14; // Error!
?>
```

**Important**: `const` objects and arrays can have their contents changed:
```javascript
const user = { name: "Kieu" };
user.name = "John";  // OK! Changing property
user.age = 25;       // OK! Adding property
// user = {};        // ERROR! Cannot reassign

const fruits = ["apple"];
fruits.push("banana"); // OK! Changing contents
// fruits = [];        // ERROR! Cannot reassign
```

#### 3. `var` - The Old Way (Avoid)

`var` was used before `let` and `const` existed:

```javascript
var name = "Kieu"; // Works, but don't use it
```

**Why avoid `var`?**
- Confusing scope rules
- Can be redeclared (causes bugs)
- `let` and `const` are clearer and safer

**Best Practice:**
- Use `const` by default
- Use `let` if you need to reassign
- Never use `var`

### Variable Naming Rules

**Same as PHP:**

✅ **Valid names:**
```javascript
let firstName = "Kieu";
let user_age = 25;
let $price = 19.99;  // $ is allowed (but unusual)
let _private = "secret";
```

❌ **Invalid names:**
```javascript
let 1name = "Kieu";     // Cannot start with number
let first-name = "Kieu"; // No hyphens
let let = "value";       // Cannot use reserved words
```

**Conventions:**
- Use `camelCase` for variables: `firstName`, `userAge`, `productPrice`
- Use `UPPER_CASE` for constants: `TAX_RATE`, `API_URL`
- Make names descriptive: `userName` is better than `un`

**PHP vs JavaScript:**
```php
// PHP uses snake_case
$first_name = "Kieu";
$user_age = 25;
```

```javascript
// JavaScript uses camelCase
let firstName = "Kieu";
let userAge = 25;
```

---

## Data Types in JavaScript

JavaScript has **7 primitive data types** + objects:

### 1. String

Text enclosed in quotes:

```javascript
let name = "Kieu";           // Double quotes
let city = 'Hanoi';          // Single quotes
let message = `Hello, ${name}!`; // Template literal (backticks)
```

**Template Literals** (like PHP double quotes):
```javascript
let firstName = "Kieu";
let age = 25;

// Old way (concatenation)
let message = "Hello, " + firstName + "! You are " + age + " years old.";

// New way (template literal)
let message = `Hello, ${firstName}! You are ${age} years old.`;
```

**Compare to PHP:**
```php
<?php
$firstName = "Kieu";
$age = 25;
$message = "Hello, $firstName! You are $age years old.";
?>
```

**String Methods:**
```javascript
let text = "Hello World";

text.length              // 11
text.toUpperCase()       // "HELLO WORLD"
text.toLowerCase()       // "hello world"
text.includes("World")   // true
text.startsWith("Hello") // true
text.endsWith("World")   // true
text.replace("World", "Kieu") // "Hello Kieu"
text.split(" ")          // ["Hello", "World"]
text.trim()              // Remove whitespace
text.charAt(0)           // "H"
text.slice(0, 5)         // "Hello"
```

### 2. Number

JavaScript has only ONE number type (no int vs float):

```javascript
let age = 25;           // Integer
let price = 19.99;      // Float
let negative = -10;     // Negative
let billion = 1e9;      // Scientific notation (1,000,000,000)
```

**Compare to PHP:**
```php
<?php
$age = 25;        // Integer
$price = 19.99;   // Float - different types!
?>
```

**Number Methods:**
```javascript
let num = 3.14159;

num.toFixed(2)           // "3.14" (string)
parseInt("123")          // 123 (number)
parseFloat("3.14")       // 3.14 (number)
Number("123")            // 123
Math.round(3.7)          // 4
Math.floor(3.9)          // 3
Math.ceil(3.1)           // 4
Math.random()            // Random 0-1
Math.max(1, 5, 3)        // 5
Math.min(1, 5, 3)        // 1
```

**Special Number Values:**
```javascript
let infinite = Infinity;
let notNumber = NaN;     // "Not a Number"

console.log(1 / 0);      // Infinity
console.log("hello" / 2); // NaN
```

### 3. Boolean

True or false:

```javascript
let isStudent = true;
let isLoggedIn = false;

// From comparisons
let isAdult = age >= 18;  // true or false
let isEmpty = text.length === 0; // true or false
```

**Exactly like PHP!**

### 4. Undefined

Variable declared but not assigned:

```javascript
let name;
console.log(name); // undefined

let user = {};
console.log(user.age); // undefined (property doesn't exist)
```

**PHP equivalent**: `null` or uninitialized variable

### 5. Null

Intentionally empty value:

```javascript
let user = null; // No user
let response = null; // No response yet
```

**Difference from `undefined`:**
- `undefined`: JavaScript hasn't set a value yet
- `null`: You intentionally set it to "nothing"

### 6. Symbol (Advanced)

Unique identifier - we won't use this much:
```javascript
let id = Symbol("id");
```

### 7. BigInt (Advanced)

For very large integers - rarely needed:
```javascript
let bigNumber = 9007199254740991n;
```

---

## Checking Data Types

### The `typeof` Operator

```javascript
typeof "hello"        // "string"
typeof 123            // "number"
typeof true           // "boolean"
typeof undefined      // "undefined"
typeof null           // "object" (this is a JavaScript bug!)
typeof {}             // "object"
typeof []             // "object" (arrays are objects)
typeof function(){}   // "function"
```

**Compare to PHP:**
```php
<?php
gettype("hello");  // "string"
is_string("hello"); // true
?>
```

**Example:**
```javascript
let age = 25;
console.log(typeof age); // "number"

if (typeof age === "number") {
    console.log("Age is a number!");
}
```

---

## Type Conversion

JavaScript is **loosely typed** (like PHP). Types can be converted automatically or manually.

### Automatic Conversion (Coercion)

JavaScript tries to be helpful (sometimes too helpful):

```javascript
"5" + 2         // "52" (string concatenation)
"5" - 2         // 3 (converts to number)
"5" * 2         // 10 (converts to number)
"5" / 2         // 2.5 (converts to number)

true + 1        // 2 (true = 1)
false + 1       // 1 (false = 0)

"5" == 5        // true (loose equality, converts types)
"5" === 5       // false (strict equality, no conversion)
```

**This can cause bugs!** Always use strict equality `===`.

### Manual Conversion

**To String:**
```javascript
String(123)           // "123"
(123).toString()      // "123"
123 + ""              // "123" (hacky)
```

**To Number:**
```javascript
Number("123")         // 123
Number("123.45")      // 123.45
Number("hello")       // NaN

parseInt("123")       // 123
parseInt("123.45")    // 123 (no decimals)
parseFloat("123.45")  // 123.45

+"123"                // 123 (hacky, but common)
```

**To Boolean:**
```javascript
Boolean(1)            // true
Boolean(0)            // false
Boolean("hello")      // true
Boolean("")           // false
Boolean(null)         // false
Boolean(undefined)    // false

!!value               // Convert to boolean (double NOT)
```

**Falsy Values** (convert to `false`):
```javascript
false
0
""           // Empty string
null
undefined
NaN
```

Everything else is **truthy** (converts to `true`).

---

## Operators

You already know these from PHP! The syntax is almost identical.

### Arithmetic Operators

```javascript
let a = 10;
let b = 3;

a + b    // 13 (addition)
a - b    // 7 (subtraction)
a * b    // 30 (multiplication)
a / b    // 3.333... (division)
a % b    // 1 (modulo/remainder)
a ** b   // 1000 (exponentiation: 10^3)

// Increment and decrement
let count = 0;
count++;  // count = 1
count--;  // count = 0
++count;  // count = 1 (pre-increment)
--count;  // count = 0 (pre-decrement)
```

**Same as PHP!**

### Assignment Operators

```javascript
let x = 10;

x += 5;   // x = x + 5  → 15
x -= 3;   // x = x - 3  → 12
x *= 2;   // x = x * 2  → 24
x /= 4;   // x = x / 4  → 6
x %= 4;   // x = x % 4  → 2
```

**Same as PHP!**

### Comparison Operators

```javascript
let a = 5;
let b = "5";

// Loose equality (converts types)
a == b      // true (5 == "5")
a != b      // false

// Strict equality (NO conversion) - USE THIS!
a === b     // false (number !== string)
a !== b     // true

// Relational
a > 3       // true
a < 10      // true
a >= 5      // true
a <= 4      // false
```

**Important Difference from PHP:**

```php
// PHP
$a = 5;
$b = "5";
$a == $b;   // true (type juggling)
$a === $b;  // false (strict)
```

```javascript
// JavaScript - ALWAYS use ===
let a = 5;
let b = "5";
a == b;     // true (avoid this!)
a === b;    // false (use this!)
```

**Best Practice**: Always use `===` and `!==` in JavaScript.

### Logical Operators

```javascript
true && true    // true (AND)
true && false   // false
true || false   // true (OR)
false || false  // false
!true           // false (NOT)

// Short-circuit evaluation
let result = isLoggedIn && userName; // Return userName if isLoggedIn
let fallback = userName || "Guest";  // Use "Guest" if userName is falsy
```

**Same as PHP!**

**Practical Examples:**
```javascript
// Check if user is adult and has license
if (age >= 18 && hasLicense) {
    console.log("Can drive!");
}

// Check if user is admin OR moderator
if (isAdmin || isModerator) {
    console.log("Can manage content");
}

// Toggle boolean
let isActive = true;
isActive = !isActive; // false
```

### String Operators

```javascript
let firstName = "Kieu";
let lastName = "Nguyen";

// Concatenation
let fullName = firstName + " " + lastName; // "Kieu Nguyen"

// Template literal (better!)
let greeting = `Hello, ${firstName} ${lastName}!`;
```

### Ternary Operator

Short if/else:

```javascript
let age = 20;
let status = age >= 18 ? "adult" : "minor";
// If age >= 18, return "adult", else return "minor"

console.log(status); // "adult"
```

**Same as PHP:**
```php
<?php
$age = 20;
$status = $age >= 18 ? "adult" : "minor";
?>
```

**Examples:**
```javascript
// Set discount based on membership
let discount = isMember ? 0.1 : 0;

// Set message based on stock
let message = stock > 0 ? "In stock" : "Out of stock";

// Nested ternary (avoid if too complex)
let price = isPremium ? 0 : isMember ? 9.99 : 19.99;
```

---

## Operator Precedence

Just like math class, some operators run first:

```javascript
let result = 10 + 5 * 2;  // 20, not 30 (multiplication first)

// Use parentheses to clarify
let result = (10 + 5) * 2; // 30
```

**Order (highest to lowest):**
1. Parentheses `()`
2. Exponentiation `**`
3. Multiplication, Division, Modulo `*`, `/`, `%`
4. Addition, Subtraction `+`, `-`
5. Comparison `<`, `>`, `<=`, `>=`
6. Equality `==`, `===`, `!=`, `!==`
7. Logical AND `&&`
8. Logical OR `||`
9. Ternary `? :`
10. Assignment `=`, `+=`, etc.

**When in doubt, use parentheses!**

---

## Practical Examples

### Example 1: Calculate Age from Birth Year

```javascript
const currentYear = 2024;
let birthYear = 1995;
let age = currentYear - birthYear;

console.log(`You are ${age} years old`);

// Check if adult
let isAdult = age >= 18;
console.log(`Adult: ${isAdult}`); // Adult: true
```

### Example 2: Calculate Shopping Cart Total

```javascript
let price = 29.99;
let quantity = 3;
const TAX_RATE = 0.10;

let subtotal = price * quantity;
let tax = subtotal * TAX_RATE;
let total = subtotal + tax;

console.log(`Subtotal: $${subtotal.toFixed(2)}`);
console.log(`Tax: $${tax.toFixed(2)}`);
console.log(`Total: $${total.toFixed(2)}`);
```

### Example 3: Validate User Input

```javascript
let email = "user@example.com";
let password = "secret123";

let isValidEmail = email.includes("@") && email.includes(".");
let isValidPassword = password.length >= 8;

if (isValidEmail && isValidPassword) {
    console.log("Valid credentials!");
} else {
    console.log("Invalid credentials");
}
```

### Example 4: Convert Temperature

```javascript
let celsius = 25;
let fahrenheit = (celsius * 9/5) + 32;

console.log(`${celsius}°C = ${fahrenheit}°F`);
```

### Example 5: Check Discount Eligibility

```javascript
let totalPurchases = 1200;
let isMember = true;
let age = 65;

// Senior discount (65+) OR member with $1000+ purchases
let eligibleForDiscount = age >= 65 || (isMember && totalPurchases >= 1000);

let discountRate = eligibleForDiscount ? 0.15 : 0;

console.log(`Discount: ${discountRate * 100}%`);
```

---

## Common Mistakes and How to Fix Them

### Mistake 1: Using `==` Instead of `===`

```javascript
// Bad
if (age == "18") {  // true even if age is string "18"
    console.log("Adult");
}

// Good
if (age === 18) {  // Only true if age is number 18
    console.log("Adult");
}
```

### Mistake 2: Trying to Change `const`

```javascript
// Bad
const PI = 3.14;
PI = 3.14159; // ERROR!

// Good
const PI = 3.14159; // Set it once correctly
```

### Mistake 3: Not Declaring Variables

```javascript
// Bad
name = "Kieu"; // Creates global variable (bad!)

// Good
let name = "Kieu";
```

### Mistake 4: Concatenating When You Mean to Add

```javascript
let a = "5";
let b = "10";
let sum = a + b; // "510" (string concatenation)

// Fix: Convert to numbers
let sum = Number(a) + Number(b); // 15
// Or
let sum = +a + +b; // 15
```

### Mistake 5: Not Using Template Literals

```javascript
// Messy
let message = "Hello, " + firstName + " " + lastName + "! You are " + age + " years old.";

// Clean
let message = `Hello, ${firstName} ${lastName}! You are ${age} years old.`;
```

---

## Practice Exercises

### Exercise 1: Variable Practice

Create variables for:
1. Your first name (use `const`)
2. Your age (use `let`)
3. Your city (use `const`)
4. Your student status (use `let`, boolean)

Then:
- Log each variable with its type
- Increase your age by 1
- Change your student status
- Create a greeting message using template literals

### Exercise 2: Calculator

Create a simple calculator:
```javascript
let num1 = 15;
let num2 = 4;

// Calculate and log:
// - Addition
// - Subtraction
// - Multiplication
// - Division
// - Modulo
// - Exponentiation
```

### Exercise 3: Type Conversion Challenge

Convert these values and log the results:
```javascript
let str = "123";
let num = 456;
let bool = true;

// Convert str to number
// Convert num to string
// Convert bool to number
// Convert empty string to boolean
```

### Exercise 4: Comparison Practice

Compare these values and explain why:
```javascript
5 == "5"     // ?
5 === "5"    // ?
0 == false   // ?
0 === false  // ?
null == undefined   // ?
null === undefined  // ?
```

### Exercise 5: Real-World Calculation

Create a program that calculates:
- Product price: $49.99
- Quantity: 3
- Tax rate: 8% (0.08)
- Discount if total > $100: 10% (0.10)

Calculate:
1. Subtotal
2. Discount amount (if applicable)
3. Price after discount
4. Tax amount
5. Final total

Use template literals to create a receipt.

---

## Testing in the Browser

Open your browser console and try:

```javascript
// Variables
let name = "Kieu";
const age = 25;
console.log(`${name} is ${age} years old`);

// Math
let price = 29.99;
let quantity = 3;
let total = price * quantity;
console.log(`Total: $${total.toFixed(2)}`);

// Comparison
let isAdult = age >= 18;
console.log(`Is adult: ${isAdult}`);

// Type checking
console.log(typeof name);
console.log(typeof age);
console.log(typeof isAdult);
```

---

## Quick Reference

### Variable Declaration
```javascript
let changeable = "value";    // Can reassign
const constant = "value";    // Cannot reassign
```

### Data Types
```javascript
"string"    // String
123         // Number
true/false  // Boolean
null        // Null
undefined   // Undefined
```

### Operators
```javascript
+  -  *  /  %  **          // Arithmetic
=  +=  -=  *=  /=          // Assignment
===  !==  >  <  >=  <=     // Comparison
&&  ||  !                  // Logical
? :                        // Ternary
```

### Type Conversion
```javascript
String(value)              // To string
Number(value)              // To number
Boolean(value)             // To boolean
```

### String Methods
```javascript
str.length
str.toUpperCase()
str.toLowerCase()
str.includes("text")
str.split(" ")
str.trim()
```

### Number Methods
```javascript
num.toFixed(2)
parseInt("123")
parseFloat("3.14")
Math.round()
Math.random()
```

---

## Key Takeaways

1. **Use `const` by default**, `let` if you need to reassign
2. **Never use `var`**
3. **Always use `===` and `!==`** for comparison (strict equality)
4. **Use template literals** (backticks) for string interpolation
5. **JavaScript has only one number type** (no int vs float)
6. **Check types with `typeof`**
7. **Be careful with type coercion** (automatic conversion)
8. **Use `camelCase` for variable names**

---

## Next Lesson

In Lesson 03, we'll learn about:
- Functions (declaration, expression, arrow functions)
- Scope (global, local, block)
- Parameters and return values
- Callback functions

You already know functions from PHP, so this will be mostly translating syntax!
