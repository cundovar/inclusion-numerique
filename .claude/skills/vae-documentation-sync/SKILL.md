---
name: vae-documentation-sync
description: Synchronize the VAE dossier (VAE/, TP CDA RNCP37873) and the VitePress documentation site (docs/) of the Médiateur numérique project with verified code, test, Git, CI and deployment changes. Use after completing a feature, fix, refactor, security change, test, CI/CD change or architecture change that may alter what the site describes or the VAE evidence.
---

# Synchroniser la documentation et le dossier VAE

Utiliser ce skill à la fin d'une tâche technique terminée, après `make check` et avant de déclarer la tâche finalisée. Ne rien modifier pendant une tâche intermédiaire.

## Documents de référence

- `VAE/README.md` : référentiel visé (TP Concepteur développeur d'applications, RNCP37873) et règles de mise à jour.
- `VAE/rapport.md` : synthèse par bloc, courte.
- `VAE/matrice-competences.md` : onze compétences → réalisation Médiateur → preuve.
- `VAE/preuves.md` : registre canonique des preuves et définition des niveaux.
- `VAE/journal.md` : historique daté des ajouts significatifs.
- `VAE/evidence/` : sorties de preuve archivées, immuables (protégées par `.agents/hooks/protect-files.sh`).
- Site VitePress `docs/` : pages par thème ; table « Où écrire » dans `AGENTS.md`.

## Procédure

1. Examiner le diff, les commandes réellement exécutées et l'historique Git pertinent.
2. Identifier les pages `docs/` et les fichiers `VAE/` concernés. Ne pas modifier les autres.
3. Rechercher les affirmations devenues obsolètes : nombres, noms de fichiers, routes, tests, jobs CI, dates.
4. Vérifier chaque affirmation dans le code ou dans une commande effectivement exécutée.
5. Mettre à jour la page `docs/` concernée avec des liens relatifs vers le code et un badge `<Status is="…" />` fidèle.
6. Mettre à jour `VAE/` seulement si la preuve d'une compétence change : ligne du registre, de la matrice, puis une entrée datée du journal. Le rapport reste une synthèse.
7. Lancer `make docs-build` (échoue sur un lien interne mort) et rapporter le résultat.
8. Relire le diff documentaire et résumer.

## Niveaux de preuve

| Statut | Preuve exigée |
|---|---|
| `code` | Fichier, classe ou configuration identifiable |
| `local` | Commande exécutée dans cette session, résultat consigné (date, commande) |
| `ci` | Run GitHub Actions identifié (numéro, commit, job réussi) |
| `prod` | Contrôle du site déployé (date, URL, méthode) |
| `prevu` | Décision ou plan sans réalisation vérifiée |

Ne jamais faire monter un statut sans la commande, le run CI ou le contrôle de production correspondant.

## Règles

- Ne jamais inventer une compétence, une technologie, une date, un résultat ou une responsabilité.
- Ne reprendre aucune preuve, mesure, statistique ou exécution du projet DevDoc : DevDoc n'est cité que comme provenance du socle.
- La présence d'un fichier ne prouve pas son fonctionnement ; un nombre de fichiers de test n'est pas un résultat d'exécution.
- Un run CI réussi n'est pas un contrôle du site en production.
- Garder les lacunes visibles, avec le manque à produire.
- Signaler « formalisé a posteriori » pour un document écrit après la réalisation.

## Sortie attendue

Un court bilan :

- pages `docs/` et fichiers `VAE/` modifiés (ou raison de ne rien modifier) ;
- résultat de `make docs-build` ;
- preuves ajoutées et leur niveau ;
- affirmations obsolètes corrigées ;
- points non vérifiables ou restant à traiter.
