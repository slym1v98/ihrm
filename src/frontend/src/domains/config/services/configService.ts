import { http } from '@/core/http/client';
import type { HolidayCalendar, SystemSetting } from '@/domains/config/models/config';
interface ApiListResponse<T> { data: T[] }

export const configService = {
  async getSettings(): Promise<SystemSetting[]> { const r=await http.get<ApiListResponse<SystemSetting>>('/config/settings'); return r.data.data; },
  async saveSetting(key:string,value:string): Promise<void> { await http.post('/config/settings',{key,value}); },
  async getHolidayCalendars(): Promise<HolidayCalendar[]> { const r=await http.get<ApiListResponse<HolidayCalendar>>('/config/holiday-calendars'); return r.data.data; },
  async createHolidayCalendar(p: {code:string;name:string;year:number}): Promise<HolidayCalendar> { const r=await http.post<{data:HolidayCalendar}>('/config/holiday-calendars',p); return r.data.data; },
};
