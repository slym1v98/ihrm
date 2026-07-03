'use client';

import Link from 'next/link';
import { usePathname } from 'next/navigation';
import { LayoutDashboard, Building2, Users } from 'lucide-react';
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
  const pathname = usePathname();
  const { collapsed } = useSidebar();

  return (
    <aside className={cn(
      'flex flex-col border-r bg-[hsl(var(--card))] transition-all duration-200 pt-4',
      collapsed ? 'w-16' : 'w-64',
    )}>
      <nav className="flex-1 space-y-1 px-2">
        <NavItem href="/dashboard" icon={LayoutDashboard} label="Dashboard" collapsed={collapsed} pathname={pathname} />
        {nav.map(group => (
          <div key={group.section}>
            {!collapsed && (
              <p className="px-2 pb-1 pt-3 text-xs font-semibold uppercase tracking-wider text-muted-foreground">{group.section}</p>
            )}
            {group.items.map(item => (
              <NavItem key={item.href} href={item.href} icon={item.icon} label={item.label} collapsed={collapsed} pathname={pathname} />
            ))}
          </div>
        ))}
      </nav>
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
