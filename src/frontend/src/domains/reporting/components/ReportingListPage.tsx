'use client';
import { useReportDefinitions, useRunReport, useReportRuns } from '@/domains/reporting/hooks/useReporting';
import type { ReportDefinition, ReportRun } from '@/domains/reporting/models/reporting';
import { DataTable, type Column } from '@/shared/components/DataTable';
import { Drawer, DrawerContent, DrawerHeader, DrawerTitle, DrawerDescription, DrawerBody, DrawerFooter } from '@/shared/components/ui/drawer';
import { Button } from '@/shared/components/ui/button';
import { Badge } from '@/shared/components/ui/badge';
import { toast } from 'sonner';
import { useState, useCallback } from 'react';
import { Play } from 'lucide-react';
import { extractErrorMessage } from '@/core/errors/messages';
import { useDateFormatter } from '@/shared/hooks/useDateFormatter';

const statusL: Record<string, string> = { pending: 'Chờ chạy', running: 'Đang chạy', completed: 'Hoàn thành', failed: 'Lỗi' };
const statusV: Record<string, 'default' | 'secondary' | 'destructive' | 'outline'> = { pending: 'secondary', running: 'default', completed: 'outline', failed: 'destructive' };

export function ReportingListPage() {
  const { formatDate, formatDateTime } = useDateFormatter();
  const { data: defs, isLoading: loadingDefs } = useReportDefinitions();
  const { data: runs, isLoading: loadingRuns } = useReportRuns();
  const runReport = useRunReport();
  const [runOpen, setRunOpen] = useState(false);
  const [selectedDef, setSelectedDef] = useState<ReportDefinition | null>(null);

  const openRun = useCallback((d: ReportDefinition) => { setSelectedDef(d); setRunOpen(true); }, []);
  const handleRun = useCallback(async () => {
    if (!selectedDef) return;
    try { await runReport.mutateAsync({ code: selectedDef.code }); toast.success('Đã gửi yêu cầu chạy báo cáo'); setRunOpen(false); }
    catch (raw) { toast.error(extractErrorMessage(raw)); }
  }, [selectedDef, runReport]);

  const defCols: Column<ReportDefinition>[] = [
    { header: 'Mã', accessor: 'code', className: 'font-mono text-xs w-24' },
    { header: 'Tên báo cáo', accessor: 'name' },
    { header: 'Trạng thái', accessor: undefined, className: 'w-20', cell: (d) => <Badge variant={d.is_active ? 'default' : 'secondary'}>{d.is_active ? 'Hoạt động' : 'Tắt'}</Badge> },
    { header: '', accessor: undefined, className: 'text-right w-16', cell: (d) => <Button variant="ghost" size="sm" title="Chạy báo cáo" onClick={() => openRun(d)} disabled={!d.is_active}><Play className="h-4 w-4 text-blue-600" /></Button> },
  ];
  const runCols: Column<ReportRun>[] = [
    { header: 'Báo cáo', accessor: 'report_definition_id', className: 'font-mono text-xs' },
    { header: 'Trạng thái', accessor: undefined, className: 'w-20', cell: (r) => <Badge variant={statusV[r.status]}>{statusL[r.status]}</Badge> },
    { header: 'Bắt đầu', accessor: undefined, className: 'font-mono text-xs w-32', cell: (r) => r.started_at ? formatDateTime(r.started_at) : '-' },
    { header: 'Lỗi', accessor: undefined, className: 'max-w-xs truncate', cell: (r) => r.error ?? '-' },
  ];

  return (<div className="space-y-6">
    <div><h2 className="text-sm font-semibold mb-2">Danh sách báo cáo</h2>
      <DataTable columns={defCols} data={defs ?? []} isLoading={loadingDefs} rowKey="id" emptyMessage="Chưa có báo cáo nào" /></div>
    <div><h2 className="text-sm font-semibold mb-2">Lịch sử chạy</h2>
      <DataTable columns={runCols} data={runs ?? []} isLoading={loadingRuns} rowKey="id" emptyMessage="Chưa có lịch sử" /></div>
    <Drawer open={runOpen} onOpenChange={setRunOpen}><DrawerContent size="sm"><DrawerHeader><DrawerTitle>Chạy báo cáo</DrawerTitle><DrawerDescription>Xác nhận chạy báo cáo {selectedDef?.name}?</DrawerDescription></DrawerHeader>
      <DrawerBody><p className="text-[13px] text-muted-foreground">Kết quả sẽ hiển thị sau khi hoàn thành.</p></DrawerBody>
      <DrawerFooter><Button variant="ghost" onClick={() => setRunOpen(false)}>Hủy</Button><Button onClick={handleRun} disabled={runReport.isPending}>Chạy</Button></DrawerFooter>
    </DrawerContent></Drawer></div>);
}
