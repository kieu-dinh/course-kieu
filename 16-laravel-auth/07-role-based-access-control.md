# Lesson 07 - Role-Based Access Control (RBAC)

**Duration**: 90 minutes
**Objectives**: Build a complete role and permission system, implement multi-level authorization, understand RBAC patterns

---

## What is Role-Based Access Control?

Up until now, we've been checking simple conditions:
- "Is this user the post owner?"
- "Is this user an admin?"

But real applications often need more sophisticated permission systems:
- **Admins** can do everything
- **Moderators** can edit/delete any content
- **Premium Users** can create unlimited posts
- **Regular Users** can only manage their own content
- **Guests** can only view published content

**Role-Based Access Control (RBAC)** is a pattern where:
1. Users are assigned **roles** (Admin, Moderator, User)
2. Roles have **permissions** (create-post, delete-post, ban-user)
3. Authorization checks are based on roles and permissions

---

## Approach 1: Simple Role Column (Quick Start)

The simplest approach is adding a `role` column to the `users` table.

### Database Setup

**Migration:**

```bash
php artisan make:migration add_role_to_users_table
```

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('user')->after('email');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
```

```bash
php artisan migrate
```

### User Model

Add accessor methods for convenience:

```php
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    // Check if user is admin
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    // Check if user is moderator
    public function isModerator(): bool
    {
        return $this->role === 'moderator';
    }

    // Check if user has specific role
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    // Check if user has any of the given roles
    public function hasAnyRole(array $roles): bool
    {
        return in_array($this->role, $roles);
    }
}
```

### Gates Based on Roles

Define Gates in `App\Providers\AppServiceProvider`:

```php
<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Admins can do everything
        Gate::before(function (User $user, string $ability) {
            if ($user->isAdmin()) {
                return true;
            }
        });

        // View admin panel
        Gate::define('view-admin-panel', function (User $user) {
            return $user->hasAnyRole(['admin', 'moderator']);
        });

        // Manage users
        Gate::define('manage-users', function (User $user) {
            return $user->isAdmin();
        });

        // Moderate content
        Gate::define('moderate-content', function (User $user) {
            return $user->hasAnyRole(['admin', 'moderator']);
        });

        // Create posts
        Gate::define('create-post', function (User $user) {
            return $user->email_verified_at !== null;
        });
    }
}
```

### Policies Based on Roles

Update `PostPolicy`:

```php
<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;

class PostPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Post $post): bool
    {
        if ($post->is_published) {
            return true;
        }

        return $user && $user->id === $post->user_id;
    }

    public function create(User $user): bool
    {
        return $user->email_verified_at !== null;
    }

    public function update(User $user, Post $post): bool
    {
        // Owner can edit their own posts
        if ($user->id === $post->user_id) {
            return true;
        }

        // Moderators and admins can edit any post
        return $user->hasAnyRole(['admin', 'moderator']);
    }

    public function delete(User $user, Post $post): bool
    {
        // Owner can delete their own posts
        if ($user->id === $post->user_id) {
            return true;
        }

        // Moderators and admins can delete any post
        return $user->hasAnyRole(['admin', 'moderator']);
    }

    public function publish(User $user, Post $post): bool
    {
        // Owner can publish their own posts
        if ($user->id === $post->user_id) {
            return true;
        }

        // Moderators and admins can publish any post
        return $user->hasAnyRole(['admin', 'moderator']);
    }
}
```

### Middleware

Create role-checking middleware:

```bash
php artisan make:middleware CheckRole
```

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        if (!auth()->user()->hasAnyRole($roles)) {
            abort(403, 'You do not have permission to access this page.');
        }

        return $next($request);
    }
}
```

**Register in `bootstrap/app.php`:**

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'auth' => \App\Http\Middleware\Authenticate::class,
        'role' => \App\Http\Middleware\CheckRole::class,
    ]);
})
```

**Usage:**

```php
// Single role
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin', [AdminController::class, 'index']);
});

