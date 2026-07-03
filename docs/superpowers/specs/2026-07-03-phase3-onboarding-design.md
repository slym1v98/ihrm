# Phase 3 Onboarding BC Design

Version: 0.1
Date: 2026-07-03
Status: Design approved (brainstorming)

## 1. Scope

Build Onboarding module (`app/Modules/Onboarding/`) as the second Phase 3 sub-project. Covers onboarding templates, per-hire onboarding plans, task assignment/tracking, pre/post-start steps, workflow-based approval for plan completion and task verification, notification integration, and Recruitment BC integration via event listener.

**In scope:** OnboardingTemplate, OnboardingPlan, OnboardingTask; system-defined (template) + custom tasks; template matching by department/position/location/employment type; Workflow BC integration for plan completion approval and optional task-level approval; Recruitment candidate-hired event listener for auto plan creation; manual plan creation fallback; notification integration (direct calls + events); permission integration with Identity; full test suite.

**Out of scope:** offboarding plan creation (separate module Phase 3), document/attachment management beyond proof file reference, external employee self-onboarding portal, advanced template versioning, calendar sync for task due dates, bulk reordering/prioritization, analytics dashboard.

## 2. Architecture

Strict DDD tactical pattern with 3 layers, consistent with Recruitment module.

```
Module/Onboarding/
  Domain/         — Pure PHP, no Laravel deps
  Application/    — Commands/Handlers + Queries
  Infrastructure/ — Eloquent, HTTP, workflow integration, notification, seeders, routes
```

**Key architectural decisions:**

- **3 aggregates:** `OnboardingTemplate`, `OnboardingPlan`, `OnboardingTask`. Template is a standalone aggregate to allow reusable task sets. Plan and Task are separate root aggregates (Plan owns the lifecycle, Task tracks state per item).
- **Template → Plan generation:** Template has `addTemplateTask()` which stores tasks as value objects within the template aggregate. `generatePlan()` copies tasks as new OnboardingTask entities.
- **Plan start via Recruitment event:** Recruitment BC emits `CandidateHired` → Onboarding listens via event subscriber → creates plan in `draft` status. Manual API also available.
- **Workflow BC integration:** Plan completion can trigger Workflow request for sign-off. Per-task `requires_approval` flag can also trigger individual workflow requests. Approved callbacks transition status.
- **Notification integration:** Direct service calls from handlers + events for async delivery. Implementation may use Notification BC's public facade.
- **Audit:** All status transitions and mutations emit audit events matching the Audit BC contract.
- **Pre/post-start tasks:** `is_pre_start` boolean on tasks. No separate phase handling at plan level — just a filter for UI/notification logic.
- **All tasks are mandatory in v1:** Plan completion requires every task to be completed or waived. Defer per-task `is_mandatory` flag to v2 if partial optionality is needed.

## 3. Module Layout

```
app/Modules/Onboarding/
  Domain/
    Aggregates/
      OnboardingPlan/
        OnboardingPlan.php
        OnboardingPlanId.php
      OnboardingTask/
        OnboardingTask.php
        OnboardingTaskId.php
      OnboardingTemplate/
        OnboardingTemplate.php
        OnboardingTemplateId.php
    ValueObjects/
      OnboardingPlanStatus.php
      OnboardingTaskStatus.php
      TaskType.php
      OwnerType.php
      TemplateRules.php
    Events/
      OnboardingPlanCreated.php
      OnboardingPlanActivated.php
      OnboardingPlanCompleted.php
      OnboardingTaskAssigned.php
      OnboardingTaskStarted.php
      OnboardingTaskCompleted.php
      OnboardingTaskWaived.php
      OnboardingCompleted.php
    Repositories/
      OnboardingPlanRepositoryInterface.php
      OnboardingTaskRepositoryInterface.php
      OnboardingTemplateRepositoryInterface.php
    Exceptions/
      OnboardingPlanNotFoundException.php
      OnboardingTaskNotFoundException.php
      OnboardingTemplateNotFoundException.php
      InvalidStatusTransitionException.php
      MandatoryTaskIncompleteException.php
  Application/
    Commands/
      CreateOnboardingPlanCommand.php
      ActivateOnboardingPlanCommand.php
      CancelOnboardingPlanCommand.php
      CompleteOnboardingPlanCommand.php
      AddOnboardingTaskCommand.php
      RemoveOnboardingTaskCommand.php
      StartTaskCommand.php
      CompleteTaskCommand.php
      WaiveTaskCommand.php
    CommandHandlers/
      CreateOnboardingPlanHandler.php
      ActivateOnboardingPlanHandler.php
      CancelOnboardingPlanHandler.php
      CompleteOnboardingPlanHandler.php
      AddOnboardingTaskHandler.php
      RemoveOnboardingTaskHandler.php
      StartTaskHandler.php
      CompleteTaskHandler.php
      WaiveTaskHandler.php
    Queries/
      ListPlansQuery.php
      ListTemplatesQuery.php
      ListTasksQuery.php
    QueryHandlers/
      ...
  Infrastructure/
    Persistence/
      Eloquent/
        OnboardingPlanModel.php
        OnboardingTaskModel.php
        OnboardingTemplateModel.php
      Repositories/
        EloquentOnboardingPlanRepository.php
        EloquentOnboardingTaskRepository.php
        EloquentOnboardingTemplateRepository.php
    Http/
      Controllers/
        OnboardingTemplateController.php
        OnboardingPlanController.php
        OnboardingTaskController.php
      Requests/
        ...
      Resources/
        ...
    Services/
      PlanWorkflowService.php
      TaskWorkflowService.php
      NotificationService.php
    Listeners/
      CandidateHiredListener.php  — listens for Recruitment CandidateHired event
    Jobs/
      PlanCompletionApprovedJob.php
      TaskApprovedJob.php
    Seeders/
      OnboardingPermissionSeeder.php
  Routes/api.php
```

