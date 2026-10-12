---
status: done
---

# Instruction: Remove the obsolete Agents Cours SPA surface

## Architecture projection

> Tree of the final files. ✅ create · ✏️ modify · ❌ delete

```txt
front/src/
├── components/
│   ├── features/agents/
│   │   ├── ❌ AgentsCoursForm.vue
│   │   └── ❌ AgentsCoursRevisionForm.vue
│   ├── layout/
│   │   ├── ✏️ AppHeader.vue
│   │   └── ✏️ AppHeaderMobile.vue
│   └── navigation/
│       └── ✏️ MenuLinks.vue
├── router/
│   └── ✏️ index.js
├── services/
│   └── ❌ agentsCoursService.js
└── views/
    └── ❌ AgentsCours.vue
```

## User Journey

```mermaid
flowchart TD
  A[Open the SPA] --> B[Inspect desktop, mobile, and drawer navigation]
  B --> C[No Agents Cours entry is displayed]
  C --> D[Navigate directly to agents-cours]
  D --> E[No Agents Cours page is resolved]
```

## Test Scope

```mermaid
---
title: Test scope
---
journey
  section Setup
    Build the Vue application => production bundle is ready: 5: cli
  section Happy path
    Search navigation and routes => no Agents Cours entry or route remains: 5: cli
  section Edge case - direct legacy URL
    Open the old URL => no Agents Cours page is rendered: 1: browser
```

## Tasks to do

### `1)` Remove route and navigation access

> Make the obsolete page unreachable from the SPA and remove every navigation entry that points to it.

1. Remove the lazy view import and route definition.
2. Remove desktop, mobile, and drawer navigation links.

### `2)` Remove page-only frontend code

> Delete the view, both forms, and the Axios service that have no remaining consumers.

1. Delete the obsolete view and forms.
2. Delete the exclusively consumed service.
3. Confirm no source reference remains.

## Test acceptance criteria

| Task | Acceptance criteria |
| ---- | ------------------- |
| 1 | The SPA exposes no navigation entry or router record for Agents Cours. |
| 2 | The production Vue build succeeds and no Agents Cours frontend source reference remains. |
| 2 | Symfony and MCP agent-course code remains unchanged. |
