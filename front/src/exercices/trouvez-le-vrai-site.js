/**
 * Exercice « Trouvez le vrai site ».
 * Tous les services et adresses sont fictifs (domaine réservé .example).
 */
export default {
  slug: 'trouvez-le-vrai-site',
  titre: 'Trouvez le vrai site',
  public: 'apprendre',
  module: 'Chercher une information',
  competence: '1.1 Mener une recherche d\'information ; 4.1 Sécuriser l\'environnement numérique',
  duree: '5 minutes',
  coursSlug: null,
  consigne: 'Vous voulez faire une démarche en ligne auprès du « Service Exemple », un service imaginaire. Vous tapez son nom dans un moteur de recherche. Voici les résultats. Choisissez le bon site.',
  scenes: [{
    type: 'choisir-resultat',
    resultats: [
      { id: 'annonce', etiquette: 'Annonce', titre: 'Service Exemple : votre démarche en 2 minutes', adresse: 'https://demarche-rapide-exemple.example', extrait: 'Remplissez votre dossier ici. Frais de dossier : 29 €. Paiement par carte.', correct: false,
        retour: 'Ce résultat est une annonce. Quelqu\'un a payé pour apparaître en premier. Il demande 29 € alors que, dans cet exercice, la démarche est gratuite.' },
      { id: 'officiel', etiquette: '', titre: 'Service Exemple : site officiel', adresse: 'https://www.service-exemple.example', extrait: 'Informations et démarches du Service Exemple. Gratuit. Mentions légales et contact en bas de page.', correct: true,
        retour: 'C\'est le bon site. Il n\'est pas marqué « Annonce » et ne demande pas d\'argent. On y trouve les mentions légales et un contact.' },
      { id: 'copie', etiquette: '', titre: 'Service-Exemple : accès à votre dossier', adresse: 'https://service-exemple-dossier.example', extrait: 'Connectez-vous avec votre numéro et votre mot de passe pour voir votre dossier.', correct: false,
        retour: 'L\'adresse ressemble au nom du service, mais elle est différente. Une imitation peut copier le nom pour récupérer vos identifiants. Ne les saisissez jamais sur un site dont vous n\'êtes pas sûr.' },
      { id: 'forum', etiquette: '', titre: 'Forum Entraide : mon dossier Service Exemple est bloqué', adresse: 'https://forum-entraide.example', extrait: 'Des internautes donnent leur avis. Ces avis ne sont pas officiels.', correct: false,
        retour: 'Un forum donne des avis de personnes comme vous. Ce n\'est pas le site du service. Il peut aider à comprendre, mais pas à faire la démarche.' }
    ]
  }],
  conclusion: {
    reussite: 'Bravo, vous avez trouvé le bon site.',
    aRetenir: [
      'Une annonce est une publicité, même en haut de la liste.',
      'Un site officiel ne demande pas d\'argent pour une démarche gratuite.',
      'Une adresse proche du nom n\'est pas forcément la bonne.',
      'En cas de doute, demandez à un proche ou à une France Services.'
    ]
  }
}
