# Phase 3 Training BC Design

Version: 0.1
Date: 2026-07-03
Status: Design approved

## 1. Scope

Build Training Management module (`app/Modules/Training/`) as the fifth Phase 3 sub-project. Covers course catalog, session scheduling, employee enrollment, attendance tracking, results, and certificate output.

**In scope:** TrainingCourse, TrainingSession, TrainingEnrollment, TrainingResult; flexible attendance (present/absent + check-in/check-out); capacity guard; permission integration; full test suite.

**Out of scope:** Learning paths, self-service LMS portal, trainer management, course content hosting, approval workflows, automated certificate generation beyond code assignment.

## 2. Architecture

DDD 3-layer matching Phase 3 conventions.

```
Module/Training/
  Domain/           — Pure PHP, no Laravel deps
  Application/      — Commands/Handlers + Queries
  Infrastructure/   — Eloquent, HTTP, seeders, routes
```

## 3. Schema

### training_courses
id(uuid PK), code(varchar100 unique), name, description(nullable), category(nullable), default_duration_hours(int nullable), max_participants(int nullable), active(bool default true), timestamps

### training_sessions
id(uuid PK), course_id(uuid FK), code(varchar100), name, start_date(datetime), end_date(datetime), location(nullable), instructor(nullable), max_participants(int nullable), status(varchar20: scheduled/active/completed/cancelled), timestamps

### training_enrollments
id(uuid PK), session_id(uuid FK), employee_id(uuid), enrolled_at(datetime), attendance(jsonb nullable), status(varchar20: enrolled/completed/cancelled), timestamps. Unique(session_id, employee_id)

### training_results
id(uuid PK), enrollment_id(uuid FK unique), score(decimal 5,2 nullable), passed(bool nullable), certificate_code(varchar100 nullable), issued_at(datetime nullable), notes(text nullable), timestamps

## 4. Domain Model

### Value Objects
- **CourseStatus**: active/inactive
- **SessionStatus**: scheduled/active/completed/cancelled
- **EnrollmentStatus**: enrolled/completed/cancelled

### Aggregates
- **TrainingCourse**: CRUD. DELETE = deactivate (set active=false). Active sessions block deactivation.
- **TrainingSession**: create from course, capacity guard on enroll. Fields frozen after completed/cancelled.
- **TrainingEnrollment**: enroll → record attendance → complete/cancel. Unique (session_id, employee_id). Attendance JSONB: `{"checked_in_at":"...","checked_out_at":"...","present":true}`.
- **TrainingResult**: 1:1 with enrollment, optional. Populated after session completion.

### Events
SessionScheduled, EmployeeEnrolled, EnrollmentCancelled, AttendanceRecorded, ResultRecorded, TrainingCompleted

## 5. API

15 endpoints under `/api/v1/training/*`:
- Courses: GET list, POST create, GET show, PUT update, DELETE deactivate
- Sessions: GET list (by course), POST create, GET show, PUT update
- Enrollments: POST enroll, POST cancel, POST attendance
- Results: POST record, GET show

## 6. Permissions

Codes: training.course.{view,create,update,delete}, training.session.{view,create,update}, training.enrollment.{view,create,cancel}, training.result.{view,create}. SUPER_ADMIN + HR → all. Employee → enrollment.view/create/cancel (own, future guard).

## 7. Acceptance Criteria

1. ✅ DDD layout conventions
2. ✅ 4 aggregates: course, session, enrollment, result
3. ✅ Capacity guard: cannot enroll past session max_participants
4. ✅ Duplicate enrollment guard: unique (session_id, employee_id)
5. ✅ Flexible attendance: present/absent + check-in/check-out via JSONB
6. ✅ Result records with optional certificate_code
7. ✅ API routes + permissions seeded
8. ✅ Tests exist
