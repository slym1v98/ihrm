# Frontend Phase 2 — Attendance Module Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add AdjustmentRequest management UI (submit from timesheet + list/approve/reject).

**Architecture:** Service + hooks + two components (section + form). Embed in existing AttendanceListPage via new section below timesheets + action column.

**Tech Stack:** NextJS 14 App Router, React Query v5, react-hook-form v7, zod v3, shadcn-style DataTable/Drawer.

---

### Task 1: Adjustment service

**Files:**
- Create: `src/domains/attendance/services/adjustmentService.ts`

- [ ] **Create service** with 4 API calls following existing `attendanceService.ts` pattern:

```typescript
import { http } from '@/core/http/client';
import type { AttendanceAdjustmentRequest } from '@/domains/attendance/models/attendance';

interface ApiListResponse<T> { data: T[]; }
interface ApiOneResponse<T> { data: T; }

export const adjustmentService = {
  async list(): Promise<AttendanceAdjustmentRequest[]> {
    const r = await http.get<ApiListResponse<AttendanceAdjustmentRequest>>('/attendance/adjustments');
    return r.data.data;
  },

  async create(payload: { attendance_timesheet_id: string; reason: string }): Promise<AttendanceAdjustmentRequest> {
    const r = await http.post<ApiOneResponse<AttendanceAdjustmentRequest>>('/attendance/adjustments', payload);
    return r.data.data;
  },

  async approve(id: string): Promise<void> {
    await http.post(`/attendance/adjustments/${id}/approve`);
  },

  async reject(id: string, reason: string): Promise<void> {
    await http.post(`/attendance/adjustments/${id}/reject`, { reason });
  },
};
```

---

### Task 2: Adjustment hooks

**Files:**
- Create: `src/domains/attendance/hooks/useAdjustments.ts`

- [ ] **Create React Query hooks** for list, create, approve, reject:

```typescript
'use client';

import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { adjustmentService } from '@/domains/attendance/services/adjustmentService';

const ADJ_KEY = ['attendance-adjustments'];

export function useAdjustments() {
  return useQuery({ queryKey: ADJ_KEY, queryFn: adjustmentService.list });
}

export function useCreateAdjustment() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: (payload: Parameters<typeof adjustmentService.create>[0]) => adjustmentService.create(payload),
    onSuccess: () => qc.invalidateQueries({ queryKey: ADJ_KEY }),
  });
}

export function useApproveAdjustment() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: (id: string) => adjustmentService.approve(id),
    onSuccess: () => qc.invalidateQueries({ queryKey: ADJ_KEY }),
  });
}

export function useRejectAdjustment() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: ({ id, reason }: { id: string; reason: string }) => adjustmentService.reject(id, reason),
    onSuccess: () => qc.invalidateQueries({ queryKey: ADJ_KEY }),
  });
}
```

---

### Task 3: AdjustmentForm component

**Files:**
- Create: `src/domains/attendance/components/AdjustmentForm.tsx`

- [ ] **Create form component** — Drawer with reason textarea, triggered from timesheet action column.

