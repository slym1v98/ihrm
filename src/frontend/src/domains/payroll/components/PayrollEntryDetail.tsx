'use client';
import { useState, useCallback } from 'react'; import { useForm } from 'react-hook-form'; import { zodResolver } from '@hookform/resolvers/zod'; import { z } from 'zod'; import { toast } from 'sonner';
import { useEntry, useAdjustments, useCreateAdjustment, useApproveAdjustment, useRejectAdjustment } from '@/domains/payroll/hooks/usePayroll';
import type { PayrollComponent, PayrollEntry } from '@/domains/payroll/models/payroll';
import { Drawer, DrawerContent, DrawerHeader, DrawerTitle, DrawerDescription, DrawerBody, DrawerFooter } from '@/shared/components/ui/drawer';
import { Button } from '@/shared/components/ui/button'; import { Input } from '@/shared/components/ui/input'; import { Label } from '@/shared/components/ui/label'; import { Badge } from '@/shared/components/ui/badge'; import { extractErrorMessage } from '@/core/errors/messages';
import { useMoneyFormatter } from '@/shared/hooks/useMoneyFormatter'; import { CheckCircle, XCircle } from 'lucide-react';
import { useEmployeeDisplayName } from '@/shared/hooks/useEmployeeDisplayName';
import { http } from '@/core/http/client';

const adjSchema=z.object({component_id:z.string().min(1),amount:z.coerce.number({invalid_type_error:'Nhập số'}),reason:z.string().min(5)});

