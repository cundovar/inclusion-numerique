# Plans de tâches (AIDD)

Plans rédigés avant l'implémentation, avec le framework AIDD (plan → phases → revue), entre le 2026-08-31 et le 2026-09-07. Ils sont versionnés tels qu'ils ont été écrits. Le champ `status` de leur en-tête n'a pas été tenu à jour après la livraison : le statut réel est indiqué ci-dessous, d'après les PR fusionnées.

| Date | Dossier | Type | Objet | Livraison | Statut réel |
|---|---|---|---|---|---|
| 2026-08-31 | [`2026_08/2026_08_31_audit/`](2026_08/2026_08_31_audit/architecture.md) | Audit | Audit des serveurs MCP `agents-cours` (dépôt séparé) : 2 avertissements, 1 mineur | Dépôt MCP, hors de ce dépôt | Non vérifiable ici |
| 2026-08-31 | [`2026_08/2026_08_31_mcp_content_management/`](2026_08/2026_08_31_mcp_content_management/plan.md) | Plan, 6 phases | Gestion des cours par les agents IA via l'API Symfony, boucle de vérification limitée à 3 essais, publication humaine | [PR #30](https://github.com/cundovar/vue-symfony/pull/30) (partie Symfony) | Partie Symfony livrée (`ApiAgentCourseGenerationController`, `AgentCourseGeneration`) ; partie MCP dans un dépôt séparé |
| 2026-09-01 | [`2026_09/2026_09_01_devdoc_verified_course_orchestration/`](2026_09/2026_09_01_devdoc_verified_course_orchestration/review.md) | Revue | Revue du code, du fonctionnel et de la pertinence des branches MCP : verdict « bloqué », 4 critiques, 15 avertissements | PR #30 fusionnée le même jour | Traitement des constats non tracé |
| 2026-09-06 | [`2026_09/2026_09_06_remove_agents_cours_page/`](2026_09/2026_09_06_remove_agents_cours_page/plan.md) | Plan, 1 phase | Suppression de la page SPA obsolète `/spa/agents-cours` | [PR #31](https://github.com/cundovar/vue-symfony/pull/31) | Terminé |
| 2026-09-07 | [`2026_09_07_devdoc_vite_only_assets/`](2026_09_07_devdoc_vite_only_assets/plan.md) | Plan, 5 phases | Suppression de Webpack Encore, Vite seul pipeline d'assets | [PR #32](https://github.com/cundovar/vue-symfony/pull/32), [PR #34](https://github.com/cundovar/vue-symfony/pull/34) | Terminé |

Depuis le 2026-10-12, les tâches sont suivies dans les PR et dans [`docs/gestion-de-projet.md`](../../docs/gestion-de-projet.md).
