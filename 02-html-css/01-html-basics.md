# Lesson 01 - HTML Basics

## What is HTML?

**HTML** (HyperText Markup Language) defines the **structure** of a webpage.

It uses **tags** to mark up content:

```html
<tagname>content</tagname>
```

---

## Your First HTML File

Create `index.html`:

```html
<!DOCTYPE html>
<html>
  <head>
    <title>My First Page</title>
  </head>
  <body>
    <h1>Hello World!</h1>
    <p>This is my first webpage.</p>
  </body>
</html>
```

Open it in Chrome (drag the file into browser).

---

## HTML Structure

```html
<!DOCTYPE html>        <!-- Tells browser this is HTML5 -->
<html>                 <!-- Root element -->
  <head>               <!-- Metadata (not visible) -->
    <title>...</title> <!-- Browser tab title -->
  </head>
  <body>               <!-- Visible content -->
    ...
  </body>
</html>
```

---

## Essential Tags

### Headings

```html
<h1>Main Title</h1>      <!-- Biggest -->
<h2>Section Title</h2>
<h3>Subsection</h3>
<h4>Smaller heading</h4>
<h5>Even smaller</h5>
<h6>Smallest</h6>        <!-- Smallest -->
```

Use only ONE `<h1>` per page!

### Paragraphs & Text

```html
<p>This is a paragraph.</p>
<strong>Bold text</strong>
<em>Italic text</em>
<br>  <!-- Line break (no closing tag) -->
```

### Links

```html
<a href="https://google.com">Go to Google</a>
<a href="about.html">About page</a>  <!-- Relative link -->
<a href="#section1">Jump to section</a>  <!-- Same page -->
```

### Images

```html
<img src="photo.jpg" alt="Description of photo">
<img src="https://example.com/image.png" alt="Remote image">
```

- `src` = image location
- `alt` = description (important for accessibility)

### Lists

```html
<!-- Unordered (bullets) -->
<ul>
  <li>Item 1</li>
  <li>Item 2</li>
</ul>

<!-- Ordered (numbers) -->
<ol>
  <li>First</li>
  <li>Second</li>
</ol>
```

### Divisions

```html
<div>
  This is a container.
  It groups other elements together.
</div>

<span>Inline container for small text pieces</span>
```

---

## Attributes

Tags can have attributes that add extra information:

```html
<tag attribute="value">content</tag>
```

Common attributes:

| Attribute | Purpose | Example |
|-----------|---------|---------|
| `id` | Unique identifier | `<div id="header">` |
| `class` | CSS styling hook | `<p class="intro">` |
| `href` | Link destination | `<a href="...">` |
| `src` | Image/script source | `<img src="...">` |
| `alt` | Image description | `<img alt="...">` |

---

## Comments

```html
<!-- This is a comment. Browser ignores it. -->
<p>This shows.</p>
<!-- <p>This is hidden.</p> -->
```

---

## Practice

Create `practice.html`:

```html
<!DOCTYPE html>
<html>
  <head>
    <title>About Me</title>
  </head>
  <body>
    <h1>Your Name</h1>
    <p>A short introduction about yourself.</p>

    <h2>My Hobbies</h2>
    <ul>
      <li>Hobby 1</li>
      <li>Hobby 2</li>
      <li>Hobby 3</li>
    </ul>

    <h2>Contact</h2>
    <p>Email me at <a href="mailto:you@email.com">you@email.com</a></p>
  </body>
</html>
```

Customize it with your real information!

---

## Quick Reference

| Tag | Purpose |
|-----|---------|
| `<h1>` - `<h6>` | Headings |
| `<p>` | Paragraph |
| `<a>` | Link |
| `<img>` | Image |
| `<ul>`, `<ol>`, `<li>` | Lists |
| `<div>` | Block container |
| `<span>` | Inline container |
| `<strong>` | Bold |
| `<em>` | Italic |

---

## Next

[Lesson 02: HTML Deep Dive](./02-html-deep.md)
