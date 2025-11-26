# Lesson 07 - Middleware Concept: Elegant Page Protection

**Duration**: 1 hour
**Objectives**: Understand middleware pattern, implement route protection, and build reusable authorization checks

---

## What is Middleware?

**Middleware** is code that runs BETWEEN a request and your page logic.

Think of it like airport security:
1. You want to board a plane (access a page)
2. Before boarding, you go through security checkpoints (middleware)
3. Each checkpoint verifies something: ticket, ID, baggage
4. Only after passing all checkpoints can you board

```
Request → Middleware 1 → Middleware 2 → Middleware 3 → Your Page
           (Auth)         (Timeout)        (Permission)
```

---

## Why Middleware?

### Without Middleware (Repetitive Code)

Every protected page has the same code:

```php
<?php
// profile.php
require_once 'includes/session.php';
require_once 'includes/auth.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

if (session_has_timed_out()) {
    session_end();
    header('Location: login.php?timeout=1');
    exit;
}

// Finally, your actual page code...
?>
```

```php
<?php
// settings.php
require_once 'includes/session.php';
require_once 'includes/auth.php';

// SAME CODE REPEATED!
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

if (session_has_timed_out()) {
    session_end();
    header('Location: login.php?timeout=1');
    exit;
}

// Your page code...
?>
```

