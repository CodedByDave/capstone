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
