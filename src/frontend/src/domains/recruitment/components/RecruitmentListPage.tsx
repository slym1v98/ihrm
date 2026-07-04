'use client';
import { useState, useCallback } from 'react'; import { useForm } from 'react-hook-form'; import { zodResolver } from '@hookform/resolvers/zod'; import { z } from 'zod'; import { toast } from 'sonner';
import { useRequisitions, useCandidates, useCreateRequisition, useCreateCandidate, useUpdateCandidateStage } from '@/domains/recruitment/hooks/useRecruitment';
import type { Candidate, Requisition } from '@/domains/recruitment/models/recruitment';
import { DataTable, type Column } from '@/shared/components/DataTable'; import { Drawer, DrawerContent, DrawerHeader, DrawerTitle, DrawerDescription, DrawerBody, DrawerFooter } from '@/shared/components/ui/drawer';
import { Button } from '@/shared/components/ui/button'; import { Input } from '@/shared/components/ui/input'; import { Label } from '@/shared/components/ui/label'; import { Badge } from '@/shared/components/ui/badge'; import { extractErrorMessage } from '@/core/errors/messages';
import { Plus, ArrowRight } from 'lucide-react';

const statusL: Record<string,string>={draft:'Nháp',open:'Đang tuyển',closed:'Đã đóng',cancelled:'Đã huỷ'};
const stageL: Record<string,string>={applied:'Ứng tuyển',screening:'Sàng lọc',interview:'Phỏng vấn',offer:'Offer',hired:'Đã nhận việc',rejected:'Từ chối'};
const stageColors: Record<string,'default'|'secondary'|'destructive'|'outline'>={applied:'secondary',screening:'outline',interview:'default',offer:'outline',hired:'default',rejected:'destructive'};

const reqSchema=z.object({code:z.string().min(2),title:z.string().min(1),department_id:z.string().min(1),position_id:z.string().min(1),headcount:z.coerce.number().min(1)});type RF=z.infer<typeof reqSchema>;
const canSchema=z.object({requisition_id:z.string().min(1),name:z.string().min(1),email:z.string().email(),phone:z.string().optional()});type CF=z.infer<typeof canSchema>;

