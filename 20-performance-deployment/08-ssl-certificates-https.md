# SSL Certificates and HTTPS

**Duration**: 3-4 hours

---

## Introduction

HTTPS encrypts data between your users and your server, protecting passwords, personal information, and session data from attackers. Modern browsers flag HTTP sites as "Not Secure," hurting trust and SEO. In this lesson, you'll learn how to secure your Laravel application with free SSL certificates.

**What you'll learn:**
- Understanding SSL/TLS certificates
- Getting free certificates with Let's Encrypt
- Configuring nginx for HTTPS
- Forcing HTTPS in Laravel
- Certificate renewal
- Troubleshooting SSL issues

---

## Understanding SSL/TLS

### What Is SSL/TLS?

**SSL (Secure Sockets Layer)** and **TLS (Transport Layer Security)** encrypt data in transit.

**Without HTTPS (HTTP):**
```
User → [username: alice] → Server
       [password: secret123]
       ↑ Anyone can read this!
```

**With HTTPS:**
```
User → [encrypted gibberish] → Server
       ↑ Only server can decrypt
```

### Why HTTPS Matters

**1. Security:**
- Protects passwords and personal data
- Prevents man-in-the-middle attacks
- Secures session cookies

**2. Trust:**
- Browsers show lock icon
- "Not Secure" warning scares users away
- Required for PWAs and modern web features

**3. SEO:**
- Google ranks HTTPS sites higher
- Required for some features (geolocation, camera, etc.)

**4. Compliance:**
- PCI DSS requires HTTPS for payment processing
- GDPR requires protecting personal data

**5. Performance:**
- HTTP/2 requires HTTPS (faster than HTTP/1.1)

### How Certificates Work

**1. Certificate Authority (CA) verifies domain ownership**
```
You: "I own example.com"
CA: "Prove it - add this DNS record"
You: *adds record*
CA: "Verified! Here's your certificate"
```

**2. Browser trusts certificate**
```
Browser: "Is this certificate valid?"
CA: "Yes, I signed it"
Browser: "OK, I'll trust this connection"
```

**3. Encrypted connection established**
```
Browser ←→ Server
(encrypted traffic)
```

---

## Let's Encrypt: Free SSL Certificates

### What Is Let's Encrypt?

- Free, automated, open Certificate Authority
- Trusted by all major browsers
- 90-day certificates (auto-renewable)
- Powers 300+ million websites

### How It Works

**1. Install Certbot (client software)**
**2. Certbot proves you own domain**
**3. Let's Encrypt issues certificate**
**4. Certbot configures web server**
**5. Auto-renewal every 60 days**

---

## Installing SSL with Certbot

### Prerequisites

Before starting:
- Domain name pointing to your server (A record)
- nginx installed and running
- Port 80 open (for verification)
- Port 443 open (for HTTPS)

**Verify DNS:**
```bash
# Check if domain points to your server
dig +short yourdomain.com
# Should return your server IP
```

### Step 1: Install Certbot

**On Ubuntu 22.04:**
```bash
# Install Certbot and nginx plugin
sudo apt install -y certbot python3-certbot-nginx
```

### Step 2: Configure nginx (HTTP Only)

First, set up basic HTTP configuration:

**/etc/nginx/sites-available/yourdomain.com:**
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

**Enable and test:**
```bash
# Enable site
sudo ln -s /etc/nginx/sites-available/yourdomain.com /etc/nginx/sites-enabled/

# Test configuration
sudo nginx -t

# Reload nginx
sudo systemctl reload nginx
```

### Step 3: Obtain Certificate

**Run Certbot:**
```bash
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com
```

**Interactive prompts:**
```
Email address: your-email@example.com
Terms of Service: A (Agree)
Share email: N (No)
```

**Certbot will:**
1. Verify you own the domain
2. Obtain certificate
3. Automatically modify nginx config
4. Set up auto-renewal

**Success message:**
```
Successfully received certificate.
Certificate is saved at: /etc/letsencrypt/live/yourdomain.com/fullchain.pem
Key is saved at:         /etc/letsencrypt/live/yourdomain.com/privkey.pem
This certificate expires on 2025-05-15.
```

