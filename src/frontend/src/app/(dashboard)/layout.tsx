import { AppSidebar } from '@/shared/components/AppSidebar';
import { Header } from '@/shared/components/Header';

export default function DashboardLayout({ children }: { children: React.ReactNode }) {
  return (
    <div className="flex min-h-screen flex-col bg-muted/40">
      <Header />
      <div className="flex flex-1">
        <AppSidebar />
        <main className="flex-1 p-6">{children}</main>
      </div>
    </div>
  );
}
