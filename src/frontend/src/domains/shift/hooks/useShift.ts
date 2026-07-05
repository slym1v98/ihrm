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

export function useShiftAssignments() {
  return useQuery({ queryKey: ['shift-assignments'], queryFn: shiftService.getAssignments });
}

export function useCreateShiftAssignment() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: (payload: Parameters<typeof shiftService.createAssignment>[0]) => shiftService.createAssignment(payload),
    onSuccess: () => qc.invalidateQueries({ queryKey: ['shift-assignments'] }),
  });
}

export function useEndShiftAssignment() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: (id: string) => shiftService.endAssignment(id),
    onSuccess: () => qc.invalidateQueries({ queryKey: ['shift-assignments'] }),
  });
}
