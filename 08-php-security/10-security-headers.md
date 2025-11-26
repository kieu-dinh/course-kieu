# Lesson 10 - Security Headers: Additional Defense Layers

**Duration**: 45-60 minutes

---

## Introduction: Defense in Depth

Security headers are HTTP response headers that tell browsers how to behave when handling your content. They provide additional layers of protection against:
- Clickjacking
- MIME type attacks
- XSS attacks
- Man-in-the-middle attacks
- Information leakage

**The best part**: They're easy to implement and provide significant protection!

---

## Essential Security Headers

### 1. Content-Security-Policy (CSP)

**Purpose**: Prevent XSS attacks by controlling what resources can be loaded and executed.

**How it works**: Tells the browser what sources of content are allowed.

```php
<?php
// Basic CSP - Only allow content from same origin
header("Content-Security-Policy: default-src 'self'");
```

**More comprehensive**:
```php
<?php
header("Content-Security-Policy: " .
    "default-src 'self'; " .                    // Default: same origin only
    "script-src 'self' https://cdn.example.com; " . // Scripts from self + trusted CDN
    "style-src 'self' 'unsafe-inline'; " .      // Styles from self + inline styles
    "img-src 'self' https: data:; " .           // Images from self + https sites + data URIs
    "font-src 'self'; " .                       // Fonts from self only
    "connect-src 'self'; " .                    // AJAX/WebSocket to self only
    "frame-ancestors 'none'; " .                // Prevent embedding in frames
    "base-uri 'self'; " .                       // Restrict <base> tag
    "form-action 'self'; " .                    // Forms can only submit to self
    "upgrade-insecure-requests"                 // Upgrade HTTP to HTTPS
);
```

#### CSP Directives Explained

```php
// default-src: Fallback for other directives
"default-src 'self'"

// script-src: Where JavaScript can load from
"script-src 'self' https://trusted-cdn.com"

// style-src: Where CSS can load from
"style-src 'self' 'unsafe-inline'"

// img-src: Where images can load from
"img-src 'self' https: data:"

// font-src: Where fonts can load from
"font-src 'self' https://fonts.gstatic.com"

// connect-src: Where AJAX/fetch/WebSocket can connect
"connect-src 'self' https://api.example.com"

// media-src: Where audio/video can load from
"media-src 'self' https://videos.example.com"

// object-src: Flash, Java, etc. (usually 'none')
"object-src 'none'"

// frame-src: Where iframes can load from
"frame-src 'self' https://www.youtube.com"

// frame-ancestors: Who can embed your page in iframe
"frame-ancestors 'none'"  // No one can frame you
"frame-ancestors 'self'"  // Only your site can frame you

// base-uri: Restrict <base> tag
"base-uri 'self'"

// form-action: Where forms can submit to
"form-action 'self'"

// upgrade-insecure-requests: Upgrade HTTP to HTTPS
"upgrade-insecure-requests"
```

#### CSP with Nonce (Recommended for Inline Scripts)

```php
<?php
// Generate unique nonce
$nonce = base64_encode(random_bytes(16));

// Set CSP with nonce
header("Content-Security-Policy: " .
    "default-src 'self'; " .
    "script-src 'self' 'nonce-$nonce'; " .
    "style-src 'self' 'unsafe-inline'"
);
?>

<!DOCTYPE html>
<html>
<head>
    <!-- This script has nonce - allowed -->
    <script nonce="<?= $nonce ?>">
        console.log('This executes!');
    </script>

    <!-- This script has NO nonce - blocked -->
    <script>
        console.log('This is blocked!');
    </script>

    <!-- External scripts are allowed -->
    <script src="/js/app.js"></script>
</head>
</html>
```

#### Testing CSP (Report-Only Mode)

Don't break your site - test first:

```php
<?php
// Report violations but don't block
header("Content-Security-Policy-Report-Only: " .
    "default-src 'self'; " .
    "report-uri /csp-report"
);
```

Create report endpoint:
```php
<?php
// csp-report.php
$json = file_get_contents('php://input');
$report = json_decode($json, true);

// Log violation
error_log('CSP Violation: ' . json_encode($report));

// Store in database for analysis
$stmt = $pdo->prepare("INSERT INTO csp_violations (report, created_at) VALUES (?, NOW())");
$stmt->execute([json_encode($report)]);
```

---

### 2. X-Frame-Options

**Purpose**: Prevent clickjacking attacks.

**What is clickjacking?**
```html
<!-- Attacker's page -->
<iframe src="https://bank.com/transfer" style="opacity:0"></iframe>
<button style="position:absolute; top:100px; left:100px;">
    Click to Win $1000!
</button>

<!-- When user clicks "win", they actually click the hidden iframe -->
```

