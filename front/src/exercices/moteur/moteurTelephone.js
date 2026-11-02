/**
 * Moteur du faux téléphone : fonctions pures, sans interface et sans stockage.
 *
 * `telephone` décrit les écrans et leurs zones (voir les données d'exercice) ;
 * `missions` donne les étapes, chacune avec une condition à remplir.
 * `appliquer(scene, etat, evenement)` renvoie un nouvel état, sans modifier l'ancien.
 *
 * Événements reconnus :
 *   { type: 'toucher', cible }               application, interrupteur, champ, envoyer, 'accueil' ou 'retour'
 *   { type: 'glisser', cible, direction }    liste ; direction 'bas' ou 'haut'
 *   { type: 'regler', cible, valeur }        curseur
 *   { type: 'taper', cible, touche }         clavier ; touche = une lettre, 'espace' ou 'effacer'
 */

export const CIBLE_ACCUEIL = 'accueil'
export const CIBLE_RETOUR = 'retour'

export const creerEtat = ({ telephone }) => ({
  ecran: telephone.ecranDepart,
  valeurs: { ...telephone.valeursDepart },
  defilement: {},
  envoyes: [],
  etape: 0,
  termine: false,
  annonce: '',
  // Conseil à afficher à l'écran (envoi refusé, message vide) ; effacé à l'action suivante
  conseil: '',
  reussites: [],
})

export const trouverZone = (telephone, ecran, cible) =>
  telephone.ecrans[ecran]?.zones.find((zone) => zone.id === cible) ?? null

// Nombre de crans possibles pour une liste : éléments au-delà de ceux visibles
export const defilementMax = (zone) => Math.max(0, zone.elements.length - zone.visibles)

const borner = (valeur, min, max) => Math.min(max, Math.max(min, valeur))

// Comme un vrai téléphone : majuscule au début du message et après un point
export const majusculeAuto = (texte) => texte === '' || /[.!?]\s$/.test(texte)

export const conditionRemplie = (telephone, etat, condition) => {
  if (condition.ecran && etat.ecran !== condition.ecran) return false
  if (condition.defilementFin) {
    const zone = Object.values(telephone.ecrans)
      .flatMap((ecran) => ecran.zones)
      .find((candidate) => candidate.id === condition.defilementFin)
    if (!zone || (etat.defilement[zone.id] ?? 0) < defilementMax(zone)) return false
  }
  if (condition.valeur !== undefined) {
    const valeur = etat.valeurs[condition.valeur]
    if (condition.min !== undefined && !(valeur >= condition.min)) return false
    if (condition.egal !== undefined && valeur !== condition.egal) return false
    // Le texte ne contient plus d'erreur : c'est le début du texte attendu
    if (condition.prefixeDe !== undefined && !condition.prefixeDe.startsWith(valeur)) return false
  }
  if (condition.envoye !== undefined && !etat.envoyes.includes(condition.envoye)) return false
  return true
}

