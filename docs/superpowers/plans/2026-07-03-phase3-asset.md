# Phase 3 Asset Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build Asset module with inventory CRUD, assign/return, status actions, employee obligation read-model, and Offboarding final-clearance integration.

**Architecture:** DDD 3-layer module under `app/Modules/Asset/`, matching Training/Offboarding conventions. Pure PHP domain, command/query application layer, Eloquent persistence, Laravel HTTP controllers/routes, module seeder.

**Tech Stack:** Laravel, PHP 8.3, Eloquent, Sanctum auth, existing `permission:<code>` middleware, PHPUnit/Pest via `php artisan test`.

---

## File Map

```
Create: 3 migrations
Domain: 3 enums, 3 ID VOs, 3 aggregates, 4 events, 8 exceptions, 3 repository interfaces
Application: 5 commands + handlers, 3 queries + handlers
Infrastructure: 3 Eloquent models, 3 repos, 3 controllers, 1 seeder, routes
Modify: src/backend/routes/api.php, DatabaseSeeder.php, Offboarding AssetCheckService
Tests: Unit/Modules/Asset/, Feature/Modules/Asset/
```

## Task 1: Migrations

**Files:**
- Create: `src/backend/database/migrations/2026_07_03_140001_create_asset_items_table.php`
- Create: `src/backend/database/migrations/2026_07_03_140002_create_asset_assignments_table.php`
- Create: `src/backend/database/migrations/2026_07_03_140003_create_asset_returns_table.php`

- [ ] Run `php artisan make:migration create_asset_items_table` and fill content.

Content for `create_asset_items_table.php`:
```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('asset_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('asset_code')->unique();
            $table->string('asset_type');
            $table->string('name');
            $table->string('serial_number')->nullable();
            $table->string('condition');
            $table->string('status');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index('status');
            $table->index(['asset_type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_items');
    }
};
```

- [ ] Run `php artisan make:migration create_asset_assignments_table` and fill content.

Content for `create_asset_assignments_table.php`:
```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('asset_assignments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('asset_item_id');
            $table->uuid('employee_id');
            $table->timestamp('issued_at');
            $table->timestamp('expected_return_at')->nullable();
            $table->string('condition_on_issue');
            $table->string('status');
            $table->timestamps();
            $table->index(['employee_id', 'status']);
            $table->index(['asset_item_id', 'status']);
            $table->foreign('asset_item_id')->references('id')->on('asset_items');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_assignments');
    }
};
```

- [ ] Run `php artisan make:migration create_asset_returns_table` and fill content.

Content for `create_asset_returns_table.php`:
```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('asset_returns', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('asset_assignment_id')->unique();
            $table->timestamp('returned_at');
            $table->string('condition_on_return');
            $table->text('notes')->nullable();
            $table->decimal('settlement_amount', 12, 2)->default(0);
            $table->timestamps();
            $table->foreign('asset_assignment_id')->references('id')->on('asset_assignments');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_returns');
    }
};
```

- [ ] Run `php artisan migrate` and verify tables created.

Run: `php artisan migrate`
Expected: message confirming 3 migrations ran.

- [ ] Commit.

```bash
git add src/backend/database/migrations/
git commit -m "feat(asset): add asset_items, asset_assignments, asset_returns tables"
```

## Task 2: Domain Value Objects

**Files:**
- Create: `src/backend/app/Modules/Asset/Domain/ValueObjects/AssetItemStatus.php`
- Create: `src/backend/app/Modules/Asset/Domain/ValueObjects/AssetAssignmentStatus.php`
- Create: `src/backend/app/Modules/Asset/Domain/ValueObjects/AssetCondition.php`
- Create: `src/backend/app/Modules/Asset/Domain/ValueObjects/AssetItemId.php`
- Create: `src/backend/app/Modules/Asset/Domain/ValueObjects/AssetAssignmentId.php`
- Create: `src/backend/app/Modules/Asset/Domain/ValueObjects/AssetReturnId.php`

- [ ] Create AssetItemStatus enum with transitions.

Create `AssetItemStatus.php`:
```php
<?php
namespace App\Modules\Asset\Domain\ValueObjects;

enum AssetItemStatus: string
{
    case Available = 'available';
    case Assigned = 'assigned';
    case Maintenance = 'maintenance';
    case Lost = 'lost';
    case Damaged = 'damaged';

    public function canTransitionTo(self $target): bool
    {
        return match ($this) {
            self::Available => in_array($target, [self::Assigned, self::Maintenance, self::Lost, self::Damaged], true),
            self::Assigned => $target === self::Available,
            self::Maintenance => in_array($target, [self::Available, self::Lost, self::Damaged], true),
            self::Lost, self::Damaged => false,
        };
    }
}
```

- [ ] Create AssetAssignmentStatus enum.

Create `AssetAssignmentStatus.php`:
```php
<?php
namespace App\Modules\Asset\Domain\ValueObjects;

enum AssetAssignmentStatus: string
{
    case Active = 'active';
    case Returned = 'returned';
}
```

- [ ] Create AssetCondition enum.

Create `AssetCondition.php`:
```php
<?php
namespace App\Modules\Asset\Domain\ValueObjects;

enum AssetCondition: string
{
    case New = 'new';
    case Good = 'good';
    case Fair = 'fair';
    case Poor = 'poor';
    case Damaged = 'damaged';
    case Lost = 'lost';
}
```

- [ ] Create AssetItemId VO.

Create `AssetItemId.php`:
```php
<?php
namespace App\Modules\Asset\Domain\ValueObjects;

use App\Modules\Shared\Domain\ValueObjects\UuidValueObject;

class AssetItemId extends UuidValueObject {}
```

- [ ] Create AssetAssignmentId VO.

Create `AssetAssignmentId.php`:
```php
<?php
namespace App\Modules\Asset\Domain\ValueObjects;

use App\Modules\Shared\Domain\ValueObjects\UuidValueObject;

class AssetAssignmentId extends UuidValueObject {}
```

- [ ] Create AssetReturnId VO.

Create `AssetReturnId.php`:
```php
<?php
namespace App\Modules\Asset\Domain\ValueObjects;

use App\Modules\Shared\Domain\ValueObjects\UuidValueObject;

class AssetReturnId extends UuidValueObject {}
```

- [ ] Verify `UuidValueObject` exists in Shared module.

Run: `grep -r "class UuidValueObject" src/backend/app/Modules/Shared/`
Expected: a class file. If not, create minimal UUID VO base.

Create if missing:
```php
<?php
namespace App\Modules\Shared\Domain\ValueObjects;

use Ramsey\Uuid\Uuid;

abstract class UuidValueObject
{
    public function __construct(public readonly string $value) {}

    public static function generate(): static
    {
        return new static(Uuid::uuid7()->toString());
    }

    public static function fromString(string $value): static
    {
        return new static($value);
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

- [ ] Commit.

```bash
git add src/backend/app/Modules/Asset/Domain/ValueObjects/
git commit -m "feat(asset): add domain enums and ID value objects"
```

## Task 3: Domain Exceptions

**Files:**
- Create (8 exception files under `src/backend/app/Modules/Asset/Domain/Exceptions/`)

- [ ] Create `AssetItemNotFoundException.php`:
```php
<?php
namespace App\Modules\Asset\Domain\Exceptions;

class AssetItemNotFoundException extends \RuntimeException
{
    public function __construct(string $id)
    {
        parent::__construct("Asset item not found: {$id}");
    }
}
```

- [ ] Create `AssetAssignmentNotFoundException.php`:
```php
<?php
namespace App\Modules\Asset\Domain\Exceptions;

class AssetAssignmentNotFoundException extends \RuntimeException
{
    public function __construct(string $id)
    {
        parent::__construct("Asset assignment not found: {$id}");
    }
}
```

- [ ] Create `AssetAlreadyAssignedException.php`:
```php
<?php
namespace App\Modules\Asset\Domain\Exceptions;

class AssetAlreadyAssignedException extends \RuntimeException
{
    public function __construct(string $assetCode)
    {
        parent::__construct("Asset already has an active assignment: {$assetCode}");
    }
}
```

- [ ] Create `AssetNotAvailableException.php`:
```php
<?php
namespace App\Modules\Asset\Domain\Exceptions;

class AssetNotAvailableException extends \RuntimeException
{
    public function __construct(string $status)
    {
        parent::__construct("Asset is not available (status: {$status})");
    }
}
```

- [ ] Create `AssetAssignmentAlreadyReturnedException.php`:
```php
<?php
namespace App\Modules\Asset\Domain\Exceptions;

class AssetAssignmentAlreadyReturnedException extends \RuntimeException
{
    public function __construct(string $id)
    {
        parent::__construct("Asset assignment already returned: {$id}");
    }
}
```

- [ ] Create `AssetHasAssignmentHistoryException.php`:
```php
<?php
namespace App\Modules\Asset\Domain\Exceptions;

class AssetHasAssignmentHistoryException extends \RuntimeException
{
    public function __construct()
    {
        parent::__construct("Cannot delete asset with assignment history");
    }
}
```

- [ ] Create `AssetStatusTransitionException.php`:
```php
<?php
namespace App\Modules\Asset\Domain\Exceptions;

class AssetStatusTransitionException extends \RuntimeException
{
    public function __construct(string $from, string $to)
    {
        parent::__construct("Cannot transition asset from '{$from}' to '{$to}'");
    }
}
```

- [ ] Create `AssetObligationsNotMetException.php`:
```php
<?php
namespace App\Modules\Asset\Domain\Exceptions;

class AssetObligationsNotMetException extends \RuntimeException
{
    public function __construct(public readonly array $pendingAssets)
    {
        parent::__construct('Employee has unresolved asset obligations');
    }

