# Phase 1 Implementation Plan — Core Platform

Version: 0.1  
Date: 2026-06-30  
Status: Draft for review

## 1. Scope

Phase 1 Core Platform: Identity & Access, Organization, Employee Master, Contract, Employee Document, Configuration, Audit.

Detailed references:

- `docs/srs/01-core-platform-srs.md`
- `docs/superpowers/specs/2026-06-30-phase1-ddd-domain-model.md`
- `docs/erd/01-core-platform-erd.md`
- `docs/usecase/01-core-platform-uc.md`
- `docs/api/openapi/01-core-platform.openapi.yaml`
- `docs/tech/01-core-platform-tech.md`

## 2. Assumptions

- Laravel 12 / PHP 8.4 backend, NextJS frontend.
- Docker Compose for local dev (existing `docker-compose.yml` extended).
- PostgreSQL 16 primary, Redis 7 for cache/queue/session/lock, MinIO for files.
- Backend API-only (Sanctum bearer tokens).
- Backend and frontend can be parallelized after contract agreement.
- Team: 3 lanes (backend, frontend, infra/qa).

## 3. Definition of Done

Per epic:

- Backend: domain tests + HTTP feature tests + OpenAPI compliance.
- Frontend: component renders + form validation + error states + permission-aware.
- Infra: Docker compose works, migrations run, seeders load, CI green.

Per milestone:

- All epics in milestone meet DoD.
- No critical/high P1 bugs.
- Acceptance criteria from SRS/SRS per module pass.

## 4. Milestone Overview

| M# | Milestone | Backend | Frontend | Infra/QA | Weeks |
|---|---|---|---|---|---|
| M1 | Foundation | — | — | Docker, DB, NextJS scaffold, CI | 0.5 |
| M2 | Auth + Organization | Auth, RBAC, Org CRUD | Login, user mgmt, org screens | Seeders, API contract check | 1.5 |
| M3 | Employee Master | Employee create/transfer/search | Employee list/detail/form | E2E smoke create employee | 2 |
| M4 | Contract + Document | Contract, renewal, MinIO files | Contract/doc screens | Contract lifecycle E2E | 1.5 |
| M5 | Config + Audit | Lookup, code gen, audit search | Config screens, audit viewer | Audit pipeline integration | 1 |
| M6 | Stabilization | Bug fixes, perf, security | UX polish | Full E2E, perf test | 1.5 |
| — | Buffer | — | — | — | 1 |
| — | **Total** | | | | **~9–10** |

## 5. Epic Breakdown

### M1 — Foundation

**EPIC-01 Backend scaffold**

Tasks:
| # | Task | Owner | Estimate (d) |
|---|---|---|---|
| 1.1 | `docker-compose.yml` extend + php 8.4, pg 16, redis 7, minio service config | Infra | 0.5 |
| 1.2 | `laravel new` with Sanctum, `config/` adapters for pg, redis, minio | Infra | 0.3 |
| 1.3 | Laravel base structure: `app/Modules/`, service providers per module | Backend | 0.3 |
| 1.4 | Shared error handler, response macro, `traits/` | Backend | 0.3 |
| 1.5 | `phpunit.xml` domain + feature split | Backend | 0.1 |
| — | **Total** | | **1.5** |

Acceptance: `artisan test` passes empty suite; `curl localhost:8080` responds.

---

**EPIC-02 Frontend scaffold**

Tasks:
| # | Task | Owner | Estimate (d) |
|---|---|---|---|
| 2.1 | `create-next-app` + Tailwind + shadcn/ui setup | Frontend | 0.5 |
| 2.2 | API client module (`fetch` wrapper, Bearer token, error handling) | Frontend | 0.5 |
| 2.3 | Layout shell (sidebar, topbar, role-aware menu placeholder) | Frontend | 0.3 |
| 2.4 | NextJS app router structure (`/login`, `/dashboard`, `/employees`, etc.) | Frontend | 0.2 |
| — | **Total** | | **1.5** |

---

**EPIC-03 Dev infra + CI**

Tasks:
| # | Task | Owner | Estimate (d) |
|---|---|---|---|
| 3.1 | GitHub Actions: lint → static analysis → test → migration dry-run | Infra | 0.5 |
| 3.2 | `Makefile` for common dev tasks | Infra | 0.2 |
| — | **Total** | | **0.7** |

---

**EPIC-04 Shared API conventions**

