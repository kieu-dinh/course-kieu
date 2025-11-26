# Exercise 20.4 - Deploy to Production

## Objective

Learn how to deploy a Laravel application to production securely and reliably.

---

## Task

Prepare and deploy your Laravel application to a production environment.

### Requirements

1. **Environment Setup**
   - Configure production environment variables
   - Set up database on production server
   - Configure file storage
   - Set up proper permissions

2. **Application Preparation**
   - Optimize application for production
   - Build assets (if using mix/vite)
   - Generate application key
   - Run migrations

3. **Web Server Setup**
   - Configure Nginx or Apache
   - Set up SSL/HTTPS
   - Configure proper headers
   - Set up caching

4. **Database Setup**
   - Create production database
   - Set up backups
   - Configure replication (if needed)
   - Create monitoring

5. **Deployment Script**
   - Create automated deployment script
   - Test zero-downtime deployment
   - Implement rollback mechanism
   - Monitor deployment

---

## Hosting Options

### 1. Traditional Server (VPS)

Providers: DigitalOcean, Linode, AWS EC2, Vultr

Pros:
- Full control
- Cost-effective
- Flexible

Cons:
- Manual server management
- Security responsibility
- More setup required

### 2. Managed Hosting

Providers: Laravel Forge, Heroku, PlanetScale

Pros:
- Easy deployment
- Automatic backups
- Security managed
- Scaling built-in

Cons:
- Higher cost
- Less control
- Vendor lock-in

### 3. Serverless

Providers: AWS Lambda, Google Cloud Functions

Pros:
- Pay-per-use
- Automatic scaling
- No server management

Cons:
- Limited to certain use cases
- Cold starts
- Complex setup

---

## Production Checklist

### Environment Variables

```bash
# .env (production)
APP_NAME="My Application"
APP_ENV=production
APP_KEY=base64:generated_key_here
APP_DEBUG=false
APP_URL=https://example.com

DB_CONNECTION=mysql
DB_HOST=db.example.com
DB_PORT=3306
DB_DATABASE=prod_db
DB_USERNAME=db_user
DB_PASSWORD=secure_password

CACHE_DRIVER=redis
SESSION_DRIVER=cookie
QUEUE_CONNECTION=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=secure_redis_password
REDIS_PORT=6379

MAIL_DRIVER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=user@example.com
MAIL_PASSWORD=password

STRIPE_API_KEY=sk_live_...
STRIPE_SECRET_KEY=...
```

### Application Configuration

```bash
# Generate application key (if not set)
php artisan key:generate

# Optimize autoloader
composer dump-autoload --optimize

# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Publish assets
php artisan vendor:publish --all
```

---

## Nginx Configuration

```nginx
# /etc/nginx/sites-available/example.com
server {
    listen 80;
    listen [::]:80;
    server_name example.com www.example.com;

    # Redirect to HTTPS
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name example.com www.example.com;

    # SSL configuration
    ssl_certificate /path/to/certificate.crt;
    ssl_certificate_key /path/to/private.key;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;
    add_header Permissions-Policy "geolocation=(), microphone=(), camera=()" always;

    # Gzip compression
    gzip on;
    gzip_types text/plain text/css text/xml text/javascript application/json application/javascript application/xml+rss;
    gzip_min_length 1000;

    root /var/www/example.com/public;
    index index.php index.html;

    # Logging
    access_log /var/log/nginx/example.com.access.log;
    error_log /var/log/nginx/example.com.error.log;

    # Prevent access to hidden files
    location ~ /\. {
        deny all;
    }

    # Serve static assets
    location ~* ^/(?:css|js|images|fonts)/(.*)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    # PHP-FPM
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_buffer_size 128k;
        fastcgi_buffers 256 16k;
    }

    # Laravel routing
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
}
```

---

## SSL Certificate Setup

```bash
# Using Let's Encrypt and Certbot
sudo apt-get install certbot python3-certbot-nginx

sudo certbot certonly --nginx -d example.com -d www.example.com

# Renew automatically (cron job)
0 12 * * * /usr/bin/certbot renew --quiet
```

---

## Automated Deployment Script

```bash
#!/bin/bash
# deploy.sh

set -e

# Configuration
APP_PATH="/var/www/example.com"
REPO_URL="git@github.com:user/repo.git"
BRANCH="main"

echo "Starting deployment..."

# Pull latest code
cd $APP_PATH
git fetch origin
git checkout origin/$BRANCH

# Install dependencies
composer install --no-dev --optimize-autoloader

# Build assets
npm install --production
npm run build

# Run migrations
php artisan migrate --force

# Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Restart queue worker
sudo supervisorctl restart laravel-worker:*

# Restart PHP-FPM
sudo systemctl restart php8.2-fpm

# Clear old cache
php artisan cache:clear

echo "Deployment completed!"
```

