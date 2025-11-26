# Lesson 07 - Mass Assignment and Fillable

## The Mass Assignment Vulnerability

In Module 06 with PDO, you had complete control over what data was inserted:

```php
// PDO: You explicitly specify every field
$stmt = $pdo->prepare("INSERT INTO users (name, email) VALUES (?, ?)");
$stmt->execute([$_POST['name'], $_POST['email']]);
```

With Eloquent, you can create records from arrays:

```php
// Eloquent: Create from array
User::create($_POST);
```

**But this is dangerous!** What if a malicious user adds extra fields to the form?

```html
<!-- Normal form -->
<input name="name" value="John">
<input name="email" value="john@example.com">

<!-- Malicious user adds this via browser DevTools -->
<input name="is_admin" value="1">
```

```php
// Now this happens:
User::create($_POST);
// Creates user with is_admin = 1!
```

**The attacker just made themselves an admin!** This is a **mass assignment vulnerability**.

---

## How Laravel Protects You

Laravel **requires** you to explicitly define which fields can be mass-assigned. If you try mass assignment without protection, you'll get:

```
Illuminate\Database\Eloquent\MassAssignmentException:
Add [is_admin] to fillable property to allow mass assignment on [App\Models\User].
```

**This is a feature, not a bug!** Laravel forces you to be intentional about security.

---

## The $fillable Property (Whitelist)

The **whitelist approach**: Specify which fields **can** be mass-assigned.

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $fillable = [
        'name',
        'email',
        'password',
    ];
}
```

**Now:**

```php
// These fields work
User::create([
    'name' => 'John',
    'email' => 'john@example.com',
    'password' => 'secret'
]);

// This field is ignored (not in $fillable)
User::create([
    'name' => 'John',
    'email' => 'john@example.com',
    'is_admin' => 1  // IGNORED! Not fillable
]);
```

**Recommendation: Use $fillable for most models.** It's explicit and safe.

---

## The $guarded Property (Blacklist)

The **blacklist approach**: Specify which fields **cannot** be mass-assigned. All others are allowed.

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $guarded = [
        'id',
        'is_admin',
        'remember_token',
    ];
}
```

**Now:**

```php
// All fields except guarded ones work
User::create([
    'name' => 'John',
    'email' => 'john@example.com',
    'password' => 'secret'
]);

// Guarded fields are ignored
User::create([
    'name' => 'John',
    'is_admin' => 1  // IGNORED! It's guarded
]);
```

---

## Fillable vs Guarded: Which to Use?

| Aspect | $fillable (Whitelist) | $guarded (Blacklist) |
|--------|----------------------|---------------------|
| **Security** | More secure | Less secure |
| **Use when** | You know exactly which fields should be fillable | You have many fillable fields |
| **Default** | Nothing is fillable | Everything is fillable except guarded |
| **Maintenance** | Update when adding fields | Update when adding sensitive fields |
| **Recommended** | ✅ Yes, for most cases | ⚠️ Use carefully |

**Best practice: Use $fillable unless you have a very good reason not to.**

---

## Disable Mass Assignment Protection (Dangerous!)

```php
class User extends Model
{
    protected $guarded = [];  // Allow everything
}
```

**Only do this if:**
- You're prototyping and trust all input
- You have thorough validation before creating models
- You know exactly what you're doing

**Never do this in production with user input!**

---

## Comparing PDO and Eloquent

### PDO: Manual Security

```php
// PDO: You control every field
$allowedFields = ['name', 'email', 'password'];
$data = array_intersect_key($_POST, array_flip($allowedFields));

$stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
$stmt->execute(array_values($data));
```

**You had to:**
- Manually filter input
- Build query string
- Extract values in correct order

### Eloquent: Built-in Protection

```php
// Eloquent: Protection built into model
class User extends Model
{
    protected $fillable = ['name', 'email', 'password'];
}

// Now safely create from user input
User::create($request->validated());
```

**Laravel handles:**
- Filtering non-fillable fields
- Building INSERT query
- Binding values safely

---

## Mass Assignment in Action

### Creating Records

```php
class User extends Model
{
    protected $fillable = ['name', 'email', 'password'];
}

// Method 1: create()
$user = User::create([
    'name' => $request->name,
    'email' => $request->email,
    'password' => Hash::make($request->password)
]);

// Method 2: new + fill() + save()
$user = new User();
$user->fill([
    'name' => $request->name,
    'email' => $request->email,
]);
$user->password = Hash::make($request->password);
$user->save();

// Method 3: new + assign + save() (not mass assignment)
$user = new User();
$user->name = $request->name;  // Not protected by $fillable
$user->email = $request->email;
$user->is_admin = 1;  // This WORKS because we're setting directly!
$user->save();
```

