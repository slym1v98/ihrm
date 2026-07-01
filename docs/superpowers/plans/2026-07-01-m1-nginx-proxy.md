# M1 Nginx Proxy Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Route all dev HTTP access through nginx so `ihrm.test` serves frontend and `api.ihrm.test` serves backend, with no direct host ports for app/frontend/db/redis/minio.

**Architecture:** Add nginx reverse proxy container as the only host-facing service on port 80. Internal services use Docker network `expose` only. Dev domains resolve through `/etc/hosts`.

**Tech Stack:** Docker Compose, nginx 1.27-alpine, Laravel dev server, NextJS dev server.

---

## File Map

- `docker-compose.yml`: add `nginx` service; convert service `ports` to `expose`.
- `docker/nginx/conf.d/default.conf`: nginx virtual hosts for `ihrm.test` and `api.ihrm.test`.
- `.env.example`, `.env.local.example`, `.env.dev.example`, `.env.prod.example`: domain URLs and `NGINX_PORT=80`.
- `docs/superpowers/plans/2026-06-30-m1-foundation.md`: align M1 docs with nginx proxy architecture.

---

### Task 1: Add nginx reverse proxy config

**Files:**
- Create: `docker/nginx/conf.d/default.conf`

- [ ] **Step 1: Write nginx config**

Create `docker/nginx/conf.d/default.conf`:

```nginx
server {
    listen 80;
    server_name ihrm.test;

    location / {
        proxy_pass http://frontend:3000;
        proxy_http_version 1.1;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
    }
}

server {
    listen 80;
    server_name api.ihrm.test;

    location / {
        proxy_pass http://app:8000;
        proxy_http_version 1.1;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

- [ ] **Step 2: Validate config syntax with nginx container**

Run:

```bash
docker run --rm -v "$(pwd)/docker/nginx/conf.d:/etc/nginx/conf.d:ro" nginx:1.27-alpine nginx -t
```

Expected: `syntax is ok` and `test is successful`.

- [ ] **Step 3: Commit**

```bash
git add docker/nginx/conf.d/default.conf
git commit -m "infra(m1): add nginx reverse proxy config"
```

---

### Task 2: Refactor Docker Compose ports

**Files:**
- Modify: `docker-compose.yml`

- [ ] **Step 1: Add nginx service**

Add service:

```yaml
  nginx:
    image: nginx:1.27-alpine
    depends_on:
      - app
      - frontend
    ports:
      - "${NGINX_PORT:-80}:80"
    volumes:
      - ./docker/nginx/conf.d:/etc/nginx/conf.d:ro
```

- [ ] **Step 2: Remove direct host ports from services**

Replace:

```yaml
ports:
  - "${APP_PORT:-8000}:8000"
```

with:

```yaml
expose:
  - "8000"
```

Replace frontend/db/redis/minio `ports` with:

```yaml
frontend:
  expose: ["3000"]
db:
  expose: ["5432"]
redis:
  expose: ["6379"]
minio:
  expose: ["9000", "9001"]
```

- [ ] **Step 3: Validate Compose config**

Run:

```bash
docker compose config >/tmp/ihrm-compose.yml
```

Expected: exit code 0.

- [ ] **Step 4: Commit**

```bash
git add docker-compose.yml
git commit -m "infra(m1): expose services only through nginx"
```

---

### Task 3: Update env examples

**Files:**
- Modify: `.env.example`
- Modify: `.env.local.example`
- Modify: `.env.dev.example`
- Modify: `.env.prod.example`

- [ ] **Step 1: Replace URLs and remove direct service port vars**

Use this content for all four files:

```env
APP_ENV=local
APP_DEBUG=true
APP_URL=http://api.ihrm.test
FRONTEND_URL=http://ihrm.test
NGINX_PORT=80

DB_CONNECTION=pgsql
DB_HOST=db
DB_PORT=5432
DB_DATABASE=ihrm
DB_USERNAME=ihrm
DB_PASSWORD=ihrm

REDIS_HOST=redis
REDIS_PORT=6379

MINIO_ENDPOINT=http://minio:9000
MINIO_ACCESS_KEY=ihrm
MINIO_SECRET_KEY=ihrm_secret
MINIO_BUCKET=ihrm-documents

NEXT_PUBLIC_API_URL=http://api.ihrm.test/api/v1
```

- [ ] **Step 2: Commit**

```bash
git add .env.example .env.local.example .env.dev.example .env.prod.example
git commit -m "config(m1): use dev domains for app and frontend"
```

---

### Task 4: Update M1 foundation docs

**Files:**
- Modify: `docs/superpowers/plans/2026-06-30-m1-foundation.md`

- [ ] **Step 1: Update architecture paragraph**

State that nginx is the only host-facing service and dev domains are `ihrm.test` + `api.ihrm.test`.

- [ ] **Step 2: Add host setup note**

Add command:

```bash
echo "127.0.0.1 ihrm.test api.ihrm.test" | sudo tee -a /etc/hosts
```

- [ ] **Step 3: Add validation commands**

Add:

```bash
docker compose up -d --build
curl -I http://ihrm.test
curl -i http://api.ihrm.test/api/v1/users
docker compose ps
```

Expected:
- Frontend returns 200.
- Backend returns 401 JSON envelope.
- Only nginx has host port mapping.

- [ ] **Step 4: Commit**

```bash
git add docs/superpowers/plans/2026-06-30-m1-foundation.md
git commit -m "docs(m1): document nginx proxy access"
```

---

### Task 5: Final verification

**Files:**
- No code changes unless verification exposes bug.

- [ ] **Step 1: Ensure hosts entry exists**

Manual host setup if not present:

```bash
grep -q "ihrm.test" /etc/hosts || echo "127.0.0.1 ihrm.test api.ihrm.test" | sudo tee -a /etc/hosts
```

- [ ] **Step 2: Rebuild and start stack**

```bash
docker compose up -d --build
```

Expected: nginx/app/frontend/db/redis/minio running.

- [ ] **Step 3: Verify frontend route**

```bash
curl -s -o /dev/null -w "%{http_code}\n" http://ihrm.test
```

Expected: `200`.

- [ ] **Step 4: Verify API route**

```bash
curl -s -w "\n%{http_code}\n" http://api.ihrm.test/api/v1/users
```

Expected: JSON `error.code=UNAUTHENTICATED`, HTTP `401`.

- [ ] **Step 5: Verify direct service ports closed**

```bash
curl -s -o /dev/null -w "%{http_code}\n" http://localhost:8000 || true
curl -s -o /dev/null -w "%{http_code}\n" http://localhost:3000 || true
docker compose ps
```

Expected: app/frontend do not expose host ports; only nginx maps `0.0.0.0:80->80/tcp`.

- [ ] **Step 6: Run backend tests**

```bash
docker compose run --rm app php artisan test
```

Expected: pass.

- [ ] **Step 7: Commit verification doc updates if any**

```bash
git status --short
```

Expected: clean.

---

## Self-Review

- Spec coverage: nginx service, host domains, no direct service ports, env updates, docs updates, validation covered.
- Placeholder scan: no TODO/TBD placeholders.
- Type consistency: service names match existing Compose (`app`, `frontend`, `db`, `redis`, `minio`).
