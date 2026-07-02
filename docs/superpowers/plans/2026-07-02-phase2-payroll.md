# Phase 2 Payroll Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build Phase 2 Payroll module with period lifecycle, component catalog, calculation engine, per-employee entries, adjustments, payslips, and full test suite.

**Architecture:** Strict DDD under `src/backend/app/Modules/Payroll/` — Domain pure PHP, Application commands/queries, Infrastructure Eloquent/HTTP/routes. Read ports (AttendanceReadPort, LeaveReadPort, EmployeeContractReadPort) for upstream data. Workflow BC integration for period approval chain.

**Tech Stack:** Laravel 12, PHP 8.4, PostgreSQL 16 UUIDs, Sanctum, Eloquent, PHPUnit.

---

## File Map

### Directories
```
src/backend/app/Modules/Payroll/
  Domain/
    Aggregates/PayrollPeriod/, PayrollComponent/, PayrollRun/, PayrollEntry/, Payslip/, PayrollAdjustment/
    ValueObjects/
    Services/
    Ports/
    Events/
    Repositories/
    Exceptions/
  Application/
    Commands/PayrollPeriod/, PayrollRun/, PayrollEntry/, PayrollAdjustment/, Payslip/
    CommandHandlers/ (per command)
    Queries/
  Infrastructure/
    Http/Controllers/, Requests/, Resources/
    Persistence/Eloquent/, Repositories/, Factories/
    Ports/AttendanceReadPort.php, LeaveReadPort.php, EmployeeContractReadPort.php
    Seeders/PayrollComponentSeeder.php
  Routes/api.php

src/backend/database/migrations/2026_07_02_1000*_create_payroll_*.php
src/backend/tests/Unit/Modules/Payroll/
src/backend/tests/Feature/Modules/Payroll/
```

### Files (62 files total — modular, 1 class per file)

### Migration files (7)
1. `2026_07_02_100001_create_payroll_periods_table.php`
2. `2026_07_02_100002_create_payroll_components_table.php`
3. `2026_07_02_100003_create_payroll_runs_table.php`
4. `2026_07_02_100004_create_payroll_entries_table.php`
5. `2026_07_02_100005_create_payroll_entry_lines_table.php`
6. `2026_07_02_100006_create_payroll_adjustments_table.php`
7. `2026_07_02_100007_create_payslips_table.php`

### Domain — ValueObjects (14 files)
- `PeriodStatus.php`, `RunStatus.php`, `ComponentCategory.php`, `CalculationType.php`, `AdjustmentStatus.php`, `PayslipStatus.php`
- `Money.php`, `PayrollFormulaResult.php`, `EntryError.php`

### Domain — Aggregates (12 files — 6 aggregates × 2: class + ID value object)
- `PayrollPeriod.php` + `PayrollPeriodId.php`
- `PayrollComponent.php` + `PayrollComponentId.php`
- `PayrollRun.php` + `PayrollRunId.php`
- `PayrollEntry.php` + `PayrollEntryId.php`
- `Payslip.php` + `PayslipId.php`
- `PayrollAdjustment.php` + `PayrollAdjustmentId.php`

### Domain — Events (10 files)
- `PayrollPeriodOpened.php`, `PayrollPeriodClosed.php`, `PayrollRunStarted.php`, `PayrollRunCompleted.php`, `PayrollApproved.php`, `PayrollLocked.php`, `PayrollPublished.php`, `PayslipAccessed.php`, `PayrollAdjusted.php`, `PayrollPeriodReopened.php`

### Domain — Exceptions (11 files)
- `PayrollPeriodClosedException.php`, `PayrollPeriodLockedException.php`, `PayrollPeriodNotFoundException.php`, `PayrollRunNotFoundException.php`, `PayrollEntryNotFoundException.php`, `PayrollComponentNotFoundException.php`, `DuplicatePayrollRunException.php`, `InvalidPayrollCalculationException.php`, `PayrollNotApprovedException.php`, `PayrollAlreadyPublishedException.php`, `PayrollAdjustmentNotFoundException.php`

### Domain — Ports (3 files)
- `AttendanceReadPort.php`, `LeaveReadPort.php`, `EmployeeContractReadPort.php`

### Domain — Services (5 files)
- `PayrollCalculator.php`, `PayrollFormulaEngine.php`, `AttendanceBasisCalculator.php`, `TaxCalculator.php`, `InsuranceCalculator.php`

### Domain — Repositories (6 interfaces)
- `PayrollPeriodRepositoryInterface.php`, `PayrollComponentRepositoryInterface.php`, `PayrollRunRepositoryInterface.php`, `PayrollEntryRepositoryInterface.php`, `PayslipRepositoryInterface.php`, `PayrollAdjustmentRepositoryInterface.php`

### Application — Commands (11 files)
- `OpenPayrollPeriodCommand.php`, `ClosePayrollPeriodCommand.php`, `ReopenPayrollPeriodCommand.php`
- `StartPayrollRunCommand.php`, `CompletePayrollRunCommand.php`
- `ReviewPayrollEntryCommand.php`
- `SubmitPayrollAdjustmentCommand.php`, `ApprovePayrollAdjustmentCommand.php`, `RejectPayrollAdjustmentCommand.php`
- `PublishPayslipsCommand.php`

### Application — CommandHandlers (11 files, one per command)
- `OpenPayrollPeriodHandler.php`, `ClosePayrollPeriodHandler.php`, `ReopenPayrollPeriodHandler.php`
- `StartPayrollRunHandler.php`, `CompletePayrollRunHandler.php`
- `ReviewPayrollEntryHandler.php`
- `SubmitPayrollAdjustmentHandler.php`, `ApprovePayrollAdjustmentHandler.php`, `RejectPayrollAdjustmentHandler.php`
- `PublishPayslipsHandler.php`

### Application — Queries (5 files)
- `PayrollPeriodListQuery.php`, `PayrollEntryListQuery.php`, `PayslipListQuery.php`, `PayslipViewQuery.php`, `PayrollSummaryQuery.php`

### Infrastructure — Eloquent models (7 files)
- `PayrollPeriodModel.php`, `PayrollComponentModel.php`, `PayrollRunModel.php`, `PayrollEntryModel.php`, `PayrollEntryLineModel.php`, `PayrollAdjustmentModel.php`, `PayslipModel.php`

### Infrastructure — Eloquent Repositories (6 files)
- `PayrollPeriodRepository.php`, `PayrollComponentRepository.php`, `PayrollRunRepository.php`, `PayrollEntryRepository.php`, `PayrollAdjustmentRepository.php`, `PayslipRepository.php`

