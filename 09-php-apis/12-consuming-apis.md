# Lesson 12 - Consuming External APIs with cURL

**Duration**: 60-75 minutes

---

## Why Consume External APIs?

Your application often needs external services:
- **Payments**: Stripe, PayPal
- **Email**: SendGrid, Mailgun
- **SMS**: Twilio
- **Maps**: Google Maps
- **Weather**: OpenWeatherMap
- **Currency**: Exchange rates
- **Social**: Twitter, Facebook APIs

---

## What is cURL?

**cURL** = Client URL - a PHP extension for making HTTP requests.

**What it can do:**
- Make GET, POST, PUT, DELETE requests
- Send headers (Authorization, Content-Type)
- Send JSON/form data
- Handle responses
- Follow redirects
- Upload files

---

## Basic cURL Request

### Simple GET Request

```php
<?php

// Initialize cURL
$ch = curl_init();

// Set URL
curl_setopt($ch, CURLOPT_URL, 'https://api.github.com/users/octocat');

// Return response instead of outputting
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Execute request
$response = curl_exec($ch);

// Check for errors
if (curl_errno($ch)) {
    echo 'cURL Error: ' . curl_error($ch);
} else {
    // Parse JSON response
    $data = json_decode($response, true);
    print_r($data);
}

// Close cURL
curl_close($ch);
```

---

## cURL Helper Class

Reusable class for HTTP requests:

```php
<?php
// helpers/HttpClient.php

class HttpClient {
    private $baseUrl;
    private $headers = [];
    private $timeout = 30;

    public function __construct($baseUrl = '') {
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    /**
     * Set base URL
     */
    public function setBaseUrl($url) {
        $this->baseUrl = rtrim($url, '/');
        return $this;
    }

    /**
     * Set request timeout
     */
    public function setTimeout($seconds) {
        $this->timeout = $seconds;
        return $this;
    }

    /**
     * Set header
     */
    public function setHeader($key, $value) {
        $this->headers[$key] = $value;
        return $this;
    }

    /**
     * Set authorization header
     */
    public function setAuth($token, $type = 'Bearer') {
        $this->headers['Authorization'] = "{$type} {$token}";
        return $this;
    }

    /**
     * GET request
     */
    public function get($endpoint, $params = []) {
        $url = $this->buildUrl($endpoint, $params);
        return $this->request('GET', $url);
    }

    /**
     * POST request
     */
    public function post($endpoint, $data = []) {
        $url = $this->buildUrl($endpoint);
        return $this->request('POST', $url, $data);
    }

    /**
     * PUT request
     */
    public function put($endpoint, $data = []) {
        $url = $this->buildUrl($endpoint);
        return $this->request('PUT', $url, $data);
    }

    /**
     * DELETE request
     */
    public function delete($endpoint) {
        $url = $this->buildUrl($endpoint);
        return $this->request('DELETE', $url);
    }

    /**
     * Execute cURL request
     */
    private function request($method, $url, $data = null) {
        $ch = curl_init();

        // Set URL
        curl_setopt($ch, CURLOPT_URL, $url);

        // Set method
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        // Return response
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Set timeout
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);

        // Return headers
        curl_setopt($ch, CURLOPT_HEADER, true);

        // Follow redirects
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        // Headers
        $headers = $this->formatHeaders();
        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        // Request body
        if ($data !== null) {
            if (isset($this->headers['Content-Type']) &&
                $this->headers['Content-Type'] === 'application/json') {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            } else {
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            }
        }

        // Execute
        $response = curl_exec($ch);

        // Get info
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);

        // Check for errors
        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new Exception("cURL Error: {$error}");
        }

        curl_close($ch);

        // Parse response
        $responseHeaders = substr($response, 0, $headerSize);
        $responseBody = substr($response, $headerSize);

        return [
            'status' => $statusCode,
            'headers' => $this->parseHeaders($responseHeaders),
            'body' => $responseBody,
            'json' => json_decode($responseBody, true)
        ];
    }

    /**
     * Build full URL with query parameters
     */
    private function buildUrl($endpoint, $params = []) {
        $url = $this->baseUrl . '/' . ltrim($endpoint, '/');

        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        return $url;
    }

    /**
     * Format headers for cURL
     */
    private function formatHeaders() {
        $formatted = [];

        foreach ($this->headers as $key => $value) {
            $formatted[] = "{$key}: {$value}";
        }

        return $formatted;
    }

    /**
     * Parse response headers
     */
    private function parseHeaders($headerString) {
        $headers = [];
        $lines = explode("\r\n", $headerString);

        foreach ($lines as $line) {
            if (strpos($line, ':') !== false) {
                [$key, $value] = explode(':', $line, 2);
                $headers[trim($key)] = trim($value);
            }
        }

        return $headers;
    }
}
```

