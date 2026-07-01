# Phase 1 Organization Design

Version: 0.1
Date: 2026-07-01
Status: Design approved (brainstorming)

## 1. Scope

Build Organization module (`app/Modules/Organization/`) as the next sub-project of iHRM Phase 1 Core Platform. Covers branch, department, and position management with tree hierarchy, permission integration, and full test suite.

**In scope:** Branch CRUD + activate/deactivate, Department CRUD + move subtree + activate/deactivate (parent_id tree), Position CRUD + activate/deactivate, org-tree flattened endpoint, seed data (branches, departments, positions, permission codes), permission code integration with Identity module, full test suite (unit + application + feature).

**Out of scope:** Org chart visual (frontend), matrix organization (multiple parents), cost center / business unit (Phase 2+), position hierarchy / job grade (Phase 2+), data scope enforcement within Organization (consumer modules handle their own), manager_employee_id FK to Employee (Employee module later).

## 2. Architecture

**Pattern:** Strict DDD tactical with 3 layers (mirror Identity module).

```
Module/Organization/
  Domain/         — Pure PHP, no Laravel deps
  Application/    — Commands/Queries + Handlers, orchestrates domain
  Infrastructure/ — Eloquent, HTTP controllers, middleware, seeders, routes
```

**Dependency:** Domain ← Application ← Infrastructure. Domain knows nothing outside itself.

## 3. Module Layout

```
app/Modules/Organization/
  Domain/
    Aggregates/Branch/
      Branch.php, BranchCode.php, BranchStatus.php
    Aggregates/Department/
      Department.php, DepartmentCode.php, DepartmentStatus.php
    Aggregates/Position/
      Position.php, PositionCode.php, PositionStatus.php
    Events/
      BranchCreated, BranchUpdated, BranchActivated, BranchDeactivated
      DepartmentCreated, DepartmentUpdated, DepartmentMoved, DepartmentActivated, DepartmentDeactivated
      PositionCreated, PositionUpdated, PositionActivated, PositionDeactivated
    Repositories/
      BranchRepositoryInterface.php
      DepartmentRepositoryInterface.php
      PositionRepositoryInterface.php
    Exceptions/
      BranchNotFoundException.php, DuplicateBranchCodeException.php, BranchHasActiveDepartmentsException.php
      DepartmentNotFoundException.php, DuplicateDepartmentCodeException.php, CircularMoveException.php, DepartmentNotInSameBranchException.php
      PositionNotFoundException.php, DuplicatePositionCodeException.php
  Application/
    Commands/
      CreateBranchCommand.php, UpdateBranchCommand.php, ActivateBranchCommand.php, DeactivateBranchCommand.php
      CreateDepartmentCommand.php, UpdateDepartmentCommand.php, MoveDepartmentCommand.php, ActivateDepartmentCommand.php, DeactivateDepartmentCommand.php
      CreatePositionCommand.php, UpdatePositionCommand.php, ActivatePositionCommand.php, DeactivatePositionCommand.php
    CommandHandlers/
      (same structure as Commands)
    Queries/
      GetBranchQuery.php, ListBranchesQuery.php
      GetDepartmentQuery.php, ListDepartmentsQuery.php
      GetPositionQuery.php, ListPositionsQuery.php
      GetOrgTreeQuery.php
    QueryHandlers/
      (same structure as Queries)
  Infrastructure/
    Persistence/
      Eloquent/
        BranchModel.php, DepartmentModel.php, PositionModel.php
      Repositories/
        EloquentBranchRepository.php, EloquentDepartmentRepository.php, EloquentPositionRepository.php
    Http/
      Controllers/
        BranchController.php, DepartmentController.php, PositionController.php, OrgTreeController.php
      Requests/
        CreateBranchRequest.php, UpdateBranchRequest.php
        CreateDepartmentRequest.php, UpdateDepartmentRequest.php, MoveDepartmentRequest.php
        CreatePositionRequest.php, UpdatePositionRequest.php
      Resources/
        BranchResource.php, DepartmentResource.php, PositionResource.php, OrgTreeResource.php
      Middleware/
        (use Identity's PermissionMiddleware via route alias)
    Seeders/
      OrgStructureSeeder.php
  Routes/api.php
```

