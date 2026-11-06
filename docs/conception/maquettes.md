# Écrans et enchaînements

> **Rétrospectif.** Inclusion numérique n'a pas eu de maquette avant son développement : l'interface reprend les composants de DevDoc, adaptés par Médiateur. Cette page, établie le 2026-11-02, décrit les écrans tels qu'ils existent, à partir du routeur (`front/src/router/index.js`) et de captures du site publié. Elle sert de référence de conception a posteriori, pas de preuve de maquettage préalable. <Status is="code" />

Les maquettes Penpot présentes dans l'historique de `main2` (commit `338d54b`, 2026-10-14) appartiennent à DevDoc ; elles ne décrivent pas ce site.

## Enchaînement des écrans

Routes de la SPA (préfixe `/spa`) et pages servies par Symfony.

```mermaid
flowchart TD
  HOME["Accueil /"] --> APP["J'apprends /apprendre"]
  HOME --> ACC["J'accompagne /accompagner"]
  HOME --> QCM["QCM /qcm"]
  HOME --> EXO["Exercices /exercices"]
  APP --> PARC["Parcours /category/:nom"]
  ACC --> PARC
  PARC --> COURS["Cours /pages/:slug"]
  COURS --> PDF["PDF /cours/:slug.pdf (Symfony)"]
  COURS -->|Retour| PARC
  QCM --> Q["Question /qcm/:index"] --> RES["Résultat /qcm/result"]
  HOME --> ESP["Mon espace /mon-espace"]
  ESP -->|non connecté| LOGIN["Connexion /login (Symfony)"]
  ESP -->|non connecté| REG["Inscription /register (Symfony)"]
  LOGIN --> RESET["Mot de passe oublié /mot-de-passe-oublie"]
  LOGIN -->|connecté| ESP
  ESP -->|contenus réservés| PARC
  HOME -. recherche dans l'en-tête .-> COURS
```

Toutes les pages partagent un en-tête (nom du site, recherche, connexion), un menu latéral repliable et un pied de page vers les pages légales.

## Captures du site publié

Prises le 2026-11-02 entre 13 h 54 et 13 h 57, sans connexion, sur `https://inclusion-numerique.varascundo.com/spa/` (Chrome sans interface ; journal : [`captures.log`](../../VAE/evidence/2026-11-02-conception/captures.log)). Le bandeau d'information sur les cookies n'a pas été fermé.

### Accueil et entrées

![Accueil sur ordinateur : deux cartes « J'apprends le numérique » et « J'accompagne des personnes »](assets/accueil-desktop.png)

L'accueil propose les deux entrées et rappelle que les cours sont gratuits et sans inscription.

![Accueil sur téléphone : fenêtre proposant d'installer le site sur l'écran d'accueil](assets/accueil-mobile.png)

Sur téléphone (390 × 844), une fenêtre propose d'installer le site (PWA) et annonce la lecture des cours déjà lus en connexion faible.

![Page « J'apprends le numérique » : liste des parcours pour les apprenants](assets/apprendre-desktop.png)

![Page « J'accompagne des personnes » : parcours destiné aux médiateurs](assets/accompagner-desktop.png)

### Parcours et cours

![Parcours « Premiers gestes numériques » : point de départ conseillé, légende des niveaux et modules](assets/parcours-desktop.png)

Un parcours indique par où commencer, la légende des trois niveaux (« Je découvre », « Je pratique », « Je me débrouille seul ») et ses modules.

![Cours « Utiliser le clavier » : niveau, téléchargement PDF, écoute, sommaire des étapes](assets/cours-desktop.png)

![Cours « Utiliser le clavier » sur téléphone](assets/cours-mobile.png)

### QCM et espace apprenant

![Formulaire QCM : choix d'un niveau et d'une langue](assets/qcm-desktop.png)

![Mon espace, non connecté : boutons « Se connecter » et « Créer un compte »](assets/mon-espace-desktop.png)

## Observations à vérifier

Relevées sur les captures, sans analyse du code ; à reprendre dans le plan de tests :

- Formulaire QCM : un bouton apparaît sans libellé visible sous les deux listes.
- Une adresse de parcours construite avec un identifiant (`/category/1`) affiche « 1 » et « Ce parcours ne contient pas encore de cours » : les liens de l'application utilisent le nom du parcours, mais une adresse invalide ne renvoie pas d'erreur explicite.
