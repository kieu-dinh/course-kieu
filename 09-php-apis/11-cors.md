# Lesson 11 - CORS (Cross-Origin Resource Sharing)

**Duration**: 30-45 minutes

---

## What is CORS?

**CORS** = Cross-Origin Resource Sharing

It controls whether a website can call your API from a different domain.

### The Same-Origin Policy

Browsers block requests between different origins by default:

**Same Origin (Allowed):**
```
https://myapp.com → https://myapp.com/api     ✅
https://myapp.com → https://myapp.com:443/api ✅
```

**Different Origin (Blocked by default):**
```
https://myapp.com     → https://api.myapp.com      ❌ (different subdomain)
https://myapp.com     → http://myapp.com           ❌ (different protocol)
https://myapp.com     → https://myapp.com:8080     ❌ (different port)
https://myapp.com     → https://other.com          ❌ (different domain)
```

**Why?** Security! Prevents malicious websites from stealing your data.

---

## CORS Error Example

**Frontend (React app at https://myapp.com):**
```javascript
fetch('https://api.example.com/users')
  .then(response => response.json())
```

**Browser Console Error:**
```
Access to fetch at 'https://api.example.com/users' from origin
'https://myapp.com' has been blocked by CORS policy:
No 'Access-Control-Allow-Origin' header is present on the
requested resource.
```

**Why?** API didn't send CORS headers allowing myapp.com!

---

## How CORS Works

### Simple Requests

For simple GET/POST requests, browser:
1. Sends request to API
2. Checks response headers
3. If `Access-Control-Allow-Origin` matches, allows response
4. Otherwise, blocks it

**Headers from API:**
```
Access-Control-Allow-Origin: https://myapp.com
```

### Preflight Requests

For complex requests (PUT, DELETE, custom headers), browser:
1. Sends OPTIONS request first (preflight)
2. Asks "What methods/headers are allowed?"
3. If allowed, sends actual request
4. Otherwise, blocks it

**Preflight Request:**
```
OPTIONS /api/users/5
Origin: https://myapp.com
Access-Control-Request-Method: DELETE
Access-Control-Request-Headers: Authorization
```

**API Response:**
```
Access-Control-Allow-Origin: https://myapp.com
Access-Control-Allow-Methods: GET, POST, PUT, DELETE
Access-Control-Allow-Headers: Authorization, Content-Type
Access-Control-Max-Age: 86400
```

---

## Implementing CORS in PHP

### Basic CORS (Allow All Origins)

```php
<?php
// public/index.php

// CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-API-Key');

// Handle preflight OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Rest of your API code...
```

**What each header does:**
- `Access-Control-Allow-Origin: *` - Allow any website
- `Access-Control-Allow-Methods` - Allowed HTTP methods
- `Access-Control-Allow-Headers` - Allowed custom headers

---

## CORS Middleware

Better approach - separate CORS logic:

```php
<?php
// middleware/CorsMiddleware.php

class CorsMiddleware {
    private static $allowedOrigins = [
        'https://myapp.com',
        'https://www.myapp.com',
        'http://localhost:3000',  // For development
    ];

    private static $allowedMethods = [
        'GET',
        'POST',
        'PUT',
        'PATCH',
        'DELETE',
        'OPTIONS'
    ];

    private static $allowedHeaders = [
        'Content-Type',
        'Authorization',
        'X-API-Key',
        'X-Requested-With'
    ];

    private static $maxAge = 86400; // 24 hours

    /**
     * Handle CORS
     */
    public static function handle() {
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';

        // Check if origin is allowed
        if (self::isOriginAllowed($origin)) {
            header("Access-Control-Allow-Origin: {$origin}");
            header('Access-Control-Allow-Credentials: true');
        }

        // Set other CORS headers
        header('Access-Control-Allow-Methods: ' . implode(', ', self::$allowedMethods));
        header('Access-Control-Allow-Headers: ' . implode(', ', self::$allowedHeaders));
        header('Access-Control-Max-Age: ' . self::$maxAge);

        // Handle preflight
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }

        return true;
    }

    /**
     * Check if origin is allowed
     */
    private static function isOriginAllowed($origin) {
        // Allow all origins (development only!)
        if (getenv('APP_ENV') === 'development') {
            return true;
        }

        // Check whitelist
        return in_array($origin, self::$allowedOrigins);
    }

    /**
     * Set allowed origins dynamically
     */
    public static function setAllowedOrigins($origins) {
        self::$allowedOrigins = $origins;
    }

    /**
     * Add allowed origin
     */
    public static function addAllowedOrigin($origin) {
        if (!in_array($origin, self::$allowedOrigins)) {
            self::$allowedOrigins[] = $origin;
        }
    }
}
```

### Using CORS Middleware

```php
<?php
// public/index.php

require_once __DIR__ . '/../middleware/CorsMiddleware.php';

// Handle CORS first
CorsMiddleware::handle();

// Rest of your API...
require_once __DIR__ . '/../core/Router.php';
$router = new Router();
// ...
```

---

## CORS Configuration

### Configuration File

```php
<?php
// config/cors.php

return [
    'allowed_origins' => [
        'https://myapp.com',
        'https://www.myapp.com',
        'https://admin.myapp.com',
    ],

    'allowed_origins_patterns' => [
        '/^https:\/\/.*\.myapp\.com$/',  // All myapp.com subdomains
    ],

    'allowed_methods' => [
        'GET',
        'POST',
        'PUT',
        'PATCH',
        'DELETE',
        'OPTIONS',
    ],

    'allowed_headers' => [
        'Content-Type',
        'Authorization',
        'X-API-Key',
        'X-Requested-With',
        'Accept',
    ],

    'exposed_headers' => [
        'X-RateLimit-Remaining',
        'X-RateLimit-Limit',
    ],

    'max_age' => 86400, // 24 hours

    'supports_credentials' => true,

    // Development: allow all origins
    'allow_all_origins' => getenv('APP_ENV') === 'development',
];
```

### Loading Configuration

```php
// middleware/CorsMiddleware.php

private static $config;

public static function init() {
    self::$config = require __DIR__ . '/../config/cors.php';
}

private static function isOriginAllowed($origin) {
    // Allow all in development
    if (self::$config['allow_all_origins']) {
        return true;
    }

    // Check exact match
    if (in_array($origin, self::$config['allowed_origins'])) {
        return true;
    }

    // Check patterns
    foreach (self::$config['allowed_origins_patterns'] as $pattern) {
        if (preg_match($pattern, $origin)) {
            return true;
        }
    }

    return false;
}
```

---

## CORS with Credentials

When sending cookies or authentication:

### API (PHP)

```php
header('Access-Control-Allow-Origin: https://myapp.com'); // Must be specific!
header('Access-Control-Allow-Credentials: true');
```

**Important:** Cannot use `*` with credentials! Must specify exact origin.

### Client (JavaScript)

```javascript
fetch('https://api.example.com/users', {
  method: 'GET',
  credentials: 'include',  // Send cookies
  headers: {
    'Authorization': 'Bearer token123'
  }
})
```

---

## CORS Headers Explained

### Access-Control-Allow-Origin

Who can access the API:

```php
// Allow all
header('Access-Control-Allow-Origin: *');

// Allow specific origin
header('Access-Control-Allow-Origin: https://myapp.com');

// Dynamic (based on request)
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: {$origin}");
}
```

### Access-Control-Allow-Methods

Which HTTP methods are allowed:

```php
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
```

### Access-Control-Allow-Headers

Which custom headers client can send:

```php
header('Access-Control-Allow-Headers: Content-Type, Authorization');
```

### Access-Control-Expose-Headers

Which response headers client can read:

```php
header('Access-Control-Expose-Headers: X-RateLimit-Remaining, X-Total-Count');
```

Without this, client can only read basic headers (Content-Type, etc.).

### Access-Control-Max-Age

How long to cache preflight response (seconds):

```php
header('Access-Control-Max-Age: 86400'); // 24 hours
```

Browser won't send preflight again for 24 hours!

### Access-Control-Allow-Credentials

Allow cookies/authentication:

```php
header('Access-Control-Allow-Credentials: true');
```

---

## Common CORS Scenarios

### Scenario 1: Public API (Allow All)

```php
// Allow any website to call API
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');
```

**Use case:** Weather API, currency rates, etc.

### Scenario 2: Private API (Specific Domains)

```php
$allowedOrigins = [
    'https://myapp.com',
    'https://admin.myapp.com'
];

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: {$origin}");
    header('Access-Control-Allow-Credentials: true');
}
```

**Use case:** Your company's internal apps.

### Scenario 3: Development + Production

```php
if (getenv('APP_ENV') === 'development') {
    // Allow all in development
    header('Access-Control-Allow-Origin: *');
} else {
    // Whitelist in production
    $allowedOrigins = ['https://myapp.com'];
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';

    if (in_array($origin, $allowedOrigins)) {
        header("Access-Control-Allow-Origin: {$origin}");
    }
}
```

---

## Debugging CORS

### Check Request Headers

In browser DevTools → Network tab:

**Preflight Request:**
```
Method: OPTIONS
Origin: https://myapp.com
Access-Control-Request-Method: DELETE
Access-Control-Request-Headers: authorization
```

### Check Response Headers

**API Should Return:**
```
Access-Control-Allow-Origin: https://myapp.com
Access-Control-Allow-Methods: GET, POST, DELETE
Access-Control-Allow-Headers: authorization
```

### Common Issues

**Issue 1: Missing CORS headers**
```
Solution: Add headers to API response
```

**Issue 2: Wildcard with credentials**
```
Error: Cannot use 'Access-Control-Allow-Origin: *' with credentials
Solution: Use specific origin instead of *
```

**Issue 3: Preflight not handled**
```
Error: OPTIONS request returns 404/405
Solution: Add OPTIONS handler that returns 200
```

**Issue 4: Headers not allowed**
```
Error: 'authorization' is not allowed by CORS
Solution: Add to Access-Control-Allow-Headers
```

---

## Testing CORS

### Using curl

```bash
# Simple request
curl -H "Origin: https://myapp.com" \
     -H "Access-Control-Request-Method: GET" \
     http://localhost/api/users

# Preflight request
curl -X OPTIONS \
     -H "Origin: https://myapp.com" \
     -H "Access-Control-Request-Method: DELETE" \
     -H "Access-Control-Request-Headers: Authorization" \
     http://localhost/api/users/5
```

### Using JavaScript

```javascript
// Test from browser console
fetch('http://localhost/api/users', {
  method: 'GET',
  headers: {
    'Authorization': 'Bearer token123'
  }
})
.then(response => response.json())
.then(data => console.log(data))
.catch(error => console.error('CORS Error:', error));
```

---

## Security Best Practices

### 1. Never Use * in Production

```php
// ❌ Bad - allows anyone
header('Access-Control-Allow-Origin: *');

// ✅ Good - whitelist specific domains
$allowedOrigins = ['https://myapp.com'];
```

### 2. Validate Origin

```php
// ❌ Bad - trusts any origin
$origin = $_SERVER['HTTP_ORIGIN'];
header("Access-Control-Allow-Origin: {$origin}");

// ✅ Good - validates against whitelist
if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: {$origin}");
}
```

### 3. Be Specific with Headers

```php
// ❌ Bad - allows any header
header('Access-Control-Allow-Headers: *');

// ✅ Good - specific headers only
header('Access-Control-Allow-Headers: Content-Type, Authorization');
```

### 4. Use Short Max-Age in Development

```php
if (getenv('APP_ENV') === 'development') {
    header('Access-Control-Max-Age: 0'); // No caching
} else {
    header('Access-Control-Max-Age: 86400'); // Cache in production
}
```

---

## Laravel CORS (Preview)

Laravel handles CORS automatically with middleware:

```php
// config/cors.php
return [
    'paths' => ['api/*'],
    'allowed_origins' => ['https://myapp.com'],
    'allowed_methods' => ['*'],
    'allowed_headers' => ['*'],
    'max_age' => 86400,
];
```

No manual headers needed!

---

## Quick Quiz

**Question 1:** What is CORS?
<details>
<summary>Answer</summary>
Cross-Origin Resource Sharing - a mechanism that allows websites to call APIs from different domains.
</details>

**Question 2:** What's a preflight request?
<details>
<summary>Answer</summary>
An OPTIONS request sent by browser before actual request, asking if the operation is allowed.
</details>

**Question 3:** Can you use `Access-Control-Allow-Origin: *` with credentials?
<details>
<summary>Answer</summary>
No! Must specify exact origin when using credentials.
</details>

**Question 4:** What status code should OPTIONS requests return?
<details>
<summary>Answer</summary>
200 OK (even if endpoint doesn't exist for other methods)
</details>

**Question 5:** Why is CORS needed?
<details>
<summary>Answer</summary>
Security - prevents malicious websites from making unauthorized requests to APIs using your browser credentials.
</details>

---

## Summary

You learned:
- What CORS is and why it exists (security)
- Same-origin policy and cross-origin requests
- How CORS works (simple vs preflight requests)
- Implementing CORS in PHP
- CORS middleware and configuration
- All CORS headers and their purposes
- Handling credentials and cookies
- Common CORS scenarios
- Debugging CORS errors
- Security best practices

---

## Next Lesson

**12-consuming-apis.md** - Learn to call external APIs from PHP using cURL, handle responses, and work with third-party services!
