---
objective: "The obsolete Agents Cours SPA page and all frontend code used only by it are removed without affecting the separately used Symfony and MCP agent-course workflows."
status: implemented
---

# Plan: Remove the Agents Cours SPA page

## Overview

| Field      | Value |
| ---------- | ----- |
| **Goal**   | Remove `/spa/agents-cours`, its navigation entries, and its page-only frontend implementation. |
| **Source** | User request in the current Codex task. |

## Phases

| #   | Phase | File |
| --- | ----- | ---- |
| 1   | Remove the obsolete SPA surface | [`phase-1.md`](./phase-1.md) |

## Decisions

| Decision | Why |
| -------- | --- |
| Keep Symfony agent-course and MCP code | Those workflows are independently consumed outside the removed SPA page. |
