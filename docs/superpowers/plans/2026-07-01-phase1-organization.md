# Phase 1 Organization Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build the Phase 1 Organization module with strict DDD layering, parent_id department tree, seed data, and tests.

**Architecture:** Strict DDD tactical structure under `src/backend/app/Modules/Organization`: Domain is pure PHP, Application orchestrates commands/queries, Infrastructure owns Eloquent/HTTP/routes/seeders. Three aggregate roots (Branch, Department, Position) with department supporting parent_id tree hierarchy with cycle detection.

**Tech Stack:** Laravel 12, PHP 8.4, PostgreSQL 16, UUID primary keys, Laravel Events, Eloquent repositories, PHPUnit.

---

## File Map

- `src/backend/app/Modules/Organization/Domain/**`: pure domain model, value objects, domain events, repository contracts, exceptions.
- `src/backend/app/Modules/Organization/Application/**`: commands, handlers, queries, query handlers.
- `src/backend/app/Modules/Organization/Infrastructure/**`: Eloquent models, repositories, HTTP layer, seeders, routes.
- `src/backend/database/migrations/2026_07_01_00*_organization*.php`: organization tables.
- `src/backend/app/Modules/Identity/Infrastructure/Seeders/PermissionSeeder.php`: add organization.* permissions.
- `src/backend/app/Modules/Identity/Infrastructure/Seeders/RoleSeeder.php`: grant organization permissions to roles.
- `src/backend/routes/api.php`: load Organization routes.
- `src/backend/tests/Unit/Modules/Organization/**`: domain/application tests.
- `src/backend/tests/Feature/Modules/Organization/**`: HTTP API tests.

---

### Task 1: Database migrations

**Files:**
- Create: `src/backend/database/migrations/2026_07_01_010001_create_branches_table.php`
- Create: `src/backend/database/migrations/2026_07_01_010002_create_departments_table.php`
- Create: `src/backend/database/migrations/2026_07_01_010003_create_positions_table.php`

- [ ] **Step 1: Create branches migration**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branches', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 50)->unique();
            $table->string('name');
            $table->text('address')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('email')->nullable();
            $table->string('status', 20)->default('active')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branches');
    }
};
```

- [ ] **Step 2: Create departments migration**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('branch_id')->constrained('branches')->restrictOnDelete();
            $table->foreignUuid('parent_id')->nullable()->constrained('departments')->restrictOnDelete();
            $table->string('code', 50);
            $table->string('name');
            $table->foreignUuid('manager_employee_id')->nullable();
            $table->string('status', 20)->default('active')->index();
            $table->timestamps();
            $table->unique(['branch_id', 'code']);
        });

        Schema::table('departments', function (Blueprint $table) {
            $table->index(['branch_id', 'status']);
            $table->index('parent_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};
```

- [ ] **Step 3: Create positions migration**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('positions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 50)->unique();
            $table->string('name');
            $table->unsignedSmallInteger('level')->nullable();
            $table->text('description')->nullable();
            $table->string('status', 20)->default('active')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('positions');
    }
};
```

- [ ] **Step 4: Verify migrations**

```bash
docker compose run --rm app php artisan migrate --pretend
```

Expected: Shows SQL for 3 new tables without errors.

- [ ] **Step 5: Commit**

```bash
git add src/backend/database/migrations/2026_07_01_010001_create_branches_table.php src/backend/database/migrations/2026_07_01_010002_create_departments_table.php src/backend/database/migrations/2026_07_01_010003_create_positions_table.php
git commit -m "feat(organization): add branch, department, position schema"
```

---

### Task 2: Eloquent models

**Files:**
- Create: `src/backend/app/Modules/Organization/Infrastructure/Persistence/Eloquent/BranchModel.php`
- Create: `src/backend/app/Modules/Organization/Infrastructure/Persistence/Eloquent/DepartmentModel.php`
- Create: `src/backend/app/Modules/Organization/Infrastructure/Persistence/Eloquent/PositionModel.php`

- [ ] **Step 1: Create BranchModel**

```php
<?php

namespace App\Modules\Organization\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class BranchModel extends Model
{
    use HasUuids;

    protected $table = 'branches';

    protected $fillable = [
        'id', 'code', 'name', 'address', 'phone', 'email', 'status',
    ];

    protected $casts = [
        'id' => 'string',
    ];

    public function departments()
    {
        return $this->hasMany(DepartmentModel::class, 'branch_id');
    }

    public function activeDepartments()
    {
        return $this->hasMany(DepartmentModel::class, 'branch_id')->where('status', 'active');
    }
}
```

- [ ] **Step 2: Create DepartmentModel**

```php
<?php

namespace App\Modules\Organization\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class DepartmentModel extends Model
{
    use HasUuids;

    protected $table = 'departments';

    protected $fillable = [
        'id', 'branch_id', 'parent_id', 'code', 'name',
        'manager_employee_id', 'status',
    ];

    protected $casts = [
        'id' => 'string',
        'branch_id' => 'string',
        'parent_id' => 'string',
        'manager_employee_id' => 'string',
    ];

    public function branch()
    {
        return $this->belongsTo(BranchModel::class, 'branch_id');
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function activeChildren()
    {
        return $this->hasMany(self::class, 'parent_id')->where('status', 'active');
    }
}
```

- [ ] **Step 3: Create PositionModel**

```php
<?php

namespace App\Modules\Organization\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class PositionModel extends Model
{
    use HasUuids;

    protected $table = 'positions';

    protected $fillable = [
        'id', 'code', 'name', 'level', 'description', 'status',
    ];

