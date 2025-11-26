# Lesson 09 - Access Control & Authorization

**Duration**: 60 minutes

---

## Introduction: Who Can Do What?

**Authentication** asks: "Who are you?" (covered in Module 07)
**Authorization** asks: "What are you allowed to do?"

You can be authenticated (logged in) but not authorized (permission denied).

**Real-world breaches**:
- **Instagram (2019)**: Broken access control allowed viewing private accounts
- **Facebook (2018)**: API flaw exposed 50 million accounts
- **Uber (2016)**: Broken access control led to driver records exposure

Access control vulnerabilities are consistently in the OWASP Top 10 and are often overlooked by developers who focus on authentication.

---

## Types of Access Control Vulnerabilities

### 1. Horizontal Privilege Escalation

**Definition**: User accesses another user's resources at the same privilege level.

```php
// VULNERABLE CODE
// profile.php?user_id=123

$userId = $_GET['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

echo "Email: " . $user['email'];
echo "Phone: " . $user['phone'];

// Problem: User 123 can view user 456's profile by changing URL!
// /profile.php?user_id=456
```

**Attack**: Change `user_id` parameter to access other users' data.

**Fix**: Check authorization:
```php
// SECURE
$requestedUserId = $_GET['user_id'];
$currentUserId = $_SESSION['user_id'];

// Users can only view their own profile
if ($requestedUserId != $currentUserId) {
    http_response_code(403);
    die('Access denied');
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$currentUserId]); // Use session ID, not request parameter
$user = $stmt->fetch();
```

### 2. Vertical Privilege Escalation

**Definition**: User gains access to admin/higher-privilege functionality.

```php
// VULNERABLE CODE
// delete-user.php?id=123

$userId = $_GET['id'];

// No authorization check!
$stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
$stmt->execute([$userId]);

// Problem: Any logged-in user can delete any account!
```

**Fix**: Check role/permissions:
```php
// SECURE
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    http_response_code(403);
    die('Admin access required');
}

$userId = $_GET['id'];
$stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
$stmt->execute([$userId]);
```

### 3. Insecure Direct Object References (IDOR)

**Definition**: Application exposes internal implementation objects (IDs, filenames) without access checks.

```php
// VULNERABLE CODE
// download.php?file=invoice_123.pdf

$filename = $_GET['file'];
$path = '/var/invoices/' . $filename;

// No check if user owns this invoice!
readfile($path);
```

**Fix**: Check ownership:
```php
// SECURE
$fileId = $_GET['id']; // Use ID, not filename

// Get file info and check ownership
$stmt = $pdo->prepare("SELECT * FROM invoices WHERE id = ? AND user_id = ?");
$stmt->execute([$fileId, $_SESSION['user_id']]);
$invoice = $stmt->fetch();

if (!$invoice) {
    http_response_code(404);
    die('Invoice not found or access denied');
}

readfile('/var/invoices/' . $invoice['filename']);
```

### 4. Missing Function-Level Access Control

**Definition**: Application checks authorization on the UI but not on the backend.

```php
// VULNERABLE CODE

<!-- Frontend: Hide delete button for non-admins -->
<?php if ($_SESSION['is_admin']): ?>
    <button onclick="deleteUser(123)">Delete</button>
<?php endif; ?>

// Backend: delete-user.php
// No authorization check!
$userId = $_POST['user_id'];
$pdo->query("DELETE FROM users WHERE id = $userId");

// Problem: Anyone can call delete-user.php directly!
```

**Fix**: Always check authorization on backend:
```php
// SECURE
// delete-user.php

// Backend authorization check
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    http_response_code(403);
    die('Access denied');
}

$userId = $_POST['user_id'];
$stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
$stmt->execute([$userId]);
```

---

## Building an Access Control System

### Simple Role-Based Access Control (RBAC)

