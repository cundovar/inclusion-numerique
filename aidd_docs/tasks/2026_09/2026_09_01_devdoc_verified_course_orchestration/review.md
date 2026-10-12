# Review: DevDoc verified course orchestration

- **Verdict**: blocked
- **Diff**: `origin/main...feat/mcp-content-management`, `origin/main...feat/devdoc-course-orchestration`, `origin/main...feat/devdoc-course-workflow`
- **Axes run**: code, functional, relevancy
- **Date**: 2026_09_01
- **Findings**: 4 critical, 15 warning, 0 minor

## Phases

### Phase 1 — Arborescence Symfony

- [x] Les routes administratives des supermenus et positions existent, et les catégories acceptent les relations demandées — `src/Controller/Api/AdminCrud/ApiCategoryController.php:197`
- [ ] La préparation idempotente ne couvre pas les positions et peut créer deux menus identiques demandés dans le même appel — `MCP/agents-cours/src/domain/use-cases/OrchestrerCours.js:20`
- [x] Un menu `Cours` est utilisé quand aucun menu n'est fourni — `MCP/agents-cours/src/domain/use-cases/OrchestrerCours.js:21`
- [ ] La finalisation ne refuse pas un `menuId` appartenant à une autre catégorie ou à un autre niveau — `src/Controller/Api/AdminCrud/ApiAgentCourseGenerationController.php:95`

### Phase 2 — Journal des générations

- [x] Le journal stocke l'identité du lot, l'état, les tentatives, le candidat, les rapports, les erreurs et le cours final — `src/Entity/AgentCourseGeneration.php:14`
- [x] Les cinq opérations API de création, lecture, mise à jour, finalisation et échec existent — `src/Controller/Api/AdminCrud/ApiAgentCourseGenerationController.php:32`
- [x] La finalisation exige un rapport positif et crée un cours visible dans une transaction — `src/Controller/Api/AdminCrud/ApiAgentCourseGenerationController.php:75`
- [ ] L'appel d'échec réenregistre le dernier rapport et incrémente la tentative une quatrième fois après trois refus — `src/Controller/Api/AdminCrud/ApiAgentCourseGenerationController.php:123`

### Phase 3 — Médiathèque

- [x] Les médias possèdent le stockage local et les métadonnées prévues — `src/Entity/CourseMedia.php:13`
- [x] Le téléversement contrôle taille, MIME, dimensions et texte alternatif — `src/Controller/Api/AdminCrud/ApiCourseMediaController.php:33`
- [ ] L'API ne fournit ni consultation individuelle ni recherche de média — `src/Controller/Api/AdminCrud/ApiCourseMediaController.php:27`
- [x] Les URL Symfony sont insérées et les médias temporaires sont supprimés lors de l'échec définitif — `src/Controller/Api/AdminCrud/ApiAgentCourseGenerationController.php:124`

### Phase 4 — Créateur et vérificateur

- [x] DeepSeek produit séparément le contenu, les objectifs et le plan d'illustrations sans créer de brouillon — `MCP/agents-cours/src/domain/use-cases/OrchestrerCours.js:33`
- [x] Le vérificateur OpenAI utilise une invocation séparée et demande un résultat JSON — `MCP/agents-cours/src/infrastructure/ia/OpenAIVerificationService.js:9`
- [ ] Le vérificateur ne reçoit aucune image réelle : il reçoit seulement du JSON contenant les métadonnées — `MCP/agents-cours/src/infrastructure/ia/OpenAIVerificationService.js:17`
- [ ] Les contrôles déterministes ne valident pas le HTML, les liens, les URL externes ni les balises dangereuses au-delà de `script` et `on*` — `MCP/agents-cours/src/domain/services/DeterministicCourseValidator.js:5`
- [ ] La correction régénère toujours le HTML et ne sait pas remplacer une illustration refusée — `MCP/agents-cours/src/domain/use-cases/OrchestrerCours.js:63`

### Phase 5 — Outils MCP et HTTP

- [x] Les principaux outils d'orchestration, de vérification, de finalisation et d'échec sont exposés — `MCP/agents-cours/src/interfaces/mcp/CreateurMCPServer.js:28`
- [ ] Plusieurs outils historiques renvoient encore du texte avec emojis au lieu de JSON stable, et `creer_cours` annonce une création qu'il n'effectue plus — `MCP/agents-cours/src/interfaces/mcp/CreateurMCPServer.js:79`
- [x] L'interface HTTP protège toutes les routes hors `/health` avec un jeton Bearer comparé en temps constant — `MCP/agents-cours/src/interfaces/http/server.js:45`
- [ ] `lister_arborescence` renvoie quatre listes plates et limite les cours aux cours IA au lieu de l'arbre complet — `MCP/agents-cours/src/interfaces/mcp/CreateurMCPServer.js:299`

### Phase 6 — Tests MCP et Symfony

