import { NextResponse, type NextRequest } from 'next/server';
import { config as appConfig } from '@/core/config';

export function middleware(request: NextRequest) {
  const token = request.cookies.get(appConfig.accessTokenCookie)?.value;
  const isLogin = request.nextUrl.pathname === '/login';

  if (!token && !isLogin) {
    return NextResponse.redirect(new URL('/login', request.url));
  }

  if (token && isLogin) {
    return NextResponse.redirect(new URL('/dashboard', request.url));
  }

  return NextResponse.next();
}

export const config = {
  matcher: ['/dashboard/:path*', '/login'],
};
