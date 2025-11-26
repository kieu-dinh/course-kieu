# Server Setup and Configuration

**Duration**: 4-5 hours

---

## Introduction

Your Laravel application needs a properly configured server to run in production. In this lesson, you'll learn how to set up and configure a production server, including web server, PHP, database, and security measures.

**What you'll learn:**
- Server requirements for Laravel
- Choosing a hosting provider
- Web server configuration (nginx)
- PHP-FPM setup and optimization
- Security hardening
- Performance tuning

---

## Server Requirements

### Minimum Requirements

Laravel 11 requires:
- PHP >= 8.2
- Composer
- MySQL 5.7+ or PostgreSQL 9.6+ or SQLite 3.8.8+
- Redis (optional, recommended)
- Node.js and NPM (for asset building)

### PHP Extensions

Required:
- Ctype
- cURL
- DOM
- Fileinfo
- Filter
- Hash
- Mbstring
- OpenSSL
- PCRE
- PDO
- Session
- Tokenizer
- XML

Recommended:
- Redis (for caching and queues)
- Imagick or GD (for image processing)
- Opcache (for performance)

### Server Resources

**Small application (< 1000 daily users):**
- 1 CPU core
- 1 GB RAM
- 25 GB storage
- Example: DigitalOcean $6/month droplet

**Medium application (1000-10000 daily users):**
- 2 CPU cores
- 2-4 GB RAM
- 50 GB storage
- Example: DigitalOcean $12-24/month droplet

**Large application (10000+ daily users):**
- 4+ CPU cores
- 8+ GB RAM
- 100+ GB storage
- Load balancer, multiple app servers
- Example: DigitalOcean $48+/month

---

## Choosing a Hosting Provider

### Options for Laravel

**1. Shared Hosting (Not Recommended)**
- Examples: Bluehost, HostGator, GoDaddy
- Pros: Cheap ($5-10/month)
- Cons: Limited control, poor performance, no SSH access usually
- Use for: Static sites only

**2. VPS (Virtual Private Server) - Recommended**
- Examples: DigitalOcean, Linode, Vultr
- Pros: Full control, good performance, affordable
- Cons: Requires server management knowledge
- Use for: Most Laravel applications

**3. Platform as a Service (PaaS)**
- Examples: Laravel Forge + DigitalOcean, Ploi, Laravel Vapor
- Pros: Easy setup, automatic deployments, managed
- Cons: More expensive
- Use for: When you want to focus on code, not servers

**4. Managed Laravel Hosting**
- Examples: Laravel Cloud (Vapor), Cloudways
- Pros: Zero server management, auto-scaling
- Cons: Most expensive
- Use for: High-traffic apps or when you want zero DevOps

### Recommended: DigitalOcean + Laravel Forge

**DigitalOcean:**
- Provides VPS servers ("droplets")
- $6/month for basic server
- Reliable, fast, good documentation

**Laravel Forge:**
- Manages your DigitalOcean server
- One-click Laravel setup
- Easy deployments
- $12/month (manages unlimited servers)

**Total cost: $18/month for fully managed Laravel hosting**

---

## Manual Server Setup (Ubuntu 22.04)

If not using Forge, here's how to set up manually.

### Step 1: Create Server

**On DigitalOcean:**
1. Create account
2. Create new Droplet
3. Choose Ubuntu 22.04 LTS
4. Choose size (start with $6/month)
5. Add SSH key
6. Create droplet

**Connect via SSH:**
```bash
ssh root@your-server-ip
```

### Step 2: Update System

```bash
# Update package list
apt update

# Upgrade installed packages
apt upgrade -y

# Install basic tools
apt install -y curl git unzip software-properties-common
```

### Step 3: Install PHP 8.2

```bash
# Add PHP repository
add-apt-repository ppa:ondrej/php -y
apt update

# Install PHP and extensions
apt install -y php8.2-fpm php8.2-cli php8.2-common \
    php8.2-mysql php8.2-mbstring php8.2-xml php8.2-curl \
    php8.2-zip php8.2-gd php8.2-redis php8.2-bcmath \
    php8.2-intl php8.2-readline php8.2-opcache

# Verify installation
php -v
```

### Step 4: Install Composer

```bash
# Download Composer installer
curl -sS https://getcomposer.org/installer -o composer-setup.php

# Install Composer globally
php composer-setup.php --install-dir=/usr/local/bin --filename=composer

# Verify
composer --version

# Remove installer
rm composer-setup.php
```

### Step 5: Install MySQL

