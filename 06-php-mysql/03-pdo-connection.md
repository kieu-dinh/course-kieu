# Lesson 03 - Connecting PHP to MySQL with PDO

## The Bridge Between PHP and MySQL

You know PHP. You know SQL. Now let's connect them!

```
PHP Code  <----PDO----> MySQL Database
```

**PDO** (PHP Data Objects) is PHP's modern way to communicate with databases.

---

## Why PDO?

PHP has three ways to talk to databases:

### 1. mysql_ (Old, Deprecated)
```php
mysql_connect();  // L Removed in PHP 7+
```
**Don't use this.** It's ancient and insecure.

### 2. mysqli (MySQL Improved)
```php
$mysqli = new mysqli('localhost', 'root', '', 'mydb');
```
**Works, but only for MySQL.** If you switch to PostgreSQL later, you rewrite everything.

### 3. PDO (PHP Data Objects)
```php
$pdo = new PDO('mysql:host=localhost;dbname=mydb', 'root', '');
```
** This is what we'll use.**

**Why PDO?**
- Works with MySQL, PostgreSQL, SQLite, and more
- Modern, object-oriented approach
- Better security features (prepared statements)
- Consistent API across different databases
- Better error handling with exceptions
- Industry standard

---

## Connection Basics

### The Connection String (DSN)

**DSN** = Data Source Name. It tells PDO what database to connect to.

```php
<?php
$dsn = 'mysql:host=localhost;dbname=my_database;charset=utf8mb4';
$username = 'root';
$password = '';

$pdo = new PDO($dsn, $username, $password);
```

**Breaking it down:**

- `mysql:` - The database driver (MySQL)
- `host=localhost` - Database server address (localhost = your computer)
- `dbname=my_database` - Which database to use
- `charset=utf8mb4` - Character encoding (supports emojis and all languages!)
- `$username` - Database user (usually 'root' in development)
- `$password` - Database password (often empty with Laravel Herd)

---

## Your First Connection

Create a file `db_connect.php`:

```php
<?php
// Database credentials
$host = '127.0.0.1';      // or 'localhost'
$dbname = 'my_first_db';  // Your database name
$username = 'root';        // Default user with Herd
$password = '';            // Empty password with Herd

// Build DSN
$dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";

// Create PDO connection
$pdo = new PDO($dsn, $username, $password);

echo "Connected successfully!";
```

**Run it:**
```bash
php db_connect.php
```

If you see "Connected successfully!" - you're in! <‰

---

## Error Handling: Try-Catch

**Problem:** What if the database doesn't exist? Or wrong password? Your script crashes.

**Solution:** Wrap connections in try-catch blocks:

```php
<?php
$host = '127.0.0.1';
$dbname = 'my_first_db';
$username = 'root';
$password = '';

$dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $username, $password);
    echo "Connected successfully!";
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    die();
}
```

**If connection fails, you'll see:**
```
Connection failed: SQLSTATE[HY000] [1049] Unknown database 'my_first_db'
```

Much better than a cryptic error!

---

## PDO Options: Making Connections Better

Configure how PDO behaves:

```php
<?php
$dsn = "mysql:host=127.0.0.1;dbname=my_first_db;charset=utf8mb4";
$username = 'root';
$password = '';

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $username, $password, $options);
    echo "Connected successfully!";
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
```

**Let's understand each option:**

### 1. ATTR_ERRMODE => ERRMODE_EXCEPTION

**What it does:** Throws exceptions on database errors instead of silent failures.

```php
// Without this option:
$stmt = $pdo->query("SELECT * FROM non_existent_table");
// Fails silently, returns false

// With this option:
$stmt = $pdo->query("SELECT * FROM non_existent_table");
// Throws PDOException, easy to catch and debug!
```

**Always use this!** It makes debugging so much easier.

### 2. ATTR_DEFAULT_FETCH_MODE => FETCH_ASSOC

**What it does:** Returns rows as associative arrays.