Tasks:
| # | Task | Owner | Estimate (d) |
|---|---|---|---|
| 4.1 | Implement error envelope (ErrorResponse schema) | Backend | 0.2 |
| 4.2 | Implement pagination/filter/sort query params | Backend | 0.3 |
| 4.3 | Paginated JSON response format | Backend | 0.2 |
| 4.4 | Auth middleware (Sanctum token check) | Backend | 0.3 |
| 4.5 | Permission middleware (`permission:{code}`) | Backend | 0.3 |
| 4.6 | Data scope middleware/filter | Backend | 0.3 |
| — | **Total** | | **1.6** |

---

### M2 — Auth + Organization

**EPIC-05 Authentication**

Tasks:
| # | Task | Owner | Estimate (d) |
|---|---|---|---|
| 5.1 | Login endpoint (Sanctum token issue) | Backend | 0.5 |
| 5.2 | Logout endpoint (token revoke) | Backend | 0.2 |
| 5.3 | Login screen | Frontend | 0.5 |
| 5.4 | Auth context (token store, redirect) | Frontend | 0.3 |
| — | **Total** | | **1.5** |

---

**EPIC-06 RBAC + Data Scope**

Tasks:
| # | Task | Owner | Estimate (d) |
|---|---|---|---|
| 6.1 | Seed permissions catalog | Backend | 0.3 |
| 6.2 | Roles CRUD (create/deactivate) | Backend | 0.5 |
| 6.3 | Role-permission assignment | Backend | 0.3 |
| 6.4 | User-Role assignment with data scope | Backend | 0.5 |
| 6.5 | DataScopeAssignment CRUD | Backend | 0.3 |
| 6.6 | Permission middleware + data scope filter | Backend | 0.5 |
| 6.7 | User/role management screens | Frontend | 1.0 |
| — | **Total** | | **3.4** |

Dependencies: EPIC-05.

---

**EPIC-07 Organization CRUD**

Tasks:
| # | Task | Owner | Estimate (d) |
|---|---|---|---|
| 7.1 | Branch CRUD (create, deactivate, list) | Backend | 0.5 |
| 7.2 | Department CRUD with parent tree (cycle check) | Backend | 1.0 |
| 7.3 | Position CRUD | Backend | 0.3 |
| 7.4 | Branch/Department/Position screens | Frontend | 1.0 |
| — | **Total** | | **2.8** |

---

**EPIC-08 Organization tests**

Tasks:
| # | Task | Owner | Estimate (d) |
|---|---|---|---|
| 8.1 | Domain: Department tree no-cycle test | Backend | 0.3 |
| 8.2 | Feature: branch/department/position HTTP CRUD tests | Backend | 0.5 |
| — | **Total** | | **0.8** |

---

### M3 — Employee Master

**EPIC-09 Employee aggregate + migrations**

Tasks:
| # | Task | Owner | Estimate (d) |
|---|---|---|---|
| 9.1 | `CreateEmployee` migration + model | Backend | 0.5 |
| 9.2 | `EmployeeHistory` migration + model | Backend | 0.3 |
| 9.3 | `EmployeeReportingLine` migration + model | Backend | 0.3 |
| 9.4 | EmployeeCodeGenerator domain service + tests | Backend | 0.5 |
| 9.5 | Employee aggregate (create profile, change status, invariants) | Backend | 1.0 |
| — | **Total** | | **2.6** |

Dependencies: EPIC-07 (organization exists).

---

**EPIC-10 Create employee**

Tasks:
| # | Task | Owner | Estimate (d) |
|---|---|---|---|
| 10.1 | `POST /employees` endpoint | Backend | 0.5 |
| 10.2 | Employee create form screen | Frontend | 1.0 |
| 10.3 | Validation: required fields, unique code, active refs | Backend | 0.3 |
| 10.4 | Feature tests: create employee success + errors | Backend | 0.5 |
| — | **Total** | | **2.3** |

---

**EPIC-11 Transfer/status/manager change**

Tasks:
| # | Task | Owner | Estimate (d) |
|---|---|---|---|
| 11.1 | `POST /employees/{id}/status-changes` | Backend | 0.5 |
| 11.2 | Transfer department + change manager (PUT employee) | Backend | 0.5 |
| 11.3 | EmployeeHistory creation on any change | Backend | 0.3 |
| 11.4 | Status/transfer screens | Frontend | 1.0 |
| — | **Total** | | **2.3** |

---

**EPIC-12 Employee listing/search/profile**

Tasks:
| # | Task | Owner | Estimate (d) |
|---|---|---|---|
| 12.1 | GET /employees with pagination, filter, sort | Backend | 0.5 |
| 12.2 | GET /employees/{id} detail | Backend | 0.3 |
| 12.3 | Employee list with search + filters | Frontend | 1.0 |
| 12.4 | Employee profile detail page | Frontend | 1.0 |
| 12.5 | Feature tests: list/detail with data scope | Backend | 0.5 |
| — | **Total** | | **3.3** |