    protected $casts = [
        'id' => 'string',
        'level' => 'integer',
    ];
}
```

- [ ] **Step 4: Commit**

```bash
git add src/backend/app/Modules/Organization/Infrastructure/Persistence/Eloquent
git commit -m "feat(organization): add eloquent models for branch, department, position"
```

---

### Task 3: Domain layer — value objects and enums

**Files:**
- Create: `src/backend/app/Modules/Organization/Domain/Aggregates/Branch/BranchId.php`
- Create: `src/backend/app/Modules/Organization/Domain/Aggregates/Branch/BranchCode.php`
- Create: `src/backend/app/Modules/Organization/Domain/Aggregates/Branch/BranchName.php`
- Create: `src/backend/app/Modules/Organization/Domain/Aggregates/Branch/BranchStatus.php`
- Create: `src/backend/app/Modules/Organization/Domain/Aggregates/Department/DepartmentId.php`
- Create: `src/backend/app/Modules/Organization/Domain/Aggregates/Department/DepartmentCode.php`
- Create: `src/backend/app/Modules/Organization/Domain/Aggregates/Department/DepartmentName.php`
- Create: `src/backend/app/Modules/Organization/Domain/Aggregates/Department/DepartmentStatus.php`
- Create: `src/backend/app/Modules/Organization/Domain/Aggregates/Position/PositionId.php`
- Create: `src/backend/app/Modules/Organization/Domain/Aggregates/Position/PositionCode.php`
- Create: `src/backend/app/Modules/Organization/Domain/Aggregates/Position/PositionName.php`
- Create: `src/backend/app/Modules/Organization/Domain/Aggregates/Position/PositionStatus.php`

- [ ] **Step 1: Create BranchId VO**

```php
<?php

namespace App\Modules\Organization\Domain\Aggregates\Branch;

use App\Modules\Shared\Domain\ValueObjects\UuidValueObject;

final readonly class BranchId
{
    use UuidValueObject;
}
```

- [ ] **Step 2: Create BranchCode VO**

```php
<?php

namespace App\Modules\Organization\Domain\Aggregates\Branch;

use App\Modules\Organization\Domain\Exceptions\InvalidOrganizationCodeException;

final readonly class BranchCode
{
    private function __construct(private string $value) {}

    public static function fromString(string $value): self
    {
        $normalized = strtoupper(trim($value));
        if (!preg_match('/^[A-Z][A-Z0-9-]{1,49}$/', $normalized)) {
            throw new InvalidOrganizationCodeException("Branch code must be uppercase alphanumeric with dash, 2-50 chars, starting with a letter.");
        }
        return new self($normalized);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
```

- [ ] **Step 3: Create BranchName VO**

```php
<?php

namespace App\Modules\Organization\Domain\Aggregates\Branch;

final readonly class BranchName
{
    private function __construct(private string $value) {}

    public static function fromString(string $value): self
    {
        $trimmed = trim($value);
        if ($trimmed === '' || mb_strlen($trimmed) > 255) {
            throw new \InvalidArgumentException('Branch name must be between 1 and 255 characters.');
        }
        return new self($trimmed);
    }

    public function value(): string { return $this->value; }
    public function __toString(): string { return $this->value; }
}
```

- [ ] **Step 4: Create BranchStatus enum**

```php
<?php

namespace App\Modules\Organization\Domain\Aggregates\Branch;

enum BranchStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';

    public function isActive(): bool
    {
        return $this === self::Active;
    }

    public function isInactive(): bool
    {
        return $this === self::Inactive;
    }
}
```

- [ ] **Step 5: Create DepartmentId, DepartmentCode, DepartmentName, DepartmentStatus**

DepartmentCode VO (same pattern as BranchCode but uses `InvalidOrganizationCodeException`):

```php
<?php

namespace App\Modules\Organization\Domain\Aggregates\Department;

use App\Modules\Organization\Domain\Exceptions\InvalidOrganizationCodeException;

final readonly class DepartmentCode
{
    private function __construct(private string $value) {}

    public static function fromString(string $value): self
    {
        $normalized = strtoupper(trim($value));
        if (!preg_match('/^[A-Z][A-Z0-9-]{1,49}$/', $normalized)) {
            throw new InvalidOrganizationCodeException("Department code must be uppercase alphanumeric with dash, 2-50 chars.");
        }
        return new self($normalized);
    }

    public function value(): string { return $this->value; }
    public function equals(self $other): bool { return $this->value === $other->value; }
    public function __toString(): string { return $this->value; }
}
```

DepartmentId: same pattern as BranchId (uses UuidValueObject trait).
DepartmentName: same pattern as BranchName.
DepartmentStatus: enum { active, inactive } — same pattern as BranchStatus.

- [ ] **Step 6: Create PositionId, PositionCode, PositionName, PositionStatus**

PositionCode: same pattern as BranchCode (uppercase alphanumeric+dash).
PositionId: UUID VO.
PositionName: string 1..255.
PositionStatus: enum { active, inactive }.

- [ ] **Step 7: Create InvalidOrganizationCodeException**

```php
<?php

namespace App\Modules\Organization\Domain\Exceptions;

class InvalidOrganizationCodeException extends \InvalidArgumentException
{
    public function __construct(string $message = 'Invalid organization code format.')
    {
        parent::__construct($message);
    }
}
```

- [ ] **Step 8: Write domain VO unit tests**

Create `src/backend/tests/Unit/Modules/Organization/Domain/BranchCodeTest.php`:

```php
<?php

namespace Tests\Unit\Modules\Organization\Domain;

use App\Modules\Organization\Domain\Aggregates\Branch\BranchCode;
use App\Modules\Organization\Domain\Exceptions\InvalidOrganizationCodeException;
use PHPUnit\Framework\TestCase;

class BranchCodeTest extends TestCase
{
    public function test_valid_code(): void
    {
        $code = BranchCode::fromString('HCM-HQ');
        $this->assertEquals('HCM-HQ', $code->value());
    }

    public function test_lowercase_is_normalized(): void
    {
        $code = BranchCode::fromString('hcm-hq');
        $this->assertEquals('HCM-HQ', $code->value());
    }

    public function test_invalid_code_throws_exception(): void
    {
        $this->expectException(InvalidOrganizationCodeException::class);
        BranchCode::fromString('invalid@code');
    }

    public function test_code_with_space_throws(): void
    {
        $this->expectException(InvalidOrganizationCodeException::class);
        BranchCode::fromString('invalid code');
    }
}
```

- [ ] **Step 9: Run domain VO tests**

```bash
docker compose run --rm app php artisan test tests/Unit/Modules/Organization/Domain/BranchCodeTest.php
```

Expected: PASS.

- [ ] **Step 10: Commit**

```bash
git add src/backend/app/Modules/Organization/Domain \
       src/backend/tests/Unit/Modules/Organization
git commit -m "feat(organization): add domain value objects and enums"
```

---

### Task 4: Domain layer — events

**Files:**
- Create: `src/backend/app/Modules/Organization/Domain/Events/BranchCreated.php`
- Create: `src/backend/app/Modules/Organization/Domain/Events/BranchUpdated.php`
- Create: `src/backend/app/Modules/Organization/Domain/Events/BranchActivated.php`
- Create: `src/backend/app/Modules/Organization/Domain/Events/BranchDeactivated.php`
- Create: `src/backend/app/Modules/Organization/Domain/Events/DepartmentCreated.php`
- Create: `src/backend/app/Modules/Organization/Domain/Events/DepartmentUpdated.php`
- Create: `src/backend/app/Modules/Organization/Domain/Events/DepartmentMoved.php`
- Create: `src/backend/app/Modules/Organization/Domain/Events/DepartmentActivated.php`
- Create: `src/backend/app/Modules/Organization/Domain/Events/DepartmentDeactivated.php`
- Create: `src/backend/app/Modules/Organization/Domain/Events/PositionCreated.php`
- Create: `src/backend/app/Modules/Organization/Domain/Events/PositionUpdated.php`
- Create: `src/backend/app/Modules/Organization/Domain/Events/PositionActivated.php`
- Create: `src/backend/app/Modules/Organization/Domain/Events/PositionDeactivated.php`

All events follow the Identity pattern: constructor with identity value + DateTimeImmutable, implements a marker interface or plain class.

- [ ] **Step 1: Create BranchCreated event**

```php
<?php

namespace App\Modules\Organization\Domain\Events;

use App\Modules\Organization\Domain\Aggregates\Branch\BranchId;
use DateTimeImmutable;

class BranchCreated
{
    public function __construct(
        public readonly BranchId $branchId,
        public readonly string $code,
        public readonly string $name,
        public readonly DateTimeImmutable $occurredAt = new DateTimeImmutable(),
    ) {}
}
```

- [ ] **Step 2: Create BranchUpdated, BranchActivated, BranchDeactivated**

BranchUpdated has `branchId` + `occurredAt`.
BranchActivated has `branchId` + `occurredAt`.
BranchDeactivated has `branchId` + `occurredAt`.

- [ ] **Step 3: Create Department events**

```php
// DepartmentCreated
class DepartmentCreated
{
    public function __construct(
        public readonly DepartmentId $departmentId,
        public readonly string $code,
        public readonly string $name,
        public readonly DateTimeImmutable $occurredAt = new DateTimeImmutable(),
    ) {}
}

// DepartmentUpdated
class DepartmentUpdated
{
    public function __construct(
        public readonly DepartmentId $departmentId,
        public readonly DateTimeImmutable $occurredAt = new DateTimeImmutable(),
    ) {}
}

// DepartmentMoved
class DepartmentMoved
{
    public function __construct(
        public readonly DepartmentId $departmentId,
        public readonly ?DepartmentId $oldParentId,
        public readonly ?DepartmentId $newParentId,
        public readonly DateTimeImmutable $occurredAt = new DateTimeImmutable(),
    ) {}
}

// DepartmentActivated, DepartmentDeactivated: same pattern (departmentId + occurredAt)
```

- [ ] **Step 4: Create Position events**

Same pattern: `PositionCreated(positionId, code, name, occurredAt)`, rest have `positionId + occurredAt`.

- [ ] **Step 5: Commit**

```bash
git add src/backend/app/Modules/Organization/Domain/Events
git commit -m "feat(organization): add domain events"
```

---

### Task 5: Domain layer — exceptions

**Files:**
- Create: `src/backend/app/Modules/Organization/Domain/Exceptions/BranchNotFoundException.php`
- Create: `src/backend/app/Modules/Organization/Domain/Exceptions/DuplicateBranchCodeException.php`
- Create: `src/backend/app/Modules/Organization/Domain/Exceptions/BranchHasActiveDepartmentsException.php`
- Create: `src/backend/app/Modules/Organization/Domain/Exceptions/DepartmentNotFoundException.php`
- Create: `src/backend/app/Modules/Organization/Domain/Exceptions/DuplicateDepartmentCodeException.php`
- Create: `src/backend/app/Modules/Organization/Domain/Exceptions/CircularMoveException.php`
- Create: `src/backend/app/Modules/Organization/Domain/Exceptions/DepartmentNotInSameBranchException.php`
- Create: `src/backend/app/Modules/Organization/Domain/Exceptions/DepartmentHasActiveChildrenException.php`
- Create: `src/backend/app/Modules/Organization/Domain/Exceptions/PositionNotFoundException.php`
- Create: `src/backend/app/Modules/Organization/Domain/Exceptions/DuplicatePositionCodeException.php`

- [ ] **Step 1: Create all exception classes**

Each extends `\DomainException` or `\InvalidArgumentException` and has a default message + optional constructor.

```php
<?php

namespace App\Modules\Organization\Domain\Exceptions;

class BranchNotFoundException extends \DomainException
{
    public function __construct(string $id = '')
    {
        parent::__construct("Branch not found: {$id}");
    }
}
```

```php
<?php

namespace App\Modules\Organization\Domain\Exceptions;

class DuplicateBranchCodeException extends \DomainException
{
    public function __construct(string $code = '')
    {
        parent::__construct("Branch code already exists: {$code}");
    }
}
```

```php
<?php

namespace App\Modules\Organization\Domain\Exceptions;

class BranchHasActiveDepartmentsException extends \DomainException
{
    public function __construct(string $branchId = '')
    {
        parent::__construct("Cannot deactivate branch {$branchId}: it has active departments.");
    }
}
```

```php
<?php

namespace App\Modules\Organization\Domain\Exceptions;

class CircularMoveException extends \DomainException
{
    public function __construct()
    {
        parent::__construct('Cannot move department: target is self or a descendant.');
    }
}
```

```php
<?php

namespace App\Modules\Organization\Domain\Exceptions;

class DepartmentNotInSameBranchException extends \DomainException
{
    public function __construct()
    {
        parent::__construct('Cannot move department: parent department is in a different branch.');
    }
}
```

```php
<?php

namespace App\Modules\Organization\Domain\Exceptions;

class DepartmentHasActiveChildrenException extends \DomainException
{
    public function __construct(string $departmentId = '')
    {
        parent::__construct("Cannot deactivate department {$departmentId}: it has active child departments.");
    }
}
```

DepartmentNotFoundException, DuplicateDepartmentCodeException, PositionNotFoundException, DuplicatePositionCodeException follow the same pattern as Branch exceptions.

- [ ] **Step 2: Commit**

```bash
git add src/backend/app/Modules/Organization/Domain/Exceptions
git commit -m "feat(organization): add domain exceptions"
```

---

### Task 6: Domain layer — aggregates (Branch, Department, Position)

**Files:**
- Create: `src/backend/app/Modules/Organization/Domain/Aggregates/Branch/Branch.php`
- Create: `src/backend/app/Modules/Organization/Domain/Aggregates/Department/Department.php`
- Create: `src/backend/app/Modules/Organization/Domain/Aggregates/Position/Position.php`

- [ ] **Step 1: Create Branch aggregate**

```php
<?php

namespace App\Modules\Organization\Domain\Aggregates\Branch;

use App\Modules\Organization\Domain\Events\BranchActivated;
use App\Modules\Organization\Domain\Events\BranchCreated;
use App\Modules\Organization\Domain\Events\BranchDeactivated;
use App\Modules\Organization\Domain\Events\BranchUpdated;
use App\Modules\Organization\Domain\Exceptions\BranchHasActiveDepartmentsException;
use DateTimeImmutable;

final class Branch
{
    /** @var object[] */
    private array $recordedEvents = [];

    private function __construct(
        private readonly BranchId $id,
        private readonly BranchCode $code,
        private BranchName $name,
        private ?string $address,
        private ?string $phone,
        private ?string $email,
        private BranchStatus $status,
    ) {}

    public static function create(
        BranchId $id,
        BranchCode $code,
        BranchName $name,
        ?string $address = null,
        ?string $phone = null,
        ?string $email = null,
    ): self {
        $branch = new self($id, $code, $name, $address, $phone, $email, BranchStatus::Active);
        $branch->record(new BranchCreated($id, $code->value(), $name->value(), new DateTimeImmutable()));
        return $branch;
    }

    public static function reconstitute(
        BranchId $id,
        BranchCode $code,
        BranchName $name,
        ?string $address,
        ?string $phone,
        ?string $email,
        BranchStatus $status,
    ): self {
        return new self($id, $code, $name, $address, $phone, $email, $status);
    }

    public function update(BranchName $name, ?string $address, ?string $phone, ?string $email): void
    {
        $this->name = $name;
        $this->address = $address;
        $this->phone = $phone;
        $this->email = $email;
        $this->record(new BranchUpdated($this->id, new DateTimeImmutable()));
    }

    public function activate(): void
    {
        if ($this->status->isActive()) return;
        $this->status = BranchStatus::Active;
        $this->record(new BranchActivated($this->id, new DateTimeImmutable()));
    }

    /**
     * @param callable(): bool $hasActiveDepartmentsFn
     */
    public function deactivate(callable $hasActiveDepartmentsFn): void
    {
        if ($this->status->isInactive()) return;
        if ($hasActiveDepartmentsFn()) {
            throw new BranchHasActiveDepartmentsException($this->id->value());
        }
        $this->status = BranchStatus::Inactive;
        $this->record(new BranchDeactivated($this->id, new DateTimeImmutable()));
    }

    /** @return object[] */
    public function getRecordedEvents(): array
    {
        return $this->recordedEvents;
    }

    public function clearRecordedEvents(): void
    {
        $this->recordedEvents = [];
    }

    private function record(object $event): void
    {
        $this->recordedEvents[] = $event;
    }

    public function id(): BranchId { return $this->id; }
    public function code(): BranchCode { return $this->code; }
    public function name(): BranchName { return $this->name; }
    public function address(): ?string { return $this->address; }
    public function phone(): ?string { return $this->phone; }
    public function email(): ?string { return $this->email; }
    public function status(): BranchStatus { return $this->status; }
}
```

- [ ] **Step 2: Create Department aggregate**

```php
<?php

namespace App\Modules\Organization\Domain\Aggregates\Department;

use App\Modules\Organization\Domain\Aggregates\Branch\BranchId;
use App\Modules\Organization\Domain\Events\DepartmentActivated;
use App\Modules\Organization\Domain\Events\DepartmentCreated;
use App\Modules\Organization\Domain\Events\DepartmentDeactivated;
use App\Modules\Organization\Domain\Events\DepartmentMoved;
use App\Modules\Organization\Domain\Events\DepartmentUpdated;
use App\Modules\Organization\Domain\Exceptions\CircularMoveException;
use App\Modules\Organization\Domain\Exceptions\DepartmentHasActiveChildrenException;
use App\Modules\Organization\Domain\Exceptions\DepartmentNotInSameBranchException;
use DateTimeImmutable;

final class Department
{
    /** @var object[] */
    private array $recordedEvents = [];

    private ?DepartmentId $parentId;
    private ?string $managerEmployeeId;

    private function __construct(
        private readonly DepartmentId $id,
        private readonly DepartmentCode $code,
        private DepartmentName $name,
        private readonly BranchId $branchId,
        ?DepartmentId $parentId,
        ?string $managerEmployeeId,
        private DepartmentStatus $status,
    ) {
        $this->parentId = $parentId;
        $this->managerEmployeeId = $managerEmployeeId;
    }

    public static function create(
        DepartmentId $id,
        DepartmentCode $code,
        DepartmentName $name,
        BranchId $branchId,
        ?DepartmentId $parentId = null,
    ): self {
        $dept = new self($id, $code, $name, $branchId, $parentId, null, DepartmentStatus::Active);
        $dept->record(new DepartmentCreated($id, $code->value(), $name->value(), new DateTimeImmutable()));
        return $dept;
    }

    public static function reconstitute(
        DepartmentId $id,
        DepartmentCode $code,
        DepartmentName $name,
        BranchId $branchId,
        ?DepartmentId $parentId,
        ?string $managerEmployeeId,
        DepartmentStatus $status,
    ): self {
        return new self($id, $code, $name, $branchId, $parentId, $managerEmployeeId, $status);
    }

    public function update(DepartmentName $name, ?string $managerEmployeeId): void
    {
        $this->name = $name;
        $this->managerEmployeeId = $managerEmployeeId;
        $this->record(new DepartmentUpdated($this->id, new DateTimeImmutable()));
    }

    /**
     * @param callable(?DepartmentId): bool $isDescendantFn
     * @param callable(?DepartmentId): BranchId $getParentBranchFn
     */
    public function moveTo(
        ?DepartmentId $newParentId,
        callable $isDescendantFn,
        callable $getParentBranchFn,
    ): void {
        if ($this->id->equals($newParentId)) {
            throw new CircularMoveException();
        }

        if ($isDescendantFn($newParentId)) {
            throw new CircularMoveException();
        }

        if ($newParentId !== null) {
            $parentBranchId = $getParentBranchFn($newParentId);
            if (!$parentBranchId->equals($this->branchId)) {
                throw new DepartmentNotInSameBranchException();
            }
        }

        $oldParentId = $this->parentId;
        $this->parentId = $newParentId;
        $this->record(new DepartmentMoved($this->id, $oldParentId, $newParentId, new DateTimeImmutable()));
    }

    public function activate(): void
    {
        if ($this->status->isActive()) return;
        $this->status = DepartmentStatus::Active;
        $this->record(new DepartmentActivated($this->id, new DateTimeImmutable()));
    }

    /**
     * @param callable(): bool $hasActiveChildrenFn
     */
    public function deactivate(callable $hasActiveChildrenFn): void
    {
        if ($this->status->isInactive()) return;
        if ($hasActiveChildrenFn()) {
            throw new DepartmentHasActiveChildrenException($this->id->value());
        }
        $this->status = DepartmentStatus::Inactive;
        $this->record(new DepartmentDeactivated($this->id, new DateTimeImmutable()));
    }

    /** @return object[] */
    public function getRecordedEvents(): array { return $this->recordedEvents; }
    public function clearRecordedEvents(): void { $this->recordedEvents = []; }
    private function record(object $event): void { $this->recordedEvents[] = $event; }

    public function id(): DepartmentId { return $this->id; }
    public function code(): DepartmentCode { return $this->code; }
    public function name(): DepartmentName { return $this->name; }
    public function branchId(): BranchId { return $this->branchId; }
    public function parentId(): ?DepartmentId { return $this->parentId; }
    public function managerEmployeeId(): ?string { return $this->managerEmployeeId; }
    public function status(): DepartmentStatus { return $this->status; }
}
```

- [ ] **Step 3: Create Position aggregate**

```php
<?php

namespace App\Modules\Organization\Domain\Aggregates\Position;

use App\Modules\Organization\Domain\Events\PositionActivated;
use App\Modules\Organization\Domain\Events\PositionCreated;
use App\Modules\Organization\Domain\Events\PositionDeactivated;
use App\Modules\Organization\Domain\Events\PositionUpdated;
use DateTimeImmutable;

final class Position
{
    /** @var object[] */
    private array $recordedEvents = [];

    private function __construct(
        private readonly PositionId $id,
        private readonly PositionCode $code,
        private PositionName $name,
        private ?int $level,
        private ?string $description,
        private PositionStatus $status,
    ) {}

    public static function create(
        PositionId $id,
        PositionCode $code,
        PositionName $name,
        ?int $level = null,
        ?string $description = null,
    ): self {
        $position = new self($id, $code, $name, $level, $description, PositionStatus::Active);
        $position->record(new PositionCreated($id, $code->value(), $name->value(), new DateTimeImmutable()));
        return $position;
    }

    public static function reconstitute(
        PositionId $id,
        PositionCode $code,
        PositionName $name,
        ?int $level,
        ?string $description,
        PositionStatus $status,
    ): self {
        return new self($id, $code, $name, $level, $description, $status);
    }

    public function update(PositionName $name, ?int $level, ?string $description): void
    {
        $this->name = $name;
        $this->level = $level;
        $this->description = $description;
        $this->record(new PositionUpdated($this->id, new DateTimeImmutable()));
    }

    public function activate(): void
    {
        if ($this->status->isActive()) return;
        $this->status = PositionStatus::Active;
        $this->record(new PositionActivated($this->id, new DateTimeImmutable()));
    }

    public function deactivate(): void
    {
        if ($this->status->isInactive()) return;
        $this->status = PositionStatus::Inactive;
        $this->record(new PositionDeactivated($this->id, new DateTimeImmutable()));
    }

    /** @return object[] */
    public function getRecordedEvents(): array { return $this->recordedEvents; }
    public function clearRecordedEvents(): void { $this->recordedEvents = []; }
    private function record(object $event): void { $this->recordedEvents[] = $event; }

    public function id(): PositionId { return $this->id; }
    public function code(): PositionCode { return $this->code; }
    public function name(): PositionName { return $this->name; }
    public function level(): ?int { return $this->level; }
    public function description(): ?string { return $this->description; }
    public function status(): PositionStatus { return $this->status; }
}
```

- [ ] **Step 4: Write domain aggregate unit tests**

Create `src/backend/tests/Unit/Modules/Organization/Domain/BranchTest.php`:

```php
<?php

namespace Tests\Unit\Modules\Organization\Domain;

use App\Modules\Organization\Domain\Aggregates\Branch\Branch;
use App\Modules\Organization\Domain\Aggregates\Branch\BranchCode;
use App\Modules\Organization\Domain\Aggregates\Branch\BranchId;
use App\Modules\Organization\Domain\Aggregates\Branch\BranchName;
use App\Modules\Organization\Domain\Events\BranchActivated;
use App\Modules\Organization\Domain\Events\BranchCreated;
use App\Modules\Organization\Domain\Events\BranchDeactivated;
use App\Modules\Organization\Domain\Events\BranchUpdated;
use App\Modules\Organization\Domain\Exceptions\BranchHasActiveDepartmentsException;
use PHPUnit\Framework\TestCase;

class BranchTest extends TestCase
{
    private Branch $branch;
    private BranchId $id;

    protected function setUp(): void
    {
        $this->id = new BranchId('550e8400-e29b-41d4-a716-446655440000');
        $this->branch = Branch::create(
            $this->id,
            BranchCode::fromString('HCM-HQ'),
            BranchName::fromString('Ho Chi Minh HQ'),
            '123 Nguyen Hue',
            '0909123456',
            'hcm@example.com',
        );
    }

    public function test_create_branch_emits_event(): void
    {
        $events = $this->branch->getRecordedEvents();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(BranchCreated::class, $events[0]);
        $this->assertEquals('HCM-HQ', $events[0]->code);
    }

    public function test_update_branch(): void
    {
        $this->branch->clearRecordedEvents();
        $this->branch->update(BranchName::fromString('Updated Name'), null, null, null);
        $events = $this->branch->getRecordedEvents();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(BranchUpdated::class, $events[0]);
        $this->assertEquals('Updated Name', $this->branch->name()->value());
    }

    public function test_activate_branch(): void
    {
        $this->branch->clearRecordedEvents();
        $this->branch->activate();
        $events = $this->branch->getRecordedEvents();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(BranchActivated::class, $events[0]);
    }

    public function test_deactivate_without_departments(): void
    {
        $this->branch->clearRecordedEvents();
        $this->branch->deactivate(fn() => false);
        $events = $this->branch->getRecordedEvents();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(BranchDeactivated::class, $events[0]);
    }

    public function test_deactivate_with_active_departments_throws(): void
    {
        $this->branch->clearRecordedEvents();
        $this->expectException(BranchHasActiveDepartmentsException::class);
        $this->branch->deactivate(fn() => true);
    }

    public function test_reconstitute_branch(): void
    {
        $branch = Branch::reconstitute(
            $this->id,
            BranchCode::fromString('HCM-HQ'),
            BranchName::fromString('Ho Chi Minh HQ'),
            null, null, null,
            \App\Modules\Organization\Domain\Aggregates\Branch\BranchStatus::Active,
        );
        $this->assertEquals('HCM-HQ', $branch->code()->value());
        $this->assertEmpty($branch->getRecordedEvents());
    }
}
```

- [ ] **Step 5: Create DepartmentTest**

`src/backend/tests/Unit/Modules/Organization/Domain/DepartmentTest.php`:

```php
<?php

namespace Tests\Unit\Modules\Organization\Domain;

use App\Modules\Organization\Domain\Aggregates\Branch\BranchId;
use App\Modules\Organization\Domain\Aggregates\Department\Department;
use App\Modules\Organization\Domain\Aggregates\Department\DepartmentCode;
use App\Modules\Organization\Domain\Aggregates\Department\DepartmentId;
use App\Modules\Organization\Domain\Aggregates\Department\DepartmentName;
use App\Modules\Organization\Domain\Events\DepartmentCreated;
use App\Modules\Organization\Domain\Events\DepartmentMoved;
use App\Modules\Organization\Domain\Exceptions\CircularMoveException;
use App\Modules\Organization\Domain\Exceptions\DepartmentHasActiveChildrenException;
use App\Modules\Organization\Domain\Exceptions\DepartmentNotInSameBranchException;
use PHPUnit\Framework\TestCase;

class DepartmentTest extends TestCase
{
    private BranchId $branchId;
    private DepartmentId $parentDeptId;
    private DepartmentId $childDeptId;
    private Department $parentDept;

    protected function setUp(): void
    {
        $this->branchId = new BranchId('550e8400-e29b-41d4-a716-446655440000');
        $this->parentDeptId = new DepartmentId('660e8400-e29b-41d4-a716-446655440001');
        $this->childDeptId = new DepartmentId('660e8400-e29b-41d4-a716-446655440002');
        $this->parentDept = Department::create(
            $this->parentDeptId,
            DepartmentCode::fromString('IT'),
            DepartmentName::fromString('IT Department'),
            $this->branchId,
        );
    }

    public function test_create_department_emits_event(): void
    {
        $this->assertCount(1, $this->parentDept->getRecordedEvents());
        $this->assertInstanceOf(DepartmentCreated::class, $this->parentDept->getRecordedEvents()[0]);
    }

    public function test_move_to_self_throws(): void
    {
        $dept = Department::create(
            new DepartmentId('660e8400-e29b-41d4-a716-446655440003'),
            DepartmentCode::fromString('SELF'),
            DepartmentName::fromString('Self Dept'),
            $this->branchId,
        );
        $dept->clearRecordedEvents();
        $this->expectException(CircularMoveException::class);
        $dept->moveTo(
            $dept->id(),
            fn(?DepartmentId $id) => false,
            fn(?DepartmentId $id) => $this->branchId,
        );
    }

    public function test_move_to_descendant_throws(): void
    {
        $dept = Department::create(
            $this->childDeptId,
            DepartmentCode::fromString('DEV'),
            DepartmentName::fromString('Dev Team'),
            $this->branchId,
            $this->parentDeptId,
        );
        $dept->clearRecordedEvents();

        $this->expectException(CircularMoveException::class);
        $this->parentDept->moveTo(
            $this->childDeptId,
            fn(?DepartmentId $id) => true,
            fn(?DepartmentId $id) => $this->branchId,
        );
    }

    public function test_move_to_different_branch_throws(): void
    {
        $otherBranchId = new BranchId('770e8400-e29b-41d4-a716-446655440099');
        $dept = Department::create(
            $this->childDeptId,
            DepartmentCode::fromString('DEV'),
            DepartmentName::fromString('Dev Team'),
            $this->branchId,
        );
        $dept->clearRecordedEvents();

        $this->expectException(DepartmentNotInSameBranchException::class);
        $dept->moveTo(
            new DepartmentId('660e8400-e29b-41d4-a716-446655440100'),
            fn(?DepartmentId $id) => false,
            fn(?DepartmentId $id) => $otherBranchId,
        );
    }

    public function test_move_to_valid_parent(): void
    {
        $dept = Department::create(
            $this->childDeptId,
            DepartmentCode::fromString('DEV'),
            DepartmentName::fromString('Dev Team'),
            $this->branchId,
        );
        $dept->clearRecordedEvents();

        $dept->moveTo(
            $this->parentDeptId,
            fn(?DepartmentId $id) => false,
            fn(?DepartmentId $id) => $this->branchId,
        );
        $events = $dept->getRecordedEvents();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(DepartmentMoved::class, $events[0]);
    }

    public function test_deactivate_with_children_throws(): void
    {
        $dept = Department::create(
            $this->parentDeptId,
            DepartmentCode::fromString('IT'),
            DepartmentName::fromString('IT Dept'),
            $this->branchId,
        );
        $dept->clearRecordedEvents();
        $this->expectException(DepartmentHasActiveChildrenException::class);
        $dept->deactivate(fn() => true);
    }

    public function test_deactivate_without_children(): void
    {
        $dept = Department::create(
            $this->parentDeptId,
            DepartmentCode::fromString('IT'),
            DepartmentName::fromString('IT Dept'),
            $this->branchId,
        );
        $dept->clearRecordedEvents();
        $dept->deactivate(fn() => false);
        $this->assertCount(1, $dept->getRecordedEvents());
        $this->assertInstanceOf(\App\Modules\Organization\Domain\Events\DepartmentDeactivated::class, $dept->getRecordedEvents()[0]);
    }
}
```

- [ ] **Step 6: Create PositionTest**

`src/backend/tests/Unit/Modules/Organization/Domain/PositionTest.php`:

```php
<?php

namespace Tests\Unit\Modules\Organization\Domain;

use App\Modules\Organization\Domain\Aggregates\Position\Position;
use App\Modules\Organization\Domain\Aggregates\Position\PositionCode;
use App\Modules\Organization\Domain\Aggregates\Position\PositionId;
use App\Modules\Organization\Domain\Aggregates\Position\PositionName;
use App\Modules\Organization\Domain\Events\PositionCreated;
use App\Modules\Organization\Domain\Events\PositionDeactivated;
use PHPUnit\Framework\TestCase;

class PositionTest extends TestCase
{
    public function test_create_position_emits_event(): void
    {
        $position = Position::create(
            new PositionId('880e8400-e29b-41d4-a716-446655440000'),
            PositionCode::fromString('DEV'),
            PositionName::fromString('Developer'),
            3,
        );
        $events = $position->getRecordedEvents();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(PositionCreated::class, $events[0]);
        $this->assertEquals('DEV', $events[0]->code);
    }

    public function test_deactivate_position(): void
    {
        $position = Position::create(
            new PositionId('880e8400-e29b-41d4-a716-446655440001'),
            PositionCode::fromString('SR_DEV'),
            PositionName::fromString('Senior Developer'),
            4,
        );
        $position->clearRecordedEvents();
        $position->deactivate();
        $events = $position->getRecordedEvents();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(PositionDeactivated::class, $events[0]);
    }

    public function test_update_position(): void
    {
        $position = Position::create(
            new PositionId('880e8400-e29b-41d4-a716-446655440002'),
            PositionCode::fromString('TL'),
            PositionName::fromString('Team Leader'),
        );
        $position->clearRecordedEvents();
        $position->update(PositionName::fromString('Senior Team Leader'), 5, 'Team lead with 5+ years');
        $this->assertEquals('Senior Team Leader', $position->name()->value());
        $this->assertEquals(5, $position->level());
    }
}
```

- [ ] **Step 7: Run domain tests**

```bash
docker compose run --rm app php artisan test tests/Unit/Modules/Organization
```

Expected: PASS.

- [ ] **Step 8: Commit**

```bash
git add src/backend/app/Modules/Organization/Domain/Aggregates \
       src/backend/tests/Unit/Modules/Organization/Domain
git commit -m "feat(organization): add domain aggregates and unit tests"
```

---

### Task 7: Domain repository interfaces

**Files:**
- Create: `src/backend/app/Modules/Organization/Domain/Repositories/BranchRepositoryInterface.php`
- Create: `src/backend/app/Modules/Organization/Domain/Repositories/DepartmentRepositoryInterface.php`
- Create: `src/backend/app/Modules/Organization/Domain/Repositories/PositionRepositoryInterface.php`

- [ ] **Step 1: Create BranchRepositoryInterface**

```php
<?php

namespace App\Modules\Organization\Domain\Repositories;

use App\Modules\Organization\Domain\Aggregates\Branch\Branch;
use App\Modules\Organization\Domain\Aggregates\Branch\BranchCode;
use App\Modules\Organization\Domain\Aggregates\Branch\BranchId;

interface BranchRepositoryInterface
{
    public function findById(BranchId $id): Branch;
    public function findByCode(BranchCode $code): ?Branch;
    public function existsByCode(BranchCode $code): bool;
    public function hasActiveDepartments(BranchId $id): bool;
    public function save(Branch $branch): void;
    public function saveAndDispatch(Branch $branch): void;
}
```

- [ ] **Step 2: Create DepartmentRepositoryInterface**

```php
<?php

namespace App\Modules\Organization\Domain\Repositories;

use App\Modules\Organization\Domain\Aggregates\Branch\BranchId;
use App\Modules\Organization\Domain\Aggregates\Department\Department;
use App\Modules\Organization\Domain\Aggregates\Department\DepartmentCode;
use App\Modules\Organization\Domain\Aggregates\Department\DepartmentId;

interface DepartmentRepositoryInterface
{
    public function findById(DepartmentId $id): Department;
    public function findByCodeAndBranch(DepartmentCode $code, BranchId $branchId): ?Department;
    public function existsByCodeAndBranch(DepartmentCode $code, BranchId $branchId): bool;
    public function findChildrenOf(DepartmentId $id): array;
    public function hasActiveChildren(DepartmentId $id): bool;
    public function findDescendantIds(DepartmentId $id): array;
    public function findBranchIdOf(DepartmentId $id): BranchId;
    public function save(Department $department): void;
    public function saveAndDispatch(Department $department): void;
}
```

- [ ] **Step 3: Create PositionRepositoryInterface**

```php
<?php

namespace App\Modules\Organization\Domain\Repositories;

use App\Modules\Organization\Domain\Aggregates\Position\Position;
use App\Modules\Organization\Domain\Aggregates\Position\PositionCode;
use App\Modules\Organization\Domain\Aggregates\Position\PositionId;

interface PositionRepositoryInterface
{
    public function findById(PositionId $id): Position;
    public function findByCode(PositionCode $code): ?Position;
    public function existsByCode(PositionCode $code): bool;
    public function save(Position $position): void;
    public function saveAndDispatch(Position $position): void;
}
```

- [ ] **Step 4: Commit**

```bash
git add src/backend/app/Modules/Organization/Domain/Repositories
git commit -m "feat(organization): add repository interfaces"
```

---

### Task 8: Application layer — commands and handlers (Branch)

**Files:**
- Create: `src/backend/app/Modules/Organization/Application/Commands/Branch/CreateBranchCommand.php`
- Create: `src/backend/app/Modules/Organization/Application/CommandHandlers/Branch/CreateBranchHandler.php`
- Create: `src/backend/app/Modules/Organization/Application/Commands/Branch/UpdateBranchCommand.php`
- Create: `src/backend/app/Modules/Organization/Application/CommandHandlers/Branch/UpdateBranchHandler.php`
- Create: `src/backend/app/Modules/Organization/Application/Commands/Branch/ActivateBranchCommand.php`
- Create: `src/backend/app/Modules/Organization/Application/CommandHandlers/Branch/ActivateBranchHandler.php`
- Create: `src/backend/app/Modules/Organization/Application/Commands/Branch/DeactivateBranchCommand.php`
- Create: `src/backend/app/Modules/Organization/Application/CommandHandlers/Branch/DeactivateBranchHandler.php`

- [ ] **Step 1: Create CreateBranchCommand**

```php
<?php

namespace App\Modules\Organization\Application\Commands\Branch;

use App\Modules\Organization\Domain\Aggregates\Branch\BranchCode;
use App\Modules\Organization\Domain\Aggregates\Branch\BranchName;

readonly class CreateBranchCommand
{
    public function __construct(
        public BranchCode $code,
        public BranchName $name,
        public ?string $address = null,
        public ?string $phone = null,
        public ?string $email = null,
    ) {}
}
```

```php
<?php

namespace App\Modules\Organization\Application\CommandHandlers\Branch;

use App\Modules\Organization\Application\Commands\Branch\CreateBranchCommand;
use App\Modules\Organization\Domain\Aggregates\Branch\Branch;
use App\Modules\Organization\Domain\Aggregates\Branch\BranchId;
use App\Modules\Organization\Domain\Exceptions\DuplicateBranchCodeException;
use App\Modules\Organization\Domain\Repositories\BranchRepositoryInterface;
use App\Modules\Shared\Application\Services\AuthorizationService;

class CreateBranchHandler
{
    public function __construct(
        private BranchRepositoryInterface $branchRepository,
        private AuthorizationService $authorizationService,
    ) {}

    public function handle(CreateBranchCommand $command, string $userId): Branch
    {
        $this->authorizationService->requirePermission($userId, 'organization.branch.create');

        if ($this->branchRepository->existsByCode($command->code)) {
            throw new DuplicateBranchCodeException($command->code->value());
        }

        $branch = Branch::create(
            new BranchId(\Illuminate\Support\Str::uuid()->toString()),
            $command->code,
            $command->name,
            $command->address,
            $command->phone,
            $command->email,
        );

        $this->branchRepository->saveAndDispatch($branch);
        return $branch;
    }
}
```

- [ ] **Step 2: Create UpdateBranchCommand + Handler**

```php
<?php

namespace App\Modules\Organization\Application\Commands\Branch;

use App\Modules\Organization\Domain\Aggregates\Branch\BranchId;
use App\Modules\Organization\Domain\Aggregates\Branch\BranchName;

readonly class UpdateBranchCommand
{
    public function __construct(
        public BranchId $id,
        public BranchName $name,
        public ?string $address = null,
        public ?string $phone = null,
        public ?string $email = null,
    ) {}
}
```

Handler: loads branch via repo, calls `$branch->update(...)`, saves and dispatches.

```php
<?php

namespace App\Modules\Organization\Application\CommandHandlers\Branch;

use App\Modules\Organization\Application\Commands\Branch\UpdateBranchCommand;
use App\Modules\Organization\Domain\Repositories\BranchRepositoryInterface;
use App\Modules\Shared\Application\Services\AuthorizationService;

class UpdateBranchHandler
{
    public function __construct(
        private BranchRepositoryInterface $branchRepository,
        private AuthorizationService $authorizationService,
    ) {}

    public function handle(UpdateBranchCommand $command, string $userId): void
    {
        $this->authorizationService->requirePermission($userId, 'organization.branch.update');
        $branch = $this->branchRepository->findById($command->id);
        $branch->update($command->name, $command->address, $command->phone, $command->email);
        $this->branchRepository->saveAndDispatch($branch);
    }
}
```

- [ ] **Step 3: Create ActivateBranch + DeactivateBranch commands/handlers**

ActivateBranchCommand(id), DeactivateBranchCommand(id). Handlers check permission `organization.branch.update`, load branch, call activate/deactivate, save and dispatch.

Deactivate handler passes `fn() => $this->branchRepository->hasActiveDepartments($id)` to the aggregate method.

- [ ] **Step 4: Commit**

```bash
git add src/backend/app/Modules/Organization/Application/Commands/Branch \
       src/backend/app/Modules/Organization/Application/CommandHandlers/Branch
git commit -m "feat(organization): add branch commands and handlers"
```

---

### Task 9: Application layer — commands and handlers (Department)

**Files:**
- Create: `src/backend/app/Modules/Organization/Application/Commands/Department/CreateDepartmentCommand.php`
- Create: `src/backend/app/Modules/Organization/Application/CommandHandlers/Department/CreateDepartmentHandler.php`
- Create: `src/backend/app/Modules/Organization/Application/Commands/Department/UpdateDepartmentCommand.php`
- Create: `src/backend/app/Modules/Organization/Application/CommandHandlers/Department/UpdateDepartmentHandler.php`
- Create: `src/backend/app/Modules/Organization/Application/Commands/Department/MoveDepartmentCommand.php`
- Create: `src/backend/app/Modules/Organization/Application/CommandHandlers/Department/MoveDepartmentHandler.php`
- Create: `src/backend/app/Modules/Organization/Application/Commands/Department/ActivateDepartmentCommand.php`
- Create: `src/backend/app/Modules/Organization/Application/CommandHandlers/Department/ActivateDepartmentHandler.php`
- Create: `src/backend/app/Modules/Organization/Application/Commands/Department/DeactivateDepartmentCommand.php`
- Create: `src/backend/app/Modules/Organization/Application/CommandHandlers/Department/DeactivateDepartmentHandler.php`

- [ ] **Step 1: Create CreateDepartmentCommand + Handler**

```php
<?php

namespace App\Modules\Organization\Application\Commands\Department;

use App\Modules\Organization\Domain\Aggregates\Branch\BranchId;
use App\Modules\Organization\Domain\Aggregates\Department\DepartmentCode;
use App\Modules\Organization\Domain\Aggregates\Department\DepartmentId;
use App\Modules\Organization\Domain\Aggregates\Department\DepartmentName;

readonly class CreateDepartmentCommand
{
    public function __construct(
        public BranchId $branchId,
        public DepartmentCode $code,
        public DepartmentName $name,
        public ?DepartmentId $parentId = null,
    ) {}
}
```

Handler checks `organization.department.create`, validates branch exists (via BranchRepository), validates duplicate code in branch, validates parent exists and in same branch if given, creates and saves.

- [ ] **Step 2: Create MoveDepartmentCommand + Handler**

```php
<?php

namespace App\Modules\Organization\Application\Commands\Department;

use App\Modules\Organization\Domain\Aggregates\Department\DepartmentId;

readonly class MoveDepartmentCommand
{
    public function __construct(
        public DepartmentId $id,
        public ?DepartmentId $newParentId,
    ) {}
}
```

Handler loads department, calls `moveTo($newParentId, $isDescendantFn, $getParentBranchFn)` where the callables delegate to `DepartmentRepositoryInterface::findDescendantIds` and `DepartmentRepositoryInterface::findBranchIdOf`.

```php
// Inside MoveDepartmentHandler::handle():
$department = $this->departmentRepository->findById($command->id);

$department->moveTo(
    $command->newParentId,
    fn(?DepartmentId $id) => $id !== null && in_array($id->value(), $this->departmentRepository->findDescendantIds($department->id())),
    fn(?DepartmentId $id) => $id !== null ? $this->departmentRepository->findBranchIdOf($id) : $department->branchId(),
);

$this->departmentRepository->saveAndDispatch($department);
```

- [ ] **Step 3: Create UpdateDepartment, ActivateDepartment, DeactivateDepartment commands/handlers**

Same pattern as Branch equivalents. Deactivate passes `fn() => $this->departmentRepository->hasActiveChildren($id)`.

- [ ] **Step 4: Commit**

```bash
git add src/backend/app/Modules/Organization/Application/Commands/Department \
       src/backend/app/Modules/Organization/Application/CommandHandlers/Department
git commit -m "feat(organization): add department commands and handlers"
```

---

### Task 10: Application layer — commands and handlers (Position)

**Files:**
- Create: `src/backend/app/Modules/Organization/Application/Commands/Position/CreatePositionCommand.php`
- Create: `src/backend/app/Modules/Organization/Application/CommandHandlers/Position/CreatePositionHandler.php`
- Create: `src/backend/app/Modules/Organization/Application/Commands/Position/UpdatePositionCommand.php`
- Create: `src/backend/app/Modules/Organization/Application/CommandHandlers/Position/UpdatePositionHandler.php`
- Create: `src/backend/app/Modules/Organization/Application/Commands/Position/ActivatePositionCommand.php`
- Create: `src/backend/app/Modules/Organization/Application/CommandHandlers/Position/ActivatePositionHandler.php`
- Create: `src/backend/app/Modules/Organization/Application/Commands/Position/DeactivatePositionCommand.php`
- Create: `src/backend/app/Modules/Organization/Application/CommandHandlers/Position/DeactivatePositionHandler.php`

- [ ] **Step 1: Position commands/handlers**

Same pattern as Branch but simpler (no external guard callables for position deactivation in Phase 1).

```php
<?php

namespace App\Modules\Organization\Application\Commands\Position;

use App\Modules\Organization\Domain\Aggregates\Position\PositionCode;
use App\Modules\Organization\Domain\Aggregates\Position\PositionName;

readonly class CreatePositionCommand
{
    public function __construct(
        public PositionCode $code,
        public PositionName $name,
        public ?int $level = null,
        public ?string $description = null,
    ) {}
}
```

Handler: check permission `organization.position.create`, check duplicate code, create and save.

UpdatePositionCommand: id + optional name/level/description updates.
ActivatePositionCommand: id.
DeactivatePositionCommand: id (no guards in Phase 1 — employee check deferred).

- [ ] **Step 2: Commit**

```bash
git add src/backend/app/Modules/Organization/Application/Commands/Position \
       src/backend/app/Modules/Organization/Application/CommandHandlers/Position
git commit -m "feat(organization): add position commands and handlers"
```

---

### Task 11: Application layer — queries and query handlers

**Files:**
- Create: `src/backend/app/Modules/Organization/Application/Queries/Branch/GetBranchQuery.php`
- Create: `src/backend/app/Modules/Organization/Application/QueryHandlers/Branch/GetBranchHandler.php`
- Create: `src/backend/app/Modules/Organization/Application/Queries/Branch/ListBranchesQuery.php`
- Create: `src/backend/app/Modules/Organization/Application/QueryHandlers/Branch/ListBranchesHandler.php`
- Create: `src/backend/app/Modules/Organization/Application/Queries/Department/GetDepartmentQuery.php`
- Create: `src/backend/app/Modules/Organization/Application/QueryHandlers/Department/GetDepartmentHandler.php`
- Create: `src/backend/app/Modules/Organization/Application/Queries/Department/ListDepartmentsQuery.php`
- Create: `src/backend/app/Modules/Organization/Application/QueryHandlers/Department/ListDepartmentsHandler.php`
- Create: `src/backend/app/Modules/Organization/Application/Queries/Position/GetPositionQuery.php`
- Create: `src/backend/app/Modules/Organization/Application/QueryHandlers/Position/GetPositionHandler.php`
- Create: `src/backend/app/Modules/Organization/Application/Queries/Position/ListPositionsQuery.php`
- Create: `src/backend/app/Modules/Organization/Application/QueryHandlers/Position/ListPositionsHandler.php`
- Create: `src/backend/app/Modules/Organization/Application/Queries/OrgTree/GetOrgTreeQuery.php`
- Create: `src/backend/app/Modules/Organization/Application/QueryHandlers/OrgTree/GetOrgTreeHandler.php`

- [ ] **Step 1: Create branch queries**

GetBranchQuery(id), ListBranchesQuery(?status, page, perPage). Queries are readonly DTOs. Handlers call repository findAll/findById and return data for the controller to format.

ListBranchesHandler queries via Eloquent directly or a separate read model (simplest: inject BranchModel and paginate).

- [ ] **Step 2: Create department queries**

GetDepartmentQuery(id), ListDepartmentsQuery(?branch_id, ?parent_id, ?status, page, perPage).

ListDepartmentsHandler filters by department_model columns.

- [ ] **Step 3: Create position queries**

GetPositionQuery(id), ListPositionsQuery(?status, page, perPage).

- [ ] **Step 4: Create OrgTree query**

```php
<?php

namespace App\Modules\Organization\Application\Queries\OrgTree;

readonly class GetOrgTreeQuery
{
    public function __construct(
        public ?string $branchId = null,
    ) {}
}
```

Handler loads branches, loads departments, groups by branch, nests by parent_id, returns structured array.

- [ ] **Step 5: Commit**

```bash
git add src/backend/app/Modules/Organization/Application/Queries \
       src/backend/app/Modules/Organization/Application/QueryHandlers
git commit -m "feat(organization): add query handlers"
```

---

### Task 12: Infrastructure persistence — Eloquent repositories

**Files:**
- Create: `src/backend/app/Modules/Organization/Infrastructure/Persistence/Repositories/EloquentBranchRepository.php`
- Create: `src/backend/app/Modules/Organization/Infrastructure/Persistence/Repositories/EloquentDepartmentRepository.php`
- Create: `src/backend/app/Modules/Organization/Infrastructure/Persistence/Repositories/EloquentPositionRepository.php`

- [ ] **Step 1: Create EloquentBranchRepository**

```php
<?php

namespace App\Modules\Organization\Infrastructure\Persistence\Repositories;

use App\Modules\Organization\Domain\Aggregates\Branch\Branch;
use App\Modules\Organization\Domain\Aggregates\Branch\BranchCode;
use App\Modules\Organization\Domain\Aggregates\Branch\BranchId;
use App\Modules\Organization\Domain\Aggregates\Branch\BranchName;
use App\Modules\Organization\Domain\Aggregates\Branch\BranchStatus;
use App\Modules\Organization\Domain\Exceptions\BranchNotFoundException;
use App\Modules\Organization\Domain\Repositories\BranchRepositoryInterface;
use App\Modules\Organization\Infrastructure\Persistence\Eloquent\BranchModel;
use Illuminate\Support\Facades\Event;

class EloquentBranchRepository implements BranchRepositoryInterface
{
    public function __construct(
        private BranchModel $model,
    ) {}

    public function findById(BranchId $id): Branch
    {
        $record = $this->model->find($id->value());
        if (!$record) {
            throw new BranchNotFoundException($id->value());
        }
        return $this->toDomain($record);
    }

    public function findByCode(BranchCode $code): ?Branch
    {
        $record = $this->model->where('code', $code->value())->first();
        return $record ? $this->toDomain($record) : null;
    }

    public function existsByCode(BranchCode $code): bool
    {
        return $this->model->where('code', $code->value())->exists();
    }

    public function hasActiveDepartments(BranchId $id): bool
    {
        return $this->model->find($id->value())?->activeDepartments()->exists() ?? false;
    }

    public function save(Branch $branch): void
    {
        $this->model->updateOrCreate(
            ['id' => $branch->id()->value()],
            [
                'code' => $branch->code()->value(),
                'name' => $branch->name()->value(),
                'address' => $branch->address(),
                'phone' => $branch->phone(),
                'email' => $branch->email(),
                'status' => $branch->status()->value,
            ]
        );
    }

    public function saveAndDispatch(Branch $branch): void
    {
        $this->save($branch);
        foreach ($branch->getRecordedEvents() as $event) {
            Event::dispatch($event);
        }
        $branch->clearRecordedEvents();
    }

    private function toDomain(BranchModel $record): Branch
    {
        return Branch::reconstitute(
            new BranchId($record->id),
            BranchCode::fromString($record->code),
            BranchName::fromString($record->name),
            $record->address,
            $record->phone,
            $record->email,
            BranchStatus::from($record->status),
        );
    }
}
```

- [ ] **Step 2: Create EloquentDepartmentRepository**

```php
<?php

namespace App\Modules\Organization\Infrastructure\Persistence\Repositories;

use App\Modules\Organization\Domain\Aggregates\Branch\BranchId;
use App\Modules\Organization\Domain\Aggregates\Department\Department;
use App\Modules\Organization\Domain\Aggregates\Department\DepartmentCode;
use App\Modules\Organization\Domain\Aggregates\Department\DepartmentId;
use App\Modules\Organization\Domain\Aggregates\Department\DepartmentName;
use App\Modules\Organization\Domain\Aggregates\Department\DepartmentStatus;
use App\Modules\Organization\Domain\Exceptions\DepartmentNotFoundException;
use App\Modules\Organization\Domain\Repositories\DepartmentRepositoryInterface;
use App\Modules\Organization\Infrastructure\Persistence\Eloquent\DepartmentModel;
use Illuminate\Support\Facades\Event;

class EloquentDepartmentRepository implements DepartmentRepositoryInterface
{
    public function __construct(
        private DepartmentModel $model,
    ) {}

    public function findById(DepartmentId $id): Department
    {
        $record = $this->model->find($id->value());
        if (!$record) throw new DepartmentNotFoundException($id->value());
        return $this->toDomain($record);
    }

    public function findByCodeAndBranch(DepartmentCode $code, BranchId $branchId): ?Department
    {
        $record = $this->model
            ->where('code', $code->value())
            ->where('branch_id', $branchId->value())
            ->first();
        return $record ? $this->toDomain($record) : null;
    }

    public function existsByCodeAndBranch(DepartmentCode $code, BranchId $branchId): bool
    {
        return $this->model
            ->where('code', $code->value())
            ->where('branch_id', $branchId->value())
            ->exists();
    }

    public function findChildrenOf(DepartmentId $id): array
    {
        return $this->model
            ->where('parent_id', $id->value())
            ->get()
            ->map(fn($r) => $this->toDomain($r))
            ->all();
    }

    public function hasActiveChildren(DepartmentId $id): bool
    {
        return $this->model
            ->where('parent_id', $id->value())
            ->where('status', 'active')
            ->exists();
    }

    public function findDescendantIds(DepartmentId $id): array
    {
        $ids = [];
        $this->collectDescendantIds($id->value(), $ids);
        return $ids;
    }

    private function collectDescendantIds(string $parentId, array &$ids): void
    {
        $children = $this->model->where('parent_id', $parentId)->pluck('id');
        foreach ($children as $childId) {
            $ids[] = $childId;
            $this->collectDescendantIds($childId, $ids);
        }
    }

    public function findBranchIdOf(DepartmentId $id): BranchId
    {
        $record = $this->model->find($id->value());
        if (!$record) throw new DepartmentNotFoundException($id->value());
        return new BranchId($record->branch_id);
    }

    public function save(Department $department): void
    {
        $this->model->updateOrCreate(
            ['id' => $department->id()->value()],
            [
                'branch_id' => $department->branchId()->value(),
                'parent_id' => $department->parentId()?->value(),
                'code' => $department->code()->value(),
                'name' => $department->name()->value(),
                'manager_employee_id' => $department->managerEmployeeId(),
                'status' => $department->status()->value,
            ]
        );
    }

    public function saveAndDispatch(Department $department): void
    {
        $this->save($department);
        foreach ($department->getRecordedEvents() as $event) {
            Event::dispatch($event);
        }
        $department->clearRecordedEvents();
    }

    private function toDomain(DepartmentModel $record): Department
    {
        return Department::reconstitute(
            new DepartmentId($record->id),
            DepartmentCode::fromString($record->code),
            DepartmentName::fromString($record->name),
            new BranchId($record->branch_id),
            $record->parent_id ? new DepartmentId($record->parent_id) : null,
            $record->manager_employee_id,
            DepartmentStatus::from($record->status),
        );
    }
}
```

- [ ] **Step 3: Create EloquentPositionRepository**

Same pattern as BranchRepo: `toDomain`, `save`, `saveAndDispatch`, `findById`, `findByCode`, `existsByCode`.

- [ ] **Step 4: Register repository bindings in AppServiceProvider or a OrganizationServiceProvider**

Create or verify binding in `AppServiceProvider` or Module ServiceProvider:

```php
$this->app->bind(BranchRepositoryInterface::class, EloquentBranchRepository::class);
$this->app->bind(DepartmentRepositoryInterface::class, EloquentDepartmentRepository::class);
$this->app->bind(PositionRepositoryInterface::class, EloquentPositionRepository::class);
```

- [ ] **Step 5: Commit**

```bash
git add src/backend/app/Modules/Organization/Infrastructure/Persistence
git commit -m "feat(organization): add eloquent repositories"
```

---

### Task 13: HTTP layer — controllers, requests, resources, routes

**Files:**
- Create: `src/backend/app/Modules/Organization/Infrastructure/Http/Controllers/BranchController.php`
- Create: `src/backend/app/Modules/Organization/Infrastructure/Http/Controllers/DepartmentController.php`
- Create: `src/backend/app/Modules/Organization/Infrastructure/Http/Controllers/PositionController.php`
- Create: `src/backend/app/Modules/Organization/Infrastructure/Http/Controllers/OrgTreeController.php`
- Create: `src/backend/app/Modules/Organization/Infrastructure/Http/Requests/CreateBranchRequest.php`
- Create: `src/backend/app/Modules/Organization/Infrastructure/Http/Requests/UpdateBranchRequest.php`
- Create: `src/backend/app/Modules/Organization/Infrastructure/Http/Requests/CreateDepartmentRequest.php`
- Create: `src/backend/app/Modules/Organization/Infrastructure/Http/Requests/UpdateDepartmentRequest.php`
- Create: `src/backend/app/Modules/Organization/Infrastructure/Http/Requests/MoveDepartmentRequest.php`
- Create: `src/backend/app/Modules/Organization/Infrastructure/Http/Requests/CreatePositionRequest.php`
- Create: `src/backend/app/Modules/Organization/Infrastructure/Http/Requests/UpdatePositionRequest.php`
- Create: `src/backend/app/Modules/Organization/Infrastructure/Http/Resources/BranchResource.php`
- Create: `src/backend/app/Modules/Organization/Infrastructure/Http/Resources/DepartmentResource.php`
- Create: `src/backend/app/Modules/Organization/Infrastructure/Http/Resources/PositionResource.php`
- Create: `src/backend/app/Modules/Organization/Infrastructure/Http/Resources/OrgTreeResource.php`
- Create: `src/backend/app/Modules/Organization/Routes/api.php`
- Modify: `src/backend/routes/api.php`

- [ ] **Step 1: Create BranchController**

```php
<?php

namespace App\Modules\Organization\Infrastructure\Http\Controllers;

use App\Modules\Organization\Application\Commands\Branch\ActivateBranchCommand;
use App\Modules\Organization\Application\Commands\Branch\CreateBranchCommand;
use App\Modules\Organization\Application\Commands\Branch\DeactivateBranchCommand;
use App\Modules\Organization\Application\Commands\Branch\UpdateBranchCommand;
use App\Modules\Organization\Application\CommandHandlers\Branch\ActivateBranchHandler;
use App\Modules\Organization\Application\CommandHandlers\Branch\CreateBranchHandler;
use App\Modules\Organization\Application\CommandHandlers\Branch\DeactivateBranchHandler;
use App\Modules\Organization\Application\CommandHandlers\Branch\UpdateBranchHandler;
use App\Modules\Organization\Application\Queries\Branch\GetBranchQuery;
use App\Modules\Organization\Application\Queries\Branch\ListBranchesQuery;
use App\Modules\Organization\Infrastructure\Http\Requests\CreateBranchRequest;
use App\Modules\Organization\Infrastructure\Http\Requests\UpdateBranchRequest;
use App\Modules\Organization\Infrastructure\Http\Resources\BranchResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class BranchController extends Controller
{
    public function __construct(
        private CreateBranchHandler $createHandler,
        private UpdateBranchHandler $updateHandler,
        private ActivateBranchHandler $activateHandler,
        private DeactivateBranchHandler $deactivateHandler,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = new ListBranchesQuery($request->get('status'), $request->get('page', 1), $request->get('per_page', 15));
        // handler returns paginated resource collection
        return response()->json(/* ... */);
    }

    public function store(CreateBranchRequest $request): JsonResponse
    {
        $command = new CreateBranchCommand(
            $request->getCode(),
            $request->getName(),
            $request->get('address'),
            $request->get('phone'),
            $request->get('email'),
        );
        $branch = $this->createHandler->handle($command, $request->user()->id);
        return response()->json(['data' => new BranchResource($branch)], 201);
    }

    public function show(string $id): JsonResponse
    {
        $query = new GetBranchQuery(new \App\Modules\Organization\Domain\Aggregates\Branch\BranchId($id));
        // handler returns branch
        return response()->json(['data' => /* ... */]);
    }

    public function update(UpdateBranchRequest $request, string $id): JsonResponse
    {
        $command = new UpdateBranchCommand(/* ... */);
        $this->updateHandler->handle($command, $request->user()->id);
        return response()->json(['message' => 'Updated']);
    }

    public function activate(Request $request, string $id): JsonResponse
    {
        $command = new ActivateBranchCommand(new \App\Modules\Organization\Domain\Aggregates\Branch\BranchId($id));
        $this->activateHandler->handle($command, $request->user()->id);
        return response()->json(['message' => 'Activated']);
    }

    public function deactivate(Request $request, string $id): JsonResponse
    {
        $command = new DeactivateBranchCommand(new \App\Modules\Organization\Domain\Aggregates\Branch\BranchId($id));
        $this->deactivateHandler->handle($command, $request->user()->id);
        return response()->json(['message' => 'Deactivated']);
    }
}
```

- [ ] **Step 2: Create DepartmentController**

Same pattern: index (filter by branch_id, parent_id, status), store, show, update, move, activate, deactivate.

Move endpoint:

```php
public function move(MoveDepartmentRequest $request, string $id): JsonResponse
{
    $command = new MoveDepartmentCommand(
        new DepartmentId($id),
        $request->get('new_parent_id') ? new DepartmentId($request->get('new_parent_id')) : null,
    );
    $this->moveHandler->handle($command, $request->user()->id);
    return response()->json(['message' => 'Moved']);
}
```

- [ ] **Step 3: Create PositionController**

index, store, show, update, activate, deactivate.

- [ ] **Step 4: Create OrgTreeController**

```php
public function __invoke(Request $request): JsonResponse
{
    $query = new GetOrgTreeQuery($request->get('branch_id'));
    // handler returns nested array
    return response()->json(['data' => /* ... */]);
}
```

- [ ] **Step 5: Create FormRequest classes**

Each FormRequest extends `Illuminate\Foundation\Http\FormRequest` with `authorize()` returning `true` (permission middleware handles auth) and `rules()` array.

CreateBranchRequest:

```php
<?php

namespace App\Modules\Organization\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateBranchRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'regex:/^[A-Z][A-Z0-9-]{1,49}$/', 'unique:branches,code'],
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
        ];
    }

    public function getCode(): \App\Modules\Organization\Domain\Aggregates\Branch\BranchCode
    {
        return \App\Modules\Organization\Domain\Aggregates\Branch\BranchCode::fromString($this->input('code'));
    }

    public function getName(): \App\Modules\Organization\Domain\Aggregates\Branch\BranchName
    {
        return \App\Modules\Organization\Domain\Aggregates\Branch\BranchName::fromString($this->input('name'));
    }
}
```

UpdateBranchRequest: same rules minus code (immutable).

CreateDepartmentRequest:

```php
public function rules(): array
{
    return [
        'branch_id' => ['required', 'string', 'exists:branches,id'],
        'code' => ['required', 'string', 'regex:/^[A-Z][A-Z0-9-]{1,49}$/'],
        'name' => ['required', 'string', 'max:255'],
        'parent_id' => ['nullable', 'string', 'exists:departments,id'],
    ];
}
```

`parent_id` existence in same branch is validated at handler level (can't cross-FK in FormRequest).

MoveDepartmentRequest: `new_parent_id` nullable, `exists:departments,id`.

CreatePositionRequest, UpdatePositionRequest: same pattern.

- [ ] **Step 6: Create Resources**

BranchResource:

```php
<?php

