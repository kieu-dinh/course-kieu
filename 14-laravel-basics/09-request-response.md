# Lesson 09 - Request and Response

**Duration**: 60 minutes
**Difficulty**: Beginner

---

## What is Request and Response?

**HTTP is a request-response protocol:**

```
Browser → Request → Laravel → Response → Browser
```

**Request:** Data from user (URL, form data, files, headers)
**Response:** What Laravel sends back (HTML, JSON, redirect, file)

### Pure PHP (Modules 04-10)

**Request data:**
```php
$title = $_POST['title'] ?? null;
$email = $_GET['email'] ?? null;
$file = $_FILES['image'] ?? null;
$method = $_SERVER['REQUEST_METHOD'];
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
```

**Response:**
```php
header('Content-Type: application/json');
header('Location: /posts');
http_response_code(404);
echo json_encode(['error' => 'Not found']);
```

**Problems:**
- Superglobals everywhere (`$_POST`, `$_GET`, `$_SERVER`)
- No validation helper
- Manual header management
- No type safety
- Messy code

### Laravel Approach

**Request data:**
```php
public function store(Request $request)
{
    $title = $request->input('title');
    $email = $request->query('email');
    $file = $request->file('image');
    $method = $request->method();
    $userAgent = $request->userAgent();
}
```

**Response:**
```php
return response()->json(['error' => 'Not found'], 404);
return redirect('/posts');
return view('posts.show', compact('post'));
```

**Much cleaner!**

---

## The Request Object

Laravel injects the `Request` object into controller methods automatically.

```php
use Illuminate\Http\Request;

public function store(Request $request)
{
    // $request contains all request data
}
```

---

## Retrieving Input

### All Input

```php
// All input (POST, GET, etc.)
$all = $request->all();

// Only specific fields
$data = $request->only(['title', 'content']);

// All except specific fields
$data = $request->except(['_token', '_method']);
```

**Pure PHP:**
```php
$all = array_merge($_GET, $_POST);
$data = [
    'title' => $_POST['title'] ?? null,
    'content' => $_POST['content'] ?? null
];
```

### Single Input Value

```php
// Get value
$title = $request->input('title');

// With default
$name = $request->input('name', 'Guest');

// Nested (dot notation)
$city = $request->input('address.city');
// From: <input name="address[city]">
```

### Checking Input Presence

```php
// Check if key exists
if ($request->has('email')) {
    // Input exists (even if empty)
}

// Check if multiple exist
if ($request->has(['email', 'password'])) {
    // Both exist
}

// Check if exists and not empty
if ($request->filled('email')) {
    // Exists and has value
}

// Check if missing
if ($request->missing('email')) {
    // Doesn't exist
}
```

**Pure PHP:**
```php
if (isset($_POST['email'])) { }
if (!empty($_POST['email'])) { }
```

### Query String Parameters

```php
// URL: /posts?page=2&sort=latest

$page = $request->query('page');
$sort = $request->query('sort', 'recent');  // With default

// All query parameters
$query = $request->query();
```

**Pure PHP:**
```php
$page = $_GET['page'] ?? null;
$sort = $_GET['sort'] ?? 'recent';
```

### Boolean Input

**Checkboxes and radios:**

```php
// Returns true if present, false if not
$published = $request->boolean('published');

// HTML: <input type="checkbox" name="published" value="1">
```

**Handles:** `1`, `"1"`, `true`, `"true"`, `"on"`, `"yes"` → `true`
**Anything else:** → `false`

### Old Input

**Repopulate forms after validation failure:**

```php
$title = $request->old('title');
```

**In views:**
```blade
<input type="text" name="title" value="{{ old('title') }}">
```

**Flash input to session:**
```php
$request->flash();  // All input
$request->flashOnly(['title', 'email']);
$request->flashExcept(['password']);

return redirect()->back()->withInput();
```

---

## Request Information

### URL & Path

```php
// Full URL: https://myapp.com/posts/123?page=2
$url = $request->url();  // https://myapp.com/posts/123
$fullUrl = $request->fullUrl();  // https://myapp.com/posts/123?page=2

// Path
$path = $request->path();  // posts/123

// Check path
if ($request->is('posts/*')) {
    // Matches posts/123, posts/create, etc.
}

// Check route name
if ($request->routeIs('posts.*')) {
    // Matches posts.index, posts.show, etc.
}
```

**Pure PHP:**
```php
$url = $_SERVER['REQUEST_URI'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
```

### HTTP Method

```php
$method = $request->method();  // GET, POST, PUT, DELETE

if ($request->isMethod('post')) {
    // Is POST request
}
```

