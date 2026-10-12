---
objective: "Le MCP permet de gérer de bout en bout les cours et leur arborescence Symfony avec une boucle de vérification limitée, traçable et soumise à validation humaine."
status: pending
---

# Plan: Gestion complète des contenus DevDoc par MCP

## Overview

| Field      | Value |
| ---------- | ----- |
| **Goal**   | Compléter les serveurs MCP pour gérer les contenus via Symfony et faire vérifier puis corriger chaque cours avant toute publication humaine. |
| **Source** | Demandes utilisateur dans cette tâche : modification du MCP, gestion de l'arborescence `Category → Menus → Page → PageContent`, puis ajout d'un agent vérificateur avec boucle de correction. |

## Phases

| # | Phase | File |
| --- | --- | --- |
| 1 | Fiabiliser le contrat MCP | [`phase-1.md`](./phase-1.md) |
| 2 | Rattacher et rechercher les cours | [`phase-2.md`](./phase-2.md) |
| 3 | Gérer l'arborescence de navigation | [`phase-3.md`](./phase-3.md) |
| 4 | Gérer le cycle de vie et les révisions | [`phase-4.md`](./phase-4.md) |
| 5 | Ajouter l'agent vérificateur et la boucle de correction | [`phase-5.md`](./phase-5.md) |
| 6 | Valider l'intégration et documenter | [`phase-6.md`](./phase-6.md) |

## Decisions

| Decision | Why |
| -------- | --- |
| Symfony reste l'unique propriétaire des règles métier et des écritures en base. | Le MCP doit être une passerelle IA et ne doit pas dupliquer les transactions ou validations Symfony. |
| Tous les appels mutatifs passent par des routes `/api/admin` protégées par `X-API-KEY`. | Cela conserve le mécanisme d'authentification et d'autorisation existant. |
| Les deux serveurs MCP `stdio` existants sont conservés. | Le créateur porte la gestion du contenu et de la navigation, le réviseur garde le workflow de révision. |
| Symfony et `MCP/agents-cours` sont traités comme deux dépôts coordonnés. | `MCP/agents-cours` possède son propre dépôt Git et est ignoré par le dépôt Symfony parent. |
| Le cycle de vie exposé est `brouillon` ou `publié`; l'archivage est différé. | `PageContent` ne possède actuellement qu'un booléen `visible`, insuffisant pour représenter un troisième état sans migration métier. |
| La vérification utilise un agent séparé, un contexte neuf et un rapport structuré. | Une autocorrection dans le même échange reproduirait plus facilement les erreurs du créateur. |
| La boucle est limitée à trois vérifications et corrections. | Une limite empêche les cycles infinis et déclenche une intervention humaine prévisible. |
| Aucun agent ne peut publier un cours. | Un verdict automatique réduit le risque mais ne garantit pas l'absence d'hallucination; la décision finale reste humaine. |
| n8n n'est pas introduit dans cette version. | La boucle tient dans le MCP et Symfony; n8n ne devient utile que pour planification, notifications, traitements massifs ou workflows externes. |