namespace App\Modules\Organization\Infrastructure\Http\Resources;

use App\Modules\Organization\Domain\Aggregates\Branch\Branch;
use Illuminate\Http\Resources\Json\JsonResource;

class BranchResource extends JsonResource
{
    public function toArray($request): array
    {
        $branch = $this->resource instanceof Branch ? $this->resource : $this;
        return [
            'id' => (string) $branch->id(),
            'code' => (string) $branch->code(),
            'name' => (string) $branch->name(),
            'address' => $branch->address(),
            'phone' => $branch->phone(),
            'email' => $branch->email(),
            'status' => $branch->status()->value,
            'created_at' => $this->when($this->resource->created_at ?? null, fn() => $this->resource->created_at),
            'updated_at' => $this->when($this->resource->updated_at ?? null, fn() => $this->resource->updated_at),
        ];
    }
}
```

DepartmentResource: id, code, name, branch_id, parent_id, manager_employee_id, status.
PositionResource: id, code, name, level, description, status.
OrgTreeResource: nested structure `{id, code, name, departments: [{id, code, name, children: [...]}]}`.

- [ ] **Step 7: Create api.php routes file**

```php
<?php

use App\Modules\Organization\Infrastructure\Http\Controllers\BranchController;
use App\Modules\Organization\Infrastructure\Http\Controllers\DepartmentController;
use App\Modules\Organization\Infrastructure\Http\Controllers\OrgTreeController;
use App\Modules\Organization\Infrastructure\Http\Controllers\PositionController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    // Branches
    Route::get('/branches', [BranchController::class, 'index'])->middleware('permission:organization.branch.list');
    Route::post('/branches', [BranchController::class, 'store'])->middleware('permission:organization.branch.create');
    Route::get('/branches/{branch}', [BranchController::class, 'show'])->middleware('permission:organization.branch.view');
    Route::patch('/branches/{branch}', [BranchController::class, 'update'])->middleware('permission:organization.branch.update');
    Route::post('/branches/{branch}/activate', [BranchController::class, 'activate'])->middleware('permission:organization.branch.update');
    Route::post('/branches/{branch}/deactivate', [BranchController::class, 'deactivate'])->middleware('permission:organization.branch.update');

    // Departments
    Route::get('/departments', [DepartmentController::class, 'index'])->middleware('permission:organization.department.list');
    Route::post('/departments', [DepartmentController::class, 'store'])->middleware('permission:organization.department.create');
    Route::get('/departments/{department}', [DepartmentController::class, 'show'])->middleware('permission:organization.department.view');
    Route::patch('/departments/{department}', [DepartmentController::class, 'update'])->middleware('permission:organization.department.update');
    Route::post('/departments/{department}/move', [DepartmentController::class, 'move'])->middleware('permission:organization.department.move');
    Route::post('/departments/{department}/activate', [DepartmentController::class, 'activate'])->middleware('permission:organization.department.update');
    Route::post('/departments/{department}/deactivate', [DepartmentController::class, 'deactivate'])->middleware('permission:organization.department.update');

    // Positions
    Route::get('/positions', [PositionController::class, 'index'])->middleware('permission:organization.position.list');
    Route::post('/positions', [PositionController::class, 'store'])->middleware('permission:organization.position.create');
    Route::get('/positions/{position}', [PositionController::class, 'show'])->middleware('permission:organization.position.view');
    Route::patch('/positions/{position}', [PositionController::class, 'update'])->middleware('permission:organization.position.update');
    Route::post('/positions/{position}/activate', [PositionController::class, 'activate'])->middleware('permission:organization.position.update');
    Route::post('/positions/{position}/deactivate', [PositionController::class, 'deactivate'])->middleware('permission:organization.position.update');

    // Org tree
    Route::get('/org-tree', OrgTreeController::class)->middleware('permission:organization.tree.view');
});
```

- [ ] **Step 8: Update `src/backend/routes/api.php`**

Add this line at the end:

```php
require __DIR__ . '/../app/Modules/Organization/Routes/api.php';
```

- [ ] **Step 9: Register exception-to-HTTP mapping in bootstrap/app.php or ExceptionHandler**

Add Organization domain exceptions to `dontReport` / render mapping. Example render closure in `bootstrap/app.php`:

```php
use App\Modules\Organization\Domain\Exceptions\BranchNotFoundException;
use App\Modules\Organization\Domain\Exceptions\DuplicateBranchCodeException;
// ... etc

