# Payroll Enhancement Design

> Module: Payroll | Type: Feature Enhancement | Status: Draft

## 1. Goal

Transform Payroll frontend từ CRUD cơ bản thành công cụ quản lý lương nghiệp vụ: entry detail, adjustment workflow, payslip view, summary dashboard.

Backend đã có layer domain hoàn chỉnh (calculator, formula engine, tax, insurance, ports). Chính xác thì phần lớn code backend đã tồn tại — thiếu chủ yếu là frontend + 1-2 endpoint hỗ trợ.

## 2. Approach

Drawer-based expansion (thống nhất với UI pattern hiện tại của hệ thống). Không thêm route mới. Mỗi level click mở drawer với nội dung sâu hơn:

```
Period row → Drawer lg (entries list + summary)
  └─ Entry row → Drawer sm (lines breakdown + adjustments)
Payslip row → Drawer sm (payslip detail + download)
Summary bar (cố định trên trang chính)
```

## 3. Frontend Components

### 3.1. PayrollEntryDetail (new)
- `src/frontend/src/domains/payroll/components/PayrollEntryDetail.tsx`
- Props: `entryId: string` → fetch từ `GET /payroll/entries/{id}`
- Renders lines table (component, category, amount, note)
- Gross/Deductions/Net summary row
- Review button (if status=calculated)
- Adjustment section (list + add)
- Snapshot badges (contract, attendance, leave)

### 3.2. PayslipDetail (new)
- `src/frontend/src/domains/payroll/components/PayslipDetail.tsx`
- Props: `payslipId: string`
- Show payload lines, gross/ded/net summary
- Download button

### 3.3. PayrollListPage changes
- Add `useDateFormatter` + `useMoneyFormatter` (done)
- Add Summary bar at top (cards)
- Add click handler on period row → open PeriodDetailDrawer
- Add click handler on payslip row → open PayslipDrawer
- PeriodDetailDrawer: entry list table + summary badge

### 3.4. Model additions
```ts
export interface PayrollEntry {
  id: string; run_id: string; period_id: string; employee_id: string;
  gross_amount: number; deduction_amount: number; net_amount: number;
  status: string; error_message: string | null;
  lines: PayrollEntryLine[];
}
export interface PayrollEntryLine {
  component_id: string; category: string; amount: number; calculation_note: string | null;
}
export interface PayrollAdjustment {
  id: string; entry_id: string; component_id: string; amount: number; reason: string;
  status: string; created_by: string;
}
export interface PeriodSummary {
  employee_count: number; total_gross: number; total_deductions: number; total_net: number;
  status: string; locked_at: string | null;
}
```

### 3.5. Service additions
```ts
getEntry(id): PayrollEntry
getPeriodEntries(periodId, page): paginated Entries
getPeriodSummary(periodId): PeriodSummary
createAdjustment(entryId, payload)
approveAdjustment(id)
rejectAdjustment(id)
```

## 4. Backend additions

### 4.1. New endpoint
```
GET /api/v1/payroll/periods/{id}/summary
```
Response:
```json
{
  "data": {
    "employee_count": 6,
    "total_gross": 130500000,
    "total_deductions": 16200000,
    "total_net": 114300000,
    "status": "locked",
    "locked_at": "2026-06-03T12:00:00Z"
  }
}
```

### 4.2. Existing endpoints (re-use)
- `GET /payroll/periods/{periodId}/entries` → paginated with lines
- `GET /payroll/entries/{id}` → detail with lines
- `POST /payroll/entries/{id}/review`
- `GET /payroll/entries/{entryId}/adjustments`
- `POST /payroll/entries/{entryId}/adjustments`
- `POST /payroll/adjustments/{id}/approve`
- `POST /payroll/adjustments/{id}/reject`
- `GET /payroll/payslips/{id}` → detail
- `GET /payroll/payslips/{id}/download`

## 5. UI Flow

```
PayrollListPage
├── Summary bar (total gross/ded/net per last locked period)
├── Periods table
│   └── Click row → Drawer lg
│       ├── Summary badge (NV count, gross, ded, net)
│       ├── Entries table (employee, gross, ded, net, status)
│       │   └── Click row → Drawer sm → EntryDetail
│       │       ├── Lines breakdown
│       │       ├── Summary (gross → ded → net)
│       │       ├── Review button
│       │       └── Adjustments list + create
│       └── [Close]
├── Components table (no changes)
└── Payslips table
    └── Click row → Drawer sm → PayslipDetail
        ├── Lines from payload
        ├── Summary
        └── [Download PDF]
```

## 6. Error handling

- Drawer loading: spinner in body
- API failure: toast error, keep drawer open
- Adjustment validation error: inline form message
- Review failure: toast, keep footer review button

## 7. Non-goals

- PDF generation detail (payslip download uses backend's existing mechanism)
- Bank file export (Phase B)
- PIT/annual tax settlement (Phase B)
- Bulk entry editing (Phase B)
- Salary history chart (Phase B)

## 8. Files changed/created

### Frontend (new)
- `src/frontend/src/domains/payroll/components/PayrollEntryDetail.tsx`
- `src/frontend/src/domains/payroll/components/PayslipDetail.tsx`

### Frontend (modified)
- `src/frontend/src/domains/payroll/models/payroll.ts` — add Entry, Adjustment interfaces
- `src/frontend/src/domains/payroll/services/payrollService.ts` — add entry/adjustment/summary methods
- `src/frontend/src/domains/payroll/hooks/usePayroll.ts` — add hooks
- `src/frontend/src/domains/payroll/components/PayrollListPage.tsx` — add summary bar, drawer wiring

### Backend (new)
- `src/backend/app/Modules/Payroll/Infrastructure/Http/Controllers/PayrollSummaryController.php`
- Route: `GET /payroll/periods/{id}/summary`

### Backend (modified)
- `src/backend/app/Modules/Payroll/Routes/api.php` — add summary route
