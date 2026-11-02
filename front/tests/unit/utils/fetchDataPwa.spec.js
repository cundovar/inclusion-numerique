import 'fake-indexeddb/auto'
import { IDBFactory } from 'fake-indexeddb'
import { describe, it, expect, beforeEach, vi } from 'vitest'

vi.mock('axios', () => ({ default: { get: vi.fn() } }))

const networkError = () => Object.assign(new Error('Network Error'), { response: undefined })

// Recharge le module (états partagés vides) sur la même base IndexedDB : simule un nouveau lancement de l'app
async function launchApp() {
  vi.resetModules()
  const axios = (await import('axios')).default
  const { useData } = await import('@/utils/fetchDataPwa')
  return { axios, data: useData() }
}

beforeEach(() => {
  globalThis.indexedDB = new IDBFactory()
  vi.spyOn(console, 'log').mockImplementation(() => {})
  vi.spyOn(console, 'warn').mockImplementation(() => {})
})

describe('fetchDataPwa — repli hors ligne sur IndexedDB', () => {
  it('en ligne, enregistre menus et catégories visibles ; hors ligne, les relit', async () => {
    const online = await launchApp()
    online.axios.get.mockImplementation(async (url) => ({
      data: {
        member: url === '/api/categories'
          ? [{ id: 1, name: 'css' }]
          : [{ id: 10, title: 'Flexbox' }, { id: 11, title: 'Brouillon', visible: false }]
      }
    }))
    await online.data.fetchMenus()
    expect(online.data.menus.value).toEqual([{ id: 10, title: 'Flexbox' }])

    const offline = await launchApp()
    offline.axios.get.mockRejectedValue(networkError())
    expect(offline.data.menus.value).toEqual([])

    await offline.data.fetchMenus()

    expect(offline.data.menus.value).toEqual([{ id: 10, title: 'Flexbox' }])
    expect(offline.data.cats.value).toEqual([{ id: 1, name: 'css' }])
  })

  it('hors ligne, restaure l’utilisateur précédemment connecté', async () => {
    const online = await launchApp()
    online.axios.get.mockResolvedValue({ data: { username: 'alice', roles: ['ROLE_USER'] } })
    await online.data.fetchUser()

    const offline = await launchApp()
    offline.axios.get.mockRejectedValue(networkError())
    await offline.data.fetchUser()

    expect(offline.data.user.value).toEqual({ username: 'alice', roles: ['ROLE_USER'] })
  })

  it('une session expirée (401) efface l’utilisateur mis en cache', async () => {
    const online = await launchApp()
    online.axios.get.mockResolvedValue({ data: { username: 'alice', roles: ['ROLE_USER'] } })
    await online.data.fetchUser()

    online.axios.get.mockRejectedValue(Object.assign(new Error('Unauthorized'), { response: { status: 401 } }))
    await online.data.fetchUser()
    expect(online.data.user.value).toEqual({ username: '', roles: [] })

    const offline = await launchApp()
    offline.axios.get.mockRejectedValue(networkError())
    await offline.data.fetchUser()

    expect(offline.data.user.value).toBeNull()
  })
})

describe('fetchDataPwa — contenus personnalisés hors ligne', () => {
  it('à la déconnexion, efface le catalogue et les pages qui ont pu être réservés à la personne', async () => {
    const online = await launchApp()
    online.axios.get.mockImplementation(async (url) => {
      if (url === '/user-api/me') return { data: { username: 'alice', roles: ['ROLE_USER'] } }
      if (url === '/api/categories') return { data: { member: [{ id: 1, name: 'Atelier Alice', reserve: true }] } }
      return { data: { member: [{ id: 10, title: 'Pour Alice', reserve: true }] } }
    })
    await online.data.fetchUser()
    await online.data.fetchMenus()

    window.dispatchEvent(new Event('auth:logout'))
    await vi.waitFor(async () => {
      const offline = await launchApp()
      offline.axios.get.mockRejectedValue(networkError())
      await offline.data.fetchMenus()
      expect(offline.data.menus.value).toEqual([])
      expect(offline.data.cats.value).toEqual([])
    })
  })

  it('un visiteur jamais connecté garde son catalogue hors ligne', async () => {
    const online = await launchApp()
    online.axios.get.mockImplementation(async (url) => {
      if (url === '/user-api/me') throw Object.assign(new Error('Unauthorized'), { response: { status: 401 } })
      return { data: { member: [{ id: 10, title: 'Public' }] } }
    })
    await online.data.fetchMenus()
    await online.data.fetchUser()

    const offline = await launchApp()
    offline.axios.get.mockRejectedValue(networkError())
    await offline.data.fetchMenus()

    expect(offline.data.menus.value).toEqual([{ id: 10, title: 'Public' }])
  })

  it('un cours qui n’est plus servi disparaît du cache au rechargement suivant', async () => {
    const first = await launchApp()
    first.axios.get.mockResolvedValue({ data: { member: [{ id: 10, title: 'Public' }, { id: 11, title: 'Retiré' }] } })
    await first.data.fetchMenus()
    first.axios.get.mockResolvedValue({ data: { member: [{ id: 10, title: 'Public' }] } })
    await first.data.fetchMenus()

    const offline = await launchApp()
    offline.axios.get.mockRejectedValue(networkError())
    await offline.data.fetchMenus()

    expect(offline.data.menus.value).toEqual([{ id: 10, title: 'Public' }])
  })
})
