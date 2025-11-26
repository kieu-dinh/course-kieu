# Lesson 5: Forms and Validation

**Duration**: 60 minutes
**Prerequisites**: Lesson 4 completed

---

## What You'll Learn

- Building forms with Livewire
- Real-time validation
- Validation attributes
- Custom validation rules
- Error handling and display
- Form objects for complex forms
- File uploads basics

---

## Basic Form Handling

### Simple Create Form

```php
class CreatePost extends Component
{
    public $title = '';
    public $content = '';
    public $published = false;

    public function save()
    {
        // Validation (we'll cover this next)
        $validated = $this->validate([
            'title' => 'required|min:3|max:255',
            'content' => 'required|min:10',
            'published' => 'boolean'
        ]);

        Post::create($validated);

        session()->flash('message', 'Post created successfully!');

        return redirect()->route('posts.index');
    }

    public function render()
    {
        return view('livewire.create-post');
    }
}
```

```html
<div>
    <form wire:submit="save">
        <!-- Title -->
        <div class="mb-4">
            <label class="block mb-2">Title</label>
            <input
                type="text"
                wire:model="title"
                class="w-full px-3 py-2 border rounded @error('title') border-red-500 @enderror"
            >
            @error('title')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <!-- Content -->
        <div class="mb-4">
            <label class="block mb-2">Content</label>
            <textarea
                wire:model="content"
                rows="5"
                class="w-full px-3 py-2 border rounded @error('content') border-red-500 @enderror"
            ></textarea>
            @error('content')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <!-- Published -->
        <div class="mb-4">
            <label class="inline-flex items-center">
                <input type="checkbox" wire:model="published">
                <span class="ml-2">Publish immediately</span>
            </label>
        </div>

        <!-- Submit -->
        <button
            type="submit"
            wire:loading.attr="disabled"
            class="bg-blue-500 text-white px-6 py-2 rounded"
        >
            <span wire:loading.remove wire:target="save">Create Post</span>
            <span wire:loading wire:target="save">Creating...</span>
        </button>
    </form>
</div>
```

---

## Validation Methods

### 1. Inline Validation

Most common approach:

```php
public function save()
{
    $validated = $this->validate([
        'title' => 'required|min:3|max:255',
        'content' => 'required',
    ]);

    Post::create($validated);
}
```

### 2. Validation Attributes (Laravel 10+)

Cleaner and more explicit:

```php
use Livewire\Attributes\Validate;

class CreatePost extends Component
{
    #[Validate('required|min:3|max:255')]
    public $title = '';

    #[Validate('required|min:10')]
    public $content = '';

    #[Validate('boolean')]
    public $published = false;

    public function save()
    {
        $validated = $this->validate();

        Post::create($validated);
    }
}
```

### 3. Rules Method

For complex or dynamic rules:

```php
class UpdatePost extends Component
{
    public $postId;
    public $title = '';
    public $slug = '';

    protected function rules()
    {
        return [
            'title' => 'required|min:3|max:255',
            'slug' => [
                'required',
                'alpha_dash',
                Rule::unique('posts', 'slug')->ignore($this->postId)
            ],
        ];
    }

    public function save()
    {
        $validated = $this->validate();

        Post::find($this->postId)->update($validated);
    }
}
```

### 4. Custom Validation Messages

```php
class CreatePost extends Component
{
    public $title = '';

    protected function rules()
    {
        return [
            'title' => 'required|min:3|max:255',
        ];
    }

    protected function messages()
    {
        return [
            'title.required' => 'The post title is required.',
            'title.min' => 'The title must be at least 3 characters.',
            'title.max' => 'The title cannot exceed 255 characters.',
        ];
    }

    public function save()
    {
        $validated = $this->validate();
        // ...
    }
}
```

### 5. Custom Attribute Names

```php
protected function validationAttributes()
{
    return [
        'title' => 'post title',
        'content' => 'post content',
    ];
}

// Error message becomes:
// "The post title is required." instead of "The title is required."
```

---

## Real-Time Validation

Validate as user types!

### Validate on Property Update

```php
use Livewire\Attributes\Validate;

class CreatePost extends Component
{
    #[Validate('required|email')]
    public $email = '';

    // Runs every time $email changes
    public function updatedEmail()
    {
        $this->validateOnly('email');
    }
}
```

```html
<input
    type="email"
    wire:model.blur="email"
    class="@error('email') border-red-500 @enderror"
>
@error('email')
    <span class="text-red-500 text-sm">{{ $message }}</span>
@enderror
```

**When user tabs out**, validation runs immediately!

### Validate All Fields on Any Update

```php
public function updated($property)
{
    $this->validateOnly($property);
}
```

Now every field validates as you fill the form.

### Live Validation with Debounce

