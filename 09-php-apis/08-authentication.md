# Lesson 08 - API Authentication (API Keys & Tokens)

**Duration**: 60-75 minutes

---

## Why API Authentication?

Without authentication, anyone can:
- Access private data
- Create/modify/delete resources
- Abuse your API (spam, DDoS)
- Steal data

**Authentication** answers: "Who are you?"
**Authorization** answers: "What can you do?"

---

## Authentication Methods

### 1. API Keys
- Simple unique string
- Good for: Server-to-server, third-party integrations
- Example: Google Maps API, Stripe API

### 2. Bearer Tokens
- Token sent in Authorization header
- Good for: Mobile apps, SPAs (Single Page Applications)
- Example: Most modern APIs

### 3. Session-based (NOT RESTful)
- Uses cookies and server sessions
- Good for: Traditional web apps
- Not recommended for APIs (stateful)

### 4. OAuth 2.0
- Complex, industry standard
- Good for: "Login with Google/Facebook"
- We'll cover basics, full implementation later

We'll focus on API Keys and Bearer Tokens!

---

## Method 1: API Keys

### What is an API Key?

A unique, random string that identifies the client:

```
X-API-Key: your_api_key_here_abc123def456
```

### Generating API Keys

```php
<?php
// helpers/ApiKey.php

class ApiKey {
    /**
     * Generate secure API key
     */
    public static function generate($prefix = 'sk') {
        $randomBytes = random_bytes(32);
        $key = bin2hex($randomBytes);

        return $prefix . '_' . $key;
    }

    /**
     * Hash API key for storage
     */
    public static function hash($key) {
        return hash('sha256', $key);
    }

    /**
     * Verify API key
     */
    public static function verify($providedKey, $hashedKey) {
        return hash_equals($hashedKey, self::hash($providedKey));
    }
}
```

**Generate:**
```php
$apiKey = ApiKey::generate();
// Result: sk_a3f8d9c1e2b5f7a0c8d3e6f1a9b4c7e2d5f8a1b4c7e0d3f6a9b2c5e8f1a4b7c0
```

### Database Schema

```sql
CREATE TABLE api_keys (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,          -- "Production Server", "Mobile App"
    key_hash VARCHAR(64) NOT NULL,       -- Hashed API key
    prefix VARCHAR(10) NOT NULL,         -- First part of key (for identification)
    last_used_at TIMESTAMP NULL,
    expires_at TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_prefix (prefix),
    INDEX idx_user_id (user_id)
);
```

**Why hash?** Like passwords, if database is compromised, actual keys aren't exposed.

### Creating API Keys

