# Consignes du projet — formation à la médiation numérique

Ce fichier est la source commune des consignes pour Codex et Claude Code. Le plan de produit est dans `PLAN_APP_FORMATION_MEDIATEUR_NUMERIQUE.md`. Lire les sections utiles au travail demandé, sans recharger tout le plan pour une correction mineure.

## État réel du projet

- Le dépôt est un nouveau projet indépendant de DevDoc. Le 15 octobre 2026, le socle Vue/Symfony a été repris de DevDoc (commit `fe8f559`) après un essai concluant, puis allégé des fonctions écartées. Détail, constats et reste à adapter : `docs/conception/reprise-devdoc.md`.
- Conserver le style et les composants Vue de DevDoc. Ne pas modifier le dépôt DevDoc lorsque le travail concerne ce projet. L'accueil et les deux catalogues publics sont en place : chaque catégorie (parcours) porte un champ `audience` (`apprendre` ou `accompagner`) qui décide de l'entrée où elle apparaît. Le nom « Médiation numérique » est provisoire et se change dans la configuration globale du back-office.
- La première version accueille deux publics : adultes et seniors qui apprennent le numérique ; médiateurs qui apprennent à accompagner. Les formations sont libres et gratuites, utilisables seuls ou en atelier.
- Compte apprenant facultatif (décision du 16 octobre 2026) : inscription libre sur `/register` avec une adresse e-mail (connexion, jamais affichée ni transmise au connecteur) et un pseudo (affiché, utilisé pour attribuer des contenus), espace `/spa/mon-espace` et mot de passe oublié (`/mot-de-passe-oublie` : lien à usage unique valable une heure, envoyé tout de suite, sans file Messenger ; en production : `MAILER_DSN` vers Brevo, de préférence par l'API `brevo+api://` (paquet `symfony/brevo-mailer`, voir `docs/env.example`), `MAILER_FROM` sur un domaine authentifié dans Brevo, et `MCP_PUBLIC_URL`). Il sert uniquement aux cours, parcours et QCM personnalisés qu'un administrateur réserve à une personne par le connecteur (`attribuer_acces`, `apprenantIds`). Les cours publics restent ouverts sans compte ; aucun score, favori ni suivi n'est enregistré. Le back-office éditorial garde un accès protégé.
- Prévoir les connexions faibles : pages légères, captures optimisées, support imprimable et consultation hors ligne d'un module préparé. Chaque cours et chaque module se télécharge en PDF (`/cours/<slug>.pdf`, `/module/<id>.pdf`, `src/Service/Pdf/`, dompdf avec la police Atkinson Hyperlegible de `resources/fonts/`) : mêmes règles d'accès que l'API publique (`CourseVisibility`), images lues sur le disque, jamais de réseau ; le PDF est gardé dans `var/pdf/` et refait quand le cours ou une capture change. Les pages légales (`/mentions-legales`, `/privacy`, `/terms`, `TermsController`) sont reliées par le pied de page `AppFooter` ; les informations de l'éditeur se saisissent dans le back-office (Configuration du site → Informations légales, `LegalInformation`) et ne s'inventent jamais : un champ vide s'affiche « À compléter ».

## Priorités de livraison

1. Faire fonctionner un socle indépendant, avec les composants et styles de DevDoc.
2. Afficher deux entrées publiques claires : « J'apprends le numérique » et « J'accompagne des personnes ».
3. Livrer un cours complet par public et une fiche illustrée sur la CAF (choix du 15 octobre 2026).
4. Permettre au responsable éditorial de corriger un cours, un lien et une capture dans le back-office.
5. Vérifier le parcours sur mobile, au clavier et en connexion lente ou coupée.

Le suivi de progression, les attestations, les réservations et les statistiques sont hors du premier périmètre. La génération de cours passe par l'assistant de rédaction (MCP, voir le plan) : un agent publie seulement après les contrôles bloquants et une relecture favorable d'un autre connecteur. Si une demande ultérieure les rend nécessaires, mettre à jour le plan avant de les introduire.

## Travail sur le code

- Avant une reprise de DevDoc, identifier le commit source, examiner ses dépendances et vérifier les droits de réutilisation du code et des médias. Importer seulement des fichiers suivis et nécessaires ; exclure secrets, `.env` réels, bases, sauvegardes, dépendances installées et builds.
- Garder le frontend et le backend de ce site séparés de ceux de DevDoc : dépôts, bases et déploiements distincts.
- Réutiliser d'abord les composants de navigation, cours, recherche et QCM de DevDoc. Adapter les textes et données aux deux publics, sans refaire inutilement l'interface.
- Pour les fonds d'interface (cartes, boutons, barres de navigation et états survol/focus/actif), utiliser uniquement des couleurs claires ou pastel. Ne jamais appliquer de couleur foncée ou saturée en arrière-plan ; garder un texte suffisamment contrasté et foncé pour rester lisible.
- Les captures de services extérieurs utilisent des pages publiques ou des données entièrement fictives. Chaque image a une source, une date, une légende et une alternative textuelle. Ne jamais publier les données d'un usager réel.
- Ne pas présenter ce site comme un portail officiel de la CAF, de France Travail ou d'une administration. Les fiches pointent vers les services officiels et signalent quand un parcours a changé.
- Pour un changement de données ou d'accès, vérifier les effets sur l'API, le back-office, le cache hors ligne et les tests. Les contenus non publiés ne doivent pas être exposés par l'API publique : `PublishedContentExtension` filtre les cours par statut et les contenus réservés (cours, parcours, modules, QCM) par destinataire ; le plan du site suit les mêmes règles. Le cache hors ligne est vidé à la déconnexion.
- Tout contenu pédagogique respecte le référentiel `regles/` (posture, limites du rôle, données personnelles, langage clair, accessibilité, captures). Une règle se modifie dans `regles/` avec sa source, pas dans le code.
- L'assistant de rédaction est un connecteur MCP du site (`/mcp`, `src/Mcp/`, SDK `mcp/sdk`), ajouté dans claude.ai ou ChatGPT et autorisé par OAuth par un administrateur (`src/Service/OAuth/`, voir `docs/fonctionnalites/connecteur.md`). Il prépare des brouillons (`src/Service/Redaction/RedactionService.php`) et les publie (`publier_cours`) si les contrôles de `DraftChecker.php` passent et si un autre connecteur a donné un avis favorable ; un cours publié reste modifiable par l'agent. Chaque connecteur est un agent distinct : la relecture vient d'un autre connecteur que l'auteur. Les QCM suivent le même circuit (`src/Service/Redaction/QcmDraftService.php`, `publier_qcm`, règles `regles/06-qcm.md`) : un brouillon `QcmDraft` reste hors de l'API publique jusqu'à sa publication, qui crée le `QCM` et ses `ChoicesQCM` ; il est consultable par le responsable dans le back-office, sous QCM → Brouillons du connecteur.
- Les images d'un cours passent uniquement par les captures du back-office (marqueur `[capture:ID]`, guide : `docs/fonctionnalites/edition.md`).
- La capture automatique (`capture/`, Playwright et Chromium) s'exécute dans un service Docker isolé sur Netcup ; le site OVH l'appelle en HTTPS avec un jeton et conserve seul les images et leurs métadonnées. Elle accepte tous les domaines publics en https sans liste prédéfinie, mais refuse les adresses locales ou privées pour protéger le serveur. Conserver les garde-fous de lecture seule (GET uniquement), le refus des écrans de connexion, les cookies jamais acceptés et ne jamais contourner une protection anti-robots (`CAP-08`) : la capture se fait alors à la main.

## Validation et compte rendu

- Avant toute modification, constater les fichiers présents et l'état Git du dépôt concerné. Préserver les changements préexistants.
- Les commandes réelles de validation sont dans `docs/harness/index.md`. Pendant l'itération : `make check-fast` (syntaxe PHP, PHPStan, ESLint). **Une tâche de code n'est terminée que si `make check` passe** (ajoute PHPUnit, Vitest et le build Vite) ; un échec, même préexistant, est rapporté avec sa sortie. Les hooks `.agents/hooks/` lintent le fichier modifié et rappellent `make check` en fin de tâche. Pour les parcours publics, tester aussi le navigateur sur mobile et le mode hors ligne.
- Tout changement d'entité passe par une nouvelle migration Doctrine ; ne jamais modifier une migration déjà commitée.
- Dans le compte rendu, distinguer ce qui est présent dans le code, ce qui a été testé localement, ce qui a été vérifié en CI et ce qui a été vérifié sur le site déployé. Signaler les contrôles non effectués.
- Mettre à jour le plan et la documentation seulement lorsque le comportement ou une décision durable change.

## Documentation VitePress et dossier VAE

Le site de documentation (`docs/`, VitePress, publié sous `/docs/`) décrit le fonctionnement réel du projet ; le dossier `VAE/` (TP CDA, RNCP37873) en tire la synthèse et le registre des preuves. Toute modification **validée** (`make check` réussi) est reportée dans `docs/` si elle change ce que le site décrit. Un refactoring interne sans effet visible n'en a pas besoin : le dire dans le compte rendu.

| Changement | Page |
|---|---|
| Fonctionnalité, écran ou parcours | `docs/fonctionnalites/index.md` (et `edition.md` ou `connecteur.md` si l'usage change) |
| Besoin, publics, modèle de contenu | `docs/conception/index.md` |
| Couches, entités, migrations, intégrations | `docs/architecture/index.md` |
| Rôles, accès, OAuth, garde-fous | `docs/securite/index.md` |
| Tests | `docs/tests/index.md` |
| CI, déploiement OVH ou Netcup, variables d'environnement | `docs/deploiement/index.md` |
| Harness (consignes, hooks, permissions, skill, cibles `make`) | `docs/harness/index.md` |
| Preuve d'une compétence VAE | `VAE/preuves.md`, `VAE/matrice-competences.md`, entrée datée dans `VAE/journal.md` |

- Nouvelle page : l'ajouter à la barre latérale (`docs/.vitepress/config.mjs`). Liens vers le code en chemins relatifs (réécrits vers GitHub au build).
- Badges `<Status is="code|local|ci|prod|prevu" />` fidèles à la preuve réelle (définitions : `VAE/preuves.md`) : pas de « En production » sans contrôle du site déployé, pas de « Validé par la CI » sans run identifié.
- Vérifier avec `make docs-build` (échoue sur un lien interne mort) et rapporter le résultat ; `make docs-dev` sert le site sur <http://localhost:5176/docs/>.
- À la fin d'une tâche technique terminée qui peut affecter la preuve VAE, appliquer le skill `vae-documentation-sync` (`.claude/skills/`, exposé à Codex par `.agents/skills/`). Aucune preuve, mesure ni exécution du projet DevDoc n'est reprise.
- `VAE/evidence/` contient des preuves archivées immuables : un nouveau contrôle crée un nouveau dossier daté.

## Réponses Git demandées par l'utilisateur

- Si l'utilisateur demande uniquement un nom de commit, répondre par une seule ligne prête à copier au format Conventional Commits anglais : `type(scope): short description`.
- S'il demande uniquement quels fichiers ajouter, répondre par une seule commande `git add <file1> <file2> ...` avec les seuls fichiers pertinents modifiés, sans explication.
