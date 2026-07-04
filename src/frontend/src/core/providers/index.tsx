'use client';

import React, { useState } from 'react';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { AuthProvider } from '@/domains/auth/hooks/useAuth';
import { SidebarProvider } from '@/shared/hooks/useSidebar';
import { Toaster } from '@/shared/components/ui/sonner';

export function Providers({ children }: { children: React.ReactNode }) {
  const [queryClient] = useState(() => new QueryClient({
    defaultOptions: { queries: { staleTime: 30_000, retry: 1 } },
  }));

  return (
    <QueryClientProvider client={queryClient}>
      <SidebarProvider>
        <AuthProvider>
          {children}
          <Toaster />
        </AuthProvider>
      </SidebarProvider>
    </QueryClientProvider>
  );
}
