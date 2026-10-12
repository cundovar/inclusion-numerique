import { test, expect } from './fixtures.js'

// ENF-02 : un cours déjà consulté reste lisible sans réseau (service worker Workbox)
test('un cours consulté se recharge hors ligne', async ({ page, context }) => {
  await page.goto('/spa/pages/flexbox')
  await expect(page.locator('main.main-content h1')).toHaveText(/flexbox/i)

  // Attendre que le service worker contrôle la page, puis recharger pour remplir ses caches
  await page.evaluate(async () => {
    await navigator.serviceWorker.ready
  })
  await page.reload()
  await expect(page.locator('main.main-content h1')).toHaveText(/flexbox/i)
  expect(await page.evaluate(() => !!navigator.serviceWorker.controller)).toBe(true)

  await context.setOffline(true)
  await page.reload()

  await expect(page.locator('main.main-content h1')).toHaveText(/flexbox/i)
  await context.setOffline(false)
})
