# 04 - Complete Secure Application

## Objective
Build a complete, production-ready application with comprehensive authentication, authorization, and security best practices.

## Prerequisites
- Completed all Module 16 exercises
- Understanding of Laravel security
- Knowledge of OWASP security principles

## Instructions

### Step 1: Project Planning
Design a project management application with:
- Users with roles (admin, project manager, team member)
- Projects (created by managers, accessed by team)
- Tasks within projects (assigned to team members)
- Comments on tasks
- Activity logging

### Step 2: Create Database Schema
```bash
php artisan make:model Project -m
php artisan make:model Task -m
php artisan make:model TaskComment -m
php artisan make:model ActivityLog -m
```

Edit migrations:

```php
// projects table
Schema::create('projects', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->text('description')->nullable();
    $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
    $table->enum('status', ['active', 'archived'])->default('active');
    $table->timestamps();
});

// project_user (many-to-many)
Schema::create('project_user', function (Blueprint $table) {
    $table->foreignId('project_id')->constrained()->onDelete('cascade');
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->enum('role', ['manager', 'member'])->default('member');
    $table->primary(['project_id', 'user_id']);
});

// tasks table
Schema::create('tasks', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->text('description')->nullable();
    $table->foreignId('project_id')->constrained()->onDelete('cascade');
    $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
    $table->enum('status', ['todo', 'in_progress', 'done'])->default('todo');
    $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
    $table->date('due_date')->nullable();
    $table->timestamps();
});

// task_comments table
Schema::create('task_comments', function (Blueprint $table) {
    $table->id();
    $table->text('content');
    $table->foreignId('task_id')->constrained()->onDelete('cascade');
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->timestamps();
});

// activity_logs table
Schema::create('activity_logs', function (Blueprint $table) {
    $table->id();
    $table->string('action');
    $table->string('model_type');
    $table->unsignedBigInteger('model_id');
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->text('changes')->nullable();
    $table->ip_address('ip_address')->nullable();
    $table->string('user_agent')->nullable();
    $table->timestamps();
});
```

Run migrations:

```bash
php artisan migrate
```

### Step 3: Create Models

In `app/Models/Project.php`:

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    protected $fillable = ['name', 'description', 'created_by', 'status'];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_user')
            ->withPivot('role');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function activeTasks(): HasMany
    {
        return $this->tasks()->where('status', '!=', 'done');
    }

    public function isUserMember(User $user): bool
    {
        return $this->users()->where('user_id', $user->id)->exists();
    }

    public function isUserManager(User $user): bool
    {
        return $this->users()
            ->where('user_id', $user->id)
            ->where('role', 'manager')
            ->exists();
    }
}
```

In `app/Models/Task.php`:

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    protected $fillable = [
        'title', 'description', 'project_id', 'assigned_to',
        'status', 'priority', 'due_date'
    ];

    protected $casts = [
        'due_date' => 'date',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TaskComment::class);
    }

    public function isOverdue(): bool
    {
        return $this->due_date && $this->due_date < now()->date() && $this->status !== 'done';
    }
}
```

In `app/Models/TaskComment.php`:

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskComment extends Model
{
    protected $fillable = ['content', 'task_id', 'user_id'];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
```

### Step 4: Create Comprehensive Policies

```bash
php artisan make:policy ProjectPolicy --model=Project
php artisan make:policy TaskPolicy --model=Task
php artisan make:policy TaskCommentPolicy --model=TaskComment
```

In `app/Policies/ProjectPolicy.php`:

```php
namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    public function viewAny(User $user): bool
    {
        return true; // Any authenticated user can see projects
    }

    public function view(User $user, Project $project): bool
    {
        // Project members can view
        if ($project->isUserMember($user)) {
            return true;
        }

        // Admin can view all
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        // Only non-admin and admin can create
        return true; // Or restrict to managers
    }

    public function update(User $user, Project $project): bool
    {
        // Only creator or manager can update
        if ($project->created_by === $user->id) {
            return true;
        }

        // Project manager can update
        if ($project->isUserManager($user)) {
            return true;
        }

        return $user->isAdmin();
    }

    public function delete(User $user, Project $project): bool
    {
        // Only creator or admin can delete
        if ($project->created_by === $user->id) {
            return true;
        }

        return $user->isAdmin();
    }

    public function addMember(User $user, Project $project): bool
    {
        return $user->isAdmin() || $project->isUserManager($user);
    }

    public function removeMember(User $user, Project $project): bool
    {
        return $user->isAdmin() || $project->isUserManager($user);
    }
}
```

In `app/Policies/TaskPolicy.php`:

```php
namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    public function viewAny(User $user, Project $project): bool
    {
        return $project->isUserMember($user) || $user->isAdmin();
    }

    public function view(User $user, Task $task): bool
    {
        return $task->project->isUserMember($user) || $user->isAdmin();
    }

    public function create(User $user, Project $project): bool
    {
        return $project->isUserManager($user) || $user->isAdmin();
    }

    public function update(User $user, Task $task): bool
    {
        // Assignee or manager can update
        if ($task->assigned_to === $user->id) {
            return true;
        }

        if ($task->project->isUserManager($user)) {
            return true;
        }

        return $user->isAdmin();
    }

    public function delete(User $user, Task $task): bool
    {
        return $task->project->isUserManager($user) || $user->isAdmin();
    }
}
```

In `app/Policies/TaskCommentPolicy.php`:

```php
namespace App\Policies;

