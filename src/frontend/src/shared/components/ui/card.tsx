import * as React from 'react';
import { cn } from '@/core/utils/cn';

export function Card({ className, ...props }: React.HTMLAttributes<HTMLDivElement>) {
  return <div className={cn('rounded-lg border bg-[hsl(var(--card))] shadow-sm', className)} {...props} />;
}