### Infrastructure — Factories (6 files, for testing/seeding)
- `PayrollPeriodFactory.php`, `PayrollComponentFactory.php`, `PayrollRunFactory.php`, `PayrollEntryFactory.php`, `PayrollAdjustmentFactory.php`, `PayslipFactory.php`

### Infrastructure — HTTP (8 files)
- `PayrollPeriodController.php`, `PayrollRunController.php`, `PayrollEntryController.php`, `PayrollAdjustmentController.php`, `PayslipController.php`, `PayrollComponentController.php`
- `StorePayrollPeriodRequest.php`, `StorePayrollComponentRequest.php`, `UpdatePayrollComponentRequest.php`, `SubmitPayrollAdjustmentRequest.php`
- `PayrollPeriodResource.php`, `PayrollRunResource.php`, `PayrollEntryResource.php`, `PayslipResource.php`, `PayrollComponentResource.php`

### Infrastructure — Seeder
- `PayrollComponentSeeder.php`

### Module route
- `api.php`

### Module Registration
- Modify `src/backend/app/Providers/AppServiceProvider.php` — bind repository interfaces
- Modify `src/backend/routes/api.php` — load Payroll routes
- Modify Identity module's `PermissionSeeder.php` — add payroll.* permissions
- Modify Identity module's `RoleSeeder.php` — assign permissions to roles

### Tests (target: ~20 unit, ~10 feature)
```
tests/Unit/Modules/Payroll/
  PayrollPeriodTest.php
  PayrollComponentTest.php
  PayrollRunTest.php
  PayrollEntryTest.php
  PayrollAdjustmentTest.php
  PayslipTest.php
  PayrollFormulaEngineTest.php
  TaxCalculatorTest.php
  InsuranceCalculatorTest.php

tests/Feature/Modules/Payroll/
  PayrollApiTest.php
```

---

### Task 1: Database schema — create all 7 migrations

**Files:**
- Create: `src/backend/database/migrations/2026_07_02_100001_create_payroll_periods_table.php`
- Create: `src/backend/database/migrations/2026_07_02_100002_create_payroll_components_table.php`
- Create: `src/backend/database/migrations/2026_07_02_100003_create_payroll_runs_table.php`
- Create: `src/backend/database/migrations/2026_07_02_100004_create_payroll_entries_table.php`
- Create: `src/backend/database/migrations/2026_07_02_100005_create_payroll_entry_lines_table.php`
- Create: `src/backend/database/migrations/2026_07_02_100006_create_payroll_adjustments_table.php`
- Create: `src/backend/database/migrations/2026_07_02_100007_create_payslips_table.php`

- [ ] **Step 1:** Create `100001` periods migration — schema per §5 spec: id (uuid), period_code (unique), start_date, end_date, cutoff_date, status (varchar 20 default 'open'), attendance_period_id (nullable FK), workflow_request_id (nullable int), opened_by, opened_at, approved_by (nullable), approved_at, locked_by, locked_at, published_at, timestamps. Index on status, FKs to attendance_periods and users.

- [ ] **Step 2:** Create `100002` components migration — id (uuid), code (unique), name, category, calculation_type, percent_base_component_id (nullable FK self), default_amount (decimal 15,2 nullable), default_percent (decimal 5,2 nullable), taxable (bool default true), active (bool default true), timestamps.

- [ ] **Step 3:** Create `100003` runs migration — id (uuid), period_id (FK), run_type (varchar 20), status (varchar 20 default 'running'), formula_version (varchar 50), triggered_by (FK users), started_at, completed_at (nullable), error_summary (text nullable), timestamps. Index (period_id, status).

- [ ] **Step 4:** Create `100004` entries migration — id (uuid), run_id (FK), period_id (FK), employee_id (FK employees), contract_snapshot (json), attendance_snapshot (json), leave_snapshot (json), gross_amount, deduction_amount, net_amount (all decimal 15,2 default 0), status (varchar 20 default 'calculated'), error_message (text nullable), reviewed_by (nullable FK), reviewed_at (nullable), timestamps. UNIQUE(run_id, employee_id), index(period_id, employee_id).

- [ ] **Step 5:** Create `100005` entry_lines migration — id (bigint auto PK), entry_id (FK), component_id (FK), category (varchar 30), amount (decimal 15,2), calculation_note (varchar 255 nullable), timestamps. Index(entry_id).

- [ ] **Step 6:** Create `100006` adjustments migration — id (uuid), entry_id (FK), component_id (nullable FK), adjustment_type (varchar 20), amount (decimal 15,2), reason (text), status (varchar 20 default 'pending'), submitted_by (FK users), submitted_at, approved_by (nullable FK), approved_at (nullable), rejected_reason (text nullable), timestamps. Index(entry_id, status).

- [ ] **Step 7:** Create `100007` payslips migration — id (uuid), entry_id (FK UNIQUE), employee_id (FK), period_id (FK), gross, deductions, net (decimal 15,2), payload (json), status (varchar 20 default 'draft'), published_at (nullable), first_accessed_at (nullable), access_count (int default 0), timestamps.

- [ ] **Step 8:** Verify migrations run clean

  Run: `docker compose run --rm app php artisan migrate:fresh --seed 2>&1 | tail -35`
  Expected: all migrations run, no errors, 7 payroll tables present.

- [ ] **Step 9:** Commit

  ```bash
  git add src/backend/database/migrations/2026_07_02_1000*
  git commit -m "feat(payroll): add schema — periods, components, runs, entries, lines, adjustments, payslips"
  ```

### Task 2: Eloquent models + factories

**Files:** Create 7 models, 6 factories under `src/backend/app/Modules/Payroll/Infrastructure/Persistence/Eloquent/`

- [ ] **Step 1:** Create `PayrollPeriodModel.php` — extends Model, table `payroll_periods`, casts: dates, status string. Fillable: all columns. No guarded.

- [ ] **Step 2:** Create `PayrollComponentModel.php` — table `payroll_components`, casts: default_amount, default_percent, taxable bool, active bool.

- [ ] **Step 3:** Create `PayrollRunModel.php` — table `payroll_runs`, belongsTo period.

- [ ] **Step 4:** Create `PayrollEntryModel.php` — table `payroll_entries`, casts: snapshots array, amounts float. belongsTo run, period, employee. hasMany lines.

- [ ] **Step 5:** Create `PayrollEntryLineModel.php` — table `payroll_entry_lines`, belongsTo entry, component.

- [ ] **Step 6:** Create `PayrollAdjustmentModel.php` — table `payroll_adjustments`, belongsTo entry.

- [ ] **Step 7:** Create `PayslipModel.php` — table `payslips`, belongsTo entry, employee, period. Casts payload array.

