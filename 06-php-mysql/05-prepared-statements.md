# Lesson 05 - Prepared Statements: Security First

## The Problem: SQL Injection

Imagine you have a login form:

```php
<?php
$email = $_POST['email'];
$password = $_POST['password'];

// L DANGEROUS CODE - DON'T DO THIS!
$sql = "SELECT * FROM users WHERE email = '$email' AND password = '$password'";
$stmt = $pdo->query($sql);
$user = $stmt->fetch();

if ($user) {
    echo "Logged in!";
} else {
    echo "Invalid credentials";
}
```

**Looks innocent, right? This is one of the most dangerous vulnerabilities in web development.**

---

## How SQL Injection Works

A malicious user enters this as their email:

```
admin@example.com' OR '1'='1
```

Your SQL becomes:

```sql
SELECT * FROM users
WHERE email = 'admin@example.com' OR '1'='1' AND password = 'anything'
```

**`'1'='1'` is always true!** This query returns ALL users. The attacker just logged in as the first user (probably admin) **without knowing the password.**

### Another Attack: Deleting Your Database

User enters this as email:

```
'; DROP TABLE users; --
```

Your SQL becomes:

```sql
SELECT * FROM users WHERE email = ''; DROP TABLE users; --' AND password = 'x'
```

**Your entire users table is now deleted.** This is called **SQL injection**, and it's the #1 database attack.

---

## The Solution: Prepared Statements

**Prepared statements** separate SQL code from user data:

```php
<?php
//  SAFE - Data is treated as data, never as SQL code
$sql = "SELECT * FROM users WHERE email = ? AND password = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$email, $password]);
$user = $stmt->fetch();
```

**What happens?**

1. `prepare()` sends SQL with placeholders (`?`) to MySQL
2. MySQL compiles the SQL (placeholders are marked as "data slots")
3. `execute()` sends the actual data
4. MySQL inserts data into those slots **but never interprets it as SQL**

**Even if the email contains `' OR '1'='1`, it's treated as literal text, not SQL code.**

---

## Two Types of Placeholders

### 1. Positional Placeholders (`?`)

```php
<?php
$sql = "INSERT INTO users (name, email, age) VALUES (?, ?, ?)";
$stmt = $pdo->prepare($sql);
$stmt->execute(['Kieu', 'kieu@example.com', 28]);
```

**Order matters!** First `?` gets 'Kieu', second gets 'kieu@example.com', third gets 28.

### 2. Named Placeholders (`:name`)

```php
<?php
$sql = "INSERT INTO users (name, email, age) VALUES (:name, :email, :age)";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    'name' => 'Kieu',
    'email' => 'kieu@example.com',
    'age' => 28
]);
```

**Order doesn't matter!** You use an associative array with keys matching placeholder names.

---

## When to Use Which?

### Use Positional (`?`) When:
- You have few parameters (1-3)
- Parameters appear in order
- Quick, simple queries

```php
<?php
// Simple and clean
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
```

### Use Named (`:name`) When:
- You have many parameters
- Same parameter used multiple times
- More readable code

```php
<?php
// Clear what each value is for
$stmt = $pdo->prepare("
    INSERT INTO posts (user_id, title, content, category)
    VALUES (:user_id, :title, :content, :category)
");
$stmt->execute([
    'user_id' => $userId,
    'title' => $title,
    'content' => $content,
    'category' => $category
]);
```

**My recommendation:** Use named placeholders for anything beyond 2-3 parameters. Your future self will thank you!

---

## Complete Examples

### SELECT with Prepared Statement

```php
<?php
require_once 'db.php';

$email = $_POST['email'];

// Prepare the statement
$sql = "SELECT * FROM users WHERE email = :email";
$stmt = $pdo->prepare($sql);

// Execute with data
$stmt->execute(['email' => $email]);

// Fetch result
$user = $stmt->fetch();

if ($user) {
    echo "Found user: " . $user['name'];
} else {
    echo "User not found";
}
```

### INSERT with Prepared Statement

```php
<?php
$name = $_POST['name'];
$email = $_POST['email'];
$age = $_POST['age'];

$sql = "INSERT INTO users (name, email, age) VALUES (:name, :email, :age)";
$stmt = $pdo->prepare($sql);

$stmt->execute([
    'name' => $name,
    'email' => $email,
    'age' => $age
]);

echo "User created with ID: " . $pdo->lastInsertId();
```

### UPDATE with Prepared Statement

