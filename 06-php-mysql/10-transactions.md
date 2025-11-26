# Lesson 10 - Transactions: All or Nothing

## The Problem: Partial Failures

Imagine transferring money between bank accounts:

```php
<?php
// Withdraw from account A
$stmt = $pdo->prepare("UPDATE accounts SET balance = balance - ? WHERE id = ?");
$stmt->execute([100, $accountA]);

// Deposit to account B
$stmt = $pdo->prepare("UPDATE accounts SET balance = balance + ? WHERE id = ?");
$stmt->execute([100, $accountB]);
```

**What if:**
- Power goes out after the first query?
- Database crashes between queries?
- Second query fails due to an error?

**Result:** $100 disappears! Account A was debited but account B was never credited.

**This is catastrophic.**

---

## The Solution: Transactions

A **transaction** groups multiple queries into a single atomic unit:
- **All succeed** ’ Changes are committed (saved)
- **Any fails** ’ All changes are rolled back (undone)

**No partial failures!**

```php
<?php
try {
    $pdo->beginTransaction();

    // Withdraw from account A
    $stmt = $pdo->prepare("UPDATE accounts SET balance = balance - ? WHERE id = ?");
    $stmt->execute([100, $accountA]);

    // Deposit to account B
    $stmt = $pdo->prepare("UPDATE accounts SET balance = balance + ? WHERE id = ?");
    $stmt->execute([100, $accountB]);

    $pdo->commit();  //  Both succeeded - save changes
    echo "Transfer successful!";
} catch (Exception $e) {
    $pdo->rollBack();  // L Something failed - undo everything
    echo "Transfer failed: " . $e->getMessage();
}
```

**Now it's safe!** Either both queries happen or neither does.

---

## ACID Properties

Transactions guarantee **ACID**:

### A - Atomicity
**All or nothing.** Either all operations in a transaction succeed, or none do.

```php
// Atomic: Both happen or neither happens
$pdo->beginTransaction();
$pdo->query("UPDATE accounts SET balance = balance - 100 WHERE id = 1");
$pdo->query("UPDATE accounts SET balance = balance + 100 WHERE id = 2");
$pdo->commit();
```

### C - Consistency
**Database moves from one valid state to another.** Rules and constraints are always enforced.

```php
// If this violates a constraint, the whole transaction fails
$pdo->beginTransaction();
$pdo->query("INSERT INTO users (email) VALUES ('duplicate@email.com')");  // Violates UNIQUE
$pdo->commit();  // This will fail, rollback happens
```

### I - Isolation
**Concurrent transactions don't interfere.** Each transaction sees a consistent snapshot.

```php
// User A and User B both try to update the same row
// MySQL ensures they don't corrupt each other's data
```

### D - Durability
**Committed transactions are permanent.** Survive crashes, power failures, etc.

```php
$pdo->commit();  // Once committed, data is safe even if server crashes
```

---

## Transaction Syntax

### Basic Pattern

```php
<?php
try {
    $pdo->beginTransaction();  // Start transaction

    // Your queries here
    $pdo->query("INSERT INTO ...");
    $pdo->query("UPDATE ...");
    $pdo->query("DELETE FROM ...");

    $pdo->commit();  // Save changes
} catch (Exception $e) {
    $pdo->rollBack();  // Undo everything
    // Handle error
}
```

### Three Methods

```php
$pdo->beginTransaction();  // Start a transaction
$pdo->commit();            // Save all changes
$pdo->rollBack();          // Undo all changes
```

---

## Real-World Example: Bank Transfer

```php
<?php
require_once 'db.php';

function transfer(PDO $pdo, int $fromAccount, int $toAccount, float $amount): bool {
    try {
        $pdo->beginTransaction();

        // Check balance
        $stmt = $pdo->prepare("SELECT balance FROM accounts WHERE id = ?");
        $stmt->execute([$fromAccount]);
        $balance = $stmt->fetchColumn();

        if ($balance < $amount) {
            throw new Exception("Insufficient funds");
        }

        // Withdraw
        $stmt = $pdo->prepare("UPDATE accounts SET balance = balance - ? WHERE id = ?");
        $stmt->execute([$amount, $fromAccount]);

        // Deposit
        $stmt = $pdo->prepare("UPDATE accounts SET balance = balance + ? WHERE id = ?");
        $stmt->execute([$amount, $toAccount]);

        // Log transaction
        $stmt = $pdo->prepare("
            INSERT INTO transaction_log (from_account, to_account, amount)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$fromAccount, $toAccount, $amount]);

        $pdo->commit();
        return true;

    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Transfer failed: " . $e->getMessage());
        return false;
    }
}

// Usage
if (transfer($pdo, 1, 2, 100.00)) {
    echo "Transfer successful!";
} else {
    echo "Transfer failed!";
}
```

