'use client';

import { useState, useCallback } from 'react';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { toast } from 'sonner';
import { Pencil, Plus, ToggleLeft, ToggleRight } from 'lucide-react';
import { useShiftTemplates, useCreateShiftTemplate, useUpdateShiftTemplate, useActivateShiftTemplate, useDeactivateShiftTemplate } from '@/domains/shift/hooks/useShift';
import type { ShiftTemplate } from '@/domains/shift/models/shift';
import { DataTable, type Column } from '@/shared/components/DataTable';
import { Drawer, DrawerContent, DrawerHeader, DrawerTitle, DrawerDescription, DrawerBody, DrawerFooter } from '@/shared/components/ui/drawer';
import { Button } from '@/shared/components/ui/button';
import { Input } from '@/shared/components/ui/input';
import { Label } from '@/shared/components/ui/label';
import { Badge } from '@/shared/components/ui/badge';
import { extractErrorMessage, extractFieldErrors } from '@/core/errors/messages';

const schema = z.object({
  code: z.string().min(2, 'Mã tối thiểu 2 ký tự').max(20),
  name: z.string().min(1, 'Tên không được để trống').max(255),
  start_time: z.string().min(1, 'Chọn giờ bắt đầu'),
  end_time: z.string().min(1, 'Chọn giờ kết thúc'),
  is_overnight: z.boolean().optional(),
  break_minutes: z.coerce.number().min(0).optional(),
  late_tolerance_minutes: z.coerce.number().min(0).optional(),
});

type FormData = z.infer<typeof schema>;