```php
<?php
// User roles
class Role {
    const GUEST = 'guest';
    const USER = 'user';
    const MODERATOR = 'moderator';
    const ADMIN = 'admin';
}

// Get user role from session
function getUserRole(): string {
    return $_SESSION['role'] ?? Role::GUEST;
}

// Check if user has role
function hasRole(string $role): bool {
    return getUserRole() === $role;
}

// Check if user has at least this role level
function hasRoleOrHigher(string $minRole): bool {
    $hierarchy = [
        Role::GUEST => 0,
        Role::USER => 1,
        Role::MODERATOR => 2,
        Role::ADMIN => 3
    ];

    $userLevel = $hierarchy[getUserRole()] ?? 0;
    $requiredLevel = $hierarchy[$minRole] ?? 999;

    return $userLevel >= $requiredLevel;
}

// Require specific role or die
function requireRole(string $role): void {
    if (!hasRole($role)) {
        http_response_code(403);
        die('Access denied - ' . $role . ' role required');
    }
}

// Require minimum role level
function requireRoleOrHigher(string $minRole): void {
    if (!hasRoleOrHigher($minRole)) {
        http_response_code(403);
        die('Access denied - insufficient privileges');
    }
}
```

### Usage

```php
<?php
// admin-panel.php
session_start();
require_once 'access-control.php';

// Only admins can access
requireRole(Role::ADMIN);

echo "<h1>Admin Panel</h1>";
?>

<?php
// moderate-comments.php
requireRoleOrHigher(Role::MODERATOR);
// Moderators and admins can access
?>

<?php
// user-profile.php
requireRoleOrHigher(Role::USER);
// All logged-in users can access
?>
```

### Permission-Based Access Control

More flexible than roles - check specific permissions:

```php
<?php
// Permission constants
class Permission {
    const VIEW_USERS = 'view_users';
    const EDIT_USERS = 'edit_users';
    const DELETE_USERS = 'delete_users';
    const VIEW_POSTS = 'view_posts';
    const EDIT_POSTS = 'edit_posts';
    const DELETE_POSTS = 'delete_posts';
    const MANAGE_SETTINGS = 'manage_settings';
}

// Get user permissions from database
function getUserPermissions(int $userId): array {
    global $pdo;

    $stmt = $pdo->prepare("
        SELECT p.permission_name
        FROM user_permissions up
        JOIN permissions p ON up.permission_id = p.id
        WHERE up.user_id = ?
    ");
    $stmt->execute([$userId]);

    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

// Check if user has permission
function hasPermission(string $permission): bool {
    if (!isset($_SESSION['user_id'])) {
        return false;
    }

    // Cache permissions in session
    if (!isset($_SESSION['permissions'])) {
        $_SESSION['permissions'] = getUserPermissions($_SESSION['user_id']);
    }

    return in_array($permission, $_SESSION['permissions'], true);
}

// Require permission or die
function requirePermission(string $permission): void {
    if (!hasPermission($permission)) {
        http_response_code(403);
        die('Access denied - permission required: ' . $permission);
    }
}

// Check multiple permissions (OR logic)
function hasAnyPermission(array $permissions): bool {
    foreach ($permissions as $permission) {
        if (hasPermission($permission)) {
            return true;
        }
    }
    return false;
}

// Check multiple permissions (AND logic)
function hasAllPermissions(array $permissions): bool {
    foreach ($permissions as $permission) {
        if (!hasPermission($permission)) {
            return false;
        }
    }
    return true;
}
```

### Database Schema

```sql
-- Users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('guest', 'user', 'moderator', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Permissions table
CREATE TABLE permissions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    permission_name VARCHAR(50) UNIQUE NOT NULL,
    description TEXT
);

-- User permissions (many-to-many)
CREATE TABLE user_permissions (
    user_id INT NOT NULL,
    permission_id INT NOT NULL,
    granted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, permission_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
);

-- Role permissions (assign permissions to roles)
CREATE TABLE role_permissions (
    role VARCHAR(20) NOT NULL,
    permission_id INT NOT NULL,
    PRIMARY KEY (role, permission_id),
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
);

-- Seed permissions
INSERT INTO permissions (permission_name, description) VALUES
('view_users', 'View user list and profiles'),
('edit_users', 'Edit user information'),
('delete_users', 'Delete users'),
('view_posts', 'View posts'),
('edit_posts', 'Edit posts'),
('delete_posts', 'Delete posts'),
('manage_settings', 'Manage application settings');

-- Assign permissions to roles
INSERT INTO role_permissions (role, permission_id) VALUES
('admin', 1), ('admin', 2), ('admin', 3), ('admin', 4), ('admin', 5), ('admin', 6), ('admin', 7),
('moderator', 1), ('moderator', 4), ('moderator', 5), ('moderator', 6),
('user', 4);
```

