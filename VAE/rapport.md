# Rapport VAE — synthèse

> Document canonique de synthèse. Chaque fait renvoie à une preuve du [registre](preuves.md) ; le détail technique est dans `docs/`. Les éléments formalisés après la réalisation sont signalés.

## Projet

Site de formation libre et gratuit à la médiation numérique, pour deux publics : les adultes et seniors qui apprennent le numérique, et les médiateurs qui apprennent à accompagner. Le socle Vue 3 / Symfony 6.4 a été repris de DevDoc le 15 octobre 2026, puis allégé et adapté ; le dépôt et le déploiement sont indépendants, tandis que la base et les images sont historiquement partagées avec mediateur. Le site a déjà été déployé sur OVH ; après le retrait de l'ancien code le 6 novembre 2026, la racine répond 404 et attend la synchronisation du code puis un déploiement manuel depuis le dépôt dédié. Un service de capture tourne séparément sur Netcup.

## BC01 — Développer une application sécurisée

- Environnement de travail Docker et commandes de validation communes (`make check`) ; harness d'agents IA qui protège les fichiers sensibles et impose la validation ([harness](../docs/harness/index.md)).
- Interface Vue 3 à deux entrées publiques, PWA consultable hors ligne, espace apprenant facultatif ([fonctionnalités](../docs/fonctionnalites/index.md)).
- Composants métier : assistant de rédaction (connecteur MCP) avec contrôles bloquants et relecture par un autre connecteur, PDF des cours, captures automatiques, accès réservés ([sécurité](../docs/securite/index.md)).
- Gestion de projet par plans de phase, branches et pull requests. Méthode et indicateurs restent à formaliser.

## BC02 — Concevoir et développer une application organisée en couches

- Besoins et publics décrits dans le plan produit ; conception formalisée a posteriori ([conception](../docs/conception/index.md)). Pas de maquette dans le dépôt.
- Architecture en couches (Domain, Infrastructure, Service, Controller), API Platform, serveur MCP et service de capture isolé ([architecture](../docs/architecture/index.md)).
- Base MySQL gérée par migrations Doctrine, vérifiées en CI (montée, validation, retour arrière). MCD et MLD à formaliser.
- Accès aux données : repositories Doctrine et filtre des contenus publiés ou réservés ; IndexedDB côté client pour le hors ligne.

## BC03 — Préparer le déploiement d'une application sécurisée

- Tests PHPUnit, Vitest et moteur de capture exécutés en CI. Le scénario Playwright hérité n'est pas exécutable en l'état ([tests](../docs/tests/index.md)).
- Déploiement documenté : OVH par rsync depuis la CI, service de capture sur Netcup ([déploiement](../docs/deploiement/index.md)).
- Workflow GitHub Actions : les contrôles PHP, migrations, front, documentation et build de release sont réussis sur la PR #2 ([run 42206685700](https://github.com/cundovar/inclusion-numerique/actions/runs/42206685700)) et sur `main` après fusion ([run 42206988450](https://github.com/cundovar/inclusion-numerique/actions/runs/42206988450)). Le déploiement du premier push sur `main` a échoué car le workflow ciblait le mauvais environnement GitHub ; la correction est fusionnée et le lancement OVH reste manuel jusqu'à synchronisation du code.

## État de la couverture

Au 2026-10-30, d'après la [matrice](matrice-competences.md) : 6 compétences couvertes, 5 partielles, aucune lacune complète. Les manques principaux sont les maquettes, le modèle de données formalisé (MCD/MLD), la gestion de projet (méthode, indicateurs) et un essai consigné de l'environnement complet.