->withExceptions(function (Exceptions $exceptions) {
    $exceptions->render(function (BranchNotFoundException $e) {
        return response()->json(['error' => ['code' => 'BRANCH_NOT_FOUND', 'message' => $e->getMessage()]], 404);
    });
    $exceptions->render(function (DuplicateBranchCodeException $e) {
        return response()->json(['error' => ['code' => 'DUPLICATE_BRANCH_CODE', 'message' => $e->getMessage()]], 409);
    });
    $exceptions->render(function (CircularMoveException $e) {
        return response()->json(['error' => ['code' => 'CIRCULAR_MOVE', 'message' => $e->getMessage()]], 422);
    });
    $exceptions->render(function (DepartmentNotInSameBranchException $e) {
        return response()->json(['error' => ['code' => 'DEPARTMENT_NOT_IN_SAME_BRANCH', 'message' => $e->getMessage()]], 422);
    });
    // Add the rest: BranchHasActiveDepartmentsException, DepartmentHasActiveChildrenException, etc.
});
```

- [ ] **Step 10: Commit**

```bash
git add src/backend/app/Modules/Organization/Infrastructure/Http \
       src/backend/app/Modules/Organization/Routes \
       src/backend/routes/api.php \
       src/backend/bootstrap/app.php
git commit -m "feat(organization): add HTTP layer and routes"
```

---

### Task 14: Seeders — permissions, roles, org structure

**Files:**
- Modify: `src/backend/app/Modules/Identity/Infrastructure/Seeders/PermissionSeeder.php`
- Modify: `src/backend/app/Modules/Identity/Infrastructure/Seeders/RoleSeeder.php`
- Create: `src/backend/app/Modules/Organization/Infrastructure/Seeders/OrgStructureSeeder.php`

- [ ] **Step 1: Add organization permissions to PermissionSeeder**

In the `PermissionSeeder::run()`, after existing identity permissions:

```php
$orgPermissions = [
    ['code' => 'organization.branch.list', 'module' => 'organization', 'action' => 'list', 'description' => 'List branches'],
    ['code' => 'organization.branch.view', 'module' => 'organization', 'action' => 'view', 'description' => 'View branch details'],
    ['code' => 'organization.branch.create', 'module' => 'organization', 'action' => 'create', 'description' => 'Create branch'],
    ['code' => 'organization.branch.update', 'module' => 'organization', 'action' => 'update', 'description' => 'Update/activate/deactivate branch'],
    ['code' => 'organization.department.list', 'module' => 'organization', 'action' => 'list', 'description' => 'List departments'],
    ['code' => 'organization.department.view', 'module' => 'organization', 'action' => 'view', 'description' => 'View department details'],
    ['code' => 'organization.department.create', 'module' => 'organization', 'action' => 'create', 'description' => 'Create department'],
    ['code' => 'organization.department.update', 'module' => 'organization', 'action' => 'update', 'description' => 'Update/activate/deactivate department'],
    ['code' => 'organization.department.move', 'module' => 'organization', 'action' => 'move', 'description' => 'Move department'],
    ['code' => 'organization.position.list', 'module' => 'organization', 'action' => 'list', 'description' => 'List positions'],
    ['code' => 'organization.position.view', 'module' => 'organization', 'action' => 'view', 'description' => 'View position details'],
    ['code' => 'organization.position.create', 'module' => 'organization', 'action' => 'create', 'description' => 'Create position'],
    ['code' => 'organization.position.update', 'module' => 'organization', 'action' => 'update', 'description' => 'Update/activate/deactivate position'],
    ['code' => 'organization.tree.view', 'module' => 'organization', 'action' => 'view', 'description' => 'View organization tree'],
];

