# Gestion de projet

> Formalisé a posteriori le 2026-11-02. Aucun outil de suivi (tableau, sprints, tickets) n'a été tenu pendant le développement : la chronologie et les indicateurs ci-dessous sont recalculés depuis Git et GitHub Actions, avec les commandes archivées dans [`indicateurs-git.txt`](../VAE/evidence/2026-11-02-conception/indicateurs-git.txt). Le backlog est daté du jour de sa rédaction. <Status is="local" />

## Chronologie

| Période | Projet et dépôt | Faits vérifiables |
|---|---|---|
| 2025-06-05 → 2026-10-19 | DevDoc, `cundovar/vue-symfony`, `main` | 167 commits hors fusions, dont 33 avec mention d'un agent IA ; historique contenu dans `main2` |
| 2026-10-15 | Médiateur, `cundovar/mediateur-numerique-formation` | import du socle DevDoc (`ee88484`) ; 52 commits dans la journée |
| 2026-10-16 → 2026-10-20 | Médiateur | compte apprenant, captures, service Netcup, connecteur ; 32 commits |
| 2026-10-30 | Médiateur | dossier VAE et documentation VitePress ; fusion de la PR #48 (`c87941f`), dernier commit repris |
| 2026-10-31 | `vue-symfony`, `main2` | 2 commits (`2b7de33`, `90623fd`), remplacés ou repris ensuite |
| 2026-11-02 | `vue-symfony`, `main2` | 8 commits : déploiement d'Inclusion (`d148fd6`), base partagée (`deb7543`), reprise de Médiateur (`95d47ac`), lien des images (`8ed912f`), retrait du MCP (`a379bfe`), documentation (`30da5e5`) |
| 2026-11-02 | dossier VAE | plan de préparation en six phases, cadrage et conception (ce document) |

## Méthode observée

Constatée dans les dépôts, pas déclarée a priori :

- **Plans par phase** : chaque chantier important a un plan et des fichiers de phase (`aidd_docs/tasks/2026_10/`, hérités de Médiateur), avec tâches et critères d'acceptation.
- **Commits typés** (Conventional Commits) : sur les 86 commits de Médiateur, 46 `feat`, 17 `fix`, 10 `docs`, 4 `ci`, 4 `style`, 2 `test`.
- **Branches et pull requests** : 48 PR fusionnées dans Médiateur. Sur `main2`, les 10 commits sont poussés directement, sans PR.
- **Intégration continue bloquante** : le build et le déploiement dépendent des jobs PHP, Migrations, Front et Documentation (`.github/workflows/ci.yml`).
- **Agents IA encadrés** : consignes communes (`AGENTS.md`), hooks qui bloquent les fichiers sensibles et rappellent `make check` ([harness](harness/index.md)), mention `Co-Authored-By` dans les commits assistés.

## Outils

| Besoin | Outil |
|---|---|
| Versions, revue | Git, GitHub (PR sur Médiateur) |
| Intégration et déploiement | GitHub Actions, rsync vers OVH |
| Environnement | Docker Compose, `Makefile` |
| Planification | fichiers de plan Markdown (`aidd_docs/tasks/`) |
| Assistance au développement | Claude Code, Codex, avec le harness du dépôt |
| Documentation | VitePress (`docs/`) |

## Indicateurs

| Indicateur | Définition | Valeur au 2026-11-02 |
|---|---|---|
| Commits propres à Inclusion | `git log --no-merges ec173dc..30da5e5` | 10 (2 le 2026-10-31, 8 le 2026-11-02) |
| Part des commits avec mention d'un agent IA | commits hors fusions portant une ligne `Co-Authored-By`, divisés par le total | Inclusion : 10 / 10 ; Médiateur : 47 / 86 ; DevDoc : 33 / 167 |
| Commits dont l'auteur Git est un agent | auteur « Claude » | Médiateur : 15 ; Inclusion et DevDoc : 0 |
| Taux de réussite de la CI de `main2` | runs `success` / runs terminés (`gh run list --branch main2`) | 8 / 8 |
| Délai d'une correction en production | du commit au job `deploy` réussi | `8ed912f` : commit à 12 h 18, déploiement terminé dans le run [41436221028](https://github.com/cundovar/vue-symfony/actions/runs/41436221028) |

Lecture : une mention `Co-Authored-By` signale une assistance ; son absence ne prouve pas une écriture sans assistance. Aucun indicateur ne mesure la part personnelle du candidat.

## Backlog au 2026-11-02

Priorités : P1 nécessaire au dossier, P2 améliore la preuve, P3 amélioration du produit.

| # | Élément | Priorité | Phase du plan VAE |
|---|---|---|---|
| B1 | Plan de tests et jeu d'essai fixés avant les nouveaux essais | P1 | 4 |
| B2 | Installation isolée, `make check` et `make docs-build` archivés | P1 | 4 |
| B3 | Essais mobile, clavier, connexion faible et hors ligne | P1 | 4 |
| B4 | Scénario de bout en bout exécutable (`@playwright/test` absent) | P2 | 4 |
| B5 | Corriger `AGENTS.md`, `README.md` et les pages `docs/` qui décrivent encore le connecteur MCP, OAuth ou la capture comme présents | P2 | hors plan |
| B6 | Projet Docker Compose encore nommé `mediateur_numerique` dans ce dépôt | P2 | 4 |
| B7 | Bouton sans libellé du formulaire QCM ([écrans](conception/maquettes.md#observations-à-vérifier)) | P3 | 4 (constat), puis correction séparée |
| B8 | Veille sur les vulnérabilités de sécurité, exigée par le RE | P1 | 5 |
| B9 | Rôle du candidat et part des agents IA, confirmés par lui | P1 | 5 |
