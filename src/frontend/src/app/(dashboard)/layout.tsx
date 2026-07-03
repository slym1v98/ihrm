import { AppSidebar } from '@/shared/components/AppSidebar';

export default function DashboardLayout({ children }: { children: React.ReactNode }) {
  return (
    <div className="flex min-h-screen bg-muted/40">
      <AppSidebar />
      <main className="flex-1 p-6">{children}</main>
    </div>
  );
}
