import { test, expect } from './fixtures.js'

// US-01 / US-03 : un visiteur trouve une catégorie, ouvre un cours et lit son contenu
test('accueil → catégorie → cours', async ({ page }) => {
  await page.goto('/spa/')

  // Le menu latéral est replié par section : ouvrir « Languages » puis choisir la catégorie
  const css = page.getByRole('link', { name: 'css', exact: true }).locator('visible=true').first()
  if (!(await css.isVisible())) {
    await page.getByText('Languages', { exact: false }).locator('visible=true').first().click()
  }
  await css.click()
  await expect(page).toHaveURL(/\/spa\/category\/css$/)
  await expect(page.locator('.tech-title')).toHaveText(/css/i)

  // Le menu latéral ouvre un aperçu au survol : éloigner la souris comme un utilisateur
  await page.mouse.move(1300, 850)
  const course = page.locator('a.course-link[href^="/spa/pages/"]').first()
  const href = await course.getAttribute('href')
  expect(href).toMatch(/^\/spa\/pages\/.+/)
  await course.click()

  await expect(page).toHaveURL(new RegExp(`${href}$`))
  const title = page.locator('main.main-content h1')
  await expect(title).not.toBeEmpty()
  // Le contenu HTML du cours est rendu, avec la coloration du code par Prism
  await expect(page.locator('main.main-content pre code').first()).toBeVisible()
  await expect(page.locator('main.main-content code span.token').first()).toBeAttached()
})

// Une URL de cours ouverte directement (lien partagé, rechargement) est servie par Symfony puis routée par Vue
test('accès direct à un cours par son URL', async ({ page }) => {
  const response = await page.goto('/spa/pages/flexbox')

  expect(response?.status()).toBe(200)
  await expect(page.locator('main.main-content h1')).toHaveText(/flexbox/i)
})