---

## Using HttpClient

### Basic GET Request

```php
$client = new HttpClient('https://api.github.com');

try {
    $response = $client->get('/users/octocat');

    if ($response['status'] === 200) {
        $user = $response['json'];
        echo "Name: " . $user['name'];
        echo "Followers: " . $user['followers'];
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
```

### POST with JSON

```php
$client = new HttpClient('https://api.example.com');
$client->setHeader('Content-Type', 'application/json');
$client->setAuth('your-api-key');

$response = $client->post('/posts', [
    'title' => 'My Post',
    'content' => 'Hello World'
]);

if ($response['status'] === 201) {
    echo "Post created!";
    print_r($response['json']);
}
```

### GET with Query Parameters

```php
$client = new HttpClient('https://api.openweathermap.org/data/2.5');

$response = $client->get('/weather', [
    'q' => 'Paris',
    'appid' => 'your-api-key',
    'units' => 'metric'
]);

$weather = $response['json'];
echo "Temperature: " . $weather['main']['temp'] . "°C";
```

---

## Real-World Examples

### Example 1: Weather API

```php
<?php
// services/WeatherService.php

class WeatherService {
    private $client;
    private $apiKey;

    public function __construct($apiKey) {
        $this->apiKey = $apiKey;
        $this->client = new HttpClient('https://api.openweathermap.org/data/2.5');
    }

    /**
     * Get current weather for city
     */
    public function getCurrentWeather($city) {
        try {
            $response = $this->client->get('/weather', [
                'q' => $city,
                'appid' => $this->apiKey,
                'units' => 'metric'
            ]);

            if ($response['status'] !== 200) {
                throw new Exception('Weather API error');
            }

            $data = $response['json'];

            return [
                'city' => $data['name'],
                'country' => $data['sys']['country'],
                'temperature' => $data['main']['temp'],
                'feels_like' => $data['main']['feels_like'],
                'humidity' => $data['main']['humidity'],
                'description' => $data['weather'][0]['description'],
                'icon' => $data['weather'][0]['icon']
            ];

        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Get 5-day forecast
     */
    public function getForecast($city) {
        try {
            $response = $this->client->get('/forecast', [
                'q' => $city,
                'appid' => $this->apiKey,
                'units' => 'metric'
            ]);

            if ($response['status'] !== 200) {
                throw new Exception('Forecast API error');
            }

            $data = $response['json'];

            $forecast = [];
            foreach ($data['list'] as $item) {
                $forecast[] = [
                    'date' => $item['dt_txt'],
                    'temperature' => $item['main']['temp'],
                    'description' => $item['weather'][0]['description']
                ];
            }

            return $forecast;

        } catch (Exception $e) {
            return [];
        }
    }
}
```

**Usage:**
```php
// API endpoint
$router->get('/api/weather/{city}', function($city) {
    $weatherService = new WeatherService(getenv('WEATHER_API_KEY'));
    $weather = $weatherService->getCurrentWeather($city);

    if (!$weather) {
        Response::notFound('City not found');
    }

    Response::success($weather);
});
```

### Example 2: Currency Exchange API

```php
<?php
// services/CurrencyService.php

class CurrencyService {
    private $client;
    private $apiKey;

    public function __construct($apiKey) {
        $this->apiKey = $apiKey;
        $this->client = new HttpClient('https://api.exchangerate-api.com/v4');
    }

    /**
     * Get exchange rates for base currency
     */
    public function getRates($baseCurrency = 'USD') {
        try {
            $response = $this->client->get("/latest/{$baseCurrency}");

            if ($response['status'] !== 200) {
                throw new Exception('Exchange rate API error');
            }

            return $response['json']['rates'];

        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Convert amount from one currency to another
     */
    public function convert($amount, $from, $to) {
        $rates = $this->getRates($from);

        if (!$rates || !isset($rates[$to])) {
            return null;
        }

        return [
            'from' => $from,
            'to' => $to,
            'amount' => $amount,
            'converted' => $amount * $rates[$to],
            'rate' => $rates[$to]
        ];
    }
}
```

