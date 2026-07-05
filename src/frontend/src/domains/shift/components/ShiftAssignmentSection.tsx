'use client';

import { useState, useCallback } from 'react';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { toast } from 'sonner';
import { Plus, Ban } from 'lucide-react';
import { useShiftTemplates, useShiftAssignments, useCreateShiftAssignment, useEndShiftAssignment } from '@/domains/shift/hooks/useShift';
import { useEmployees } from '@/domains/employee/hooks/useEmployees';
import { useDepartments } from '@/domains/organization/hooks/useDepartments';
import { useEmployeeDisplayName } from '@/shared/hooks/useEmployeeDisplayName';
import type { ShiftAssignment } from '@/domains/shift/models/shift';
import { DataTable, type Column } from '@/shared/components/DataTable';
import { Drawer, DrawerContent, DrawerHeader, DrawerTitle, DrawerDescription, DrawerBody, DrawerFooter } from '@/shared/components/ui/drawer';
import { Button } from '@/shared/components/ui/button';
import { Input } from '@/shared/components/ui/input';
import { Label } from '@/shared/components/ui/label';
import { Badge } from '@/shared/components/ui/badge';
import { extractErrorMessage } from '@/core/errors/messages';
import { useDateFormatter } from '@/shared/hooks/useDateFormatter';

const schema = z.object({
  shift_template_id: z.string().min(1, 'Chọn ca làm việc'),
  assignable_type: z.enum(['employee', 'department']),
  assignable_id: z.string().min(1, 'Chọn đối tượng'),
  effective_from: z.string().min(1, 'Chọn ngày hiệu lực'),
  effective_to: z.string().optional(),
});

type FormData = z.infer<typeof schema>;

