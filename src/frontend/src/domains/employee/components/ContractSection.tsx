'use client';

import { useState, useCallback } from 'react';
import { z } from 'zod';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { toast } from 'sonner';
import { useContracts, useCreateContract, useActivateContract, useRenewContract, useTerminateContract } from '@/domains/employee/hooks/useContracts';
import { extractErrorMessage } from '@/core/errors/messages';
import { DataTable, type Column } from '@/shared/components/DataTable';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter } from '@/shared/components/ui/dialog';
import { Button } from '@/shared/components/ui/button';
import { Input } from '@/shared/components/ui/input';
import { Label } from '@/shared/components/ui/label';
import { Badge } from '@/shared/components/ui/badge';
import type { Contract } from '@/domains/employee/models/contract';

const contractSchema = z.object({
  contract_number: z.string().min(1, 'Số HĐ không được để trống'),
  contract_type: z.string().optional(),
  start_date: z.string().optional(),
  end_date: z.string().optional(),
  sign_date: z.string().optional(),
  base_salary: z.string().optional(),
});
type ContractForm = z.infer<typeof contractSchema>;

function isExpiringSoon(endDate: string | null) {
  if (!endDate) return false;
  const diff = new Date(endDate).getTime() - Date.now();
  return diff > 0 && diff < 30 * 24 * 60 * 60 * 1000;
}