```php
<?php
$userId = $_GET['id'];
$newEmail = $_POST['email'];
$newAge = $_POST['age'];

$sql = "UPDATE users SET email = :email, age = :age WHERE id = :id";
$stmt = $pdo->prepare($sql);

$stmt->execute([
    'email' => $newEmail,
    'age' => $newAge,
    'id' => $userId
]);

if ($stmt->rowCount() > 0) {
    echo "User updated!";
} else {
    echo "No user found with that ID";
}
```

### DELETE with Prepared Statement

```php
<?php
$userId = $_GET['id'];

$sql = "DELETE FROM users WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['id' => $userId]);

if ($stmt->rowCount() > 0) {
    echo "User deleted!";
} else {
    echo "No user found with that ID";
}
```

---

## Multiple WHERE Conditions

```php
<?php
$minAge = $_GET['min_age'];
$maxAge = $_GET['max_age'];
$city = $_GET['city'];

$sql = "SELECT * FROM users
        WHERE age >= :min_age
          AND age <= :max_age
          AND city = :city";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    'min_age' => $minAge,
    'max_age' => $maxAge,
    'city' => $city
]);

$users = $stmt->fetchAll();
```

---

## LIKE Queries with Wildcards

```php
<?php
$search = $_GET['search'];

// Add % wildcards in PHP, not SQL
$sql = "SELECT * FROM products WHERE name LIKE :search";
$stmt = $pdo->prepare($sql);
$stmt->execute(['search' => "%$search%"]);

$products = $stmt->fetchAll();
```

**Important:** Add `%` wildcards to the data, not in the SQL:

```php
//  Correct
$stmt->execute(['search' => "%$search%"]);

// L Wrong - defeats the purpose of prepared statements
$sql = "SELECT * FROM products WHERE name LIKE '%:search%'";
```

---

## Reusing Prepared Statements

If you're running the same query multiple times, prepare once and execute many times:

```php
<?php
$sql = "INSERT INTO logs (user_id, action) VALUES (:user_id, :action)";
$stmt = $pdo->prepare($sql);

// Execute multiple times with different data
$stmt->execute(['user_id' => 1, 'action' => 'login']);
$stmt->execute(['user_id' => 1, 'action' => 'view_profile']);
$stmt->execute(['user_id' => 1, 'action' => 'logout']);

// Much faster than preparing 3 times!
```

**Why faster?** MySQL compiles the SQL once and reuses it.

---

## IN Clause with Variable Number of Values

The `IN` clause is tricky with prepared statements:

### Wrong Approach

```php
<?php
// L Doesn't work
$ids = [1, 2, 3, 4, 5];
$sql = "SELECT * FROM users WHERE id IN (?)";
$stmt = $pdo->prepare($sql);
$stmt->execute([$ids]);  // Only looks for id = Array (doesn't work!)
```

### Correct Approach

```php
<?php
$ids = [1, 2, 3, 4, 5];

// Create placeholders: ?, ?, ?, ?, ?
$placeholders = implode(',', array_fill(0, count($ids), '?'));

$sql = "SELECT * FROM users WHERE id IN ($placeholders)";
$stmt = $pdo->prepare($sql);
$stmt->execute($ids);

$users = $stmt->fetchAll();
```

**How it works:**
- `array_fill(0, 5, '?')` creates `['?', '?', '?', '?', '?']`
- `implode(',', ...)` joins them: `"?, ?, ?, ?, ?"`
- SQL becomes: `SELECT * FROM users WHERE id IN (?, ?, ?, ?, ?)`
- Execute with `[1, 2, 3, 4, 5]`

### Named Version

```php
<?php
$ids = [1, 2, 3, 4, 5];

// Create :id0, :id1, :id2, :id3, :id4
$placeholders = [];
$params = [];

foreach ($ids as $index => $id) {
    $placeholder = ":id$index";
    $placeholders[] = $placeholder;
    $params[$placeholder] = $id;
}

$sql = "SELECT * FROM users WHERE id IN (" . implode(',', $placeholders) . ")";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
```

---

## Binding Values: Alternative Syntax

Instead of passing an array to `execute()`, you can bind values individually:

### bindValue()

```php
<?php
$sql = "SELECT * FROM users WHERE email = :email AND age > :age";
$stmt = $pdo->prepare($sql);

$stmt->bindValue(':email', $email, PDO::PARAM_STR);
$stmt->bindValue(':age', $age, PDO::PARAM_INT);

$stmt->execute();  // No array needed
$user = $stmt->fetch();
```

**PDO data types:**
- `PDO::PARAM_STR` - String (default)
- `PDO::PARAM_INT` - Integer
- `PDO::PARAM_BOOL` - Boolean
- `PDO::PARAM_NULL` - NULL

### bindParam() - Pass by Reference