```html
<!-- Validate as user types, but debounced -->
<input
    type="text"
    wire:model.live.debounce.500ms="username"
    class="@error('username') border-red-500 @enderror"
>
```

```php
public function updatedUsername()
{
    $this->validateOnly('username');
}
```

---

## Validation Rules Reference

### Common Rules

```php
'title' => 'required|min:3|max:255',
'email' => 'required|email|unique:users',
'age' => 'required|integer|min:18|max:120',
'price' => 'required|numeric|min:0',
'website' => 'nullable|url',
'bio' => 'nullable|string|max:1000',
'category' => 'required|in:tech,business,health',
'tags' => 'array|min:1|max:5',
'tags.*' => 'string|max:20',
'published_at' => 'nullable|date|after:today',
'password' => 'required|min:8|confirmed',
'image' => 'required|image|mimes:jpg,png|max:2048',
```

### Array Validation

```php
public $users = [];

protected function rules()
{
    return [
        'users' => 'required|array|min:1',
        'users.*.name' => 'required|string|max:255',
        'users.*.email' => 'required|email|unique:users,email',
        'users.*.age' => 'required|integer|min:18',
    ];
}
```

### Conditional Rules

```php
use Illuminate\Validation\Rule;

protected function rules()
{
    return [
        'payment_method' => 'required|in:card,paypal,bank',
        'card_number' => [
            Rule::requiredIf($this->payment_method === 'card'),
            'digits:16'
        ],
        'paypal_email' => [
            Rule::requiredIf($this->payment_method === 'paypal'),
            'email'
        ],
    ];
}
```

### Custom Rules

```php
use Illuminate\Validation\Rule;

protected function rules()
{
    return [
        'username' => [
            'required',
            'alpha_dash',
            Rule::unique('users')->where(function ($query) {
                return $query->where('active', 1);
            })
        ],
    ];
}
```

---

## Error Display Patterns

### Field-Level Errors

```html
<div class="mb-4">
    <label>Email</label>
    <input
        type="email"
        wire:model="email"
        class="border px-3 py-2 rounded @error('email') border-red-500 @enderror"
    >
    @error('email')
        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
    @enderror
</div>
```

### Error Summary

```html
@if ($errors->any())
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        <h3 class="font-bold mb-2">Please fix the following errors:</h3>
        <ul class="list-disc list-inside">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
```

### Styled Error States

```html
<!-- ✅ Valid state -->
<input
    wire:model.blur="email"
    class="border rounded px-3 py-2
           @error('email') border-red-500 bg-red-50 @else border-gray-300 @enderror"
>

<!-- Show checkmark when valid -->
<div class="relative">
    <input wire:model.blur="email" class="...">

    @error('email')
        <svg class="absolute right-3 top-3 text-red-500"><!-- X icon --></svg>
    @else
        @if($email)
            <svg class="absolute right-3 top-3 text-green-500"><!-- Check icon --></svg>
        @endif
    @enderror
</div>
```

---

## Form Wizard (Multi-Step)

### Component

```php
class RegistrationWizard extends Component
{
    public $step = 1;

    // Step 1: Personal Info
    public $name = '';
    public $email = '';

    // Step 2: Company Info
    public $company = '';
    public $role = '';

    // Step 3: Preferences
    public $newsletter = false;

    public function goToStep2()
    {
        $this->validate([
            'name' => 'required|min:2',
            'email' => 'required|email|unique:users',
        ]);

        $this->step = 2;
    }

    public function goToStep3()
    {
        $this->validate([
            'company' => 'required',
            'role' => 'required',
        ]);

        $this->step = 3;
    }

    public function submit()
    {
        // Validate everything
        $validated = $this->validate([
            'name' => 'required|min:2',
            'email' => 'required|email|unique:users',
            'company' => 'required',
            'role' => 'required',
            'newsletter' => 'boolean',
        ]);

        User::create($validated);

        session()->flash('message', 'Registration complete!');

        return redirect()->route('dashboard');
    }

    public function back()
    {
        $this->step--;
    }

    public function render()
    {
        return view('livewire.registration-wizard');
    }
}
```

### View

