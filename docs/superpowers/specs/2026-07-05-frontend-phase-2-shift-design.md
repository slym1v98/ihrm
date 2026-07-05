# Frontend Phase 2 — Shift Module Design

Date: 2026-07-05
Status: Draft for user review
Scope: Shift module frontend — retro-doc + gaps

## 1. Mục tiêu

Tài liệu hóa frontend Shift module đang chạy + bổ sung gaps còn thiếu.

## 2. UI Conventions

- **Stack**: NextJS App Router page → domain component → React Query hook → Axios service → Laravel API.
- **Pattern**: `DataTable` + `Drawer` cho form CRUD. Inline action buttons trong table row.
- **Language**: UI text = tiếng Việt.
- **Permission**: HR Admin only (server 403). Không frontend guard.
- **Toast**: Sonner toast với `extractErrorMessage`.
- **API**: `ApiListResponse<T>` (`{ data: T[] }`), `ApiOneResponse<T>` (`{ data: T }`).

## 3. Hiện trạng

### 3.1. Đã ship

- `ShiftListPage.tsx` — DataTable + Drawer CRUD cho ShiftTemplate (code, name, start_time, end_time, is_overnight, break_minutes, late_tolerance).
- Activate/deactivate toggle inline trong table.
- Models, services (`shiftService.ts`), hooks (`useShift.ts`) cho template CRUD + activate/deactivate.
- Route: `app/(dashboard)/shift/page.tsx` → `ShiftListPage`.

### 3.2. Backend API available

- `GET /shift-templates` — list
- `POST /shift-templates` — create
- `PATCH /shift-templates/{id}` — update
- `POST /shift-templates/{id}/activate`, `/deactivate` — toggle
- `GET /shift-assignments` — list assignments
- `POST /shift-assignments` — create
- `POST /shift-assignments/{id}/end` — end
- `GET /employees/{id}/shifts` — get employee shifts

### 3.3. Gaps

- **ShiftAssignment UI**: chưa có tab/section để hiển thị + quản lý assignment.

## 4. Design — Tabs restructure

### 4.1. Tab structure

Page shift hiện tại → thêm navigation tabs:

```
[ Ca làm việc | Phân ca ]
```

Tab "Ca làm việc": giữ nguyên `ShiftListPage` hiện tại.

### 4.2. Tab "Phân ca" (`ShiftAssignmentSection`)

- **DataTable**: code ca làm việc, assignee (type + ID/name), effective_from → effective_to, active status
- **Drawer create**: shift_template_id (select), assignable_type (radio: employee | department), assignable_id (select phụ thuộc type), effective_from (date), effective_to (date, optional)
- **Row action**: "Kết thúc" button (gọi `POST /shift-assignments/{id}/end`), disabled nếu đã ended
- **Filter**: theo assignee type + active status

### 4.3. File map

- Create: `src/domains/shift/components/ShiftAssignmentSection.tsx` — DataTable + Drawer + filter
- Modify: `src/domains/shift/hooks/useShift.ts` — add `useShiftAssignments()`, `useCreateAssignment()`, `useEndAssignment()` (nếu chưa có)
- Modify: `src/app/(dashboard)/shift/page.tsx` — thêm tab navigation layout
- Keep: `ShiftListPage.tsx` unchanged

### 4.4. Không trong scope

- Recurrence rule UI (backend có, frontend enter plain text hoặc để sau)
- Shift schedule calendar view (Gantt/Scheduler → Phase 2.2)
END OF FILE