import { http } from '@/core/http/client';
import type { Contract, CreateContractPayload } from '@/domains/employee/models/contract';

export const contractService = {
  async listByEmployee(employeeId: string) {
    const res = await http.get<{ data: Contract[] }>(`/employees/${employeeId}/contracts`);
    return res.data;
  },
  async create(employeeId: string, payload: CreateContractPayload) {
    const res = await http.post<{ data: Contract }>(`/employees/${employeeId}/contracts`, payload);
    return res.data.data;
  },
  async activate(id: string) {
    const res = await http.post<{ data: Contract }>(`/contracts/${id}/activate`);
    return res.data.data;
  },
  async renew(id: string, payload: { new_end_date?: string }) {
    const res = await http.post<{ data: Contract }>(`/contracts/${id}/renew`, payload);
    return res.data.data;
  },
  async terminate(id: string) {
    const res = await http.post<{ data: Contract }>(`/contracts/${id}/terminate`);
    return res.data.data;
  },
};
