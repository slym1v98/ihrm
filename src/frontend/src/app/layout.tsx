import type { Metadata } from 'next';
import './globals.css';
import { AuthProvider } from '@/domains/auth/hooks/useAuth';

export const metadata: Metadata = {
  title: 'iHRM Admin',
  description: 'iHRM Enterprise Admin Portal',
};

export default function RootLayout({ children }: { children: React.ReactNode }) {
  return (
    <html lang="vi">
      <body>
        <AuthProvider>{children}</AuthProvider>
      </body>
    </html>
  );
}
