# Phase 1 Sub-1 Design — Identity & Access

Version: 0.1
Date: 2026-07-01
Status: Design approved (brainstorming)

## 1. Scope

Build Identity & Access module (`app/Modules/Identity/`) as the first sub-project of iHRM Phase 1 Core Platform. Covers authentication, user management, role/permission-based authorization, and data-scope foundation.

**In scope:** Login/logout, user CRUD, role CRUD, permission read-only, role-permission assignment, user-role assignment, data-scope assignment, permission middleware, admin seed data, full test suite (unit + integration + feature).

**Out of scope:** Password reset via email, 2FA, session auth, employee/contract/document (later sub-projects), audit log writes (events emitted only), branch/department FK enforcement, rate limiting.

## 2. Architecture

**Pattern:** Strict DDD tactical with 3 layers.

```
Module/Identity/
  Domain/         — Pure PHP, no Laravel deps
  Application/    — Commands/Queries + Handlers, orchestrates domain
  Infrastructure/ — Eloquent, HTTP controllers, middleware, seeders, routes
```

**Dependency:** Domain ← Application ← Infrastructure. Domain knows nothing outside itself.

## 3. Module Layout

```
app/Modules/Identity/
  Domain/
    Aggregates/User/{User.php, UserStatus.php, Email.php, RoleBinding.php, DataScopeAssignment.php}
    Aggregates/Role/{Role.php, RoleCode.php, RolePermission.php}
    Events/{UserCreated, UserLoggedIn, UserLoginFailed, UserDisabled, UserReactivated,
            UserRoleAssigned, UserRoleRevoked, UserDataScopeGranted, UserPasswordChanged,
            RoleCreated, RoleUpdated, RolePermissionGranted, RolePermissionRevoked}.php
    Repositories/{UserRepositoryInterface.php, RoleRepositoryInterface.php}
    Services/{PasswordHasher.php, DataScopeResolver.php}
    Exceptions/{UserNotFoundException.php, RoleNotFoundException.php,
                DuplicateEmailException.php, RoleAlreadyAssignedException.php,
                RoleNotActiveException.php, InvalidPasswordException.php,
                UserDisabledException.php}
  Application/
    Commands/{CreateUser, DisableUser, ReactivateUser, ChangePassword,
              AssignRole, RevokeRole, GrantDataScope, CreateRole, UpdateRole,
              GrantRolePermission, RevokeRolePermission}Command.php
    CommandHandlers/{CreateUser, DisableUser, ReactivateUser, ChangePassword,
                     AssignRole, RevokeRole, GrantDataScope, CreateRole, UpdateRole,
                     GrantRolePermission, RevokeRolePermission}Handler.php
    Queries/{GetUser, ListUsers, GetRole, ListRoles, ListPermissions}Query.php
    QueryHandlers/{GetUser, ListUsers, GetRole, ListRoles, ListPermissions}Handler.php
    Services/{AuthenticationService.php, AuthorizationService.php}
  Infrastructure/
    Persistence/
      Eloquent/{UserModel, RoleModel, PermissionModel, UserRoleModel,
                RolePermissionModel, DataScopeAssignmentModel}.php
      Repositories/{EloquentUserRepository.php, EloquentRoleRepository.php}
    Http/
      Controllers/{AuthController, UserController, RoleController, PermissionController}.php
      Requests/{LoginRequest, CreateUserRequest, UpdateUserRequest, ChangePasswordRequest,
                AssignRoleRequest, GrantDataScopeRequest, CreateRoleRequest, UpdateRoleRequest,
                GrantRolePermissionRequest}.php
      Resources/{UserResource, RoleResource, PermissionResource}.php
      Middleware/PermissionMiddleware.php
    Seeders/{PermissionSeeder, RoleSeeder, AdminUserSeeder}.php
    Database/Migrations/{6 migration files}
  Routes/api.php
```

## 4. Domain Model

### User Aggregate Root

