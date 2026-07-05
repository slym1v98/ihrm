# Phase 3 Training Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build Training module with courses, sessions, enrollments, attendance, results, certificates, permissions, and tests.

**Architecture:** DDD 3-layer module under `app/Modules/Training/`, matching Onboarding/Offboarding/Performance conventions. Pure PHP domain, command/query application layer, Eloquent persistence, Laravel HTTP controllers/routes, module seeder.

**Tech Stack:** Laravel, PHP 8.3, Eloquent, Sanctum auth, existing `permission:<code>` middleware, PHPUnit/Pest via `php artisan test`.

---

## File Map

```
Create: 4 migrations
Domain: 3 enums, 4 ID classes, 4 aggregates, 6 events, 6 exceptions, 4 repository interfaces
Application: 10 commands, 10 handlers, 4 queries, 4 query handlers
Infrastructure: 4 Eloquent models, 4 repos, 4 controllers, seeder, routes
Modify: src/backend/routes/api.php, AppServiceProvider.php, DatabaseSeeder.php
Tests: Unit/Modules/Training/, Feature/Modules/Training/
```

## Task 0: Worktree + Baseline

**Files:** none

- [ ] Ensure `main` includes PR #14.

Run:
```bash
git checkout main
git pull origin main
git log --oneline -3
```
Expected: latest log includes `Merge pull request #14`.

- [ ] Create isolated branch/worktree.

Run:
```bash
git checkout -b feature/training
```
Expected: branch `feature/training`.

## Task 1: RED Domain Tests

**Files:**
- Create: `src/backend/tests/Unit/Modules/Training/TrainingSessionTest.php`
- Create: `src/backend/tests/Unit/Modules/Training/TrainingEnrollmentTest.php`

- [ ] Add failing session capacity/lifecycle tests.

```php
<?php

namespace Tests\Unit\Modules\Training;

use PHPUnit\Framework\TestCase;
use App\Modules\Training\Domain\Aggregates\TrainingSession\TrainingSession;
use App\Modules\Training\Domain\Aggregates\TrainingSession\TrainingSessionId;
use App\Modules\Training\Domain\ValueObjects\SessionStatus;
use App\Modules\Training\Domain\Exceptions\SessionFullException;

class TrainingSessionTest extends TestCase
{
    public function test_session_lifecycle(): void
    {
        $s = TrainingSession::schedule(TrainingSessionId::generate(), 'course-1', 'S1', 'Session 1', new \DateTimeImmutable('2026-08-01 09:00'), new \DateTimeImmutable('2026-08-01 17:00'), 'Room A', 'Trainer', 2);
        $this->assertSame(SessionStatus::Scheduled, $s->getStatus());
        $s->start();
        $this->assertSame(SessionStatus::Active, $s->getStatus());
        $s->complete();
        $this->assertSame(SessionStatus::Completed, $s->getStatus());
    }

    public function test_capacity_guard_rejects_extra_enrollment(): void
    {
        $s = TrainingSession::schedule(TrainingSessionId::generate(), 'course-1', 'S1', 'Session 1', new \DateTimeImmutable('2026-08-01 09:00'), new \DateTimeImmutable('2026-08-01 17:00'), null, null, 1);
        $s->assertCanEnroll(0);
        $this->expectException(SessionFullException::class);
        $s->assertCanEnroll(1);
    }
}
```

- [ ] Add failing enrollment attendance/result-adjacent tests.

```php
<?php

namespace Tests\Unit\Modules\Training;

use PHPUnit\Framework\TestCase;
use App\Modules\Training\Domain\Aggregates\TrainingEnrollment\TrainingEnrollment;
use App\Modules\Training\Domain\Aggregates\TrainingEnrollment\TrainingEnrollmentId;
use App\Modules\Training\Domain\ValueObjects\EnrollmentStatus;
use App\Modules\Training\Domain\Exceptions\InvalidEnrollmentStatusException;

class TrainingEnrollmentTest extends TestCase
{
    public function test_record_attendance_and_complete(): void
    {
        $e = TrainingEnrollment::enroll(TrainingEnrollmentId::generate(), 'session-1', 'employee-1', new \DateTimeImmutable('2026-08-01 08:00'));
        $e->recordAttendance(['present' => true, 'checked_in_at' => '2026-08-01 09:00:00']);
        $this->assertTrue($e->getAttendance()['present']);
        $e->complete();
        $this->assertSame(EnrollmentStatus::Completed, $e->getStatus());
    }

    public function test_cancelled_enrollment_cannot_record_attendance(): void
    {
        $e = TrainingEnrollment::enroll(TrainingEnrollmentId::generate(), 'session-1', 'employee-1', new \DateTimeImmutable('2026-08-01 08:00'));
        $e->cancel();
        $this->expectException(InvalidEnrollmentStatusException::class);
        $e->recordAttendance(['present' => true]);
    }
}
```

