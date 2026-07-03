# Frontend Admin Phase 1 Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build the iHRM admin frontend foundation: shadcn-ready UI, auth, protected dashboard shell, and permission guard.

**Architecture:** Keep `src/app` routing-only. Move cross-cutting HTTP/config/auth plumbing into `src/core` and domain auth code into `src/domains/auth`. Use the existing Laravel Sanctum token API exactly as implemented: login/logout/me, no refresh endpoint.

**Tech Stack:** NextJS 14 App Router, TypeScript strict, Tailwind CSS, Axios, minimal local UI components styled like shadcn/ui.

---

## File Map

- Modify: `src/frontend/package.json` — add `axios`, update scripts if needed.
- Modify: `src/frontend/src/app/layout.tsx` — wrap app with `AuthProvider`.
- Modify: `src/frontend/src/app/page.tsx` — redirect root to `/dashboard`.
- Modify: `src/frontend/src/app/globals.css` — add CSS variables and base theme.
- Modify: `src/frontend/tailwind.config.ts` — add theme tokens.
- Delete/replace: `src/frontend/src/lib/api-client.ts` — superseded by `src/core/http/client.ts`.
- Delete/replace: `src/frontend/src/lib/auth-context.tsx` — superseded by domain auth provider.
- Create: `src/frontend/src/core/config/index.ts` — API base URL and cookie names.
- Create: `src/frontend/src/core/http/client.ts` — Axios instance, token setter, 401 redirect.
- Create: `src/frontend/src/core/utils/cn.ts` — className join helper.
- Create: `src/frontend/src/domains/auth/models/auth.ts` — auth/user/role types.
- Create: `src/frontend/src/domains/auth/services/authService.ts` — login/logout/me/role service.
- Create: `src/frontend/src/domains/auth/hooks/useAuth.tsx` — provider + hook + cookie/token lifecycle.
- Create: `src/frontend/src/domains/auth/components/LoginForm.tsx` — simple login card.
- Create: `src/frontend/src/shared/components/ui/button.tsx` — minimal shadcn-style button.
- Create: `src/frontend/src/shared/components/ui/card.tsx` — minimal card.
- Create: `src/frontend/src/shared/components/ui/input.tsx` — minimal input.
- Create: `src/frontend/src/shared/components/PermissionGuard.tsx` — permission rendering.
- Create: `src/frontend/src/shared/components/AppSidebar.tsx` — minimal sidebar.
- Create: `src/frontend/src/app/(auth)/login/page.tsx` — login route.
- Create: `src/frontend/src/app/(dashboard)/layout.tsx` — protected shell.
- Create: `src/frontend/src/app/(dashboard)/dashboard/page.tsx` — dashboard placeholder.
- Create: `src/frontend/src/middleware.ts` — cookie presence gate.

---

### Task 1: Dependencies And Theme

**Files:**
- Modify: `src/frontend/package.json`
- Modify: `src/frontend/tailwind.config.ts`
- Modify: `src/frontend/src/app/globals.css`
- Create: `src/frontend/src/core/utils/cn.ts`
- Create: `src/frontend/src/shared/components/ui/button.tsx`
- Create: `src/frontend/src/shared/components/ui/card.tsx`
- Create: `src/frontend/src/shared/components/ui/input.tsx`

- [ ] **Step 1: Install only needed dependency**

Run:

```bash
cd src/frontend
npm install axios
```

Expected: `package.json` and `package-lock.json` include `axios`.

- [ ] **Step 2: Add className helper**

Create `src/frontend/src/core/utils/cn.ts`:

```ts
export function cn(...classes: Array<string | false | null | undefined>) {
  return classes.filter(Boolean).join(' ');
}
```

- [ ] **Step 3: Add theme tokens**

Replace `src/frontend/tailwind.config.ts` with:

```ts
import type { Config } from 'tailwindcss';

const config: Config = {
  content: ['./src/**/*.{js,ts,jsx,tsx,mdx}'],
  theme: {
    extend: {
      colors: {
        border: 'hsl(var(--border))',
        background: 'hsl(var(--background))',
        foreground: 'hsl(var(--foreground))',
        primary: 'hsl(var(--primary))',
        'primary-foreground': 'hsl(var(--primary-foreground))',
        muted: 'hsl(var(--muted))',
        'muted-foreground': 'hsl(var(--muted-foreground))',
        destructive: 'hsl(var(--destructive))',
      },
      borderRadius: { lg: '0.5rem', md: '0.375rem', sm: '0.25rem' },
    },
  },
  plugins: [],
};

export default config;
```

- [ ] **Step 4: Add base CSS**

Replace `src/frontend/src/app/globals.css` with:

```css
@tailwind base;
@tailwind components;
@tailwind utilities;

:root {
  --background: 0 0% 100%;
  --foreground: 222.2 84% 4.9%;
  --primary: 221.2 83.2% 53.3%;
  --primary-foreground: 210 40% 98%;
  --muted: 210 40% 96.1%;
  --muted-foreground: 215.4 16.3% 46.9%;
  --destructive: 0 84.2% 60.2%;
  --border: 214.3 31.8% 91.4%;
}

* { border-color: hsl(var(--border)); }
body { background: hsl(var(--background)); color: hsl(var(--foreground)); }
```

- [ ] **Step 5: Add minimal UI primitives**

Create `src/frontend/src/shared/components/ui/button.tsx`:

```tsx
import * as React from 'react';
import { cn } from '@/core/utils/cn';

type ButtonProps = React.ButtonHTMLAttributes<HTMLButtonElement> & {
  variant?: 'primary' | 'ghost' | 'destructive';
};

export function Button({ className, variant = 'primary', ...props }: ButtonProps) {
  const variants = {
    primary: 'bg-primary text-primary-foreground hover:bg-primary/90',
    ghost: 'hover:bg-muted',
    destructive: 'bg-destructive text-white hover:bg-destructive/90',
  };

  return (
    <button
      className={cn(
        'inline-flex h-10 items-center justify-center rounded-md px-4 py-2 text-sm font-medium transition-colors disabled:pointer-events-none disabled:opacity-50',
        variants[variant],
        className,
      )}
      {...props}
    />
  );
}
```

Create `src/frontend/src/shared/components/ui/card.tsx`:

```tsx
import * as React from 'react';
import { cn } from '@/core/utils/cn';

export function Card({ className, ...props }: React.HTMLAttributes<HTMLDivElement>) {
  return <div className={cn('rounded-lg border bg-white shadow-sm', className)} {...props} />;
}
```

Create `src/frontend/src/shared/components/ui/input.tsx`:

```tsx
import * as React from 'react';
import { cn } from '@/core/utils/cn';

export function Input({ className, ...props }: React.InputHTMLAttributes<HTMLInputElement>) {
  return (
    <input
      className={cn(
        'h-10 w-full rounded-md border bg-white px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-primary disabled:cursor-not-allowed disabled:opacity-50',
        className,
      )}
      {...props}
    />
  );
}
```

- [ ] **Step 6: Commit**

```bash
git add src/frontend/package.json src/frontend/package-lock.json src/frontend/tailwind.config.ts src/frontend/src/app/globals.css src/frontend/src/core src/frontend/src/shared/components/ui
git commit -m "feat(frontend): add phase 1 UI foundation"
```

---

### Task 2: HTTP And Auth Domain

**Files:**
- Create: `src/frontend/src/core/config/index.ts`
- Create: `src/frontend/src/core/http/client.ts`
- Create: `src/frontend/src/domains/auth/models/auth.ts`
- Create: `src/frontend/src/domains/auth/services/authService.ts`
- Create: `src/frontend/src/domains/auth/hooks/useAuth.tsx`
- Modify: `src/frontend/src/app/layout.tsx`

- [ ] **Step 1: Add config**

Create `src/frontend/src/core/config/index.ts`:

```ts
export const config = {
  apiBaseUrl: process.env.NEXT_PUBLIC_API_URL ?? 'http://localhost:8000/api/v1',
  accessTokenCookie: 'ihrm_at',
};
```

- [ ] **Step 2: Add auth models**

Create `src/frontend/src/domains/auth/models/auth.ts`:

```ts
export interface RoleSummary {
  id: string;
  code: string;
  name: string;
}

export interface User {
  id: string;
  email: string;
  name: string;
  employee_id: string | null;
  status: string;
  last_login_at: string | null;
  roles: RoleSummary[];
}

export interface LoginResponse {
  access_token: string;
  token_type: string;
  user: User;
}

export interface RoleDetail extends RoleSummary {
  permissions: string[];
}

export interface ApiResponse<T> {
  data: T;
}
```

- [ ] **Step 3: Add HTTP client**

Create `src/frontend/src/core/http/client.ts`:

```ts
import axios from 'axios';
import { config } from '@/core/config';

let accessToken: string | null = null;

export const http = axios.create({
  baseURL: config.apiBaseUrl,
  headers: { Accept: 'application/json' },
});

export function setAccessToken(token: string | null) {
  accessToken = token;
}

http.interceptors.request.use((request) => {
  if (accessToken) request.headers.Authorization = `Bearer ${accessToken}`;
  return request;
});

http.interceptors.response.use(
  (response) => response,
  (error: unknown) => {
    if (axios.isAxiosError(error) && error.response?.status === 401 && typeof window !== 'undefined') {
      document.cookie = `${config.accessTokenCookie}=; path=/; max-age=0; SameSite=Lax`;
      setAccessToken(null);
      window.location.assign('/login');
    }

    return Promise.reject(error);
  },
);
```

- [ ] **Step 4: Add auth service**

Create `src/frontend/src/domains/auth/services/authService.ts`:

```ts
import { http } from '@/core/http/client';
import type { ApiResponse, LoginResponse, RoleDetail, User } from '@/domains/auth/models/auth';

export const authService = {
  async login(email: string, password: string) {
    const response = await http.post<ApiResponse<LoginResponse>>('/auth/login', { email, password });
    return response.data.data;
  },

  async me() {
    const response = await http.get<ApiResponse<User>>('/auth/me');
    return response.data.data;
  },

  async logout() {
    await http.post<ApiResponse<null>>('/auth/logout');
  },

  async role(id: string) {
    const response = await http.get<ApiResponse<RoleDetail>>(`/roles/${id}`);
    return response.data.data;
  },
};
```

- [ ] **Step 5: Add auth hook/provider**

Create `src/frontend/src/domains/auth/hooks/useAuth.tsx`:

```tsx
'use client';

import React, { createContext, useContext, useEffect, useMemo, useState } from 'react';
import { config } from '@/core/config';
import { setAccessToken } from '@/core/http/client';
import type { User } from '@/domains/auth/models/auth';
import { authService } from '@/domains/auth/services/authService';

interface AuthContextValue {
  user: User | null;
  permissions: string[];
  isLoading: boolean;
  login: (email: string, password: string) => Promise<void>;
  logout: () => Promise<void>;
  hasAnyPermission: (allowed: string[]) => boolean;
}

const AuthContext = createContext<AuthContextValue | null>(null);

function readCookie(name: string) {
  if (typeof document === 'undefined') return null;
  return document.cookie.split('; ').find((row) => row.startsWith(`${name}=`))?.split('=')[1] ?? null;
}

function writeTokenCookie(token: string) {
  const secure = window.location.protocol === 'https:' ? '; Secure' : '';
  document.cookie = `${config.accessTokenCookie}=${encodeURIComponent(token)}; path=/; SameSite=Lax${secure}`;
}

function clearTokenCookie() {
  document.cookie = `${config.accessTokenCookie}=; path=/; max-age=0; SameSite=Lax`;
}

async function loadPermissions(user: User) {
  const roleDetails = await Promise.allSettled(user.roles.map((role) => authService.role(role.id)));
  return Array.from(
    new Set(
      roleDetails.flatMap((result) => (result.status === 'fulfilled' ? result.value.permissions : [])),
    ),
  );
}

export function AuthProvider({ children }: { children: React.ReactNode }) {
  const [user, setUser] = useState<User | null>(null);
  const [permissions, setPermissions] = useState<string[]>([]);
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    const token = readCookie(config.accessTokenCookie);
    if (!token) {
      setIsLoading(false);
      return;
    }

    setAccessToken(decodeURIComponent(token));
    authService.me()
      .then(async (currentUser) => {
        setUser(currentUser);
        setPermissions(await loadPermissions(currentUser));
      })
      .catch(() => {
        clearTokenCookie();
        setAccessToken(null);
        setUser(null);
        setPermissions([]);
      })
      .finally(() => setIsLoading(false));
  }, []);

  const value = useMemo<AuthContextValue>(() => ({
    user,
    permissions,
    isLoading,
    async login(email, password) {
      const result = await authService.login(email, password);
      writeTokenCookie(result.access_token);
      setAccessToken(result.access_token);
      setUser(result.user);
      setPermissions(await loadPermissions(result.user));
    },
    async logout() {
      try { await authService.logout(); } finally {
        clearTokenCookie();
        setAccessToken(null);
        setUser(null);
        setPermissions([]);
      }
    },
    hasAnyPermission(allowed) {
      return allowed.some((permission) => permissions.includes(permission));
    },
  }), [permissions, user, isLoading]);

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

export function useAuth() {
  const context = useContext(AuthContext);
  if (!context) throw new Error('useAuth must be used inside AuthProvider');
  return context;
}
```

- [ ] **Step 6: Wrap root layout**

Modify `src/frontend/src/app/layout.tsx` so it contains:

```tsx
import type { Metadata } from 'next';
import './globals.css';
import { AuthProvider } from '@/domains/auth/hooks/useAuth';

export const metadata: Metadata = {
  title: 'iHRM Admin',
  description: 'iHRM Enterprise Admin Portal',
};

export default function RootLayout({ children }: { children: React.ReactNode }) {
  return (
    <html lang="vi">
      <body>
        <AuthProvider>{children}</AuthProvider>
      </body>
    </html>
  );
}
```

- [ ] **Step 7: Commit**

```bash
git add src/frontend/src/core src/frontend/src/domains/auth src/frontend/src/app/layout.tsx
git commit -m "feat(frontend): add auth client and provider"
```

---

### Task 3: Login Route

**Files:**
- Create: `src/frontend/src/domains/auth/components/LoginForm.tsx`
- Create: `src/frontend/src/app/(auth)/login/page.tsx`
- Modify: `src/frontend/src/app/page.tsx`

- [ ] **Step 1: Add login form**

Create `src/frontend/src/domains/auth/components/LoginForm.tsx`:

```tsx
'use client';

import { FormEvent, useState } from 'react';
import { useRouter } from 'next/navigation';
import { useAuth } from '@/domains/auth/hooks/useAuth';
import { Button } from '@/shared/components/ui/button';
import { Card } from '@/shared/components/ui/card';
import { Input } from '@/shared/components/ui/input';

export function LoginForm() {
  const router = useRouter();
  const { login } = useAuth();
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState<string | null>(null);
  const [isSubmitting, setIsSubmitting] = useState(false);

  async function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    setError(null);
    setIsSubmitting(true);

    try {
      await login(email, password);
      router.replace('/dashboard');
    } catch {
      setError('Email hoặc mật khẩu không đúng.');
    } finally {
      setIsSubmitting(false);
    }
  }

  return (
    <Card className="w-full max-w-md p-6">
      <div className="mb-6 space-y-2 text-center">
        <h1 className="text-2xl font-semibold">Đăng nhập iHRM</h1>
        <p className="text-sm text-muted-foreground">Truy cập cổng quản trị nhân sự</p>
      </div>
      <form className="space-y-4" onSubmit={handleSubmit}>
        <div className="space-y-2">
          <label className="text-sm font-medium" htmlFor="email">Email</label>
          <Input id="email" type="email" value={email} onChange={(event) => setEmail(event.target.value)} required />
        </div>
        <div className="space-y-2">
          <label className="text-sm font-medium" htmlFor="password">Mật khẩu</label>
          <Input id="password" type="password" value={password} onChange={(event) => setPassword(event.target.value)} required />
        </div>
        {error ? <p className="text-sm text-destructive">{error}</p> : null}
        <Button className="w-full" disabled={isSubmitting} type="submit">
          {isSubmitting ? 'Đang đăng nhập...' : 'Đăng nhập'}
        </Button>
      </form>
    </Card>
  );
}
```