```php
<?php
$sql = "INSERT INTO logs (user_id, action) VALUES (:user_id, :action)";
$stmt = $pdo->prepare($sql);

// Bind by reference (variables can change)
$stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
$stmt->bindParam(':action', $action, PDO::PARAM_STR);

// Execute multiple times with different values
$userId = 1;
$action = 'login';
$stmt->execute();

$userId = 2;
$action = 'logout';
$stmt->execute();
```

**Difference:**
- `bindValue()` - Copies the value immediately
- `bindParam()` - Binds the variable by reference (value captured at execute time)

**Most of the time, just use the execute() array approach - it's simpler!**

---

## What Can't Be a Placeholder?

Placeholders work for **values only**, not for:

### L Table Names

```php
// L Doesn't work
$table = $_GET['table'];
$stmt = $pdo->prepare("SELECT * FROM :table");
$stmt->execute(['table' => $table]);
```

**Solution:** Whitelist table names:

```php
<?php
$table = $_GET['table'];

$allowedTables = ['users', 'posts', 'comments'];

if (!in_array($table, $allowedTables)) {
    die("Invalid table");
}

// Safe to use directly (it's whitelisted)
$stmt = $pdo->query("SELECT * FROM $table");
```

### L Column Names

```php
// L Doesn't work
$column = $_GET['sort'];
$stmt = $pdo->prepare("SELECT * FROM users ORDER BY :column");
$stmt->execute(['column' => $column]);
```

**Solution:** Whitelist column names:

```php
<?php
$column = $_GET['sort'];

$allowedColumns = ['name', 'email', 'created_at'];

if (!in_array($column, $allowedColumns)) {
    $column = 'created_at';  // Default
}

$stmt = $pdo->query("SELECT * FROM users ORDER BY $column");
```

### L Keywords (ASC, DESC, AND, OR)

```php
// L Doesn't work
$order = $_GET['order'];  // 'ASC' or 'DESC'
$stmt = $pdo->prepare("SELECT * FROM users ORDER BY name :order");
$stmt->execute(['order' => $order]);
```

**Solution:** Validate and insert directly:

```php
<?php
$order = $_GET['order'];

if ($order !== 'ASC' && $order !== 'DESC') {
    $order = 'ASC';
}

$stmt = $pdo->query("SELECT * FROM users ORDER BY name $order");
```

**Rule:** If it's not a value (number, string, date), you can't use a placeholder. Whitelist instead!

---

## Common Pitfalls

### 1. Quotes Around Placeholders

```php
// L Wrong - Quotes make it a string literal, not a placeholder
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ':email'");

//  Correct - No quotes
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
```

PDO adds quotes automatically when needed!

### 2. Mixing Placeholder Styles

```php
// L Can't mix ? and :name in same query
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND email = :email");

//  Pick one style
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND email = ?");
// OR
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id AND email = :email");
```

### 3. Reusing Named Placeholders

```php
<?php
//  This works! Same placeholder used twice
$stmt = $pdo->prepare("
    SELECT * FROM posts
    WHERE user_id = :user_id
       OR reviewer_id = :user_id
");
$stmt->execute(['user_id' => 5]);

// Only need to pass the value once!
```

### 4. Wrong Parameter Count

```php
<?php
// L 3 placeholders but only 2 values
$stmt = $pdo->prepare("INSERT INTO users (name, email, age) VALUES (?, ?, ?)");
$stmt->execute(['Kieu', 'kieu@example.com']);  // ERROR!

//  Correct - 3 values for 3 placeholders
$stmt->execute(['Kieu', 'kieu@example.com', 28]);
```

---

## Real-World Example: Secure Login

```php
<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Get user by email (prepared statement!)
    $sql = "SELECT * FROM users WHERE email = :email";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    // Check if user exists and password matches
    if ($user && password_verify($password, $user['password'])) {
        // Login successful
        $_SESSION['user_id'] = $user['id'];
        header('Location: dashboard.php');
        exit;
    } else {
        $error = "Invalid email or password";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
</head>
<body>
    <h1>Login</h1>

    <?php if (isset($error)): ?>
        <p style="color: red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST">
        <input type="email" name="email" placeholder="Email" required>
        <br>
        <input type="password" name="password" placeholder="Password" required>
        <br>
        <button type="submit">Login</button>
    </form>
</body>
</html>
```

**Security features:**
- Prepared statement prevents SQL injection
- `password_verify()` safely checks hashed password
- Generic error message doesn't reveal if email exists
- `htmlspecialchars()` prevents XSS

---

## Real-World Example: Secure Registration

