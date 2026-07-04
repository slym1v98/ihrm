'use client';

import Link from 'next/link';
import { usePathname } from 'next/navigation';
import { LayoutDashboard, Building2, Users, Calendar, Clock, ClipboardCheck, Package, GraduationCap, UserPlus, UserX, BarChart3, FileText, Settings, History, GitBranch, UserCheck, DollarSign } from 'lucide-react';
import { useSidebar } from '@/shared/hooks/useSidebar';
import { cn } from '@/core/utils/cn';

const nav = [
  { section: 'Nhân sự', items: [
    { href: '/employees', label: 'Nhân viên', icon: Users },
  ]},
  { section: 'Nghỉ phép', items: [
    { href: '/leave', label: 'Đơn nghỉ phép', icon: Calendar },
  ]},
  { section: 'Ca làm việc', items: [
    { href: '/shift', label: 'Ca làm việc', icon: Clock },
  ]},
  { section: 'Chấm công', items: [
    { href: '/attendance', label: 'Chấm công', icon: ClipboardCheck },
  ]},
  { section: 'Tài sản', items: [
    { href: '/asset', label: 'Tài sản', icon: Package },
  ]},
  { section: 'Đào tạo', items: [
    { href: '/training', label: 'Khoá học', icon: GraduationCap },
  ]},
  { section: 'Hội nhập', items: [
    { href: '/onboarding', label: 'Onboarding', icon: UserPlus },
  ]},
  { section: 'Thôi việc', items: [
    { href: '/offboarding', label: 'Offboarding', icon: UserX },
  ]},
  { section: 'Hiệu suất', items: [
    { href: '/performance', label: 'Đánh giá', icon: BarChart3 },
  ]},
  { section: 'Tuyển dụng', items: [
    { href: '/recruitment', label: 'Tuyển dụng', icon: UserCheck },
  ]},
  { section: 'Báo cáo', items: [
    { href: '/reports', label: 'Báo cáo', icon: FileText },
  ]},
  { section: 'Cấu hình', items: [
    { href: '/settings', label: 'Cài đặt', icon: Settings },
  ]},
  { section: 'Lương', items: [
    { href: '/payroll', label: 'Bảng lương', icon: DollarSign },
  ]},
  { section: 'Hệ thống', items: [
    { href: '/workflow', label: 'Quy trình duyệt', icon: GitBranch },
    { href: '/workflow/designer', label: 'Thiết kế quy trình', icon: GitBranch },
    { href: '/audit', label: 'Nhật ký', icon: History },
  ]},
  { section: 'Tổ chức', items: [
    { href: '/organization/branches', label: 'Chi nhánh', icon: Building2 },
    { href: '/organization/departments', label: 'Phòng ban', icon: Building2 },
    { href: '/organization/positions', label: 'Chức vụ', icon: Building2 },
    { href: '/organization/tree', label: 'Sơ đồ tổ chức', icon: Building2 },
    { href: '/employees/[id]', label: 'Chi tiết NV', icon: Users, hidden: true },
  ]},
];

export function AppSidebar() {
  const pathname = usePathname();
  const { collapsed } = useSidebar();

  return (
    <aside className={cn(
      'flex h-screen flex-col overflow-hidden border-r bg-[hsl(var(--card))] transition-all duration-200',
      collapsed ? 'w-16' : 'w-64',
    )}>
      {/* Logo area — same height as header */}
      <div className={cn(
        'flex items-center border-b h-16 shrink-0',
        collapsed ? 'justify-center px-0' : 'px-4',
      )}>
        {collapsed ? (
          <span className="text-lg font-bold text-primary">i</span>
        ) : (
          <span className="text-lg font-bold text-primary">iHRM</span>
        )}
      </div>

      <nav className="flex-1 space-y-1 p-2 overflow-y-auto">
        <NavItem href="/dashboard" icon={LayoutDashboard} label="Dashboard" collapsed={collapsed} pathname={pathname} />
        {nav.map(group => (
          <div key={group.section}>
            {!collapsed && (
              <p className="px-2 pb-1 pt-3 text-xs font-semibold uppercase tracking-wider text-muted-foreground">{group.section}</p>
            )}
            {group.items.filter(i => !i.hidden).map(item => (
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
  hidden?: boolean;
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
