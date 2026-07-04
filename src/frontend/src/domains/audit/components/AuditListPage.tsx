'use client';
import { useAuditLogs } from '@/domains/audit/hooks/useAudit';
import type { AuditLog } from '@/domains/audit/models/audit';
import { DataTable, type Column } from '@/shared/components/DataTable';
import { useDateFormatter } from '@/shared/hooks/useDateFormatter';

export function AuditListPage() {
  const {formatDateTime}=useDateFormatter();
  const {data:logs,isLoading}=useAuditLogs();
  const cols:Column<AuditLog>[]=[
    {header:'Thời gian',accessor:undefined,cell:(l)=>formatDateTime(l.created_at),className:'font-mono text-xs w-36'},
    {header:'User',accessor:'user_id',className:'font-mono text-xs w-36 truncate text-muted-foreground'},
    {header:'Sự kiện',accessor:'event',className:'font-mono text-xs w-32'},
    {header:'Loại',accessor:'auditable_type',className:'w-40 truncate text-muted-foreground'},
    {header:'ID đối tượng',accessor:'auditable_id',className:'font-mono text-xs w-36 truncate text-muted-foreground'},
    {header:'IP',accessor:'ip_address',className:'font-mono text-xs w-28 text-muted-foreground'},
  ];
  return (<DataTable columns={cols} data={logs??[]} isLoading={isLoading} rowKey="id" emptyMessage="Chưa có nhật ký"/>);
}
