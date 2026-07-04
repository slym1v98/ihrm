# Frontend Admin Phase 2 — Organization Module Design

Date: 2026-07-04
Scope: Organization module only (Branch, Department, Position CRUD + Org Tree).
Status: Draft for user review.

## Goal

Build the Organization admin UI: manage branches, departments (with nesting and moves), positions, and render the org chart tree.

## Dependencies To Add

- `@tanstack/react-query` — server state: caching query results, auto-refetch, mutations
- `react-hook-form` + `zod` + `@hookform/resolvers` — form validation
- Shadcn components: Table, Dialog, Select, Badge, Tabs, useToast (or Sonner)

## Domain Structure

```
src/domains/organization/
├── models/
│   ├── branch.ts           — Branch model + create/update payloads
│   ├── department.ts       — Department model with parent ref
│   └── position.ts         — Position model + level
├── services/
│   ├── branchService.ts    — Axios calls for /branches/*
│   ├── departmentService.ts— Axios calls for /departments/*
│   └── positionService.ts  — Axios calls for /positions/*
├── hooks/
│   ├── useBranches.ts      — React Query hooks (list, create, update, toggle)
│   ├── useDepartments.ts   — React Query hooks (list, create, update, move, toggle)
│   └── usePositions.ts     — React Query hooks (list, create, update, toggle)
└── components/
    ├── BranchListPage.tsx   — page component rendered at route
    ├── DepartmentListPage.tsx
    ├── PositionListPage.tsx
    └── OrgTreePage.tsx
```

## Pages

All pages share a common layout shell.

### Branch List (`/organization/branches`)

Table with columns: `#`, `Code`, `Name`, `Address`, `Status` (Badge), `Actions` (Edit, Activate/Deactivate).

Create/edit dialog: fields `code`, `name`, `address`, `phone` (optional), `email` (optional). Zod schema: code min 2 uppercase, name required. Server-side validation errors from API shown inline.

### Department List (`/organization/departments`)

Table with columns: `#`, `Code`, `Name`, `Branch`, `Parent Dept`, `Manager`, `Status`, Actions.

Create/edit dialog: fields `code`, `name`, `branch_id` (select), `parent_id` (select, filtered by chosen branch). Move action opens same dialog with branch+parent fields.

### Position List (`/organization/positions`)

Table with columns: `#`, `Code`, `Name`, `Level`, `Description`, `Status`, Actions.

Create/edit dialog: fields `code`, `name`, `level` (number 1-99), `description` (textarea, optional).

### Org Tree (`/organization/tree`)

Single page fetching GET `/org-tree`. Renders a nested tree:

```
Branch 1
├── Department A
│   ├── Sub-department A-1
│   └── Sub-department A-2
└── Department B
Branch 2
├── Department C
...
```

Implementation: recursive `<ul><li>` styled with indentation and folder icons (Lucide). Read-only view.

## Shared UI Pattern

- Table uses Shadcn `<Table>` component.
- Dialogs use Shadcn `<Dialog>`.
- Form uses `react-hook-form` with `zodResolver`.
- Submit mutation triggers `useQueryClient.invalidateQueries` to refresh list.
- Toast for success/failure via Shadcn sonner.
- Status toggle uses a confirmation dialog before sending activate/deactivate API call.

## Backend Endpoints Referenced

All from `src/backend/app/Modules/Organization/Routes/api.php`:
- Branches: index, store, show, update, activate, deactivate
- Departments: index, store, show, update, move, activate, deactivate
- Positions: index, store, show, update, activate, deactivate
- Org Tree: `GET /v1/org-tree`

## Acceptance Criteria

AC1. Branches page lists, creates, edits, and toggles status.
AC2. Departments page lists with parent branch, creates/edits with branch+parent selects, moves between branches/parents.
AC3. Positions page lists, creates, edits with level validation.
AC4. Org Tree page renders the full nested tree structure.
AC5. All data-fetching pages use React Query with proper query keys and stale times.
AC6. Mutations invalidate the correct query keys after success.
AC7. Status toggles show confirmation before submitting.
AC8. Form errors from the backend (422) display inline on the correct field.
AC9. Navigation items for Organization appear in the sidebar.
AC10. `npm run lint` and `npm run build` pass before completion.
