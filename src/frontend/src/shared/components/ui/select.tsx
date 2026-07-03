'use client';
import * as React from 'react';
import { cn } from '@/core/utils/cn';

const Select = React.forwardRef<HTMLSelectElement, React.SelectHTMLAttributes<HTMLSelectElement> & {
  children: React.ReactNode; placeholder?: string;
}>(({ className, children, placeholder, ...props }, ref) => (
  <select
    ref={ref}
    className={cn(
      'h-8 w-full rounded-md border bg-[hsl(var(--card))] text-foreground px-2 py-1 text-[13px] outline-none focus:ring-2 focus:ring-primary',
      !props.value && 'text-muted-foreground',
      className,
    )}
    {...props}
  >
    {placeholder ? <option value="">{placeholder}</option> : null}
    {children}
  </select>
));
Select.displayName = 'Select';
export { Select };

export function SelectItem({ value, children }: { value: string; children: React.ReactNode }) {
  return <option value={value}>{children}</option>;
}
