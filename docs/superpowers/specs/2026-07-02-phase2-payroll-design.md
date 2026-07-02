# Phase 2 Payroll BC Design

Version: 0.1
Date: 2026-07-02
Status: Design approved (brainstorming)

## 1. Scope

Build Payroll module (`app/Modules/Payroll/`) as the next Phase 2 module. Covers payroll periods, component catalog, calculation runs, per-employee entries with breakdown lines, payslip publishing, and inline adjustments.

### In scope

- `PayrollPeriod` lifecycle with state machine: `open → calculating → completed → reviewing → approved → locked → published`
- `PayrollComponent` catalog with hybrid calculation types (`fixed_amount`, `percent_of_component`, `manual_entry`)
- `PayrollRun` tracking with formula versioning
- `PayrollEntry` per-employee, snapshotting contract/attendance/leave data at run time
- `PayrollEntryLine` flat breakdown per component (audit trail)
- `Payslip` published read-only snapshot from entry
- `PayrollAdjustment` inline approval (status, approved_by/at, rejected_reason)
- Hybrid formula engine: fixed formula (gross = base + allowance + bonus + overtime - penalty; deductions = insurance + tax + custom; net = gross - deductions) + component-level `calculation_type`
- TaxCalculator (flat bracket/lookup), InsuranceCalculator (fixed rate)
- AttendanceReadPort / LeaveReadPort / EmployeeContractReadPort (read-only query interfaces)
- Workflow BC integration for period approval chain
- Export CSV (flat service, no aggregate)
- Full unit + feature test suite
- Audit events for all sensitive transitions

### Out of scope

- `PayrollExport` aggregate (flat CSV export from service, deferred)
- VN-specific tax bracket engine (tax = component `manual_entry`; bracket seeder added later if needed)
- Notification BC integration (payslip publish notification deferred)
- Workflow BC for adjustments (inline approval matches Attendance pattern)

## 2. Architecture

Strict DDD tactical pattern with 3 layers, mirroring Attendance/Leave/Shift modules.

```
Module/Payroll/
  Domain/         — Pure PHP, no Laravel deps
  Application/    — Commands/Handlers + Queries
  Infrastructure/ — Eloquent, HTTP controllers, seeders, routes
```

**Dependency:** Domain ← Application ← Infrastructure.

**Ports/Adapters:** Domain defines `AttendanceReadPort`, `LeaveReadPort`, `EmployeeContractReadPort`. Infrastructure implements via direct DB queries against existing tables (anti-corruption layer). No event sync.

**Workflow BC integration:** PayrollPeriod has `workflow_request_id`. Submit → WorkflowRequest created in Workflow BC. Workflow approved/rejected callback updates PayrollPeriod status.

### State Machine

```
                    ┌─ rejected ─┐
                    ↓            │
open → calculating → completed → reviewing → approved → locked → published
                       ↑  adjust           ↑ wfl-approve
                       └───────────────────┘
```

Correction from locked → privileged `reopen` (sets back to `reviewing`).

## 3. Module Layout

