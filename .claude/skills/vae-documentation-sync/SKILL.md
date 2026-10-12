---
name: vae-documentation-sync
description: Synchronize the VAE CDA documentation and the VitePress documentation site (docs/) with verified code, test, Git, and deployment changes. Use after completing a feature, fix, refactor, security change, test, CI/CD change, or architecture change that may alter the project's evidence for the VAE dossier.
---

# Synchroniser la documentation VAE

Utiliser ce skill à la fin d'une tâche technique terminée, après les tests et avant de déclarer la tâche finalisée.

## Documents de référence

- `agents/agent_VAE.md` : méthode d'analyse et critères CDA.
- `VAE/README.md` : point d'entrée du dossier VAE.
- `VAE/rapport-cda.md` : rapport consolidé à maintenir.
- `VAE/preuves.md` : registre canonique des preuves.
- `VAE/journal.md` : historique des évolutions documentaires.
- `VAE/result_analyse_VAE.md` : analyse historique, à ne pas traiter comme source actuelle.
- `documentations/doc-pour-portfolio.md` : présentation fonctionnelle et technique.
- `docs/CI_CD.md` : qualité, CI et déploiement.
- `documentations/architecture/` : décisions et plans d'architecture.
- Site VitePress `docs/` : pages par thème (`fonctionnalites/`, `api/`, `donnees/`, `conception/`, `decisions/`, `demarrer/`) ; table « Où écrire » dans `AGENTS.md`.

## Procédure

1. Examiner le diff, les tests exécutés et l'historique Git pertinent.
2. Identifier les documents concernés par le changement. Ne pas modifier les documents non concernés.
3. Rechercher les affirmations devenues obsolètes : versions, nombres, noms de fichiers, fonctionnalités, tests, CI/CD et dates.
4. Vérifier chaque affirmation dans le code ou dans une commande effectivement exécutée.
5. Mettre à jour la documentation en conservant les preuves concrètes : fichier, classe, méthode, test ou commit.
6. Distinguer explicitement :
   - présent dans le code ;
   - vérifié localement ;
   - validé par la CI ;
   - déployé ou vérifié en production.
7. Signaler les informations impossibles à vérifier au lieu de les inventer.
8. Reporter le changement dans le site VitePress quand il modifie ce que le site décrit (voir la section « Documentation VitePress » de `AGENTS.md`), puis lancer `make docs-build`.
9. Relire le diff documentaire et résumer les documents changés, les preuves ajoutées et les points restant à vérifier.

## Règles VAE

- Ne jamais inventer une compétence, une technologie, une date, un niveau de maîtrise ou une responsabilité.
- Ne pas transformer la présence d'un fichier en preuve de fonctionnement.
- Ne pas compter les tests sans vérifier leur nature et leur exécution.
- Ne pas présenter une CI comme un déploiement réussi.
- Préserver l'honnêteté du rapport : les lacunes doivent rester visibles avec une recommandation concrète.
- Ne pas modifier la documentation pendant une tâche intermédiaire ou avant validation du code.

## Sortie attendue

Terminer par un court bilan :

- documents synchronisés, pages VitePress comprises (ou raison de ne pas en modifier) ;
- résultat de `make docs-build` ;
- preuves vérifiées ;
- éléments devenus obsolètes corrigés ;
- éléments non vérifiables ou restant à traiter ;
- tests et validations réellement exécutés.
