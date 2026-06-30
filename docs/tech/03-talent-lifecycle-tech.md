# Phase 3 Technical Design — Talent Lifecycle

Version: 0.1  
Date: 2026-06-30  
Status: Draft for review

## 1. Scope

Covers Recruitment, Onboarding, Offboarding, Performance, Training, and Asset.

## 2. Modules

```text
app/Modules/
  Recruitment/     # Requisitions, candidates, interviews, offers
  Onboarding/      # Templates, plans, tasks
  Offboarding/     # Requests, plans, tasks, final clearance
  Performance/     # Cycles, goals, reviews
  Training/        # Courses, sessions, enrollments, results
  Asset/           # Items, assignments, returns
```

## 3. Recurring Patterns

### 3.1 Checklist/Task Generic Implementation

- `OnboardingPlan`/`OnboardingTask`, `OffboardingPlan`/`OffboardingTask` share the same pattern
- `owner_type` + `owner_id` polymorphic
- Status state machine: `open → in_progress → done` or `waived`
- Mandatory flag used for clearance logic

### 3.2 Candidate to Employee Conversion

- `Recruitment` BC owns candidate
- `Employee` BC owns employee
- Conversion app service in `Recruitment` calls `Employee` use case via application layer
- Or: domain event `CandidateAccepted` consumed by `Employee` to provision
- Choose one path and stick to it; prefer the event path for decoupling

## 4. Performance Review

- `PerformanceCycle` locks config after reviews start
- `PerformanceReview` aggregates self-assessment and manager assessment
- Final review is immutable except via privileged correction
- Score calculation in domain service
- Result feeds into reporting/analytics only in this phase; no automatic payroll impact yet

## 5. Training Architecture

- `TrainingSession` bounded by capacity
- `TrainingEnrollment` unique per `(session_id, employee_id)`
- `TrainingResult` records outcome
- Certificate files stored in MinIO via `FileObject` reference

## 6. Asset Architecture

- `AssetItem` inventory
- `AssetAssignment` is unique active per asset
- `AssetReturn` records condition/settlement
- Offboarding clearance checks pending mandatory returns via event/query

## 7. Cross-Module Integration

- `Recruitment` → `Employee` (conversion)
- `Onboarding` → `Employee` (task completion may not mutate Employee directly)
- `Offboarding` → `Employee` (status change to resigned is a use case)
- `Offboarding` → `Asset` (asset return obligations)
- All cross-module coordination via events except for explicit conversion use case

## 8. Queues and Jobs

- `GenerateOnboardingPlanFromTemplateJob`
- `NotifyOnboardingOwnersJob`
- `NotifyPerformanceReviewDueJob`
- `NotifyTrainingReminderJob`
- `NotifyAssetReturnDueJob`

## 9. Scheduler

- Daily reminder for onboarding/offboarding tasks
- Performance cycle kickoff
- Training reminders

## 10. Performance Targets

- Candidate pipeline: <500ms p95
- Onboarding plan creation: <500ms p95
- Performance review submission: <500ms p95
- Asset return operations: <500ms p95

## 11. Security

- PII on candidates masked in listing views
- Performance reviews restricted to cycle participants and authorized roles
- Asset operations audited
- Offboarding approval requires elevated permission

## 12. Test Strategy

- Domain tests for state machines (onboarding, offboarding, performance finalization)
- Application tests for conversion flow
- Integration tests for cross-module event flow
- Permission/data-scope tests for restricted endpoints
