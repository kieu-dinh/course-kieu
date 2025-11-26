# Environment Configuration

**Duration**: 3-4 hours

---

## Introduction

Your application behaves differently in development versus production. Environment configuration allows you to manage settings for different environments (local, staging, production) safely and securely.

**What you'll learn:**
- Understanding .env files
- Managing environment variables
- Configuration caching
- Multi-environment setup
- Managing secrets securely
- Environment-specific features

---

## Understanding Environment Variables

### What Are Environment Variables?

Environment variables store sensitive or environment-specific configuration outside your code:

**Examples:**
- Database credentials
- API keys
- Debug mode settings
- Cache drivers
- Mail server configuration

**Why not hardcode these?**
- ❌ Exposes secrets in Git
- ❌ Different values needed per environment
- ❌ Can't change without deploying code
- ❌ Security risk

### The .env File

Laravel uses `.env` files to store environment variables:

```
APP_NAME=Laravel
APP_ENV=local
APP_KEY=base64:xxx...
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=root
DB_PASSWORD=
```

**Important rules:**
1. Never commit `.env` to Git (it's in `.gitignore`)
2. Commit `.env.example` as a template
3. Each environment has its own `.env` file

---

## Environment-Specific Configuration

### Development (.env - Local)

```
APP_NAME="My Laravel App"
APP_ENV=local
APP_KEY=base64:generated-key-here
APP_DEBUG=true
APP_URL=http://myapp.test

LOG_CHANNEL=stack
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_dev
DB_USERNAME=root
DB_PASSWORD=

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file

MAIL_MAILER=log
MAIL_FROM_ADDRESS="dev@example.com"
MAIL_FROM_NAME="${APP_NAME}"
```

**Key settings for development:**
- `APP_ENV=local`
- `APP_DEBUG=true` (show detailed errors)
- `QUEUE_CONNECTION=sync` (run jobs immediately)
- `MAIL_MAILER=log` (don't send real emails)

### Staging (.env - Staging Server)

```
APP_NAME="My Laravel App (Staging)"
APP_ENV=staging
APP_KEY=base64:different-key-than-production
APP_DEBUG=true
APP_URL=https://staging.myapp.com

LOG_CHANNEL=daily
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_staging
DB_USERNAME=laravel_staging_user
DB_PASSWORD=secure-password-here

BROADCAST_DRIVER=redis
CACHE_DRIVER=redis
FILESYSTEM_DISK=s3
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your-mailtrap-username
MAIL_PASSWORD=your-mailtrap-password
MAIL_FROM_ADDRESS="staging@example.com"

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

**Key settings for staging:**
- `APP_ENV=staging`
- `APP_DEBUG=true` (still debugging, but on server)
- `CACHE_DRIVER=redis` (production-like caching)
- `MAIL_MAILER=smtp` with Mailtrap (test emails safely)
- Real queue workers

### Production (.env - Production Server)

```
APP_NAME="My Laravel App"
APP_ENV=production
APP_KEY=base64:unique-production-key-here
APP_DEBUG=false
APP_URL=https://myapp.com

LOG_CHANNEL=daily
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_production
DB_USERNAME=laravel_prod_user
DB_PASSWORD=very-secure-password-here

BROADCAST_DRIVER=redis
CACHE_DRIVER=redis
FILESYSTEM_DISK=s3
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

MAIL_MAILER=smtp
MAIL_HOST=smtp.mailgun.org
MAIL_PORT=587
MAIL_USERNAME=your-mailgun-username
MAIL_PASSWORD=your-mailgun-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@myapp.com"
MAIL_FROM_NAME="${APP_NAME}"

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=secure-redis-password
REDIS_PORT=6379

AWS_ACCESS_KEY_ID=your-aws-key
AWS_SECRET_ACCESS_KEY=your-aws-secret
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=your-s3-bucket

PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_APP_CLUSTER=mt1
```

**Key settings for production:**
- `APP_ENV=production`
- `APP_DEBUG=false` (never show errors to users!)
- `LOG_LEVEL=error` (only log errors, not debug info)
- Redis for cache, sessions, queues
- Real email service
- S3 for file storage
- Strong passwords everywhere

---

## Using Environment Variables

### In Configuration Files

**config/database.php:**
```php
'mysql' => [
    'driver' => 'mysql',
    'host' => env('DB_HOST', '127.0.0.1'),
    'port' => env('DB_PORT', '3306'),
    'database' => env('DB_DATABASE', 'forge'),
    'username' => env('DB_USERNAME', 'forge'),
    'password' => env('DB_PASSWORD', ''),
    // ...
],
```

**The `env()` helper:**
```php
env('KEY', 'default')
```

- First argument: Environment variable name
- Second argument: Default value if not set

### In Your Code

**❌ Bad: Using env() directly**
```php
// In a controller
if (env('APP_DEBUG')) {
    // This won't work with config caching!
}
```

**✅ Good: Use config() instead**
```php
// In a controller
if (config('app.debug')) {
    // This works even with config caching
}
```

**Why?** When you cache config, `env()` returns `null` everywhere except config files.

### Creating Custom Configuration

**config/services.php:**
```php
return [
    'stripe' => [
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    ],

    'mailchimp' => [
        'key' => env('MAILCHIMP_KEY'),
        'list_id' => env('MAILCHIMP_LIST_ID'),
    ],
];
```

**.env:**
```
STRIPE_KEY=pk_test_xxx
STRIPE_SECRET=sk_test_xxx
STRIPE_WEBHOOK_SECRET=whsec_xxx

MAILCHIMP_KEY=xxx
MAILCHIMP_LIST_ID=xxx
```

**Usage in code:**
```php
use Stripe\Stripe;

Stripe::setApiKey(config('services.stripe.secret'));
```

---

## Configuration Caching

### Why Cache Configuration?

**Without caching:**
- Laravel reads all config files on every request
- Reads `.env` file
- Parses and processes all configurations
- Slow!

**With caching:**
- Configuration compiled into single cached file
- One read instead of dozens
- Much faster!

### Cache Configuration (Production Only)

```bash
# Cache configuration
php artisan config:cache

# This creates: bootstrap/cache/config.php
```

**Performance impact:**
- Without cache: ~50ms to load config
- With cache: ~2ms to load config
- **25x faster!**

### Important: env() Won't Work

After caching, `env()` returns `null` outside config files:

```php
// ❌ Bad: Won't work with cached config
$apiKey = env('API_KEY');

// ✅ Good: Always works
$apiKey = config('services.api.key');
```

**Solution:** Always use `config()` in your application code.

### Clear Configuration Cache

After updating `.env` or config files:

```bash
php artisan config:clear
```

Or rebuild cache:
```bash
php artisan config:cache
```

### In Development

Don't cache in development! You want changes to take effect immediately.

```bash
# Clear cache if accidentally cached
php artisan config:clear
```

---

## Managing Secrets Securely

### 1. Never Commit Secrets

**.gitignore (Laravel includes this by default):**
```
.env
.env.backup
.env.production
.phpunit.result.cache
```

**Verify:**
```bash
# This should show .env is ignored
git status
```

### 2. Use Strong Secrets

**Generate application key:**
```bash
php artisan key:generate
```

**Generate random strings (for API tokens, etc.):**
```php
use Illuminate\Support\Str;

$token = Str::random(60);
// Output: yYi9Q3kk2V8zRQ5ZXRsqNKLzVbHhKBYMNF4PQ1BqF8FVXr1
```

Or in terminal:
```bash
php artisan tinker
>>> Str::random(60)
```

### 3. Rotate Secrets Regularly

**When to rotate:**
- Every 90 days (general practice)
- When employee leaves
- After security incident
- When credentials may be exposed

**How to rotate:**
1. Generate new secret
2. Update `.env` on server
3. Restart application
4. Test
5. Revoke old secret

### 4. Use Different Secrets Per Environment

Never use the same:
- App key
- Database password
- API keys
- Encryption keys

across environments.

### 5. Limit Secret Access

**Who should have access to production secrets?**
- Senior developers only
- DevOps team
- CTO/Tech lead

**Not:**
- Junior developers
- Contractors (unless necessary)
- Anyone who doesn't need them

### 6. Secret Management Tools

**For teams:**

**AWS Secrets Manager:**
```php
// Store secrets in AWS, not .env
$secret = AWS::secretsManager()->getSecretValue([
    'SecretId' => 'production/laravel/db',
]);
```

**HashiCorp Vault:**
```php
// Enterprise-grade secret management
$credentials = Vault::read('secret/data/database');
```

**1Password CLI:**
```bash
# Store secrets in 1Password
op inject -i .env.template -o .env
```

**For small teams:**
- Use encrypted password manager (1Password, LastPass)
- Share .env files through secure channel
- Document who has access

---

## Environment Detection

### Check Current Environment

```php
use Illuminate\Support\Facades\App;

// Get environment name
$env = App::environment(); // 'local', 'staging', 'production'

// Check specific environment
if (App::environment('local')) {
    // Only in local
}

if (App::environment('production')) {
    // Only in production
}

// Check multiple environments
if (App::environment(['staging', 'production'])) {
    // In staging OR production
}
```

### Environment-Specific Code

```php
// AppServiceProvider.php
public function boot()
{
    // Only in production
    if (App::environment('production')) {
        URL::forceScheme('https'); // Force HTTPS
    }

    // Only in development
    if (App::environment('local')) {
        Model::preventLazyLoading(); // Prevent N+1
    }
}
```

**In Blade templates:**
```blade
@env('local')
    {{-- Debug toolbar, only in development --}}
    <div class="debug-toolbar">
        <p>Queries: {{ count(DB::getQueryLog()) }}</p>
    </div>
@endenv

@production
    {{-- Analytics, only in production --}}
    <script src="https://analytics.example.com/track.js"></script>
@endproduction
```

---

## Multi-Environment Workflow

### Local Development

**Developer A:**
```
# .env
APP_NAME="Laravel App - Alice"
APP_URL=http://laravel.test

DB_DATABASE=laravel_alice
MAIL_MAILER=log
```

**Developer B:**
```
# .env
APP_NAME="Laravel App - Bob"
APP_URL=http://myapp.test

DB_DATABASE=laravel_bob
MAIL_MAILER=log
```

Each developer has their own `.env`, but shares `.env.example`.

### .env.example Template

**Commit this to Git:**
```
APP_NAME=Laravel
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=root
DB_PASSWORD=

# Add comments for clarity
# Get Stripe keys from: https://dashboard.stripe.com/apikeys
STRIPE_KEY=
STRIPE_SECRET=

# Get Mailchimp key from: https://us1.admin.mailchimp.com/account/api/
MAILCHIMP_KEY=
```

### Setting Up New Environment

**When new developer joins:**
```bash
# Clone repository
git clone https://github.com/yourorg/laravel-app.git
cd laravel-app

# Copy example env
cp .env.example .env

# Install dependencies
composer install
npm install

# Generate key
php artisan key:generate

# Create database
php artisan migrate

# Seed data
php artisan db:seed
```

---

## Environment-Specific Features

### Debug Mode

**Development:**
```
APP_DEBUG=true
```
Shows detailed error messages with stack traces.

**Production:**
```
APP_DEBUG=false
```
Shows user-friendly error page, logs details.

### Logging

**Development:**
```
LOG_CHANNEL=stack
LOG_LEVEL=debug
```
Log everything to help with debugging.

**Production:**
```
LOG_CHANNEL=daily
LOG_LEVEL=error
```
Only log errors, rotate logs daily.

**Available channels:**
- `stack`: Multiple channels
- `single`: Single file (storage/logs/laravel.log)
- `daily`: New file each day, rotates after 7 days
- `slack`: Send to Slack
- `syslog`: System log
- `errorlog`: PHP error log

### Cache Drivers

**Development:**
```
CACHE_DRIVER=file
```
Simple, no setup required.

**Production:**
```
CACHE_DRIVER=redis
```
Fast, scalable, supports cache tags.

### Queue Drivers

**Development:**
```
QUEUE_CONNECTION=sync
```
Jobs run immediately, easy to debug.

**Production:**
```
QUEUE_CONNECTION=redis
```
Jobs run in background, need queue workers.

### Mail Drivers

**Development:**
```
MAIL_MAILER=log
```
Emails written to log file, not sent.

**Staging:**
```
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
```
Use Mailtrap to test emails safely.

**Production:**
```
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailgun.org
```
Use real email service.

---

## Deployment Environment Setup

### On Server

**1. Create .env file:**
```bash
# SSH to server
ssh deployer@yourserver.com

# Navigate to app
cd /home/deployer/laravel-app

# Create .env from example
cp .env.example .env

# Edit .env
nano .env
```

**2. Set production values:**
```
APP_ENV=production
APP_DEBUG=false
DB_PASSWORD=secure-production-password
# ... etc
```

**3. Generate key:**
```bash
php artisan key:generate
```

**4. Set permissions:**
```bash
chmod 600 .env
chown deployer:www-data .env
```

### Deployment Script

Include in your deployment process:

```bash
#!/bin/bash

# Pull latest code
git pull origin main

# Install dependencies
composer install --no-dev --optimize-autoloader

# Run migrations
php artisan migrate --force

# Clear and cache config
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Restart queue workers
php artisan queue:restart

# Reload PHP-FPM
sudo systemctl reload php8.2-fpm
```

**Never run in deployment:**
```bash
# ❌ Don't do these in production
php artisan config:clear  # Removes optimization
php artisan cache:clear   # Removes all cached data
```

---

## Common Pitfalls

### 1. Using env() Outside Config

```php
// ❌ Bad: Won't work with config cache
public function store()
{
    if (env('FEATURE_ENABLED')) {
        // ...
    }
}

// ✅ Good: Always use config()
public function store()
{
    if (config('features.enabled')) {
        // ...
    }
}
```

### 2. Forgetting to Clear Cache

After changing `.env`:
```bash
php artisan config:clear  # In development
php artisan config:cache  # In production
```

### 3. Committing .env to Git

```bash
# Check what you're committing
git status

# If .env is showing:
echo ".env" >> .gitignore
git rm --cached .env
git commit -m "Remove .env from repository"
```

### 4. Same Secrets Across Environments

Each environment needs unique:
- APP_KEY
- Database passwords
- API keys

### 5. Debug Mode in Production

```php
// ❌ NEVER in production
APP_DEBUG=true

// ✅ Always in production
APP_DEBUG=false
```

Exposing debug info is a security risk!

---

## Testing with Environments

### PHPUnit Configuration

**phpunit.xml:**
```xml
<php>
    <env name="APP_ENV" value="testing"/>
    <env name="DB_CONNECTION" value="sqlite"/>
    <env name="DB_DATABASE" value=":memory:"/>
    <env name="CACHE_DRIVER" value="array"/>
    <env name="SESSION_DRIVER" value="array"/>
    <env name="QUEUE_CONNECTION" value="sync"/>
    <env name="MAIL_MAILER" value="array"/>
</php>
```

**Why?**
- Tests run in isolated environment
- Use in-memory SQLite (fast, clean state)
- No real emails sent
- No real cache/sessions

### Feature Tests

```php
public function test_sends_email_in_production()
{
    // Fake production environment
    config(['app.env' => 'production']);
    config(['app.debug' => false]);

    Mail::fake();

    $this->post('/contact', [
        'email' => 'test@example.com',
        'message' => 'Hello',
    ]);

    Mail::assertSent(ContactEmail::class);
}
```

---

## Quick Reference

```bash
# Configuration commands
php artisan config:cache     # Cache config (production)
php artisan config:clear     # Clear cache (development)
php artisan config:show      # Show all config values

# Generate keys
php artisan key:generate     # Generate APP_KEY

# Check environment
php artisan tinker
>>> app()->environment()     # Show current environment
>>> config('app.env')        # Same
```

```php
// In code
App::environment()                          // Get env name
App::environment('production')              // Check if production
App::environment(['staging', 'production']) // Check multiple

config('app.debug')                         // Get config value
config(['app.name' => 'New Name'])          // Set config at runtime
```

---

## Practice Questions

1. **What's the difference between .env and .env.example?** Why commit one but not the other?

2. **When should you use env() vs config()?** Why does this matter?

3. **What happens when you run config:cache?** Why should you never use it in development?

4. **Why should APP_DEBUG be false in production?** What are the risks?

5. **How do you securely share environment variables with your team?**

6. **What's the purpose of different cache drivers per environment?**

---

## Next Steps

Now you understand environment configuration! Next, you'll learn about SSL certificates and securing your application with HTTPS.

**Coming up in Lesson 08:**
- SSL/TLS certificates
- Let's Encrypt (free SSL)
- HTTPS configuration
- Mixed content issues
- Certificate renewal
