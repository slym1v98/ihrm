import { http } from '@/core/http/client';
import type { AttendanceAdjustmentRequest } from '@/domains/attendance/models/attendance';

interface ApiListResponse<T> { data: T[]; }
interface ApiOneResponse<T> { data: T; }

export const adjustmentService = {
  async list(): Promise<AttendanceAdjustmentRequest[]> {
    const r = await http.get<ApiListResponse<AttendanceAdjustmentRequest>>('/attendance/adjustments');
    return r.data.data;
  },

  async create(payload: { attendance_timesheet_id: string; reason: string }): Promise<AttendanceAdjustmentRequest> {
    const r = await http.post<ApiOneResponse<AttendanceAdjustmentRequest>>('/attendance/adjustments', payload);
    return r.data.data;
  },

  async approve(id: string): Promise<void> {
    await http.post(`/attendance/adjustments/${id}/approve`);
  },

  async reject(id: string, reason: string): Promise<void> {
    await http.post(`/attendance/adjustments/${id}/reject`, { reason });
  },
};
