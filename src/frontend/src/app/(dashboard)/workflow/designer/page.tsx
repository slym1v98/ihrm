'use client';
import { useState, useCallback } from 'react';
import { useWorkflowTemplates } from '@/domains/workflow/hooks/useWorkflow';
import { DynamicForm, type FormFieldSchema } from '@/shared/components/DynamicForm';
import { DataTable, type Column } from '@/shared/components/DataTable';
import { Drawer, DrawerContent, DrawerHeader, DrawerTitle, DrawerDescription, DrawerBody, DrawerFooter } from '@/shared/components/ui/drawer';
import { Button } from '@/shared/components/ui/button';
import { Input } from '@/shared/components/ui/input';
import { Label } from '@/shared/components/ui/label';
import { Badge } from '@/shared/components/ui/badge';
import { toast } from 'sonner';
import { Plus, Pencil, Trash2, X, GripVertical } from 'lucide-react';
import { extractErrorMessage } from '@/core/errors/messages';
import type { WorkflowTemplate } from '@/domains/workflow/models/workflow';

interface StepDef { id: string; name: string; assignee_type: string; assignee_id?: string; form_schema: FormFieldSchema[]; execution_type: string; }

export default function WorkflowDesignerPage() {
  const { data: templates, isLoading } = useWorkflowTemplates();
  const [selectedTemplate, setSelectedTemplate] = useState<WorkflowTemplate | null>(null);
  const [steps, setSteps] = useState<StepDef[]>([]);
  const [drawerOpen, setDrawerOpen] = useState(false);
  const [editingStep, setEditingStep] = useState<number | null>(null);

  const openDesigner = useCallback((t: WorkflowTemplate) => {
    setSelectedTemplate(t);
    setSteps([]);
    setDrawerOpen(true);
  }, []);

  const addStep = useCallback(() => {
    const idx = steps.length + 1;
    setSteps(prev => [...prev, { id: `step-${idx}`, name: `Bước ${idx}`, assignee_type: 'specific_user', assignee_id: '', form_schema: [], execution_type: 'sequential' }]);
    setEditingStep(steps.length);
  }, [steps.length]);

  const updateStep = useCallback((idx: number, field: string, value: unknown) => {
    setSteps(prev => prev.map((s, i) => i === idx ? { ...s, [field]: value } : s));
  }, []);

  const removeStep = useCallback((idx: number) => {
    setSteps(prev => prev.filter((_, i) => i !== idx));
  }, []);

  const moveStep = useCallback((idx: number, dir: -1 | 1) => {
    setSteps(prev => {
      const arr = [...prev];
      const target = idx + dir;
      if (target < 0 || target >= arr.length) return arr;
      [arr[idx], arr[target]] = [arr[target], arr[idx]];
      return arr.map((s, i) => ({ ...s, name: `Bước ${i + 1}` }));
    });
  }, []);

  const addField = useCallback((stepIdx: number) => {
    updateStep(stepIdx, 'form_schema', [...(steps[stepIdx]?.form_schema ?? []), { key: '', type: 'text', label: '', required: false }]);
  }, [steps, updateStep]);

  const updateField = useCallback((stepIdx: number, fieldIdx: number, field: string, value: unknown) => {
    setSteps(prev => prev.map((s, i) => {
      if (i !== stepIdx) return s;
      const fs = [...(s.form_schema ?? [])];
      fs[fieldIdx] = { ...fs[fieldIdx], [field]: value };
      return { ...s, form_schema: fs };
    }));
  }, []);

  const removeField = useCallback((stepIdx: number, fieldIdx: number) => {
    setSteps(prev => prev.map((s, i) => {
      if (i !== stepIdx) return s;
      return { ...s, form_schema: s.form_schema.filter((_, fi) => fi !== fieldIdx) };
    }));
  }, []);

  const cols: Column<WorkflowTemplate>[] = [
    { header: 'Mã', accessor: 'code', className: 'font-mono text-xs w-24' },
    { header: 'Tên template', accessor: 'name' },
    { header: 'Trạng thái', accessor: undefined, className: 'w-20', cell: (t) => <Badge variant={t.active ? 'default' : 'secondary'}>{t.active ? 'Kích hoạt' : 'Tắt'}</Badge> },
    { header: '', accessor: undefined, className: 'text-right w-16', cell: (t) => <Button variant="ghost" size="sm" title="Thiết kế" onClick={() => openDesigner(t)}><Pencil className="h-4 w-4" /></Button> },
  ];

  return (<div className="space-y-4">
    <h2 className="text-sm font-semibold">Thiết kế Workflow</h2>
    <DataTable columns={cols} data={templates ?? []} isLoading={isLoading} rowKey="id" emptyMessage="Chưa có template" />

    <Drawer open={drawerOpen} onOpenChange={setDrawerOpen}>
      <DrawerContent><DrawerHeader><DrawerTitle>Thiết kế: {selectedTemplate?.name}</DrawerTitle>
        <DrawerDescription>Thêm/sắp xếp các bước và cấu hình form từng bước</DrawerDescription></DrawerHeader>
      <DrawerBody className="overflow-y-auto">
        <div className="space-y-6">
          {steps.map((step, idx) => (<div key={step.id} className="border rounded-lg p-4 space-y-3 bg-[hsl(var(--card))]">
            <div className="flex items-center gap-2">
              <Button variant="ghost" size="sm" onClick={() => moveStep(idx, -1)} disabled={idx === 0}><span className="text-xs">▲</span></Button>
              <Button variant="ghost" size="sm" onClick={() => moveStep(idx, 1)} disabled={idx === steps.length - 1}><span className="text-xs">▼</span></Button>
              <span className="text-sm font-medium w-32">{step.name}</span>
              <div className="flex-1 grid grid-cols-3 gap-2">
                <Input size={6} placeholder="Tên bước" value={step.name} onChange={e => updateStep(idx, 'name', e.target.value)} className="text-xs" />
                <select value={step.assignee_type} onChange={e => updateStep(idx, 'assignee_type', e.target.value)}
                  className="rounded-md border bg-[hsl(var(--card))] px-1 text-xs">
                  <option value="specific_user">Người cụ thể</option><option value="role">Vai trò</option><option value="department">Phòng ban</option>
                </select>
                <select value={step.execution_type} onChange={e => updateStep(idx, 'execution_type', e.target.value)}
                  className="rounded-md border bg-[hsl(var(--card))] px-1 text-xs">
                  <option value="sequential">Tuần tự</option><option value="all_of">Tất cả</option><option value="any_of">Một người</option>
                </select>
              </div>
              <Button variant="ghost" size="sm" onClick={() => removeStep(idx)}><Trash2 className="h-4 w-4 text-destructive" /></Button>
            </div>
            <div className="ml-2 space-y-2">
              <div className="flex items-center gap-2"><span className="text-xs text-muted-foreground">Form fields:</span><Button variant="ghost" size="sm" onClick={() => addField(idx)}><Plus className="h-3 w-3" /></Button></div>
              {(step.form_schema ?? []).map((f, fi) => (
                <div key={fi} className="flex items-center gap-1 ml-4">
                  <Input size={4} placeholder="Key" value={f.key} onChange={e => updateField(idx, fi, 'key', e.target.value)} className="text-xs w-20" />
                  <Input size={8} placeholder="Label" value={f.label} onChange={e => updateField(idx, fi, 'label', e.target.value)} className="text-xs w-28" />
                  <select value={f.type} onChange={e => updateField(idx, fi, 'type', e.target.value)}
                    className="rounded-md border bg-[hsl(var(--card))] px-1 text-xs w-20">
                    <option value="text">Text</option><option value="textarea">Textarea</option><option value="number">Số</option>
                    <option value="date">Ngày</option><option value="select">Chọn</option><option value="file">File</option><option value="comment">Comment</option>
                  </select>
                  <label className="flex items-center gap-1 text-xs"><input type="checkbox" checked={f.required} onChange={e => updateField(idx, fi, 'required', e.target.checked)} />Bắt buộc</label>
                  <Button variant="ghost" size="sm" onClick={() => removeField(idx, fi)}><X className="h-3 w-3 text-destructive" /></Button>
                </div>
              ))}
            </div>
          </div>))}
          <Button onClick={addStep} variant="ghost" size="sm"><Plus className="h-4 w-4 mr-1" />Thêm bước</Button>
        </div>
      </DrawerBody>
      <DrawerFooter>
        <Button variant="ghost" onClick={() => setDrawerOpen(false)}>Đóng</Button>
        <Button onClick={() => toast.success('Template đã lưu (demo)')}>Lưu template</Button>
      </DrawerFooter>
    </DrawerContent></Drawer></div>);
}
