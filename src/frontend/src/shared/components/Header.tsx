'use client';

import { useState, useRef, useEffect } from 'react';
import { Bell, User, Settings, LogOut, Moon, Sun, PanelLeft, PanelLeftClose } from 'lucide-react';
import { useRouter } from 'next/navigation';
import { useAuth } from '@/domains/auth/hooks/useAuth';
import { useTheme } from '@/shared/hooks/useTheme';
import { useSidebar } from '@/shared/hooks/useSidebar';
import { Breadcrumb } from '@/shared/components/Breadcrumb';
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
  const { collapsed, toggle } = useSidebar();

  return (
    <header className="sticky top-0 z-30 flex h-16 items-center justify-between border-b bg-[hsl(var(--card))] px-4">
      <div className="flex items-center gap-3">
        <button type="button" onClick={toggle} title={collapsed ? 'Mở rộng' : 'Thu gọn'}
          className="rounded-md p-1.5 hover:bg-muted transition-colors">
          {collapsed ? <PanelLeft className="h-5 w-5 text-muted-foreground" /> : <PanelLeftClose className="h-5 w-5 text-muted-foreground" />}
        </button>
        <Breadcrumb />
      </div>

      <div className="flex items-center gap-2">
        <button type="button" onClick={toggleTheme} title={theme === 'dark' ? 'Chế độ sáng' : 'Chế độ tối'}
          className="rounded-md p-1.5 hover:bg-muted transition-colors">
          {theme === 'dark' ? <Sun className="h-5 w-5 text-muted-foreground" /> : <Moon className="h-5 w-5 text-muted-foreground" />}
        </button>

        <Dropdown trigger={<Bell className="h-5 w-5 text-muted-foreground" />}>
          <div className="px-3 py-2 text-sm text-muted-foreground">Chưa có thông báo</div>
        </Dropdown>

        <Dropdown trigger={<User className="h-5 w-5 text-muted-foreground" />}>
          <div className="border-b px-3 py-2 text-sm font-medium">Welcome, {user?.name ?? 'Admin'}</div>
          <DropdownItem icon={User} label="Hồ sơ nhân sự" onClick={() => router.push('/employees')} />
          <DropdownItem icon={Settings} label="Cài đặt" onClick={() => toast.info('Chưa có trang cài đặt')} />
        </Dropdown>

        <button type="button" onClick={async () => { await logout(); router.replace('/login'); }}
          title="Đăng xuất" className="rounded-md p-1.5 hover:bg-muted transition-colors">
          <LogOut className="h-5 w-5 text-muted-foreground" />
        </button>
      </div>
    </header>
  );
}
