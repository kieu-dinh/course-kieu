# Lesson 03 - Conditions

## The Concept

Conditions let your program make decisions:

```
Is the user logged in?
├── YES → Show dashboard
└── NO → Show login page
```

---

## If Statement

```php
<?php
$age = 20;

if ($age >= 18) {
    echo "You can vote!";
}
```

Only runs the code if condition is `true`.

---

## If-Else

```php
<?php
$age = 15;

if ($age >= 18) {
    echo "Adult";
} else {
    echo "Minor";
}
```

---

## If-Elseif-Else

```php
<?php
$score = 85;

if ($score >= 90) {
    echo "Grade: A";
} elseif ($score >= 80) {
    echo "Grade: B";
} elseif ($score >= 70) {
    echo "Grade: C";
} elseif ($score >= 60) {
    echo "Grade: D";
} else {
    echo "Grade: F";
}
```

---

## Comparison Operators

| Operator | Meaning | Example |
|----------|---------|---------|
| `==` | Equal (value) | `5 == "5"` → true |
| `===` | Equal (value + type) | `5 === "5"` → false |
| `!=` | Not equal | `5 != 3` → true |
| `!==` | Not equal (strict) | `5 !== "5"` → true |
| `>` | Greater than | `5 > 3` → true |
| `<` | Less than | `5 < 3` → false |
| `>=` | Greater or equal | `5 >= 5` → true |
| `<=` | Less or equal | `5 <= 3` → false |

**Always use `===` (strict) when possible!**

---

## Logical Operators

| Operator | Meaning | Example |
|----------|---------|---------|
| `&&` | AND | `true && false` → false |
| `\|\|` | OR | `true \|\| false` → true |
| `!` | NOT | `!true` → false |

```php
<?php
$age = 25;
$hasLicense = true;

// AND: both must be true
if ($age >= 18 && $hasLicense) {
    echo "Can drive";
}

// OR: at least one true
$isWeekend = true;
$isHoliday = false;

if ($isWeekend || $isHoliday) {
    echo "Day off!";
}

// NOT: inverse
$isLoggedIn = false;

if (!$isLoggedIn) {
    echo "Please login";
}
```

---

## Switch Statement

For multiple conditions on same variable:

```php
<?php
$day = "Monday";

switch ($day) {
    case "Monday":
        echo "Start of week";
        break;
    case "Friday":
        echo "Almost weekend!";
        break;
    case "Saturday":
    case "Sunday":
        echo "Weekend!";
        break;
    default:
        echo "Regular day";
}
```

Don't forget `break;`!

---

## Ternary Operator

Short if-else:

```php
<?php
$age = 20;

// Long version
if ($age >= 18) {
    $status = "Adult";
} else {
    $status = "Minor";
}

// Short version (ternary)
$status = $age >= 18 ? "Adult" : "Minor";

echo $status;
```

Syntax: `condition ? value_if_true : value_if_false`

---

## Null Coalescing

```php
<?php
// If $name exists and not null, use it. Otherwise use default.
$name = $username ?? "Guest";

// Same as:
$name = isset($username) ? $username : "Guest";
```

---

## Practice

```php
<?php
// 1. Check if number is positive, negative, or zero
$number = -5;

if ($number > 0) {
    echo "Positive";
} elseif ($number < 0) {
    echo "Negative";
} else {
    echo "Zero";
}

// 2. Check if someone can enter a bar (18+ and has ID)
$age = 20;
$hasID = true;

if ($age >= 18 && $hasID) {
    echo "Welcome!";
} else {
    echo "Cannot enter";
}

// 3. Determine shipping cost
$country = "France";
$shipping = match ($country) {
    "France" => 5,
    "Germany", "Belgium" => 10,
    "USA", "Canada" => 25,
    default => 30,
};
echo "Shipping: $shipping€";
```

---

## Next

[Lesson 04: Loops](./04-loops.md)
