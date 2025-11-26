# Lesson 07 - SQL Injection: Advanced Prevention & Review

**Duration**: 45-60 minutes

---

## Introduction: Still the Most Dangerous

**SQL Injection** has been around for decades, yet it remains one of the most critical vulnerabilities:

- **OWASP Top 10**: Consistently in top 3
- **Impact**: Complete database compromise, data theft, data loss
- **Ease of exploitation**: Simple to execute, devastating results

**Real-world breaches**:
- **Heartland Payment Systems (2008)**: 134 million credit cards stolen via SQL injection
- **Sony PlayStation Network (2011)**: 77 million accounts compromised
- **TalkTalk (2015)**: £77 million in costs from SQL injection attack

We covered SQL injection basics in Module 06. This lesson reviews those concepts and adds advanced techniques.

---

## Quick Review: What is SQL Injection?

SQL injection occurs when attacker input is inserted into SQL queries, allowing them to:
- Read sensitive data
- Modify or delete data
- Execute admin operations
- Bypass authentication
- Execute operating system commands (in extreme cases)

### Classic Example

```php
// VULNERABLE CODE
$username = $_POST['username'];
$password = $_POST['password'];

$query = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
$result = $db->query($query);
```

**Attack**:
```
Username: admin' OR '1'='1
Password: anything

Resulting query:
SELECT * FROM users WHERE username = 'admin' OR '1'='1' AND password = 'anything'

This returns the admin user (1=1 is always true)!
```

---

## The Solution: Prepared Statements

**The only correct way** to prevent SQL injection is **prepared statements** (also called parameterized queries).

### With PDO (Recommended)

```php
<?php
// SECURE - Using prepared statements with PDO

$pdo = new PDO('mysql:host=localhost;dbname=myapp', 'user', 'pass');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Positional parameters (?)
$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND password = ?");
$stmt->execute([$username, $password]);
$user = $stmt->fetch();

// Named parameters (:name)
$stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username AND password = :password");
$stmt->execute([
    ':username' => $username,
    ':password' => $password
]);
$user = $stmt->fetch();
```

### With MySQLi

```php
<?php
// SECURE - Using prepared statements with MySQLi

$mysqli = new mysqli('localhost', 'user', 'pass', 'myapp');

$stmt = $mysqli->prepare("SELECT * FROM users WHERE username = ? AND password = ?");
$stmt->bind_param('ss', $username, $password); // 's' = string
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
```

### Why Prepared Statements Work

**How they work**:
1. **Prepare**: SQL structure sent to database server
2. **Bind**: Parameters sent separately
3. **Execute**: Database knows what's code and what's data

```
Step 1 (Prepare):
"SELECT * FROM users WHERE username = ? AND password = ?"
Database: "OK, I understand the query structure"

Step 2 (Execute with parameters):
Parameters: ['admin', 'password123']
Database: "These are DATA values, not SQL code"

Result: Even if parameter contains SQL, it's treated as data!
```

**Example with malicious input**:
```php
$username = "admin' OR '1'='1";

// With prepared statement:
$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$username]);

// Database searches for a user literally named: admin' OR '1'='1
// No SQL injection possible!
```

---

## Common SQL Injection Mistakes

### Mistake 1: String Concatenation

```php
// WRONG - Building SQL with concatenation
$query = "SELECT * FROM users WHERE id = " . $_GET['id'];

// WRONG - Even with quotes
$query = "SELECT * FROM users WHERE username = '" . $_POST['username'] . "'";

// WRONG - Even with escape functions
$username = mysqli_real_escape_string($conn, $_POST['username']);
$query = "SELECT * FROM users WHERE username = '$username'";
// mysqli_real_escape_string is NOT sufficient! Use prepared statements!
```

### Mistake 2: Partial Use of Prepared Statements

```php
// WRONG - Mixing prepared statements with concatenation
$table = $_GET['table']; // User-controlled!
$stmt = $pdo->prepare("SELECT * FROM $table WHERE id = ?");
$stmt->execute([$id]);
// Vulnerable! Table name can't be parameterized!
```

**Fix**: Whitelist table names:
```php
// RIGHT - Whitelist allowed tables
$allowedTables = ['users', 'products', 'orders'];
$table = $_GET['table'];

if (!in_array($table, $allowedTables, true)) {
    die('Invalid table');
}

// Now safe to use
$stmt = $pdo->prepare("SELECT * FROM $table WHERE id = ?");
$stmt->execute([$id]);
```

### Mistake 3: Dynamic Column Names

