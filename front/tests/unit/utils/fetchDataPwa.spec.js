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
