# Frontend Phase 2 — Payroll Module Design

Date: 2026-07-05
Status: Draft for user review
Scope: Payroll module frontend — retro-doc only

## 1. Mục tiêu

Tài liệu hóa frontend Payroll module đã ship. Không có gaps cần fill.

## 2. UI Conventions

Giống Shift spec §2.

## 3. Hiện trạng

### 3.1. Đã ship

**Page chính `PayrollListPage.tsx`:**

- **Kỳ lương** section: DataTable periods với lifecycle actions theo status:
  - `open`: N/A (cần submit-approval trước)
  - `pending_approval`: Approve / Reject
  - `approved`: Start Run (Play icon)
  - `completed`: Lock
  - `locked`: Publish payslips + Reopen
- **Thành phần lương** section: DataTable components + create Drawer + delete button.
- **Phiếu lương** section: DataTable payslips + publish button + download PDF.
- **Summary bar**: period_code, employee_count, total_gross, total_deductions, total_net (khi chọn period).
- **Entries table**: list entries per selected period, click entry → `PayrollEntryDetail`.
- **Drawer Create Period**: period_code, start_date, end_date, cutoff_date.
- **Drawer Create Component**: code, name, category, calculation_type, default_amount, taxable.

**`PayrollEntryDetail.tsx`:**

- Entry lines breakdown table (component, category, amount, calculation_note).
- Gross/Deductions/Net summary footer.
- **Adjustments section**: list with status badge + approve/reject actions (pending only).
- **Adjustment create form**: component select + amount + reason.
- **Review button** (status `calculated` → `reviewed`).

**`PayslipDetail.tsx`:**

- Lines breakdown + gross/net footer.
- **Download PDF** button (opens `/api/v1/payroll/payslips/{id}/download`).

**Services & Hooks:**

- Full coverage: periods CRUD, lifecycle actions (submit-approval/approve/reject/lock/reopen), start-run, components CRUD, entries list + detail, adjustments CRUD + approve/reject, payslips list + publish.
- Routes: `app/(dashboard)/payroll/page.tsx` → `PayrollListPage`.

### 3.2. Backend API consumed

- `GET/POST /payroll/periods`
- `POST /payroll/periods/{id}/submit-approval`, `/approve`, `/reject`, `/lock`, `/reopen`
- `POST /payroll/periods/{id}/start-run`
- `GET/POST /payroll/components`, `DELETE /payroll/components/{id}`
- `GET /payroll/periods/{id}/entries` (paginated)
- `GET /payroll/entries/{id}`
- `GET /payroll/periods/{id}/summary`
- `GET/POST /payroll/entries/{id}/adjustments`
- `POST /payroll/adjustments/{id}/approve`, `/reject`
- `GET /payroll/payslips`
- `POST /payroll/periods/{id}/publish`
- `GET /payroll/payslips/{id}/download` (browser opens)

### 3.3. Gaps

Không có gaps. Module đã hoàn chỉnh.

### 3.4. Không trong scope

- Export CSV (backend chưa có endpoint, spec defer)
- Batch payslip publish selectors (publish all / per employee)
- Employee self-service payslip portal
END OF FILE