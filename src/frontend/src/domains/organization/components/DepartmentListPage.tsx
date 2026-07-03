'use client';

import { useState, useMemo } from 'react';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { toast } from 'sonner';
import { useDepartments, useCreateDepartment, useUpdateDepartment, useMoveDepartment, useToggleDepartmentStatus } from '@/domains/organization/hooks/useDepartments';
import { useBranches } from '@/domains/organization/hooks/useBranches';
import type { Department } from '@/domains/organization/models/department';
import { Table, TableHeader, TableBody, TableRow, TableHead, TableCell } from '@/shared/components/ui/table';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter } from '@/shared/components/ui/dialog';
import { Button } from '@/shared/components/ui/button';
import { Input } from '@/shared/components/ui/input';
import { Label } from '@/shared/components/ui/label';
import { Badge } from '@/shared/components/ui/badge';

const deptSchema = z.object({
  code: z.string().min(2, 'Mã tối thiểu 2 ký tự').regex(/^[A-Za-z][A-Za-z0-9-]+$/, 'Chỉ chấp nhận chữ, số, dấu gạch ngang'),
  name: z.string().min(1, 'Tên không được để trống'),
  branch_id: z.string().min(1, 'Chọn chi nhánh'),
  parent_id: z.string().optional(),
});

type DeptFormData = z.infer<typeof deptSchema>;

