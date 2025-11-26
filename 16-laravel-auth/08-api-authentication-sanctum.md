# Lesson 08 - API Authentication with Laravel Sanctum

**Duration**: 90 minutes
**Objectives**: Understand API authentication, install and configure Laravel Sanctum, implement token-based auth for SPAs and mobile apps

---

## What is API Authentication?

So far, we've been using **session-based authentication** for traditional web applications:
1. User logs in
2. Server stores user ID in session
3. Session ID stored in cookie
4. Cookie sent with every request

This works great for traditional web apps where the frontend and backend are on the same domain.

But for **APIs** (Single Page Apps, mobile apps, third-party integrations), session cookies don't work well because:
- Mobile apps can't use cookies
- SPAs on different domains can't share cookies
- Third-party apps need access tokens

**API Authentication** uses **tokens** instead of sessions:
1. User logs in with credentials
2. Server returns an API token
3. Client stores token (localStorage, mobile storage)
4. Token sent with every request in the `Authorization` header

---

## What is Laravel Sanctum?

**Laravel Sanctum** is Laravel's official package for API authentication. It provides:

1. **Token-based API authentication** - For mobile apps and SPAs on different domains
2. **Cookie-based SPA authentication** - For SPAs on the same domain (faster, more secure)
3. **Simple, lightweight** - Unlike Laravel Passport (OAuth2), Sanctum is simple

**When to use Sanctum:**
- Building a mobile app
- Building a SPA (React, Vue, etc.) on a different domain
- Providing an API to third-party developers
- Building a SPA on the same domain (cookie-based)

**When to use Passport:**
- Need OAuth2 (login with Google, Facebook, etc.)
- Need complex token scopes and permissions

---

## Installing Laravel Sanctum

### Step 1: Install via Composer

```bash
composer require laravel/sanctum
```

### Step 2: Publish Configuration

```bash
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
```

This creates `config/sanctum.php`.

### Step 3: Run Migrations

```bash
php artisan migrate
```

This creates the `personal_access_tokens` table:

```sql
CREATE TABLE personal_access_tokens (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tokenable_type VARCHAR(255) NOT NULL,
    tokenable_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    token VARCHAR(64) UNIQUE NOT NULL,
    abilities TEXT NULL,
    last_used_at TIMESTAMP NULL,
    expires_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

### Step 4: Add HasApiTokens Trait to User Model

```php
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens; // Add this trait

    // ...
}
```

### Step 5: Configure Middleware

Sanctum middleware is already configured in Laravel 11. Check `bootstrap/app.php`:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->api(prepend: [
        \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
    ]);
})
```

---

## Token-Based Authentication (Mobile Apps & Third-Party APIs)

This approach uses API tokens that are sent with each request.

### Creating Tokens

**Login endpoint:**

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Create token
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user,
        ]);
    }

    public function logout(Request $request)
    {
        // Revoke current token
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully',
        ]);
    }

    public function logoutAll(Request $request)
    {
        // Revoke all tokens
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Logged out from all devices',
        ]);
    }
}
```

**Register endpoint:**

```php
public function register(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:8|confirmed',
    ]);

    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
    ]);

    $token = $user->createToken('api-token')->plainTextToken;

    return response()->json([
        'token' => $token,
        'user' => $user,
    ], 201);
}
```

### API Routes

Create `routes/api.php`:

```php
<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PostController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/logout-all', [AuthController::class, 'logoutAll']);

    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::apiResource('posts', PostController::class);
});
```

### Using the API

**1. Register:**

```bash
POST /api/register
Content-Type: application/json

