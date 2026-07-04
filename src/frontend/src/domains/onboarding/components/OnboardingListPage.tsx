'use client';
import { useState, useCallback } from 'react'; import { useForm } from 'react-hook-form'; import { zodResolver } from '@hookform/resolvers/zod'; import { z } from 'zod'; import { toast } from 'sonner'; import { Plus, Play, XCircle, CheckCircle } from 'lucide-react';
import { useOnboardingPlans, useCreateOnboardingPlan, useOnboardingPlanAction } from '@/domains/onboarding/hooks/useOnboarding';
import { useEmployees } from '@/domains/employee/hooks/useEmployees';
import type { OnboardingPlan } from '@/domains/onboarding/models/onboarding';
import { DataTable, type Column } from '@/shared/components/DataTable'; import { Drawer, DrawerContent, DrawerHeader, DrawerTitle, DrawerDescription, DrawerBody, DrawerFooter } from '@/shared/components/ui/drawer';
import { Button } from '@/shared/components/ui/button'; import { Label } from '@/shared/components/ui/label'; import { Badge } from '@/shared/components/ui/badge'; import { extractErrorMessage } from '@/core/errors/messages';
import { useDateFormatter } from '@/shared/hooks/useDateFormatter';

const statusL: Record<string,string> = { draft:'Nháp',active:'Đang thực hiện',completed:'Hoàn thành',cancelled:'Đã huỷ' };
const statusV: Record<string,'default'|'secondary'|'destructive'|'outline'> = { draft:'secondary',active:'default',completed:'outline',cancelled:'destructive' };
const schema = z.object({ employee_id: z.string().min(1,'Chọn nhân viên'), start_date: z.string().min(1,'Chọn ngày') });
type F = z.infer<typeof schema>;

export function OnboardingListPage() {
  const { formatDate } = useDateFormatter();
  const { data:plans, isLoading } = useOnboardingPlans(); const createPlan=useCreateOnboardingPlan(); const planAction=useOnboardingPlanAction();
  const { data: empData } = useEmployees();
  const employees = empData?.data ?? [];
  const [open,setOpen]=useState(false); const form=useForm<F>({resolver:zodResolver(schema),defaultValues:{employee_id:'',start_date:''}});
  const onSubmit=useCallback(async(v:F)=>{try{await createPlan.mutateAsync(v);toast.success('Tạo kế hoạch thành công');setOpen(false);form.reset();}catch(raw){toast.error(extractErrorMessage(raw));}},[createPlan,form]);
  const handleAction=useCallback(async(id:string,action:'activate'|'cancel'|'complete')=>{try{await planAction.mutateAsync({id,action});const m={activate:'Đã kích hoạt',cancel:'Đã huỷ',complete:'Đã hoàn thành'};toast.success(m[action]);}catch(raw){toast.error(extractErrorMessage(raw));}},[planAction]);
  const cols:Column<OnboardingPlan>[]=[
    {header:'NV',accessor:'employee_id',className:'font-mono text-xs w-36 truncate'},
    {header:'Ngày bắt đầu',accessor:undefined,cell:(p)=>formatDate(p.start_date),className:'font-mono text-xs w-28'},
    {header:'Trạng thái',accessor:undefined,className:'w-24',cell:(p)=> <Badge variant={statusV[p.status]}>{statusL[p.status]}</Badge>},
    {header:'Thao tác',accessor:undefined,className:'text-right w-24',cell:(p)=>(
      <div className="flex justify-end gap-1">
        {p.status==='draft'&&<Button variant="ghost" size="sm" title="Kích hoạt" onClick={()=>handleAction(p.id,'activate')}><Play className="h-4 w-4 text-green-600"/></Button>}
        {p.status==='active'&&<Button variant="ghost" size="sm" title="Hoàn thành" onClick={()=>handleAction(p.id,'complete')}><CheckCircle className="h-4 w-4"/></Button>}
        {(p.status==='draft'||p.status==='active')&&<Button variant="ghost" size="sm" title="Huỷ" onClick={()=>handleAction(p.id,'cancel')}><XCircle className="h-4 w-4 text-destructive"/></Button>}
      </div>
    )},
  ];
  return (<div className="space-y-4"><Button onClick={()=>{form.reset();setOpen(true);}}><Plus className="h-4 w-4 mr-1"/>Tạo kế hoạch</Button>
    <DataTable columns={cols} data={plans??[]} isLoading={isLoading} rowKey="id" emptyMessage="Chưa có kế hoạch nào"/>
    <Drawer open={open} onOpenChange={setOpen}><DrawerContent size="sm"><DrawerHeader><DrawerTitle>Tạo kế hoạch Onboarding</DrawerTitle><DrawerDescription>Chọn nhân viên và ngày bắt đầu</DrawerDescription></DrawerHeader>
      <DrawerBody><form id="ob-form" onSubmit={form.handleSubmit(onSubmit)} className="space-y-4">
        <div className="space-y-2"><Label htmlFor="eid">Nhân viên <span className="text-destructive">*</span></Label>
          <select id="eid" className="h-8 w-full rounded-md border bg-[hsl(var(--card))] px-2 py-1 text-[13px] outline-none focus:ring-2 focus:ring-primary" {...form.register('employee_id')}>
            <option value="">Chọn nhân viên</option>
            {employees.map(e=><option key={e.id} value={e.id}>{e.last_name??''} {e.first_name??''} ({e.employee_code})</option>)}
          </select>
        </div>
        <div className="space-y-2"><Label htmlFor="sd">Ngày bắt đầu <span className="text-destructive">*</span></Label><input id="sd" type="date" autoComplete="off" className="h-8 w-full rounded-md border bg-[hsl(var(--card))] px-2 py-1 text-[13px] outline-none focus:ring-2 focus:ring-primary" {...form.register('start_date')}/></div>
      </form></DrawerBody>
      <DrawerFooter><Button variant="ghost" type="button" onClick={()=>setOpen(false)}>Hủy</Button><Button type="submit" form="ob-form" disabled={createPlan.isPending}>Tạo</Button></DrawerFooter>
    </DrawerContent></Drawer></div>);
}
