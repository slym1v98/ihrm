'use client';
import { ReviewSection } from '@/domains/performance/components/ReviewSection';
import { useState, useCallback } from 'react';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { toast } from 'sonner';
import { Plus, Play, XCircle, CheckCircle, Target } from 'lucide-react';
import { usePerformanceCycles, useCreateCycle, useCycleAction, useGoals, useCreateGoal, useCompleteGoal, useReviews } from '@/domains/performance/hooks/usePerformance';
import type { PerformanceCycle, Goal, CreateGoalPayload } from '@/domains/performance/models/performance';
import { DataTable, type Column } from '@/shared/components/DataTable';
import { Drawer, DrawerContent, DrawerHeader, DrawerTitle, DrawerDescription, DrawerBody, DrawerFooter } from '@/shared/components/ui/drawer';
import { Button } from '@/shared/components/ui/button';
import { Input } from '@/shared/components/ui/input';
import { Label } from '@/shared/components/ui/label';
import { Badge } from '@/shared/components/ui/badge';
import { extractErrorMessage } from '@/core/errors/messages';
import { useDateFormatter } from '@/shared/hooks/useDateFormatter';

const statusL: Record<string, string> = { draft: 'Nháp', active: 'Đang diễn ra', completed: 'Hoàn thành', cancelled: 'Đã huỷ' };
const statusV: Record<string, 'default' | 'secondary' | 'destructive' | 'outline'> = { draft: 'secondary', active: 'default', completed: 'outline', cancelled: 'destructive' };
const gStatusL: Record<string, string> = { pending: 'Chờ', in_progress: 'Đang TH', completed: 'Hoàn thành', cancelled: 'Đã huỷ' };
const gStatusV: Record<string, 'default' | 'secondary' | 'destructive' | 'outline'> = { pending: 'secondary', in_progress: 'default', completed: 'outline', cancelled: 'destructive' };
const schema = z.object({ code: z.string().min(2), name: z.string().min(1), start_date: z.string().min(1), end_date: z.string().min(1), description: z.string().optional() }); type F = z.infer<typeof schema>;
const gSchema = z.object({ cycle_id: z.string().min(1), employee_id: z.string().min(1), title: z.string().min(1), description: z.string().optional(), weight: z.coerce.number().min(0).max(100).optional(), target_value: z.coerce.number().optional() }); type GF = z.infer<typeof gSchema>;

