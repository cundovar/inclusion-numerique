# Rapport VAE — synthèse

> Document canonique de synthèse. Chaque fait renvoie à une preuve du [registre](preuves.md) ; le détail technique est dans `docs/`. Les éléments formalisés après la réalisation sont signalés. La part personnelle du candidat dans chaque réalisation reste à confirmer ([questions ouvertes](evidence/2026-11-02-cadrage/inventaire.md#auteurs-et-aide-des-outils-ia)).

## Projet

[Inclusion numérique](https://inclusion-numerique.varascundo.com/spa/) est un site de formation libre et gratuit au numérique, pour deux publics : les adultes et seniors qui apprennent le numérique, et les médiateurs qui apprennent à accompagner.

Provenance : le socle Vue 3 / Symfony 6.4 vient de DevDoc ; Médiateur numérique l'a repris le 15 octobre 2026 et adapté jusqu'au 30 octobre (`c87941f`) ; Inclusion numérique reprend ce code le 2 novembre 2026 sur la branche `main2` du dépôt de DevDoc (`cundovar/vue-symfony`), sans le connecteur MCP. Inclusion partage la base de données et les images de Médiateur ; son déploiement est distinct (dossier OVH propre, environnement GitHub `production`). Version de référence : `30da5e5` ([cadrage](evidence/2026-11-02-cadrage/inventaire.md)).

Les dix commits propres à `main2` ont été écrits avec l'assistance de Claude Opus 5.5 (mention `Co-Authored-By` dans chacun).

Le dépôt dédié `cundovar/inclusion-numerique` reprend cette histoire. Le site a déjà été déployé sur OVH ; depuis le retrait de l'ancien code le 6 novembre 2026, la racine répond 404. Le transfert des deux commits récents est en cours de validation avant un déploiement manuel.

## BC01 — Développer une application sécurisée

- Environnement de travail Docker et commandes de validation communes (`make check`) ; harness d'agents IA qui protège les fichiers sensibles et impose la validation ([harness](../docs/harness/index.md)). Installation complète non encore consignée.
- Interface Vue 3 à deux entrées publiques, PWA consultable hors ligne, espace apprenant facultatif, héritée de Médiateur ; nom du site propre à Inclusion par `SITE_NAME` ([fonctionnalités](../docs/fonctionnalites/index.md)).
- Composants métier : visibilité des contenus réservés, PDF des cours, compte apprenant, informations légales. Le connecteur MCP, OAuth et la capture automatique ont été retirés d'Inclusion et un test vérifie leur absence ([sécurité](../docs/securite/index.md), [connecteur retiré](../docs/fonctionnalites/connecteur.md)).
- Gestion de projet par plans de phase, commits typés et CI bloquante ; chronologie, indicateurs et backlog recalculés a posteriori ([gestion de projet](../docs/gestion-de-projet.md)). Aucun suivi n'a été tenu pendant le projet.

## BC02 — Concevoir et développer une application organisée en couches

- Besoins et publics décrits dans le plan produit hérité de Médiateur ; user stories, cas d'utilisation et écrans formalisés a posteriori ([user stories](../docs/conception/besoins.md), [écrans](../docs/conception/maquettes.md)). Aucune maquette n'a précédé le développement.
- Architecture en couches (Domain, Infrastructure, Service, Controller) et API Platform ([architecture](../docs/architecture/index.md)). Le choix structurant d'Inclusion est le partage de la base et des images de Médiateur ([décisions d'architecture](../docs/conception/decisions-architecture.md)).
- Base MySQL gérée par migrations Doctrine, vérifiées en CI sur une base vide (montée, validation, retour arrière). En production, Inclusion ne joue aucune migration : un filtre de schéma ignore les tables qu'il n'utilise pas. MCD, MLD et script de création sont tirés des migrations de `main2` ([modèle de données](../docs/conception/base-de-donnees.md), formalisé a posteriori).
- Accès aux données : repositories Doctrine et filtre des contenus publiés ou réservés ; IndexedDB côté client pour le hors ligne.

## BC03 — Préparer le déploiement d'une application sécurisée

- Tests PHPUnit et Vitest exécutés en CI sur `30da5e5`. Le scénario Playwright hérité n'est pas exécutable en l'état ; le plan de tests est formalisé a posteriori et le jeu d'essai reste à produire ([tests](../docs/tests/index.md)).
- Déploiement documenté : OVH par rsync depuis la CI, base et images partagées avec Médiateur ([déploiement](../docs/deploiement/index.md)).
- Déploiement historique réussi le 2026-11-02 (run 41442887513) et site contrôlé le même jour ; cet état historique ne prouve pas l'état actuel.

- Workflow GitHub Actions : les contrôles PHP, migrations, front, documentation et build de release sont réussis sur la PR #2 ([run 42206685700](https://github.com/cundovar/inclusion-numerique/actions/runs/42206685700)) et sur `main` après fusion ([run 42206988450](https://github.com/cundovar/inclusion-numerique/actions/runs/42206988450)). Le déploiement du premier push sur `main` a échoué car le workflow ciblait le mauvais environnement GitHub ; la correction est fusionnée et le lancement OVH reste manuel après validation de la synchronisation du code.

## État de la couverture

La [matrice](matrice-competences.md) distingue les compétences couvertes et partielles selon les preuves disponibles. La compétence « plans de tests » reste partielle faute de plan préalable, de jeu d'essai et de scénario de bout en bout exécuté. Les manques principaux sont l'absence de maquettes antérieures au développement, la part personnelle du candidat, le plan de tests et le jeu d'essai, et un essai consigné de l'environnement complet. MCD, MLD, user stories, décisions et indicateurs ont été formalisés a posteriori le 2026-11-02 sans relever automatiquement la couverture.