**If ANY step fails (insufficient funds, constraint violation, database error), ALL changes are rolled back.**

---

## Real-World Example: E-commerce Order

```php
<?php
function createOrder(PDO $pdo, int $userId, array $items): ?int {
    try {
        $pdo->beginTransaction();

        // Create order
        $stmt = $pdo->prepare("INSERT INTO orders (user_id, total, status) VALUES (?, ?, 'pending')");
        $total = array_sum(array_column($items, 'price'));
        $stmt->execute([$userId, $total]);
        $orderId = $pdo->lastInsertId();

        // Add order items
        $stmt = $pdo->prepare("
            INSERT INTO order_items (order_id, product_id, quantity, price)
            VALUES (?, ?, ?, ?)
        ");

        foreach ($items as $item) {
            $stmt->execute([
                $orderId,
                $item['product_id'],
                $item['quantity'],
                $item['price']
            ]);

            // Decrease product stock
            $updateStock = $pdo->prepare("
                UPDATE products
                SET stock = stock - ?
                WHERE id = ? AND stock >= ?
            ");
            $updateStock->execute([
                $item['quantity'],
                $item['product_id'],
                $item['quantity']
            ]);

            // Check if stock update worked
            if ($updateStock->rowCount() === 0) {
                throw new Exception("Product {$item['product_id']} out of stock");
            }
        }

        $pdo->commit();
        return $orderId;

    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Order creation failed: " . $e->getMessage());
        return null;
    }
}

// Usage
$items = [
    ['product_id' => 1, 'quantity' => 2, 'price' => 29.99],
    ['product_id' => 5, 'quantity' => 1, 'price' => 99.99]
];

$orderId = createOrder($pdo, $userId, $items);

if ($orderId) {
    echo "Order created: #$orderId";
} else {
    echo "Order failed - no stock was changed";
}
```

**Benefits:**
- Order and items created together
- Stock decremented atomically
- If any item is out of stock, entire order fails
- No partial orders!

---

## When to Use Transactions

###  Use Transactions When:

1. **Multiple related operations must all succeed**
   - Bank transfers
   - Creating orders with items
   - User registration (create user, send email, log activity)

2. **Data integrity is critical**
   - Inventory management
   - Financial transactions
   - Booking systems

3. **Complex business logic**
   - Multi-step processes
   - Dependent operations

### L Don't Need Transactions For:

1. **Single operations**
   - One INSERT, UPDATE, or DELETE
   - Already atomic by default

2. **Read-only queries**
   - SELECT statements
   - No data changes

3. **Independent operations**
   - Logging
   - Analytics
   - Cache updates

---

## Nested Transactions: Not Supported

PDO doesn't support nested transactions:

```php
<?php
// L This doesn't work as you might expect
$pdo->beginTransaction();
    $pdo->query("INSERT INTO ...");

    $pdo->beginTransaction();  // This does nothing!
        $pdo->query("INSERT INTO ...");
    $pdo->commit();  // This does nothing!

$pdo->commit();
```

**Solution:** Use savepoints (advanced) or restructure your code.

---

## Savepoints (Advanced)

Savepoints allow partial rollbacks within a transaction:

```php
<?php
try {
    $pdo->beginTransaction();

    $pdo->query("INSERT INTO users (name) VALUES ('Kieu')");

    $pdo->exec("SAVEPOINT my_savepoint");

    $pdo->query("INSERT INTO posts (title) VALUES ('Bad post')");

    // Rollback to savepoint (undoes only the post insert)
    $pdo->exec("ROLLBACK TO SAVEPOINT my_savepoint");

    $pdo->query("INSERT INTO posts (title) VALUES ('Good post')");

    $pdo->commit();  // Commits user and "Good post"
} catch (Exception $e) {
    $pdo->rollBack();
}
```

