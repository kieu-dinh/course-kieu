# Lesson 3: Sanctum Authentication

**Duration**: 3-4 hours
**Prerequisites**: Lessons 1-2 (API Routes and Resources)
**Objective**: Secure your APIs with token-based authentication using Laravel Sanctum

---

## Introduction

Right now, anyone can access your API endpoints. In production, you need to:
- Identify who is making requests
- Protect endpoints that require authentication
- Allow users to generate and revoke API tokens
- Handle different permission levels

**Laravel Sanctum** provides a simple authentication system for:
- SPAs (Single Page Applications) like React, Vue
- Mobile applications (iOS, Android)
- Simple token-based APIs

Think of Sanctum tokens like keys to a building: you give keys to authorized people, and they present the key each time they want to enter.

---

## Authentication Types: Session vs Token

### Session-Based Authentication (Traditional Web)

**How it works**:
1. User logs in with email/password
2. Server creates a session and stores user ID
3. Server sends session ID in a cookie
4. Browser automatically sends cookie with every request
5. Server reads session ID, knows who you are

**Good for**: Traditional web apps (Blade templates, Livewire)

**Problems for APIs**:
- Requires cookies (mobile apps don't use cookies well)
- CSRF protection needed
- Stateful (server must remember sessions)

### Token-Based Authentication (APIs)

**How it works**:
1. User logs in with email/password
2. Server generates a unique token (long random string)
3. Client stores token (localStorage, secure storage)
4. Client sends token with every request in Authorization header
5. Server validates token, knows who you are

**Good for**: APIs, SPAs, mobile apps

**Benefits**:
- Stateless (no server-side sessions)
- Works with any client (mobile, web, desktop)
- Easy to implement
- Tokens can have abilities/permissions
- Tokens can be revoked

**Sanctum provides**: Token-based authentication for APIs

---

## Installing Sanctum

Laravel 11 includes Sanctum by default, but let's verify:

### Step 1: Check Installation

```bash
# Check if Sanctum is in composer.json
composer show laravel/sanctum
```

If not installed:
```bash
composer require laravel/sanctum
```

### Step 2: Publish Configuration

```bash
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
```

This creates:
- `config/sanctum.php` - Configuration file
- Migration for `personal_access_tokens` table

### Step 3: Run Migrations

```bash
php artisan migrate
```

This creates the `personal_access_tokens` table to store API tokens.

### Step 4: Add Sanctum Middleware

In `app/Http/Kernel.php` (Laravel 10) or `bootstrap/app.php` (Laravel 11):

Laravel 11:
```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->api(prepend: [
        \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
    ]);
})
```

Laravel 10:
```php
'api' => [
    \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
    'throttle:api',
    \Illuminate\Routing\Middleware\SubstituteBindings::class,
],
```

### Step 5: Use HasApiTokens Trait

In `app/Models/User.php`:

```php
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens; // Add this trait

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
}
```

**Done!** Sanctum is ready to use.

---

## Creating Authentication Endpoints

### Registration Endpoint

Create `app/Http/Controllers/Api/AuthController.php`:

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    /**
     * Register a new user
     * POST /api/register
     */
    public function register(Request $request)
    {
        // Validate input
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        // Create user
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        // Create token
        $token = $user->createToken('auth-token')->plainTextToken;

        // Return user and token
        return response()->json([
            'success' => true,
            'message' => 'User registered successfully',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'token' => $token,
        ], 201);
    }
}
```

### Login Endpoint

```php
/**
 * Login user
 * POST /api/login
 */
public function login(Request $request)
{
    // Validate input
    $validated = $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    // Find user
    $user = User::where('email', $validated['email'])->first();

    // Check user exists and password is correct
    if (!$user || !Hash::check($validated['password'], $user->password)) {
        return response()->json([
            'success' => false,
            'message' => 'Invalid credentials',
        ], 401);
    }

    // Create token
    $token = $user->createToken('auth-token')->plainTextToken;

    return response()->json([
        'success' => true,
        'message' => 'Login successful',
        'user' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ],
        'token' => $token,
    ]);
}
```

### Logout Endpoint

```php
/**
 * Logout user (revoke current token)
 * POST /api/logout
 */
