import { test, expect } from '@playwright/test'

// Première visite : la notification des mises à jour s'affiche une fois, puis plus jamais (cookie d'un an)
test('la notification de première visite se ferme et ne revient pas', async ({ page, context }) => {
  await page.goto('/spa/')

  const modal = page.getByText('Notification', { exact: true })
  await expect(modal).toBeVisible()
  await page.keyboard.press('Escape')
  await expect(modal).toBeHidden()

  const cookies = await context.cookies()
  expect(cookies.find(c => c.name === 'notifVue')?.value).toBe('true')

  await page.reload()
  await page.waitForTimeout(1500) // la modale apparaît après 1 s quand le cookie est absent
  await expect(modal).toBeHidden()
})
