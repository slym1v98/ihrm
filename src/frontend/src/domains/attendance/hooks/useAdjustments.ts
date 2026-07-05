'use client';

import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { adjustmentService } from '@/domains/attendance/services/adjustmentService';

const ADJ_KEY = ['attendance-adjustments'];

export function useAdjustments() {
  return useQuery({ queryKey: ADJ_KEY, queryFn: adjustmentService.list });
}

export function useCreateAdjustment() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: (payload: Parameters<typeof adjustmentService.create>[0]) => adjustmentService.create(payload),
    onSuccess: () => qc.invalidateQueries({ queryKey: ADJ_KEY }),
  });
}

export function useApproveAdjustment() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: (id: string) => adjustmentService.approve(id),
    onSuccess: () => qc.invalidateQueries({ queryKey: ADJ_KEY }),
  });
}

export function useRejectAdjustment() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: ({ id, reason }: { id: string; reason: string }) => adjustmentService.reject(id, reason),
    onSuccess: () => qc.invalidateQueries({ queryKey: ADJ_KEY }),
  });
}
