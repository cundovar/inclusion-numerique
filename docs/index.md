# Inclusion numérique

[Inclusion numérique](https://inclusion-numerique.varascundo.com/spa/) est un site libre et gratuit qui forme deux publics : les **adultes et seniors** qui apprennent le numérique (« J'apprends le numérique ») et les **médiateurs** qui apprennent à les accompagner (« J'accompagne des personnes »). Les cours sont consultables sans compte, sur téléphone comme en atelier à faible connexion.

## Origine et dépendances

| Élément | Inclusion numérique | Preuve |
|---|---|---|
| Code | Dépôt dédié [`cundovar/inclusion-numerique`](https://github.com/cundovar/inclusion-numerique), branche `main`, issu de `main2` de DevDoc. Le code reprend celui de Médiateur numérique ([`cundovar/mediateur-numerique-formation`](https://github.com/cundovar/mediateur-numerique-formation), commit `c87941f`), lui-même construit sur le socle de DevDoc ([reprise](conception/reprise-devdoc.md)). | <Status is="code" /> |
| Base de données | `egflbugcundo6`, partagée avec Médiateur ; aucune migration jouée par ce site | <Status is="code" /> |
| Images | `public/uploads`, encore un lien vers le dossier de Médiateur ; [bascule préparée](deploiement/index.md#connecteur-mcp-et-images), pas encore faite | <Status is="prod" /> |
| Connecteur MCP | Repris de Médiateur sur `/mcp`, jetons cloisonnés ; pas encore déployé | <Status is="ci" /> |

Détail et contrôles : [déploiement](deploiement/index.md#base-et-fichiers-partagés). Les preuves de DevDoc et de Médiateur ne valent pas pour ce site.

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
| Assistant de rédaction | Connecteur MCP du site (`/mcp`), pas encore déployé | <Status is="ci" /> |
| Hébergement | OVH (`inclusion-numerique.varascundo.com`), workflow CI/CD corrigé en PR #2 ; déploiement manuel après validation de la synchronisation | <Status is="ci" /> |

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