export function PerformanceListPage() {
  const { formatDate } = useDateFormatter();
  const { data: cycles, isLoading: l1 } = usePerformanceCycles();
  const { data: goals, isLoading: l2 } = useGoals();
  const { data: reviews } = useReviews();
  const create = useCreateCycle(); const action = useCycleAction();
  const createGoal = useCreateGoal(); const completeGoal = useCompleteGoal();
  const [open, setOpen] = useState(false); const [gOpen, setGOpen] = useState(false);
  const form = useForm<F>({ resolver: zodResolver(schema), defaultValues: { code: '', name: '', start_date: '', end_date: '', description: '' } });
  const gForm = useForm<GF>({ resolver: zodResolver(gSchema), defaultValues: { cycle_id: '', employee_id: '', title: '', description: '', weight: undefined, target_value: undefined } });
  const openCreate = useCallback(() => { form.reset({ code: '', name: '', start_date: '', end_date: '', description: '' }); setOpen(true); }, [form]);
  const openCreateGoal = useCallback(() => { gForm.reset({ cycle_id: '', employee_id: '', title: '', description: '', weight: undefined, target_value: undefined }); setGOpen(true); }, [gForm]);
  const onSubmit = useCallback(async (v: F) => { try { await create.mutateAsync(v); toast.success('Tạo kỳ đánh giá thành công'); setOpen(false); } catch (raw) { toast.error(extractErrorMessage(raw)); } }, [create]);
  const onSubmitGoal = useCallback(async (v: GF) => { try { await createGoal.mutateAsync(v as CreateGoalPayload); toast.success('Tạo mục tiêu thành công'); setGOpen(false); } catch (raw) { toast.error(extractErrorMessage(raw)); } }, [createGoal]);
  const handleCycleAction = useCallback(async (id: string, a: 'activate' | 'complete' | 'cancel') => {
    try { await action.mutateAsync({ id, action: a }); toast.success({ activate: 'Đã kích hoạt', complete: 'Đã hoàn thành', cancel: 'Đã huỷ' }[a]); } catch (raw) { toast.error(extractErrorMessage(raw)); }
  }, [action]);
  const handleCompleteGoal = useCallback(async (id: string) => { try { await completeGoal.mutateAsync(id); toast.success('Mục tiêu hoàn thành'); } catch (raw) { toast.error(extractErrorMessage(raw)); } }, [completeGoal]);

  const cycleCols: Column<PerformanceCycle>[] = [
    { header: 'Mã', accessor: 'code', className: 'font-mono text-xs w-20' }, { header: 'Tên kỳ', accessor: 'name' },
    { header: 'Bắt đầu', accessor: undefined, cell: (c) => formatDate(c.start_date), className: 'font-mono text-xs w-28' },
    { header: 'Kết thúc', accessor: undefined, cell: (c) => formatDate(c.end_date), className: 'font-mono text-xs w-28' },
    { header: 'Trạng thái', accessor: undefined, className: 'w-20', cell: (c) => <Badge variant={statusV[c.status]}>{statusL[c.status]}</Badge> },
    { header: '', accessor: undefined, className: 'text-right w-20', cell: (c) => (
      <div className="flex justify-end gap-1">
        {c.status === 'draft' && <Button variant="ghost" size="sm" title="Kích hoạt" onClick={() => handleCycleAction(c.id, 'activate')}><Play className="h-4 w-4 text-green-600" /></Button>}
        {c.status === 'active' && <Button variant="ghost" size="sm" title="Hoàn thành" onClick={() => handleCycleAction(c.id, 'complete')}><CheckCircle className="h-4 w-4" /></Button>}
        {(c.status === 'draft' || c.status === 'active') && <Button variant="ghost" size="sm" title="Huỷ" onClick={() => handleCycleAction(c.id, 'cancel')}><XCircle className="h-4 w-4 text-destructive" /></Button>}
      </div>
    )},
  ];
  const goalCols: Column<Goal>[] = [
    { header: 'Mục tiêu', accessor: 'title' },
    { header: 'Trọng số', accessor: 'weight', className: 'w-16 text-center' },
    { header: 'Giá trị', accessor: undefined, className: 'w-20 text-center', cell: (g) => g.actual_value != null ? `${g.actual_value}` : g.target_value != null ? `/${g.target_value}` : '-' },
    { header: 'Trạng thái', accessor: undefined, className: 'w-20', cell: (g) => <Badge variant={gStatusV[g.status]}>{gStatusL[g.status]}</Badge> },
    { header: '', accessor: undefined, className: 'text-right w-16', cell: (g) => g.status === 'in_progress' ? <Button variant="ghost" size="sm" title="Hoàn thành" onClick={() => handleCompleteGoal(g.id)}><CheckCircle className="h-4 w-4" /></Button> : null },
  ];
  return (<div className="space-y-6">
    <div><div className="flex gap-2 mb-2"><Button onClick={openCreate}><Plus className="h-4 w-4 mr-1" />Tạo kỳ đánh giá</Button><Button onClick={openCreateGoal} variant="ghost"><Target className="h-4 w-4 mr-1" />Thêm mục tiêu</Button></div>
      <h2 className="text-sm font-semibold mb-2">Kỳ đánh giá</h2>
      <DataTable columns={cycleCols} data={cycles ?? []} isLoading={l1} rowKey="id" emptyMessage="Chưa có kỳ đánh giá" /></div>
    <div><h2 className="text-sm font-semibold mb-2">Mục tiêu</h2>
      <DataTable columns={goalCols} data={goals ?? []} isLoading={l2} rowKey="id" emptyMessage="Chưa có mục tiêu" /></div>
    <div><h2 className="text-sm font-semibold mb-2">Đánh giá</h2><ReviewSection /></div>
    <Drawer open={open} onOpenChange={setOpen}><DrawerContent size="sm"><DrawerHeader><DrawerTitle>Tạo kỳ đánh giá</DrawerTitle><DrawerDescription>Nhập thông tin kỳ mới</DrawerDescription></DrawerHeader>
      <DrawerBody><form id="pf-form" onSubmit={form.handleSubmit(onSubmit)} className="space-y-4">...</form></DrawerBody>
      <DrawerFooter><Button variant="ghost" onClick={() => setOpen(false)}>Hủy</Button><Button type="submit" form="pf-form" disabled={create.isPending}>Tạo</Button></DrawerFooter></DrawerContent></Drawer></div>);
}
