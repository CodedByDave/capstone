# LaundryHub feature-dev reference

## Roles and routing (see `routes/web.php`, `bootstrap/app.php`)

- `role:super_admin` for `/admin/*`; shop owner routes under `/shop/*`; `role:staff` for `/staff/*`.
- Middleware aliases: `role`, `permission:<Module>,<action>` (owners bypass; staff are checked against `RolePermission` per shop), `shop.capability:<feature>` (plan-gated features such as branches, pickup, delivery), plus shop setup, subscription, archived, and business-agreement guards on shop groups.
- Staff features mirror owner features under `/staff/*` with `permission:` middleware. When adding a shop feature, decide whether staff get it and which module/action it maps to.
- Policies are minimal (`EmployeePolicy` only); most authorization is middleware plus repository scoping.

## Tenant isolation checklist

- Every shop-owned table has `shop_id`; repositories filter on it (see `BranchRepository::paginateForShop`). Scope route-bound lookups in the repository, not after loading.
- Derive `shop_id` from the authenticated user/employee, never from request input.
- Out-of-scope or nonexistent record: same 404.
- Never put sequential IDs in sensitive URLs (see `AGENTS.md`).
- Test: create two shops, act as shop A, assert shop B's records are not listed, shown, updated, or deleted.

## Backend conventions

- Repositories extend `App\Repositories\Repository`; services live in `app/Services`; requests are grouped under `app/Http/Requests/{Admin,Shop,User,...}`.
- Contracts plus `Eloquent/` implementations exist only for platform roles; add contracts only where a real boundary helps.
- Transactions start in services. Audit/activity logging goes through `ActivityLogService` / `AuditLogService` where similar features already log.

## Frontend conventions

- Pages in `resources/js/pages/{admin,shop,staff,user}`; Inertia + Vue 3 + TypeScript; ShadCN Vue + Tailwind 4.
- Tables use `EasyDataTable` modeled on `pages/admin/shop/Index.vue`; KPIs likewise (see `AGENTS.md`). Keep existing icons.

## Test conventions

- Pest files in `tests/Feature/{Admin,Auth,Settings,Shop,User}` and `tests/Unit`; helper functions at file top; `AccountType` enum for roles; Inertia assertions via `AssertableInertia`.
- Repository tests exist for non-trivial queries (`BranchRepositoryTest`, `EmployeeRepositoryTest`).
- Payment/PayMongo tests need credentials supplied (see CI env).

## Migration safety

- Prefer additive, nullable-or-defaulted columns; add indexes for new filter/sort columns.
- Provide a working `down()`; never edit already-applied migrations.
- Run only on the local/test DB; production migration is a deployment step listed in the report, not something you run.
