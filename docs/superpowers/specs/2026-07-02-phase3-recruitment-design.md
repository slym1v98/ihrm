# Phase 3 Recruitment BC Design

Version: 0.1
Date: 2026-07-02
Status: Design approved (brainstorming)

## 1. Scope

Build Recruitment module (`app/Modules/Recruitment/`) as the first Phase 3 sub-project. Covers requisitions, candidates, interviews, offers, Workflow approval for requisitions, and candidate→employee conversion stub.

**In scope:** `RecruitmentRequisition`, `Candidate`, `Interview`, `Offer`, requisition workflow integration with Phase 2 Workflow BC, duplicate candidate guard by email/phone, interview scorecards, offer accept/reject, candidate→employee conversion stub, permission integration with Identity, full test suite.

**Out of scope:** public job board/career site, onboarding plan creation, external applicant portal, interview calendar sync, offer letter document generation, notification delivery details (event-driven only), advanced recruiting analytics, delegated interview scheduling, multi-step scorecard correction workflow.

## 2. Architecture

Strict DDD tactical pattern with 3 layers, consistent with Phase 2 modules.

```
Module/Recruitment/
  Domain/         — Pure PHP, no Laravel deps
  Application/    — Commands/Handlers + Queries
  Infrastructure/ — Eloquent, HTTP, workflow integration, seeders, routes
```

**Key architectural decisions:**

- **4 aggregates:** `RecruitmentRequisition`, `Candidate`, `Interview`, `Offer`. Keep interviews first-class to preserve auditability and scorecard immutability.
- **Workflow BC integration:** Requisitions do not open directly. `CreateRequisition` creates draft. `SubmitRequisition` starts Workflow request. Workflow approval callback transitions requisition to `open`.
- **Conversion stub only:** Offer acceptance can later trigger Onboarding BC, but Phase 3 Recruitment only creates/links Employee and emits `CandidateHired`.
- **Reuse existing modules:** Workflow BC handles approval orchestration. Employee module handles employee record creation. Notification BC may consume recruitment events later but is not coupled here.

## 3. Module Layout

```
app/Modules/Recruitment/
  Domain/
    Aggregates/
      RecruitmentRequisition/
        RecruitmentRequisition.php
        RecruitmentRequisitionId.php
      Candidate/
        Candidate.php
        CandidateId.php
      Interview/
        Interview.php
        InterviewId.php
        Scorecard.php
      Offer/
        Offer.php
        OfferId.php
    ValueObjects/
      RequisitionStatus.php
      CandidateStatus.php
      InterviewStatus.php
      OfferStatus.php
      CandidateSource.php
    Events/
      RequisitionApproved.php
      RequisitionOpened.php
      CandidateAdded.php
      CandidateStageChanged.php
      InterviewScheduled.php
      ScorecardSubmitted.php
      OfferAccepted.php
      OfferRejected.php
      CandidateHired.php
    Repositories/
      RecruitmentRequisitionRepositoryInterface.php
      CandidateRepositoryInterface.php
      InterviewRepositoryInterface.php
      OfferRepositoryInterface.php
    Exceptions/
      RecruitmentRequisitionNotFoundException.php
      CandidateNotFoundException.php
      InterviewNotFoundException.php
      OfferNotFoundException.php
      DuplicateCandidateException.php
      InvalidStatusTransitionException.php
      CandidateConversionException.php
  Application/
    Commands/
      CreateRequisitionCommand.php
      UpdateRequisitionCommand.php
      SubmitRequisitionCommand.php
      AddCandidateCommand.php
      UpdateCandidateStageCommand.php
      ScheduleInterviewCommand.php
      SubmitScorecardCommand.php
      CreateOfferCommand.php
      AcceptOfferCommand.php
      RejectOfferCommand.php
      ConvertCandidateToEmployeeCommand.php
    CommandHandlers/
      ...
    Queries/
      ListRequisitionsQuery.php
      ListCandidatesQuery.php
      ListInterviewsQuery.php
      ListOffersQuery.php
    QueryHandlers/
      ...
  Infrastructure/
    Persistence/
      Eloquent/
        RecruitmentRequisitionModel.php
        CandidateModel.php
        InterviewModel.php
        OfferModel.php
      Repositories/
        EloquentRecruitmentRequisitionRepository.php
        EloquentCandidateRepository.php
        EloquentInterviewRepository.php
        EloquentOfferRepository.php
    Http/
      Controllers/
        RecruitmentRequisitionController.php
        CandidateController.php
        InterviewController.php
        OfferController.php
      Requests/
        ...
      Resources/
        ...
    Services/
      WorkflowIntegrationService.php
      EmployeeConversionService.php
    Jobs/
      RequisitionWorkflowApprovedJob.php
    Seeders/
      RecruitmentPermissionSeeder.php
  Routes/api.php
```