```
User {
  id: UserId (UUID)
  employeeId: ?EmployeeId (UUID)
  email: Email (VO)
  passwordHash: HashedPassword (VO)
  name: UserName (VO)
  status: UserStatus (active|disabled)
  lastLoginAt: ?DateTimeImmutable
  roleBindings: RoleBinding[]
  dataScopeAssignments: DataScopeAssignment[]

  static create(email, hashedPassword, name): self
  disable(): void                          — emits UserDisabled
  reactivate(): void                       — emits UserReactivated
  changePassword(newPassword): void        — emits UserPasswordChanged
  recordLogin(): void                      — emits UserLoggedIn
  recordFailedLogin(): void                — emits UserLoginFailed
  assignRole(roleId): void                 — emits UserRoleAssigned
  revokeRole(roleId): void                 — emits UserRoleRevoked
  grantDataScope(scope): void              — emits UserDataScopeGranted

  Invariants:
  • Email must be unique across active users (repository-level check).
  • Disabled user cannot login (checked in AuthenticationService).
  • Same role cannot be actively assigned twice (domain guard in assignRole).
}

UserStatus: enum { active, disabled }
Email: VO — validates format, normalizes to lowercase
HashedPassword: VO — wraps bcrypt hash string
UserId: VO — wraps UUID string
```

### Role Aggregate Root

```
Role {
  id: RoleId (UUID)
  code: RoleCode (VO)          — unique, uppercase, snake_case
  name: string
  description: ?string
  active: bool
  permissions: RolePermission[]   — child entities

  static create(code, name, description): self   — emits RoleCreated
  update(name, description): void                — emits RoleUpdated
  activate(): void
  deactivate(): void
  grantPermission(permissionCode): void          — emits RolePermissionGranted
  revokePermission(permissionCode): void         — emits RolePermissionRevoked
  isActive(): bool
}
```

### Permission

Permission is a **reference catalog**. Not a DDD aggregate. Persisted in `permissions` table. Read-only API. Attached to roles via `RolePermission` child entity.

## 5. Database Schema

```
users
  id uuid pk
  employee_id uuid nullable, index
  name varchar(255)
  email varchar(255) unique
  password varchar(255)
  status varchar(20) default 'active'
  last_login_at timestamp nullable
  timestamps

roles
  id uuid pk
  code varchar(100) unique
  name varchar(255)
  description text nullable
  active boolean default true
  timestamps

permissions
  id uuid pk
  code varchar(150) unique
  module varchar(50)
  action varchar(100)
  description text nullable
  active boolean default true
  timestamps

role_permissions
  id uuid pk
  role_id uuid fk roles.id cascade
  permission_code varchar(150) fk permissions.code restrict
  created_at timestamp
  unique(role_id, permission_code)

user_roles
  id uuid pk
  user_id uuid fk users.id cascade
  role_id uuid fk roles.id restrict
  assigned_by uuid nullable fk users.id nullOnDelete
  assigned_at timestamp
  revoked_at timestamp nullable
  PARTIAL UNIQUE (user_id, role_id) WHERE revoked_at IS NULL

data_scope_assignments
  id uuid pk
  user_id uuid fk users.id cascade
  scope_type varchar(30)  — self|direct_reports|department|branch|all_company
  branch_id uuid nullable, index
  department_id uuid nullable, index
  effective_from date nullable
  effective_to date nullable
  timestamps
```

**No hard FK to employees/branches/departments** — those tables created in later sub-projects. App-level validation pending Organization module.

## 6. API Endpoints

Base: `/api/v1`, BearerAuth, Content-Type: application/json

| Method | Path | Permission | Notes |
|--------|------|-----------|-------|
| POST | /auth/login | unprotected | 200 → {data: {access_token, token_type, user}} |
| POST | /auth/logout | auth:sanctum | Revokes current token |
| GET | /auth/me | auth:sanctum | User + roles + permissions |
| POST | /auth/change-password | auth:sanctum | current_password + new_password |
| GET | /users | identity.user.list | Paginated |
| POST | /users | identity.user.create | email, name, password, employee_id? |
| GET | /users/{id} | identity.user.view | Full profile |
| PATCH | /users/{id} | identity.user.update | name, email |
| POST | /users/{id}/disable | identity.user.disable | Disables user account |
| POST | /users/{id}/reactivate | identity.user.reactivate | Re-enables user account |
| POST | /users/{id}/reset-password | identity.user.reset_password | new_password |
| POST | /users/{id}/roles | identity.user.assign_role | role_id, scope_type, branch?, department? |
| DELETE | /users/{id}/roles/{roleId} | identity.user.revoke_role | Soft revoke (set revoked_at) |
| POST | /users/{id}/data-scopes | identity.user.grant_scope | scope_type, branch?, department? |
| DELETE | /users/{id}/data-scopes/{scopeId} | identity.user.revoke_scope | |
| GET | /roles | identity.role.list | Paginated |
| POST | /roles | identity.role.create | code, name, description |
| GET | /roles/{id} | identity.role.view | With permission list |
| PATCH | /roles/{id} | identity.role.update | name, description |
| POST | /roles/{id}/activate | identity.role.update | |
| POST | /roles/{id}/deactivate | identity.role.update | |
| POST | /roles/{id}/permissions | identity.role.grant_permission | permission_code |
| DELETE | /roles/{id}/permissions/{code} | identity.role.revoke_permission | |
| GET | /permissions | identity.permission.list | Read-only catalog |

