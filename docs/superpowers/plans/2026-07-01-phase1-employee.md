# Phase 1 Employee Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build the Phase 1 Employee module with employee master profile, contracts, MinIO-backed documents, permissions, and tests.

**Architecture:** Strict DDD tactical structure under `src/backend/app/Modules/Employee`: Domain is pure PHP, Application orchestrates commands/queries, Infrastructure owns Eloquent/HTTP/MinIO/routes/seeders. Three aggregate roots: `Employee`, `Contract`, and `EmployeeDocument`; no giant aggregate.

**Tech Stack:** Laravel 12, PHP 8.4, PostgreSQL 16, UUID primary keys, Sanctum, MinIO/S3 filesystem, Eloquent repositories, PHPUnit.

---

## File Map

- `src/backend/app/Modules/Employee/Domain/**`: value objects, enums, aggregates, events, exceptions, repository contracts.
- `src/backend/app/Modules/Employee/Application/**`: commands, handlers, queries, query handlers, thin domain services.
- `src/backend/app/Modules/Employee/Infrastructure/**`: Eloquent models/repositories, HTTP controllers/requests/resources, routes, seeders, MinIO adapter.
- `src/backend/database/migrations/2026_07_01_02000*_create_employee_*.php`: Employee tables.
- `src/backend/app/Modules/Identity/Infrastructure/Seeders/PermissionSeeder.php`: add `employee.*` permissions.
- `src/backend/app/Modules/Identity/Infrastructure/Seeders/RoleSeeder.php`: grant `employee.*` to `SUPER_ADMIN` and `HR_MANAGER`.
- `src/backend/routes/api.php`: load Employee routes.
- `src/backend/tests/Unit/Modules/Employee/**`: domain/application tests.
- `src/backend/tests/Feature/Modules/Employee/**`: API + permission + MinIO tests.

---

### Task 1: Database migrations

**Files:**
- Create: `src/backend/database/migrations/2026_07_01_020001_create_employees_table.php`
- Create: `src/backend/database/migrations/2026_07_01_020002_create_employee_history_table.php`
- Create: `src/backend/database/migrations/2026_07_01_020003_create_employee_contracts_table.php`
- Create: `src/backend/database/migrations/2026_07_01_020004_create_employee_documents_table.php`

- [ ] **Step 1: Create employees migration**

Use UUID PK, unique `employee_code`, nullable org refs, nullable `user_id`, and indexes from `docs/superpowers/specs/2026-07-01-phase1-employee-design.md:377`.

Run: `cd src/backend && php artisan migrate`
Expected: migration succeeds.

- [ ] **Step 2: Create employee_history migration**

Use append-only table: `id`, `employee_id`, `branch_id`, `department_id`, `position_id`, `effective_at`, `created_at`. No `updated_at`.

Run: `cd src/backend && php artisan migrate`
Expected: migration succeeds.

- [ ] **Step 3: Create employee_contracts migration**

Use UUID PK, `employee_id`, unique `contract_number`, `contract_type`, dates, `status`, `predecessor_contract_id`, `base_salary`, nullable `position_id`, timestamps, indexes.

Run: `cd src/backend && php artisan migrate`
Expected: migration succeeds.

- [ ] **Step 4: Create employee_documents migration**

Use UUID PK, `employee_id`, document metadata columns, file descriptor columns, dates, `status`, timestamps, indexes.

Run: `cd src/backend && php artisan migrate`
Expected: migration succeeds.

- [ ] **Step 5: Commit schema**

```bash
git add src/backend/database/migrations/2026_07_01_020001_create_employees_table.php \
  src/backend/database/migrations/2026_07_01_020002_create_employee_history_table.php \
  src/backend/database/migrations/2026_07_01_020003_create_employee_contracts_table.php \
  src/backend/database/migrations/2026_07_01_020004_create_employee_documents_table.php
git commit -m "feat(employee): add employee schema"
```

---

### Task 2: Eloquent models

**Files:**
- Create: `src/backend/app/Modules/Employee/Infrastructure/Persistence/Eloquent/EmployeeModel.php`
- Create: `src/backend/app/Modules/Employee/Infrastructure/Persistence/Eloquent/ContractModel.php`
- Create: `src/backend/app/Modules/Employee/Infrastructure/Persistence/Eloquent/EmployeeDocumentModel.php`

- [ ] **Step 1: Add EmployeeModel**

