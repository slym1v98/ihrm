'use client';

import { useState, useMemo, useCallback } from 'react';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { toast } from 'sonner';
import { Pencil, ToggleLeft, ToggleRight, ArrowRight } from 'lucide-react';
import { useDepartments, useCreateDepartment, useUpdateDepartment, useMoveDepartment, useToggleDepartmentStatus } from '@/domains/organization/hooks/useDepartments';
import { useBranches } from '@/domains/organization/hooks/useBranches';
import type { Department } from '@/domains/organization/models/department';
import { DataTable, type Column } from '@/shared/components/DataTable';
import { Drawer, DrawerContent, DrawerHeader, DrawerTitle, DrawerDescription, DrawerBody, DrawerFooter } from '@/shared/components/ui/drawer';
import { Button } from '@/shared/components/ui/button';
import { Input } from '@/shared/components/ui/input';
import { Label } from '@/shared/components/ui/label';
import { Badge } from '@/shared/components/ui/badge';
import { extractErrorMessage, extractFieldErrors } from '@/core/errors/messages';

const deptSchema = z.object({
  code: z.string().min(2, 'Mã tối thiểu 2 ký tự').regex(/^[A-Za-z][A-Za-z0-9-]+$/, 'Chỉ chấp nhận chữ, số, dấu gạch ngang'),
  name: z.string().min(1, 'Tên không được để trống'),
  branch_id: z.string().min(1, 'Chọn chi nhánh'),
  parent_id: z.string().optional(),
});

type DeptFormData = z.infer<typeof deptSchema>;

