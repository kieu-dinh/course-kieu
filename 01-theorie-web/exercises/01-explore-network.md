# Exercise 01 - Explore the Network

## Objective

Use the browser's Network tab to observe HTTP requests in real-time.

---

## Part 1: Basic Observation

### Steps

1. Open Chrome
2. Go to `about:blank`
3. Open DevTools: `Cmd + Option + I`
4. Click the **Network** tab
5. Visit `https://example.com`

### Questions to Answer

Write your answers below:

**Q1: How many requests were made?**
```
Your answer:
```

**Q2: What was the status code of the first request?**
```
Your answer:
```

**Q3: What is the Content-Type of the HTML response?**
```
Your answer: (click on the request → Headers → Response Headers)
```

---

## Part 2: A Real Website

### Steps

1. Clear the Network tab (click the 🚫 icon)
2. Visit `https://github.com`

### Questions to Answer

**Q4: How many total requests were made?**
```
Your answer:
```

**Q5: List 3 different file types you see:**
```
1.
2.
3.
```

**Q6: Find a request with status code 304. What does 304 mean?**
```
Your answer:
```

---

## Part 3: Filter Requests

The Network tab has filters. Try them:

1. Click **Doc** - shows only HTML documents
2. Click **CSS** - shows only stylesheets
3. Click **JS** - shows only JavaScript files
4. Click **Img** - shows only images

### Questions

**Q7: How many JavaScript files did GitHub load?**
```
Your answer:
```

**Q8: How many images?**
```
Your answer:
```

---

## Part 4: Request Details

1. Click on the main `github.com` request
2. Look at the Headers panel

### Questions

**Q9: What HTTP method was used?**
```
Your answer:
```

**Q10: What is the User-Agent header? (This identifies your browser)**
```
Your answer:
```

---

## Validation

Show your answers to Claude and ask:
> "I completed the Network exploration exercise. Can you verify my answers are correct?"

Paste your answers and Claude will check them.

---

## Completed?

- [ ] I can open the Network tab
- [ ] I understand what each column means
- [ ] I can filter by file type
- [ ] I can read request/response headers

Next: [Exercise 02: Analyze a Website](./02-analyze-website.md)
