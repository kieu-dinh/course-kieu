# Deployment Strategies

**Duration**: 4-5 hours

---

## Introduction

Deploying changes to production is risky. A bad deployment can break your site, lose data, or cause downtime. In this lesson, you'll learn professional deployment strategies that minimize risk and enable quick rollbacks.

**What you'll learn:**
- Zero-downtime deployment
- Deployment strategies (basic, blue-green, rolling)
- Git-based deployment workflow
- Database migration strategies
- Rollback procedures
- Deployment automation with scripts

---

## Understanding Deployment

### What Happens During Deployment?

**Typical deployment steps:**
1. Pull new code from Git
2. Install/update dependencies (Composer, NPM)
3. Run database migrations
4. Clear and rebuild cache
5. Compile assets
6. Restart services (queue workers, PHP-FPM)

**The problem:** During these steps, your site might:
- Serve old code with new database schema (broken)
- Show errors while dependencies install
- Have cached routes pointing to deleted files

### Deployment Goals

**1. Zero downtime** - Site stays available during deployment
**2. Atomic** - All or nothing (no half-deployed state)
**3. Reversible** - Can rollback quickly if something breaks
**4. Automated** - Reduce human error
**5. Tested** - Verify deployment succeeded

---

## Basic Deployment Strategy

### Naive Approach (Don't Do This!)

```bash
# SSH to server
ssh deployer@server

cd /var/www/myapp

# Pull code
git pull origin main

# Update dependencies
composer install

# Migrate database
php artisan migrate

# Clear cache
php artisan cache:clear
php artisan config:cache
```

**Problems:**
- Site serves broken pages during installation
- If migration fails, site is broken
- No easy rollback
- Manual process (error-prone)

---

## Releases Strategy (Recommended)

### Directory Structure

```
/home/deployer/myapp/
├── current -> releases/20250115120000/  (symlink)
├── releases/
│   ├── 20250115120000/                  (latest)
│   ├── 20250114100000/
│   └── 20250113090000/
└── shared/
    ├── .env
    ├── storage/
    │   ├── app/
    │   ├── framework/
    │   └── logs/
    └── public/
        └── uploads/
```

**How it works:**
1. Deploy to new release directory
2. Symlink `shared` files
3. Run migrations
4. Switch `current` symlink to new release
5. Old releases kept for rollback

**Benefits:**
- Near-instant switch (atomic)
- Easy rollback (change symlink)
- Shared files persist across deployments
- Keep last 5 releases for quick rollback

---

## Manual Deployment with Releases

### Step 1: Initial Setup

```bash
# SSH to server
ssh deployer@server

# Create directory structure
mkdir -p /home/deployer/myapp/{releases,shared}
mkdir -p /home/deployer/myapp/shared/{storage/{app,framework,logs},public/uploads}

# Create .env in shared
cp /path/to/.env /home/deployer/myapp/shared/.env

# Set permissions
chmod 775 -R /home/deployer/myapp/shared/storage
```

### Step 2: Deploy Script

**Create:** `/home/deployer/myapp/deploy.sh`

```bash
#!/bin/bash

set -e  # Exit on error

# Configuration
APP_DIR="/home/deployer/myapp"
REPO_URL="git@github.com:yourorg/yourapp.git"
BRANCH="main"
KEEP_RELEASES=5

# Create release name (timestamp)
RELEASE=$(date +%Y%m%d%H%M%S)
RELEASE_DIR="$APP_DIR/releases/$RELEASE"

echo "🚀 Starting deployment: $RELEASE"

# 1. Clone repository
echo "📦 Cloning repository..."
git clone --depth 1 --branch $BRANCH $REPO_URL $RELEASE_DIR

cd $RELEASE_DIR

# 2. Create symlinks to shared files
echo "🔗 Creating symlinks..."
rm -rf storage
ln -s $APP_DIR/shared/storage storage

rm -f .env
ln -s $APP_DIR/shared/.env .env

rm -rf public/uploads
ln -s $APP_DIR/shared/public/uploads public/uploads

# 3. Install dependencies
echo "📥 Installing dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

# 4. Build assets
echo "🎨 Building assets..."
npm ci --production
npm run build
rm -rf node_modules  # Don't need these in production

# 5. Optimize Laravel
echo "⚡ Optimizing Laravel..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 6. Run migrations
echo "🗄️  Running migrations..."
php artisan migrate --force

# 7. Switch current symlink
echo "🔄 Switching to new release..."
ln -sfn $RELEASE_DIR $APP_DIR/current

# 8. Reload PHP-FPM
echo "♻️  Reloading PHP-FPM..."
sudo systemctl reload php8.2-fpm

# 9. Restart queue workers
echo "👷 Restarting queue workers..."
php artisan queue:restart

# 10. Cleanup old releases
echo "🧹 Cleaning up old releases..."
cd $APP_DIR/releases
ls -t | tail -n +$((KEEP_RELEASES + 1)) | xargs -r rm -rf

echo "✅ Deployment complete: $RELEASE"
echo "🌐 Site: https://yourdomain.com"
```

