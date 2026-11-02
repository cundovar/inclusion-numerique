# Connecteur MCP

Inclusion-numerique n'a pas de connecteur MCP. Les cours se rédigent avec le connecteur de **mediateur**, ajouté dans claude.ai ou ChatGPT à l'adresse `https://mediateur.varascundo.com/mcp`.

Les deux sites partagent la base `egflbugcundo6` et le dossier des images (`public/uploads`, lien vers celui de mediateur) : un cours créé, modifié, publié ou retiré par ce connecteur, ou une capture qu'il ajoute, apparaît sur les deux sites. Voir [Déploiement](../deploiement/index.md#site-inclusion-numerique-branche-main2).

## Ce qui a été retiré de ce site

| Élément | Avant |
|---|---|
| Serveur MCP | `src/Mcp/`, `/mcp` |
| Serveur OAuth et pare-feu | `src/Service/OAuth/`, `src/Controller/OAuthController.php`, `src/Security/OAuthBearerAuthenticator.php`, `templates/oauth/` |
| Rédaction et contrôles du connecteur | `src/Service/Redaction/`, brouillons de QCM (`QcmDraft`, écran du back-office) |
| Captures automatiques | `src/Service/Capture/`, `capture/`, `capture-service/`, `deploy/netcup-capture/`, job CI « Moteur de capture », Node et Chromium de l'image Docker |
| Dépôt d'image et commande `app:connecteurs` | `src/Controller/ImageDepositController.php`, `src/Command/ConnectorsCommand.php` |
| Dépendance | `mcp/sdk` |

Les tables du connecteur (`oauth_client`, `oauth_grant`, `oauth_authorization_code`, `qcm_draft`) restent dans la base partagée, où mediateur s'en sert : `DOCTRINE_SCHEMA_FILTER` les fait ignorer par Doctrine. Le guide de rédaction `regles/` est conservé, comme référence pour écrire les cours.

[`RemovedFeaturesTest`](../../tests/Functional/RemovedFeaturesTest.php) vérifie qu'aucune route `/mcp`, `/oauth`, `/.well-known/oauth` ni `/depot-image` n'existe. <Status is="local" />
