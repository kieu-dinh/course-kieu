# Lesson 03 - CSS Basics

## What is CSS?

**CSS** (Cascading Style Sheets) controls how HTML looks.

```
HTML = Structure (what)
CSS = Style (how it looks)
```

---

## Adding CSS to HTML

### Method 1: Inline (avoid)

```html
<p style="color: red;">Red text</p>
```

### Method 2: Internal (ok for learning)

```html
<head>
  <style>
    p { color: red; }
  </style>
</head>
```

### Method 3: External (best)

```html
<!-- In index.html -->
<head>
  <link rel="stylesheet" href="style.css">
</head>
```

```css
/* In style.css */
p { color: red; }
```

---

## CSS Syntax

```css
selector {
  property: value;
  property: value;
}
```

Example:
```css
h1 {
  color: blue;
  font-size: 32px;
}
```

---

## Selectors

### Element Selector

```css
p { color: blue; }      /* All paragraphs */
h1 { color: red; }      /* All h1 */
```

### Class Selector

```html
<p class="intro">Hello</p>
```
```css
.intro { color: green; }   /* Elements with class="intro" */
```

### ID Selector

```html
<p id="special">Hello</p>
```
```css
#special { color: purple; }   /* Element with id="special" */
```

### Combining

```css
div p { }           /* p inside div */
div > p { }         /* p directly inside div */
h1, h2, h3 { }      /* h1, h2, and h3 */
p.intro { }         /* p with class intro */
```

---

## Common Properties

### Colors

```css
color: red;                  /* Text color */
color: #ff0000;              /* Hex */
color: rgb(255, 0, 0);       /* RGB */
background-color: yellow;   /* Background */
```

### Text

```css
font-size: 16px;
font-weight: bold;         /* or 400, 700 */
font-family: Arial, sans-serif;
text-align: center;        /* left, right, center, justify */
text-decoration: underline;
line-height: 1.5;
```

### Sizing

```css
width: 100px;
width: 50%;              /* 50% of parent */
height: 200px;
max-width: 800px;
min-height: 100vh;       /* vh = viewport height */
```

---

## The Box Model

Every element is a box:

```
┌─────────────────────────────────────┐
│             MARGIN                  │  ← Space outside
│  ┌───────────────────────────────┐  │
│  │          BORDER               │  │  ← Border
│  │  ┌─────────────────────────┐  │  │
│  │  │        PADDING          │  │  │  ← Space inside
│  │  │  ┌───────────────────┐  │  │  │
│  │  │  │     CONTENT       │  │  │  │  ← Your text/image
│  │  │  └───────────────────┘  │  │  │
│  │  └─────────────────────────┘  │  │
│  └───────────────────────────────┘  │
└─────────────────────────────────────┘
```

```css
.box {
  /* Content */
  width: 200px;
  height: 100px;

  /* Padding (inside) */
  padding: 20px;           /* All sides */
  padding: 10px 20px;      /* Top/bottom, left/right */
  padding-top: 10px;

  /* Border */
  border: 1px solid black;
  border-radius: 8px;      /* Rounded corners */

  /* Margin (outside) */
  margin: 20px;
  margin: 0 auto;          /* Center horizontally */
}
```

### Box Sizing

By default, width/height = content only. This is confusing.

Fix it:

```css
* {
  box-sizing: border-box;  /* width/height includes padding + border */
}
```

Always add this to your CSS!

---

## Display Property

```css
display: block;        /* Takes full width, new line */
display: inline;       /* Only takes needed space, same line */
display: inline-block; /* Inline but respects width/height */
display: none;         /* Hidden completely */
```

| Element | Default |
|---------|---------|
| `div`, `p`, `h1` | block |
| `span`, `a`, `strong` | inline |

---

## Basic Reset

Start every CSS file with:

```css
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: system-ui, sans-serif;
  line-height: 1.5;
}
```

---

## Practice

Create `style.css` that:
1. Sets a light gray background on body
2. Centers all h1 tags
3. Makes links blue with no underline
4. Adds padding to paragraphs
5. Creates a `.card` class with white background, padding, and border-radius

---

## Next

[Lesson 04: CSS Layout](./04-css-layout.md)
