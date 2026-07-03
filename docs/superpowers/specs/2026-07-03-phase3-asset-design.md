# Phase 3 Asset BC Design

## 1. Scope

Build Asset module at `src/backend/app/Modules/Asset/` as the last Phase 3 sub-project.

In scope:
- Asset inventory CRUD
- Asset assignment to employee
- Asset return with condition + settlement amount
- Asset status actions: available, maintenance, lost, damaged
- Employee asset obligations read-model
- Offboarding final-clearance integration for unresolved asset obligations
- Permission seeding, API routes, unit/feature tests, audit event emission

Out of scope:
- Reservation queue
- Maintenance workflow engine
- Procurement/vendor flows
- Accounting/depreciation
- Barcode/QR scanning

## 2. Goals

- Track company assets through inventory, custody, return, and condition changes.
- Prevent more than one active assignment for the same asset.
- Expose employee asset obligations for Offboarding clearance checks.
- Follow Identity-style module structure and existing Phase 3 module patterns.

## 3. Module Structure

Use standard module layout:

- `Application/Commands`, `Application/CommandHandlers`, `Application/Queries`, `Application/QueryHandlers`
- `Domain/Aggregates/AssetItem`, `Domain/Aggregates/AssetAssignment`, `Domain/Aggregates/AssetReturn`
- `Domain/Events`, `Domain/Exceptions`, `Domain/Repositories`, `Domain/ValueObjects`
- `Infrastructure/Http/Controllers`
- `Infrastructure/Persistence/Eloquent/Models`
- `Infrastructure/Persistence/Eloquent/Repositories`
- `Infrastructure/Seeders`
- `Routes/api.php`

## 4. Domain Model

### 4.1 Aggregates

**AssetItem**
- Fields: `id`, `assetCode`, `assetType`, `serialNumber`, `name`, `condition`, `status`, `notes`, timestamps
- Behaviors: create, update details, markAvailable, markMaintenance, markLost, markDamaged, markAssigned, markReturned

**AssetAssignment**
- Fields: `id`, `assetItemId`, `employeeId`, `issuedAt`, `expectedReturnAt`, `conditionOnIssue`, `status`, timestamps
- Behaviors: create, completeReturn

**AssetReturn**
- Fields: `id`, `assetAssignmentId`, `returnedAt`, `conditionOnReturn`, `notes`, `settlementAmount`, timestamps
- Behaviors: create only

### 4.2 Value Objects / Enums

- `AssetItemId`, `AssetAssignmentId`, `AssetReturnId`
- `AssetItemStatus`: `available`, `assigned`, `maintenance`, `lost`, `damaged`
- `AssetAssignmentStatus`: `active`, `returned`
- `AssetCondition`: `new`, `good`, `fair`, `poor`, `damaged`, `lost`

### 4.3 Invariants

- `asset_code` unique
- Active assignment unique per asset
- Only `available` asset can be assigned
- Returned assignment cannot be returned again
- Status actions cannot move an actively assigned asset except through return flow
- Offboarding clearance fails when employee has active asset obligations

## 5. Persistence

Create tables from Phase 3 ERD with minimal additive fields needed by full-ops scope.

### `asset_items`

Columns:
- `id` UUID PK
- `asset_code` string unique
- `asset_type` string
- `name` string
- `serial_number` string nullable
- `condition` string
- `status` string
- `notes` text nullable
- timestamps

Indexes:
- unique `(asset_code)`
- index `(status)`
- index `(asset_type, status)`

### `asset_assignments`

Columns:
- `id` UUID PK
- `asset_item_id` UUID FK
- `employee_id` UUID
- `issued_at` timestamp
- `expected_return_at` timestamp nullable
- `condition_on_issue` string
- `status` string
- timestamps

Indexes/constraints:
- index `(employee_id, status)`
- index `(asset_item_id, status)`
- app-level uniqueness for one active assignment per asset

### `asset_returns`

Columns:
- `id` UUID PK
- `asset_assignment_id` UUID FK unique
- `returned_at` timestamp
- `condition_on_return` string
- `notes` text nullable
- `settlement_amount` decimal(12,2) default 0
- timestamps

## 6. API Design

Prefix: `/api/v1/assets`
Middleware: `auth:sanctum`

### Inventory
- `GET /items` -> list assets, optional filters `status`, `asset_type`, `employee_id`
- `POST /items` -> create asset item
- `GET /items/{id}` -> show asset item
- `PUT /items/{id}` -> update asset item details
- `DELETE /items/{id}` -> hard delete allowed only when never assigned, otherwise 422