---

### M4 — Contract + Document

**EPIC-13 Contract lifecycle**

Tasks:
| # | Task | Owner | Estimate (d) |
|---|---|---|---|
| 13.1 | Contract + ContractType migrations | Backend | 0.3 |
| 13.2 | Contract aggregate (create draft, activate, terminate) | Backend | 1.0 |
| 13.3 | `POST /employee-contracts` + GET list | Backend | 0.5 |
| 13.4 | Contract list/create screen | Frontend | 1.0 |
| 13.5 | Feature tests: create, list, terminate | Backend | 0.5 |
| — | **Total** | | **3.3** |

---

**EPIC-14 Contract renewal**

Tasks:
| # | Task | Owner | Estimate (d) |
|---|---|---|---|
| 14.1 | ContractRenewalPolicy domain service + tests | Backend | 0.5 |
| 14.2 | `POST /employee-contracts/{id}/renew` | Backend | 0.5 |
| 14.3 | Contract renewal screen | Frontend | 0.5 |
| — | **Total** | | **1.5** |

---

**EPIC-15 Document upload/download with MinIO**

Tasks:
| # | Task | Owner | Estimate (d) |
|---|---|---|---|
| 15.1 | MinIO adapter (config, bucket init) | Backend | 0.3 |
| 15.2 | FileObject entity + migration + MinIO upload logic | Backend | 0.5 |
| 15.3 | EmployeeDocument aggregate + migration | Backend | 0.5 |
| 15.4 | `POST /employee-documents` (multipart) | Backend | 0.5 |
| 15.5 | `GET /employee-documents/{id}/download` (presigned URL) | Backend | 0.3 |
| 15.6 | Document upload screen with drag-drop | Frontend | 1.0 |
| 15.7 | Document list/download screen | Frontend | 0.5 |
| 15.8 | Feature/integration tests: upload, download, MinIO interaction | Backend | 0.5 |
| — | **Total** | | **4.1** |

Dependencies: MinIO available (M1).

---

**EPIC-16 Document expiry metadata**

Tasks:
| # | Task | Owner | Estimate (d) |
|---|---|---|---|
| 16.1 | Expiry metadata on document upload/replace | Backend | 0.3 |
| 16.2 | Scheduled command: mark expired documents | Backend | 0.3 |
| 16.3 | Expiry date field on upload form | Frontend | 0.3 |
| — | **Total** | | **0.9** |

---

### M5 — Config + Audit

**EPIC-17 Lookup/config management**

Tasks:
| # | Task | Owner | Estimate (d) |
|---|---|---|---|
| 17.1 | LookupGroup + LookupValue migrations + aggregate | Backend | 0.5 |
| 17.2 | `GET /lookups`, `POST/PUT lookup-values` | Backend | 0.5 |
| 17.3 | Lookup management screens | Frontend | 0.5 |
| — | **Total** | | **1.5** |

---

**EPIC-18 Code generation rules**

Tasks:
| # | Task | Owner | Estimate (d) |
|---|---|---|---|
| 18.1 | CodeGenerationRule migration + aggregate + repository | Backend | 0.5 |
| 18.2 | Code generation service integration with employee | Backend | 0.3 |
| 18.3 | Code rule management screen | Frontend | 0.3 |
| — | **Total** | | **1.1** |

---

**EPIC-19 Audit event pipeline**

Tasks:
| # | Task | Owner | Estimate (d) |
|---|---|---|---|
| 19.1 | AuditLog migration + repository | Backend | 0.3 |
| 19.2 | Domain/application event classes per Module | Backend | 1.0 |
| 19.3 | AuditLogSubscriber per module | Backend | 0.5 |
| 19.4 | before/after payload capture for material changes | Backend | 0.5 |
| 19.5 | Feature tests: audit log written on create/update/delete | Backend | 0.5 |
| — | **Total** | | **2.8** |

---

**EPIC-20 Audit log search**

Tasks:
| # | Task | Owner | Estimate (d) |
|---|---|---|---|
| 20.1 | `GET /audit-logs` with filter params (actor, action, module, date range) | Backend | 0.5 |
| 20.2 | Audit log search screen | Frontend | 0.5 |
| — | **Total** | | **1.0** |

---

### M6 — Stabilization

Tasks:

| # | Task | Owner | Estimate (d) |
|---|---|---|---|
| 21.1 | OpenAPI contract validation test per endpoint | QA | 1.0 |
| 22.1 | Security review: permission checks on every API | Backend | 1.0 |
| 22.2 | Data scope boundary tests | Backend | 0.5 |
| 23.1 | E2E smoke: employee creation end-to-end (login → org → employee → contract → doc) | QA | 1.5 |
| 23.2 | E2E smoke: audit trail verify | QA | 0.5 |
| 24.1 | Performance: employee list with 1000 records | Backend | 0.5 |
| 24.2 | Performance: document upload 10 MB | Backend | 0.3 |
| 25.1 | UX polish: form validation messages, empty states, loading skeletons | Frontend | 1.0 |
| — | **Total** | | **6.3** |

## 6. Dependency Graph

```text
M1 Foundation
 ├── EPIC-01 (Backend scaffold)
 ├── EPIC-02 (Frontend scaffold)
 ├── EPIC-03 (CI infra)
 └── EPIC-04 (API conventions)
      │
      ▼
M2 Auth + Organization
 ├── EPIC-05 (Login) ←─ EPIC-04
 ├── EPIC-06 (RBAC/Scope) ←─ EPIC-04
 ├── EPIC-07 (Org CRUD) ←─ EPIC-04
 └── EPIC-08 (Org tests)
      │
      ▼
M3 Employee Master
 ├── EPIC-09 (Employee aggregate) ←─ EPIC-07
 ├── EPIC-10 (Create employee) ←─ EPIC-09
 ├── EPIC-11 (Transfer/status) ←─ EPIC-09
 └── EPIC-12 (List/search) ←─ EPIC-09
      │
      ├─────────────┐
      ▼             ▼
M4 Contract      M5 Config
 ├── EPIC-13    ├── EPIC-17 (Lookup)
 ├── EPIC-14    ├── EPIC-18 (Code gen)
 ├── EPIC-15    ├── EPIC-19 (Audit)
 └── EPIC-16    └── EPIC-20 (Search)
      │             │
      └────┬────────┘
           ▼
      M6 Stabilization
```

## 7. Frontend Sync Points

| Milestone | Backend API Available | Frontend Can Start |
| --- | --- | --- |
| M2 | POST /auth/login, GET/POST /users, GET/POST /roles, GET/POST /branches/departments/positions | Week 2 after M1 scaffold |
| M3 | GET/POST /employees, PUT /employees/{id} | Week 4 when M3 backend tasks complete |
| M4 | GET/POST /employee-contracts, POST /employee-documents, GET /employee-documents/{id}/download | Week 6 when contract/document endpoints done |
| M5 | GET /lookups, GET /audit-logs | Week 7 independent |

Frontend effort estimate: ~6-8 person-weeks (2 frontend devs × 3-4 weeks).

## 8. Risk Register

| Risk | Likelihood | Impact | Mitigation |
| --- | --- | --- | --- |
| MinIO integration complexity | Medium | Medium | Adopt Laravel Filesystem adapter early; test with Docker MinIO in M1 |
| Department tree cycle detection | Low | Low | Domain invariant test + DB-level parent_id != self |
| Audit pipeline overhead (15+ event classes) | Medium | Medium | Shared base event class per module; one subscriber per module |
| Data scope filter performance on large employee list | Low | Medium | Avoid in-query scope filtering; use database joins; monitor during M6 |
| Frontend backlog | Medium | High | Start frontend early with API stubs; parallelize milestone screens |
| Sanctum token customization | Low | Low | Use default token ability API; customize later if needed |

## 9. Effort Summary

| Lane | Total (man-days) | Duration (calendar weeks) | Notes |
| --- | --- | --- | --- |
| Backend | ~30 | ~5-6 | Parallel tasks across M2-M5 |
| Frontend | ~18 | ~4-5 | Parallel with backend starting M2 |
| Infra/QA | ~8 | 2-3 | Spikes at M1 and M6 |
| **Total** | **~56** | **~9-10** | With buffer; 2 backend + 1-2 frontend |

## 10. Key Technical Decisions During Implementation

- Permission lookup: cached in Redis after first load; invalidated on role/permission config changes.
- Employee code generation: prevent race via DB unique constraint + `DB::raw('...nextval...')` or atomic lock.
- MinIO presigned URLs: short TTL (5 min) for security; frontend can handle redirect.
- Audit: async via queue for high-frequency operations (attendance raw logs in Phase 2); sync for Phase 1.
- Data scope: SQL join to `data_scope_assignments` table, not in-memory filtering.

## 11. Out of Scope

- Mobile responsive beyond basic for Phase 1.
- Performance tuning beyond M6 baseline.
- Phase 2+ epics (attendance, leave, payroll, etc.).
- Full E2E test automation (Cypress/Playwright) — manual acceptance + HTTP feature tests sufficient for Phase 1.
