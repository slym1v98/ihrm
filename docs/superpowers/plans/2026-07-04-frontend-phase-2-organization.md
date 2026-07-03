# Frontend Admin Phase 2 — Organization Implementation Plan

> **For agentic workers:** Use superpowers:executing-plans or superpowers:subagent-driven-development.

**Goal:** Build Organization module UI: Branch/Department/Position CRUD + Org Tree.

**Architecture:** Each entity gets models → services (Axios) → hooks (React Query) → page component. Forms use react-hook-form + zod. Toast for feedback.

**Tech Stack:** NextJS 14 App Router, React Query v5, react-hook-form v7, zod v3, shadcn/ui Table/Dialog/Select/Badge/Sonner.

---

## File Map

- Modify: src/frontend/package.json — add dependencies
- Modify: src/frontend/src/app/layout.tsx — wrap with QueryClientProvider + Toaster
- Modify: src/frontend/src/shared/components/AppSidebar.tsx — add Organization nav items
- Create: src/frontend/src/domains/organization/models/branch.ts
- Create: src/frontend/src/domains/organization/models/department.ts
- Create: src/frontend/src/domains/organization/models/position.ts
- Create: src/frontend/src/domains/organization/services/branchService.ts
- Create: src/frontend/src/domains/organization/services/departmentService.ts
- Create: src/frontend/src/domains/organization/services/positionService.ts
- Create: src/frontend/src/domains/organization/hooks/useBranches.ts
- Create: src/frontend/src/domains/organization/hooks/useDepartments.ts
- Create: src/frontend/src/domains/organization/hooks/usePositions.ts
- Create: src/frontend/src/domains/organization/components/BranchListPage.tsx
- Create: src/frontend/src/domains/organization/components/DepartmentListPage.tsx
- Create: src/frontend/src/domains/organization/components/PositionListPage.tsx
- Create: src/frontend/src/domains/organization/components/OrgTreePage.tsx
- Create: `src/frontend/src/app/(dashboard)/organization/branches/page.tsx`
- Create: `src/frontend/src/app/(dashboard)/organization/departments/page.tsx`
- Create: `src/frontend/src/app/(dashboard)/organization/positions/page.tsx`
- Create: `src/frontend/src/app/(dashboard)/organization/tree/page.tsx`
- Create: src/frontend/src/shared/components/ui/table.tsx
- Create: src/frontend/src/shared/components/ui/dialog.tsx
- Create: src/frontend/src/shared/components/ui/select.tsx
- Create: src/frontend/src/shared/components/ui/badge.tsx
- Create: src/frontend/src/shared/components/ui/label.tsx
- Create: src/frontend/src/shared/components/ui/sonner.tsx
- Create: src/frontend/src/shared/components/ui/textarea.tsx

---

### Task 1: Dependencies and Provider

**Files:**
- Modify: `src/frontend/package.json`
- Modify: `src/frontend/src/app/layout.tsx`
- Create: `src/frontend/src/shared/components/ui/table.tsx`
- Create: `src/frontend/src/shared/components/ui/dialog.tsx`
- Create: `src/frontend/src/shared/components/ui/select.tsx`
- Create: `src/frontend/src/shared/components/ui/badge.tsx`
- Create: `src/frontend/src/shared/components/ui/label.tsx`
- Create: `src/frontend/src/shared/components/ui/sonner.tsx`
- Create: `src/frontend/src/shared/components/ui/textarea.tsx`

- [ ] **Step 1: Install deps**

```bash
cd src/frontend
npm install @tanstack/react-query react-hook-form zod @hookform/resolvers sonner lucide-react
```

Expected: packages added to package.json.

- [ ] **Step 2: Add shadcn-style Table**

Create `src/frontend/src/shared/components/ui/table.tsx`:

```tsx
import * as React from 'react';
import { cn } from '@/core/utils/cn';

const Table = React.forwardRef<HTMLTableElement, React.HTMLAttributes<HTMLTableElement>>(
  ({ className, ...props }, ref) => (
    <div className="relative w-full overflow-auto">
      <table ref={ref} className={cn('w-full caption-bottom text-sm', className)} {...props} />
    </div>
  ),
);
Table.displayName = 'Table';

const TableHeader = React.forwardRef<HTMLTableSectionElement, React.HTMLAttributes<HTMLTableSectionElement>>(
  ({ className, ...props }, ref) => <thead ref={ref} className={cn('[&_tr]:border-b', className)} {...props} />,
);
TableHeader.displayName = 'TableHeader';

const TableBody = React.forwardRef<HTMLTableSectionElement, React.HTMLAttributes<HTMLTableSectionElement>>(
  ({ className, ...props }, ref) => <tbody ref={ref} className={cn('[&_tr:last-child]:border-0', className)} {...props} />,
);
TableBody.displayName = 'TableBody';

const TableRow = React.forwardRef<HTMLTableRowElement, React.HTMLAttributes<HTMLTableRowElement>>(
  ({ className, ...props }, ref) => (
    <tr ref={ref} className={cn('border-b transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted', className)} {...props} />
  ),
);
TableRow.displayName = 'TableRow';

const TableHead = React.forwardRef<HTMLTableCellElement, React.ThHTMLAttributes<HTMLTableCellElement>>(
  ({ className, ...props }, ref) => (
    <th ref={ref} className={cn('h-12 px-4 text-left align-middle font-medium text-muted-foreground [&:has([role=checkbox])]:pr-0', className)} {...props} />
  ),
);
TableHead.displayName = 'TableHead';

const TableCell = React.forwardRef<HTMLTableCellElement, React.TdHTMLAttributes<HTMLTableCellElement>>(
  ({ className, ...props }, ref) => (
    <td ref={ref} className={cn('p-4 align-middle [&:has([role=checkbox])]:pr-0', className)} {...props} />
  ),
);
TableCell.displayName = 'TableCell';

export { Table, TableHeader, TableBody, TableRow, TableHead, TableCell };
```