```
app/Modules/Payroll/
  Domain/
    Aggregates/
      PayrollPeriod/
        PayrollPeriod.php, PayrollPeriodId.php
      PayrollComponent/
        PayrollComponent.php, PayrollComponentId.php
      PayrollRun/
        PayrollRun.php, PayrollRunId.php
      PayrollEntry/
        PayrollEntry.php, PayrollEntryId.php
      Payslip/
        Payslip.php, PayslipId.php
      PayrollAdjustment/
        PayrollAdjustment.php, PayrollAdjustmentId.php
    ValueObjects/
      PeriodStatus.php, RunStatus.php, ComponentCategory.php
      CalculationType.php, PayrollFormulaResult.php
      Money.php, TaxBracket.php, InsuranceRate.php
      EntryError.php, PayslipStatus.php, AdjustmentStatus.php
    Services/
      PayrollCalculator.php              # engine: entries → amounts
      PayrollFormulaEngine.php           # eval hybrid formula
      AttendanceBasisCalculator.php      # read port consumer
      TaxCalculator.php                  # basic bracket (flat)
      InsuranceCalculator.php            # basic rate
    Ports/
      AttendanceReadPort.php
      LeaveReadPort.php
      EmployeeContractReadPort.php
    Events/
      PayrollPeriodOpened.php, PayrollPeriodClosed.php
      PayrollRunStarted.php, PayrollRunCompleted.php
      PayrollApproved.php, PayrollLocked.php, PayrollPublished.php
      PayslipAccessed.php, PayrollAdjusted.php
    Repositories/
      PayrollPeriodRepositoryInterface.php
      PayrollComponentRepositoryInterface.php
      PayrollRunRepositoryInterface.php
      PayrollEntryRepositoryInterface.php
      PayslipRepositoryInterface.php
      PayrollAdjustmentRepositoryInterface.php
    Exceptions/
      PayrollPeriodClosedException.php
      PayrollPeriodLockedException.php
      PayrollPeriodNotFoundException.php
      PayrollRunNotFoundException.php
      PayrollEntryNotFoundException.php
      PayrollComponentNotFoundException.php
      DuplicatePayrollRunException.php
      InvalidPayrollCalculationException.php
      PayrollNotApprovedException.php
      PayrollAlreadyPublishedException.php
      PayrollAdjustmentNotFoundException.php
      DuplicatePendingAdjustmentException.php
  Application/
    Commands/PayrollPeriod/
      OpenPayrollPeriodCommand.php
      ClosePayrollPeriodCommand.php
      ReopenPayrollPeriodCommand.php
    CommandHandlers/PayrollPeriod/
      OpenPayrollPeriodHandler.php
      ClosePayrollPeriodHandler.php
      ReopenPayrollPeriodHandler.php
    Commands/PayrollRun/
      StartPayrollRunCommand.php
      CompletePayrollRunCommand.php
    CommandHandlers/PayrollRun/
      StartPayrollRunHandler.php
      CompletePayrollRunHandler.php
    Commands/PayrollEntry/
      ReviewPayrollEntryCommand.php
    CommandHandlers/PayrollEntry/
      ReviewPayrollEntryHandler.php
    Commands/PayrollAdjustment/
      SubmitPayrollAdjustmentCommand.php
      ApprovePayrollAdjustmentCommand.php
      RejectPayrollAdjustmentCommand.php
    CommandHandlers/PayrollAdjustment/
      SubmitPayrollAdjustmentHandler.php
      ApprovePayrollAdjustmentHandler.php
      RejectPayrollAdjustmentHandler.php
    Commands/Payslip/
      PublishPayslipsCommand.php
    CommandHandlers/Payslip/
      PublishPayslipsHandler.php
    Queries/
      PayrollPeriodListQuery.php
      PayrollEntryListQuery.php
      PayslipListQuery.php
      PayslipViewQuery.php
      PayrollSummaryQuery.php
  Infrastructure/
    Http/
      Controllers/
        PayrollPeriodController.php
        PayrollRunController.php
        PayrollEntryController.php
        PayrollAdjustmentController.php
        PayslipController.php
        PayrollComponentController.php
      Requests/
        StorePayrollPeriodRequest.php
        StorePayrollComponentRequest.php
        UpdatePayrollComponentRequest.php
        SubmitPayrollAdjustmentRequest.php
      Resources/
        PayrollPeriodResource.php
        PayrollRunResource.php
        PayrollEntryResource.php
        PayslipResource.php
        PayrollComponentResource.php
    Persistence/
      Eloquent/
        PayrollPeriodModel.php
        PayrollComponentModel.php
        PayrollRunModel.php
        PayrollEntryModel.php
        PayrollEntryLineModel.php
        PayslipModel.php
        PayrollAdjustmentModel.php
        Repositories/
          PayrollPeriodRepository.php
          PayrollComponentRepository.php
          PayrollRunRepository.php
          PayrollEntryRepository.php
          PayslipRepository.php
          PayrollAdjustmentRepository.php
        Factories/
          PayrollPeriodFactory.php
          PayrollComponentFactory.php
          PayrollRunFactory.php
          PayrollEntryFactory.php
          PayslipFactory.php
          PayrollAdjustmentFactory.php
    Seeders/
      PayrollComponentSeeder.php
  Routes/
    api.php
```