```bash
# Install MySQL
apt install -y mysql-server

# Secure MySQL installation
mysql_secure_installation
# Set root password, remove test database, etc.

# Create database and user for Laravel
mysql -u root -p
```

```sql
CREATE DATABASE laravel_app;
CREATE USER 'laravel_user'@'localhost' IDENTIFIED BY 'secure_password';
GRANT ALL PRIVILEGES ON laravel_app.* TO 'laravel_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### Step 6: Install Redis

```bash
# Install Redis
apt install -y redis-server

# Start Redis
systemctl start redis-server
systemctl enable redis-server

# Verify
redis-cli ping
# Should return: PONG
```

### Step 7: Install nginx

```bash
# Install nginx
apt install -y nginx

# Start nginx
systemctl start nginx
systemctl enable nginx

# Verify (should see nginx welcome page)
curl http://your-server-ip
```

### Step 8: Install Node.js and NPM

```bash
# Install Node.js 20.x
curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
apt install -y nodejs

# Verify
node -v
npm -v
```

### Step 9: Create Deploy User

Don't deploy as root!

```bash
# Create user
adduser deployer

# Add to sudo group
usermod -aG sudo deployer

# Switch to deploy user
su - deployer

# Generate SSH key (for GitHub)
ssh-keygen -t ed25519 -C "your-email@example.com"

# Display public key (add to GitHub deploy keys)
cat ~/.ssh/id_ed25519.pub
```

---

## nginx Configuration

### Basic Laravel Configuration

**Create nginx config:** `/etc/nginx/sites-available/laravel`

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name yourdomain.com www.yourdomain.com;
    root /home/deployer/laravel-app/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

**Enable site:**
```bash
# Link to sites-enabled
sudo ln -s /etc/nginx/sites-available/laravel /etc/nginx/sites-enabled/

# Remove default site
sudo rm /etc/nginx/sites-enabled/default

# Test configuration
sudo nginx -t

# Reload nginx
sudo systemctl reload nginx
```

### Performance Optimizations

**Add to nginx config:**
```nginx
server {
    # ... previous config ...

    # Enable gzip compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types text/plain text/css text/xml text/javascript
               application/x-javascript application/xml+rss
               application/javascript application/json;

    # Browser caching for static assets
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|woff|woff2|ttf|svg)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    # Deny access to sensitive files
    location ~ /\. {
        deny all;
    }

    location ~ /\.env {
        deny all;
    }

    # Client body size (for uploads)
    client_max_body_size 20M;
}
```

### SSL Configuration (will cover in Lesson 08)

We'll add HTTPS in the SSL certificates lesson.

---

## PHP-FPM Configuration

### Understanding PHP-FPM

PHP-FPM (FastCGI Process Manager) manages PHP worker processes.

**Key concepts:**
- **Pool**: Group of worker processes
- **Process Manager**: Controls how workers are created
- **Workers**: PHP processes that handle requests

### Configuration File

**Main config:** `/etc/php/8.2/fpm/php-fpm.conf`
**Pool config:** `/etc/php/8.2/fpm/pool.d/www.conf`

### Optimize PHP-FPM Pool

**Edit:** `/etc/php/8.2/fpm/pool.d/www.conf`

```ini
[www]
user = www-data
group = www-data
listen = /var/run/php/php8.2-fpm.sock
listen.owner = www-data
listen.group = www-data
listen.mode = 0660

; Process manager
pm = dynamic

; Max children (total PHP processes)
; Formula: (RAM for PHP-FPM / Average memory per process)
; Example: (1 GB / 50 MB) = 20 processes
pm.max_children = 20

; Start servers on boot
pm.start_servers = 4

; Minimum idle servers
pm.min_spare_servers = 2

; Maximum idle servers
pm.max_spare_servers = 6

; Max requests per child (prevents memory leaks)
pm.max_requests = 1000

; Process idle timeout
pm.process_idle_timeout = 10s

; Status page (for monitoring)
pm.status_path = /fpm-status

; Slow log (logs slow requests)
slowlog = /var/log/php8.2-fpm-slow.log
request_slowlog_timeout = 5s
```

**Process Manager modes:**

1. **static**: Fixed number of workers (predictable memory, less dynamic)
2. **dynamic**: Workers scale up/down based on demand (recommended)
3. **ondemand**: Workers created on demand, killed when idle (minimal memory)

### Optimize php.ini

**Edit:** `/etc/php/8.2/fpm/php.ini`

```ini
; Memory per request (adjust based on your app)
memory_limit = 256M

; Maximum execution time
max_execution_time = 30

