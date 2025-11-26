# Lesson 11 - Rate Limiting: Preventing Brute Force & Abuse

**Duration**: 60 minutes

---

## Introduction: The Automatic Attack Problem

Imagine an attacker trying to guess your password. Manually, they might try 10-20 passwords per minute. But with automated tools, they can try **thousands per second**.

**Rate limiting** restricts how many requests a user (or IP) can make in a given timeframe. It prevents:
- **Brute force attacks**: Password guessing, API key guessing
- **Credential stuffing**: Testing leaked passwords from other sites
- **DDoS attacks**: Overwhelming your server
- **Data scraping**: Automated data harvesting
- **Resource abuse**: Preventing spam, excessive API calls

**Real-world examples**:
- **Login forms**: 5 attempts per 15 minutes
- **API endpoints**: 100 requests per hour
- **Password reset**: 3 requests per hour
- **Registration**: 5 accounts per day per IP

---

## Types of Rate Limiting

### 1. Fixed Window

Count requests in fixed time windows:

```
Window 1: 00:00-00:59 → 100 requests allowed
Window 2: 01:00-01:59 → 100 requests allowed
Window 3: 02:00-02:59 → 100 requests allowed
```

**Problem**: Burst at window boundaries
```
00:59 → 100 requests
01:00 → 100 requests
Total: 200 requests in 1 minute!
```

### 2. Sliding Window

Count requests in rolling time period:

```
Request at 01:30
Check: How many requests in last 60 minutes?
If < limit: Allow
If >= limit: Deny
```

**Better**: No burst at boundaries, but more complex to implement.

### 3. Token Bucket

Start with bucket of tokens, consume one per request, refill at fixed rate:

```
Bucket capacity: 100 tokens
Refill rate: 10 tokens/minute
Request consumes: 1 token
```

**Advantage**: Allows bursts while maintaining average rate.

### 4. Leaky Bucket

Requests enter queue, processed at fixed rate:

```
Queue size: 100 requests
Process rate: 10 requests/minute
New request: Add to queue if space available
```

**Advantage**: Smooths traffic, good for API protection.

---

## Implementing Rate Limiting

### Simple File-Based Rate Limiting

```php
<?php
// SimpleRateLimit.php

class SimpleRateLimit {

    private string $storageDir;

    public function __construct(string $storageDir = '/tmp/rate_limit') {
        $this->storageDir = $storageDir;

        if (!is_dir($this->storageDir)) {
            mkdir($this->storageDir, 0755, true);
        }
    }

    /**
     * Check if rate limit exceeded
     *
     * @param string $key Identifier (IP, user ID, etc.)
     * @param int $maxAttempts Maximum attempts allowed
     * @param int $decayMinutes Time window in minutes
     * @return bool True if allowed, false if rate limited
     */
    public function attempt(string $key, int $maxAttempts, int $decayMinutes): bool {
        $file = $this->getFilePath($key);
        $now = time();
        $decay = $decayMinutes * 60;

        // Read existing attempts
        $attempts = $this->readAttempts($file);

        // Remove old attempts outside time window
        $attempts = array_filter($attempts, function($timestamp) use ($now, $decay) {
            return ($now - $timestamp) < $decay;
        });

        // Check if limit exceeded
        if (count($attempts) >= $maxAttempts) {
            return false; // Rate limited
        }

        // Add new attempt
        $attempts[] = $now;

        // Save attempts
        $this->writeAttempts($file, $attempts);

        return true; // Allowed
    }

    /**
     * Get number of remaining attempts
     */
    public function remaining(string $key, int $maxAttempts, int $decayMinutes): int {
        $file = $this->getFilePath($key);
        $now = time();
        $decay = $decayMinutes * 60;

        $attempts = $this->readAttempts($file);

        $attempts = array_filter($attempts, function($timestamp) use ($now, $decay) {
            return ($now - $timestamp) < $decay;
        });

        return max(0, $maxAttempts - count($attempts));
    }

    /**
     * Get seconds until next attempt allowed
     */
    public function availableIn(string $key, int $maxAttempts, int $decayMinutes): int {
        $file = $this->getFilePath($key);
        $now = time();
        $decay = $decayMinutes * 60;

        $attempts = $this->readAttempts($file);

        $attempts = array_filter($attempts, function($timestamp) use ($now, $decay) {
            return ($now - $timestamp) < $decay;
        });

        if (count($attempts) < $maxAttempts) {
            return 0; // Available now
        }

        // Find oldest attempt
        $oldest = min($attempts);

        // Calculate when it expires
        return ($oldest + $decay) - $now;
    }

    /**
     * Clear attempts for key
     */
    public function clear(string $key): void {
        $file = $this->getFilePath($key);
        if (file_exists($file)) {
            unlink($file);
        }
    }

    /**
     * Get file path for key
     */
    private function getFilePath(string $key): string {
        return $this->storageDir . '/' . md5($key) . '.json';
    }

    /**
     * Read attempts from file
     */
    private function readAttempts(string $file): array {
        if (!file_exists($file)) {
            return [];
        }

        $content = file_get_contents($file);
        $attempts = json_decode($content, true);

        return is_array($attempts) ? $attempts : [];
    }

    /**
     * Write attempts to file
     */
    private function writeAttempts(string $file, array $attempts): void {
        file_put_contents($file, json_encode($attempts));
    }
}
```

