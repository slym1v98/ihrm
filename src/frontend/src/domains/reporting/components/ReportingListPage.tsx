'use client';
import { useState } from 'react'; import { toast } from 'sonner'; import { Play, FileText } from 'lucide-react';
import { useReportDefinitions, useRunReport, useReportRuns } from '@/domains/reporting/hooks/useReporting';
import type { ReportDefinition, ReportRun } from '@/domains/reporting/models/reporting';
import { DataTable, type Column } from '@/shared/components/DataTable'; import { Drawer, DrawerContent, DrawerHeader, DrawerTitle, DrawerDescription, DrawerBody, DrawerFooter } from '@/shared/components/ui/drawer';
import { Button } from '@/shared/components/ui/button'; import { Badge } from '@/shared/components/ui/badge'; import { extractErrorMessage } from '@/core/errors/messages';

const statusL: Record<string,string>={pending:'Đang chạy',completed:'Hoàn thành',failed:'Lỗi'};
const statusV: Record<string,'default'|'secondary'|'destructive'|'outline'>={pending:'secondary',completed:'default',failed:'destructive'};

export function ReportingListPage() {
  const {data:defs}=useReportDefinitions(); const {data:runs}=useReportRuns(); const runReport=useRunReport();
  const [runCode,setRunCode]=useState<string|null>(null);

  const defCols:Column<ReportDefinition>[]=[
    {header:'Mã',accessor:'code',className:'font-mono text-xs w-24'},{header:'Tên báo cáo',accessor:'name'},
    {header:'Trạng thái',accessor:undefined,className:'w-20',cell:(d)=><Badge variant={d.is_active?'default':'secondary'}>{d.is_active?'Hoạt động':'Ngừng'}</Badge>},
    {header:'Thao tác',accessor:undefined,className:'text-right w-20',cell:(d)=>d.is_active&&<Button variant="ghost" size="sm" title="Chạy báo cáo" onClick={()=>setRunCode(d.code)}><Play className="h-4 w-4"/></Button>},
  ];
  const runCols:Column<ReportRun>[]=[
    {header:'Báo cáo',accessor:'report_definition_id',className:'font-mono text-xs w-36 truncate'},
    {header:'Trạng thái',accessor:undefined,className:'w-20',cell:(r)=><Badge variant={statusV[r.status]}>{statusL[r.status]}</Badge>},
    {header:'Lỗi',accessor:'error',cell:(r)=>r.error??'—',className:'max-w-xs truncate text-muted-foreground'},
    {header:'Hoàn thành',accessor:'completed_at',className:'font-mono text-xs w-32 text-muted-foreground'},
  ];

  return (<div className="space-y-6"><div className="space-y-4"><span className="text-sm font-medium text-muted-foreground">Định nghĩa báo cáo</span>
    <DataTable columns={defCols} data={defs??[]} rowKey="id" emptyMessage="Chưa có báo cáo nào"/></div>
    <div className="space-y-4"><span className="text-sm font-medium text-muted-foreground">Lịch sử chạy báo cáo</span>
    <DataTable columns={runCols} data={runs??[]} rowKey="id" emptyMessage="Chưa có lịch sử"/></div>
    <Drawer open={!!runCode} onOpenChange={(o)=>{if(!o)setRunCode(null);}}><DrawerContent size="sm"><DrawerHeader><DrawerTitle>Chạy báo cáo</DrawerTitle><DrawerDescription>Xác nhận chạy báo cáo ngay?</DrawerDescription></DrawerHeader>
      <DrawerBody><p className="text-sm text-muted-foreground">Báo cáo sẽ được xử lý và kết quả sẽ hiển thị trong lịch sử bên dưới.</p></DrawerBody>
      <DrawerFooter><Button variant="ghost" onClick={()=>setRunCode(null)}>Hủy</Button>
        <Button disabled={runReport.isPending} onClick={async()=>{try{if(!runCode)return;await runReport.mutateAsync({code:runCode});toast.success('Đã chạy báo cáo');setRunCode(null);}catch(raw){toast.error(extractErrorMessage(raw));}}}>Chạy</Button>
    </DrawerFooter></DrawerContent></Drawer></div>);
}
