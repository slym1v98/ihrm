'use client';

import { useState, useCallback } from 'react';
import { useForm } from 'react-hook-form';
import { z } from 'zod';
import { zodResolver } from '@hookform/resolvers/zod';
import { toast } from 'sonner';
import { useRouter } from 'next/navigation';
import { Pencil } from 'lucide-react';
import { useEmployees, useCreateEmployee } from '@/domains/employee/hooks/useEmployees';
import { extractErrorMessage } from '@/core/errors/messages';
import type { Employee } from '@/domains/employee/models/employee';
import { DataTable, type Column } from '@/shared/components/DataTable';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter } from '@/shared/components/ui/dialog';
import { Button } from '@/shared/components/ui/button';
import { Input } from '@/shared/components/ui/input';
import { Label } from '@/shared/components/ui/label';
import { Badge } from '@/shared/components/ui/badge';

const createSchema = z.object({
  first_name: z.string().min(1, 'Tên không được để trống').max(100),
  last_name: z.string().min(1, 'Họ không được để trống').max(100),
});
type CreateForm = z.infer<typeof createSchema>;

export function EmployeeListPage() {
  const router = useRouter();
  const { data, isLoading } = useEmployees();
  const createEmp = useCreateEmployee();
  const [dialogOpen, setDialogOpen] = useState(false);

  const form = useForm<CreateForm, unknown>({
    resolver: zodResolver(createSchema),
    defaultValues: { first_name: '', last_name: '' },
  });

  const onSubmit = useCallback(async (v: CreateForm) => {
    try {
      const emp = await createEmp.mutateAsync(v);
      toast.success('Tạo nhân viên thành công');
      setDialogOpen(false);
      router.push(`/employees/${emp.id}`);
    } catch (raw) {
      toast.error(extractErrorMessage(raw));
    }
  }, [createEmp, router]);

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
        <div>
          <h1 className="text-2xl font-semibold">Nhân viên</h1>
          <p className="text-sm text-muted-foreground">Quản lý danh sách nhân viên</p>
        </div>
        <Button onClick={() => { form.reset({ first_name: '', last_name: '' }); setDialogOpen(true); }}>+ Thêm nhân viên</Button>
      </div>

      <DataTable<Employee> columns={columns} data={employees} isLoading={isLoading} rowKey="id" emptyMessage="Chưa có nhân viên nào" />

      <Dialog open={dialogOpen} onOpenChange={setDialogOpen}>
        <DialogContent>
          <DialogHeader>
            <DialogTitle>Thêm nhân viên</DialogTitle>
            <DialogDescription>Nhập họ và tên nhân viên mới. Các thông tin khác có thể bổ sung sau.</DialogDescription>
          </DialogHeader>
          <form onSubmit={form.handleSubmit(onSubmit)} className="space-y-4">
            <div className="space-y-2">
              <Label htmlFor="last_name">Họ <span className="text-destructive">*</span></Label>
              <Input id="last_name" {...form.register('last_name')} />
              {form.formState.errors.last_name && <p className="text-xs text-destructive">{form.formState.errors.last_name.message}</p>}
            </div>
            <div className="space-y-2">
              <Label htmlFor="first_name">Tên <span className="text-destructive">*</span></Label>
              <Input id="first_name" {...form.register('first_name')} />
              {form.formState.errors.first_name && <p className="text-xs text-destructive">{form.formState.errors.first_name.message}</p>}
            </div>
            <DialogFooter>
              <Button variant="ghost" type="button" onClick={() => setDialogOpen(false)}>Hủy</Button>
              <Button type="submit" disabled={createEmp.isPending}>Tạo</Button>
            </DialogFooter>
          </form>
        </DialogContent>
      </Dialog>
    </div>
  );
}
