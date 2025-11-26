# Lesson 13 - Error Handling in APIs

**Duration**: 45-60 minutes

---

## Why Good Error Handling Matters

Bad error handling:
```json
"Error"
```

Good error handling:
```json
{
  "success": false,
  "error": "User not found",
  "code": "USER_NOT_FOUND",
  "message": "The user with ID 999 does not exist"
}
```

**Benefits:**
- Clients understand what went wrong
- Easier debugging
- Better user experience
- Professional API

---

## Types of Errors

### 1. Client Errors (4xx)

User made a mistake:
- 400 Bad Request - Invalid syntax
- 401 Unauthorized - Not authenticated
- 403 Forbidden - Not allowed
- 404 Not Found - Resource doesn't exist
- 422 Unprocessable - Validation failed
- 429 Too Many Requests - Rate limit exceeded

### 2. Server Errors (5xx)

Something wrong on our end:
- 500 Internal Server Error - Bug in code
- 502 Bad Gateway - Upstream service failed
- 503 Service Unavailable - Server overloaded

---

## Error Response Format

### Standard Error Response

```json
{
  "success": false,
  "error": "Short error message",
  "code": "ERROR_CODE",
  "message": "Detailed explanation of what went wrong",
  "errors": {
    "field1": "Specific error for this field",
    "field2": "Specific error for this field"
  },
  "debug": {
    "file": "/path/to/file.php",
    "line": 42,
    "trace": "..."
  }
}
```

**Note:** Only include `debug` in development mode!

---

## Error Handler Class

```php
<?php
// helpers/ErrorHandler.php

class ErrorHandler {
    private static $isDevelopment;

    public static function init() {
        self::$isDevelopment = getenv('APP_ENV') === 'development';

        // Convert PHP errors to exceptions
        set_error_handler([self::class, 'handleError']);

        // Catch uncaught exceptions
        set_exception_handler([self::class, 'handleException']);

        // Catch fatal errors
        register_shutdown_function([self::class, 'handleShutdown']);
    }

    /**
     * Handle PHP errors (notices, warnings)
     */
    public static function handleError($severity, $message, $file, $line) {
        if (!(error_reporting() & $severity)) {
            return; // Error suppressed with @
        }

        throw new ErrorException($message, 0, $severity, $file, $line);
    }

    /**
     * Handle uncaught exceptions
     */
    public static function handleException($exception) {
        // Log error
        self::logException($exception);

        // Send error response
        http_response_code(500);
        header('Content-Type: application/json');

        $response = [
            'success' => false,
            'error' => 'Internal server error',
            'code' => 'INTERNAL_ERROR'
        ];

        // Add details in development
        if (self::$isDevelopment) {
            $response['message'] = $exception->getMessage();
            $response['debug'] = [
                'exception' => get_class($exception),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString()
            ];
        }

        echo json_encode($response, JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * Handle fatal errors
     */
    public static function handleShutdown() {
        $error = error_get_last();

        if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            self::handleException(
                new ErrorException($error['message'], 0, $error['type'], $error['file'], $error['line'])
            );
        }
    }

    /**
     * Log exception to file
     */
    private static function logException($exception) {
        $logFile = __DIR__ . '/../storage/logs/errors.log';
        $logDir = dirname($logFile);

        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $message = sprintf(
            "[%s] %s: %s in %s:%d\n",
            date('Y-m-d H:i:s'),
            get_class($exception),
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine()
        );

        file_put_contents($logFile, $message, FILE_APPEND);
    }
}
```

**Initialize in entry point:**
```php
// public/index.php

require_once __DIR__ . '/../helpers/ErrorHandler.php';
ErrorHandler::init();

// Rest of your API...
```

---

## Custom Exception Classes

### Base API Exception

```php
<?php
// exceptions/ApiException.php

class ApiException extends Exception {
    protected $statusCode = 500;
    protected $errorCode = 'API_ERROR';
    protected $errors = [];

    public function __construct($message = '', $errors = []) {
        parent::__construct($message);
        $this->errors = $errors;
    }

    public function getStatusCode() {
        return $this->statusCode;
    }

    public function getErrorCode() {
        return $this->errorCode;
    }

    public function getErrors() {
        return $this->errors;
    }

    public function toArray() {
        $response = [
            'success' => false,
            'error' => $this->getMessage() ?: 'An error occurred',
            'code' => $this->errorCode
        ];

        if (!empty($this->errors)) {
            $response['errors'] = $this->errors;
        }

        return $response;
    }
}
```

