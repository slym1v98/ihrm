'use client';
import { useState, useCallback } from 'react'; import { useForm } from 'react-hook-form'; import { zodResolver } from '@hookform/resolvers/zod'; import { z } from 'zod'; import { toast } from 'sonner';
import { usePayrollPeriods, useCreatePeriod, usePayrollAction, useStartRun, usePayrollComponents, useCreateComponent, useDeleteComponent, usePayslips, usePublishPayslips, usePeriodSummary, usePeriodEntries } from '@/domains/payroll/hooks/usePayroll';
import type { PayrollComponent, PayrollPeriod, Payslip, PayrollEntry } from '@/domains/payroll/models/payroll';
import { DataTable, type Column } from '@/shared/components/DataTable'; import { Drawer, DrawerContent, DrawerHeader, DrawerTitle, DrawerDescription, DrawerBody, DrawerFooter } from '@/shared/components/ui/drawer';
import { Button } from '@/shared/components/ui/button'; import { Input } from '@/shared/components/ui/input'; import { Label } from '@/shared/components/ui/label'; import { Badge } from '@/shared/components/ui/badge'; import { extractErrorMessage } from '@/core/errors/messages';
import { Plus, Lock, Unlock, Play, CheckCircle, XCircle, Trash2, FileText } from 'lucide-react'; import { useDateFormatter } from '@/shared/hooks/useDateFormatter'; import { useMoneyFormatter } from '@/shared/hooks/useMoneyFormatter';
import { useEmployeeDisplayName } from '@/shared/hooks/useEmployeeDisplayName';
import { PayrollEntryDetail } from '@/domains/payroll/components/PayrollEntryDetail'; import { PayslipDetail } from '@/domains/payroll/components/PayslipDetail';

const statusL:Record<string,string>={draft:'Nháp',pending_approval:'Chờ duyệt',approved:'Đã duyệt',locked:'Đã khoá',rejected:'Từ chối'};
const statusV:Record<string,'default'|'secondary'|'destructive'|'outline'>={draft:'secondary',pending_approval:'default',approved:'outline',locked:'default',rejected:'destructive'};
const periodSchema=z.object({period_code:z.string().min(2),start_date:z.string().min(1),end_date:z.string().min(1),cutoff_date:z.string().min(1)});type PF=z.infer<typeof periodSchema>;
const compSchema=z.object({code:z.string().min(2),name:z.string().min(1),category:z.enum(['base','allowance','bonus','penalty','overtime','deduction','insurance','tax','net']),calculation_type:z.enum(['fixed_amount','percent_of_component','manual_entry']),default_amount:z.coerce.number().optional(),taxable:z.coerce.boolean().optional()});type CF2=z.infer<typeof compSchema>;
const categoryL:Record<string,string>={base:'Lương gốc',allowance:'Phụ cấp',bonus:'Thưởng',penalty:'Phạt',overtime:'Tăng ca',deduction:'Khấu trừ',insurance:'Bảo hiểm',tax:'Thuế',net:'Thực nhận'};
const calcL:Record<string,string>={fixed_amount:'Cố định',percent_of_component:'% theo lương',manual_entry:'Nhập tay'};

function SummaryCard({label,value}:{label:string;value?:any}){return(<div className="rounded-lg border bg-[hsl(var(--card))] px-3 py-2 text-center"><p className="text-xs text-muted-foreground">{label}</p><p className="text-sm font-semibold">{value??'—'}</p></div>);}

function EntryStatus({status}:{status:string}){const m:Record<string,string>={calculated:'Đã tính',reviewed:'Đã duyệt',error:'Lỗi'};const v:Record<string,'default'|'secondary'|'destructive'>={calculated:'secondary',reviewed:'default',error:'destructive'};return<Badge variant={v[status]??'secondary'}>{m[status]??status}</Badge>;}

