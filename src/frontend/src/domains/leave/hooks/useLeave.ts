'use client';

import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { leaveService } from '@/domains/leave/services/leaveService';
import type { CreateLeaveRequestPayload } from '@/domains/leave/models/leave';

export function useLeaveTypes() {
  return useQuery({ queryKey: ['leave-types'], queryFn: leaveService.getTypes });
}

export function useLeavePolicies() {
  return useQuery({ queryKey: ['leave-policies'], queryFn: leaveService.getPolicies });
}

export function useLeaveRequests() {
  return useQuery({ queryKey: ['leave-requests'], queryFn: leaveService.getRequests });
}

export function useLeaveRequest(id: string) {
  return useQuery({
    queryKey: ['leave-request', id],
    queryFn: () => leaveService.getRequest(id),
    enabled: !!id,
  });
}

export function useCreateLeaveRequest() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: (payload: CreateLeaveRequestPayload) => leaveService.createRequest(payload),
    onSuccess: () => qc.invalidateQueries({ queryKey: ['leave-requests'] }),
  });
}

export function useApproveLeaveRequest() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: (id: string) => leaveService.approveRequest(id),
    onSuccess: () => qc.invalidateQueries({ queryKey: ['leave-requests'] }),
  });
}

export function useRejectLeaveRequest() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: ({ id, reason }: { id: string; reason: string }) => leaveService.rejectRequest(id, reason),
    onSuccess: () => qc.invalidateQueries({ queryKey: ['leave-requests'] }),
  });
}

export function useCancelLeaveRequest() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: (id: string) => leaveService.cancelRequest(id),
    onSuccess: () => qc.invalidateQueries({ queryKey: ['leave-requests'] }),
  });
}

export function useLeaveBalances() {
  return useQuery({ queryKey: ['leave-balances'], queryFn: leaveService.getBalances });
}

export function useLeaveBalanceSummary() {
  return useQuery({ queryKey: ['leave-balance-summary'], queryFn: leaveService.getBalanceSummary });
}
