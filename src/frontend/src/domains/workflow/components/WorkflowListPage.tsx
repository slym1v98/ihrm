'use client';
import { useState, useCallback } from 'react'; import { useForm } from 'react-hook-form'; import { zodResolver } from '@hookform/resolvers/zod'; import { z } from 'zod'; import { toast } from 'sonner'; import { Plus, CheckCircle, XCircle, Ban } from 'lucide-react';
import { useWorkflowRequests, useWorkflowTemplates, useStartWorkflow, useWorkflowAction } from '@/domains/workflow/hooks/useWorkflow';
import type { WorkflowRequest } from '@/domains/workflow/models/workflow';
import { DataTable, type Column } from '@/shared/components/DataTable'; import { Drawer, DrawerContent, DrawerHeader, DrawerTitle, DrawerDescription, DrawerBody, DrawerFooter } from '@/shared/components/ui/drawer';
import { Button } from '@/shared/components/ui/button'; import { Input } from '@/shared/components/ui/input'; import { Label } from '@/shared/components/ui/label'; import { Badge } from '@/shared/components/ui/badge'; import { extractErrorMessage } from '@/core/errors/messages';

const statusL: Record<string,string>={pending:'Chờ duyệt',approved:'Đã duyệt',rejected:'Từ chối',cancelled:'Đã huỷ',returned:'Trả lại'};
const statusV: Record<string,'default'|'secondary'|'destructive'|'outline'>={pending:'secondary',approved:'default',rejected:'destructive',cancelled:'outline',returned:'outline'};
const schema=z.object({workflow_template_id:z.string().min(1),subject_type:z.string().min(1),subject_id:z.string().min(1)});
type F=z.infer<typeof schema>;

export function WorkflowListPage() {
  const {data:requests,isLoading}=useWorkflowRequests(); const {data:templates}=useWorkflowTemplates(); const start=useStartWorkflow(); const act=useWorkflowAction();
  const [open,setOpen]=useState(false); const form=useForm<F>({resolver:zodResolver(schema),defaultValues:{workflow_template_id:'',subject_type:'',subject_id:''}});
  const onSubmit=useCallback(async(v:F)=>{try{await start.mutateAsync(v);toast.success('Khởi tạo yêu cầu thành công');setOpen(false);form.reset();}catch(raw){toast.error(extractErrorMessage(raw));}},[start,form]);
  const handleAction=useCallback(async(id:string,a:'approve'|'reject'|'cancel')=>{try{await act.mutateAsync({id,action:a});const m={approve:'Đã duyệt',reject:'Đã từ chối',cancel:'Đã huỷ'};toast.success(m[a]);}catch(raw){toast.error(extractErrorMessage(raw));}},[act]);
  const cols:Column<WorkflowRequest>[]=[
    {header:'Template',accessor:'workflow_template_id',className:'font-mono text-xs w-36 truncate'},
    {header:'Đối tượng',accessor:'subject_type',className:'w-24'},{header:'ID',accessor:'subject_id',className:'font-mono text-xs w-36 truncate'},
    {header:'Trạng thái',accessor:undefined,className:'w-24',cell:(r)=><Badge variant={statusV[r.status]}>{statusL[r.status]}</Badge>},
    {header:'',accessor:undefined,className:'text-right w-28',cell:(r)=>(
      <div className="flex justify-end gap-1">
        {r.status==='pending'&&<><Button variant="ghost" size="sm" title="Duyệt" onClick={()=>handleAction(r.id,'approve')}><CheckCircle className="h-4 w-4 text-green-600"/></Button>
        <Button variant="ghost" size="sm" title="Từ chối" onClick={()=>handleAction(r.id,'reject')}><XCircle className="h-4 w-4 text-destructive"/></Button>
        <Button variant="ghost" size="sm" title="Huỷ" onClick={()=>handleAction(r.id,'cancel')}><Ban className="h-4 w-4 text-muted-foreground"/></Button></>}
      </div>
    )},
  ];
  return (<div className="space-y-4"><Button onClick={()=>{form.reset();setOpen(true);}}><Plus className="h-4 w-4 mr-1"/>Tạo yêu cầu</Button>
    <DataTable columns={cols} data={requests??[]} isLoading={isLoading} rowKey="id" emptyMessage="Chưa có yêu cầu nào"/>
    <Drawer open={open} onOpenChange={setOpen}><DrawerContent size="sm"><DrawerHeader><DrawerTitle>Khởi tạo yêu cầu duyệt</DrawerTitle><DrawerDescription>Chọn template và đối tượng</DrawerDescription></DrawerHeader>
      <DrawerBody><form id="wf-form" onSubmit={form.handleSubmit(onSubmit)} className="space-y-4">
        <div className="space-y-2"><Label>Template <span className="text-destructive">*</span></Label>
          <select className="h-8 w-full rounded-md border bg-[hsl(var(--card))] px-2 py-1 text-[13px] outline-none focus:ring-2 focus:ring-primary"{...form.register('workflow_template_id')}>
            <option value="">Chọn template</option>{templates?.map(t=><option key={t.id} value={t.id}>{t.name}</option>)}
          </select></div>
        <div className="space-y-2"><Label htmlFor="st">Loại đối tượng <span className="text-destructive">*</span></Label><Input id="st" autoComplete="off"{...form.register('subject_type')} placeholder="VD: leave_request"/></div>
        <div className="space-y-2"><Label htmlFor="si">ID đối tượng <span className="text-destructive">*</span></Label><Input id="si" autoComplete="off"{...form.register('subject_id')}/></div>
      </form></DrawerBody>
      <DrawerFooter><Button variant="ghost" onClick={()=>setOpen(false)}>Hủy</Button><Button type="submit" form="wf-form" disabled={start.isPending}>Tạo</Button></DrawerFooter>
    </DrawerContent></Drawer></div>);
}
