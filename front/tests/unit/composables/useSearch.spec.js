import { describe, expect, it, vi } from 'vitest'
import { ref } from 'vue'
import { useSearch } from '@/composables/domain/useSearch'

const menus = ref([
  { title: 'Envoyer un courriel', content: 'Écrire et envoyer un message', category: { name: 'messagerie' } },
  { title: 'Trouver le site de la CAF', content: 'Repérer le service officiel', category: { name: 'services' } },
  { title: 'Préparer un atelier', content: 'Fiche pour le courriel en groupe', category: { name: 'accompagner' } },
])

const setup = (isMobile = false, onSearchComplete = null) =>
  useSearch(menus, ref(null), ref(isMobile), onSearchComplete)

describe('recherche locale', () => {
  it('trouve les cours sans compte ni service externe, titre en tête', async () => {
    const fetchSpy = vi.spyOn(globalThis, 'fetch')
    const { search, searchResults, launchSearch } = setup()

    search.value = 'courriel'
    await launchSearch()

    expect(searchResults.value.map((menu) => menu.title)).toEqual(['Envoyer un courriel', 'Préparer un atelier'])
    expect(fetchSpy).not.toHaveBeenCalled()
    fetchSpy.mockRestore()
  })

  it('ignore les mots vides d\'une question', async () => {
    const { search, searchResults, launchSearch } = setup()

    search.value = 'comment trouver la CAF ?'
    await launchSearch()

    expect(searchResults.value.map((menu) => menu.title)).toEqual(['Trouver le site de la CAF'])
  })

  it('ne cherche pas avant deux caractères et vide les résultats à la fermeture', async () => {
    const { search, searchResults, launchSearch, closeSearchResults } = setup()

    search.value = 'c'
    await launchSearch()
    expect(searchResults.value).toEqual([])

    search.value = 'atelier'
    await launchSearch()
    closeSearchResults()
    expect(search.value).toBe('')
    expect(searchResults.value).toEqual([])
  })

  it('ouvre la fenêtre de résultats sur mobile', async () => {
    const onSearchComplete = vi.fn()
    const { search, launchSearch } = setup(true, onSearchComplete)

    search.value = 'CAF'
    await launchSearch()

    expect(onSearchComplete).toHaveBeenCalledOnce()
  })
})
