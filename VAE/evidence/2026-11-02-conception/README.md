# Contrôles de conception — 2026-11-02

Archive immuable (phase 3 du plan VAE Inclusion). Version examinée : `30da5e5b98c17fa1ad3740346f53a782cd3543dc` (`main2`).

## Environnement

| Élément | Valeur |
|---|---|
| Copie | `git archive 30da5e5` extraite dans un dossier temporaire hors du dépôt ; aucun fichier du dépôt n'est modifié |
| `.env` | valeurs fictives (`APP_SECRET=iso-only-secret`, `MAILER_DSN=null://null`, `MESSENGER_TRANSPORT_DSN=sync://`), base MySQL jetable ; aucun fichier d'environnement réel lu |
| PHP | image `inclusion_numerique-php` (construite le 2026-11-02 à 11 h 36 depuis le `Dockerfile` du dépôt, `sha256:d80a5a94bece…`), PHP 8.3.36, Composer 2.11.1 ; `composer install` sur le `composer.lock` de `30da5e5` |
| MySQL | conteneur `mysql:8.0` (`sha256:f37951fc…`, MySQL 8.0.44), données en `tmpfs`, réseau Docker dédié, détruit après le contrôle |

## Fichiers

| Fichier | Contenu | Commande |
|---|---|---|
| [`mapping-info.txt`](mapping-info.txt) | 18 entités mappées, toutes `[OK]` | `php bin/console doctrine:mapping:info` (le `--dump-sql` qui suit a échoué faute de base, sortie conservée) |
| [`migrate-validate.txt`](migrate-validate.txt) | 13 migrations jouées jusqu'à `Version20261016220000` ; mapping correct ; schéma synchronisé avec le mapping | `doctrine:migrations:migrate -n` puis `doctrine:schema:validate` |
| [`schema-30da5e5.sql`](schema-30da5e5.sql) | schéma MySQL produit par les migrations : 27 tables | `mysqldump --no-data --skip-comments --skip-add-drop-table` (compteurs `AUTO_INCREMENT` et lignes `SET`/commentaires conditionnels retirés) |
| [`indicateurs-git.txt`](indicateurs-git.txt) | commits par jour, auteur et mention d'un agent IA ; PR ; types de commits ; runs CI de `main2` | commandes indiquées dans le fichier |
| [`captures.log`](captures.log) | horodatage, URL et taille de fenêtre des captures d'écran publiées dans `docs/conception/assets/` | Google Chrome sans interface (`--headless=new --screenshot`), pages publiques en lecture seule |

## Constats

- Les migrations créent aussi `oauth_client`, `oauth_grant`, `oauth_authorization_code` et `qcm_draft`, tables du connecteur de Médiateur qu'aucune entité de `main2` ne mappe ; `DOCTRINE_SCHEMA_FILTER` (`config/services.yaml`) les exclut de la validation.
- Le schéma de la base de production partagée (`egflbugcundo6`) n'a pas été observé : il évolue par les déploiements de Médiateur et contient en plus des tables de DevDoc selon `config/services.yaml`. Le schéma ci-dessus est celui que produisent les migrations de `30da5e5` sur une base vide.
- Indicateurs : « ia=non » signifie seulement qu'aucune ligne `Co-Authored-By` n'est présente ; cela ne prouve pas une écriture sans assistance.

## Corrections de l'inventaire du cadrage

L'archive [`../2026-11-02-cadrage/inventaire.md`](../2026-11-02-cadrage/inventaire.md) reste inchangée. Deux affirmations y sont inexactes :

1. **Migration et table de suivi** : `Version20261102104351` et le réglage `MIGRATIONS_TABLE`, ajoutés par `deb7543`, ont été retirés par `95d47ac`. À `30da5e5`, les 13 migrations vont de `Version20261015093531` à `Version20261016220000` ; `config/packages/doctrine_migrations.yaml` ne fixe pas de table de suivi. La table `doctrine_migration_versions_inclusion` figure seulement dans la liste d'exclusion de `DOCTRINE_SCHEMA_FILTER`.
2. **Entités et mentions IA** : `main2` compte 18 entités mappées (19 fichiers dans `src/Entity/`, dont le trait `AccesReserve`). Pour Médiateur, les 47 commits avec mention `Co-Authored-By` se répartissent en 15 commits dont l'auteur est « Claude » et 32 dont l'auteur est « Facundo VARAS » ; 39 commits de « Facundo VARAS » n'ont pas de mention (et non « 47 parmi les 71 »).
