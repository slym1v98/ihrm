'use client';

import Link from 'next/link';
import { Moon, Sun } from 'lucide-react';
import { useRouter } from 'next/navigation';
import { useAuth } from '@/domains/auth/hooks/useAuth';
import { useTheme } from '@/shared/hooks/useTheme';
import { Button } from '@/shared/components/ui/button';

export function AppSidebar() {
  const router = useRouter();
  const { user, logout } = useAuth();
  const { theme, toggleTheme } = useTheme();

  async function handleLogout() {
    await logout();
    router.replace('/login');
  }

  return (
    <aside className="flex min-h-screen w-64 flex-col border-r bg-[hsl(var(--card))]">
      <div className="border-b p-4">
        <div className="flex items-center justify-between gap-2">
          <div>
            <p className="text-lg font-semibold">iHRM Admin</p>
            <p className="text-sm text-muted-foreground">{user?.name ?? 'Đang tải...'}</p>
          </div>
          <Button variant="ghost" size="sm" title="Toggle dark mode" onClick={toggleTheme}>
            {theme === 'dark' ? <Sun className="h-4 w-4" /> : <Moon className="h-4 w-4" />}
          </Button>
        </div>
      </div>
      <nav className="flex-1 space-y-1 p-4">
        <Link className="block rounded-md px-3 py-2 text-sm hover:bg-muted" href="/dashboard">Dashboard</Link>
        <p className="px-3 pb-1 pt-4 text-xs font-semibold uppercase tracking-wider text-muted-foreground">Nhân sự</p>
        <Link className="block rounded-md px-3 py-2 text-sm hover:bg-muted" href="/employees">Nhân viên</Link>
        <p className="px-3 pb-1 pt-4 text-xs font-semibold uppercase tracking-wider text-muted-foreground">Tổ chức</p>
        <Link className="block rounded-md px-3 py-2 text-sm hover:bg-muted" href="/organization/branches">Chi nhánh</Link>
        <Link className="block rounded-md px-3 py-2 text-sm hover:bg-muted" href="/organization/departments">Phòng ban</Link>
        <Link className="block rounded-md px-3 py-2 text-sm hover:bg-muted" href="/organization/positions">Chức vụ</Link>
        <Link className="block rounded-md px-3 py-2 text-sm hover:bg-muted" href="/organization/tree">Sơ đồ tổ chức</Link>
      </nav>
      <div className="border-t p-4">
        <Button className="w-full" onClick={handleLogout} type="button" variant="ghost">Đăng xuất</Button>
      </div>
    </aside>
  );
}
