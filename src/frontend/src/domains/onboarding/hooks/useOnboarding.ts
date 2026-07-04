'use client';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { onboardingService } from '@/domains/onboarding/services/onboardingService';
import type { CreateOnboardingPlanPayload, CreateOnboardingTemplatePayload } from '@/domains/onboarding/models/onboarding';

export function useOnboardingTemplates() { return useQuery({ queryKey: ['onboarding-templates'], queryFn: onboardingService.getTemplates }); }
export function useCreateOnboardingTemplate() { const qc=useQueryClient(); return useMutation({ mutationFn:(p:CreateOnboardingTemplatePayload)=>onboardingService.createTemplate(p), onSuccess:()=>qc.invalidateQueries({queryKey:['onboarding-templates']}) }); }
export function useOnboardingPlans() { return useQuery({ queryKey: ['onboarding-plans'], queryFn: onboardingService.getPlans }); }
export function useCreateOnboardingPlan() { const qc=useQueryClient(); return useMutation({ mutationFn:(p:CreateOnboardingPlanPayload)=>onboardingService.createPlan(p), onSuccess:()=>qc.invalidateQueries({queryKey:['onboarding-plans']}) }); }
export function useOnboardingPlanAction() { const qc=useQueryClient(); return useMutation({ mutationFn:({id,action}:{id:string;action:'activate'|'cancel'|'complete'})=>onboardingService[action+'Plan' as 'activatePlan'|'cancelPlan'|'completePlan'](id), onSuccess:()=>qc.invalidateQueries({queryKey:['onboarding-plans']}) }); }
