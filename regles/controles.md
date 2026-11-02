# Contrôles automatiques

Le serveur les applique quand un brouillon est vérifié ou soumis à la relecture par l'API de rédaction (`src/Service/Redaction/DraftChecker.php`). Un contrôle **bloquant** empêche le passage « En revue » ; un **avertissement** est signalé au rédacteur et au relecteur.

Les règles qui demandent du jugement (posture, pertinence d'un texte alternatif, clarté d'une explication) ne sont pas vérifiables ainsi : elles relèvent de la relecture humaine et, à l'étape 3, d'un agent relecteur.

| Code | Règle | Niveau | Ce qui est vérifié |
| --- | --- | --- | --- |
| `TITRE_MANQUANT` | — | bloquant | Le cours a un titre. |
| `PARCOURS_SANS_PUBLIC` | — | bloquant | Le cours appartient à un parcours qui a un public (« apprendre » ou « accompagner »), sinon il n'apparaîtrait nulle part. |
| `CAPTURE_INCONNUE` | `CAP-02` | bloquant | Chaque marqueur `[capture:ID]` désigne une capture existante. |
| `IMAGE_DIRECTE` | `CAP-02`, `ACC-01` | bloquant | Aucune balise `<img>` dans le contenu : les images passent par les captures. |
| `SOURCE_MANQUANTE` | `CAP-04` | bloquant | Un cours qui montre une capture réelle a une page officielle de référence. |
| `LIEN_OFFICIEL_MANQUANT` | `CAP-04`, `LIM-06` | bloquant | Un cours qui a une page officielle de référence contient un lien vers ce site. |
| `NUMERO_SECURITE_SOCIALE` | `DON-04` | bloquant | Aucun numéro au format du numéro de sécurité sociale. |
| `IBAN` | `DON-04` | bloquant | Aucun IBAN. |
| `TELEPHONE` | `DON-04` | bloquant | Aucun numéro de téléphone français ; écrire un exemple avec des X (06 XX XX XX XX). |
| `COURRIEL_REEL` | `DON-04` | bloquant | Aucune adresse de courriel, sauf sur les domaines réservés aux exemples (`example.com`, `example.org`, `example.net`, `.example`). |
| `RELECTURE_MANQUANTE` | `RED-02` | bloquant (à la soumission) | Un autre agent (autre connecteur) a enregistré une relecture depuis la dernière modification. |
| `RELECTURE_DEFAVORABLE` | `RED-02` | bloquant (à la soumission) | La relecture n'est pas défavorable. |
| `PHRASE_LONGUE` | `RED-01` | avertissement | Phrases de plus de 25 mots. |
| `SANS_EXERCICE` | `POS-06`, `RED-03` | avertissement | Un cours « apprendre » contient une partie « À vous ». |
| `SANS_ORIENTATION` | `LIM-05` | avertissement | Un cours « accompagner » contient « Quand orienter » ; une fiche de service, « Si ça ne marche pas ». |

## Contrôles d'un QCM

Le serveur les applique à chaque création ou modification d'un brouillon de QCM, à sa vérification et à sa publication (`src/Service/Redaction/QcmDraftChecker.php`). Un brouillon qui a un contrôle **bloquant** n'est pas enregistré : rien de ce qui est refusé n'est gardé, pas même une donnée personnelle.

| Code | Règle | Niveau | Ce qui est vérifié |
| --- | --- | --- | --- |
| `QUESTION_MANQUANTE` | `QCM-01` | bloquant | Le QCM a une question. |
| `QUESTION_TROP_LONGUE`, `REPONSE_TROP_LONGUE` | `QCM-03` | bloquant | La question et chaque réponse tiennent en 255 caractères. |
| `EXPLICATION_MANQUANTE` | `QCM-04` | bloquant | Une explication générale est affichée après la réponse. |
| `THEME_MANQUANT`, `NIVEAU_MANQUANT` | — | bloquant | Le QCM a un thème et un niveau du catalogue QCM, sans quoi aucune série publique ne le propose. |
| `NOMBRE_REPONSES` | `QCM-01` | bloquant | De 2 à 6 réponses. |
| `BONNE_REPONSE_UNIQUE` | `QCM-01` | bloquant | Exactement une réponse correcte. |
| `REPONSE_VIDE` | `QCM-01` | bloquant | Aucune réponse vide. |
| `REPONSES_IDENTIQUES` | `QCM-02` | bloquant | Deux réponses n'ont pas le même texte. |
| `NUMERO_SECURITE_SOCIALE`, `IBAN`, `TELEPHONE`, `COURRIEL_REEL` | `DON-04` | bloquant | Mêmes contrôles que pour un cours, sur la question, les réponses et les explications. |
| `RELECTURE_MANQUANTE`, `RELECTURE_AVEC_RESERVES`, `RELECTURE_DEFAVORABLE` | `RED-02` | bloquant (à la publication) | Un autre agent a relu le QCM depuis sa dernière modification, avec un avis favorable. |
| `QUESTION_LONGUE` | `RED-01` | avertissement | Question de plus de 25 mots. |
| `REPONSE_PIEGE` | `QCM-02` | avertissement | Réponse du type « toutes les réponses » ou « aucune des réponses ». |
| `EXPLICATION_REPONSE_MANQUANTE` | `QCM-04` | avertissement | Chaque réponse fausse a son explication. |

La plausibilité des réponses fausses, la justesse de la bonne réponse et le ton de la correction ne se vérifient pas automatiquement : ils relèvent de la relecture (`relire_qcm`).