- [ ] **Step 3: Create Dialog**

Create `src/frontend/src/shared/components/ui/dialog.tsx` — standard shadcn Dialog with:
- Dialog, DialogTrigger, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter, DialogClose
- Uses Radix UI primitives approach: overlay + content with animations

Since we don't have @radix-ui/react-dialog, implement a lightweight version using React portal:

```tsx
'use client';
import * as React from 'react';
import { cn } from '@/core/utils/cn';

interface DialogContextValue { open: boolean; onOpenChange: (open: boolean) => void; }
const DialogContext = React.createContext<DialogContextValue>({ open: false, onOpenChange: () => {} });

export function Dialog({ children, open: controlledOpen, onOpenChange }: {
  children: React.ReactNode; open?: boolean; onOpenChange?: (open: boolean) => void;
}) {
  const [internalOpen, setInternalOpen] = React.useState(false);
  const isOpen = controlledOpen ?? internalOpen;
  const setOpen = onOpenChange ?? setInternalOpen;
  return <DialogContext.Provider value={{ open: isOpen, onOpenChange: setOpen }}>{children}</DialogContext.Provider>;
}

export function DialogTrigger({ children, asChild }: { children: React.ReactNode; asChild?: boolean }) {
  const { onOpenChange } = React.useContext(DialogContext);
  return React.cloneElement(React.Children.only(children) as React.ReactElement, { onClick: () => onOpenChange(true) });
}

export function DialogContent({ children, className }: { children: React.ReactNode; className?: string }) {
  const { open, onOpenChange } = React.useContext(DialogContext);
  if (!open) return null;
  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center">
      <div className="fixed inset-0 bg-black/50" onClick={() => onOpenChange(false)} />
      <div className={cn('relative z-50 w-full max-w-lg rounded-lg border bg-white p-6 shadow-lg', className)}>
        {children}
      </div>
    </div>
  );
}

export function DialogHeader({ children, className }: { children: React.ReactNode; className?: string }) {
  return <div className={cn('mb-4 space-y-1.5', className)}>{children}</div>;
}

export function DialogTitle({ children, className }: { children: React.ReactNode; className?: string }) {
  return <h2 className={cn('text-lg font-semibold', className)}>{children}</h2>;
}

export function DialogDescription({ children, className }: { children: React.ReactNode; className?: string }) {
  return <p className={cn('text-sm text-muted-foreground', className)}>{children}</p>;
}

export function DialogFooter({ children, className }: { children: React.ReactNode; className?: string }) {
  return <div className={cn('mt-4 flex justify-end gap-2', className)}>{children}</div>;
}

export function DialogClose({ children }: { children: React.ReactNode }) {
  const { onOpenChange } = React.useContext(DialogContext);
  return React.cloneElement(React.Children.only(children) as React.ReactElement, { onClick: () => onOpenChange(false) });
}
```

- [ ] **Step 4: Create Select, Badge, Label, Sonner, Textarea**

These follow the same minimal pattern as button/card/input.

Create `src/frontend/src/shared/components/ui/select.tsx`:
```tsx
'use client';
import * as React from 'react';
import { cn } from '@/core/utils/cn';

export function Select({ value, onChange, children, placeholder }: {
  value?: string; onChange?: (v: string) => void; children: React.ReactNode; placeholder?: string;
}) {
  return (
    <select
      value={value}
      onChange={(e) => onChange?.(e.target.value)}
      className="h-10 w-full rounded-md border bg-white px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-primary"
    >
      {placeholder ? <option value="">{placeholder}</option> : null}
      {children}
    </select>
  );
}

export function SelectItem({ value, children }: { value: string; children: React.ReactNode }) {
  return <option value={value}>{children}</option>;
}
```

Create `src/frontend/src/shared/components/ui/badge.tsx`:
```tsx
import { cn } from '@/core/utils/cn';

const variants = {
  default: 'bg-primary text-primary-foreground',
  secondary: 'bg-muted text-muted-foreground',
  destructive: 'bg-destructive text-white',
  outline: 'border text-foreground',
} as const;

export function Badge({ className, variant = 'default', ...props }: {
  className?: string; variant?: keyof typeof variants; children?: React.ReactNode;
}) {
  return <span className={cn('inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium', variants[variant], className)} {...props} />;
}
```

Create `src/frontend/src/shared/components/ui/label.tsx`:
```tsx
import { cn } from '@/core/utils/cn';

export function Label({ className, ...props }: React.LabelHTMLAttributes<HTMLLabelElement>) {
  return <label className={cn('text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70', className)} {...props} />;
}
```

