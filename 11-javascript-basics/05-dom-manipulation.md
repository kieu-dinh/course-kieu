# Lesson 05 - DOM Manipulation

**Duration**: 3-4 hours
**Prerequisites**: Lesson 04 - Arrays and Objects

---

## What is the DOM?

**DOM** = Document Object Model

When a browser loads your HTML, it creates a **JavaScript representation** of your page called the DOM. It's a tree structure of all your HTML elements.

### HTML to DOM

**HTML:**
```html
<!DOCTYPE html>
<html>
  <head>
    <title>My Page</title>
  </head>
  <body>
    <h1>Hello World</h1>
    <p>This is a paragraph.</p>
  </body>
</html>
```

**DOM Tree:**
```
document
  └─ html
      ├─ head
      │   └─ title
      │       └─ "My Page"
      └─ body
          ├─ h1
          │   └─ "Hello World"
          └─ p
              └─ "This is a paragraph."
```

**JavaScript can access and modify this tree!**

### The `document` Object

The entry point to the DOM:

```javascript
console.log(document);           // The whole page
console.log(document.title);     // Page title
console.log(document.URL);       // Current URL
console.log(document.body);      // <body> element
```

---

## Selecting Elements

Before you can modify an element, you need to **select it**.

### 1. getElementById() - Select by ID

```html
<h1 id="title">Hello World</h1>
```

```javascript
let title = document.getElementById("title");
console.log(title); // <h1 id="title">Hello World</h1>
```

**Most common** for unique elements.

### 2. getElementsByClassName() - Select by Class

```html
<p class="text">First paragraph</p>
<p class="text">Second paragraph</p>
```

```javascript
let paragraphs = document.getElementsByClassName("text");
console.log(paragraphs);        // HTMLCollection [p.text, p.text]
console.log(paragraphs.length); // 2
console.log(paragraphs[0]);     // First <p>
```

**Returns HTMLCollection** (array-like, but not a real array).

### 3. getElementsByTagName() - Select by Tag

```html
<p>First paragraph</p>
<p>Second paragraph</p>
```

```javascript
let paragraphs = document.getElementsByTagName("p");
console.log(paragraphs.length); // 2
```

### 4. querySelector() - Select First Match (CSS Selector)

**Most modern and flexible:**

```html
<h1 id="title">Hello</h1>
<p class="text">Paragraph 1</p>
<p class="text highlight">Paragraph 2</p>
<div data-id="123">Content</div>
```

```javascript
// By ID
let title = document.querySelector("#title");

// By class
let text = document.querySelector(".text");

// By tag
let paragraph = document.querySelector("p");

// Complex selectors
let highlight = document.querySelector(".text.highlight");
let dataAttr = document.querySelector("[data-id='123']");

// Child selectors
let firstP = document.querySelector("div p");
```

**Returns first match** or `null` if not found.

### 5. querySelectorAll() - Select All Matches

```html
<p class="text">Paragraph 1</p>
<p class="text">Paragraph 2</p>
<p class="text">Paragraph 3</p>
```

```javascript
let paragraphs = document.querySelectorAll(".text");
console.log(paragraphs);        // NodeList [p.text, p.text, p.text]
console.log(paragraphs.length); // 3

// Iterate over NodeList
paragraphs.forEach((p) => {
    console.log(p.textContent);
});
```

**Returns NodeList** (can use forEach, unlike HTMLCollection).

### Which Method to Use?

**Use querySelector() and querySelectorAll()** - they're the most flexible!

```javascript
// Good (modern)
let title = document.querySelector("#title");
let items = document.querySelectorAll(".item");

// Old way (still works)
let title = document.getElementById("title");
let items = document.getElementsByClassName("item");
```

---

## Reading Element Content

### textContent - Plain Text

```html
<h1 id="title">Hello <strong>World</strong></h1>
```

```javascript
let title = document.querySelector("#title");

console.log(title.textContent); // "Hello World" (no HTML tags)
```

### innerHTML - HTML Content

```html
<div id="content">
    <p>Hello <strong>World</strong></p>
</div>
```

```javascript
let content = document.querySelector("#content");

console.log(content.innerHTML);
// "<p>Hello <strong>World</strong></p>"
```

