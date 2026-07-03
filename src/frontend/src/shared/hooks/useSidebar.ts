'use client';

import { useEffect, useState } from 'react';

const STORAGE_KEY = 'ihrm-sidebar';

export function useSidebar() {
  const [collapsed, setCollapsed] = useState(false);

  useEffect(() => {
    const stored = localStorage.getItem(STORAGE_KEY);
    if (stored === 'collapsed') setCollapsed(true);
  }, []);

  function toggle() {
    const next = !collapsed;
    setCollapsed(next);
    localStorage.setItem(STORAGE_KEY, next ? 'collapsed' : 'expanded');
  }

  return { collapsed, toggle };
}
