'use client';
import * as React from 'react';
import { cn } from '@/core/utils/cn';

interface DialogContextValue { open: boolean; onOpenChange: (open: boolean) => void; }
const DialogContext = React.createContext<DialogContextValue>({ open: false, onOpenChange: () => {} });

export function Dialog({ children, open: controlledOpen, onOpenChange }: {
  children: React.ReactNode; open?: boolean; onOpenChange?: (open: boolean) => void;
}) {
  const [internalOpen, setInternalOpen] = React.useState(false);
  const isOpen = controlledOpen ?? internalOpen;
  const setOpen = onOpenChange ?? setInternalOpen;
  return <DialogContext.Provider value={{ open: isOpen, onOpenChange: setOpen }}>{children}</DialogContext.Provider>;
}

export function DialogTrigger({ children }: { children: React.ReactElement }) {
  const { onOpenChange } = React.useContext(DialogContext);
  return React.cloneElement(children, { onClick: (e: React.MouseEvent) => { children.props.onClick?.(e); onOpenChange(true); } });
}

export function DialogContent({ children, className }: { children: React.ReactNode; className?: string }) {
  const { open, onOpenChange } = React.useContext(DialogContext);
  if (!open) return null;
  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center">
      <div className="fixed inset-0 bg-black/50" onClick={() => onOpenChange(false)} />
      <div className={cn('relative z-50 w-full max-w-lg rounded-lg border bg-white p-6 shadow-lg', className)}>
        {children}
      </div>
    </div>
  );
}

export function DialogHeader({ children, className }: { children: React.ReactNode; className?: string }) {
  return <div className={cn('mb-4 space-y-1.5', className)}>{children}</div>;
}

export { DialogHeader as DialogHeaderComponent };

export function DialogTitle({ children, className }: { children: React.ReactNode; className?: string }) {
  return <h2 className={cn('text-lg font-semibold', className)}>{children}</h2>;
}

export function DialogDescription({ children, className }: { children: React.ReactNode; className?: string }) {
  return <p className={cn('text-sm text-muted-foreground', className)}>{children}</p>;
}

export function DialogFooter({ children, className }: { children: React.ReactNode; className?: string }) {
  return <div className={cn('mt-4 flex justify-end gap-2', className)}>{children}</div>;
}

export function DialogClose({ children }: { children: React.ReactElement }) {
  const { onOpenChange } = React.useContext(DialogContext);
  return React.cloneElement(children, { onClick: (e: React.MouseEvent) => { children.props.onClick?.(e); onOpenChange(false); } });
}
