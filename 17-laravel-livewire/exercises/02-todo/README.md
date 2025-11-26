# 02 - Todo App with Livewire

## Objective
Build a complete todo application using Livewire with database persistence and advanced features.

## Prerequisites
- Completed "01-component" exercise
- Understanding of Livewire basics
- Knowledge of Eloquent ORM

## Instructions

### Step 1: Create Todo Model and Migration
```bash
php artisan make:model Todo -m
```

Edit migration:

```php
Schema::create('todos', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->text('description')->nullable();
    $table->boolean('completed')->default(false);
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->timestamps();
});
```

Run migration:

```bash
php artisan migrate
```

### Step 2: Define Todo Model
In `app/Models/Todo.php`:

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Todo extends Model
{
    protected $fillable = ['title', 'description', 'completed', 'user_id'];
    protected $casts = ['completed' => 'boolean'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeIncomplete($query)
    {
        return $query->where('completed', false);
    }

    public function scopeCompleted($query)
    {
        return $query->where('completed', true);
    }
}
```

Add relationship to User:

```php
public function todos(): HasMany
{
    return $this->hasMany(Todo::class);
}
```

### Step 3: Create Todo Livewire Component
```bash
php artisan make:livewire TodoApp
```

In `app/Livewire/TodoApp.php`:

```php
namespace App\Livewire;

use App\Models\Todo;
use Livewire\Component;
use Livewire\Attributes\Validate;

class TodoApp extends Component
{
    #[Validate('required|string|min:3|max:255')]
    public string $title = '';

    #[Validate('nullable|string|max:1000')]
    public string $description = '';

    public string $filter = 'all'; // all, active, completed
    public ?int $editingId = null;

    public function mount(): void
    {
        // Check user is authenticated
        if (!auth()->check()) {
            abort(401);
        }
    }

    public function addTodo(): void
    {
        $this->validate();

        auth()->user()->todos()->create([
            'title' => $this->title,
            'description' => $this->description,
        ]);

        $this->reset('title', 'description');
        $this->dispatch('todo-added');
    }

    public function toggleTodo(Todo $todo): void
    {
        $this->authorize('update', $todo);

        $todo->update(['completed' => !$todo->completed]);
        $this->dispatch('todo-toggled');
    }

    public function deleteTodo(Todo $todo): void
    {
        $this->authorize('delete', $todo);

        $todo->delete();
        $this->dispatch('todo-deleted');
    }

    public function editTodo(Todo $todo): void
    {
        $this->authorize('update', $todo);

        $this->editingId = $todo->id;
        $this->title = $todo->title;
        $this->description = $todo->description;
    }

    public function updateTodo(Todo $todo): void
    {
        $this->authorize('update', $todo);

        $this->validate();

        $todo->update([
            'title' => $this->title,
            'description' => $this->description,
        ]);

        $this->cancelEdit();
        $this->dispatch('todo-updated');
    }

    public function cancelEdit(): void
    {
        $this->reset('editingId', 'title', 'description');
    }

    public function clearCompleted(): void
    {
        auth()->user()
            ->todos()
            ->completed()
            ->delete();

        $this->dispatch('todos-cleared');
    }

    public function getTodosProperty()
    {
        $query = auth()->user()->todos();

        return match($this->filter) {
            'active' => $query->incomplete()->latest()->get(),
            'completed' => $query->completed()->latest()->get(),
            default => $query->latest()->get(),
        };
    }

    public function render()
    {
        return view('livewire.todo-app', [
            'todos' => $this->todos,
            'activeCount' => auth()->user()->todos()->incomplete()->count(),
            'completedCount' => auth()->user()->todos()->completed()->count(),
        ]);
    }
}
```

### Step 4: Create Todo View
In `resources/views/livewire/todo-app.blade.php`:

```blade
<div class="w-full max-w-3xl mx-auto p-6 bg-white rounded-lg shadow-lg">
    <h1 class="text-3xl font-bold mb-6">My Todos</h1>

    {{-- Form to add/edit todo --}}
    <form wire:submit="@if($editingId) updateTodo($editingId) @else addTodo @endif"
          class="mb-6 p-4 bg-gray-50 rounded">

        <div class="mb-4">
            <label for="title" class="block font-bold mb-2">Title *</label>
            <input type="text"
                   id="title"
                   wire:model="title"
                   placeholder="What needs to be done?"
                   class="w-full px-3 py-2 border rounded @error('title') border-red-500 @enderror">
            @error('title')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label for="description" class="block font-bold mb-2">Description</label>
            <textarea id="description"
                      wire:model="description"
                      placeholder="Add more details..."
                      rows="3"
                      class="w-full px-3 py-2 border rounded @error('description') border-red-500 @enderror"></textarea>
            @error('description')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex gap-2">
            @if($editingId)
                <button type="submit" class="px-4 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600">
                    Update Todo
                </button>
                <button type="button"
                        wire:click="cancelEdit"
                        class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">
                    Cancel
                </button>
            @else
                <button type="submit"
                        class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                    Add Todo
                </button>
            @endif
        </div>
    </form>

    {{-- Filter buttons --}}
    <div class="flex gap-2 mb-6">
        <button wire:click="$set('filter', 'all')"
                @class(['px-3 py-1 rounded', 'bg-blue-500 text-white' => $filter === 'all', 'bg-gray-200' => $filter !== 'all'])>
            All ({{ count($todos) }})
        </button>

        <button wire:click="$set('filter', 'active')"
                @class(['px-3 py-1 rounded', 'bg-blue-500 text-white' => $filter === 'active', 'bg-gray-200' => $filter !== 'active'])>
            Active ({{ $activeCount }})
        </button>

        <button wire:click="$set('filter', 'completed')"
                @class(['px-3 py-1 rounded', 'bg-blue-500 text-white' => $filter === 'completed', 'bg-gray-200' => $filter !== 'completed'])>
            Completed ({{ $completedCount }})
        </button>
    </div>

    {{-- Todo list --}}
    @if(count($todos) > 0)
        <ul class="space-y-2 mb-4">
            @foreach($todos as $todo)
                <li class="flex items-start gap-3 p-3 bg-gray-50 rounded hover:bg-gray-100 transition">
                    <input type="checkbox"
                           wire:change="toggleTodo(${{ $todo->id }})"
                           @checked($todo->completed)
                           class="w-5 h-5 mt-0.5 cursor-pointer">

                    <div class="flex-1">
                        <p @class(['font-semibold text-lg', 'line-through text-gray-400' => $todo->completed])>
                            {{ $todo->title }}
                        </p>

                        @if($todo->description)
                            <p class="text-gray-600 text-sm mt-1">
                                {{ $todo->description }}
                            </p>
                        @endif

                        <p class="text-xs text-gray-400 mt-2">
                            {{ $todo->created_at->diffForHumans() }}
                        </p>
                    </div>

                    <div class="flex gap-2">
                        @if(!$todo->completed)
                            <button wire:click="editTodo(${{ $todo->id }})"
                                    class="text-yellow-500 hover:text-yellow-700 font-bold">
                                Edit
                            </button>
                        @endif

                        <button wire:click="deleteTodo(${{ $todo->id }})"
                                onclick="return confirm('Delete this todo?')"
                                class="text-red-500 hover:text-red-700 font-bold">
                            Delete
                        </button>
                    </div>
                </li>
            @endforeach
        </ul>

        {{-- Clear completed button --}}
        @if($completedCount > 0 && $filter !== 'active')
            <button wire:click="clearCompleted"
                    onclick="return confirm('Clear all completed todos?')"
                    class="w-full px-4 py-2 bg-orange-500 text-white rounded hover:bg-orange-600">
                Clear {{ $completedCount }} Completed Todo(s)
            </button>
        @endif
    @else
        <div class="text-center py-12 text-gray-500">
            @if($filter === 'active')
                <p class="text-lg">All tasks completed!</p>
                <p class="text-sm mt-2">Nice work!</p>
            @elseif($filter === 'completed')
                <p class="text-lg">No completed tasks yet</p>
            @else
                <p class="text-lg">No todos yet</p>
                <p class="text-sm mt-2">Add one to get started!</p>
            @endif
        </div>
    @endif

    {{-- Stats --}}
    @if(count($todos) > 0)
        <div class="mt-6 pt-4 border-t text-sm text-gray-600">
            <p>Progress: {{ round(($completedCount / (count($todos))) * 100) }}%</p>
            <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                <div class="bg-green-500 h-2 rounded-full transition-all"
                     style="width: {{ round(($completedCount / (count($todos))) * 100) }}%"></div>
            </div>
        </div>
    @endif
</div>
```

### Step 5: Create Policy
```bash
php artisan make:policy TodoPolicy --model=Todo
```

In `app/Policies/TodoPolicy.php`:

```php
namespace App\Policies;

use App\Models\Todo;
use App\Models\User;

class TodoPolicy
{
    public function view(User $user, Todo $todo): bool
    {
        return $user->id === $todo->user_id;
    }

    public function update(User $user, Todo $todo): bool
    {
        return $user->id === $todo->user_id;
    }

    public function delete(User $user, Todo $todo): bool
    {
        return $user->id === $todo->user_id;
    }
}
```

Register in `AuthServiceProvider`:

```php
protected $policies = [
    Todo::class => TodoPolicy::class,
];
```

### Step 6: Create Route
In `routes/web.php`:

```php
use App\Livewire\TodoApp;

Route::middleware('auth')->group(function () {
    Route::get('/todos', TodoApp::class)->name('todos.index');
});
```

### Step 7: Add Link in Navigation
Update `resources/views/layouts/app.blade.php`:

```blade
@auth
    <a href="/todos" class="ml-4">My Todos</a>
@endauth
```

### Step 8: Test the Application
1. Register/login
2. Go to `/todos`
3. Add a todo with title and description
4. Click checkbox to complete
5. Edit todo by clicking Edit button
6. Delete todos
7. Filter by all/active/completed
8. Check progress bar

## Deliverables
- [ ] Todo model with migrations
- [ ] Livewire component created
- [ ] Add todo functionality working
- [ ] Edit todo functionality working
- [ ] Delete todo with confirmation
- [ ] Toggle completion status
- [ ] Filter by status (all, active, completed)
- [ ] Clear completed todos
- [ ] Progress bar showing completion
- [ ] Validation on form fields
- [ ] Authorization checked
- [ ] Styling with Tailwind CSS
- [ ] Responsive design

## Resources
- [Livewire Components](https://livewire.laravel.com/docs/components)
- [Validation](https://livewire.laravel.com/docs/validation)
- [Database Persistence](https://livewire.laravel.com/docs/properties#reactive-properties)
- [Events](https://livewire.laravel.com/docs/events)

## Tips
- Use #[Validate()] attributes for validation
- Use $this->authorize() in component methods
- Use wire:model for two-way binding
- Use wire:click for method calls
- Keep business logic in component class
- Use dispatch() for component communication
- Test form validation thoroughly
- Consider adding due dates or priority levels
