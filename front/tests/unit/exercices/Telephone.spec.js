import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest'
import { flushPromises, mount } from '@vue/test-utils'
import { createMemoryHistory, createRouter } from 'vue-router'
import ScenePlayer from '@/components/features/exercices/ScenePlayer.vue'
import exercice from '@/exercices/gestes-du-telephone'

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
  wrapper = mount(ScenePlayer, {
    props: { exercice },
    attachTo: document.body,
    global: { plugins: [router] },
  })
  return wrapper
}

const cliquer = async (testid) => {
  await wrapper.get(`[data-testid="${testid}"]`).trigger('click')
  await flushPromises()
}
// jsdom n'a pas PointerEvent : un MouseEvent du même nom suffit au composant
const pointeur = async (zone, type, clientY) => {
  const evenement = new MouseEvent(type, { clientY, bubbles: true })
  Object.defineProperty(evenement, 'pointerId', { value: 1 })
  zone.element.dispatchEvent(evenement)
  await flushPromises()
}
const annonce = () => wrapper.get('[role="status"][aria-live="polite"]').text()
const consigne = () => wrapper.get('[data-testid="consigne-mission"]').text()
const titreEcran = () => wrapper.get('[data-testid="titre-ecran"]').text()

const liste = exercice.scenes[0].telephone.ecrans.messages.zones[0]
const crans = liste.elements.length - liste.visibles

beforeEach(() => {
  setItem = vi.spyOn(Storage.prototype, 'setItem')
})

afterEach(() => {
  expect(setItem).not.toHaveBeenCalled()
  setItem.mockRestore()
  wrapper?.unmount()
})