- [ ] Les tests d'idempotence, relations incohérentes, médias, rapports, finalisation et authentification prévus ne sont pas présents — `tests/Entity/AgentCourseGenerationTest.php:11`
- [x] Deux tests MCP simulés couvrent le menu par défaut et le rejet d'un script — `MCP/agents-cours/test/orchestration/OrchestrerCours.test.js:5`
- [ ] Aucun test ne couvre les rapports mal formés, zéro à trois images ou un fournisseur IA contrôlé — `MCP/agents-cours/test/orchestration/OrchestrerCours.test.js:5`

### Phase 7 — Connexion privée n8n

- [ ] Tailscale n'est pas installé sur Netcup et Tailscale Serve n'est pas configuré — `MCP/agents-cours/docs/ORCHESTRATION.md:1`
- [x] Le workflow ne contient pas les clés OpenAI ou DeepSeek et utilise un jeton MCP dédié — `automation/workflows/50-devdoc-course-batch.json:32`
- [ ] La nouvelle clé API n8n et les appels `/health` puis Symfony depuis le conteneur ne sont pas validés — `automation/workflows/README.md:18`

### Phase 8 — Entrée du lot n8n

- [x] Le workflow est inactif, valide les champs essentiels et impose des `externalId` uniques — `automation/workflows/50-devdoc-course-batch.json:24`
- [x] La formation est préparée une seule fois avant la division en cours — `automation/workflows/50-devdoc-course-batch.json:164`
- [ ] Le webhook n'a aucune authentification et aucune limite explicite ne borne le traitement simultané à deux cours — `automation/workflows/50-devdoc-course-batch.json:8`

### Phase 9 — Orchestration d'un cours n8n

- [x] La génération, les illustrations, la vérification, la correction et la finalisation sont chaînées avec trois vérifications maximum — `automation/workflows/50-devdoc-course-batch.json:169`
- [x] Le troisième refus appelle l'échec Symfony, qui nettoie les médias temporaires — `automation/workflows/50-devdoc-course-batch.json:175`
- [ ] Aucun appel HTTP ne configure trois reprises réseau avec attente progressive — `automation/workflows/50-devdoc-course-batch.json:32`
- [ ] Une erreur portant sur une image est envoyée au correcteur de texte et la même image est revérifiée — `automation/workflows/50-devdoc-course-batch.json:137`

### Phase 10 — Reprises, observation et activation n8n

- [x] La clé `(batchId, externalId)` est réutilisée et un cours finalisé est ignoré — `automation/workflows/50-devdoc-course-batch.json:56`
- [ ] Une génération `generating`, `verifying` ou `failed` repart du début au lieu de reprendre à la dernière étape Symfony — `automation/workflows/50-devdoc-course-batch.json:64`
- [ ] Aucun bilan final du lot n'est construit ou renvoyé — `automation/workflows/50-devdoc-course-batch.json:160`
- [ ] Aucun workflow d'erreur commun, journal nettoyé ou batterie de scénarios n'est fourni — `automation/workflows/50-devdoc-course-batch.json:1`
- [ ] La PR n8n ne laisse pas les changements WordPress hors du périmètre : elle contient 34 commits et 1 230 fichiers depuis `main` — `PR #2 / merge-base c3c5d8c`

## Findings

