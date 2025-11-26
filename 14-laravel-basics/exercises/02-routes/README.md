# 02 - Routes and Controllers

## Objective
Learn how to create routes and controllers in Laravel, understanding the separation of concerns and how requests flow through the framework.

## Prerequisites
- Completed "01-first-app" exercise
- Understanding of HTTP methods (GET, POST, PUT, DELETE)
- Basic knowledge of routing concepts

## Instructions

### Step 1: Create Your First Controller
Use Artisan to generate a controller:

```bash
php artisan make:controller PostController
```

This creates `app/Http/Controllers/PostController.php`.

### Step 2: Add Controller Methods
Edit `app/Http/Controllers/PostController.php` and add methods:

```php
public function index()
{
    return 'List of all posts';
}

public function show($id)
{
    return "Post #{$id}";
}

public function create()
{
    return 'Create post form';
}

public function store()
{
    return 'Store post to database';
}
```

### Step 3: Define Routes
Open `routes/web.php` and replace the welcome route with:

```php
use App\Http\Controllers\PostController;

Route::get('/posts', [PostController::class, 'index']);
Route::get('/posts/{id}', [PostController::class, 'show']);
Route::get('/posts/create', [PostController::class, 'create']);
Route::post('/posts', [PostController::class, 'store']);
```

### Step 4: Use Resource Routes (Recommended)
Replace individual routes with:

```php
Route::resource('posts', PostController::class);
```

This automatically creates routes for:
- `GET /posts` → index
- `GET /posts/create` → create
- `POST /posts` → store
- `GET /posts/{id}` → show
- `GET /posts/{id}/edit` → edit
- `PUT/PATCH /posts/{id}` → update
- `DELETE /posts/{id}` → destroy

### Step 5: View All Routes
List all routes in your application:

```bash
php artisan route:list
```

### Step 6: Add Route Parameters
Modify the `show` method to handle different types:

```php
public function show(string $id)
{
    // $id is automatically injected by Laravel
    return "Viewing post #{$id}";
}
```

Test with URL: `/posts/42`

### Step 7: Create Additional Controllers
Create a `CommentController`:

```bash
php artisan make:controller CommentController
```

Add basic methods and define nested routes:

```php
Route::resource('posts.comments', CommentController::class);
```

### Step 8: Test Your Routes
- Visit each route in your browser
- Run `php artisan route:list` to verify all routes
- Test with different URL parameters

## Deliverables
- [ ] PostController created with all CRUD methods
- [ ] Routes defined (both individual and resource routes)
- [ ] CommentController created with resource routing
- [ ] Route list output showing all routes
- [ ] All routes accessible and returning expected responses
- [ ] Understanding of how URL parameters are passed to controllers

## Resources
- [Laravel Routing](https://laravel.com/docs/11.x/routing)
- [Laravel Controllers](https://laravel.com/docs/11.x/controllers)
- [Resource Controllers](https://laravel.com/docs/11.x/controllers#resource-controllers)

## Tips
- Use `php artisan route:list --verbose` for detailed route information
- Group related routes with `Route::group()` or `Route::prefix()`
- Use route names: `Route::get('/posts/{id}', [...])→name('posts.show')`
- Route model binding can automatically inject models based on parameters