**Pure PHP:**
```php
$method = $_SERVER['REQUEST_METHOD'];
```

### Headers

```php
// Get header
$accept = $request->header('Accept');
$token = $request->bearerToken();  // Authorization: Bearer xxx

// Check if header exists
if ($request->hasHeader('Accept')) {
    //
}
```

**Pure PHP:**
```php
$accept = $_SERVER['HTTP_ACCEPT'] ?? '';
```

### IP Address

```php
$ip = $request->ip();
```

**Pure PHP:**
```php
$ip = $_SERVER['REMOTE_ADDR'];
```

### User Agent

```php
$userAgent = $request->userAgent();
```

**Pure PHP:**
```php
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
```

### Content Types

```php
// Expects JSON?
if ($request->expectsJson()) {
    return response()->json([...]);
}

// Wants JSON? (via Accept header)
if ($request->wantsJson()) {
    return response()->json([...]);
}

// Is AJAX?
if ($request->ajax()) {
    // Is XMLHttpRequest
}
```

---

## File Uploads

### Retrieving Files

```php
// Get file
$file = $request->file('image');

// Check if file was uploaded
if ($request->hasFile('image')) {
    // File exists
}

// Check if file valid
if ($request->file('image')->isValid()) {
    // File uploaded successfully
}
```

**Pure PHP:**
```php
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    // File uploaded
}
```

### File Information

```php
$file = $request->file('image');

$path = $file->path();              // /tmp/phpXXXXXX
$extension = $file->extension();     // jpg
$size = $file->getSize();           // bytes
$mimeType = $file->getMimeType();   // image/jpeg
$originalName = $file->getClientOriginalName();  // photo.jpg
```

### Storing Files

```php
$file = $request->file('image');

// Store in /storage/app/images
$path = $file->store('images');

// Store with specific name
$path = $file->storeAs('images', 'profile.jpg');

// Store in public disk
$path = $file->store('images', 'public');
// Accessible at: /storage/images/filename.jpg
```

**Pure PHP (Module 06):**
```php
$tmpName = $_FILES['image']['tmp_name'];
$name = $_FILES['image']['name'];
$destination = 'uploads/' . $name;

if (move_uploaded_file($tmpName, $destination)) {
    // Success
}
```

**Laravel handles:**
- Unique filenames
- Storage drivers (local, S3, etc.)
- File validation

### Complete Upload Example

```php
public function store(Request $request)
{
    $request->validate([
        'image' => 'required|image|mimes:jpeg,png,jpg|max:2048',  // 2MB
    ]);

    $file = $request->file('image');

    // Store with unique name
    $path = $file->store('posts', 'public');

    $post = Post::create([
        'title' => $request->title,
        'image_path' => $path,
    ]);

    return redirect()->route('posts.show', $post);
}
```

**In view:**
```blade
<img src="{{ asset('storage/' . $post->image_path) }}">
```

---

## Request Validation

### Basic Validation

```php
public function store(Request $request)
{
    $validated = $request->validate([
        'title' => 'required|max:255',
        'email' => 'required|email|unique:users',
        'password' => 'required|min:8|confirmed',
        'age' => 'required|integer|between:18,100',
    ]);

    // Only validated data
    Post::create($validated);
}
```

**If validation fails:**
- Automatically redirects back
- Flashes errors to session
- Preserves old input

**Pure PHP (Module 04):**
```php
$errors = [];

if (empty($_POST['title'])) {
    $errors[] = 'Title is required';
}

if (strlen($_POST['title']) > 255) {
    $errors[] = 'Title too long';
}

if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Invalid email';
}

if (!empty($errors)) {
    $_SESSION['errors'] = $errors;
    $_SESSION['old'] = $_POST;
    header('Location: create.php');
    exit;
}
```

### Custom Error Messages

```php
$request->validate([
    'title' => 'required|max:255',
], [
    'title.required' => 'Please enter a post title.',
    'title.max' => 'Title cannot exceed 255 characters.',
]);
```

### Manual Validation

```php
use Illuminate\Support\Facades\Validator;

$validator = Validator::make($request->all(), [
    'title' => 'required|max:255',
]);

if ($validator->fails()) {
    return redirect()->back()
        ->withErrors($validator)
        ->withInput();
}

$validated = $validator->validated();
```

### Common Validation Rules

