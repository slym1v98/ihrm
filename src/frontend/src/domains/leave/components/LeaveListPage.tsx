'use client';

import { useState, useCallback } from 'react';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { toast } from 'sonner';
import { CheckCircle, XCircle, Ban, Plus } from 'lucide-react';
import { useLeaveRequests, useLeaveTypes, useCreateLeaveRequest, useApproveLeaveRequest, useRejectLeaveRequest, useCancelLeaveRequest } from '@/domains/leave/hooks/useLeave';
import type { LeaveRequest } from '@/domains/leave/models/leave';
import { DataTable, type Column } from '@/shared/components/DataTable';
import { Drawer, DrawerContent, DrawerHeader, DrawerTitle, DrawerDescription, DrawerBody, DrawerFooter } from '@/shared/components/ui/drawer';
import { Button } from '@/shared/components/ui/button';
import { Badge } from '@/shared/components/ui/badge';
import { extractErrorMessage } from '@/core/errors/messages';
import { Input } from '@/shared/components/ui/input';
import { Label } from '@/shared/components/ui/label';
import { useDateFormatter } from '@/shared/hooks/useDateFormatter';

const statusLabels: Record<string, string> = {
  pending: 'Chờ duyệt',
  approved: 'Đã duyệt',
  rejected: 'Từ chối',
  cancelled: 'Đã huỷ',
};

const statusVariant: Record<string, 'secondary' | 'default' | 'destructive' | 'outline'> = {
  pending: 'secondary',
  approved: 'default',
  rejected: 'destructive',
  cancelled: 'outline',
};

const durationLabels: Record<string, string> = {
  day: 'Ngày',
  half_day: 'Nửa ngày',
  hour: 'Giờ',
};

const createSchema = z.object({
  leave_type_id: z.string().min(1, 'Chọn loại nghỉ phép'),
  start_at: z.string().min(1, 'Chọn ngày bắt đầu'),
  end_at: z.string().min(1, 'Chọn ngày kết thúc'),
  duration_unit: z.enum(['day', 'half_day', 'hour']),
  reason: z.string().optional(),
});

type CreateForm = z.infer<typeof createSchema>;

