# Lesson 04 - CRUD Operations with PDO

## What is CRUD?

**CRUD** = Create, Read, Update, Delete

These are the four fundamental operations you'll perform on database data:

| Operation | SQL | What it does | Example |
|-----------|-----|--------------|---------|
| **Create** | INSERT | Add new records | Register a new user |
| **Read** | SELECT | Get existing records | Show all products |
| **Update** | UPDATE | Modify existing records | Change user email |
| **Delete** | DELETE | Remove records | Delete a comment |

**Every app uses CRUD.** Blog posts, user accounts, products, comments - they all need these operations.

---

## Setup: Our Example Table

Let's work with a `tasks` table (simple todo app):

```sql
CREATE TABLE tasks (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    is_completed BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

Create this table in your database before continuing!

---

## CREATE: Inserting Data

### Basic Insert with exec()

The simplest way (but NOT recommended for user input):

```php
<?php
require_once 'db.php';

//   Only safe with hardcoded data!
$sql = "INSERT INTO tasks (title, description)
        VALUES ('Learn PDO', 'Complete all PDO exercises')";

$pdo->exec($sql);

echo "Task created!";
```

**Problem:** What if the title comes from user input? **SQL injection risk!**

### Safer: Prepared Statements (Preview)

```php
<?php
require_once 'db.php';

$title = "Learn PDO";
$description = "Complete all PDO exercises";

$sql = "INSERT INTO tasks (title, description) VALUES (?, ?)";
$stmt = $pdo->prepare($sql);
$stmt->execute([$title, $description]);

echo "Task created with ID: " . $pdo->lastInsertId();
```

**This is the right way!** We'll dive deeper into prepared statements in the next lesson.

### Getting the New Record's ID

When you insert a row with an auto-increment ID, you often need that ID:

```php
<?php
$sql = "INSERT INTO tasks (title) VALUES (?)";
$stmt = $pdo->prepare($sql);
$stmt->execute(['My new task']);

$newId = $pdo->lastInsertId();
echo "Created task with ID: $newId";  // e.g., 15
```

**Use case:** After registering a user, redirect to their profile page using their new ID.

---

## READ: Selecting Data

### Get All Records

```php
<?php
require_once 'db.php';

$stmt = $pdo->query("SELECT * FROM tasks");
$tasks = $stmt->fetchAll();

foreach ($tasks as $task) {
    echo "{$task['title']} - Completed: " . ($task['is_completed'] ? 'Yes' : 'No');
    echo "<br>";
}
```

### Get One Record by ID

```php
<?php
$id = 5;

$sql = "SELECT * FROM tasks WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id]);

$task = $stmt->fetch();

if ($task) {
    echo "Title: " . $task['title'];
    echo "<br>Description: " . $task['description'];
} else {
    echo "Task not found";
}
```

### Get Records with Conditions

```php
<?php
// Get all completed tasks
$sql = "SELECT * FROM tasks WHERE is_completed = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([1]);  // 1 = true
$completedTasks = $stmt->fetchAll();

foreach ($completedTasks as $task) {
    echo " " . $task['title'] . "<br>";
}
```

### Search by Pattern

```php
<?php
$searchTerm = "PDO";

$sql = "SELECT * FROM tasks WHERE title LIKE ?";
$stmt = $pdo->prepare($sql);
$stmt->execute(["%$searchTerm%"]);  // % = wildcard

$results = $stmt->fetchAll();

echo "Found " . count($results) . " tasks matching '$searchTerm'";
```

### Ordering Results

```php
<?php
// Get all tasks, newest first
$stmt = $pdo->query("SELECT * FROM tasks ORDER BY created_at DESC");
$tasks = $stmt->fetchAll();

