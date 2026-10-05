# Shop Setup Wizard Implementation Plan

## Status

- Planning only
- Awaiting review and approval
- No application code has been changed

## Objective

Introduce a setup wizard for newly activated shop owners so the platform does not assume that every shop operates multiple branches or offers pickup and delivery.

The wizard will configure the shop's operating model. The selected subscription will continue to control plan entitlements and available modules.

## Recommendation

Run the setup wizard for every newly activated shop:

| Subscription | Wizard required | Location options | Pickup and delivery |
| --- | --- | --- | --- |
| Trial | Yes | Single or multiple for evaluation | Optional |
| Basic | Yes | Single location | Optional |
| Standard | Yes | Single location | Optional |
| Premium | Yes | Single or multiple locations | Optional |

The existing Premium description presents it as the multi-branch plan. Therefore, the recommended first version reserves multiple-location operation for Premium. This entitlement can be changed later without redesigning the wizard.

Subscription entitlement and shop preference must remain separate:

- Entitlement: Premium permits multiple locations.
- Preference: A particular Premium shop currently operates one location.
- Preference: Any shop may choose not to offer pickup or delivery.

Selecting Premium must not automatically create branches or enable logistics.

## Target onboarding process

```text
Register owner and shop
        |
        v
Select subscription plan
        |
        v
Submit application and documents
        |
        v
Admin approval
        |
        v
Complete payment or activate trial
        |
        v
Complete Shop Setup Wizard
        |
        v
Open configured dashboard
```

The wizard must not interrupt pending approval or unpaid subscription flows. It begins only after a paid subscription or trial is active.

## Wizard screens

### Step 1: Confirm shop information

Show the information already collected during registration:

- Shop name
- Contact number
- Main shop address
- Map location

The owner may correct this information. The registered address represents the main shop location and must not automatically produce a branch record.

### Step 2: Configure location structure

Ask:

> How many locations does your business operate?

Options:

- One location
- Multiple locations

Single-location behavior:

- Treat the registered shop as the main location.
- Do not create a branch record.
- Hide Branch Management.
- Hide branch selectors, columns, and filters where they have no value.
- Allow employees, attendance, and orders to remain unassigned to a branch.

Multiple-location behavior:

- Only available when the active plan permits it.
- Enable Branch Management.
- After completing setup, provide an explicit action to add the first branch.
- Require the owner to provide branch details before creating a branch.
- Never silently create a default branch.

When a Basic or Standard owner selects multiple locations, show the Premium requirement and provide an upgrade link. The backend must enforce the entitlement even if a request is submitted manually.

### Step 3: Configure fulfillment

Present these independent operating choices:

- Customers visit the shop
- Offer pickup from the customer
- Offer delivery back to the customer

The first choice is the normal baseline. Pickup and delivery are optional and are not automatically enabled by a subscription.

When pickup is disabled:

- Customers cannot select `pickup` while placing an order.
- Shop employees cannot create an order with customer pickup.
- Backend validation rejects manually submitted pickup orders.

When delivery is disabled:

- Hide Logistics and Riders navigation.
- Hide the customer `Request Delivery` action.
- Reject delivery requests and shop-created deliveries at the backend.

Pickup and delivery should be stored separately because the current platform already models incoming customer pickup and post-completion delivery as different workflows.

### Step 4: Review and finish

Display a summary containing:

- Active plan
- Main shop location
- Single or multiple locations
- Customer pickup status
- Customer delivery status

Finishing the wizard saves the configuration atomically, records setup completion, and redirects the owner to the dashboard.

## Proposed data model

Add the following nullable fields to `shops`:

| Field | Type | Purpose |
| --- | --- | --- |
| `setup_completed_at` | timestamp | Indicates that onboarding configuration is complete |
| `location_mode` | string/enum | `single` or `multiple` |
| `offers_pickup` | boolean | Enables incoming customer pickup workflows |
| `offers_delivery` | boolean | Enables outgoing customer delivery workflows |

These settings belong to the shop rather than an order because they must survive subscription renewals and upgrades.

Use model casts for the booleans and timestamp. Use named constants or an enum for location mode instead of repeating string literals.

## Backend architecture

All new and materially changed backend flows will follow:

```text
Route -> Form Request -> Controller -> Service -> Repository -> Model / Database
```

### Routes

Add authenticated owner routes for:

- Displaying the setup wizard
- Completing the setup wizard
- Updating operating preferences later from Shop Settings

Add middleware to owner routes that require completed setup. Exclude subscription, payment, logout, notification, agreement, and setup routes to avoid redirect loops.

