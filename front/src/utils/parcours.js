/**
 * Parcours (catégories) d'un public, prêts à afficher dans son catalogue.
 *
 * Un parcours n'apparaît que s'il est visible, destiné à ce public et contient au moins un cours
 * visible. Les parcours sont rangés par rang (champ `ordre`), puis par ordre alphabétique. Le filtre de niveau suit la règle du reste du site : on garde les niveaux inférieurs
 * ou égaux au niveau choisi, et les parcours sans niveau.
 *
 * @param {Array} categories catégories de l'API (déjà filtrées par visibilité)
 * @param {Array} courses contenus de cours (page_contents) de l'API
 * @param {string} audience clé du public ('apprendre' ou 'accompagner')
 * @param {{ordre: number}|null} selectedNiveau niveau choisi dans l'en-tête
 */
export function buildParcours(categories, courses, audience, selectedNiveau = null) {
  return categories
    .filter((category) => category?.audience === audience && category.visible !== false)
    .filter((category) => matchesNiveau(category.niveauCours, selectedNiveau))
    .map((category) => ({
      id: category.id,
      name: category.name,
      title: capitalize(category.name),
      summary: toPlainText(category.description),
      niveau: category.niveauCours?.name ?? null,
      ordre: category.ordre ?? null,
      // Miniature de la carte : sans illustration, la carte garde une place réservée
      illustration: category.illustrationUrl
        ? { url: category.illustrationUrl, alt: category.illustrationAlt ?? '' }
        : null,
      courseCount: courses.filter(
        (course) => course?.category?.id === category.id && course.visible !== false
      ).length,
    }))
    .filter((parcours) => parcours.courseCount > 0)
    .sort(
      (a, b) =>
        (a.ordre ?? Number.POSITIVE_INFINITY) - (b.ordre ?? Number.POSITIVE_INFINITY) ||
        a.title.localeCompare(b.title, 'fr')
    )
}

const matchesNiveau = (niveau, selectedNiveau) =>
  !selectedNiveau || !niveau?.name || niveau.ordre <= selectedNiveau.ordre

const capitalize = (text = '') => text.charAt(0).toUpperCase() + text.slice(1)

// La description vient de l'éditeur du back-office : on n'en garde que le texte pour la carte.
// DOMParser produit un document inerte (ni script ni chargement d'image), contrairement à innerHTML.
export function toPlainText(html) {
  if (!html) return ''
  const text = new DOMParser().parseFromString(html, 'text/html').body.textContent ?? ''
  return text.replace(/\s+/g, ' ').trim()
}
