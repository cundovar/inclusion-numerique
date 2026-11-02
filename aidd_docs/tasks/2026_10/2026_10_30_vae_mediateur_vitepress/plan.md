---
objective: "Le projet Médiateur numérique dispose d'un dossier VAE traçable, d'une documentation VitePress propre au projet et d'un harness Claude Code/Codex qui maintient les preuves sans réutiliser celles de DevDoc."
status: done
---

# Plan: Préparer la VAE sur le projet Médiateur numérique

## Overview

| Field      | Value |
| ---------- | ----- |
| **Goal**   | Adapter au projet Médiateur la méthode VAE, la documentation VitePress et les garde-fous éprouvés dans DevDoc. |
| **Source** | Demande utilisateur du 2026-10-30 : le projet Médiateur remplace DevDoc comme projet présenté pour la VAE. |

## Phases

| #   | Phase | File |
| --- | ----- | ---- |
| 1 | Cadrer le référentiel et le dossier VAE | [`phase-1.md`](./phase-1.md) |
| 2 | Installer le site VitePress indépendant | [`phase-2.md`](./phase-2.md) |
| 3 | Documenter Médiateur et enregistrer ses preuves | [`phase-3.md`](./phase-3.md) |
| 4 | Synchroniser le harness avec la documentation VAE | [`phase-4.md`](./phase-4.md) |
| 5 | Intégrer la documentation à la CI et préparer sa publication | [`phase-5.md`](./phase-5.md) |

## Decisions

| Decision | Why |
| -------- | --- |
| DevDoc reste une référence de structure uniquement. | Ses métriques, compétences couvertes, tests et validations ne prouvent rien pour Médiateur. |
| Le référentiel officiel exact est requis avant de remplir la matrice de compétences. | Le changement de programme VAE interdit de conserver implicitement le référentiel CDA de DevDoc. |
| `VAE/` contient la synthèse et `docs/` les explications techniques. | Cette séparation garde le dossier VAE lisible et évite la duplication des documents. |
| Chaque affirmation porte un niveau de preuve explicite. | Un fichier présent, un test local, une CI réussie et une vérification en production sont quatre preuves différentes. |
| VitePress appartient au dépôt Médiateur. | Le projet, sa base, son déploiement et sa documentation restent indépendants de DevDoc. |