- [ ] **Step 2: Add login page**

Create `src/frontend/src/app/(auth)/login/page.tsx`:

```tsx
import { LoginForm } from '@/domains/auth/components/LoginForm';

export default function LoginPage() {
  return (
    <main className="flex min-h-screen items-center justify-center bg-muted px-4">
      <LoginForm />
    </main>
  );
}
```

- [ ] **Step 3: Redirect root**

Replace `src/frontend/src/app/page.tsx` with:

```tsx
import { redirect } from 'next/navigation';

export default function HomePage() {
  redirect('/dashboard');
}
```

- [ ] **Step 4: Commit**

```bash
git add src/frontend/src/domains/auth/components/LoginForm.tsx src/frontend/src/app src/frontend/src/app/page.tsx
git commit -m "feat(frontend): add admin login page"
```

---

### Task 4: Protected Dashboard Shell

**Files:**
- Create: `src/frontend/src/middleware.ts`
- Create: `src/frontend/src/shared/components/AppSidebar.tsx`
- Create: `src/frontend/src/app/(dashboard)/layout.tsx`
- Create: `src/frontend/src/app/(dashboard)/dashboard/page.tsx`

- [ ] **Step 1: Add middleware**

Create `src/frontend/src/middleware.ts`:

```ts
import { NextResponse, type NextRequest } from 'next/server';
import { config } from '@/core/config';

export function middleware(request: NextRequest) {
  const token = request.cookies.get(config.accessTokenCookie)?.value;
  const isLogin = request.nextUrl.pathname === '/login';

  if (!token && !isLogin) {
    return NextResponse.redirect(new URL('/login', request.url));
  }

  if (token && isLogin) {
    return NextResponse.redirect(new URL('/dashboard', request.url));
  }

  return NextResponse.next();
}

export const config = {
  matcher: ['/dashboard/:path*', '/login'],
};
```

If TypeScript reports a name conflict with imported `config`, rename the import to `appConfig` and use `appConfig.accessTokenCookie`.

- [ ] **Step 2: Add sidebar**

Create `src/frontend/src/shared/components/AppSidebar.tsx`:

```tsx
'use client';

import Link from 'next/link';
import { useRouter } from 'next/navigation';
import { useAuth } from '@/domains/auth/hooks/useAuth';
import { Button } from '@/shared/components/ui/button';

export function AppSidebar() {
  const router = useRouter();
  const { user, logout } = useAuth();

  async function handleLogout() {
    await logout();
    router.replace('/login');
  }

  return (
    <aside className="flex min-h-screen w-64 flex-col border-r bg-white">
      <div className="border-b p-4">
        <p className="text-lg font-semibold">iHRM Admin</p>
        <p className="text-sm text-muted-foreground">{user?.name ?? 'Đang tải...'}</p>
      </div>
      <nav className="flex-1 p-4">
        <Link className="block rounded-md px-3 py-2 text-sm hover:bg-muted" href="/dashboard">
          Dashboard
        </Link>
      </nav>
      <div className="border-t p-4">
        <Button className="w-full" onClick={handleLogout} type="button" variant="ghost">Đăng xuất</Button>
      </div>
    </aside>
  );
}
```

- [ ] **Step 3: Add dashboard layout**