### Example 3: GitHub API

```php
<?php
// services/GitHubService.php

class GitHubService {
    private $client;

    public function __construct($token = null) {
        $this->client = new HttpClient('https://api.github.com');
        $this->client->setHeader('Accept', 'application/vnd.github.v3+json');
        $this->client->setHeader('User-Agent', 'PHP-App');

        if ($token) {
            $this->client->setAuth($token);
        }
    }

    /**
     * Get user info
     */
    public function getUser($username) {
        $response = $this->client->get("/users/{$username}");

        if ($response['status'] !== 200) {
            return null;
        }

        return $response['json'];
    }

    /**
     * Get user repositories
     */
    public function getUserRepos($username) {
        $response = $this->client->get("/users/{$username}/repos", [
            'sort' => 'updated',
            'per_page' => 10
        ]);

        if ($response['status'] !== 200) {
            return [];
        }

        return $response['json'];
    }

    /**
     * Search repositories
     */
    public function searchRepos($query) {
        $response = $this->client->get('/search/repositories', [
            'q' => $query,
            'sort' => 'stars',
            'order' => 'desc'
        ]);

        if ($response['status'] !== 200) {
            return [];
        }

        return $response['json']['items'];
    }
}
```

---

## Error Handling

### Handling HTTP Errors

```php
try {
    $response = $client->get('/users/invalid');

    if ($response['status'] >= 400 && $response['status'] < 500) {
        // Client error (4xx)
        echo "Client error: " . $response['status'];
    } elseif ($response['status'] >= 500) {
        // Server error (5xx)
        echo "Server error: " . $response['status'];
    }

} catch (Exception $e) {
    // Network error, timeout, etc.
    echo "Request failed: " . $e->getMessage();
}
```

### Retry Logic

```php
function requestWithRetry($client, $method, $endpoint, $maxRetries = 3) {
    $attempt = 0;

    while ($attempt < $maxRetries) {
        try {
            $response = $client->$method($endpoint);

            // Success
            if ($response['status'] < 500) {
                return $response;
            }

            // Server error - retry
            $attempt++;
            sleep(pow(2, $attempt)); // Exponential backoff: 2s, 4s, 8s

        } catch (Exception $e) {
            $attempt++;
            if ($attempt >= $maxRetries) {
                throw $e;
            }
            sleep(pow(2, $attempt));
        }
    }

    throw new Exception('Max retries exceeded');
}
```

---

## Caching API Responses

Save bandwidth and speed up responses:

```php
<?php
// helpers/ApiCache.php

class ApiCache {
    private $cacheDir = __DIR__ . '/../cache/api';

    public function __construct() {
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }

    /**
     * Get cached response
     */
    public function get($key) {
        $file = $this->getCacheFile($key);

        if (!file_exists($file)) {
            return null;
        }

        $cached = json_decode(file_get_contents($file), true);

        // Check expiration
        if ($cached['expires_at'] < time()) {
            unlink($file);
            return null;
        }

        return $cached['data'];
    }

    /**
     * Store response in cache
     */
    public function set($key, $data, $ttl = 3600) {
        $file = $this->getCacheFile($key);

        $cached = [
            'data' => $data,
            'expires_at' => time() + $ttl
        ];

        file_put_contents($file, json_encode($cached));
    }

    /**
     * Get cache file path
     */
    private function getCacheFile($key) {
        $hash = md5($key);
        return $this->cacheDir . '/' . $hash . '.json';
    }
}
```

**Usage with API:**
```php
$cache = new ApiCache();
$cacheKey = "weather:paris";

// Try cache first
$weather = $cache->get($cacheKey);

if (!$weather) {
    // Cache miss - call API
    $weatherService = new WeatherService($apiKey);
    $weather = $weatherService->getCurrentWeather('Paris');

    // Cache for 30 minutes
    $cache->set($cacheKey, $weather, 1800);
}

Response::success($weather);
```

---

## Rate Limiting for API Calls

Respect external API rate limits:

