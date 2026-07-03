'use client';
import * as React from 'react';
import { cn } from '@/core/utils/cn';

export function Select({ value, onChange, children, placeholder }: {
  value?: string; onChange?: (v: string) => void; children: React.ReactNode; placeholder?: string;
}) {
  return (
    <select
      value={value}
      onChange={(e) => onChange?.(e.target.value)}
      className={cn(
        'h-10 w-full rounded-md border bg-white px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-primary',
        !value && 'text-muted-foreground',
      )}
    >
      {placeholder ? <option value="">{placeholder}</option> : null}
      {children}
    </select>
  );
}

export function SelectItem({ value, children }: { value: string; children: React.ReactNode }) {
  return <option value={value}>{children}</option>;
}