### Specific Exceptions

```php
<?php
// exceptions/NotFoundException.php
class NotFoundException extends ApiException {
    protected $statusCode = 404;
    protected $errorCode = 'NOT_FOUND';

    public function __construct($message = 'Resource not found') {
        parent::__construct($message);
    }
}

// exceptions/ValidationException.php
class ValidationException extends ApiException {
    protected $statusCode = 422;
    protected $errorCode = 'VALIDATION_ERROR';

    public function __construct($errors, $message = 'Validation failed') {
        parent::__construct($message, $errors);
    }
}

// exceptions/UnauthorizedException.php
class UnauthorizedException extends ApiException {
    protected $statusCode = 401;
    protected $errorCode = 'UNAUTHORIZED';

    public function __construct($message = 'Unauthorized') {
        parent::__construct($message);
    }
}

// exceptions/ForbiddenException.php
class ForbiddenException extends ApiException {
    protected $statusCode = 403;
    protected $errorCode = 'FORBIDDEN';

    public function __construct($message = 'Forbidden') {
        parent::__construct($message);
    }
}

// exceptions/BadRequestException.php
class BadRequestException extends ApiException {
    protected $statusCode = 400;
    protected $errorCode = 'BAD_REQUEST';

    public function __construct($message = 'Bad request') {
        parent::__construct($message);
    }
}

// exceptions/TooManyRequestsException.php
class TooManyRequestsException extends ApiException {
    protected $statusCode = 429;
    protected $errorCode = 'TOO_MANY_REQUESTS';

    public function __construct($message = 'Too many requests') {
        parent::__construct($message);
    }
}
```

---

## Using Custom Exceptions

### In Controllers

```php
<?php
// controllers/PostController.php

class PostController {
    public function show($id) {
        $post = Post::find($id);

        if (!$post) {
            throw new NotFoundException("Post with ID {$id} not found");
        }

        Response::success($post);
    }

    public function store() {
        $data = Request::json();

        // Validate
        $validator = new Validator($data, [
            'title' => 'required|min:5',
            'content' => 'required|min:100'
        ]);

        if (!$validator->validate()) {
            throw new ValidationException($validator->errors());
        }

        // Check authentication
        if (!Auth::check()) {
            throw new UnauthorizedException('You must be logged in to create posts');
        }

        // Create post
        $post = Post::create($data);
        Response::success($post, 201);
    }

    public function destroy($id) {
        $post = Post::find($id);

        if (!$post) {
            throw new NotFoundException("Post not found");
        }

        // Check ownership
        if ($post['author_id'] !== Auth::userId()) {
            throw new ForbiddenException('You can only delete your own posts');
        }

        $post->delete();
        Response::noContent();
    }
}
```

### Exception Handler

Update error handler to catch API exceptions:

```php
public static function handleException($exception) {
    // Log exception
    self::logException($exception);

    // Handle API exceptions
    if ($exception instanceof ApiException) {
        http_response_code($exception->getStatusCode());
        header('Content-Type: application/json');
        echo json_encode($exception->toArray(), JSON_PRETTY_PRINT);
        exit;
    }

    // Handle other exceptions (500 errors)
    http_response_code(500);
    header('Content-Type: application/json');

    $response = [
        'success' => false,
        'error' => 'Internal server error',
        'code' => 'INTERNAL_ERROR'
    ];

    if (self::$isDevelopment) {
        $response['message'] = $exception->getMessage();
        $response['debug'] = [
            'exception' => get_class($exception),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString()
        ];
    }

    echo json_encode($response, JSON_PRETTY_PRINT);
    exit;
}
```

---

## Try-Catch Blocks

### Database Operations

```php
public function store() {
    try {
        $data = Request::json();

        // Validate
        if (empty($data['email'])) {
            throw new ValidationException(['email' => 'Email is required']);
        }

        // Create user
        $user = User::create($data);

        Response::success($user, 201);

    } catch (PDOException $e) {
        // Database error
        if ($e->getCode() == 23000) {
            // Duplicate entry
            throw new BadRequestException('Email already exists');
        }

        // Other database errors
        throw new ApiException('Database error occurred');
    }
}
```