**Problems:**
- Code duplication
- Easy to forget checks
- Hard to maintain (change in 50 files?)
- Not DRY (Don't Repeat Yourself)

### With Middleware (Clean Code)

```php
<?php
// profile.php
require_once 'includes/middleware.php';

// One line!
protectPage();

// Your page code...
?>
```

```php
<?php
// settings.php
require_once 'includes/middleware.php';

// One line!
protectPage();

// Your page code...
?>
```

**Benefits:**
- One place to maintain logic
- Consistent behavior everywhere
- Easy to add new checks
- Clean, readable code

---

## Building Middleware System

Let's build a complete middleware system for authentication.

### Step 1: Basic Middleware Functions

```php
<?php
// includes/middleware.php

require_once __DIR__ . '/session.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config/database.php';

/**
 * Guest middleware - only allow non-logged-in users
 * Use on login/register pages
 */
function guest(): void
{
    if (isLoggedIn()) {
        header('Location: dashboard.php');
        exit;
    }
}

/**
 * Auth middleware - require logged-in user
 */
function auth(): void
{
    if (!isLoggedIn()) {
        // Save intended URL
        session_set('redirect_after_login', $_SERVER['REQUEST_URI']);

        header('Location: login.php');
        exit;
    }
}

/**
 * Timeout middleware - check session timeout
 */
function timeout(int $seconds = 1800): void
{
    if (!session_has('user_id')) {
        return; // Not logged in, skip
    }

    if (session_has_timed_out($seconds)) {
        session_end();
        session_set('error', 'Your session expired due to inactivity.');
        header('Location: login.php?timeout=1');
        exit;
    }
}

/**
 * Verified middleware - require email verification
 */
function verified(): void
{
    auth(); // Must be logged in first

    global $pdo;
    $user = getCurrentUser($pdo);

    if (!$user['email_verified']) {
        header('Location: verify-email-notice.php');
        exit;
    }
}

/**
 * Admin middleware - require admin role
 */
function admin(): void
{
    auth(); // Must be logged in first

    global $pdo;
    $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->execute([session_get('user_id')]);
    $role = $stmt->fetchColumn();

    if ($role !== 'admin') {
        http_response_code(403);
        die('Access denied. Admin only.');
    }
}

/**
 * Combined middleware - protect page with auth + timeout
 */
function protectPage(int $timeout = 1800): void
{
    auth();
    timeout($timeout);
}
?>
```

---

## Step 2: Using Middleware

### Public Page (No Protection)

```php
<?php
// index.php - Anyone can access
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Home</title>
</head>
<body>
    <h1>Welcome to Our Site</h1>
    <a href="login.php">Login</a>
    <a href="register.php">Register</a>
</body>
</html>
```

### Guest-Only Page (Login/Register)

```php
<?php
// login.php - Only non-logged-in users
require_once 'includes/middleware.php';

guest(); // Redirect to dashboard if already logged in

// Login form...
?>
```

### Protected Page (Require Login)

```php
<?php
// dashboard.php - Require login
require_once 'includes/middleware.php';

protectPage(); // Require login + check timeout

// Page content...
?>
```

### Admin-Only Page

```php
<?php
// admin/users.php - Require admin role
require_once '../includes/middleware.php';

admin(); // Require login + admin role

// Admin panel...
?>
```

### Email-Verified Only Page

```php
<?php
// premium-content.php - Require verified email
require_once 'includes/middleware.php';

verified(); // Require login + verified email

// Premium content...
?>
```

---

## Step 3: Advanced Middleware - Permission System

Let's build a flexible permission system.

### Database Schema

```sql
-- Add role column to users
ALTER TABLE users ADD COLUMN role VARCHAR(50) DEFAULT 'user';

-- Permissions table
CREATE TABLE permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) UNIQUE NOT NULL,
    description TEXT NULL
);

-- Role permissions (many-to-many)
CREATE TABLE role_permissions (
    role VARCHAR(50) NOT NULL,
    permission_id INT NOT NULL,
    PRIMARY KEY (role, permission_id),
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
);

-- Insert default permissions
INSERT INTO permissions (name, description) VALUES
    ('view_users', 'View user list'),
    ('create_users', 'Create new users'),
    ('edit_users', 'Edit existing users'),
    ('delete_users', 'Delete users'),
    ('view_reports', 'View reports'),
    ('manage_settings', 'Manage system settings');

-- Assign permissions to admin role
INSERT INTO role_permissions (role, permission_id)
SELECT 'admin', id FROM permissions;

-- Assign some permissions to moderator role
INSERT INTO role_permissions (role, permission_id)
SELECT 'moderator', id FROM permissions WHERE name IN ('view_users', 'view_reports');
```

### Permission Checking Functions

```php
<?php
// includes/permissions.php

/**
 * Get user's role
 */
function getUserRole(PDO $pdo, int $userId): string
{
    $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    return $stmt->fetchColumn() ?: 'user';
}

/**
 * Get permissions for a role
 */
function getRolePermissions(PDO $pdo, string $role): array
{
    $stmt = $pdo->prepare("
        SELECT p.name
        FROM permissions p
        JOIN role_permissions rp ON p.id = rp.permission_id
        WHERE rp.role = ?
    ");
    $stmt->execute([$role]);

    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

/**
 * Check if user has permission
 */
function userHasPermission(PDO $pdo, int $userId, string $permission): bool
{
    $role = getUserRole($pdo, $userId);
    $permissions = getRolePermissions($pdo, $role);

    return in_array($permission, $permissions);
}

/**
 * Check if user has any of the permissions
 */
function userHasAnyPermission(PDO $pdo, int $userId, array $permissions): bool
{
    foreach ($permissions as $permission) {
        if (userHasPermission($pdo, $userId, $permission)) {
            return true;
        }
    }
    return false;
}

/**
 * Check if user has all permissions
 */
function userHasAllPermissions(PDO $pdo, int $userId, array $permissions): bool
{
    foreach ($permissions as $permission) {
        if (!userHasPermission($pdo, $userId, $permission)) {
            return false;
        }
    }
    return true;
}
?>
```

### Permission Middleware

```php
<?php
// includes/middleware.php (add these)

/**
 * Require specific permission
 */
function can(string $permission): void
{
    auth(); // Must be logged in

    global $pdo;
    $userId = session_get('user_id');

    if (!userHasPermission($pdo, $userId, $permission)) {
        http_response_code(403);
        die("Access denied. Required permission: $permission");
    }
}

/**
 * Require any of the permissions
 */
function canAny(array $permissions): void
{
    auth(); // Must be logged in

    global $pdo;
    $userId = session_get('user_id');

    if (!userHasAnyPermission($pdo, $userId, $permissions)) {
        http_response_code(403);
        die("Access denied. Required permissions: " . implode(', ', $permissions));
    }
}

/**
 * Require all permissions
 */
function canAll(array $permissions): void
{
    auth(); // Must be logged in

    global $pdo;
    $userId = session_get('user_id');

    if (!userHasAllPermissions($pdo, $userId, $permissions)) {
        http_response_code(403);
        die("Access denied. Required permissions: " . implode(', ', $permissions));
    }
}
?>
```

### Using Permission Middleware

```php
<?php
// admin/users/create.php
require_once '../../includes/middleware.php';

can('create_users'); // Require specific permission

// Create user form...
?>
```

```php
<?php
// admin/users/edit.php
require_once '../../includes/middleware.php';

canAny(['edit_users', 'manage_settings']); // Need at least one

// Edit user form...
?>
```

```php
<?php
// admin/danger-zone.php
require_once '../../includes/middleware.php';

canAll(['delete_users', 'manage_settings']); // Need both

// Dangerous operations...
?>
```

---

## Step 4: Middleware Chain (Stacking)

Sometimes you need multiple checks:

```php
<?php
// includes/middleware.php (add this)

/**
 * Apply multiple middleware functions
 */
function middleware(array $middlewares): void
{
    foreach ($middlewares as $middleware) {
        if (is_string($middleware)) {
            // Simple middleware name
            call_user_func($middleware);
        } elseif (is_array($middleware)) {
            // Middleware with parameters [function, param1, param2...]
            $function = array_shift($middleware);
            call_user_func_array($function, $middleware);
        }
    }
}
?>
```

### Using Middleware Chain

```php
<?php
// sensitive-page.php
require_once 'includes/middleware.php';

// Stack multiple middleware
middleware([
    'auth',                    // Must be logged in
    ['timeout', 900],         // 15-minute timeout (stricter)
    'verified',               // Must have verified email
    ['can', 'view_reports']  // Must have permission
]);

// Your sensitive page...
?>
```

---

## Step 5: Conditional Middleware (View Logic)

Sometimes you want to show/hide content based on permissions:

```php
<?php
// includes/helpers.php

/**
 * Check if current user can do something (for views)
 */
function can_user(string $permission): bool
{
    if (!isLoggedIn()) {
        return false;
    }

    global $pdo;
    $userId = session_get('user_id');

    return userHasPermission($pdo, $userId, $permission);
}

/**
 * Check if current user has role
 */
function has_role(string $role): bool
{
    if (!isLoggedIn()) {
        return false;
    }

    global $pdo;
    $userId = session_get('user_id');
    $userRole = getUserRole($pdo, $userId);

    return $userRole === $role;
}
?>
```

### Using in Views

```php
<?php
require_once 'includes/middleware.php';
require_once 'includes/helpers.php';

protectPage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
</head>
<body>
    <h1>Dashboard</h1>

    <!-- Show only to users with permission -->
    <?php if (can_user('view_users')): ?>
        <a href="admin/users.php">Manage Users</a>
    <?php endif; ?>

    <?php if (can_user('view_reports')): ?>
        <a href="reports.php">View Reports</a>
    <?php endif; ?>

    <!-- Show only to admins -->
    <?php if (has_role('admin')): ?>
        <a href="admin/settings.php">System Settings</a>
    <?php endif; ?>

    <!-- Show to everyone -->
    <a href="profile.php">My Profile</a>
    <a href="logout.php">Logout</a>
</body>
</html>
```

---

## Step 6: Error Pages

Create user-friendly error pages for middleware failures:

```php
<?php
// includes/middleware.php (enhanced error handling)

function auth(): void
{
    if (!isLoggedIn()) {
        session_set('redirect_after_login', $_SERVER['REQUEST_URI']);

        // Use custom error page
        require_once __DIR__ . '/../errors/401.php';
        exit;
    }
}

function can(string $permission): void
{
    auth();

    global $pdo;
    $userId = session_get('user_id');

    if (!userHasPermission($pdo, $userId, $permission)) {
        // Use custom error page
        $errorMessage = "You don't have permission to access this page.";
        $requiredPermission = $permission;
        require_once __DIR__ . '/../errors/403.php';
        exit;
    }
}
?>
```

### 401 Unauthorized Page

```php
<?php
// errors/401.php
http_response_code(401);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login Required</title>
    <style>
        body {
            font-family: sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: #f5f5f5;
            margin: 0;
        }
        .error-box {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 400px;
        }
        h1 { color: #ff6b6b; margin-bottom: 20px; }
        p { color: #666; margin-bottom: 30px; }
        a {
            display: inline-block;
            padding: 12px 24px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
        }
        a:hover { background: #5568d3; }
    </style>
</head>
<body>
    <div class="error-box">
        <h1>🔒 Login Required</h1>
        <p>You need to be logged in to access this page.</p>
        <a href="/login.php">Go to Login</a>
    </div>
</body>
</html>
```

### 403 Forbidden Page

```php
<?php
// errors/403.php
http_response_code(403);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Access Denied</title>
    <style>
        body {
            font-family: sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: #f5f5f5;
            margin: 0;
        }
        .error-box {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 400px;
        }
        h1 { color: #ff6b6b; margin-bottom: 20px; }
        p { color: #666; margin-bottom: 10px; }
        .permission { color: #999; font-size: 14px; margin-bottom: 30px; }
        a {
            display: inline-block;
            padding: 12px 24px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
        }
        a:hover { background: #5568d3; }
    </style>
</head>
<body>
    <div class="error-box">
        <h1>⛔ Access Denied</h1>
        <p><?= $errorMessage ?? "You don't have permission to access this page." ?></p>
        <?php if (isset($requiredPermission)): ?>
            <div class="permission">Required permission: <?= htmlspecialchars($requiredPermission) ?></div>
        <?php endif; ?>
        <a href="/dashboard.php">Back to Dashboard</a>
    </div>
</body>
</html>
```

---

## Real-World Example: Admin Panel

Let's build a complete admin panel with middleware:

```php
<?php
// admin/index.php
require_once '../includes/middleware.php';

admin(); // Require admin role

global $pdo;
// Get stats...
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel</title>
</head>
<body>
    <h1>Admin Panel</h1>

    <nav>
        <?php if (can_user('view_users')): ?>
            <a href="users.php">Users</a>
        <?php endif; ?>

        <?php if (can_user('view_reports')): ?>
            <a href="reports.php">Reports</a>
        <?php endif; ?>

        <?php if (can_user('manage_settings')): ?>
            <a href="settings.php">Settings</a>
        <?php endif; ?>
    </nav>

    <!-- Admin content -->
</body>
</html>
```

```php
<?php
// admin/users.php
require_once '../includes/middleware.php';

middleware([
    'admin',
    ['can', 'view_users']
]);

// Show users list...
?>
```

```php
<?php
// admin/users/delete.php
require_once '../../includes/middleware.php';

middleware([
    'admin',
    ['can', 'delete_users']
]);

// Delete user...
?>
```

---

## Best Practices

### 1. Always Check at the Top

```php
<?php
// GOOD - Check immediately
require_once 'includes/middleware.php';
protectPage();

// Now safe to proceed...
?>
```

```php
<?php
// BAD - Too late!
echo "Some content";
require_once 'includes/middleware.php';
protectPage(); // Headers already sent!
?>
```

### 2. Be Specific with Permissions

```php
// GOOD - Specific permissions
can('edit_posts');
can('delete_comments');

// BAD - Too broad
admin(); // Gives access to everything!
```

### 3. Fail Securely

```php
// GOOD - Deny by default
if (!userHasPermission($pdo, $userId, 'view_secret')) {
    die('Access denied');
}

// BAD - Grant by default
if (userHasPermission($pdo, $userId, 'view_secret')) {
    // Show content
} // Else shows content anyway!
```

### 4. Log Access Attempts

```php
function can(string $permission): void
{
    auth();

    global $pdo;
    $userId = session_get('user_id');

    if (!userHasPermission($pdo, $userId, $permission)) {
        // Log unauthorized access attempt
        logActivity($pdo, 'unauthorized_access', "Tried to access: $permission");

        http_response_code(403);
        die('Access denied');
    }
}
```

---

## What's Next?

You now understand middleware:
- What it is and why it's useful
- How to build reusable protection
- Permission-based access control
- Middleware chains
- Error handling

**In Lesson 08**, we'll implement **"Remember Me"** functionality for persistent login.

**In Lesson 09**, we'll build **password reset** flows.

---

## Laravel Preview

**Now (Pure PHP):**
```php
<?php
require_once 'includes/middleware.php';

middleware([
    'auth',
    ['can', 'edit_posts']
]);
?>
```

**Laravel:**
```php
// routes/web.php
Route::get('/posts/edit', function() {
    //...
})->middleware(['auth', 'can:edit_posts']);

// Or in controller
class PostController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:edit_posts');
    }
}
```

Laravel provides:
- Built-in middleware system
- Authorization gates and policies
- Role and permission packages (Spatie, etc.)

But now you understand how middleware works under the hood!