## 4. Schema

### `onboarding_templates`

| Column | Type | Notes |
|---|---|---|
| id | uuid | PK |
| code | varchar(50) | Unique template code |
| name | varchar(255) | Display name |
| rules | jsonb | Department/position/location/employment_type filter criteria + embedded task definitions |
| active | boolean | Soft enable/disable |
| created_at, updated_at | timestamps | |

### `onboarding_plans`

| Column | Type | Notes |
|---|---|---|
| id | uuid | PK |
| employee_id | uuid | FK to employees |
| candidate_id | uuid, nullable | FK to candidates (Recruitment) |
| template_id | uuid, nullable | FK to onboarding_templates |
| start_date | date | Employee start date |
| status | varchar(20) | draft / active / completed / cancelled |
| workflow_request_id | uuid, nullable | Workflow request for plan completion approval |
| completed_at | timestamp, nullable | |
| created_at, updated_at | timestamps | |

### `onboarding_tasks`

| Column | Type | Notes |
|---|---|---|
| id | uuid | PK |
| onboarding_plan_id | uuid | FK to onboarding_plans |
| task_type | varchar(20) | system_defined / custom |
| owner_type | varchar(20) | department / user_role |
| owner_id | varchar(100) | Department ID or role code |
| title | varchar(255) | Task name |
| description | text, nullable | |
| due_date | date, nullable | Computed from template due_days + start_date |
| status | varchar(20) | pending / in_progress / completed / waived |
| requires_approval | boolean | If true, task completion triggers workflow |
| approval_workflow_request_id | uuid, nullable | |
| proof_file_object_id | uuid, nullable | File reference for completion proof |
| sort_order | integer | Display ordering |
| is_pre_start | boolean | True = before employee start date |
| created_at, updated_at | timestamps | |

Indexes:
- `onboarding_plans`: `employee_id`, `status`
- `onboarding_tasks`: `(onboarding_plan_id, status)`, `(owner_type, owner_id, status)`

### Template task storage

Template tasks are stored as JSONB array within `onboarding_templates.rules`:

```json
{
  "departments": ["uuid1"],
  "positions": ["uuid2"],
  "locations": ["uuid3"],
  "employment_types": ["full-time"],
  "tasks": [
    {
      "title": "Prepare laptop",
      "description": "Set up company laptop",
      "owner_type": "department",
      "owner_id": "it-department-uuid",
      "due_days": -3,
      "requires_approval": true,
      "is_pre_start": true,
      "sort_order": 1
    }
  ]
}
```

Due_days is relative to plan start_date (negative = before start, positive = after start). Defer separate `template_tasks` table to v2 if direct querying/editing of template tasks is needed.

## 5. Domain Model

### Value Objects

**OnboardingPlanStatus:** `draft`, `active`, `completed`, `cancelled`.
**OnboardingTaskStatus:** `pending`, `in_progress`, `completed`, `waived`.
**TaskType:** `system_defined`, `custom`.
**OwnerType:** `department`, `user_role`.
**TemplateRules:** value object wrapping the JSONB rules + task array, with methods for matching evaluation and task generation.

### Aggregates

#### OnboardingTemplate

- `id: OnboardingTemplateId`
- `code: string`
- `name: string`
- `rules: TemplateRules`
- `active: bool`

Methods:
- `addTemplateTask(title, description, ownerType, ownerId, dueDays, requiresApproval, isPreStart, sortOrder)`
- `removeTemplateTask(sortOrder)`
- `matches(criteria): bool`
- `generatePlan(employeeId, candidateId?, startDate): OnboardingPlan`