### Using Simple Rate Limiting

```php
<?php
// login.php
require_once 'SimpleRateLimit.php';

$rateLimit = new SimpleRateLimit();

$identifier = $_SERVER['REMOTE_ADDR']; // IP-based
// OR: $identifier = 'login:' . $_POST['username']; // Username-based

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Check rate limit: 5 attempts per 15 minutes
    if (!$rateLimit->attempt($identifier, 5, 15)) {
        $availableIn = $rateLimit->availableIn($identifier, 5, 15);
        $minutes = ceil($availableIn / 60);

        http_response_code(429); // Too Many Requests
        die("Too many login attempts. Please try again in $minutes minutes.");
    }

    // Proceed with login attempt
    $username = $_POST['username'];
    $password = $_POST['password'];

    if (verifyLogin($username, $password)) {
        // Success - clear rate limit
        $rateLimit->clear($identifier);

        $_SESSION['user_id'] = $userId;
        header('Location: /dashboard.php');
        exit;
    } else {
        // Failed - rate limit already recorded
        $remaining = $rateLimit->remaining($identifier, 5, 15);
        echo "Invalid credentials. $remaining attempts remaining.";
    }
}
?>

<form method="POST">
    <input type="text" name="username" required>
    <input type="password" name="password" required>
    <button type="submit">Login</button>
</form>
```

---

## Database-Based Rate Limiting

For production with multiple servers:

```sql
CREATE TABLE rate_limits (
    id INT PRIMARY KEY AUTO_INCREMENT,
    identifier VARCHAR(255) NOT NULL,
    attempts INT NOT NULL DEFAULT 1,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_identifier_expires (identifier, expires_at)
);
```

```php
<?php
// DatabaseRateLimit.php

class DatabaseRateLimit {

    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Check rate limit
     */
    public function attempt(string $key, int $maxAttempts, int $decayMinutes): bool {
        // Clean up expired entries
        $this->cleanup();

        $expiresAt = date('Y-m-d H:i:s', time() + ($decayMinutes * 60));

        // Get current count
        $stmt = $this->pdo->prepare("
            SELECT SUM(attempts) as total
            FROM rate_limits
            WHERE identifier = ? AND expires_at > NOW()
        ");
        $stmt->execute([$key]);
        $result = $stmt->fetch();
        $current = (int)($result['total'] ?? 0);

        if ($current >= $maxAttempts) {
            return false; // Rate limited
        }

        // Record attempt
        $stmt = $this->pdo->prepare("
            INSERT INTO rate_limits (identifier, attempts, expires_at)
            VALUES (?, 1, ?)
        ");
        $stmt->execute([$key, $expiresAt]);

        return true; // Allowed
    }

    /**
     * Get remaining attempts
     */
    public function remaining(string $key, int $maxAttempts, int $decayMinutes): int {
        $stmt = $this->pdo->prepare("
            SELECT SUM(attempts) as total
            FROM rate_limits
            WHERE identifier = ? AND expires_at > NOW()
        ");
        $stmt->execute([$key]);
        $result = $stmt->fetch();
        $current = (int)($result['total'] ?? 0);

        return max(0, $maxAttempts - $current);
    }

    /**
     * Clear rate limit for key
     */
    public function clear(string $key): void {
        $stmt = $this->pdo->prepare("DELETE FROM rate_limits WHERE identifier = ?");
        $stmt->execute([$key]);
    }

    /**
     * Clean up expired entries
     */
    private function cleanup(): void {
        $this->pdo->exec("DELETE FROM rate_limits WHERE expires_at < NOW()");
    }
}
```

