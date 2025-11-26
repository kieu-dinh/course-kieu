# Lesson 01 - PHP Basics

## What is PHP?

PHP is a server-side programming language. It runs on the server, not in the browser.

```
Browser: Sends request
   ↓
Server: PHP runs, generates HTML
   ↓
Browser: Receives HTML, displays it
```

---

## Your First PHP File

Create `hello.php`:

```php
<?php
echo "Hello World!";
```

Run it:
```bash
php hello.php
```

Output:
```
Hello World!
```

---

## Echo and Print

`echo` outputs text:

```php
<?php
echo "Hello";
echo " World";
// Output: Hello World

// With newline
echo "Line 1\n";
echo "Line 2\n";
```

---

## Comments

```php
<?php
// Single line comment

/*
Multi-line
comment
*/

echo "Hello"; // Inline comment
```

---

## Variables

Variables store values. They start with `$`:

```php
<?php
$name = "Kieu";
$age = 28;

echo $name;  // Kieu
echo $age;   // 28
```

### Rules
- Start with `$`
- Then letter or underscore
- Can contain letters, numbers, underscores
- Case-sensitive (`$Name` ≠ `$name`)

---

## String Concatenation

Join strings with `.`:

```php
<?php
$first = "Hello";
$second = "World";

echo $first . " " . $second;  // Hello World
```

Or use double quotes:

```php
<?php
$name = "Kieu";
echo "Hello $name!";  // Hello Kieu!
echo 'Hello $name!';  // Hello $name! (single quotes = literal)
```

---

## Printing Variables (Debugging)

```php
<?php
$name = "Kieu";
$numbers = [1, 2, 3];

// For simple values
echo $name;

// For arrays/objects
print_r($numbers);

// More detailed
var_dump($numbers);
```

---

## PHP in HTML

PHP can generate HTML:

Create `page.php`:
```php
<!DOCTYPE html>
<html>
<head>
  <title>PHP Page</title>
</head>
<body>
  <h1><?php echo "Welcome!"; ?></h1>

  <?php
  $name = "Kieu";
  ?>

  <p>Hello, <?php echo $name; ?>!</p>

  <!-- Short syntax -->
  <p>Hello, <?= $name ?>!</p>
</body>
</html>
```

To see this in browser, you need a web server. With Herd:
```bash
# Put file in ~/Herd/project-name/page.php
# Visit: http://project-name.test/page.php
```

For now, we'll focus on command-line PHP.

---

## Practice

Create `practice.php`:

```php
<?php
// 1. Create variables for your name and age
$name = "Your name";
$age = 25;

// 2. Print a sentence using those variables
echo "My name is $name and I am $age years old.\n";

// 3. Calculate and print your age next year
$nextYear = $age + 1;
echo "Next year I will be $nextYear.\n";
```

Run: `php practice.php`

---

## Quick Reference

| Syntax | What it does |
|--------|--------------|
| `<?php ?>` | PHP tags |
| `$var = value;` | Create variable |
| `echo "text";` | Output text |
| `.` | Concatenate strings |
| `//` | Comment |
| `print_r()` | Print array |
| `var_dump()` | Print with type info |

---

## Next

[Lesson 02: Variables & Types](./02-variables.md)
