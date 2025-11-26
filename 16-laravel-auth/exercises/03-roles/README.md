# 03 - Roles (Admin vs User)

## Objective
Implement a role-based authorization system to distinguish between admin and regular users with different permissions.

## Prerequisites
- Completed "01-breeze" and "02-policies" exercises
- Understanding of authorization
- Knowledge of database relationships

## Instructions

### Step 1: Extend User Migration
Create a new migration to add role column:

```bash
php artisan make:migration add_role_to_users_table
```

Edit the migration:

```php
Schema::table('users', function (Blueprint $table) {
    $table->enum('role', ['user', 'admin'])->default('user')->after('password');
});
```

Run migration:

```bash
php artisan migrate
```

### Step 2: Update User Model
In `app/Models/User.php`:

```php
class User extends Authenticatable
{
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Check if user is admin
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    // Check if user is regular user
    public function isUser(): bool
    {
        return $this->role === 'user';
    }

    // Set as admin
    public function makeAdmin(): void
    {
        $this->update(['role' => 'admin']);
    }

    // Set as user
    public function makeUser(): void
    {
        $this->update(['role' => 'user']);
    }
}
```

### Step 3: Create Admin Policy
```bash
php artisan make:policy UserPolicy --model=User
```

In `app/Policies/UserPolicy.php`:

```php
namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Determine if user can view users list
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine if user can view a user
     */
    public function view(User $user, User $model): bool
    {
        return $user->isAdmin() || $user->id === $model->id;
    }

    /**
     * Only admin can update users
     */
    public function update(User $user, User $model): bool
    {
        return $user->isAdmin();
    }

    /**
     * Only admin can delete users
     */
    public function delete(User $user, User $model): bool
    {
        return $user->isAdmin();
    }

    /**
     * Only admin can change roles
     */
    public function changeRole(User $user, User $model): bool
    {
        return $user->isAdmin();
    }
}
```

### Step 4: Register User Policy
In `app/Providers/AuthServiceProvider.php`:

```php
protected $policies = [
    Post::class => PostPolicy::class,
    User::class => UserPolicy::class,
];
```

### Step 5: Update Post Policy
Admins should have more permissions. In `app/Policies/PostPolicy.php`:

```php
public function update(User $user, Post $post): bool
{
    // Owner can update
    if ($user->id === $post->user_id) {
        return true;
    }

    // Admin can update any post
    return $user->isAdmin();
}

public function delete(User $user, Post $post): bool
{
    // Owner can delete
    if ($user->id === $post->user_id) {
        return true;
    }

    // Admin can delete any post
    return $user->isAdmin();
}

// Add method to publish posts
public function publish(User $user, Post $post): bool
{
    return $user->isAdmin();
}
```

### Step 6: Create Admin Controller
```bash
php artisan make:controller Admin/UserController --resource
```

In `app/Http/Controllers/Admin/UserController.php`:

```php
namespace App\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Http\Request;

class UserController
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin'); // Create this middleware next
    }

    public function index()
    {
        $this->authorize('viewAny', User::class);

        $users = User::latest()->paginate(20);
        return view('admin.users.index', compact('users'));
    }

    public function show(User $user)
    {
        $this->authorize('view', $user);

        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $this->authorize('update', $user);

        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
        ]);

        $user->update($validated);

        return redirect("/admin/users/{$user->id}")->with('success', 'User updated!');
    }

    public function destroy(User $user)
    {
        $this->authorize('delete', $user);

        $user->delete();

        return redirect('/admin/users')->with('success', 'User deleted!');
    }

    // Change user role
    public function changeRole(Request $request, User $user)
    {
        $this->authorize('changeRole', $user);

        $validated = $request->validate([
            'role' => 'required|in:user,admin',
        ]);

        $user->update($validated);

        return redirect("/admin/users/{$user->id}")
            ->with('success', "User role changed to {$validated['role']}!");
    }
}
```

### Step 7: Create Admin Middleware
```bash
php artisan make:middleware Admin
```

In `app/Http/Middleware/Admin.php`:

```php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Admin
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check() && auth()->user()->isAdmin()) {
            return $next($request);
        }

        abort(403, 'This action is unauthorized.');
    }
}
```

Register in `app/Http/Kernel.php`:

```php
protected $routeMiddleware = [
    // ... existing middleware
    'admin' => \App\Http\Middleware\Admin::class,
];
```

### Step 8: Create Admin Views
Create `resources/views/admin/users/index.blade.php`:

