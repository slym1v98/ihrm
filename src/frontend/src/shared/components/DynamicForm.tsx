'use client';
import { useForm } from 'react-hook-form';
import { Input } from '@/shared/components/ui/input';
import { Label } from '@/shared/components/ui/label';
import { Button } from '@/shared/components/ui/button';
import { X } from 'lucide-react';

export interface FormFieldSchema {
  key: string; type: 'text' | 'textarea' | 'number' | 'date' | 'select' | 'file' | 'comment';
  label: string; required?: boolean; options?: string[]; max_length?: number; max_size_mb?: number;
}

interface Props {
  schema: FormFieldSchema[];
  values?: Record<string, unknown>;
  onChange?: (data: Record<string, unknown>) => void;
  readOnly?: boolean;
}

export function DynamicForm({ schema, values, onChange, readOnly }: Props) {
  const form = useForm({ defaultValues: values ?? {}, mode: 'onChange' });

  return (<div className="space-y-4">{(schema ?? []).map((field) => (
    <div key={field.key} className="space-y-1">
      <Label htmlFor={field.key}>{field.label} {field.required && <span className="text-destructive">*</span>}</Label>
      {field.type === 'textarea' || field.type === 'comment' ? (
        <textarea id={field.key} readOnly={readOnly} rows={3}
          className="w-full rounded-md border bg-[hsl(var(--card))] px-2 py-1 text-[13px] outline-none resize-none"
          {...form.register(field.key, { required: field.required })}
          onBlur={() => onChange?.(form.getValues())}
        />
      ) : field.type === 'select' ? (
        <select id={field.key} disabled={readOnly}
          className="w-full rounded-md border bg-[hsl(var(--card))] px-2 py-1 text-[13px]"
          {...form.register(field.key, { required: field.required })}
          onChange={() => onChange?.(form.getValues())}>
          <option value="">Chọn...</option>
          {(field.options ?? []).map(o => <option key={o} value={o}>{o}</option>)}
        </select>
      ) : field.type === 'number' ? (
        <Input id={field.key} type="number" autoComplete="off" readOnly={readOnly}
          {...form.register(field.key, { required: field.required, valueAsNumber: true })}
          onBlur={() => onChange?.(form.getValues())} />
      ) : field.type === 'date' ? (
        <Input id={field.key} type="date" autoComplete="off" readOnly={readOnly}
          {...form.register(field.key, { required: field.required })}
          onBlur={() => onChange?.(form.getValues())} />
      ) : (
        <Input id={field.key} autoComplete="off" readOnly={readOnly}
          {...form.register(field.key, { required: field.required })}
          onBlur={() => onChange?.(form.getValues())} />
      )}
    </div>
  ))}</div>);
}