### innerText - Visible Text Only

```html
<div id="content">
    Hello <span style="display: none;">Hidden</span> World
</div>
```

```javascript
let content = document.querySelector("#content");

console.log(content.textContent); // "Hello Hidden World"
console.log(content.innerText);   // "Hello World" (hidden text excluded)
```

**Use textContent** in most cases (faster, more predictable).

---

## Changing Element Content

### Change Text

```html
<h1 id="title">Old Title</h1>
```

```javascript
let title = document.querySelector("#title");

title.textContent = "New Title";
// <h1 id="title">New Title</h1>
```

### Change HTML

```html
<div id="content">Old content</div>
```

```javascript
let content = document.querySelector("#content");

content.innerHTML = "<p>New <strong>HTML</strong> content</p>";
// <div id="content"><p>New <strong>HTML</strong> content</p></div>
```

**Warning**: Be careful with innerHTML - it can introduce XSS vulnerabilities if you insert user input!

**Safe:**
```javascript
content.innerHTML = "<p>Hello World</p>";
```

**Unsafe:**
```javascript
let userInput = '<script>alert("XSS")</script>';
content.innerHTML = userInput; // Dangerous!
```

**Better:**
```javascript
content.textContent = userInput; // Safe (no HTML parsing)
```

---

## Changing Styles

### style Property

```html
<h1 id="title">Hello World</h1>
```

```javascript
let title = document.querySelector("#title");

// Change individual styles
title.style.color = "red";
title.style.fontSize = "48px";
title.style.backgroundColor = "yellow";

// Note: CSS properties become camelCase
// background-color → backgroundColor
// font-size → fontSize
// text-align → textAlign
```

**CSS to JavaScript property names:**
- `background-color` → `backgroundColor`
- `font-size` → `fontSize`
- `margin-top` → `marginTop`
- `border-radius` → `borderRadius`

### Multiple Styles at Once

```javascript
let title = document.querySelector("#title");

title.style.cssText = "color: red; font-size: 48px; background: yellow;";

// Or use Object.assign
Object.assign(title.style, {
    color: "red",
    fontSize: "48px",
    backgroundColor: "yellow"
});
```

### Getting Computed Styles

```javascript
let title = document.querySelector("#title");

let styles = window.getComputedStyle(title);
console.log(styles.color);      // "rgb(255, 0, 0)"
console.log(styles.fontSize);   // "48px"
```

---

## Working with Classes

**Best practice**: Use classes instead of inline styles!

### classList Property

```html
<div id="box" class="container"></div>
```

```javascript
let box = document.querySelector("#box");

// Add class
box.classList.add("active");
// <div class="container active">

// Add multiple classes
box.classList.add("highlight", "large");
// <div class="container active highlight large">

// Remove class
box.classList.remove("active");
// <div class="container highlight large">

// Toggle class (add if not present, remove if present)
box.classList.toggle("active");
// <div class="container highlight large active">

box.classList.toggle("active");
// <div class="container highlight large">

// Check if has class
if (box.classList.contains("active")) {
    console.log("Box is active");
}

// Replace class
box.classList.replace("large", "small");
// <div class="container highlight small">
```

### className Property (Old Way)

```javascript
let box = document.querySelector("#box");

// Get all classes as string
console.log(box.className); // "container active"

// Set classes (overwrites all)
box.className = "new-class";
// <div class="new-class">
```

**Use classList - it's cleaner and safer!**

---

## Working with Attributes

### Standard Attributes

```html
<img id="photo" src="photo.jpg" alt="My Photo">
<a id="link" href="https://example.com">Link</a>
<input id="email" type="email" placeholder="Enter email">
```

```javascript
let photo = document.querySelector("#photo");
let link = document.querySelector("#link");
let input = document.querySelector("#email");

// Get attribute
console.log(photo.src);         // "photo.jpg"
console.log(link.href);         // "https://example.com"
console.log(input.placeholder); // "Enter email"

// Set attribute
photo.src = "new-photo.jpg";
link.href = "https://google.com";
input.placeholder = "Your email here";

// Remove attribute
input.removeAttribute("placeholder");
```