{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

**Response:**

```json
{
    "token": "1|abcdefghijklmnopqrstuvwxyz123456789",
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com"
    }
}
```

**2. Login:**

```bash
POST /api/login
Content-Type: application/json

{
    "email": "john@example.com",
    "password": "password123"
}
```

**3. Access protected endpoint:**

```bash
GET /api/user
Authorization: Bearer 1|abcdefghijklmnopqrstuvwxyz123456789
```

**Response:**

```json
{
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com"
}
```

**4. Logout:**

```bash
POST /api/logout
Authorization: Bearer 1|abcdefghijklmnopqrstuvwxyz123456789
```

---

## Token Abilities (Permissions)

You can assign specific abilities (permissions) to tokens.

### Creating Tokens with Abilities

```php
// Create token with specific abilities
$token = $user->createToken('mobile-app', ['post:create', 'post:update'])->plainTextToken;

// Create token with all abilities
$token = $user->createToken('admin-token', ['*'])->plainTextToken;
```

### Checking Abilities

```php
if ($request->user()->tokenCan('post:create')) {
    // Token has permission to create posts
}

if ($request->user()->tokenCan('post:delete')) {
    // Token has permission to delete posts
}
```

### Middleware for Abilities

```php
Route::middleware(['auth:sanctum', 'abilities:post:create,post:update'])->group(function () {
    Route::post('/posts', [PostController::class, 'store']);
    Route::put('/posts/{post}', [PostController::class, 'update']);
});

Route::middleware(['auth:sanctum', 'ability:post:delete'])->group(function () {
    Route::delete('/posts/{post}', [PostController::class, 'destroy']);
});
```

**Difference between `abilities` and `ability`:**
- `abilities` - Token must have ALL specified abilities (AND)
- `ability` - Token must have AT LEAST ONE specified ability (OR)

### Example: Different Tokens for Different Devices

```php
public function login(Request $request)
{
    // ... validate credentials ...

    $deviceName = $request->input('device_name', 'unknown');

    $abilities = match($deviceName) {
        'mobile-app' => ['post:read', 'post:create', 'post:update'],
        'web-app' => ['*'],
        'third-party-integration' => ['post:read'],
        default => ['post:read'],
    };

    $token = $user->createToken($deviceName, $abilities)->plainTextToken;

    return response()->json([
        'token' => $token,
        'abilities' => $abilities,
    ]);
}
```

---

## Token Expiration

By default, Sanctum tokens don't expire. For security, you should set expiration.

### Configure in `config/sanctum.php`:

```php
'expiration' => 60 * 24 * 30, // 30 days (in minutes)
```

Or set to `null` for no expiration:

```php
'expiration' => null,
```

### Check Token Expiration

```php
$token = $user->createToken('api-token');

// Set custom expiration
$token->accessToken->expires_at = now()->addDays(7);
$token->accessToken->save();

return response()->json([
    'token' => $token->plainTextToken,
    'expires_at' => $token->accessToken->expires_at,
]);
```

### Automatically Delete Expired Tokens

Schedule in `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    $schedule->command('sanctum:prune-expired --hours=24')->daily();
}
```

Or run manually:

```bash
php artisan sanctum:prune-expired --hours=24
```

---

## SPA Authentication (Same Domain)

For SPAs on the same domain, Sanctum uses **cookie-based authentication** (like traditional web apps).

### Configuration

**1. Set frontend domain in `.env`:**

```env
SANCTUM_STATEFUL_DOMAINS=localhost:3000,127.0.0.1:3000,localhost:5173
SESSION_DOMAIN=localhost
```

**2. Configure CORS in `config/cors.php`:**

```php
'paths' => ['api/*', 'sanctum/csrf-cookie'],

'supports_credentials' => true,
```

### Frontend Setup (Example with Axios)

**1. Set up Axios:**

```javascript
import axios from 'axios';

axios.defaults.baseURL = 'http://localhost:8000';
axios.defaults.withCredentials = true;
```

**2. Get CSRF token before login:**

```javascript
await axios.get('/sanctum/csrf-cookie');
```

**3. Login:**

```javascript
const response = await axios.post('/login', {
    email: 'john@example.com',
    password: 'password123'
});
```

**4. Subsequent requests automatically include session cookie:**

```javascript
const user = await axios.get('/api/user');
```

### Backend Routes

Same as before, but authentication is handled via session cookies instead of tokens.

---

## API Resource Controller Example

Let's build a complete API for managing posts.

### PostController

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::with('user')->paginate(10);

        return PostResource::collection($posts);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Post::class);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $post = $request->user()->posts()->create($validated);

        return new PostResource($post);
    }

    public function show(Post $post)
    {
        $this->authorize('view', $post);

        return new PostResource($post);
    }

    public function update(Request $request, Post $post)
    {
        $this->authorize('update', $post);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $post->update($validated);

        return new PostResource($post);
    }

    public function destroy(Post $post)
    {
        $this->authorize('delete', $post);

        $post->delete();

        return response()->json(['message' => 'Post deleted successfully']);
    }
}
```

### API Resource

```bash
php artisan make:resource PostResource
```

```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'is_published' => $this->is_published,
            'author' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ],
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
            'can' => [
                'update' => $request->user()?->can('update', $this),
                'delete' => $request->user()?->can('delete', $this),
            ],
        ];
    }
}
```

---

## Testing API Endpoints

### Using Postman

**1. Login:**

```
POST http://localhost:8000/api/login
Content-Type: application/json

{
    "email": "john@example.com",
    "password": "password123"
}
```

**Copy the token from the response.**

**2. Get User:**

```
GET http://localhost:8000/api/user
Authorization: Bearer {your-token-here}
```

**3. Create Post:**

```
POST http://localhost:8000/api/posts
Authorization: Bearer {your-token-here}
Content-Type: application/json

{
    "title": "My First API Post",
    "content": "This is the content"
}
```

### Using cURL

```bash
# Login
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"john@example.com","password":"password123"}'