```php
<?php
// models/ApiKeyModel.php

class ApiKeyModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Create API key for user
     */
    public function create($userId, $name, $expiresInDays = null) {
        // Generate key
        $fullKey = ApiKey::generate('sk');

        // Extract prefix (first 10 chars)
        $prefix = substr($fullKey, 0, 10);

        // Hash for storage
        $hash = ApiKey::hash($fullKey);

        // Calculate expiration
        $expiresAt = null;
        if ($expiresInDays) {
            $expiresAt = date('Y-m-d H:i:s', strtotime("+{$expiresInDays} days"));
        }

        // Store in database
        $stmt = $this->db->prepare("
            INSERT INTO api_keys (user_id, name, key_hash, prefix, expires_at)
            VALUES (?, ?, ?, ?, ?)
        ");

        $stmt->execute([$userId, $name, $hash, $prefix, $expiresAt]);

        // Return the FULL key (only time we show it!)
        return [
            'id' => $this->db->lastInsertId(),
            'key' => $fullKey,  // Show once!
            'name' => $name,
            'expires_at' => $expiresAt
        ];
    }

    /**
     * Verify API key and return associated user
     */
    public function verify($providedKey) {
        // Extract prefix
        $prefix = substr($providedKey, 0, 10);

        // Find by prefix (faster than checking all)
        $stmt = $this->db->prepare("
            SELECT ak.*, u.id as user_id, u.name, u.email
            FROM api_keys ak
            JOIN users u ON ak.user_id = u.id
            WHERE ak.prefix = ?
              AND ak.is_active = 1
              AND (ak.expires_at IS NULL OR ak.expires_at > NOW())
        ");

        $stmt->execute([$prefix]);
        $apiKey = $stmt->fetch();

        if (!$apiKey) {
            return null;
        }

        // Verify full key
        if (!ApiKey::verify($providedKey, $apiKey['key_hash'])) {
            return null;
        }

        // Update last used
        $this->updateLastUsed($apiKey['id']);

        return [
            'user_id' => $apiKey['user_id'],
            'name' => $apiKey['name'],
            'email' => $apiKey['email']
        ];
    }

    /**
     * Update last used timestamp
     */
    private function updateLastUsed($keyId) {
        $stmt = $this->db->prepare("
            UPDATE api_keys SET last_used_at = NOW() WHERE id = ?
        ");
        $stmt->execute([$keyId]);
    }

    /**
     * Revoke (deactivate) API key
     */
    public function revoke($keyId, $userId) {
        $stmt = $this->db->prepare("
            UPDATE api_keys SET is_active = 0
            WHERE id = ? AND user_id = ?
        ");
        return $stmt->execute([$keyId, $userId]);
    }

    /**
     * List user's API keys
     */
    public function getUserKeys($userId) {
        $stmt = $this->db->prepare("
            SELECT id, name, prefix, last_used_at, expires_at, is_active, created_at
            FROM api_keys
            WHERE user_id = ?
            ORDER BY created_at DESC
        ");

        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
}
```

### API Key Middleware

```php
<?php
// middleware/ApiKeyMiddleware.php

class ApiKeyMiddleware {
    /**
     * Check if request has valid API key
     */
    public static function handle() {
        // Get API key from header
        $apiKey = Request::header('X-API-Key');

        if (!$apiKey) {
            Response::unauthorized('API key required');
        }

        // Verify key
        $apiKeyModel = new ApiKeyModel();
        $user = $apiKeyModel->verify($apiKey);

        if (!$user) {
            Response::unauthorized('Invalid API key');
        }

        // Store user in "Auth" for later use
        Auth::setUser($user);

        return true;
    }
}
```

### Using API Key Authentication

```php
// routes/api.php

// Public endpoint (no auth)
$router->get('/api/products', [ProductController::class, 'index']);

// Protected endpoint (requires API key)
$router->post('/api/products', [ProductController::class, 'store'], [
    ApiKeyMiddleware::class
]);

$router->delete('/api/products/{id}', [ProductController::class, 'destroy'], [
    ApiKeyMiddleware::class
]);
```

### API Key Management Endpoints

