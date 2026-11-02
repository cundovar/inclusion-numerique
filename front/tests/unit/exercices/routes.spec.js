import { describe, expect, it } from 'vitest'
import { flushPromises, mount } from '@vue/test-utils'
import { createMemoryHistory, createRouter } from 'vue-router'
import router from '@/router'
import ExercicesPage from '@/views/ExercicesPage.vue'
import ExercicePage from '@/views/ExercicePage.vue'

const Stub = { template: '<div />' }

const monter = async (component, props = {}) => {
  const memoire = createRouter({
    history: createMemoryHistory(),
    routes: [
      { path: '/', component: Stub },
      { path: '/exercices', component: Stub },
      { path: '/exercices/:slug', component: Stub },
    ],
  })
  memoire.push('/')
  await memoire.isReady()
  const wrapper = mount(component, { props, global: { plugins: [memoire] } })
  await flushPromises()
  return wrapper
}

describe('routes des exercices', () => {
  it('déclare le catalogue et la page d\'un exercice', () => {
    expect(router.resolve('/exercices').name).toBe('exercices')

    const page = router.resolve('/exercices/trouvez-le-vrai-site')
    expect(page.name).toBe('exercice')
    expect(page.params.slug).toBe('trouvez-le-vrai-site')
  })

  it('liste les exercices par public dans le catalogue', async () => {
    const wrapper = await monter(ExercicesPage)

    expect(wrapper.findAll('h1')).toHaveLength(1)
    const liste = wrapper.get('[data-testid="exercices-apprendre"]')
    expect(liste.text()).toContain('Trouvez le vrai site')
    expect(liste.text()).toContain('5 minutes')
    expect(liste.text()).toContain('1.1 Mener une recherche d\'information')
    expect(liste.find('a[href="/exercices/trouvez-le-vrai-site"]').exists()).toBe(true)
  })

  it('affiche la consigne d\'un exercice connu', async () => {
    const wrapper = await monter(ExercicePage, { slug: 'trouvez-le-vrai-site' })

    expect(wrapper.get('h1').text()).toBe('Trouvez le vrai site')
    expect(wrapper.text()).toContain('Choisissez le bon site.')
    expect(wrapper.find('[data-testid="exercice-introuvable"]').exists()).toBe(false)
  })

  it('affiche un message clair pour un slug inconnu', async () => {
    const wrapper = await monter(ExercicePage, { slug: 'n-existe-pas' })

    const message = wrapper.get('[data-testid="exercice-introuvable"]')
    expect(message.get('h1').text()).toBe('Exercice introuvable')
    expect(message.get('a').attributes('href')).toBe('/exercices')
  })
})