## 4. Domain Model

### 4.1 PayrollPeriod

| Field | Type | Notes |
|-------|------|-------|
| id | PayrollPeriodId | UUID |
| period_code | string | e.g. `2026-06`, unique |
| start_date | DateImmutable | |
| end_date | DateImmutable | |
| cutoff_date | DateImmutable | last date for attendance data |
| status | PeriodStatus | enum: open/calculating/completed/reviewing/approved/locked/published |
| attendance_period_id | ?int | FK reference |
| workflow_request_id | ?int | FK to workflow_requests |
| opened_by | int | user_id |
| opened_at | DateTimeImmutable | |
| approved_by | ?int | |
| approved_at | ?DateTimeImmutable | |
| locked_by | ?int | |
| locked_at | ?DateTimeImmutable | |
| published_at | ?DateTimeImmutable | |

**Transitions:**

- `open → calculating`: start-run
- `calculating → completed`: async calc done
- `completed → reviewing`: submit for approval (creates WorkflowRequest)
- `reviewing → approved`: Workflow approved
- `reviewing → completed`: Workflow rejected
- `reviewing → completed`: manual reject (no WFL)
- `approved → locked`: lock
- `locked → published`: publish payslips
- `locked → reviewing`: privileged reopen

**Invariants:**

- Locked periods immutable except via privileged reopen.
- Cannot publish without locked status.
- Cannot start run on non-open period.
- `period_code` unique across all periods.

### 4.2 PayrollComponent

| Field | Type | Notes |
|-------|------|-------|
| id | PayrollComponentId | UUID |
| code | string | unique, e.g. `base_salary` |
| name | string | |
| category | ComponentCategory | enum: base/allowance/bonus/penalty/overtime/deduction/insurance/tax/net |
| calculation_type | CalculationType | enum: fixed_amount/percent_of_component/manual_entry |
| percent_base_component_id | ?uuid | FK, only for percent_of_component |
| default_amount | ?Money | for fixed_amount |
| default_percent | ?float | for percent_of_component |
| taxable | bool | included in taxable gross for tax calc |
| active | bool | |

Invariants:
- `percent_of_component` requires `percent_base_component_id`.
- `manual_entry` components have no default (amount entered at run/adjustment time).
- Net category must have `is_net` flag (computed, not input).

### 4.3 PayrollRun

| Field | Type | Notes |
|-------|------|-------|
| id | PayrollRunId | UUID |
| period_id | PayrollPeriodId | |
| run_type | string | initial/recalc/correction |
| status | RunStatus | running/completed/failed |
| formula_version | string | snapshotted config or git hash |
| triggered_by | int | user_id |
| started_at | DateTimeImmutable | |
| completed_at | ?DateTimeImmutable | |
| error_summary | ?string | nullable |

**Invariants:**
- One active `running` run per period at a time.
- `completed` run finalizes; next run requires a new period lifecycle transition.

### 4.4 PayrollEntry

| Field | Type | Notes |
|-------|------|-------|
| id | PayrollEntryId | UUID |
| run_id | PayrollRunId | |
| period_id | PayrollPeriodId | |
| employee_id | int | FK |
| contract_snapshot | array | effective-dated salary info at run time |
| attendance_snapshot | array | worked/OT/late/leave minutes |
| leave_snapshot | array | paid/unpaid leave days |
| gross_amount | Money | sum base+allowance+bonus+overtime-penalty |
| deduction_amount | Money | sum insurance+tax+deductions |
| net_amount | Money | gross - deduction |
| status | string | calculated/reviewed/error |
| error_message | ?string | |
| reviewed_by | ?int | |
| reviewed_at | ?DateTimeImmutable | |