// Completed tasks first, then by title
$stmt = $pdo->query("
    SELECT * FROM tasks
    ORDER BY is_completed DESC, title ASC
");
$tasks = $stmt->fetchAll();
```

### Limiting Results (Pagination)

```php
<?php
// Get 10 tasks per page
$page = 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

$sql = "SELECT * FROM tasks LIMIT ? OFFSET ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$perPage, $offset]);
$tasks = $stmt->fetchAll();

// Page 1: tasks 1-10
// Page 2: tasks 11-20
// Page 3: tasks 21-30
```

---

## UPDATE: Modifying Data

### Update One Record

```php
<?php
$taskId = 5;
$newTitle = "Learn PDO Deeply";

$sql = "UPDATE tasks SET title = ? WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$newTitle, $taskId]);

echo "Task updated!";
```

### Update Multiple Fields

```php
<?php
$taskId = 5;
$newTitle = "Master PDO";
$newDescription = "Become a PDO expert";

$sql = "UPDATE tasks
        SET title = ?, description = ?
        WHERE id = ?";

$stmt = $pdo->prepare($sql);
$stmt->execute([$newTitle, $newDescription, $taskId]);
```

### Mark Task as Completed

```php
<?php
$taskId = 5;

$sql = "UPDATE tasks SET is_completed = ? WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([1, $taskId]);  // 1 = true

echo "Task marked as complete!";
```

### Toggle Completed Status

```php
<?php
$taskId = 5;

// Flip the boolean: 0 becomes 1, 1 becomes 0
$sql = "UPDATE tasks SET is_completed = NOT is_completed WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$taskId]);
```

### Check If Update Affected Rows

```php
<?php
$taskId = 999;  // Doesn't exist

$sql = "UPDATE tasks SET title = ? WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute(['New title', $taskId]);

$rowsAffected = $stmt->rowCount();

if ($rowsAffected > 0) {
    echo "Task updated!";
} else {
    echo "No task found with that ID";
}
```

---

## DELETE: Removing Data

### Delete One Record

```php
<?php
$taskId = 5;

$sql = "DELETE FROM tasks WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$taskId]);

echo "Task deleted!";
```

### Delete with Condition

```php
<?php
// Delete all completed tasks
$sql = "DELETE FROM tasks WHERE is_completed = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([1]);

$deleted = $stmt->rowCount();
echo "Deleted $deleted completed tasks";
```

### Delete All (Careful!)

```php
<?php
//   Deletes EVERYTHING in the table!
$sql = "DELETE FROM tasks";
$pdo->exec($sql);

// Better: Use TRUNCATE (faster, resets auto-increment)
$pdo->exec("TRUNCATE TABLE tasks");
```

### Safe Delete Pattern

Always check if the record exists before deleting:

```php
<?php
$taskId = 5;

// First, check if it exists
$stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ?");
$stmt->execute([$taskId]);
$task = $stmt->fetch();

if (!$task) {
    echo "Task not found";
} else {
    // Now delete it
    $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ?");
    $stmt->execute([$taskId]);
    echo "Task '{$task['title']}' deleted";
}
```

---

## Complete CRUD Example: Task Manager

Let's put it all together in a simple task management system:

### db.php (Database Connection)

```php
<?php
// db.php
$host = '127.0.0.1';
$dbname = 'todo_app';
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
    die("Connection failed: " . $e->getMessage());
}
```

### create.php (Add New Task)

```php
<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];

    $sql = "INSERT INTO tasks (title, description) VALUES (?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$title, $description]);

    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Task</title>
</head>
<body>
    <h1>Add New Task</h1>
    <form method="POST">
        <input type="text" name="title" placeholder="Task title" required>
        <br><br>
        <textarea name="description" placeholder="Description"></textarea>
        <br><br>
        <button type="submit">Add Task</button>
    </form>
    <br>
    <a href="index.php">Back to tasks</a>
</body>
</html>
```

### index.php (List All Tasks)

```php
<?php
require_once 'db.php';

// Get all tasks
$stmt = $pdo->query("SELECT * FROM tasks ORDER BY created_at DESC");
$tasks = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Tasks</title>
</head>
<body>
    <h1>My Tasks</h1>
    <a href="create.php">Add New Task</a>
    <br><br>

    <?php if (empty($tasks)): ?>
        <p>No tasks yet!</p>
    <?php else: ?>
        <ul>
            <?php foreach ($tasks as $task): ?>
                <li>
                    <strong><?= htmlspecialchars($task['title']) ?></strong>
                    <?php if ($task['is_completed']): ?>
                         Completed
                    <?php else: ?>
                        Ë Pending
                    <?php endif; ?>
                    <br>
                    <?= htmlspecialchars($task['description']) ?>
                    <br>
                    <a href="edit.php?id=<?= $task['id'] ?>">Edit</a>
                    <a href="delete.php?id=<?= $task['id'] ?>">Delete</a>
                    <a href="toggle.php?id=<?= $task['id'] ?>">Toggle</a>
                </li>
                <br>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</body>
