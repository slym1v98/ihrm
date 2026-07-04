'use client';

import { useState, useCallback } from 'react';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { toast } from 'sonner';
import { Pencil, Plus, Trash2 } from 'lucide-react';
import { useAssetItems, useCreateAssetItem, useUpdateAssetItem, useDeleteAssetItem, useMarkAssetStatus } from '@/domains/asset/hooks/useAsset';
import type { AssetItem } from '@/domains/asset/models/asset';
import { DataTable, type Column } from '@/shared/components/DataTable';
import { Drawer, DrawerContent, DrawerHeader, DrawerTitle, DrawerDescription, DrawerBody, DrawerFooter } from '@/shared/components/ui/drawer';
import { Button } from '@/shared/components/ui/button';
import { Input } from '@/shared/components/ui/input';
import { Label } from '@/shared/components/ui/label';
import { Badge } from '@/shared/components/ui/badge';
import { extractErrorMessage } from '@/core/errors/messages';

const schema = z.object({
  asset_code: z.string().min(2, 'Mã tối thiểu 2 ký tự'),
  asset_type: z.string().min(1, 'Chọn loại tài sản'),
  name: z.string().min(1, 'Tên không được để trống'),
  serial_number: z.string().optional(),
  notes: z.string().optional(),
});

type FormData = z.infer<typeof schema>;

const statusLabels: Record<string, string> = {
  available: 'Sẵn sàng',
  assigned: 'Đã cấp phát',
  maintenance: 'Bảo trì',
  lost: 'Mất',
  damaged: 'Hư hỏng',
};

const statusVariants: Record<string, 'default' | 'secondary' | 'destructive' | 'outline'> = {
  available: 'default',
  assigned: 'secondary',
  maintenance: 'outline',
  lost: 'destructive',
  damaged: 'destructive',
};

