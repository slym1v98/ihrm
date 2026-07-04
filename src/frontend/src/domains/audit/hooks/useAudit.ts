'use client';
import { useQuery } from '@tanstack/react-query';
import { auditService } from '@/domains/audit/services/auditService';
export function useAuditLogs() { return useQuery({queryKey:['audit-logs'],queryFn:auditService.getLogs}); }
