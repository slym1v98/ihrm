# Backend Performance Improvement Plan

**Date:** 2026-07-05
**Context:** After quality improvements PR merged. Redis already configured but unused. Queue sync default.

---

## 1. Queue Workers

### 1.1 Configuration
- `.env`: `QUEUE_CONNECTION=redis`
- `docker-compose.yml`: Add queue worker service
- Production: supervisor config cho `php artisan queue:work redis --sleep=3 --tries=3`

### 1.2 Jobs
| Job | Current | After |
|-----|---------|-------|
| `ReportRunJob` | sync dispatch + może timeout | async, report run poll status |
| Notification batch send | inline in controller | `SendBulkNotificationJob` |
| Payroll calculation | lock request until done | `CalculatePayrollJob`, notify on complete |
| Attendance period close | inline | `CloseAttendancePeriodJob` |

### 1.3 Failure handling
- `failed_jobs` table (migration exists)
- Retry: 3 attempts with exponential backoff
- Notify admin on final failure

---

## 2. Response Caching

### 2.1 Middleware
Create `App\Http\Middleware\CacheResponse`:
```php
public function handle(Request $request, Closure $next, int $ttl = 300)
{
    if (! $request->isMethod('GET')) return $next($request);
    $key = 'response:' . $request->fullUrl();
    return Cache::remember($key, $ttl, fn() => $next($request));
}
```

### 2.2 Cached endpoints
| Endpoint | TTL | Notes |
|----------|-----|-------|
| `GET /api/v1/org-tree` | 600s | invalidation on org change |
| `GET /api/v1/branches` | 300s | infrequent changes |
| `GET /api/v1/positions` | 300s | |
| `GET /api/v1/lookups` | 600s | config data |
| `GET /api/v1/holidays` | 3600s | yearly data |
| `GET /api/v1/report-definitions` | 300s | |
| `GET /api/v1/report-runs` | 60s | run status poll |

### 2.3 Invalidation
- After POST/PUT/DELETE on related resource → `Cache::forget('response:...')`
- Tag-based on Redis ≥ 6.0: `Cache::tags(['org'])->flush()`

---

## 3. Database Optimization

### 3.1 Pagination review
Modules with `->get()` without pagination:
- Audit logs
- Notification outbox
- Workflow requests
- Training sessions/enrollments
- Asset items

→ Add `->paginate(int $request->per_page ?? 20)`

### 3.2 Eager loading
- Employee: contracts, documents
- Leave types: policies
- Workflow: template steps, actions
- Onboarding: tasks, templates

### 3.3 Missing indexes
Review columns frequent in WHERE/ORDER BY:
- `{table}.status`, `{table}.type`, `{table}.code`
- `{table}.{foreign_key_id}`
- `{table}.created_at`, `{table}.started_at`, `{table}.completed_at`
- Composite: `(status, created_at)`, `(employee_id, date)`

### 3.4 Column selection
Replace `Model::all()` / `Model::get()` với column list:
```php
Model::get(['id', 'name', 'status'])
```

---

## 4. Acceptance Criteria

- **AC1:** Queue worker chạy trong docker-compose, jobs process async
- **AC2:** Report generation và notification gửi background không block response
- **AC3:** GET endpoints configurable cache (middleware)
- **AC4:** Cache invalidate khi data thay đổi
- **AC5:** Tất cả list endpoints có pagination
- **AC6:** N+1 queries fixed (eager loading)
- **AC7:** Missing indexes added
- **AC8:** Full test suite pass + zero PHPStan + Pint pass
