# Lesson 09 - OOP with Databases: Models and Classes

## The Problem with Procedural Database Code

Right now, your database code is scattered everywhere:

```php
<?php
// users.php
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

// posts.php
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

// comments.php
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();
```

**Problems:**
- Repeated code everywhere
- Hard to maintain (change query in 10 places!)
- No structure or organization
- Mixed concerns (database + business logic + presentation)

---

## The Solution: Model Classes

A **Model** is a class that represents a database table and provides methods to work with that data.

```php
<?php
class User {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function find(int $id): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function all(): array {
        $stmt = $this->pdo->query("SELECT * FROM users");
        return $stmt->fetchAll();
    }
}

// Usage - clean and reusable!
$userModel = new User($pdo);
$user = $userModel->find(1);
$allUsers = $userModel->all();
```

**Benefits:**
- Database queries in one place
- Reusable across the application
- Easy to test and maintain
- Clear separation of concerns

---

## Creating Your First Model

Let's build a `User` model step by step:

```php
<?php
// User.php

class User {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Get all users
     */
    public function all(): array {
        $stmt = $this->pdo->query("SELECT * FROM users ORDER BY created_at DESC");
        return $stmt->fetchAll();
    }

    /**
     * Find user by ID
     */
    public function find(int $id): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Find user by email
     */
    public function findByEmail(string $email): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Create a new user
     */
    public function create(array $data): int {
        $sql = "INSERT INTO users (name, email, password) VALUES (:name, :email, :password)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => password_hash($data['password'], PASSWORD_DEFAULT)
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    /**
     * Update a user
     */
    public function update(int $id, array $data): bool {
        $sql = "UPDATE users SET name = :name, email = :email WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'name' => $data['name'],
            'email' => $data['email'],
            'id' => $id
        ]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Delete a user
     */
    public function delete(int $id): bool {
        $stmt = $this->pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }
}
```

---

## Using the Model

```php
<?php
require_once 'db.php';
require_once 'User.php';

$userModel = new User($pdo);

// Get all users
$users = $userModel->all();
foreach ($users as $user) {
    echo $user['name'] . "<br>";
}

// Find one user
$user = $userModel->find(1);
if ($user) {
    echo "Found: " . $user['name'];
}

// Create new user
$newId = $userModel->create([
    'name' => 'Kieu',
    'email' => 'kieu@example.com',
    'password' => 'secret123'
]);
echo "Created user with ID: $newId";

// Update user
$updated = $userModel->update(1, [
    'name' => 'Kieu Nguyen',
    'email' => 'kieu@example.com'
]);

// Delete user
$deleted = $userModel->delete(5);
```

**Much cleaner!** All database logic is in the model.

---

## Post Model Example

```php
<?php
// Post.php

class Post {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function all(): array {
        $sql = "
            SELECT
                posts.*,
                users.name as author_name
            FROM posts
            INNER JOIN users ON posts.user_id = users.id
            ORDER BY posts.created_at DESC
        ";
        return $this->pdo->query($sql)->fetchAll();
    }

    public function find(int $id): ?array {
        $sql = "
            SELECT
                posts.*,
                users.name as author_name,
                users.email as author_email
            FROM posts
            INNER JOIN users ON posts.user_id = users.id
            WHERE posts.id = ?
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function findByUser(int $userId): array {
        $sql = "SELECT * FROM posts WHERE user_id = ? ORDER BY created_at DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function create(array $data): int {
        $sql = "INSERT INTO posts (user_id, title, content) VALUES (?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $data['user_id'],
            $data['title'],
            $data['content']
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): bool {
        $sql = "UPDATE posts SET title = ?, content = ? WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $data['title'],
            $data['content'],
            $id
        ]);
        return $stmt->rowCount() > 0;
    }

    public function delete(int $id): bool {
        $stmt = $this->pdo->prepare("DELETE FROM posts WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }
}
```

---

## Database Class: Centralized Connection

Instead of passing `$pdo` everywhere, create a Database class:

```php
<?php
// Database.php

class Database {
    private static ?PDO $pdo = null;

    public static function connect(): PDO {
        if (self::$pdo === null) {
            $host = '127.0.0.1';
            $dbname = 'my_app';
            $username = 'root';
            $password = '';

            $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            self::$pdo = new PDO($dsn, $username, $password, $options);
        }

        return self::$pdo;
    }
}
```

**Usage:**

```php
<?php
require_once 'Database.php';
require_once 'User.php';

$pdo = Database::connect();
$userModel = new User($pdo);
$users = $userModel->all();
```

---

## Base Model: Reduce Duplication

Many models share common methods. Create a base class:

```php
<?php
// Model.php (Base Class)

abstract class Model {
    protected PDO $pdo;
    protected string $table;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function all(): array {
        $stmt = $this->pdo->query("SELECT * FROM {$this->table}");
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function delete(int $id): bool {
        $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }
}
```

**Extend in specific models:**