// Multiple roles (any of them)
Route::middleware(['auth', 'role:admin,moderator'])->group(function () {
    Route::get('/moderate', [ModerateController::class, 'index']);
});
```

### Blade Directives

```blade
@if(auth()->user()->isAdmin())
    <a href="{{ route('admin.dashboard') }}">Admin Dashboard</a>
@endif

@if(auth()->user()->hasAnyRole(['admin', 'moderator']))
    <a href="{{ route('moderate.posts') }}">Moderate Posts</a>
@endif
```

**Create custom Blade directives:**

In `App\Providers\AppServiceProvider`:

```php
use Illuminate\Support\Facades\Blade;

public function boot(): void
{
    Blade::if('admin', function () {
        return auth()->check() && auth()->user()->isAdmin();
    });

    Blade::if('moderator', function () {
        return auth()->check() && auth()->user()->isModerator();
    });

    Blade::if('role', function (string ...$roles) {
        return auth()->check() && auth()->user()->hasAnyRole($roles);
    });
}
```

**Usage:**

```blade
@admin
    <a href="{{ route('admin.dashboard') }}">Admin Dashboard</a>
@endadmin

@moderator
    <a href="{{ route('moderate.posts') }}">Moderate Posts</a>
@endmoderator

@role('admin', 'moderator')
    <a href="{{ route('moderate.posts') }}">Moderate</a>
@endrole
```

---

## Approach 2: Roles and Permissions Tables (Flexible)

For more complex applications, use separate tables for roles and permissions.

### Database Structure

```
users
  - id
  - name
  - email
  - ...

roles
  - id
  - name (admin, moderator, user)
  - description

permissions
  - id
  - name (create-post, delete-post, ban-user)
  - description

role_user (pivot)
  - role_id
  - user_id

permission_role (pivot)
  - permission_id
  - role_id
```

### Migrations

**Roles table:**

```bash
php artisan make:migration create_roles_table
```

```php
public function up(): void
{
    Schema::create('roles', function (Blueprint $table) {
        $table->id();
        $table->string('name')->unique();
        $table->string('description')->nullable();
        $table->timestamps();
    });
}
```

**Permissions table:**

```bash
php artisan make:migration create_permissions_table
```

```php
public function up(): void
{
    Schema::create('permissions', function (Blueprint $table) {
        $table->id();
        $table->string('name')->unique();
        $table->string('description')->nullable();
        $table->timestamps();
    });
}
```

**Pivot tables:**

```bash
php artisan make:migration create_role_user_table
php artisan make:migration create_permission_role_table
```

```php
// role_user
public function up(): void
{
    Schema::create('role_user', function (Blueprint $table) {
        $table->id();
        $table->foreignId('role_id')->constrained()->onDelete('cascade');
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->timestamps();
    });
}

// permission_role
public function up(): void
{
    Schema::create('permission_role', function (Blueprint $table) {
        $table->id();
        $table->foreignId('permission_id')->constrained()->onDelete('cascade');
        $table->foreignId('role_id')->constrained()->onDelete('cascade');
        $table->timestamps();
    });
}
```

```bash
php artisan migrate
```

### Models

**Role model:**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    protected $fillable = ['name', 'description'];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class);
    }

    public function hasPermission(string $permission): bool
    {
        return $this->permissions()->where('name', $permission)->exists();
    }
}
```

