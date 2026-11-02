import { beforeEach, describe, expect, it, vi } from 'vitest'
import { flushPromises, mount } from '@vue/test-utils'
import { createPinia, setActivePinia } from 'pinia'
import { ref } from 'vue'
import { createMemoryHistory, createRouter } from 'vue-router'

// useData ouvre IndexedDB et appelle l'API : on le remplace par des données contrôlées
const cats = ref([])
const menus = ref([])
const fetchMenus = vi.fn(async () => {})
vi.mock('@/utils/fetchDataPwa', () => ({ useData: () => ({ cats, menus, fetchMenus }) }))

const { default: HomePage } = await import('@/components/features/home/HomePage.vue')
const { default: AudiencePage } = await import('@/views/AudiencePage.vue')
const { default: CategoryPage } = await import('@/views/CategoryPage.vue')

const Stub = { template: '<div />' }

const mountAt = async (component, props = {}, path = '/') => {
  const router = createRouter({
    history: createMemoryHistory(),
    routes: [
      { path: '/', component: Stub },
      { path: '/apprendre', component: Stub },
      { path: '/accompagner', component: Stub },
      { path: '/category/:category', component: Stub },
    ],
  })
  router.push(path)
  await router.isReady()
  const wrapper = mount(component, { props, global: { plugins: [router] } })
  await flushPromises()
  return wrapper
}

const hrefs = (wrapper) => wrapper.findAll('a').map((link) => link.attributes('href'))

beforeEach(() => {
  setActivePinia(createPinia())
  cats.value = []
  menus.value = []
})

describe('accueil', () => {
  it('propose les deux entrées comme des liens titrés', async () => {
    const wrapper = await mountAt(HomePage)

    const apprendre = wrapper.get('[data-testid="entry-apprendre"] h2 a')
    const accompagner = wrapper.get('[data-testid="entry-accompagner"] h2 a')
    expect(apprendre.text()).toBe("J'apprends le numérique")
    expect(apprendre.attributes('href')).toBe('/apprendre')
    expect(accompagner.text()).toBe("J'accompagne des personnes")
    expect(accompagner.attributes('href')).toBe('/accompagner')
  })

  it('a un seul titre principal et rappelle que le site n\'est pas un service officiel', async () => {
    const wrapper = await mountAt(HomePage)

    expect(wrapper.findAll('h1')).toHaveLength(1)
    expect(wrapper.text()).toContain("Ce site n'est pas un service de l'administration")
    expect(wrapper.text()).not.toMatch(/inscri(s|v)ez-vous|connexion/i)
  })
})

describe('catalogue d\'un public', () => {
  it('liste les parcours de ce public et renvoie vers l\'autre entrée', async () => {
    cats.value = [
      { id: 1, name: 'messagerie', audience: 'apprendre' },
      { id: 2, name: 'animer un atelier', audience: 'accompagner' },
    ]
    menus.value = [{ category: { id: 1 } }, { category: { id: 2 } }]

    const wrapper = await mountAt(AudiencePage, { audienceKey: 'apprendre' })

    expect(fetchMenus).toHaveBeenCalled()
    expect(wrapper.get('h1').text()).toBe("J'apprends le numérique")
    const list = wrapper.get('[data-testid="parcours-list"]')
    expect(list.findAll('li')).toHaveLength(1)
    expect(list.get('a').attributes('href')).toBe('/category/messagerie')
    expect(list.text()).toContain('1 cours')
    expect(hrefs(wrapper)).toContain('/accompagner')
  })

  it('réserve une place à la miniature : image si le parcours en a une, icône sinon', async () => {
    cats.value = [
      { id: 1, name: 'avec miniature', audience: 'apprendre', ordre: 1, illustrationUrl: '/uploads/course-media/a.png', illustrationAlt: 'Un clavier et une souris' },
      { id: 2, name: 'sans miniature', audience: 'apprendre', ordre: 2 },
    ]
    menus.value = [{ category: { id: 1 } }, { category: { id: 2 } }]

    const wrapper = await mountAt(AudiencePage, { audienceKey: 'apprendre' })

    const thumbs = wrapper.findAll('[data-testid="parcours-thumb"]')
    expect(thumbs).toHaveLength(2)
    const image = thumbs[0].get('img')
    expect(image.attributes('src')).toBe('/uploads/course-media/a.png')
    expect(image.attributes('alt')).toBe('Un clavier et une souris')
    expect(thumbs[1].find('img').exists()).toBe(false)
    expect(thumbs[1].get('i').attributes('aria-hidden')).toBe('true')
  })

  it('annonce simplement qu\'aucun parcours n\'est encore publié', async () => {
    const wrapper = await mountAt(AudiencePage, { audienceKey: 'accompagner' })

    expect(wrapper.find('[data-testid="parcours-list"]').exists()).toBe(false)
    expect(wrapper.get('[data-testid="parcours-empty"]').text()).toContain('en préparation')
  })
})

describe('parcours', () => {
  it('affiche un cours publié sans module', async () => {
    cats.value = [{ id: 1, name: 'Premiers gestes numériques', audience: 'apprendre' }]
    menus.value = [{
      title: 'Utiliser un écran tactile',
      category: { id: 1, name: 'Premiers gestes numériques' },
      menu: null,
      page: { slug: '/utiliser-un-ecran-tactile' },
    }]

    const wrapper = await mountAt(
      CategoryPage,
      {},
      '/category/Premiers%20gestes%20num%C3%A9riques',
    )

    expect(wrapper.text()).toContain('Premiers gestes numériques')
    expect(wrapper.text()).toContain('Utiliser un écran tactile')
    expect(wrapper.get('a[href="/pages/utiliser-un-ecran-tactile"]').exists()).toBe(true)
  })
})