Create `src/frontend/src/app/(dashboard)/layout.tsx`:

```tsx
import { AppSidebar } from '@/shared/components/AppSidebar';

export default function DashboardLayout({ children }: { children: React.ReactNode }) {
  return (
    <div className="flex min-h-screen bg-muted/40">
      <AppSidebar />
      <main className="flex-1 p-6">{children}</main>
    </div>
  );
}
```

- [ ] **Step 4: Add dashboard page**

Create `src/frontend/src/app/(dashboard)/dashboard/page.tsx`:

```tsx
export default function DashboardPage() {
  return (
    <div className="space-y-2">
      <h1 className="text-2xl font-semibold">Dashboard</h1>
      <p className="text-muted-foreground">Nền tảng frontend admin Phase 1 đã sẵn sàng.</p>
    </div>
  );
}
```

- [ ] **Step 5: Commit**

```bash
git add src/frontend/src/middleware.ts src/frontend/src/shared/components/AppSidebar.tsx src/frontend/src/app/'(dashboard)'
git commit -m "feat(frontend): add protected dashboard shell"
```

---

### Task 5: PermissionGuard And Cleanup

**Files:**
- Create: `src/frontend/src/shared/components/PermissionGuard.tsx`
- Delete: `src/frontend/src/lib/api-client.ts`
- Delete: `src/frontend/src/lib/auth-context.tsx`

- [ ] **Step 1: Add PermissionGuard**

Create `src/frontend/src/shared/components/PermissionGuard.tsx`:

```tsx
'use client';

import { ReactNode } from 'react';
import { useAuth } from '@/domains/auth/hooks/useAuth';

interface PermissionGuardProps {
  allowedPermissions: string[];
  children: ReactNode;
  fallback?: ReactNode;
}

export function PermissionGuard({ allowedPermissions, children, fallback = null }: PermissionGuardProps) {
  const { hasAnyPermission, isLoading } = useAuth();

  if (isLoading) return null;
  return hasAnyPermission(allowedPermissions) ? <>{children}</> : <>{fallback}</>;
}
```

- [ ] **Step 2: Remove superseded lib files**

Delete:

```bash
rm src/frontend/src/lib/api-client.ts src/frontend/src/lib/auth-context.tsx
```

- [ ] **Step 3: Search for old imports**

Run:

```bash
cd src/frontend
rg "@/lib|src/lib"
```

Expected: no output.

- [ ] **Step 4: Commit**

```bash
git add -A src/frontend/src
git commit -m "feat(frontend): add permission guard"
```

---

### Task 6: Verification

**Files:**
- Modify if needed: files from Tasks 1-5 only.

- [ ] **Step 1: Type/lint check**

Run:

```bash
cd src/frontend
npm run lint
```

Expected: pass. If `next lint` asks to configure ESLint, keep existing `.eslintrc.json` and rerun.

- [ ] **Step 2: Build check**

Run:

```bash
cd src/frontend
npm run build
```

Expected: production build succeeds.

- [ ] **Step 3: Manual route check**

Run:

```bash
cd src/frontend
npm run dev
```

Check:

- `/login` renders a centered login card.
- `/dashboard` without `ihrm_at` cookie redirects to `/login`.
- Failed login shows `Email hoặc mật khẩu không đúng.`.
- Successful login redirects to `/dashboard`.
- Logout clears auth and returns to `/login`.

- [ ] **Step 4: Commit verification fixes only if needed**

```bash
git add -A src/frontend
git commit -m "fix(frontend): stabilize phase 1 foundation"
```

Skip this commit if no fixes were needed.

---

## Self-Review

- Spec coverage: AC1-AC10 covered by Tasks 1-6.
- Backend mismatch handled: no `/auth/refresh`; plan uses existing login/logout/me and redirects on 401.
- Scope held: no Phase 2 modules, no CRUD, no SSO, no forgot password, no TanStack Query/Zustand/form libraries.
- Main risk: backend user resource does not include permission codes; plan derives permissions from role detail endpoints.