export function LeaveListPage() {
  const { formatDate } = useDateFormatter();
  const { data: requests, isLoading } = useLeaveRequests();
  const { data: leaveTypes } = useLeaveTypes();
  const createLeave = useCreateLeaveRequest();
  const approveLeave = useApproveLeaveRequest();
  const rejectLeave = useRejectLeaveRequest();
  const cancelLeave = useCancelLeaveRequest();

  const [dialogOpen, setDialogOpen] = useState(false);
  const [rejectDialog, setRejectDialog] = useState<{ id: string } | null>(null);
  const [rejectReason, setRejectReason] = useState('');

  const form = useForm<CreateForm>({
    resolver: zodResolver(createSchema),
    defaultValues: { leave_type_id: '', start_at: '', end_at: '', duration_unit: 'day', reason: '' },
  });

  const onSubmit = useCallback(async (values: CreateForm) => {
    try {
      await createLeave.mutateAsync({
        leave_type_id: values.leave_type_id,
        start_at: values.start_at,
        end_at: values.end_at,
        duration_unit: values.duration_unit,
        reason: values.reason || undefined,
      });
      toast.success('Tạo đơn nghỉ phép thành công');
      setDialogOpen(false);
      form.reset();
    } catch (raw) {
      toast.error(extractErrorMessage(raw));
    }
  }, [createLeave, form]);

  const handleApprove = useCallback(async (id: string) => {
    try {
      await approveLeave.mutateAsync(id);
      toast.success('Đã duyệt đơn nghỉ phép');
    } catch (raw) {
      toast.error(extractErrorMessage(raw));
    }
  }, [approveLeave]);

  const handleReject = useCallback(async () => {
    if (!rejectDialog) return;
    try {
      await rejectLeave.mutateAsync({ id: rejectDialog.id, reason: rejectReason });
      toast.success('Đã từ chối đơn nghỉ phép');
      setRejectDialog(null);
      setRejectReason('');
    } catch (raw) {
      toast.error(extractErrorMessage(raw));
    }
  }, [rejectDialog, rejectReason, rejectLeave]);

  const handleCancel = useCallback(async (id: string) => {
    try {
      await cancelLeave.mutateAsync(id);
      toast.success('Đã huỷ đơn nghỉ phép');
    } catch (raw) {
      toast.error(extractErrorMessage(raw));
    }
  }, [cancelLeave]);

  const leaveTypeName = (id: string) => leaveTypes?.find(t => t.id === id)?.name ?? id;

  const columns: Column<LeaveRequest>[] = [
    { header: 'Loại nghỉ', accessor: undefined, cell: (r) => leaveTypeName(r.leave_type_id), headerClassName: 'w-32' },
    { header: 'Thời gian', accessor: undefined, cell: (r) => `${formatDate(r.start_at)} → ${formatDate(r.end_at)}`, className: 'text-xs font-mono' },
    { header: 'Đơn vị', accessor: undefined, cell: (r) => durationLabels[r.duration_unit] ?? r.duration_unit, className: 'w-20' },
    { header: 'Số phút', accessor: 'duration_minutes', className: 'text-right w-20' },
    { header: 'Lý do', accessor: 'reason', cell: (r) => r.reason ?? '—', className: 'max-w-xs truncate text-muted-foreground' },
    {
      header: 'Trạng thái', accessor: undefined, className: 'w-24',
      cell: (r) => <Badge variant={statusVariant[r.status]}>{statusLabels[r.status]}</Badge>,
    },
    {
      header: 'Thao tác', accessor: undefined, className: 'text-right w-32',
      cell: (r) => (
        <div className="flex justify-end gap-1">
          {r.status === 'pending' && (
            <>
              <Button variant="ghost" size="sm" title="Duyệt" onClick={() => handleApprove(r.id)}>
                <CheckCircle className="h-4 w-4 text-green-600" />
              </Button>
              <Button variant="ghost" size="sm" title="Từ chối" onClick={() => setRejectDialog({ id: r.id })}>
                <XCircle className="h-4 w-4 text-destructive" />
              </Button>
              <Button variant="ghost" size="sm" title="Huỷ" onClick={() => handleCancel(r.id)}>
                <Ban className="h-4 w-4 text-muted-foreground" />
              </Button>
            </>
          )}
        </div>
      ),
    },
  ];

  return (
    <div className="space-y-4">
      <div className="flex items-center justify-between">
        <Button onClick={() => { form.reset(); setDialogOpen(true); }}>
          <Plus className="h-4 w-4 mr-1" /> Tạo đơn nghỉ
        </Button>
      </div>

      <DataTable<LeaveRequest> columns={columns} data={requests ?? []} isLoading={isLoading} rowKey="id" emptyMessage="Chưa có đơn nghỉ phép nào" />

      <Drawer open={dialogOpen} onOpenChange={setDialogOpen}>
        <DrawerContent size="sm">
          <DrawerHeader>
            <DrawerTitle>Tạo đơn nghỉ phép</DrawerTitle>
            <DrawerDescription>Nhập thông tin đơn nghỉ phép mới</DrawerDescription>
          </DrawerHeader>
          <DrawerBody>
            <form id="leave-form" onSubmit={form.handleSubmit(onSubmit)} className="space-y-4">
              <div className="space-y-2">
                <Label htmlFor="leave_type_id">Loại nghỉ phép <span className="text-destructive">*</span></Label>
                <select id="leave_type_id" className="h-8 w-full rounded-md border bg-[hsl(var(--card))] px-2 py-1 text-[13px] outline-none focus:ring-2 focus:ring-primary" {...form.register('leave_type_id')}>
                  <option value="">Chọn loại</option>
                  {leaveTypes?.filter(t => t.is_active).map(t => <option key={t.id} value={t.id}>{t.name}</option>)}
                </select>
                {form.formState.errors.leave_type_id && <p className="text-xs text-destructive">{form.formState.errors.leave_type_id.message}</p>}
              </div>
              <div className="grid grid-cols-2 gap-3">
                <div className="space-y-2">
                  <Label htmlFor="start_at">Ngày bắt đầu <span className="text-destructive">*</span></Label>
                  <Input id="start_at" type="date" autoComplete="off" {...form.register('start_at')} />
                  {form.formState.errors.start_at && <p className="text-xs text-destructive">{form.formState.errors.start_at.message}</p>}
                </div>
                <div className="space-y-2">
                  <Label htmlFor="end_at">Ngày kết thúc <span className="text-destructive">*</span></Label>
                  <Input id="end_at" type="date" autoComplete="off" {...form.register('end_at')} />
                  {form.formState.errors.end_at && <p className="text-xs text-destructive">{form.formState.errors.end_at.message}</p>}
                </div>
              </div>
              <div className="space-y-2">
                <Label htmlFor="duration_unit">Đơn vị tính <span className="text-destructive">*</span></Label>
                <select id="duration_unit" className="h-8 w-full rounded-md border bg-[hsl(var(--card))] px-2 py-1 text-[13px] outline-none focus:ring-2 focus:ring-primary" {...form.register('duration_unit')}>
                  <option value="day">Ngày</option>
                  <option value="half_day">Nửa ngày</option>
                  <option value="hour">Giờ</option>
                </select>
              </div>
              <div className="space-y-2">
                <Label htmlFor="reason">Lý do</Label>
                <textarea id="reason" className="h-20 w-full rounded-md border bg-[hsl(var(--card))] px-2 py-1 text-[13px] outline-none focus:ring-2 focus:ring-primary resize-none" {...form.register('reason')} />
              </div>
            </form>
          </DrawerBody>
          <DrawerFooter>
            <Button variant="ghost" type="button" onClick={() => setDialogOpen(false)}>Hủy</Button>
            <Button type="submit" form="leave-form" disabled={createLeave.isPending}>Tạo</Button>
          </DrawerFooter>
        </DrawerContent>
      </Drawer>

      <Drawer open={!!rejectDialog} onOpenChange={(o) => { if (!o) { setRejectDialog(null); setRejectReason(''); } }}>
        <DrawerContent size="sm">
          <DrawerHeader>
            <DrawerTitle>Từ chối đơn nghỉ phép</DrawerTitle>
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
            <Button variant="ghost" onClick={() => { setRejectDialog(null); setRejectReason(''); }}>Hủy</Button>
            <Button variant="destructive" onClick={handleReject} disabled={!rejectReason.trim() || rejectLeave.isPending}>Từ chối</Button>
          </DrawerFooter>
        </DrawerContent>
      </Drawer>
    </div>
  );
}