public function logout(Request $request)
{
    // Revoke the token that was used to authenticate the current request
    $request->user()->currentAccessToken()->delete();

    return response()->json([
        'success' => true,
        'message' => 'Logged out successfully',
    ]);
}
```

### Get Current User

```php
/**
 * Get authenticated user
 * GET /api/user
 */
public function user(Request $request)
{
    return response()->json([
        'success' => true,
        'user' => [
            'id' => $request->user()->id,
            'name' => $request->user()->name,
            'email' => $request->user()->email,
        ],
    ]);
}
```

### Register Routes

In `routes/api.php`:

```php
use App\Http\Controllers\Api\AuthController;

// Public routes (no authentication required)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes (authentication required)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);
});
```

---

## Understanding Token Creation

### The createToken Method

```php
$token = $user->createToken('token-name')->plainTextToken;
```

**What happens**:
1. Generates a random 80-character token
2. Hashes the token (stores hash in database)
3. Returns the plain text token (only time you'll see it!)
4. Client must store and send this token with requests

**Important**: `plainTextToken` is only available once. If lost, user must create a new token.

### Token Names

Give tokens descriptive names:

```php
// Different devices
$token = $user->createToken('mobile-app')->plainTextToken;
$token = $user->createToken('web-app')->plainTextToken;

// Different purposes
$token = $user->createToken('read-only')->plainTextToken;
$token = $user->createToken('full-access')->plainTextToken;
```

This helps users identify and manage their tokens.

---

## Protecting Routes

### Using Middleware

Protect routes that require authentication:

```php
// Single route
Route::get('/tasks', [TaskController::class, 'index'])
    ->middleware('auth:sanctum');

// Route group
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('tasks', TaskController::class);
    Route::apiResource('posts', PostController::class);
});
```

**How it works**:
1. Client sends token in `Authorization` header
2. Sanctum validates token
3. If valid, request continues and `$request->user()` is available
4. If invalid/missing, returns 401 Unauthorized

---

## Testing with cURL

### Register a User

```bash
curl -X POST http://localhost/api/register \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

Response:
```json
{
    "success": true,
    "message": "User registered successfully",
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com"
    },
    "token": "1|abcdef123456..."
}
```

**Save the token!** You'll need it for authenticated requests.

### Login

```bash
curl -X POST http://localhost/api/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "password123"
  }'
```

### Access Protected Route

```bash
# Without token - 401 Unauthorized
curl http://localhost/api/user

# With token - Success
curl http://localhost/api/user \
  -H "Authorization: Bearer 1|abcdef123456..." \
  -H "Accept: application/json"
```

**Format**: `Authorization: Bearer {token}`

### Logout

```bash
curl -X POST http://localhost/api/logout \
  -H "Authorization: Bearer 1|abcdef123456..." \
  -H "Accept: application/json"
```

---

## Token Abilities (Permissions)

Sanctum tokens can have "abilities" - permissions that limit what they can do.

### Creating Tokens with Abilities

```php
// Token with specific abilities
$token = $user->createToken('token-name', ['task:read', 'task:create'])->plainTextToken;

// Token with all abilities
$token = $user->createToken('token-name', ['*'])->plainTextToken;
```

### Checking Abilities in Controllers

```php
public function store(Request $request)
{
    // Check if token has ability
    if (!$request->user()->tokenCan('task:create')) {
        return response()->json([
            'message' => 'Insufficient permissions'
        ], 403);
    }

    // Create task...
}
```

### Protecting Routes by Abilities

```php
Route::middleware(['auth:sanctum', 'abilities:task:create'])->group(function () {
    Route::post('/tasks', [TaskController::class, 'store']);
});

Route::middleware(['auth:sanctum', 'ability:task:read'])->group(function () {
    Route::get('/tasks', [TaskController::class, 'index']);
});
```