---

## Redis-Based Rate Limiting (Production)

Redis is ideal for rate limiting - fast, supports expiration, atomic operations:

```php
<?php
// RedisRateLimit.php

class RedisRateLimit {

    private Redis $redis;

    public function __construct(Redis $redis) {
        $this->redis = $redis;
    }

    /**
     * Sliding window rate limit
     */
    public function attempt(string $key, int $maxAttempts, int $decaySeconds): bool {
        $now = microtime(true);
        $windowStart = $now - $decaySeconds;

        // Use sorted set with timestamps as scores
        $redisKey = "rate_limit:$key";

        // Remove old entries
        $this->redis->zRemRangeByScore($redisKey, 0, $windowStart);

        // Count current entries
        $current = $this->redis->zCard($redisKey);

        if ($current >= $maxAttempts) {
            return false; // Rate limited
        }

        // Add new entry
        $this->redis->zAdd($redisKey, $now, uniqid('', true));

        // Set expiration on key
        $this->redis->expire($redisKey, $decaySeconds + 10);

        return true; // Allowed
    }

    /**
     * Token bucket algorithm
     */
    public function tokenBucket(string $key, int $capacity, int $refillRate, int $refillPeriod): bool {
        $redisKey = "token_bucket:$key";

        // Get bucket state
        $bucket = $this->redis->get($redisKey);

        if ($bucket === false) {
            // Initialize bucket
            $bucket = json_encode([
                'tokens' => $capacity - 1,
                'last_refill' => microtime(true)
            ]);
            $this->redis->setex($redisKey, 3600, $bucket);
            return true;
        }

        $bucket = json_decode($bucket, true);
        $now = microtime(true);

        // Calculate tokens to refill
        $timePassed = $now - $bucket['last_refill'];
        $refillAmount = floor($timePassed / $refillPeriod) * $refillRate;

        if ($refillAmount > 0) {
            $bucket['tokens'] = min($capacity, $bucket['tokens'] + $refillAmount);
            $bucket['last_refill'] = $now;
        }

        // Check if token available
        if ($bucket['tokens'] < 1) {
            // Update bucket state
            $this->redis->setex($redisKey, 3600, json_encode($bucket));
            return false; // Rate limited
        }

        // Consume token
        $bucket['tokens']--;

        // Update bucket state
        $this->redis->setex($redisKey, 3600, json_encode($bucket));

        return true; // Allowed
    }

    /**
     * Simple counter with expiration
     */
    public function simpleCounter(string $key, int $maxAttempts, int $decaySeconds): bool {
        $redisKey = "counter:$key";

        $current = (int)$this->redis->get($redisKey);

        if ($current >= $maxAttempts) {
            return false; // Rate limited
        }

        // Increment counter
        $this->redis->incr($redisKey);

        // Set expiration on first request
        if ($current === 0) {
            $this->redis->expire($redisKey, $decaySeconds);
        }

        return true; // Allowed
    }

    /**
     * Get remaining attempts
     */
    public function remaining(string $key, int $maxAttempts, int $decaySeconds): int {
        $now = microtime(true);
        $windowStart = $now - $decaySeconds;

        $redisKey = "rate_limit:$key";

        // Remove old entries
        $this->redis->zRemRangeByScore($redisKey, 0, $windowStart);

        // Count current entries
        $current = $this->redis->zCard($redisKey);

        return max(0, $maxAttempts - $current);
    }

    /**
     * Clear rate limit
     */
    public function clear(string $key): void {
        $this->redis->del("rate_limit:$key");
    }
}
```

### Using Redis Rate Limiting

```php
<?php
$redis = new Redis();
$redis->connect('127.0.0.1', 6379);

$rateLimit = new RedisRateLimit($redis);

// Login rate limiting
$ip = $_SERVER['REMOTE_ADDR'];
if (!$rateLimit->attempt("login:$ip", 5, 900)) { // 5 attempts per 15 minutes
    die('Too many login attempts');
}

// API rate limiting with token bucket
$apiKey = $_POST['api_key'];
if (!$rateLimit->tokenBucket("api:$apiKey", 100, 10, 60)) { // 100 capacity, refill 10/minute
    http_response_code(429);
    die('Rate limit exceeded');
}
```

---

## Complete Rate Limiting Middleware