### External API Calls

```php
public function getWeather($city) {
    try {
        $weatherService = new WeatherService(getenv('WEATHER_API_KEY'));
        $weather = $weatherService->getCurrentWeather($city);

        if (!$weather) {
            throw new NotFoundException("Weather data not found for {$city}");
        }

        Response::success($weather);

    } catch (Exception $e) {
        // External API failed
        throw new ApiException('Weather service is currently unavailable', [], 503);
    }
}
```

### File Operations

```php
public function uploadImage() {
    try {
        if (!isset($_FILES['image'])) {
            throw new BadRequestException('No image provided');
        }

        $file = $_FILES['image'];

        // Validate
        if ($file['size'] > 5 * 1024 * 1024) {
            throw new BadRequestException('Image must be less than 5MB');
        }

        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($file['type'], $allowedTypes)) {
            throw new BadRequestException('Image must be JPEG, PNG, or GIF');
        }

        // Save file
        $filename = uniqid() . '_' . $file['name'];
        $path = __DIR__ . '/../storage/uploads/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $path)) {
            throw new ApiException('Failed to save image');
        }

        Response::success(['url' => '/uploads/' . $filename]);

    } catch (ApiException $e) {
        throw $e; // Re-throw API exceptions
    } catch (Exception $e) {
        throw new ApiException('File upload failed');
    }
}
```

---

## Validation Errors

### Detailed Field Errors

```php
public function store() {
    $data = Request::json();

    $validator = new Validator($data, [
        'name' => 'required|min:3|max:100',
        'email' => 'required|email',
        'age' => 'integer|min:18',
        'website' => 'url'
    ]);

    if (!$validator->validate()) {
        throw new ValidationException($validator->errors());
    }

    // ...
}
```

**Response (422):**
```json
{
  "success": false,
  "error": "Validation failed",
  "code": "VALIDATION_ERROR",
  "errors": {
    "name": "Name must be at least 3 characters",
    "email": "Invalid email format",
    "age": "Age must be at least 18"
  }
}
```

---

## Logging

### Simple Logger

```php
<?php
// helpers/Logger.php

class Logger {
    private static $logDir = __DIR__ . '/../storage/logs';

    /**
     * Log info message
     */
    public static function info($message, $context = []) {
        self::log('info', $message, $context);
    }

    /**
     * Log warning message
     */
    public static function warning($message, $context = []) {
        self::log('warning', $message, $context);
    }

    /**
     * Log error message
     */
    public static function error($message, $context = []) {
        self::log('error', $message, $context);
    }

    /**
     * Write log message
     */
    private static function log($level, $message, $context) {
        if (!is_dir(self::$logDir)) {
            mkdir(self::$logDir, 0755, true);
        }

        $date = date('Y-m-d');
        $time = date('H:i:s');
        $logFile = self::$logDir . "/{$date}.log";

        $contextString = !empty($context) ? ' ' . json_encode($context) : '';

        $logMessage = "[{$time}] {$level}: {$message}{$contextString}\n";

        file_put_contents($logFile, $logMessage, FILE_APPEND);
    }
}
```

**Usage:**
```php
// Log info
Logger::info('User logged in', ['user_id' => 5]);

// Log warning
Logger::warning('Rate limit approaching', ['user_id' => 5, 'requests' => 95]);

// Log error
Logger::error('Payment failed', [
    'user_id' => 5,
    'amount' => 99.99,
    'error' => 'Card declined'
]);

// In exception handler
Logger::error('Exception occurred', [
    'exception' => get_class($exception),
    'message' => $exception->getMessage(),
    'file' => $exception->getFile(),
    'line' => $exception->getLine()
]);
```

---

## Debug Mode

### Environment-Based Debugging

```php
// config/app.php

return [
    'debug' => getenv('APP_DEBUG') === 'true',
    'env' => getenv('APP_ENV') ?: 'production'
];
```

### Conditional Debug Info

```php
public static function handleException($exception) {
    $config = require __DIR__ . '/../config/app.php';

    $response = [
        'success' => false,
        'error' => 'An error occurred',
        'code' => 'INTERNAL_ERROR'
    ];

    // Show details in debug mode
    if ($config['debug']) {
        $response['message'] = $exception->getMessage();
        $response['debug'] = [
            'exception' => get_class($exception),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => explode("\n", $exception->getTraceAsString())
        ];
    }

    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode($response, JSON_PRETTY_PRINT);
    exit;
}
```

