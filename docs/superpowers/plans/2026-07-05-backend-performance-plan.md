# Backend Performance Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Improve backend performance through queue workers, response caching, and DB optimization.

**Architecture:** Add queue worker to docker-compose, create cache middleware, add pagination/eager-loading/indexes across modules. Tasks are sequenced so infrastructure comes first, then optimization passes.

**Tech Stack:** Laravel 12, Redis, PostgreSQL 16, Docker Compose

---

### Task 1: Queue Worker Setup

**Files:**
- Modify: `.worktrees/backend-performance/docker-compose.yml`
- Create: `docker/php/queue-entrypoint.sh`

- [ ] **Step 1: Read current docker-compose.yml**

```bash
cat docker-compose.yml
```

- [ ] **Step 2: Add queue worker service**

Add to `docker-compose.yml` after `app` service:

```yaml
  queue:
    build:
      context: .
      dockerfile: docker/php/Dockerfile
    working_dir: /app/src/backend
    command: php artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
    restart: unless-stopped
    depends_on:
      db:
        condition: service_healthy
      redis:
        condition: service_healthy
    volumes:
      - .:/app
```

- [ ] **Step 3: Verify `.env` has `QUEUE_CONNECTION=redis`**

```bash
grep QUEUE_CONNECTION .env
```

Expected: `QUEUE_CONNECTION=redis`

- [ ] **Step 4: Run tests to verify nothing broken**

```bash
docker compose run --rm app php artisan test --compact
```

- [ ] **Step 5: Commit**

```bash
git add docker-compose.yml
git commit -m "perf: add queue worker service to docker-compose"
```

---

### Task 2: Create CacheResponse Middleware

**Files:**
- Create: `app/Http/Middleware/CacheResponse.php`

- [ ] **Step 1: Create middleware**

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class CacheResponse
{
    public function handle(Request $request, Closure $next, int $ttl = 300): Response
    {
        if (! $request->isMethod('GET')) {
            return $next($request);
        }

        $key = 'response:'.md5($request->fullUrl());

        return Cache::remember($key, $ttl, fn () => $next($request));
    }
}
```

- [ ] **Step 2: Register middleware in bootstrap/app.php**

```php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->alias([
        'permission' => PermissionMiddleware::class,
        'cache' => \App\Http\Middleware\CacheResponse::class,
    ]);
});
```

- [ ] **Step 3: Apply cache middleware to routes**

In each module `Routes/api.php`, wrap stable list endpoints:

```php
Route::middleware(['auth:sanctum', 'cache:600'])->group(function () {
    Route::get('/branches', ListBranchController::class);
    Route::get('/positions', ListPositionController::class);
    // ...
});
```

Apply to modules: Organization (branches/positions/departments), Configuration (lookups/holidays), Shift (templates).

- [ ] **Step 4: Add cache invalidation**

In `SaveSomeAggregate` handlers, after DB save:

```php
use Illuminate\Support\Facades\Cache;

// After successful operation
Cache::forget('response:'.md5(route('api.branches.index')));
```

- [ ] **Step 5: Run tests**

```bash
docker compose run --rm app php artisan test --compact
```

- [ ] **Step 6: Commit**

```bash
git add app/Http/Middleware/CacheResponse.php bootstrap/app.php
git commit -m "perf: add CacheResponse middleware with route alias"
```

---

### Task 3: Create Queue Jobs

**Files:**
- Create: `app/Modules/Notification/Infrastructure/Jobs/SendBulkNotificationJob.php`
- Modify: `app/Modules/Payroll/Infrastructure/Http/Controllers/PayrollRunController.php`
- Modify: `app/Modules/Attendance/Routes/api.php`

- [ ] **Step 1: Create SendBulkNotificationJob**

```php
<?php

namespace App\Modules\Notification\Infrastructure\Jobs;

use App\Modules\Notification\Infrastructure\Persistence\Eloquent\NotificationOutboxModel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendBulkNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 120;

    public function __construct(
        private readonly array $notificationIds,
    ) {}

    public function handle(): void
    {
        $notifications = NotificationOutboxModel::whereIn('id', $this->notificationIds)
            ->where('status', 'pending')
            ->get();

        foreach ($notifications as $notification) {
            try {
                // Send via channel (email/sms/in_app)
                $notification->update(['status' => 'sent', 'sent_at' => now()]);
            } catch (\Throwable $e) {
                $notification->update(['status' => 'failed', 'error' => $e->getMessage()]);
            }
        }
    }
}
```

- [ ] **Step 2: Dispatch job from notification controller**

```php
use App\Modules\Notification\Infrastructure\Jobs\SendBulkNotificationJob;

