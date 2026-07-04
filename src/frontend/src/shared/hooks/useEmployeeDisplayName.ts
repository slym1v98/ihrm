'use client';
import { useEmployees } from '@/domains/employee/hooks/useEmployees';

export function useEmployeeDisplayName() {
  const { data } = useEmployees();
  const employees = data?.data ?? [];

  function getDisplayName(employeeId: string | null | undefined): string {
    if (!employeeId) return '—';
    const emp = employees.find(e => e.id === employeeId);
    if (!emp) return employeeId; // fallback to raw UUID
    return `[${emp.employee_code}] ${emp.last_name} ${emp.first_name}`;
  }

  return { getDisplayName };
}
