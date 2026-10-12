# Maquettes — DevDoc

> **Maquettes basse fidélité réalisées a posteriori** le 2026-10-12, à partir de l'application existante et du code source (`front/src/`). Elles formalisent la structure des écrans et la relient aux user stories de [besoins.md](besoins.md). Elles ne précèdent pas le développement.

- Fichier Figma : [DevDoc — Maquettes VAE CDA](https://www.figma.com/design/yAkKPYcYS6XMhwGVI8RqKX). Page « Maquettes basse fidélité » (composants partagés et écrans), page « Réalisation (captures) ».
- Exports versionnés : [`maquettes/`](maquettes/). Captures de l'application réelle : [`captures/`](captures/), prises en local (`http://localhost:8092/spa/`) le 2026-10-12.

## Composants partagés

Les écrans sont construits à partir de 4 composants Figma réutilisés, reflétant les composants de mise en page Vue :

| Composant Figma | Rôle | Source dans le code |
|---|---|---|
| Header / Desktop | Logo, recherche, filtre par niveau, connexion | `front/src/components/layout/AppHeader.vue` |
| Sidebar / Menus | Catégories dépliables, accès QCM et exercices, filtres de vocabulaire, liens de documentation | `front/src/components/layout/AppNavigation.vue`, `components/navigation/CategoryMenu.vue`, `SelectfunctionVocabulaire.vue` |
| Header / Mobile | Menu, logo, connexion, filtre par niveau, recherche repliée | `front/src/components/layout/AppHeaderMobile.vue` |
| BottomBar / Mobile | Accès direct QCM et exercices | `front/src/components/navigation/NavFooterMobile.vue` |

## Maquette et réalisation

| Écran | Maquette | Réalisation | User stories |
|---|---|---|---|
| Accueil | [01-accueil.png](maquettes/01-accueil.png) | [accueil-desktop.png](captures/accueil-desktop.png) | US-01, US-02, US-04, US-08 |
| Page de cours | [02-cours.png](maquettes/02-cours.png) | [cours-desktop.png](captures/cours-desktop.png) | US-03, US-09, US-10 |
| QCM | [03-qcm-question.png](maquettes/03-qcm-question.png) | [qcm-desktop.png](captures/qcm-desktop.png) (formulaire de choix) | US-06, US-07 |
| Profil | [04-profil.png](maquettes/04-profil.png) | Non capturé (écran réservé aux utilisateurs connectés) | US-09 à US-13 |
| Page de cours mobile | [05-cours-mobile.png](maquettes/05-cours-mobile.png) | [cours-mobile.png](captures/cours-mobile.png) | US-03, ENF-01, ENF-02 |

## Écarts relevés entre maquette et réalisation

- **Couleurs** : les maquettes sont en niveaux de gris. L'application distingue les entrées QCM (violet), Exercices (vert) et les filtres de vocabulaire (jaune).
- **Notes** : dans l'application, l'éditeur de note est un onglet du panneau « Sommaire » (`components/features/pages/toc/TableOfContents.vue`). La maquette reprend ce choix.
- **Sommaire sur mobile** : la maquette montre un bouton « Sommaire » replié. Le code prévoit un sommaire replié sur mobile (`TocList.vue`, `collapsedOnMobile`), mais il n'est pas visible dans la capture.
- **QCM** : la maquette représente l'écran de question (`views/QCM/questionQCM.vue`) ; la capture montre l'écran précédent, le formulaire de choix du niveau et du langage (`views/QCM/PageQCM.vue`).
- **Accueil** : la grille de la maquette correspond à la section « Cours » de l'accueil (`HomePage.vue`), située sous la ligne de flottaison dans la capture. L'application y affiche une carte par catégorie (icône, nom, nombre de cours) menant à `/category/:category`, alors que la maquette les illustre par des noms de technologies.
