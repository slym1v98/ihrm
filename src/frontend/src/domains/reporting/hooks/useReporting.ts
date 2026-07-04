'use client';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { reportingService } from '@/domains/reporting/services/reportingService';

export function useReportDefinitions() { return useQuery({queryKey:['report-defs'],queryFn:reportingService.getDefinitions}); }
export function useRunReport() { const qc=useQueryClient(); return useMutation({mutationFn:({code,filters}:{code:string;filters?:Record<string,unknown>})=>reportingService.runReport(code,filters),onSuccess:()=>qc.invalidateQueries({queryKey:['report-runs']})}); }
export function useReportRuns() { return useQuery({queryKey:['report-runs'],queryFn:reportingService.getRuns}); }
