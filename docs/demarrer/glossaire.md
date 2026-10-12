# Glossaire

| Terme | Définition | Dans DevDoc |
|---|---|---|
| **SPA** | Application monopage : le navigateur charge une page et Vue gère la navigation | `front/`, servie sous `/spa/` |
| **PWA** | Application web installable, utilisable hors connexion grâce à un service worker | [`front/vite.config.js`](../../front/vite.config.js) (`VitePWA`) |
| **Service worker** | Script du navigateur qui intercepte les requêtes et sert le cache | `/spa/sw.js` (Workbox) |
| **IndexedDB** | Base NoSQL du navigateur, clé / documents | `spa-db`, [`offlineDb.js`](../../front/src/utils/offlineDb.js) |
| **API Platform** | Génère une API REST à partir des entités Doctrine | Attribut `#[ApiResource]` |
| **JSON-LD / Hydra** | Format JSON des réponses d'API Platform (`member`, `@id`) | `Accept: application/ld+json` |
| **Architecture hexagonale** | Le métier ne dépend que d'interfaces (ports) ; la technique les implémente (adaptateurs) | `src/Domain/`, `src/Infrastructure/` |
| **Port / adaptateur** | Interface du domaine / sa mise en œuvre technique | `QcmResultsGatewayInterface` / `HttpQcmResultsGateway` |
| **CQRS** | Séparer les commandes (écrire) des requêtes (lire), chacune traitée par un handler | `src/Application/*/Command`, `Query`, `Handler` |
| **Handler** | Classe qui exécute un cas d'usage | `RecordQcmAttemptHandler` |
| **EasyAdmin** | Back-office généré pour les entités | `/admin` |
| **MCP** | Model Context Protocol : protocole qui donne des outils à un agent IA | Agents de cours, voir [Intégrations](../migration-mcp-vers-symfony.md) |
| **Micro-service** | Service séparé, déployé indépendamment, avec sa propre base | [`services/qcm-results/`](../../services/qcm-results/README.md) |
| **Coolify** | Plateforme auto-hébergée de déploiement de conteneurs, avec Traefik et TLS | VPS Netcup |
| **Rôles** | `ROLE_USER` (connecté), `ROLE_ADMIN` (administrateur) | [`security.yaml`](../../config/packages/security.yaml) |