**Permission model:**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    protected $fillable = ['name', 'description'];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }
}
```

**Update User model:**

```php
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    public function hasRole(string $role): bool
    {
        return $this->roles()->where('name', $role)->exists();
    }

    public function hasAnyRole(array $roles): bool
    {
        return $this->roles()->whereIn('name', $roles)->exists();
    }

    public function hasPermission(string $permission): bool
    {
        return $this->roles->flatMap->permissions->contains('name', $permission);
    }

    public function assignRole(string $role): void
    {
        $roleModel = Role::where('name', $role)->firstOrFail();
        $this->roles()->syncWithoutDetaching($roleModel);
    }

    public function removeRole(string $role): void
    {
        $roleModel = Role::where('name', $role)->firstOrFail();
        $this->roles()->detach($roleModel);
    }
}
```

### Seeder

Create roles and permissions:

```bash
php artisan make:seeder RolesAndPermissionsSeeder
```

```php
<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Create permissions
        $permissions = [
            ['name' => 'create-post', 'description' => 'Create posts'],
            ['name' => 'edit-post', 'description' => 'Edit own posts'],
            ['name' => 'edit-any-post', 'description' => 'Edit any post'],
            ['name' => 'delete-post', 'description' => 'Delete own posts'],
            ['name' => 'delete-any-post', 'description' => 'Delete any post'],
            ['name' => 'publish-post', 'description' => 'Publish posts'],
            ['name' => 'manage-users', 'description' => 'Manage users'],
            ['name' => 'view-admin-panel', 'description' => 'View admin panel'],
        ];

        foreach ($permissions as $permission) {
            Permission::create($permission);
        }

        // Create roles and assign permissions
        $adminRole = Role::create([
            'name' => 'admin',
            'description' => 'Administrator with full access',
        ]);
        $adminRole->permissions()->attach(Permission::all());

        $moderatorRole = Role::create([
            'name' => 'moderator',
            'description' => 'Moderator who can manage content',
        ]);
        $moderatorRole->permissions()->attach(
            Permission::whereIn('name', [
                'create-post',
                'edit-post',
                'edit-any-post',
                'delete-any-post',
                'publish-post',
            ])->get()
        );

        $userRole = Role::create([
            'name' => 'user',
            'description' => 'Regular user',
        ]);
        $userRole->permissions()->attach(
            Permission::whereIn('name', [
                'create-post',
                'edit-post',
                'delete-post',
                'publish-post',
            ])->get()
        );
    }
}
```

**Run seeder:**

```bash
php artisan db:seed --class=RolesAndPermissionsSeeder
```

### Gates Based on Permissions

```php
<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Check permissions dynamically
        Gate::before(function (User $user, string $ability) {
            // Convert ability name to permission name
            // Example: 'create-post' ability checks 'create-post' permission
            if ($user->hasPermission($ability)) {
                return true;
            }
        });

        // Or define specific gates
        Gate::define('view-admin-panel', function (User $user) {
            return $user->hasPermission('view-admin-panel');
        });

        Gate::define('manage-users', function (User $user) {
            return $user->hasPermission('manage-users');
        });
    }
}
```

### Usage

**Assign roles to users:**

```php
$user = User::find(1);
$user->assignRole('admin');

$user = User::find(2);
$user->assignRole('moderator');

$user = User::find(3);
$user->assignRole('user');
```

**Check roles:**

```php
if ($user->hasRole('admin')) {
    // User is admin
}

if ($user->hasAnyRole(['admin', 'moderator'])) {
    // User is admin or moderator
}
```

**Check permissions:**

```php
if ($user->hasPermission('edit-any-post')) {
    // User can edit any post
}

if (Gate::allows('manage-users')) {
    // Current user can manage users
}
```

**In controllers:**

```php
public function index()
{
    $this->authorize('view-admin-panel');

    return view('admin.dashboard');
}
```

**In Blade:**

```blade
@can('view-admin-panel')
    <a href="{{ route('admin.dashboard') }}">Admin Panel</a>
@endcan

@can('manage-users')
    <a href="{{ route('admin.users') }}">Manage Users</a>
@endcan
```

---

## Approach 3: Using Laravel Packages (Production-Ready)

For real projects, consider using a battle-tested package:

### Laravel Permission (by Spatie)

**Most popular RBAC package for Laravel.**

**Installation:**

```bash
composer require spatie/laravel-permission
```

**Publish config and migrations:**

```bash
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan migrate
```

**Usage:**

```php
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

// Create roles
$role = Role::create(['name' => 'admin']);
$role = Role::create(['name' => 'moderator']);

// Create permissions
$permission = Permission::create(['name' => 'edit posts']);
$permission = Permission::create(['name' => 'delete posts']);

// Assign permissions to roles
$role->givePermissionTo('edit posts');
$role->givePermissionTo('delete posts');