Create `src/frontend/src/shared/components/ui/sonner.tsx`:
```tsx
'use client';
import { Toaster as SonnerToaster } from 'sonner';

export function Toaster() {
  return <SonnerToaster position="top-right" richColors closeButton />;
}
```

Create `src/frontend/src/shared/components/ui/textarea.tsx`:
```tsx
import * as React from 'react';
import { cn } from '@/core/utils/cn';

export function Textarea({ className, ...props }: React.TextareaHTMLAttributes<HTMLTextAreaElement>) {
  return (
    <textarea
      className={cn(
        'flex min-h-[80px] w-full rounded-md border bg-white px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-primary disabled:cursor-not-allowed disabled:opacity-50',
        className,
      )}
      {...props}
    />
  );
}
```

- [ ] **Step 5: Set up providers in layout.tsx**

Modify `src/frontend/src/app/layout.tsx` to wrap with `QueryClientProvider` + `Toaster`:

```tsx
import type { Metadata } from 'next';
import './globals.css';
import { Providers } from '@/core/providers';

export const metadata: Metadata = {
  title: 'iHRM Admin',
  description: 'iHRM Enterprise Admin Portal',
};

export default function RootLayout({ children }: { children: React.ReactNode }) {
  return (
    <html lang="vi">
      <body>
        <Providers>{children}</Providers>
      </body>
    </html>
  );
}
```

Create `src/frontend/src/core/providers.tsx`:
```tsx
'use client';
import React, { useState } from 'react';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { AuthProvider } from '@/domains/auth/hooks/useAuth';
import { Toaster } from '@/shared/components/ui/sonner';

export function Providers({ children }: { children: React.ReactNode }) {
  const [queryClient] = useState(() => new QueryClient({
    defaultOptions: { queries: { staleTime: 30_000, retry: 1 } },
  }));

  return (
    <QueryClientProvider client={queryClient}>
      <AuthProvider>
        {children}
        <Toaster />
      </AuthProvider>
    </QueryClientProvider>
  );
}
```

- [ ] **Step 6: Commit**

```bash
git add src/frontend/package.json src/frontend/package-lock.json src/frontend/src/core/providers.tsx src/frontend/src/app/layout.tsx src/frontend/src/shared/components/ui
git commit -m "feat(frontend): add Phase 2 deps and shared UI components"
```

---

### Task 2: Organization Models and Services

**Files:**
- Create: `src/frontend/src/domains/organization/models/branch.ts`
- Create: `src/frontend/src/domains/organization/models/department.ts`
- Create: `src/frontend/src/domains/organization/models/position.ts`
- Create: `src/frontend/src/domains/organization/services/branchService.ts`
- Create: `src/frontend/src/domains/organization/services/departmentService.ts`
- Create: `src/frontend/src/domains/organization/services/positionService.ts`

- [ ] **Step 1: Create Branch model**

Create `src/frontend/src/domains/organization/models/branch.ts`:
```ts
export interface Branch {
  id: string;
  code: string;
  name: string;
  address: string | null;
  phone: string | null;
  email: string | null;
  status: string;
  created_at: string;
  updated_at: string;
}

export interface CreateBranchPayload {
  code: string;
  name: string;
  address?: string;
  phone?: string;
  email?: string;
}

export interface UpdateBranchPayload {
  name: string;
  address?: string;
  phone?: string;
  email?: string;
}
```

- [ ] **Step 2: Create Department model**

Create `src/frontend/src/domains/organization/models/department.ts`:
```ts
export interface Department {
  id: string;
  branch_id: string;
  parent_id: string | null;
  code: string;
  name: string;
  manager_employee_id: string | null;
  status: string;
  parent?: Department | null;
  created_at: string;
  updated_at: string;
}

export interface CreateDepartmentPayload {
  code: string;
  name: string;
  branch_id: string;
  parent_id?: string;
}

export interface UpdateDepartmentPayload {
  name: string;
  manager_employee_id?: string | null;
}

export interface MoveDepartmentPayload {
  new_parent_id: string | null;
}
```

- [ ] **Step 3: Create Position model**

Create `src/frontend/src/domains/organization/models/position.ts`:
```ts
export interface Position {
  id: string;
  code: string;
  name: string;
  level: number | null;
  description: string | null;
  status: string;
  created_at: string;
  updated_at: string;
}

export interface CreatePositionPayload {
  code: string;
  name: string;
  level?: number;
  description?: string;
}

export interface UpdatePositionPayload {
  name: string;
  level?: number;
  description?: string;
}
```

- [ ] **Step 4: Create Branch service**

Create `src/frontend/src/domains/organization/services/branchService.ts`:
```ts
import { http } from '@/core/http/client';
import type { Branch, CreateBranchPayload, UpdateBranchPayload } from '@/domains/organization/models/branch';

interface ApiListResponse<T> { data: T[]; meta?: { current_page: number; per_page: number; total: number; last_page: number }; }
interface ApiSingleResponse<T> { data: T; }

export const branchService = {
  async list(params?: Record<string, string>) {
    const res = await http.get<ApiListResponse<Branch>>('/branches', { params });
    return res.data;
  },
  async get(id: string) {
    const res = await http.get<ApiSingleResponse<Branch>>(`/branches/${id}`);
    return res.data.data;
  },
  async create(payload: CreateBranchPayload) {
    const res = await http.post<ApiSingleResponse<Branch>>('/branches', payload);
    return res.data.data;
  },
  async update(id: string, payload: UpdateBranchPayload) {
    const res = await http.patch<ApiSingleResponse<Branch>>(`/branches/${id}`, payload);
    return res.data.data;
  },
  async toggleStatus(id: string, action: 'activate' | 'deactivate') {
    const res = await http.post<ApiSingleResponse<Branch>>(`/branches/${id}/${action}`);
    return res.data.data;
  },
};
```