---

## Resource Ownership

Check if user owns the resource they're trying to access:

```php
<?php
// Check if user owns a post
function ownsPost(int $postId, int $userId): bool {
    global $pdo;

    $stmt = $pdo->prepare("SELECT user_id FROM posts WHERE id = ?");
    $stmt->execute([$postId]);
    $post = $stmt->fetch();

    return $post && $post['user_id'] === $userId;
}

// Edit post (must own it or be admin)
// edit-post.php
$postId = $_GET['id'];

if (!ownsPost($postId, $_SESSION['user_id']) && !hasRole(Role::ADMIN)) {
    http_response_code(403);
    die('You can only edit your own posts');
}

// Proceed with edit...
```

### Generic Ownership Check

```php
<?php
function ownsResource(string $table, int $resourceId, int $userId): bool {
    global $pdo;

    // Whitelist allowed tables
    $allowedTables = ['posts', 'comments', 'files', 'invoices'];

    if (!in_array($table, $allowedTables, true)) {
        throw new InvalidArgumentException('Invalid table');
    }

    $stmt = $pdo->prepare("SELECT user_id FROM $table WHERE id = ?");
    $stmt->execute([$resourceId]);
    $resource = $stmt->fetch();

    return $resource && $resource['user_id'] === $userId;
}

// Usage
if (!ownsResource('comments', $commentId, $_SESSION['user_id'])) {
    die('Access denied');
}
```

---

## Complete Authorization Class

```php
<?php
// Authorization.php

class Authorization {

    private PDO $pdo;
    private ?int $userId;
    private ?string $userRole;
    private array $permissions = [];

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
        $this->userId = $_SESSION['user_id'] ?? null;
        $this->userRole = $_SESSION['role'] ?? null;

        // Load permissions if user is logged in
        if ($this->userId) {
            $this->loadPermissions();
        }
    }

    /**
     * Check if user is logged in
     */
    public function isLoggedIn(): bool {
        return $this->userId !== null;
    }

    /**
     * Require login or redirect
     */
    public function requireLogin(string $redirectTo = '/login.php'): void {
        if (!$this->isLoggedIn()) {
            header('Location: ' . $redirectTo);
            exit;
        }
    }

    /**
     * Check if user has specific role
     */
    public function hasRole(string $role): bool {
        return $this->userRole === $role;
    }

    /**
     * Require specific role
     */
    public function requireRole(string $role): void {
        if (!$this->hasRole($role)) {
            http_response_code(403);
            die('Access denied - role required: ' . $role);
        }
    }

    /**
     * Check if user has permission
     */
    public function hasPermission(string $permission): bool {
        return in_array($permission, $this->permissions, true);
    }

    /**
     * Require permission
     */
    public function requirePermission(string $permission): void {
        if (!$this->hasPermission($permission)) {
            http_response_code(403);
            die('Access denied - permission required: ' . $permission);
        }
    }

    /**
     * Check if user has any of the permissions
     */
    public function hasAnyPermission(array $permissions): bool {
        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if user owns resource
     */
    public function owns(string $table, int $resourceId): bool {
        if (!$this->userId) {
            return false;
        }

        // Whitelist allowed tables
        $allowedTables = ['posts', 'comments', 'files', 'invoices', 'documents'];

        if (!in_array($table, $allowedTables, true)) {
            throw new InvalidArgumentException('Invalid table for ownership check');
        }

        $stmt = $this->pdo->prepare("SELECT user_id FROM $table WHERE id = ?");
        $stmt->execute([$resourceId]);
        $resource = $stmt->fetch();

        return $resource && $resource['user_id'] === $this->userId;
    }

    /**
     * Check if user can access resource (owns it OR has permission)
     */
    public function canAccess(string $table, int $resourceId, string $permission): bool {
        return $this->owns($table, $resourceId) || $this->hasPermission($permission);
    }

    /**
     * Require ownership or permission
     */
    public function requireOwnershipOrPermission(string $table, int $resourceId, string $permission): void {
        if (!$this->canAccess($table, $resourceId, $permission)) {
            http_response_code(403);
            die('Access denied - you must own this resource or have permission: ' . $permission);
        }
    }

    /**
     * Load user permissions from database
     */
    private function loadPermissions(): void {
        // Get permissions directly assigned to user
        $stmt = $this->pdo->prepare("
            SELECT p.permission_name
            FROM user_permissions up
            JOIN permissions p ON up.permission_id = p.id
            WHERE up.user_id = ?
        ");
        $stmt->execute([$this->userId]);
        $userPermissions = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // Get permissions from role
        $rolePermissions = [];
        if ($this->userRole) {
            $stmt = $this->pdo->prepare("
                SELECT p.permission_name
                FROM role_permissions rp
                JOIN permissions p ON rp.permission_id = p.id
                WHERE rp.role = ?
            ");
            $stmt->execute([$this->userRole]);
            $rolePermissions = $stmt->fetchAll(PDO::FETCH_COLUMN);
        }

        // Merge and deduplicate
        $this->permissions = array_unique(array_merge($userPermissions, $rolePermissions));
    }

    /**
     * Get current user ID
     */
    public function getUserId(): ?int {
        return $this->userId;
    }

    /**
     * Get current user role
     */
    public function getRole(): ?string {
        return $this->userRole;
    }

    /**
     * Get all user permissions
     */
    public function getPermissions(): array {
        return $this->permissions;
    }
}
```

