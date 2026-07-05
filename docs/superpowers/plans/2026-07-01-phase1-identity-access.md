# Phase 1 Identity & Access Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build the Phase 1 Identity & Access module with strict DDD layering, Sanctum authentication, RBAC, data-scope foundations, seed data, and tests.

**Architecture:** Strict DDD tactical structure under `src/backend/app/Modules/Identity`: Domain is pure PHP, Application orchestrates commands/queries, Infrastructure owns Eloquent/HTTP/routes/seeders. Permissions are seed-owned read-only catalog; roles and assignments are dynamic via API.

**Tech Stack:** Laravel 12, PHP 8.4, Sanctum, PostgreSQL, PHPUnit, UUID primary keys, Laravel Events, Eloquent repositories.

---

## File Map

- `src/backend/app/Modules/Identity/Domain/**`: pure domain model, value objects, domain events, repository contracts.
- `src/backend/app/Modules/Identity/Application/**`: commands, handlers, authz/authn services.
- `src/backend/app/Modules/Identity/Infrastructure/**`: Eloquent models, repositories, HTTP layer, seeders, routes.
- `src/backend/database/migrations/*identity*.php`: identity tables; keep module migrations discoverable by Laravel.
- `src/backend/database/seeders/DatabaseSeeder.php`: call Identity seeders.
- `src/backend/bootstrap/app.php`: register `PermissionMiddleware` alias.
- `src/backend/routes/api.php`: load Identity routes.
- `src/backend/tests/Unit/Modules/Identity/**`: domain/application tests.
- `src/backend/tests/Feature/Modules/Identity/**`: HTTP API tests.

---

### Task 1: Identity database schema

**Files:**
- Modify: `src/backend/database/migrations/0001_01_01_000000_create_users_table.php`
- Create: `src/backend/database/migrations/2026_07_01_000001_create_roles_table.php`
- Create: `src/backend/database/migrations/2026_07_01_000002_create_permissions_table.php`
- Create: `src/backend/database/migrations/2026_07_01_000003_create_role_permissions_table.php`
- Create: `src/backend/database/migrations/2026_07_01_000004_create_user_roles_table.php`
- Create: `src/backend/database/migrations/2026_07_01_000005_create_data_scope_assignments_table.php`

- [ ] **Step 1: Replace users migration with UUID/status schema**

Use this content:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('employee_id')->nullable()->index();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('status', 20)->default('active')->index();
            $table->timestamp('last_login_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignUuid('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
```

- [ ] **Step 2: Add role/permission/assignment migrations**

Create exact schemas:

```php
// roles migration up() body
Schema::create('roles', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->string('code', 100)->unique();
    $table->string('name');
    $table->text('description')->nullable();
    $table->boolean('active')->default(true)->index();
    $table->timestamps();
});

// permissions migration up() body
Schema::create('permissions', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->string('code', 150)->unique();
    $table->string('module', 50)->index();
    $table->string('action', 100);
    $table->text('description')->nullable();
    $table->boolean('active')->default(true)->index();
    $table->timestamps();
});

// role_permissions migration up() body
Schema::create('role_permissions', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->foreignUuid('role_id')->constrained('roles')->cascadeOnDelete();
    $table->string('permission_code', 150);
    $table->foreign('permission_code')->references('code')->on('permissions')->restrictOnDelete();
    $table->timestamp('created_at')->nullable();
    $table->unique(['role_id', 'permission_code']);
    $table->index('permission_code');
});

// user_roles migration up() body
Schema::create('user_roles', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
    $table->foreignUuid('role_id')->constrained('roles')->restrictOnDelete();
    $table->foreignUuid('assigned_by')->nullable()->constrained('users')->nullOnDelete();
    $table->timestamp('assigned_at');
    $table->timestamp('revoked_at')->nullable();
    $table->timestamps();
    $table->index(['user_id', 'role_id']);
});
DB::statement('CREATE UNIQUE INDEX user_roles_active_unique ON user_roles(user_id, role_id) WHERE revoked_at IS NULL');