**Protection**:
```php
<?php
// Don't allow any site to frame your page
header("X-Frame-Options: DENY");

// Or: Only allow your own site to frame
header("X-Frame-Options: SAMEORIGIN");

// Or: Allow specific site
header("X-Frame-Options: ALLOW-FROM https://trusted-site.com");
```

**Note**: `ALLOW-FROM` is deprecated. Use CSP `frame-ancestors` instead:
```php
header("Content-Security-Policy: frame-ancestors 'self' https://trusted-site.com");
```

---

### 3. X-Content-Type-Options

**Purpose**: Prevent MIME type sniffing attacks.

**The problem**:
```php
// Server sends: Content-Type: text/plain
// But file contains: <script>alert('XSS')</script>
// Browser "helpfully" detects it as HTML and executes it!
```

**Protection**:
```php
<?php
header("X-Content-Type-Options: nosniff");
// Browser will NOT guess content type - uses what server declares
```

This is especially important for user uploads:
```php
<?php
// When serving user-uploaded files
header("Content-Type: image/jpeg");
header("X-Content-Type-Options: nosniff");
// Browser won't try to execute as HTML even if it detects script tags
```

---

### 4. Strict-Transport-Security (HSTS)

**Purpose**: Force HTTPS and prevent SSL stripping attacks.

**The problem**:
```
1. User types: bank.com (no https://)
2. Browser tries: http://bank.com
3. Attacker intercepts: Serves fake HTTP version
4. User enters password on HTTP: Stolen!
```

**Protection**:
```php
<?php
// Only set on HTTPS connections!
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    header("Strict-Transport-Security: max-age=31536000; includeSubDomains; preload");
}
```

**Parameters**:
- `max-age=31536000`: Enforce HTTPS for 1 year
- `includeSubDomains`: Apply to all subdomains
- `preload`: Allow inclusion in browser HSTS preload list

**Important**: Once set, the browser will ONLY use HTTPS for your domain for the specified time. Make sure your HTTPS is working perfectly first!

---

### 5. X-XSS-Protection

**Purpose**: Enable browser's XSS filter.

```php
<?php
header("X-XSS-Protection: 1; mode=block");
```

