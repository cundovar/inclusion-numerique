/**
 * Niveaux des cours (règle NIV-01 de regles/07-niveaux.md) : un niveau par cours, affiché en badge,
 * et cours rangés du plus simple au plus avancé dans chaque module.
 */

/** Niveaux du plus simple au plus avancé (rang `ordre`, puis identifiant). */
export function sortNiveaux(niveaux = []) {
  return [...niveaux].sort((a, b) => (a.ordre ?? 0) - (b.ordre ?? 0) || (a.id ?? 0) - (b.id ?? 0))
}

/**
 * Place d'un niveau parmi tous les niveaux : { rang, total, nom }, ou null si le cours n'a pas de niveau.
 * Le rang affiché suit la position (1, 2, 3…), pas la valeur brute de `ordre`.
 */
export function niveauRank(niveau, niveaux = []) {
  if (!niveau?.name) return null
  const sorted = sortNiveaux(niveaux)
  const index = sorted.findIndex((n) => (niveau.id != null && n.id === niveau.id) || n.name === niveau.name)
  if (index === -1) return null
  return { rang: index + 1, total: sorted.length, nom: niveau.name }
}

/**
 * Ordre d'affichage des cours d'un module : par niveau (les cours sans niveau après), puis par rang
 * éditorial (`ordre`), puis par ancienneté.
 */
export function compareCourses(a, b) {
  const levelA = a.niveauCours?.name ? (a.niveauCours.ordre ?? 0) : Number.POSITIVE_INFINITY
  const levelB = b.niveauCours?.name ? (b.niveauCours.ordre ?? 0) : Number.POSITIVE_INFINITY
  if (levelA !== levelB) return levelA < levelB ? -1 : 1
  const rankA = a.ordre ?? Number.POSITIVE_INFINITY
  const rankB = b.ordre ?? Number.POSITIVE_INFINITY
  if (rankA !== rankB) return rankA < rankB ? -1 : 1
  return (a.id ?? 0) - (b.id ?? 0)
}

/**
 * Cours par lequel commencer un parcours : le premier cours du niveau le plus simple, en suivant l'ordre
 * des modules. null si aucun cours n'a de niveau (on ne devine pas).
 *
 * @param {Array<{items: Array}>} groups modules du parcours, cours déjà triés
 */
export function firstCourseToStart(groups = []) {
  const courses = groups.flatMap((group) => group.items ?? []).filter((course) => course.niveauCours?.name)
  if (courses.length === 0) return null
  const lowest = Math.min(...courses.map((course) => course.niveauCours.ordre ?? 0))
  return courses.find((course) => (course.niveauCours.ordre ?? 0) === lowest) ?? null
}
