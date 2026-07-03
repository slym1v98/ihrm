import type { Metadata } from 'next';
import './globals.css';
import { Providers } from '@/core/providers';

export const metadata: Metadata = {
  title: 'iHRM Admin',
  description: 'iHRM Enterprise Admin Portal',
};

export default function RootLayout({ children }: { children: React.ReactNode }) {
  return (
    <html lang="vi">
      <body>
        <Providers>{children}</Providers>
      </body>
    </html>
  );
}
