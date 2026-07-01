# Phase 1 Audit Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build the Phase 1 Audit module with append-only audit persistence, Identity event capture, read-only search API, permission seeding, and tests.

**Architecture:** Use event-driven capture: Identity domain events already dispatch through repositories, so Audit registers a listener and normalizes events into `audit_logs`. Public API is read-only under `/api/v1/audit-logs`; audit writes use `AuditLogger` and are best-effort.

**Tech Stack:** Laravel 12, PHP 8.4, PostgreSQL JSONB, Sanctum auth, existing `permission:{code}` middleware, existing `PaginatedCollection` and `ErrorResource` conventions.

---

## File Structure

Create:

- `src/backend/database/migrations/2026_07_01_200001_create_audit_logs_table.php` — audit table.
- `src/backend/app/Modules/Audit/Application/Services/AuditLogger.php` — redaction + write service.
- `src/backend/app/Modules/Audit/Domain/Events/AuditLogged.php` — optional internal event after successful write.
- `src/backend/app/Modules/Audit/Infrastructure/Persistence/Eloquent/AuditLogModel.php` — Eloquent model.
- `src/backend/app/Modules/Audit/Infrastructure/Listeners/AuditEventListener.php` — maps Identity events to audit rows.
- `src/backend/app/Modules/Audit/Infrastructure/Http/Controllers/AuditLogController.php` — read-only index/search.
- `src/backend/app/Modules/Audit/Infrastructure/Http/Resources/AuditLogResource.php` — API response shape.
- `src/backend/app/Modules/Audit/Routes/api.php` — module route.
- `src/backend/tests/Unit/Modules/Audit/AuditLoggerTest.php` — logger and redaction tests.
- `src/backend/tests/Unit/Modules/Audit/AuditEventListenerTest.php` — event mapping tests.
- `src/backend/tests/Feature/Modules/Audit/AuditLogApiTest.php` — API permission/filter tests.
- `src/backend/tests/Feature/Modules/Audit/IdentityAuditIntegrationTest.php` — login/failed login/role-permission audit integration.

Modify:

- `src/backend/app/Providers/AppServiceProvider.php` — register event listener and Audit bindings if needed.
- `src/backend/routes/api.php` — require Audit routes.
- `src/backend/app/Modules/Identity/Infrastructure/Seeders/PermissionSeeder.php` — add `audit.log.list`.
- `src/backend/app/Modules/Identity/Application/Services/AuthenticationService.php` — dispatch `UserLoggedIn` and `UserLoginFailed` events for login flows because login currently updates Eloquent directly.

---

## Task 1: Audit table and model

**Files:**
- Create: `src/backend/database/migrations/2026_07_01_200001_create_audit_logs_table.php`
- Create: `src/backend/app/Modules/Audit/Infrastructure/Persistence/Eloquent/AuditLogModel.php`
- Test: `src/backend/tests/Unit/Modules/Audit/AuditLoggerTest.php`

- [ ] **Step 1: Create failing model smoke test**

Create `src/backend/tests/Unit/Modules/Audit/AuditLoggerTest.php`:

```php
<?php

namespace Tests\Unit\Modules\Audit;

use App\Modules\Audit\Infrastructure\Persistence\Eloquent\AuditLogModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLoggerTest extends TestCase
{
    use RefreshDatabase;

    public function test_audit_log_model_can_persist_row(): void
    {
        $log = AuditLogModel::create([
            'actor_user_id' => null,
            'action' => 'login_failed',
            'module' => 'identity',
            'entity_type' => 'user',
            'entity_id' => null,
            'before_payload' => null,
            'after_payload' => ['email' => 'missing@ihrm.local'],
            'ip_address' => '127.0.0.1',
            'user_agent' => 'phpunit',
            'result' => 'failure',
            'occurred_at' => now(),
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'id' => $log->id,
            'action' => 'login_failed',
            'module' => 'identity',
            'result' => 'failure',
        ]);
    }
}
```

- [ ] **Step 2: Run test to verify failure**

```bash
docker compose run --rm app php artisan test tests/Unit/Modules/Audit/AuditLoggerTest.php
```

Expected: FAIL because `AuditLogModel` or `audit_logs` table does not exist.

- [ ] **Step 3: Create migration**

