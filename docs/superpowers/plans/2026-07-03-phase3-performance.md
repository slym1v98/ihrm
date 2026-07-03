# Phase 3 Performance Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development or superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Build Performance module with cycles, 3-stage reviews, goals, competency templates, weighted scoring, workflow integration.

**Architecture:** DDD 3-layer, Workflow BC for cycle activation + review finalization, Eloquent repos, JSONB assessments/scoring_rules.

**Branch:** `feature/performance`. Work on branch only, PR to main.

---

## File Map

```
Create: 4 schema migrations (cycles, reviews, goals, competency_templates)
Domain: 4 VOs, 4 IDs, 4 aggregates, 9 events, 5 exceptions, 4 repository interfaces
Application: ~15 commands, ~15 handlers, 3 queries, 3 query handlers
Infrastructure: 4 Eloquent models, 4 repos, 4 controllers, 4 services, 2 jobs, seeder, routes
Modify: routes/api.php, AppServiceProvider, DatabaseSeeder
Tests: Unit/Modules/Performance/, Feature/Modules/Performance/
```

### Task 1: Migrations
Create 4 migration files: `performance_cycles`, `performance_reviews`, `goals`, `competency_templates`.

### Task 2: Domain — VOs + IDs
CycleStatus, ReviewStatus, GoalStatus enums + 4 ID classes.

### Task 3: Domain — Exceptions + Events
5 exceptions (not found + invalid transition + review stage), 9 events.

### Task 4-7: Aggregates
PerformanceCycle (activate/complete/cancel), PerformanceReview (submitSelf/Manager/Hr, finalize, stage guards), Goal (complete/archive), CompetencyTemplate (CRUD).

### Task 8-9: Repos + Models + Bindings
4 interfaces, 4 Eloquent models, 4 repos. Add 4 bindings to AppServiceProvider.

### Task 10-12: Application layer
Commands/handlers for all endpoints. 3 queries (list cycles/reviews/goals).

### Task 13-14: Services + Jobs
CycleWorkflowService (activate), ReviewWorkflowService (finalize), NotificationService. CycleActivatedJob, ReviewFinalizedJob.

### Task 15-16: Controllers + Routes
4 controllers, 20 routes under `/api/v1/performance/*`.

### Task 17: Seeder
PerformancePermissionSeeder with all permission codes. SUPER_ADMIN granted.

### Task 18-19: Tests
Domain tests for cycle lifecycle, review stage progression, goal completion, immutable finalize. Feature tests for happy path API flow.

### Task 20: Full verification + PR
