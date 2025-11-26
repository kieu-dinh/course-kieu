# Lesson 6: File Uploads with Livewire

**Duration**: 60 minutes
**Prerequisites**: Lesson 5 completed

---

## What You'll Learn

- Single file uploads
- Multiple file uploads
- Image previews
- File validation
- Progress indicators
- Temporary URLs
- Direct uploads to S3
- Deleting uploaded files

---

## Setup

### 1. Configure Storage

Make sure your storage is set up:

```bash
# Create symbolic link (if not done)
php artisan storage:link
```

This creates a symlink from `public/storage` to `storage/app/public`.

### 2. Configure Livewire

In `config/livewire.php`:

```php
return [
    'temporary_file_upload' => [
        'disk' => 'local',        // Where temp files are stored
        'rules' => ['required', 'file', 'max:12288'], // 12MB default
        'directory' => 'livewire-tmp',
        'middleware' => null,
        'preview_mimes' => [
            'png', 'gif', 'bmp', 'svg', 'wav', 'mp4',
            'mov', 'avi', 'wmv', 'mp3', 'm4a',
            'jpg', 'jpeg', 'mpga', 'webp', 'wma',
        ],
    ],
];
```

---

## Single File Upload

### Component

```php
<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Post;

class CreatePost extends Component
{
    use WithFileUploads; // Required!

    public $title = '';
    public $image;

    protected function rules()
    {
        return [
            'title' => 'required|min:3',
            'image' => 'required|image|max:2048', // 2MB
        ];
    }

    public function save()
    {
        $validated = $this->validate();

        // Store the file
        $path = $this->image->store('posts', 'public');

        Post::create([
            'title' => $validated['title'],
            'image' => $path,
        ]);

        session()->flash('message', 'Post created!');

        return redirect()->route('posts.index');
    }

    public function render()
    {
        return view('livewire.create-post');
    }
}
```

### View

```html
<div>
    <form wire:submit="save">
        <!-- Title -->
        <div class="mb-4">
            <label class="block mb-2">Title</label>
            <input type="text" wire:model="title" class="w-full px-3 py-2 border rounded">
            @error('title') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <!-- Image Upload -->
        <div class="mb-4">
            <label class="block mb-2">Image</label>
            <input type="file" wire:model="image" class="w-full">
            @error('image') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

            <!-- Loading indicator -->
            <div wire:loading wire:target="image" class="text-blue-500 text-sm mt-1">
                Uploading...
            </div>
        </div>

        <button
            type="submit"
            wire:loading.attr="disabled"
            class="bg-blue-500 text-white px-6 py-2 rounded"
        >
            Save
        </button>
    </form>
</div>
```

---

## Image Preview

Show preview before upload:

```html
<div class="mb-4">
    <label class="block mb-2">Image</label>
    <input type="file" wire:model="image" accept="image/*">
    @error('image') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

    <!-- Preview -->
    @if ($image)
        <div class="mt-4">
            <img src="{{ $image->temporaryUrl() }}" class="w-64 h-64 object-cover rounded">
        </div>
    @endif

    <!-- Loading -->
    <div wire:loading wire:target="image" class="mt-2">
        <div class="inline-block h-8 w-8 animate-spin rounded-full border-4 border-solid border-current border-r-transparent"></div>
        <span class="ml-2">Uploading...</span>
    </div>
</div>
```

**How it works:**
1. User selects file
2. Livewire uploads to temp directory
3. `temporaryUrl()` creates preview URL
4. On form submit, file is moved to permanent location

---

## Multiple File Uploads

### Component

```php
use Livewire\WithFileUploads;

class CreateGallery extends Component
{
    use WithFileUploads;

    public $images = []; // Array!

    protected function rules()
    {
        return [
            'images' => 'required|array|min:1|max:5',
            'images.*' => 'image|max:2048',
        ];
    }

    public function save()
    {
        $validated = $this->validate();

        $gallery = Gallery::create([
            'name' => 'My Gallery'
        ]);

        foreach ($this->images as $image) {
            $path = $image->store('galleries', 'public');

            $gallery->images()->create([
                'path' => $path
            ]);
        }

        session()->flash('message', 'Gallery created!');

        return redirect()->route('galleries.index');
    }

    public function render()
    {
        return view('livewire.create-gallery');
    }
}
```

