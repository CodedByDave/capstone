# Repository Instructions

## Laravel Backend Architecture

All new or modified Laravel backend code must use the following layered architecture. Apply these rules whenever a backend feature is created, fixed, or materially changed.

### Required dependency flow

Use this one-way dependency flow:

`Route -> Form Request -> Controller -> Service -> Repository -> Model / Database`

Responses return through the controller. Lower layers must never depend on controllers, HTTP responses, redirects, sessions, or Inertia.

### Controllers: HTTP orchestration only

Controllers must remain as thin as possible. A controller may:

- Receive route-bound models and validated Form Request data.
- Call a service method.
- Return an Inertia response, JSON response, redirect, download, stream, or API Resource.
- Translate a known application result or exception into an HTTP response when necessary.

Controllers must not:

- Contain Eloquent or query-builder calls.
- Call `DB` directly or manage database transactions.
- Perform inline validation.
- Implement business rules, calculations, workflows, or authorization decisions.
- Build complex presentation payloads through large mapping or transformation blocks.
- Coordinate multiple repositories directly.

As a target, most controller actions should contain only a few lines: obtain validated input, invoke one service operation, and return the response.

### Form Requests: validation and request authorization

Create a dedicated Form Request for every state-changing endpoint and for non-trivial query/filter input.

Form Requests own:

- Validation rules and user-facing validation messages.
- Input normalization through `prepareForValidation()`.
- Request-level authorization through `authorize()` when appropriate.
- Safe access to validated data through `validated()` or a small typed data object.

Form Requests must not query or mutate application data except for narrowly scoped validation rules such as `exists` and `unique`. They must not execute business workflows.

### Services: business and application logic

Services own use-case behavior, including:

- Business rules and invariants.
- Multi-step workflows and coordination across repositories.
- Calculations, state transitions, and domain decisions.
- Database transaction boundaries.
- Dispatching jobs, events, or notifications as part of a completed use case.
- Converting repository results into application-level result data.

Services must not return redirects, Inertia responses, or other HTTP-layer objects. Prefer focused services with explicit method names over generic manager classes.

### Repositories: all persistence and queries

Repositories own database access, including:

- Eloquent and query-builder reads and writes.
- Filtering, sorting, searching, eager loading, pagination, aggregates, and existence checks.
- Creating, updating, deleting, restoring, and bulk persistence operations.
- Query-specific data mapping when needed for an application use case.

Do not place database queries in controllers or services. Services express what data operation is needed; repositories implement how it is stored or retrieved.

Use repository contracts and dependency injection when an abstraction boundary is useful or when multiple implementations/testing substitutes are expected. Avoid empty pass-through abstractions that add no meaningful boundary, but never bypass the repository layer for application queries.

### Models and presentation

Models should be limited to model concerns such as relationships, casts, attributes, reusable scopes, and lifecycle behavior intrinsic to the entity. Avoid placing multi-entity workflows in models.

Use API Resources, dedicated presenters, or small view-data objects for substantial response shaping. Do not move business logic into Vue components or response transformers.

### Transactions and errors

- Start transactions in the service layer, never in controllers or repositories.
- Keep repositories transaction-agnostic so a service can coordinate several repositories atomically.
- Throw meaningful domain or application exceptions from services when a rule is violated.
- Convert exceptions to HTTP behavior at the controller or centralized exception-handler boundary.
- Do not swallow failures. Preserve useful context while avoiding exposure of secrets or internal details.

### Suggested project structure

Follow the existing namespace when one is established; otherwise prefer:

- `app/Http/Controllers/...`
- `app/Http/Requests/...`
- `app/Services/...`
- `app/Repositories/Contracts/...`
- `app/Repositories/Eloquent/...`
- `app/Http/Resources/...`
- `app/DTOs/...` when typed transfer objects materially improve the boundary

Bind repository contracts to implementations in a service provider.

### Testing requirements

- Add feature tests for endpoint behavior, authorization, validation, and responses.
- Add focused service tests for important business rules and transaction behavior.
- Test repository filtering, sorting, pagination, aggregates, and persistence when queries are non-trivial.
- Mock or fake at architectural boundaries, not internal framework details.
- Run relevant tests, formatting, static analysis, and frontend checks before handoff.

### Working with legacy code

Do not rewrite unrelated legacy features solely to enforce this architecture. When modifying an existing backend flow, keep the requested scope controlled while moving the touched logic into the correct layers. Do not add new controller queries or business logic just because nearby legacy code already contains them.

### Limited exceptions

Migrations, seeders, factories, console-only maintenance scripts, and framework configuration may use the database directly when that is their intended responsibility. Any other exception must be explicitly justified in the implementation notes and kept as narrow as possible.

## Frontend Table and KPI Layout Standards

When the user asks to create, change, update, improve, or refactor a table layout without naming a different visual reference, use the admin **Shop Management** table in `resources/js/pages/admin/shop/Index.vue` as the canonical layout and styling reference.

- Every new or materially modified user-facing data table must use `vue3-easy-data-table` (`EasyDataTable`). Do not build data grids with the shadcn `Table` components or custom table markup unless the user explicitly requests an exception or `EasyDataTable` cannot support a required behavior.
- Match its overall table presentation, including the container, toolbar, search and filters, spacing, typography, borders, status badges, row actions, empty state, responsive behavior, and pagination where applicable.
- Define columns through `EasyDataTable` headers and use named header/item slots when custom rendering is needed.
- Sortable columns must show a visible sorting arrow at all times, matching the neutral, ascending, and descending arrow states used by the admin tables. The active sort column and direction must remain visually clear.
- For backend-paginated data, use `EasyDataTable` server options and perform searching, filtering, sorting, and pagination through the backend query flow instead of sorting only the currently loaded page.
- Include consistent loading and empty states, rows-per-page controls, and pagination behavior where the dataset supports them.
- Adapt the columns, labels, filters, actions, and data to the feature being changed; do not copy Shop Management-specific behavior that does not apply.
- Preserve the target feature's authorization, routes, and business behavior.
- Reuse the admin table classes, shared slots, and UI components where available instead of duplicating table behavior unnecessarily.
- If the user explicitly identifies another table or design as the reference, follow that instruction instead.

When the user asks to create, change, update, improve, or refactor KPI cards without naming a different visual reference, use the KPI section on the admin **Shop Management** page in `resources/js/pages/admin/shop/Index.vue` as the canonical layout and styling reference.

- Match its compact card grid, card treatment, spacing, typography, labels, and value hierarchy.
- Include only the KPIs needed for the target page and avoid decorative, redundant, or low-value metrics.
- Adapt the KPI labels and values to the target feature while preserving its data scope and permissions.
- If the user explicitly identifies another KPI design as the reference, follow that instruction instead.