- [ ] **Step 8:** Create factories under `Factories/` — `PayrollPeriodFactory`, `PayrollComponentFactory`, `PayrollRunFactory`, `PayrollEntryFactory`, `PayrollAdjustmentFactory`, `PayslipFactory`. Each with sensible defaults (use seq UUID for id, sample codes, etc).

- [ ] **Step 9:** Commit

  ```bash
  git add src/backend/app/Modules/Payroll/Infrastructure/Persistence/Eloquent/
  git commit -m "feat(payroll): add Eloquent models + factories"
  ```

### Task 3: Domain value objects + enums

**Files:** Create 13 files under `Domain/ValueObjects/`

- [ ] **Step 1:** Create `PeriodStatus.php`

  ```php
  <?php
  namespace App\Modules\Payroll\Domain\ValueObjects;

  enum PeriodStatus: string
  {
      case Open = 'open';
      case Calculating = 'calculating';
      case Completed = 'completed';
      case Reviewing = 'reviewing';
      case Approved = 'approved';
      case Locked = 'locked';
      case Published = 'published';

      public function canTransitionTo(self $target): bool
      {
          return match ($this) {
              self::Open => $target === self::Calculating,
              self::Calculating => $target === self::Completed || $target === self::Calculating,
              self::Completed => $target === self::Reviewing,
              self::Reviewing => $target === self::Approved || $target === self::Completed,
              self::Approved => $target === self::Locked,
              self::Locked => $target === self::Published || $target === self::Reviewing,
              self::Published => false,
          };
      }
  }
  ```

- [ ] **Step 2:** Create `RunStatus.php` — enum string: Running, Completed, Failed.

- [ ] **Step 3:** Create `ComponentCategory.php` — enum string: Base, Allowance, Bonus, Penalty, Overtime, Deduction, Insurance, Tax, Net.

- [ ] **Step 4:** Create `CalculationType.php` — enum string: FixedAmount, PercentOfComponent, ManualEntry.

- [ ] **Step 5:** Create `AdjustmentStatus.php` — enum string: Pending, Approved, Rejected. with `canTransitionTo`.

- [ ] **Step 6:** Create `PayslipStatus.php` — enum string: Draft, Published.

- [ ] **Step 7:** Create `Money.php`

  ```php
  <?php
  namespace App\Modules\Payroll\Domain\ValueObjects;

  readonly class Money
  {
      public function __construct(
          private int $amount, // stored as cents
          private string $currency = 'VND',
      ) {}

      public static function fromDecimal(float $amount): self
      {
          return new self((int) round($amount * 100));
      }

      public function toDecimal(): float
      {
          return $this->amount / 100;
      }

      public function add(self $other): self
      {
          return new self($this->amount + $other->amount, $this->currency);
      }

      public function subtract(self $other): self
      {
          return new self($this->amount - $other->amount, $this->currency);
      }

      public function lessThan(self $other): bool
      {
          return $this->amount < $other->amount;
      }

      public function greaterThanOrEqual(self $other): bool
      {
          return $this->amount >= $other->amount;
      }

      public function equals(self $other): bool
      {
          return $this->amount === $other->amount && $this->currency === $other->currency;
      }
  }
  ```

- [ ] **Step 8:** Create `PayrollFormulaResult.php` — holds gross, deduction, net as Money, and lines as array of `['component_id' => string, 'category' => string, 'amount' => Money, 'note' => ?string]`.

- [ ] **Step 9:** Create `EntryError.php` — simple DTO: employeeId, message.

- [ ] **Step 10:** Commit

  ```bash
  git add src/backend/app/Modules/Payroll/Domain/ValueObjects/
  git commit -m "feat(payroll): add domain value objects and enums"
  ```

### Task 4: Domain events + exceptions

**Files:** Create 9 events, 11 exceptions under `Domain/Events/` and `Domain/Exceptions/`

- [ ] **Step 1:** Create events — each event is a simple class implementing `ShouldBroadcast` (or plain class). Each carries relevant IDs:

  - `PayrollPeriodOpened.php` — $periodId
  - `PayrollPeriodClosed.php` — $periodId
  - `PayrollPeriodReopened.php` — $periodId
  - `PayrollRunStarted.php` — $runId, $periodId, $triggeredBy
  - `PayrollRunCompleted.php` — $runId, $periodId, $stats (total, errors)
  - `PayrollApproved.php` — $periodId, $approvedBy
  - `PayrollLocked.php` — $periodId, $lockedBy
  - `PayrollPublished.php` — $periodId, $publishedBy
  - `PayslipAccessed.php` — $payslipId, $employeeId, $accessedBy
  - `PayrollAdjusted.php` — $adjustmentId, $entryId, $adjustedBy

- [ ] **Step 2:** Create exceptions — extend `\RuntimeException`:

  - `PayrollPeriodClosedException` — "Payroll period is closed."
  - `PayrollPeriodLockedException` — "Payroll period is locked and cannot be modified."
  - `PayrollPeriodNotFoundException` — message with $periodId
  - `PayrollRunNotFoundException` — message with $runId
  - `PayrollEntryNotFoundException` — message with $entryId
  - `PayrollComponentNotFoundException` — message with $componentId
  - `DuplicatePayrollRunException` — "An active run already exists for this period."
  - `InvalidPayrollCalculationException` — net < 0
  - `PayrollNotApprovedException` — "Payroll must be approved before locking."
  - `PayrollAlreadyPublishedException` — "Payslips already published for this period."
  - `PayrollAdjustmentNotFoundException` — $adjustmentId

- [ ] **Step 3:** Commit

  ```bash
  git add src/backend/app/Modules/Payroll/Domain/Events/ src/backend/app/Modules/Payroll/Domain/Exceptions/
  git commit -m "feat(payroll): add domain events and exceptions"
  ```

### Task 5: Domain aggregate roots

**Files:** Create 6 aggregate root classes + 6 ID value objects under `Domain/Aggregates/`

- [ ] **Step 1:** Create `PayrollPeriodId.php` — UUID value object wrapping `Ramsey\Uuid\Uuid`

- [ ] **Step 2:** Create `PayrollPeriod.php`

  Key behaviors:
  - `__construct(PayrollPeriodId $id, string $periodCode, DateTimeImmutable $startDate, DateTimeImmutable $endDate, DateTimeImmutable $cutoffDate, int $openedBy)`: sets status=Open
  - `startRun()`: guard status=Open, transition Calculating, return PayrollRunStarted event
  - `completeRun()`: guard Calculating, transition Completed, return PayrollRunCompleted
  - `submitForApproval(int $workflowRequestId)`: guard Completed, transition Reviewing
  - `approve(int $approvedBy)`: guard Reviewing, transition Approved, return PayrollApproved
  - `reject()`: guard Reviewing, transition back to Completed
  - `lock(int $lockedBy)`: guard Approved, transition Locked, return PayrollLocked
  - `publish(int $publishedBy)`: guard Locked, transition Published, return PayrollPublished
  - `reopen()`: guard Locked, transition Reviewing (privileged), return PayrollPeriodReopened

