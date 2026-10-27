---
name: flat-form-layout
description: Use when building or restyling a create/edit form or settings page. Gives a flat, divider-based layout with one summary card, instead of a card around every section.
---

# Flat form layout

## 1. When to use it

- Create and edit pages with several groups of fields (service create, order edit, and similar).
- Settings pages with a list of options or toggles.
- Not for dashboards, lists, or pages that mix charts, tables, and forms. Those can keep cards.

## 2. Core principle

- Use a card only when it groups something that stands apart from the rest of the page. A card around every section makes everything look equally important.
- On a single-task form, the fields should be the only boxed things on the page.

## 3. Layout rules

**Page structure**

- Page header: breadcrumb, title, subtitle, back button.
- Below it, a two-column area: the form on the left, one sticky side panel on the right (`lg:grid-cols-[minmax(0,1fr)_300px]`).

**Form sections**

- The section wrapper has no border, background, shadow, rounded corners, or inner padding.
- Each section has a heading, an optional one-line description, then its fields.
- Sections are separated by a 1px divider on top (`border-t`, the existing border colour) and about 32px of space above and below (`space-y-8` on the column, `pt-8` on the section). The first section has no divider.
- Gap between fields inside a section: about 20px (`gap-y-5`).

**Side panel**

- It is the only card on the page, and it stays visible while scrolling (`lg:sticky lg:top-4`).
- Order inside it: the summary or preview, a divider, the save status text, then the buttons (primary action first, then secondary).
- Never use two separate cards in the side panel.

**Responsive**

- The side panel stacks below the form on narrow screens, and field rows wrap to one column.

## 4. What keeps its outline or box

- Form fields (inputs, selects, textareas).
- Option controls that act like buttons: radio-style choices, segmented toggles, status buttons. A selected option gets a blue outline and a light blue tint.
- Info and warning banners (for example "this order is locked"): a tinted box, no heavy border.
- Rows with a switch (settings and automation lists): flat rows with a bottom divider. A row that is switched on gets a light tint of about 8% of the primary colour.

## 5. Form behaviour patterns

- Required fields have a visible label and a red asterisk.
- Help text sits under the field in a smaller, muted colour.
- The save button is disabled until something changes. A short status line near it says "No changes yet", "You have unsaved changes", or "Changes saved".
- If a form becomes read-only after a status change, show a banner explaining why and disable all fields. Do not hide them.
- Modals are for adding something small, such as a new category. They close with Cancel, an X button, or a click on the backdrop.

## 6. Implementation rules

- Use the project's existing design tokens, utility classes, and components. Do not add libraries or hard-coded colours.
- If a shared Card component exists, do not change it. Use a flat wrapper or a variant for these pages.
- Keep accessibility: real labels for fields, headings in order, visible focus outlines, aria attributes on custom controls, and text contrast of at least 4.5:1.
- Restyling is markup and classes only. Do not touch validation, routes, or form submission.

Reference implementations: `resources/js/pages/shop/operations/services/Create.vue` and `Edit.vue`.

## 7. Checklist before finishing

- [ ] No section has its own border or background, except the side panel and banners.
- [ ] Sections are separated by dividers and spacing only.
- [ ] The side panel is one card.
- [ ] The page works at narrow widths.
- [ ] Nothing about validation, routes, or form submission changed.

## Before and after

**Before.** Every group of fields (service type, details, pricing, supplies) sits in its own rounded, bordered, shadowed card with inner padding, and the side panel has two more cards (preview and checklist). The page is a stack of equal boxes, so nothing stands out, and the inputs inside look boxed twice.

**After.** The sections run straight down the page on the plain background, each with a heading and a thin divider above it, so the inputs are the only boxed things. The pricing options and any banners keep their outlines or tint. On the right, a single sticky card holds the preview, a divider, the save status, and the Save and Cancel buttons.
