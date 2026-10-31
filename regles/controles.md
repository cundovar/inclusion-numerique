# Contrôles automatiques

Le serveur les applique quand un brouillon est vérifié, soumis en revue ou publié par un connecteur (`src/Service/Redaction/DraftChecker.php`). Un contrôle **bloquant** empêche la soumission et la publication ; un **avertissement** est signalé au rédacteur et au relecteur.

Les règles qui demandent du jugement (justesse technique, qualité d'un exemple, clarté d'une explication) ne sont pas vérifiables ainsi : elles relèvent de la relecture (`relire_brouillon`).

| Code | Règle | Niveau | Ce qui est vérifié |
| --- | --- | --- | --- |
| `TITRE_MANQUANT` | — | bloquant | Le cours a un titre. |
| `PARCOURS_MANQUANT` | — | bloquant | Le cours appartient à un parcours. |
| `CONTENU_VIDE` | `RED-01` | bloquant | Le cours a un contenu. |
| `CAPTURE_INCONNUE` | `CAP-02` | bloquant | Chaque marqueur `[capture:ID]` désigne une capture ou une illustration du back-office. |
| `IMAGE_DIRECTE` | `CAP-02` | bloquant | Aucune balise `<img>` : les images passent par un marqueur `[capture:ID]`. |
| `SOURCE_MANQUANTE` | `CAP-04` | bloquant | Un cours qui montre une capture d'un outil indique sa documentation officielle de référence. |
| `LIEN_OFFICIEL_MANQUANT` | `SRC-01` | bloquant | Un cours qui a une documentation de référence contient un lien vers ce site. |
| `NUMERO_SECURITE_SOCIALE`, `IBAN`, `TELEPHONE`, `CLE_API`, `COURRIEL_REEL` | `DON-01` | bloquant | Aucune de ces données, même dans un bloc de code ; courriels autorisés seulement sur `example.com`, `example.org`, `example.net` ou `.example`. |
| `MODULE_MANQUANT` | — | bloquant (à la publication) | Le cours est rangé dans un module, sauf choix explicite `sansModule`. |
| `RELECTURE_MANQUANTE`, `RELECTURE_DEFAVORABLE` | `RED-02` | bloquant (soumission et publication) | Un autre connecteur a relu le cours depuis sa dernière modification, sans avis défavorable. |
| `RELECTURE_AVEC_RESERVES` | `RED-02` | bloquant (à la publication) | La publication par le connecteur demande un avis favorable. |
| `SOURCE_MANQUANTE` | `SRC-01` | avertissement | Une documentation officielle de référence est indiquée. |
| `CODE_SANS_LANGAGE` | `COD-01` | avertissement | Chaque bloc `<pre><code>` indique son langage (`class="language-…"`). |
| `PHRASE_LONGUE` | `RED-01` | avertissement | Phrases de plus de 25 mots, hors blocs de code. |
| `SANS_EXERCICE` | `RED-03` | avertissement | Le cours contient une partie « À vous », « Exercice » ou « En pratique ». |

## Contrôles d'un QCM

Le serveur les applique à chaque création ou modification d'un brouillon de QCM, à sa vérification et à sa publication (`src/Service/Redaction/QcmDraftChecker.php`). Un brouillon qui a un contrôle **bloquant** n'est pas enregistré.

| Code | Règle | Niveau | Ce qui est vérifié |
| --- | --- | --- | --- |
| `QUESTION_MANQUANTE` | `QCM-01` | bloquant | Le QCM a une question. |
| `QUESTION_TROP_LONGUE`, `REPONSE_TROP_LONGUE` | `QCM-03` | bloquant | La question et chaque réponse tiennent en 255 caractères. |
| `EXPLICATION_MANQUANTE` | `QCM-04` | bloquant | Une explication générale est affichée après la réponse. |
| `THEME_MANQUANT`, `NIVEAU_MANQUANT` | — | bloquant | Le QCM a un thème et un niveau du catalogue QCM. |
| `NOMBRE_REPONSES` | `QCM-01` | bloquant | De 2 à 6 réponses. |
| `BONNE_REPONSE_UNIQUE` | `QCM-01` | bloquant | Exactement une réponse correcte. |
| `REPONSE_VIDE` | `QCM-01` | bloquant | Aucune réponse vide. |
| `REPONSES_IDENTIQUES` | `QCM-02` | bloquant | Deux réponses n'ont pas le même texte. |
| `NUMERO_SECURITE_SOCIALE`, `IBAN`, `TELEPHONE`, `CLE_API`, `COURRIEL_REEL` | `DON-01` | bloquant | Mêmes contrôles que pour un cours. |
| `RELECTURE_MANQUANTE`, `RELECTURE_AVEC_RESERVES`, `RELECTURE_DEFAVORABLE` | `RED-02` | bloquant (à la publication) | Un autre connecteur a relu le QCM depuis sa dernière modification, avec un avis favorable. |
| `QUESTION_LONGUE` | `RED-01` | avertissement | Question de plus de 25 mots. |
| `REPONSE_PIEGE` | `QCM-02` | avertissement | Réponse du type « toutes les réponses » ou « aucune des réponses ». |
| `EXPLICATION_REPONSE_MANQUANTE` | `QCM-04` | avertissement | Chaque réponse fausse a son explication. |

