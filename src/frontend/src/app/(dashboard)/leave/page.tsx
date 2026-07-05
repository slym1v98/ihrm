'use client';

import { useState } from 'react';
import { LeaveListPage } from '@/domains/leave/components/LeaveListPage';
import { LeaveBalanceSection } from '@/domains/leave/components/LeaveBalanceSection';

const tabs = [
  { key: 'requests', label: 'Đơn nghỉ' },
  { key: 'balances', label: 'Tồn quỹ' },
] as const;

export default function LeavePage() {
  const [tab, setTab] = useState<'requests' | 'balances'>('requests');

  return (
    <div className="space-y-4">
      <div className="flex gap-1 border-b">
        {tabs.map(t => (
          <button
            key={t.key}
            onClick={() => setTab(t.key)}
            className={`px-4 py-2 text-sm font-medium border-b-2 transition-colors ${
              tab === t.key
                ? 'border-primary text-primary'
                : 'border-transparent text-muted-foreground hover:text-foreground'
            }`}
          >
            {t.label}
          </button>
        ))}
      </div>
      {tab === 'requests' ? <LeaveListPage /> : <LeaveBalanceSection />}
    </div>
  );
}