// data_scope_assignments migration up() body
Schema::create('data_scope_assignments', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
    $table->string('scope_type', 30)->index();
    $table->uuid('branch_id')->nullable()->index();
    $table->uuid('department_id')->nullable()->index();
    $table->date('effective_from')->nullable();
    $table->date('effective_to')->nullable();
    $table->timestamps();
});
```

Use `DB::statement('CREATE UNIQUE INDEX user_roles_active_unique ON user_roles(user_id, role_id) WHERE revoked_at IS NULL')` in `user_roles` migration and drop it in `down()`.

- [ ] **Step 3: Run migration reset**

Run:

```bash
docker compose run --rm app php artisan migrate
```

Expected: all migrations run successfully.

- [ ] **Step 4: Commit**

```bash
git add src/backend/database/migrations
git commit -m "feat(identity): add identity database schema"
```

---

### Task 2: Domain value objects and enums

**Files:**
- Create: `src/backend/app/Modules/Identity/Domain/Aggregates/User/{UserId.php,EmployeeId.php,Email.php,HashedPassword.php,UserName.php,UserStatus.php,RoleBinding.php,DataScope.php,DataScopeAssignment.php}`
- Create: `src/backend/app/Modules/Identity/Domain/Aggregates/Role/{RoleId.php,RoleCode.php,PermissionCode.php,RoleName.php,RolePermission.php}`
- Test: `src/backend/tests/Unit/Modules/Identity/Domain/EmailTest.php`

- [ ] **Step 1: Write failing Email value object test**

```php
<?php

namespace Tests\Unit\Modules\Identity\Domain;

use App\Modules\Identity\Domain\Aggregates\User\Email;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class EmailTest extends TestCase
{
    public function test_email_normalizes_to_lowercase(): void
    {
        $this->assertSame('admin@ihrm.local', (string) Email::fromString('Admin@IHRM.Local'));
    }

    public function test_invalid_email_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Email::fromString('bad-email');
    }
}
```

- [ ] **Step 2: Run failing test**

```bash
docker compose run --rm app php artisan test tests/Unit/Modules/Identity/Domain/EmailTest.php
```

Expected: FAIL because `Email` class does not exist.

- [ ] **Step 3: Implement value objects**

Implement small final readonly classes with `fromString()`, `__toString()`, `equals()` where useful. Enums:

```php
enum UserStatus: string { case Active = 'active'; case Disabled = 'disabled'; }
enum ScopeType: string { case Self = 'self'; case DirectReports = 'direct_reports'; case Department = 'department'; case Branch = 'branch'; case AllCompany = 'all_company'; }
```

Validation rules:
- `Email`: `filter_var($email, FILTER_VALIDATE_EMAIL)`, lowercase.
- `RoleCode`: uppercase snake-like regex `/^[A-Z][A-Z0-9_]*$/`.
- `PermissionCode`: lowercase dotted regex `/^[a-z][a-z0-9_]*(\.[a-z][a-z0-9_]*)+$/`.
- ID VOs: validate UUID via `Ramsey\Uuid\Uuid::isValid()`; add `generate()`.

- [ ] **Step 4: Run test**

```bash
docker compose run --rm app php artisan test tests/Unit/Modules/Identity/Domain/EmailTest.php
```

Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add src/backend/app/Modules/Identity/Domain/Aggregates src/backend/tests/Unit/Modules/Identity/Domain/EmailTest.php
git commit -m "feat(identity): add domain value objects"
```

---

### Task 3: User and Role aggregates

**Files:**
- Create: `src/backend/app/Modules/Identity/Domain/Aggregates/User/User.php`
- Create: `src/backend/app/Modules/Identity/Domain/Aggregates/Role/Role.php`
- Create: `src/backend/app/Modules/Identity/Domain/Events/*.php`
- Test: `src/backend/tests/Unit/Modules/Identity/Domain/UserAggregateTest.php`
- Test: `src/backend/tests/Unit/Modules/Identity/Domain/RoleAggregateTest.php`

- [ ] **Step 1: Write failing aggregate tests**

Tests must assert:
- User create records `UserCreated` event.
- User disable changes status and records `UserDisabled`.
- User cannot assign same role twice actively.
- Role grant permission records `RolePermissionGranted`.
- Role cannot grant same permission twice.

- [ ] **Step 2: Run tests**

```bash
docker compose run --rm app php artisan test tests/Unit/Modules/Identity/Domain/UserAggregateTest.php tests/Unit/Modules/Identity/Domain/RoleAggregateTest.php
```

Expected: FAIL because aggregate classes do not exist.

- [ ] **Step 3: Implement aggregates and event recording**

Implement `recordThat(object $event)`, `releaseEvents(): array`, `hasActiveRole(RoleId $roleId)`, `assignRole()`, `revokeRole()`, `grantDataScope()`, `grantPermission()`, `revokePermission()`.