## Contrôles d'un diaporama

Le serveur les applique à un diaporama (`src/Service/Redaction/DiaporamaChecker.php`) ; le connecteur les utilisera à chaque création, modification, vérification et publication. Les champs de chaque modèle sont listés dans `DiaporamaChecker::MODELES`.

| Code | Règle | Niveau | Ce qui est vérifié |
| --- | --- | --- | --- |
| `DIAPO_CIBLE` | — | bloquant | Le diaporama vise un cours ou un chapitre, et un seul. |
| `DIAPO_MODELE_INCONNU` | — | bloquant | Chaque diapositive a un modèle connu. |
| `DIAPO_CHAMP_MANQUANT` | — | bloquant | Les champs du modèle sont remplis : nombre minimal de points ou de réponses, consigne, question, deux colonnes avec un titre, identifiant de capture. |
| `DIAPO_NOMBRE` | `DIA-01` | bloquant | De 6 à 12 diapositives pour un cours, de 10 à 20 pour un chapitre. |
| `DIAPO_TITRE_MANQUANT`, `DIAPO_TITRE_LONG` | `DIA-01`, `DIA-05` | bloquant | Chaque diapositive a un titre de 60 caractères au plus. |
| `DIAPO_TROP_DE_POINTS` | `DIA-01` | bloquant | 5 points au plus par diapositive, et le maximum du modèle (4 pour une notion, 4 réponses…). |
| `DIAPO_COPIE` | `DIA-02` | bloquant | Aucun passage de plus de 8 mots identiques au cours (ou aux cours du chapitre), sans tenir compte de la casse ni de la ponctuation. Les notes ne sont pas comparées. |
| `CAPTURE_INCONNUE` | `CAP-02`, `DIA-05` | bloquant | Chaque capture citée existe dans le back-office et a un texte alternatif. |
| `DIAPO_CHAPITRE_INCOMPLET` | — | bloquant | Tous les cours du chapitre sont publiés avant son diaporama. |
| `NUMERO_SECURITE_SOCIALE`, `IBAN`, `TELEPHONE`, `CLE_API`, `COURRIEL_REEL` | `DON-01` | bloquant | Mêmes contrôles que pour un cours, sur les diapositives et les notes. |
| `RELECTURE_MANQUANTE`, `RELECTURE_AVEC_RESERVES`, `RELECTURE_DEFAVORABLE` | `RED-02` | bloquant (à la publication) | Un autre connecteur a relu le diaporama depuis sa dernière modification, avec un avis favorable. |
| `DIAPO_NOTES_MANQUANTES` | `DIA-03` | bloquant | Chaque diapositive, sauf la première et la dernière, a des notes de présentation. |
| `DIAPO_POINT_LONG` | `DIA-01` | avertissement | Point de plus de 12 mots. |
| `DIAPO_NOTES_LONGUEUR` | `DIA-03` | avertissement | Notes de 300 à 900 caractères. |
| `DIAPO_SANS_PRATIQUE` | `DIA-04` | avertissement | Un diaporama de cours a une diapositive d'exercice ou de question. |
| `DIAPO_SOURCE_PERIMEE` | — | avertissement | Le cours a changé depuis le diaporama (empreinte de la source différente). |

La qualité de la synthèse, le choix des idées et la clarté des notes ne se vérifient pas automatiquement : ils relèvent de la relecture.
