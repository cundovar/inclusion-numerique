import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest'
import { flushPromises, mount } from '@vue/test-utils'
import { createMemoryHistory, createRouter } from 'vue-router'
import ScenePlayer from '@/components/features/exercices/ScenePlayer.vue'
import exercice from '@/exercices/trouvez-le-vrai-site'

const Stub = { template: '<div />' }

let wrapper
let setItem

const monter = async () => {
  const router = createRouter({
    history: createMemoryHistory(),
    routes: [
      { path: '/', component: Stub },
      { path: '/exercices', component: Stub },
    ],
  })
  router.push('/')
  await router.isReady()
  // attachTo : le focus n'existe que pour un élément présent dans le document
  wrapper = mount(ScenePlayer, {
    props: { exercice },
    attachTo: document.body,
    global: { plugins: [router] },
  })
  return wrapper
}

const retour = () => wrapper.get('[role="status"][aria-live="polite"]')

beforeEach(() => {
  // Storage.prototype couvre à la fois localStorage et sessionStorage
  setItem = vi.spyOn(Storage.prototype, 'setItem')
})

afterEach(() => {
  expect(setItem).not.toHaveBeenCalled()
  setItem.mockRestore()
  wrapper?.unmount()
})

describe('ScenePlayer — Trouvez le vrai site', () => {
  it('affiche les quatre résultats et l\'étiquette « Annonce » en texte', async () => {
    await monter()

    expect(wrapper.findAll('[data-testid="resultats"] button')).toHaveLength(4)
    expect(wrapper.get('[data-testid="resultat-annonce"]').text()).toContain('Annonce')
    expect(retour().text()).toBe('')
  })

  it('explique un mauvais choix et laisse choisir un autre résultat', async () => {
    await monter()

    await wrapper.get('[data-testid="resultat-annonce"]').trigger('click')
    expect(retour().text()).toContain('Ce n\'est pas le bon site.')
    expect(retour().text()).toContain('Quelqu\'un a payé pour apparaître en premier.')
    expect(retour().text()).toContain('Vous pouvez choisir un autre résultat.')

    // Le retour est visible juste sous le résultat choisi, pas en bas de la liste
    const visible = wrapper.get('[data-testid="item-annonce"] [data-testid="retour-visible"]')
    expect(visible.text()).toContain('Quelqu\'un a payé pour apparaître en premier.')
    expect(wrapper.findAll('[data-testid="retour-visible"]')).toHaveLength(1)
    expect(wrapper.find('[data-testid="conclusion"]').exists()).toBe(false)
    expect(wrapper.find('[data-testid="continuer"]').exists()).toBe(false)

    // Aucun bouton n'est bloqué : la personne recommence librement
    for (const bouton of wrapper.findAll('[data-testid="resultats"] button')) {
      expect(bouton.attributes('disabled')).toBeUndefined()
    }

    await wrapper.get('[data-testid="resultat-copie"]').trigger('click')
    expect(retour().text()).toContain('Une imitation peut copier le nom')
    expect(retour().text()).not.toContain('Quelqu\'un a payé')
    expect(wrapper.find('[data-testid="item-annonce"] [data-testid="retour-visible"]').exists()).toBe(false)
    expect(wrapper.find('[data-testid="item-copie"] [data-testid="retour-visible"]').exists()).toBe(true)
  })

  it('mène à la conclusion après le bon choix, sans score', async () => {
    await monter()

    await wrapper.get('[data-testid="resultat-forum"]').trigger('click')
    await wrapper.get('[data-testid="resultat-officiel"]').trigger('click')
    expect(retour().text()).toContain('Bon choix.')
    expect(retour().text()).toContain('C\'est le bon site.')

    await wrapper.get('[data-testid="continuer"]').trigger('click')
    await flushPromises()

    const conclusion = wrapper.get('[data-testid="conclusion"]')
    expect(conclusion.text()).toContain('Bravo, vous avez trouvé le bon site.')
    for (const phrase of exercice.conclusion.aRetenir) {
      expect(conclusion.text()).toContain(phrase)
    }
    expect(conclusion.text()).not.toMatch(/score|point|erreur/i)
    expect(document.activeElement?.id).toBe('conclusion-titre')
    // Marge de défilement : le titre ne passe pas sous l'en-tête fixe
    expect(conclusion.get('h2').classes()).toContain('scroll-mt-32')
    expect(conclusion.get('a').attributes('href')).toBe('/exercices')
  })

  it('peut recommencer l\'exercice depuis la conclusion', async () => {
    await monter()

    await wrapper.get('[data-testid="resultat-officiel"]').trigger('click')
    await wrapper.get('[data-testid="continuer"]').trigger('click')
    await flushPromises()
    await wrapper.get('[data-testid="recommencer"]').trigger('click')

    await flushPromises()
    expect(wrapper.find('[data-testid="conclusion"]').exists()).toBe(false)
    expect(retour().text()).toBe('')
    // Le focus revient au début des résultats, pas en haut de la page
    expect(document.activeElement?.id).toBe('resultats-titre')
  })

  it('se fait entièrement au clavier', async () => {
    await monter()

    // Boutons natifs : le navigateur les place dans l'ordre de tabulation
    // et les active avec Entrée et Espace. jsdom ne simule ni Tab ni cette activation :
    // on vérifie la nature des éléments, l'ordre de focus, puis on déclenche le clic produit par la touche.
    const boutons = wrapper.findAll('[data-testid="resultats"] button')
    const ordre = []
    for (const bouton of boutons) {
      expect(bouton.element.tagName).toBe('BUTTON')
      expect(bouton.attributes('type')).toBe('button')
      expect(bouton.attributes('tabindex')).toBeUndefined()
      bouton.element.focus()
      ordre.push(document.activeElement?.dataset.testid)
    }
    expect(ordre).toEqual(['resultat-annonce', 'resultat-officiel', 'resultat-copie', 'resultat-forum'])

    // Entrée sur un mauvais résultat
    const annonce = wrapper.get('[data-testid="resultat-annonce"]')
    annonce.element.focus()
    await annonce.trigger('keydown', { key: 'Enter' })
    await annonce.trigger('click')
    expect(retour().text()).toContain('Ce n\'est pas le bon site.')
    expect(document.activeElement).toBe(annonce.element)

    // Espace sur le bon résultat
    const officiel = wrapper.get('[data-testid="resultat-officiel"]')
    officiel.element.focus()
    await officiel.trigger('keyup', { key: ' ' })
    await officiel.trigger('click')
    expect(retour().text()).toContain('Bon choix.')

    // Le bouton suivant est lui aussi un bouton natif
    const continuer = wrapper.get('[data-testid="continuer"]')
    expect(continuer.element.tagName).toBe('BUTTON')
    continuer.element.focus()
    expect(document.activeElement).toBe(continuer.element)
    await continuer.trigger('click')
    await flushPromises()
    expect(document.activeElement?.id).toBe('conclusion-titre')
  })

  it('affiche un message clair pour un type de scène inconnu', async () => {
    const router = createRouter({ history: createMemoryHistory(), routes: [{ path: '/', component: Stub }] })
    wrapper = mount(ScenePlayer, {
      props: { exercice: { ...exercice, scenes: [{ type: 'inconnu' }] } },
      global: { plugins: [router] },
    })

    expect(wrapper.get('[data-testid="scene-inconnue"]').text()).toContain('ne peut pas s\'afficher')
  })
})