### getAttribute() and setAttribute()

```html
<div id="box" data-id="123" data-color="red"></div>
```

```javascript
let box = document.querySelector("#box");

// Get attribute
let id = box.getAttribute("data-id");      // "123"
let color = box.getAttribute("data-color"); // "red"

// Set attribute
box.setAttribute("data-id", "456");
box.setAttribute("data-color", "blue");

// Check if has attribute
if (box.hasAttribute("data-id")) {
    console.log("Has data-id");
}

// Remove attribute
box.removeAttribute("data-color");
```

### Data Attributes (data-*)

```html
<div id="user" data-id="123" data-name="Kieu" data-age="25"></div>
```

```javascript
let user = document.querySelector("#user");

// Access via dataset (modern way)
console.log(user.dataset.id);    // "123"
console.log(user.dataset.name);  // "Kieu"
console.log(user.dataset.age);   // "25"

// Set via dataset
user.dataset.email = "kieu@example.com";
// <div data-email="kieu@example.com">

// Note: multi-word attributes use camelCase
// data-user-id → dataset.userId
// data-birth-year → dataset.birthYear
```

---

## Creating Elements

### createElement()

```javascript
// Create element
let paragraph = document.createElement("p");

// Set content
paragraph.textContent = "This is a new paragraph";

// Set attributes
paragraph.id = "my-paragraph";
paragraph.className = "text highlight";

// Set styles
paragraph.style.color = "blue";

console.log(paragraph);
// <p id="my-paragraph" class="text highlight" style="color: blue;">
//   This is a new paragraph
// </p>

// But it's not in the page yet!
```

### Appending to Page

```javascript
let paragraph = document.createElement("p");
paragraph.textContent = "New paragraph";

// Append to body
document.body.appendChild(paragraph);

// Append to specific element
let container = document.querySelector("#container");
container.appendChild(paragraph);
```

### append() vs appendChild()

```javascript
let container = document.querySelector("#container");

// appendChild() - only accepts one Node
let p1 = document.createElement("p");
p1.textContent = "Paragraph 1";
container.appendChild(p1);

// append() - accepts multiple Nodes and strings
let p2 = document.createElement("p");
p2.textContent = "Paragraph 2";

container.append(p2, "Some text", p3);
// More flexible!
```

### prepend() - Add to Beginning

```javascript
let container = document.querySelector("#container");
let title = document.createElement("h2");
title.textContent = "Title";

container.prepend(title); // Add as first child
```

### insertBefore()

```javascript
let container = document.querySelector("#container");
let existingP = container.querySelector("p");

let newP = document.createElement("p");
newP.textContent = "Inserted before";

container.insertBefore(newP, existingP);
```

### insertAdjacentHTML()

Insert HTML at specific position:

```javascript
let container = document.querySelector("#container");

// beforebegin: before the element
// afterbegin: first child of element
// beforeend: last child of element
// afterend: after the element

container.insertAdjacentHTML("beforeend", "<p>New paragraph</p>");
```

---

## Removing Elements

### remove()

```html
<div id="container">
    <p id="remove-me">Remove this</p>
</div>
```

```javascript
let paragraph = document.querySelector("#remove-me");
paragraph.remove();
// Element is gone from the page
```

### removeChild()

```javascript
let container = document.querySelector("#container");
let paragraph = document.querySelector("#remove-me");

container.removeChild(paragraph);
```

### Clear All Children

```javascript
let container = document.querySelector("#container");

// Method 1: Set innerHTML to empty
container.innerHTML = "";

// Method 2: Remove children one by one
while (container.firstChild) {
    container.removeChild(container.firstChild);
}
```

---

## Practical Examples

### Example 1: Todo List (Add Items)

```html
<div id="app">
    <input type="text" id="todo-input" placeholder="Enter todo">
    <button id="add-btn">Add</button>
    <ul id="todo-list"></ul>
</div>
```

```javascript
let input = document.querySelector("#todo-input");
let addBtn = document.querySelector("#add-btn");
let list = document.querySelector("#todo-list");

addBtn.addEventListener("click", () => {
    let todoText = input.value;

    if (todoText.trim() === "") {
        alert("Please enter a todo");
        return;
    }

    // Create list item
    let li = document.createElement("li");
    li.textContent = todoText;

    // Add to list
    list.appendChild(li);

    // Clear input
    input.value = "";
});
```