## 4. Domain Model

### 4.1 Branch Aggregate

```
Branch {
  id: BranchId (UUID)
  code: BranchCode (VO, unique, immutable)
  name: BranchName (VO)
  address: ?Address (VO)
  phone: ?Phone (VO)
  email: ?Email (VO)
  status: BranchStatus (active|inactive)

  static create(code, name, address?, phone?, email?): self
  update(name, address, phone, email): void     — emits BranchUpdated
  activate(): void                               — emits BranchActivated
  deactivate(): void                             — emits BranchDeactivated

  Invariants:
  - Code is unique across all branches (repository-level check before persist).
  - Deactivation fails if branch has active child departments.
  - Code is immutable after creation.
}

BranchStatus: enum { active, inactive }
BranchCode: VO — uppercase alphanumeric+dash, max 50
BranchName: VO — string 1..255
BranchId: VO — wraps UUID v7 string
```

### 4.2 Department Aggregate

```
Department {
  id: DepartmentId (UUID)
  code: DepartmentCode (VO, unique within branch)
  name: DepartmentName (VO)
  branchId: BranchId (FK to Branch)
  parentId: ?DepartmentId (self-ref tree)
  managerId: ?EmployeeId (nullable, set when Employee module exists)
  status: DepartmentStatus (active|inactive)

  static create(branchId, code, name, parentId?): self
  update(name, managerId?): void                — emits DepartmentUpdated
  moveTo(newParentId): void                     — emits DepartmentMoved
  activate(): void                               — emits DepartmentActivated
  deactivate(): void                             — emits DepartmentDeactivated

  Invariants:
  - Code is unique within (branch_id, code) tuple (repository check).
  - Cannot move to itself (id === newParentId) → CircularMoveException.
  - Cannot move to descendant of self (cycle detection) → CircularMoveException.
  - New parent must belong to same branch → DepartmentNotInSameBranchException.
  - Cannot deactivate if has active child departments.
  - Code is immutable after creation.
}

DepartmentStatus: enum { active, inactive }
DepartmentCode: VO — uppercase alphanumeric+dash, max 50
DepartmentName: VO — string 1..255
DepartmentId: VO — wraps UUID v7 string
```

### 4.3 Position Aggregate

```
Position {
  id: PositionId (UUID)
  code: PositionCode (VO, unique)
  name: PositionName (VO)
  level: ?int (1..n, optional job grade)
  description: ?string
  status: PositionStatus (active|inactive)

  static create(code, name, level?, description?): self
  update(name, level, description): void        — emits PositionUpdated
  activate(): void                               — emits PositionActivated
  deactivate(): void                             — emits PositionDeactivated

  Invariants:
  - Code is unique across all positions (repository check).
  - Code is immutable after creation.
  - Deactivation check against active employee references is deferred to Employee module.
}

PositionStatus: enum { active, inactive }
PositionCode: VO — uppercase alphanumeric+dash, max 50
PositionName: VO — string 1..255
PositionId: VO — wraps UUID v7 string
```

### 4.4 Value Objects

- `BranchId`, `DepartmentId`, `PositionId`: UUID v7 string wrapper
- `BranchCode`, `DepartmentCode`, `PositionCode`: uppercase alphanumeric + dash, max 50, regex `/^[A-Z][A-Z0-9-]{1,49}$/`, throws on invalid format
- `BranchName`, `DepartmentName`, `PositionName`: non-empty string max 255
- `Email`, `Phone`, `Address`: lightweight string VOs (reuse from Shared or define per-module)

### 4.5 Domain Events

Each aggregate mutation emits one domain event (named pattern `<Entity><PastTenseAction>`):