Create `src/backend/database/migrations/2026_07_01_200001_create_audit_logs_table.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 100)->index();
            $table->string('module', 100);
            $table->string('entity_type', 100);
            $table->string('entity_id')->nullable();
            $table->jsonb('before_payload')->nullable();
            $table->jsonb('after_payload')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('result', 30)->index();
            $table->timestamp('occurred_at')->index();
            $table->timestamps();

            $table->index(['actor_user_id', 'occurred_at']);
            $table->index(['module', 'entity_type', 'entity_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
```

- [ ] **Step 4: Create model**

Create `src/backend/app/Modules/Audit/Infrastructure/Persistence/Eloquent/AuditLogModel.php`:

```php
<?php

namespace App\Modules\Audit\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class AuditLogModel extends Model
{
    use HasUuids;

    protected $table = 'audit_logs';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = [];

    protected $casts = [
        'before_payload' => 'array',
        'after_payload' => 'array',
        'occurred_at' => 'datetime',
    ];
}
```

- [ ] **Step 5: Run test to verify pass**

```bash
docker compose run --rm app php artisan test tests/Unit/Modules/Audit/AuditLoggerTest.php
```

Expected: PASS.

- [ ] **Step 6: Commit**

```bash
git add src/backend/database/migrations/2026_07_01_200001_create_audit_logs_table.php src/backend/app/Modules/Audit/Infrastructure/Persistence/Eloquent/AuditLogModel.php src/backend/tests/Unit/Modules/Audit/AuditLoggerTest.php
git commit -m "feat(audit): add audit log persistence"
```

---

## Task 2: AuditLogger service with redaction

**Files:**
- Create: `src/backend/app/Modules/Audit/Application/Services/AuditLogger.php`
- Create: `src/backend/app/Modules/Audit/Domain/Events/AuditLogged.php`
- Modify: `src/backend/tests/Unit/Modules/Audit/AuditLoggerTest.php`

- [ ] **Step 1: Extend failing tests**

Append these tests to `AuditLoggerTest`:

```php
use App\Modules\Audit\Application\Services\AuditLogger;

public function test_audit_logger_writes_row(): void
{
    app(AuditLogger::class)->log(
        action: 'login',
        module: 'identity',
        entityType: 'user',
        entityId: 'user-123',
        actorUserId: null,
        beforePayload: null,
        afterPayload: ['email' => 'admin@ihrm.local'],
        result: 'success',
        occurredAt: now(),
        ipAddress: '127.0.0.1',
        userAgent: 'phpunit',
    );

    $this->assertDatabaseHas('audit_logs', [
        'action' => 'login',
        'module' => 'identity',
        'entity_type' => 'user',
        'entity_id' => 'user-123',
        'result' => 'success',
    ]);
}

public function test_audit_logger_redacts_sensitive_nested_values(): void
{
    $log = app(AuditLogger::class)->log(
        action: 'updated',
        module: 'identity',
        entityType: 'user',
        entityId: 'user-123',
        actorUserId: null,
        beforePayload: ['password' => 'old', 'profile' => ['api_key' => 'secret', 'name' => 'Admin']],
        afterPayload: ['access_token' => 'token', 'profile' => ['name' => 'Admin 2']],
        result: 'success',
        occurredAt: now(),
    );

    $this->assertSame('[REDACTED]', $log->before_payload['password']);
    $this->assertSame('[REDACTED]', $log->before_payload['profile']['api_key']);
    $this->assertSame('Admin', $log->before_payload['profile']['name']);
    $this->assertSame('[REDACTED]', $log->after_payload['access_token']);
}
```

- [ ] **Step 2: Run tests to verify failure**

```bash
docker compose run --rm app php artisan test tests/Unit/Modules/Audit/AuditLoggerTest.php
```

Expected: FAIL because `AuditLogger` does not exist.

- [ ] **Step 3: Create internal event**

Create `src/backend/app/Modules/Audit/Domain/Events/AuditLogged.php`:

```php
<?php

namespace App\Modules\Audit\Domain\Events;

use DateTimeInterface;

final readonly class AuditLogged
{
    public function __construct(
        public string $auditLogId,
        public string $action,
        public string $module,
        public string $entityType,
        public ?string $entityId,
        public string $result,
        public DateTimeInterface $occurredAt,
    ) {}
}
```

- [ ] **Step 4: Create AuditLogger**

Create `src/backend/app/Modules/Audit/Application/Services/AuditLogger.php`:

