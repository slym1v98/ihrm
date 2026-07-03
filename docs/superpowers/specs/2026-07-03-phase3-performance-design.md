# Phase 3 Performance BC Design

Version: 0.1
Date: 2026-07-03
Status: Design approved (brainstorming)

## 1. Scope

Build Performance Management module (`app/Modules/Performance/`) as the fourth Phase 3 sub-project. Covers performance cycles, goals, competency templates, multi-stage reviews (self → manager → HR), scoring, finalization, and workflow integration.

**In scope:** PerformanceCycle, PerformanceReview, Goal, CompetencyTemplate; 3-stage review (self/manager/HR); weighted scoring; finalization with workflow sign-off; Workflow BC integration for cycle activation and review finalization; Notification via events; permission integration; full test suite.

**Out of scope:** Compensation/promotion linkage, automated cycle triggers, peer reviews, 360 feedback, calibration sessions, advanced analytics, employee self-service dashboard beyond API.

## 2. Architecture

DDD 3-layer matching Phase 3 conventions.

```
Module/Performance/
  Domain/           — Pure PHP, no Laravel deps
  Application/      — Commands/Handlers + Queries
  Infrastructure/   — Eloquent, HTTP, workflow integration, seeders, routes
```

## 3. Schema

### performance_cycles
id(uuid PK), code(varchar50 unique), name, description(nullable), start_date(date), end_date(date), status(varchar20: draft/active/completed/cancelled), scoring_rules(jsonb: weights, max_score, pass_threshold), workflow_request_id(uuid nullable), timestamps

### performance_reviews
id(uuid PK), cycle_id(uuid FK), employee_id(uuid), self_assessment(jsonb), manager_assessment(jsonb), hr_assessment(jsonb), final_score(decimal nullable), status(varchar20: pending_self/self_completed/manager_completed/hr_completed/finalized), finalized_at(timestamp nullable), timestamps. Unique(cycle_id, employee_id)

### goals
id(uuid PK), cycle_id(uuid FK), employee_id(uuid nullable), title, description, weight(decimal), target_value(text nullable), actual_value(text nullable), status(varchar20: active/completed/archived), sort_order(int), timestamps

### competency_templates
id(uuid PK), code(varchar50 unique), name, rules(jsonb), active(bool), timestamps

## 4. Domain Model

**Value Objects:** CycleStatus (draft/active/completed/cancelled), ReviewStatus (pending_self/self_completed/manager_completed/hr_completed/finalized — strict progression), GoalStatus (active/completed/archived)

**Aggregates:**
- **PerformanceCycle:** activate() via workflow, complete(), cancel(). Invariants: start<end, active cycles locked for edit
- **PerformanceReview:** submitSelf(), submitManager(), submitHr(), finalize(). Strict stage progression. Finalized = immutable.
- **Goal:** complete(actualValue), archive()
- **CompetencyTemplate:** CRUD, no lifecycle

**Events:** CycleActivated, CycleCompleted, ReviewCreated, SelfAssessmentSubmitted, ManagerReviewSubmitted, HrReviewSubmitted, ReviewFinalized, GoalCompleted, GoalArchived

## 5. API

20 endpoints under `/api/v1/performance/*` covering cycles CRUD+activate/complete/cancel, reviews CRUD+self/manager/hr/finalize, goals CRUD+complete, competency-templates CRUD.

## 6. Permissions

Codes: performance.cycle.{view,create,update,activate,complete,cancel}, performance.review.{view,create,submit_self,submit_manager,submit_hr,finalize}, performance.goal.{view,create,update,complete}, performance.template.{view,create,update,delete}. SUPER_ADMIN + HR roles → all. Dept Manager → review.view/submit_manager, goal.*. Employee → review.view/submit_self (own).

## 7. Acceptance Criteria
1. ✅ DDD layout conventions
2. ✅ 4 aggregates: cycle, review, goal, competency template
3. ✅ Multi-stage review: self → manager → HR → finalized
4. ✅ Workflow for cycle activation + review finalization
5. ✅ Weighted scoring with validation
6. ✅ Finalized reviews immutable
7. ✅ Goals assignable per-employee or cycle-wide
8. ✅ Competency templates for reusable scoring rules
9. ✅ API routes + permissions seeded
10. ✅ Tests exist
