# Payroll Enhancement Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add period summary bar, entry detail drawer, payslip detail drawer, and adjustment management UI for Payroll module.

**Architecture:** Drawer-based expansion keeping single `/payroll` route. Backend adds one new summary endpoint; all others exist. Frontend adds 2 new components, extends model/service/hook layers.

**Tech Stack:** Laravel 12 (PHP 8.4), NextJS 14+, TypeScript, Tailwind, TanStack Query, react-hook-form, zod, lucide-react

---

### Task 1: Backend — PeriodSummaryController

**Files:**
- Create: `src/backend/app/Modules/Payroll/Infrastructure/Http/Controllers/PayrollSummaryController.php`
- Modify: `src/backend/app/Modules/Payroll/Routes/api.php`

- [ ] **Step 1: Write PayrollSummaryController**

```php
<?php

namespace App\Modules\Payroll\Infrastructure\Http\Controllers;

use App\Modules\Payroll\Infrastructure\Persistence\Eloquent\PayrollEntryModel;
use App\Modules\Payroll\Infrastructure\Persistence\Eloquent\PayrollPeriodModel;
use Illuminate\Http\JsonResponse;

class PayrollSummaryController
{
    public function show(string $periodId): JsonResponse
    {
        $period = PayrollPeriodModel::findOrFail($periodId);
        $summary = PayrollEntryModel::where('period_id', $periodId)
            ->selectRaw('count(*) as employee_count')
            ->selectRaw('coalesce(sum(gross_amount),0) as total_gross')
            ->selectRaw('coalesce(sum(deduction_amount),0) as total_deductions')
            ->selectRaw('coalesce(sum(net_amount),0) as total_net')
            ->first();

        return response()->json(['data' => [
            'employee_count' => (int) $summary->employee_count,
            'total_gross' => (float) $summary->total_gross,
            'total_deductions' => (float) $summary->total_deductions,
            'total_net' => (float) $summary->total_net,
            'status' => $period->status,
            'locked_at' => $period->locked_at,
            'period_code' => $period->period_code,
        ]]);
    }
}
```

- [ ] **Step 2: Add route**

Add inside `Route::prefix('v1')->middleware('auth:sanctum')->group(function () {`:
```php
Route::get('/payroll/periods/{id}/summary', [PayrollSummaryController::class, 'show'])->middleware('permission:payroll.period.view');
```

- [ ] **Step 3: Run backend tests**

```bash
docker compose run --rm app php artisan test --compact
```

---

### Task 2: Frontend — Models

**Files:**
- Modify: `src/frontend/src/domains/payroll/models/payroll.ts`

- [ ] **Step 1: Add interfaces**

```ts
export interface PayrollEntry {
  id: string; run_id: string; period_id: string; employee_id: string;
  gross_amount: number; deduction_amount: number; net_amount: number;
  status: string; error_message: string | null;
  reviewed_by: string | null; reviewed_at: string | null;
  lines: PayrollEntryLine[];
}
export interface PayrollEntryLine {
  component_id: string; category: string; amount: number; calculation_note: string | null;
}
export interface PayrollAdjustment {
  id: string; entry_id: string; component_id: string; amount: number; reason: string; status: string; created_by: string | null; created_at: string | null;
}
export interface PeriodSummary {
  employee_count: number; total_gross: number; total_deductions: number; total_net: number;
  status: string; locked_at: string | null; period_code: string;
}
```

---

### Task 3: Frontend — Service

**Files:**
- Modify: `src/frontend/src/domains/payroll/services/payrollService.ts`

- [ ] **Step 1: Add service methods**

