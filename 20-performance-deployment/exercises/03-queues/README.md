# Exercise 20.3 - Queue Jobs

## Objective

Learn to use queues for asynchronous task processing to improve application responsiveness.

---

## Task

Implement queue jobs for long-running or resource-intensive tasks.

### Requirements

1. **Create Jobs**
   - Create job classes for long-running tasks
   - Implement job logic
   - Handle job failures

2. **Queue Configuration**
   - Set up queue driver (database, Redis, etc.)
   - Configure queue connections
   - Set retry policies

3. **Dispatch Jobs**
   - Dispatch jobs from controllers/services
   - Dispatch with delay
   - Dispatch in batches

4. **Process Queue**
   - Run queue worker
   - Monitor job progress
   - Handle failed jobs

5. **Job Events**
   - Listen to job events
   - Log job progress
   - Send notifications on completion

---

## Setup

```bash
# Create job class
php artisan make:job ProcessItemCreate

# Create database queue table
php artisan queue:table
php artisan migrate

# Or use Redis (recommended for production)
# Update .env
QUEUE_CONNECTION=redis
```

---

## Creating Jobs

### Simple Job

```php
// app/Jobs/ProcessItemCreate.php
namespace App\Jobs;

use App\Models\Item;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessItemCreate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private Item $item;

    public function __construct(Item $item)
    {
        $this->item = $item;
    }

    /**
     * Execute the job
     */
    public function handle(): void
    {
        // Long-running task
        $this->item->update(['status' => 'processing']);

        // Generate thumbnail
        $this->generateThumbnail($this->item);

        // Send notification
        $this->sendNotification($this->item);

        // Update index
        $this->updateSearchIndex($this->item);

        $this->item->update(['status' => 'completed']);
    }

    private function generateThumbnail(Item $item): void
    {
        // Generate thumbnail logic
        sleep(2);
    }

    private function sendNotification(Item $item): void
    {
        // Send email/notification
    }

    private function updateSearchIndex(Item $item): void
    {
        // Update search index (Elasticsearch, etc.)
    }
}

// If job should be queued only under certain conditions
public function shouldQueue(): bool
{
    return config('queue.enabled', true);
}

// Set priority
public function middleware(): array
{
    return [new WithoutOverlapping($this->item->id)];
}
```

### Job with Retry

```php
// app/Jobs/SendEmailNotification.php
namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendEmailNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // Retry 5 times
    public int $tries = 5;

    // Wait 10 seconds between retries, 60 seconds, 5 minutes, 10 minutes, 30 minutes
    public array $backoff = [10, 60, 300, 600, 1800];

    // Timeout after 30 seconds
    public int $timeout = 30;

    private User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function handle(): void
    {
        try {
            // Send email
            Mail::to($this->user->email)->send(new WelcomeEmail($this->user));
        } catch (\Exception $e) {
            // Log error
            Log::error("Email failed for user {$this->user->id}: " . $e->getMessage());

            // Re-throw to trigger retry
            throw $e;
        }
    }

    // Called when job is about to be retried
    public function retrying(): void
    {
        Log::warning("Retrying email job for user {$this->user->id}");
    }

    // Called when job fails after all retries
    public function failed(\Throwable $exception): void
    {
        Log::error("Email job failed for user {$this->user->id}: " . $exception->getMessage());

        // Notify admin
        Notification::send(admin_users(), new JobFailedNotification(
            'Email sending failed',
            $exception->getMessage()
        ));
    }
}
```

---

## Dispatching Jobs

### From Controller

```php
// app/Http/Controllers/ItemController.php
namespace App\Http\Controllers;

use App\Jobs\ProcessItemCreate;
use App\Jobs\SendEmailNotification;

class ItemController extends Controller
{
    public function store(StoreItemRequest $request)
    {
        $item = Item::create($request->validated());

        // Dispatch job immediately
        ProcessItemCreate::dispatch($item);

        // Dispatch with delay (5 minutes)
        ProcessItemCreate::dispatch($item)->delay(now()->addMinutes(5));

        // Dispatch on specific queue
        ProcessItemCreate::dispatch($item)->onQueue('high');

        // Send email notification
        SendEmailNotification::dispatch(auth()->user())->onQueue('emails');

        return new ItemResource($item);
    }
}
```

### From Service

```php
// app/Services/OrderService.php
namespace App\Services;

use App\Jobs\ProcessPayment;
use App\Jobs\SendOrderConfirmation;

class OrderService
{
    public function createOrder(array $data)
    {
        $order = Order::create($data);

        // Dispatch jobs
        ProcessPayment::dispatch($order)->onQueue('payments');
        SendOrderConfirmation::dispatch($order)->onQueue('emails');

        return $order;
    }
}
```

