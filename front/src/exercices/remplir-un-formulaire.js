/**
 * Exercice « Remplir un formulaire en ligne » sur le site du « Service Exemple », un service imaginaire.
 * Rose Dupré, son adresse et le numéro de demande sont inventés. Rien n'est envoyé ni enregistré.
 */
export default {
  slug: 'remplir-un-formulaire',
  titre: 'Remplir un formulaire en ligne',
  public: 'apprendre',
  module: 'Démarches en ligne',
  competence: '2.4 S\'insérer dans le monde numérique ; 4.2 Protéger les données personnelles',
  duree: '15 minutes',
  coursSlug: null,
  consigne: 'Vous aidez Rose Dupré, une personne imaginaire, à demander une aide au « Service Exemple ». Recopiez sa fiche dans le formulaire. N\'écrivez jamais vos propres informations.',
  scenes: [{
    type: 'formulaire',
    navigateur: {
      adresse: 'https://www.service-exemple.example/demande-aide',
      onglet: 'Service Exemple : demande d\'aide',
    },
    fiche: {
      titre: 'Fiche de Rose Dupré (personne imaginaire)',
      lignes: [
        { libelle: 'Nom', valeur: 'DUPRÉ' },
        { libelle: 'Prénom', valeur: 'Rose' },
        { libelle: 'Née le', valeur: '14 février 1956' },
        { libelle: 'Adresse', valeur: '8 rue des Lilas' },
        { libelle: 'Code postal et ville', valeur: '99100 Villexemple' },
        { libelle: 'Logement', valeur: 'Elle loue son appartement : elle est locataire.' },
      ],
    },
    formulaire: {
      titre: 'Demande d\'aide au logement',
      introduction: 'Les champs marqués d\'une étoile (*) sont obligatoires.',
      champs: [
        {
          id: 'nom', type: 'texte', libelle: 'Nom', obligatoire: true, attendu: 'DUPRÉ',
          erreurs: { vide: 'Indiquez le nom.', valeur: 'Recopiez le nom de la fiche : DUPRÉ.' },
        },
        {
          id: 'prenom', type: 'texte', libelle: 'Prénom', obligatoire: true, attendu: 'Rose',
          erreurs: { vide: 'Indiquez le prénom.', valeur: 'Recopiez le prénom de la fiche : Rose.' },
        },
        {
          id: 'naissance', type: 'texte', libelle: 'Date de naissance', obligatoire: true,
          indication: 'Format JJ/MM/AAAA, par exemple 05/03/1960.',
          depart: '14-2-56', format: '^\\d{2}/\\d{2}/\\d{4}$', attendu: '14/02/1956',
          erreurs: {
            vide: 'Indiquez la date de naissance.',
            format: 'La date doit avoir la forme JJ/MM/AAAA : deux chiffres, une barre, deux chiffres, une barre, quatre chiffres.',
            valeur: 'Rose est née le 14 février 1956. Écrivez 14/02/1956.',
          },
        },
        {
          id: 'adresse', type: 'texte', libelle: 'Adresse', obligatoire: true, attendu: '8 rue des Lilas',
          erreurs: { vide: 'Indiquez l\'adresse.', valeur: 'Recopiez l\'adresse de la fiche : 8 rue des Lilas.' },
        },
        {
          id: 'code-postal', type: 'texte', libelle: 'Code postal', obligatoire: true,
          indication: 'Cinq chiffres.',
          format: '^\\d{5}$', attendu: '99100',
          erreurs: {
            vide: 'Indiquez le code postal.',
            format: 'Le code postal compte cinq chiffres, sans espace.',
            valeur: 'Recopiez le code postal de la fiche : 99100.',
          },
        },
        {
          id: 'ville', type: 'texte', libelle: 'Ville', obligatoire: true, attendu: 'Villexemple',
          erreurs: { vide: 'Indiquez la ville.', valeur: 'Recopiez la ville de la fiche : Villexemple.' },
        },
        {
          id: 'telephone', type: 'texte', libelle: 'Téléphone', obligatoire: false,
          indication: 'Facultatif. La fiche ne donne pas de numéro : laissez ce champ vide.',
          erreurs: { vide: 'Indiquez un numéro de téléphone.' },
        },
        {
          id: 'logement', type: 'choix', libelle: 'Situation de logement', obligatoire: true, attendu: 'Locataire',
          options: ['Locataire', 'Propriétaire', 'Hébergée par un proche'],
          erreurs: {
            vide: 'Choisissez la situation de logement dans la liste.',
            valeur: 'D\'après la fiche, Rose loue son appartement : choisissez « Locataire ».',
          },
        },
        {
          id: 'certification', type: 'case', libelle: 'Je certifie que ces informations sont exactes.', obligatoire: true,
          erreurs: { vide: 'Cochez la case pour certifier les informations.' },
        },
      ],
      bouton: 'Continuer',
    },
    recapitulatif: {
      titre: 'Vérifiez votre demande',
      texte: 'Relisez chaque ligne. Pour corriger, revenez au formulaire.',
      modifier: 'Modifier',
      envoyer: 'Envoyer la demande',
    },
    confirmation: {
      titre: 'Demande envoyée',
      texte: 'Votre demande est enregistrée sous le numéro ci-dessous. Notez-le : il sert à suivre la demande.',
      numero: 'DEM-2026-0417',
      question: 'Recopiez le numéro de demande, comme si vous le notiez sur papier.',
      erreur: 'Ce n\'est pas le même numéro. Relisez-le lettre par lettre.',
    },
  }],
  conclusion: {
    reussite: 'Bravo, la demande de Rose est envoyée.',
    aRetenir: [
      'L\'étoile (*) signale un champ obligatoire.',
      'Un champ facultatif peut rester vide.',
      'Un message en rouge sous un champ explique quoi corriger.',
      'Relisez le récapitulatif avant d\'envoyer.',
      'Notez le numéro de demande : il sert à suivre votre dossier.',
      'Remplissez un formulaire seulement sur le site officiel du service.',
    ],
  },
}
