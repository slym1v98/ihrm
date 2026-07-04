import * as React from 'react';
import { cn } from '@/core/utils/cn';

const Input = React.forwardRef<HTMLInputElement, React.InputHTMLAttributes<HTMLInputElement>>(
  ({ className, type, autoComplete = "off", ...props }, ref) => (
    <input
      type={type}
      autoComplete={autoComplete}
      className={cn(
        'h-8 w-full rounded-md border bg-[hsl(var(--card))] text-foreground px-2 py-1 text-[13px] outline-none focus:ring-2 focus:ring-primary disabled:cursor-not-allowed disabled:opacity-50',
        className,
      )}
      ref={ref}
      {...props}
    />
  ),
);
Input.displayName = 'Input';
export { Input };
