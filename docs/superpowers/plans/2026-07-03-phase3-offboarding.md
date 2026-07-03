# Phase 3 Offboarding Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build Phase 3 Offboarding module with requests, plans, tasks, final clearance, asset check, and 2-stage workflow integration.

**Architecture:** Strict DDD 3-layer module under `app/Modules/Offboarding/`. 4 aggregates (request/plan/task/clearance). 2-stage Workflow BC integration (request approval → clearance sign-off). Asset check stub. Eloquent repos, PHPUnit tests.

**Tech Stack:** Laravel 12, PHP 8.4, PostgreSQL 16 UUID, Sanctum, Eloquent repos, PHPUnit.

**Branch:** feature/offboarding. Work in `.worktrees/offboarding` or directly on branch. Do NOT commit to main.

---

## File Map

```
Create: src/backend/database/migrations/2026_07_03_150001_create_offboarding_requests_table.php
Create: src/backend/database/migrations/2026_07_03_150002_create_offboarding_plans_table.php
Create: src/backend/database/migrations/2026_07_03_150003_create_offboarding_tasks_table.php
Create: src/backend/database/migrations/2026_07_03_150004_create_final_clearances_table.php

Domain/ValueObjects/   — 5 files: OffboardingRequestType, OffboardingRequestStatus, OffboardingPlanStatus, OffboardingTaskStatus, TaskType, OwnerType
Domain/Aggregates/     — 8 files (4 aggregate classes + 4 ID classes)
Domain/Events/         — 12 event classes
Domain/Exceptions/     — 7 exceptions
Domain/Repositories/   — 4 interfaces

Application/Commands/       — 13 command classes
Application/CommandHandlers/ — 13 handlers (mirror Onboarding pattern)
Application/Queries/        — 3 query classes
Application/QueryHandlers/  — 3 handlers

Infrastructure/Persistence/Eloquent/    — 4 models
Infrastructure/Persistence/Repositories/ — 4 Eloquent repos
Infrastructure/Http/Controllers/        — 4 controllers
Infrastructure/Services/                — RequestWorkflowService, ClearanceWorkflowService, AssetCheckService, NotificationService
Infrastructure/Jobs/                    — RequestApprovedJob, ClearanceApprovedJob
Infrastructure/Seeders/                 — OffboardingPermissionSeeder
Infrastructure/Routes/api.php           — 19 routes

Modify: src/backend/routes/api.php (require route)
Modify: src/backend/app/Providers/AppServiceProvider.php (4 bindings)
Modify: src/backend/database/seeders/DatabaseSeeder.php (add seeder)

Tests: Unit/Modules/Offboarding/ (6 test classes), Feature/Modules/Offboarding/ (1 test class)
```

---

### Task 1-2: Schema migrations

**Files:**
- Create: `src/backend/database/migrations/2026_07_03_150001_create_offboarding_requests_table.php`
- Create: `src/backend/database/migrations/2026_07_03_150002_create_offboarding_plans_table.php`
- Create: `src/backend/database/migrations/2026_07_03_150003_create_offboarding_tasks_table.php`
- Create: `src/backend/database/migrations/2026_07_03_150004_create_final_clearances_table.php`

**4 migration files matching schema from spec §4.** All use uuid PK, timestamps. OffboardingTasks has FK cascade to offboarding_plans.

### Task 3: Value Objects + IDs

**5 value objects (enums) + 4 ID classes — mirror Onboarding pattern.**

### Task 4: Domain Exceptions

**7 exceptions — same pattern as Onboarding.** Add `AssetObligationsNotMetException`.

### Task 5: Domain Events

**12 events — same constructor pattern.** All use readonly properties matching aggregate state.

### Task 6-9: Aggregates

**OffboardingRequest** (id, employeeId, type, reason, requestedLastWorkingDate, approvedLastWorkingDate, status, workflowRequestId)
- submit(), approve(lastWorkingDate), reject(reason), cancel()
- Terminal guard: approved/rejected/cancelled → no transitions

**OffboardingPlan** (mirrors OnboardingPlan)
- activate(), complete(), cancel(), addTask(), removeTask(), markWorkflowApproved()
- All tasks must be terminal before complete

**OffboardingTask** (identical to OnboardingTask)
- start(), complete(), waive(), markApproved()

**FinalClearance** (id, planId, employeeId, clearedAt, clearedBy, assetObligationsMet, payrollNotes)
- Stateless — created once, immutable
- Factory: `create(planId, employeeId, clearedBy, assetObligationsMet, payrollNotes)`

### Task 10: Repository Interfaces + Eloquent Models

**4 interfaces + 4 Eloquent models — same pattern as Onboarding.**

### Task 11: Eloquent Repositories + Service Provider Bindings

**4 repository implementations — same pattern as Onboarding.** EloquentOffboardingPlanRepository saves plan + tasks. Add bindings in AppServiceProvider.

### Task 12-14: Application Commands, Handlers, Queries

**13 commands + 13 handlers (mirror Onboarding's pattern):**
- CreateOffboardingRequest → handler validates type, reason
- SubmitOffboardingRequest → handler sets workflowRequestId
- ApproveOffboardingRequest → handler calls $request->approve()
- RejectOffboardingRequest → handler calls $request->reject()
- CreateOffboardingPlan → from request, creates plan + default task set
- Activate/Cancel/CompleteOffboardingPlan → same as Onboarding
- Add/Remove/Start/Complete/WaiveTask → same as Onboarding
- CompleteFinalClearance → check asset obligations → create FinalClearance

**3 queries + 3 handlers for listing.**

### Task 15: Infrastructure Services

**RequestWorkflowService** — stub (throw RuntimeException, not wired to Workflow BC)
**ClearanceWorkflowService** — stub
**AssetCheckService** — returns `new AssetCheckResult(obligationsMet: true, pending: [])` (stub)
**NotificationService** — emits events

### Task 16: Jobs

**RequestApprovedJob** — finds request by workflowRequestId, calls $request->approve(), dispatches events
**ClearanceApprovedJob** — finds plan by workflowRequestId, calls $plan->markWorkflowApproved()

### Task 17: Controllers — 4 classes mirroring Onboarding's controller pattern.

### Task 18: Routes — 19 routes under `/api/v1/offboarding/*` with `auth:sanctum` + permission middleware.

### Task 19: Permission Seeder — 18 permission codes matching spec §8. SUPER_ADMIN granted all.

### Task 20: Domain + Feature Tests — 6 domain test classes + 1 feature test class.

### Task 21: Full verification — migrate + seed + test run + spec acceptance review.