### View

```html
<div>
    <form wire:submit="save">
        <div class="mb-4">
            <label class="block mb-2">Images (max 5)</label>
            <input
                type="file"
                wire:model="images"
                multiple
                accept="image/*"
            >
            @error('images') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            @error('images.*') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

            <!-- Loading -->
            <div wire:loading wire:target="images" class="text-blue-500 mt-2">
                Uploading images...
            </div>
        </div>

        <!-- Previews -->
        @if ($images)
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-4">
                @foreach($images as $image)
                    <div class="relative">
                        <img src="{{ $image->temporaryUrl() }}" class="w-full h-32 object-cover rounded">
                    </div>
                @endforeach
            </div>
        @endif

        <button type="submit" class="bg-blue-500 text-white px-6 py-2 rounded">
            Create Gallery
        </button>
    </form>
</div>
```

---

## File Validation

### Common Validation Rules

```php
// Image files
'image' => 'required|image|max:2048', // 2MB
'image' => 'required|image|mimes:jpg,jpeg,png|max:1024',
'image' => 'required|image|dimensions:min_width=100,min_height=100',
'image' => 'required|image|dimensions:max_width=2000,max_height=2000',

// Documents
'document' => 'required|file|mimes:pdf,doc,docx|max:5120', // 5MB
'csv' => 'required|file|mimes:csv,txt|max:10240', // 10MB

// Video
'video' => 'required|file|mimes:mp4,mov,avi|max:51200', // 50MB

// Multiple files
'files' => 'required|array|min:1|max:10',
'files.*' => 'file|mimes:jpg,png,pdf|max:2048',
```

### Real-Time Validation

Validate immediately after file selection:

```php
public function updatedImage()
{
    $this->validateOnly('image');
}
```

```html
<input
    type="file"
    wire:model="image"
    wire:change="$validate('image')" <!-- Validate on change -->
>
@error('image')
    <span class="text-red-500 text-sm">{{ $message }}</span>
@enderror
```

---

## Advanced Upload UI

### With Progress Bar

Using Alpine.js for better UX:

```html
<div x-data="{ uploading: false, progress: 0 }"
     x-on:livewire-upload-start="uploading = true"
     x-on:livewire-upload-finish="uploading = false"
     x-on:livewire-upload-cancel="uploading = false"
     x-on:livewire-upload-error="uploading = false"
     x-on:livewire-upload-progress="progress = $event.detail.progress">

    <!-- File Input -->
    <input type="file" wire:model="image">

    <!-- Progress Bar -->
    <div x-show="uploading" class="mt-2">
        <div class="bg-gray-200 rounded-full h-4 overflow-hidden">
            <div
                class="bg-blue-500 h-4 transition-all duration-300"
                :style="`width: ${progress}%`"
            ></div>
        </div>
        <p class="text-sm text-gray-600 mt-1">
            Uploading: <span x-text="progress"></span>%
        </p>
    </div>
</div>
```

### Drag and Drop

```html
<div
    x-data="{ isHovering: false }"
    x-on:drop.prevent="isHovering = false"
    x-on:dragover.prevent="isHovering = true"
    x-on:dragleave.prevent="isHovering = false"
    class="border-2 border-dashed rounded-lg p-8 text-center transition"
    :class="isHovering ? 'border-blue-500 bg-blue-50' : 'border-gray-300'"
>
    <input
        type="file"
        wire:model="image"
        class="hidden"
        id="file-upload"
    >

    <label for="file-upload" class="cursor-pointer">
        <div class="text-gray-600">
            <svg class="w-12 h-12 mx-auto mb-4"><!-- Upload icon --></svg>
            <p class="text-lg font-medium">Drop your image here</p>
            <p class="text-sm">or click to browse</p>
        </div>
    </label>

    @if ($image)
        <div class="mt-4">
            <img src="{{ $image->temporaryUrl() }}" class="mx-auto w-64 rounded">
        </div>
    @endif
</div>
```

### Remove Selected File

```php
public $image;

public function removeImage()
{
    $this->image = null;
}
```

```html
@if ($image)
    <div class="relative inline-block">
        <img src="{{ $image->temporaryUrl() }}" class="w-64 rounded">
        <button
            type="button"
            wire:click="removeImage"
            class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-2"
        >
            X
        </button>
    </div>
@endif
```