```php
'field' => 'required',               // Must be present and not empty
'field' => 'nullable',               // Optional field
'field' => 'string',                 // Must be string
'field' => 'integer',                // Must be integer
'field' => 'numeric',                // Must be numeric
'field' => 'boolean',                // Must be boolean
'field' => 'email',                  // Valid email
'field' => 'url',                    // Valid URL
'field' => 'date',                   // Valid date
'field' => 'min:3',                  // Min length/value
'field' => 'max:255',                // Max length/value
'field' => 'between:18,100',         // Between values
'field' => 'in:draft,published',     // One of these values
'field' => 'unique:users,email',     // Unique in table
'field' => 'exists:users,id',        // Exists in table
'field' => 'confirmed',              // Must have field_confirmation
'field' => 'same:other_field',       // Must match other field
'field' => 'different:other_field',  // Must differ from other field
'field' => 'alpha',                  // Only letters
'field' => 'alpha_num',              // Letters and numbers
'field' => 'regex:/pattern/',        // Match regex
'field' => 'image',                  // Must be image
'field' => 'mimes:jpeg,png',         // File mime types
'field' => 'size:1024',              // File size in KB
```

**Combine rules:**
```php
'email' => 'required|email|unique:users|max:255',
'password' => 'required|string|min:8|confirmed',
'age' => 'required|integer|between:18,100',
```

---

## The Response Object

### Returning Responses

**String:**
```php
return 'Hello World';
```

**Array (auto-converted to JSON):**
```php
return ['name' => 'John', 'age' => 30];
// Content-Type: application/json
```

**View:**
```php
return view('posts.index', compact('posts'));
```

**JSON:**
```php
return response()->json(['success' => true]);
```

**Redirect:**
```php
return redirect('/posts');
return redirect()->route('posts.show', $post);
return redirect()->back();
```

---

## Response Types

### JSON Response

```php
return response()->json([
    'success' => true,
    'data' => $posts,
], 200);
```

**With headers:**
```php
return response()->json($data, 200, [
    'X-Custom-Header' => 'Value',
]);
```

**Pure PHP:**
```php
header('Content-Type: application/json');
http_response_code(200);
echo json_encode(['success' => true, 'data' => $posts]);
```

### Download Response

```php
return response()->download($pathToFile);
return response()->download($pathToFile, 'custom-name.pdf');
```

**Stream file (for large files):**
```php
return response()->streamDownload(function () {
    echo file_get_contents($url);
}, 'filename.pdf');
```

### File Response

**Display in browser:**
```php
return response()->file($pathToFile);
```

**Use case:** Display PDF, image directly in browser.

### Redirect Response

**To URL:**
```php
return redirect('/posts');
return redirect('https://example.com');
```

**To route:**
```php
return redirect()->route('posts.show', ['post' => $post]);
return redirect()->route('posts.index');
```

**Back:**
```php
return redirect()->back();
return redirect()->back()->withInput();
```

**With flash data:**
```php
return redirect()
    ->route('posts.index')
    ->with('success', 'Post created!');
```

**To controller action:**
```php
return redirect()->action([PostController::class, 'index']);
```

**Pure PHP:**
```php
header('Location: /posts');
exit;
```

### Response Macros

**Custom response types (advanced):**

```php
// In AppServiceProvider
Response::macro('caps', function ($value) {
    return Response::make(strtoupper($value));
});

// Usage
return response()->caps('hello');  // HELLO
```

---

## Response Headers

### Adding Headers

```php
return response('Hello')
    ->header('X-Custom-Header', 'Value')
    ->header('Content-Type', 'text/plain');
```

**Chain multiple:**
```php
return response($content)
    ->withHeaders([
        'X-Header-One' => 'Value 1',
        'X-Header-Two' => 'Value 2',
    ]);
```

### Cookies

**Attach cookie:**
```php
return response('Hello')
    ->cookie('name', 'value', $minutes);
```

**With options:**
```php
return response('Hello')
    ->cookie('name', 'value', $minutes, $path, $domain, $secure, $httpOnly);
```

**Queue cookie (send with any response):**
```php
Cookie::queue('name', 'value', $minutes);
```

**Get cookie from request:**
```php
$value = $request->cookie('name');
```

**Pure PHP:**
```php
setcookie('name', 'value', time() + 3600);
$value = $_COOKIE['name'] ?? null;
```

---

## HTTP Status Codes

### Common Status Codes

```php
return response('OK', 200);                    // OK
return response('Created', 201);               // Created
return response('No Content', 204);            // No Content
return response('Bad Request', 400);           // Bad Request
return response('Unauthorized', 401);          // Unauthorized
return response('Forbidden', 403);             // Forbidden
return response('Not Found', 404);             // Not Found
return response('Server Error', 500);          // Internal Server Error
```

### Helper Functions