```php
// Result row with FETCH_ASSOC:
[
    'id' => 1,
    'name' => 'Kieu',
    'email' => 'kieu@example.com'
]

// Without it (default FETCH_BOTH):
[
    'id' => 1,
    0 => 1,              //  Duplicates!
    'name' => 'Kieu',
    1 => 'Kieu',
    'email' => 'kieu@example.com',
    2 => 'kieu@example.com'
]
```

**FETCH_ASSOC is cleaner!** You access data with `$row['name']`, not `$row[0]`.

### 3. ATTR_EMULATE_PREPARES => false

**What it does:** Uses real prepared statements (more secure).

```php
// false = MySQL handles preparation (recommended)
// true = PHP emulates it (less secure)
```

**Always set to false** for better security.

---

## Creating a Reusable Database Connection

**Don't repeat connection code everywhere!** Create a reusable file:

### db.php (Database Connection File)

```php
<?php
// db.php - Reusable database connection

$host = '127.0.0.1';
$dbname = 'my_first_db';
$username = 'root';
$password = '';

$dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
```

**Now use it anywhere:**

```php
<?php
// Any other file
require_once 'db.php';

// $pdo is now available!
$stmt = $pdo->query("SELECT * FROM users");
```

**One connection file, use it everywhere!**

---

## Running Your First Query

Now that you're connected, let's query the database:

```php
<?php
require_once 'db.php';

// Simple query
$stmt = $pdo->query("SELECT * FROM users");

// Fetch all results
$users = $stmt->fetchAll();

// Display
foreach ($users as $user) {
    echo $user['name'] . " - " . $user['email'] . "<br>";
}
```

**Output:**
```
Kieu - kieu@example.com
John - john@example.com
Sarah - sarah@example.com
```

---

## Fetch Modes: How to Get Data

PDO has several ways to fetch results:

### 1. fetchAll() - Get All Rows

```php
<?php
$stmt = $pdo->query("SELECT * FROM users");
$users = $stmt->fetchAll();

// Returns array of arrays:
// [
//     ['id' => 1, 'name' => 'Kieu', ...],
//     ['id' => 2, 'name' => 'John', ...],
//     ['id' => 3, 'name' => 'Sarah', ...]
// ]

foreach ($users as $user) {
    echo $user['name'];
}
```

**Use when:** You need all results at once.

### 2. fetch() - Get One Row

```php
<?php
$stmt = $pdo->query("SELECT * FROM users WHERE id = 1");
$user = $stmt->fetch();

// Returns single array:
// ['id' => 1, 'name' => 'Kieu', 'email' => 'kieu@example.com']

echo $user['name'];  // Kieu
```

**Use when:** You expect only one result (or want just the first row).

### 3. fetch() in a Loop - Memory Efficient

```php
<?php
$stmt = $pdo->query("SELECT * FROM users");

// Fetch one row at a time
while ($user = $stmt->fetch()) {
    echo $user['name'] . "<br>";
}
```

**Use when:** You have lots of results and want to save memory.

### 4. fetchColumn() - Get Single Value

```php
<?php
// Get just the count
$stmt = $pdo->query("SELECT COUNT(*) FROM users");
$count = $stmt->fetchColumn();

echo "Total users: $count";  // Total users: 3
```

**Use when:** You need just one value (count, sum, single column).

---

## Different Fetch Modes

We set `FETCH_ASSOC` as default, but there are others:

### FETCH_ASSOC (Default - Recommended)

```php
$user = $stmt->fetch(PDO::FETCH_ASSOC);
// ['id' => 1, 'name' => 'Kieu']
echo $user['name'];
```

### FETCH_OBJ (As Object)

```php
$user = $stmt->fetch(PDO::FETCH_OBJ);
// stdClass Object
echo $user->name;  // Note: -> not []
```

### FETCH_NUM (Numeric Array)

```php
$user = $stmt->fetch(PDO::FETCH_NUM);
// [1, 'Kieu', 'kieu@example.com']
echo $user[1];  // Kieu
```

### FETCH_CLASS (Into Custom Class)

