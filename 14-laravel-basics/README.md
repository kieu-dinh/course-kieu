# Module 14 - Laravel Basics

**Duration**: 2-3 weeks
**Prerequisites**: Modules 04-13 (ALL previous modules!)

---

## 🎉 Welcome to Laravel!

**Remember all that code you wrote in Modules 04-10?**

Laravel will make you say: **"Wait, THAT'S IT?!"**

But NOW you understand what Laravel does behind the scenes!

---

## Learning Objectives

- Install Laravel with Composer
- Understand MVC architecture
- Use Laravel routing
- Create controllers
- Work with Blade templates
- Understand Laravel request lifecycle

---

## The "Aha!" Moments

### Pure PHP vs Laravel

**Pure PHP (Module 07):**
```php
// Login - 50+ lines of code
$sql = "SELECT * FROM users WHERE email = :email";
$stmt = $pdo->prepare($sql);
// ... validation, password_verify, session setup, etc.
```

**Laravel:**
```php
Auth::attempt(['email' => $email, 'password' => $password]);
```

**Pure PHP (Module 06):**
```php
// Get all posts - 10+ lines
$sql = "SELECT * FROM posts";
$stmt = $pdo->query($sql);
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
```

**Laravel:**
```php
$posts = Post::all();
```

**You'll appreciate Laravel SO much more now!**

---

## Topics Covered

1. Laravel installation and structure
2. Artisan CLI
3. Routing (web.php, api.php)
4. Controllers
5. Views and Blade templates
6. Migrations and databases
7. Models (introduction to Eloquent)
8. Request and Response
9. Middleware basics
10. Environment configuration (.env)

---

## Exercises

| ID | Exercise | Description | Duration |
|----|----------|-------------|----------|
| 14.1 | First Laravel App | Install and explore Laravel | 2 hours |
| 14.2 | Routes & Controllers | Create routes and controllers | 2 hours |
| 14.3 | Blade Templates | Build views with Blade | 3 hours |
| 14.4 | Simple Blog | Posts with MVC pattern | 6 hours |

---

## Next Module

**Module 15 - Eloquent ORM**: Laravel's powerful database layer!