```ts
async getPeriodEntries(periodId: string, page?: number): Promise<{data: PayrollEntry[]; meta: any}> {
  const r = await http.get(`/payroll/periods/${periodId}/entries?page=${page??1}`);
  return r.data;
},
async getEntry(id: string): Promise<PayrollEntry> {
  const r = await http.get<ApiOneResponse<PayrollEntry>>(`/payroll/entries/${id}`);
  return r.data.data;
},
async getPeriodSummary(periodId: string): Promise<PeriodSummary> {
  const r = await http.get<ApiOneResponse<PeriodSummary>>(`/payroll/periods/${periodId}/summary`);
  return r.data.data;
},
async getAdjustments(entryId: string): Promise<PayrollAdjustment[]> {
  const r = await http.get<ApiListResponse<PayrollAdjustment>>(`/payroll/entries/${entryId}/adjustments`);
  return r.data.data;
},
async createAdjustment(entryId: string, p: {component_id: string; amount: number; reason: string}): Promise<PayrollAdjustment> {
  const r = await http.post<ApiOneResponse<PayrollAdjustment>>(`/payroll/entries/${entryId}/adjustments`, p);
  return r.data.data;
},
async approveAdjustment(id: string): Promise<void> { await http.post(`/payroll/adjustments/${id}/approve`); },
async rejectAdjustment(id: string): Promise<void> { await http.post(`/payroll/adjustments/${id}/reject`); },
```

---

### Task 4: Frontend — Hooks

**Files:**
- Modify: `src/frontend/src/domains/payroll/hooks/usePayroll.ts`

- [ ] **Step 1: Add hooks**

```ts
export function usePeriodEntries(periodId: string|null) {
  return useQuery({queryKey:['payroll-entries',periodId],queryFn:()=>payrollService.getPeriodEntries(periodId!),enabled:!!periodId});
}
export function useEntry(id: string|null) {
  return useQuery({queryKey:['payroll-entry',id],queryFn:()=>payrollService.getEntry(id!),enabled:!!id});
}
export function usePeriodSummary(periodId: string|null) {
  return useQuery({queryKey:['payroll-summary',periodId],queryFn:()=>payrollService.getPeriodSummary(periodId!),enabled:!!periodId});
}
export function useAdjustments(entryId: string|null) {
  return useQuery({queryKey:['payroll-adjustments',entryId],queryFn:()=>payrollService.getAdjustments(entryId!),enabled:!!entryId});
}
export function useCreateAdjustment() {
  const qc=useQueryClient();
  return useMutation({mutationFn:({entryId,...p}:{entryId:string;component_id:string;amount:number;reason:string})=>payrollService.createAdjustment(entryId,p),onSuccess:(_,v)=>qc.invalidateQueries({queryKey:['payroll-adjustments',v.entryId]})});
}
export function useApproveAdjustment() {
  const qc=useQueryClient();
  return useMutation({mutationFn:(id:string)=>payrollService.approveAdjustment(id),onSuccess:()=>qc.invalidateQueries({queryKey:['payroll-adjustments']})});
}
export function useRejectAdjustment() {
  const qc=useQueryClient();
  return useMutation({mutationFn:(id:string)=>payrollService.rejectAdjustment(id),onSuccess:()=>qc.invalidateQueries({queryKey:['payroll-adjustments']})});
}
```

---

### Task 5: Frontend — PayrollEntryDetail component

**Files:**
- Create: `src/frontend/src/domains/payroll/components/PayrollEntryDetail.tsx`

- [ ] **Step 1: Write component**

Drawer sm with:
- Header: employee info (từ contract_snapshot)
- Body: lines table + summary row + adjustment section
  - Lines: component name → category → amount → note (fetch from API hoặc từ entry.lines)
  - Gross → Deductions → Net separator
  - Adjustments list (status badges: pending→"Chờ duyệt", approved→"Đã duyệt", rejected→"Từ chối")
  - "Thêm điều chỉnh" form: select component, input amount, textarea reason
- Footer: Review button (nếu status=calculated), Close

Imports: PayrollEntry, PayrollEntryLine, PayrollAdjustment, useEntry, useAdjustments, useCreateAdjustment, useApproveAdjustment, useRejectAdjustment, useMoneyFormatter, useForm+zod, Drawer components.