### Using the Authorization Class

```php
<?php
// bootstrap.php
session_start();
require_once 'Database.php';
require_once 'Authorization.php';

$db = new Database('mysql:host=localhost;dbname=myapp', 'user', 'pass');
$pdo = $db->getPdo(); // Assume Database class exposes PDO

$auth = new Authorization($pdo);
```

```php
<?php
// admin-panel.php
require_once 'bootstrap.php';

// Require login
$auth->requireLogin();

// Require admin role
$auth->requireRole('admin');

echo "<h1>Admin Panel</h1>";
?>
```

```php
<?php
// edit-post.php
require_once 'bootstrap.php';

$auth->requireLogin();

$postId = $_GET['id'];

// User must own the post OR have edit_posts permission
$auth->requireOwnershipOrPermission('posts', $postId, 'edit_posts');

// Proceed with editing...
?>
```

```php
<?php
// delete-comment.php
require_once 'bootstrap.php';

$auth->requireLogin();

$commentId = $_GET['id'];

// Only owner or moderator+ can delete
if (!$auth->owns('comments', $commentId) && !$auth->hasPermission('delete_comments')) {
    http_response_code(403);
    die('You can only delete your own comments');
}

// Delete comment...
?>
```

---

## Access Control Best Practices

### 1. Always Check Authorization Server-Side

```php
// BAD - Only hiding UI
<?php if ($_SESSION['is_admin']): ?>
    <button id="delete">Delete User</button>
<?php endif; ?>

<script>
// No server-side check!
document.getElementById('delete').onclick = function() {
    fetch('/api/delete-user', {method: 'POST', body: 'id=123'});
};
</script>

// GOOD - Server-side check
// api/delete-user.php
$auth->requireRole('admin');
// Now process delete
```

### 2. Deny by Default

```php
// BAD - Allow unless explicitly denied
if ($action === 'delete' && !$auth->hasRole('admin')) {
    die('Access denied');
}
// Proceeds if action is anything else!

// GOOD - Deny unless explicitly allowed
if ($action === 'delete') {
    $auth->requireRole('admin');
}
// Clear permission check for specific action
```

### 3. Use IDs, Not Sensitive Data in URLs

