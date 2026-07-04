'use client';
import { useState, useCallback } from 'react'; import { useForm } from 'react-hook-form'; import { zodResolver } from '@hookform/resolvers/zod'; import { z } from 'zod'; import { toast } from 'sonner'; import { Plus, Send, CheckCircle, XCircle } from 'lucide-react';
import { useOffboardingRequests, useCreateOffboardingRequest, useOffboardingAction } from '@/domains/offboarding/hooks/useOffboarding';
import { useEmployees } from '@/domains/employee/hooks/useEmployees';
import type { OffboardingRequest } from '@/domains/offboarding/models/offboarding';
import { DataTable, type Column } from '@/shared/components/DataTable'; import { Drawer, DrawerContent, DrawerHeader, DrawerTitle, DrawerDescription, DrawerBody, DrawerFooter } from '@/shared/components/ui/drawer';
import { Button } from '@/shared/components/ui/button'; import { Label } from '@/shared/components/ui/label'; import { Badge } from '@/shared/components/ui/badge'; import { extractErrorMessage } from '@/core/errors/messages';

const statusL: Record<string,string>={draft:'Nháp',submitted:'Chờ duyệt',approved:'Đã duyệt',rejected:'Từ chối'};
const statusV: Record<string,'default'|'secondary'|'destructive'|'outline'>={draft:'secondary',submitted:'default',approved:'outline',rejected:'destructive'};
const schema=z.object({employee_id:z.string().min(1,'Chọn nhân viên'),reason:z.string().optional()});type F=z.infer<typeof schema>;

export function OffboardingListPage() {
  const {data:requests,isLoading}=useOffboardingRequests(); const create=useCreateOffboardingRequest(); const action=useOffboardingAction();
  const { data: empData } = useEmployees(); const employees = empData?.data ?? [];
  const [open,setOpen]=useState(false); const form=useForm<F>({resolver:zodResolver(schema),defaultValues:{employee_id:'',reason:''}});
  const onSubmit=useCallback(async(v:F)=>{try{await create.mutateAsync(v);toast.success('Tạo yêu cầu thành công');setOpen(false);form.reset();}catch(raw){toast.error(extractErrorMessage(raw));}},[create,form]);
  const handleAction=useCallback(async(id:string,a:'submit'|'approve'|'reject')=>{try{await action.mutateAsync({id,action:a});const m={submit:'Đã gửi duyệt',approve:'Đã duyệt',reject:'Đã từ chối'};toast.success(m[a]);}catch(raw){toast.error(extractErrorMessage(raw));}},[action]);
  const cols:Column<OffboardingRequest>[]=[
    {header:'NV',accessor:'employee_id',className:'font-mono text-xs w-36 truncate'},
    {header:'Lý do',accessor:'reason',cell:(r)=>r.reason??'—',className:'max-w-xs truncate text-muted-foreground'},
    {header:'Trạng thái',accessor:undefined,className:'w-24',cell:(r)=><Badge variant={statusV[r.status]}>{statusL[r.status]}</Badge>},
    {header:'Thao tác',accessor:undefined,className:'text-right w-28',cell:(r)=>(
      <div className="flex justify-end gap-1">
        {r.status==='draft'&&<Button variant="ghost" size="sm" title="Gửi duyệt" onClick={()=>handleAction(r.id,'submit')}><Send className="h-4 w-4"/></Button>}
        {r.status==='submitted'&&<><Button variant="ghost" size="sm" title="Duyệt" onClick={()=>handleAction(r.id,'approve')}><CheckCircle className="h-4 w-4 text-green-600"/></Button>
        <Button variant="ghost" size="sm" title="Từ chối" onClick={()=>handleAction(r.id,'reject')}><XCircle className="h-4 w-4 text-destructive"/></Button></>}
      </div>
    )},
  ];
  return (<div className="space-y-4"><Button onClick={()=>{form.reset();setOpen(true);}}><Plus className="h-4 w-4 mr-1"/>Tạo yêu cầu</Button>
    <DataTable columns={cols} data={requests??[]} isLoading={isLoading} rowKey="id" emptyMessage="Chưa có yêu cầu nào"/>
    <Drawer open={open} onOpenChange={setOpen}><DrawerContent size="sm"><DrawerHeader><DrawerTitle>Tạo yêu cầu Offboarding</DrawerTitle><DrawerDescription>Chọn nhân viên nghỉ việc</DrawerDescription></DrawerHeader>
      <DrawerBody><form id="of-form" onSubmit={form.handleSubmit(onSubmit)} className="space-y-4">
        <div className="space-y-2"><Label htmlFor="eid">Nhân viên <span className="text-destructive">*</span></Label>
          <select id="eid" className="h-8 w-full rounded-md border bg-[hsl(var(--card))] px-2 py-1 text-[13px] outline-none focus:ring-2 focus:ring-primary"{...form.register('employee_id')}>
            <option value="">Chọn nhân viên</option>
            {employees.map(e=><option key={e.id} value={e.id}>{e.last_name??''} {e.first_name??''} ({e.employee_code})</option>)}
          </select>
        </div>
        <div className="space-y-2"><Label htmlFor="reason">Lý do</Label>
          <textarea id="reason" className="h-20 w-full rounded-md border bg-[hsl(var(--card))] px-2 py-1 text-[13px] outline-none focus:ring-2 focus:ring-primary resize-none"{...form.register('reason')}/>
        </div>
      </form></DrawerBody>
      <DrawerFooter><Button variant="ghost" onClick={()=>setOpen(false)}>Hủy</Button><Button type="submit" form="of-form" disabled={create.isPending}>Tạo</Button></DrawerFooter>
    </DrawerContent></Drawer></div>);
}
