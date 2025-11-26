# Module 15 - Laravel Eloquent ORM

**Duration**: 2 weeks
**Prerequisites**: Module 14 - Laravel Basics

---

## Learning Objectives

- Master Eloquent ORM
- Understand Active Record pattern
- Define model relationships
- Use query builder
- Implement accessors and mutators
- Work with Eloquent collections

---

## "Remember PDO?" Moments

**Pure PHP (Module 06):**
```php
$sql = "SELECT * FROM posts WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$userId]);
$posts = $stmt->fetchAll();
```

**Eloquent:**
```php
$posts = Post::where('user_id', $userId)
    ->orderBy('created_at', 'desc')
    ->get();
```

---

## Topics Covered

1. Eloquent models
2. CRUD with Eloquent
3. Relationships (hasMany, belongsTo, belongsToMany)
4. Eager loading (N+1 problem solution)
5. Query scopes
6. Accessors and mutators
7. Mass assignment protection
8. Soft deletes

---

## Exercises

| ID | Exercise | Description | Duration |
|----|----------|-------------|----------|
| 15.1 | Models & CRUD | Create models with CRUD | 3 hours |
| 15.2 | Relationships | Users, Posts, Comments | 4 hours |
| 15.3 | Query Optimization | Fix N+1 queries | 2 hours |
| 15.4 | Blog with Eloquent | Complete blog rebuild | 8 hours |

---

## Next Module

**Module 16 - Laravel Auth & Security**: Authentication the Laravel way!
