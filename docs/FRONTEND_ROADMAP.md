Dựa trên nền tảng API đã hoàn thiện từ Phase 1 đến Phase 3 và chiến lược phát triển hệ thống decoupled (Laravel backend API và NextJS frontend), dưới đây là tài liệu **FRONTEND_ROADMAP.md** dành cho phân hệ **Frontend Admin iHRM**.

Tài liệu này được thiết kế dựa trên các tiêu chuẩn nghiêm ngặt của **Clean Architecture**, định hướng nghiệp vụ theo **Domain-Driven Design (DDD)**, kết hợp với hệ sinh thái công nghệ hiện đại bao gồm NextJS (App Router), Shadcn UI, Tailwind CSS và TypeScript.

---

# Tài liệu Kỹ thuật: FRONTEND_ROADMAP.md

**Hệ thống:** iHRM Enterprise Admin Portal

**Công nghệ lõi:** NextJS 15+ (App Router), TypeScript, Tailwind CSS, Shadcn UI, TanStack Query (React Query), Zustand.

**Kiến trúc chủ đạo:** Clean Architecture & Domain-Driven Design (DDD) (Phẳng hóa dữ liệu từ API).

---

## MỤC LỤC

1. [TIÊU CHUẨN KIẾN TRÚC & TỔ CHỨC THƯ MỤC (Strict Clean Architecture)](https://www.google.com/search?q=%231-ti%C3%AAu-chu%E1%BA%A9n-ki%E1%BA%BFn-tr%C3%BAc--t%E1%BB%95-ch%E1%BB%A9c-th%C6%B0-m%E1%BB%A5c-strict-clean-architecture)
2. [QUY CHUẨN TRIỂN KHAI THIẾT KẾ VÀ UI/UX (Strict Design System)](https://www.google.com/search?q=%232-quy-chu%E1%BA%A9n-tri%E1%BB%83n-khai-thi%E1%BA%BFt-k%E1%BA%BF-v%C3%A0-uiux-strict-design-system)
3. [QUY TRÌNH XỬ LÝ DỮ LIỆU & QUẢN LÝ TRẠNG THÁI (Data Flow & State Management)](https://www.google.com/search?q=%233-quy-tr%C3%ACnh-x%E1%BB%AD-l%C3%BD-d%E1%BB%AF-li%E1%BB%87u--qu%E1%BA%A3n-l%C3%BD-tr%E1%BA%A1ng-th%C3%A1i-data-flow--state-management)
4. [LỘ TRÌNH TRIỂN KHAI FRONTEND (Frontend Implementation Roadmap)](https://www.google.com/search?q=%234-l%E1%BB%99-tr%C3%ACnh-tri%E1%BB%83n-khai-frontend-frontend-implementation-roadmap)
5. [CÁC QUY TẮC PHÁT TRIỂN NGHIÊM NGẶT (Strict Development Rules)](https://www.google.com/search?q=%235-c%C3%A1c-quy-t%E1%BA%AFc-ph%C3%A1t-tri%E1%BB%83n-nghi%C3%AAm-ng%E1%BA%B7t-strict-development-rules)

---

## 1. TIÊU CHUẨN KIẾN TRÚC & TỔ CHỨC THƯ MỤC (Strict Clean Architecture)

Để tránh tình trạng mã nguồn phình to và hỗn loạn khi mở rộng lên hàng chục module Enterprise[cite: 2], toàn bộ mã nguồn Frontend được chia cắt độc lập theo Domain (Bounded Context). Tuyệt đối **không** chia thư mục theo kiểu kỹ thuật truyền thống (`components/`, `hooks/`, `apis/` ở root).

### 1.1. Cấu trúc cây thư mục chuẩn (`src/`)

```text
src/
├── app/                      # NextJS App Router (Routing & Pages Only)
│   ├── (auth)/               # Biện pháp cô lập layout cho phân hệ Auth
│   └── (dashboard)/          # Layout chính của Admin portal
│       ├── employees/        # Chỉ chứa page.tsx, layout.tsx, không chứa business components
│       └── organization/
├── core/                     # Hạ tầng chung của ứng dụng (Cross-cutting Concerns)
│   ├── config/               # Biến môi trường, cấu hình hệ thống
│   ├── http/                 # Cấu hình Axios/Fetch client, Interceptors (gắn Token, xử lý 401, 403)
│   ├── styles/               # Global CSS, cấu hình Tailwind themes
│   └── utils/                # Các hàm tiện ích thuần túy (date, format currency,...)
├── shared/                   # Các thành phần tái sử dụng không mang tính nghiệp vụ
│   ├── components/           # UI Components cơ bản (Wrapping Shadcn)
│   │   └── ui/               # Nguyên bản nút bấm, input, dialog từ Shadcn
│   └── hooks/                # Custom hooks dùng chung (useDebounce, useMediaQuery,...)
└── domains/                  # NƠI THỰC THI KIẾN TRÚC DDD & CLEAN ARCHITECTURE (Theo từng Module)
    ├── employee/             # Domain Nhân viên (Aggregate Root)
    │   ├── components/       # UI Components dành riêng cho Domain này (EmployeeTable, SalaryMask,...)
    │   ├── hooks/            # React Query hooks, domain state hooks
    │   ├── models/           # Định nghĩa TypeScript Interfaces, DTOs, Value Objects
    │   ├── services/         # Tương tác API trực tiếp của riêng domain này
    │   └── utils/            # Logic xử lý đặc thù (ví dụ: chuyển đổi trạng thái vòng đời nhân viên)
    ├── attendance/           # Domain Chấm công & Ca kíp
    └── payroll/              # Domain Lương & Công thức lương

```

### 1.2. Quy tắc cô lập Layer nghiêm ngặt (Strict Layering)

* **App Layer (`src/app`):** Chỉ làm nhiệm vụ điều hướng, bắt sự kiện URL (Query Parameters cho Pagination, Filter) và truyền vào Domain Component. Không viết logic tính toán, không gọi API trực tiếp tại đây.
* **Domain Layer (`src/domains/*`):** Chứa toàn bộ "linh hồn" nghiệp vụ của màn hình đó.
* **Quy tắc Import dependency:** Hướng di chuyển của phụ thuộc phải đi từ ngoài vào trong. Thư mục `domains/` có thể import từ `shared/` và `core/`, nhưng tuyệt đối `shared/` và `core/` **không được phép** import ngược từ `domains/`.

---

## 2. QUY CHUẨN TRIỂN KHAI THIẾT KẾ VÀ UI/UX (Strict Design System)

Ứng dụng iHRM yêu cầu hiển thị mật độ thông tin cao (Data-dense UI). Do đó, thiết kế giao diện phải tuân thủ tính nhất quán tối đa thông qua cấu hình hệ thống (Configuration-driven UI).

### 2.1. Cấu hình màu sắc đồng bộ (Tailwind & Shadcn Tokens)

Toàn bộ màu sắc phải sử dụng mã màu ngữ nghĩa (Semantic Colors) từ `tailwind.config.js`. Không Hard-code mã HEX (`#FFFFFF`, `#3B82F6`) vào thuộc tính class.

* **Primary:** `bg-primary`, `text-primary-foreground` (Dành cho Brand, các hành động chính như Lưu, Duyệt đơn).
* **Destructive/Error:** `bg-destructive` (Dành cho xóa, hủy hợp đồng, từ chối đơn từ).
* **Warning:** Dành cho trạng thái Chờ duyệt (Pending), Cảnh báo hợp đồng sắp hết hạn[cite: 3].
* **Success:** Dành cho trạng thái Đã duyệt, Đã thanh toán lương, Đang hoạt động[cite: 3].

### 2.2. Quy chuẩn thiết kế Grid & Bố cục dữ liệu (Data-Dense Layout)

* **Form Layout:** Sử dụng hệ thống Grid cố định. Tất cả các form nhập liệu lớn (như Hồ sơ nhân viên, Cấu hình ca kíp) phải tuân theo cấu trúc tối đa 3 cột trên màn hình Desktop:
```tsx
<div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

```


* **Table UI Standard (Bắt buộc dùng `@tanstack/react-table` kết hợp Shadcn):**
* Tiêu đề bảng (`thead`) phải được cố định khi cuộn (`sticky top-0 bg-background z-10`).
* Tất cả các trường số liệu (Lương, Số phút đi trễ, Ngày công) bắt buộc căn phải (`text-right`).
* Tất cả mã định danh (Mã nhân viên, Mã hợp đồng) bắt buộc font chữ đơn cách (`font-mono`)[cite: 1, 3].
* Hỗ trợ bắt buộc: Trạng thái Hover dòng (`hover:bg-muted/50`) và phân trang cố định ở góc dưới bên phải.



---

## 3. QUY TRÌNH XỬ LÝ DỮ LIỆU & QUẢN LÝ TRẠNG THÁI (Data Flow & State Management)

### 3.1. Phẳng hóa dữ liệu (Data Flattening) tại Frontend

Như thỏa thuận kiến trúc để tối ưu hiệu năng và luồng xử lý đồ thị phức tạp (như cấu hình cây Org Chart của phòng ban hoặc thông tin nhóm), Frontend sẽ nhận dữ liệu dạng phẳng (Flattened Data) từ API.

* Frontend có nhiệm vụ dựng lại cấu trúc cây trực quan (nhờ thuật toán chuyển đổi O(N) từ mảng phẳng sang cây) phục vụ hiển thị sơ đồ Org Chart thông qua các thư viện hỗ trợ render cây (như `reactflow` hoặc cấu trúc tree của Shadcn UI), không dùng đệ quy gọi API lặp đi lặp lại.

### 3.2. Quản lý State phân tầng (State Layering Rule)

1. **Server State (Chiếm 90%):** Quản lý tuyệt đối bằng **TanStack Query (React Query)**. Cấm lưu trữ dữ liệu API trả về vào `useState` hoặc `Zustand`.
* Thời gian `staleTime` mặc định cho dữ liệu cấu hình (Phòng ban, Chức vụ): `5 * 60 * 1000` (5 phút).
* Dữ liệu biến động cao (Bảng chấm công thô, Trạng thái Check-in): `0` (Luôn fetch mới khi focus màn hình)[cite: 1, 3].


2. **Global Client State:** Sử dụng **Zustand** cho các trạng thái toàn cục thực sự nhẹ: Thông tin User đăng nhập hiện tại, Quyền hạn (Permissions Matrix), hoặc trạng thái đóng/mở Sidebar[cite: 1, 3].
3. **Local State:** Dùng `useState` của React chỉ cho UI State (Đóng/mở Modal, trạng thái loading nội bộ của một nút bấm).

---

## 4. LỘ TRÌNH TRIỂN KHAI FRONTEND (Frontend Implementation Roadmap)

Lộ trình triển khai bám sát mô hình cuốn chiếu MVP để đảm bảo tính sẵn sàng của hạ tầng trước khi ráp nối các logic nghiệp vụ phức tạp.

```
[Phase 1: Foundation & Auth] ──► [Phase 2: Core Admin (MVP 1)] ──► [Phase 3: Operation & Engine (MVP 2)] ──► [Phase 4: Advanced & Enterprise]

```

### Phase 1: Kiến trúc nền tảng, Xác thực & Phân quyền (Tuần 1 - Tuần 2)

* **Mục tiêu:** Dựng xong bộ khung ứng dụng, bảo mật và cơ chế quét quyền truy cập.
* **Nhiệm vụ cụ thể:**
1. Khởi tạo dự án NextJS, cài đặt Tailwind, Shadcn UI và cấu hình file `theme`.
2. Thiết lập HTTP Client (Axios Interceptors) xử lý gắn Access Token và xử lý tự động Refresh Token khi hết hạn.
3. Xây dựng màn hình Đăng nhập (Login). Xử lý định tuyến bảo vệ (Protected Routes) thông qua NextJS Middleware dựa trên Token JWT.
4. Triển khai component bảo vệ quyền hạn `<PermissionGuard allowedPermissions="{['Employee.Create']}">` để ẩn/hiện hoặc chặn truy cập các phần tử UI tương ứng[cite: 2].



### Phase 2: Phân hệ Core Admin Portal [MVP 1] (Tuần 3 - Tuần 5)

* **Mục tiêu:** Hoàn thiện giao diện quản trị nhân sự nền tảng giúp vận hành bộ máy.


* **Nhiệm vụ cụ thể:**
1. **Organization Module:** Màn hình quản lý Công ty, Chi nhánh, Phòng ban[cite: 3]. Render Sơ đồ tổ chức (Org Chart) bằng cách chuyển đổi dữ liệu mảng phẳng nhận từ API.


2. **Employee Profile Module:** Form đăng ký nhân viên mới (đa bước - Multi-step form để tránh quá tải UI), Danh sách nhân viên tích hợp bộ lọc nâng cao (Filter theo Phòng ban, Chức vụ, Trạng thái)[cite: 3].
3. **Contract Module:** Màn hình danh sách, chi tiết hợp đồng, hiển thị badge cảnh báo các hợp đồng sắp hết hạn một cách trực quan[cite: 3].



### Phase 3: Phân hệ Vận hành & Công cụ Tính toán [MVP 2] (Tuần 6 - Tuần 9)

* **Mục tiêu:** Xử lý các giao diện nhập liệu phức tạp, xử lý thời gian thực và cấu hình công thức.


* **Nhiệm vụ cụ thể:**
1. **Attendance Workspace:** Giao diện xem dữ liệu Chấm công (Timesheet Grid) dạng bảng lưới lớn theo tháng, tích hợp đánh dấu màu sắc trực quan cho các ngày đi trễ, về sớm, nghỉ không lương[cite: 3].
2. **Shift & Leave Schedule:** Bộ công cụ cấu hình ca làm việc (Shift), kéo thả gán lịch làm việc cho nhân sự, và Form gửi đơn từ xin nghỉ phép trực quan[cite: 3].
3. **Workflow Engine UI:** Giao diện thiết lập Quy trình phê duyệt động (Approval Workflow) theo sơ đồ bước (Step-by-step Builder) giúp HR tự cấu hình luồng duyệt nhiều cấp[cite: 1, 3].
4. **Payroll Dashboard:** Màn hình bảng tính lương tổng hợp, tích hợp bộ khóa dữ liệu bảng lương (Payroll Lock UI) để bảo toàn tính bất biến sau khi chốt lương[cite: 1, 2].



### Phase 4: Phân hệ Doanh nghiệp mở rộng & Đóng gói [MVP 3 & 4] (Tuần 10 - Tuần 12)

* **Mục tiêu:** Hoàn thiện trải nghiệm quản trị tài năng và tối ưu hóa hệ thống báo cáo Enterprise.


* **Nhiệm vụ cụ thể:**
1. **Talent & Recruitment UI:** Kanban Board quản lý vòng đời ứng viên tuyển dụng (Ứng tuyển -> Sàng lọc -> Phỏng vấn -> Offer)[cite: 3].
2. **Asset & Performance Management:** Giao diện cấp phát tài sản (ký biên bản bàn giao điện tử) và form đánh giá KPI/OKR[cite: 3].
3. **Executive Dashboard & Audit Log:** Biểu đồ hóa dữ liệu nhân sự cấp cao (tỷ lệ nghỉ việc, biến động lương qua các tháng) bằng `Recharts`. Màn hình truy vết lịch sử thao tác hệ thống (Audit Log UI) phục vụ bảo mật doanh nghiệp[cite: 1, 2].



---

## 5. CÁC QUY TẮC PHÁT TRIỂN NGHIÊM NGẶT (Strict Development Rules)

Để đảm bảo chất lượng code đầu ra đồng đều giữa toàn bộ các thành viên dự án, các quy tắc sau đây là **bắt buộc** và sẽ được kiểm tra thông qua CI/CD Pipeline (Linting/Type-check):

1. **Tuyệt đối không sử dụng kiểu dữ liệu `any`:** Mọi biến số, tham số API, dữ liệu Form bắt buộc phải được định nghĩa Type/Interface rõ ràng. Nếu dữ liệu chưa xác định, dùng `unknown` kết hợp Type Guard.
2. **Quy tắc về Component:** Một file chỉ chứa tối đa 1 Component duy nhất. Nếu component vượt quá 250 dòng code, bắt buộc phải bóc tách (Extract) các component con ra thư mục `components/` nội bộ của màn hình đó.
3. **Xử lý bất đồng bộ (Async/Await):** Tất cả các thao tác gọi API hoặc Mutation thông qua React Query phải được bọc trong cơ chế thông báo trực quan đến người dùng. Sử dụng `toast.error()` và `toast.success()` từ hệ sinh thái Shadcn UI để hiển thị trạng thái kết quả, tuyệt đối không dùng lệnh `alert()` mặc định của trình duyệt.
4. **Quản lý Form nghiêm ngặt:** Tất cả các Form trên hệ thống bắt buộc phải sử dụng bộ đôi thư viện `react-hook-form` kết hợp kiểm thử biểu thức dữ liệu bằng `zod` (Zod Schema Validation). Không xử lý kiểm tra dữ liệu thủ công bằng câu lệnh `if/else`.