// Assign roles to users
$user->assignRole('admin');

// Check permissions
if ($user->hasPermissionTo('edit posts')) {
    // User can edit posts
}

if ($user->hasRole('admin')) {
    // User is admin
}

// In Blade
@role('admin')
    <p>Admin content</p>
@endrole

@hasrole('admin')
    <p>Admin content</p>
@endhasrole

@can('edit posts')
    <p>Can edit posts</p>
@endcan
```

**Benefits:**
- Battle-tested
- Caching for performance
- Multiple roles per user
- Direct permissions (bypass roles)
- Middleware included
- Great documentation

---

## Building an Admin Panel

Let's create a simple admin panel to manage roles and permissions.

### Routes

```php
Route::middleware(['auth', 'can:view-admin-panel'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');

    Route::middleware('can:manage-users')->group(function () {
        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::get('/users/{user}', [AdminUserController::class, 'show'])->name('users.show');
        Route::put('/users/{user}/role', [AdminUserController::class, 'updateRole'])->name('users.updateRole');
        Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');
    });
});
```

### AdminUserController

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    public function index()
    {
        $users = User::with('roles')->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    public function show(User $user)
    {
        $user->load('roles.permissions');

        return view('admin.users.show', compact('user'));
    }

    public function updateRole(Request $request, User $user)
    {
        $request->validate([
            'role' => 'required|exists:roles,name',
        ]);

        $user->roles()->sync([]);
        $user->assignRole($request->role);

        return redirect()->route('admin.users.show', $user)
            ->with('success', 'User role updated successfully.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete yourself.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }
}
```

### View (admin/users/index.blade.php)

```blade
<x-app-layout>
    <h1>Manage Users</h1>

    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
                <tr>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>
                        @foreach($user->roles as $role)
                            <span class="badge">{{ $role->name }}</span>
                        @endforeach
                    </td>
                    <td>
                        <a href="{{ route('admin.users.show', $user) }}">View</a>

                        @if($user->id !== auth()->id())
                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit">Delete</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{ $users->links() }}
</x-app-layout>
```

---

## Best Practices

1. **Start simple, add complexity as needed:**
   - Start with a simple `role` column
   - Move to roles/permissions tables when needed
   - Consider packages for complex systems

2. **Use Gates for global checks:**
   - `view-admin-panel`
   - `bypass-rate-limits`
   - `access-beta-features`

3. **Use Policies for model-based checks:**
   - Can user edit THIS post?
   - Can user delete THIS comment?

4. **Cache permissions:**
   - Permissions don't change often
   - Cache them to avoid extra queries

5. **Don't over-engineer:**
   - Not every app needs complex RBAC
   - Simple `is_admin` column is often enough

6. **Test thoroughly:**
   - Test each role can do what they should
   - Test each role CANNOT do what they shouldn't

---

## Quick Quiz

1. **What's the simplest way to implement roles?**
   - Single `role` column on users table

2. **When should you use separate roles/permissions tables?**
   - When you need flexible, dynamic permission assignment
   - When users can have multiple roles

3. **What package is recommended for RBAC?**
   - Spatie's Laravel Permission

4. **How do you check if a user has a role?**
   - `$user->hasRole('admin')`
   - `$user->hasAnyRole(['admin', 'moderator'])`

5. **How do you protect admin routes?**
   - `Route::middleware('can:view-admin-panel')`
   - `Route::middleware('role:admin')`

---

## What's Next?

In **Lesson 08 - API Authentication with Sanctum**, we'll learn:
- What is Laravel Sanctum?
- Token-based authentication
- SPA authentication
- Mobile app authentication
- Protecting API routes

---

## Key Takeaways

1. **RBAC assigns permissions based on roles**
2. **Three approaches:**
   - Simple `role` column (quick start)
   - Roles/permissions tables (flexible)
   - Packages like Spatie Permission (production)
3. **Use Gates for global checks, Policies for model checks**
4. **Start simple, add complexity as needed**
5. **Test thoroughly**

You now know how to implement complete role-based access control in Laravel!