Invariants:
- `gross_amount >= deduction_amount` (net >= 0) — enforced, with error if violates.
- Unique per (run_id, employee_id).

### 4.5 PayrollEntryLine

| Field | Type | Notes |
|-------|------|-------|
| id | int | auto |
| entry_id | uuid | FK to payroll_entries |
| component_id | uuid | FK to payroll_components |
| category | string | denormalized for query |
| amount | Money | computed or entered |
| calculation_note | ?string | e.g. "base_salary * 0.08" |

### 4.6 Payslip

| Field | Type | Notes |
|-------|------|-------|
| id | PayslipId | UUID |
| entry_id | uuid | FK UNIQUE |
| employee_id | int | FK |
| period_id | uuid | FK |
| gross | Money | |
| deductions | Money | |
| net | Money | |
| payload | array | full JSON snapshot of entry + lines |
| status | string | draft/published |
| published_at | ?DateTimeImmutable | |
| first_accessed_at | ?DateTimeImmutable | |
| access_count | int | |

Invariants:
- Publishing creates payslip from locked entry only.
- Payslip is immutable after publish.

### 4.7 PayrollAdjustment

| Field | Type | Notes |
|-------|------|-------|
| id | PayrollAdjustmentId | UUID |
| entry_id | uuid | FK |
| component_id | ?uuid | FK nullable |
| adjustment_type | string | add/subtract/override |
| amount | Money | |
| reason | string | |
| status | AdjustmentStatus | pending/approved/rejected |
| submitted_by | int | |
| submitted_at | DateTimeImmutable | |
| approved_by | ?int | |
| approved_at | ?DateTimeImmutable | |
| rejected_reason | ?string | |

Invariants:
- Only `pending` → `approved|rejected`.
- Adjustments can only be submitted while entry is in `calculated` or `reviewed` status.
- Adjustment approved → triggers `PayrollAdjusted` event.

### 4.8 Domain Services

**PayrollCalculator:** orchestrates the full calculation for a run:
1. Fetch all active employees (via Employee contract port).
2. For each employee: fetch contract snapshot, attendance data, leave data.
3. Run `PayrollFormulaEngine` per employee → populates entry + entry lines.
4. Mark entry OK/error.

**PayrollFormulaEngine:**
- Fixed formula: iterate components in category order (base → allowance → bonus → overtime → penalty → deduction → insurance → tax → net).
- For each component, resolve amount based on `calculation_type`:
  - `fixed_amount`: use `default_amount`.
  - `percent_of_component`: calculate `base.amount * default_percent / 100`.
  - `manual_entry`: default 0 (filled by adjustment).
- Sum lines into gross/deduction/net.

**AttendanceBasisCalculator:** implements `AttendanceReadPort`. For a given `employee_id` and date range, returns: worked_minutes, overtime_minutes, late_minutes, early_leave_minutes, paid_leave_minutes, unpaid_leave_minutes. Queries `attendance_timesheets` directly.

**TaxCalculator:** simple bracket. Takes taxable_gross, returns amount via configured brackets (flat seeder).

**InsuranceCalculator:** takes base_salary + allowance, returns social/health/unemployment rates (seeder).

## 5. Database Schema

