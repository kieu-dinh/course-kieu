# Exercise 02 - Styled Page

## Objective

Add CSS styling to your About Me page.

---

## Task

Take your `about-me.html` from Exercise 01 and add CSS styling.

### Requirements

1. Create a separate `style.css` file
2. Link it to your HTML
3. Add the following styles:

**Body:**
- Light background color
- Nice font family (like `system-ui, sans-serif`)
- Max width of 800px, centered

**Headings:**
- Different color for h1 vs h2
- Custom font sizes

**Links:**
- Remove underline
- Custom color
- Different color on hover

**Image:**
- Border radius (rounded)
- Max width 100%

**Lists:**
- Custom spacing between items

---

## Steps

1. Create `style.css` in the same folder as your HTML
2. Add the CSS reset at the top:
```css
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}
```
3. Link CSS in your HTML `<head>`:
```html
<link rel="stylesheet" href="style.css">
```
4. Style each element
5. Refresh browser to see changes

---

## Starter CSS

```css
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  /* Add your styles */
}

h1 {
  /* Add your styles */
}

h2 {
  /* Add your styles */
}

p {
  /* Add your styles */
}

a {
  /* Add your styles */
}

a:hover {
  /* Add your styles */
}

ul, ol {
  /* Add your styles */
}

img {
  /* Add your styles */
}
```

---

## Checklist

- [ ] CSS file is linked correctly
- [ ] Body has max-width and is centered (`margin: 0 auto`)
- [ ] Colors are applied
- [ ] Font is changed from default
- [ ] Links have hover effect
- [ ] Image is rounded
- [ ] Spacing looks good

---

## Validation

1. Change a color in CSS
2. Save the file
3. Refresh browser
4. Color should change

If it doesn't change, check:
- Is the CSS file linked correctly?
- Is the file saved?
- Try hard refresh: `Cmd + Shift + R`

---

## Bonus Challenge

Add a `.card` class that you can apply to sections:
- White background
- Padding
- Border radius
- Box shadow

Apply it to your hobbies and goals sections.

---

## Next

[Exercise 03: Card Component](./03-card-component.md)