---

## Error Response Examples

### 400 Bad Request

```json
{
  "success": false,
  "error": "Invalid JSON",
  "code": "BAD_REQUEST",
  "message": "The request body contains invalid JSON syntax"
}
```

### 401 Unauthorized

```json
{
  "success": false,
  "error": "Unauthorized",
  "code": "UNAUTHORIZED",
  "message": "You must be logged in to access this resource"
}
```

### 403 Forbidden

```json
{
  "success": false,
  "error": "Forbidden",
  "code": "FORBIDDEN",
  "message": "You do not have permission to delete this post"
}
```

### 404 Not Found

```json
{
  "success": false,
  "error": "Not found",
  "code": "NOT_FOUND",
  "message": "User with ID 999 does not exist"
}
```

### 422 Validation Error

```json
{
  "success": false,
  "error": "Validation failed",
  "code": "VALIDATION_ERROR",
  "errors": {
    "email": "Invalid email format",
    "password": "Password must be at least 8 characters"
  }
}
```

### 429 Too Many Requests

```json
{
  "success": false,
  "error": "Too many requests",
  "code": "TOO_MANY_REQUESTS",
  "message": "You have exceeded the rate limit. Try again in 60 seconds"
}
```

### 500 Internal Server Error

**Production:**
```json
{
  "success": false,
  "error": "Internal server error",
  "code": "INTERNAL_ERROR"
}
```

**Development:**
```json
{
  "success": false,
  "error": "Internal server error",
  "code": "INTERNAL_ERROR",
  "message": "Call to undefined function nonExistentFunction()",
  "debug": {
    "exception": "Error",
    "file": "/path/to/controller.php",
    "line": 42,
    "trace": ["#0 ...", "#1 ..."]
  }
}
```

---

## Best Practices

### 1. Be Specific with Error Messages

```php
// ❌ Bad - vague
throw new NotFoundException('Not found');

// ✅ Good - specific
throw new NotFoundException("Post with ID {$id} not found");
```

### 2. Don't Expose Sensitive Info

```php
// ❌ Bad - exposes SQL
throw new ApiException("SQL error: SQLSTATE[23000]: Duplicate entry 'john@example.com'");

// ✅ Good - user-friendly
throw new BadRequestException('Email already exists');
```

### 3. Log Everything

```php
try {
    // Risky operation
} catch (Exception $e) {
    Logger::error('Operation failed', [
        'user_id' => Auth::userId(),
        'error' => $e->getMessage()
    ]);

    throw new ApiException('Operation failed');
}
```

### 4. Use HTTP Status Codes Correctly

```php
// ❌ Bad - everything is 200
http_response_code(200);
echo json_encode(['error' => 'Not found']);

// ✅ Good - proper status code
throw new NotFoundException('User not found');
```

---

## Quick Quiz

**Question 1:** What's the difference between 401 and 403?
<details>
<summary>Answer</summary>
401 = Not authenticated (no valid credentials). 403 = Authenticated but not allowed (no permission).
</details>

**Question 2:** Should you show stack traces in production?
<details>
<summary>Answer</summary>
NO! Only in development. In production, log details server-side and show generic error to client.
</details>

**Question 3:** What status code for validation errors?
<details>
<summary>Answer</summary>
422 Unprocessable Entity
</details>

**Question 4:** Why use custom exception classes?
<details>
<summary>Answer</summary>
Organize errors by type, attach status codes and error codes, easier to catch and handle specific errors.
</details>

**Question 5:** What should you log when an error occurs?
<details>
<summary>Answer</summary>
Exception type, message, file, line, user ID, request details, timestamp - everything that helps debug!
</details>

---

## Summary

You learned:
- Types of errors (4xx client errors, 5xx server errors)
- Standard error response format
- Error handler for PHP errors and exceptions
- Custom exception classes for different error types
- Using try-catch blocks effectively
- Validation error responses
- Logging errors for debugging
- Debug mode vs production mode
- Error response examples for all status codes
- Best practices for error handling

---

## Next Lesson

**14-versioning.md** - Learn API versioning strategies to update your API without breaking existing clients!
