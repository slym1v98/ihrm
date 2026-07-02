# Phase 3 Recruitment Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build the Phase 3 Recruitment module with requisition → workflow approval → candidate pipeline → interview scorecards → offer accept/reject → employee conversion stub.

**Architecture:** Strict DDD 3-layer module under `app/Modules/Recruitment/`. Workflow BC integration via command dispatch + job callback. Employee conversion stub creates employee record directly (no Onboarding BC).

**Tech Stack:** Laravel 12, PHP 8.4, PostgreSQL 16 UUID/JSONB, Sanctum, Eloquent repos, PHPUnit.

---

## File Map

- `src/backend/app/Modules/Recruitment/Domain/**`: 4 aggregates + VOs + events + exceptions + repos.
- `src/backend/app/Modules/Recruitment/Application/**`: commands/handlers + queries.
- `src/backend/app/Modules/Recruitment/Infrastructure/**`: Eloquent, HTTP, services, jobs, seeders.
- `src/backend/app/Modules/Recruitment/Routes/api.php`: module routes.
- `src/backend/database/migrations/2026_07_02_13000*_create_recruitment_*.php`: schema.
- `src/backend/app/Providers/AppServiceProvider.php`: repo bindings.
- `src/backend/routes/api.php`: require route file.
- `src/backend/tests/Unit/Modules/Recruitment/**`: domain/app tests.
- `src/backend/tests/Feature/Modules/Recruitment/**`: HTTP tests.

---

### Task 1: Schema

**Files:**
- Create: `2026_07_02_130001_create_recruitment_requisitions_table.php`
- Create: `2026_07_02_130002_create_recruitment_candidates_table.php`
- Create: `2026_07_02_130003_create_recruitment_interviews_table.php`
- Create: `2026_07_02_130004_create_recruitment_offers_table.php`

- [ ] **Step 1-4:** Write each migration matching spec Schema §4.
  `docker compose run --rm app php artisan migrate:fresh --seed` → PASS.
- [ ] **Step 5:** Commit.

---

### Task 2: Domain + Persistence

**Files (batch):**
- All 4 IDs + aggregates + VOs + events + exceptions + repo interfaces.
- All 4 Eloquent models + repo implementations.
- AppServiceProvider bindings.
- Domain unit tests.

- [ ] **Step 1:** Write IDs, VOs (status enums), aggregate files.
- [ ] **Step 2:** Events (POPO pattern).
- [ ] **Step 3:** Exceptions.
- [ ] **Step 4:** Repo interfaces.
- [ ] **Step 5:** Eloquent models + repos.
- [ ] **Step 6:** AppServiceProvider bindings.
- [ ] **Step 7:** Domain tests.
- [ ] **Step 8:** Commit.

---

### Task 3: Application + HTTP + Jobs

**Files:**
- Commands/handlers for all 11 commands.
- Queries.
- Controllers, requests, resources.
- Routes.
- `WorkflowIntegrationService` and `EmployeeConversionService`.
- `RequisitionWorkflowApprovedJob`.
- Feature tests.

- [ ] **Step 1:** Commands + handlers.
- [ ] **Step 2:** Controllers/resources/routes.
- [ ] **Step 3:** Workflow integration service.
- [ ] **Step 4:** Employee conversion service.
- [ ] **Step 5:** Requisition workflow job.
- [ ] **Step 6:** Feature tests.
- [ ] **Step 7:** Commit.

---

### Task 4: Seeders + Final verification

- [ ] **Step 1:** Permission seeder + role grants.
- [ ] **Step 2:** `docker compose run --rm app php artisan migrate:fresh --seed` → PASS.
- [ ] **Step 3:** Targeted tests.
- [ ] **Step 4:** Full suite.
- [ ] **Step 5:** Spec acceptance review.
- [ ] **Step 6:** Commit.