```php
class User {
    public int $id;
    public string $name;
    public string $email;
}

$stmt = $pdo->query("SELECT * FROM users");
$users = $stmt->fetchAll(PDO::FETCH_CLASS, 'User');

// Each $user is a User object!
foreach ($users as $user) {
    echo $user->name;  // Using object property
}
```

**Stick with FETCH_ASSOC** for now. It's the most flexible.

---

## Counting Results

Check how many rows a query returned:

```php
<?php
$stmt = $pdo->query("SELECT * FROM users WHERE age > 25");
$users = $stmt->fetchAll();

echo "Found " . count($users) . " users";
```

Or use `rowCount()` (but be careful - doesn't work reliably with SELECT on all systems):

```php
<?php
$stmt = $pdo->query("SELECT * FROM users WHERE age > 25");
echo "Found " . $stmt->rowCount() . " rows";
```

**For COUNT queries, use SQL:**

```php
<?php
$stmt = $pdo->query("SELECT COUNT(*) FROM users");
$count = $stmt->fetchColumn();
echo "Total users: $count";
```

---

## Checking If Results Exist

```php
<?php
$stmt = $pdo->query("SELECT * FROM users WHERE id = 999");
$user = $stmt->fetch();

if ($user) {
    echo "User found: " . $user['name'];
} else {
    echo "User not found";
}
```

Or with fetchAll():

```php
<?php
$stmt = $pdo->query("SELECT * FROM users WHERE age > 100");
$users = $stmt->fetchAll();

if (empty($users)) {
    echo "No users found";
} else {
    echo "Found " . count($users) . " users";
}
```

---

## Closing Connections

**Good news:** You don't have to manually close connections!

PHP automatically closes PDO connections when your script ends:

```php
<?php
$pdo = new PDO(...);
// Do stuff
// Connection closes automatically at end of script
```

**Want to close early?**

```php
<?php
$pdo = new PDO(...);
// Do stuff
$pdo = null;  // Closes connection immediately
```

**In practice:** Just let PHP handle it. Only close manually if you're done with the database but have more processing to do.

---

## Security Note: Never Echo Queries with User Input!

**DANGER:**

```php
<?php
// L NEVER DO THIS!
$email = $_POST['email'];
$sql = "SELECT * FROM users WHERE email = '$email'";
$stmt = $pdo->query($sql);
```

**Why?** If someone enters:
```
' OR '1'='1
```

Your query becomes:
```sql
SELECT * FROM users WHERE email = '' OR '1'='1'
```

**This returns ALL users!** They just hacked your database.

**This is called SQL Injection** - one of the most common attacks.

**The fix:** Use prepared statements (next lesson!).

---

## Configuration Best Practices

### 1. Use Environment Variables (Production)

Don't hardcode credentials in production:

```php
<?php
// .env file (never commit this!)
DB_HOST=127.0.0.1
DB_NAME=my_app
DB_USER=root
DB_PASS=secret123
```

```php
<?php
// Load from environment
$host = getenv('DB_HOST');
$dbname = getenv('DB_NAME');
$username = getenv('DB_USER');
$password = getenv('DB_PASS');

$dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
$pdo = new PDO($dsn, $username, $password, $options);
```

### 2. Separate Config File

```php
<?php
// config.php
return [
    'database' => [
        'host' => '127.0.0.1',
        'name' => 'my_db',
        'user' => 'root',
        'pass' => '',
    ]
];
```

```php
<?php
// db.php
$config = require 'config.php';
$db = $config['database'];

$dsn = "mysql:host={$db['host']};dbname={$db['name']};charset=utf8mb4";
$pdo = new PDO($dsn, $db['user'], $db['pass'], $options);
```

---

## Common Connection Errors

### Error 1: "Unknown database"

```
SQLSTATE[HY000] [1049] Unknown database 'my_first_db'
```

**Fix:** Create the database first in TablePlus or phpMyAdmin!

```sql
CREATE DATABASE my_first_db;
```

### Error 2: "Access denied"

```
SQLSTATE[HY000] [1045] Access denied for user 'root'@'localhost'
```

**Fix:** Wrong username or password. With Laravel Herd:
- Username: `root`
- Password: (empty string)

### Error 3: "Connection refused"

```
SQLSTATE[HY000] [2002] Connection refused
```

**Fix:** MySQL isn't running. Start it with Herd or check your system.

### Error 4: "Port 3306 already in use"

**Fix:** Another MySQL instance is running. Stop it or use a different port.

---

## Testing Your Connection

Create a simple test file:

```php
<?php
// test_connection.php

$host = '127.0.0.1';
$dbname = 'my_first_db';
$username = 'root';
$password = '';

$dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $username, $password, $options);
    echo " Connected successfully!\n";

    // Test a simple query
    $stmt = $pdo->query("SELECT DATABASE() as current_db");
    $result = $stmt->fetch();
    echo "=Ê Current database: " . $result['current_db'] . "\n";

    // Test getting tables
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "=Á Tables in database: " . implode(', ', $tables) . "\n";

} catch (PDOException $e) {
    echo "L Connection failed: " . $e->getMessage() . "\n";
}
```

**Run it:**
```bash
php test_connection.php
```

**Expected output:**
```
 Connected successfully!
=Ê Current database: my_first_db
=Á Tables in database: users, posts, comments
```

---

## Practice Exercises

### Exercise 1: Basic Connection

1. Create a database called `practice_db` in TablePlus/phpMyAdmin
2. Create a `connection.php` file that connects to it
3. Add proper error handling with try-catch
4. Echo "Connected!" if successful

### Exercise 2: Test Queries

1. Create a `users` table with id, name, email
2. Insert 3 users using TablePlus/phpMyAdmin
3. Create a PHP file that:
   - Connects to the database
   - Fetches all users
   - Displays their names and emails in HTML

<details>
<summary>Solution Hint</summary>

```php
<?php
require_once 'connection.php';

$stmt = $pdo->query("SELECT * FROM users");
$users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Users</title>
</head>
<body>
    <h1>All Users</h1>
    <ul>
        <?php foreach ($users as $user): ?>
            <li><?= $user['name'] ?> - <?= $user['email'] ?></li>
        <?php endforeach; ?>
    </ul>
</body>
</html>
```
</details>

### Exercise 3: Different Fetch Modes

1. Query all users with `fetchAll()` and loop through them
2. Query one user by ID with `fetch()`
3. Get total user count with `fetchColumn()`
4. Try fetching users as objects with `FETCH_OBJ`

### Exercise 4: Error Handling

1. Try connecting to a non-existent database
2. Catch the error and display a friendly message
3. Try querying a non-existent table
4. Handle that error gracefully too

---

## Quick Reference

### Basic Connection

```php
<?php
$dsn = "mysql:host=127.0.0.1;dbname=mydb;charset=utf8mb4";
$username = 'root';
$password = '';

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
```

### Fetch Methods

```php
$users = $stmt->fetchAll();         // All rows
$user = $stmt->fetch();              // One row
$count = $stmt->fetchColumn();       // Single value
while ($row = $stmt->fetch()) {...}  // Loop through rows
```

---

## What's Next?

You can now connect PHP to MySQL and run basic queries!

**But there's a critical problem:** Using `query()` with user input is dangerous (SQL injection).

**In the next lesson**, you'll learn about **prepared statements** - the secure way to handle user input in SQL queries.

---

## Key Takeaways

1. **PDO is PHP's modern database interface** - Works with many databases
2. **Always use try-catch** - Handle connection errors gracefully
3. **Set PDO options** - ERRMODE_EXCEPTION, FETCH_ASSOC, EMULATE_PREPARES false
4. **Create a reusable db.php file** - Don't repeat connection code
5. **fetchAll() vs fetch()** - All rows vs one row
6. **fetchColumn()** - For single values like COUNT
7. **NEVER use query() with user input** - SQL injection risk!
8. **Connections close automatically** - No need to manually close

---

**Next Lesson:** [04 - CRUD Operations: Create, Read, Update, Delete](./04-crud-operations.md)