**Difference**:
- `abilities` - Requires ALL listed abilities
- `ability` - Requires ANY of the listed abilities

### Example: Different Token Types

```php
public function login(Request $request)
{
    $validated = $request->validate([
        'email' => 'required|email',
        'password' => 'required',
        'device' => 'required|in:web,mobile'
    ]);

    $user = User::where('email', $validated['email'])->first();

    if (!$user || !Hash::check($validated['password'], $user->password)) {
        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    // Different abilities based on device
    $abilities = match($validated['device']) {
        'web' => ['*'], // Full access
        'mobile' => ['task:read', 'task:create', 'task:update'], // Limited
    };

    $token = $user->createToken(
        $validated['device'] . '-token',
        $abilities
    )->plainTextToken;

    return response()->json([
        'user' => $user,
        'token' => $token,
    ]);
}
```

---

## Managing Multiple Tokens

Users can have multiple active tokens (for different devices).

### List User's Tokens

```php
public function tokens(Request $request)
{
    $tokens = $request->user()->tokens()->get();

    return response()->json([
        'tokens' => $tokens->map(function ($token) {
            return [
                'id' => $token->id,
                'name' => $token->name,
                'last_used_at' => $token->last_used_at?->diffForHumans(),
                'created_at' => $token->created_at->diffForHumans(),
            ];
        })
    ]);
}
```

### Revoke Specific Token

```php
public function revokeToken(Request $request, $tokenId)
{
    $request->user()->tokens()->where('id', $tokenId)->delete();

    return response()->json([
        'message' => 'Token revoked successfully'
    ]);
}
```

### Revoke All Tokens (Logout Everywhere)

```php
public function revokeAllTokens(Request $request)
{
    $request->user()->tokens()->delete();

    return response()->json([
        'message' => 'All tokens revoked successfully'
    ]);
}
```

---

## Securing Your API

### 1. HTTPS in Production

Always use HTTPS in production. Configure in `.env`:

```env
APP_URL=https://your-domain.com
SANCTUM_STATEFUL_DOMAINS=your-domain.com
```

### 2. Token Expiration

Sanctum tokens don't expire by default. Implement expiration:

```php
// In config/sanctum.php
'expiration' => 60 * 24, // Tokens expire after 24 hours (in minutes)
```

Or check manually:

```php
public function handle($request, Closure $next)
{
    if ($request->user()->currentAccessToken()->created_at->addDays(7)->isPast()) {
        return response()->json(['message' => 'Token expired'], 401);
    }

    return $next($request);
}
```

### 3. Rate Limiting

Prevent abuse by limiting requests:

```php
Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
    Route::apiResource('tasks', TaskController::class);
});
```

This limits to 60 requests per minute per user.

### 4. Validate Origin

Restrict which domains can use your API:

In `config/sanctum.php`:

```php
'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', sprintf(
    '%s%s',
    'localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,::1',
    env('APP_URL') ? ','.parse_url(env('APP_URL'), PHP_URL_HOST) : ''
))),
```

### 5. Hide Sensitive User Data

Never return passwords or tokens in responses:

```php
// In User model
protected $hidden = [
    'password',
    'remember_token',
];

// In UserResource
public function toArray($request): array
{
    return [
        'id' => $this->id,
        'name' => $this->name,
        'email' => $this->email,
        // NO password, NO tokens!
    ];
}
```

---

## Complete Example: Protected Task API

### Models

```php
// Task belongs to User
class Task extends Model
{
    protected $fillable = ['title', 'description', 'completed', 'user_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
```