- [ ] Verify RED.

Run:
```bash
docker compose run --rm app php artisan test tests/Unit/Modules/Training --compact
```
Expected: FAIL because Training classes do not exist.

## Task 2: Migrations

**Files:**
- Create: `src/backend/database/migrations/2026_07_03_170001_create_training_courses_table.php`
- Create: `src/backend/database/migrations/2026_07_03_170002_create_training_sessions_table.php`
- Create: `src/backend/database/migrations/2026_07_03_170003_create_training_enrollments_table.php`
- Create: `src/backend/database/migrations/2026_07_03_170004_create_training_results_table.php`

- [ ] Create 4 migrations.

```php
// courses: id uuid pk, code unique, name, description nullable, category nullable,
// default_duration_hours nullable int, max_participants nullable int, active bool default true, timestamps
```

```php
// sessions: id uuid pk, course_id fk cascade, code, name, start_date datetime, end_date datetime,
// location nullable, instructor nullable, max_participants nullable int, status default scheduled, timestamps
```

```php
// enrollments: id uuid pk, session_id fk cascade, employee_id uuid, enrolled_at datetime,
// attendance json nullable, status default enrolled, timestamps, unique(session_id, employee_id)
```

```php
// results: id uuid pk, enrollment_id fk cascade unique, score decimal(5,2) nullable,
// passed boolean nullable, certificate_code nullable, issued_at datetime nullable, notes text nullable, timestamps
```

- [ ] Verify migration syntax.

Run:
```bash
docker compose run --rm app php artisan migrate 2>&1 | tail -40
```
Expected: migrations complete; Training seeder not added yet is fine.

## Task 3: Domain VOs, IDs, Events, Exceptions

**Files:**
- Create: `src/backend/app/Modules/Training/Domain/ValueObjects/SessionStatus.php`
- Create: `src/backend/app/Modules/Training/Domain/ValueObjects/EnrollmentStatus.php`
- Create: `src/backend/app/Modules/Training/Domain/Aggregates/*/*Id.php`
- Create: `src/backend/app/Modules/Training/Domain/Events/*.php`
- Create: `src/backend/app/Modules/Training/Domain/Exceptions/*.php`

- [ ] Add enums.

```php
<?php
namespace App\Modules\Training\Domain\ValueObjects;

enum SessionStatus: string
{
    case Scheduled = 'scheduled';
    case Active = 'active';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function canTransitionTo(self $target): bool
    {
        return match($this) {
            self::Scheduled => in_array($target, [self::Active, self::Cancelled], true),
            self::Active => in_array($target, [self::Completed, self::Cancelled], true),
            self::Completed, self::Cancelled => false,
        };
    }
}
```

```php
<?php
namespace App\Modules\Training\Domain\ValueObjects;

enum EnrollmentStatus: string
{
    case Enrolled = 'enrolled';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function canTransitionTo(self $target): bool
    {
        return match($this) {
            self::Enrolled => in_array($target, [self::Completed, self::Cancelled], true),
            self::Completed, self::Cancelled => false,
        };
    }
}
```

- [ ] Add ID classes with `Ramsey\Uuid\Uuid::uuid7()` matching Performance IDs.
- [ ] Add event classes: `SessionScheduled`, `EmployeeEnrolled`, `EnrollmentCancelled`, `AttendanceRecorded`, `ResultRecorded`, `TrainingCompleted`.
- [ ] Add exception classes: `TrainingCourseNotFoundException`, `TrainingSessionNotFoundException`, `TrainingEnrollmentNotFoundException`, `TrainingResultNotFoundException`, `SessionFullException`, `InvalidEnrollmentStatusException`.

## Task 4: Domain Aggregates

**Files:**
- Create: `src/backend/app/Modules/Training/Domain/Aggregates/TrainingCourse/TrainingCourse.php`
- Create: `src/backend/app/Modules/Training/Domain/Aggregates/TrainingSession/TrainingSession.php`
- Create: `src/backend/app/Modules/Training/Domain/Aggregates/TrainingEnrollment/TrainingEnrollment.php`
- Create: `src/backend/app/Modules/Training/Domain/Aggregates/TrainingResult/TrainingResult.php`

- [ ] Implement `TrainingSession::schedule()`, `start()`, `complete()`, `cancel()`, `assertCanEnroll(int $currentEnrollmentCount)`.
- [ ] Implement `TrainingEnrollment::enroll()`, `recordAttendance(array $attendance)`, `complete()`, `cancel()`.
- [ ] Implement `TrainingCourse::create()`, `update()`, `deactivate()`.
- [ ] Implement `TrainingResult::record()` with optional `certificateCode` and `issuedAt`.
- [ ] Run RED tests again.