export function AssetListPage() {
  const { data: items, isLoading } = useAssetItems();
  const createItem = useCreateAssetItem();
  const updateItem = useUpdateAssetItem();
  const deleteItem = useDeleteAssetItem();
  const markStatus = useMarkAssetStatus();

  const [dialogOpen, setDialogOpen] = useState(false);
  const [editing, setEditing] = useState<AssetItem | null>(null);

  const form = useForm<FormData>({
    resolver: zodResolver(schema),
    defaultValues: { asset_code: '', asset_type: '', name: '', serial_number: '', notes: '' },
  });

  const openCreate = useCallback(() => {
    setEditing(null);
    form.reset({ asset_code: '', asset_type: '', name: '', serial_number: '', notes: '' });
    setDialogOpen(true);
  }, [form]);

  const openEdit = useCallback((item: AssetItem) => {
    setEditing(item);
    form.reset({
      asset_code: item.asset_code, asset_type: item.asset_type, name: item.name,
      serial_number: item.serial_number ?? '', notes: item.notes ?? '',
    });
    setDialogOpen(true);
  }, [form]);

  const onSubmit = useCallback(async (values: FormData) => {
    try {
      if (editing) {
        await updateItem.mutateAsync({ id: editing.id, payload: values });
        toast.success('Cập nhật tài sản thành công');
      } else {
        await createItem.mutateAsync(values);
        toast.success('Tạo tài sản thành công');
      }
      setDialogOpen(false);
    } catch (raw) {
      toast.error(extractErrorMessage(raw));
    }
  }, [editing, createItem, updateItem]);

  const handleDelete = useCallback(async (id: string, name: string) => {
    if (!confirm(`Xoá tài sản "${name}"?`)) return;
    try {
      await deleteItem.mutateAsync(id);
      toast.success('Đã xoá tài sản');
    } catch (raw) {
      toast.error(extractErrorMessage(raw));
    }
  }, [deleteItem]);

  const handleMark = useCallback(async (id: string, status: string, name: string) => {
    try {
      await markStatus.mutateAsync({ id, status });
      toast.success(`Đã chuyển "${name}" sang ${statusLabels[status]}`);
    } catch (raw) {
      toast.error(extractErrorMessage(raw));
    }
  }, [markStatus]);

  const columns: Column<AssetItem>[] = [
    { header: 'Mã TS', accessor: 'asset_code', className: 'font-mono text-xs w-24' },
    { header: 'Loại', accessor: 'asset_type', className: 'w-20' },
    { header: 'Tên tài sản', accessor: 'name' },
    { header: 'Serial', accessor: 'serial_number', cell: (i) => i.serial_number ?? '—', className: 'font-mono text-xs w-28 text-muted-foreground' },
    { header: 'Tình trạng', accessor: 'condition', className: 'w-20' },
    {
      header: 'Trạng thái', accessor: undefined, className: 'w-24',
      cell: (i) => <Badge variant={statusVariants[i.status]}>{statusLabels[i.status]}</Badge>,
    },
    {
      header: 'Thao tác', accessor: undefined, className: 'text-right w-24',
      cell: (i) => (
        <div className="flex justify-end gap-1">
          <Button variant="ghost" size="sm" title="Sửa" onClick={() => openEdit(i)}><Pencil className="h-4 w-4" /></Button>
          {i.status === 'available' && (
            <Button variant="ghost" size="sm" title="Chuyển sang bảo trì" onClick={() => handleMark(i.id, 'maintenance', i.name)}>
              <Badge variant="outline" className="px-1 py-0 text-[10px]">BT</Badge>
            </Button>
          )}
          {i.status === 'maintenance' && (
            <Button variant="ghost" size="sm" title="Đánh dấu sẵn sàng" onClick={() => handleMark(i.id, 'available', i.name)}>
              <Badge variant="default" className="px-1 py-0 text-[10px]">OK</Badge>
            </Button>
          )}
          <Button variant="ghost" size="sm" title="Xoá" onClick={() => handleDelete(i.id, i.name)}><Trash2 className="h-4 w-4 text-destructive" /></Button>
        </div>
      ),
    },
  ];

  return (
    <div className="space-y-4">
      <div className="flex items-center justify-between">
        <Button onClick={openCreate}><Plus className="h-4 w-4 mr-1" /> Thêm tài sản</Button>
      </div>
      <DataTable<AssetItem> columns={columns} data={items ?? []} isLoading={isLoading} rowKey="id" emptyMessage="Chưa có tài sản nào" />

      <Drawer open={dialogOpen} onOpenChange={setDialogOpen}>
        <DrawerContent size="sm">
          <DrawerHeader>
            <DrawerTitle>{editing ? 'Sửa tài sản' : 'Thêm tài sản'}</DrawerTitle>
            <DrawerDescription>{editing ? 'Cập nhật thông tin tài sản' : 'Nhập thông tin tài sản mới'}</DrawerDescription>
          </DrawerHeader>
          <DrawerBody>
            <form id="asset-form" onSubmit={form.handleSubmit(onSubmit)} className="space-y-4">
              <div className="space-y-2">
                <Label htmlFor="asset_code">Mã tài sản <span className="text-destructive">*</span></Label>
                <Input id="asset_code" autoComplete="off" {...form.register('asset_code')} disabled={!!editing} />
                {form.formState.errors.asset_code && <p className="text-xs text-destructive">{form.formState.errors.asset_code.message}</p>}
              </div>
              <div className="space-y-2">
                <Label htmlFor="asset_type">Loại tài sản <span className="text-destructive">*</span></Label>
                <Input id="asset_type" autoComplete="off" {...form.register('asset_type')} />
              </div>
              <div className="space-y-2">
                <Label htmlFor="name">Tên tài sản <span className="text-destructive">*</span></Label>
                <Input id="name" autoComplete="off" {...form.register('name')} />
              </div>
              <div className="space-y-2">
                <Label htmlFor="serial_number">Số serial</Label>
                <Input id="serial_number" autoComplete="off" {...form.register('serial_number')} />
              </div>
              <div className="space-y-2">
                <Label htmlFor="notes">Ghi chú</Label>
                <textarea id="notes" className="h-20 w-full rounded-md border bg-[hsl(var(--card))] px-2 py-1 text-[13px] outline-none focus:ring-2 focus:ring-primary resize-none" {...form.register('notes')} />
              </div>
            </form>
          </DrawerBody>
          <DrawerFooter>
            <Button variant="ghost" type="button" onClick={() => setDialogOpen(false)}>Hủy</Button>
            <Button type="submit" form="asset-form" disabled={createItem.isPending || updateItem.isPending}>{editing ? 'Cập nhật' : 'Tạo'}</Button>
          </DrawerFooter>
        </DrawerContent>
      </Drawer>
    </div>
  );
}
