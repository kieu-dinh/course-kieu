# Lesson 09 - JSON Web Tokens (JWT)

**Duration**: 60-75 minutes

---

## What is JWT?

**JWT** (JSON Web Token) is a compact, self-contained token format that carries information about the user.

**Key difference from regular tokens:**
- Regular token: Random string, need database lookup to find user
- JWT: Contains user data, NO database lookup needed!

**Example JWT:**
```
eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VyX2lkIjo1LCJuYW1lIjoiSm9obiIsImV4cCI6MTYxNjE4OTUyM30.SflKxwRJSMeKKF2QT4fwpMeJf36POk6yJV_adQssw5c
```

Looks like gibberish, but it actually contains data!

---

## JWT Structure

JWT has 3 parts separated by dots (`.`):

```
HEADER.PAYLOAD.SIGNATURE
```

### 1. Header

Contains token type and algorithm:

```json
{
  "alg": "HS256",
  "typ": "JWT"
}
```

### 2. Payload

Contains the data (called "claims"):

```json
{
  "user_id": 5,
  "name": "John Doe",
  "email": "john@example.com",
  "role": "admin",
  "iat": 1706176800,  // Issued at
  "exp": 1706263200   // Expires at
}
```

### 3. Signature

Proves token hasn't been tampered with:

```
HMACSHA256(
  base64UrlEncode(header) + "." + base64UrlEncode(payload),
  secret_key
)
```

**Full JWT:**
```
eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9           ← Header (base64)
.
eyJ1c2VyX2lkIjo1LCJuYW1lIjoiSm9obiJ9           ← Payload (base64)
.
SflKxwRJSMeKKF2QT4fwpMeJf36POk6yJV_adQssw5c   ← Signature
```

---

## How JWT Works

### Creating JWT

1. Create header (algorithm + type)
2. Create payload (user data + expiration)
3. Encode header and payload to base64
4. Sign with secret key
5. Combine: `header.payload.signature`

### Verifying JWT

1. Split token into 3 parts
2. Decode header and payload
3. Re-create signature using secret key
4. Compare signatures
5. If match → token is valid and hasn't been modified!

**Important:** JWT is NOT encrypted! Anyone can decode and read the payload. The signature just proves it hasn't been changed.

---

## Building JWT in Pure PHP

### JWT Helper Class

```php
<?php
// helpers/JWT.php

class JWT {
    private static $secret = 'your-super-secret-key-change-this-in-production';

    /**
     * Generate JWT token
     */
    public static function encode($payload, $expiresInHours = 24) {
        // Add standard claims
        $payload['iat'] = time(); // Issued at
        $payload['exp'] = time() + ($expiresInHours * 3600); // Expiration

        // Create header
        $header = [
            'alg' => 'HS256',
            'typ' => 'JWT'
        ];

        // Encode header and payload
        $base64UrlHeader = self::base64UrlEncode(json_encode($header));
        $base64UrlPayload = self::base64UrlEncode(json_encode($payload));

        // Create signature
        $signature = hash_hmac(
            'sha256',
            $base64UrlHeader . '.' . $base64UrlPayload,
            self::$secret,
            true
        );

        $base64UrlSignature = self::base64UrlEncode($signature);

        // Create JWT
        return $base64UrlHeader . '.' . $base64UrlPayload . '.' . $base64UrlSignature;
    }

    /**
     * Decode and verify JWT token
     */
    public static function decode($token) {
        // Split token
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            return null; // Invalid format
        }

        [$base64UrlHeader, $base64UrlPayload, $base64UrlSignature] = $parts;

        // Decode payload
        $payload = json_decode(self::base64UrlDecode($base64UrlPayload), true);

        if (!$payload) {
            return null; // Invalid payload
        }

        // Check expiration
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            return null; // Token expired
        }

        // Verify signature
        $signature = self::base64UrlDecode($base64UrlSignature);
        $expectedSignature = hash_hmac(
            'sha256',
            $base64UrlHeader . '.' . $base64UrlPayload,
            self::$secret,
            true
        );

        if (!hash_equals($expectedSignature, $signature)) {
            return null; // Invalid signature
        }

        return $payload;
    }

    /**
     * Validate token (returns true/false)
     */
    public static function validate($token) {
        return self::decode($token) !== null;
    }

    /**
     * Base64 URL encode
     */
    private static function base64UrlEncode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Base64 URL decode
     */
    private static function base64UrlDecode($data) {
        return base64_decode(strtr($data, '-_', '+/'));
    }

    /**
     * Set secret key
     */
    public static function setSecret($secret) {
        self::$secret = $secret;
    }
}
```