### Example 2: Show/Hide Content

```html
<button id="toggle-btn">Show/Hide</button>
<div id="content" style="display: none;">
    <p>This content can be toggled</p>
</div>
```

```javascript
let toggleBtn = document.querySelector("#toggle-btn");
let content = document.querySelector("#content");

toggleBtn.addEventListener("click", () => {
    if (content.style.display === "none") {
        content.style.display = "block";
    } else {
        content.style.display = "none";
    }
});

// Better: use classList
toggleBtn.addEventListener("click", () => {
    content.classList.toggle("hidden");
});
```

```css
.hidden {
    display: none;
}
```

### Example 3: Dynamic Table

```html
<div id="app">
    <table id="user-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Age</th>
                <th>City</th>
            </tr>
        </thead>
        <tbody id="table-body"></tbody>
    </table>
</div>
```

```javascript
let users = [
    { name: "Kieu", age: 25, city: "Hanoi" },
    { name: "John", age: 30, city: "New York" },
    { name: "Jane", age: 28, city: "London" }
];

let tbody = document.querySelector("#table-body");

users.forEach((user) => {
    // Create row
    let tr = document.createElement("tr");

    // Create cells
    let tdName = document.createElement("td");
    tdName.textContent = user.name;

    let tdAge = document.createElement("td");
    tdAge.textContent = user.age;

    let tdCity = document.createElement("td");
    tdCity.textContent = user.city;

    // Append cells to row
    tr.append(tdName, tdAge, tdCity);

    // Append row to table
    tbody.appendChild(tr);
});
```

### Example 4: Image Gallery

```html
<div id="gallery"></div>
```

```javascript
let images = [
    { src: "photo1.jpg", alt: "Photo 1" },
    { src: "photo2.jpg", alt: "Photo 2" },
    { src: "photo3.jpg", alt: "Photo 3" }
];

let gallery = document.querySelector("#gallery");

images.forEach((image) => {
    let img = document.createElement("img");
    img.src = image.src;
    img.alt = image.alt;
    img.style.width = "200px";
    img.style.margin = "10px";

    gallery.appendChild(img);
});
```

### Example 5: Counter

```html
<div id="counter-app">
    <h1 id="count">0</h1>
    <button id="increment">+</button>
    <button id="decrement">-</button>
    <button id="reset">Reset</button>
</div>
```

```javascript
let countDisplay = document.querySelector("#count");
let incrementBtn = document.querySelector("#increment");
let decrementBtn = document.querySelector("#decrement");
let resetBtn = document.querySelector("#reset");

let count = 0;

incrementBtn.addEventListener("click", () => {
    count++;
    countDisplay.textContent = count;
});

decrementBtn.addEventListener("click", () => {
    count--;
    countDisplay.textContent = count;
});

resetBtn.addEventListener("click", () => {
    count = 0;
    countDisplay.textContent = count;
});
```

---

## DOM Traversal

Navigate between elements:

```html
<div id="container">
    <h1>Title</h1>
    <p>Paragraph 1</p>
    <p>Paragraph 2</p>
</div>
```

```javascript
let container = document.querySelector("#container");

// Children
console.log(container.children);       // HTMLCollection [h1, p, p]
console.log(container.firstElementChild); // <h1>
console.log(container.lastElementChild);  // <p>Paragraph 2</p>
console.log(container.childElementCount); // 3

// Parent
let p = document.querySelector("p");
console.log(p.parentElement);          // <div id="container">

// Siblings
let h1 = document.querySelector("h1");
console.log(h1.nextElementSibling);    // <p>Paragraph 1</p>
console.log(h1.previousElementSibling); // null (no previous sibling)

let p2 = document.querySelectorAll("p")[1];
console.log(p2.previousElementSibling); // <p>Paragraph 1</p>
```

---

## Performance Tips

### 1. Cache Selectors

