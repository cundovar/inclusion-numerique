/**
 * Exercice « Les gestes de base du téléphone ».
 * Le « Téléphone Exemple » est imaginaire : il n'imite ni Android ni iPhone.
 * Les écrans et missions sont lus par le moteur (moteur/moteurTelephone.js).
 */
const TEXTE_APPLICATION_IMAGINAIRE = 'Cette application est imaginaire. Touchez « Accueil » en bas pour revenir.'

const ecranImaginaire = (titre) => ({
  titre,
  parent: 'accueil',
  zones: [{ id: `texte-${titre.toLowerCase()}`, type: 'texte', texte: TEXTE_APPLICATION_IMAGINAIRE }],
})

export default {
  slug: 'gestes-du-telephone',
  titre: 'Les gestes de base du téléphone',
  public: 'apprendre',
  module: 'Smartphone et tablette',
  competence: '5.2 Construire un environnement numérique',
  duree: '10 minutes',
  coursSlug: null,
  consigne: 'Voici un téléphone imaginaire, le « Téléphone Exemple ». Votre téléphone peut avoir un autre aspect. Suivez les missions une par une, sans vous presser.',
  scenes: [{
    type: 'telephone',
    telephone: {
      nom: 'Téléphone Exemple',
      heure: '10:30',
      ecranDepart: 'accueil',
      valeursDepart: { volume: 3, wifi: false },
      ecrans: {
        accueil: {
          titre: 'Accueil',
          zones: [
            { id: 'app-messages', type: 'application', libelle: 'Messages', icone: 'fas fa-comment', couleur: 'bg-sky-700', aller: 'messages' },
            { id: 'app-appels', type: 'application', libelle: 'Appels', icone: 'fas fa-phone', couleur: 'bg-green-700', aller: 'appels' },
            { id: 'app-photos', type: 'application', libelle: 'Photos', icone: 'fas fa-image', couleur: 'bg-rose-700', aller: 'photos' },
            { id: 'app-meteo', type: 'application', libelle: 'Météo', icone: 'fas fa-sun', couleur: 'bg-amber-700', aller: 'meteo' },
            { id: 'app-agenda', type: 'application', libelle: 'Agenda', icone: 'fas fa-calendar', couleur: 'bg-violet-700', aller: 'agenda' },
            { id: 'app-reglages', type: 'application', libelle: 'Réglages', icone: 'fas fa-gear', couleur: 'bg-gray-700', aller: 'reglages' },
          ],
        },
        messages: {
          titre: 'Messages',
          parent: 'accueil',
          zones: [{
            id: 'liste-messages',
            type: 'liste',
            libelle: 'Messages',
            visibles: 4,
            elements: [
              { de: 'Club de marche', texte: 'Rendez-vous samedi à 9 h.' },
              { de: 'Pharmacie Exemple', texte: 'Votre commande est prête.' },
              { de: 'Léa', texte: 'Merci pour les photos !' },
              { de: 'Marc', texte: 'On se voit demain ?' },
              { de: 'Bibliothèque Exemple', texte: 'Votre livre est arrivé.' },
              { de: 'Sophie', texte: 'Bon anniversaire !' },
              { de: 'Atelier Exemple', texte: 'L\'atelier commence à 14 h.' },
              { de: 'Paul', texte: 'C\'est le dernier message de la liste.' },
            ],
          }],
        },
        reglages: {
          titre: 'Réglages',
          parent: 'accueil',
          zones: [
            { id: 'volume', type: 'curseur', libelle: 'Volume', valeur: 'volume', min: 0, max: 10, pas: 1 },
            { id: 'wifi', type: 'interrupteur', libelle: 'Wi-Fi', valeur: 'wifi' },
          ],
        },
        appels: ecranImaginaire('Appels'),
        photos: ecranImaginaire('Photos'),
        meteo: ecranImaginaire('Météo'),
        agenda: ecranImaginaire('Agenda'),
      },
    },
    missions: [
      {
        id: 'ouvrir',
        geste: 'Toucher',
        consigne: 'Ouvrez l\'application « Messages ».',
        aide: 'Touchez l\'icône « Messages » sur l\'écran d\'accueil. Un toucher bref suffit.',
        reussite: 'Bravo, l\'application « Messages » est ouverte.',
        condition: { ecran: 'messages' },
      },
      {
        id: 'defiler',
        geste: 'Glisser',
        consigne: 'Faites défiler la liste jusqu\'au dernier message.',
        aide: 'Posez le doigt sur la liste, puis glissez-le vers le haut. Vous pouvez aussi toucher « Faire défiler vers le bas ».',
        reussite: 'Bravo, vous avez vu toute la liste.',
        condition: { defilementFin: 'liste-messages' },
      },
      {
        id: 'volume',
        geste: 'Régler',
        consigne: 'Montez le volume jusqu\'à 8 ou plus. Le volume se règle dans « Réglages ».',
        aide: 'Touchez « Accueil », puis « Réglages ». Un curseur est une barre avec un rond. Faites glisser le rond vers la droite.',
        reussite: 'Bravo, le volume est monté.',
        condition: { valeur: 'volume', min: 8 },
      },
      {
        id: 'wifi',
        geste: 'Activer',
        consigne: 'Activez le Wi-Fi dans « Réglages ».',
        aide: 'Le Wi-Fi permet d\'aller sur Internet sans fil. Touchez l\'interrupteur « Wi-Fi » pour l\'activer.',
        reussite: 'Bravo, le Wi-Fi est activé.',
        condition: { valeur: 'wifi', egal: true },
      },
      {
        id: 'accueil',
        geste: 'Revenir',
        consigne: 'Revenez à l\'écran d\'accueil.',
        aide: 'Touchez le bouton « Accueil » tout en bas de l\'écran du téléphone.',
        reussite: 'Bravo, voici de nouveau l\'écran d\'accueil.',
        condition: { ecran: 'accueil' },
      },
    ],
  }],
  conclusion: {
    reussite: 'Bravo, vous avez réussi les cinq missions.',
    aRetenir: [
      'Un toucher bref ouvre une application.',
      'Pour voir la suite d\'une liste, glissez le doigt vers le haut.',
      'Un curseur se règle en faisant glisser son rond.',
      'Un interrupteur s\'active ou se désactive d\'un toucher.',
      'Le bouton « Accueil » vous ramène toujours au début.',
      'Votre téléphone peut avoir un autre aspect, mais les gestes sont les mêmes.',
    ],
  },
}