    public function getPendingAssets(): array
    {
        return $this->pendingAssets;
    }
}
```

- [ ] Commit.

```bash
git add src/backend/app/Modules/Asset/Domain/Exceptions/
git commit -m "feat(asset): add domain exceptions"
```

## Task 4: Domain Events

**Files:**
- Create: `src/backend/app/Modules/Asset/Domain/Events/AssetItemCreated.php`
- Create: `src/backend/app/Modules/Asset/Domain/Events/AssetItemStatusChanged.php`
- Create: `src/backend/app/Modules/Asset/Domain/Events/AssetAssigned.php`
- Create: `src/backend/app/Modules/Asset/Domain/Events/AssetReturned.php`

- [ ] Create event classes (follow existing pattern).

Create `AssetItemCreated.php`:
```php
<?php
namespace App\Modules\Asset\Domain\Events;

use App\Modules\Asset\Domain\ValueObjects\AssetItemId;

class AssetItemCreated
{
    public function __construct(
        public readonly AssetItemId $itemId,
        public readonly string $assetCode,
        public readonly string $assetType,
        public readonly string $name,
    ) {}
}
```

Create `AssetItemStatusChanged.php`:
```php
<?php
namespace App\Modules\Asset\Domain\Events;

use App\Modules\Asset\Domain\ValueObjects\AssetItemId;

class AssetItemStatusChanged
{
    public function __construct(
        public readonly AssetItemId $itemId,
        public readonly string $fromStatus,
        public readonly string $toStatus,
    ) {}
}
```

Create `AssetAssigned.php`:
```php
<?php
namespace App\Modules\Asset\Domain\Events;

use App\Modules\Asset\Domain\ValueObjects\AssetAssignmentId;
use App\Modules\Asset\Domain\ValueObjects\AssetItemId;

class AssetAssigned
{
    public function __construct(
        public readonly AssetAssignmentId $assignmentId,
        public readonly AssetItemId $itemId,
        public readonly string $employeeId,
    ) {}
}
```

Create `AssetReturned.php`:
```php
<?php
namespace App\Modules\Asset\Domain\Events;

use App\Modules\Asset\Domain\ValueObjects\AssetAssignmentId;
use App\Modules\Asset\Domain\ValueObjects\AssetReturnId;

class AssetReturned
{
    public function __construct(
        public readonly AssetReturnId $returnId,
        public readonly AssetAssignmentId $assignmentId,
        public readonly string $conditionOnReturn,
    ) {}
}
```

- [ ] Commit.

```bash
git add src/backend/app/Modules/Asset/Domain/Events/
git commit -m "feat(asset): add domain events"
```

## Task 5: Domain Aggregates

**Files:**
- Create: `src/backend/app/Modules/Asset/Domain/Aggregates/AssetItem/AssetItem.php`
- Create: `src/backend/app/Modules/Asset/Domain/Aggregates/AssetAssignment/AssetAssignment.php`
- Create: `src/backend/app/Modules/Asset/Domain/Aggregates/AssetReturn/AssetReturn.php`

- [ ] Create AssetItem aggregate.

Create `AssetItem.php`:
```php
<?php
namespace App\Modules\Asset\Domain\Aggregates\AssetItem;

use App\Modules\Asset\Domain\Events\AssetItemCreated;
use App\Modules\Asset\Domain\Events\AssetItemStatusChanged;
use App\Modules\Asset\Domain\Exceptions\AssetStatusTransitionException;
use App\Modules\Asset\Domain\ValueObjects\AssetItemId;
use App\Modules\Asset\Domain\ValueObjects\AssetItemStatus;

class AssetItem
{
    private array $recordedEvents = [];

    private function __construct(
        private readonly AssetItemId $id,
        private string $assetCode,
        private string $assetType,
        private string $name,
        private ?string $serialNumber,
        private string $condition,
        private AssetItemStatus $status,
        private ?string $notes,
        private ?\DateTimeImmutable $createdAt = null,
        private ?\DateTimeImmutable $updatedAt = null,
    ) {}

    public static function create(
        AssetItemId $id,
        string $assetCode,
        string $assetType,
        string $name,
        ?string $serialNumber,
        string $condition,
        ?string $notes,
    ): self {
        $item = new self(
            $id, $assetCode, $assetType, $name, $serialNumber,
            $condition, AssetItemStatus::Available, $notes,
            new \DateTimeImmutable(), new \DateTimeImmutable(),
        );
        $item->recordEvent(new AssetItemCreated($id, $assetCode, $assetType, $name));
        return $item;
    }

    public static function reconstitute(
        AssetItemId $id,
        string $assetCode,
        string $assetType,
        string $name,
        ?string $serialNumber,
        string $condition,
        AssetItemStatus $status,
        ?string $notes,
        ?\DateTimeImmutable $createdAt,
        ?\DateTimeImmutable $updatedAt,
    ): self {
        return new self($id, $assetCode, $assetType, $name, $serialNumber, $condition, $status, $notes, $createdAt, $updatedAt);
    }

    public function updateDetails(string $assetType, string $name, ?string $serialNumber, string $condition, ?string $notes): void
    {
        $this->assetType = $assetType;
        $this->name = $name;
        $this->serialNumber = $serialNumber;
        $this->condition = $condition;
        $this->notes = $notes;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function markStatus(AssetItemStatus $newStatus): void
    {
        if (!$this->status->canTransitionTo($newStatus)) {
            throw new AssetStatusTransitionException($this->status->value, $newStatus->value);
        }
        $from = $this->status->value;
        $this->status = $newStatus;
        $this->updatedAt = new \DateTimeImmutable();
        $this->recordEvent(new AssetItemStatusChanged($this->id, $from, $newStatus->value));
    }

    public function assign(): void
    {
        if ($this->status !== AssetItemStatus::Available) {
            throw new AssetStatusTransitionException($this->status->value, AssetItemStatus::Assigned->value);
        }
        $this->markStatus(AssetItemStatus::Assigned);
    }

    public function finishReturn(string $conditionOnReturn): AssetItemStatus
    {
        return match (true) {
            $conditionOnReturn === 'lost' => AssetItemStatus::Lost,
            $conditionOnReturn === 'damaged' => AssetItemStatus::Damaged,
            $conditionOnReturn === 'poor' => AssetItemStatus::Maintenance,
            default => AssetItemStatus::Available,
        };
    }

    public function getId(): AssetItemId { return $this->id; }
    public function getAssetCode(): string { return $this->assetCode; }
    public function getName(): string { return $this->name; }
    public function getStatus(): AssetItemStatus { return $this->status; }
    public function getCondition(): string { return $this->condition; }
    public function getType(): string { return $this->assetType; }
    public function getSerialNumber(): ?string { return $this->serialNumber; }
    public function getNotes(): ?string { return $this->notes; }
    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }

    public function recordEvent(object $event): void { $this->recordedEvents[] = $event; }
    public function popRecordedEvents(): array { $events = $this->recordedEvents; $this->recordedEvents = []; return $events; }
}
```

- [ ] Create AssetAssignment aggregate.

Create `AssetAssignment.php`:
```php
<?php
namespace App\Modules\Asset\Domain\Aggregates\AssetAssignment;

use App\Modules\Asset\Domain\Events\AssetAssigned;
use App\Modules\Asset\Domain\Exceptions\AssetAssignmentAlreadyReturnedException;
use App\Modules\Asset\Domain\ValueObjects\AssetAssignmentId;
use App\Modules\Asset\Domain\ValueObjects\AssetAssignmentStatus;
use App\Modules\Asset\Domain\ValueObjects\AssetItemId;

class AssetAssignment
{
    private array $recordedEvents = [];

    private function __construct(
        private readonly AssetAssignmentId $id,
        private readonly AssetItemId $assetItemId,
        private readonly string $employeeId,
        private \DateTimeImmutable $issuedAt,
        private ?\DateTimeImmutable $expectedReturnAt,
        private string $conditionOnIssue,
        private AssetAssignmentStatus $status,
        private ?\DateTimeImmutable $createdAt = null,
        private ?\DateTimeImmutable $updatedAt = null,
    ) {}

    public static function create(
        AssetAssignmentId $id,
        AssetItemId $assetItemId,
        string $employeeId,
        \DateTimeImmutable $issuedAt,
        ?\DateTimeImmutable $expectedReturnAt,
        string $conditionOnIssue,
    ): self {
        $a = new self(
            $id, $assetItemId, $employeeId, $issuedAt, $expectedReturnAt,
            $conditionOnIssue, AssetAssignmentStatus::Active,
            new \DateTimeImmutable(), new \DateTimeImmutable(),
        );
        $a->recordEvent(new AssetAssigned($id, $assetItemId, $employeeId));
        return $a;
    }

    public static function reconstitute(
        AssetAssignmentId $id,
        AssetItemId $assetItemId,
        string $employeeId,
        \DateTimeImmutable $issuedAt,
        ?\DateTimeImmutable $expectedReturnAt,
        string $conditionOnIssue,
        AssetAssignmentStatus $status,
        ?\DateTimeImmutable $createdAt,
        ?\DateTimeImmutable $updatedAt,
    ): self {
        return new self($id, $assetItemId, $employeeId, $issuedAt, $expectedReturnAt, $conditionOnIssue, $status, $createdAt, $updatedAt);
    }

    public function completeReturn(): void
    {
        if ($this->status !== AssetAssignmentStatus::Active) {
            throw new AssetAssignmentAlreadyReturnedException($this->id->value);
        }
        $this->status = AssetAssignmentStatus::Returned;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): AssetAssignmentId { return $this->id; }
    public function getAssetItemId(): AssetItemId { return $this->assetItemId; }
    public function getEmployeeId(): string { return $this->employeeId; }
    public function getIssuedAt(): \DateTimeImmutable { return $this->issuedAt; }
    public function getStatus(): AssetAssignmentStatus { return $this->status; }
    public function getConditionOnIssue(): string { return $this->conditionOnIssue; }

