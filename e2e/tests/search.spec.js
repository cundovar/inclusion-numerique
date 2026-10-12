import { test, expect } from './fixtures.js'

// La recherche d'un visiteur filtre la liste des cours chargée au démarrage : attendre cette liste
async function openHome(page) {
  const coursesLoaded = page.waitForResponse(r => r.url().includes('/api/page_contents') && r.ok())
  await page.goto('/spa/')
  await coursesLoaded
}

// US-04 : recherche d'un cours par mot-clé (recherche locale pour un visiteur)
test('rechercher un cours et ouvrir un résultat', async ({ page }) => {
  await openHome(page)

  const input = page.getByPlaceholder('Rechercher').locator('visible=true').first()
  await input.fill('flexbox')
  await input.press('Enter')

  const results = page.locator('.search-results')
  await expect(results).toBeVisible()
  await expect(results).toContainText(/résultat\(s\) trouvé\(s\)/)

  const firstResult = results.locator('a[href*="/pages/"]').first()
  await expect(firstResult).toContainText(/flexbox/i)
  await firstResult.click()

  await expect(page).toHaveURL(/\/spa\/pages\//)
  await expect(page.locator('main.main-content h1')).not.toBeEmpty()
})

test('une recherche sans correspondance vide la liste de résultats', async ({ page }) => {
  await openHome(page)
  const input = page.getByPlaceholder('Rechercher').locator('visible=true').first()
  const results = page.locator('.search-results')

  await input.fill('flexbox')
  await input.press('Enter')
  await expect(results).toBeVisible()

  await input.fill('zzqqxxnotfound')
  await input.press('Enter')
  await expect(results).toHaveCount(0)
})