```php
<?php

namespace App\Modules\Audit\Application\Services;

use App\Modules\Audit\Domain\Events\AuditLogged;
use App\Modules\Audit\Infrastructure\Persistence\Eloquent\AuditLogModel;
use DateTimeInterface;
use Illuminate\Support\Facades\Event;

class AuditLogger
{
    private const REDACTED = '[REDACTED]';
    private const SENSITIVE_KEYS = ['password', 'password_hash', 'token', 'access_token', 'refresh_token', 'secret', 'api_key'];

    public function log(
        string $action,
        string $module,
        string $entityType,
        ?string $entityId = null,
        ?string $actorUserId = null,
        ?array $beforePayload = null,
        ?array $afterPayload = null,
        string $result = 'success',
        ?DateTimeInterface $occurredAt = null,
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): AuditLogModel {
        $log = AuditLogModel::create([
            'actor_user_id' => $actorUserId,
            'action' => $action,
            'module' => $module,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'before_payload' => $this->redact($beforePayload),
            'after_payload' => $this->redact($afterPayload),
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'result' => $result,
            'occurred_at' => $occurredAt ?? now(),
        ]);

        Event::dispatch(new AuditLogged($log->id, $action, $module, $entityType, $entityId, $result, $log->occurred_at));

        return $log;
    }

    private function redact(?array $payload): ?array
    {
        if ($payload === null) {
            return null;
        }

        $redacted = [];
        foreach ($payload as $key => $value) {
            $redacted[$key] = in_array(strtolower((string) $key), self::SENSITIVE_KEYS, true)
                ? self::REDACTED
                : (is_array($value) ? $this->redact($value) : $value);
        }

        return $redacted;
    }
}
```

- [ ] **Step 5: Run tests to verify pass**

```bash
docker compose run --rm app php artisan test tests/Unit/Modules/Audit/AuditLoggerTest.php
```

Expected: PASS.

- [ ] **Step 6: Commit**

```bash
git add src/backend/app/Modules/Audit/Application/Services/AuditLogger.php src/backend/app/Modules/Audit/Domain/Events/AuditLogged.php src/backend/tests/Unit/Modules/Audit/AuditLoggerTest.php
git commit -m "feat(audit): add audit logger with redaction"
```

---

## Task 3: Identity event listener mapping

**Files:**
- Create: `src/backend/app/Modules/Audit/Infrastructure/Listeners/AuditEventListener.php`
- Modify: `src/backend/app/Providers/AppServiceProvider.php`
- Modify: `src/backend/tests/Unit/Modules/Audit/AuditEventListenerTest.php`

- [ ] **Step 1: Create listener mapping tests**

Create `src/backend/tests/Unit/Modules/Audit/AuditEventListenerTest.php`:

```php
<?php

namespace Tests\Unit\Modules\Audit;

use App\Modules\Audit\Infrastructure\Listeners\AuditEventListener;
use App\Modules\Audit\Infrastructure\Persistence\Eloquent\AuditLogModel;
use App\Modules\Identity\Domain\Aggregates\Role\PermissionCode;
use App\Modules\Identity\Domain\Aggregates\Role\RoleCode;
use App\Modules\Identity\Domain\Aggregates\Role\RoleId;
use App\Modules\Identity\Domain\Aggregates\User\Email;
use App\Modules\Identity\Domain\Aggregates\User\UserId;
use App\Modules\Identity\Domain\Events\RoleCreated;
use App\Modules\Identity\Domain\Events\RolePermissionGranted;
use App\Modules\Identity\Domain\Events\UserCreated;
use App\Modules\Identity\Domain\Events\UserLoginFailed;
use DateTimeImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditEventListenerTest extends TestCase
{
    use RefreshDatabase;

    public function test_maps_user_created_event(): void
    {
        $userId = UserId::generate();

        app(AuditEventListener::class)->handle(new UserCreated(
            userId: $userId,
            email: Email::fromString('admin@ihrm.local'),
            occurredAt: new DateTimeImmutable('2026-07-01 00:00:00'),
        ));

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'created',
            'module' => 'identity',
            'entity_type' => 'user',
            'entity_id' => (string) $userId,
            'result' => 'success',
        ]);
    }

    public function test_maps_failed_login_event(): void
    {
        app(AuditEventListener::class)->handle(new UserLoginFailed(
            email: Email::fromString('missing@ihrm.local'),
            reason: 'Invalid credentials',
            occurredAt: new DateTimeImmutable('2026-07-01 00:00:00'),
        ));

        $log = AuditLogModel::firstOrFail();
        $this->assertSame('login_failed', $log->action);
        $this->assertSame('failure', $log->result);
        $this->assertSame('missing@ihrm.local', $log->after_payload['email']);
    }

    public function test_maps_role_and_permission_events(): void
    {
        $roleId = RoleId::generate();

        app(AuditEventListener::class)->handle(new RoleCreated(
            roleId: $roleId,
            code: RoleCode::fromString('HR_MANAGER'),
            occurredAt: new DateTimeImmutable('2026-07-01 00:00:00'),
        ));
        app(AuditEventListener::class)->handle(new RolePermissionGranted(
            roleId: $roleId,
            code: PermissionCode::fromString('identity.user.list'),
            occurredAt: new DateTimeImmutable('2026-07-01 00:00:00'),
        ));

        $this->assertDatabaseHas('audit_logs', ['action' => 'created', 'entity_type' => 'role', 'entity_id' => (string) $roleId]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'permission_granted', 'entity_type' => 'role', 'entity_id' => (string) $roleId]);
    }
}
```

