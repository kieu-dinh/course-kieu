# Lesson 08 - Traits

## The Problem: Single Inheritance Limitation

PHP only allows a class to extend one parent class. But sometimes you need functionality from multiple sources:

```php
<?php
// Want timestamping functionality
class Timestampable {
    public function setCreatedAt() { /* ... */ }
    public function setUpdatedAt() { /* ... */ }
}

// Want UUID functionality
class HasUuid {
    public function generateUuid() { /* ... */ }
}

// Want soft delete functionality
class SoftDeletes {
    public function softDelete() { /* ... */ }
    public function restore() { /* ... */ }
}

// ❌ Can't do this - only single inheritance!
class User extends Timestampable, HasUuid, SoftDeletes {}

// 😢 Must choose only one
class User extends Timestampable {
    // Have to copy-paste UUID and SoftDeletes code
}
```

**Problems:**
- Single inheritance means choosing one parent
- Code duplication when you need multiple features
- Can't compose behavior flexibly

---

## What are Traits?

**Traits** are a mechanism for code reuse that lets you include methods in multiple classes without inheritance.

Think of traits like:
- **Copy-paste** but automatic and reusable
- **Mixins** from other languages
- **Horizontal code reuse** (unlike inheritance which is vertical)

```php
<?php
// Define a trait
trait Timestampable {
    public string $createdAt;
    public string $updatedAt;

    public function setCreatedAt(): void {
        $this->createdAt = date('Y-m-d H:i:s');
    }

    public function setUpdatedAt(): void {
        $this->updatedAt = date('Y-m-d H:i:s');
    }

    public function touch(): void {
        $this->setUpdatedAt();
    }
}

// Use the trait
class User {
    use Timestampable; // Include all Timestampable methods

    public function __construct(
        public string $name,
        public string $email
    ) {
        $this->setCreatedAt();
        $this->setUpdatedAt();
    }
}

$user = new User("Kieu", "kieu@example.com");
echo $user->createdAt;  // 2024-01-15 14:30:00
$user->touch();         // Update timestamp
echo $user->updatedAt;  // 2024-01-15 14:30:05
```

**Key points:**
- Use `trait` keyword to define
- Use `use TraitName;` inside class to include
- Methods and properties become part of the class
- Can use multiple traits

---

## Multiple Traits

The main benefit: use as many traits as you need!

```php
<?php
trait Timestampable {
    public function touch(): void {
        echo "Updating timestamp\n";
    }
}

trait HasUuid {
    public string $uuid;

    public function generateUuid(): void {
        $this->uuid = uniqid('', true);
    }

    public function getUuid(): string {
        return $this->uuid;
    }
}

trait SoftDeletes {
    public ?string $deletedAt = null;

    public function softDelete(): void {
        $this->deletedAt = date('Y-m-d H:i:s');
        echo "Soft deleted\n";
    }

    public function restore(): void {
        $this->deletedAt = null;
        echo "Restored\n";
    }

    public function isDeleted(): bool {
        return $this->deletedAt !== null;
    }
}

// Use multiple traits!
class User {
    use Timestampable, HasUuid, SoftDeletes;

    public function __construct(
        public string $name
    ) {
        $this->generateUuid();
        $this->touch();
    }
}

$user = new User("Kieu");
echo $user->getUuid();  // From HasUuid
$user->touch();         // From Timestampable
$user->softDelete();    // From SoftDeletes
echo $user->isDeleted() ? "Deleted" : "Active"; // Deleted
$user->restore();       // From SoftDeletes
```

---

## Trait Methods Can Access Class Properties

```php
<?php
trait Sluggable {
    public string $slug;

    // This trait method can access $this->name from the class!
    public function generateSlug(): void {
        if (property_exists($this, 'name')) {
            $this->slug = strtolower(str_replace(' ', '-', $this->name));
        }
    }

    public function getSlug(): string {
        return $this->slug;
    }
}

class Product {
    use Sluggable;

    public function __construct(
        public string $name,
        public float $price
    ) {
        $this->generateSlug(); // Trait method accesses $this->name
    }
}

$product = new Product("Magic Mouse", 79.99);
echo $product->getSlug(); // magic-mouse
```

---

## Trait Method Conflicts

What happens when multiple traits have methods with the same name?

```php
<?php
trait Logger {
    public function log(string $message): void {
        echo "[LOG] $message\n";
    }
}

trait Debugger {
    public function log(string $message): void {
        echo "[DEBUG] $message\n";
    }
}

// ❌ Error: Trait method log has not been applied, because there are collisions
class Application {
    use Logger, Debugger;
}
```

### Solution 1: `insteadof` - Choose One

