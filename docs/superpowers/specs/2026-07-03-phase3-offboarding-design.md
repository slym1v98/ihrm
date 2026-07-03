# Phase 3 Offboarding BC Design

Version: 0.1
Date: 2026-07-03
Status: Design approved (brainstorming)

## 1. Scope

Build Offboarding module (`app/Modules/Offboarding/`) as the third Phase 3 sub-project. Covers resignation/company-initiated separation requests, approval workflow, task checklists, asset return integration, final clearance, and payroll linkage.

**In scope:** OffboardingRequest, OffboardingPlan, OffboardingTask, FinalClearance; request approval workflow (stage 1); clearance sign-off workflow (stage 2); asset return obligation check before final clearance; payroll note capture; notification integration via events; permission integration with Identity; full test suite.

**Out of scope:** Automated account de-provisioning (AD/LDAP integration), exit interview module, offboarding analytics, bulk offboarding, asset module creation (Asset BC is separate Phase 3 sub-project).

## 2. Architecture

Strict DDD tactical pattern with 3 layers, consistent with Onboarding and Recruitment modules.

```
Module/Offboarding/
  Domain/         — Pure PHP, no Laravel deps
  Application/    — Commands/Handlers + Queries
  Infrastructure/ — Eloquent, HTTP, workflow integration, seeders, routes
```

**Key architectural decisions:**

- **4 aggregates:** `OffboardingRequest` (initiation + approval), `OffboardingPlan` (checklist lifecycle), `OffboardingTask` (per-item state), `FinalClearance` (audit trail for clearance completion).
- **2-stage Workflow:** Request approval (resignation manager approval or company-initiated HR approval) → Clearance sign-off (plan complete approval).
- **Asset check stub:** Before final clearance, verify `asset_obligations_met`. Asset BC not yet built — stub service returns true.
- **Payroll note:** FinalClearance captures `payroll_notes` for linkage to Payroll BC (no active integration in v1).
- **Notification:** Events emitted for all state changes; Notification BC listens.
- **Task management:** Mirrors Onboarding — system-defined and custom tasks, owner assignment.

## 3. Module Layout

```
app/Modules/Offboarding/
  Domain/
    Aggregates/
      OffboardingRequest/    (OffboardingRequest.php, OffboardingRequestId.php)
      OffboardingPlan/       (OffboardingPlan.php, OffboardingPlanId.php)
      OffboardingTask/       (OffboardingTask.php, OffboardingTaskId.php)
      FinalClearance/        (FinalClearance.php, FinalClearanceId.php)
    ValueObjects/            (Request type/status, plan status, task status, task type, owner type)
    Events/                  (12 events: request/plan/task lifecycle + clearance)
    Repositories/            (4 repository interfaces)
    Exceptions/              (7 exception classes)
  Application/
    Commands/                (13 commands)
    CommandHandlers/         (13 handlers)
    Queries/                 (ListRequestsQuery, ListPlansQuery, ListTasksQuery)
    QueryHandlers/
  Infrastructure/
    Persistence/
      Eloquent/              (4 Eloquent models)
      Repositories/          (4 Eloquent repository implementations)
    Http/Controllers/        (OffboardingRequestController, OffboardingPlanController, OffboardingTaskController, FinalClearanceController)
    Services/                (RequestWorkflowService, ClearanceWorkflowService, AssetCheckService, NotificationService)
    Jobs/                    (RequestApprovedJob, ClearanceApprovedJob)
    Seeders/                 (OffboardingPermissionSeeder)
  Routes/api.php
```

## 4. Schema

### `offboarding_requests`
id(uuid PK), employee_id(uuid), type(varchar30), reason(text), requested_last_working_date(date), approved_last_working_date(date nullable), status(varchar20), workflow_request_id(uuid nullable), timestamps

### `offboarding_plans`
id(uuid PK), offboarding_request_id(uuid FK), status(varchar20), completed_at(timestamp nullable), timestamps

### `offboarding_tasks`
id(uuid PK), offboarding_plan_id(uuid FK cascade delete), task_type(varchar20), owner_type(varchar20), owner_id(varchar100), title(varchar255), description(text nullable), due_date(date nullable), status(varchar20), requires_approval(bool), approval_workflow_request_id(uuid nullable), proof_file_object_id(uuid nullable), sort_order(int), timestamps

### `final_clearances`
id(uuid PK), offboarding_plan_id(uuid FK), employee_id(uuid), cleared_at(timestamp), cleared_by(uuid), asset_obligations_met(bool), payroll_notes(text nullable), timestamps

Indexes: requests(employee_id, status), plans(offboarding_request_id), tasks(plan_id, status)

## 5. Domain Model

### Value Objects
**OffboardingRequestType:** resignation, company_initiated
**OffboardingRequestStatus:** draft, pending_approval, approved, rejected, cancelled
**OffboardingPlanStatus:** draft, active, completed, cancelled
**OffboardingTaskStatus:** pending, in_progress, completed, waived
**TaskType, OwnerType** — mirror Onboarding

### Aggregates

**OffboardingRequest:** submit()→pending_approval, approve(lastWorkingDate)→approved, reject(reason)→rejected, cancel()→cancelled. Invariants: approved date required before plan creation; terminal states dead.

**OffboardingPlan:** activate(), complete(), cancel(), addTask(), removeTask(), markWorkflowApproved(). Invariants: all tasks terminal before completion; clearance workflow may delay completion.

**OffboardingTask:** start(), complete(), waive(), markApproved(). Mirrors OnboardingTask exactly.

**FinalClearance:** created when all tasks done + asset check passes.

### Domain Events
OffboardingRequestCreated, OffboardingRequestSubmitted, OffboardingRequestApproved, OffboardingRequestRejected, OffboardingPlanCreated, OffboardingPlanActivated, OffboardingPlanCompleted, OffboardingTaskAssigned, OffboardingTaskStarted, OffboardingTaskCompleted, OffboardingTaskWaived, FinalClearanceCompleted

## 6. Workflow Integration
**Stage 1:** Submit request → Workflow request → RequestApprovedJob/RejectedJob callback
**Stage 2:** Complete plan → start clearance workflow → ClearanceApprovedJob → asset check → FinalClearance created

## 7. API Endpoints
19 endpoints under `/api/v1/offboarding/*` covering request CRUD+submit/approve/reject, plan CRUD+activate/complete, task CRUD+start/complete/waive, and final-clearance.

## 8. Permissions
Codes: offboarding.request.{view,create,update,submit,approve,reject}, offboarding.plan.{view,create,activate,complete}, offboarding.task.{view,create,update,start,complete,waive}, offboarding.clearance.complete
Role assignment: same as Onboarding

## 9. Error Handling
422 for blocked transitions, pending tasks, asset obligations not met.

## 10. Testing
Domain tests for all 4 aggregates; feature tests for happy path resignation→clearance, company-initiated flow, permission boundaries, 422 scenarios.

## 11. Acceptance Criteria
1. ✅ DDD layout conventions.
2. ✅ 4 aggregates: request, plan, task, final clearance.
3. ✅ Resignation + company-initiated support.
4. ✅ Workflow stage 1 (request approval).
5. ✅ Workflow stage 2 (clearance sign-off).
6. ✅ Asset obligation check before clearance.
7. ✅ Payroll notes in clearance.
8. ✅ Manual plan creation API.
9. ✅ Notification via events.
10. ✅ API routes + permissions seeded.
11. ✅ Tests exist.
