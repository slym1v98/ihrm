'use client';

import * as React from 'react';
import { cn } from '@/core/utils/cn';
import { X } from 'lucide-react';

interface DrawerContextValue {
  open: boolean;
  onOpenChange: (open: boolean) => void;
}
const DrawerContext = React.createContext<DrawerContextValue>({
  open: false,
  onOpenChange: () => {},
});

export function Drawer({
  children,
  open: controlledOpen,
  onOpenChange,
}: {
  children: React.ReactNode;
  open?: boolean;
  onOpenChange?: (open: boolean) => void;
}) {
  const [internalOpen, setInternalOpen] = React.useState(false);
  const isOpen = controlledOpen ?? internalOpen;
  const setOpen = onOpenChange ?? setInternalOpen;
  return (
    <DrawerContext.Provider value={{ open: isOpen, onOpenChange: setOpen }}>
      {children}
    </DrawerContext.Provider>
  );
}

/** small = 1/3 screen width, large = full screen */
type DrawerSize = 'sm' | 'lg';

export function DrawerContent({
  children,
  size = 'sm',
  className,
}: {
  children: React.ReactNode;
  size?: DrawerSize;
  className?: string;
}) {
  const { open, onOpenChange } = React.useContext(DrawerContext);
  if (!open) return null;

  const widthClass = size === 'lg' ? 'w-full max-w-[100vw]' : 'w-full max-w-md';

  return (
    <div style={{margin:0}} className="fixed inset-0 z-50 flex justify-end">
      <div className="absolute inset-0 bg-black/50" onClick={() => onOpenChange(false)} />
      <div
        className={cn(
          'relative z-10 flex flex-col h-full',
          'space-y-0',
          widthClass,
          'bg-[hsl(var(--card))] border-l shadow-lg',
          className,
        )}
      >
        {children}
      </div>
    </div>
  );
}

export function DrawerHeader({
  children,
  className,
  onClose,
}: {
  children: React.ReactNode;
  className?: string;
  onClose?: () => void;
}) {
  const { onOpenChange } = React.useContext(DrawerContext);
  return (
    <div
      className={cn(
        'flex items-start justify-between gap-2 border-b px-4 py-3 shrink-0',
        className,
      )}
    >
      <div className="space-y-1 min-w-0">{children}</div>
      <button
        type="button"
        onClick={() => (onClose ? onClose() : onOpenChange(false))}
        className="rounded-md p-1 hover:bg-muted transition-colors shrink-0"
      >
        <X className="h-4 w-4 text-muted-foreground" />
      </button>
    </div>
  );
}

export function DrawerTitle({
  children,
  className,
}: {
  children: React.ReactNode;
  className?: string;
}) {
  return (
    <h2 className={cn('text-base font-semibold leading-6', className)}>
      {children}
    </h2>
  );
}

export function DrawerDescription({
  children,
  className,
}: {
  children: React.ReactNode;
  className?: string;
}) {
  return (
    <p className={cn('text-xs text-muted-foreground', className)}>
      {children}
    </p>
  );
}

export function DrawerBody({
  children,
  className,
}: {
  children: React.ReactNode;
  className?: string;
}) {
  return (
    <div className={cn('flex-1 overflow-y-auto px-4 py-4', className)}>
      {children}
    </div>
  );
}

export function DrawerFooter({
  children,
  className,
}: {
  children: React.ReactNode;
  className?: string;
}) {
  return (
    <div
      className={cn(
        'flex items-center justify-end gap-2 border-t px-4 py-3 shrink-0',
        className,
      )}
    >
      {children}
    </div>
  );
}