Run:
```bash
docker compose run --rm app php artisan test tests/Unit/Modules/Training --compact
```
Expected: PASS.

- [ ] Commit.

```bash
git add src/backend/app/Modules/Training/Domain src/backend/tests/Unit/Modules/Training src/backend/database/migrations/2026_07_03_17000*.php
git commit -m "feat(training): add domain model and schema"
```

## Task 5: Repositories + Models + Bindings

**Files:**
- Create: `src/backend/app/Modules/Training/Domain/Repositories/*.php`
- Create: `src/backend/app/Modules/Training/Infrastructure/Persistence/Eloquent/*.php`
- Create: `src/backend/app/Modules/Training/Infrastructure/Persistence/Repositories/*.php`
- Modify: `src/backend/app/Providers/AppServiceProvider.php`

- [ ] Add 4 repository interfaces: course/session/enrollment/result.
- [ ] Add 4 Eloquent models using `HasUuids`, `$fillable`, casts (`attendance` array, dates, booleans, decimal score).
- [ ] Add 4 repository implementations mapping Eloquent rows to domain with `reconstitute()` methods.
- [ ] Add AppServiceProvider bindings.

```php
$this->app->bind(\App\Modules\Training\Domain\Repositories\TrainingCourseRepositoryInterface::class, \App\Modules\Training\Infrastructure\Persistence\Repositories\EloquentTrainingCourseRepository::class);
$this->app->bind(\App\Modules\Training\Domain\Repositories\TrainingSessionRepositoryInterface::class, \App\Modules\Training\Infrastructure\Persistence\Repositories\EloquentTrainingSessionRepository::class);
$this->app->bind(\App\Modules\Training\Domain\Repositories\TrainingEnrollmentRepositoryInterface::class, \App\Modules\Training\Infrastructure\Persistence\Repositories\EloquentTrainingEnrollmentRepository::class);
$this->app->bind(\App\Modules\Training\Domain\Repositories\TrainingResultRepositoryInterface::class, \App\Modules\Training\Infrastructure\Persistence\Repositories\EloquentTrainingResultRepository::class);
```

## Task 6: Application Layer

**Files:**
- Create: `src/backend/app/Modules/Training/Application/Commands/*.php`
- Create: `src/backend/app/Modules/Training/Application/CommandHandlers/*.php`
- Create: `src/backend/app/Modules/Training/Application/Queries/*.php`
- Create: `src/backend/app/Modules/Training/Application/QueryHandlers/*.php`

- [ ] Add commands: `CreateCourse`, `UpdateCourse`, `DeactivateCourse`, `CreateSession`, `UpdateSession`, `EnrollEmployee`, `CancelEnrollment`, `RecordAttendance`, `RecordResult`, `CompleteEnrollment`.
- [ ] Add handlers. `EnrollEmployeeHandler` MUST count active enrollments for session and call `$session->assertCanEnroll($count)` before creating enrollment.
- [ ] Add queries: `ListCoursesQuery`, `ListSessionsQuery`, `ListEnrollmentsQuery`, `ListResultsQuery`.
- [ ] Add query handlers using repositories.

## Task 7: HTTP Routes + Controllers

**Files:**
- Create: `src/backend/app/Modules/Training/Infrastructure/Http/Controllers/TrainingCourseController.php`
- Create: `src/backend/app/Modules/Training/Infrastructure/Http/Controllers/TrainingSessionController.php`
- Create: `src/backend/app/Modules/Training/Infrastructure/Http/Controllers/TrainingEnrollmentController.php`
- Create: `src/backend/app/Modules/Training/Infrastructure/Http/Controllers/TrainingResultController.php`
- Create: `src/backend/app/Modules/Training/Routes/api.php`
- Modify: `src/backend/routes/api.php`

- [ ] Add controllers mirroring Performance controller style: inject handlers/repos, return JSON arrays, catch domain exceptions as 422 where lifecycle guard fails.
- [ ] Add routes under `Route::prefix('v1/training')->middleware(['auth:sanctum'])`.

