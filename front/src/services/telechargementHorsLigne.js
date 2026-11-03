// « Tout télécharger pour le hors ligne » : le texte de tous les cours, puis leurs captures (légendes et images).
// Le texte est déjà enregistré à chaque lancement (fetchMenus) ; les images, plus lourdes, ne sont
// téléchargées qu'à la demande de la personne, pour ménager les connexions faibles.
import { useData } from '@/utils/fetchDataPwa'
import { findCaptureIds } from '@/utils/captures'
import { chargerCaptures } from '@/services/captures'
import { COURSE_MEDIA_CACHE } from '../../pwa/courseMedia.js'

// Identifiants envoyés par requête : l'adresse de /api/captures?id[]=… reste courte
export const PAQUET = 50
const CLE_DERNIER = 'hors-ligne:dernier-telechargement'

export class HorsLigneError extends Error {}

const paquets = (liste, taille) => {
  const resultat = []
  for (let i = 0; i < liste.length; i += taille) resultat.push(liste.slice(i, i + taille))
  return resultat
}

// Date du dernier téléchargement complet sur cet appareil (null si jamais, ou stockage bloqué)
export function dernierTelechargement() {
  try {
    const valeur = localStorage.getItem(CLE_DERNIER)
    return valeur ? new Date(Number(valeur)) : null
  } catch {
    return null
  }
}

// Place utilisée par le site sur l'appareil, en octets (null si le navigateur ne le dit pas)
export async function espaceUtilise() {
  try {
    return (await navigator.storage?.estimate?.())?.usage ?? null
  } catch {
    return null
  }
}

/**
 * @param {{ onProgress?: (fait: number, total: number) => void, now?: () => number }} options
 * @returns {Promise<{ cours: number, captures: number, images: number, echecs: number }>}
 */
export async function toutTelecharger({ onProgress = () => {}, now = () => Date.now() } = {}) {
  if (typeof navigator !== 'undefined' && navigator.onLine === false) {
    throw new HorsLigneError('Connectez-vous à Internet pour télécharger les cours.')
  }

  // Demande au navigateur de ne pas effacer ces données quand l'appareil manque de place
  await navigator.storage?.persist?.().catch(() => false)

  const { fetchMenus, menus } = useData()
  await fetchMenus()
  const cours = menus.value
  const ids = [...new Set(cours.flatMap((c) => findCaptureIds(c.code)))]
  const lots = paquets(ids, PAQUET)

  let fait = 0
  let echecs = 0
  const captures = []
  // Total provisoire : un pas par lot de légendes, puis un par image une fois les adresses connues
  onProgress(fait, lots.length + ids.length)

  for (const lot of lots) {
    try {
      captures.push(...await chargerCaptures(lot))
    } catch {
      echecs += lot.length
    }
    onProgress(++fait, lots.length + ids.length)
  }

  // Images rangées directement dans le cache lu par le service worker (pwa/courseMedia.js),
  // même si le service worker ne contrôle pas encore la page. Sans Cache API, pas d'images hors ligne.
  const cache = typeof caches === 'undefined' ? null : await caches.open(COURSE_MEDIA_CACHE)
  const urls = cache ? [...new Set(captures.map((capture) => capture.url).filter(Boolean))] : []
  const total = lots.length + urls.length
  onProgress(fait, total)

  let images = 0
  for (const url of urls) {
    try {
      if (!(await cache.match(url))) await cache.add(url)
      images++
    } catch {
      echecs++
    }
    onProgress(++fait, total)
  }

  if (echecs === 0) {
    try {
      localStorage.setItem(CLE_DERNIER, String(now()))
    } catch {
      // Stockage bloqué : la date ne sera simplement pas affichée
    }
  }

  return { cours: cours.length, captures: captures.length, images, echecs }
}
