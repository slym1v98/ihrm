import { http } from '@/core/http/client';
import type { AttendancePeriod, AttendanceTimesheet } from '@/domains/attendance/models/attendance';

interface ApiListResponse<T> {
  data: T[];
}

export const attendanceService = {
  async getPeriods(): Promise<AttendancePeriod[]> {
    const res = await http.get<ApiListResponse<AttendancePeriod>>('/attendance-periods');
    return res.data.data;
  },

  async getTimesheets(): Promise<AttendanceTimesheet[]> {
    const res = await http.get<ApiListResponse<AttendanceTimesheet>>('/attendance/timesheets');
    return res.data.data;
  },

  async calculate(): Promise<void> {
    await http.post('/attendance/calculate');
  },

  async closePeriod(id: string): Promise<void> {
    await http.post(`/attendance-periods/${id}/close`);
  },

  async reopenPeriod(id: string): Promise<void> {
    await http.post(`/attendance-periods/${id}/reopen`);
  },
};