```html
<div class="max-w-2xl mx-auto">
    <!-- Progress Indicator -->
    <div class="flex justify-between mb-8">
        <div class="flex-1 text-center">
            <div class="w-10 h-10 mx-auto rounded-full {{ $step >= 1 ? 'bg-blue-500' : 'bg-gray-300' }}"></div>
            <p class="mt-2 text-sm">Personal</p>
        </div>
        <div class="flex-1 text-center">
            <div class="w-10 h-10 mx-auto rounded-full {{ $step >= 2 ? 'bg-blue-500' : 'bg-gray-300' }}"></div>
            <p class="mt-2 text-sm">Company</p>
        </div>
        <div class="flex-1 text-center">
            <div class="w-10 h-10 mx-auto rounded-full {{ $step >= 3 ? 'bg-blue-500' : 'bg-gray-300' }}"></div>
            <p class="mt-2 text-sm">Preferences</p>
        </div>
    </div>

    <!-- Step 1: Personal Info -->
    @if($step === 1)
        <div>
            <h2 class="text-2xl font-bold mb-4">Personal Information</h2>

            <div class="mb-4">
                <label>Name</label>
                <input type="text" wire:model="name" class="w-full px-3 py-2 border rounded">
                @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="mb-4">
                <label>Email</label>
                <input type="email" wire:model="email" class="w-full px-3 py-2 border rounded">
                @error('email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <button wire:click="goToStep2" class="bg-blue-500 text-white px-6 py-2 rounded">
                Next
            </button>
        </div>
    @endif

    <!-- Step 2: Company Info -->
    @if($step === 2)
        <div>
            <h2 class="text-2xl font-bold mb-4">Company Information</h2>

            <div class="mb-4">
                <label>Company</label>
                <input type="text" wire:model="company" class="w-full px-3 py-2 border rounded">
                @error('company') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="mb-4">
                <label>Role</label>
                <input type="text" wire:model="role" class="w-full px-3 py-2 border rounded">
                @error('role') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="flex gap-2">
                <button wire:click="back" class="bg-gray-500 text-white px-6 py-2 rounded">
                    Back
                </button>
                <button wire:click="goToStep3" class="bg-blue-500 text-white px-6 py-2 rounded">
                    Next
                </button>
            </div>
        </div>
    @endif

    <!-- Step 3: Preferences -->
    @if($step === 3)
        <div>
            <h2 class="text-2xl font-bold mb-4">Preferences</h2>

            <div class="mb-4">
                <label class="inline-flex items-center">
                    <input type="checkbox" wire:model="newsletter">
                    <span class="ml-2">Subscribe to newsletter</span>
                </label>
            </div>

            <div class="flex gap-2">
                <button wire:click="back" class="bg-gray-500 text-white px-6 py-2 rounded">
                    Back
                </button>
                <button
                    wire:click="submit"
                    wire:loading.attr="disabled"
                    class="bg-green-500 text-white px-6 py-2 rounded"
                >
                    <span wire:loading.remove wire:target="submit">Complete Registration</span>
                    <span wire:loading wire:target="submit">Submitting...</span>
                </button>
            </div>
        </div>
    @endif
</div>
```

---

## Form Objects (Advanced)

For very complex forms, use form objects:

### Create Form Object

```bash
php artisan make:form PostForm
```

### Form Class

```php
<?php

namespace App\Livewire\Forms;

use Livewire\Form;
use Livewire\Attributes\Validate;

class PostForm extends Form
{
    #[Validate('required|min:3|max:255')]
    public $title = '';

    #[Validate('required|min:10')]
    public $content = '';

    #[Validate('required|exists:categories,id')]
    public $category_id = '';

    #[Validate('array')]
    public $tags = [];

    public function store()
    {
        $validated = $this->validate();

        return Post::create($validated);
    }

    public function update(Post $post)
    {
        $validated = $this->validate();

        $post->update($validated);
    }
}
```

### Use in Component

```php
use App\Livewire\Forms\PostForm;

class CreatePost extends Component
{
    public PostForm $form;

    public function save()
    {
        $this->form->store();

        session()->flash('message', 'Post created!');

        return redirect()->route('posts.index');
    }

    public function render()
    {
        return view('livewire.create-post', [
            'categories' => Category::all()
        ]);
    }
}
```

### View with Form Object

```html
<form wire:submit="save">
    <input wire:model="form.title">
    @error('form.title') <span>{{ $message }}</span> @enderror

    <textarea wire:model="form.content"></textarea>
    @error('form.content') <span>{{ $message }}</span> @enderror

    <select wire:model="form.category_id">
        @foreach($categories as $category)
            <option value="{{ $category->id }}">{{ $category->name }}</option>
        @endforeach
    </select>
    @error('form.category_id') <span>{{ $message }}</span> @enderror

    <button type="submit">Create Post</button>
</form>
```

**Benefits:**
- Reusable across Create/Edit components
- Cleaner component code
- Validation in one place

---

## File Upload Basics

We'll cover this in depth in the next lesson, but here's a preview:

```php
use Livewire\WithFileUploads;

class CreatePost extends Component
{
    use WithFileUploads;

    public $image;

    protected function rules()
    {
        return [
            'image' => 'required|image|max:2048', // 2MB
        ];
    }

    public function save()
    {
        $validated = $this->validate();

        $path = $this->image->store('posts', 'public');

        Post::create([
            'image' => $path,
        ]);
    }
}
```

