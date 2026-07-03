# iHRM — IntrustDSS Human Resource Management

Hệ thống quản trị nhân sự doanh nghiệp (single-tenant), hỗ trợ toàn bộ vòng đời nhân viên từ tuyển dụng đến nghỉ việc.

## Tính năng cốt lõi

| Phase                        | Modules                                                                          |
|------------------------------|----------------------------------------------------------------------------------|
| **1. Core Platform**         | Identity & Access, Organization, Employee Master, Configuration, Audit, Document |
| **2. Workforce Operations**  | Attendance, Shift, Leave, Payroll, Reporting, Workflow, Notification             |
| **3. Talent Lifecycle**      | Recruitment, Onboarding, Offboarding, Performance, Training, Asset               |
| **4. Enterprise Extensions** | SSO, Mobile, Advanced Analytics, Compliance                                      |

## Tech Stack

| Layer           | Technology                                     |
|-----------------|------------------------------------------------|
| Backend         | PHP 8.4, Laravel 12, Sanctum, PHPStan, PHPUnit |
| Frontend        | Next.js 14, React 18, Tailwind CSS, shadcn/ui  |
| Database        | PostgreSQL 16                                  |
| Cache           | Redis 7                                        |
| Storage         | MinIO (S3-compatible object storage)           |
| Proxy           | Nginx 1.27 (dev)                               |
| Dev environment | Docker Compose                                 |

## Yêu cầu

- Docker & Docker Compose
- Thêm host entries (một lần):

```bash
echo "127.0.0.1 ihrm.test api.ihrm.test" | sudo tee -a /etc/hosts
```

## Quick Start

```bash
# Khởi động stack
make up

# Tạo APP_KEY và storage link
make setup

# Chạy migration + seed
make fresh

# Truy cập:
#   Frontend: http://ihrm.test
#   API:      http://api.ihrm.test/api/v1
```

## Makefile Reference

| Target                 | Usage                           |
|------------------------|---------------------------------|
| `make up`              | `docker compose up -d`          |
| `make down`            | `docker compose down`           |
| `make build`           | Rebuild images                  |
| `make test`            | Run backend tests (`--compact`) |
| `make shell`           | `sh` vào container app          |
| `make artisan cmd=...` | Chạy artisan command            |
| `make migrate`         | `php artisan migrate`           |
| `make fresh`           | `migrate:fresh --seed`          |
| `make logs`            | Tail logs                       |
| `make lint`            | PHPStan analysis                |

## Kiến trúc

**DDD Modular** — mỗi module độc lập, 3 lớp:

```
Module/<Name>/
  Domain/          — Pure PHP, không phụ thuộc Laravel
  Application/     — Commands/Queries + Handlers
  Infrastructure/  — Eloquent, Controllers, Routes, Seeders
```

Luồng phụ thuộc: `Domain ← Application ← Infrastructure`.

### Bounded Context Map

```
IAM ──→ Employee ──→ Organization ──→ Configuration ──→ Audit
```

Mỗi context chỉ giao tiếp qua domain events và repository interfaces.

## Project Structure

```
ihrm/
├── docker/
│   ├── php/Dockerfile
│   ├── nextjs/Dockerfile
│   └── nginx/conf.d/default.conf
├── src/
│   ├── backend/           # Laravel 12
│   │   └── app/Modules/   # 19 modules
│   └── frontend/          # Next.js 14
├── docs/
│   ├── srs/               # SRS documents
│   └── superpowers/       # Specs & Plans
├── docker-compose.yml
├── Makefile
└── .env.example
```

## Module Conventions

**Identity** là module tham chiếu. Module mới phải theo cấu trúc:

```
Domain/Aggregates/<Name>/{Model, ValueObjects, Events}
Domain/Repositories/<Name>RepositoryInterface.php
Domain/Exceptions/
Application/Commands|Queries + Handlers
Infrastructure/Persistence/Eloquent{Model, Repository}
Infrastructure/Http/{Controllers, Requests, Resources, Middleware}
Infrastructure/Seeders/
Routes/api.php
```

### Routing

- `routes/api.php` chỉ load module routes — không thêm route trực tiếp.
- Mỗi module tự quản lý `Routes/api.php` với prefix tương ứng.
- Middleware và prefix khai báo trong module route file.

## Testing

```bash
# Toàn bộ backend test
make test

# Hoặc trực tiếp
docker compose run --rm app php artisan test --compact
```

Cấu trúc mirror module:

```
tests/Unit/Modules/<Module>/
tests/Feature/Modules/<Module>/
```

Mỗi module mới cần ít nhất một feature test cho auth/permission boundary.

## Domain Access

| URL                         | Service            |
|-----------------------------|--------------------|
| http://ihrm.test            | Frontend (Next.js) |
| http://api.ihrm.test/api/v1 | Backend API        |

## Roadmap

```
Phase 1 ── Core Platform ── Đang xây dựng
Phase 2 ── Workforce Operations
Phase 3 ── Talent Lifecycle
Phase 4 ── Enterprise Extensions
```

Xem `docs/ROADMAP.md` và `docs/superpowers/` cho chi tiết.
