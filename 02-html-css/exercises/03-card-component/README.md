# Exercise 03 - Card Component

## Objective

Build reusable card components using Tailwind CSS.

---

## Setup

Create `cards.html`:

```html
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
  <title>Card Components</title>
</head>
<body class="bg-gray-100 p-8">

  <!-- Your cards go here -->

</body>
</html>
```

---

## Task 1: Simple Card

Create a simple card with:
- White background
- Rounded corners
- Shadow
- Padding
- A title and description

Expected result:
```
┌──────────────────────┐
│  Card Title          │
│  Description text    │
│  goes here...        │
└──────────────────────┘
```

### Your Code

Write your card HTML here (in the file), using only Tailwind classes.

---

## Task 2: Product Card

Create a product card with:
- Image at top
- Product name
- Price
- "Add to Cart" button

Expected result:
```
┌──────────────────────┐
│  [    Image      ]   │
├──────────────────────┤
│  Product Name        │
│  $29.99              │
│  [  Add to Cart  ]   │
└──────────────────────┘
```

Use this placeholder image:
```
https://via.placeholder.com/400x200
```

---

## Task 3: Profile Card

Create a profile card with:
- Circular avatar image
- Name (bold)
- Job title (gray, smaller)
- Short bio
- Social links row

Expected result:
```
┌──────────────────────┐
│       (avatar)       │
│     John Smith       │
│    Web Developer     │
│                      │
│  I build amazing...  │
│                      │
│  [tw] [gh] [li]      │
└──────────────────────┘
```

For avatar, use:
```
https://via.placeholder.com/100
```

---

## Task 4: Card Grid

Create a grid of 6 simple cards that:
- Shows 1 column on mobile
- Shows 2 columns on tablet
- Shows 3 columns on desktop

Hint: Use `grid`, `grid-cols-1`, `md:grid-cols-2`, `lg:grid-cols-3`, `gap-4`

---

## Checklist

- [ ] Simple card has shadow and rounded corners
- [ ] Product card has image, price, and button
- [ ] Profile card has circular avatar
- [ ] Grid is responsive (test with DevTools)
- [ ] Button has hover effect

---

## Hints

**Circular image:**
```html
<img class="w-24 h-24 rounded-full object-cover" src="...">
```

**Button:**
```html
<button class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
  Click
</button>
```

**Center content:**
```html
<div class="flex flex-col items-center text-center">
```

---

## Validation

1. Resize browser window
2. Cards should reflow correctly
3. All hover effects should work

Ask Claude:
> "Review my Tailwind card component. Is there a better way to do this?"

---

## Next

[Exercise 04: Landing Page](./04-landing-page.md)
