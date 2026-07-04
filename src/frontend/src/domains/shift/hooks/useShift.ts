'use client';

import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { shiftService } from '@/domains/shift/services/shiftService';
import type { CreateShiftTemplatePayload } from '@/domains/shift/models/shift';

export function useShiftTemplates() {
  return useQuery({ queryKey: ['shift-templates'], queryFn: shiftService.getTemplates });
}

export function useCreateShiftTemplate() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: (payload: CreateShiftTemplatePayload) => shiftService.createTemplate(payload),
    onSuccess: () => qc.invalidateQueries({ queryKey: ['shift-templates'] }),
  });
}

export function useUpdateShiftTemplate() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: ({ id, payload }: { id: string; payload: Partial<CreateShiftTemplatePayload> }) =>
      shiftService.updateTemplate(id, payload),
    onSuccess: () => qc.invalidateQueries({ queryKey: ['shift-templates'] }),
  });
}

export function useActivateShiftTemplate() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: (id: string) => shiftService.activateTemplate(id),
    onSuccess: () => qc.invalidateQueries({ queryKey: ['shift-templates'] }),
  });
}

export function useDeactivateShiftTemplate() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: (id: string) => shiftService.deactivateTemplate(id),
    onSuccess: () => qc.invalidateQueries({ queryKey: ['shift-templates'] }),
  });
}