**Make executable:**
```bash
chmod +x /home/deployer/myapp/deploy.sh
```

### Step 3: nginx Configuration

**Point nginx to `current` symlink:**

```nginx
server {
    listen 443 ssl http2;
    server_name yourdomain.com;

    # Point to current release
    root /home/deployer/myapp/current/public;

    # ... rest of configuration
}
```

**Reload nginx:**
```bash
sudo nginx -t
sudo systemctl reload nginx
```

### Step 4: Deploy

```bash
# SSH to server
ssh deployer@server

# Run deployment
/home/deployer/myapp/deploy.sh
```

### Step 5: Rollback (if needed)

```bash
#!/bin/bash
# rollback.sh

APP_DIR="/home/deployer/myapp"

# Get previous release
CURRENT=$(readlink $APP_DIR/current)
PREVIOUS=$(ls -t $APP_DIR/releases | grep -A1 $(basename $CURRENT) | tail -1)

if [ -z "$PREVIOUS" ]; then
    echo "❌ No previous release found"
    exit 1
fi

echo "⏪ Rolling back to: $PREVIOUS"

# Switch symlink
ln -sfn $APP_DIR/releases/$PREVIOUS $APP_DIR/current

# Reload services
sudo systemctl reload php8.2-fpm
php artisan queue:restart

echo "✅ Rollback complete"
```

---

## Zero-Downtime Database Migrations

### The Problem

**Dangerous migration:**
```php
Schema::table('users', function (Blueprint $table) {
    $table->dropColumn('old_name');
    $table->string('new_name');
});
```

**What happens:**
1. Migration runs
2. Old code expects `old_name` column
3. **Site breaks!**
4. New code deployed
5. Site works again

**Downtime:** Several minutes

### Solution: Multi-Step Migrations

**Step 1: Add new column (keep old)**
```php
// Migration 1 - Deploy first
Schema::table('users', function (Blueprint $table) {
    $table->string('new_name')->nullable()->after('old_name');
});
```

Deploy, test, wait 24 hours (can rollback safely).

**Step 2: Update code to use new column**
```php
// Before
$user->old_name = 'Alice';

// After
$user->new_name = 'Alice';
$user->old_name = 'Alice'; // Still update old for safety
```

Deploy, test, wait 24 hours.

**Step 3: Drop old column**
```php
// Migration 2 - Deploy later
Schema::table('users', function (Blueprint $table) {
    $table->dropColumn('old_name');
});
```

Deploy safely - no code uses old column anymore.

### Safe Migration Patterns

**Adding columns: ✅ Safe**
```php
$table->string('new_column')->nullable();
```

**Dropping columns: ⚠️ Multi-step**
1. Stop using in code
2. Wait for deployment
3. Drop column

**Renaming columns: ⚠️ Multi-step**
1. Add new column
2. Populate new column
3. Update code
4. Drop old column

**Adding indexes: ✅ Safe** (but slow on large tables)
```php
$table->index('email'); // Can lock table briefly
```

**Dropping indexes: ✅ Safe**
```php
$table->dropIndex('users_email_index');
```

---

## Deployment Strategies

### 1. Basic Strategy

Single server, direct deployment:

```
Old Version → Deploy → New Version
            ↓
        Downtime
```

**Pros:** Simple
**Cons:** Downtime during deployment

### 2. Blue-Green Deployment

Two identical environments:

```
Blue (Live) ──┐
              ├─→ Load Balancer → Users
Green (Idle) ─┘

Deploy to Green, test, switch traffic:

Blue (Idle) ──┐
              ├─→ Load Balancer → Users
Green (Live) ─┘
```

**How it works:**
1. Green is idle
2. Deploy to Green
3. Test Green
4. Switch load balancer to Green
5. Blue becomes idle (quick rollback if needed)

**Pros:** Zero downtime, instant rollback
**Cons:** Requires 2x infrastructure

### 3. Rolling Deployment