export function PayrollListPage() {
  const {formatDate,formatDateTime}=useDateFormatter(); const{formatMoney}=useMoneyFormatter();
  const {getDisplayName}=useEmployeeDisplayName();
  const {data:periods}=usePayrollPeriods(); const {data:comps}=usePayrollComponents(); const {data:payslips}=usePayslips(); const publish=usePublishPayslips();
  const createPeriod=useCreatePeriod(); const perAct=usePayrollAction(); const startRun=useStartRun(); const createComp=useCreateComponent(); const delComp=useDeleteComponent();
  const [perOpen,setPerOpen]=useState(false); const[compOpen,setCompOpen]=useState(false);
  const [selectedPeriod,setSelectedPeriod]=useState<PayrollPeriod|null>(null);
  const [selectedEntry,setSelectedEntry]=useState<PayrollEntry|null>(null);
  const [selectedPayslip,setSelectedPayslip]=useState<string|null>(null);
  const {data:summary}=usePeriodSummary(selectedPeriod?.id??null);
  const {data:entries}=usePeriodEntries(selectedPeriod?.id??null);
  const pForm=useForm<PF>({resolver:zodResolver(periodSchema),defaultValues:{period_code:'',start_date:'',end_date:'',cutoff_date:''}});
  const cForm=useForm<CF2>({resolver:zodResolver(compSchema),defaultValues:{code:'',name:'',category:'base' as any,calculation_type:'fixed_amount' as any,default_amount:0,taxable:false}});
  const onPeriod=useCallback(async(v:PF)=>{try{await createPeriod.mutateAsync(v);toast.success('Tạo kỳ lương');setPerOpen(false);pForm.reset();}catch(raw){toast.error(extractErrorMessage(raw));}},[createPeriod,pForm]);
  const onComp=useCallback(async(v:CF2)=>{try{await createComp.mutateAsync(v);toast.success('Thêm thành phần lương');setCompOpen(false);cForm.reset();}catch(raw){toast.error(extractErrorMessage(raw));}},[createComp,cForm]);
  const act=useCallback(async(id:string,a:string)=>{try{await perAct.mutateAsync({id,action:a as any});const ms:Record<string,string>={'submit-approval':'Đã gửi duyệt',approve:'Đã duyệt',reject:'Đã từ chối',lock:'Đã khoá',reopen:'Đã mở lại'};toast.success(ms[a]??a);}catch(raw){toast.error(extractErrorMessage(raw));}},[perAct]);

  const pCols:Column<PayrollPeriod>[]=[
    {header:'Kỳ lương',accessor:undefined,className:'font-mono text-xs w-28 cursor-pointer hover:text-primary',cell:(p)=><button onClick={()=>setSelectedPeriod(p)} className="text-left font-mono text-xs">{p.period_code}</button>},
    {header:'Bắt đầu',accessor:undefined,cell:(p)=>formatDate(p.start_date),className:'font-mono text-xs w-28'},{header:'Kết thúc',accessor:undefined,cell:(p)=>formatDate(p.end_date),className:'font-mono text-xs w-28'},
    {header:'Trạng thái',accessor:undefined,className:'w-24',cell:(p)=><Badge variant={statusV[p.status]??'secondary'}>{statusL[p.status]??p.status}</Badge>},
    {header:'',accessor:undefined,className:'text-right w-32',cell:(p)=>(
      <div className="flex justify-end gap-1">
        {p.status==='draft'&&<Button variant="ghost" size="sm" title="Gửi duyệt" onClick={()=>act(p.id,'submit-approval')}><CheckCircle className="h-4 w-4"/></Button>}
        {p.status==='pending_approval'&&<><Button variant="ghost" size="sm" title="Duyệt" onClick={()=>act(p.id,'approve')}><CheckCircle className="h-4 w-4 text-green-600"/></Button>
        <Button variant="ghost" size="sm" title="Từ chối" onClick={()=>act(p.id,'reject')}><XCircle className="h-4 w-4 text-destructive"/></Button></>}
        {p.status==='approved'&&<><Button variant="ghost" size="sm" title="Chạy lương" onClick={async()=>{try{await startRun.mutateAsync(p.id);toast.success('Đã chạy lương');}catch(raw){toast.error(extractErrorMessage(raw));}}}><Play className="h-4 w-4"/></Button>
        <Button variant="ghost" size="sm" title="Khoá" onClick={()=>act(p.id,'lock')}><Lock className="h-4 w-4"/></Button></>}
        {p.status==='locked'&&<><Button variant="ghost" size="sm" title="Phát hành" onClick={async()=>{try{await publish.mutateAsync(p.id);toast.success('Phiếu lương đã phát hành');}catch(raw){toast.error(extractErrorMessage(raw));}}}><FileText className="h-4 w-4"/></Button><Button variant="ghost" size="sm" title="Mở lại" onClick={()=>act(p.id,'reopen')}><Unlock className="h-4 w-4"/></Button></>}
      </div>
    )},
  ];
  const cCols:Column<PayrollComponent>[]=[
    {header:'Mã',accessor:'code',className:'font-mono text-xs w-24'},{header:'Tên',accessor:'name'},
    {header:'Loại',accessor:undefined,className:'w-24',cell:(c)=>categoryL[c.category]??c.category},{header:'PP tính',accessor:undefined,className:'w-32',cell:(c)=>calcL[c.calculation_type]??c.calculation_type},
    {header:'',accessor:undefined,className:'text-right w-12',cell:(c)=><Button variant="ghost" size="sm" title="Xoá" onClick={async()=>{try{await delComp.mutateAsync(c.id);toast.success('Đã xoá');}catch(raw){toast.error(extractErrorMessage(raw));}}}><Trash2 className="h-4 w-4 text-destructive"/></Button>},
  ];
  const psCols:Column<Payslip>[]=[
    {header:'NV',accessor:undefined,className:'font-mono text-xs w-48 truncate cursor-pointer hover:text-primary',cell:(s)=><button onClick={()=>setSelectedPayslip(s.id)} className="text-left truncate">{getDisplayName(s.employee_id)}</button>},
    {header:'Tổng thu nhập',accessor:undefined,cell:(s)=>formatMoney(s.gross),className:'text-right w-28'},
    {header:'Thực nhận',accessor:undefined,cell:(s)=>formatMoney(s.net),className:'text-right w-28'},
    {header:'Phát hành',accessor:undefined,cell:(s)=>formatDateTime(s.published_at),className:'font-mono text-xs w-36'},
    {header:'Trạng thái',accessor:undefined,className:'w-20',cell:(s)=><Badge variant={s.status==='published'?'default':'secondary'}>{s.status==='published'?'Đã phát hành':'Nháp'}</Badge>},
  ];

  const entryCols:Column<PayrollEntry>[]=[
    {header:'NV',accessor:undefined,className:'font-mono text-xs w-48 truncate cursor-pointer hover:text-primary',cell:(e)=><button onClick={()=>setSelectedEntry(e)} className="text-left truncate">{getDisplayName(e.employee_id)}</button>},
    {header:'Gross',accessor:undefined,cell:(e)=>formatMoney(e.gross_amount),className:'text-right w-24'},
    {header:'Khấu trừ',accessor:undefined,cell:(e)=>formatMoney(e.deduction_amount),className:'text-right w-24'},
    {header:'Thực nhận',accessor:undefined,cell:(e)=>formatMoney(e.net_amount),className:'text-right w-24'},
    {header:'Trạng thái',accessor:undefined,className:'w-20',cell:(e)=><EntryStatus status={e.status}/>},
  ];

  return (<div className="space-y-6">
    {/* Summary bar */}
    {selectedPeriod&&summary&&<div className="grid grid-cols-5 gap-3">
      <SummaryCard label="Kỳ" value={summary.period_code}/>
      <SummaryCard label="Nhân viên" value={summary.employee_count}/>
      <SummaryCard label="Tổng thu nhập" value={formatMoney(summary.total_gross)}/>
      <SummaryCard label="Khấu trừ" value={formatMoney(summary.total_deductions)}/>
      <SummaryCard label="Thực nhận" value={formatMoney(summary.total_net)}/>
    </div>}
    <div className="space-y-4"><div className="flex items-center justify-between"><span className="text-sm font-medium text-muted-foreground">Kỳ lương</span>
      <Button size="sm" onClick={()=>setPerOpen(true)}><Plus className="h-4 w-4 mr-1"/>Tạo kỳ lương</Button></div>
    <DataTable columns={pCols} data={periods??[]} rowKey="id" emptyMessage="Chưa có kỳ lương"/></div>
    <div className="space-y-4"><div className="flex items-center justify-between"><span className="text-sm font-medium text-muted-foreground">Thành phần lương</span>
      <Button size="sm" onClick={()=>{cForm.reset();setCompOpen(true);}}><Plus className="h-4 w-4 mr-1"/>Thêm TP</Button></div>
    <DataTable columns={cCols} data={comps??[]} rowKey="id" emptyMessage="Chưa có thành phần"/></div>
    <div className="space-y-4"><span className="text-sm font-medium text-muted-foreground">Phiếu lương</span>
    <DataTable columns={psCols} data={payslips??[]} rowKey="id" emptyMessage="Chưa có phiếu lương"/></div>

    <Drawer open={perOpen} onOpenChange={setPerOpen}><DrawerContent size="sm"><DrawerHeader><DrawerTitle>Tạo kỳ lương</DrawerTitle><DrawerDescription>Nhập thông tin kỳ lương mới</DrawerDescription></DrawerHeader>
      <DrawerBody><form id="pr-form" onSubmit={pForm.handleSubmit(onPeriod,()=>toast.error('Vui lòng nhập đầy đủ thông tin kỳ lương'))} className="space-y-4">
        <div className="space-y-2"><Label>Mã kỳ <span className="text-destructive">*</span></Label><Input autoComplete="off"{...pForm.register('period_code')}/></div>
        <div className="grid grid-cols-3 gap-3"><div className="space-y-2"><Label>Bắt đầu <span className="text-destructive">*</span></Label><Input type="date" autoComplete="off"{...pForm.register('start_date')}/></div>
        <div className="space-y-2"><Label>Kết thúc <span className="text-destructive">*</span></Label><Input type="date" autoComplete="off"{...pForm.register('end_date')}/></div>
        <div className="space-y-2"><Label>Ngày chốt <span className="text-destructive">*</span></Label><Input type="date" autoComplete="off"{...pForm.register('cutoff_date')}/></div></div>
      </form></DrawerBody>
      <DrawerFooter><Button variant="ghost" onClick={()=>setPerOpen(false)}>Hủy</Button><Button type="submit" form="pr-form" disabled={createPeriod.isPending}>Tạo</Button></DrawerFooter>
    </DrawerContent></Drawer>

    <Drawer open={compOpen} onOpenChange={setCompOpen}><DrawerContent size="sm"><DrawerHeader><DrawerTitle>Thêm thành phần lương</DrawerTitle><DrawerDescription>Nhập thông tin thành phần lương mới</DrawerDescription></DrawerHeader>
      <DrawerBody><form id="cp-form" onSubmit={cForm.handleSubmit(onComp)} className="space-y-4">
        <div className="space-y-2"><Label>Mã <span className="text-destructive">*</span></Label><Input autoComplete="off"{...cForm.register('code')}/></div>
        <div className="space-y-2"><Label>Tên <span className="text-destructive">*</span></Label><Input autoComplete="off"{...cForm.register('name')}/></div>
        <div className="grid grid-cols-2 gap-3"><div className="space-y-2"><Label>Loại <span className="text-destructive">*</span></Label>
          <select className="h-8 w-full rounded-md border bg-[hsl(var(--card))] text-foreground px-2 text-[13px]" {...cForm.register('category')}>
            {Object.entries(categoryL).map(([v,l])=><option key={v} value={v}>{l}</option>)}
          </select></div>
        <div className="space-y-2"><Label>PP tính <span className="text-destructive">*</span></Label>
          <select className="h-8 w-full rounded-md border bg-[hsl(var(--card))] text-foreground px-2 text-[13px]" {...cForm.register('calculation_type')}>
            {Object.entries(calcL).map(([v,l])=><option key={v} value={v}>{l}</option>)}
          </select></div></div>
        <div className="space-y-2"><Label>Giá trị mặc định</Label><Input type="number" autoComplete="off"{...cForm.register('default_amount')}/></div>
      </form></DrawerBody>
      <DrawerFooter><Button variant="ghost" onClick={()=>setCompOpen(false)}>Hủy</Button><Button type="submit" form="cp-form" disabled={createComp.isPending}>Thêm</Button></DrawerFooter>
    </DrawerContent></Drawer>

    {/* Period entries drawer (full) */}
    <Drawer open={!!selectedPeriod} onOpenChange={(o)=>{if(!o)setSelectedPeriod(null)}}><DrawerContent size="lg"><DrawerHeader><DrawerTitle>Kỳ {selectedPeriod?.period_code}</DrawerTitle><DrawerDescription>{summary?`${summary.employee_count} NV · Gross: ${formatMoney(summary.total_gross)} · Net: ${formatMoney(summary.total_net)}`:''}</DrawerDescription></DrawerHeader>
      <DrawerBody><DataTable columns={entryCols} data={entries?.data??[]} rowKey="id" emptyMessage="Chọn kỳ lương có dữ liệu"/></DrawerBody>
      <DrawerFooter><Button variant="ghost" onClick={()=>setSelectedPeriod(null)}>Đóng</Button></DrawerFooter>
    </DrawerContent></Drawer>

    <PayrollEntryDetail entryId={selectedEntry?.id??null} initialEntry={selectedEntry} open={!!selectedEntry} onOpenChange={(o)=>{if(!o)setSelectedEntry(null)}} components={comps??[]}/>
    <PayslipDetail payslipId={selectedPayslip} open={!!selectedPayslip} onOpenChange={(o)=>{if(!o)setSelectedPayslip(null)}}/>
  </div>);
}