**Values**:
- `0`: Disable XSS filter (don't use)
- `1`: Enable XSS filter
- `1; mode=block`: Block page if XSS detected (recommended)

**Note**: This header is deprecated in modern browsers because CSP is better. But it doesn't hurt to include it for older browsers.

---

### 6. Referrer-Policy

**Purpose**: Control how much referrer information is sent.

**The problem**:
```
User visits: https://example.com/admin/secret-page?token=abc123
Clicks external link
Referrer sent: https://example.com/admin/secret-page?token=abc123
Token leaked!
```

**Protection**:
```php
<?php
// Don't send referrer on cross-origin requests
header("Referrer-Policy: strict-origin-when-cross-origin");
```

**Options**:
- `no-referrer`: Never send referrer
- `no-referrer-when-downgrade`: Send on HTTPS→HTTPS, not HTTPS→HTTP
- `same-origin`: Only send to same origin
- `origin`: Send only origin, not full URL
- `strict-origin`: Send origin on HTTPS→HTTPS, nothing on HTTPS→HTTP
- `strict-origin-when-cross-origin`: Full URL to same origin, origin only to others (recommended)

---

### 7. Permissions-Policy (formerly Feature-Policy)

**Purpose**: Control which browser features can be used.

```php
<?php
header("Permissions-Policy: " .
    "camera=(), " .              // Disable camera
    "microphone=(), " .          // Disable microphone
    "geolocation=(self), " .     // Geolocation only for your site
    "payment=(self)"             // Payment API only for your site
);
```

**Common features to restrict**:
- `camera`: Camera access
- `microphone`: Microphone access
- `geolocation`: Location access
- `payment`: Payment Request API
- `usb`: USB device access
- `autoplay`: Autoplay videos
- `fullscreen`: Fullscreen mode

---

### 8. X-Permitted-Cross-Domain-Policies

**Purpose**: Restrict Flash/PDF cross-domain requests.

```php
<?php
header("X-Permitted-Cross-Domain-Policies: none");
```

Even though Flash is dead, some PDF readers still use this.

---

### 9. Cache-Control (For Sensitive Pages)

**Purpose**: Prevent sensitive data from being cached.

```php
<?php
// For pages with sensitive data (admin, account, etc.)
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");
```

This prevents:
- Sensitive data in browser cache
- Back button showing logged-out data
- Shared computer seeing previous user's data

---

### 10. X-Content-Security-Policy (Legacy)

**Purpose**: Old CSP header for old browsers.

```php
<?php
// Include for older browsers
header("X-Content-Security-Policy: default-src 'self'");
header("X-WebKit-CSP: default-src 'self'");
```

---

## Complete Security Headers Implementation

```php
<?php
// SecurityHeaders.php

class SecurityHeaders {

    private bool $isHttps;

    public function __construct() {
        $this->isHttps = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
    }

    /**
     * Set all security headers
     */
    public function setAll(array $options = []): void {
        $this->setCSP($options['csp'] ?? []);
        $this->setFrameOptions($options['frameOptions'] ?? 'DENY');
        $this->setContentTypeOptions();
        $this->setXSSProtection();
        $this->setReferrerPolicy($options['referrerPolicy'] ?? 'strict-origin-when-cross-origin');
        $this->setPermissionsPolicy($options['permissions'] ?? []);
        $this->setPermittedCrossDomainPolicies();

        if ($this->isHttps) {
            $this->setHSTS($options['hstsMaxAge'] ?? 31536000);
        }
    }

    /**
     * Set Content-Security-Policy
     */
    public function setCSP(array $directives = []): void {
        $default = [
            'default-src' => ["'self'"],
            'script-src' => ["'self'"],
            'style-src' => ["'self'", "'unsafe-inline'"],
            'img-src' => ["'self'", 'https:', 'data:'],
            'font-src' => ["'self'"],
            'connect-src' => ["'self'"],
            'frame-ancestors' => ["'none'"],
            'base-uri' => ["'self'"],
            'form-action' => ["'self'"],
            'object-src' => ["'none'"]
        ];

        $directives = array_merge($default, $directives);

        $csp = [];
        foreach ($directives as $directive => $values) {
            $csp[] = $directive . ' ' . implode(' ', $values);
        }

        header('Content-Security-Policy: ' . implode('; ', $csp));
    }

    /**
     * Set Content-Security-Policy with nonce for inline scripts
     */
    public function setCSPWithNonce(array $directives = []): string {
        $nonce = base64_encode(random_bytes(16));

        $directives['script-src'] = array_merge(
            $directives['script-src'] ?? ["'self'"],
            ["'nonce-$nonce'"]
        );

        $this->setCSP($directives);

        return $nonce;
    }

    /**
     * Set X-Frame-Options
     */
    public function setFrameOptions(string $option = 'DENY'): void {
        // DENY or SAMEORIGIN
        if (in_array($option, ['DENY', 'SAMEORIGIN'])) {
            header("X-Frame-Options: $option");
        }
    }

    /**
     * Set X-Content-Type-Options
     */
    public function setContentTypeOptions(): void {
        header('X-Content-Type-Options: nosniff');
    }

    /**
     * Set Strict-Transport-Security (HTTPS only)
     */
    public function setHSTS(int $maxAge = 31536000, bool $includeSubDomains = true, bool $preload = false): void {
        if (!$this->isHttps) {
            return; // Only set on HTTPS
        }

        $header = "Strict-Transport-Security: max-age=$maxAge";

        if ($includeSubDomains) {
            $header .= '; includeSubDomains';
        }

        if ($preload) {
            $header .= '; preload';
        }

        header($header);
    }

    /**
     * Set X-XSS-Protection
     */
    public function setXSSProtection(): void {
        header('X-XSS-Protection: 1; mode=block');
    }

    /**
     * Set Referrer-Policy
     */
    public function setReferrerPolicy(string $policy = 'strict-origin-when-cross-origin'): void {
        header("Referrer-Policy: $policy");
    }

    /**
     * Set Permissions-Policy
     */
    public function setPermissionsPolicy(array $policies = []): void {
        $default = [
            'camera' => [],
            'microphone' => [],
            'geolocation' => ['self'],
            'payment' => ['self']
        ];

        $policies = array_merge($default, $policies);

        $header = [];
        foreach ($policies as $feature => $allowlist) {
            if (empty($allowlist)) {
                $header[] = "$feature=()";
            } else {
                $origins = implode(' ', array_map(function($origin) {
                    return $origin === 'self' ? 'self' : "\"$origin\"";
                }, $allowlist));
                $header[] = "$feature=($origins)";
            }
        }

        header('Permissions-Policy: ' . implode(', ', $header));
    }

    /**
     * Set X-Permitted-Cross-Domain-Policies
     */
    public function setPermittedCrossDomainPolicies(): void {
        header('X-Permitted-Cross-Domain-Policies: none');
    }

    /**
     * Set cache headers for sensitive pages
     */
    public function setNoCacheHeaders(): void {
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Expires: 0');
    }

    /**
     * Remove server information header
     */
    public function removeServerHeader(): void {
        header_remove('X-Powered-By');
        // Note: Server header removal requires PHP/server config
    }
}
```

### Using the Security Headers Class

```php
<?php
// bootstrap.php
require_once 'SecurityHeaders.php';

$headers = new SecurityHeaders();
$headers->setAll();

// Or with custom options
$headers->setAll([
    'csp' => [
        'script-src' => ["'self'", 'https://cdn.jsdelivr.net'],
        'style-src' => ["'self'", 'https://fonts.googleapis.com', "'unsafe-inline'"],
        'font-src' => ["'self'", 'https://fonts.gstatic.com']
    ],
    'frameOptions' => 'SAMEORIGIN',
    'referrerPolicy' => 'no-referrer',
    'hstsMaxAge' => 63072000 // 2 years
]);
```

```php
<?php
// admin-page.php
require_once 'bootstrap.php';

// Additional headers for sensitive pages
$headers->setNoCacheHeaders();

// ... rest of page
```

```php
<?php
// page-with-inline-scripts.php
require_once 'bootstrap.php';

$nonce = $headers->setCSPWithNonce([
    'script-src' => ["'self'", 'https://cdn.example.com']
]);
?>

<!DOCTYPE html>
<html>
<head>
    <!-- Inline script with nonce -->
    <script nonce="<?= $nonce ?>">
        console.log('This is allowed!');
    </script>
</head>
</html>
```

---

## Testing Security Headers

### Online Tools

1. **securityheaders.com**: Comprehensive header analysis
   ```
   Visit: https://securityheaders.com
   Enter your URL
   Get grade A+ rating
   ```

2. **Mozilla Observatory**: Another excellent tool
   ```
   Visit: https://observatory.mozilla.org
   Scan your site
   Get detailed recommendations
   ```

### Manual Testing

```bash
# Check headers with curl
curl -I https://your-site.com

# Look for:
# Content-Security-Policy
# X-Frame-Options
# X-Content-Type-Options
# Strict-Transport-Security
# X-XSS-Protection
# Referrer-Policy
```

### Browser DevTools

```
1. Open DevTools (F12)
2. Go to Network tab
3. Reload page
4. Click on document request
5. Check Response Headers section
```

---

## Security Headers Checklist

- [ ] **Content-Security-Policy**: Prevent XSS attacks
- [ ] **X-Frame-Options or CSP frame-ancestors**: Prevent clickjacking
- [ ] **X-Content-Type-Options**: Prevent MIME sniffing
- [ ] **Strict-Transport-Security**: Force HTTPS (if using HTTPS)
- [ ] **X-XSS-Protection**: Enable browser XSS filter
- [ ] **Referrer-Policy**: Control referrer information
- [ ] **Permissions-Policy**: Restrict browser features
- [ ] **Cache-Control**: No-cache for sensitive pages
- [ ] **Remove X-Powered-By**: Don't leak PHP version
- [ ] **Test with online tools**: Verify all headers present

---

## Headers for Different Page Types

### Public Pages

```php
<?php
$headers->setAll([
    'frameOptions' => 'SAMEORIGIN', // Allow embedding on your site
    'referrerPolicy' => 'strict-origin-when-cross-origin'
]);
```

### Admin/Sensitive Pages

```php
<?php
$headers->setAll([
    'frameOptions' => 'DENY', // No embedding
    'referrerPolicy' => 'no-referrer' // No referrer
]);
$headers->setNoCacheHeaders(); // Don't cache
```

### API Endpoints

```php
<?php
$headers->setAll([
    'csp' => [
        'default-src' => ["'none'"] // API doesn't need to load resources
    ],
    'frameOptions' => 'DENY'
]);
```

### File Download Pages

```php
<?php
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="document.pdf"');
header('X-Content-Type-Options: nosniff');
header('Content-Security-Policy: default-src \'none\'');
```

---

## Key Takeaways

1. **Security headers add defense layers** - Easy to implement, significant protection
2. **Content-Security-Policy** - Most powerful header for preventing XSS
3. **X-Frame-Options** - Prevents clickjacking attacks
4. **HSTS** - Forces HTTPS (only on HTTPS connections)
5. **X-Content-Type-Options** - Prevents MIME sniffing
6. **Test with online tools** - securityheaders.com gives you a grade
7. **Use CSP nonce** - For inline scripts while maintaining security
8. **Different headers for different pages** - Admin vs public
9. **Start with Report-Only** - Test CSP before enforcing
10. **Defense in depth** - Headers + code security = strong protection

---

## What's Next?

Security headers configured! Next: **Rate Limiting** - preventing brute force attacks and abuse.

You'll learn:
- What is rate limiting
- Implementing login attempt limits
- API rate limiting
- IP-based and user-based limits
- Using Redis for scalable rate limiting
- Protecting against brute force attacks
