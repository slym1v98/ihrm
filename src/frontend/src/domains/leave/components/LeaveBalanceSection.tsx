'use client';
import { useLeaveBalances } from '@/domains/leave/hooks/useLeave';
import { useLeaveTypes } from '@/domains/leave/hooks/useLeave';
import type { LeaveBalance } from '@/domains/leave/models/leave';
import { DataTable, type Column } from '@/shared/components/DataTable';

export function LeaveBalanceSection({employeeId}:{employeeId?:string}) {
  const {data:balances,isLoading}=useLeaveBalances(); const {data:types}=useLeaveTypes();
  const filtered = employeeId ? balances?.filter(b=>b.employee_id===employeeId) : balances;
  const typeName=(id:string)=>types?.find(t=>t.id===id)?.name??id;
  const cols:Column<LeaveBalance>[]=[
    {header:'Loại nghỉ',accessor:undefined,cell:(b)=>typeName(b.leave_type_id)},
    {header:'Năm',accessor:'year',className:'w-16 text-right'},
    {header:'Đầu kỳ',accessor:'opening',className:'text-right w-16'},
    {header:'Phát sinh',accessor:'accrued',className:'text-right w-16'},
    {header:'Đã dùng',accessor:'used',className:'text-right w-16'},
    {header:'Còn lại',accessor:'remaining',className:'text-right w-16 font-semibold'},
  ];
  return (<DataTable columns={cols} data={filtered??[]} isLoading={isLoading} rowKey={b=>b.employee_id+b.leave_type_id+b.year} emptyMessage="Chưa có số dư"/>);
}
