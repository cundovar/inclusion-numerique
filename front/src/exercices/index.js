/**
 * Catalogue des exercices : chaque fichier de ce dossier (sauf index.js) exporte un exercice.
 * Les données restent dans le code : aucun appel réseau, aucun stockage.
 */
const modules = import.meta.glob(['./*.js', '!./index.js'], { eager: true, import: 'default' })

const EXERCICES = Object.values(modules)
  .sort((a, b) => a.titre.localeCompare(b.titre, 'fr'))

export const listerExercices = () => EXERCICES

export const trouverExercice = (slug) => EXERCICES.find((exercice) => exercice.slug === slug) ?? null