    public function recordEvent(object $event): void { $this->recordedEvents[] = $event; }
    public function popRecordedEvents(): array { $events = $this->recordedEvents; $this->recordedEvents = []; return $events; }
}
```

- [ ] Create AssetReturn aggregate.

Create `AssetReturn.php`:
```php
<?php
namespace App\Modules\Asset\Domain\Aggregates\AssetReturn;

use App\Modules\Asset\Domain\Events\AssetReturned;
use App\Modules\Asset\Domain\ValueObjects\AssetAssignmentId;
use App\Modules\Asset\Domain\ValueObjects\AssetReturnId;

class AssetReturn
{
    private array $recordedEvents = [];

    private function __construct(
        private readonly AssetReturnId $id,
        private readonly AssetAssignmentId $assetAssignmentId,
        private \DateTimeImmutable $returnedAt,
        private string $conditionOnReturn,
        private ?string $notes,
        private float $settlementAmount,
        private ?\DateTimeImmutable $createdAt = null,
        private ?\DateTimeImmutable $updatedAt = null,
    ) {}

    public static function create(
        AssetReturnId $id,
        AssetAssignmentId $assetAssignmentId,
        \DateTimeImmutable $returnedAt,
        string $conditionOnReturn,
        ?string $notes,
        float $settlementAmount = 0.0,
    ): self {
        $r = new self(
            $id, $assetAssignmentId, $returnedAt, $conditionOnReturn,
            $notes, $settlementAmount,
            new \DateTimeImmutable(), new \DateTimeImmutable(),
        );
        $r->recordEvent(new AssetReturned($id, $assetAssignmentId, $conditionOnReturn));
        return $r;
    }

    public static function reconstitute(
        AssetReturnId $id,
        AssetAssignmentId $assetAssignmentId,
        \DateTimeImmutable $returnedAt,
        string $conditionOnReturn,
        ?string $notes,
        float $settlementAmount,
        ?\DateTimeImmutable $createdAt,
        ?\DateTimeImmutable $updatedAt,
    ): self {
        return new self($id, $assetAssignmentId, $returnedAt, $conditionOnReturn, $notes, $settlementAmount, $createdAt, $updatedAt);
    }

    public function getId(): AssetReturnId { return $this->id; }

    public function recordEvent(object $event): void { $this->recordedEvents[] = $event; }
    public function popRecordedEvents(): array { $events = $this->recordedEvents; $this->recordedEvents = []; return $events; }
}
```

- [ ] Commit.

```bash
git add src/backend/app/Modules/Asset/Domain/
git commit -m "feat(asset): add domain aggregates"
```

## Task 6: Repository Interfaces

**Files:**
- Create: `src/backend/app/Modules/Asset/Domain/Repositories/AssetItemRepositoryInterface.php`
- Create: `src/backend/app/Modules/Asset/Domain/Repositories/AssetAssignmentRepositoryInterface.php`
- Create: `src/backend/app/Modules/Asset/Domain/Repositories/AssetReturnRepositoryInterface.php`

- [ ] Create AssetItemRepositoryInterface.

```php
<?php
namespace App\Modules\Asset\Domain\Repositories;

use App\Modules\Asset\Domain\Aggregates\AssetItem\AssetItem;
use App\Modules\Asset\Domain\ValueObjects\AssetItemId;

interface AssetItemRepositoryInterface
{
    public function findById(AssetItemId $id): ?AssetItem;
    public function findByAssetCode(string $assetCode): ?AssetItem;
    public function save(AssetItem $item): void;
    public function delete(AssetItem $item): void;
    public function all(array $filters = []): array;
}
```

- [ ] Create AssetAssignmentRepositoryInterface.

```php
<?php
namespace App\Modules\Asset\Domain\Repositories;

use App\Modules\Asset\Domain\Aggregates\AssetAssignment\AssetAssignment;
use App\Modules\Asset\Domain\ValueObjects\AssetAssignmentId;
use App\Modules\Asset\Domain\ValueObjects\AssetItemId;

interface AssetAssignmentRepositoryInterface
{
    public function findById(AssetAssignmentId $id): ?AssetAssignment;
    public function findActiveByAsset(AssetItemId $assetItemId): ?AssetAssignment;
    public function findActiveByEmployee(string $employeeId): array;
    public function save(AssetAssignment $assignment): void;
    public function all(array $filters = []): array;
}
```

- [ ] Create AssetReturnRepositoryInterface.

```php
<?php
namespace App\Modules\Asset\Domain\Repositories;

use App\Modules\Asset\Domain\Aggregates\AssetReturn\AssetReturn;
use App\Modules\Asset\Domain\ValueObjects\AssetReturnId;

interface AssetReturnRepositoryInterface
{
    public function findById(AssetReturnId $id): ?AssetReturn;
    public function save(AssetReturn $return): void;
}
```

- [ ] Commit.

```bash
git add src/backend/app/Modules/Asset/Domain/Repositories/
git commit -m "feat(asset): add repository interfaces"
```

## Task 7: Eloquent Models

**Files:**
- Create: `src/backend/app/Modules/Asset/Infrastructure/Persistence/Eloquent/Models/AssetItemModel.php`
- Create: `src/backend/app/Modules/Asset/Infrastructure/Persistence/Eloquent/Models/AssetAssignmentModel.php`
- Create: `src/backend/app/Modules/Asset/Infrastructure/Persistence/Eloquent/Models/AssetReturnModel.php`

- [ ] Create AssetItemModel.

```php
<?php
namespace App\Modules\Asset\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class AssetItemModel extends Model
{
    use HasUuids;

    protected $table = 'asset_items';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id', 'asset_code', 'asset_type', 'name', 'serial_number',
        'condition', 'status', 'notes',
    ];

    protected $casts = [
        'id' => 'string',
    ];
}
```

- [ ] Create AssetAssignmentModel.

```php
<?php
namespace App\Modules\Asset\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class AssetAssignmentModel extends Model
{
    use HasUuids;

    protected $table = 'asset_assignments';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id', 'asset_item_id', 'employee_id', 'issued_at',
        'expected_return_at', 'condition_on_issue', 'status',
    ];

    protected $casts = [
        'id' => 'string',
        'asset_item_id' => 'string',
        'issued_at' => 'datetime',
        'expected_return_at' => 'datetime',
    ];
}
```

- [ ] Create AssetReturnModel.

```php
<?php
namespace App\Modules\Asset\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class AssetReturnModel extends Model
{
    use HasUuids;

    protected $table = 'asset_returns';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id', 'asset_assignment_id', 'returned_at',
        'condition_on_return', 'notes', 'settlement_amount',
    ];

    protected $casts = [
        'id' => 'string',
        'asset_assignment_id' => 'string',
        'returned_at' => 'datetime',
        'settlement_amount' => 'decimal:2',
    ];
}
```

- [ ] Commit.

```bash
git add src/backend/app/Modules/Asset/Infrastructure/Persistence/
git commit -m "feat(asset): add Eloquent models"
```

## Task 8: Eloquent Repositories

**Files:**
- Create: `src/backend/app/Modules/Asset/Infrastructure/Persistence/Eloquent/Repositories/EloquentAssetItemRepository.php`
- Create: `src/backend/app/Modules/Asset/Infrastructure/Persistence/Eloquent/Repositories/EloquentAssetAssignmentRepository.php`
- Create: `src/backend/app/Modules/Asset/Infrastructure/Persistence/Eloquent/Repositories/EloquentAssetReturnRepository.php`

- [ ] Create EloquentAssetItemRepository.

```php
<?php
namespace App\Modules\Asset\Infrastructure\Persistence\Eloquent\Repositories;

use App\Modules\Asset\Domain\Aggregates\AssetItem\AssetItem;
use App\Modules\Asset\Domain\Repositories\AssetItemRepositoryInterface;
use App\Modules\Asset\Domain\ValueObjects\AssetItemId;
use App\Modules\Asset\Infrastructure\Persistence\Eloquent\Models\AssetItemModel;
use App\Modules\Asset\Domain\ValueObjects\AssetItemStatus;

class EloquentAssetItemRepository implements AssetItemRepositoryInterface
{
    public function findById(AssetItemId $id): ?AssetItem
    {
        $model = AssetItemModel::find($id->value);
        return $model ? $this->toDomain($model) : null;
    }

    public function findByAssetCode(string $assetCode): ?AssetItem
    {
        $model = AssetItemModel::where('asset_code', $assetCode)->first();
        return $model ? $this->toDomain($model) : null;
    }

    public function save(AssetItem $item): void
    {
        AssetItemModel::updateOrCreate(
            ['id' => $item->getId()->value],
            [
                'asset_code' => $item->getAssetCode(),
                'asset_type' => $item->getType(),
                'name' => $item->getName(),
                'serial_number' => $item->getSerialNumber(),
                'condition' => $item->getCondition(),
                'status' => $item->getStatus()->value,
                'notes' => $item->getNotes(),
            ]
        );
    }

    public function delete(AssetItem $item): void
    {
        AssetItemModel::destroy($item->getId()->value);
    }