---

## Using JWT for Authentication

### Login and Generate JWT

```php
<?php
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

        // Generate JWT
        $token = JWT::encode([
            'user_id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role']
        ], 24); // 24 hours

        Response::success([
            'token' => $token,
            'type' => 'Bearer',
            'expires_in' => 86400, // 24 hours in seconds
            'user' => [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'role' => $user['role']
            ]
        ]);
    }

    /**
     * GET /api/auth/me - Get current user from JWT
     */
    public function me() {
        $user = Auth::user();

        if (!$user) {
            Response::unauthorized();
        }

        Response::success($user);
    }

    /**
     * POST /api/auth/refresh - Refresh token
     */
    public function refresh() {
        $token = Request::bearerToken();

        if (!$token) {
            Response::error('Token required', 400);
        }

        $payload = JWT::decode($token);

        if (!$payload) {
            Response::unauthorized('Invalid token');
        }

        // Generate new token with same user data
        $newToken = JWT::encode([
            'user_id' => $payload['user_id'],
            'name' => $payload['name'],
            'email' => $payload['email'],
            'role' => $payload['role']
        ], 24);

        Response::success([
            'token' => $newToken,
            'type' => 'Bearer',
            'expires_in' => 86400
        ]);
    }
}
```

### JWT Middleware

```php
<?php
// middleware/JWTMiddleware.php

class JWTMiddleware {
    public static function handle() {
        // Get token from Authorization header
        $token = Request::bearerToken();

        if (!$token) {
            Response::unauthorized('Token required');
        }

        // Decode and verify token
        $payload = JWT::decode($token);

        if (!$payload) {
            Response::unauthorized('Invalid or expired token');
        }

        // Set user in Auth
        Auth::setUser($payload);

        return true;
    }
}
```

### Using JWT Middleware

```php
// routes/api.php

// Public routes
$router->post('/api/auth/login', [AuthController::class, 'login']);
$router->post('/api/auth/register', [AuthController::class, 'register']);

// Protected routes (require JWT)
$router->get('/api/auth/me', [AuthController::class, 'me'], [
    JWTMiddleware::class
]);

$router->post('/api/auth/refresh', [AuthController::class, 'refresh'], [
    JWTMiddleware::class
]);

$router->post('/api/posts', [PostController::class, 'store'], [
    JWTMiddleware::class
]);

$router->delete('/api/posts/{id}', [PostController::class, 'destroy'], [
    JWTMiddleware::class
]);
```

---

## Testing JWT

### Login and Get Token

```bash
curl -X POST http://localhost/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "secret123"
  }'

# Response:
{
  "success": true,
  "data": {
    "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VyX2lkIjo1LCJuYW1lIjoiSm9obiIsImVtYWlsIjoiam9obkBleGFtcGxlLmNvbSIsInJvbGUiOiJ1c2VyIiwiaWF0IjoxNzA2MTc2ODAwLCJleHAiOjE3MDYyNjMyMDB9.signature",
    "type": "Bearer",
    "expires_in": 86400
  }
}
```

### Use Token

```bash
curl http://localhost/api/posts \
  -H "Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
```

### Decode Token (for debugging)