```php
// BAD - Exposes email
/profile.php?email=john@example.com

// GOOD - Uses non-sensitive ID
/profile.php?id=123
```

### 4. Check Ownership at Data Layer

```php
// BAD - Fetch first, check later
$stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ?");
$stmt->execute([$postId]);
$post = $stmt->fetch();

if ($post['user_id'] !== $_SESSION['user_id']) {
    die('Access denied');
}

// GOOD - Check in query
$stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ? AND user_id = ?");
$stmt->execute([$postId, $_SESSION['user_id']]);
$post = $stmt->fetch();

if (!$post) {
    die('Post not found or access denied');
}
```

### 5. Log Access Control Failures

```php
function logAccessDenied(string $resource, string $action, ?int $userId): void {
    error_log(sprintf(
        '[ACCESS DENIED] User %d attempted %s on %s at %s from IP %s',
        $userId ?? 0,
        $action,
        $resource,
        date('Y-m-d H:i:s'),
        $_SERVER['REMOTE_ADDR']
    ));
}

// Usage
if (!$auth->owns('posts', $postId)) {
    logAccessDenied("post:{$postId}", 'delete', $auth->getUserId());
    http_response_code(403);
    die('Access denied');
}
```

---

## Testing Access Control

### Manual Testing Checklist

For every protected resource:

- [ ] Try accessing without login
- [ ] Try accessing as different user (horizontal escalation)
- [ ] Try accessing as lower privilege user (vertical escalation)
- [ ] Try manipulating IDs in URLs/forms
- [ ] Try accessing with direct URL (bypass navigation)
- [ ] Check that UI restrictions match backend restrictions

### Example Test Script

```php
<?php
// test-access-control.php

// Test 1: Horizontal privilege escalation
echo "Test 1: Can user 1 access user 2's profile?\n";
$_SESSION['user_id'] = 1;
// Try to access /profile.php?id=2
// Expected: 403 Forbidden

// Test 2: Vertical privilege escalation
echo "Test 2: Can regular user access admin panel?\n";
$_SESSION['role'] = 'user';
// Try to access /admin/panel.php
// Expected: 403 Forbidden

// Test 3: Direct object reference
echo "Test 3: Can user access someone else's invoice?\n";
// Try to access /download.php?invoice_id=999
// Expected: 404 or 403

// Test 4: Function-level access
echo "Test 4: Can regular user call admin API?\n";
// Try POST to /api/delete-user
// Expected: 403 Forbidden
```

---

## Access Control Checklist

- [ ] **Authentication before authorization** - Check user is logged in
- [ ] **Check authorization on every request** - Don't cache authorization decisions
- [ ] **Server-side checks** - Never rely on client-side only
- [ ] **Deny by default** - Explicitly allow, don't explicitly deny
- [ ] **Check ownership** - Users can only access their own resources
- [ ] **Role-based or permission-based** - Implement appropriate model
- [ ] **Database-level checks** - Include user_id in queries
- [ ] **Log access failures** - Monitor for attack attempts
- [ ] **Test thoroughly** - Try to bypass your own access control
- [ ] **Consistent enforcement** - Use same logic across all endpoints

---

## Key Takeaways

1. **Authorization checks are critical** - Being logged in doesn't mean access to everything
2. **Horizontal escalation**: User accessing another user's data
3. **Vertical escalation**: User accessing admin functions
4. **Check ownership** - Users can only access their own resources
5. **Server-side enforcement** - Never trust client-side restrictions
6. **Role-based or permission-based** - Choose model that fits your needs
7. **Deny by default** - Explicit allowlist approach
8. **Test extensively** - Try to bypass as attacker would
9. **Log failures** - Monitor for attack attempts
10. **Consistent implementation** - Use authorization class everywhere

---

## What's Next?

Access control covered! Next: **Security Headers** - HTTP headers that add layers of protection against various attacks.

You'll learn:
- X-Frame-Options (clickjacking protection)
- X-Content-Type-Options (MIME sniffing prevention)
- Content-Security-Policy (XSS prevention)
- Strict-Transport-Security (HTTPS enforcement)
- And more headers that harden your application!
