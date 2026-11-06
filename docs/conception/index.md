# Besoin et publics

> Synthèse formalisée a posteriori à partir du [plan produit](../../PLAN_APP_FORMATION_MEDIATEUR_NUMERIQUE.md) (cadrage du 15 octobre 2026) et du code. <Status is="code" />

## Le besoin

Les structures de médiation numérique accompagnent des personnes éloignées du numérique dans des gestes du quotidien (messagerie, services publics en ligne, sécurité). Le site leur fournit des supports libres et gratuits, utilisables seuls ou en atelier, y compris avec une connexion faible. Il ne réalise jamais une démarche administrative à la place d'un usager et ne se présente pas comme un portail officiel.

## Deux publics, deux entrées

| Public | Entrée | Résultat attendu |
|---|---|---|
| Adultes et seniors | « J'apprends le numérique » (`/apprendre`) | Refaire seul un geste utile : envoyer un courriel, trouver un service officiel, préparer une démarche |
| Médiateurs et professionnels | « J'accompagne des personnes » (`/accompagner`) | Diagnostiquer un besoin, expliquer simplement, faire pratiquer, vérifier l'autonomie, orienter |

Chaque parcours (catégorie) porte un champ `audience` (`apprendre` ou `accompagner`) qui décide de l'entrée où il apparaît : `src/Entity/Category.php`, testé par [`CategoryAudienceApiTest`](../../tests/Functional/CategoryAudienceApiTest.php). <Status is="ci" />

## Exigences retenues

- **Accès sans compte** aux cours publics ; compte apprenant facultatif (16 octobre 2026), seulement pour retrouver des contenus personnalisés. Aucun score, favori ni suivi n'est enregistré. <Status is="ci" />
- **Connexion faible** : PWA consultable hors ligne, PDF de chaque cours et module. <Status is="ci" />
- **Données personnelles** : aucune donnée d'usager réel dans les contenus ; captures sur pages publiques ou données fictives (référentiel [`regles/`](../../regles/README.md)). <Status is="code" />
- **Accessibilité** : navigation clavier, contraste, langage clair (règle `regles/04-redaction-et-accessibilite.md`). Audit RGAA et essais avec des apprenants : <Status is="prevu" />

## Modèle de contenu

Parcours → modules → cours (leçons) → QCM, avec captures pédagogiques sourcées. Chaque cours a un statut éditorial (brouillon, en revue, publié, à réviser). Voir l'[architecture](../architecture/index.md#données) pour les entités.

## Ce qui manque

Pages ajoutées le 2026-11-02, toutes formalisées a posteriori :

- [User stories et cas d'utilisation](besoins.md), avec des critères observables et leur niveau de preuve.
- [Écrans et enchaînements](maquettes.md) : aucune maquette n'a précédé le développement ; la page décrit les écrans existants à partir du routeur et de captures datées du site publié.
- [Modèle de données](base-de-donnees.md) : MCD, MLD et accès aux données, tirés des migrations de `main2`.
- [Décisions d'architecture](decisions-architecture.md) et [gestion de projet](../gestion-de-projet.md).

Reste à faire : essais avec des apprenants et audit d'accessibilité. <Status is="prevu" />