use App\Models\TaskComment;
use App\Models\User;

class TaskCommentPolicy
{
    public function create(User $user): bool
    {
        return true; // Any member of project can comment
    }

    public function update(User $user, TaskComment $comment): bool
    {
        // Only comment author can update
        return $user->id === $comment->user_id || $user->isAdmin();
    }

    public function delete(User $user, TaskComment $comment): bool
    {
        return $user->id === $comment->user_id || $user->isAdmin();
    }
}
```

### Step 5: Register Policies
In `app/Providers/AuthServiceProvider.php`:

```php
protected $policies = [
    Project::class => ProjectPolicy::class,
    Task::class => TaskPolicy::class,
    TaskComment::class => TaskCommentPolicy::class,
];
```

### Step 6: Create Activity Logging Trait
Create `app/Traits/LogsActivity.php`:

```php
namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;

trait LogsActivity
{
    public static function bootLogsActivity()
    {
        static::created(function (Model $model) {
            ActivityLog::create([
                'action' => 'created',
                'model_type' => static::class,
                'model_id' => $model->id,
                'user_id' => auth()->id(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        });

        static::updated(function (Model $model) {
            ActivityLog::create([
                'action' => 'updated',
                'model_type' => static::class,
                'model_id' => $model->id,
                'user_id' => auth()->id(),
                'changes' => json_encode($model->getChanges()),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        });

        static::deleted(function (Model $model) {
            ActivityLog::create([
                'action' => 'deleted',
                'model_type' => static::class,
                'model_id' => $model->id,
                'user_id' => auth()->id(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        });
    }
}
```

Use in models:

```php
class Project extends Model
{
    use LogsActivity;
    // ...
}

class Task extends Model
{
    use LogsActivity;
    // ...
}
```

### Step 7: Create Controllers

```bash
php artisan make:controller ProjectController --resource
php artisan make:controller TaskController --resource
php artisan make:controller TaskCommentController
```

In `app/Http/Controllers/ProjectController.php`:

```php
namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        // Show projects user is member of
        $projects = auth()->user()
            ->projects()
            ->with('creator')
            ->latest()
            ->paginate(10);

        // Also show admin can see all
        if (auth()->user()->isAdmin()) {
            $projects = Project::with('creator')->latest()->paginate(10);
        }

        return view('projects.index', compact('projects'));
    }

    public function create()
    {
        return view('projects.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $validated['created_by'] = auth()->id();

        $project = Project::create($validated);

        // Add creator as manager
        $project->users()->attach(auth()->id(), ['role' => 'manager']);

        return redirect("/projects/{$project->id}")->with('success', 'Project created!');
    }

    public function show(Project $project)
    {
        $this->authorize('view', $project);

        $project->load('creator', 'users', 'tasks.assignee');

        return view('projects.show', compact('project'));
    }

    public function edit(Project $project)
    {
        $this->authorize('update', $project);

        return view('projects.edit', compact('project'));
    }

    public function update(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:active,archived',
        ]);

        $project->update($validated);

        return redirect("/projects/{$project->id}")->with('success', 'Project updated!');
    }

    public function destroy(Project $project)
    {
        $this->authorize('delete', $project);

        $project->delete();

        return redirect('/projects')->with('success', 'Project deleted!');
    }

    public function addMember(Request $request, Project $project)
    {
        $this->authorize('addMember', $project);

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|in:manager,member',
        ]);

        $project->users()->attach(
            $validated['user_id'],
            ['role' => $validated['role']]
        );

        return redirect("/projects/{$project->id}")
            ->with('success', 'Member added!');
    }
}
```

In `app/Http/Controllers/TaskController.php`:

```php
namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Project $project)
    {
        $this->authorize('viewAny', [Task::class, $project]);

        $tasks = $project->tasks()
            ->with('assignee')
            ->latest()
            ->paginate(20);

        return view('tasks.index', compact('project', 'tasks'));
    }

    public function create(Project $project)
    {
        $this->authorize('create', [Task::class, $project]);

        $users = $project->users;

        return view('tasks.create', compact('project', 'users'));
    }

    public function store(Request $request, Project $project)
    {
        $this->authorize('create', [Task::class, $project]);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
            'priority' => 'required|in:low,medium,high',
            'due_date' => 'nullable|date|after:today',
        ]);

