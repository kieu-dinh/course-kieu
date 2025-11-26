# Lesson 03 - Browser & DevTools

## What You'll Learn

- How a browser works
- Using Chrome DevTools
- Watching HTTP traffic live

---

## How a Browser Works

When you visit a website:

```
1. GET HTML  ──────────────────────────────>
   <────────────── index.html ──────────────

2. Browser parses HTML, finds:
   - <link href="style.css">
   - <script src="app.js">
   - <img src="photo.jpg">

3. GET style.css ──────────────────────────>
   <────────────── style.css ──────────────

4. GET app.js ─────────────────────────────>
   <────────────── app.js ─────────────────

5. GET photo.jpg ──────────────────────────>
   <────────────── photo.jpg ──────────────

6. Browser combines everything → displays page
```

One webpage = many HTTP requests!

---

## Opening DevTools

**Shortcut:** `Cmd + Option + I` (or right-click → Inspect)

You'll see a panel with several tabs. The most important:

| Tab | What It Does |
|-----|--------------|
| **Elements** | See/edit HTML & CSS live |
| **Console** | JavaScript errors and logs |
| **Network** | All HTTP requests |
| **Sources** | Debug JavaScript |

---

## The Network Tab - Your Best Friend

This is where you SEE HTTP in action.

### How to Use It

1. Open DevTools (`Cmd + Option + I`)
2. Click the **Network** tab
3. Refresh the page (`Cmd + R`)
4. Watch requests appear!

### What You See

Each row is one HTTP request:

| Column | Meaning |
|--------|---------|
| Name | The file requested |
| Status | 200, 404, etc. |
| Type | HTML, CSS, JS, image... |
| Size | File size |
| Time | How long it took |

### Clicking a Request

Click any request to see details:
- **Headers**: Request and response headers
- **Preview**: The actual content
- **Response**: Raw response data

---

## Practice: Spy on Google

1. Open Chrome
2. Go to `about:blank` (empty page)
3. Open DevTools → Network tab
4. Check "Preserve log" at the top
5. Type `google.com` and press Enter
6. Watch all the requests appear!

**Questions to answer:**
- How many requests were made?
- What was the first request?
- What types of files were loaded?

---

## The Elements Tab

See and edit HTML/CSS in real-time.

### Try It

1. Go to any website
2. Open DevTools → Elements
3. Click the arrow icon (top-left of DevTools)
4. Click any element on the page
5. See its HTML highlighted
6. Double-click to edit it!

Changes are temporary - refresh and they're gone.

---

## The Console Tab

Shows JavaScript errors and lets you run code.

### Try It

1. Open DevTools → Console
2. Type: `console.log("Hello!")`
3. Press Enter
4. See your message!

The Console is where errors appear. If a website breaks, check here.

---

## Useful DevTools Shortcuts

| Shortcut | Action |
|----------|--------|
| `Cmd + Option + I` | Open/close DevTools |
| `Cmd + Shift + C` | Inspect element mode |
| `Cmd + R` | Refresh page |
| `Cmd + Shift + R` | Hard refresh (clear cache) |

---

## Quick Quiz

1. How do you open DevTools on Mac?
2. Which tab shows HTTP requests?
3. One webpage makes how many HTTP requests?
4. Where do JavaScript errors appear?

<details>
<summary>Answers</summary>

1. Cmd + Option + I
2. Network tab
3. Many! (HTML, CSS, JS, images...)
4. Console tab

</details>

---

## Next Lesson

[Lesson 04: Frontend vs Backend](./04-frontend-backend.md)
