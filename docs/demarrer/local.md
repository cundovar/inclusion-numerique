# Lancer en local

Tout tourne dans Docker Compose ([`compose.yaml`](../../compose.yaml)), piloté par le [`Makefile`](../../Makefile).

```bash
make up
```

| Service | Adresse | Rôle |
|---|---|---|
| `nginx` | http://localhost:8092 | Application (Symfony et SPA sous `/spa/`) |
| `front` | http://localhost:5173 | Serveur Vite (rechargement à chaud) |
| `php` | — | PHP-FPM 8.3, Symfony |
| `db` | — | MySQL |
| phpMyAdmin | http://localhost:8091 | Consultation de la base |
| `qcm-results`, `qcm-mongo` | — | Micro-service des résultats de QCM et sa base, joignables seulement par le réseau Docker |

## Valider une modification

| Commande | Contrôles |
|---|---|
| `make check-fast` | Syntaxe PHP, PHPStan, ESLint |
| `make check` | En plus : PHPUnit, Vitest, tests du micro-service, build Vite. **Une tâche n'est terminée que si elle passe.** |
| `make test-e2e` | Parcours Playwright sur l'application compilée (`docker compose run --rm front-build` avant) |

## Cette documentation

```bash
make docs-dev
```

Le site s'ouvre sur http://localhost:5174. `make docs-build` vérifie qu'il compile, liens morts compris.

## Maquettes (Penpot)

Le design system et les maquettes ([Design system](../conception/design-system.md), [Maquettes](../conception/maquettes.md)) sont faits dans Penpot, auto-hébergé à part de l'application ([`tools/penpot/compose.yaml`](../../tools/penpot/compose.yaml), projet Compose `devdoc-penpot`, version 2.18.3 figée).

| Commande | Effet |
|---|---|
| `make design-up` | Génère au premier lancement `tools/penpot/penpot.local.env` (clé secrète, non versionné), puis démarre Penpot. Premier démarrage : 1 à 2 minutes |
| `make design-down` | Arrête Penpot. Les volumes (base PostgreSQL et fichiers) sont conservés |
| `make design-logs` | Suit les journaux des conteneurs Penpot |

| Service | Adresse | Rôle |
|---|---|---|
| `penpot-frontend` | http://localhost:9001 | Interface Penpot (port lié à `127.0.0.1`) |
| `penpot-mailcatch` | http://localhost:1080 | Courriels de Penpot (inscription locale, sans vérification d'adresse) |
| `penpot-mcp` | http://localhost:9001/mcp/stream | Serveur MCP de Penpot pour les agents IA |

Pour qu'un agent IA (Claude Code) travaille dans Penpot :
1. générer une clé MCP dans son compte Penpot ;
2. déclarer le serveur MCP **soi-même**, car l'adresse contient la clé : `claude mcp add --transport http --scope user penpot "http://localhost:9001/mcp/stream?userToken=<clé>"`. En portée `local`, le serveur n'est visible que depuis le dossier où la commande a été lancée ;
3. ouvrir le fichier dans Penpot et y connecter le plugin MCP, qui exécute les actions de l'agent dans la page affichée. Ne pas changer de page dans Penpot pendant que l'agent construit.

::: danger À ne pas lancer
`make down-volumes` supprime la base MySQL locale. `make down` arrête les conteneurs sans toucher aux données.
:::