export function DepartmentListPage() {
  const { data: deptData, isLoading, error } = useDepartments();
  const { data: branchData } = useBranches();
  const createDept = useCreateDepartment();
  const updateDept = useUpdateDepartment();
  const moveDept = useMoveDepartment();
  const toggleStatus = useToggleDepartmentStatus();

  const [dialogOpen, setDialogOpen] = useState(false);
  const [editing, setEditing] = useState<Department | null>(null);
  const [isMove, setIsMove] = useState(false);
  const [confirm, setConfirm] = useState<{ id: string; action: 'activate' | 'deactivate'; name: string } | null>(null);

  const branches = branchData?.data ?? [];
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

  function openCreate() {
    setEditing(null);
    setIsMove(false);
    form.reset({ code: '', name: '', branch_id: '', parent_id: '' });
    setDialogOpen(true);
  }

  function openEdit(dept: Department) {
    setEditing(dept);
    setIsMove(false);
    form.reset({
      code: dept.code,
      name: dept.name,
      branch_id: dept.branch_id,
      parent_id: dept.parent_id ?? '',
    });
    setDialogOpen(true);
  }

  function openMove(dept: Department) {
    setEditing(dept);
    setIsMove(true);
    form.reset({
      code: dept.code,
      name: dept.name,
      branch_id: dept.branch_id,
      parent_id: dept.parent_id ?? '',
    });
    setDialogOpen(true);
  }

  async function onSubmit(values: DeptFormData) {
    type ApiError = { response?: { data?: { error?: { details?: { field: string; message: string }[]; message?: string } } } };

    try {
      if (isMove && editing) {
        await moveDept.mutateAsync({ id: editing.id, payload: { new_parent_id: values.parent_id || null } });
        toast.success('Di chuyển phòng ban thành công');
      } else if (editing) {
        await updateDept.mutateAsync({ id: editing.id, payload: { name: values.name, manager_employee_id: null } });
        toast.success('Cập nhật phòng ban thành công');
      } else {
        await createDept.mutateAsync({
          code: values.code,
          name: values.name,
          branch_id: values.branch_id,
          parent_id: values.parent_id || undefined,
        });
        toast.success('Tạo phòng ban thành công');
      }
      setDialogOpen(false);
    } catch (raw) {
      const err = raw as ApiError;
      const details = err?.response?.data?.error?.details;
      if (details) {
        details.forEach(({ field, message }) => form.setError(field as keyof DeptFormData, { message }));
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
    } catch { toast.error('Thao tác thất bại'); }
  }

  function getBranchName(id: string) {
    return branches.find(b => b.id === id)?.name ?? '—';
  }

  function getDeptName(id: string | null) {
    if (!id) return null;
    return departments.find(d => d.id === id)?.name ?? '—';
  }

  if (isLoading) return <div className="py-12 text-center text-muted-foreground">Đang tải...</div>;
  if (error) return <div className="py-12 text-center text-destructive">Không thể tải danh sách phòng ban.</div>;

  return (
    <div className="space-y-4">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-semibold">Phòng ban</h1>
          <p className="text-sm text-muted-foreground">Quản lý cấu trúc phòng ban theo chi nhánh</p>
        </div>
        <Button onClick={openCreate}>+ Thêm phòng ban</Button>
      </div>

      <div className="rounded-lg border bg-white">
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead className="w-12">#</TableHead>
              <TableHead>Mã</TableHead>
              <TableHead>Tên</TableHead>
              <TableHead>Chi nhánh</TableHead>
              <TableHead>Phòng ban cha</TableHead>
              <TableHead>Trạng thái</TableHead>
              <TableHead className="text-right">Thao tác</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            {departments.map((dept, index) => (
              <TableRow key={dept.id}>
                <TableCell>{index + 1}</TableCell>
                <TableCell className="font-mono text-xs">{dept.code}</TableCell>
                <TableCell>{dept.name}</TableCell>
                <TableCell className="text-muted-foreground">{getBranchName(dept.branch_id)}</TableCell>
                <TableCell className="text-muted-foreground">{getDeptName(dept.parent_id) ?? '—'}</TableCell>
                <TableCell>
                  <Badge variant={dept.status === 'active' ? 'default' : 'secondary'}>
                    {dept.status === 'active' ? 'Hoạt động' : 'Ngừng'}
                  </Badge>
                </TableCell>
                <TableCell className="text-right">
                  <div className="flex justify-end gap-1">
                    <Button variant="ghost" onClick={() => openEdit(dept)}>Sửa</Button>
                    <Button variant="ghost" onClick={() => openMove(dept)}>Di chuyển</Button>
                    <Button variant="ghost" onClick={() => setConfirm({ id: dept.id, action: dept.status === 'active' ? 'deactivate' : 'activate', name: dept.name })}>
                      {dept.status === 'active' ? 'Vô hiệu' : 'Kích hoạt'}
                    </Button>
                  </div>
                </TableCell>
              </TableRow>
            ))}
            {departments.length === 0 && (
              <TableRow><TableCell colSpan={7} className="py-8 text-center text-muted-foreground">Chưa có phòng ban nào</TableCell></TableRow>
            )}
          </TableBody>
        </Table>
      </div>

      <Dialog open={dialogOpen} onOpenChange={setDialogOpen}>
        <DialogContent>
          <DialogHeader>
            <DialogTitle>
              {isMove ? 'Di chuyển phòng ban' : editing ? 'Sửa phòng ban' : 'Thêm phòng ban'}
            </DialogTitle>
            <DialogDescription>
              {isMove ? 'Chọn cấp cha mới cho phòng ban' : editing ? 'Cập nhật thông tin phòng ban' : 'Nhập thông tin phòng ban mới'}
            </DialogDescription>
          </DialogHeader>
          <form onSubmit={form.handleSubmit(onSubmit)} className="space-y-4">
            {!isMove && (
              <>
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
              </>
            )}
            <div className="space-y-2">
              <Label htmlFor="branch_id">Chi nhánh <span className="text-destructive">*</span></Label>
              <select
                id="branch_id"
                className="h-10 w-full rounded-md border bg-white px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-primary"
                {...form.register('branch_id', { required: 'Chọn chi nhánh' })}
              >
                <option value="">Chọn chi nhánh</option>
                {branches.map(b => (
                  <option key={b.id} value={b.id}>{b.name}</option>
                ))}
              </select>
              {form.formState.errors.branch_id && <p className="text-xs text-destructive">{form.formState.errors.branch_id.message}</p>}
            </div>
            <div className="space-y-2">
              <Label htmlFor="parent_id">Phòng ban cha</Label>
              <select
                id="parent_id"
                className="h-10 w-full rounded-md border bg-white px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-primary"
                {...form.register('parent_id')}
              >
                <option value="">Không có (cấp cao nhất)</option>
                {availableParents.map(p => (
                  <option key={p.id} value={p.id}>{p.name}</option>
                ))}
              </select>
            </div>
            <DialogFooter>
              <Button variant="ghost" type="button" onClick={() => setDialogOpen(false)}>Hủy</Button>
              <Button type="submit" disabled={createDept.isPending || updateDept.isPending || moveDept.isPending}>
                {isMove ? 'Di chuyển' : editing ? 'Cập nhật' : 'Tạo'}
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
              {confirm?.action === 'activate' ? `Kích hoạt phòng ban "${confirm?.name}"?` : `Vô hiệu hóa phòng ban "${confirm?.name}"?`}
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
