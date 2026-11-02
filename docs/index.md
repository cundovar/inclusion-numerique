# Site de formation à la médiation numérique

Site libre et gratuit qui forme deux publics : les **adultes et seniors** qui apprennent le numérique (« J'apprends le numérique ») et les **médiateurs** qui apprennent à les accompagner (« J'accompagne des personnes »). Les cours sont consultables sans compte, sur téléphone comme en atelier à faible connexion.

Cette documentation décrit le fonctionnement réel du dépôt. Chaque information importante porte un badge de niveau de preuve :

| Badge | Signification |
|---|---|
| <Status is="code" /> | Le code ou la configuration existe dans le dépôt |
| <Status is="local" /> | Une commande a été exécutée localement et son résultat consigné |
| <Status is="ci" /> | Un run GitHub Actions identifié l'a validé |
| <Status is="prod" /> | Contrôlé sur le site déployé |
| <Status is="prevu" /> | Décidé ou planifié, pas encore réalisé |

## En bref

| Élément | Choix | Preuve |
|---|---|---|
| Interface | Vue 3, Vite, Pinia, Tailwind, PWA hors ligne | <Status is="ci" /> |
| API et back-office | Symfony 6.4, API Platform, EasyAdmin, MySQL 8 | <Status is="ci" /> |
| Assistant de rédaction | Connecteur MCP `/mcp` pour claude.ai et ChatGPT, OAuth | <Status is="ci" /> |
| Captures automatiques | Service Playwright isolé sur Netcup | <Status is="ci" /> |
| Hébergement | OVH, déploiement automatique depuis `main` | <Status is="prod" /> |

## Parcourir

- [Besoin et publics](conception/index.md) et [reprise du socle DevDoc](conception/reprise-devdoc.md)
- [Fonctionnalités](fonctionnalites/index.md), [guide d'édition](fonctionnalites/edition.md), [connecteur MCP](fonctionnalites/connecteur.md)
- [Architecture](architecture/index.md) et [sécurité](securite/index.md)
- [Tests](tests/index.md), [déploiement et CI](deploiement/index.md), [harness des agents](harness/index.md)
- [Dossier VAE](vae/index.md)

## Lancer cette documentation

```bash
make docs-dev     # http://localhost:5176/docs/
make docs-build   # build complet, échoue sur un lien interne mort
```