- [ ] **Step 2: Run tests to verify failure**

```bash
docker compose run --rm app php artisan test tests/Unit/Modules/Audit/AuditEventListenerTest.php
```

Expected: FAIL because listener does not exist.

- [ ] **Step 3: Create AuditEventListener**

Create `src/backend/app/Modules/Audit/Infrastructure/Listeners/AuditEventListener.php`:

```php
<?php

namespace App\Modules\Audit\Infrastructure\Listeners;

use App\Modules\Audit\Application\Services\AuditLogger;
use App\Modules\Identity\Domain\Events\RoleCreated;
use App\Modules\Identity\Domain\Events\RolePermissionGranted;
use App\Modules\Identity\Domain\Events\RolePermissionRevoked;
use App\Modules\Identity\Domain\Events\RoleUpdated;
use App\Modules\Identity\Domain\Events\UserCreated;
use App\Modules\Identity\Domain\Events\UserDataScopeGranted;
use App\Modules\Identity\Domain\Events\UserDisabled;
use App\Modules\Identity\Domain\Events\UserLoggedIn;
use App\Modules\Identity\Domain\Events\UserLoginFailed;
use App\Modules\Identity\Domain\Events\UserPasswordChanged;
use App\Modules\Identity\Domain\Events\UserReactivated;
use App\Modules\Identity\Domain\Events\UserRoleAssigned;
use App\Modules\Identity\Domain\Events\UserRoleRevoked;
use Illuminate\Support\Facades\Log;

class AuditEventListener
{
    public function __construct(private AuditLogger $logger) {}

    public function handle(object $event): void
    {
        $data = $this->map($event);
        if ($data === null) {
            return;
        }

        try {
            $this->logger->log(
                action: $data['action'],
                module: 'identity',
                entityType: $data['entity_type'],
                entityId: $data['entity_id'],
                actorUserId: $data['actor_user_id'] ?? (auth()->id() ? (string) auth()->id() : null),
                beforePayload: $data['before_payload'] ?? null,
                afterPayload: $data['after_payload'] ?? null,
                result: $data['result'],
                occurredAt: $event->occurredAt ?? now(),
                ipAddress: request()?->ip(),
                userAgent: request()?->userAgent(),
            );
        } catch (\Throwable $exception) {
            Log::warning('Audit write failed.', ['event' => $event::class, 'message' => $exception->getMessage()]);
        }
    }

    private function map(object $event): ?array
    {
        return match ($event::class) {
            UserCreated::class => ['action' => 'created', 'entity_type' => 'user', 'entity_id' => (string) $event->userId, 'result' => 'success', 'after_payload' => ['email' => (string) $event->email]],
            UserLoggedIn::class => ['action' => 'login', 'entity_type' => 'user', 'entity_id' => (string) $event->userId, 'result' => 'success'],
            UserLoginFailed::class => ['action' => 'login_failed', 'entity_type' => 'user', 'entity_id' => null, 'result' => 'failure', 'after_payload' => ['email' => (string) $event->email, 'reason' => $event->reason]],
            UserDisabled::class => ['action' => 'disabled', 'entity_type' => 'user', 'entity_id' => (string) $event->userId, 'result' => 'success'],
            UserReactivated::class => ['action' => 'reactivated', 'entity_type' => 'user', 'entity_id' => (string) $event->userId, 'result' => 'success'],
            UserPasswordChanged::class => ['action' => 'password_changed', 'entity_type' => 'user', 'entity_id' => (string) $event->userId, 'result' => 'success'],
            UserRoleAssigned::class => ['action' => 'role_assigned', 'entity_type' => 'user', 'entity_id' => (string) $event->userId, 'actor_user_id' => $event->assignedBy ? (string) $event->assignedBy : null, 'result' => 'success', 'after_payload' => ['role_id' => (string) $event->roleId]],
            UserRoleRevoked::class => ['action' => 'role_revoked', 'entity_type' => 'user', 'entity_id' => (string) $event->userId, 'result' => 'success', 'after_payload' => ['role_id' => (string) $event->roleId]],
            UserDataScopeGranted::class => ['action' => 'data_scope_granted', 'entity_type' => 'user', 'entity_id' => (string) $event->userId, 'result' => 'success', 'after_payload' => ['scope_type' => $event->scope->type->value]],
            RoleCreated::class => ['action' => 'created', 'entity_type' => 'role', 'entity_id' => (string) $event->roleId, 'result' => 'success', 'after_payload' => ['code' => (string) $event->code]],
            RoleUpdated::class => ['action' => 'updated', 'entity_type' => 'role', 'entity_id' => (string) $event->roleId, 'result' => 'success'],
            RolePermissionGranted::class => ['action' => 'permission_granted', 'entity_type' => 'role', 'entity_id' => (string) $event->roleId, 'result' => 'success', 'after_payload' => ['permission_code' => (string) $event->code]],
            RolePermissionRevoked::class => ['action' => 'permission_revoked', 'entity_type' => 'role', 'entity_id' => (string) $event->roleId, 'result' => 'success', 'after_payload' => ['permission_code' => (string) $event->code]],
            default => null,
        };
    }
}
```