- [ ] **Step 4: Run tests**

Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add src/backend/app/Modules/Identity/Domain src/backend/tests/Unit/Modules/Identity/Domain
git commit -m "feat(identity): add user and role aggregates"
```

---

### Task 4: Eloquent models and repositories

**Files:**
- Create: `src/backend/app/Modules/Identity/Infrastructure/Persistence/Eloquent/{UserModel,RoleModel,PermissionModel,UserRoleModel,RolePermissionModel,DataScopeAssignmentModel}.php`
- Create: `src/backend/app/Modules/Identity/Domain/Repositories/{UserRepositoryInterface,RoleRepositoryInterface}.php`
- Create: `src/backend/app/Modules/Identity/Infrastructure/Persistence/Repositories/{EloquentUserRepository,EloquentRoleRepository}.php`
- Modify: `src/backend/app/Providers/AppServiceProvider.php`
- Test: `src/backend/tests/Unit/Modules/Identity/Application/UserRepositoryTest.php`

- [ ] **Step 1: Write repository integration tests**

Test save/find by id/email, role assignment persistence, data-scope persistence, role permissions persistence.

- [ ] **Step 2: Run failing tests**

Expected: FAIL missing repositories/models.

- [ ] **Step 3: Implement Eloquent models**

All models: `$incrementing=false`, `$keyType='string'`, guarded `[]`, relationships. `UserModel` uses `HasApiTokens`, `Authenticatable`, `Notifiable`, maps table `users`.

- [ ] **Step 4: Implement repository interfaces and Eloquent repos**

`findById`, `findByEmail`, `save`, `listPaginated` for users; `findById`, `findByCode`, `save`, `listPaginated`, `listPermissions` for roles.

- [ ] **Step 5: Bind interfaces in AppServiceProvider**

Bind `UserRepositoryInterface::class => EloquentUserRepository::class`, `RoleRepositoryInterface::class => EloquentRoleRepository::class`.

- [ ] **Step 6: Run tests and commit**

```bash
docker compose run --rm app php artisan test tests/Unit/Modules/Identity/Application/UserRepositoryTest.php
git add src/backend/app/Modules/Identity src/backend/app/Providers/AppServiceProvider.php src/backend/tests/Unit/Modules/Identity/Application
git commit -m "feat(identity): add eloquent repositories"
```

---

### Task 5: Seed permissions, roles, admin user

**Files:**
- Create: `src/backend/app/Modules/Identity/Infrastructure/Seeders/{PermissionSeeder.php,RoleSeeder.php,AdminUserSeeder.php}.php`
- Modify: `src/backend/database/seeders/DatabaseSeeder.php`
- Test: `src/backend/tests/Feature/Modules/Identity/IdentitySeederTest.php`

- [ ] **Step 1: Write seeder test**

Assert permissions exist, `SUPER_ADMIN` has all permissions, `admin@ihrm.local` exists with active status and SUPER_ADMIN role.

- [ ] **Step 2: Implement seeders**

Use `updateOrCreate` idempotently. Admin password from `IHRM_ADMIN_PASSWORD`, default `password`.

- [ ] **Step 3: Register seeders**

In `DatabaseSeeder`, call the three Identity seeders.

- [ ] **Step 4: Run seeder test**

```bash
docker compose run --rm app php artisan test tests/Feature/Modules/Identity/IdentitySeederTest.php
```

Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add src/backend/app/Modules/Identity/Infrastructure/Seeders src/backend/database/seeders/DatabaseSeeder.php src/backend/tests/Feature/Modules/Identity/IdentitySeederTest.php
git commit -m "feat(identity): seed permissions roles and admin user"
```

---

### Task 6: Application services and auth flow

**Files:**
- Create: `src/backend/app/Modules/Identity/Application/Services/{AuthenticationService.php,AuthorizationService.php}.php`
- Create: `src/backend/app/Modules/Identity/Application/Commands/{LoginCommand.php,LogoutCommand.php,ChangePasswordCommand.php}.php`
- Create: `src/backend/app/Modules/Identity/Application/CommandHandlers/{LoginHandler.php,LogoutHandler.php,ChangePasswordHandler.php}.php`
- Test: `src/backend/tests/Feature/Modules/Identity/AuthTest.php`

- [ ] **Step 1: Write auth feature tests**

Cover valid login, invalid credentials, disabled user, `/auth/me`, logout revokes token, change password validates current password.

- [ ] **Step 2: Implement services/handlers**

Use Laravel `Hash::check`, `Hash::make`, Sanctum `createToken('api')`, `currentAccessToken()->delete()`.

- [ ] **Step 3: Run tests**

Expected: PASS.

- [ ] **Step 4: Commit**

```bash
git add src/backend/app/Modules/Identity/Application src/backend/tests/Feature/Modules/Identity/AuthTest.php
git commit -m "feat(identity): add authentication services"
```

---

### Task 7: HTTP routes/controllers/resources/requests

