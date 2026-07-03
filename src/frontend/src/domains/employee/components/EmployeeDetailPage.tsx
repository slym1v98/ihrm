'use client';

import { useState, useCallback } from 'react';
import { useForm } from 'react-hook-form';
import { toast } from 'sonner';
import { useParams, useRouter } from 'next/navigation';
import { useEmployee, useUpdateEmployee, useTransferEmployee, useChangeEmployeeStatus } from '@/domains/employee/hooks/useEmployees';
import { useContracts } from '@/domains/employee/hooks/useContracts';
import { extractErrorMessage } from '@/core/errors/messages';
import { ContractSection } from '@/domains/employee/components/ContractSection';
import { Button } from '@/shared/components/ui/button';
import { Input } from '@/shared/components/ui/input';
import { Label } from '@/shared/components/ui/label';
import { Badge } from '@/shared/components/ui/badge';
import { ChevronLeft } from 'lucide-react';

type Tab = 'info' | 'contracts' | 'documents';

export function EmployeeDetailPage() {
  const { id } = useParams<{ id: string }>();
  const router = useRouter();
  const { data: employee, isLoading } = useEmployee(id);
  const updateEmp = useUpdateEmployee();
  const transferEmp = useTransferEmployee();
  const changeStatus = useChangeEmployeeStatus();
  const { data: contractsData } = useContracts(id);
  const [tab, setTab] = useState<Tab>('info');

  const form = useForm({ defaultValues: {} as Record<string, string> });

  // Set form values when employee loads
  useState(() => {
    if (employee) {
      form.reset({
        first_name: employee.first_name,
        last_name: employee.last_name,
        dob: employee.dob ?? '',
        gender: employee.gender ?? '',
        personal_email: employee.personal_email ?? '',
        phone: employee.phone ?? '',
        branch_id: employee.branch_id ?? '',
        department_id: employee.department_id ?? '',
        position_id: employee.position_id ?? '',
      });
    }
  });

  const handleSave = useCallback(async () => {
    const v = form.getValues();
    try {
      await updateEmp.mutateAsync({ id, payload: { first_name: v.first_name, last_name: v.last_name, dob: v.dob || undefined, gender: v.gender || undefined, personal_email: v.personal_email || undefined, phone: v.phone || undefined } });
      if (v.branch_id || v.department_id || v.position_id) {
        await transferEmp.mutateAsync({ id, payload: { branch_id: v.branch_id || undefined, department_id: v.department_id || undefined, position_id: v.position_id || undefined } });
      }
      toast.success('Cập nhật thành công');
    } catch (raw) {
      toast.error(extractErrorMessage(raw));
    }
  }, [id, updateEmp, transferEmp, form]);

  const handleToggleStatus = useCallback(async () => {
    if (!employee) return;
    const action = employee.status === 'active' ? 'deactivate' : 'activate';
    try {
      await changeStatus.mutateAsync({ id, action });
      toast.success(action === 'activate' ? 'Kích hoạt nhân viên' : 'Vô hiệu hóa nhân viên');
    } catch (raw) {
      toast.error(extractErrorMessage(raw));
    }
  }, [id, employee, changeStatus]);

  if (isLoading) return <div className="py-12 text-center text-muted-foreground">Đang tải...</div>;
  if (!employee) return <div className="py-12 text-center text-destructive">Không tìm thấy nhân viên</div>;

  const tabs: { key: Tab; label: string }[] = [
    { key: 'info', label: 'Thông tin' },
    { key: 'contracts', label: `Hợp đồng (${contractsData?.data?.length ?? 0})` },
    { key: 'documents', label: 'Tài liệu' },
  ];

  return (
    <div className="space-y-4">
      <div className="flex items-center gap-3">
        <Button variant="ghost" size="sm" onClick={() => router.push('/employees')}>
          <ChevronLeft className="h-4 w-4" />
        </Button>
        <div className="flex-1">
          <h1 className="text-2xl font-semibold">{employee.last_name} {employee.first_name}</h1>
          <p className="text-sm text-muted-foreground">{employee.employee_code} · <Badge variant={employee.status === 'active' ? 'default' : 'secondary'}>{employee.status === 'active' ? 'Đang làm' : 'Đã nghỉ'}</Badge></p>
        </div>
        <Button variant={employee.status === 'active' ? 'destructive' : 'primary'} size="sm" onClick={handleToggleStatus} disabled={changeStatus.isPending}>
          {employee.status === 'active' ? 'Vô hiệu hóa' : 'Kích hoạt'}
        </Button>
      </div>

      <div className="flex gap-1 border-b">
        {tabs.map(t => (
          <button key={t.key} onClick={() => setTab(t.key)}
            className={`-mb-px border-b-2 px-4 py-2 text-sm font-medium transition-colors ${tab === t.key ? 'border-primary text-primary' : 'border-transparent text-muted-foreground hover:text-foreground'}`}>
            {t.label}
          </button>
        ))}
      </div>

      {tab === 'info' && (
        <div className="rounded-lg border bg-[hsl(var(--card))] p-4">
          <div className="grid grid-cols-2 gap-4">
            <div className="space-y-2"><Label>Họ</Label><Input {...form.register('last_name')} /></div>
            <div className="space-y-2"><Label>Tên</Label><Input {...form.register('first_name')} /></div>
            <div className="space-y-2"><Label>Ngày sinh</Label><Input type="date" {...form.register('dob')} /></div>
            <div className="space-y-2">
              <Label>Giới tính</Label>
              <select className="h-8 w-full rounded-md border bg-[hsl(var(--card))] px-2 text-[13px] outline-none focus:ring-2 focus:ring-primary" {...form.register('gender')}>
                <option value="">Chọn</option><option value="male">Nam</option><option value="female">Nữ</option>
              </select>
            </div>
            <div className="space-y-2"><Label>Email cá nhân</Label><Input type="email" {...form.register('personal_email')} /></div>
            <div className="space-y-2"><Label>Số điện thoại</Label><Input {...form.register('phone')} /></div>
            <div className="space-y-2"><Label>Chi nhánh</Label><Input {...form.register('branch_id')} placeholder="ID chi nhánh" /></div>
            <div className="space-y-2"><Label>Phòng ban</Label><Input {...form.register('department_id')} placeholder="ID phòng ban" /></div>
            <div className="space-y-2"><Label>Chức vụ</Label><Input {...form.register('position_id')} placeholder="ID chức vụ" /></div>
          </div>
          <div className="mt-4 flex justify-end">
            <Button onClick={handleSave} disabled={updateEmp.isPending || transferEmp.isPending}>Lưu thay đổi</Button>
          </div>
        </div>
      )}

      {tab === 'contracts' && <ContractSection employeeId={id} />}

      {tab === 'documents' && (
        <div className="rounded-lg border bg-[hsl(var(--card))] p-4">
          <p className="text-muted-foreground">Tính năng tài liệu đang phát triển</p>
        </div>
      )}
    </div>
  );
}
