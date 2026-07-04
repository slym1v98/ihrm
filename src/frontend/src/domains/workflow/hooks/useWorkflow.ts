'use client';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { workflowService } from '@/domains/workflow/services/workflowService';

export function useWorkflowTemplates() { return useQuery({queryKey:['wf-templates'],queryFn:workflowService.getTemplates}); }
export function useWorkflowRequests() { return useQuery({queryKey:['wf-requests'],queryFn:workflowService.getRequests}); }
export function useStartWorkflow() { const qc=useQueryClient(); return useMutation({mutationFn:(p:{workflow_template_id:string;subject_type:string;subject_id:string})=>workflowService.startRequest(p),onSuccess:()=>qc.invalidateQueries({queryKey:['wf-requests']})}); }
export function useWorkflowAction() { const qc=useQueryClient(); return useMutation({mutationFn:({id,action}:{id:string;action:'approve'|'reject'|'cancel'})=>workflowService[action+'Request' as 'approveRequest'|'rejectRequest'|'cancelRequest'](id),onSuccess:()=>qc.invalidateQueries({queryKey:['wf-requests']})}); }