- [ ] **Step 3:** Create `PayrollComponentId.php` — UUID value object.

- [ ] **Step 4:** Create `PayrollComponent.php`

  Key behaviors:
  - `__construct(PayrollComponentId $id, string $code, string $name, ComponentCategory $category, CalculationType $calculationType, ...)`
  - `deactivate()`: sets active=false
  - `updateConfig(...)`: update default_amount, default_percent, taxable

- [ ] **Step 5:** Create `PayrollRunId.php` — UUID value object.

- [ ] **Step 6:** Create `PayrollRun.php`

  Key behaviors:
  - `__construct(PayrollRunId $id, PayrollPeriodId $periodId, string $runType, string $formulaVersion, int $triggeredBy)`: status=Running
  - `complete(?string $errorSummary)`: transition to Completed/Failed
  - `fail(string $errorSummary)`: status=Failed

- [ ] **Step 7:** Create `PayrollEntryId.php` — UUID value object.

- [ ] **Step 8:** Create `PayrollEntry.php`

  Key behaviors:
  - Static factory `create(...)`: builds from run + employee + snapshots + formula result
  - `review(int $reviewedBy)`: set reviewed status
  - Guard: cannot modify if period locked

- [ ] **Step 9:** Create `PayslipId.php` — UUID value object.

- [ ] **Step 10:** Create `Payslip.php`

  Key behaviors:
  - Static factory `publishFromEntry(...)`: copies entry data into immutable payload
  - `recordAccess()`: increment access_count, set first_accessed_at on first call

- [ ] **Step 11:** Create `PayrollAdjustmentId.php` — UUID value object.

- [ ] **Step 12:** Create `PayrollAdjustment.php`

  Key behaviors:
  - `__construct(... entryId, componentId?, adjustmentType, amount, reason, submittedBy)`: status=Pending
  - `approve(int $approvedBy)`: guard Pending, transition Approved, return PayrollAdjusted event
  - `reject(int $rejectedBy, string $reason)`: guard Pending, transition Rejected

- [ ] **Step 13:** Commit

  ```bash
  git add src/backend/app/Modules/Payroll/Domain/Aggregates/
  git commit -m "feat(payroll): add domain aggregates — period, component, run, entry, payslip, adjustment"
  ```

### Task 6: Domain ports + services

**Files:** Create 3 Port interfaces, 5 Services (`Services/`)

- [ ] **Step 1:** Create `AttendanceReadPort.php`

  ```php
  <?php
  namespace App\Modules\Payroll\Domain\Ports;

  interface AttendanceReadPort
  {
      /** @return array{worked_minutes: int, overtime_minutes: int, late_minutes: int, paid_leave_minutes: int, unpaid_leave_minutes: int} */
      public function getAttendanceForEmployee(int $employeeId, \DateTimeImmutable $start, \DateTimeImmutable $end): array;
  }
  ```

- [ ] **Step 2:** Create `LeaveReadPort.php`

  ```php
  interface LeaveReadPort
  {
      /** @return array{paid_days: float, unpaid_days: float} */
      public function getLeaveForEmployee(int $employeeId, \DateTimeImmutable $start, \DateTimeImmutable $end): array;
  }
  ```

- [ ] **Step 3:** Create `EmployeeContractReadPort.php`

  ```php
  interface EmployeeContractReadPort
  {
      /** @return array{base_salary: float, effective_date: string, ...} */
      public function getContractForEmployee(int $employeeId, \DateTimeImmutable $asOf): ?array;
  }
  ```

- [ ] **Step 4:** Create `PayrollFormulaEngine.php`

  ```php
  class PayrollFormulaEngine
  {
      /** @param PayrollComponent[] $components */
      public function calculate(
          array $components,
          float $baseSalary,
          array $attendanceData,
          array $leaveData,
      ): PayrollFormulaResult { ... }
  }
  ```

  Logic:
  - Iterate components ordered by category: base, allowance, bonus, overtime, penalty, deduction, insurance, tax, net
  - For each component, resolve amount by `calculation_type`:
    - `fixed_amount`: use default_amount
    - `percent_of_component`: find base component amount × default_percent/100
    - `manual_entry`: amount = 0 (filled by adjustment later)
  - Sum into gross (base+allowance+bonus+overtime+penalty), deduction (insurance+tax+deductions)
  - net = gross - deduction
  - Collect lines array

- [ ] **Step 5:** Create `TaxCalculator.php` — `calculate(float $taxableGross, array $brackets): float`. Default: flat 10% if no brackets.

- [ ] **Step 6:** Create `InsuranceCalculator.php` — `calculate(float $baseSalary, float $allowanceTotal): array{social: float, health: float, unemployment: float}`. Default rates: social 8%, health 1.5%, unemployment 1% of (base+allowance), capped at 20× minimum wage.

- [ ] **Step 7:** Create `AttendanceBasisCalculator.php` — implements AttendanceReadPort. For now, returns zeros as stub. (Real DB query added in Task 9.)

- [ ] **Step 8:** Create `PayrollCalculator.php`

  ```php
  class PayrollCalculator
  {
      public function __construct(
          private PayrollFormulaEngine $formulaEngine,
          private AttendanceReadPort $attendancePort,
          private LeaveReadPort $leavePort,
          private EmployeeContractReadPort $contractPort,
          private TaxCalculator $taxCalculator,
          private InsuranceCalculator $insuranceCalculator,
      ) {}

      /**
       * @param array $employees array of int employee IDs
       * @param PayrollComponent[] $components
       * @return PayrollEntry[] built entries (not persisted)
       */
      public function calculateForPeriod(
          array $employees,
          array $components,
          PayrollPeriodId $periodId,
          PayrollRunId $runId,
          \DateTimeImmutable $startDate,
          \DateTimeImmutable $endDate,
      ): array { ... }
  }
  ```

  Logic: loop employees, fetch contract/attendance/leave, run formula engine, build entry.

