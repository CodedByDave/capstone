---
name: task-workflow
description: Standard five-step workflow (investigate, plan, implement, verify, review and report) for bug fixes, features, and changes in this repository. Use when starting or handing off any code change.
---

# Standard Task Workflow

Follow these five steps, in order, for every bug fix, feature, or change.

## 1. Investigate

- Read the relevant files and understand the surrounding architecture before changing anything.
- Reproduce the issue (or confirm the current behavior) before proposing a fix.

## 2. Plan

- Identify the root cause, the affected files, the risks, and the intended fix.
- Keep the planned scope controlled; do not include unrelated refactors.

## 3. Implement

- Make small, appropriate changes that follow the architecture and standards in `AGENTS.md`.

## 4. Verify

- Run the test suite; all tests must pass before handoff.
- Confirm the tests also pass in the CI/CD pipeline (GitHub Actions) after pushing.
- Run formatting, static analysis, and frontend checks relevant to the change.

## 5. Review and report

- Summarize the changes made.
- Report test results faithfully, including any failures or skipped checks.
- State any remaining risks or follow-up work.