    public function all(array $filters = []): array
    {
        $query = AssetItemModel::query();
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['asset_type'])) {
            $query->where('asset_type', $filters['asset_type']);
        }
        return $query->get()->map(fn(AssetItemModel $m) => $this->toDomain($m))->toArray();
    }

    private function toDomain(AssetItemModel $model): AssetItem
    {
        return AssetItem::reconstitute(
            AssetItemId::fromString($model->id),
            $model->asset_code,
            $model->asset_type,
            $model->name,
            $model->serial_number,
            $model->condition,
            AssetItemStatus::from($model->status),
            $model->notes,
            $model->created_at?->toDateTimeImmutable(),
            $model->updated_at?->toDateTimeImmutable(),
        );
    }
}
```

- [ ] Create EloquentAssetAssignmentRepository.

```php
<?php
namespace App\Modules\Asset\Infrastructure\Persistence\Eloquent\Repositories;

use App\Modules\Asset\Domain\Aggregates\AssetAssignment\AssetAssignment;
use App\Modules\Asset\Domain\Repositories\AssetAssignmentRepositoryInterface;
use App\Modules\Asset\Domain\ValueObjects\AssetAssignmentId;
use App\Modules\Asset\Domain\ValueObjects\AssetAssignmentStatus;
use App\Modules\Asset\Domain\ValueObjects\AssetItemId;
use App\Modules\Asset\Infrastructure\Persistence\Eloquent\Models\AssetAssignmentModel;

class EloquentAssetAssignmentRepository implements AssetAssignmentRepositoryInterface
{
    public function findById(AssetAssignmentId $id): ?AssetAssignment
    {
        $model = AssetAssignmentModel::find($id->value);
        return $model ? $this->toDomain($model) : null;
    }

    public function findActiveByAsset(AssetItemId $assetItemId): ?AssetAssignment
    {
        $model = AssetAssignmentModel::where('asset_item_id', $assetItemId->value)
            ->where('status', AssetAssignmentStatus::Active->value)
            ->first();
        return $model ? $this->toDomain($model) : null;
    }

    public function findActiveByEmployee(string $employeeId): array
    {
        return AssetAssignmentModel::where('employee_id', $employeeId)
            ->where('status', AssetAssignmentStatus::Active->value)
            ->get()
            ->map(fn(AssetAssignmentModel $m) => $this->toDomain($m))
            ->toArray();
    }

    public function save(AssetAssignment $assignment): void
    {
        AssetAssignmentModel::updateOrCreate(
            ['id' => $assignment->getId()->value],
            [
                'asset_item_id' => $assignment->getAssetItemId()->value,
                'employee_id' => $assignment->getEmployeeId(),
                'issued_at' => $assignment->getIssuedAt(),
                'condition_on_issue' => $assignment->getConditionOnIssue(),
                'status' => $assignment->getStatus()->value,
            ]
        );
    }