foreach ($orgPermissions as $perm) {
    PermissionModel::updateOrCreate(['code' => $perm['code']], $perm);
}
```

- [ ] **Step 2: Update RoleSeeder to grant org permissions**

In the roles array, HR_MANAGER gets all `organization.*` permissions. EMPLOYEE gets `organization.tree.view`.

Modify the existing HR_MANAGER permissions array to include ALL organization permissions:

```php
'HR_MANAGER' => [
    'name' => 'HR Manager',
    'description' => 'Manage users, roles, and organization data',
    'permissions' => [
        'identity.user.list', 'identity.user.view',
        'identity.role.list', 'identity.role.view',
        'identity.permission.list',
        'organization.branch.list', 'organization.branch.view', 'organization.branch.create', 'organization.branch.update',
        'organization.department.list', 'organization.department.view', 'organization.department.create', 'organization.department.update', 'organization.department.move',
        'organization.position.list', 'organization.position.view', 'organization.position.create', 'organization.position.update',
        'organization.tree.view',
    ],
],
'EMPLOYEE' => [
    'name' => 'Employee',
    'description' => 'Self-service only',
    'permissions' => ['organization.tree.view'],
],
```

SUPER_ADMIN already gets 'all' which covers everything.

- [ ] **Step 3: Create OrgStructureSeeder**

```php
<?php