```blade
@extends('layouts.app')

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold mb-6">User Management</h1>

            <table class="w-full border">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-2">Name</th>
                        <th class="px-4 py-2">Email</th>
                        <th class="px-4 py-2">Role</th>
                        <th class="px-4 py-2">Joined</th>
                        <th class="px-4 py-2">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr class="border-b">
                            <td class="px-4 py-2">{{ $user->name }}</td>
                            <td class="px-4 py-2">{{ $user->email }}</td>
                            <td class="px-4 py-2">
                                <span class="px-2 py-1 rounded text-white
                                    {{ $user->isAdmin() ? 'bg-red-500' : 'bg-blue-500' }}">
                                    {{ ucfirst($user->role) }}
                                </span>
                            </td>
                            <td class="px-4 py-2">{{ $user->created_at->format('M d, Y') }}</td>
                            <td class="px-4 py-2">
                                <a href="/admin/users/{{ $user->id }}" class="text-blue-500">View</a>
                                <a href="/admin/users/{{ $user->id }}/edit" class="ml-2 text-yellow-500">Edit</a>

                                {{-- Show role change only for non-admin users --}}
                                @if(!$user->isAdmin())
                                    <form method="POST" action="/admin/users/{{ $user->id }}/role"
                                          style="display:inline;">
                                        @csrf
                                        <input type="hidden" name="role" value="admin">
                                        <button type="submit" class="ml-2 text-green-500"
                                                onclick="return confirm('Make this user admin?')">
                                            Make Admin
                                        </button>
                                    </form>
                                @endif

                                {{-- Show delete only for non-admin --}}
                                @if(!$user->isAdmin())
                                    <form method="POST" action="/admin/users/{{ $user->id }}"
                                          style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="ml-2 text-red-500"
                                                onclick="return confirm('Delete this user?')">
                                            Delete
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-2">No users</td></tr>
                    @endforelse
                </tbody>
            </table>

            {{ $users->links() }}
        </div>
    </div>
@endsection
```

Create `resources/views/admin/users/show.blade.php`:

```blade
@extends('layouts.app')

@section('content')
    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <h1 class="text-2xl font-bold mb-4">{{ $user->name }}</h1>

            <div class="bg-white p-6 rounded shadow">
                <p><strong>Email:</strong> {{ $user->email }}</p>
                <p><strong>Role:</strong> {{ ucfirst($user->role) }}</p>
                <p><strong>Joined:</strong> {{ $user->created_at->format('M d, Y') }}</p>
                <p><strong>Last Updated:</strong> {{ $user->updated_at->format('M d, Y') }}</p>

                <div class="mt-6 flex gap-4">
                    <a href="/admin/users" class="px-4 py-2 bg-gray-500 text-white rounded">Back</a>
                    <a href="/admin/users/{{ $user->id }}/edit" class="px-4 py-2 bg-yellow-500 text-white rounded">
                        Edit
                    </a>

                    {{-- Role change form --}}
                    @if(!$user->isAdmin())
                        <form method="POST" action="/admin/users/{{ $user->id }}/role"
                              style="display:inline;">
                            @csrf
                            <input type="hidden" name="role" value="admin">
                            <button type="submit" class="px-4 py-2 bg-green-500 text-white rounded"
                                    onclick="return confirm('Make admin?')">
                                Make Admin
                            </button>
                        </form>
                    @else
                        <form method="POST" action="/admin/users/{{ $user->id }}/role"
                              style="display:inline;">
                            @csrf
                            <input type="hidden" name="role" value="user">
                            <button type="submit" class="px-4 py-2 bg-orange-500 text-white rounded"
                                    onclick="return confirm('Revoke admin?')">
                                Revoke Admin
                            </button>
                        </form>
                    @endif

                    {{-- Delete user --}}
                    @if(!$user->isAdmin())
                        <form method="POST" action="/admin/users/{{ $user->id }}"
                              style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-4 py-2 bg-red-500 text-white rounded"
                                    onclick="return confirm('Delete this user?')">
                                Delete
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
```

### Step 9: Add Admin Routes
In `routes/web.php`:

```php
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::resource('users', Admin\UserController::class);
    Route::post('/users/{user}/role', [Admin\UserController::class, 'changeRole']);
});
```

### Step 10: Update Navigation
Add admin links to `resources/views/layouts/app.blade.php`:

```blade
@auth
    @if(auth()->user()->isAdmin())
        <a href="/admin/users" class="ml-4 text-blue-500">User Management</a>
    @endif
@endauth
```

### Step 11: Seed Initial Admin
Create a seeder or use Tinker:

```bash
php artisan tinker

$user = User::create([
    'name' => 'Admin User',
    'email' => 'admin@example.com',
    'password' => Hash::make('password'),
    'role' => 'admin',
]);
```

Or in a seeder:

```php
User::create([
    'name' => 'Admin',
    'email' => 'admin@example.com',
    'password' => Hash::make('admin123'),
    'role' => 'admin',
]);
```

### Step 12: Test Role-Based Access
1. Create admin account
2. Create regular user account
3. As admin: Access `/admin/users` - should work
4. As regular user: Try `/admin/users` - should see 403
5. Admin should see "Make Admin" button for regular users
6. Admin should be able to view all posts
7. Admin should be able to edit/delete any post

## Deliverables
- [ ] Role column added to users table
- [ ] User model has isAdmin() and isUser() methods
- [ ] UserPolicy created with admin checks
- [ ] Admin middleware created and registered
- [ ] User management controller created
- [ ] Admin views for user management
- [ ] Routes protected with admin middleware
- [ ] Admin can view all users
- [ ] Admin can change user roles
- [ ] Only admin can access admin panel
- [ ] Role-based permissions tested

## Resources
- [Authorization](https://laravel.com/docs/11.x/authorization)
- [Middleware](https://laravel.com/docs/11.x/middleware)
- [Policies](https://laravel.com/docs/11.x/authorization#policies)

## Tips
- Use custom middleware for role checks
- Store roles in database for flexibility
- Use policies for model-level authorization
- Use middleware for route-level authorization
- Always check authorization in both controllers and views
- Consider using a package like Spatie for complex role systems
