'use client';
import { useState, useCallback } from 'react';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { toast } from 'sonner';
import { Plus, Play, XCircle, CheckCircle, FileText } from 'lucide-react';
import { useOnboardingTemplates, useOnboardingPlans, useCreateOnboardingPlan, useOnboardingPlanAction } from '@/domains/onboarding/hooks/useOnboarding';
import type { OnboardingPlan, OnboardingTemplate, CreateOnboardingPlanPayload } from '@/domains/onboarding/models/onboarding';
import { DataTable, type Column } from '@/shared/components/DataTable';
import { Drawer, DrawerContent, DrawerHeader, DrawerTitle, DrawerDescription, DrawerBody, DrawerFooter } from '@/shared/components/ui/drawer';
import { Button } from '@/shared/components/ui/button';
import { Input } from '@/shared/components/ui/input';
import { Label } from '@/shared/components/ui/label';
import { Badge } from '@/shared/components/ui/badge';
import { extractErrorMessage } from '@/core/errors/messages';
import { useDateFormatter } from '@/shared/hooks/useDateFormatter';

const sL: Record<string, string> = { draft: 'Nháp', active: 'Đang thực hiện', completed: 'Hoàn thành', cancelled: 'Đã huỷ' };
const sV: Record<string, 'default' | 'secondary' | 'destructive' | 'outline'> = { draft: 'secondary', active: 'default', completed: 'outline', cancelled: 'destructive' };
const planSchema = z.object({ employee_id: z.string().min(1), candidate_id: z.string().optional(), template_id: z.string().optional(), start_date: z.string().min(1) }); type PF = z.infer<typeof planSchema>;

export function OnboardingListPage() {
  const { formatDate } = useDateFormatter();
  const { data: plans, isLoading } = useOnboardingPlans();
  const { data: templates } = useOnboardingTemplates();
  const create = useCreateOnboardingPlan();
  const action = useOnboardingPlanAction();
  const [open, setOpen] = useState(false);
  const form = useForm<PF>({ resolver: zodResolver(planSchema), defaultValues: { employee_id: '', candidate_id: '', template_id: '', start_date: '' } });
  const openCreate = useCallback(() => { form.reset({ employee_id: '', candidate_id: '', template_id: '', start_date: '' }); setOpen(true); }, [form]);
  const onSubmit = useCallback(async (v: PF) => { try { await create.mutateAsync(v as CreateOnboardingPlanPayload); toast.success('Tạo kế hoạch thành công'); setOpen(false); } catch (raw) { toast.error(extractErrorMessage(raw)); } }, [create]);
  const handleAction = useCallback(async (id: string, a: 'activate' | 'cancel' | 'complete') => {
    try { await action.mutateAsync({ id, action: a }); toast.success(a === 'activate' ? 'Đã kích hoạt' : a === 'cancel' ? 'Đã huỷ' : 'Đã hoàn thành'); } catch (raw) { toast.error(extractErrorMessage(raw)); }
  }, [action]);
  const cols: Column<OnboardingPlan>[] = [
    { header: 'Nhân viên', accessor: 'employee_id', className: 'font-mono text-xs w-32 truncate' },
    { header: 'Ngày bắt đầu', accessor: undefined, className: 'font-mono text-xs w-28', cell: (p) => formatDate(p.start_date) },
    { header: 'Trạng thái', accessor: undefined, className: 'w-20', cell: (p) => <Badge variant={sV[p.status]}>{sL[p.status]}</Badge> },
    { header: '', accessor: undefined, className: 'text-right w-20', cell: (p) => (
      <div className="flex justify-end gap-1">
        {p.status === 'draft' && <Button variant="ghost" size="sm" title="Kích hoạt" onClick={() => handleAction(p.id, 'activate')}><Play className="h-4 w-4 text-green-600" /></Button>}
        {p.status === 'active' && <Button variant="ghost" size="sm" title="Hoàn thành" onClick={() => handleAction(p.id, 'complete')}><CheckCircle className="h-4 w-4" /></Button>}
        {(p.status === 'draft' || p.status === 'active') && <Button variant="ghost" size="sm" title="Huỷ" onClick={() => handleAction(p.id, 'cancel')}><XCircle className="h-4 w-4 text-destructive" /></Button>}
      </div>
    )},
  ];
  return (<div className="space-y-4"><Button onClick={openCreate}><Plus className="h-4 w-4 mr-1" />Tạo kế hoạch</Button>
    <DataTable columns={cols} data={plans ?? []} isLoading={isLoading} rowKey="id" emptyMessage="Chưa có kế hoạch nào" />
    <Drawer open={open} onOpenChange={setOpen}><DrawerContent size="sm"><DrawerHeader><DrawerTitle>Tạo kế hoạch Onboarding</DrawerTitle><DrawerDescription>Nhập thông tin kế hoạch mới</DrawerDescription></DrawerHeader>
      <DrawerBody><form id="on-form" onSubmit={form.handleSubmit(onSubmit)} className="space-y-4">
        <div className="space-y-2"><Label htmlFor="oe">ID Nhân viên <span className="text-destructive">*</span></Label><Input id="oe" autoComplete="off" {...form.register('employee_id')} /></div>
        <div className="grid grid-cols-2 gap-3"><div className="space-y-2"><Label htmlFor="oc">ID Ứng viên</Label><Input id="oc" autoComplete="off" {...form.register('candidate_id')} /></div>
        <div className="space-y-2"><Label htmlFor="ot">Template</Label>
          <select id="ot" className="w-full rounded-md border bg-[hsl(var(--card))] px-2 py-1 text-[13px]" {...form.register('template_id')}>
            <option value="">Chọn template</option>{templates?.map(t => <option key={t.id} value={t.id}>{t.name}</option>)}</select></div></div>
        <div className="space-y-2"><Label htmlFor="os">Ngày bắt đầu <span className="text-destructive">*</span></Label><Input id="os" type="date" autoComplete="off" {...form.register('start_date')} /></div>
      </form></DrawerBody>
      <DrawerFooter><Button variant="ghost" onClick={() => setOpen(false)}>Hủy</Button><Button type="submit" form="on-form" disabled={create.isPending}>Tạo</Button></DrawerFooter>
    </DrawerContent></Drawer></div>);
}
