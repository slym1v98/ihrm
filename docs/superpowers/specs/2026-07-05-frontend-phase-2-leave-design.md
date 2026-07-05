# Frontend Phase 2 — Leave Module Design

Date: 2026-07-05
Status: Draft for user review
Scope: Leave module frontend — retro-doc + gaps

## 1. Mục tiêu

Tài liệu hóa frontend Leave module đang chạy + bổ sung LeaveBalance display.

## 2. UI Conventions

Giống Shift spec §2.

## 3. Hiện trạng

### 3.1. Đã ship

- `LeaveListPage.tsx` — full request lifecycle:
  - DataTable: leave type, time range, duration (minutes), reason, status badge.
  - Drawer create: leave_type_id (select), start_at/end_at (date), duration_unit (day/half_day/hour), reason (textarea).
  - Row actions: Approve (CheckCircle), Reject (XCircle), Cancel (Ban) — contextual theo status.
  - Reject Drawer: lý do từ chối (textarea, required).
- Models, services, hooks cho types, requests, balances, approve/reject/cancel.
- Routes: `app/(dashboard)/leave/page.tsx` → `LeaveListPage`.

### 3.2. Backend API available

- `GET /leave-types` — list
- `GET /leave-requests` — list
- `GET /leave-requests/{id}` — detail
- `POST /leave-requests` — create
- `POST /leave-requests/{id}/approve` — approve
- `POST /leave-requests/{id}/reject` — reject (body: `{ reason }`)
- `POST /leave-requests/{id}/cancel` — cancel
- `GET /leave-balances` — list all
- `GET /leave-balances/summary` — summary per employee

### 3.3. Gaps

- **LeaveBalance**: chưa có UI hiển thị tồn quỹ phép.
- **LeaveType/LeavePolicy CRUD**: skip — admin seed/DB, defer.

## 4. Design — Tabs restructure + Balance

### 4.1. Tab structure

```
[ Đơn nghỉ | Tồn quỹ ]
```

Tab "Đơn nghỉ": giữ nguyên `LeaveListPage` hiện tại.

### 4.2. Tab "Tồn quỹ" (`LeaveBalanceSection`)

- **DataTable**: employee name, leave type, opening (đầu năm), accrued (tích lũy), used (đã dùng), carried_over (tồn), expired (hết hạn), remaining (còn lại)
- Columns styling: remaining nổi bật (font-semibold). Dùng `useDateFormatter` cho year context.
- **Read-only**: admin xem, không edit.
- **Filter**: theo year (mặc định current), theo employee (search).
- **Empty state**: "Chưa có dữ liệu tồn quỹ cho năm này".

### 4.3. File map

- Create: `src/domains/leave/components/LeaveBalanceSection.tsx` — DataTable + filter year/employee
- Modify: `src/domains/leave/components/LeaveListPage.tsx` — thêm tab + render `LeaveBalanceSection`
- Keep: hooks/services đã có (`useLeaveBalances`, `useLeaveBalanceSummary`)

### 4.4. Không trong scope

- LeavePolicy CRUD (admin seed)
- LeaveType CRUD
- Employee request portal (self-service)
- Balance adjustment UI
END OF FILE