### Controller

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    /**
     * List authenticated user's tasks
     */
    public function index(Request $request)
    {
        $tasks = $request->user()
            ->tasks()
            ->latest()
            ->paginate(15);

        return TaskResource::collection($tasks);
    }

    /**
     * Create a task for authenticated user
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $task = $request->user()->tasks()->create($validated);

        return new TaskResource($task);
    }

    /**
     * Show a task (only if owned by user)
     */
    public function show(Request $request, Task $task)
    {
        // Check ownership
        if ($task->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        return new TaskResource($task);
    }

    /**
     * Update a task (only if owned by user)
     */
    public function update(Request $request, Task $task)
    {
        // Check ownership
        if ($task->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'completed' => 'boolean',
        ]);

        $task->update($validated);

        return new TaskResource($task);
    }

    /**
     * Delete a task (only if owned by user)
     */
    public function destroy(Request $request, Task $task)
    {
        // Check ownership
        if ($task->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        $task->delete();

        return response()->json([
            'message' => 'Task deleted successfully'
        ]);
    }
}
```

### Routes

```php
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TaskController;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Tasks
    Route::apiResource('tasks', TaskController::class);
});
```

---

## Testing Authentication Flow

### Full Workflow

```bash
# 1. Register
curl -X POST http://localhost/api/register \
  -H "Content-Type: application/json" \
  -d '{"name":"John","email":"john@test.com","password":"password","password_confirmation":"password"}'

# Save token from response

# 2. Create a task
curl -X POST http://localhost/api/tasks \
  -H "Authorization: Bearer {YOUR_TOKEN}" \
  -H "Content-Type: application/json" \
  -d '{"title":"My first task","description":"Test description"}'

# 3. List tasks
curl http://localhost/api/tasks \
  -H "Authorization: Bearer {YOUR_TOKEN}"

# 4. Update task
curl -X PUT http://localhost/api/tasks/1 \
  -H "Authorization: Bearer {YOUR_TOKEN}" \
  -H "Content-Type: application/json" \
  -d '{"completed":true}'

# 5. Delete task
curl -X DELETE http://localhost/api/tasks/1 \
  -H "Authorization: Bearer {YOUR_TOKEN}"

# 6. Logout
curl -X POST http://localhost/api/logout \
  -H "Authorization: Bearer {YOUR_TOKEN}"
```

---

## Best Practices

1. **Always use HTTPS in production** - Tokens sent over HTTP can be intercepted
2. **Store tokens securely on client** - Not in localStorage (vulnerable to XSS)
3. **Implement token expiration** - Don't let tokens live forever
4. **Revoke tokens on password change** - Force re-authentication
5. **Use different tokens for different devices** - Better tracking and security
6. **Check ownership** - Always verify user owns the resource
7. **Rate limit** - Prevent abuse
8. **Log authentication attempts** - Monitor for suspicious activity
9. **Use abilities** - Limit what tokens can do
10. **Never expose tokens in logs or errors** - They're like passwords!

---

## Quick Quiz

1. **What's the difference between session and token authentication?**
   - When would you use each?

2. **What does the `auth:sanctum` middleware do?**
   - What happens if token is invalid?

3. **Can a user have multiple active tokens?**
   - Why would this be useful?

4. **What are token abilities?**
   - Give an example use case

5. **How do you revoke a token?**
   - What's the difference between logout and revoke all?

---

## Practice Exercise

**Build an authenticated Notes API**:

1. Models:
   - User (with HasApiTokens)
   - Note (belongs to User)

2. Auth endpoints:
   - POST `/api/register`
   - POST `/api/login`
   - POST `/api/logout`
   - GET `/api/user`

3. Note endpoints (protected):
   - GET `/api/notes` - User's notes only
   - POST `/api/notes` - Create note for user
   - GET `/api/notes/{id}` - Show if owned
   - PUT `/api/notes/{id}` - Update if owned
   - DELETE `/api/notes/{id}` - Delete if owned

4. Token abilities:
   - Create tokens with `note:read`, `note:write` abilities
   - Implement read-only and full-access token types

**Test with Postman/Insomnia**:
- Register → Login → Create notes → Try accessing other users' notes (should fail)

---

## What's Next?

In the next lesson, you'll learn about **API Versioning** - how to manage changes to your API over time without breaking existing clients. This is crucial for maintaining APIs in production!

Sanctum makes API authentication simple and secure. Master it, and you'll be ready to build production-ready APIs!
