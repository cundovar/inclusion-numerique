# Rapport VAE — synthèse

> Document canonique de synthèse. Chaque fait renvoie à une preuve du [registre](preuves.md) ; le détail technique est dans `docs/`. Les éléments formalisés après la réalisation sont signalés.

## Projet

Site de formation libre et gratuit à la médiation numérique, pour deux publics : les adultes et seniors qui apprennent le numérique, et les médiateurs qui apprennent à accompagner. Le socle Vue 3 / Symfony 6.4 a été repris de DevDoc le 15 octobre 2026, puis allégé et adapté ; le dépôt, la base et le déploiement sont indépendants. Le site est en ligne sur OVH ; un service de capture tourne séparément sur Netcup.

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
- Pipeline GitHub Actions jusqu'à la production, avec un déploiement réussi le 2026-10-20 et un site qui répond au contrôle du 2026-10-30.

## État de la couverture

Au 2026-10-30, d'après la [matrice](matrice-competences.md) : 6 compétences couvertes, 5 partielles, aucune lacune complète. Les manques principaux sont les maquettes, le modèle de données formalisé (MCD/MLD), la gestion de projet (méthode, indicateurs) et un essai consigné de l'environnement complet.