**Important:** Direct assignment (`$user->name = 'John'`) bypasses mass assignment protection!

### Updating Records

```php
$user = User::find(1);

// Method 1: update() - mass assignment protected
$user->update([
    'name' => 'New Name',
    'email' => 'new@example.com',
    'is_admin' => 1  // IGNORED if not fillable
]);

// Method 2: Direct assignment - NOT protected
$user->name = 'New Name';
$user->is_admin = 1;  // This WORKS!
$user->save();

// Method 3: fill() then save() - mass assignment protected
$user->fill([
    'name' => 'New Name',
    'is_admin' => 1  // IGNORED if not fillable
]);
$user->save();
```

---

## Real-World Examples

### User Registration

```php
class User extends Model
{
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];
}

class RegisterController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
        ]);

        // Safe: Only validated fields are used
        // And only fillable fields are assigned
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('dashboard');
    }
}
```

**Why this is safe:**
1. Validation ensures data is correct format
2. Only validated fields are passed
3. Only fillable fields are assigned
4. Password is hashed before saving

**Even if attacker sends:**
```json
{
  "name": "John",
  "email": "john@example.com",
  "password": "secret123",
  "password_confirmation": "secret123",
  "is_admin": 1
}
```

**The `is_admin` is ignored because:**
1. Not in validation rules
2. Not in $fillable

### Blog Post Creation

```php
class Post extends Model
{
    protected $fillable = [
        'title',
        'content',
        'category_id',
        'status',
    ];

    protected $guarded = [];  // Or use this instead of $fillable
}

class PostController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|max:255',
            'content' => 'required',
            'category_id' => 'required|exists:categories,id',
            'status' => 'in:draft,published',
        ]);

        // Add user_id (not from user input!)
        $post = Post::create([
            ...$validated,
            'user_id' => auth()->id(),
        ]);

        return redirect()->route('posts.show', $post);
    }
}
```

