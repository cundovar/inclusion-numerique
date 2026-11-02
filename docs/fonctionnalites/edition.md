# Guide d'édition du back-office

Le back-office est à l'adresse `/admin`. Le compte se crée avec `php bin/console app:create-admin <nom>`.

## Statut d'un cours

Menu **Cours**, champ **Statut** :

| Statut | Effet |
| --- | --- |
| Brouillon | En cours d'écriture. Invisible sur le site. |
| En revue | Prêt à relire. Invisible sur le site ; listé dans « Cours à relire » du tableau de bord. |
| Publié | Visible sur le site, dans l'API et dans le plan du site. |
| À réviser | Le service officiel a changé ou un usager a signalé un décalage. Le cours disparaît du site jusqu'à sa remise en « Publié ». |

L'API ne permet pas de publier : un statut change dans le back-office, ou par l'assistant de rédaction de mediateur (`publier_cours`, après une relecture favorable d'un autre connecteur, voir [le connecteur](connecteur.md)), dont les modifications s'appliquent aussi à ce site. Un brouillon préparé par l'assistant de rédaction porte « agent:… » dans **Préparé par** ; quand il est soumis, il arrive « En revue » après avoir passé les contrôles de `regles/controles.md`. La relecture vérifie le reste du référentiel `regles/`. Un administrateur connecté voit aussi les brouillons sur le site, avec un bandeau « Ce cours n'est pas publié ».

Pour un cours sur un service, remplir **Page officielle de référence** et **Vérifié le** après chaque comparaison avec le site officiel.

## Captures

Menu **Captures** :

1. **Créer** : déposer l'image (PNG, JPEG ou WebP, 5 Mo au plus) et choisir le type.
   - **Capture d'une page publique** : la page officielle et la date de capture sont obligatoires. Ne jamais capturer un écran connecté ni des données réelles.
   - **Illustration fictive** : pour montrer un écran de compte personnel. Le site la signale comme « Illustration pédagogique ».
2. Écrire la **légende** (ce qu'il faut repérer) et le **texte alternatif** (décrire l'image à quelqu'un qui ne la voit pas).
3. Fixer **À revérifier le** : à cette date, la capture apparaît dans « Captures à revérifier » du tableau de bord.
4. Copier le **marqueur** affiché dans la liste, par exemple `[capture:12]`, et le coller sur une ligne seule dans le contenu du cours.

Sur le site, le marqueur devient l'image, sa légende, « Capture du site CAF, le 15/10/2026 », un lien vers la page officielle et un lien pour agrandir l'image.

**Remplacer une capture** : ouvrir la capture, déposer le nouveau fichier, mettre à jour la date. Tous les cours qui contiennent son marqueur affichent la nouvelle image sans être modifiés. La fiche de la capture indique les cours qui l'utilisent.

Le site n'affiche aucune autre image que les captures gérées ici. Une image collée directement dans le HTML d'un cours est retirée à l'affichage.

## PDF des cours et des modules

Chaque cours se télécharge en PDF (bouton « Télécharger en PDF » sous le titre, adresse `/cours/<adresse-du-cours>.pdf`), et chaque module en fiche d'atelier qui réunit ses cours (lien « Fiche d'atelier en PDF » sur la carte du module, adresse `/module/<identifiant>.pdf`).

- Le PDF reprend le texte, le niveau, les captures avec leur légende et leur source, l'adresse du cours en ligne et la mention « Ce document n'est pas un document officiel ».
- Il n'y a rien à faire après une correction : le PDF est refait au premier téléchargement qui suit une modification du cours, de son niveau ou d'une de ses captures.
- Il suit les mêmes règles que le site : un brouillon n'est téléchargeable que par un administrateur, un contenu réservé que par ses destinataires. La fiche d'un module ne contient que les cours que la personne peut voir.
- Les grandes images sont réduites dans le PDF pour qu'il reste léger ; l'image du site n'est pas modifiée.

## Informations légales

Menu **Configuration du site**, rubrique **Informations légales** : nom de l'éditeur, adresse postale, e-mail de contact (questions et droits sur les données personnelles), directeur·rice de la publication, SIRET ou RNA (facultatif) et date de mise à jour des textes légaux.

- Ces informations s'affichent dans les **mentions légales** (`/mentions-legales`), la **politique de confidentialité** (`/privacy`) et les **conditions d'utilisation** (`/terms`), reliées par le pied de page de toutes les pages du site.
- Tant qu'un champ obligatoire est vide, ces pages affichent « À compléter » à sa place et le tableau de bord le signale.
- Changer la **date de mise à jour** à chaque modification d'un de ces textes.
- Ces informations sont publiques : ne pas y mettre d'adresse ou de téléphone personnels sans le vouloir.