- [ ] **Step 9:** Create repository interfaces (6 files) under `Domain/Repositories/`:
  - `PayrollPeriodRepositoryInterface.php`: `save(PayrollPeriod $period)`, `findById(PayrollPeriodId $id): ?PayrollPeriod`, `findAll(): array`
  - `PayrollComponentRepositoryInterface.php`: `save(PayrollComponent $component)`, `findById(PayrollComponentId $id): ?PayrollComponent`, `findActive(): array`
  - `PayrollRunRepositoryInterface.php`: `save(PayrollRun $run)`, `findById(PayrollRunId $id): ?PayrollRun`, `findByPeriod(PayrollPeriodId $periodId): array`
  - `PayrollEntryRepositoryInterface.php`: `save(PayrollEntry $entry)`, `findById(PayrollEntryId $id): ?PayrollEntry`, `findByPeriod(PayrollPeriodId $periodId): array`
  - `PayrollAdjustmentRepositoryInterface.php`: `save(PayrollAdjustment $adjustment)`, `findById(PayrollAdjustmentId $id): ?PayrollAdjustment`, `findByEntry(PayrollEntryId $entryId): array`
  - `PayslipRepositoryInterface.php`: `save(Payslip $payslip)`, `findById(PayslipId $id): ?Payslip`, `findByPeriod(PayrollPeriodId $periodId): array`

- [ ] **Step 10:** Commit

  ```bash
  git add src/backend/app/Modules/Payroll/Domain/Services/ src/backend/app/Modules/Payroll/Domain/Ports/ src/backend/app/Modules/Payroll/Domain/Repositories/
  git commit -m "feat(payroll): add domain services, ports, repository interfaces"
  ```

### Task 7: Repository implementations + port implementations

**Files:** Create 6 Eloquent repositories + 3 port implementations

- [ ] **Step 1:** Create `PayrollPeriodRepository.php` — implements interface, uses `PayrollPeriodModel`, maps to/from domain aggregate (Eloq → Domain mapper). Use a private `toDomain(PayrollPeriodModel $model): PayrollPeriod` and `toModel(PayrollPeriod $period): array` methods.

- [ ] **Step 2:** Create `PayrollComponentRepository.php`

- [ ] **Step 3:** Create `PayrollRunRepository.php`

- [ ] **Step 4:** Create `PayrollEntryRepository.php` — includes saving entry + lines.

- [ ] **Step 5:** Create `PayrollAdjustmentRepository.php`

- [ ] **Step 6:** Create `PayslipRepository.php`

- [ ] **Step 7:** Create `DatabaseAttendanceReadPort.php` (under `Infrastructure/Ports/`) — implements AttendanceReadPort, queries `attendance_timesheets` table directly.

- [ ] **Step 8:** Create `DatabaseLeaveReadPort.php` — queries leave_requests/leave_balances.

- [ ] **Step 9:** Create `DatabaseEmployeeContractReadPort.php` — queries employee_contracts table.

- [ ] **Step 10:** Register bindings in `AppServiceProvider.php` (modify existing):

  ```php
  $this->app->bind(PayrollPeriodRepositoryInterface::class, PayrollPeriodRepository::class);
  $this->app->bind(PayrollComponentRepositoryInterface::class, PayrollComponentRepository::class);
  $this->app->bind(PayrollRunRepositoryInterface::class, PayrollRunRepository::class);
  $this->app->bind(PayrollEntryRepositoryInterface::class, PayrollEntryRepository::class);
  $this->app->bind(PayrollAdjustmentRepositoryInterface::class, PayrollAdjustmentRepository::class);
  $this->app->bind(PayslipRepositoryInterface::class, PayslipRepository::class);
  $this->app->bind(AttendanceReadPort::class, DatabaseAttendanceReadPort::class);
  $this->app->bind(LeaveReadPort::class, DatabaseLeaveReadPort::class);
  $this->app->bind(EmployeeContractReadPort::class, DatabaseEmployeeContractReadPort::class);
  ```

- [ ] **Step 11:** Commit

  ```bash
  git add src/backend/app/Modules/Payroll/Infrastructure/Persistence/Eloquent/Repositories/ src/backend/app/Modules/Payroll/Infrastructure/Ports/ src/backend/app/Providers/
  git commit -m "feat(payroll): add Eloquent repositories and port implementations"
  ```

### Task 8: Application commands + handlers (period + run)

**Files:** Create 6 command + 6 handler files under `Application/Commands/PayrollPeriod/` and `PayrollRun/`

- [ ] **Step 1:** Create `OpenPayrollPeriodCommand.php`

  ```php
  readonly class OpenPayrollPeriodCommand
  {
      public function __construct(
          public string $periodCode,
          public DateTimeImmutable $startDate,
          public DateTimeImmutable $endDate,
          public DateTimeImmutable $cutoffDate,
          public ?int $attendancePeriodId,
          public int $openedBy,
      ) {}
  }
  ```

- [ ] **Step 2:** Create `OpenPayrollPeriodHandler.php` — validates period_code unique, creates PayrollPeriod + event.

- [ ] **Step 3:** Create `ClosePayrollPeriodCommand.php` — `public function __construct(public string $periodId, public int $closedBy)`.

- [ ] **Step 4:** Create `ClosePayrollPeriodHandler.php` — locks period (if open → locked).

- [ ] **Step 5:** Create `ReopenPayrollPeriodCommand.php` + handler.

- [ ] **Step 6:** Create `StartPayrollRunCommand.php` — `public function __construct(public string $periodId, public int $triggeredBy)`.

- [ ] **Step 7:** Create `StartPayrollRunHandler.php` — validates period open, creates run + dispatches job (sync for now).

- [ ] **Step 8:** Create `CompletePayrollRunCommand.php` — `public function __construct(public string $runId, public array $entries)`.

- [ ] **Step 9:** Create `CompletePayrollRunHandler.php` — persists entries via repository, updates run status.

- [ ] **Step 10:** Commit

  ```bash
  git add src/backend/app/Modules/Payroll/Application/Commands/PayrollPeriod/ src/backend/app/Modules/Payroll/Application/Commands/PayrollRun/ src/backend/app/Modules/Payroll/Application/CommandHandlers/
  git commit -m "feat(payroll): add application commands for period and run lifecycle"
  ```

### Task 9: Application commands + handlers (entry, adjustment, payslip)

**Files:** Create 6 more command + handler files

- [ ] **Step 1:** Create `ReviewPayrollEntryCommand.php` + handler — validates entry exists, calls `review()`.

- [ ] **Step 2:** Create `SubmitPayrollAdjustmentCommand.php` + `SubmitPayrollAdjustmentHandler.php` — validates entry mutable, creates adjustment.

- [ ] **Step 3:** Create `ApprovePayrollAdjustmentCommand.php` + handler — transitions pending→approved, updates entry if needed.

- [ ] **Step 4:** Create `RejectPayrollAdjustmentCommand.php` + handler.

- [ ] **Step 5:** Create `PublishPayslipsCommand.php` + `PublishPayslipsHandler.php` — validates period locked, creates Payslip per entry, fires PayrollPublished event.

