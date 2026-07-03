'use client';

import { useState } from 'react';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { toast } from 'sonner';
import { useBranches, useCreateBranch, useUpdateBranch, useToggleBranchStatus } from '@/domains/organization/hooks/useBranches';
import type { Branch } from '@/domains/organization/models/branch';
import { Table, TableHeader, TableBody, TableRow, TableHead, TableCell } from '@/shared/components/ui/table';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter } from '@/shared/components/ui/dialog';
import { Button } from '@/shared/components/ui/button';
import { Input } from '@/shared/components/ui/input';
import { Label } from '@/shared/components/ui/label';
import { Badge } from '@/shared/components/ui/badge';

const branchSchema = z.object({
  code: z.string().trim().min(2, 'Mã tối thiểu 2 ký tự').max(50, 'Mã tối đa 50 ký tự').regex(/^[A-Z][A-Z0-9-]*$/, 'Mã phải viết hoa, bắt đầu bằng chữ, chỉ gồm A-Z, 0-9, dấu gạch ngang'),
  name: z.string().trim().min(1, 'Tên không được để trống').max(255, 'Tên tối đa 255 ký tự'),
  address: z.string().optional(),
  phone: z.string().optional(),
  email: z.string().email('Email không hợp lệ').optional().or(z.literal('')),
});

type BranchFormData = z.infer<typeof branchSchema>;

