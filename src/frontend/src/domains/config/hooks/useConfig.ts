'use client';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { configService } from '@/domains/config/services/configService';

export function useSettings() { return useQuery({queryKey:['settings'],queryFn:configService.getSettings}); }
export function useSaveSetting() { const qc=useQueryClient(); return useMutation({mutationFn:({key,value}:{key:string;value:string})=>configService.saveSetting(key,value),onSuccess:()=>qc.invalidateQueries({queryKey:['settings']})}); }
export function useHolidayCalendars() { return useQuery({queryKey:['holiday-calendars'],queryFn:configService.getHolidayCalendars}); }
export function useCreateHolidayCalendar() { const qc=useQueryClient(); return useMutation({mutationFn:(p:{code:string;name:string;year:number})=>configService.createHolidayCalendar(p),onSuccess:()=>qc.invalidateQueries({queryKey:['holiday-calendars']})}); }
