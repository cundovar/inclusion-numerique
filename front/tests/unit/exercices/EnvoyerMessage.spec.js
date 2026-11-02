import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest'
import { flushPromises, mount } from '@vue/test-utils'
import { createMemoryHistory, createRouter } from 'vue-router'
import ScenePlayer from '@/components/features/exercices/ScenePlayer.vue'
import exercice from '@/exercices/envoyer-un-message'

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
}

const cliquer = async (testid) => {
  await wrapper.get(`[data-testid="${testid}"]`).trigger('click')
  await flushPromises()
}
const champ = () => wrapper.get('[data-testid="champ-message"]')
const consigne = () => wrapper.get('[data-testid="consigne-mission"]').text()
const annonce = () => wrapper.get('[role="status"][aria-live="polite"]').text()

beforeEach(() => {
  setItem = vi.spyOn(Storage.prototype, 'setItem')
})

afterEach(() => {
  expect(setItem).not.toHaveBeenCalled()
  setItem.mockRestore()
  wrapper?.unmount()
})

describe('Envoyer un message', () => {
  it('affiche la conversation, le brouillon fautif et pas encore de clavier', async () => {
    await monter()

    expect(wrapper.get('[data-testid="conversation-lea"]').attributes('aria-label')).toBe('Conversation avec Léa')
    expect(wrapper.get('[data-testid="conversation-lea"]').text()).toContain('Tu viens samedi ?')
    expect(champ().text()).toContain('Oui, avec plaisur')
    expect(wrapper.find('[data-testid="clavier"]').exists()).toBe(false)
    expect(consigne()).toContain('faire apparaître le clavier')
  })

  it('corrige la faute avec le clavier tactile puis envoie le message', async () => {
    await monter()

    await cliquer('champ-message')
    const clavier = wrapper.get('[data-testid="clavier"]')
    expect(clavier.attributes('role')).toBe('group')
    expect(clavier.findAll('button')).toHaveLength(30)
    expect(consigne()).toContain('Effacez « ur »')

    await cliquer('touche-effacer')
    expect(annonce()).toContain('« r » effacé.')
    await cliquer('touche-effacer')
    expect(champ().text()).toContain('Oui, avec plais')
    expect(consigne()).toContain('Écrivez les lettres qui manquent')

    await cliquer('touche-i')
    await cliquer('touche-r')
    expect(champ().text()).toContain('Oui, avec plaisir')
    expect(consigne()).toBe('Envoyez le message à Léa.')

    await cliquer('envoyer')
    const envoye = wrapper.get('[data-testid="message-envoye"]')
    expect(envoye.text()).toContain('Vous')
    expect(envoye.text()).toContain('Oui, avec plaisir')
    expect(champ().text()).toContain('Écrire un message')
    expect(wrapper.get('#mission-titre').text()).toBe('Toutes les missions sont faites')

    await cliquer('continuer')
    expect(wrapper.get('[data-testid="conclusion"]').text()).toContain('Bravo, vous avez corrigé et envoyé votre message.')
  })

  it('montre un conseil visible si on envoie trop tôt, sans bloquer', async () => {
    await monter()
    await cliquer('champ-message')
    await cliquer('envoyer')

    expect(wrapper.find('[data-testid="message-envoye"]').exists()).toBe(false)
    expect(wrapper.get('[data-testid="conseil"]').text()).toContain('Corrigez d\'abord le message.')
    expect(annonce()).toContain('Corrigez d\'abord le message.')

    await cliquer('touche-effacer')
    expect(wrapper.find('[data-testid="conseil"]').exists()).toBe(false)
  })

  it('affiche les lettres en majuscule quand le message est vide', async () => {
    await monter()
    await cliquer('champ-message')
    for (let i = 0; i < 'Oui, avec plaisur'.length; i += 1) await cliquer('touche-effacer')

    expect(wrapper.get('[data-testid="touche-a"]').text()).toBe('A')
    await cliquer('touche-a')
    expect(wrapper.get('[data-testid="touche-a"]').text()).toBe('a')
    expect(champ().text()).toContain('A')
  })

  it('accepte le clavier de l\'ordinateur : lettres et touche d\'effacement', async () => {
    await monter()
    await cliquer('champ-message')

    await champ().trigger('keydown', { key: 'Backspace' })
    await champ().trigger('keydown', { key: 'Backspace' })
    await champ().trigger('keydown', { key: 'i' })
    await champ().trigger('keydown', { key: 'r' })
    await flushPromises()

    expect(champ().text()).toContain('Oui, avec plaisir')
    expect(consigne()).toBe('Envoyez le message à Léa.')
  })

  it('donne un nom lisible aux touches sans lettre', async () => {
    await monter()
    await cliquer('champ-message')

    expect(wrapper.get('[data-testid="touche-effacer"]').attributes('aria-label')).toBe('Effacer')
    expect(wrapper.get('[data-testid="touche-espace"]').attributes('aria-label')).toBe('Espace')
    expect(wrapper.get('[data-testid="touche-,"]').attributes('aria-label')).toBe('Virgule')
    expect(wrapper.get('[data-testid="touche-a"]').attributes('aria-label')).toBeUndefined()
    expect(champ().text()).toContain('Zone de texte « Écrire un message »')
  })
})
