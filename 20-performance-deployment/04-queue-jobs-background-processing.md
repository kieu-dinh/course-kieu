# Queue Jobs and Background Processing

**Duration**: 4-5 hours

---

## Introduction

Some tasks take too long to perform during a web request: sending emails, processing images, generating reports, calling external APIs. Queue jobs let you defer these tasks to background workers, keeping your application responsive.

**What you'll learn:**
- What queue jobs are and why they matter
- Setting up Laravel queues
- Creating and dispatching jobs
- Queue workers and supervisors
- Handling failed jobs
- Best practices for background processing

---

## Understanding Queue Jobs

### The Problem: Slow Web Requests

**Without queues:**
```php
public function register(Request $request)
{
    $user = User::create($request->validated());

    // Send welcome email (3 seconds)
    Mail::to($user)->send(new WelcomeEmail($user));

    // Notify admin (2 seconds)
    Mail::to('admin@example.com')->send(new NewUserNotification($user));

    // Create sample data (5 seconds)
    $this->createSampleDataForUser($user);

    return redirect()->dashboard(); // User waits 10 seconds!
}
```

User experience: "Why is this taking so long?"

**With queues:**
```php
public function register(Request $request)
{
    $user = User::create($request->validated());

    // Queue these tasks - instant response!
    Mail::to($user)->queue(new WelcomeEmail($user));
    Mail::to('admin@example.com')->queue(new NewUserNotification($user));
    CreateSampleDataJob::dispatch($user);

    return redirect()->dashboard(); // User sees page immediately!
}
```

User experience: Instant response, work happens in background.

### How Queues Work

```
┌─────────┐         ┌───────────┐         ┌────────────┐
│   Web   │  push   │   Queue   │  pull   │   Worker   │
│ Request │────────>│  Storage  │<────────│  Process   │
└─────────┘         └───────────┘         └────────────┘
    ↓                     ↓                      ↓
 Instant              Job stored            Job executed
 Response            (Redis/DB)            in background
```

1. Web request pushes job to queue
2. Response sent immediately
3. Background worker pulls and processes job

---

## Queue Drivers

Laravel supports multiple queue backends:

### 1. Sync (No Queue)
Jobs run immediately (for testing)
```
QUEUE_CONNECTION=sync
```

### 2. Database
Jobs stored in database table
- Good for: Small apps, shared hosting
- Pros: No extra setup, persistent
- Cons: Slower than Redis

```
QUEUE_CONNECTION=database
```

### 3. Redis (Recommended)
Jobs stored in Redis
- Good for: Production apps
- Pros: Fast, reliable, feature-rich
- Cons: Requires Redis server

```
QUEUE_CONNECTION=redis
```

### 4. SQS (Amazon)
AWS queue service
- Good for: AWS-hosted apps
- Pros: Scalable, managed
- Cons: Costs money, AWS-specific

### 5. Beanstalkd
Dedicated queue server
- Good for: High-throughput apps
- Pros: Very fast, purpose-built
- Cons: Another service to manage

---

## Setting Up Queues

### Step 1: Choose Driver

**.env:**
```
QUEUE_CONNECTION=redis
```

### Step 2: Database Queue Setup (if using database)

**Create migration:**
```bash
php artisan queue:table
php artisan migrate
```

**Creates:**
- `jobs` table (queued jobs)
- `failed_jobs` table (failed jobs)

### Step 3: Redis Setup (if using Redis)

Redis is included with Laravel Herd:

**config/queue.php:**
```php
'connections' => [
    'redis' => [
        'driver' => 'redis',
        'connection' => 'default',
        'queue' => env('REDIS_QUEUE', 'default'),
        'retry_after' => 90,
        'block_for' => null,
    ],
],
```

**.env:**
```
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

---

## Creating Jobs

### Generate a Job

```bash
php artisan make:job ProcessPodcast
```

**Creates:** `app/Jobs/ProcessPodcast.php`

```php
<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessPodcast implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Podcast $podcast
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Process the podcast
    }
}
```

### Anatomy of a Job

**Key traits:**

1. **Dispatchable**: Allows `Job::dispatch()`
2. **InteractsWithQueue**: Access queue information
3. **Queueable**: Job can be queued
4. **SerializesModels**: Safely serialize Eloquent models

**Key interface:**

- **ShouldQueue**: Marks job as queueable

### Simple Job Example

```php
<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendWelcomeEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public User $user
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Mail::to($this->user->email)->send(new WelcomeEmail($this->user));
    }
}
```

---

## Dispatching Jobs

### Basic Dispatch

```php
use App\Jobs\SendWelcomeEmail;

