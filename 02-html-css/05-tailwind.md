# Lesson 05 - Tailwind CSS

## What is Tailwind?

Tailwind is a **utility-first** CSS framework. Instead of writing CSS, you use predefined classes.

```html
<!-- Without Tailwind -->
<div class="card">Hello</div>
<style>
  .card {
    padding: 20px;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  }
</style>

<!-- With Tailwind -->
<div class="p-5 bg-white rounded-lg shadow">Hello</div>
```

No CSS file needed!

---

## Setup (Quick Start)

For learning, use the CDN (in production, use proper install):

```html
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
  <title>Tailwind Practice</title>
</head>
<body>
  <h1 class="text-3xl font-bold text-blue-500">Hello Tailwind!</h1>
</body>
</html>
```

---

## Core Concepts

### Spacing (padding & margin)

```
p-{size}  = padding all sides
px-{size} = padding left + right
py-{size} = padding top + bottom
pt, pr, pb, pl = padding single side

m-{size}  = margin (same pattern)
```

Sizes: 0, 1, 2, 3, 4, 5, 6, 8, 10, 12, 16, 20, 24...

```html
<div class="p-4">16px padding all sides</div>
<div class="px-4 py-2">Horizontal 16px, vertical 8px</div>
<div class="mt-8">Margin top 32px</div>
```

### Colors

Format: `{property}-{color}-{shade}`

```html
<p class="text-blue-500">Blue text</p>
<p class="text-red-600">Darker red text</p>
<div class="bg-gray-100">Light gray background</div>
<div class="bg-green-500">Green background</div>
<div class="border border-gray-300">Gray border</div>
```

Shades: 50, 100, 200, 300, 400, 500, 600, 700, 800, 900

### Typography

```html
<p class="text-sm">Small</p>
<p class="text-base">Normal (default)</p>
<p class="text-lg">Large</p>
<p class="text-xl">Extra large</p>
<p class="text-2xl">2x large</p>
<p class="text-3xl">3x large</p>

<p class="font-light">Light</p>
<p class="font-normal">Normal</p>
<p class="font-medium">Medium</p>
<p class="font-semibold">Semibold</p>
<p class="font-bold">Bold</p>

<p class="text-center">Centered</p>
<p class="text-right">Right aligned</p>
```

### Width & Height

```html
<div class="w-full">100% width</div>
<div class="w-1/2">50% width</div>
<div class="w-64">256px width</div>
<div class="max-w-md">Max width medium</div>

<div class="h-screen">100vh height</div>
<div class="h-64">256px height</div>
```

### Flexbox

```html
<div class="flex">Enable flex</div>
<div class="flex justify-center">Center horizontal</div>
<div class="flex justify-between">Space between</div>
<div class="flex items-center">Center vertical</div>
<div class="flex flex-col">Vertical direction</div>
<div class="flex gap-4">Gap between items</div>
```

### Grid

```html
<div class="grid grid-cols-3 gap-4">3 columns</div>
<div class="grid grid-cols-2 md:grid-cols-4 gap-4">Responsive</div>
```

### Borders & Rounded

```html
<div class="border">1px border</div>
<div class="border-2">2px border</div>
<div class="border-gray-300">Gray border</div>
<div class="rounded">Slightly rounded</div>
<div class="rounded-lg">More rounded</div>
<div class="rounded-full">Circle/pill</div>
```

### Shadows

```html
<div class="shadow">Small shadow</div>
<div class="shadow-md">Medium shadow</div>
<div class="shadow-lg">Large shadow</div>
<div class="shadow-xl">Extra large shadow</div>
```

---

## Common Patterns

### Card

```html
<div class="bg-white rounded-lg shadow-md p-6">
  <h2 class="text-xl font-semibold mb-2">Card Title</h2>
  <p class="text-gray-600">Card content goes here.</p>
</div>
```

### Button

```html
<button class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
  Click Me
</button>
```

### Navbar

```html
<nav class="bg-white shadow px-6 py-4 flex justify-between items-center">
  <div class="text-xl font-bold">Logo</div>
  <div class="flex gap-6">
    <a href="#" class="text-gray-600 hover:text-gray-900">Home</a>
    <a href="#" class="text-gray-600 hover:text-gray-900">About</a>
    <a href="#" class="text-gray-600 hover:text-gray-900">Contact</a>
  </div>
</nav>
```

### Centered Content

```html
<div class="min-h-screen flex items-center justify-center">
  <div class="text-center">
    <h1 class="text-4xl font-bold">Centered!</h1>
  </div>
</div>
```

---

## Hover, Focus, Active

Add states with prefixes:

```html
<button class="bg-blue-500 hover:bg-blue-600 focus:ring-2">
  Hover me
</button>

<input class="border focus:border-blue-500 focus:ring-2">
```

---

## Responsive Design

Prefix classes with breakpoint:

| Prefix | Screen Width |
|--------|-------------|
| `sm:` | 640px+ |
| `md:` | 768px+ |
| `lg:` | 1024px+ |
| `xl:` | 1280px+ |

```html
<!-- 1 column on mobile, 2 on tablet, 3 on desktop -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
  <div>Card 1</div>
  <div>Card 2</div>
  <div>Card 3</div>
</div>

<!-- Hidden on mobile, visible on desktop -->
<div class="hidden lg:block">Desktop only</div>

<!-- Different text size per screen -->
<h1 class="text-2xl md:text-4xl lg:text-6xl">Responsive Text</h1>
```

---

## Cheat Sheet

| CSS | Tailwind |
|-----|----------|
| `padding: 16px` | `p-4` |
| `margin: 0 auto` | `mx-auto` |
| `display: flex` | `flex` |
| `justify-content: center` | `justify-center` |
| `align-items: center` | `items-center` |
| `background: white` | `bg-white` |
| `color: gray` | `text-gray-500` |
| `font-weight: bold` | `font-bold` |
| `border-radius: 8px` | `rounded-lg` |
| `box-shadow` | `shadow-md` |

---

## Practice

Recreate this card using only Tailwind classes:

```
┌──────────────────────────┐
│  [image placeholder]     │
├──────────────────────────┤
│  Product Name            │
│  $29.99                  │
│  [Add to Cart Button]    │
└──────────────────────────┘
```

---

## Next

[Lesson 06: Responsive Design](./06-responsive.md)
