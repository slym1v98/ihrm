import { LoginForm } from '@/domains/auth/components/LoginForm';

export default function LoginPage() {
  return (
    <main className="flex min-h-screen">
      {/* Left: Login Form */}
      <div className="flex w-full items-center justify-center bg-[hsl(var(--background))] px-6 lg:w-1/2">
        <LoginForm />
      </div>

      {/* Right: System Introduction */}
      <div className="hidden w-1/2 flex-col items-center justify-center bg-gradient-to-br from-primary/10 via-primary/5 to-background p-12 lg:flex">
        <div className="max-w-md space-y-8">
          <div className="space-y-3">
            <h1 className="text-4xl font-bold tracking-tight">iHRM</h1>
            <p className="text-lg text-muted-foreground">
              Hệ thống Quản trị Nhân sự Doanh nghiệp
            </p>
          </div>

          <div className="space-y-6">
            <div className="space-y-2">
              <h3 className="font-semibold">Quản lý nhân sự toàn diện</h3>
              <p className="text-sm text-muted-foreground leading-relaxed">
                Quản lý thông tin nhân viên, hợp đồng, chấm công, nghỉ phép, ca làm việc,
                tài sản, đào tạo và nhiều hơn nữa — tất cả trong một nền tảng duy nhất.
              </p>
            </div>

            <div className="grid grid-cols-2 gap-4">
              {[
                { label: 'Nhân sự', desc: 'Hồ sơ & hợp đồng' },
                { label: 'Chấm công', desc: 'Theo dõi thời gian' },
                { label: 'Nghỉ phép', desc: 'Đơn từ & phê duyệt' },
                { label: 'Ca làm việc', desc: 'Lịch & phân ca' },
                { label: 'Tài sản', desc: 'Cấp phát & quản lý' },
                { label: 'Đào tạo', desc: 'Khoá học & kết quả' },
              ].map((item) => (
                <div key={item.label} className="rounded-lg border bg-[hsl(var(--card))]/50 p-3">
                  <div className="text-sm font-medium">{item.label}</div>
                  <div className="text-xs text-muted-foreground">{item.desc}</div>
                </div>
              ))}
            </div>
          </div>

          <div className="border-t pt-6">
            <p className="text-xs text-muted-foreground">
              Phiên bản 2026 · iHRM Enterprise
            </p>
          </div>
        </div>
      </div>
    </main>
  );
}