- [ ] **Step 5: Create Department service**

Create `src/frontend/src/domains/organization/services/departmentService.ts`:
```ts
import { http } from '@/core/http/client';
import type { Department, CreateDepartmentPayload, UpdateDepartmentPayload, MoveDepartmentPayload } from '@/domains/organization/models/department';

export const departmentService = {
  async list(params?: Record<string, string>) {
    const res = await http.get<{ data: Department[] }>('/departments', { params });
    return res.data;
  },
  async get(id: string) {
    const res = await http.get<{ data: Department }>(`/departments/${id}`);
    return res.data.data;
  },
  async create(payload: CreateDepartmentPayload) {
    const res = await http.post<{ data: Department }>('/departments', payload);
    return res.data.data;
  },
  async update(id: string, payload: UpdateDepartmentPayload) {
    const res = await http.patch<{ data: Department }>(`/departments/${id}`, payload);
    return res.data.data;
  },
  async move(id: string, payload: MoveDepartmentPayload) {
    const res = await http.post<{ data: Department }>(`/departments/${id}/move`, payload);
    return res.data.data;
  },
  async toggleStatus(id: string, action: 'activate' | 'deactivate') {
    const res = await http.post<{ data: Department }>(`/departments/${id}/${action}`);
    return res.data.data;
  },
};
```

- [ ] **Step 6: Create Position service**

Create `src/frontend/src/domains/organization/services/positionService.ts`:
```ts
import { http } from '@/core/http/client';
import type { Position, CreatePositionPayload, UpdatePositionPayload } from '@/domains/organization/models/position';

export const positionService = {
  async list(params?: Record<string, string>) {
    const res = await http.get<{ data: Position[] }>('/positions', { params });
    return res.data;
  },
  async get(id: string) {
    const res = await http.get<{ data: Position }>(`/positions/${id}`);
    return res.data.data;
  },
  async create(payload: CreatePositionPayload) {
    const res = await http.post<{ data: Position }>('/positions', payload);
    return res.data.data;
  },
  async update(id: string, payload: UpdatePositionPayload) {
    const res = await http.patch<{ data: Position }>(`/positions/${id}`, payload);
    return res.data.data;
  },
  async toggleStatus(id: string, action: 'activate' | 'deactivate') {
    const res = await http.post<{ data: Position }>(`/positions/${id}/${action}`);
    return res.data.data;
  },
};
```

- [ ] **Step 7: Commit**

```bash
git add src/frontend/src/domains/organization
git commit -m "feat(frontend): add organization models and services"
```

---

### Task 3: React Query Hooks

**Files:**
- Create: `src/frontend/src/domains/organization/hooks/useBranches.ts`
- Create: `src/frontend/src/domains/organization/hooks/useDepartments.ts`
- Create: `src/frontend/src/domains/organization/hooks/usePositions.ts`

- [ ] **Step 1: Branch hooks**

Create `src/frontend/src/domains/organization/hooks/useBranches.ts`:
```ts
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { branchService } from '@/domains/organization/services/branchService';
import type { CreateBranchPayload, UpdateBranchPayload } from '@/domains/organization/models/branch';

const BRANCHES_KEY = ['branches'];

export function useBranches(params?: Record<string, string>) {
  return useQuery({
    queryKey: [...BRANCHES_KEY, params],
    queryFn: () => branchService.list(params),
  });
}

export function useBranch(id: string) {
  return useQuery({
    queryKey: [...BRANCHES_KEY, id],
    queryFn: () => branchService.get(id),
    enabled: !!id,
  });
}

export function useCreateBranch() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: (payload: CreateBranchPayload) => branchService.create(payload),
    onSuccess: () => qc.invalidateQueries({ queryKey: BRANCHES_KEY }),
  });
}

export function useUpdateBranch() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: ({ id, payload }: { id: string; payload: UpdateBranchPayload }) => branchService.update(id, payload),
    onSuccess: () => qc.invalidateQueries({ queryKey: BRANCHES_KEY }),
  });
}

export function useToggleBranchStatus() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: ({ id, action }: { id: string; action: 'activate' | 'deactivate' }) => branchService.toggleStatus(id, action),
    onSuccess: () => qc.invalidateQueries({ queryKey: BRANCHES_KEY }),
  });
}
```

- [ ] **Step 2: Department hooks**