// Instead of inline send loop:
SendBulkNotificationJob::dispatch($notificationIds);
return response()->json(['message' => 'Đang gửi thông báo'], 202);
```

- [ ] **Step 3: Dispatch payroll calculation as async**

```php
// PayrollRunController
use App\Modules\Payroll\Infrastructure\Jobs\CalculatePayrollJob;

CalculatePayrollJob::dispatch($runId);
return response()->json(['message' => 'Đang tính lương'], 202);
```

- [ ] **Step 4: Run tests**

```bash
docker compose run --rm app php artisan test --compact
```

- [ ] **Step 5: Commit**

```bash
git add app/Modules/Notification/Infrastructure/Jobs/ [other modified files]
git commit -m "perf: add queue jobs for notification and payroll"
```

---

### Task 4: Pagination Audit

**Files:**
- Modify: Modules with `->get()` without pagination (Audit, Notification, Workflow, Training, Asset)

- [ ] **Step 1: Find all unpaginated list endpoints**

```bash
grep -rn "->get()" app/Modules/*/Infrastructure/Http/Controllers/ | grep -v "->where\|->select\|with("
```

- [ ] **Step 2: Add pagination to each**

For each list endpoint:

```php
// Before:
$items = Model::where(...)->get();

// After:
$items = Model::where(...)->paginate((int) $request->input('per_page', 20));
```

- [ ] **Step 3: Run tests**

```bash
docker compose run --rm app php artisan test --compact
```

- [ ] **Step 4: Commit**

```bash
git add [modified controller files]
git commit -m "perf: add pagination to list endpoints"
```

---

### Task 5: N+1 Query Fixes

**Files:**
- Modify: Repositories/Controllers with N+1 patterns

- [ ] **Step 1: Find N+1 suspects**

```bash
grep -rn "foreach.*->" app/Modules/*/Infrastructure/ | grep "get()\|all()" | head -10
```

- [ ] **Step 2: Add eager loading where missing**

For each pattern, add `->with('relation')` before the query:

```php
// Before:
$models = Model::where(...)->get();
foreach ($models as $m) { $m->relation->name; }

// After:
$models = Model::with('relation')->where(...)->get();
```

- [ ] **Step 3: Run tests**

```bash
docker compose run --rm app php artisan test --compact
```

- [ ] **Step 4: Commit**

```bash
git add [modified files]
git commit -m "perf: add eager loading to fix N+1 queries"
```

---

### Task 6: Add Database Indexes

**Files:**
- Create: migration files

- [ ] **Step 1: Create migration**

```bash
php artisan make:migration add_performance_indexes
```

- [ ] **Step 2: Add indexes in migration**

```php
public function up(): void
{
    // Attendance
    Schema::table('attendance_raw_logs', function (Blueprint $table) {
        $table->index(['employee_id', 'date']);
    });
    Schema::table('attendance_adjustment_requests', function (Blueprint $table) {
        $table->index(['employee_id', 'status']);
    });

    // Leave
    Schema::table('leave_requests', function (Blueprint $table) {
        $table->index(['employee_id', 'status', 'start_date']);
    });

    // Notification
    Schema::table('notification_outbox', function (Blueprint $table) {
        $table->index(['user_id', 'status', 'created_at']);
    });

    // Workflow
    Schema::table('workflow_requests', function (Blueprint $table) {
        $table->index(['status', 'created_at']);
    });

    // Audit
    Schema::table('audit_logs', function (Blueprint $table) {
        $table->index(['auditable_type', 'auditable_id']);
        $table->index(['created_at']);
    });

    // Payroll
    Schema::table('payroll_entries', function (Blueprint $table) {
        $table->index(['payroll_run_id', 'employee_id']);
    });
}
```

- [ ] **Step 3: Run migration**

```bash
docker compose run --rm app php artisan migrate
```

Expected: Migrations run successfully.

- [ ] **Step 4: Run tests**

```bash
docker compose run --rm app php artisan test --compact
```

- [ ] **Step 5: Commit**

```bash
git add database/migrations/xxxx_add_performance_indexes.php
git commit -m "perf: add database indexes for common query patterns"
```

---

### Task 7: Full Verification

- [ ] **Step 1: Run complete test suite**

```bash
docker compose run --rm app php artisan test --compact
```

Expected: All passing.

- [ ] **Step 2: Run PHPStan**

```bash
docker compose run --rm app php vendor/bin/phpstan analyse -c phpstan.neon --memory-limit=2G
```

Expected: `[OK] No errors`

- [ ] **Step 3: Run Pint**

```bash
php vendor/bin/pint --test
```

Expected: `passed`

- [ ] **Step 4: Verify queue worker starts**

```bash
docker compose up -d queue
docker compose logs queue --tail=5
```

Expected: Queue worker running.