```php
<?php
// User.php

class User extends Model {
    protected string $table = 'users';

    public function findByEmail(string $email): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $data): int {
        $sql = "INSERT INTO {$this->table} (name, email, password) VALUES (?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $data['name'],
            $data['email'],
            password_hash($data['password'], PASSWORD_DEFAULT)
        ]);
        return (int) $this->pdo->lastInsertId();
    }
}
```

**Now User has both inherited methods (all, find, delete) AND custom methods (findByEmail, create)!**

---

## Active Record Pattern

The **Active Record** pattern represents a row as an object:

```php
<?php
class User {
    private PDO $pdo;

    public int $id;
    public string $name;
    public string $email;
    public string $created_at;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Load user from database
     */
    public static function find(PDO $pdo, int $id): ?self {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch();

        if (!$data) {
            return null;
        }

        $user = new self($pdo);
        $user->id = $data['id'];
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->created_at = $data['created_at'];

        return $user;
    }

    /**
     * Save current object to database
     */
    public function save(): bool {
        if (isset($this->id)) {
            // Update existing
            $sql = "UPDATE users SET name = ?, email = ? WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([$this->name, $this->email, $this->id]);
        } else {
            // Insert new
            $sql = "INSERT INTO users (name, email) VALUES (?, ?)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$this->name, $this->email]);
            $this->id = (int) $this->pdo->lastInsertId();
            return true;
        }
    }

    /**
     * Delete this user
     */
    public function delete(): bool {
        $stmt = $this->pdo->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([$this->id]);
    }

    /**
     * Get user's posts
     */
    public function posts(): array {
        $stmt = $this->pdo->prepare("SELECT * FROM posts WHERE user_id = ?");
        $stmt->execute([$this->id]);
        return $stmt->fetchAll();
    }
}
```

**Usage:**

```php
<?php
// Load a user
$user = User::find($pdo, 1);

// Modify and save
$user->name = "Kieu Nguyen";
$user->save();

// Get their posts
$posts = $user->posts();

// Delete
$user->delete();

// Create new user
$newUser = new User($pdo);
$newUser->name = "John";
$newUser->email = "john@example.com";
$newUser->save();  // Inserts into database
```

**This is closer to Laravel's Eloquent!**

---

## Relationships with Models

### User has many Posts

```php
<?php
// User.php
class User extends Model {
    public function posts(): array {
        $stmt = $this->pdo->prepare("SELECT * FROM posts WHERE user_id = ?");
        $stmt->execute([$this->id]);
        return $stmt->fetchAll();
    }
}

// Usage
$user = User::find($pdo, 1);
$posts = $user->posts();
```

### Post belongs to User

```php
<?php
// Post.php
class Post extends Model {
    public function user(): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$this->user_id]);
        return $stmt->fetch() ?: null;
    }
}

// Usage
$post = Post::find($pdo, 1);
$author = $post->user();
echo "Written by: " . $author['name'];
```

### Post has many Comments

```php
<?php
// Post.php
class Post extends Model {
    public function comments(): array {
        $stmt = $this->pdo->prepare("
            SELECT comments.*, users.name as author_name
            FROM comments
            INNER JOIN users ON comments.user_id = users.id
            WHERE comments.post_id = ?
            ORDER BY comments.created_at ASC
        ");
        $stmt->execute([$this->id]);
        return $stmt->fetchAll();
    }
}

// Usage
$post = Post::find($pdo, 1);
$comments = $post->comments();
```

---

## Repository Pattern (Alternative)

Instead of Active Record, use repositories for better separation:

```php
<?php
// UserRepository.php

class UserRepository {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function all(): array {
        return $this->pdo->query("SELECT * FROM users")->fetchAll();
    }

    public function find(int $id): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function create(string $name, string $email, string $password): int {
        $sql = "INSERT INTO users (name, email, password) VALUES (?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT)]);
        return (int) $this->pdo->lastInsertId();
    }
}

// Usage
$userRepo = new UserRepository($pdo);
$users = $userRepo->all();
$user = $userRepo->find(1);
$newId = $userRepo->create('Kieu', 'kieu@example.com', 'password');
```

**Difference:**
- **Active Record:** User object knows how to save itself
- **Repository:** Separate class handles database operations

**Both are valid!** Laravel uses Active Record (Eloquent).

---

## Query Builder: More Flexibility

For complex queries, add a query builder:

