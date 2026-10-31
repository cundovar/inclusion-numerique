# Captures d'écran des outils et des sites

Règles du projet, issues du plan produit et de `AGENTS.md`, sauf mention contraire.

## CAP-01 — Pages publiques seulement

**Règle.** On capture des pages publiques en https, sans se connecter et sans saisir de formulaire. Aucun domaine n'est prédéfini : le connecteur peut demander une capture sur tout site public. Le moteur refuse les adresses locales ou privées pour protéger le serveur, toute requête autre qu'une lecture, et les écrans de connexion.

## CAP-02 — Une capture dit d'où elle vient

**Règle.** Chaque capture a : outil (service), adresse de la page officielle, date de capture, auteur, légende, texte alternatif et date de prochaine vérification.

**Dans le back-office.** Champs obligatoires du menu « Captures ».

## CAP-03 — Une conversation montrée est fictive

**Règle.** Un écran visible seulement après connexion (conversation, historique, réglages du compte) n'est jamais capturé automatiquement. On montre une conversation préparée exprès avec des données inventées (voir `DON-01`), sur un compte d'atelier sans historique personnel, ou une illustration fictive signalée comme telle sur le site.

## CAP-04 — Le site officiel reste la référence

**Règle.** Une capture ne doit pas laisser croire que ce site est lié à l'éditeur de l'outil. Le cours renvoie vers la page officielle de l'outil, et ne reprend pas son identité visuelle (logo, couleurs) hors des captures.

## CAP-05 — Revérifier régulièrement

**Règle.** Chaque capture a une date de prochaine vérification. Les interfaces changent : viser une vérification tous les trois à six mois. Si l'interface a changé, le cours passe « À réviser » jusqu'à la mise à jour de la capture et du texte.

## CAP-06 — Droits de réutilisation

**Règle.** Avant la première publication des captures d'un outil, le responsable éditorial vérifie les conditions de réutilisation de ses éléments graphiques et la manière d'attribuer la source.

## CAP-07 — Repères numérotés

**Règle.** L'élément à repérer est signalé par un repère numéroté sur la capture, et le texte reprend le même numéro (« Repère 1 : la zone de saisie du message »).

**Source.** Le plan du projet ; le RGAA critère 3.1.

## CAP-08 — Ne jamais contourner une protection anti-robots

**Règle.** Si un site bloque les navigateurs automatiques (page de vérification, captcha, refus d'accès), on ne cherche pas à passer outre : ni changement d'identité du navigateur, ni service de résolution, ni nouvelles tentatives en boucle. La capture est faite à la main par une personne, dans un navigateur ordinaire, puis déposée avec sa source et sa date (`CAP-02`).

**Pour l'assistant.** Le refus `PROTECTION_ANTI_ROBOT` de `capturer_page` est définitif : signaler dans le brouillon la capture à faire à la main (page, repères, légende proposée).

**À noter.** Plusieurs services protègent leurs pages contre les navigateurs automatiques : leurs captures se font alors à la main.

## CAP-09 — Bandeau de cookies

**Règle.** Un bandeau de consentement n'est jamais accepté pour faire une capture. On le refuse ou on le masque, pour que la capture montre la page telle qu'elle est utile à l'apprenant.