- [ ] **Step 6:** Create query classes (for now, return empty placeholders — implement with repo calls):
  - `PayrollPeriodListQuery.php` — `getAll(array $filters = []): array`
  - `PayrollEntryListQuery.php` — `getByPeriod(string $periodId): array`
  - `PayslipListQuery.php` — `getByEmployee(int $employeeId): array`
  - `PayslipViewQuery.php` — `getById(string $payslipId): ?array`
  - `PayrollSummaryQuery.php` — `getSummary(string $periodId): array`

- [ ] **Step 7:** Commit

  ```bash
  git add src/backend/app/Modules/Payroll/Application/Commands/PayrollEntry/ src/backend/app/Modules/Payroll/Application/Commands/PayrollAdjustment/ src/backend/app/Modules/Payroll/Application/Commands/Payslip/ src/backend/app/Modules/Payroll/Application/Queries/
  git commit -m "feat(payroll): add entry review, adjustment, payslip commands and queries"
  ```

### Task 10: HTTP layer — controllers, requests, resources

**Files:** Create 6 controllers, 4 form requests, 5 resources

- [ ] **Step 1:** Create `PayrollPeriodController.php`:

  ```php
  class PayrollPeriodController extends Controller
  {
      public function __construct(
          private OpenPayrollPeriodHandler $openHandler,
          private PayrollPeriodListQuery $listQuery,
          // ...
      ) {}

      public function index(Request $request): JsonResponse
      {
          $this->authorize('payroll.period.view');
          return PayrollPeriodResource::collection(
              $this->listQuery->getAll($request->only(['status', 'from', 'to']))
          );
      }

      public function store(StorePayrollPeriodRequest $request): JsonResponse
      {
          $this->authorize('payroll.period.manage');
          $command = new OpenPayrollPeriodCommand(...);
          $this->openHandler->handle($command);
          return response()->json(['message' => 'Period created'], 201);
      }
      // ... startRun, submitApproval, approve, reject, lock, publish, close
  }
  ```

- [ ] **Step 2:** Create `PayrollRunController.php` — start run + status.

- [ ] **Step 3:** Create `PayrollEntryController.php` — index (by period), show, review.

- [ ] **Step 4:** Create `PayrollAdjustmentController.php` — index (by entry), store, approve, reject.

- [ ] **Step 5:** Create `PayslipController.php` — index (self or all), show, download (returns JSON for now).

- [ ] **Step 6:** Create `PayrollComponentController.php` — index, store, update, deactivate.

- [ ] **Step 7:** Create form requests with basic validation:
  - `StorePayrollPeriodRequest.php`: period_code required|unique, start/end/cutoff required|date, end_date >= start_date
  - `StorePayrollComponentRequest.php`: code required|unique, name required, category required, calculation_type required, amounts conditional
  - `UpdatePayrollComponentRequest.php`: same but optional
  - `SubmitPayrollAdjustmentRequest.php`: amount required|numeric, reason required, adjustment_type required

- [ ] **Step 8:** Create resources (all extend `JsonResource`):
  - `PayrollPeriodResource.php`, `PayrollRunResource.php`, `PayrollEntryResource.php`, `PayslipResource.php`, `PayrollComponentResource.php`

- [ ] **Step 9:** Create `api.php` routes under prefix `payroll`:

  ```php
  <?php
  use Illuminate\Support\Facades\Route;
  use App\Modules\Payroll\Infrastructure\Http\Controllers\{
      PayrollPeriodController, PayrollRunController, PayrollEntryController,
      PayrollAdjustmentController, PayslipController, PayrollComponentController
  };

  Route::middleware(['auth:sanctum', 'verified'])->prefix('payroll')->group(function () {
      Route::apiResource('periods', PayrollPeriodController::class)->only(['index', 'store', 'show']);
      Route::post('periods/{id}/start-run', [PayrollRunController::class, 'start']);
      Route::post('periods/{id}/submit-approval', [PayrollPeriodController::class, 'submitApproval']);
      Route::post('periods/{id}/approve', [PayrollPeriodController::class, 'approve']);
      Route::post('periods/{id}/reject', [PayrollPeriodController::class, 'reject']);
      Route::post('periods/{id}/lock', [PayrollPeriodController::class, 'lock']);
      Route::post('periods/{id}/publish', [PayrollPeriodController::class, 'publish']);

      Route::get('periods/{periodId}/entries', [PayrollEntryController::class, 'index']);
      Route::get('entries/{id}', [PayrollEntryController::class, 'show']);
      Route::post('entries/{id}/review', [PayrollEntryController::class, 'review']);

      Route::get('entries/{entryId}/adjustments', [PayrollAdjustmentController::class, 'index']);
      Route::post('entries/{entryId}/adjustments', [PayrollAdjustmentController::class, 'store']);
      Route::post('adjustments/{id}/approve', [PayrollAdjustmentController::class, 'approve']);
      Route::post('adjustments/{id}/reject', [PayrollAdjustmentController::class, 'reject']);

      Route::get('payslips', [PayslipController::class, 'index']);
      Route::get('payslips/{id}', [PayslipController::class, 'show']);
      Route::get('payslips/{id}/download', [PayslipController::class, 'download']);

      Route::apiResource('components', PayrollComponentController::class)->only(['index', 'store', 'update']);
      Route::delete('components/{id}', [PayrollComponentController::class, 'destroy']);
  });
  ```

- [ ] **Step 10:** Modify `src/backend/routes/api.php` — add `require base_path('app/Modules/Payroll/Routes/api.php');`

- [ ] **Step 11:** Commit

  ```bash
  git add src/backend/app/Modules/Payroll/Infrastructure/Http/ src/backend/app/Modules/Payroll/Routes/ src/backend/routes/api.php
  git commit -m "feat(payroll): add HTTP controllers, requests, resources, and routes"
  ```

### Task 11: Permissions — register in Identity module

**Files:** Modify `PermissionSeeder.php`, `RoleSeeder.php`

