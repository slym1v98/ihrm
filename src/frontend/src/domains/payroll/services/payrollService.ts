import { http } from '@/core/http/client';
import type { CreatePeriodPayload, PayrollComponent, PayrollPeriod, PayrollRun, Payslip, PayrollEntry, PayrollAdjustment, PeriodSummary } from '@/domains/payroll/models/payroll';
interface ApiListResponse<T> { data: T[] }
interface ApiOneResponse<T> { data: T }

export const payrollService = {
  async getPeriods(): Promise<PayrollPeriod[]> { const r=await http.get<ApiListResponse<PayrollPeriod>>('/payroll/periods'); return r.data.data; },
  async createPeriod(p: CreatePeriodPayload): Promise<PayrollPeriod> { const r=await http.post<ApiOneResponse<PayrollPeriod>>('/payroll/periods',p); return r.data.data; },
  async submitApproval(id:string): Promise<void> { await http.post(`/payroll/periods/${id}/submit-approval`); },
  async approvePeriod(id:string): Promise<void> { await http.post(`/payroll/periods/${id}/approve`); },
  async rejectPeriod(id:string): Promise<void> { await http.post(`/payroll/periods/${id}/reject`); },
  async lockPeriod(id:string): Promise<void> { await http.post(`/payroll/periods/${id}/lock`); },
  async reopenPeriod(id:string): Promise<void> { await http.post(`/payroll/periods/${id}/reopen`); },
  async startRun(periodId:string): Promise<PayrollRun> { const r=await http.post<ApiOneResponse<PayrollRun>>(`/payroll/periods/${periodId}/start-run`); return r.data.data; },
  async getComponents(): Promise<PayrollComponent[]> { const r=await http.get<ApiListResponse<PayrollComponent>>('/payroll/components'); return r.data.data; },
  async createComponent(p:{code:string;name:string;category:string;calculation_type:string;default_amount?:number;taxable?:boolean}): Promise<PayrollComponent> { const r=await http.post<ApiOneResponse<PayrollComponent>>('/payroll/components',p); return r.data.data; },
  async deleteComponent(id:string): Promise<void> { await http.delete(`/payroll/components/${id}`); },
  async getPeriodEntries(periodId:string,page=1): Promise<{data:PayrollEntry[];meta:any}> { const r=await http.get(`/payroll/periods/${periodId}/entries?page=${page}`); return r.data; },
  async getEntry(id:string): Promise<PayrollEntry> { const r=await http.get<ApiOneResponse<PayrollEntry>>(`/payroll/entries/${id}`); return r.data.data; },
  async getPeriodSummary(periodId:string): Promise<PeriodSummary> { const r=await http.get<ApiOneResponse<PeriodSummary>>(`/payroll/periods/${periodId}/summary`); return r.data.data; },
  async getAdjustments(entryId:string): Promise<PayrollAdjustment[]> { const r=await http.get<ApiListResponse<PayrollAdjustment>>(`/payroll/entries/${entryId}/adjustments`); return r.data.data; },
  async createAdjustment(entryId:string,p:{component_id:string;amount:number;reason:string}): Promise<PayrollAdjustment> { const r=await http.post<ApiOneResponse<PayrollAdjustment>>(`/payroll/entries/${entryId}/adjustments`,p); return r.data.data; },
  async approveAdjustment(id:string): Promise<void> { await http.post(`/payroll/adjustments/${id}/approve`); },
  async rejectAdjustment(id:string): Promise<void> { await http.post(`/payroll/adjustments/${id}/reject`); },
  async getPayslips(): Promise<Payslip[]> { const r=await http.get<ApiListResponse<Payslip>>('/payroll/payslips'); return r.data.data; },
  async publishPayslips(periodId:string): Promise<void> { await http.post(`/payroll/periods/${periodId}/publish`); },
};
