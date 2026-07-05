# Frontend Phase 2 — Shift Module Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add ShiftAssignment management UI (assign/end) with tabs in existing Shift page.

**Architecture:** New `ShiftAssignmentSection` component with DataTable + Drawer. Add assignment hooks to existing `useShift.ts`. Wrap `ShiftListPage` + new section in tabs.

**Tech Stack:** NextJS 14 App Router, React Query v5, react-hook-form v7, zod v3, shadcn-style DataTable/Drawer.

---

### Task 1: Assignment hooks in useShift.ts

**Files:**
- Modify: `src/domains/shift/hooks/useShift.ts` — append after last export

- [ ] **Add 3 assignment hooks**

```typescript
export function useShiftAssignments() {
  return useQuery({ queryKey: ['shift-assignments'], queryFn: shiftService.getAssignments });
}

export function useCreateShiftAssignment() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: (payload: Parameters<typeof shiftService.createAssignment>[0]) => shiftService.createAssignment(payload),
    onSuccess: () => qc.invalidateQueries({ queryKey: ['shift-assignments'] }),
  });
}

export function useEndShiftAssignment() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: (id: string) => shiftService.endAssignment(id),
    onSuccess: () => qc.invalidateQueries({ queryKey: ['shift-assignments'] }),
  });
}
```

---

### Task 2: ShiftAssignmentSection component

**Files:**
- Create: `src/domains/shift/components/ShiftAssignmentSection.tsx`

- [ ] **Create the component** — DataTable listing all assignments with Drawer to add new.

Component uses:
- `useShiftAssignments`, `useCreateShiftAssignment`, `useEndShiftAssignment` (from Task 1)
- `useShiftTemplates` (already exists in `useShift.ts`)
- `useEmployees` from `@/domains/employee/hooks/useEmployees` — for employee select in form
- `useDepartments` from `@/domains/organization/hooks/useDepartments` — for department select in form
- `useEmployeeDisplayName` from `@/shared/hooks/useEmployeeDisplayName` — for display in table

Table columns: Shift template (name), Assignee (resolved name via employee/department hooks), Effective from, Effective to, Status (active/ended badge), Action (End button if active).

Drawer form: select shift_template_id, toggle employee/department type, select assignable_id (employee or department select), effective_from (date), effective_to (date, optional).

Use same DataTable/Drawer pattern as `ShiftListPage.tsx`. Toast on success/error. Zod schema matching `createAssignment` payload.

- [ ] **Verify that all imports resolve and the component renders** by running build check.

---

### Task 3: Tabs wrapper in Shift page route

**Files:**
- Modify: `src/app/(dashboard)/shift/page.tsx`

- [ ] **Rewrite route** to use tab state switching between `ShiftListPage` and `ShiftAssignmentSection`.

Use inline button tabs (no external tabs library needed):

```tsx
'use client';

import { useState } from 'react';
import { ShiftListPage } from '@/domains/shift/components/ShiftListPage';
import { ShiftAssignmentSection } from '@/domains/shift/components/ShiftAssignmentSection';

const tabs = [
  { key: 'templates', label: 'Ca làm việc' },
  { key: 'assignments', label: 'Phân ca' },
] as const;

export default function ShiftPage() {
  const [tab, setTab] = useState<'templates' | 'assignments'>('templates');

  return (
    <div className="space-y-4">
      <div className="flex gap-1 border-b">
        {tabs.map(t => (
          <button
            key={t.key}
            onClick={() => setTab(t.key)}
            className={`px-4 py-2 text-sm font-medium border-b-2 transition-colors ${
              tab === t.key
                ? 'border-primary text-primary'
                : 'border-transparent text-muted-foreground hover:text-foreground'
            }`}
          >
            {t.label}
          </button>
        ))}
      </div>
      {tab === 'templates' ? <ShiftListPage /> : <ShiftAssignmentSection />}
    </div>
  );
}
```

- [ ] **Verify page renders** without runtime error (manual check — visit `/shift`).

---

### No tests needed

This is pure UI composition (DataTable + Drawer + tabs). Backend tests cover assignment API. No business logic in these components.
