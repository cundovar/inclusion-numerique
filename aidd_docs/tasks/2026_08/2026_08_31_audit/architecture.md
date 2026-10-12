# Codebase Audit: `MCP/agents-cours`

Le dossier contient deux serveurs MCP fonctionnels sur `stdio`, mais leur signalement d'erreur et leur intégration cliente sont incomplets.

- **Date**: 2026-08-31
- **Scope**: `MCP/agents-cours`
- **Health**: good
- **Findings**: 0 critical, 2 warning, 1 minor

## Findings

| Sev | Category | Location | Issue | Suggested fix | Effort |
| --- | --- | --- | --- | --- | --- |
| 🟡 | architecture | `MCP/agents-cours/src/interfaces/mcp/CreateurMCPServer.js:220` | `isError` est placé dans le bloc de contenu au lieu du résultat MCP racine ; le SDK le supprime et le client reçoit donc une réponse présentée comme réussie. | Retourner `{ content: [...], isError: true }` et appliquer la même correction au serveur réviseur. | S |
| 🟡 | architecture | `MCP/agents-cours/package.json:23` | Les deux points d'entrée MCP existent, mais aucun fichier de configuration client MCP n'est fourni ; le projet n'est donc pas découvrable automatiquement par Codex, Claude, Cursor ou VS Code. | Ajouter un exemple de configuration MCP avec les deux commandes et leurs chemins absolus ou relatifs appropriés. | S |
| 🟢 | architecture | `MCP/agents-cours/README.md:20` | Le README documente le serveur HTTP mais pas le lancement ni la connexion aux serveurs MCP. | Documenter `npm run mcp:createur`, `npm run mcp:reviseur`, le transport `stdio` et un exemple de configuration cliente. | S |

## Top actions

1. Corriger la position de `isError` dans les deux gestionnaires d'appel d'outil.
2. Ajouter une configuration cliente MCP utilisable pour les deux serveurs.
3. Compléter le README avec les commandes et le mode de transport MCP.

## Coverage

- **Scanned**: architecture MCP, points d'entrée, transport, négociation client, découverte des outils, documentation d'intégration
- **Skipped**: comportement métier des outils, afin d'éviter toute création ou modification de données ; conformité C4/ADR, aucun document d'architecture MCP dédié trouvé
