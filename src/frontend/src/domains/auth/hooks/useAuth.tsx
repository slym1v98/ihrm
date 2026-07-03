'use client';

import React, { createContext, useContext, useEffect, useMemo, useState } from 'react';
import { config } from '@/core/config';
import { setAccessToken } from '@/core/http/client';
import type { User } from '@/domains/auth/models/auth';
import { authService } from '@/domains/auth/services/authService';

interface AuthContextValue {
  user: User | null;
  permissions: string[];
  isLoading: boolean;
  login: (email: string, password: string) => Promise<void>;
  logout: () => Promise<void>;
  hasAnyPermission: (allowed: string[]) => boolean;
}

const AuthContext = createContext<AuthContextValue | null>(null);

function readCookie(name: string) {
  if (typeof document === 'undefined') return null;
  return document.cookie.split('; ').find((row) => row.startsWith(`${name}=`))?.split('=')[1] ?? null;
}

function writeTokenCookie(token: string) {
  const secure = window.location.protocol === 'https:' ? '; Secure' : '';
  document.cookie = `${config.accessTokenCookie}=${encodeURIComponent(token)}; path=/; SameSite=Lax${secure}`;
}

function clearTokenCookie() {
  document.cookie = `${config.accessTokenCookie}=; path=/; max-age=0; SameSite=Lax`;
}

async function loadPermissions(user: User) {
  const roleDetails = await Promise.allSettled(user.roles.map((role) => authService.role(role.id)));
  return Array.from(
    new Set(
      roleDetails.flatMap((result) => (result.status === 'fulfilled' ? result.value.permissions : [])),
    ),
  );
}

export function AuthProvider({ children }: { children: React.ReactNode }) {
  const [user, setUser] = useState<User | null>(null);
  const [permissions, setPermissions] = useState<string[]>([]);
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    const token = readCookie(config.accessTokenCookie);
    if (!token) {
      setIsLoading(false);
      return;
    }

    setAccessToken(decodeURIComponent(token));
    authService.me()
      .then(async (currentUser) => {
        setUser(currentUser);
        setPermissions(await loadPermissions(currentUser));
      })
      .catch(() => {
        clearTokenCookie();
        setAccessToken(null);
        setUser(null);
        setPermissions([]);
      })
      .finally(() => setIsLoading(false));
  }, []);

  const value = useMemo<AuthContextValue>(() => ({
    user,
    permissions,
    isLoading,
    async login(email, password) {
      const result = await authService.login(email, password);
      writeTokenCookie(result.access_token);
      setAccessToken(result.access_token);
      setUser(result.user);
      setPermissions(await loadPermissions(result.user));
    },
    async logout() {
      try { await authService.logout(); } finally {
        clearTokenCookie();
        setAccessToken(null);
        setUser(null);
        setPermissions([]);
      }
    },
    hasAnyPermission(allowed) {
      return allowed.some((permission) => permissions.includes(permission));
    },
  }), [permissions, user, isLoading]);

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

export function useAuth() {
  const context = useContext(AuthContext);
  if (!context) throw new Error('useAuth must be used inside AuthProvider');
  return context;
}