Visit [jwt.io](https://jwt.io) and paste your token to see the decoded payload!

---

## JWT Claims (Payload Fields)

### Standard Claims (Registered)

```json
{
  "iss": "your-app.com",       // Issuer
  "sub": "user-5",              // Subject (user ID)
  "aud": "your-app-users",      // Audience
  "exp": 1706263200,            // Expiration (timestamp)
  "nbf": 1706176800,            // Not Before (timestamp)
  "iat": 1706176800,            // Issued At (timestamp)
  "jti": "unique-token-id"      // JWT ID (unique identifier)
}
```

### Custom Claims (Your Data)

```json
{
  "user_id": 5,
  "name": "John Doe",
  "email": "john@example.com",
  "role": "admin",
  "permissions": ["create", "edit", "delete"]
}
```

### Example with All Claims

```php
$token = JWT::encode([
    // Standard claims
    'iss' => 'myapp.com',
    'sub' => 'user-' . $user['id'],
    'aud' => 'myapp-users',
    'jti' => uniqid(),

    // Custom claims
    'user_id' => $user['id'],
    'name' => $user['name'],
    'email' => $user['email'],
    'role' => $user['role'],
    'permissions' => $user['permissions']
], 24);
```

---

## JWT vs Regular Tokens

### Regular Token (Database Lookup)

**Pros:**
- Can revoke immediately
- Can track usage
- More control

**Cons:**
- Database query on every request
- Slower
- Database can become bottleneck

```php
// Every request needs DB lookup
$token = "abc123...";
$user = $db->query("SELECT * FROM tokens WHERE token = ?", [$token]);
```

### JWT (No Database Lookup)

**Pros:**
- No database query needed
- Faster
- Scalable
- Stateless

**Cons:**
- Can't revoke immediately (until expiration)
- Token can get large if too much data
- Less control

```php
// No DB lookup!
$token = "eyJhbGciOi...";
$user = JWT::decode($token); // Just decodes, no DB
```

---

## JWT Best Practices

### 1. Keep Secret Key Secret!

```php
// ❌ Bad - hardcoded
private static $secret = 'my-secret-key';

// ✅ Good - from environment
private static $secret;

public static function init() {
    self::$secret = getenv('JWT_SECRET');

    if (!self::$secret) {
        throw new Exception('JWT_SECRET not configured');
    }
}
```

### 2. Use Short Expiration

```php
// ❌ Bad - long lived
$token = JWT::encode($payload, 720); // 30 days

// ✅ Good - short lived
$token = JWT::encode($payload, 1); // 1 hour

// With refresh token
$accessToken = JWT::encode($payload, 1);        // 1 hour
$refreshToken = JWT::encode($payload, 168);     // 7 days
```

### 3. Don't Store Sensitive Data

```php
// ❌ Bad - storing password hash
$token = JWT::encode([
    'user_id' => $user['id'],
    'password' => $user['password']  // Never!
]);

// ✅ Good - only necessary data
$token = JWT::encode([
    'user_id' => $user['id'],
    'name' => $user['name'],
    'role' => $user['role']
]);
```

### 4. Validate All Claims

```php
public static function decode($token) {
    $payload = self::decodeToken($token);

    // Validate expiration
    if ($payload['exp'] < time()) {
        return null;
    }

    // Validate issuer
    if ($payload['iss'] !== 'myapp.com') {
        return null;
    }

    // Validate audience
    if ($payload['aud'] !== 'myapp-users') {
        return null;
    }

    return $payload;
}
```

### 5. Use HTTPS Always

JWT in HTTP = anyone can steal it and impersonate user!

---

## Token Refresh Strategy

### Access Token + Refresh Token

**Access Token:** Short-lived (1 hour), used for API requests
**Refresh Token:** Long-lived (7 days), used to get new access token

```php
public function login() {
    // ... authenticate user ...

    // Access token (short)
    $accessToken = JWT::encode([
        'user_id' => $user['id'],
        'type' => 'access'
    ], 1); // 1 hour

    // Refresh token (longer)
    $refreshToken = JWT::encode([
        'user_id' => $user['id'],
        'type' => 'refresh'
    ], 168); // 7 days

    Response::success([
        'access_token' => $accessToken,
        'refresh_token' => $refreshToken,
        'expires_in' => 3600
    ]);
}

public function refresh() {
    $refreshToken = Request::input('refresh_token');

    $payload = JWT::decode($refreshToken);

    if (!$payload || $payload['type'] !== 'refresh') {
        Response::unauthorized('Invalid refresh token');
    }

    // Generate new access token
    $newAccessToken = JWT::encode([
        'user_id' => $payload['user_id'],
        'type' => 'access'
    ], 1);

    Response::success([
        'access_token' => $newAccessToken,
        'expires_in' => 3600
    ]);
}
```

---

## JWT Blacklist (Revocation)

JWT can't be revoked by default, but we can maintain a blacklist:

```php
<?php
// models/JWTBlacklist.php

class JWTBlacklist {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Add token to blacklist
     */
    public function add($token, $expiresAt) {
        $stmt = $this->db->prepare("
            INSERT INTO jwt_blacklist (token, expires_at)
            VALUES (?, ?)
        ");

        return $stmt->execute([
            hash('sha256', $token),
            date('Y-m-d H:i:s', $expiresAt)
        ]);
    }

    /**
     * Check if token is blacklisted
     */
    public function isBlacklisted($token) {
        $stmt = $this->db->prepare("
            SELECT id FROM jwt_blacklist
            WHERE token = ? AND expires_at > NOW()
        ");

        $stmt->execute([hash('sha256', $token)]);
        return $stmt->fetch() !== false;
    }

    /**
     * Clean expired entries
     */
    public function cleanExpired() {
        return $this->db->query("
            DELETE FROM jwt_blacklist WHERE expires_at < NOW()
        ");
    }
}
```

**Database:**
```sql
CREATE TABLE jwt_blacklist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    token VARCHAR(64) NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_token (token),
    INDEX idx_expires (expires_at)
);
```

**Check in Middleware:**
```php
public static function handle() {
    $token = Request::bearerToken();

    if (!$token) {
        Response::unauthorized('Token required');
    }

    // Check blacklist
    $blacklist = new JWTBlacklist();
    if ($blacklist->isBlacklisted($token)) {
        Response::unauthorized('Token has been revoked');
    }

    $payload = JWT::decode($token);

    if (!$payload) {
        Response::unauthorized('Invalid token');
    }

    Auth::setUser($payload);
}
```

**Logout (revoke):**
```php
public function logout() {
    $token = Request::bearerToken();

    if (!$token) {
        Response::error('No token provided', 400);
    }

    $payload = JWT::decode($token);

    // Add to blacklist
    $blacklist = new JWTBlacklist();
    $blacklist->add($token, $payload['exp']);

    Response::success(['message' => 'Logged out successfully']);
}
```

---

## Using PHP JWT Libraries

For production, consider using well-tested libraries:

### Firebase JWT

```bash
composer require firebase/php-jwt
```

```php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Encode
$token = JWT::encode($payload, $secretKey, 'HS256');

// Decode
try {
    $decoded = JWT::decode($token, new Key($secretKey, 'HS256'));
} catch (Exception $e) {
    // Invalid token
}
```

---

## Quick Quiz

**Question 1:** Can you read the payload of a JWT without the secret key?
<details>
<summary>Answer</summary>
Yes! JWT is base64 encoded, not encrypted. Anyone can decode and read it. The signature just proves it hasn't been modified.
</details>

**Question 2:** What's the main advantage of JWT over regular tokens?
<details>
<summary>Answer</summary>
No database lookup needed. User data is in the token itself, making it faster and more scalable.
</details>

**Question 3:** Can you revoke a JWT immediately?
<details>
<summary>Answer</summary>
Not by default. JWT is stateless. You need to implement a blacklist (database) to revoke tokens before expiration.
</details>

**Question 4:** Should you store passwords in JWT payload?
<details>
<summary>Answer</summary>
NEVER! JWT is not encrypted. Anyone can decode and read the payload. Only store non-sensitive data.
</details>

**Question 5:** What happens if you change the JWT secret key?
<details>
<summary>Answer</summary>
All existing tokens become invalid immediately (signatures won't match). Users need to log in again.
</details>

---

## Summary

You learned:
- What JWT is and how it works (header, payload, signature)
- Building JWT encoder/decoder in pure PHP
- Using JWT for authentication
- JWT middleware for protected routes
- Standard and custom claims
- JWT vs regular tokens (pros/cons)
- Best practices (short expiration, HTTPS, no sensitive data)
- Token refresh strategy
- JWT blacklist for revocation
- Using production JWT libraries

---

## Next Lesson

**10-pagination.md** - Handle large datasets efficiently with pagination, limiting, and cursors!
