# Exercise 04 - Landing Page

## Objective

Build a complete, responsive landing page using HTML and Tailwind CSS.

---

## The Challenge

Create a landing page for a fictional product/service with these sections:

1. **Navigation** - Logo + links (hamburger icon on mobile)
2. **Hero** - Big headline, subtitle, CTA button
3. **Features** - 3 feature cards in a grid
4. **Testimonials** - 2-3 customer quotes
5. **CTA Section** - Final call to action
6. **Footer** - Links and copyright

---

## Requirements

- Fully responsive (mobile, tablet, desktop)
- Use only Tailwind CSS (no custom CSS)
- Professional-looking design
- Hover effects on buttons and links

---

## Starter Template

```html
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
  <title>My Landing Page</title>
</head>
<body class="bg-white">

  <!-- Navigation -->
  <nav>
    <!-- Your code -->
  </nav>

  <!-- Hero Section -->
  <section>
    <!-- Your code -->
  </section>

  <!-- Features Section -->
  <section>
    <!-- Your code -->
  </section>

  <!-- Testimonials Section -->
  <section>
    <!-- Your code -->
  </section>

  <!-- CTA Section -->
  <section>
    <!-- Your code -->
  </section>

  <!-- Footer -->
  <footer>
    <!-- Your code -->
  </footer>

</body>
</html>
```

---

## Section Guidelines

### Navigation

```
Desktop: [Logo]                    [Home] [Features] [Pricing] [Contact]
Mobile:  [Logo]                                                     [☰]
```

Key classes: `flex`, `justify-between`, `items-center`, `hidden md:flex`

### Hero

```
┌──────────────────────────────────────────────────┐
│                                                  │
│           Your Amazing Product                   │
│   The best solution for your everyday needs      │
│                                                  │
│              [ Get Started ]                     │
│                                                  │
└──────────────────────────────────────────────────┘
```

Key classes: `text-center`, `py-20`, `text-5xl`, `font-bold`

### Features Grid

```
┌─────────────┐  ┌─────────────┐  ┌─────────────┐
│     ⚡       │  │     🔒       │  │     📱       │
│   Fast      │  │   Secure    │  │  Responsive │
│  Speed...   │  │  Safe...    │  │  Any device │
└─────────────┘  └─────────────┘  └─────────────┘
```

Key classes: `grid`, `grid-cols-1 md:grid-cols-3`, `gap-8`

### Testimonials

```
┌─────────────────────────────────────────┐
│  "This product changed my life!"        │
│                        - Customer Name  │
└─────────────────────────────────────────┘
```

Key classes: `bg-gray-50`, `italic`, `text-gray-600`

### CTA Section

```
┌──────────────────────────────────────────────────┐
│                                                  │
│          Ready to get started?                   │
│          [ Start Free Trial ]                    │
│                                                  │
└──────────────────────────────────────────────────┘
```

Key classes: `bg-blue-500`, `text-white`, `text-center`

### Footer

```
┌──────────────────────────────────────────────────┐
│  Company    Resources    Contact                 │
│  About      Docs         Email                   │
│  Team       Blog         Twitter                 │
│                                                  │
│           © 2024 Company. All rights reserved.   │
└──────────────────────────────────────────────────┘
```

Key classes: `bg-gray-800`, `text-white`, `grid grid-cols-1 md:grid-cols-3`

---

## Checklist

- [ ] Navigation works on mobile and desktop
- [ ] Hero section is centered and impactful
- [ ] Features show 1 col on mobile, 3 on desktop
- [ ] Testimonials look professional
- [ ] CTA section stands out
- [ ] Footer has multiple columns on desktop
- [ ] All buttons have hover effects
- [ ] Page looks good at 375px, 768px, and 1200px

---

## Testing

1. Open in Chrome
2. Open DevTools → Toggle device toolbar
3. Test on iPhone, iPad, and Desktop sizes
4. Check all spacing and alignment

---

## Validation

When complete, ask Claude:
> "Review my landing page code. What could I improve for better design or responsiveness?"

---

## Congratulations!

You've completed Module 02! You can now:
- Write HTML from scratch
- Style with CSS
- Use Tailwind CSS efficiently
- Build responsive layouts

**Next Module:** [03 - Git & GitHub](../../03-git-github/)