export function ShiftAssignmentSection() {
  const { formatDate } = useDateFormatter();
  const { data: assignments, isLoading } = useShiftAssignments();
  const { data: templates } = useShiftTemplates();
  const { data: employeeData } = useEmployees();
  const employees = employeeData?.data ?? [];
  const { data: departmentData } = useDepartments();
  const departments = departmentData?.data ?? [];
  const { getDisplayName } = useEmployeeDisplayName();

  const createAssignment = useCreateShiftAssignment();
  const endAssignment = useEndShiftAssignment();

  const [dialogOpen, setDialogOpen] = useState(false);
  const [assignableType, setAssignableType] = useState<'employee' | 'department'>('employee');

  const form = useForm<FormData>({
    resolver: zodResolver(schema),
    defaultValues: { shift_template_id: '', assignable_type: 'employee', assignable_id: '', effective_from: '', effective_to: '' },
  });

  const onSubmit = useCallback(async (values: FormData) => {
    try {
      await createAssignment.mutateAsync(values);
      toast.success('Phân ca thành công');
      setDialogOpen(false);
      form.reset();
    } catch (raw) {
      toast.error(extractErrorMessage(raw));
    }
  }, [createAssignment, form]);

  const handleEnd = useCallback(async (id: string) => {
    try {
      await endAssignment.mutateAsync(id);
      toast.success('Đã kết thúc phân ca');
    } catch (raw) {
      toast.error(extractErrorMessage(raw));
    }
  }, [endAssignment]);

  const resolveAssigneeName = (a: ShiftAssignment) => {
    if (a.assignable_type === 'employee') return getDisplayName(a.assignable_id);
    return departments.find(d => d.id === a.assignable_id)?.name ?? a.assignable_id;
  };

  const columns: Column<ShiftAssignment>[] = [
    { header: 'Ca làm việc', accessor: undefined, cell: (a) => templates?.find(t => t.id === a.shift_template_id)?.name ?? a.shift_template_id },
    { header: 'Loại', accessor: undefined, cell: (a) => a.assignable_type === 'employee' ? 'Nhân viên' : 'Phòng ban', className: 'w-24' },
    { header: 'Đối tượng', accessor: undefined, cell: resolveAssigneeName },
    { header: 'Hiệu lực từ', accessor: undefined, cell: (a) => formatDate(a.effective_from), className: 'font-mono text-xs w-28' },
    { header: 'Đến', accessor: undefined, cell: (a) => a.effective_to ? formatDate(a.effective_to) : '—', className: 'font-mono text-xs w-28' },
    { header: 'Trạng thái', accessor: undefined, className: 'w-28', cell: (a) => <Badge variant={a.active ? 'default' : 'secondary'}>{a.active ? 'Đang áp dụng' : 'Đã kết thúc'}</Badge> },
    { header: '', accessor: undefined, className: 'text-right w-12', cell: (a) => a.active
        ? <Button variant="ghost" size="sm" title="Kết thúc" onClick={() => handleEnd(a.id)}><Ban className="h-4 w-4 text-destructive" /></Button>
        : null },
  ];

  return (
    <div className="space-y-4">
      <div className="flex items-center justify-between">
        <Button onClick={() => { form.reset(); setAssignableType('employee'); setDialogOpen(true); }}>
          <Plus className="h-4 w-4 mr-1" /> Phân ca
        </Button>
      </div>

      <DataTable<ShiftAssignment> columns={columns} data={assignments ?? []} isLoading={isLoading} rowKey="id" emptyMessage="Chưa có phân ca nào" />

      <Drawer open={dialogOpen} onOpenChange={setDialogOpen}>
        <DrawerContent size="sm">
          <DrawerHeader>
            <DrawerTitle>Phân ca làm việc</DrawerTitle>
            <DrawerDescription>Chọn ca và đối tượng áp dụng</DrawerDescription>
          </DrawerHeader>
          <DrawerBody>
            <form id="assignment-form" onSubmit={form.handleSubmit(onSubmit)} className="space-y-4">
              <div className="space-y-2">
                <Label htmlFor="shift_template_id">Ca làm việc <span className="text-destructive">*</span></Label>
                <select id="shift_template_id" className="h-8 w-full rounded-md border bg-[hsl(var(--card))] px-2 py-1 text-[13px] outline-none focus:ring-2 focus:ring-primary" {...form.register('shift_template_id')}>
                  <option value="">Chọn ca</option>
                  {templates?.filter(t => t.active).map(t => <option key={t.id} value={t.id}>{t.name} ({t.code})</option>)}
                </select>
                {form.formState.errors.shift_template_id && <p className="text-xs text-destructive">{form.formState.errors.shift_template_id.message}</p>}
              </div>

              <div className="space-y-2">
                <Label>Đối tượng <span className="text-destructive">*</span></Label>
                <div className="flex gap-2">
                  <Button type="button" size="sm" variant={assignableType === 'employee' ? 'primary' : 'ghost'}
                    onClick={() => { setAssignableType('employee'); form.setValue('assignable_type', 'employee'); form.setValue('assignable_id', ''); }}>
                    Nhân viên
                  </Button>
                  <Button type="button" size="sm" variant={assignableType === 'department' ? 'primary' : 'ghost'}
                    onClick={() => { setAssignableType('department'); form.setValue('assignable_type', 'department'); form.setValue('assignable_id', ''); }}>
                    Phòng ban
                  </Button>
                </div>
              </div>

              <div className="space-y-2">
                <Label htmlFor="assignable_id">{assignableType === 'employee' ? 'Nhân viên' : 'Phòng ban'} <span className="text-destructive">*</span></Label>
                <select id="assignable_id" className="h-8 w-full rounded-md border bg-[hsl(var(--card))] px-2 py-1 text-[13px] outline-none focus:ring-2 focus:ring-primary" {...form.register('assignable_id')}>
                  <option value="">Chọn</option>
                  {assignableType === 'employee'
                    ? employees.map(e => <option key={e.id} value={e.id}>[{e.employee_code}] {e.last_name} {e.first_name}</option>)
                    : departments.map(d => <option key={d.id} value={d.id}>{d.name}</option>)}
                </select>
                {form.formState.errors.assignable_id && <p className="text-xs text-destructive">{form.formState.errors.assignable_id.message}</p>}
              </div>

              <div className="grid grid-cols-2 gap-3">
                <div className="space-y-2">
                  <Label htmlFor="effective_from">Hiệu lực từ <span className="text-destructive">*</span></Label>
                  <Input id="effective_from" type="date" autoComplete="off" {...form.register('effective_from')} />
                  {form.formState.errors.effective_from && <p className="text-xs text-destructive">{form.formState.errors.effective_from.message}</p>}
                </div>
                <div className="space-y-2">
                  <Label htmlFor="effective_to">Đến (tùy chọn)</Label>
                  <Input id="effective_to" type="date" autoComplete="off" {...form.register('effective_to')} />
                </div>
              </div>
            </form>
          </DrawerBody>
          <DrawerFooter>
            <Button variant="ghost" type="button" onClick={() => setDialogOpen(false)}>Hủy</Button>
            <Button type="submit" form="assignment-form" disabled={createAssignment.isPending}>Phân ca</Button>
          </DrawerFooter>
        </DrawerContent>
      </Drawer>
    </div>
  );
}