// Change l'écran ou les valeurs ; renvoie null si l'événement ne fait rien
const transformer = (telephone, etat, evenement) => {
  const ecran = telephone.ecrans[etat.ecran]

  if (evenement.type === 'toucher' && evenement.cible === CIBLE_ACCUEIL) {
    return { ...etat, ecran: CIBLE_ACCUEIL, annonce: `Écran « ${telephone.ecrans.accueil.titre} ».` }
  }
  if (evenement.type === 'toucher' && evenement.cible === CIBLE_RETOUR) {
    if (!ecran.parent) return null
    return { ...etat, ecran: ecran.parent, annonce: `Écran « ${telephone.ecrans[ecran.parent].titre} ».` }
  }

  const zone = trouverZone(telephone, etat.ecran, evenement.cible)
  if (!zone) return null

  if (evenement.type === 'toucher' && zone.aller) {
    return { ...etat, ecran: zone.aller, annonce: `Application « ${telephone.ecrans[zone.aller].titre} » ouverte.` }
  }
  if (evenement.type === 'toucher' && zone.type === 'interrupteur') {
    const active = !etat.valeurs[zone.valeur]
    return {
      ...etat,
      valeurs: { ...etat.valeurs, [zone.valeur]: active },
      annonce: `${zone.libelle} ${active ? 'activé' : 'désactivé'}.`,
    }
  }
  if (evenement.type === 'glisser' && zone.type === 'liste') {
    const avant = etat.defilement[zone.id] ?? 0
    const apres = borner(avant + (evenement.direction === 'haut' ? -1 : 1), 0, defilementMax(zone))
    if (apres === avant) {
      return { ...etat, annonce: apres === 0 ? 'Vous êtes en haut de la liste.' : 'Vous êtes à la fin de la liste.' }
    }
    const premier = apres + 1
    const dernier = Math.min(apres + zone.visibles, zone.elements.length)
    return {
      ...etat,
      defilement: { ...etat.defilement, [zone.id]: apres },
      annonce: `${zone.libelle} ${premier} à ${dernier} sur ${zone.elements.length}.`,
    }
  }
  if (evenement.type === 'toucher' && zone.type === 'champ') {
    return { ...etat, valeurs: { ...etat.valeurs, [zone.ouvre]: true }, annonce: 'Clavier ouvert.' }
  }
  if (evenement.type === 'taper' && zone.type === 'clavier') {
    if (!etat.valeurs[zone.ouvert]) return null
    const texte = etat.valeurs[zone.valeur]
    if (evenement.touche === 'effacer') {
      if (texte === '') return { ...etat, annonce: 'Le message est vide.', conseil: 'Le message est vide.' }
      const efface = texte.at(-1)
      return {
        ...etat,
        valeurs: { ...etat.valeurs, [zone.valeur]: texte.slice(0, -1) },
        annonce: efface === ' ' ? 'Espace effacée.' : `« ${efface} » effacé.`,
      }
    }
    const caractere = evenement.touche === 'espace' ? ' ' : String(evenement.touche ?? '')
    if (caractere.length !== 1) return null
    const ajoute = majusculeAuto(texte) ? caractere.toUpperCase() : caractere
    return {
      ...etat,
      valeurs: { ...etat.valeurs, [zone.valeur]: texte + ajoute },
      annonce: ajoute === ' ' ? 'Espace.' : `« ${ajoute} »`,
    }
  }
  if (evenement.type === 'toucher' && zone.type === 'envoyer') {
    const texte = etat.valeurs[zone.valeur]
    if (texte.trim() === '') {
      const conseil = 'Le message est vide : écrivez d\'abord quelque chose.'
      return { ...etat, annonce: conseil, conseil }
    }
    // Dans l'exercice, on n'envoie que le texte attendu pour ne pas bloquer la suite
    if (zone.attendu !== undefined && texte !== zone.attendu) return { ...etat, annonce: zone.refus, conseil: zone.refus }
    return {
      ...etat,
      valeurs: { ...etat.valeurs, [zone.valeur]: '' },
      envoyes: [...etat.envoyes, texte],
      annonce: `Message envoyé : « ${texte} ».`,
    }
  }
  if (evenement.type === 'regler' && zone.type === 'curseur') {
    const valeur = borner(Number(evenement.valeur), zone.min, zone.max)
    if (Number.isNaN(valeur)) return null
    return {
      ...etat,
      valeurs: { ...etat.valeurs, [zone.valeur]: valeur },
      annonce: `${zone.libelle} : ${valeur} sur ${zone.max}.`,
    }
  }
  return null
}

export const appliquer = (scene, etat, evenement) => {
  if (etat.termine) return etat
  const suivant = transformer(scene.telephone, { ...etat, conseil: '' }, evenement)
  if (!suivant) return { ...etat, annonce: '', conseil: '', reussites: [] }

  // Une action peut remplir plusieurs missions d'affilée (par exemple un réglage fait en avance)
  let etape = etat.etape
  const reussites = []
  while (etape < scene.missions.length && conditionRemplie(scene.telephone, suivant, scene.missions[etape].condition)) {
    reussites.push(scene.missions[etape].reussite)
    etape += 1
  }

  return { ...suivant, etape, termine: etape >= scene.missions.length, reussites }
}
