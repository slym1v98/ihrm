# Frontend Admin Phase 1 Design

Date: 2026-07-04
Scope: iHRM Admin Portal frontend foundation only.
Status: Approved for planning after user review.

## Goal

Build the minimum frontend foundation required before admin modules are added:

1. shadcn/ui + Tailwind design baseline.
2. HTTP client with access token attachment and automatic refresh-token retry.
3. Simple login screen.
4. Protected dashboard routes.
5. PermissionGuard for permission-based UI visibility.

Out of scope: Organization, Employee, Contract, Attendance, Payroll, Recruitment, dashboards, CRUD screens, SSO, forgot password.

## Current Context

`src/frontend` already contains a small NextJS 14 app with Tailwind and TypeScript. It has:

- `src/app/layout.tsx`
- `src/app/page.tsx`
- `src/app/globals.css`
- `src/lib/api-client.ts`
- `src/lib/auth-context.tsx`

The current API client uses `fetch` and only supports a manually set access token. It has no refresh-token retry, no route protection, no shadcn/ui setup, and no permission guard.

## Architecture

Use the roadmap's domain-first structure, but only create folders needed for Phase 1.

```text
src/frontend/src/
├── app/
│   ├── (auth)/
│   │   └── login/page.tsx
│   ├── (dashboard)/
│   │   ├── dashboard/page.tsx
│   │   └── layout.tsx
│   └── layout.tsx
├── core/
│   ├── config/index.ts
│   └── http/client.ts
├── domains/
│   └── auth/
│       ├── components/LoginForm.tsx
│       ├── hooks/useAuth.tsx
│       ├── models/auth.ts
│       └── services/authService.ts
└── shared/
    ├── components/PermissionGuard.tsx
    ├── components/app-sidebar.tsx
    └── components/ui/
```

`src/app` stays routing-only. Domain logic lives under `domains/auth`. Shared non-business UI lives under `shared/components`.

## Auth And HTTP Flow

Use Axios instead of the current fetch wrapper because Phase 1 explicitly needs request/response interceptors.

**Backend reality (Sanctum, single access token):**

- `POST /auth/login` returns `{ data: { access_token, token_type, user } }`.
- `POST /auth/logout` revokes the current token.
- `GET /auth/me` returns the current user.
- There is **no** `/auth/refresh` endpoint.

**Request flow:**

1. Client calls a domain service.
2. Axios request interceptor adds `Authorization: Bearer <access_token>` when available.
3. If a response returns `401`, the response interceptor clears auth state and redirects to `/login`.
4. All other errors bubble up to the caller.

**Token persistence:**

- Access token is stored in a cookie (`ihrm_at`) so the NextJS middleware can gate protected routes without hydrating React state.
- On app boot, the auth hook reads the cookie, sets it into the Axios client, and calls `GET /auth/me` to hydrate user state. If `/auth/me` returns 401, clear the cookie and treat the user as logged out.
- The cookie is set client-side after login and cleared on logout or 401. It is `SameSite=Lax`, `Secure` in production, and **not** HTTP-only because the middleware only needs presence-check and the frontend sets it after login.

> Automatic refresh-token retry is deferred: the backend does not expose a refresh endpoint. When the backend adds one, the response interceptor gains the retry-once-with-queue behavior. This is called out here so a future task can add it without changing the surrounding architecture.

## Login Screen

Simple login only:

- Centered card.
- Title: `Đăng nhập iHRM`.
- Email input.
- Password input.
- Submit button with loading state.
- Error message or toast on failure.
- Successful login redirects to `/dashboard`.

Do not add remember-me, forgot-password, SSO, marketing panels, or multi-layout variants in Phase 1.

## Protected Routes

Use `src/middleware.ts` for coarse protection:

- `/login` is public.
- Dashboard routes are protected.
- Missing auth cookie redirects to `/login`.

The middleware can only make a coarse cookie check. Real user and permission checks happen client-side after auth state loads.

## Dashboard Shell

Implement a minimal sidebar dashboard shell:

- Left sidebar.
- Top header with user display and logout.
- Main content area.
- `/dashboard` placeholder page.

Only include navigation items needed for Phase 1, such as Dashboard and Logout. Module navigation is deferred to later phases.

## PermissionGuard

Create `PermissionGuard` with this behavior:

- Props: `allowedPermissions: string[]`, `children`, optional `fallback`.
- Reads current user permission codes from auth context.
- Renders `children` if the user has at least one allowed permission.
- Renders `fallback` or `null` otherwise.

**Permission source:** Backend `/auth/me` currently returns `roles: [{ id, code, name }]` but not permission codes. Phase 1 loads permissions once after login via `GET /roles/{id}` for each user role and merges the returned `permissions` arrays into a flat set. Cached in auth context. Failures fall back to empty permissions (guard hides everything).

No route-level permission matrix in Phase 1. Add it when module pages exist.

## Dependencies

Add only dependencies needed for this phase:

- `axios`
- shadcn/ui required packages generated by `shadcn` for selected components
- `lucide-react` if required by shadcn/sidebar/icons

Avoid TanStack Query, Zustand, zod, react-hook-form until module forms and server-state screens need them.

## Testing And Verification

Minimum verification for Phase 1:

1. `npm run lint` in `src/frontend`.
2. `npm run build` in `src/frontend`.
3. Manual flow check:
   - `/login` renders.
   - Failed login shows error.
   - Successful login redirects to `/dashboard`.
   - Protected route redirects when unauthenticated.
   - `PermissionGuard` hides unauthorized content.

Full backend test suite is not required for frontend-only Phase 1 unless backend files change.

## Acceptance Criteria

AC1. shadcn/ui base components are available under `src/shared/components/ui` or configured alias equivalent.

AC2. Login page exists at `/login` and uses a simple card form.

AC3. Auth service calls backend `POST /auth/login`, `POST /auth/logout`, and `GET /auth/me`.

AC4. Axios client attaches access token from the client-managed cookie.

AC5. 401 responses clear auth state and redirect to `/login`.

AC6. Permission set is loaded from role endpoints once and cached in auth context.

AC7. Dashboard route is protected and has a minimal sidebar shell.

AC8. `PermissionGuard` supports permission-based rendering.

AC9. No module CRUD screens are implemented in Phase 1.

AC10. `npm run lint` and `npm run build` pass before completion.
