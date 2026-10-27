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

## Sensitive URL Identifier Standards

Never expose sequential database IDs in URLs for sensitive or user-owned resources such as payments, receipts, invoices, orders, account records, private documents, invitations, password-related actions, or administrative operations.

Choose the protection that fits the use case:

- Use a ULID or UUID as a stable public identifier when a resource needs a shareable, bookmarkable route. Store it in a dedicated unique indexed column and resolve it together with the authenticated owner's or tenant's scope.
- Use a signed URL when the URL grants permission to perform an action or access a time-limited resource. Prefer temporary signed routes for email actions, downloads, approvals, and other expiring links.
- Use authenticated encryption when the original internal value must be recovered from the route parameter. Validate decryption failures safely and never treat successful decryption as authorization.
- Use a cryptographically random token or keyed HMAC when a one-way lookup or verification token is appropriate. Do not use predictable hashes of sequential IDs, unsalted checksums, Base64 encoding, or reversible obfuscation as security controls.

Identifier protection never replaces authorization. Every sensitive lookup must also be scoped to the authenticated user, shop, tenant, role, or policy at the repository/query boundary, following the principle of least privilege. For resources outside the caller's scope, return the same not-found response used for nonexistent identifiers so the URL cannot be used to enumerate records. Do not include secrets, API keys, payment session credentials, personal data, or authorization claims in URLs or query strings.

## Frontend Table and KPI Layout Standards

### Icon preservation

- Do not remove existing meaningful icons when creating, updating, or refactoring frontend layouts, tables, cards, forms, navigation, buttons, statuses, or actions.
- Preserve icons that help users recognize an action, category, status, payment method, or feature. Reposition or restyle them when required by the target layout, but keep their meaning and accessibility intact.
- When adding a new action or information element that benefits from an icon, use the project's existing icon library and visual conventions.
- Icons must support the accompanying text or accessible label and must not replace essential text when doing so would make the interface unclear.
- Remove an icon only when the user explicitly requests its removal, it is misleading or duplicated, or retaining it would create an accessibility or functional problem. Document that exception in the implementation notes.

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

#### Table UX and design details

Apply these on top of the rules above, using `EasyDataTable` as the implementation and the project's existing theme tokens and accent color (do not introduce new fonts or colors, gradients, or emoji):

- **Toolbar:** a search input bound with `v-model` that filters across the table's key text columns, plus segmented status filter chips that show a count per status. The toolbar wraps on small screens.
- **Sortable headers:** clicking toggles asc/desc; the active column shows an accent-colored arrow and the others a faint neutral ↕. Keep `aria-sort` correct on each sortable header.
- **Header row:** small, uppercase, muted labels on a slightly tinted header row.
- **Selection:** row checkboxes with a select-all for the current page. When rows are selected, show a bulk-action bar (feature-appropriate actions plus Clear). Selected rows get a light tint of the accent color; rows have a subtle hover.
- **Identity cell:** for people or entities, show an initials avatar with a bold name and the muted secondary line (e.g. email) beneath it.
- **Status:** pill badges with a colored dot and a text label, never color alone.
- **Row actions:** a kebab "more actions" button per row with an `aria-label` naming the row.
- **Empty state:** an icon, a message, and a "Reset filters" button that clears search, filters, and sort.
- **Footer:** "Showing 1–6 of 12" on the left; prev/next and page numbers on the right, with prev/next disabled at the ends.
- **Paging:** reset to page 1 whenever search, filter, or sort changes.
- **Container:** white card with a 1px border and a 14px radius on a light grey page background; the table scrolls horizontally inside its card on small screens.
- **Accent usage:** reserve the accent color for primary actions, the active sort, and selection.
- **Accessibility:** text contrast of at least 4.5:1, touch targets of at least 44px, visible focus rings, and real `<button>`, `<input>`, and `<label>` elements so the table is fully keyboard accessible.
- **Reusability:** keep filter/sort/paginate/selection state in a composable (for example `useDataTable`) separate from the presentational component, with scoped slots for cell, bulk-action, and empty-state customization, so the logic is easy to test.

When the user asks to create, change, update, improve, or refactor KPI cards without naming a different visual reference, use the KPI section on the admin **Shop Management** page in `resources/js/pages/admin/shop/Index.vue` as the canonical layout and styling reference.

- Match its compact card grid, card treatment, spacing, typography, labels, and value hierarchy.
- Include only the KPIs needed for the target page and avoid decorative, redundant, or low-value metrics.
- Adapt the KPI labels and values to the target feature while preserving its data scope and permissions.
- If the user explicitly identifies another KPI design as the reference, follow that instruction instead.
