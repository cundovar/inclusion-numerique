# Frontend — conventions

## Stack
Vue 3 (SFC), Pinia (`src/stores/`), Vue Router (`src/router/`), Vite (`vite.config.js`), Tailwind 4, PWA (vite-plugin-pwa).

## Organisation
- `src/components/ui/` : composants génériques réutilisables (`App*`).
- `src/components/features/<domaine>/` : composants métier ; `src/components/layout/`, `src/components/navigation/`.
- `src/views/` : pages routées. `src/composables/` : logique réutilisable. `src/services/` : appels API.

## Règles
- HTML venant de l'API : utiliser `components/ui/SafeHtml.vue` (DOMPurify) plutôt qu'un `v-html` direct dans le nouveau code.
- Appels HTTP via `src/services/` (`api.js`) dans le nouveau code ; certains composants existants importent encore axios directement.
- Pas de nouvel outil de build : Vite est le seul pipeline.

## Validation
- `make lint-front` (ESLint, `eslint.config.js`) : 0 erreur exigée ; ne pas ajouter de warnings dans les fichiers touchés.
- `make build-front` : le build Vite doit passer.