describe('Téléphone Exemple', () => {
  it('affiche la première mission, l\'accueil et ses applications', async () => {
    await monter()

    expect(wrapper.get('#mission-titre').text()).toBe('Mission 1 sur 5')
    expect(consigne()).toBe('Ouvrez l\'application « Messages ».')
    expect(titreEcran()).toBe('Accueil')
    expect(wrapper.get('[data-testid="telephone"]').attributes('aria-label')).toBe('Écran du Téléphone Exemple')
    expect(wrapper.findAll('[data-testid^="app-"]')).toHaveLength(6)
    expect(wrapper.text()).toContain('Votre téléphone peut avoir un autre aspect.')
  })

  it('affiche la mission dans le téléphone, avec une progression écrite', async () => {
    await monter()

    // La bulle de mission est dans le faux écran, juste sous la barre d'état
    const telephone = wrapper.get('[data-testid="telephone"]')
    expect(telephone.find('[data-testid="mission"]').exists()).toBe(true)
    const enfants = [...telephone.element.children]
    expect(enfants.indexOf(telephone.get('[data-testid="mission"]').element))
      .toBe(enfants.indexOf(telephone.get('[data-testid="barre-etat"]').element) + 1)

    // Pastilles visuelles, état écrit pour les lecteurs d'écran
    const progression = wrapper.get('[data-testid="progression"]')
    expect(progression.findAll('li')).toHaveLength(5)
    expect(progression.text()).toContain('Toucher : en cours')
    expect(progression.text()).toContain('Glisser : à venir')
  })

  it('réalise les cinq missions et mène à la conclusion', async () => {
    await monter()

    await cliquer('app-messages')
    expect(titreEcran()).toBe('Messages')
    expect(wrapper.get('[data-testid="reussite"]').text()).toContain('« Messages » est ouverte')
    expect(annonce()).toContain('Mission suivante : Faites défiler la liste jusqu\'au dernier message.')
    // Le focus passe au titre du nouvel écran, pas en haut de la page
    expect(document.activeElement?.dataset.testid).toBe('titre-ecran')

    for (let i = 0; i < crans; i += 1) await cliquer('defiler-bas')
    expect(annonce()).toContain('Messages 5 à 8 sur 8.')
    expect(consigne()).toContain('Montez le volume')

    await cliquer('bouton-accueil')
    await cliquer('app-reglages')
    await wrapper.get('[data-testid="volume"]').setValue('8')
    expect(wrapper.get('output').text()).toBe('8 sur 10')
    expect(consigne()).toBe('Activez le Wi-Fi dans « Réglages ».')

    const wifi = wrapper.get('[data-testid="wifi"]')
    expect(wifi.attributes('role')).toBe('switch')
    expect(wifi.attributes('aria-checked')).toBe('false')
    await cliquer('wifi')
    expect(wifi.attributes('aria-checked')).toBe('true')
    expect(wifi.text()).toContain('Activé')

    await cliquer('bouton-accueil')
    expect(wrapper.get('#mission-titre').text()).toBe('Toutes les missions sont faites')
    // Le focus rejoint le panneau qui contient le bouton pour continuer
    expect(document.activeElement?.id).toBe('mission-titre')
    expect(wrapper.find('[data-testid="consigne-mission"]').exists()).toBe(false)
    expect(wrapper.get('[data-testid="progression"]').text()).not.toContain('à venir')

    await cliquer('continuer')
    const conclusion = wrapper.get('[data-testid="conclusion"]')
    expect(conclusion.text()).toContain('Bravo, vous avez réussi les cinq missions.')
    expect(conclusion.text()).not.toMatch(/score|point|erreur/i)
  })

  it('n\'avance pas sur une mauvaise action et propose une aide sans pénalité', async () => {
    await monter()

    await cliquer('app-photos')
    expect(titreEcran()).toBe('Photos')
    expect(wrapper.get('#mission-titre').text()).toBe('Mission 1 sur 5')
    expect(wrapper.find('[data-testid="reussite"]').exists()).toBe(false)

    const aide = wrapper.get('[data-testid="bouton-aide"]')
    expect(aide.attributes('aria-expanded')).toBe('false')
    await cliquer('bouton-aide')
    expect(aide.attributes('aria-expanded')).toBe('true')
    expect(wrapper.get('[data-testid="aide"]').isVisible()).toBe(true)
    expect(wrapper.get('[data-testid="aide"]').text()).toContain('Touchez l\'icône « Messages »')

    await cliquer('bouton-retour')
    expect(titreEcran()).toBe('Accueil')
  })

  it('fait défiler la liste par un glissement du doigt au-delà du seuil seulement', async () => {
    await monter()
    await cliquer('app-messages')
    const zone = wrapper.get('[data-testid="zone-glisser"]')

    // Petit mouvement : un toucher, pas un glissement
    await pointeur(zone, 'pointerdown', 300)
    await pointeur(zone, 'pointerup', 290)
    expect(annonce()).not.toContain('Messages 2')

    // Le doigt monte d'environ un élément : la liste descend d'un cran
    await pointeur(zone, 'pointerdown', 300)
    await pointeur(zone, 'pointerup', 236)
    expect(annonce()).toContain('Messages 2 à 5 sur 8.')
    // Les messages hors de la fenêtre sont cachés aux lecteurs d'écran, comme à l'écran
    const elements = zone.findAll('li')
    expect(elements[0].attributes('aria-hidden')).toBe('true')
    expect(elements[1].attributes('aria-hidden')).toBeUndefined()

    // Un long glissement vers le haut de l'écran va jusqu'au bout
    await pointeur(zone, 'pointerdown', 400)
    await pointeur(zone, 'pointerup', 100)
    expect(consigne()).toContain('Montez le volume')
  })

  it('fait défiler la liste à la molette', async () => {
    await monter()
    await cliquer('app-messages')

    await wrapper.get('[data-testid="zone-glisser"]').trigger('wheel', { deltaY: 100 })
    expect(annonce()).toContain('Messages 2 à 5 sur 8.')
  })

  it('se fait au clavier avec des contrôles natifs', async () => {
    await monter()

    // Applications, retour, accueil, défilement, interrupteur : de vrais boutons
    for (const bouton of wrapper.findAll('[data-testid="telephone"] button')) {
      expect(bouton.attributes('type')).toBe('button')
      expect(bouton.attributes('tabindex')).toBeUndefined()
    }
    await cliquer('app-reglages')
    // Le curseur est un champ standard, avec une étiquette
    const volume = wrapper.get('[data-testid="volume"]')
    expect(volume.element.tagName).toBe('INPUT')
    expect(volume.attributes('type')).toBe('range')
    expect(wrapper.get(`label[for="${volume.attributes('id')}"]`).text()).toContain('Volume')
  })

  it('remet le téléphone à zéro quand on recommence', async () => {
    await monter()
    await cliquer('app-reglages')
    await cliquer('wifi')
    await cliquer('bouton-accueil')
    await cliquer('app-messages')
    for (let i = 0; i < crans; i += 1) await cliquer('defiler-bas')
    await cliquer('bouton-accueil')
    await cliquer('app-reglages')
    await wrapper.get('[data-testid="volume"]').setValue('10')
    await cliquer('bouton-accueil')
    await cliquer('continuer')

    await cliquer('recommencer')
    expect(wrapper.get('#mission-titre').text()).toBe('Mission 1 sur 5')
    expect(titreEcran()).toBe('Accueil')
    expect(document.activeElement?.id).toBe('mission-titre')
  })
})