Invariants:
- Code must be unique.
- Active templates cannot be deleted.

#### OnboardingPlan

- `id: OnboardingPlanId`
- `employeeId: string`
- `candidateId: string|null`
- `templateId: string|null`
- `startDate: DateTimeImmutable`
- `status: OnboardingPlanStatus`
- `workflowRequestId: string|null`
- `completedAt: DateTimeImmutable|null`

Methods:
- `activate()` — draft → active
- `cancel()` — draft/active → cancelled
- `complete()` — active → completed; all tasks must be completed/waived; if `workflow_request_id` set, status stays active until `markWorkflowApproved()` called
- `addTask(task: OnboardingTask)` — only on active plan, only task_type=custom
- `removeTask(taskId)` — only removes custom tasks
- `markWorkflowApproved()` — transitions to completed, sets completed_at

Invariants:
- All tasks must be completed or waived before completion (v1: all tasks are mandatory).
- If plan completion workflow is pending, status stays `active` until approval callback.
- Terminal statuses (completed/cancelled) do not transition.
- Custom tasks max 50 per plan.

#### OnboardingTask

- `id: OnboardingTaskId`
- `planId: string`
- `taskType: TaskType`
- `ownerType: OwnerType`
- `ownerId: string`
- `title: string`
- `description: string|null`
- `dueDate: DateTimeImmutable|null`
- `status: OnboardingTaskStatus`
- `requiresApproval: bool`
- `approvalWorkflowRequestId: string|null`
- `proofFileObjectId: string|null`
- `sortOrder: int`
- `isPreStart: bool`

Methods:
- `start()` — pending → in_progress
- `complete(proofFileObjectId?)` — in_progress → completed (if requires_approval, triggers workflow, stays in_progress until approved)
- `waive(reason?)` — pending/in_progress → waived
- `markApproved()` — after workflow approval: in_progress → completed
- `markRejected()` — after workflow rejection: in_progress stays, optionally revert via handler

Invariants:
- Completed/waived tasks do not revert.
- `requires_approval` tasks must go through workflow to become completed.

### Domain Events

- `OnboardingPlanCreated(planId, employeeId, startDate)`
- `OnboardingPlanActivated(planId, employeeId, startDate)`
- `OnboardingPlanCompleted(planId, employeeId)`
- `OnboardingTaskAssigned(taskId, planId, ownerType, ownerId, dueDate)`
- `OnboardingTaskStarted(taskId, planId)`
- `OnboardingTaskCompleted(taskId, planId, proofFileObjectId?)`
- `OnboardingTaskWaived(taskId, planId, reason?)`
- `OnboardingCompleted(planId, employeeId)` — aggregate signal when plan+all tasks are complete

## 6. Workflow Integration

### Plan Completion Approval

`CompleteOnboardingPlanHandler`:
1. Load plan in `active`.
2. Verify all tasks are completed or waived.
3. Create Workflow request via `PlanWorkflowService` with `subject_type=onboarding_plan`, `subject_id=plan.id`.
4. Persist `workflow_request_id`, keep status `active`.
5. Emit event.

`PlanCompletionApprovedJob`:
1. Receive Workflow approved callback.
2. Load plan by `workflow_request_id`.
3. Call `markWorkflowApproved()`, set `completed_at`.
4. Emit `OnboardingPlanCompleted`, `OnboardingCompleted`.

### Task-Level Approval

`CompleteTaskHandler` (if `requires_approval=true`):
1. Load task, verify `in_progress`.
2. Call `TaskWorkflowService` to create Workflow request.
3. Persist `approval_workflow_request_id`, keep status `in_progress`.
4. Emit event.

`TaskApprovedJob`:
1. Load task by `approval_workflow_request_id`.
2. Call `markApproved()`.
3. Emit `OnboardingTaskCompleted`.

Workflow rejection handled similarly — job calls `markRejected()`.

## 7. Recruitment Integration

`CandidateHiredListener`:
1. Listen for `CandidateHired` event from Recruitment BC.
2. Resolve matching `OnboardingTemplate` by candidate's department/position/location/employment_type (via `templateRepository.findMatching(departmentId, positionId, locationId, employmentType)`).
3. If template matched, call `template.generatePlan(employeeId, candidateId, startDate)`.
4. If no template, create empty plan (HR adds tasks manually).
5. Emit `OnboardingPlanCreated`.

## 8. API Endpoints

All under `/api/v1/onboarding/*`, Sanctum auth.

### Templates

| Method | Path | Permission |
|---|---|---|
| GET | `/onboarding/templates` | onboarding.template.view |
| POST | `/onboarding/templates` | onboarding.template.create |
| GET | `/onboarding/templates/{id}` | onboarding.template.view |
| PATCH | `/onboarding/templates/{id}` | onboarding.template.update |
| DELETE | `/onboarding/templates/{id}` | onboarding.template.delete |

