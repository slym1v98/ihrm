import { cn } from '@/core/utils/cn';

const variants = {
  default: 'bg-primary text-primary-foreground',
  secondary: 'bg-muted text-muted-foreground',
  destructive: 'bg-destructive text-white',
  outline: 'border text-foreground',
} as const;

export function Badge({ className, variant = 'default', ...props }: {
  className?: string; variant?: keyof typeof variants; children?: React.ReactNode;
}) {
  return <span className={cn('inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium whitespace-nowrap', variants[variant], className)} {...props} />;
}
