# Sécurité

Mesures présentes dans le code et leurs tests. Les tests cités passent dans le job « PHP » du run CI [39493348934](https://github.com/cundovar/mediateur-numerique-formation/actions/runs/39493348934).

## Authentification et rôles

Configuration : [`config/packages/security.yaml`](../../config/packages/security.yaml). <Status is="ci" />

| Zone | Accès | Mécanisme |
|---|---|---|
| `/admin` (back-office) | `ROLE_ADMIN` | Session, formulaire de connexion (`src/Security/AuthAuthenticator.php`) |
| `/mcp` (connecteur) | `ROLE_AGENT_REDACTION` | Jeton OAuth Bearer, sans session (`src/Security/OAuthBearerAuthenticator.php`) |
| `/oauth/authorize` | `ROLE_ADMIN` | Un administrateur autorise le connecteur une fois |
| `/user-api` | `ROLE_USER` | Compte apprenant |
| `/api` en écriture | `ROLE_ADMIN` | L'API publique est en lecture seule |

- Mots de passe hachés par l'algorithme `auto` de Symfony ; déconnexion protégée par jeton CSRF.
- Mot de passe oublié : lien à usage unique valable une heure (`src/Service/Account/PasswordResetService.php`, `ResetPasswordTest`).
- Tests : [`AccessControlTest`](../../tests/Functional/Security/AccessControlTest.php), [`OAuthFlowTest`](../../tests/Functional/OAuthFlowTest.php).

## Contenus non publiés et réservés

`src/Infrastructure/ApiPlatform/PublishedContentExtension.php` filtre l'API publique : un cours non publié n'est jamais exposé, un contenu réservé (cours, parcours, module, QCM) n'est visible que de son destinataire. Le plan du site et les PDF (`src/Service/CourseVisibility.php`) suivent les mêmes règles. Tests : `PublishedContentApiTest`, `PersonalizedContentApiTest`, `CoursePdfTest`. <Status is="ci" />

## Assistant de rédaction

- Un connecteur ne publie qu'après les contrôles bloquants (`src/Service/Redaction/DraftChecker.php`, `QcmDraftChecker.php`) et l'avis favorable d'un **autre** connecteur.
- `PersonalDataChecker` refuse les données personnelles dans un brouillon (`tests/Service/PersonalDataCheckerTest.php`).
- Les accès se listent et se coupent par `php bin/console app:connecteurs` (`ConnectorsCommandTest`).

<Status is="ci" />

## Capture automatique

Garde-fous du moteur (`capture/src/capturer.js`, règles `regles/05-captures.md`) : pages publiques en HTTPS uniquement, adresses locales et privées refusées, requêtes GET seulement, écrans de connexion refusés, cookies jamais acceptés, aucune tentative de contourner une protection anti-robots (`CAP-08`). Le service Netcup exige un jeton (`CAPTURE_SERVICE_TOKEN`). Tests : `capture/test/`, `capture-service/test/`. <Status is="ci" />

## Secrets et contenus

- `.env` réels hors dépôt (`.gitignore`), modèle sans secret dans [`docs/env.example`](../env.example) ; le `.env` de production est créé sur le serveur.
- HTML des contenus nettoyé à l'enregistrement (`src/EventListener/PageContentSanitizerListener.php`, `src/Service/HtmlSanitizerService.php`) et dans les PDF (`src/Service/Pdf/CourseHtmlBuilder.php`).
- Le harness des agents refuse l'écriture des fichiers sensibles ([harness](../harness/index.md)). <Status is="code" />
