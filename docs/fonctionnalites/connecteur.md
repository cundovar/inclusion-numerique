# Connecteur MCP de l'assistant de rédaction

Le site expose un serveur MCP à l'adresse `https://<site>/mcp`. On l'ajoute comme **connecteur** dans claude.ai ou dans ChatGPT, puis on s'y connecte une fois avec un compte **administrateur** du back-office. Il n'y a pas de clé à copier.

Le connecteur prépare des cours conformes au référentiel [`regles/`](../../regles/README.md), puis les **publie** avec `publier_cours`, seulement si aucun contrôle bloquant ne reste et si **un autre connecteur** a donné un avis **favorable**. Il peut aussi confier le cours à une personne (`soumettre_en_revue`). Il prépare et publie aussi des **QCM** selon la même règle (voir [QCM](#qcm)).

## Mise en service

1. Déployer le site : les migrations créent les tables `oauth_client`, `oauth_authorization_code`, `oauth_grant` et `qcm_draft`.
2. Dans le `.env` du serveur, indiquer l'adresse publique du site, en https et sans `/` final :

   ```
   MCP_PUBLIC_URL=https://<site>
   ```

   Sans ce réglage, l'adresse vient de la requête : derrière un proxy, elle risque d'être annoncée en `http`, et les connecteurs refuseront alors la connexion.
3. Vérifier que `https://<site>/.well-known/oauth-authorization-server` répond en JSON.

`OAUTH_REDIRECT_HOSTS` (par défaut `claude.ai,claude.com,chatgpt.com`) liste les sites vers lesquels la connexion peut revenir. Un connecteur qui annonce une autre adresse de retour est refusé.

## Ajouter le connecteur

**claude.ai** (et Claude Desktop, qui partage les connecteurs du compte) : Paramètres → Connecteurs → Ajouter un connecteur personnalisé → nom « Médiation numérique », adresse `https://<site>/mcp`. Cliquer sur « Se connecter » : la page de connexion du site s'ouvre, puis la page « Autoriser « Claude » ». Accepter.

**ChatGPT** : activer le mode développeur (Paramètres → Applications et connecteurs → Paramètres avancés), puis Créer un connecteur → adresse `https://<site>/mcp`, authentification OAuth. Se connecter, puis accepter de la même façon.

Les menus de ces services changent souvent : en cas de doute, chercher « connecteur personnalisé MCP » dans leur aide.

## Deux connecteurs, deux agents

Un brouillon doit être relu par **un autre connecteur** que celui qui l'a rédigé. Le site le vérifie : chaque connecteur a sa propre identité (`connecteur:Claude#…`, `connecteur:ChatGPT#…`), enregistrée comme auteur ou comme relecteur. Toute modification du brouillon efface la relecture.

1. Dans Claude, lancer la consigne **`rediger_cours`** (sujet, public). L'assistant lit les règles et le gabarit, rédige, prépare les captures, crée le brouillon, puis corrige jusqu'à ne plus avoir de contrôle bloquant.
2. Dans ChatGPT, lancer la consigne **`relire_brouillon`** (identifiant du brouillon). L'agent relecteur applique la grille du médiateur et enregistre son avis, sans modifier le cours.
3. Revenir dans Claude : corriger si besoin (une modification demande une nouvelle relecture), puis `publier_cours` après un avis favorable. Le cours est aussitôt visible sur le site.
4. Variante : `soumettre_en_revue` (avis favorable ou avec réserves) ; le cours apparaît dans « Cours à relire » du back-office, et une personne le publie.

On peut inverser les rôles. Deux connecteurs ajoutés dans le même outil fonctionnent aussi : ce sont deux agents distincts.

## Outils

| Outil | Rôle |
| --- | --- |
| `lister_catalogue` | Parcours (avec leur public), modules, niveaux, sites capturables |
| `creer_parcours`, `creer_module`, `creer_niveau` | Structure du catalogue, à la demande de la personne ; un doublon est refusé avec l'identifiant existant. Un parcours n'apparaît sur le site qu'avec un cours publié |
| `lire_regles` | Statut du référentiel, liste des règles, ou texte de règles précises avec leurs sources |
| `obtenir_modele` | Gabarit `cours-apprendre`, `cours-accompagner` ou `fiche-service` |
| `lister_brouillons`, `voir_brouillon` | Cours en brouillon, en revue ou à réviser |
| `lister_cours_publies` | Cours déjà visibles sur le site, avec leurs identifiants |
| `creer_brouillon`, `modifier_brouillon` | Crée un brouillon ; modifie un brouillon, un cours « à réviser » ou un cours publié |
| `verifier_brouillon` | Contrôles automatiques ([`regles/controles.md`](../../regles/controles.md)), avec le titre des règles concernées |
| `enregistrer_relecture` | Avis du relecteur (autre connecteur) : `favorable`, `reserves` ou `defavorable` |
| `publier_cours` | Publication si aucun contrôle bloquant, une relecture favorable d'un autre connecteur et un module (sauf `sansModule: true`, choix explicite) |
| `mettre_en_brouillon` | Retire du site un cours publié, ou tous les cours publiés d'un module ou d'un parcours (`type` : `cours`, `module`, `parcours`), en les repassant en brouillon ; un module ou un parcours sans cours publié disparaît du site. Rien n'est supprimé : le contenu et la relecture sont gardés, et `publier_cours` remet chaque cours en ligne (une modification demande une nouvelle relecture) |
| `classer_cours` | Range ou déplace jusqu'à 100 cours, publiés compris, dans un module du même parcours (`null` : hors module), sans toucher au contenu, au statut ni à la relecture. L'ordre des identifiants fixe le rang des cours dans le module (1, 2, 3…) ; renvoie les cours classés (ancien et nouveau module), ceux déjà en place, leur rang (`ordres`) et les refus |
| `ordonner_parcours` | Fixe l'ordre des parcours dans le catalogue d'après l'ordre des identifiants (rang 1, 2, 3…) ; les parcours non cités passent après, par ordre alphabétique ; renvoie le rang de chaque parcours et les refus |
| `definir_illustration_parcours` | Associe une illustration existante (type `illustration`, jamais une capture) à un parcours pour sa miniature sur la carte du catalogue ; `null` retire la miniature. Nouvelle image : `preparer_depot_image` (type illustration), envoi du fichier, puis cet outil avec l'identifiant renvoyé |
| `remplacer_capture` | Remplace dans un cours, même publié, le marqueur d'une capture par une autre ; refusé si le cours est en relecture humaine ou si le remplacement ajoute un contrôle bloquant ; la relecture est gardée |
| `soumettre_en_revue` | Passage « En revue » si aucun contrôle bloquant et une relecture non défavorable |
| `lister_captures` | Captures existantes, pour les réutiliser |
| `capturer_page` | Capture d'une page officielle autorisée, avec repères numérotés (voir plus bas) |
| `preparer_depot_image` | Fiche d'une image de l'ordinateur et adresse de dépôt à usage unique (voir plus bas) |

Consignes : `rediger_cours`, `relire_brouillon`, `rediger_qcm`, `relire_qcm`. Ressources : chaque fichier de `regles/` sous `regles:///<fichier>`.

Règles appliquées par le site, quel que soit l'assistant :

- le statut est toujours fixé par le site : « brouillon » à la création ;
- le contenu HTML est nettoyé à l'enregistrement, comme dans le back-office ;
- un cours « en revue » ne peut plus être modifié par le connecteur ;
- un cours publié reste modifiable : la modification est visible tout de suite et efface la relecture enregistrée ;
- pour retrouver un cours publié à corriger, utiliser `lister_cours_publies`, puis `voir_brouillon` et `modifier_brouillon` avec son identifiant ;
- un cours sans module apparaît dans un chapitre générique « Cours » : pour une série, l'assistant propose des modules par thème et y range chaque cours avant de le publier.

### Image de l'ordinateur

Le connecteur tourne sur le serveur : il ne voit pas les fichiers de l'ordinateur. Une image locale arrive donc en deux temps.

1. `preparer_depot_image` : l'agent remplit la fiche (`type` `capture` ou `illustration`, `legende`, `texteAlternatif` ; pour une capture, `pageSource` et `dateCapture`). Le site la vérifie et renvoie une adresse `https://<site>/depot-image/<jeton>`, valable 10 minutes et à usage unique.
2. Le fichier part dans le champ `image` de cette adresse :

   ```bash
   curl -sS -F "image=@souris.png" https://<site>/depot-image/<jeton>
   ```

   La réponse (201) donne le marqueur `[capture:ID]`. Formats et poids : ceux du back-office (PNG, JPEG ou WebP, 5 Mo au plus). Un fichier refusé (422) laisse l'adresse valable ; une image déjà enregistrée est refusée (409) avec son marqueur existant ; une adresse expirée ou déjà utilisée répond 404.

Seul un agent qui a accès aux fichiers de l'ordinateur fait l'envoi : Claude Code (onglet Code de Claude Desktop), ou la personne avec `curl`. Dans claude.ai ou ChatGPT, une image glissée dans la conversation ne peut pas être transmise : on la dépose dans le back-office (menu « Captures »), puis le brouillon la cite par son marqueur. Le serveur web doit accepter des envois de 5 Mo (`upload_max_filesize`, `post_max_size`, et `client_max_body_size` sous Nginx).

### Capture automatique

`capturer_page` demande au site d'ouvrir une page publique en HTTPS dans un navigateur sans affichage (moteur `capture/`, Playwright et Chromium exécuté dans le service Netcup), de placer les repères et d'enregistrer la capture avec sa source, sa date et son auteur. Les adresses locales ou privées, les pages de connexion et les demandes autres que la lecture sont refusées.

- `url` : adresse https publique, sans identifiants. Aucun domaine n'est prédéfini ; les adresses locales et privées sont refusées pour protéger le serveur.
- `format` : `ordinateur` (1280×800) ou `mobile` (390×844).
- `elements` : 9 au plus, `repere` de 1 à 9 ; `texte` (texte visible de l'élément) **ou** `selecteur` CSS.
- `legende` et `texteAlternatif` obligatoires ; `aRevoirLe` est fixé par défaut à dans trois mois.

Garde-fous (`CAP-01`, `CAP-03`, `CAP-08`, `CAP-09`) :

- seules les requêtes GET partent, et les adresses locales ou privées sont refusées ;
- une page avec un champ mot de passe visible est refusée ;
- les bandeaux de cookies sont refusés ou masqués, jamais acceptés ;
- une protection anti-robots n'est jamais contournée.

| Code de refus | Sens |
| --- | --- |
| `ADRESSE_REFUSEE` | Adresse en http, avec identifiants, ou vers une destination locale ou privée |
| `REDIRECTION_REFUSEE` | La page redirige vers une adresse qui ne respecte pas ces conditions |
| `ECRAN_CONNEXION` | Écran de connexion : faire une illustration fictive (`CAP-03`) |
| `PROTECTION_ANTI_ROBOT` | Le site bloque les navigateurs automatiques : capture à la main (`CAP-08`) |
| `ELEMENT_INTROUVABLE` | Un repère ne correspond à aucun élément visible |
| `PAGE_INACCESSIBLE`, `DELAI_DEPASSE` | Page en erreur ou trop lente |
| `CAPTURE_INDISPONIBLE` | Le service de capture Netcup est absent, indisponible ou mal configuré |

## QCM

Un QCM pose une question, propose de 2 à 6 réponses dont une seule correcte, et explique la bonne réponse. Les règles sont dans [`regles/06-qcm.md`](../../regles/06-qcm.md) et les contrôles dans [`regles/controles.md`](../../regles/controles.md#contrôles-dun-qcm).

1. Dans Claude, lancer la consigne **`rediger_qcm`** (sujet, public). L'assistant choisit le thème et le niveau (`lister_catalogue_qcm`) ; si l'un manque, il propose une entrée et la crée après accord (`creer_theme_qcm`, `creer_niveau_qcm`), puis crée et vérifie le brouillon (`creer_brouillon_qcm`).
2. Dans ChatGPT, lancer **`relire_qcm`** (identifiant du brouillon de QCM). Le relecteur enregistre son avis avec `enregistrer_relecture_qcm`.
3. Revenir dans Claude : corriger si besoin (`modifier_brouillon_qcm`, qui efface la relecture), puis `publier_qcm` après un avis favorable.

| Outil | Rôle |
| --- | --- |
| `lister_catalogue_qcm` | Thèmes et niveaux du parcours QCM public, nombre de QCM publiés, adresse publique |
| `creer_theme_qcm`, `creer_niveau_qcm` | Ajoute au catalogue public un thème ou un niveau ; renvoie l'identifiant existant si le nom est déjà présent |
| `lister_brouillons_qcm`, `voir_brouillon_qcm` | Brouillons de QCM (`brouillon`, `a_corriger`, `publie`) ; leurs identifiants sont distincts de ceux des cours |
| `creer_brouillon_qcm`, `modifier_brouillon_qcm` | Écriture d'un brouillon non publié ; refusée, sans rien enregistrer, si un contrôle bloquant apparaît |
| `verifier_brouillon_qcm` | Contrôles automatiques et état de la relecture : dit si le QCM est publiable |
| `enregistrer_relecture_qcm` | Avis d'un autre connecteur ; avec des réserves ou un avis défavorable, le QCM passe `a_corriger` |
| `publier_qcm` | Crée le QCM et toutes ses réponses ensemble, une seule fois, si aucun contrôle ne bloque et si l'avis est favorable |

Ce que fait le site :

- un brouillon de QCM n'est pas une ressource de l'API : `/api/q_c_ms` ne montre que les QCM publiés ;
- un brouillon qui contient une donnée personnelle (`DON-04`) ou une structure invalide n'est jamais enregistré ;
- à la publication, la question, les réponses et les explications sont converties en HTML (texte échappé, paragraphes) et rejoignent la série publique de leur thème et de leur niveau, à l'adresse `/spa/qcm` ;
- aucune tentative ni note n'est enregistrée par le site.

Limites de cette première version :

- un thème ou un niveau manquant peut être ajouté par le connecteur après accord de la personne ; ces entrées rejoignent le catalogue public. Le thème est l'entité « langage » reprise de DevDoc ;
- un QCM publié se corrige dans le back-office, pas par le connecteur ;
- un QCM n'est pas rattaché à un cours, à un module ni à un public ; les brouillons du connecteur sont visibles dans QCM → Brouillons du connecteur, en consultation seule.

## Cours et exercices personnalisés

Un cours, un parcours ou un QCM peut être **réservé** à certains apprenants : il n'est alors servi qu'à eux (et aux administrateurs). Un apprenant crée librement son compte sur `/register` (adresse e-mail pour se connecter, pseudo affiché) ; le connecteur ne voit jamais l'adresse et retrouve ses contenus sur `/spa/mon-espace`.

1. La personne donne son pseudo au médiateur. L'assistant le retrouve avec `lister_apprenants`.
2. Pour un cours sur mesure : `creer_brouillon` avec `apprenantIds`, puis le circuit habituel (vérification, relecture par l'autre connecteur, `publier_cours`). Pour un exercice : `publier_qcm` avec `apprenantIds`, le QCM n'est jamais visible par les autres.
3. Pour un contenu existant : `attribuer_acces` (type `cours`, `parcours` ou `qcm`).

| Outil | Rôle |
| --- | --- |
| `lister_apprenants` | Comptes non administrateurs : identifiant, pseudo et nombre de contenus réservés, rien d'autre |
| `lister_acces` | Contenus réservés et leurs destinataires, éventuellement pour un seul apprenant |
| `attribuer_acces` | Réserve un contenu à des apprenants, en plus des destinataires actuels |
| `retirer_acces` | Retire des destinataires ; le contenu reste réservé, même sans destinataire (il n'est alors visible par personne) |
| `ouvrir_a_tous` | Retire la réservation et les destinataires : le contenu redevient public |

Ce que fait le site :

- `PublishedContentExtension` filtre cours, parcours, modules et QCM : un cours ou un module d'un parcours réservé suit la réservation du parcours ;
- les contenus réservés sont absents du plan du site et du nombre de cours d'un module ;
- un identifiant d'apprenant inconnu fait tout refuser, sans rien créer ni modifier ;
- à la déconnexion, le cache hors ligne du navigateur (catalogue, pages, réponses API) est vidé, pour qu'un poste partagé ne garde pas les cours d'une autre personne ;
- supprimer un compte (depuis `/spa/mon-espace`) supprime ses accès ; le contenu reste réservé.

Un contenu personnalisé suit les mêmes règles que les autres : jamais de nom, d'adresse ni de numéro réel de la personne (`DON-04`) ; le pseudo suffit pour lui attribuer.

## Sécurité

- **Qui peut connecter un connecteur :** seuls les comptes `ROLE_ADMIN`. Un accès s'arrête dès que la personne perd ce rôle.
- **OAuth 2.1 :**
  - enregistrement dynamique (RFC 7591), limité aux adresses de retour de `OAUTH_REDIRECT_HOSTS`, et à 30 enregistrements par heure ;
  - code d'autorisation à usage unique, valable 5 minutes, avec PKCE S256 obligatoire ; un code rejoué coupe l'accès qu'il a ouvert ;
  - jeton d'accès valable 1 heure ; jeton de rafraîchissement valable 30 jours, renouvelé à chaque usage ; révocation au point `/oauth/revoke` (RFC 7009).
- **Stockage :** seules les empreintes SHA-256 des codes et des jetons sont enregistrées.
- **Métadonnées :** `/.well-known/oauth-protected-resource` (RFC 9728) et `/.well-known/oauth-authorization-server` (RFC 8414). Sans jeton, `/mcp` répond 401 avec `WWW-Authenticate: Bearer resource_metadata="…"`.
- **Couper un accès depuis le serveur :**

  ```bash
  php bin/console app:connecteurs                       # connecteurs, dernier usage, accès actifs
  php bin/console app:connecteurs revoke <identifiant>  # début de l'identifiant, 8 caractères au moins
  ```

- **Hébergement Apache (OVH) :** `public/.htaccess` transmet l'en-tête `Authorization` à PHP. Sans cette règle, tous les jetons seraient refusés.