```php
// WRONG - Column name from user input
$column = $_GET['sort']; // User wants to sort by column
$stmt = $pdo->prepare("SELECT * FROM users ORDER BY $column");
$stmt->execute();
// Vulnerable! Column names can't be parameterized!
```

**Fix**: Whitelist column names:
```php
// RIGHT - Whitelist allowed columns
$allowedColumns = ['username', 'email', 'created_at'];
$column = $_GET['sort'];

if (!in_array($column, $allowedColumns, true)) {
    $column = 'created_at'; // Safe default
}

// Now safe to use
$stmt = $pdo->prepare("SELECT * FROM users ORDER BY $column");
$stmt->execute();
```

### Mistake 4: LIKE Queries

```php
// WRONG - Vulnerable to SQL injection in LIKE
$search = $_GET['q'];
$query = "SELECT * FROM products WHERE name LIKE '%$search%'";
```

**Fix**: Use prepared statements with LIKE:
```php
// RIGHT - Prepared statement with LIKE
$search = $_GET['q'];
$searchParam = '%' . $search . '%';

$stmt = $pdo->prepare("SELECT * FROM products WHERE name LIKE ?");
$stmt->execute([$searchParam]);

// Or use CONCAT
$stmt = $pdo->prepare("SELECT * FROM products WHERE name LIKE CONCAT('%', ?, '%')");
$stmt->execute([$search]);
```

**But also escape LIKE wildcards**:
```php
function escapeLikeWildcards($string) {
    // Escape % and _
    return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $string);
}

$search = escapeLikeWildcards($_GET['q']);
$searchParam = '%' . $search . '%';

$stmt = $pdo->prepare("SELECT * FROM products WHERE name LIKE ? ESCAPE '\\'");
$stmt->execute([$searchParam]);
```

### Mistake 5: IN Clauses

```php
// WRONG - Building IN clause with array
$ids = $_POST['ids']; // Array: [1, 2, 3]
$inClause = implode(',', $ids);
$query = "SELECT * FROM users WHERE id IN ($inClause)";
// Vulnerable if $ids is manipulated!
```

**Fix**: Build placeholders dynamically:
```php
// RIGHT - Dynamic placeholders for IN clause
$ids = $_POST['ids'];

// Validate that all values are integers
$ids = array_filter($ids, 'is_numeric');
$ids = array_map('intval', $ids);

if (empty($ids)) {
    die('No valid IDs');
}

// Create placeholders: ?, ?, ?
$placeholders = implode(',', array_fill(0, count($ids), '?'));

$stmt = $pdo->prepare("SELECT * FROM users WHERE id IN ($placeholders)");
$stmt->execute($ids);
```

---

## Advanced SQL Injection Scenarios

### 1. Second-Order SQL Injection

**What is it**: Malicious payload stored in database, then used in a query later without sanitization.

```php
// Step 1: Attacker registers with malicious username
$username = "admin' OR '1'='1";

// This is safely stored (using prepared statement)
$stmt = $pdo->prepare("INSERT INTO users (username) VALUES (?)");
$stmt->execute([$username]);
// Database now contains: admin' OR '1'='1

// Step 2: Later, developer uses this data unsafely
$stmt = $pdo->query("SELECT * FROM users WHERE id = 1");
$user = $stmt->fetch();

// WRONG - Using data from database without preparation
$query = "SELECT * FROM logs WHERE username = '{$user['username']}'";
$result = $pdo->query($query);
// SQL INJECTION! Even though data came from database!
```

**Fix**: Always use prepared statements, even with database data:
```php
// RIGHT - Prepared statement even for database data
$stmt = $pdo->prepare("SELECT * FROM logs WHERE username = ?");
$stmt->execute([$user['username']]);
```

**Key lesson**: Never trust ANY data, even from your own database. Always use prepared statements.

### 2. Blind SQL Injection

**What is it**: Attacker can't see query results but can infer information from application behavior.

```php
// Vulnerable code
$id = $_GET['id'];
$stmt = $pdo->query("SELECT * FROM users WHERE id = $id");
$user = $stmt->fetch();

if ($user) {
    echo "User found";
} else {
    echo "User not found";
}
```

**Attack**:
```
# Test if admin user exists
/page.php?id=1 AND username='admin'
Response: "User found" → Admin exists

# Extract password character by character
/page.php?id=1 AND SUBSTRING(password,1,1)='a'
Response: "User not found"

/page.php?id=1 AND SUBSTRING(password,1,1)='$'
Response: "User found" → First char is $
```

**Prevention**: Use prepared statements (prevents the injection) + don't leak information in error messages.

