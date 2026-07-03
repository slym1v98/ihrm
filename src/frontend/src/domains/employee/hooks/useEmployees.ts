import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { employeeService } from '@/domains/employee/services/employeeService';
import type { CreateEmployeePayload, UpdatePersonalInfoPayload } from '@/domains/employee/models/employee';

const EMP_KEY = ['employees'];

export function useEmployees(params?: Record<string, string>) {
  return useQuery({ queryKey: [...EMP_KEY, params], queryFn: () => employeeService.list(params) });
}

export function useEmployee(id: string) {
  return useQuery({ queryKey: [...EMP_KEY, id], queryFn: () => employeeService.get(id), enabled: !!id });
}

export function useCreateEmployee() {
  const qc = useQueryClient();
  return useMutation({ mutationFn: (p: CreateEmployeePayload) => employeeService.create(p), onSuccess: () => qc.invalidateQueries({ queryKey: EMP_KEY }) });
}

export function useUpdateEmployee() {
  const qc = useQueryClient();
  return useMutation({ mutationFn: ({ id, payload }: { id: string; payload: UpdatePersonalInfoPayload }) => employeeService.updatePersonalInfo(id, payload), onSuccess: () => qc.invalidateQueries({ queryKey: EMP_KEY }) });
}

export function useTransferEmployee() {
  const qc = useQueryClient();
  return useMutation({ mutationFn: ({ id, payload }: { id: string; payload: { branch_id?: string; department_id?: string; position_id?: string } }) => employeeService.transfer(id, payload), onSuccess: () => qc.invalidateQueries({ queryKey: EMP_KEY }) });
}

export function useChangeEmployeeStatus() {
  const qc = useQueryClient();
  return useMutation({ mutationFn: ({ id, action }: { id: string; action: 'activate' | 'deactivate' }) => employeeService.changeStatus(id, action), onSuccess: () => qc.invalidateQueries({ queryKey: EMP_KEY }) });
}