- [ ] **Step 4: Register listener in AppServiceProvider**

Modify `src/backend/app/Providers/AppServiceProvider.php`:

```php
use App\Modules\Audit\Infrastructure\Listeners\AuditEventListener;
use App\Modules\Identity\Domain\Events\RoleCreated;
use App\Modules\Identity\Domain\Events\RolePermissionGranted;
use App\Modules\Identity\Domain\Events\RolePermissionRevoked;
use App\Modules\Identity\Domain\Events\RoleUpdated;
use App\Modules\Identity\Domain\Events\UserCreated;
use App\Modules\Identity\Domain\Events\UserDataScopeGranted;
use App\Modules\Identity\Domain\Events\UserDisabled;
use App\Modules\Identity\Domain\Events\UserLoggedIn;
use App\Modules\Identity\Domain\Events\UserLoginFailed;
use App\Modules\Identity\Domain\Events\UserPasswordChanged;
use App\Modules\Identity\Domain\Events\UserReactivated;
use App\Modules\Identity\Domain\Events\UserRoleAssigned;
use App\Modules\Identity\Domain\Events\UserRoleRevoked;
use Illuminate\Support\Facades\Event;
```

Replace `boot()` with:

```php
public function boot(): void
{
    foreach ([
        UserCreated::class,
        UserLoggedIn::class,
        UserLoginFailed::class,
        UserDisabled::class,
        UserReactivated::class,
        UserPasswordChanged::class,
        UserRoleAssigned::class,
        UserRoleRevoked::class,
        UserDataScopeGranted::class,
        RoleCreated::class,
        RoleUpdated::class,
        RolePermissionGranted::class,
        RolePermissionRevoked::class,
    ] as $event) {
        Event::listen($event, AuditEventListener::class);
    }
}
```

- [ ] **Step 5: Run tests to verify pass**

```bash
docker compose run --rm app php artisan test tests/Unit/Modules/Audit/AuditEventListenerTest.php tests/Unit/Modules/Audit/AuditLoggerTest.php
```

Expected: PASS.

- [ ] **Step 6: Commit**

```bash
git add src/backend/app/Modules/Audit/Infrastructure/Listeners/AuditEventListener.php src/backend/app/Providers/AppServiceProvider.php src/backend/tests/Unit/Modules/Audit/AuditEventListenerTest.php
git commit -m "feat(audit): map identity events to audit logs"
```

---

## Task 4: Read-only Audit API and permission

**Files:**
- Create: `src/backend/app/Modules/Audit/Infrastructure/Http/Resources/AuditLogResource.php`
- Create: `src/backend/app/Modules/Audit/Infrastructure/Http/Controllers/AuditLogController.php`
- Create: `src/backend/app/Modules/Audit/Routes/api.php`
- Modify: `src/backend/routes/api.php`
- Modify: `src/backend/app/Modules/Identity/Infrastructure/Seeders/PermissionSeeder.php`
- Test: `src/backend/tests/Feature/Modules/Audit/AuditLogApiTest.php`

- [ ] **Step 1: Create failing API tests**