## 4. Schema

### `recruitment_requisitions`

| Column | Type | Notes |
|---|---|---|
| id | uuid | PK |
| department_id | uuid | FK to departments |
| position | varchar(255) | position title or snapshot |
| headcount | integer | |
| reason | text | |
| status | varchar(20) | draft / pending_approval / open / on_hold / closed / cancelled |
| workflow_request_id | uuid nullable | Workflow BC link |
| opened_at | timestamptz nullable | |
| closed_at | timestamptz nullable | |
| created_by | uuid | FK to users |
| timestamps | | |

### `recruitment_candidates`

| Column | Type | Notes |
|---|---|---|
| id | uuid | PK |
| requisition_id | uuid nullable | FK to recruitment_requisitions |
| employee_id | uuid nullable | FK to employees after conversion stub |
| full_name | varchar(255) | |
| email | varchar(255) nullable | unique nullable |
| phone | varchar(50) nullable | unique nullable |
| source | varchar(20) | referral / linkedin / website / agency / manual |
| cv_file_descriptor | varchar(255) nullable | document reference |
| status | varchar(20) | new / screening / interviewing / offered / hired / rejected / archived |
| notes | text nullable | |
| timestamps | | |

### `recruitment_interviews`

| Column | Type | Notes |
|---|---|---|
| id | uuid | PK |
| candidate_id | uuid | FK |
| requisition_id | uuid | FK |
| interviewers | jsonb | array of user IDs |
| scheduled_at | timestamptz | |
| status | varchar(20) | scheduled / completed / cancelled |
| scorecards | jsonb nullable | array of `{interviewer_id, score, comment, submitted_at}` |
| notes | text nullable | |
| timestamps | | |

### `recruitment_offers`

| Column | Type | Notes |
|---|---|---|
| id | uuid | PK |
| candidate_id | uuid | unique FK |
| requisition_id | uuid | FK |
| terms | jsonb | salary, title, start_date, employment_type |
| status | varchar(20) | draft / sent / accepted / rejected / withdrawn |
| accepted_at | timestamptz nullable | |
| rejected_at | timestamptz nullable | |
| created_by | uuid | FK to users |
| timestamps | | |

## 5. Domain Model

### 5.1 RecruitmentRequisition

Key attributes: `id`, `departmentId`, `position`, `headcount`, `reason`, `status`, `workflowRequestId`, `openedAt`, `closedAt`, `createdBy`.

Invariants:
- `draft → pending_approval → open/on_hold/closed/cancelled` only.
- Requisition cannot accept candidates unless `open`.
- Workflow approval is required before `open` unless privileged override is added later.

### 5.2 Candidate

Key attributes: `id`, `requisitionId`, `employeeId`, `fullName`, `email`, `phone`, `source`, `cvFileDescriptor`, `status`, `notes`.

Invariants:
- Duplicate candidate check uses configured identity keys (Phase 3 v1: email or phone).
- Candidate can convert to employee only once.
- Candidate cannot become `hired` without accepted offer.

### 5.3 Interview

Key attributes: `id`, `candidateId`, `requisitionId`, `interviewers[]`, `scheduledAt`, `status`, `scorecards[]`, `notes`.

Invariants:
- Scorecard entry by interviewer is immutable once submitted.
- Cannot submit scorecard on cancelled interview.

### 5.4 Offer

Key attributes: `id`, `candidateId`, `requisitionId`, `terms`, `status`, `acceptedAt`, `rejectedAt`, `createdBy`.

Invariants:
- One offer per candidate at a time (Phase 3 v1 unique `candidate_id`).
- Offer must be `accepted` before candidate conversion.
- Terminal statuses do not transition further.

## 6. Workflow Integration

`SubmitRequisitionHandler`:
1. Load requisition in `draft`.
2. Call Workflow BC to start request with subject_type=`recruitment_requisition`, subject_id=`requisition.id`.
3. Persist `workflow_request_id`, set status=`pending_approval`.

`RequisitionWorkflowApprovedJob`:
1. Receive Workflow approved callback/event.
2. Load requisition by `workflow_request_id`.
3. Transition to `open`, set `opened_at`.
4. Emit `RequisitionApproved`, `RequisitionOpened`.

