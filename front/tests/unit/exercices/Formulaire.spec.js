import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest'
import { flushPromises, mount } from '@vue/test-utils'
import { createMemoryHistory, createRouter } from 'vue-router'
import ScenePlayer from '@/components/features/exercices/ScenePlayer.vue'
import exercice from '@/exercices/remplir-un-formulaire'

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

const FICHE = {
  nom: 'DUPRÉ',
  prenom: 'Rose',
  naissance: '14/02/1956',
  adresse: '8 rue des Lilas',
  'code-postal': '99100',
  ville: 'Villexemple',
}

const remplir = async (valeurs) => {
  for (const [id, valeur] of Object.entries(valeurs)) {
    await wrapper.get(`[data-testid="champ-${id}"]`).setValue(valeur)
  }
}

beforeEach(() => {
  setItem = vi.spyOn(Storage.prototype, 'setItem')
})

afterEach(() => {
  expect(setItem).not.toHaveBeenCalled()
  setItem.mockRestore()
  wrapper?.unmount()
})

describe('ScenePlayer — Remplir un formulaire en ligne', () => {
  it('affiche la fiche, une adresse fictive et les champs obligatoires annoncés', async () => {
    await monter()

    expect(wrapper.get('[data-testid="fiche"]').text()).toContain('99100 Villexemple')
    expect(wrapper.get('[data-testid="adresse"]').text()).toMatch(/\.example\//)
    expect(wrapper.get('label[for="champ-nom"]').text()).toContain('(obligatoire)')
    expect(wrapper.get('label[for="champ-telephone"]').text()).not.toContain('(obligatoire)')
    // Le navigateur ne doit pas proposer les vraies informations de la personne
    expect(wrapper.get('[data-testid="formulaire"]').attributes('autocomplete')).toBe('off')
  })

  it('signale les erreurs, les relie aux champs et les efface une fois corrigées', async () => {
    await monter()

    await wrapper.get('[data-testid="formulaire"]').trigger('submit')
    await flushPromises()
    expect(wrapper.get('[data-testid="resume-erreurs"]').text()).toContain('8 champs sont à corriger')
    const naissance = wrapper.get('[data-testid="champ-naissance"]')
    expect(naissance.attributes('aria-invalid')).toBe('true')
    expect(naissance.attributes('aria-describedby')).toBe('indication-naissance erreur-naissance')
    expect(wrapper.get('[data-testid="erreur-naissance"]').text()).toContain('JJ/MM/AAAA')
    expect(wrapper.get('[data-testid="annonce"]').text()).toBe('8 champs sont à corriger.')

    await naissance.setValue('14/02/1956')
    expect(wrapper.find('[data-testid="erreur-naissance"]').exists()).toBe(false)
    expect(wrapper.get('[data-testid="resume-erreurs"]').text()).toContain('7 champs sont à corriger')
  })

  it('va du formulaire au récapitulatif, permet de modifier puis d\'envoyer', async () => {
    await monter()

    await remplir(FICHE)
    await wrapper.get('[data-testid="champ-logement"]').setValue('Locataire')
    await wrapper.get('[data-testid="champ-certification"]').setValue(true)
    await wrapper.get('[data-testid="formulaire"]').trigger('submit')
    await flushPromises()

    const recap = wrapper.get('[data-testid="recapitulatif"]')
    expect(recap.text()).toContain('14/02/1956')
    expect(recap.text()).toContain('Non renseigné')

    // Retour au formulaire : les saisies sont gardées
    await wrapper.get('[data-testid="modifier"]').trigger('click')
    await flushPromises()
    expect(wrapper.get('[data-testid="champ-ville"]').element.value).toBe('Villexemple')
    await wrapper.get('[data-testid="formulaire"]').trigger('submit')
    await flushPromises()

    await wrapper.get('[data-testid="envoyer"]').trigger('click')
    await flushPromises()
    expect(wrapper.get('[data-testid="numero"]').text()).toBe('DEM-2026-0417')

    await wrapper.get('[data-testid="champ-numero"]').setValue('DEM-2026-0471')
    await wrapper.get('[data-testid="valider-numero"]').trigger('submit')
    expect(wrapper.get('[data-testid="erreur-numero"]').text()).toContain('Relisez-le')

    await wrapper.get('[data-testid="champ-numero"]').setValue('dem-2026-0417')
    await wrapper.get('[data-testid="valider-numero"]').trigger('submit')
    await flushPromises()
    expect(wrapper.get('[data-testid="conclusion"]').text()).toContain('Bravo, la demande de Rose est envoyée.')
  })
})
