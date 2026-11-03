import 'fake-indexeddb/auto'
import { IDBFactory } from 'fake-indexeddb'
import { describe, it, expect, beforeEach, vi } from 'vitest'

vi.mock('@/services/api', () => ({ default: { get: vi.fn() } }))

const networkError = () => Object.assign(new Error('Network Error'), { response: undefined })

async function charger() {
  vi.resetModules()
  const api = (await import('@/services/api')).default
  const { chargerCaptures } = await import('@/services/captures')
  return { api, chargerCaptures }
}

beforeEach(() => {
  globalThis.indexedDB = new IDBFactory()
})

describe('captures — lecture hors ligne', () => {
  it('garde les captures reçues et les relit sans réseau', async () => {
    const online = await charger()
    online.api.get.mockResolvedValue({ data: { member: [{ id: 3, url: '/uploads/course-media/c.png', caption: 'Accueil' }] } })
    expect(await online.chargerCaptures([3])).toHaveLength(1)
    expect(online.api.get).toHaveBeenCalledWith('/captures?id[]=3')

    const offline = await charger()
    offline.api.get.mockRejectedValue(networkError())

    expect(await offline.chargerCaptures([3, 4])).toEqual([{ id: 3, url: '/uploads/course-media/c.png', caption: 'Accueil' }])
  })

  it('une erreur du serveur n’est pas masquée par les copies', async () => {
    const { api, chargerCaptures } = await charger()
    api.get.mockRejectedValue(Object.assign(new Error('Erreur'), { response: { status: 500 } }))

    await expect(chargerCaptures([3])).rejects.toThrow('Erreur')
  })
})