Create `src/frontend/src/domains/organization/hooks/useDepartments.ts`:
```ts
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { departmentService } from '@/domains/organization/services/departmentService';
import type { CreateDepartmentPayload, UpdateDepartmentPayload, MoveDepartmentPayload } from '@/domains/organization/models/department';

const DEPARTMENTS_KEY = ['departments'];

export function useDepartments(params?: Record<string, string>) {
  return useQuery({
    queryKey: [...DEPARTMENTS_KEY, params],
    queryFn: () => departmentService.list(params),
  });
}

export function useCreateDepartment() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: (payload: CreateDepartmentPayload) => departmentService.create(payload),
    onSuccess: () => qc.invalidateQueries({ queryKey: DEPARTMENTS_KEY }),
  });
}

export function useUpdateDepartment() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: ({ id, payload }: { id: string; payload: UpdateDepartmentPayload }) => departmentService.update(id, payload),
    onSuccess: () => qc.invalidateQueries({ queryKey: DEPARTMENTS_KEY }),
  });
}

export function useMoveDepartment() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: ({ id, payload }: { id: string; payload: MoveDepartmentPayload }) => departmentService.move(id, payload),
    onSuccess: () => qc.invalidateQueries({ queryKey: DEPARTMENTS_KEY }),
  });
}

export function useToggleDepartmentStatus() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: ({ id, action }: { id: string; action: 'activate' | 'deactivate' }) => departmentService.toggleStatus(id, action),
    onSuccess: () => qc.invalidateQueries({ queryKey: DEPARTMENTS_KEY }),
  });
}
```

- [ ] **Step 3: Position hooks**

Create `src/frontend/src/domains/organization/hooks/usePositions.ts`:
```ts
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { positionService } from '@/domains/organization/services/positionService';
import type { CreatePositionPayload, UpdatePositionPayload } from '@/domains/organization/models/position';

const POSITIONS_KEY = ['positions'];

export function usePositions(params?: Record<string, string>) {
  return useQuery({
    queryKey: [...POSITIONS_KEY, params],
    queryFn: () => positionService.list(params),
  });
}

export function useCreatePosition() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: (payload: CreatePositionPayload) => positionService.create(payload),
    onSuccess: () => qc.invalidateQueries({ queryKey: POSITIONS_KEY }),
  });
}

export function useUpdatePosition() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: ({ id, payload }: { id: string; payload: UpdatePositionPayload }) => positionService.update(id, payload),
    onSuccess: () => qc.invalidateQueries({ queryKey: POSITIONS_KEY }),
  });
}

export function useTogglePositionStatus() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: ({ id, action }: { id: string; action: 'activate' | 'deactivate' }) => positionService.toggleStatus(id, action),
    onSuccess: () => qc.invalidateQueries({ queryKey: POSITIONS_KEY }),
  });
}
```

- [ ] **Step 4: Commit**

```bash
git add src/frontend/src/domains/organization/hooks
git commit -m "feat(frontend): add organization React Query hooks"
```

---

### Task 4: Branch List Page

**Files:**
- Create: `src/frontend/src/domains/organization/components/BranchListPage.tsx`
- Create: `src/frontend/src/app/(dashboard)/organization/branches/page.tsx`

- [ ] **Step 1: Create BranchListPage component**

Create `src/frontend/src/domains/organization/components/BranchListPage.tsx`:

This is a substantial component. Structure:
- `'use client'` 
- Import hooks and UI components
- Table header with "Thêm chi nhánh" button
- Data from `useBranches()`
- Render rows with status badge + actions
- Create/Edit dialog with react-hook-form + zod
- Toggle status with confirmation dialog