### 3. Time-Based Blind SQL Injection

```php
// Even without visible differences, attacker can use timing
?id=1 AND SLEEP(5)

// If page takes 5 seconds → SQL injection successful
```

**Prevention**: Prepared statements + reasonable query timeout limits.

### 4. Union-Based SQL Injection

```php
// Vulnerable
$id = $_GET['id'];
$query = "SELECT name, email FROM users WHERE id = $id";
```

**Attack**:
```
?id=1 UNION SELECT credit_card, ssn FROM sensitive_data

// Attacker adds extra SELECT to retrieve other data
```

**Prevention**: Prepared statements prevent UNION injection.

---

## Building a Safe Database Layer

Create a database wrapper that enforces prepared statements:

```php
<?php
// Database.php

class Database {

    private PDO $pdo;

    public function __construct(string $dsn, string $user, string $pass) {
        $this->pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false, // Use real prepared statements
        ]);
    }

    /**
     * Execute query with parameters
     */
    public function query(string $sql, array $params = []): PDOStatement {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Fetch all rows
     */
    public function fetchAll(string $sql, array $params = []): array {
        return $this->query($sql, $params)->fetchAll();
    }

    /**
     * Fetch single row
     */
    public function fetch(string $sql, array $params = []): ?array {
        $result = $this->query($sql, $params)->fetch();
        return $result ?: null;
    }

    /**
     * Fetch single value
     */
    public function fetchColumn(string $sql, array $params = []) {
        return $this->query($sql, $params)->fetchColumn();
    }

    /**
     * Insert and return last inserted ID
     */
    public function insert(string $sql, array $params = []): int {
        $this->query($sql, $params);
        return (int) $this->pdo->lastInsertId();
    }

    /**
     * Get affected rows from last operation
     */
    public function affectedRows(string $sql, array $params = []): int {
        return $this->query($sql, $params)->rowCount();
    }

    /**
     * Begin transaction
     */
    public function beginTransaction(): void {
        $this->pdo->beginTransaction();
    }

    /**
     * Commit transaction
     */
    public function commit(): void {
        $this->pdo->commit();
    }

    /**
     * Rollback transaction
     */
    public function rollback(): void {
        $this->pdo->rollBack();
    }

    /**
     * Validate and sanitize table name (whitelist only)
     */
    public function validateTable(string $table, array $allowed): string {
        if (!in_array($table, $allowed, true)) {
            throw new InvalidArgumentException('Invalid table name');
        }
        return $table;
    }

    /**
     * Validate and sanitize column name (whitelist only)
     */
    public function validateColumn(string $column, array $allowed): string {
        if (!in_array($column, $allowed, true)) {
            throw new InvalidArgumentException('Invalid column name');
        }
        return $column;
    }

    /**
     * Build safe IN clause
     */
    public function buildInClause(array $values): array {
        if (empty($values)) {
            throw new InvalidArgumentException('Empty array for IN clause');
        }

        $placeholders = implode(',', array_fill(0, count($values), '?'));

        return [$placeholders, $values];
    }
}
```

### Using the Database Class

```php
<?php
require_once 'Database.php';

$db = new Database('mysql:host=localhost;dbname=myapp', 'user', 'pass');

// Simple select
$users = $db->fetchAll("SELECT * FROM users WHERE status = ?", ['active']);

// Single row
$user = $db->fetch("SELECT * FROM users WHERE id = ?", [$userId]);

// Insert
$newId = $db->insert(
    "INSERT INTO users (username, email, password) VALUES (?, ?, ?)",
    [$username, $email, $hash]
);

// Update
$affected = $db->affectedRows(
    "UPDATE users SET email = ? WHERE id = ?",
    [$newEmail, $userId]
);

// Safe table name
$allowedTables = ['users', 'products', 'orders'];
$table = $db->validateTable($_GET['table'], $allowedTables);
$results = $db->fetchAll("SELECT * FROM $table WHERE status = ?", ['active']);

// Safe IN clause
$ids = [1, 2, 3, 4, 5];
[$placeholders, $values] = $db->buildInClause($ids);
$users = $db->fetchAll("SELECT * FROM users WHERE id IN ($placeholders)", $values);

// Transaction
try {
    $db->beginTransaction();

    $db->query("UPDATE accounts SET balance = balance - ? WHERE id = ?", [100, $fromAccount]);
    $db->query("UPDATE accounts SET balance = balance + ? WHERE id = ?", [100, $toAccount]);

    $db->commit();
} catch (Exception $e) {
    $db->rollback();
    throw $e;
}
```