| Event | Payload |
|-------|---------|
| `BranchCreated` | branch_id, code, name |
| `BranchUpdated` | branch_id, changed_fields |
| `BranchActivated` | branch_id |
| `BranchDeactivated` | branch_id |
| `DepartmentCreated` | department_id, branch_id, code, parent_id?, name |
| `DepartmentUpdated` | department_id, changed_fields |
| `DepartmentMoved` | department_id, old_parent_id, new_parent_id |
| `DepartmentActivated` | department_id |
| `DepartmentDeactivated` | department_id |
| `PositionCreated` | position_id, code, name |
| `PositionUpdated` | position_id, changed_fields |
| `PositionActivated` | position_id |
| `PositionDeactivated` | position_id |

All events implement `App\Modules\Shared\Domain\DomainEvent` interface (or equivalent). Audit module subscribes to all.

## 5. Data Layer

### 5.1 Database Schema

```sql
-- branches
CREATE TABLE branches (
    id UUID PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    address TEXT NULL,
    phone VARCHAR(30) NULL,
    email VARCHAR(255) NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'active',
    created_at TIMESTAMP WITH TIME ZONE NULL,
    updated_at TIMESTAMP WITH TIME ZONE NULL
);
CREATE INDEX idx_branches_status ON branches(status);

-- departments
CREATE TABLE departments (
    id UUID PRIMARY KEY,
    branch_id UUID NOT NULL REFERENCES branches(id) ON DELETE RESTRICT,
    parent_id UUID NULL REFERENCES departments(id) ON DELETE RESTRICT,
    code VARCHAR(50) NOT NULL,
    name VARCHAR(255) NOT NULL,
    manager_employee_id UUID NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'active',
    created_at TIMESTAMP WITH TIME ZONE NULL,
    updated_at TIMESTAMP WITH TIME ZONE NULL,
    UNIQUE (branch_id, code)
);
CREATE INDEX idx_departments_branch_status ON departments(branch_id, status);
CREATE INDEX idx_departments_parent ON departments(parent_id);

-- positions
CREATE TABLE positions (
    id UUID PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    level INT NULL,
    description TEXT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'active',
    created_at TIMESTAMP WITH TIME ZONE NULL,
    updated_at TIMESTAMP WITH TIME ZONE NULL
);
CREATE INDEX idx_positions_status ON positions(status);
```

### 5.2 Eloquent Models

- `BranchModel` — maps `branches` table, hasMany `DepartmentModel`
- `DepartmentModel` — maps `departments` table, belongsTo `BranchModel`, belongsTo self `parent`, hasMany self `children`; belongsTo `BranchModel`
- `PositionModel` — maps `positions` table

### 5.3 Repositories (Interfaces + Eloquent Implementations)

**Common interface methods per repository** (mirror Identity):
- `findById(Id): AggregateRoot` — throws `NotFoundException` if not found
- `findByCode(string): ?AggregateRoot` — nullable return
- `save(AggregateRoot): void` — persist via Eloquent
- `saveAndDispatch(AggregateRoot): void` — persist + dispatch domain events
- `existsByCode(string): bool`

**DepartmentRepository-specific:**
- `findChildrenOf(DepartmentId): Collection` — direct children
- `hasActiveChildren(DepartmentId): bool` — guard check
- `findDescendantIds(DepartmentId): array` — for cycle detection (recursive)
- `existsByCodeAndBranch(code, BranchId): bool` — unique within branch

## 6. Application Layer

### 6.1 Command Handlers

Each handler is a single-use-case class, mirrors Identity pattern.
- Checks authorization (via injected `AuthorizationService` from Identity module).
- Loads aggregate from repository.
- Calls aggregate method.
- Saves via repository with event dispatch.

Guards (failure → exception → HTTP 422/409 via exception handler):

