import { describe, it, expect } from 'vitest'
import { apiRuntimeCaching } from '../../../pwa/apiCache.js'

const [plugin] = apiRuntimeCaching.options.plugins
const keep = (response) => plugin.cacheWillUpdate({ response })
const get = (path) => ({ request: { method: 'GET' }, url: new URL(path, 'https://site.test') })

describe('service worker — lecture de l’API', () => {
  it('ne garde jamais une réponse marquée par le serveur (administrateur, brouillons)', async () => {
    const forAdmin = new Response('{}', { status: 200, headers: { 'X-Offline-Cache': 'none' } })

    expect(await keep(forAdmin)).toBeNull()
  })

  it('garde une réponse publique réussie pour la lecture hors ligne, pas une erreur', async () => {
    const publicResponse = new Response('{}', { status: 200 })

    expect(await keep(publicResponse)).toBe(publicResponse)
    expect(await keep(new Response('{}', { status: 500 }))).toBeNull()
  })

  it('attend le réseau : le cache ne sert que si la connexion est coupée', () => {
    expect(apiRuntimeCaching.handler).toBe('NetworkFirst')
    expect(apiRuntimeCaching.options.networkTimeoutSeconds).toBeUndefined()
  })

  it('ne concerne que la lecture des contenus, jamais les données personnelles', () => {
    expect(apiRuntimeCaching.urlPattern(get('/api/page_contents'))).toBe(true)
    expect(apiRuntimeCaching.urlPattern(get('/api/users/3'))).toBe(false)
    expect(apiRuntimeCaching.urlPattern({ ...get('/api/page_contents'), request: { method: 'POST' } })).toBe(false)
  })
})
