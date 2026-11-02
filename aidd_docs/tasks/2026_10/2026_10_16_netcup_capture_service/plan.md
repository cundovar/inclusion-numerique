---
objective: "The OVH application delegates authenticated capture requests to an isolated Playwright service deployed on Netcup, while it keeps ownership of the resulting media."
status: done
---

# Plan: Netcup capture service

## Overview

| Field      | Value                   |
| ---------- | ----------------------- |
| **Goal**   | Run automated captures in an isolated Netcup container instead of the OVH shared host. |
| **Source** | User request: "sortire juste un service de capture séparé disposant de Node, Chromium et Playwright. dans netcup". |

## Phases

| #   | Phase        | File                         |
| --- | ------------ | ---------------------------- |
| 1   | Capture HTTP service | [`phase-1.md`](./phase-1.md) |
| 2   | Symfony remote runner | [`phase-2.md`](./phase-2.md) |
| 3   | Netcup operation | [`phase-3.md`](./phase-3.md) |

## Decisions

| Decision   | Why   |
| ---------- | ----- |
| Keep the media upload and database write on OVH. | The capture worker remains stateless and never receives application credentials or the course database. |
| Use HTTPS and a shared bearer token between OVH and Netcup. | The endpoint captures arbitrary public URLs and must not be publicly callable. |
| Return the JPEG as base64 in the authenticated response. | It avoids a public temporary-file URL and lets the existing back-office storage flow remain authoritative. |