```sql
CREATE TABLE payroll_periods (
    id CHAR(36) PRIMARY KEY,
    period_code VARCHAR(20) NOT NULL UNIQUE,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    cutoff_date DATE NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'open',
    attendance_period_id INT UNSIGNED NULL,
    workflow_request_id INT UNSIGNED NULL,
    opened_by INT UNSIGNED NOT NULL,
    opened_at DATETIME NOT NULL,
    approved_by INT UNSIGNED NULL,
    approved_at DATETIME NULL,
    locked_by INT UNSIGNED NULL,
    locked_at DATETIME NULL,
    published_at DATETIME NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    INDEX (status),
    FOREIGN KEY (attendance_period_id) REFERENCES attendance_periods(id),
    FOREIGN KEY (opened_by) REFERENCES users(id),
    FOREIGN KEY (approved_by) REFERENCES users(id),
    FOREIGN KEY (locked_by) REFERENCES users(id)
);

CREATE TABLE payroll_components (
    id CHAR(36) PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    category VARCHAR(30) NOT NULL,
    calculation_type VARCHAR(30) NOT NULL,
    percent_base_component_id CHAR(36) NULL,
    default_amount DECIMAL(15,2) NULL,
    default_percent DECIMAL(5,2) NULL,
    taxable TINYINT(1) NOT NULL DEFAULT 1,
    active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    FOREIGN KEY (percent_base_component_id) REFERENCES payroll_components(id)
);

CREATE TABLE payroll_runs (
    id CHAR(36) PRIMARY KEY,
    period_id CHAR(36) NOT NULL,
    run_type VARCHAR(20) NOT NULL DEFAULT 'initial',
    status VARCHAR(20) NOT NULL DEFAULT 'running',
    formula_version VARCHAR(50) NOT NULL,
    triggered_by INT UNSIGNED NOT NULL,
    started_at DATETIME NOT NULL,
    completed_at DATETIME NULL,
    error_summary TEXT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    INDEX (period_id, status),
    FOREIGN KEY (period_id) REFERENCES payroll_periods(id),
    FOREIGN KEY (triggered_by) REFERENCES users(id)
);

CREATE TABLE payroll_entries (
    id CHAR(36) PRIMARY KEY,
    run_id CHAR(36) NOT NULL,
    period_id CHAR(36) NOT NULL,
    employee_id INT UNSIGNED NOT NULL,
    contract_snapshot JSON NOT NULL,
    attendance_snapshot JSON NOT NULL,
    leave_snapshot JSON NOT NULL,
    gross_amount DECIMAL(15,2) NOT NULL DEFAULT 0,
    deduction_amount DECIMAL(15,2) NOT NULL DEFAULT 0,
    net_amount DECIMAL(15,2) NOT NULL DEFAULT 0,
    status VARCHAR(20) NOT NULL DEFAULT 'calculated',
    error_message TEXT NULL,
    reviewed_by INT UNSIGNED NULL,
    reviewed_at DATETIME NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    UNIQUE (run_id, employee_id),
    INDEX (period_id, employee_id),
    FOREIGN KEY (run_id) REFERENCES payroll_runs(id),
    FOREIGN KEY (period_id) REFERENCES payroll_periods(id),
    FOREIGN KEY (employee_id) REFERENCES employees(id)
);

CREATE TABLE payroll_entry_lines (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    entry_id CHAR(36) NOT NULL,
    component_id CHAR(36) NOT NULL,
    category VARCHAR(30) NOT NULL,
    amount DECIMAL(15,2) NOT NULL DEFAULT 0,
    calculation_note VARCHAR(255) NULL,
    created_at DATETIME NOT NULL,
    INDEX (entry_id),
    FOREIGN KEY (entry_id) REFERENCES payroll_entries(id),
    FOREIGN KEY (component_id) REFERENCES payroll_components(id)
);

CREATE TABLE payroll_adjustments (
    id CHAR(36) PRIMARY KEY,
    entry_id CHAR(36) NOT NULL,
    component_id CHAR(36) NULL,
    adjustment_type VARCHAR(20) NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    reason TEXT NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'pending',
    submitted_by INT UNSIGNED NOT NULL,
    submitted_at DATETIME NOT NULL,
    approved_by INT UNSIGNED NULL,
    approved_at DATETIME NULL,
    rejected_reason TEXT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    INDEX (entry_id, status),
    FOREIGN KEY (entry_id) REFERENCES payroll_entries(id),
    FOREIGN KEY (component_id) REFERENCES payroll_components(id),
    FOREIGN KEY (submitted_by) REFERENCES users(id),
    FOREIGN KEY (approved_by) REFERENCES users(id)
);

CREATE TABLE payslips (
    id CHAR(36) PRIMARY KEY,
    entry_id CHAR(36) NOT NULL UNIQUE,
    employee_id INT UNSIGNED NOT NULL,
    period_id CHAR(36) NOT NULL,
    gross DECIMAL(15,2) NOT NULL,
    deductions DECIMAL(15,2) NOT NULL,
    net DECIMAL(15,2) NOT NULL,
    payload JSON NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'draft',
    published_at DATETIME NULL,
    first_accessed_at DATETIME NULL,
    access_count INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    FOREIGN KEY (entry_id) REFERENCES payroll_entries(id),
    FOREIGN KEY (employee_id) REFERENCES employees(id),
    FOREIGN KEY (period_id) REFERENCES payroll_periods(id)
);
```