### Form Requests

Create dedicated requests such as:

- `StoreShopSetupRequest`
- `UpdateShopOperationsSettingsRequest`

Responsibilities:

- Normalize booleans.
- Validate `location_mode`.
- Validate the required shop information.
- Require at least the normal in-shop workflow.
- Perform request-level authorization for the authenticated owner.

Plan entitlement remains a service-level business rule rather than a validation-only rule.

### Controller

Create a thin `ShopSetupController` that:

- Requests wizard view data from the service.
- Passes validated input to the service.
- Returns the Inertia response or redirect.
- Converts known application exceptions into an appropriate response.

It must not query Eloquent, start transactions, or decide plan entitlements.

### Service

Create `ShopSetupService` to:

- Resolve the active subscription through a repository.
- Determine available setup choices.
- Enforce multi-location entitlement.
- Complete setup in a database transaction.
- Coordinate shop and branch repositories when necessary.
- Return presentation-neutral result data.

No branch should be created merely because `location_mode` is `multiple`. Branch creation remains an explicit follow-up use case.

### Repositories

Add focused repository operations for:

- Loading a shop by owner.
- Saving shop setup settings.
- Checking active plan entitlement.
- Checking whether a shop already has branches, riders, or deliveries when changing settings.

Repositories remain transaction-agnostic. Transaction boundaries belong to the service.

### Capability enforcement

Navigation visibility is not security. Enforce capabilities on the server for:

- Branch management routes
- Pickup order submission
- Logistics and rider routes
- Customer delivery requests

Use middleware or policies backed by a focused capability service. Return a clear forbidden or validation response rather than allowing hidden functionality through direct URLs.

### Refactoring touched legacy flows

The current delivery request in `UserOrderController` validates, queries, creates a delivery, and sends a notification directly. If this flow is changed, move it into:

```text
StoreDeliveryRequest
    -> UserOrderController
    -> DeliveryService
    -> DeliveryRepository / ShopOrderRepository
```

Apply the same separation to materially changed logistics controller actions. Do not rewrite unrelated legacy features.

## Frontend changes

### New wizard page

Create a responsive Inertia/Vue wizard with:

- A visible step indicator
- Back and continue controls
- Clear descriptions of operational consequences
- Conditional Premium upgrade explanation
- Server validation error display
- A final review screen
- Protection against accidental duplicate submission

Keep the form state client-side until final submission unless product requirements later call for resumable partial steps.

### Shop navigation

Update the shop sidebar to:

- Show Branch List only for a multi-location shop with entitlement.
- Show Logistics only when pickup or delivery logistics are enabled.
- Continue applying module and platform permission checks.

The sidebar will consume server-provided shop capabilities rather than recreating entitlement rules in Vue.

### Branch-dependent pages

For single-location shops:

- Hide branch filters in employee and attendance pages.
- Hide branch columns where every value would be empty.
- Hide branch assignment inputs.
- Keep stored historical branch values readable if the shop previously used multiple locations.

### Customer order flow

- Always allow the standard walk-in option while the shop is active.
- Show customer pickup only when `offers_pickup` is enabled.
- Show `Request Delivery` only when `offers_delivery` is enabled.
- Pass capability data from backend presenters/resources rather than querying it in Vue.

### Settings

Add an Operations Setup section where the owner can change configuration later.

Safety behavior:

- Disabling branches does not delete branches or employee history.
- Disabling pickup does not change existing orders.
- Disabling delivery does not delete riders or delivery history.
- Existing in-progress work remains viewable and completable.
- New use of the disabled capability is blocked.

Show a confirmation message explaining these effects before disabling a capability with existing data.

## Activation and redirect rules

| Account state | Destination |
| --- | --- |
| No selected plan | Plan selection |
| Application pending | Existing pending dashboard |
| Approved but unpaid | Payment flow |
| Paid and setup incomplete | Shop Setup Wizard |
| Trial active and setup incomplete | Shop Setup Wizard |
| Active and setup complete | Shop dashboard |
| Staff user | Staff dashboard; never owner setup |

The payment success screen should make `Set up your shop` its primary next action.

The setup middleware should also catch owners who leave the payment page and return later.

## Existing shop migration strategy

Preserve current behavior for existing shops during deployment:

1. Add nullable configuration columns.
2. Backfill existing shops as setup-complete.
3. Preserve currently available branch and logistics behavior for them initially.
4. Show a non-blocking dashboard notice asking existing owners to review Operations Setup.
5. Require the wizard only for shops activated after the feature is deployed.

