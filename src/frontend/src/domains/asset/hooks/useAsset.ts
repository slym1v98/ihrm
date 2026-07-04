'use client';

import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { assetService } from '@/domains/asset/services/assetService';
import type { CreateAssetItemPayload, AssignAssetPayload } from '@/domains/asset/models/asset';

export function useAssetItems() {
  return useQuery({ queryKey: ['asset-items'], queryFn: assetService.getItems });
}

export function useCreateAssetItem() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: (p: CreateAssetItemPayload) => assetService.createItem(p),
    onSuccess: () => qc.invalidateQueries({ queryKey: ['asset-items'] }),
  });
}

export function useUpdateAssetItem() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: ({ id, payload }: { id: string; payload: Partial<CreateAssetItemPayload> }) =>
      assetService.updateItem(id, payload),
    onSuccess: () => qc.invalidateQueries({ queryKey: ['asset-items'] }),
  });
}

export function useDeleteAssetItem() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: (id: string) => assetService.deleteItem(id),
    onSuccess: () => qc.invalidateQueries({ queryKey: ['asset-items'] }),
  });
}

export function useMarkAssetStatus() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: ({ id, status }: { id: string; status: string }) => assetService.markStatus(id, status),
    onSuccess: () => qc.invalidateQueries({ queryKey: ['asset-items'] }),
  });
}

export function useAssetAssignments() {
  return useQuery({ queryKey: ['asset-assignments'], queryFn: assetService.getAssignments });
}

export function useAssignAsset() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: (p: AssignAssetPayload) => assetService.assignAsset(p),
    onSuccess: () => qc.invalidateQueries({ queryKey: ['asset-assignments'] }),
  });
}

export function useReturnAsset() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: (id: string) => assetService.returnAsset(id),
    onSuccess: () => qc.invalidateQueries({ queryKey: ['asset-assignments'] }),
  });
}