## 6. API Design

### Payroll Periods
- `GET /api/payroll/periods` — paginated list (filterable: status, date range)
- `POST /api/payroll/periods` — create new (body: period_code, start_date, end_date, cutoff_date, attendance_period_id)
- `GET /api/payroll/periods/{id}` — single with run summary
- `POST /api/payroll/periods/{id}/start-run` — start calculation run
- `POST /api/payroll/periods/{id}/submit-approval` — submit to Workflow BC (body: workflow_template_id)
- `POST /api/payroll/periods/{id}/approve` — approve (from workflow callback)
- `POST /api/payroll/periods/{id}/reject` — reject (from workflow callback)
- `POST /api/payroll/periods/{id}/lock` — lock
- `POST /api/payroll/periods/{id}/publish` — publish payslips
- `POST /api/payroll/periods/{id}/close` — close (set status=published not needed; lock+publish = terminal)

### Payroll Entries
- `GET /api/payroll/periods/{periodId}/entries` — paginated, filter by status/employee
- `GET /api/payroll/entries/{id}` — single with lines
- `POST /api/payroll/entries/{id}/review` — mark reviewed

### Payroll Adjustments
- `GET /api/payroll/entries/{entryId}/adjustments` — list for entry
- `POST /api/payroll/entries/{entryId}/adjustments` — submit (body: component_id?, adjustment_type, amount, reason)
- `POST /api/payroll/adjustments/{id}/approve` — approve
- `POST /api/payroll/adjustments/{id}/reject` — reject (body: reason)

### Payslips
- `GET /api/payroll/payslips` — list (self for employee, all for payroll role)
- `GET /api/payroll/payslips/{id}` — view
- `GET /api/payroll/payslips/{id}/download` — PDF/HTML download (future: returns plain data now)

### Payroll Components
- `GET /api/payroll/components` — active list
- `POST /api/payroll/components` — create
- `PATCH /api/payroll/components/{id}` — update (only if no active run)
- `DELETE /api/payroll/components/{id}` — deactivate (soft)

## 7. Permissions

| Permission | Scope | Default Roles |
|------------|-------|---------------|
| `payroll.period.view` | View periods | HR Manager, Payroll |
| `payroll.period.manage` | Create/edit periods | Payroll |
| `payroll.run.start` | Start calculation run | Payroll |
| `payroll.entry.view` | View all entries | HR Manager, Payroll |
| `payroll.entry.review` | Review entries | Payroll |
| `payroll.adjustment.manage` | Submit/approve adjustments | Payroll |
| `payroll.approve` | Approve payroll (period) | HR Manager |
| `payroll.lock` | Lock payroll | Payroll |
| `payroll.publish` | Publish payslips | Payroll |
| `payroll.payslip.view` | View any payslip | HR Manager, Payroll |
| `payroll.payslip.view_self` | View own payslip | Employee |
| `payroll.component.manage` | Manage component catalog | Admin |

Data scope: payroll entry/payslip visibility follows employee's branch/department data scope.