| Sev | Kind | Phase | Location | Issue | Fix |
| --- | ---- | ----- | -------- | ----- | --- |
| 🔴 critical | conform | 10 | `PR #2 / merge-base c3c5d8c` | La branche n8n n'a pas été créée depuis `main`; la PR contient 1 230 fichiers, 34 commits et 166 093 suppressions hors DevDoc. | Recréer la branche depuis `origin/main`, puis cherry-pick uniquement `8c7484e` et `7723034`. |
| 🔴 critical | functional | 4 | `MCP/agents-cours/src/infrastructure/ia/OpenAIVerificationService.js:17` | Le vérificateur annoncé comme multimodal ne voit aucune image; il ne peut contrôler ni correspondance ni lisibilité. | Fournir les images au modèle comme entrées vision, idéalement en `data:` privé après lecture contrôlée du média Symfony. |
| 🔴 critical | code | 4 | `MCP/agents-cours/src/domain/services/DeterministicCourseValidator.js:5` | Du HTML publié peut contenir `javascript:` dans un lien, `iframe`, `object`, `embed` ou des URL externes non autorisées. | Analyser le DOM avec une liste blanche de balises et d'attributs, puis valider chaque URL et protocole avant l'appel IA. |
| 🔴 critical | functional | 8 | `automation/workflows/50-devdoc-course-batch.json:8` | Le webhook coûteux n'a pas d'authentification et peut être déclenché par tout client qui atteint n8n. | Activer `headerAuth` avec un credential dédié avant tout import ou activation. |
| 🟡 warning | functional | 1 | `MCP/agents-cours/src/domain/use-cases/OrchestrerCours.js:20` | `preparerFormation` ignore les positions et ne met pas à jour la liste locale après une création, donc une demande répétée dans le même lot peut dupliquer un menu. | Résoudre/créer les positions et indexer les menus créés au fur et à mesure avec une clé normalisée. |
| 🟡 warning | functional | 1 | `src/Controller/Api/AdminCrud/ApiAgentCourseGenerationController.php:95` | La finalisation accepte un `menuId` sans vérifier qu'il appartient à la catégorie et au niveau demandés. | Faire valider cette cohérence par le cas d'usage Symfony avant de créer la page et le cours. |
| 🟡 warning | code | 2 | `src/Controller/Api/AdminCrud/ApiAgentCourseGenerationController.php:123` | `fail()` transmet une seconde fois le rapport à `update()`, ce qui incrémente le compteur de trois à quatre. | Séparer l'enregistrement d'une vérification de la transition terminale d'échec. |
| 🟡 warning | functional | 3 | `src/Controller/Api/AdminCrud/ApiCourseMediaController.php:27` | La médiathèque ne permet ni recherche ni lecture individuelle comme prévu. | Ajouter les routes de consultation et une recherche bornée sur métadonnées. |
| 🟡 warning | code | 3 | `MCP/agents-cours/src/domain/use-cases/OrchestrerCours.js:54` | Si le HTML ne finit pas exactement par `</main>`, les médias sont stockés mais jamais insérés, sans erreur. | Vérifier le remplacement, utiliser un parseur HTML et refuser un média non référencé. |
| 🟡 warning | functional | 4 | `MCP/agents-cours/src/domain/use-cases/OrchestrerCours.js:63` | Les refus d'illustration déclenchent une correction de texte uniquement. | Router les problèmes par type et régénérer l'image, l'alt ou le texte concerné seulement. |
| 🟡 warning | code | 4 | `MCP/agents-cours/src/infrastructure/ia/OpenAIVerificationService.js:20` | Le schéma du rapport n'est que partiellement vérifié et un rapport `approved: true` peut contenir des problèmes bloquants ou des objets incomplets. | Valider tout le schéma et forcer `approved=false` dès qu'un problème bloquant existe. |
| 🟡 warning | functional | 5 | `MCP/agents-cours/src/interfaces/mcp/CreateurMCPServer.js:299` | `lister_arborescence` n'assemble pas l'arbre et omet les cours non IA. | Construire la hiérarchie complète SuperMenu → Catégorie → Menu → Page → Cours. |
| 🟡 warning | conform | 5 | `MCP/agents-cours/src/interfaces/mcp/CreateurMCPServer.js:79` | Le contrat de `creer_cours` est trompeur et plusieurs réponses historiques restent textuelles avec emojis. | Renommer/déprécier clairement l'outil et normaliser toutes les réponses en JSON stable. |
| 🟡 warning | functional | 6 | `tests/Entity/AgentCourseGenerationTest.php:11` | La majorité des scénarios de sécurité et d'idempotence du plan n'a aucun test. | Ajouter les tests API/Symfony et MCP simulés listés dans le plan avant fusion. |
| 🟡 warning | functional | 7 | `automation/workflows/README.md:18` | La connexion privée Netcup, la nouvelle clé n8n et les contrôles depuis le conteneur ne sont pas réalisés. | Installer Tailscale avec accès administrateur, renouveler la clé puis exécuter les contrôles prévus. |
| 🟡 warning | functional | 8 | `automation/workflows/50-devdoc-course-batch.json:164` | Aucune construction n'impose explicitement au plus deux cours simultanés. | Ajouter un contrôle de concurrence ou traiter les éléments par lots de deux avec reprise correcte. |
| 🟡 warning | functional | 9 | `automation/workflows/50-devdoc-course-batch.json:32` | Les appels réseau n'ont ni reprise automatique ni attente progressive. | Configurer trois tentatives réseau avec backoff sans incrémenter le compteur de vérification. |
| 🟡 warning | functional | 10 | `automation/workflows/50-devdoc-course-batch.json:56` | Seul `succeeded` est repris correctement; les autres états régénèrent depuis le début. | Router chaque état Symfony vers sa prochaine étape persistée. |
| 🟡 warning | functional | 10 | `automation/workflows/50-devdoc-course-batch.json:160` | Le bilan final, le workflow d'erreur, les journaux nettoyés et les scénarios de validation sont absents. | Ajouter l'agrégation du lot, le Error Trigger commun et les tests contrôlés avant activation. |

## Verification

| Metric        | Value |
| ------------- | ----- |
| Verified      | 49% (20/41) |
| Files checked | Symfony PR #30 (15 files), MCP PR #1 (17 files), n8n PR #2 (1 230 files/ancestry plus workflow DevDoc), GitHub checks |
| Unchecked     | Idempotence complète et positions — fix; cohérence catégorie/menu/niveau — fix; compteur exact — fix; recherche média — fix; vision réelle — fix; validation HTML/liens — fix; correction d'images — fix; JSON MCP stable — fix; arbre complet — fix; tests prévus — fix; connexion Tailscale/clé n8n — fix; webhook protégé/concurrence — fix; retries réseau — fix; reprise par état — fix; bilan/erreurs/scénarios — fix; branche n8n propre — fix |
| Unplanned     | La PR n8n transporte 32 commits antérieurs au travail DevDoc et des modifications WordPress/bridge/frontend hors périmètre. |
