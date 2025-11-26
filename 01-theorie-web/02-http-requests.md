# Lesson 02 - HTTP Protocol

## What You'll Learn

- What HTTP is
- What requests and responses are
- HTTP methods (GET, POST, PUT, DELETE)
- Status codes (200, 404, 500...)
- Headers and body

---

## What is HTTP?

**HTTP** (HyperText Transfer Protocol) is the "language" that browsers and servers use to communicate.

When you visit a website:
1. Your browser sends an **HTTP Request** to the server
2. The server processes it
3. The server sends back an **HTTP Response**

```
BROWSER                                    SERVER
   |                                          |
   |  -------- HTTP REQUEST --------->       |
   |  "GET /index.html please"                |
   |                                          |
   |  <------- HTTP RESPONSE ---------       |
   |  "200 OK, here's the HTML..."            |
   |                                          |
```

---

## HTTP Request

An HTTP request has several parts:

### 1. Method (What You Want to Do)

| Method | Purpose | Example |
|--------|---------|---------|
| **GET** | Retrieve data | View a webpage, get user info |
| **POST** | Send/create data | Submit a form, create an account |
| **PUT** | Update data | Update your profile |
| **DELETE** | Delete data | Delete a comment |

### 2. URL (Where)

```
https://example.com/users/123
  |          |        |    |
  |          |        |    └── Specific user (ID 123)
  |          |        └── Resource (users)
  |          └── Domain
  └── Protocol (secure HTTP)
```

### 3. Headers (Extra Information)

Headers are key-value pairs with additional info:

```
Host: example.com
User-Agent: Chrome/120.0
Accept: text/html
Authorization: Bearer abc123
Content-Type: application/json
```

### 4. Body (Data You're Sending)

For POST/PUT requests, the body contains the data:

```json
{
  "name": "Kieu",
  "email": "kieu@example.com"
}
```

### Complete Request Example

```http
POST /api/users HTTP/1.1
Host: example.com
Content-Type: application/json
Authorization: Bearer abc123

{
  "name": "Kieu",
  "email": "kieu@example.com"
}
```

---

## HTTP Response

The server responds with:

### 1. Status Code (What Happened)

A 3-digit number indicating the result:

| Code | Meaning | When |
|------|---------|------|
| **200** | OK | Success! Here's your data |
| **201** | Created | New resource created successfully |
| **301** | Moved Permanently | Page has moved, go here instead |
| **400** | Bad Request | You sent invalid data |
| **401** | Unauthorized | You need to log in |
| **403** | Forbidden | You're logged in but not allowed |
| **404** | Not Found | That page/resource doesn't exist |
| **500** | Internal Server Error | Server crashed/broke |
| **503** | Service Unavailable | Server is overloaded or down |

### Status Code Categories

| Range | Category |
|-------|----------|
| 1xx | Information |
| 2xx | Success |
| 3xx | Redirect |
| 4xx | Client error (YOUR fault) |
| 5xx | Server error (THEIR fault) |

### 2. Headers

```
Content-Type: text/html
Content-Length: 1234
Set-Cookie: session=abc123
```

### 3. Body (The Actual Content)

```html
<!DOCTYPE html>
<html>
  <head><title>Welcome</title></head>
  <body><h1>Hello!</h1></body>
</html>
```

### Complete Response Example

```http
HTTP/1.1 200 OK
Content-Type: text/html
Content-Length: 123

<!DOCTYPE html>
<html>
  <body><h1>Hello Kieu!</h1></body>
</html>
```

---

## HTTPS - Secure HTTP

**HTTPS** = HTTP + Security (encryption)

```
HTTP:  Data travels in plain text (anyone can read it)
HTTPS: Data is encrypted (only you and the server can read it)
```

- Always use HTTPS for sensitive data (passwords, payments)
- Modern browsers warn users about HTTP sites
- Look for the 🔒 padlock in the address bar

---

## Real Example: What Happens When You Visit google.com

```
1. You type google.com in your browser

2. Browser checks DNS for google.com's IP
   DNS says: "It's 142.250.185.238"

3. Browser creates HTTP Request:
   GET / HTTP/1.1
   Host: google.com
   User-Agent: Chrome/120.0
   Accept: text/html

4. Request travels through the Internet to Google

5. Google's server processes the request

6. Google sends HTTP Response:
   HTTP/1.1 200 OK
   Content-Type: text/html

   <!DOCTYPE html>
   <html>...(Google's homepage)...</html>

7. Browser receives HTML

8. Browser sees the HTML references CSS, JS, images
   Browser makes MORE requests for each of those

9. Browser combines everything and shows you the page
```

---

## Content Types

The `Content-Type` header tells what kind of data is being sent:

| Content-Type | What It Is |
|--------------|------------|
| `text/html` | HTML webpage |
| `text/css` | CSS stylesheet |
| `text/javascript` | JavaScript code |
| `application/json` | JSON data (very common for APIs) |
| `image/png` | PNG image |
| `image/jpeg` | JPEG image |

---

## Quick Quiz

1. What HTTP method do you use to view a webpage?
2. What does status code 404 mean?
3. What's the difference between 401 and 403?
4. What does HTTPS add to HTTP?
5. A form submission typically uses which HTTP method?

<details>
<summary>Click to see answers</summary>

1. GET
2. Not Found - the page/resource doesn't exist
3. 401 = not logged in, 403 = logged in but not allowed
4. Encryption (security)
5. POST

</details>

---

## Key Takeaways

1. **HTTP is request-response** - Browser asks, server answers
2. **Methods define intent** - GET to read, POST to create, etc.
3. **Status codes tell you what happened** - 2xx good, 4xx your fault, 5xx their fault
4. **Headers carry metadata** - Content type, authentication, etc.
5. **Body carries data** - HTML, JSON, images, etc.

---

## Next Lesson

Now let's learn how to SEE all this happening in real-time: [Lesson 03: Browser & DevTools](./03-browser-devtools.md)