This prevents an update from unexpectedly hiding features or blocking an existing business.

Once an existing owner reviews and saves the settings, the explicit configuration replaces the compatibility defaults.

## Validation and business rules

- Only a shop owner may complete or update shop setup.
- Setup requires an active paid subscription or active trial.
- Basic and Standard cannot save `location_mode = multiple` in the recommended plan model.
- Premium may still select `single`.
- A disabled capability cannot be used through a direct HTTP request.
- Repeating the setup submission must not create duplicate data.
- Plan downgrade from Premium must address an existing multiple-location configuration before completion.
- Plan expiration must not erase setup choices.
- Renewing a subscription must not rerun setup unless a new required setting is introduced.

## Plan upgrade and downgrade behavior

### Upgrade to Premium

- Keep the shop's existing single-location setting.
- Inform the owner that multiple locations are now available.
- Do not automatically switch the shop to multiple-location mode.

### Downgrade from Premium

If the shop currently uses multiple locations, do not silently hide or delete them. Before completing the downgrade, require one of these product decisions:

- Remain on Premium, or
- Select a main location and move to single-location mode while preserving other branches as inactive historical records.

The downgrade rule should be finalized before implementation if plan downgrades are currently supported.

## Test plan

### Feature tests

- Paid owner with incomplete setup is redirected to the wizard.
- Trial owner with incomplete setup is redirected to the wizard.
- Pending and unpaid owners retain their existing flows.
- Completed owners reach the dashboard.
- Staff users are not redirected to owner setup.
- An owner cannot update another shop.
- Basic and Standard cannot submit multiple-location mode.
- Premium can submit single or multiple-location mode.
- Setup submission is idempotent.
- Branch endpoints reject single-location shops.
- Pickup orders are rejected when pickup is disabled.
- Delivery requests are rejected when delivery is disabled.
- Logistics and rider endpoints reject shops without the capability.
- Existing in-progress deliveries remain manageable after delivery is disabled, if that exception is approved.

### Service tests

- Setup completes transactionally.
- Entitlement decisions are correct for every plan and trial.
- A failed operation does not partially save settings.
- Upgrade preserves current preferences.
- Disabling capabilities preserves historical data.

### Repository tests

- Shop setup fields persist and cast correctly.
- Active subscription lookup selects the correct order.
- Existing branch/delivery checks are correctly scoped to the shop.

### Frontend checks

- Wizard navigation and conditional fields work at mobile and desktop widths.
- Validation errors appear on the correct step.
- Branch navigation and fields follow server-provided capabilities.
- Pickup and delivery choices are absent when disabled.
- Loading, disabled, success, and error states are clear.

### Commands before handoff

- Run targeted Laravel feature and unit tests.
- Run the full relevant PHP test suite.
- Run Laravel Pint on changed PHP files.
- Run frontend type checking and linting.
- Run the production frontend build.

## Suggested delivery phases

### Phase 1: Configuration foundation

- Add shop setup columns and casts.
- Add repository operations, service rules, requests, controller, and routes.
- Add activation redirect middleware.
- Add feature and service tests.

### Phase 2: Wizard interface

- Build the four-step wizard.
- Connect it to the setup endpoint.
- Update payment/trial completion actions.
- Add responsive and validation testing.

### Phase 3: Capability enforcement

- Gate branch routes and UI.
- Gate pickup choices and submissions.
- Gate delivery/logistics routes and UI.
- Refactor touched delivery logic into the required layers.

### Phase 4: Settings and existing shops

- Add Operations Setup to Shop Settings.
- Add safe disable confirmations.
- Backfill existing shops without disrupting current behavior.
- Add the existing-owner review notice.

### Phase 5: Verification and rollout

- Run the complete test and build checks.
- Verify Trial, Basic, ndard, and Premium manually.
- Verify upgrades, renewals, expiration, and existing-shop compatibility.
- Deploy with monitoring for redirect loops and forbidden capability requests.

## Review decisions required before execution

1. Confirm whether multiple locations should be Premium-only. This plan recommends yes because the current Premium markeStating describes it as the multi-branch plan.
2. Confirm whether Trial should allow testing multiple locations. This plan recommends yes.
3. Confirm whether Basic and Standard should both be allowed to enable pickup and delivery. This plan recommends yes because all current plans include Operations.
4. Confirm whether an existing in-progress delivery remains editable after delivery is disabled. This plan recommends allowing completion while blocking new deliveries.
5. Confirm the downgrade behavior for a Premium multi-location shop.

Implementation should begin only after these decisions and the plan are approved.
