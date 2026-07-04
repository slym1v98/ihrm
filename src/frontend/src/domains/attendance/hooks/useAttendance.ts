'use client';

import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { attendanceService } from '@/domains/attendance/services/attendanceService';

export function useAttendancePeriods() {
  return useQuery({ queryKey: ['attendance-periods'], queryFn: attendanceService.getPeriods });
}

export function useAttendanceTimesheets() {
  return useQuery({ queryKey: ['attendance-timesheets'], queryFn: attendanceService.getTimesheets });
}

export function useCalculateAttendance() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: attendanceService.calculate,
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: ['attendance-timesheets'] });
    },
  });
}

export function useClosePeriod() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: (id: string) => attendanceService.closePeriod(id),
    onSuccess: () => qc.invalidateQueries({ queryKey: ['attendance-periods'] }),
  });
}

export function useReopenPeriod() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: (id: string) => attendanceService.reopenPeriod(id),
    onSuccess: () => qc.invalidateQueries({ queryKey: ['attendance-periods'] }),
  });
}