</html>
```

### edit.php (Update Task)

```php
<?php
require_once 'db.php';

$id = $_GET['id'];

// Get current task data
$stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ?");
$stmt->execute([$id]);
$task = $stmt->fetch();

if (!$task) {
    die("Task not found");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];

    $sql = "UPDATE tasks SET title = ?, description = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$title, $description, $id]);

    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Task</title>
</head>
<body>
    <h1>Edit Task</h1>
    <form method="POST">
        <input type="text" name="title" value="<?= htmlspecialchars($task['title']) ?>" required>
        <br><br>
        <textarea name="description"><?= htmlspecialchars($task['description']) ?></textarea>
        <br><br>
        <button type="submit">Update Task</button>
    </form>
    <br>
    <a href="index.php">Cancel</a>
</body>
</html>
```

### toggle.php (Toggle Completed)

```php
<?php
require_once 'db.php';

$id = $_GET['id'];

$sql = "UPDATE tasks SET is_completed = NOT is_completed WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id]);

header('Location: index.php');
exit;
```

### delete.php (Delete Task)

```php
<?php
require_once 'db.php';

$id = $_GET['id'];

$sql = "DELETE FROM tasks WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id]);

header('Location: index.php');
exit;
```

**You now have a full CRUD application!**

---

## Working with Forms

### Getting Form Data Safely

```php
<?php
// Always check if the key exists
$title = $_POST['title'] ?? '';
$description = $_POST['description'] ?? '';

// Or with isset()
if (isset($_POST['title'])) {
    $title = $_POST['title'];
}
```

### Validating Before Insert

```php
<?php
$errors = [];

if (empty($_POST['title'])) {
    $errors[] = "Title is required";
}

if (strlen($_POST['title']) > 255) {
    $errors[] = "Title is too long (max 255 characters)";
}

if (empty($errors)) {
    // Safe to insert
    $sql = "INSERT INTO tasks (title, description) VALUES (?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_POST['title'], $_POST['description']]);
} else {
    // Show errors
    foreach ($errors as $error) {
        echo $error . "<br>";
    }
}
```

---

## Security: htmlspecialchars()

**Always escape output to prevent XSS (Cross-Site Scripting):**

```php
<?php
// L DANGEROUS: If title contains <script>, it executes!
echo $task['title'];

//  SAFE: Converts <script> to &lt;script&gt;
echo htmlspecialchars($task['title']);

// Shorthand
echo htmlspecialchars($task['title'], ENT_QUOTES, 'UTF-8');
```

**Rule:** Always use `htmlspecialchars()` when displaying user-generated content.

---

## Common Patterns

### Check if Record Exists

```php
<?php
function taskExists($pdo, $id) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM tasks WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetchColumn() > 0;
}

if (taskExists($pdo, 5)) {
    echo "Task exists!";
}
```

### Get Record or Die

```php
<?php
function getTaskOrDie($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ?");
    $stmt->execute([$id]);
    $task = $stmt->fetch();

    if (!$task) {
        die("Task not found");
    }

    return $task;
}

$task = getTaskOrDie($pdo, 5);
```

### Count Records

```php
<?php
// Total tasks
$stmt = $pdo->query("SELECT COUNT(*) FROM tasks");
$total = $stmt->fetchColumn();

// Completed tasks
$stmt = $pdo->prepare("SELECT COUNT(*) FROM tasks WHERE is_completed = ?");
$stmt->execute([1]);
$completed = $stmt->fetchColumn();

echo "Completed $completed out of $total tasks";
```

---

## Error Handling

### Try-Catch for Database Operations

```php
<?php
require_once 'db.php';

try {
    $sql = "INSERT INTO tasks (title) VALUES (?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_POST['title']]);

    echo "Task created!";
} catch (PDOException $e) {
    // Log error (don't show database details to users!)
    error_log($e->getMessage());
    echo "Something went wrong. Please try again.";
}
```

### Checking for Duplicates

```php
<?php
// Check if task with same title exists
$stmt = $pdo->prepare("SELECT COUNT(*) FROM tasks WHERE title = ?");
$stmt->execute([$title]);

