import type { Metadata } from 'next';
import './globals.css';
import { Providers } from '@/core/providers';

export const metadata: Metadata = {
  title: 'iHRM Admin',
  description: 'iHRM Enterprise Admin Portal',
};

const themeScript = `
(function() {
  try {
    var theme = localStorage.getItem('ihrm-theme');
    if (theme === 'dark') {
      document.documentElement.classList.add('dark');
    }
  } catch(e) {}
})();
`;

export default function RootLayout({ children }: { children: React.ReactNode }) {
  return (
    <html lang="vi" suppressHydrationWarning>
      <head>
        <script dangerouslySetInnerHTML={{ __html: themeScript }} />
      </head>
      <body className="font-sans" style={{ fontSize: 13 }}>
        <Providers>{children}</Providers>
      </body>
    </html>
  );
}
