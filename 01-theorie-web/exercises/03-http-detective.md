# Exercise 03 - HTTP Detective

## Objective

Understand HTTP methods, status codes, and headers by investigating real requests.

---

## Part 1: Status Code Hunt

Visit each URL and record the status code (check Network tab):

| URL | Expected Status | Your Result |
|-----|-----------------|-------------|
| `https://httpstat.us/200` | 200 | |
| `https://httpstat.us/404` | 404 | |
| `https://httpstat.us/500` | 500 | |
| `https://httpstat.us/301` | 301 | |

**Note:** httpstat.us is a tool that returns whatever status code you ask for!

### Questions

**Q1: What color does Chrome show for 404 errors?**
```
Your answer:
```

**Q2: When you visited /301, what happened? Where did it redirect?**
```
Your answer:
```

---

## Part 2: Real 404 Hunt

Find a real 404 error:

1. Go to `https://github.com/thispagereallydoesnotexist`
2. Check the Network tab

**Q3: What is the status code?**
```
Your answer:
```

**Q4: Does GitHub show a custom 404 page?**
```
Your answer:
```

---

## Part 3: Request Headers Investigation

1. Go to `https://www.google.com`
2. Network tab → click the first request
3. Click "Headers"

Find these headers and write their values:

| Header | Value |
|--------|-------|
| Request Method | |
| Status Code | |
| Content-Type (response) | |
| User-Agent (request) | |

---

## Part 4: POST Request (Form Submission)

We'll observe a form submission:

1. Go to `https://httpbin.org/forms/post`
2. Open Network tab
3. Fill out the form with fake data
4. Click Submit
5. Watch the Network tab

### Questions

**Q5: What HTTP method was used?**
```
Your answer:
```

**Q6: Click the request. What tab shows the data you submitted?**
```
Your answer: (Hint: look for "Payload" or "Form Data")
```

**Q7: What Content-Type was used for the request?**
```
Your answer:
```

---

## Part 5: API Request

APIs return JSON instead of HTML. Let's see one:

1. Open a new tab
2. Go to `https://api.github.com/users/octocat`
3. Check Network tab

### Questions

**Q8: What is the Content-Type of the response?**
```
Your answer:
```

**Q9: Is this HTML or JSON?**
```
Your answer:
```

**Q10: What is the "login" field in the response?**
```
Your answer:
```

---

## Summary Challenge

Match each scenario to the correct status code:

| Scenario | Status Code |
|----------|-------------|
| Page loaded successfully | |
| Page not found | |
| Server crashed | |
| Not logged in | |
| Page moved to new URL | |
| Resource created successfully | |

Options: 200, 201, 301, 401, 404, 500

---

## Validation

Ask Claude:
> "I completed the HTTP Detective exercise. Can you check my answers and explain anything I got wrong?"

---

## Module 01 Complete!

You now understand:
- How the Internet works
- How HTTP works
- How to use DevTools
- Frontend vs Backend

**Next Module:** [02 - HTML/CSS/Tailwind](../../02-html-css/)
