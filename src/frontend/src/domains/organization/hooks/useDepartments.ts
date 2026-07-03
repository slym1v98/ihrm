import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { departmentService } from '@/domains/organization/services/departmentService';
import type { CreateDepartmentPayload, UpdateDepartmentPayload, MoveDepartmentPayload } from '@/domains/organization/models/department';

const DEPARTMENTS_KEY = ['departments'];

export function useDepartments(params?: Record<string, string>) {
  return useQuery({
    queryKey: [...DEPARTMENTS_KEY, params],
    queryFn: () => departmentService.list(params),
  });
}

export function useCreateDepartment() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: (payload: CreateDepartmentPayload) => departmentService.create(payload),
    onSuccess: () => qc.invalidateQueries({ queryKey: DEPARTMENTS_KEY }),
  });
}

export function useUpdateDepartment() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: ({ id, payload }: { id: string; payload: UpdateDepartmentPayload }) => departmentService.update(id, payload),
    onSuccess: () => qc.invalidateQueries({ queryKey: DEPARTMENTS_KEY }),
  });
}

export function useMoveDepartment() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: ({ id, payload }: { id: string; payload: MoveDepartmentPayload }) => departmentService.move(id, payload),
    onSuccess: () => qc.invalidateQueries({ queryKey: DEPARTMENTS_KEY }),
  });
}

export function useToggleDepartmentStatus() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: ({ id, action }: { id: string; action: 'activate' | 'deactivate' }) => departmentService.toggleStatus(id, action),
    onSuccess: () => qc.invalidateQueries({ queryKey: DEPARTMENTS_KEY }),
  });
}
