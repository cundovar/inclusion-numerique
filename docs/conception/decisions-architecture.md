# Décisions d'architecture

> Registre formalisé a posteriori le 2026-11-02. Chaque décision est datée par sa source (commit, document du dépôt) ; le contexte et les conséquences reprennent ce que cette source écrit. Quand la source ne mentionne pas d'alternative, la ligne le dit : aucune alternative n'est reconstituée après coup. Les décisions héritées ont été prises pour DevDoc ou Médiateur et s'appliquent à Inclusion parce que son code les reprend. <Status is="code" />

| # | Date | Décision | Projet d'origine | Source |
|---|---|---|---|---|
| D1 | 2026-10-15 | Reprendre le socle de DevDoc plutôt que repartir de zéro | Médiateur | [reprise de DevDoc](reprise-devdoc.md) |
| D2 | 2026-10-16 | Compte apprenant facultatif, réservé aux contenus personnalisés | Médiateur | `AGENTS.md` (§ État réel du projet) |
| D3 | 2026-10-12 | Stockage hors ligne côté client en IndexedDB, isolé dans une couche d'accès testée | DevDoc | `fe16d88`, `front/src/utils/offlineDb.js` |
| D4 | 2026-11-02 | Déployer Inclusion depuis la branche `main2` du dépôt de DevDoc, vers un dossier OVH distinct | Inclusion | `d148fd6` |
| D5 | 2026-11-02 | Partager la base `egflbugcundo6` avec Médiateur et reprendre son code | Inclusion | `deb7543`, `95d47ac` |
| D6 | 2026-11-02 | Ne jouer aucune migration au déploiement ; filtrer le schéma | Inclusion | `95d47ac`, `a379bfe` |
| D7 | 2026-11-02 | Partager les images par un lien symbolique | Inclusion | `95d47ac`, `8ed912f` |
| D8 | 2026-11-02 | Retirer le connecteur MCP d'Inclusion | Inclusion | `a379bfe` |

## D1 — Reprise du socle DevDoc

- **Contexte** : construire vite un site de formation avec navigation, cours, recherche et QCM, que DevDoc possédait déjà.
- **Décision** : copier les fichiers suivis de DevDoc (`fe8f559`) après un essai court, puis retirer les fonctions écartées.
- **Alternative** : aucune autre option n'est décrite ; la source précise seulement qu'un essai dans une copie isolée a précédé la décision ([reprise](reprise-devdoc.md#essai-court-et-décision)).
- **Conséquences** : architecture, composants et une partie des tests viennent de DevDoc ; leurs résultats ne valent pas pour ce site.

## D2 — Compte apprenant facultatif

- **Contexte** : formations libres et gratuites, publics peu à l'aise avec les comptes en ligne.
- **Décision** : cours publics ouverts sans compte ; compte (e-mail et pseudo) seulement pour retrouver des contenus réservés ; aucun score ni suivi enregistré.
- **Alternative** : non documentée dans la source.
- **Conséquences** : entités `User`, `PasswordResetToken`, trait `AccesReserve` et tables `acces_*` ; filtre `PublishedContentExtension` et service `CourseVisibility` ; tests `LearnerAccountTest`, `PersonalizedContentApiTest`.

## D3 — IndexedDB côté client

- **Contexte** : connexions faibles ou coupées en atelier.
- **Décision** : PWA et stockage IndexedDB des modules préparés, vidé à la déconnexion ; l'accès est regroupé dans `offlineDb.js` et testé avec `fake-indexeddb` (`fe16d88`, DevDoc).
- **Alternative** : non documentée dans la source.
- **Conséquences** : seul composant NoSQL du projet ; aucune base NoSQL côté serveur.

## D4 — Branche `main2` et déploiement distinct

- **Contexte** (`d148fd6`) : « chaque branche correspond à son site » ; `main` continue de déployer DevDoc.
- **Décision** : `main2` déploie vers `/home/egflbug/inclusion-numerique` par l'environnement GitHub `inclusion-numerique` ; le job refuse toute autre cible, car `rsync --delete` vide la cible.
- **Alternative** : non documentée (un dépôt dédié, comme pour Médiateur, était possible ; la raison du choix est à faire préciser par le candidat).
- **Conséquences** : l'historique de DevDoc est présent dans `main2` ; la CI de ce workflow ne s'exécute que pour `main2`.

## D5 — Base et code de Médiateur

- **Contexte** (`deb7543`, `95d47ac`) : Inclusion lit `egflbugcundo6`, la base de Médiateur ; le front de Médiateur est fait pour ces données.
- **Décision** : reprendre le code de Médiateur (`c87941f`) sur `main2` ; nom du site fourni par `SITE_NAME`, parce que la configuration en base est partagée.
- **Alternative** : `deb7543` a d'abord adapté le code de DevDoc à la base partagée (colonnes conservées, migration additive) ; `95d47ac` remplace cette approche par la reprise du code de Médiateur.
- **Conséquences** : une modification de contenu faite sur l'un des sites apparaît sur l'autre ; Inclusion dépend du schéma maintenu par Médiateur.

## D6 — Pas de migration au déploiement

- **Contexte** (`95d47ac`) : « mediateur's own deploy evolves the shared schema ».
- **Décision** : le job `deploy` ne joue aucune migration ; `DOCTRINE_SCHEMA_FILTER` ignore les tables de DevDoc (`95d47ac`) puis celles du connecteur (`a379bfe`).
- **Alternative** : non documentée.
- **Conséquences** : le job CI « Migrations MySQL » vérifie les migrations sur une base vide, pas la base de production ; un écart entre le code d'Inclusion et le schéma maintenu par Médiateur ne serait détecté qu'à l'exécution ([modèle de données](base-de-donnees.md#schéma-attendu-et-base-réelle)).

## D7 — Images partagées

- **Contexte** : les cours de la base pointent vers les images de Médiateur.
- **Décision** : `public/uploads` est un lien vers `~/mediateur/public/uploads`, créé sur le serveur ; `rsync` l'exclut sans barre finale (`8ed912f`), après qu'un déploiement l'a supprimé.
- **Alternative** : non documentée.
- **Conséquences** : une capture ajoutée depuis l'un des deux sites s'affiche sur l'autre ; le lien est une étape manuelle du serveur ([déploiement](../deploiement/index.md#site-inclusion-numerique-branche-main2)).

## D8 — Retrait du connecteur MCP

- **Contexte** (`a379bfe`) : « Content is written through mediateur's connector » ; base et images communes.
- **Décision** : retirer serveur MCP, OAuth, services de rédaction, brouillons de QCM et moteur de capture ; garder `PublicUrl` et `CaptureFileManager`.
- **Alternative** : non documentée.
- **Conséquences** : surface d'attaque réduite (`/mcp`, `/oauth/*` en 404 en production le 2026-11-02) ; `RemovedFeaturesTest` vérifie l'absence des routes ; la rédaction assistée dépend du site Médiateur.

## Limites

- Ces décisions ont été rédigées en une fois, le 2026-11-02 ; seules les dates et les citations viennent des sources.
- Le rôle du candidat dans chaque décision et les alternatives réellement envisagées restent à confirmer par lui.
