---
objective: "The project keeps a single asset pipeline (Vite) and removes every trace of the legacy Webpack Encore tooling used only to build a dead EasyAdmin entry, without affecting the EasyAdmin backend served by its own bundle or the Vue.js SPA build."
status: in-progress
---

# Plan: Remove Webpack Encore, keep Vite only

## Overview

| Field      | Value |
| ---------- | ----- |
| **Goal**   | Remove Webpack Encore (root `package.json`, `webpack.config.js`, `assets/easyadmin.js`), the dead build outputs, the `symfony/webpack-encore-bundle` dependency, and every script/CI/doc reference, so Vite is the only asset pipeline. |
| **Source** | User request in the current Codex task. |

## Phases

| #   | Phase | File |
| --- | ----- | ---- |
| 1   | Remove Webpack Encore Node tooling | [`phase-1.md`](./phase-1.md) |
| 2   | Remove committed build artifacts | [`phase-2.md`](./phase-2.md) |
| 3   | Remove `symfony/webpack-encore-bundle` | [`phase-3.md`](./phase-3.md) |
| 4   | Update build scripts and documentation | [`phase-4.md`](./phase-4.md) |
| 5   | Update CI/CD workflow | [`phase-5.md`](./phase-5.md) |

## Decisions

| Decision | Why |
| -------- | --- |
| Keep EasyAdmin backend and its bundle assets (`public/bundles/easyadmin/`) | EasyAdmin 4 serves its own CSS/JS from the bundle; no Twig template references `encore_entry_*`. |
| Keep the Vue.js SPA built by Vite (`front/`, `public/build/.vite/`, `public/spa/`) | This is the sole active asset pipeline. |
| Keep `symfony/asset` | Required by `pentatrion/vite-bundle` for asset versioning. |
