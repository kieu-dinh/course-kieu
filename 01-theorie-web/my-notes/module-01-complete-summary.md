# Module 01 Complete Summary - Web Theory

## What I Learned

### 1. Internet & Networks
- **Network** = computers connected together
- **Internet** = network of networks (worldwide)
- **IP Address** = computer's address (like phone number)
- **DNS** = translates domain names to IP addresses (like phone book)
- **Port** = which service (80=HTTP, 443=HTTPS, 3306=MySQL)
- **Server** = computer that serves/provides things to others

### 2. HTTP Protocol
- **HTTP** = language browsers and servers use to communicate
- **Request** = browser asks for something
- **Response** = server sends back data

**Methods:**
| Method | Purpose |
|--------|---------|
| GET | Read/view data |
| POST | Send/create data |
| PUT | Update data |
| DELETE | Remove data |

**Status Codes:**
| Code | Meaning |
|------|---------|
| 200 | OK - Success |
| 304 | Not Modified (use cache) |
| 404 | Not Found |
| 401 | Not logged in |
| 403 | Logged in but not allowed |
| 500 | Server error |

### 3. Browser DevTools
- **Open:** `Cmd + Option + I`
- **Network tab** = see all HTTP requests
- **Elements tab** = see/edit HTML & CSS
- **Console tab** = see JavaScript errors

**Key discoveries:**
- Google homepage = 92 requests
- GitHub = 213 requests
- Hacker News = 16 requests (simple site)
- Apple = 97 requests, 12.8 MB (fancy site)

### 4. Frontend vs Backend

| | Frontend | Backend |
|---|----------|---------|
| Where | Browser | Server |
| Languages | HTML, CSS, JS | PHP, Python |
| Purpose | Show things | Process data, database |

**TALL Stack (what I'll learn):**
- **T**ailwind CSS (styling)
- **A**lpine.js (interactivity)
- **L**aravel (PHP framework)
- **L**ivewire (connects both)

### 5. Other Concepts
- **`<a>` tag** = HTML link tag
- **JSON** = data format used by APIs (not HTML)
- **Responsive design** = sites that adapt to different screen sizes
- More features = more requests = slower loading

---

## Commands I Used

```bash
# Find IP address for a domain
nslookup google.com

# Find my private IP address
ipconfig getifaddr en0

# Check PHP version
php -v

# Check Composer
composer -v
```

---

## Next: Module 02 - HTML/CSS/Tailwind

I'll start writing actual code!