```php
<?php
// RateLimitMiddleware.php

class RateLimitMiddleware {

    private $rateLimit;
    private array $rules = [];

    public function __construct($rateLimit) {
        $this->rateLimit = $rateLimit;
    }

    /**
     * Define rate limit rule
     */
    public function rule(string $name, int $maxAttempts, int $decayMinutes): self {
        $this->rules[$name] = [
            'max_attempts' => $maxAttempts,
            'decay_minutes' => $decayMinutes
        ];
        return $this;
    }

    /**
     * Check rate limit for current request
     */
    public function check(string $ruleName, ?string $identifier = null): void {
        if (!isset($this->rules[$ruleName])) {
            throw new Exception("Rate limit rule '$ruleName' not defined");
        }

        $rule = $this->rules[$ruleName];

        // Use IP if no identifier provided
        if ($identifier === null) {
            $identifier = $this->getIdentifier();
        }

        $key = "$ruleName:$identifier";

        if (!$this->rateLimit->attempt($key, $rule['max_attempts'], $rule['decay_minutes'])) {
            $this->rateLimitExceeded($key, $rule);
        }
    }

    /**
     * Handle rate limit exceeded
     */
    private function rateLimitExceeded(string $key, array $rule): void {
        $remaining = $this->rateLimit->remaining($key, $rule['max_attempts'], $rule['decay_minutes']);
        $availableIn = $this->rateLimit->availableIn($key, $rule['max_attempts'], $rule['decay_minutes']);

        // Set headers
        header('X-RateLimit-Limit: ' . $rule['max_attempts']);
        header('X-RateLimit-Remaining: ' . $remaining);
        header('X-RateLimit-Reset: ' . (time() + $availableIn));
        header('Retry-After: ' . $availableIn);

        http_response_code(429); // Too Many Requests

        // JSON response for APIs
        if (strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false) {
            header('Content-Type: application/json');
            echo json_encode([
                'error' => 'Rate limit exceeded',
                'retry_after' => $availableIn,
                'limit' => $rule['max_attempts'],
                'remaining' => $remaining
            ]);
        } else {
            // HTML response
            $minutes = ceil($availableIn / 60);
            echo "<h1>429 Too Many Requests</h1>";
            echo "<p>You have exceeded the rate limit. Please try again in $minutes minutes.</p>";
        }

        exit;
    }

    /**
     * Get identifier (IP address)
     */
    private function getIdentifier(): string {
        // Check for proxy headers
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            return trim($ips[0]);
        }

        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        }

        return $_SERVER['REMOTE_ADDR'];
    }
}
```

### Using Rate Limit Middleware

```php
<?php
// config.php
require_once 'RateLimitMiddleware.php';
require_once 'SimpleRateLimit.php';

$rateLimit = new SimpleRateLimit();

$rateLimiter = new RateLimitMiddleware($rateLimit);

// Define rules
$rateLimiter
    ->rule('login', 5, 15)          // 5 attempts per 15 minutes
    ->rule('register', 3, 60)       // 3 attempts per hour
    ->rule('password_reset', 3, 60) // 3 attempts per hour
    ->rule('api', 100, 60)          // 100 requests per hour
    ->rule('search', 20, 1);        // 20 searches per minute
```

```php
<?php
// login.php
require_once 'config.php';

// Check rate limit
$rateLimiter->check('login');

// Proceed with login...
```

```php
<?php
// api/endpoint.php
require_once 'config.php';

// User-specific rate limiting
$apiKey = $_SERVER['HTTP_X_API_KEY'] ?? '';
$rateLimiter->check('api', "user:$apiKey");

// Process API request...
```

---

## Rate Limiting Best Practices

### 1. Choose Appropriate Limits

```php
// Too strict - frustrates users
'login' => 3 attempts per 5 minutes

// Too loose - doesn't prevent attacks
'login' => 1000 attempts per 1 hour

// Just right
'login' => 5 attempts per 15 minutes
```

### 2. Clear Successful Login Attempts

```php
if (verifyLogin($username, $password)) {
    // Login successful - clear rate limit
    $rateLimit->clear("login:$ip");
    $rateLimit->clear("login:$username");
}
```

### 3. Use Multiple Identifiers

```php
// Rate limit by IP
$rateLimiter->check('login', "ip:{$ip}");

// AND by username
$rateLimiter->check('login', "user:{$username}");

// Prevents distributed attacks and account-specific attacks
```

### 4. Different Limits for Different Actions