| Command | Guard | Exception | HTTP |
|---------|-------|-----------|------|
| CreateBranch | Duplicate code | DuplicateBranchCodeException | 409 |
| DeactivateBranch | Has active departments | BranchHasActiveDepartmentsException | 409 |
| CreateDepartment | Duplicate code in branch | DuplicateDepartmentCodeException | 409 |
| MoveDepartment | Self-move | CircularMoveException | 422 |
| MoveDepartment | Descendant cycle | CircularMoveException | 422 |
| MoveDepartment | Different branch parent | DepartmentNotInSameBranchException | 422 |
| DeactivateDepartment | Has active children | DepartmentHasActiveChildrenException | 409 |
| CreatePosition | Duplicate code | DuplicatePositionCodeException | 409 |

### 6.2 Query Handlers

Simple read-only queries returning DTOs/arrays. No authorization inside query — authorization at controller level via PermissionMiddleware.

- `GetBranchQuery(id)` → `BranchResource` payload
- `ListBranchesQuery(?status, pagination)` → paginated result
- `GetDepartmentQuery(id)` → `DepartmentResource` payload
- `ListDepartmentsQuery(?branch_id, ?parent_id, ?status, pagination)` → paginated result
- `GetPositionQuery(id)` → `PositionResource` payload
- `ListPositionsQuery(?status, pagination)` → paginated result
- `GetOrgTreeQuery(?branch_id)` → tree structure: `{branch: ..., departments: [{...children}]}`

### 6.3 Authorization

Handlers call `AuthorizationService::userHasPermission(userId, permissionCode)` before mutation.
Identity module's `PermissionMiddleware` enforces at route level for read endpoints.
Permission codes: all `organization.*` (see Section 8).

## 7. HTTP API

### 7.1 Routes (all under `/api/v1`, `auth:sanctum` + `permission:{code}`)

| Method | Endpoint | Permission | Notes |
|--------|----------|------------|-------|
| GET | /branches | organization.branch.list | Paginated, ?status filter |
| POST | /branches | organization.branch.create | |
| GET | /branches/{branch} | organization.branch.view | Route model binding |
| PATCH | /branches/{branch} | organization.branch.update | |
| POST | /branches/{branch}/activate | organization.branch.update | |
| POST | /branches/{branch}/deactivate | organization.branch.update | |
| GET | /departments | organization.department.list | Filter: branch_id, parent_id, status |
| POST | /departments | organization.department.create | |
| GET | /departments/{department} | organization.department.view | |
| PATCH | /departments/{department} | organization.department.update | |
| POST | /departments/{department}/move | organization.department.move | Body: {new_parent_id} |
| POST | /departments/{department}/activate | organization.department.update | |
| POST | /departments/{department}/deactivate | organization.department.update | |
| GET | /positions | organization.position.list | Paginated, ?status filter |
| POST | /positions | organization.position.create | |
| GET | /positions/{position} | organization.position.view | |
| PATCH | /positions/{position} | organization.position.update | |
| POST | /positions/{position}/activate | organization.position.update | |
| POST | /positions/{position}/deactivate | organization.position.update | |
| GET | /org-tree | organization.tree.view | ?branch_id filter, nested response |

### 7.2 Response Format

- Single resource: `{data: {id, code, name, ...}}`
- List: `{data: [...], meta: {current_page, per_page, total, last_page}}`
- Error: `{error: {code, message, details?, trace_id}}` — matches Identity envelope

### 7.3 FormRequest Validation

| Field | Rule |
|-------|------|
| code | required, string, regex `/^[A-Z][A-Z0-9-]{1,49}$/`, unique check |
| name | required, string, max:255 |
| branch_id | required (department), exists:branches,id |
| parent_id | nullable (department), exists:departments,id, no self-cycle |
| level | nullable, integer, min:1, max:99 |
| description | nullable, string, max:1000 |
| status | nullable, string, in:active,inactive |

### 7.4 Error Codes