```php
<?php
class Application {
    use Logger, Debugger {
        Logger::log insteadof Debugger; // Use Logger's log, not Debugger's
    }
}

$app = new Application();
$app->log("Hello"); // [LOG] Hello (using Logger's version)
```

### Solution 2: `as` - Rename One

```php
<?php
class Application {
    use Logger, Debugger {
        Logger::log as logMessage;    // Rename Logger::log
        Debugger::log as debugMessage; // Rename Debugger::log
    }
}

$app = new Application();
$app->logMessage("Info");   // [LOG] Info
$app->debugMessage("Debug"); // [DEBUG] Debug
```

### Solution 3: Both - Choose Default and Rename Other

```php
<?php
class Application {
    use Logger, Debugger {
        Logger::log insteadof Debugger;   // Default is Logger
        Debugger::log as debugLog;        // But also keep Debugger as debugLog
    }
}

$app = new Application();
$app->log("Info");      // [LOG] Info (from Logger)
$app->debugLog("Debug"); // [DEBUG] Debug (from Debugger)
```

---

## Changing Trait Method Visibility

```php
<?php
trait Authenticator {
    private function validateToken(string $token): bool {
        return strlen($token) === 32;
    }

    public function authenticate(string $token): bool {
        return $this->validateToken($token);
    }
}

class API {
    use Authenticator {
        validateToken as public; // Make private method public
    }
}

$api = new API();
$result = $api->validateToken("abc123"); // Now accessible!
```

---

## Traits Using Other Traits

Traits can use other traits:

```php
<?php
trait Timestampable {
    public string $createdAt;

    public function setCreatedAt(): void {
        $this->createdAt = date('Y-m-d H:i:s');
    }
}

trait Loggable {
    public function log(string $message): void {
        echo "[" . date('Y-m-d H:i:s') . "] $message\n";
    }
}

trait Auditable {
    use Timestampable, Loggable; // Trait uses other traits!

    public function auditCreate(): void {
        $this->setCreatedAt();
        $this->log("Created at {$this->createdAt}");
    }
}

class User {
    use Auditable; // Gets Timestampable and Loggable too!

    public function __construct(public string $name) {
        $this->auditCreate();
    }
}

$user = new User("Kieu");
// Output: [2024-01-15 14:30:00] Created at 2024-01-15 14:30:00
```

---

## Traits with Abstract Methods

Traits can require the class to implement certain methods:

```php
<?php
trait Validator {
    // Abstract: class using this trait MUST implement rules()
    abstract public function rules(): array;

    public function validate(): bool {
        $rules = $this->rules(); // Call class's implementation
        $errors = [];

        foreach ($rules as $field => $rule) {
            if (!isset($this->$field)) {
                $errors[] = "$field is required";
            }
        }

        return empty($errors);
    }
}

class User {
    use Validator;

    public string $name;
    public string $email;

    // Must implement because Validator requires it
    public function rules(): array {
        return [
            'name' => 'required',
            'email' => 'required|email'
        ];
    }
}

$user = new User();
$user->name = "Kieu";
$user->email = "kieu@example.com";

if ($user->validate()) {
    echo "Valid!";
}
```

---

## Practical Example: Blog Post Features