Mirror Organization models: `$table = 'employees'`, `$keyType = 'string'`, `$incrementing = false`, fillable explicit fields, casts for `dob` and timestamps. Add relations: `manager()`, `contracts()`, `documents()`, `history()`.

- [ ] **Step 2: Add ContractModel**

Use table `employee_contracts`, UUID string key, fillable explicit fields, casts for `start_date`, `end_date`, `sign_date`, `base_salary` decimal.

- [ ] **Step 3: Add EmployeeDocumentModel**

Use table `employee_documents`, UUID string key, fillable explicit fields, casts for `issue_date`, `expiry_date`, `file_size` int.

- [ ] **Step 4: Smoke test autoload**

Run: `cd src/backend && php artisan test --filter=ExampleTest --compact`
Expected: PASS or no model autoload errors.

- [ ] **Step 5: Commit models**

```bash
git add src/backend/app/Modules/Employee/Infrastructure/Persistence/Eloquent
git commit -m "feat(employee): add eloquent models"
```

---

### Task 3: Domain value objects and enums

**Files:**
- Create: `src/backend/app/Modules/Employee/Domain/Aggregates/Employee/*.php`
- Create: `src/backend/app/Modules/Employee/Domain/Aggregates/Contract/*.php`
- Create: `src/backend/app/Modules/Employee/Domain/Aggregates/EmployeeDocument/*.php`
- Test: `src/backend/tests/Unit/Modules/Employee/Domain/*Test.php`

- [ ] **Step 1: Write failing value object tests**

Test `EmployeeId::generate()`, `fromString()`, `equals()`, `EmployeeCode` non-empty, `PersonalName` non-empty, `EmployeeStatus` transitions via policy, `DateRange::overlaps()`, `DocumentDescriptor` rejects empty path.

Run: `cd src/backend && php artisan test tests/Unit/Modules/Employee/Domain --compact`
Expected: FAIL because classes missing.

- [ ] **Step 2: Implement minimal VOs/enums**

Create:
- `EmployeeId`, `ContractId`, `EmployeeDocumentId`: same pattern as Identity/Organization IDs.
- `EmployeeCode`, `PersonalName`, `Address`.
- `EmployeeStatus`, `ContractStatus`, `DocumentStatus` PHP enums.
- `DateRange`, `ContractTerm`, `DocumentDescriptor`, `EmploymentSnapshot`.

- [ ] **Step 3: Run domain VO tests**

Run: `cd src/backend && php artisan test tests/Unit/Modules/Employee/Domain --compact`
Expected: PASS.

- [ ] **Step 4: Commit VOs**

```bash
git add src/backend/app/Modules/Employee/Domain/Aggregates src/backend/tests/Unit/Modules/Employee/Domain
git commit -m "feat(employee): add domain value objects"
```

---

### Task 4: Domain events and exceptions

**Files:**
- Create: `src/backend/app/Modules/Employee/Domain/Events/*.php`
- Create: `src/backend/app/Modules/Employee/Domain/Exceptions/*.php`

- [ ] **Step 1: Add events**

Create event classes from spec section 5: Employee, Contract, EmployeeDocument events. Use public readonly constructor args. Keep no Laravel deps.

- [ ] **Step 2: Add exceptions**

Create exceptions extending `App\Modules\Shared\Exceptions\AppException`:
- Employee: not found 404, duplicate code 409, invalid transition 422, active contracts 409.
- Contract: not found 404, overlap 422, renewal 422.
- Document: not found 404, expired 422.

- [ ] **Step 3: Static smoke test**

Run: `cd src/backend && php artisan test tests/Unit/Modules/Employee/Domain --compact`
Expected: PASS/no autoload errors.

- [ ] **Step 4: Commit events/exceptions**

```bash
git add src/backend/app/Modules/Employee/Domain/Events src/backend/app/Modules/Employee/Domain/Exceptions
git commit -m "feat(employee): add domain events and exceptions"
```

---

### Task 5: Domain aggregates and policies

**Files:**
- Create: `src/backend/app/Modules/Employee/Domain/Aggregates/Employee/Employee.php`
- Create: `src/backend/app/Modules/Employee/Domain/Aggregates/Contract/Contract.php`
- Create: `src/backend/app/Modules/Employee/Domain/Aggregates/EmployeeDocument/EmployeeDocument.php`
- Create: `src/backend/app/Modules/Employee/Application/Services/EmployeeLifecyclePolicy.php`
- Create: `src/backend/app/Modules/Employee/Application/Services/ContractRenewalPolicy.php`
- Test: `src/backend/tests/Unit/Modules/Employee/Domain/EmployeeTest.php`
- Test: `src/backend/tests/Unit/Modules/Employee/Domain/ContractTest.php`
- Test: `src/backend/tests/Unit/Modules/Employee/Domain/EmployeeDocumentTest.php`

