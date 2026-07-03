'use client';

import { useState, useEffect, useCallback, useMemo } from 'react';
import { toast } from 'sonner';
import { useParams, useRouter } from 'next/navigation';
import { useEmployee, useUpdateEmployee, useTransferEmployee, useChangeEmployeeStatus } from '@/domains/employee/hooks/useEmployees';
import { useContracts } from '@/domains/employee/hooks/useContracts';
import { useBranches } from '@/domains/organization/hooks/useBranches';
import { useDepartments } from '@/domains/organization/hooks/useDepartments';
import { usePositions } from '@/domains/organization/hooks/usePositions';
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
  const { data: branchesData } = useBranches();
  const { data: departmentsData } = useDepartments();
  const { data: positionsData } = usePositions();
  const [tab, setTab] = useState<Tab>('info');

  const [form, setForm] = useState({
    first_name: '', last_name: '', dob: '', gender: '',
    personal_email: '', phone: '', branch_id: '', department_id: '', position_id: '',
  });

  useEffect(() => {
    if (!employee) return;
    setForm({
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
  }, [employee]);

  const branches = useMemo(() => branchesData?.data ?? [], [branchesData]);
  const departments = useMemo(() => departmentsData?.data ?? [], [departmentsData]);
  const positions = useMemo(() => positionsData?.data ?? [], [positionsData]);

  const filteredDepts = useMemo(() => {
    if (!form.branch_id) return [];
    return departments.filter(d => d.branch_id === form.branch_id && d.status === 'active');
  }, [departments, form.branch_id]);


  const setField = useCallback((field: string, value: string) => {
    setForm(prev => {
      const next = { ...prev, [field]: value };
      // Clear department when branch changes
      if (field === 'branch_id') next.department_id = '';
      return next;
    });
  }, []);

  const handleSave = useCallback(async () => {
    try {
      await updateEmp.mutateAsync({
        id,
        payload: {
          first_name: form.first_name,
          last_name: form.last_name,
          dob: form.dob || undefined,
          gender: form.gender || undefined,
          personal_email: form.personal_email || undefined,
          phone: form.phone || undefined,
        },
      });
      if (form.branch_id || form.department_id || form.position_id) {
        await transferEmp.mutateAsync({
          id,
          payload: {
            branch_id: form.branch_id || undefined,
            department_id: form.department_id || undefined,
            position_id: form.position_id || undefined,
          },
        });
      }
      toast.success('Cập nhật thành công');
    } catch (raw) {
      toast.error(extractErrorMessage(raw));
    }
  }, [id, form, updateEmp, transferEmp]);

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
        <Button variant="ghost" size="sm" onClick={() => router.push('/employees')} type="button">
          <ChevronLeft className="h-4 w-4" />
        </Button>
        <div className="flex-1">
          <h1 className="text-2xl font-semibold">{employee.last_name} {employee.first_name}</h1>
          <p className="text-sm text-muted-foreground">
            {employee.employee_code} ·{' '}
            <Badge variant={employee.status === 'active' ? 'default' : 'secondary'}>
              {employee.status === 'active' ? 'Đang làm' : 'Đã nghỉ'}
            </Badge>
          </p>
        </div>
        <Button
          variant={employee.status === 'active' ? 'destructive' : 'primary'}
          size="sm"
          onClick={handleToggleStatus}
          disabled={changeStatus.isPending}
          type="button"
        >
          {employee.status === 'active' ? 'Vô hiệu hóa' : 'Kích hoạt'}
        </Button>
      </div>

      <div className="flex gap-1 border-b">
        {tabs.map(t => (
          <button
            key={t.key}
            type="button"
            onClick={() => setTab(t.key)}
            className={`-mb-px border-b-2 px-4 py-2 text-sm font-medium transition-colors ${
              tab === t.key
                ? 'border-primary text-primary'
                : 'border-transparent text-muted-foreground hover:text-foreground'
            }`}
          >
            {t.label}
          </button>
        ))}
      </div>

      {tab === 'info' && (
        <div className="rounded-lg border bg-[hsl(var(--card))] p-4">
          <div className="grid grid-cols-2 gap-4">
            <div className="space-y-2">
              <Label htmlFor="ed-last_name">Họ</Label>
              <Input id="ed-last_name" value={form.last_name} onChange={e => setField('last_name', e.target.value)} />
            </div>
            <div className="space-y-2">
              <Label htmlFor="ed-first_name">Tên</Label>
              <Input id="ed-first_name" value={form.first_name} onChange={e => setField('first_name', e.target.value)} />
            </div>
            <div className="space-y-2">
              <Label htmlFor="ed-dob">Ngày sinh</Label>
              <Input id="ed-dob" type="date" value={form.dob} onChange={e => setField('dob', e.target.value)}
                className="[&::-webkit-calendar-picker-indicator]:cursor-pointer [&::-webkit-calendar-picker-indicator]:opacity-100" />
            </div>
            <div className="space-y-2">
              <Label htmlFor="ed-gender">Giới tính</Label>
              <select id="ed-gender" className="h-8 w-full rounded-md border bg-[hsl(var(--card))] px-2 text-[13px] outline-none focus:ring-2 focus:ring-primary"
                value={form.gender} onChange={e => setField('gender', e.target.value)}>
                <option value="">Chọn</option>
                <option value="male">Nam</option>
                <option value="female">Nữ</option>
              </select>
            </div>
            <div className="space-y-2">
              <Label htmlFor="ed-email">Email cá nhân</Label>
              <Input id="ed-email" type="email" value={form.personal_email} onChange={e => setField('personal_email', e.target.value)} />
            </div>
            <div className="space-y-2">
              <Label htmlFor="ed-phone">Số điện thoại</Label>
              <Input id="ed-phone" value={form.phone} onChange={e => setField('phone', e.target.value)} />
            </div>
            <div className="space-y-2">
              <Label htmlFor="ed-branch">Chi nhánh</Label>
              <select id="ed-branch" className="h-8 w-full rounded-md border bg-[hsl(var(--card))] px-2 text-[13px] outline-none focus:ring-2 focus:ring-primary"
                value={form.branch_id} onChange={e => { setField('branch_id', e.target.value); }}>
                <option value="">Chọn chi nhánh</option>
                {branches.map(b => <option key={b.id} value={b.id}>{b.name}</option>)}
              </select>
            </div>
            <div className="space-y-2">
              <Label htmlFor="ed-dept">Phòng ban</Label>
              <select id="ed-dept" className="h-8 w-full rounded-md border bg-[hsl(var(--card))] px-2 text-[13px] outline-none focus:ring-2 focus:ring-primary"
                value={form.department_id} onChange={e => setField('department_id', e.target.value)} disabled={!form.branch_id}>
                <option value="">{form.branch_id ? 'Chọn phòng ban' : 'Chọn chi nhánh trước'}</option>
                {filteredDepts.map(d => <option key={d.id} value={d.id}>{d.name}</option>)}
              </select>
            </div>
            <div className="space-y-2">
              <Label htmlFor="ed-pos">Chức vụ</Label>
              <select id="ed-pos" className="h-8 w-full rounded-md border bg-[hsl(var(--card))] px-2 text-[13px] outline-none focus:ring-2 focus:ring-primary"
                value={form.position_id} onChange={e => setField('position_id', e.target.value)}>
                <option value="">Chọn chức vụ</option>
                {positions.map(p => <option key={p.id} value={p.id}>{p.name}</option>)}
              </select>
            </div>
          </div>
          <div className="mt-4 flex justify-end">
            <Button onClick={handleSave} disabled={updateEmp.isPending || transferEmp.isPending} type="button">Lưu thay đổi</Button>
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