### Step 4: Verify HTTPS Works

**Visit your site:**
```
https://yourdomain.com
```

Should see:
- Lock icon in browser
- "Connection is secure"
- Your site loads properly

### Step 5: Check Auto-Renewal

Certbot automatically sets up renewal:

```bash
# Test renewal process (dry run)
sudo certbot renew --dry-run

# If successful:
Congratulations, all simulated renewals succeeded!
```

---

## nginx HTTPS Configuration

### After Certbot Configuration

Certbot modifies your nginx config to look like this:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name yourdomain.com www.yourdomain.com;

    # Redirect HTTP to HTTPS
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name yourdomain.com www.yourdomain.com;
    root /home/deployer/laravel-app/public;

    # SSL Configuration
    ssl_certificate /etc/letsencrypt/live/yourdomain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/yourdomain.com/privkey.pem;
    include /etc/letsencrypt/options-ssl-nginx.conf;
    ssl_dhparam /etc/letsencrypt/ssl-dhparams.pem;

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

    # Browser caching for assets
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|woff|woff2|ttf|svg)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types text/plain text/css text/xml text/javascript
               application/x-javascript application/xml+rss
               application/javascript application/json;
}
```

### Enhanced SSL Configuration

For better security, enhance the SSL settings:

```nginx
server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name yourdomain.com www.yourdomain.com;
    root /home/deployer/laravel-app/public;

    # SSL Configuration
    ssl_certificate /etc/letsencrypt/live/yourdomain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/yourdomain.com/privkey.pem;

    # SSL protocols and ciphers
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers 'ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256:ECDHE-ECDSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-GCM-SHA384';
    ssl_prefer_server_ciphers on;

    # SSL session caching
    ssl_session_cache shared:SSL:10m;
    ssl_session_timeout 10m;

    # OCSP Stapling
    ssl_stapling on;
    ssl_stapling_verify on;
    ssl_trusted_certificate /etc/letsencrypt/live/yourdomain.com/chain.pem;
    resolver 8.8.8.8 8.8.4.4 valid=300s;

    # Security headers
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains; preload" always;
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;

    # ... rest of Laravel configuration ...
}
```

**Reload nginx:**
```bash
sudo nginx -t
sudo systemctl reload nginx
```

---

## Forcing HTTPS in Laravel

### Update .env

```
APP_URL=https://yourdomain.com
```

### Force HTTPS Globally

**app/Providers/AppServiceProvider.php:**
```php
<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Force HTTPS in production
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
```

This ensures all generated URLs use HTTPS.

### Force HTTPS Middleware

For specific routes:

**app/Http/Middleware/ForceHttps.php:**
```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ForceHttps
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->secure() && app()->environment('production')) {
            return redirect()->secure($request->getRequestUri());
        }

        return $next($request);
    }
}
```

**Register in app/Http/Kernel.php:**
```php
protected $middlewareGroups = [
    'web' => [
        // ... other middleware
        \App\Http\Middleware\ForceHttps::class,
    ],
];
```

### Update Asset URLs

**config/app.php:**
```php
'asset_url' => env('ASSET_URL', 'https://yourdomain.com'),
```

Now `asset()` helper always generates HTTPS URLs:
```blade
<img src="{{ asset('images/logo.png') }}">
{{-- Always: https://yourdomain.com/images/logo.png --}}
```

---

## Mixed Content Issues

### What Is Mixed Content?

HTTPS page loading HTTP resources:

```html
<!-- HTTPS page -->
https://yourdomain.com/dashboard

<!-- But image loaded over HTTP -->
<img src="http://yourdomain.com/logo.png">
                ↑ Mixed content warning!
```

**Result:**
- Browser blocks HTTP content (or shows warning)
- Broken images, styles, or scripts
- "Not Secure" warning returns

### Finding Mixed Content

**Browser DevTools:**
1. Open DevTools (F12)
2. Check Console
3. Look for warnings:
```
Mixed Content: The page at 'https://...' was loaded over HTTPS,
but requested an insecure resource 'http://...'.
```

### Fixing Mixed Content

**1. Use relative URLs:**
```blade
{{-- ❌ Bad: Hardcoded protocol --}}
<img src="http://yourdomain.com/logo.png">

{{-- ✅ Good: Relative URL --}}
<img src="/logo.png">

{{-- ✅ Good: asset() helper --}}
<img src="{{ asset('logo.png') }}">
```

**2. Use protocol-relative URLs:**
```blade
{{-- Works for both HTTP and HTTPS --}}
<script src="//cdn.example.com/library.js"></script>
```

**3. Update external resources:**
```blade
{{-- ❌ Bad --}}
<script src="http://code.jquery.com/jquery-3.6.0.min.js"></script>

{{-- ✅ Good --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
```

**4. Fix CDN URLs:**
```php
// config/filesystems.php
'cdn' => [
    'url' => env('CDN_URL', 'https://cdn.yourdomain.com'), // HTTPS!
],
```

---

## Certificate Renewal

### Automatic Renewal

Certbot automatically creates a systemd timer:

**Check renewal timer:**
```bash
sudo systemctl list-timers | grep certbot

# Output:
# Mon 2025-05-01 12:00:00 UTC  certbot.timer
```

**Manually trigger renewal:**
```bash
sudo certbot renew
```

**Test renewal (without actually renewing):**
```bash
sudo certbot renew --dry-run
```

### Renewal Process

Certificates expire after 90 days. Certbot renews them automatically:

**1. Certbot checks certificates daily**
**2. If expiring in < 30 days, renew**
**3. Reload nginx automatically**

### Monitoring Renewal

**Check certificate expiration:**
```bash
sudo certbot certificates

# Output:
Certificate Name: yourdomain.com
  Domains: yourdomain.com www.yourdomain.com
  Expiry Date: 2025-05-15 10:00:00+00:00 (VALID: 89 days)
```

**Set up expiration alerts:**
```bash
# Certbot emails you before expiration
# Use the email you provided during setup
```

### Manual Renewal

If automatic renewal fails:

```bash
# Renew all certificates
sudo certbot renew

# Renew specific certificate
sudo certbot renew --cert-name yourdomain.com

# Force renewal (even if not expiring)
sudo certbot renew --force-renewal

# Reload nginx after renewal
sudo systemctl reload nginx
```

---

## Troubleshooting SSL

### Common Issues

**1. Port 80 or 443 blocked:**
```bash
# Check if ports are open
sudo ufw status

# Open ports if needed
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
```

**2. DNS not pointing to server:**
```bash
# Check DNS
dig +short yourdomain.com

# Should return your server IP
# If not, update DNS A record
```

**3. nginx not running:**
```bash
# Check nginx status
sudo systemctl status nginx

# Start if not running
sudo systemctl start nginx
```

**4. Wrong domain in certificate:**
```bash
# Check certificate domains
sudo certbot certificates

# Add domain to certificate
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com -d subdomain.yourdomain.com
```

**5. Certificate expired:**
```bash
# Check expiration
sudo certbot certificates

# Renew
sudo certbot renew --force-renewal
```

### Testing SSL Configuration

**Online tools:**

**1. SSL Labs:**
- Visit: https://www.ssllabs.com/ssltest/
- Enter your domain
- Wait for scan
- Goal: A+ rating

**2. Why No Padlock:**
- Visit: https://www.whynopadlock.com/
- Enter your domain
- Shows mixed content issues

**Command line:**
```bash
# Test SSL connection
openssl s_client -connect yourdomain.com:443

# Check certificate expiration
echo | openssl s_client -servername yourdomain.com -connect yourdomain.com:443 2>/dev/null | openssl x509 -noout -dates
```

---

## Wildcard Certificates

### For Multiple Subdomains

**Wildcard certificate covers:**
- yourdomain.com
- *.yourdomain.com (any subdomain)

**Obtain wildcard certificate:**
```bash
sudo certbot certonly --manual --preferred-challenges=dns -d yourdomain.com -d *.yourdomain.com
```

**Process:**
1. Certbot asks you to add TXT DNS record
2. Add record to your DNS provider
3. Verify record: `dig TXT _acme-challenge.yourdomain.com`
4. Press Enter in Certbot
5. Certificate issued

**nginx configuration:**
```nginx
# Main domain
server {
    listen 443 ssl http2;
    server_name yourdomain.com;
    ssl_certificate /etc/letsencrypt/live/yourdomain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/yourdomain.com/privkey.pem;
    # ...
}

# Subdomain
server {
    listen 443 ssl http2;
    server_name api.yourdomain.com;
    ssl_certificate /etc/letsencrypt/live/yourdomain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/yourdomain.com/privkey.pem;
    # ...
}
```

---

## Security Best Practices

### 1. Use Strong TLS Protocols

```nginx
# Only modern protocols
ssl_protocols TLSv1.2 TLSv1.3;

# No SSLv3, TLSv1, TLSv1.1 (vulnerable)
```

### 2. HSTS (HTTP Strict Transport Security)

Tells browsers to always use HTTPS:

```nginx
add_header Strict-Transport-Security "max-age=31536000; includeSubDomains; preload" always;
```

**What it does:**
- `max-age=31536000`: Remember for 1 year
- `includeSubDomains`: Apply to all subdomains
- `preload`: Include in browser preload list

### 3. Redirect www to non-www (or vice versa)

**Redirect www to non-www:**
```nginx
server {
    listen 443 ssl http2;
    server_name www.yourdomain.com;

    ssl_certificate /etc/letsencrypt/live/yourdomain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/yourdomain.com/privkey.pem;

    return 301 https://yourdomain.com$request_uri;
}

server {
    listen 443 ssl http2;
    server_name yourdomain.com;
    # ... main configuration
}
```

### 4. Security Headers

```nginx
# Prevent clickjacking
add_header X-Frame-Options "SAMEORIGIN" always;

# Prevent MIME sniffing
add_header X-Content-Type-Options "nosniff" always;

# XSS protection
add_header X-XSS-Protection "1; mode=block" always;

# Referrer policy
add_header Referrer-Policy "strict-origin-when-cross-origin" always;

# Content Security Policy (adjust as needed)
add_header Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline';" always;
```

---

## Quick Reference

```bash
# Install Certbot
sudo apt install -y certbot python3-certbot-nginx

# Obtain certificate
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com

# Test renewal
sudo certbot renew --dry-run

# Renew certificates
sudo certbot renew

# Check certificates
sudo certbot certificates

# Revoke certificate
sudo certbot revoke --cert-path /etc/letsencrypt/live/yourdomain.com/cert.pem

# Delete certificate
sudo certbot delete --cert-name yourdomain.com
```

```php
// Force HTTPS in Laravel
// AppServiceProvider.php
if ($this->app->environment('production')) {
    URL::forceScheme('https');
}
```

---

## Checklist

```
✓ Domain points to server (DNS A record)
✓ Port 80 and 443 open in firewall
✓ nginx configured and running
✓ Certbot installed
✓ SSL certificate obtained
✓ HTTPS works in browser
✓ HTTP redirects to HTTPS
✓ Auto-renewal enabled
✓ APP_URL set to https://
✓ URL::forceScheme('https') in production
✓ No mixed content warnings
✓ Security headers configured
✓ SSL test passes (SSL Labs)
```

---

## Practice Questions

1. **What does SSL/TLS do?** Why is it important?

2. **What is Let's Encrypt?** How often do certificates need renewal?

3. **What is mixed content?** How do you fix it?

4. **What is HSTS?** What does it protect against?

5. **How do you check if your certificate is expiring?**

6. **What happens if you don't renew your certificate?**

---

## Next Steps

Now your site is secured with HTTPS! Next, you'll learn about deployment strategies for safely releasing changes to production.

**Coming up in Lesson 09:**
- Zero-downtime deployments
- Deployment strategies (Blue-Green, Rolling)
- Git-based deployment
- Rollback procedures
- Deployment automation