Run with:
```bash
chmod +x deploy.sh
./deploy.sh
```

---

## Database Backup

```bash
#!/bin/bash
# backup.sh

BACKUP_DIR="/backups"
DB_NAME="prod_db"
DB_USER="db_user"

mkdir -p $BACKUP_DIR

# Create backup
mysqldump -u $DB_USER -p$DB_PASSWORD $DB_NAME > $BACKUP_DIR/backup_$(date +%Y%m%d_%H%M%S).sql

# Keep only last 30 days
find $BACKUP_DIR -name "backup_*.sql" -mtime +30 -delete

echo "Backup completed"
```

Cron job:
```bash
0 2 * * * /path/to/backup.sh  # 2 AM daily
```

---

## Zero-Downtime Deployment

```bash
#!/bin/bash
# zero-downtime-deploy.sh

APP_PATH="/var/www/example.com"

# Create new release directory
RELEASE_DIR="$APP_PATH/releases/$(date +%s)"
mkdir -p $RELEASE_DIR

# Clone repository
git clone -b main --single-branch $REPO_URL $RELEASE_DIR

# Install and build in new directory
cd $RELEASE_DIR
composer install --no-dev
npm install --production && npm run build

# Update symlink to point to new release
ln -nfs $RELEASE_DIR $APP_PATH/current

# Clear cache
cd $APP_PATH/current
php artisan config:cache
php artisan route:cache

# Reload PHP-FPM (graceful reload)
sudo systemctl reload php8.2-fpm

# Cleanup old releases (keep last 3)
cd $APP_PATH/releases
ls -t | tail -n +4 | xargs rm -rf

echo "Deployment complete!"
```

---

## Monitoring

### Health Check Endpoint

```php
// routes/api.php
Route::get('/health', function() {
    $checks = [
        'database' => health_check_database(),
        'cache' => health_check_cache(),
        'queue' => health_check_queue(),
    ];

    $status = collect($checks)->every(fn($check) => $check) ? 200 : 503;

    return response()->json($checks, $status);
});

function health_check_database(): bool
{
    try {
        DB::connection()->getPdo();
        return true;
    } catch (\Exception $e) {
        return false;
    }
}

function health_check_cache(): bool
{
    try {
        Cache::put('health_check', true, 60);
        return Cache::get('health_check') === true;
    } catch (\Exception $e) {
        return false;
    }
}

function health_check_queue(): bool
{
    try {
        return Queue::connection()->getConnection()->ping();
    } catch (\Exception $e) {
        return false;
    }
}
```

### Monitoring Tools

Popular options:
- New Relic
- DataDog
- Scout APM
- Papertrail (logging)
- Sentry (error tracking)

---

## Checklist

- [ ] Environment variables configured
- [ ] Database created and migrated
- [ ] SSL certificate installed
- [ ] Web server configured
- [ ] Application optimized
- [ ] Backups automated
- [ ] Deployment script created
- [ ] Health checks implemented
- [ ] Monitoring set up
- [ ] Error tracking enabled
- [ ] Logging configured
- [ ] Queue worker running
- [ ] Caching enabled
- [ ] Security headers set
- [ ] Documentation written

---

## Common Issues

| Issue | Solution |
|-------|----------|
| Permission denied | Check file permissions: `chmod -R 775 storage bootstrap/cache` |
| Database connection error | Verify DB credentials and network access |
| Assets not loading | Run `php artisan storage:link` for public storage |
| Queue jobs not running | Check supervisor status and logs |
| High memory usage | Enable opcache and view caching |
| Slow response time | Enable Redis caching, optimize queries |

---

## Bonus Challenges

1. Set up CI/CD pipeline (GitHub Actions, GitLab CI)
2. Implement automated testing before deploy
3. Set up database replication
4. Implement blue-green deployment
5. Set up CDN for static assets
6. Create monitoring dashboards
7. Implement load balancing
8. Set up automatic scaling

---

## Solution Check

Verify deployment:

```bash
# Check application
curl https://example.com

# Check health endpoint
curl https://example.com/api/health

# Check logs
tail -f /var/log/nginx/example.com.error.log
tail -f storage/logs/laravel.log

# Check database
php artisan tinker
>>> DB::connection()->getPDO()
>>> User::count()

# Check cache
php artisan tinker
>>> Cache::put('test', 'value')
>>> Cache::get('test')

# Check queue
php artisan queue:work --once
```
