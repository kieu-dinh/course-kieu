# Lesson 04 - Frontend vs Backend

## What You'll Learn

- What frontend means
- What backend means
- How they work together
- What a web application is

---

## Frontend - What the User Sees

**Frontend** = everything that runs in the browser

```
┌─────────────────────────────┐
│         BROWSER             │
│  ┌───────────────────────┐  │
│  │   HTML (structure)    │  │
│  │   CSS (style)         │  │
│  │   JavaScript (logic)  │  │
│  └───────────────────────┘  │
└─────────────────────────────┘
```

### Technologies

| Technology | Role |
|------------|------|
| **HTML** | Structure (what's on the page) |
| **CSS** | Style (how it looks) |
| **JavaScript** | Interactivity (what it does) |

### Example

```html
<!-- HTML: Structure -->
<button id="btn">Click me</button>

<!-- CSS: Style -->
<style>
  button { background: blue; color: white; }
</style>

<!-- JavaScript: Interactivity -->
<script>
  document.getElementById('btn').onclick = function() {
    alert('Clicked!');
  }
</script>
```

---

## Backend - What the Server Does

**Backend** = code running on the server

```
┌─────────────────────────────┐
│          SERVER             │
│  ┌───────────────────────┐  │
│  │   PHP / Python / etc  │  │
│  │   Database (MySQL)    │  │
│  │   Files               │  │
│  └───────────────────────┘  │
└─────────────────────────────┘
```

### What Backend Does

- Process form submissions
- Read/write to database
- Handle user authentication
- Send emails
- Store files
- Business logic

### Technologies

| Technology | Type |
|------------|------|
| **PHP** | Language (what we'll learn) |
| **Laravel** | Framework (makes PHP easier) |
| **MySQL** | Database (stores data) |

---

## How They Work Together

```
┌──────────────┐                    ┌──────────────┐
│   BROWSER    │                    │    SERVER    │
│  (Frontend)  │                    │   (Backend)  │
│              │   HTTP Request     │              │
│  HTML/CSS/JS │ ─────────────────> │  PHP/Laravel │
│              │                    │              │
│              │   HTTP Response    │   Database   │
│              │ <───────────────── │              │
└──────────────┘                    └──────────────┘
```

### Real Example: Login Form

1. **Frontend**: Shows login form (HTML/CSS)
2. **User**: Enters email/password, clicks submit
3. **Frontend**: Sends POST request with credentials
4. **Backend**: Receives request, checks database
5. **Backend**: Password correct? Send success response
6. **Frontend**: Shows "Welcome!" or error message

---

## Static vs Dynamic Websites

### Static Website

```
Browser requests page → Server sends same HTML file every time
```

- Simple HTML files
- Same content for everyone
- No database needed
- Example: A portfolio page

### Dynamic Website (Web Application)

```
Browser requests page → Server generates HTML based on data
```

- Content changes based on user, time, data
- Uses a database
- Personalized experience
- Example: Facebook, Twitter, Gmail

**We'll build dynamic web applications!**

---

## The Full Stack

**Full Stack** = Frontend + Backend

```
┌─────────────────────────────────────────────────┐
│                   FULL STACK                    │
│                                                 │
│  ┌──────────────┐         ┌──────────────────┐  │
│  │  FRONTEND    │         │    BACKEND       │  │
│  │  HTML/CSS    │  <--->  │    PHP/Laravel   │  │
│  │  JavaScript  │         │    MySQL         │  │
│  │  Tailwind    │         │    Files         │  │
│  │  Alpine      │         │                  │  │
│  └──────────────┘         └──────────────────┘  │
│                                                 │
└─────────────────────────────────────────────────┘
```

By the end of this training, you'll be a **full stack developer**!

---

## Our Stack: TALL

We'll learn the **TALL Stack**:

| Letter | Technology | Frontend/Backend |
|--------|------------|------------------|
| **T** | Tailwind CSS | Frontend (styling) |
| **A** | Alpine.js | Frontend (interactivity) |
| **L** | Laravel | Backend (framework) |
| **L** | Livewire | Bridge (makes frontend reactive) |

---

## Quick Quiz

1. HTML, CSS, JavaScript run where?
2. PHP runs where?
3. Where is user data stored?
4. What makes a website "dynamic"?

<details>
<summary>Answers</summary>

1. In the browser (frontend)
2. On the server (backend)
3. In a database (backend)
4. Content generated from data, different for each user/request

</details>

---

## Module Complete!

You now understand how the web works! Time for exercises.

Go to: [Exercise 01: Explore the Network](./exercises/01-explore-network.md)