Multiple servers, deploy one at a time:

```
Server 1: Old → Deploy → New ✓
Server 2: Old → Deploy → New ✓
Server 3: Old → Deploy → New ✓
```

**How it works:**
1. Remove Server 1 from load balancer
2. Deploy to Server 1
3. Add Server 1 back to load balancer
4. Repeat for Server 2, 3, etc.

**Pros:** Zero downtime, no extra infrastructure
**Cons:** Mixed versions during deployment

### 4. Canary Deployment

Deploy to small subset of users first:

```
5% traffic → New Version (test)
95% traffic → Old Version (safe)

If OK:
100% traffic → New Version
```

**Pros:** Test in production with real traffic, low risk
**Cons:** Complex setup

---

## Automated Deployment

### Using Laravel Forge

**Laravel Forge** manages servers and automates deployment.

**Setup:**
1. Connect Forge to DigitalOcean account
2. Create server in Forge
3. Add site and link to Git repository
4. Configure deployment script

**Deployment script (Forge):**
```bash
cd /home/forge/yourdomain.com

git pull origin main

composer install --no-dev --optimize-autoloader

php artisan migrate --force

php artisan config:cache
php artisan route:cache
php artisan view:cache

php artisan queue:restart

npm ci --production
npm run build
```

**Deploy:**
- Push to GitHub
- Forge automatically deploys (if auto-deploy enabled)
- Or click "Deploy Now" in Forge dashboard

**Benefits:**
- Zero downtime (Forge uses releases strategy)
- Easy rollback (click "Rollback")
- Deployment history
- Scheduled deployments
- Slack notifications

### Using Deployer

**Deployer** is a PHP deployment tool.

**Install:**
```bash
composer require --dev deployer/deployer
```

**Create:** `deploy.php`
```php
<?php
namespace Deployer;

require 'recipe/laravel.php';

// Configuration
set('application', 'My Laravel App');
set('repository', 'git@github.com:yourorg/yourapp.git');
set('keep_releases', 5);

// Hosts
host('production')
    ->set('remote_user', 'deployer')
    ->set('hostname', 'yourdomain.com')
    ->set('deploy_path', '/home/deployer/myapp');

// Tasks
task('deploy:secrets', function () {
    upload('.env.production', '{{deploy_path}}/shared/.env');
});

// Deployment flow
before('deploy:symlink', 'artisan:migrate');
after('deploy:failed', 'deploy:unlock');

// Main deployment command
desc('Deploy the application');
task('deploy', [
    'deploy:prepare',
    'deploy:vendors',
    'artisan:storage:link',
    'artisan:config:cache',
    'artisan:route:cache',
    'artisan:view:cache',
    'artisan:migrate',
    'deploy:publish',
]);
```

**Deploy:**
```bash
# From local machine
dep deploy production
```

### Using GitHub Actions

**Automated deployment on push:**

**.github/workflows/deploy.yml:**
```yaml
name: Deploy to Production

on:
  push:
    branches: [ main ]

jobs:
  deploy:
    runs-on: ubuntu-latest

    steps:
    - name: Checkout code
      uses: actions/checkout@v3

    - name: Deploy to server
      uses: appleboy/ssh-action@master
      with:
        host: ${{ secrets.HOST }}
        username: ${{ secrets.USERNAME }}
        key: ${{ secrets.SSH_KEY }}
        script: |
          cd /home/deployer/myapp
          ./deploy.sh
```

**Setup:**
1. Add secrets in GitHub (Settings → Secrets)
2. Push to `main` branch
3. GitHub Actions automatically deploys

---

## Health Checks

### After Deployment Verification

**Check critical functionality:**

**create:** `health-check.sh`
```bash
#!/bin/bash

URL="https://yourdomain.com"

# Check HTTP status
STATUS=$(curl -s -o /dev/null -w "%{http_code}" $URL)

if [ $STATUS -eq 200 ]; then
    echo "✅ Site is up: $STATUS"
else
    echo "❌ Site is down: $STATUS"
    exit 1
fi

# Check database
if ssh deployer@server "cd /home/deployer/myapp/current && php artisan tinker --execute='DB::connection()->getPdo()'"; then
    echo "✅ Database connected"
else
    echo "❌ Database connection failed"
    exit 1
fi

# Check queue workers
if ssh deployer@server "ps aux | grep -v grep | grep 'queue:work' > /dev/null"; then
    echo "✅ Queue workers running"
else
    echo "⚠️  Queue workers not running"
fi

echo "🎉 Health check passed"
```

