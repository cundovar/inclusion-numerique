import { describe, expect, it } from 'vitest'
import { buildParcours, toPlainText } from '@/utils/parcours'

const debutant = { name: 'Débutant', ordre: 1 }
const confirme = { name: 'Confirmé', ordre: 2 }

const categories = [
  { id: 1, name: 'messagerie', audience: 'apprendre', description: '<p>Envoyer un <strong>courriel</strong></p>', niveauCours: debutant },
  { id: 2, name: 'démarches', audience: 'apprendre', niveauCours: confirme },
  { id: 3, name: 'animer un atelier', audience: 'accompagner' },
  { id: 4, name: 'brouillon', audience: 'apprendre', visible: false },
  { id: 5, name: 'sans public' },
  { id: 6, name: 'vide', audience: 'apprendre' },
]

const courses = [
  { category: { id: 1 } },
  { category: { id: 1 } },
  { category: { id: 1 }, visible: false },
  { category: { id: 2 } },
  { category: { id: 3 } },
  { category: { id: 4 } },
  { category: { id: 5 } },
]

const names = (list) => list.map((parcours) => parcours.name)

describe('buildParcours', () => {
  it('ne garde que les parcours visibles du public demandé qui ont au moins un cours', () => {
    expect(names(buildParcours(categories, courses, 'apprendre'))).toEqual(['démarches', 'messagerie'])
    expect(names(buildParcours(categories, courses, 'accompagner'))).toEqual(['animer un atelier'])
  })

  it('compte uniquement les cours visibles et prépare le texte de la carte', () => {
    const [messagerie] = buildParcours(categories, courses, 'apprendre').filter((p) => p.name === 'messagerie')

    expect(messagerie).toMatchObject({
      title: 'Messagerie',
      summary: 'Envoyer un courriel',
      niveau: 'Débutant',
      courseCount: 2,
    })
  })

  it("range les parcours par rang, puis par ordre alphabétique ceux qui n'en ont pas", () => {
    const rangees = [
      { id: 1, name: 'démarches', audience: 'apprendre', ordre: 3 },
      { id: 2, name: 'premiers gestes', audience: 'apprendre', ordre: 1 },
      { id: 3, name: 'se protéger', audience: 'apprendre', ordre: 2 },
      { id: 4, name: 'bonus', audience: 'apprendre' },
      { id: 5, name: 'archives', audience: 'apprendre' },
    ]
    const cours = rangees.map((category) => ({ category: { id: category.id } }))

    expect(names(buildParcours(rangees, cours, 'apprendre'))).toEqual([
      'premiers gestes',
      'se protéger',
      'démarches',
      'archives',
      'bonus',
    ])
  })

  it("prépare la miniature d'un parcours, ou rien quand il n'en a pas", () => {
    const avecMiniature = [
      { id: 1, name: 'illustré', audience: 'apprendre', illustrationUrl: '/uploads/course-media/a.png', illustrationAlt: 'Un clavier' },
      { id: 2, name: 'nu', audience: 'apprendre', illustrationUrl: null, illustrationAlt: null },
    ]
    const cours = avecMiniature.map((category) => ({ category: { id: category.id } }))
    const [illustre, nu] = buildParcours(avecMiniature, cours, 'apprendre')

    expect(illustre.illustration).toEqual({ url: '/uploads/course-media/a.png', alt: 'Un clavier' })
    expect(nu.illustration).toBeNull()
  })

  it('applique le niveau choisi : niveaux inférieurs ou égaux, et parcours sans niveau', () => {
    expect(names(buildParcours(categories, courses, 'apprendre', debutant))).toEqual(['messagerie'])
    expect(names(buildParcours(categories, courses, 'accompagner', debutant))).toEqual(['animer un atelier'])
  })
})

describe('toPlainText', () => {
  it('extrait le texte sans exécuter le HTML', () => {
    window.__piege = false
    expect(toPlainText('<img src="x" onerror="window.__piege = true">Bonjour <em>à tous</em>')).toBe('Bonjour à tous')
    expect(window.__piege).toBe(false)
    expect(toPlainText(null)).toBe('')
  })
})
