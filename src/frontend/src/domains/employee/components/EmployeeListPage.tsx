'use client';

import { useState, useCallback } from 'react';
import { toast } from 'sonner';
import { useRouter } from 'next/navigation';
import { Pencil } from 'lucide-react';
import { useEmployees, useCreateEmployee } from '@/domains/employee/hooks/useEmployees';
import { extractErrorMessage } from '@/core/errors/messages';
import type { Employee } from '@/domains/employee/models/employee';
import { DataTable, type Column } from '@/shared/components/DataTable';
import { Drawer, DrawerContent, DrawerHeader, DrawerTitle, DrawerDescription, DrawerBody, DrawerFooter } from '@/shared/components/ui/drawer';
import { Button } from '@/shared/components/ui/button';
import { Input } from '@/shared/components/ui/input';
import { Label } from '@/shared/components/ui/label';
import { Badge } from '@/shared/components/ui/badge';

export function EmployeeListPage() {
  const router = useRouter();
  const { data, isLoading } = useEmployees();
  const createEmp = useCreateEmployee();
  const [dialogOpen, setDialogOpen] = useState(false);
  const [firstName, setFirstName] = useState('');
  const [lastName, setLastName] = useState('');

  const openCreate = useCallback(() => {
    setFirstName('');
    setLastName('');
    setDialogOpen(true);
  }, []);

  const handleSubmit = useCallback(async (e: React.FormEvent) => {
    e.preventDefault();
    if (!firstName.trim() || !lastName.trim()) return;
    try {
      const emp = await createEmp.mutateAsync({ first_name: firstName.trim(), last_name: lastName.trim() });
      toast.success('Tạo nhân viên thành công');
      setDialogOpen(false);
      router.push(`/employees/${emp.id}`);
    } catch (raw) {
      toast.error(extractErrorMessage(raw));
    }
  }, [firstName, lastName, createEmp, router]);

  const employees: Employee[] = data?.data ?? [];

  const columns: Column<Employee>[] = [
    { header: 'Mã NV', accessor: 'employee_code', className: 'font-mono text-xs' },
    { header: 'Họ', accessor: 'last_name' },
    { header: 'Tên', accessor: 'first_name' },
    { header: 'Email', accessor: 'personal_email', cell: (e) => e.personal_email ?? '—', className: 'text-muted-foreground' },
    { header: 'Trạng thái', accessor: undefined as never, cell: (e) => <Badge variant={e.status === 'active' ? 'default' : 'secondary'}>{e.status === 'active' ? 'Đang làm' : 'Đã nghỉ'}</Badge> },
    { header: '', accessor: undefined as never, className: 'text-right', cell: (e) => (
      <Button variant="ghost" size="sm" title="Xem chi tiết" onClick={() => router.push(`/employees/${e.id}`)}>
        <Pencil className="h-4 w-4" />
      </Button>
    )},
  ];

  return (
    <div className="space-y-4">
      <div className="flex items-center justify-between">
        <Button onClick={openCreate}>+ Thêm nhân viên</Button>
      </div>

      <DataTable<Employee> columns={columns} data={employees} isLoading={isLoading} rowKey="id" emptyMessage="Chưa có nhân viên nào" />

      <Drawer open={dialogOpen} onOpenChange={(o) => { if (!o) setDialogOpen(false); }}>
        <DrawerContent size="sm">
          <DrawerHeader>
            <DrawerTitle>Thêm nhân viên</DrawerTitle>
            <DrawerDescription>Nhập họ và tên nhân viên mới</DrawerDescription>
          </DrawerHeader>
          <DrawerBody>
<form id="drawer-form" onSubmit={handleSubmit} className="space-y-4">
<div className="space-y-2">
              <Label htmlFor="last_name">Họ <span className="text-destructive">*</span></Label>
              <Input id="last_name" value={lastName} onChange={(e) => setLastName(e.target.value)} required />
            </div>
            <div className="space-y-2">
              <Label htmlFor="first_name">Tên <span className="text-destructive">*</span></Label>
              <Input id="first_name" value={firstName} onChange={(e) => setFirstName(e.target.value)} required />
            </div>
</form>
</DrawerBody>
<DrawerFooter>
<Button variant="ghost" type="button" onClick={() => setDialogOpen(false)}>Hủy</Button>
              <Button type="submit" form="drawer-form" disabled={createEmp.isPending}>Tạo</Button>
            
</DrawerFooter>

        </DrawerContent>
      </Drawer>
    </div>
  );
}