if ($stmt->fetchColumn() > 0) {
    echo "A task with this title already exists!";
} else {
    // Insert new task
    $stmt = $pdo->prepare("INSERT INTO tasks (title) VALUES (?)");
    $stmt->execute([$title]);
}
```

---

## Practice Exercises

### Exercise 1: User CRUD

Create a `users` table and build:
1. Create new user (name, email)
2. List all users
3. Edit user details
4. Delete user

```sql
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Exercise 2: Search Feature

Add search to your tasks app:
- Search by title
- Filter by completed status
- Sort by date (newest/oldest first)

### Exercise 3: Pagination

Implement pagination for tasks:
- Show 5 tasks per page
- Add "Next" and "Previous" links
- Display current page number

<details>
<summary>Pagination Hint</summary>

```php
<?php
$page = $_GET['page'] ?? 1;
$perPage = 5;
$offset = ($page - 1) * $perPage;

$sql = "SELECT * FROM tasks LIMIT ? OFFSET ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$perPage, $offset]);
$tasks = $stmt->fetchAll();

// Get total for calculating pages
$total = $pdo->query("SELECT COUNT(*) FROM tasks")->fetchColumn();
$totalPages = ceil($total / $perPage);
?>
<a href="?page=<?= $page - 1 ?>">Previous</a>
Page <?= $page ?> of <?= $totalPages ?>
<a href="?page=<?= $page + 1 ?>">Next</a>
```
</details>

---

## Common Mistakes

### 1. Not Using Placeholders

```php
// L SQL INJECTION RISK!
$sql = "SELECT * FROM tasks WHERE id = {$_GET['id']}";

//  Safe with placeholder
$sql = "SELECT * FROM tasks WHERE id = ?";
$stmt->execute([$_GET['id']]);
```

### 2. Forgetting to Execute

```php
// L Forgot execute()
$stmt = $pdo->prepare("SELECT * FROM tasks");
$tasks = $stmt->fetchAll();  // ERROR: Nothing to fetch

//  Must execute first
$stmt = $pdo->prepare("SELECT * FROM tasks");
$stmt->execute();
$tasks = $stmt->fetchAll();
```

### 3. Using query() with Variables

```php
// L Don't use query() with variables
$stmt = $pdo->query("SELECT * FROM tasks WHERE id = $id");

//  Use prepare() + execute()
$stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ?");
$stmt->execute([$id]);
```

### 4. Not Checking if Record Exists

```php
// L Might try to use NULL
$task = $stmt->fetch();
echo $task['title'];  // ERROR if task not found

//  Check first
$task = $stmt->fetch();
if ($task) {
    echo $task['title'];
} else {
    echo "Task not found";
}
```

---

## Quick Reference

### Create
```php
$sql = "INSERT INTO table (col1, col2) VALUES (?, ?)";
$stmt = $pdo->prepare($sql);
$stmt->execute([$val1, $val2]);
$newId = $pdo->lastInsertId();
```

### Read
```php
// All records
$stmt = $pdo->query("SELECT * FROM table");
$rows = $stmt->fetchAll();

// One record
$stmt = $pdo->prepare("SELECT * FROM table WHERE id = ?");
$stmt->execute([$id]);
$row = $stmt->fetch();
```

### Update
```php
$sql = "UPDATE table SET col = ? WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$newValue, $id]);
$affected = $stmt->rowCount();
```

### Delete
```php
$sql = "DELETE FROM table WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id]);
$deleted = $stmt->rowCount();
```

---

## What's Next?

You can now perform all CRUD operations! But there's a critical security topic we've only briefly touched on:

**Prepared statements** and **SQL injection prevention**.

In the next lesson, you'll learn:
- What SQL injection is and why it's dangerous
- How prepared statements protect you
- Named vs positional placeholders
- Best practices for secure database queries

---

## Key Takeaways

1. **CRUD = Create, Read, Update, Delete** - The four fundamental operations
2. **Always use prepare() + execute()** - Not query() with variables
3. **Check rowCount()** - To see if UPDATE/DELETE affected anything
4. **Use lastInsertId()** - To get the ID of newly inserted records
5. **Validate input before inserting** - Check required fields, lengths, formats
6. **Use htmlspecialchars() on output** - Prevent XSS attacks
7. **Check if records exist** - Before updating or deleting
8. **Use try-catch for errors** - Handle database failures gracefully

---

**Next Lesson:** [05 - Prepared Statements: Security with Placeholders](./05-prepared-statements.md)
