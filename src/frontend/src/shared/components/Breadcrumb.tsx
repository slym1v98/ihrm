'use client';

import { usePathname } from 'next/navigation';
import { ChevronRight, Home } from 'lucide-react';
import Link from 'next/link';

const labels: Record<string, string> = {
  dashboard: 'Dashboard',
  employees: 'Nhân viên',
  organization: 'Tổ chức',
  branches: 'Chi nhánh',
  departments: 'Phòng ban',
  positions: 'Chức vụ',
  tree: 'Sơ đồ tổ chức',
  leave: 'Nghỉ phép',
  shift: 'Ca làm việc',
  attendance: 'Chấm công',
  asset: 'Tài sản',
  training: 'Đào tạo',
  onboarding: 'Hội nhập',
  offboarding: 'Thôi việc',
  performance: 'Đánh giá',
  recruitment: 'Tuyển dụng',
  reports: 'Báo cáo',
  settings: 'Cài đặt',
  payroll: 'Bảng lương',
  audit: 'Nhật ký',
  workflow: 'Quy trình duyệt',
};

export function Breadcrumb() {
  const pathname = usePathname();
  const segments = pathname.split('/').filter(Boolean);

  const crumbs = segments.map((seg, i) => {
    const href = '/' + segments.slice(0, i + 1).join('/');
    const label = labels[seg] ?? seg.charAt(0).toUpperCase() + seg.slice(1);
    const isLast = i === segments.length - 1;
    return { href, label, isLast };
  });

  return (
    <nav className="flex items-center gap-1.5 text-sm text-muted-foreground">
      <Link href="/dashboard" className="rounded p-0.5 hover:text-foreground transition-colors">
        <Home className="h-4 w-4" />
      </Link>
      {crumbs.length > 0 && <ChevronRight className="h-3.5 w-3.5" />}
      {crumbs.map((crumb, i) => (
        <span key={crumb.href} className="flex items-center gap-1.5">
          {i > 0 && <ChevronRight className="h-3.5 w-3.5" />}
          {crumb.isLast ? (
            <span className="text-foreground font-medium">{crumb.label}</span>
          ) : (
            <Link href={crumb.href} className="hover:text-foreground transition-colors">{crumb.label}</Link>
          )}
        </span>
      ))}
    </nav>
  );
}