// Dispatch to queue
SendWelcomeEmail::dispatch($user);

// Dispatch with delay
SendWelcomeEmail::dispatch($user)->delay(now()->addMinutes(10));

// Dispatch to specific queue
SendWelcomeEmail::dispatch($user)->onQueue('emails');

// Dispatch to specific connection
SendWelcomeEmail::dispatch($user)->onConnection('redis');
```

### Dispatch After Response

Job runs after HTTP response sent:
```php
SendWelcomeEmail::dispatchAfterResponse($user);
```

Good for tasks that should run ASAP but not block the response.

### Conditional Dispatch

```php
SendWelcomeEmail::dispatchIf($condition, $user);
SendWelcomeEmail::dispatchUnless($condition, $user);
```

### Dispatch Sync (for testing)

```php
SendWelcomeEmail::dispatchSync($user); // Runs immediately
```

### Chain Jobs

Run jobs sequentially:
```php
use Illuminate\Support\Facades\Bus;

Bus::chain([
    new ProcessPodcast($podcast),
    new OptimizePodcast($podcast),
    new ReleasePodcast($podcast),
])->dispatch();
```

If one fails, the rest don't run.

### Batch Jobs

Run multiple jobs, track progress:
```php
use Illuminate\Support\Facades\Bus;

$batch = Bus::batch([
    new ProcessPodcast($podcast1),
    new ProcessPodcast($podcast2),
    new ProcessPodcast($podcast3),
])->then(function (Batch $batch) {
    // All jobs completed successfully
})->catch(function (Batch $batch, Throwable $e) {
    // First batch job failure
})->finally(function (Batch $batch) {
    // Batch finished executing
})->dispatch();

// Check batch progress
$batch->progress(); // 66.67
$batch->pending(); // 1
$batch->processed(); // 2
```

---

## Running Queue Workers

### Start a Worker

```bash
php artisan queue:work
```

This process:
1. Connects to queue
2. Pulls next job
3. Executes job
4. Repeats

### Worker Options

**Process specific queue:**
```bash
php artisan queue:work --queue=high,default,low
```

**Process specific connection:**
```bash
php artisan queue:work redis
```

**Limit attempts:**
```bash
php artisan queue:work --tries=3
```

**Timeout:**
```bash
php artisan queue:work --timeout=30
```

**Memory limit:**
```bash
php artisan queue:work --memory=128
```

**Stop after one job:**
```bash
php artisan queue:work --once
```

**Stop when queue empty:**
```bash
php artisan queue:work --stop-when-empty
```

### Restarting Workers

**After code changes, restart workers:**
```bash
php artisan queue:restart
```

This gracefully stops workers after current job. Start them again with:
```bash
php artisan queue:work
```

---

## Queue Priority

### Multiple Queues

Define job queue:
```php
class SendWelcomeEmail implements ShouldQueue
{
    public $queue = 'emails';
}
```

Or when dispatching:
```php
SendWelcomeEmail::dispatch($user)->onQueue('high');
```

### Process Queues by Priority

```bash
php artisan queue:work --queue=high,default,low
```

Worker processes:
1. All jobs in `high` queue
2. Then `default` queue
3. Then `low` queue

**Example setup:**
- `critical`: Payment processing, order confirmations
- `high`: User emails, notifications
- `default`: General tasks
- `low`: Cleanup, analytics

---

## Failed Jobs

### Automatic Retries

**Set max attempts in job:**
```php
class ProcessPodcast implements ShouldQueue
{
    public $tries = 3; // Retry up to 3 times
    public $timeout = 120; // Timeout after 2 minutes
}
```

**Or when dispatching:**
```php
ProcessPodcast::dispatch($podcast)->tries(5);
```

### Exponential Backoff

Wait longer between retries:
```php
public $backoff = [10, 30, 60]; // Wait 10s, 30s, 60s between retries
```

Or:
```php
public function backoff()
{
    return [10, 30, 60, 120];
}
```

### Handling Failed Jobs

**Define failed method:**
```php
class ProcessPodcast implements ShouldQueue
{
    public function handle(): void
    {
        // Process podcast
    }

    public function failed(Throwable $exception): void
    {
        // Send notification
        Log::error('Podcast processing failed', [
            'podcast_id' => $this->podcast->id,
            'error' => $exception->getMessage(),
        ]);

        // Notify admin
        Mail::to('admin@example.com')->send(
            new JobFailedNotification($this->podcast, $exception)
        );
    }
}
```

### Viewing Failed Jobs

```bash
# List failed jobs
php artisan queue:failed

# Retry a failed job
php artisan queue:retry {id}

# Retry all failed jobs
php artisan queue:retry all