Render key:
```tsx
<Drawer open={!!entryId} onOpenChange={(o)=>{if(!o)setEntryId(null)}}>
  <DrawerContent size="sm">
    <DrawerHeader>
      <DrawerTitle>Chi tiết lương</DrawerTitle>
      <DrawerDescription>NV: {entry?.employee_id} · {data?.contract_snapshot?.base_salary ? formatMoney(data.contract_snapshot.base_salary) : ''}</DrawerDescription>
    </DrawerHeader>
    <DrawerBody>
      {/* Lines table */}
      <div className="space-y-1 text-[13px]">
        {entry?.lines?.map(line => (
          <div key={line.component_id} className="flex justify-between py-1 border-b">
            <span>{line.category} · {formatMoney(line.amount)}</span>
            <span className="text-muted-foreground">{line.calculation_note}</span>
          </div>
        ))}
      </div>
      {/* Summary */}
      <div className="mt-4 pt-2 border-t font-semibold flex justify-between">
        <span>Tổng thu nhập: {formatMoney(entry?.gross_amount)}</span>
        <span>Khấu trừ: {formatMoney(entry?.deduction_amount)}</span>
        <span>Thực nhận: {formatMoney(entry?.net_amount)}</span>
      </div>
      {/* Adjustments */}
      <SectionHeader>Điều chỉnh</SectionHeader>
      {adjustments?.map(a => <div>...</div>)}
      <Button size="sm" onClick={openAdjForm}>+ Thêm điều chỉnh</Button>
    </DrawerBody>
    <DrawerFooter>
      {entry?.status === 'calculated' && <Button onClick={handleReview}>Duyệt</Button>}
      <Button variant="ghost" onClick={()=>setEntryId(null)}>Đóng</Button>
    </DrawerFooter>
  </DrawerContent>
</Drawer>
```

---

### Task 6: Frontend — PayslipDetail component

**Files:**
- Create: `src/frontend/src/domains/payroll/components/PayslipDetail.tsx`

- [ ] **Step 1: Write component**

Drawer sm with:
- Header: "Phiếu lương · {period_code}"
- Body: lines từ payload, gross/ded/net summary, status badge
- Footer: download button (window.open to API URL), Close

Key logic:
```tsx
const {data:payslip}=useQuery({queryKey:['payslip',payslipId],queryFn:()=>http.get(`/payroll/payslips/${payslipId}`)});
// payload.lines hoặc dùng trực tiếp gross/deductions/net từ payslip
```

---

### Task 7: Frontend — PayrollListPage integration

**Files:**
- Modify: `src/frontend/src/domains/payroll/components/PayrollListPage.tsx`

- [ ] **Step 1: Add summary bar**

At top of return, before periods section:
```tsx
{/* Summary bar */}
{latestPeriod && <div className="grid grid-cols-5 gap-3 mb-4">
  <SummaryCard label="Nhân viên" value={summary?.employee_count} />
  <SummaryCard label="Tổng thu nhập" value={formatMoney(summary?.total_gross)} />
  <SummaryCard label="Bảo hiểm" value={formatMoney(summary?.total_deductions)} />
  <SummaryCard label="Thực nhận" value={formatMoney(summary?.total_net)} />
  <SummaryCard label="Trạng thái" value={statusL[summary?.status]??summary?.status} />
</div>}
```

Helper: `function SummaryCard({label,value}:{label:string;value?:any})`.

- [ ] **Step 2: Add period row click → entries drawer**

Period table: add `onClick` handler to row (via `cell` on first column or row-level click):
```tsx
cell:(p)=><button onClick={()=>setSelectedPeriod(p)} className="text-left font-mono text-xs">{p.period_code}</button>
```

Entries drawer (Drawer lg) với simple table + summary.

- [ ] **Step 3: Add payslip row click → payslip drawer**

Similar to entry:
```tsx
cell:(s)=>formatMoney(s.gross)  // clickable
// Click → setPayslipId(s.id)
```

- [ ] **Step 4: Wire all drawer states**

```ts
const [selectedPeriod, setSelectedPeriod] = useState<PayrollPeriod | null>(null);
const [selectedEntry, setSelectedEntry] = useState<string | null>(null);
const [selectedPayslip, setSelectedPayslip] = useState<string | null>(null);
```

---

### Task 8: Verify

- [ ] **Step 1: TypeScript check**

```bash
cd src/frontend && npx tsc --noEmit
```

- [ ] **Step 2: Backend tests**

```bash
docker compose run --rm app php artisan test --compact
```