```php
// controllers/ApiKeyController.php

class ApiKeyController {
    private $apiKeyModel;

    public function __construct() {
        $this->apiKeyModel = new ApiKeyModel();
    }

    /**
     * POST /api/keys - Create new API key
     */
    public function store() {
        $user = Auth::user();
        if (!$user) {
            Response::unauthorized();
        }

        $data = Request::json();

        // Validate
        $validator = new Validator($data, [
            'name' => 'required|min:3|max:100'
        ]);

        if (!$validator->validate()) {
            Response::validationError($validator->errors());
        }

        // Check limit (max 10 keys per user)
        $existingKeys = $this->apiKeyModel->getUserKeys($user['user_id']);
        if (count($existingKeys) >= 10) {
            Response::error('Maximum of 10 API keys allowed', 400);
        }

        // Create key
        $expiresInDays = $data['expires_in_days'] ?? null;
        $result = $this->apiKeyModel->create(
            $user['user_id'],
            $data['name'],
            $expiresInDays
        );

        // Return key (only time we show it!)
        Response::created([
            'id' => $result['id'],
            'key' => $result['key'],
            'name' => $result['name'],
            'message' => 'Save this key now! You won\'t be able to see it again.'
        ]);
    }

    /**
     * GET /api/keys - List user's API keys
     */
    public function index() {
        $user = Auth::user();
        if (!$user) {
            Response::unauthorized();
        }

        $keys = $this->apiKeyModel->getUserKeys($user['user_id']);

        // Don't show actual keys, just metadata
        $formatted = array_map(function($key) {
            return [
                'id' => $key['id'],
                'name' => $key['name'],
                'prefix' => $key['prefix'],
                'last_used_at' => $key['last_used_at'],
                'expires_at' => $key['expires_at'],
                'is_active' => (bool)$key['is_active'],
                'created_at' => $key['created_at']
            ];
        }, $keys);

        Response::success($formatted);
    }

    /**
     * DELETE /api/keys/{id} - Revoke API key
     */
    public function destroy($id) {
        $user = Auth::user();
        if (!$user) {
            Response::unauthorized();
        }

        $result = $this->apiKeyModel->revoke($id, $user['user_id']);

        if (!$result) {
            Response::notFound('API key not found');
        }

        Response::noContent();
    }
}
```

### Testing with curl

**Create API key:**
```bash
curl -X POST http://localhost/api/keys \
  -H "Authorization: Bearer your-login-token" \
  -H "Content-Type: application/json" \
  -d '{"name": "Production Server"}'

# Response:
{
  "success": true,
  "data": {
    "id": 1,
    "key": "sk_a3f8d9c1e2b5f7a0c8d3e6f1a9b4c7e2",
    "name": "Production Server",
    "message": "Save this key now! You won't be able to see it again."
  }
}
```

**Use API key:**
```bash
curl http://localhost/api/products \
  -H "X-API-Key: sk_a3f8d9c1e2b5f7a0c8d3e6f1a9b4c7e2"
```

**List keys:**
```bash
curl http://localhost/api/keys \
  -H "Authorization: Bearer your-login-token"
```

**Revoke key:**
```bash
curl -X DELETE http://localhost/api/keys/1 \
  -H "Authorization: Bearer your-login-token"
```

---

## Method 2: Bearer Tokens

### What is a Bearer Token?

Token sent in `Authorization` header:

```
Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...
```

### Simple Token System

```php
<?php
// models/TokenModel.php

class TokenModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Generate random token
     */
    private function generateToken() {
        return bin2hex(random_bytes(32));
    }

    /**
     * Create token for user
     */
    public function create($userId, $expiresInHours = 24) {
        $token = $this->generateToken();
        $expiresAt = date('Y-m-d H:i:s', strtotime("+{$expiresInHours} hours"));

        $stmt = $this->db->prepare("
            INSERT INTO tokens (user_id, token, expires_at)
            VALUES (?, ?, ?)
        ");

        $stmt->execute([$userId, hash('sha256', $token), $expiresAt]);

        return $token; // Return unhashed token
    }

    /**
     * Verify token and return user
     */
    public function verify($providedToken) {
        $hashedToken = hash('sha256', $providedToken);

        $stmt = $this->db->prepare("
            SELECT t.*, u.id, u.name, u.email, u.role
            FROM tokens t
            JOIN users u ON t.user_id = u.id
            WHERE t.token = ?
              AND t.expires_at > NOW()
              AND t.revoked_at IS NULL
        ");

        $stmt->execute([$hashedToken]);
        return $stmt->fetch();
    }

    /**
     * Revoke token
     */
    public function revoke($token) {
        $hashedToken = hash('sha256', $token);

        $stmt = $this->db->prepare("
            UPDATE tokens SET revoked_at = NOW()
            WHERE token = ?
        ");

        return $stmt->execute([$hashedToken]);
    }

    /**
     * Clean up expired tokens
     */
    public function cleanExpired() {
        $stmt = $this->db->query("
            DELETE FROM tokens WHERE expires_at < NOW()
        ");

        return $stmt->rowCount();
    }
}
```