export function PayrollEntryDetail({entryId,open,onOpenChange,components,initialEntry}:{entryId:string|null;open:boolean;onOpenChange:(o:boolean)=>void;components:PayrollComponent[];initialEntry?:PayrollEntry|null}) {
  const {data}=useEntry(entryId); const entry=data??initialEntry; const {data:adjustments}=useAdjustments(entryId); const createAdj=useCreateAdjustment(); const approveAdj=useApproveAdjustment(); const rejectAdj=useRejectAdjustment();
  const {formatMoney}=useMoneyFormatter();
  const {getDisplayName}=useEmployeeDisplayName();
  const [adjOpen,setAdjOpen]=useState(false);
  const f=useForm<z.infer<typeof adjSchema>>({resolver:zodResolver(adjSchema),defaultValues:{component_id:'',amount:0,reason:''}});

  const handleReview=useCallback(async()=>{if(!entryId)return;try{await http.post(`/payroll/entries/${entryId}/review`);toast.success('Đã duyệt entry');}catch(raw){toast.error(extractErrorMessage(raw));}},[entryId]);
  const handleAdj=useCallback(async(v:z.infer<typeof adjSchema>)=>{if(!entryId)return;try{await createAdj.mutateAsync({entryId,...v});toast.success('Đã gửi điều chỉnh');setAdjOpen(false);f.reset();}catch(raw){toast.error(extractErrorMessage(raw));}},[entryId,createAdj,f]);

  if(!entry)return null;
  const snap=entry.contract_snapshot as any;
  const catLabel:Record<string,string>={base:'Lương gốc',allowance:'Phụ cấp',bonus:'Thưởng',penalty:'Phạt',overtime:'Tăng ca',deduction:'Khấu trừ',insurance:'BH',tax:'Thuế',net:'Thực nhận'};
  const adjStatusL:Record<string,string>={pending:'Chờ duyệt',approved:'Đã duyệt',rejected:'Từ chối'};
  const adjStatusV:Record<string,'default'|'secondary'|'destructive'>={pending:'secondary',approved:'default',rejected:'destructive'};

  return (<Drawer open={open} onOpenChange={onOpenChange}><DrawerContent size="sm"><DrawerHeader><DrawerTitle>Chi tiết lương</DrawerTitle><DrawerDescription>NV: {getDisplayName(entry.employee_id)} · Lương cơ bản: {snap?.base_salary?formatMoney(snap.base_salary):'—'}</DrawerDescription></DrawerHeader>
    <DrawerBody className="space-y-4">
      <div className="rounded-lg border"><table className="w-full text-[13px]"><thead><tr className="border-b text-muted-foreground"><th className="text-left px-3 py-2 font-medium">Khoản</th><th className="text-right px-3 py-2 font-medium">Loại</th><th className="text-right px-3 py-2 font-medium">Số tiền</th><th className="text-right px-3 py-2 font-medium">Ghi chú</th></tr></thead>
        <tbody>{entry.lines?.map((l,i)=><tr key={i} className="border-b last:border-0"><td className="px-3 py-1.5">{components.find(c=>c.id===l.component_id)?.name??l.component_id}</td>
          <td className="px-3 py-1.5 text-right text-muted-foreground">{catLabel[l.category]??l.category}</td>
          <td className="px-3 py-1.5 text-right">{formatMoney(l.amount)}</td>
          <td className="px-3 py-1.5 text-right text-muted-foreground">{l.calculation_note??'—'}</td></tr>)}
        </tbody>
        <tfoot><tr className="border-t font-semibold"><td className="px-3 py-2">Tổng thu nhập</td><td></td><td className="text-right px-3 py-2">{formatMoney(entry.gross_amount)}</td><td></td></tr>
        <tr className="text-muted-foreground"><td className="px-3 py-1">Khấu trừ</td><td></td><td className="text-right px-3 py-1">{formatMoney(entry.deduction_amount)}</td><td></td></tr>
        <tr className="border-t font-bold text-base"><td className="px-3 py-2">Thực nhận</td><td></td><td className="text-right px-3 py-2">{formatMoney(entry.net_amount)}</td><td></td></tr></tfoot>
      </table></div>

      <div><div className="flex items-center justify-between mb-2"><span className="text-sm font-medium">Điều chỉnh</span><Button size="sm" variant="ghost" onClick={()=>{f.reset();setAdjOpen(true)}}>+ Thêm</Button></div>
      {(!adjustments||adjustments.length===0)&&<p className="text-xs text-muted-foreground">Chưa có điều chỉnh.</p>}
      {adjustments?.map(a=><div key={a.id} className="flex items-center justify-between py-1 border-b text-[13px]"><span>{formatMoney(a.amount)} · {a.reason}</span>
        <div className="flex items-center gap-2"><Badge variant={adjStatusV[a.status]??'secondary'}>{adjStatusL[a.status]??a.status}</Badge>
          {a.status==='pending'&&<><Button variant="ghost" size="sm" onClick={async()=>{try{await approveAdj.mutateAsync(a.id);toast.success('Đã duyệt');}catch(raw){toast.error(extractErrorMessage(raw));}}}><CheckCircle className="h-3 w-3 text-green-600"/></Button>
          <Button variant="ghost" size="sm" onClick={async()=>{try{await rejectAdj.mutateAsync(a.id);toast.success('Đã từ chối');}catch(raw){toast.error(extractErrorMessage(raw));}}}><XCircle className="h-3 w-3 text-destructive"/></Button></>}</div></div>)}
      {adjOpen&&<form onSubmit={f.handleSubmit(handleAdj)} className="space-y-2 pt-2 border-t"><div><Label>Khoản</Label><select className="h-8 w-full rounded-md border px-2 text-[13px]" {...f.register('component_id')}>
        {components.map(c=><option key={c.id} value={c.id}>{c.name}</option>)}</select></div>
        <div><Label>Số tiền</Label><Input type="number" autoComplete="off"{...f.register('amount')}/></div>
        <div><Label>Lý do</Label><Input autoComplete="off"{...f.register('reason')}/></div>
        {f.formState.errors.amount&&<p className="text-xs text-destructive">{f.formState.errors.amount.message}</p>}
        <div className="flex gap-2"><Button type="submit" size="sm" disabled={createAdj.isPending}>Gửi</Button><Button type="button" size="sm" variant="ghost" onClick={()=>setAdjOpen(false)}>Hủy</Button></div>
      </form>}</div>
    </DrawerBody>
    <DrawerFooter>
      {entry.status==='calculated'&&<Button onClick={handleReview}><CheckCircle className="h-4 w-4 mr-1"/>Duyệt</Button>}
      {entry.status==='reviewed'&&<Badge variant="default">Đã duyệt</Badge>}
      {entry.status==='error'&&<Badge variant="destructive">Lỗi: {entry.error_message}</Badge>}
      <Button variant="ghost" onClick={()=>onOpenChange(false)}>Đóng</Button>
    </DrawerFooter>
  </DrawerContent></Drawer>);
}
