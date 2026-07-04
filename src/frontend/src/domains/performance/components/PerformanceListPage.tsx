'use client';
import { ReviewSection } from '@/domains/performance/components/ReviewSection';
import { useState, useCallback } from 'react'; import { useForm } from 'react-hook-form'; import { zodResolver } from '@hookform/resolvers/zod'; import { z } from 'zod'; import { toast } from 'sonner'; import { Plus, Play, XCircle, CheckCircle, Target } from 'lucide-react';
import { usePerformanceCycles, useCreateCycle, useCycleAction } from '@/domains/performance/hooks/usePerformance';
import type { PerformanceCycle } from '@/domains/performance/models/performance';
import { DataTable, type Column } from '@/shared/components/DataTable'; import { Drawer, DrawerContent, DrawerHeader, DrawerTitle, DrawerDescription, DrawerBody, DrawerFooter } from '@/shared/components/ui/drawer';
import { Button } from '@/shared/components/ui/button'; import { Input } from '@/shared/components/ui/input'; import { Label } from '@/shared/components/ui/label'; import { Badge } from '@/shared/components/ui/badge'; import { extractErrorMessage } from '@/core/errors/messages';
import { useDateFormatter } from '@/shared/hooks/useDateFormatter';

const statusL: Record<string,string>={draft:'Nháp',active:'Đang diễn ra',completed:'Hoàn thành',cancelled:'Đã huỷ'};
const statusV: Record<string,'default'|'secondary'|'destructive'|'outline'>={draft:'secondary',active:'default',completed:'outline',cancelled:'destructive'};
const schema=z.object({code:z.string().min(2),name:z.string().min(1),start_date:z.string().min(1),end_date:z.string().min(1),description:z.string().optional()});type F=z.infer<typeof schema>;

export function PerformanceListPage() {
  const { formatDate } = useDateFormatter();
  const {data:cycles,isLoading}=usePerformanceCycles(); const create=useCreateCycle(); const action=useCycleAction();
  const [open,setOpen]=useState(false); const form=useForm<F>({resolver:zodResolver(schema),defaultValues:{code:'',name:'',start_date:'',end_date:'',description:''}});
  const openCreate=useCallback(()=>{form.reset({code:'',name:'',start_date:'',end_date:'',description:''});setOpen(true);},[form]);
  const onSubmit=useCallback(async(v:F)=>{try{await create.mutateAsync(v);toast.success('Tạo kỳ đánh giá thành công');setOpen(false);}catch(raw){toast.error(extractErrorMessage(raw));}},[create]);
  const handleAction=useCallback(async(id:string,a:'activate'|'complete'|'cancel')=>{try{await action.mutateAsync({id,action:a});const m={activate:'Đã kích hoạt',complete:'Đã hoàn thành',cancel:'Đã huỷ'};toast.success(m[a]);}catch(raw){toast.error(extractErrorMessage(raw));}},[action]);
  const cols:Column<PerformanceCycle>[]=[
    {header:'Mã',accessor:'code',className:'font-mono text-xs w-24'},{header:'Tên kỳ',accessor:'name'},
    {header:'Bắt đầu',accessor:undefined,cell:(c)=>formatDate(c.start_date),className:'font-mono text-xs w-28'},{header:'Kết thúc',accessor:undefined,cell:(c)=>formatDate(c.end_date),className:'font-mono text-xs w-28'},
    {header:'Trạng thái',accessor:undefined,className:'w-24',cell:(c)=><Badge variant={statusV[c.status]}>{statusL[c.status]}</Badge>},
    {header:'Thao tác',accessor:undefined,className:'text-right w-24',cell:(c)=>(
      <div className="flex justify-end gap-1">
        {c.status==='draft'&&<Button variant="ghost" size="sm" title="Kích hoạt"onClick={()=>handleAction(c.id,'activate')}><Play className="h-4 w-4 text-green-600"/></Button>}
        {c.status==='active'&&<Button variant="ghost" size="sm" title="Hoàn thành"onClick={()=>handleAction(c.id,'complete')}><CheckCircle className="h-4 w-4"/></Button>}
        {(c.status==='draft'||c.status==='active')&&<Button variant="ghost" size="sm" title="Huỷ"onClick={()=>handleAction(c.id,'cancel')}><XCircle className="h-4 w-4 text-destructive"/></Button>}
      </div>
    )},
  ];
  return (<div className="space-y-4"><Button onClick={openCreate}><Plus className="h-4 w-4 mr-1"/>Tạo kỳ đánh giá</Button>
    <DataTable columns={cols} data={cycles??[]} isLoading={isLoading} rowKey="id" emptyMessage="Chưa có kỳ đánh giá nào"/>
    <Drawer open={open} onOpenChange={setOpen}><DrawerContent size="sm"><DrawerHeader><DrawerTitle>Tạo kỳ đánh giá</DrawerTitle><DrawerDescription>Nhập thông tin kỳ đánh giá mới</DrawerDescription></DrawerHeader>
      <DrawerBody><form id="pf-form" onSubmit={form.handleSubmit(onSubmit)} className="space-y-4">
        <div className="space-y-2"><Label htmlFor="code">Mã kỳ <span className="text-destructive">*</span></Label><Input id="code" autoComplete="off"{...form.register('code')}/></div>
        <div className="space-y-2"><Label htmlFor="name">Tên kỳ <span className="text-destructive">*</span></Label><Input id="name" autoComplete="off"{...form.register('name')}/></div>
        <div className="grid grid-cols-2 gap-3"><div className="space-y-2"><Label htmlFor="sd">Bắt đầu <span className="text-destructive">*</span></Label><Input id="sd" type="date" autoComplete="off"{...form.register('start_date')}/></div>
        <div className="space-y-2"><Label htmlFor="ed">Kết thúc <span className="text-destructive">*</span></Label><Input id="ed" type="date" autoComplete="off"{...form.register('end_date')}/></div></div>
        <div className="space-y-2"><Label htmlFor="desc">Mô tả</Label><textarea id="desc" className="h-20 w-full rounded-md border bg-[hsl(var(--card))] px-2 py-1 text-[13px] outline-none focus:ring-2 focus:ring-primary resize-none"{...form.register('description')}/></div>
      </form></DrawerBody>
      <DrawerFooter><Button variant="ghost" type="button" onClick={()=>setOpen(false)}>Hủy</Button><Button type="submit" form="pf-form" disabled={create.isPending}>Tạo</Button></DrawerFooter>
    </DrawerContent></Drawer></div>);
}