Create `src/backend/tests/Feature/Modules/Audit/AuditLogApiTest.php`:

```php
<?php

namespace Tests\Feature\Modules\Audit;

use App\Modules\Audit\Infrastructure\Persistence\Eloquent\AuditLogModel;
use App\Modules\Identity\Infrastructure\Persistence\Eloquent\UserModel;
use App\Modules\Identity\Infrastructure\Seeders\AdminUserSeeder;
use App\Modules\Identity\Infrastructure\Seeders\PermissionSeeder;
use App\Modules\Identity\Infrastructure\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogApiTest extends TestCase
{
    use RefreshDatabase;

    private function seedAdmin(): UserModel
    {
        $this->seed(PermissionSeeder::class);
        $this->seed(RoleSeeder::class);
        $this->seed(AdminUserSeeder::class);
        return UserModel::where('email', 'admin@ihrm.local')->firstOrFail();
    }

    public function test_admin_can_list_audit_logs(): void
    {
        $admin = $this->seedAdmin();
        AuditLogModel::create(['actor_user_id' => $admin->id, 'action' => 'login', 'module' => 'identity', 'entity_type' => 'user', 'entity_id' => $admin->id, 'result' => 'success', 'occurred_at' => now()]);

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/audit-logs')
            ->assertOk()
            ->assertJsonStructure(['data' => [['id', 'actor_user_id', 'action', 'module', 'entity_type', 'result', 'occurred_at']], 'meta' => ['total']]);
    }

    public function test_user_without_permission_gets_403(): void
    {
        $user = UserModel::create(['name' => 'No Permission', 'email' => 'no-permission@ihrm.local', 'password' => bcrypt('password'), 'status' => 'active']);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/audit-logs')
            ->assertStatus(403);
    }

    public function test_filters_by_action_module_entity_result_and_date(): void
    {
        $admin = $this->seedAdmin();
        AuditLogModel::create(['actor_user_id' => $admin->id, 'action' => 'login', 'module' => 'identity', 'entity_type' => 'user', 'entity_id' => $admin->id, 'result' => 'success', 'occurred_at' => '2026-07-01 10:00:00']);
        AuditLogModel::create(['actor_user_id' => null, 'action' => 'login_failed', 'module' => 'identity', 'entity_type' => 'user', 'entity_id' => null, 'result' => 'failure', 'occurred_at' => '2026-07-02 10:00:00']);

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/audit-logs?action=login&module=identity&entity_type=user&result=success&date_from=2026-07-01&date_to=2026-07-01')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.action', 'login');
    }
}
```

- [ ] **Step 2: Run tests to verify failure**

```bash
docker compose run --rm app php artisan test tests/Feature/Modules/Audit/AuditLogApiTest.php
```

Expected: FAIL because routes/controller/resource and permission are missing.

- [ ] **Step 3: Create resource**

Create `src/backend/app/Modules/Audit/Infrastructure/Http/Resources/AuditLogResource.php`:

```php
<?php

namespace App\Modules\Audit\Infrastructure\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AuditLogResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'actor_user_id' => $this->actor_user_id,
            'action' => $this->action,
            'module' => $this->module,
            'entity_type' => $this->entity_type,
            'entity_id' => $this->entity_id,
            'before_payload' => $this->before_payload,
            'after_payload' => $this->after_payload,
            'ip_address' => $this->ip_address,
            'user_agent' => $this->user_agent,
            'result' => $this->result,
            'occurred_at' => $this->occurred_at?->toISOString(),
        ];
    }
}
```

- [ ] **Step 4: Create controller**

Create `src/backend/app/Modules/Audit/Infrastructure/Http/Controllers/AuditLogController.php`:

```php
<?php

namespace App\Modules\Audit\Infrastructure\Http\Controllers;

use App\Modules\Audit\Infrastructure\Http\Resources\AuditLogResource;
use App\Modules\Audit\Infrastructure\Persistence\Eloquent\AuditLogModel;
use App\Modules\Shared\Http\Resources\PaginatedCollection;
use Illuminate\Http\Request;

class AuditLogController
{
    public function index(Request $request): PaginatedCollection
    {
        $query = AuditLogModel::query()->orderByDesc('occurred_at');

        foreach (['actor_user_id', 'action', 'module', 'entity_type', 'entity_id', 'result'] as $field) {
            if ($request->filled($field)) {
                $query->where($field, $request->string($field));
            }
        }

        if ($request->filled('date_from')) {
            $query->whereDate('occurred_at', '>=', $request->date('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('occurred_at', '<=', $request->date('date_to'));
        }

        return new PaginatedCollection($query->paginate((int) $request->integer('per_page', 20)), AuditLogResource::class);
    }
}
```

