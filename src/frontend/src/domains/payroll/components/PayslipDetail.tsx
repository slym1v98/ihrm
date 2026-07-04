'use client';
import { useQuery } from '@tanstack/react-query'; import { toast } from 'sonner';
import { http } from '@/core/http/client';
import type { Payslip } from '@/domains/payroll/models/payroll';
import { Drawer, DrawerContent, DrawerHeader, DrawerTitle, DrawerDescription, DrawerBody, DrawerFooter } from '@/shared/components/ui/drawer';
import { Button } from '@/shared/components/ui/button'; import { Badge } from '@/shared/components/ui/badge';
import { useMoneyFormatter } from '@/shared/hooks/useMoneyFormatter'; import { useDateFormatter } from '@/shared/hooks/useDateFormatter'; import { Download } from 'lucide-react';

export function PayslipDetail({payslipId,open,onOpenChange}:{payslipId:string|null;open:boolean;onOpenChange:(o:boolean)=>void}) {
  const {data}=useQuery({queryKey:['payslip',payslipId],queryFn:()=>http.get<{data:Payslip}>(`/payroll/payslips/${payslipId}`).then(r=>r.data.data),enabled:!!payslipId});
  const {formatMoney}=useMoneyFormatter(); const {formatDateTime}=useDateFormatter();
  const ps=data;
  const lines:any[] = ps?.payload?.lines??[];
  const catLabel:Record<string,string>={base:'Lương gốc',allowance:'Phụ cấp',bonus:'Thưởng',penalty:'Phạt',overtime:'Tăng ca',deduction:'Khấu trừ',insurance:'BH',tax:'Thuế',net:'Thực nhận'};
  return (<Drawer open={open} onOpenChange={onOpenChange}><DrawerContent size="sm"><DrawerHeader><DrawerTitle>Phiếu lương</DrawerTitle><DrawerDescription>Trạng thái: <Badge variant={ps?.status==='published'?'default':'secondary'}>{ps?.status==='published'?'Đã phát hành':'Nháp'}</Badge> · {formatDateTime(ps?.published_at)}</DrawerDescription></DrawerHeader>
    <DrawerBody>
      {lines.length>0&&<div className="rounded-lg border"><table className="w-full text-[13px]"><thead><tr className="border-b text-muted-foreground"><th className="text-left px-3 py-2 font-medium">Khoản</th><th className="text-right px-3 py-2 font-medium">Số tiền</th></tr></thead>
        <tbody>{lines.map((l:any,i:number)=><tr key={i} className="border-b last:border-0"><td className="px-3 py-1.5">{catLabel[l.category]??l.category}</td><td className="px-3 py-1.5 text-right">{formatMoney(l.amount)}</td></tr>)}
        </tbody>
        <tfoot><tr className="border-t font-semibold"><td className="px-3 py-2">Tổng thu nhập</td><td className="text-right px-3 py-2">{formatMoney(ps?.gross)}</td></tr>
        <tr className="text-muted-foreground"><td className="px-3 py-1">Khấu trừ</td><td className="text-right px-3 py-1">{formatMoney(ps?.deductions)}</td></tr>
        <tr className="border-t font-bold text-base"><td className="px-3 py-2">Thực nhận</td><td className="text-right px-3 py-2">{formatMoney(ps?.net)}</td></tr></tfoot>
      </table></div>}
    </DrawerBody>
    <DrawerFooter>
      {ps?.status==='published'&&<Button onClick={()=>window.open(`/api/v1/payroll/payslips/${payslipId}/download`,'_blank')}><Download className="h-4 w-4 mr-1"/>Tải PDF</Button>}
      <Button variant="ghost" onClick={()=>onOpenChange(false)}>Đóng</Button>
    </DrawerFooter>
  </DrawerContent></Drawer>);
}