```php
<?php
class QueryBuilder {
    private PDO $pdo;
    private string $table;
    private array $wheres = [];
    private array $bindings = [];
    private string $orderBy = '';
    private string $limit = '';

    public function __construct(PDO $pdo, string $table) {
        $this->pdo = $pdo;
        $this->table = $table;
    }

    public function where(string $column, string $operator, $value): self {
        $this->wheres[] = "$column $operator ?";
        $this->bindings[] = $value;
        return $this;
    }

    public function orderBy(string $column, string $direction = 'ASC'): self {
        $this->orderBy = "ORDER BY $column $direction";
        return $this;
    }

    public function limit(int $limit): self {
        $this->limit = "LIMIT $limit";
        return $this;
    }

    public function get(): array {
        $sql = "SELECT * FROM {$this->table}";

        if (!empty($this->wheres)) {
            $sql .= " WHERE " . implode(' AND ', $this->wheres);
        }

        if ($this->orderBy) {
            $sql .= " " . $this->orderBy;
        }

        if ($this->limit) {
            $sql .= " " . $this->limit;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($this->bindings);
        return $stmt->fetchAll();
    }
}

// Usage
$qb = new QueryBuilder($pdo, 'users');
$users = $qb->where('age', '>', 25)
            ->where('is_active', '=', 1)
            ->orderBy('created_at', 'DESC')
            ->limit(10)
            ->get();
```

**This is what Laravel's query builder does!**

---

## Validation in Models

Add validation methods:

```php
<?php
class User extends Model {
    public function validate(array $data): array {
        $errors = [];

        if (empty($data['name'])) {
            $errors['name'] = 'Name is required';
        }

        if (empty($data['email'])) {
            $errors['email'] = 'Email is required';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format';
        } elseif ($this->emailExists($data['email'])) {
            $errors['email'] = 'Email already taken';
        }

        if (empty($data['password'])) {
            $errors['password'] = 'Password is required';
        } elseif (strlen($data['password']) < 8) {
            $errors['password'] = 'Password must be at least 8 characters';
        }

        return $errors;
    }

    private function emailExists(string $email): bool {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetchColumn() > 0;
    }
}

// Usage
$userModel = new User($pdo);
$errors = $userModel->validate($_POST);

if (empty($errors)) {
    $userModel->create($_POST);
} else {
    foreach ($errors as $field => $error) {
        echo "$field: $error<br>";
    }
}
```

---

## Complete Example: Blog with Models

### Directory Structure

```
/project
  /models
    Database.php
    Model.php
    User.php
    Post.php
    Comment.php
  /views
    posts.php
    post.php
  index.php
```

### Database.php

```php
<?php
class Database {
    private static ?PDO $pdo = null;

    public static function connect(): PDO {
        if (self::$pdo === null) {
            $dsn = "mysql:host=127.0.0.1;dbname=blog;charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ];
            self::$pdo = new PDO($dsn, 'root', '', $options);
        }
        return self::$pdo;
    }
}
```

### Model.php

```php
<?php
abstract class Model {
    protected PDO $pdo;
    protected string $table;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function all(): array {
        return $this->pdo->query("SELECT * FROM {$this->table}")->fetchAll();
    }

    public function find(int $id): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }
}
```

### Post.php

```php
<?php
class Post extends Model {
    protected string $table = 'posts';

    public function allWithAuthors(): array {
        $sql = "
            SELECT posts.*, users.name as author_name
            FROM posts
            JOIN users ON posts.user_id = users.id
            ORDER BY posts.created_at DESC
        ";
        return $this->pdo->query($sql)->fetchAll();
    }

    public function findWithAuthor(int $id): ?array {
        $sql = "
            SELECT posts.*, users.name as author_name, users.email as author_email
            FROM posts
            JOIN users ON posts.user_id = users.id
            WHERE posts.id = ?
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }
}
```

### index.php

```php
<?php
require_once 'models/Database.php';
require_once 'models/Model.php';
require_once 'models/Post.php';

$pdo = Database::connect();
$postModel = new Post($pdo);
$posts = $postModel->allWithAuthors();

require 'views/posts.php';
```

### views/posts.php

```php
<!DOCTYPE html>
<html>
<head>
    <title>Blog Posts</title>
</head>
<body>
    <h1>All Posts</h1>
    <?php foreach ($posts as $post): ?>
        <article>
            <h2><?= htmlspecialchars($post['title']) ?></h2>
            <p>By <?= htmlspecialchars($post['author_name']) ?></p>
            <p><?= htmlspecialchars(substr($post['content'], 0, 200)) ?>...</p>
            <a href="post.php?id=<?= $post['id'] ?>">Read more</a>
        </article>
    <?php endforeach; ?>
</body>
</html>
```

---

## Key Takeaways

1. **Models represent tables** - One class per table
2. **Encapsulate database logic** - No SQL in views or controllers
3. **Reusable methods** - find(), all(), create(), update(), delete()
4. **Base class reduces duplication** - Extend Model for common functionality
5. **Active Record = objects represent rows** - $user->save()
6. **Repository = separate query logic** - UserRepository->create()
7. **Relationships as methods** - $user->posts(), $post->comments()
8. **Validation in models** - Keep business logic centralized

---

## What's Next?

You now know how to organize database code with OOP! One more critical topic: **transactions**.

**In the next lesson**, you'll learn how to ensure data integrity when multiple operations must succeed or fail together (like transferring money between accounts).

---

**Next Lesson:** [10 - Transactions: Atomic Operations](./10-transactions.md)
