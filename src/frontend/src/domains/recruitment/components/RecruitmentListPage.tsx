'use client';
import { useState, useCallback } from 'react';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { toast } from 'sonner';
import { Plus, UserPlus, Send, Eye } from 'lucide-react';
import { useRequisitions, useCreateRequisition, useCandidates, useCreateCandidate } from '@/domains/recruitment/hooks/useRecruitment';
import type { Requisition, Candidate, CreateRequisitionPayload, CreateCandidatePayload } from '@/domains/recruitment/models/recruitment';
import { DataTable, type Column } from '@/shared/components/DataTable';
import { Drawer, DrawerContent, DrawerHeader, DrawerTitle, DrawerDescription, DrawerBody, DrawerFooter } from '@/shared/components/ui/drawer';
import { Button } from '@/shared/components/ui/button';
import { Input } from '@/shared/components/ui/input';
import { Label } from '@/shared/components/ui/label';
import { Badge } from '@/shared/components/ui/badge';
import { extractErrorMessage } from '@/core/errors/messages';

const reqSchema = z.object({ code: z.string().min(2), title: z.string().min(1), department_id: z.string().min(1), position_id: z.string().min(1), headcount: z.coerce.number().min(1) });
const candSchema = z.object({ requisition_id: z.string().min(1), name: z.string().min(1), email: z.string().email(), phone: z.string().optional() });
type ReqF = z.infer<typeof reqSchema>; type CandF = z.infer<typeof candSchema>;

const statusL: Record<string, string> = { draft: 'Nháp', pending_approval: 'Chờ duyệt', open: 'Đang tuyển', filled: 'Đã đủ', cancelled: 'Đã huỷ', closed: 'Đóng' };
const statusV: Record<string, 'default' | 'secondary' | 'destructive' | 'outline'> = { draft: 'secondary', pending_approval: 'default', open: 'default', filled: 'outline', cancelled: 'destructive', closed: 'secondary' };
const cStatusL: Record<string, string> = { new: 'Mới', screened: 'Đã sàng lọc', interviewed: 'Đã PV', offered: 'Đã offer', hired: 'Đã nhận', rejected: 'Loại' };
const cStatusV: Record<string, 'default' | 'secondary' | 'destructive' | 'outline'> = { new: 'secondary', screened: 'secondary', interviewed: 'default', offered: 'default', hired: 'outline', rejected: 'destructive' };

