# Exercise 6.1 - Database Connection with PDO

## Objective

Learn how to connect to a MySQL database using PDO (PHP Data Objects) for secure database operations.

## Duration

1-1.5 hours

## Task

Create a secure database connection using PDO and test the connection with basic queries.

## Requirements

- [ ] Create a `Database.php` file with a Database class
- [ ] Implement PDO connection with proper error handling
- [ ] Use environment variables or config file for database credentials
- [ ] Create a `connect()` method that returns PDO connection
- [ ] Test connection with a simple query
- [ ] Handle connection errors gracefully
- [ ] Create a test script that verifies the connection

## Starter Files

Work in `Database.php` and `test-connection.php` - see starter code there.

## Expected Output

```
php test-connection.php

Database Connection Test
========================

Attempting to connect to MySQL database...
Connection successful!

Database: MySQL
Version: 8.0.xx

Executing test query...
Test query completed successfully!

Connection test passed!
```

## Checklist

- [ ] Database class created with PDO
- [ ] Connection credentials configured properly
- [ ] Error handling implemented for connection failures
- [ ] Test connection verifies database is accessible
- [ ] PDO is configured for error reporting
- [ ] Connection uses prepared statements (shown in example)
- [ ] Proper try-catch blocks for exception handling
- [ ] Code runs without errors

## Tips

- PDO is more secure than MySQLi for most use cases
- Use try-catch blocks to handle PDOException
- Set PDO attributes for error mode
- Use prepared statements to prevent SQL injection
- Keep database credentials in a separate config file
- Test connection before running full application