### Laravel Health Check Endpoint

**Create health check route:**

**routes/web.php:**
```php
Route::get('/health', function () {
    $checks = [
        'database' => false,
        'cache' => false,
        'storage' => false,
    ];

    // Check database
    try {
        DB::connection()->getPdo();
        $checks['database'] = true;
    } catch (\Exception $e) {
        //
    }

    // Check cache
    try {
        Cache::put('health-check', true, 10);
        $checks['cache'] = Cache::get('health-check') === true;
    } catch (\Exception $e) {
        //
    }

    // Check storage
    $checks['storage'] = Storage::exists('public');

    $healthy = array_reduce($checks, fn($carry, $check) => $carry && $check, true);

    return response()->json([
        'status' => $healthy ? 'healthy' : 'unhealthy',
        'checks' => $checks,
    ], $healthy ? 200 : 503);
});
```

**Usage:**
```bash
curl https://yourdomain.com/health

# Output:
{
  "status": "healthy",
  "checks": {
    "database": true,
    "cache": true,
    "storage": true
  }
}
```

---

## Rollback Procedures

### When to Rollback

**Immediate rollback if:**
- Critical functionality broken
- Data loss occurring
- Security vulnerability introduced
- Site completely down

**Consider rollback if:**
- Performance degraded significantly
- Major bugs affecting many users
- Database migration failed

**Don't rollback for:**
- Minor cosmetic issues
- Edge case bugs
- Issues fixable with hotfix

### Quick Rollback

**With releases strategy:**
```bash
# Rollback to previous release
cd /home/deployer/myapp
ln -sfn releases/20250114100000 current
sudo systemctl reload php8.2-fpm
php artisan queue:restart

# Verify
curl https://yourdomain.com/health
```

**With Git:**
```bash
cd /home/deployer/myapp
git reset --hard HEAD~1  # Go back one commit
composer install
php artisan migrate:rollback
php artisan config:cache
sudo systemctl reload php8.2-fpm
```

### Database Rollback

**If migration can't rollback automatically:**

**Option 1: Restore backup**
```bash
# Restore yesterday's backup
mysql -u root -p laravel_db < backups/laravel_db_2025-01-14.sql
```

**Option 2: Manual fix**
```bash
# SSH to server
php artisan tinker

# Manually fix data or schema
```

**Option 3: Forward fix**
```bash
# Create emergency migration to fix issue
php artisan make:migration fix_broken_migration
# Edit migration
php artisan migrate
```

---

## Deployment Checklist

```
Pre-Deployment:
□ Code reviewed and merged
□ All tests passing
□ Feature tested locally
□ Staging deployment successful
□ Database backup created
□ Deployment window scheduled (if needed)
□ Team notified

During Deployment:
□ Run deployment script
□ Monitor logs for errors
□ Check health endpoint
□ Test critical functionality
□ Verify database migrations succeeded
□ Check queue workers restarted

Post-Deployment:
□ Smoke test major features
□ Check error logs
□ Monitor application performance
□ Watch error tracking (Sentry, etc.)
□ Verify no spike in errors
□ Update team on success
□ Document any issues

Rollback (if needed):
□ Stop deployment immediately
□ Rollback to previous release
□ Restore database if needed
□ Verify site working
□ Investigate issue
□ Create hotfix or plan re-deployment
```

---

## Quick Reference

```bash
# Deploy with releases strategy
./deploy.sh

# Rollback to previous release
./rollback.sh

# Check deployment status
curl https://yourdomain.com/health

# View deployment logs
tail -f /home/deployer/myapp/current/storage/logs/laravel.log

# Restart services
sudo systemctl reload php8.2-fpm
php artisan queue:restart
sudo systemctl reload nginx

# Database backup before deployment
mysqldump -u root -p laravel_db > backup-$(date +%Y%m%d).sql
```

---

## Practice Questions

1. **Why is the releases strategy better than deploying directly?**

2. **What is zero-downtime deployment?** How do you achieve it?

3. **Why are multi-step migrations important?** Give an example.

4. **What's the difference between blue-green and rolling deployment?**

5. **When should you rollback a deployment?** When shouldn't you?

6. **What should you check after deploying?**

---

## Next Steps

Now you can deploy safely! Next, you'll learn about monitoring and logging to catch issues quickly.

**Coming up in Lesson 10:**
- Application monitoring
- Error tracking (Sentry)
- Log management
- Performance monitoring
- Uptime monitoring
- Alerting strategies