    public function all(array $filters = []): array
    {
        $query = AssetAssignmentModel::query();
        if (!empty($filters['employee_id'])) {
            $query->where('employee_id', $filters['employee_id']);
        }
        if (!empty($filters['asset_item_id'])) {
            $query->where('asset_item_id', $filters['asset_item_id']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        return $query->get()->map(fn(AssetAssignmentModel $m) => $this->toDomain($m))->toArray();
    }

    private function toDomain(AssetAssignmentModel $model): AssetAssignment
    {
        return AssetAssignment::reconstitute(
            AssetAssignmentId::fromString($model->id),
            AssetItemId::fromString($model->asset_item_id),
            $model->employee_id,
            new \DateTimeImmutable($model->issued_at),
            $model->expected_return_at ? new \DateTimeImmutable($model->expected_return_at) : null,
            $model->condition_on_issue,
            AssetAssignmentStatus::from($model->status),
            $model->created_at?->toDateTimeImmutable(),
            $model->updated_at?->toDateTimeImmutable(),
        );
    }
}
```

- [ ] Create EloquentAssetReturnRepository.

```php
<?php
namespace App\Modules\Asset\Infrastructure\Persistence\Eloquent\Repositories;

use App\Modules\Asset\Domain\Aggregates\AssetReturn\AssetReturn;
use App\Modules\Asset\Domain\Repositories\AssetReturnRepositoryInterface;
use App\Modules\Asset\Domain\ValueObjects\AssetAssignmentId;
use App\Modules\Asset\Domain\ValueObjects\AssetReturnId;
use App\Modules\Asset\Infrastructure\Persistence\Eloquent\Models\AssetReturnModel;

class EloquentAssetReturnRepository implements AssetReturnRepositoryInterface
{
    public function findById(AssetReturnId $id): ?AssetReturn
    {
        $model = AssetReturnModel::find($id->value);
        return $model ? $this->toDomain($model) : null;
    }

    public function save(AssetReturn $return): void
    {
        AssetReturnModel::updateOrCreate(
            ['id' => $return->getId()->value],
            [
                'asset_assignment_id' => $return->getAssetAssignmentId()->value,
                'returned_at' => $return->getReturnedAt(),
                'condition_on_return' => $return->getConditionOnReturn(),
                'notes' => $return->getNotes(),
                'settlement_amount' => $return->getSettlementAmount(),
            ]
        );
    }

    private function toDomain(AssetReturnModel $model): AssetReturn
    {
        return AssetReturn::reconstitute(
            AssetReturnId::fromString($model->id),
            AssetAssignmentId::fromString($model->asset_assignment_id),
            $model->returned_at->toDateTimeImmutable(),
            $model->condition_on_return,
            $model->notes,
            (float) $model->settlement_amount,
            $model->created_at?->toDateTimeImmutable(),
            $model->updated_at?->toDateTimeImmutable(),
        );
    }
}
```

Add required AssetReturn getters:
```php
    public function getAssetAssignmentId(): AssetAssignmentId
    {
        return $this->assetAssignmentId;
    }
```

- [ ] Fix AssetReturn aggregate with getters.

Add to AssetReturn:
```php
    public function getAssetAssignmentId(): AssetAssignmentId { return $this->assetAssignmentId; }
    public function getReturnedAt(): \DateTimeImmutable { return $this->returnedAt; }
    public function getConditionOnReturn(): string { return $this->conditionOnReturn; }
    public function getNotes(): ?string { return $this->notes; }
    public function getSettlementAmount(): float { return $this->settlementAmount; }
```

- [ ] Commit.

```bash
git add src/backend/app/Modules/Asset/Infrastructure/Persistence/Eloquent/Repositories/ src/backend/app/Modules/Asset/Domain/Aggregates/
git commit -m "feat(asset): add Eloquent repositories and AssetReturn getters"
```

## Task 9: Application Commands and Handlers

**Files:** Create 5 command/handler pairs:
- CreateAssetItemCommand + Handler
- UpdateAssetItemCommand + Handler
- MarkAssetItemStatusCommand + Handler
- AssignAssetCommand + Handler
- ReturnAssetCommand + Handler

And 3 query/handler pairs:
- ListAssetItemsQuery + Handler
- ListAssetAssignmentsQuery + Handler
- GetEmployeeObligationsQuery + Handler

- [ ] Create `CreateAssetItemCommand.php`.

```php
<?php
namespace App\Modules\Asset\Application\Commands;

class CreateAssetItemCommand
{
    public function __construct(
        public readonly string $assetCode,
        public readonly string $assetType,
        public readonly string $name,
        public readonly ?string $serialNumber = null,
        public readonly string $condition = 'new',
        public readonly ?string $notes = null,
    ) {}
}
```

- [ ] Create `CreateAssetItemHandler.php`.

```php
<?php
namespace App\Modules\Asset\Application\CommandHandlers;

use App\Modules\Asset\Application\Commands\CreateAssetItemCommand;
use App\Modules\Asset\Domain\Aggregates\AssetItem\AssetItem;
use App\Modules\Asset\Domain\Repositories\AssetItemRepositoryInterface;
use App\Modules\Asset\Domain\ValueObjects\AssetItemId;

class CreateAssetItemHandler
{
    public function __construct(
        private readonly AssetItemRepositoryInterface $repo,
    ) {}

    public function handle(CreateAssetItemCommand $command): AssetItem
    {
        $item = AssetItem::create(
            AssetItemId::generate(),
            $command->assetCode,
            $command->assetType,
            $command->name,
            $command->serialNumber,
            $command->condition,
            $command->notes,
        );
        $this->repo->save($item);
        return $item;
    }
}
```

- [ ] Create `UpdateAssetItemCommand.php`.

```php
<?php
namespace App\Modules\Asset\Application\Commands;

class UpdateAssetItemCommand
{
    public function __construct(
        public readonly string $id,
        public readonly string $assetType,
        public readonly string $name,
        public readonly ?string $serialNumber = null,
        public readonly string $condition = 'good',
        public readonly ?string $notes = null,
    ) {}
}
```

- [ ] Create `UpdateAssetItemHandler.php`.

```php
<?php
namespace App\Modules\Asset\Application\CommandHandlers;

use App\Modules\Asset\Application\Commands\UpdateAssetItemCommand;
use App\Modules\Asset\Domain\Exceptions\AssetItemNotFoundException;
use App\Modules\Asset\Domain\Repositories\AssetItemRepositoryInterface;
use App\Modules\Asset\Domain\ValueObjects\AssetItemId;

class UpdateAssetItemHandler
{
    public function __construct(
        private readonly AssetItemRepositoryInterface $repo,
    ) {}

    public function handle(UpdateAssetItemCommand $command): AssetItem
    {
        $id = AssetItemId::fromString($command->id);
        $item = $this->repo->findById($id);
        if (!$item) {
            throw new AssetItemNotFoundException($command->id);
        }
        $item->updateDetails(
            $command->assetType,
            $command->name,
            $command->serialNumber,
            $command->condition,
            $command->notes,
        );
        $this->repo->save($item);
        return $item;
    }
}
```

- [ ] Create `MarkAssetItemStatusCommand.php`.

```php
<?php
namespace App\Modules\Asset\Application\Commands;

use App\Modules\Asset\Domain\ValueObjects\AssetItemStatus;

class MarkAssetItemStatusCommand
{
    public function __construct(
        public readonly string $id,
        public readonly AssetItemStatus $newStatus,
    ) {}
}
```

- [ ] Create `MarkAssetItemStatusHandler.php`.

```php
<?php
namespace App\Modules\Asset\Application\CommandHandlers;

use App\Modules\Asset\Application\Commands\MarkAssetItemStatusCommand;
use App\Modules\Asset\Domain\Aggregates\AssetItem\AssetItem;
use App\Modules\Asset\Domain\Exceptions\AssetItemNotFoundException;
use App\Modules\Asset\Domain\Repositories\AssetAssignmentRepositoryInterface;
use App\Modules\Asset\Domain\Repositories\AssetItemRepositoryInterface;
use App\Modules\Asset\Domain\ValueObjects\AssetItemId;
use App\Modules\Asset\Domain\ValueObjects\AssetItemStatus;

class MarkAssetItemStatusHandler
{
    public function __construct(
        private readonly AssetItemRepositoryInterface $itemRepo,
        private readonly AssetAssignmentRepositoryInterface $assignmentRepo,
    ) {}

    public function handle(MarkAssetItemStatusCommand $command): AssetItem
    {
        $id = AssetItemId::fromString($command->id);
        $item = $this->itemRepo->findById($id);
        if (!$item) {
            throw new AssetItemNotFoundException($command->id);
        }
        $activeAssignment = $this->assignmentRepo->findActiveByAsset($id);
        if ($activeAssignment !== null) {
            throw new \RuntimeException('Cannot change status of an actively assigned asset');
        }
        $item->markStatus($command->newStatus);
        $this->itemRepo->save($item);
        return $item;
    }
}
```

- [ ] Create `AssignAssetCommand.php`.

```php
<?php
namespace App\Modules\Asset\Application\Commands;

class AssignAssetCommand
{
    public function __construct(
        public readonly string $assetItemId,
        public readonly string $employeeId,
        public readonly ?string $expectedReturnAt = null,
        public readonly ?string $conditionOnIssue = null,
    ) {}
}
```

- [ ] Create `AssignAssetHandler.php`.

```php
<?php
namespace App\Modules\Asset\Application\CommandHandlers;

use App\Modules\Asset\Application\Commands\AssignAssetCommand;
use App\Modules\Asset\Domain\Aggregates\AssetAssignment\AssetAssignment;
use App\Modules\Asset\Domain\Exceptions\AssetAlreadyAssignedException;
use App\Modules\Asset\Domain\Exceptions\AssetItemNotFoundException;
use App\Modules\Asset\Domain\Exceptions\AssetNotAvailableException;
use App\Modules\Asset\Domain\Repositories\AssetAssignmentRepositoryInterface;
use App\Modules\Asset\Domain\Repositories\AssetItemRepositoryInterface;
use App\Modules\Asset\Domain\ValueObjects\AssetAssignmentId;
use App\Modules\Asset\Domain\ValueObjects\AssetItemId;

class AssignAssetHandler
{
    public function __construct(
        private readonly AssetItemRepositoryInterface $itemRepo,
        private readonly AssetAssignmentRepositoryInterface $assignmentRepo,
    ) {}

    public function handle(AssignAssetCommand $command): AssetAssignment
    {
        $assetId = AssetItemId::fromString($command->assetItemId);
        $item = $this->itemRepo->findById($assetId);
        if (!$item) {
            throw new AssetItemNotFoundException($command->assetItemId);
        }
        if ($this->assignmentRepo->findActiveByAsset($assetId)) {
            throw new AssetAlreadyAssignedException($item->getAssetCode());
        }
        $item->assign();
        $assignment = AssetAssignment::create(
            AssetAssignmentId::generate(),
            $assetId,
            $command->employeeId,
            new \DateTimeImmutable(),
            $command->expectedReturnAt ? new \DateTimeImmutable($command->expectedReturnAt) : null,
            $command->conditionOnIssue ?? $item->getCondition(),
        );
        $this->itemRepo->save($item);
        $this->assignmentRepo->save($assignment);
        return $assignment;
    }
}
```

- [ ] Create `ReturnAssetCommand.php`.

```php
<?php
namespace App\Modules\Asset\Application\Commands;

class ReturnAssetCommand
{
    public function __construct(
        public readonly string $assignmentId,
        public readonly string $conditionOnReturn,
        public readonly ?string $notes = null,
        public readonly float $settlementAmount = 0.0,
        public readonly ?string $returnedAt = null,
    ) {}
}
```

- [ ] Create `ReturnAssetHandler.php`.

```php
<?php
namespace App\Modules\Asset\Application\CommandHandlers;

use App\Modules\Asset\Application\Commands\ReturnAssetCommand;
use App\Modules\Asset\Domain\Aggregates\AssetReturn\AssetReturn;
use App\Modules\Asset\Domain\Exceptions\AssetAssignmentNotFoundException;
use App\Modules\Asset\Domain\Exceptions\AssetItemNotFoundException;
use App\Modules\Asset\Domain\Repositories\AssetAssignmentRepositoryInterface;
use App\Modules\Asset\Domain\Repositories\AssetItemRepositoryInterface;
use App\Modules\Asset\Domain\Repositories\AssetReturnRepositoryInterface;
use App\Modules\Asset\Domain\ValueObjects\AssetAssignmentId;
use App\Modules\Asset\Domain\ValueObjects\AssetItemId;
use App\Modules\Asset\Domain\ValueObjects\AssetReturnId;

class ReturnAssetHandler
{
    public function __construct(
        private readonly AssetAssignmentRepositoryInterface $assignmentRepo,
        private readonly AssetItemRepositoryInterface $itemRepo,
        private readonly AssetReturnRepositoryInterface $returnRepo,
    ) {}

    public function handle(ReturnAssetCommand $command): AssetReturn
    {
        $assignmentId = AssetAssignmentId::fromString($command->assignmentId);
        $assignment = $this->assignmentRepo->findById($assignmentId);
        if (!$assignment) {
            throw new AssetAssignmentNotFoundException($command->assignmentId);
        }
        $assignment->completeReturn();
        $return = AssetReturn::create(
            AssetReturnId::generate(),
            $assignmentId,
            $command->returnedAt ? new \DateTimeImmutable($command->returnedAt) : new \DateTimeImmutable(),
            $command->conditionOnReturn,
            $command->notes,
            $command->settlementAmount,
        );
        $itemId = $assignment->getAssetItemId();
        $item = $this->itemRepo->findById($itemId);
        if (!$item) {
            throw new AssetItemNotFoundException($itemId->value);
        }
        $newItemStatus = $item->finishReturn($command->conditionOnReturn);
        $item->markStatus($newItemStatus);
        $this->assignmentRepo->save($assignment);
        $this->returnRepo->save($return);
        $this->itemRepo->save($item);
        return $return;
    }
}
```

- [ ] Create `ListAssetItemsQuery.php`.

```php
<?php
namespace App\Modules\Asset\Application\Queries;

class ListAssetItemsQuery
{
    public function __construct(
        public readonly ?string $status = null,
        public readonly ?string $assetType = null,
    ) {}
}
```

- [ ] Create `ListAssetItemsHandler.php`.

```php
<?php
namespace App\Modules\Asset\Application\QueryHandlers;

use App\Modules\Asset\Application\Queries\ListAssetItemsQuery;
use App\Modules\Asset\Domain\Repositories\AssetItemRepositoryInterface;

class ListAssetItemsHandler
{
    public function __construct(
        private readonly AssetItemRepositoryInterface $repo,
    ) {}

    public function handle(ListAssetItemsQuery $query): array
    {
        return $this->repo->all([
            'status' => $query->status,
            'asset_type' => $query->assetType,
        ]);
    }
}
```

- [ ] Create `ListAssetAssignmentsQuery.php`.

```php
<?php
namespace App\Modules\Asset\Application\Queries;

class ListAssetAssignmentsQuery
{
    public function __construct(
        public readonly ?string $employeeId = null,
        public readonly ?string $assetItemId = null,
        public readonly ?string $status = null,
    ) {}
}
```

- [ ] Create `ListAssetAssignmentsHandler.php`.

```php
<?php
namespace App\Modules\Asset\Application\QueryHandlers;

use App\Modules\Asset\Application\Queries\ListAssetAssignmentsQuery;
use App\Modules\Asset\Domain\Repositories\AssetAssignmentRepositoryInterface;

class ListAssetAssignmentsHandler
{
    public function __construct(
        private readonly AssetAssignmentRepositoryInterface $repo,
    ) {}

    public function handle(ListAssetAssignmentsQuery $query): array
    {
        return $this->repo->all([
            'employee_id' => $query->employeeId,
            'asset_item_id' => $query->assetItemId,
            'status' => $query->status,
        ]);
    }
}
```

- [ ] Create `GetEmployeeObligationsQuery.php`.

```php
<?php
namespace App\Modules\Asset\Application\Queries;

class GetEmployeeObligationsQuery
{
    public function __construct(
        public readonly string $employeeId,
    ) {}
}
```

- [ ] Create `GetEmployeeObligationsHandler.php`.

```php
<?php
namespace App\Modules\Asset\Application\QueryHandlers;

use App\Modules\Asset\Application\Queries\GetEmployeeObligationsQuery;
use App\Modules\Asset\Domain\Repositories\AssetAssignmentRepositoryInterface;

class GetEmployeeObligationsHandler
{
    public function __construct(
        private readonly AssetAssignmentRepositoryInterface $assignmentRepo,
    ) {}

    public function handle(GetEmployeeObligationsQuery $query): array
    {
        return $this->assignmentRepo->findActiveByEmployee($query->employeeId);
    }
}
```

- [ ] Commit.

```bash
git add src/backend/app/Modules/Asset/Application/
git commit -m "feat(asset): add application commands, handlers, queries"
```

## Task 10: Controllers

**Files:**
- Create: `src/backend/app/Modules/Asset/Infrastructure/Http/Controllers/AssetItemController.php`
- Create: `src/backend/app/Modules/Asset/Infrastructure/Http/Controllers/AssetAssignmentController.php`
- Create: `src/backend/app/Modules/Asset/Infrastructure/Http/Controllers/AssetObligationController.php`

- [ ] Create AssetItemController.

```php
<?php
namespace App\Modules\Asset\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Asset\Application\Commands\CreateAssetItemCommand;
use App\Modules\Asset\Application\Commands\UpdateAssetItemCommand;
use App\Modules\Asset\Application\Commands\MarkAssetItemStatusCommand;
use App\Modules\Asset\Application\CommandHandlers\CreateAssetItemHandler;
use App\Modules\Asset\Application\CommandHandlers\UpdateAssetItemHandler;
use App\Modules\Asset\Application\CommandHandlers\MarkAssetItemStatusHandler;
use App\Modules\Asset\Application\Queries\ListAssetItemsQuery;
use App\Modules\Asset\Application\QueryHandlers\ListAssetItemsHandler;
use App\Modules\Asset\Domain\Repositories\AssetItemRepositoryInterface;
use App\Modules\Asset\Domain\ValueObjects\AssetItemId;
use App\Modules\Asset\Domain\ValueObjects\AssetItemStatus;
use App\Modules\Asset\Domain\Exceptions\AssetItemNotFoundException;
use App\Modules\Asset\Domain\Exceptions\AssetHasAssignmentHistoryException;
use App\Modules\Asset\Domain\Exceptions\AssetStatusTransitionException;
use App\Modules\Asset\Domain\Repositories\AssetAssignmentRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssetItemController extends Controller
{
    public function __construct(
        private readonly CreateAssetItemHandler $createHandler,
        private readonly UpdateAssetItemHandler $updateHandler,
        private readonly MarkAssetItemStatusHandler $markStatusHandler,
        private readonly ListAssetItemsHandler $listHandler,
        private readonly AssetItemRepositoryInterface $itemRepo,
        private readonly AssetAssignmentRepositoryInterface $assignmentRepo,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $items = $this->listHandler->handle(
            new ListAssetItemsQuery(
                status: $request->query('status'),
                assetType: $request->query('asset_type'),
            )
        );
        return response()->json(['data' => $items]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'asset_code' => 'required|string|max:255',
            'asset_type' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'serial_number' => 'nullable|string|max:255',
            'condition' => 'string',
            'notes' => 'nullable|string',
        ]);
        $item = $this->createHandler->handle(
            new CreateAssetItemCommand(
                assetCode: $validated['asset_code'],
                assetType: $validated['asset_type'],
                name: $validated['name'],
                serialNumber: $validated['serial_number'] ?? null,
                condition: $validated['condition'] ?? 'new',
                notes: $validated['notes'] ?? null,
            )
        );
        return response()->json(['data' => $item], 201);
    }

    public function show(string $id): JsonResponse
    {
        $item = $this->itemRepo->findById(AssetItemId::fromString($id));
        if (!$item) {
            throw new AssetItemNotFoundException($id);
        }
        return response()->json(['data' => $item]);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'asset_type' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'serial_number' => 'nullable|string|max:255',
            'condition' => 'string',
            'notes' => 'nullable|string',
        ]);
        $item = $this->updateHandler->handle(
            new UpdateAssetItemCommand(
                id: $id,
                assetType: $validated['asset_type'],
                name: $validated['name'],
                serialNumber: $validated['serial_number'] ?? null,
                condition: $validated['condition'] ?? 'good',
                notes: $validated['notes'] ?? null,
            )
        );
        return response()->json(['data' => $item]);
    }

    public function destroy(string $id): JsonResponse
    {
        $itemId = AssetItemId::fromString($id);
        $item = $this->itemRepo->findById($itemId);
        if (!$item) {
            throw new AssetItemNotFoundException($id);
        }
        $assignments = $this->assignmentRepo->all(['asset_item_id' => $id]);
        if (count($assignments) > 0) {
            throw new AssetHasAssignmentHistoryException();
        }
        $this->itemRepo->delete($item);
        return response()->json(['message' => 'Asset item deleted']);
    }

    public function markAvailable(string $id): JsonResponse
    {
        return $this->markStatus($id, AssetItemStatus::Available);
    }

    public function markMaintenance(string $id): JsonResponse
    {
        return $this->markStatus($id, AssetItemStatus::Maintenance);
    }

    public function markLost(string $id): JsonResponse
    {
        return $this->markStatus($id, AssetItemStatus::Lost);
    }

    public function markDamaged(string $id): JsonResponse
    {
        return $this->markStatus($id, AssetItemStatus::Damaged);
    }

    private function markStatus(string $id, AssetItemStatus $status): JsonResponse
    {
        $item = $this->markStatusHandler->handle(
            new MarkAssetItemStatusCommand(id: $id, newStatus: $status)
        );
        return response()->json(['data' => $item]);
    }
}
```

- [ ] Create AssetAssignmentController.

```php
<?php
namespace App\Modules\Asset\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Asset\Application\Commands\AssignAssetCommand;
use App\Modules\Asset\Application\Commands\ReturnAssetCommand;
use App\Modules\Asset\Application\CommandHandlers\AssignAssetHandler;
use App\Modules\Asset\Application\CommandHandlers\ReturnAssetHandler;
use App\Modules\Asset\Application\Queries\ListAssetAssignmentsQuery;
use App\Modules\Asset\Application\QueryHandlers\ListAssetAssignmentsHandler;
use App\Modules\Asset\Domain\Repositories\AssetAssignmentRepositoryInterface;
use App\Modules\Asset\Domain\ValueObjects\AssetAssignmentId;
use App\Modules\Asset\Domain\Exceptions\AssetAssignmentNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssetAssignmentController extends Controller
{
    public function __construct(
        private readonly AssignAssetHandler $assignHandler,
        private readonly ReturnAssetHandler $returnHandler,
        private readonly ListAssetAssignmentsHandler $listHandler,
        private readonly AssetAssignmentRepositoryInterface $assignmentRepo,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $assignments = $this->listHandler->handle(
            new ListAssetAssignmentsQuery(
                employeeId: $request->query('employee_id'),
                assetItemId: $request->query('asset_item_id'),
                status: $request->query('status'),
            )
        );
        return response()->json(['data' => $assignments]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'asset_item_id' => 'required|uuid',
            'employee_id' => 'required|uuid',
            'expected_return_at' => 'nullable|date',
            'condition_on_issue' => 'nullable|string',
        ]);
        $assignment = $this->assignHandler->handle(
            new AssignAssetCommand(
                assetItemId: $validated['asset_item_id'],
                employeeId: $validated['employee_id'],
                expectedReturnAt: $validated['expected_return_at'] ?? null,
                conditionOnIssue: $validated['condition_on_issue'] ?? null,
            )
        );
        return response()->json(['data' => $assignment], 201);
    }

    public function show(string $id): JsonResponse
    {
        $assignment = $this->assignmentRepo->findById(AssetAssignmentId::fromString($id));
        if (!$assignment) {
            throw new AssetAssignmentNotFoundException($id);
        }
        return response()->json(['data' => $assignment]);
    }

    public function returnAsset(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'condition_on_return' => 'required|string',
            'notes' => 'nullable|string',
            'settlement_amount' => 'nullable|numeric|min:0',
        ]);
        $result = $this->returnHandler->handle(
            new ReturnAssetCommand(
                assignmentId: $id,
                conditionOnReturn: $validated['condition_on_return'],
                notes: $validated['notes'] ?? null,
                settlementAmount: (float)($validated['settlement_amount'] ?? 0),
            )
        );
        return response()->json(['data' => $result], 201);
    }
}
```

- [ ] Create AssetObligationController.

```php
<?php
namespace App\Modules\Asset\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Asset\Application\Queries\GetEmployeeObligationsQuery;
use App\Modules\Asset\Application\QueryHandlers\GetEmployeeObligationsHandler;
use Illuminate\Http\JsonResponse;