```tsx
'use client';

import { useState } from 'react';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { toast } from 'sonner';
import { useBranches, useCreateBranch, useUpdateBranch, useToggleBranchStatus } from '@/domains/organization/hooks/useBranches';
import type { Branch } from '@/domains/organization/models/branch';
import { Table, TableHeader, TableBody, TableRow, TableHead, TableCell } from '@/shared/components/ui/table';
import { Dialog, DialogTrigger, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter, DialogClose } from '@/shared/components/ui/dialog';
import { Button } from '@/shared/components/ui/button';
import { Input } from '@/shared/components/ui/input';
import { Label } from '@/shared/components/ui/label';
import { Badge } from '@/shared/components/ui/badge';

const branchSchema = z.object({
  code: z.string().min(2, 'Mã tối thiểu 2 ký tự').regex(/^[A-Za-z][A-Za-z0-9-]+$/, 'Chỉ chấp nhận chữ, số, dấu gạch ngang'),
  name: z.string().min(1, 'Tên không được để trống'),
  address: z.string().optional(),
  phone: z.string().optional(),
  email: z.string().email('Email không hợp lệ').optional().or(z.literal('')),
});

type BranchFormData = z.infer<typeof branchSchema>;

export function BranchListPage() {
  const { data, isLoading, error } = useBranches();
  const createBranch = useCreateBranch();
  const updateBranch = useUpdateBranch();
  const toggleStatus = useToggleBranchStatus();
  const [dialogOpen, setDialogOpen] = useState(false);
  const [editingBranch, setEditingBranch] = useState<Branch | null>(null);
  const [confirmAction, setConfirmAction] = useState<{ id: string; action: 'activate' | 'deactivate'; name: string } | null>(null);

  const form = useForm<BranchFormData>({
    resolver: zodResolver(branchSchema),
    defaultValues: { code: '', name: '', address: '', phone: '', email: '' },
  });

  function openCreate() {
    setEditingBranch(null);
    form.reset({ code: '', name: '', address: '', phone: '', email: '' });
    setDialogOpen(true);
  }

  function openEdit(branch: Branch) {
    setEditingBranch(branch);
    form.reset({
      code: branch.code,
      name: branch.name,
      address: branch.address ?? '',
      phone: branch.phone ?? '',
      email: branch.email ?? '',
    });
    setDialogOpen(true);
  }

  async function onSubmit(data: BranchFormData) {
    try {
      if (editingBranch) {
        await updateBranch.mutateAsync({ id: editingBranch.id, payload: { name: data.name, address: data.address || undefined, phone: data.phone || undefined, email: data.email || undefined } });
        toast.success('Cập nhật chi nhánh thành công');
      } else {
        await createBranch.mutateAsync(data);
        toast.success('Tạo chi nhánh thành công');
      }
      setDialogOpen(false);
    } catch (err: any) {
      const msg = err?.response?.data?.message ?? 'Có lỗi xảy ra';
      toast.error(msg);
    }
  }

  async function handleToggle(id: string, action: 'activate' | 'deactivate') {
    try {
      await toggleStatus.mutateAsync({ id, action });
      toast.success(action === 'activate' ? 'Kích hoạt thành công' : 'Vô hiệu hóa thành công');
      setConfirmAction(null);
    } catch { toast.error('Thao tác thất bại'); }
  }

  if (isLoading) return <div className="flex items-center justify-center py-12"><p className="text-muted-foreground">Đang tải...</p></div>;
  if (error) return <div className="py-12 text-center text-destructive">Không thể tải danh sách chi nhánh.</div>;

  const branches = data?.data ?? [];

  return (
    <div className="space-y-4">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-semibold">Chi nhánh</h1>
          <p className="text-sm text-muted-foreground">Quản lý danh sách chi nhánh công ty</p>
        </div>
        <Dialog open={dialogOpen} onOpenChange={setDialogOpen}>
          <DialogTrigger asChild>
            <Button onClick={openCreate}>+ Thêm chi nhánh</Button>
          </DialogTrigger>
          <DialogContent>
            <DialogHeader>
              <DialogTitle>{editingBranch ? 'Sửa chi nhánh' : 'Thêm chi nhánh'}</DialogTitle>
              <DialogDescription>{editingBranch ? 'Cập nhật thông tin chi nhánh' : 'Nhập thông tin chi nhánh mới'}</DialogDescription>
            </DialogHeader>
            <form onSubmit={form.handleSubmit(onSubmit)} className="space-y-4">
              <div className="space-y-2">
                <Label htmlFor="code">Mã chi nhánh</Label>
                <Input id="code" {...form.register('code')} disabled={!!editingBranch} />
                {form.formState.errors.code && <p className="text-xs text-destructive">{form.formState.errors.code.message}</p>}
              </div>
              <div className="space-y-2">
                <Label htmlFor="name">Tên chi nhánh</Label>
                <Input id="name" {...form.register('name')} />
                {form.formState.errors.name && <p className="text-xs text-destructive">{form.formState.errors.name.message}</p>}
              </div>
              <div className="space-y-2">
                <Label htmlFor="address">Địa chỉ</Label>
                <Input id="address" {...form.register('address')} />
              </div>
              <div className="grid grid-cols-2 gap-4">
                <div className="space-y-2">
                  <Label htmlFor="phone">Số điện thoại</Label>
                  <Input id="phone" {...form.register('phone')} />
                </div>
                <div className="space-y-2">
                  <Label htmlFor="email">Email</Label>
                  <Input id="email" type="email" {...form.register('email')} />
                  {form.formState.errors.email && <p className="text-xs text-destructive">{form.formState.errors.email.message}</p>}
                </div>
              </div>
              <DialogFooter>
                <DialogClose asChild><Button variant="ghost" type="button">Hủy</Button></DialogClose>
                <Button type="submit" disabled={createBranch.isPending || updateBranch.isPending}>
                  {editingBranch ? 'Cập nhật' : 'Tạo'}
                </Button>
              </DialogFooter>
            </form>
          </DialogContent>
        </Dialog>
      </div>

      <Table>
        <TableHeader>
          <TableRow>
            <TableHead className="w-12">#</TableHead>
            <TableHead>Mã</TableHead>
            <TableHead>Tên</TableHead>
            <TableHead>Địa chỉ</TableHead>
            <TableHead>Trạng thái</TableHead>
            <TableHead className="text-right">Thao tác</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          {branches.map((branch, index) => (
            <TableRow key={branch.id}>
              <TableCell>{index + 1}</TableCell>
              <TableCell className="font-mono text-xs">{branch.code}</TableCell>
              <TableCell>{branch.name}</TableCell>
              <TableCell className="text-muted-foreground">{branch.address ?? '—'}</TableCell>
              <TableCell>
                <Badge variant={branch.status === 'active' ? 'default' : 'secondary'}>
                  {branch.status === 'active' ? 'Hoạt động' : 'Ngừng'}
                </Badge>
              </TableCell>
              <TableCell className="text-right">
                <div className="flex justify-end gap-1">
                  <Button variant="ghost" size="sm" onClick={() => openEdit(branch)}>Sửa</Button>
                  <Button variant="ghost" size="sm" onClick={() => setConfirmAction({ id: branch.id, action: branch.status === 'active' ? 'deactivate' : 'activate', name: branch.name })}>
                    {branch.status === 'active' ? 'Vô hiệu' : 'Kích hoạt'}
                  </Button>
                </div>
              </TableCell>
            </TableRow>
          ))}
          {branches.length === 0 ? (
            <TableRow><TableCell colSpan={6} className="text-center text-muted-foreground py-8">Chưa có chi nhánh nào</TableCell></TableRow>
          ) : null}
        </TableBody>
      </Table>

      {confirmAction ? (
        <Dialog open={!!confirmAction} onOpenChange={() => setConfirmAction(null)}>
          <DialogContent>
            <DialogHeader>
              <DialogTitle>Xác nhận</DialogTitle>
              <DialogDescription>
                {confirmAction.action === 'activate' ? `Kích hoạt chi nhánh "${confirmAction.name}"?` : `Vô hiệu hóa chi nhánh "${confirmAction.name}"?`}
              </DialogDescription>
            </DialogHeader>
            <DialogFooter>
              <Button variant="ghost" onClick={() => setConfirmAction(null)}>Hủy</Button>
              <Button
                variant={confirmAction.action === 'deactivate' ? 'destructive' : 'primary'}
                onClick={() => handleToggle(confirmAction.id, confirmAction.action)}
                disabled={toggleStatus.isPending}
              >
                Xác nhận
              </Button>
            </DialogFooter>
          </DialogContent>
        </Dialog>
      ) : null}
    </div>
  );
}
```

