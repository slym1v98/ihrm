'use client';
import { useState, useCallback } from 'react'; import { useForm } from 'react-hook-form'; import { zodResolver } from '@hookform/resolvers/zod'; import { z } from 'zod'; import { toast } from 'sonner'; import { Plus, Calendar } from 'lucide-react';
import { useSettings, useSaveSetting, useHolidayCalendars, useCreateHolidayCalendar } from '@/domains/config/hooks/useConfig';
import type { HolidayCalendar, SystemSetting } from '@/domains/config/models/config';
import { DataTable, type Column } from '@/shared/components/DataTable'; import { Drawer, DrawerContent, DrawerHeader, DrawerTitle, DrawerDescription, DrawerBody, DrawerFooter } from '@/shared/components/ui/drawer';
import { Button } from '@/shared/components/ui/button'; import { Input } from '@/shared/components/ui/input'; import { Label } from '@/shared/components/ui/label'; import { extractErrorMessage } from '@/core/errors/messages';
import { Badge } from '@/shared/components/ui/badge';
import { Pencil } from 'lucide-react';
import { DATE_FORMAT_OPTIONS, DATETIME_FORMAT_OPTIONS } from '@/shared/lib/dateFormat';

const FORMAT_KEYS: Record<string, readonly string[]> = {
  'locale.date_format': DATE_FORMAT_OPTIONS,
  'locale.datetime_format': DATETIME_FORMAT_OPTIONS,
  'currency.position': ['suffix', 'prefix'],
  'currency.decimal_separator': [',', '.'],
  'currency.thousands_separator': ['.', ',', ' '],
};

const schema=z.object({key:z.string().min(1),value:z.string().min(1)});type F=z.infer<typeof schema>;
const calSchema=z.object({code:z.string().min(2),name:z.string().min(1),year:z.coerce.number().int().min(2000)});type CF=z.infer<typeof calSchema>;

export function ConfigListPage() {
  const {data:settings,isLoading:sl}=useSettings(); const save=useSaveSetting();
  const {data:cals,isLoading:cl}=useHolidayCalendars(); const createCal=useCreateHolidayCalendar();
  const [editKey,setEditKey]=useState<{key:string;value:string}|null>(null);
  const [calOpen,setCalOpen]=useState(false);

  const form=useForm<F>({resolver:zodResolver(schema),defaultValues:{key:'',value:''}});
  const calForm=useForm<CF>({resolver:zodResolver(calSchema),defaultValues:{code:'',name:'',year:new Date().getFullYear()}});

  const openEdit=useCallback((s:SystemSetting)=>{form.reset({key:s.key,value:s.value});setEditKey({key:s.key,value:s.value});},[form]);
  const onSubmitSetting=useCallback(async(v:F)=>{try{await save.mutateAsync(v);toast.success('Đã lưu cấu hình');setEditKey(null);}catch(raw){toast.error(extractErrorMessage(raw));}},[save]);
  const onSubmitCal=useCallback(async(v:CF)=>{try{await createCal.mutateAsync(v);toast.success('Tạo lịch thành công');setCalOpen(false);calForm.reset();}catch(raw){toast.error(extractErrorMessage(raw));}},[createCal,calForm]);

  const sCols:Column<SystemSetting>[]=[
    {header:'Key',accessor:'key',className:'font-mono text-xs w-48 truncate'},{header:'Giá trị',accessor:'value',className:'max-w-xs truncate'},
    {header:'Mô tả',accessor:'description',cell:(s)=>s.description??'—',className:'text-muted-foreground'},
    {header:'',accessor:undefined,className:'text-right w-12',cell:(s)=> <Button variant="ghost" size="sm" title="Sửa" onClick={()=>openEdit(s)}><Pencil className="h-4 w-4"/></Button>},
  ];
  const cCols:Column<HolidayCalendar>[]=[
    {header:'Mã',accessor:'code',className:'font-mono text-xs w-24'},{header:'Tên',accessor:'name'},
    {header:'Năm',accessor:'year',className:'w-16 text-right'},
  ];

  return (<div className="space-y-6">
    <div className="space-y-4"><div className="flex items-center justify-between"><span className="text-sm font-medium text-muted-foreground">Cấu hình hệ thống</span></div>
    <DataTable columns={sCols} data={settings??[]} isLoading={sl} rowKey="id" emptyMessage="Chưa có cấu hình"/></div>
    <div className="space-y-4"><div className="flex items-center justify-between"><span className="text-sm font-medium text-muted-foreground">Lịch nghỉ lễ</span>
    <Button size="sm" onClick={()=>{calForm.reset();setCalOpen(true);}}><Plus className="h-4 w-4 mr-1"/>Thêm lịch</Button></div>
    <DataTable columns={cCols} data={cals??[]} isLoading={cl} rowKey="id" emptyMessage="Chưa có lịch"/></div>

    <Drawer open={!!editKey} onOpenChange={(o)=>{if(!o)setEditKey(null);}}><DrawerContent size="sm"><DrawerHeader><DrawerTitle>Sửa cấu hình</DrawerTitle><DrawerDescription>Cập nhật giá trị cho {editKey?.key}</DrawerDescription></DrawerHeader>
      <DrawerBody><form id="cfg-form" onSubmit={form.handleSubmit(onSubmitSetting)} className="space-y-4">
        <div className="space-y-2"><Label>Key</Label><Input value={editKey?.key??''} disabled/></div>
        <div className="space-y-2"><Label htmlFor="val">Giá trị <span className="text-destructive">*</span></Label>
          {editKey && FORMAT_KEYS[editKey.key]
            ? <select id="val" className="h-8 w-full rounded-md border bg-[hsl(var(--card))] text-foreground px-2 text-[13px]" {...form.register('value')}>
                {FORMAT_KEYS[editKey.key].map(v=><option key={v} value={v}>{v}</option>)}
              </select>
            : <Input id="val" autoComplete="off"{...form.register('value')}/>
          }
        </div>
      </form></DrawerBody>
      <DrawerFooter><Button variant="ghost" onClick={()=>setEditKey(null)}>Hủy</Button><Button type="submit" form="cfg-form" disabled={save.isPending}>Lưu</Button></DrawerFooter>
    </DrawerContent></Drawer>

    <Drawer open={calOpen} onOpenChange={setCalOpen}><DrawerContent size="sm"><DrawerHeader><DrawerTitle>Thêm lịch nghỉ lễ</DrawerTitle><DrawerDescription>Nhập thông tin lịch nghỉ lễ mới</DrawerDescription></DrawerHeader>
      <DrawerBody><form id="cal-form" onSubmit={calForm.handleSubmit(onSubmitCal)} className="space-y-4">
        <div className="space-y-2"><Label htmlFor="code">Mã lịch <span className="text-destructive">*</span></Label><Input id="code" autoComplete="off"{...calForm.register('code')}/></div>
        <div className="space-y-2"><Label htmlFor="name">Tên lịch <span className="text-destructive">*</span></Label><Input id="name" autoComplete="off"{...calForm.register('name')}/></div>
        <div className="space-y-2"><Label htmlFor="year">Năm <span className="text-destructive">*</span></Label><Input id="year" type="number" autoComplete="off"{...calForm.register('year')}/></div>
      </form></DrawerBody>
      <DrawerFooter><Button variant="ghost" onClick={()=>setCalOpen(false)}>Hủy</Button><Button type="submit" form="cal-form" disabled={createCal.isPending}>Tạo</Button></DrawerFooter>
    </DrawerContent></Drawer></div>);
}