class AssetObligationController extends Controller
{
    public function __construct(
        private readonly GetEmployeeObligationsHandler $handler,
    ) {}

    public function __invoke(string $employeeId): JsonResponse
    {
        $obligations = $this->handler->handle(
            new GetEmployeeObligationsQuery(employeeId: $employeeId)
        );
        return response()->json(['data' => $obligations]);
    }
}
```

- [ ] Commit.

```bash
git add src/backend/app/Modules/Asset/Infrastructure/Http/
git commit -m "feat(asset): add HTTP controllers"
```

## Task 11: Permission Seeder and Routes

**Files:**
- Create: `src/backend/app/Modules/Asset/Infrastructure/Seeders/AssetPermissionSeeder.php`
- Create: `src/backend/app/Modules/Asset/Routes/api.php`
- Modify: `src/backend/routes/api.php`
- Modify: `src/backend/database/seeders/DatabaseSeeder.php`

- [ ] Create AssetPermissionSeeder.

```php
<?php
namespace App\Modules\Asset\Infrastructure\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AssetPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['asset.item.view', 'item', 'view'],
            ['asset.item.create', 'item', 'create'],
            ['asset.item.update', 'item', 'update'],
            ['asset.item.delete', 'item', 'delete'],
            ['asset.item.mark-status', 'item', 'mark-status'],
            ['asset.assignment.view', 'assignment', 'view'],
            ['asset.assignment.create', 'assignment', 'create'],
            ['asset.assignment.return', 'assignment', 'return'],
            ['asset.obligation.view', 'obligation', 'view'],
        ];
        foreach ($permissions as [$code, $resource, $action]) {
            DB::table('permissions')->updateOrInsert(
                ['code' => $code],
                ['resource' => $resource, 'action' => $action, 'created_at' => now(), 'updated_at' => now()],
            );
        }
    }
}
```

- [ ] Create Route file.

Create `src/backend/app/Modules/Asset/Routes/api.php`:
```php
<?php
use Illuminate\Support\Facades\Route;
use App\Modules\Asset\Infrastructure\Http\Controllers\AssetItemController;
use App\Modules\Asset\Infrastructure\Http\Controllers\AssetAssignmentController;
use App\Modules\Asset\Infrastructure\Http\Controllers\AssetObligationController;