### Database Schema

```sql
CREATE TABLE tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(64) NOT NULL,      -- Hashed token
    expires_at TIMESTAMP NOT NULL,
    revoked_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_token (token),
    INDEX idx_user_id (user_id)
);
```

### Login to Get Token

```php
// controllers/AuthController.php

class AuthController {
    /**
     * POST /api/auth/login
     */
    public function login() {
        $data = Request::json();

        // Validate
        $validator = new Validator($data, [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (!$validator->validate()) {
            Response::validationError($validator->errors());
        }

        // Find user
        $userModel = new UserModel();
        $user = $userModel->findByEmail($data['email']);

        if (!$user || !password_verify($data['password'], $user['password'])) {
            Response::error('Invalid credentials', 401);
        }

        // Generate token
        $tokenModel = new TokenModel();
        $token = $tokenModel->create($user['id'], 24); // 24 hours

        Response::success([
            'token' => $token,
            'type' => 'Bearer',
            'expires_in' => 86400, // seconds (24 hours)
            'user' => [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email']
            ]
        ]);
    }

    /**
     * POST /api/auth/logout
     */
    public function logout() {
        $token = Request::bearerToken();

        if (!$token) {
            Response::error('No token provided', 400);
        }

        $tokenModel = new TokenModel();
        $tokenModel->revoke($token);

        Response::success(['message' => 'Logged out successfully']);
    }

    /**
     * GET /api/auth/me - Get current user
     */
    public function me() {
        $user = Auth::user();

        if (!$user) {
            Response::unauthorized();
        }

        Response::success($user);
    }
}
```

### Bearer Token Middleware

```php
<?php
// middleware/BearerAuthMiddleware.php

class BearerAuthMiddleware {
    public static function handle() {
        $token = Request::bearerToken();

        if (!$token) {
            Response::unauthorized('Token required');
        }

        $tokenModel = new TokenModel();
        $user = $tokenModel->verify($token);

        if (!$user) {
            Response::unauthorized('Invalid or expired token');
        }

        Auth::setUser($user);

        return true;
    }
}
```

### Using Bearer Token

```bash
# Login
curl -X POST http://localhost/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"john@example.com","password":"secret123"}'

# Response:
{
  "success": true,
  "data": {
    "token": "a3f8d9c1e2b5f7a0c8d3e6f1a9b4c7e2d5f8a1b4c7e0d3f6a9b2c5e8f1a4b7c0",
    "type": "Bearer",
    "expires_in": 86400,
    "user": {
      "id": 5,
      "name": "John Doe",
      "email": "john@example.com"
    }
  }
}

# Use token
curl http://localhost/api/posts \
  -H "Authorization: Bearer a3f8d9c1e2b5f7a0c8d3e6f1a9b4c7e2d5f8a1b4c7e0d3f6a9b2c5e8f1a4b7c0"

# Get current user
curl http://localhost/api/auth/me \
  -H "Authorization: Bearer a3f8d9c1e2b5f7a0c8d3e6f1a9b4c7e2d5f8a1b4c7e0d3f6a9b2c5e8f1a4b7c0"

# Logout
curl -X POST http://localhost/api/auth/logout \
  -H "Authorization: Bearer a3f8d9c1e2b5f7a0c8d3e6f1a9b4c7e2d5f8a1b4c7e0d3f6a9b2c5e8f1a4b7c0"
```

---

## Auth Helper Class

