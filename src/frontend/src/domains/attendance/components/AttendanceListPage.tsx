'use client';

import { useState, useCallback } from 'react';
import { toast } from 'sonner';
import { Calculator, Lock, Unlock } from 'lucide-react';
import { useAttendancePeriods, useAttendanceTimesheets, useCalculateAttendance, useClosePeriod, useReopenPeriod } from '@/domains/attendance/hooks/useAttendance';
import type { AttendanceTimesheet, AttendancePeriod } from '@/domains/attendance/models/attendance';
import { DataTable, type Column } from '@/shared/components/DataTable';
import { Button } from '@/shared/components/ui/button';
import { Badge } from '@/shared/components/ui/badge';
import { extractErrorMessage } from '@/core/errors/messages';
import { useDateFormatter } from '@/shared/hooks/useDateFormatter';

const periodStatusLabels: Record<string, string> = {
  open: 'Đang mở',
  closed: 'Đã chốt',
};

const resultLabels: Record<string, string> = {
  present: 'Có mặt',
  absent: 'Vắng',
  late: 'Đi muộn',
  early_leave: 'Về sớm',
};

const resultVariants: Record<string, 'default' | 'secondary' | 'destructive' | 'outline'> = {
  present: 'default',
  absent: 'destructive',
  late: 'outline',
  early_leave: 'secondary',
};

export function AttendanceListPage() {
  const { formatDate } = useDateFormatter();
  const { data: periods, isLoading: periodsLoading } = useAttendancePeriods();
  const { data: timesheets, isLoading: timesheetsLoading } = useAttendanceTimesheets();
  const calcAttendance = useCalculateAttendance();
  const closePeriod = useClosePeriod();
  const reopenPeriod = useReopenPeriod();

  const handleCalculate = useCallback(async () => {
    try {
      await calcAttendance.mutateAsync();
      toast.success('Tính công thành công');
    } catch (raw) {
      toast.error(extractErrorMessage(raw));
    }
  }, [calcAttendance]);

  const handleClosePeriod = useCallback(async (id: string) => {
    try {
      await closePeriod.mutateAsync(id);
      toast.success('Đã chốt kỳ công');
    } catch (raw) {
      toast.error(extractErrorMessage(raw));
    }
  }, [closePeriod]);

  const handleReopenPeriod = useCallback(async (id: string) => {
    try {
      await reopenPeriod.mutateAsync(id);
      toast.success('Đã mở lại kỳ công');
    } catch (raw) {
      toast.error(extractErrorMessage(raw));
    }
  }, [reopenPeriod]);

  const periodColumns: Column<AttendancePeriod>[] = [
    { header: 'Mã kỳ', accessor: 'period_code', className: 'font-mono text-xs w-32' },
    { header: 'Ngày bắt đầu', accessor: undefined, cell: (p) => formatDate(p.start_date), className: 'font-mono text-xs w-28' },
    { header: 'Ngày kết thúc', accessor: undefined, cell: (p) => formatDate(p.end_date), className: 'font-mono text-xs w-28' },
    {
      header: 'Trạng thái', accessor: undefined, className: 'w-20',
      cell: (p) => <Badge variant={p.status === 'open' ? 'default' : 'secondary'}>{periodStatusLabels[p.status] ?? p.status}</Badge>,
    },
    {
      header: 'Thao tác', accessor: undefined, className: 'text-right w-20',
      cell: (p) => (
        <div className="flex justify-end gap-1">
          {p.status === 'open' ? (
            <Button variant="ghost" size="sm" title="Chốt kỳ" onClick={() => handleClosePeriod(p.id)}>
              <Lock className="h-4 w-4" />
            </Button>
          ) : (
            <Button variant="ghost" size="sm" title="Mở lại" onClick={() => handleReopenPeriod(p.id)}>
              <Unlock className="h-4 w-4" />
            </Button>
          )}
        </div>
      ),
    },
  ];

  const timesheetColumns: Column<AttendanceTimesheet>[] = [
    { header: 'Ngày', accessor: undefined, cell: (t) => formatDate(t.work_date), className: 'font-mono text-xs w-28' },
    { header: 'Dự kiến (ph)', accessor: 'expected_minutes', className: 'text-right w-20' },
    { header: 'Làm (ph)', accessor: 'worked_minutes', className: 'text-right w-20' },
    { header: 'Muộn (ph)', accessor: 'late_minutes', className: 'text-right w-16 text-destructive' },
    { header: 'Về sớm (ph)', accessor: 'early_leave_minutes', className: 'text-right w-20 text-destructive' },
    { header: 'OT (ph)', accessor: 'overtime_minutes', className: 'text-right w-16 text-green-600' },
    {
      header: 'Kết quả', accessor: undefined, className: 'w-20',
      cell: (t) => <Badge variant={resultVariants[t.result_status] ?? 'secondary'}>{resultLabels[t.result_status] ?? t.result_status}</Badge>,
    },
  ];

  return (
    <div className="space-y-6">
      <div className="space-y-4">
        <div className="flex items-center justify-between">
          <span className="text-sm font-medium text-muted-foreground">Kỳ công</span>
          <Button size="sm" variant="ghost" onClick={handleCalculate} disabled={calcAttendance.isPending}>
            <Calculator className="h-4 w-4 mr-1" /> Tính công
          </Button>
        </div>
        <DataTable<AttendancePeriod> columns={periodColumns} data={periods ?? []} isLoading={periodsLoading} rowKey="id" emptyMessage="Chưa có kỳ công nào" />
      </div>

      <div className="space-y-4">
        <span className="text-sm font-medium text-muted-foreground">Bảng chấm công</span>
        <DataTable<AttendanceTimesheet> columns={timesheetColumns} data={timesheets ?? []} isLoading={timesheetsLoading} rowKey="id" emptyMessage="Chưa có dữ liệu chấm công" />
      </div>
    </div>
  );
}