- [ ] **Step 5: Add route**

Create `src/backend/app/Modules/Audit/Routes/api.php`:

```php
<?php

use App\Modules\Audit\Infrastructure\Http\Controllers\AuditLogController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::get('audit-logs', [AuditLogController::class, 'index'])->middleware('permission:audit.log.list');
});
```

Modify `src/backend/routes/api.php`:

```php
<?php

require __DIR__ . '/../app/Modules/Identity/Routes/api.php';
require __DIR__ . '/../app/Modules/Configuration/Routes/api.php';
require __DIR__ . '/../app/Modules/Audit/Routes/api.php';
```

- [ ] **Step 6: Seed audit permission**

Modify `src/backend/app/Modules/Identity/Infrastructure/Seeders/PermissionSeeder.php`; add to `$permissions`:

```php
['audit.log.list', 'log.list', 'List audit logs'],
```

The existing seeder loop sets module to `identity`; change the loop to derive module from code prefix:

```php
foreach ($permissions as [$code, $action, $description]) {
    PermissionModel::updateOrCreate(
        ['code' => $code],
        [
            'module' => str($code)->before('.')->toString(),
            'action' => $action,
            'description' => $description,
            'active' => true,
        ],
    );
}
```

- [ ] **Step 7: Run API tests**

```bash
docker compose run --rm app php artisan test tests/Feature/Modules/Audit/AuditLogApiTest.php
```

Expected: PASS.

- [ ] **Step 8: Commit**

```bash
git add src/backend/app/Modules/Audit/Infrastructure/Http src/backend/app/Modules/Audit/Routes/api.php src/backend/routes/api.php src/backend/app/Modules/Identity/Infrastructure/Seeders/PermissionSeeder.php src/backend/tests/Feature/Modules/Audit/AuditLogApiTest.php
git commit -m "feat(audit): expose read-only audit log API"
```

---

## Task 5: Login and failed login integration

**Files:**
- Modify: `src/backend/app/Modules/Identity/Application/Services/AuthenticationService.php`
- Test: `src/backend/tests/Feature/Modules/Audit/IdentityAuditIntegrationTest.php`

- [ ] **Step 1: Create failing integration tests**

Create `src/backend/tests/Feature/Modules/Audit/IdentityAuditIntegrationTest.php`:

```php
<?php

namespace Tests\Feature\Modules\Audit;

use App\Modules\Audit\Infrastructure\Persistence\Eloquent\AuditLogModel;
use App\Modules\Identity\Infrastructure\Persistence\Eloquent\PermissionModel;
use App\Modules\Identity\Infrastructure\Persistence\Eloquent\RoleModel;
use App\Modules\Identity\Infrastructure\Persistence\Eloquent\UserModel;
use App\Modules\Identity\Infrastructure\Seeders\AdminUserSeeder;
use App\Modules\Identity\Infrastructure\Seeders\PermissionSeeder;
use App\Modules\Identity\Infrastructure\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IdentityAuditIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private function seedIdentity(): UserModel
    {
        $this->seed(PermissionSeeder::class);
        $this->seed(RoleSeeder::class);
        $this->seed(AdminUserSeeder::class);
        return UserModel::where('email', 'admin@ihrm.local')->firstOrFail();
    }

    public function test_successful_login_creates_audit_log(): void
    {
        $admin = $this->seedIdentity();

        $this->postJson('/api/v1/auth/login', ['email' => 'admin@ihrm.local', 'password' => 'password'])->assertOk();

        $this->assertDatabaseHas('audit_logs', [
            'actor_user_id' => $admin->id,
            'action' => 'login',
            'module' => 'identity',
            'entity_type' => 'user',
            'entity_id' => $admin->id,
            'result' => 'success',
        ]);
    }

    public function test_failed_login_creates_audit_log(): void
    {
        $this->seedIdentity();

        $this->postJson('/api/v1/auth/login', ['email' => 'admin@ihrm.local', 'password' => 'wrong'])->assertUnauthorized();

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'login_failed',
            'module' => 'identity',
            'entity_type' => 'user',
            'result' => 'failure',
        ]);
    }

    public function test_role_permission_grant_creates_audit_log(): void
    {
        $admin = $this->seedIdentity();
        $role = RoleModel::where('code', 'HR_MANAGER')->firstOrFail();
        PermissionModel::firstOrCreate(['code' => 'identity.user.list'], ['module' => 'identity', 'action' => 'user.list', 'description' => 'List users', 'active' => true]);

        $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/roles/{$role->id}/permissions", ['permission_code' => 'identity.user.list'])
            ->assertOk();

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'permission_granted',
            'module' => 'identity',
            'entity_type' => 'role',
            'entity_id' => $role->id,
            'result' => 'success',
        ]);
    }
}
```