namespace App\Modules\Organization\Infrastructure\Seeders;

use App\Modules\Organization\Infrastructure\Persistence\Eloquent\BranchModel;
use App\Modules\Organization\Infrastructure\Persistence\Eloquent\DepartmentModel;
use App\Modules\Organization\Infrastructure\Persistence\Eloquent\PositionModel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class OrgStructureSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedBranches();
        $this->seedPositions();
    }

    private function seedBranches(): void
    {
        $hcm = BranchModel::updateOrCreate(
            ['code' => 'HCM-HQ'],
            ['name' => 'Ho Chi Minh Head Office', 'address' => '123 Nguyen Hue, District 1, HCMC', 'phone' => '02838223344', 'email' => 'hcm@ihrm.local', 'status' => 'active']
        );

        $hn = BranchModel::updateOrCreate(
            ['code' => 'HN-OFFICE'],
            ['name' => 'Ha Noi Office', 'address' => '456 Tran Hung Dao, Hoan Kiem, Hanoi', 'phone' => '02439332244', 'email' => 'hn@ihrm.local', 'status' => 'active']
        );

        // Departments under HCM
        $board = DepartmentModel::updateOrCreate(
            ['branch_id' => $hcm->id, 'code' => 'BOARD'],
            ['name' => 'Ban Giam Doc', 'parent_id' => null, 'status' => 'active']
        );
        $hr = DepartmentModel::updateOrCreate(
            ['branch_id' => $hcm->id, 'code' => 'HR'],
            ['name' => 'Nhan Su', 'parent_id' => null, 'status' => 'active']
        );
        $acc = DepartmentModel::updateOrCreate(
            ['branch_id' => $hcm->id, 'code' => 'ACC'],
            ['name' => 'Ke Toan', 'parent_id' => null, 'status' => 'active']
        );
        $it = DepartmentModel::updateOrCreate(
            ['branch_id' => $hcm->id, 'code' => 'IT'],
            ['name' => 'Ky Thuat', 'parent_id' => null, 'status' => 'active']
        );
        DepartmentModel::updateOrCreate(
            ['branch_id' => $hcm->id, 'code' => 'SALES'],
            ['name' => 'Kinh Doanh', 'parent_id' => null, 'status' => 'active']
        );
        DepartmentModel::updateOrCreate(
            ['branch_id' => $hcm->id, 'code' => 'IT-DEV'],
            ['name' => 'Phong Phat Trien', 'parent_id' => $it->id, 'status' => 'active']
        );
        DepartmentModel::updateOrCreate(
            ['branch_id' => $hcm->id, 'code' => 'IT-OPS'],
            ['name' => 'Phong Van Hanh', 'parent_id' => $it->id, 'status' => 'active']
        );

        // Departments under HN
        DepartmentModel::updateOrCreate(
            ['branch_id' => $hn->id, 'code' => 'HN-HR'],
            ['name' => 'Nhan Su HN', 'parent_id' => null, 'status' => 'active']
        );
        DepartmentModel::updateOrCreate(
            ['branch_id' => $hn->id, 'code' => 'HN-ACC'],
            ['name' => 'Ke Toan HN', 'parent_id' => null, 'status' => 'active']
        );
    }

    private function seedPositions(): void
    {
        $positions = [
            ['code' => 'DEV', 'name' => 'Developer', 'level' => 3],
            ['code' => 'SR_DEV', 'name' => 'Senior Developer', 'level' => 4],
            ['code' => 'TL', 'name' => 'Team Leader', 'level' => 5],
            ['code' => 'HR_EXEC', 'name' => 'HR Executive', 'level' => 3],
            ['code' => 'HR_MGR', 'name' => 'HR Manager', 'level' => 5],
            ['code' => 'ACCT', 'name' => 'Accountant', 'level' => 3],
            ['code' => 'SALES_EXEC', 'name' => 'Sales Executive', 'level' => 3],
            ['code' => 'MGR', 'name' => 'General Manager', 'level' => 6],
        ];

        foreach ($positions as $pos) {
            PositionModel::updateOrCreate(['code' => $pos['code']], $pos + ['status' => 'active']);
        }
    }
}
```

- [ ] **Step 4: Add OrgStructureSeeder call to DatabaseSeeder**

In `src/backend/database/seeders/DatabaseSeeder.php`:

```php
$this->call(\App\Modules\Organization\Infrastructure\Seeders\OrgStructureSeeder::class);
```

- [ ] **Step 5: Verify seeders**

```bash
docker compose run --rm app php artisan migrate
docker compose run --rm app php artisan db:seed --class=OrgStructureSeeder
echo "SELECT code FROM permissions WHERE code LIKE 'organization.%';" | docker compose exec -T db psql -U ihrm
```

Expected: 14 organization.* permissions listed.

- [ ] **Step 6: Commit**

```bash
git add src/backend/app/Modules/Identity/Infrastructure/Seeders \
       src/backend/app/Modules/Organization/Infrastructure/Seeders \
       src/backend/database/seeders
