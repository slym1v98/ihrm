'use client';

import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { trainingService } from '@/domains/training/services/trainingService';
import type { CreateCoursePayload } from '@/domains/training/models/training';

export function useTrainingCourses() {
  return useQuery({ queryKey: ['training-courses'], queryFn: trainingService.getCourses });
}

export function useCreateCourse() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: (p: CreateCoursePayload) => trainingService.createCourse(p),
    onSuccess: () => qc.invalidateQueries({ queryKey: ['training-courses'] }),
  });
}

export function useUpdateCourse() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: ({ id, payload }: { id: string; payload: Partial<CreateCoursePayload> }) =>
      trainingService.updateCourse(id, payload),
    onSuccess: () => qc.invalidateQueries({ queryKey: ['training-courses'] }),
  });
}

export function useDeleteCourse() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: (id: string) => trainingService.deleteCourse(id),
    onSuccess: () => qc.invalidateQueries({ queryKey: ['training-courses'] }),
  });
}
