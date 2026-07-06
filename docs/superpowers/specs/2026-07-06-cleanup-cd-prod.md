# Cleanup CD — Remove Build & Deploy to Prod

**Date:** 2026-07-06
**Status:** Approved

## Motivation

Project is in active local development. No production deployment needed yet.
Build/push to GHCR, Watchtower auto-update, and prod Dockerfiles add unnecessary complexity.

## Scope

Remove everything related to building and deploying to production.

## Changes

1. **Delete** `.github/workflows/build.yml` — pushes Docker images to GHCR
2. **Delete** `docker-compose.prod.yml` — production compose config
3. **Delete** `docker/backend/Dockerfile.prod` — production backend image
4. **Delete** `docker/frontend/Dockerfile.prod` — production frontend image  
5. **Delete** `docker/nginx/Dockerfile.prod` — production nginx image
6. **Keep** `.github/workflows/ci.yml` — CI tests still run on push/PR
7. **Keep** `docker-compose.yml` — local dev setup
8. **Keep** `docker/php/Dockerfile`, `docker/nextjs/Dockerfile` — local dev images

## Non-Changes

- `.env.production` stays in `.gitignore`
- No changes to `ci.yml`
- No changes to source code