**Notice:** `user_id` is **not fillable** (users shouldn't set it). We add it manually from `auth()->id()`.

### Product Update

```php
class Product extends Model
{
    protected $fillable = [
        'name',
        'description',
        'price',
        'category_id',
    ];

    // Not fillable - controlled by system
    // - stock (managed separately)
    // - sales_count (managed separately)
    // - slug (auto-generated)
}

class ProductController extends Controller
{
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|max:255',
            'description' => 'nullable',
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
        ]);

        // Safe mass assignment
        $product->update($validated);

        // Update non-fillable fields directly if needed
        $product->slug = Str::slug($validated['name']);
        $product->save();

        return redirect()->route('products.show', $product);
    }
}
```

---

## Force Mass Assignment (Bypass Protection)

Sometimes you need to bypass protection temporarily:

```php
// Method 1: unguard() and reguard()
User::unguard();

$user = User::create([
    'name' => 'John',
    'email' => 'john@example.com',
    'is_admin' => 1  // Now allowed
]);

User::reguard();

// Method 2: forceCreate()
$user = User::forceCreate([
    'name' => 'John',
    'email' => 'john@example.com',
    'is_admin' => 1  // Allowed
]);

// Method 3: forceFill()
$user = new User();
$user->forceFill([
    'name' => 'John',
    'is_admin' => 1  // Allowed
])->save();
```

**When to use:**
- Seeding database
- Admin panel where you trust the source
- Importing data from trusted source

**Never use with user input!**

---

## Validation + Mass Assignment = Security

The best approach combines validation and mass assignment protection:

```php
public function store(Request $request)
{
    // Step 1: Validate input (format, rules)
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users',
    ]);

    // Step 2: Mass assignment (field filtering)
    $user = User::create($validated);

    // Even if $validated somehow had is_admin,
    // it would be ignored because not in $fillable

    return $user;
}
```

**Two layers of security:**
1. **Validation**: Ensures data is in correct format
2. **Fillable**: Ensures only allowed fields are assigned

---

## Common Patterns

### Pattern 1: Validated Data Only

```php
// Best practice
$validated = $request->validated();
$model = Model::create($validated);
```

### Pattern 2: Add System Fields

```php
$validated = $request->validated();

$post = Post::create([
    ...$validated,
    'user_id' => auth()->id(),
    'ip_address' => $request->ip(),
    'published_at' => now(),
]);
```

### Pattern 3: Selective Updates

```php
$user = User::find(1);

// Update only specific fields
$user->update($request->only(['name', 'email']));
```

### Pattern 4: Conditional Fillable

```php
class User extends Model
{
    protected $fillable = ['name', 'email'];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        // Admins can mass-assign role
        if (auth()->check() && auth()->user()->isAdmin()) {
            $this->fillable[] = 'role';
        }
    }
}
```

---

## API Security

Mass assignment is especially important for APIs:

```php
class UserController extends Controller
{
    public function update(Request $request, User $user)
    {
        // Danger: User could update any field
        $user->update($request->all());  // DON'T DO THIS!

        // Safe: Only validated fields
        $validated = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email',
        ]);

        $user->update($validated);  // SAFE

        return response()->json($user);
    }
}
```

**API attackers will send:**
```json
{
  "name": "John",
  "email": "john@example.com",
  "is_admin": true,
  "balance": 1000000
}
```

**Without proper protection, they could:**
- Make themselves admin
- Change their balance
- Modify system fields

---

## Testing Mass Assignment Protection

```php
use Tests\TestCase;
use App\Models\User;

class UserMassAssignmentTest extends TestCase
{
    /** @test */
    public function it_protects_is_admin_field()
    {
        $user = User::create([
            'name' => 'John',
            'email' => 'john@example.com',
            'password' => 'secret',
            'is_admin' => 1  // Try to set is_admin
        ]);

        // is_admin should NOT be set
        $this->assertFalse($user->is_admin);
    }

    /** @test */
    public function it_allows_fillable_fields()
    {
        $user = User::create([
            'name' => 'John',
            'email' => 'john@example.com',
            'password' => 'secret',
        ]);

        $this->assertEquals('John', $user->name);
        $this->assertEquals('john@example.com', $user->email);
    }
}
```

---

## Practice Exercises

### Exercise 1: Secure User Model

Create a `User` model with proper mass assignment protection:

1. Fillable: name, email, password, phone
2. Guarded/Hidden: is_admin, remember_token, balance
3. Create registration endpoint that safely handles user input
4. Test that is_admin cannot be mass assigned
5. Create admin endpoint that CAN set is_admin (using direct assignment)

### Exercise 2: Product Management

Create `Product` model with:

1. Fillable: name, description, price, category_id
2. Not fillable: slug, stock, sales_count, rating
3. Create controller that:
   - Validates input
   - Creates product from validated data
   - Auto-generates slug from name
   - Sets initial stock to 0
4. Test mass assignment protection

### Exercise 3: Multi-role System

Create:

1. `User` model with roles
2. Admin can update user roles
3. Users cannot update their own roles
4. Implement conditional fillable based on authenticated user's role

---

## Common Mistakes

### 1. Forgetting to Define Fillable

```php
// Wrong - no protection defined
class Post extends Model
{
    // $fillable not defined
}

Post::create($request->all());  // MassAssignmentException
```

### 2. Using $request->all() Without Validation

```php
// DANGEROUS!
public function store(Request $request)
{
    User::create($request->all());  // Don't trust all input!
}

// SAFE
public function store(Request $request)
{
    $validated = $request->validated();
    User::create($validated);
}
```

### 3. Making Everything Fillable

```php
// Bad - too permissive
class User extends Model
{
    protected $guarded = [];
}
```

### 4. Confusing Direct Assignment with Mass Assignment

```php
// This bypasses $fillable!
$user = new User();
$user->is_admin = 1;  // Works even if not fillable
$user->save();

// This respects $fillable
$user = User::create(['is_admin' => 1]);  // Ignored if not fillable
```

---

## Quick Reference

```php
// Fillable (whitelist)
protected $fillable = ['name', 'email', 'password'];

// Guarded (blacklist)
protected $guarded = ['id', 'is_admin', 'remember_token'];

// Allow all (dangerous)
protected $guarded = [];

// Mass assignment methods
Model::create($array);
$model->fill($array);
$model->update($array);

// Bypass protection
Model::forceCreate($array);
$model->forceFill($array);
Model::unguard();
Model::reguard();

// Safe pattern
$validated = $request->validated();
Model::create($validated);
```

---

## What's Next?

You now understand how to protect your models from mass assignment vulnerabilities! There's one more powerful Eloquent feature to learn.

**In the next lesson**, you'll learn about **Soft Deletes**:
- Delete records without actually deleting them
- Restore deleted records
- Query with and without deleted records
- Implement a "trash bin" feature
- Real-world use cases

Soft deletes are essential for applications where you need to recover data!

---

## Key Takeaways

1. **Mass assignment = security risk** - Attackers can modify any field
2. **Laravel forces protection** - MassAssignmentException if not configured
3. **Use $fillable (whitelist)** - More secure, explicitly define allowed fields
4. **$guarded (blacklist)** - Less secure, use only if needed
5. **Validate + fillable = security** - Two layers of protection
6. **Direct assignment bypasses** - `$model->field = value` ignores $fillable
7. **Never `$request->all()` without validation** - Always validate first
8. **PDO required manual filtering** - Eloquent has it built-in

---

**Next Lesson:** [08 - Soft Deletes](./08-soft-deletes.md)
