import { test, expect } from './fixtures.js'

// Choisit une option dans un sélecteur en cascade Element Plus
async function pickCascader(page, placeholder, option) {
  await page.getByPlaceholder(placeholder).click()
  await page.locator('.el-cascader-node:visible', { hasText: option }).first().click()
}

// US-06 / US-07 : un visiteur passe un QCM complet et obtient son score
test('QCM CSS niveau basique : 10 questions puis score', async ({ page }) => {
  await page.goto('/spa/qcm')

  await pickCascader(page, 'Sélectionnez un niveau', 'basique')
  await pickCascader(page, 'Sélectionnez une langue', 'CSS')
  await page.getByRole('button', { name: /GO/ }).click()

  await expect(page).toHaveURL(/\/spa\/qcm\/0\?/)

  for (let i = 0; i < 10; i++) {
    // Une question affiche 4 choix ; on choisit le premier puis on valide
    const choices = page.locator('.el-radio')
    await expect(choices).toHaveCount(4)
    await choices.first().click()
    await page.getByRole('button', { name: 'Réponse' }).click()

    const next = i < 9 ? 'Question suivante' : 'Fin'
    await page.getByRole('button', { name: next }).click()
  }

  await expect(page).toHaveURL(/\/spa\/qcm\/result/)
  await expect(page.getByText('Votre score')).toBeVisible()
  await expect(page.locator('h2').filter({ hasText: '/ 10' })).toBeVisible()
  // Visiteur non connecté : le résultat n'est pas enregistré, on l'invite à se connecter
  await expect(page.getByTestId('qcm-save-status')).toHaveText(/Connectez-vous/)
})