- [ ] **Step 1: Write failing aggregate tests**

Cover:
- Employee starts `draft` and emits `EmployeeCreated`.
- Invalid transition `draft → resigned` throws.
- Valid transition `draft → active` emits `EmployeeStatusChanged`.
- `changeEmployment()` appends history and emits event.
- Contract definite without end date throws.
- Contract active overlap policy throws.
- Document upload emits event; replace archives current and returns new active document.

Run: `cd src/backend && php artisan test tests/Unit/Modules/Employee/Domain --compact`
Expected: FAIL.

- [ ] **Step 2: Implement Employee aggregate**

Implement only spec behaviors: `create()`, `updatePersonalInfo()`, `changeEmployment()`, `changeManager()`, `changeStatus()`, `linkUserAccount()`, `releaseEvents()`.

- [ ] **Step 3: Implement Contract aggregate**

Implement `create()`, `activate()`, `renew()`, `terminate()`, `cancel()`, `markExpired()`, `releaseEvents()`.

- [ ] **Step 4: Implement EmployeeDocument aggregate**

Implement `upload()`, `replace()`, `archive()`, `markExpired()`, `releaseEvents()`.

- [ ] **Step 5: Run aggregate tests**

Run: `cd src/backend && php artisan test tests/Unit/Modules/Employee/Domain --compact`
Expected: PASS.

- [ ] **Step 6: Commit aggregates**

```bash
git add src/backend/app/Modules/Employee/Domain/Aggregates src/backend/app/Modules/Employee/Application/Services src/backend/tests/Unit/Modules/Employee/Domain
git commit -m "feat(employee): add domain aggregates"
```

---

### Task 6: Repository contracts and persistence mapping

**Files:**
- Create: `src/backend/app/Modules/Employee/Domain/Repositories/EmployeeRepositoryInterface.php`
- Create: `src/backend/app/Modules/Employee/Domain/Repositories/ContractRepositoryInterface.php`
- Create: `src/backend/app/Modules/Employee/Domain/Repositories/EmployeeDocumentRepositoryInterface.php`
- Create: `src/backend/app/Modules/Employee/Infrastructure/Persistence/Repositories/EloquentEmployeeRepository.php`
- Create: `src/backend/app/Modules/Employee/Infrastructure/Persistence/Repositories/EloquentContractRepository.php`
- Create: `src/backend/app/Modules/Employee/Infrastructure/Persistence/Repositories/EloquentEmployeeDocumentRepository.php`
- Modify: `src/backend/app/Providers/AppServiceProvider.php`

- [ ] **Step 1: Define repository interfaces**

Methods:
- Employee: `findById`, `findByCode`, `findByUserId`, `findAllPaginated`, `existsByCode`, `save`.
- Contract: `findById`, `findByEmployeeId`, `findActiveByEmployeeId`, `findAllPaginated`, `save`.
- Document: `findById`, `findByEmployeeId`, `findAllPaginated`, `save`.

- [ ] **Step 2: Implement Eloquent repositories**

Map Eloquent rows to domain aggregates and back. Follow Organization repository style. Use `PaginatedCollection` for pagination.

- [ ] **Step 3: Bind interfaces**

Add bindings in `AppServiceProvider::register()`.

- [ ] **Step 4: Smoke test bindings**

Run: `cd src/backend && php artisan test tests/Unit/Modules/Employee/Domain --compact`
Expected: PASS.

- [ ] **Step 5: Commit persistence**

```bash
git add src/backend/app/Modules/Employee/Domain/Repositories src/backend/app/Modules/Employee/Infrastructure/Persistence src/backend/app/Providers/AppServiceProvider.php
git commit -m "feat(employee): add repositories"
```

---

### Task 7: Application commands, queries, handlers

**Files:**
- Create: `src/backend/app/Modules/Employee/Application/Commands/**`
- Create: `src/backend/app/Modules/Employee/Application/CommandHandlers/**`
- Create: `src/backend/app/Modules/Employee/Application/Queries/**`
- Create: `src/backend/app/Modules/Employee/Application/QueryHandlers/**`
- Create: `src/backend/app/Modules/Employee/Application/Services/EmployeeCodeGenerator.php`
- Test: `src/backend/tests/Unit/Modules/Employee/Application/**`

