'use client';
import { useState, useCallback } from 'react'; import { useForm } from 'react-hook-form'; import { zodResolver } from '@hookform/resolvers/zod'; import { z } from 'zod'; import { toast } from 'sonner'; import { Plus, CheckCircle } from 'lucide-react';
import { useReviews, useSubmitReview, useFinalizeReview } from '@/domains/performance/hooks/usePerformance';
import type { Review } from '@/domains/performance/models/performance';
import { DataTable, type Column } from '@/shared/components/DataTable'; import { Drawer, DrawerContent, DrawerHeader, DrawerTitle, DrawerDescription, DrawerBody, DrawerFooter } from '@/shared/components/ui/drawer';
import { Button } from '@/shared/components/ui/button'; import { Input } from '@/shared/components/ui/input'; import { Label } from '@/shared/components/ui/label'; import { Badge } from '@/shared/components/ui/badge'; import { extractErrorMessage } from '@/core/errors/messages';

const statusL: Record<string,string>={draft:'Nháp',self_submitted:'Đã tự đánh giá',manager_submitted:'Manager đã duyệt',hr_submitted:'HR đã duyệt',finalized:'Hoàn thành'};
const statusV: Record<string,'default'|'secondary'|'destructive'|'outline'>={draft:'secondary',self_submitted:'outline',manager_submitted:'default',hr_submitted:'outline',finalized:'default'};
const fSchema=z.object({score:z.coerce.number().min(0).max(100)});type FF=z.infer<typeof fSchema>;

export function ReviewSection() {
  const {data:reviews,isLoading}=useReviews(); const submitR=useSubmitReview(); const finalizeR=useFinalizeReview();
  const [finalOpen,setFinalOpen]=useState<Review|null>(null);
  const fForm=useForm<FF>({resolver:zodResolver(fSchema),defaultValues:{score:0}});
  const onFinalize=useCallback(async(v:FF)=>{if(!finalOpen)return;try{await finalizeR.mutateAsync({id:finalOpen.id,finalScore:v.score});toast.success('Đã hoàn thành đánh giá');setFinalOpen(null);}catch(raw){toast.error(extractErrorMessage(raw));}},[finalizeR,finalOpen]);
  const handleSubmit=useCallback(async(id:string,role:'self'|'manager'|'hr')=>{try{await submitR.mutateAsync({id,role,assessment:{}});toast.success('Đã nộp đánh giá');}catch(raw){toast.error(extractErrorMessage(raw));}},[submitR]);

  const cols:Column<Review>[]=[
    {header:'NV',accessor:'employee_id',className:'font-mono text-xs w-36 truncate'},
    {header:'Kỳ',accessor:'cycle_id',className:'font-mono text-xs w-36 truncate'},
    {header:'Điểm cuối',accessor:undefined,className:'text-right w-20',cell:(r)=>r.final_score!=null?r.final_score.toFixed(1):'—'},
    {header:'Trạng thái',accessor:undefined,className:'w-36',cell:(r)=><Badge variant={statusV[r.status]??'secondary'}>{statusL[r.status]??r.status}</Badge>},
    {header:'',accessor:undefined,className:'text-right w-36',cell:(r)=>(
      <div className="flex justify-end gap-1">
        {r.status==='draft'&&<Button variant="ghost" size="sm" title="Tự đánh giá" onClick={()=>handleSubmit(r.id,'self')}>Self</Button>}
        {r.status==='self_submitted'&&<Button variant="ghost" size="sm" title="Manager duyệt" onClick={()=>handleSubmit(r.id,'manager')}>Mgr</Button>}
        {r.status==='manager_submitted'&&<Button variant="ghost" size="sm" title="HR duyệt" onClick={()=>handleSubmit(r.id,'hr')}>HR</Button>}
        {r.status==='hr_submitted'&&<Button variant="ghost" size="sm" title="Hoàn thành" onClick={()=>{fForm.reset({score:0});setFinalOpen(r);}}><CheckCircle className="h-4 w-4"/></Button>}
      </div>
    )},
  ];

  return (<div className="space-y-4">
    <DataTable columns={cols} data={reviews??[]} isLoading={isLoading} rowKey="id" emptyMessage="Chưa có đánh giá nào"/>
    <Drawer open={!!finalOpen} onOpenChange={(o)=>{if(!o)setFinalOpen(null);}}>
      <DrawerContent size="sm"><DrawerHeader><DrawerTitle>Hoàn thành đánh giá</DrawerTitle><DrawerDescription>Nhập điểm cuối cùng (0-100)</DrawerDescription></DrawerHeader>
      <DrawerBody><form id="fin-form" onSubmit={fForm.handleSubmit(onFinalize)} className="space-y-4">
        <div className="space-y-2"><Label>Điểm cuối <span className="text-destructive">*</span></Label>
        <Input type="number" step="0.1" autoComplete="off"{...fForm.register('score')}/></div>
      </form></DrawerBody>
      <DrawerFooter><Button variant="ghost" onClick={()=>setFinalOpen(null)}>Hủy</Button><Button type="submit" form="fin-form" disabled={finalizeR.isPending}>Xác nhận</Button></DrawerFooter>
    </DrawerContent></Drawer></div>);
}