```php
<?php
trait Publishable {
    private bool $published = false;
    private ?string $publishedAt = null;

    public function publish(): void {
        $this->published = true;
        $this->publishedAt = date('Y-m-d H:i:s');
        echo "Published at {$this->publishedAt}\n";
    }

    public function unpublish(): void {
        $this->published = false;
        $this->publishedAt = null;
        echo "Unpublished\n";
    }

    public function isPublished(): bool {
        return $this->published;
    }
}

trait Commentable {
    private array $comments = [];

    public function addComment(string $author, string $text): void {
        $this->comments[] = [
            'author' => $author,
            'text' => $text,
            'created_at' => date('Y-m-d H:i:s')
        ];
        echo "Comment added by $author\n";
    }

    public function getComments(): array {
        return $this->comments;
    }

    public function getCommentCount(): int {
        return count($this->comments);
    }
}

trait Likeable {
    private int $likes = 0;

    public function like(): void {
        $this->likes++;
        echo "Liked! Total: {$this->likes}\n";
    }

    public function unlike(): void {
        if ($this->likes > 0) {
            $this->likes--;
        }
        echo "Unliked! Total: {$this->likes}\n";
    }

    public function getLikes(): int {
        return $this->likes;
    }
}

trait Taggable {
    private array $tags = [];

    public function addTag(string $tag): void {
        if (!in_array($tag, $this->tags)) {
            $this->tags[] = $tag;
            echo "Tag added: $tag\n";
        }
    }

    public function removeTag(string $tag): void {
        $this->tags = array_filter($this->tags, fn($t) => $t !== $tag);
        echo "Tag removed: $tag\n";
    }

    public function getTags(): array {
        return $this->tags;
    }
}

class BlogPost {
    use Publishable, Commentable, Likeable, Taggable;

    public function __construct(
        public string $title,
        public string $content,
        public string $author
    ) {}

    public function display(): void {
        echo "\n=== {$this->title} ===\n";
        echo "By: {$this->author}\n";
        echo "Status: " . ($this->isPublished() ? "Published" : "Draft") . "\n";
        echo "Likes: {$this->getLikes()}\n";
        echo "Comments: {$this->getCommentCount()}\n";
        echo "Tags: " . implode(', ', $this->getTags()) . "\n";
        echo "\n{$this->content}\n";
    }
}

// Usage
$post = new BlogPost(
    "Learning PHP Traits",
    "Traits are awesome for code reuse...",
    "Kieu"
);

// Use methods from all traits
$post->publish();
$post->addTag("php");
$post->addTag("oop");
$post->like();
$post->like();
$post->addComment("John", "Great article!");
$post->addComment("Sarah", "Very helpful, thanks!");

$post->display();

/*
Output:
Published at 2024-01-15 14:30:00
Tag added: php
Tag added: oop
Liked! Total: 1
Liked! Total: 2
Comment added by John
Comment added by Sarah

=== Learning PHP Traits ===
By: Kieu
Status: Published
Likes: 2
Comments: 2
Tags: php, oop

Traits are awesome for code reuse...
*/
```

---

## When to Use Traits

### ✅ Good Use Cases

**1. Cross-Cutting Concerns**
```php
<?php
// Features needed by many unrelated classes
trait Timestampable {}
trait Loggable {}
trait Cacheable {}
```

**2. Utility Methods**
```php
<?php
trait StringHelpers {
    public function slugify(string $text): string { /* ... */ }
    public function truncate(string $text, int $length): string { /* ... */ }
}
```

**3. Optional Features**
```php
<?php
// Add feature only to classes that need it
trait Searchable {
    public function search(string $query): array { /* ... */ }
}

class Product {
    use Searchable; // Products are searchable
}

class Category {
    // Categories are not searchable - don't include trait
}
```

**4. Avoid Inheritance Limitations**
```php
<?php
// When single inheritance is too limiting
class User extends Model {
    use Notifiable, Authenticatable, SoftDeletes;
}
```

### ❌ Bad Use Cases

**1. Replace Proper Classes**
```php
<?php
// ❌ Bad: This should be a class
trait User {
    public $name;
    public $email;
    public function save() {}
}

// ✅ Good: Make it a class
class User {
    public $name;
    public $email;
    public function save() {}
}
```

**2. Creating Complex Dependencies**
```php
<?php
// ❌ Bad: Trait requires too much from class
trait DatabaseOperations {
    // Requires: $connection, $table, $primaryKey, etc.
    public function save() {
        // Uses $this->connection...
    }
}

// ✅ Good: Use composition
class Repository {
    public function __construct(private Connection $db) {}
    public function save($model) {}
}
```

**3. Deep Trait Hierarchies**
```php
<?php
// ❌ Bad: Trait using trait using trait...
trait A { use B; }
trait B { use C; }
trait C { use D; }

// ✅ Good: Keep it simple
trait Feature { /* standalone functionality */ }
```

---

## Traits vs Other Approaches

### Trait vs Inheritance

```php
<?php
// Inheritance: IS-A relationship
class Dog extends Animal {} // A Dog IS-AN Animal ✅

// Trait: HAS-A capability
class User {
    use Notifiable; // A User HAS notification capability ✅
}
```

### Trait vs Interface

```php
<?php
// Interface: contract, no implementation
interface Loggable {
    public function log(string $message): void;
}

// Trait: reusable implementation
trait LoggerTrait {
    public function log(string $message): void {
        echo "[LOG] $message\n"; // Actual implementation
    }
}
```

### Trait vs Composition

```php
<?php
// Composition: inject dependency
class User {
    public function __construct(
        private Logger $logger  // Injected
    ) {}
}

// Trait: include code directly
class User {
    use LoggerTrait; // Becomes part of class
}
```

**When to choose what:**
- **Inheritance**: IS-A relationship, related classes
- **Interface**: Define contract, multiple implementations
- **Trait**: Share code across unrelated classes
- **Composition**: Flexible dependencies, testability

---

## Best Practices

### 1. Name Traits as Adjectives/Capabilities

```php
<?php
// ✅ Good: describes capability
trait Timestampable {}
trait Cacheable {}
trait Searchable {}
trait Notifiable {}

// ❌ Bad: noun names
trait Timestamp {}
trait Cache {}
```