- [ ] **Step 1: Write failing handler tests**

Use fake repositories. Cover create employee duplicate code, invalid org refs, status change invalid transition, contract overlap, document metadata save after fake upload descriptor.

Run: `cd src/backend && php artisan test tests/Unit/Modules/Employee/Application --compact`
Expected: FAIL.

- [ ] **Step 2: Implement Employee commands/handlers**

Commands: create, update personal info, transfer, change manager, change status, link user. Handlers call `AuthorizationService::requirePermission()` first, then repo/domain, then save.

- [ ] **Step 3: Implement Contract commands/handlers**

Commands: create, activate, renew, terminate. Check employee exists. Check active contract overlap before activation/renewal.

- [ ] **Step 4: Implement Document commands/handlers**

Commands: upload, replace, archive. Upload handler receives `DocumentDescriptor` from infrastructure after MinIO succeeds.

- [ ] **Step 5: Implement queries/query handlers**

Queries: get employee, list employees, get employee contracts, get employee documents.

- [ ] **Step 6: Run application tests**

Run: `cd src/backend && php artisan test tests/Unit/Modules/Employee/Application --compact`
Expected: PASS.

- [ ] **Step 7: Commit application layer**

```bash
git add src/backend/app/Modules/Employee/Application src/backend/tests/Unit/Modules/Employee/Application
git commit -m "feat(employee): add application layer"
```

---

### Task 8: MinIO document storage adapter

**Files:**
- Create: `src/backend/app/Modules/Employee/Infrastructure/Storage/EmployeeDocumentStorage.php`
- Modify: `src/backend/config/filesystems.php` if no MinIO disk exists
- Modify: `.env.example` if required MinIO vars missing
- Test: `src/backend/tests/Feature/Modules/Employee/EmployeeDocumentStorageTest.php`

- [ ] **Step 1: Write failing storage feature test**

Use `Storage::fake('minio')` if framework supports it. Test `store()` returns descriptor with private path and `delete()` removes object.

Run: `cd src/backend && php artisan test tests/Feature/Modules/Employee/EmployeeDocumentStorageTest.php --compact`
Expected: FAIL.

- [ ] **Step 2: Implement storage adapter**

Use Laravel `Storage::disk('minio')` or existing S3 disk. Path format: `employees/{employeeId}/documents/{documentId}_{safeOriginalName}`. Return `DocumentDescriptor`.

- [ ] **Step 3: Add config only if missing**

Do not duplicate existing S3 config. Add only minimal MinIO disk/env vars needed.

- [ ] **Step 4: Run storage test**

Run: `cd src/backend && php artisan test tests/Feature/Modules/Employee/EmployeeDocumentStorageTest.php --compact`
Expected: PASS.

- [ ] **Step 5: Commit storage**

```bash
git add src/backend/app/Modules/Employee/Infrastructure/Storage src/backend/config/filesystems.php .env.example src/backend/tests/Feature/Modules/Employee/EmployeeDocumentStorageTest.php
git commit -m "feat(employee): add document storage"
```

---

### Task 9: HTTP layer and routes

**Files:**
- Create: `src/backend/app/Modules/Employee/Infrastructure/Http/Controllers/*.php`
- Create: `src/backend/app/Modules/Employee/Infrastructure/Http/Requests/*.php`
- Create: `src/backend/app/Modules/Employee/Infrastructure/Http/Resources/*.php`
- Create: `src/backend/app/Modules/Employee/Routes/api.php`
- Modify: `src/backend/routes/api.php`

- [ ] **Step 1: Add FormRequests**

Requests validate shape only. Do not use `unique` rules for domain conflicts; handlers throw `AppException` 409/422.

- [ ] **Step 2: Add Resources**

Return stable JSON shapes for employee, contract, document. Hide raw MinIO paths from document resource.

- [ ] **Step 3: Add Controllers**

Controllers call handlers/queries, wrap resources/collections, no domain logic.

- [ ] **Step 4: Add routes**

Use prefix `/employee`, Sanctum auth, `permission:<code>` middleware on each route.

- [ ] **Step 5: Load module routes**

Update `src/backend/routes/api.php` to require Employee route file.

- [ ] **Step 6: Route list smoke test**

