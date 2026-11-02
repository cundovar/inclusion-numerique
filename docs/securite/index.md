# Sécurité

Mesures présentes dans le code et leurs tests. Les tests cités passent dans le job « PHP » du run CI [39493348934](https://github.com/cundovar/mediateur-numerique-formation/actions/runs/39493348934).

## Authentification et rôles

Configuration : [`config/packages/security.yaml`](../../config/packages/security.yaml). <Status is="ci" />

| Zone | Accès | Mécanisme |
|---|---|---|
| `/admin` (back-office) | `ROLE_ADMIN` | Session, formulaire de connexion (`src/Security/AuthAuthenticator.php`) |
| `/user-api` | `ROLE_USER` | Compte apprenant |
| `/api` en écriture | `ROLE_ADMIN` | L'API publique est en lecture seule |

- Mots de passe hachés par l'algorithme `auto` de Symfony ; déconnexion protégée par jeton CSRF.
- Mot de passe oublié : lien à usage unique valable une heure (`src/Service/Account/PasswordResetService.php`, `ResetPasswordTest`).
- Tests : [`AccessControlTest`](../../tests/Functional/Security/AccessControlTest.php), [`RemovedFeaturesTest`](../../tests/Functional/RemovedFeaturesTest.php) (aucune route `/mcp` ni `/oauth`).

## Contenus non publiés et réservés

`src/Infrastructure/ApiPlatform/PublishedContentExtension.php` filtre l'API publique : un cours non publié n'est jamais exposé, un contenu réservé (cours, parcours, module, QCM) n'est visible que de son destinataire. Le plan du site et les PDF (`src/Service/CourseVisibility.php`) suivent les mêmes règles. Tests : `PublishedContentApiTest`, `PersonalizedContentApiTest`, `CoursePdfTest`. <Status is="ci" />

## Assistant de rédaction et captures

Pas de connecteur MCP ni de moteur de capture sur ce site : la rédaction passe par le connecteur de mediateur, qui applique ses propres contrôles (voir [Connecteur MCP](../fonctionnalites/connecteur.md)).

## Secrets et contenus

- `.env` réels hors dépôt (`.gitignore`), modèle sans secret dans [`docs/env.example`](../env.example) ; le `.env` de production est créé sur le serveur.
- HTML des contenus nettoyé à l'enregistrement (`src/EventListener/PageContentSanitizerListener.php`, `src/Service/HtmlSanitizerService.php`) et dans les PDF (`src/Service/Pdf/CourseHtmlBuilder.php`).
- Le harness des agents refuse l'écriture des fichiers sensibles ([harness](../harness/index.md)). <Status is="code" />