```php
return response()->noContent();                // 204

abort(404);                                    // Throw 404
abort(403, 'Unauthorized action.');            // Throw 403 with message
abort_if($condition, 404);                     // Abort if condition true
abort_unless($condition, 404);                 // Abort unless condition true
```

---

## Complete CRUD Examples

### Create (Store)

```php
public function store(Request $request)
{
    $validated = $request->validate([
        'title' => 'required|max:255',
        'content' => 'required',
        'image' => 'nullable|image|max:2048',
    ]);

    if ($request->hasFile('image')) {
        $validated['image_path'] = $request->file('image')->store('posts', 'public');
    }

    $post = Post::create($validated);

    return redirect()
        ->route('posts.show', $post)
        ->with('success', 'Post created successfully!');
}
```

### Update

```php
public function update(Request $request, Post $post)
{
    $validated = $request->validate([
        'title' => 'required|max:255',
        'content' => 'required',
    ]);

    $post->update($validated);

    return redirect()
        ->route('posts.show', $post)
        ->with('success', 'Post updated successfully!');
}
```

### Delete

```php
public function destroy(Post $post)
{
    $post->delete();

    return redirect()
        ->route('posts.index')
        ->with('success', 'Post deleted successfully!');
}
```

### API Responses

```php
public function index()
{
    $posts = Post::published()->latest()->paginate(10);

    return response()->json([
        'success' => true,
        'data' => $posts,
    ]);
}

public function show(Post $post)
{
    return response()->json([
        'success' => true,
        'data' => $post,
    ]);
}

public function store(Request $request)
{
    $validated = $request->validate([
        'title' => 'required|max:255',
        'content' => 'required',
    ]);

    $post = Post::create($validated);

    return response()->json([
        'success' => true,
        'message' => 'Post created successfully',
        'data' => $post,
    ], 201);
}
```

---

## Form Request Classes

**For complex validation, extract to dedicated class:**

```bash
php artisan make:request StorePostRequest
```

**StorePostRequest.php:**
```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePostRequest extends FormRequest
{
    public function authorize()
    {
        return true;  // Or check permissions
    }

    public function rules()
    {
        return [
            'title' => 'required|max:255',
            'content' => 'required',
            'image' => 'nullable|image|max:2048',
        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'Please enter a post title.',
            'content.required' => 'Post content is required.',
        ];
    }
}
```

**Usage in controller:**
```php
public function store(StorePostRequest $request)
{
    // Automatically validated!
    $validated = $request->validated();

    $post = Post::create($validated);

    return redirect()->route('posts.show', $post);
}
```

**Benefits:**
- Cleaner controllers
- Reusable validation
- Easier testing
- Authorization logic included

---

## Summary

**What You Learned:**
- How to access request data (input, query, files)
- Checking input presence and type
- Handling file uploads
- Request validation
- Different response types (view, JSON, redirect, download)
- HTTP status codes
- Form Request classes for complex validation

**Key Takeaways:**
1. **Request object** contains all input data - no superglobals!
2. **`$request->validate()`** validates and returns clean data
3. **File uploads** are simple with `$request->file()`
4. **Responses are chainable** - add headers, cookies easily
5. **JSON responses** perfect for APIs
6. **Redirects with flash data** for user feedback
7. **Form Requests** organize complex validation

**Pure PHP vs Laravel:**
- Pure PHP: `$_POST`, `$_GET`, manual validation, manual headers
- Laravel: Request object, built-in validation, clean response methods

**Next Lesson:** We'll learn about Middleware - the filters between requests and responses!

---

## Practice Exercise

**Create a Post form with full request/response handling:**

1. **Create form** (`posts/create.blade.php`)
   - Title, content, image upload
   - CSRF token
   - Show validation errors
   - Repopulate with old input

2. **Store method** in controller
   - Validate input
   - Handle file upload
   - Create post
   - Redirect with success message

3. **API endpoint**
   - Create `api/posts` route
   - Return JSON response
   - Include pagination

---

## Quick Quiz

**1. How do you get all request input?**
```php
$request->all()
```

**2. How do you validate input?**
```php
$validated = $request->validate([...]);
```

**3. How do you handle file uploads?**
```php
if ($request->hasFile('image')) {
    $path = $request->file('image')->store('images', 'public');
}
```

**4. How do you return JSON?**
```php
return response()->json(['data' => $data]);
```

**5. How do you redirect with flash data?**
```php
return redirect()->route('posts.index')->with('success', 'Saved!');
```

**6. How do you return a 404?**
```php
abort(404);
// or
return response('Not Found', 404);
```

**7. What's a Form Request?**
- A dedicated class for complex validation rules

---

**Next**: [Lesson 10 - Middleware Basics →](10-middleware-basics.md)
