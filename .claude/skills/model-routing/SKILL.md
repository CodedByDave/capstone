---
name: model-routing
description: Choose which Claude model handles a task. Sonnet 5.5 for planning, reasoning, small tasks, and debugging; Opus 5.5 (via subagent) for heavy-duty work such as implementing a large feature. Use at the start of any non-trivial task to decide whether to stay inline or delegate.
---

# Model Routing

The main session runs on **Sonnet 5.5** (`claude-sonnet-5-5`). A skill cannot change the session model, so heavy work is delegated to a subagent with `model: "opus"` (Opus 5.5, `claude-opus-5-5`).

## Routing table

| Task type | Model | How |
|---|---|---|
| Planning, design, trade-off reasoning | Sonnet 5.5 | Inline in the main session |
| Minimal tasks (one-line fixes, small edits, renames, copy/style tweaks, config changes) | Sonnet 5.5 | Inline |
| Debugging (reproduce, trace, fix a bug, failing tests) | Sonnet 5.5 | Inline |
| Code review, running tests/formatters, Q&A about the code | Sonnet 5.5 | Inline |
| Heavy duty: implementing a big feature, multi-file/multi-layer changes, large refactors, migrations plus backend plus frontend | Opus 5.5 | `Agent` tool with `model: "opus"` |

## Heavy-duty criteria

Delegate to Opus 5.5 when **two or more** apply:

- Touches 5+ files or more than one architectural layer (Route, Form Request, Controller, Service, Repository, Model, Vue page).
- Adds a new feature, module, or workflow rather than modifying one behavior.
- Needs a new migration plus backend plus frontend plus tests.
- Is a large refactor or cross-cutting change (auth, RBAC, payments, per-shop data isolation).

If none or one apply, stay on Sonnet 5.5 inline.

## Workflow for heavy-duty tasks

1. **Plan on Sonnet 5.5** inline: read the relevant code, decide the approach, list files and layers, and get user approval if the task is large (see `feature-dev`).
2. **Delegate implementation to Opus 5.5**: call `Agent` with `model: "opus"` and a self-contained prompt:
   - The goal and the approved plan.
   - Exact files and layers to change.
   - Repo rules from `AGENTS.md` (layered architecture, no sequential IDs in sensitive URLs, EasyDataTable and KPI standards, icon preservation).
   - Required tests and checks to run (Pint, Prettier, relevant PHPUnit/Pest tests).
   - What to report back: files changed, tests run and results, open issues.
3. **Verify on Sonnet 5.5**: review the subagent's diff, run the tests and formatters, and fix small problems inline.
4. **Escalate again only if needed**: if verification reveals a large gap, send a follow-up to the same agent with `SendMessage` instead of starting a new one.

## Rules

- Do not delegate small work to Opus; it costs more and loses context.
- Do not delegate debugging by default. If a bug turns out to need a large rewrite, treat it as heavy duty from that point.
- A subagent starts cold, so put all needed context in its prompt.
- Always report outcomes faithfully: say which model did what, and whether tests passed.
- Combine with `debug-workflow` for bug fixes and `feature-dev` for features; this skill only decides who does the work.