Route::prefix('v1/assets')->middleware(['auth:sanctum'])->group(function () {
    // Inventory CRUD
    Route::get('items', [AssetItemController::class, 'index'])->middleware('permission:asset.item.view');
    Route::post('items', [AssetItemController::class, 'store'])->middleware('permission:asset.item.create');
    Route::get('items/{id}', [AssetItemController::class, 'show'])->middleware('permission:asset.item.view');
    Route::put('items/{id}', [AssetItemController::class, 'update'])->middleware('permission:asset.item.update');
    Route::delete('items/{id}', [AssetItemController::class, 'destroy'])->middleware('permission:asset.item.delete');

    // Status actions
    Route::post('items/{id}/mark-available', [AssetItemController::class, 'markAvailable'])->middleware('permission:asset.item.mark-status');
    Route::post('items/{id}/mark-maintenance', [AssetItemController::class, 'markMaintenance'])->middleware('permission:asset.item.mark-status');
    Route::post('items/{id}/mark-lost', [AssetItemController::class, 'markLost'])->middleware('permission:asset.item.mark-status');
    Route::post('items/{id}/mark-damaged', [AssetItemController::class, 'markDamaged'])->middleware('permission:asset.item.mark-status');

    // Assignments
    Route::get('assignments', [AssetAssignmentController::class, 'index'])->middleware('permission:asset.assignment.view');
    Route::post('assignments', [AssetAssignmentController::class, 'store'])->middleware('permission:asset.assignment.create');
    Route::get('assignments/{id}', [AssetAssignmentController::class, 'show'])->middleware('permission:asset.assignment.view');
    Route::post('assignments/{id}/return', [AssetAssignmentController::class, 'returnAsset'])->middleware('permission:asset.assignment.return');

    // Employee obligations
    Route::get('employees/{employeeId}/obligations', AssetObligationController::class)->middleware('permission:asset.obligation.view');
});
```

- [ ] Register routes in `src/backend/routes/api.php`.

Add line at the end (before last `});`):
```php
require __DIR__.'/../app/Modules/Asset/Routes/api.php';
```

- [ ] Register seeder in `DatabaseSeeder.php`.

Add line in `run()`:
```php
$this->call(\App\Modules\Asset\Infrastructure\Seeders\AssetPermissionSeeder::class);
```

- [ ] Commit.

```bash
git add src/backend/app/Modules/Asset/Routes/ src/backend/app/Modules/Asset/Infrastructure/Seeders/ src/backend/routes/api.php src/backend/database/seeders/DatabaseSeeder.php
git commit -m "feat(asset): add routes, seeder, and register in app"
```

## Task 12: Service Binding

**Files:**
- Modify: `src/backend/app/Providers/AppServiceProvider.php`

- [ ] Register repository bindings.

Add to `register()`:
```php
$this->app->bind(
    \App\Modules\Asset\Domain\Repositories\AssetItemRepositoryInterface::class,
    \App\Modules\Asset\Infrastructure\Persistence\Eloquent\Repositories\EloquentAssetItemRepository::class,
);
$this->app->bind(
    \App\Modules\Asset\Domain\Repositories\AssetAssignmentRepositoryInterface::class,
    \App\Modules\Asset\Infrastructure\Persistence\Eloquent\Repositories\EloquentAssetAssignmentRepository::class,
);
$this->app->bind(
    \App\Modules\Asset\Domain\Repositories\AssetReturnRepositoryInterface::class,
    \App\Modules\Asset\Infrastructure\Persistence\Eloquent\Repositories\EloquentAssetReturnRepository::class,
);
```

- [ ] Also bind the controller classes. (Check if Training module registers controllers manually or relies on auto-wiring.)

Run: `rg "Controllers" src/backend/app/Providers/AppServiceProvider.php` to check.

If controllers rely on auto-wiring, no action needed. If Training module registers manually, follow same pattern.

- [ ] Commit.

```bash
git add src/backend/app/Providers/AppServiceProvider.php
git commit -m "feat(asset): register repository bindings"
```

## Task 13: Offboarding Integration

**Files:**
- Modify: `src/backend/app/Modules/Offboarding/Infrastructure/Services/AssetCheckService.php`

- [ ] Update AssetCheckService to query real obligations.

```php
<?php
namespace App\Modules\Offboarding\Infrastructure\Services;

use App\Modules\Asset\Domain\Repositories\AssetAssignmentRepositoryInterface;
use App\Modules\Offboarding\Domain\Repositories\OffboardingPlanRepositoryInterface;
use App\Modules\Offboarding\Domain\Aggregates\OffboardingPlan\OffboardingPlanId;

class AssetCheckService
{
    public function __construct(
        private readonly OffboardingPlanRepositoryInterface $planRepo,
        private readonly AssetAssignmentRepositoryInterface $assignmentRepo,
    ) {}

    public function checkObligations(string $planId): AssetCheckResult
    {
        $plan = $this->planRepo->findById(OffboardingPlanId::fromString($planId));
        if (!$plan) {
            throw new \RuntimeException("Offboarding plan not found: {$planId}");
        }

        // Get the employee from the plan's offboarding request
        $employeeId = $plan->getEmployeeId(); // exists? need to check

        if (!$employeeId) {
            throw new \RuntimeException("Cannot resolve employee for offboarding plan: {$planId}");
        }

        $activeAssignments = $this->assignmentRepo->findActiveByEmployee($employeeId);

        if (count($activeAssignments) > 0) {
            $pending = array_map(
                fn($a) => ['assignment_id' => $a->getId()->value, 'asset_item_id' => $a->getAssetItemId()->value],
                $activeAssignments
            );
            return new AssetCheckResult(obligationsMet: false, pending: $pending);
        }

        return new AssetCheckResult(obligationsMet: true);
    }
}
```

- [ ] Check if Offboarding plan/request exposes employee ID.

Run: `grep -r "employee\|EmployeeId\|getEmployeeId" src/backend/app/Modules/Offboarding/Domain/ | head -20`

If employee ID is not directly available, the implementation needs the plan to have a method to retrieve it, or store employee_id directly on the plan at creation time.

- [ ] Commit.

```bash
git add src/backend/app/Modules/Offboarding/Infrastructure/Services/AssetCheckService.php
git commit -m "feat(asset): update Offboarding AssetCheckService to query real obligations"
```

## Task 14: Migrate and seed

- [ ] Run migrations.

```bash
docker compose run --rm app php artisan migrate
```
Expected: 3 migrations ran.

- [ ] Run permission seeder.

```bash
docker compose run --rm app php artisan db:seed --class=AssetPermissionSeeder
```
Expected: 9 asset permissions inserted.

- [ ] Check routes loaded.

```bash
docker compose run --rm app php artisan route:list --path=assets
```
Expected: all asset routes listed.

- [ ] Commit.

```bash
git add -A && git commit -m "feat(asset): run migrations and seed permissions"
```

## Task 15: Unit Tests — Domain

**Files:**
- Create: `src/backend/tests/Unit/Modules/Asset/AssetItemTest.php`
- Create: `src/backend/tests/Unit/Modules/Asset/AssetAssignmentTest.php`

- [ ] Write asset item test.

Create `AssetItemTest.php`:
```php
<?php
namespace Tests\Unit\Modules\Asset;

use PHPUnit\Framework\TestCase;
use App\Modules\Asset\Domain\Aggregates\AssetItem\AssetItem;
use App\Modules\Asset\Domain\ValueObjects\AssetItemId;
use App\Modules\Asset\Domain\ValueObjects\AssetItemStatus;
use App\Modules\Asset\Domain\Exceptions\AssetStatusTransitionException;

class AssetItemTest extends TestCase
{
    public function test_create_item(): void
    {
        $item = AssetItem::create(
            AssetItemId::generate(),
            'AST-001',
            'laptop',
            'MacBook Pro',
            'SN001',
            'new',
            null,
        );
        $this->assertSame('AST-001', $item->getAssetCode());
        $this->assertSame(AssetItemStatus::Available, $item->getStatus());
    }

    public function test_mark_maintenance_from_available(): void
    {
        $item = AssetItem::create(AssetItemId::generate(), 'AST-002', 'laptop', 'Dell XPS', null, 'good', null);
        $item->markStatus(AssetItemStatus::Maintenance);
        $this->assertSame(AssetItemStatus::Maintenance, $item->getStatus());
    }

    public function test_cannot_mark_available_from_assigned(): void
    {
        $item = AssetItem::create(AssetItemId::generate(), 'AST-003', 'laptop', 'ThinkPad', null, 'good', null);
        $item->assign();
        $this->expectException(AssetStatusTransitionException::class);
        $item->markStatus(AssetItemStatus::Available);
    }

