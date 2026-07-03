'use client';

import { useState } from 'react';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { toast } from 'sonner';
import { usePositions, useCreatePosition, useUpdatePosition, useTogglePositionStatus } from '@/domains/organization/hooks/usePositions';
import type { Position } from '@/domains/organization/models/position';
import { Table, TableHeader, TableBody, TableRow, TableHead, TableCell } from '@/shared/components/ui/table';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter } from '@/shared/components/ui/dialog';
import { Button } from '@/shared/components/ui/button';
import { Input } from '@/shared/components/ui/input';
import { Label } from '@/shared/components/ui/label';
import { Textarea } from '@/shared/components/ui/textarea';
import { Badge } from '@/shared/components/ui/badge';

const positionSchema = z.object({
  code: z.string().min(2, 'Mã tối thiểu 2 ký tự').regex(/^[A-Za-z][A-Za-z0-9-]+$/, 'Chỉ chấp nhận chữ, số, dấu gạch ngang'),
  name: z.string().min(1, 'Tên không được để trống'),
  level: z.string().optional(),
  description: z.string().optional(),
});

type PositionFormData = z.infer<typeof positionSchema>;

function toPayload(values: PositionFormData) {
  return {
    code: values.code,
    name: values.name,
    level: values.level ? parseInt(values.level) : undefined,
    description: values.description || undefined,
  };
}

export function PositionListPage() {
  const { data, isLoading, error } = usePositions();
  const createPosition = useCreatePosition();
  const updatePosition = useUpdatePosition();
  const toggleStatus = useTogglePositionStatus();
  const [dialogOpen, setDialogOpen] = useState(false);
  const [editing, setEditing] = useState<Position | null>(null);
  const [confirm, setConfirm] = useState<{ id: string; action: 'activate' | 'deactivate'; name: string } | null>(null);

  const form = useForm<PositionFormData>({
    resolver: zodResolver(positionSchema),
    defaultValues: { code: '', name: '', level: '', description: '' },
  });

  function openCreate() {
    setEditing(null);
    form.reset({ code: '', name: '', level: '', description: '' });
    setDialogOpen(true);
  }

  function openEdit(pos: Position) {
    setEditing(pos);
    form.reset({
      code: pos.code,
      name: pos.name,
      level: pos.level?.toString() ?? '',
      description: pos.description ?? '',
    });
    setDialogOpen(true);
  }

  async function onSubmit(values: PositionFormData) {
    try {
      if (editing) {
        await updatePosition.mutateAsync({ id: editing.id, payload: toPayload(values) });
        toast.success('Cập nhật chức vụ thành công');
      } else {
        await createPosition.mutateAsync(toPayload(values));
        toast.success('Tạo chức vụ thành công');
      }
      setDialogOpen(false);
    } catch (err) {
      const message = (err as { response?: { data?: { error?: { message?: string } } } })?.response?.data?.error?.message ?? 'Có lỗi xảy ra';
      toast.error(message);
    }
  }

  async function handleToggle(id: string, action: 'activate' | 'deactivate') {
    try {
      await toggleStatus.mutateAsync({ id, action });
      toast.success(action === 'activate' ? 'Kích hoạt thành công' : 'Vô hiệu hóa thành công');
      setConfirm(null);
    } catch { toast.error('Thao tác thất bại'); }
  }

  if (isLoading) return <div className="py-12 text-center text-muted-foreground">Đang tải...</div>;
  if (error) return <div className="py-12 text-center text-destructive">Không thể tải danh sách chức vụ.</div>;

  const positions = data?.data ?? [];

  return (
    <div className="space-y-4">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-semibold">Chức vụ</h1>
          <p className="text-sm text-muted-foreground">Quản lý danh sách chức vụ trong công ty</p>
        </div>
        <Button onClick={openCreate}>+ Thêm chức vụ</Button>
      </div>

      <div className="rounded-lg border bg-white">
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead className="w-12">#</TableHead>
              <TableHead>Mã</TableHead>
              <TableHead>Tên</TableHead>
              <TableHead className="w-20 text-center">Cấp bậc</TableHead>
              <TableHead>Mô tả</TableHead>
              <TableHead>Trạng thái</TableHead>
              <TableHead className="text-right">Thao tác</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            {positions.map((pos, index) => (
              <TableRow key={pos.id}>
                <TableCell>{index + 1}</TableCell>
                <TableCell className="font-mono text-xs">{pos.code}</TableCell>
                <TableCell>{pos.name}</TableCell>
                <TableCell className="text-center">{pos.level ?? '—'}</TableCell>
                <TableCell className="max-w-xs truncate text-muted-foreground">{pos.description ?? '—'}</TableCell>
                <TableCell>
                  <Badge variant={pos.status === 'active' ? 'default' : 'secondary'}>
                    {pos.status === 'active' ? 'Hoạt động' : 'Ngừng'}
                  </Badge>
                </TableCell>
                <TableCell className="text-right">
                  <div className="flex justify-end gap-1">
                    <Button variant="ghost" onClick={() => openEdit(pos)}>Sửa</Button>
                    <Button variant="ghost" onClick={() => setConfirm({ id: pos.id, action: pos.status === 'active' ? 'deactivate' : 'activate', name: pos.name })}>
                      {pos.status === 'active' ? 'Vô hiệu' : 'Kích hoạt'}
                    </Button>
                  </div>
                </TableCell>
              </TableRow>
            ))}
            {positions.length === 0 && (
              <TableRow><TableCell colSpan={7} className="py-8 text-center text-muted-foreground">Chưa có chức vụ nào</TableCell></TableRow>
            )}
          </TableBody>
        </Table>
      </div>

      <Dialog open={dialogOpen} onOpenChange={setDialogOpen}>
        <DialogContent>
          <DialogHeader>
            <DialogTitle>{editing ? 'Sửa chức vụ' : 'Thêm chức vụ'}</DialogTitle>
            <DialogDescription>{editing ? 'Cập nhật thông tin chức vụ' : 'Nhập thông tin chức vụ mới'}</DialogDescription>
          </DialogHeader>
          <form onSubmit={form.handleSubmit(onSubmit)} className="space-y-4">
            <div className="space-y-2">
              <Label htmlFor="code">Mã chức vụ</Label>
              <Input id="code" {...form.register('code')} disabled={!!editing} />
              {form.formState.errors.code && <p className="text-xs text-destructive">{form.formState.errors.code.message}</p>}
            </div>
            <div className="space-y-2">
              <Label htmlFor="name">Tên chức vụ</Label>
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
              <Button type="submit" disabled={createPosition.isPending || updatePosition.isPending}>
                {editing ? 'Cập nhật' : 'Tạo'}
              </Button>
            </DialogFooter>
          </form>
        </DialogContent>
      </Dialog>

      <Dialog open={!!confirm} onOpenChange={(open) => !open && setConfirm(null)}>
        <DialogContent>
          <DialogHeader>
            <DialogTitle>Xác nhận</DialogTitle>
            <DialogDescription>
              {confirm?.action === 'activate' ? `Kích hoạt chức vụ "${confirm?.name}"?` : `Vô hiệu hóa chức vụ "${confirm?.name}"?`}
            </DialogDescription>
          </DialogHeader>
          <DialogFooter>
            <Button variant="ghost" onClick={() => setConfirm(null)}>Hủy</Button>
            <Button
              variant={confirm?.action === 'deactivate' ? 'destructive' : 'primary'}
              disabled={toggleStatus.isPending}
              onClick={() => confirm && handleToggle(confirm.id, confirm.action)}
            >Xác nhận</Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </div>
  );
}