---

## Storing Files

### Different Storage Disks

```php
// Default (local)
$path = $this->image->store('posts');
// Stored in: storage/app/posts/

// Public disk
$path = $this->image->store('posts', 'public');
// Stored in: storage/app/public/posts/
// Accessible at: /storage/posts/filename.jpg

// S3 (requires league/flysystem-aws-s3-v3)
$path = $this->image->store('posts', 's3');
// Stored in S3 bucket
```

### Custom Filename

```php
// Auto-generated name
$path = $this->image->store('posts', 'public');
// Result: posts/abc123def456.jpg

// Custom name
$filename = time() . '_' . $this->image->getClientOriginalName();
$path = $this->image->storeAs('posts', $filename, 'public');
// Result: posts/1699000000_myimage.jpg

// Using model ID
$post = Post::create(['title' => $this->title]);
$filename = $post->id . '.' . $this->image->getClientOriginalExtension();
$path = $this->image->storeAs('posts', $filename, 'public');
// Result: posts/1.jpg
```

### With Image Processing

Using Intervention Image package:

```bash
composer require intervention/image
```

```php
use Intervention\Image\Facades\Image;

public function save()
{
    $validated = $this->validate();

    // Save original
    $path = $this->image->store('posts/original', 'public');

    // Create thumbnail
    $thumbnailPath = 'posts/thumbnails/' . basename($path);
    Image::make($this->image->getRealPath())
        ->fit(300, 300)
        ->save(storage_path('app/public/' . $thumbnailPath));

    Post::create([
        'title' => $validated['title'],
        'image' => $path,
        'thumbnail' => $thumbnailPath,
    ]);
}
```

---

## Temporary Files

### How Temporary Files Work

1. User selects file
2. Livewire uploads to `storage/app/livewire-tmp/`
3. Temporary signed URL created for preview
4. On form submit, file moved to permanent location
5. Temporary files auto-deleted after 24 hours

### Temporary URL

```php
// In view
@if ($image)
    <img src="{{ $image->temporaryUrl() }}">
@endif
```

**Important**: Only works for preview-able file types (images, videos, PDFs).

### Check if File is Uploaded

```php
if ($this->image) {
    // File has been selected and uploaded to temp
}
```

### Get Original Filename

```php
$originalName = $this->image->getClientOriginalName();
// "vacation-photo.jpg"

$extension = $this->image->getClientOriginalExtension();
// "jpg"

$mimeType = $this->image->getMimeType();
// "image/jpeg"

$size = $this->image->getSize();
// 2048000 (bytes)
```

---

## Direct S3 Uploads

For large files, upload directly to S3 (bypasses server):

### 1. Install Flysystem

```bash
composer require league/flysystem-aws-s3-v3
```

### 2. Configure S3 in .env

```env
AWS_ACCESS_KEY_ID=your-key
AWS_SECRET_ACCESS_KEY=your-secret
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=your-bucket
AWS_USE_PATH_STYLE_ENDPOINT=false
```

### 3. Update Livewire Config

```php
// config/livewire.php
'temporary_file_upload' => [
    'disk' => 's3',
],
```

### 4. Component

```php
public function save()
{
    $validated = $this->validate([
        'image' => 'required|image|max:10240', // 10MB
    ]);

    // Already on S3!
    $path = $this->image->store('posts', 's3');

    Post::create([
        'image' => $path,
    ]);
}
```

**Benefits:**
- Faster uploads (parallel to S3)
- No server bandwidth used
- Can handle very large files

---

## Deleting Files

### When Updating

```php
use Illuminate\Support\Facades\Storage;

class EditPost extends Component
{
    use WithFileUploads;

    public Post $post;
    public $newImage;

    public function save()
    {
        if ($this->newImage) {
            // Delete old image
            if ($this->post->image) {
                Storage::disk('public')->delete($this->post->image);
            }

            // Upload new image
            $path = $this->newImage->store('posts', 'public');

            $this->post->update(['image' => $path]);
        }
    }
}
```

### When Deleting Model

Use model events:

```php
// In Post model
protected static function booted()
{
    static::deleting(function ($post) {
        if ($post->image) {
            Storage::disk('public')->delete($post->image);
        }
    });
}
```

---

## Real-World Example: Avatar Upload

