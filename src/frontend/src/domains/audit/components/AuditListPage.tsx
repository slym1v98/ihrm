'use client';
import { useAuditLogs } from '@/domains/audit/hooks/useAudit';
import type { AuditLog } from '@/domains/audit/models/audit';
import { DataTable, type Column } from '@/shared/components/DataTable';
import { Badge } from '@/shared/components/ui/badge';
import { useDateFormatter } from '@/shared/hooks/useDateFormatter';

export function AuditListPage() {
  const { formatDateTime } = useDateFormatter();
  const { data: logs, isLoading } = useAuditLogs();
  const cols: Column<AuditLog>[] = [
    { header: 'Thời gian', accessor: undefined, className: 'font-mono text-xs w-40', cell: (l) => l.created_at ? formatDateTime(l.created_at) : '-' },
    { header: 'Sự kiện', accessor: 'event', className: 'font-mono text-xs w-32' },
    { header: 'Đối tượng', accessor: 'auditable_type', className: 'text-xs w-40' },
    { header: 'ID', accessor: 'auditable_id', className: 'font-mono text-xs w-32 truncate' },
    { header: 'IP', accessor: 'ip_address', className: 'font-mono text-xs w-32' },
    { header: '', accessor: undefined, cell: (l) =>
      l.old_values || l.new_values
        ? <Badge variant="secondary">Xem</Badge>
        : null },
  ];
  return (<div className="space-y-4">
    <h2 className="text-sm font-semibold">Nhật ký truy cập</h2>
    <DataTable columns={cols} data={logs ?? []} isLoading={isLoading} rowKey="id" emptyMessage="Chưa có nhật ký" />
  </div>);
}
