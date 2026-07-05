'use client';

import { useState } from 'react';
import { ShiftListPage } from '@/domains/shift/components/ShiftListPage';
import { ShiftAssignmentSection } from '@/domains/shift/components/ShiftAssignmentSection';

const tabs = [
  { key: 'templates', label: 'Ca làm việc' },
  { key: 'assignments', label: 'Phân ca' },
] as const;

export default function ShiftPage() {
  const [tab, setTab] = useState<'templates' | 'assignments'>('templates');

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
      {tab === 'templates' ? <ShiftListPage /> : <ShiftAssignmentSection />}
    </div>
  );
}
