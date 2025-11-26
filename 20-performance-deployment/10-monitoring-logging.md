# Monitoring and Logging

**Duration**: 4-5 hours

---

## Introduction

You've deployed your application - congratulations! But your work isn't done. You need to know when things break, why they break, and how your application performs. Monitoring and logging are your eyes and ears in production.

**What you'll learn:**
- Application logging strategies
- Error tracking with Sentry
- Performance monitoring
- Uptime monitoring
- Log management and analysis
- Alerting and notifications

---

## Understanding Monitoring vs Logging

### Logging

**What it is:** Recording events that happen in your application

**Examples:**
- User logged in
- Payment processed
- Email sent
- Error occurred
- Query executed

**Purpose:** Debug issues, audit trails, compliance

### Monitoring

**What it is:** Watching metrics and health of your application

**Examples:**
- Response time
- Error rate
- CPU/memory usage
- Request count
- Queue length

**Purpose:** Detect issues proactively, measure performance

### Why Both Matter

**Scenario: Your site is slow**

**Monitoring tells you:**
- Response time increased from 200ms to 5000ms at 2pm
- CPU usage spiked to 90%
- 500 errors increased by 300%

**Logging tells you:**
- Which endpoints are slow
- Which database queries are taking 10+ seconds
- Exact error messages and stack traces
- What users were doing when it broke

---

## Laravel Logging

### Log Channels

**config/logging.php:**
```php
'channels' => [
    'stack' => [
        'driver' => 'stack',
        'channels' => ['single', 'slack'], // Multiple channels
    ],

    'single' => [
        'driver' => 'single',
        'path' => storage_path('logs/laravel.log'),
        'level' => 'debug',
    ],

    'daily' => [
        'driver' => 'daily',
        'path' => storage_path('logs/laravel.log'),
        'level' => 'debug',
        'days' => 14, // Keep 14 days of logs
    ],

    'slack' => [
        'driver' => 'slack',
        'url' => env('LOG_SLACK_WEBHOOK_URL'),
        'username' => 'Laravel Log',
        'emoji' => ':boom:',
        'level' => 'critical', // Only critical errors to Slack
    ],

    'stderr' => [
        'driver' => 'monolog',
        'handler' => StreamHandler::class,
        'with' => [
            'stream' => 'php://stderr',
        ],
    ],

    'syslog' => [
        'driver' => 'syslog',
        'level' => 'debug',
    ],
],
```

### Log Levels

From most to least severe:

```php
Log::emergency('System is down!');        // Level 0
Log::alert('Action required immediately'); // Level 1
Log::critical('Critical component failed'); // Level 2
Log::error('Runtime error');               // Level 3
Log::warning('Something unexpected');      // Level 4
Log::notice('Normal but significant');     // Level 5
Log::info('Interesting event');            // Level 6
Log::debug('Detailed debug information');  // Level 7
```

**Best practices:**

**Production:**
```
LOG_LEVEL=error  # Only errors and above
```

**Development:**
```
LOG_LEVEL=debug  # Everything
```

### Logging in Code

**Basic logging:**
```php
use Illuminate\Support\Facades\Log;

// Simple message
Log::info('User logged in');

// With context
Log::info('User logged in', [
    'user_id' => $user->id,
    'ip' => request()->ip(),
]);

// Error with exception
try {
    $payment->process();
} catch (\Exception $e) {
    Log::error('Payment processing failed', [
        'user_id' => $user->id,
        'amount' => $payment->amount,
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
    ]);

    throw $e;
}
```

**Contextual logging:**
```php
// Add context for all logs in request
Log::withContext([
    'user_id' => auth()->id(),
    'session_id' => session()->getId(),
]);

// Now all logs include this context
Log::info('Action performed'); // Includes user_id and session_id
```

**To specific channel:**
```php
// Log to Slack
Log::channel('slack')->critical('Payment system down!');

// Log to multiple channels
Log::stack(['single', 'slack'])->error('Critical error');
```

### Custom Log Context

**Add to every log entry:**

**app/Providers/AppServiceProvider.php:**
```php
use Illuminate\Support\Facades\Log;

public function boot()
{
    if ($this->app->environment('production')) {
        Log::shareContext([
            'environment' => 'production',
            'server' => gethostname(),
            'release' => config('app.release', 'unknown'),
        ]);
    }
}
```

### Request Logging Middleware

**Log all requests:**