# Get user (replace TOKEN with actual token)
curl -X GET http://localhost:8000/api/user \
  -H "Authorization: Bearer TOKEN"

# Create post
curl -X POST http://localhost:8000/api/posts \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"title":"Test Post","content":"Test content"}'
```

### Using HTTP Client (VS Code Extension)

Create `api-tests.http`:

```http
@baseUrl = http://localhost:8000/api
@token = 1|abcdefghijklmnopqrstuvwxyz123456789

### Register
POST {{baseUrl}}/register
Content-Type: application/json

{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}

### Login
POST {{baseUrl}}/login
Content-Type: application/json

{
    "email": "john@example.com",
    "password": "password123"
}

### Get User
GET {{baseUrl}}/user
Authorization: Bearer {{token}}

### Get Posts
GET {{baseUrl}}/posts
Authorization: Bearer {{token}}

### Create Post
POST {{baseUrl}}/posts
Authorization: Bearer {{token}}
Content-Type: application/json

{
    "title": "Test Post",
    "content": "This is a test post"
}

### Logout
POST {{baseUrl}}/logout
Authorization: Bearer {{token}}
```

---

## Rate Limiting

Protect your API from abuse with rate limiting.

### Global Rate Limiting

In `bootstrap/app.php`:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->api(append: [
        \Illuminate\Routing\Middleware\ThrottleRequests::class.':api',
    ]);
})
```

**Configure in `app/Providers/RouteServiceProvider.php`:**

```php
RateLimiter::for('api', function (Request $request) {
    return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
});
```

### Per-Route Rate Limiting

```php
Route::middleware(['auth:sanctum', 'throttle:10,1'])->group(function () {
    Route::post('/posts', [PostController::class, 'store']); // 10 requests per minute
});
```

### Custom Rate Limiters

```php
RateLimiter::for('uploads', function (Request $request) {
    return $request->user()->isPremium()
        ? Limit::none()
        : Limit::perMinute(10);
});

// Usage
Route::post('/upload', [UploadController::class, 'store'])
    ->middleware('throttle:uploads');
```

---

## Best Practices

1. **Always use HTTPS in production**
   - Tokens are sensitive!
   - Never send tokens over HTTP

2. **Store tokens securely on the client**
   - Mobile: Use encrypted storage (Keychain, Keystore)
   - Web: Use HttpOnly cookies or localStorage (know the risks)

3. **Set token expiration**
   - Don't let tokens live forever
   - Force re-authentication periodically

4. **Revoke tokens when needed**
   - On logout
   - On password change
   - On suspicious activity

5. **Use abilities for fine-grained control**
   - Don't give all permissions to all tokens
   - Least privilege principle

6. **Rate limit your API**
   - Prevent abuse
   - Protect server resources

7. **Use API Resources**
   - Control what data is exposed
   - Include authorization in responses

---

## Comparison: Module 07 vs Sanctum

**Module 07 (Session-based):**
- Session ID in cookie
- Server stores session data
- Works for same-domain web apps

**Sanctum (Token-based):**
- Token in Authorization header
- Server validates token from database
- Works for mobile apps, SPAs, third-party APIs

**Sanctum (SPA on same domain):**
- Uses session cookies (like Module 07)
- But with CSRF protection
- Best of both worlds!

---

## Quick Quiz

1. **What does Sanctum provide?**
   - Token-based API authentication
   - Cookie-based SPA authentication

2. **How do you create a token?**
   - `$user->createToken('token-name')->plainTextToken`

3. **How do you protect API routes?**
   - `Route::middleware('auth:sanctum')`

4. **How do you send the token from the client?**
   - `Authorization: Bearer {token}`

5. **What are token abilities?**
   - Permissions assigned to specific tokens
   - Example: `'post:create'`, `'post:delete'`

6. **Should tokens expire?**
   - Yes! Set expiration for security

---

## What's Next

Congratulations! You've completed Module 16 - Laravel Authentication & Security!

You now understand:
- Laravel Breeze installation
- Authentication system internals
- Login, register, password reset
- Middleware
- Gates and Policies
- Authorization throughout your app
- Role-based access control
- API authentication with Sanctum

**Next Module: Module 17 - Livewire** - Build reactive components without writing JavaScript!

---

## Key Takeaways

1. **Sanctum provides two authentication methods:**
   - Token-based (mobile apps, third-party APIs)
   - Cookie-based (SPAs on same domain)

2. **Token workflow:**
   - Login → Get token → Send with requests → Logout/revoke

3. **Token abilities provide fine-grained permissions**
   - Different tokens can have different abilities

4. **Always use HTTPS in production**

5. **Set token expiration for security**

6. **Rate limit your API**

7. **Use API Resources to control exposed data**

You now have complete mastery of Laravel's authentication and authorization system!
