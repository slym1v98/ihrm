'use client';

import { useState, useCallback } from 'react';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { toast } from 'sonner';
import { Pencil, Plus, Trash2 } from 'lucide-react';
import { useTrainingCourses, useCreateCourse, useUpdateCourse, useDeleteCourse } from '@/domains/training/hooks/useTraining';
import type { TrainingCourse } from '@/domains/training/models/training';
import { DataTable, type Column } from '@/shared/components/DataTable';
import { Drawer, DrawerContent, DrawerHeader, DrawerTitle, DrawerDescription, DrawerBody, DrawerFooter } from '@/shared/components/ui/drawer';
import { Button } from '@/shared/components/ui/button';
import { Input } from '@/shared/components/ui/input';
import { Label } from '@/shared/components/ui/label';
import { Badge } from '@/shared/components/ui/badge';
import { extractErrorMessage } from '@/core/errors/messages';

const schema = z.object({
  code: z.string().min(2, 'Mã tối thiểu 2 ký tự'),
  name: z.string().min(1, 'Tên không được để trống'),
  description: z.string().optional(),
  category: z.string().optional(),
  default_duration_hours: z.coerce.number().min(0).optional(),
  max_participants: z.coerce.number().min(1).optional(),
});

type FormData = z.infer<typeof schema>;

export function TrainingListPage() {
  const { data: courses, isLoading } = useTrainingCourses();
  const createCourse = useCreateCourse();
  const updateCourse = useUpdateCourse();
  const deleteCourse = useDeleteCourse();

  const [dialogOpen, setDialogOpen] = useState(false);
  const [editing, setEditing] = useState<TrainingCourse | null>(null);

  const form = useForm<FormData>({
    resolver: zodResolver(schema),
    defaultValues: { code: '', name: '', description: '', category: '', default_duration_hours: 0, max_participants: 30 },
  });

  const openCreate = useCallback(() => {
    setEditing(null);
    form.reset({ code: '', name: '', description: '', category: '', default_duration_hours: 0, max_participants: 30 });
    setDialogOpen(true);
  }, [form]);

  const openEdit = useCallback((c: TrainingCourse) => {
    setEditing(c);
    form.reset({
      code: c.code, name: c.name, description: c.description ?? '', category: c.category ?? '',
      default_duration_hours: c.default_duration_hours, max_participants: c.max_participants,
    });
    setDialogOpen(true);
  }, [form]);

  const onSubmit = useCallback(async (values: FormData) => {
    try {
      if (editing) { await updateCourse.mutateAsync({ id: editing.id, payload: values }); toast.success('Cập nhật khoá học thành công'); }
      else { await createCourse.mutateAsync(values); toast.success('Tạo khoá học thành công'); }
      setDialogOpen(false);
    } catch (raw) { toast.error(extractErrorMessage(raw)); }
  }, [editing, createCourse, updateCourse]);

  const handleDelete = useCallback(async (id: string, name: string) => {
    if (!confirm(`Xoá khoá học "${name}"?`)) return;
    try { await deleteCourse.mutateAsync(id); toast.success('Đã xoá'); }
    catch (raw) { toast.error(extractErrorMessage(raw)); }
  }, [deleteCourse]);

  const columns: Column<TrainingCourse>[] = [
    { header: 'Mã', accessor: 'code', className: 'font-mono text-xs w-24' },
    { header: 'Tên khoá học', accessor: 'name' },
    { header: 'Danh mục', accessor: 'category', cell: (c) => c.category ?? '—', className: 'w-24 text-muted-foreground' },
    { header: 'Giờ', accessor: 'default_duration_hours', className: 'text-right w-16' },
    { header: 'SL tối đa', accessor: 'max_participants', className: 'text-right w-20' },
    { header: 'Trạng thái', accessor: undefined, className: 'w-20',
      cell: (c) => <Badge variant={c.active ? 'default' : 'secondary'}>{c.active ? 'Hoạt động' : 'Ngừng'}</Badge> },
    { header: 'Thao tác', accessor: undefined, className: 'text-right w-20',
      cell: (c) => (
        <div className="flex justify-end gap-1">
          <Button variant="ghost" size="sm" title="Sửa" onClick={() => openEdit(c)}><Pencil className="h-4 w-4" /></Button>
          <Button variant="ghost" size="sm" title="Xoá" onClick={() => handleDelete(c.id, c.name)}><Trash2 className="h-4 w-4 text-destructive" /></Button>
        </div>
      )},
  ];

  return (
    <div className="space-y-4">
      <div className="flex items-center justify-between">
        <Button onClick={openCreate}><Plus className="h-4 w-4 mr-1" /> Thêm khoá học</Button>
      </div>
      <DataTable<TrainingCourse> columns={columns} data={courses ?? []} isLoading={isLoading} rowKey="id" emptyMessage="Chưa có khoá học nào" />

      <Drawer open={dialogOpen} onOpenChange={setDialogOpen}>
        <DrawerContent size="sm">
          <DrawerHeader>
            <DrawerTitle>{editing ? 'Sửa khoá học' : 'Thêm khoá học'}</DrawerTitle>
            <DrawerDescription>{editing ? 'Cập nhật thông tin khoá học' : 'Nhập thông tin khoá học mới'}</DrawerDescription>
          </DrawerHeader>
          <DrawerBody>
            <form id="course-form" onSubmit={form.handleSubmit(onSubmit)} className="space-y-4">
              <div className="space-y-2">
                <Label htmlFor="code">Mã khoá học <span className="text-destructive">*</span></Label>
                <Input id="code" autoComplete="off" {...form.register('code')} disabled={!!editing} />
              </div>
              <div className="space-y-2">
                <Label htmlFor="name">Tên khoá học <span className="text-destructive">*</span></Label>
                <Input id="name" autoComplete="off" {...form.register('name')} />
              </div>
              <div className="space-y-2">
                <Label htmlFor="category">Danh mục</Label>
                <Input id="category" autoComplete="off" {...form.register('category')} />
              </div>
              <div className="grid grid-cols-2 gap-3">
                <div className="space-y-2">
                  <Label htmlFor="default_duration_hours">Số giờ</Label>
                  <Input id="default_duration_hours" type="number" autoComplete="off" {...form.register('default_duration_hours')} />
                </div>
                <div className="space-y-2">
                  <Label htmlFor="max_participants">SL tối đa</Label>
                  <Input id="max_participants" type="number" autoComplete="off" {...form.register('max_participants')} />
                </div>
              </div>
              <div className="space-y-2">
                <Label htmlFor="description">Mô tả</Label>
                <textarea id="description" className="h-20 w-full rounded-md border bg-[hsl(var(--card))] px-2 py-1 text-[13px] outline-none focus:ring-2 focus:ring-primary resize-none" {...form.register('description')} />
              </div>
            </form>
          </DrawerBody>
          <DrawerFooter>
            <Button variant="ghost" type="button" onClick={() => setDialogOpen(false)}>Hủy</Button>
            <Button type="submit" form="course-form" disabled={createCourse.isPending || updateCourse.isPending}>{editing ? 'Cập nhật' : 'Tạo'}</Button>
          </DrawerFooter>
        </DrawerContent>
      </Drawer>
    </div>
  );
}
