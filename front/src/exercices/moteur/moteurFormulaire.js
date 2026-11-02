/**
 * Moteur du faux formulaire en ligne : fonctions pures, sans interface et sans stockage.
 *
 * Un champ décrit (voir les données d'exercice) :
 *   id, type ('texte' | 'choix' | 'case'), libelle, obligatoire,
 *   attendu    valeur de la fiche à recopier (texte ou choix) ; absent pour un champ libre
 *   format     expression régulière du format demandé (date, code postal…)
 *   erreurs    { vide, format, valeur } : messages affichés sous le champ
 *
 * La comparaison avec la fiche ignore les majuscules, les accents et les espaces en trop :
 * l'exercice porte sur la démarche, pas sur la frappe.
 */

export const normaliser = (texte) => String(texte ?? '')
  .normalize('NFD')
  .replace(/\p{Diacritic}/gu, '')
  .toLowerCase()
  .replace(/\s+/g, ' ')
  .trim()

export const valeursDepart = (formulaire) => Object.fromEntries(
  formulaire.champs.map((champ) => [champ.id, champ.depart ?? (champ.type === 'case' ? false : '')]),
)

// Message d'erreur du champ, ou null s'il est bien rempli
export const verifierChamp = (champ, valeur) => {
  if (champ.type === 'case') {
    return champ.obligatoire && valeur !== true ? champ.erreurs.vide : null
  }

  const texte = String(valeur ?? '').trim()
  if (texte === '') {
    return champ.obligatoire ? champ.erreurs.vide : null
  }
  if (champ.format && !new RegExp(champ.format, 'u').test(texte)) {
    return champ.erreurs.format
  }
  if (champ.attendu !== undefined && normaliser(texte) !== normaliser(champ.attendu)) {
    return champ.erreurs.valeur
  }
  return null
}

// Erreurs du formulaire, dans l'ordre des champs : [{ id, libelle, message }]
export const verifierFormulaire = (formulaire, valeurs) => formulaire.champs
  .map((champ) => ({ id: champ.id, libelle: champ.libelle, message: verifierChamp(champ, valeurs[champ.id]) }))
  .filter((erreur) => erreur.message !== null)

// Lignes du récapitulatif : les champs remplis, avec la valeur saisie
export const recapitulatif = (formulaire, valeurs) => formulaire.champs
  .filter((champ) => champ.type !== 'case')
  .map((champ) => ({ id: champ.id, libelle: champ.libelle, valeur: String(valeurs[champ.id] ?? '').trim() || 'Non renseigné' }))

export const numeroRecopie = (confirmation, saisie) =>
  normaliser(saisie).replace(/[\s-]/g, '') === normaliser(confirmation.numero).replace(/[\s-]/g, '')