        $validated['project_id'] = $project->id;

        Task::create($validated);

        return redirect("/projects/{$project->id}")
            ->with('success', 'Task created!');
    }

    public function show(Project $project, Task $task)
    {
        $this->authorize('view', $task);

        $task->load('assignee', 'comments.user');

        return view('tasks.show', compact('project', 'task'));
    }

    public function edit(Project $project, Task $task)
    {
        $this->authorize('update', $task);

        $users = $project->users;

        return view('tasks.edit', compact('project', 'task', 'users'));
    }

    public function update(Request $request, Project $project, Task $task)
    {
        $this->authorize('update', $task);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
            'status' => 'required|in:todo,in_progress,done',
            'priority' => 'required|in:low,medium,high',
            'due_date' => 'nullable|date',
        ]);

        $task->update($validated);

        return redirect("/projects/{$project->id}")
            ->with('success', 'Task updated!');
    }

    public function destroy(Project $project, Task $task)
    {
        $this->authorize('delete', $task);

        $task->delete();

        return redirect("/projects/{$project->id}")
            ->with('success', 'Task deleted!');
    }
}
```

### Step 8: Create Security Headers Middleware
```bash
php artisan make:middleware SecurityHeaders
```

In `app/Http/Middleware/SecurityHeaders.php`:

```php
namespace App\Http\Middleware;

use Closure;

class SecurityHeaders
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        $response->header('X-Content-Type-Options', 'nosniff');
        $response->header('X-Frame-Options', 'DENY');
        $response->header('X-XSS-Protection', '1; mode=block');
        $response->header('Referrer-Policy', 'strict-origin-when-cross-origin');

        return $response;
    }
}
```

Register in `app/Http/Kernel.php`:

```php
protected $middleware = [
    // ...
    \App\Http\Middleware\SecurityHeaders::class,
];
```

### Step 9: Create Views
Create comprehensive views for:
- `resources/views/projects/index.blade.php` - Project listing
- `resources/views/projects/create.blade.php` - Create project
- `resources/views/projects/show.blade.php` - Project details with tasks
- `resources/views/tasks/index.blade.php` - Task listing
- `resources/views/tasks/create.blade.php` - Create task
- `resources/views/tasks/show.blade.php` - Task details with comments

### Step 10: Define Routes
In `routes/web.php`:

```php
Route::middleware('auth')->group(function () {
    Route::resource('projects', ProjectController::class);
    Route::post('/projects/{project}/members', [ProjectController::class, 'addMember']);

    Route::resource('projects.tasks', TaskController::class);

    Route::post('/tasks/{task}/comments', [TaskCommentController::class, 'store']);
    Route::delete('/comments/{comment}', [TaskCommentController::class, 'destroy']);
});
```

### Step 11: Implement Rate Limiting
In `app/Http/Kernel.php`, configure rate limiting:

```php
// Add to API routes
Route::middleware('throttle:60,1')->group(function () {
    // Your routes
});
```

### Step 12: Input Validation
Always validate user input:

```php
$request->validate([
    'title' => 'required|string|max:255|min:3',
    'email' => 'required|email|unique:users',
]);
```

### Step 13: CSRF Protection
All forms automatically include CSRF tokens with Breeze.

### Step 14: Password Security
Passwords are automatically hashed by Breeze's registration.

### Step 15: Testing
Create tests for authorization:

```bash
php artisan make:test ProjectPolicyTest
```

### Step 16: Deployment Checklist
Before deploying:
- [ ] Change APP_DEBUG to false
- [ ] Set APP_KEY if not done
- [ ] Configure database
- [ ] Set up email
- [ ] Enable HTTPS
- [ ] Configure CORS if needed
- [ ] Set up rate limiting
- [ ] Enable security headers
- [ ] Backup database regularly

## Deliverables
- [ ] Database with all tables and relationships
- [ ] All models created with proper relationships
- [ ] Comprehensive policies for all models
- [ ] Controllers implementing full CRUD
- [ ] Views for all operations
- [ ] Activity logging implemented
- [ ] Security middleware implemented
- [ ] Input validation on all forms
- [ ] Authorization checks working
- [ ] Project and task management system functional
- [ ] User management with roles

## Resources
- [Security Best Practices](https://laravel.com/docs/11.x/security)
- [Authorization](https://laravel.com/docs/11.x/authorization)
- [OWASP Guidelines](https://owasp.org)
- [Password Hashing](https://laravel.com/docs/11.x/hashing)

## Tips
- Always validate and sanitize user input
- Use CSRF tokens in all forms
- Hash passwords with bcrypt
- Implement rate limiting
- Log security events
- Use HTTPS in production
- Keep dependencies updated
- Test authorization thoroughly
- Use parameterized queries (Eloquent)
- Never store sensitive data in logs