```php
<?php
// helpers/RateLimiter.php

class RateLimiter {
    private $storageDir = __DIR__ . '/../storage/rate-limits';

    public function __construct() {
        if (!is_dir($this->storageDir)) {
            mkdir($this->storageDir, 0755, true);
        }
    }

    /**
     * Check if rate limit exceeded
     */
    public function check($key, $maxRequests, $windowSeconds) {
        $file = $this->getFile($key);

        // Load request log
        $requests = $this->loadRequests($file);

        // Remove old requests outside window
        $cutoff = time() - $windowSeconds;
        $requests = array_filter($requests, fn($time) => $time > $cutoff);

        // Check limit
        if (count($requests) >= $maxRequests) {
            return false; // Rate limit exceeded
        }

        // Add current request
        $requests[] = time();
        file_put_contents($file, json_encode($requests));

        return true; // Within limit
    }

    private function loadRequests($file) {
        if (!file_exists($file)) {
            return [];
        }

        return json_decode(file_get_contents($file), true) ?? [];
    }

    private function getFile($key) {
        return $this->storageDir . '/' . md5($key) . '.json';
    }
}
```

**Usage:**
```php
$limiter = new RateLimiter();
$key = "github:user123";

// GitHub allows 60 requests per hour for unauthenticated
if (!$limiter->check($key, 60, 3600)) {
    Response::tooManyRequests('GitHub API rate limit exceeded. Try again later.');
}

// Make GitHub API call
$github = new GitHubService();
$user = $github->getUser('octocat');
```

---

## Webhook Handling

Sometimes APIs call YOUR endpoint (webhook):

```php
<?php
// controllers/WebhookController.php

class WebhookController {
    /**
     * POST /webhooks/stripe
     * Handle Stripe payment webhook
     */
    public function stripe() {
        // Get raw body (for signature verification)
        $payload = file_get_contents('php://input');
        $signature = Request::header('Stripe-Signature');

        // Verify signature
        if (!$this->verifyStripeSignature($payload, $signature)) {
            Response::unauthorized('Invalid signature');
        }

        // Parse payload
        $event = json_decode($payload, true);

        // Handle event
        switch ($event['type']) {
            case 'payment_intent.succeeded':
                $this->handlePaymentSuccess($event['data']['object']);
                break;

            case 'payment_intent.failed':
                $this->handlePaymentFailure($event['data']['object']);
                break;

            default:
                // Unknown event
                break;
        }

        // Always return 200 to acknowledge receipt
        Response::success(['received' => true]);
    }

    private function verifyStripeSignature($payload, $signature) {
        $secret = getenv('STRIPE_WEBHOOK_SECRET');

        $expectedSignature = hash_hmac('sha256', $payload, $secret);

        return hash_equals($expectedSignature, $signature);
    }

    private function handlePaymentSuccess($paymentIntent) {
        // Update order status, send confirmation email, etc.
        $orderId = $paymentIntent['metadata']['order_id'];

        $order = Order::find($orderId);
        $order->update(['status' => 'paid']);

        // Send email
        // ...
    }
}
```

---

## Quick Quiz

**Question 1:** What does CURLOPT_RETURNTRANSFER do?
<details>
<summary>Answer</summary>
Makes curl_exec() return the response as a string instead of outputting it directly.
</details>

**Question 2:** Why cache API responses?
<details>
<summary>Answer</summary>
To reduce external API calls (save bandwidth, avoid rate limits, speed up responses).
</details>

**Question 3:** How do you send JSON in a POST request?
<details>
<summary>Answer</summary>
Set Content-Type header to application/json and use json_encode() on the data.
</details>

**Question 4:** What's exponential backoff in retry logic?
<details>
<summary>Answer</summary>
Waiting progressively longer between retries (2s, 4s, 8s) to avoid overwhelming the server.
</details>

**Question 5:** Why verify webhook signatures?
<details>
<summary>Answer</summary>
Security - to ensure the webhook actually came from the service (Stripe, etc.) and not a malicious attacker.
</details>

---

## Summary

You learned:
- Using cURL for HTTP requests in PHP
- Building reusable HttpClient class
- Making GET, POST, PUT, DELETE requests
- Setting headers and authentication
- Consuming real APIs (weather, currency, GitHub)
- Error handling and retry logic
- Caching API responses
- Rate limiting for external APIs
- Handling webhooks
- Best practices for API integration

---

## Next Lesson

**13-error-handling.md** - Learn to handle API errors properly with consistent error responses, logging, and debugging!
