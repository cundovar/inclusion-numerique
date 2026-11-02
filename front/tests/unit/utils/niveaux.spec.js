import { describe, expect, it } from 'vitest'
import { mount } from '@vue/test-utils'
import { compareCourses, firstCourseToStart, niveauRank, sortNiveaux } from '@/utils/niveaux'
import NiveauBadge from '@/components/ui/NiveauBadge.vue'
import NiveauLegend from '@/components/ui/NiveauLegend.vue'

const decouvre = { id: 7, name: 'Je découvre', ordre: 10 }
const pratique = { id: 8, name: 'Je pratique', ordre: 20 }
const seul = { id: 9, name: 'Je me débrouille seul', ordre: 30 }
const niveaux = [seul, decouvre, pratique]

describe('niveaux des cours', () => {
  it('range les niveaux et donne leur place, pas leur ordre brut', () => {
    expect(sortNiveaux(niveaux).map((n) => n.id)).toEqual([7, 8, 9])
    expect(niveauRank(pratique, niveaux)).toEqual({ rang: 2, total: 3, nom: 'Je pratique' })
    expect(niveauRank(null, niveaux)).toBeNull()
    expect(niveauRank({ id: 99, name: 'Inconnu' }, niveaux)).toBeNull()
  })

  it('range les cours par niveau, puis par rang éditorial, les cours sans niveau après', () => {
    const courses = [
      { id: 1, ordre: 1, niveauCours: seul },
      { id: 2, ordre: 2, niveauCours: null },
      { id: 3, ordre: 3, niveauCours: decouvre },
      { id: 4, ordre: 1, niveauCours: decouvre },
    ]
    expect([...courses].sort(compareCourses).map((c) => c.id)).toEqual([4, 3, 1, 2])
  })

  it('propose de commencer par le premier cours du niveau le plus simple', () => {
    const groups = [
      { items: [{ id: 1, niveauCours: pratique }] },
      { items: [{ id: 2, niveauCours: null }, { id: 3, niveauCours: decouvre }, { id: 4, niveauCours: decouvre }] },
    ]
    expect(firstCourseToStart(groups).id).toBe(3)
    expect(firstCourseToStart([{ items: [{ id: 5, niveauCours: null }] }])).toBeNull()
  })

  it('affiche le niveau en mots et le lit en entier aux lecteurs d\'écran', () => {
    const wrapper = mount(NiveauBadge, { props: { niveau: decouvre, niveaux } })
    expect(wrapper.text()).toContain('Je découvre')
    expect(wrapper.find('.sr-only').text()).toBe('Niveau 1 sur 3 : Je découvre')
    expect(wrapper.findAll('.niveau-step--on')).toHaveLength(1)

    expect(mount(NiveauBadge, { props: { niveau: null, niveaux } }).html()).not.toContain('niveau-badge')
  })

  it('n\'affiche que les marches dans une liste, en gardant le nom pour les lecteurs d\'écran et au survol', () => {
    const wrapper = mount(NiveauBadge, { props: { niveau: pratique, niveaux, variant: 'icon' } })
    expect(wrapper.find('.sr-only').text()).toBe('Niveau 2 sur 3 : Je pratique')
    expect(wrapper.attributes('title')).toBe('Je pratique')
    expect(wrapper.text()).toBe('Niveau 2 sur 3 : Je pratique')
    expect(wrapper.findAll('.niveau-step--on')).toHaveLength(2)
  })

  it('écrit en clair le sens des marches, du plus simple au plus avancé', () => {
    const wrapper = mount(NiveauLegend, { props: { niveaux } })
    const items = wrapper.findAll('li')
    expect(items.map((item) => item.find('[aria-hidden="true"]:not(.niveau-steps)').text())).toEqual(['Je découvre', 'Je pratique', 'Je me débrouille seul'])
    expect(items[2].findAll('.niveau-step--on')).toHaveLength(3)
    expect(mount(NiveauLegend, { props: { niveaux: [] } }).html()).not.toContain('niveau-legend')
  })
})