export function ContractSection({ employeeId }: { employeeId: string }) {
  const { data, isLoading } = useContracts(employeeId);
  const createContract = useCreateContract(employeeId);
  const activateContract = useActivateContract();
  const renewContract = useRenewContract();
  const terminateContract = useTerminateContract();
  const [dialogOpen, setDialogOpen] = useState(false);
  const [confirmAction, setConfirmAction] = useState<{ type: 'activate' | 'terminate' | 'renew'; id: string; number: string } | null>(null);
  const [renewDays, setRenewDays] = useState('365');

  const form = useForm<ContractForm, unknown>({
    resolver: zodResolver(contractSchema),
    defaultValues: { contract_number: '', contract_type: '', start_date: '', end_date: '', sign_date: '', base_salary: '' },
  });

  const onSubmitCreate = useCallback(async (v: ContractForm) => {
    try {
      await createContract.mutateAsync({ ...v, base_salary: v.base_salary ? parseInt(v.base_salary) : undefined });
      toast.success('Tạo hợp đồng thành công');
      setDialogOpen(false);
    } catch (raw) { toast.error(extractErrorMessage(raw)); }
  }, [createContract]);

  const contracts: Contract[] = data?.data ?? [];

  const columns: Column<Contract>[] = [
    { header: 'Số HĐ', accessor: 'contract_number', className: 'font-mono text-xs' },
    { header: 'Loại', accessor: 'contract_type', cell: (c) => c.contract_type ?? '—' },
    { header: 'Ngày BĐ', accessor: 'start_date', cell: (c) => c.start_date ?? '—' },
    { header: 'Ngày KT', accessor: undefined as never, cell: (c) => (
      <span className={isExpiringSoon(c.end_date) ? 'font-semibold text-destructive' : ''}>{c.end_date ?? '—'}</span>
    )},
    { header: 'Lương CB', accessor: undefined as never, cell: (c) => c.base_salary != null ? c.base_salary.toLocaleString() : '—' },
    { header: 'Trạng thái', accessor: undefined as never, cell: (c) => {
      const st = c.status === 'active' ? 'default' : c.status === 'expired' ? 'secondary' : 'destructive';
      const lb = c.status === 'active' ? 'Hiệu lực' : c.status === 'expired' ? 'Hết hạn' : 'Đã chấm dứt';
      return (
        <div className="flex items-center gap-2">
          <Badge variant={st}>{lb}</Badge>
          {isExpiringSoon(c.end_date) && c.status === 'active' && <Badge variant="destructive">Sắp hết hạn</Badge>}
        </div>
      );
    }},
    { header: '', accessor: undefined as never, className: 'text-right', cell: (c) => (
      <div className="flex justify-end gap-1">
        {c.status === 'pending' && <Button variant="ghost" size="sm" title="Kích hoạt" onClick={() => setConfirmAction({ type: 'activate', id: c.id, number: c.contract_number })}>Kích hoạt</Button>}
        {c.status === 'active' && <>
          <Button variant="ghost" size="sm" title="Gia hạn" onClick={() => setConfirmAction({ type: 'renew', id: c.id, number: c.contract_number })}>Gia hạn</Button>
          <Button variant="ghost" size="sm" title="Chấm dứt" onClick={() => setConfirmAction({ type: 'terminate', id: c.id, number: c.contract_number })}>Chấm dứt</Button>
        </>}
      </div>
    )},
  ];

  return (
    <div className="space-y-4">
      <div className="flex items-center justify-between">
        <h2 className="text-lg font-semibold">Hợp đồng lao động</h2>
        <Button onClick={() => { form.reset({ contract_number: '', contract_type: '', start_date: '', end_date: '', sign_date: '', base_salary: '' }); setDialogOpen(true); }}>+ Thêm HĐ</Button>
      </div>

      <DataTable<Contract> columns={columns} data={contracts} isLoading={isLoading} rowKey="id" emptyMessage="Chưa có hợp đồng" />

      <Dialog open={dialogOpen} onOpenChange={setDialogOpen}>
        <DialogContent>
          <DialogHeader><DialogTitle>Thêm hợp đồng</DialogTitle><DialogDescription>Nhập thông tin hợp đồng mới</DialogDescription></DialogHeader>
          <form onSubmit={form.handleSubmit(onSubmitCreate)} className="space-y-3">
            <div className="space-y-1"><Label>Số hợp đồng *</Label><Input {...form.register('contract_number')} /></div>
            <div className="grid grid-cols-2 gap-3">
              <div className="space-y-1"><Label>Loại HĐ</Label><Input {...form.register('contract_type')} /></div>
              <div className="space-y-1"><Label>Lương cơ bản</Label><Input type="number" {...form.register('base_salary')} /></div>
            </div>
            <div className="grid grid-cols-3 gap-3">
              <div className="space-y-1"><Label>Ngày BĐ</Label><Input type="date" {...form.register('start_date')} /></div>
              <div className="space-y-1"><Label>Ngày KT</Label><Input type="date" {...form.register('end_date')} /></div>
              <div className="space-y-1"><Label>Ngày ký</Label><Input type="date" {...form.register('sign_date')} /></div>
            </div>
            <DialogFooter>
              <Button variant="ghost" type="button" onClick={() => setDialogOpen(false)}>Hủy</Button>
              <Button type="submit" disabled={createContract.isPending}>Tạo</Button>
            </DialogFooter>
          </form>
        </DialogContent>
      </Dialog>

      {/* Confirm action dialog */}
      <Dialog open={!!confirmAction} onOpenChange={(o) => !o && setConfirmAction(null)}>
        <DialogContent>
          <DialogHeader><DialogTitle>Xác nhận</DialogTitle>
          <DialogDescription>
            {confirmAction?.type === 'activate' ? `Kích hoạt hợp đồng "${confirmAction?.number}"?` :
             confirmAction?.type === 'terminate' ? `Chấm dứt hợp đồng "${confirmAction?.number}"?` :
             `Gia hạn hợp đồng "${confirmAction?.number}"?`}
          </DialogDescription></DialogHeader>
          {confirmAction?.type === 'renew' && (
            <div className="space-y-2">
              <Label>Số ngày gia hạn</Label>
              <Input type="number" value={renewDays} onChange={(e) => setRenewDays(e.target.value)} />
            </div>
          )}
          <DialogFooter>
            <Button variant="ghost" onClick={() => setConfirmAction(null)}>Hủy</Button>
            <Button variant="primary" disabled={activateContract.isPending || renewContract.isPending || terminateContract.isPending}
              onClick={async () => {
                if (!confirmAction) return;
                try {
                  if (confirmAction.type === 'activate') await activateContract.mutateAsync(confirmAction.id);
                  else if (confirmAction.type === 'terminate') await terminateContract.mutateAsync(confirmAction.id);
                  else {
                    const newEnd = new Date();
                    newEnd.setDate(newEnd.getDate() + parseInt(renewDays));
                    await renewContract.mutateAsync({ id: confirmAction.id, payload: { new_end_date: newEnd.toISOString().split('T')[0] } });
                  }
                  toast.success('Thành công');
                  setConfirmAction(null);
                } catch (raw) { toast.error(extractErrorMessage(raw)); }
              }}>
              Xác nhận
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </div>
  );
}
