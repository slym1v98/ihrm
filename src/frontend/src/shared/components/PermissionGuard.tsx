'use client';

import { type ReactNode } from 'react';
import { useAuth } from '@/domains/auth/hooks/useAuth';

interface PermissionGuardProps {
  allowedPermissions: string[];
  children: ReactNode;
  fallback?: ReactNode;
}

export function PermissionGuard({ allowedPermissions, children, fallback = null }: PermissionGuardProps) {
  const { hasAnyPermission, isLoading } = useAuth();

  if (isLoading) return null;
  return hasAnyPermission(allowedPermissions) ? <>{children}</> : <>{fallback}</>;
}