**Rarely needed in practice.** Most apps use simple begin/commit/rollback.

---

## Transaction Isolation Levels

Controls how transactions see each other's changes:

```php
<?php
// Set isolation level
$pdo->exec("SET TRANSACTION ISOLATION LEVEL READ COMMITTED");
$pdo->beginTransaction();
// ...
```

### Levels (Most to Least Strict):

1. **SERIALIZABLE** - Complete isolation (slowest)
2. **REPEATABLE READ** - MySQL default
3. **READ COMMITTED** - See committed changes from others
4. **READ UNCOMMITTED** - See uncommitted changes (dirty reads)

**Default is usually fine.** Don't change unless you have specific concurrency needs.

---

## Checking Transaction Status

```php
<?php
if ($pdo->inTransaction()) {
    echo "Currently in a transaction";
} else {
    echo "No active transaction";
}
```

---

## Automatic Rollback on Exception

With `PDO::ERRMODE_EXCEPTION`, PDO automatically triggers an exception on errors:

```php
<?php
try {
    $pdo->beginTransaction();

    $pdo->query("INSERT INTO users (email) VALUES ('test@example.com')");
    $pdo->query("INSERT INTO users (email) VALUES ('test@example.com')");  // L Duplicate!

    $pdo->commit();  // Never reached

} catch (PDOException $e) {
    $pdo->rollBack();  // Automatic rollback
    echo "Transaction failed: " . $e->getMessage();
}
```

---

## Common Mistakes

### 1. Forgetting rollback in catch

```php
// L Bad - changes might be partially committed
try {
    $pdo->beginTransaction();
    // queries...
    $pdo->commit();
} catch (Exception $e) {
    // Forgot rollback!
    echo "Error: " . $e->getMessage();
}

//  Good
try {
    $pdo->beginTransaction();
    // queries...
    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    echo "Error: " . $e->getMessage();
}
```

### 2. Not using try-catch

```php
// L Bad - no error handling
$pdo->beginTransaction();
$pdo->query("...");
$pdo->query("...");
$pdo->commit();

//  Good
try {
    $pdo->beginTransaction();
    $pdo->query("...");
    $pdo->query("...");
    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
}
```

### 3. Long-running transactions

```php
// L Bad - locks tables for too long
$pdo->beginTransaction();
sleep(30);  // Doing slow work
$pdo->query("...");
$pdo->commit();

//  Good - keep transactions short
// Do slow work first
$data = doSlowWork();

// Then quick transaction
$pdo->beginTransaction();
$pdo->query("INSERT INTO ... VALUES (?)", [$data]);
$pdo->commit();
```

### 4. Committing twice

```php
// L Bad
$pdo->beginTransaction();
$pdo->query("...");
$pdo->commit();
$pdo->commit();  // Error - no active transaction

//  Good - commit once
$pdo->beginTransaction();
$pdo->query("...");
$pdo->commit();
```

---

## Performance Considerations

### Transactions are Slower

```php
// Without transaction (faster for bulk inserts)
for ($i = 0; $i < 1000; $i++) {
    $pdo->query("INSERT INTO logs (message) VALUES ('Log $i')");
}

// With transaction (MUCH faster!)
$pdo->beginTransaction();
for ($i = 0; $i < 1000; $i++) {
    $pdo->query("INSERT INTO logs (message) VALUES ('Log $i')");
}
$pdo->commit();
```

**Transactions are faster for bulk operations** because MySQL commits once instead of 1000 times.

### Lock Considerations

Transactions lock rows/tables:

```php
// Transaction A
$pdo->beginTransaction();
$pdo->query("UPDATE products SET stock = 5 WHERE id = 1");  // Locks row
sleep(10);  // Other transactions wait here!
$pdo->commit();
```

**Keep transactions SHORT!**

---

## Transactions with Models

Add transaction support to your models:

```php
<?php
class Database {
    private static ?PDO $pdo = null;

    public static function connect(): PDO {
        if (self::$pdo === null) {
            // ... connection setup ...
        }
        return self::$pdo;
    }

    public static function transaction(callable $callback) {
        $pdo = self::connect();

        try {
            $pdo->beginTransaction();
            $result = $callback($pdo);
            $pdo->commit();
            return $result;
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}

// Usage
Database::transaction(function($pdo) {
    // All queries here are in a transaction
    $stmt = $pdo->prepare("INSERT INTO users (name) VALUES (?)");
    $stmt->execute(['Kieu']);

    $stmt = $pdo->prepare("INSERT INTO posts (user_id, title) VALUES (?, ?)");
    $stmt->execute([$pdo->lastInsertId(), 'First Post']);
});
```

**Clean and reusable!**

---

## Real-World Example: Blog Post with Tags

```php
<?php
function createPostWithTags(PDO $pdo, int $userId, string $title, string $content, array $tags): ?int {
    try {
        $pdo->beginTransaction();

        // Create post
        $stmt = $pdo->prepare("INSERT INTO posts (user_id, title, content) VALUES (?, ?, ?)");
        $stmt->execute([$userId, $title, $content]);
        $postId = $pdo->lastInsertId();

        // Create or get tags and link them
        $stmt = $pdo->prepare("SELECT id FROM tags WHERE name = ?");
        $linkStmt = $pdo->prepare("INSERT INTO post_tag (post_id, tag_id) VALUES (?, ?)");

        foreach ($tags as $tagName) {
            // Check if tag exists
            $stmt->execute([$tagName]);
            $tag = $stmt->fetch();

            if ($tag) {
                $tagId = $tag['id'];
            } else {
                // Create new tag
                $createTag = $pdo->prepare("INSERT INTO tags (name) VALUES (?)");
                $createTag->execute([$tagName]);
                $tagId = $pdo->lastInsertId();
            }

            // Link post to tag
            $linkStmt->execute([$postId, $tagId]);
        }

        $pdo->commit();
        return $postId;

    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Failed to create post: " . $e->getMessage());
        return null;
    }
}

// Usage
$postId = createPostWithTags(
    $pdo,
    1,
    'Learn MySQL',
    'MySQL is great!',
    ['MySQL', 'Database', 'Tutorial']
);
```

---

## Practice Exercises

### Exercise 1: User Registration

Create a function that registers a user and creates their profile in one transaction:
- Insert into `users` table
- Insert into `profiles` table
- Insert into `activity_log` table

### Exercise 2: Product Purchase

Create a function that:
- Creates an order
- Adds order items
- Decrements product stock
- All in one transaction

### Exercise 3: Blog Post Deletion

Create a function that deletes a post and all related data:
- Delete post
- Delete all comments on the post
- Delete all likes on the post
- All in one transaction

---

## Quick Reference

### Basic Transaction

```php
try {
    $pdo->beginTransaction();
    // queries...
    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    throw $e;
}
```

### With Helper Function

```php
function transaction(PDO $pdo, callable $callback) {
    try {
        $pdo->beginTransaction();
        $result = $callback($pdo);
        $pdo->commit();
        return $result;
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

// Usage
transaction($pdo, function($pdo) {
    $pdo->query("INSERT INTO ...");
    $pdo->query("UPDATE ...");
});
```

---

## Module Complete!

Congratulations! You've completed Module 06 - PHP & MySQL!

**You learned:**
1. What databases are and why they're essential
2. SQL basics - SELECT, INSERT, UPDATE, DELETE
3. Connecting PHP to MySQL with PDO
4. CRUD operations
5. Prepared statements for security
6. Foreign keys and relationships
7. JOIN queries
8. Database design principles
9. OOP with databases (models)
10. Transactions for data integrity

**What's next?**
- **Module 07:** Sessions & Authentication - Build login/register systems
- **Module 08:** Security - Protect against attacks
- **Module 09:** APIs - Build RESTful APIs

---

## Key Takeaways

1. **Transactions ensure atomicity** - All or nothing
2. **Use try-catch with rollback** - Always handle errors
3. **Keep transactions short** - Avoid long-running operations
4. **Critical for financial operations** - Transfers, orders, payments
5. **Three methods:** beginTransaction(), commit(), rollBack()
6. **ACID properties** - Atomicity, Consistency, Isolation, Durability
7. **Wrap in helper functions** - Makes code cleaner

---

**You're now ready to build secure, data-driven web applications!**
