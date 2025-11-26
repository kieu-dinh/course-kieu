# Lesson 04 - CSS Layout

## The Problem

Without layout tools, elements stack vertically:

```
┌──────────────────┐
│     Box 1        │
├──────────────────┤
│     Box 2        │
├──────────────────┤
│     Box 3        │
└──────────────────┘
```

We often want:

```
┌─────┐ ┌─────┐ ┌─────┐
│  1  │ │  2  │ │  3  │
└─────┘ └─────┘ └─────┘
```

Solution: **Flexbox** and **Grid**

---

## Flexbox

Flexbox arranges items in one direction (row or column).

### Enable Flexbox

```css
.container {
  display: flex;
}
```

That's it! Children now sit side by side.

### Direction

```css
.container {
  display: flex;
  flex-direction: row;      /* Default: horizontal */
  flex-direction: column;   /* Vertical */
}
```

### Justify Content (main axis)

```css
.container {
  display: flex;
  justify-content: flex-start;   /* Left (default) */
  justify-content: flex-end;     /* Right */
  justify-content: center;       /* Center */
  justify-content: space-between;/* Spread out */
  justify-content: space-around; /* Even spacing */
}
```

Visual:
```
flex-start:     [1][2][3]
flex-end:                  [1][2][3]
center:            [1][2][3]
space-between:  [1]    [2]    [3]
space-around:    [1]   [2]   [3]
```

### Align Items (cross axis)

```css
.container {
  display: flex;
  align-items: stretch;    /* Fill height (default) */
  align-items: flex-start; /* Top */
  align-items: flex-end;   /* Bottom */
  align-items: center;     /* Center vertically */
}
```

### Gap

```css
.container {
  display: flex;
  gap: 20px;  /* Space between items */
}
```

### Flex Wrap

```css
.container {
  display: flex;
  flex-wrap: wrap;  /* Items wrap to next line if no space */
}
```

### Complete Example

```html
<div class="navbar">
  <div class="logo">Logo</div>
  <nav class="links">
    <a href="#">Home</a>
    <a href="#">About</a>
    <a href="#">Contact</a>
  </nav>
</div>
```

```css
.navbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px;
}

.links {
  display: flex;
  gap: 20px;
}
```

---

## Center Anything

The magic combo:

```css
.center-me {
  display: flex;
  justify-content: center;
  align-items: center;
}
```

---

## CSS Grid

Grid is for two-dimensional layouts (rows AND columns).

### Enable Grid

```css
.container {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr;  /* 3 equal columns */
  gap: 20px;
}
```

`1fr` = 1 fraction of available space

### Column Sizes

```css
grid-template-columns: 200px 1fr;        /* Fixed + flexible */
grid-template-columns: 1fr 2fr 1fr;      /* 1:2:1 ratio */
grid-template-columns: repeat(3, 1fr);   /* Same as 1fr 1fr 1fr */
grid-template-columns: repeat(4, 200px); /* 4 columns of 200px */
```

### Responsive Grid

```css
.container {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 20px;
}
```

This creates as many 250px+ columns as fit!

### Grid Example: Card Layout

```html
<div class="card-grid">
  <div class="card">Card 1</div>
  <div class="card">Card 2</div>
  <div class="card">Card 3</div>
  <div class="card">Card 4</div>
  <div class="card">Card 5</div>
  <div class="card">Card 6</div>
</div>
```

```css
.card-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 20px;
}

.card {
  background: white;
  padding: 20px;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
```

---

## Flexbox vs Grid

| Use Case | Tool |
|----------|------|
| Navigation bar | Flexbox |
| Centering | Flexbox |
| Card grid | Grid |
| Complex layouts | Grid |
| One direction | Flexbox |
| Two directions | Grid |

---

## Position

```css
position: static;    /* Default */
position: relative;  /* Relative to normal position */
position: absolute;  /* Relative to positioned parent */
position: fixed;     /* Relative to viewport */
position: sticky;    /* Sticks when scrolling */
```

### Fixed Header Example

```css
.header {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  background: white;
  z-index: 100;  /* Above other elements */
}

body {
  padding-top: 60px;  /* So content isn't hidden */
}
```

---

## Practice

Create a page layout with:
1. Fixed header with logo left, nav links right
2. Main content with 3-column card grid
3. Footer at the bottom

---

## Next

[Lesson 05: Tailwind CSS](./05-tailwind.md)
