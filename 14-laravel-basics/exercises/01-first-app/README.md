# 01 - First Laravel App

## Objective
Install Laravel and explore the basic project structure to understand how a modern Laravel application is organized.

## Prerequisites
- PHP 8.2 or higher installed
- Composer installed
- Basic knowledge of terminal/command line
- Understanding of MVC pattern (from previous modules)

## Instructions

### Step 1: Install Laravel
Create a new Laravel project using Composer:

```bash
composer create-project laravel/laravel first-app
cd first-app
```

### Step 2: Explore Project Structure
Navigate through the project and examine:
- `app/` - Application code (Models, Controllers, Middleware)
- `routes/` - Route definitions (web.php, api.php)
- `resources/` - Views and frontend assets
- `database/` - Migrations and seeders
- `public/` - Entry point and static assets
- `config/` - Application configuration
- `storage/` - Cache, logs, and sessions
- `bootstrap/` - Framework bootstrapping

### Step 3: Run the Development Server
Start the Laravel development server:

```bash
php artisan serve
```

Visit `http://localhost:8000` in your browser to see the welcome page.

### Step 4: Check the Welcome Route
Open `routes/web.php` and examine the welcome route:

```php
Route::get('/', function () {
    return view('welcome');
});
```

### Step 5: Explore the View
Look at `resources/views/welcome.blade.php` to understand Blade template syntax.

### Step 6: Create Your First Route
Add a simple route in `routes/web.php`:

```php
Route::get('/hello', function () {
    return 'Hello, Laravel!';
});
```

Visit `http://localhost:8000/hello` to see your custom route.

## Deliverables
- [ ] Laravel project successfully created
- [ ] Development server running without errors
- [ ] Welcome page accessible at `http://localhost:8000`
- [ ] Custom `/hello` route created and working
- [ ] Screenshot of project structure in your IDE
- [ ] Brief notes on directory purposes

## Resources
- [Laravel Official Documentation](https://laravel.com/docs)
- [Laravel Installation Guide](https://laravel.com/docs/11.x/installation)
- [Laravel Directory Structure](https://laravel.com/docs/11.x/structure)

## Tips
- Use `php artisan` command to explore available artisan commands
- The `artisan` tool is your best friend for scaffolding and development
- Keep the development server running in a terminal while working
- Check logs in `storage/logs/` if something goes wrong
