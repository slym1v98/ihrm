'use client';

import { useState, useCallback } from 'react';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { toast } from 'sonner';
import { Pencil, ToggleLeft, ToggleRight } from 'lucide-react';
import { useBranches, useCreateBranch, useUpdateBranch, useToggleBranchStatus } from '@/domains/organization/hooks/useBranches';
import type { Branch } from '@/domains/organization/models/branch';
import { DataTable, type Column } from '@/shared/components/DataTable';
import { Drawer, DrawerContent, DrawerHeader, DrawerTitle, DrawerDescription, DrawerBody, DrawerFooter } from '@/shared/components/ui/drawer';
import { Button } from '@/shared/components/ui/button';
import { Input } from '@/shared/components/ui/input';
import { Label } from '@/shared/components/ui/label';
import { Badge } from '@/shared/components/ui/badge';
import { extractErrorMessage, extractFieldErrors } from '@/core/errors/messages';

const branchSchema = z.object({
  code: z.string().trim().min(2, 'Mã tối thiểu 2 ký tự').max(50, 'Mã tối đa 50 ký tự').regex(/^[A-Z][A-Z0-9-]*$/, 'Mã phải viết hoa, bắt đầu bằng chữ, chỉ gồm A-Z, 0-9, dấu gạch ngang'),
  name: z.string().trim().min(1, 'Tên không được để trống').max(255, 'Tên tối đa 255 ký tự'),
  address: z.string().optional(),
  phone: z.string().optional(),
  email: z.string().email('Email không hợp lệ').optional().or(z.literal('')),
});

type BranchFormData = z.infer<typeof branchSchema>;