export function ShiftListPage() {
  const { data: templates, isLoading } = useShiftTemplates();
  const createShift = useCreateShiftTemplate();
  const updateShift = useUpdateShiftTemplate();
  const activateShift = useActivateShiftTemplate();
  const deactivateShift = useDeactivateShiftTemplate();

  const [dialogOpen, setDialogOpen] = useState(false);
  const [editing, setEditing] = useState<ShiftTemplate | null>(null);

  const form = useForm<FormData>({
    resolver: zodResolver(schema),
    defaultValues: { code: '', name: '', start_time: '', end_time: '', is_overnight: false, break_minutes: 0, late_tolerance_minutes: 0 },
  });

  const openCreate = useCallback(() => {
    setEditing(null);
    form.reset({ code: '', name: '', start_time: '', end_time: '', is_overnight: false, break_minutes: 0, late_tolerance_minutes: 0 });
    setDialogOpen(true);
  }, [form]);

  const openEdit = useCallback((t: ShiftTemplate) => {
    setEditing(t);
    form.reset({
      code: t.code, name: t.name, start_time: t.start_time, end_time: t.end_time,
      is_overnight: t.is_overnight, break_minutes: t.break_minutes, late_tolerance_minutes: t.late_tolerance_minutes,
    });
    setDialogOpen(true);
  }, [form]);

  const onSubmit = useCallback(async (values: FormData) => {
    try {
      if (editing) {
        await updateShift.mutateAsync({ id: editing.id, payload: values });
        toast.success('Cập nhật ca làm việc thành công');
      } else {
        await createShift.mutateAsync(values);
        toast.success('Tạo ca làm việc thành công');
      }
      setDialogOpen(false);
    } catch (raw) {
      const details = extractFieldErrors(raw);
      if (details.length > 0) details.forEach(({ field, message }) => form.setError(field as never, { message }));
      else toast.error(extractErrorMessage(raw));
    }
  }, [editing, createShift, updateShift, form]);

  const handleToggle = useCallback(async (t: ShiftTemplate) => {
    try {
      if (t.active) await deactivateShift.mutateAsync(t.id);
      else await activateShift.mutateAsync(t.id);
      toast.success(t.active ? 'Đã vô hiệu hóa' : 'Đã kích hoạt');
    } catch (raw) {
      toast.error(extractErrorMessage(raw));
    }
  }, [activateShift, deactivateShift]);

  const columns: Column<ShiftTemplate>[] = [
    { header: '#', accessor: undefined, cell: (_t, idx) => idx + 1, className: 'w-12', headerClassName: 'w-12' },
    { header: 'Mã', accessor: 'code', className: 'font-mono text-xs w-24' },
    { header: 'Tên ca', accessor: 'name' },
    { header: 'Giờ vào', accessor: 'start_time', className: 'font-mono text-xs w-20' },
    { header: 'Giờ ra', accessor: 'end_time', className: 'font-mono text-xs w-20' },
    { header: 'Qua đêm', accessor: undefined, cell: (t) => t.is_overnight ? 'Có' : '—', className: 'w-16' },
    { header: 'Nghỉ (ph)', accessor: 'break_minutes', className: 'text-right w-16' },
    {
      header: 'Trạng thái', accessor: undefined, className: 'w-20',
      cell: (t) => <Badge variant={t.active ? 'default' : 'secondary'}>{t.active ? 'Hoạt động' : 'Ngừng'}</Badge>,
    },
    {
      header: 'Thao tác', accessor: undefined, className: 'text-right w-20',
      cell: (t) => (
        <div className="flex justify-end gap-1">
          <Button variant="ghost" size="sm" title="Sửa" onClick={() => openEdit(t)}>
            <Pencil className="h-4 w-4" />
          </Button>
          <Button variant="ghost" size="sm" title={t.active ? 'Vô hiệu hóa' : 'Kích hoạt'} onClick={() => handleToggle(t)}>
            {t.active ? <ToggleLeft className="h-4 w-4" /> : <ToggleRight className="h-4 w-4" />}
          </Button>
        </div>
      ),
    },
  ];

  return (
    <div className="space-y-4">
      <div className="flex items-center justify-between">
        <Button onClick={openCreate}><Plus className="h-4 w-4 mr-1" /> Thêm ca làm việc</Button>
      </div>

      <DataTable<ShiftTemplate> columns={columns} data={templates ?? []} isLoading={isLoading} rowKey="id" emptyMessage="Chưa có ca làm việc nào" />

      <Drawer open={dialogOpen} onOpenChange={setDialogOpen}>
        <DrawerContent size="sm">
          <DrawerHeader>
            <DrawerTitle>{editing ? 'Sửa ca làm việc' : 'Thêm ca làm việc'}</DrawerTitle>
            <DrawerDescription>{editing ? 'Cập nhật thông tin ca làm việc' : 'Nhập thông tin ca làm việc mới'}</DrawerDescription>
          </DrawerHeader>
          <DrawerBody>
            <form id="shift-form" onSubmit={form.handleSubmit(onSubmit)} className="space-y-4">
              <div className="space-y-2">
                <Label htmlFor="code">Mã ca <span className="text-destructive">*</span></Label>
                <Input id="code" autoComplete="off" {...form.register('code')} disabled={!!editing} />
                {form.formState.errors.code && <p className="text-xs text-destructive">{form.formState.errors.code.message}</p>}
              </div>
              <div className="space-y-2">
                <Label htmlFor="name">Tên ca <span className="text-destructive">*</span></Label>
                <Input id="name" autoComplete="off" {...form.register('name')} />
                {form.formState.errors.name && <p className="text-xs text-destructive">{form.formState.errors.name.message}</p>}
              </div>
              <div className="grid grid-cols-2 gap-3">
                <div className="space-y-2">
                  <Label htmlFor="start_time">Giờ bắt đầu <span className="text-destructive">*</span></Label>
                  <Input id="start_time" type="time" autoComplete="off" {...form.register('start_time')} />
                </div>
                <div className="space-y-2">
                  <Label htmlFor="end_time">Giờ kết thúc <span className="text-destructive">*</span></Label>
                  <Input id="end_time" type="time" autoComplete="off" {...form.register('end_time')} />
                </div>
              </div>
              <div className="grid grid-cols-2 gap-3">
                <div className="space-y-2">
                  <Label htmlFor="break_minutes">Nghỉ (phút)</Label>
                  <Input id="break_minutes" type="number" autoComplete="off" {...form.register('break_minutes')} />
                </div>
                <div className="space-y-2">
                  <Label htmlFor="late_tolerance_minutes">Dung sai (phút)</Label>
                  <Input id="late_tolerance_minutes" type="number" autoComplete="off" {...form.register('late_tolerance_minutes')} />
                </div>
              </div>
              <div className="flex items-center gap-2">
                <input id="is_overnight" type="checkbox" className="h-4 w-4" {...form.register('is_overnight')} />
                <Label htmlFor="is_overnight">Qua đêm</Label>
              </div>
            </form>
          </DrawerBody>
          <DrawerFooter>
            <Button variant="ghost" type="button" onClick={() => setDialogOpen(false)}>Hủy</Button>
            <Button type="submit" form="shift-form" disabled={createShift.isPending || updateShift.isPending}>
              {editing ? 'Cập nhật' : 'Tạo'}
            </Button>
          </DrawerFooter>
        </DrawerContent>
      </Drawer>
    </div>
  );
}