- [ ] **Step 2: Create route page**

Create `src/frontend/src/app/(dashboard)/organization/branches/page.tsx`:
```tsx
import { BranchListPage } from '@/domains/organization/components/BranchListPage';

export default function BranchesRoute() {
  return <BranchListPage />;
}
```

- [ ] **Step 3: Commit**

```bash
git add src/frontend/src/domains/organization/components/BranchListPage.tsx 'src/frontend/src/app/(dashboard)/organization'
git commit -m "feat(frontend): add branch list page"
```

---

### Task 5: Department List Page

**Files:**
- Create: `src/frontend/src/domains/organization/components/DepartmentListPage.tsx`
- Assumes `src/frontend/src/app/(dashboard)/organization/departments/page.tsx`

Follow the same pattern as BranchListPage but adds:
- Branch select field (loads from branches list)
- Parent department select (filtered by chosen branch)
- Move action
- Table shows Branch name, Parent Dept name

The page component will be created inline here but note it's a larger file. Key additions:
- Fetch branches for the select dropdown via `useBranches()`
- Fetch departments for the parent select
- Move dialog reuses same form with branch_id + parent_id fields
- Submit calls `moveDepartments` mutation when moving

- [ ] **Step 1: Create DepartmentListPage**

Create the department list page component. Structure mirrors BranchListPage with additions for branch/parent selects and move functionality.

```tsx
'use client';
// Similar imports to BranchListPage + useBranches hook
// schema adds: branch_id, parent_id
// form adds: Select for branch, Select for parent (filtered by branch)
// "Di chuyển" button in actions opens same dialog with move mode
```

Route page: `src/frontend/src/app/(dashboard)/organization/departments/page.tsx`:
```tsx
import { DepartmentListPage } from '@/domains/organization/components/DepartmentListPage';
export default function DepartmentsRoute() { return <DepartmentListPage />; }
```

- [ ] **Step 2: Commit**

```bash
git add src/frontend/src/domains/organization/components/DepartmentListPage.tsx 'src/frontend/src/app/(dashboard)/organization/departments'
git commit -m "feat(frontend): add department list page"
```

---

### Task 6: Position List Page

**Files:**
- Create: `src/frontend/src/domains/organization/components/PositionListPage.tsx`
- Create: `src/frontend/src/app/(dashboard)/organization/positions/page.tsx`

Pattern identical to BranchListPage but schema has: `code`, `name`, `level` (number 1-99), `description` (textarea, optional).

Lighter than BranchListPage — no branching/parent logic.

- [ ] **Step 1: Create PositionListPage**

```tsx
'use client';
// Similar to BranchListPage but:
// schema: code (required, regex), name (required), level (optional number 1-99), description (optional)
// table columns: #, Code, Name, Level, Description (truncated), Status, Actions
```

Route page:
```tsx
import { PositionListPage } from '@/domains/organization/components/PositionListPage';
export default function PositionsRoute() { return <PositionListPage />; }
```

- [ ] **Step 2: Commit**

```bash
git add src/frontend/src/domains/organization/components/PositionListPage.tsx 'src/frontend/src/app/(dashboard)/organization/positions'
git commit -m "feat(frontend): add position list page"
```

---

### Task 7: Org Tree Page

**Files:**
- Create: `src/frontend/src/domains/organization/components/OrgTreePage.tsx`
- Create: `src/frontend/src/app/(dashboard)/organization/tree/page.tsx`

- [ ] **Step 1: Create OrgTreePage**