Response format: success → `{data: ...}` or paginated envelope; error → `{error: {code, message, details, trace_id}}`.

## 7. Seed Data

### Permissions

```
identity.user.list, .view, .create, .update, .disable, .reactivate, .reset_password,
  .assign_role, .revoke_role, .grant_scope, .revoke_scope
identity.role.list, .view, .create, .update, .grant_permission, .revoke_permission
identity.permission.list
```

### Roles

| Code | Permissions |
|------|-------------|
| SUPER_ADMIN | All permission codes |
| HR_MANAGER | user.list, user.view, role.list, role.view, permission.list |
| EMPLOYEE | (none — can only use /auth endpoints) |

### Admin User

```
email: admin@ihrm.local (configurable via IHRM_ADMIN_EMAIL env)
password: password (configurable via IHRM_ADMIN_PASSWORD env)
role: SUPER_ADMIN
scope: all_company
```

## 8. Error Codes

| Code | HTTP | Trigger |
|------|------|---------|
| INVALID_CREDENTIALS | 401 | Login wrong email/password |
| USER_DISABLED | 401 | Disabled user tries login |
| UNAUTHENTICATED | 401 | Missing/invalid token |
| PERMISSION_DENIED | 403 | No permission for endpoint |
| USER_NOT_FOUND | 404 | User ID not found |
| ROLE_NOT_FOUND | 404 | Role ID not found |
| DUPLICATE_EMAIL | 409 | Email already in use |
| ROLE_ALREADY_ASSIGNED | 409 | Same role active on user |
| ROLE_INACTIVE | 409 | Role is inactive |
| VALIDATION_ERROR | 422 | FormRequest validation |
| INVALID_PASSWORD | 422 | Current password mismatch |

## 9. Middleware Chain

```
ForceJsonMiddleware (global api prepend)
  → auth:sanctum
    → PermissionMiddleware (checks user has {code} permission via AuthorizationService)
      → Controller
```

AuthorizationService resolves user permissions per-request (no cache in Phase 1).

## 10. Testing Strategy

| Layer | Coverage | Key tests |
|-------|----------|-----------|
| Domain unit | 100% business logic | User behavior, status transitions, event emission, email validation |
| Application integration | 90%+ handlers | Command handler flows, exception paths, event dispatch |
| Feature HTTP | 80%+ | Login auth flows, permission enforcement, CRUD endpoints, error responses |

## 11. Acceptance Criteria

- All 18+ API endpoints functional and documented
- Sanctum login issues token, validated on all protected routes
- PermissionMiddleware correctly grants/denies access
- Role assignment + data scope work end-to-end
- Seeders create SUPER_ADMIN user, roles, permissions
- All tests pass (unit + integration + feature)
- Error responses follow ErrorResource format
- Paginated responses follow PaginatedCollection format
- Domain events emitted on all mutations (audit listener ready for Sub-7)
- Disabled user cannot login (verified by test)
- No N+1 queries in list endpoints
- Password hashed with bcrypt
- README documents module usage

## 12. Implementation Order

1. Migrations + Eloquent models
2. Domain layer (VOs, aggregates, events, repo interfaces)
3. Application layer (commands, handlers, services)
4. Infrastructure persistence (Eloquent repos, mapping)
5. HTTP layer (controllers, requests, middleware, routes)
6. Seeders
7. Tests
8. OpenAPI verification

## 13. Risks

- Branch/department FK deferred → app-level validation only until Organization module
- DDD overhead for pure CRUD (permissions, role-permission mapping) — accepted trade-off
- Data scope integration tests limited until Employee/Organization exist