    public function test_cannot_transition_from_lost(): void
    {
        $item = AssetItem::create(AssetItemId::generate(), 'AST-004', 'phone', 'iPhone', null, 'new', null);
        $item->markStatus(AssetItemStatus::Lost);
        $this->expectException(AssetStatusTransitionException::class);
        $item->markStatus(AssetItemStatus::Available);
    }

    public function test_finish_return_sets_status(): void
    {
        $item = AssetItem::create(AssetItemId::generate(), 'AST-005', 'laptop', 'MBP', null, 'good', null);
        $this->assertSame(AssetItemStatus::Available, $item->finishReturn('good'));
        $this->assertSame(AssetItemStatus::Maintenance, $item->finishReturn('poor'));
        $this->assertSame(AssetItemStatus::Lost, $item->finishReturn('lost'));
        $this->assertSame(AssetItemStatus::Damaged, $item->finishReturn('damaged'));
    }
}
```

- [ ] Write asset assignment test.

Create `AssetAssignmentTest.php`:
```php
<?php
namespace Tests\Unit\Modules\Asset;

use PHPUnit\Framework\TestCase;
use App\Modules\Asset\Domain\Aggregates\AssetAssignment\AssetAssignment;
use App\Modules\Asset\Domain\ValueObjects\AssetAssignmentId;
use App\Modules\Asset\Domain\ValueObjects\AssetAssignmentStatus;
use App\Modules\Asset\Domain\ValueObjects\AssetItemId;
use App\Modules\Asset\Domain\Exceptions\AssetAssignmentAlreadyReturnedException;

class AssetAssignmentTest extends TestCase
{
    public function test_create_assignment(): void
    {
        $a = AssetAssignment::create(
            AssetAssignmentId::generate(),
            AssetItemId::generate(),
            'emp-1',
            new \DateTimeImmutable(),
            null,
            'good',
        );
        $this->assertSame(AssetAssignmentStatus::Active, $a->getStatus());
    }

    public function test_complete_return(): void
    {
        $a = AssetAssignment::create(
            AssetAssignmentId::generate(),
            AssetItemId::generate(),
            'emp-1',
            new \DateTimeImmutable(),
            null,
            'good',
        );
        $a->completeReturn();
        $this->assertSame(AssetAssignmentStatus::Returned, $a->getStatus());
    }

    public function test_cannot_return_again(): void
    {
        $a = AssetAssignment::create(
            AssetAssignmentId::generate(),
            AssetItemId::generate(),
            'emp-1',
            new \DateTimeImmutable(),
            null,
            'good',
        );
        $a->completeReturn();
        $this->expectException(AssetAssignmentAlreadyReturnedException::class);
        $a->completeReturn();
    }
}
```

- [ ] Run domain tests.

```bash
docker compose run --rm app php artisan test tests/Unit/Modules/Asset --compact
```
Expected: all tests pass.

- [ ] Commit.

```bash
git add src/backend/tests/Unit/Modules/Asset/
git commit -m "test(asset): add domain unit tests"
```

## Task 16: Feature Tests

**Files:**
- Create: `src/backend/tests/Feature/Modules/Asset/AssetItemApiTest.php`
- Create: `src/backend/tests/Feature/Modules/Asset/AssetAssignmentApiTest.php`
- Create: `src/backend/tests/Feature/Modules/Asset/AssetObligationApiTest.php`
- Modify: `src/backend/tests/Feature/Modules/Offboarding/OffboardingClearanceTest.php`

- [ ] Write AssetItemApiTest.

```php
<?php
namespace Tests\Feature\Modules\Asset;

use Tests\TestCase;
use App\Modules\Asset\Infrastructure\Persistence\Eloquent\Models\AssetItemModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AssetItemApiTest extends TestCase
{
    use RefreshDatabase;

    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        // Create a user with all asset permissions
        $user = \App\Modules\Identity\Infrastructure\Persistence\Eloquent\Models\UserModel::factory()->create();
        $user->givePermissionTo(['asset.item.view', 'asset.item.create', 'asset.item.update', 'asset.item.delete', 'asset.item.mark-status']);
        $this->token = $user->createToken('test')->plainTextToken;
    }

    public function test_unauthenticated_access(): void
    {
        $this->getJson('/api/v1/assets/items')->assertStatus(401);
    }

    public function test_create_asset_item(): void
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/v1/assets/items', [
                'asset_code' => 'LAP-001',
                'asset_type' => 'laptop',
                'name' => 'MacBook Pro 16',
                'serial_number' => 'SN12345',
            ]);
        $response->assertStatus(201);
        $response->assertJsonStructure(['data' => ['id', 'asset_code', 'name']]);
        $this->assertDatabaseHas('asset_items', ['asset_code' => 'LAP-001']);
    }

    public function test_list_asset_items(): void
    {
        AssetItemModel::factory()->count(3)->create(['status' => 'available']);
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/v1/assets/items');
        $response->assertStatus(200);
        $response->assertJsonCount(3, 'data');
    }

    public function test_delete_asset_with_assignment_history_rejected(): void
    {
        $item = AssetItemModel::factory()->create(['status' => 'assigned']);
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->deleteJson("/api/v1/assets/items/{$item->id}");
        $response->assertStatus(422);
    }
}
```

- [ ] Write AssetAssignmentApiTest.

```php
<?php
namespace Tests\Feature\Modules\Asset;

use Tests\TestCase;
use App\Modules\Asset\Infrastructure\Persistence\Eloquent\Models\AssetItemModel;
use App\Modules\Asset\Infrastructure\Persistence\Eloquent\Models\AssetAssignmentModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AssetAssignmentApiTest extends TestCase
{
    use RefreshDatabase;

    private string $token;
    private string $itemId;

    protected function setUp(): void
    {
        parent::setUp();
        $user = \App\Modules\Identity\Infrastructure\Persistence\Eloquent\Models\UserModel::factory()->create();
        $user->givePermissionTo(['asset.assignment.view', 'asset.assignment.create', 'asset.assignment.return', 'asset.item.mark-status']);
        $this->token = $user->createToken('test')->plainTextToken;
        $item = AssetItemModel::factory()->create(['status' => 'available']);
        $this->itemId = $item->id;
    }

    public function test_assign_asset(): void
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/v1/assets/assignments', [
                'asset_item_id' => $this->itemId,
                'employee_id' => '00000000-0000-0000-0000-000000000001',
            ]);
        $response->assertStatus(201);
        $this->assertDatabaseHas('asset_assignments', [
            'asset_item_id' => $this->itemId,
            'status' => 'active',
        ]);
        $this->assertDatabaseHas('asset_items', [
            'id' => $this->itemId,
            'status' => 'assigned',
        ]);
    }
}
```

- [ ] Write AssetObligationApiTest.

```php
<?php
namespace Tests\Feature\Modules\Asset;

use Tests\TestCase;
use App\Modules\Asset\Infrastructure\Persistence\Eloquent\Models\AssetItemModel;
use App\Modules\Asset\Infrastructure\Persistence\Eloquent\Models\AssetAssignmentModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AssetObligationApiTest extends TestCase
{
    use RefreshDatabase;

    private string $token;
    private string $employeeId = '00000000-0000-0000-0000-000000000099';

    protected function setUp(): void
    {
        parent::setUp();
        $user = \App\Modules\Identity\Infrastructure\Persistence\Eloquent\Models\UserModel::factory()->create();
        $user->givePermissionTo(['asset.obligation.view']);
        $this->token = $user->createToken('test')->plainTextToken;
    }

    public function test_employee_without_obligations_returns_empty(): void
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson("/api/v1/assets/employees/{$this->employeeId}/obligations");
        $response->assertStatus(200);
        $response->assertJson(['data' => []]);
    }

    public function test_employee_with_active_assignments_shows_obligations(): void
    {
        $item = AssetItemModel::factory()->create(['status' => 'assigned']);
        AssetAssignmentModel::factory()->create([
            'asset_item_id' => $item->id,
            'employee_id' => $this->employeeId,
            'status' => 'active',
        ]);
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson("/api/v1/assets/employees/{$this->employeeId}/obligations");
        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
    }
}
```

- [ ] Run feature tests.

```bash
docker compose run --rm app php artisan test tests/Feature/Modules/Asset --compact
```
Expected: all feature tests pass.

- [ ] Run full suite.

```bash
docker compose run --rm app php artisan test --compact
```
Expected: all backend tests pass.

- [ ] Commit.

```bash
git add src/backend/tests/Feature/Modules/Asset/
git commit -m "test(asset): add feature tests for asset API"
```

## Task 17: Spec Verification

- [ ] Review each AC from spec against implementation.

```
AC1: CRUD items    -> Task 10 index/store/show/update, Task 11 routes ✅
AC2: assign only available -> Task 9 AssignAssetHandler checks `item->assign()` which enforces Available ✅
AC3: unique active assignment -> Task 9 handler checks findActiveByAsset before assign ✅
AC4: return creates record -> Task 9 ReturnAssetHandler creates AssetReturn ✅
AC5: item status from return condition -> Task 9 Handler calls fin return -> Task 5 finishReturn() ✅
AC6: manual status endpoints -> Task 10 markAvailable/Maintenance/Lost/Damaged ✅
AC7: obligations endpoint -> Task 10 AssetObligationController + Task 9 handler ✅
AC8: offboarding fails on obligations -> Task 13 AssetCheckService ✅
AC9: permissions seeded -> Task 11 seeder ✅
AC10: DDD layout -> Tasks 2-8 ✅
AC11: tests -> Tasks 15-16 ✅
```

- [ ] Run full suite one more time.

```bash
docker compose run --rm app php artisan test --compact
```
Expected: all pass.

- [ ] Commit any final fixes.

```bash
git add -A && git commit -m "chore(asset): finalize module — all tests pass"
```
