import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { positionService } from '@/domains/organization/services/positionService';
import type { CreatePositionPayload, UpdatePositionPayload } from '@/domains/organization/models/position';

const POSITIONS_KEY = ['positions'];

export function usePositions(params?: Record<string, string>) {
  return useQuery({
    queryKey: [...POSITIONS_KEY, params],
    queryFn: () => positionService.list(params),
  });
}

export function useCreatePosition() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: (payload: CreatePositionPayload) => positionService.create(payload),
    onSuccess: () => qc.invalidateQueries({ queryKey: POSITIONS_KEY }),
  });
}

export function useUpdatePosition() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: ({ id, payload }: { id: string; payload: UpdatePositionPayload }) => positionService.update(id, payload),
    onSuccess: () => qc.invalidateQueries({ queryKey: POSITIONS_KEY }),
  });
}

export function useTogglePositionStatus() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: ({ id, action }: { id: string; action: 'activate' | 'deactivate' }) => positionService.toggleStatus(id, action),
    onSuccess: () => qc.invalidateQueries({ queryKey: POSITIONS_KEY }),
  });
}
