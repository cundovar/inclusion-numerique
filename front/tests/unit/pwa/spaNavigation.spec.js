import { describe, it, expect, afterEach, vi } from 'vitest'
import { spaNavigationCaching, SPA_SHELL_URL } from '../../../pwa/spaNavigation.js'

const [plugin] = spaNavigationCaching.options.plugins
const ouvrir = (mode, path) => spaNavigationCaching.urlPattern({ request: { mode }, url: new URL(path, 'https://site.test') })

// Cache Storage minimal : { nomDuCache: { url: corps } }
const stubCaches = (contenu) => {
  const cache = (entrees) => ({
    keys: async () => Object.keys(entrees).map((url) => ({ url })),
    match: async (request) => new Response(entrees[request.url ?? request])
  })
  vi.stubGlobal('caches', {
    keys: async () => Object.keys(contenu),
    open: async (name) => cache(contenu[name] ?? {})
  })
}

afterEach(() => vi.unstubAllGlobals())

describe('service worker — ouverture des pages de la SPA', () => {
  it('ne concerne que la navigation vers une page /spa/', () => {
    expect(ouvrir('navigate', '/spa/pages/flexbox')).toBe(true)
    expect(ouvrir('cors', '/spa/pages/flexbox')).toBe(false)
    expect(ouvrir('navigate', '/register')).toBe(false)
    expect(ouvrir('navigate', '/build/assets/main.js')).toBe(false)
  })

  it('en ligne, la page vient toujours du serveur', () => {
    expect(spaNavigationCaching.handler).toBe('NetworkFirst')
  })

  it('hors ligne, page jamais ouverte : sert la page préchargée du même build', async () => {
    stubCaches({
      'workbox-precache-v2-https://site.test/spa/': {
        [`https://site.test${SPA_SHELL_URL}&__WB_REVISION__=42`]: 'page préchargée'
      },
      'spa-pages': { 'https://site.test/spa/ancienne': 'page gardée' }
    })

    expect(await (await plugin.handlerDidError()).text()).toBe('page préchargée')
  })

  it('sans page préchargée, sert une page déjà ouverte en ligne', async () => {
    stubCaches({ 'spa-pages': { 'https://site.test/spa/accueil': 'page gardée' } })

    expect(await (await plugin.handlerDidError()).text()).toBe('page gardée')
  })

  it('sans aucune copie, laisse le navigateur signaler l’erreur', async () => {
    stubCaches({})

    expect((await plugin.handlerDidError()).type).toBe('error')
  })
})