## 8. Testing Strategy

**Unit tests** (Domain + Application):
- PayrollPeriod state machine transitions (all valid + invalid)
- PayrollFormulaEngine calculation (component permutations)
- PayrollAdjustment status transitions
- TaxCalculator / InsuranceCalculator with sample inputs
- PayrollEntry invariants (net >= 0, unique per run/employee)

**Feature tests** (API):
- Full payroll run lifecycle: create period → start-run → complete → submit → approve → lock → publish
- Adjustment submit/approve/reject
- Payslip access permission boundary (self vs other)
- Data scope filtering on entries/payslips
- Auth enforcement (each permission tested)
- Audit log assertions on sensitive actions

## 9. Acceptance Criteria

AC1. Payroll periods can be created with code, dates, and status `open`.
AC2. Starting a run transitions period to `calculating` and creates `PayrollRun`.
AC3. Completing a run creates `PayrollEntry` per employee with entry_lines.
AC4. Formula engine correctly calculates gross/deductions/net using component catalog.
AC5. Contract/attendance/leave data is snapshotted per entry.
AC6. Submitting for approval creates `WorkflowRequest` in Workflow BC.
AC7. Workflow approve → period status `approved`; workflow reject → `completed`.
AC8. Locking transitions `approved → locked`.
AC9. Publishing transitions `locked → published` and creates payslips.
AC10. Adjustments can be submitted on `calculated`/`reviewed` entries.
AC11. Adjustment approve updates entry amounts and emits event.
AC12. Locked/published periods reject mutation (start-run, adjustment, edit).
AC13. Payslip access is restricted: self + payroll/HR roles.
AC14. Audit log entries for: run start, run complete, approve, reject, lock, publish, adjustment approve.
AC15. All unit tests pass; all feature tests pass.
AC16. Full backend test suite passes.

## 10. Implementation Order

1. **Migration + Eloquent models** — all 7 tables, models, factories
2. **Domain layer** — aggregates, value objects, exceptions, events, repository interfaces, ports
3. **Domain services** — PayrollFormulaEngine, TaxCalculator, InsuranceCalculator, PayrollCalculator
4. **Port implementations** — AttendanceReadPort, LeaveReadPort, EmployeeContractReadPort (Infrastructure)
5. **Application layer** — commands + handlers per aggregate
6. **Repository implementations** — all 6 Eloquent repositories
7. **Workflow BC integration** — WorkflowRequest creation + callback handling
8. **HTTP layer** — controllers, requests, resources, routes
9. **Permissions** — register permissions in Identity module
10. **Seeder** — PayrollComponentSeeder with VN baseline components
11. **Tests** — unit tests first, then feature tests
12. **Seeder refresh + migration** — validate full flow

## 11. Dependencies

| Dependency | Nature | Status |
|------------|--------|--------|
| Employee BC | Contract read port (effective-dated salary) | ✅ Exists |
| Attendance BC | Timesheet read port | ✅ Exists |
| Leave BC | Leave balance/request read port | ✅ Exists |
| Workflow BC | Approval request create/callback | ✅ Exists |
| Identity BC | Permission checks, user resolving | ✅ Exists |
| Configuration BC | Formula version, component settings (future) | ✅ Exists |
| Audit BC | Event recording | ✅ Exists |

## 12. Risks

1. **Calculation performance** — PayrollCalculator loops per employee; large (>1,000) sets need queue job. Mitigation: handler dispatches job, not inline.
2. **Formula engine simplicity** — Hybrid type covers 90% VN use cases. Expression eval may be needed for complex clients. Ponytail: add simple expression parser later without changing aggregates.
3. **Workflow BC dependency** — If Workflow BC down, period stuck in `reviewing`. Mitigation: manual override permission for admin.
4. **Snapshot staleness** — Attendance/leave snapshots taken at run time; late adjustments to attendance after run start need recalc run. Mitigation: document process → officer must recalc after relevant adjustments.