```tsx
'use client';

import { useQuery } from '@tanstack/react-query';
import { http } from '@/core/http/client';
import { ChevronRight, ChevronDown, Building2, FolderTree } from 'lucide-react';
import { useState } from 'react';

interface OrgBranch {
  id: string; code: string; name: string;
  departments: OrgDepartment[];
}

interface OrgDepartment {
  id: string; code: string; name: string; branch_id: string; parent_id: string | null;
  children: OrgDepartment[];
}

function DepartmentNode({ dept, depth }: { dept: OrgDepartment; depth: number }) {
  const [expanded, setExpanded] = useState(true);
  const hasChildren = dept.children && dept.children.length > 0;

  return (
    <li>
      <div
        className="flex items-center gap-1 py-1 hover:bg-muted/50 rounded px-2 cursor-pointer"
        style={{ paddingLeft: `${depth * 20 + 8}px` }}
        onClick={() => hasChildren && setExpanded(!expanded)}
      >
        {hasChildren ? (
          expanded ? <ChevronDown className="h-4 w-4 text-muted-foreground" /> : <ChevronRight className="h-4 w-4 text-muted-foreground" />
        ) : (
          <span className="w-4" />
        )}
        <span className="text-sm">{dept.name}</span>
        <span className="text-xs text-muted-foreground font-mono">{dept.code}</span>
      </div>
      {hasChildren && expanded && (
        <ul>{dept.children.map(child => <DepartmentNode key={child.id} dept={child} depth={depth + 1} />)}</ul>
      )}
    </li>
  );
}

export function OrgTreePage() {
  const { data, isLoading, error } = useQuery({
    queryKey: ['org-tree'],
    queryFn: async () => {
      const res = await http.get<{ data: OrgBranch[] }>('/org-tree');
      return res.data.data;
    },
  });

  if (isLoading) return <div className="py-12 text-center text-muted-foreground">Đang tải sơ đồ tổ chức...</div>;
  if (error) return <div className="py-12 text-center text-destructive">Không thể tải sơ đồ tổ chức.</div>;

  const branches = data ?? [];

  return (
    <div className="space-y-4">
      <div>
        <h1 className="text-2xl font-semibold">Sơ đồ tổ chức</h1>
        <p className="text-sm text-muted-foreground">Cấu trúc cây công ty, chi nhánh và phòng ban</p>
      </div>
      <div className="rounded-lg border bg-white p-6">
        {branches.length === 0 ? (
          <p className="text-center text-muted-foreground py-8">Chưa có dữ liệu tổ chức</p>
        ) : (
          <ul className="space-y-4">
            {branches.map(branch => (
              <li key={branch.id}>
                <div className="flex items-center gap-2 py-2 font-medium">
                  <Building2 className="h-5 w-5 text-primary" />
                  <span>{branch.name}</span>
                  <span className="text-xs text-muted-foreground font-mono">{branch.code}</span>
                </div>
                {branch.departments.length > 0 ? (
                  <ul>
                    {branch.departments.map(dept => (
                      <DepartmentNode key={dept.id} dept={dept} depth={1} />
                    ))}
                  </ul>
                ) : (
                  <p className="text-sm text-muted-foreground pl-8">Chưa có phòng ban</p>
                )}
              </li>
            ))}
          </ul>
        )}
      </div>
    </div>
  );
}
```

Route page:
```tsx
import { OrgTreePage } from '@/domains/organization/components/OrgTreePage';
export default function TreeRoute() { return <OrgTreePage />; }
```

- [ ] **Step 2: Commit**

```bash
git add src/frontend/src/domains/organization/components/OrgTreePage.tsx 'src/frontend/src/app/(dashboard)/organization/tree'
git commit -m "feat(frontend): add org tree page"
```

---

### Task 8: Sidebar Navigation and Verification

**Files:**
- Modify: `src/frontend/src/shared/components/AppSidebar.tsx`

- [ ] **Step 1: Add Organization nav section**

Modify AppSidebar to include an Organization section:

```tsx
// In the nav section, replace the single Dashboard link with:
<nav className="flex-1 space-y-1 p-4">
  <Link className="block rounded-md px-3 py-2 text-sm hover:bg-muted" href="/dashboard">
    Dashboard
  </Link>
  <p className="px-3 pt-4 text-xs font-semibold uppercase tracking-wider text-muted-foreground">
    Tổ chức
  </p>
  <Link className="block rounded-md px-3 py-2 text-sm hover:bg-muted" href="/organization/branches">
    Chi nhánh
  </Link>
  <Link className="block rounded-md px-3 py-2 text-sm hover:bg-muted" href="/organization/departments">
    Phòng ban
  </Link>
  <Link className="block rounded-md px-3 py-2 text-sm hover:bg-muted" href="/organization/positions">
    Chức vụ
  </Link>
  <Link className="block rounded-md px-3 py-2 text-sm hover:bg-muted" href="/organization/tree">
    Sơ đồ tổ chức
  </Link>
</nav>
```

- [ ] **Step 2: Lint and build**

```bash
cd src/frontend
npm run lint && npm run build
```

Expected: both pass.

- [ ] **Step 3: Commit**

```bash
git add src/frontend/src/shared/components/AppSidebar.tsx
git commit -m "feat(frontend): add organization sidebar navigation"
```

---

## Self-Review

- Spec coverage: AC1-AC10 covered by Tasks 1-8.
- React Query used for all data fetching.
- react-hook-form + zod for all forms.
- No multi-step forms (deferred).
- No employee/contract screens (next Phase 2 module).