**app/Http/Middleware/LogRequests.php:**
```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LogRequests
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        Log::info('Request handled', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_id' => auth()->id(),
            'status' => $response->status(),
            'duration' => microtime(true) - LARAVEL_START,
        ]);

        return $response;
    }
}
```

**Register middleware:**
```php
// app/Http/Kernel.php
protected $middleware = [
    // ...
    \App\Http\Middleware\LogRequests::class,
];
```

---

## Error Tracking with Sentry

### What Is Sentry?

**Sentry** is an error tracking service that:
- Captures all errors and exceptions
- Groups similar errors
- Shows stack traces and context
- Tracks error frequency
- Notifies you of new errors

**Better than log files because:**
- Organized by error type
- Shows how often errors occur
- Shows which users affected
- Shows environment (browser, OS, etc.)
- Includes user context (actions before error)

### Setting Up Sentry

**1. Create Sentry account:**
- Visit: https://sentry.io
- Create account
- Create new Laravel project
- Get your DSN (looks like: https://xxx@xxx.ingest.sentry.io/xxx)

**2. Install Laravel SDK:**
```bash
composer require sentry/sentry-laravel
```

**3. Publish config:**
```bash
php artisan vendor:publish --provider="Sentry\Laravel\ServiceProvider"
```

**4. Configure:**

**.env:**
```
SENTRY_LARAVEL_DSN=https://xxx@xxx.ingest.sentry.io/xxx
SENTRY_TRACES_SAMPLE_RATE=0.2  # 20% of transactions for performance monitoring
```

**config/sentry.php:**
```php
return [
    'dsn' => env('SENTRY_LARAVEL_DSN'),

    'environment' => env('APP_ENV', 'production'),

    'release' => env('SENTRY_RELEASE', config('app.version')),

    'breadcrumbs' => [
        'logs' => true,      // Include log breadcrumbs
        'sql_queries' => true, // Include SQL queries
        'sql_bindings' => true, // Include query bindings
    ],

    'traces_sample_rate' => env('SENTRY_TRACES_SAMPLE_RATE', 0.0),

    'send_default_pii' => false, // Don't send personally identifiable info
];
```

**5. Test it works:**
```php
Route::get('/sentry-test', function () {
    throw new \Exception('Testing Sentry!');
});
```

Visit `/sentry-test` and check Sentry dashboard for the error.

### Using Sentry

**Automatic error capture:**
All uncaught exceptions are automatically sent to Sentry.

**Manual error capture:**
```php
use Sentry\SentrySdk;

try {
    // Risky operation
} catch (\Exception $e) {
    SentrySdk::getCurrentHub()->captureException($e);

    // Handle error
}
```

**Add user context:**
```php
// In a middleware or controller
\Sentry\configureScope(function (\Sentry\State\Scope $scope) {
    $scope->setUser([
        'id' => auth()->id(),
        'email' => auth()->user()->email,
        'username' => auth()->user()->name,
    ]);
});
```

**Add custom context:**
```php
\Sentry\configureScope(function (\Sentry\State\Scope $scope) {
    $scope->setExtra('order_id', $order->id);
    $scope->setExtra('payment_method', $paymentMethod);
    $scope->setTag('subscription', 'premium');
});
```

**Capture messages:**
```php
\Sentry\captureMessage('Something suspicious happened', \Sentry\Severity::warning());
```

### Sentry Features

**1. Error Grouping:**
Similar errors grouped together (e.g., all "Division by zero" errors)

**2. Release Tracking:**
See which deployment introduced an error:
```bash
# Tag your deployment
SENTRY_RELEASE=$(git rev-parse --short HEAD)
```

**3. Performance Monitoring:**
Track slow transactions:
```php
$transaction = \Sentry\startTransaction(['name' => 'checkout.process']);

// Your code

$transaction->finish();
```

**4. Breadcrumbs:**
See what user did before error:
```
1. User logged in
2. Navigated to /products
3. Added product to cart
4. Clicked checkout
5. Error occurred
```

**5. Alerts:**
Get notified when:
- New error type occurs
- Error frequency spikes
- Critical error happens

---

## Performance Monitoring

### Laravel Telescope

**Install (development/staging only):**
```bash
composer require laravel/telescope --dev
php artisan telescope:install
php artisan migrate
```

**Features:**
- Request monitoring
- Query monitoring (see N+1 problems)
- Job monitoring
- Cache hits/misses
- Exception tracking
- Slow queries

**Access:**
```
https://yourdomain.com/telescope
```

**Protect in staging:**

**app/Providers/TelescopeServiceProvider.php:**
```php
protected function gate()
{
    Gate::define('viewTelescope', function ($user) {
        return in_array($user->email, [
            'admin@example.com',
        ]);
    });
}
```

### New Relic (Production)

**What it does:**
- Application Performance Monitoring (APM)
- Shows slowest endpoints
- Database query analysis
- External service monitoring
- Real user monitoring

**Setup:**
1. Sign up at https://newrelic.com
2. Install agent on server:
```bash
# Add repository
echo 'deb http://apt.newrelic.com/debian/ newrelic non-free' | sudo tee /etc/apt/sources.list.d/newrelic.list
wget -O- https://download.newrelic.com/548C16BF.gpg | sudo apt-key add -

# Install
sudo apt update
sudo apt install newrelic-php5

# Configure
sudo newrelic-install install
```

3. Restart PHP-FPM:
```bash
sudo systemctl restart php8.2-fpm
```

**View metrics:**
Visit New Relic dashboard

### Application Performance Metrics

**Track in your app:**

**app/Http/Middleware/PerformanceMonitoring.php:**
```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PerformanceMonitoring
{
    public function handle(Request $request, Closure $next)
    {
        $start = microtime(true);

        $response = $next($request);

        $duration = (microtime(true) - $start) * 1000; // Convert to ms

        // Log slow requests
        if ($duration > 1000) { // Over 1 second
            Log::warning('Slow request detected', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'duration' => round($duration, 2) . 'ms',
                'memory' => round(memory_get_peak_usage() / 1024 / 1024, 2) . 'MB',
                'queries' => count(\DB::getQueryLog()),
            ]);
        }

        return $response;
    }
}
```

---

## Uptime Monitoring

### What Is Uptime Monitoring?

Services that check if your site is up and alert you if it's down.

**What they check:**
- HTTP status (200 = good, 500 = bad)
- Response time
- SSL certificate validity
- DNS resolution

**Why it matters:**
You want to know your site is down before your users tell you!

### Popular Services

**1. UptimeRobot (Free tier available)**
- Visit: https://uptimerobot.com
- Create monitor
- Set check interval (5 minutes on free plan)
- Get alerts via email, SMS, Slack

**2. Pingdom**
- More advanced monitoring
- Multiple locations
- Transaction monitoring

**3. Better Uptime**
- Status page included
- Incident management
- Modern interface

**4. Oh Dear (Laravel-focused)**
- Made for Laravel apps
- Monitors routes
- Checks scheduled jobs
- Mixed content detection
- Broken links

### Setting Up UptimeRobot

**1. Create account:**
Visit https://uptimerobot.com

**2. Add monitor:**
- Type: HTTP(s)
- URL: https://yourdomain.com
- Monitoring interval: 5 minutes
- Alert contacts: Your email

**3. Add health check endpoint:**
Monitor `/health` instead of homepage (more reliable)

**4. Set up alerts:**
- Email
- SMS (paid)
- Slack webhook
- Webhook to your endpoint

### Health Check Endpoint

**routes/web.php:**
```php
Route::get('/health', function () {
    $healthy = true;
    $checks = [];

    // Check database
    try {
        DB::connection()->getPdo();
        $checks['database'] = 'ok';
    } catch (\Exception $e) {
        $checks['database'] = 'failed';
        $healthy = false;
    }

    // Check cache
    try {
        Cache::put('health-check', true, 10);
        $checks['cache'] = Cache::get('health-check') ? 'ok' : 'failed';
    } catch (\Exception $e) {
        $checks['cache'] = 'failed';
        $healthy = false;
    }

    // Check storage
    $checks['storage'] = Storage::exists('public') ? 'ok' : 'failed';

    // Check queue
    try {
        $size = Redis::connection()->llen('queues:default');
        $checks['queue'] = $size < 1000 ? 'ok' : 'backed_up';
    } catch (\Exception $e) {
        $checks['queue'] = 'failed';
    }

    return response()->json([
        'status' => $healthy ? 'healthy' : 'unhealthy',
        'timestamp' => now()->toIso8601String(),
        'checks' => $checks,
    ], $healthy ? 200 : 503);
});
```

---

## Log Management

### The Problem

**Without log management:**
- Logs fill up disk space
- Hard to search through thousands of log lines
- Logs lost when server dies
- Can't correlate logs across multiple servers

### Solutions

**1. Log Rotation (Built into Laravel)**

**config/logging.php:**
```php
'daily' => [
    'driver' => 'daily',
    'path' => storage_path('logs/laravel.log'),
    'level' => 'debug',
    'days' => 14, // Keep 2 weeks, delete older
],
```

**2. Centralized Logging**

**Option A: Papertrail (Simple, Free tier)**
- Visit: https://papertrailapp.com
- Create account
- Get endpoint: logs.papertrailapp.com:12345

**Configure rsyslog:**
```bash
# /etc/rsyslog.d/70-laravel.conf
$ModLoad imfile
$InputFilePollInterval 10
$PrivDropToGroup adm

$InputFileName /home/deployer/myapp/current/storage/logs/laravel.log
$InputFileTag laravel:
$InputFileStateFile stat-laravel
$InputFileSeverity info
$InputRunFileMonitor

*.* @@logs.papertrailapp.com:12345
```

**Restart rsyslog:**
```bash
sudo systemctl restart rsyslog
```

**Option B: ELK Stack (Advanced)**
- Elasticsearch: Store logs
- Logstash: Process logs
- Kibana: Visualize logs

**Option C: CloudWatch Logs (AWS)**

Install agent:
```bash
wget https://s3.amazonaws.com/amazoncloudwatch-agent/ubuntu/amd64/latest/amazon-cloudwatch-agent.deb
sudo dpkg -i -E ./amazon-cloudwatch-agent.deb
```

Configure to send Laravel logs.

---

## Alerting Strategies

### When to Alert

**Alert for:**
- Site down (uptime monitoring)
- Critical errors (Sentry)
- Queue backed up (> 1000 jobs)
- Disk space low (< 10%)
- Memory usage high (> 90%)
- Failed jobs (> 100 per hour)

**Don't alert for:**
- Individual non-critical errors
- Single slow requests
- Expected warnings
- Development/staging issues

### Alert Channels

**1. Email:**
Good for non-urgent alerts
```php
Mail::to('admin@example.com')->send(new AlertEmail($issue));
```

**2. Slack:**
Good for team awareness
```php
Log::channel('slack')->critical('Payment system down!');
```

**3. SMS/Phone:**
For critical issues only (expensive!)
```php
// Using Twilio
$twilio->messages->create('+1234567890', [
    'from' => '+0987654321',
    'body' => 'URGENT: Payment system down!',
]);
```

**4. PagerDuty:**
For on-call rotations
```php
// Trigger PagerDuty incident
Http::post('https://events.pagerduty.com/v2/enqueue', [
    'routing_key' => config('services.pagerduty.key'),
    'event_action' => 'trigger',
    'payload' => [
        'summary' => 'Payment system down',
        'severity' => 'critical',
        'source' => 'laravel-app',
    ],
]);
```

### Alert Fatigue

**Problem:** Too many alerts = ignored alerts

**Solutions:**

**1. Group similar alerts:**
Don't send 100 emails for same error - send one summary

**2. Use thresholds:**
Alert if error rate > 10 per minute (not every error)

**3. Alert on trends:**
Alert if error rate increased 300% (not just errors happening)

**4. Escalation:**
- First alert: Slack
- If not acknowledged in 5 minutes: Email
- If not acknowledged in 15 minutes: SMS

---

## Monitoring Dashboard

### Key Metrics to Display

**1. Application metrics:**
- Requests per minute
- Average response time
- Error rate
- Active users

**2. System metrics:**
- CPU usage
- Memory usage
- Disk usage
- Network traffic

**3. Business metrics:**
- New signups
- Revenue (today/this month)
- Active subscriptions
- Conversion rate

### Creating a Dashboard

**Option 1: Laravel Pulse (Official, Laravel 10+)**

```bash
composer require laravel/pulse
php artisan vendor:publish --provider="Laravel\Pulse\PulseServiceProvider"
php artisan migrate
```

**Access:**
```
https://yourdomain.com/pulse
```

Shows:
- Requests
- Usage
- Exceptions
- Queues
- Cache

**Option 2: Grafana + Prometheus**

Complex but powerful - visualize any metric.

**Option 3: Custom Dashboard**

**routes/web.php:**
```php
Route::get('/dashboard/metrics', function () {
    return response()->json([
        'requests_today' => Redis::get('metrics:requests:' . today()),
        'revenue_today' => Order::whereDate('created_at', today())->sum('total'),
        'active_users' => User::where('last_seen_at', '>', now()->subMinutes(5))->count(),
        'queue_size' => Redis::llen('queues:default'),
        'error_count' => Redis::get('metrics:errors:' . today()),
    ]);
})->middleware('auth');
```

Display with Alpine.js:
```blade
<div x-data="metrics" x-init="fetch()">
    <div class="grid grid-cols-4 gap-4">
        <div class="stat">
            <div class="label">Requests Today</div>
            <div class="value" x-text="data.requests_today"></div>
        </div>
        <!-- More metrics -->
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('metrics', () => ({
        data: {},
        fetch() {
            fetch('/dashboard/metrics')
                .then(r => r.json())
                .then(data => this.data = data);

            setInterval(() => this.fetch(), 30000); // Refresh every 30s
        }
    }));
});
</script>
```

---

## Best Practices

### 1. Log Meaningful Information

**❌ Bad:**
```php
Log::error('Error occurred');
```

**✅ Good:**
```php
Log::error('Payment processing failed', [
    'user_id' => $user->id,
    'order_id' => $order->id,
    'amount' => $order->total,
    'gateway' => $gateway,
    'error_message' => $e->getMessage(),
    'gateway_response' => $gatewayResponse,
]);
```

### 2. Don't Log Sensitive Data

**❌ Bad:**
```php
Log::info('User logged in', [
    'password' => $request->password, // Never!
    'credit_card' => $request->card_number, // Never!
]);
```

**✅ Good:**
```php
Log::info('User logged in', [
    'user_id' => $user->id,
    'ip' => $request->ip(),
    'user_agent' => $request->userAgent(),
]);
```

### 3. Use Appropriate Log Levels

```php
// Critical: System completely broken
Log::critical('Database server unreachable');

// Error: Something failed but system still working
Log::error('Failed to send email to ' . $user->email);

// Warning: Something unexpected but handled
Log::warning('Slow query detected: ' . $query);

// Info: Notable event
Log::info('User ' . $user->id . ' created order ' . $order->id);

// Debug: Detailed info for debugging (not in production)
Log::debug('Order total calculated', ['subtotal' => $subtotal, 'tax' => $tax]);
```

### 4. Monitor Business Metrics

Don't just monitor technical metrics:
```php
// Track business events
Log::info('Sale completed', [
    'order_id' => $order->id,
    'total' => $order->total,
    'customer_type' => $customer->type,
]);
```

Use these logs to:
- Generate reports
- Detect fraud
- Understand user behavior

---

## Quick Reference

```bash
# View logs
tail -f storage/logs/laravel.log

# View only errors
tail -f storage/logs/laravel.log | grep ERROR

# Clear old logs
rm storage/logs/laravel-2025-01-*.log

# Check disk space
df -h

# Check memory
free -h

# Check processes
ps aux | grep php-fpm
```

```php
// Logging
Log::error('Message', ['context' => 'data']);
Log::channel('slack')->critical('Critical!');

// Sentry
\Sentry\captureException($exception);
\Sentry\captureMessage('Something happened');

// Performance tracking
$start = microtime(true);
// ... code ...
$duration = microtime(true) - $start;
```

---

## Monitoring Checklist

```
✓ Logging configured
  ✓ Daily rotation enabled
  ✓ Appropriate log level set
  ✓ Sensitive data not logged

✓ Error tracking
  ✓ Sentry installed
  ✓ User context added
  ✓ Alerts configured

✓ Uptime monitoring
  ✓ UptimeRobot configured
  ✓ Health check endpoint created
  ✓ Alerts set up (email, Slack)

✓ Performance monitoring
  ✓ Slow queries identified
  ✓ Response times tracked
  ✓ Memory usage monitored

✓ Alerting
  ✓ Critical alerts go to SMS
  ✓ Warnings go to Slack
  ✓ Info goes to logs only
  ✓ Alert fatigue prevented

✓ Dashboard
  ✓ Key metrics visible
  ✓ Real-time updates
  ✓ Accessible to team
```

---

## Practice Questions

1. **What's the difference between monitoring and logging?**

2. **What log level should you use for production errors?** What about development?

3. **What is Sentry?** What advantages does it have over log files?

4. **Why do you need uptime monitoring?** What should it check?

5. **What metrics are most important to monitor?** Technical and business?

6. **How do you prevent alert fatigue?**

---

## Congratulations!

You've completed Module 20! You now know how to:
- Optimize database queries and use indexes
- Implement effective caching strategies
- Use queue jobs for background processing
- Optimize assets for fast loading
- Set up and configure production servers
- Manage environment configuration
- Secure applications with SSL/TLS
- Deploy safely with zero downtime
- Monitor and log application health

**You're ready to build and deploy professional Laravel applications!**

**Next module:** AI-Assisted Development - Learn to use AI tools effectively to amplify your development skills.
