'use client';
import { useState, useCallback } from 'react';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { toast } from 'sonner';
import { Plus, Send, CheckCircle, XCircle } from 'lucide-react';
import { useOffboardingRequests, useCreateOffboardingRequest, useOffboardingAction } from '@/domains/offboarding/hooks/useOffboarding';
import type { OffboardingRequest, CreateOffboardingPayload } from '@/domains/offboarding/models/offboarding';
import { DataTable, type Column } from '@/shared/components/DataTable';
import { Drawer, DrawerContent, DrawerHeader, DrawerTitle, DrawerDescription, DrawerBody, DrawerFooter } from '@/shared/components/ui/drawer';
import { Button } from '@/shared/components/ui/button';
import { Input } from '@/shared/components/ui/input';
import { Label } from '@/shared/components/ui/label';
import { Badge } from '@/shared/components/ui/badge';
import { extractErrorMessage } from '@/core/errors/messages';
import { useDateFormatter } from '@/shared/hooks/useDateFormatter';

const sL: Record<string, string> = { draft: 'Nháp', pending_approval: 'Chờ duyệt', approved: 'Đã duyệt', rejected: 'Từ chối' };
const sV: Record<string, 'default' | 'secondary' | 'destructive' | 'outline'> = { draft: 'secondary', pending_approval: 'default', approved: 'outline', rejected: 'destructive' };
const schema = z.object({ employee_id: z.string().min(1), reason: z.string().optional() }); type F = z.infer<typeof schema>;

export function OffboardingListPage() {
  const { formatDateTime } = useDateFormatter();
  const { data: reqs, isLoading } = useOffboardingRequests();
  const create = useCreateOffboardingRequest();
  const action = useOffboardingAction();
  const [open, setOpen] = useState(false);
  const form = useForm<F>({ resolver: zodResolver(schema), defaultValues: { employee_id: '', reason: '' } });
  const openCreate = useCallback(() => { form.reset({ employee_id: '', reason: '' }); setOpen(true); }, [form]);
  const onSubmit = useCallback(async (v: F) => { try { await create.mutateAsync(v as CreateOffboardingPayload); toast.success('Tạo yêu cầu thành công'); setOpen(false); } catch (raw) { toast.error(extractErrorMessage(raw)); } }, [create]);
  const handleAction = useCallback(async (id: string, a: 'submit' | 'approve' | 'reject') => {
    try { await action.mutateAsync({ id, action: a }); toast.success(a === 'approve' ? 'Đã duyệt' : a === 'submit' ? 'Đã gửi' : 'Đã từ chối'); } catch (raw) { toast.error(extractErrorMessage(raw)); }
  }, [action]);
  const cols: Column<OffboardingRequest>[] = [
    { header: 'Nhân viên', accessor: 'employee_id', className: 'font-mono text-xs w-32 truncate' },
    { header: 'Lý do', accessor: 'reason', className: 'max-w-xs truncate' },
    { header: 'Trạng thái', accessor: undefined, className: 'w-20', cell: (r) => <Badge variant={sV[r.status]}>{sL[r.status]}</Badge> },
    { header: 'Ngày tạo', accessor: undefined, className: 'font-mono text-xs w-36', cell: (r) => r.created_at ? formatDateTime(r.created_at) : '-' },
    { header: '', accessor: undefined, className: 'text-right w-28', cell: (r) => (
      <div className="flex justify-end gap-1">
        {r.status === 'draft' && <Button variant="ghost" size="sm" title="Gửi duyệt" onClick={() => handleAction(r.id, 'submit')}><Send className="h-4 w-4 text-blue-600" /></Button>}
        {r.status === 'pending_approval' && <><Button variant="ghost" size="sm" title="Duyệt" onClick={() => handleAction(r.id, 'approve')}><CheckCircle className="h-4 w-4 text-green-600" /></Button>
        <Button variant="ghost" size="sm" title="Từ chối" onClick={() => handleAction(r.id, 'reject')}><XCircle className="h-4 w-4 text-destructive" /></Button></>}
      </div>
    )},
  ];
  return (<div className="space-y-4"><Button onClick={openCreate}><Plus className="h-4 w-4 mr-1" />Tạo yêu cầu</Button>
    <DataTable columns={cols} data={reqs ?? []} isLoading={isLoading} rowKey="id" emptyMessage="Chưa có yêu cầu nào" />
    <Drawer open={open} onOpenChange={setOpen}><DrawerContent size="sm"><DrawerHeader><DrawerTitle>Tạo yêu cầu Offboarding</DrawerTitle><DrawerDescription>Nhập thông tin yêu cầu mới</DrawerDescription></DrawerHeader>
      <DrawerBody><form id="off-form" onSubmit={form.handleSubmit(onSubmit)} className="space-y-4">
        <div className="space-y-2"><Label htmlFor="oe">ID Nhân viên <span className="text-destructive">*</span></Label><Input id="oe" autoComplete="off" {...form.register('employee_id')} /></div>
        <div className="space-y-2"><Label htmlFor="or">Lý do</Label><textarea id="or" className="h-20 w-full rounded-md border bg-[hsl(var(--card))] px-2 py-1 text-[13px] outline-none resize-none" {...form.register('reason')} /></div>
      </form></DrawerBody>
      <DrawerFooter><Button variant="ghost" onClick={() => setOpen(false)}>Hủy</Button><Button type="submit" form="off-form" disabled={create.isPending}>Tạo</Button></DrawerFooter>
    </DrawerContent></Drawer></div>);
}