```tsx
'use client';

import { useState, useCallback } from 'react';
import { toast } from 'sonner';
import { useCreateAdjustment } from '@/domains/attendance/hooks/useAdjustments';
import { Drawer, DrawerContent, DrawerHeader, DrawerTitle, DrawerDescription, DrawerBody, DrawerFooter } from '@/shared/components/ui/drawer';
import { Button } from '@/shared/components/ui/button';
import { Label } from '@/shared/components/ui/label';
import { extractErrorMessage } from '@/core/errors/messages';

interface Props {
  timesheetId: string | null;
  open: boolean;
  onOpenChange: (o: boolean) => void;
}

export function AdjustmentForm({ timesheetId, open, onOpenChange }: Props) {
  const [reason, setReason] = useState('');
  const create = useCreateAdjustment();

  const handleSubmit = useCallback(async () => {
    if (!timesheetId || !reason.trim()) return;
    try {
      await create.mutateAsync({ attendance_timesheet_id: timesheetId, reason: reason.trim() });
      toast.success('Gửi yêu cầu điều chỉnh thành công');
      setReason('');
      onOpenChange(false);
    } catch (raw) {
      toast.error(extractErrorMessage(raw));
    }
  }, [timesheetId, reason, create, onOpenChange]);

  return (
    <Drawer open={open} onOpenChange={(o) => { if (!o) { setReason(''); } onOpenChange(o); }}>
      <DrawerContent size="sm">
        <DrawerHeader>
          <DrawerTitle>Yêu cầu điều chỉnh công</DrawerTitle>
          <DrawerDescription>Nhập lý do điều chỉnh</DrawerDescription>
        </DrawerHeader>
        <DrawerBody>
          <div className="space-y-2">
            <Label htmlFor="adj-reason">Lý do <span className="text-destructive">*</span></Label>
            <textarea id="adj-reason" className="h-24 w-full rounded-md border bg-[hsl(var(--card))] px-2 py-1 text-[13px] outline-none focus:ring-2 focus:ring-primary resize-none"
              placeholder="Mô tả lý do điều chỉnh..."
              value={reason} onChange={(e) => setReason(e.target.value)} />
          </div>
        </DrawerBody>
        <DrawerFooter>
          <Button variant="ghost" onClick={() => onOpenChange(false)}>Hủy</Button>
          <Button onClick={handleSubmit} disabled={!reason.trim() || create.isPending}>Gửi yêu cầu</Button>
        </DrawerFooter>
      </DrawerContent>
    </Drawer>
  );
}
```

---

### Task 4: AdjustmentSection component

**Files:**
- Create: `src/domains/attendance/components/AdjustmentSection.tsx`

- [ ] **Create component** listing adjustments with approve/reject actions.

