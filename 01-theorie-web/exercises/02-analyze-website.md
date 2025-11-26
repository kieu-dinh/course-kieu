# Exercise 02 - Analyze a Website

## Objective

Analyze a website to understand its structure using DevTools.

---

## Part 1: HTML Structure

### Steps

1. Visit `https://news.ycombinator.com` (Hacker News - simple site)
2. Open DevTools: `Cmd + Option + I`
3. Click the **Elements** tab

### Tasks

**Task 1: Find the page title**

Use the Elements tab to find the `<title>` tag.

```
Page title:
```

**Task 2: Find the main content**

Look for the main content container. What HTML tag is it? What class does it have?

```
Tag:
Class:
```

**Task 3: Inspect a link**

1. Click the arrow icon (top-left of DevTools)
2. Click on any article title
3. Look at the HTML

```
What tag is the link?
What is the href attribute?
```

---

## Part 2: CSS Inspection

### Steps

1. In Elements tab, click on an article title
2. Look at the right panel → "Styles"

### Questions

**Q1: What color is the link text?**
```
Your answer:
```

**Q2: What font-size is used?**
```
Your answer:
```

**Q3: Try changing the color!**
- Click on the color value
- Pick a different color
- What happened?

```
Your answer:
```

---

## Part 3: Responsive Design

1. Click the device icon (looks like a phone/tablet) in DevTools
2. Or press `Cmd + Shift + M`

### Tasks

**Task 1: Test mobile view**

Select "iPhone 12 Pro" from the dropdown.

```
How does the site look different on mobile?
```

**Task 2: Test different sizes**

Try iPad, then a desktop resolution.

```
Does this site adapt to different screen sizes? How?
```

---

## Part 4: Performance

1. Go to Network tab
2. Refresh the page
3. Look at the bottom bar

### Questions

**Q1: How many requests total?**
```
Your answer:
```

**Q2: How much data was transferred?**
```
Your answer:
```

**Q3: How long did the page take to load?**
```
Your answer:
```

---

## Bonus: Compare Two Sites

Do the same analysis for `https://apple.com`

| Metric | Hacker News | Apple |
|--------|-------------|-------|
| Total requests | | |
| Data transferred | | |
| Load time | | |

**Why is Apple's site so different?**
```
Your answer:
```

---

## Validation

Share your findings with Claude and ask:
> "I analyzed Hacker News and Apple websites. Here's what I found. Is my analysis correct?"

---

## Completed?

- [ ] I can inspect HTML elements
- [ ] I can view and modify CSS in DevTools
- [ ] I understand responsive design testing
- [ ] I can analyze page performance

Next: [Exercise 03: HTTP Detective](./03-http-detective.md)