- [ ] **Step 1:** Add payroll.* permissions to `PermissionSeeder.php`:

  ```php
  // Payroll
  ['code' => 'payroll.period.view', 'name' => 'View payroll periods', 'module' => 'payroll'],
  ['code' => 'payroll.period.manage', 'name' => 'Manage payroll periods', 'module' => 'payroll'],
  ['code' => 'payroll.run.start', 'name' => 'Start payroll calculation run', 'module' => 'payroll'],
  ['code' => 'payroll.entry.view', 'name' => 'View payroll entries', 'module' => 'payroll'],
  ['code' => 'payroll.entry.review', 'name' => 'Review payroll entries', 'module' => 'payroll'],
  ['code' => 'payroll.adjustment.manage', 'name' => 'Manage payroll adjustments', 'module' => 'payroll'],
  ['code' => 'payroll.approve', 'name' => 'Approve payroll', 'module' => 'payroll'],
  ['code' => 'payroll.lock', 'name' => 'Lock payroll', 'module' => 'payroll'],
  ['code' => 'payroll.publish', 'name' => 'Publish payslips', 'module' => 'payroll'],
  ['code' => 'payroll.payslip.view', 'name' => 'View any payslip', 'module' => 'payroll'],
  ['code' => 'payroll.payslip.view_self', 'name' => 'View own payslip', 'module' => 'payroll'],
  ['code' => 'payroll.component.manage', 'name' => 'Manage payroll components', 'module' => 'payroll'],
  ```

- [ ] **Step 2:** Assign permissions to roles in `RoleSeeder.php`:
  - `SUPER_ADMIN`: all payroll.*
  - `HR_MANAGER`: period.view, entry.view, payslip.view, approve
  - `ACCOUNTANT` / `PAYROLL` (or whichever role name used): all payroll.* except component.manage, approve
  - `EMPLOYEE`: payslip.view_self

- [ ] **Step 3:** Commit

  ```bash
  git add src/backend/app/Modules/Identity/Infrastructure/Seeders/
  git commit -m "feat(payroll): register payroll permissions and role assignments"
  ```

### Task 12: Payroll component seeder

**Files:** Create `PayrollComponentSeeder.php`

- [ ] **Step 1:** Create seeder with standard VN payroll components:

  ```php
  class PayrollComponentSeeder extends Seeder
  {
      public function run(): void
      {
          $components = [
              ['code' => 'base_salary', 'name' => 'Lương cơ bản', 'category' => ComponentCategory::Base, 'calculation_type' => CalculationType::FixedAmount, 'default_amount' => 0, 'taxable' => true],
              ['code' => 'position_allowance', 'name' => 'Phụ cấp chức vụ', 'category' => ComponentCategory::Allowance, 'calculation_type' => CalculationType::PercentOfComponent, 'percent_base_component_id' => 'base_salary', 'default_percent' => 10, 'taxable' => true],
              ['code' => 'meal_allowance', 'name' => 'Phụ cấp ăn trưa', 'category' => ComponentCategory::Allowance, 'calculation_type' => CalculationType::FixedAmount, 'default_amount' => 730000, 'taxable' => false],
              ['code' => 'travel_allowance', 'name' => 'Phụ cấp đi lại', 'category' => ComponentCategory::Allowance, 'calculation_type' => CalculationType::FixedAmount, 'default_amount' => 200000, 'taxable' => false],
              ['code' => 'overtime_pay', 'name' => 'Lương tăng ca', 'category' => ComponentCategory::Overtime, 'calculation_type' => CalculationType::ManualEntry, 'default_amount' => 0, 'taxable' => true],
              ['code' => 'bonus', 'name' => 'Thưởng', 'category' => ComponentCategory::Bonus, 'calculation_type' => CalculationType::ManualEntry, 'default_amount' => 0, 'taxable' => true],
              ['code' => 'penalty', 'name' => 'Phạt', 'category' => ComponentCategory::Penalty, 'calculation_type' => CalculationType::ManualEntry, 'default_amount' => 0, 'taxable' => false],
              ['code' => 'social_insurance', 'name' => 'Bảo hiểm xã hội', 'category' => ComponentCategory::Insurance, 'calculation_type' => CalculationType::PercentOfComponent, 'percent_base_component_id' => 'base_salary', 'default_percent' => 8, 'taxable' => false],
              ['code' => 'health_insurance', 'name' => 'Bảo hiểm y tế', 'category' => ComponentCategory::Insurance, 'calculation_type' => CalculationType::PercentOfComponent, 'percent_base_component_id' => 'base_salary', 'default_percent' => 1.5, 'taxable' => false],
              ['code' => 'unemployment_insurance', 'name' => 'Bảo hiểm thất nghiệp', 'category' => ComponentCategory::Insurance, 'calculation_type' => CalculationType::PercentOfComponent, 'percent_base_component_id' => 'base_salary', 'default_percent' => 1, 'taxable' => false],
              ['code' => 'income_tax', 'name' => 'Thuế TNCN', 'category' => ComponentCategory::Tax, 'calculation_type' => CalculationType::PercentOfComponent, 'percent_base_component_id' => 'base_salary', 'default_percent' => 10, 'taxable' => false],
              ['code' => 'other_deduction', 'name' => 'Khấu trừ khác', 'category' => ComponentCategory::Deduction, 'calculation_type' => CalculationType::ManualEntry, 'default_amount' => 0, 'taxable' => false],
              ['code' => 'net_pay', 'name' => 'Lương thực nhận', 'category' => ComponentCategory::Net, 'calculation_type' => CalculationType::ManualEntry, 'default_amount' => 0, 'taxable' => false],
          ];
          // Insert, resolving percent_base_component_id references
      }
  }
  ```

  Note: use a two-pass insert — first insert all with null percent_base_component_id, then update percent_base_component_id for percent_of_component types.

- [ ] **Step 2:** Register seeder in `DatabaseSeeder.php` or module.

- [ ] **Step 3:** Commit

  ```bash
  git add src/backend/app/Modules/Payroll/Infrastructure/Seeders/
  git commit -m "feat(payroll): add payroll component seeder (VN baseline)"
  ```

### Task 13: Workflow BC integration callback

**Files:** Create integration handler for Workflow BC callback

- [ ] **Step 1:** Create a listener/subscriber class that responds to WorkflowApproved/WorkflowRejected events from Workflow BC. When workflow_request_id matches a PayrollPeriod's request:
  - Approved → call `approve()` on PayrollPeriod
  - Rejected → call `reject()` on PayrollPeriod

- [ ] **Step 2:** Register the listener in `EventServiceProvider.php`.

- [ ] **Step 3:** Commit

  ```bash
  git add ...
  git commit -m "feat(payroll): add Workflow BC callback integration"
  ```

### Task 14: Unit tests — domain aggregates

**Files:** Create `tests/Unit/Modules/Payroll/PayrollPeriodTest.php`

- [ ] **Step 1:** Write test for PayrollPeriod state machine

  ```php
  public function test_open_period_can_start_run(): void
  {
      $period = PayrollPeriod::open(...);
      $event = $period->startRun();
      $this->assertEquals(PeriodStatus::Calculating, $period->status);
      $this->assertInstanceOf(PayrollRunStarted::class, $event);
  }

  public function test_locked_period_cannot_start_run(): void
  {
      $this->expectException(PayrollPeriodLockedException::class);
      $period = PayrollPeriod::open(...);
      $period->startRun(); $period->completeRun();
      $period->submitForApproval(1); $period->approve(1); $period->lock(1);
      $period->startRun(); // Should throw
  }
  // Test all transitions, invalid transitions, reopen privilege, publish guard
  ```