| Code | HTTP | Trigger |
|------|------|---------|
| PERMISSION_DENIED | 403 | Missing organization.* permission |
| BRANCH_NOT_FOUND | 404 | Branch ID not found |
| DEPARTMENT_NOT_FOUND | 404 | Department ID not found |
| POSITION_NOT_FOUND | 404 | Position ID not found |
| DUPLICATE_BRANCH_CODE | 409 | Branch code already exists |
| DUPLICATE_DEPARTMENT_CODE | 409 | Department code already exists in branch |
| DUPLICATE_POSITION_CODE | 409 | Position code already exists |
| BRANCH_HAS_ACTIVE_DEPARTMENTS | 409 | Cannot deactivate branch |
| DEPARTMENT_HAS_ACTIVE_CHILDREN | 409 | Cannot deactivate department |
| CIRCULAR_MOVE | 422 | Cannot move to self or descendant |
| DEPARTMENT_NOT_IN_SAME_BRANCH | 422 | Parent department in different branch |
| VALIDATION_ERROR | 422 | FormRequest validation |
| UNAUTHENTICATED | 401 | Missing/invalid token |

## 8. Seed Data

### 8.1 Permission Catalog

```php
// Updated in PermissionSeeder or OrgPermissionSeeder
[
    ['code' => 'organization.branch.list',     'module' => 'organization', 'action' => 'list'],
    ['code' => 'organization.branch.view',     'module' => 'organization', 'action' => 'view'],
    ['code' => 'organization.branch.create',   'module' => 'organization', 'action' => 'create'],
    ['code' => 'organization.branch.update',   'module' => 'organization', 'action' => 'update'],
    ['code' => 'organization.branch.activate', 'module' => 'organization', 'action' => 'activate'],
    ['code' => 'organization.branch.deactivate','module' => 'organization', 'action' => 'deactivate'],
    ['code' => 'organization.department.list',     'module' => 'organization', 'action' => 'list'],
    ['code' => 'organization.department.view',     'module' => 'organization', 'action' => 'view'],
    ['code' => 'organization.department.create',   'module' => 'organization', 'action' => 'create'],
    ['code' => 'organization.department.update',   'module' => 'organization', 'action' => 'update'],
    ['code' => 'organization.department.move',     'module' => 'organization', 'action' => 'move'],
    ['code' => 'organization.department.activate', 'module' => 'organization', 'action' => 'activate'],
    ['code' => 'organization.department.deactivate','module' => 'organization', 'action' => 'deactivate'],
    ['code' => 'organization.position.list',     'module' => 'organization', 'action' => 'list'],
    ['code' => 'organization.position.view',     'module' => 'organization', 'action' => 'view'],
    ['code' => 'organization.position.create',   'module' => 'organization', 'action' => 'create'],
    ['code' => 'organization.position.update',   'module' => 'organization', 'action' => 'update'],
    ['code' => 'organization.position.activate', 'module' => 'organization', 'action' => 'activate'],
    ['code' => 'organization.position.deactivate','module' => 'organization', 'action' => 'deactivate'],
    ['code' => 'organization.tree.view',         'module' => 'organization', 'action' => 'view'],
]
```

### 8.2 Role Updates (extend existing RoleSeeder)

| Role | Organization permissions |
|------|-------------------------|
| SUPER_ADMIN | all organization.* |
| HR_MANAGER | all organization.* |
| EMPLOYEE | organization.tree.view |

### 8.3 Org Structure Seeder

Branches:
| Code | Name |
|------|------|
| HCM-HQ | Ho Chi Minh Head Office |
| HN-OFFICE | Ha Noi Office |

Departments under HCM-HQ:
| Code | Name | Parent |
|------|------|--------|
| BOARD | Ban Giam Doc | null |
| HR | Nhan Su | null |
| ACC | Ke Toan | null |
| IT | Ky Thuat | null |
| SALES | Kinh Doanh | null |
| IT-DEV | Phong Phat Trien | IT |
| IT-OPS | Phong Van Hanh | IT |