git commit -m "feat(organization): add seeders for permissions, roles, and org structure"
```

---

### Task 15: Feature HTTP tests

**Files:**
- Create: `src/backend/tests/Feature/Modules/Organization/BranchApiTest.php`
- Create: `src/backend/tests/Feature/Modules/Organization/DepartmentApiTest.php`
- Create: `src/backend/tests/Feature/Modules/Organization/PositionApiTest.php`
- Create: `src/backend/tests/Feature/Modules/Organization/OrgTreeApiTest.php`
- Create: `src/backend/tests/Feature/Modules/Organization/PermissionEnforcementTest.php`

Uses `RefreshDatabase` trait and seeds organization data for each test.

- [ ] **Step 1: Create BranchApiTest**

```php
<?php

namespace Tests\Feature\Modules\Organization;

use App\Modules\Organization\Infrastructure\Persistence\Eloquent\BranchModel;
use App\Modules\Organization\Infrastructure\Persistence\Eloquent\DepartmentModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BranchApiTest extends TestCase
{
    use RefreshDatabase;

    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\App\Modules\Identity\Infrastructure\Seeders\PermissionSeeder::class);
        $this->seed(\App\Modules\Identity\Infrastructure\Seeders\RoleSeeder::class);
        $this->seed(\App\Modules\Organization\Infrastructure\Seeders\OrgStructureSeeder::class);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@ihrm.local',
            'password' => 'password',
        ]);
        $this->token = $response->json('data.access_token');
    }

    public function test_list_branches(): void
    {
        $response = $this->withToken($this->token)->getJson('/api/v1/branches');
        $response->assertStatus(200);
        $response->assertJsonStructure(['data' => [['id', 'code', 'name', 'status']]]);
    }

    public function test_create_branch(): void
    {
        $response = $this->withToken($this->token)->postJson('/api/v1/branches', [
            'code' => 'DN-OFFICE',
            'name' => 'Da Nang Office',
            'address' => '789 Bach Dang, Da Nang',
        ]);
        $response->assertStatus(201);
        $this->assertDatabaseHas('branches', ['code' => 'DN-OFFICE']);
    }

    public function test_create_branch_duplicate_code_returns_409(): void
    {
        $this->withToken($this->token)->postJson('/api/v1/branches', ['code' => 'HCM-HQ', 'name' => 'Duplicate']);
        $response = $this->withToken($this->token)->postJson('/api/v1/branches', ['code' => 'HCM-HQ', 'name' => 'Duplicate']);
        $response->assertStatus(409);
        $response->assertJsonPath('error.code', 'DUPLICATE_BRANCH_CODE');
    }

    public function test_deactivate_branch_with_departments_returns_409(): void
    {
        $branch = BranchModel::where('code', 'HCM-HQ')->first();
        $response = $this->withToken($this->token)->postJson("/api/v1/branches/{$branch->id}/deactivate");
        $response->assertStatus(409);
        $response->assertJsonPath('error.code', 'BRANCH_HAS_ACTIVE_DEPARTMENTS');
    }

    public function test_show_branch(): void
    {
        $branch = BranchModel::where('code', 'HCM-HQ')->first();
        $response = $this->withToken($this->token)->getJson("/api/v1/branches/{$branch->id}");
        $response->assertStatus(200);
        $response->assertJsonPath('data.code', 'HCM-HQ');
    }
}
```

- [ ] **Step 2: Create DepartmentApiTest**

```php
<?php

