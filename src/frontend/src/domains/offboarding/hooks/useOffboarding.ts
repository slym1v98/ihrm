'use client';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { offboardingService } from '@/domains/offboarding/services/offboardingService';
import type { CreateOffboardingPayload } from '@/domains/offboarding/models/offboarding';
export function useOffboardingRequests() { return useQuery({queryKey:['offboarding-requests'],queryFn:offboardingService.getRequests}); }
export function useCreateOffboardingRequest() { const qc=useQueryClient(); return useMutation({mutationFn:(p:CreateOffboardingPayload)=>offboardingService.createRequest(p),onSuccess:()=>qc.invalidateQueries({queryKey:['offboarding-requests']})}); }
export function useOffboardingAction() { const qc=useQueryClient(); return useMutation({mutationFn:({id,action}:{id:string;action:'submit'|'approve'|'reject'})=>offboardingService[action=='approve'?'approveRequest':action=='submit'?'submitRequest':'rejectRequest'](id),onSuccess:()=>qc.invalidateQueries({queryKey:['offboarding-requests']})}); }