Run: `cd src/backend && php artisan route:list | grep employee`
Expected: all employee/contract/document routes visible.

- [ ] **Step 7: Commit HTTP layer**

```bash
git add src/backend/app/Modules/Employee/Infrastructure/Http src/backend/app/Modules/Employee/Routes src/backend/routes/api.php
git commit -m "feat(employee): add HTTP API"
```

---

### Task 10: Seeders and permissions

**Files:**
- Create: `src/backend/app/Modules/Employee/Infrastructure/Seeders/EmployeeDataSeeder.php`
- Modify: `src/backend/app/Modules/Identity/Infrastructure/Seeders/PermissionSeeder.php`
- Modify: `src/backend/app/Modules/Identity/Infrastructure/Seeders/RoleSeeder.php`
- Modify: `src/backend/database/seeders/DatabaseSeeder.php`

- [ ] **Step 1: Add `employee.*` permission codes**

Add exact 14 permission codes from design spec section 9.

- [ ] **Step 2: Grant roles**

Grant all `employee.*` permissions to `SUPER_ADMIN` and `HR_MANAGER`. Do not grant EMPLOYEE self-service yet.

- [ ] **Step 3: Add small EmployeeDataSeeder**

Seed one active employee with existing Organization IDs if present. Keep optional/guarded so seed works when org seed absent.

- [ ] **Step 4: Run seed smoke test**

Run: `cd src/backend && php artisan migrate`
Expected: PASS; employee permissions present.

- [ ] **Step 5: Commit seeders**

```bash
git add src/backend/app/Modules/Employee/Infrastructure/Seeders src/backend/app/Modules/Identity/Infrastructure/Seeders src/backend/database/seeders/DatabaseSeeder.php
git commit -m "feat(employee): add permissions and seed data"
```

---

### Task 11: Feature HTTP tests

**Files:**
- Create: `src/backend/tests/Feature/Modules/Employee/EmployeeApiTest.php`
- Create: `src/backend/tests/Feature/Modules/Employee/ContractApiTest.php`
- Create: `src/backend/tests/Feature/Modules/Employee/EmployeeDocumentApiTest.php`
- Create: `src/backend/tests/Feature/Modules/Employee/EmployeePermissionTest.php`

- [ ] **Step 1: Write Employee API tests**

Cover create/list/show/update personal info/transfer/change manager/status/link-user happy paths and invalid status transition.

- [ ] **Step 2: Write Contract API tests**

Cover create draft, activate, overlap blocked, renew successor, terminate.

- [ ] **Step 3: Write Document API tests**

Use fake MinIO disk. Cover upload, list, replace, archive, download auth.

- [ ] **Step 4: Write permission tests**

Unauthenticated → 401; authenticated without permission → 403; HR_MANAGER → allowed.

- [ ] **Step 5: Run targeted feature tests**

Run: `cd src/backend && php artisan test tests/Feature/Modules/Employee --compact`
Expected: PASS.

- [ ] **Step 6: Commit feature tests**

```bash
git add src/backend/tests/Feature/Modules/Employee
git commit -m "test(employee): add HTTP feature coverage"
```

---

### Task 12: Module README and final verification

**Files:**
- Create: `src/backend/app/Modules/Employee/README.md`

- [ ] **Step 1: Write README**

Document aggregates, endpoints, permissions, MinIO env vars, test commands.

- [ ] **Step 2: Run targeted tests**

Run: `cd src/backend && php artisan test tests/Unit/Modules/Employee tests/Feature/Modules/Employee --compact`
Expected: PASS.

- [ ] **Step 3: Run full backend tests**

Run: `cd src/backend && php artisan test --compact`
Expected: PASS.

- [ ] **Step 4: Check git status**

Run: `git status --short`
Expected: only intended Employee README if not committed.

- [ ] **Step 5: Commit README**

```bash
git add src/backend/app/Modules/Employee/README.md
git commit -m "docs(employee): add module README"
```

---

## Execution Notes

- Start execution in isolated worktree: `.worktrees/employee` on `feature/employee`.
- Keep commits exactly task-sized unless a test fix must be included with the task it validates.
- Use Organization module as implementation reference, not as code to refactor.
- Do not add a generic service layer. Only keep `EmployeeCodeGenerator`, `EmployeeLifecyclePolicy`, `ContractRenewalPolicy`, and storage adapter.
- Do not expose MinIO object paths in API responses.
- If MinIO Docker/config is missing, add minimal config only; no broader infra rewrite.