```php
Route::get('courses', [TrainingCourseController::class, 'index'])->middleware('permission:training.course.view');
Route::post('courses', [TrainingCourseController::class, 'store'])->middleware('permission:training.course.create');
Route::get('courses/{id}', [TrainingCourseController::class, 'show'])->middleware('permission:training.course.view');
Route::put('courses/{id}', [TrainingCourseController::class, 'update'])->middleware('permission:training.course.update');
Route::delete('courses/{id}', [TrainingCourseController::class, 'destroy'])->middleware('permission:training.course.delete');
Route::get('courses/{courseId}/sessions', [TrainingSessionController::class, 'index'])->middleware('permission:training.session.view');
Route::post('courses/{courseId}/sessions', [TrainingSessionController::class, 'store'])->middleware('permission:training.session.create');
Route::get('sessions/{id}', [TrainingSessionController::class, 'show'])->middleware('permission:training.session.view');
Route::put('sessions/{id}', [TrainingSessionController::class, 'update'])->middleware('permission:training.session.update');
Route::post('sessions/{id}/enroll', [TrainingEnrollmentController::class, 'store'])->middleware('permission:training.enrollment.create');
Route::post('enrollments/{id}/cancel', [TrainingEnrollmentController::class, 'cancel'])->middleware('permission:training.enrollment.cancel');
Route::post('enrollments/{id}/attendance', [TrainingEnrollmentController::class, 'attendance'])->middleware('permission:training.enrollment.create');
Route::post('enrollments/{id}/complete', [TrainingEnrollmentController::class, 'complete'])->middleware('permission:training.enrollment.create');
Route::post('enrollments/{id}/result', [TrainingResultController::class, 'store'])->middleware('permission:training.result.create');
Route::get('results/{id}', [TrainingResultController::class, 'show'])->middleware('permission:training.result.view');
```

- [ ] Register route loader.

```php
require __DIR__ . '/../app/Modules/Training/Routes/api.php';
```

## Task 8: Permissions Seeder

**Files:**
- Create: `src/backend/app/Modules/Training/Infrastructure/Seeders/TrainingPermissionSeeder.php`
- Modify: `src/backend/database/seeders/DatabaseSeeder.php`

- [ ] Add permission codes: `training.course.{view,create,update,delete}`, `training.session.{view,create,update}`, `training.enrollment.{view,create,cancel}`, `training.result.{view,create}`.
- [ ] Follow Onboarding/Performance seeder pattern: `PermissionModel::firstOrCreate(['code'=>$code], [...])`, then grant all codes to `SUPER_ADMIN` via `RolePermissionModel`.
- [ ] Register seeder in `DatabaseSeeder` after `PerformancePermissionSeeder`.

## Task 9: Feature Tests

**Files:**
- Create: `src/backend/tests/Feature/Modules/Training/TrainingApiTest.php`

- [ ] Add auth boundary test.

```php
public function test_auth_required(): void
{
    $this->getJson('/api/v1/training/courses')->assertStatus(401);
    $this->postJson('/api/v1/training/courses', [])->assertStatus(401);
}
```

- [ ] Add happy path test: create course -> create session -> enroll -> attendance -> complete -> result -> fetch result.
- [ ] Add capacity guard test: session max 1, second enroll returns 422.
- [ ] Add duplicate enrollment guard test: same session/employee second enroll returns 422.

Run:
```bash
docker compose run --rm app php artisan test tests/Unit/Modules/Training tests/Feature/Modules/Training --compact
```
Expected: all Training tests pass.

## Task 10: Full Verification + Review + PR

**Files:**
- Review: `docs/superpowers/specs/2026-07-03-phase3-training-design.md`

- [ ] Run full backend suite.

```bash
docker compose run --rm app php artisan test --compact
```
Expected: all tests pass; report count.

- [ ] Spec review checklist.
  - AC1 DDD layout: verify `src/backend/app/Modules/Training/{Domain,Application,Infrastructure,Routes}`.
  - AC2 4 aggregates: verify Course, Session, Enrollment, Result.
  - AC3 capacity guard: test covers second enroll 422.
  - AC4 duplicate guard: DB unique + test covers duplicate 422.
  - AC5 attendance JSONB: migration + API test verifies attendance record.
  - AC6 result/certificate: API test verifies `certificate_code`.
  - AC7 permissions/routes: seeder + route file.
  - AC8 tests: unit + feature tests exist.

- [ ] Commit final changes.

```bash
git add -A
git commit -m "feat(training): add Phase 3 Training module"
```

- [ ] Push branch and create PR (do not merge).

```bash
git push -u origin feature/training
gh pr create --base main --head feature/training --title "feat(training): Phase 3 Training module" --body "## Summary
- Add Training BC: courses, sessions, enrollments, attendance, results
- Add application handlers, Eloquent persistence, API routes/controllers
- Add permission seeder + tests

## Verification
- Full backend: <paste count>
"
```

---

## Self-Review

- Spec coverage: all scope items and AC1-8 mapped to tasks.
- Placeholder scan: no TBD/TODO/later placeholders.
- Type consistency: aggregate/repository/controller names align with Training module paths.
