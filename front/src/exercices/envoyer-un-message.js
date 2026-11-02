/**
 * Exercice « Envoyer un message » sur le « Téléphone Exemple ».
 * Léa et la conversation sont imaginaires. Le clavier tactile est fictif (AZERTY sans accents).
 */
const TEXTE_APPLICATION_IMAGINAIRE = 'Cette application est imaginaire. Touchez « Accueil » en bas pour revenir.'
const MESSAGE_ATTENDU = 'Oui, avec plaisir'

const ecranImaginaire = (titre) => ({
  titre,
  parent: 'accueil',
  zones: [{ id: `texte-${titre.toLowerCase()}`, type: 'texte', texte: TEXTE_APPLICATION_IMAGINAIRE }],
})

export default {
  slug: 'envoyer-un-message',
  titre: 'Envoyer un message',
  public: 'apprendre',
  module: 'Smartphone et tablette',
  competence: '2.1 Interagir',
  duree: '10 minutes',
  coursSlug: null,
  consigne: 'Léa, une personne imaginaire, vous demande si vous venez samedi. Votre réponse est déjà commencée, mais elle contient une faute. Corrigez-la, puis envoyez le message.',
  scenes: [{
    type: 'telephone',
    telephone: {
      nom: 'Téléphone Exemple',
      heure: '10:30',
      ecranDepart: 'conversation',
      valeursDepart: { brouillon: 'Oui, avec plaisur', clavierOuvert: false },
      ecrans: {
        accueil: {
          titre: 'Accueil',
          zones: [
            { id: 'app-messages', type: 'application', libelle: 'Messages', icone: 'fas fa-comment', couleur: 'bg-sky-700', aller: 'conversation' },
            { id: 'app-appels', type: 'application', libelle: 'Appels', icone: 'fas fa-phone', couleur: 'bg-green-700', aller: 'appels' },
            { id: 'app-photos', type: 'application', libelle: 'Photos', icone: 'fas fa-image', couleur: 'bg-rose-700', aller: 'photos' },
          ],
        },
        conversation: {
          titre: 'Messages',
          parent: 'accueil',
          zones: [
            { id: 'conversation-lea', type: 'conversation', avec: 'Léa', recus: ['Tu viens samedi ?'] },
            { id: 'champ-message', type: 'champ', libelle: 'Écrire un message', valeur: 'brouillon', ouvre: 'clavierOuvert' },
            {
              id: 'envoyer',
              type: 'envoyer',
              libelle: 'Envoyer',
              valeur: 'brouillon',
              attendu: MESSAGE_ATTENDU,
              refus: 'Corrigez d\'abord le message. Il doit afficher « Oui, avec plaisir ».',
            },
            {
              id: 'clavier',
              type: 'clavier',
              valeur: 'brouillon',
              ouvert: 'clavierOuvert',
              rangees: [
                ['a', 'z', 'e', 'r', 't', 'y', 'u', 'i', 'o', 'p'],
                ['q', 's', 'd', 'f', 'g', 'h', 'j', 'k', 'l', 'm'],
                ['w', 'x', 'c', 'v', 'b', 'n', ',', '\'', 'effacer'],
                ['espace'],
              ],
            },
          ],
        },
        appels: ecranImaginaire('Appels'),
        photos: ecranImaginaire('Photos'),
      },
    },
    missions: [
      {
        id: 'ouvrir-clavier',
        geste: 'Toucher',
        consigne: 'Touchez la zone « Écrire un message » pour faire apparaître le clavier.',
        aide: 'La zone de texte se trouve sous la conversation. Touchez-la une fois : le clavier apparaît en dessous.',
        reussite: 'Bravo, le clavier est ouvert.',
        condition: { valeur: 'clavierOuvert', egal: true },
      },
      {
        id: 'effacer',
        geste: 'Effacer',
        consigne: 'Le mot « plaisur » contient une faute. Effacez « ur » avec la touche « Effacer ».',
        aide: 'La touche « Effacer » est à droite, sur la troisième rangée. Chaque toucher enlève la dernière lettre. Touchez-la deux fois.',
        reussite: 'Bravo, la faute est effacée.',
        condition: { valeur: 'brouillon', prefixeDe: MESSAGE_ATTENDU },
      },
      {
        id: 'ecrire',
        geste: 'Écrire',
        consigne: 'Écrivez les lettres qui manquent pour obtenir « plaisir ».',
        aide: 'Touchez la lettre « i », puis la lettre « r ». En cas d\'erreur, utilisez « Effacer ». Sur ordinateur, vous pouvez aussi taper sur votre clavier.',
        reussite: 'Bravo, le message est corrigé.',
        condition: { valeur: 'brouillon', egal: MESSAGE_ATTENDU },
      },
      {
        id: 'envoyer',
        geste: 'Envoyer',
        consigne: 'Envoyez le message à Léa.',
        aide: 'Touchez le bouton « Envoyer », à droite de la zone de texte.',
        reussite: 'Bravo, votre message est envoyé.',
        condition: { envoye: MESSAGE_ATTENDU },
      },
    ],
  }],
  conclusion: {
    reussite: 'Bravo, vous avez corrigé et envoyé votre message.',
    aRetenir: [
      'Touchez la zone de texte pour faire apparaître le clavier.',
      'La touche « Effacer » enlève la dernière lettre écrite.',
      'Relisez votre message avant de l\'envoyer.',
      'Un message envoyé part tout de suite : vérifiez à qui vous écrivez.',
      'Votre clavier peut avoir un autre aspect, mais les touches se ressemblent.',
    ],
  },
}
