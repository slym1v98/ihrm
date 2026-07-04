'use client';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { recruitmentService } from '@/domains/recruitment/services/recruitmentService';
import type { CreateCandidatePayload, CreateRequisitionPayload } from '@/domains/recruitment/models/recruitment';

export function useRequisitions() { return useQuery({queryKey:['requisitions'],queryFn:recruitmentService.getRequisitions}); }
export function useCreateRequisition() { const qc=useQueryClient(); return useMutation({mutationFn:(p:CreateRequisitionPayload)=>recruitmentService.createRequisition(p),onSuccess:()=>qc.invalidateQueries({queryKey:['requisitions']})}); }
export function useCandidates() { return useQuery({queryKey:['candidates'],queryFn:recruitmentService.getCandidates}); }
export function useCreateCandidate() { const qc=useQueryClient(); return useMutation({mutationFn:(p:CreateCandidatePayload)=>recruitmentService.createCandidate(p),onSuccess:()=>qc.invalidateQueries({queryKey:['candidates']})}); }
export function useUpdateCandidateStage() { const qc=useQueryClient(); return useMutation({mutationFn:({id,stage}:{id:string;stage:string})=>recruitmentService.updateCandidateStage(id,stage),onSuccess:()=>qc.invalidateQueries({queryKey:['candidates']})}); }
