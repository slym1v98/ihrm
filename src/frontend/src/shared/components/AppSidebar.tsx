'use client';

import Link from 'next/link';
import { usePathname, useRouter } from 'next/navigation';
import { Moon, Sun, LogOut, LayoutDashboard, Building2, Users, PanelLeftClose, PanelLeft } from 'lucide-react';
import { useAuth } from '@/domains/auth/hooks/useAuth';
import { useTheme } from '@/shared/hooks/useTheme';
import { useSidebar } from '@/shared/hooks/useSidebar';
import { cn } from '@/core/utils/cn';

const nav = [
  { section: 'Nhân sự', items: [
    { href: '/employees', label: 'Nhân viên', icon: Users },
  ]},
  { section: 'Tổ chức', items: [
    { href: '/organization/branches', label: 'Chi nhánh', icon: Building2 },
    { href: '/organization/departments', label: 'Phòng ban', icon: Building2 },
    { href: '/organization/positions', label: 'Chức vụ', icon: Building2 },
    { href: '/organization/tree', label: 'Sơ đồ tổ chức', icon: Building2 },
  ]},
];

export function AppSidebar() {
  const router = useRouter();
  const pathname = usePathname();
  const { user, logout } = useAuth();
  const { theme, toggleTheme } = useTheme();
  const { collapsed, toggle } = useSidebar();

  async function handleLogout() {
    await logout();
    router.replace('/login');
  }

  return (
    <aside className={cn(
      'flex min-h-screen flex-col border-r bg-[hsl(var(--card))] transition-all duration-200',
      collapsed ? 'w-16' : 'w-64',
    )}>
      {/* Header */}
      <div className={cn('flex items-center border-b', collapsed ? 'justify-center p-2' : 'justify-between p-4')}>
        {!collapsed && <p className="text-lg font-semibold truncate">iHRM Admin</p>}
        <button type="button" onClick={toggle} title={collapsed ? 'Mở rộng' : 'Thu gọn'}
          className="rounded-md p-1 hover:bg-muted transition-colors">
          {collapsed ? <PanelLeft className="h-5 w-5" /> : <PanelLeftClose className="h-5 w-5" />}
        </button>
      </div>

      {/* Nav */}
      <nav className="flex-1 space-y-1 p-2">
        <NavItem href="/dashboard" icon={LayoutDashboard} label="Dashboard" collapsed={collapsed} pathname={pathname} />

        {nav.map(group => (
          <div key={group.section}>
            {!collapsed && (
              <p className="px-2 pb-1 pt-3 text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                {group.section}
              </p>
            )}
            {group.items.map(item => (
              <NavItem key={item.href} href={item.href} icon={item.icon} label={item.label} collapsed={collapsed} pathname={pathname} />
            ))}
          </div>
        ))}
      </nav>

      {/* Footer */}
      <div className={cn('border-t', collapsed ? 'space-y-2 p-2' : 'p-4')}>
        {!collapsed && (
          <p className="truncate text-sm text-muted-foreground mb-2">{user?.name ?? 'Đang tải...'}</p>
        )}
        <div className={cn('flex', collapsed ? 'flex-col items-center gap-2' : 'items-center justify-between gap-2')}>
          <button type="button" onClick={toggleTheme} title={theme === 'dark' ? 'Chế độ sáng' : 'Chế độ tối'}
            className="rounded-md p-1 hover:bg-muted transition-colors">
            {theme === 'dark' ? <Sun className="h-4 w-4" /> : <Moon className="h-4 w-4" />}
          </button>
          <button type="button" onClick={handleLogout} title="Đăng xuất"
            className="rounded-md p-1 hover:bg-muted transition-colors">
            <LogOut className="h-4 w-4" />
          </button>
        </div>
      </div>
    </aside>
  );
}

function NavItem({ href, icon: Icon, label, collapsed, pathname }: {
  href: string; icon: React.ComponentType<{ className?: string }>; label: string;
  collapsed: boolean; pathname: string;
}) {
  const active = pathname === href || pathname.startsWith(href + '/');
  return (
    <Link href={href}
      className={cn(
        'flex items-center rounded-md text-sm transition-colors',
        collapsed ? 'justify-center p-2' : 'gap-2 px-3 py-2',
        active
          ? 'bg-primary/10 text-primary font-medium'
          : 'text-muted-foreground hover:bg-muted hover:text-foreground',
      )}
      title={collapsed ? label : undefined}
    >
      <Icon className={cn('h-4 w-4 shrink-0', collapsed && 'h-5 w-5')} />
      {!collapsed && <span className="truncate">{label}</span>}
    </Link>
  );
}