**Files:**
- Create: `src/backend/app/Modules/Identity/Infrastructure/Http/Controllers/{AuthController,UserController,RoleController,PermissionController}.php`
- Create: `src/backend/app/Modules/Identity/Infrastructure/Http/Requests/*.php`
- Create: `src/backend/app/Modules/Identity/Infrastructure/Http/Resources/{UserResource,RoleResource,PermissionResource}.php`
- Create: `src/backend/app/Modules/Identity/Routes/api.php`
- Modify: `src/backend/routes/api.php`
- Test: `src/backend/tests/Feature/Modules/Identity/{UserControllerTest.php,RoleControllerTest.php,PermissionControllerTest.php}.php`

- [ ] **Step 1: Write HTTP feature tests**

Cover list/create/update/disable/reactivate users, reset password, assign/revoke role, grant/revoke scope, list/create/update/deactivate roles, grant/revoke role permissions, list permissions.

- [ ] **Step 2: Implement FormRequests**

Validation rules match spec: email, password min 8, role code regex, permission code exists, scope type enum, branch/department UUID nullable.

- [ ] **Step 3: Implement Resources**

Return `{id,email,name,employee_id,status,last_login_at,roles,data_scopes}` for users; `{id,code,name,description,active,permissions}` for roles.

- [ ] **Step 4: Implement controllers and routes**

Routes under `/api/v1`, use `auth:sanctum` and `permission:{code}` middleware. Remove temporary `/users` stub from `src/backend/routes/api.php`.

- [ ] **Step 5: Run tests and commit**

```bash
docker compose run --rm app php artisan test tests/Feature/Modules/Identity
git add src/backend/app/Modules/Identity/Infrastructure/Http src/backend/app/Modules/Identity/Routes src/backend/routes/api.php src/backend/tests/Feature/Modules/Identity
git commit -m "feat(identity): expose identity HTTP API"
```

---

### Task 8: Permission middleware and data-scope helper

**Files:**
- Create: `src/backend/app/Modules/Identity/Infrastructure/Http/Middleware/PermissionMiddleware.php`
- Create: `src/backend/app/Modules/Identity/Domain/Services/DataScopeResolver.php`
- Create: `src/backend/app/Modules/Identity/Infrastructure/Persistence/Eloquent/Concerns/HasDataScope.php`
- Modify: `src/backend/bootstrap/app.php`
- Test: `src/backend/tests/Feature/Modules/Identity/PermissionMiddlewareTest.php`
- Test: `src/backend/tests/Unit/Modules/Identity/Domain/DataScopeResolverTest.php`

- [ ] **Step 1: Write middleware/data-scope tests**

Assert missing permission returns 403 `PERMISSION_DENIED`; SUPER_ADMIN can access; data scope resolver handles self, branch, department, all_company.

- [ ] **Step 2: Register middleware alias**

In `bootstrap/app.php`: `$middleware->alias(['permission' => PermissionMiddleware::class]);`

- [ ] **Step 3: Implement PermissionMiddleware**

Resolve current user; check `AuthorizationService::userHasPermission($userId, $permissionCode)`; return JSON ErrorResource with 403 if missing.

- [ ] **Step 4: Implement DataScopeResolver + HasDataScope trait**

Trait exposes `scopeVisibleTo($query, UserModel $user)` and delegates filter generation to resolver.

- [ ] **Step 5: Run tests and commit**

```bash
docker compose run --rm app php artisan test tests/Feature/Modules/Identity/PermissionMiddlewareTest.php tests/Unit/Modules/Identity/Domain/DataScopeResolverTest.php
git add src/backend/app/Modules/Identity src/backend/bootstrap/app.php src/backend/tests
git commit -m "feat(identity): add permission middleware and data scope resolver"
```

---

### Task 9: Final verification and docs

**Files:**
- Create: `src/backend/app/Modules/Identity/README.md`
- Modify: `docs/api/openapi/01-core-platform.openapi.yaml` if response drift exists

- [ ] **Step 1: Write module README**

Document routes, seed commands, env variables, permissions, and test commands.

- [ ] **Step 2: Run all backend tests**

```bash
docker compose run --rm app php artisan test
```

Expected: PASS.

- [ ] **Step 3: Run smoke test**

```bash
docker compose run --rm app php artisan migrate
curl -s -X POST http://localhost:8000/api/v1/auth/login \
  -H 'Content-Type: application/json' \
  -d '{"email":"admin@ihrm.local","password":"password"}'
```

Expected: `access_token` in response.

- [ ] **Step 4: Commit**

```bash
git add src/backend/app/Modules/Identity/README.md docs/api/openapi/01-core-platform.openapi.yaml
git commit -m "docs(identity): document identity module"
```

---

## Self-Review

- Spec coverage: auth, users, roles, permissions, data scope, seed data, middleware, tests covered.
- Placeholders: none requiring engineer invention; task internals define exact expected shape and commands.
- Type consistency: `UserId`, `RoleId`, `PermissionCode`, `RoleCode`, `Email`, `DataScope` names align across tasks.