```php
$rateLimiter
    ->rule('login', 5, 15)              // Strict for authentication
    ->rule('api_read', 1000, 60)        // Generous for reads
    ->rule('api_write', 100, 60)        // Stricter for writes
    ->rule('password_reset', 3, 60)     // Very strict for sensitive actions
    ->rule('contact_form', 5, 1440);    // 5 per day to prevent spam
```

### 5. Inform Users

```php
if (!$rateLimit->attempt($key, 5, 15)) {
    $remaining = $rateLimit->remaining($key, 5, 15);
    $availableIn = $rateLimit->availableIn($key, 5, 15);

    http_response_code(429);

    // Tell user exactly when they can try again
    echo "Too many attempts. Please try again in " . ceil($availableIn / 60) . " minutes.";

    // Also show in headers for APIs
    header("X-RateLimit-Remaining: $remaining");
    header("Retry-After: $availableIn");
}
```

### 6. Whitelist Trusted IPs

```php
$trustedIps = ['203.0.113.10', '198.51.100.20'];

if (in_array($_SERVER['REMOTE_ADDR'], $trustedIps)) {
    // Skip rate limiting for trusted IPs (internal systems, monitoring, etc.)
} else {
    $rateLimiter->check('api');
}
```

---

## Rate Limiting for Different Scenarios

### Login Protection

```php
$ip = $_SERVER['REMOTE_ADDR'];
$username = $_POST['username'];

// Rate limit by IP (prevent distributed brute force)
if (!$rateLimit->attempt("login_ip:$ip", 10, 15)) {
    die('Too many login attempts from your IP');
}

// Rate limit by username (prevent targeted attack)
if (!$rateLimit->attempt("login_user:$username", 5, 15)) {
    die('Too many attempts for this account');
}
```

### API Protection

```php
$apiKey = $_SERVER['HTTP_X_API_KEY'];

// Different limits for different tiers
if ($user['plan'] === 'free') {
    $maxRequests = 100;
} elseif ($user['plan'] === 'pro') {
    $maxRequests = 1000;
} else {
    $maxRequests = 10000;
}

if (!$rateLimit->attempt("api:$apiKey", $maxRequests, 60)) {
    http_response_code(429);
    die(json_encode(['error' => 'Rate limit exceeded']));
}
```

### Password Reset Protection

```php
$email = $_POST['email'];

// Strict limit to prevent abuse
if (!$rateLimit->attempt("password_reset:$email", 3, 60)) {
    die('Too many password reset requests. Please try again later.');
}

// Send reset email...
```

### Comment/Post Submission

```php
$userId = $_SESSION['user_id'];

// Prevent spam
if (!$rateLimit->attempt("comment:$userId", 10, 60)) {
    die('You are posting too quickly. Please slow down.');
}

// Save comment...
```

---

## Rate Limiting Checklist

- [ ] **Login forms**: 5-10 attempts per 15 minutes
- [ ] **Password reset**: 3 attempts per hour
- [ ] **Registration**: 3-5 per day per IP
- [ ] **API endpoints**: Based on plan (100-10000/hour)
- [ ] **Comment/posts**: 10-20 per hour
- [ ] **Contact forms**: 5 per day per IP
- [ ] **File uploads**: 10-50 per hour
- [ ] **Search**: 20-100 per minute
- [ ] **Clear on success**: Reset counter after successful login
- [ ] **Inform users**: Show remaining attempts and retry time
- [ ] **Log rate limit events**: Monitor for attack patterns
- [ ] **Use Redis in production**: For performance and scalability

---

## Key Takeaways

1. **Rate limiting prevents automated attacks** - Brute force, credential stuffing, DDoS
2. **Multiple strategies**: Fixed window, sliding window, token bucket
3. **Choose appropriate limits** - Balance security with user experience
4. **Multiple identifiers**: IP + username for comprehensive protection
5. **Clear on success**: Reset after successful login
6. **Inform users**: Tell them when they can try again
7. **Use Redis in production**: Fast, scalable, atomic operations
8. **Different limits for different actions**: Login stricter than API reads
9. **Monitor and adjust**: Track rate limit hits, adjust limits as needed
10. **Defense in depth**: Rate limiting + other security measures

---

## What's Next?

Final lesson coming up: **Security Checklist** - a comprehensive audit checklist to ensure your application is secure against all the vulnerabilities we've covered in this module.

You'll get:
- Complete security audit checklist
- Common vulnerabilities review
- Testing procedures
- Deployment security
- Ongoing security practices
