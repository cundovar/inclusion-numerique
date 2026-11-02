# Dossier VAE — Médiation numérique

Ce dossier présente le projet **Médiateur numérique** (site de formation à la médiation numérique) pour la validation des acquis de l'expérience. Il contient la synthèse et le suivi des preuves ; les explications techniques détaillées sont dans le site de documentation (`docs/`).

## Référentiel visé

| Élément | Valeur |
|---|---|
| Certification | Titre professionnel **Concepteur développeur d'applications** (TP CDA) |
| Code | [RNCP37873](https://www.francecompetences.fr/recherche/rncp/37873/) |
| Niveau | 6 |
| Certificateur | Ministère du Travail, du Plein emploi et de l'Insertion |
| Fiche | publiée le 20-07-2023, début des parcours certifiants le 18-12-2023, échéance de l'enregistrement le 18-12-2028 |
| Version du REAC | à confirmer sur le document du certificateur |
| Fiche consultée le | 2026-10-30 |

Les trois blocs (BC01 à BC03) et leurs onze compétences sont repris mot pour mot de la fiche France Compétences dans la [matrice](matrice-competences.md).

DevDoc, projet présenté auparavant, a fourni le socle technique repris le 15 octobre 2026 (voir [`docs/conception/reprise-devdoc.md`](../docs/conception/reprise-devdoc.md)). Aucune preuve, mesure, statistique ou exécution de test de DevDoc n'est utilisée dans ce dossier.

## Documents canoniques

| Document | Rôle |
|---|---|
| [`rapport.md`](rapport.md) | Synthèse par bloc, destinée au jury |
| [`matrice-competences.md`](matrice-competences.md) | Correspondance compétence → réalisation → preuve |
| [`preuves.md`](preuves.md) | Registre des preuves et niveaux de validation |
| [`journal.md`](journal.md) | Historique daté des ajouts significatifs |

## Références techniques

Site de documentation : sources dans [`docs/`](../docs/index.md), lancé localement par `make docs-dev` (<http://localhost:5176/docs/>) et publié avec le site sous `/docs/`.

| Sujet | Page |
|---|---|
| Besoin, publics, conception | [`docs/conception/index.md`](../docs/conception/index.md) |
| Fonctionnalités | [`docs/fonctionnalites/index.md`](../docs/fonctionnalites/index.md) |
| Architecture | [`docs/architecture/index.md`](../docs/architecture/index.md) |
| Sécurité | [`docs/securite/index.md`](../docs/securite/index.md) |
| Tests | [`docs/tests/index.md`](../docs/tests/index.md) |
| Déploiement et CI | [`docs/deploiement/index.md`](../docs/deploiement/index.md) |
| Harness des agents | [`docs/harness/index.md`](../docs/harness/index.md) |

## Règles de mise à jour

- Une affirmation porte toujours un niveau de preuve (voir [`preuves.md`](preuves.md#niveaux-de-preuve)).
- Un statut ne monte (`code` → `local` → `ci` → `prod`) qu'avec la commande, le run CI ou le contrôle de production correspondant.
- `VAE/evidence/` est réservé aux sorties de preuve archivées : un nouveau contrôle crée un nouveau dossier daté, on ne modifie jamais un dossier existant.
- La mise à jour se fait après validation du code, avec le skill `vae-documentation-sync` (voir [`AGENTS.md`](../AGENTS.md)).