export function BranchListPage() {
  const { data, isLoading } = useBranches();
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

  const openCreate = useCallback(() => {
    setEditing(null);
    form.reset({ code: '', name: '', address: '', phone: '', email: '' });
    setDialogOpen(true);
  }, [form]);

  const openEdit = useCallback((branch: Branch) => {
    setEditing(branch);
    form.reset({
      code: branch.code, name: branch.name,
      address: branch.address ?? '', phone: branch.phone ?? '', email: branch.email ?? '',
    });
    setDialogOpen(true);
  }, [form]);

  const onSubmit = useCallback(async (values: BranchFormData) => {
    type ApiError = { response?: { data?: { error?: { details?: { field: string; message: string }[]; message?: string } } } };
    const payload = {
      code: values.code.trim().toUpperCase(), name: values.name.trim(),
      address: values.address?.trim() || undefined,
      phone: values.phone?.trim() || undefined, email: values.email?.trim() || undefined,
    };
    try {
      if (editing) {
        await updateBranch.mutateAsync({ id: editing.id, payload });
        toast.success('Cập nhật chi nhánh thành công');
      } else {
        await createBranch.mutateAsync(payload);
        toast.success('Tạo chi nhánh thành công');
      }
      setDialogOpen(false);
    } catch (raw) {
      const details = extractFieldErrors(raw);
      if (details.length > 0) details.forEach(({ field, message }) => form.setError(field as never, { message }));
      else toast.error(extractErrorMessage(raw));
    }
  }, [editing, createBranch, updateBranch, form]);

  const branches = data?.data ?? [];

  const columns: Column<Branch>[] = [
    { header: '#', accessor: undefined, cell: (_b, idx) => idx + 1, className: 'w-12', headerClassName: 'w-12' },
    { header: 'Mã', accessor: 'code', className: 'font-mono text-xs' },
    { header: 'Tên', accessor: 'name' },
    { header: 'Địa chỉ', accessor: 'address', cell: (b) => b.address ?? '—', className: 'text-muted-foreground' },
    {
      header: 'Trạng thái',
      accessor: undefined,
      cell: (b) => <Badge variant={b.status === 'active' ? 'default' : 'secondary'}>{b.status === 'active' ? 'Hoạt động' : 'Ngừng'}</Badge>,
    },
    {
      header: 'Thao tác',
      accessor: undefined,
      className: 'text-right',
      cell: (b) => (
        <div className="flex justify-end gap-1">
          <Button variant="ghost" size="sm" title="Sửa" onClick={() => openEdit(b)}>
            <Pencil className="h-4 w-4" />
          </Button>
          <Button variant="ghost" size="sm"
            title={b.status === 'active' ? 'Vô hiệu hóa' : 'Kích hoạt'}
            onClick={() => setConfirm({ id: b.id, action: b.status === 'active' ? 'deactivate' : 'activate', name: b.name })}
          >
            {b.status === 'active' ? <ToggleLeft className="h-4 w-4" /> : <ToggleRight className="h-4 w-4" />}
          </Button>
        </div>
      ),
    },
  ];

  return (
    <div className="space-y-4">
      <div className="flex items-center justify-between">
        <Button onClick={openCreate}>+ Thêm chi nhánh</Button>
      </div>

      <DataTable<Branch> columns={columns} data={branches} isLoading={isLoading} rowKey="id" emptyMessage="Chưa có chi nhánh nào" />

      <Drawer open={dialogOpen} onOpenChange={setDialogOpen}>
        <DrawerContent size="sm">
          <DrawerHeader>
            <DrawerTitle>{editing ? 'Sửa chi nhánh' : 'Thêm chi nhánh'}</DrawerTitle>
            <DrawerDescription>{editing ? 'Cập nhật thông tin chi nhánh' : 'Nhập thông tin chi nhánh mới'}</DrawerDescription>
          </DrawerHeader>
          <DrawerBody>
<form id="drawer-form" onSubmit={form.handleSubmit(onSubmit)} className="space-y-4">
<div className="space-y-2">
              <Label htmlFor="code">Mã chi nhánh <span className="text-destructive">*</span></Label>
              <Input id="code" {...form.register('code')} disabled={!!editing} />
              {form.formState.errors.code && <p className="text-xs text-destructive">{form.formState.errors.code.message}</p>}
            </div>
            <div className="space-y-2">
              <Label htmlFor="name">Tên chi nhánh <span className="text-destructive">*</span></Label>
              <Input id="name" {...form.register('name')} />
              {form.formState.errors.name && <p className="text-xs text-destructive">{form.formState.errors.name.message}</p>}
            </div>
            <div className="space-y-2">
              <Label htmlFor="address">Địa chỉ</Label>
              <Input id="address" {...form.register('address')} />
            </div>
            <div className="grid grid-cols-2 gap-4">
              <div className="space-y-2"><Label htmlFor="phone">Số điện thoại</Label><Input id="phone" {...form.register('phone')} /></div>
              <div className="space-y-2">
                <Label htmlFor="email">Email</Label>
                <Input id="email" type="email" {...form.register('email')} />
                {form.formState.errors.email && <p className="text-xs text-destructive">{form.formState.errors.email.message}</p>}
              </div>
            </div>
</form>
</DrawerBody>
<DrawerFooter>
<Button variant="ghost" type="button" onClick={() => setDialogOpen(false)}>Hủy</Button>
              <Button type="submit" form="drawer-form" disabled={createBranch.isPending || updateBranch.isPending}>{editing ? 'Cập nhật' : 'Tạo'}</Button>
            
</DrawerFooter>

        </DrawerContent>
      </Drawer>

      <Drawer open={!!confirm} onOpenChange={(o) => !o && setConfirm(null)}>
        <DrawerContent size="sm">
          <DrawerHeader>
            <DrawerTitle>Xác nhận</DrawerTitle>
            <DrawerDescription>{confirm?.action === 'activate' ? `Kích hoạt chi nhánh "${confirm?.name}"?` : `Vô hiệu hóa chi nhánh "${confirm?.name}"?`}</DrawerDescription>
          </DrawerHeader>
          <DrawerFooter>
            <Button variant="ghost" onClick={() => setConfirm(null)}>Hủy</Button>
            <Button variant={confirm?.action === 'deactivate' ? 'destructive' : 'primary'} disabled={toggleStatus.isPending}
              onClick={() => confirm && toggleStatus.mutateAsync(confirm).then(() => { toast.success('Thành công'); setConfirm(null); }).catch((raw) => toast.error(extractErrorMessage(raw)))}>
              Xác nhận
            </Button>
          </DrawerFooter>
        </DrawerContent>
      </Drawer>
    </div>
  );
}