# Delete a failed job
php artisan queue:forget {id}

# Delete all failed jobs
php artisan queue:flush
```

### Failed Job Event

Listen for failed jobs globally:

```php
// AppServiceProvider.php
use Illuminate\Support\Facades\Queue;
use Illuminate\Queue\Events\JobFailed;

public function boot()
{
    Queue::failing(function (JobFailed $event) {
        // $event->connectionName
        // $event->job
        // $event->exception

        // Notify admin, log to service, etc.
    });
}
```

---

## Real-World Examples

### Example 1: Welcome Email Flow

```php
// RegisterController.php
public function register(Request $request)
{
    $user = User::create($request->validated());

    // Queue welcome email - instant response
    SendWelcomeEmail::dispatch($user);

    return redirect()->route('dashboard')
        ->with('success', 'Welcome! Check your email.');
}

// app/Jobs/SendWelcomeEmail.php
class SendWelcomeEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 30;

    public function __construct(public User $user) {}

    public function handle(): void
    {
        Mail::to($this->user->email)->send(new WelcomeEmail($this->user));
    }

    public function failed(Throwable $exception): void
    {
        Log::error('Failed to send welcome email', [
            'user_id' => $this->user->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
```

### Example 2: Image Processing

```php
// ProfileController.php
public function updateAvatar(Request $request)
{
    $path = $request->file('avatar')->store('avatars');

    auth()->user()->update(['avatar' => $path]);

    // Queue image processing
    ProcessAvatar::dispatch(auth()->user(), $path);

    return back()->with('success', 'Avatar uploaded! Processing...');
}

// app/Jobs/ProcessAvatar.php
class ProcessAvatar implements ShouldQueue
{
    public $tries = 3;
    public $timeout = 120;

    public function __construct(
        public User $user,
        public string $path
    ) {}

    public function handle(): void
    {
        $image = Image::make(Storage::path($this->path));

        // Create thumbnails
        $thumbnail = $image->fit(150, 150);
        $thumbnail->save(Storage::path('avatars/thumb_' . basename($this->path)));

        // Create medium size
        $medium = $image->fit(500, 500);
        $medium->save(Storage::path('avatars/medium_' . basename($this->path)));

        // Update user
        $this->user->update(['avatar_processed' => true]);
    }
}
```

### Example 3: Report Generation

```php
// ReportController.php
public function generate(Request $request)
{
    $report = Report::create([
        'user_id' => auth()->id(),
        'type' => $request->type,
        'status' => 'pending',
    ]);

    GenerateReport::dispatch($report);

    return back()->with('success', 'Report queued! We\'ll email you when ready.');
}

// app/Jobs/GenerateReport.php
class GenerateReport implements ShouldQueue
{
    public $tries = 2;
    public $timeout = 300; // 5 minutes

    public function __construct(public Report $report) {}

    public function handle(): void
    {
        $this->report->update(['status' => 'processing']);

        // Generate report (expensive operation)
        $data = $this->generateReportData();

        $pdf = PDF::loadView('reports.template', compact('data'));
        $path = $pdf->save(storage_path('reports/report_' . $this->report->id . '.pdf'));

        $this->report->update([
            'status' => 'completed',
            'file_path' => $path,
        ]);

        // Notify user
        Mail::to($this->report->user)->send(new ReportReadyEmail($this->report));
    }

    protected function generateReportData()
    {
        // Complex calculations...
        return [
            'total_revenue' => Order::sum('total'),
            'total_orders' => Order::count(),
            // ... more data
        ];
    }

    public function failed(Throwable $exception): void
    {
        $this->report->update(['status' => 'failed']);

        Mail::to($this->report->user)->send(new ReportFailedEmail($this->report));
    }
}
```

### Example 4: Batch Import

```php
// ImportController.php
public function import(Request $request)
{
    $path = $request->file('csv')->store('imports');

    $rows = array_map('str_getcsv', file(Storage::path($path)));

    $jobs = collect($rows)->map(function ($row) {
        return new ImportRow($row);
    });

    $batch = Bus::batch($jobs)
        ->then(function (Batch $batch) {
            // All imported
            Mail::to(auth()->user())->send(new ImportCompleted($batch));
        })
        ->catch(function (Batch $batch, Throwable $e) {
            // Some imports failed
            Mail::to(auth()->user())->send(new ImportFailed($batch, $e));
        })
        ->dispatch();

    return back()->with('success', 'Import started!');
}

// app/Jobs/ImportRow.php
class ImportRow implements ShouldQueue
{
    public $tries = 2;

    public function __construct(public array $row) {}

    public function handle(): void
    {
        User::create([
            'name' => $this->row[0],
            'email' => $this->row[1],
            'password' => Hash::make('password'),
        ]);
    }
}
```

---

## Monitoring Queues

### Laravel Horizon (Redis only)

Beautiful dashboard for monitoring Redis queues:

```bash
composer require laravel/horizon
php artisan horizon:install
```

**Features:**
- Real-time queue monitoring
- Job throughput metrics
- Failed job management
- Job priority visualization

**Start Horizon:**
```bash
php artisan horizon
```

Access at: `http://your-app.test/horizon`

### Queue Metrics

**Get queue size:**
```php
use Illuminate\Support\Facades\Redis;

$size = Redis::connection('default')->llen('queues:default');
```

**Monitor in code:**
```php
use Illuminate\Support\Facades\Queue;

Queue::before(function (JobProcessing $event) {
    // Job starting
    Log::info('Job starting', ['job' => $event->job->resolveName()]);
});

Queue::after(function (JobProcessed $event) {
    // Job finished
    Log::info('Job finished', ['job' => $event->job->resolveName()]);
});
```

---

## Production Setup

### Supervisor

Keep queue workers running automatically:

**Install Supervisor (on server):**
```bash
sudo apt-get install supervisor
```

**Create config file:** `/etc/supervisor/conf.d/laravel-worker.conf`
```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/your/app/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=8
redirect_stderr=true
stdout_logfile=/path/to/your/app/storage/logs/worker.log
stopwaitsecs=3600
```

**Start Supervisor:**
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-worker:*
```

### Deployment Script

```bash
#!/bin/bash

# Deploy application
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Restart queue workers
php artisan queue:restart

# Or restart supervisor
sudo supervisorctl restart laravel-worker:*
```

---

## Best Practices

### 1. Keep Jobs Small and Focused

```php
// ❌ Bad: Does too much
class ProcessOrder implements ShouldQueue
{
    public function handle()
    {
        $this->validateStock();
        $this->chargePayment();
        $this->sendConfirmation();
        $this->updateInventory();
        $this->notifyWarehouse();
    }
}

// ✅ Good: Separate jobs
ProcessPayment::dispatch($order);
SendOrderConfirmation::dispatch($order);
UpdateInventory::dispatch($order);
NotifyWarehouse::dispatch($order);
```

### 2. Make Jobs Idempotent

Jobs should be safe to run multiple times:

```php
// ❌ Bad: Not idempotent
public function handle()
{
    $this->user->increment('credits', 10); // Runs twice = 20 credits!
}

// ✅ Good: Idempotent
public function handle()
{
    if ($this->user->credits === $this->originalCredits) {
        $this->user->increment('credits', 10);
    }
}
```

### 3. Use Job Middleware

```php
use Illuminate\Queue\Middleware\RateLimited;

class SendEmail implements ShouldQueue
{
    public function middleware()
    {
        return [new RateLimited('emails')];
    }
}

// AppServiceProvider.php
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

RateLimiter::for('emails', function ($job) {
    return Limit::perMinute(30); // Max 30 emails per minute
});
```

### 4. Monitor Performance

```php
use Illuminate\Support\Facades\Log;

public function handle()
{
    $start = microtime(true);

    // Do work

    $duration = microtime(true) - $start;

    Log::info('Job completed', [
        'job' => static::class,
        'duration' => $duration,
    ]);
}
```

---

## Quick Reference

```bash
# Commands
php artisan queue:work                 # Start worker
php artisan queue:work --queue=high    # Process specific queue
php artisan queue:restart              # Restart workers
php artisan queue:failed               # List failed jobs
php artisan queue:retry {id}           # Retry failed job
php artisan queue:retry all            # Retry all failed
php artisan queue:flush                # Delete all failed

# Dispatch
Job::dispatch($data);                  # Dispatch to queue
Job::dispatchSync($data);              # Run immediately
Job::dispatchAfterResponse($data);     # After HTTP response
Job::dispatch($data)->delay(60);       # Delay 60 seconds
Job::dispatch($data)->onQueue('high'); # Specific queue
```

---

## Practice Questions

1. **Why use queue jobs?** What types of tasks are good candidates?

2. **What's the difference between `dispatch()` and `dispatchSync()`?**

3. **How do you set up retries for failed jobs?**

4. **What is Supervisor?** Why do you need it in production?

5. **How do you prioritize different types of jobs?**

6. **What makes a job idempotent?** Why does it matter?

---

## Next Steps

Now you understand background processing! Next, you'll learn about asset optimization for faster page loads.

**Coming up in Lesson 05:**
- CSS and JavaScript optimization
- Image optimization
- Laravel Mix/Vite
- CDN integration
- Browser caching