```php
class UpdateProfile extends Component
{
    use WithFileUploads;

    public User $user;
    public $avatar;

    public function mount()
    {
        $this->user = auth()->user();
    }

    public function updateAvatar()
    {
        $this->validate([
            'avatar' => 'required|image|max:1024|dimensions:min_width=200,min_height=200',
        ]);

        // Delete old avatar
        if ($this->user->avatar) {
            Storage::disk('public')->delete($this->user->avatar);
        }

        // Save new avatar
        $path = $this->avatar->store('avatars', 'public');

        $this->user->update(['avatar' => $path]);

        $this->avatar = null;

        session()->flash('message', 'Avatar updated!');
    }

    public function removeAvatar()
    {
        if ($this->user->avatar) {
            Storage::disk('public')->delete($this->user->avatar);
            $this->user->update(['avatar' => null]);
        }

        session()->flash('message', 'Avatar removed!');
    }

    public function render()
    {
        return view('livewire.update-profile');
    }
}
```

```html
<div class="max-w-md mx-auto">
    @if(session('message'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('message') }}
        </div>
    @endif

    <!-- Current Avatar -->
    <div class="text-center mb-6">
        @if($user->avatar)
            <img src="{{ Storage::url($user->avatar) }}" class="w-32 h-32 rounded-full mx-auto mb-4">
            <button wire:click="removeAvatar" class="text-red-600 text-sm">
                Remove Avatar
            </button>
        @else
            <div class="w-32 h-32 bg-gray-200 rounded-full mx-auto mb-4 flex items-center justify-center">
                <span class="text-4xl text-gray-400">{{ substr($user->name, 0, 1) }}</span>
            </div>
        @endif
    </div>

    <!-- Upload New Avatar -->
    <div class="mb-4">
        <label class="block mb-2 font-medium">Upload New Avatar</label>
        <input type="file" wire:model="avatar" accept="image/*">
        @error('avatar')
            <span class="text-red-500 text-sm">{{ $message }}</span>
        @enderror

        <!-- Preview -->
        @if ($avatar)
            <div class="mt-4 text-center">
                <img src="{{ $avatar->temporaryUrl() }}" class="w-32 h-32 rounded-full mx-auto">
                <button
                    wire:click="updateAvatar"
                    wire:loading.attr="disabled"
                    class="mt-4 bg-blue-500 text-white px-6 py-2 rounded"
                >
                    <span wire:loading.remove wire:target="updateAvatar">Save Avatar</span>
                    <span wire:loading wire:target="updateAvatar">Saving...</span>
                </button>
            </div>
        @endif

        <!-- Loading -->
        <div wire:loading wire:target="avatar" class="text-blue-500 mt-2">
            Uploading...
        </div>
    </div>
</div>
```

---

## Quick Quiz

**Question 1**: What trait must you use for file uploads?

<details>
<summary>Show Answer</summary>

```php
use Livewire\WithFileUploads;

class MyComponent extends Component
{
    use WithFileUploads;
}
```

</details>

**Question 2**: How do you show a preview of the uploaded image?

<details>
<summary>Show Answer</summary>

```html
@if ($image)
    <img src="{{ $image->temporaryUrl() }}">
@endif
```

</details>

**Question 3**: How do you validate multiple images?

<details>
<summary>Show Answer</summary>

```php
protected function rules()
{
    return [
        'images' => 'required|array|min:1',
        'images.*' => 'image|max:2048',
    ];
}
```

</details>

---

## Practice Exercise

Create an **Image Gallery Upload**:

**Requirements:**
1. Upload multiple images (max 5)
2. Show previews with remove button
3. Validate: images only, max 2MB each
4. Show upload progress
5. Display existing gallery images
6. Delete existing images

Try it yourself!

<details>
<summary>Show Solution</summary>

See the complete solution in Exercise 17.2!

</details>

---

## Summary

You mastered:

- ✅ Single and multiple file uploads
- ✅ Image previews with temporary URLs
- ✅ File validation rules
- ✅ Progress indicators and loading states
- ✅ Advanced upload UIs (drag-drop, progress bars)
- ✅ Storing files to different disks
- ✅ Direct S3 uploads
- ✅ Deleting uploaded files

**Next Lesson**: Pagination - efficiently display large datasets!

---

**File uploads are a crucial feature. You can now handle them professionally with Livewire!**