export function BranchListPage() {
  const { data, isLoading, error } = useBranches();
const createBranch = useCreateBranch();
const updateBranch = useUpdateBranch();
const toggleStatus = useToggleBranchStatus();
const [dialogOpen, setDialogOpen] = useState(false);
const [editing, setEditing] = useState<Branch | null>(null);
const [confirm, setConfirm] = useState<{ id: string; action: 'activate' | 'deactivate'; name: string } | null>(null);

  const form = useForm<BranchFormData, unknown>({
    resolver: zodResolver(branchSchema),
    defaultValues: { code: '', name: '', address: '', phone: '', email: '' },
  });

  function openCreate() {
    setEditing(null);
    form.reset({ code: '', name: '', address: '', phone: '', email: '' });
    setDialogOpen(true);
  }

  function openEdit(branch: Branch) {
    setEditing(branch);
    form.reset({
      code: branch.code,
      name: branch.name,
      address: branch.address ?? '',
      phone: branch.phone ?? '',
      email: branch.email ?? '',
    });
    setDialogOpen(true);
  }

  async function onSubmit(values: BranchFormData) {
    type ApiError = { response?: { data?: { error?: { details?: { field: string; message: string }[]; message?: string } } } };

    try {
      const payload = {
        code: values.code.trim().toUpperCase(),
        name: values.name.trim(),
        address: values.address?.trim() || undefined,
        phone: values.phone?.trim() || undefined,
        email: values.email?.trim() || undefined,
      };

      if (editing) {
        await updateBranch.mutateAsync({
          id: editing.id,
          payload: {
            name: payload.name,
            address: payload.address,
            phone: payload.phone,
            email: payload.email,
          },
        });
        toast.success('Cập nhật chi nhánh thành công');
      } else {
        await createBranch.mutateAsync(payload);
        toast.success('Tạo chi nhánh thành công');
      }
      setDialogOpen(false);
    } catch (raw) {
      const err = raw as ApiError;
      const details = err?.response?.data?.error?.details;
      if (details) {
        details.forEach(({ field, message }) => form.setError(field as keyof BranchFormData, { message }));
      } else {
        toast.error(err?.response?.data?.error?.message ?? 'Có lỗi xảy ra');
      }
    }
  }

  async function handleToggle(id: string, action: 'activate' | 'deactivate') {
    try {
      await toggleStatus.mutateAsync({ id, action });
      toast.success(action === 'activate' ? 'Kích hoạt thành công' : 'Vô hiệu hóa thành công');
      setConfirm(null);
    } catch {
      toast.error('Thao tác thất bại');
    }
  }

  if (isLoading) return <div className="flex items-center justify-center py-12"><p className="text-muted-foreground">Đang tải...</p></div>;
  if (error) return <div className="py-12 text-center text-destructive">Không thể tải danh sách chi nhánh.</div>;

  const branches = data?.data ?? [];

  return (
    <div className="space-y-4">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-semibold">Chi nhánh</h1>
          <p className="text-sm text-muted-foreground">Quản lý danh sách chi nhánh công ty</p>
        </div>
        <Button onClick={openCreate}>+ Thêm chi nhánh</Button>
      </div>

      <div className="rounded-lg border bg-white">
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead className="w-12">#</TableHead>
              <TableHead>Mã</TableHead>
              <TableHead>Tên</TableHead>
              <TableHead>Địa chỉ</TableHead>
              <TableHead>Trạng thái</TableHead>
              <TableHead className="text-right">Thao tác</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            {branches.map((branch, index) => (
              <TableRow key={branch.id}>
                <TableCell>{index + 1}</TableCell>
                <TableCell className="font-mono text-xs">{branch.code}</TableCell>
                <TableCell>{branch.name}</TableCell>
                <TableCell className="text-muted-foreground">{branch.address ?? '—'}</TableCell>
                <TableCell>
                  <Badge variant={branch.status === 'active' ? 'default' : 'secondary'}>
                    {branch.status === 'active' ? 'Hoạt động' : 'Ngừng'}
                  </Badge>
                </TableCell>
                <TableCell className="text-right">
                  <div className="flex justify-end gap-1">
                    <Button variant="ghost" onClick={() => openEdit(branch)}>Sửa</Button>
                    <Button
                      variant="ghost"
                      onClick={() => setConfirm({ id: branch.id, action: branch.status === 'active' ? 'deactivate' : 'activate', name: branch.name })}
                    >
                      {branch.status === 'active' ? 'Vô hiệu' : 'Kích hoạt'}
                    </Button>
                  </div>
                </TableCell>
              </TableRow>
            ))}
            {branches.length === 0 ? (
              <TableRow>
                <TableCell colSpan={6} className="py-8 text-center text-muted-foreground">Chưa có chi nhánh nào</TableCell>
              </TableRow>
            ) : null}
          </TableBody>
        </Table>
      </div>

      <Dialog open={dialogOpen} onOpenChange={setDialogOpen}>
        <DialogContent>
          <DialogHeader>
            <DialogTitle>{editing ? 'Sửa chi nhánh' : 'Thêm chi nhánh'}</DialogTitle>
            <DialogDescription>{editing ? 'Cập nhật thông tin chi nhánh' : 'Nhập thông tin chi nhánh mới'}</DialogDescription>
          </DialogHeader>
          <form onSubmit={form.handleSubmit(onSubmit)} className="space-y-4">
            <div className="space-y-2">
              <Label htmlFor="code">Mã chi nhánh</Label>
              <Input id="code" {...form.register('code')} disabled={!!editing} />
              {form.formState.errors.code && <p className="text-xs text-destructive">{form.formState.errors.code.message}</p>}
            </div>
            <div className="space-y-2">
              <Label htmlFor="name">Tên chi nhánh</Label>
              <Input id="name" {...form.register('name')} />
              {form.formState.errors.name && <p className="text-xs text-destructive">{form.formState.errors.name.message}</p>}
            </div>
            <div className="space-y-2">
              <Label htmlFor="address">Địa chỉ</Label>
              <Input id="address" {...form.register('address')} />
            </div>
            <div className="grid grid-cols-2 gap-4">
              <div className="space-y-2">
                <Label htmlFor="phone">Số điện thoại</Label>
                <Input id="phone" {...form.register('phone')} />
              </div>
              <div className="space-y-2">
                <Label htmlFor="email">Email</Label>
                <Input id="email" type="email" {...form.register('email')} />
                {form.formState.errors.email && <p className="text-xs text-destructive">{form.formState.errors.email.message}</p>}
              </div>
            </div>
            <DialogFooter>
              <Button variant="ghost" type="button" onClick={() => setDialogOpen(false)}>Hủy</Button>
              <Button type="submit" disabled={createBranch.isPending || updateBranch.isPending}>
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
              {confirm?.action === 'activate' ? `Kích hoạt chi nhánh "${confirm.name}"?` : `Vô hiệu hóa chi nhánh "${confirm?.name}"?`}
            </DialogDescription>
          </DialogHeader>
          <DialogFooter>
            <Button variant="ghost" onClick={() => setConfirm(null)}>Hủy</Button>
            <Button
              variant={confirm?.action === 'deactivate' ? 'destructive' : 'primary'}
              disabled={toggleStatus.isPending}
              onClick={() => confirm && handleToggle(confirm.id, confirm.action)}
            >
              Xác nhận
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </div>
  );
}
