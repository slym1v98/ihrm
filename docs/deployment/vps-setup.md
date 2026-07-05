# VPS Deployment Guide

## Prerequisites

```bash
# Docker & Docker Compose
curl -fsSL https://get.docker.com | sh
sudo usermod -aG docker $USER
# Logout & login lại để nhóm docker生效

# Git
sudo apt install -y git
```

## 1. Clone

```bash
cd /opt
git clone https://github.com/slym1v98/ihrm.git
cd ihrm
```

## 2. Environment

```bash
cp .env.example .env.production
nano .env.production
```

`.env.production` tối thiểu:

```env
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:... # php artisan key:generate --show

DB_CONNECTION=pgsql
DB_HOST=db
DB_PORT=5432
DB_DATABASE=ihrm
DB_USERNAME=ihrm
DB_PASSWORD=your_strong_password

REDIS_HOST=redis
QUEUE_CONNECTION=redis
CACHE_STORE=redis

FRONTEND_URL=https://ihrm.flownest.vn
SANCTUM_STATEFUL_DOMAINS=ihrm.flownest.vn

# GHCR PAT (GitHub Personal Access Token with read:packages)
GHCR_PAT=github_pat_xxx
```

## 3. GitHub PAT cho GHCR

Tạo PAT tại: https://github.com/settings/tokens

- **Name:** `ihrm-vps-pull`
- **Expiration:** Custom (vd: 1 year)
- **Scopes:** `read:packages` (chỉ cần quyền đọc)

```bash
echo "GHCR_PAT=github_pat_xxx" >> .env.production
# Login để Docker có thể pull private images
echo $GHCR_PAT | docker login ghcr.io -u slym1v98 --password-stdin
```

## 4. Database Setup

```bash
# Tạo volume riêng cho PostgreSQL data
docker volume create pgdata

# Khởi động DB trước
docker compose -f docker-compose.prod.yml up -d db

# Kiểm tra DB ready
docker compose -f docker-compose.prod.yml logs db

# Run migration (thủ công, không auto migrate)
docker compose -f docker-compose.prod.yml run --rm app php artisan migrate

# Seed dữ liệu
docker compose -f docker-compose.prod.yml run --rm app php artisan db:seed --class=DatabaseSeeder
```

## 5. Start Services

```bash
# Pull latest images từ GHCR
docker compose -f docker-compose.prod.yml pull

# Start tất cả services
docker compose -f docker-compose.prod.yml up -d

# Kiểm tra logs
docker compose -f docker-compose.prod.yml logs -f
```

## 6. Nginx & SSL (Cloudflare)

File `docker/nginx/conf.d/prod.conf` có thể cần custom cho domain:

```nginx
server {
    listen 80;
    server_name api.ihrm.flownest.net;

    location / {
        proxy_pass http://app:8000;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}

server {
    listen 80;
    server_name ihrm.flownest.vn;

    location / {
        proxy_pass http://frontend:3000;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

> **SSL:** Cloudflare Flexible mode — Cloudflare xử lý SSL, upstream HTTP từ Nginx tới app/frontend.

## 7. Auto-Update (Watchtower)

Watchtower đã có trong `docker-compose.prod.yml`. Nó sẽ:

- Poll GHCR mỗi 300s (5 phút)
- Khi phát hiện image mới → pull + restart container
- Container order: app -> queue -> frontend -> nginx

**Kiểm tra Watchtower logs:**

```bash
docker compose -f docker-compose.prod.yml logs watchtower
```

## 8. Useful Commands

```bash
# View logs
docker compose -f docker-compose.prod.yml logs -f app

# Execute artisan
docker compose -f docker-compose.prod.yml exec app php artisan cache:clear

# Restart service
docker compose -f docker-compose.prod.yml restart queue

# Manual update (khi Watchtower chưa kịp poll)
docker compose -f docker-compose.prod.yml pull && docker compose -f docker-compose.prod.yml up -d

# Full rebuild + restart
docker compose -f docker-compose.prod.yml down
docker compose -f docker-compose.prod.yml pull
docker compose -f docker-compose.prod.yml up -d
```

## 9. Troubleshooting

| Problem | Solution |
|---------|----------|
| `denied: requested access to the resource is denied` | Check GHCR_PAT, login lại |
| `Connection refused` to DB | `docker compose logs db` kiểm tra |
| 500 errors after update | `docker compose exec app php artisan optimize:clear` |
| Watchtower không update | `docker compose logs watchtower` kiểm tra poll |
