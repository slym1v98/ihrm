'use client';

import { useCallback, useState } from 'react';
import { toast } from 'sonner';
import { CheckCircle, XCircle } from 'lucide-react';
import { useAdjustments, useApproveAdjustment, useRejectAdjustment } from '@/domains/attendance/hooks/useAdjustments';
import type { AttendanceAdjustmentRequest } from '@/domains/attendance/models/attendance';
import { DataTable, type Column } from '@/shared/components/DataTable';
import { Drawer, DrawerContent, DrawerHeader, DrawerTitle, DrawerDescription, DrawerBody, DrawerFooter } from '@/shared/components/ui/drawer';
import { Button } from '@/shared/components/ui/button';
import { Label } from '@/shared/components/ui/label';
import { Badge } from '@/shared/components/ui/badge';
import { extractErrorMessage } from '@/core/errors/messages';
import { useDateFormatter } from '@/shared/hooks/useDateFormatter';
import { useEmployeeDisplayName } from '@/shared/hooks/useEmployeeDisplayName';

const statusLabels: Record<string, string> = { pending: 'Chờ duyệt', approved: 'Đã duyệt', rejected: 'Từ chối' };
const statusVariant: Record<string, 'default' | 'secondary' | 'destructive'> = { pending: 'secondary', approved: 'default', rejected: 'destructive' };

export function AdjustmentSection() {
  const { formatDate } = useDateFormatter();
  const { getDisplayName } = useEmployeeDisplayName();
  const { data: adjustments, isLoading } = useAdjustments();
  const approveAdj = useApproveAdjustment();
  const rejectAdj = useRejectAdjustment();
  const [rejectTarget, setRejectTarget] = useState<{ id: string } | null>(null);
  const [rejectReason, setRejectReason] = useState('');

  const handleApprove = useCallback(async (id: string) => {
    try { await approveAdj.mutateAsync(id); toast.success('Đã duyệt'); }
    catch (raw) { toast.error(extractErrorMessage(raw)); }
  }, [approveAdj]);

  const handleReject = useCallback(async () => {
    if (!rejectTarget) return;
    try {
      await rejectAdj.mutateAsync({ id: rejectTarget.id, reason: rejectReason });
      toast.success('Đã từ chối');
      setRejectTarget(null);
      setRejectReason('');
    } catch (raw) { toast.error(extractErrorMessage(raw)); }
  }, [rejectTarget, rejectReason, rejectAdj]);

  const columns: Column<AttendanceAdjustmentRequest>[] = [
    { header: 'Nhân viên', accessor: undefined, cell: (r) => getDisplayName(r.employee_id), className: 'font-mono text-xs' },
    { header: 'Bảng công', accessor: 'attendance_timesheet_id', className: 'font-mono text-xs w-32 truncate' },
    { header: 'Lý do', accessor: 'reason', className: 'max-w-xs truncate text-muted-foreground' },
    { header: 'Ngày tạo', accessor: undefined, cell: (r) => formatDate(r.created_at), className: 'font-mono text-xs w-28' },
    { header: 'Trạng thái', accessor: undefined, className: 'w-24', cell: (r) => <Badge variant={statusVariant[r.status] ?? 'secondary'}>{statusLabels[r.status] ?? r.status}</Badge> },
    { header: '', accessor: undefined, className: 'text-right w-20', cell: (r) => r.status === 'pending' ? (
      <div className="flex justify-end gap-1">
        <Button variant="ghost" size="sm" title="Duyệt" onClick={() => handleApprove(r.id)}><CheckCircle className="h-4 w-4 text-green-600" /></Button>
        <Button variant="ghost" size="sm" title="Từ chối" onClick={() => setRejectTarget({ id: r.id })}><XCircle className="h-4 w-4 text-destructive" /></Button>
      </div>
    ) : null },
  ];

  return (
    <>
      <DataTable<AttendanceAdjustmentRequest> columns={columns} data={adjustments ?? []} isLoading={isLoading} rowKey="id" emptyMessage="Chưa có yêu cầu điều chỉnh" />
      <Drawer open={!!rejectTarget} onOpenChange={(o) => { if (!o) { setRejectTarget(null); setRejectReason(''); } }}>
        <DrawerContent size="sm">
          <DrawerHeader>
            <DrawerTitle>Từ chối điều chỉnh</DrawerTitle>
            <DrawerDescription>Nhập lý do từ chối</DrawerDescription>
          </DrawerHeader>
          <DrawerBody>
            <div className="space-y-2">
              <Label htmlFor="reject-reason">Lý do <span className="text-destructive">*</span></Label>
              <textarea id="reject-reason" className="h-24 w-full rounded-md border bg-[hsl(var(--card))] px-2 py-1 text-[13px] outline-none focus:ring-2 focus:ring-primary resize-none"
                value={rejectReason} onChange={(e) => setRejectReason(e.target.value)} />
            </div>
          </DrawerBody>
          <DrawerFooter>
            <Button variant="ghost" onClick={() => { setRejectTarget(null); setRejectReason(''); }}>Hủy</Button>
            <Button variant="destructive" onClick={handleReject} disabled={!rejectReason.trim() || rejectAdj.isPending}>Từ chối</Button>
          </DrawerFooter>
        </DrawerContent>
      </Drawer>
    </>
  );
}