export function RecruitmentListPage() {
  const { data: reqs, isLoading: l1 } = useRequisitions();
  const { data: candidates, isLoading: l2 } = useCandidates();
  const createReq = useCreateRequisition();
  const createCand = useCreateCandidate();
  const [reqOpen, setReqOpen] = useState(false); const [candOpen, setCandOpen] = useState(false);
  const reqForm = useForm<ReqF>({ resolver: zodResolver(reqSchema), defaultValues: { code: '', title: '', department_id: '', position_id: '', headcount: 1 } });
  const candForm = useForm<CandF>({ resolver: zodResolver(candSchema), defaultValues: { requisition_id: '', name: '', email: '', phone: '' } });

  const openCreateReq = useCallback(() => { reqForm.reset({ code: '', title: '', department_id: '', position_id: '', headcount: 1 }); setReqOpen(true); }, [reqForm]);
  const openCreateCand = useCallback(() => { candForm.reset({ requisition_id: '', name: '', email: '', phone: '' }); setCandOpen(true); }, [candForm]);
  const onSubmitReq = useCallback(async (v: ReqF) => { try { await createReq.mutateAsync(v as CreateRequisitionPayload); toast.success('Tạo yêu cầu thành công'); setReqOpen(false); } catch (raw) { toast.error(extractErrorMessage(raw)); } }, [createReq]);
  const onSubmitCand = useCallback(async (v: CandF) => { try { await createCand.mutateAsync(v as CreateCandidatePayload); toast.success('Thêm ứng viên thành công'); setCandOpen(false); } catch (raw) { toast.error(extractErrorMessage(raw)); } }, [createCand]);

  const rCols: Column<Requisition>[] = [
    { header: 'Mã', accessor: 'code', className: 'font-mono text-xs w-20' }, { header: 'Vị trí', accessor: 'title' },
    { header: 'Số lượng', accessor: 'headcount', className: 'w-16 text-center' },
    { header: 'Trạng thái', accessor: undefined, className: 'w-20', cell: (r) => <Badge variant={statusV[r.status]}>{statusL[r.status]}</Badge> },
  ];
  const cCols: Column<Candidate>[] = [
    { header: 'Họ tên', accessor: 'name' }, { header: 'Email', accessor: 'email', className: 'text-xs' },
    { header: 'Giai đoạn', accessor: undefined, className: 'w-20', cell: (c) => <Badge variant={cStatusV[c.stage]}>{cStatusL[c.stage]}</Badge> },
    { header: 'Trạng thái', accessor: undefined, className: 'w-16', cell: (c) => <Badge variant={c.status === 'active' ? 'default' : 'secondary'}>{c.status}</Badge> },
  ];

  return (<div className="space-y-6">
    <div><div className="flex gap-2 mb-2"><Button onClick={openCreateReq}><Plus className="h-4 w-4 mr-1" />Tạo yêu cầu</Button><Button onClick={openCreateCand} variant="ghost"><UserPlus className="h-4 w-4 mr-1" />Thêm ứng viên</Button></div>
      <h2 className="text-sm font-semibold mb-2">Yêu cầu tuyển dụng</h2>
      <DataTable columns={rCols} data={reqs ?? []} isLoading={l1} rowKey="id" emptyMessage="Chưa có yêu cầu nào" /></div>
    <div><h2 className="text-sm font-semibold mb-2">Ứng viên</h2>
      <DataTable columns={cCols} data={candidates ?? []} isLoading={l2} rowKey="id" emptyMessage="Chưa có ứng viên" /></div>

    <Drawer open={reqOpen} onOpenChange={setReqOpen}><DrawerContent size="sm"><DrawerHeader><DrawerTitle>Tạo yêu cầu tuyển dụng</DrawerTitle><DrawerDescription>Nhập thông tin yêu cầu mới</DrawerDescription></DrawerHeader>
      <DrawerBody><form id="req-form" onSubmit={reqForm.handleSubmit(onSubmitReq)} className="space-y-4">
        <div className="space-y-2"><Label htmlFor="rc">Mã yêu cầu <span className="text-destructive">*</span></Label><Input id="rc" autoComplete="off" {...reqForm.register('code')} /></div>
        <div className="space-y-2"><Label htmlFor="rt">Chức danh <span className="text-destructive">*</span></Label><Input id="rt" autoComplete="off" {...reqForm.register('title')} /></div>
        <div className="grid grid-cols-2 gap-3"><div className="space-y-2"><Label htmlFor="rd">Phòng ban <span className="text-destructive">*</span></Label><Input id="rd" autoComplete="off" {...reqForm.register('department_id')} /></div>
        <div className="space-y-2"><Label htmlFor="rp">Vị trí <span className="text-destructive">*</span></Label><Input id="rp" autoComplete="off" {...reqForm.register('position_id')} /></div></div>
        <div className="space-y-2"><Label htmlFor="rh">Số lượng <span className="text-destructive">*</span></Label><Input id="rh" type="number" autoComplete="off" {...reqForm.register('headcount')} /></div>
      </form></DrawerBody>
      <DrawerFooter><Button variant="ghost" onClick={() => setReqOpen(false)}>Hủy</Button><Button type="submit" form="req-form" disabled={createReq.isPending}>Tạo</Button></DrawerFooter></DrawerContent></Drawer>

    <Drawer open={candOpen} onOpenChange={setCandOpen}><DrawerContent size="sm"><DrawerHeader><DrawerTitle>Thêm ứng viên</DrawerTitle><DrawerDescription>Nhập thông tin ứng viên mới</DrawerDescription></DrawerHeader>
      <DrawerBody><form id="cand-form" onSubmit={candForm.handleSubmit(onSubmitCand)} className="space-y-4">
        <div className="space-y-2"><Label htmlFor="cr">Yêu cầu <span className="text-destructive">*</span></Label>
          <select id="cr" className="w-full rounded-md border bg-[hsl(var(--card))] px-2 py-1 text-[13px]" {...candForm.register('requisition_id')}>
            <option value="">Chọn yêu cầu</option>{reqs?.map(r => <option key={r.id} value={r.id}>{r.code}</option>)}</select></div>
        <div className="space-y-2"><Label htmlFor="cn">Họ tên <span className="text-destructive">*</span></Label><Input id="cn" autoComplete="off" {...candForm.register('name')} /></div>
        <div className="space-y-2"><Label htmlFor="ce">Email <span className="text-destructive">*</span></Label><Input id="ce" type="email" autoComplete="off" {...candForm.register('email')} /></div>
        <div className="space-y-2"><Label htmlFor="cp">Điện thoại</Label><Input id="cp" autoComplete="off" {...candForm.register('phone')} /></div>
      </form></DrawerBody>
      <DrawerFooter><Button variant="ghost" onClick={() => setCandOpen(false)}>Hủy</Button><Button type="submit" form="cand-form" disabled={createCand.isPending}>Thêm</Button></DrawerFooter></DrawerContent></Drawer></div>);
}
