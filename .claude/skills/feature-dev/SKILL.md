---
name: feature-dev
description: Full feature-development workflow for LaundryHub (Laravel 12, Vue 3 + Inertia, RBAC, per-shop data isolation) with an approval gate before implementation. Use when asked to build or materially change a feature, not for one-line fixes.
---

# Feature Development Workflow

Run the phases in order. Do not skip the approval gate. Architecture, sensitive-URL, and table/KPI rules live in `AGENTS.md`; follow them and do not restate them here. Project-specific checklists are in [reference.md](reference.md); load it during Plan, Implement, and Test.

## Safety rules (always on)

- Never expose secrets or copy `.env` contents into reports.
- Never use `git stash`.
- No destructive database commands (`migrate:fresh`, `db:wipe`, `migrate:rollback`, truncates, drops) and no production migrations without explicit approval. Run migrations only against the local/test database.
- Never commit, push, or deploy without explicit approval.
- Never claim a check passed unless it ran and succeeded. Report failures and skipped checks as such.
- Do not implement assumptions that could change business rules (pricing, plans, payments, payroll, permissions); ask.
- Check `git status` before starting and preserve unrelated work. Adapt commands to what the repo actually has; verify a script or file exists before using it.

## 1. Understand the goal

Restate the request, then pin down:
- Acceptance criteria (observable behavior).
- Roles involved: `super_admin`, shop `owner`, `staff` (with module/action permissions), customer `user`.
- Constraints, and what is explicitly out of scope.

Ask the user only about genuine ambiguity or business-rule questions (use `AskUserQuestion`). Resolve everything else from the code.

## 2. Explore (read-only, no edits)

- Run `git status`, then find the closest existing feature and mirror its route, request, controller, service, repository, page, and test.
- Search for existing logic to reuse before writing new code.
- Read the relevant sections of `routes/web.php`, middleware, models, and `tests/`.
- Note which layers are legacy (e.g. controllers with inline queries); do not copy that pattern.

## 3. Plan

Cover each item or state "none": affected files; migrations and indexes; routes (with middleware); Form Requests and validation; services, repositories, models; policies/permissions; Vue pages and components; error handling; notifications/jobs; tests (see [reference.md](reference.md)). Include risks and migration impact (new columns on large tables, backfills, rollback story).

## 4. Approval gate

Present the plan, risks, and migration impact, then **stop and wait for approval**. After approval, proceed through phases 5-8 without re-asking. Ask again only if scope or risk changes materially (new tables, changed business rules, destructive migrations, unexpected files).

## 5. Implement

- Small, incremental changes in the layer order from `AGENTS.md`: Route, Form Request, Controller, Service, Repository.
- Apply RBAC and tenant isolation per [reference.md](reference.md): shop scoping happens in the repository query; unauthorized or out-of-scope records return the same 404 as missing ones.
- Reuse existing components, EasyDataTable patterns, and the icon library.
- Touch only files the plan names. If something else needs changing, say so and update the plan.
- Wayfinder output (`resources/js/routes`, `resources/js/actions`) is committed in this repo; regenerate it after route/controller changes instead of hand-editing.

## 6. Test

Add or update tests for: success path, validation failures, authorization (each role), tenant isolation (shop A cannot touch shop B), edge cases, and regressions for touched behavior. Follow existing test style in `tests/Feature` (Pest on PHPUnit, factories).

Run what exists, narrowest first, then wider:
- `php artisan test --filter=<Name>`, then `composer test`.
- `vendor/bin/pint --test` (PHP formatting, enforced in CI).
- `npm run format:check`, `npm run lint`, `npm run build`.
- `npx vue-tsc --noEmit` if frontend types changed (not a CI step; report if it already fails on unrelated code).

CI (`.github/workflows`) runs Pint, Prettier, ESLint, and `./vendor/bin/pest` against MySQL; local runs may use a different DB, so mention the difference when relevant. Fix failures you caused. Report pre-existing failures without hiding them.

## 7. Review

For anything non-trivial, get an independent read-only review (a read-only subagent such as `Explore`, or `/code-review`). Ask it to check: security and authorization gaps, tenant leaks, data integrity and transactions, duplicated logic, regressions, missing tests. Fix valid findings, rerun the affected checks, and note findings you rejected and why.

## 8. Report

Give: what was implemented; changed files; migrations (name, effect, reversibility); commands run with real results; unresolved issues and risks; deployment steps (migrate, `npm run build`, cache clears, env/config changes). Do not commit or push; ask whether to.