Workflow rejection or return-for-edit is deferred in v1. Requisition stays `pending_approval` or can be cancelled manually.

## 7. Candidate→Employee Conversion Stub

`AcceptOfferHandler` sets `OfferStatus::Accepted`, `accepted_at`, candidate status=`hired`.

`ConvertCandidateToEmployeeHandler`:
1. Require accepted offer.
2. Create/link Employee via Employee module application/repository call.
3. Copy minimal fields: full_name, email, phone, department, position.
4. Set `candidate.employee_id`.
5. Emit `CandidateHired`.

If Employee module shape prevents creation, handler may emit `CandidateHired` and return `202 Accepted` with warning, leaving `employee_id` null. No Onboarding BC creation here.

## 8. API Endpoints

All under `/api/v1/recruitment/*`, Sanctum auth.

| Method | Path | Permission |
|---|---|---|
| GET | `/recruitment/requisitions` | recruitment.requisition.view |
| POST | `/recruitment/requisitions` | recruitment.requisition.create |
| PATCH | `/recruitment/requisitions/{id}` | recruitment.requisition.update |
| POST | `/recruitment/requisitions/{id}/submit` | recruitment.requisition.submit |
| GET | `/recruitment/candidates` | recruitment.candidate.view |
| POST | `/recruitment/candidates` | recruitment.candidate.create |
| PATCH | `/recruitment/candidates/{id}/stage` | recruitment.candidate.update |
| GET | `/recruitment/interviews` | recruitment.interview.view |
| POST | `/recruitment/interviews` | recruitment.interview.create |
| POST | `/recruitment/interviews/{id}/scorecard` | recruitment.interview.scorecard |
| GET | `/recruitment/offers` | recruitment.offer.view |
| POST | `/recruitment/offers` | recruitment.offer.create |
| POST | `/recruitment/offers/{id}/accept` | recruitment.offer.accept |
| POST | `/recruitment/offers/{id}/reject` | recruitment.offer.reject |
| POST | `/recruitment/offers/{id}/convert` | recruitment.offer.convert |

## 9. Permissions

Seeded permission codes:
- `recruitment.requisition.view`
- `recruitment.requisition.create`
- `recruitment.requisition.update`
- `recruitment.requisition.submit`
- `recruitment.candidate.view`
- `recruitment.candidate.create`
- `recruitment.candidate.update`
- `recruitment.interview.view`
- `recruitment.interview.create`
- `recruitment.interview.scorecard`
- `recruitment.offer.view`
- `recruitment.offer.create`
- `recruitment.offer.accept`
- `recruitment.offer.reject`
- `recruitment.offer.convert`

Default roles:
- Admin: all
- HR Manager: all
- HR Staff: all
- Recruiter: all (new role may be added later, Phase 3 v1 can map to HR Staff)
- Department Manager: requisition view, candidate view, interview view, interview scorecard

## 10. Error Handling

| Scenario | Behavior |
|---|---|
| Duplicate email/phone | `DuplicateCandidateException` → 422 |
| Submit requisition without workflow template | 422 |
| Invalid candidate stage transition | 422 |
| Scorecard submitted twice by same interviewer | 422 |
| Accept/reject terminal offer | 422 |
| Convert without accepted offer | 422 |
| Convert twice | 422 |
| Employee conversion fails | Candidate remains hired; API may return 202 with warning; no employee_id set |

## 11. Testing

### Domain Unit Tests
- Requisition status transitions
- Candidate stage transitions
- Duplicate-candidate guard logic
- Interview scorecard immutability
- Offer accept/reject/convert guards

### Application Unit Tests
- Create requisition starts workflow on submit
- Workflow approval opens requisition
- Add candidate blocks duplicates
- Offer accepted required before conversion

### Feature Tests
- Auth required for all endpoints
- Permission boundaries per area
- Happy path: requisition → submit → candidate → interview → scorecard → offer → accept → convert
- Duplicate candidate returns 422
- Convert without accepted offer returns 422

## 12. Acceptance Criteria

1. ✅ Recruitment BC follows Phase 3 DDD layout conventions.
2. ✅ 4 aggregates: requisition, candidate, interview, offer.
3. ✅ Requisition approval integrates Workflow BC.
4. ✅ Duplicate candidate detection by email/phone.
5. ✅ Interview scorecards immutable per interviewer.
6. ✅ Offer acceptance required before conversion.
7. ✅ Candidate→employee conversion stub exists without Onboarding dependency.
8. ✅ API routes and permissions seeded.
9. ✅ Domain, application, and feature tests exist.
