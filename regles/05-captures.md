# Captures d'écran des services

Règles du projet, issues du plan produit et de `AGENTS.md`, sauf mention contraire.

## CAP-01 — Pages publiques seulement

**Règle.** On capture des pages publiques en https, sans se connecter et sans saisir de formulaire. Aucun domaine n'est prédéfini : le connecteur peut demander une capture sur tout site public. Le moteur refuse les adresses locales ou privées pour protéger le serveur, toute requête autre qu'une lecture, et les écrans de connexion.

## CAP-02 — Une capture dit d'où elle vient

**Règle.** Chaque capture a : service, adresse de la page officielle, date de capture, auteur, légende, texte alternatif (voir `ACC-01`) et date de prochaine vérification.

**Dans le back-office.** Champs obligatoires du menu « Captures ».

## CAP-03 — Un écran personnel devient une illustration fictive

**Règle.** Un écran visible seulement après connexion (espace personnel, dossier, messages) n'est jamais capturé. On le remplace par une illustration fictive, signalée comme telle sur le site, avec des données inventées (voir `DON-04`).

## CAP-04 — Le site officiel reste la référence

**Règle.** Une capture ne doit pas laisser croire que ce site est un service officiel. Le cours renvoie vers la page officielle pour faire la démarche, et ne reprend pas l'identité visuelle du service hors des captures.

## CAP-05 — Revérifier régulièrement

**Règle.** Chaque capture a une date de prochaine vérification. Si la page officielle a changé, le cours passe « À réviser » jusqu'à la mise à jour de la capture et du texte.

## CAP-06 — Droits de réutilisation

**Règle.** Avant la première publication des captures d'un service, le responsable éditorial vérifie les conditions de réutilisation de ses éléments graphiques et la manière d'attribuer la source.

## CAP-07 — Repères numérotés

**Règle.** L'élément à repérer est signalé par un repère numéroté sur la capture, et le texte reprend le même numéro (« Repère 1 : le bouton "Mon Compte" »).

**Source.** [PLAN] ; [RGAA] critère 3.1 (voir `ACC-03`).

## CAP-08 — Ne jamais contourner une protection anti-robots

**Règle.** Si un site bloque les navigateurs automatiques (page de vérification, captcha, refus d'accès), on ne cherche pas à passer outre : ni changement d'identité du navigateur, ni service de résolution, ni nouvelles tentatives en boucle. La capture est faite à la main par une personne, dans un navigateur ordinaire, puis déposée avec sa source et sa date (`CAP-02`).

**Pour l'assistant.** Le refus `PROTECTION_ANTI_ROBOT` de `capturer_page` est définitif : signaler dans le brouillon la capture à faire à la main (page, repères, légende proposée).

**Constat du 15 octobre 2026.** Le site de la CAF redirige les navigateurs automatiques vers une page de vérification : ses captures se font à la main.

## CAP-09 — Bandeau de cookies

**Règle.** Un bandeau de consentement n'est jamais accepté pour faire une capture. On le refuse ou on le masque, pour que la capture montre la page telle qu'elle est utile à l'apprenant.