; Upload limits
upload_max_filesize = 20M
post_max_size = 20M

; Opcache (huge performance boost!)
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=20000
opcache.validate_timestamps=0  ; Disable in production!
opcache.revalidate_freq=0
opcache.save_comments=1
opcache.fast_shutdown=1

; Realpath cache (speeds up file path resolution)
realpath_cache_size=4096K
realpath_cache_ttl=600

; Session configuration
session.save_handler = redis
session.save_path = "tcp://127.0.0.1:6379"

; Error handling (production)
display_errors = Off
display_startup_errors = Off
log_errors = On
error_log = /var/log/php8.2-fpm-errors.log
error_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT

; Security
expose_php = Off
allow_url_fopen = On
allow_url_include = Off
```

**Restart PHP-FPM:**
```bash
sudo systemctl restart php8.2-fpm
```

### Monitoring PHP-FPM

**Enable status page in nginx:**
```nginx
location ~ ^/(fpm-status|fpm-ping)$ {
    access_log off;
    allow 127.0.0.1;
    deny all;
    include fastcgi_params;
    fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
}
```

**Check status:**
```bash
curl http://localhost/fpm-status

# Output:
pool:                 www
process manager:      dynamic
start time:           10/May/2024:10:00:00 +0000
accepted conn:        1234
listen queue:         0
max listen queue:     1
idle processes:       3
active processes:     1
total processes:      4
```

---

## Security Hardening

### 1. Firewall (UFW)

```bash
# Install UFW
apt install -y ufw

# Default policies
ufw default deny incoming
ufw default allow outgoing

# Allow SSH (important! Do this first)
ufw allow 22/tcp

# Allow HTTP and HTTPS
ufw allow 80/tcp
ufw allow 443/tcp

# Enable firewall
ufw enable

# Check status
ufw status
```

### 2. Fail2Ban (Prevent Brute Force)

```bash
# Install Fail2Ban
apt install -y fail2ban

# Create local config
cp /etc/fail2ban/jail.conf /etc/fail2ban/jail.local

# Edit config
nano /etc/fail2ban/jail.local
```

```ini
[DEFAULT]
bantime = 3600      ; Ban for 1 hour
findtime = 600      ; 10 minute window
maxretry = 5        ; 5 failures before ban

[sshd]
enabled = true
port = 22
logpath = /var/log/auth.log

[nginx-http-auth]
enabled = true
port = http,https
logpath = /var/log/nginx/error.log
```

**Start Fail2Ban:**
```bash
systemctl start fail2ban
systemctl enable fail2ban

# Check banned IPs
fail2ban-client status sshd
```

### 3. Disable Root Login

**Edit:** `/etc/ssh/sshd_config`

```bash
PermitRootLogin no
PasswordAuthentication no  # Use SSH keys only
```

**Restart SSH:**
```bash
systemctl restart sshd
```

### 4. Automatic Security Updates

```bash
# Install unattended-upgrades
apt install -y unattended-upgrades

# Enable
dpkg-reconfigure -plow unattended-upgrades
```

### 5. File Permissions

```bash
# Laravel application directory
cd /home/deployer/laravel-app

# Set ownership
sudo chown -R deployer:www-data .

# Set directory permissions
find . -type d -exec chmod 755 {} \;

# Set file permissions
find . -type f -exec chmod 644 {} \;

# Storage and cache need write access
chmod -R 775 storage bootstrap/cache
```

### 6. Environment Variables

Never commit `.env` to Git!

```bash
# Secure .env file
chmod 600 .env
chown deployer:deployer .env

# Generate secure app key
php artisan key:generate
```

---

## Performance Tuning

### 1. Opcache

Already configured in php.ini above. Verify it's working:

```bash
php -i | grep opcache
```

**Important:** Set `opcache.validate_timestamps=0` in production (then restart PHP-FPM after each deployment).

### 2. MySQL Optimization

**Edit:** `/etc/mysql/mysql.conf.d/mysqld.cnf`

```ini
[mysqld]
# Buffer pool size (50-70% of available RAM)
innodb_buffer_pool_size = 512M

# Log file size
innodb_log_file_size = 128M

# Query cache (for read-heavy apps)
query_cache_type = 1
query_cache_size = 64M

# Connection limits
max_connections = 150

# Slow query log
slow_query_log = 1
slow_query_log_file = /var/log/mysql/slow-query.log
long_query_time = 2
```

**Restart MySQL:**
```bash
systemctl restart mysql
```

### 3. Redis Optimization

**Edit:** `/etc/redis/redis.conf`

```ini
# Maximum memory
maxmemory 256mb

