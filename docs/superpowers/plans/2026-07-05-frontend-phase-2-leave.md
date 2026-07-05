# Frontend Phase 2 — Leave Module Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add tabs to Leave page — "Đơn nghỉ" (existing) and "Tồn quỹ" (LeaveBalanceSection already exists).

**Architecture:** Tabs wrapper in the route page. No new component/hook needed — `LeaveBalanceSection` exists at `src/domains/leave/components/LeaveBalanceSection.tsx`.

**Tech Stack:** NextJS 14 App Router, React state, inline tab UI (no lib).

---

### Task 1: Verify LeaveBalanceSection is functional

**Files:**
- Read: `src/domains/leave/components/LeaveBalanceSection.tsx`

- [ ] **Confirm the component exists and renders a DataTable of balances.** File already contains: year, opening, accrued, used, remaining columns via `useLeaveBalances` + `useLeaveTypes`.

- [ ] **Check remaining columns**: spec calls for `carried_over` and `expired` columns. Verify whether present. If missing, add them.

If missing, add to columns array (position between `used` and `remaining`):

```typescript
{ header: 'Chuyển tiếp', accessor: 'carried_over', className: 'text-right w-16' },
{ header: 'Hết hạn', accessor: 'expired', className: 'text-right w-16' },
```

---

### Task 2: Tabs wrapper in Leave page route

**Files:**
- Modify: `src/app/(dashboard)/leave/page.tsx`

- [ ] **Rewrite route** to use tab state:

```tsx
'use client';

import { useState } from 'react';
import { LeaveListPage } from '@/domains/leave/components/LeaveListPage';
import { LeaveBalanceSection } from '@/domains/leave/components/LeaveBalanceSection';

const tabs = [
  { key: 'requests', label: 'Đơn nghỉ' },
  { key: 'balances', label: 'Tồn quỹ' },
] as const;

export default function LeavePage() {
  const [tab, setTab] = useState<'requests' | 'balances'>('requests');

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
      {tab === 'requests' ? <LeaveListPage /> : <LeaveBalanceSection />}
    </div>
  );
}
```

- [ ] **Verify** by visiting `/leave` in dev, switching tabs, and confirming both sections render.

---

### No tests needed

Pure UI composition. All logic already tested in backend.