- [ ] **Step 2:** Create `tests/Unit/Modules/Payroll/PayrollAdjustmentTest.php` — test approve/reject/invalid transition.

- [ ] **Step 3:** Create `tests/Unit/Modules/Payroll/PayrollFormulaEngineTest.php`

  ```php
  public function test_fixed_amount_component(): void
  {
      $engine = new PayrollFormulaEngine();
      $component = new PayrollComponent(..., CalculationType::FixedAmount, defaultAmount: Money::fromDecimal(5000000));
      $result = $engine->calculate([$component], 0, [], []);
      $this->assertEquals(5000000, $result->gross->toDecimal());
  }

  public function test_percent_of_base(): void
  {
      // 8% of 5M = 400K
  }

  public function test_net_non_negative(): void
  {
      // deductions > gross throws InvalidPayrollCalculationException
  }

  public function test_full_run_scenario(): void
  {
      // base=5M, position_allowance=10%=500K, meal=730K, social=8%=400K, health=1.5%=75K, tax=10%=500K
      // gross=5M+500K+730K=6.23M, deductions=400K+75K+500K=975K, net=5.255M
  }
  ```

- [ ] **Step 4:** Create `tests/Unit/Modules/Payroll/TaxCalculatorTest.php`, `InsuranceCalculatorTest.php`

- [ ] **Step 5:** Run unit tests

  Run: `docker compose run --rm app php artisan test tests/Unit/Modules/Payroll --compact 2>&1 | tail -20`
  Expected: all PASS

- [ ] **Step 6:** Commit

  ```bash
  git add tests/Unit/Modules/Payroll/
  git commit -m "test(payroll): add unit tests for domain aggregates and services"
  ```

### Task 15: Feature tests — full API lifecycle

**Files:** Create `tests/Feature/Modules/Payroll/PayrollApiTest.php`

- [ ] **Step 1:** Write test for full payroll lifecycle

  ```php
  public function test_full_payroll_lifecycle(): void
  {
      // 1. Login as payroll officer
      // 2. POST /api/payroll/periods — create open period → 201
      // 3. POST /api/payroll/periods/{id}/start-run → 200
      // 4. POST /api/payroll/periods/{id}/submit-approval → 200
      // 5. POST /api/payroll/periods/{id}/approve → 200
      // 6. POST /api/payroll/periods/{id}/lock → 200
      // 7. POST /api/payroll/periods/{id}/publish → 200
      // 8. GET /api/payroll/payslips → 200, has payslip
  }
  ```

- [ ] **Step 2:** Write permission boundary tests

  ```php
  public function test_employee_cannot_view_all_payslips(): void
  {
      // Login as employee
      // GET /api/payroll/payslips → 403
  }

  public function test_employee_can_view_own_payslip(): void
  {
      // Login as employee
      // GET /api/payroll/payslips (with self filter) → 200, only own payslip
  }
  ```

- [ ] **Step 3:** Write adjustment test

  ```php
  public function test_adjustment_approval_updates_entry(): void
  {
      // Create period, start run, complete
      // POST /api/payroll/entries/{entry}/adjustments → 201
      // POST /api/payroll/adjustments/{adj}/approve → 200
      // GET entry → reflects adjustment
  }
  ```

- [ ] **Step 4:** Write locked period guard test

  ```php
  public function test_locked_period_rejects_start_run(): void
  {
      // Lock period
      // POST /api/payroll/periods/{id}/start-run → 422
  }
  ```

- [ ] **Step 5:** Run feature tests

  Run: `docker compose run --rm app php artisan test tests/Feature/Modules/Payroll --compact 2>&1 | tail -20`
  Expected: all PASS

- [ ] **Step 6:** Run full test suite

  Run: `docker compose run --rm app php artisan test --compact 2>&1 | tail -30`
  Expected: no regressions, new tests pass

- [ ] **Step 7:** Commit

  ```bash
  git add tests/Feature/Modules/Payroll/
  git commit -m "test(payroll): add feature tests for full API lifecycle"
  ```

### Task 16: README and final verification

**Files:** Create `src/backend/app/Modules/Payroll/README.md`

- [ ] **Step 1:** Create README.md — brief module description, aggregates, setup, testing commands

  ```markdown
  # Payroll Module

  ## Aggregates
  - PayrollPeriod — lifecycle state machine
  - PayrollComponent — salary component catalog
  - PayrollRun — calculation run tracking
  - PayrollEntry — per-employee calculation result
  - PayrollAdjustment — inline approval adjustments
  - Payslip — published immutable snapshot

  ## Setup
  ```bash
  docker compose run --rm app php artisan migrate
  docker compose run --rm app php artisan db:seed --class=PayrollComponentSeeder
  ```

  ## Testing
  ```bash
  docker compose run --rm app php artisan test tests/Unit/Modules/Payroll
  docker compose run --rm app php artisan test tests/Feature/Modules/Payroll
  ```
  ```

- [ ] **Step 2:** Run full test suite one final time

  Run: `docker compose run --rm app php artisan test --compact 2>&1 | tail -30`
  Expected: all tests pass

- [ ] **Step 3:** Commit

  ```bash
  git add src/backend/app/Modules/Payroll/README.md
  git commit -m "docs(payroll): add README"
  ```

- [ ] **Step 4:** Verify spec ACs

  Walk through AC1–16 from spec §9 and confirm each is covered by a task:
  - AC1 (period create) → Task 1.1 + Task 8.1–2
  - AC2 (start run) → Task 8.6–7
  - AC3 (complete run creates entries) → Task 8.8–9 + Task 6.8
  - AC4 (formula engine) → Task 6.4 + Task 14.3
  - AC5 (snapshots) → Task 6.8 + Task 7.7–9
  - AC6 (submit approval) → Task 8 implicit via period.submitForApproval
  - AC7 (approve/reject) → Task 5.2 (approve/reject on period) + Task 13
  - AC8 (lock) → Task 5.2 lock()
  - AC9 (publish) → Task 9.5
  - AC10 (adjustments) → Task 9.2–4
  - AC11 (adjustment approve) → Task 9.3
  - AC12 (locked guard) → Task 5.2 guard + Task 15.4
  - AC13 (payslip access) → Task 15.2
  - AC14 (audit log) → events emitted in domain, stub for Audit BC
  - AC15 (unit/feature tests) → Task 14 + Task 15
  - AC16 (full suite) → Task 16.2