```php
<?php
// helpers/Auth.php

class Auth {
    private static $user = null;

    /**
     * Set authenticated user
     */
    public static function setUser($user) {
        self::$user = $user;
    }

    /**
     * Get authenticated user
     */
    public static function user() {
        return self::$user;
    }

    /**
     * Get user ID
     */
    public static function userId() {
        return self::$user['user_id'] ?? self::$user['id'] ?? null;
    }

    /**
     * Check if user is authenticated
     */
    public static function check() {
        return self::$user !== null;
    }

    /**
     * Check if user is guest (not authenticated)
     */
    public static function guest() {
        return self::$user === null;
    }

    /**
     * Check if user has role
     */
    public static function hasRole($role) {
        if (!self::$user) {
            return false;
        }

        return (self::$user['role'] ?? null) === $role;
    }

    /**
     * Check if user is admin
     */
    public static function isAdmin() {
        return self::hasRole('admin');
    }
}
```

---

## Role-Based Access Control

### Admin Middleware

```php
<?php
// middleware/AdminMiddleware.php

class AdminMiddleware {
    public static function handle() {
        if (!Auth::check()) {
            Response::unauthorized();
        }

        if (!Auth::isAdmin()) {
            Response::forbidden('Admin access required');
        }

        return true;
    }
}
```

**Usage:**
```php
// Admin-only routes
$router->get('/api/admin/users', [AdminController::class, 'users'], [
    BearerAuthMiddleware::class,
    AdminMiddleware::class
]);

$router->delete('/api/admin/users/{id}', [AdminController::class, 'deleteUser'], [
    BearerAuthMiddleware::class,
    AdminMiddleware::class
]);
```

---

## Security Best Practices

### 1. Always Hash Keys/Tokens
```php
// ❌ Bad - storing plain text
INSERT INTO tokens (token) VALUES ('abc123')

// ✅ Good - storing hashed
INSERT INTO tokens (token) VALUES (SHA256('abc123'))
```

### 2. Use HTTPS
```php
// Check if HTTPS
if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off') {
    Response::error('HTTPS required', 426); // 426 Upgrade Required
}
```

### 3. Set Token Expiration
```php
// Short-lived tokens
$token = $tokenModel->create($userId, 1); // 1 hour

// Refresh tokens (longer lived)
$refreshToken = $tokenModel->create($userId, 720); // 30 days
```

### 4. Rate Limiting
```php
// Limit login attempts
if (LoginAttempts::exceeded($email)) {
    Response::tooManyRequests('Too many login attempts', 300);
}
```

### 5. Revoke Tokens on Password Change
```php
public function changePassword($userId) {
    // Change password
    $userModel->updatePassword($userId, $newPassword);

    // Revoke all tokens
    $tokenModel->revokeAllForUser($userId);
}
```

---

## Quick Quiz

**Question 1:** What's the difference between API keys and bearer tokens?
<details>
<summary>Answer</summary>
API keys are long-lived, identify applications/services. Bearer tokens are short-lived, identify users after login.
</details>

**Question 2:** Why hash tokens before storing in database?
<details>
<summary>Answer</summary>
If database is compromised, actual tokens aren't exposed. Attackers can't use them to access accounts.
</details>

**Question 3:** Where should API keys be sent?
<details>
<summary>Answer</summary>
In headers like `X-API-Key: your-key`, never in URL (URLs are logged everywhere).
</details>

**Question 4:** What status code for expired/invalid token?
<details>
<summary>Answer</summary>
401 Unauthorized
</details>

**Question 5:** Should you show API keys/tokens after creation?
<details>
<summary>Answer</summary>
Only ONCE during creation. After that, they're hashed and cannot be retrieved.
</details>

---

## Summary

You learned:
- API keys for server-to-server authentication
- Bearer tokens for user authentication
- Generating secure random keys/tokens
- Hashing for secure storage
- Middleware for protecting routes
- Token management (create, verify, revoke)
- Auth helper class for checking user identity
- Role-based access control
- Security best practices

---

## Next Lesson

**09-jwt-basics.md** - Learn about JSON Web Tokens (JWT), a modern token format that's self-contained and doesn't require database lookups!
