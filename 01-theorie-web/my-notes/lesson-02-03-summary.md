# Lesson 02 & 03 Summary - HTTP Protocol & Browser DevTools

## Lesson 02: HTTP Protocol

### What is HTTP?
- The "language" browsers and servers use to communicate
- Browser sends **Request** → Server sends **Response**

### HTTP Methods (What You Want to Do)

| Method | Purpose | Example |
|--------|---------|---------|
| **GET** | Read/retrieve data | View a webpage |
| **POST** | Send/create data | Submit a form, login |
| **PUT** | Update data | Edit your profile |
| **DELETE** | Remove data | Delete a comment |

### Status Codes (What Happened)

| Code | Meaning | Remember |
|------|---------|----------|
| **200** | OK - Success | Everything worked! |
| **404** | Not Found | Wrong URL, page doesn't exist |
| **401** | Unauthorized | You need to log in |
| **403** | Forbidden | Logged in, but not allowed |
| **500** | Server Error | Server's problem, not yours |

**Quick tip:**
- **2xx** = Success (good!)
- **4xx** = YOUR mistake
- **5xx** = SERVER's mistake

### Request Structure
```
1. Method + URL    →  GET /products/123
2. Headers         →  Extra info (who you are, what you accept)
3. Empty line
4. Body            →  Data you're sending (only for POST/PUT)
```

### Response Structure
```
1. Status code     →  200 OK
2. Headers         →  Extra info (content type, cookies)
3. Empty line
4. Body            →  The actual content (HTML, JSON, image)
```

---

## Lesson 03: Browser DevTools

### How to Open DevTools
- **Shortcut:** `Cmd + Option + I`
- Or: Right-click → Inspect

### Important Tabs

| Tab | What It Does |
|-----|--------------|
| **Network** | See all HTTP requests |
| **Elements** | See/edit HTML & CSS live |
| **Console** | See JavaScript errors |

### Key Discovery: One Page = Many Requests
- Google homepage made **92 requests!**
- Browser gets HTML first, then CSS, JavaScript, images, fonts...

### What I Learned from Network Tab
- First request is always the **document** (HTML)
- Status **200** = success
- Can see every file the browser downloads

### What I Learned from Elements Tab
- Can see real HTML code of any website
- Can edit HTML/CSS (changes are temporary)
- CSS can override HTML attributes (like `width: auto`)
- Example of HTML I found:
```html
<img
  class="lnXdpd"
  alt="Seasonal holidays 2025"
  src="/logos/doodles/..."
  width="500"
  height="200"
>
```

### Useful Shortcuts

| Shortcut | Action |
|----------|--------|
| `Cmd + Option + I` | Open/close DevTools |
| `Cmd + Shift + C` | Inspect element mode |
| `Cmd + R` | Refresh page |

---

## Quick Review Questions

1. What method to view a webpage? → **GET**
2. What method to submit a form? → **POST**
3. What does 404 mean? → **Not Found**
4. What's the difference between 401 and 403? → **401 = not logged in, 403 = logged in but not allowed**
5. Which tab shows HTTP requests? → **Network tab**
6. How many requests for one webpage? → **Many! (50+)**

---

**Next:** Lesson 04 - Frontend vs Backend
