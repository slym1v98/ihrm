'use client';

import { useState, useCallback } from 'react';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { toast } from 'sonner';
import { Pencil, ToggleLeft, ToggleRight } from 'lucide-react';
import { usePositions, useCreatePosition, useUpdatePosition, useTogglePositionStatus } from '@/domains/organization/hooks/usePositions';
import type { Position } from '@/domains/organization/models/position';
import { DataTable, type Column } from '@/shared/components/DataTable';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter } from '@/shared/components/ui/dialog';
import { Button } from '@/shared/components/ui/button';
import { Input } from '@/shared/components/ui/input';
import { Label } from '@/shared/components/ui/label';
import { Textarea } from '@/shared/components/ui/textarea';
import { Badge } from '@/shared/components/ui/badge';
import { extractErrorMessage, extractFieldErrors } from '@/core/errors/messages';

const positionSchema = z.object({
  code: z.string().min(2, 'Mã tối thiểu 2 ký tự').regex(/^[A-Za-z][A-Za-z0-9-]+$/, 'Chỉ chấp nhận chữ, số, dấu gạch ngang'),
  name: z.string().min(1, 'Tên không được để trống'),
  level: z.string().optional(),
  description: z.string().optional(),
});

type PositionFormData = z.infer<typeof positionSchema>;

function toPayload(values: PositionFormData) {
  return {
    code: values.code, name: values.name,
    level: values.level ? parseInt(values.level) : undefined,
    description: values.description || undefined,
  };
}

