'use client';
import { Users, CalendarCheck, ClipboardList, Wallet, ArrowRight } from 'lucide-react';
import { useDashboardSummary } from '@/domains/dashboard/hooks/useDashboard';
import Link from 'next/link';

const cards = [
  { key: 'employees', label: 'Nhân viên', icon: Users, href: '/employees', color: 'text-blue-600 bg-blue-100 dark:bg-blue-900/30' },
  { key: 'leaves', label: 'Đơn nghỉ phép', icon: CalendarCheck, href: '/leave', color: 'text-green-600 bg-green-100 dark:bg-green-900/30' },
  { key: 'attendances', label: 'Chấm công', icon: ClipboardList, href: '/attendance', color: 'text-amber-600 bg-amber-100 dark:bg-amber-900/30' },
  { key: 'payrollPeriods', label: 'Kỳ lương', icon: Wallet, href: '/payroll', color: 'text-purple-600 bg-purple-100 dark:bg-purple-900/30' },
];

const quickLinks = [
  { label: 'Sơ đồ tổ chức', href: '/organization/tree' },
  { label: 'Quản lý phòng ban', href: '/organization/departments' },
  { label: 'Tuyển dụng', href: '/recruitment' },
  { label: 'Đào tạo', href: '/training' },
  { label: 'Tài sản', href: '/asset' },
  { label: 'Workflow', href: '/workflow' },
  { label: 'Báo cáo', href: '/reports' },
];

export default function DashboardPage() {
  const { data: summary, isLoading } = useDashboardSummary();

  return (
    <div className="space-y-6">
      <div><h1 className="text-lg font-semibold">Tổng quan</h1><p className="text-sm text-muted-foreground">Bảng điều khiển hệ thống iHRM</p></div>

      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {cards.map(c => {
          const Icon = c.icon;
          const value = summary?.[c.key as keyof typeof summary];
          return (
            <Link key={c.key} href={c.href} className="block rounded-lg border bg-[hsl(var(--card))] p-4 hover:shadow-sm transition-shadow">
              <div className="flex items-center justify-between">
                <div className={`rounded-lg p-2 ${c.color}`}><Icon className="h-5 w-5" /></div>
                <ArrowRight className="h-4 w-4 text-muted-foreground" />
              </div>
              <div className="mt-3"><div className="text-2xl font-bold">{isLoading ? '...' : value ?? 0}</div>
              <div className="text-xs text-muted-foreground">{c.label}</div></div>
            </Link>
          );
        })}
      </div>

      <div><h2 className="text-sm font-semibold mb-2">Truy cập nhanh</h2>
        <div className="grid grid-cols-2 sm:grid-cols-4 gap-2">
          {quickLinks.map(l => (
            <Link key={l.href} href={l.href}
              className="rounded-lg border bg-[hsl(var(--card))] px-3 py-2 text-sm hover:bg-muted transition-colors">{l.label}</Link>
          ))}
        </div>
      </div>
    </div>
  );
}
