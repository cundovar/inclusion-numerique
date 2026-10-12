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

::: danger À ne pas lancer
`make down-volumes` supprime la base MySQL locale. `make down` arrête les conteneurs sans toucher aux données.
:::
