import * as React from 'react';
import { cn } from '@/core/utils/cn';

type ButtonProps = React.ButtonHTMLAttributes<HTMLButtonElement> & {
  variant?: 'primary' | 'ghost' | 'destructive';
  size?: 'default' | 'sm';
};

const Button = React.forwardRef<HTMLButtonElement, ButtonProps>(
  ({ className, variant = 'primary', size = 'sm', ...props }, ref) => {
    const variants = {
      primary: 'bg-primary text-primary-foreground hover:bg-primary/90',
      ghost: 'hover:bg-muted',
      destructive: 'bg-destructive text-white hover:bg-destructive/90',
    };
    const sizes = {
      default: 'h-9 px-3 py-1.5',
      sm: 'h-8 px-2 py-1',
    };

    return (
      <button
        className={cn(
          'inline-flex items-center justify-center rounded-md text-[13px] font-medium transition-colors disabled:pointer-events-none disabled:opacity-50',
          variants[variant],
          sizes[size],
          className,
        )}
        ref={ref}
        {...props}
      />
    );
  },
);
Button.displayName = 'Button';
export { Button };