Positions:
| Code | Name | Level |
|------|------|-------|
| DEV | Developer | 3 |
| SR_DEV | Senior Developer | 4 |
| TL | Team Leader | 5 |
| HR_EXEC | HR Executive | 3 |
| HR_MGR | HR Manager | 5 |
| ACCT | Accountant | 3 |
| SALES_EXEC | Sales Executive | 3 |
| MGR | General Manager | 6 |

## 9. Testing Strategy

| Layer | Key test classes | Coverage |
|-------|-----------------|----------|
| Domain unit | `BranchTest`, `DepartmentTest`, `PositionTest` | VO validation (invalid code), status transitions, event emission, invariants (cycle check on move, has-children guard on deactivate) |
| Application | Per-command HandlerTest (e.g. `CreateBranchHandlerTest`, `MoveDepartmentHandlerTest`) | Happy path + each guard condition + duplicate code + not found + permission denied |
| Feature HTTP | `BranchApiTest`, `DepartmentApiTest`, `PositionApiTest`, `OrgTreeApiTest`, `OrgPermissionEnforcementTest` | 200/4xx responses per endpoint, payload shape, auth required, pagination, permission enforcement (403) |

Key domain unit test cases:

- `Department::moveTo(department.id)` → throws CircularMoveException
- `Department::moveTo(descendant.id)` → throws CircularMoveException (cycle)
- `Department::moveTo(parent in different branch)` → throws DepartmentNotInSameBranchException
- `Branch::deactivate()` when departments exist with status=active → throws BranchHasActiveDepartmentsException
- `Department::deactivate()` when children exist with status=active → throws DepartmentHasActiveChildrenException
- `BranchCode('invalid 123')` → throws; `BranchCode('HCM-HQ')` → OK
- `Department('test')` (lowercase code) → throws; `Department('TEST')` → OK

## 10. Acceptance Criteria

1. All 21+ API endpoints functional and documented.
2. `GET /departments/{id}` includes `parent_id` and `branch_id`.
3. Move department cycle/self-reference is prevented (422 regression test passes).
4. Branch deactivate blocked when departments exist (409 regression test passes).
5. All seed data loads via `php artisan db:seed --class=OrgStructureSeeder`.
6. All 22 `organization.*` permissions exist in DB after seed.
7. `HR_MANAGER` role has full `organization.*` permission set (verified by test).
8. All tests pass (unit + application + feature).
9. Audit events emitted for every mutation (verified by Audit test listener).
10. PermissionMiddleware returns 403 for requests without matching permission.
11. Code style and module structure match Identity module.
12. No N+1 queries in list endpoints (eager load parent/branch relations).
13. README documents module.

## 11. Implementation Order

1. Migration files (3: branches, departments, positions) + indexes
2. Eloquent models (BranchModel, DepartmentModel, PositionModel)
3. Domain layer: value objects, aggregates, events, exceptions, repository interfaces
4. Application layer: command/query classes + handlers
5. Infrastructure persistence: Eloquent repositories
6. Infrastructure HTTP: controllers, FormRequests, Resources, routes (in Organization/Routes/api.php)
7. Routes: update `src/backend/routes/api.php` to load Organization routes
8. Seeders: update Identity's PermissionSeeder/RoleSeeder + new OrgStructureSeeder
9. Test suite: domain unit → application integration → feature HTTP
10. Module README

## 12. Dependencies & Integration Points

- **Identity module**: AuthorizationService, PermissionMiddleware, Sanctum auth, PermissionSeeder/RoleSeeder extend
- **Audit module**: subscribes to Organization domain events
- **Employee module (planned)**: will reference Branch/Department/Position IDs

## 13. Risks

- `manager_employee_id` FK deferred → app-level nullable field until Employee module; no DB FK until then.
- Department cycle detection via recursive query in repository may be slow on deep trees; acceptable for Phase 1 (< 10k depts). If needed, add materialized path in Phase 2.
- Position deactivation guard against active employee references deferred to Employee module (pessimistic: position is deactivate-able even if employees reference it until Employee module exists).