### Plans

| Method | Path | Permission |
|---|---|---|
| GET | `/onboarding/plans` | onboarding.plan.view |
| POST | `/onboarding/plans` | onboarding.plan.create |
| GET | `/onboarding/plans/{id}` | onboarding.plan.view |
| PATCH | `/onboarding/plans/{id}` | onboarding.plan.update |
| POST | `/onboarding/plans/{id}/activate` | onboarding.plan.activate |
| POST | `/onboarding/plans/{id}/cancel` | onboarding.plan.cancel |
| POST | `/onboarding/plans/{id}/complete` | onboarding.plan.complete |

### Tasks

| Method | Path | Permission |
|---|---|---|
| GET | `/onboarding/plans/{planId}/tasks` | onboarding.task.view |
| POST | `/onboarding/plans/{planId}/tasks` | onboarding.task.create |
| GET | `/onboarding/tasks/{id}` | onboarding.task.view |
| PATCH | `/onboarding/tasks/{id}` | onboarding.task.update |
| POST | `/onboarding/tasks/{id}/start` | onboarding.task.start |
| POST | `/onboarding/tasks/{id}/complete` | onboarding.task.complete |
| POST | `/onboarding/tasks/{id}/waive` | onboarding.task.waive |

## 9. Permissions

**Seeded permission codes:**
- `onboarding.template.view`
- `onboarding.template.create`
- `onboarding.template.update`
- `onboarding.template.delete`
- `onboarding.plan.view`
- `onboarding.plan.create`
- `onboarding.plan.update`
- `onboarding.plan.activate`
- `onboarding.plan.cancel`
- `onboarding.plan.complete`
- `onboarding.task.view`
- `onboarding.task.create`
- `onboarding.task.update`
- `onboarding.task.start`
- `onboarding.task.complete`
- `onboarding.task.waive`

**Default roles:**
- Admin: all
- HR Manager: all
- HR Staff: template.*, plan.*, task.*
- Department Manager: plan.view, plan.complete, task.view, task.start, task.complete, task.waive
- Employee (self): task.view (own tasks), task.start, task.waive

## 10. Error Handling

| Scenario | Behavior |
|---|---|
| Create plan for non-existent employee | 422 |
| Activate plan with 0 tasks | 422 |
| Complete plan with pending/in_progress tasks | `MandatoryTaskIncompleteException` → 422 |
| Complete plan without workflow approval | 422 |
| Add task to cancelled/completed plan | 422 |
| Remove system_defined task | 422 |
| Transition from terminal status | `InvalidStatusTransitionException` → 422 |
| Complete approval-required task without workflow | 422 |
| Waive completed task | 422 |
| Waive already-waived task | 422 |

## 11. Testing

### Domain Unit Tests
- Plan: draft→active→completed, draft→cancelled, active→cancelled, terminal→invalid
- Plan: complete blocks if any task pending/in_progress
- Plan: complete with workflow→status stays active until `markWorkflowApproved()`
- Task: pending→in_progress→completed, pending→waived
- Task: requires_approval blocks direct completion
- Template: matches correct department/position/location/employment_type
- Template: generatePlan creates correct tasks with computed due_dates

### Application Unit Tests
- Create plan from template copies all tasks
- Activate plan validates minimum task count
- Complete plan triggers workflow when `PlanWorkflowService` configured
- Workflow approval job transitions plan to completed
- CandidateHiredListener creates plan (with and without matching template)
- Add/remove custom task on active plan
- Remove system_defined task → exception

### Feature Tests
- Auth required for all 19 endpoints
- Permission boundaries per role/permission code
- Happy path: POST template → POST plan → POST tasks → PATCH activate → POST start/complete tasks → POST complete plan
- Plan completion with workflow approval callback
- Task approval: start → complete (triggers workflow) → job approves → completed
- Duplicate/conflict: 422 for all invalid transitions
- Edge: plan with 0 tasks cannot activate

## 12. Acceptance Criteria

1. ✅ Onboarding BC follows Phase 3 DDD layout conventions.
2. ✅ 3 aggregates: template, plan, task.
3. ✅ Templates support department/position/location/employment_type filtering.
4. ✅ Plan creation from template auto-generates tasks.
5. ✅ Plan completion integrates Workflow BC for sign-off.
6. ✅ Task-level approval via Workflow BC is supported.
7. ✅ Recruitment CandidateHired event triggers plan creation.
8. ✅ Manual plan creation API available.
9. ✅ Pre-start and post-start tasks supported via `is_pre_start` flag.
10. ✅ Notification integration via events + direct calls.
11. ✅ API routes and permissions seeded.
12. ✅ Domain, application, and feature tests exist.
