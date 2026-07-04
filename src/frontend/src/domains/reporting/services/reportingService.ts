import { http } from '@/core/http/client';
import type { ReportDefinition, ReportRun } from '@/domains/reporting/models/reporting';
interface ApiListResponse<T> { data: T[] }

export const reportingService = {
  async getDefinitions(): Promise<ReportDefinition[]> { const r=await http.get<ApiListResponse<ReportDefinition>>('/reports'); return r.data.data; },
  async runReport(code:string, filters?:Record<string,unknown>): Promise<ReportRun> { const r=await http.post<{data:ReportRun}>(`/reports/${code}/runs`, {filters}); return r.data.data; },
  async getRuns(): Promise<ReportRun[]> { const r=await http.get<ApiListResponse<ReportRun>>('/report-runs'); return r.data.data; },
};
