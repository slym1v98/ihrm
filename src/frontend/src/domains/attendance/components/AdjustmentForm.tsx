'use client';

import { useState, useCallback } from 'react';
import { toast } from 'sonner';
import { useCreateAdjustment } from '@/domains/attendance/hooks/useAdjustments';
import { Drawer, DrawerContent, DrawerHeader, DrawerTitle, DrawerDescription, DrawerBody, DrawerFooter } from '@/shared/components/ui/drawer';
import { Button } from '@/shared/components/ui/button';
import { Label } from '@/shared/components/ui/label';
import { extractErrorMessage } from '@/core/errors/messages';

interface Props {
  timesheetId: string | null;
  open: boolean;
  onOpenChange: (o: boolean) => void;
}

export function AdjustmentForm({ timesheetId, open, onOpenChange }: Props) {
  const [reason, setReason] = useState('');
  const create = useCreateAdjustment();

  const handleSubmit = useCallback(async () => {
    if (!timesheetId || !reason.trim()) return;
    try {
      await create.mutateAsync({ attendance_timesheet_id: timesheetId, reason: reason.trim() });
      toast.success('Gửi yêu cầu điều chỉnh thành công');
      setReason('');
      onOpenChange(false);
    } catch (raw) {
      toast.error(extractErrorMessage(raw));
    }
  }, [timesheetId, reason, create, onOpenChange]);

  return (
    <Drawer open={open} onOpenChange={(o) => { if (!o) { setReason(''); } onOpenChange(o); }}>
      <DrawerContent size="sm">
        <DrawerHeader>
          <DrawerTitle>Yêu cầu điều chỉnh công</DrawerTitle>
          <DrawerDescription>Nhập lý do điều chỉnh</DrawerDescription>
        </DrawerHeader>
        <DrawerBody>
          <div className="space-y-2">
            <Label htmlFor="adj-reason">Lý do <span className="text-destructive">*</span></Label>
            <textarea id="adj-reason" className="h-24 w-full rounded-md border bg-[hsl(var(--card))] px-2 py-1 text-[13px] outline-none focus:ring-2 focus:ring-primary resize-none"
              placeholder="Mô tả lý do điều chỉnh..."
              value={reason} onChange={(e) => setReason(e.target.value)} />
          </div>
        </DrawerBody>
        <DrawerFooter>
          <Button variant="ghost" onClick={() => onOpenChange(false)}>Hủy</Button>
          <Button onClick={handleSubmit} disabled={!reason.trim() || create.isPending}>Gửi yêu cầu</Button>
        </DrawerFooter>
      </DrawerContent>
    </Drawer>
  );
}
