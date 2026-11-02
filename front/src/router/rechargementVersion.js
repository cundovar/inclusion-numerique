// Après une mise en ligne, les anciens fichiers de pages sont supprimés du serveur.
// Un onglet ouvert avant la mise en ligne ne peut alors plus charger une page :
// on recharge une seule fois pour récupérer la nouvelle version du site.

const CLE = 'rechargementApresMiseAJour'
// Pas de second rechargement avant ce délai, pour ne jamais boucler
const DELAI_MS = 10_000

export const estErreurDeChargement = (erreur) =>
  /Failed to fetch dynamically imported module|error loading dynamically imported module|Importing a module script failed|Unable to preload CSS/i
    .test(String(erreur?.message ?? erreur))

/**
 * Recharge la page vers `cible` si ce n'est pas déjà fait il y a moins de DELAI_MS.
 * Renvoie true si le rechargement est lancé.
 */
export const rechargerUneFois = (cible, { stockage, adresse = window.location, maintenant = Date.now() } = {}) => {
  let precedent = null
  try {
    // Lire window.sessionStorage peut déjà échouer quand le navigateur le bloque
    stockage ??= window.sessionStorage
    const valeur = stockage.getItem(CLE)
    precedent = valeur === null ? null : Number(valeur)
  } catch {
    // Stockage indisponible : on recharge quand même, une fois
  }
  if (precedent !== null && maintenant - precedent < DELAI_MS) return false
  try {
    stockage?.setItem(CLE, String(maintenant))
  } catch {
    // idem
  }
  adresse.assign(cible)
  return true
}

export const installerRechargementVersion = (router) => {
  router.onError((erreur, destination) => {
    if (!estErreurDeChargement(erreur)) return
    rechargerUneFois(router.resolve(destination?.fullPath ?? router.currentRoute.value.fullPath).href)
  })
}