export function DepartmentListPage() {
  const { data: deptData, isLoading } = useDepartments();
  const { data: branchData } = useBranches();
  const createDept = useCreateDepartment();
  const updateDept = useUpdateDepartment();
  const moveDept = useMoveDepartment();
  const toggleStatus = useToggleDepartmentStatus();

  const [dialogOpen, setDialogOpen] = useState(false);
  const [editing, setEditing] = useState<Department | null>(null);
  const [isMove, setIsMove] = useState(false);
  const [confirm, setConfirm] = useState<{ id: string; action: 'activate' | 'deactivate'; name: string } | null>(null);

  const branches = useMemo(() => branchData?.data ?? [], [branchData]);
  const departments = useMemo(() => deptData?.data ?? [], [deptData]);

  const form = useForm<DeptFormData, unknown>({
    resolver: zodResolver(deptSchema),
    defaultValues: { code: '', name: '', branch_id: '', parent_id: '' },
  });

  const selectedBranch = form.watch('branch_id');
  const availableParents = useMemo(() => {
    if (!selectedBranch) return [];
    return departments.filter(d => d.branch_id === selectedBranch && d.id !== editing?.id && d.status === 'active');
  }, [departments, selectedBranch, editing?.id]);

  const openCreate = useCallback(() => {
    setEditing(null); setIsMove(false);
    form.reset({ code: '', name: '', branch_id: '', parent_id: '' });
    setDialogOpen(true);
  }, [form]);

  const openEdit = useCallback((dept: Department) => {
    setEditing(dept); setIsMove(false);
    form.reset({ code: dept.code, name: dept.name, branch_id: dept.branch_id, parent_id: dept.parent_id ?? '' });
    setDialogOpen(true);
  }, [form]);

  const openMove = useCallback((dept: Department) => {
    setEditing(dept); setIsMove(true);
    form.reset({ code: dept.code, name: dept.name, branch_id: dept.branch_id, parent_id: dept.parent_id ?? '' });
    setDialogOpen(true);
  }, [form]);

  const onSubmit = useCallback(async (values: DeptFormData) => {
    type ApiError = { response?: { data?: { error?: { details?: { field: string; message: string }[]; message?: string } } } };
    try {
      if (isMove && editing) {
        await moveDept.mutateAsync({ id: editing.id, payload: { new_parent_id: values.parent_id || null } });
        toast.success('Di chuyển phòng ban thành công');
      } else if (editing) {
        await updateDept.mutateAsync({ id: editing.id, payload: { name: values.name, manager_employee_id: null } });
        toast.success('Cập nhật phòng ban thành công');
      } else {
        await createDept.mutateAsync({ code: values.code, name: values.name, branch_id: values.branch_id, parent_id: values.parent_id || undefined });
        toast.success('Tạo phòng ban thành công');
      }
      setDialogOpen(false);
    } catch (raw) {
      const details = extractFieldErrors(raw);
      if (details.length > 0) details.forEach(({ field, message }) => form.setError(field as never, { message }));
      else toast.error(extractErrorMessage(raw));
    }
  }, [editing, isMove, createDept, updateDept, moveDept, form]);

  function getBranchName(id: string) { return branches.find(b => b.id === id)?.name ?? '—'; }

  const columns: Column<Department>[] = [
    { header: '#', accessor: undefined as never, cell: (_d, idx) => idx + 1, className: 'w-12', headerClassName: 'w-12' },
    { header: 'Mã', accessor: 'code', className: 'font-mono text-xs' },
    { header: 'Tên', accessor: 'name' },
    { header: 'Chi nhánh', accessor: undefined as never, cell: (d) => <span className="text-muted-foreground">{getBranchName(d.branch_id)}</span> },
    { header: 'Phòng ban cha', accessor: undefined as never, cell: (d) => {
      const parent = departments.find(p => p.id === d.parent_id);
      return parent?.name ?? '—';
    }, className: 'text-muted-foreground' },
    {
      header: 'Trạng thái', accessor: undefined as never,
      cell: (d) => <Badge variant={d.status === 'active' ? 'default' : 'secondary'}>{d.status === 'active' ? 'Hoạt động' : 'Ngừng'}</Badge>,
    },
    {
      header: 'Thao tác', accessor: undefined as never, className: 'text-right',
      cell: (d) => (
        <div className="flex justify-end gap-1">
          <Button variant="ghost" size="sm" title="Sửa" onClick={() => openEdit(d)}><Pencil className="h-4 w-4" /></Button>
          <Button variant="ghost" size="sm" title="Di chuyển" onClick={() => openMove(d)}><ArrowRight className="h-4 w-4" /></Button>
          <Button variant="ghost" size="sm" title={d.status === 'active' ? 'Vô hiệu hóa' : 'Kích hoạt'}
            onClick={() => setConfirm({ id: d.id, action: d.status === 'active' ? 'deactivate' : 'activate', name: d.name })}>
            {d.status === 'active' ? <ToggleLeft className="h-4 w-4" /> : <ToggleRight className="h-4 w-4" />}
          </Button>
        </div>
      ),
    },
  ];

  return (
    <div className="space-y-4">
      <div className="flex items-center justify-between">
      </div>

      <DataTable<Department> columns={columns} data={departments} isLoading={isLoading} rowKey="id" emptyMessage="Chưa có phòng ban nào" />

      <Drawer open={dialogOpen} onOpenChange={setDialogOpen}>
        <DrawerContent size="sm">
          <DrawerHeader>
            <DrawerTitle>{isMove ? 'Di chuyển phòng ban' : editing ? 'Sửa phòng ban' : 'Thêm phòng ban'}</DrawerTitle>
            <DrawerDescription>{isMove ? 'Chọn cấp cha mới cho phòng ban' : editing ? 'Cập nhật thông tin phòng ban' : 'Nhập thông tin phòng ban mới'}</DrawerDescription>
          </DrawerHeader>
          <DrawerBody>
<form id="drawer-form" onSubmit={form.handleSubmit(onSubmit)} className="space-y-4">
{!isMove && <>
              <div className="space-y-2">
                <Label htmlFor="code">Mã phòng ban <span className="text-destructive">*</span></Label>
                <Input id="code" {...form.register('code')} disabled={!!editing} />
                {form.formState.errors.code && <p className="text-xs text-destructive">{form.formState.errors.code.message}</p>}
              </div>
              <div className="space-y-2">
                <Label htmlFor="name">Tên phòng ban <span className="text-destructive">*</span></Label>
                <Input id="name" {...form.register('name')} disabled={isMove} />
                {form.formState.errors.name && <p className="text-xs text-destructive">{form.formState.errors.name.message}</p>}
              </div>
            </>}
            <div className="space-y-2">
              <Label htmlFor="branch_id">Chi nhánh <span className="text-destructive">*</span></Label>
              <select id="branch_id" className="h-8 w-full rounded-md border bg-[hsl(var(--card))] px-2 py-1 text-[13px] outline-none focus:ring-2 focus:ring-primary"
                disabled={isMove || !!editing} {...form.register('branch_id', { required: 'Chọn chi nhánh' })}>
                <option value="">Chọn chi nhánh</option>
                {branches.map(b => <option key={b.id} value={b.id}>{b.name}</option>)}
              </select>
              {form.formState.errors.branch_id && <p className="text-xs text-destructive">{form.formState.errors.branch_id.message}</p>}
            </div>
            <div className="space-y-2">
              <Label htmlFor="parent_id">Phòng ban cha</Label>
              <select id="parent_id" className="h-8 w-full rounded-md border bg-[hsl(var(--card))] px-2 py-1 text-[13px] outline-none focus:ring-2 focus:ring-primary"
                {...form.register('parent_id')}>
                <option value="">Không có (cấp cao nhất)</option>
                {availableParents.map(p => <option key={p.id} value={p.id}>{p.name}</option>)}
              </select>
            </div>
</form>
</DrawerBody>
<DrawerFooter>
<Button variant="ghost" type="button" onClick={() => setDialogOpen(false)}>Hủy</Button>
              <Button type="submit" disabled={createDept.isPending || updateDept.isPending || moveDept.isPending}>
                {isMove ? 'Di chuyển' : editing ? 'Cập nhật' : 'Tạo'}
              </Button>
            
</DrawerFooter>

        </DrawerContent>
      </Drawer>

      <Drawer open={!!confirm} onOpenChange={(o) => !o && setConfirm(null)}>
        <DrawerContent size="sm">
          <DrawerHeader><DrawerTitle>Xác nhận</DrawerTitle>
          <DrawerDescription>{confirm?.action === 'activate' ? `Kích hoạt phòng ban "${confirm?.name}"?` : `Vô hiệu hóa phòng ban "${confirm?.name}"?`}</DrawerDescription></DrawerHeader>
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
