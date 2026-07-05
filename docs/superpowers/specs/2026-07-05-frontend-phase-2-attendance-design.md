# Frontend Phase 2 — Attendance Module Design

Date: 2026-07-05
Status: Draft for user review
Scope: Attendance module frontend — retro-doc + gaps

## 1. Mục tiêu

Tài liệu hóa frontend Attendance module đang chạy + bổ sung AdjustmentRequest UI.

## 2. UI Conventions

Giống Shift spec §2.

## 3. Hiện trạng

### 3.1. Đã ship

- `AttendanceListPage.tsx` — hai section:
  - **Kỳ công**: DataTable period list + Calculate button (gọi `POST /attendance/calculate`).
  - **Bảng công**: DataTable timesheets (work_date, expected/worked/late/early/OT minutes, result_status).
- Close/Reopen period buttons inline.
- Models, services, hooks cho periods, timesheets, calculate, close, reopen.
- Route: `app/(dashboard)/attendance/page.tsx` → `AttendanceListPage`.

### 3.2. Backend API available

- `GET /attendance-periods` — list
- `GET /attendance/timesheets` — list
- `POST /attendance/calculate` — calculate
- `POST /attendance-periods/{id}/close`, `/reopen`
- `GET /attendance/adjustments` — list
- `POST /attendance/adjustments` — create
- `POST /attendance/adjustments/{id}/approve`, `/reject` — inline approve/reject

### 3.3. Gaps

- **AdjustmentRequest service + hooks**: chưa có (`attendanceService.ts` chỉ có periods + timesheets calls).
- **AdjustmentRequest UI**: chưa có form submit từ timesheet, chưa có list/approve/reject.
- **RawLog view**: skip (admin debug tool, defer).

## 4. Design — Adjustment request integration

### 4.1. Adjustment trigger

Trong timesheet table, thêm Action column: nút "Điều chỉnh" (Pencil icon) → mở Drawer `AdjustmentForm`.

### 4.2. AdjustmentForm

- **Fields**: reason (textarea, required, min 5 chars), evidence_file (file input, optional).
- Submit → `POST /attendance/adjustments` → toast + refetch.

### 4.3. Adjustment list section

Thêm section "Yêu cầu điều chỉnh" dưới bảng công:

- **DataTable**: employee, timesheet date, reason, evidence file link, status, created_at.
- **Filter**: tabs `Chờ duyệt` / `Đã duyệt` / `Từ chối`.
- **Row action (pending only)**: Approve (CheckCircle, green) / Reject (XCircle, red).
- Approve → `POST /attendance/adjustments/{id}/approve` → refetch.
- Reject → Drawer nhập lý do → `POST /attendance/adjustments/{id}/reject`.

### 4.4. File map

- **Create**: `src/domains/attendance/services/adjustmentService.ts` — `getAdjustments()`, `createAdjustment()`, `approveAdjustment()`, `rejectAdjustment()`
- **Create**: `src/domains/attendance/hooks/useAdjustments.ts` — React Query hooks cho adjustment mutations + queries
- **Create**: `src/domains/attendance/components/AdjustmentSection.tsx` — DataTable adjustments + filter tabs + approve/reject actions
- **Create**: `src/domains/attendance/components/AdjustmentForm.tsx` — Drawer với reason + evidence_file
- **Modify**: `src/domains/attendance/components/AttendanceListPage.tsx` — thêm AdjustmentSection + nút "Điều chỉnh" trong timesheet action column

### 4.5. Không trong scope

- RawLog browser (defer)
- Bulk adjust (1 form nhiều timesheets)
- Workflow BC integration for adjustments (giữ nguyên inline approval)