### 2. Keep Traits Focused

```php
<?php
// ✅ Good: single responsibility
trait Sluggable {
    public function generateSlug() {}
    public function getSlug() {}
}

// ❌ Bad: too many responsibilities
trait EverythingYouNeed {
    public function generateSlug() {}
    public function sendEmail() {}
    public function validateData() {}
    public function logActivity() {}
}
```

### 3. Document Requirements

```php
<?php
/**
 * Adds slugging capability to models.
 *
 * Requires:
 * - Property: string $name (used to generate slug)
 *
 * Provides:
 * - Property: string $slug
 * - Method: generateSlug()
 */
trait Sluggable {
    public string $slug;

    public function generateSlug(): void {
        $this->slug = strtolower(str_replace(' ', '-', $this->name));
    }
}
```

### 4. Prefer Composition for Complex Logic

```php
<?php
// ❌ Trait with complex dependencies
trait PaymentProcessor {
    public function processPayment() {
        // Complex logic, needs database, external APIs, etc.
    }
}

// ✅ Better: use a service class
class PaymentService {
    public function process() {
        // Complex logic here
    }
}

class Order {
    public function __construct(
        private PaymentService $payment
    ) {}
}
```

---

## Try It Yourself

### Exercise 1: Content Traits

Create traits and use them:
- `Publishable`: publish(), unpublish(), isPublished()
- `Draftable`: saveDraft(), isDraft()
- `Schedulable`: scheduleFor($date), isScheduled()
- Create `BlogPost` and `NewsArticle` classes using these traits

### Exercise 2: User Features

Create:
- `HasAvatar`: setAvatar($url), getAvatar(), hasAvatar()
- `HasBio`: setBio($text), getBio(), hasBio()
- `HasSocialLinks`: addSocialLink($platform, $url), getSocialLinks()
- Create `User` and `Author` classes with different trait combinations

### Exercise 3: Trait Conflict Resolution

Create two traits with conflicting method names, then:
- Resolve using `insteadof`
- Resolve using `as` (rename)
- Resolve using both techniques

---

## Common Mistakes

### 1. Using Traits as Classes

```php
<?php
// ❌ Wrong: treating trait like a class
trait User {
    public $name;
    public function save() {}
}

// ✅ Correct: make it a class
class User {
    public $name;
    public function save() {}
}
```

### 2. Not Resolving Conflicts

```php
<?php
trait A {
    public function method() { echo "A"; }
}
trait B {
    public function method() { echo "B"; }
}

// ❌ Error: must resolve conflict
class MyClass {
    use A, B; // Fatal error
}

// ✅ Resolve it
class MyClass {
    use A, B {
        A::method insteadof B;
    }
}
```

### 3. Overusing Traits

```php
<?php
// ❌ Bad: too many traits makes class hard to understand
class User {
    use TraitA, TraitB, TraitC, TraitD, TraitE, TraitF, TraitG;
}

// ✅ Better: consider if class has too many responsibilities
class User {
    use Timestampable, Authenticatable; // Just essential ones
}
```

---

## Quick Reference

```php
<?php
// Define trait
trait TraitName {
    public $property;

    public function method() {
        // Implementation
    }

    abstract public function requiredMethod();
}

// Use single trait
class MyClass {
    use TraitName;
}

// Use multiple traits
class MyClass {
    use Trait1, Trait2, Trait3;
}

// Resolve conflicts
class MyClass {
    use Trait1, Trait2 {
        Trait1::method insteadof Trait2;  // Choose one
        Trait2::method as method2;         // Rename other
    }
}

// Change visibility
class MyClass {
    use MyTrait {
        privateMethod as public;  // Make public
        publicMethod as private;  // Make private
    }
}

// Trait using trait
trait TraitA {
    use TraitB, TraitC;
}
```

---

## What's Next?

You now know how to reuse code across classes with traits! But as your codebase grows, you'll have many classes and traits. How do you organize them? How do you avoid naming conflicts?

In the next lesson, you'll learn about **namespaces** - a way to organize your code into logical groups and avoid naming collisions.

---

## Key Takeaways

- **Traits** enable horizontal code reuse without inheritance
- Use `trait` keyword to define, `use` keyword to include
- Can use multiple traits in one class
- Trait methods can access class properties
- Resolve conflicts with `insteadof` and `as`
- Can change method visibility with `as`
- Traits can use other traits
- Traits can have abstract methods
- Use for cross-cutting concerns and optional features
- Name as capabilities (adjectives ending in -able)
- Keep traits focused and simple
- Don't overuse - consider composition for complex logic
- Can't instantiate traits directly