- [ ] **Step 2: Run tests to verify failure**

```bash
docker compose run --rm app php artisan test tests/Feature/Modules/Audit/IdentityAuditIntegrationTest.php
```

Expected: login tests FAIL because `AuthenticationService` does not dispatch login events yet. Role permission test may already pass after listener registration.

- [ ] **Step 3: Dispatch login events**

Modify `src/backend/app/Modules/Identity/Application/Services/AuthenticationService.php` imports:

```php
use App\Modules\Identity\Domain\Aggregates\User\Email;
use App\Modules\Identity\Domain\Aggregates\User\UserId;
use App\Modules\Identity\Domain\Events\UserLoggedIn;
use App\Modules\Identity\Domain\Events\UserLoginFailed;
use DateTimeImmutable;
use Illuminate\Support\Facades\Event;
```

Update `login()` around credential checks:

```php
$emailValue = mb_strtolower(trim($email));
$user = UserModel::where('email', $emailValue)->first();

if (! $user || ! Hash::check($password, $user->password)) {
    Event::dispatch(new UserLoginFailed(Email::fromString($emailValue), 'Invalid credentials', new DateTimeImmutable()));
    throw new InvalidCredentialsException('Invalid credentials');
}

if ($user->status !== 'active') {
    Event::dispatch(new UserLoginFailed(Email::fromString($emailValue), 'User is disabled', new DateTimeImmutable()));
    throw new UserDisabledException('User is disabled');
}

$user->last_login_at = now();
$user->save();
Event::dispatch(new UserLoggedIn(UserId::fromString($user->id), new DateTimeImmutable()));
```

- [ ] **Step 4: Run integration tests**

```bash
docker compose run --rm app php artisan test tests/Feature/Modules/Audit/IdentityAuditIntegrationTest.php
```

Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add src/backend/app/Modules/Identity/Application/Services/AuthenticationService.php src/backend/tests/Feature/Modules/Audit/IdentityAuditIntegrationTest.php
git commit -m "feat(audit): record identity login audit events"
```

---

## Task 6: Final verification and docs

**Files:**
- Create: `src/backend/app/Modules/Audit/README.md`

- [ ] **Step 1: Create module README**

Create `src/backend/app/Modules/Audit/README.md`:

```md
# Audit Module

Read-only Audit & Activity Log module for Phase 1.

## Responsibilities

- Persist append-only audit rows in `audit_logs`.
- Capture Identity events through `AuditEventListener`.
- Redact sensitive keys before persistence.
- Expose `GET /api/v1/audit-logs` for authorized users.

## Permission

- `audit.log.list` — list and filter audit logs.

## Test commands

```bash
docker compose run --rm app php artisan test tests/Unit/Modules/Audit tests/Feature/Modules/Audit
docker compose run --rm app php artisan test
```
```

- [ ] **Step 2: Run audit tests**

```bash
docker compose run --rm app php artisan test tests/Unit/Modules/Audit tests/Feature/Modules/Audit
```

Expected: all Audit tests PASS.

- [ ] **Step 3: Run full tests**

```bash
docker compose run --rm app php artisan test
```

Expected: full backend suite PASS.

- [ ] **Step 4: Check routes**

```bash
docker compose run --rm app php artisan route:list --path=audit-logs
```

Expected includes:

```text
GET|HEAD api/v1/audit-logs
```

- [ ] **Step 5: Commit docs and any final fixes**

```bash
git add src/backend/app/Modules/Audit/README.md
git commit -m "docs(audit): add module readme"
```

- [ ] **Step 6: Push**

```bash
git push
```

---

## Self-Review Checklist

- Spec coverage: persistence, event capture, redaction, read API, permission seeding, tests all mapped to tasks.
- No write/update/delete audit API is planned.
- Append-only is enforced by module convention and absence of public mutation endpoints.
- Full integration with future Organization/Employee/Contract/Document modules is explicitly deferred until those modules exist.
- Plan uses existing module conventions: `Infrastructure/Http`, module `Routes/api.php`, Laravel root migrations, small commits.