```javascript
// Bad (queries DOM 3 times)
document.querySelector("#title").textContent = "Hello";
document.querySelector("#title").style.color = "red";
document.querySelector("#title").classList.add("active");

// Good (queries once, caches result)
let title = document.querySelector("#title");
title.textContent = "Hello";
title.style.color = "red";
title.classList.add("active");
```

### 2. Batch DOM Changes

```javascript
// Bad (causes multiple reflows)
let list = document.querySelector("#list");
for (let i = 0; i < 100; i++) {
    let li = document.createElement("li");
    li.textContent = `Item ${i}`;
    list.appendChild(li); // DOM update each time!
}

// Good (single DOM update)
let list = document.querySelector("#list");
let fragment = document.createDocumentFragment();

for (let i = 0; i < 100; i++) {
    let li = document.createElement("li");
    li.textContent = `Item ${i}`;
    fragment.appendChild(li); // In-memory, no DOM update
}

list.appendChild(fragment); // Single DOM update
```

### 3. Use Event Delegation (Next Lesson)

Instead of adding listeners to many elements, add one to the parent.

---

## Common Mistakes

### Mistake 1: Forgetting to Check if Element Exists

```javascript
// Bad
let element = document.querySelector("#non-existent");
element.textContent = "Hello"; // Error: Cannot read property of null

// Good
let element = document.querySelector("#non-existent");
if (element) {
    element.textContent = "Hello";
}
```

### Mistake 2: Modifying Elements During Iteration

```javascript
// Bad
let items = document.querySelectorAll(".item");
items.forEach((item) => {
    item.remove(); // May cause issues
});

// Good
let items = Array.from(document.querySelectorAll(".item"));
items.forEach((item) => {
    item.remove();
});
```

### Mistake 3: innerHTML with User Input

```javascript
// Dangerous!
let userInput = prompt("Enter your name");
element.innerHTML = `<p>Hello ${userInput}</p>`; // XSS risk!

// Safe
element.textContent = `Hello ${userInput}`;
```

---

## Practice Exercises

### Exercise 1: Change Page Content

Create an HTML page with:
- A heading
- A paragraph
- A button

When the button is clicked:
- Change the heading text
- Change the paragraph text
- Change the heading color
- Add a class to the paragraph

### Exercise 2: Create List from Array

Given this array:
```javascript
let fruits = ["Apple", "Banana", "Cherry", "Date", "Elderberry"];
```

Create a `<ul>` and add each fruit as an `<li>`.

### Exercise 3: Remove Items

Create a list of items, each with a "Remove" button. When clicked, remove that item.

### Exercise 4: Toggle Theme

Create a button that toggles between light and dark theme by adding/removing a class on `<body>`.

### Exercise 5: Build a Card

Given this data:
```javascript
let product = {
    name: "Laptop",
    price: 999,
    image: "laptop.jpg",
    description: "High-performance laptop"
};
```

Create a product card with image, name, price, and description.

---

## Quick Reference

### Selecting
```javascript
document.getElementById("id")
document.querySelector("#id")
document.querySelectorAll(".class")
```

### Content
```javascript
element.textContent = "text"
element.innerHTML = "<p>HTML</p>"
```

### Styles
```javascript
element.style.color = "red"
element.style.fontSize = "20px"
```

### Classes
```javascript
element.classList.add("active")
element.classList.remove("active")
element.classList.toggle("active")
element.classList.contains("active")
```

### Attributes
```javascript
element.getAttribute("attr")
element.setAttribute("attr", "value")
element.dataset.id = "123"
```

### Creating
```javascript
document.createElement("div")
element.appendChild(child)
element.append(child1, child2)
element.remove()
```

---

## Key Takeaways

1. **Use querySelector()** for selecting elements
2. **Cache selectors** for better performance
3. **Use classList** instead of style for styling
4. **Use textContent** instead of innerHTML when possible
5. **Always check if element exists** before manipulating
6. **Batch DOM changes** for better performance
7. **Create elements** with createElement(), append with appendChild()

---

## Next Lesson

In Lesson 06, we'll learn about:
- Event handling (click, input, submit, etc.)
- Event listeners
- Event object and properties
- Event delegation
- Preventing default behavior

Time to make your page truly interactive!