# Eviction policy (remove least recently used when memory full)
maxmemory-policy allkeys-lru

# Persistence (RDB snapshots)
save 900 1      # Save after 900 seconds if 1 key changed
save 300 10     # Save after 300 seconds if 10 keys changed
save 60 10000   # Save after 60 seconds if 10000 keys changed

# AOF (Append Only File) - more durable but slower
appendonly no  # Use 'yes' for critical data
```

**Restart Redis:**
```bash
systemctl restart redis-server
```

### 4. System Limits

**Edit:** `/etc/security/limits.conf`

```
*  soft  nofile  65535
*  hard  nofile  65535
```

**Edit:** `/etc/sysctl.conf`

```ini
# Network tuning
net.core.somaxconn = 4096
net.ipv4.tcp_max_syn_backlog = 4096

# File handles
fs.file-max = 65535
```

**Apply:**
```bash
sysctl -p
```

---

## Monitoring

### Server Monitoring

**Install htop (better top):**
```bash
apt install -y htop
htop
```

**Check disk usage:**
```bash
df -h
```

**Check memory:**
```bash
free -h
```

**Check processes:**
```bash
ps aux | grep php-fpm
ps aux | grep nginx
```

### Log Monitoring

**nginx logs:**
```bash
tail -f /var/log/nginx/access.log
tail -f /var/log/nginx/error.log
```

**PHP-FPM logs:**
```bash
tail -f /var/log/php8.2-fpm.log
tail -f /var/log/php8.2-fpm-slow.log
```

**Laravel logs:**
```bash
tail -f /home/deployer/laravel-app/storage/logs/laravel.log
```

**MySQL slow queries:**
```bash
tail -f /var/log/mysql/slow-query.log
```

---

## Deployment User Setup

### Create Deployment Structure

```bash
# As deployer user
cd /home/deployer

# Create releases directory
mkdir -p laravel-app/releases
mkdir -p laravel-app/shared

# Shared files (persist across deployments)
mkdir -p laravel-app/shared/storage
mkdir -p laravel-app/shared/storage/app
mkdir -p laravel-app/shared/storage/framework
mkdir -p laravel-app/shared/storage/logs

# Set permissions
chmod -R 775 laravel-app/shared/storage
```

### Deployment Script

We'll create full deployment scripts in Lesson 10.

---

## Quick Server Setup Checklist

```bash
# 1. Update system
apt update && apt upgrade -y

# 2. Install PHP 8.2
add-apt-repository ppa:ondrej/php -y
apt install -y php8.2-fpm php8.2-cli php8.2-mysql php8.2-mbstring php8.2-xml php8.2-curl php8.2-zip php8.2-redis

# 3. Install Composer
curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# 4. Install MySQL
apt install -y mysql-server

# 5. Install Redis
apt install -y redis-server

# 6. Install nginx
apt install -y nginx

# 7. Install Node.js
curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
apt install -y nodejs

# 8. Create deploy user
adduser deployer
usermod -aG sudo deployer

# 9. Configure firewall
apt install -y ufw
ufw allow 22
ufw allow 80
ufw allow 443
ufw enable

# 10. Configure nginx (see examples above)

# 11. Configure PHP-FPM (see examples above)

# 12. Secure server (see security section above)
```

---

## Quick Reference

```bash
# Service management
systemctl start nginx
systemctl stop nginx
systemctl restart nginx
systemctl reload nginx
systemctl status nginx

# Same for PHP-FPM, MySQL, Redis:
systemctl restart php8.2-fpm
systemctl restart mysql
systemctl restart redis-server

# View logs
tail -f /var/log/nginx/error.log
tail -f /var/log/php8.2-fpm.log

# Test nginx config
nginx -t

# Check PHP-FPM status
curl http://localhost/fpm-status

# Check open files
lsof | grep php-fpm

# Check connections
netstat -tulpn | grep :80
```

---

## Practice Questions

1. **What are the minimum requirements for running Laravel 11?**

2. **What's the difference between VPS and managed hosting?** When would you choose each?

3. **What is PHP-FPM?** What does it do?

4. **What is Opcache?** Why is it important?

5. **Why should you never run your application as root?**

6. **What ports need to be open in the firewall?**

---

## Next Steps

Now you have a configured server! Next, you'll learn about environment configuration and managing different environments (development, staging, production).

**Coming up in Lesson 07:**
- Environment configuration
- Managing secrets
- Multi-environment setup
- Configuration caching
- Environment-specific settings