### Batch Processing

```php
// Process multiple items as batch
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;

$items = Item::factory(100)->create();

$batch = Bus::batch([
    ...$items->map(fn($item) => new ProcessItemCreate($item)),
])->dispatch();

// Monitor batch
echo $batch->id;
echo $batch->processedCount();
echo $batch->percentComplete();
```

---

## Queue Configuration

```php
// config/queue.php
'connections' => [
    'database' => [
        'driver' => 'database',
        'connection' => 'mysql',
        'table' => 'jobs',
        'queue' => 'default',
        'retry_after' => 90,
        'after_commit' => false,
    ],

    'redis' => [
        'driver' => 'redis',
        'connection' => 'default',
        'queue' => '{default}',
        'retry_after' => 90,
        'block_for' => null,
        'after_commit' => false,
    ],

    'failed' => [
        'driver' => env('QUEUE_FAILED_DRIVER', 'database'),
        'database' => env('DB_CONNECTION', 'mysql'),
        'table' => 'failed_jobs',
    ],
],

'failed' => [
    'driver' => env('QUEUE_FAILED_DRIVER', 'database'),
],
```

---

## Running Queue Worker

```bash
# Start queue worker
php artisan queue:work

# Start with specific connection/queue
php artisan queue:work redis --queue=default,high

# Process one job and exit
php artisan queue:work --once

# With max tries
php artisan queue:work --tries=3

# With timeout
php artisan queue:work --timeout=60

# In background
nohup php artisan queue:work > storage/logs/queue.log 2>&1 &

# Using supervisor (production)
# See supervisor configuration below
```

---

## Failed Jobs Handling

```bash
# List failed jobs
php artisan queue:failed

# Retry failed job
php artisan queue:retry all

# Retry specific job
php artisan queue:retry 5

# Delete failed job
php artisan queue:forget 5
```

---

## Supervisor Configuration (Production)

```ini
; /etc/supervisor/conf.d/laravel-worker.conf
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/app/artisan queue:work redis --sleep=3 --tries=3 --timeout=90
autostart=true
autorestart=true
numprocs=4
redirect_stderr=true
stdout_logfile=/path/to/app/storage/logs/worker.log
stopwaitsecs=60
```

---

## Testing Jobs

```php
// tests/Feature/JobsTest.php
namespace Tests\Feature;

use Tests\TestCase;
use App\Jobs\ProcessItemCreate;
use App\Models\Item;
use Illuminate\Support\Facades\Queue;
use Illuminate\Foundation\Testing\RefreshDatabase;

class JobsTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_is_dispatched()
    {
        Queue::fake();

        $item = Item::factory()->create();
        ProcessItemCreate::dispatch($item);

        Queue::assertPushed(ProcessItemCreate::class);
    }

    public function test_job_is_dispatched_to_correct_queue()
    {
        Queue::fake();

        $item = Item::factory()->create();
        ProcessItemCreate::dispatch($item)->onQueue('processing');

        Queue::assertPushedOn('processing', ProcessItemCreate::class);
    }

    public function test_job_executes_successfully()
    {
        $item = Item::factory()->create();

        $job = new ProcessItemCreate($item);
        $job->handle();

        $this->assertEquals('completed', $item->fresh()->status);
    }

    public function test_job_retries_on_failure()
    {
        Queue::fake();

        $user = User::factory()->create();
        SendEmailNotification::dispatch($user);

        Queue::assertPushed(SendEmailNotification::class);
    }
}
```

---

## Checklist

- [ ] Job class created
- [ ] Queue connection configured
- [ ] Jobs dispatched correctly
- [ ] Queue worker running
- [ ] Failure handling implemented
- [ ] Tests pass
- [ ] Monitoring in place
- [ ] Documentation written

---

## Performance Benefits

- Faster response times (don't wait for jobs)
- Better user experience
- Ability to process heavy tasks
- Retry mechanisms for reliability
- Scalability with multiple workers

---

## Bonus Challenges

1. Create custom job middleware
2. Implement job events and listeners
3. Create queue monitoring dashboard
4. Implement job batching with progress tracking
5. Set up rate limiting for jobs
6. Create health check for queue worker
7. Implement dead letter queue for failed jobs

---

## Solution Check

Test implementation:
```bash
# Start queue worker
php artisan queue:work --once

# Run tests
php artisan test tests/Feature/JobsTest.php

# Check failed jobs
php artisan queue:failed
```
