import { test, expect } from './fixtures.js'

// US-13 : l'espace personnel est réservé aux utilisateurs connectés
test('un visiteur qui ouvre son profil est renvoyé à l’accueil, destination mémorisée', async ({ page }) => {
  await page.goto('/spa/')
  await page.evaluate(() => sessionStorage.removeItem('redirectAfterLogin'))

  // Navigation côté client (la garde du routeur Vue s'applique)
  await page.evaluate(() => {
    history.pushState({}, '', '/spa/profile')
    dispatchEvent(new PopStateEvent('popstate'))
  })

  await expect(page).toHaveURL(/\/spa\/$/)
  expect(await page.evaluate(() => sessionStorage.getItem('redirectAfterLogin'))).toBe('/profile')
})

// US-14 / AN-01 : le back-office exige une connexion (contrôle côté serveur)
test('le back-office redirige un visiteur vers la page de connexion', async ({ page }) => {
  await page.goto('/admin')

  await expect(page).toHaveURL(/\/login$/)
  await expect(page.locator('input[type="password"]')).toBeVisible()
})