```php
<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Check if email already exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);

    if ($stmt->fetchColumn() > 0) {
        $error = "Email already registered";
    } else {
        // Hash password (never store plain text!)
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Insert new user
        $sql = "INSERT INTO users (name, email, password) VALUES (:name, :email, :password)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'name' => $name,
            'email' => $email,
            'password' => $hashedPassword
        ]);

        // Auto-login after registration
        $_SESSION['user_id'] = $pdo->lastInsertId();
        header('Location: dashboard.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
</head>
<body>
    <h1>Register</h1>

    <?php if (isset($error)): ?>
        <p style="color: red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST">
        <input type="text" name="name" placeholder="Name" required>
        <br>
        <input type="email" name="email" placeholder="Email" required>
        <br>
        <input type="password" name="password" placeholder="Password" required>
        <br>
        <button type="submit">Register</button>
    </form>
</body>
</html>
```

---

## Performance: Prepared vs Direct Queries

### Single Query - No Difference

```php
// Both are equally fast for one query
$stmt = $pdo->query("SELECT * FROM users");
$stmt = $pdo->prepare("SELECT * FROM users");
$stmt->execute();
```

### Multiple Queries - Prepared is Faster

```php
// Slow: Compiles SQL 100 times
for ($i = 0; $i < 100; $i++) {
    $pdo->exec("INSERT INTO logs (message) VALUES ('Log $i')");
}

// Fast: Compiles SQL once, reuses 100 times
$stmt = $pdo->prepare("INSERT INTO logs (message) VALUES (?)");
for ($i = 0; $i < 100; $i++) {
    $stmt->execute(["Log $i"]);
}
```

**Prepared statements are faster when executing the same query multiple times.**

---

## Practice Exercises

### Exercise 1: Secure Search

Create a search page that:
1. Takes a search term from a form
2. Searches products by name using LIKE
3. Uses prepared statements
4. Displays results

### Exercise 2: Dynamic Filters

Create a product filter with:
- Min/max price range
- Category selection
- Sort order (price ASC/DESC)
- All using prepared statements

<details>
<summary>Hint</summary>

```php
<?php
$sql = "SELECT * FROM products WHERE price >= :min AND price <= :max";

if (!empty($_GET['category'])) {
    $sql .= " AND category = :category";
}

$sql .= " ORDER BY price " . ($order === 'DESC' ? 'DESC' : 'ASC');

$stmt = $pdo->prepare($sql);
$params = [
    'min' => $minPrice,
    'max' => $maxPrice
];

if (!empty($_GET['category'])) {
    $params['category'] = $_GET['category'];
}

$stmt->execute($params);
```
</details>

### Exercise 3: Bulk Insert

Insert 100 test users using a prepared statement in a loop. Time how long it takes!

---

## Security Checklist

Before deploying any database code, verify:

- [ ] All user input uses prepared statements
- [ ] No string concatenation in SQL queries
- [ ] Table/column names are whitelisted (not from user input)
- [ ] Passwords are hashed with `password_hash()`
- [ ] Output is escaped with `htmlspecialchars()`
- [ ] Error messages don't reveal database structure
- [ ] Database credentials are in a separate config file (not in Git)

---

## Quick Reference

### Positional Placeholders
```php
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND age > ?");
$stmt->execute([$id, $age]);
```

### Named Placeholders
```php
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id AND age > :age");
$stmt->execute(['id' => $id, 'age' => $age]);
```

### Insert and Get ID
```php
$stmt = $pdo->prepare("INSERT INTO users (name) VALUES (?)");
$stmt->execute([$name]);
$newId = $pdo->lastInsertId();
```

### Check Affected Rows
```php
$stmt = $pdo->prepare("UPDATE users SET name = ? WHERE id = ?");
$stmt->execute([$name, $id]);
if ($stmt->rowCount() > 0) {
    echo "Updated!";
}
```

---

## What's Next?

You now understand how to secure your database queries!

**Next up:** Working with relationships between tables. How do you connect users to their posts? Posts to comments? Products to categories?

You'll learn about:
- Foreign keys
- One-to-many relationships
- Many-to-many relationships
- JOIN queries

---

## Key Takeaways

1. **SQL injection is the #1 database attack** - Never trust user input
2. **Always use prepared statements** - With ? or :name placeholders
3. **Never concatenate user input into SQL** - Even if you think it's safe
4. **Placeholders work for values only** - Not tables, columns, or keywords
5. **Named placeholders are more readable** - Use for 3+ parameters
6. **Whitelist table/column names** - Can't use placeholders for these
7. **Prepared statements are faster** - When executing same query multiple times
8. **Hash passwords** - Use `password_hash()` and `password_verify()`

---

**Next Lesson:** [06 - Relationships: Foreign Keys and Associations](./06-relationships.md)
