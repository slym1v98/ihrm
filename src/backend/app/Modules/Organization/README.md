# Organization Module

Branch, Department, and Position management with DDD tactical design.

## Routes

All routes under `/api/v1`, require `auth:sanctum` + permission middleware.

| Method | Endpoint | Permission |
|--------|----------|------------|
| GET | /branches | organization.branch.list |
| POST | /branches | organization.branch.create |
| GET | /branches/{id} | organization.branch.view |
| PATCH | /branches/{id} | organization.branch.update |
| POST | /branches/{id}/activate | organization.branch.update |
| POST | /branches/{id}/deactivate | organization.branch.update |
| GET | /departments | organization.department.list |
| POST | /departments | organization.department.create |
| GET | /departments/{id} | organization.department.view |
| PATCH | /departments/{id} | organization.department.update |
| POST | /departments/{id}/move | organization.department.move |
| POST | /departments/{id}/activate | organization.department.update |
| POST | /departments/{id}/deactivate | organization.department.update |
| GET | /positions | organization.position.list |
| POST | /positions | organization.position.create |
| GET | /positions/{id} | organization.position.view |
| PATCH | /positions/{id} | organization.position.update |
| POST | /positions/{id}/activate | organization.position.update |
| POST | /positions/{id}/deactivate | organization.position.update |
| GET | /org-tree | organization.tree.view |

## Seed

```bash
php artisan db:seed --class=OrgStructureSeeder
```

Creates HCM-HQ and HN-OFFICE branches, departments with hierarchy, and 8 positions.

## Permissions

14 `organization.*` permissions auto-seeded. HR_MANAGER gets full access. EMPLOYEE gets tree.view.

## Tests

```bash
php artisan test tests/Unit/Modules/Organization
php artisan test tests/Feature/Modules/Organization
```

## File Structure

```
Application/
  Commands/ + CommandHandlers/   — Branches, Departments, Positions
  Queries/ + QueryHandlers/     — get/list for each aggregate + org tree
Domain/
  Aggregates/ — Branch, Department, Position (DDD aggregate roots)
  Events/     — 13 domain events
  Exceptions/ — 10 domain + 1 InvalidCodeException
  Repositories/ — 3 interfaces
Infrastructure/
  Http/        — Controllers, FormRequests, Resources
  Persistence/ — Eloquent models + repository implementations
  Seeders/     — OrgStructureSeeder
Routes/api.php
```