export function PositionListPage() {
  const { data, isLoading } = usePositions();
  const createPosition = useCreatePosition();
  const updatePosition = useUpdatePosition();
  const toggleStatus = useTogglePositionStatus();
  const [dialogOpen, setDialogOpen] = useState(false);
  const [editing, setEditing] = useState<Position | null>(null);
  const [confirm, setConfirm] = useState<{ id: string; action: 'activate' | 'deactivate'; name: string } | null>(null);

  const form = useForm<PositionFormData, unknown>({
    resolver: zodResolver(positionSchema),
    defaultValues: { code: '', name: '', level: '', description: '' },
  });

  const openCreate = useCallback(() => {
    setEditing(null);
    form.reset({ code: '', name: '', level: '', description: '' });
    setDialogOpen(true);
  }, [form]);

  const openEdit = useCallback((pos: Position) => {
    setEditing(pos);
    form.reset({ code: pos.code, name: pos.name, level: pos.level?.toString() ?? '', description: pos.description ?? '' });
    setDialogOpen(true);
  }, [form]);

  const onSubmit = useCallback(async (values: PositionFormData) => {
    type ApiError = { response?: { data?: { error?: { details?: { field: string; message: string }[]; message?: string } } } };
    try {
      if (editing) {
        await updatePosition.mutateAsync({ id: editing.id, payload: toPayload(values) });
        toast.success('Cập nhật chức vụ thành công');
      } else {
        await createPosition.mutateAsync(toPayload(values));
        toast.success('Tạo chức vụ thành công');
      }
      setDialogOpen(false);
    } catch (raw) {
      const details = extractFieldErrors(raw);
      if (details.length > 0) details.forEach(({ field, message }) => form.setError(field as never, { message }));
      else toast.error(extractErrorMessage(raw));
    }
  }, [editing, createPosition, updatePosition, form]);

  const positions = data?.data ?? [];

  const columns: Column<Position>[] = [
    { header: '#', accessor: undefined as never, cell: (_p, idx) => idx + 1, className: 'w-12', headerClassName: 'w-12' },
    { header: 'Mã', accessor: 'code', className: 'font-mono text-xs' },
    { header: 'Tên', accessor: 'name' },
    { header: 'Cấp bậc', accessor: undefined as never, cell: (p) => p.level ?? '—', className: 'w-20 text-center', headerClassName: 'w-20 text-center' },
    { header: 'Mô tả', accessor: undefined as never, cell: (p) => p.description ?? '—', className: 'max-w-xs truncate text-muted-foreground' },
    { header: 'Trạng thái', accessor: undefined as never, cell: (p) => <Badge variant={p.status === 'active' ? 'default' : 'secondary'}>{p.status === 'active' ? 'Hoạt động' : 'Ngừng'}</Badge> },
    {
      header: 'Thao tác', accessor: undefined as never, className: 'text-right',
      cell: (p) => (
        <div className="flex justify-end gap-1">
          <Button variant="ghost" size="sm" title="Sửa" onClick={() => openEdit(p)}><Pencil className="h-4 w-4" /></Button>
          <Button variant="ghost" size="sm" title={p.status === 'active' ? 'Vô hiệu hóa' : 'Kích hoạt'}
            onClick={() => setConfirm({ id: p.id, action: p.status === 'active' ? 'deactivate' : 'activate', name: p.name })}>
            {p.status === 'active' ? <ToggleLeft className="h-4 w-4" /> : <ToggleRight className="h-4 w-4" />}
          </Button>
        </div>
      ),
    },
  ];

  return (
    <div className="space-y-4">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-semibold">Chức vụ</h1>
          <p className="text-sm text-muted-foreground">Quản lý danh sách chức vụ trong công ty</p>
        </div>
        <Button onClick={openCreate}>+ Thêm chức vụ</Button>
      </div>

      <DataTable<Position> columns={columns} data={positions} isLoading={isLoading} rowKey="id" emptyMessage="Chưa có chức vụ nào" />

      <Dialog open={dialogOpen} onOpenChange={setDialogOpen}>
        <DialogContent>
          <DialogHeader>
            <DialogTitle>{editing ? 'Sửa chức vụ' : 'Thêm chức vụ'}</DialogTitle>
            <DialogDescription>{editing ? 'Cập nhật thông tin chức vụ' : 'Nhập thông tin chức vụ mới'}</DialogDescription>
          </DialogHeader>
          <form onSubmit={form.handleSubmit(onSubmit)} className="space-y-4">
            <div className="space-y-2">
              <Label htmlFor="code">Mã chức vụ <span className="text-destructive">*</span></Label>
              <Input id="code" {...form.register('code')} disabled={!!editing} />
              {form.formState.errors.code && <p className="text-xs text-destructive">{form.formState.errors.code.message}</p>}
            </div>
            <div className="space-y-2">
              <Label htmlFor="name">Tên chức vụ <span className="text-destructive">*</span></Label>
              <Input id="name" {...form.register('name')} />
              {form.formState.errors.name && <p className="text-xs text-destructive">{form.formState.errors.name.message}</p>}
            </div>
            <div className="space-y-2">
              <Label htmlFor="level">Cấp bậc (1-99)</Label>
              <Input id="level" type="text" {...form.register('level')} placeholder="Để trống nếu không có" />
            </div>
            <div className="space-y-2">
              <Label htmlFor="description">Mô tả</Label>
              <Textarea id="description" {...form.register('description')} />
            </div>
            <DialogFooter>
              <Button variant="ghost" type="button" onClick={() => setDialogOpen(false)}>Hủy</Button>
              <Button type="submit" disabled={createPosition.isPending || updatePosition.isPending}>{editing ? 'Cập nhật' : 'Tạo'}</Button>
            </DialogFooter>
          </form>
        </DialogContent>
      </Dialog>

      <Dialog open={!!confirm} onOpenChange={(o) => !o && setConfirm(null)}>
        <DialogContent>
          <DialogHeader><DialogTitle>Xác nhận</DialogTitle>
          <DialogDescription>{confirm?.action === 'activate' ? `Kích hoạt chức vụ "${confirm?.name}"?` : `Vô hiệu hóa chức vụ "${confirm?.name}"?`}</DialogDescription></DialogHeader>
          <DialogFooter>
            <Button variant="ghost" onClick={() => setConfirm(null)}>Hủy</Button>
            <Button variant={confirm?.action === 'deactivate' ? 'destructive' : 'primary'} disabled={toggleStatus.isPending}
              onClick={() => confirm && toggleStatus.mutateAsync(confirm).then(() => { toast.success('Thành công'); setConfirm(null); }).catch((raw) => toast.error(extractErrorMessage(raw)))}>
              Xác nhận
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </div>
  );
}
