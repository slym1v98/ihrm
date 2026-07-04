import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { branchService } from '@/domains/organization/services/branchService';
import type { CreateBranchPayload, UpdateBranchPayload } from '@/domains/organization/models/branch';

const BRANCHES_KEY = ['branches'];

export function useBranches(params?: Record<string, string>) {
  return useQuery({
    queryKey: [...BRANCHES_KEY, params],
    queryFn: () => branchService.list(params),
  });
}

export function useBranch(id: string) {
  return useQuery({
    queryKey: [...BRANCHES_KEY, id],
    queryFn: () => branchService.get(id),
    enabled: !!id,
  });
}

export function useCreateBranch() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: (payload: CreateBranchPayload) => branchService.create(payload),
    onSuccess: () => qc.invalidateQueries({ queryKey: BRANCHES_KEY }),
  });
}

export function useUpdateBranch() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: ({ id, payload }: { id: string; payload: UpdateBranchPayload }) => branchService.update(id, payload),
    onSuccess: () => qc.invalidateQueries({ queryKey: BRANCHES_KEY }),
  });
}

export function useToggleBranchStatus() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: ({ id, action }: { id: string; action: 'activate' | 'deactivate' }) => branchService.toggleStatus(id, action),
    onSuccess: () => qc.invalidateQueries({ queryKey: BRANCHES_KEY }),
  });
}
