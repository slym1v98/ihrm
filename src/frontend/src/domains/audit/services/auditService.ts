import { http } from '@/core/http/client';
import type { AuditLog } from '@/domains/audit/models/audit';
interface ApiListResponse<T> { data: T[] }
export const auditService = { async getLogs(): Promise<AuditLog[]> { const r=await http.get<ApiListResponse<AuditLog>>('/audit-logs'); return r.data.data; }, };