export function RecruitmentListPage() {
  const {data:reqs}=useRequisitions(); const {data:candidates}=useCandidates();
  const createReq=useCreateRequisition(); const createCan=useCreateCandidate(); const updStage=useUpdateCandidateStage();
  const [reqOpen,setReqOpen]=useState(false); const[canOpen,setCanOpen]=useState(false);
  const reqForm=useForm<RF>({resolver:zodResolver(reqSchema),defaultValues:{code:'',title:'',department_id:'',position_id:'',headcount:1}});
  const canForm=useForm<CF>({resolver:zodResolver(canSchema),defaultValues:{requisition_id:'',name:'',email:'',phone:''}});
  const onReq=useCallback(async(v:RF)=>{try{await createReq.mutateAsync(v);toast.success('Tạo yêu cầu tuyển dụng');setReqOpen(false);reqForm.reset();}catch(raw){toast.error(extractErrorMessage(raw));}},[createReq,reqForm]);
  const onCan=useCallback(async(v:CF)=>{try{await createCan.mutateAsync(v);toast.success('Thêm ứng viên');setCanOpen(false);canForm.reset();}catch(raw){toast.error(extractErrorMessage(raw));}},[createCan,canForm]);
  const nextStage=useCallback(async(id:string,stage:string)=>{const m={applied:'screening',screening:'interview',interview:'offer',offer:'hired'};try{await updStage.mutateAsync({id,stage:(m as Record<string,string>)[stage]??'rejected'});toast.success('Đã chuyển giai đoạn');}catch(raw){toast.error(extractErrorMessage(raw));}},[updStage]);

  const rCols:Column<Requisition>[]=[
    {header:'Mã',accessor:'code',className:'font-mono text-xs w-24'},{header:'Tiêu đề',accessor:'title'},
    {header:'Số lượng',accessor:'headcount',className:'text-right w-20'},
    {header:'Trạng thái',accessor:undefined,className:'w-20',cell:(r)=><Badge variant={statusL[r.status]?statusL[r.status]==='Đang tuyển'?'default':'secondary':'secondary'}>{statusL[r.status]??r.status}</Badge>},
  ];
  const cCols:Column<Candidate>[]=[
    {header:'Tên',accessor:'name'},{header:'Email',accessor:'email',className:'text-muted-foreground'},
    {header:'Giai đoạn',accessor:undefined,className:'w-24',cell:(c)=><Badge variant={stageColors[c.stage]??'secondary'}>{stageL[c.stage]??c.stage}</Badge>},
    {header:'',accessor:undefined,className:'text-right w-12',cell:(c)=><Button variant="ghost" size="sm" title="Chuyển giai đoạn" onClick={()=>nextStage(c.id,c.stage)}><ArrowRight className="h-4 w-4"/></Button>},
  ];

  return (<div className="space-y-6">
    <div className="space-y-4"><div className="flex items-center justify-between"><span className="text-sm font-medium text-muted-foreground">Yêu cầu tuyển dụng</span>
      <Button size="sm" onClick={()=>{reqForm.reset();setReqOpen(true);}}><Plus className="h-4 w-4 mr-1"/>Tạo yêu cầu</Button></div>
    <DataTable columns={rCols} data={reqs??[]} rowKey="id" emptyMessage="Chưa có yêu cầu"/></div>
    <div className="space-y-4"><div className="flex items-center justify-between"><span className="text-sm font-medium text-muted-foreground">Ứng viên</span>
      <Button size="sm" onClick={()=>{canForm.reset();setCanOpen(true);}}><Plus className="h-4 w-4 mr-1"/>Thêm ứng viên</Button></div>
    <DataTable columns={cCols} data={candidates??[]} rowKey="id" emptyMessage="Chưa có ứng viên"/></div>

    <Drawer open={reqOpen} onOpenChange={setReqOpen}><DrawerContent size="sm"><DrawerHeader><DrawerTitle>Yêu cầu tuyển dụng</DrawerTitle><DrawerDescription>Thông tin yêu cầu tuyển dụng mới</DrawerDescription></DrawerHeader>
      <DrawerBody><form id="req-form" onSubmit={reqForm.handleSubmit(onReq)} className="space-y-4">
        <div className="space-y-2"><Label>Mã <span className="text-destructive">*</span></Label><Input autoComplete="off"{...reqForm.register('code')}/></div>
        <div className="space-y-2"><Label>Tiêu đề <span className="text-destructive">*</span></Label><Input autoComplete="off"{...reqForm.register('title')}/></div>
        <div className="grid grid-cols-2 gap-3"><div className="space-y-2"><Label>Phòng ban <span className="text-destructive">*</span></Label><Input autoComplete="off"{...reqForm.register('department_id')}/></div>
        <div className="space-y-2"><Label>Chức vụ <span className="text-destructive">*</span></Label><Input autoComplete="off"{...reqForm.register('position_id')}/></div></div>
        <div className="space-y-2"><Label>Số lượng <span className="text-destructive">*</span></Label><Input type="number" autoComplete="off"{...reqForm.register('headcount')}/></div>
      </form></DrawerBody>
      <DrawerFooter><Button variant="ghost" onClick={()=>setReqOpen(false)}>Hủy</Button><Button type="submit" form="req-form" disabled={createReq.isPending}>Tạo</Button></DrawerFooter>
    </DrawerContent></Drawer>

    <Drawer open={canOpen} onOpenChange={setCanOpen}><DrawerContent size="sm"><DrawerHeader><DrawerTitle>Thêm ứng viên</DrawerTitle><DrawerDescription>Nhập thông tin ứng viên mới</DrawerDescription></DrawerHeader>
      <DrawerBody><form id="can-form" onSubmit={canForm.handleSubmit(onCan)} className="space-y-4">
        <div className="space-y-2"><Label>Yêu cầu <span className="text-destructive">*</span></Label>
          <select className="h-8 w-full rounded-md border bg-[hsl(var(--card))] px-2 py-1 text-[13px] outline-none focus:ring-2 focus:ring-primary"{...canForm.register('requisition_id')}>
            <option value="">Chọn yêu cầu</option>{reqs?.map(r=><option key={r.id} value={r.id}>{r.code} - {r.title}</option>)}
          </select></div>
        <div className="space-y-2"><Label>Tên <span className="text-destructive">*</span></Label><Input autoComplete="off"{...canForm.register('name')}/></div>
        <div className="space-y-2"><Label>Email <span className="text-destructive">*</span></Label><Input type="email" autoComplete="off"{...canForm.register('email')}/></div>
        <div className="space-y-2"><Label>Điện thoại</Label><Input autoComplete="off"{...canForm.register('phone')}/></div>
      </form></DrawerBody>
      <DrawerFooter><Button variant="ghost" onClick={()=>setCanOpen(false)}>Hủy</Button><Button type="submit" form="can-form" disabled={createCan.isPending}>Thêm</Button></DrawerFooter>
    </DrawerContent></Drawer></div>);
}
