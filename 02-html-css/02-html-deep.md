# Lesson 02 - HTML Deep Dive

## Forms

Forms collect user input and send it to a server.

```html
<form action="/submit" method="POST">
  <label for="name">Name:</label>
  <input type="text" id="name" name="name" placeholder="Your name">

  <label for="email">Email:</label>
  <input type="email" id="email" name="email">

  <label for="message">Message:</label>
  <textarea id="message" name="message"></textarea>

  <button type="submit">Send</button>
</form>
```

### Input Types

```html
<input type="text">       <!-- Plain text -->
<input type="email">      <!-- Email validation -->
<input type="password">   <!-- Hidden characters -->
<input type="number">     <!-- Numbers only -->
<input type="date">       <!-- Date picker -->
<input type="checkbox">   <!-- Checkbox -->
<input type="radio">      <!-- Radio button -->
<input type="file">       <!-- File upload -->
```

### Form Attributes

| Attribute | Purpose |
|-----------|---------|
| `action` | Where to send data |
| `method` | GET or POST |
| `name` | Field identifier (sent to server) |
| `placeholder` | Hint text |
| `required` | Must be filled |
| `disabled` | Can't be edited |

### Complete Form Example

```html
<form action="/register" method="POST">
  <div>
    <label for="username">Username:</label>
    <input type="text" id="username" name="username" required>
  </div>

  <div>
    <label for="email">Email:</label>
    <input type="email" id="email" name="email" required>
  </div>

  <div>
    <label for="password">Password:</label>
    <input type="password" id="password" name="password" required>
  </div>

  <div>
    <label for="country">Country:</label>
    <select id="country" name="country">
      <option value="">Select...</option>
      <option value="vn">Vietnam</option>
      <option value="fr">France</option>
      <option value="us">USA</option>
    </select>
  </div>

  <div>
    <input type="checkbox" id="terms" name="terms" required>
    <label for="terms">I accept the terms</label>
  </div>

  <button type="submit">Register</button>
</form>
```

---

## Tables

```html
<table>
  <thead>
    <tr>
      <th>Name</th>
      <th>Age</th>
      <th>City</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>Kieu</td>
      <td>28</td>
      <td>Paris</td>
    </tr>
    <tr>
      <td>John</td>
      <td>32</td>
      <td>London</td>
    </tr>
  </tbody>
</table>
```

| Tag | Purpose |
|-----|---------|
| `<table>` | Table container |
| `<thead>` | Header section |
| `<tbody>` | Body section |
| `<tr>` | Table row |
| `<th>` | Header cell |
| `<td>` | Data cell |

---

## Semantic HTML

Semantic tags describe their meaning:

```html
<!-- Bad: Just divs -->
<div class="header">...</div>
<div class="nav">...</div>
<div class="main">...</div>
<div class="footer">...</div>

<!-- Good: Semantic -->
<header>...</header>
<nav>...</nav>
<main>...</main>
<footer>...</footer>
```

### Semantic Tags

```html
<header>    <!-- Page/section header -->
<nav>       <!-- Navigation links -->
<main>      <!-- Main content (only one per page) -->
<article>   <!-- Self-contained content -->
<section>   <!-- Thematic grouping -->
<aside>     <!-- Sidebar content -->
<footer>    <!-- Page/section footer -->
```

### Why Use Semantic HTML?

1. **Accessibility** - Screen readers understand the structure
2. **SEO** - Search engines understand content better
3. **Readability** - Code is clearer to read

### Complete Page Structure

```html
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Website</title>
</head>
<body>
  <header>
    <nav>
      <a href="/">Home</a>
      <a href="/about">About</a>
      <a href="/contact">Contact</a>
    </nav>
  </header>

  <main>
    <article>
      <h1>Welcome to My Site</h1>
      <p>This is the main content.</p>
    </article>

    <aside>
      <h2>Sidebar</h2>
      <p>Related links or info.</p>
    </aside>
  </main>

  <footer>
    <p>&copy; 2024 My Website</p>
  </footer>
</body>
</html>
```

---

## Meta Tags

In `<head>`, meta tags provide information about the page:

```html
<head>
  <meta charset="UTF-8">  <!-- Character encoding -->
  <meta name="viewport" content="width=device-width, initial-scale=1.0">  <!-- Mobile responsive -->
  <meta name="description" content="A description of your page">  <!-- SEO -->
  <title>Page Title</title>
  <link rel="stylesheet" href="style.css">  <!-- CSS file -->
</head>
```

---

## Practice

Create a contact form page with:
- Header with navigation
- A contact form (name, email, subject, message)
- Footer with copyright

Try it yourself before looking at the solution!

---

## Next

[Lesson 03: CSS Basics](./03-css-basics.md)