### Status actions
- `POST /items/{id}/mark-available`
- `POST /items/{id}/mark-maintenance`
- `POST /items/{id}/mark-lost`
- `POST /items/{id}/mark-damaged`

### Assignment / return
- `GET /assignments` -> optional filters `employee_id`, `asset_item_id`, `status`
- `POST /assignments` -> assign asset to employee
- `GET /assignments/{id}` -> show assignment
- `POST /assignments/{id}/return` -> return asset, create return record

### Obligations
- `GET /employees/{employeeId}/obligations` -> active assignments summary for employee

Response shape follows existing modules: `{"data": ...}` or `{"message": ...}`.

## 7. Permissions

Seeder: `AssetPermissionSeeder`

- `asset.item.view`
- `asset.item.create`
- `asset.item.update`
- `asset.item.delete`
- `asset.item.mark-status`
- `asset.assignment.view`
- `asset.assignment.create`
- `asset.assignment.return`
- `asset.obligation.view`

## 8. Application Flow

### Assign asset
1. Validate input.
2. Load `AssetItem`.
3. Ensure status is `available`.
4. Ensure no active assignment exists for asset.
5. Create `AssetAssignment` with `active`.
6. Mark item `assigned`.
7. Persist both in one transaction.

### Return asset
1. Validate input.
2. Load assignment + item.
3. Ensure assignment is `active`.
4. Create `AssetReturn`.
5. Mark assignment `returned`.
6. Set item status from return condition:
   - `lost` -> `lost`
   - `damaged` -> `damaged`
   - `poor` -> `maintenance`
   - otherwise -> `available`
7. Persist in one transaction.

### Status action
1. Load item.
2. Reject if item currently has active assignment.
3. Transition to requested status.
4. Persist + emit audit event.

### Offboarding obligation check
1. Replace `App\Modules\Offboarding\Infrastructure\Services\AssetCheckService` stub.
2. Resolve employee from Offboarding plan aggregate.
3. Query active assignments for employee.
4. If any active assignment exists, return `obligationsMet=false` + pending asset codes.
5. If employee cannot be resolved from plan, throw domain/runtime error (fail closed).

## 9. Errors

- `AssetItemNotFoundException`
- `AssetAssignmentNotFoundException`
- `AssetAlreadyAssignedException`
- `AssetNotAvailableException`
- `AssetAssignmentAlreadyReturnedException`
- `AssetHasAssignmentHistoryException`
- `AssetStatusTransitionException`
- `AssetObligationsNotMetException` reused by Offboarding

HTTP mapping mirrors existing modules:
- not found -> 404
- validation/domain precondition failure -> 422
- unauthenticated -> 401
- unauthorized permission -> 403

## 10. Auditing / Events

Emit domain events for:
- asset item created/updated/status changed
- asset assigned
- asset returned

Audit integration should follow existing project conventions; no separate lifecycle event table in this phase.

## 11. Testing

### Unit
- Domain status transitions for AssetItem
- Assignment guard: only available asset can assign
- Return guard: cannot double return
- Obligation query/service behavior

### Feature
- Auth boundary on item list/create
- Inventory CRUD happy path
- Assign asset success + duplicate active assignment rejection
- Return asset success + item status update
- Status action blocked for actively assigned asset
- Offboarding final clearance blocked when active asset obligation exists

Test locations:
- `src/backend/tests/Unit/Modules/Asset`
- `src/backend/tests/Feature/Modules/Asset`
- one Offboarding feature test update for asset clearance integration

## 12. Acceptance Criteria

- AC1: Asset inventory CRUD works through `/api/v1/assets/items`
- AC2: Asset can be assigned only when available
- AC3: Asset cannot have more than one active assignment
- AC4: Asset return creates return record and closes assignment
- AC5: Return updates item status based on return condition
- AC6: Manual status action endpoints work for unassigned assets
- AC7: Employee obligations endpoint lists active assignments
- AC8: Offboarding final clearance fails when obligations exist
- AC9: Asset permissions seeded and enforced
- AC10: Module follows standard DDD/module layout and route loader pattern
- AC11: Unit + feature tests added, including auth/permission boundaries

## 13. Implementation Notes

- Keep controllers thin; orchestration stays in handlers.
- Use existing JSON response style from Training/Offboarding.
- No speculative reservation or maintenance workflows.
- Prefer app-level invariant check for active assignment uniqueness unless project DB patterns already use partial unique indexes.
