import { http } from '@/core/http/client';
import type { ShiftAssignment, ShiftTemplate, CreateShiftTemplatePayload } from '@/domains/shift/models/shift';

interface ApiListResponse<T> {
  data: T[];
}

export const shiftService = {
  async getAssignments(): Promise<ShiftAssignment[]> { const r=await http.get<ApiListResponse<ShiftAssignment>>('/shift-assignments'); return r.data.data; },
  async createAssignment(payload:{shift_template_id:string;assignable_type:string;assignable_id:string;effective_from:string;effective_to?:string}): Promise<ShiftAssignment> { const r=await http.post<{data:ShiftAssignment}>('/shift-assignments',payload); return r.data.data; },
  async endAssignment(id:string): Promise<void> { await http.post(`/shift-assignments/${id}/end`); },
  async getEmployeeShifts(employeeId:string): Promise<ShiftAssignment[]> { const r=await http.get<ApiListResponse<ShiftAssignment>>(`/employees/${employeeId}/shifts`); return r.data.data; },
  async getTemplates(): Promise<ShiftTemplate[]> {
    const res = await http.get<ApiListResponse<ShiftTemplate>>('/shift-templates');
    return res.data.data;
  },

  async getTemplate(id: string): Promise<ShiftTemplate> {
    const res = await http.get<{ data: ShiftTemplate }>(`/shift-templates/${id}`);
    return res.data.data;
  },

  async createTemplate(payload: CreateShiftTemplatePayload): Promise<ShiftTemplate> {
    const res = await http.post<{ data: ShiftTemplate }>('/shift-templates', payload);
    return res.data.data;
  },

  async updateTemplate(id: string, payload: Partial<CreateShiftTemplatePayload>): Promise<ShiftTemplate> {
    const res = await http.patch<{ data: ShiftTemplate }>(`/shift-templates/${id}`, payload);
    return res.data.data;
  },

  async activateTemplate(id: string): Promise<void> {
    await http.post(`/shift-templates/${id}/activate`);
  },

  async deactivateTemplate(id: string): Promise<void> {
    await http.post(`/shift-templates/${id}/deactivate`);
  },
};