```html
<input type="file" wire:model="image">

@error('image') <span>{{ $message }}</span> @enderror

<!-- Preview -->
@if ($image)
    <img src="{{ $image->temporaryUrl() }}" class="w-32 h-32">
@endif
```

---

## Quick Quiz

**Question 1**: What's better for real-time validation?

```php
// A)
public function updated($property)
{
    $this->validate();
}

// B)
public function updated($property)
{
    $this->validateOnly($property);
}
```

<details>
<summary>Show Answer</summary>

**B** - `validateOnly()` validates only the changed property, more efficient than validating all fields.

</details>

**Question 2**: How do you validate an array of items?

<details>
<summary>Show Answer</summary>

```php
protected function rules()
{
    return [
        'items' => 'required|array|min:1',
        'items.*.name' => 'required|string',
        'items.*.price' => 'required|numeric|min:0',
    ];
}
```

</details>

**Question 3**: What does this attribute do?

```php
#[Validate('required|email')]
public $email = '';
```

<details>
<summary>Show Answer</summary>

Defines validation rules directly on the property. When you call `$this->validate()`, it will use these rules automatically.

</details>

---

## Practice Exercise

Create a **User Registration Form**:

**Requirements:**
1. Fields: name, email, password, password_confirmation, terms checkbox
2. Real-time validation on blur
3. Show validation errors inline
4. Disable submit until all valid
5. Show loading state on submit

Try it yourself!

<details>
<summary>Show Solution</summary>

```php
<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class Register extends Component
{
    public $name = '';
    public $email = '';
    public $password = '';
    public $password_confirmation = '';
    public $terms = false;

    protected function rules()
    {
        return [
            'name' => 'required|min:2|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
            'terms' => 'accepted',
        ];
    }

    protected function messages()
    {
        return [
            'terms.accepted' => 'You must accept the terms and conditions.',
        ];
    }

    public function updated($property)
    {
        $this->validateOnly($property);
    }

    public function register()
    {
        $validated = $this->validate();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        auth()->login($user);

        session()->flash('message', 'Welcome! Your account has been created.');

        return redirect()->route('dashboard');
    }

    public function render()
    {
        return view('livewire.register');
    }
}
```

```html
<div class="max-w-md mx-auto bg-white p-8 rounded-lg shadow">
    <h2 class="text-2xl font-bold mb-6">Create Account</h2>

    <form wire:submit="register">
        <!-- Name -->
        <div class="mb-4">
            <label class="block text-sm font-medium mb-2">Name</label>
            <input
                type="text"
                wire:model.blur="name"
                class="w-full px-3 py-2 border rounded focus:ring-2 focus:ring-blue-500 @error('name') border-red-500 @enderror"
            >
            @error('name')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Email -->
        <div class="mb-4">
            <label class="block text-sm font-medium mb-2">Email</label>
            <input
                type="email"
                wire:model.blur="email"
                class="w-full px-3 py-2 border rounded focus:ring-2 focus:ring-blue-500 @error('email') border-red-500 @enderror"
            >
            @error('email')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Password -->
        <div class="mb-4">
            <label class="block text-sm font-medium mb-2">Password</label>
            <input
                type="password"
                wire:model.blur="password"
                class="w-full px-3 py-2 border rounded focus:ring-2 focus:ring-blue-500 @error('password') border-red-500 @enderror"
            >
            @error('password')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Confirm Password -->
        <div class="mb-4">
            <label class="block text-sm font-medium mb-2">Confirm Password</label>
            <input
                type="password"
                wire:model.blur="password_confirmation"
                class="w-full px-3 py-2 border rounded focus:ring-2 focus:ring-blue-500"
            >
        </div>

        <!-- Terms -->
        <div class="mb-6">
            <label class="inline-flex items-center">
                <input type="checkbox" wire:model.live="terms" class="rounded">
                <span class="ml-2 text-sm">I accept the terms and conditions</span>
            </label>
            @error('terms')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Submit -->
        <button
            type="submit"
            wire:loading.attr="disabled"
            class="w-full bg-blue-500 text-white py-2 rounded hover:bg-blue-600 disabled:opacity-50"
        >
            <span wire:loading.remove wire:target="register">Create Account</span>
            <span wire:loading wire:target="register">Creating Account...</span>
        </button>
    </form>
</div>
```

</details>

---

## Summary

You mastered:

- ✅ Building forms with Livewire
- ✅ Multiple validation approaches
- ✅ Real-time validation
- ✅ Custom rules and messages
- ✅ Error display patterns
- ✅ Multi-step forms
- ✅ Form objects for complex forms

**Next Lesson**: File Uploads - handle images and documents with Livewire!

---

**Forms are the backbone of web apps. You now have the power to build robust, validated forms!**
