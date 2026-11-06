import { describe, expect, it } from 'vitest'
import { createMemoryHistory, createRouter } from 'vue-router'
import { ancienneAdressePublic } from '@/router/ancienneAdressePublic'

const Vue = { template: '<div />' }

async function ouvrir(adresse) {
  const router = createRouter({
    history: createMemoryHistory(),
    routes: [
      ancienneAdressePublic,
      { path: '/', component: Vue },
      { path: '/apprendre', component: Vue },
      { path: '/:slug(.*)', component: Vue }
    ]
  })
  await router.push(adresse)
  return router.currentRoute.value.fullPath
}

describe('ancienne adresse /public/spa', () => {
  it("ramène à l'accueil", async () => {
    expect(await ouvrir('/public/spa/')).toBe('/')
    expect(await ouvrir('/public/spa')).toBe('/')
  })

  it('garde la page, les paramètres et le repère', async () => {
    expect(await ouvrir('/public/spa/apprendre?a=1#b')).toBe('/apprendre?a=1#b')
  })

  it('ne touche pas aux autres adresses', async () => {
    expect(await ouvrir('/publications')).toBe('/publications')
  })
})
