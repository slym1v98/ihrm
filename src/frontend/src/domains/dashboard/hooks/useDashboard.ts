'use client';
import { useQuery } from '@tanstack/react-query';
import { http } from '@/core/http/client';

export function useDashboardSummary() {
  return useQuery({
    queryKey: ['dashboard-summary'],
    queryFn: async () => {
      const [empRes, leaveRes, attRes, payPeriodRes] = await Promise.allSettled([
        http.get<{ meta: { total: number } }>('/employees?per_page=1'),
        http.get<{ meta: { total: number } }>('/leave-requests?per_page=1'),
        http.get<{ meta: { total: number } }>('/attendance/raw-logs?per_page=1'),
        http.get<{ meta: { total: number } }>('/payroll/periods?per_page=1'),
      ]);
      return {
        employees: empRes.status === 'fulfilled' ? empRes.value.data.meta.total : 0,
        leaves: leaveRes.status === 'fulfilled' ? leaveRes.value.data.meta.total : 0,
        attendances: attRes.status === 'fulfilled' ? attRes.value.data.meta.total : 0,
        payrollPeriods: payPeriodRes.status === 'fulfilled' ? payPeriodRes.value.data.meta.total : 0,
      };
    },
    refetchInterval: 60000,
  });
}
