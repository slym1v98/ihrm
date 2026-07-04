'use client';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { performanceService } from '@/domains/performance/services/performanceService';
import type { CreateCyclePayload, CreateGoalPayload } from '@/domains/performance/models/performance';

export function usePerformanceCycles() { return useQuery({queryKey:['perf-cycles'],queryFn:performanceService.getCycles}); }
export function useCreateCycle() { const qc=useQueryClient(); return useMutation({mutationFn:(p:CreateCyclePayload)=>performanceService.createCycle(p),onSuccess:()=>qc.invalidateQueries({queryKey:['perf-cycles']})}); }
export function useCycleAction() { const qc=useQueryClient(); return useMutation({mutationFn:({id,action}:{id:string;action:'activate'|'complete'|'cancel'})=>performanceService.cycleAction(id,action),onSuccess:()=>qc.invalidateQueries({queryKey:['perf-cycles']})}); }
export function useGoals() { return useQuery({queryKey:['perf-goals'],queryFn:performanceService.getGoals}); }
export function useCreateGoal() { const qc=useQueryClient(); return useMutation({mutationFn:(p:CreateGoalPayload)=>performanceService.createGoal(p),onSuccess:()=>qc.invalidateQueries({queryKey:['perf-goals']})}); }
export function useReviews() { return useQuery({queryKey:['perf-reviews'],queryFn:performanceService.getReviews}); }
export function useSubmitReview() { const qc=useQueryClient(); return useMutation({mutationFn:({id,role,assessment}:{id:string;role:'self'|'manager'|'hr';assessment:Record<string,unknown>})=>performanceService.submitReview(id,role,assessment),onSuccess:()=>qc.invalidateQueries({queryKey:['perf-reviews']})}); }
export function useFinalizeReview() { const qc=useQueryClient(); return useMutation({mutationFn:({id,finalScore}:{id:string;finalScore:number})=>performanceService.finalizeReview(id,finalScore),onSuccess:()=>qc.invalidateQueries({queryKey:['perf-reviews']})}); }
export function useCompleteGoal() { const qc=useQueryClient(); return useMutation({mutationFn:(id:string)=>performanceService.completeGoal(id),onSuccess:()=>qc.invalidateQueries({queryKey:['perf-goals']})}); }