---

## SQL Injection Testing

### Manual Testing

Test every input with these payloads:

```sql
-- Basic tests
'
"
' OR '1'='1
" OR "1"="1
admin' --
admin' #
admin'/*

-- Union tests
' UNION SELECT NULL--
' UNION SELECT NULL, NULL--
' UNION SELECT NULL, NULL, NULL--

-- Time-based tests
' AND SLEEP(5)--
' AND BENCHMARK(1000000,MD5('A'))--

-- Boolean tests
' AND 1=1--
' AND 1=2--

-- Stacked queries (MySQL doesn't allow, but test anyway)
'; DROP TABLE users--
```

### Automated Testing Tools

- **SQLMap**: Most popular SQL injection tool
```bash
sqlmap -u "http://example.com/page.php?id=1" --batch
```

- **Burp Suite**: Web vulnerability scanner
- **OWASP ZAP**: Free security testing tool

### Code Review Checklist

Search your codebase for these patterns:

```bash
# Search for potential SQL injection
grep -r "\$_GET\|POST\|_REQUEST" . | grep -i "query\|execute\|sql"

# Look for string concatenation in queries
grep -r "SELECT.*\." . | grep -v "prepare"

# Find queries without prepare()
grep -r "->query(" . | grep -v "prepare"
```

---

## SQL Injection Prevention Checklist

- [ ] **Always use prepared statements** - No exceptions
- [ ] **Never concatenate SQL** - Not even with escaped values
- [ ] **Whitelist table/column names** - Can't be parameterized
- [ ] **Validate data types** - Ensure integers are integers, etc.
- [ ] **Escape LIKE wildcards** - When using LIKE with user input
- [ ] **Validate IN clause values** - Ensure all are expected type
- [ ] **Use transactions** - For multi-step operations
- [ ] **Disable detailed errors** - In production (don't leak schema)
- [ ] **Principle of least privilege** - Database user has minimal permissions
- [ ] **Regular security audits** - Automated and manual testing

---

## Database Security Best Practices

### 1. Principle of Least Privilege

```sql
-- Create limited user for web app
CREATE USER 'webapp'@'localhost' IDENTIFIED BY 'strong_password';

-- Grant only necessary permissions
GRANT SELECT, INSERT, UPDATE ON myapp.users TO 'webapp'@'localhost';
GRANT SELECT, INSERT, UPDATE ON myapp.products TO 'webapp'@'localhost';

-- DO NOT grant:
-- DROP, CREATE, ALTER, DELETE (unless specifically needed)
-- FILE (can read/write files)
-- SUPER (admin privileges)
-- ALL PRIVILEGES (gives everything)
```

### 2. Separate Users for Different Operations

```sql
-- Read-only user for reports
CREATE USER 'reports'@'localhost' IDENTIFIED BY 'password';
GRANT SELECT ON myapp.* TO 'reports'@'localhost';

-- Admin user for migrations (not used by web app)
CREATE USER 'admin'@'localhost' IDENTIFIED BY 'password';
GRANT ALL PRIVILEGES ON myapp.* TO 'admin'@'localhost';
```

### 3. Disable Dangerous Features

```ini
# my.cnf / my.ini
[mysqld]
# Disable loading files from filesystem
local_infile=0

# Disable symbolic links
symbolic-links=0
```

### 4. Error Handling in Production

```php
<?php
// config.php

if (getenv('APP_ENV') === 'production') {
    // Don't show SQL errors to users
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', '/var/log/php_errors.log');

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    // Log errors internally but show generic message to users
} else {
    // Development: show errors
    ini_set('display_errors', 1);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
```

---

## Key Takeaways

1. **Prepared statements are the only solution** - Use them always
2. **Never concatenate user input into SQL** - Not even with escaping
3. **Whitelist table and column names** - Can't be parameterized
4. **Validate all input** - Type, range, format
5. **Watch for second-order injection** - Don't trust database data
6. **Escape LIKE wildcards** - % and _ are special characters
7. **Use database wrapper** - Enforce prepared statements everywhere
8. **Least privilege** - Database user has minimal permissions
9. **Hide errors in production** - Don't leak database schema
10. **Test regularly** - Manual and automated SQL injection testing

---

## What's Next?

SQL injection covered! Next lesson: **File Upload Security** - one of the most dangerous features if not implemented correctly.

You'll learn:
- File type validation (MIME types, extensions, magic bytes)
- Preventing PHP file uploads
- Secure file storage
- Image validation
- Handling malicious files

File uploads combine multiple vulnerabilities - we need to get this right!
