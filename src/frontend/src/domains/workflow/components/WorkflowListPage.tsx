'use client';
import { useState, useCallback } from 'react';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { toast } from 'sonner';
import { useWorkflowTemplates, useWorkflowRequests, useStartWorkflow, useWorkflowAction } from '@/domains/workflow/hooks/useWorkflow';
import type { WorkflowTemplate, WorkflowRequest } from '@/domains/workflow/models/workflow';
import { DataTable, type Column } from '@/shared/components/DataTable';
import { Drawer, DrawerContent, DrawerHeader, DrawerTitle, DrawerDescription, DrawerBody, DrawerFooter } from '@/shared/components/ui/drawer';
import { Button } from '@/shared/components/ui/button';
import { Input } from '@/shared/components/ui/input';
import { Label } from '@/shared/components/ui/label';
import { Badge } from '@/shared/components/ui/badge';
import { extractErrorMessage } from '@/core/errors/messages';
import { useDateFormatter } from '@/shared/hooks/useDateFormatter';
import { Plus, CheckCircle, XCircle } from 'lucide-react';

const statusL: Record<string, string> = { pending: 'Chờ', in_review: 'Đang xử lý', approved: 'Đã duyệt', rejected: 'Từ chối', cancelled: 'Đã huỷ', returned: 'Trả lại' };
const statusV: Record<string, 'default' | 'secondary' | 'destructive' | 'outline'> = { pending: 'secondary', in_review: 'default', approved: 'outline', rejected: 'destructive', cancelled: 'destructive', returned: 'secondary' };
const schema = z.object({ workflow_template_id: z.string().min(1), subject_type: z.string().min(1), subject_id: z.string().min(1) }); type F = z.infer<typeof schema>;

export function WorkflowListPage() {
  const { formatDateTime } = useDateFormatter();
  const { data: templates } = useWorkflowTemplates();
  const { data: requests, isLoading } = useWorkflowRequests();
  const startReq = useStartWorkflow();
  const action = useWorkflowAction();
  const [open, setOpen] = useState(false);
  const form = useForm<F>({ resolver: zodResolver(schema), defaultValues: { workflow_template_id: '', subject_type: '', subject_id: '' } });
  const openCreate = useCallback(() => { form.reset({ workflow_template_id: '', subject_type: '', subject_id: '' }); setOpen(true); }, [form]);
  const onSubmit = useCallback(async (v: F) => { try { await startReq.mutateAsync(v); toast.success('Tạo yêu cầu thành công'); setOpen(false); } catch (raw) { toast.error(extractErrorMessage(raw)); } }, [startReq]);
  const handleAction = useCallback(async (id: string, a: 'approve' | 'reject' | 'cancel') => {
    try { await action.mutateAsync({ id, action: a }); toast.success(a === 'approve' ? 'Đã duyệt' : a === 'cancel' ? 'Đã huỷ' : 'Đã từ chối'); } catch (raw) { toast.error(extractErrorMessage(raw)); }
  }, [action]);
  const reqCols: Column<WorkflowRequest>[] = [
    { header: 'Loại', accessor: 'subject_type', className: 'text-xs w-24' },
    { header: 'Trạng thái', accessor: undefined, className: 'w-20', cell: (r) => <Badge variant={statusV[r.status]}>{statusL[r.status]}</Badge> },
    { header: 'Người gửi', accessor: 'submitted_by', className: 'text-xs w-28 truncate' },
    { header: 'Ngày', accessor: undefined, className: 'font-mono text-xs w-36', cell: (r) => r.created_at ? formatDateTime(r.created_at) : '-' },
    { header: '', accessor: undefined, className: 'text-right w-20', cell: (r) => (
      <div className="flex justify-end gap-1">
        {r.status === 'in_review' && <><Button variant="ghost" size="sm" title="Duyệt" onClick={() => handleAction(r.id, 'approve')}><CheckCircle className="h-4 w-4 text-green-600" /></Button>
        <Button variant="ghost" size="sm" title="Từ chối" onClick={() => handleAction(r.id, 'reject')}><XCircle className="h-4 w-4 text-destructive" /></Button></>}
        {(r.status === 'pending' || r.status === 'in_review') && <Button variant="ghost" size="sm" title="Huỷ" onClick={() => handleAction(r.id, 'cancel')}><XCircle className="h-4 w-4" /></Button>}
      </div>
    )},
  ];
  return (<div className="space-y-4"><Button onClick={openCreate}><Plus className="h-4 w-4 mr-1" />Tạo yêu cầu</Button>
    <DataTable columns={reqCols} data={requests ?? []} isLoading={isLoading} rowKey="id" emptyMessage="Chưa có yêu cầu nào" />
    <Drawer open={open} onOpenChange={setOpen}><DrawerContent size="sm"><DrawerHeader><DrawerTitle>Tạo yêu cầu workflow</DrawerTitle><DrawerDescription>Nhập thông tin yêu cầu mới</DrawerDescription></DrawerHeader>
      <DrawerBody><form id="wf-form" onSubmit={form.handleSubmit(onSubmit)} className="space-y-4">
        <div className="space-y-2"><Label htmlFor="tpl">Template ID <span className="text-destructive">*</span></Label>
          <select id="tpl" className="w-full rounded-md border bg-[hsl(var(--card))] px-2 py-1 text-[13px]" {...form.register('workflow_template_id')}>
            <option value="">Chọn template</option>
            {templates?.map((t) => <option key={t.id} value={t.id}>{t.name}</option>)}
          </select></div>
        <div className="space-y-2"><Label htmlFor="st">Loại đối tượng <span className="text-destructive">*</span></Label><Input id="st" autoComplete="off" {...form.register('subject_type')} /></div>
        <div className="space-y-2"><Label htmlFor="si">ID đối tượng <span className="text-destructive">*</span></Label><Input id="si" autoComplete="off" {...form.register('subject_id')} /></div>
      </form></DrawerBody>
      <DrawerFooter><Button variant="ghost" onClick={() => setOpen(false)}>Hủy</Button><Button type="submit" form="wf-form" disabled={startReq.isPending}>Tạo</Button></DrawerFooter>
    </DrawerContent></Drawer></div>);
}
