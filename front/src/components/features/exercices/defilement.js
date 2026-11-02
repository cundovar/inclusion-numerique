/**
 * Amène un élément à l'écran (et lui donne le focus si demandé),
 * sans animation quand la personne a demandé de réduire les mouvements.
 * La marge sous l'en-tête fixe vient de la classe scroll-mt-* de l'élément.
 */
const comportement = () =>
  (window.matchMedia?.('(prefers-reduced-motion: reduce)')?.matches ? 'auto' : 'smooth')

export const amenerEnVue = (element, { focus = false, bloc = 'nearest' } = {}) => {
  if (!element) return
  if (focus) element.focus({ preventScroll: true })
  element.scrollIntoView?.({ block: bloc, behavior: comportement() })
}

// Barres fixes de la page sur mobile (en-tête en haut, onglets en bas), en pixels
const MARGE_HAUT = 128
const MARGE_BAS = 112

/**
 * Descend la page juste assez pour que le bas de `element` passe au-dessus des onglets du bas,
 * sans faire passer son haut sous l'en-tête.
 */
export const garderVisible = (element) => {
  if (!element?.getBoundingClientRect) return
  const { top, bottom } = element.getBoundingClientRect()
  const depasse = bottom - (window.innerHeight - MARGE_BAS)
  const decalage = Math.min(depasse, top - MARGE_HAUT)
  if (decalage > 0) window.scrollBy?.({ top: decalage, behavior: 'auto' })
}

/**
 * Remonte la page pour montrer `aMontrer` (au-dessus), sans faire sortir de l'écran
 * `aGarder`, l'élément que la personne est en train d'utiliser. Si les deux ne tiennent pas,
 * `aGarder` reste visible et `aMontrer` n'est dévoilé qu'en partie.
 */
export const montrerSansPerdre = (aMontrer, aGarder) => {
  if (!aMontrer?.getBoundingClientRect) return
  const manque = MARGE_HAUT - aMontrer.getBoundingClientRect().top
  if (manque <= 0) return
  const place = aGarder?.getBoundingClientRect
    ? window.innerHeight - MARGE_BAS - aGarder.getBoundingClientRect().bottom
    : manque
  const decalage = Math.min(manque, place)
  if (decalage > 0) window.scrollBy?.({ top: -decalage, behavior: comportement() })
}