```tsx
'use client';

import { useCallback, useState } from 'react';
import { toast } from 'sonner';
import { CheckCircle, XCircle } from 'lucide-react';
import { useAdjustments, useApproveAdjustment, useRejectAdjustment } from '@/domains/attendance/hooks/useAdjustments';
import type { AttendanceAdjustmentRequest } from '@/domains/attendance/models/attendance';
import { DataTable, type Column } from '@/shared/components/DataTable';
import { Drawer, DrawerContent, DrawerHeader, DrawerTitle, DrawerDescription, DrawerBody, DrawerFooter } from '@/shared/components/ui/drawer';
import { Button } from '@/shared/components/ui/button';
import { Label } from '@/shared/components/ui/label';
import { Badge } from '@/shared/components/ui/badge';
import { extractErrorMessage } from '@/core/errors/messages';
import { useDateFormatter } from '@/shared/hooks/useDateFormatter';

const statusLabels: Record<string, string> = { pending: 'Chờ duyệt', approved: 'Đã duyệt', rejected: 'Từ chối' };
const statusVariant: Record<string, 'default' | 'secondary' | 'destructive'> = { pending: 'secondary', approved: 'default', rejected: 'destructive' };

export function AdjustmentSection() {
  const { formatDate } = useDateFormatter();
  const { data: adjustments, isLoading } = useAdjustments();
  const approveAdj = useApproveAdjustment();
  const rejectAdj = useRejectAdjustment();
  const [rejectTarget, setRejectTarget] = useState<{ id: string } | null>(null);
  const [rejectReason, setRejectReason] = useState('');

  const handleApprove = useCallback(async (id: string) => {
    try { await approveAdj.mutateAsync(id); toast.success('Đã duyệt'); }
    catch (raw) { toast.error(extractErrorMessage(raw)); }
  }, [approveAdj]);

  const handleReject = useCallback(async () => {
    if (!rejectTarget) return;
    try { await rejectAdj.mutateAsync({ id: rejectTarget.id, reason: rejectReason }); toast.success('Đã từ chối'); setRejectTarget(null); setRejectReason(''); }
    catch (raw) { toast.error(extractErrorMessage(raw)); }
  }, [rejectTarget, rejectReason, rejectAdj]);

  const columns: Column<AttendanceAdjustmentRequest>[] = [
    { header: 'Bảng công', accessor: 'attendance_timesheet_id', className: 'font-mono text-xs' },
    { header: 'Lý do', accessor: 'reason', className: 'max-w-xs truncate' },
    { header: 'Ngày tạo', accessor: undefined, cell: (r) => formatDate(r.created_at), className: 'font-mono text-xs w-28' },
    { header: 'Trạng thái', accessor: undefined, className: 'w-24', cell: (r) => <Badge variant={statusVariant[r.status] ?? 'secondary'}>{statusLabels[r.status] ?? r.status}</Badge> },
    { header: '', accessor: undefined, className: 'text-right w-20', cell: (r) => r.status === 'pending' ? (
      <div className="flex justify-end gap-1">
        <Button variant="ghost" size="sm" title="Duyệt" onClick={() => handleApprove(r.id)}><CheckCircle className="h-4 w-4 text-green-600" /></Button>
        <Button variant="ghost" size="sm" title="Từ chối" onClick={() => setRejectTarget({ id: r.id })}><XCircle className="h-4 w-4 text-destructive" /></Button>
      </div>
    ) : null },
  ];

  return (
    <>
      <DataTable columns={columns} data={adjustments ?? []} isLoading={isLoading} rowKey="id" emptyMessage="Chưa có yêu cầu điều chỉnh" />
      <Drawer open={!!rejectTarget} onOpenChange={(o) => { if (!o) { setRejectTarget(null); setRejectReason(''); } }}>
        <DrawerContent size="sm">
          <DrawerHeader><DrawerTitle>Từ chối điều chỉnh</DrawerTitle><DrawerDescription>Nhập lý do từ chối</DrawerDescription></DrawerHeader>
          <DrawerBody>
            <div className="space-y-2">
              <Label htmlFor="reject-reason">Lý do <span className="text-destructive">*</span></Label>
              <textarea id="reject-reason" className="h-24 w-full rounded-md border bg-[hsl(var(--card))] px-2 py-1 text-[13px] outline-none focus:ring-2 focus:ring-primary resize-none"
                value={rejectReason} onChange={(e) => setRejectReason(e.target.value)} />
            </div>
          </DrawerBody>
          <DrawerFooter>
            <Button variant="ghost" onClick={() => { setRejectTarget(null); setRejectReason(''); }}>Hủy</Button>
            <Button variant="destructive" onClick={handleReject} disabled={!rejectReason.trim() || rejectAdj.isPending}>Từ chối</Button>
          </DrawerFooter>
        </DrawerContent>
      </Drawer>
    </>
  );
}
```

---

### Task 5: Integrate into AttendanceListPage

**Files:**
- Modify: `src/domains/attendance/components/AttendanceListPage.tsx`

- [ ] **Add import** of `AdjustmentSection`, `AdjustmentForm` and `Pencil` icon at top.

- [ ] **Add state** for adjust dialog:

```typescript
const [adjustTimesheetId, setAdjustTimesheetId] = useState<string | null>(null);
```

- [ ] **Add action column** to `timesheetColumns` before the last column:

```typescript
{ header: '', accessor: undefined, className: 'text-right w-12', cell: (t) =>
  <Button variant="ghost" size="sm" title="Điều chỉnh" onClick={() => setAdjustTimesheetId(t.id)}>
    <Pencil className="h-4 w-4" />
  </Button>
},
```

- [ ] **Add AdjustmentSection** after the timesheets DataTable section, wrapped in `<div className="space-y-4">`:

```tsx
<div className="space-y-4">
  <span className="text-sm font-medium text-muted-foreground">Yêu cầu điều chỉnh</span>
  <AdjustmentSection />
</div>
```

- [ ] **Add AdjustmentForm** after the last closing `</div>`:

```tsx
<AdjustmentForm timesheetId={adjustTimesheetId} open={!!adjustTimesheetId} onOpenChange={(o) => { if (!o) setAdjustTimesheetId(null); }} />
```

---

### No tests needed

Pure UI composition. Backend tests cover adjustment API.
