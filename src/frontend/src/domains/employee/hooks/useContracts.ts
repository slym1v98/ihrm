import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { contractService } from '@/domains/employee/services/contractService';
import type { CreateContractPayload } from '@/domains/employee/models/contract';

const CON_KEY = ['contracts'];

export function useContracts(employeeId: string) {
  return useQuery({ queryKey: [...CON_KEY, employeeId], queryFn: () => contractService.listByEmployee(employeeId), enabled: !!employeeId });
}

export function useCreateContract(employeeId: string) {
  const qc = useQueryClient();
  return useMutation({ mutationFn: (p: CreateContractPayload) => contractService.create(employeeId, p), onSuccess: () => qc.invalidateQueries({ queryKey: CON_KEY }) });
}

export function useActivateContract() {
  const qc = useQueryClient();
  return useMutation({ mutationFn: (id: string) => contractService.activate(id), onSuccess: () => qc.invalidateQueries({ queryKey: CON_KEY }) });
}

export function useRenewContract() {
  const qc = useQueryClient();
  return useMutation({ mutationFn: ({ id, payload }: { id: string; payload: { new_end_date?: string } }) => contractService.renew(id, payload), onSuccess: () => qc.invalidateQueries({ queryKey: CON_KEY }) });
}

export function useTerminateContract() {
  const qc = useQueryClient();
  return useMutation({ mutationFn: (id: string) => contractService.terminate(id), onSuccess: () => qc.invalidateQueries({ queryKey: CON_KEY }) });
}
