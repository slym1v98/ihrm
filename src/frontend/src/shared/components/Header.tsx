'use client';

import { useState, useRef, useEffect } from 'react';
import { Bell, User, Settings, LogOut, ChevronDown, Moon, Sun } from 'lucide-react';
import { useRouter } from 'next/navigation';
import { useAuth } from '@/domains/auth/hooks/useAuth';
import { useTheme } from '@/shared/hooks/useTheme';
import { toast } from 'sonner';

function Dropdown({ trigger, children }: { trigger: React.ReactNode; children: React.ReactNode }) {
  const [open, setOpen] = useState(false);
  const ref = useRef<HTMLDivElement>(null);

  useEffect(() => {
    function handleClick(e: MouseEvent) {
      if (ref.current && !ref.current.contains(e.target as Node)) setOpen(false);
    }
    if (open) document.addEventListener('mousedown', handleClick);
    return () => document.removeEventListener('mousedown', handleClick);
  }, [open]);

  return (
    <div ref={ref} className="relative">
      <button type="button" onClick={() => setOpen(!open)} className="rounded-md p-1.5 hover:bg-muted transition-colors">
        {trigger}
      </button>
      {open && (
        <div className="absolute right-0 top-full z-50 mt-1 min-w-[200px] rounded-lg border bg-[hsl(var(--card))] p-1 shadow-lg">
          {children}
        </div>
      )}
    </div>
  );
}

function DropdownItem({ icon: Icon, label, onClick }: {
  icon: React.ComponentType<{ className?: string }>; label: string; onClick: () => void;
}) {
  return (
    <button type="button" onClick={onClick}
      className="flex w-full items-center gap-2 rounded-md px-3 py-2 text-sm text-foreground hover:bg-muted transition-colors">
      <Icon className="h-4 w-4 shrink-0 text-muted-foreground" />
      <span>{label}</span>
    </button>
  );
}

export function Header() {
  const router = useRouter();
  const { user, logout } = useAuth();
  const { theme, toggleTheme } = useTheme();

  return (
    <header className="flex h-12 items-center justify-between border-b bg-[hsl(var(--card))] px-4">
      <div className="flex items-center gap-2">
        <p className="text-base font-semibold">iHRM Admin</p>
      </div>

      <div className="flex items-center gap-2">
        <button type="button" onClick={toggleTheme} title={theme === 'dark' ? 'Chế độ sáng' : 'Chế độ tối'}
          className="rounded-md p-1.5 hover:bg-muted transition-colors">
          {theme === 'dark' ? <Sun className="h-5 w-5 text-muted-foreground" /> : <Moon className="h-5 w-5 text-muted-foreground" />}
        </button>

        <Dropdown trigger={<Bell className="h-5 w-5 text-muted-foreground" />}>
          <div className="px-3 py-2 text-sm text-muted-foreground">Chưa có thông báo</div>
        </Dropdown>

        <Dropdown
          trigger={
            <div className="flex items-center gap-2 rounded-md px-2 py-1 hover:bg-muted transition-colors">
              <div className="flex h-7 w-7 items-center justify-center rounded-full bg-primary text-xs font-semibold text-primary-foreground">
                {user?.name?.charAt(0)?.toUpperCase() ?? 'A'}
              </div>
              <span className="hidden md:inline text-sm font-medium">{user?.name ?? 'Admin'}</span>
              <ChevronDown className="h-3 w-3 text-muted-foreground" />
            </div>
          }
        >
          <DropdownItem icon={User} label="Hồ sơ nhân sự" onClick={() => router.push('/employees')} />
          <DropdownItem icon={Settings} label="Cài đặt" onClick={() => toast.info('Chưa có trang cài đặt')} />
          <div className="my-1 border-t" />
          <DropdownItem icon={LogOut} label="Đăng xuất" onClick={async () => { await logout(); router.replace('/login'); }} />
        </Dropdown>
      </div>
    </header>
  );
}