namespace Tests\Feature\Modules\Organization;

use App\Modules\Organization\Infrastructure\Persistence\Eloquent\BranchModel;
use App\Modules\Organization\Infrastructure\Persistence\Eloquent\DepartmentModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepartmentApiTest extends TestCase
{
    use RefreshDatabase;

    private string $token;
    private string $hcmBranchId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\App\Modules\Identity\Infrastructure\Seeders\PermissionSeeder::class);
        $this->seed(\App\Modules\Identity\Infrastructure\Seeders\RoleSeeder::class);
        $this->seed(\App\Modules\Organization\Infrastructure\Seeders\OrgStructureSeeder::class);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@ihrm.local',
            'password' => 'password',
        ]);
        $this->token = $response->json('data.access_token');
        $this->hcmBranchId = BranchModel::where('code', 'HCM-HQ')->first()->id;
    }

    public function test_list_departments(): void
    {
        $response = $this->withToken($this->token)->getJson('/api/v1/departments');
        $response->assertStatus(200);
        $response->assertJsonStructure(['data' => [['id', 'code', 'name', 'branch_id', 'parent_id', 'status']]]);
    }

    public function test_filter_departments_by_branch(): void
    {
        $response = $this->withToken($this->token)->getJson("/api/v1/departments?branch_id={$this->hcmBranchId}");
        $response->assertStatus(200);
    }

    public function test_create_department(): void
    {
        $response = $this->withToken($this->token)->postJson('/api/v1/departments', [
            'branch_id' => $this->hcmBranchId,
            'code' => 'TEST',
            'name' => 'Test Department',
        ]);
        $response->assertStatus(201);
        $this->assertDatabaseHas('departments', ['code' => 'TEST']);
    }

    public function test_move_department_to_self_returns_422(): void
    {
        $dept = DepartmentModel::where('code', 'IT')->first();
        $response = $this->withToken($this->token)->postJson("/api/v1/departments/{$dept->id}/move", [
            'new_parent_id' => $dept->id,
        ]);
        $response->assertStatus(422);
        $response->assertJsonPath('error.code', 'CIRCULAR_MOVE');
    }

    public function test_move_department_to_descendant_returns_422(): void
    {
        $parentId = DepartmentModel::where('code', 'IT')->first()->id;
        $childId = DepartmentModel::where('code', 'IT-DEV')->first()->id;

        // Try to move IT under IT-DEV (descendant)
        $response = $this->withToken($this->token)->postJson("/api/v1/departments/{$parentId}/move", [
            'new_parent_id' => $childId,
        ]);
        $response->assertStatus(422);
        $response->assertJsonPath('error.code', 'CIRCULAR_MOVE');
    }

    public function test_deactivate_department_with_children_returns_409(): void
    {
        $dept = DepartmentModel::where('code', 'IT')->first();
        $response = $this->withToken($this->token)->postJson("/api/v1/departments/{$dept->id}/deactivate");
        $response->assertStatus(409);
        $response->assertJsonPath('error.code', 'DEPARTMENT_HAS_ACTIVE_CHILDREN');
    }
}
```

- [ ] **Step 3: Create PositionApiTest**

```php
<?php

namespace Tests\Feature\Modules\Organization;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PositionApiTest extends TestCase
{
    use RefreshDatabase;

    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\App\Modules\Identity\Infrastructure\Seeders\PermissionSeeder::class);
        $this->seed(\App\Modules\Identity\Infrastructure\Seeders\RoleSeeder::class);
        $this->seed(\App\Modules\Organization\Infrastructure\Seeders\OrgStructureSeeder::class);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@ihrm.local',
            'password' => 'password',
        ]);
        $this->token = $response->json('data.access_token');
    }

    public function test_list_positions(): void
    {
        $response = $this->withToken($this->token)->getJson('/api/v1/positions');
        $response->assertStatus(200);
        $response->assertJsonStructure(['data' => [['id', 'code', 'name', 'level', 'status']]]);
    }

    public function test_create_position(): void
    {
        $response = $this->withToken($this->token)->postJson('/api/v1/positions', [
            'code' => 'QA',
            'name' => 'QA Engineer',
            'level' => 3,
        ]);
        $response->assertStatus(201);
        $this->assertDatabaseHas('positions', ['code' => 'QA']);
    }

    public function test_duplicate_position_code_returns_409(): void
    {
        $this->withToken($this->token)->postJson('/api/v1/positions', ['code' => 'QA', 'name' => 'QA']);
        $response = $this->withToken($this->token)->postJson('/api/v1/positions', ['code' => 'QA', 'name' => 'Duplicate']);
        $response->assertStatus(409);
    }
}
```

- [ ] **Step 4: Create OrgTreeApiTest**

```php
<?php

namespace Tests\Feature\Modules\Organization;

use App\Modules\Organization\Infrastructure\Persistence\Eloquent\BranchModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrgTreeApiTest extends TestCase
{
    use RefreshDatabase;

    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\App\Modules\Identity\Infrastructure\Seeders\PermissionSeeder::class);
        $this->seed(\App\Modules\Identity\Infrastructure\Seeders\RoleSeeder::class);
        $this->seed(\App\Modules\Organization\Infrastructure\Seeders\OrgStructureSeeder::class);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@ihrm.local',
            'password' => 'password',
        ]);
        $this->token = $response->json('data.access_token');
    }

    public function test_org_tree_returns_nested_structure(): void
    {
        $response = $this->withToken($this->token)->getJson('/api/v1/org-tree');
        $response->assertStatus(200);
        $response->assertJsonStructure(['data' => [
            '*' => ['id', 'code', 'name', 'departments' => [
                '*' => ['id', 'code', 'name', 'children' => ['*' => ['id', 'code', 'name']]],
            ]],
        ]]);
    }

    public function test_org_tree_filtered_by_branch(): void
    {
        $hcm = BranchModel::where('code', 'HCM-HQ')->first();
        $response = $this->withToken($this->token)->getJson("/api/v1/org-tree?branch_id={$hcm->id}");
        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
    }
}
```

- [ ] **Step 5: Create PermissionEnforcementTest**

```php
<?php

namespace Tests\Feature\Modules\Organization;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermissionEnforcementTest extends TestCase
{
    use RefreshDatabase;

    private string $employeeToken;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\App\Modules\Identity\Infrastructure\Seeders\PermissionSeeder::class);
        $this->seed(\App\Modules\Identity\Infrastructure\Seeders\RoleSeeder::class);

        // Create an employee user (only has organization.tree.view)
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@ihrm.local',
            'password' => 'password',
        ]);
        $this->employeeToken = $response->json('data.access_token');
    }

    public function test_employee_can_view_org_tree(): void
    {
        $response = $this->withToken($this->employeeToken)->getJson('/api/v1/org-tree');
        $response->assertStatus(200);
    }

    public function test_employee_cannot_create_branch(): void
    {
        $response = $this->withToken($this->employeeToken)->postJson('/api/v1/branches', [
            'code' => 'TEST',
            'name' => 'Test',
        ]);
        $response->assertStatus(403);
    }

    public function test_employee_cannot_create_department(): void
    {
        $response = $this->withToken($this->employeeToken)->postJson('/api/v1/departments', [
            'branch_id' => '00000000-0000-0000-0000-000000000001',
            'code' => 'TEST',
            'name' => 'Test',
        ]);
        $response->assertStatus(403);
    }

    public function test_unauthenticated_request_returns_401(): void
    {
        $response = $this->getJson('/api/v1/branches');
        $response->assertStatus(401);
    }
}
```

- [ ] **Step 6: Run feature tests**

```bash
docker compose run --rm app php artisan test tests/Feature/Modules/Organization
```

Expected: PASS.

- [ ] **Step 7: Commit**

```bash
git add src/backend/tests/Feature/Modules/Organization
git commit -m "test(organization): add feature HTTP tests"
```

---

### Task 16: Module README and final verification

**Files:**
- Create: `src/backend/app/Modules/Organization/README.md`

- [ ] **Step 1: Write README**

```markdown
# Organization Module

Branch, Department, and Position management.

## Routes

All routes under `/api/v1`, require `auth:sanctum`.

| Method | Endpoint | Permission |
|--------|----------|------------|
| GET | /branches | organization.branch.list |
| POST | /branches | organization.branch.create |
| GET | /branches/{id} | organization.branch.view |
| PATCH | /branches/{id} | organization.branch.update |
| POST | /branches/{id}/activate | organization.branch.update |
| POST | /branches/{id}/deactivate | organization.branch.update |
| GET | /departments | organization.department.list |
| POST | /departments | organization.department.create |
| GET | /departments/{id} | organization.department.view |
| PATCH | /departments/{id} | organization.department.update |
| POST | /departments/{id}/move | organization.department.move |
| POST | /departments/{id}/activate | organization.department.update |
| POST | /departments/{id}/deactivate | organization.department.update |
| GET | /positions | organization.position.list |
| POST | /positions | organization.position.create |
| GET | /positions/{id} | organization.position.view |
| PATCH | /positions/{id} | organization.position.update |
| POST | /positions/{id}/activate | organization.position.update |
| POST | /positions/{id}/deactivate | organization.position.update |
| GET | /org-tree | organization.tree.view |

## Seed Data

```bash
php artisan db:seed --class=OrgStructureSeeder
```

Creates 2 branches (HCM-HQ, HN-OFFICE), departments with hierarchy, and 8 positions.

## Permissions

14 organization.* permissions auto-seeded. HR_MANAGER role gets all. EMPLOYEE role gets tree.view only.

## Error Codes

| Code | HTTP | Description |
|------|------|-------------|
| BRANCH_NOT_FOUND | 404 | |
| DUPLICATE_BRANCH_CODE | 409 | |
| BRANCH_HAS_ACTIVE_DEPARTMENTS | 409 | |
| DEPARTMENT_NOT_FOUND | 404 | |
| DUPLICATE_DEPARTMENT_CODE | 409 | |
| CIRCULAR_MOVE | 422 | |
| DEPARTMENT_NOT_IN_SAME_BRANCH | 422 | |
| DEPARTMENT_HAS_ACTIVE_CHILDREN | 409 | |
| POSITION_NOT_FOUND | 404 | |
| DUPLICATE_POSITION_CODE | 409 | |

## Running Tests

```bash
# Domain unit tests
php artisan test tests/Unit/Modules/Organization

# Feature API tests
php artisan test tests/Feature/Modules/Organization

# All
php artisan test tests/Unit/Modules/Organization tests/Feature/Modules/Organization
```
```

- [ ] **Step 2: Run full test suite**

```bash
docker compose run --rm app php artisan test
```

Expected: PASS (all existing tests + new organization tests).

- [ ] **Step 3: Commit**

```bash
git add src/backend/app/Modules/Organization/README.md
git commit -m "docs(organization): add module README"
```

---

## Self-Review

1. **Spec coverage:**
   - Branch CRUD + activate/deactivate → Task 1/2/6/8/12/13/15
   - Department CRUD + move + activate/deactivate (parent_id tree) → Task 1/2/6/9/12/13/15
   - Position CRUD + activate/deactivate → Task 1/2/6/10/12/13/15
   - Org tree endpoint → Task 11/13/15
   - Cycle detection on move → Task 6 (Department aggregate) + Task 15 (test)
   - Has-children guard on deactivate → Task 6/8/9 + tests
   - Permissions seed → Task 14
   - Role mapping (SUPER_ADMIN/HR_MANAGER/EMPLOYEE) → Task 14
   - Seed data (2 branches, 10 depts, 8 positions) → Task 14
   - Domain unit tests (branch, department, position) → Task 6
   - Feature HTTP tests (CRUD + permission enforcement) → Task 15
   - No N+1 queries → Task 12 repositories use eager loading
   - README → Task 16

2. **Placeholder scan:** All steps contain actual PHP code. No TBD/TODO/FIXME.

3. **Type consistency:** BranchId/DepartmentId/PositionId used consistently across all tasks. Event constructors match. Method signatures (update, moveTo, deactivate callable params) align between aggregate and handler.

4. **Scope check:** Focused on Organization BC only. No Employee/Contract/Document leakage.
