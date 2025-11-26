# Lesson 06 - Responsive Design

## What is Responsive Design?

A responsive website adapts to any screen size:

```
Desktop (1200px+)     Tablet (768px)      Mobile (375px)
┌────────────────┐    ┌──────────┐        ┌─────┐
│  [Logo] [Nav]  │    │  [Logo]  │        │[☰]  │
├────┬─────┬─────┤    │  [Nav]   │        ├─────┤
│    │     │     │    ├────┬─────┤        │     │
│ 1  │  2  │  3  │    │ 1  │  2  │        │  1  │
│    │     │     │    │    │     │        ├─────┤
└────┴─────┴─────┘    ├────┴─────┤        │  2  │
                      │    3     │        ├─────┤
                      └──────────┘        │  3  │
                                          └─────┘
```

---

## Mobile-First Approach

Start with mobile design, then add complexity for larger screens.

### With Tailwind

```html
<!-- Mobile first: 1 column, then 2, then 3 -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
  <div>Item 1</div>
  <div>Item 2</div>
  <div>Item 3</div>
</div>
```

Breakpoints:
- No prefix = mobile (default)
- `md:` = 768px+
- `lg:` = 1024px+

---

## The Viewport Meta Tag

Always include this in `<head>`:

```html
<meta name="viewport" content="width=device-width, initial-scale=1.0">
```

Without it, mobile browsers zoom out to show desktop view.

---

## Responsive Patterns

### Responsive Navigation

```html
<nav class="bg-white shadow">
  <div class="max-w-6xl mx-auto px-4 py-3 flex justify-between items-center">
    <div class="text-xl font-bold">Logo</div>

    <!-- Mobile: hamburger button -->
    <button class="md:hidden">☰</button>

    <!-- Desktop: nav links -->
    <div class="hidden md:flex gap-6">
      <a href="#">Home</a>
      <a href="#">About</a>
      <a href="#">Contact</a>
    </div>
  </div>
</nav>
```

### Responsive Text

```html
<h1 class="text-2xl md:text-4xl lg:text-5xl font-bold">
  Responsive Heading
</h1>

<p class="text-sm md:text-base lg:text-lg">
  Text that scales with screen size.
</p>
```

### Responsive Spacing

```html
<section class="px-4 py-8 md:px-8 md:py-16 lg:px-16 lg:py-24">
  More padding on larger screens
</section>
```

### Responsive Grid

```html
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
  <div class="bg-gray-100 p-4">1</div>
  <div class="bg-gray-100 p-4">2</div>
  <div class="bg-gray-100 p-4">3</div>
  <div class="bg-gray-100 p-4">4</div>
</div>
```

### Hide/Show Elements

```html
<!-- Only visible on mobile -->
<div class="block md:hidden">Mobile only</div>

<!-- Only visible on desktop -->
<div class="hidden md:block">Desktop only</div>
```

---

## Container

Center content with max-width:

```html
<div class="max-w-6xl mx-auto px-4">
  Content centered with max width
</div>
```

Or use Tailwind's container:

```html
<div class="container mx-auto px-4">
  Auto max-width based on breakpoint
</div>
```

---

## Testing Responsive Design

1. Open DevTools (`Cmd + Option + I`)
2. Click device icon or `Cmd + Shift + M`
3. Select different devices or drag to resize

Always test on:
- Mobile (375px - iPhone)
- Tablet (768px - iPad)
- Desktop (1024px+)

---

## Complete Responsive Page

```html
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
  <title>Responsive Page</title>
</head>
<body class="bg-gray-50">

  <!-- Header -->
  <header class="bg-white shadow">
    <div class="max-w-6xl mx-auto px-4 py-4 flex justify-between items-center">
      <h1 class="text-xl font-bold">MySite</h1>
      <nav class="hidden md:flex gap-6">
        <a href="#" class="text-gray-600 hover:text-gray-900">Home</a>
        <a href="#" class="text-gray-600 hover:text-gray-900">About</a>
        <a href="#" class="text-gray-600 hover:text-gray-900">Contact</a>
      </nav>
      <button class="md:hidden text-2xl">☰</button>
    </div>
  </header>

  <!-- Hero -->
  <section class="py-12 md:py-24 px-4">
    <div class="max-w-4xl mx-auto text-center">
      <h2 class="text-3xl md:text-5xl font-bold mb-4">
        Welcome to My Site
      </h2>
      <p class="text-gray-600 text-lg md:text-xl mb-8">
        A beautiful responsive website.
      </p>
      <button class="bg-blue-500 text-white px-6 py-3 rounded-lg hover:bg-blue-600">
        Get Started
      </button>
    </div>
  </section>

  <!-- Features -->
  <section class="py-12 px-4 bg-white">
    <div class="max-w-6xl mx-auto">
      <h3 class="text-2xl md:text-3xl font-bold text-center mb-8">Features</h3>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <div class="text-center p-6">
          <div class="text-4xl mb-4">⚡</div>
          <h4 class="font-semibold mb-2">Fast</h4>
          <p class="text-gray-600">Lightning quick performance.</p>
        </div>
        <div class="text-center p-6">
          <div class="text-4xl mb-4">🔒</div>
          <h4 class="font-semibold mb-2">Secure</h4>
          <p class="text-gray-600">Enterprise-grade security.</p>
        </div>
        <div class="text-center p-6">
          <div class="text-4xl mb-4">📱</div>
          <h4 class="font-semibold mb-2">Responsive</h4>
          <p class="text-gray-600">Works on any device.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer class="bg-gray-800 text-white py-8 px-4">
    <div class="max-w-6xl mx-auto text-center">
      <p>&copy; 2024 MySite. All rights reserved.</p>
    </div>
  </footer>

</body>
</html>
```

---

## Practice

Create a responsive landing page with:
1. Header with logo and navigation (hamburger on mobile)
2. Hero section with big title and button
3. 3-column features section (1 column on mobile)
4. Footer

Test it on mobile, tablet, and desktop!

---

## Module Complete!

Go to: [Exercises](./exercises/)
