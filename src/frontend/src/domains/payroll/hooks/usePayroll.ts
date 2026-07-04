'use client';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { payrollService } from '@/domains/payroll/services/payrollService';

export function usePayrollPeriods() { return useQuery({queryKey:['payroll-periods'],queryFn:payrollService.getPeriods}); }
export function useCreatePeriod() { const qc=useQueryClient(); return useMutation({mutationFn:(p:any)=>payrollService.createPeriod(p),onSuccess:()=>qc.invalidateQueries({queryKey:['payroll-periods']})}); }
export function usePayrollAction() { const qc=useQueryClient(); return useMutation({mutationFn:({id,action}:{id:string;action:'submit-approval'|'approve'|'reject'|'lock'|'reopen'})=>payrollService[action==='submit-approval'?'submitApproval':action==='approve'?'approvePeriod':action==='reject'?'rejectPeriod':action==='lock'?'lockPeriod':'reopenPeriod'](id),onSuccess:()=>qc.invalidateQueries({queryKey:['payroll-periods']})}); }
export function useStartRun() { const qc=useQueryClient(); return useMutation({mutationFn:(periodId:string)=>payrollService.startRun(periodId),onSuccess:()=>qc.invalidateQueries({queryKey:['payroll-periods']})}); }
export function usePayrollComponents() { return useQuery({queryKey:['payroll-components'],queryFn:payrollService.getComponents}); }
export function useCreateComponent() { const qc=useQueryClient(); return useMutation({mutationFn:(p:any)=>payrollService.createComponent(p),onSuccess:()=>qc.invalidateQueries({queryKey:['payroll-components']})}); }
export function useDeleteComponent() { const qc=useQueryClient(); return useMutation({mutationFn:(id:string)=>payrollService.deleteComponent(id),onSuccess:()=>qc.invalidateQueries({queryKey:['payroll-components']})}); }
export function usePayslips() { return useQuery({queryKey:['payroll-payslips'],queryFn:payrollService.getPayslips}); }
export function usePublishPayslips() { const qc=useQueryClient(); return useMutation({mutationFn:(periodId:string)=>payrollService.publishPayslips(periodId),onSuccess:()=>qc.invalidateQueries({queryKey:['payroll-payslips']})}); }
export function usePeriodEntries(periodId:string|null) { return useQuery({queryKey:['payroll-entries',periodId],queryFn:()=>payrollService.getPeriodEntries(periodId!),enabled:!!periodId}); }
export function useEntry(id:string|null) { return useQuery({queryKey:['payroll-entry',id],queryFn:()=>payrollService.getEntry(id!),enabled:!!id}); }
export function usePeriodSummary(periodId:string|null) { return useQuery({queryKey:['payroll-summary',periodId],queryFn:()=>payrollService.getPeriodSummary(periodId!),enabled:!!periodId}); }
export function useAdjustments(entryId:string|null) { return useQuery({queryKey:['payroll-adjustments',entryId],queryFn:()=>payrollService.getAdjustments(entryId!),enabled:!!entryId}); }
export function useCreateAdjustment() { const qc=useQueryClient(); return useMutation({mutationFn:({entryId,...p}:{entryId:string;component_id:string;amount:number;reason:string})=>payrollService.createAdjustment(entryId,p),onSuccess:(_,v)=>qc.invalidateQueries({queryKey:['payroll-adjustments',v.entryId]})}); }
export function useApproveAdjustment() { const qc=useQueryClient(); return useMutation({mutationFn:(id:string)=>payrollService.approveAdjustment(id),onSuccess:()=>qc.invalidateQueries({queryKey:['payroll-adjustments']})}); }
export function useRejectAdjustment() { const qc=useQueryClient(); return useMutation({mutationFn:(id:string)=>payrollService.rejectAdjustment(id),onSuccess:()=>qc.invalidateQueries({queryKey:['payroll-adjustments']})}); }
